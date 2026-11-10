<?php
require_once __DIR__ . '/includes/header.php';

$productId = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container' style='padding: 100px 0; text-align: center;'><h2>Товар не найден</h2><a href='catalog.php' class='hero-cta-btn' style='margin-top:20px;'>В каталог</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Record recently viewed
recordProductView($productId);

// Fetch recommendations
if ($product['category_slug'] === 'clothes') {
    // Recommend matching prints
    $recStmt = $pdo->prepare("SELECT * FROM products WHERE category_slug = 'prints' ORDER BY id ASC LIMIT 4");
    $recStmt->execute();
    $relatedItems = $recStmt->fetchAll();
    $relatedTitle = "РЕКОМЕНДУЕМЫЕ СМЕННЫЕ ПРИНТЫ (ВЕЛКРО)";
} else {
    // Recommend matching hoodies
    $recStmt = $pdo->prepare("SELECT * FROM products WHERE category_slug = 'clothes' ORDER BY id ASC LIMIT 4");
    $recStmt->execute();
    $relatedItems = $recStmt->fetchAll();
    $relatedTitle = "ПОДХОДЯЩИЕ КОФТЫ С ВЕЛКРО-БАЗОЙ";
}

// Fetch recently viewed items
$recentItems = getRecentlyViewedProducts(4, $productId);

// Fetch gallery images
$gallery = [];
if (!empty($product['images_gallery'])) {
    $decoded = json_decode($product['images_gallery'], true);
    if (is_array($decoded) && count($decoded) > 0) {
        $gallery = $decoded;
    }
}
if (empty($gallery)) {
    // If not set, duplicate main image for testing carousel
    $gallery = [$product['image'], $product['image'], $product['image']];
}

// Sizes array
$sizesArr = array_map('trim', explode(',', $product['sizes'] ?: 'S, M, L, XL, OVERSIZE'));
$isLiked = isProductLiked($product['id']);
?>

