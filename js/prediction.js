/**
 * prediction.js
 * Well This Is Sports — prediction component.
 *
 * Sprint 2 implementation:
 *   - Confidence meter animation
 *   - Mobile nav toggle
 *   - Newsletter form submit
 *   - Share button (Web Share API + clipboard fallback)
 */

(function () {
  'use strict';

  // ── Mobile nav toggle ────────────────────────────────────────

  function initMobileNav() {
    var trigger = document.querySelector('.wtis-masthead__mobile-toggle');
    var nav     = document.querySelector('.wtis-mobile-nav');
    if (!trigger || !nav) return;

    trigger.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('open');
      trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // ── Newsletter form ──────────────────────────────────────────

  function initNewsletterForm() {
    var forms = Array.from(document.querySelectorAll('.wtis-nl-form'));

    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = form.querySelector('input[type="email"]');
        var btn   = form.querySelector('button[type="submit"]');
        var email = input ? input.value.trim() : '';

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          if (input) input.focus();
          return;
        }

        if (btn) {
          btn.disabled    = true;
          btn.textContent = 'Subscribing...';
        }

        var data = new FormData();
        data.append('action', 'wtis_newsletter_subscribe');
        data.append('email', email);
        data.append('nonce', wtisData.nonce);

        fetch(wtisData.ajaxUrl, { method: 'POST', body: data })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            if (res.success) {
              form.innerHTML = '<p class="wtis-nl-success">You\'re in. Check your inbox.</p>';
            } else {
              if (btn) { btn.disabled = false; btn.textContent = 'Subscribe free'; }
              var errEl = form.querySelector('.wtis-nl-error') || document.createElement('p');
              errEl.className = 'wtis-nl-error';
              errEl.textContent = (res.data && res.data.message) ? res.data.message : 'Something went wrong.';
              if (!form.contains(errEl)) form.appendChild(errEl);
            }
          })
          .catch(function () {
            if (btn) { btn.disabled = false; btn.textContent = 'Subscribe free'; }
          });
      });
    });
  }

  // ── Share button ─────────────────────────────────────────────

  function initShare() {
    var btns = Array.from(document.querySelectorAll('.wtis-share__btn'));
    if (!btns.length) return;

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (navigator.share) {
          navigator.share({ title: document.title, url: window.location.href }).catch(function () {});
          return;
        }
        // Clipboard fallback
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(window.location.href)
            .then(function () { showCopied(btn); })
            .catch(function () { execCopy(btn); });
        } else {
          execCopy(btn);
        }
      });
    });

    function execCopy(btn) {
      var el = document.createElement('textarea');
      el.value = window.location.href;
      el.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
      document.body.appendChild(el);
      el.select();
      try { document.execCommand('copy'); } catch (e) {}
      document.body.removeChild(el);
      showCopied(btn);
    }

    function showCopied(btn) {
      btn.classList.add('copied');
      setTimeout(function () { btn.classList.remove('copied'); }, 2000);
    }
  }

  // ── Init ─────────────────────────────────────────────────────

  function init() {
    initMobileNav();
    initNewsletterForm();
    initShare();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
