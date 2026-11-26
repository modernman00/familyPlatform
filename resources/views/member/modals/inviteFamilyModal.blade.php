<!-- Invite Family & Phone Contacts Modal (Artboards 15, 16, 17 — Desktop, iPad, Mobile) -->
<style>
#inviteFamilyModal .ifm-dialog {
    max-width: 780px;
}
#inviteFamilyModal .ifm-content {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e6dfd5;
    box-shadow: 0 24px 60px rgba(28, 20, 18, 0.22);
    overflow: hidden;
    padding: 0; /* main.css gives every .modal-content 20px side padding, which insets the sheet and clips the right edge */
    max-height: calc(100dvh - 2rem);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #1c1412;
}
#inviteFamilyModal .modal-body { min-width: 0; overflow-x: hidden; }
#inviteFamilyModal .ifm-header > div:first-child { min-width: 0; flex: 1 1 auto; }
#inviteFamilyModal .ifm-footer { gap: 12px; flex-wrap: nowrap; }
#inviteFamilyModal .ifm-footer-note { min-width: 0; line-height: 1.3; }
#inviteFamilyModal .ifm-tabs-wrap { padding: 12px 24px 8px; background: #faf6f0; border-bottom: 1px solid #efe8de; }
#inviteFamilyModal .ifm-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #efe8de;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    background: #ffffff;
}
#inviteFamilyModal .ifm-header-icon {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 50%;
    background: #fdf2f2;
    color: #a31621;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
#inviteFamilyModal .ifm-title {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.35rem;
    font-weight: 800;
    color: #1c1412;
    letter-spacing: -0.02em;
    margin: 0 0 2px;
}
#inviteFamilyModal .ifm-subtitle {
    font-size: 0.85rem;
    color: #786c65;
    margin: 0;
}
#inviteFamilyModal .ifm-close-btn {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 50%;
    border: none;
    background: #f6f1ea;
    color: #5c5049;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease;
}
#inviteFamilyModal .ifm-close-btn:hover {
    background: #ede5da;
}
#inviteFamilyModal .ifm-body-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
    gap: 24px;
    padding: 20px 24px 16px;
}
@media (max-width: 899.98px) {
    #inviteFamilyModal .ifm-dialog {
        max-width: 560px;
    }
    #inviteFamilyModal .ifm-body-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 18px;
    }
}
@media (max-width: 575.98px) {
    #inviteFamilyModal .ifm-dialog {
        margin: auto 0 0 0;
        max-width: 100%;
        min-height: 0;
    }
    #inviteFamilyModal .ifm-content {
        border-radius: 24px 24px 0 0;
        border-bottom: 0;
        max-height: 92dvh;
    }
    #inviteFamilyModal .ifm-content::before {
        content: '';
        display: block;
        width: 40px;
        height: 4px;
        border-radius: 4px;
        background: #d9cfc2;
        margin: 8px auto 0;
        flex-shrink: 0;
    }
    #inviteFamilyModal .ifm-header { padding: 12px 16px; gap: 10px; }
    #inviteFamilyModal .ifm-header-icon { display: none; }
    #inviteFamilyModal .ifm-title { font-size: 1.2rem; }
    #inviteFamilyModal .ifm-subtitle { font-size: 0.8rem; line-height: 1.4; }
    #inviteFamilyModal .ifm-tabs-wrap { padding: 10px 16px 8px; }
    #inviteFamilyModal .ifm-body-grid { padding: 16px 16px 12px; gap: 16px; }
    #inviteFamilyModal .ifm-friends-body { padding: 16px 16px 12px; }
    #inviteFamilyModal .ifm-lineage-grid { gap: 8px; }
    #inviteFamilyModal .ifm-lineage-card { padding: 10px 12px; align-items: center; }
    /* chips: a tidy 2-column grid instead of a ragged row that runs off-screen */
    #inviteFamilyModal #kinshipChipsContainer {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px !important;
    }
    #inviteFamilyModal .kinship-chip {
        white-space: nowrap;
        justify-content: flex-start;
        padding: 6px 12px;
        font-size: 0.8rem;
        min-width: 0;
        overflow: hidden;
    }
    #inviteFamilyModal .kinship-chip > span {
        overflow: hidden;
        text-overflow: ellipsis;
    }
    #inviteFamilyModal .ifm-cat-nav {
        gap: 8px;
    }
    #inviteFamilyModal .ifm-cat-pill {
        font-size: 0.76rem;
        padding: 6px 8px;
    }
    #inviteFamilyModal .ifm-preview-card { max-height: 120px; }
    #inviteFamilyModal .ifm-secondary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    #inviteFamilyModal .ifm-footer { padding: 10px 16px calc(10px + env(safe-area-inset-bottom)) !important; }
    #inviteFamilyModal .ifm-footer-note { font-size: 0.72rem !important; }
}
#inviteFamilyModal .ifm-section-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c7e75;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
#inviteFamilyModal .ifm-body-grid > * { min-width: 0; }
#inviteFamilyModal .ifm-lineage-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 10px;
}
@media (max-width: 479.98px) {
    #inviteFamilyModal .ifm-lineage-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}
#inviteFamilyModal .ifm-lineage-card {
    border: 1.5px solid #e6dfd5;
    border-radius: 14px;
    padding: 12px 14px;
    background: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    transition: all 0.15s ease;
    min-height: 44px;
}
#inviteFamilyModal .ifm-lineage-card:has(input:checked) {
    background: #fdf2f2;
    border-color: #a31621;
}
#inviteFamilyModal .ifm-lineage-card input[type="radio"] {
    accent-color: #a31621;
    margin-top: 3px;
}
#inviteFamilyModal .kinship-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
    min-width: 44px;
    max-width: 100%;
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid #e2d9cd;
    background: #ffffff;
    color: #3d322c;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    overflow: hidden;
    transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
#inviteFamilyModal .kinship-chip > span {
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 170px;
}
#inviteFamilyModal .kinship-chip:active {
    transform: scale(0.98);
}
#inviteFamilyModal .kinship-chip:focus-visible,
#inviteFamilyModal .ifm-cat-pill:focus-visible,
#inviteFamilyModal .ifm-preset-pill:focus-visible {
    outline: 2px solid #a31621;
    outline-offset: 2px;
}
#inviteFamilyModal .kinship-chip:hover:not(.active) {
    background: #faf6f0;
    border-color: #cbbba8;
    color: #1c1412;
}
#inviteFamilyModal .kinship-chip--custom:not(.active) {
    border: 1.5px dashed #b09a88;
    background: #fdfaf6;
    color: #7a121b;
}
#inviteFamilyModal .kinship-chip--custom:hover:not(.active) {
    background: #fdf2f2;
    border-color: #a31621;
    color: #7a121b;
}
#inviteFamilyModal .kinship-chip.active {
    background: #1c1412;
    border: 1.5px solid #1c1412;
    color: #ffffff;
}
#inviteFamilyModal .ifm-cat-nav {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin-bottom: 10px;
}
#inviteFamilyModal .ifm-cat-pill {
    min-height: 44px;
    min-width: 44px;
    padding: 7px 10px;
    border-radius: 12px;
    border: 1px solid #e2d9cd;
    background: #faf6f0;
    color: #3d322c;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    cursor: pointer;
    transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}
#inviteFamilyModal .ifm-cat-pill:hover:not(.active) {
    background: #f1eae0;
    border-color: #cbbba8;
    color: #1c1412;
}
#inviteFamilyModal .ifm-cat-pill:active,
#inviteFamilyModal .ifm-preset-pill:active {
    transform: scale(0.98);
}
#inviteFamilyModal .ifm-cat-pill.active {
    background: #a31621;
    border-color: #a31621;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(163, 22, 33, 0.18);
}
#inviteFamilyModal .ifm-selected-pill {
    background: #fdf2f2;
    color: #7a121b;
    border: 1px solid #f3d0d3;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: none;
    letter-spacing: 0;
    padding: 3px 10px;
    border-radius: 999px;
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#inviteFamilyModal .ifm-personalise-box {
    background: #faf6f0;
    border: 1px solid #e6dfd5;
    border-radius: 14px;
    padding: 12px;
    margin-top: 10px;
}
#inviteFamilyModal .ifm-personalise-title {
    font-size: 0.8rem;
    font-weight: 700;
    color: #1c1412;
}
#inviteFamilyModal .ifm-personalise-hint {
    font-size: 0.75rem;
    color: #5c5049;
}
#inviteFamilyModal .ifm-preset-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}
#inviteFamilyModal .ifm-preset-pill {
    min-height: 44px;
    min-width: 44px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #dcd1c2;
    background: #ffffff;
    color: #3d322c;
    font-size: 0.76rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.15s ease, background 0.15s ease, border-color 0.15s ease;
}
#inviteFamilyModal .ifm-preset-pill:hover {
    background: #fdf2f2;
    border-color: #a31621;
    color: #7a121b;
}
#inviteFamilyModal .ifm-preset-pill.active {
    background: #a31621;
    border-color: #a31621;
    color: #ffffff;
}
#inviteFamilyModal .ifm-save-chip-btn {
    min-height: 44px;
    min-width: 44px;
    padding: 8px 14px;
    border-radius: 10px;
    background: #1c1412;
    color: #ffffff;
    border: none;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
#inviteFamilyModal .ifm-save-chip-btn:hover {
    background: #332622;
    color: #ffffff;
}
#inviteFamilyModal .ifm-clear-personalise-btn {
    min-height: 44px;
    min-width: 44px;
    padding: 8px 12px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #dcd1c2;
    color: #3d322c;
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
#inviteFamilyModal .ifm-personalise-toggle-btn {
    min-height: 44px;
    padding: 6px 12px;
    border-radius: 10px;
    border: 1px dashed #cbbba8;
    background: #faf6f0;
    color: #3d322c;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
