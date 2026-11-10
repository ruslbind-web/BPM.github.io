<?php
/**
 * Telegram Authentication & Verification
 * Documentation for Telegram Login Widget & WebApp
 */

require_once __DIR__ . '/db.php';

/**
 * Verifies the data received from the Telegram Login Widget
 * Spec: https://core.telegram.org/widgets/login#checking-authorization
 *
 * @param array $auth_data Array of data received from Telegram (id, first_name, username, photo_url, auth_date, hash)
 * @return bool True if authentic and not expired (within 24 hours), false otherwise
 */
function checkTelegramAuthorization(array $auth_data, string $bot_token): bool {
    $check_hash = $auth_data['hash'] ?? '';
    unset($auth_data['hash']);
    
    $data_check_arr = [];
    foreach ($auth_data as $key => $value) {
        $data_check_arr[] = $key . '=' . $value;
    }
    sort($data_check_arr);
    $data_check_string = implode("\n", $data_check_arr);
    
    $secret_key = hash('sha256', $bot_token, true);
    $hash = hash_hmac('sha256', $data_check_string, $secret_key);
    
    if (strcmp($hash, $check_hash) !== 0) {
        return false;
    }
    
    if ((time() - ($auth_data['auth_date'] ?? 0)) > 86400) {
        return false; // Expired
    }
    
    return true;
}

/**
 * Handles Telegram login, finds or creates user in database
 */
function processTelegramLogin(array $tgUser, PDO $pdo): array {
    $telegram_id = $tgUser['id'];
    $first_name = $tgUser['first_name'] ?? 'Raver';
    $last_name = $tgUser['last_name'] ?? '';
    $username = $tgUser['username'] ?? '';
    $photo_url = $tgUser['photo_url'] ?? '';
    $auth_date = $tgUser['auth_date'] ?? time();

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_id = ? LIMIT 1");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user information
        $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, photo_url = ?, auth_date = ? WHERE id = ?");
        $updateStmt->execute([$first_name, $last_name, $username, $photo_url, $auth_date, $user['id']]);
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['username'] = $username;
        $user['photo_url'] = $photo_url;
    } else {
        // Create user
        $insertStmt = $pdo->prepare("INSERT INTO users (telegram_id, first_name, last_name, username, photo_url, auth_date, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
        $insertStmt->execute([$telegram_id, $first_name, $last_name, $username, $photo_url, $auth_date]);
        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$newId]);
        $user = $stmt->fetch();
    }

    // Set Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['telegram_id'] = $user['telegram_id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['first_name'] . ($user['last_name'] ? ' ' . $user['last_name'] : '');
    $_SESSION['username'] = $user['username'];
    $_SESSION['photo_url'] = $user['photo_url'];

    return $user;
}
