<?php
require_once __DIR__ . '/includes/header.php';

// Fetch featured items
$featuredStmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY id ASC LIMIT 10");
$featuredProducts = $featuredStmt->fetchAll();

// Fetch prints for simulator
$printsStmt = $pdo->query("SELECT * FROM products WHERE category_slug = 'prints' ORDER BY id ASC LIMIT 6");
$simulatorPrints = $printsStmt->fetchAll();

// First featured hoodie (real photo)
$heroHoodieStmt = $pdo->query("SELECT * FROM products WHERE id = 1 LIMIT 1");
$heroHoodie = $heroHoodieStmt->fetch() ?: $featuredProducts[0];
?>

<!-- ==========================================================================
     HERO SECTION (NO BPM EXPANSION - PURE BRAND IDENTITY)
     ========================================================================== -->
<section class="hero-section">
  <div class="container hero-grid">
    <!-- Left Hero Column -->
    <div class="hero-content">
      <div class="hero-tagline">
        <span>•</span> UNDERGROUND SOUND SYSTEM GARMENTS
      </div>

      <h1 class="hero-title">
        BPM<span style="color: var(--neon-lime);">.</span><br>
        VELCRO<br>
        <span class="title-outline">GARMENTS.</span>
      </h1>

      <p class="hero-desc">
        Одежда, созданная для клубов, бетонных рейвов и ночных улиц. Премиальный плотный хлопок 480–520 GSM, авторские психоделические и кислотные принты на сменных липучках (Velcro), свободный оверсайз крой.
      </p>

      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="#catalog" class="hero-cta-btn">
          <span>СМОТРЕТЬ ДРОП</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>

        <a href="#simulator" class="hero-cta-btn" style="background: transparent; color: #fff; border: 1px solid var(--neon-cyan); box-shadow: var(--neon-cyan-glow);">
          <span>КОНСТРУКТОР ЛИПУЧЕК</span>
        </a>
      </div>

      <!-- Hero Spec Highlights -->
      <div class="hero-stats">
        <div class="stat-item">
          <span class="stat-value cyan">500GSM</span>
          <span class="stat-label">ФРАНЦУЗСКИЙ ФУТЕР</span>
        </div>
        <div class="stat-item">
          <span class="stat-value lime">100%</span>
          <span class="stat-label">ОРИГИНАЛЬНЫЙ КРОЙ</span>
        </div>
        <div class="stat-item">
          <span class="stat-value violet">#04</span>
          <span class="stat-label">ЛИМИТИРОВАННЫЙ ТИРАЖ</span>
        </div>
      </div>
    </div>

    <!-- Right Hero Showcase Card (Using user's real hoodie photo!) -->
    <div class="hero-showcase">
      <div class="hero-card">
        <div class="hero-card-img-wrap">
          <img src="assets/images/products/hoodie-real-eyes.jpg" alt="BPM Psy-Matrix Hoodie" class="hero-card-img">
          <span class="hero-card-badge">#04 DROP // REAL VELCRO</span>
        </div>
        <div class="hero-card-info">
          <div>
            <h3 class="hero-card-title"><?= htmlspecialchars($heroHoodie['title']) ?></h3>
            <p class="hero-card-sub"><?= htmlspecialchars($heroHoodie['short_desc']) ?></p>
          </div>
          <div>
            <div class="hero-card-price"><?= formatPrice($heroHoodie['price']) ?></div>
            <button onclick="quickAddToCart(<?= $heroHoodie['id'] ?>, 'L', 1)" class="btn-card-action" style="margin-top: 8px; width: 100%;">
              В КОРЗИНУ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     VELCRO MODULAR CONCEPT SECTION
     ========================================================================== -->
<section class="concept-section" id="concept">
  <div class="container">
    <div class="section-head">
      <div class="section-pill">ТЕХНОЛОГИЯ VELCRO MODULAR</div>
      <h2 class="section-title">ОДНА КОФТА. <span class="cyan">БЕСКОНЕЧНОСТЬ</span> <span class="violet">ОБРАЗОВ.</span></h2>
      <p class="section-desc">
        Больше не нужно покупать десять разных толстовок. Мы встроили контактную велкро-панель военного стандарта спереди на грудь наших худи. Смените визуал за 2 секунды прямо в клубе перед любимым диджей-сетом.
      </p>
    </div>

    <!-- 3 Steps -->
    <div class="concept-steps">
      <div class="concept-step-card">
        <div class="step-num">01.</div>
        <h3 class="step-title">БАЗОВАЯ ВЕЛКРО-КОФТА</h3>
        <p class="step-text">
          Тяжелый 500 GSM французский футер без начеса. Бархатистая микро-петля не цепляется за другие ткани, не царапает кожу и выдерживает 10 000+ циклов перестегивания.
        </p>
      </div>

      <div class="concept-step-card">
        <div class="step-num">02.</div>
        <h3 class="step-title">СМЕННЫЕ ПРИНТЫ BPM</h3>
        <p class="step-text">
          Патчи с микро-крючками: шелкография, люминесцентные неоновые нити, UV-свечение в ультрафиолете клубов и светоотражающие кибер-элементы.
        </p>
      </div>

      <div class="concept-step-card">
        <div class="step-num">03.</div>
        <h3 class="step-title">КАСТОМИЗАЦИЯ НА ХОДУ</h3>
        <p class="step-text">
          Берите в карман несколько сменных патчей. Днем — сдержанный минималистичный логотип, ночью на рейве — взрывной кислотный 16-глазый арт.
        </p>
      </div>
    </div>

    <!-- Interactive Velcro Simulator -->
    <div class="velcro-simulator-box" id="simulator">
      <div class="sim-grid">
        <!-- Interactive Hoodie Canvas -->
        <div class="sim-preview-wrap">
          <img src="assets/images/products/hoodie-real-eyes.jpg" alt="Base Hoodie" class="sim-hoodie-base" id="simHoodieImg" style="border-radius: 8px;">
          
          <!-- Velcro Target Zone -->
          <div class="sim-velcro-target" id="simVelcroTarget" data-hoodie-id="<?= $heroHoodie['id'] ?>" data-hoodie-price="<?= $heroHoodie['price'] ?>" style="top: 55%; left: 68%; width: 34%; height: 26%;">
            <div class="sim-target-hint">
              <span>+ ЗОНА VELCRO<br>ВЫБЕРИТЕ ПРИНТ</span>
            </div>
          </div>
        </div>

        <!-- Simulator Controls -->
        <div class="sim-controls-wrap">
          <div class="section-pill" style="border-color: var(--neon-cyan); color: var(--neon-cyan); background: rgba(0, 240, 255, 0.1);">ИНТЕРАКТИВНЫЙ КОНСТРУКТОР</div>
          <h4>ПРИМЕРЬТЕ ПРИНТ НА ХУДИ</h4>
          <p class="sim-controls-desc">
            Нажмите на любой сменный принт ниже, чтобы пристегнуть его на липучку худи и услышать тактильный звук Velcro:
          </p>

          <!-- Prints Grid Selector -->
          <div class="sim-prints-selector">
            <?php foreach ($simulatorPrints as $idx => $pr): ?>
              <div class="sim-print-choice <?= ($idx === 0) ? 'active' : '' ?>" 
                   data-id="<?= $pr['id'] ?>"
                   data-title="<?= htmlspecialchars($pr['title']) ?>"
                   data-price="<?= $pr['price'] ?>"
                   data-img="<?= $pr['image'] ?>">
                <img src="<?= $pr['image'] ?>" class="sim-print-thumb" alt="<?= htmlspecialchars($pr['title']) ?>">
                <div class="sim-print-name"><?= htmlspecialchars(str_replace('PRINT: ', '', $pr['title'])) ?></div>
                <div class="sim-print-price"><?= formatPrice($pr['price']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Combo Pricing -->
          <div style="padding: 14px; background: rgba(10, 10, 20, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); margin-bottom: 16px;">
            <div style="font-family: var(--font-mono); font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Стоимость комплекта (Худи + Принт):</div>
            <div id="simComboPrice" style="font-family: var(--font-mono); font-size: 22px; font-weight: 900; margin-top: 4px;">
              <span style="color: var(--neon-lime);">10 160 ₽</span>
              <span style="font-size: 13px; color: var(--text-dim); text-decoration: line-through; margin-left: 8px;">10 700 ₽</span>
              <span style="display: block; font-size: 11px; color: var(--neon-cyan); margin-top: 2px;">⚡ Скидка 30% на принт при покупке с худи</span>
            </div>
          </div>

          <button class="btn-drawer-checkout" id="btnAddComboToCart" style="font-size: 12px; padding: 14px;">
            <span>⚡ КУПИТЬ КОМПЛЕКТ В 1 КЛИК</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================================
     CATALOG SECTION (CLOTHES & PRINTS TABS)
     ========================================================================== -->
<section class="catalog-section" id="catalog">
  <div class="container">
    <div class="catalog-header-bar">
      <div>
        <div class="section-pill" style="border-color: var(--neon-lime); color: var(--neon-lime); background: rgba(204, 255, 0, 0.1);">ДРОП #04</div>
        <h2 class="section-title" style="margin-bottom: 0;">КАТАЛОГ <span class="cyan">BPM</span></h2>
      </div>

      <!-- Section Tabs -->
      <div class="catalog-tabs">
        <button class="tab-btn active" data-cat="all">ВСЁ (<?= count($featuredProducts) ?>)</button>
        <button class="tab-btn" data-cat="clothes">ОДЕЖДА</button>
        <button class="tab-btn" data-cat="prints">СМЕННЫЕ ПРИНТЫ</button>
      </div>
    </div>

    <!-- Products Grid (2 columns on mobile, touch-friendly) -->
    <div class="products-grid">
      <?php foreach ($featuredProducts as $item): 
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

              <div style="display: flex; gap: 6px;">
                <button onclick="quickAddToCart(<?= $item['id'] ?>, '<?= ($item['category_slug'] === 'prints') ? 'Стандарт (20x15 см)' : 'L' ?>', 1)" class="btn-card-action">
                  + В КОРЗИНУ
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
