<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Col 1: Brand (No acronym expansion) -->
      <div>
        <div class="brand">
          <span class="brand-logo">BPM<span class="brand-dot">.</span></span>
        </div>
        <p class="footer-brand-desc">
          UNDERGROUND SOUND SYSTEM GARMENTS.<br>
          Концептуальная клубная одежда со сменными велкографическими принтами. Премиальный плотный хлопок 480–520 GSM.
        </p>
      </div>

      <!-- Col 2: Catalog -->
      <div>
        <h4 class="footer-col-title">КАТАЛОГ</h4>
        <ul class="footer-links-list">
          <li><a href="catalog.php">Вся коллекция</a></li>
          <li><a href="catalog.php?cat=clothes">Худи и свитшоты</a></li>
          <li><a href="catalog.php?cat=prints">Сменные принты (Велкро)</a></li>
          <li><a href="index.php#simulator">Конструктор липучек</a></li>
        </ul>
      </div>

      <!-- Col 3: Customer & Info -->
      <div>
        <h4 class="footer-col-title">ИНФОРМАЦИЯ</h4>
        <ul class="footer-links-list">
          <li><a href="profile.php">Личный кабинет</a></li>
          <li><a href="payment_guide.php" style="color: var(--neon-cyan);">Интеграция оплаты</a></li>
          <li><a href="index.php#concept">О технологии Velcro</a></li>
          <li><a href="admin/login.php?action=quick_login" style="color: var(--neon-lime); font-weight: 800;">⚡ Панель администратора</a></li>
        </ul>
      </div>

      <!-- Col 4: Contacts -->
      <div>
        <h4 class="footer-col-title">СВЯЗЬ</h4>
        <ul class="footer-links-list">
          <li><a href="https://t.me/bpm_support" target="_blank">Telegram: @bpm_support</a></li>
          <li><a href="#">Доставка: СДЭК по всей РФ</a></li>
          <li><a href="admin/login.php" style="color: var(--text-dim);">Вход для персонала</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>&copy; <?= date('Y') ?> BPM GARMENTS. ВСЕ ПРАВА ЗАЩИЩЕНЫ.</div>
      <div style="display: flex; gap: 16px;">
        <span style="color: var(--neon-cyan);">● VELCRO MODULAR TECH</span>
      </div>
    </div>
  </div>
</footer>

<!-- ==========================================================================
     MOBILE BOTTOM NAVIGATION BAR (FIXED BOTTOM SWITCHER)
     ========================================================================== -->
<nav class="mobile-bottom-nav" aria-label="Мобильное меню">
  <div class="mobile-bottom-nav-inner">
    <!-- Home -->
    <a href="index.php" class="mob-nav-item <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      <span>Главная</span>
    </a>

    <!-- Catalog -->
    <a href="catalog.php" class="mob-nav-item <?= (basename($_SERVER['PHP_SELF']) == 'catalog.php') ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/>
        <rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/>
      </svg>
      <span>Каталог</span>
    </a>

    <!-- Velcro Simulator -->
    <a href="index.php#simulator" class="mob-nav-item" style="color: var(--neon-lime);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
      </svg>
      <span>Липучки</span>
    </a>

    <!-- Cart Drawer Trigger -->
    <button class="mob-nav-item" data-open-cart>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"/>
        <circle cx="20" cy="21" r="1"/>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
      </svg>
      <span class="mob-cart-badge cart-count-pill"><?= getCartCount() ?></span>
      <span>Корзина</span>
    </button>

    <!-- Profile -->
    <a href="profile.php" class="mob-nav-item <?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span>Профиль</span>
    </a>
  </div>
</nav>

<!-- Scripts -->
<script src="assets/js/main.js"></script>
<script src="assets/js/velcro-sim.js"></script>
</body>
</html>
