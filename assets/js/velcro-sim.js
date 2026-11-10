/**
 * BPM Interactive Velcro Print Simulator
 * Lets customers customize hoodies by snapping swappable velcro patches onto them
 */

document.addEventListener('DOMContentLoaded', () => {
  initVelcroSimulator();
});

function initVelcroSimulator() {
  const targetZone = document.getElementById('simVelcroTarget');
  const printChoices = document.querySelectorAll('.sim-print-choice');
  const comboPriceEl = document.getElementById('simComboPrice');
  const addComboBtn = document.getElementById('btnAddComboToCart');

  if (!targetZone || !printChoices.length) return;

  let currentPrintId = null;
  let currentPrintPrice = 0;
  const hoodieId = targetZone.dataset.hoodieId || 1;
  const hoodiePrice = parseFloat(targetZone.dataset.hoodiePrice || 8900);

  printChoices.forEach(choice => {
    choice.addEventListener('click', () => {
      printChoices.forEach(c => c.classList.remove('active'));
      choice.classList.add('active');

      const printImg = choice.dataset.img;
      const printTitle = choice.dataset.title;
      currentPrintPrice = parseFloat(choice.dataset.price);
      currentPrintId = choice.dataset.id;

      // Update target patch
      targetZone.classList.add('has-print');
      targetZone.innerHTML = `
        <img src="${printImg}" alt="${printTitle}" class="sim-velcro-patch" />
      `;

      // Update combo price (applying combo 30% discount on print!)
      const discountedPrintPrice = currentPrintPrice * 0.7; // 30% off print when bought with hoodie
      const totalCombo = hoodiePrice + discountedPrintPrice;
      const oldTotal = hoodiePrice + currentPrintPrice;

      if (comboPriceEl) {
        comboPriceEl.innerHTML = `
          <span style="color: var(--neon-lime);">${Math.round(totalCombo).toLocaleString('ru-RU')} ₽</span>
          <span style="font-size: 13px; color: var(--text-dim); text-decoration: line-through; margin-left: 8px;">${Math.round(oldTotal).toLocaleString('ru-RU')} ₽</span>
          <span style="display: block; font-size: 11px; color: var(--neon-cyan); margin-top: 2px;">⚡ Скидка 30% на принт при покупке с худи</span>
        `;
      }
    });
  });

  // Add Combo to Cart button
  if (addComboBtn) {
    addComboBtn.addEventListener('click', () => {
      // Add hoodie
      window.quickAddToCart(hoodieId, 'L', 1);

      // If print selected, add print after short delay
      if (currentPrintId) {
        setTimeout(() => {
          window.quickAddToCart(currentPrintId, 'Стандарт (20x15 см)', 1);
        }, 300);
      }
    });
  }
}
