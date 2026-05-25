// js/script.js - Oli's SelfieTea & Coffee

document.addEventListener('DOMContentLoaded', () => {

  // ── CATEGORY TAB FILTERING ──
  const tabs = document.querySelectorAll('.cat-tab');
  const cards = document.querySelectorAll('.menu-card');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filter = tab.dataset.filter;

      cards.forEach((card, i) => {
        const match = filter === 'all' || card.dataset.sub === filter;
        card.style.display = match ? 'block' : 'none';
        if (match) {
          card.style.animationDelay = (i * 0.05) + 's';
          card.classList.remove('fade-in-up');
          void card.offsetWidth; // reflow
          card.classList.add('fade-in-up');
        }
      });
    });
  });

  // ── ALERT AUTO-DISMISS ──
  const alerts = document.querySelectorAll('.alert-dismissible');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 3500);
  });

  // ── LOGIN FORM PASSWORD TOGGLE ──
  const pwToggle = document.getElementById('pwToggle');
  const pwInput  = document.getElementById('password');
  if (pwToggle && pwInput) {
    pwToggle.addEventListener('click', () => {
      const isText = pwInput.type === 'text';
      pwInput.type = isText ? 'password' : 'text';
      pwToggle.innerHTML = isText
        ? '<i class="bi bi-eye"></i>'
        : '<i class="bi bi-eye-slash"></i>';
    });
  }

  // ── CONFIRM DELETE ──
  const deleteForms = document.querySelectorAll('.delete-form');
  deleteForms.forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirm('Are you sure you want to delete this item?')) {
        e.preventDefault();
      }
    });
  });

  // ── ACTIVE NAV HIGHLIGHT ──
  const currentPage = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });

  // ── SMOOTH SCROLL ──
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      e.preventDefault();
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

});

// ── TOGGLE AVAILABILITY (Admin) ──
function toggleAvail(id) {
  if (!confirm('Toggle availability for this item?')) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'menu_manage.php';
  const input1 = document.createElement('input');
  input1.name = 'action'; input1.value = 'toggle';
  const input2 = document.createElement('input');
  input2.name = 'id'; input2.value = id;
  form.appendChild(input1);
  form.appendChild(input2);
  document.body.appendChild(form);
  form.submit();
}
