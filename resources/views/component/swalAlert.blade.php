{{-- 
  Universal SweetAlert2 (Swal) Alert Management Component
  Ensures SweetAlert2 is universally loaded and manages all alerts across the application.
  Intercepts native window.alert to guarantee zero thread-blocking OS dialogs.
  Adheres to Global Design Standards v2.0 & British English invariants.
--}}
<script nonce="{{ $nonce ?? '' }}" src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  .swal2-popup.swal2-toast {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(229, 231, 235, 0.8) !important;
    border-radius: 1rem !important;
    font-family: inherit !important;
  }
  .swal2-popup.swal2-modal {
    border-radius: 1.25rem !important;
    padding: 1.75rem !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    border: 1px solid rgba(229, 231, 235, 0.9) !important;
    font-family: inherit !important;
  }
  .dark .swal2-popup.swal2-modal {
    background-color: #1e1e1e !important;
    color: #f3f4f6 !important;
    border-color: #333333 !important;
  }
  .dark .swal2-title {
    color: #ffffff !important;
  }
  .dark .swal2-html-container {
    color: #9ca3af !important;
  }
  .swal2-actions button {
    min-height: 44px !important;
    min-width: 44px !important;
    border-radius: 0.75rem !important;
    font-weight: 600 !important;
    font-size: 0.875rem !important;
    padding: 0.625rem 1.25rem !important;
    white-space: nowrap !important;
    transition: all 0.15s ease-in-out !important;
  }
  .swal2-actions button:active {
    transform: scale(0.98) !important;
  }
