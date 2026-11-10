<?php
/**
 * BPM Promo Code & Discount Calculation Engine
 * Supports conditional rules:
 * - 'second_print_percent': "берёшь принт, второй со скидкой n%"
 * - 'bundle_hoodie_print': "скидка n% на принт при покупке кофты"
 * - 'percent': процент на весь заказ
 * - 'fixed': фиксированная сумма в рублях
 */

function calculatePromoDiscount(array $cartItems, string $promoCodeInput, PDO $pdo): array {
    $promoCode = trim(strtoupper($promoCodeInput));
    if (empty($promoCode)) {
        return [
            'valid' => false,
            'code' => '',
            'discount' => 0,
            'message' => 'Промокод не введен'
        ];
    }

    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$promoCode]);
    $promo = $stmt->fetch();

    if (!$promo) {
        return [
            'valid' => false,
            'code' => $promoCode,
            'discount' => 0,
            'message' => 'Промокод не найден или деактивирован'
        ];
    }

    // Check expiration
    if (!empty($promo['expires_at']) && strtotime($promo['expires_at']) < strtotime(date('Y-m-d'))) {
        return [
            'valid' => false,
            'code' => $promoCode,
            'discount' => 0,
            'message' => 'Срок действия промокода истёк'
        ];
    }

    // Check usage limits
    if ($promo['max_uses'] > 0 && $promo['used_count'] >= $promo['max_uses']) {
        return [
            'valid' => false,
            'code' => $promoCode,
            'discount' => 0,
            'message' => 'Лимит активаций этого промокода исчерпан'
        ];
    }

    // Calculate subtotal
    $subtotal = 0;
    $clothesItems = [];
    $printItems = [];

    foreach ($cartItems as $item) {
        $qty = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);
        $lineTotal = $price * $qty;
        $subtotal += $lineTotal;

        $category = $item['category_slug'] ?? 'clothes';
        if ($category === 'prints') {
            for ($i = 0; $i < $qty; $i++) {
                $printItems[] = $price;
            }
        } else {
            for ($i = 0; $i < $qty; $i++) {
                $clothesItems[] = $price;
            }
        }
    }

    // Check minimum order amount
    if ($promo['min_order_amount'] > 0 && $subtotal < $promo['min_order_amount']) {
        return [
            'valid' => false,
            'code' => $promoCode,
            'discount' => 0,
            'message' => 'Минимальная сумма заказа для промокода: ' . number_format($promo['min_order_amount'], 0, '', ' ') . ' ₽'
        ];
    }

    $discount = 0;
    $ruleDescription = $promo['description'];

    switch ($promo['discount_type']) {
        case 'second_print_percent':
            // "Берёшь принт, второй со скидкой n%"
            // Needs at least 2 prints in cart
            $printCount = count($printItems);
            if ($printCount < 2) {
                return [
                    'valid' => false,
                    'code' => $promoCode,
                    'discount' => 0,
                    'message' => "Условие не выполнено: добавьте минимум 2 сменных принта в корзину (сейчас: $printCount)"
                ];
            }
            // Sort prints ascending so we discount the cheapest prints or second in pair
            sort($printItems);
            $pairsCount = intval(floor($printCount / 2));
            $percent = floatval($promo['discount_value']);

            // For each pair, apply discount to one print
            for ($i = 0; $i < $pairsCount; $i++) {
                $discount += $printItems[$i] * ($percent / 100);
            }
            $ruleDescription .= " (применена скидка {$percent}% на {$pairsCount} шт. принтов)";
            break;

        case 'bundle_hoodie_print':
            // "Скидка n% на сменный принт при покупке кофты"
            $clothesCount = count($clothesItems);
            $printCount = count($printItems);

            if ($clothesCount < 1 || $printCount < 1) {
                return [
                    'valid' => false,
                    'code' => $promoCode,
                    'discount' => 0,
                    'message' => 'Условие не выполнено: в корзине должна быть минимум 1 кофта и 1 сменный принт'
                ];
            }

            // Apply discount to min(clothesCount, printCount) prints
            sort($printItems);
            $applicableCount = min($clothesCount, $printCount);
            $percent = floatval($promo['discount_value']);

            for ($i = 0; $i < $applicableCount; $i++) {
                $discount += $printItems[$i] * ($percent / 100);
            }
            $ruleDescription .= " (применена скидка {$percent}% на комплект кофта + принт)";
            break;

        case 'percent':
            // Straight percentage off subtotal
            $percent = floatval($promo['discount_value']);
            $discount = $subtotal * ($percent / 100);
            break;

        case 'fixed':
            // Straight fixed amount in rubles
            $discount = min($subtotal, floatval($promo['discount_value']));
            break;

        default:
            $discount = 0;
            break;
    }

    $discount = round($discount, 2);

    return [
        'valid' => true,
        'code' => $promoCode,
        'promo_id' => $promo['id'],
        'discount_type' => $promo['discount_type'],
        'discount_value' => $promo['discount_value'],
        'discount' => $discount,
        'description' => $ruleDescription,
        'message' => "Промокод {$promoCode} успешно применен! Скидка: " . number_format($discount, 0, '', ' ') . ' ₽'
    ];
}
