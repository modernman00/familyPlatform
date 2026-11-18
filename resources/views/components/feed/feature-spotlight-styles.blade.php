{{-- Styles for In-Feed Feature Spotlight Card (included once in feed) --}}
<style nonce="{{ csp_nonce($nonce ?? null) }}">
  [x-cloak] { display: none !important; }

  .fp-spotlight-card {
    border-radius: 20px;
    background-color: #ffffff;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  body.dark-mode .fp-spotlight-card,
  body.dark-theme .fp-spotlight-card,
  [data-bs-theme="dark"] .fp-spotlight-card,
  html.dark .fp-spotlight-card {
    background-color: #1e293b;
    border-color: #334155;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.42);
  }

  /* Editorial Jewel-Toned Header Themes (Strict WCAG AA White/Gold on Rich Dark Gradients) */
  .fp-spotlight-header {
    position: relative;
    padding: 20px 22px 18px;
    color: #ffffff !important;
    overflow: hidden;
  }
  .fp-spotlight-header--indigo-gold {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 55%, #4338ca 100%);
    border-bottom: 2px solid rgba(212, 175, 55, 0.65);
  }
  .fp-spotlight-header--plum-rose {
    background: linear-gradient(135deg, #4a044e 0%, #831843 58%, #be185d 100%);
    border-bottom: 2px solid rgba(251, 191, 36, 0.45);
  }
  .fp-spotlight-header--amber-crimson {
    background: linear-gradient(135deg, #7c2d12 0%, #b45309 55%, #d97706 100%);
    border-bottom: 2px solid rgba(254, 243, 199, 0.45);
  }
  .fp-spotlight-header--royal-crimson {
    background: linear-gradient(135deg, #6e0d15 0%, #a31621 60%, #be202e 100%);
    border-bottom: 2px solid rgba(253, 224, 71, 0.55);
  }
  .fp-spotlight-header--emerald-teal {
    background: linear-gradient(135deg, #064e3b 0%, #047857 58%, #0d9488 100%);
    border-bottom: 2px solid rgba(167, 243, 208, 0.45);
  }
  .fp-spotlight-header--cobalt-sky {
    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 58%, #0284c7 100%);
    border-bottom: 2px solid rgba(186, 230, 253, 0.45);
  }

  .fp-spotlight-glow {
    position: absolute;
    top: -48px;
    right: -36px;
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, transparent 70%);
    pointer-events: none;
  }

  .fp-spotlight-medallion {
    width: 50px;
    height: 50px;
    min-width: 50px;
    min-height: 50px;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.18);
    border: 1.5px solid rgba(255, 255, 255, 0.38);
    box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.28), 0 6px 14px rgba(0, 0, 0, 0.25);
    color: #fef08a !important;
    font-size: 1.35rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .fp-spotlight-kicker {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 10px;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.38);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fef3c7 !important;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .fp-spotlight-feature-name {
    color: #ffffff !important;
    font-size: 0.92rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    margin: 4px 0 0;
  }

  .fp-spotlight-dismiss-btn {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background-color 0.15s ease, transform 0.15s ease;
  }
  .fp-spotlight-dismiss-btn:hover {
    background: rgba(15, 23, 42, 0.5);
    color: #ffffff !important;
  }
  .fp-spotlight-dismiss-btn:active {
    transform: scale(0.96);
  }
  .fp-spotlight-dismiss-btn:focus-visible {
    outline: 2px solid #fef08a;
    outline-offset: 2px;
  }

  .fp-spotlight-body {
    padding: 20px 22px 22px;
  }

  .fp-spotlight-headline {
    color: #0f172a !important;
    font-size: 1.2rem;
    font-weight: 800;
    line-height: 1.32;
    letter-spacing: -0.015em;
    margin-bottom: 10px;
  }
  body.dark-mode .fp-spotlight-headline,
  body.dark-theme .fp-spotlight-headline,
  [data-bs-theme="dark"] .fp-spotlight-headline,
  html.dark .fp-spotlight-headline {
    color: #f8fafc !important;
  }

  .fp-spotlight-story {
    color: #334155 !important;
    font-size: 0.96rem;
    line-height: 1.58;
    margin-bottom: 16px;
  }
  body.dark-mode .fp-spotlight-story,
  body.dark-theme .fp-spotlight-story,
  [data-bs-theme="dark"] .fp-spotlight-story,
  html.dark .fp-spotlight-story {
    color: #cbd5e1 !important;
  }

  .fp-spotlight-highlights {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  body.dark-mode .fp-spotlight-highlights,
  body.dark-theme .fp-spotlight-highlights,
  [data-bs-theme="dark"] .fp-spotlight-highlights,
  html.dark .fp-spotlight-highlights {
    background-color: #0f172a;
    border-color: #334155;
  }

  .fp-spotlight-highlight-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: #1e293b !important;
    font-size: 0.875rem;
    line-height: 1.45;
    font-weight: 500;
  }
  body.dark-mode .fp-spotlight-highlight-item,
  body.dark-theme .fp-spotlight-highlight-item,
  [data-bs-theme="dark"] .fp-spotlight-highlight-item,
  html.dark .fp-spotlight-highlight-item {
    color: #e2e8f0 !important;
  }
  .fp-spotlight-highlight-check {
    color: #059669 !important;
    font-size: 1rem;
    line-height: 1.3;
    flex-shrink: 0;
  }
  body.dark-mode .fp-spotlight-highlight-check,
  [data-bs-theme="dark"] .fp-spotlight-highlight-check,
  html.dark .fp-spotlight-highlight-check {
    color: #34d399 !important;
  }

  .fp-spotlight-cta {
    min-height: 44px;
    min-width: 44px;
    padding: 10px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #8b121c 0%, #a31621 60%, #be202e 100%);
    color: #ffffff !important;
    border: 1px solid #8b121c;
    font-size: 0.92rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(163, 22, 33, 0.25);
    transition: transform 0.15s ease, filter 0.15s ease;
  }
  .fp-spotlight-cta:hover {
    filter: brightness(1.08);
    color: #ffffff !important;
  }
  .fp-spotlight-cta:active {
    transform: scale(0.98);
  }
  .fp-spotlight-cta:focus-visible {
    outline: 2px solid #a31621;
    outline-offset: 2px;
  }

  .fp-spotlight-secondary-btn {
    min-height: 44px;
    min-width: 44px;
    padding: 10px 14px;
    border-radius: 12px;
    background-color: #f1f5f9;
    color: #0f172a !important;
    border: 1px solid #cbd5e1;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    transition: background-color 0.15s ease, transform 0.15s ease;
  }
  .fp-spotlight-secondary-btn:hover {
    background-color: #e2e8f0;
    color: #0f172a !important;
  }
  .fp-spotlight-secondary-btn:active {
    transform: scale(0.98);
  }
  .fp-spotlight-secondary-btn:focus-visible {
    outline: 2px solid #0f172a;
    outline-offset: 2px;
  }
  body.dark-mode .fp-spotlight-secondary-btn,
  body.dark-theme .fp-spotlight-secondary-btn,
  [data-bs-theme="dark"] .fp-spotlight-secondary-btn,
  html.dark .fp-spotlight-secondary-btn {
    background-color: #0f172a;
    color: #f8fafc !important;
    border-color: #475569;
  }
  body.dark-mode .fp-spotlight-secondary-btn:hover,
  [data-bs-theme="dark"] .fp-spotlight-secondary-btn:hover,
  html.dark .fp-spotlight-secondary-btn:hover {
    background-color: #334155;
    color: #ffffff !important;
  }
</style>
