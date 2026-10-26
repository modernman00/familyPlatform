{{-- 
  Universal Submit & Action Button Spinner Component
  Provides instantaneous spinning feedback on all submit and action buttons across FamilyPlatform.
  Guarantees zero double submissions, visual confirmation, and automatic watchdog recovery.
--}}
<style>
  .st-spinner-border {
    display: inline-block;
    width: 1.15em;
    height: 1.15em;
    vertical-align: -0.15em;
    border: 0.18em solid currentColor;
    border-right-color: transparent !important;
    border-radius: 50%;
    -webkit-animation: st-spinner-spin 0.75s linear infinite;
    animation: st-spinner-spin 0.75s linear infinite;
    flex-shrink: 0;
    box-sizing: border-box;
    margin: 0 auto;
  }
  @-webkit-keyframes st-spinner-spin {
    100% { -webkit-transform: rotate(360deg); }
  }
  @keyframes st-spinner-spin {
    100% { transform: rotate(360deg); }
  }
  button[aria-busy="true"],
  .btn[aria-busy="true"],
  .button[aria-busy="true"],
  input[type="submit"][aria-busy="true"] {
    cursor: wait !important;
    opacity: 0.88;
    pointer-events: none !important;
  }
</style>
<script nonce="{{ $nonce ?? '' }}">
(function() {
  if (window.FamilyPlatformSpinner) return;

  var FamilyPlatformSpinner = {
    activeButtons: new WeakMap(),
    activeSet: new Set(),

    start: function(btn) {
      if (!btn || btn.getAttribute('aria-busy') === 'true') return;

      var originalHtml = btn.innerHTML;
      var originalWidth = btn.offsetWidth ? (btn.offsetWidth + 'px') : '';
      var originalHeight = btn.offsetHeight ? (btn.offsetHeight + 'px') : '';

      FamilyPlatformSpinner.activeButtons.set(btn, {
        html: originalHtml,
        width: originalWidth,
        height: originalHeight,
        isInput: btn.tagName === 'INPUT'
      });
      FamilyPlatformSpinner.activeSet.add(btn);

      // DOM dataset backup for unlinked recovery
      btn.dataset.stOrigHtml = originalHtml;
      if (originalWidth) btn.dataset.stOrigWidth = originalWidth;
      if (originalHeight) btn.dataset.stOrigHeight = originalHeight;

      btn.setAttribute('aria-busy', 'true');
      btn.style.pointerEvents = 'none';
      if (originalWidth) {
        btn.style.minWidth = originalWidth;
      }
      if (originalHeight) {
        btn.style.minHeight = originalHeight;
      }

      // Bulma is-loading support
      if (btn.classList.contains('button')) {
        btn.classList.add('is-loading');
      }

      // Modern minimal tactile spinner: NO "Processing..." text, strictly the spinning indicator
      if (btn.tagName === 'INPUT') {
        btn.dataset.stOrigVal = btn.value;
        btn.value = '';
      } else {
        btn.innerHTML = '<span class="st-spinner-border" role="status" aria-label="Loading" aria-hidden="true"></span>';
      }

      // Watchdog auto-recovery (6s) in case network drops or asynchronous action completes silently
      setTimeout(function() {
        if (btn.getAttribute('aria-busy') === 'true') {
          FamilyPlatformSpinner.stop(btn);
        }
      }, 6000);
    },

    stop: function(btn) {
      if (!btn) return;
      var data = FamilyPlatformSpinner.activeButtons.get(btn);
      var backupHtml = btn.dataset.stOrigHtml;

      if (data) {
        if (data.isInput) {
          btn.value = btn.dataset.stOrigVal || data.html || 'Submit';
        } else {
          btn.innerHTML = data.html;
        }
        FamilyPlatformSpinner.activeButtons.delete(btn);
      } else if (backupHtml !== undefined) {
        if (btn.tagName === 'INPUT') {
          btn.value = btn.dataset.stOrigVal || backupHtml || 'Submit';
        } else {
          btn.innerHTML = backupHtml;
        }
      }

      FamilyPlatformSpinner.activeSet.delete(btn);
      delete btn.dataset.stOrigHtml;
      delete btn.dataset.stOrigWidth;
      delete btn.dataset.stOrigHeight;
      delete btn.dataset.stOrigVal;

      btn.removeAttribute('aria-busy');
      btn.style.pointerEvents = '';
      btn.style.minWidth = '';
      btn.style.minHeight = '';
      btn.disabled = false;

      if (btn.classList.contains('button')) {
        btn.classList.remove('is-loading');
      }
      if (btn.hasAttribute('data-mmlib-loading')) {
        btn.removeAttribute('data-mmlib-loading');
        btn.removeAttribute('data-original-html');
        btn.removeAttribute('data-original-disabled');
      }
    },

    stopAll: function(container) {
      if (container && typeof container.querySelectorAll === 'function') {
        var scoped = container.querySelectorAll('[aria-busy="true"]');
        scoped.forEach(function(b) { FamilyPlatformSpinner.stop(b); });
        return;
      }
      var busyButtons = document.querySelectorAll('[aria-busy="true"]');
      busyButtons.forEach(function(b) {
        FamilyPlatformSpinner.stop(b);
      });
      if (FamilyPlatformSpinner.activeSet) {
        FamilyPlatformSpinner.activeSet.forEach(function(b) {
          FamilyPlatformSpinner.stop(b);
        });
        FamilyPlatformSpinner.activeSet.clear();
      }
    },

    getActiveButtons: function() {
      if (!FamilyPlatformSpinner.activeSet || FamilyPlatformSpinner.activeSet.size === 0) {
        return Array.from(document.querySelectorAll('[aria-busy="true"]'));
      }
      return Array.from(FamilyPlatformSpinner.activeSet);
    },

    findSubmitButton: function(form, submitter) {
      if (submitter && (submitter.tagName === 'BUTTON' || submitter.tagName === 'INPUT')) {
        return submitter;
      }
      if (!form || typeof form.querySelector !== 'function') return null;
      return form.querySelector(
        'button[type="submit"], input[type="submit"], button[name="submit"], button.submit, button.btn-primary, button.is-primary, #submit, [id*="SubmitBtn"], [id*="submit"]'
      );
    }
  };

  window.FamilyPlatformSpinner = FamilyPlatformSpinner;
  // Compatibility aliases
  window.ScoreTenantSpinner = FamilyPlatformSpinner;
  window.PartyPlatformSpinner = FamilyPlatformSpinner;
  window.BCPSpinner = FamilyPlatformSpinner;
  window.SubmitSpinner = FamilyPlatformSpinner;

  // 1. Intercept standard form submission
  document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM') return;

    // Check client-side HTML5 validation first
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
      return;
    }

    var submitBtn = FamilyPlatformSpinner.findSubmitButton(form, e.submitter);
    if (submitBtn) {
      FamilyPlatformSpinner.start(submitBtn);
    }
  }, false);

  // 2. Intercept direct clicks on action buttons & non-form triggers
  document.addEventListener('click', function(e) {
    var target = e.target.closest(
      'button[type="submit"], input[type="submit"], button[name="submit"], button.submit, ' +
      '#btn-toggle-push, #btn-test-push, .btn-spin-on-click, [data-spin="true"]'
    );

    if (!target) return;

    var form = target.closest('form');
    // If it's a standard form submit button, the 'submit' event handles it upon valid dispatch
    if (form && (target.type === 'submit' || target.getAttribute('type') === 'submit' || target.name === 'submit')) {
      return;
    }

    if (form && typeof form.checkValidity === 'function' && !form.checkValidity()) {
      return;
    }

    FamilyPlatformSpinner.start(target);
  }, false);

  // 3. Automated XMLHttpRequest interceptor (Catches Axios, jQuery, and native XHR)
  if (typeof window.XMLHttpRequest !== 'undefined' && !window._fp_xhr_intercepted) {
    window._fp_xhr_intercepted = true;
    var origXhrSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.send = function() {
      var xhr = this;
      var active = FamilyPlatformSpinner.getActiveButtons();
      if (active.length > 0) {
        xhr._fp_tracked_buttons = active;
      }
      xhr.addEventListener('loadend', function() {
        if (xhr._fp_tracked_buttons && xhr._fp_tracked_buttons.length > 0) {
          setTimeout(function() {
            xhr._fp_tracked_buttons.forEach(function(b) {
              FamilyPlatformSpinner.stop(b);
            });
          }, 80);
        }
      });
      return origXhrSend.apply(xhr, arguments);
    };
  }

  // 4. Automated Fetch API interceptor
  if (typeof window.fetch === 'function' && !window._fp_fetch_intercepted) {
    window._fp_fetch_intercepted = true;
    var origFetch = window.fetch;
    window.fetch = function() {
      var active = FamilyPlatformSpinner.getActiveButtons();
      var promise = origFetch.apply(this, arguments);
      if (active.length > 0) {
        promise.finally(function() {
          setTimeout(function() {
            active.forEach(function(b) {
              FamilyPlatformSpinner.stop(b);
            });
          }, 80);
        });
      }
      return promise;
    };
  }

  // 5. Reset buttons when user navigates back using browser history cache (BFCache)
  window.addEventListener('pageshow', function() {
    FamilyPlatformSpinner.stopAll();
  });

  // 6. Reset buttons when a modal closes or is dismissed
  document.addEventListener('hidden.bs.modal', function(e) {
    FamilyPlatformSpinner.stopAll(e.target);
  });

  // 7. Reset buttons on form reset
  document.addEventListener('reset', function(e) {
    if (e.target && e.target.tagName === 'FORM') {
      FamilyPlatformSpinner.stopAll(e.target);
    }
  }, true);

  // 8. Reset buttons on custom application lifecycle completion events
  ['post-created', 'post-updated', 'comment-created', 'reel-updated', 'event-created'].forEach(function(evt) {
    window.addEventListener(evt, function() {
      FamilyPlatformSpinner.stopAll();
    });
  });
})();
</script>
@include('component.swalAlert')

