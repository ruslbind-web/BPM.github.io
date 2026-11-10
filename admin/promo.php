<?php
require_once __DIR__ . '/auth_check.php';

$promoCodes = $pdo->query("SELECT * FROM promo_codes ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BPM Admin — Промокоды и условия акций</title>
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
      <li class="admin-nav-item active"><a href="promo.php">🎟 Промокоды и акции</a></li>
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
        <h1 class="admin-page-title">СИСТЕМА ПРОМОКОДОВ И УСЛОВИЙ</h1>
        <p style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); margin-top: 4px;">
          Создание промокодов со специальными условиями (2-й принт со скидкой N%, бандлы кофта + принт, % на чек)
        </p>
      </div>

      <div style="display: flex; gap: 12px;">
        <button class="btn-admin-primary" onclick="openPromoModal()">
          + СОЗДАТЬ ПРОМОКОД
        </button>
      </div>
    </div>

    <!-- Promo Codes Table -->
    <div class="admin-table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ПРОМОКОД</th>
            <th>ТИП УСЛОВИЯ</th>
            <th>СКИДКА</th>
            <th>МИН. СУММА</th>
            <th>ОПИСАНИЕ УСЛОВИЯ ДЛЯ КЛИЕНТА</th>
            <th>ИСПОЛЬЗОВАНИЙ</th>
            <th>СТАТУС</th>
            <th>ДЕЙСТВИЯ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($promoCodes as $promo): ?>
            <tr id="promo-row-<?= $promo['id'] ?>">
              <td>
                <span style="font-family: var(--font-mono); font-size: 14px; font-weight: 800; color: var(--neon-cyan); background: rgba(0,240,255,0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(0,240,255,0.3);">
                  <?= htmlspecialchars($promo['code']) ?>
                </span>
              </td>
              <td>
                <?php if ($promo['discount_type'] === 'second_print_percent'): ?>
                  <span style="color: var(--neon-lime); font-weight: 700;">★ 2-й принт со скидкой</span>
                <?php elseif ($promo['discount_type'] === 'bundle_hoodie_print'): ?>
                  <span style="color: var(--neon-violet); font-weight: 700;">⚡ Комплект (Кофта + Принт)</span>
                <?php elseif ($promo['discount_type'] === 'percent'): ?>
                  <span>% на весь заказ</span>
                <?php else: ?>
                  <span>Фиксированная сумма (₽)</span>
                <?php endif; ?>
              </td>
              <td style="font-weight: 800; color: #fff;">
                <?= ($promo['discount_type'] === 'fixed') ? formatPrice($promo['discount_value']) : ($promo['discount_value'] . '%') ?>
              </td>
              <td style="color: var(--text-muted);">
                <?= ($promo['min_order_amount'] > 0) ? formatPrice($promo['min_order_amount']) : 'Без минимума' ?>
              </td>
              <td style="font-size: 11px; color: var(--text-main); max-width: 280px;">
                <?= htmlspecialchars($promo['description']) ?>
              </td>
              <td>
                <span style="font-family: var(--font-mono); font-size: 11px;">
                  <?= $promo['used_count'] ?> / <?= $promo['max_uses'] ?>
                </span>
              </td>
              <td>
                <?php if ($promo['is_active']): ?>
                  <span style="color: #22c55e; font-weight: 700; font-size: 11px;">АКТИВЕН</span>
                <?php else: ?>
                  <span style="color: #f43f5e; font-size: 11px;">ОТКЛЮЧЕН</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="btn-admin-action" onclick='editPromo(<?= json_encode($promo, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Редакт.</button>
                  <button class="btn-admin-action btn-admin-danger" onclick="deletePromo(<?= $promo['id'] ?>)">Удалить</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Modal Promo Form -->
