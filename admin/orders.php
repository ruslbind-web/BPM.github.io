<?php
require_once __DIR__ . '/auth_check.php';

$statusFilter = $_GET['status'] ?? '';
$query = "SELECT * FROM orders WHERE 1=1";
$params = [];
if (!empty($statusFilter)) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}
$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BPM Admin — Заказы покупателей</title>
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
      <li class="admin-nav-item"><a href="index.php">📊 Дашборд</a></li>
      <li class="admin-nav-item"><a href="products.php">👕 Товары (Одежда и Принты)</a></li>
      <li class="admin-nav-item"><a href="promo.php">🎟 Промокоды и акции</a></li>
      <li class="admin-nav-item active"><a href="orders.php">📦 Заказы</a></li>
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
        <h1 class="admin-page-title">ЗАКАЗЫ КЛИЕНТОВ</h1>
        <p style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); margin-top: 4px;">
          Обработка заказов, контакты покупателей и управление статусами доставки
        </p>
      </div>
    </div>

    <!-- Status Filters -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
      <a href="orders.php" class="btn-admin-action <?= empty($statusFilter) ? 'active' : '' ?>" style="<?= empty($statusFilter) ? 'background: var(--neon-cyan); color: #000; font-weight: 800;' : '' ?>">Все заказы (<?= count($orders) ?>)</a>
      <a href="orders.php?status=new" class="btn-admin-action <?= ($statusFilter === 'new') ? 'active' : '' ?>">Новые</a>
      <a href="orders.php?status=processing" class="btn-admin-action <?= ($statusFilter === 'processing') ? 'active' : '' ?>">В обработке</a>
      <a href="orders.php?status=assembled" class="btn-admin-action <?= ($statusFilter === 'assembled') ? 'active' : '' ?>">Собраны</a>
      <a href="orders.php?status=shipped" class="btn-admin-action <?= ($statusFilter === 'shipped') ? 'active' : '' ?>">Отправлены</a>
      <a href="orders.php?status=delivered" class="btn-admin-action <?= ($statusFilter === 'delivered') ? 'active' : '' ?>">Доставлены</a>
    </div>

    <!-- Orders Cards List -->
    <?php if (empty($orders)): ?>
      <div class="admin-table-card" style="text-align: center; padding: 60px;">
        <p style="font-family: var(--font-mono); color: var(--text-muted);">Заказов с таким статусом не найдено</p>
      </div>
    <?php else: ?>
      <?php foreach ($orders as $ord): 
        $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$ord['id']]);
        $items = $itemsStmt->fetchAll();
      ?>
        <div class="admin-table-card" style="margin-bottom: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--admin-border);">
            <div>
              <span style="font-family: var(--font-mono); font-size: 16px; font-weight: 900; color: #fff;">
                <?= htmlspecialchars($ord['order_number']) ?>
              </span>
              <span style="font-family: var(--font-mono); font-size: 12px; color: var(--text-dim); margin-left: 12px;">
                <?= date('d.m.Y в H:i', strtotime($ord['created_at'])) ?>
              </span>
            </div>

            <!-- Status Changer Dropdown -->
            <div style="display: flex; align-items: center; gap: 10px;">
              <span style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted);">СТАТУС:</span>
              <select class="form-select" style="padding: 6px 12px; font-size: 11px; width: auto;" onchange="updateStatus(<?= $ord['id'] ?>, this.value)">
                <option value="new" <?= ($ord['status'] === 'new') ? 'selected' : '' ?>>Новый</option>
                <option value="processing" <?= ($ord['status'] === 'processing') ? 'selected' : '' ?>>В обработке</option>
                <option value="assembled" <?= ($ord['status'] === 'assembled') ? 'selected' : '' ?>>Собран</option>
                <option value="shipped" <?= ($ord['status'] === 'shipped') ? 'selected' : '' ?>>Отправлен</option>
                <option value="delivered" <?= ($ord['status'] === 'delivered') ? 'selected' : '' ?>>Доставлен</option>
                <option value="cancelled" <?= ($ord['status'] === 'cancelled') ? 'selected' : '' ?>>Отменен</option>
              </select>
            </div>
          </div>

          <!-- Grid: Customer Info & Items -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Customer Column -->
            <div style="font-family: var(--font-mono); font-size: 12px; line-height: 1.8;">
              <div style="color: var(--neon-cyan); font-weight: 800; margin-bottom: 4px;">ДАННЫЕ ПОКУПАТЕЛЯ:</div>
              <div>Клиент: <strong style="color: #fff;"><?= htmlspecialchars($ord['customer_name']) ?></strong></div>
              <div>Телефон: <strong style="color: #fff;"><?= htmlspecialchars($ord['customer_phone']) ?></strong></div>
              <div>Telegram: <strong style="color: var(--neon-cyan);"><?= htmlspecialchars($ord['customer_telegram'] ?: 'Не указан') ?></strong></div>
              <div>Город: <?= htmlspecialchars($ord['customer_city']) ?></div>
              <div>Адрес / ПВЗ: <?= htmlspecialchars($ord['customer_address']) ?></div>
              <div>Служба: <?= htmlspecialchars($ord['delivery_type']) ?></div>
              <div>Оплата: <?= htmlspecialchars($ord['payment_method']) ?></div>
              <?php if (!empty($ord['notes'])): ?>
                <div style="color: var(--neon-lime);">Комментарий: <?= htmlspecialchars($ord['notes']) ?></div>
              <?php endif; ?>
            </div>

            <!-- Items Column -->
            <div>
              <div style="font-family: var(--font-mono); font-size: 12px; color: var(--neon-cyan); font-weight: 800; margin-bottom: 8px;">
                СОСТАВ ЗАКАЗА:
              </div>
              <div style="display: flex; flex-direction: column; gap: 8px;">
                <?php foreach ($items as $item): ?>
                  <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 4px;">
                    <div>
                      <strong style="color: #fff;"><?= htmlspecialchars($item['product_title']) ?></strong>
                      <span style="color: var(--text-dim); margin-left: 6px;">(<?= htmlspecialchars($item['selected_size']) ?>) × <?= $item['quantity'] ?> шт.</span>
                    </div>
                    <span style="font-family: var(--font-mono); font-weight: 800; color: var(--neon-lime);"><?= formatPrice($item['total']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>

              <!-- Price Breakdown -->
              <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: baseline; font-family: var(--font-mono);">
                <div style="font-size: 11px; color: var(--text-dim);">
                  <?php if ($ord['promo_code']): ?>
                    Промокод: <strong style="color: var(--neon-cyan);"><?= htmlspecialchars($ord['promo_code']) ?></strong> (-<?= formatPrice($ord['discount_amount']) ?>)
                  <?php endif; ?>
                </div>
                <div>
                  <span style="font-size: 11px; color: var(--text-muted); margin-right: 8px;">ИТОГО:</span>
                  <span style="font-size: 18px; font-weight: 900; color: var(--neon-lime);"><?= formatPrice($ord['total_price']) ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<script>
function updateStatus(orderId, newStatus) {
  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('status', newStatus);

  fetch('../api/admin_api.php?action=update_order_status', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(d => {
    if (!d.success) alert(d.message);
  });
}
</script>

</body>
</html>
