/* ═══════════════════════════════════════════════════════════
   RÉEMPLOI BTP — SCRIPT.JS
   Role selection · Login form · Animations · Catalogue
═══════════════════════════════════════════════════════════ */

'use strict';

// ── ROLE CONFIG ────────────────────────────────────────────
const ROLES = {
  entreprise: {
    icon: '🏢',
    label: 'Entreprise BTP',
    sub: 'Accédez à votre espace de publication des matériaux',
    registerUrl: 'register.php?role=entreprise'
  },
  artisan: {
    icon: '🔨',
    label: 'Artisan / Particulier',
    sub: 'Accédez au catalogue et réservez des matériaux',
    registerUrl: 'register.php?role=artisan'
  },
  admin: {
    icon: '🛡️',
    label: 'Administrateur',
    sub: 'Accédez au tableau de bord d\'administration',
    registerUrl: '#'
  }
};

// ── NAVBAR SCROLL ──────────────────────────────────────────
(function initNavScroll() {
  const navbar = document.getElementById('navbar');
  if (!navbar) return;
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  }, { passive: true });
})();

// ── MOBILE MENU ────────────────────────────────────────────
function toggleMenu() {
  const menu = document.getElementById('navMobile');
  if (!menu) return;
  menu.classList.toggle('open');
}

// Close mobile menu on outside click
document.addEventListener('click', (e) => {
  const burger = document.getElementById('navBurger');
  const menu = document.getElementById('navMobile');
  if (menu && burger && !burger.contains(e.target) && !menu.contains(e.target)) {
    menu.classList.remove('open');
  }
});

// ── ROLE SELECTION ─────────────────────────────────────────
let currentRole = null;

function selectRole(role) {
  currentRole = role;
  const cfg = ROLES[role];
  if (!cfg) return;

  // Highlight selected card
  document.querySelectorAll('.role-card').forEach(card => {
    card.classList.remove('active');
  });
  const activeCard = document.getElementById('card-' + role);
  if (activeCard) activeCard.classList.add('active');

  // Update login box content
  const loginRoleBadge = document.getElementById('loginRoleBadge');
  const loginSub       = document.getElementById('loginSub');
  const loginRoleInput = document.getElementById('loginRoleInput');
  const registerLink   = document.getElementById('registerLink');

  if (loginRoleBadge) loginRoleBadge.textContent = cfg.icon + ' ' + cfg.label;
  if (loginSub)       loginSub.textContent = cfg.sub;
  if (loginRoleInput) loginRoleInput.value = role;
  if (registerLink)   registerLink.href = cfg.registerUrl;

  // Show login box
  const loginBox = document.getElementById('loginBox');
  if (loginBox) {
    loginBox.style.display = 'block';
    // Small delay for paint before animation
    requestAnimationFrame(() => {
      loginBox.querySelector('.login-glass-inner').style.animation = 'none';
      requestAnimationFrame(() => {
        loginBox.querySelector('.login-glass-inner').style.animation = '';
      });
    });
    // Smooth scroll to login box
    setTimeout(() => {
      loginBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 100);
  }

  // Focus email input
  setTimeout(() => {
    const emailInput = document.getElementById('email');
    if (emailInput) emailInput.focus();
  }, 400);
}

function closeLogin() {
  const loginBox = document.getElementById('loginBox');
  if (loginBox) {
    loginBox.style.display = 'none';
  }
  // Deselect cards
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
  currentRole = null;
}

// ── SCROLL TO LOGIN ────────────────────────────────────────
function scrollToLogin() {
  const roleSection = document.getElementById('role-section');
  if (roleSection) {
    roleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

// ── SMOOTH SCROLL FOR ANCHOR LINKS ─────────────────────────
function smoothScroll(selector) {
  const el = document.querySelector(selector);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── PASSWORD TOGGLE ────────────────────────────────────────
function togglePw() {
  const pw = document.getElementById('password');
  if (!pw) return;
  pw.type = pw.type === 'password' ? 'text' : 'password';
}

// ── ECO COUNTER ANIMATION ──────────────────────────────────
(function initCounters() {
  const counters = document.querySelectorAll('.eco-val[data-target]');
  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseInt(el.dataset.target, 10);
      animateCounter(el, target);
      observer.unobserve(el);
    });
  }, { threshold: 0.3 });

  counters.forEach(c => observer.observe(c));

  function animateCounter(el, target) {
    const duration = 1800;
    const start = performance.now();
    function tick(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const ease = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      el.textContent = Math.round(ease * target).toLocaleString('fr-FR');
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
})();

// ── CATALOGUE FILTER & SEARCH ──────────────────────────────
(function initCatalogue() {
  const filterTabs  = document.querySelectorAll('.filter-tab');
  const searchInput = document.getElementById('catalogueSearch');
  const matCards    = document.querySelectorAll('.mat-card');
  if (!filterTabs.length && !searchInput) return;

  let activeFilter = 'all';

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeFilter = tab.dataset.filter || 'all';
      applyFilter();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', applyFilter);
  }

  function applyFilter() {
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    matCards.forEach(card => {
      const cat   = (card.dataset.cat || '').toLowerCase();
      const title = card.querySelector('.mat-title')?.textContent.toLowerCase() || '';
      const meta  = card.querySelector('.mat-meta')?.textContent.toLowerCase() || '';

      const matchCat   = activeFilter === 'all' || cat === activeFilter.toLowerCase();
      const matchQuery = !query || title.includes(query) || meta.includes(query) || cat.includes(query);

      card.style.display = (matchCat && matchQuery) ? '' : 'none';
    });

    // Empty state
    const grid = document.querySelector('.materials-grid');
    if (!grid) return;
    let emptyMsg = grid.querySelector('.empty-state');
    const visible = [...matCards].filter(c => c.style.display !== 'none');
    if (visible.length === 0) {
      if (!emptyMsg) {
        emptyMsg = document.createElement('p');
        emptyMsg.className = 'empty-state';
        emptyMsg.style.cssText = 'grid-column:1/-1; text-align:center; color:var(--gray-400); padding:3rem; font-size:0.95rem;';
        emptyMsg.textContent = 'Aucun matériau trouvé pour cette recherche.';
        grid.appendChild(emptyMsg);
      }
    } else if (emptyMsg) {
      emptyMsg.remove();
    }
  }
})();

// ── RESERVATION BUTTON HANDLER ─────────────────────────────
document.addEventListener('click', (e) => {
  if (!e.target.classList.contains('btn-reserver')) return;
  const matId = e.target.dataset.id;
  if (!matId) return;

  // Redirect to reservation (handled server-side via form submission)
  const confirmed = confirm('Confirmer la réservation de ce matériau ?');
  if (confirmed) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'reserver.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'materiau_id';
    input.value = matId;
    form.appendChild(input);

    // CSRF token if present
    const csrf = document.querySelector('meta[name="csrf-token"]');
    if (csrf) {
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = 'csrf_token';
      csrfInput.value = csrf.content;
      form.appendChild(csrfInput);
    }

    document.body.appendChild(form);
    form.submit();
  }
});

