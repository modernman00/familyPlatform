@extends('layouts.profileBase')

@section('title', 'Kin Whispers & Artifact Messages — FamilyPlatform')
@section('meta_description', 'Private, thoughtful messages and voice dedications anchored to your family photographs, memories and lineage.')

@push('styles')
<style>
.kw-wrap {
    --kw-primary: #a31621;
    --kw-primary-dark: #7a0e16;
    --kw-gold: #c9a24b;
    --kw-gold-bright: #ffd700;
    --kw-canvas: #faf8f5;
    --kw-card: #ffffff;
    --kw-soft: #f8fafc;
    --kw-ink: #0f172a;
    --kw-muted: #475569;
    --kw-line: #e2e8f0;
    --kw-mine: #7a0e16;
    --kw-theirs: #f1f5f9;
    max-width: 1240px;
    margin: 0 auto;
    padding: 16px 16px 96px;
    color: var(--kw-ink);
}
.dark-mode .kw-wrap {
    --kw-canvas: #0b0f19;
    --kw-card: #141a29;
    --kw-soft: #0f1524;
    --kw-ink: #f8fafc;
    --kw-muted: #cbd5e1;
    --kw-line: #1e293b;
    --kw-theirs: #1e293b;
}
[x-cloak] { display: none !important; }

