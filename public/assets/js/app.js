document.addEventListener('DOMContentLoaded', function () {
  // Sidebar toggle on mobile
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

  // Auto-close alerts after 5 seconds
  document.querySelectorAll('.alert-dismissible').forEach(alert => {
    setTimeout(() => {
      const closeBtn = alert.querySelector('.btn-close');
      if (closeBtn) closeBtn.click();
    }, 5000);
  });
});

function deleteItem(url) {
  if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const csrf = document.querySelector('input[name="csrf_token"]');
    if (csrf) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'csrf_token';
      input.value = csrf.value;
      form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
  }
}

function addItem() {
  const container = document.getElementById('items-container');
  const rows = container.querySelectorAll('.item-row');
  const idx = rows.length;
  const template = document.getElementById('item-template').innerHTML;
  container.insertAdjacentHTML('beforeend', template.replace(/{{idx}}/g, idx));
  if (typeof window.refreshSelect2 === 'function') window.refreshSelect2();
}

function removeItem(btn) {
  btn.closest('.item-row').remove();
  recalculateTotal();
}

function updateItemTotal(row) {
  const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
  const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
  const total = qty * price;
  const totalEl = row.querySelector('.item-total');
  if (totalEl) totalEl.textContent = total.toFixed(2);
  recalculateTotal();
}

function recalculateTotal() {
  let total = 0;
  document.querySelectorAll('.item-total').forEach(el => {
    total += parseFloat(el.textContent) || 0;
  });
  const totalEl = document.getElementById('grand-total');
  if (totalEl) totalEl.textContent = total.toFixed(2);
}
