import { useEffect, useRef, useState } from 'preact/hooks';

const clamp01 = (v) => Math.max(0, Math.min(1, v));
const easeOutCubic = (t) => 1 - Math.pow(1 - clamp01(t), 3);
const springOut = (t) => {
    const x = clamp01(t);
    const c4 = (2 * Math.PI) / 3;
    return x === 0 ? 0 : x === 1 ? 1 : Math.pow(2, -10 * x) * Math.sin((x * 10 - 0.75) * c4) + 1;
};

function safeMediaUrl(url) {
    if (!url || typeof url !== 'string') return '';
    const trimmed = url.trim();
    if (trimmed.startsWith('/') || trimmed.startsWith('https://') || trimmed.startsWith('http://')) {
        return trimmed;
    }
    return '';
}

function wrapLines(ctx, text, maxW) {
    const words = String(text || '').split(/\s+/);
    const lines = [];
    let cur = '';
    for (const word of words) {
        const test = cur ? `${cur} ${word}` : word;
        if (ctx.measureText(test).width > maxW && cur) {
            lines.push(cur);
            cur = word;
        } else {
            cur = test;
        }
    }
    if (cur) lines.push(cur);
    return lines;
}

export function WeeklyFunFactsPlayer({ reel }) {
    const scenes = Array.isArray(reel?.scenes) ? reel.scenes : [];
    const [aspectRatio, setAspectRatio] = useState(reel?.aspect_ratio === '9:16' ? '9:16' : '16:9');
    const [isPlaying, setIsPlaying] = useState(true);
    const [sfxEnabled, setSfxEnabled] = useState(false);
    const [isExporting, setIsExporting] = useState(false);
    const [timecodeLabel, setTimecodeLabel] = useState('00.0s');

    const canvasRef = useRef(null);
    const audioRef = useRef(null);
    const audioCtxRef = useRef(null);
    const imageCacheRef = useRef({});
    const stateRef = useRef({
        elapsedMs: 0,
        totalMs: 30000,
        timings: [],
        lastSceneIdx: -1,
        lastFrameTs: null,
        isPlaying: true,
        sfxEnabled: false,
    });

    stateRef.current.isPlaying = isPlaying;
    stateRef.current.sfxEnabled = sfxEnabled;

    const playChime = (freq = 523.25, duration = 0.14) => {
        if (!stateRef.current.sfxEnabled) return;
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            if (!audioCtxRef.current) audioCtxRef.current = new Ctx();
            const ctx = audioCtxRef.current;
            if (ctx.state === 'suspended') ctx.resume();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, ctx.currentTime);
            gain.gain.setValueAtTime(0.06, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + duration);
        } catch {
            // AudioContext blocked or unavailable
        }
    };

    const renderFrame = (ms) => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const w = canvas.width;
        const h = canvas.height;
        const isVertical = h > w;
        const { timings, totalMs } = stateRef.current;
        if (!timings.length) return;

        const current = timings.find((t) => ms >= t.start && ms < t.end) || timings[timings.length - 1];
        if (!current) return;

        if (current.idx !== stateRef.current.lastSceneIdx) {
            stateRef.current.lastSceneIdx = current.idx;
            playChime(440 + current.idx * 55, 0.12);
        }

        const s = current.scene;
        const localMs = ms - current.start;
        const accent = s.accent || '#38bdf8';

        const grad = ctx.createLinearGradient(0, 0, w, h);
        grad.addColorStop(0, '#0b1120');
        grad.addColorStop(1, '#1e1b4b');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);

        const drift = Math.sin(ms * 0.0012) * 35;
        const g1 = ctx.createRadialGradient(w * 0.25 + drift, h * 0.25, 20, w * 0.25 + drift, h * 0.25, w * 0.55);
        g1.addColorStop(0, `${accent}38`);
        g1.addColorStop(1, 'transparent');
        ctx.fillStyle = g1;
        ctx.fillRect(0, 0, w, h);

        const pad = isVertical ? 44 : 56;

        if (s.type === 'fun_fact_intro' || s.type === 'intro') {
            const pop = springOut(localMs / 850);
            const countTarget = Number(s.stat_value || 0);
            const countNow = Math.round(countTarget * easeOutCubic((localMs - 550) / 1300));
            ctx.save();
            ctx.translate(w / 2, h / 2);
            ctx.scale(0.88 + 0.12 * pop, 0.88 + 0.12 * pop);
            ctx.globalAlpha = clamp01(localMs / 350);
            ctx.textAlign = 'center';

            ctx.fillStyle = '#fcd34d';
            ctx.font = `700 ${isVertical ? 26 : 28}px system-ui, sans-serif`;
            ctx.fillText(s.badge || 'WEEKLY FAMILY FUN FACTS', 0, isVertical ? -220 : -145);

            ctx.fillStyle = '#ffffff';
            ctx.font = `800 ${isVertical ? 58 : 60}px system-ui, sans-serif`;
            const titleLines = wrapLines(ctx, s.title || 'Our Family Week in Numbers', w - pad * 2);
            const titleLineH = isVertical ? 68 : 68;
            const titleStartY = isVertical ? -135 : -75;
            titleLines.forEach((ln, i) => ctx.fillText(ln, 0, titleStartY + i * titleLineH));

            if (countTarget > 0) {
                ctx.fillStyle = '#38bdf8';
                ctx.font = `800 ${isVertical ? 156 : 148}px system-ui, sans-serif`;
                ctx.fillText(String(countNow), 0, isVertical ? 110 : 85);
                ctx.fillStyle = '#e2e8f0';
                ctx.font = `600 ${isVertical ? 34 : 34}px system-ui, sans-serif`;
                const capLines = wrapLines(ctx, s.caption || '', w - pad * 2);
                capLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, 0, (isVertical ? 185 : 150) + i * 42));
            } else {
                ctx.fillStyle = '#cbd5e1';
                ctx.font = `500 ${isVertical ? 34 : 34}px system-ui, sans-serif`;
                const capLines = wrapLines(ctx, s.caption || s.subtitle || '', w - pad * 2);
                capLines.slice(0, 3).forEach((ln, i) => ctx.fillText(ln, 0, (isVertical ? 80 : 65) + i * 44));
            }
            ctx.restore();
        } else if (s.type === 'fun_fact_leaderboard') {
            ctx.save();
            ctx.globalAlpha = easeOutCubic(localMs / 500);
            ctx.textAlign = 'left';
            ctx.fillStyle = '#7dd3fc';
            ctx.font = `700 ${isVertical ? 26 : 26}px system-ui, sans-serif`;
            ctx.fillText(s.badge || 'FUN FACT #1', pad, pad + 26);

            ctx.fillStyle = '#ffffff';
            ctx.font = `800 ${isVertical ? 46 : 46}px system-ui, sans-serif`;
            const lines = wrapLines(ctx, s.title || '', w - pad * 2);
            const titleLineH = 56;
            lines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 88 + i * titleLineH));

            ctx.fillStyle = '#cbd5e1';
            ctx.font = `400 ${isVertical ? 28 : 28}px system-ui, sans-serif`;
            const subLines = wrapLines(ctx, s.subtitle || '', w - pad * 2);
            const subStartY = pad + 88 + lines.length * titleLineH + 8;
            subLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, pad, subStartY + i * 36));

            const lb = Array.isArray(s.leaderboard) ? s.leaderboard : [];
            const maxPosts = Math.max(1, ...lb.map((b) => Number(b.posts || 1)));
            const subEndY = subStartY + Math.min(2, subLines.length) * 36;
            const startY = isVertical ? Math.max(410, subEndY + 56) : Math.max(295, subEndY + 34);
            const rowStep = isVertical ? 210 : 120;
            const barH = isVertical ? 72 : 60;
            const colors = ['#f59e0b', '#38bdf8', '#a78bfa'];
            lb.forEach((b, idx) => {
                const y = startY + idx * rowStep;
                const prog = springOut((localMs - 300 - idx * 180) / 850);
                const barAreaW = isVertical ? w - pad * 2 : w - pad * 2 - 330;
                const barX = isVertical ? pad : pad + 310;
                const barY = isVertical ? y + 42 : y;
                const targetW = Math.max(36, (Number(b.posts || 0) / maxPosts) * barAreaW * prog);

                ctx.fillStyle = '#f8fafc';
                ctx.font = `600 ${isVertical ? 34 : 32}px system-ui, sans-serif`;
                ctx.fillText(String(b.name || 'Member'), pad, y + (isVertical ? 18 : 40));

                ctx.fillStyle = 'rgba(255,255,255,0.09)';
                ctx.beginPath();
                ctx.roundRect(barX, barY, barAreaW, barH, 14);
                ctx.fill();

                ctx.fillStyle = colors[idx % colors.length];
                ctx.beginPath();
                ctx.roundRect(barX, barY, targetW, barH, 14);
                ctx.fill();

                ctx.fillStyle = '#ffffff';
                ctx.font = `700 ${isVertical ? 28 : 26}px system-ui, sans-serif`;
                ctx.fillText(`${b.posts || 0} posts`, barX + 18, barY + (isVertical ? 46 : 39));
            });
            ctx.restore();
        } else if (s.type === 'fun_fact_golden_hour') {
            ctx.save();
            ctx.globalAlpha = easeOutCubic(localMs / 500);
            ctx.textAlign = 'left';
            ctx.fillStyle = '#fcd34d';
            ctx.font = `700 ${isVertical ? 26 : 26}px system-ui, sans-serif`;
            ctx.fillText(s.badge || 'FUN FACT #2', pad, pad + 26);

            ctx.fillStyle = '#ffffff';
            ctx.font = `800 ${isVertical ? 46 : 46}px system-ui, sans-serif`;
            const lines = wrapLines(ctx, s.title || '', w - pad * 2);
            const titleLineH = 56;
            lines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 88 + i * titleLineH));

            ctx.fillStyle = '#cbd5e1';
            ctx.font = `400 ${isVertical ? 28 : 28}px system-ui, sans-serif`;
            const subLines = wrapLines(ctx, s.subtitle || '', w - pad * 2);
            const subStartY = pad + 88 + lines.length * titleLineH + 8;
            subLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, pad, subStartY + i * 36));

            const daily = s.daily_counts || { Mon: 1, Tue: 1, Wed: 2, Thu: 2, Fri: 3, Sat: 2, Sun: 4 };
            const keys = Object.keys(daily);
            const maxV = Math.max(1, ...Object.values(daily).map(Number));
            const baseY = h - (isVertical ? 150 : 82);
            const maxH = isVertical ? 480 : 240;
            const colW = (w - pad * 2) / keys.length;
            const goldenShort = String(s.golden_day || 'Sunday').slice(0, 3);

            keys.forEach((day, idx) => {
                const val = Number(daily[day] || 0);
                const p = springOut((localMs - 250 - idx * 80) / 800);
                const bh = Math.max(16, (val / maxV) * maxH * p);
                const x = pad + idx * colW + 8;
                const bw = colW - 16;
                const isPeak = day === goldenShort;

                ctx.fillStyle = isPeak ? '#f59e0b' : 'rgba(148, 163, 184, 0.35)';
                ctx.beginPath();
                ctx.roundRect(x, baseY - bh, bw, bh, 10);
                ctx.fill();

                if (isPeak && p > 0.6) {
                    ctx.fillStyle = '#fcd34d';
                    ctx.font = `700 ${isVertical ? 22 : 22}px system-ui, sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.fillText('★ PEAK', x + bw / 2, baseY - bh - 16);
                }
                ctx.fillStyle = isPeak ? '#ffffff' : '#94a3b8';
                ctx.font = isPeak ? `700 ${isVertical ? 26 : 26}px system-ui, sans-serif` : `500 ${isVertical ? 24 : 24}px system-ui, sans-serif`;
                ctx.textAlign = 'center';
                ctx.fillText(day, x + bw / 2, baseY + 38);
            });
            ctx.restore();
        } else if (s.type === 'fun_fact_most_loved') {
            const cardPop = springOut((localMs - 200) / 800);
            ctx.save();
            ctx.globalAlpha = easeOutCubic(localMs / 500);
            ctx.textAlign = 'left';
            ctx.fillStyle = '#fda4af';
            ctx.font = `700 ${isVertical ? 26 : 26}px system-ui, sans-serif`;
            ctx.fillText(s.badge || 'FUN FACT #3', pad, pad + 26);

            ctx.fillStyle = '#ffffff';
            ctx.font = `800 ${isVertical ? 46 : 46}px system-ui, sans-serif`;
            const tLines = wrapLines(ctx, s.title || '', w - pad * 2);
            tLines.forEach((ln, i) => ctx.fillText(ln, pad, pad + 88 + i * 56));

            ctx.save();
            ctx.translate(w / 2, h / 2 + (isVertical ? 80 : 85));
            ctx.scale(0.92 + 0.08 * cardPop, 0.92 + 0.08 * cardPop);
            const cw = w - pad * 2;
            const ch = isVertical ? 460 : 290;
            ctx.fillStyle = 'rgba(255,255,255,0.08)';
            ctx.strokeStyle = 'rgba(255,255,255,0.22)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.roundRect(-cw / 2, -ch / 2, cw, ch, 22);
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#fcd34d';
            ctx.font = `700 ${isVertical ? 22 : 22}px system-ui, sans-serif`;
            ctx.fillText(`MOST CELEBRATED POST  •  ${String(s.quote_author || 'Family').toUpperCase()}`, -cw / 2 + 34, -ch / 2 + 50);

            ctx.fillStyle = '#ffffff';
            ctx.font = `600 ${isVertical ? 34 : 36}px system-ui, sans-serif`;
            const qLines = wrapLines(ctx, `“${s.quote_text || ''}”`, cw - 68);
            const maxQuoteLines = isVertical ? 5 : 3;
            qLines.slice(0, maxQuoteLines).forEach((ln, i) => ctx.fillText(ln, -cw / 2 + 34, -ch / 2 + 108 + i * 46));

            ctx.fillStyle = '#fda4af';
            ctx.font = `700 ${isVertical ? 26 : 26}px system-ui, sans-serif`;
            ctx.fillText(`❤️ ${s.quote_likes || 0} reactions   •   💬 ${s.quote_comments || 0} replies`, -cw / 2 + 34, ch / 2 - 36);
            ctx.restore();
            ctx.restore();
        } else if (s.type === 'photo' || s.type === 'sanctuary' || s.type === 'milestone') {
            const url = safeMediaUrl(s.image_url);
            const img = url ? imageCacheRef.current[url] : null;
            const zoom = 1 + 0.08 * (localMs / current.dur);
            if (img && img.complete && img.naturalWidth > 0) {
                const scale = Math.max(w / img.naturalWidth, h / img.naturalHeight) * zoom;
                const dw = img.naturalWidth * scale;
                const dh = img.naturalHeight * scale;
                ctx.drawImage(img, (w - dw) / 2, (h - dh) / 2, dw, dh);
            }
            const shade = ctx.createLinearGradient(0, h * 0.45, 0, h);
            shade.addColorStop(0, 'rgba(0,0,0,0.1)');
            shade.addColorStop(1, 'rgba(0,0,0,0.84)');
            ctx.fillStyle = shade;
            ctx.fillRect(0, 0, w, h);

            ctx.save();
            ctx.globalAlpha = easeOutCubic(localMs / 500);
            ctx.textAlign = 'left';
            if (s.author) {
                ctx.fillStyle = '#fcd34d';
                ctx.font = `700 ${isVertical ? 24 : 24}px system-ui, sans-serif`;
                ctx.fillText(`SHARED BY ${String(s.author).toUpperCase()}`, pad, h - (isVertical ? 175 : 150));
            }
            ctx.fillStyle = '#ffffff';
            ctx.font = `700 ${isVertical ? 38 : 38}px system-ui, sans-serif`;
            const cLines = wrapLines(ctx, s.caption || s.title || '', w - pad * 2);
            cLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, pad, h - (isVertical ? 120 : 100) + i * 48));
            ctx.restore();
        } else {
            const pop = springOut(localMs / 800);
            ctx.save();
            ctx.translate(w / 2, h / 2);
            ctx.scale(0.9 + 0.1 * pop, 0.9 + 0.1 * pop);
            ctx.globalAlpha = clamp01(localMs / 400);
            ctx.textAlign = 'center';
            ctx.fillStyle = '#fcd34d';
            ctx.font = `700 ${isVertical ? 26 : 26}px system-ui, sans-serif`;
            ctx.fillText(s.badge || 'FAMILY CIRCLE HIGHLIGHTS', 0, -110);
            ctx.fillStyle = '#ffffff';
            ctx.font = `800 ${isVertical ? 54 : 56}px system-ui, sans-serif`;
            const oLines = wrapLines(ctx, s.title || 'One Family. Endless Love.', w - pad * 2);
            oLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, 0, -35 + i * 64));
            ctx.fillStyle = '#cbd5e1';
            ctx.font = `400 ${isVertical ? 30 : 32}px system-ui, sans-serif`;
            const sLines = wrapLines(ctx, s.subtitle || '', w - pad * 2);
            sLines.slice(0, 2).forEach((ln, i) => ctx.fillText(ln, 0, (oLines.length > 1 ? 85 : 40) + i * 40));
            ctx.restore();
        }

        ctx.fillStyle = 'rgba(255,255,255,0.16)';
        ctx.fillRect(0, h - 6, w, 6);
        ctx.fillStyle = accent;
        ctx.fillRect(0, h - 6, (ms / totalMs) * w, 6);
        setTimecodeLabel(`${(ms / 1000).toFixed(1).padStart(4, '0')}s`);
    };

    useEffect(() => {
        if (!scenes.length) return undefined;
        let acc = 0;
        stateRef.current.timings = scenes.map((s, idx) => {
            const dur = Math.max(2000, Number(s.duration || 5) * 1000);
            const item = { idx, scene: s, start: acc, end: acc + dur, dur };
            acc += dur;
            return item;
        });
        stateRef.current.totalMs = Math.max(5000, acc);

        scenes.forEach((s) => {
            const url = safeMediaUrl(s.image_url);
            if (url && !imageCacheRef.current[url]) {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.src = url;
                imageCacheRef.current[url] = img;
            }
        });

        let rafId = null;
        const step = (ts) => {
            if (!stateRef.current.lastFrameTs) stateRef.current.lastFrameTs = ts;
            const delta = Math.min(100, ts - stateRef.current.lastFrameTs);
            stateRef.current.lastFrameTs = ts;
            if (stateRef.current.isPlaying) {
                stateRef.current.elapsedMs = (stateRef.current.elapsedMs + delta) % stateRef.current.totalMs;
            }
            renderFrame(stateRef.current.elapsedMs);
            rafId = requestAnimationFrame(step);
        };
        rafId = requestAnimationFrame(step);

        return () => {
            if (rafId) cancelAnimationFrame(rafId);
        };
    }, [scenes.length]);

    const handleTogglePlay = () => {
        const next = !isPlaying;
        setIsPlaying(next);
        if (audioRef.current && reel?.audio_url) {
            if (next) audioRef.current.play().catch(() => {});
            else audioRef.current.pause();
        }
    };

    const handlePrevScene = () => {
        const { timings, elapsedMs } = stateRef.current;
        if (!timings.length) return;
        const idx = timings.findIndex((t) => elapsedMs >= t.start && elapsedMs < t.end);
        const prev = idx > 0 ? idx - 1 : timings.length - 1;
        stateRef.current.elapsedMs = timings[prev].start + 10;
        renderFrame(stateRef.current.elapsedMs);
    };

    const handleNextScene = () => {
        const { timings, elapsedMs } = stateRef.current;
        if (!timings.length) return;
        const idx = timings.findIndex((t) => elapsedMs >= t.start && elapsedMs < t.end);
        const next = (idx + 1) % timings.length;
        stateRef.current.elapsedMs = timings[next].start + 10;
        renderFrame(stateRef.current.elapsedMs);
    };

    const handleExportVideo = () => {
        const canvas = canvasRef.current;
        if (!canvas || typeof canvas.captureStream !== 'function' || typeof MediaRecorder === 'undefined') {
            alert('Video export requires a modern browser supporting MediaRecorder.');
            return;
        }
        setIsExporting(true);
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
                a.download = `family-fun-facts-${aspectRatio.replace(':', 'x')}.webm`;
                a.click();
                URL.revokeObjectURL(url);
                setIsExporting(false);
            };
            stateRef.current.elapsedMs = 0;
            stateRef.current.lastSceneIdx = -1;
            setIsPlaying(true);
            recorder.start();
            setTimeout(() => {
                if (recorder.state === 'recording') recorder.stop();
            }, Math.min(stateRef.current.totalMs, 15000));
        } catch {
            setIsExporting(false);
        }
    };

    if (!scenes.length) return null;

    const canvasW = aspectRatio === '9:16' ? 720 : 1280;
    const canvasH = aspectRatio === '9:16' ? 1280 : 720;

    return (
        <div
            className="mb-3 rounded-4 overflow-hidden border"
            style={{ background: '#0f172a', borderColor: 'rgba(148, 163, 184, 0.2)' }}
            data-testid="weekly-fun-facts-feed-player"
        >
            <div className="px-3 py-2 d-flex align-items-center justify-content-between flex-wrap gap-2" style={{ background: 'rgba(15, 23, 42, 0.92)' }}>
                <div className="d-flex align-items-center gap-2 flex-wrap">
                    <span
                        className="badge rounded-pill px-3 py-2 text-white fw-bold"
                        style={{ background: 'linear-gradient(135deg, #b8202c 0%, #8b131d 100%)', fontSize: '0.72rem', letterSpacing: '0.04em', whiteSpace: 'nowrap', flexShrink: 0 }}
                    >
                        <i className="bi bi-stars me-1" aria-hidden="true" />
                        WEEKLY FUN FACTS ({Number(reel.duration_seconds || 30)}s)
                    </span>
                    <span className="small text-white-50">60fps Motion Presentation</span>
                </div>
                <div className="btn-group" role="group" aria-label="Presentation aspect ratio">
                    <button
                        type="button"
                        onClick={() => setAspectRatio('16:9')}
                        className={`btn btn-sm ${aspectRatio === '16:9' ? 'btn-danger text-white fw-bold' : 'btn-outline-light'}`}
                        style={{ minHeight: '44px', minWidth: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                    >
                        16:9
                    </button>
                    <button
                        type="button"
                        onClick={() => setAspectRatio('9:16')}
                        className={`btn btn-sm ${aspectRatio === '9:16' ? 'btn-danger text-white fw-bold' : 'btn-outline-light'}`}
                        style={{ minHeight: '44px', minWidth: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                    >
                        9:16
                    </button>
                </div>
            </div>

            <div className="d-flex justify-content-center align-items-center p-2 p-md-3" style={{ background: '#020617' }}>
                <div
                    className="w-100 rounded-3 overflow-hidden"
                    style={{
                        maxWidth: aspectRatio === '9:16' ? '380px' : '100%',
                        aspectRatio: aspectRatio === '9:16' ? '9 / 16' : '16 / 9',
                        background: '#0b1120',
                    }}
                >
                    <canvas ref={canvasRef} width={canvasW} height={canvasH} className="w-100 h-100 d-block" />
                    {reel.audio_url && (
                        <audio ref={audioRef} preload="none" loop>
                            <source src={reel.audio_url} type="audio/wav" />
                            <source src={reel.audio_url} type="audio/mpeg" />
                        </audio>
                    )}
                </div>
            </div>

            <div className="px-3 py-2 d-flex align-items-center justify-content-between flex-wrap gap-2 text-white" style={{ background: '#0f172a' }}>
                <div className="d-flex align-items-center gap-2">
                    <button
                        type="button"
                        onClick={handlePrevScene}
                        className="btn btn-sm btn-outline-light rounded-circle d-inline-flex align-items-center justify-content-center"
                        style={{ minWidth: '44px', minHeight: '44px', flexShrink: 0 }}
                        aria-label="Previous scene"
                    >
                        <i className="bi bi-chevron-left" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        onClick={handleTogglePlay}
                        className="btn btn-sm btn-light text-dark rounded-circle fw-bold d-inline-flex align-items-center justify-content-center"
                        style={{ minWidth: '44px', minHeight: '44px', flexShrink: 0 }}
                        aria-label="Play or pause presentation"
                    >
                        <i className={isPlaying ? 'bi bi-pause-fill' : 'bi bi-play-fill'} aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        onClick={handleNextScene}
                        className="btn btn-sm btn-outline-light rounded-circle d-inline-flex align-items-center justify-content-center"
                        style={{ minWidth: '44px', minHeight: '44px', flexShrink: 0 }}
                        aria-label="Next scene"
                    >
                        <i className="bi bi-chevron-right" aria-hidden="true" />
                    </button>
                    <span className="small font-monospace text-white-50 ms-1">{timecodeLabel}</span>
                </div>

                <div className="d-flex align-items-center gap-2 flex-wrap">
                    <button
                        type="button"
                        onClick={() => setSfxEnabled((v) => !v)}
                        className="btn btn-sm btn-outline-light rounded-pill px-3 d-inline-flex align-items-center gap-1"
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        aria-label="Toggle motion sound effects"
                    >
                        <i className={sfxEnabled ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill'} aria-hidden="true" />
                        <span>{sfxEnabled ? 'SFX On' : 'SFX Off'}</span>
                    </button>
                    <button
                        type="button"
                        onClick={handleExportVideo}
                        disabled={isExporting}
                        className="btn btn-sm btn-outline-info rounded-pill px-3 d-inline-flex align-items-center gap-1"
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                    >
                        <i className="bi bi-download" aria-hidden="true" />
                        <span>{isExporting ? 'Rendering…' : 'Save Video'}</span>
                    </button>
                </div>
            </div>
        </div>
    );
}
