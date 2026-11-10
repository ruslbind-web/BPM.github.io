<?php
/**
 * BPM Cart API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$action = $_REQUEST['action'] ?? 'get';
$response = ['success' => false];

switch ($action) {
    case 'add':
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $size = trim($_POST['size'] ?? 'M');

        if ($productId > 0) {
            $added = addToCart($productId, $quantity, $size);
            if ($added) {
                $response = [
                    'success' => true,
                    'message' => 'Товар добавлен в корзину',
                    'count' => getCartCount(),
                    'subtotal' => getCartSubtotal(),
                    'formatted_subtotal' => formatPrice(getCartSubtotal())
                ];
            } else {
                $response = ['success' => false, 'message' => 'Товар не найден'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Неверный ID товара'];
        }
        break;

    case 'update':
        $itemKey = trim($_POST['item_key'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 1);
        if ($itemKey !== '') {
            updateCartQuantity($itemKey, $quantity);
            $response = [
                'success' => true,
                'count' => getCartCount(),
                'subtotal' => getCartSubtotal(),
                'formatted_subtotal' => formatPrice(getCartSubtotal())
            ];
        }
        break;

    case 'remove':
        $itemKey = trim($_POST['item_key'] ?? '');
        if ($itemKey !== '') {
            removeFromCart($itemKey);
            $response = [
                'success' => true,
                'count' => getCartCount(),
                'subtotal' => getCartSubtotal(),
                'formatted_subtotal' => formatPrice(getCartSubtotal())
            ];
        }
        break;

    case 'clear':
        clearCart();
        $response = ['success' => true, 'count' => 0, 'subtotal' => 0];
        break;

    case 'apply_promo':
        $promoCode = trim($_POST['promo_code'] ?? '');
        $cart = getCartItems();
        if (empty($cart)) {
            $response = ['success' => false, 'message' => 'Корзина пуста'];
            break;
        }

        $calc = calculatePromoDiscount($cart, $promoCode, $pdo);
        if ($calc['valid']) {
            $_SESSION['applied_promo'] = $calc;
            $subtotal = getCartSubtotal();
            $total = max(0, $subtotal - $calc['discount']);
            $response = [
                'success' => true,
                'promo' => $calc,
                'subtotal' => $subtotal,
                'discount' => $calc['discount'],
                'total' => $total,
                'formatted_subtotal' => formatPrice($subtotal),
                'formatted_discount' => formatPrice($calc['discount']),
                'formatted_total' => formatPrice($total),
                'message' => $calc['message']
            ];
        } else {
            unset($_SESSION['applied_promo']);
            $response = [
                'success' => false,
                'message' => $calc['message']
            ];
        }
        break;

    case 'remove_promo':
        unset($_SESSION['applied_promo']);
        $subtotal = getCartSubtotal();
        $response = [
            'success' => true,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'formatted_total' => formatPrice($subtotal),
            'message' => 'Промокод отменен'
        ];
        break;

    case 'get':
    default:
        $cart = getCartItems();
        $subtotal = getCartSubtotal();
        $promo = $_SESSION['applied_promo'] ?? null;
        $discount = 0;
        if ($promo && !empty($promo['code'])) {
            $recalc = calculatePromoDiscount($cart, $promo['code'], $pdo);
            if ($recalc['valid']) {
                $discount = $recalc['discount'];
                $_SESSION['applied_promo'] = $recalc;
            } else {
                unset($_SESSION['applied_promo']);
                $promo = null;
            }
        }
        $total = max(0, $subtotal - $discount);
        $response = [
            'success' => true,
            'items' => array_values($cart),
            'count' => getCartCount(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'formatted_subtotal' => formatPrice($subtotal),
            'formatted_discount' => formatPrice($discount),
            'formatted_total' => formatPrice($total),
            'promo' => $promo
        ];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
