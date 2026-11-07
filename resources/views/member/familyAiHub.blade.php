@extends('layouts.profileBase')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Great+Vibes&family=Montserrat:wght@500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
<style>
/* 🌟 FAMILY HUB SIGNATURE HIGH-DEFINITION ARTWORK HERO */
.ai-hub-hero-banner {
    position: relative;
    width: 100%;
    border-radius: 24px;
    border: 1.5px solid rgba(212, 175, 55, 0.45);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.16);
    overflow: hidden;
    background: #0b0f19;
    aspect-ratio: 1024 / 572;
    margin-bottom: 1.5rem;
}
.ai-hub-hero-banner picture,
.ai-hub-hero-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    image-rendering: -webkit-optimize-contrast;
    transform: translateZ(0);
}
.hero-hotspot {
    position: absolute;
    border-radius: 14px;
    transition: all 0.22s ease-in-out;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.001);
    border: 1.5px solid transparent;
    z-index: 10;
    text-decoration: none;
    display: block;
}
.hero-hotspot:hover,
.hero-hotspot:focus-visible {
    border-color: rgba(255, 255, 255, 0.85);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.45), inset 0 0 12px rgba(255, 255, 255, 0.25);
    transform: scale(1.02);
}

.flyer-horiz-strip .btn-sm {
    min-height: 38px;
    padding: 0.35rem 0.75rem !important;
    font-size: 0.74rem !important;
    scroll-snap-align: start;
}

.input-group > .luxury-input:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* 🗺️ PREMIUM LUXURY CONCIERGE & OUTINGS SALON */
.outings-salon-hero {
    background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 50%, #fbf8ef 100%);
    border: 1.5px solid rgba(212, 175, 55, 0.35);
    border-radius: 24px;
    box-shadow: 0 10px 30px -8px rgba(212, 175, 55, 0.12), 0 4px 12px rgba(15, 23, 42, 0.03);
    position: relative;
    overflow: hidden;
    padding: 2.25rem 2.25rem 1.75rem 2.25rem;
}
.outings-salon-hero::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 260px;
    height: 100%;
    background: radial-gradient(circle at 100% 0%, rgba(212, 175, 55, 0.14) 0%, transparent 70%);
    pointer-events: none;
}
.outings-preset-rail-wrap {
    position: relative;
    width: 100%;
}
.outings-preset-rail {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 6px 2px 10px 2px;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.outings-preset-rail::-webkit-scrollbar {
    display: none;
}
.outings-preset-chip {
    flex-shrink: 0 !important;
    white-space: nowrap !important;
    background: #ffffff;
    color: #1e293b;
    border: 1.5px solid #e2e8f0;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-flex !important;
    align-items: center;
    gap: 0.55rem;
    min-height: 44px;
    min-width: 44px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    cursor: pointer;
    user-select: none;
    scroll-snap-align: start;
}
.outings-chip-icon {
    font-size: 1.05rem;
    line-height: 1;
}
.outings-chip-text {
    font-weight: 600;
    letter-spacing: -0.01em;
}
.outings-chip-loc {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    letter-spacing: 0.02em;
}
.outings-preset-chip:hover {
    background: #fdfbf7;
    color: #0f172a;
    border-color: #d4af37;
    transform: translateY(-1.5px);
    box-shadow: 0 4px 14px rgba(212, 175, 55, 0.18);
}
.outings-preset-chip.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #d4af37 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.3) !important;
}
.outings-preset-chip.active .outings-chip-loc {
    background: rgba(212, 175, 55, 0.2);
    color: #ffd700;
    border-color: rgba(212, 175, 55, 0.4);
}
.outings-surface-card {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04);
    padding: 1.75rem;
}
.outings-result-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 1.5rem;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    position: relative;
    word-break: break-word;
}
.outings-result-card:hover {
    border-color: #d4af37;
    box-shadow: 0 10px 28px -5px rgba(212, 175, 55, 0.18), 0 4px 10px rgba(15, 23, 42, 0.04);
    transform: translateY(-2px);
}
.outings-btn-search {
    background: linear-gradient(135deg, #a31621 0%, #7e101a 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 215, 0, 0.35) !important;
    box-shadow: 0 6px 18px -4px rgba(163, 22, 33, 0.4) !important;
    font-weight: 700 !important;
    min-height: 48px !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    transition: all 0.2s ease !important;
}
.outings-btn-search:hover {
    filter: brightness(1.08);
    transform: translateY(-1px);
    box-shadow: 0 8px 22px -4px rgba(163, 22, 33, 0.5) !important;
}
.outings-btn-search:active {
    transform: scale(0.98);
}
.outings-btn-book {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25) !important;
    min-height: 44px;
    min-width: 44px;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.2s ease;
    font-weight: 700;
}
.outings-btn-book:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.35) !important;
    color: #ffffff !important;
}
.outings-btn-book:active {
    transform: scale(0.98);
}

/* 📱 MOBILE RESPONSIVE OPTIMISATIONS */
@media (max-width: 768px) {
    .outings-salon-hero {
        padding: 1.25rem 1rem !important;
        border-radius: 18px !important;
    }
    .outings-surface-card {
        padding: 1.15rem !important;
        border-radius: 18px !important;
    }
    .outings-result-card {
        padding: 1.15rem !important;
        border-radius: 16px !important;
    }
    .outings-preset-chip {
        padding: 0.45rem 0.85rem !important;
        font-size: 0.78rem !important;
    }
    .outings-btn-book {
        width: 100% !important;
        justify-content: center !important;
        min-height: 48px !important;
    }
    .outings-btn-search {
        min-height: 48px !important;
    }
}

