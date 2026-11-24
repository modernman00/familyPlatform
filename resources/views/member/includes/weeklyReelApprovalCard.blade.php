@php
    $hasPendingReel = !empty($pendingWeeklyReel);
    $reel = $pendingWeeklyReel ?? null;
    $storyboard = $reel['storyboard'] ?? [];
    $scenes = $storyboard['scenes'] ?? [];
    $audioUrl = $reel['audio_url'] ?? '/public/audio/reels/library/acoustic_warmth.wav';
    $musicTitle = $reel['music_title'] ?? 'Acoustic Warmth (Family Highlights)';
    $reelId = $reel['id'] ?? 0;
    $reelTitle = $reel['title'] ?? 'Our Family Fun Facts';
    $duration = $reel['duration_seconds'] ?? 35;
@endphp

@if($hasPendingReel)
<style>
#weeklyReelApprovalCard.weekly-reel-card {
    border-radius: 20px;
    overflow: hidden;
    background: linear-gradient(145deg, #ffffff 0%, #fdfbf7 100%);
    border: 1.5px solid #e2e8f0;
}
#weeklyReelApprovalCard .weekly-reel-header,
#weeklyReelApprovalCard .weekly-reel-footer {
    background: rgba(248, 250, 252, 0.92);
}
#weeklyReelApprovalCard .weekly-reel-stage-wrap {
    background: #0b1120;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
}
#weeklyReelApprovalCard .weekly-reel-canvas-frame {
    position: relative;
    width: 100%;
    border-radius: 14px;
    overflow: hidden;
    background: #0f172a;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45);
    transition: max-width 0.25s ease, aspect-ratio 0.25s ease;
}
#weeklyReelApprovalCard .weekly-reel-canvas-frame.ratio-16-9 {
    aspect-ratio: 16 / 9;
    max-width: 100%;
}
#weeklyReelApprovalCard .weekly-reel-canvas-frame.ratio-9-16 {
    aspect-ratio: 9 / 16;
    max-width: 280px;
    margin: 0 auto;
}
body.dark-mode #weeklyReelApprovalCard.weekly-reel-card,
body.dark-theme #weeklyReelApprovalCard.weekly-reel-card,
[data-bs-theme="dark"] #weeklyReelApprovalCard.weekly-reel-card,
html.dark #weeklyReelApprovalCard.weekly-reel-card,
html.dark-mode #weeklyReelApprovalCard.weekly-reel-card {
    background: #141418 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode #weeklyReelApprovalCard .weekly-reel-header,
body.dark-theme #weeklyReelApprovalCard .weekly-reel-header,
[data-bs-theme="dark"] #weeklyReelApprovalCard .weekly-reel-header,
html.dark #weeklyReelApprovalCard .weekly-reel-header,
html.dark-mode #weeklyReelApprovalCard .weekly-reel-header,
body.dark-mode #weeklyReelApprovalCard .weekly-reel-footer,
body.dark-theme #weeklyReelApprovalCard .weekly-reel-footer,
[data-bs-theme="dark"] #weeklyReelApprovalCard .weekly-reel-footer,
html.dark #weeklyReelApprovalCard .weekly-reel-footer,
html.dark-mode #weeklyReelApprovalCard .weekly-reel-footer {
    background: #18181f !important;
    border-color: rgba(255, 255, 255, 0.09) !important;
    color: #f8fafc !important;
}
body.dark-mode #weeklyReelApprovalCard .weekly-reel-title-text,
body.dark-theme #weeklyReelApprovalCard .weekly-reel-title-text,
[data-bs-theme="dark"] #weeklyReelApprovalCard .weekly-reel-title-text,
html.dark #weeklyReelApprovalCard .weekly-reel-title-text,
html.dark-mode #weeklyReelApprovalCard .weekly-reel-title-text {
    color: #f8fafc !important;
}
</style>

