<?php
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();
$cartCount = getCartCount();
$tgBotUsername = getSetting('telegram_bot_username', TG_BOT_USERNAME);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= SITE_NAME ?> — <?= SITE_TAGLINE ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" type="image/svg+xml" href="assets/images/logo/bpm-logo.svg">
</head>
<body>

<!-- Header / Navigation -->
<header class="site-header">
  <div class="container nav-wrap">
    <!-- Brand -->
    <a href="index.php" class="brand">
      <span class="brand-logo">BPM<span class="brand-dot">.</span></span>
    </a>

    <!-- Desktop Nav Links -->
    <nav>
      <ul class="nav-links">
        <li><a href="catalog.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'catalog.php' && empty($_GET['cat'])) ? 'active' : '' ?>">КОЛЛЕКЦИЯ</a></li>
        <li><a href="catalog.php?cat=clothes" class="nav-link <?= (isset($_GET['cat']) && $_GET['cat'] == 'clothes') ? 'active' : '' ?>">ОДЕЖДА</a></li>
        <li><a href="catalog.php?cat=prints" class="nav-link <?= (isset($_GET['cat']) && $_GET['cat'] == 'prints') ? 'active' : '' ?>">ПРИНТЫ</a></li>
        <li><a href="index.php#simulator" class="nav-link" style="color: var(--neon-lime);">ЛИПУЧКИ</a></li>
        <li><a href="payment_guide.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'payment_guide.php') ? 'active' : '' ?>" style="color: var(--neon-cyan);">ГАЙД ОПЛАТЫ</a></li>
      </ul>
    </nav>

    <!-- Nav Controls -->
    <div class="nav-actions">
      <!-- Profile / Login Button -->
      <?php if ($currentUser): ?>
        <a href="profile.php" class="user-nav-btn">
          <img src="<?= $currentUser['photo_url'] ?: 'assets/images/logo/bpm-logo.svg' ?>" class="user-nav-avatar" alt="Avatar">
          <span>@<?= htmlspecialchars($currentUser['username'] ?: $currentUser['first_name']) ?></span>
        </a>
      <?php else: ?>
        <a href="profile.php" class="user-nav-btn">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Вход</span>
        </a>
      <?php endif; ?>

      <!-- Cart Button -->
      <button class="cart-nav-btn" data-open-cart>
        <span>Корзина</span>
        <span class="cart-count-pill"><?= $cartCount ?></span>
      </button>
    </div>
  </div>
</header>

<!-- Slideout Cart Drawer -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay">
  <div class="cart-drawer">
    <div class="drawer-header">
      <span class="drawer-title">ВАША КОРЗИНА</span>
      <button class="drawer-close-btn" id="cartDrawerClose">&times;</button>
    </div>

    <div class="drawer-body">
      <!-- Items Container -->
      <div class="cart-items-list" id="cartItemsList">
        <!-- Rendered dynamically by main.js -->
      </div>

      <!-- Promo Code Engine Block -->
      <div class="promo-box">
        <label class="form-label" style="display:flex; justify-content:space-between;">
          <span>ПРОМОКОД НА СКИДКУ</span>
          <span style="color: var(--neon-cyan);">PRINT50 / COMBO30 / BPM10</span>
        </label>
        <div class="promo-input-row">
          <input type="text" class="promo-input" id="promoCodeInput" placeholder="Введите код...">
          <button class="promo-apply-btn" id="btnApplyPromo">Применить</button>
        </div>
        <div class="promo-alert" id="promoAlert"></div>
      </div>
    </div>

    <!-- Drawer Footer -->
    <div class="drawer-footer">
      <div class="drawer-totals">
        <div class="total-line">
          <span>Сумма товаров:</span>
          <span id="drawerSubtotal">0 ₽</span>
        </div>
        <div class="total-line" id="drawerDiscountRow" style="display:none; color: var(--neon-lime);">
          <span>Скидка по акции:</span>
          <span id="drawerDiscount">-0 ₽</span>
        </div>
        <div class="total-line final">
          <span>ИТОГО К ОПЛАТЕ:</span>
          <span class="val" id="drawerTotal">0 ₽</span>
        </div>
      </div>

      <button class="btn-drawer-checkout" id="btnDrawerCheckout">
        <span>Перейти к оформлению</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
  </div>
</div>
