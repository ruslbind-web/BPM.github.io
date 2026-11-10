<?php
/**
 * BPM Telegram & Admin Authentication API
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/telegram_config.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_REQUEST['action'] ?? '';
$response = ['success' => false];

switch ($action) {
    // 1. Official Telegram Login Widget Callback
    case 'telegram_callback':
        $auth_data = $_GET;
        unset($auth_data['action']);

        if (!empty($auth_data['id']) && !empty($auth_data['hash'])) {
            $bot_token = getSetting('telegram_bot_token', TG_BOT_TOKEN);

            // If user hasn't replaced dummy token yet, we still allow login or report clear notice
            $is_dummy_token = ($bot_token === 'YOUR_TELEGRAM_BOT_TOKEN' || empty($bot_token));
            $is_valid = false;

            if (!$is_dummy_token) {
                $is_valid = checkTelegramAuthorization($auth_data, $bot_token);
            } else {
                // In local dev mode without active bot token yet, accept auth if telegram id is present
                $is_valid = true;
            }

            if ($is_valid) {
                $user = processTelegramLogin($auth_data, $pdo);
                header('Location: ../profile.php?login=success');
                exit;
            } else {
                header('Location: ../profile.php?login=error&reason=invalid_hash');
                exit;
            }
        } else {
            header('Location: ../profile.php?login=error&reason=missing_data');
            exit;
        }
        break;

    // 2. Local Dev Simulator: Instant Telegram Login without needing live BotFather domain setup
    case 'dev_telegram_login':
        $username = trim($_POST['username'] ?? 'rave_lover_144');
        $firstName = trim($_POST['first_name'] ?? 'Кибер');
        $lastName = trim($_POST['last_name'] ?? 'Рейвер');
        $phone = trim($_POST['phone'] ?? '+7 (999) 777-14-40');
        $tgId = intval($_POST['telegram_id'] ?? 78429103);

        $fakeTgData = [
            'id' => $tgId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => ltrim($username, '@'),
            'photo_url' => 'assets/images/logo/bpm-logo.svg',
            'auth_date' => time()
        ];

        $user = processTelegramLogin($fakeTgData, $pdo);
        if (!empty($phone)) {
            $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")->execute([$phone, $user['id']]);
        }

        $response = [
            'success' => true,
            'message' => 'Авторизация через Telegram успешна!',
            'user' => $user
        ];
        break;

    // 3. Admin Login
    case 'admin_login':
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === ADMIN_USER && $password === ADMIN_PASS) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = 'BPM Главный Администратор';
            $response = [
                'success' => true,
                'redirect' => 'admin/index.php'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Неверный логин или пароль администратора'
            ];
        }
        break;

    // 4. Update Profile Details
    case 'update_profile':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Не авторизован'];
            break;
        }
        $userId = $_SESSION['user_id'];
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, city = ?, address = ? WHERE id = ?");
        $stmt->execute([$first_name, $last_name, $phone, $city, $address, $userId]);

        $_SESSION['user_name'] = $first_name . ($last_name ? ' ' . $last_name : '');
        $response = ['success' => true, 'message' => 'Профиль сохранен'];
        break;

    // 5. Logout
    case 'logout':
        unset($_SESSION['user_id']);
        unset($_SESSION['telegram_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);
        unset($_SESSION['username']);
        unset($_SESSION['photo_url']);
        unset($_SESSION['admin_logged_in']);
        $response = ['success' => true, 'message' => 'Вы вышли из системы'];
        break;

    default:
        $response = ['success' => false, 'message' => 'Неизвестное действие'];
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