#inviteFamilyModal .ifm-preview-card {
    background: #faf6f0;
    border: 1px solid #e6dfd5;
    border-radius: 14px;
    padding: 14px 16px;
    font-size: 0.83rem;
    line-height: 1.55;
    color: #3d322c;
    max-height: 210px;
    overflow-y: auto;
}
#inviteFamilyModal .ifm-primary-cta {
    background: #a31621;
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    min-height: 50px;
    padding: 12px 20px;
    font-size: 0.96rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    width: 100%;
    box-shadow: 0 6px 18px rgba(163, 22, 33, 0.24);
    transition: background 0.15s ease, transform 0.15s ease;
}
#inviteFamilyModal .ifm-primary-cta:hover {
    background: #8a1019;
    color: #ffffff !important;
}
#inviteFamilyModal .ifm-secondary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    margin-top: 10px;
}
#inviteFamilyModal .ifm-sec-tile {
    border: 1px solid #e6dfd5;
    background: #ffffff;
    border-radius: 12px;
    min-height: 56px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #3d322c;
    font-size: 0.72rem;
    font-weight: 700;
    text-decoration: none;
    overflow: hidden;
    transition: background 0.15s ease, border-color 0.15s ease;
}
#inviteFamilyModal .ifm-sec-tile:hover {
    background: #faf6f0;
    border-color: #d5c8b8;
    color: #1c1412;
}
#inviteFamilyModal .ifm-sec-tile i {
    font-size: 1rem;
    color: #5c5049;
}
#inviteFamilyModal .ifm-lineage-highlight {
    color: #1c1412;
}
#inviteFamilyModal .ifm-tabs-track {
    background: #ffffff;
    border-color: #e6dfd5 !important;
}
#inviteFamilyModal .ifm-tabs-track .nav-link {
    font-style: normal !important;
    font-size: 0.85rem;
    color: #5c5049 !important;
    border: none !important;
    min-height: 44px;
}
#inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active) {
    color: #1c1412 !important;
    background: rgba(28, 20, 18, 0.04);
    border: none !important;
}
#inviteFamilyModal .ifm-tabs-track .nav-link.active {
    background: #a31621 !important;
    color: #ffffff !important;
    border: none !important;
}
#inviteFamilyModal .ifm-auto-badge {
    background: #f6f1ea;
    color: #786c65;
    font-size: 0.66rem;
    text-transform: none;
    letter-spacing: 0;
}
#inviteFamilyModal .ifm-lineage-title {
    font-size: 0.86rem;
    color: #1c1412;
}
#inviteFamilyModal .ifm-lineage-code {
    font-size: 0.75rem;
    color: #786c65;
}
#inviteFamilyModal .ifm-contact-box {
    background: #faf6f0;
    border: 1px solid #e6dfd5;
}
#inviteFamilyModal .ifm-contact-title {
    color: #1c1412;
}
#inviteFamilyModal .ifm-contact-btn {
    min-height: 44px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #d5c8b8;
    color: #1c1412;
}
#inviteFamilyModal .ifm-contact-row {
    background: #ffffff;
    border-color: #e2e8f0;
}
#inviteFamilyModal .ifm-contact-avatar {
    width: 38px;
    height: 38px;
    background: #f5dede;
    color: #a31621;
    font-weight: 700;
    font-size: 0.9rem;
}
#inviteFamilyModal .ifm-sms-summary {
    color: #786c65;
    cursor: pointer;
    list-style: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
    font-style: normal;
}
#inviteFamilyModal .ifm-sms-send-btn {
    min-height: 44px;
    border-radius: 10px;
    background: #1c1412;
    color: #ffffff;
    border: none;
}
#inviteFamilyModal .ifm-copy-text-btn {
    font-size: 0.74rem;
    color: var(--brand-primary, #a31621);
    text-transform: none;
    letter-spacing: 0;
}
#inviteFamilyModal .ifm-divider-hr {
    border-color: #e2e8f0;
}
#inviteFamilyModal .ifm-footer {
    background: #faf6f0;
    border-top: 1px solid #efe8de !important;
}
#inviteFamilyModal .ifm-footer-note {
    color: #786c65;
    font-size: 0.76rem;
    font-style: normal;
}
#inviteFamilyModal .ifm-footer-lock {
    color: #a31621;
}
#inviteFamilyModal .ifm-done-btn {
    min-height: 44px;
    background: #ffffff;
    border: 1px solid #dfd7cc;
    color: #1c1412;
}
#inviteFamilyModal .ifm-done-btn:hover {
    background: #f6f1ea;
    color: #1c1412;
}

/* 🌙 Invite Family Modal — Complete 5-Selector Luxury Dark Theme */
body.dark-mode #inviteFamilyModal .ifm-content,
body.dark-theme #inviteFamilyModal .ifm-content,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-content,
html.dark #inviteFamilyModal .ifm-content,
html.dark-mode #inviteFamilyModal .ifm-content {
    background: #141418 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.72) !important;
    color-scheme: dark;
}
body.dark-mode #inviteFamilyModal .ifm-content::before,
body.dark-theme #inviteFamilyModal .ifm-content::before,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-content::before,
html.dark #inviteFamilyModal .ifm-content::before,
html.dark-mode #inviteFamilyModal .ifm-content::before {
    background: rgba(255, 255, 255, 0.24) !important;
}
body.dark-mode #inviteFamilyModal .ifm-header,
body.dark-theme #inviteFamilyModal .ifm-header,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-header,
html.dark #inviteFamilyModal .ifm-header,
html.dark-mode #inviteFamilyModal .ifm-header {
    background: #18181f !important;
    border-bottom-color: rgba(255, 255, 255, 0.09) !important;
}
body.dark-mode #inviteFamilyModal .ifm-header-icon,
body.dark-theme #inviteFamilyModal .ifm-header-icon,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-header-icon,
html.dark #inviteFamilyModal .ifm-header-icon,
html.dark-mode #inviteFamilyModal .ifm-header-icon {
    background: rgba(255, 77, 97, 0.16) !important;
    color: #ff8fa3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-title,
body.dark-theme #inviteFamilyModal .ifm-title,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-title,
html.dark #inviteFamilyModal .ifm-title,
html.dark-mode #inviteFamilyModal .ifm-title {
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-subtitle,
body.dark-theme #inviteFamilyModal .ifm-subtitle,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-subtitle,
html.dark #inviteFamilyModal .ifm-subtitle,
html.dark-mode #inviteFamilyModal .ifm-subtitle {
    color: #cbd5e1 !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-highlight,
body.dark-theme #inviteFamilyModal .ifm-lineage-highlight,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-highlight,
html.dark #inviteFamilyModal .ifm-lineage-highlight,
html.dark-mode #inviteFamilyModal .ifm-lineage-highlight {
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal #modalFamilyCodeBadge,
body.dark-theme #inviteFamilyModal #modalFamilyCodeBadge,
[data-bs-theme="dark"] #inviteFamilyModal #modalFamilyCodeBadge,
html.dark #inviteFamilyModal #modalFamilyCodeBadge,
html.dark-mode #inviteFamilyModal #modalFamilyCodeBadge {
    color: #fecdd3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-close-btn,
body.dark-theme #inviteFamilyModal .ifm-close-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-close-btn,
html.dark #inviteFamilyModal .ifm-close-btn,
html.dark-mode #inviteFamilyModal .ifm-close-btn {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-close-btn:hover,
body.dark-theme #inviteFamilyModal .ifm-close-btn:hover,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-close-btn:hover,
html.dark #inviteFamilyModal .ifm-close-btn:hover,
html.dark-mode #inviteFamilyModal .ifm-close-btn:hover {
    background: rgba(255, 255, 255, 0.16) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-tabs-wrap,
body.dark-theme #inviteFamilyModal .ifm-tabs-wrap,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-tabs-wrap,
html.dark #inviteFamilyModal .ifm-tabs-wrap,
html.dark-mode #inviteFamilyModal .ifm-tabs-wrap {
    background: #18181f !important;
    border-bottom-color: rgba(255, 255, 255, 0.08) !important;
}
body.dark-mode #inviteFamilyModal .ifm-tabs-track,
body.dark-theme #inviteFamilyModal .ifm-tabs-track,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-tabs-track,
html.dark #inviteFamilyModal .ifm-tabs-track,
html.dark-mode #inviteFamilyModal .ifm-tabs-track {
    background: #0d0f14 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
}
body.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link,
body.dark-theme #inviteFamilyModal .ifm-tabs-track .nav-link,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-tabs-track .nav-link,
html.dark #inviteFamilyModal .ifm-tabs-track .nav-link,
html.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link {
    color: #cbd5e1 !important;
    font-style: normal !important;
}
body.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active),
body.dark-theme #inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active),
[data-bs-theme="dark"] #inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active),
html.dark #inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active),
html.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link:hover:not(.active) {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.06) !important;
}
body.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link.active,
body.dark-theme #inviteFamilyModal .ifm-tabs-track .nav-link.active,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-tabs-track .nav-link.active,
html.dark #inviteFamilyModal .ifm-tabs-track .nav-link.active,
html.dark-mode #inviteFamilyModal .ifm-tabs-track .nav-link.active {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-section-label,
body.dark-theme #inviteFamilyModal .ifm-section-label,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-section-label,
html.dark #inviteFamilyModal .ifm-section-label,
html.dark-mode #inviteFamilyModal .ifm-section-label {
    color: #94a3b8 !important;
}
body.dark-mode #inviteFamilyModal .ifm-auto-badge,
body.dark-theme #inviteFamilyModal .ifm-auto-badge,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-auto-badge,
html.dark #inviteFamilyModal .ifm-auto-badge,
html.dark-mode #inviteFamilyModal .ifm-auto-badge {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-card,
body.dark-theme #inviteFamilyModal .ifm-lineage-card,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-card,
html.dark #inviteFamilyModal .ifm-lineage-card,
html.dark-mode #inviteFamilyModal .ifm-lineage-card {
    background: #1a1d26 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-card:has(input:checked),
body.dark-theme #inviteFamilyModal .ifm-lineage-card:has(input:checked),
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-card:has(input:checked),
html.dark #inviteFamilyModal .ifm-lineage-card:has(input:checked),
html.dark-mode #inviteFamilyModal .ifm-lineage-card:has(input:checked) {
    background: rgba(255, 77, 97, 0.14) !important;
    border-color: #ff4d61 !important;
    box-shadow: 0 0 0 1px rgba(255, 77, 97, 0.25) !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-card input[type="radio"],
body.dark-theme #inviteFamilyModal .ifm-lineage-card input[type="radio"],
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-card input[type="radio"],
html.dark #inviteFamilyModal .ifm-lineage-card input[type="radio"],
html.dark-mode #inviteFamilyModal .ifm-lineage-card input[type="radio"] {
    accent-color: #ff4d61;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-title,
body.dark-theme #inviteFamilyModal .ifm-lineage-title,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-title,
html.dark #inviteFamilyModal .ifm-lineage-title,
html.dark-mode #inviteFamilyModal .ifm-lineage-title {
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-code,
body.dark-theme #inviteFamilyModal .ifm-lineage-code,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-code,
html.dark #inviteFamilyModal .ifm-lineage-code,
html.dark-mode #inviteFamilyModal .ifm-lineage-code {
    color: #94a3b8 !important;
}
body.dark-mode #inviteFamilyModal .ifm-lineage-card:has(input:checked) .ifm-lineage-code,
body.dark-theme #inviteFamilyModal .ifm-lineage-card:has(input:checked) .ifm-lineage-code,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-lineage-card:has(input:checked) .ifm-lineage-code,
html.dark #inviteFamilyModal .ifm-lineage-card:has(input:checked) .ifm-lineage-code,
html.dark-mode #inviteFamilyModal .ifm-lineage-card:has(input:checked) .ifm-lineage-code {
    color: #fecdd3 !important;
}
body.dark-mode #inviteFamilyModal .kinship-chip:not(.active),
body.dark-theme #inviteFamilyModal .kinship-chip:not(.active),
[data-bs-theme="dark"] #inviteFamilyModal .kinship-chip:not(.active),
html.dark #inviteFamilyModal .kinship-chip:not(.active),
html.dark-mode #inviteFamilyModal .kinship-chip:not(.active) {
    background: #1a1d26 !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .kinship-chip:not(.active):hover,
body.dark-theme #inviteFamilyModal .kinship-chip:not(.active):hover,
[data-bs-theme="dark"] #inviteFamilyModal .kinship-chip:not(.active):hover,
html.dark #inviteFamilyModal .kinship-chip:not(.active):hover,
html.dark-mode #inviteFamilyModal .kinship-chip:not(.active):hover {
    background: #242938 !important;
    border-color: rgba(255, 255, 255, 0.26) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .kinship-chip.active,
