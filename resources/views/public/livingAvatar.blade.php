<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ogTitle }}</title>
    @include('partials.og-meta', [
        'ogTitle' => $ogTitle,
        'ogDescription' => $ogDescription,
        'ogImage' => rtrim((string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/') . '/public/images/og-living-avatar.jpg?v=1',
    ])
    <link rel="stylesheet" href="/public/css/main.css?v={{ assetVersion('css/main.css') }}">
    <link rel="stylesheet" href="/public/css/atg-emoji.css?v={{ assetVersion('css/atg-emoji.css') }}">
    <style nonce="{{ csp_nonce($nonce ?? null) }}">
        body.fp-public-living {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top, #1e293b 0%, #0f172a 65%, #020617 100%);
            color: #f8fafc;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px 48px;
        }
        .fp-teaser-shell {
            width: min(100%, 540px);
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
            text-align: center;
        }
        .fp-teaser-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.16);
            border: 1px solid rgba(245, 158, 11, 0.45);
            color: #fde68a;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .fp-teaser-title {
            margin: 14px 0 8px;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
        }
        .fp-teaser-sub {
            margin: 0 0 20px;
            font-size: 0.95rem;
            color: #cbd5e1;
            line-height: 1.5;
        }
        .fp-teaser-upload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 18px;
            border-radius: 999px;
            background: #1e293b;
            border: 1px solid #475569;
            color: #f8fafc;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 16px;
            white-space: nowrap;
        }
        .fp-teaser-upload:hover { background: #334155; }
        .fp-teaser-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            width: 100%;
            margin-top: 14px;
            padding: 12px 20px;
            border-radius: 12px;
            background: linear-gradient(135deg, #a31621 0%, #7d1018 100%);
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .fp-teaser-frames {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 14px 0;
        }
        .fp-frame-pill {
            min-height: 44px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid #334155;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .fp-frame-pill[aria-pressed="true"] {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.18);
            color: #fde68a;
        }
    </style>
</head>
<body class="fp-public-living">
    @include('component.submitSpinner')
    <main class="fp-teaser-shell">
        <span class="fp-teaser-badge">✨ ATG Living Avatar Challenge</span>
        <h1 class="fp-teaser-title">
            @if ($fromName !== '')
                {{ $fromName }} challenged you: {{ $reactionLabel }} {{ $reactionEmoji }}
            @else
                Bring Your Portrait to Life
            @endif
        </h1>
        <p class="fp-teaser-sub">
            Upload any selfie — it stays 100% on your device — and watch it breathe, blink, follow your cursor and burst with reactions.
        </p>

        <div class="fp-teaser-frames" role="group" aria-label="Upload and duet controls">
            <label class="fp-teaser-upload" for="fpPublicSelfieInput" style="margin-bottom:0;">
                <span>📸 Try with your own photo</span>
                <input type="file" id="fpPublicSelfieInput" accept="image/png,image/jpeg,image/webp" hidden>
            </label>
            <label class="fp-teaser-upload" for="fpPublicDuetInput" style="margin-bottom:0;">
                <span>🤝 Merge 2nd photo (Sync Duet)</span>
                <input type="file" id="fpPublicDuetInput" accept="image/png,image/jpeg,image/webp" hidden>
            </label>
            <button type="button" id="fpPublicDuetClearBtn" class="fp-frame-pill" hidden>✕ Solo</button>
            <button type="button" id="fpPublicVoiceBtn" class="fp-frame-pill" aria-pressed="false">🎤 Talk &amp; sing</button>
        </div>

        <div class="atg-canvas-viewport" id="fpPublicViewport" tabindex="0" role="button"
             aria-label="Interactive living avatar preview. Tap to trigger reaction.">
            <canvas id="fpPublicCanvas" class="atg-photo-living-stage" width="512" height="512"></canvas>
        </div>

        <div class="atg-reaction-bar" role="group" aria-label="Avatar reactions">
            <button type="button" class="atg-react-btn" data-reaction="hello" aria-label="Wave hello">👋</button>
            <button type="button" class="atg-react-btn" data-reaction="laugh" aria-label="Laugh">😂</button>
            <button type="button" class="atg-react-btn" data-reaction="wow" aria-label="Wow">😮</button>
            <button type="button" class="atg-react-btn" data-reaction="love" aria-label="Love">❤️</button>
            <button type="button" class="atg-react-btn" data-reaction="nod" aria-label="Nod yes">🙆</button>
            <button type="button" class="atg-react-btn" data-reaction="dance" aria-label="Dance">💃</button>
            <button type="button" class="atg-react-btn" data-reaction="point" aria-label="Point at you">🫵</button>
            <button type="button" class="atg-react-btn" data-reaction="clap" aria-label="Clap">👏</button>
        </div>

        <div class="fp-teaser-frames" role="group" aria-label="Challenge frames">
            <button type="button" class="fp-frame-pill" data-frame="none" aria-pressed="false">Clean</button>
            <button type="button" class="fp-frame-pill" data-frame="birthday" aria-pressed="false">🥂 Birthday Toast</button>
            <button type="button" class="fp-frame-pill" data-frame="sunday" aria-pressed="false">💃 Sunday Groove</button>
            <button type="button" class="fp-frame-pill" data-frame="proud" aria-pressed="false">👏 Proud Moment</button>
            <button type="button" class="fp-frame-pill" data-frame="surprise" aria-pressed="false">😮 Big News!</button>
        </div>

        <div class="atg-photo-export-row">
            <button type="button" id="fpPublicDownloadBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true">
                <span>⬇️ Video MP4</span>
            </button>
            <button type="button" id="fpPublicGifBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true">
                <span>🎞️ Loop GIF</span>
            </button>
            <button type="button" id="fpPublicShareBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true">
                <span>📤 Share</span>
            </button>
        </div>
        <div class="fp-teaser-frames" style="margin-top:10px;">
            <select id="fpPublicAspectSelect" class="fp-frame-pill" aria-label="Export aspect ratio">
                <option value="1:1" selected>1:1 Square</option>
                <option value="9:16">9:16 Story / Reels</option>
                <option value="16:9">16:9 Widescreen</option>
                <option value="4:5">4:5 Portrait</option>
                <option value="3:4">3:4 Classic</option>
                <option value="2:3">2:3 Tall</option>
                <option value="3:2">3:2 Photo</option>
                <option value="4:3">4:3 Standard</option>
                <option value="5:4">5:4 Print</option>
                <option value="9:21">9:21 Ultra Tall</option>
                <option value="21:9">21:9 Cinema</option>
            </select>
        </div>

        <a href="/register" class="fp-teaser-cta">
            <span>🚀 Sign up free to save &amp; unlock full studio</span>
        </a>
    </main>

    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/manifest.js?v={{ assetVersion('js/manifest.js') }}" defer></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/vendor.js?v={{ assetVersion('js/vendor.js') }}" defer></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignVideoStudioClient.js?v={{ assetVersion('js/SovereignVideoStudioClient.js') }}" defer></script>
    <script type="module" nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/atg-living-photo-avatar.js?v={{ assetVersion('js/atg-living-photo-avatar.js') }}"></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}">
        document.addEventListener('DOMContentLoaded', async function () {
            const canvas = document.getElementById('fpPublicCanvas');
            const viewport = document.getElementById('fpPublicViewport');
            if (!canvas || !viewport || typeof window.AtgLivingPhotoAvatarStage !== 'function') return;

            const stage = new window.AtgLivingPhotoAvatarStage({
                canvas: canvas,
                viewport: viewport,
                photoUrl: '/public/img/hero-family.webp',
                onVoiceStateChange: (active) => {
                    const vBtn = document.getElementById('fpPublicVoiceBtn');
                    if (vBtn) vBtn.setAttribute('aria-pressed', String(Boolean(active)));
                },
                onDuetStateChange: (active) => {
                    const cBtn = document.getElementById('fpPublicDuetClearBtn');
                    if (cBtn) cBtn.hidden = !active;
                },
            });
            await stage.init();
            stage.unlockPack(@json($pack));
            stage.setPack(@json($pack));
            stage.setFrame(@json($frame));
            stage.start();
            stage.triggerReaction(@json($reaction), { playSound: false });

            document.querySelectorAll('.fp-frame-pill[data-frame]').forEach(btn => {
                btn.setAttribute('aria-pressed', String(btn.dataset.frame === @json($frame)));
                btn.addEventListener('click', () => {
                    stage.setFrame(btn.dataset.frame);
                    document.querySelectorAll('.fp-frame-pill').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
                });
            });

            document.querySelectorAll('.atg-react-btn[data-reaction]').forEach(btn => {
                btn.addEventListener('click', () => stage.triggerReaction(btn.dataset.reaction, { playSound: true }));
            });

            const input = document.getElementById('fpPublicSelfieInput');
            if (input) {
                input.addEventListener('change', (e) => {
                    const file = e.target && e.target.files && e.target.files[0];
                    if (file) stage.loadCustomFile(file);
                });
            }

            const duetInput = document.getElementById('fpPublicDuetInput');
            const duetClear = document.getElementById('fpPublicDuetClearBtn');
            if (duetInput) {
                duetInput.addEventListener('change', (e) => {
                    const file = e.target && e.target.files && e.target.files[0];
                    if (file) stage.loadDuetFile(file);
                });
            }
            if (duetClear) {
                duetClear.addEventListener('click', () => {
                    stage.clearDuet();
                    if (duetInput) duetInput.value = '';
                });
            }

            const voiceBtn = document.getElementById('fpPublicVoiceBtn');
            if (voiceBtn) {
                voiceBtn.addEventListener('click', async () => {
                    try { await stage.toggleVoiceReactive(); } catch (_) {}
                });
            }

            const bindExport = (id, fn) => {
                const btn = document.getElementById(id);
                if (!btn) return;
                btn.addEventListener('click', async () => {
                    if (btn.dataset.busy === '1') return;
                    btn.dataset.busy = '1';
                    btn.disabled = true;
                    if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
                    try { await fn(); } finally {
                        btn.dataset.busy = '';
                        btn.disabled = false;
                        if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
                    }
                });
            };
            bindExport('fpPublicDownloadBtn', () => stage.downloadAnimatedVideo());
            bindExport('fpPublicGifBtn', () => stage.downloadAnimatedGif());
            bindExport('fpPublicShareBtn', () => stage.shareAnimatedVideo());
            const aspectSel = document.getElementById('fpPublicAspectSelect');
            if (aspectSel) {
                aspectSel.addEventListener('change', () => stage.setAspectRatio(aspectSel.value));
            }
        });
    </script>
</body>
</html>
