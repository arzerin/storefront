(() => {
  const form = document.getElementById('price-manager-form');
  if (!form) return;

  const rows = Array.from(form.querySelectorAll('.price-manager-row'));
  const reviewed = document.getElementById('price-reviewed');
  const count = document.getElementById('price-dirty-count');
  const dialog = document.getElementById('price-review-dialog');
  const list = document.getElementById('price-review-list');

  const changesFor = row => Array.from(row.querySelectorAll('[data-price-field]'))
    .filter(field => field.value !== field.dataset.original)
    .map(field => ({label: field.dataset.fieldLabel, before: field.dataset.original, after: field.value}));

  function updateCount() {
    rows.forEach(row => row.classList.toggle('is-dirty', changesFor(row).length > 0));
    const changed = rows.filter(row => changesFor(row).length).length;
    count.textContent = changed ? `${changed} product${changed === 1 ? '' : 's'} changed` : 'No changes';
    count.className = `badge ${changed ? 'text-bg-warning' : 'text-bg-secondary'}`;
  }

  function validateSaleDates() {
    for (const row of rows) {
      const regular = row.querySelector('[data-field-label="Regular price"]');
      const sale = row.querySelector('[data-field-label="Sale price"]');
      const starts = row.querySelector('[data-field-label="Sale starts"]');
      const ends = row.querySelector('[data-field-label="Sale ends"]');
      sale.setCustomValidity('');
      ends.setCustomValidity('');
      if (sale.value && Number(sale.value) >= Number(regular.value)) {
        sale.setCustomValidity('Sale price must be below the regular price.');
      } else if (!sale.value && (starts.value || ends.value)) {
        sale.setCustomValidity('Enter a sale price for the scheduled dates.');
      }
      if (starts.value && ends.value && ends.value <= starts.value) {
        ends.setCustomValidity('Sale end must be after its start.');
      }
    }
    return form.reportValidity();
  }

  function valueForReview(label, value) {
    if (!value) return '—';
    return label.includes('price') ? `$${Number(value).toFixed(2)}` : value;
  }

  function showReview(event) {
    if (reviewed.value === '1') return;
    event.preventDefault();
    if (!validateSaleDates()) return;
    const changedRows = rows.map(row => ({name: row.dataset.productName, changes: changesFor(row)}))
      .filter(row => row.changes.length);
    if (!changedRows.length) {
      count.textContent = 'No changes to review';
      return;
    }
    list.replaceChildren();
    for (const row of changedRows) {
      const item = document.createElement('div');
      item.className = 'price-review-item';
      const heading = document.createElement('strong');
      heading.textContent = row.name;
      item.append(heading);
      for (const change of row.changes) {
        const line = document.createElement('div');
        line.className = 'price-review-change';
        line.textContent = `${change.label}: ${valueForReview(change.label, change.before)} → ${valueForReview(change.label, change.after)}`;
        item.append(line);
      }
      list.append(item);
    }
    dialog.showModal();
  }

  form.addEventListener('submit', showReview);
  form.addEventListener('input', () => { reviewed.value = '0'; updateCount(); });
  form.addEventListener('change', () => { reviewed.value = '0'; updateCount(); });
  document.getElementById('price-review-close').addEventListener('click', () => dialog.close());
  document.getElementById('price-review-back').addEventListener('click', () => dialog.close());
  document.getElementById('price-review-confirm').addEventListener('click', () => {
    reviewed.value = '1';
    dialog.close();
    form.requestSubmit();
  });
  updateCount();
})();
