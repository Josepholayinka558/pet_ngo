(function () {
  'use strict';

  const hamburger = document.getElementById('hamburger');
  const navLinks = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('mobile-open');
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      const t = document.querySelector(this.getAttribute('href'));
      if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

  const imgs = document.querySelectorAll('img[data-src]');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const i = entry.target;
          i.src = i.getAttribute('data-src');
          i.removeAttribute('data-src');
          io.unobserve(i);
        }
      });
    });
    imgs.forEach(function (i) { io.observe(i); });
  } else {
    imgs.forEach(function (i) { i.src = i.getAttribute('data-src'); });
  }

  document.querySelectorAll('img').forEach(function (i) {
    if (!i.alt) {
      const src = (i.src || i.getAttribute('data-src') || '').split('/').pop().split('?')[0];
      i.alt = src.replace(/[-_]+/g, ' ').replace(/\.\w+$/, '') || 'Image';
    }
  });

  const statNums = document.querySelectorAll('.impact-num[data-count]');
  if (statNums.length && 'IntersectionObserver' in window) {
    const so = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          so.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    statNums.forEach(function (s) { so.observe(s); });
  }

  function animateCount(el) {
    const targetText = el.getAttribute('data-count') || el.textContent;
    const numeric = parseFloat(targetText.replace(/[^\d.]/g, ''));
    const suffix = targetText.replace(/[\d.,]/g, '');
    if (isNaN(numeric)) return;
    const duration = 1500;
    const start = performance.now();
    function tick(t) {
      const p = Math.min((t - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      const val = numeric * eased;
      const formatted = (numeric >= 100) ? Math.round(val).toLocaleString() : val.toFixed(1);
      el.textContent = formatted + suffix;
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  const revealEls = document.querySelectorAll('.activity-card, .animal-card, .story-sm, .story-featured, .involve-card, .value-card, .animal-tile, .event-card, .team-member, .blog-card, .blog-card-sm, .tier-card');
  revealEls.forEach(function (el) {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
  });
  if ('IntersectionObserver' in window) {
    const ro = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          ro.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(function (el) { ro.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.style.opacity = '1'; el.style.transform = 'translateY(0)'; });
  }

  document.querySelectorAll('.needs-validation').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!f.checkValidity()) { e.preventDefault(); f.classList.add('was-validated'); }
    });
  });

  const forms = document.querySelectorAll('form[data-ajax]');
  forms.forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = f.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }
      fetch(f.action || '', { method: f.method || 'POST', body: new FormData(f) })
        .then(function (r) { return r.json().catch(function () { return { success: r.ok }; }); })
        .then(function (d) {
          if (d.success) {
            alert(d.message || 'Success!');
            if (d.redirect) location.href = d.redirect;
            f.reset();
          } else {
            alert(d.message || 'Something went wrong. Please try again.');
          }
        })
        .catch(function () { alert('Submission failed. Please try again.'); })
        .finally(function () { if (btn) { btn.disabled = false; btn.innerHTML = btn.getAttribute('data-label') || 'Submit'; } });
    });
  });
})();