/* 🌙 DARK MODE HIGH-CONTRAST ATELIER & GAME SHOW OVERRIDES (WCAG AA/AAA) */
body.dark-mode .luxury-card-box,
body.dark-mode .outings-surface-card,
body.dark-mode .outings-result-card,
body.dark-theme .luxury-card-box,
body.dark-theme .outings-surface-card,
body.dark-theme .outings-result-card {
    background: var(--surface-card, #141416) !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
    color: var(--text-main, #f8fafc) !important;
}

body.dark-mode .luxury-card-gold,
body.dark-mode .luxury-card-ribbon,
body.dark-mode .outings-salon-hero,
body.dark-theme .luxury-card-gold,
body.dark-theme .luxury-card-ribbon,
body.dark-theme .outings-salon-hero {
    background: linear-gradient(135deg, #1c1917 0%, #141416 100%) !important;
    border-color: rgba(251, 191, 36, 0.4) !important;
    color: var(--text-main, #f8fafc) !important;
}

body.dark-mode .outings-preset-chip,
body.dark-mode .flyer-preset-btn,
body.dark-mode .flyer-format-btn,
body.dark-theme .outings-preset-chip,
body.dark-theme .flyer-preset-btn,
body.dark-theme .flyer-format-btn {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}

body.dark-mode .outings-preset-chip:hover,
body.dark-mode .flyer-preset-btn:hover,
body.dark-mode .flyer-format-btn:hover,
body.dark-theme .outings-preset-chip:hover,
body.dark-theme .flyer-preset-btn:hover,
body.dark-theme .flyer-format-btn:hover {
    background: #334155 !important;
    color: #ffffff !important;
    border-color: #fbbf24 !important;
}

body.dark-mode .outings-chip-loc,
body.dark-theme .outings-chip-loc {
    background: #0f172a !important;
    color: #cbd5e1 !important;
    border-color: #334155 !important;
}

body.dark-mode .tt-statement-card,
body.dark-theme .tt-statement-card {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
}

body.dark-mode .tt-statement-card:hover,
body.dark-theme .tt-statement-card:hover {
    background-color: #263349 !important;
    border-color: rgba(251, 191, 36, 0.45) !important;
}

body.dark-mode #tab-roundtable [style*="background: #fafafa"],
body.dark-mode #tab-roundtable [style*="background:#fafafa"],
body.dark-mode #tab-roundtable [style*="background: #ffffff"],
body.dark-mode #tab-roundtable [style*="background:#ffffff"],
body.dark-mode #tab-roundtable [style*="background: #f8fafc"],
body.dark-mode #tab-roundtable [style*="background:#f8fafc"],
body.dark-mode #tab-journal [style*="background: #ffffff"],
body.dark-mode #tab-journal [style*="background:#ffffff"],
body.dark-mode #tab-journal [style*="background: #f8fafc"],
body.dark-mode #tab-outings [style*="background: #f8fafc"],
body.dark-mode #tab-outings [style*="background: #ffffff"],
body.dark-theme #tab-roundtable [style*="background: #fafafa"],
body.dark-theme #tab-roundtable [style*="background:#fafafa"],
body.dark-theme #tab-roundtable [style*="background: #ffffff"],
body.dark-theme #tab-roundtable [style*="background:#ffffff"],
body.dark-theme #tab-roundtable [style*="background: #f8fafc"],
body.dark-theme #tab-roundtable [style*="background:#f8fafc"],
body.dark-theme #tab-journal [style*="background: #ffffff"],
body.dark-theme #tab-journal [style*="background:#ffffff"],
body.dark-theme #tab-journal [style*="background: #f8fafc"],
body.dark-theme #tab-outings [style*="background: #f8fafc"],
body.dark-theme #tab-outings [style*="background: #ffffff"],
[data-bs-theme="dark"] #tab-roundtable [style*="background: #fafafa"],
[data-bs-theme="dark"] #tab-roundtable [style*="background:#fafafa"],
[data-bs-theme="dark"] #tab-roundtable [style*="background: #ffffff"],
[data-bs-theme="dark"] #tab-roundtable [style*="background:#ffffff"],
[data-bs-theme="dark"] #tab-roundtable [style*="background: #f8fafc"],
[data-bs-theme="dark"] #tab-roundtable [style*="background:#f8fafc"],
[data-bs-theme="dark"] #tab-journal [style*="background: #ffffff"],
[data-bs-theme="dark"] #tab-journal [style*="background:#ffffff"],
[data-bs-theme="dark"] #tab-journal [style*="background: #f8fafc"],
[data-bs-theme="dark"] #tab-outings [style*="background: #f8fafc"],
[data-bs-theme="dark"] #tab-outings [style*="background: #ffffff"] {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
}

/* 🛡️ Explicit Docket & Journal Card Contrast in Dark Mode */
body.dark-mode .roundtable-docket-card,
body.dark-theme .roundtable-docket-card,
[data-bs-theme="dark"] .roundtable-docket-card {
    background: #0f172a !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode .roundtable-docket-card .text-dark,
body.dark-theme .roundtable-docket-card .text-dark,
[data-bs-theme="dark"] .roundtable-docket-card .text-dark {
    color: #f8fafc !important;
}
body.dark-mode .roundtable-docket-card .text-secondary,
body.dark-theme .roundtable-docket-card .text-secondary,
[data-bs-theme="dark"] .roundtable-docket-card .text-secondary,
body.dark-mode .roundtable-docket-card .text-muted,
body.dark-theme .roundtable-docket-card .text-muted,
[data-bs-theme="dark"] .roundtable-docket-card .text-muted {
    color: #94a3b8 !important;
}

body.dark-mode .family-journal-card,
body.dark-theme .family-journal-card,
[data-bs-theme="dark"] .family-journal-card {
    background: #0f172a !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25) !important;
}
body.dark-mode .family-journal-card:hover,
body.dark-theme .family-journal-card:hover,
[data-bs-theme="dark"] .family-journal-card:hover {
    border-color: rgba(99, 102, 241, 0.5) !important;
    transform: translateY(-2px);
}
body.dark-mode .family-journal-card .text-dark,
body.dark-theme .family-journal-card .text-dark,
[data-bs-theme="dark"] .family-journal-card .text-dark {
    color: #f8fafc !important;
}
body.dark-mode .family-journal-card .badge,
body.dark-theme .family-journal-card .badge,
[data-bs-theme="dark"] .family-journal-card .badge {
    background: rgba(99, 102, 241, 0.25) !important;
    color: #c7d2fe !important;
}
body.dark-mode .family-journal-card a.btn-link,
body.dark-theme .family-journal-card a.btn-link,
[data-bs-theme="dark"] .family-journal-card a.btn-link {
    color: #818cf8 !important;
}

/* 🌙 Two Truths Arena Dark Contrast */
body.dark-mode .tt-statement-card,
body.dark-theme .tt-statement-card,
[data-bs-theme="dark"] .tt-statement-card {
    background-color: #1e293b !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}

/* ===================================================================
   ✨ FAMILY HUB — MODERN SKIN (Nov 2026 redesign)
   Sticky tool dock, unified surfaces, motion. Hero artwork untouched.
   =================================================================== */
.fhub {
    --fhub-ink: #0f172a;
    --fhub-muted: #64748b;
    --fhub-line: rgba(15, 23, 42, 0.08);
    --fhub-surface: #ffffff;
    --fhub-dock-bg: rgba(255, 255, 255, 0.82);
    --fhub-radius: 20px;
    --fhub-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 32px -16px rgba(15, 23, 42, 0.18);
    --fhub-sticky-top: 72px;
    font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    -webkit-font-smoothing: antialiased;
}

/* Tool dock */
.fhub-dock {
    position: sticky;
    top: var(--fhub-sticky-top);
    z-index: 1010;
    margin: 0 0 1.75rem;
}
.fhub .fhub-dock-list {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 0.4rem;
    margin: 0;
    padding: 0.45rem;
    list-style: none;
    flex-wrap: nowrap;
    background: var(--fhub-dock-bg);
    -webkit-backdrop-filter: saturate(180%) blur(18px);
    backdrop-filter: saturate(180%) blur(18px);
    border: 1px solid var(--fhub-line);
    border-radius: 22px;
    box-shadow: var(--fhub-shadow);
}
.fhub .fhub-dock-list .nav-item { min-width: 0; }
.fhub .fhub-tile {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 44px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.8rem 0.85rem 0.9rem;
    border: 1px solid transparent;
    border-radius: 16px;
    background: transparent;
    color: var(--fhub-ink);
    text-align: left;
    overflow: hidden;
    transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.fhub .fhub-tile:hover {
    background: rgba(15, 23, 42, 0.04);
    color: var(--fhub-ink);
    transform: translateY(-1px);
}
.fhub .fhub-tile:focus-visible {
    outline: 3px solid color-mix(in srgb, var(--fhub-from) 55%, transparent);
    outline-offset: 2px;
}
.fhub-tile-icon {
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: #ffffff;
    font-size: 1.05rem;
    background: linear-gradient(135deg, var(--fhub-from), var(--fhub-to));
    box-shadow: 0 8px 16px -8px var(--fhub-from);
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fhub .fhub-tile:hover .fhub-tile-icon { transform: scale(1.08) rotate(-5deg); }
.fhub-tile-text { min-width: 0; display: block; }
.fhub-tile-label,
.fhub-tile-sub {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fhub-tile-label {
    display: block;
    font-weight: 700;
    font-size: 0.86rem;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.fhub-tile-sub {
    display: block;
    margin-top: 3px;
    font-size: 0.72rem;
    font-weight: 500;
    line-height: 1.3;
    color: var(--fhub-muted);
}
.fhub .fhub-tile.active,
.fhub .fhub-tile.active:hover {
    background: var(--fhub-surface);
    color: var(--fhub-ink);
    border-color: color-mix(in srgb, var(--fhub-from) 30%, transparent);
    box-shadow: 0 14px 30px -18px var(--fhub-from), 0 1px 2px rgba(15, 23, 42, 0.06);
    transform: none;
}
.fhub .fhub-tile.active::after {
    content: '';
    position: absolute;
    left: 16px;
    right: 16px;
    bottom: 0;
    height: 3px;
    border-radius: 3px 3px 0 0;
    background: linear-gradient(90deg, var(--fhub-from), var(--fhub-to));
}

/* Mobile & tablet: swipeable chip rail */
@media (max-width: 991.98px) {
    .fhub-dock { margin-bottom: 1.25rem; }
    .fhub .fhub-dock-list {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x proximity;
        scrollbar-width: none;
        border-radius: 18px;
        -webkit-overflow-scrolling: touch;
        mask-image: linear-gradient(90deg, #000 0, #000 calc(100% - 28px), transparent 100%);
    }
    .fhub .fhub-dock-list::-webkit-scrollbar { display: none; }
    .fhub .fhub-dock-list .nav-item { flex: 0 0 auto; scroll-snap-align: start; }
    .fhub .fhub-tile {
        flex-direction: row;
        align-items: center;
        gap: 0.55rem;
        padding: 0.4rem 0.95rem 0.4rem 0.4rem;
        white-space: nowrap;
        border-radius: 14px;
    }
    .fhub-tile-icon { width: 34px; height: 34px; border-radius: 10px; font-size: 0.92rem; }
    .fhub-tile-sub { display: none; }
    .fhub .fhub-tile.active::after { left: 10px; right: 10px; }
}

/* Panes: unified surfaces & entrance motion */
.fhub #familyAiContent > .tab-pane.active {
    animation: fhubRise 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
}
@keyframes fhubRise {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: none; }
}
.fhub #familyAiContent .card {
    border-radius: var(--fhub-radius) !important;
    border: 1px solid var(--fhub-line) !important;
    box-shadow: var(--fhub-shadow) !important;
}
.fhub #familyAiContent > .tab-pane > .row > [class*="col-"] > .card > h3,
.fhub #familyAiContent > .tab-pane > .row > [class*="col-"] > .card > h4,
.fhub #familyAiContent > .tab-pane > .card > h3,
.fhub #familyAiContent > .tab-pane > .card > h4 {
    font-weight: 800 !important;
    letter-spacing: -0.02em;
    color: var(--fhub-ink);
}
.fhub #familyAiContent .btn.rounded-pill { font-weight: 600; letter-spacing: -0.005em; }
.fhub #familyAiContent .form-control,
.fhub #familyAiContent .form-select {
    border-radius: 12px;
    border-color: rgba(15, 23, 42, 0.12);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.fhub #familyAiContent .form-control:focus,
.fhub #familyAiContent .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
}
.fhub #fridge-drop-zone {
    border-radius: 18px !important;
    background: linear-gradient(180deg, #f8fafc 0%, #f0fdf4 100%) !important;
    transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
}
.fhub #fridge-drop-zone:hover {
    border-color: #10b981 !important;
    transform: translateY(-2px);
}
.fhub #fam-voice-btn:hover { transform: scale(1.05); box-shadow: 0 18px 40px -12px rgba(236, 72, 153, 0.6) !important; }

@media (prefers-reduced-motion: reduce) {
    .fhub #familyAiContent > .tab-pane.active { animation: none; }
    .fhub .fhub-tile,
    .fhub-tile-icon,
    .fhub #fridge-drop-zone { transition: none; }
}

/* Dark theme */
body.dark-mode .fhub,
body.dark-theme .fhub,
[data-bs-theme="dark"] .fhub {
    --fhub-ink: #f1f5f9;
    --fhub-muted: #94a3b8;
    --fhub-line: rgba(255, 255, 255, 0.09);
    --fhub-surface: #1e293b;
    --fhub-dock-bg: rgba(15, 23, 42, 0.82);
    --fhub-shadow: 0 1px 2px rgba(0, 0, 0, 0.3), 0 12px 32px -16px rgba(0, 0, 0, 0.6);
}
body.dark-mode .fhub .fhub-tile:hover,
body.dark-theme .fhub .fhub-tile:hover,
[data-bs-theme="dark"] .fhub .fhub-tile:hover {
    background: rgba(255, 255, 255, 0.05);
}
body.dark-mode .fhub #fridge-drop-zone,
body.dark-theme .fhub #fridge-drop-zone,
[data-bs-theme="dark"] .fhub #fridge-drop-zone {
    background: rgba(255, 255, 255, 0.03) !important;
}

/* Dock focus: drop the global red focus glow, keep a tool-coloured keyboard ring */
.fhub .fhub-tile:focus { outline: none; }
.fhub .fhub-tile:focus:not(.active) { box-shadow: none; }

/* ===== Modern form system (scoped to hub panes) ===== */
.fhub {
    --fhub-field-bg: #f8fafc;
    --fhub-field-bg-focus: #ffffff;
    --fhub-field-line: rgba(15, 23, 42, 0.12);
    --fhub-accent: #6366f1;
    --fhub-ring: rgba(99, 102, 241, 0.18);
}
.fhub #familyAiContent .form-label,
.fhub #familyAiContent label.small,
.fhub #familyAiContent label.fw-bold,
.fhub #familyAiContent label.fw-semibold {
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    font-style: normal !important;
    letter-spacing: 0.01em;
    text-transform: none;
    color: var(--fhub-muted) !important;
    margin-bottom: 0.4rem;
}
.fhub #familyAiContent .small,
.fhub #familyAiContent small,
.fhub #familyAiContent .text-muted,
.fhub #familyAiContent .text-secondary { font-style: normal !important; }

.fhub #familyAiContent .form-control,
.fhub #familyAiContent .form-select,
.fhub #familyAiContent input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]):not(.form-check-input),
.fhub #familyAiContent textarea,
.fhub #familyAiContent select {
    min-height: 48px;
    padding: 0.7rem 1rem;
    font-family: inherit;
    font-size: 0.95rem !important;
    font-weight: 500;
    font-style: normal !important;
    text-transform: none !important;
    letter-spacing: normal;
    line-height: 1.45;
    color: var(--fhub-ink) !important;
    background-color: var(--fhub-field-bg) !important;
    border: 1px solid var(--fhub-field-line) !important;
    border-radius: 14px !important;
    box-shadow: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
}
.fhub #familyAiContent .form-control-sm,
.fhub #familyAiContent .form-select-sm {
    min-height: 40px;
    padding: 0.45rem 0.8rem;
    font-size: 0.85rem !important;
    border-radius: 12px !important;
}
.fhub #familyAiContent .form-select,
.fhub #familyAiContent select { padding-right: 2.5rem; background-position: right 0.95rem center; }
.fhub #familyAiContent textarea { min-height: 112px; resize: vertical; }
.fhub #familyAiContent .form-control:hover,
.fhub #familyAiContent .form-select:hover,
.fhub #familyAiContent textarea:hover,
.fhub #familyAiContent select:hover {
    border-color: color-mix(in srgb, var(--fhub-accent) 35%, var(--fhub-field-line)) !important;
}
.fhub #familyAiContent .form-control:focus,
.fhub #familyAiContent .form-select:focus,
.fhub #familyAiContent input:focus:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]),
.fhub #familyAiContent textarea:focus,
.fhub #familyAiContent select:focus {
    outline: none;
    background-color: var(--fhub-field-bg-focus) !important;
    border-color: var(--fhub-accent) !important;
    box-shadow: 0 0 0 4px var(--fhub-ring) !important;
}
.fhub #familyAiContent ::placeholder {
    color: var(--fhub-muted) !important;
    opacity: 0.75 !important;
    text-transform: none !important;
    font-style: normal !important;
    letter-spacing: normal !important;
    font-weight: 400;
}
/* Input groups read as one field: icon well + input share a single rounded shell */
.fhub #familyAiContent .input-group { flex-wrap: nowrap; }
.fhub #familyAiContent#familyAiContent .input-group > :not(:first-child) { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; margin-left: -1px; }
.fhub #familyAiContent#familyAiContent .input-group > :not(:last-child) { border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }
.fhub #familyAiContent#familyAiContent .input-group > .input-group-text:first-child { border-right-color: transparent !important; padding-right: 0.25rem; }
.fhub #familyAiContent#familyAiContent .input-group > .input-group-text:first-child + :is(input, .form-control, .form-select, select) { border-left-color: transparent !important; padding-left: 0.5rem; }
.fhub #familyAiContent .input-group-text {
    min-width: 46px;
    justify-content: center;
    border-radius: 14px;
    background: var(--fhub-field-bg) !important;
    border: 1px solid var(--fhub-field-line) !important;
    color: var(--fhub-muted) !important;
}
.fhub #familyAiContent .input-group:focus-within .input-group-text {
    border-color: var(--fhub-accent) !important;
    color: var(--fhub-accent) !important;
}
.fhub #familyAiContent .form-check-input {
    width: 1.15em;
    height: 1.15em;
    border-color: var(--fhub-field-line);
    cursor: pointer;
}
.fhub #familyAiContent .form-switch .form-check-input { width: 2.2em; border-radius: 2em; }
.fhub #familyAiContent .form-check-input:checked {
    background-color: var(--fhub-accent);
    border-color: var(--fhub-accent);
}
.fhub #familyAiContent .form-check-input:focus { box-shadow: 0 0 0 4px var(--fhub-ring); }
.fhub #familyAiContent input[type="range"] { accent-color: var(--fhub-accent); cursor: pointer; }
.fhub #familyAiContent input[type="color"] {
    border-radius: 12px !important;
    border: 1px solid var(--fhub-field-line) !important;
    padding: 3px;
    min-height: 40px;
    cursor: pointer;
}

/* Primary action buttons inside forms */
.fhub #familyAiContent .btn:not(.btn-sm):not(.btn-link):not(.rounded-circle) { min-height: 46px; }
.fhub #familyAiContent .btn { transition: transform 0.15s ease, box-shadow 0.2s ease, background-color 0.2s ease; }
.fhub #familyAiContent .btn:active { transform: scale(0.98); }
.fhub #familyAiContent .btn-success,
.fhub #familyAiContent .btn-primary {
    border: 0;
    box-shadow: 0 10px 24px -12px currentColor;
}
.fhub #familyAiContent .btn-success { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; }
.fhub #familyAiContent .btn-success:hover { box-shadow: 0 14px 28px -12px rgba(16, 185, 129, 0.7); transform: translateY(-1px); }

/* Voice recorder: true circle with a soft halo */
.fhub #fam-voice-btn {
    width: 104px !important;
    height: 104px !important;
    min-height: 104px !important;
    aspect-ratio: 1;
    padding: 0 !important;
    border-radius: 50% !important;
    box-shadow: 0 0 0 10px rgba(236, 72, 153, 0.1), 0 18px 40px -14px rgba(123, 3, 252, 0.7) !important;
}

/* Dark theme fields */
body.dark-mode .fhub,
body.dark-theme .fhub,
[data-bs-theme="dark"] .fhub {
    --fhub-field-bg: rgba(255, 255, 255, 0.04);
    --fhub-field-bg-focus: rgba(255, 255, 255, 0.07);
    --fhub-field-line: rgba(255, 255, 255, 0.12);
    --fhub-accent: #818cf8;
    --fhub-ring: rgba(129, 140, 248, 0.22);
}
body.dark-mode .fhub #familyAiContent .form-select,
body.dark-theme .fhub #familyAiContent .form-select,
[data-bs-theme="dark"] .fhub #familyAiContent .form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat;
    background-size: 14px 10px;
}
body.dark-mode .fhub #familyAiContent .form-select option,
body.dark-theme .fhub #familyAiContent .form-select option { background: #0f172a; color: #f1f5f9; }

/* Dock: neutralise global .nav-link.active underline; compact when pinned */
.fhub .fhub-tile,
.fhub .fhub-tile.active { border-bottom-width: 1px !important; }
.fhub .fhub-tile:not(.active) { border-bottom-color: transparent !important; }
.fhub-dock-sentinel { height: 1px; margin-bottom: -1px; }
@media (min-width: 992px) {
    .fhub-dock.is-stuck .fhub-dock-list { padding: 0.35rem; border-radius: 18px; }
    .fhub-dock.is-stuck .fhub-tile {
        flex-direction: row;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.5rem;
        border-radius: 13px;
    }
    .fhub-dock.is-stuck .fhub-tile-icon { width: 26px; height: 26px; border-radius: 8px; font-size: 0.78rem; }
    .fhub-dock.is-stuck .fhub-tile-label { font-size: 0.8rem; }
    .fhub-dock.is-stuck .fhub-tile-sub { display: none; }
    .fhub-dock.is-stuck .fhub-tile.active::after { left: 10px; right: 10px; }
}

/* In-pane tab strips (Flyer Studio controls) → segmented control */
.fhub #familyAiContent .nav-tabs {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.25rem;
    padding: 0.3rem;
    border: 1px solid var(--fhub-field-line) !important;
    border-radius: 14px;
    background: var(--fhub-field-bg);
}
.fhub #familyAiContent .nav-tabs .nav-item { flex: 1 1 0; }
.fhub #familyAiContent .nav-tabs .nav-link {
    width: 100%;
    min-height: 40px;
    border: 0 !important;
    border-radius: 10px !important;
    background: transparent !important;
    color: var(--fhub-muted) !important;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}
.fhub #familyAiContent .nav-tabs .nav-link:hover { color: var(--fhub-ink) !important; }
.fhub #familyAiContent .nav-tabs .nav-link.active {
    background: var(--fhub-surface) !important;
    color: var(--fhub-ink) !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08), 0 4px 12px -6px rgba(15, 23, 42, 0.25);
}

