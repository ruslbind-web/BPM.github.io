<?php
require_once __DIR__ . '/includes/functions.php';

// Handle Direct Simple POST Login (No Telegram needed!)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simple_login') {
    $name = trim($_POST['name'] ?? 'Покупатель BPM');
    $phone = trim($_POST['phone'] ?? '+7 (999) 144-00-14');
    $tg = trim($_POST['telegram'] ?? 'bpm_user');

    // Find or create in DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? OR username = ? LIMIT 1");
    $stmt->execute([$phone, ltrim($tg, '@')]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ($user['last_name'] ? ' ' . $user['last_name'] : '');
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
    } else {
        $ins = $pdo->prepare("INSERT INTO users (first_name, username, phone, city, role) VALUES (?, ?, ?, 'Москва', 'user')");
        $ins->execute([$name, ltrim($tg, '@'), $phone]);
        $newId = $pdo->lastInsertId();

        $_SESSION['user_id'] = $newId;
        $_SESSION['user_name'] = $name;
        $_SESSION['username'] = ltrim($tg, '@');
        $_SESSION['user_role'] = 'user';
    }

    header('Location: profile.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$user = getCurrentUser();
$orders = [];
$likedProducts = [];
$recentProducts = [];

if ($user) {
    // Fetch orders
    $oStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? OR customer_telegram = ? ORDER BY id DESC");
    $oStmt->execute([$user['id'], '@' . $user['username']]);
    $orders = $oStmt->fetchAll();

    $likedProducts = getUserLikedProducts();
    $recentProducts = getRecentlyViewedProducts(8);
} else {
    $likedProducts = getUserLikedProducts();
    $recentProducts = getRecentlyViewedProducts(8);
}
?>

<div class="profile-section">
  <div class="container">
    <?php if ($user): ?>
      <!-- Logged In User Profile -->
      <div class="profile-card">
        <div class="profile-header-flex">
          <div class="user-badge-group">
            <img src="<?= $user['photo_url'] ?: 'assets/images/logo/bpm-logo.svg' ?>" class="profile-avatar-large" alt="Avatar">
            <div>
              <h1 class="user-name-title"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
              <div class="user-tg-handle">
                <span>Профиль: @<?= htmlspecialchars($user['username'] ?: 'resident') ?></span>
                <span style="color: var(--text-dim); margin-left: 10px;">ID: #<?= $user['id'] ?></span>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <?php if (isAdmin()): ?>
              <a href="admin/index.php" class="btn-card-action" style="background: var(--neon-cyan); color: #000; font-weight: 800;">
                ⚙️ Панель Администратора
              </a>
            <?php endif; ?>
            <button class="hero-cta-btn" onclick="logoutUser()" style="padding: 8px 16px; font-size: 11px; background: rgba(244,63,94,0.15); color: #f43f5e; border: 1px solid #f43f5e; box-shadow: none;">
              Выйти
            </button>
          </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="profile-tabs-nav">
          <button class="profile-tab-btn active" data-tab="orders">ЗАКАЗЫ (<?= count($orders) ?>)</button>
          <button class="profile-tab-btn" data-tab="likes">ИЗБРАННОЕ (<?= count($likedProducts) ?>)</button>
          <button class="profile-tab-btn" data-tab="recent">ПРОСМОТРЕННЫЕ (<?= count($recentProducts) ?>)</button>
          <button class="profile-tab-btn" data-tab="settings">ДАННЫЕ ДОСТАВКИ</button>
        </div>

        <!-- Tab 1: Orders -->
        <div class="profile-tab-content" id="tab-orders">
          <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
              <p style="font-family: var(--font-mono); font-size: 13px;">У ВАС ПОКА НЕТ ОФОРМЛЕННЫХ ЗАКАЗОВ</p>
              <a href="catalog.php" class="hero-cta-btn" style="margin-top: 14px; font-size: 11px; padding: 10px 20px;">ПЕРЕЙТИ В КАТАЛОГ</a>
            </div>
          <?php else: ?>
            <?php foreach ($orders as $order): 
              $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
              $itemsStmt->execute([$order['id']]);
              $orderItems = $itemsStmt->fetchAll();
            ?>
              <div class="order-history-card">
                <div class="order-head-row">
                  <div>
                    <span class="order-num"><?= htmlspecialchars($order['order_number']) ?></span>
                    <span style="font-family: var(--font-mono); font-size: 11px; color: var(--text-dim); margin-left: 8px;">
                      <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                    </span>
                  </div>
                  <div>
                    <span class="order-status-badge status-<?= $order['status'] ?>">
                      <?= ($order['status'] === 'new') ? 'НОВЫЙ' : 
                          (($order['status'] === 'processing') ? 'В ОБРАБОТКЕ' : 
                          (($order['status'] === 'assembled') ? 'СОБРАН' : 
                          (($order['status'] === 'shipped') ? 'ОТПРАВЛЕН' : 
                          (($order['status'] === 'delivered') ? 'ДОСТАВЛЕН' : 'ОТМЕНЕН')))) ?>
                    </span>
                  </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                  <?php foreach ($orderItems as $oi): ?>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--text-muted);">
                      <span><?= htmlspecialchars($oi['product_title']) ?> (<?= htmlspecialchars($oi['selected_size']) ?>) × <?= $oi['quantity'] ?></span>
                      <span style="font-family: var(--font-mono); color: #fff;"><?= formatPrice($oi['total']) ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: baseline; padding-top: 8px; border-top: 1px solid var(--border-subtle); font-family: var(--font-mono); font-size: 12px;">
                  <div style="color: var(--text-dim); font-size: 11px;">
                    <?= htmlspecialchars($order['delivery_type']) ?>
                    <?php if ($order['promo_code']): ?>
                      <span style="color: var(--neon-cyan); margin-left: 6px;">• Промокод: <?= htmlspecialchars($order['promo_code']) ?></span>
                    <?php endif; ?>
                  </div>
                  <div>
                    <span style="font-size: 16px; font-weight: 900; color: var(--neon-lime);"><?= formatPrice($order['total_price']) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Tab 2: Likes -->
        <div class="profile-tab-content" id="tab-likes" style="display: none;">
          <?php if (empty($likedProducts)): ?>
            <div style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
              <p style="font-family: var(--font-mono); font-size: 13px;">В ИЗБРАННОМ ПОКА НЕТ ТОВАРОВ</p>
              <p style="font-size: 12px; margin-top: 6px;">Нажмите на сердечко в каталоге, чтобы сохранить худи или принт</p>
            </div>
          <?php else: ?>
            <div class="products-grid">
              <?php foreach ($likedProducts as $item): ?>
                <div class="product-card">
                  <div class="product-card-top">
                    <a href="product.php?id=<?= $item['id'] ?>">
                      <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-card-img" />
                    </a>
                  </div>
                  <div class="product-card-content">
                    <h3 class="product-title"><?= htmlspecialchars($item['title']) ?></h3>
                    <div class="product-card-bottom">
                      <span class="price-main"><?= formatPrice($item['price']) ?></span>
                      <button onclick="quickAddToCart(<?= $item['id'] ?>, 'L', 1)" class="btn-card-action">+ В КОРЗИНУ</button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tab 3: Recently Viewed -->
        <div class="profile-tab-content" id="tab-recent" style="display: none;">
          <?php if (empty($recentProducts)): ?>
            <div style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
              <p style="font-family: var(--font-mono); font-size: 13px;">ИСТОРИЯ ПРОСМОТРОВ ПУСТА</p>
            </div>
          <?php else: ?>
            <div class="products-grid">
              <?php foreach ($recentProducts as $item): ?>
                <div class="product-card">
                  <div class="product-card-top">
                    <a href="product.php?id=<?= $item['id'] ?>">
                      <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-card-img" />
                    </a>
                  </div>
                  <div class="product-card-content">
                    <h3 class="product-title"><?= htmlspecialchars($item['title']) ?></h3>
                    <div class="product-card-bottom">
                      <span class="price-main"><?= formatPrice($item['price']) ?></span>
                      <a href="product.php?id=<?= $item['id'] ?>" class="btn-card-action">СМОТРЕТЬ</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Tab 4: Delivery Settings -->
        <div class="profile-tab-content" id="tab-settings" style="display: none;">
          <form id="profileSettingsForm" style="max-width: 600px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div class="form-group">
                <label class="form-label">Имя</label>
                <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name']) ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Фамилия</label>
                <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Телефон для доставки</label>
              <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7 (999) 000-00-00">
            </div>
            <div class="form-group">
              <label class="form-label">Город</label>
              <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="Москва">
            </div>
            <div class="form-group">
              <label class="form-label">Адрес доставки или ПВЗ СДЭК</label>
              <textarea name="address" class="form-textarea" rows="3" placeholder="Улица, дом или пункт СДЭК"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="hero-cta-btn" style="padding: 12px 24px; font-size: 11px;">СОХРАНИТЬ ДАННЫЕ</button>
            <div id="profileSaveMsg" style="margin-top: 10px; font-family: var(--font-mono); font-size: 11px; color: var(--neon-lime);"></div>
          </form>
        </div>
      </div>

    <?php else: ?>

      <!-- Guest Screen: Simple Direct Login + Telegram Option -->
      <div style="max-width: 600px; margin: 0 auto;">
        <div class="profile-card">
          <div style="text-align: center; margin-bottom: 24px;">
            <div class="section-pill" style="border-color: var(--neon-cyan); color: var(--neon-cyan); background: rgba(0, 240, 255, 0.1);">ЛИЧНЫЙ КАБИНЕТ</div>
            <h1 class="section-title" style="font-size: 26px;">ВХОД В ПРОФИЛЬ</h1>
            <p class="section-desc" style="font-size: 13px;">
              Войдите для сохранения истории заказов, корзины и избранного
            </p>
          </div>

          <!-- 1. Simple Regular Login Form (As requested by user!) -->
          <form method="POST" action="profile.php" style="margin-bottom: 28px; padding: 20px; background: rgba(14, 14, 24, 0.85); border: 1px solid var(--border-neon); border-radius: var(--radius-sm);">
            <input type="hidden" name="action" value="simple_login">
            <div style="font-family: var(--font-mono); font-size: 11px; font-weight: 800; color: var(--neon-cyan); margin-bottom: 14px;">
              ⚡ БЫСТРЫЙ ВХОД (БЕЗ ПАРОЛЯ)
            </div>

            <div class="form-group">
              <label class="form-label">Ваше Имя</label>
              <input type="text" name="name" class="form-input" required value="Покупатель BPM" placeholder="Ваше имя">
            </div>

            <div class="form-group">
              <label class="form-label">Номер телефона</label>
              <input type="tel" name="phone" class="form-input" required value="+7 (999) 144-00-14" placeholder="+7 (___) ___-__-__">
            </div>

            <div class="form-group">
              <label class="form-label">Telegram Никнейм (необязательно)</label>
              <input type="text" name="telegram" class="form-input" value="@bpm_client" placeholder="@username">
            </div>

            <button type="submit" class="btn-drawer-checkout" style="width: 100%; justify-content: center;">
              ВОЙТИ В КАБИНЕТ
            </button>
          </form>

          <!-- 2. Telegram Widget Option -->
          <div style="text-align: center; padding-top: 16px; border-top: 1px solid var(--border-subtle);">
            <p style="font-family: var(--font-mono); font-size: 11px; color: var(--text-dim); margin-bottom: 12px;">
              ИЛИ АВТОРИЗАЦИЯ ЧЕРЕЗ TELEGRAM ВИДЖЕТ:
            </p>
            <div style="min-height: 44px; display: flex; justify-content: center;">
              <script async src="https://telegram.org/js/telegram-widget.js?22" 
                      data-telegram-login="<?= htmlspecialchars($tgBotUsername) ?>" 
                      data-size="large" 
                      data-userpic="true"
                      data-auth-url="api/auth.php?action=telegram_callback" 
                      data-request-access="write"></script>
            </div>
          </div>
        </div>

        <!-- Quick Admin Link -->
        <div style="text-align: center; margin-top: 16px;">
          <a href="admin/login.php?action=quick_login" style="font-family: var(--font-mono); font-size: 12px; color: var(--neon-lime);">
            ⚡ Вход в панель администратора (1 клик) →
          </a>
        </div>
      </div>

    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabBtns = document.querySelectorAll('.profile-tab-btn');
  const tabContents = document.querySelectorAll('.profile-tab-content');

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const tabId = btn.dataset.tab;
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      tabContents.forEach(c => {
        if (c.id === `tab-${tabId}`) {
          c.style.display = 'block';
        } else {
          c.style.display = 'none';
        }
      });
    });
  });

  const settingsForm = document.getElementById('profileSettingsForm');
  if (settingsForm) {
    settingsForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(settingsForm);
      fetch('api/auth.php?action=update_profile', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(d => {
        const msg = document.getElementById('profileSaveMsg');
        if (msg) msg.textContent = d.message || 'Сохранено!';
      });
    });
  }
});

function logoutUser() {
  fetch('api/auth.php?action=logout')
    .then(r => r.json())
    .then(() => window.location.href = 'index.php');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
