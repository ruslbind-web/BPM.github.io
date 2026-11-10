<?php
/**
 * BPM Core Helpers & Business Logic
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/promo_calculator.php';

/**
 * Format price to rubles
 */
function formatPrice($price) {
    return number_format(floatval($price), 0, '', ' ') . ' ₽';
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function isAdmin() {
    return !empty($_SESSION['admin_logged_in']) || 
           (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

/**
 * Cart Management (Stored in $_SESSION['cart'] with item keys: product_id_size)
 */
function initCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function addToCart($productId, $quantity = 1, $size = 'M') {
    global $pdo;
    initCart();
    
    $stmt = $pdo->prepare("SELECT id, title, price, old_price, image, category_slug, sizes FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) return false;

    $itemKey = $productId . '_' . ($size ?: 'default');
    if (isset($_SESSION['cart'][$itemKey])) {
        $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$itemKey] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => floatval($product['price']),
            'old_price' => $product['old_price'] ? floatval($product['old_price']) : null,
            'image' => $product['image'],
            'category_slug' => $product['category_slug'],
            'size' => $size,
            'quantity' => $quantity
        ];
    }
    return true;
}

function updateCartQuantity($itemKey, $quantity) {
    initCart();
    $quantity = intval($quantity);
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$itemKey]);
    } elseif (isset($_SESSION['cart'][$itemKey])) {
        $_SESSION['cart'][$itemKey]['quantity'] = $quantity;
    }
}

function removeFromCart($itemKey) {
    initCart();
    if (isset($_SESSION['cart'][$itemKey])) {
        unset($_SESSION['cart'][$itemKey]);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
    unset($_SESSION['applied_promo']);
}

function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += intval($item['quantity'] ?? 1);
    }
    return $count;
}

function getCartSubtotal() {
    initCart();
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += floatval($item['price']) * intval($item['quantity']);
    }
    return $subtotal;
}

/**
 * Likes / Favorites (stored in DB if logged in, in session if guest)
 */
function toggleProductLike($productId) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $productId = intval($productId);

    if ($userId) {
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        if ($stmt->fetch()) {
            $del = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND product_id = ?");
            $del->execute([$userId, $productId]);
            return ['liked' => false];
        } else {
            $ins = $pdo->prepare("INSERT INTO likes (user_id, product_id) VALUES (?, ?)");
            $ins->execute([$userId, $productId]);
            return ['liked' => true];
        }
    } else {
        // Session fallback for guests
        if (!isset($_SESSION['guest_likes'])) {
            $_SESSION['guest_likes'] = [];
        }
        $key = array_search($productId, $_SESSION['guest_likes']);
        if ($key !== false) {
            unset($_SESSION['guest_likes'][$key]);
            return ['liked' => false];
        } else {
            $_SESSION['guest_likes'][] = $productId;
            return ['liked' => true];
        }
    }
}

function isProductLiked($productId) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $productId = intval($productId);
    if ($userId) {
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetch();
    }
    return in_array($productId, $_SESSION['guest_likes'] ?? []);
}

function getUserLikedProducts() {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $stmt = $pdo->prepare("SELECT p.* FROM products p 
            INNER JOIN likes l ON p.id = l.product_id 
            WHERE l.user_id = ? ORDER BY l.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } else {
        $likes = $_SESSION['guest_likes'] ?? [];
        if (empty($likes)) return [];
        $placeholders = implode(',', array_fill(0, count($likes), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($likes);
        return $stmt->fetchAll();
    }
}

/**
 * Recently Viewed Tracker
 */
function recordProductView($productId) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $productId = intval($productId);

    if ($userId) {
        $stmt = $pdo->prepare("INSERT INTO recently_viewed (user_id, product_id, viewed_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE viewed_at = NOW()");
        $stmt->execute([$userId, $productId]);
    } else {
        if (!isset($_SESSION['recent_views'])) {
            $_SESSION['recent_views'] = [];
        }
        // Remove if exists and prepend to front
        $_SESSION['recent_views'] = array_values(array_diff($_SESSION['recent_views'], [$productId]));
        array_unshift($_SESSION['recent_views'], $productId);
        // Keep last 10
        $_SESSION['recent_views'] = array_slice($_SESSION['recent_views'], 0, 10);
    }
}

function getRecentlyViewedProducts($limit = 6, $excludeId = 0) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $excludeId = intval($excludeId);

    if ($userId) {
        $query = "SELECT p.* FROM products p 
                  INNER JOIN recently_viewed r ON p.id = r.product_id 
                  WHERE r.user_id = ?";
        $params = [$userId];
        if ($excludeId > 0) {
            $query .= " AND p.id != ?";
            $params[] = $excludeId;
        }
        $query .= " ORDER BY r.viewed_at DESC LIMIT " . intval($limit);
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } else {
        $recent = $_SESSION['recent_views'] ?? [];
        if ($excludeId > 0) {
            $recent = array_diff($recent, [$excludeId]);
        }
        if (empty($recent)) return [];
        $recent = array_slice($recent, 0, $limit);
        $placeholders = implode(',', array_fill(0, count($recent), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_values($recent));
        return $stmt->fetchAll();
    }
}
