<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="product-page-section">
  <div class="container" style="max-width: 960px;">
    <div class="section-head" style="text-align: left; margin-bottom: 30px;">
      <div class="section-pill" style="border-color: var(--neon-cyan); color: var(--neon-cyan); background: rgba(0, 240, 255, 0.1);">ФИНАНСОВЫЙ ГАЙД // 54-ФЗ</div>
      <h1 class="section-title" style="font-size: 36px;">КАК ПОДКЛЮЧИТЬ <span class="cyan">ОНЛАЙН-ОПЛАТУ</span> К МАГАЗИНУ BPM</h1>
      <p class="section-desc">
        Пошаговое руководство: как легально и технически настроить прием платежей (карты, СБП, T-Pay) для продажи одежды и принтов.
      </p>
    </div>

    <!-- Step 1: Legal Status -->
    <div class="checkout-card" style="margin-bottom: 24px;">
      <h2 class="checkout-card-title">
        <span style="color: var(--neon-cyan);">ШАГ 1.</span> ВЫБОР ЮРИДИЧЕСКОГО СТАТУСА
      </h2>
      <p style="font-size: 14px; color: var(--text-muted); line-height: 1.7; margin-bottom: 16px;">
        Для подключения официального интернет-эквайринга платежные агрегаторы требуют зарегистрированный статус:
      </p>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div style="padding: 16px; background: rgba(14, 14, 24, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
          <strong style="color: var(--neon-lime); font-family: var(--font-display); font-size: 13px;">Самозанятость (НПД)</strong>
          <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px; line-height: 1.6;">
            <strong>Плюсы:</strong> Открытие за 10 минут в приложении «Мой налог». Налог 4-6%. Нет страховых взносов.<br>
            <strong>Важно:</strong> Подходит, если вы сами производите или кастомизируете одежду/принты (не перепродажа чужого готового). Лимит дохода: 2.4 млн ₽ в год.
          </p>
        </div>
        <div style="padding: 16px; background: rgba(14, 14, 24, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
          <strong style="color: var(--neon-violet); font-family: var(--font-display); font-size: 13px;">ИП (УСН 6% или «Доходы минус Расходы»)</strong>
          <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px; line-height: 1.6;">
            <strong>Плюсы:</strong> Нет лимита в 2.4 млн, можно перепродавать товары, нанимать швей и менеджеров, подключать любые банки и кассы.<br>
            <strong>Идеально для:</strong> Масштабирования бренда одежды BPM.
          </p>
        </div>
      </div>
    </div>

    <!-- Step 2: Payment Gateway Comparison -->
    <div class="checkout-card" style="margin-bottom: 24px;">
      <h2 class="checkout-card-title">
        <span style="color: var(--neon-cyan);">ШАГ 2.</span> ВЫБОР ПЛАТЕЖНОГО ШЛЮЗА
      </h2>
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <!-- YooKassa -->
        <div style="padding: 18px; background: rgba(14, 14, 24, 0.8); border: 1px solid var(--border-neon); border-radius: var(--radius-sm);">
          <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <strong style="font-family: var(--font-display); font-size: 16px; color: #fff;">1. ЮKassa (Рекомендуется для BPM)</strong>
            <span style="font-family: var(--font-mono); font-size: 12px; color: var(--neon-lime);">Комиссия ~2.8% – 3.5%</span>
          </div>
          <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px; line-height: 1.6;">
            Самый популярный сервис в РФ. Принимает карты МИР/Visa/Mastercard, СБП по QR-коду, SberPay, T-Pay, Mir Pay, ЮMoney. Работает как с ИП/ООО, так и с самозанятыми. Предоставляет готовый модуль «Чеки от ЮKassa» по 54-ФЗ.
          </p>
        </div>

        <!-- Tinkoff Kassa -->
        <div style="padding: 18px; background: rgba(14, 14, 24, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
          <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <strong style="font-family: var(--font-display); font-size: 16px; color: #fff;">2. Т-Банк Оплата (Тинькофф Эквайринг)</strong>
            <span style="font-family: var(--font-mono); font-size: 12px; color: var(--neon-cyan);">СБП от 0.4% / Карты от 2.49%</span>
          </div>
          <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px; line-height: 1.6;">
            Мгновенное зачисление средств на расчетный счет день-в-день. Отличный виджет оплаты, поддержка T-Pay в 1 клик со смартфона без ввода реквизитов карты.
          </p>
        </div>

        <!-- Robokassa -->
        <div style="padding: 18px; background: rgba(14, 14, 24, 0.8); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
          <div style="display: flex; justify-content: space-between; align-items: baseline;">
            <strong style="font-family: var(--font-display); font-size: 16px; color: #fff;">3. Robokassa («Робочеки»)</strong>
            <span style="font-family: var(--font-mono); font-size: 12px; color: var(--neon-violet);">Комиссия ~3.5% – 3.9%</span>
          </div>
          <p style="font-size: 13px; color: var(--text-muted); margin-top: 8px; line-height: 1.6;">
            Главная фишка — не нужно покупать отдельную онлайн-кассу. Robokassa сама фискализирует чеки и отправляет их в налоговую и покупателю на почту («Робочеки»).
          </p>
        </div>
      </div>
    </div>

    <!-- Step 3: Architecture & Webhooks -->
    <div class="checkout-card" style="margin-bottom: 24px;">
      <h2 class="checkout-card-title">
        <span style="color: var(--neon-cyan);">ШАГ 3.</span> КАК ЭТО РАБОТАЕТ В КОДЕ
      </h2>
      <p style="font-size: 13px; color: var(--text-muted); line-height: 1.7; margin-bottom: 16px;">
        Схема работы безопасного онлайн-платежа:
      </p>
      <ol style="padding-left: 20px; font-size: 13px; color: #e2e8f0; line-height: 1.9; margin-bottom: 20px;">
        <li>Покупатель нажимает «Оплатить заказ».</li>
        <li>Ваш сервер формирует JSON с суммой, номером заказа (например, <code>BPM-78421</code>) и описанием товаров.</li>
        <li>Сервер отправляет запрос в API эквайринга (например, в ЮKassa) и получает в ответ <code>confirmation_url</code> (ссылку на страницу защищенной оплаты банка).</li>
        <li>Сайт перенаправляет покупателя на эту ссылку. Покупатель платит (карта, СБП, Mir Pay).</li>
        <li>Банк отправляет <strong>Webhook</strong> (фоновое HTTP-уведомление <code>payment.succeeded</code>) на ваш URL <code>/api/payment_webhook.php</code>.</li>
        <li>Ваш сервер проверяет цифровую подпись, находит заказ по ID и меняет его статус на <code>assembled</code> («Оплачен / Собирается»).</li>
      </ol>

      <h3 style="font-family: var(--font-mono); font-size: 13px; color: var(--neon-lime); margin-bottom: 10px;">
        Пример кода для ЮKassa (PHP REST API):
      </h3>
      <pre style="background: #05050a; padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle); overflow-x: auto; font-family: var(--font-mono); font-size: 12px; color: #a5f3fc; line-height: 1.5;">