<div id="weeklyReelApprovalCard"
     x-data="weeklyReelPlayer({{ (int)$reelId }}, {{ json_encode($scenes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}, '{{ addslashes((string)$audioUrl) }}')"
     x-show="visible"
     x-cloak
     class="card mb-4 shadow-sm border-0 weekly-reel-card">

    <!-- Header Banner -->
    <div class="px-3 px-md-4 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom weekly-reel-header">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge rounded-pill px-3 py-2 text-white fw-bold"
                  style="background: linear-gradient(135deg, #b8202c 0%, #8b131d 100%); font-size: 0.75rem; letter-spacing: 0.04em; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-stars me-1" aria-hidden="true"></i> WEEKLY FUN FACTS ({{ (int)$duration }}s)
            </span>
            <span class="text-muted small fw-medium">60fps Motion Presentation</span>
        </div>

        <!-- Aspect Ratio Switcher (16:9 & 9:16) + Refresh + Dismiss -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group" role="group" aria-label="Presentation aspect ratio">
                <button type="button"
                        @click="setAspectRatio('16:9')"
                        :class="aspectRatio === '16:9' ? 'btn btn-sm btn-primary fw-bold' : 'btn btn-sm btn-outline-secondary fw-medium'"
                        style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                    16:9
                </button>
                <button type="button"
                        @click="setAspectRatio('9:16')"
                        :class="aspectRatio === '9:16' ? 'btn btn-sm btn-primary fw-bold' : 'btn btn-sm btn-outline-secondary fw-medium'"
                        style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                    9:16
                </button>
            </div>

            <button type="button"
                    @click="regenerateReel($event)"
                    data-spin="true"
                    class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                    title="Refresh weekly fun facts"
                    style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                <span class="d-none d-sm-inline">Refresh</span>
            </button>

            <button type="button"
                    @click="dismissReel()"
                    class="btn btn-sm btn-link text-muted p-1 d-inline-flex align-items-center justify-content-center"
                    title="Dismiss for this week"
                    aria-label="Dismiss weekly fun facts"
                    style="min-width: 44px; min-height: 44px; text-decoration: none; flex-shrink: 0;">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <!-- 60fps Canvas Motion Stage -->
    <div class="weekly-reel-stage-wrap">
        <div class="weekly-reel-canvas-frame" :class="aspectRatio === '9:16' ? 'ratio-9-16' : 'ratio-16-9'">
            <canvas x-ref="motionCanvas" class="w-100 h-100 d-block"></canvas>

            <!-- Audio Track (Optional Background Music) -->
            <audio x-ref="audioTrack" preload="none" loop>
                <source :src="audioUrl" type="audio/wav">
                <source :src="audioUrl" type="audio/mpeg">
            </audio>
        </div>
    </div>

    <!-- Transport & Scene Scrubber -->
    <div class="px-3 px-md-4 py-2 bg-dark text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button"
                    @click="prevScene()"
                    class="btn btn-sm btn-outline-light rounded-circle d-inline-flex align-items-center justify-content-center"
                    style="min-width: 44px; min-height: 44px; flex-shrink: 0;"
                    aria-label="Previous scene">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button"
                    @click="togglePlay()"
                    class="btn btn-sm btn-light text-dark rounded-circle fw-bold d-inline-flex align-items-center justify-content-center"
                    style="min-width: 44px; min-height: 44px; flex-shrink: 0;"
                    aria-label="Play or pause presentation">
                <i :class="isPlaying ? 'bi bi-pause-fill' : 'bi bi-play-fill'" aria-hidden="true"></i>
            </button>
            <button type="button"
                    @click="nextScene()"
                    class="btn btn-sm btn-outline-light rounded-circle d-inline-flex align-items-center justify-content-center"
                    style="min-width: 44px; min-height: 44px; flex-shrink: 0;"
                    aria-label="Next scene">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
            <span class="small font-monospace text-white-50 ms-1" x-text="timecodeLabel">00.0s</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button"
                    @click="toggleSfx()"
                    class="btn btn-sm btn-outline-light rounded-pill px-3 d-inline-flex align-items-center gap-1"
                    style="min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                    aria-label="Toggle motion sound effects">
                <i :class="sfxEnabled ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill'" aria-hidden="true"></i>
                <span x-text="sfxEnabled ? 'SFX On' : 'SFX Off'">SFX Off</span>
            </button>
            <button type="button"
                    @click="exportVideo($event)"
                    data-spin="true"
                    class="btn btn-sm btn-outline-info rounded-pill px-3 d-inline-flex align-items-center gap-1"
                    style="min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                    title="Render and download video clip">
                <i class="bi bi-download" aria-hidden="true"></i>
                <span x-text="isExporting ? 'Rendering…' : 'Save Video'">Save Video</span>
            </button>
        </div>
    </div>

    <!-- Action Confirmation & Approval Bar -->
    <div class="p-3 px-md-4 weekly-reel-footer border-top d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="pe-2" style="max-width: 520px;">
            <div class="fw-bold weekly-reel-title-text text-dark" style="font-size: 0.95rem;">
                <i class="bi bi-heart-fill text-danger me-1" aria-hidden="true"></i> {{ $reelTitle }}
            </div>
            <div class="text-muted small">
                Private until you approve. Share this <span x-text="aspectRatio">16:9</span> fun-facts presentation to your family feed &amp; Reels tray.
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button"
                    @click="dismissReel()"
                    class="btn btn-outline-secondary px-3"
                    style="min-height: 44px; border-radius: 12px; font-weight: 500; white-space: nowrap; flex-shrink: 0;">
                Not This Week
            </button>

            <button type="button"
                    @click="approveReel($event)"
                    class="btn btn-success px-4 text-white fw-bold btn-spin-on-click d-inline-flex align-items-center gap-2"
                    data-spin="true"
                    style="min-height: 44px; border-radius: 12px; background: #16a34a; border-color: #16a34a; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-check-lg" aria-hidden="true"></i>
                <span>Approve &amp; Share</span>
            </button>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