body.dark-theme #inviteFamilyModal .kinship-chip.active,
[data-bs-theme="dark"] #inviteFamilyModal .kinship-chip.active,
html.dark #inviteFamilyModal .kinship-chip.active,
html.dark-mode #inviteFamilyModal .kinship-chip.active {
    background: rgba(217, 27, 56, 0.28) !important;
    border: 1.5px solid #ff4d61 !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 1px rgba(255, 77, 97, 0.28) !important;
}
body.dark-mode #inviteFamilyModal .kinship-chip--custom:not(.active),
body.dark-theme #inviteFamilyModal .kinship-chip--custom:not(.active),
[data-bs-theme="dark"] #inviteFamilyModal .kinship-chip--custom:not(.active),
html.dark #inviteFamilyModal .kinship-chip--custom:not(.active),
html.dark-mode #inviteFamilyModal .kinship-chip--custom:not(.active) {
    background: rgba(255, 77, 97, 0.08) !important;
    border: 1.5px dashed rgba(255, 143, 163, 0.5) !important;
    color: #fecdd3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-cat-pill:not(.active),
body.dark-theme #inviteFamilyModal .ifm-cat-pill:not(.active),
[data-bs-theme="dark"] #inviteFamilyModal .ifm-cat-pill:not(.active),
html.dark #inviteFamilyModal .ifm-cat-pill:not(.active),
html.dark-mode #inviteFamilyModal .ifm-cat-pill:not(.active) {
    background: #1a1d26 !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-cat-pill:not(.active):hover,
body.dark-theme #inviteFamilyModal .ifm-cat-pill:not(.active):hover,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-cat-pill:not(.active):hover,
html.dark #inviteFamilyModal .ifm-cat-pill:not(.active):hover,
html.dark-mode #inviteFamilyModal .ifm-cat-pill:not(.active):hover {
    background: #242938 !important;
    border-color: rgba(255, 255, 255, 0.26) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-cat-pill.active,
body.dark-theme #inviteFamilyModal .ifm-cat-pill.active,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-cat-pill.active,
html.dark #inviteFamilyModal .ifm-cat-pill.active,
html.dark-mode #inviteFamilyModal .ifm-cat-pill.active {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    border-color: #ff4d61 !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-selected-pill,
body.dark-theme #inviteFamilyModal .ifm-selected-pill,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-selected-pill,
html.dark #inviteFamilyModal .ifm-selected-pill,
html.dark-mode #inviteFamilyModal .ifm-selected-pill {
    background: rgba(255, 77, 97, 0.16) !important;
    border-color: rgba(255, 77, 97, 0.35) !important;
    color: #fecdd3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-personalise-box,
body.dark-theme #inviteFamilyModal .ifm-personalise-box,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-personalise-box,
html.dark #inviteFamilyModal .ifm-personalise-box,
html.dark-mode #inviteFamilyModal .ifm-personalise-box {
    background: #181b24 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-personalise-title,
body.dark-theme #inviteFamilyModal .ifm-personalise-title,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-personalise-title,
html.dark #inviteFamilyModal .ifm-personalise-title,
html.dark-mode #inviteFamilyModal .ifm-personalise-title {
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-personalise-hint,
body.dark-theme #inviteFamilyModal .ifm-personalise-hint,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-personalise-hint,
html.dark #inviteFamilyModal .ifm-personalise-hint,
html.dark-mode #inviteFamilyModal .ifm-personalise-hint {
    color: #cbd5e1 !important;
}
body.dark-mode #inviteFamilyModal .ifm-preset-pill:not(.active),
body.dark-theme #inviteFamilyModal .ifm-preset-pill:not(.active),
[data-bs-theme="dark"] #inviteFamilyModal .ifm-preset-pill:not(.active),
html.dark #inviteFamilyModal .ifm-preset-pill:not(.active),
html.dark-mode #inviteFamilyModal .ifm-preset-pill:not(.active) {
    background: #222736 !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-preset-pill.active,
body.dark-theme #inviteFamilyModal .ifm-preset-pill.active,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-preset-pill.active,
html.dark #inviteFamilyModal .ifm-preset-pill.active,
html.dark-mode #inviteFamilyModal .ifm-preset-pill.active {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    border-color: #ff4d61 !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-save-chip-btn,
body.dark-theme #inviteFamilyModal .ifm-save-chip-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-save-chip-btn,
html.dark #inviteFamilyModal .ifm-save-chip-btn,
html.dark-mode #inviteFamilyModal .ifm-save-chip-btn {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-clear-personalise-btn,
body.dark-mode #inviteFamilyModal .ifm-personalise-toggle-btn,
body.dark-theme #inviteFamilyModal .ifm-clear-personalise-btn,
body.dark-theme #inviteFamilyModal .ifm-personalise-toggle-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-clear-personalise-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-personalise-toggle-btn,
html.dark #inviteFamilyModal .ifm-clear-personalise-btn,
html.dark #inviteFamilyModal .ifm-personalise-toggle-btn,
html.dark-mode #inviteFamilyModal .ifm-clear-personalise-btn,
html.dark-mode #inviteFamilyModal .ifm-personalise-toggle-btn {
    background: #222736 !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-preview-card,
body.dark-theme #inviteFamilyModal .ifm-preview-card,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-preview-card,
html.dark #inviteFamilyModal .ifm-preview-card,
html.dark-mode #inviteFamilyModal .ifm-preview-card {
    background: #0e1118 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-primary-cta,
body.dark-theme #inviteFamilyModal .ifm-primary-cta,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-primary-cta,
html.dark #inviteFamilyModal .ifm-primary-cta,
html.dark-mode #inviteFamilyModal .ifm-primary-cta {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 6px 18px rgba(217, 27, 56, 0.32) !important;
}
body.dark-mode #inviteFamilyModal .ifm-primary-cta:hover,
body.dark-theme #inviteFamilyModal .ifm-primary-cta:hover,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-primary-cta:hover,
html.dark #inviteFamilyModal .ifm-primary-cta:hover,
html.dark-mode #inviteFamilyModal .ifm-primary-cta:hover {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-sec-tile,
body.dark-theme #inviteFamilyModal .ifm-sec-tile,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-sec-tile,
html.dark #inviteFamilyModal .ifm-sec-tile,
html.dark-mode #inviteFamilyModal .ifm-sec-tile {
    background: #1a1d26 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #e2e8f0 !important;
}
body.dark-mode #inviteFamilyModal .ifm-sec-tile:hover,
body.dark-theme #inviteFamilyModal .ifm-sec-tile:hover,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-sec-tile:hover,
html.dark #inviteFamilyModal .ifm-sec-tile:hover,
html.dark-mode #inviteFamilyModal .ifm-sec-tile:hover {
    background: #242938 !important;
    border-color: rgba(255, 255, 255, 0.24) !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-sec-tile i,
body.dark-theme #inviteFamilyModal .ifm-sec-tile i,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-sec-tile i,
html.dark #inviteFamilyModal .ifm-sec-tile i,
html.dark-mode #inviteFamilyModal .ifm-sec-tile i {
    color: #cbd5e1 !important;
}
body.dark-mode #inviteFamilyModal .ifm-contact-box,
body.dark-mode #inviteFamilyModal .ifm-contact-row,
body.dark-theme #inviteFamilyModal .ifm-contact-box,
body.dark-theme #inviteFamilyModal .ifm-contact-row,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-contact-box,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-contact-row,
html.dark #inviteFamilyModal .ifm-contact-box,
html.dark #inviteFamilyModal .ifm-contact-row,
html.dark-mode #inviteFamilyModal .ifm-contact-box,
html.dark-mode #inviteFamilyModal .ifm-contact-row {
    background: #1a1d26 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-contact-title,
body.dark-theme #inviteFamilyModal .ifm-contact-title,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-contact-title,
html.dark #inviteFamilyModal .ifm-contact-title,
html.dark-mode #inviteFamilyModal .ifm-contact-title {
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-contact-btn,
body.dark-theme #inviteFamilyModal .ifm-contact-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-contact-btn,
html.dark #inviteFamilyModal .ifm-contact-btn,
html.dark-mode #inviteFamilyModal .ifm-contact-btn {
    background: #232836 !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-contact-avatar,
body.dark-theme #inviteFamilyModal .ifm-contact-avatar,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-contact-avatar,
html.dark #inviteFamilyModal .ifm-contact-avatar,
html.dark-mode #inviteFamilyModal .ifm-contact-avatar {
    background: rgba(255, 77, 97, 0.18) !important;
    color: #ff8fa3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-sms-summary,
body.dark-theme #inviteFamilyModal .ifm-sms-summary,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-sms-summary,
html.dark #inviteFamilyModal .ifm-sms-summary,
html.dark-mode #inviteFamilyModal .ifm-sms-summary {
    color: #cbd5e1 !important;
}
body.dark-mode #inviteFamilyModal .ifm-sms-send-btn,
body.dark-theme #inviteFamilyModal .ifm-sms-send-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-sms-send-btn,
html.dark #inviteFamilyModal .ifm-sms-send-btn,
html.dark-mode #inviteFamilyModal .ifm-sms-send-btn {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    color: #ffffff !important;
    border: none !important;
}
body.dark-mode #inviteFamilyModal .ifm-divider-hr,
body.dark-theme #inviteFamilyModal .ifm-divider-hr,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-divider-hr,
html.dark #inviteFamilyModal .ifm-divider-hr,
html.dark-mode #inviteFamilyModal .ifm-divider-hr {
    border-color: rgba(255, 255, 255, 0.14) !important;
    opacity: 1;
}
body.dark-mode #inviteFamilyModal .ifm-footer,
body.dark-theme #inviteFamilyModal .ifm-footer,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-footer,
html.dark #inviteFamilyModal .ifm-footer,
html.dark-mode #inviteFamilyModal .ifm-footer {
    background: #11131a !important;
    border-top-color: rgba(255, 255, 255, 0.09) !important;
}
body.dark-mode #inviteFamilyModal .ifm-footer-note,
body.dark-theme #inviteFamilyModal .ifm-footer-note,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-footer-note,
html.dark #inviteFamilyModal .ifm-footer-note,
html.dark-mode #inviteFamilyModal .ifm-footer-note {
    color: #cbd5e1 !important;
    font-style: normal !important;
}
body.dark-mode #inviteFamilyModal .ifm-footer-lock,
body.dark-theme #inviteFamilyModal .ifm-footer-lock,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-footer-lock,
html.dark #inviteFamilyModal .ifm-footer-lock,
html.dark-mode #inviteFamilyModal .ifm-footer-lock {
    color: #ff8fa3 !important;
}
body.dark-mode #inviteFamilyModal .ifm-done-btn,
body.dark-theme #inviteFamilyModal .ifm-done-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-done-btn,
html.dark #inviteFamilyModal .ifm-done-btn,
html.dark-mode #inviteFamilyModal .ifm-done-btn {
    background: #232836 !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .ifm-done-btn:hover,
body.dark-theme #inviteFamilyModal .ifm-done-btn:hover,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-done-btn:hover,
html.dark #inviteFamilyModal .ifm-done-btn:hover,
html.dark-mode #inviteFamilyModal .ifm-done-btn:hover {
    background: #2d3345 !important;
    color: #ffffff !important;
}
body.dark-mode #inviteFamilyModal .ifm-copy-text-btn,
body.dark-theme #inviteFamilyModal .ifm-copy-text-btn,
[data-bs-theme="dark"] #inviteFamilyModal .ifm-copy-text-btn,
html.dark #inviteFamilyModal .ifm-copy-text-btn,
html.dark-mode #inviteFamilyModal .ifm-copy-text-btn {
    color: #ff8fa3 !important;
}
body.dark-mode #inviteFamilyModal .form-control,
body.dark-theme #inviteFamilyModal .form-control,
[data-bs-theme="dark"] #inviteFamilyModal .form-control,
html.dark #inviteFamilyModal .form-control,
html.dark-mode #inviteFamilyModal .form-control {
    background: #0e1118 !important;
    border-color: rgba(255, 255, 255, 0.16) !important;
    color: #f8fafc !important;
}
body.dark-mode #inviteFamilyModal .form-control::placeholder,
body.dark-theme #inviteFamilyModal .form-control::placeholder,
[data-bs-theme="dark"] #inviteFamilyModal .form-control::placeholder,
html.dark #inviteFamilyModal .form-control::placeholder,
html.dark-mode #inviteFamilyModal .form-control::placeholder {
    color: #94a3b8 !important;
}
body.dark-mode #inviteFamilyModal .btn-link.text-danger,
body.dark-theme #inviteFamilyModal .btn-link.text-danger,
[data-bs-theme="dark"] #inviteFamilyModal .btn-link.text-danger,
html.dark #inviteFamilyModal .btn-link.text-danger,
html.dark-mode #inviteFamilyModal .btn-link.text-danger {
    color: #ff8fa3 !important;
}
</style>

