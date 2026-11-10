@php
    $jsonSafeFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
    $recipient = trim((string)($card['recipient_name'] ?? ''));
    $sender = (string)($card['sender_name'] ?? 'Your family');
    $mood = (string)($card['mood'] ?? 'joyful');
    $moodMeta = (array)($card['mood_meta'] ?? []);
    $ogTitle = ($recipient !== '' ? '💌 A card for ' . $recipient : '💌 You’ve received a card') . ' from ' . $sender;
    $ogDesc = $card['headline'] . ' — tap to break the seal and open your living envelope.';
    $ogImage = $card['absolute_og_image_url'] ?? $card['absolute_image_url'] ?? $card['image_url'];
    $prompt = (string)($moodMeta['envelope_prompt'] ?? 'Something wonderful is inside');
    $replyHref = '/register?ref=festive_' . $card['share_token'] . (!empty($card['fam_code']) ? '&famCode=' . rawurlencode((string)preg_replace('/^#+/', '', (string)$card['fam_code'])) : '');
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="{{ $theme['deep_color'] ?? '#120409' }}">

    <title>{{ $ogTitle }}</title>
    <meta name="description" content="{{ $ogDesc }}">

    <!-- OpenGraph & Social Previews (WhatsApp, iMessage, Facebook, X) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="FamilyPlatform">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $card['occasion_label'] }} card from {{ $sender }}">
    <meta property="og:url" content="{{ $card['absolute_public_url'] ?? $card['public_url'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Great+Vibes&family=Playfair+Display:wght@400;500;600&family=Inter:wght@500;600&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/public/css/festive-cards.css?v={{ assetVersion('css/festive-cards.css') }}">
    <link rel="preload" as="image" href="{{ $card['portrait_image_url'] }}">

    <style nonce="{{ $nonce ?? '' }}">
        :root {
            --primary: {{ $theme['primary_color'] ?? '#7a0f2a' }};
            --accent: {{ $theme['accent_color'] ?? '#d4af37' }};
            --deep: {{ $theme['deep_color'] ?? '#1a0309' }};
            --light: {{ $theme['light_color'] ?? '#f6e7a8' }};
            --paper: {{ $theme['paper_color'] ?? '#fbf3e4' }};
            --seal: {{ $theme['seal_color'] ?? '#9f1239' }};
            --metal: linear-gradient(118deg, var(--light) 0%, var(--accent) 22%, color-mix(in srgb, var(--accent) 52%, #000) 42%, var(--light) 58%, var(--accent) 78%, color-mix(in srgb, var(--accent) 55%, #000) 100%);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        html { background: #050406; }
        body {
            min-height: 100vh;
            min-height: 100dvh;
            color: #f6f0e4;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background:
                radial-gradient(70% 55% at 50% 38%, color-mix(in srgb, var(--accent) 24%, transparent), transparent 70%),
                radial-gradient(120% 80% at 50% 120%, color-mix(in srgb, var(--primary) 55%, transparent), transparent 70%),
                linear-gradient(180deg, color-mix(in srgb, var(--deep) 80%, #000) 0%, var(--deep) 60%, #050406 100%);
            background-attachment: fixed;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            pointer-events: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 .035 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            z-index: 0;
        }
        #particleCanvas { position: fixed; inset: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 1; }

        main { position: relative; z-index: 2; flex: 1; display: flex; flex-direction: column; align-items: center; padding: max(28px, env(safe-area-inset-top)) 16px 24px; }

        .intro { text-align: center; max-width: 520px; margin-bottom: 18px; transition: opacity .6s ease, transform .6s ease; }
        .intro.is-hidden { opacity: 0; transform: translateY(-10px); pointer-events: none; }
        .eyebrow { font-family: 'Cinzel', Georgia, serif; font-size: .74rem; letter-spacing: .34em; text-transform: uppercase; background: var(--metal); -webkit-background-clip: text; background-clip: text; color: transparent; margin: 0 0 10px; }
        .intro h1 { font-family: 'Playfair Display', Georgia, serif; font-weight: 500; font-size: clamp(1.6rem, 6vw, 2.3rem); line-height: 1.15; margin: 0 0 6px; color: #fff; }
        .intro h1 .script { font-family: 'Great Vibes', cursive; font-weight: 400; font-size: 1.3em; background: var(--metal); -webkit-background-clip: text; background-clip: text; color: transparent; padding: 0 .06em; }
        .intro p { margin: 0; color: rgba(246,240,228,.72); font-size: .95rem; }

        /* ── Scene ─────────────────────────────────────────────────────── */
        .scene { position: relative; width: min(88vw, 460px, calc((100vh - 190px) * 0.78)); min-width: 260px; aspect-ratio: 4 / 5.6; perspective: 1600px; margin-top: 4px; }
        .scene.is-revealed { aspect-ratio: 4 / 5; transition: aspect-ratio .9s cubic-bezier(.2,.8,.2,1); }

        .env-card {
            position: absolute; left: 7%; right: 7%; bottom: 14%;
            transform-origin: 50% 100%;
            transform: translateY(0) scale(.86);
            opacity: 0;
            z-index: 2;
            transition: transform 1.1s cubic-bezier(.2,.85,.25,1), opacity .2s linear .35s, left .9s cubic-bezier(.2,.8,.2,1), right .9s cubic-bezier(.2,.8,.2,1), bottom .9s cubic-bezier(.2,.8,.2,1);
        }
        .scene.is-open .env-card { opacity: 1; }
        .scene.is-rising .env-card { transform: translateY(-46%) scale(.86); }
        .scene.is-revealed .env-card { left: 0; right: 0; bottom: 0; transform: none; z-index: 6; }

        .env {
            position: absolute; left: 0; right: 0; bottom: 6%;
            aspect-ratio: 1.42;
            transform-style: preserve-3d;
            transition: transform 1s cubic-bezier(.5,0,.2,1), opacity .8s ease;
        }
        .scene.is-revealed .env { transform: translateY(70%) rotateX(18deg); opacity: 0; pointer-events: none; }
        .env__back, .env__front, .env__flap { position: absolute; inset: 0; border-radius: 10px; }
        .env__back {
            z-index: 1;
            box-shadow: 0 30px 50px -10px rgba(0,0,0,.6), 0 8px 16px rgba(0,0,0,.35);
            background:
                repeating-linear-gradient(45deg, color-mix(in srgb, var(--accent) 35%, transparent) 0 2px, transparent 2px 14px),
                linear-gradient(180deg, color-mix(in srgb, var(--primary) 75%, #000), var(--primary));
        }
        .env__front {
            z-index: 3;
            background:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 .09 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"),
                linear-gradient(165deg, color-mix(in srgb, var(--primary) 92%, #fff) 0%, var(--primary) 45%, color-mix(in srgb, var(--primary) 70%, #000) 100%);
            clip-path: polygon(0 0, 50% 50%, 100% 0, 100% 100%, 0 100%);
        }
        .env__front::before, .env__front::after { content: ''; position: absolute; inset: 0; }
        .env__front::before { background: linear-gradient(to top right, rgba(0,0,0,.22), transparent 50%); clip-path: polygon(0 0, 50% 50%, 0 100%); }
        .env__front::after { background: linear-gradient(to top left, rgba(255,255,255,.08), transparent 50%); clip-path: polygon(100% 0, 50% 50%, 100% 100%); }
        .env__address { position: absolute; z-index: 4; left: 0; right: 0; bottom: 5%; text-align: center; pointer-events: none; }
        .env__to { font-family: 'Great Vibes', cursive; font-size: clamp(1.6rem, 7.5vw, 2.3rem); line-height: 1.1; background: var(--metal); -webkit-background-clip: text; background-clip: text; color: transparent; padding: 0 .1em; filter: drop-shadow(0 1px 0 rgba(0,0,0,.35)); }
        .env__from { font-family: 'Cinzel', Georgia, serif; font-size: .62rem; letter-spacing: .32em; text-transform: uppercase; color: color-mix(in srgb, var(--light) 80%, transparent); margin-top: 4px; }
        .env__flap {
            z-index: 5;
            height: 56%;
            transform-origin: 50% 0;
            transform-style: preserve-3d;
            transition: transform .9s cubic-bezier(.45,.05,.2,1), z-index 0s linear .45s;
        }
        .env__flap-face, .env__flap-inner { position: absolute; inset: 0; clip-path: polygon(0 0, 100% 0, 50% 100%); backface-visibility: hidden; -webkit-backface-visibility: hidden; border-radius: 10px 10px 0 0; }
        .env__flap-face { background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 85%, #fff) 0%, color-mix(in srgb, var(--primary) 88%, #000) 100%); }
        .env__flap-face::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 60%, rgba(0,0,0,.25)); }
        .env__flap-inner {
            transform: rotateX(180deg);
            background:
                radial-gradient(circle at 50% 30%, color-mix(in srgb, var(--light) 50%, transparent) 0 1px, transparent 1.6px) 0 0 / 12px 12px,
                var(--metal);
        }
        .scene.is-open .env__flap { transform: rotateX(180deg); z-index: 0; }

        .wax-seal {
            position: absolute; z-index: 7;
            left: 50%; top: 54%;
            width: clamp(74px, 22vw, 96px); aspect-ratio: 1;
            transform: translate(-50%, -50%);
            border: 0; padding: 0; cursor: pointer;
            border-radius: 47% 53% 50% 50% / 52% 48% 52% 48%;
            background: radial-gradient(circle at 34% 28%, color-mix(in srgb, var(--seal) 55%, #fff) 0%, var(--seal) 38%, color-mix(in srgb, var(--seal) 45%, #000) 100%);
            box-shadow: 0 10px 22px rgba(0,0,0,.5), inset 0 3px 4px rgba(255,255,255,.35), inset 0 -6px 9px rgba(0,0,0,.35);
            display: grid; place-items: center;
            -webkit-tap-highlight-color: transparent;
            transition: transform .25s cubic-bezier(.2,1.4,.4,1);
        }
        .wax-seal::before { content: ''; position: absolute; inset: 16%; border-radius: 50%; border: 2px solid rgba(0,0,0,.22); box-shadow: 0 1px 0 rgba(255,255,255,.18); }
        .wax-seal::after { content: ''; position: absolute; inset: -14px; border-radius: 50%; border: 2px solid color-mix(in srgb, var(--light) 60%, transparent); animation: sealPulse 2.2s ease-out infinite; }
        .wax-seal:hover { transform: translate(-50%, -50%) scale(1.06); }
        .wax-seal:focus-visible { outline: 3px solid var(--light); outline-offset: 6px; }
        .wax-seal__mono { position: relative; font-family: 'Cinzel', Georgia, serif; font-weight: 700; font-size: clamp(1.4rem, 6vw, 1.9rem); background: var(--metal); -webkit-background-clip: text; background-clip: text; color: transparent; filter: drop-shadow(0 -1px 0 rgba(0,0,0,.45)); }
        .wax-seal__mono.is-pair { font-size: clamp(1.1rem, 4.6vw, 1.45rem); }
        .wax-seal.broken { animation: crackWax .7s forwards cubic-bezier(.5,0,.75,0); pointer-events: none; }
        .wax-seal.broken::after { display: none; }
        @keyframes sealPulse { 0% { transform: scale(.85); opacity: .9; } 100% { transform: scale(1.35); opacity: 0; } }
        @keyframes crackWax {
            0% { transform: translate(-50%, -50%) scale(1); filter: brightness(1.3); }
            35% { transform: translate(-50%, -50%) scale(1.12) rotate(6deg); }
            100% { transform: translate(-50%, -20%) scale(.5) rotate(-24deg); opacity: 0; }
        }
        .seal-anchor { position: absolute; left: 0; right: 0; bottom: 6%; aspect-ratio: 1.42; pointer-events: none; z-index: 7; }
        .seal-anchor .wax-seal { top: 49%; pointer-events: auto; }
        .scene.is-revealed .seal-anchor { display: none; }

        .tap-hint { margin-top: 16px; text-align: center; color: rgba(246,240,228,.7); font-size: .88rem; display: flex; align-items: center; justify-content: center; gap: 8px; transition: opacity .4s; }
        .tap-hint.is-hidden { opacity: 0; }

        /* ── After reveal ──────────────────────────────────────────────── */
        .after { width: min(92vw, 460px); margin-top: 26px; opacity: 0; transform: translateY(14px); transition: opacity .7s ease .5s, transform .7s ease .5s; pointer-events: none; }
        .after.is-in { opacity: 1; transform: none; pointer-events: auto; }
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 50px; padding: 12px 14px; border-radius: 16px; border: 1px solid color-mix(in srgb, var(--light) 25%, transparent); background: rgba(255,255,255,.06); color: #f6f0e4; font: 600 .9rem/1.2 'Inter', sans-serif; text-decoration: none; cursor: pointer; -webkit-tap-highlight-color: transparent; transition: background .2s, transform .2s; }
        .btn:hover { background: rgba(255,255,255,.12); color: #fff; }
        .btn--wa { grid-column: 1 / -1; min-height: 56px; font-size: 1rem; border: 0; color: #fff; background: linear-gradient(135deg, #25d366, #128c4a); box-shadow: 0 14px 30px -14px rgba(37,211,102,.75); }
        .btn--wa:hover { filter: brightness(1.05); background: linear-gradient(135deg, #25d366, #128c4a); }
        .btn--gold { grid-column: 1 / -1; color: #1b1406; border: 0; background: linear-gradient(110deg, var(--light), var(--accent) 45%, var(--light) 70%, var(--accent)); background-size: 200% 100%; box-shadow: 0 14px 30px -16px color-mix(in srgb, var(--accent) 80%, transparent); }
        .btn--gold:hover { color: #1b1406; background-position: 100% 0; }
        .audio { display: flex; align-items: center; gap: 12px; justify-content: center; margin: 0 0 14px; }

        footer { position: relative; z-index: 2; text-align: center; padding: 28px 20px calc(28px + env(safe-area-inset-bottom)); color: rgba(246,240,228,.55); font-size: .8rem; }
        footer .brand { font-family: 'Cinzel', Georgia, serif; letter-spacing: .38em; text-transform: uppercase; font-size: .68rem; color: color-mix(in srgb, var(--light) 70%, transparent); display: block; margin-bottom: 6px; }
        footer a { color: color-mix(in srgb, var(--light) 85%, #fff); }

        .fp-toast { position: fixed; left: 50%; bottom: 28px; z-index: 30; transform: translate(-50%, 20px); opacity: 0; background: #17120c; color: #f4efe6; border: 1px solid rgba(243,220,154,.35); padding: 12px 18px; border-radius: 999px; font: 600 .9rem 'Inter', sans-serif; box-shadow: 0 16px 30px rgba(0,0,0,.35); transition: opacity .3s, transform .3s; }
        .fp-toast.is-in { opacity: 1; transform: translate(-50%, 0); }

        @media (prefers-reduced-motion: reduce) {
            .env-card, .env, .env__flap, .after, .intro { transition-duration: .01s !important; transition-delay: 0s !important; }
            .wax-seal::after { animation: none; }
        }
    </style>
</head>
<body x-data="livingEnvelopeApp()" @keydown.enter.window="if (!isOpened) openEnvelope()">

    <canvas id="particleCanvas" aria-hidden="true"></canvas>

    <main>
        <header class="intro" :class="{ 'is-hidden': stage !== 'closed' }">
            <p class="eyebrow">{{ $card['occasion_label'] }}</p>
            <h1>
                @if($recipient !== '')
                    A card for <span class="script">{{ $recipient }}</span>
                @else
                    {{ $prompt }}
                @endif
            </h1>
            <p>Sealed with love by <strong>{{ $sender }}</strong></p>
        </header>

        <div class="scene" :class="{ 'is-open': stage !== 'closed', 'is-rising': stage === 'rising' || stage === 'revealed', 'is-revealed': stage === 'revealed' }">
            <div class="env-card" id="envCard">
                @include('festive.card', ['card' => $card, 'live' => false, 'tilt' => true, 'thumb' => false, 'animateIn' => false, 'styleExpr' => 'c.style'])
            </div>

            <div class="env" aria-hidden="true">
                <div class="env__back"></div>
                <div class="env__front"></div>
                <div class="env__address">
                    <div class="env__to">{{ $recipient !== '' ? 'For ' . $recipient : 'For you' }}</div>
                    <div class="env__from">From {{ $sender }}</div>
                </div>
                <div class="env__flap">
                    <div class="env__flap-face"></div>
                    <div class="env__flap-inner"></div>
                </div>
            </div>

            <div class="seal-anchor">
                <button type="button" class="wax-seal" :class="{ 'broken': isOpened }" @click="openEnvelope()" aria-label="Break the wax seal and open your card">
                    <span class="wax-seal__mono {{ mb_strlen((string)$card['monogram']) > 1 ? 'is-pair' : '' }}">{{ $card['monogram'] }}</span>
                </button>
            </div>
        </div>

        <p class="tap-hint" :class="{ 'is-hidden': isOpened }"><i class="bi bi-hand-index-thumb" aria-hidden="true"></i> Tap the seal to open</p>

        <section class="after" :class="{ 'is-in': stage === 'revealed' }" aria-label="Card actions">
            @if(!empty($card['audio_voice_url']))
            <div class="audio">
                <button type="button" class="btn" style="min-width:50px" @click="toggleAudio()" :aria-label="isPlayingAudio ? 'Pause voice note' : 'Play voice note'">
                    <i class="bi" :class="isPlayingAudio ? 'bi-pause-fill' : 'bi-play-fill'" aria-hidden="true"></i>
                </button>
                <span>Listen to the voice note</span>
                <audio id="festiveAudio" src="{{ $card['audio_voice_url'] }}" preload="none"></audio>
            </div>
            @endif
            <div class="actions">
                <button type="button" class="btn btn--wa" @click="shareWhatsApp()"><i class="bi bi-whatsapp" aria-hidden="true"></i> Share on WhatsApp</button>
                <a class="btn" href="{{ $card['portrait_image_url'] }}?download=1" download><i class="bi bi-download" aria-hidden="true"></i> Save card</a>
                <button type="button" class="btn" @click="replay()"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Replay</button>
                <a class="btn btn--gold" href="{{ $replyHref }}"><i class="bi bi-envelope-paper-heart" aria-hidden="true"></i> Send a card back — it’s free</a>
            </div>
        </section>
    </main>

    <footer>
        <span class="brand">FamilyPlatform</span>
        The private home for your family’s stories, tree and celebrations. <a href="{{ $replyHref }}">Create yours</a>
    </footer>

    <script nonce="{{ $nonce ?? '' }}" src="/public/js/festive-cards.js?v={{ assetVersion('js/festive-cards.js') }}"></script>
    <script nonce="{{ $nonce ?? '' }}">
        const FESTIVE = {
            particle: {!! json_encode((string)($card['particle'] ?? 'snow'), $jsonSafeFlags) !!},
            mood: {!! json_encode($mood, $jsonSafeFlags) !!},
            accent: {!! json_encode((string)($theme['accent_color'] ?? '#d4af37'), $jsonSafeFlags) !!},
            light: {!! json_encode((string)($theme['light_color'] ?? '#f6e7a8'), $jsonSafeFlags) !!},
            primary: {!! json_encode((string)($theme['primary_color'] ?? '#7a0f2a'), $jsonSafeFlags) !!},
            whatsapp: {!! json_encode((string)($card['whatsapp_text'] ?? ''), $jsonSafeFlags) !!},
            image: {!! json_encode((string)$card['portrait_image_url'], $jsonSafeFlags) !!},
        };

        function livingEnvelopeApp() {
            return {
                isOpened: false,
                stage: 'closed',
                isPlayingAudio: false,
                timers: [],

                init() {
                    if (window.FPCards) window.FPCards.prepareShareFile(FESTIVE.image, 'greeting-card.jpg');
                },

                openEnvelope() {
                    if (this.isOpened) return;
                    this.isOpened = true;
                    this.playChime();
                    const card = document.querySelector('#envCard .fc-card');
                    if (window.FPCards) window.FPCards.enableGyro(card);
                    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const t = (fn, ms) => this.timers.push(setTimeout(fn, reduce ? 0 : ms));
                    t(() => { this.stage = 'open'; }, 280);
                    t(() => { this.stage = 'rising'; }, 950);
                    t(() => {
                        this.stage = 'revealed';
                        if (card) { card.classList.add('fc-animate-in'); }
                        window.triggerParticleBurst && window.triggerParticleBurst();
                    }, 2000);
                },

                replay() {
                    this.timers.forEach(clearTimeout);
                    this.timers = [];
                    const card = document.querySelector('#envCard .fc-card');
                    if (card) card.classList.remove('fc-animate-in');
                    this.stage = 'closed';
                    this.isOpened = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const solemn = FESTIVE.mood === 'reverent';
                        const notes = solemn ? [220, 329.63] : [783.99, 987.77, 1174.66, 1567.98];
                        notes.forEach((f, i) => {
                            const start = ctx.currentTime + i * (solemn ? 0.5 : 0.11);
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(f, start);
                            gain.gain.setValueAtTime(0.0001, start);
                            gain.gain.exponentialRampToValueAtTime(solemn ? 0.16 : 0.12, start + 0.02);
                            gain.gain.exponentialRampToValueAtTime(0.0001, start + (solemn ? 3.2 : 1.4));
                            osc.connect(gain); gain.connect(ctx.destination);
                            osc.start(start); osc.stop(start + (solemn ? 3.3 : 1.5));
                        });
                    } catch (e) {}
                },

                toggleAudio() {
                    const audio = document.getElementById('festiveAudio');
                    if (!audio) return;
                    if (this.isPlayingAudio) { audio.pause(); this.isPlayingAudio = false; return; }
                    audio.play().catch(() => {});
                    this.isPlayingAudio = true;
                    audio.onended = () => { this.isPlayingAudio = false; };
                },

                shareWhatsApp() {
                    window.FPCards.shareWhatsApp({ text: FESTIVE.whatsapp, imageUrl: FESTIVE.image });
                },

                /* v1 API kept for callers/tests */
                async shareCard() { this.shareWhatsApp(); },
            };
        }

        /* ── Ambient particles tuned to the occasion's mood ───────────────── */
        (function () {
            const canvas = document.getElementById('particleCanvas');
            const ctx = canvas.getContext('2d');
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            let W = 0, H = 0;
            function size() { W = window.innerWidth; H = window.innerHeight; canvas.width = W * dpr; canvas.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); }
            size();
            window.addEventListener('resize', size);

            const type = FESTIVE.particle;
            const gold = [FESTIVE.light, FESTIVE.accent, '#fff4d1'];
            const palettes = {
                confetti: [FESTIVE.accent, FESTIVE.light, FESTIVE.primary, '#ffffff', '#f472b6', '#60a5fa'],
                fireworks: ['#ffd700', '#ff6b6b', '#38bdf8', '#c084fc', '#34d399', '#ffffff', FESTIVE.light],
                petals: ['#fbcfe8', '#f9a8d4', '#fde2e4', '#ffffff'],
                leaves: ['#c2410c', '#ea580c', '#b45309', '#a16207', '#7c2d12'],
                snow: ['#ffffff', '#e0f2fe', '#f8fafc'], stardust: ['#ffffff', FESTIVE.light], golddust: gold,
                sparks: ['#fde68a', '#fbbf24', '#f59e0b', '#fff7ed'], embers: ['#fde7c2', '#fcd9a8', '#ffffff'],
            };
            const colours = palettes[type] || palettes.snow;
            const small = window.innerWidth < 600;
            const counts = { confetti: 46, fireworks: 42, petals: 30, leaves: 26, snow: 74, stardust: 80, golddust: 70, sparks: 40, embers: 22 };
            const parts = [];
            const rand = (a, b) => a + Math.random() * (b - a);

            function spawnFirework(cx, cy, ringCount) {
                const baseCol = colours[Math.floor(Math.random() * colours.length)];
                const rays = ringCount || (small ? 22 : 34);
                for (let k = 0; k < rays; k++) {
                    const a = (Math.PI * 2 * k) / rays + rand(-0.06, 0.06);
                    const sp = rand(1.8, 5.4);
                    parts.push({
                        x: cx, y: cy,
                        vx: Math.cos(a) * sp,
                        vy: Math.sin(a) * sp,
                        r: rand(1.5, 2.8),
                        rot: a, vr: 0, sway: 0, tw: rand(0, Math.PI * 2),
                        c: k % 3 === 0 ? '#ffffff' : baseCol,
                        burst: 1, firework: 1, life: rand(0.75, 1.1)
                    });
                }
            }

            function make(burst) {
                const p = { x: rand(0, W), y: rand(-H, H), rot: rand(0, Math.PI * 2), vr: rand(-0.03, 0.03), sway: rand(0, Math.PI * 2), c: colours[Math.floor(Math.random() * colours.length)], tw: rand(0, Math.PI * 2) };
                switch (type) {
                    case 'confetti': Object.assign(p, { w: rand(5, 9), h: rand(8, 14), vy: rand(0.6, 1.6), vx: rand(-0.4, 0.4) }); break;
                    case 'petals': Object.assign(p, { w: rand(6, 10), h: rand(10, 15), vy: rand(0.4, 1.0), vx: rand(-0.3, 0.6) }); break;
                    case 'leaves': Object.assign(p, { w: rand(7, 12), h: rand(12, 18), vy: rand(0.5, 1.2), vx: rand(-0.3, 0.5) }); break;
                    case 'embers': Object.assign(p, { r: rand(2, 5), vy: rand(-0.35, -0.12), vx: rand(-0.15, 0.15), y: rand(0, H * 1.4) }); break;
                    case 'sparks':
                    case 'fireworks': Object.assign(p, { r: rand(1.1, 2.5), vy: rand(-1.2, -0.4), vx: rand(-0.3, 0.3), y: rand(0, H * 1.3) }); break;
                    case 'stardust':
                    case 'golddust': Object.assign(p, { r: rand(0.6, 2), vy: rand(0.08, 0.3), vx: rand(-0.12, 0.12) }); break;
                    default:
                        const isFlake = type === 'snow' && Math.random() < 0.28;
                        Object.assign(p, { r: isFlake ? rand(3.5, 6.2) : rand(1.2, 3.4), flake: isFlake, vy: rand(0.4, 1.15), vx: rand(-0.3, 0.3) });
                }
                if (burst) {
                    const a = rand(0, Math.PI * 2), sp = rand(3, 9);
                    Object.assign(p, { x: W / 2, y: H * 0.42, vx: Math.cos(a) * sp, vy: Math.sin(a) * sp - 3, burst: 1, life: 1, c: [...gold, ...colours][Math.floor(Math.random() * (gold.length + colours.length))] });
                    if (!p.w && type !== 'embers' && type !== 'fireworks') { p.w = rand(5, 9); p.h = rand(8, 13); }
                }
                return p;
            }
            for (let i = 0; i < Math.round((counts[type] || 50) * (small ? 0.6 : 1)); i++) parts.push(make(false));

            function draw(p) {
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.globalAlpha = p.burst ? Math.max(0, p.life) : 1;
                if (p.firework) {
                    ctx.strokeStyle = p.c;
                    ctx.lineWidth = p.r;
                    ctx.lineCap = 'round';
                    ctx.shadowColor = p.c;
                    ctx.shadowBlur = 8;
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.lineTo(-p.vx * 2.6, -p.vy * 2.6);
                    ctx.stroke();
                } else if (p.flake) {
                    ctx.rotate(p.rot);
                    ctx.strokeStyle = p.c;
                    ctx.lineWidth = 1.15;
                    ctx.lineCap = 'round';
                    ctx.globalAlpha = 0.82;
                    for (let k = 0; k < 3; k++) {
                        ctx.beginPath();
                        ctx.moveTo(-p.r, 0);
                        ctx.lineTo(p.r, 0);
                        ctx.stroke();
                        ctx.rotate(Math.PI / 3);
                    }
                } else if (type === 'embers' && !p.burst) {
                    const g = ctx.createRadialGradient(0, 0, 0, 0, 0, p.r * 4);
                    const a = 0.35 + 0.25 * Math.sin(p.tw);
                    g.addColorStop(0, 'rgba(255,240,210,' + a + ')');
                    g.addColorStop(1, 'rgba(255,200,140,0)');
                    ctx.fillStyle = g; ctx.beginPath(); ctx.arc(0, 0, p.r * 4, 0, Math.PI * 2); ctx.fill();
                } else if (p.w) {
                    ctx.rotate(p.rot);
                    ctx.fillStyle = p.c;
                    if (type === 'petals' || type === 'leaves') { ctx.beginPath(); ctx.ellipse(0, 0, p.w / 2, p.h / 2, 0, 0, Math.PI * 2); ctx.fill(); }
                    else { ctx.scale(1, Math.cos(p.tw)); ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h); }
                } else {
                    const tw = (type === 'stardust' || type === 'golddust' || type === 'fireworks') ? 0.45 + 0.55 * Math.abs(Math.sin(p.tw)) : 0.85;
                    ctx.globalAlpha *= tw;
                    ctx.fillStyle = p.c;
                    ctx.shadowColor = p.c; ctx.shadowBlur = type === 'snow' ? 0 : 6;
                    ctx.beginPath(); ctx.arc(0, 0, p.r, 0, Math.PI * 2); ctx.fill();
                }
                ctx.restore();
            }

            let fwTick = 0;
            function step() {
                ctx.clearRect(0, 0, W, H);
                if (type === 'fireworks' && !reduce) {
                    fwTick++;
                    if (fwTick % 75 === 15) {
                        spawnFirework(rand(W * 0.14, W * 0.86), rand(H * 0.12, H * 0.48));
                    }
                }
                for (let i = parts.length - 1; i >= 0; i--) {
                    const p = parts[i];
                    p.tw += 0.05; p.sway += 0.02; p.rot += p.vr;
                    if (p.burst) { p.vy += p.firework ? 0.055 : 0.12; p.vx *= p.firework ? 0.968 : 0.985; p.life -= p.firework ? 0.015 : 0.009; if (p.life <= 0) { parts.splice(i, 1); continue; } }
                    p.x += p.vx + (p.burst ? 0 : Math.sin(p.sway) * 0.35);
                    p.y += p.vy;
                    if (!p.burst) {
                        if (p.vy > 0 && p.y > H + 20) { p.y = -20; p.x = rand(0, W); }
                        if (p.vy < 0 && p.y < -20) { p.y = H + 20; p.x = rand(0, W); }
                        if (p.x > W + 20) p.x = -20; if (p.x < -20) p.x = W + 20;
                    }
                    draw(p);
                }
                if (!reduce) requestAnimationFrame(step);
            }
            step();

            window.triggerParticleBurst = function () {
                if (reduce || FESTIVE.mood === 'reverent') return; // remembrance: stillness, no confetti
                if (type === 'fireworks') {
                    spawnFirework(W * 0.25, H * 0.28, 36);
                    spawnFirework(W * 0.75, H * 0.26, 36);
                    spawnFirework(W * 0.5, H * 0.18, 44);
                }
                for (let i = 0; i < (small ? 70 : 120); i++) parts.push(make(true));
            };
        })();
    </script>
    <script nonce="{{ $nonce ?? '' }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</body>
</html>
