@extends('layouts.profileBase')

@section('title', 'Festive Cards Studio — FamilyPlatform')
@section('meta_description', 'Design couture greeting cards for every celebration — Christmas, Eid, Diwali, birthdays, weddings, remembrance or your own occasion — and send them as living envelopes.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Great+Vibes&family=Playfair+Display:wght@400;500;600&display=swap">
<link rel="stylesheet" href="/public/css/festive-cards.css?v={{ assetVersion('css/festive-cards.css') }}">
<style>
.fs-wrap { --fs-gold: #c9a24b; --fs-gold-2: #f3dc9a; --fs-ink: #17120c; --fs-line: #e8e1d4; --fs-surface: #ffffff; --fs-muted: #6b6257; --fs-soft: #faf7f1;
    max-width: 1240px; margin: 0 auto; padding: 8px 16px 72px; color: var(--fs-ink); }
.dark-mode .fs-wrap { --fs-ink: #f4efe6; --fs-line: #2d2a26; --fs-surface: #18171a; --fs-muted: #a79f94; --fs-soft: #201e22; }

/* Hero */
.fs-hero { position: relative; overflow: hidden; border-radius: 28px; padding: 28px 24px; margin-bottom: 24px; color: #fbf6ea;
    background: radial-gradient(80% 120% at 85% 10%, rgba(201,162,75,.35), transparent 60%), radial-gradient(60% 90% at 0% 100%, rgba(122,15,42,.6), transparent 70%), linear-gradient(135deg, #0c0a0e 0%, #1d1418 55%, #2a0f19 100%);
    box-shadow: 0 30px 60px -30px rgba(0,0,0,.6); }
.fs-hero::after { content: ''; position: absolute; inset: 10px; border: 1px solid rgba(243,220,154,.28); border-radius: 20px; pointer-events: none; }
.fs-hero__eyebrow { font-family: 'Cinzel', Georgia, serif; font-size: .72rem; letter-spacing: .32em; text-transform: uppercase; color: var(--fs-gold-2); margin-bottom: 10px; }
.fs-hero h1 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; font-size: clamp(1.7rem, 3.6vw, 2.6rem); line-height: 1.12; margin: 0 0 10px; color: #fff; letter-spacing: -.01em; }
.fs-hero h1 em { font-family: 'Great Vibes', cursive; font-style: normal; font-weight: 400; font-size: 1.25em; background: linear-gradient(110deg,#f3dc9a,#c9a24b 40%,#fff1c4 60%,#c9a24b); -webkit-background-clip: text; background-clip: text; color: transparent; padding: 0 .08em; }
.fs-hero p { color: rgba(251,246,234,.78); max-width: 620px; font-size: .98rem; margin: 0; }
.fs-hero__row { position: relative; z-index: 1; display: flex; gap: 20px; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; }
.fs-pill-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 10px 18px; border-radius: 999px; border: 1px solid rgba(243,220,154,.45); background: rgba(255,255,255,.06); color: #fbf6ea; font-weight: 600; font-size: .9rem; white-space: nowrap; cursor: pointer; transition: background .2s; }
.fs-pill-btn:hover { background: rgba(255,255,255,.14); color: #fff; }

/* Layout */
.fs-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; }
@media (min-width: 992px) { .fs-grid { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); gap: 32px; } }
.fs-preview-col { order: -1; }
@media (min-width: 992px) { .fs-preview-col { order: 2; } }
.fs-stage { position: sticky; top: 84px; border-radius: 28px; padding: 28px 22px 20px; overflow: hidden;
    background: radial-gradient(70% 55% at 50% 30%, rgba(201,162,75,.22), transparent 70%), radial-gradient(120% 80% at 50% 120%, #2a1a10, transparent 70%), #0e0c10;
    box-shadow: inset 0 0 0 1px rgba(243,220,154,.14), 0 30px 60px -36px rgba(0,0,0,.7); }
@media (max-width: 991.98px) { .fs-stage { position: relative; top: 0; padding: 20px 16px 16px; } }
.fs-stage__card { max-width: 380px; margin: 0 auto; }
@media (max-width: 575.98px) { .fs-stage__card { max-width: 300px; } }
.fs-stage__meta { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
.fs-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; font-size: .76rem; font-weight: 600; color: #efe6d4; background: rgba(255,255,255,.07); border: 1px solid rgba(243,220,154,.22); }
.fs-stage__hint { text-align: center; color: rgba(239,230,212,.55); font-size: .76rem; margin-top: 10px; }

.fs-panel { background: var(--fs-surface); border: 1px solid var(--fs-line); border-radius: 24px; padding: 22px 18px; box-shadow: 0 20px 40px -32px rgba(23,18,12,.35); }
@media (min-width: 768px) { .fs-panel { padding: 28px 28px; } }
.fs-step { padding-bottom: 26px; margin-bottom: 26px; border-bottom: 1px solid var(--fs-line); }
.fs-step:last-of-type { border-bottom: 0; margin-bottom: 8px; }
.fs-step__head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 14px; }
.fs-step__num { font-family: 'Cinzel', Georgia, serif; font-size: .78rem; color: var(--fs-gold); letter-spacing: .12em; flex: none; }
.fs-step__title { font-family: 'Playfair Display', Georgia, serif; font-size: 1.22rem; font-weight: 500; margin: 0; color: var(--fs-ink); }
.fs-step__sub { color: var(--fs-muted); font-size: .84rem; margin: -6px 0 14px; }

/* Occasion tiles */
.fs-occasions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
@media (min-width: 576px) { .fs-occasions { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (min-width: 1200px) { .fs-occasions { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.fs-occ { display: flex; align-items: center; gap: 10px; min-height: 56px; padding: 10px 12px; border-radius: 16px; border: 1.5px solid var(--fs-line); background: var(--fs-soft); color: var(--fs-ink); text-align: left; cursor: pointer; transition: border-color .2s, transform .2s, box-shadow .2s; font: inherit; }
.fs-occ:hover { transform: translateY(-1px); border-color: #d9c38f; }
.fs-occ[aria-pressed="true"] { border-color: var(--fs-gold); background: linear-gradient(135deg, rgba(243,220,154,.28), rgba(201,162,75,.10)); box-shadow: 0 8px 20px -12px rgba(201,162,75,.8); }
.fs-occ__emoji { font-size: 1.35rem; line-height: 1; flex: none; }
.fs-occ__name { font-size: .84rem; font-weight: 600; line-height: 1.2; }
.fs-occ--custom { border-style: dashed; border-color: #cdb47a; background: transparent; grid-column: 1 / -1; }
.fs-occ--custom .fs-occ__name { font-family: 'Playfair Display', Georgia, serif; font-size: .98rem; font-weight: 500; }
.fs-occ--custom small { display: block; color: var(--fs-muted); font-size: .76rem; font-weight: 400; margin-top: 2px; }
.fs-custom { margin-top: 14px; padding: 18px; border-radius: 18px; border: 1px solid #e3d3ab; background: linear-gradient(160deg, rgba(243,220,154,.18), rgba(255,255,255,0)); }
.dark-mode .fs-custom { border-color: #4a3f2a; }
.fs-moods { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-top: 10px; }
@media (min-width: 768px) { .fs-moods { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.fs-mood { min-height: 64px; padding: 10px 12px; border-radius: 14px; border: 1.5px solid var(--fs-line); background: var(--fs-surface); text-align: left; cursor: pointer; font: inherit; color: var(--fs-ink); }
.fs-mood[aria-pressed="true"] { border-color: var(--fs-gold); box-shadow: 0 6px 16px -10px rgba(201,162,75,.9); }
.fs-mood b { display: block; font-size: .84rem; }
.fs-mood span { display: block; font-size: .72rem; color: var(--fs-muted); line-height: 1.3; margin-top: 2px; }

/* Style tiles (live mini cards) */
.fs-styles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
@media (min-width: 576px) { .fs-styles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (min-width: 1200px) { .fs-styles { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.fs-style { padding: 10px 10px 12px; border-radius: 18px; border: 1.5px solid var(--fs-line); background: var(--fs-soft); cursor: pointer; text-align: left; font: inherit; color: var(--fs-ink); transition: transform .2s, border-color .2s, box-shadow .2s; }
.fs-style:hover { transform: translateY(-2px); }
.fs-style[aria-pressed="true"] { border-color: var(--fs-gold); box-shadow: 0 12px 26px -16px rgba(201,162,75,.95); background: linear-gradient(160deg, rgba(243,220,154,.25), transparent); }
.fs-style__card { border-radius: 10px; padding: 8px; background: #0f0d11; margin-bottom: 10px; }
.fs-style__name { font-weight: 700; font-size: .82rem; line-height: 1.2; }
.fs-style__tag { color: var(--fs-muted); font-size: .72rem; line-height: 1.3; margin-top: 2px; }

/* Palettes */
.fs-palettes { display: flex; flex-wrap: wrap; gap: 10px; }
.fs-pal { display: inline-flex; align-items: center; gap: 10px; min-height: 48px; padding: 6px 14px 6px 6px; border-radius: 999px; border: 1.5px solid var(--fs-line); background: var(--fs-surface); cursor: pointer; font: inherit; font-size: .8rem; font-weight: 600; color: var(--fs-ink); }
.fs-pal[aria-pressed="true"] { border-color: var(--fs-gold); box-shadow: 0 6px 16px -10px rgba(201,162,75,.9); }
.fs-pal__dot { width: 34px; height: 34px; border-radius: 50%; flex: none; box-shadow: inset 0 0 0 2px rgba(255,255,255,.7), 0 2px 6px rgba(0,0,0,.25); }

/* Centrepiece modes & Photo drop */
.fs-centre-modes { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.fs-cmode { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 8px 14px; border-radius: 999px; border: 1.5px solid var(--fs-line); background: var(--fs-surface); cursor: pointer; font: inherit; font-size: .8rem; font-weight: 600; color: var(--fs-ink); white-space: nowrap; flex-shrink: 0; transition: border-color .2s, box-shadow .2s, transform .15s; }
.fs-cmode:hover { border-color: #d9c38f; }
.fs-cmode:active { transform: scale(0.98); }
.fs-cmode[aria-pressed="true"] { border-color: var(--fs-gold); background: linear-gradient(160deg, rgba(243,220,154,.24), transparent); box-shadow: 0 6px 16px -10px rgba(201,162,75,.9); }
.fs-scene-pill { display: flex; align-items: center; gap: 10px; padding: 10px 14px; margin-bottom: 12px; border-radius: 14px; border: 1px solid rgba(201,162,75,.38); background: rgba(243,220,154,.12); font-size: .8rem; color: var(--fs-ink); }
.fs-scene-pill i { color: #b48a35; font-size: 1rem; flex: none; }
.fs-drop { position: relative; display: flex; align-items: center; gap: 16px; padding: 16px; min-height: 96px; border-radius: 18px; border: 1.5px dashed #cdb47a; background: var(--fs-soft); cursor: pointer; transition: background .2s, border-color .2s; }
.fs-drop:hover, .fs-drop.is-over { background: rgba(243,220,154,.18); border-color: var(--fs-gold); }
.fs-drop input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.fs-drop__thumb { width: 72px; height: 72px; border-radius: 14px; object-fit: cover; flex: none; background: #e9e1d2; display: grid; place-items: center; color: #9b8a66; font-size: 1.6rem; overflow: hidden; }
.fs-drop__thumb img { width: 100%; height: 100%; object-fit: cover; }
.fs-drop b { display: block; font-size: .92rem; }
.fs-drop small { color: var(--fs-muted); font-size: .76rem; }
.fs-link-btn { background: none; border: 0; color: #9b2c2c; font-weight: 600; font-size: .82rem; min-height: 44px; padding: 0 6px; cursor: pointer; position: relative; z-index: 2; }

/* Fields */
.fs-label { display: block; font-size: .78rem; font-weight: 700; letter-spacing: .02em; color: var(--fs-muted); margin-bottom: 6px; }
.fs-input, .fs-textarea { width: 100%; min-height: 48px; padding: 12px 14px; border-radius: 14px; border: 1.5px solid var(--fs-line); background: var(--fs-surface); color: var(--fs-ink); font-size: 1rem; font-family: inherit; transition: border-color .2s, box-shadow .2s; }
.fs-input:focus, .fs-textarea:focus { outline: none; border-color: var(--fs-gold); box-shadow: 0 0 0 4px rgba(201,162,75,.18); }
.fs-textarea { min-height: 120px; resize: vertical; line-height: 1.5; }
.fs-field { margin-bottom: 14px; }
.fs-row { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 576px) { .fs-row { grid-template-columns: 1fr 1fr; } }
.fs-count { float: right; font-weight: 500; }
.fs-verses { display: grid; gap: 8px; margin-bottom: 12px; }
.fs-verse { text-align: left; padding: 12px 14px; min-height: 48px; border-radius: 14px; border: 1px solid var(--fs-line); background: var(--fs-soft); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 1.02rem; line-height: 1.4; color: var(--fs-ink); cursor: pointer; font-style: italic; }
.fs-verse:hover { border-color: #d9c38f; }
.fs-verse[aria-pressed="true"] { border-color: var(--fs-gold); background: rgba(243,220,154,.18); }

.fs-submit { display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; min-height: 56px; border: 0; border-radius: 999px; padding: 14px 28px; font-weight: 700; font-size: 1.02rem; letter-spacing: .01em; color: #1b1406; cursor: pointer;
    background: linear-gradient(110deg, #f3dc9a 0%, #c9a24b 35%, #fff1c4 55%, #c9a24b 75%, #b48a35 100%); background-size: 200% 100%; box-shadow: 0 16px 30px -16px rgba(180,138,53,.9), inset 0 1px 0 rgba(255,255,255,.6); transition: background-position .6s, transform .2s; }
.fs-submit:hover { background-position: 100% 0; transform: translateY(-1px); }
.fs-submit[disabled] { opacity: .7; cursor: progress; }
.fs-error { color: #b42318; font-size: .86rem; margin-top: 10px; }
.fs-spin { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(27,20,6,.25); border-top-color: #1b1406; animation: fs-spin .8s linear infinite; }
@keyframes fs-spin { to { transform: rotate(360deg); } }

/* Archive */
.fs-archive { margin-top: 48px; }
.fs-archive__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
.fs-archive__head h2 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; font-size: 1.6rem; margin: 0; color: var(--fs-ink); }
.fs-archive__head p { color: var(--fs-muted); margin: 4px 0 0; font-size: .9rem; }
.fs-archive__grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
@media (min-width: 768px) { .fs-archive__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (min-width: 1200px) { .fs-archive__grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; } }
.fs-item { display: flex; flex-direction: column; border-radius: 20px; background: var(--fs-surface); border: 1px solid var(--fs-line); overflow: hidden; }
.fs-item__stage { display: block; padding: 14px; background: radial-gradient(70% 60% at 50% 30%, rgba(201,162,75,.18), transparent 70%), #110f13; cursor: pointer; border: 0; width: 100%; }
.fs-item__body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.fs-item__title { font-weight: 700; font-size: .88rem; line-height: 1.25; color: var(--fs-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fs-item__meta { color: var(--fs-muted); font-size: .74rem; display: flex; gap: 10px; flex-wrap: wrap; }
.fs-item__actions { display: flex; gap: 8px; margin-top: 10px; }
.fs-icon-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; min-width: 44px; border-radius: 12px; border: 1px solid var(--fs-line); background: var(--fs-soft); color: var(--fs-ink); font-size: .8rem; font-weight: 600; cursor: pointer; text-decoration: none; }
.fs-icon-btn:hover { border-color: #d9c38f; color: var(--fs-ink); text-decoration: none; }
.fs-icon-btn--wa { background: #128c4a; border-color: #128c4a; color: #fff; }
.fs-icon-btn--wa:hover { background: #0f7a40; color: #fff; }
.fs-empty { padding: 40px 20px; text-align: center; border-radius: 24px; border: 1.5px dashed var(--fs-line); color: var(--fs-muted); }
.fs-empty b { display: block; font-family: 'Playfair Display', Georgia, serif; font-size: 1.2rem; color: var(--fs-ink); font-weight: 500; margin-bottom: 4px; }

/* Share sheet */
.fs-sheet { position: fixed; inset: 0; z-index: 2000; display: grid; place-items: end center; padding: 0; background: rgba(8,6,10,.72); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
@media (min-width: 768px) { .fs-sheet { place-items: center; padding: 24px; } }
.fs-sheet__panel { width: 100%; max-width: 860px; max-height: 92vh; overflow: auto; border-radius: 28px 28px 0 0; background: #121014; color: #f4efe6; box-shadow: 0 -20px 60px rgba(0,0,0,.5); padding: 22px 18px calc(22px + env(safe-area-inset-bottom)); }
@media (min-width: 768px) { .fs-sheet__panel { border-radius: 28px; padding: 28px; display: grid; grid-template-columns: 300px 1fr; gap: 28px; align-items: center; } }
.fs-sheet__img { display: block; width: 100%; max-width: 300px; margin: 0 auto 16px; border-radius: 12px; box-shadow: 0 24px 50px -20px rgba(0,0,0,.8), 0 0 0 1px rgba(243,220,154,.2); aspect-ratio: 4/5; object-fit: cover; background: #1c1a1e; }
@media (max-width: 767.98px) { .fs-sheet__img { max-width: 220px; } }
.fs-sheet h3 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; font-size: 1.5rem; margin: 0 0 6px; color: #fff; }
.fs-sheet__sub { color: rgba(244,239,230,.7); font-size: .9rem; margin-bottom: 18px; }
.fs-sheet__eyebrow { font-family: 'Cinzel', Georgia, serif; font-size: .7rem; letter-spacing: .3em; text-transform: uppercase; color: #f3dc9a; margin-bottom: 8px; }
.fs-sheet__actions { display: grid; gap: 10px; }
.fs-sheet__row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.fs-sbtn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 52px; padding: 12px 16px; border-radius: 16px; border: 1px solid rgba(243,220,154,.22); background: rgba(255,255,255,.05); color: #f4efe6; font-weight: 600; font-size: .92rem; cursor: pointer; text-decoration: none; }
.fs-sbtn:hover { background: rgba(255,255,255,.1); color: #fff; text-decoration: none; }
.fs-sbtn--wa { background: linear-gradient(135deg, #25d366, #128c4a); border-color: transparent; color: #fff; font-size: 1rem; min-height: 56px; box-shadow: 0 14px 28px -14px rgba(37,211,102,.7); }
.fs-sbtn--wa:hover { filter: brightness(1.05); }
.fs-sbtn--gold { background: linear-gradient(110deg, #f3dc9a, #c9a24b 50%, #f3dc9a); color: #1b1406; border-color: transparent; }
.fs-sbtn--gold:hover { color: #1b1406; }
.fs-sheet__close { position: absolute; top: 14px; right: 14px; width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,.15); background: rgba(0,0,0,.4); color: #fff; font-size: 1.2rem; cursor: pointer; }
.fs-sheet__note { color: rgba(244,239,230,.55); font-size: .76rem; margin-top: 12px; }

.fs-fab { position: fixed; left: 16px; bottom: calc(18px + env(safe-area-inset-bottom)); z-index: 1040; display: inline-flex; align-items: center; gap: 8px; min-height: 48px; padding: 10px 18px 10px 10px; border-radius: 999px; border: 1px solid rgba(243,220,154,.4); background: #121014; color: #f4efe6; font-weight: 600; font-size: .88rem; box-shadow: 0 14px 30px -10px rgba(0,0,0,.6); cursor: pointer; }
.fs-fab__thumb { width: 30px; height: 36px; border-radius: 5px; overflow: hidden; flex: none; }
@media (min-width: 992px) { .fs-fab { display: none !important; } }
.fs-sheet__img { animation: fs-shimmer 1.4s ease-in-out infinite alternate; }
.fs-sheet__img.is-loaded { animation: none; }
@keyframes fs-shimmer { from { background: #1c1a1e; } to { background: #2a2630; } }
.fp-toast { position: fixed; left: 50%; bottom: 28px; z-index: 3000; transform: translate(-50%, 20px); opacity: 0; background: #17120c; color: #f4efe6; border: 1px solid rgba(243,220,154,.35); padding: 12px 18px; border-radius: 999px; font-size: .9rem; font-weight: 600; box-shadow: 0 16px 30px rgba(0,0,0,.35); transition: opacity .3s, transform .3s; }
.fp-toast.is-in { opacity: 1; transform: translate(-50%, 0); }
</style>
@endpush

@section('content')
@include('member.creativeStudioTabs', ['creativeTab' => 'cards'])
<script nonce="{{ $nonce ?? '' }}" src="/public/js/festive-cards.js?v={{ assetVersion('js/festive-cards.js') }}"></script>

<div class="fs-wrap" x-data="festiveStudioWizard()">
    @include('csrf')

    <section class="fs-hero">
        <div class="fs-hero__row">
            <div>
                <div class="fs-hero__eyebrow">Festive Cards Studio</div>
                <h1>Couture cards for <em>every</em> celebration</h1>
                <p>Choose an occasion — or name your own — pick a design, add a photo and your words. We seal it in a living envelope you can send on WhatsApp in one tap.</p>
            </div>
            @if(count($cards) > 0)
            <button type="button" class="fs-pill-btn" @click="document.getElementById('fsArchive').scrollIntoView({ behavior: 'smooth' })">
                <i class="bi bi-collection" aria-hidden="true"></i>
                <span>Your cards ({{ count($cards) }})</span>
            </button>
            @endif
        </div>
    </section>

    <div class="fs-grid">
        {{-- ─────────── Live preview ─────────── --}}
        <aside class="fs-preview-col" aria-label="Live preview">
            <div class="fs-stage">
                <div class="fs-stage__card">
                    @include('festive.card', ['live' => true, 'tilt' => true, 'thumb' => false, 'animateIn' => false, 'styleExpr' => 'c.style', 'card' => []])
                </div>
                <div class="fs-stage__meta">
                    <span class="fs-chip"><i class="bi bi-gem" aria-hidden="true"></i><span x-text="selectedStyle.name"></span></span>
                    <span class="fs-chip"><span style="width:10px;height:10px;border-radius:50%;display:inline-block" :style="'background:' + selectedTheme.accent_color"></span><span x-text="selectedTheme.label"></span></span>
                </div>
                <p class="fs-stage__hint" x-text="FPCards.isTouch ? 'This is exactly how your card will look.' : 'Move your cursor over the card to see the light play.'"></p>
            </div>
        </aside>

        {{-- ─────────── Studio form ─────────── --}}
        <div>
            <form class="fs-panel" @submit.prevent="submit()" novalidate>
                @csrf

                {{-- 1. Occasion --}}
                <section class="fs-step" aria-labelledby="fsStepOccasion">
                    <div class="fs-step__head"><span class="fs-step__num">I</span><h2 class="fs-step__title" id="fsStepOccasion">What are we celebrating?</h2></div>
                    <div class="fs-occasions" role="group" aria-label="Occasion">
                        @foreach($occasions as $key => $occ)
                            @continue($key === 'custom')
                            <button type="button" class="fs-occ" :aria-pressed="occasionKey === '{{ $key }}'" @click="selectOccasion('{{ $key }}')">
                                <span class="fs-occ__emoji" aria-hidden="true">{{ $occ['emoji'] }}</span>
                                <span class="fs-occ__name">{{ $occ['short'] }}</span>
                            </button>
                        @endforeach
                        <button type="button" class="fs-occ fs-occ--custom" :aria-pressed="occasionKey === 'custom'" @click="selectOccasion('custom')">
                            <span class="fs-occ__emoji" aria-hidden="true">✍️</span>
                            <span class="fs-occ__name">Create your own celebration
                                <small>Anything that matters to you — a remembrance, a retirement, a citizenship, a first home…</small>
                            </span>
                        </button>
                    </div>

                    <div class="fs-custom" x-show="occasionKey === 'custom'" x-cloak x-transition.opacity>
                        <div class="fs-field">
                            <label class="fs-label" for="fsCustomOccasion">Name your celebration <span class="fs-count" x-text="customOccasion.length + '/80'"></span></label>
                            <input id="fsCustomOccasion" class="fs-input" type="text" maxlength="80" x-model="customOccasion" x-ref="customInput"
                                   placeholder="e.g. Dad’s 25th Remembrance" @input="onCustomName()" autocomplete="off">
                        </div>
                        <span class="fs-label">Set the mood</span>
                        <div class="fs-moods" role="group" aria-label="Mood">
                            @foreach($moods as $mKey => $m)
                            <button type="button" class="fs-mood" :aria-pressed="mood === '{{ $mKey }}'" @click="selectMood('{{ $mKey }}')">
                                <b>{{ $m['emoji'] }} {{ $m['label'] }}</b>
                                <span>{{ $m['description'] }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- 2. Design style --}}
                <section class="fs-step" aria-labelledby="fsStepStyle">
                    <div class="fs-step__head"><span class="fs-step__num">II</span><h2 class="fs-step__title" id="fsStepStyle">Choose a design</h2></div>
                    <p class="fs-step__sub">Each preview uses your words and photo.</p>
                    <div class="fs-styles" role="group" aria-label="Design style">
                        @foreach($templateStyles as $sKey => $s)
                        <button type="button" class="fs-style" :aria-pressed="templateStyle === '{{ $sKey }}'" @click="templateStyle = '{{ $sKey }}'">
                            <div class="fs-style__card">
                                @include('festive.card', ['live' => true, 'thumb' => true, 'tilt' => false, 'animateIn' => false, 'styleExpr' => "'" . $sKey . "'", 'card' => []])
                            </div>
                            <div class="fs-style__name">{{ $s['name'] }}</div>
                            <div class="fs-style__tag">{{ $s['tagline'] }}</div>
                        </button>
                        @endforeach
                    </div>
                </section>

                {{-- 3. Palette --}}
                <section class="fs-step" aria-labelledby="fsStepPalette">
                    <div class="fs-step__head"><span class="fs-step__num">III</span><h2 class="fs-step__title" id="fsStepPalette">Palette &amp; metal</h2></div>
                    <div class="fs-palettes" role="group" aria-label="Palette">
                        @foreach($themes as $tKey => $thm)
                        <button type="button" class="fs-pal" :aria-pressed="themeKey === '{{ $tKey }}'" @click="themeKey = '{{ $tKey }}'">
                            <span class="fs-pal__dot" style="background: linear-gradient(135deg, {{ $thm['primary_color'] }} 0 48%, {{ $thm['accent_color'] }} 52% 100%);"></span>
                            <span>{{ $thm['label'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </section>

                {{-- 4. Centrepiece & Photo --}}
                <section class="fs-step" aria-labelledby="fsStepPhoto">
                    <div class="fs-step__head"><span class="fs-step__num">IV</span><h2 class="fs-step__title" id="fsStepPhoto">Centrepiece &amp; photo <span style="font-size:.8rem;color:var(--fs-muted);font-family:inherit">(optional)</span></h2></div>
                    <div class="fs-centre-modes" role="group" aria-label="Centrepiece style">
                        <button type="button" class="fs-cmode" :aria-pressed="heroMode === 'art'" @click="heroMode = 'art'">
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            <span>Festive Illustration</span>
                        </button>
                        <button type="button" class="fs-cmode" :aria-pressed="heroMode === 'photo'" @click="heroMode = 'photo'">
                            <i class="bi bi-camera" aria-hidden="true"></i>
                            <span>Family Photo</span>
                        </button>
                        <button type="button" class="fs-cmode" :aria-pressed="heroMode === 'crest'" @click="heroMode = 'crest'">
                            <i class="bi bi-shield-shaded" aria-hidden="true"></i>
                            <span>Family Monogram</span>
                        </button>
                    </div>
                    <div class="fs-scene-pill" x-show="heroMode === 'art'" x-cloak>
                        <i class="bi bi-palette2" aria-hidden="true"></i>
                        <span><strong x-text="selectedOccasion.scene_title || 'Bespoke Occasion Artwork'"></strong> — crafted for your chosen celebration, or upload a family photo below.</span>
                    </div>
                    <label class="fs-drop" :class="{ 'is-over': dragOver }" @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop.prevent="dragOver = false; onPhoto($event.dataTransfer.files[0])">
                        <input type="file" accept="image/*" @change="onPhoto($event.target.files[0]); $event.target.value = ''" aria-label="Upload a photo">
                        <span class="fs-drop__thumb">
                            <template x-if="photoUrl"><img :src="photoUrl" alt=""></template>
                            <template x-if="!photoUrl"><i class="bi" :class="photoBusy ? 'bi-hourglass-split' : 'bi-image'" aria-hidden="true"></i></template>
                        </span>
                        <span style="flex:1;min-width:0">
                            <b x-text="photoUrl ? 'Photo added — looking lovely' : (photoBusy ? 'Preparing your photo…' : 'Tap to choose or drop a family photo')"></b>
                            <small x-text="heroMode === 'crest' ? 'Your gilded family monogram crest is active as the centrepiece. Location data is always removed.' : 'Without a photo, the bespoke occasion illustration shines at the centre. Location data is always removed.'"></small>
                        </span>
                        <button type="button" class="fs-link-btn" x-show="photoUrl" x-cloak @click.prevent.stop="removePhoto()">Remove</button>
                    </label>
                    <p class="fs-error" x-show="photoError" x-text="photoError" x-cloak></p>
                </section>

                {{-- 5. Words --}}
                <section class="fs-step" aria-labelledby="fsStepWords">
                    <div class="fs-step__head"><span class="fs-step__num">V</span><h2 class="fs-step__title" id="fsStepWords">Your words</h2></div>

                    <div class="fs-field">
                        <label class="fs-label" for="fsHeadline">Headline <span class="fs-count" x-text="headline.length + '/160'"></span></label>
                        <input id="fsHeadline" class="fs-input" type="text" maxlength="160" x-model="headline" @input="headlineTouched = true" required>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="fsRecipient">Who is it for? <span style="font-weight:400">(optional)</span></label>
                        <input id="fsRecipient" class="fs-input" type="text" maxlength="128" x-model="recipientName" placeholder="e.g. Grandma Rose, Aunty Funmi, The Sterling Family">
                    </div>

                    <span class="fs-label">Pick a verse, then make it yours</span>
                    <div class="fs-verses">
                        <template x-for="(v, i) in suggestions" :key="occasionKey + mood + i">
                            <button type="button" class="fs-verse" :aria-pressed="verse === v" @click="verse = v" x-text="v"></button>
                        </template>
                    </div>
                    <div class="fs-field">
                        <label class="fs-label" for="fsVerse">Message <span class="fs-count" x-text="verse.length + '/600'"></span></label>
                        <textarea id="fsVerse" class="fs-textarea" maxlength="600" x-model="verse" required></textarea>
                    </div>
                    <div class="fs-row">
                        <div class="fs-field">
                            <label class="fs-label" for="fsSignOff">Sign-off</label>
                            <input id="fsSignOff" class="fs-input" type="text" maxlength="128" x-model="signOff" placeholder="With all our love,">
                        </div>
                        <div class="fs-field">
                            <label class="fs-label" for="fsSender">From</label>
                            <input id="fsSender" class="fs-input" type="text" maxlength="128" x-model="senderName" placeholder="The Olaogun Family" required>
                        </div>
                    </div>
                </section>

                <button type="submit" class="fs-submit" :disabled="busy">
                    <span class="fs-spin" x-show="busy" x-cloak aria-hidden="true"></span>
                    <i class="bi bi-envelope-paper-heart" x-show="!busy" aria-hidden="true"></i>
                    <span x-text="busy ? 'Sealing your envelope…' : 'Create card & seal the envelope'"></span>
                </button>
                <p class="fs-error" role="alert" x-show="error" x-text="error" x-cloak></p>
            </form>
        </div>
    </div>

    {{-- ─────────── Archive ─────────── --}}
    <section class="fs-archive" id="fsArchive" aria-labelledby="fsArchiveTitle">
        <div class="fs-archive__head">
            <div>
                <h2 id="fsArchiveTitle">Your family’s cards</h2>
                <p>Every card your family has sent. Tap one to share it again.</p>
            </div>
        </div>

        @if(empty($cards))
            <div class="fs-empty">
                <b>No cards yet</b>
                Your first card will appear here the moment you seal it.
            </div>
        @else
            <div class="fs-archive__grid">
                @foreach($cards as $c)
                    @php
                        $sheetData = [
                            'token' => $c['share_token'],
                            'label' => $c['occasion_label'],
                            'headline' => $c['headline'],
                            'url' => $c['absolute_public_url'],
                            'path' => $c['public_url'],
                            'image' => $c['portrait_image_url'],
                            'whatsapp' => $c['whatsapp_text'],
                            'posted' => !empty($c['feed_post_id']),
                        ];
                    @endphp
                    <article class="fs-item" data-card='@json($sheetData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)'>
                        <button type="button" class="fs-item__stage" @click="openSheet(JSON.parse($el.closest('[data-card]').dataset.card))" aria-label="Share {{ $c['occasion_label'] }} card">
                            @include('festive.card', ['card' => $c, 'thumb' => true, 'live' => false, 'tilt' => false, 'animateIn' => false, 'styleExpr' => 'c.style'])
                        </button>
                        <div class="fs-item__body">
                            <div class="fs-item__title">{{ $c['emoji'] }} {{ $c['occasion_label'] }}</div>
                            <div class="fs-item__meta">
                                @if(!empty($c['recipient_name']))<span>For {{ $c['recipient_name'] }}</span>@endif
                                <span><i class="bi bi-eye" aria-hidden="true"></i> {{ (int)$c['view_count'] }}</span>
                                @if(!empty($c['created_at']))<span>{{ date('j M Y', strtotime((string)$c['created_at'])) }}</span>@endif
                            </div>
                            <div class="fs-item__actions">
                                <button type="button" class="fs-icon-btn fs-icon-btn--wa" @click="openSheet(JSON.parse($el.closest('[data-card]').dataset.card))" aria-label="Share on WhatsApp">
                                    <i class="bi bi-whatsapp" aria-hidden="true"></i><span>Share</span>
                                </button>
                                <a class="fs-icon-btn" href="{{ $c['public_url'] }}" target="_blank" rel="noopener" aria-label="Open envelope" style="flex:0 0 44px">
                                    <i class="bi bi-envelope-open-heart" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Mobile: jump back to the live preview --}}
    <button type="button" class="fs-fab" x-show="!stageVisible && !sheet" x-cloak x-transition.opacity
            @click="document.querySelector('.fs-stage').scrollIntoView({ behavior: 'smooth', block: 'start' })" aria-label="See your card preview">
        <span class="fs-fab__thumb" aria-hidden="true">@include('festive.card', ['live' => true, 'thumb' => true, 'tilt' => false, 'animateIn' => false, 'styleExpr' => 'c.style', 'card' => []])</span>
        <span>Preview card</span>
    </button>

    {{-- ─────────── Share sheet ─────────── --}}
    <div class="fs-sheet" x-show="sheet" x-cloak x-transition.opacity @keydown.escape.window="closeSheet()" @click.self="closeSheet()" role="dialog" aria-modal="true" aria-labelledby="fsSheetTitle">
        <template x-if="sheet">
            <div class="fs-sheet__panel" style="position:relative" @click.stop>
                <button type="button" class="fs-sheet__close" @click="closeSheet()" aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                <img class="fs-sheet__img" :src="sheet.image" alt="Your card as it will be shared" @load="$el.classList.add('is-loaded')">
                <div>
                    <div class="fs-sheet__eyebrow" x-text="sheet.label"></div>
                    <h3 id="fsSheetTitle" x-text="sheet.justCreated ? 'Sealed and ready to send' : 'Share this card'"></h3>
                    <p class="fs-sheet__sub">They’ll see the card as a rich preview, then tap to break the wax seal and open the living envelope.</p>
                    <div class="fs-sheet__actions">
                        <button type="button" class="fs-sbtn fs-sbtn--wa" @click="sendWhatsApp()">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i><span>Send on WhatsApp</span>
                        </button>
                        <div class="fs-sheet__row">
                            <button type="button" class="fs-sbtn" @click="copyLink()"><i class="bi bi-link-45deg" aria-hidden="true"></i><span>Copy link</span></button>
                            <a class="fs-sbtn" :href="sheet.path" target="_blank" rel="noopener"><i class="bi bi-envelope-open-heart" aria-hidden="true"></i><span>Open envelope</span></a>
                        </div>
                        <div class="fs-sheet__row">
                            <a class="fs-sbtn" :href="sheet.image + '?download=1'" download><i class="bi bi-download" aria-hidden="true"></i><span>Save image</span></a>
                            <button type="button" class="fs-sbtn" x-show="canNativeShare" @click="shareMore()"><i class="bi bi-share" aria-hidden="true"></i><span>More apps</span></button>
                            <button type="button" class="fs-sbtn" x-show="!canNativeShare" @click="copyMessage()"><i class="bi bi-chat-quote" aria-hidden="true"></i><span>Copy message</span></button>
                        </div>
                        <button type="button" class="fs-sbtn fs-sbtn--gold" :disabled="sheet.posted || feedBusy" @click="postToFeed()">
                            <i class="bi" :class="sheet.posted ? 'bi-check2-circle' : 'bi-broadcast'" aria-hidden="true"></i>
                            <span x-text="sheet.posted ? 'On your family feed' : (feedBusy ? 'Posting…' : 'Post to family feed')"></span>
                        </button>
                    </div>
                    <p class="fs-sheet__note" x-show="sheet.justCreated">Your card is saved in “Your family’s cards” below.</p>
                </div>
            </div>
        </template>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
function festiveStudioWizard() {
    const occasions = @json($occasions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const themes = @json($themes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const templateStyles = @json($templateStyles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const moods = @json($moods, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    const defaultSender = @json(trim((string)($user['lastName'] ?? '')) !== '' ? 'The ' . trim((string)$user['lastName']) . ' Family' : (trim((string)($user['name'] ?? '')) ?: 'The Family'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    return {
        occasions, themes, templateStyles, moods,
        occasionKey: 'christmas',
        customOccasion: '',
        mood: 'joyful',
        templateStyle: 'classic',
        themeKey: 'royal_crimson',
        headline: occasions.christmas.default_headline,
        headlineTouched: false,
        recipientName: '',
        verse: occasions.christmas.default_verse,
        signOff: occasions.christmas.default_sign_off,
        senderName: defaultSender,
        heroMode: 'art',
        photoBlob: null,
        photoUrl: '',
        photoBusy: false,
        photoError: '',
        dragOver: false,
        busy: false,
        error: '',
        sheet: null,
        feedBusy: false,
        canNativeShare: !!navigator.share,
        stageVisible: true,

        init() {
            if (window.FPCards) window.FPCards.initTilt(this.$el);
            const stage = this.$el.querySelector('.fs-stage');
            if (stage && 'IntersectionObserver' in window) {
                new IntersectionObserver((entries) => { this.stageVisible = entries[0].isIntersecting; }, { threshold: 0.15 }).observe(stage);
            }
        },

        get selectedOccasion() { return this.occasions[this.occasionKey] || this.occasions.christmas; },
        get selectedStyle() { return this.templateStyles[this.templateStyle] || this.templateStyles.classic; },
        get selectedTheme() { return this.themes[this.themeKey] || this.themes.royal_crimson; },
        get selectedMood() { return this.moods[this.mood] || this.moods.joyful; },
        get suggestions() {
            return this.occasionKey === 'custom' ? this.selectedMood.sample_verses : (this.selectedOccasion.sample_verses || []);
        },
        get eyebrow() {
            if (this.occasionKey === 'custom') return this.customOccasion.trim() || 'Your Celebration';
            return this.selectedOccasion.badge;
        },
        /* Everything the shared card partial binds to */
        get c() {
            const fp = window.FPCards;
            const resolvedMode = (this.heroMode === 'photo' && this.photoUrl)
                ? 'photo'
                : (this.heroMode === 'crest' ? 'crest' : 'art');
            const artKey = fp && fp.resolveArtKey
                ? fp.resolveArtKey(this.occasionKey, this.mood, this.customOccasion)
                : (this.occasionKey === 'custom' ? 'milestone' : this.occasionKey);
            return {
                style: this.templateStyle,
                vars: fp ? fp.cardVars(this.selectedTheme, this.headline, this.verse) : '',
                eyebrow: this.eyebrow,
                headline: this.headline || this.selectedOccasion.default_headline,
                recipient: this.recipientName.trim(),
                verse: this.verse,
                signOff: this.signOff,
                sender: this.senderName || 'The Family',
                photo: this.photoUrl,
                heroMode: resolvedMode,
                art: artKey,
                monogram: fp ? fp.monogram(this.senderName) : 'F',
            };
        },

        selectOccasion(key) {
            this.occasionKey = key;
            if (key === 'custom') {
                this.headlineTouched = false;
                this.applyMood(this.mood);
                this.$nextTick(() => this.$refs.customInput && this.$refs.customInput.focus());
                return;
            }
            const occ = this.selectedOccasion;
            this.themeKey = occ.default_theme || 'royal_crimson';
            this.headline = occ.default_headline || '';
            this.headlineTouched = false;
            this.verse = occ.default_verse || '';
            this.signOff = occ.default_sign_off || 'With love,';
        },
        selectMood(key) { this.mood = key; this.applyMood(key); },
        applyMood(key) {
            const m = this.moods[key] || this.moods.joyful;
            this.themeKey = m.default_theme;
            if (!this.headlineTouched) this.headline = m.default_headline;
            this.verse = m.sample_verses[0];
            this.signOff = m.default_sign_off;
        },
        onCustomName() { /* the name appears live as the card's eyebrow */ },

        async onPhoto(file) {
            if (!file) return;
            this.photoError = '';
            this.photoBusy = true;
            try {
                const out = await window.FPCards.preparePhoto(file, 2000);
                if (this.photoUrl) URL.revokeObjectURL(this.photoUrl);
                this.photoBlob = out.blob;
                this.photoUrl = out.url;
                this.heroMode = 'photo';
            } catch (e) {
                this.photoError = e.message || 'We could not read that photo.';
            } finally {
                this.photoBusy = false;
            }
        },
        removePhoto() {
            if (this.photoUrl) URL.revokeObjectURL(this.photoUrl);
            this.photoBlob = null;
            this.photoUrl = '';
            if (this.heroMode === 'photo') this.heroMode = 'art';
        },

        csrf() { return document.querySelector('input[name="token"]')?.value || ''; },

        async submit() {
            this.error = '';
            if (this.occasionKey === 'custom' && !this.customOccasion.trim()) {
                this.error = 'Please name your celebration, e.g. “Dad’s 25th Remembrance”.';
                this.$refs.customInput && this.$refs.customInput.focus();
                return;
            }
            if (!this.headline.trim() || !this.verse.trim() || !this.senderName.trim()) {
                this.error = 'Please add a headline, a message and who it’s from.';
                return;
            }
            this.busy = true;
            try {
                const fd = new FormData();
                fd.append('token', this.csrf());
                fd.append('occasion', this.occasionKey);
                fd.append('custom_occasion', this.customOccasion.trim());
                fd.append('mood', this.mood);
                fd.append('template_style', this.templateStyle);
                fd.append('theme', this.themeKey);
                fd.append('headline', this.headline.trim());
                fd.append('recipient_name', this.recipientName.trim());
                fd.append('verse', this.verse.trim());
                fd.append('sign_off', this.signOff.trim());
                fd.append('sender_name', this.senderName.trim());
                const usePhoto = !!(this.photoBlob && this.heroMode === 'photo');
                fd.append('hero_type', usePhoto ? 'photo' : (this.heroMode === 'crest' ? 'crest' : 'avatar'));
                if (usePhoto) fd.append('hero_photo', this.photoBlob, 'photo.jpg');

                const response = await fetch('/api/festive-cards/create', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                const res = await response.json().catch(() => ({}));
                if (!response.ok || res.status !== 'success' || !res.data) {
                    this.error = res.message || 'We could not create your card. Please try again.';
                    return;
                }
                const d = res.data;
                this.openSheet({
                    token: d.share_token, label: d.occasion_label, headline: d.headline,
                    url: d.absolute_public_url, path: d.public_url, image: d.portrait_image_url,
                    whatsapp: d.whatsapp_text, posted: false, justCreated: true,
                });
            } catch (e) {
                this.error = 'Network error — please check your connection and try again.';
            } finally {
                this.busy = false;
            }
        },

        openSheet(data) {
            this.sheet = data;
            document.documentElement.style.overflow = 'hidden';
            if (window.FPCards) window.FPCards.prepareShareFile(data.image, 'greeting-card.jpg');
        },
        closeSheet() {
            const reload = this.sheet && this.sheet.justCreated;
            this.sheet = null;
            document.documentElement.style.overflow = '';
            if (reload) window.location.reload();
        },
        sendWhatsApp() {
            window.FPCards.shareWhatsApp({ text: this.sheet.whatsapp, imageUrl: this.sheet.image });
        },
        shareMore() {
            window.FPCards.shareNative({ title: this.sheet.headline, text: this.sheet.whatsapp, url: this.sheet.url, imageUrl: this.sheet.image });
        },
        async copyLink() {
            await window.FPCards.copy(this.sheet.url);
            window.FPCards.toast('Link copied — paste it anywhere');
        },
        async copyMessage() {
            await window.FPCards.copy(this.sheet.whatsapp);
            window.FPCards.toast('Message copied');
        },
        async postToFeed() {
            if (!this.sheet || this.sheet.posted) return;
            this.feedBusy = true;
            try {
                const fd = new FormData();
                fd.append('token', this.csrf());
                fd.append('share_token', this.sheet.token);
                const response = await fetch('/api/festive-cards/post-to-feed', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                const res = await response.json().catch(() => ({}));
                if (response.ok && res.status === 'success') {
                    this.sheet.posted = true;
                    window.FPCards.toast(res.data && res.data.status === 'already_posted' ? 'Already on your family feed' : 'Posted to your family feed');
                } else {
                    window.FPCards.toast(res.message || 'Could not post to the feed');
                }
            } catch (e) {
                window.FPCards.toast('Network error — please try again');
            } finally {
                this.feedBusy = false;
            }
        },
    };
}
</script>
@endsection
