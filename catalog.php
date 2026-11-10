<?php
require_once __DIR__ . '/includes/header.php';

$catFilter = $_GET['cat'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$search = trim($_GET['q'] ?? '');

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($catFilter === 'clothes' || $catFilter === 'prints') {
    $query .= " AND category_slug = ?";
    $params[] = $catFilter;
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    default:
        $query .= " ORDER BY id DESC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="product-page-section">
  <div class="container">
    <div class="catalog-header-bar">
      <div>
        <div class="section-pill" style="border-color: var(--neon-cyan); color: var(--neon-cyan); background: rgba(0, 240, 255, 0.1);">
          <?= ($catFilter === 'clothes') ? 'ХУДИ И СВИТШОТЫ' : (($catFilter === 'prints') ? 'СМЕННЫЕ ПРИНТЫ НА ЛИПУЧКЕ' : 'ПОЛНЫЙ КАТАЛОГ BPM') ?>
        </div>
        <h1 class="section-title" style="margin-bottom: 0;">
          <?= ($catFilter === 'clothes') ? 'КОЛЛЕКЦИЯ <span class="cyan">ОДЕЖДЫ</span>' : (($catFilter === 'prints') ? 'СМЕННЫЕ <span class="violet">ПРИНТЫ</span>' : 'ВСЯ <span class="cyan">КОЛЛЕКЦИЯ</span>') ?>
        </h1>
      </div>

      <!-- Filters & Sort -->
      <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <!-- Categories pills -->
        <div class="catalog-tabs">
          <a href="catalog.php" class="tab-btn <?= empty($catFilter) ? 'active' : '' ?>">ВСЁ</a>
          <a href="catalog.php?cat=clothes" class="tab-btn <?= ($catFilter === 'clothes') ? 'active' : '' ?>">ОДЕЖДА</a>
          <a href="catalog.php?cat=prints" class="tab-btn <?= ($catFilter === 'prints') ? 'active' : '' ?>">ПРИНТЫ</a>
        </div>

        <!-- Sort Select -->
        <form method="GET" style="display: flex; gap: 8px;">
          <?php if ($catFilter): ?>
            <input type="hidden" name="cat" value="<?= htmlspecialchars($catFilter) ?>">
          <?php endif; ?>
          <select name="sort" class="form-select" onchange="this.form.submit()" style="padding: 10px 14px; font-size: 11px;">
            <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Сначала новые</option>
            <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Сначала дешевле</option>
            <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Сначала дороже</option>
          </select>
        </form>
      </div>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
      <div style="text-align: center; padding: 80px 20px; background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
        <p style="font-family: var(--font-mono); font-size: 16px; color: var(--text-muted);">Товары по заданным параметрам не найдены</p>
        <a href="catalog.php" class="hero-cta-btn" style="margin-top: 20px; font-size: 12px; padding: 12px 24px;">СБРОСИТЬ ФИЛЬТРЫ</a>
      </div>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($products as $item): 
          $liked = isProductLiked($item['id']);
          $catLabel = ($item['category_slug'] === 'prints') ? 'ПРИНТ ВЕЛКРО' : 'ОДЕЖДА VELCRO';
        ?>
          <div class="product-card" data-category="<?= $item['category_slug'] ?>">
            <div class="product-card-top">
              <a href="product.php?id=<?= $item['id'] ?>">
                <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-card-img" />
              </a>

              <?php if (!empty($item['badge'])): ?>
                <span class="product-badge <?= ($item['category_slug'] === 'prints') ? 'violet' : 'cyan' ?>">
                  <?= htmlspecialchars($item['badge']) ?>
                </span>
              <?php endif; ?>

              <button class="like-btn <?= $liked ? 'liked' : '' ?>" data-product-id="<?= $item['id'] ?>" title="В избранное">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $liked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
            </div>

            <div class="product-card-content">
              <span class="product-category-tag"><?= $catLabel ?></span>
              <h3 class="product-title">
                <a href="product.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
              </h3>
              <p class="product-short-desc"><?= htmlspecialchars($item['short_desc']) ?></p>

              <div class="product-card-bottom">
                <div class="product-prices">
                  <span class="price-main"><?= formatPrice($item['price']) ?></span>
                  <?php if ($item['old_price']): ?>
                    <span class="price-old"><?= formatPrice($item['old_price']) ?></span>
                  <?php endif; ?>
                </div>

                <div style="display: flex; gap: 8px;">
                  <a href="product.php?id=<?= $item['id'] ?>" class="btn-card-action" style="background: transparent; border-color: rgba(255,255,255,0.2); color: #fff;">
                    ОБЗОР
                  </a>
                  <button onclick="quickAddToCart(<?= $item['id'] ?>, '<?= ($item['category_slug'] === 'prints') ? 'Стандарт (20x15 см)' : 'L' ?>', 1)" class="btn-card-action">
                    + В КОРЗИНУ
                  </button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
