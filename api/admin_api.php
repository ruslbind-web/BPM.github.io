<?php
/**
 * BPM Admin API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

// Auth check
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен. Требуются права администратора.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$response = ['success' => false];

switch ($action) {
    // 1. Save (Create or Update) Product
    case 'save_product':
        $id = intval($_POST['id'] ?? 0);
        $category_slug = trim($_POST['category_slug'] ?? 'clothes');
        $category_id = ($category_slug === 'prints') ? 2 : 1;
        $title = trim($_POST['title'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
        $short_desc = trim($_POST['short_desc'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sizes = trim($_POST['sizes'] ?? ($category_slug === 'clothes' ? 'S, M, L, XL, OVERSIZE' : 'Стандарт (20x15 см)'));
        $material = trim($_POST['material'] ?? '500 GSM Хлопок 100%');
        $badge = trim($_POST['badge'] ?? '');
        $velcro_zone = trim($_POST['velcro_zone'] ?? 'Грудь (20x15 см)');
        $stock = intval($_POST['stock'] ?? 50);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $image = trim($_POST['image'] ?? '');

        // Handle File Upload if provided
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                $filename = 'product_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $target = __DIR__ . '/../assets/images/products/' . $filename;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                    $image = 'assets/images/products/' . $filename;
                }
            }
        }

        if (empty($image)) {
            $image = ($category_slug === 'prints') ? 'assets/images/products/print-psy.svg' : 'assets/images/products/hoodie-psy.svg';
        }

        if (empty($title) || $price <= 0) {
            $response = ['success' => false, 'message' => 'Укажите название и корректную цену товара'];
            break;
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET 
                category_id = ?, category_slug = ?, title = ?, slug = ?, price = ?, old_price = ?, 
                short_desc = ?, description = ?, image = ?, sizes = ?, material = ?, badge = ?, 
                velcro_zone = ?, is_featured = ?, stock = ? 
                WHERE id = ?");
            $stmt->execute([
                $category_id, $category_slug, $title, $slug, $price, $old_price,
                $short_desc, $description, $image, $sizes, $material, $badge,
                $velcro_zone, $is_featured, $stock, $id
            ]);
            $response = ['success' => true, 'message' => 'Карточка товара успешно обновлена'];
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO products 
                (category_id, category_slug, title, slug, price, old_price, short_desc, description, image, sizes, material, badge, velcro_zone, is_featured, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $category_id, $category_slug, $title, $slug, $price, $old_price,
                $short_desc, $description, $image, $sizes, $material, $badge,
                $velcro_zone, $is_featured, $stock
            ]);
            $response = ['success' => true, 'message' => 'Новый товар успешно добавлен в каталог!'];
        }
        break;

    // 2. Delete Product
    case 'delete_product':
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $response = ['success' => true, 'message' => 'Товар удален'];
        }
        break;

    // 3. Save Promo Code (with complex rules)
    case 'save_promo':
        $id = intval($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount_type = trim($_POST['discount_type'] ?? 'second_print_percent');
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $min_order_amount = floatval($_POST['min_order_amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $max_uses = intval($_POST['max_uses'] ?? 1000);
        $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($code) || $discount_value <= 0) {
            $response = ['success' => false, 'message' => 'Укажите код промокода и величину скидки'];
            break;
        }

        if (empty($description)) {
            if ($discount_type === 'second_print_percent') {
                $description = "Берёшь принт — второй со скидкой {$discount_value}%";
            } elseif ($discount_type === 'bundle_hoodie_print') {
                $description = "Скидка {$discount_value}% на принт при покупке кофты";
            } elseif ($discount_type === 'percent') {
                $description = "Скидка {$discount_value}% на всё";
            } else {
                $description = "Скидка {$discount_value} ₽ на заказ";
            }
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE promo_codes SET 
                code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, 
                description = ?, max_uses = ?, expires_at = ?, is_active = ? 
                WHERE id = ?");
            $stmt->execute([
                $code, $discount_type, $discount_value, $min_order_amount,
                $description, $max_uses, $expires_at, $is_active, $id
            ]);
            $response = ['success' => true, 'message' => 'Промокод успешно обновлен'];
        } else {
            // Check uniqueness
            $check = $pdo->prepare("SELECT id FROM promo_codes WHERE code = ?");
            $check->execute([$code]);
            if ($check->fetch()) {
                $response = ['success' => false, 'message' => 'Промокод с таким кодом уже существует!'];
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO promo_codes 
                (code, discount_type, discount_value, min_order_amount, description, max_uses, expires_at, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $code, $discount_type, $discount_value, $min_order_amount,
                $description, $max_uses, $expires_at, $is_active
            ]);
            $response = ['success' => true, 'message' => 'Новый промокод создан!'];
        }
        break;

    // 4. Delete Promo Code
    case 'delete_promo':
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM promo_codes WHERE id = ?")->execute([$id]);
            $response = ['success' => true, 'message' => 'Промокод удален'];
        }
        break;

    // 5. Update Order Status
    case 'update_order_status':
        $orderId = intval($_POST['order_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'new');
        $validStatuses = ['new', 'processing', 'assembled', 'shipped', 'delivered', 'cancelled'];

        if ($orderId > 0 && in_array($status, $validStatuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            $response = ['success' => true, 'message' => 'Статус заказа обновлен на: ' . $status];
        } else {
            $response = ['success' => false, 'message' => 'Неверный статус'];
        }
        break;

    // 6. Save Shop & Telegram Settings
    case 'save_settings':
        $botUsername = trim($_POST['telegram_bot_username'] ?? '');
        $botToken = trim($_POST['telegram_bot_token'] ?? '');
        $freeShipping = trim($_POST['free_shipping_threshold'] ?? '10000');
        $shippingCost = trim($_POST['shipping_cost'] ?? '390');

        $settStmt = $pdo->prepare("INSERT INTO shop_settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $settStmt->execute(['telegram_bot_username', $botUsername]);
        $settStmt->execute(['telegram_bot_token', $botToken]);
        $settStmt->execute(['free_shipping_threshold', $freeShipping]);
        $settStmt->execute(['shipping_cost', $shippingCost]);

        $response = ['success' => true, 'message' => 'Настройки успешно сохранены'];
        break;

    default:
        $response = ['success' => false, 'message' => 'Неизвестное действие'];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