<div class="product-page-section">
  <div class="container">
    <!-- Breadcrumbs -->
    <div style="font-family: var(--font-mono); font-size: 11px; color: var(--text-dim); margin-bottom: 24px;">
      <a href="index.php" style="color: var(--text-muted);">ГЛАВНАЯ</a> / 
      <a href="catalog.php?cat=<?= $product['category_slug'] ?>" style="color: var(--text-muted);"><?= ($product['category_slug'] === 'prints') ? 'ПРИНТЫ' : 'ОДЕЖДА' ?></a> / 
      <span style="color: var(--neon-cyan);"><?= htmlspecialchars($product['title']) ?></span>
    </div>

    <!-- Main Product Details Grid -->
    <div class="product-detail-grid">
      <!-- Gallery Column (Smooth Touch Carousel) -->
      <div class="product-gallery-box">
        <div class="product-carousel-viewport" id="productCarouselViewport">
          <div class="product-carousel-track" id="productCarouselTrack">
            <?php foreach ($gallery as $gIdx => $gImg): ?>
              <div class="carousel-slide" data-index="<?= $gIdx ?>">
                <img src="<?= htmlspecialchars($gImg) ?>" alt="<?= htmlspecialchars($product['title']) ?> - Фото <?= $gIdx + 1 ?>" />
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (!empty($product['badge'])): ?>
            <span class="product-badge cyan" style="position: absolute; top: 14px; left: 14px; z-index: 10;">
              <?= htmlspecialchars($product['badge']) ?>
            </span>
          <?php endif; ?>

          <?php if (count($gallery) > 1): ?>
            <!-- Carousel Nav Arrows -->
            <button class="carousel-nav-btn carousel-prev" id="carouselPrevBtn" aria-label="Предыдущее фото">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <button class="carousel-nav-btn carousel-next" id="carouselNextBtn" aria-label="Следующее фото">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>

            <!-- Counter Badge -->
            <div class="carousel-counter-badge" id="carouselCounter">
              1 / <?= count($gallery) ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if (count($gallery) > 1): ?>
          <!-- Thumbnail strip below main image -->
          <div class="product-thumbs-strip" id="productThumbsStrip">
            <?php foreach ($gallery as $tIdx => $tImg): ?>
              <div class="product-thumb-item <?= ($tIdx === 0) ? 'active' : '' ?>" data-index="<?= $tIdx ?>">
                <img src="<?= htmlspecialchars($tImg) ?>" alt="Миниатюра <?= $tIdx + 1 ?>" />
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info Column -->
      <div class="product-info-panel">
        <div class="product-page-badge">
          <span>•</span> <?= ($product['category_slug'] === 'prints') ? 'СМЕННЫЙ ВЕЛКРО-ПАТЧ // BPM-04' : 'OVERSIZE GARMENT // 500 GSM' ?>
        </div>

        <h1 class="product-page-title"><?= htmlspecialchars($product['title']) ?></h1>

        <div class="product-page-price-row">
          <span class="product-page-price"><?= formatPrice($product['price']) ?></span>
          <?php if ($product['old_price']): ?>
            <span class="product-page-old-price"><?= formatPrice($product['old_price']) ?></span>
            <span style="font-family: var(--font-mono); font-size: 11px; color: var(--neon-lime); background: rgba(204,255,0,0.1); padding: 4px 8px; border-radius: 4px;">
              -<?= round((1 - $product['price'] / $product['old_price']) * 100) ?>%
            </span>
          <?php endif; ?>
        </div>

        <!-- Technical Specs -->
        <div class="product-specs-list">
          <div class="spec-cell">
            <span class="spec-title">Материал &amp; Плотность</span>
            <span class="spec-data"><?= htmlspecialchars($product['material']) ?></span>
          </div>
          <div class="spec-cell">
            <span class="spec-title">Велкро-зона</span>
            <span class="spec-data"><?= htmlspecialchars($product['velcro_zone']) ?></span>
          </div>
          <div class="spec-cell">
            <span class="spec-title">Тираж &amp; Статус</span>
            <span class="spec-data" style="color: var(--neon-lime);">В наличии (<?= $product['stock'] ?> шт.)</span>
          </div>
          <div class="spec-cell">
            <span class="spec-title">Крой &amp; Посадка</span>
            <span class="spec-data"><?= ($product['category_slug'] === 'clothes') ? 'Свободный оверсайз' : 'Сменный модуль' ?></span>
          </div>
        </div>

        <!-- Size Picker -->
        <div class="size-picker-wrap">
          <div class="size-picker-label">
            <span>Выберите размер:</span>
            <span style="color: var(--neon-cyan); cursor: pointer;" onclick="openModal('sizeGuideModal')">Размерная сетка ↗</span>
          </div>
          <div class="size-options" id="sizeOptionsGroup">
            <?php foreach ($sizesArr as $idx => $s): ?>
              <div class="size-pill <?= ($idx === 0) ? 'active' : '' ?>" data-size="<?= htmlspecialchars($s) ?>">
                <?= htmlspecialchars($s) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="product-actions-row">
          <button class="btn-add-to-cart" id="btnPageAddToCart">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            <span>ДОБАВИТЬ В КОРЗИНУ</span>
          </button>

          <button class="btn-wishlist-large <?= $isLiked ? 'liked' : '' ?>" data-product-id="<?= $product['id'] ?>" title="В избранное">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="<?= $isLiked ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
        </div>

        <!-- Full Description Box -->
        <div class="product-desc-box">
          <h4 style="font-family: var(--font-display); font-size: 14px; color: #fff; margin-bottom: 10px;">ОПИСАНИЕ И ДЕТАЛИ</h4>
          <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
      </div>
    </div>

    <!-- Related / Recommended Compatible Items -->
    <?php if (!empty($relatedItems)): ?>
      <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--border-subtle);">
        <div class="section-head" style="text-align: left; margin-bottom: 30px;">
          <div class="section-pill" style="border-color: var(--neon-violet); color: var(--neon-violet); background: rgba(168,85,247,0.1);">СОВМЕСТИМЫЕ ВЕЩИ</div>
          <h3 class="section-title" style="font-size: 26px;"><?= $relatedTitle ?></h3>
        </div>
        <div class="products-grid">
          <?php foreach ($relatedItems as $item): ?>
            <div class="product-card">
              <div class="product-card-top">
                <a href="product.php?id=<?= $item['id'] ?>">
                  <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-card-img" />
                </a>
              </div>
              <div class="product-card-content">
                <h4 class="product-title" style="font-size: 14px;">
                  <a href="product.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                </h4>
                <div class="product-card-bottom">
                  <span class="price-main"><?= formatPrice($item['price']) ?></span>
                  <button onclick="quickAddToCart(<?= $item['id'] ?>, 'Стандарт', 1)" class="btn-card-action">
                    + В КОРЗИНУ
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Recently Viewed Items -->
    <?php if (!empty($recentItems)): ?>
      <div style="margin-top: 80px; padding-top: 40px; border-top: 1px solid var(--border-subtle);">
        <div class="section-head" style="text-align: left; margin-bottom: 30px;">
          <div class="section-pill">ИСТОРИЯ</div>
          <h3 class="section-title" style="font-size: 24px;">ВЫ НЕДАВНО СМОТРЕЛИ</h3>
        </div>
        <div class="products-grid">
          <?php foreach ($recentItems as $item): ?>
            <div class="product-card">
              <div class="product-card-top">
                <a href="product.php?id=<?= $item['id'] ?>">
                  <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="product-card-img" />
                </a>
              </div>
              <div class="product-card-content">
                <h4 class="product-title" style="font-size: 14px;">
                  <a href="product.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                </h4>
                <div class="product-card-bottom">
                  <span class="price-main"><?= formatPrice($item['price']) ?></span>
                  <a href="product.php?id=<?= $item['id'] ?>" class="btn-card-action">
                    СМОТРЕТЬ
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Size Guide Modal -->
<div class="modal-overlay" id="sizeGuideModal">
  <div class="modal-card" style="max-width: 600px;">
    <button class="modal-close-btn">&times;</button>
    <h3 style="font-family: var(--font-display); font-size: 18px; margin-bottom: 16px;">РАЗМЕРНАЯ СЕТКА (OVERSIZE FIT)</h3>
    <table style="width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 12px; text-align: left;">
      <thead>
        <tr style="border-bottom: 1px solid var(--border-neon); color: var(--neon-cyan);">
          <th style="padding: 10px;">РАЗМЕР</th>
          <th style="padding: 10px;">ШИРИНА (ОГ)</th>
          <th style="padding: 10px;">ДЛИНА</th>
          <th style="padding: 10px;">РУКАВ</th>
        </tr>
      </thead>
      <tbody>
        <tr style="border-bottom: 1px solid var(--border-subtle);">
          <td style="padding: 10px; font-weight: 700;">S</td>
          <td style="padding: 10px;">58 см</td>
          <td style="padding: 10px;">70 см</td>
          <td style="padding: 10px;">62 см</td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border-subtle);">
          <td style="padding: 10px; font-weight: 700;">M</td>
          <td style="padding: 10px;">62 см</td>
          <td style="padding: 10px;">73 см</td>
          <td style="padding: 10px;">64 см</td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border-subtle);">
          <td style="padding: 10px; font-weight: 700;">L</td>
          <td style="padding: 10px;">66 см</td>
          <td style="padding: 10px;">76 см</td>
          <td style="padding: 10px;">66 см</td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border-subtle);">
          <td style="padding: 10px; font-weight: 700;">XL / OVER</td>
          <td style="padding: 10px;">70 см</td>
          <td style="padding: 10px;">79 см</td>
          <td style="padding: 10px;">68 см</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 1. Size Picker
  let selectedSize = '<?= $sizesArr[0] ?>';
  const sizePills = document.querySelectorAll('.size-pill');
  sizePills.forEach(p => {
    p.addEventListener('click', () => {
      sizePills.forEach(x => x.classList.remove('active'));
      p.classList.add('active');
      selectedSize = p.dataset.size;
    });
  });

  const addBtn = document.getElementById('btnPageAddToCart');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      window.quickAddToCart(<?= $product['id'] ?>, selectedSize, 1);
    });
  }

  // 2. Interactive Product Image Carousel & Touch Swipe
  const track = document.getElementById('productCarouselTrack');
  const slides = document.querySelectorAll('.carousel-slide');
  const prevBtn = document.getElementById('carouselPrevBtn');
  const nextBtn = document.getElementById('carouselNextBtn');
  const counter = document.getElementById('carouselCounter');
  const thumbs = document.querySelectorAll('.product-thumb-item');
  const viewport = document.getElementById('productCarouselViewport');

  if (track && slides.length > 1) {
    let currentSlide = 0;
    const totalSlides = slides.length;

    function updateCarousel(index) {
      if (index < 0) index = totalSlides - 1;
      if (index >= totalSlides) index = 0;
      currentSlide = index;

      track.style.transform = `translateX(-${currentSlide * 100}%)`;

      if (counter) {
        counter.textContent = `${currentSlide + 1} / ${totalSlides}`;
      }

      thumbs.forEach((t, i) => {
        if (i === currentSlide) {
          t.classList.add('active');
          t.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
          t.classList.remove('active');
        }
      });
    }

    if (prevBtn) {
      prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        updateCarousel(currentSlide - 1);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        updateCarousel(currentSlide + 1);
      });
    }

    thumbs.forEach(thumb => {
      thumb.addEventListener('click', () => {
        const idx = parseInt(thumb.dataset.index, 10);
        updateCarousel(idx);
      });
    });

    // Mobile Touch Swipe Handling
    if (viewport) {
      let touchStartX = 0;
      let touchEndX = 0;
      let touchStartY = 0;
      let touchEndY = 0;

      viewport.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
      }, { passive: true });

      viewport.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
      }, { passive: true });

      function handleSwipe() {
        const diffX = touchEndX - touchStartX;
        const diffY = touchEndY - touchStartY;

        // Ensure horizontal swipe is dominant and above 35px threshold
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 35) {
          if (diffX < 0) {
            // Swipe Left -> Next
            updateCarousel(currentSlide + 1);
          } else {
            // Swipe Right -> Prev
            updateCarousel(currentSlide - 1);
          }
        }
      }
    }

    // Keyboard Arrow Keys support
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') updateCarousel(currentSlide - 1);
      if (e.key === 'ArrowRight') updateCarousel(currentSlide + 1);
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
