<?php
require_once __DIR__ . '/auth_check.php';

$cat = $_GET['cat'] ?? '';
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
if ($cat === 'clothes' || $cat === 'prints') {
    $query .= " AND category_slug = ?";
    $params[] = $cat;
}
$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BPM Admin — Товары и карточки</title>
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
      <li class="admin-nav-item active"><a href="products.php">👕 Товары (Одежда и Принты)</a></li>
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
        <h1 class="admin-page-title">УПРАВЛЕНИЕ КАРТОЧКАМИ ТОВАРОВ</h1>
        <p style="font-family: var(--font-mono); font-size: 12px; color: var(--text-muted); margin-top: 4px;">
          Добавление и редактирование одежды со сменными липучками и патчей
        </p>
      </div>

      <div style="display: flex; gap: 12px;">
        <button class="btn-admin-primary" onclick="openProductModal()">
          + ДОБАВИТЬ КАРТОЧКУ
        </button>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px;">
      <a href="products.php" class="btn-admin-action <?= empty($cat) ? 'active' : '' ?>" style="<?= empty($cat) ? 'background: var(--neon-cyan); color: #000; font-weight: 800;' : '' ?>">Все товары (<?= count($products) ?>)</a>
      <a href="products.php?cat=clothes" class="btn-admin-action <?= ($cat === 'clothes') ? 'active' : '' ?>" style="<?= ($cat === 'clothes') ? 'background: var(--neon-cyan); color: #000; font-weight: 800;' : '' ?>">Одежда (Худи / Свитшоты)</a>
      <a href="products.php?cat=prints" class="btn-admin-action <?= ($cat === 'prints') ? 'active' : '' ?>" style="<?= ($cat === 'prints') ? 'background: var(--neon-cyan); color: #000; font-weight: 800;' : '' ?>">Сменные принты (Велкро)</a>
    </div>

    <!-- Products Table -->
    <div class="admin-table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ФОТО</th>
            <th>НАЗВАНИЕ</th>
            <th>РАЗДЕЛ</th>
            <th>ЦЕНА</th>
            <th>БЕЙДЖ</th>
            <th>МАТЕРИАЛ / ЗОНА</th>
            <th>ОСТАТОК</th>
            <th>ДЕЙСТВИЯ</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr id="prod-row-<?= $p['id'] ?>">
              <td>
                <img src="../<?= $p['image'] ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 4px; background: #000;" onerror="this.src='../assets/images/logo/bpm-logo.svg'">
              </td>
              <td>
                <div style="font-weight: 800; color: #fff;"><?= htmlspecialchars($p['title']) ?></div>
                <div style="color: var(--text-dim); font-size: 11px; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($p['short_desc']) ?></div>
              </td>
              <td>
                <span style="font-size: 11px; font-weight: 700; color: <?= ($p['category_slug'] === 'prints') ? 'var(--neon-violet)' : 'var(--neon-cyan)' ?>;">
                  <?= ($p['category_slug'] === 'prints') ? 'ПРИНТ ВЕЛКРО' : 'ОДЕЖДА' ?>
                </span>
              </td>
              <td>
                <span style="font-weight: 800; color: var(--neon-lime);"><?= formatPrice($p['price']) ?></span>
                <?php if ($p['old_price']): ?>
                  <span style="display: block; font-size: 10px; color: var(--text-dim); text-decoration: line-through;"><?= formatPrice($p['old_price']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($p['badge']): ?>
                  <span class="product-badge cyan" style="position: static; font-size: 9px;"><?= htmlspecialchars($p['badge']) ?></span>
                <?php else: ?>
                  <span style="color: var(--text-dim);">—</span>
                <?php endif; ?>
              </td>
              <td style="font-size: 11px; color: var(--text-muted);">
                <?= htmlspecialchars($p['material']) ?><br>
                <span style="color: var(--text-dim);"><?= htmlspecialchars($p['velcro_zone']) ?></span>
              </td>
              <td>
                <span style="font-weight: 700; color: <?= ($p['stock'] < 10) ? '#f43f5e' : '#fff' ?>;"><?= $p['stock'] ?> шт.</span>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <button class="btn-admin-action" onclick='editProduct(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Редакт.</button>
                  <button class="btn-admin-action btn-admin-danger" onclick="deleteProduct(<?= $p['id'] ?>)">Удалить</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- Modal Product Form -->
<div class="admin-modal-overlay" id="productModal">
  <div class="admin-modal-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="font-family: var(--font-display); font-size: 18px; color: #fff;" id="productModalTitle">ДОБАВИТЬ КАРТОЧКУ ТОВАРА</h3>
      <button style="background: transparent; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;" onclick="closeProductModal()">&times;</button>
    </div>

    <form id="productForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="prodId" value="0">

      <div class="admin-form-grid">
        <div class="form-group">
          <label class="form-label">Тип товара / Раздел *</label>
          <select name="category_slug" id="prodCatSlug" class="form-select" required>
            <option value="clothes">Одежда (Худи, Свитшоты, Кофты с велкро)</option>
            <option value="prints">Сменный принт (Патч на липучке)</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Название карточки *</label>
          <input type="text" name="title" id="prodTitle" class="form-input" required placeholder="Например: PSY-MATRIX HOODIE">
        </div>

        <div class="form-group">
          <label class="form-label">Цена (₽) *</label>
          <input type="number" step="0.01" name="price" id="prodPrice" class="form-input" required placeholder="8900">
        </div>

        <div class="form-group">
          <label class="form-label">Старая цена (₽ для скидки)</label>
          <input type="number" step="0.01" name="old_price" id="prodOldPrice" class="form-input" placeholder="10500">
        </div>

        <div class="form-group">
          <label class="form-label">Бейдж / Лейбл</label>
          <input type="text" name="badge" id="prodBadge" class="form-input" placeholder="HOT DROP, LIMITED 50, ХИТ">
        </div>

        <div class="form-group">
          <label class="form-label">Остаток на складе (шт.)</label>
          <input type="number" name="stock" id="prodStock" class="form-input" value="50">
        </div>

        <div class="form-group">
          <label class="form-label">Материал & Плотность</label>
          <input type="text" name="material" id="prodMaterial" class="form-input" value="500 GSM Премиальный хлопок">
        </div>

        <div class="form-group">
          <label class="form-label">Размещение липучки (Велкро)</label>
          <input type="text" name="velcro_zone" id="prodVelcro" class="form-input" value="Грудь (20x15 см)">
        </div>

        <div class="form-group-full">
          <label class="form-label">Доступные размеры (через запятую)</label>
          <input type="text" name="sizes" id="prodSizes" class="form-input" value="S, M, L, XL, OVERSIZE">
        </div>

        <div class="form-group-full">
          <label class="form-label">Краткое описание (в карточке каталога)</label>
          <input type="text" name="short_desc" id="prodShortDesc" class="form-input" placeholder="Плотный оверсайз со сменной велкро-базой...">
        </div>

        <div class="form-group-full">
          <label class="form-label">Полное описание и свойства</label>
          <textarea name="description" id="prodDescription" class="form-textarea" rows="4" placeholder="Подробное описание футера, швов, инструкции по стирке..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Путь к изображению или URL</label>
          <input type="text" name="image" id="prodImage" class="form-input" value="assets/images/products/hoodie-psy.svg">
        </div>

        <div class="form-group">
          <label class="form-label">Или загрузить файл с компьютера</label>
          <input type="file" name="image_file" class="form-input" accept="image/*">
        </div>

        <div class="form-group-full">
          <label style="display: flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-size: 12px; color: #fff; cursor: pointer;">
            <input type="checkbox" name="is_featured" id="prodFeatured" value="1" checked>
            Показывать на главной в блоке «Дроп»
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 12px; margin-top: 24px;">
        <button type="submit" class="btn-admin-primary" style="flex-grow: 1; justify-content: center;">
          СОХРАНИТЬ КАРТОЧКУ
        </button>
        <button type="button" class="btn-admin-action" onclick="closeProductModal()">
          Отмена
        </button>
      </div>

      <div id="productFormMsg" style="margin-top: 12px; font-family: var(--font-mono); font-size: 12px; text-align: center;"></div>
    </form>
  </div>
</div>

<script>
function openProductModal() {
  document.getElementById('productForm').reset();
  document.getElementById('prodId').value = 0;
  document.getElementById('productModalTitle').textContent = 'ДОБАВИТЬ КАРТОЧКУ ТОВАРА';
  document.getElementById('productFormMsg').textContent = '';
  document.getElementById('productModal').classList.add('active');
}

function closeProductModal() {
  document.getElementById('productModal').classList.remove('active');
}

function editProduct(p) {
  document.getElementById('prodId').value = p.id;
  document.getElementById('prodCatSlug').value = p.category_slug;
  document.getElementById('prodTitle').value = p.title;
  document.getElementById('prodPrice').value = p.price;
  document.getElementById('prodOldPrice').value = p.old_price || '';
  document.getElementById('prodBadge').value = p.badge || '';
  document.getElementById('prodStock').value = p.stock;
  document.getElementById('prodMaterial').value = p.material || '';
  document.getElementById('prodVelcro').value = p.velcro_zone || '';
  document.getElementById('prodSizes').value = p.sizes || '';
  document.getElementById('prodShortDesc').value = p.short_desc || '';
  document.getElementById('prodDescription').value = p.description || '';
  document.getElementById('prodImage').value = p.image || '';
  document.getElementById('prodFeatured').checked = (p.is_featured == 1);

  document.getElementById('productModalTitle').textContent = 'РЕДАКТИРОВАТЬ: ' + p.title;
  document.getElementById('productFormMsg').textContent = '';
  document.getElementById('productModal').classList.add('active');
}

document.getElementById('productForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  fetch('../api/admin_api.php?action=save_product', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    const msg = document.getElementById('productFormMsg');
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

function deleteProduct(id) {
  if (!confirm('Вы уверены, что хотите удалить эту карточку товара?')) return;
  const formData = new FormData();
  formData.append('id', id);

  fetch('../api/admin_api.php?action=delete_product', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const row = document.getElementById('prod-row-' + id);
      if (row) row.remove();
    } else {
      alert(data.message);
    }
  });
}
</script>

</body>
</html>