// ── HOVER CARD ANIMATION ───────────────────────────────────
(function initCardHovers() {
  document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
      card.style.transition = 'transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease';
    });
  });
})();

// ── FADE IN ON SCROLL ──────────────────────────────────────
(function initScrollReveal() {
  const targets = document.querySelectorAll('.feature-item, .step, .pf-card, .eco-item, .mat-card');
  if (!targets.length || !('IntersectionObserver' in window)) return;

  const obs = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      el.style.opacity = '0';
      el.style.transform = 'translateY(20px)';
      el.style.transition = `opacity 0.5s ease ${i * 0.07}s, transform 0.5s ease ${i * 0.07}s`;
      requestAnimationFrame(() => {
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      });
      obs.unobserve(el);
    });
  }, { threshold: 0.1 });

  targets.forEach(t => obs.observe(t));
})();

// ── FORM VALIDATION FEEDBACK ───────────────────────────────
(function initFormValidation() {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    const email = form.querySelector('#email');
    const password = form.querySelector('#password');
    let valid = true;

    [email, password].forEach(input => {
      if (!input) return;
      if (!input.value.trim()) {
        input.style.borderColor = '#e74c3c';
        input.style.boxShadow = '0 0 0 3px rgba(231,76,60,0.1)';
        valid = false;
      } else {
        input.style.borderColor = '';
        input.style.boxShadow = '';
      }
    });

    if (!valid) {
      e.preventDefault();
      return;
    }

    // Loading state
    const btn = form.querySelector('#btnLogin');
    if (btn) {
      btn.innerHTML = '<span>Connexion en cours...</span>';
      btn.disabled = true;
    }
  });
})();

// ── NOTIFICATION / TOAST ───────────────────────────────────
function showToast(message, type = 'success') {
  let toast = document.getElementById('toast-notification');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast-notification';
    toast.style.cssText = `
      position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
      padding: 0.9rem 1.5rem; border-radius: 10px;
      font-family: var(--font-body); font-size: 0.875rem; font-weight: 500;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      transform: translateY(100px); opacity: 0;
      transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
      max-width: 320px;
    `;
    document.body.appendChild(toast);
  }

  const colors = {
    success: { bg: '#d4edda', color: '#155724', border: '#c3e6cb' },
    error:   { bg: '#f8d7da', color: '#721c24', border: '#f5c6cb' },
    info:    { bg: '#d1ecf1', color: '#0c5460', border: '#bee5eb' }
  };
  const c = colors[type] || colors.success;
  toast.style.background = c.bg;
  toast.style.color = c.color;
  toast.style.border = `1px solid ${c.border}`;
  toast.textContent = message;

  requestAnimationFrame(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
  });

  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
  }, 3500);
}

// Check for URL params (success/error messages)
(function checkUrlParams() {
  const params = new URLSearchParams(window.location.search);
  if (params.get('success') === 'reservation') {
    showToast('✅ Réservation effectuée avec succès !', 'success');
  }
  if (params.get('success') === 'publication') {
    showToast('✅ Matériau publié avec succès !', 'success');
  }
  if (params.get('error') === 'access') {
    showToast('❌ Accès refusé. Veuillez vous connecter.', 'error');
  }
})();