/* Hero */
.kw-hero {
    position: relative; overflow: hidden; border-radius: 24px; padding: 32px 24px; margin-bottom: 16px; color: #fff;
    background: radial-gradient(85% 120% at 85% 15%, rgba(201,162,75,.35), transparent 65%),
                radial-gradient(70% 90% at 5% 95%, rgba(163,22,33,.8), transparent 70%),
                linear-gradient(135deg, #120306 0%, #29060d 50%, #4a0914 100%);
    border: 1px solid rgba(255,215,0,.25);
    box-shadow: 0 20px 45px -15px rgba(163,22,33,.45);
    display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;
}
.kw-hero__eyebrow { font-size: .75rem; letter-spacing: .2em; text-transform: uppercase; color: var(--kw-gold-bright); font-weight: 600; margin-bottom: 8px; }
.kw-hero h1 { font-size: 1.75rem; font-weight: 800; letter-spacing: -.01em; margin: 0 0 8px; color: #fff; line-height: 1.2; }
.kw-hero p { margin: 0; max-width: 560px; color: #fef3c7; font-size: .875rem; line-height: 1.6; }

/* Buttons */
.kw-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    min-height: 44px; min-width: 44px; padding: 0 20px; border-radius: 12px; border: 1px solid transparent;
    font-size: .875rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; cursor: pointer;
    transition: transform .15s ease, filter .15s ease, background-color .15s ease;
}
.kw-btn:active { transform: scale(.98); }
.kw-btn:focus-visible { outline: 2px solid var(--kw-gold-bright); outline-offset: 2px; }
.kw-btn:disabled { opacity: .5; cursor: not-allowed; }
@media (hover: hover) { .kw-btn:not(:disabled):hover { filter: brightness(1.06); } }
.kw-btn--gold { background: var(--kw-gold-bright); color: #3b0a10; }
.kw-btn--primary { background: var(--kw-primary); color: #fff; }
.kw-btn--ghost { background: var(--kw-card); color: var(--kw-ink); border-color: var(--kw-line); }
.kw-btn--ghost.is-live { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.kw-btn--square { padding: 0; width: 44px; height: 44px; min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }

/* Safeguarding strip */
.kw-notice {
    display: flex; gap: 12px; align-items: flex-start; padding: 12px 16px; margin-bottom: 16px;
    border-radius: 16px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #064e3b; font-size: .8125rem; line-height: 1.55;
}
.dark-mode .kw-notice { background: #052e22; border-color: #065f46; color: #d1fae5; }
.kw-notice i { font-size: 1.125rem; margin-top: 1px; }

/* Workspace */
.kw-shell {
    display: grid; grid-template-columns: 340px minmax(0, 1fr); grid-template-rows: 100%;
    background: var(--kw-card); border: 1px solid var(--kw-line); border-radius: 24px; overflow: hidden;
    height: clamp(660px, 78vh, 880px); min-height: 620px; box-shadow: 0 10px 30px -18px rgba(15,23,42,.35);
}
.kw-side {
    border-right: 1px solid var(--kw-line); background: var(--kw-soft); display: flex; flex-direction: column;
    min-width: 0; min-height: 0; height: 100%; max-height: 100%; overflow: hidden;
}
.kw-search { padding: 16px; border-bottom: 1px solid var(--kw-line); position: relative; flex-shrink: 0; }
.kw-search i { position: absolute; left: 30px; top: 50%; transform: translateY(-50%); color: var(--kw-muted); pointer-events: none; }
.kw-input, .kw-select, .kw-textarea {
    width: 100%; background: var(--kw-card); color: var(--kw-ink); border: 1px solid var(--kw-line); border-radius: 12px;
    padding: 10px 14px; font-size: .875rem; line-height: 1.5; min-height: 44px;
}
.kw-search .kw-input { padding-left: 40px; }
.kw-input:focus, .kw-select:focus, .kw-textarea:focus { outline: 2px solid var(--kw-primary); outline-offset: 1px; border-color: var(--kw-primary); }
.kw-textarea { resize: none; }

.kw-threads { flex: 1 1 0%; overflow-y: auto; min-height: 0; }
.kw-thread {
    width: 100%; display: flex; gap: 12px; align-items: flex-start; text-align: left; padding: 16px;
    background: transparent; border: 0; border-bottom: 1px solid var(--kw-line); border-left: 4px solid transparent;
    color: var(--kw-ink); cursor: pointer; min-height: 72px;
}
.kw-thread.is-active { background: var(--kw-card); border-left-color: var(--kw-primary); }
@media (hover: hover) { .kw-thread:hover { background: var(--kw-card); } }
.kw-thread:focus-visible { outline: 2px solid var(--kw-primary); outline-offset: -2px; }
.kw-thread__body { flex: 1; min-width: 0; }
.kw-thread__top { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
.kw-thread__name { font-size: .875rem; font-weight: 700; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-thread__time { font-size: .75rem; color: var(--kw-muted); white-space: nowrap; flex-shrink: 0; }
.kw-thread__preview { margin: 4px 0 0; font-size: .75rem; color: var(--kw-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-chip {
    display: inline-flex; align-items: center; gap: 4px; max-width: 100%; margin-top: 4px; padding: 2px 8px; border-radius: 999px;
    background: #fef3c7; color: #78350f; border: 1px solid #fcd34d; font-size: .75rem; font-weight: 600;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.kw-chip--green { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.kw-badge { min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background: var(--kw-primary); color: #fff; font-size: .75rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Avatar (initials fallback sits behind the image) */
.kw-avatar { position: relative; width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
    background: linear-gradient(135deg, var(--kw-primary), #4a0914); color: #fff; font-weight: 700; font-size: .875rem;
    display: flex; align-items: center; justify-content: center; border: 2px solid var(--kw-card); box-shadow: 0 0 0 1px var(--kw-line); }
.kw-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }

/* Conversation */
.kw-main {
    display: flex; flex-direction: column; min-width: 0; min-height: 0;
    height: 100%; max-height: 100%; overflow: hidden; position: relative;
}
.kw-conversation {
    display: flex; flex-direction: column; flex: 1 1 0%; min-width: 0; min-height: 0;
    height: 100%; max-height: 100%; overflow: hidden;
}
.kw-head {
    display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
    padding: 16px 24px; border-bottom: 1px solid var(--kw-line); background: var(--kw-soft); flex-shrink: 0;
}
.kw-head__who { display: flex; gap: 12px; align-items: center; min-width: 0; }
.kw-head__title { font-size: 1rem; font-weight: 700; margin: 0; }
.kw-head__sub { font-size: .75rem; color: var(--kw-muted); margin: 2px 0 0; }
.kw-back { display: none; }

.kw-stream {
    flex: 1 1 0%; min-height: 0; padding: 24px; overflow-y: auto; overflow-x: hidden;
    display: flex; flex-direction: column; gap: 16px; background: var(--kw-card);
}
.kw-row { display: flex; width: 100%; }
.kw-row.is-mine { justify-content: flex-end; }
.kw-row:not(.is-mine) { justify-content: flex-start; }
.kw-bubble__container { position: relative; max-width: min(78%, 580px); min-width: 190px; width: fit-content; display: flex; flex-direction: column; }
.kw-bubble {
    min-width: 190px; width: 100%; max-width: 100%; box-sizing: border-box;
    padding: 12px 18px; border-radius: 18px; background: var(--kw-theirs); color: var(--kw-ink);
    border: 1px solid var(--kw-line); position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.kw-row.is-mine .kw-bubble { background: var(--kw-mine); color: #fff; border-color: transparent; border-bottom-right-radius: 6px; margin-left: auto; }
.kw-row:not(.is-mine) .kw-bubble { border-bottom-left-radius: 6px; margin-right: auto; }
.kw-bubble__who { font-size: .75rem; font-weight: 700; opacity: .85; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kw-bubble__text { margin: 0; font-size: .9375rem; line-height: 1.55; white-space: pre-wrap; word-break: normal; overflow-wrap: break-word; }
.kw-bubble__meta { margin-top: 6px; font-size: .75rem; opacity: .75; display: flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap; }
.kw-voice { margin-bottom: 8px; padding: 12px; border-radius: 12px; background: rgba(0,0,0,.08); }
.kw-row.is-mine .kw-voice { background: rgba(255,255,255,.14); }
.kw-voice__label { display: flex; align-items: center; gap: 8px; font-size: .75rem; font-weight: 700; margin-bottom: 8px; }
.kw-voice audio { width: 100%; height: 36px; }

/* Message Reactions & Actions */
.kw-bubble__actions {
    position: absolute; top: -14px; opacity: 0; transition: opacity .15s ease;
    display: flex; gap: 8px; z-index: 20;
}
.kw-row.is-mine .kw-bubble__actions { right: 8px; left: auto; }
.kw-row:not(.is-mine) .kw-bubble__actions { left: 8px; right: auto; }
.kw-bubble__container:hover .kw-bubble__actions,
.kw-bubble__container:focus-within .kw-bubble__actions,
.kw-bubble__actions.is-open { opacity: 1; }

.kw-react-trigger {
    background: var(--kw-card); border: 1px solid var(--kw-line); border-radius: 999px;
    width: 32px; height: 32px; min-width: 32px; min-height: 32px; display: inline-flex;
    align-items: center; justify-content: center; font-size: .875rem; color: var(--kw-muted);
    cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,.15); position: relative;
    transition: transform .15s ease, color .15s ease, border-color .15s ease;
}
.kw-react-trigger::after {
    content: ''; position: absolute; inset: -6px;
}
.kw-react-trigger:hover { transform: scale(1.1); color: var(--kw-primary); }
.kw-react-trigger--danger:hover { color: #dc2626; border-color: #fecaca; }

.kw-react-popover {
    position: absolute; bottom: calc(100% + 8px); top: auto;
    width: 290px; max-width: min(290px, calc(100vw - 32px));
    background: var(--kw-card); border: 1px solid var(--kw-line);
    border-radius: 20px; padding: 10px 12px;
    box-shadow: 0 16px 38px -8px rgba(0,0,0,.35), 0 0 0 1px rgba(0,0,0,.04);
    z-index: 50; display: flex; flex-direction: column; gap: 8px;
    animation: kwPopoverPop .15s cubic-bezier(0.16, 1, 0.3, 1);
}
.kw-react-popover.is-below {
    bottom: auto; top: calc(100% + 8px);
}
.kw-row.is-mine .kw-react-popover { right: 0; left: auto; }
.kw-row:not(.is-mine) .kw-react-popover { left: 0; right: auto; }

@keyframes kwPopoverPop {
    from { opacity: 0; transform: translateY(6px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.kw-react-popover__grid {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px;
}
.kw-react-popover__btn {
    background: transparent; border: 0; border-radius: 10px;
    height: 38px; font-size: 1.25rem; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: transform .12s ease, background-color .12s ease;
    position: relative;
}
.kw-react-popover__btn:hover {
    transform: scale(1.22); background: var(--kw-soft);
}
.kw-react-popover__heritage {
    margin-top: 2px; padding-top: 8px;
    border-top: 1px dashed var(--kw-line);
}
.kw-react-popover__heritage-title {
    font-size: .6875rem; font-weight: 700; color: var(--kw-muted);
    text-transform: uppercase; letter-spacing: .05em;
    display: flex; align-items: center; gap: 5px; margin-bottom: 6px;
    padding-left: 2px;
}
.kw-react-popover__heritage-grid {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px;
}
.kw-react-popover__btn--heritage {
    background: rgba(254, 243, 199, 0.4);
    border: 1px solid rgba(252, 211, 77, 0.45);
}
.dark-mode .kw-react-popover__btn--heritage {
    background: rgba(180, 83, 9, 0.18);
    border-color: rgba(217, 119, 6, 0.35);
}
.kw-react-popover__btn--heritage:hover {
    transform: scale(1.25);
    background: #fef3c7;
    border-color: #f59e0b;
}
.dark-mode .kw-react-popover__btn--heritage:hover {
    background: rgba(180, 83, 9, 0.4);
    border-color: #fbbf24;
}

.kw-reactions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
.kw-reaction-pill {
    display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px;
    font-size: .75rem; font-weight: 700; background: rgba(0,0,0,.06); border: 1px solid transparent;
    cursor: pointer; transition: transform .12s ease, background-color .12s ease;
}
.kw-row.is-mine .kw-reaction-pill { background: rgba(255,255,255,.18); color: #fff; }
.kw-reaction-pill.is-active {
    background: #fef3c7; color: #92400e; border-color: #fcd34d;
}
.kw-row.is-mine .kw-reaction-pill.is-active {
    background: #ffd700; color: #3b0a10; border-color: #fef08a;
}
.kw-reaction-pill:hover { transform: scale(1.05); }

/* Seen Status Badges */
.kw-seen-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: .75rem; white-space: nowrap;
}
.kw-seen--delivered { opacity: .75; }
.kw-seen--read { color: #38bdf8; font-weight: 800; }
.kw-seen--guardian { color: #34d399; font-weight: 700; font-size: .7rem; }

/* Quick Emoji Bar */
.kw-quick-emojis {
    display: flex; align-items: center; gap: 6px; overflow-x: auto; padding: 6px 0 10px;
    scrollbar-width: thin; -webkit-overflow-scrolling: touch;
}
.kw-quick-emojis__label {
    font-size: .6875rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700;
    color: var(--kw-muted); margin-right: 2px; flex-shrink: 0;
}
.kw-emoji-chip {
    flex-shrink: 0; width: 34px; height: 34px; border-radius: 10px; background: var(--kw-card);
    border: 1px solid var(--kw-line); font-size: 1.125rem; display: inline-flex; align-items: center;
    justify-content: center; cursor: pointer; transition: transform .12s ease, background-color .12s ease;
}
.kw-emoji-chip:hover { transform: scale(1.15); background: var(--kw-soft); }
.kw-emoji-chip:active { transform: scale(.95); }

/* Emoji Drawer */
.kw-emoji-drawer {
    margin-bottom: 12px; background: var(--kw-card); border: 1px solid var(--kw-line);
    border-radius: 16px; padding: 12px; box-shadow: 0 8px 24px -10px rgba(0,0,0,.15);
}
.kw-emoji-drawer__tabs {
    display: flex; gap: 4px; border-bottom: 1px solid var(--kw-line); padding-bottom: 8px; margin-bottom: 10px;
    overflow-x: auto;
}
.kw-emoji-drawer__tab {
    padding: 4px 10px; border-radius: 8px; border: 0; background: transparent; font-size: .75rem;
    font-weight: 700; color: var(--kw-muted); cursor: pointer; white-space: nowrap;
}
.kw-emoji-drawer__tab.is-active { background: var(--kw-primary); color: #fff; }
.kw-emoji-drawer__grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(36px, 1fr)); gap: 6px;
    max-height: 140px; overflow-y: auto; padding: 2px;
}
.kw-emoji-drawer__item {
    width: 36px; height: 36px; border-radius: 8px; border: 0; background: transparent;
    font-size: 1.25rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
    transition: transform .12s ease, background-color .12s ease;
}
.kw-emoji-drawer__item:hover { transform: scale(1.2); background: var(--kw-soft); }

.kw-composer { padding: 14px 20px 16px; border-top: 1px solid var(--kw-line); background: var(--kw-soft); flex-shrink: 0; position: relative; z-index: 5; }
.kw-rec { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px 16px; margin-bottom: 12px; border-radius: 12px; background: #fffbeb; border: 1px solid #fcd34d; color: #78350f; font-size: .8125rem; font-weight: 600; }
.kw-rec__dot { width: 10px; height: 10px; border-radius: 50%; background: #dc2626; animation: kwPulse 1s ease-in-out infinite; display: inline-block; margin-right: 8px; }
@keyframes kwPulse { 50% { opacity: .35; } }
.kw-composer__form { display: flex !important; flex-direction: column !important; align-items: stretch !important; gap: 10px !important; width: 100% !important; }
.kw-composer__form .kw-textarea {
    display: block !important; width: 100% !important; min-width: 100% !important; box-sizing: border-box !important;
    min-height: 58px; max-height: 150px; padding: 12px 16px; font-size: .9375rem;
    line-height: 1.55; border-radius: 14px; background: var(--kw-card); border: 1px solid var(--kw-line);
    box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: border-color .15s ease, box-shadow .15s ease;
    resize: none;
}
.kw-composer__form .kw-textarea:focus { outline: none; border-color: var(--kw-primary); box-shadow: 0 0 0 3px rgba(163,22,33,0.12); }
.kw-composer__actions { display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; width: 100% !important; gap: 8px; }
.kw-composer__tools { display: flex; align-items: center; gap: 8px; }

.kw-emoji-live-preview {
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px; padding: 6px 12px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    font-size: .75rem; color: #92400e;
}
.dark-mode .kw-emoji-live-preview { background: rgba(217,119,6,0.15); border-color: rgba(245,158,11,0.3); color: #fef3c7; }
.kw-emoji-live-preview__label { font-weight: 700; font-size: .6875rem; text-transform: uppercase; letter-spacing: .05em; display: inline-flex; align-items: center; gap: 4px; }
.kw-emoji-live-chip {
    display: inline-flex; align-items: center; gap: 6px; padding: 2px 8px;
    background: var(--kw-card); border: 1px solid var(--kw-line); border-radius: 999px;
    font-weight: 600; font-size: .75rem; color: var(--kw-ink);
}

/* Rich ContentEditable Composer & Inline 3D Emoji */
.kw-editable-composer {
    display: block !important; width: 100% !important; min-width: 100% !important; box-sizing: border-box !important;
    min-height: 58px; max-height: 150px; overflow-y: auto; white-space: pre-wrap;
    word-break: normal; overflow-wrap: break-word; user-select: text; outline: none;
    line-height: 1.55; padding: 12px 16px;
}
.kw-editable-composer:empty::before {
    content: attr(data-placeholder); color: var(--kw-muted); pointer-events: none; display: inline-block;
}
.kw-inline-3d-emoji {
    display: inline-block; vertical-align: -5px; margin: 0 3px; width: 24px; height: 24px;
    pointer-events: none; user-select: none;
}

/* Share Badges & Consent Banners */
.kw-btn.is-share-approved {
    background: #ecfdf5; border-color: #a7f3d0; color: #065f46;
}
.dark-mode .kw-btn.is-share-approved {
    background: #064e3b; border-color: #047857; color: #6ee7b7;
}
.kw-share-badge {
    font-size: .75rem; display: inline-flex; align-items: center; margin-left: 4px;
}
.kw-share-badge--approved { color: #16a34a; }
.kw-share-badge--pending { color: #d97706; }

.kw-consent-banner {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px;
    color: #92400e; margin-bottom: 8px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.08);
}
.dark-mode .kw-consent-banner {
    background: rgba(120, 53, 15, 0.25); border-color: rgba(217, 119, 6, 0.4); color: #fde68a;
}
.kw-consent-banner--approved {
    background: #f0fdf4; border-color: #bbf7d0; color: #166534;
}
.dark-mode .kw-consent-banner--approved {
    background: rgba(20, 83, 45, 0.25); border-color: rgba(34, 197, 94, 0.35); color: #bbf7d0;
}
.kw-consent-banner__icon { font-size: 1.25rem; flex-shrink: 0; }
.kw-consent-banner__body { flex: 1; min-width: 0; font-size: .8125rem; line-height: 1.45; }
.kw-consent-banner__body strong { display: block; margin-bottom: 2px; font-weight: 700; }
.kw-consent-banner__body p { margin: 0; }
.kw-consent-banner__actions { display: flex; gap: 6px; flex-shrink: 0; }

/* In-Stream Screenshot Audit Badge */
.kw-stream-event {
    align-self: center; display: inline-flex; align-items: center; gap: 6px;
    font-size: .75rem; font-weight: 600; padding: 4px 12px; border-radius: 999px;
    margin: 4px auto;
}
.kw-stream-event--screenshot {
    background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;
    box-shadow: 0 1px 3px rgba(220, 38, 38, 0.1);
}
.dark-mode .kw-stream-event--screenshot {
    background: #450a0a; color: #fca5a5; border-color: #7f1d1d;
}

/* Disappearing Ephemeral Message Styles */
.kw-disappear-pill {
    position: absolute; top: -5px; right: -5px; background: #dc2626; color: #fff;
    font-size: .625rem; font-weight: 800; border-radius: 999px; padding: 1px 5px;
    border: 1.5px solid var(--kw-card); line-height: 1.2;
}
.kw-disappear-popover {
    position: absolute; bottom: calc(100% + 10px); left: 0; right: auto; width: 220px;
    background: var(--kw-card); border: 1px solid var(--kw-line); border-radius: 16px;
    padding: 8px; box-shadow: 0 15px 35px -10px rgba(0,0,0,.35); z-index: 50;
}
.kw-disappear-popover__title {
    font-size: .75rem; font-weight: 700; color: var(--kw-muted); padding: 4px 8px 6px;
    border-bottom: 1px solid var(--kw-line); margin-bottom: 4px; display: flex; align-items: center; gap: 6px;
}
.kw-disappear-opt {
    width: 100%; display: flex; align-items: center; justify-content: space-between;
    padding: 7px 10px; font-size: .8125rem; font-weight: 600; border: 0; border-radius: 8px;
    background: transparent; color: var(--kw-ink); cursor: pointer; text-align: left;
    transition: background-color .12s ease;
}
.kw-disappear-opt:hover { background: var(--kw-soft); }
.kw-disappear-opt.is-selected { color: var(--kw-primary); font-weight: 700; background: rgba(163,22,33,.08); }
.kw-disappear-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; font-weight: 700;
    color: #dc2626; background: #fee2e2; border-radius: 999px; padding: 1px 7px;
}
.dark-mode .kw-disappear-badge { background: #450a0a; color: #fca5a5; }

/* Empty states */
.kw-empty { margin: auto; text-align: center; padding: 48px 24px; color: var(--kw-muted); max-width: 360px; }
.kw-empty__icon { width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; background: #fef3c7; color: #92400e; font-size: 1.5rem; }
.kw-empty h3 { font-size: 1.125rem; font-weight: 700; color: var(--kw-ink); margin: 0 0 8px; }
.kw-empty p { font-size: .875rem; line-height: 1.6; margin: 0 0 16px; }

/* Modal */
.kw-modal { position: fixed; inset: 0; z-index: 1080; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.kw-modal__card { width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto; background: var(--kw-card); color: var(--kw-ink); border: 1px solid var(--kw-line); border-radius: 20px; padding: 24px; box-shadow: 0 30px 60px -20px rgba(0,0,0,.5); }
.kw-modal__head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.kw-modal__head h2 { font-size: 1.125rem; font-weight: 700; margin: 0; }
.kw-field { margin-bottom: 16px; }
.kw-field label { display: block; font-size: .75rem; font-weight: 700; margin-bottom: 4px; color: var(--kw-ink); }
.kw-modal__foot { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }

/* Kin Picker & Omni-Search Component */
.kw-avatar--sm { width: 36px; height: 36px; font-size: .75rem; }
.kw-selected-kin {
    display: flex; align-items: center; gap: 12px; padding: 10px 14px;
    background: var(--kw-soft); border: 1px solid var(--kw-line); border-radius: 12px;
}
.kw-selected-kin__info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.kw-selected-kin__info strong { font-size: .875rem; font-weight: 700; color: var(--kw-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.kw-search-box { position: relative; width: 100%; }
.kw-search-box i.bi-search { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--kw-muted); pointer-events: none; }
.kw-search-box .kw-input { padding-left: 38px; padding-right: 38px; }
.kw-search-spinner { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: var(--kw-primary); }

.kw-kin-picker-list {
    max-height: 220px; overflow-y: auto; border: 1px solid var(--kw-line); border-radius: 12px;
    margin-top: 8px; background: var(--kw-card);
}
.kw-kin-item {
    width: 100%; display: flex; align-items: center; gap: 12px; padding: 10px 12px;
    border: 0; border-bottom: 1px solid var(--kw-line); background: transparent; cursor: pointer;
    text-align: left; transition: background-color .15s ease;
}
.kw-kin-item:last-child { border-bottom: 0; }
.kw-kin-item:hover { background: var(--kw-soft); }
.kw-kin-item.is-chosen { background: #fef2f2; }
.dark-mode .kw-kin-item.is-chosen { background: rgba(163,22,33,.2); }
.kw-kin-item__info { flex: 1; min-width: 0; }
.kw-kin-item__name { font-size: .875rem; font-weight: 700; color: var(--kw-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-kin-item__sub { font-size: .75rem; color: var(--kw-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-kin-item__check { color: #16a34a; font-size: 1.125rem; flex-shrink: 0; }
.kw-kin-item__arrow { color: var(--kw-muted); font-size: .875rem; flex-shrink: 0; }

/* Group Whispers & Call Styles */
.kw-modal-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--kw-line); margin-bottom: 16px; }
.kw-modal-tab { flex: 1; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 0; background: transparent; color: var(--kw-muted); font-size: 0.875rem; font-weight: 700; cursor: pointer; border-bottom: 2px solid transparent; transition: all .15s ease; }
.kw-modal-tab.is-active { color: var(--kw-primary); border-bottom-color: var(--kw-primary); }
.kw-chips-wrap { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; max-height: 110px; overflow-y: auto; padding: 4px; border: 1px solid var(--kw-line); border-radius: 12px; background: var(--kw-soft); }
.kw-member-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: var(--kw-card); border: 1px solid var(--kw-line); font-size: 0.8125rem; font-weight: 600; color: var(--kw-ink); box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.kw-member-chip button { border: 0; background: transparent; cursor: pointer; color: var(--kw-muted); padding: 0; margin-left: 2px; font-size: 0.875rem; }
.kw-member-chip button:hover { color: #dc2626; }

.kw-btn--call { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
.dark-mode .kw-btn--call { background: #064e3b; color: #a7f3d0; border-color: #047857; }
.kw-call-modal-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px; }
.kw-call-opt-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 18px 12px; border-radius: 16px; border: 1px solid var(--kw-line); background: var(--kw-card); color: var(--kw-ink); cursor: pointer; transition: all .15s ease; text-decoration: none; }
.kw-call-opt-btn:hover { border-color: var(--kw-primary); background: var(--kw-soft); transform: translateY(-2px); }
.kw-call-opt-btn i { font-size: 1.75rem; }
.kw-call-opt-btn span { font-size: 0.8125rem; font-weight: 700; text-align: center; }

/* In-App Calling Screen Overlay */
.kw-call-overlay { position: fixed; inset: 0; z-index: 1090; background: rgba(10, 15, 29, 0.95); backdrop-filter: blur(14px); display: flex; flex-direction: column; color: #fff; }
.kw-call-overlay__head { padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); }
.kw-call-overlay__title { font-size: 1.125rem; font-weight: 800; color: #fff; margin: 0; }
.kw-call-overlay__status { font-size: 0.8125rem; color: #ffd700; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.kw-call-overlay__body { flex: 1; position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 16px; }
.kw-remote-video-wrap { width: 100%; height: 100%; max-width: 960px; max-height: 640px; border-radius: 20px; overflow: hidden; background: #000; position: relative; box-shadow: 0 25px 60px rgba(0,0,0,0.8); }
.kw-remote-video { width: 100%; height: 100%; object-fit: cover; }
.kw-local-video-pip { position: absolute; bottom: 20px; right: 20px; width: 160px; height: 120px; border-radius: 14px; overflow: hidden; border: 2px solid rgba(255,255,255,0.3); background: #111; z-index: 10; box-shadow: 0 10px 25px rgba(0,0,0,0.6); }
.kw-local-video { width: 100%; height: 100%; object-fit: cover; }
.kw-voice-call-avatar { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; text-align: center; }
.kw-voice-avatar-ring { width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 4px solid #ffd700; box-shadow: 0 0 35px rgba(255,215,0,0.4); animation: kwWavePulse 1.8s ease-in-out infinite; }
@keyframes kwWavePulse { 0% { box-shadow: 0 0 0 0 rgba(255,215,0,0.6); } 70% { box-shadow: 0 0 0 25px rgba(255,215,0,0); } 100% { box-shadow: 0 0 0 0 rgba(255,215,0,0); } }
.kw-call-overlay__controls { padding: 24px; display: flex; align-items: center; justify-content: center; gap: 16px; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.3); }
.kw-call-ctrl-btn { width: 56px; height: 56px; border-radius: 50%; border: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; cursor: pointer; transition: transform .15s ease, filter .15s ease; color: #fff; background: rgba(255,255,255,0.15); }
.kw-call-ctrl-btn:hover { transform: scale(1.08); filter: brightness(1.15); }
.kw-call-ctrl-btn.is-active { background: #dc2626; color: #fff; }
.kw-call-ctrl-btn--end { width: 64px; height: 64px; background: #dc2626; color: #fff; box-shadow: 0 10px 25px rgba(220,38,38,0.5); }
.kw-call-ctrl-btn--end:hover { background: #b91c1c; }

/* Incoming Call Ringing Banner */
.kw-incoming-ring { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1100; width: 92%; max-width: 440px; background: #141a29; border: 1.5px solid #ffd700; border-radius: 20px; padding: 16px 20px; color: #fff; box-shadow: 0 20px 50px rgba(0,0,0,0.7); display: flex; align-items: center; gap: 16px; animation: kwRingSlide .3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes kwRingSlide { from { transform: translate(-50%, -40px); opacity: 0; } to { transform: translate(-50%, 0); opacity: 1; } }
.kw-incoming-ring__info { flex: 1; min-width: 0; }
.kw-incoming-ring__name { font-size: 0.9375rem; font-weight: 800; color: #fff; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-incoming-ring__type { font-size: 0.75rem; color: #fef08a; margin: 2px 0 0; }
.kw-incoming-ring__actions { display: flex; gap: 8px; }
.kw-incoming-btn { width: 44px; height: 44px; border-radius: 50%; border: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; cursor: pointer; color: #fff; }
.kw-incoming-btn--accept { background: #16a34a; box-shadow: 0 4px 15px rgba(22,163,74,0.4); }
.kw-incoming-btn--decline { background: #dc2626; box-shadow: 0 4px 15px rgba(220,38,38,0.4); }

@media (max-width: 991.98px) {
    .kw-shell { grid-template-columns: 1fr; grid-template-rows: 100%; height: clamp(580px, 82vh, 850px); min-height: 540px; }
    .kw-side { border-right: 0; }
    .kw-shell[data-pane="chat"] .kw-side { display: none; }
    .kw-shell[data-pane="list"] .kw-main { display: none; }
    .kw-back { display: inline-flex; }
    .kw-threads { max-height: none; }
    .kw-stream { padding: 16px; min-height: 0; }
    .kw-head, .kw-composer { padding: 12px 16px; }
    .kw-hero { padding: 24px 16px; }
    .kw-bubble__container { max-width: 88%; }
    .kw-bubble { max-width: 100%; }
    .kw-bubble__actions { opacity: .85; }
}
@media (prefers-reduced-motion: reduce) { .kw-rec__dot { animation: none; } .kw-btn { transition: none; } }
</style>
@endpush

@section('content')
<div class="kw-wrap" x-data="kinWhisperApp()" x-cloak>

  <header class="kw-hero">
    <div>
      <div class="kw-hero__eyebrow"><i class="bi bi-chat-heart-fill"></i> &nbsp;Private &amp; Supervised</div>
      <h1>Kin Whispers</h1>
      <p>Thoughtful private messages and 60-second voice dedications, anchored to the photographs, memories and people that matter to your family.</p>
    </div>
    <button type="button" class="kw-btn kw-btn--gold" @click="openCompose()">
      <i class="bi bi-plus-lg"></i><span>New Whisper</span>
    </button>
  </header>

  <div class="kw-notice" role="note">
    <i class="bi bi-shield-check"></i>
    <div><strong>Safeguarding:</strong> any whisper involving a member under 18 is visible to their parent or guardian, who is notified of new messages.</div>
  </div>

  <section class="kw-shell" :data-pane="pane" aria-label="Kin whispers workspace">

    {{-- Thread list --}}
    <aside class="kw-side" aria-label="Conversations">
      <div class="kw-search">
        <i class="bi bi-search"></i>
        <label for="kw_search" class="visually-hidden">Search conversations</label>
        <input id="kw_search" type="search" class="kw-input" x-model="searchQuery" placeholder="Search by name or topic">
      </div>

      <div class="kw-threads">
        <template x-for="thread in filteredThreads" :key="thread.id">
          <button type="button" class="kw-thread" :class="{'is-active': activeThread && activeThread.id === thread.id}" @click="selectThread(thread)">
            <div class="kw-avatar">
              <template x-if="thread.thread_type === 'group'">
                <i class="bi bi-people-fill" style="font-size:1.15rem; color:#ffd700;"></i>
              </template>
              <template x-if="thread.thread_type !== 'group'">
                <div>
                  <span x-text="initials(partnerName(thread))"></span>
                  <img :src="partnerAvatar(thread)" alt="" loading="lazy" x-on:error="$el.remove()">
                </div>
              </template>
            </div>
            <div class="kw-thread__body">
              <div class="kw-thread__top">
                <h3 class="kw-thread__name" :title="partnerName(thread)" x-text="partnerName(thread)"></h3>
                <span class="kw-thread__time" x-text="formatShort(thread.last_message_at)"></span>
              </div>
              <template x-if="thread.thread_type === 'group'">
                <span class="kw-chip kw-chip--green" style="font-size:0.6875rem;">
                  <i class="bi bi-people-fill"></i> <span x-text="(thread.participant_count || 2) + ' members'"></span>
                </span>
              </template>
              <template x-if="thread.thread_type !== 'group'">
                <span class="kw-chip" :title="thread.context_title || labelFor(thread.context_type)">
                  <i class="bi" :class="iconFor(thread.context_type)"></i><span x-text="thread.context_title || labelFor(thread.context_type)"></span>
                </span>
              </template>
              <template x-if="thread.is_supervisor_view"><span class="kw-chip kw-chip--green"><i class="bi bi-eye"></i> Guardian view</span></template>
              <p class="kw-thread__preview" x-text="thread.last_message_preview || 'No messages yet'"></p>
            </div>
            <template x-if="thread.unread_count > 0"><span class="kw-badge" x-text="thread.unread_count"></span></template>
          </button>
        </template>

        <template x-if="filteredThreads.length === 0">
          <div class="kw-empty">
            <div class="kw-empty__icon"><i class="bi bi-chat-dots"></i></div>
            <h3 x-text="searchQuery ? 'No matches' : 'No whispers yet'"></h3>
            <p x-text="searchQuery ? 'Try a different name or topic.' : 'Start a private message about a photo, memory or relative.'"></p>
            <button type="button" class="kw-btn kw-btn--primary" x-show="!searchQuery" @click="openCompose()"><i class="bi bi-plus-lg"></i><span>New Whisper</span></button>
          </div>
        </template>
      </div>
    </aside>

    {{-- Conversation --}}
    <div class="kw-main">
      <template x-if="activeThread">
        <div class="kw-conversation">
          <div class="kw-head">
            <div class="kw-head__who">
              <button type="button" class="kw-btn kw-btn--ghost kw-btn--square kw-back" @click="pane = 'list'" aria-label="Back to conversations"><i class="bi bi-arrow-left"></i></button>
              <div class="kw-avatar">
                <template x-if="activeThread.thread_type === 'group'">
                  <i class="bi bi-people-fill" style="font-size:1.25rem; color:#ffd700;"></i>
                </template>
                <template x-if="activeThread.thread_type !== 'group'">
                  <div>
                    <span x-text="initials(partnerName(activeThread))"></span>
                    <img :src="partnerAvatar(activeThread)" alt="" x-on:error="$el.remove()">
                  </div>
                </template>
              </div>
              <div style="min-width:0;">
                <h2 class="kw-head__title" x-text="partnerName(activeThread)"></h2>
                <p class="kw-head__sub">
                  <template x-if="activeThread.thread_type === 'group'">
                    <span><i class="bi bi-people"></i> <span x-text="(activeThread.participants ? activeThread.participants.length : (activeThread.participant_count || 2)) + ' family members'"></span> &middot; <button type="button" @click="showGroupRosterModal = true" style="background:none; border:none; padding:0; color:var(--kw-primary); font-weight:700; text-decoration:underline; cursor:pointer;">View roster</button></span>
                  </template>
                  <template x-if="activeThread.thread_type !== 'group'">
                    <span><i class="bi" :class="iconFor(activeThread.context_type)"></i> <span x-text="activeThread.context_title || labelFor(activeThread.context_type)"></span></span>
                  </template>
                  <template x-if="activeThread.has_minor_participant"><span> &middot; <i class="bi bi-shield-check"></i> Supervised</span></template>
                </p>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <button type="button" class="kw-btn kw-btn--call" @click="openCallModal()" title="Start video or voice call with kin">
                <i class="bi bi-telephone-fill"></i><span>Call</span>
              </button>
              <button type="button" class="kw-btn"
                      :class="activeThread.share_status === 'approved' ? 'kw-btn--ghost is-share-approved' : 'kw-btn--ghost'"
                      @click="handleShareClick()"
                      :title="shareBtnTooltip()">
                <i class="bi bi-whatsapp" style="color:#16a34a"></i>
                <span class="d-none d-sm-inline" x-text="shareBtnLabel()"></span>
                <template x-if="activeThread.share_status === 'pending'">
                  <span class="kw-share-badge kw-share-badge--pending" title="Awaiting consent">⏳</span>
                </template>
                <template x-if="activeThread.share_status === 'approved'">
                  <span class="kw-share-badge kw-share-badge--approved" title="Authorised"><i class="bi bi-check-circle-fill"></i></span>
                </template>
              </button>
            </div>
          </div>

          <div class="kw-stream" id="kwStream" aria-live="polite">
            {{-- Bilateral Share Consent Request Banner (for recipient) --}}
            <template x-if="activeThread && activeThread.share_status === 'pending' && activeThread.share_requested_by !== currentUserId">
              <div class="kw-consent-banner">
                <div class="kw-consent-banner__icon"><i class="bi bi-shield-lock-fill"></i></div>
                <div class="kw-consent-banner__body">
                  <strong>External Sharing Request</strong>
                  <p><span x-text="partnerName()"></span> would like permission to share this whisper on WhatsApp. Do you authorise sharing?</p>
                </div>
                <div class="kw-consent-banner__actions">
                  <button type="button" class="kw-btn kw-btn--primary" style="padding:6px 14px;" @click="respondToShareRequest('approve')" :disabled="isRespondingShare">
                    <i class="bi bi-check-lg"></i> <span>Authorise</span>
                  </button>
                  <button type="button" class="kw-btn kw-btn--ghost" style="padding:6px 14px;" @click="respondToShareRequest('decline')" :disabled="isRespondingShare">
                    <i class="bi bi-x-lg"></i> <span>Decline</span>
                  </button>
                </div>
              </div>
            </template>

            {{-- Bilateral Share Approved Reassurance Banner --}}
            <template x-if="activeThread && activeThread.share_status === 'approved'">
              <div class="kw-consent-banner kw-consent-banner--approved">
                <div class="kw-consent-banner__icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="kw-consent-banner__body">
                  <span>Mutual WhatsApp sharing is authorised for this whisper.</span>
                </div>
                <template x-if="activeThread.share_approved_by === currentUserId">
                  <button type="button" class="kw-btn kw-btn--ghost" style="padding:4px 10px; font-size:.75rem;" @click="respondToShareRequest('revoke')" title="Revoke sharing consent">
                    Revoke Consent
                  </button>
                </template>
              </div>
            </template>

            {{-- In-Stream Screenshot Audit Events --}}
            <template x-for="evt in threadEvents.filter(e => e.event_type === 'screenshot_taken')" :key="evt.id">
              <div class="kw-stream-event kw-stream-event--screenshot">
                <i class="bi bi-camera-fill"></i>
                <span x-text="evt.user_id === currentUserId ? 'You took a screenshot' : evt.user_name + ' took a screenshot'"></span>
                &middot;
                <span x-text="formatShort(evt.created_at)"></span>
              </div>
            </template>
            <template x-for="msg in messages" :key="msg.id">
              <div class="kw-row" :class="{'is-mine': msg.sender_id === currentUserId}">
                <div class="kw-bubble__container">
                  <div class="kw-bubble__actions" :class="{'is-open': activeReactionMsgId === msg.id}">
                    <button type="button" class="kw-react-trigger" @click="openReactionPopover(msg.id, $event)" aria-label="Add reaction" title="Add reaction">
                      <i class="bi bi-emoji-smile"></i>
                    </button>
                    <template x-if="msg.sender_id === currentUserId">
                      <button type="button" class="kw-react-trigger kw-react-trigger--danger" @click="confirmDeleteMessage(msg)" aria-label="Delete message" title="Delete your whisper">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </template>
                    <div class="kw-react-popover" :class="{'is-below': reactionPopoverPosition === 'below'}" x-show="activeReactionMsgId === msg.id" @click.outside="activeReactionMsgId = null" x-cloak>
                      {{-- Standard Family Reactions --}}
                      <div class="kw-react-popover__grid">
                        <template x-for="emo in allowedReactions.filter(e => !e.endsWith('_3d'))" :key="emo">
                          <button type="button" class="kw-react-popover__btn" @click="toggleReaction(msg.id, emo); activeReactionMsgId = null;" :title="'React with ' + emo">
                            <span x-text="emo"></span>
                          </button>
                        </template>
                      </div>

                      {{-- Living Heritage 3D Reactions --}}
                      <div class="kw-react-popover__heritage">
                        <div class="kw-react-popover__heritage-title">
                          <i class="bi bi-stars" style="color:var(--kw-gold-bright)"></i>
                          <span>Living Heritage 3D</span>
                        </div>
                        <div class="kw-react-popover__heritage-grid">
                          <template x-for="emo in allowedReactions.filter(e => e.endsWith('_3d'))" :key="emo">
                            <button type="button" class="kw-react-popover__btn kw-react-popover__btn--heritage" @click="toggleReaction(msg.id, emo); activeReactionMsgId = null;" :title="'React with ' + formatEmojiTitle(emo)">
                              <img :src="'/img/emoji/' + emo + '.svg'" class="atg-3d-emoji" width="24" height="24" :alt="emo" x-on:error="$el.src='/img/emoji/' + emo + '.png'">
                            </button>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="kw-bubble">
                    <div class="kw-bubble__who" x-text="msg.sender_id === currentUserId ? 'You' : msg.sender_name"></div>
                    <template x-if="msg.message_type === 'audio_whisper' && msg.audio_url">
                      <div class="kw-voice">
                        <div class="kw-voice__label"><i class="bi bi-mic-fill"></i> Voice whisper</div>
                        <audio controls preload="none" :src="msg.audio_url"></audio>
                      </div>
                    </template>
                    <template x-if="msg.body"><p class="kw-bubble__text" x-html="renderMessageBody(msg.body)"></p></template>

                    {{-- Reactions display --}}
                    <div class="kw-reactions" x-show="hasReactions(msg)">
                      <template x-for="(uids, emo) in (msg.context_metadata?.reactions || {})" :key="emo">
                        <button type="button" class="kw-reaction-pill"
                                :class="{'is-active': uids && uids.includes(currentUserId)}"
                                @click="toggleReaction(msg.id, emo)"
                                :title="(uids ? uids.length : 0) + ' reaction' + ((uids && uids.length > 1) ? 's' : '')">
                          <template x-if="emo.endsWith('_3d')">
                            <img :src="'/img/emoji/' + emo + '.svg'" class="atg-3d-emoji" width="18" height="18" :alt="emo" x-on:error="$el.src='/img/emoji/' + emo + '.png'">
                          </template>
                          <template x-if="!emo.endsWith('_3d')">
                            <span x-text="emo"></span>
                          </template>
                          <span class="kw-reaction-pill__count" x-text="uids ? uids.length : 1"></span>
                        </button>
                      </template>
                    </div>

                    {{-- Metadata, Seen Receipts & Ephemeral Countdown --}}
                    <div class="kw-bubble__meta" style="display:flex; align-items:center; justify-content:flex-end; gap:6px; flex-wrap:wrap;">
                      <template x-if="msg.expires_at">
                        <span class="kw-disappear-badge" :title="'Auto-disappears at ' + formatSeenTime(msg.expires_at)">
                          <i class="bi bi-hourglass-split"></i> <span x-text="formatTimeLeft(msg, nowTs)"></span>
                        </span>
                      </template>
                      <span x-text="formatShort(msg.created_at)"></span>
                      <template x-if="msg.sender_id === currentUserId">
                        <span class="kw-seen-badge" :title="msg.is_read ? ('Seen at ' + formatSeenTime(msg.read_at)) : 'Delivered to family member'">
                          <span :class="msg.is_read ? 'kw-seen--read' : 'kw-seen--delivered'"
                                x-text="msg.is_read ? '✓✓ Seen ' + formatSeenLabel(msg.read_at) : '✓ Delivered'"></span>
                        </span>
                      </template>
                      <template x-if="msg.parent_viewed">
                        <span class="kw-seen--guardian" title="Reviewed by supervising parent">
                          <i class="bi bi-shield-check"></i> Guardian Seen
                        </span>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </template>
            <template x-if="messages.length === 0">
              <div class="kw-empty">
                <div class="kw-empty__icon"><i class="bi bi-mic"></i></div>
                <h3>Say something warm</h3>
                <p>Type a message or record a voice whisper of up to 60 seconds.</p>
              </div>
            </template>
          </div>

          <div class="kw-composer">
            {{-- Quick Emojis --}}
            <div class="kw-quick-emojis" aria-label="Quick family emojis">
              <span class="kw-quick-emojis__label">Quick:</span>
              <template x-for="emo in quickEmojis" :key="emo">
                <button type="button" class="kw-emoji-chip" @click="insertEmoji(emo)" :title="'Insert ' + emo">
                  <template x-if="emo.endsWith('_3d')">
                    <img :src="'/img/emoji/' + emo + '.svg'" class="atg-3d-emoji" width="22" height="22" :alt="emo" x-on:error="$el.src='/img/emoji/' + emo + '.png'">
                  </template>
                  <template x-if="!emo.endsWith('_3d')">
                    <span x-text="emo"></span>
                  </template>
                </button>
              </template>
            </div>

            {{-- Collapsible Emoji Drawer --}}
            <div class="kw-emoji-drawer" x-show="showEmojiDrawer" x-collapse x-cloak>
              <div class="kw-emoji-drawer__tabs">
                <template x-for="tab in emojiCategories" :key="tab.id">
                  <button type="button" class="kw-emoji-drawer__tab" :class="{'is-active': activeEmojiCategory === tab.id}" @click="activeEmojiCategory = tab.id" x-text="tab.name"></button>
                </template>
              </div>
              <div class="kw-emoji-drawer__grid">
                <template x-for="emo in currentCategoryEmojis" :key="emo">
                  <button type="button" class="kw-emoji-drawer__item" @click="insertEmoji(emo)" :title="emo">
                    <template x-if="emo.endsWith('_3d')">
                      <img :src="'/img/emoji/' + emo + '.svg'" class="atg-3d-emoji" width="30" height="30" :alt="emo" x-on:error="$el.src='/img/emoji/' + emo + '.png'">
                    </template>
                    <template x-if="!emo.endsWith('_3d')">
                      <span x-text="emo"></span>
                    </template>
                  </button>
                </template>
              </div>
            </div>

            <div class="kw-rec" x-show="isRecording || recordedAudioUrl" x-cloak>
              <span><span class="kw-rec__dot" x-show="isRecording"></span><span x-text="recordingStatusText"></span> &middot; <span x-text="formatSeconds(recordingSeconds)"></span> / 1:00</span>
              <span style="display:flex; gap:8px;">
                <button type="button" class="kw-btn kw-btn--primary" x-show="isRecording" @click="stopAudioRecording()">Stop</button>
                <button type="button" class="kw-btn kw-btn--ghost" x-show="recordedAudioUrl && !isRecording" @click="discardRecordedAudio()">Discard</button>
              </span>
            </div>
            <div x-show="recordedAudioUrl && !isRecording" x-cloak style="margin-bottom:12px;"><audio controls :src="recordedAudioUrl" style="width:100%; height:36px;"></audio></div>

            <form @submit.prevent="sendMessage()" class="kw-composer__form">
              @include('csrf')
              <label for="kw_body" class="visually-hidden">Your whisper</label>
              <div id="kw_body"
                   class="kw-textarea kw-editable-composer"
                   contenteditable="true"
                   role="textbox"
                   aria-multiline="true"
                   data-placeholder="Write a private message about this memory…"
                   @input="onComposerInput($event)"
                   @keydown.enter.prevent.exact="sendMessage()"></div>

              {{-- Live 3D Heritage Emoji Preview Chip --}}
              <div class="kw-emoji-live-preview" x-show="preview3dEmojis.length > 0" x-cloak>
                <span class="kw-emoji-live-preview__label"><i class="bi bi-stars"></i> Living Heritage:</span>
                <template x-for="emo in preview3dEmojis" :key="emo">
                  <span class="kw-emoji-live-chip">
                    <img :src="'/img/emoji/' + emo + '.svg'" class="atg-3d-emoji" width="20" height="20" :alt="emo" x-on:error="$el.src='/img/emoji/' + emo + '.png'">
                    <span x-text="formatEmojiLabel(emo)"></span>
                  </span>
                </template>
              </div>

              <div class="kw-composer__actions">
                <div class="kw-composer__tools">
                  {{-- Disappearing message timer toggle --}}
                  <div style="position:relative;">
                    <button type="button" class="kw-btn kw-btn--ghost kw-btn--square"
                            :class="{'is-active': activeDisappearSeconds > 0}"
                            @click="showDisappearMenu = !showDisappearMenu"
                            :title="activeDisappearSeconds > 0 ? ('Auto-disappears in ' + disappearLabel(activeDisappearSeconds)) : 'Auto-disappear timer (Off)'"
                            aria-label="Auto-disappear message timer">
                      <i class="bi bi-hourglass-split" :style="activeDisappearSeconds > 0 ? 'color:#dc2626; font-weight:bold;' : ''"></i>
                      <span x-show="activeDisappearSeconds > 0" class="kw-disappear-pill" x-text="disappearShort(activeDisappearSeconds)"></span>
                    </button>
                    <div class="kw-disappear-popover" x-show="showDisappearMenu" @click.outside="showDisappearMenu = false" x-cloak>
                      <div class="kw-disappear-popover__title"><i class="bi bi-stopwatch"></i> Disappearing Message</div>
                      <template x-for="opt in disappearOptions" :key="opt.seconds">
                        <button type="button" class="kw-disappear-opt"
                                :class="{'is-selected': activeDisappearSeconds === opt.seconds}"
                                @click="activeDisappearSeconds = opt.seconds; showDisappearMenu = false;">
                          <span x-text="opt.label"></span>
                          <i class="bi bi-check" x-show="activeDisappearSeconds === opt.seconds"></i>
                        </button>
                      </template>
                    </div>
                  </div>

                  <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" :class="{'is-active': showEmojiDrawer}" @click="showEmojiDrawer = !showEmojiDrawer" aria-label="Emoji picker" title="Open family emoji palette">
                    <i class="bi bi-emoji-smile-fill" style="color:var(--kw-gold)"></i>
                  </button>
                  <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" :class="{'is-live': isRecording}" @click="toggleAudioRecording()" :aria-label="isRecording ? 'Stop recording' : 'Record a voice whisper'">
                    <i class="bi" :class="isRecording ? 'bi-stop-fill' : 'bi-mic-fill'"></i>
                  </button>
                </div>

                <button type="submit" class="kw-btn kw-btn--primary" data-spin="true" :disabled="isSubmitting || (!newMessageBody.trim() && !recordedAudioBlob)">
                  <span x-text="isSubmitting ? 'Sending…' : 'Send'"></span><i class="bi bi-send-fill"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </template>

      <template x-if="!activeThread">
        <div class="kw-empty">
          <div class="kw-empty__icon"><i class="bi bi-chat-heart"></i></div>
          <h3>Choose a conversation</h3>
          <p>Pick a thread on the left, or start a new whisper about a photo, memory or relative.</p>
          <button type="button" class="kw-btn kw-btn--primary" @click="openCompose()"><i class="bi bi-plus-lg"></i><span>New Whisper</span></button>
        </div>
      </template>
    </div>
  </section>

  {{-- Compose modal --}}
  <div class="kw-modal" x-show="showCompose" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_modal_title"
       @keydown.escape.window="showCompose = false" @click.self="showCompose = false">
    <div class="kw-modal__card" x-trap.noscroll="showCompose">
      <div class="kw-modal__head">
        <h2 id="kw_modal_title" x-text="composeMode === 'group' ? 'New Family Group Whisper' : 'New Direct Kin Whisper'"></h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="showCompose = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>

      {{-- Dual-Mode Tabs --}}
      <div class="kw-modal-tabs">
        <button type="button" class="kw-modal-tab" :class="{'is-active': composeMode === 'direct'}" @click="composeMode = 'direct'">
          <i class="bi bi-person"></i> Direct Whisper
        </button>
        <button type="button" class="kw-modal-tab" :class="{'is-active': composeMode === 'group'}" @click="composeMode = 'group'">
          <i class="bi bi-people-fill"></i> Group Whisper
        </button>
      </div>

      {{-- Direct Whisper Form --}}
      <form x-show="composeMode === 'direct'" @submit.prevent="createNewThread()">
        @include('csrf')
        <div class="kw-field">
          <label id="kw_recipient_label">Who is it for? <span style="font-weight:400; color:var(--kw-muted)">(Select relative)</span></label>
          <input type="hidden" name="recipient_id" :value="newRecipientId" required>

          {{-- Chosen Recipient Card --}}
          <div class="kw-selected-kin" x-show="selectedKinMember" x-cloak>
            <div class="kw-avatar kw-avatar--sm">
              <span x-text="initials(selectedKinMember?.name)"></span>
              <img :src="resolveAvatar(selectedKinMember?.avatar)" alt="" x-on:error="$el.remove()">
            </div>
            <div class="kw-selected-kin__info">
              <strong x-text="selectedKinMember?.name"></strong>
              <span class="kw-chip" style="margin:0; font-size:0.7rem; align-self:flex-start;" x-text="selectedKinMember?.famCode ? ('#' + selectedKinMember.famCode.replace('#', '')) : 'Family Kin'"></span>
            </div>
            <button type="button" class="kw-btn kw-btn--ghost" @click="clearSelectedKin()" style="min-height:36px; padding:0 12px; font-size:0.75rem;">Change</button>
          </div>

          {{-- Dynamic Omni-Search & Relative Selector --}}
          <div class="kw-search-kin" x-show="!selectedKinMember">
            <div class="kw-search-box">
              <i class="bi bi-search"></i>
              <label for="kw_kin_search" class="visually-hidden">Search relatives</label>
              <input id="kw_kin_search" type="search" class="kw-input"
                     x-model="kinSearchQuery"
                     @input.debounce.250ms="performKinSearch()"
                     placeholder="Type a relative's name or code to search…"
                     autocomplete="off">
              <span x-show="isSearchingKin" class="spinner-border spinner-border-sm kw-search-spinner" role="status" aria-hidden="true" x-cloak></span>
            </div>

            <div class="kw-kin-picker-list" role="listbox" aria-labelledby="kw_recipient_label">
              <template x-for="k in displayedKin" :key="k.id">
                <button type="button" class="kw-kin-item"
                        :class="{'is-chosen': newRecipientId === k.id}"
                        @click="selectRecipient(k)">
                  <div class="kw-avatar kw-avatar--sm">
                    <span x-text="initials(k.name)"></span>
                    <img :src="resolveAvatar(k.avatar)" alt="" x-on:error="$el.remove()">
                  </div>
                  <div class="kw-kin-item__info">
                    <div class="kw-kin-item__name" x-text="k.name"></div>
                    <div class="kw-kin-item__sub" x-text="k.description || (k.famCode ? ('#' + k.famCode.replace('#', '')) : 'Verified Kin')"></div>
                  </div>
                  <i class="bi bi-check-circle-fill kw-kin-item__check" x-show="newRecipientId === k.id"></i>
                  <i class="bi bi-chevron-right kw-kin-item__arrow" x-show="newRecipientId !== k.id"></i>
                </button>
              </template>

              <template x-if="displayedKin.length === 0">
                <div class="kw-empty" style="padding:16px;">
                  <div class="kw-empty__icon" style="width:40px; height:40px; font-size:1.125rem; margin-bottom:8px;"><i class="bi bi-person-x"></i></div>
                  <p style="font-size:0.8125rem; margin:0;" x-text="kinSearchQuery ? ('No relatives matching \"' + kinSearchQuery + '\".') : 'No other relatives in directory yet.'"></p>
                </div>
              </template>
            </div>
          </div>
        </div>
        <div class="kw-field">
          <label for="kw_ctx_type">What is it about?</label>
          <select id="kw_ctx_type" class="kw-select" x-model="newContextType">
            <option value="catch_up">☕ Just a friendly catch-up</option>
            <option value="post">📝 Something about a family post</option>
            <option value="photo">📷 A photograph or heirloom</option>
            <option value="memory">💭 A shared family memory</option>
            <option value="family_node">🌳 A person in the family tree</option>
            <option value="event">🎉 A family event or gathering</option>
            <option value="milestone">🏆 A milestone or achievement</option>
            <option value="recipe">🍲 A family recipe or tradition</option>
            <option value="prayer">🙏 A prayer, blessing, or comfort</option>
            <option value="advice">💡 Family wisdom or advice</option>
            <option value="general">❤️ A general family dedication</option>
          </select>
        </div>
        <div class="kw-field">
          <label for="kw_ctx_title">Topic <span style="font-weight:400; color:var(--kw-muted)">(optional)</span></label>
          <input id="kw_ctx_title" type="text" class="kw-input" maxlength="120" x-model="newContextTitle" placeholder="e.g. Catching up after the weekend, Grandma's wedding photo, new job celebration…">
        </div>
        <div class="kw-modal__foot">
          <button type="button" class="kw-btn kw-btn--ghost" @click="showCompose = false">Cancel</button>
          <button type="submit" class="kw-btn kw-btn--primary" data-spin="true" :disabled="!newRecipientId || isCreating">
            <span x-text="isCreating ? 'Opening…' : 'Start whisper'"></span>
          </button>
        </div>
      </form>

      {{-- Group Whisper Form --}}
      <form x-show="composeMode === 'group'" @submit.prevent="createGroupThread()">
        @include('csrf')
        <div class="kw-field">
          <label for="kw_group_title">Group Name / Title</label>
          <input id="kw_group_title" type="text" class="kw-input" maxlength="100" x-model="newGroupTitle" placeholder="e.g. Olaogun Siblings, Family Reunion 2026, Holiday Planning" required>
        </div>

        <div class="kw-field">
          <label>Select Family Members <span style="font-weight:400; color:var(--kw-muted)">(Pick at least 1 relative)</span></label>

          {{-- Selected Members Chips --}}
          <div class="kw-chips-wrap" x-show="selectedGroupMembers.length > 0">
            <template x-for="m in selectedGroupMembers" :key="m.id">
              <span class="kw-member-chip">
                <span x-text="m.name"></span>
                <button type="button" @click="toggleGroupMember(m)" aria-label="Remove member">&times;</button>
              </span>
            </template>
          </div>

          {{-- Searchable Kin Picker List --}}
          <div class="kw-search-box" style="margin-bottom:8px;">
            <i class="bi bi-search"></i>
            <input type="search" class="kw-input" x-model="kinSearchQuery" placeholder="Filter relatives…">
          </div>

          <div class="kw-kin-picker-list" style="max-height:180px;">
            <template x-for="k in displayedKin" :key="k.id">
              <button type="button" class="kw-kin-item"
                      :class="{'is-chosen': isMemberSelected(k.id)}"
                      @click="toggleGroupMember(k)">
                <div class="kw-avatar kw-avatar--sm">
                  <span x-text="initials(k.name)"></span>
                  <img :src="resolveAvatar(k.avatar)" alt="" x-on:error="$el.remove()">
                </div>
                <div class="kw-kin-item__info">
                  <div class="kw-kin-item__name" x-text="k.name"></div>
                  <div class="kw-kin-item__sub" x-text="k.description || ('#' + (k.famCode || '').replace('#', ''))"></div>
                </div>
                <i class="bi bi-check-circle-fill kw-kin-item__check" x-show="isMemberSelected(k.id)"></i>
                <i class="bi bi-plus-circle kw-kin-item__arrow" x-show="!isMemberSelected(k.id)"></i>
              </button>
            </template>
          </div>
        </div>

        <div class="kw-field">
          <label for="kw_group_ctx_title">Topic / Purpose <span style="font-weight:400; color:var(--kw-muted)">(optional)</span></label>
          <input id="kw_group_ctx_title" type="text" class="kw-input" maxlength="120" x-model="newContextTitle" placeholder="e.g. Planning Christmas gathering, sharing memories, weekly blessings…">
        </div>

        <div class="kw-modal__foot">
          <button type="button" class="kw-btn kw-btn--ghost" @click="showCompose = false">Cancel</button>
          <button type="submit" class="kw-btn kw-btn--primary" data-spin="true" :disabled="selectedGroupMembers.length < 1 || !newGroupTitle.trim() || isCreating">
            <span x-text="isCreating ? 'Creating group…' : 'Create Group Whisper'"></span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Call Options Modal --}}
  <div class="kw-modal" x-show="showCallModal" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_call_modal_title"
       @keydown.escape.window="showCallModal = false" @click.self="showCallModal = false">
    <div class="kw-modal__card" x-trap.noscroll="showCallModal" style="max-width:440px;">
      <div class="kw-modal__head">
        <h2 id="kw_call_modal_title"><i class="bi bi-telephone-outbound-fill" style="color:var(--kw-gold)"></i> Connect with Kin</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="showCallModal = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <p style="font-size:0.875rem; color:var(--kw-muted); margin-bottom:12px;">Choose how you would like to connect with <strong x-text="partnerName(activeThread)"></strong>:</p>
      <div class="kw-call-modal-options">
        <button type="button" class="kw-call-opt-btn" @click="initiateInAppCall('video'); showCallModal = false;">
          <i class="bi bi-camera-video-fill" style="color:var(--kw-primary)"></i>
          <span>In-App Video Call</span>
        </button>
        <button type="button" class="kw-call-opt-btn" @click="initiateInAppCall('audio'); showCallModal = false;">
          <i class="bi bi-mic-fill" style="color:var(--kw-gold)"></i>
          <span>In-App Voice Call</span>
        </button>
        <template x-if="partnerMobileNumber()">
          <a :href="'tel:' + partnerMobileNumber()" class="kw-call-opt-btn" @click="showCallModal = false;">
            <i class="bi bi-phone-fill" style="color:#0284c7"></i>
            <span>Cellular Call</span>
          </a>
        </template>
        <button type="button" class="kw-call-opt-btn" @click="openWhatsApp(); showCallModal = false;">
          <i class="bi bi-whatsapp" style="color:#16a34a"></i>
          <span>WhatsApp Call</span>
        </button>
      </div>
    </div>
  </div>

  {{-- Bilateral Share Consent Request Modal --}}
  <div class="kw-modal" x-show="showShareRequestModal" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_share_modal_title"
       @keydown.escape.window="showShareRequestModal = false" @click.self="showShareRequestModal = false">
    <div class="kw-modal__card" x-trap.noscroll="showShareRequestModal" style="max-width:440px;">
      <div class="kw-modal__head">
        <h2 id="kw_share_modal_title"><i class="bi bi-shield-lock-fill" style="color:#16a34a"></i> Recipient Consent Required</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="showShareRequestModal = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <p style="font-size:0.9375rem; line-height:1.6; color:var(--kw-ink); margin-bottom:16px;">
        Private whispers are encrypted and strictly confidential. To protect everyone's privacy, <strong x-text="partnerName()"></strong> will be notified to authorise sharing before this whisper can be shared on WhatsApp.
      </p>
      <div class="kw-modal__foot">
        <button type="button" class="kw-btn kw-btn--ghost" @click="showShareRequestModal = false">Cancel</button>
        <button type="button" class="kw-btn kw-btn--primary" data-spin="true" @click="submitShareRequest()" :disabled="isRequestingShare">
          <i class="bi bi-send-fill"></i> <span x-text="isRequestingShare ? 'Sending…' : 'Notify Kin to Authorise'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- Delete Own Whisper Confirmation Modal --}}
  <div class="kw-modal" x-show="pendingDeleteMsg !== null" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_delete_modal_title"
       @keydown.escape.window="cancelDeleteMessage()" @click.self="cancelDeleteMessage()">
    <div class="kw-modal__card" x-trap.noscroll="pendingDeleteMsg !== null" style="max-width:440px;">
      <div class="kw-modal__head">
        <h2 id="kw_delete_modal_title"><i class="bi bi-trash3-fill" style="color:#dc2626"></i> Delete Whisper</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="cancelDeleteMessage()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <p style="font-size:0.9375rem; line-height:1.6; color:var(--kw-ink); margin-bottom:16px;">
        Are you sure you want to delete this whisper? It will be permanently removed for everyone in the conversation and cannot be undone.
      </p>
      <div class="kw-modal__foot">
        <button type="button" class="kw-btn kw-btn--ghost" @click="cancelDeleteMessage()" :disabled="isDeletingMsg">Keep Whisper</button>
        <button type="button" class="kw-btn kw-btn--primary" data-spin="true" @click="executeDeleteMessage()" :disabled="isDeletingMsg">
          <i class="bi bi-trash3-fill"></i> <span x-text="isDeletingMsg ? 'Deleting…' : 'Delete Permanently'"></span>
        </button>
      </div>
    </div>
  </div>

  {{-- Group Roster Modal --}}
  <div class="kw-modal" x-show="showGroupRosterModal" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_roster_modal_title"
       @keydown.escape.window="showGroupRosterModal = false" @click.self="showGroupRosterModal = false">
    <div class="kw-modal__card" x-trap.noscroll="showGroupRosterModal" style="max-width:480px;">
      <div class="kw-modal__head">
        <h2 id="kw_roster_modal_title"><i class="bi bi-people-fill" style="color:var(--kw-gold)"></i> Group Members</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="showGroupRosterModal = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div style="max-height:300px; overflow-y:auto; margin-bottom:16px;">
        <template x-for="p in (activeThread?.participants || [])" :key="p.id">
          <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--kw-line);">
            <div style="display:flex; align-items:center; gap:10px;">
              <div class="kw-avatar kw-avatar--sm">
                <span x-text="initials(p.name)"></span>
                <img :src="resolveAvatar(p.avatar)" alt="" x-on:error="$el.remove()">
              </div>
              <div>
                <strong style="font-size:0.875rem;" x-text="p.name"></strong>
                <div style="font-size:0.75rem; color:var(--kw-muted);" x-text="p.fam_code ? ('#' + String(p.fam_code).replace('#', '')) : 'Connected Kin'"></div>
              </div>
            </div>
            <span class="kw-chip" :class="p.role === 'admin' ? 'kw-chip--green' : ''" x-text="p.role === 'admin' ? 'Admin' : 'Member'"></span>
          </div>
        </template>
      </div>
      <div class="kw-modal__foot">
        <button type="button" class="kw-btn kw-btn--ghost" @click="showGroupRosterModal = false">Close</button>
      </div>
    </div>
  </div>

  {{-- Active In-App Video / Voice Call Overlay --}}
  <div class="kw-call-overlay" x-show="isInCall" x-cloak>
    <div class="kw-call-overlay__head">
      <div>
        <h3 class="kw-call-overlay__title" x-text="callPartnerName || 'Family Kin Call'"></h3>
        <div class="kw-call-overlay__status">
          <i class="bi bi-record-circle" :style="isCallRinging ? 'color:#ffd700;' : 'color:#22c55e;'"></i>
          <span x-text="isCallRinging ? 'Calling family member…' : ('Connected • ' + formatCallDuration(callDurationSeconds))"></span>
        </div>
      </div>
      <button type="button" class="kw-call-ctrl-btn kw-call-ctrl-btn--end" @click="endCall()" aria-label="End call" title="End call">
        <i class="bi bi-telephone-x-fill"></i>
      </button>
    </div>

    <div class="kw-call-overlay__body">
      {{-- Video Stream View --}}
      <template x-if="callType === 'video'">
        <div class="kw-remote-video-wrap">
          <video id="kwRemoteVideo" class="kw-remote-video" autoplay playsinline></video>
          <div class="kw-local-video-pip">
            <video id="kwLocalVideo" class="kw-local-video" autoplay playsinline muted></video>
          </div>
        </div>
      </template>

      {{-- Audio-Only Wave View --}}
      <template x-if="callType === 'audio'">
        <div class="kw-voice-call-avatar">
          <div class="kw-voice-avatar-ring">
            <img :src="partnerAvatar(activeThread)" alt="" style="width:100%; height:100%; object-fit:cover;" x-on:error="$el.remove()">
          </div>
          <h2 style="font-size:1.5rem; font-weight:800; margin:0;" x-text="callPartnerName"></h2>
          <p style="color:#cbd5e1; margin:0;"><i class="bi bi-soundwave"></i> Encrypted In-App Family Voice Call</p>
        </div>
      </template>
    </div>

    <div class="kw-call-overlay__controls">
      <button type="button" class="kw-call-ctrl-btn" :class="{'is-active': isMicMuted}" @click="toggleMuteAudio()" :title="isMicMuted ? 'Unmute microphone' : 'Mute microphone'">
        <i class="bi" :class="isMicMuted ? 'bi-mic-mute-fill' : 'bi-mic-fill'"></i>
      </button>

      <template x-if="callType === 'video'">
        <button type="button" class="kw-call-ctrl-btn" :class="{'is-active': isCameraOff}" @click="toggleMuteVideo()" :title="isCameraOff ? 'Turn camera on' : 'Turn camera off'">
          <i class="bi" :class="isCameraOff ? 'bi-camera-video-off-fill' : 'bi-camera-video-fill'"></i>
        </button>
      </template>

      <button type="button" class="kw-call-ctrl-btn kw-call-ctrl-btn--end" @click="endCall()" aria-label="End Call" title="End call">
        <i class="bi bi-telephone-x-fill"></i>
      </button>
    </div>
  </div>

  {{-- Incoming Call Ringing Banner --}}
  <div class="kw-incoming-ring" x-show="incomingCall !== null" x-cloak role="alert" aria-live="assertive">
    <div class="kw-avatar kw-avatar--sm" style="border-color:#ffd700;">
      <span x-text="initials(incomingCall?.caller_name)"></span>
      <img :src="resolveAvatar(incomingCall?.caller_avatar)" alt="" x-on:error="$el.remove()">
    </div>
    <div class="kw-incoming-ring__info">
      <h4 class="kw-incoming-ring__name" x-text="incomingCall?.caller_name"></h4>
      <p class="kw-incoming-ring__type">
        <i class="bi" :class="incomingCall?.call_type === 'video' ? 'bi-camera-video-fill' : 'bi-telephone-fill'"></i>
        <span x-text="'Incoming Family ' + (incomingCall?.call_type === 'video' ? 'Video' : 'Voice') + ' Call…'"></span>
      </p>
    </div>
    <div class="kw-incoming-ring__actions">
      <button type="button" class="kw-incoming-btn kw-incoming-btn--accept" @click="acceptCall()" aria-label="Accept call" title="Accept call">
        <i class="bi bi-telephone-fill"></i>
      </button>
      <button type="button" class="kw-incoming-btn kw-incoming-btn--decline" @click="declineCall()" aria-label="Decline call" title="Decline call">
        <i class="bi bi-telephone-x-fill"></i>
      </button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.2.0/dist/web/pusher.min.js"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
function kinWhisperApp() {
  const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  const jsonHeaders = () => ({ 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() });
  const LABELS = {
    catch_up: 'Catch-up',
    post: 'Family post',
    photo: 'Photograph',
    memory: 'Memory',
    family_node: 'Family tree',
    event: 'Family event',
    milestone: 'Milestone',
    recipe: 'Family recipe',
    prayer: 'Prayer & blessing',
    advice: 'Family wisdom',
    general: 'General dedication'
  };

  const ICONS = {
    catch_up: 'bi-cup-hot-fill',
    post: 'bi-card-text',
    photo: 'bi-image-fill',
    memory: 'bi-journal-bookmark-fill',
    family_node: 'bi-diagram-3-fill',
    event: 'bi-calendar-event-fill',
    milestone: 'bi-trophy-fill',
    recipe: 'bi-egg-fried',
    prayer: 'bi-heart-pulse-fill',
    advice: 'bi-lightbulb-fill',
    general: 'bi-pin-angle-fill'
  };

  return {
    currentUserId: @json((string)$current_user_id),
    threads: @json($threads ?? []),
    activeThread: @json($selected_thread ?? null),
    messages: @json($messages ?? []),
    kin: @json($kin ?? []),
    pane: @json(!empty($selected_thread) && !empty($_GET['thread_id']) ? 'chat' : 'list'),
    searchQuery: '',
    newMessageBody: '',
    isSubmitting: false,
    isCreating: false,
    composeMode: 'direct',
    newGroupTitle: '',
    selectedGroupMembers: [],
    showCallModal: false,
    showGroupRosterModal: false,
    showShareRequestModal: false,
    isRequestingShare: false,
    isRespondingShare: false,
    pendingDeleteMsg: null,
    isDeletingMsg: false,
    threadEvents: [],
    lastScreenshotTime: 0,
    candidateBuffer: [],
    candidateFlushTimer: null,
    isInCall: false,
    isCallRinging: false,
    callType: 'video',
    callRoomId: null,
    callPartnerName: '',
    callDurationSeconds: 0,
    callDurationTimer: null,
    isMicMuted: false,
    isCameraOff: false,
    localStream: null,
    remoteStream: null,
    peerConnection: null,
    incomingCall: null,
    audioCtx: null,
    ringtoneInterval: null,
    pusher: null,
    userChannel: null,
    isRecording: false,
    mediaRecorder: null,
    audioChunks: [],
    recordedAudioUrl: null,
    recordedAudioBlob: null,
    recordingSeconds: 0,
    recordingTimer: null,
    recordingStatusText: 'Ready',
    showCompose: false,
    newRecipientId: '',
    newContextType: 'catch_up',
    newContextTitle: '',
    kinSearchQuery: '',
    isSearchingKin: false,
    omniResults: null,
    selectedKinMember: null,
    disappearOptions: [
      { seconds: 0, label: 'Off (Keep indefinitely)' },
      { seconds: 30, label: '30 seconds' },
      { seconds: 60, label: '1 minute' },
      { seconds: 1800, label: '30 minutes' },
      { seconds: 3600, label: '1 hour' },
      { seconds: 86400, label: '24 hours' }
    ],
    activeDisappearSeconds: 0,
    showDisappearMenu: false,
    allowedReactions: [
      '❤️', '🙏', '🥰', '🎉', '👏', '😂', '👍', '🎂', '🕊️', '😮',
      'family_roots_3d', 'ancestor_blessing_3d', 'family_hug_3d', 'ancestor_crown_3d', 'kinship_pride_3d'
    ],
    quickEmojis: [
      'family_roots_3d', 'ancestor_blessing_3d', '❤️', '🙏', '🥰', '🎉', '🎂', '👏', '👴', '👵', '👶', '🕊️', '🌟', '🏠'
    ],
    activeReactionMsgId: null,
    reactionPopoverPosition: 'above',
    showEmojiDrawer: false,
    activeEmojiCategory: 'atg_3d',
    emojiCategories: [
      {
        id: 'atg_3d',
        name: '✨ 3D Heritage',
        emojis: [
          'family_roots_3d', 'ancestor_blessing_3d', 'family_crest_3d', 'family_hearth_3d',
          'family_hug_3d', 'ancestor_crown_3d', 'kinship_pride_3d', 'memory_capsule_3d',
          'wisdom_scroll_3d', 'family_feast_3d', 'generations_link_3d', 'family_celebrate_3d',
          'baby_cradle_3d', 'wedding_vows_3d', 'family_house_3d', 'family_toast_3d',
          'family_wave_3d', 'family_heart_hands_3d', 'family_clap_3d', 'laugh_tears_3d', 'star_struck_3d'
        ]
      },
      { id: 'family', name: 'Family & Love', emojis: ['❤️', '🥰', '💕', '💖', '🫂', '👨‍👩‍👧‍👦', '👴', '👵', '👶', '🤍', '💞', '🌸'] },
      { id: 'blessings', name: 'Blessings', emojis: ['🙏', '✨', '🕊️', '🌟', '🤲', '💐', '🙌', '⛪', '🕯️', '👑', '🌈', '☀️'] },
      { id: 'celebration', name: 'Celebration', emojis: ['🎉', '🎂', '🎊', '🥂', '👏', '🎈', '🥳', '🎁', '🍰', '🏆', '🎵', '🎺'] },
      { id: 'smiles', name: 'Smiles & Warmth', emojis: ['😊', '😂', '😍', '🤗', '👍', '💯', '🏠', '💌', '☕', '🌟', '🌻', '🥧'] }
    ],
    serverNowIso: @json($server_now_iso ?? gmdate('Y-m-d\TH:i:s\Z')),
    nowTs: Date.now(),
    serverClockOffset: 0,
    tickCounter: 0,
    pollTimer: null,
    countdownTimer: null,

    init() {
      if (this.serverNowIso) {
        const sTs = this.parseUtcTimestamp(this.serverNowIso);
        if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
      }
      this.scrollToBottom();
      this.startPolling();
      this.startCountdownLoop();
      this.initPusher();
      this.initScreenshotDetection();
      if (this.activeThread) {
        this.fetchThreadEvents();
        window.dispatchEvent(new CustomEvent('kin-whispers:read'));
      }
    },

    parseUtcTimestamp(str) {
      if (!str) return 0;
      let s = String(str).trim();
      if (!s.endsWith('Z') && !/[+-]\d{2}:?\d{2}$/.test(s)) {
        s = s.replace(' ', 'T') + 'Z';
      }
      const ts = new Date(s).getTime();
      return isNaN(ts) ? 0 : ts;
    },

    startCountdownLoop() {
      if (this.countdownTimer) clearInterval(this.countdownTimer);
      this.countdownTimer = setInterval(() => {
        this.nowTs = Date.now();
        this.tickCounter++;
        if (!this.messages || this.messages.length === 0) return;
        const currentTs = this.nowTs - this.serverClockOffset;
        const beforeLen = this.messages.length;
        this.messages = this.messages.filter(m => {
          if (!m.expires_at) return true;
          const target = this.parseUtcTimestamp(m.expires_at_iso || m.expires_at);
          return isNaN(target) || target > currentTs;
        });
        if (this.messages.length !== beforeLen && this.activeThread) {
          const lastMsg = this.messages[this.messages.length - 1];
          this.activeThread.last_message_preview = lastMsg ? (lastMsg.body || 'Voice whisper') : 'No messages';
        }
      }, 1000);
    },

    get currentCategoryEmojis() {
      const cat = this.emojiCategories.find(c => c.id === this.activeEmojiCategory);
      return cat ? cat.emojis : [];
    },

    get preview3dEmojis() {
      return [];
    },

    formatEmojiLabel(emo) {
      return (emo || '').replace(/_3d$/, '').replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase());
    },

    hasReactions(msg) {
      if (!msg || !msg.context_metadata || !msg.context_metadata.reactions) return false;
      return Object.keys(msg.context_metadata.reactions).length > 0;
    },

    create3dEmojiNode(emojiId) {
      const img = document.createElement('img');
      img.src = '/img/emoji/' + emojiId + '.svg';
      img.className = 'atg-3d-emoji kw-inline-3d-emoji';
      img.setAttribute('data-emoji', ':' + emojiId + ':');
      img.alt = ':' + emojiId + ':';
      img.width = 24;
      img.height = 24;
      img.onerror = function() { this.src = '/img/emoji/' + emojiId + '.png'; };
      return img;
    },

    upgradeInlineEmojiTokens(el) {
      if (!el) return;
      const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
      const nodesToReplace = [];
      let current;
      while ((current = walker.nextNode())) {
        if (/:([a-z0-9_]+_3d):/.test(current.nodeValue || '')) {
          nodesToReplace.push(current);
        }
      }
      nodesToReplace.forEach(textNode => {
        const parts = (textNode.nodeValue || '').split(/(:[a-z0-9_]+_3d:)/g);
        const frag = document.createDocumentFragment();
        parts.forEach(part => {
          const m = part.match(/^:([a-z0-9_]+_3d):$/);
          if (m) {
            frag.appendChild(this.create3dEmojiNode(m[1]));
            frag.appendChild(document.createTextNode('\u00A0'));
          } else if (part) {
            frag.appendChild(document.createTextNode(part));
          }
        });
        textNode.replaceWith(frag);
      });
    },

    getComposerText() {
      const el = document.getElementById('kw_body');
      if (!el) return this.newMessageBody || '';
      const clone = el.cloneNode(true);
      clone.querySelectorAll('img[data-emoji]').forEach(img => {
        const token = img.getAttribute('data-emoji') || '';
        img.replaceWith(document.createTextNode(' ' + token + ' '));
      });
      clone.querySelectorAll('br').forEach(br => br.replaceWith(document.createTextNode('\n')));
      return (clone.textContent || '').replace(/\u00A0/g, ' ').replace(/[ \t]{2,}/g, ' ').trim();
    },

    onComposerInput() {
      const el = document.getElementById('kw_body');
      if (el) {
        this.upgradeInlineEmojiTokens(el);
        if (!el.textContent.trim() && el.querySelectorAll('img[data-emoji]').length === 0) {
          el.innerHTML = '';
        }
      }
      this.newMessageBody = this.getComposerText();
    },

    clearComposer() {
      this.newMessageBody = '';
      const el = document.getElementById('kw_body');
      if (el) el.innerHTML = '';
    },

    insertEmoji(emoji) {
      const el = document.getElementById('kw_body');
      if (!el) {
        const val = emoji.endsWith('_3d') ? (':' + emoji + ':') : emoji;
        this.newMessageBody = (this.newMessageBody ? this.newMessageBody + ' ' : '') + val;
        return;
      }
      el.focus();
      if (emoji.endsWith('_3d')) {
        const img = this.create3dEmojiNode(emoji);
        const space = document.createTextNode('\u00A0');
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0 && el.contains(sel.anchorNode)) {
          const range = sel.getRangeAt(0);
          range.deleteContents();
          range.insertNode(space);
          range.insertNode(img);
          range.setStartAfter(space);
          range.setEndAfter(space);
          sel.removeAllRanges();
          sel.addRange(range);
        } else {
          el.appendChild(img);
          el.appendChild(space);
        }
      } else {
        const textNode = document.createTextNode((el.textContent ? ' ' : '') + emoji + ' ');
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0 && el.contains(sel.anchorNode)) {
          const range = sel.getRangeAt(0);
          range.deleteContents();
          range.insertNode(textNode);
          range.setStartAfter(textNode);
          range.setEndAfter(textNode);
          sel.removeAllRanges();
          sel.addRange(range);
        } else {
          el.appendChild(textNode);
        }
      }
      this.newMessageBody = this.getComposerText();
    },

    disappearLabel(sec) {
      const opt = this.disappearOptions.find(o => o.seconds === sec);
      return opt ? opt.label : 'Off';
    },

    disappearShort(sec) {
      if (sec === 30) return '30s';
      if (sec === 60) return '1m';
      if (sec === 1800) return '30m';
      if (sec === 3600) return '1h';
      if (sec === 86400) return '24h';
      return '';
    },

    formatTimeLeft(msg, now) {
      if (!msg || !msg.expires_at) return '';
      const expireTs = this.parseUtcTimestamp(msg.expires_at_iso || msg.expires_at);
      if (!expireTs) return '';
      const currentTs = (now || this.nowTs) - this.serverClockOffset;
      const diffSec = Math.max(0, Math.floor((expireTs - currentTs) / 1000));
      if (diffSec <= 0) return 'Expiring…';
      if (diffSec < 60) return diffSec + 's';
      if (diffSec < 3600) {
        const m = Math.floor(diffSec / 60);
        const s = diffSec % 60;
        return m + 'm' + (s > 0 ? (' ' + s + 's') : '');
      }
      const h = Math.floor(diffSec / 3600);
      const m = Math.floor((diffSec % 3600) / 60);
      return h + 'h' + (m > 0 ? (' ' + m + 'm') : '');
    },

    renderMessageBody(text) {
      if (!text) return '';
      const div = document.createElement('div');
      div.textContent = text;
      let safe = div.innerHTML;

      safe = safe.replace(/:([a-z0-9_]+_3d):/g, (match, p1) => {
        return `<img src="/img/emoji/${p1}.svg" class="atg-3d-emoji" width="28" height="28" alt="${p1}" onerror="this.src='/img/emoji/${p1}.png'" style="vertical-align:middle; display:inline-block; margin:0 2px;">`;
      });

      safe = safe.replace(/:me_([a-z]+)@([a-zA-Z0-9_-]+):/g, (match, r, uid) => {
        return `<img src="/api/emoji/avatar-sticker/${encodeURIComponent(uid)}/${encodeURIComponent(r)}" class="atg-3d-emoji" width="48" height="48" alt="Me sticker" style="vertical-align:middle; display:inline-block; margin:2px;">`;
      });

      safe = safe.replace(/\n/g, '<br>');
      return safe;
    },

    formatSeenLabel(readAt) {
      if (!readAt) return '';
      const d = new Date(String(readAt).replace(' ', 'T'));
      if (isNaN(d.getTime())) return '';
      return d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    },

    formatSeenTime(readAt) {
      if (!readAt) return '';
      const d = new Date(String(readAt).replace(' ', 'T'));
      if (isNaN(d.getTime())) return readAt;
      return d.toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });
    },

    openReactionPopover(msgId, evt) {
      if (this.activeReactionMsgId === msgId) {
        this.activeReactionMsgId = null;
        return;
      }
      this.activeReactionMsgId = msgId;
      const trigger = evt?.currentTarget;
      if (trigger) {
        const tRect = trigger.getBoundingClientRect();
        const stream = document.getElementById('kwStream');
        const sRect = stream ? stream.getBoundingClientRect() : null;
        if (sRect && (tRect.top - sRect.top < 180)) {
          this.reactionPopoverPosition = 'below';
        } else {
          this.reactionPopoverPosition = 'above';
        }
      } else {
        this.reactionPopoverPosition = 'above';
      }
    },

    formatEmojiTitle(emo) {
      return (emo || '').replace(/_3d$/, '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    },

    async toggleReaction(msgId, emoji) {
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(msgId) + '/reaction', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ emoji })
        });
        const data = await res.json();
        if (data.success && data.message) {
          const idx = this.messages.findIndex(m => m.id === msgId);
          if (idx > -1) {
            this.messages[idx].context_metadata = data.message.context_metadata;
          }
        } else {
          this.toast(data.error || 'Could not update reaction.');
        }
      } catch (e) {
        console.error('Reaction toggle error', e);
      }
    },

    confirmDeleteMessage(msg) {
      if (!msg || msg.sender_id !== this.currentUserId) return;
      this.activeReactionMsgId = null;
      this.pendingDeleteMsg = msg;
    },

    cancelDeleteMessage() {
      if (this.isDeletingMsg) return;
      this.pendingDeleteMsg = null;
    },

    applyDeletedMessageState(threadId, messageId, lastPreview, lastAt) {
      if (this.activeThread && this.activeThread.id === threadId) {
        this.messages = this.messages.filter(m => m.id !== messageId);
        if (lastPreview !== undefined) this.activeThread.last_message_preview = lastPreview;
        if (lastAt) this.activeThread.last_message_at = lastAt;
      }
      const tIdx = this.threads.findIndex(t => t.id === threadId);
      if (tIdx > -1) {
        if (lastPreview !== undefined) this.threads[tIdx].last_message_preview = lastPreview;
        if (lastAt) this.threads[tIdx].last_message_at = lastAt;
      }
    },

    async executeDeleteMessage() {
      if (!this.pendingDeleteMsg || this.isDeletingMsg) return;
      const targetMsg = this.pendingDeleteMsg;
      this.isDeletingMsg = true;
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(targetMsg.id), {
          method: 'DELETE',
          headers: jsonHeaders(),
          body: JSON.stringify({ token: csrf() })
        });
        const data = await res.json();
        if (data.success) {
          this.applyDeletedMessageState(
            data.thread_id || targetMsg.thread_id,
            data.deleted_message_id || targetMsg.id,
            data.last_message_preview,
            data.last_message_at
          );
          this.pendingDeleteMsg = null;
          this.toast('Whisper permanently deleted.');
        } else {
          this.toast(data.error || 'Could not delete whisper.');
        }
      } catch (e) {
        console.error('Delete message error', e);
        this.toast('Network error while deleting whisper.');
      } finally {
        this.isDeletingMsg = false;
      }
    },

    startPolling() {
      if (this.pollTimer) clearInterval(this.pollTimer);
      this.pollTimer = setInterval(() => this.pollUpdates(), 4000);
      window.addEventListener('focus', () => this.pollUpdates());
    },

    async pollUpdates() {
      if (!this.activeThread || document.hidden) return;
      const pollingThreadId = this.activeThread.id;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(pollingThreadId), {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success && data.messages && this.activeThread && this.activeThread.id === pollingThreadId) {
          if (data.server_time) {
            const sTs = this.parseUtcTimestamp(data.server_time);
            if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
          }
          const currentTs = this.nowTs - this.serverClockOffset;
          const validMessages = data.messages.filter(m => {
            if (!m.expires_at) return true;
            const target = this.parseUtcTimestamp(m.expires_at_iso || m.expires_at);
            return isNaN(target) || target > currentTs;
          });
          const hadNewMessages = validMessages.length > this.messages.length;
          this.messages = validMessages;
          if (data.thread) {
            this.activeThread.last_message_preview = data.thread.last_message_preview;
            this.activeThread.last_message_at = data.thread.last_message_at;
            this.activeThread.share_status = data.thread.share_status;
            this.activeThread.share_requested_by = data.thread.share_requested_by;
            this.activeThread.share_approved_by = data.thread.share_approved_by;
          }
          if (hadNewMessages) {
            this.scrollToBottom();
            window.dispatchEvent(new CustomEvent('kin-whispers:read'));
          }
        }
      } catch (e) {
        // silent polling failure
      }
    },

    get filteredThreads() {
      const q = this.searchQuery.trim().toLowerCase();
      if (!q) return this.threads;
      return this.threads.filter(t => this.partnerName(t).toLowerCase().includes(q) || (t.context_title || '').toLowerCase().includes(q));
    },

    resolveAvatar(raw) {
      const fallback = '/resources/images/profile/avatarM.png';
      if (!raw) return fallback;
      const s = String(raw).trim();
      if (s === '' || s.includes('avatarM.jpeg') || s.includes('avatarM.png') || s.includes('avatarF.png') || s.includes('default-avatar.png')) {
        return fallback;
      }
      if (s.startsWith('http://') || s.startsWith('https://')) return s;
      if (s.startsWith('/resources/images/profile/')) return s;
      const parts = s.split('/');
      return '/resources/images/profile/' + parts[parts.length - 1];
    },

    partnerName(t) {
      t = t || this.activeThread;
      if (!t) return 'Family Kin';
      if (t.thread_type === 'group') return t.title || 'Family Group';
      return t.sender_id === this.currentUserId ? t.recipient_name : t.sender_name;
    },
    partnerAvatar(t) {
      const fallback = '/resources/images/profile/avatarM.png';
      if (!t) return fallback;
      if (t.thread_type === 'group') return fallback;
      const raw = (t.sender_id === this.currentUserId) ? t.recipient_avatar : t.sender_avatar;
      return this.resolveAvatar(raw);
    },
    labelFor(type) { return LABELS[type] || 'Whisper'; },
    iconFor(type) { return ICONS[type] || 'bi-pin-angle-fill'; },
    initials(name) { return (name || '?').trim().split(/\s+/).slice(0, 2).map(p => p[0]).join('').toUpperCase(); },
    formatSeconds(s) { return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); },
    formatShort(str) {
      if (!str) return '';
      const d = new Date(String(str).replace(' ', 'T'));
      if (isNaN(d.getTime())) return str;
      const now = new Date();
      return d.toDateString() === now.toDateString()
        ? d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
        : d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
    },
    scrollToBottom() {
      this.$nextTick(() => { const s = document.getElementById('kwStream'); if (s) s.scrollTop = s.scrollHeight; });
    },
    get displayedKin() {
      if (this.omniResults !== null) return this.omniResults;
      const q = this.kinSearchQuery.trim().toLowerCase();
      if (!q) return this.kin;
      return this.kin.filter(k => (k.name || '').toLowerCase().includes(q) || (k.famCode || '').toLowerCase().includes(q));
    },

    async performKinSearch() {
      const q = this.kinSearchQuery.trim();
      if (!q) {
        this.omniResults = null;
        return;
      }
      this.isSearchingKin = true;
      try {
        const res = await fetch('/api/omni-search?q=' + encodeURIComponent(q), {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.status === 'success' && data.data && data.data.categories && Array.isArray(data.data.categories.members)) {
          this.omniResults = data.data.categories.members
            .filter(m => String(m.id) !== String(this.currentUserId))
            .map(m => ({
              id: String(m.id),
              name: m.title || 'Family Kin',
              avatar: this.resolveAvatar(m.avatar),
              famCode: (m.badge || '').replace('#', ''),
              description: m.description || ''
            }));
        } else {
          this.omniResults = null;
        }
      } catch (e) {
        this.omniResults = null;
      } finally {
        this.isSearchingKin = false;
      }
    },

    selectRecipient(k) {
      this.newRecipientId = k.id;
      this.selectedKinMember = k;
    },

    clearSelectedKin() {
      this.newRecipientId = '';
      this.selectedKinMember = null;
      this.kinSearchQuery = '';
      this.omniResults = null;
    },

    openCompose() {
      this.clearSelectedKin();
      this.showCompose = true;
    },

    async selectThread(thread) {
      if (!thread) return;
      this.activeThread = thread;
      this.messages = [];
      this.threadEvents = [];
      this.pane = 'chat';
      this.clearComposer();
      this.discardRecordedAudio();
      try {
        if (window.history && window.history.replaceState) {
          window.history.replaceState({}, '', '/kin-whispers?thread_id=' + encodeURIComponent(thread.id));
        }
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(thread.id), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && this.activeThread && this.activeThread.id === data.thread.id) {
          if (data.server_time) {
            const sTs = this.parseUtcTimestamp(data.server_time);
            if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
          }
          this.activeThread = data.thread;
          const currentTs = this.nowTs - this.serverClockOffset;
          this.messages = (data.messages || []).filter(m => {
            if (!m.expires_at) return true;
            const target = this.parseUtcTimestamp(m.expires_at_iso || m.expires_at);
            return isNaN(target) || target > currentTs;
          });
          const i = this.threads.findIndex(t => t.id === thread.id);
          if (i > -1) {
            this.threads[i] = data.thread;
            this.threads[i].unread_count = 0;
          }
          this.fetchThreadEvents();
          this.scrollToBottom();
          window.dispatchEvent(new CustomEvent('kin-whispers:read'));
        }
      } catch (e) {
        console.error('Failed to load whisper thread', e);
      }
    },

    async toggleAudioRecording() { this.isRecording ? this.stopAudioRecording() : await this.startAudioRecording(); },

    async startAudioRecording() {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === 'undefined') {
        this.toast('Voice recording is not supported in this browser.'); return;
      }
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        this.mediaRecorder = new MediaRecorder(stream);
        this.audioChunks = [];
        this.recordingSeconds = 0;
        this.recordingStatusText = 'Recording';
        this.mediaRecorder.ondataavailable = e => { if (e.data.size > 0) this.audioChunks.push(e.data); };
        this.mediaRecorder.onstop = () => {
          this.recordedAudioBlob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType || 'audio/webm' });
          this.recordedAudioUrl = URL.createObjectURL(this.recordedAudioBlob);
          this.recordingStatusText = 'Recorded';
          clearInterval(this.recordingTimer);
          this.isRecording = false;
        };
        this.mediaRecorder.start();
        this.isRecording = true;
        this.recordingTimer = setInterval(() => {
          this.recordingSeconds++;
          if (this.recordingSeconds >= 60) this.stopAudioRecording();
        }, 1000);
      } catch (e) { this.toast('Microphone access is needed to record a whisper.'); }
    },

    stopAudioRecording() {
      if (this.mediaRecorder && this.isRecording) {
        this.mediaRecorder.stop();
        if (this.mediaRecorder.stream) this.mediaRecorder.stream.getTracks().forEach(t => t.stop());
      }
    },

    discardRecordedAudio() {
      this.stopAudioRecording();
      clearInterval(this.recordingTimer);
      this.recordedAudioUrl = null;
      this.recordedAudioBlob = null;
      this.recordingSeconds = 0;
      this.isRecording = false;
    },

    async sendMessage() {
      if (!this.activeThread || this.isSubmitting) return;
      this.newMessageBody = this.getComposerText();
      if (!this.newMessageBody.trim() && !this.recordedAudioBlob) return;
      this.isSubmitting = true;
      let audioUrl = null, duration = null;
      try {
        if (this.recordedAudioBlob) {
          const fd = new FormData();
          fd.append('audio', this.recordedAudioBlob, 'whisper.webm');
          const up = await (await fetch('/api/kin-whispers/audio-upload', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf() }, body: fd })).json();
          if (!up.success) { this.toast(up.error || 'Voice upload failed.'); return; }
          audioUrl = up.audio.url;
          duration = Math.max(1, Math.min(60, this.recordingSeconds || up.audio.duration));
        }
        const sendingThreadId = this.activeThread.id;
        const res = await fetch('/api/kin-whispers/messages', {
          method: 'POST', headers: jsonHeaders(),
          body: JSON.stringify({
            thread_id: sendingThreadId, body: this.newMessageBody,
            message_type: audioUrl ? 'audio_whisper' : 'text', audio_url: audioUrl, audio_duration: duration,
            disappear_seconds: this.activeDisappearSeconds > 0 ? this.activeDisappearSeconds : null
          })
        });
        const data = await res.json();
        if (data.success && data.message) {
          if (data.server_time) {
            const sTs = this.parseUtcTimestamp(data.server_time);
            if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
          }
          if (this.activeThread && this.activeThread.id === data.message.thread_id) {
            this.messages.push(data.message);
            this.activeThread.last_message_preview = data.message.body || 'Voice whisper';
            this.activeThread.last_message_at = data.message.created_at;
          }
          const tIdx = this.threads.findIndex(t => t.id === data.message.thread_id);
          if (tIdx > -1) {
            this.threads[tIdx].last_message_preview = data.message.body || 'Voice whisper';
            this.threads[tIdx].last_message_at = data.message.created_at;
          }
          this.clearComposer();
          this.discardRecordedAudio();
          this.scrollToBottom();
        } else { this.toast(data.error || 'Could not send whisper.'); }
      } catch (e) { this.toast('Network error. Please try again.'); }
      finally { this.isSubmitting = false; }
    },

    shareBtnLabel() {
      if (!this.activeThread) return 'Share';
      if (this.activeThread.share_status === 'approved') return 'Share';
      if (this.activeThread.share_status === 'pending') return 'Pending';
      return 'Share';
    },

    shareBtnTooltip() {
      if (!this.activeThread) return 'Share on WhatsApp';
      if (this.activeThread.share_status === 'approved') {
        return 'Sharing is authorised by both kin — click to share on WhatsApp';
      }
      if (this.activeThread.share_status === 'pending') {
        return this.activeThread.share_requested_by === this.currentUserId
          ? 'Awaiting approval from ' + this.partnerName(this.activeThread) + ' before sharing'
          : this.partnerName(this.activeThread) + ' requested to share — review consent banner below';
      }
      return 'Request recipient approval to share on WhatsApp';
    },

    handleShareClick() {
      if (!this.activeThread) return;
      if (this.activeThread.share_status === 'approved') {
        this.openWhatsApp();
        return;
      }
      if (this.activeThread.share_status === 'pending') {
        if (this.activeThread.share_requested_by === this.currentUserId) {
          this.toast('Awaiting approval from ' + this.partnerName(this.activeThread) + ' before this whisper can be shared on WhatsApp.');
        } else {
          const stream = document.getElementById('kwStream');
          if (stream) stream.scrollTop = 0;
          this.toast('Please authorise or decline the sharing request at the top of the conversation.');
        }
        return;
      }
      this.showShareRequestModal = true;
    },

    async submitShareRequest() {
      if (!this.activeThread || this.isRequestingShare) return;
      this.isRequestingShare = true;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/share/request', {
          method: 'POST',
          headers: jsonHeaders()
        });
        const data = await res.json();
        if (data.success && data.thread) {
          this.activeThread = data.thread;
          const idx = this.threads.findIndex(t => t.id === data.thread.id);
          if (idx > -1) this.threads[idx] = data.thread;
          this.showShareRequestModal = false;
          this.toast(data.message || ('Notification sent to ' + this.partnerName(this.activeThread) + ' to authorise WhatsApp sharing.'));
        } else {
          this.toast(data.error || 'Could not request sharing approval.');
        }
      } catch (e) {
        this.toast('Network error while requesting share approval.');
      } finally {
        this.isRequestingShare = false;
      }
    },

    async respondToShareRequest(action) {
      if (!this.activeThread || this.isRespondingShare) return;
      this.isRespondingShare = true;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/share/respond', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ action })
        });
        const data = await res.json();
        if (data.success && data.thread) {
          this.activeThread = data.thread;
          const idx = this.threads.findIndex(t => t.id === data.thread.id);
          if (idx > -1) this.threads[idx] = data.thread;
          this.toast(data.message || 'Sharing preference updated.');
        } else {
          this.toast(data.error || 'Could not update sharing consent.');
        }
      } catch (e) {
        this.toast('Network error while updating sharing consent.');
      } finally {
        this.isRespondingShare = false;
      }
    },

    async openWhatsApp() {
      if (!this.activeThread) return;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/whatsapp', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && data.whatsapp_url) {
          window.open(data.whatsapp_url, '_blank', 'noopener,noreferrer');
        } else if (data.requires_approval) {
          this.showShareRequestModal = true;
        } else {
          this.toast(data.error || 'Could not open WhatsApp.');
        }
      } catch (e) { this.toast('Could not open WhatsApp.'); }
    },

    initScreenshotDetection() {
      const checkKey = (e) => {
        if (!this.activeThread) return;
        const isPrintScreen = e.key === 'PrintScreen' || e.keyCode === 44;
        const isMacShot = (e.metaKey || e.ctrlKey) && e.shiftKey && ['3', '4', '5'].includes(String(e.key));
        const isWinSnip = e.metaKey && e.shiftKey && (e.key === 's' || e.key === 'S');
        if (isPrintScreen || isMacShot || isWinSnip) {
          this.recordScreenshot();
        }
      };
      window.addEventListener('keyup', checkKey);
      window.addEventListener('keydown', (e) => {
        if (!this.activeThread) return;
        const isMacShot = (e.metaKey || e.ctrlKey) && e.shiftKey && ['3', '4', '5'].includes(String(e.key));
        if (isMacShot) {
          this.recordScreenshot();
        }
      });
    },

    async recordScreenshot() {
      if (!this.activeThread) return;
      const now = Date.now();
      if (now - this.lastScreenshotTime < 4000) return;
      this.lastScreenshotTime = now;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/screenshot', {
          method: 'POST',
          headers: jsonHeaders()
        });
        const data = await res.json();
        if (data.success && data.event && !data.throttled) {
          this.threadEvents.push(data.event);
          this.toast('📸 Screenshot recorded by Kin Whispers and notified to ' + this.partnerName(this.activeThread) + '.');
        }
      } catch (e) {
        console.warn('Screenshot audit log error', e);
      }
    },

    async fetchThreadEvents() {
      if (!this.activeThread) return;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/events', {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success && Array.isArray(data.events)) {
          this.threadEvents = data.events;
        }
      } catch (e) {
        console.warn('Failed to fetch thread audit events', e);
      }
    },

    async createNewThread() {
      if (!this.newRecipientId || this.isCreating) return;
      this.isCreating = true;
      try {
        const data = await (await fetch('/api/kin-whispers/threads', {
          method: 'POST', headers: jsonHeaders(),
          body: JSON.stringify({ recipient_id: this.newRecipientId, context_type: this.newContextType, context_title: this.newContextTitle })
        })).json();
        if (data.success && data.thread) {
          this.showCompose = false;
          const existingIdx = this.threads.findIndex(t => t.id === data.thread.id);
          if (existingIdx === -1) {
            this.threads.unshift(data.thread);
          } else {
            this.threads[existingIdx] = data.thread;
          }
          this.clearSelectedKin();
          this.newContextTitle = '';
          this.newContextType = 'catch_up';
          await this.selectThread(data.thread);
        } else { this.toast(data.error || 'Could not start whisper.'); }
      } catch (e) { this.toast('Network error. Please try again.'); }
      finally { this.isCreating = false; }
    },

    isMemberSelected(id) {
      return this.selectedGroupMembers.some(m => String(m.id) === String(id));
    },

    toggleGroupMember(k) {
      const idx = this.selectedGroupMembers.findIndex(m => String(m.id) === String(k.id));
      if (idx > -1) {
        this.selectedGroupMembers.splice(idx, 1);
      } else {
        this.selectedGroupMembers.push(k);
      }
    },

    async createGroupThread() {
      if (this.selectedGroupMembers.length < 1 || !this.newGroupTitle.trim() || this.isCreating) return;
      this.isCreating = true;
      try {
        const participantIds = this.selectedGroupMembers.map(m => m.id);
        const res = await fetch('/api/kin-whispers/groups', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            title: this.newGroupTitle.trim(),
            participant_ids: participantIds,
            context_type: this.newContextType,
            context_title: this.newContextTitle
          })
        });
        const data = await res.json();
        if (data.success && data.thread) {
          this.showCompose = false;
          this.threads.unshift(data.thread);
          this.newGroupTitle = '';
          this.selectedGroupMembers = [];
          this.newContextTitle = '';
          await this.selectThread(data.thread);
        } else {
          this.toast(data.error || 'Could not create group whisper.');
        }
      } catch (e) {
        this.toast('Network error. Please try again.');
      } finally {
        this.isCreating = false;
      }
    },

    partnerMobileNumber() {
      if (!this.activeThread || this.activeThread.thread_type === 'group') return null;
      const partnerId = this.activeThread.sender_id === this.currentUserId ? this.activeThread.recipient_id : this.activeThread.sender_id;
      const kinObj = this.kin.find(k => String(k.id) === String(partnerId));
      return (kinObj && kinObj.mobile) ? kinObj.mobile : null;
    },

    openCallModal() {
      if (!this.activeThread) return;
      this.showCallModal = true;
    },

    formatCallDuration(sec) {
      const m = Math.floor(sec / 60);
      const s = sec % 60;
      return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    },

    playRingtone() {
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        this.audioCtx = new AudioContext();
        const playBeep = () => {
          if (!this.audioCtx || this.audioCtx.state === 'closed') return;
          const osc1 = this.audioCtx.createOscillator();
          const osc2 = this.audioCtx.createOscillator();
          const gain = this.audioCtx.createGain();
          osc1.type = 'sine';
          osc1.frequency.setValueAtTime(400, this.audioCtx.currentTime);
          osc2.type = 'sine';
          osc2.frequency.setValueAtTime(450, this.audioCtx.currentTime);
          gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime);
          gain.gain.exponentialRampToValueAtTime(0.0001, this.audioCtx.currentTime + 0.4);
          osc1.connect(gain);
          osc2.connect(gain);
          gain.connect(this.audioCtx.destination);
          osc1.start();
          osc2.start();
          osc1.stop(this.audioCtx.currentTime + 0.4);
          osc2.stop(this.audioCtx.currentTime + 0.4);
        };
        playBeep();
        this.ringtoneInterval = setInterval(() => playBeep(), 2200);
      } catch (e) {
        console.warn('AudioContext ringtone unavailable', e);
      }
    },

    stopRingtone() {
      if (this.ringtoneInterval) {
        clearInterval(this.ringtoneInterval);
        this.ringtoneInterval = null;
      }
      if (this.audioCtx) {
        try { this.audioCtx.close(); } catch (e) {}
        this.audioCtx = null;
      }
    },

    async initiateInAppCall(type) {
      if (!this.activeThread) return;
      this.callType = type;
      this.callPartnerName = this.partnerName(this.activeThread);
      this.isInCall = true;
      this.isCallRinging = true;
      this.callDurationSeconds = 0;
      this.isMicMuted = false;
      this.isCameraOff = false;

      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          audio: true,
          video: type === 'video'
        });
        this.localStream = stream;
        this.$nextTick(() => {
          const localVid = document.getElementById('kwLocalVideo');
          if (localVid) localVid.srcObject = stream;
        });

        this.peerConnection = new RTCPeerConnection({
          iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
        });

        stream.getTracks().forEach(track => {
          this.peerConnection.addTrack(track, stream);
        });

        this.peerConnection.ontrack = (event) => {
          this.isCallRinging = false;
          this.startCallTimer();
          const remoteVid = document.getElementById('kwRemoteVideo');
          if (remoteVid && event.streams[0]) {
            remoteVid.srcObject = event.streams[0];
          }
        };

        this.peerConnection.onicecandidate = (event) => {
          if (event.candidate && this.activeThread) {
            this.queueIceCandidate(event.candidate, this.activeThread.id);
          } else if (!event.candidate && this.activeThread) {
            this.flushIceCandidates(this.activeThread.id);
          }
        };

        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/initiate', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ call_type: type })
        });
        const data = await res.json();
        if (data.success && data.call) {
          this.callRoomId = data.call.room_id;
        }

        const offer = await this.peerConnection.createOffer();
        await this.peerConnection.setLocalDescription(offer);

        await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/signal', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ signal: { sdp: offer } })
        });

      } catch (err) {
        console.error('Call initiation error', err);
        this.toast('Microphone and camera permissions are required to start a call.');
        this.endCall();
      }
    },

    async acceptCall() {
      if (!this.incomingCall) return;
      this.stopRingtone();
      const callData = this.incomingCall;
      this.incomingCall = null;
      this.callType = callData.call_type || 'video';
      this.callPartnerName = callData.caller_name || 'Family Kin';
      this.callRoomId = callData.room_id;
      this.isInCall = true;
      this.isCallRinging = false;
      this.startCallTimer();

      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          audio: true,
          video: this.callType === 'video'
        });
        this.localStream = stream;
        this.$nextTick(() => {
          const localVid = document.getElementById('kwLocalVideo');
          if (localVid) localVid.srcObject = stream;
        });

        this.peerConnection = new RTCPeerConnection({
          iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
        });

        stream.getTracks().forEach(track => {
          this.peerConnection.addTrack(track, stream);
        });

        this.peerConnection.ontrack = (event) => {
          const remoteVid = document.getElementById('kwRemoteVideo');
          if (remoteVid && event.streams[0]) {
            remoteVid.srcObject = event.streams[0];
          }
        };

        this.peerConnection.onicecandidate = (event) => {
          if (event.candidate && callData.thread_id) {
            this.queueIceCandidate(event.candidate, callData.thread_id, callData.caller_id);
          } else if (!event.candidate && callData.thread_id) {
            this.flushIceCandidates(callData.thread_id, callData.caller_id);
          }
        };

        const targetThread = this.threads.find(t => t.id === callData.thread_id);
        if (targetThread && (!this.activeThread || this.activeThread.id !== callData.thread_id)) {
          this.selectThread(targetThread);
        }

      } catch (e) {
        console.error('Accept call error', e);
        this.toast('Could not access media devices.');
        this.endCall();
      }
    },

    declineCall() {
      this.stopRingtone();
      if (this.incomingCall) {
        fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.incomingCall.thread_id) + '/call/end', {
          method: 'POST',
          headers: jsonHeaders()
        });
        this.incomingCall = null;
      }
    },

    queueIceCandidate(candidate, threadId, targetUserId) {
      if (!candidate) return;
      this.candidateBuffer.push(candidate);
      if (this.candidateFlushTimer) clearTimeout(this.candidateFlushTimer);
      this.candidateFlushTimer = setTimeout(() => {
        this.flushIceCandidates(threadId, targetUserId);
      }, 120);
    },

    async flushIceCandidates(threadId, targetUserId) {
      if (!this.candidateBuffer.length) return;
      const candidatesToSend = [...this.candidateBuffer];
      this.candidateBuffer = [];
      if (this.candidateFlushTimer) {
        clearTimeout(this.candidateFlushTimer);
        this.candidateFlushTimer = null;
      }
      const tId = threadId || (this.activeThread ? this.activeThread.id : null);
      if (!tId) return;

      try {
        await fetch('/api/kin-whispers/threads/' + encodeURIComponent(tId) + '/call/signal', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            target_user_id: targetUserId || undefined,
            signal: { candidates: candidatesToSend }
          })
        });
      } catch (e) {
        console.error('Failed to flush ICE candidates', e);
      }
    },

    endCall() {
      this.stopRingtone();
      if (this.candidateFlushTimer) {
        clearTimeout(this.candidateFlushTimer);
        this.candidateFlushTimer = null;
      }
      this.candidateBuffer = [];
      if (this.callDurationTimer) {
        clearInterval(this.callDurationTimer);
        this.callDurationTimer = null;
      }
      if (this.peerConnection) {
        this.peerConnection.onicecandidate = null;
        this.peerConnection.ontrack = null;
        try { this.peerConnection.close(); } catch (e) {}
        this.peerConnection = null;
      }
      if (this.localStream) {
        try { this.localStream.getTracks().forEach(t => t.stop()); } catch (e) {}
        this.localStream = null;
      }
      if (this.activeThread) {
        fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/end', {
          method: 'POST',
          headers: jsonHeaders()
        });
      }
      this.isInCall = false;
      this.isCallRinging = false;
      this.callDurationSeconds = 0;
      this.incomingCall = null;
    },

    startCallTimer() {
      if (this.callDurationTimer) clearInterval(this.callDurationTimer);
      this.callDurationSeconds = 0;
      this.callDurationTimer = setInterval(() => {
        this.callDurationSeconds++;
      }, 1000);
    },

    toggleMuteAudio() {
      if (!this.localStream) return;
      const audioTracks = this.localStream.getAudioTracks();
      if (audioTracks.length > 0) {
        this.isMicMuted = !this.isMicMuted;
        audioTracks[0].enabled = !this.isMicMuted;
      }
    },

    toggleMuteVideo() {
      if (!this.localStream) return;
      const videoTracks = this.localStream.getVideoTracks();
      if (videoTracks.length > 0) {
        this.isCameraOff = !this.isCameraOff;
        videoTracks[0].enabled = !this.isCameraOff;
      }
    },

    initPusher() {
      const key = (document.querySelector('meta[name="pusher-key"]') || {}).content || '';
      const cluster = (document.querySelector('meta[name="pusher-cluster"]') || {}).content || 'eu';
      if (!key || typeof window.Pusher === 'undefined') return;

      try {
        this.pusher = new window.Pusher(key, {
          cluster: cluster,
          authEndpoint: '/pusher/auth',
          auth: {
            headers: {
              'X-CSRF-TOKEN': csrf()
            }
          }
        });

        const channelName = 'private-user-' + String(this.currentUserId).toLowerCase();
        this.userChannel = this.pusher.subscribe(channelName);

        this.userChannel.bind('kin-whisper', (data) => {
          if (data && data.thread_id) {
            if (this.activeThread && this.activeThread.id === data.thread_id) {
              this.pollUpdates();
            } else {
              const t = this.threads.find(x => x.id === data.thread_id);
              if (t) t.unread_count = (t.unread_count || 0) + 1;
            }
          }
        });

        this.userChannel.bind('kin-call-incoming', (data) => {
          if (data && data.caller_id !== this.currentUserId) {
            this.incomingCall = data;
            this.playRingtone();
          }
        });

        this.userChannel.bind('kin-call-signal', async (data) => {
          if (!this.peerConnection || !data || !data.signal) return;
          try {
            if (data.signal.sdp) {
              await this.peerConnection.setRemoteDescription(new RTCSessionDescription(data.signal.sdp));
              if (data.signal.sdp.type === 'offer') {
                const answer = await this.peerConnection.createAnswer();
                await this.peerConnection.setLocalDescription(answer);
                if (this.activeThread) {
                  fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/signal', {
                    method: 'POST',
                    headers: jsonHeaders(),
                    body: JSON.stringify({ target_user_id: data.sender_id, signal: { sdp: answer } })
                  });
                }
              }
            } else if (data.signal.candidate) {
              await this.peerConnection.addIceCandidate(new RTCIceCandidate(data.signal.candidate));
            } else if (Array.isArray(data.signal.candidates)) {
              for (const cand of data.signal.candidates) {
                if (cand) {
                  try {
                    await this.peerConnection.addIceCandidate(new RTCIceCandidate(cand));
                  } catch (candErr) {
                    console.warn('ICE candidate addition skipped', candErr);
                  }
                }
              }
            }
          } catch (e) {
            console.error('Signal handle error', e);
          }
        });

        this.userChannel.bind('kin-call-ended', () => {
          this.endCall();
        });

        this.userChannel.bind('kin-share-status', (data) => {
          if (!data || !data.thread_id) return;
          const idx = this.threads.findIndex(t => t.id === data.thread_id);
          if (idx > -1) {
            this.threads[idx].share_status = data.status;
          }
          if (this.activeThread && this.activeThread.id === data.thread_id) {
            this.activeThread.share_status = data.status;
            if (data.status === 'pending' && data.actor_id) {
              this.activeThread.share_requested_by = data.actor_id;
              this.toast('🔒 ' + (data.actor_name || 'Your kin') + ' requested your approval to share this whisper on WhatsApp.');
            } else if (data.status === 'approved') {
              this.activeThread.share_approved_by = data.actor_id || null;
              this.toast('✓ ' + (data.actor_name || 'Your kin') + ' authorised WhatsApp sharing for this whisper.');
            } else if (data.status === 'declined') {
              this.toast((data.actor_name || 'Your kin') + ' declined external WhatsApp sharing for this whisper.');
            } else if (data.status === 'none') {
              this.toast('WhatsApp sharing consent was revoked for this whisper.');
            }
          }
        });

        this.userChannel.bind('kin-screenshot-detected', (data) => {
          if (!data || !data.thread_id) return;
          if (this.activeThread && this.activeThread.id === data.thread_id) {
            this.threadEvents.push({
              id: data.event_id || Date.now(),
              thread_id: data.thread_id,
              user_id: data.actor_id,
              user_name: data.actor_name || 'Family Kin',
              event_type: 'screenshot_taken',
              created_at: data.timestamp || new Date().toISOString()
            });
            this.toast('📸 ' + (data.actor_name || 'Your kin') + ' took a screenshot of this whisper.');
          }
        });

        this.userChannel.bind('kin-whisper-deleted', (data) => {
          if (!data || !data.thread_id || !data.message_id) return;
          this.applyDeletedMessageState(data.thread_id, data.message_id, data.last_message_preview, data.last_message_at);
        });

      } catch (err) {
        console.warn('KinWhisper Pusher setup error', err);
      }
    },

    toast(msg) {
      if (window.Swal) { Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: msg, showConfirmButton: false, timer: 3500 }); }
      else { alert(msg); }
    }
  };
}
</script>
@endpush
