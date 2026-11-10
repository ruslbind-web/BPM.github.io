<?php
require_once __DIR__ . '/../includes/functions.php';

$errorMsg = '';

// 1. Instant 1-Click Quick Admin Login
if (isset($_GET['action']) && $_GET['action'] === 'quick_login') {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'BPM Администратор';
    header('Location: index.php');
    exit;
}

// 2. Direct Standard Form POST Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Allow standard admin credentials or quick bypass
    if (($username === ADMIN_USER && $password === ADMIN_PASS) || isset($_POST['btn_quick'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = 'BPM Администратор';
        header('Location: index.php');
        exit;
    } else {
        $errorMsg = 'Неверный логин или пароль администратора';
    }
}

if (isAdmin()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BPM Admin — Вход в панель</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

<div class="admin-modal-card" style="max-width: 460px; box-shadow: 0 0 50px rgba(0, 240, 255, 0.25); border-color: var(--neon-cyan);">
  <div style="text-align: center; margin-bottom: 24px;">
    <span class="brand-logo" style="justify-content: center; font-size: 32px;">BPM<span class="brand-dot">.</span></span>
    <p style="font-family: var(--font-mono); font-size: 11px; color: var(--neon-cyan); letter-spacing: 2px; margin-top: 6px;">ПАНЕЛЬ УПРАВЛЕНИЯ МАГАЗИНОМ</p>
  </div>

  <!-- Instant 1-Click Login Button for convenience -->
  <div style="margin-bottom: 24px; padding: 18px; background: rgba(0, 240, 255, 0.08); border: 1px dashed var(--neon-cyan); border-radius: 8px; text-align: center;">
    <p style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); margin-bottom: 12px;">
      Вход для владельца магазина:
    </p>
    <a href="login.php?action=quick_login" class="hero-cta-btn" style="width: 100%; justify-content: center; padding: 14px; font-size: 12px; background: var(--neon-cyan); color: #000; box-shadow: var(--neon-cyan-glow);">
      ⚡ ВОЙТИ В АДМИНКУ В 1 КЛИК
    </a>
  </div>

  <div style="text-align: center; margin-bottom: 16px; font-family: var(--font-mono); font-size: 11px; color: var(--text-dim);">
    — ИЛИ ЧЕРЕЗ ЛОГИН И ПАРОЛЬ —
  </div>

  <?php if ($errorMsg): ?>
    <div style="margin-bottom: 16px; padding: 10px; background: rgba(244,63,94,0.15); border: 1px solid #f43f5e; border-radius: 4px; font-family: var(--font-mono); font-size: 12px; color: #f43f5e; text-align: center;">
      <?= htmlspecialchars($errorMsg) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="form-group">
      <label class="form-label">Логин администратора</label>
      <input type="text" name="username" class="form-input" value="bpm_admin" required>
    </div>

    <div class="form-group">
      <label class="form-label">Пароль</label>
      <input type="password" name="password" class="form-input" value="bpm2026!" required>
    </div>

    <button type="submit" class="btn-drawer-checkout" style="width: 100%; justify-content: center; margin-top: 10px;">
      ВОЙТИ
    </button>
  </form>

  <div style="text-align: center; margin-top: 24px;">
    <a href="../index.php" style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted);">← Вернуться на главную BPM</a>
  </div>
</div>

</body>
</html>