$ch = curl_init('https://api.yookassa.ru/v3/payments');
$data = [
    'amount' => ['value' => number_format($totalPrice, 2, '.', ''), 'currency' => 'RUB'],
    'confirmation' => ['type' => 'redirect', 'return_url' => 'http://bpm/profile.php?order=success'],
    'capture' => true,
    'description' => 'Заказ ' . $orderNumber . ' в магазине BPM',
    'metadata' => ['order_id' => $orderId]
];
curl_setopt($ch, CURLOPT_USERPWD, $shopId . ':' . $secretApiKey);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Idempotence-Key: ' . uniqid('', true)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = json_decode(curl_exec($ch), true);
$paymentUrl = $res['confirmation']['confirmation_url'];
header('Location: ' . $paymentUrl);
      </pre>
    </div>

    <!-- Step 4: Current Demo Setup Summary -->
    <div class="checkout-card" style="border-color: var(--neon-violet);">
      <h3 style="font-family: var(--font-display); font-size: 16px; color: var(--neon-violet); margin-bottom: 10px;">
        ⚡ ЧТО СДЕЛАНО В BPM ПРЯМО СЕЙЧАС:
      </h3>
      <p style="font-size: 13px; color: var(--text-muted); line-height: 1.7;">
        • Реализован полноценный модуль чекаута со сбором контактов, города, адреса ПВЗ СДЭК и Telegram никнейма.<br>
        • Поддерживаются режимы «Оплата при получении (СДЭК)» и «СБП Демо».<br>
        • Заказы мгновенно записываются в базу данных <code>bpm</code>, списывают остатки и привязываются к профилю.<br>
        • Когда вы решите подключить реальный эквайринг, вам потребуется всего лишь вставить свои ключи <code>shopId</code> и <code>secretKey</code>.
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
