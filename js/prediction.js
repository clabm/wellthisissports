/**
 * prediction.js
 * Well This Is Sports — confidence meter animation, mobile nav, newsletter, share.
 */

(function () {
  'use strict';

  function initMobileNav() {
    var trigger = document.querySelector('.wtis-masthead__mobile-toggle');
    var nav = document.getElementById('wtis-mobile-nav');
    if (!trigger || !nav) return;

    trigger.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      trigger.setAttribute(
        'aria-label',
        open ? 'Close menu' : 'Open menu'
      );
    });
  }

  function initConfidenceMeters() {
    var meters = document.querySelectorAll('.wtis-confidence-meter');
    if (!meters.length) return;

    meters.forEach(function (meter) {
      var fill = meter.querySelector('.wtis-confidence-meter__fill');
      if (!fill) return;

      var raw = meter.style.getPropertyValue('--confidence').trim();
      var target = parseFloat(raw);
      if (isNaN(target)) {
        target = parseFloat(
          window.getComputedStyle(meter).getPropertyValue('--confidence')
        );
      }
      if (isNaN(target)) target = 0;
      target = Math.max(0, Math.min(100, target));

      fill.style.transition = 'none';
      fill.style.width = '0%';

      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () {
          fill.style.transition =
            'width 0.85s cubic-bezier(0.22, 1, 0.36, 1)';
          fill.style.width = target + '%';
        });
      });
    });
  }

  function initNewsletterForm() {
    var forms = Array.prototype.slice.call(
      document.querySelectorAll('.wtis-nl-form')
    );

    forms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = form.querySelector('input[type="email"]');
        var btn = form.querySelector('button[type="submit"]');
        var email = input ? input.value.trim() : '';

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          if (input) input.focus();
          return;
        }

        if (btn) {
          btn.disabled = true;
          btn.textContent = 'Subscribing...';
        }

        var data = new FormData();
        data.append('action', 'wtis_newsletter_subscribe');
        data.append('email', email);
        data.append('nonce', wtisData.nonce);

        fetch(wtisData.ajaxUrl, { method: 'POST', body: data })
          .then(function (r) {
            return r.json();
          })
          .then(function (res) {
            if (res.success) {
              form.innerHTML =
                '<p class="wtis-nl-success">You\'re in. Check your inbox.</p>';
            } else {
              if (btn) {
                btn.disabled = false;
                btn.textContent = 'Subscribe free';
              }
              var errEl =
                form.querySelector('.wtis-nl-error') ||
                document.createElement('p');
              errEl.className = 'wtis-nl-error';
              errEl.textContent =
                res.data && res.data.message
                  ? res.data.message
                  : 'Something went wrong.';
              if (!form.contains(errEl)) form.appendChild(errEl);
            }
          })
          .catch(function () {
            if (btn) {
              btn.disabled = false;
              btn.textContent = 'Subscribe free';
            }
          });
      });
    });
  }

  function initMastheadScroll() {
    var masthead = document.querySelector('.wtis-masthead');
    if (!masthead) return;

    var threshold = 8;

    function update() {
      if (window.scrollY > threshold) {
        masthead.classList.add('is-scrolled');
      } else {
        masthead.classList.remove('is-scrolled');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function initShare() {
    var btns = Array.prototype.slice.call(
      document.querySelectorAll('.wtis-share__btn')
    );
    if (!btns.length) return;

    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (navigator.share) {
          navigator
            .share({ title: document.title, url: window.location.href })
            .catch(function () {});
          return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard
            .writeText(window.location.href)
            .then(function () {
              showCopied(btn);
            })
            .catch(function () {
              execCopy(btn);
            });
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
      try {
        document.execCommand('copy');
      } catch (e) {}
      document.body.removeChild(el);
      showCopied(btn);
    }

    function showCopied(btn) {
      btn.classList.add('copied');
      setTimeout(function () {
        btn.classList.remove('copied');
      }, 2000);
    }
  }

  function init() {
    initMobileNav();
    initMastheadScroll();
    initConfidenceMeters();
    initNewsletterForm();
    initShare();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
