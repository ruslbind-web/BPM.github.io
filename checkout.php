<?php
require_once __DIR__ . '/includes/header.php';

$cart = getCartItems();
if (empty($cart)) {
    echo "<div class='container' style='padding: 100px 0; text-align: center;'><h2>Корзина пуста</h2><p style='color:var(--text-muted); margin: 10px 0 20px;'>Добавьте кофты или сменные принты перед оформлением</p><a href='catalog.php' class='hero-cta-btn'>В КАТАЛОГ</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$user = getCurrentUser();
$subtotal = getCartSubtotal();
$discount = 0;
$promo = $_SESSION['applied_promo'] ?? null;
if ($promo) {
    $recalc = calculatePromoDiscount($cart, $promo['code'], $pdo);
    if ($recalc['valid']) {
        $discount = $recalc['discount'];
    }
}
$deliveryCost = ($subtotal >= 10000) ? 0 : 390;
$total = max(0, ($subtotal - $discount) + $deliveryCost);
?>

<div class="product-page-section">
  <div class="container">
    <div class="section-head" style="text-align: left; margin-bottom: 30px;">
      <div class="section-pill" style="border-color: var(--neon-cyan); color: var(--neon-cyan); background: rgba(0, 240, 255, 0.1);">CHECKOUT</div>
      <h1 class="section-title" style="font-size: 34px;">ОФОРМЛЕНИЕ <span class="cyan">ЗАКАЗА</span></h1>
    </div>

    <!-- Payment Notice Banner -->
    <div class="payment-notice-banner">
      <div style="font-size: 24px;">💳</div>
      <div class="payment-notice-text">
        <strong>Оплата работает в демонстрационном режиме:</strong><br>
        Реальное списание средств пока отключено. Заказ регистрируется в базе данных, резервирует тираж и отображается в вашем Telegram-профиле. Как подключить боевой эквайринг (ЮKassa, Tinkoff/Т-Банк, Telegram Stars), читайте в нашем <a href="payment_guide.php" target="_blank">Гайде по подключению оплаты ↗</a>.
      </div>
    </div>

    <form id="checkoutOrderForm">
      <div class="checkout-grid">
        <!-- Left: Customer & Delivery Info -->
        <div class="checkout-card">
          <h2 class="checkout-card-title">
            <span>01.</span> ДАННЫЕ ПОЛУЧАТЕЛЯ
          </h2>

          <div class="form-group">
            <label class="form-label">ФИО Получателя *</label>
            <input type="text" name="customer_name" class="form-input" required 
                   value="<?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>" 
                   placeholder="Иванов Иван Иванович">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div class="form-group">
              <label class="form-label">Номер телефона *</label>
              <input type="tel" name="customer_phone" class="form-input" required 
                     value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                     placeholder="+7 (999) 000-00-00">
            </div>
            <div class="form-group">
              <label class="form-label">Telegram Аккаунт</label>
              <input type="text" name="customer_telegram" class="form-input" 
                     value="<?= htmlspecialchars($user['username'] ? '@' . $user['username'] : '') ?>" 
                     placeholder="@username">
            </div>
          </div>

          <h2 class="checkout-card-title" style="margin-top: 34px;">
            <span>02.</span> СПОСОБ ДОСТАВКИ
          </h2>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div class="form-group">
              <label class="form-label">Город доставки *</label>
              <input type="text" name="customer_city" class="form-input" required 
                     value="<?= htmlspecialchars($user['city'] ?? 'Москва') ?>" 
                     placeholder="Город">
            </div>
            <div class="form-group">
              <label class="form-label">Транспортная служба</label>
              <select name="delivery_type" class="form-select">
                <option value="СДЭК (Пункт выдачи)">СДЭК (Пункт выдачи) — 390 ₽</option>
                <option value="СДЭК (Курьер до двери)">СДЭК (Курьер до двери) — 590 ₽</option>
                <option value="Почта России">Почта России 1-й класс — 390 ₽</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Адрес доставки или номер/адрес ПВЗ СДЭК *</label>
            <textarea name="customer_address" class="form-textarea" rows="3" required 
                      placeholder="Укажите адрес ПВЗ или точный домашний адрес"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Комментарий к заказу</label>
            <textarea name="notes" class="form-textarea" rows="2" placeholder="Пожелания по примерке, звонку курьера и т.д."></textarea>
          </div>

          <h2 class="checkout-card-title" style="margin-top: 34px;">
            <span>03.</span> ВЫБОР СПОСОБА ОПЛАТЫ
          </h2>

          <div style="display: flex; flex-direction: column; gap: 10px;">
            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; background: rgba(10,10,20,0.6); border: 1px solid var(--border-neon); border-radius: var(--radius-sm); cursor: pointer;">
              <input type="radio" name="payment_method" value="Оплата при получении в СДЭК" checked>
              <div>
                <strong style="font-family: var(--font-mono); font-size: 13px;">Оплата при получении (СДЭК)</strong>
                <div style="font-size: 11px; color: var(--text-muted);">Оплата картой или наличными после осмотра и примерки</div>
              </div>
            </label>

            <label style="display: flex; align-items: center; gap: 10px; padding: 12px; background: rgba(10,10,20,0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); cursor: pointer;">
              <input type="radio" name="payment_method" value="СБП (Система быстрых платежей)">
              <div>
                <strong style="font-family: var(--font-mono); font-size: 13px;">СБП (Демо-режим)</strong>
                <div style="font-size: 11px; color: var(--text-muted);">Мгновенный перевод по QR-коду любого банка</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Right: Order Summary -->
        <div>
          <div class="checkout-card" style="position: sticky; top: 100px;">
            <h3 class="checkout-card-title" style="font-size: 18px; margin-bottom: 20px;">СОСТАВ ЗАКАЗА</h3>

            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto; margin-bottom: 20px; padding-right: 6px;">
              <?php foreach ($cart as $item): ?>
                <div style="display: grid; grid-template-columns: 50px 1fr auto; gap: 12px; align-items: center; font-size: 12px;">
                  <img src="<?= $item['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; background: #000;" alt="">
                  <div>
                    <div style="font-family: var(--font-display); font-weight: 700; color: #fff;"><?= htmlspecialchars($item['title']) ?></div>
                    <div style="color: var(--text-dim); font-family: var(--font-mono); font-size: 11px;"><?= htmlspecialchars($item['size']) ?> × <?= $item['quantity'] ?> шт.</div>
                  </div>
                  <div style="font-family: var(--font-mono); font-weight: 800; color: var(--neon-cyan);">
                    <?= formatPrice($item['price'] * $item['quantity']) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Totals breakdown -->
            <div style="border-top: 1px solid var(--border-subtle); padding-top: 16px; display: flex; flex-direction: column; gap: 8px; font-family: var(--font-mono); font-size: 13px;">
              <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                <span>Сумма:</span>
                <span><?= formatPrice($subtotal) ?></span>
              </div>

              <?php if ($discount > 0): ?>
                <div style="display: flex; justify-content: space-between; color: var(--neon-lime);">
                  <span>Скидка по акции (<?= htmlspecialchars($promo['code']) ?>):</span>
                  <span>-<?= formatPrice($discount) ?></span>
                </div>
              <?php endif; ?>

              <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                <span>Доставка:</span>
                <span><?= ($deliveryCost == 0) ? 'БЕСПЛАТНО' : formatPrice($deliveryCost) ?></span>
              </div>

              <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 900; color: #fff; border-top: 1px solid var(--border-subtle); padding-top: 12px; margin-top: 4px;">
                <span>ИТОГО:</span>
                <span style="color: var(--neon-lime); text-shadow: var(--neon-lime-glow);"><?= formatPrice($total) ?></span>
              </div>
            </div>

            <button type="submit" class="btn-drawer-checkout" style="margin-top: 24px;" id="btnSubmitOrder">
              <span>ПОДТВЕРДИТЬ И ЗАКАЗАТЬ</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </button>

            <div id="orderErrorMsg" style="margin-top: 12px; font-family: var(--font-mono); font-size: 11px; color: #f43f5e; text-align: center;"></div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Order Success Modal -->
<div class="modal-overlay" id="orderSuccessModal">
  <div class="modal-card" style="text-align: center;">
    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(204, 255, 0, 0.1); border: 2px solid var(--neon-lime); margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; color: var(--neon-lime); font-size: 28px;">
      ✓
    </div>
    <h3 style="font-family: var(--font-display); font-size: 22px; color: #fff; margin-bottom: 8px;">ЗАКАЗ УСПЕШНО ОФОРМЛЕН!</h3>
    <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 16px;">
      Номер вашего заказа: <strong id="successOrderNum" style="color: var(--neon-cyan); font-family: var(--font-mono); font-size: 18px;">BPM-XXXXX</strong>
    </p>
    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 24px;">
      Менеджер саунд-системы BPM свяжется с вами в Telegram для подтверждения параметров отправки. Заказ уже появился в вашем личном кабинете.
    </p>
    <div style="display: flex; gap: 12px; justify-content: center;">
      <a href="profile.php" class="hero-cta-btn" style="padding: 12px 24px; font-size: 11px;">В ЛИЧНЫЙ КАБИНЕТ</a>
      <a href="catalog.php" class="hero-cta-btn" style="padding: 12px 24px; font-size: 11px; background: transparent; color: #fff; border: 1px solid var(--border-neon);">В КАТАЛОГ</a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('checkoutOrderForm');
  const errorMsg = document.getElementById('orderErrorMsg');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      errorMsg.textContent = '';
      const formData = new FormData(form);

      fetch('api/orders.php?action=create', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('successOrderNum').textContent = data.order_number;
          openModal('orderSuccessModal');
        } else {
          errorMsg.textContent = data.message || 'Ошибка оформления заказа';
        }
      })
      .catch(err => {
        errorMsg.textContent = 'Ошибка сети. Попробуйте еще раз.';
      });
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
