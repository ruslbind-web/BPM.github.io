/**
 * BPM Garments - Main Interactive Script
 * Sound Synthesizer, Cart Drawer, Promo Engine, Likes & Modals
 */

document.addEventListener('DOMContentLoaded', () => {
  initCartDrawer();
  initLikes();
  initModals();
  initCatalogFilters();
});

/* ==========================================================================
   2. CART DRAWER & PROMO ENGINE
   ========================================================================== */
function initCartDrawer() {
  const overlay = document.getElementById('cartDrawerOverlay');
  const openBtns = document.querySelectorAll('[data-open-cart]');
  const closeBtn = document.getElementById('cartDrawerClose');

  if (openBtns) {
    openBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openCartDrawer();
      });
    });
  }

  if (closeBtn && overlay) {
    closeBtn.addEventListener('click', closeCartDrawer);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeCartDrawer();
    });
  }

  // Promo Code apply button
  const promoBtn = document.getElementById('btnApplyPromo');
  if (promoBtn) {
    promoBtn.addEventListener('click', applyPromoCode);
  }

  // Checkout button
  const checkoutBtn = document.getElementById('btnDrawerCheckout');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      window.location.href = 'checkout.php';
    });
  }
}

function openCartDrawer() {
  const overlay = document.getElementById('cartDrawerOverlay');
  if (overlay) {
    overlay.classList.add('active');
    refreshCartDrawer();
  }
}

function closeCartDrawer() {
  const overlay = document.getElementById('cartDrawerOverlay');
  if (overlay) overlay.classList.remove('active');
}

function refreshCartDrawer() {
  fetch('api/cart.php?action=get')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        updateCartBadge(data.count);
        renderCartItems(data.items);
        updateCartTotals(data);
      }
    })
    .catch(err => console.error('Cart load error:', err));
}

function updateCartBadge(count) {
  const badges = document.querySelectorAll('.cart-count-pill');
  badges.forEach(b => {
    b.textContent = count;
  });
}

function renderCartItems(items) {
  const container = document.getElementById('cartItemsList');
  if (!container) return;

  if (!items || items.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 40px 10px; color: var(--text-dim);">
        <p style="font-family: var(--font-mono); font-size: 13px;">КОРЗИНА ПУСТА</p>
        <p style="font-size: 12px; margin-top: 8px;">Выберите одежду или сменные принты в каталоге</p>
      </div>`;
    return;
  }

  let html = '';
  items.forEach(item => {
    const itemKey = `${item.id}_${item.size || 'default'}`;
    const categoryTag = item.category_slug === 'prints' ? 'ПРИНТ ВЕЛКРО' : 'ОДЕЖДА';
    html += `
      <div class="cart-item-row" data-key="${itemKey}">
        <img src="${item.image}" alt="${item.title}" class="cart-item-thumb">
        <div class="cart-item-info">
          <span class="cart-item-meta">${categoryTag} • ${item.size}</span>
          <span class="cart-item-name">${item.title}</span>
          <span class="cart-item-price">${formatCurrency(item.price)}</span>
        </div>
        <div class="cart-qty-ctrls">
          <button class="qty-btn" onclick="changeCartQty('${itemKey}', ${item.quantity - 1})">-</button>
          <span class="qty-val">${item.quantity}</span>
          <button class="qty-btn" onclick="changeCartQty('${itemKey}', ${item.quantity + 1})">+</button>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
}

function updateCartTotals(data) {
  const subtotalEl = document.getElementById('drawerSubtotal');
  const discountRow = document.getElementById('drawerDiscountRow');
  const discountEl = document.getElementById('drawerDiscount');
  const totalEl = document.getElementById('drawerTotal');
  const promoAlert = document.getElementById('promoAlert');

  if (subtotalEl) subtotalEl.textContent = data.formatted_subtotal;
  if (totalEl) totalEl.textContent = data.formatted_total;

  if (data.discount > 0 && discountRow) {
    discountRow.style.display = 'flex';
    if (discountEl) discountEl.textContent = '-' + data.formatted_discount;
  } else if (discountRow) {
    discountRow.style.display = 'none';
  }

  if (data.promo && promoAlert) {
    promoAlert.className = 'promo-alert success';
    promoAlert.textContent = data.promo.message;
  }
}

function changeCartQty(itemKey, newQty) {
  const formData = new FormData();
  formData.append('item_key', itemKey);
  formData.append('quantity', newQty);

  fetch('api/cart.php?action=update', {
    method: 'POST',
    body: formData
  })
    .then(r => r.json())
    .then(() => refreshCartDrawer());
}

function applyPromoCode() {
  const input = document.getElementById('promoCodeInput');
  const alertEl = document.getElementById('promoAlert');
  if (!input || !alertEl) return;

  const code = input.value.trim();
  if (!code) {
    alertEl.className = 'promo-alert error';
    alertEl.textContent = 'Введите промокод';
    return;
  }

  const formData = new FormData();
  formData.append('promo_code', code);

  fetch('api/cart.php?action=apply_promo', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alertEl.className = 'promo-alert success';
        alertEl.textContent = data.message;
        refreshCartDrawer();
      } else {
        alertEl.className = 'promo-alert error';
        alertEl.textContent = data.message;
      }
    });
}

// Global Quick Add To Cart function
window.quickAddToCart = function(productId, size = 'M', qty = 1) {
  const formData = new FormData();
  formData.append('product_id', productId);
  formData.append('size', size);
  formData.append('quantity', qty);

  fetch('api/cart.php?action=add', {
    method: 'POST',
    body: formData
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        openCartDrawer();
      } else {
        alert(data.message || 'Ошибка добавления');
      }
    });
};

/* ==========================================================================
   3. LIKES & WISHLIST
   ========================================================================== */
function initLikes() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.like-btn') || e.target.closest('.btn-wishlist-large');
    if (!btn) return;

    e.preventDefault();
    const productId = btn.dataset.productId;
    if (!productId) return;

    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('api/likes.php?action=toggle', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          if (data.liked) {
            btn.classList.add('liked');
          } else {
            btn.classList.remove('liked');
          }
        }
      });
  });
}

/* ==========================================================================
   4. CATALOG FILTER TABS (CLOTHES / PRINTS / ALL)
   ========================================================================== */
function initCatalogFilters() {
  const tabBtns = document.querySelectorAll('.tab-btn[data-cat]');
  const cards = document.querySelectorAll('.product-card[data-category]');

  if (!tabBtns.length || !cards.length) return;

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.dataset.cat;
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      cards.forEach(card => {
        if (cat === 'all' || card.dataset.category === cat) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
}

/* ==========================================================================
   5. MODALS & TELEGRAM DEV LOGIN
   ========================================================================== */
function initModals() {
  const modalOverlays = document.querySelectorAll('.modal-overlay');
  const closeBtns = document.querySelectorAll('.modal-close-btn');

  closeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      modalOverlays.forEach(m => m.classList.remove('active'));
    });
  });

  modalOverlays.forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('active');
      }
    });
  });

  // Dev Telegram Login form
  const devTgForm = document.getElementById('devTelegramForm');
  if (devTgForm) {
    devTgForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(devTgForm);

      fetch('api/auth.php?action=dev_telegram_login', {
        method: 'POST',
        body: formData
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'profile.php';
          } else {
            alert(data.message);
          }
        });
    });
  }
}

window.openModal = function(id) {
  const modal = document.getElementById(id);
  if (modal) modal.classList.add('active');
};

function formatCurrency(num) {
  return Number(num).toLocaleString('ru-RU') + ' ₽';
}
