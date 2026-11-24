{{--
  PWA Install Prompt Partial: FamilyPlatform
  Provides consistent, zero-CLS, WCAG AA compliant styling and defensive standalone
  detection across both the Homepage (landing_layout) and Profile Page (profileBase).
--}}
<style nonce="{{ csp_nonce($nonce ?? null) }}">
  #pwa-install-banner {
    position: fixed;
    bottom: calc(16px + env(safe-area-inset-bottom, 0px));
    left: 50%;
    transform: translateX(-50%) translateY(130%);
    width: calc(100% - 32px);
    max-width: 440px;
    z-index: 1040;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
  }
  #pwa-install-banner.show {
    transform: translateX(-50%) translateY(0);
    pointer-events: auto;
  }
  @media (max-width: 991.98px) {
    body.fp-has-bottom-nav #pwa-install-banner,
    body.fp-has-bottom-nav #pwa-ios-overlay {
      bottom: calc(76px + env(safe-area-inset-bottom, 0px));
    }
  }
  #pwa-install-banner .pwa-banner-card {
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(15, 23, 42, 0.12);
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.18), 0 2px 6px rgba(15, 23, 42, 0.08);
    border-radius: 16px;
    padding: 16px;
    color: #111827;
    text-align: left;
  }
  #pwa-install-banner .pwa-banner-header {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
  }
  #pwa-install-banner .pwa-banner-logo {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    object-fit: cover;
  }
  #pwa-install-banner .pwa-banner-text {
    flex: 1;
    min-width: 0;
    padding-right: 40px;
  }
  #pwa-install-banner .pwa-banner-text h6 {
    margin: 0 0 2px 0;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.3;
    color: #111827;
  }
  #pwa-install-banner .pwa-banner-text p {
    margin: 0;
    font-size: 0.82rem;
    color: #4b5563;
    line-height: 1.4;
  }
  #pwa-install-banner .pwa-banner-actions {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
  }
  #pwa-install-banner .pwa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: auto;
    max-width: 100%;
    flex: 1 1 auto;
    min-height: 44px;
    min-width: 44px;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    line-height: 1.2;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background-color 0.2s ease, transform 0.1s ease;
  }
  #pwa-install-banner .pwa-btn:active {
    transform: scale(0.98);
  }
  #pwa-install-banner .pwa-btn:focus-visible,
  #pwa-install-banner .pwa-btn-close:focus-visible,
  #pwa-ios-overlay .pwa-btn-close:focus-visible {
    outline: 2px solid #a31621;
    outline-offset: 2px;
  }
  #pwa-install-banner .pwa-btn-primary {
    background: #a31621;
    color: #ffffff;
  }
  #pwa-install-banner .pwa-btn-primary:hover {
    background: #8a1019;
    color: #ffffff;
  }
  #pwa-install-banner .pwa-btn-secondary {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    flex: 0 0 auto;
  }
  #pwa-install-banner .pwa-btn-secondary:hover {
    background: #e2e8f0;
    color: #0f172a;
  }
  #pwa-install-banner .pwa-btn-close,
  #pwa-ios-overlay .pwa-btn-close {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 44px;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    font-size: 1.35rem;
    color: #4b5563;
    cursor: pointer;
    line-height: 1;
    padding: 8px;
    border-radius: 10px;
    white-space: nowrap;
    flex-shrink: 0;
  }
  #pwa-install-banner .pwa-btn-close:hover,
  #pwa-ios-overlay .pwa-btn-close:hover {
    color: #111827;
    background: rgba(15, 23, 42, 0.06);
  }
  #pwa-ios-overlay {
    position: fixed;
    bottom: calc(12px + env(safe-area-inset-bottom, 0px));
    left: 50%;
    transform: translateX(-50%) translateY(130%);
    width: calc(100% - 24px);
    max-width: 400px;
    z-index: 10000;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  }
  #pwa-ios-overlay.show {
    transform: translateX(-50%) translateY(0);
  }
  #pwa-ios-overlay .pwa-ios-card {
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(15, 23, 42, 0.1);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
    border-radius: 20px;
    padding: 18px 18px 12px 18px;
    position: relative;
    text-align: left;
    color: #111827;
  }
  #pwa-ios-overlay .pwa-ios-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
    padding-right: 40px;
  }
  #pwa-ios-overlay .pwa-ios-logo {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    flex-shrink: 0;
  }
  #pwa-ios-overlay .pwa-ios-header h6 {
    margin: 0 0 2px 0;
    font-weight: 700;
    font-size: 0.95rem;
    color: #111827;
  }
  #pwa-ios-overlay .pwa-ios-header p {
    margin: 0;
    font-size: 0.8rem;
    color: #4b5563;
  }
  #pwa-ios-overlay .pwa-ios-steps {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: rgba(243, 244, 246, 0.9);
    border-radius: 12px;
    padding: 12px;
  }
  #pwa-ios-overlay .pwa-step {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.82rem;
    color: #1f2937;
  }
  #pwa-ios-overlay .pwa-step .step-num {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #a31621;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.75rem;
    flex-shrink: 0;
  }
  body.modal-open #pwa-install-banner,
  body.modal-open #pwa-ios-overlay,
  body.swal2-shown #pwa-install-banner,
  body.swal2-shown #pwa-ios-overlay {
    display: none !important;
  }
  body.dark-mode #pwa-install-banner .pwa-banner-card,
  body.dark-mode #pwa-ios-overlay .pwa-ios-card {
    background: rgba(30, 41, 59, 0.97) !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
    color: #f8fafc !important;
  }
  body.dark-mode #pwa-install-banner .pwa-banner-text h6,
  body.dark-mode #pwa-ios-overlay .pwa-ios-header h6 {
    color: #f8fafc !important;
  }
  body.dark-mode #pwa-install-banner .pwa-banner-text p,
  body.dark-mode #pwa-ios-overlay .pwa-ios-header p {
    color: #cbd5e1 !important;
  }
  body.dark-mode #pwa-install-banner .pwa-btn-secondary {
    background: #334155 !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
  }
  body.dark-mode #pwa-install-banner .pwa-btn-close,
  body.dark-mode #pwa-ios-overlay .pwa-btn-close {
    color: #cbd5e1 !important;
  }
  body.dark-mode #pwa-install-banner .pwa-btn-close:hover,
  body.dark-mode #pwa-ios-overlay .pwa-btn-close:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1) !important;
  }
  @media (prefers-reduced-motion: reduce) {
    #pwa-install-banner,
    #pwa-ios-overlay {
      transition: none;
    }
  }
</style>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function () {
  'use strict';
  if (document.getElementById('pwaBottomNav')) {
    document.body && document.body.classList.add('fp-has-bottom-nav');
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      if (document.getElementById('pwaBottomNav') && document.body) {
        document.body.classList.add('fp-has-bottom-nav');
      }
    });
  }
})();
</script>
