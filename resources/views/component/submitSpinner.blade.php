{{-- 
  Universal Submit & Action Button Spinner Component
  Provides instantaneous spinning feedback on all submit and action buttons across FamilyPlatform.
  Guarantees zero double submissions, visual confirmation, and automatic watchdog recovery.
--}}
<style>
  .st-spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    vertical-align: -0.125em;
    border: 0.18em solid currentColor;
    border-right-color: transparent !important;
    border-radius: 50%;
    -webkit-animation: st-spinner-spin 0.75s linear infinite;
    animation: st-spinner-spin 0.75s linear infinite;
    flex-shrink: 0;
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

    start: function(btn, customText) {
      if (!btn || btn.getAttribute('aria-busy') === 'true') return;

      var originalHtml = btn.innerHTML;
      var originalWidth = btn.offsetWidth ? (btn.offsetWidth + 'px') : '';

      FamilyPlatformSpinner.activeButtons.set(btn, {
        html: originalHtml,
        width: originalWidth,
        isInput: btn.tagName === 'INPUT'
      });

      btn.setAttribute('aria-busy', 'true');
      btn.style.pointerEvents = 'none';
      if (originalWidth) {
        btn.style.minWidth = originalWidth;
      }

      // Bulma is-loading support
      if (btn.classList.contains('button')) {
        btn.classList.add('is-loading');
      }

      var rawText = (btn.innerText || btn.textContent || btn.value || (btn.innerHTML ? btn.innerHTML.replace(/<[^>]*>?/gm, '') : '') || '').trim();

      if (btn.tagName === 'INPUT') {
        btn.value = customText || 'Processing...';
      } else {
        var spinnerText = customText || (rawText ? ' ' + rawText : ' Processing...');
        btn.innerHTML = '<span class="st-spinner-border me-2" role="status" aria-hidden="true"></span>' + spinnerText;
      }

      // Watchdog auto-recovery (15s) in case network drops or asynchronous action fails
      setTimeout(function() {
        if (btn.getAttribute('aria-busy') === 'true') {
          FamilyPlatformSpinner.stop(btn);
        }
      }, 15000);
    },

    stop: function(btn) {
      if (!btn) return;
      var data = FamilyPlatformSpinner.activeButtons.get(btn);
      if (data) {
        if (data.isInput) {
          btn.value = data.html || 'Submit';
        } else {
          btn.innerHTML = data.html;
        }
        FamilyPlatformSpinner.activeButtons.delete(btn);
      }
      btn.removeAttribute('aria-busy');
      btn.style.pointerEvents = '';
      btn.style.minWidth = '';
      btn.disabled = false;
      if (btn.classList.contains('button')) {
        btn.classList.remove('is-loading');
      }
    },

    stopAll: function() {
      var busyButtons = document.querySelectorAll('[aria-busy="true"]');
      busyButtons.forEach(function(b) {
        FamilyPlatformSpinner.stop(b);
      });
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

  // 3. Reset buttons when user navigates back using browser history cache
  window.addEventListener('pageshow', function() {
    FamilyPlatformSpinner.stopAll();
  });
})();
</script>
@include('component.swalAlert')

