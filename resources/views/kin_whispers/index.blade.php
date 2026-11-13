@extends('layouts.profileBase')

@section('title', 'Kin Whispers & Artifact Messages — FamilyPlatform')
@section('meta_description', 'Private, thoughtful messages and voice dedications anchored to your family photographs, memories and lineage.')

@section('manifest_href', '/public/manifest-whispers.json')
@section('apple_app_title', 'Whispers')

@push('styles')
<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function () {
  try {
    var q = new URLSearchParams(window.location.search);
    var standalone = (window.navigator && window.navigator.standalone === true) ||
      (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches);
    if (q.get('mode') === 'popup' || standalone) {
      document.documentElement.classList.add('kw-popup');
    }
  } catch (e) {}
})();
</script>
<style>
/* Pop-out / installed-app mode: messenger-only chrome, no feed navigation */
html.kw-popup #mainNavbar,
html.kw-popup .kw-hero,
html.kw-popup .kw-notice { display: none !important; }
.kw-wrap {
    --kw-primary: #a31621;
    --kw-primary-dark: #7a0e16;
    --kw-blush: #fbe9eb;
    --kw-gold: #a31621;
    --kw-gold-bright: #a31621;
    --kw-canvas: #faf6f0;
    --kw-card: #ffffff;
    --kw-soft: #f6f1ea;
    --kw-ink: #1c1412;
    --kw-muted: #6e6259;
    --kw-line: #ece4da;
    --kw-mine: #a31621;
    --kw-theirs: #ffffff;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 0 80px;
    color: var(--kw-ink);
}
.dark-mode .kw-wrap {
    --kw-blush: rgba(163, 22, 33, 0.22);
    --kw-canvas: #0b0f19;
    --kw-card: #141a29;
    --kw-soft: #0f1524;
    --kw-ink: #f8fafc;
    --kw-muted: #cbd5e1;
    --kw-line: #1e293b;
    --kw-theirs: #1e293b;
}
[x-cloak] { display: none !important; }

/* Hero & Top Notice removed per revamp so conversations start at top */
.kw-hero,
.kw-notice {
    display: none !important;
}

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
.kw-btn--gold { background: var(--kw-primary); color: #fff; }
.kw-btn--primary { background: var(--kw-primary); color: #fff; border-radius: 999px; }
.kw-btn--ghost { background: var(--kw-card); color: var(--kw-ink); border-color: var(--kw-line); }
.kw-btn--ghost.is-live { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.kw-btn--square { padding: 0; width: 44px; height: 44px; min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; }

/* Workspace: 3-Pane on Desktop (>=1100px), 2-Pane on iPad (768px-1099px), List/Chat on Mobile (<768px) */
.kw-shell {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr) 290px;
    grid-template-rows: 100%;
    background: var(--kw-card);
    border-top: 1px solid var(--kw-line);
    border-bottom: 1px solid var(--kw-line);
    overflow: hidden;
    height: calc(100vh - 70px);
    min-height: 620px;
}
.kw-side {
    border-right: 1px solid var(--kw-line); background: var(--kw-card); display: flex; flex-direction: column;
    min-width: 0; min-height: 0; height: 100%; max-height: 100%; overflow: hidden;
}
.kw-side__head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 18px 18px 12px; flex-shrink: 0;
}
.kw-side__title {
    font-family: 'Outfit', 'Inter', sans-serif; font-size: 1.375rem; font-weight: 800;
    letter-spacing: -0.02em; margin: 0; color: var(--kw-ink); line-height: 1.15;
}
.kw-side__sub {
    display: flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 500;
    color: var(--kw-muted); margin: 3px 0 0;
}
.kw-new-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    min-height: 40px; padding: 0 16px; border-radius: 999px; border: 0;
    background: var(--kw-primary); color: #ffffff; font-size: 0.8125rem; font-weight: 700;
    white-space: nowrap; flex-shrink: 0; cursor: pointer; transition: filter 0.15s ease, transform 0.15s ease;
}
.kw-new-btn:hover { filter: brightness(1.06); }
.kw-new-btn:active { transform: scale(0.98); }

.kw-search { padding: 0 16px 10px; position: relative; flex-shrink: 0; }
.kw-search i { position: absolute; left: 30px; top: calc(50% - 5px); transform: translateY(-50%); color: var(--kw-muted); pointer-events: none; font-size: 0.875rem; }
.kw-input, .kw-select, .kw-textarea {
    width: 100%; background: var(--kw-soft); color: var(--kw-ink); border: 1px solid transparent; border-radius: 999px;
    padding: 9px 14px; font-size: .875rem; line-height: 1.4; min-height: 42px;
}
.kw-modal .kw-input, .kw-modal .kw-select, .kw-modal .kw-textarea { border-radius: 12px; border-color: var(--kw-line); background: var(--kw-card); }
.kw-search .kw-input { padding-left: 38px; }
.kw-input:focus, .kw-select:focus, .kw-textarea:focus { outline: none; border-color: var(--kw-primary); background: var(--kw-card); }
.kw-textarea { resize: none; border-radius: 18px; }

.kw-filter-pills {
    display: flex; align-items: center; gap: 6px; padding: 0 16px 12px;
    border-bottom: 1px solid var(--kw-line); overflow-x: auto; flex-shrink: 0;
    scrollbar-width: none;
}
.kw-filter-pills::-webkit-scrollbar { display: none; }
.kw-filter-pill {
    display: inline-flex; align-items: center; justify-content: center;
    min-height: 32px; padding: 4px 13px; border-radius: 999px;
    border: 1px solid var(--kw-line); background: var(--kw-card); color: var(--kw-muted);
    font-size: 0.78rem; font-weight: 700; white-space: nowrap; flex-shrink: 0; cursor: pointer;
    transition: all 0.15s ease;
}
.kw-filter-pill:hover { color: var(--kw-ink); background: var(--kw-soft); }
.kw-filter-pill.is-active {
    background: var(--kw-blush); color: var(--kw-primary); border-color: transparent;
}