</style>
<script nonce="{{ $nonce ?? '' }}">
(function() {
  'use strict';
  if (window.SwalAlertManagerInitialized) return;
  window.SwalAlertManagerInitialized = true;

  var BRAND_COLOR = '#1d4ed8';
  var DANGER_COLOR = '#dc2626';

  function safeString(val, fallback) {
    fallback = fallback || 'Notification';
    if (val === null || val === undefined) return fallback;
    if (typeof val === 'string') {
      var trimmed = val.trim();
      return (trimmed && trimmed !== ('[object ' + 'Object]')) ? trimmed : fallback;
    }
    if (typeof val === 'object') {
      if (typeof val.message === 'string' && val.message.trim()) return val.message.trim();
      if (typeof val.error === 'string' && val.error.trim()) return val.error.trim();
      if (val.response && val.response.data) {
        var d = val.response.data;
        if (typeof d === 'string' && d.trim()) return d.trim();
        if (typeof d.message === 'string' && d.message.trim()) return d.message.trim();
        if (typeof d.error === 'string' && d.error.trim()) return d.error.trim();
      }
      try {
        var s = JSON.stringify(val);
        if (s && s !== '{}' && s.length < 300) return s;
      } catch (_) {}
    }
    return String(val) || fallback;
  }

  var recentTexts = {};
  function trackRecentText(msg) {
    if (!msg) return;
    recentTexts[msg] = true;
    setTimeout(function() { delete recentTexts[msg]; }, 4000);
  }

  // Toast instance
  function getToast() {
    if (typeof Swal === 'undefined') return null;
    return Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3500,
      timerProgressBar: true,
      didOpen: function(t) {
        t.addEventListener('mouseenter', Swal.stopTimer);
        t.addEventListener('mouseleave', Swal.resumeTimer);
      }
    });
  }

  window.swalToast = function(message, icon, timer) {
    icon = icon || 'success';
    timer = timer || 3500;
    var cleanMsg = safeString(message);
    trackRecentText(cleanMsg);
    if (typeof Swal !== 'undefined') {
      var t = getToast();
      if (t) return t.fire({ icon: icon, title: cleanMsg, timer: timer });
    }
    console.log('[Toast ' + icon + ']: ' + cleanMsg);
    return Promise.resolve(null);
  };

  window.swalSuccess = function(message, asToast, title) {
    if (asToast === undefined) asToast = true;
    title = title || 'Success';
    var cleanMsg = safeString(message);
    trackRecentText(cleanMsg);
    if (asToast) {
      return window.swalToast(cleanMsg, 'success');
    }
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        icon: 'success',
        title: title,
        text: cleanMsg,
        confirmButtonColor: BRAND_COLOR,
        confirmButtonText: 'OK'
      });
    }
    return Promise.resolve(null);
  };

  window.swalError = function(message, isModal, title) {
    if (isModal === undefined) isModal = true;
    title = title || 'Error';
    var cleanMsg = safeString(message);
    trackRecentText(cleanMsg);
    if (!isModal) {
      return window.swalToast(cleanMsg, 'error');
    }
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        icon: 'error',
        title: title,
        text: cleanMsg,
        confirmButtonColor: DANGER_COLOR,
        confirmButtonText: 'Dismiss'
      });
    }
    return Promise.resolve(null);
  };

  window.swalWarning = function(message, title) {
    title = title || 'Warning';
    var cleanMsg = safeString(message);
    trackRecentText(cleanMsg);
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        icon: 'warning',
        title: title,
        text: cleanMsg,
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'Understood'
      });
    }
    return Promise.resolve(null);
  };

  window.swalConfirm = function(title, text, confirmButtonText, cancelButtonText) {
    confirmButtonText = confirmButtonText || 'Yes, proceed';
    cancelButtonText = cancelButtonText || 'Cancel';
    var cleanText = safeString(text);
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        title: title,
        text: cleanText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: BRAND_COLOR,
        cancelButtonColor: DANGER_COLOR,
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        reverseButtons: true
      }).then(function(res) {
        return !!(res && res.isConfirmed);
      });
    }
    return Promise.resolve(false);
  };

  window.swalAlert = function(message, type, title) {
    type = type || 'info';
    var cleanMsg = safeString(message);
    trackRecentText(cleanMsg);
    if (type === 'success') return window.swalSuccess(cleanMsg, false, title);
    if (type === 'error') return window.swalError(cleanMsg, true, title);
    if (type === 'warning') return window.swalWarning(cleanMsg, title);
    if (typeof Swal !== 'undefined') {
      return Swal.fire({
        icon: 'info',
        title: title || 'Information',
        text: cleanMsg,
        confirmButtonColor: BRAND_COLOR,
        confirmButtonText: 'OK'
      });
    }
    return Promise.resolve(null);
  };

  // Intercept native window.alert to completely prevent OS-level dialog freezes
  if (!window._nativeAlert) {
    window._nativeAlert = window.alert;
    window.alert = function(msg) {
      var cleanMsg = safeString(msg);
      trackRecentText(cleanMsg);
      if (typeof Swal !== 'undefined') {
        return Swal.fire({
          text: cleanMsg,
          icon: 'info',
          confirmButtonColor: BRAND_COLOR,
          confirmButtonText: 'OK'
        });
      }
      if (window._nativeAlert) {
        return window._nativeAlert(cleanMsg);
      }
    };
  }

  // Banner MutationObserver Bridge: Automatically elevates #*_notification elements to SweetAlert
  function bridgeNotification(el) {
    if (!el || !el.id || el.id.slice(-13) !== '_notification') return;
    var text = (el.textContent || '').trim();
    if (!text) return;

    // If an alert was already triggered directly for this exact message, prevent duplicates/conflicts
    if (recentTexts[text]) {
      el.style.display = 'none';
      el.textContent = '';
      return;
    }

    var inlineShown = el.style.display && el.style.display !== 'none';
    var hasClass = /(?:^|\s)(?:bg|is)-(?:danger|success|warning|info)(?:\s|$)/.test(el.className) ||
                   /(?:^|\s)w3-(?:red|green|yellow|blue)(?:\s|$)/.test(el.className) ||
                   /(?:^|\s)alert-(?:danger|success|warning|info)(?:\s|$)/.test(el.className);

    if (!inlineShown && !hasClass) return;
    if (typeof Swal === 'undefined') return;

    trackRecentText(text);

    // Intelligently evaluate whether this is an error or success message
    var hasErrorKeyword = /(?:error|failed|failure|unable|cannot|could not|invalid|problem|denied|unauthorized|forbidden|wrong|incorrect|mismatch|required|not match)/i.test(text);
    var hasSuccessKeyword = /(?:success|successfully|saved|updated|created|added|welcom|thank|completed|verified|approved|branched|switched)/i.test(text);
    var hasRedStyle = /background-color:\s*red|color:\s*red|text-danger/i.test(el.innerHTML || '');
    var hasDangerClass = /(?:^|\s)(?:bg-danger|is-danger|w3-red|alert-danger)(?:\s|$)/.test(el.className);
    var hasSuccessClass = /(?:^|\s)(?:bg-success|is-success|w3-green|alert-success)(?:\s|$)/.test(el.className);

    var isError = false;
    if (hasErrorKeyword || hasRedStyle) {
      isError = true;
    } else if (hasSuccessKeyword || hasSuccessClass) {
      isError = false;
    } else if (hasDangerClass) {
      isError = true;
    } else {
      isError = false;
    }

    el.style.display = 'none';
    el.textContent = '';

    if (isError) {
      window.swalError(text, true);
    } else {
      window.swalSuccess(text, true);
    }
  }

  function observeBanners() {
    var obs = new MutationObserver(function(mutations) {
      for (var i = 0; i < mutations.length; i++) {
        var m = mutations[i];
        if (m.target && m.target.id && m.target.id.slice(-13) === '_notification') {
          bridgeNotification(m.target);
        } else if (m.addedNodes) {
          for (var j = 0; j < m.addedNodes.length; j++) {
            var n = m.addedNodes[j];
            if (n.nodeType === 1) {
              if (n.id && n.id.slice(-13) === '_notification') bridgeNotification(n);
              var hits = n.querySelectorAll ? n.querySelectorAll('[id$="_notification"]') : [];
              for (var k = 0; k < hits.length; k++) bridgeNotification(hits[k]);
            }
          }
        }
      }
    });

    obs.observe(document.body || document.documentElement, {
      subtree: true,
      childList: true,
      characterData: true,
      attributes: true,
      attributeFilter: ['style', 'class']
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', observeBanners);
  } else {
    observeBanners();
  }
})();
</script>