function weeklyReelPlayer(reelId, scenes, audioUrl) {
    return {
        reelId: reelId,
        scenes: Array.isArray(scenes) ? scenes : [],
        audioUrl: audioUrl || '',
        aspectRatio: '16:9',
        isPlaying: true,
        sfxEnabled: false,
        isExporting: false,
        visible: true,
        elapsedMs: 0,
        totalMs: 30000,
        sceneTimings: [],
        lastSceneIdx: -1,
        lastFrameTs: null,
        rafId: null,
        audioCtx: null,
        imageCache: {},
        timecodeLabel: '00.0s',

        init() {
            if (!this.scenes.length) return;
            this.buildTimings();
            this.preloadImages();
            this.$nextTick(() => {
                this.resizeCanvas();
                window.addEventListener('resize', () => this.resizeCanvas());
                this.startLoop();
            });
        },

        buildTimings() {
            let cursor = 0;
            this.sceneTimings = this.scenes.map((s, idx) => {
                const dur = Math.max(2500, ((Number(s.duration) || 5) * 1000));
                const entry = { idx, start: cursor, end: cursor + dur, dur, scene: s };
                cursor += dur;
                return entry;
            });
            this.totalMs = Math.max(1000, cursor);
        },

        safeMediaUrl(url) {
            if (!url || typeof url !== 'string') return '';
            const trimmed = url.trim();
            if (trimmed.startsWith('/') && !trimmed.startsWith('//')) return trimmed;
            if (trimmed.startsWith('https://') || trimmed.startsWith('http://')) return trimmed;
            return '';
        },

        preloadImages() {
            this.scenes.forEach((s) => {
                const url = this.safeMediaUrl(s.image_url);
                if (url && !this.imageCache[url]) {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.src = url;
                    this.imageCache[url] = img;
                }
            });
        },

        setAspectRatio(ratio) {
            this.aspectRatio = ratio === '9:16' ? '9:16' : '16:9';
            this.$nextTick(() => {
                this.resizeCanvas();
                this.renderFrame(this.elapsedMs);
            });
        },

        resizeCanvas() {
            const canvas = this.$refs.motionCanvas;
            if (!canvas) return;
            if (this.aspectRatio === '9:16') {
                canvas.width = 540;
                canvas.height = 960;
            } else {
                canvas.width = 960;
                canvas.height = 540;
            }
        },

        playChime(freq = 480, dur = 0.12) {
            if (!this.sfxEnabled) return;
            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(freq * 1.16, this.audioCtx.currentTime + dur);
                gain.gain.setValueAtTime(0.045, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + dur);
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start();
                osc.stop(this.audioCtx.currentTime + dur);
            } catch (_) {}
        },

        toggleSfx() {
            this.sfxEnabled = !this.sfxEnabled;
            if (this.sfxEnabled) {
                this.playChime(587.33, 0.14);
                if (this.$refs.audioTrack && this.isPlaying) {
                    this.$refs.audioTrack.play().catch(() => {});
                }
            } else if (this.$refs.audioTrack) {
                this.$refs.audioTrack.pause();
            }
        },

        togglePlay() {
            this.isPlaying = !this.isPlaying;
            if (this.$refs.audioTrack) {
                if (this.isPlaying && this.sfxEnabled) {
                    this.$refs.audioTrack.play().catch(() => {});
                } else {
                    this.$refs.audioTrack.pause();
                }
            }
        },

        prevScene() {
            const cur = this.sceneTimings.find(t => this.elapsedMs >= t.start && this.elapsedMs < t.end) || this.sceneTimings[0];
            const prevIdx = Math.max(0, (cur ? cur.idx : 0) - 1);
            this.elapsedMs = this.sceneTimings[prevIdx].start + 10;
            this.lastSceneIdx = -1;
            this.renderFrame(this.elapsedMs);
        },

        nextScene() {
            const cur = this.sceneTimings.find(t => this.elapsedMs >= t.start && this.elapsedMs < t.end) || this.sceneTimings[0];
            const nextIdx = ((cur ? cur.idx : 0) + 1) % this.sceneTimings.length;
            this.elapsedMs = this.sceneTimings[nextIdx].start + 10;
            this.lastSceneIdx = -1;
            this.renderFrame(this.elapsedMs);
        },

        startLoop() {
            const tick = (ts) => {
                if (!this.visible) return;
                if (this.lastFrameTs === null) this.lastFrameTs = ts;
                const dt = Math.min(100, ts - this.lastFrameTs);
                this.lastFrameTs = ts;
                if (this.isPlaying) {
                    this.elapsedMs += dt;
                    if (this.elapsedMs >= this.totalMs) {
                        this.elapsedMs = 0;
                        this.lastSceneIdx = -1;
                    }
                    this.renderFrame(this.elapsedMs);
                }
                this.rafId = requestAnimationFrame(tick);
            };
            this.rafId = requestAnimationFrame(tick);
        },

        clamp01(v) { return Math.max(0, Math.min(1, v)); },
        springOut(t) {
            const x = this.clamp01(t);
            const c4 = (2 * Math.PI) / 3;
            return x === 0 ? 0 : x === 1 ? 1 : Math.pow(2, -10 * x) * Math.sin((x * 10 - 0.75) * c4) + 1;
        },
        easeOutCubic(t) {
            const x = this.clamp01(t);
            return 1 - Math.pow(1 - x, 3);
        },

        wrapLines(ctx, text, maxWidth) {
            const words = String(text || '').split(/\s+/);
            const lines = [];
            let line = '';
            for (const word of words) {
                const test = line ? line + ' ' + word : word;
                if (ctx.measureText(test).width > maxWidth && line) {
                    lines.push(line);
                    line = word;
                } else {
                    line = test;
                }
            }
            if (line) lines.push(line);
            return lines;
        },

        renderFrame(ms) {
            const canvas = this.$refs.motionCanvas;
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const w = canvas.width;
            const h = canvas.height;
            const isVertical = h > w;

            const current = this.sceneTimings.find(t => ms >= t.start && ms < t.end) || this.sceneTimings[this.sceneTimings.length - 1];
            if (!current) return;

            if (current.idx !== this.lastSceneIdx) {
                this.lastSceneIdx = current.idx;
                this.playChime(440 + current.idx * 55, 0.12);
            }

            const s = current.scene;
            const localMs = ms - current.start;
            const accent = s.accent || '#38bdf8';

            // Background
            const grad = ctx.createLinearGradient(0, 0, w, h);
            grad.addColorStop(0, '#0b1120');
            grad.addColorStop(1, '#1e1b4b');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);

            const drift = Math.sin(ms * 0.0012) * 35;
            const g1 = ctx.createRadialGradient(w * 0.25 + drift, h * 0.25, 20, w * 0.25 + drift, h * 0.25, w * 0.55);
            g1.addColorStop(0, accent + '38');
            g1.addColorStop(1, 'transparent');
            ctx.fillStyle = g1;
            ctx.fillRect(0, 0, w, h);

            const pad = isVertical ? 36 : 56;

            // Render by scene type
            if (s.type === 'fun_fact_intro' || s.type === 'intro') {
                const pop = this.springOut(localMs / 850);
                const countTarget = Number(s.stat_value || 0);
                const countNow = Math.round(countTarget * this.easeOutCubic((localMs - 550) / 1300));
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.scale(0.88 + 0.12 * pop, 0.88 + 0.12 * pop);
                ctx.globalAlpha = this.clamp01(localMs / 350);
                ctx.textAlign = 'center';

                ctx.fillStyle = '#fcd34d';
                ctx.font = `700 ${isVertical ? 16 : 18}px system-ui, sans-serif`;
                ctx.fillText(s.badge || 'WEEKLY FAMILY FUN FACTS', 0, isVertical ? -150 : -110);

                ctx.fillStyle = '#ffffff';
                ctx.font = `800 ${isVertical ? 38 : 44}px system-ui, sans-serif`;
                const titleLines = this.wrapLines(ctx, s.title || 'Our Family Week in Numbers', w - pad * 2);
                titleLines.forEach((ln, i) => ctx.fillText(ln, 0, (isVertical ? -90 : -55) + i * 48));

                if (countTarget > 0) {
                    ctx.fillStyle = '#38bdf8';
                    ctx.font = `800 ${isVertical ? 84 : 78}px system-ui, sans-serif`;
                    ctx.fillText(String(countNow), 0, isVertical ? 65 : 55);
                    ctx.fillStyle = '#e2e8f0';
                    ctx.font = `600 ${isVertical ? 22 : 20}px system-ui, sans-serif`;
                    ctx.fillText(s.caption || '', 0, isVertical ? 115 : 100);
                } else {
                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = `500 ${isVertical ? 22 : 22}px system-ui, sans-serif`;
                    ctx.fillText(s.caption || s.subtitle || '', 0, 50);
                }
                ctx.restore();
            } else if (s.type === 'fun_fact_leaderboard') {
                ctx.save();
                ctx.globalAlpha = this.easeOutCubic(localMs / 500);
                ctx.textAlign = 'left';
                ctx.fillStyle = '#7dd3fc';
                ctx.font = '700 16px system-ui, sans-serif';
                ctx.fillText(s.badge || 'FUN FACT #1', pad, pad + 18);

                ctx.fillStyle = '#ffffff';
                ctx.font = `800 ${isVertical ? 30 : 34}px system-ui, sans-serif`;
                const lines = this.wrapLines(ctx, s.title || '', w - pad * 2);
                lines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 64 + i * 38));

                ctx.fillStyle = '#cbd5e1';
                ctx.font = '400 18px system-ui, sans-serif';
                ctx.fillText(s.subtitle || '', pad, pad + 64 + lines.length * 38 + 8);

                const lb = Array.isArray(s.leaderboard) ? s.leaderboard : [];
                const maxPosts = Math.max(1, ...lb.map(b => Number(b.posts || 1)));
                const startY = isVertical ? 320 : 230;
                const colors = ['#f59e0b', '#38bdf8', '#a78bfa'];
                lb.forEach((b, idx) => {
                    const y = startY + idx * (isVertical ? 110 : 78);
                    const prog = this.springOut((localMs - 300 - idx * 180) / 850);
                    const barAreaW = isVertical ? (w - pad * 2) : (w - pad * 2 - 220);
                    const barX = isVertical ? pad : (pad + 200);
                    const barY = isVertical ? (y + 28) : y;
                    const targetW = Math.max(24, (Number(b.posts || 0) / maxPosts) * barAreaW * prog);

                    ctx.fillStyle = '#f8fafc';
                    ctx.font = '600 20px system-ui, sans-serif';
                    ctx.fillText(String(b.name || 'Member'), pad, y + (isVertical ? 12 : 24));

                    ctx.fillStyle = 'rgba(255,255,255,0.09)';
                    ctx.beginPath();
                    ctx.roundRect(barX, barY, barAreaW, 38, 10);
                    ctx.fill();

                    ctx.fillStyle = colors[idx % colors.length];
                    ctx.beginPath();
                    ctx.roundRect(barX, barY, targetW, 38, 10);
                    ctx.fill();

                    ctx.fillStyle = '#ffffff';
                    ctx.font = '700 16px system-ui, sans-serif';
                    ctx.fillText(`${b.posts || 0} posts`, barX + 14, barY + 25);
                });
                ctx.restore();
            } else if (s.type === 'fun_fact_golden_hour') {
                ctx.save();
                ctx.globalAlpha = this.easeOutCubic(localMs / 500);
                ctx.textAlign = 'left';
                ctx.fillStyle = '#fcd34d';
                ctx.font = '700 16px system-ui, sans-serif';
                ctx.fillText(s.badge || 'FUN FACT #2', pad, pad + 18);

                ctx.fillStyle = '#ffffff';
                ctx.font = `800 ${isVertical ? 28 : 34}px system-ui, sans-serif`;
                const lines = this.wrapLines(ctx, s.title || '', w - pad * 2);
                lines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 64 + i * 38));

                ctx.fillStyle = '#cbd5e1';
                ctx.font = '400 18px system-ui, sans-serif';
                ctx.fillText(s.subtitle || '', pad, pad + 64 + lines.length * 38 + 8);

                const daily = s.daily_counts || { Mon: 1, Tue: 1, Wed: 2, Thu: 2, Fri: 3, Sat: 2, Sun: 4 };
                const keys = Object.keys(daily);
                const maxV = Math.max(1, ...Object.values(daily).map(Number));
                const baseY = h - (isVertical ? 140 : 85);
                const maxH = isVertical ? 320 : 185;
                const colW = (w - pad * 2) / keys.length;
                const goldenShort = String(s.golden_day || 'Sunday').slice(0, 3);

                keys.forEach((day, idx) => {
                    const val = Number(daily[day] || 0);
                    const p = this.springOut((localMs - 250 - idx * 80) / 800);
                    const bh = Math.max(12, (val / maxV) * maxH * p);
                    const x = pad + idx * colW + 8;
                    const bw = colW - 16;
                    const isPeak = day === goldenShort;

                    ctx.fillStyle = isPeak ? '#f59e0b' : 'rgba(148, 163, 184, 0.35)';
                    ctx.beginPath();
                    ctx.roundRect(x, baseY - bh, bw, bh, 8);
                    ctx.fill();

                    if (isPeak && p > 0.6) {
                        ctx.fillStyle = '#fcd34d';
                        ctx.font = '700 14px system-ui, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText('★ PEAK', x + bw / 2, baseY - bh - 12);
                    }
                    ctx.fillStyle = isPeak ? '#ffffff' : '#94a3b8';
                    ctx.font = isPeak ? '700 16px system-ui, sans-serif' : '500 15px system-ui, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText(day, x + bw / 2, baseY + 28);
                });
                ctx.restore();
            } else if (s.type === 'fun_fact_most_loved') {
                const cardPop = this.springOut((localMs - 200) / 800);
                ctx.save();
                ctx.globalAlpha = this.easeOutCubic(localMs / 500);
                ctx.textAlign = 'left';
                ctx.fillStyle = '#fda4af';
                ctx.font = '700 16px system-ui, sans-serif';
                ctx.fillText(s.badge || 'FUN FACT #3', pad, pad + 18);

                ctx.fillStyle = '#ffffff';
                ctx.font = `800 ${isVertical ? 28 : 34}px system-ui, sans-serif`;
                const tLines = this.wrapLines(ctx, s.title || '', w - pad * 2);
                tLines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 64 + i * 38));

                ctx.save();
                ctx.translate(w / 2, h / 2 + 40);
                ctx.scale(0.92 + 0.08 * cardPop, 0.92 + 0.08 * cardPop);
                const cw = w - pad * 2;
                const ch = isVertical ? 260 : 185;
                ctx.fillStyle = 'rgba(255,255,255,0.08)';
                ctx.strokeStyle = 'rgba(255,255,255,0.2)';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.roundRect(-cw / 2, -ch / 2, cw, ch, 18);
                ctx.fill();
                ctx.stroke();

                ctx.fillStyle = '#fcd34d';
                ctx.font = '700 14px system-ui, sans-serif';
                ctx.fillText(`MOST CELEBRATED POST  •  ${String(s.quote_author || 'Family').toUpperCase()}`, -cw / 2 + 28, -ch / 2 + 38);

                ctx.fillStyle = '#ffffff';
                ctx.font = `600 ${isVertical ? 22 : 24}px system-ui, sans-serif`;
                const qLines = this.wrapLines(ctx, `“${s.quote_text || ''}”`, cw - 56);
                qLines.slice(0, 3).forEach((ln, i) => ctx.fillText(ln, -cw / 2 + 28, -ch / 2 + 82 + i * 32));

                ctx.fillStyle = '#fda4af';
                ctx.font = '700 16px system-ui, sans-serif';
                ctx.fillText(`❤️ ${s.quote_likes || 0} reactions   •   💬 ${s.quote_comments || 0} replies`, -cw / 2 + 28, ch / 2 - 28);
                ctx.restore();
                ctx.restore();
            } else if (s.type === 'photo' || s.type === 'sanctuary' || s.type === 'milestone') {
                const url = this.safeMediaUrl(s.image_url);
                const img = url ? this.imageCache[url] : null;
                const zoom = 1 + 0.08 * (localMs / current.dur);
                if (img && img.complete && img.naturalWidth > 0) {
                    const scale = Math.max(w / img.naturalWidth, h / img.naturalHeight) * zoom;
                    const dw = img.naturalWidth * scale;
                    const dh = img.naturalHeight * scale;
                    ctx.drawImage(img, (w - dw) / 2, (h - dh) / 2, dw, dh);
                }
                const shade = ctx.createLinearGradient(0, h * 0.45, 0, h);
                shade.addColorStop(0, 'rgba(0,0,0,0.1)');
                shade.addColorStop(1, 'rgba(0,0,0,0.82)');
                ctx.fillStyle = shade;
                ctx.fillRect(0, 0, w, h);

                ctx.save();
                ctx.globalAlpha = this.easeOutCubic(localMs / 500);
                ctx.textAlign = 'left';
                if (s.author) {
                    ctx.fillStyle = '#fcd34d';
                    ctx.font = '700 16px system-ui, sans-serif';
                    ctx.fillText(`SHARED BY ${String(s.author).toUpperCase()}`, pad, h - 115);
                }
                ctx.fillStyle = '#ffffff';
                ctx.font = `700 ${isVertical ? 24 : 26}px system-ui, sans-serif`;
                const cLines = this.wrapLines(ctx, s.caption || s.title || '', w - pad * 2);
                cLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, pad, h - 78 + i * 32));
                ctx.restore();
            } else {
                // Outro
                const pop = this.springOut(localMs / 800);
                ctx.save();
                ctx.translate(w / 2, h / 2);
                ctx.scale(0.9 + 0.1 * pop, 0.9 + 0.1 * pop);
                ctx.globalAlpha = this.clamp01(localMs / 400);
                ctx.textAlign = 'center';
                ctx.fillStyle = '#fcd34d';
                ctx.font = '700 16px system-ui, sans-serif';
                ctx.fillText(s.badge || 'READY FOR THE FAMILY FEED', 0, -68);
                ctx.fillStyle = '#ffffff';
                ctx.font = `800 ${isVertical ? 34 : 42}px system-ui, sans-serif`;
                ctx.fillText(s.title || 'One Family. Endless Love.', 0, -18);
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '400 19px system-ui, sans-serif';
                const sLines = this.wrapLines(ctx, s.subtitle || '', w - pad * 2);
                sLines.forEach((ln, i) => ctx.fillText(ln, 0, 28 + i * 26));
                ctx.restore();
            }

            // Bottom progress bar
            ctx.fillStyle = 'rgba(255,255,255,0.16)';
            ctx.fillRect(0, h - 6, w, 6);
            ctx.fillStyle = accent;
            ctx.fillRect(0, h - 6, (ms / this.totalMs) * w, 6);

            this.timecodeLabel = (ms / 1000).toFixed(1).padStart(4, '0') + 's';
        },

        async exportVideo(event) {
            const btn = event?.currentTarget || event?.target;
            const canvas = this.$refs.motionCanvas;
            if (!canvas || typeof canvas.captureStream !== 'function' || typeof MediaRecorder === 'undefined') {
                alert('Video export requires a modern browser supporting MediaRecorder.');
                return;
            }
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
            this.isExporting = true;
            try {
                const stream = canvas.captureStream(30);
                const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9') ? 'video/webm;codecs=vp9' : 'video/webm';
                const recorder = new MediaRecorder(stream, { mimeType });
                const chunks = [];
                recorder.ondataavailable = (e) => { if (e.data && e.data.size > 0) chunks.push(e.data); };
                recorder.onstop = () => {
                    const blob = new Blob(chunks, { type: mimeType });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `family-fun-facts-${this.aspectRatio.replace(':', 'x')}.webm`;
                    a.click();
                    URL.revokeObjectURL(url);
                    this.isExporting = false;
                    if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
                };
                this.elapsedMs = 0;
                this.lastSceneIdx = -1;
                this.isPlaying = true;
                recorder.start();
                setTimeout(() => {
                    if (recorder.state === 'recording') recorder.stop();
                }, Math.min(this.totalMs, 15000));
            } catch (err) {
                this.isExporting = false;
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            }
        },

        async regenerateReel(event) {
            const btn = event?.currentTarget || event?.target;
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="token"]')?.value || '';
            try {
                const res = await fetch('/api/weekly-reel/generate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ token: csrfToken })
                });
                const data = await res.json();
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
                if (data && data.success) {
                    window.location.reload();
                } else {
                    alert(data?.error || 'Could not refresh weekly fun facts.');
                }
            } catch (e) {
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            }
        },

        async approveReel(event) {
            const btn = event?.currentTarget || event?.target;
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="token"]')?.value || '';
            try {
                const response = await fetch('/api/weekly-reel/approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        reel_id: this.reelId,
                        aspect_ratio: this.aspectRatio,
                        token: csrfToken
                    })
                });
                const data = await response.json();
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);

                if (data.success) {
                    this.visible = false;
                    if (this.rafId) cancelAnimationFrame(this.rafId);
                    if (this.$refs.audioTrack) this.$refs.audioTrack.pause();
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Fun Facts Published!',
                            text: 'Your Weekly Family Fun Facts presentation has been shared to the feed and Reels tray.',
                            icon: 'success',
                            confirmButtonColor: '#16a34a'
                        }).then(() => window.location.reload());
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.error || 'Failed to approve weekly fun facts. Please try again.');
                }
            } catch (err) {
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
                alert('A network error occurred. Please try again.');
            }
        },

        async dismissReel() {
            if (!confirm('Would you like to dismiss this week’s fun-facts recap?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="token"]')?.value || '';
            try {
                await fetch('/api/weekly-reel/dismiss', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ reel_id: this.reelId, token: csrfToken })
                });
            } catch (e) {}
            this.visible = false;
            if (this.rafId) cancelAnimationFrame(this.rafId);
            if (this.$refs.audioTrack) this.$refs.audioTrack.pause();
        }
    };
}
</script>
@endif