<div class="admin-modal-overlay" id="promoModal">
  <div class="admin-modal-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="font-family: var(--font-display); font-size: 18px; color: #fff;" id="promoModalTitle">СОЗДАТЬ ПРОМОКОД</h3>
      <button style="background: transparent; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;" onclick="closePromoModal()">&times;</button>
    </div>

    <form id="promoForm">
      <input type="hidden" name="id" id="promoId" value="0">

      <div class="admin-form-grid">
        <div class="form-group">
          <label class="form-label">Код промокода (латиница) *</label>
          <input type="text" name="code" id="promoCode" class="form-input" required placeholder="Например: PRINT50, COMBO30" style="text-transform: uppercase;">
        </div>

        <div class="form-group">
          <label class="form-label">Тип скидки и Условие *</label>
          <select name="discount_type" id="promoType" class="form-select" required onchange="handleTypeChange()">
            <option value="second_print_percent">★ Берёшь принт, второй со скидкой n%</option>
            <option value="bundle_hoodie_print">⚡ Скидка n% на принт при покупке кофты</option>
            <option value="percent">Процентная скидка на весь заказ (%)</option>
            <option value="fixed">Фиксированная скидка в рублях (₽)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" id="valLabel">Размер скидки (%) *</label>
          <input type="number" step="0.01" name="discount_value" id="promoVal" class="form-input" required placeholder="50">
        </div>

        <div class="form-group">
          <label class="form-label">Мин. сумма заказа (₽, 0 = без порога)</label>
          <input type="number" step="0.01" name="min_order_amount" id="promoMin" class="form-input" value="0">
        </div>

        <div class="form-group-full">
          <label class="form-label">Текст описания условия (показывается клиенту в корзине)</label>
          <input type="text" name="description" id="promoDesc" class="form-input" placeholder="Берёшь 2 принта — второй со скидкой 50%!">
        </div>

        <div class="form-group">
          <label class="form-label">Лимит использований</label>
          <input type="number" name="max_uses" id="promoMaxUses" class="form-input" value="500">
        </div>

        <div class="form-group">
          <label class="form-label">Действует до даты (необязательно)</label>
          <input type="date" name="expires_at" id="promoExpires" class="form-input">
        </div>

        <div class="form-group-full">
          <label style="display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 12px; color: #fff; cursor: pointer;">
            <input type="checkbox" name="is_active" id="promoActive" value="1" checked>
            Промокод активен и готов к применению
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 12px; margin-top: 24px;">
        <button type="submit" class="btn-admin-primary" style="flex-grow: 1; justify-content: center;">
          СОХРАНИТЬ ПРОМОКОД
        </button>
        <button type="button" class="btn-admin-action" onclick="closePromoModal()">
          Отмена
        </button>
      </div>

      <div id="promoFormMsg" style="margin-top: 12px; font-family: var(--font-mono); font-size: 12px; text-align: center;"></div>
    </form>
  </div>
</div>

<script>
function handleTypeChange() {
  const type = document.getElementById('promoType').value;
  const label = document.getElementById('valLabel');
  const desc = document.getElementById('promoDesc');

  if (type === 'fixed') {
    label.textContent = 'Размер скидки (₽) *';
  } else {
    label.textContent = 'Размер скидки (%) *';
  }

  if (type === 'second_print_percent' && !desc.value) {
    desc.value = 'Берёшь 2 принта — второй со скидкой 50%!';
  } else if (type === 'bundle_hoodie_print' && !desc.value) {
    desc.value = 'Скидка на принт при покупке любой кофты!';
  }
}

function openPromoModal() {
  document.getElementById('promoForm').reset();
  document.getElementById('promoId').value = 0;
  document.getElementById('promoActive').checked = true;
  document.getElementById('promoModalTitle').textContent = 'СОЗДАТЬ ПРОМОКОД С УСЛОВИЕМ';
  document.getElementById('promoFormMsg').textContent = '';
  document.getElementById('promoModal').classList.add('active');
  handleTypeChange();
}

function closePromoModal() {
  document.getElementById('promoModal').classList.remove('active');
}

function editPromo(p) {
  document.getElementById('promoId').value = p.id;
  document.getElementById('promoCode').value = p.code;
  document.getElementById('promoType').value = p.discount_type;
  document.getElementById('promoVal').value = p.discount_value;
  document.getElementById('promoMin').value = p.min_order_amount;
  document.getElementById('promoDesc').value = p.description || '';
  document.getElementById('promoMaxUses').value = p.max_uses;
  document.getElementById('promoExpires').value = p.expires_at || '';
  document.getElementById('promoActive').checked = (p.is_active == 1);

  document.getElementById('promoModalTitle').textContent = 'РЕДАКТИРОВАТЬ: ' + p.code;
  document.getElementById('promoFormMsg').textContent = '';
  document.getElementById('promoModal').classList.add('active');
  handleTypeChange();
}

document.getElementById('promoForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  fetch('../api/admin_api.php?action=save_promo', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    const msg = document.getElementById('promoFormMsg');
    if (data.success) {
      msg.style.color = 'var(--neon-lime)';
      msg.textContent = data.message;
      setTimeout(() => window.location.reload(), 800);
    } else {
      msg.style.color = '#f43f5e';
      msg.textContent = data.message;
    }
  });
});

function deletePromo(id) {
  if (!confirm('Вы уверены, что хотите удалить этот промокод?')) return;
  const formData = new FormData();
  formData.append('id', id);

  fetch('../api/admin_api.php?action=delete_promo', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const row = document.getElementById('promo-row-' + id);
      if (row) row.remove();
    } else {
      alert(data.message);
    }
  });
}
</script>

</body>
</html>
