<?php
/**
 * BPM Likes API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$action = $_REQUEST['action'] ?? 'toggle';
$response = ['success' => false];

if ($action === 'toggle') {
    $productId = intval($_POST['product_id'] ?? 0);
    if ($productId > 0) {
        $result = toggleProductLike($productId);
        $likedItems = getUserLikedProducts();
        $response = [
            'success' => true,
            'liked' => $result['liked'],
            'total_likes' => count($likedItems),
            'message' => $result['liked'] ? 'Добавлено в избранное' : 'Удалено из избранного'
        ];
    } else {
        $response = ['success' => false, 'message' => 'Неверный ID товара'];
    }
} elseif ($action === 'get') {
    $likedItems = getUserLikedProducts();
    $response = [
        'success' => true,
        'count' => count($likedItems),
        'items' => $likedItems
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
