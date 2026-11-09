<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- OpenGraph & Social Previews -->
    <title>{{ $card['headline'] }} — From {{ $card['sender_name'] }}</title>
    <meta name="description" content="{{ $card['headline'] }}: {{ $card['verse'] }}">
    
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $card['headline'] }} ✨">
    <meta property="og:description" content="A warm festive greeting from {{ $card['sender_name'] }}. Tap to open your family envelope.">
    <meta property="og:image" content="{{ $card['absolute_image_url'] ?? $card['image_url'] }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ $card['absolute_public_url'] ?? $card['public_url'] }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $card['headline'] }} ✨">
    <meta name="twitter:description" content="A warm festive greeting from {{ $card['sender_name'] }}.">
    <meta name="twitter:image" content="{{ $card['absolute_image_url'] ?? $card['image_url'] }}">

    <link rel="icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <style>
        :root {
            --theme-bg: {{ $theme['primary_color'] ?? '#881337' }};
            --theme-accent: {{ $theme['accent_color'] ?? '#f59e0b' }};
            --theme-seal: {{ $theme['seal_color'] ?? '#b91c1c' }};
            --theme-gradient: {{ $theme['gradient'] ?? 'linear-gradient(135deg, #1e0910 0%, #4c0519 50%, #881337 100%)' }};
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #090205;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        #particleCanvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Living Envelope & 3D Stage ── */
        .envelope-stage {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 680px;
            margin: 40px auto 20px auto;
            perspective: 1200px;
            padding: 0 16px;
        }

        .envelope-box {
            position: relative;
            width: 100%;
            background: var(--theme-gradient);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75), 0 0 40px rgba(245, 158, 11, 0.2);
            border: 2px solid rgba(254, 240, 138, 0.35);
            overflow: visible;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Top Flap */
        .envelope-flap {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 140px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0.35) 100%);
            clip-path: polygon(0 0, 100% 0, 50% 100%);
            transform-origin: top center;
            transition: transform 0.8s ease-in-out;
            z-index: 25;
            pointer-events: none;
        }

        .envelope-flap.opened {
            transform: rotateX(180deg);
            z-index: 2;
        }

        /* Gold Wax Seal */
        .wax-seal {
            position: absolute;
            top: 95px;
            left: 50%;
            transform: translateX(-50%);
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #ef4444 0%, var(--theme-seal) 70%, #450a0a 100%);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6), inset 0 2px 4px rgba(255, 255, 255, 0.4);
            border: 2px solid rgba(254, 240, 138, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 30;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), filter 0.3s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .wax-seal:hover {
            transform: translateX(-50%) scale(1.08);
            filter: brightness(1.15);
        }

        .wax-seal.broken {
            animation: crackWax 0.6s forwards ease-out;
            pointer-events: none;
        }

        @keyframes crackWax {
            0% { transform: translateX(-50%) scale(1); filter: brightness(1.5); }
            50% { transform: translateX(-50%) scale(1.15) rotate(5deg); opacity: 0.8; }
            100% { transform: translateX(-50%) scale(0.4) rotate(-15deg); opacity: 0; display: none; }
        }

        .wax-seal-monogram {
            font-family: "Georgia", serif;
            font-weight: bold;
            font-size: 1.5rem;
            color: #fef08a;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
            letter-spacing: 1px;
        }

        /* ── The Unfolded Card Container ── */
        .card-inner-sheet {
            display: none;
            position: relative;
            z-index: 20;
            border-radius: 20px;
            padding: 36px 28px;
            margin: 12px;
            animation: slideUpCard 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .card-inner-sheet.revealed {
            display: block;
        }

        @keyframes slideUpCard {
            0% { opacity: 0; transform: translateY(40px) scale(0.96); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── The 7 Archetype Themes ── */

        /* 1. Classic Heritage */
        .fp-festive-card-classic {
            background: linear-gradient(135deg, #18050b 0%, #2e0815 50%, #4c0519 100%);
            border: 2px solid rgba(245, 158, 11, 0.55);
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.6);
        }

        /* 2. Luxury 3D Spatial */
        .fp-festive-card-spatial {
            background: linear-gradient(145deg, #1f1b2e 0%, #110e1c 100%);
            border: 2px solid #a855f7;
            transform-style: preserve-3d;
            box-shadow: 0 20px 50px rgba(168, 85, 247, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }

        /* 3. Translucent Frost */
        .fp-festive-card-translucent {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 0 20px rgba(255, 255, 255, 0.1);
        }

        /* 4. Hollow Die-Cut */
        .fp-festive-card-hollow {
            background: #111827;
            border: 3px dashed var(--theme-accent);
            box-shadow: inset 0 0 40px rgba(0, 0, 0, 0.9);
            position: relative;
        }

        /* 5. Plaque & Royal Engraving */
        .fp-festive-card-plaque {
            background: linear-gradient(180deg, #09090b 0%, #18181b 100%);
            border: 3px solid #d4af37;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.8), inset 0 2px 5px rgba(212, 175, 55, 0.5);
        }

        /* 6. Hyper-Reality Hologram */
        .fp-festive-card-holographic {
            background: linear-gradient(135deg, rgba(236,72,153,0.2) 0%, rgba(59,130,246,0.2) 50%, rgba(16,185,129,0.2) 100%);
            backdrop-filter: blur(16px);
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.35);
            animation: holoShift 8s infinite alternate ease-in-out;
        }

        @keyframes holoShift {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(90deg); }
        }

        /* 7. 3D Origami Pop-Up */
        .fp-festive-card-origami {
            background: #ffffff;
            color: #1e293b;
            border: 3px solid #e11d48;
            box-shadow: 0 25px 60px rgba(225, 29, 72, 0.35);
        }
        .fp-festive-card-origami .card-verse {
            color: #334155 !important;
        }
        .fp-festive-card-origami .card-sender {
            color: #9f1239 !important;
        }
        .fp-festive-card-origami .card-headline {
            color: #0f172a !important;
        }

        /* Hero Photo Frame */
        .festive-hero-wrap {
            max-width: 380px;
            margin: 0 auto 24px auto;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            border: 2px solid rgba(254, 240, 138, 0.4);
        }

        .festive-hero-img {
            width: 100%;
            height: auto;
            max-height: 320px;
            object-fit: cover;
            display: block;
        }

        /* Audio Note Player */
        .festive-audio-dock {
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 10px 20px;
            max-width: 340px;
            margin: 16px auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ── Viral Invitation Banner ── */
        .viral-growth-dock {
            position: relative;
            z-index: 20;
            background: linear-gradient(180deg, transparent 0%, rgba(15, 23, 42, 0.95) 30%, #0f172a 100%);
            padding: 40px 20px 30px 20px;
            text-align: center;
            margin-top: auto;
        }

        .btn-viral-cta {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            font-weight: 700;
            border-radius: 999px;
            padding: 14px 32px;
            min-height: 48px;
            border: none;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            transition: transform 0.2s, filter 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-viral-cta:hover {
            transform: scale(1.03);
            filter: brightness(1.1);
            color: #ffffff;
        }
    </style>
</head>
<body x-data="livingEnvelopeApp()">

    <!-- 60fps Particle Dynamics Canvas (Snow, Petals, Confetti) -->
    <canvas id="particleCanvas"></canvas>

    <main class="container d-flex flex-column align-items-center justify-content-center py-4 flex-grow-1">

        <!-- Top Greeting Invitation Prompt (Pre-Open) -->
        <div class="text-center mb-3" x-show="!isOpened" x-transition.opacity>
            <span class="badge rounded-pill px-3 py-1.5 fw-bold mb-2 shadow-sm"
                  style="background: rgba(254, 240, 138, 0.2); color: #fef08a; border: 1px solid rgba(254, 240, 138, 0.4); font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase;">
                {{ $occasion['title'] }}
            </span>
            <h2 class="fw-bold text-white mb-1" style="font-size: 1.4rem;">
                A Special Holiday Card for You
            </h2>
            <p class="text-white-50 small mb-0">
                From <strong>{{ $card['sender_name'] }}</strong> &bull; Tap the gold wax seal to open
            </p>
        </div>

        <!-- 3D Living Envelope Stage -->
        <div class="envelope-stage">
            <div class="envelope-box">
                
                <!-- Envelope Flap -->
                <div class="envelope-flap" :class="{ 'opened': isOpened }"></div>

                <!-- Interactive Wax Seal -->
                <div class="wax-seal" 
                     :class="{ 'broken': isOpened }"
                     @click="openEnvelope()"
                     title="Tap to break seal and open card"
                     aria-label="Break wax seal and open card"
                     role="button">
                    <span class="wax-seal-monogram">FP</span>
                </div>

                <!-- Unfolded Card Interior -->
                <div class="card-inner-sheet {{ $templateStyle['card_class'] ?? 'fp-festive-card-classic' }}" 
                     :class="{ 'revealed': isOpened }">
                    
                    <!-- Decorative Corner Filigree -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge rounded-pill fw-bold px-3 py-1"
                              style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.75rem;">
                            {{ $templateStyle['badge'] ?? 'Festive Keepsake' }}
                        </span>
                        <span class="small text-white-50" style="font-size: 0.8rem;">
                            {{ $occasion['badge'] }}
                        </span>
                    </div>

                    @if(!empty($card['hero_photo_url']))
                    <div class="festive-hero-wrap">
                        <img src="{{ $card['hero_photo_url'] }}" alt="Family Holiday Photo" class="festive-hero-img">
                    </div>
                    @endif

                    <!-- Headline -->
                    <h1 class="card-headline text-center fw-bold mb-3" style="font-size: 1.85rem; letter-spacing: -0.02em; font-family: 'Georgia', serif;">
                        {{ $card['headline'] }}
                    </h1>

                    @if(!empty($card['recipient_name']))
                    <h5 class="text-center text-warning fw-semibold mb-3" style="font-size: 1.1rem;">
                        Dearest {{ $card['recipient_name'] }},
                    </h5>
                    @endif

                    <!-- Verse Body -->
                    <p class="card-verse text-center mb-4 text-white" style="font-size: 1.05rem; line-height: 1.7; font-family: -apple-system, sans-serif;">
                        {!! nl2br(e($card['verse'])) !!}
                    </p>

                    <!-- Sign-Off & Sender -->
                    <div class="text-center pt-2 border-top border-white-50">
                        <p class="text-white-50 mb-1 fst-italic" style="font-size: 0.95rem;">
                            {{ $card['sign_off'] }}
                        </p>
                        <h4 class="card-sender fw-bold" style="color: #fef08a; font-family: 'Georgia', serif; font-size: 1.5rem;">
                            {{ $card['sender_name'] }}
                        </h4>
                    </div>

                    <!-- Audio Note Player (If present) -->
                    @if(!empty($card['audio_voice_url']))
                    <div class="festive-audio-dock">
                        <button type="button" class="btn btn-sm btn-warning rounded-circle" @click="toggleAudio()" style="width: 36px; height: 36px;">
                            <i class="bi" :class="isPlayingAudio ? 'bi-pause-fill' : 'bi-play-fill'"></i>
                        </button>
                        <span class="small text-white fw-semibold">Listen to Family Voice Note</span>
                        <audio id="festiveAudio" src="{{ $card['audio_voice_url'] }}" preload="none"></audio>
                    </div>
                    @endif

                    <!-- Share / Download Action Pill Strip -->
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mt-4 pt-2">
                        <button type="button" 
                                class="btn btn-outline-light rounded-pill px-3 py-2 btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                                @click="shareCard()"
                                style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-share-fill"></i>
                            <span>Share with Family &amp; Friends</span>
                        </button>
                        <a href="{{ $card['image_url'] }}" 
                           download="festive_card_{{ $card['share_token'] }}.png"
                           class="btn btn-outline-warning rounded-pill px-3 py-2 btn-sm fw-semibold d-inline-flex align-items-center gap-2"
                           style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-download"></i>
                            <span>Save Image</span>
                        </a>
                        <a href="/register?ref=festive_{{ $card['share_token'] }}@if(!empty($card['fam_code']))&famCode={{ preg_replace('/^#+/', '', $card['fam_code']) }}@endif" 
                           class="btn rounded-pill px-3 py-2 btn-sm fw-bold d-inline-flex align-items-center gap-2 text-white shadow-sm"
                           style="background: var(--theme-accent, #f59e0b); min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-reply-fill"></i>
                            <span>Reply with a Card</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Viral Growth Invitation Dock -->
    <footer class="viral-growth-dock">
        <div class="container" style="max-width: 580px;">
            <p class="text-white-50 small mb-2" style="font-size: 0.88rem;">
                Crafted with love on <strong>FamilyPlatform</strong> &bull; The private sanctuary for family heritage &amp; celebration.
            </p>
            <h5 class="text-white fw-bold mb-3" style="font-size: 1.15rem;">
                Send your own family’s festive greetings or build your 7-generation ancestral tree.
            </h5>
            <a href="/register?ref=festive_{{ $card['share_token'] }}@if(!empty($card['fam_code']))&famCode={{ preg_replace('/^#+/', '', $card['fam_code']) }}@endif" class="btn-viral-cta">
                <i class="bi bi-stars fs-5"></i>
                <span>Create Your Family’s Free Festive Card</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </footer>

    <!-- Audio Chime Synthesizer & Particle Simulation Engine -->
    <script>
        function livingEnvelopeApp() {
            return {
                isOpened: false,
                isPlayingAudio: false,

                openEnvelope() {
                    if (this.isOpened) return;
                    this.isOpened = true;
                    this.playChime();
                    triggerParticleBurst();
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                        osc.frequency.exponentialRampToValueAtTime(880.00, ctx.currentTime + 0.3); // A5
                        osc.frequency.exponentialRampToValueAtTime(1174.66, ctx.currentTime + 0.6); // D6
                        
                        gain.gain.setValueAtTime(0.01, ctx.currentTime);
                        gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + 0.1);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.8);
                        
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.85);
                    } catch (e) {}
                },

                toggleAudio() {
                    const audio = document.getElementById('festiveAudio');
                    if (!audio) return;
                    if (this.isPlayingAudio) {
                        audio.pause();
                        this.isPlayingAudio = false;
                    } else {
                        audio.play().catch(() => {});
                        this.isPlayingAudio = true;
                        audio.onended = () => { this.isPlayingAudio = false; };
                    }
                },

                async shareCard() {
                    const shareData = {
                        title: {!! json_encode((string)($card['headline'] ?? 'Festive Greetings'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                        text: {!! json_encode('A warm holiday card from ' . ((string)($card['sender_name'] ?? 'Family')) . ' on FamilyPlatform ✨', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!},
                        url: window.location.href
                    };

                    if (navigator.share) {
                        try {
                            await navigator.share(shareData);
                        } catch (e) {}
                    } else {
                        navigator.clipboard.writeText(window.location.href);
                        alert('Card link copied to clipboard! Paste it into WhatsApp or iMessage to send.');
                    }
                }
            };
        }

        // ── 60fps Particle Engine ──
        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        });

        const particleType = {!! json_encode((string)($occasion['particle'] ?? 'snow'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
        const particles = [];
        const count = window.innerWidth < 600 ? 35 : 70;

        for (let i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                r: Math.random() * 3 + 1.5,
                dx: Math.random() * 1.5 - 0.75,
                dy: Math.random() * 1.2 + 0.8,
                color: particleType === 'confetti' 
                    ? ['#f59e0b', '#ec4899', '#3b82f6', '#10b981', '#fef08a'][Math.floor(Math.random() * 5)]
                    : (particleType === 'petals' ? '#f472b6' : '#ffffff'),
                opacity: Math.random() * 0.7 + 0.3
            });
        }

        function renderParticles() {
            ctx.clearRect(0, 0, width, height);
            for (let p of particles) {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.opacity;
                ctx.fill();

                p.x += p.dx;
                p.y += p.dy;

                if (p.y > height) {
                    p.y = -5;
                    p.x = Math.random() * width;
                }
                if (p.x > width) p.x = 0;
                if (p.x < 0) p.x = width;
            }
            requestAnimationFrame(renderParticles);
        }
        renderParticles();

        function triggerParticleBurst() {
            for (let i = 0; i < 60; i++) {
                particles.push({
                    x: width / 2,
                    y: height * 0.4,
                    r: Math.random() * 4 + 2,
                    dx: (Math.random() - 0.5) * 8,
                    dy: (Math.random() - 0.7) * 8,
                    color: ['#fbbf24', '#f43f5e', '#ffffff', '#38bdf8'][Math.floor(Math.random() * 4)],
                    opacity: 1
                });
            }
        }
    </script>
</body>
</html>
