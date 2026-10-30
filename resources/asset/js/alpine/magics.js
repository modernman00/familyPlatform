'use strict';

/**
 * FamilyPlatform Enterprise Alpine Custom Magic Helpers
 * Declarative shortcuts accessible inside any x-data component or template expression.
 */

export function initAlpineMagics(Alpine) {
  // 1. $clipboard: 1-click clipboard copy with tactile haptics & toast feedback
  Alpine.magic('clipboard', () => (text, successMsg = 'Copied to clipboard!') => {
    if (!text) return Promise.reject(new Error('No text provided to copy'));

    const doSuccess = () => {
      if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
        try { navigator.vibrate(30); } catch (e) {}
      }
      if (typeof window !== 'undefined' && typeof window.swalToast === 'function') {
        window.swalToast({ icon: 'success', title: successMsg });
      }
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(String(text))
        .then(doSuccess)
        .catch(() => fallbackCopy(String(text), doSuccess));
    } else {
      return fallbackCopy(String(text), doSuccess);
    }
  });

  // 2. $csrf: Instant access to active CSRF token without DOM query boilerplate
  Alpine.magic('csrf', () => () => {
    if (typeof document === 'undefined') return '';
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
  });

  // 3. $trans: Direct access to FamilyPlatform's i18n translation system
  Alpine.magic('trans', () => (key, fallback = '') => {
    if (typeof window !== 'undefined' && typeof window.trans === 'function') {
      return window.trans(key, fallback);
    }
    return fallback || key;
  });

  // 4. $toast: Universal tactile toast notification shortcut
  Alpine.magic('toast', () => (title, icon = 'info') => {
    if (typeof window !== 'undefined' && typeof window.swalToast === 'function') {
      window.swalToast({ icon, title });
    } else if (typeof window !== 'undefined' && typeof window.swal === 'function') {
      window.swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 3000
      });
    }
  });

  // 5. $screen: Reactive viewport breakpoint detection
  Alpine.magic('screen', () => ({
    get width() { return typeof window !== 'undefined' ? window.innerWidth : 1024; },
    get isMobile() { return this.width < 768; },
    get isTablet() { return this.width >= 768 && this.width < 1024; },
    get isDesktop() { return this.width >= 1024; }
  }));
}

/**
 * Robust fallback copy mechanism for legacy browsers or non-secure contexts
 */
function fallbackCopy(text, onSuccess) {
  return new Promise((resolve, reject) => {
    try {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.top = '-9999px';
      textArea.style.left = '-9999px';
      textArea.setAttribute('readonly', '');
      document.body.appendChild(textArea);
      textArea.select();
      const successful = document.execCommand('copy');
      document.body.removeChild(textArea);
      if (successful) {
        if (typeof onSuccess === 'function') onSuccess();
        resolve(true);
      } else {
        reject(new Error('ExecCommand copy failed'));
      }
    } catch (err) {
      reject(err);
    }
  });
}