.kw-threads { flex: 1 1 0%; overflow-y: auto; min-height: 0; }
.kw-thread {
    width: 100%; display: flex; gap: 12px; align-items: flex-start; text-align: left; padding: 14px 16px;
    background: transparent; border: 0; border-bottom: 1px solid var(--kw-line); border-left: 3px solid transparent;
    color: var(--kw-ink); cursor: pointer; min-height: 76px; transition: background-color 0.15s ease;
}
.kw-thread.is-active { background: #fdf5f6; border-left-color: var(--kw-primary); }
.dark-mode .kw-thread.is-active { background: rgba(163, 22, 33, 0.18); }
@media (hover: hover) { .kw-thread:hover:not(.is-active) { background: var(--kw-canvas); } }
.kw-thread:focus-visible { outline: 2px solid var(--kw-primary); outline-offset: -2px; }
.kw-thread__body { flex: 1; min-width: 0; }
.kw-thread__top { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; }
.kw-thread__name { font-size: .9rem; font-weight: 700; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--kw-ink); }
.kw-thread__time { font-size: .75rem; color: var(--kw-muted); white-space: nowrap; flex-shrink: 0; }
.kw-thread__sub { font-size: 0.75rem; color: var(--kw-muted); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-thread__preview { margin: 4px 0 0; font-size: .8125rem; color: var(--kw-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-chip {
    display: inline-flex; align-items: center; gap: 4px; max-width: 100%; margin-top: 3px; padding: 2px 8px; border-radius: 999px;
    background: var(--kw-soft); color: var(--kw-muted); border: 1px solid var(--kw-line); font-size: .72rem; font-weight: 600;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.kw-chip--green { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
.kw-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: var(--kw-primary); color: #fff; font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; align-self: center; }

/* Avatar (rose/pastel monogram fallback sits behind the image) */
.kw-avatar { position: relative; width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
    background: #f3dce0; color: #7a1c24; font-weight: 700; font-size: .875rem;
    display: flex; align-items: center; justify-content: center; border: 1.5px solid var(--kw-card); box-shadow: 0 0 0 1px var(--kw-line); }
.kw-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }

/* Conversation (Centre Pane) */
.kw-main {
    display: flex; flex-direction: column; min-width: 0; min-height: 0;
    height: 100%; max-height: 100%; overflow: hidden; position: relative;
    background: var(--kw-canvas);
}
.kw-conversation {
    display: flex; flex-direction: column; flex: 1 1 0%; min-width: 0; min-height: 0;
    height: 100%; max-height: 100%; overflow: hidden;
}
.kw-head {
    display: flex; flex-wrap: nowrap; gap: 12px; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid var(--kw-line); background: var(--kw-card); flex-shrink: 0;
}
.kw-head__who { display: flex; gap: 12px; align-items: center; min-width: 0; flex: 1; }
.kw-head__title { font-size: .95rem; font-weight: 700; margin: 0; color: var(--kw-ink); }
.kw-head__sub { font-size: .75rem; color: var(--kw-muted); margin: 2px 0 0; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.kw-back { display: none; }

/* Context memory pill at top of stream */
.kw-context-pill {
    align-self: center; display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 999px; background: #efe8de; color: #5c5046;
    font-size: 0.75rem; font-weight: 600; margin: 0 auto 4px; max-width: 92%;
}
.dark-mode .kw-context-pill { background: #1e293b; color: #cbd5e1; }
.kw-context-pill strong { color: var(--kw-ink); font-weight: 700; }

.kw-stream {
    flex: 1 1 0%; min-height: 0; padding: 20px 24px; overflow-y: auto; overflow-x: hidden;
    display: flex; flex-direction: column; gap: 14px; background: var(--kw-canvas);
}
.kw-row { display: flex; width: 100%; }
.kw-row.is-mine { justify-content: flex-end; }
.kw-row:not(.is-mine) { justify-content: flex-start; }
.kw-bubble__container { position: relative; max-width: min(76%, 540px); min-width: 160px; width: fit-content; display: flex; flex-direction: column; }
.kw-bubble {
    min-width: 160px; width: 100%; max-width: 100%; box-sizing: border-box;
    padding: 12px 16px; border-radius: 18px; background: var(--kw-theirs); color: var(--kw-ink);
    border: 1px solid var(--kw-line); position: relative; box-shadow: 0 1px 2px rgba(28, 20, 18, 0.04);
}
.kw-row.is-mine .kw-bubble { background: var(--kw-mine); color: #fff; border-color: transparent; border-bottom-right-radius: 6px; margin-left: auto; }
.kw-row:not(.is-mine) .kw-bubble { border-bottom-left-radius: 6px; margin-right: auto; }
.kw-bubble__who { display: none; }
.kw-bubble__text { margin: 0; font-size: .9rem; line-height: 1.5; white-space: pre-wrap; word-break: normal; overflow-wrap: break-word; }
.kw-bubble__meta { margin-top: 4px; font-size: .7rem; opacity: .72; display: flex; align-items: center; justify-content: flex-end; gap: 5px; white-space: nowrap; }
.kw-voice { margin-bottom: 6px; padding: 10px 12px; border-radius: 14px; background: var(--kw-soft); }
.kw-row.is-mine .kw-voice { background: rgba(255,255,255,.16); }
.kw-voice__label { display: flex; align-items: center; justify-content: space-between; gap: 8px; font-size: .75rem; font-weight: 700; margin-bottom: 6px; }
.kw-voice audio { width: 100%; height: 34px; }

/* Right Details Pane (Desktop 3rd column) */
.kw-details-pane {
    border-left: 1px solid var(--kw-line); background: var(--kw-card);
    display: flex; flex-direction: column; height: 100%; overflow-y: auto;
    padding: 24px 20px; gap: 22px;
}
.kw-details__profile {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    padding-bottom: 18px; border-bottom: 1px solid var(--kw-line);
}
.kw-details__avatar {
    width: 68px; height: 68px; border-radius: 50%; background: #f3dce0; color: #7a1c24;
    font-size: 1.35rem; font-weight: 800; display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden; margin-bottom: 10px;
}
.kw-details__avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.kw-details__name { font-family: 'Outfit', 'Inter', sans-serif; font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--kw-ink); }
.kw-details__sub { font-size: 0.78rem; color: var(--kw-muted); margin: 3px 0 14px; }
.kw-details__btns { display: flex; gap: 8px; width: 100%; }
.kw-details__pill-btn {
    flex: 1; min-height: 38px; padding: 0 12px; border-radius: 999px;
    border: 1px solid var(--kw-line); background: var(--kw-card); color: var(--kw-ink);
    font-size: 0.78rem; font-weight: 700; text-decoration: none;
    display: inline-flex; align-items: center; justify-content: center;
    white-space: nowrap; transition: background-color 0.15s ease;
}
.kw-details__pill-btn:hover { background: var(--kw-soft); color: var(--kw-ink); text-decoration: none; }

.kw-details__sec-head {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;
}
.kw-details__sec-title { font-size: 0.78rem; font-weight: 700; color: var(--kw-muted); margin: 0; }
.kw-details__sec-link { font-size: 0.78rem; font-weight: 700; color: var(--kw-primary); background: none; border: 0; padding: 0; cursor: pointer; }
.kw-details__media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.kw-details__media-slot {
    aspect-ratio: 1; border-radius: 10px; overflow: hidden; background: #eadccf;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 600; color: #7a6855; cursor: pointer;
}
.kw-details__media-slot img { width: 100%; height: 100%; object-fit: cover; }

.kw-details__opt-list { display: flex; flex-direction: column; gap: 2px; }
.kw-details__opt {
    width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px;
    min-height: 42px; padding: 8px 10px; border-radius: 10px; border: 0; background: transparent;
    color: var(--kw-ink); font-size: 0.84rem; font-weight: 600; text-align: left; cursor: pointer;
    transition: background-color 0.15s ease;
}
.kw-details__opt:hover { background: var(--kw-soft); }
.kw-details__opt-left { display: flex; align-items: center; gap: 10px; }
.kw-details__opt-left i { color: var(--kw-muted); font-size: 0.95rem; }

.kw-details__privacy-card {
    margin-top: auto; padding: 12px 14px; border-radius: 14px; background: var(--kw-soft);
    display: flex; align-items: flex-start; gap: 10px; font-size: 0.75rem; line-height: 1.45; color: var(--kw-muted);
}
.kw-details__privacy-card i { color: var(--kw-primary); font-size: 0.95rem; margin-top: 1px; flex-shrink: 0; }
.kw-details__privacy-card strong { color: var(--kw-ink); font-weight: 700; }

/* Message Reactions & Actions */
.kw-bubble__actions {
    position: absolute; top: -12px; opacity: 0; pointer-events: none;
    transform: translateY(2px);
    transition: opacity .15s ease, transform .15s ease;
    display: inline-flex; align-items: center; gap: 2px; z-index: 20;
    background: var(--kw-card); border: 1px solid var(--kw-line);
    border-radius: 999px; padding: 2px 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
.kw-row.is-mine .kw-bubble__actions { right: 6px; left: auto; }
.kw-row:not(.is-mine) .kw-bubble__actions { left: 6px; right: auto; }
@media (hover: hover) {
    .kw-bubble__container:hover .kw-bubble__actions { opacity: 1; pointer-events: auto; transform: translateY(0); }
}
.kw-bubble__container:focus-within .kw-bubble__actions,
.kw-bubble__actions.is-open { opacity: 1; pointer-events: auto; transform: translateY(0); }

.kw-react-trigger {
    background: transparent; border: 0; border-radius: 999px;
    width: 24px; height: 24px; min-width: 24px; min-height: 24px; padding: 0;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .72rem; color: var(--kw-muted);
    cursor: pointer; position: relative;
    transition: transform .12s ease, color .12s ease, background-color .12s ease;
}
.kw-react-trigger::after {
    content: ''; position: absolute; inset: -4px;
}
.kw-react-trigger:hover { transform: scale(1.08); color: var(--kw-primary); background: var(--kw-soft); }
.kw-react-trigger--danger:hover { color: #dc2626; background: #fef2f2; }

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

.kw-composer { padding: 12px 20px 14px; border-top: 1px solid var(--kw-line); background: var(--kw-card); flex-shrink: 0; position: relative; z-index: 5; }
.kw-rec { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 14px; margin-bottom: 10px; border-radius: 12px; background: var(--kw-blush); border: 1px solid rgba(163,22,33,0.2); color: var(--kw-primary); font-size: .8125rem; font-weight: 600; }
.kw-rec__dot { width: 10px; height: 10px; border-radius: 50%; background: #dc2626; animation: kwPulse 1s ease-in-out infinite; display: inline-block; margin-right: 8px; }
@keyframes kwPulse { 50% { opacity: .35; } }
.kw-composer__form { display: flex !important; flex-direction: column !important; align-items: stretch !important; gap: 8px !important; width: 100% !important; }
.kw-composer__pill-bar {
    display: flex; align-items: center; gap: 6px; width: 100%;
    background: var(--kw-soft); border: 1px solid var(--kw-line); border-radius: 999px;
    padding: 4px 6px 4px 8px; transition: border-color .15s ease, box-shadow .15s ease;
}
.kw-composer__pill-bar:focus-within { border-color: var(--kw-primary); background: var(--kw-card); box-shadow: 0 0 0 3px rgba(163,22,33,0.1); }
.kw-composer__form .kw-textarea {
    display: block !important; flex: 1 1 auto !important; width: auto !important; min-width: 0 !important; box-sizing: border-box !important;
    min-height: 38px; max-height: 110px; padding: 8px 10px; font-size: .9rem;
    line-height: 1.45; border-radius: 18px; background: transparent; border: 0;
    box-shadow: none; resize: none;
}
.kw-composer__form .kw-textarea:focus { outline: none; border: 0; box-shadow: none; }
.kw-composer__actions { display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: flex-end !important; gap: 4px; flex-shrink: 0; }
.kw-composer__tools { display: flex; align-items: center; gap: 2px; }
.kw-icon-tool {
    width: 38px; height: 38px; min-width: 38px; min-height: 38px; border-radius: 50%;
    border: 0; background: transparent; color: var(--kw-muted);
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; position: relative; transition: background-color 0.15s ease, color 0.15s ease;
}
.kw-icon-tool:hover { background: rgba(28, 20, 18, 0.06); color: var(--kw-ink); }
.kw-icon-tool.is-active, .kw-icon-tool.is-live { background: var(--kw-blush); color: var(--kw-primary); }
.kw-send-circle {
    width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 50%;
    border: 0; background: var(--kw-primary); color: #ffffff;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; flex-shrink: 0; transition: filter 0.15s ease, transform 0.15s ease;
}
.kw-send-circle:hover:not(:disabled) { filter: brightness(1.08); }
.kw-send-circle:active:not(:disabled) { transform: scale(0.96); }
.kw-send-circle:disabled { opacity: 0.45; cursor: not-allowed; }

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

/* Responsive breakpoints:
   Desktop >= 1100px: 3 panes (list, chat, details)
   iPad 768px - 1099.98px: 2 panes (list, chat); details opens via slide-over drawer
   Mobile < 768px: 1 pane (list then chat)
*/
@media (max-width: 1099.98px) {
    .kw-shell {
        grid-template-columns: 310px minmax(0, 1fr);
    }
    .kw-details-pane {
        display: none;
    }
    .kw-new-btn span {
        display: none;
    }
    .kw-new-btn {
        width: 40px; height: 40px; min-width: 40px; padding: 0; border-radius: 50%;
    }
}
@media (max-width: 767.98px) {
    .kw-shell {
        grid-template-columns: 1fr;
        grid-template-rows: 100%;
        height: calc(100vh - 118px);
        min-height: 500px;
    }
    .kw-side { border-right: 0; }
    .kw-shell[data-pane="chat"] .kw-side { display: none; }
    .kw-shell[data-pane="list"] .kw-main { display: none; }
    .kw-back { display: inline-flex; }
    .kw-threads { max-height: none; }
    .kw-stream { padding: 14px 16px; min-height: 0; }
    .kw-head, .kw-composer { padding: 10px 14px; }
    .kw-bubble__container { max-width: 86%; }
    .kw-bubble { max-width: 100%; }
    .kw-bubble__actions { top: -10px; padding: 1px 3px; gap: 1px; }
    .kw-react-trigger { width: 22px; height: 22px; min-width: 22px; min-height: 22px; font-size: .68rem; }
}
/* WhatsApp Parity Enhancements */

.kw-pinned-icon { font-size: 0.8125rem; color: var(--kw-gold-bright); margin-left: 4px; }
.kw-muted-icon { font-size: 0.8125rem; color: var(--kw-muted); margin-left: 4px; }

/* Presence: online dot & last seen */
.kw-presence-dot { position: absolute; right: 0; bottom: 0; width: 12px; height: 12px; border-radius: 50%; background: #22c55e; border: 2px solid var(--kw-card); z-index: 2; }
.kw-avatar-wrap { position: relative; flex-shrink: 0; width: 44px; height: 44px; }
.kw-presence-text { font-size: .75rem; font-weight: 600; color: var(--kw-muted); white-space: nowrap; }
.kw-presence-text.is-online { color: #15803d; }
.kw-presence-text.is-online::before { content: ""; display: inline-block; width: 8px; height: 8px; margin-right: 6px; border-radius: 50%; background: #22c55e; vertical-align: baseline; }
.kw-privacy-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border: 1px solid var(--kw-line); border-radius: 12px; background: var(--kw-soft); }
.kw-privacy-row__copy { min-width: 0; }
.kw-privacy-row__title { font-size: .875rem; font-weight: 700; margin: 0; }
.kw-privacy-row__hint { font-size: .75rem; color: var(--kw-muted); margin: 2px 0 0; }
.kw-switch { position: relative; flex-shrink: 0; min-width: 44px; min-height: 44px; background: none; border: 0; padding: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.kw-switch__track { width: 44px; height: 26px; border-radius: 999px; background: var(--kw-line); position: relative; transition: background .2s ease; }
.kw-switch__track::after { content: ""; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3); transition: transform .2s ease; }
.kw-switch[aria-checked="true"] .kw-switch__track { background: #16a34a; }
.kw-switch[aria-checked="true"] .kw-switch__track::after { transform: translateX(18px); }
.kw-switch:focus-visible { outline: 2px solid var(--kw-primary); outline-offset: 2px; border-radius: 12px; }
.kw-switch:disabled { opacity: .5; cursor: not-allowed; }
@media (prefers-reduced-motion: reduce) { .kw-switch__track, .kw-switch__track::after { transition: none; } }

.kw-quote-banner { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: rgba(163,22,33,0.08); border-left: 4px solid var(--kw-primary); border-radius: 8px; margin-bottom: 8px; }
.kw-quote-banner__content { flex: 1; min-width: 0; }
.kw-quote-banner__sender { font-size: 0.75rem; font-weight: 700; color: var(--kw-primary); }
.kw-quote-banner__snippet { font-size: 0.8125rem; color: var(--kw-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-quote-banner__close { background: none; border: 0; color: var(--kw-muted); cursor: pointer; padding: 4px; font-size: 0.875rem; }

.kw-quoted-box { background: rgba(0,0,0,0.06); border-left: 3px solid var(--kw-gold); border-radius: 6px; padding: 6px 10px; margin-bottom: 6px; cursor: pointer; font-size: 0.8125rem; transition: background .15s; }
.kw-quoted-box:hover { background: rgba(0,0,0,0.1); }
.is-mine .kw-quoted-box { background: rgba(255,255,255,0.12); border-left-color: #ffd700; }
.kw-quoted-box__sender { font-weight: 700; font-size: 0.6875rem; color: var(--kw-primary); }
.is-mine .kw-quoted-box__sender { color: #fef08a; }
.kw-quoted-box__snippet { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.kw-att-preview { display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: var(--kw-card); border: 1px solid var(--kw-line); border-radius: 10px; margin-bottom: 8px; }
.kw-att-preview__thumb { width: 44px; height: 44px; border-radius: 6px; object-fit: cover; }
.kw-att-preview__icon { font-size: 1.75rem; color: var(--kw-primary); }
.kw-att-preview__info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.kw-att-preview__name { font-size: 0.8125rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-att-preview__size { font-size: 0.6875rem; color: var(--kw-muted); }
.kw-att-preview__remove { background: none; border: 0; color: #ef4444; font-size: 1.15rem; cursor: pointer; min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }

.kw-bubble__link {
    color: var(--kw-primary, #a31621);
    text-decoration: underline;
    text-underline-offset: 2px;
    font-weight: 500;
    word-break: break-word;
    overflow-wrap: anywhere;
}
.kw-row.is-mine .kw-bubble__link {
    color: #fef08a;
}
.kw-link-preview {
    display: block;
    margin-top: 8px;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    background: var(--kw-card, #ffffff);
    color: var(--kw-ink, #0f172a);
    border: 1px solid var(--kw-line, #e2e8f0);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    max-width: 340px;
    min-height: 44px;
    transition: transform .15s ease, box-shadow .15s ease;
}
.dark-mode .kw-link-preview {
    background: #1e293b;
    color: #f8fafc;
    border-color: rgba(255, 255, 255, 0.14);
}
@media (hover: hover) {
    .kw-link-preview:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.14); }
}
.kw-link-preview:active { transform: scale(0.98); }
.kw-link-preview:focus-visible { outline: 2px solid var(--kw-primary, #a31621); outline-offset: 2px; }
.kw-link-preview__media {
    width: 100%;
    aspect-ratio: 16 / 9;
    max-height: 168px;
    background: #0f172a;
    overflow: hidden;
    position: relative;
}
.kw-link-preview__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.kw-link-preview__body {
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.kw-link-preview__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--kw-muted, #64748b);
}
.dark-mode .kw-link-preview__meta { color: #94a3b8; }
.kw-link-preview__cta {
    color: var(--kw-primary, #a31621);
    white-space: nowrap;
    flex-shrink: 0;
}
.dark-mode .kw-link-preview__cta { color: #fca5a5; }
.kw-link-preview__title {
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.kw-link-preview__desc {
    font-size: 0.78rem;
    line-height: 1.4;
    color: var(--kw-muted, #475569);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.dark-mode .kw-link-preview__desc { color: #cbd5e1; }
.kw-composer-link-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: var(--kw-card, #ffffff);
    color: var(--kw-ink, #0f172a);
    border: 1px solid var(--kw-line, #e2e8f0);
    border-radius: 12px;
    margin-bottom: 8px;
}
.dark-mode .kw-composer-link-preview {
    background: #1e293b;
    color: #f8fafc;
    border-color: rgba(255, 255, 255, 0.14);
}
.kw-composer-link-preview__thumb {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    background: #0f172a;
    flex-shrink: 0;
}
.kw-composer-link-preview__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.kw-composer-link-preview__domain {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--kw-muted, #64748b);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.kw-composer-link-preview__title {
    font-size: 0.8125rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.kw-composer-link-preview__desc {
    font-size: 0.75rem;
    color: var(--kw-muted, #64748b);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.kw-att-image-wrap { margin-top: 6px; margin-bottom: 4px; border-radius: 12px; overflow: hidden; cursor: pointer; max-width: 320px; }
.kw-att-image { width: 100%; height: auto; max-height: 260px; object-fit: cover; display: block; transition: transform .2s; }
.kw-att-image:hover { transform: scale(1.02); }
.kw-att-video { width: 100%; max-width: 320px; max-height: 280px; border-radius: 10px; margin-top: 6px; background: #000; display: block; }
.kw-att-doc { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: rgba(0,0,0,0.06); border-radius: 8px; text-decoration: none; color: inherit; margin-top: 6px; }
.is-mine .kw-att-doc { background: rgba(255,255,255,0.12); color: #fff; }
.kw-att-doc i { font-size: 1.5rem; color: #ef4444; }
.kw-att-doc__info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.kw-att-doc__name { font-size: 0.8125rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-att-doc__size { font-size: 0.6875rem; opacity: .8; }

.kw-double-check { font-weight: 800; letter-spacing: -2px; font-size: 0.8125rem; margin-right: 2px; }
.kw-double-check--blue { color: #0284c7 !important; }
.kw-star-badge { color: #ffd700; font-size: 0.75rem; margin-right: 2px; }
.kw-edited-badge { font-size: 0.6875rem; font-style: italic; opacity: 0.75; margin-right: 4px; }

.kw-speed-btn { background: rgba(0,0,0,0.1); border: 0; border-radius: 12px; padding: 2px 8px; font-size: 0.6875rem; font-weight: 800; cursor: pointer; margin-left: 6px; color: inherit; }
.is-mine .kw-speed-btn { background: rgba(255,255,255,0.2); color: #fff; }
.kw-speed-btn:hover { filter: brightness(1.15); }

.kw-in-chat-search { padding: 8px 16px; background: var(--kw-card); border-bottom: 1px solid var(--kw-line); flex-shrink: 0; }
.kw-in-chat-search__inner { display: flex; align-items: center; gap: 8px; max-width: 600px; margin: 0 auto; position: relative; }
.kw-in-chat-search__inner input { flex: 1; padding: 6px 14px; border-radius: 20px; border: 1px solid var(--kw-line); background: var(--kw-bg); color: var(--kw-ink); font-size: 0.875rem; }
.kw-match-count { font-size: 0.75rem; color: var(--kw-muted); font-weight: 600; white-space: nowrap; }
.kw-match-count--none { color: #ef4444; }
.kw-match-nav, .kw-match-close { background: none; border: 0; color: var(--kw-muted); cursor: pointer; padding: 4px 6px; font-size: 0.875rem; border-radius: 4px; }
.kw-match-nav:hover, .kw-match-close:hover { color: var(--kw-ink); background: rgba(0,0,0,0.05); }
mark.kw-highlight { background: #fef08a; color: #000; border-radius: 2px; padding: 0 2px; }

.kw-drawer-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1050; }
.kw-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 100%; max-width: 380px; background: var(--kw-card); border-left: 1px solid var(--kw-line); z-index: 1051; display: flex; flex-direction: column; box-shadow: -10px 0 30px rgba(0,0,0,0.25); animation: kwDrawerSlide .25s ease-out; }
@keyframes kwDrawerSlide { from { transform: translateX(100%); } to { transform: translateX(0); } }
.kw-drawer__head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--kw-line); }
.kw-drawer__head h3 { font-size: 1.1rem; font-weight: 800; margin: 0; }
.kw-drawer__body { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 20px; }
.kw-drawer__profile { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; }
.kw-avatar--large { width: 80px; height: 80px; font-size: 1.75rem; border-width: 3px; }
.kw-drawer__profile h4 { font-size: 1.15rem; font-weight: 800; margin: 0; }
.kw-drawer__actions { display: flex; justify-content: center; gap: 16px; border-bottom: 1px solid var(--kw-line); padding-bottom: 16px; }
.kw-drawer-action { display: flex; flex-direction: column; align-items: center; gap: 4px; background: none; border: 0; color: var(--kw-ink); cursor: pointer; font-size: 0.75rem; font-weight: 600; padding: 8px 12px; border-radius: 8px; }
.kw-drawer-action i { font-size: 1.25rem; color: var(--kw-primary); }
.kw-drawer-action:hover { background: rgba(0,0,0,0.05); }
.kw-drawer-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--kw-line); }
.kw-drawer-tab { background: none; border: 0; border-bottom: 2px solid transparent; padding: 8px 12px; font-size: 0.8125rem; font-weight: 700; color: var(--kw-muted); cursor: pointer; }
.kw-drawer-tab.is-active { color: var(--kw-primary); border-bottom-color: var(--kw-primary); }
.kw-media-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.kw-media-thumb { aspect-ratio: 1; border-radius: 6px; overflow: hidden; cursor: pointer; }
.kw-media-thumb img { width: 100%; height: 100%; object-fit: cover; }
.kw-starred-list, .kw-docs-list { display: flex; flex-direction: column; gap: 8px; }
.kw-starred-item { padding: 10px 12px; background: rgba(0,0,0,0.03); border-radius: 8px; cursor: pointer; }
.kw-starred-item:hover { background: rgba(0,0,0,0.06); }
.kw-starred-item__who { font-size: 0.75rem; font-weight: 700; color: var(--kw-primary); }
.kw-starred-item__body { font-size: 0.8125rem; margin: 2px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kw-starred-item__time { font-size: 0.6875rem; color: var(--kw-muted); }
.kw-drawer-empty { text-align: center; color: var(--kw-muted); font-size: 0.8125rem; padding: 24px 0; }

.kw-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 1200; display: flex; align-items: center; justify-content: center; padding: 24px; cursor: zoom-out; animation: kwFade .2s; }
.kw-lightbox__img { max-width: 95%; max-height: 90vh; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); cursor: default; }
.kw-lightbox__video { max-width: 95%; max-height: 85vh; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); cursor: default; background: #000; }
.kw-lightbox__close { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.15); border: 0; color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 1.25rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.kw-lightbox__close:hover { background: rgba(255,255,255,0.3); }

.kw-media-thumb--video { position: relative; background: #000; }
.kw-video-overlay-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.28); color: #fff; font-size: 1.5rem; transition: transform 0.2s ease, background 0.2s ease; }
.kw-media-thumb--video:hover .kw-video-overlay-icon { transform: scale(1.1); background: rgba(0,0,0,0.45); }

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
      <div class="kw-side__head">
        <div>
          <h1 class="kw-side__title">Whispers</h1>
          <p class="kw-side__sub"><i class="bi bi-lock"></i> Private &amp; supervised</p>
        </div>
        <button type="button" class="kw-new-btn" @click="openCompose()" aria-label="New whisper" title="Start a new whisper">
          <i class="bi bi-plus-lg"></i><span>New whisper</span>
        </button>
      </div>

      <div class="kw-search">
        <i class="bi bi-search"></i>
        <label for="kw_search" class="visually-hidden">Search conversations</label>
        <input id="kw_search" type="search" class="kw-input" x-model="searchQuery" placeholder="Search by name or topic">
      </div>

      <div class="kw-filter-pills" role="tablist" aria-label="Filter whispers">
        <button type="button" class="kw-filter-pill" :class="{'is-active': threadFilter === 'all'}" @click="threadFilter = 'all'">All</button>
        <button type="button" class="kw-filter-pill" :class="{'is-active': threadFilter === 'unread'}" @click="threadFilter = 'unread'">Unread</button>
        <button type="button" class="kw-filter-pill" :class="{'is-active': threadFilter === 'groups'}" @click="threadFilter = 'groups'">Groups</button>
        <button type="button" class="kw-filter-pill" :class="{'is-active': threadFilter === 'starred'}" @click="threadFilter = 'starred'">Starred</button>
      </div>

      <div class="kw-threads">
        <template x-for="thread in filteredThreads" :key="thread.id">
          <button type="button" class="kw-thread" :class="{'is-active': activeThread && activeThread.id === thread.id}" @click="selectThread(thread)">
            <div class="kw-avatar-wrap">
            <div class="kw-avatar">
              <template x-if="thread.thread_type === 'group'">
                <i class="bi bi-people" style="font-size:1.1rem; color:var(--kw-primary);"></i>
              </template>
              <template x-if="thread.thread_type !== 'group'">
                <div>
                  <span x-text="initials(partnerName(thread))"></span>
                  <img :src="partnerAvatar(thread)" alt="" loading="lazy" x-on:error="$el.remove()">
                </div>
              </template>
            </div>
            <span class="kw-presence-dot" x-show="isPartnerOnline(thread)" x-cloak title="Online" aria-label="Online"></span>
            </div>
            <div class="kw-thread__body">
              <div class="kw-thread__top">
                <h3 class="kw-thread__name" :title="partnerName(thread)">
                  <span x-text="partnerName(thread)"></span>
                  <template x-if="thread.is_pinned"><i class="bi bi-pin-angle-fill kw-pinned-icon" title="Pinned conversation"></i></template>
                  <template x-if="thread.is_muted"><i class="bi bi-bell-slash-fill kw-muted-icon" title="Muted notifications"></i></template>
                </h3>
                <span class="kw-thread__time" x-text="formatShort(thread.last_message_at)"></span>
              </div>
              <div class="kw-thread__sub">
                <template x-if="thread.thread_type === 'group'">
                  <span x-text="(thread.participant_count || 2) + ' family members'"></span>
                </template>
                <template x-if="thread.thread_type !== 'group'">
                  <span x-text="thread.context_title || labelFor(thread.context_type)"></span>
                </template>
              </div>
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
            <button type="button" class="kw-btn kw-btn--primary" x-show="!searchQuery" @click="openCompose()"><i class="bi bi-plus-lg"></i><span>New whisper</span></button>
          </div>
        </template>
      </div>
    </aside>

    {{-- Conversation --}}
    <div class="kw-main">
      <template x-if="activeThread">
        <div class="kw-conversation">
          <div class="kw-head">
            <div class="kw-head__who" @click="openInfoDrawer()" style="cursor:pointer;" title="View thread info & shared media">
              <button type="button" class="kw-btn kw-btn--ghost kw-btn--square kw-back" @click.stop="pane = 'list'" aria-label="Back to conversations"><i class="bi bi-arrow-left"></i></button>
              <div class="kw-avatar-wrap">
              <div class="kw-avatar">
                <template x-if="activeThread.thread_type === 'group'">
                  <i class="bi bi-people" style="font-size:1.2rem; color:var(--kw-primary);"></i>
                </template>
                <template x-if="activeThread.thread_type !== 'group'">
                  <div>
                    <span x-text="initials(partnerName(activeThread))"></span>
                    <img :src="partnerAvatar(activeThread)" alt="" x-on:error="$el.remove()">
                  </div>
                </template>
              </div>
              <span class="kw-presence-dot" x-show="isPartnerOnline(activeThread)" x-cloak aria-hidden="true"></span>
              </div>
              <div style="min-width:0;">
                <h2 class="kw-head__title">
                  <span x-text="partnerName(activeThread)"></span>
                  <template x-if="activeThread.is_pinned"><i class="bi bi-pin-angle-fill kw-pinned-icon" title="Pinned conversation"></i></template>
                  <template x-if="activeThread.is_muted"><i class="bi bi-bell-slash-fill kw-muted-icon" title="Muted notifications"></i></template>
                </h2>
                <p class="kw-head__sub">
                  <template x-if="presenceLabel(activeThread)">
                    <span class="kw-presence-text" :class="{'is-online': isPartnerOnline(activeThread)}" x-text="presenceLabel(activeThread)" aria-live="polite"></span>
                  </template>
                  <template x-if="presenceLabel(activeThread)"><span>&middot;</span></template>
                  <template x-if="activeThread.thread_type === 'group'">
                    <span><span x-text="((activeThread?.participants || []).length || (activeThread.participant_count || 2)) + ' family members'"></span> &middot; <button type="button" @click.stop="showGroupRosterModal = true" style="background:none; border:none; padding:0; color:var(--kw-primary); font-weight:700; text-decoration:underline; cursor:pointer;">View roster</button></span>
                  </template>
                  <template x-if="activeThread.thread_type !== 'group'">
                    <span x-text="activeThread.context_title || labelFor(activeThread.context_type)"></span>
                  </template>
                  <template x-if="activeThread.has_minor_participant"><span> &middot; <i class="bi bi-shield-check"></i> Supervised</span></template>
                </p>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
              <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" :class="{'is-active': showInChatSearch}" @click="toggleInChatSearch()" title="Search in conversation" aria-label="Search in conversation">
                <i class="bi bi-search"></i>
              </button>
              <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="openCallModal()" title="Start video or voice call with kin" aria-label="Start call">
                <i class="bi bi-telephone"></i>
              </button>
              <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="openInfoDrawer()" title="Conversation info, shared media & starred whispers" aria-label="Conversation info">
                <i class="bi bi-info-circle"></i>
              </button>
            </div>
          </div>

          {{-- WhatsApp-style In-Chat Message Search Bar --}}
          <div class="kw-in-chat-search" x-show="showInChatSearch" x-collapse x-cloak>
            <div class="kw-in-chat-search__inner">
              <input type="search" x-model="inChatSearchQuery" @input="executeInChatSearch()" @keydown.enter.prevent="nextMatch()" placeholder="Search in conversation…" x-ref="inChatSearchInput">
              <span class="kw-match-count" :class="{'kw-match-count--none': inChatSearchQuery && inChatMatches.length === 0}">
                <template x-if="inChatSearchQuery && inChatMatches.length > 0">
                  <span x-text="(currentMatchIndex + 1) + ' of ' + inChatMatches.length"></span>
                </template>
                <template x-if="inChatSearchQuery && inChatMatches.length === 0">
                  <span>0 matches</span>
                </template>
              </span>
              <button type="button" class="kw-match-nav" @click="prevMatch()" :disabled="inChatMatches.length === 0" title="Previous match"><i class="bi bi-chevron-up"></i></button>
              <button type="button" class="kw-match-nav" @click="nextMatch()" :disabled="inChatMatches.length === 0" title="Next match"><i class="bi bi-chevron-down"></i></button>
              <button type="button" class="kw-match-close" @click="closeInChatSearch()" title="Close search"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>

          <div class="kw-stream" id="kwStream" aria-live="polite">
            <template x-if="activeThread && (activeThread.context_title || activeThread.context_type)">
              <div class="kw-context-pill">
                <i class="bi bi-image"></i>
                <span>About the memory <strong x-text="'&ldquo;' + (activeThread.context_title || labelFor(activeThread.context_type)) + '&rdquo;'"></strong></span>
              </div>
            </template>
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
              <div class="kw-row" :class="{'is-mine': msg.sender_id === currentUserId}" :id="'msg_' + msg.id">
                <div class="kw-bubble__container" @click.outside="if (activeActionMsgId === msg.id) activeActionMsgId = null">
                  <div class="kw-bubble__actions" :class="{'is-open': activeReactionMsgId === msg.id || activeActionMsgId === msg.id}">
                    <button type="button" class="kw-react-trigger" @click.stop="setReplyTo(msg); activeActionMsgId = null" aria-label="Reply" title="Quote & reply">
                      <i class="bi bi-reply-fill"></i>
                    </button>
                    <button type="button" class="kw-react-trigger" @click.stop="toggleStar(msg); activeActionMsgId = null" :title="isMessageStarred(msg) ? 'Unstar whisper' : 'Star whisper'" :style="isMessageStarred(msg) ? 'color:var(--kw-gold-bright)' : ''">
                      <i class="bi" :class="isMessageStarred(msg) ? 'bi-star-fill' : 'bi-star'"></i>
                    </button>
                    <button type="button" class="kw-react-trigger" @click.stop="openReactionPopover(msg.id, $event)" aria-label="Add reaction" title="Add reaction">
                      <i class="bi bi-emoji-smile"></i>
                    </button>
                    <template x-if="msg.sender_id === currentUserId && canEditMessage(msg)">
                      <button type="button" class="kw-react-trigger" @click.stop="openEditModal(msg); activeActionMsgId = null" title="Edit whisper (within 15 minutes)" aria-label="Edit whisper">
                        <i class="bi bi-pencil-fill"></i>
                      </button>
                    </template>
                    <button type="button" class="kw-react-trigger kw-react-trigger--danger" @click.stop="openDeleteChoice(msg); activeActionMsgId = null" aria-label="Delete message" title="Delete whisper">
                      <i class="bi bi-trash3"></i>
                    </button>
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

                  <div class="kw-bubble" @click="activeActionMsgId = (activeActionMsgId === msg.id ? null : msg.id)">
                    <div class="kw-bubble__who" x-text="msg.sender_id === currentUserId ? 'You' : msg.sender_name"></div>

                    {{-- WhatsApp-style Quoted Reply Preview Box --}}
                    <template x-if="msg.context_metadata && msg.context_metadata.reply_to">
                      <div class="kw-quoted-box" @click="scrollToMessage(msg.context_metadata.reply_to.id)" title="Click to view quoted whisper">
                        <div class="kw-quoted-box__sender" x-text="msg.context_metadata.reply_to.sender_name || 'Relative'"></div>
                        <div class="kw-quoted-box__snippet" x-text="msg.context_metadata.reply_to.body || 'Attached media / Voice whisper'"></div>
                      </div>
                    </template>

                    {{-- WhatsApp-style Media Attachments --}}
                    <template x-if="msg.context_metadata && msg.context_metadata.attachments && msg.context_metadata.attachments.length > 0">
                      <div>
                        <template x-for="(att, aIdx) in msg.context_metadata.attachments" :key="aIdx">
                          <div>
                            <template x-if="attKind(att) === 'image'">
                              <div class="kw-att-image-wrap" @click="activeLightboxImg = att.url" title="Click to enlarge">
                                <img :src="att.url" :alt="att.original_name || att.name || 'Attached photograph'" class="kw-att-image" loading="lazy">
                              </div>
                            </template>
                            <template x-if="attKind(att) === 'video'">
                              <div style="position:relative; max-width:320px; margin-top:6px;">
                                <video controls preload="metadata" playsinline :src="att.url" class="kw-att-video"></video>
                              </div>
                            </template>
                            <template x-if="attKind(att) === 'doc'">
                              <a :href="att.url" target="_blank" download class="kw-att-doc">
                                <i class="bi" :class="(att.mime_type || att.mime) === 'application/pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-text-fill'"></i>
                                <div class="kw-att-doc__info">
                                  <span class="kw-att-doc__name" x-text="att.original_name || att.name || 'Document'"></span>
                                  <span class="kw-att-doc__size" x-text="formatFileSize(att.size_bytes || att.size)"></span>
                                </div>
                                <i class="bi bi-download" style="font-size:1rem; opacity:0.8;"></i>
                              </a>
                            </template>
                          </div>
                        </template>
                      </div>
                    </template>

                    {{-- Voice Whisper with 1.5x / 2x Speed Toggle --}}
                    <template x-if="msg.message_type === 'audio_whisper' && msg.audio_url">
                      <div class="kw-voice">
                        <div class="kw-voice__label">
                          <span><i class="bi bi-mic-fill"></i> Voice whisper</span>
                          <button type="button" class="kw-speed-btn" @click="cycleAudioSpeed($el)" title="Change playback speed (1x, 1.5x, 2x)">1x</button>
                        </div>
                        <audio controls preload="none" :src="msg.audio_url"></audio>
                      </div>
                    </template>

                    <template x-if="msg.body"><p class="kw-bubble__text" x-html="renderHighlightedBody(msg.body)"></p></template>

                    {{-- Rich OpenGraph Link Preview Card --}}
                    <template x-if="resolveMessageLinkPreview(msg)">
                      <a :href="resolveMessageLinkPreview(msg).url"
                         target="_blank"
                         rel="noopener noreferrer"
                         class="kw-link-preview"
                         @click.stop>
                        <template x-if="resolveMessageLinkPreview(msg).image">
                          <div class="kw-link-preview__media">
                            <img :src="resolveMessageLinkPreview(msg).image"
                                 :alt="resolveMessageLinkPreview(msg).title || 'Link preview image'"
                                 loading="lazy"
                                 x-on:error="$el.parentElement.style.display='none'">
                          </div>
                        </template>
                        <div class="kw-link-preview__body">
                          <div class="kw-link-preview__meta">
                            <span class="text-truncate" x-text="resolveMessageLinkPreview(msg).site_name || resolveMessageLinkPreview(msg).domain || 'Web Link'"></span>
                            <span class="kw-link-preview__cta" x-text="resolveMessageLinkPreview(msg).cta_label || 'Open Link →'"></span>
                          </div>
                          <div class="kw-link-preview__title" x-text="resolveMessageLinkPreview(msg).title || resolveMessageLinkPreview(msg).domain || resolveMessageLinkPreview(msg).url"></div>
                          <template x-if="resolveMessageLinkPreview(msg).description">
                            <div class="kw-link-preview__desc" x-text="resolveMessageLinkPreview(msg).description"></div>
                          </template>
                        </div>
                      </a>
                    </template>

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
                      <template x-if="isMessageStarred(msg)">
                        <span class="kw-star-badge" title="Starred whisper"><i class="bi bi-star-fill"></i></span>
                      </template>
                      <template x-if="msg.context_metadata && msg.context_metadata.is_edited">
                        <span class="kw-edited-badge" title="Edited whisper">Edited</span>
                      </template>
                      <template x-if="msg.expires_at">
                        <span class="kw-disappear-badge" :title="'Auto-disappears at ' + formatSeenTime(msg.expires_at)">
                          <i class="bi bi-hourglass-split"></i> <span x-text="formatTimeLeft(msg, nowTs)"></span>
                        </span>
                      </template>
                      <span x-text="formatShort(msg.created_at)"></span>
                      <template x-if="msg.sender_id === currentUserId">
                        <span class="kw-seen-badge" :title="msg.status === 'sending' ? 'Sending whisper…' : (msg.status === 'failed' ? 'Failed to deliver — tap to retry' : (msg.is_read ? ('Read at ' + formatSeenTime(msg.read_at)) : 'Delivered'))">
                          <template x-if="msg.status === 'sending'">
                            <span class="kw-seen--sending" style="font-size:0.75rem; color:#94a3b8; display:inline-flex; align-items:center; gap:4px;">
                              <span class="spinner-border spinner-border-sm" style="width:10px; height:10px; border-width:1.5px;" role="status"></span>
                              <span>Sending…</span>
                            </span>
                          </template>
                          <template x-if="msg.status === 'failed'">
                            <span class="kw-seen--failed" style="font-size:0.75rem; color:#ef4444; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" @click="retrySendMessage(msg)">
                              <i class="bi bi-arrow-clockwise"></i> <span>Retry</span>
                            </span>
                          </template>
                          <template x-if="!msg.status || msg.status === 'sent'">
                            <span :class="{'kw-double-check--blue': msg.is_read}" class="kw-double-check" :title="msg.is_read ? 'Read' : 'Delivered'" x-text="msg.is_read ? '✓✓' : '✓✓'"></span>
                          </template>
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

            {{-- WhatsApp-style Quoted Reply Banner in Composer --}}
            <div class="kw-quote-banner" x-show="replyingTo" x-cloak>
              <div class="kw-quote-banner__content">
                <div class="kw-quote-banner__sender" x-text="'Replying to ' + (replyingTo?.sender_id === currentUserId ? 'yourself' : replyingTo?.sender_name)"></div>
                <div class="kw-quote-banner__snippet" x-text="replyingTo?.body || 'Attachment / Voice whisper'"></div>
              </div>
              <button type="button" class="kw-quote-banner__close" @click="cancelReply()" title="Cancel reply" aria-label="Cancel reply"><i class="bi bi-x-lg"></i></button>
            </div>

            {{-- WhatsApp-style Pending Attachment Preview in Composer --}}
            <div class="kw-att-preview" x-show="pendingAttachment" x-cloak>
              <template x-if="pendingAttachment && pendingAttachment.type && pendingAttachment.type.startsWith('image/')">
                <img :src="pendingAttachmentThumb" class="kw-att-preview__thumb" alt="Preview">
              </template>
              <template x-if="pendingAttachment && pendingAttachment.type && pendingAttachment.type.startsWith('video/')">
                <div style="position:relative; width:44px; height:44px; border-radius:6px; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <video :src="pendingAttachmentThumb" style="width:100%; height:100%; object-fit:cover;" muted playsinline></video>
                  <i class="bi bi-play-circle-fill" style="position:absolute; color:#fff; font-size:1.1rem; pointer-events:none;"></i>
                </div>
              </template>
              <template x-if="!pendingAttachment || (!pendingAttachment.type?.startsWith('image/') && !pendingAttachment.type?.startsWith('video/'))">
                <i class="bi bi-file-earmark-text-fill kw-att-preview__icon"></i>
              </template>
              <div class="kw-att-preview__info">
                <span class="kw-att-preview__name" x-text="pendingAttachment?.name"></span>
                <span class="kw-att-preview__size" x-text="formatFileSize(pendingAttachment?.size)"></span>
              </div>
              <button type="button" class="kw-att-preview__remove" @click="removePendingAttachment()" title="Remove attachment" aria-label="Remove attachment"><i class="bi bi-x-circle-fill"></i></button>
            </div>

            {{-- Live Link Preview Card in Composer --}}
            <div class="kw-composer-link-preview" x-show="composerLinkPreview" x-cloak>
              <template x-if="composerLinkPreview && composerLinkPreview.image">
                <img :src="composerLinkPreview.image" class="kw-composer-link-preview__thumb" :alt="composerLinkPreview.title || 'Link preview'" x-on:error="$el.style.display='none'">
              </template>
              <div class="kw-composer-link-preview__info">
                <span class="kw-composer-link-preview__domain" x-text="composerLinkPreview?.site_name || composerLinkPreview?.domain || 'Web Link'"></span>
                <span class="kw-composer-link-preview__title" x-text="composerLinkPreview?.title || composerLinkPreview?.url"></span>
                <template x-if="composerLinkPreview?.description">
                  <span class="kw-composer-link-preview__desc" x-text="composerLinkPreview.description"></span>
                </template>
              </div>
              <button type="button" class="kw-att-preview__remove" @click="dismissComposerLinkPreview()" title="Dismiss link preview" aria-label="Dismiss link preview"><i class="bi bi-x-circle-fill"></i></button>
            </div>

            <form @submit.prevent="sendMessage()" class="kw-composer__form">
              @include('csrf')
              <label for="kw_body" class="visually-hidden">Your whisper</label>

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

              <div class="kw-composer__pill-bar">
                <div class="kw-composer__tools">
                  {{-- WhatsApp-style Attachment File Input & Button --}}
                  <input type="file" x-ref="mediaFileInput" @change="handleFileSelected($event)" style="display:none;" accept="image/*,video/*,application/pdf,audio/*,.docx,.txt">
                  <button type="button" class="kw-icon-tool" @click="$refs.mediaFileInput.click()" aria-label="Attach photo, video or document" title="Attach photo, video or document">
                    <i class="bi bi-paperclip" style="font-size:1.1rem;"></i>
                  </button>

                  {{-- Disappearing message timer toggle --}}
                  <div style="position:relative;">
                    <button type="button" class="kw-icon-tool"
                            :class="{'is-active': activeDisappearSeconds > 0}"
                            @click="showDisappearMenu = !showDisappearMenu"
                            :title="activeDisappearSeconds > 0 ? ('Auto-disappears in ' + disappearLabel(activeDisappearSeconds)) : 'Auto-disappear timer (Off)'"
                            aria-label="Auto-disappear message timer">
                      <i class="bi bi-clock" :style="activeDisappearSeconds > 0 ? 'color:#dc2626; font-weight:bold;' : ''"></i>
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
                </div>

                <div id="kw_body"
                     class="kw-textarea kw-editable-composer"
                     contenteditable="true"
                     role="textbox"
                     aria-multiline="true"
                     data-placeholder="Write a private message…"
                     @input="onComposerInput($event)"
                     @keydown.enter.prevent.exact="sendMessage()"></div>

                <div class="kw-composer__actions">
                  <button type="button" class="kw-icon-tool" :class="{'is-active': showEmojiDrawer}" @click="showEmojiDrawer = !showEmojiDrawer" aria-label="Emoji picker" title="Open family emoji palette">
                    <i class="bi bi-emoji-smile"></i>
                  </button>
                  <button type="button" class="kw-icon-tool" :class="{'is-live': isRecording}" @click="toggleAudioRecording()" :aria-label="isRecording ? 'Stop recording' : 'Record a voice whisper'" title="Record a voice whisper">
                    <i class="bi" :class="isRecording ? 'bi-stop-fill' : 'bi-mic'"></i>
                  </button>
                  <button type="submit" class="kw-send-circle" :disabled="!newMessageBody.trim() && !recordedAudioBlob && !pendingAttachment" aria-label="Send whisper" title="Send whisper">
                    <i class="bi bi-send-fill" style="font-size:0.9rem;"></i>
                  </button>
                </div>
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
          <button type="button" class="kw-btn kw-btn--primary" @click="openCompose()"><i class="bi bi-plus-lg"></i><span>New whisper</span></button>
        </div>
      </template>
    </div>

    {{-- 3rd Pane: Right Details Column (Desktop >= 1100px) --}}
    <aside class="kw-details-pane" aria-label="Conversation details">
      <template x-if="activeThread">
        <div style="display:flex; flex-direction:column; gap:22px; height:100%;">
          <div class="kw-details__profile">
            <div class="kw-details__avatar">
              <template x-if="activeThread.thread_type === 'group'">
                <i class="bi bi-people" style="font-size:1.75rem; color:var(--kw-primary);"></i>
              </template>
              <template x-if="activeThread.thread_type !== 'group'">
                <div>
                  <span x-text="initials(partnerName(activeThread))"></span>
                  <img :src="partnerAvatar(activeThread)" alt="" x-on:error="$el.remove()">
                </div>
              </template>
            </div>
            <h3 class="kw-details__name" x-text="partnerName(activeThread)"></h3>
            <p class="kw-details__sub" x-text="activeThread.context_title || labelFor(activeThread.context_type)"></p>
            <div class="kw-details__btns" x-show="activeThread.thread_type !== 'group'">
              <a :href="'/allMembers/seeProfile/' + encodeURIComponent(partnerId(activeThread))" class="kw-details__pill-btn">View profile</a>
              <a :href="'/organogram/' + encodeURIComponent(partnerId(activeThread)) + '?mode=canvas'" class="kw-details__pill-btn">Show in tree</a>
            </div>
            <div class="kw-details__btns" x-show="activeThread.thread_type === 'group'">
              <button type="button" class="kw-details__pill-btn" @click="showGroupRosterModal = true">View roster</button>
            </div>
          </div>

          <div>
            <div class="kw-details__sec-head">
              <h4 class="kw-details__sec-title">Shared here</h4>
              <button type="button" class="kw-details__sec-link" @click="openInfoDrawer()">See all</button>
            </div>
            <div class="kw-details__media-grid">
              <template x-for="(p, pIdx) in [...(threadMedia?.photos || []), ...(threadMedia?.videos || [])].slice(0, 6)" :key="'det_p_' + pIdx">
                <div class="kw-details__media-slot" @click="p.type === 'video' ? (activeLightboxVideo = p.url) : (activeLightboxImg = p.url)">
                  <template x-if="p.type === 'video'">
                    <div style="position:relative; width:100%; height:100%; background:#000;">
                      <video :src="p.url" preload="metadata" playsinline style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                      <i class="bi bi-play-circle-fill" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.1rem; background:rgba(0,0,0,0.3);"></i>
                    </div>
                  </template>
                  <template x-if="p.type !== 'video'">
                    <img :src="p.url" alt="" loading="lazy">
                  </template>
                </div>
              </template>
              <template x-if="(threadMedia?.photos || []).length === 0 && (threadMedia?.videos || []).length === 0">
                <template x-for="slotIdx in [1, 2, 3, 4, 5, 6]" :key="'placeholder_' + slotIdx">
                  <div class="kw-details__media-slot" @click="openInfoDrawer()">
                    <span>Media</span>
                  </div>
                </template>
              </template>
            </div>
          </div>

          <div>
            <div class="kw-details__sec-head">
              <h4 class="kw-details__sec-title">Options</h4>
            </div>
            <div class="kw-details__opt-list">
              <button type="button" class="kw-details__opt" @click="showInfoDrawer = true; infoDrawerTab = 'starred'">
                <span class="kw-details__opt-left"><i class="bi bi-star"></i><span>Starred whispers</span></span>
                <span style="font-size:0.75rem; color:var(--kw-muted);" x-text="starredMessages.length || ''"></span>
              </button>
              <button type="button" class="kw-details__opt" @click="showDisappearMenu = !showDisappearMenu">
                <span class="kw-details__opt-left"><i class="bi bi-clock"></i><span>Disappearing messages</span></span>
                <span style="font-size:0.75rem; color:var(--kw-muted);" x-text="activeDisappearSeconds > 0 ? disappearShort(activeDisappearSeconds) : 'Off'"></span>
              </button>
              <button type="button" class="kw-details__opt" @click="toggleMuteThread()">
                <span class="kw-details__opt-left"><i class="bi" :class="activeThread.is_muted ? 'bi-bell-slash' : 'bi-bell'"></i><span x-text="activeThread.is_muted ? 'Unmute notifications' : 'Mute notifications'"></span></span>
              </button>
            </div>
          </div>

          <div class="kw-details__privacy-card">
            <i class="bi bi-shield-check"></i>
            <div><strong>Private &amp; supervised.</strong> Only the people in this whisper can read it.</div>
          </div>
        </div>
      </template>
      <template x-if="!activeThread">
        <div class="kw-details__privacy-card" style="margin-top:0;">
          <i class="bi bi-shield-check"></i>
          <div><strong>Private &amp; supervised.</strong> Only the people in this whisper can read it.</div>
        </div>
      </template>
    </aside>
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

  {{-- WhatsApp-style Delete Whisper Modal (Delete for Everyone vs Delete for Me) --}}
  <div class="kw-modal" x-show="pendingDeleteMsg !== null" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_delete_modal_title"
       @keydown.escape.window="cancelDeleteMessage()" @click.self="cancelDeleteMessage()">
    <div class="kw-modal__card" x-trap.noscroll="pendingDeleteMsg !== null" style="max-width:440px;">
      <div class="kw-modal__head">
        <h2 id="kw_delete_modal_title"><i class="bi bi-trash3-fill" style="color:#dc2626"></i> Delete Whisper</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="cancelDeleteMessage()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <p style="font-size:0.9375rem; line-height:1.6; color:var(--kw-ink); margin-bottom:16px;">
        Choose whether to remove this whisper for yourself only, or for everyone in the family conversation.
      </p>
      <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
        <template x-if="pendingDeleteMsg && pendingDeleteMsg.sender_id === currentUserId">
          <button type="button" class="kw-btn kw-btn--primary" style="background:#dc2626; border-color:#dc2626; justify-content:center;" @click="executeDeleteMessage('everyone')" :disabled="isDeletingMsg">
            <i class="bi bi-trash3-fill"></i> <span>Delete for Everyone</span>
          </button>
        </template>
        <button type="button" class="kw-btn kw-btn--ghost" style="justify-content:center;" @click="executeDeleteMessage('me')" :disabled="isDeletingMsg">
          <i class="bi bi-person-x"></i> <span>Delete for Me</span>
        </button>
      </div>
      <div class="kw-modal__foot">
        <button type="button" class="kw-btn kw-btn--ghost" @click="cancelDeleteMessage()" :disabled="isDeletingMsg">Cancel</button>
      </div>
    </div>
  </div>

  {{-- WhatsApp-style Edit Whisper Modal (Within 15 minutes) --}}
  <div class="kw-modal" x-show="editingMessage !== null" x-cloak role="dialog" aria-modal="true" aria-labelledby="kw_edit_modal_title"
       @keydown.escape.window="cancelEditMessage()" @click.self="cancelEditMessage()">
    <div class="kw-modal__card" x-trap.noscroll="editingMessage !== null" style="max-width:460px;">
      <div class="kw-modal__head">
        <h2 id="kw_edit_modal_title"><i class="bi bi-pencil-fill" style="color:var(--kw-primary)"></i> Edit Whisper</h2>
        <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="cancelEditMessage()" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <form @submit.prevent="submitEditMessage()">
        @include('csrf')
        <div class="kw-field">
          <label for="kw_edit_body">Whisper text</label>
          <textarea id="kw_edit_body" class="kw-textarea" rows="3" x-model="editMessageBody" required></textarea>
          <p style="font-size:0.75rem; color:var(--kw-muted); margin-top:4px;">Whispers can be edited within 15 minutes of sending.</p>
        </div>
        <div class="kw-modal__foot">
          <button type="button" class="kw-btn kw-btn--ghost" @click="cancelEditMessage()" :disabled="isSubmittingEdit">Cancel</button>
          <button type="submit" class="kw-btn kw-btn--primary" data-spin="true" :disabled="!editMessageBody.trim() || isSubmittingEdit">
            <span x-text="isSubmittingEdit ? 'Saving…' : 'Save Changes'"></span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- WhatsApp-style Lightbox Image & Video Viewer --}}
  <div class="kw-lightbox" x-show="activeLightboxImg || activeLightboxVideo" @click="activeLightboxImg = null; activeLightboxVideo = null;" x-cloak>
    <template x-if="activeLightboxImg">
      <img :src="activeLightboxImg" class="kw-lightbox__img" @click.stop alt="Full media preview">
    </template>
    <template x-if="activeLightboxVideo">
      <video :src="activeLightboxVideo" class="kw-lightbox__video" controls autoplay playsinline @click.stop></video>
    </template>
    <button type="button" class="kw-lightbox__close" @click="activeLightboxImg = null; activeLightboxVideo = null;" aria-label="Close"><i class="bi bi-x-lg"></i></button>
  </div>

  {{-- WhatsApp-style Contact Details & Media Drawer --}}
  <div class="kw-drawer-backdrop" x-show="showInfoDrawer" @click="showInfoDrawer = false" x-cloak></div>
  <aside class="kw-drawer" x-show="showInfoDrawer" x-cloak aria-label="Contact details and shared media">
    <div class="kw-drawer__head">
      <h3>Conversation Info</h3>
      <button type="button" class="kw-btn kw-btn--ghost kw-btn--square" @click="showInfoDrawer = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="kw-drawer__body" x-show="activeThread">
      <div class="kw-drawer__profile">
        <div class="kw-avatar kw-avatar--large">
          <template x-if="activeThread?.thread_type === 'group'">
            <i class="bi bi-people-fill" style="font-size:2rem; color:#ffd700;"></i>
          </template>
          <template x-if="activeThread?.thread_type !== 'group'">
            <div>
              <span x-text="initials(partnerName(activeThread))"></span>
              <img :src="partnerAvatar(activeThread)" alt="" x-on:error="$el.remove()">
            </div>
          </template>
        </div>
        <h4 x-text="partnerName(activeThread)"></h4>
        <p style="font-size:0.8125rem; color:var(--kw-muted); margin:0;" x-text="activeThread?.context_title || labelFor(activeThread?.context_type)"></p>
      </div>

      <div class="kw-drawer__actions">
        <button type="button" class="kw-drawer-action" @click="togglePinThread()">
          <i class="bi" :class="activeThread?.is_pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle'" :style="activeThread?.is_pinned ? 'color:var(--kw-gold-bright)' : ''"></i>
          <span x-text="activeThread?.is_pinned ? 'Unpin' : 'Pin'"></span>
        </button>
        <button type="button" class="kw-drawer-action" @click="toggleMuteThread()">
          <i class="bi" :class="activeThread?.is_muted ? 'bi-bell-slash-fill' : 'bi-bell'" :style="activeThread?.is_muted ? 'color:var(--kw-primary)' : ''"></i>
          <span x-text="activeThread?.is_muted ? 'Unmute' : 'Mute'"></span>
        </button>
        <button type="button" class="kw-drawer-action" @click="showInChatSearch = true; showInfoDrawer = false; $nextTick(() => $refs.inChatSearchInput?.focus())">
          <i class="bi bi-search"></i>
          <span>Search</span>
        </button>
      </div>

      <div class="kw-privacy-row">
        <div class="kw-privacy-row__copy">
          <p class="kw-privacy-row__title" id="kwPresenceLabel">Show online &amp; last seen</p>
          <p class="kw-privacy-row__hint" x-text="myPresencePrivacy === 'everyone' ? 'Your kin can see when you are online or were last seen.' : 'Hidden. You will not see anyone else\'s status either.'"></p>
        </div>
        <button type="button" role="switch" class="kw-switch" aria-labelledby="kwPresenceLabel"
                :aria-checked="myPresencePrivacy === 'everyone' ? 'true' : 'false'"
                :disabled="savingPresencePrivacy" @click="togglePresencePrivacy()">
          <span class="kw-switch__track"></span>
        </button>
      </div>

      <div class="kw-drawer-tabs">
        <button type="button" class="kw-drawer-tab" :class="{'is-active': infoDrawerTab === 'media'}" @click="infoDrawerTab = 'media'">Media &amp; Docs</button>
        <button type="button" class="kw-drawer-tab" :class="{'is-active': infoDrawerTab === 'starred'}" @click="infoDrawerTab = 'starred'">Starred</button>
        <template x-if="activeThread?.thread_type === 'group'">
          <button type="button" class="kw-drawer-tab" :class="{'is-active': infoDrawerTab === 'members'}" @click="infoDrawerTab = 'members'">Members</button>
        </template>
      </div>

      {{-- Media & Docs Tab Content --}}
      <div x-show="infoDrawerTab === 'media'">
        <div x-show="(threadMedia?.photos || []).length > 0" style="margin-bottom:16px;">
          <h5 style="font-size:0.75rem; text-transform:uppercase; color:var(--kw-muted); margin-bottom:8px;">Photographs (<span x-text="(threadMedia?.photos || []).length"></span>)</h5>
          <div class="kw-media-grid">
            <template x-for="(p, pIdx) in (threadMedia?.photos || [])" :key="pIdx">
              <div class="kw-media-thumb" @click="activeLightboxImg = p.url" title="View photograph">
                <img :src="p.url" alt="" loading="lazy">
              </div>
            </template>
          </div>
        </div>

        <div x-show="(threadMedia?.videos || []).length > 0" style="margin-bottom:16px;">
          <h5 style="font-size:0.75rem; text-transform:uppercase; color:var(--kw-muted); margin-bottom:8px;">Videos (<span x-text="(threadMedia?.videos || []).length"></span>)</h5>
          <div class="kw-media-grid">
            <template x-for="(v, vIdx) in (threadMedia?.videos || [])" :key="vIdx">
              <div class="kw-media-thumb kw-media-thumb--video" @click="activeLightboxVideo = v.url" title="Play video">
                <video :src="v.url" preload="metadata" playsinline style="width:100%; height:100%; object-fit:cover; pointer-events:none;"></video>
                <div class="kw-video-overlay-icon"><i class="bi bi-play-circle-fill"></i></div>
              </div>
            </template>
          </div>
        </div>

        <div x-show="(threadMedia?.documents || threadMedia?.docs || []).length > 0">
          <h5 style="font-size:0.75rem; text-transform:uppercase; color:var(--kw-muted); margin-bottom:8px;">Documents (<span x-text="(threadMedia?.documents || threadMedia?.docs || []).length"></span>)</h5>
          <div class="kw-docs-list">
            <template x-for="(doc, dIdx) in (threadMedia?.documents || threadMedia?.docs || [])" :key="dIdx">
              <a :href="doc.url" target="_blank" download class="kw-att-doc">
                <i class="bi" :class="doc.mime_type === 'application/pdf' ? 'bi-file-earmark-pdf-fill' : 'bi-file-earmark-text-fill'"></i>
                <div class="kw-att-doc__info">
                  <span class="kw-att-doc__name" x-text="doc.name"></span>
                  <span class="kw-att-doc__size" x-text="formatFileSize(doc.size)"></span>
                </div>
                <i class="bi bi-download" style="font-size:1rem; opacity:0.8;"></i>
              </a>
            </template>
          </div>
        </div>

        <div x-show="(threadMedia?.photos || []).length === 0 && (threadMedia?.videos || []).length === 0 && (threadMedia?.documents || threadMedia?.docs || []).length === 0" class="kw-drawer-empty">
          <i class="bi bi-images" style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;"></i>
          No media or documents shared in this conversation yet.
        </div>
      </div>

      {{-- Starred Tab Content --}}
      <div x-show="infoDrawerTab === 'starred'">
        <div class="kw-starred-list" x-show="starredMessages.length > 0">
          <template x-for="sm in starredMessages" :key="sm.id">
            <div class="kw-starred-item" @click="scrollToMessage(sm.id); showInfoDrawer = false;" title="Click to view in conversation">
              <div class="kw-starred-item__who" x-text="sm.sender_id === currentUserId ? 'You' : sm.sender_name"></div>
              <div class="kw-starred-item__body" x-text="sm.body || (sm.message_type === 'audio_whisper' ? 'Voice whisper' : 'Attached media')"></div>
              <div class="kw-starred-item__time" x-text="formatShort(sm.created_at)"></div>
            </div>
          </template>
        </div>
        <div x-show="starredMessages.length === 0" class="kw-drawer-empty">
          <i class="bi bi-star" style="font-size:2rem; opacity:0.5; display:block; margin-bottom:8px;"></i>
          No starred whispers in this conversation yet.
        </div>
      </div>

      {{-- Members Tab Content (for group threads) --}}
      <template x-if="activeThread?.thread_type === 'group'">
        <div x-show="infoDrawerTab === 'members'">
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
      </template>
    </div>
  </aside>

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
        <div class="kw-call-overlay__status" role="status" aria-live="polite">
          <i class="bi bi-record-circle" :style="callConnectionState === 'connected' ? 'color:#22c55e;' : 'color:#ffd700;'"></i>
          <span x-text="callStatusText()"></span>
        </div>
      </div>
      <button type="button" class="kw-call-ctrl-btn kw-call-ctrl-btn--end" @click="endCall()" aria-label="End call" title="End call">
        <i class="bi bi-telephone-x-fill"></i>
      </button>
    </div>

    <div class="kw-call-overlay__body">
      {{-- Dedicated Remote Audio Element for Audio & Video Call Streams --}}
      <audio id="kwRemoteAudio" autoplay playsinline style="display:none;"></audio>

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
    threadCache: {},
    kin: @json($kin ?? []),
    pane: @json(!empty($selected_thread) && !empty($_GET['thread_id']) ? 'chat' : 'list'),
    searchQuery: '',
    threadFilter: 'all',
    newMessageBody: '',
    isSubmitting: false,
    isCreating: false,
    composeMode: 'direct',
    newGroupTitle: '',
    selectedGroupMembers: [],
    showCallModal: false,
    isPopupMode: document.documentElement.classList.contains('kw-popup'),
    canInstall: false,
    installPromptEvent: null,
    showGroupRosterModal: false,
    showShareRequestModal: false,
    isRequestingShare: false,
    isRespondingShare: false,
    pendingDeleteMsg: null,
    isDeletingMsg: false,
    replyingTo: null,
    pendingAttachment: null,
    pendingAttachmentThumb: null,
    linkPreviewCache: {},
    linkPreviewPending: {},
    composerLinkPreview: null,
    composerLinkPreviewUrl: '',
    composerDismissedLinkUrl: '',
    composerLinkTimer: null,
    activeLightboxImg: null,
    activeLightboxVideo: null,
    showInfoDrawer: false,
    infoDrawerTab: 'media',
    threadMedia: { photos: [], videos: [], documents: [], audio: [] },
    showInChatSearch: false,
    inChatSearchQuery: '',
    inChatMatches: [],
    currentMatchIndex: -1,
    editingMessage: null,
    editMessageBody: '',
    isSubmittingEdit: false,
    threadEvents: [],
    lastScreenshotTime: 0,
    candidateBuffer: [],
    candidateFlushTimer: null,
    pendingOffer: null,
    pendingOfferSenderId: null,
    pendingCandidates: [],
    isInCall: false,
    isCallRinging: false,
    callConnectionState: 'connecting',
    activeActionMsgId: null,
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
    presence: {},
    myPresencePrivacy: @json($presencePrivacy ?? 'everyone'),
    savingPresencePrivacy: false,
    presenceTimer: null,

    init() {
      if (this.serverNowIso) {
        const sTs = this.parseUtcTimestamp(this.serverNowIso);
        if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
      }
      if (this.activeThread && this.messages) {
        if (!this.threadCache) this.threadCache = {};
        this.threadCache[this.activeThread.id] = [...this.messages];
      }
      this.scrollToBottom();
      this.startPolling();
      this.startCountdownLoop();
      this.startPresence();
      this.initPusher();
      this.initScreenshotDetection();
      this.initInstallAndBadge();
      if (this.activeThread) {
        this.fetchThreadEvents();
        this.loadThreadMedia();
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
      this.scheduleComposerLinkPreview(this.newMessageBody);
    },

    clearComposer() {
      this.newMessageBody = '';
      const el = document.getElementById('kw_body');
      if (el) el.innerHTML = '';
      if (this.composerLinkTimer) clearTimeout(this.composerLinkTimer);
      this.composerLinkPreview = null;
      this.composerLinkPreviewUrl = '';
      this.composerDismissedLinkUrl = '';
    },

    isSafeHttpUrl(url) {
      return typeof url === 'string' && /^https?:\/\//i.test(url.trim());
    },

    extractFirstUrl(text) {
      if (!text || typeof text !== 'string') return null;
      const m = text.match(/(https?:\/\/[^\s<>"'`]+)/i);
      if (!m || !m[1]) return null;
      const cleaned = m[1].replace(/:[a-z0-9_]+_3d:.*$/i, '').replace(/[.,;:!?)]+$/, '');
      return this.isSafeHttpUrl(cleaned) ? cleaned : null;
    },

    scheduleComposerLinkPreview(text) {
      const url = this.extractFirstUrl(text);
      if (!url) {
        if (this.composerLinkTimer) clearTimeout(this.composerLinkTimer);
        this.composerLinkPreview = null;
        this.composerLinkPreviewUrl = '';
        return;
      }
      if (url === this.composerDismissedLinkUrl || url === this.composerLinkPreviewUrl) {
        return;
      }
      if (url in this.linkPreviewCache) {
        const cached = this.linkPreviewCache[url];
        this.composerLinkPreview = cached;
        this.composerLinkPreviewUrl = url;
        return;
      }
      if (this.composerLinkTimer) clearTimeout(this.composerLinkTimer);
      this.composerLinkTimer = setTimeout(async () => {
        const preview = await this.fetchLinkPreviewData(url);
        if (this.extractFirstUrl(this.newMessageBody) === url && url !== this.composerDismissedLinkUrl) {
          this.composerLinkPreview = preview;
          this.composerLinkPreviewUrl = url;
        }
      }, 380);
    },

    dismissComposerLinkPreview() {
      this.composerDismissedLinkUrl = this.composerLinkPreviewUrl || this.extractFirstUrl(this.newMessageBody) || '';
      this.composerLinkPreview = null;
      this.composerLinkPreviewUrl = '';
    },

    async fetchLinkPreviewData(url) {
      if (!this.isSafeHttpUrl(url)) return null;
      if (url in this.linkPreviewCache) return this.linkPreviewCache[url];
      if (this.linkPreviewPending[url]) return null;
      this.linkPreviewPending = { ...this.linkPreviewPending, [url]: true };
      try {
        const res = await fetch('/api/link-preview?url=' + encodeURIComponent(url), {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
          this.linkPreviewCache = { ...this.linkPreviewCache, [url]: null };
          return null;
        }
        const json = await res.json();
        const d = (json?.status === 'success' && json?.data && (json.data.title || json.data.image || json.data.domain))
          ? json.data
          : null;
        const safePreview = (d && this.isSafeHttpUrl(d.url || url)) ? { ...d, url: d.url || url } : null;
        this.linkPreviewCache = { ...this.linkPreviewCache, [url]: safePreview };
        return safePreview;
      } catch (e) {
        this.linkPreviewCache = { ...this.linkPreviewCache, [url]: null };
        return null;
      } finally {
        const nextPending = { ...this.linkPreviewPending };
        delete nextPending[url];
        this.linkPreviewPending = nextPending;
      }
    },

    resolveMessageLinkPreview(msg) {
      const embedded = msg?.context_metadata?.link_preview;
      if (embedded && (embedded.title || embedded.image || embedded.domain) && this.isSafeHttpUrl(embedded.url)) {
        return embedded;
      }
      const url = this.extractFirstUrl(msg?.body);
      if (!url) return null;
      if (url in this.linkPreviewCache) {
        return this.linkPreviewCache[url];
      }
      if (!this.linkPreviewPending[url]) {
        this.fetchLinkPreviewData(url);
      }
      return null;
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
      const escHtml = (s) => {
        const div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
      };
      const renderPlainSegment = (seg) => {
        let safe = escHtml(seg);
        safe = safe.replace(/:([a-z0-9_]+_3d):/g, (match, p1) => {
          return `<img src="/img/emoji/${p1}.svg" class="atg-3d-emoji" width="28" height="28" alt="${p1}" onerror="this.src='/img/emoji/${p1}.png'" style="vertical-align:middle; display:inline-block; margin:0 2px;">`;
        });
        safe = safe.replace(/:me_([a-z]+)@([a-zA-Z0-9_-]+):/g, (match, r, uid) => {
          return `<img src="/api/emoji/avatar-sticker/${encodeURIComponent(uid)}/${encodeURIComponent(r)}" class="atg-3d-emoji" width="48" height="48" alt="Me sticker" style="vertical-align:middle; display:inline-block; margin:2px;">`;
        });
        return safe.replace(/\n/g, '<br>');
      };

      const urlRegex = /(https?:\/\/[^\s<>"'`]+)/gi;
      let out = '';
      let lastIdx = 0;
      let match;
      while ((match = urlRegex.exec(text)) !== null) {
        if (match.index > lastIdx) {
          out += renderPlainSegment(text.slice(lastIdx, match.index));
        }
        let rawUrl = match[1];
        let trailing = '';
        const gluedEmoji = rawUrl.match(/(:[a-z0-9_]+_3d:.*)$/i);
        if (gluedEmoji) {
          trailing = gluedEmoji[1] + trailing;
          rawUrl = rawUrl.slice(0, -gluedEmoji[1].length);
        }
        const punctMatch = rawUrl.match(/([.,;:!?)]+)$/);
        if (punctMatch) {
          trailing = punctMatch[1] + trailing;
          rawUrl = rawUrl.slice(0, -punctMatch[1].length);
        }
        if (this.isSafeHttpUrl(rawUrl)) {
          const safeHref = escHtml(rawUrl);
          out += `<a href="${safeHref}" target="_blank" rel="noopener noreferrer" class="kw-bubble__link" onclick="event.stopPropagation()">${safeHref}</a>`;
        } else {
          out += renderPlainSegment(rawUrl);
        }
        if (trailing) {
          out += renderPlainSegment(trailing);
        }
        lastIdx = urlRegex.lastIndex;
      }
      if (lastIdx < text.length) {
        out += renderPlainSegment(text.slice(lastIdx));
      }
      return out;
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
      const idx = this.messages.findIndex(m => m.id === msgId);
      let previousReactions = null;
      if (idx > -1) {
        const msg = this.messages[idx];
        if (!msg.context_metadata) msg.context_metadata = {};
        if (!msg.context_metadata.reactions) msg.context_metadata.reactions = {};
        previousReactions = JSON.parse(JSON.stringify(msg.context_metadata.reactions));
        let list = Array.isArray(msg.context_metadata.reactions[emoji]) ? [...msg.context_metadata.reactions[emoji]] : [];
        if (list.includes(this.currentUserId)) {
          list = list.filter(u => u !== this.currentUserId);
        } else {
          list.push(this.currentUserId);
        }
        if (list.length > 0) {
          msg.context_metadata.reactions[emoji] = list;
        } else {
          delete msg.context_metadata.reactions[emoji];
        }
      }
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(msgId) + '/reaction', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ emoji })
        });
        const data = await res.json();
        if (data.success && data.message) {
          const target = this.messages.findIndex(m => m.id === msgId);
          if (target > -1) {
            this.messages[target].context_metadata = data.message.context_metadata;
          }
        } else if (idx > -1 && previousReactions !== null) {
          this.messages[idx].context_metadata.reactions = previousReactions;
          this.toast(data.error || 'Could not update reaction.');
        }
      } catch (e) {
        if (idx > -1 && previousReactions !== null) {
          this.messages[idx].context_metadata.reactions = previousReactions;
        }
      }
    },

    openDeleteChoice(msg) {
      if (!msg) return;
      this.activeReactionMsgId = null;
      this.pendingDeleteMsg = msg;
    },

    confirmDeleteMessage(msg) {
      if (!msg) return;
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

    async executeDeleteMessage(mode = 'everyone') {
      if (!this.pendingDeleteMsg || this.isDeletingMsg) return;
      const targetMsg = this.pendingDeleteMsg;
      this.isDeletingMsg = true;
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(targetMsg.id), {
          method: 'DELETE',
          headers: jsonHeaders(),
          body: JSON.stringify({ token: csrf(), mode: mode })
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
          this.toast(mode === 'everyone' ? 'Whisper deleted for everyone.' : 'Whisper deleted for you.');
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

    canEditMessage(msg) {
      if (!msg || msg.sender_id !== this.currentUserId) return false;
      if (msg.message_type === 'audio_whisper') return false;
      const createdTs = new Date(String(msg.created_at).replace(' ', 'T')).getTime();
      if (isNaN(createdTs)) return false;
      return (Date.now() - createdTs) < 15 * 60 * 1000;
    },

    openEditModal(msg) {
      this.activeReactionMsgId = null;
      this.editingMessage = msg;
      this.editMessageBody = msg.body || '';
    },

    cancelEditMessage() {
      this.editingMessage = null;
      this.editMessageBody = '';
      this.isSubmittingEdit = false;
    },

    async submitEditMessage() {
      if (!this.editingMessage || !this.editMessageBody.trim() || this.isSubmittingEdit) return;
      this.isSubmittingEdit = true;
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(this.editingMessage.id), {
          method: 'PATCH',
          headers: jsonHeaders(),
          body: JSON.stringify({ body: this.editMessageBody.trim() })
        });
        const data = await res.json();
        if (data.success && data.message) {
          const idx = this.messages.findIndex(m => m.id === this.editingMessage.id);
          if (idx > -1) {
            this.messages[idx].body = data.message.body;
            this.messages[idx].context_metadata = data.message.context_metadata;
          }
          this.cancelEditMessage();
          this.toast('Whisper edited successfully.');
        } else {
          this.toast(data.error || 'Could not edit whisper.');
        }
      } catch (e) {
        this.toast('Network error while editing whisper.');
      } finally {
        this.isSubmittingEdit = false;
      }
    },

    isMessageStarred(msg) {
      if (!msg || !msg.context_metadata || !Array.isArray(msg.context_metadata.starred_by)) return false;
      return msg.context_metadata.starred_by.includes(this.currentUserId);
    },

    async toggleStar(msg) {
      if (!msg) return;
      if (!msg.context_metadata) msg.context_metadata = {};
      if (!Array.isArray(msg.context_metadata.starred_by)) msg.context_metadata.starred_by = [];
      const isStarred = msg.context_metadata.starred_by.includes(this.currentUserId);
      if (isStarred) {
        msg.context_metadata.starred_by = msg.context_metadata.starred_by.filter(id => id !== this.currentUserId);
      } else {
        msg.context_metadata.starred_by.push(this.currentUserId);
      }
      try {
        const res = await fetch('/api/kin-whispers/messages/' + encodeURIComponent(msg.id) + '/star', {
          method: 'POST',
          headers: jsonHeaders()
        });
        const data = await res.json();
        if (data.success && data.message) {
          msg.context_metadata = data.message.context_metadata;
        }
      } catch (e) {
        console.warn('Star toggle error', e);
      }
    },

    setReplyTo(msg) {
      this.activeReactionMsgId = null;
      this.replyingTo = msg;
      this.$nextTick(() => {
        const el = document.getElementById('kw_body');
        if (el) el.focus();
      });
    },

    cancelReply() {
      this.replyingTo = null;
    },

    handleFileSelected(event) {
      const files = event.target.files;
      if (!files || files.length === 0) return;
      const file = files[0];
      if (file.size > 15 * 1024 * 1024) {
        this.toast('Attachment exceeds maximum allowed size of 15MB.');
        event.target.value = '';
        return;
      }
      this.pendingAttachment = file;
      if (file.type && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => { this.pendingAttachmentThumb = e.target.result; };
        reader.readAsDataURL(file);
      } else if (file.type && file.type.startsWith('video/')) {
        this.pendingAttachmentThumb = URL.createObjectURL(file);
      } else {
        this.pendingAttachmentThumb = null;
      }
      event.target.value = '';
    },

    removePendingAttachment() {
      if (this.pendingAttachmentThumb && this.pendingAttachment && this.pendingAttachment.type && this.pendingAttachment.type.startsWith('video/')) {
        URL.revokeObjectURL(this.pendingAttachmentThumb);
      }
      this.pendingAttachment = null;
      this.pendingAttachmentThumb = null;
    },

    formatFileSize(bytes) {
      if (!bytes || isNaN(bytes)) return '';
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    },

    cycleAudioSpeed(btn) {
      const voiceContainer = btn.closest('.kw-voice');
      if (!voiceContainer) return;
      const audio = voiceContainer.querySelector('audio');
      if (!audio) return;
      let rate = audio.playbackRate || 1.0;
      if (rate === 1.0) {
        rate = 1.5;
      } else if (rate === 1.5) {
        rate = 2.0;
      } else {
        rate = 1.0;
      }
      audio.playbackRate = rate;
      btn.textContent = rate + 'x';
    },

    scrollToMessage(msgId) {
      if (!msgId) return;
      const el = document.getElementById('msg_' + msgId);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        el.style.transition = 'background-color 0.4s ease';
        const origBg = el.style.backgroundColor;
        el.style.backgroundColor = 'rgba(201, 162, 75, 0.25)';
        setTimeout(() => { el.style.backgroundColor = origBg; }, 1800);
      }
    },

    async togglePinThread() {
      if (!this.activeThread) return;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/pin', {
          method: 'POST',
          headers: jsonHeaders()
        });
        const data = await res.json();
        if (data.success) {
          this.activeThread.is_pinned = data.is_pinned;
          const idx = this.threads.findIndex(t => t.id === this.activeThread.id);
          if (idx > -1) {
            this.threads[idx].is_pinned = data.is_pinned;
            this.sortThreads();
          }
          this.toast(data.is_pinned ? 'Conversation pinned to top.' : 'Conversation unpinned.');
        }
      } catch (e) {
        console.warn('Pin toggle error', e);
      }
    },

    async toggleMuteThread() {
      if (!this.activeThread) return;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/mute', {
          method: 'POST',
          headers: jsonHeaders()
        });
        const data = await res.json();
        if (data.success) {
          this.activeThread.is_muted = data.is_muted;
          const idx = this.threads.findIndex(t => t.id === this.activeThread.id);
          if (idx > -1) {
            this.threads[idx].is_muted = data.is_muted;
          }
          this.toast(data.is_muted ? 'Notifications muted for this conversation.' : 'Notifications unmuted.');
        }
      } catch (e) {
        console.warn('Mute toggle error', e);
      }
    },

    sortThreads() {
      this.threads.sort((a, b) => {
        const aPin = a.is_pinned ? 1 : 0;
        const bPin = b.is_pinned ? 1 : 0;
        if (aPin !== bPin) return bPin - aPin;
        const aTime = a.last_message_at ? new Date(String(a.last_message_at).replace(' ', 'T')).getTime() : 0;
        const bTime = b.last_message_at ? new Date(String(b.last_message_at).replace(' ', 'T')).getTime() : 0;
        return bTime - aTime;
      });
    },

    async openInfoDrawer() {
      if (!this.activeThread) return;
      this.showInfoDrawer = true;
      this.infoDrawerTab = 'media';
      await this.loadThreadMedia();
    },

    async loadThreadMedia() {
      if (!this.activeThread) return;
      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/media', {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success && data.media) {
          this.threadMedia = {
            photos: data.media.photos || [],
            videos: data.media.videos || [],
            documents: data.media.documents || data.media.docs || [],
            docs: data.media.documents || data.media.docs || [],
            audio: data.media.audio || []
          };
        }
      } catch (e) {
        console.warn('Load media error', e);
      }
    },

    get starredMessages() {
      return (this.messages || []).filter(m => this.isMessageStarred(m));
    },

    toggleInChatSearch() {
      this.showInChatSearch = !this.showInChatSearch;
      if (this.showInChatSearch) {
        this.$nextTick(() => {
          const inp = this.$refs.inChatSearchInput;
          if (inp) inp.focus();
        });
      } else {
        this.closeInChatSearch();
      }
    },

    closeInChatSearch() {
      this.showInChatSearch = false;
      this.inChatSearchQuery = '';
      this.inChatMatches = [];
      this.currentMatchIndex = -1;
    },

    executeInChatSearch() {
      const q = (this.inChatSearchQuery || '').trim().toLowerCase();
      if (!q) {
        this.inChatMatches = [];
        this.currentMatchIndex = -1;
        return;
      }
      this.inChatMatches = (this.messages || []).filter(m => (m.body || '').toLowerCase().includes(q)).map(m => m.id);
      if (this.inChatMatches.length > 0) {
        this.currentMatchIndex = 0;
        this.scrollToMessage(this.inChatMatches[0]);
      } else {
        this.currentMatchIndex = -1;
      }
    },

    nextMatch() {
      if (this.inChatMatches.length === 0) return;
      this.currentMatchIndex = (this.currentMatchIndex + 1) % this.inChatMatches.length;
      this.scrollToMessage(this.inChatMatches[this.currentMatchIndex]);
    },

    prevMatch() {
      if (this.inChatMatches.length === 0) return;
      this.currentMatchIndex = (this.currentMatchIndex - 1 + this.inChatMatches.length) % this.inChatMatches.length;
      this.scrollToMessage(this.inChatMatches[this.currentMatchIndex]);
    },

    renderHighlightedBody(text) {
      if (!text) return '';
      const q = (this.inChatSearchQuery || '').trim();
      let rendered = this.renderMessageBody(text);
      if (q && q.length > 0) {
        const regex = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        rendered = rendered.replace(/(<[^>]+>)|([^<]+)/g, (m, tag, txt) => {
          return tag ? tag : txt.replace(regex, '<mark class="kw-highlight">$1</mark>');
        });
      }
      return rendered;
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
          const inFlightOptimistic = (this.messages || []).filter(m => m.id && String(m.id).startsWith('temp_'));
          const hadNewMessages = (validMessages.length + inFlightOptimistic.length) > this.messages.length;
          this.messages = [...validMessages, ...inFlightOptimistic];
          if (!this.threadCache) this.threadCache = {};
          this.threadCache[pollingThreadId] = this.messages;
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
      let list = this.threads || [];
      if (this.threadFilter === 'unread') {
        list = list.filter(t => (parseInt(t.unread_count, 10) || 0) > 0);
      } else if (this.threadFilter === 'groups') {
        list = list.filter(t => t.thread_type === 'group');
      } else if (this.threadFilter === 'starred') {
        list = list.filter(t => Boolean(t.is_pinned || t.has_starred));
      }
      if (!q) return list;
      return list.filter(t => this.partnerName(t).toLowerCase().includes(q) || (t.context_title || '').toLowerCase().includes(q));
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
    // Attachments exist in two shapes: optimistic (mime_type/original_name/size_bytes) and server-persisted
    // (mime/name/size/type). Normalise here so media never vanishes after the server sync.
    attKind(att) {
      if (!att) return 'doc';
      const m = String(att.mime_type || att.mime || '').toLowerCase();
      const t = String(att.type || '').toLowerCase();
      const u = String(att.url || '').toLowerCase();
      if (m.startsWith('image/') || t === 'image' || /\.(jpe?g|png|gif|webp|heic)$/i.test(u)) return 'image';
      if (m.startsWith('video/') || t === 'video' || /\.(mp4|webm|mov|m4v|ogv)$/i.test(u)) return 'video';
      return 'doc';
    },
    partnerId(t) {
      t = t || this.activeThread;
      if (!t || t.thread_type === 'group') return '';
      return String(t.sender_id === this.currentUserId ? t.recipient_id : t.sender_id);
    },
    presenceOf(t) {
      const id = this.partnerId(t);
      return id ? (this.presence[id] || null) : null;
    },
    isPartnerOnline(t) {
      const p = this.presenceOf(t);
      return !!(p && !p.is_hidden && p.is_online);
    },
    presenceLabel(t) {
      const p = this.presenceOf(t);
      if (!p || p.is_hidden) return '';
      if (p.is_online) return 'Online';
      if (!p.last_seen_ts) return '';
      const d = new Date(p.last_seen_ts * 1000);
      const time = d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
      const now = new Date();
      const yesterday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
      if (d.toDateString() === now.toDateString()) return 'Last seen today at ' + time;
      if (d.toDateString() === yesterday.toDateString()) return 'Last seen yesterday at ' + time;
      return 'Last seen ' + d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) + ' at ' + time;
    },
    startPresence() {
      if (this.presenceTimer) clearInterval(this.presenceTimer);
      this.syncPresence();
      this.presenceTimer = setInterval(() => { if (!document.hidden) this.syncPresence(); }, 30000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden) this.syncPresence(); });
    },
    async syncPresence() {
      const ids = [...new Set((this.threads || []).map(t => this.partnerId(t)).filter(Boolean))];
      try {
        const res = await fetch('/api/kin-whispers/presence/sync', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ user_ids: ids })
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data && data.success) {
          this.presence = data.presence || {};
          if (data.privacy) this.myPresencePrivacy = data.privacy;
        }
      } catch (e) {
        console.warn('Presence sync error', e);
      }
    },
    async togglePresencePrivacy() {
      if (this.savingPresencePrivacy) return;
      const next = this.myPresencePrivacy === 'everyone' ? 'nobody' : 'everyone';
      this.savingPresencePrivacy = true;
      try {
        const res = await fetch('/api/kin-whispers/presence/privacy', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ last_seen_privacy: next })
        });
        const data = await res.json();
        if (data.success) {
          this.myPresencePrivacy = data.last_seen_privacy;
          this.toast(next === 'everyone' ? 'Online status and last seen are now visible to your kin.' : 'Online status and last seen are now hidden.');
          this.syncPresence();
        } else {
          this.toast(data.error || 'Could not update your privacy setting.');
        }
      } catch (e) {
        this.toast('Could not update your privacy setting.');
      } finally {
        this.savingPresencePrivacy = false;
      }
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
      this.pane = 'chat';
      this.clearComposer();
      this.discardRecordedAudio();
      if (!this.threadCache) this.threadCache = {};
      if (this.threadCache[thread.id]) {
        this.messages = this.threadCache[thread.id];
        this.scrollToBottom();
      } else {
        this.messages = [];
      }
      this.threadEvents = [];
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
          const validMessages = (data.messages || []).filter(m => {
            if (!m.expires_at) return true;
            const target = this.parseUtcTimestamp(m.expires_at_iso || m.expires_at);
            return isNaN(target) || target > currentTs;
          });
          const pendingOptimistic = (this.messages || []).filter(m => m.id && String(m.id).startsWith('temp_'));
          this.messages = [...validMessages, ...pendingOptimistic];
          this.threadCache[thread.id] = this.messages;
          const i = this.threads.findIndex(t => t.id === thread.id);
          if (i > -1) {
            this.threads[i] = data.thread;
            this.threads[i].unread_count = 0;
          }
          this.fetchThreadEvents();
          this.loadThreadMedia();
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
      if (!this.activeThread) return;
      this.newMessageBody = this.getComposerText();
      const text = this.newMessageBody.trim();
      if (!text && !this.recordedAudioBlob && !this.pendingAttachment) return;

      const sendingThreadId = this.activeThread.id;
      const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
      const currentTs = new Date().toISOString();
      const outgoingText = text;
      const outgoingDisappear = this.activeDisappearSeconds > 0 ? this.activeDisappearSeconds : null;
      const hasAudio = Boolean(this.recordedAudioBlob);
      const audioBlobToUpload = this.recordedAudioBlob;
      const audioDuration = Math.max(1, Math.min(60, this.recordingSeconds || 1));
      const audioLocalUrl = this.recordedAudioUrl;
      const attachmentToUpload = this.pendingAttachment;
      const attachmentThumb = this.pendingAttachmentThumb;
      const replyContext = this.replyingTo ? {
        id: this.replyingTo.id,
        sender_name: this.replyingTo.sender_id === this.currentUserId ? 'You' : this.replyingTo.sender_name,
        body: (this.replyingTo.body || (this.replyingTo.message_type === 'audio_whisper' ? 'Voice whisper' : 'Attachment')).substring(0, 100)
      } : null;

      const optimisticAttachments = attachmentToUpload ? [{
        url: attachmentThumb || (attachmentToUpload.type?.startsWith('video/') ? URL.createObjectURL(attachmentToUpload) : ''),
        original_name: attachmentToUpload.name,
        mime_type: attachmentToUpload.type,
        size_bytes: attachmentToUpload.size
      }] : [];

      const outgoingLinkPreview = (this.composerLinkPreview && this.extractFirstUrl(outgoingText) === this.composerLinkPreviewUrl)
        ? this.composerLinkPreview
        : null;

      // 1. INSTANT OPTIMISTIC RENDER (Perceived 0ms Latency)
      const optimisticMsg = {
        id: tempId,
        thread_id: sendingThreadId,
        sender_id: this.currentUserId,
        sender_name: 'You',
        sender_avatar: this.partnerAvatar({ sender_id: this.currentUserId }) || '/resources/images/profile/avatarM.png',
        recipient_id: this.activeThread.thread_type === 'direct'
          ? (this.activeThread.sender_id === this.currentUserId ? this.activeThread.recipient_id : this.activeThread.sender_id)
          : null,
        message_type: hasAudio ? 'audio_whisper' : 'text',
        body: outgoingText,
        audio_url: audioLocalUrl || null,
        audio_duration_seconds: hasAudio ? audioDuration : null,
        created_at: currentTs,
        is_read: 0,
        status: 'sending',
        is_optimistic: true,
        expires_at: outgoingDisappear ? new Date(Date.now() + outgoingDisappear * 1000).toISOString() : null,
        context_metadata: {
          attachments: optimisticAttachments,
          reply_to: replyContext,
          ...(outgoingLinkPreview ? { link_preview: outgoingLinkPreview } : {})
        }
      };

      this.messages.push(optimisticMsg);
      if (!this.threadCache) this.threadCache = {};
      this.threadCache[sendingThreadId] = this.messages;

      // Immediately update active thread preview and pin thread to top of list
      const previewText = outgoingText || (hasAudio ? 'Voice whisper' : (attachmentToUpload ? '📎 ' + attachmentToUpload.name : 'Whisper'));
      if (this.activeThread) {
        this.activeThread.last_message_preview = previewText;
        this.activeThread.last_message_at = currentTs;
      }
      const tIdx = this.threads.findIndex(t => t.id === sendingThreadId);
      if (tIdx > -1) {
        this.threads[tIdx].last_message_preview = previewText;
        this.threads[tIdx].last_message_at = currentTs;
        if (tIdx > 0 && !this.threads[0].is_pinned) {
          const [moved] = this.threads.splice(tIdx, 1);
          this.threads.unshift(moved);
        }
      }

      // Immediately clear composer, reply, attachment and scroll
      this.clearComposer();
      this.discardRecordedAudio();
      this.cancelReply();
      this.removePendingAttachment();
      this.scrollToBottom();

      // 2. BACKGROUND SERVER SYNC
      try {
        let uploadedAudioUrl = null;
        if (hasAudio && audioBlobToUpload) {
          const fd = new FormData();
          fd.append('audio', audioBlobToUpload, 'whisper.webm');
          const upRes = await fetch('/api/kin-whispers/audio-upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf() },
            body: fd
          });
          const up = await upRes.json();
          if (!up.success) {
            this.markMessageFailed(tempId, up.error || 'Voice upload failed.');
            return;
          }
          uploadedAudioUrl = up.audio.url;
        }

        let uploadedAttachments = [];
        if (attachmentToUpload) {
          const fdMedia = new FormData();
          fdMedia.append('media', attachmentToUpload);
          const upMediaRes = await fetch('/api/kin-whispers/media-upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf() },
            body: fdMedia
          });
          const upMedia = await upMediaRes.json();
          if (!upMedia.success) {
            this.markMessageFailed(tempId, upMedia.error || 'Attachment upload failed.');
            return;
          }
          uploadedAttachments.push(upMedia.attachment);
        }

        const res = await fetch('/api/kin-whispers/messages', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            thread_id: sendingThreadId,
            body: outgoingText,
            message_type: uploadedAudioUrl ? 'audio_whisper' : 'text',
            audio_url: uploadedAudioUrl,
            audio_duration: uploadedAudioUrl ? audioDuration : null,
            disappear_seconds: outgoingDisappear,
            attachments: uploadedAttachments,
            reply_to: replyContext
          })
        });
        const data = await res.json();
        if (data.success && data.message) {
          if (data.server_time) {
            const sTs = this.parseUtcTimestamp(data.server_time);
            if (sTs > 0) this.serverClockOffset = Date.now() - sTs;
          }
          // Smoothly replace optimistic message with server-authoritative record
          const idx = this.messages.findIndex(m => m.id === tempId);
          if (idx > -1) {
            this.messages.splice(idx, 1, { ...data.message, status: 'sent' });
          }
          this.threadCache[sendingThreadId] = this.messages;
        } else {
          this.markMessageFailed(tempId, data.error || 'Could not send whisper.');
        }
      } catch (err) {
        this.markMessageFailed(tempId, 'Network error. Tap to retry.');
      }
    },

    markMessageFailed(tempId, errorMsg) {
      const idx = this.messages.findIndex(m => m.id === tempId);
      if (idx > -1) {
        this.messages[idx].status = 'failed';
        this.messages[idx].error_message = errorMsg;
      }
      this.toast(errorMsg || 'Could not send whisper.');
    },

    async retrySendMessage(failedMsg) {
      if (!failedMsg || !this.activeThread) return;
      failedMsg.status = 'sending';
      try {
        const res = await fetch('/api/kin-whispers/messages', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            thread_id: failedMsg.thread_id,
            body: failedMsg.body,
            message_type: failedMsg.message_type || 'text',
            audio_url: failedMsg.audio_url,
            audio_duration: failedMsg.audio_duration_seconds
          })
        });
        const data = await res.json();
        if (data.success && data.message) {
          const idx = this.messages.findIndex(m => m.id === failedMsg.id);
          if (idx > -1) {
            this.messages.splice(idx, 1, { ...data.message, status: 'sent' });
          }
          if (this.threadCache) {
            this.threadCache[this.activeThread.id] = this.messages;
          }
          this.toast('Whisper sent successfully.');
        } else {
          failedMsg.status = 'failed';
          this.toast(data.error || 'Retry failed.');
        }
      } catch (e) {
        failedMsg.status = 'failed';
        this.toast('Retry failed. Please check your connection.');
      }
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

    popOutWhisperWindow() {
      const threadPart = this.activeThread ? '&thread_id=' + encodeURIComponent(this.activeThread.id) : '';
      const win = window.open(
        '/kin-whispers?mode=popup' + threadPart,
        'KinWhispersPopup',
        'popup=yes,width=440,height=760,menubar=no,toolbar=no,location=no,status=no'
      );
      if (!win) {
        this.toast('Your browser blocked the pop-out window. Please allow pop-ups for this site.');
        return;
      }
      win.focus();
    },

    initInstallAndBadge() {
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        this.installPromptEvent = e;
        this.canInstall = true;
      });
      window.addEventListener('appinstalled', () => {
        this.installPromptEvent = null;
        this.canInstall = false;
        this.toast('Kin Whispers installed. Look for it on your home screen or dock.');
      });
      this.$watch('threads', () => this.updateAppBadge());
      this.updateAppBadge();

      const params = new URLSearchParams(window.location.search);
      if (params.get('compose') === '1') this.openCompose();
    },

    async installWhispersApp() {
      if (!this.installPromptEvent) {
        this.toast('Use your browser menu and choose "Install app" or "Add to Home Screen".');
        return;
      }
      this.installPromptEvent.prompt();
      try { await this.installPromptEvent.userChoice; } catch (e) {}
      this.installPromptEvent = null;
      this.canInstall = false;
    },

    updateAppBadge() {
      if (!('setAppBadge' in navigator)) return;
      const total = (this.threads || []).reduce((sum, t) => sum + (parseInt(t.unread_count, 10) || 0), 0);
      const action = total > 0 ? navigator.setAppBadge(total) : navigator.clearAppBadge();
      if (action && action.catch) action.catch(() => {});
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

    checkMediaSupport() {
      if (typeof window !== 'undefined' && window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        this.toast('WebRTC calls require a secure HTTPS connection. Please access via HTTPS or localhost.');
        return false;
      }
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        this.toast('Media devices are not supported on this browser or connection.');
        return false;
      }
      return true;
    },

    formatMediaError(err, type) {
      console.error('Media stream acquisition error', err);
      const isVideo = type === 'video';
      if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
        return isVideo
          ? 'Camera and microphone permissions were denied. Please enable them in your browser or device settings.'
          : 'Microphone permission was denied. Please enable microphone access in your browser or device settings.';
      }
      if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
        return isVideo
          ? 'No camera or microphone was found on this device.'
          : 'No microphone was found on this device.';
      }
      if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
        return 'Your audio or video hardware is currently in use by another application.';
      }
      if (err.name === 'OverconstrainedError') {
        return 'Requested media settings are not supported by your hardware.';
      }
      return isVideo
        ? 'Could not access camera or microphone: ' + (err.message || 'Permission or hardware error')
        : 'Could not access microphone: ' + (err.message || 'Permission or hardware error');
    },

    attachRemoteStream(stream) {
      this.remoteStream = stream;
      this.$nextTick(() => {
        const remoteAudio = document.getElementById('kwRemoteAudio');
        const remoteVid = document.getElementById('kwRemoteVideo');

        if (this.callType === 'video') {
          if (remoteVid) {
            remoteVid.srcObject = stream;
            remoteVid.play().catch(e => console.warn('Remote video playback warning', e));
          }
          if (remoteAudio) {
            remoteAudio.srcObject = null;
          }
        } else {
          if (remoteAudio) {
            remoteAudio.srcObject = stream;
            remoteAudio.play().catch(e => console.warn('Remote audio playback warning', e));
          }
          if (remoteVid) {
            remoteVid.srcObject = null;
          }
        }
      });
    },

    createPeerConnection(targetThreadId, targetUserId) {
      if (this.peerConnection) {
        try { this.peerConnection.close(); } catch (e) {}
      }

      const pc = new RTCPeerConnection({
        iceServers: [
          { urls: ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302', 'stun:stun2.l.google.com:19302'] }
        ]
      });

      if (this.localStream) {
        this.localStream.getTracks().forEach(track => {
          pc.addTrack(track, this.localStream);
        });
      }

      pc.ontrack = (event) => {
        const stream = (event.streams && event.streams[0]) ? event.streams[0] : new MediaStream([event.track]);
        this.attachRemoteStream(stream);
        if (event.track) {
          event.track.onunmute = () => {
            this.markCallConnected();
          };
        }
        if (pc.connectionState === 'connected' || pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
          this.markCallConnected();
        }
      };

      const handleConnStateChange = () => {
        const cState = pc.connectionState;
        const iState = pc.iceConnectionState;
        if (cState === 'connected' || iState === 'connected' || iState === 'completed') {
          this.markCallConnected();
        } else if (cState === 'disconnected' || cState === 'failed' || iState === 'disconnected' || iState === 'failed' || cState === 'connecting' || iState === 'checking') {
          this.callConnectionState = 'connecting';
        }
      };
      pc.onconnectionstatechange = handleConnStateChange;
      pc.oniceconnectionstatechange = handleConnStateChange;

      pc.onicecandidate = (event) => {
        if (event.candidate) {
          this.queueIceCandidate(event.candidate, targetThreadId, targetUserId);
        } else {
          this.flushIceCandidates(targetThreadId, targetUserId);
        }
      };

      this.peerConnection = pc;
      return pc;
    },

    async initiateInAppCall(type) {
      if (!this.activeThread) return;
      if (!this.checkMediaSupport()) return;

      this.callType = type;
      this.callPartnerName = this.partnerName(this.activeThread);
      this.isInCall = true;
      this.isCallRinging = true;
      this.callConnectionState = 'connecting';
      this.callDurationSeconds = 0;
      this.isMicMuted = false;
      this.isCameraOff = false;
      this.pendingOffer = null;
      this.pendingOfferSenderId = null;
      this.pendingCandidates = [];

      let stream;
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          audio: true,
          video: type === 'video'
        });
      } catch (mediaErr) {
        this.toast(this.formatMediaError(mediaErr, type));
        this.endCall();
        return;
      }

      this.localStream = stream;
      this.$nextTick(() => {
        const localVid = document.getElementById('kwLocalVideo');
        if (localVid) localVid.srcObject = stream;
      });

      const partnerId = this.activeThread.sender_id === this.currentUserId ? this.activeThread.recipient_id : this.activeThread.sender_id;
      const pc = this.createPeerConnection(this.activeThread.id, partnerId);

      try {
        const res = await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/initiate', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({ call_type: type })
        });
        const data = await res.json();
        if (data.success && data.call) {
          this.callRoomId = data.call.room_id;
        } else if (!data.success) {
          this.toast(data.error || 'Could not initiate call.');
          this.endCall();
          return;
        }

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);

        await fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/signal', {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            target_user_id: partnerId || undefined,
            signal: { sdp: offer }
          })
        });
      } catch (err) {
        console.error('Call signaling error', err);
        this.toast('Failed to connect call with server. Please check your network.');
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
      this.callConnectionState = 'connecting';
      this.callDurationSeconds = 0;

      if (!this.checkMediaSupport()) {
        this.endCall();
        return;
      }

      let stream;
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          audio: true,
          video: this.callType === 'video'
        });
      } catch (e) {
        this.toast(this.formatMediaError(e, this.callType));
        this.endCall();
        return;
      }

      this.localStream = stream;
      this.$nextTick(() => {
        const localVid = document.getElementById('kwLocalVideo');
        if (localVid) localVid.srcObject = stream;
      });

      const pc = this.createPeerConnection(callData.thread_id, callData.caller_id);

      const targetThread = this.threads.find(t => t.id === callData.thread_id);
      if (targetThread && (!this.activeThread || this.activeThread.id !== callData.thread_id)) {
        this.selectThread(targetThread);
      }

      if (this.pendingOffer) {
        try {
          await pc.setRemoteDescription(new RTCSessionDescription(this.pendingOffer));
          const answer = await pc.createAnswer();
          await pc.setLocalDescription(answer);

          const senderId = this.pendingOfferSenderId || callData.caller_id;
          fetch('/api/kin-whispers/threads/' + encodeURIComponent(callData.thread_id) + '/call/signal', {
            method: 'POST',
            headers: jsonHeaders(),
            body: JSON.stringify({ target_user_id: senderId, signal: { sdp: answer } })
          });

          this.pendingOffer = null;
          this.pendingOfferSenderId = null;

          if (this.pendingCandidates.length > 0) {
            for (const cand of this.pendingCandidates) {
              try {
                await pc.addIceCandidate(new RTCIceCandidate(cand));
              } catch (cErr) {
                console.warn('Buffered candidate application skipped', cErr);
              }
            }
            this.pendingCandidates = [];
          }
        } catch (signalErr) {
          console.error('Failed to answer buffered offer', signalErr);
          this.toast('Failed to negotiate call connection.');
          this.endCall();
        }
      }
    },

    declineCall() {
      this.stopRingtone();
      this.pendingOffer = null;
      this.pendingOfferSenderId = null;
      this.pendingCandidates = [];
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
      this.pendingOffer = null;
      this.pendingOfferSenderId = null;
      this.pendingCandidates = [];
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
        this.peerConnection.onconnectionstatechange = null;
        this.peerConnection.oniceconnectionstatechange = null;
        try { this.peerConnection.close(); } catch (e) {}
        this.peerConnection = null;
      }
      if (this.localStream) {
        try { this.localStream.getTracks().forEach(t => t.stop()); } catch (e) {}
        this.localStream = null;
      }
      if (this.remoteStream) {
        try { this.remoteStream.getTracks().forEach(t => t.stop()); } catch (e) {}
        this.remoteStream = null;
      }

      const remoteAudio = document.getElementById('kwRemoteAudio');
      if (remoteAudio) {
        remoteAudio.pause();
        remoteAudio.srcObject = null;
      }
      const remoteVid = document.getElementById('kwRemoteVideo');
      if (remoteVid) {
        remoteVid.pause();
        remoteVid.srcObject = null;
      }
      const localVid = document.getElementById('kwLocalVideo');
      if (localVid) {
        localVid.pause();
        localVid.srcObject = null;
      }

      if (this.activeThread) {
        fetch('/api/kin-whispers/threads/' + encodeURIComponent(this.activeThread.id) + '/call/end', {
          method: 'POST',
          headers: jsonHeaders()
        });
      }
      this.isInCall = false;
      this.isCallRinging = false;
      this.callConnectionState = 'connecting';
      this.callDurationSeconds = 0;
      this.incomingCall = null;
    },

    markCallConnected() {
      this.isCallRinging = false;
      if (this.callConnectionState !== 'connected') {
        this.callConnectionState = 'connected';
        if (!this.callDurationTimer) {
          this.startCallTimer();
        }
      }
    },

    callStatusText() {
      if (this.callConnectionState === 'connected') {
        return 'Connected • ' + this.formatCallDuration(this.callDurationSeconds);
      }
      return 'Trying to connect…';
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
        audioTracks.forEach(track => { track.enabled = !this.isMicMuted; });
      }
    },

    toggleMuteVideo() {
      if (!this.localStream) return;
      const videoTracks = this.localStream.getVideoTracks();
      if (videoTracks.length > 0) {
        this.isCameraOff = !this.isCameraOff;
        videoTracks.forEach(track => { track.enabled = !this.isCameraOff; });
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
            this.pendingOffer = null;
            this.pendingOfferSenderId = null;
            this.pendingCandidates = [];
            this.playRingtone();
          }
        });

        this.userChannel.bind('kin-call-signal', async (data) => {
          if (!data || !data.signal) return;
          try {
            if (data.signal.sdp) {
              const sdp = data.signal.sdp;
              if (sdp.type === 'offer') {
                if (this.peerConnection && this.isInCall) {
                  await this.peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
                  const answer = await this.peerConnection.createAnswer();
                  await this.peerConnection.setLocalDescription(answer);
                  const threadId = (this.activeThread ? this.activeThread.id : (this.incomingCall ? this.incomingCall.thread_id : data.thread_id));
                  if (threadId) {
                    fetch('/api/kin-whispers/threads/' + encodeURIComponent(threadId) + '/call/signal', {
                      method: 'POST',
                      headers: jsonHeaders(),
                      body: JSON.stringify({ target_user_id: data.sender_id, signal: { sdp: answer } })
                    });
                  }
                } else {
                  this.pendingOffer = sdp;
                  this.pendingOfferSenderId = data.sender_id;
                }
              } else if (sdp.type === 'answer') {
                if (this.peerConnection) {
                  await this.peerConnection.setRemoteDescription(new RTCSessionDescription(sdp));
                  this.isCallRinging = false;
                  if (this.peerConnection.connectionState === 'connected' || this.peerConnection.iceConnectionState === 'connected' || this.peerConnection.iceConnectionState === 'completed') {
                    this.markCallConnected();
                  } else {
                    this.callConnectionState = 'connecting';
                  }
                  if (this.pendingCandidates.length > 0) {
                    for (const cand of this.pendingCandidates) {
                      try {
                        await this.peerConnection.addIceCandidate(new RTCIceCandidate(cand));
                      } catch (cErr) {
                        console.warn('Buffered ICE candidate skipped', cErr);
                      }
                    }
                    this.pendingCandidates = [];
                  }
                }
              }
            } else if (data.signal.candidate || Array.isArray(data.signal.candidates)) {
              const cands = data.signal.candidate ? [data.signal.candidate] : data.signal.candidates;
              for (const cand of cands) {
                if (!cand) continue;
                if (this.peerConnection && this.peerConnection.remoteDescription) {
                  try {
                    await this.peerConnection.addIceCandidate(new RTCIceCandidate(cand));
                  } catch (candErr) {
                    console.warn('ICE candidate addition skipped', candErr);
                  }
                } else {
                  this.pendingCandidates.push(cand);
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