/* File pickers */
.fhub #familyAiContent input[type="file"].form-control { padding: 0.4rem; display: flex; align-items: center; }
.fhub #familyAiContent input[type="file"]::file-selector-button {
    margin: 0 0.85rem 0 0;
    padding: 0.45rem 0.95rem;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--fhub-accent), #a855f7);
    color: #ffffff;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    transition: filter 0.15s ease;
}
.fhub #familyAiContent input[type="file"]::file-selector-button:hover { filter: brightness(1.1); }


/* ===== Flyer Studio: embedded Universal Poster Engine ===== */
.fhub #familyAiContent .fhub-engine {
    padding: 0;
    overflow: hidden;
    background: #070b14 !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.fhub-engine-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 1rem 0.85rem 1.1rem;
    background: linear-gradient(90deg, rgba(6, 182, 212, 0.12), rgba(99, 102, 241, 0.12));
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.fhub-engine-title { display: flex; align-items: center; gap: 0.75rem; min-width: 0; }
.fhub-engine-badge {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: #fff;
    background: linear-gradient(135deg, #06b6d4, #6366f1);
    box-shadow: 0 8px 18px -8px #06b6d4;
}
.fhub-engine-title h3 { font-size: 1.05rem; font-weight: 800; letter-spacing: -0.02em; color: #f8fafc !important; }
.fhub-engine-title p { font-size: 0.8rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fhub-engine-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
.fhub #familyAiContent .fhub-ghost-btn {
    min-height: 44px;
    min-width: 44px;
    padding: 0.4rem 0.9rem;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    background: rgba(255, 255, 255, 0.05);
    color: #e2e8f0;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}
.fhub #familyAiContent .fhub-ghost-btn:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
.fhub-engine-stage {
    position: relative;
    height: max(640px, calc(100dvh - var(--fhub-sticky-top) - 150px));
}
.fhub-engine-stage iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    background: #070b14;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.fhub-engine-stage.is-ready iframe { opacity: 1; }
.fhub-engine-loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    color: #94a3b8;
    font-weight: 600;
    font-size: 0.9rem;
}
.fhub-engine-stage.is-ready .fhub-engine-loading { display: none; }
@media (max-width: 575.98px) {
    .fhub-engine-title p { display: none; }
    .fhub-engine-stage { height: max(560px, calc(100dvh - var(--fhub-sticky-top) - 130px)); }
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4 fhub" style="max-width: 1200px;">
    {{-- Hub Header Banner with User's Exact High-Definition Artwork & Interactive Hotspots --}}
    <div class="ai-hub-hero-banner shadow-sm">
        <h1 class="visually-hidden">Family Hub &mdash; The Fun Place</h1>
        <p class="visually-hidden">Your household creative playground &mdash; Family RoundTable, Family Journal, 15-minute fridge meal ideas, transcribed voice notes, illustrated family flyers, and Family Game Show.</p>
        
        <picture class="w-100 h-100 d-block">
            <source srcset="/public/img/family-hub-hero.webp, /img/family-hub-hero.webp" type="image/webp">
            <img src="/public/img/family-hub-hero.jpg" 
                 alt="Family Hub &mdash; The Fun Place: Your household creative playground, Family RoundTable, Family Journal, 15-minute fridge meal ideas, transcribed voice notes, illustrated family flyers, and Family Game Show." 
                 class="img-fluid w-100 h-100" 
                 width="1024" 
                 height="572" 
                 loading="eager" 
                 decoding="async"
                 fetchpriority="high"
                 onerror="if(!this.dataset.retried){this.dataset.retried='1';this.src='/img/family-hub-hero.jpg';}else if(this.dataset.retried==='1'){this.dataset.retried='2';this.src='/public/images/family-hub-hero.jpg';}">
        </picture>

        {{-- Interactive Clickable Hotspots Matching the 6 Neon Cards in the Artwork --}}
        <button type="button" 
           class="hero-hotspot" 
           style="top: 18.5%; left: 20.5%; width: 15%; height: 13.5%;" 
           onclick="document.getElementById('tab-roundtable-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
           title="Family RoundTable" 
           aria-label="Switch to Family RoundTable"></button>

        <button type="button" 
           class="hero-hotspot" 
           style="top: 36.5%; left: 28.5%; width: 15%; height: 13.5%;" 
           onclick="document.getElementById('tab-journal-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
           title="Family Journal" 
           aria-label="Switch to Family Journal"></button>

        <button type="button" 
                class="hero-hotspot" 
                style="top: 55.5%; left: 17%; width: 16%; height: 23%;" 
                onclick="document.getElementById('tab-fridge-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
                title="15-Minute Fridge Meals" 
                aria-label="Switch to 15-Minute Fridge Meals"></button>

        <button type="button" 
                class="hero-hotspot" 
                style="top: 11.5%; left: 68%; width: 14%; height: 15.5%;" 
                onclick="document.getElementById('tab-voice-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
                title="Transcribed Voice Notes" 
                aria-label="Switch to Transcribed Voice Notes"></button>

        <button type="button" 
                class="hero-hotspot" 
                style="top: 22.5%; left: 81%; width: 15.5%; height: 19%;" 
                onclick="document.getElementById('tab-flyer-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
                title="Illustrated Family Flyers" 
                aria-label="Switch to Illustrated Family Flyers"></button>

        <button type="button" 
                class="hero-hotspot" 
                style="top: 44.5%; left: 84.5%; width: 15%; height: 18%;" 
                onclick="document.getElementById('tab-trivia-btn').click(); document.getElementById('familyAiTabs').scrollIntoView({behavior: 'smooth'});" 
                title="Family Game Show" 
                aria-label="Switch to Family Game Show"></button>
    </div>

    {{-- ✨ Hub Tool Dock — single entry point for every Fun Place tool (sticky, swipeable on mobile) --}}
    @php
        $hubTools = [
            ['key' => 'fridge',     'icon' => 'bi-egg-fried',              'label' => 'Fridge Chef',      'sub' => 'Cook what you have',  'from' => '#10b981', 'to' => '#84cc16'],
            ['key' => 'voice',      'icon' => 'bi-mic-fill',               'label' => 'Voice Notes',      'sub' => 'Speak, we type',      'from' => '#f59e0b', 'to' => '#ef4444'],
            ['key' => 'flyer',      'icon' => 'bi-palette-fill',           'label' => 'Flyer Studio',     'sub' => 'Posters & invites','from' => '#06b6d4', 'to' => '#6366f1'],
            ['key' => 'trivia',     'icon' => 'bi-controller',             'label' => 'Game Show',        'sub' => 'Trivia & Two Truths',       'from' => '#f43f5e', 'to' => '#d946ef'],
            ['key' => 'roundtable', 'icon' => 'bi-shield-shaded',          'label' => 'RoundTable',       'sub' => 'Calm family talks',      'from' => '#eab308', 'to' => '#b45309'],
            ['key' => 'journal',    'icon' => 'bi-journal-bookmark-fill',  'label' => 'Journal',          'sub' => 'Family stories',       'from' => '#6366f1', 'to' => '#a855f7'],
            ['key' => 'outings',    'icon' => 'bi-compass-fill',           'label' => 'Outings',          'sub' => 'Great days out',       'from' => '#0ea5e9', 'to' => '#14b8a6'],
        ];
    @endphp
    <div class="fhub-dock-sentinel" aria-hidden="true"></div>
    <nav class="fhub-dock" aria-label="Family Hub tools">
        <ul class="nav fhub-dock-list" id="familyAiTabs" role="tablist">
            @foreach($hubTools as $i => $tool)
                <li class="nav-item" role="presentation">
                    <button class="nav-link fhub-tile {{ $i === 0 ? 'active' : '' }}"
                            id="tab-{{ $tool['key'] }}-btn"
                            data-bs-toggle="pill"
                            data-bs-target="#tab-{{ $tool['key'] }}"
                            data-hub-key="{{ $tool['key'] }}"
                            type="button" role="tab"
                            aria-controls="tab-{{ $tool['key'] }}"
                            aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                            style="--fhub-from: {{ $tool['from'] }}; --fhub-to: {{ $tool['to'] }};">
                        <span class="fhub-tile-icon" aria-hidden="true"><i class="bi {{ $tool['icon'] }}"></i></span>
                        <span class="fhub-tile-text">
                            <span class="fhub-tile-label">{{ $tool['label'] }}</span>
                            <span class="fhub-tile-sub">{{ $tool['sub'] }}</span>
                        </span>
                    </button>
                </li>
            @endforeach
        </ul>
    </nav>
    </ul>

    {{-- Panes --}}
    <div class="tab-content" id="familyAiContent">

        {{-- 🥦 TAB 1: FRIDGE MEAL PLANNER --}}
        <div class="tab-pane fade show active" id="tab-fridge" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-camera-fill text-success me-2"></i> Snap Your Fridge</h4>
                        <p class="text-muted small mb-4">Upload a quick snap of your open fridge, crisper drawer, or pantry. Our AI vision will identify ingredients and craft 2 fast 15-minute family recipes.</p>

                        <div class="drop-zone border-2 border-dashed rounded-4 p-4 text-center mb-3 bg-light" 
                             id="fridge-drop-zone"
                             onclick="document.getElementById('fridge-photo-input').click()"
                             style="cursor: pointer; border-color: #cbd5e1;">
                            <input type="file" id="fridge-photo-input" accept="image/*" class="d-none" onchange="previewFridgePhoto(this)">
                            <i class="bi bi-cloud-arrow-up display-4 text-success d-block mb-2"></i>
                            <span class="fw-bold text-dark d-block">Click to upload photo</span>
                            <span class="text-muted small">JPG, PNG, or HEIC (up to 25MB &mdash; auto-optimised)</span>
                        </div>

                        <div id="photo-preview-wrap" style="display:none;" class="mb-3 text-center">
                            <img id="fridge-preview-img" src="" alt="Fridge Preview" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px;">
                        </div>

                        <button type="button" id="btn-scan-fridge" class="btn btn-success rounded-pill w-100 fw-bold py-2" onclick="scanFridge()" style="min-height: 44px;">
                            <i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook
                        </button>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-book-half text-primary me-2"></i> 15-Minute Recipe Ideas</h4>
                        
                        <div id="detected-ingredients-wrap" style="display:none;" class="mb-4">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Detected Ingredients:</h6>
                            <div id="ingredients-badges" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        <div id="recipes-list" class="d-flex flex-column gap-3">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-basket display-4 d-block mb-2 text-secondary opacity-50"></i>
                                <span>No fridge photo analysed yet. Upload a picture of your pantry or fridge to see instant meal ideas.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎤 TAB 3: VOICE NOTES --}}
        <div class="tab-pane fade" id="tab-voice" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center mx-auto" style="max-width: 600px;">
                <h3 class="fw-bold mb-2">Family Voice Notes</h3>
                <p class="text-muted small mb-4">Dictate chore checklists, grocery items, or family memories effortlessly with instantaneous voice recognition and resilient AI transcription.</p>

                <div class="d-flex justify-content-center mb-4">
                    <button type="button" id="fam-voice-btn" class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
                            onclick="toggleFamVoice()" 
                            aria-label="Toggle voice recording"
                            style="width: 100px; height: 100px; background: linear-gradient(135deg, #7b03fc, #ec4899); border: none; color: white; font-size: 2.2rem; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="bi bi-mic-fill" id="fam-mic-icon"></i>
                    </button>
                </div>

                <div id="fam-voice-status" class="small fw-semibold text-muted mb-4">Click to Start Recording</div>

                <div class="text-start">
                    <label class="form-label fw-semibold small" for="fam-voice-output">Dictated Notes</label>
                    <textarea id="fam-voice-output" class="form-control rounded-3" rows="4" placeholder="Your spoken notes will appear here in real time..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
                        <button type="button" id="btn-copy-fam-voice" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-bold btn-spin-on-click" onclick="copyFamVoice()" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-clipboard me-1" id="btn-copy-fam-icon"></i> <span id="btn-copy-fam-text">Copy to Clipboard</span>
                        </button>
                        <button type="button" id="btn-clear-fam-voice" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold" onclick="clearFamVoice()" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-eraser me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎨 TAB 4: FAMILY FLYER STUDIO — Universal Poster Engine (shared-lib, /poster-studio) --}}
        <div class="tab-pane fade" id="tab-flyer" role="tabpanel">
            <div class="fhub-engine card" id="flyerEngineCard">
                <div class="fhub-engine-bar">
                    <div class="fhub-engine-title">
                        <span class="fhub-engine-badge" aria-hidden="true"><i class="bi bi-palette-fill"></i></span>
                        <div>
                            <h3 class="mb-0">Flyer Studio</h3>
                            <p class="mb-0">Design reunion, birthday, jubilee &amp; naming posters, then export or share.</p>
                        </div>
                    </div>
                    <div class="fhub-engine-actions">
                        <button type="button" class="btn btn-sm fhub-ghost-btn" id="btnFlyerEngineGuide" disabled>
                            <i class="bi bi-question-circle"></i><span class="d-none d-sm-inline"> How to use</span>
                        </button>
                        <a href="/poster-studio?app=familyplatform" target="_blank" rel="noopener" class="btn btn-sm fhub-ghost-btn" title="Open Flyer Studio full screen">
                            <i class="bi bi-arrows-fullscreen"></i><span class="d-none d-sm-inline"> Full screen</span>
                        </a>
                    </div>
                </div>
                <div class="fhub-engine-stage">
                    <div class="fhub-engine-loading" id="flyerEngineLoading" role="status">
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span>Loading Flyer Studio…</span>
                    </div>
                    <iframe id="flyerEngineFrame"
                            title="Flyer Studio poster designer"
                            data-src="/poster-studio?app=familyplatform"
                            loading="lazy"
                            allow="clipboard-write; web-share"></iframe>
                </div>
            </div>
        </div>

        {{-- 🎮 TAB 5: FAMILY GAME SHOW & RITUALS --}}
        <div class="tab-pane fade" id="tab-trivia" role="tabpanel">
            {{-- Game Mode Subtabs Navigation --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-2 bg-white rounded-pill shadow-sm border">
                <div class="nav nav-pills gap-1" id="gameModeSubTabs" role="tablist">
                    <button class="nav-link active rounded-pill fw-bold py-2 px-3 small d-inline-flex align-items-center gap-2" 
                            id="subtab-twotruths-btn" data-bs-toggle="pill" data-bs-target="#subtab-twotruths" type="button" role="tab" style="min-height: 40px;">
                        <i class="bi bi-mask text-danger"></i> 🎭 Two Truths &amp; a Lie (Weekly Challenge)
                    </button>
                    <button class="nav-link rounded-pill fw-bold py-2 px-3 small d-inline-flex align-items-center gap-2" 
                            id="subtab-trivia-btn" data-bs-toggle="pill" data-bs-target="#subtab-trivia" type="button" role="tab" style="min-height: 40px;">
                        <i class="bi bi-trophy text-warning"></i> 🏆 Live Family Game Show &amp; Trivia
                    </button>
                </div>
                <div class="px-2 d-none d-md-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalCreateTwoTruths" style="min-height: 38px;">
                        <i class="bi bi-plus-circle"></i> Create Challenge
                    </button>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold small">
                        <i class="bi bi-fire me-1"></i> Diaspora Ritual
                    </span>
                </div>
            </div>

            <div class="tab-content" id="gameModeSubContent">
                {{-- SUB-TAB 1: TWO TRUTHS & A LIE (HERITAGE EDITION) --}}
                <div class="tab-pane fade show active" id="subtab-twotruths" role="tabpanel">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" id="twoTruthsArenaCard">
                                {{-- Top Status Banner --}}
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            @php
                                                $ttAvatarRaw = (string)($twoTruthsGame['author_avatar'] ?? '');
                                                $ttAvatarSrc = !empty($ttAvatarRaw)
                                                    ? ((str_starts_with($ttAvatarRaw, '/') || str_starts_with($ttAvatarRaw, 'http')) ? $ttAvatarRaw : '/resources/images/profile/' . $ttAvatarRaw)
                                                    : '/resources/images/profile/avatarM.png';
                                            @endphp
                                            <img src="{{ $ttAvatarSrc }}" 
                                                 alt="{{ $twoTruthsGame['author_name'] ?? 'Storyteller' }}" 
                                                 class="rounded-circle shadow-sm object-fit-cover" 
                                                 width="48" 
                                                 height="48" 
                                                 style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; aspect-ratio: 1/1; object-fit: cover;" 
                                                 onerror="this.onerror=null; this.src='/resources/images/profile/avatarM.png';">
                                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-white p-1" title="Active Storyteller">
                                                <span class="visually-hidden">Active</span>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 mb-1 fw-bold small" id="tt-status-badge">
                                                {{ ($twoTruthsGame['status'] ?? '') === 'revealed' ? '🎉 Revealed & Archived' : '⏳ Live Weekend Challenge' }}
                                            </span>
                                            <h4 class="fw-bold mb-0 text-dark" id="tt-game-title">{{ $twoTruthsGame['title'] ?? 'Can you spot the lie?' }}</h4>
                                            <small class="text-muted">Storyteller: <strong class="text-dark" id="tt-author-name">{{ $twoTruthsGame['author_name'] ?? 'Family Member' }}</strong></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted" id="tt-countdown-label">Total Votes</div>
                                        <div class="fw-extrabold fs-4 text-danger" id="tt-total-votes">{{ $twoTruthsGame['total_votes'] ?? 0 }}</div>
                                    </div>
                                </div>

                                {{-- Instructions pill --}}
                                <div class="p-3 bg-light rounded-4 mb-4 border d-flex align-items-start gap-2">
                                    <i class="bi bi-info-circle-fill text-danger fs-5 flex-shrink-0 mt-1"></i>
                                    <div class="small text-muted" style="line-height: 1.45;">
                                        <strong>How to play:</strong> Two of these statements are authentic life moments, but one is an elaborate fabrication! Select the card you believe is the <strong>Lie</strong> and place your vote. When revealed, genuine memories are immortalised in our official Family Journal.
                                    </div>
                                </div>

                                {{-- Statement Cards Container --}}
                                <div class="d-flex flex-column gap-3 mb-4" id="twoTruthsCardsContainer">
                                    @php
                                        $userGuess = $twoTruthsGame['user_vote']['guessed_lie_index'] ?? null;
                                        $isRevealed = ($twoTruthsGame['status'] ?? '') === 'revealed';
                                        $actualLie = $twoTruthsGame['lie_index'] ?? null;
                                    @endphp

                                    @for($i = 1; $i <= 3; $i++)
                                        @php
                                            $stKey = "statement_{$i}";
                                            $stText = $twoTruthsGame[$stKey] ?? "Statement {$i}";
                                            $isMyPick = ($userGuess == $i);
                                            $isLie = ($isRevealed && $actualLie == $i);
                                            $isTruth = ($isRevealed && $actualLie != $i);
                                            $rvStory = $twoTruthsGame["reveal_story_{$i}"] ?? '';
                                            $votePct = 0;
                                            if (!empty($twoTruthsGame['total_votes']) && !empty($twoTruthsGame['vote_distribution'][$i])) {
                                                $votePct = round(($twoTruthsGame['vote_distribution'][$i] / $twoTruthsGame['total_votes']) * 100);
                                            }
                                        @endphp
                                        <div class="card rounded-4 border-2 p-3 p-md-4 transition-all tt-statement-card position-relative {{ $isMyPick ? 'border-primary bg-primary-subtle bg-opacity-10' : ($isLie ? 'border-danger bg-danger-subtle bg-opacity-25' : ($isTruth ? 'border-success bg-success-subtle bg-opacity-25' : 'border-light-subtle bg-white')) }}" 
                                             data-opt="{{ $i }}" 
                                             onclick="selectTwoTruthsOption({{ $i }})"
                                             style="cursor: {{ (!$twoTruthsGame['has_voted'] && !$isRevealed) ? 'pointer' : 'default' }}; min-height: 44px;">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="d-flex align-items-start gap-3">
                                                    <span class="badge rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 {{ $isLie ? 'bg-danger text-white' : ($isTruth ? 'bg-success text-white' : ($isMyPick ? 'bg-primary text-white' : 'bg-light text-dark border')) }}" style="width: 36px; height: 36px; font-size: 1rem;">
                                                        {{ $i }}
                                                    </span>
                                                    <div>
                                                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.05rem; line-height: 1.4;">
                                                            {{ $stText }}
                                                        </h5>
                                                        @if($isRevealed)
                                                            <div class="mt-2 pt-2 border-top small">
                                                                @if($isLie)
                                                                    <div class="text-danger fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i> THE LIE UNMASKED!</div>
                                                                    <div class="text-muted">{{ $rvStory ?: 'This was an elaborate fabrication crafted to fool the family!' }}</div>
                                                                @else
                                                                    <div class="text-success fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> VERIFIED FAMILY TRUTH &mdash; Archived in Journal</div>
                                                                    <div class="text-muted">{{ $rvStory ?: 'An authentic heritage memory now saved to our permanent records.' }}</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-end flex-shrink-0">
                                                    @if($isMyPick)
                                                        <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold small"><i class="bi bi-person-check-fill me-1"></i> Your Pick</span>
                                                    @elseif(!$twoTruthsGame['has_voted'] && !$isRevealed)
                                                        <div class="form-check m-0">
                                                            <input class="form-check-input tt-radio-select" type="radio" name="tt_choice" id="tt_radio_{{ $i }}" value="{{ $i }}" style="cursor: pointer; width: 22px; height: 22px;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(($twoTruthsGame['has_voted'] || $isRevealed) && !empty($twoTruthsGame['total_votes']))
                                                <div class="mt-3 pt-2">
                                                    <div class="d-flex justify-content-between small fw-semibold text-muted mb-1">
                                                        <span>{{ $twoTruthsGame['vote_distribution'][$i] ?? 0 }} vote(s)</span>
                                                        <span>{{ $votePct }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar {{ $isLie ? 'bg-danger' : ($isTruth ? 'bg-success' : 'bg-primary') }}" role="progressbar" style="width: {{ $votePct }}%;" aria-valuenow="{{ $votePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                </div>

                                {{-- Action Footer --}}
                                <div class="p-3 bg-light rounded-4 border" id="twoTruthsActionBox">
                                    @if(empty($twoTruthsGame['has_voted']) && !$isRevealed)
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="small text-muted">
                                                <i class="bi bi-hand-index-thumb text-danger me-1"></i> Tap an option card above to select your guess.
                                            </div>
                                            <button type="button" id="btn-cast-tt-vote" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="castTwoTruthsVote(this)" disabled style="min-height: 44px;">
                                                <i class="bi bi-send-fill"></i> Spot as Lie &amp; Cast Guess
                                            </button>
                                        </div>
                                    @elseif(!empty($twoTruthsGame['has_voted']) && !$isRevealed)
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div>
                                                <div class="fw-bold text-dark"><i class="bi bi-check-circle-fill text-success me-1"></i> Your Guess is Registered!</div>
                                                <div class="small text-muted">The lie unmasks Sunday. Share this challenge with cousins on WhatsApp:</div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="shareTwoTruthsToWhatsApp()" style="min-height: 44px; background: #25D366; border-color: #25D366;">
                                                    <i class="bi bi-whatsapp"></i> WhatsApp Challenge
                                                </button>
                                                @if(($twoTruthsGame['author_id'] ?? '') === ($userData['id'] ?? ''))
                                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="revealTwoTruthsGame(this, '{{ $twoTruthsGame['id'] ?? '' }}')" style="min-height: 44px;">
                                                        <i class="bi bi-unlock-fill"></i> Unmask Now
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- Revealed State --}}
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                            <div>
                                                <div class="fw-bold text-dark fs-5">
                                                    @if(!empty($twoTruthsGame['user_vote']['is_correct']))
                                                        🎉 <span class="text-success">Splendid! You Spotted the Lie! (+50 pts)</span>
                                                    @else
                                                        😅 <span class="text-danger">{{ $twoTruthsGame['author_name'] ?? 'The storyteller' }} fooled you! (+10 pts)</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">The verified truths have been permanently archived into our Family Journal.</div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="/family/journal" class="btn btn-outline-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1" style="min-height: 44px;">
                                                    <i class="bi bi-book"></i> Read in Journal
                                                </a>
                                                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalCreateTwoTruths" style="min-height: 44px;">
                                                    <i class="bi bi-plus-circle"></i> Create Next Challenge
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUB-TAB 2: LIVE GAME SHOW & TRIVIA --}}
                <div class="tab-pane fade" id="subtab-trivia" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                                <h4 class="fw-bold mb-2"><i class="bi bi-trophy-fill text-danger me-2"></i> Family Game Show</h4>
                                <p class="text-muted small mb-3">Host an affectionate family game show! Test how well relatives know family milestones, childhood stories, and funny memories.</p>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Family Name</label>
                                    <input type="text" id="trivia-family-name" class="form-control rounded-3" value="The {{ $userData['lastName'] ?? 'Family' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Game Show Theme</label>
                                    <select id="trivia-category" class="form-select rounded-3">
                                        <option value="family_folklore">📜 Family Folklore &amp; Heritage Milestones</option>
                                        <option value="childhood_memories">🧸 Hilarious Childhood Mishaps &amp; Stories</option>
                                        <option value="who_is_most_likely_to">🤔 Who Is Most Likely To...?</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold small mb-0"><i class="bi bi-chat-heart text-danger me-1"></i> Family Stories &amp; Memories</label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" onclick="insertExampleTriviaFacts()" style="font-size: 0.78rem;">
                                            <i class="bi bi-magic me-1"></i> Try an Example
                                        </button>
                                    </div>

                                    {{-- Friendly Guidance Box for Older & Less Tech-Educated Users --}}
                                    <div class="p-3 mb-2 rounded-3 bg-light border" style="font-size: 0.8rem; line-height: 1.45;">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Easy Guide for Family Stories:</div>
                                        <div class="text-muted mb-2">Write 2 to 4 quick memories or funny moments from your family. Put each story on its own line:</div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🎉 Milestones &amp; Origins</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Grandpa moved to London in 1968</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">😂 Funny Mishaps</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Uncle Tunde burnt the Christmas turkey</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🏆 Proud Achievements</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Maya won the school spelling bee</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🍲 Famous Claims &amp; Habits</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Auntie Simi claims the best jollof recipe</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <textarea id="trivia-facts" class="form-control rounded-3" rows="4" placeholder="Type or paste your family memories here, one per line...">Grandpa moved to London in 1968
Uncle Tunde burnt the Christmas turkey in 2012
Auntie Simi claims she invented Nigerian fried rice
Maya won the school spelling bee with the word 'onomatopoeia'</textarea>

                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-check2-circle text-success me-1"></i>Each line becomes an interactive question for family to play.</small>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" onclick="document.getElementById('trivia-facts').value='';" style="font-size: 0.72rem;">Clear text</button>
                                    </div>
                                </div>

                                <button type="button" id="btn-launch-trivia" class="btn btn-danger rounded-pill w-100 fw-bold py-2 mt-2 d-flex align-items-center justify-content-center gap-2" onclick="generateTriviaShow()" style="min-height: 44px;">
                                    <i class="bi bi-play-circle-fill"></i> Start Family Game Show
                                </button>
                                <div class="text-center mt-2">
                                    <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-broadcast text-primary me-1"></i>Automatically posted to your family feed so everyone can play</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                                {{-- Posted to Feed Confirmation Banner --}}
                                <div id="trivia-posted-alert" class="alert alert-success d-flex align-items-center justify-content-between rounded-4 p-3 mb-3 border-0 shadow-sm text-start" style="display:none;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                                        <div>
                                            <div class="fw-bold text-dark">Posted to Family Feed!</div>
                                            <div class="text-muted small">Your Family Game Show &amp; Round 1 poll are now live for everyone to play.</div>
                                        </div>
                                    </div>
                                    <a id="trivia-post-feed-link" href="/profilePage" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold text-nowrap px-3">
                                        View on Feed <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>

                                <div id="trivia-empty-state" class="py-5">
                                    <i class="bi bi-trophy display-3 text-warning opacity-75 d-block mb-3"></i>
                                    <h4 class="fw-bold">Ready for Family Game Show Night?</h4>
                                    <p class="text-muted small mx-auto" style="max-width: 420px;">
                                        Enter your family's memories and stories on the left and tap 'Start Family Game Show' to launch an interactive game show.
                                    </p>
                                </div>

                                <div id="trivia-game-arena" style="display:none;" class="text-start">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                        <div>
                                            <span class="badge bg-danger rounded-pill px-3 py-1 mb-1">Live Game Show</span>
                                            <h4 id="trivia-game-title" class="fw-bold mb-0 text-dark">Family Game Show</h4>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">Score</div>
                                            <div class="fw-extrabold fs-4 text-primary" id="trivia-score">0 pts</div>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-light rounded-4 mb-3 border">
                                        <span class="badge bg-secondary mb-2" id="trivia-q-num">Question 1 of 3</span>
                                        <h5 class="fw-bold text-dark mb-0" id="trivia-q-text">Loading question...</h5>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mb-3" id="trivia-options-box">
                                        {{-- Dynamically populated options --}}
                                    </div>

                                    <div id="trivia-feedback-box" class="p-3 rounded-3 small fw-semibold mb-3" style="display:none;"></div>

                                    <div class="d-flex justify-content-end">
                                        <button type="button" id="btn-next-trivia" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="nextTriviaQuestion()" style="display:none;">
                                            Next Question <i class="bi bi-chevron-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🗺️ TAB 6: LUXURY FAMILY OUTINGS & CULTURAL CONCIERGE --}}
        <div class="tab-pane fade" id="tab-outings" role="tabpanel">
            {{-- Luxury Editorial Salon Header --}}
            <div class="outings-salon-hero p-4 p-md-5 mb-4 position-relative">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold mb-2.5 d-inline-flex align-items-center gap-1.5" style="background: rgba(212, 175, 55, 0.12); color: #8c6d1f; border: 1px solid rgba(212, 175, 55, 0.35); font-size: 0.72rem; letter-spacing: 0.06em;">
                            <i class="bi bi-compass-fill text-warning"></i> FAMILYPLATFORM LUXURY CONCIERGE
                        </span>
                        <h2 class="fw-bold mb-2 text-dark" style="font-family: 'Playfair Display', Georgia, serif; font-size: clamp(1.5rem, 3vw, 2.1rem); letter-spacing: -0.015em;">
                            Weekend &amp; Holiday Outings Salon
                        </h2>
                        <div style="height: 2px; width: 64px; background: linear-gradient(90deg, #d4af37 0%, transparent 100%); margin: 0.85rem 0;"></div>
                        <p class="text-secondary mb-3 mb-lg-0" style="font-size: 0.92rem; line-height: 1.65; max-width: 640px;">
                            Curated days out, iconic cultural exhibitions, private heritage walks, scenic countryside drives, and exclusive staycations for your family — enriched with commercial partner booking privileges.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="p-3 rounded-4 border bg-white shadow-sm d-inline-block text-start" style="border-color: rgba(212,175,55,0.25) !important;">
                            <div class="small fw-bold text-dark d-flex align-items-center gap-1.5 mb-1" style="font-size: 0.82rem;">
                                <i class="bi bi-shield-check text-success"></i> 100% Verified Attractions
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem; line-height: 1.4;">
                                Curated across Viator, GetYourGuide, National Trust &amp; Booking.com.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Interactive Luxury Preset Rails --}}
                <div class="mt-3.5 pt-3 border-top outings-preset-rail-wrap" style="border-color: rgba(212, 175, 55, 0.2) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="small fw-bold text-dark text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.72rem; letter-spacing: 0.05em; color: #8c6d1f !important;">
                            <i class="bi bi-lightning-charge-fill text-warning"></i> Curated Destinations &amp; Inspiration
                        </div>
                        <span class="text-muted small d-inline-block d-md-none" style="font-size: 0.68rem;"><i class="bi bi-arrow-left-right me-1"></i>Swipe</span>
                    </div>
                    <div class="outings-preset-rail">
                        <button type="button" class="outings-preset-chip active" data-city="South Kensington London SW7" onclick="setOutingsPreset('South Kensington London SW7', 'science museums, dinosaur galleries, family exhibitions')">
                            <span class="outings-chip-icon">🏛️</span>
                            <span class="outings-chip-text">Kensington Museums</span>
                            <span class="outings-chip-loc">SW7</span>
                        </button>
                        <button type="button" class="outings-preset-chip" data-city="Richmond London TW10" onclick="setOutingsPreset('Richmond London TW10', 'botanical gardens, deer walks, outdoor family picnics')">
                            <span class="outings-chip-icon">🌿</span>
                            <span class="outings-chip-text">Richmond &amp; Kew</span>
                            <span class="outings-chip-loc">TW10</span>
                        </button>
                        <button type="button" class="outings-preset-chip" data-city="Cotswolds GL54" onclick="setOutingsPreset('Cotswolds GL54', 'scenic countryside drive, rare farm breeds, afternoon tea')">
                            <span class="outings-chip-icon">🚗</span>
                            <span class="outings-chip-text">Cotswolds Tour</span>
                            <span class="outings-chip-loc">GL54</span>
                        </button>
                        <button type="button" class="outings-preset-chip" data-city="Warwick CV34" onclick="setOutingsPreset('Warwick CV34', 'medieval castle, falconry jousting, woodland glamping')">
                            <span class="outings-chip-icon">🏰</span>
                            <span class="outings-chip-text">Warwick Castle</span>
                            <span class="outings-chip-loc">CV34</span>
                        </button>
                        <button type="button" class="outings-preset-chip" data-city="Birmingham B30" onclick="setOutingsPreset('Birmingham B30', 'chocolate factory experience, interactive science museum, minibrum')">
                            <span class="outings-chip-icon">🍫</span>
                            <span class="outings-chip-text">Cadbury World</span>
                            <span class="outings-chip-loc">B30</span>
                        </button>
                        <button type="button" class="outings-preset-chip" data-city="Cornwall TR26" onclick="setOutingsPreset('Cornwall TR26', 'coastal holiday cottages, beach walks, family staycation')">
                            <span class="outings-chip-icon">🏡</span>
                            <span class="outings-chip-text">Coastal Cottages</span>
                            <span class="outings-chip-loc">Cornwall</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Left: Curated Scout Filter Form --}}
                <div class="col-lg-5">
                    <div class="outings-surface-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem;">
                                <i class="bi bi-sliders2-vertical text-warning me-2"></i> Customise Discovery
                            </h4>
                            <span class="badge bg-light text-secondary border small px-2 py-1" style="font-size: 0.7rem;">UK Wide</span>
                        </div>
                        <p class="text-secondary small mb-4" style="font-size: 0.82rem; line-height: 1.55;">
                            Input any UK town, city, or outward postcode to discover verified exhibitions, barbecue parks, scenic road trips, and holiday deals.
                        </p>

                        <form id="family-outings-form" onsubmit="handleScoutFamilyOutings(event)">
                            @csrf
                            <div class="mb-3.5">
                                <label class="form-label fw-semibold small text-dark d-flex align-items-center justify-content-between" for="outings-city" style="font-size: 0.82rem;">
                                    <span><i class="bi bi-geo-alt-fill text-danger me-1"></i> Town, City or Outward Postcode</span>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">Privacy Guarded</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted rounded-start-3" style="border-color: #cbd5e1;"><i class="bi bi-search"></i></span>
                                    <input type="text" id="outings-city" class="form-control border-start-0 rounded-end-3 py-2.5" value="South Kensington London SW7" placeholder="e.g. London SW7, Birmingham B1, Edinburgh EH1" required style="border-color: #cbd5e1; font-size: 0.88rem;">
                                </div>
                                <div class="form-text small text-muted mt-1.5" style="font-size: 0.72rem;">
                                    <i class="bi bi-shield-lock-fill text-success me-1"></i> Full postcodes are automatically anonymised to outward districts for family privacy.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold small text-dark" for="outings-interests" style="font-size: 0.82rem;">
                                    <i class="bi bi-stars text-warning me-1"></i> Family Interests &amp; Activity Theme
                                </label>
                                <textarea id="outings-interests" class="form-control rounded-3 py-2.5" rows="3" placeholder="e.g. science museums, dinosaur galleries, barbecue parklands, scenic drives, cottage staycations" style="border-color: #cbd5e1; font-size: 0.88rem;">science museums, outdoor parks, child-friendly exhibitions</textarea>
                                <div class="d-flex align-items-center gap-1.5 mt-2 flex-wrap">
                                    <span class="badge bg-light text-secondary border cursor-pointer hover:border-dark" style="font-size: 0.68rem; cursor: pointer;" onclick="document.getElementById('outings-interests').value='free interactive museums, child discovery zones'">+ Museums</span>
                                    <span class="badge bg-light text-secondary border cursor-pointer hover:border-dark" style="font-size: 0.68rem; cursor: pointer;" onclick="document.getElementById('outings-interests').value='designated barbecue areas, picnic lawns, play park'">+ Barbecues</span>
                                    <span class="badge bg-light text-secondary border cursor-pointer hover:border-dark" style="font-size: 0.68rem; cursor: pointer;" onclick="document.getElementById('outings-interests').value='scenic countryside drive, viewpoint, family pub'">+ Road Trips</span>
                                    <span class="badge bg-light text-secondary border cursor-pointer hover:border-dark" style="font-size: 0.68rem; cursor: pointer;" onclick="document.getElementById('outings-interests').value='family holiday cottage, coastal beach break, lodges'">+ Staycations</span>
                                </div>
                            </div>

                            <button type="submit" id="btn-scout-outings" class="btn outings-btn-search w-100 rounded-pill py-2.5 d-inline-flex align-items-center justify-content-center gap-2" style="font-size: 0.92rem;">
                                <i class="bi bi-compass"></i> Discover Curated Outings
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right: Curated Recommendations & Interactive Polls --}}
                <div class="col-lg-7">
                    <div class="outings-surface-card h-100">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem;">
                                    <i class="bi bi-gem text-warning me-1.5"></i> Curated Family Experiences
                                </h4>
                                <div class="text-secondary small" style="font-size: 0.76rem;">Real-time verified attractions with partner privileges</div>
                            </div>
                            <button type="button" id="btn-post-outings-poll" class="btn rounded-pill px-3.5 py-2 fw-bold btn-sm d-none align-items-center gap-2 text-white shadow-sm" onclick="postOutingsPollToFeed()" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); min-height: 44px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.25); font-size: 0.8rem;">
                                <i class="bi bi-broadcast"></i> 🗳️ Post Outings Vote to Family Feed
                            </button>
                        </div>

                        <div id="outings-posted-alert" class="alert alert-success d-none align-items-center justify-content-between rounded-4 p-3 mb-3 border-0 shadow-sm" style="background: #ecfdf5; border: 1px solid #a7f3d0 !important;">
                            <div class="d-flex align-items-center gap-2.5">
                                <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Published to Family Feed!</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">Your outing poll is live for all family members to cast their votes.</div>
                                </div>
                            </div>
                            <a id="outings-feed-link" href="/profilePage" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold text-nowrap px-3" style="min-height: 38px;">
                                View Poll <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>

                        <div id="family-outings-results" class="d-flex flex-column gap-3">
                            <div class="text-center py-5 px-3">
                                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 76px; height: 76px; background: linear-gradient(135deg, rgba(212,175,55,0.15) 0%, rgba(212,175,55,0.05) 100%); border: 2px dashed #d4af37;">
                                    <i class="bi bi-compass text-warning fs-1"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1" style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.25rem;">Explore Curated Family Adventures</h5>
                                <p class="text-secondary small mb-3.5" style="max-width: 440px; margin: 0 auto; line-height: 1.6; font-size: 0.84rem;">
                                    Tap any destination chip above or customise your search on the left to reveal bookable family outings, exhibitions, and country breaks.
                                </p>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" style="min-height: 38px; font-size: 0.78rem;" onclick="setOutingsPreset('South Kensington London SW7', 'science museums, dinosaur galleries, family exhibitions')">
                                        🏛️ Scout South Kensington
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" style="min-height: 38px; font-size: 0.78rem;" onclick="setOutingsPreset('Cotswolds GL54', 'scenic countryside drive, rare farm breeds, afternoon tea')">
                                        🚗 Scout Cotswolds
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🛡️ TAB 7: FAMILY ROUNDTABLE (RECONCILIATORY COUNCIL) --}}
        <div class="tab-pane fade" id="tab-roundtable" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white position-relative overflow-hidden" style="border: 1.5px solid rgba(212, 175, 55, 0.35) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">
                    <div>
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold mb-2 d-inline-flex align-items-center gap-1.5" style="background: rgba(212, 175, 55, 0.12); color: #8c6d1f; border: 1px solid rgba(212, 175, 55, 0.35); font-size: 0.75rem;">
                            <i class="bi bi-shield-shaded"></i> ✨ KINSHIP COUNCIL &bull; RECONCILIATORY JUSTICE
                        </span>
                        <h3 class="fw-bold text-dark mb-1" style="font-family: 'Cinzel', serif;">Family RoundTable</h3>
                        <p class="text-muted small mb-0" style="max-width: 650px;">
                            A dignified household council for reconciling family tensions calmly with empathy, zero blame, and restorative accords that preserve kinship bonds.
                        </p>
                    </div>
                    <a href="/family/roundtable" class="btn btn-outline-dark rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="min-height: 44px; white-space: nowrap; font-size: 0.88rem; border-color: #d4af37;">
                        <i class="bi bi-box-arrow-up-right text-warning"></i> Open Grand Council Chamber
                    </a>
                </div>

                <div class="row g-4">
                    {{-- Left: Quick 2-Minute Peacemaker --}}
                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100" style="background: #fafafa; border: 1.5px solid #e2e8f0;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-lightning-charge-fill text-warning me-1.5"></i> Quick 2-Minute Peacemaker
                                </h5>
                                <span class="badge bg-light text-secondary border px-2 py-1 small">Instant Resolution</span>
                            </div>
                            <p class="text-muted small mb-3">
                                Describe the issue or disagreement between two family members to receive instant, balanced reconciliatory guidance.
                            </p>

                            <form id="inline-roundtable-peacemaker-form" onsubmit="handleInlineArbitrate(event)">
                                @csrf
                                <div class="row g-2 mb-2.5">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1" for="rt-party-a-name">First Member (Party A)</label>
                                        <input type="text" id="rt-party-a-name" class="form-control form-control-sm rounded-3" value="{{ $userData['firstName'] ?? 'Myself' }}" required placeholder="e.g. Mum, Alex">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1" for="rt-party-b-name">Second Member (Party B)</label>
                                        <input type="text" id="rt-party-b-name" class="form-control form-control-sm rounded-3" required placeholder="e.g. Dad, Sibling">
                                    </div>
                                </div>
                                <div class="mb-2.5">
                                    <label class="form-label small fw-semibold mb-1" for="rt-party-a-claim">Party A's Perspective / Grievance</label>
                                    <input type="text" id="rt-party-a-claim" class="form-control form-control-sm rounded-3" required placeholder="e.g. I did the washing up twice yesterday...">
                                </div>
                                <div class="mb-2.5">
                                    <label class="form-label small fw-semibold mb-1" for="rt-party-b-claim">Party B's Perspective</label>
                                    <input type="text" id="rt-party-b-claim" class="form-control form-control-sm rounded-3" required placeholder="e.g. I worked late and had to care for the toddler...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold mb-1" for="rt-dispute-context">Household Context &amp; Category</label>
                                    <select id="rt-dispute-context" class="form-select form-select-sm rounded-3">
                                        <option value="chores_cleaning">🧹 Chores &amp; Housework Equity</option>
                                        <option value="screen_time_noise">📱 Screen Time, Noise &amp; Sleep Boundaries</option>
                                        <option value="borrowing_items">👕 Borrowing Clothes &amp; Belongings</option>
                                        <option value="communication_tone">🗣️ Tone of Voice &amp; Misunderstanding</option>
                                        <option value="events_gatherings">📅 Family Schedule &amp; Gathering Commitments</option>
                                    </select>
                                </div>

                                <button type="submit" id="btn-inline-arbitrate" class="btn btn-dark w-100 rounded-pill py-2 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border: 1.5px solid #d4af37; min-height: 44px;">
                                    <i class="bi bi-peace text-warning"></i> Deliberate Restorative Accord
                                </button>
                            </form>

                            {{-- Verdict Container --}}
                            <div id="rt-verdict-card" class="mt-3 p-3 rounded-3" style="display: none; background: #ffffff; border: 1.5px solid #d4af37; box-shadow: 0 4px 14px rgba(0,0,0,0.06);">
                                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                    <span class="badge bg-warning text-dark fw-bold rounded-pill">🕊️ Accord Sealed</span>
                                    <span class="small text-muted fw-semibold" id="rt-closing-quip"></span>
                                </div>
                                <p class="small text-dark mb-2 fw-medium" id="rt-verdict-box" style="line-height: 1.55;"></p>
                                <div class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Action Compromise Plan:</div>
                                <div id="rt-action-plan-list" class="d-flex flex-column gap-1 mb-2"></div>
                                <div class="p-2 rounded-2 bg-light small text-secondary fst-italic border" id="rt-penalty-box"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Active Dockets & Kinship Principles --}}
                    <div class="col-lg-6">
                        <div class="p-4 rounded-4 h-100 d-flex flex-column" style="background: #ffffff; border: 1.5px solid #e2e8f0;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-archive-fill text-primary me-1.5"></i> Family Council Dockets
                                </h5>
                                <span class="badge bg-light text-muted border px-2 py-1 small">Current Matters</span>
                            </div>

                            @if(!empty($disputes) && count($disputes) > 0)
                                <div class="d-flex flex-column gap-2.5 overflow-auto flex-grow-1" style="max-height: 380px;">
                                    @foreach($disputes as $d)
                                        <div class="p-3.5 rounded-3 border roundtable-docket-card" style="background: #f8fafc;">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1.5">
                                                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.94rem; line-height: 1.4;">{{ $d['title'] ?? 'Family Discussion' }}</h6>
                                                <span class="badge rounded-pill {{ ($d['status'] ?? '') === 'adjudicated' ? 'bg-success' : 'bg-warning text-dark' }} small flex-shrink-0">
                                                    {{ ucfirst($d['status'] ?? 'Active') }}
                                                </span>
                                            </div>
                                            <div class="text-muted small mb-2.5" style="font-size: 0.8rem;">
                                                Category: <span class="fw-semibold text-secondary">{{ ucwords(str_replace('_', ' ', $d['category'] ?? 'General')) }}</span>
                                            </div>
                                            <a href="/family/roundtable?dispute_id={{ $d['id'] ?? '' }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center" style="font-size: 0.8rem; min-height: 44px;">
                                                View Council File <i class="bi bi-arrow-right ms-1.5"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 my-auto">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 58px; height: 58px; background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 1.6rem;">
                                        <i class="bi bi-peace"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Domestic Harmony Prevails</h6>
                                    <p class="text-muted small mb-3" style="max-width: 320px; margin: 0 auto;">
                                        No contentious disputes are active on your family docket. Keep lines of communication open and loving!
                                    </p>
                                    <a href="/family/roundtable" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold" style="min-height: 38px; font-size: 0.8rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Convene Formal Hearing
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📖 TAB 8: FAMILY LIVING JOURNAL & CHRONICLES --}}
        <div class="tab-pane fade" id="tab-journal" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white position-relative overflow-hidden" style="border: 1.5px solid rgba(99, 102, 241, 0.3) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">
                    <div>
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold mb-2 d-inline-flex align-items-center gap-1.5" style="background: rgba(99, 102, 241, 0.12); color: #4338ca; border: 1px solid rgba(99, 102, 241, 0.35); font-size: 0.75rem;">
                            <i class="bi bi-journal-bookmark-fill"></i> 📖 HEIRLOOM MEMORIES &bull; ORAL HISTORIES
                        </span>
                        <h3 class="fw-bold text-dark mb-1" style="font-family: 'Playfair Display', Georgia, serif;">Family Living Journal</h3>
                        <p class="text-muted small mb-0" style="max-width: 650px;">
                            A private household sanctuary to chronicle heirloom recipes, generational triumphs, grandparent memories, and oral histories away from public social feeds.
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/familyJournal/create" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none; min-height: 44px; white-space: nowrap; font-size: 0.88rem;">
                            <i class="bi bi-pencil-square"></i> Write New Chronicle
                        </a>
                        <a href="/familyJournal" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" style="min-height: 44px; white-space: nowrap; font-size: 0.88rem;">
                            <i class="bi bi-bookshelf"></i> Open Full Shelf
                        </a>
                    </div>
                </div>

                @if(!empty($journals) && count($journals) > 0)
                    <div class="row g-4">
                        @foreach($journals as $j)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border rounded-4 p-4 p-md-4 shadow-sm transition-all family-journal-card" style="background: #ffffff; border-color: #e2e8f0 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge rounded-pill px-3 py-1.5 text-uppercase" style="font-size: 0.72rem; font-weight: 700; background: rgba(99, 102, 241, 0.12); color: #4338ca;">
                                            {{ $j['category'] ?? 'Heritage' }}
                                        </span>
                                        <span class="text-muted small" style="font-size: 0.75rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $j['reading_time'] ?? '3 min' }} read
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2.5" style="font-size: 1.1rem; line-height: 1.45;">
                                        {{ $j['title'] ?? 'Untitled Chronicle' }}
                                    </h5>
                                    <p class="text-muted small mb-4 flex-grow-1" style="font-size: 0.88rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ strip_tags($j['content'] ?? $j['story'] ?? '') }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                        <span class="small fw-semibold text-secondary" style="font-size: 0.8rem;">
                                            By {{ $j['author_name'] ?? $j['userName'] ?? 'Family Chronicler' }}
                                        </span>
                                        <a href="/familyJournal/{{ $j['id'] ?? '' }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0 d-inline-flex align-items-center gap-1" style="color: #4f46e5; font-size: 0.85rem; min-height: 44px;">
                                            Read Chronicle &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 px-3 rounded-4" style="background: #fafafa; border: 1.5px dashed #cbd5e1;">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 72px; height: 72px; background: rgba(99, 102, 241, 0.12); color: #4f46e5; font-size: 2rem;">
                            <i class="bi bi-journal-plus"></i>
                        </div>
                        <h4 class="fw-bold mb-1 text-dark" style="font-family: 'Playfair Display', Georgia, serif;">No Chronicles Recorded Yet</h4>
                        <p class="text-muted mx-auto mb-4" style="max-width: 500px; font-size: 0.88rem; line-height: 1.6;">
                            Preserve your living family story. Chronicle an unforgettable family celebration, a grandfather's childhood memory, an ancestral journey, or grandmother's secret recipe.
                        </p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="/familyJournal/create" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border: none; min-height: 44px;">
                                <i class="bi bi-magic me-1.5"></i> Draft with AI Story Assistant
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
    // 🛡️ INLINE FAMILY ROUNDTABLE PEACEMAKER ARBITRATION
    async function handleInlineArbitrate(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-inline-arbitrate');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Deliberating Accord...';

        try {
            const res = await fetch('/api/roundtable/arbitrate-instant', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    party_a_name: document.getElementById('rt-party-a-name').value,
                    party_a_claim: document.getElementById('rt-party-a-claim').value,
                    party_b_name: document.getElementById('rt-party-b-name').value,
                    party_b_claim: document.getElementById('rt-party-b-claim').value,
                    context: document.getElementById('rt-dispute-context').value
                })
            });
            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-peace text-warning me-1"></i> Deliberate Restorative Accord';

            if (data.success) {
                document.getElementById('rt-verdict-box').textContent = data.verdict;
                const planList = document.getElementById('rt-action-plan-list');
                planList.innerHTML = '';
                for (const [person, task] of Object.entries(data.compromise_action_plan || {})) {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center justify-content-between p-2 bg-light rounded-3';
                    row.innerHTML = `<span class="fw-bold small text-dark">${person}:</span><span class="small text-muted">${task}</span>`;
                    planList.appendChild(row);
                }
                document.getElementById('rt-penalty-box').textContent = data.penalty_or_chore;
                document.getElementById('rt-closing-quip').textContent = `"${data.closing_quip}"`;
                document.getElementById('rt-verdict-card').style.display = 'block';
            } else {
                alert(data.error || 'Peacemaker arbitration failed.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-peace text-warning me-1"></i> Deliberate Restorative Accord';
            alert('Request error: ' + err.message);
        }
    }

    // 🥦 FRIDGE SCANNER
    async function compressImageForUpload(file, maxDimension = 1600, quality = 0.82) {
        if (!file || !(file instanceof Blob)) return file;
        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();
            reader.onload = (e) => {
                img.onload = () => {
                    let { width, height } = img;
                    if (width > maxDimension || height > maxDimension) {
                        if (width > height) {
                            height = Math.round((height * maxDimension) / width);
                            width = maxDimension;
                        } else {
                            width = Math.round((width * maxDimension) / height);
                            height = maxDimension;
                        }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        resolve(blob || file);
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    function previewFridgePhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('fridge-preview-img').src = e.target.result;
                document.getElementById('photo-preview-wrap').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    async function scanFridge() {
        const fileInput = document.getElementById('fridge-photo-input');
        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please select or snap a photo of your fridge first.');
            return;
        }

        const btn = document.getElementById('btn-scan-fridge');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Optimising &amp; Analysing Ingredients...';

        try {
            // Client-side pre-compression (shared-js-lib pattern): reduces 10-25MB camera uploads down to ~300KB
            const originalFile = fileInput.files[0];
            const optimizedBlob = await compressImageForUpload(originalFile, 1600, 0.82);

            const fd = new FormData();
            fd.append('photo', optimizedBlob, originalFile.name || 'fridge.jpg');

            const res = await fetch('/api/ai/inspect-fridge', { method: 'POST', body: fd });
            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook';

            if (data.success) {
                const badgesWrap = document.getElementById('ingredients-badges');
                badgesWrap.innerHTML = '';
                (data.detected_ingredients || []).forEach(item => {
                    const b = document.createElement('span');
                    b.className = 'badge bg-light text-dark border px-2 py-1 rounded-pill small';
                    b.textContent = item;
                    badgesWrap.appendChild(b);
                });
                document.getElementById('detected-ingredients-wrap').style.display = 'block';

                const list = document.getElementById('recipes-list');
                list.innerHTML = '';
                (data.suggested_recipes || []).forEach(r => {
                    const card = document.createElement('div');
                    card.className = 'card border rounded-4 p-3 bg-light';
                    let stepsHtml = '';
                    (r.instructions || []).forEach((st, idx) => {
                        stepsHtml += `<li class="small text-muted mb-1">${st}</li>`;
                    });
                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">${r.title}</h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                <i class="bi bi-clock me-1"></i>${r.cooking_time_minutes} mins
                            </span>
                        </div>
                        <ol class="ps-3 mb-0">${stepsHtml}</ol>
                    `;
                    list.appendChild(card);
                });
            } else {
                alert(data.error || 'Could not analyse fridge image.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook';
            alert('Scan error: ' + err.message);
        }
    }

    // 🎤 VOICE NOTES (Dual-Engine: Live Browser SpeechRecognition + Resilient Server AI Cascade)
    let famRecorder = null;
    let famChunks = [];
    let isFamRecording = false;
    let famSpeechRecognition = null;
    let liveSpeechTranscript = '';

    async function toggleFamVoice() {
        const btn = document.getElementById('fam-voice-btn');
        const status = document.getElementById('fam-voice-status');
        const icon = document.getElementById('fam-mic-icon');

        if (!isFamRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                famRecorder = new MediaRecorder(stream);
                famChunks = [];
                liveSpeechTranscript = '';

                // 1. Initialise Live Web Speech API if supported by browser (Instant zero-latency feedback)
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (SpeechRec) {
                    try {
                        famSpeechRecognition = new SpeechRec();
                        famSpeechRecognition.continuous = true;
                        famSpeechRecognition.interimResults = true;
                        famSpeechRecognition.lang = 'en-GB';

                        famSpeechRecognition.onresult = (e) => {
                            let interim = '';
                            let final = '';
                            for (let i = 0; i < e.results.length; ++i) {
                                if (e.results[i].isFinal) {
                                    final += e.results[i][0].transcript + ' ';
                                } else {
                                    interim += e.results[i][0].transcript;
                                }
                            }
                            const current = (final + interim).trim();
                            if (current) {
                                liveSpeechTranscript = current;
                                const outEl = document.getElementById('fam-voice-output');
                                if (outEl) outEl.value = current;
                            }
                        };
                        famSpeechRecognition.onerror = (e) => {
                            console.warn('[VoiceNotes] Live speech recognition notice:', e.error);
                        };
                        famSpeechRecognition.start();
                    } catch (recErr) {
                        console.warn('[VoiceNotes] SpeechRecognition startup notice:', recErr);
                    }
                }

                famRecorder.ondataavailable = e => { if (e.data.size > 0) famChunks.push(e.data); };
                famRecorder.onstop = async () => {
                    // Stop stream tracks
                    stream.getTracks().forEach(t => t.stop());

                    const blob = new Blob(famChunks, { type: 'audio/webm' });
                    if (status) status.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status"></span> Transcribing with Resilient AI Engine...';
                    if (btn) btn.disabled = true;

                    const fd = new FormData();
                    fd.append('audio', blob, 'voice.webm');

                    try {
                        const res = await fetch('/api/ai/transcribe-voice', { method: 'POST', body: fd });
                        const data = await res.json();
                        if (btn) btn.disabled = false;

                        if (data.success && data.text && data.text.trim().length > 0) {
                            const outEl = document.getElementById('fam-voice-output');
                            if (outEl) outEl.value = data.text.trim();
                            const providerBadge = data.provider ? ` (${data.provider})` : '';
                            if (status) status.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Transcription complete!${providerBadge}</span>`;
                        } else {
                            // If live speech captured text, preserve it gracefully
                            const outEl = document.getElementById('fam-voice-output');
                            const existingText = outEl ? outEl.value.trim() : '';
                            if (existingText.length > 0) {
                                if (status) status.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Spoken words captured via device speech recognition.</span>';
                            } else {
                                if (status) status.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-exclamation-circle-fill me-1"></i> ${data.error || 'No audible speech detected. Please speak clearly into your microphone.'}</span>`;
                            }
                        }
                    } catch (err) {
                        if (btn) btn.disabled = false;
                        const outEl = document.getElementById('fam-voice-output');
                        const existingText = outEl ? outEl.value.trim() : '';
                        if (existingText.length > 0) {
                            if (status) status.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Dictation saved from browser speech stream.</span>';
                        } else {
                            if (status) status.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-wifi-off me-1"></i> Upload error: ' + (err.message || 'Network disconnected') + '</span>';
                        }
                    }
                };

                famRecorder.start();
                isFamRecording = true;
                if (btn) {
                    btn.style.transform = 'scale(1.1)';
                    btn.style.boxShadow = '0 0 25px rgba(236, 72, 153, 0.6)';
                }
                if (icon) icon.className = 'bi bi-stop-fill';
                if (status) status.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-record-circle-fill me-1"></i> Recording... Click to Stop</span>';
            } catch (err) {
                alert('Microphone access denied: ' + err.message);
            }
        } else {
            // Stop live recognition if active
            if (famSpeechRecognition) {
                try { famSpeechRecognition.stop(); } catch (_) {}
                famSpeechRecognition = null;
            }

            if (famRecorder && famRecorder.state !== 'inactive') {
                famRecorder.stop();
            }
            isFamRecording = false;
            if (btn) {
                btn.style.transform = 'scale(1)';
                btn.style.boxShadow = '';
            }
            if (icon) icon.className = 'bi bi-mic-fill';
        }
    }

    function copyFamVoice() {
        const output = document.getElementById('fam-voice-output');
        const text = output ? output.value.trim() : '';
        const btnText = document.getElementById('btn-copy-fam-text');
        const btnIcon = document.getElementById('btn-copy-fam-icon');
        const btn = document.getElementById('btn-copy-fam-voice');

        if (!text) {
            alert('No dictated notes to copy yet. Speak into the microphone first!');
            if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
            return;
        }

        navigator.clipboard.writeText(text).then(() => {
            if (btnText) btnText.textContent = 'Copied!';
            if (btnIcon) btnIcon.className = 'bi bi-check2 me-1';
            setTimeout(() => {
                if (btnText) btnText.textContent = 'Copy to Clipboard';
                if (btnIcon) btnIcon.className = 'bi bi-clipboard me-1';
                if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
            }, 2000);
        }).catch(() => {
            alert('Notes copied to clipboard!');
            if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
        });
    }

    function clearFamVoice() {
        const output = document.getElementById('fam-voice-output');
        if (output) output.value = '';
        const status = document.getElementById('fam-voice-status');
        if (status) status.innerHTML = 'Click to Start Recording';
    }

    // 🎮 FAMILY GAME SHOW
    let triviaQuestions = [];
    let currentTriviaIndex = 0;
    let triviaScore = 0;

    function insertExampleTriviaFacts() {
        const examples = [
            "Grandpa moved to London in 1968",
            "Uncle Tunde burnt the Christmas turkey in 2012",
            "Auntie Simi claims she invented Nigerian fried rice",
            "Maya won the school spelling bee with the word 'onomatopoeia'"
        ];
        document.getElementById('trivia-facts').value = examples.join('\n');
    }

    async function generateTriviaShow() {
        const famName = document.getElementById('trivia-family-name').value.trim();
        const category = document.getElementById('trivia-category').value;
        const factsRaw = document.getElementById('trivia-facts').value.split('\n').filter(f => f.trim() !== '');

        const btn = document.getElementById('btn-launch-trivia');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Preparing Family Game Show...';

        const alertEl = document.getElementById('trivia-posted-alert');
        if (alertEl) {
            alertEl.style.display = 'none';
        }

        try {
            const res = await fetch('/api/ai/generate-trivia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    family_name: famName,
                    category: category,
                    family_facts: factsRaw,
                    question_count: 3
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Family Game Show';

            if (data.success && data.questions && data.questions.length > 0) {
                triviaQuestions = data.questions;
                currentTriviaIndex = 0;
                triviaScore = 0;
                document.getElementById('trivia-game-title').textContent = data.quiz_title || 'Family Game Show';
                document.getElementById('trivia-score').textContent = '0 pts';
                document.getElementById('trivia-empty-state').style.display = 'none';
                document.getElementById('trivia-game-arena').style.display = 'block';

                if (data.posted && data.post_url && alertEl) {
                    const linkEl = document.getElementById('trivia-post-feed-link');
                    if (linkEl) {
                        linkEl.href = data.post_url;
                    }
                    alertEl.style.display = 'flex';
                }

                renderTriviaQuestion();
            } else {
                alert(data.error || 'Failed to generate game show quiz.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Family Game Show';
            alert('Game Show error: ' + err.message);
        }
    }

    function renderTriviaQuestion() {
        const q = triviaQuestions[currentTriviaIndex];
        if (!q) return;

        document.getElementById('trivia-q-num').textContent = `Question ${currentTriviaIndex + 1} of ${triviaQuestions.length}`;
        document.getElementById('trivia-q-text').textContent = q.question;
        document.getElementById('trivia-feedback-box').style.display = 'none';
        document.getElementById('btn-next-trivia').style.display = 'none';

        const optBox = document.getElementById('trivia-options-box');
        optBox.innerHTML = '';

        q.options.forEach((optText, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-dark text-start p-3 rounded-3 fw-semibold trivia-opt-btn';
            btn.innerHTML = `<span class="badge bg-secondary me-2">${String.fromCharCode(65 + idx)}</span> ${optText}`;
            btn.onclick = () => selectTriviaAnswer(idx, q.correct_index, q.fun_fact);
            optBox.appendChild(btn);
        });
    }

    function selectTriviaAnswer(chosenIdx, correctIdx, funFact) {
        const buttons = document.querySelectorAll('.trivia-opt-btn');
        buttons.forEach((b, idx) => {
            b.disabled = true;
            if (idx === correctIdx) {
                b.className = 'btn btn-success text-start p-3 rounded-3 fw-bold';
            } else if (idx === chosenIdx) {
                b.className = 'btn btn-danger text-start p-3 rounded-3 fw-semibold';
            }
        });

        const feedback = document.getElementById('trivia-feedback-box');
        if (chosenIdx === correctIdx) {
            triviaScore += 100;
            document.getElementById('trivia-score').textContent = `${triviaScore} pts`;
            feedback.className = 'alert alert-success p-3 rounded-3 small fw-semibold mb-3';
            feedback.innerHTML = `🎉 <strong>Correct! (+100 pts)</strong> ${funFact}`;
        } else {
            feedback.className = 'alert alert-warning p-3 rounded-3 small fw-semibold mb-3';
            feedback.innerHTML = `❌ <strong>Not quite!</strong> ${funFact}`;
        }
        feedback.style.display = 'block';

        const nextBtn = document.getElementById('btn-next-trivia');
        if (currentTriviaIndex < triviaQuestions.length - 1) {
            nextBtn.innerHTML = 'Next Question <i class="bi bi-chevron-right ms-1"></i>';
            nextBtn.style.display = 'inline-block';
        } else {
            nextBtn.innerHTML = '🏁 Finish Game';
            nextBtn.style.display = 'inline-block';
        }
    }

    function nextTriviaQuestion() {
        if (currentTriviaIndex < triviaQuestions.length - 1) {
            currentTriviaIndex++;
            renderTriviaQuestion();
        } else {
            alert(`🏆 Game Finished! Your final family score is ${triviaScore} points!`);
            document.getElementById('trivia-game-arena').style.display = 'none';
            document.getElementById('trivia-empty-state').style.display = 'block';
        }
    }

    // =========================================================================
    // 🎭 TWO TRUTHS & A LIE (HERITAGE EDITION) CLIENT CONTROLLER
    // =========================================================================
    let selectedTwoTruthsOpt = null;
    let activeTwoTruthsGameId = @json($twoTruthsGame['id'] ?? null);

    function selectTwoTruthsOption(num) {
        @if(!empty($twoTruthsGame['has_voted']) || ($twoTruthsGame['status'] ?? '') === 'revealed')
            return;
        @endif

        selectedTwoTruthsOpt = num;
        const cards = document.querySelectorAll('.tt-statement-card');
        cards.forEach((card) => {
            const opt = parseInt(card.getAttribute('data-opt'), 10);
            const radio = card.querySelector('.tt-radio-select');
            if (opt === num) {
                card.classList.remove('border-light-subtle', 'bg-white');
                card.classList.add('border-danger', 'bg-danger-subtle', 'bg-opacity-10');
                if (radio) radio.checked = true;
            } else {
                card.classList.remove('border-danger', 'bg-danger-subtle', 'bg-opacity-10');
                card.classList.add('border-light-subtle', 'bg-white');
                if (radio) radio.checked = false;
            }
        });

        const btn = document.getElementById('btn-cast-tt-vote');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Spot Statement #${num} as the Lie`;
        }
    }

    async function castTwoTruthsVote(btn) {
        if (!selectedTwoTruthsOpt || !activeTwoTruthsGameId) {
            alert('Please select the statement card you believe is the lie.');
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Casting Guess...';
        }

        const formEl = document.getElementById('formCreateTwoTruths');
        const csrfToken = formEl?.querySelector('input[name="token"], input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('meta[name="csrf-token"]')?.content
            || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    game_id: activeTwoTruthsGameId,
                    guess: selectedTwoTruthsOpt,
                    token: csrfToken,
                    _token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) {
                window.ScoreTenantSpinner.stop(btn);
            }

            if (data.status === 'success') {
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to register vote.');
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Spot Statement #${selectedTwoTruthsOpt} as the Lie`;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Network error while casting vote: ' + err.message);
        }
    }

    async function revealTwoTruthsGame(btn, gameId) {
        if (!confirm('Are you sure you want to reveal the lie now? This will award points and archive verified truths to the Family Journal.')) {
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Unmasking Lie...';
        }

        const formEl = document.getElementById('formCreateTwoTruths');
        const csrfToken = formEl?.querySelector('input[name="token"], input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('meta[name="csrf-token"]')?.content
            || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/reveal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    game_id: gameId,
                    token: csrfToken,
                    _token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);

            if (data.status === 'success') {
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to reveal game.');
                btn.disabled = false;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Error unmasking lie: ' + err.message);
        }
    }

    async function submitNewTwoTruthsGame(btn) {
        const title = document.getElementById('tt-new-title').value.trim();
        const st1 = document.getElementById('tt-new-st1').value.trim();
        const st2 = document.getElementById('tt-new-st2').value.trim();
        const st3 = document.getElementById('tt-new-st3').value.trim();
        const story1 = document.getElementById('tt-new-story1').value.trim();
        const story2 = document.getElementById('tt-new-story2').value.trim();
        const story3 = document.getElementById('tt-new-story3').value.trim();

        const lieChecked = document.querySelector('input[name="tt_new_lie_index"]:checked');
        if (!lieChecked) {
            alert('Please select which of the three statements is the lie.');
            return;
        }
        const lieIndex = parseInt(lieChecked.value, 10);

        if (!st1 || !st2 || !st3) {
            alert('All three statements are required.');
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Publishing...';
        }

        const formEl = document.getElementById('formCreateTwoTruths');
        const csrfToken = formEl?.querySelector('input[name="token"], input[name="_token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('meta[name="csrf-token"]')?.content
            || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: title,
                    statement_1: st1,
                    statement_2: st2,
                    statement_3: st3,
                    lie_index: lieIndex,
                    reveal_story_1: story1,
                    reveal_story_2: story2,
                    reveal_story_3: story3,
                    token: csrfToken,
                    _token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);

            if (data.status === 'success') {
                const modalEl = document.getElementById('modalCreateTwoTruths');
                if (modalEl && window.bootstrap?.Modal) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                }
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to publish challenge.');
                btn.disabled = false;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Error creating game: ' + err.message);
        }
    }

    function shareTwoTruthsToWhatsApp() {
        const title = @json($twoTruthsGame['title'] ?? 'Two Truths & a Lie');
        const author = @json($twoTruthsGame['author_name'] ?? 'Family Member');
        const st1 = @json($twoTruthsGame['statement_1'] ?? '');
        const st2 = @json($twoTruthsGame['statement_2'] ?? '');
        const st3 = @json($twoTruthsGame['statement_3'] ?? '');
        const gameUrl = window.location.origin + '/family/ai-hub?tab=trivia#two-truths';

        const text = `🎭 *Family Challenge: Two Truths & a Lie!*\n\n`
            + `${author} has laid down a challenge:\n`
            + `1️⃣ ${st1}\n`
            + `2️⃣ ${st2}\n`
            + `3️⃣ ${st3}\n\n`
            + `Two are true life moments, but one is an elaborate lie! Can you spot it?\n`
            + `👉 Vote here on FamilyPlatform: ${gameUrl}`;

        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }

    function triggerCelebrationConfetti() {
        const colors = ['#dc2626', '#2563eb', '#16a34a', '#eab308', '#9333ea', '#ec4899'];
        for (let i = 0; i < 35; i++) {
            const p = document.createElement('div');
            const size = Math.random() * 9 + 5;
            p.style.position = 'fixed';
            p.style.zIndex = '99999';
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            p.style.left = Math.random() * 100 + 'vw';
            p.style.top = '-10px';
            p.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            p.style.opacity = '0.9';
            p.style.pointerEvents = 'none';
            p.style.transform = `rotate(${Math.random() * 360}deg)`;
            p.style.transition = 'transform 3s cubic-bezier(.25,.46,.45,.94), top 3s cubic-bezier(.25,.46,.45,.94), opacity 3s ease';

            document.body.appendChild(p);

            setTimeout(() => {
                p.style.top = '105vh';
                p.style.transform = `rotate(${Math.random() * 720}deg) scale(0.6)`;
                p.style.opacity = '0';
            }, 50);

            setTimeout(() => {
                p.remove();
            }, 3200);
        }
    }

    let lastScoutedOutings = [];
    let lastScoutedCity = '';

    // 🗺️ LUXURY PRESET ACTIVATION
    function setOutingsPreset(city, interests) {
        const cityInput = document.getElementById('outings-city');
        const intInput = document.getElementById('outings-interests');
        if (cityInput) cityInput.value = city;
        if (intInput) intInput.value = interests;

        document.querySelectorAll('.outings-preset-chip').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-city') === city) {
                btn.classList.add('active');
                if (btn.scrollIntoView) {
                    btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            }
        });

        // On mobile viewports, smoothly glide down so user sees discovery results
        if (window.innerWidth < 992) {
            setTimeout(() => {
                const resultsArea = document.getElementById('family-outings-results');
                if (resultsArea) {
                    resultsArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 120);
        }

        const form = document.getElementById('family-outings-form');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    }

    // 🗺️ LIVE FAMILY WEEKEND OUTINGS SCOUT
    async function handleScoutFamilyOutings(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-scout-outings');
        const container = document.getElementById('family-outings-results');
        const pollBtn = document.getElementById('btn-post-outings-poll');
        const alertBox = document.getElementById('outings-posted-alert');
        if (alertBox) alertBox.classList.add('d-none');

        if (window.FamilyPlatformSpinner?.start) {
            window.FamilyPlatformSpinner.start(btn);
        } else if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Scouting Family Outings...';
        }

        try {
            const cityInput = document.getElementById('outings-city').value;
            const res = await fetch('/api/ai/weekend-outings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    city: cityInput,
                    region: cityInput,
                    interests: document.getElementById('outings-interests').value,
                    age_group: document.getElementById('outings-interests').value
                })
            });
            const data = await res.json();

            if (data.success && Array.isArray(data.outings) && data.outings.length > 0) {
                lastScoutedOutings = data.outings;
                lastScoutedCity = cityInput;

                const getCatBadge = (cat) => {
                    const c = (cat || '').toLowerCase();
                    if (c.includes('museum')) return 'background: rgba(124, 58, 237, 0.09); color: #6d28d9; border: 1px solid rgba(124, 58, 237, 0.25);';
                    if (c.includes('barbecue') || c.includes('picnic') || c.includes('park')) return 'background: rgba(16, 185, 129, 0.1); color: #065f46; border: 1px solid rgba(16, 185, 129, 0.25);';
                    if (c.includes('drive')) return 'background: rgba(245, 158, 11, 0.1); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25);';
                    if (c.includes('holiday') || c.includes('staycation')) return 'background: rgba(2, 132, 199, 0.1); color: #0369a1; border: 1px solid rgba(2, 132, 199, 0.25);';
                    return 'background: rgba(212, 175, 55, 0.12); color: #8c6d1f; border: 1px solid rgba(212, 175, 55, 0.3);';
                };

                const disclosureHtml = data.affiliate_disclosure 
                    ? `<div class="p-3 rounded-4 mt-3 d-flex align-items-center gap-2.5" style="background: linear-gradient(135deg, #fafaf9 0%, #f5f5f4 100%); border: 1px solid #e7e5e4; font-size: 0.74rem; color: #57534e;">
                         <i class="bi bi-shield-check text-success flex-shrink-0 fs-5"></i>
                         <div>
                             <strong>Commercial Transparency:</strong> ${data.affiliate_disclosure}
                         </div>
                       </div>`
                    : '';

                container.innerHTML = data.outings.map((o, idx) => `
                    <div class="outings-result-card d-flex flex-column gap-2 mb-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold" style="${getCatBadge(o.category)} font-size: 0.72rem;">
                                        <i class="bi bi-tag-fill me-1 opacity-75"></i>${o.category || 'Curated Outing'}
                                    </span>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold" style="background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.72rem;">
                                        <i class="bi bi-ticket-perforated-fill me-1 opacity-75"></i>${o.price_guide || 'Free Entry / Low Cost'}
                                    </span>
                                </div>
                                <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.05rem; letter-spacing: -0.01em;">${idx + 1}. ${o.title}</h5>
                                ${o.location ? `
                                    <div class="text-secondary small mb-2 d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                        <i class="bi bi-geo-alt-fill text-danger flex-shrink-0"></i>
                                        <span>${o.location}</span>
                                    </div>
                                ` : ''}
                            </div>
                            <span class="badge bg-light text-secondary border small px-2.5 py-1.5 d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;">
                                <i class="bi bi-patch-check-fill text-success"></i>
                                <span>${o.partner_name || 'Verified Attraction'}</span>
                            </span>
                        </div>
                        
                        <p class="text-secondary mb-3" style="font-size: 0.85rem; line-height: 1.55;">${o.snippet}</p>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 pt-2.5 border-top">
                            <span class="small text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.74rem;">
                                <i class="bi bi-stars text-warning"></i>
                                <span>Concierge Recommended</span>
                            </span>
                            <div class="d-flex align-items-center gap-2 w-100 w-sm-auto">
                                ${o.url ? `
                                    <a href="${o.url}" target="_blank" rel="noopener noreferrer sponsored" class="btn btn-sm rounded-pill px-3.5 py-2.5 outings-btn-book d-inline-flex align-items-center justify-content-center gap-1.5 w-100 w-sm-auto shadow-sm" style="font-size: 0.82rem; min-height: 48px;">
                                        ${o.affiliate_label || '🎟️ Reserve Tickets & Passes'}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.72rem;"></i>
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `).join('') + disclosureHtml;

                if (pollBtn) {
                    pollBtn.classList.remove('d-none');
                    pollBtn.classList.add('d-inline-flex');
                }

                // If on mobile, glide to results so user doesn't wonder if search completed
                if (window.innerWidth < 992) {
                    setTimeout(() => {
                        const target = document.getElementById('family-outings-results');
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 120);
                }
            } else {
                container.innerHTML = '<div class="alert alert-warning rounded-4 mb-0">No weekend outings matched that search. Try another city or interest.</div>';
                if (pollBtn) pollBtn.classList.add('d-none');
            }
        } catch (err) {
            alert('Outings search error: ' + err.message);
        } finally {
            if (window.FamilyPlatformSpinner?.stop) {
                window.FamilyPlatformSpinner.stop(btn);
            } else if (window.ScoreTenantSpinner?.stop) {
                window.ScoreTenantSpinner.stop(btn);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-compass"></i> Discover Curated Outings';
            }
        }
    }

    async function postOutingsPollToFeed() {
        if (!lastScoutedOutings || lastScoutedOutings.length < 2) {
            alert('Please scout at least 2 outings first before posting a vote.');
            return;
        }

        const pollBtn = document.getElementById('btn-post-outings-poll');
        const alertBox = document.getElementById('outings-posted-alert');
        const feedLink = document.getElementById('outings-feed-link');
        const origText = pollBtn.innerHTML;

        pollBtn.disabled = true;
        pollBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Publishing Poll...';

        try {
            const options = lastScoutedOutings.map(o => o.title || 'Outing');
            const res = await fetch('/api/ai/post-outings-poll', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    location: lastScoutedCity || 'Our Area',
                    options: options
                })
            });
            const data = await res.json();
            pollBtn.disabled = false;
            pollBtn.innerHTML = origText;

            if (data.success) {
                if (feedLink && data.post_url) {
                    feedLink.href = data.post_url;
                }
                if (alertBox) {
                    alertBox.classList.remove('d-none');
                    alertBox.classList.add('d-flex');
                }
                pollBtn.classList.add('d-none');
            } else {
                alert(data.error || 'Failed to post outings poll.');
            }
        } catch (err) {
            pollBtn.disabled = false;
            pollBtn.innerHTML = origText;
            alert('Post error: ' + err.message);
        }
    }

    // 🎯 DEEP-LINKING ACTIVATION (Focus Two Truths & Game Show Tab)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        const hash = window.location.hash;

        if (tabParam === 'trivia' || tabParam === 'fun-place' || tabParam === 'game-show' || hash === '#tab-trivia' || hash === '#two-truths') {
            const triviaTabBtn = document.getElementById('tab-trivia-btn');
            if (triviaTabBtn) {
                triviaTabBtn.click();
                const subtabBtn = document.getElementById('subtab-twotruths-btn');
                if (subtabBtn) subtabBtn.click();
                const arena = document.getElementById('twoTruthsArenaCard');
                if (arena) {
                    setTimeout(() => arena.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
                }
            }
        }
    });
</script>

<script>
    // 🎨 FLYER STUDIO — lazy-mount the shared Universal Poster Engine when the tab is first opened
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtn = document.getElementById('tab-flyer-btn');
        const frame = document.getElementById('flyerEngineFrame');
        const guideBtn = document.getElementById('btnFlyerEngineGuide');
        if (!tabBtn || !frame) return;
        const stage = frame.parentElement;

        // The engine ships its own branded top bar; inside the Hub our header replaces it.
        // Constructable stylesheets are used so the page CSP (style-src) is not involved.
        const EMBED_CSS = `
            .studio-topbar { display: none !important; }
            body { min-height: 0 !important; }
            .studio-workspace { padding-top: 16px !important; }
            .canvas-stage-card { top: 16px !important; }
        `;

        frame.addEventListener('load', function () {
            try {
                const win = frame.contentWindow;
                const doc = frame.contentDocument;
                if (doc && win && 'adoptedStyleSheets' in doc) {
                    const sheet = new win.CSSStyleSheet();
                    sheet.replaceSync(EMBED_CSS);
                    doc.adoptedStyleSheets = [...doc.adoptedStyleSheets, sheet];
                }
                const innerGuide = doc && doc.getElementById('btnOpenLivingGuide');
                if (guideBtn && innerGuide) {
                    guideBtn.disabled = false;
                    guideBtn.onclick = () => innerGuide.click();
                }
            } catch (e) {
                // Cross-origin or engine markup changed: fall back to the engine's own chrome
            }
            stage.classList.add('is-ready');
        });

        const mount = () => {
            if (!frame.getAttribute('src')) frame.setAttribute('src', frame.dataset.src);
        };
        tabBtn.addEventListener('shown.bs.tab', mount);
        if (tabBtn.classList.contains('active')) mount();
    });
</script>

<script>
    // ✨ HUB TOOL DOCK — sticky offset, shareable ?tool= link per tool, remembers last-used tool
    // (a query param, not #hash: navbar.js highlights any element whose id contains the hash)
    document.addEventListener('DOMContentLoaded', function () {
        const dock = document.getElementById('familyAiTabs');
        const hub = document.querySelector('.fhub');
        if (!dock || !hub) return;

        const STORAGE_KEY = 'fhub:lastTool';
        const tiles = Array.from(dock.querySelectorAll('[data-hub-key]'));
        const byKey = (key) => tiles.find((t) => t.dataset.hubKey === key);

        const syncStickyTop = () => {
            const nav = document.getElementById('mainNavbar');
            const pos = nav ? getComputedStyle(nav).position : '';
            const h = nav && (pos === 'sticky' || pos === 'fixed') ? nav.getBoundingClientRect().height : 0;
            hub.style.setProperty('--fhub-sticky-top', (h + 8) + 'px');
        };
        syncStickyTop();
        window.addEventListener('resize', syncStickyTop, { passive: true });

        // Compact the dock once it pins under the navbar
        const sentinel = hub.querySelector('.fhub-dock-sentinel');
        const dockWrap = hub.querySelector('.fhub-dock');
        if (sentinel && dockWrap && 'IntersectionObserver' in window) {
            const top = parseInt(getComputedStyle(hub).getPropertyValue('--fhub-sticky-top'), 10) || 0;
            new IntersectionObserver(([entry]) => {
                dockWrap.classList.toggle('is-stuck', !entry.isIntersecting && entry.boundingClientRect.top < top + 1);
            }, { rootMargin: `-${top + 1}px 0px 0px 0px`, threshold: 0 }).observe(sentinel);
        }

        // Keep the active chip visible on the mobile rail without moving the page
        const centreTile = (tile) => {
            if (dock.scrollWidth <= dock.clientWidth) return;
            const left = tile.offsetLeft - (dock.clientWidth - tile.offsetWidth) / 2;
            dock.scrollTo({ left: Math.max(0, left), behavior: 'smooth' });
        };

        tiles.forEach((tile) => {
            tile.addEventListener('shown.bs.tab', () => {
                const key = tile.dataset.hubKey;
                try { localStorage.setItem(STORAGE_KEY, key); } catch (_) {}
                const url = new URL(window.location.href);
                url.searchParams.set('tool', key);
                url.hash = '';
                history.replaceState(null, '', url);
                centreTile(tile);
            });
        });

        const show = (tile) => {
            if (!tile || tile.classList.contains('active')) return;
            if (window.bootstrap && bootstrap.Tab) bootstrap.Tab.getOrCreateInstance(tile).show();
            else tile.click();
        };

        // Explicit ?tab= / legacy hashes are handled by the deep-link block above
        const params = new URLSearchParams(window.location.search);
        const hash = window.location.hash.replace(/^#(tab-)?/, '');
        if (params.has('tab') || hash === 'two-truths') return;

        let initial = byKey(params.get('tool') || '') || byKey(hash);
        if (!initial && !hash && !params.has('tool')) {
            try { initial = byKey(localStorage.getItem(STORAGE_KEY) || ''); } catch (_) {}
        }
        show(initial);
    });
</script>

{{-- Modal: Create Two Truths & a Lie Challenge --}}
<div class="modal fade" id="modalCreateTwoTruths" tabindex="-1" aria-labelledby="modalCreateTwoTruthsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 mb-1 fw-bold small">
                        🎭 Family Heritage Ritual
                    </span>
                    <h4 class="modal-title fw-bold text-dark" id="modalCreateTwoTruthsLabel">Create a "Two Truths &amp; a Lie" Challenge</h4>
                    <p class="text-muted small mb-0">Share two authentic life memories or milestones, and craft one convincing lie. Can your family tell the difference?</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="min-height: 44px; min-width: 44px;"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="formCreateTwoTruths" onsubmit="event.preventDefault(); submitNewTwoTruthsGame(document.getElementById('btn-submit-two-truths'));">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Challenge Title</label>
                        <input type="text" id="tt-new-title" class="form-control rounded-3" placeholder="e.g. Uncle Tunde's 1980s London Adventures" value="Heritage Challenge: Can you spot the lie?" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 1</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_1" value="1" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_1" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st1" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 1..." required></textarea>
                                <input type="text" id="tt-new-story1" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 2</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_2" value="2" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_2" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st2" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 2..." required></textarea>
                                <input type="text" id="tt-new-story2" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 3</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_3" value="3" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_3" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st3" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 3..." required></textarea>
                                <input type="text" id="tt-new-story3" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-3 border mb-3 small text-muted">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> <strong>Secret Anti-Cheat Gate:</strong> Your designated lie remains strictly hidden server-side until the reveal. The two true statements will automatically be immortalised in your official Family Journal.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal" style="min-height: 44px;">Cancel</button>
                        <button type="submit" id="btn-submit-two-truths" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="min-height: 44px;">
                            <i class="bi bi-broadcast"></i> Publish Challenge to Family Network
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