<div class="modal fade" id="inviteFamilyModal" tabindex="-1" aria-labelledby="inviteFamilyModalLabel" aria-hidden="true" x-data="{ isOpen: false }" x-init="$el.addEventListener('show.bs.modal', () => isOpen = true); $el.addEventListener('hidden.bs.modal', () => isOpen = false);">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ifm-dialog" x-trap="isOpen">
        <div class="modal-content ifm-content">
            @include('csrf')
            
            <!-- Modal Header -->
            <div class="ifm-header flex-shrink-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="ifm-header-icon flex-shrink-0">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div>
                        <h5 class="ifm-title" id="inviteFamilyModalLabel">
                            Invite others
                        </h5>
                        <p class="ifm-subtitle" id="modalFamilyContextSub">
                            Personalise a private link for your <span id="modalFamilyLineageTypeLabel" class="ifm-lineage-highlight fw-semibold">Marital / Household family</span> (<span class="font-monospace fw-bold" id="modalFamilyCodeBadge">#{{ $data['famCode'] ?? '...' }}</span>)
                        </p>
                        <p class="ifm-subtitle d-none" id="modalFriendsContextSub">Recommend friends &amp; in-laws to start their own private family network</p>
                        <span class="d-none" id="modalFamilyContextHeading">100% Private Family Network</span>
                    </div>
                </div>
                <button type="button" class="ifm-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Audience tabs: relatives join YOUR family; friends & in-laws start their OWN network -->
            <div class="ifm-tabs-wrap flex-shrink-0">
                <ul class="nav nav-pills nav-fill gap-2 p-1 rounded-pill border ifm-tabs-track" id="inviteModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-1" id="inviteTabFamily" data-bs-toggle="pill" data-bs-target="#inviteTabFamilyPane" type="button" role="tab" aria-controls="inviteTabFamilyPane" aria-selected="true">
                            <i class="bi bi-people"></i> <span>Our Family</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-1" id="inviteTabFriends" data-bs-toggle="pill" data-bs-target="#inviteTabFriendsPane" type="button" role="tab" aria-controls="inviteTabFriendsPane" aria-selected="false">
                            <i class="bi bi-diagram-3"></i> <span>Friends &amp; In-Laws</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="modal-body p-0 tab-content" style="overflow-y: auto;">
                <!-- TAB 1: Our Family (personalised kinship inviter) -->
                <div class="tab-pane fade show active" id="inviteTabFamilyPane" role="tabpanel" aria-labelledby="inviteTabFamily" tabindex="0">
                <div class="ifm-body-grid">
                    <!-- Left Column: Step 1 (Which family) + Step 2 (Who are you inviting?) -->
                    <div>
                        <!-- Choice 1: Which family are you inviting them to? -->
                        <div id="inviteModalLineagePickerWrap" class="mb-4">
                            <div class="ifm-section-label">
                                <span>Which family are you inviting them to?</span>
                                <span class="badge rounded-pill ifm-auto-badge" id="inviteModalAutoRouteBadge">
                                    Auto-adjusts by relative
                                </span>
                            </div>
                            <div class="ifm-lineage-grid" role="radiogroup" aria-label="Select Marital or Birth Family Code">
                                <label class="ifm-lineage-card">
                                    <input type="radio" name="invite_modal_lineage_scope" value="primary" id="inviteRadioPrimary" checked>
                                    <div>
                                        <div class="fw-bold ifm-lineage-title">Marital / Household</div>
                                        <div class="font-monospace ifm-lineage-code" id="inviteRadioPrimaryCode">#{{ $primaryFamCode ?? ($data['famCode'] ?? '') }}</div>
                                    </div>
                                </label>
                                <label class="ifm-lineage-card">
                                    <input type="radio" name="invite_modal_lineage_scope" value="maternal" id="inviteRadioMaternal">
                                    <div>
                                        <div class="fw-bold ifm-lineage-title">Birth / Maiden family</div>
                                        <div class="font-monospace ifm-lineage-code" id="inviteRadioMaternalCode">#{{ !empty($maternalFamCode) ? $maternalFamCode : ($primaryFamCode ?? ($data['famCode'] ?? '')) }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="text-muted mt-1.5 d-none" id="inviteModalLineageHint" style="font-size: 0.74rem;">
                                Selecting Mum, Dad, Sister, Brother, Uncle, Aunt, or Grandparent automatically switches to your Birth / Maiden family code.
                            </div>
                        </div>

                        <!-- Choice 2: Who are you inviting? (Categorised picker + Uncle, Aunt & Add your own personaliser) -->
                        <div class="mb-3" id="kinshipPickerSection" x-data="inviteKinshipPicker()" x-init="initPicker()">
                            <div class="ifm-section-label">
                                <span>Who are you inviting?</span>
                                <span class="ifm-selected-pill" id="kinshipActiveSummaryBadge" x-text="activeSummaryLabel()" title="Currently selected invite recipient">General family</span>
                            </div>

                            <!-- 1-Tap Category Navigation (All | Close | Extended | Custom) -->
                            <div class="ifm-cat-nav" id="kinshipCategoryNav" role="tablist" aria-label="Filter family relationships">
                                <button type="button" class="ifm-cat-pill active" :class="{ 'active': activeCategory === 'all' }" @click="setCategory('all')" role="tab" :aria-selected="activeCategory === 'all' ? 'true' : 'false'">
                                    <span>All</span>
                                </button>
                                <button type="button" class="ifm-cat-pill" :class="{ 'active': activeCategory === 'close' }" @click="setCategory('close')" role="tab" :aria-selected="activeCategory === 'close' ? 'true' : 'false'">
                                    <span>Close</span>
                                </button>
                                <button type="button" class="ifm-cat-pill" :class="{ 'active': activeCategory === 'extended' }" @click="setCategory('extended')" role="tab" :aria-selected="activeCategory === 'extended' ? 'true' : 'false'">
                                    <span>Extended</span>
                                </button>
                                <button type="button" class="ifm-cat-pill" :class="{ 'active': activeCategory === 'custom' }" @click="openCustomCreator()" role="tab" :aria-selected="activeCategory === 'custom' ? 'true' : 'false'">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                    <span>Custom</span>
                                </button>
                            </div>

                            <!-- Relationship Chips -->
                            <div class="d-flex flex-wrap gap-2" id="kinshipChipsContainer">
                                <button type="button" class="btn kinship-chip active" data-role="General" data-group="close" data-label="General family"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'General' && !isSavedCustomActive }" @click="selectPreset('General', 'General family')">
                                    <i class="bi bi-people" aria-hidden="true"></i> <span>General family</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Mum" data-group="close" data-label="Mum"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Mum' && !isSavedCustomActive }" @click="selectPreset('Mum', 'Mum')">
                                    <i class="bi bi-heart" aria-hidden="true"></i> <span>Mum</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Dad" data-group="close" data-label="Dad"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Dad' && !isSavedCustomActive }" @click="selectPreset('Dad', 'Dad')">
                                    <i class="bi bi-shield-check" aria-hidden="true"></i> <span>Dad</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Sister" data-group="close" data-label="Sister"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Sister' && !isSavedCustomActive }" @click="selectPreset('Sister', 'Sister')">
                                    <i class="bi bi-person-heart" aria-hidden="true"></i> <span>Sister</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Brother" data-group="close" data-label="Brother"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Brother' && !isSavedCustomActive }" @click="selectPreset('Brother', 'Brother')">
                                    <i class="bi bi-person-check" aria-hidden="true"></i> <span>Brother</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Partner" data-group="close" data-label="Spouse / Partner"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Partner' && !isSavedCustomActive }" @click="selectPreset('Partner', 'Spouse / Partner')">
                                    <i class="bi bi-hearts" aria-hidden="true"></i> <span>Spouse / Partner</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Child" data-group="close" data-label="Son / Daughter"
                                        x-show="matchesGroup('close')" :class="{ 'active': selectedRole === 'Child' && !isSavedCustomActive }" @click="selectPreset('Child', 'Son / Daughter')">
                                    <i class="bi bi-emoji-smile" aria-hidden="true"></i> <span>Son / Daughter</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Uncle" data-group="extended" data-label="Uncle"
                                        x-show="matchesGroup('extended')" :class="{ 'active': selectedRole === 'Uncle' && !isSavedCustomActive }" @click="selectPreset('Uncle', 'Uncle')">
                                    <i class="bi bi-person-badge" aria-hidden="true"></i> <span>Uncle</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Aunt" data-group="extended" data-label="Aunt"
                                        x-show="matchesGroup('extended')" :class="{ 'active': selectedRole === 'Aunt' && !isSavedCustomActive }" @click="selectPreset('Aunt', 'Aunt')">
                                    <i class="bi bi-flower1" aria-hidden="true"></i> <span>Aunt</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Grandparent" data-group="extended" data-label="Grandparent"
                                        x-show="matchesGroup('extended')" :class="{ 'active': selectedRole === 'Grandparent' && !isSavedCustomActive }" @click="selectPreset('Grandparent', 'Grandparent')">
                                    <i class="bi bi-award" aria-hidden="true"></i> <span>Grandparent</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Cousin" data-group="extended" data-label="Cousin"
                                        x-show="matchesGroup('extended')" :class="{ 'active': selectedRole === 'Cousin' && !isSavedCustomActive }" @click="selectPreset('Cousin', 'Cousin')">
                                    <i class="bi bi-diagram-3" aria-hidden="true"></i> <span>Cousin</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="NieceNephew" data-group="extended" data-label="Niece / Nephew"
                                        x-show="matchesGroup('extended')" :class="{ 'active': selectedRole === 'NieceNephew' && !isSavedCustomActive }" @click="selectPreset('NieceNephew', 'Niece / Nephew')">
                                    <i class="bi bi-stars" aria-hidden="true"></i> <span>Niece / Nephew</span>
                                </button>

                                <!-- User-saved custom relationship chips -->
                                <template x-for="savedTitle in savedCustomRoles" :key="savedTitle">
                                    <button type="button" class="btn kinship-chip" data-role="Custom" data-group="custom"
                                            x-show="matchesGroup('custom')"
                                            :class="{ 'active': isSavedCustomActive && customRoleLabel === savedTitle }"
                                            :title="savedTitle"
                                            @click="selectSavedCustom(savedTitle)">
                                        <i class="bi bi-bookmark-heart" aria-hidden="true"></i>
                                        <span x-text="savedTitle"></span>
                                    </button>
                                </template>

                                <!-- Add your own custom relationship trigger -->
                                <button type="button" class="btn kinship-chip kinship-chip--custom" id="btnKinshipAddCustom" data-role="Custom" data-group="custom" data-label="Add your own"
                                        x-show="matchesGroup('custom')"
                                        :class="{ 'active': selectedRole === 'Custom' && !isSavedCustomActive }"
                                        @click="openCustomCreator()">
                                    <i class="bi bi-plus-circle" aria-hidden="true"></i> <span>Add your own…</span>
                                </button>
                            </div>

                            <!-- Optional 1-Tap Personalise Trigger when on General family -->
                            <div class="mt-2" x-show="selectedRole === 'General' && !showGeneralPersonaliser && activeCategory !== 'custom'" x-cloak>
                                <button type="button" class="ifm-personalise-toggle-btn" id="btnOpenPersonaliseGeneral" @click="showGeneralPersonaliser = true; $nextTick(() => $refs.presetNameInput && $refs.presetNameInput.focus())">
                                    <i class="bi bi-magic" aria-hidden="true"></i>
                                    <span>Personalise greeting with a name or title</span>
                                </button>
                            </div>

                            <!-- Mode A: Custom "Add your own" Personaliser Card -->
                            <div class="ifm-personalise-box" id="customKinshipPanel" x-show="selectedRole === 'Custom' || activeCategory === 'custom'" x-collapse x-cloak>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                    <span class="ifm-personalise-title">Add your own relationship to personalise</span>
                                </div>
                                <p class="ifm-personalise-hint mb-2">Tap a quick suggestion or type how you call them in your family:</p>

                                <!-- Quick 1-Tap Inspiration Pills -->
                                <div class="ifm-preset-row" role="group" aria-label="Suggested custom relationships">
                                    <template x-for="suggestion in customSuggestions" :key="suggestion">
                                        <button type="button" class="ifm-preset-pill"
                                                :class="{ 'active': customRoleLabel.toLowerCase() === suggestion.toLowerCase() }"
                                                @click="applySuggestion(suggestion)">
                                            <span x-text="suggestion"></span>
                                        </button>
                                    </template>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <label for="customKinshipRoleInput" class="visually-hidden">Relationship or family title</label>
                                        <input type="text" id="customKinshipRoleInput" x-ref="customRoleInput" x-model="customRoleLabel" @input="emitChange()"
                                               maxlength="50" class="form-control form-control-sm"
                                               placeholder="Title (e.g. Auntie, Great-Uncle, Godmother)"
                                               style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="customKinshipNameInput" class="visually-hidden">Relative's name (optional)</label>
                                        <input type="text" id="customKinshipNameInput" x-model="recipientName" @input="emitChange()"
                                               maxlength="60" class="form-control form-control-sm"
                                               placeholder="Their name, e.g. Funke (optional)"
                                               style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
                                    <span class="ifm-personalise-hint" x-text="customHelperText()"></span>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="ifm-clear-personalise-btn"
                                                x-show="isSavedCustomActive && savedCustomRoles.includes(customRoleLabel.trim())"
                                                @click="removeSavedCustom(customRoleLabel.trim())">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            <span>Remove saved</span>
                                        </button>
                                        <button type="button" id="btnSaveCustomKinship" class="ifm-save-chip-btn"
                                                x-show="canSaveCustomChip()"
                                                @click="saveCustomChip($event)">
                                            <i class="bi bi-bookmark-plus" aria-hidden="true"></i>
                                            <span>Save to quick picks</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Mode B: Inline Name Personaliser for Selected Preset (e.g. Uncle -> Dear Uncle Tunde) -->
                            <div class="ifm-personalise-box" id="presetKinshipPersonaliseBox"
                                 x-show="selectedRole !== 'Custom' && activeCategory !== 'custom' && (selectedRole !== 'General' || showGeneralPersonaliser)"
                                 x-collapse x-cloak>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-1.5">
                                    <label for="presetKinshipNameInput" class="ifm-personalise-title mb-0" x-text="presetPersonaliseHeading()"></label>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" id="presetKinshipNameInput" x-ref="presetNameInput" x-model="recipientName" @input="emitChange()"
                                           maxlength="60" class="form-control form-control-sm"
                                           :placeholder="presetNamePlaceholder()"
                                           style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                    <button type="button" class="ifm-clear-personalise-btn"
                                            x-show="recipientName.trim().length > 0 || selectedRole === 'General'"
                                            @click="clearPresetName()"
                                            aria-label="Clear personalised name">
                                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        <span>Clear</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Native Phone Contacts Section (Shown on supported devices) -->
                        <div id="contactPickerSupportedSection" class="p-3 mb-3 rounded-3 d-none ifm-contact-box">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold small ifm-contact-title">Phone Address Book</span>
                                <small class="text-muted">Device-only</small>
                            </div>
                            <button type="button" id="btnNativeContactPicker" class="btn btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-2 ifm-contact-btn">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>Select Relatives from Phone Contacts</span>
                            </button>
                        </div>

                        <!-- Selected Contacts Tray -->
                        <div id="selectedContactsContainer" class="d-none mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 small">Selected Contacts (<span id="selectedContactsCount">0</span>)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" id="btnClearSelectedContacts" style="font-size: 0.78rem;">Clear All</button>
                            </div>
                            <div class="d-flex flex-column gap-2" id="selectedContactsList"></div>
                        </div>

                        <!-- Compact Direct SMS Accordion -->
                        <details class="mt-2">
                            <summary class="fw-semibold ifm-sms-summary" style="font-size:0.86rem;">
                                <i class="bi bi-telephone-plus"></i> <span>Or send directly to a phone number</span>
                            </summary>
                            <div class="row g-2 mt-1">
                                <div class="col-12 col-sm-5">
                                    <input type="text" id="manualRelativeName" class="form-control form-control-sm" placeholder="Name (e.g. Auntie Mary)" style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                </div>
                                <div class="col-12 col-sm-5">
                                    <input type="tel" id="manualRelativePhone" class="form-control form-control-sm" placeholder="Mobile (+44 7123 456789)" style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                </div>
                                <div class="col-12 col-sm-2 d-flex">
                                    <button type="button" id="btnSendManualSMS" class="btn w-100 d-flex align-items-center justify-content-center ifm-sms-send-btn" title="Send SMS" aria-label="Send SMS to entered number">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Right Column: Choice 3 (Message preview) + Primary CTA + 4 Secondary Tiles -->
                    <div class="d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <div class="ifm-section-label">
                                <span>Message preview</span>
                                <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold ifm-copy-text-btn" onclick="document.getElementById('btnCopyFullInvite')?.click()">
                                    Copy text
                                </button>
                            </div>
                            <div class="ifm-preview-card" style="white-space: pre-wrap;" id="inviteMessagePreview">Loading personalised invitation link...</div>
                        </div>

                        <div>
                            <!-- Primary Action: Share via WhatsApp -->
                            <a href="#" target="_blank" id="btnInviteWhatsApp" class="ifm-primary-cta">
                                <i class="bi bi-whatsapp fs-5"></i>
                                <span style="white-space: nowrap;">Share via WhatsApp</span>
                            </a>

                            <!-- Secondary 4-Tile Row: Copy Full Invite, SMS, Share sheet, Copy Link Only -->
                            <div class="ifm-secondary-grid">
                                <button type="button" id="btnCopyFullInvite" class="ifm-sec-tile">
                                    <i class="bi bi-clipboard-check"></i>
                                    <span id="copyFullInviteBtnText" style="white-space: nowrap;">Copy Full Invite</span>
                                </button>
                                <a href="#" id="btnInviteSMS" class="ifm-sec-tile">
                                    <i class="bi bi-chat-dots"></i>
                                    <span style="white-space: nowrap;">SMS</span>
                                </a>
                                <button type="button" id="btnInviteWebShare" class="ifm-sec-tile">
                                    <i class="bi bi-box-arrow-up"></i>
                                    <span style="white-space: nowrap;">Share sheet</span>
                                </button>
                                <button type="button" id="btnCopyInviteLink" class="ifm-sec-tile">
                                    <i class="bi bi-link-45deg"></i>
                                    <span id="copyLinkBtnText" style="white-space: nowrap;">Copy Link Only</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Toast Alert -->
                <div id="inviteFeedbackAlert" class="alert alert-success d-none py-2 px-3 mx-4 mb-3 text-center small rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="inviteFeedbackText">Link copied to clipboard!</span>
                </div>
                </div>

                <!-- TAB 2: Friends & In-Laws (starts their own independent network) -->
                <div class="tab-pane fade" id="inviteTabFriendsPane" role="tabpanel" aria-labelledby="inviteTabFriends" tabindex="0">
                    <div class="ifm-friends-body p-3 p-sm-4">
          
          <!-- USP Sanctuary Promise Box -->
          <div class="modal-sub-card-green p-3 mb-3">
            <div class="d-flex align-items-start gap-2">
              <span style="font-size: 1.25rem;">🛡️</span>
              <div>
                <strong class="d-block mb-1" style="font-size: 0.95rem;">The Walled Sanctuary Guarantee</strong>
                <p class="mb-0 text-muted" style="font-size: 0.825rem; line-height: 1.4;">
                  Your friends &amp; in-laws will create their <strong>own independent family network</strong> with their own private family code. Your family tree and posts remain 100% strictly private.
                </p>
              </div>
            </div>
          </div>

          <!-- 1-Tap WhatsApp Share Button -->
          <div class="mb-3">
            <a href="#" target="_blank" id="btnShareWhatsApp" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold" style="background: #15803d; color: #ffffff; border-radius: 12px; min-height: 44px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.28); text-decoration: none;">
              <i class="bi bi-whatsapp fs-5"></i> Share via WhatsApp
            </a>
          </div>

          <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1 my-0 ifm-divider-hr">
            <span class="px-3 text-muted fw-semibold" style="font-size: 0.8rem;">OR COPY LINK</span>
            <hr class="flex-grow-1 my-0 ifm-divider-hr">
          </div>

          <!-- Tactile Link Icon & Hidden URL Standard -->
          <div class="mb-3">
            <label class="form-label text-muted fw-semibold small mb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
              <span>Private Sanctuary Link</span>
              <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">URL Hidden • 1-Click Entry</span>
            </label>
            
            <div class="sanctuary-url-pill d-flex align-items-center justify-content-between gap-2 p-2 rounded-3"
                 id="sanctuarySharePillContainer">
                
                <!-- Clickable Action: Hides raw URL behind Link Icon + Title -->
                <a href="#" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   id="linkSanctuaryDirect"
                   class="d-flex align-items-center gap-2 text-decoration-none text-dark flex-grow-1 min-w-0 px-1 py-1"
                   style="min-width: 0; flex: 1 1 0%; min-height: 44px; touch-action: manipulation; outline: none;"
                   title="Enter Private Family Sanctuary">
                    
                    <div class="sanctuary-link-icon-badge flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                         style="width: 38px; height: 38px; font-size: 1.25rem;">
                        <span aria-hidden="true">🔗</span>
                    </div>

                    <div class="d-flex flex-column min-w-0 flex-grow-1" style="min-width: 0;">
                        <span class="fw-bold text-dark text-truncate" style="font-size: 0.9rem; letter-spacing: -0.01em; line-height: 1.25;" id="linkSanctuaryTitle">
                            Enter Private Family Sanctuary
                        </span>
                        <span class="text-muted small text-truncate" style="font-size: 0.75rem; font-weight: 500;">
                            myfamilyplatform.com • 100% Private
                        </span>
                    </div>

                    <i class="bi bi-arrow-up-right-circle text-primary flex-shrink-0 opacity-75" style="font-size: 1.05rem;" aria-hidden="true"></i>
                </a>

                <!-- 1-Tap Copy Button -->
                <button type="button" 
                        id="btnCopyShareLink"
                        class="btn btn-sm btn-outline-primary sanctuary-copy-btn d-inline-flex align-items-center justify-content-center gap-1 px-3 rounded-pill fw-semibold flex-shrink-0"
                        style="min-height: 44px; min-width: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;"
                        aria-label="Copy private sanctuary link">
                    <i class="bi bi-clipboard" id="iconCopyShareLink"></i>
                    <span id="textCopyShareLink">Copy</span>
                </button>
            </div>

            <!-- Hidden input preserved for script fallback & clipboard -->
            <input type="hidden" id="inputShareLink" value="">
            
            <div id="copySuccessFeedback" class="text-success small fw-semibold mt-1.5 d-none">
              <i class="bi bi-check-circle-fill me-1"></i> Private link copied to clipboard!
            </div>
          </div>

          <!-- Stats / Impact Ribbon -->
          <div class="modal-sub-card d-flex align-items-center justify-content-between p-2 px-3 mt-3">
            <span class="text-muted small">Family Hubs Spawned:</span>
            <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold" id="badgeSpawnedCount">0</span>
          </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer ifm-footer border-0 px-4 py-2 d-flex justify-content-between align-items-center flex-shrink-0">
                <span class="ifm-footer-note d-flex align-items-center gap-1">
                    <i class="bi bi-lock ifm-footer-lock"></i>
                    <span>Private link · Only people you invite can join</span>
                </span>
                <button type="button" class="btn btn-sm px-4 fw-bold rounded-pill flex-shrink-0 ifm-done-btn" data-bs-dismiss="modal">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    // Tab-aware header copy + optional deep-link: data-invite-tab="friends" on any trigger opens the Friends & In-Laws tab
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('inviteFamilyModal');
        if (!modalEl || !window.bootstrap) return;
        var familySub = document.getElementById('modalFamilyContextSub');
        var friendsSub = document.getElementById('modalFriendsContextSub');
        modalEl.addEventListener('show.bs.modal', function(e) {
            var want = e.relatedTarget && e.relatedTarget.getAttribute ? e.relatedTarget.getAttribute('data-invite-tab') : null;
            var btn = document.getElementById(want === 'friends' ? 'inviteTabFriends' : 'inviteTabFamily');
            if (btn) window.bootstrap.Tab.getOrCreateInstance(btn).show();
        });
        modalEl.addEventListener('shown.bs.tab', function(e) {
            var isFriends = e.target && e.target.id === 'inviteTabFriends';
            if (familySub) familySub.classList.toggle('d-none', isFriends);
            if (friendsSub) friendsSub.classList.toggle('d-none', !isFriends);
        });
    });
})();
</script>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
window.inviteKinshipPicker = function() {
    const STORAGE_KEY = 'fp_custom_kinship_roles_v1';
    return {
        activeCategory: 'all',
        selectedRole: 'General',
        selectedLabel: 'General family',
        customRoleLabel: '',
        recipientName: '',
        isSavedCustomActive: false,
        showGeneralPersonaliser: false,
        savedCustomRoles: [],
        customSuggestions: ['Auntie', 'Great-Uncle', 'Great-Aunt', 'Godparent', 'Grandad', 'Grandma', 'In-Law'],

        initPicker() {
            try {
                const raw = window.localStorage ? window.localStorage.getItem(STORAGE_KEY) : null;
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) {
                        this.savedCustomRoles = parsed
                            .map(v => String(v || '').trim().slice(0, 50))
                            .filter(Boolean)
                            .slice(0, 6);
                    }
                }
            } catch (e) {
                this.savedCustomRoles = [];
            }
        },

        matchesGroup(group) {
            if (this.activeCategory === 'all') return true;
            return this.activeCategory === group;
        },

        setCategory(cat) {
            this.activeCategory = cat;
        },

        selectPreset(role, label) {
            this.selectedRole = role;
            this.selectedLabel = label;
            this.isSavedCustomActive = false;
            if (this.activeCategory === 'custom') {
                this.activeCategory = 'all';
            }
            this.emitChange(true);
        },

        openCustomCreator() {
            this.activeCategory = 'custom';
            this.selectedRole = 'Custom';
            this.isSavedCustomActive = false;
            this.emitChange(false);
            this.$nextTick(() => {
                if (this.$refs.customRoleInput) {
                    this.$refs.customRoleInput.focus();
                }
            });
        },

        selectSavedCustom(title) {
            this.selectedRole = 'Custom';
            this.customRoleLabel = title;
            this.isSavedCustomActive = true;
            this.emitChange(true);
        },

        applySuggestion(suggestion) {
            this.selectedRole = 'Custom';
            this.customRoleLabel = suggestion;
            this.isSavedCustomActive = this.savedCustomRoles.includes(suggestion);
            this.emitChange(true);
            this.$nextTick(() => {
                if (this.$refs.customRoleInput) {
                    this.$refs.customRoleInput.focus();
                }
            });
        },

        canSaveCustomChip() {
            const clean = this.customRoleLabel.trim().replace(/\s+/g, ' ').slice(0, 50);
            if (clean.length < 2) return false;
            const exists = this.savedCustomRoles.some(item => item.toLowerCase() === clean.toLowerCase());
            return !exists && this.savedCustomRoles.length < 6;
        },

        saveCustomChip(evt) {
            const btn = evt && evt.currentTarget ? evt.currentTarget : null;
            if (btn && window.ScoreTenantSpinner && typeof window.ScoreTenantSpinner.start === 'function') {
                window.ScoreTenantSpinner.start(btn);
            }
            const clean = this.customRoleLabel.trim().replace(/\s+/g, ' ').slice(0, 50);
            if (clean.length >= 2 && !this.savedCustomRoles.some(item => item.toLowerCase() === clean.toLowerCase())) {
                this.savedCustomRoles = [...this.savedCustomRoles, clean].slice(0, 6);
                this.customRoleLabel = clean;
                this.isSavedCustomActive = true;
                this.persistSavedRoles();
                if (window.Alpine && typeof this.$toast === 'function') {
                    this.$toast('Saved "' + clean + '" to your quick picks', 'success');
                }
            }
            if (btn && window.ScoreTenantSpinner && typeof window.ScoreTenantSpinner.stop === 'function') {
                setTimeout(() => window.ScoreTenantSpinner.stop(btn), 180);
            }
            this.emitChange(false);
        },

        removeSavedCustom(title) {
            this.savedCustomRoles = this.savedCustomRoles.filter(item => item !== title);
            this.isSavedCustomActive = false;
            this.persistSavedRoles();
            this.emitChange(false);
        },

        persistSavedRoles() {
            try {
                if (window.localStorage) {
                    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(this.savedCustomRoles));
                }
            } catch (e) {}
        },

        clearPresetName() {
            this.recipientName = '';
            if (this.selectedRole === 'General') {
                this.showGeneralPersonaliser = false;
            }
            this.emitChange(false);
        },

        activeSummaryLabel() {
            const cleanName = this.recipientName.trim().replace(/\s+/g, ' ');
            if (this.selectedRole === 'Custom') {
                const cleanTitle = this.customRoleLabel.trim().replace(/\s+/g, ' ');
                if (!cleanTitle && !cleanName) return 'Custom invite';
                if (cleanTitle && cleanName) {
                    return cleanName.toLowerCase().startsWith(cleanTitle.toLowerCase())
                        ? cleanName
                        : cleanTitle + ' ' + cleanName;
                }
                return cleanTitle || cleanName;
            }
            if (cleanName) {
                if (this.selectedRole === 'General') return 'For ' + cleanName;
                const base = this.selectedLabel;
                return cleanName.toLowerCase().startsWith(base.toLowerCase()) ? cleanName : base + ' · ' + cleanName;
            }
            return this.selectedLabel || 'General family';
        },

        presetPersonaliseHeading() {
            if (this.selectedRole === 'General') {
                return 'Personalise greeting with their name (optional)';
            }
            return 'Personalise for ' + this.selectedLabel + ' (optional)';
        },

        presetNamePlaceholder() {
            const examples = {
                'Uncle': "Add name, e.g. Tunde → 'Dear Uncle Tunde'",
                'Aunt': "Add name, e.g. Mary → 'Dear Aunt Mary'",
                'Grandparent': "Add name or title, e.g. Grandma Bisi",
                'Cousin': "Add name, e.g. Dayo → 'Hey Cousin Dayo'",
                'NieceNephew': "Add name, e.g. Amara",
                'Mum': "Optional pet name, e.g. Mama",
                'Dad': "Optional title, e.g. Papa",
                'Sister': "Add name, e.g. Tola",
                'Brother': "Add name, e.g. Femi",
                'Partner': "Add name, e.g. Beloved / Chidi",
                'Child': "Add name, e.g. Ayo",
                'General': "Add recipient's name, e.g. Auntie Funke"
            };
            return examples[this.selectedRole] || 'Add their name (optional)';
        },

        customHelperText() {
            const cleanTitle = this.customRoleLabel.trim();
            const cleanName = this.recipientName.trim();
            if (!cleanTitle && !cleanName) {
                return 'Previewing general family invite until you type above.';
            }
            return 'Live message preview updated automatically.';
        },

        emitChange(allowAutoLineage = false) {
            const modalEl = document.getElementById('inviteFamilyModal');
            if (!modalEl) return;
            modalEl.dispatchEvent(new CustomEvent('kinship-selection-changed', {
                detail: {
                    role: this.selectedRole,
                    customLabel: this.customRoleLabel,
                    recipientName: this.recipientName,
                    allowAutoLineage: Boolean(allowAutoLineage)
                }
            }));
        }
    };
};
</script>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    let primaryInviteData = {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $primaryFamCode ?? ($data["famCode"] ?? "") }}',
        surname: '{{ $maritalSurname ?? ($data["familySurname"] ?? ($data["lastName"] ?? "Family")) }}',
        lineage_scope: 'primary'
    };
    let birthInviteData = @if(!empty($hasMaternalCode) && !empty($maternalFamCode)) {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $maternalFamCode }}',
        surname: '{{ $maidenSurname ?? $maternalFamCode }}',
        lineage_scope: 'maternal'
    } @else null @endif;

    let activeScope = '{{ ($activeLineageScope ?? "primary") === "maternal" ? "maternal" : "primary" }}';
    let inviteData = Object.assign({}, primaryInviteData);
    let currentRole = 'General';
    let currentCustomLabel = '';
    let currentRecipientName = '';
    let selectedContacts = [];

    function sanitiseInlineText(val, maxLen) {
        return String(val ?? '')
            .replace(/[\x00-\x1F\x7F]/g, '')
            .replace(/\s+/g, ' ')
            .trim()
            .slice(0, maxLen);
    }

    function composeSalutation(prefix, roleWord, name) {
        const cleanName = sanitiseInlineText(name, 60);
        if (!cleanName) {
            return `${prefix} ${roleWord},`;
        }
        const lowerName = cleanName.toLowerCase();
        const lowerRole = roleWord.toLowerCase();
        if (
            lowerName.startsWith(lowerRole) ||
            (lowerRole === 'aunt' && (lowerName.startsWith('auntie') || lowerName.startsWith('aunty'))) ||
            (lowerRole === 'uncle' && lowerName.startsWith('uncle')) ||
            (lowerRole === 'grandparent' && (lowerName.startsWith('grand') || lowerName.startsWith('mama') || lowerName.startsWith('baba') || lowerName.startsWith('nana') || lowerName.startsWith('papa')))
        ) {
            return `${prefix} ${cleanName},`;
        }
        return `${prefix} ${roleWord} ${cleanName},`;
    }

    function composeHeaderTitle(roleWord, name) {
        const cleanName = sanitiseInlineText(name, 60);
        if (!cleanName) return roleWord;
        const lowerName = cleanName.toLowerCase();
        const lowerRole = roleWord.toLowerCase();
        if (
            lowerName.startsWith(lowerRole) ||
            (lowerRole === 'aunt' && (lowerName.startsWith('auntie') || lowerName.startsWith('aunty'))) ||
            (lowerRole === 'grandparent' && (lowerName.startsWith('grand') || lowerName.startsWith('mama') || lowerName.startsWith('baba') || lowerName.startsWith('nana') || lowerName.startsWith('papa')))
        ) {
            return cleanName;
        }
        return `${roleWord} ${cleanName}`;
    }

    // Kinship template dictionary (British English copy, dynamically adapts for Marital vs Birth family code & personalisation)
    const kinshipTemplates = {
        'General': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const greetingLine = cleanName ? `Dear ${cleanName},\n\n` : '';
            return isBirth
                ? `🌳 *Family Heritage Invitation* 🌳\n\n` +
                  greetingLine +
                  `You are invited to join our *${surname} Birth Family Network & Heritage Tree* on FamilyPlatform.\n\n` +
                  `🔑 *Birth Family Code:* #${code}\n\n` +
                  `✨ Claim your branch on our live family tree, preserve our heritage, share memories, and connect across generations.\n\n` +
                  `🔗 Tap to Enter Our Family Sanctuary:\n${url}\n\n` +
                  `Welcome to our living family legacy! 🏰`
                : `🌳 *Family Heritage Invitation* 🌳\n\n` +
                  greetingLine +
                  `You are invited to join our *${surname} Family Network & Heritage Tree*.\n\n` +
                  `🔑 *Family Code:* #${code}\n\n` +
                  `✨ Claim your branch on our live family tree, preserve our oral histories, share memories, and connect across generations.\n\n` +
                  `🔗 Tap to Enter Our Family Sanctuary:\n${url}\n\n` +
                  `Welcome to our living family legacy! 🏰`;
        },
        'Mum': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hi ${cleanName},` : 'Hi Mum,';
            return `🌳 *Family Heritage Invitation for ${cleanName || 'Mum'}* 🌳\n\n` +
                `${salutation}\n\n` +
                `I've set up our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* sanctuary.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ View our ancestral lineage, see cherished photos, and claim your spot on our living family tree:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Dad': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hi ${cleanName},` : 'Hi Dad,';
            return `🌳 *Family Heritage Invitation for ${cleanName || 'Dad'}* 🌳\n\n` +
                `${salutation}\n\n` +
                `I've set up our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* sanctuary.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ View our ancestral lineage, see cherished photos, and claim your spot on our living family tree:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Sister': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hey ${cleanName},` : 'Hey sis,';
            return `🌳 *Family Heritage Invitation for ${composeHeaderTitle('Sister', cleanName)}* 🌳\n\n` +
                `${salutation}\n\n` +
                `Join our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage hub.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your branch on our living family tree, share memories, and preserve our stories:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Brother': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hey ${cleanName},` : 'Hey bro,';
            return `🌳 *Family Heritage Invitation for ${composeHeaderTitle('Brother', cleanName)}* 🌳\n\n` +
                `${salutation}\n\n` +
                `Join our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage hub.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your branch on our living family tree, share memories, and preserve our stories:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Partner': (surname, url, code, _isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hi ${cleanName},` : 'Hi love,';
            return `🌳 *Family Heritage Invitation${cleanName ? ' for ' + cleanName : ''}* 🌳\n\n` +
                `${salutation}\n\n` +
                `I've set up our private *${surname} Family* sanctuary so we can keep our photos, milestones, and family tree safe in one place.\n\n` +
                `🔑 *Family Code:* #${code}\n\n` +
                `✨ Join our household hub and claim your spot on our living family tree:\n\n` +
                `🔗 Tap to Join Our Family Sanctuary:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Child': (surname, url, code, _isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hey ${cleanName},` : 'Hey,';
            return `🌳 *Family Heritage Invitation${cleanName ? ' for ' + cleanName : ''}* 🌳\n\n` +
                `${salutation}\n\n` +
                `I've added you to our official *${surname} Family Tree*.\n\n` +
                `🔑 *Family Code:* #${code}\n\n` +
                `✨ Claim your profile, view our ancestral roots, and share memories:\n\n` +
                `🔗 Tap to Claim Your Profile:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Uncle': (surname, url, code, isBirth, name) => {
            const headerTarget = composeHeaderTitle('Uncle', name);
            const salutation = composeSalutation('Dear', 'Uncle', name);
            return `🌳 *Family Heritage Invitation for ${headerTarget}* 🌳\n\n` +
                `${salutation}\n\n` +
                `We are bringing our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage sanctuary together on FamilyPlatform.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your honoured branch on our living family tree, share cherished memories, and help preserve our family history across generations:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Aunt': (surname, url, code, isBirth, name) => {
            const headerTarget = composeHeaderTitle('Aunt', name);
            const salutation = composeSalutation('Dear', 'Aunt', name);
            return `🌳 *Family Heritage Invitation for ${headerTarget}* 🌳\n\n` +
                `${salutation}\n\n` +
                `We are bringing our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage sanctuary together on FamilyPlatform.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your honoured branch on our living family tree, share cherished memories, and help preserve our family history across generations:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Grandparent': (surname, url, code, isBirth, name) => {
            const headerTarget = composeHeaderTitle('Grandparent', name);
            const salutation = composeSalutation('Dear', 'Grandparent', name);
            return `🌳 *Family Heritage Invitation for ${headerTarget}* 🌳\n\n` +
                `${salutation}\n\n` +
                `Our *${surname} ${isBirth ? 'Birth Family' : 'Family'}* tree and heritage have been preserved.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ View our family branches, photos, oral history recordings, and ancestral records:\n\n` +
                `🔗 Tap to View Our Family Tree:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Cousin': (surname, url, code, isBirth, name) => {
            const headerTarget = composeHeaderTitle('Cousin', name);
            const salutation = composeSalutation('Hey', 'cousin', name);
            return `🌳 *Family Heritage Invitation for ${headerTarget}* 🌳\n\n` +
                `${salutation}\n\n` +
                `We are bringing the *${surname} ${isBirth ? 'Birth Family' : 'Family'}* together on our private family hub.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your branch on our live family tree and view our roots:\n\n` +
                `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'NieceNephew': (surname, url, code, isBirth, name) => {
            const cleanName = sanitiseInlineText(name, 60);
            const salutation = cleanName ? `Hey ${cleanName},` : 'Hey there,';
            return `🌳 *Family Heritage Invitation${cleanName ? ' for ' + cleanName : ''}* 🌳\n\n` +
                `${salutation}\n\n` +
                `We are connecting the *${surname} ${isBirth ? 'Birth Family' : 'Family'}* across generations on our private family sanctuary.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your spot on our living family tree, explore our roots, and share family moments:\n\n` +
                `🔗 Tap to Claim Your Spot:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        },
        'Custom': (surname, url, code, isBirth, name, customTitle) => {
            const cleanTitle = sanitiseInlineText(customTitle, 50);
            const cleanName = sanitiseInlineText(name, 60);
            if (!cleanTitle && !cleanName) {
                return kinshipTemplates['General'](surname, url, code, isBirth, '');
            }
            const combinedTarget = cleanTitle
                ? composeHeaderTitle(cleanTitle, cleanName)
                : cleanName;
            return `🌳 *Family Heritage Invitation for ${combinedTarget}* 🌳\n\n` +
                `Dear ${combinedTarget},\n\n` +
                `You are warmly invited to join our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* sanctuary on FamilyPlatform.\n\n` +
                `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
                `✨ Claim your branch on our live family tree, preserve our heritage, share cherished memories, and connect across generations:\n\n` +
                `🔗 Tap to Enter Our Family Sanctuary:\n${url}\n\n` +
                `Welcome to our living family legacy! 🏰`;
        }
    };

    function getCleanPhoneNumber(phone) {
        if (!phone) return '';
        return phone.replace(/[^0-9+]/g, '');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[ch]));
    }

    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function buildSMSUri(phone, body) {
        const clean = getCleanPhoneNumber(phone);
        const encodedBody = encodeURIComponent(body);
        const delimiter = isIOS() ? '&' : '?';
        return clean ? `sms:${clean}${delimiter}body=${encodedBody}` : `sms:${delimiter}body=${encodedBody}`;
    }

    function buildWhatsAppUri(phone, text) {
        const clean = getCleanPhoneNumber(phone).replace(/^\+/, '');
        const encodedText = encodeURIComponent(text);
        return clean ? `https://wa.me/${clean}?text=${encodedText}` : `https://api.whatsapp.com/send?text=${encodedText}`;
    }

    function setInviteLineageScope(scope) {
        const useBirth = (scope === 'maternal' && birthInviteData && birthInviteData.family_code);
        activeScope = useBirth ? 'maternal' : 'primary';
        inviteData = Object.assign({}, useBirth ? birthInviteData : primaryInviteData);

        const rPrimary = document.getElementById('inviteRadioPrimary');
        const rMaternal = document.getElementById('inviteRadioMaternal');
        if (rPrimary) rPrimary.checked = !useBirth;
        if (rMaternal) rMaternal.checked = useBirth;

        const codeBadge = document.getElementById('modalFamilyCodeBadge');
        if (codeBadge && inviteData.family_code) {
            codeBadge.textContent = inviteData.family_code;
        }

        const typeLabel = document.getElementById('modalFamilyLineageTypeLabel');
        if (typeLabel) {
            typeLabel.textContent = useBirth
                ? `Birth / Maiden family (${inviteData.surname || inviteData.family_code})`
                : `Marital / Household family (${inviteData.surname || inviteData.family_code})`;
        }

        const hintEl = document.getElementById('inviteModalLineageHint');
        if (hintEl) {
            hintEl.innerHTML = useBirth
                ? `Using your <strong>Birth / Maiden family code (#${escapeHtml(inviteData.family_code)})</strong> so your parents, siblings, and birth relatives join your maiden lineage.`
                : `Using your <strong>Marital / Household family code (#${escapeHtml(inviteData.family_code)})</strong> for your partner, children, and household relatives.`;
        }

        updateMessageUI();
        if (selectedContacts.length > 0) {
            renderSelectedContacts();
        }
    }

    function getPersonalisedMessage() {
        const url = inviteData.invite_url || window.location.origin + '/register';
        const surname = inviteData.surname || 'Family';
        const code = inviteData.family_code || '';
        const isBirth = (activeScope === 'maternal');
        const generator = kinshipTemplates[currentRole] || kinshipTemplates['General'];
        return generator(surname, url, code, isBirth, currentRecipientName, currentCustomLabel);
    }

    function updateMessageUI() {
        const msg = getPersonalisedMessage();
        const previewEl = document.getElementById('inviteMessagePreview');
        if (previewEl) {
            previewEl.textContent = msg;
        }

        // Update WhatsApp & SMS links
        const waBtn = document.getElementById('btnInviteWhatsApp');
        if (waBtn) {
            waBtn.href = buildWhatsAppUri('', msg);
        }

        const smsBtn = document.getElementById('btnInviteSMS');
        if (smsBtn) {
            smsBtn.href = buildSMSUri('', msg);
        }
    }

    async function recordInviteStep() {
        try {
            const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                           || document.querySelector('input[name="token"]')?.value || '';
            const formData = new FormData();
            formData.append('token', csrfToken);
            formData.append('step', 'invite_family');

            await fetch('/api/onboarding/step', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
        } catch (e) {
            console.warn('[InviteFamily] Step record error:', e);
        }
    }

    async function loadInviteData() {
        try {
            const res = await fetch('/api/onboarding/state', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const json = await res.json();
                if (json.status === 'success' && json.data?.invite_data) {
                    primaryInviteData = Object.assign(primaryInviteData, json.data.invite_data);
                    const pCodeEl = document.getElementById('inviteRadioPrimaryCode');
                    if (pCodeEl && primaryInviteData.family_code) {
                        pCodeEl.textContent = '(#' + primaryInviteData.family_code + ')';
                    }

                    if (json.data.birth_invite_data && json.data.birth_invite_data.family_code) {
                        birthInviteData = Object.assign(birthInviteData || {}, json.data.birth_invite_data);
                        const mCodeEl = document.getElementById('inviteRadioMaternalCode');
                        if (mCodeEl && birthInviteData.family_code) {
                            mCodeEl.textContent = '(#' + birthInviteData.family_code + ')';
                        }
                        const pickerWrap = document.getElementById('inviteModalLineagePickerWrap');
                        if (pickerWrap) {
                            pickerWrap.classList.remove('d-none');
                        }
                    }

                    setInviteLineageScope(activeScope);
                }
            }
        } catch (err) {
            console.warn('[InviteFamily] Failed loading invite data:', err);
            updateMessageUI();
        }
    }

    function renderSelectedContacts() {
        const container = document.getElementById('selectedContactsContainer');
        const list = document.getElementById('selectedContactsList');
        const countBadge = document.getElementById('selectedContactsCount');
        if (!container || !list) return;

        if (selectedContacts.length === 0) {
            container.classList.add('d-none');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('d-none');
        if (countBadge) countBadge.textContent = String(selectedContacts.length);

        list.innerHTML = '';
        selectedContacts.forEach((contact, idx) => {
            const name = (contact.name && contact.name[0]) ? contact.name[0] : 'Relative';
            const phone = (contact.tel && contact.tel[0]) ? contact.tel[0] : '';
            const clean = getCleanPhoneNumber(phone);
            const msg = getPersonalisedMessage();

            const row = document.createElement('div');
            row.className = 'p-2.5 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border ifm-contact-row';

            row.innerHTML = `
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 ifm-contact-avatar">
                        ${escapeHtml(name.charAt(0).toUpperCase())}
                    </div>
                    <div class="min-w-0">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;" title="${escapeHtml(name)}">${escapeHtml(name)}</div>
                        <div class="text-muted small font-monospace" style="font-size: 0.76rem;">${escapeHtml(phone || 'No phone')}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <a href="${buildWhatsAppUri(clean, msg)}" target="_blank" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; min-height: 44px;" onclick="window.recordInviteStep && window.recordInviteStep()">
                        <i class="bi bi-whatsapp"></i> <span style="white-space: nowrap;">WhatsApp</span>
                    </a>
                    <a href="${buildSMSUri(clean, msg)}" class="btn btn-sm btn-info text-white rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; min-height: 44px;" onclick="window.recordInviteStep && window.recordInviteStep()">
                        <i class="bi bi-chat-dots-fill"></i> <span style="white-space: nowrap;">SMS</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center remove-contact-btn" data-index="${idx}" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;" title="Remove" aria-label="Remove contact">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            list.appendChild(row);
        });

        // Wire remove buttons
        list.querySelectorAll('.remove-contact-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'), 10);
                if (!isNaN(index) && selectedContacts[index]) {
                    selectedContacts.splice(index, 1);
                    renderSelectedContacts();
                }
            });
        });
    }

    function showFeedback(text) {
        const alertEl = document.getElementById('inviteFeedbackAlert');
        const textEl = document.getElementById('inviteFeedbackText');
        if (!alertEl || !textEl) return;
        textEl.textContent = text;
        alertEl.classList.remove('d-none');
        setTimeout(() => alertEl.classList.add('d-none'), 3500);
    }

    // Initialize listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('inviteFamilyModal');
        if (!modalEl) return;

        // Expose recordInviteStep globally for row clicks
        window.recordInviteStep = recordInviteStep;

        modalEl.addEventListener('show.bs.modal', function() {
            loadInviteData();

            // Detect Contact Picker API availability
            const contactSection = document.getElementById('contactPickerSupportedSection');
            if (contactSection) {
                if ('contacts' in navigator && 'ContactsManager' in window) {
                    contactSection.classList.remove('d-none');
                } else {
                    contactSection.classList.add('d-none');
                }
            }
        });

        // Marital vs. Birth Family Code Radio Listeners
        document.querySelectorAll('input[name="invite_modal_lineage_scope"]').forEach(radio => {
            radio.addEventListener('change', function() {
                setInviteLineageScope(this.value);
            });
        });

        // Kinship Picker Reactive Event Bridge (Alpine inviteKinshipPicker + fallback click support)
        const birthRoles = ['Mum', 'Dad', 'Sister', 'Brother', 'Grandparent', 'Uncle', 'Aunt'];
        const maritalRoles = ['Child', 'Partner'];

        function applyKinshipSelection(role, customLabel, recipientName, allowAutoLineage) {
            currentRole = role || 'General';
            currentCustomLabel = customLabel || '';
            currentRecipientName = recipientName || '';

            if (allowAutoLineage && birthInviteData && birthInviteData.family_code) {
                const customLower = currentCustomLabel.trim().toLowerCase();
                const isBirthCustom = currentRole === 'Custom' && /^(great[-\s]?uncle|great[-\s]?aunt|auntie|aunty|grandad|grandma|grandpa|nana)/.test(customLower);
                if (birthRoles.includes(currentRole) || isBirthCustom) {
                    setInviteLineageScope('maternal');
                    return;
                } else if (maritalRoles.includes(currentRole)) {
                    setInviteLineageScope('primary');
                    return;
                }
            }

            updateMessageUI();
            if (selectedContacts.length > 0) {
                renderSelectedContacts();
            }
        }

        modalEl.addEventListener('kinship-selection-changed', function(e) {
            const d = (e && e.detail) ? e.detail : {};
            applyKinshipSelection(d.role, d.customLabel, d.recipientName, Boolean(d.allowAutoLineage));
        });

        // Native Contact Picker Trigger
        const btnContact = document.getElementById('btnNativeContactPicker');
        if (btnContact) {
            btnContact.addEventListener('click', async function() {
                if (!('contacts' in navigator && 'ContactsManager' in window)) {
                    showFeedback('Contact Picker not supported on this browser. Please use WhatsApp or SMS below.');
                    return;
                }

                try {
                    const props = ['name', 'tel'];
                    const opts = { multiple: true };
                    const contacts = await navigator.contacts.select(props, opts);
                    if (Array.isArray(contacts) && contacts.length > 0) {
                        selectedContacts = contacts;
                        renderSelectedContacts();
                        recordInviteStep();
                        showFeedback(`Loaded ${contacts.length} contact(s) from your phone!`);
                    }
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.warn('[InviteFamily] Contact picker cancelled or failed:', err);
                    }
                }
            });
        }

        // Clear Contacts
        const btnClear = document.getElementById('btnClearSelectedContacts');
        if (btnClear) {
            btnClear.addEventListener('click', function() {
                selectedContacts = [];
                renderSelectedContacts();
            });
        }

        // Web Share API
        const btnWebShare = document.getElementById('btnInviteWebShare');
        if (btnWebShare) {
            btnWebShare.addEventListener('click', async function() {
                const msg = getPersonalisedMessage();
                const url = inviteData.invite_url || window.location.origin + '/register';
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: `Join our ${inviteData.surname || 'Family'} Sanctuary`,
                            text: msg,
                            url: url
                        });
                        recordInviteStep();
                    } catch (e) {
                        // User cancelled
                    }
                } else {
                    // Fallback to WhatsApp
                    window.open(buildWhatsAppUri('', msg), '_blank', 'noopener,noreferrer');
                    recordInviteStep();
                }
            });
        }

        function fallbackCopy(text, feedbackMsg) {
            try {
                const tempArea = document.createElement('textarea');
                tempArea.value = text;
                tempArea.style.position = 'fixed';
                tempArea.style.left = '-9999px';
                tempArea.style.top = '0';
                document.body.appendChild(tempArea);
                tempArea.focus();
                tempArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(tempArea);
                if (successful) {
                    showFeedback(feedbackMsg);
                    recordInviteStep();
                    return;
                }
            } catch (e) {}
            prompt('Copy invitation to clipboard:', text);
            recordInviteStep();
        }

        // Copy Full Rich Invite (with Family Code, emojis, and link)
        const btnCopyFull = document.getElementById('btnCopyFullInvite');
        if (btnCopyFull) {
            btnCopyFull.addEventListener('click', function() {
                const msg = getPersonalisedMessage();
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(msg).then(() => {
                        const textSpan = document.getElementById('copyFullInviteBtnText');
                        if (textSpan) textSpan.textContent = 'Invite Copied! ✓';
                        showFeedback('Full rich invitation with Family Code copied to clipboard!');
                        recordInviteStep();
                        setTimeout(() => {
                            if (textSpan) textSpan.textContent = 'Copy Full Invite';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopy(msg, 'Full rich invitation with Family Code copied to clipboard!');
                    });
                } else {
                    fallbackCopy(msg, 'Full rich invitation with Family Code copied to clipboard!');
                }
            });
        }

        // Copy Link Only
        const btnCopy = document.getElementById('btnCopyInviteLink');
        if (btnCopy) {
            btnCopy.addEventListener('click', function() {
                const url = inviteData.invite_url || window.location.origin + '/register';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        const textSpan = document.getElementById('copyLinkBtnText');
                        if (textSpan) textSpan.textContent = 'Link Copied! ✓';
                        showFeedback('Family invite link copied to clipboard!');
                        recordInviteStep();
                        setTimeout(() => {
                            if (textSpan) textSpan.textContent = 'Copy Link Only';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopy(url, 'Family invite link copied to clipboard!');
                    });
                } else {
                    fallbackCopy(url, 'Family invite link copied to clipboard!');
                }
            });
        }

        // Direct Manual SMS / WhatsApp
        const btnManualSMS = document.getElementById('btnSendManualSMS');
        if (btnManualSMS) {
            btnManualSMS.addEventListener('click', function() {
                const nameInput = document.getElementById('manualRelativeName');
                const phoneInput = document.getElementById('manualRelativePhone');
                const name = nameInput ? nameInput.value.trim() : '';
                const phone = phoneInput ? phoneInput.value.trim() : '';

                if (!phone) {
                    (window.swalWarning || alert)('Please enter a mobile phone number to send an SMS.');
                    return;
                }

                const previousName = currentRecipientName;
                if (name && !currentRecipientName) {
                    currentRecipientName = name;
                }
                const msg = getPersonalisedMessage();
                currentRecipientName = previousName;
                window.location.href = buildSMSUri(phone, msg);
                recordInviteStep();
            });
        }

        // WhatsApp direct button tracking
        const btnWhatsApp = document.getElementById('btnInviteWhatsApp');
        if (btnWhatsApp) {
            btnWhatsApp.addEventListener('click', function() {
                recordInviteStep();
            });
        }

        const btnSMS = document.getElementById('btnInviteSMS');
        if (btnSMS) {
            btnSMS.addEventListener('click', function() {
                recordInviteStep();
            });
        }
    });
})();
</script>
