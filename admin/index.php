<?php
require_once __DIR__ . '/auth_check.php';

// Stats
$ordersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
$clothesCount = $pdo->query("SELECT COUNT(*) FROM products WHERE category_slug = 'clothes'")->fetchColumn();
$printsCount = $pdo->query("SELECT COUNT(*) FROM products WHERE category_slug = 'prints'")->fetchColumn();
$promoCount = $pdo->query("SELECT COUNT(*) FROM promo_codes WHERE is_active = 1")->fetchColumn();

// Latest orders
$latestOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BPM Admin — Панель управления</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <span class="brand-logo" style="font-size: 24px;">BPM<span class="brand-dot">.</span></span>
      <span style="font-family: var(--font-mono); font-size: 10px; color: var(--neon-cyan); border: 1px solid var(--neon-cyan); padding: 2px 6px; border-radius: 4px;">ADMIN</span>
    </div>

    <ul class="admin-nav-menu">
      <li class="admin-nav-item active"><a href="index.php">📊 Дашборд</a></li>
      <li class="admin-nav-item"><a href="products.php">👕 Товары (Одежда и Принты)</a></li>
      <li class="admin-nav-item"><a href="promo.php">🎟 Промокоды и акции</a></li>
      <li class="admin-nav-item"><a href="orders.php">📦 Заказы</a></li>
    </ul>

    <div style="border-top: 1px solid var(--admin-border); padding-top: 16px;">
      <a href="../index.php" target="_blank" style="display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); margin-bottom: 10px;">
        ↗ Перейти на сайт
      </a>
      <a href="logout.php" style="font-family: var(--font-mono); font-size: 11px; color: #f43f5e;">
        Выйти из админки
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="admin-page-title">СИСТЕМА УПРАВЛЕНИЯ BPM</h1>
        <p style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); margin-top: 4px;">
          База данных MySQL: <strong>bpm</strong> (OpenServer) • Статус: <strong>ONLINE</strong>
        </p>
      </div>

      <div style="display: flex; gap: 12px;">
        <a href="products.php?action=new" class="btn-admin-primary">
          + Добавить товар
        </a>
        <a href="promo.php?action=new" class="btn-admin-primary" style="background: var(--neon-violet); color: #fff;">
          + Создать промокод
        </a>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="admin-stats-grid">
      <div class="admin-stat-card">
        <span class="admin-stat-label">Оформлено заказов</span>
        <span class="admin-stat-val" style="color: var(--neon-cyan);"><?= $ordersCount ?></span>
      </div>
      <div class="admin-stat-card">
        <span class="admin-stat-label">Выручка по заказам</span>
        <span class="admin-stat-val" style="color: var(--neon-lime);"><?= formatPrice($revenue) ?></span>
      </div>
      <div class="admin-stat-card">
        <span class="admin-stat-label">Каталог (Кофты / Принты)</span>
        <span class="admin-stat-val"><?= $clothesCount ?> / <?= $printsCount ?></span>
      </div>
      <div class="admin-stat-card">
        <span class="admin-stat-label">Активные промокоды</span>
        <span class="admin-stat-val" style="color: var(--neon-violet);"><?= $promoCount ?></span>
      </div>
    </div>

    <!-- Latest Orders Table -->
    <div class="admin-table-card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-family: var(--font-display); font-size: 16px; color: #fff;">ПОСЛЕДНИЕ ЗАКАЗЫ</h3>
        <a href="orders.php" style="font-family: var(--font-mono); font-size: 11px; color: var(--neon-cyan);">Смотреть все заказы →</a>
      </div>

      <?php if (empty($latestOrders)): ?>
        <p style="color: var(--text-muted); font-size: 13px; font-family: var(--font-mono);">Заказов пока нет</p>
      <?php else: ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th>№ ЗАКАЗА</th>
              <th>ПОКУПАТЕЛЬ</th>
              <th>TELEGRAM</th>
              <th>ГОРОД</th>
              <th>СУММА</th>
              <th>ПРОМОКОД</th>
              <th>СТАТУС</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($latestOrders as $ord): ?>
              <tr>
                <td style="font-weight: 800; color: #fff;"><?= htmlspecialchars($ord['order_number']) ?></td>
                <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                <td style="color: var(--neon-cyan);"><?= htmlspecialchars($ord['customer_telegram'] ?: '—') ?></td>
                <td><?= htmlspecialchars($ord['customer_city']) ?></td>
                <td style="font-weight: 800; color: var(--neon-lime);"><?= formatPrice($ord['total_price']) ?></td>
                <td><?= htmlspecialchars($ord['promo_code'] ?: '—') ?></td>
                <td>
                  <span class="order-status-badge status-<?= $ord['status'] ?>">
                    <?= $ord['status'] ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</div>

</body>
</html>
