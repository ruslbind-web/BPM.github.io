<?php
/**
 * BPM Orders API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$action = $_REQUEST['action'] ?? 'create';
$response = ['success' => false];

if ($action === 'create') {
    $cart = getCartItems();
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
        exit;
    }

    $customerName = trim($_POST['customer_name'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $customerTelegram = trim($_POST['customer_telegram'] ?? '');
    $customerCity = trim($_POST['customer_city'] ?? '');
    $customerAddress = trim($_POST['customer_address'] ?? '');
    $deliveryType = trim($_POST['delivery_type'] ?? 'СДЭК');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Оплата при получении / СБП');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($customerName) || empty($customerPhone) || empty($customerCity) || empty($customerAddress)) {
        echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля (ФИО, Телефон, Город, Адрес)']);
        exit;
    }

    // Auto-fill Telegram handle from session if logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (empty($customerTelegram) && !empty($_SESSION['username'])) {
        $customerTelegram = '@' . $_SESSION['username'];
    }

    $subtotal = getCartSubtotal();
    $discount = 0;
    $promoCodeStr = null;

    if (!empty($_SESSION['applied_promo'])) {
        $recalc = calculatePromoDiscount($cart, $_SESSION['applied_promo']['code'], $pdo);
        if ($recalc['valid']) {
            $discount = $recalc['discount'];
            $promoCodeStr = $recalc['code'];
        }
    }

    $deliveryCost = ($subtotal >= 10000) ? 0 : 390;
    $totalPrice = max(0, ($subtotal - $discount) + $deliveryCost);
    $orderNumber = 'BPM-' . rand(10000, 99999);

    try {
        $pdo->beginTransaction();

        $orderStmt = $pdo->prepare("INSERT INTO orders 
            (order_number, user_id, customer_name, customer_phone, customer_telegram, customer_city, customer_address, delivery_type, payment_method, subtotal, discount_amount, delivery_cost, total_price, promo_code, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', ?)");
        
        $orderStmt->execute([
            $orderNumber,
            $userId,
            $customerName,
            $customerPhone,
            $customerTelegram,
            $customerCity,
            $customerAddress,
            $deliveryType,
            $paymentMethod,
            $subtotal,
            $discount,
            $deliveryCost,
            $totalPrice,
            $promoCodeStr,
            $notes
        ]);

        $orderId = $pdo->lastInsertId();

        // Insert items
        $itemStmt = $pdo->prepare("INSERT INTO order_items 
            (order_id, product_id, product_title, product_category, product_image, selected_size, price, quantity, total) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stockStmt = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");

        foreach ($cart as $item) {
            $lineTotal = floatval($item['price']) * intval($item['quantity']);
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['title'],
                $item['category_slug'],
                $item['image'],
                $item['size'],
                $item['price'],
                $item['quantity'],
                $lineTotal
            ]);

            // Decrement stock
            $stockStmt->execute([$item['quantity'], $item['id']]);
        }

        // Increment promo code usage if applied
        if ($promoCodeStr) {
            $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = ?")->execute([$promoCodeStr]);
        }

        // Update user's profile phone/address if logged in and was empty
        if ($userId) {
            $pdo->prepare("UPDATE users SET phone = COALESCE(NULLIF(phone, ''), ?), city = COALESCE(NULLIF(city, ''), ?), address = COALESCE(NULLIF(address, ''), ?) WHERE id = ?")
                ->execute([$customerPhone, $customerCity, $customerAddress, $userId]);
        }

        $pdo->commit();

        // Clear cart
        clearCart();

        $response = [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total_price' => $totalPrice,
            'formatted_total' => formatPrice($totalPrice),
            'message' => 'Заказ успешно оформлен!'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        $response = [
            'success' => false,
            'message' => 'Ошибка при создании заказа: ' . $e->getMessage()
        ];
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
