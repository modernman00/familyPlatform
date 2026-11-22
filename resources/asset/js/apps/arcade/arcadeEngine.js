/**
 * Family Arcade Shared Engine:
 * - Deterministic Web Audio harmonic SFX (zero robotic speechSynthesis)
 * - AI Bot Ghost Pace & Replay interpolation
 * - KinZip DFS Hint Solver
 */

let sharedAudioCtx = null;

function getAudioContext() {
    if (typeof window === 'undefined') return null;
    const Ctx = window.AudioContext || window.webkitAudioContext;
    if (!Ctx) return null;
    if (!sharedAudioCtx) {
        try {
            sharedAudioCtx = new Ctx();
        } catch {
            return null;
        }
    }
    if (sharedAudioCtx && sharedAudioCtx.state === 'suspended') {
        sharedAudioCtx.resume().catch(() => {});
    }
    return sharedAudioCtx;
}

const PENTATONIC_FREQS = [261.63, 293.66, 329.63, 392.0, 440.0, 523.25, 587.33, 659.25, 783.99];

export function triggerArcadeHaptics(pattern = 15) {
    if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
        try {
            navigator.vibrate(pattern);
        } catch {
            // Non-blocking fallback
        }
    }
}

export function triggerArcadeConfetti() {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let canvas = document.getElementById('arcadeConfettiCanvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'arcadeConfettiCanvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '99999';
        document.body.appendChild(canvas);
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const colours = ['#fbbf24', '#f59e0b', '#ef4444', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'];
    const particles = [];
    const particleCount = 75;

    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: canvas.width * 0.5 + (Math.random() - 0.5) * 200,
            y: canvas.height * 0.35 + (Math.random() - 0.5) * 80,
            vx: (Math.random() - 0.5) * 16,
            vy: (Math.random() - 0.75) * 18,
            size: Math.random() * 8 + 6,
            colour: colours[Math.floor(Math.random() * colours.length)],
            rotation: Math.random() * 360,
            vRot: (Math.random() - 0.5) * 12,
            opacity: 1,
            gravity: 0.42,
        });
    }

    let frame = 0;
    const animate = () => {
        frame++;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        let alive = 0;

        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            if (p.opacity <= 0.01) continue;

            p.x += p.vx;
            p.y += p.vy;
            p.vy += p.gravity;
            p.vx *= 0.98;
            p.rotation += p.vRot;
            p.opacity = Math.max(0, p.opacity - 0.016);
            alive++;

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate((p.rotation * Math.PI) / 180);
            ctx.fillStyle = p.colour;
            ctx.globalAlpha = p.opacity;
            ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
            ctx.restore();
        }

        if (alive > 0 && frame < 90) {
            requestAnimationFrame(animate);
        } else {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (canvas.parentNode) {
                canvas.parentNode.removeChild(canvas);
            }
        }
    };

    requestAnimationFrame(animate);
}

export function playArcadeSfx(type, index = 0) {
    if (typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        // Still allow gentle audio unless muted, or keep minimal
    }

    // Trigger subtle tactile haptics matched to audio cue
    if (type === 'tick') {
        triggerArcadeHaptics(10);
    } else if (type === 'pulse') {
        triggerArcadeHaptics(22);
    } else if (type === 'connect') {
        triggerArcadeHaptics(28);
    } else if (type === 'error') {
        triggerArcadeHaptics([40, 50, 40]);
    } else if (type === 'complete') {
        triggerArcadeHaptics([30, 40, 50, 50, 120]);
        triggerArcadeConfetti();
    }

    const ctx = getAudioContext();
    if (!ctx) return;

    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    if (type === 'pulse') {
        const freq = PENTATONIC_FREQS[Math.abs(index) % PENTATONIC_FREQS.length];
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now);
        osc.frequency.exponentialRampToValueAtTime(freq * 1.018, now + 0.035);
        osc.frequency.exponentialRampToValueAtTime(freq, now + 0.12);
        gain.gain.setValueAtTime(0.18, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.32);
        osc.start(now);
        osc.stop(now + 0.34);
        return;
    }

    if (type === 'connect') {
        osc.type = 'triangle';
        const freq = 340 + Math.min(440, index * 14);
        osc.frequency.setValueAtTime(freq, now);
        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.09);
        osc.start(now);
        osc.stop(now + 0.1);
        return;
    }

    if (type === 'error') {
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(165, now);
        osc.frequency.linearRampToValueAtTime(120, now + 0.18);
        gain.gain.setValueAtTime(0.09, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
        osc.start(now);
        osc.stop(now + 0.21);
        return;
    }

    if (type === 'complete') {
        const chord = [523.25, 659.25, 783.99, 1046.5];
        chord.forEach((freq, idx) => {
            const cOsc = ctx.createOscillator();
            const cGain = ctx.createGain();
            cOsc.type = 'triangle';
            cOsc.frequency.setValueAtTime(freq, now + idx * 0.07);
            cGain.gain.setValueAtTime(0.11, now + idx * 0.07);
            cGain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.07 + 0.45);
            cOsc.connect(cGain);
            cGain.connect(ctx.destination);
            cOsc.start(now + idx * 0.07);
            cOsc.stop(now + idx * 0.07 + 0.46);
        });
        return;
    }

    // Default subtle click/tick
    osc.type = 'sine';
    osc.frequency.setValueAtTime(520, now);
    gain.gain.setValueAtTime(0.05, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.05);
    osc.start(now);
    osc.stop(now + 0.06);
}

export function formatElapsedMs(ms) {
    const totalSec = Math.max(0, Math.floor(ms / 1000));
    const mins = Math.floor(totalSec / 60);
    const secs = totalSec % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

export function kinZipEdgeKey(r1, c1, r2, c2) {
    if (r1 > r2 || (r1 === r2 && c1 > c2)) {
        return `${r2},${c2}-${r1},${c1}`;
    }
    return `${r1},${c1}-${r2},${c2}`;
}

/**
 * Compute ghost progress percentage (0..100) at a given elapsedMs.
 */
export function computeGhostProgress(ghost, parMs, elapsedMs) {
    const targetMs = ghost && ghost.elapsedMs > 0 ? ghost.elapsedMs : (parMs || 60000);
    const ratio = Math.min(1, Math.max(0, elapsedMs / targetMs));
    return Math.round(ratio * 100);
}

/**
 * Deterministic DFS hint solver for KinZip: finds a valid next cell from current path.
 */
export function findKinZipHintStep(puzzle, currentPath) {
    if (!puzzle || !puzzle.size || !Array.isArray(puzzle.checkpoints)) return null;
    const size = puzzle.size;
    const total = size * size;
    const startCp = puzzle.checkpoints.find((cp) => cp.num === 1);
    if (!startCp) return null;

    if (!currentPath || currentPath.length === 0) {
        return [startCp.r, startCp.c];
    }

    const wallSet = new Set(
        (puzzle.walls || []).map((w) => kinZipEdgeKey(w[0], w[1], w[2], w[3]))
    );
    const cpMap = new Map();
    puzzle.checkpoints.forEach((cp) => {
        cpMap.set(`${cp.r},${cp.c}`, cp.num);
    });

    const visited = new Set(currentPath.map(([r, c]) => `${r},${c}`));
    let nextExpectedCp = 1;
    for (const [r, c] of currentPath) {
        const num = cpMap.get(`${r},${c}`);
        if (num === nextExpectedCp) {
            nextExpectedCp += 1;
        } else if (num && num > nextExpectedCp) {
            return null; // Current path already skipped a checkpoint
        }
    }

    const last = currentPath[currentPath.length - 1];
    const dirs = [
        [-1, 0],
        [1, 0],
        [0, -1],
        [0, 1],
    ];

    // Bounded DFS to find a valid continuation
    let steps = 0;
    const maxSteps = 12000;
    const path = currentPath.map(([r, c]) => [r, c]);

    function dfs(r, c, expCp) {
        steps += 1;
        if (steps > maxSteps) return false;
        if (path.length === total) {
            return expCp > puzzle.checkpoints.length;
        }
        for (const [dr, dc] of dirs) {
            const nr = r + dr;
            const nc = c + dc;
            if (nr < 0 || nr >= size || nc < 0 || nc >= size) continue;
            const key = `${nr},${nc}`;
            if (visited.has(key)) continue;
            if (wallSet.has(kinZipEdgeKey(r, c, nr, nc))) continue;
            const cpNum = cpMap.get(key);
            if (cpNum !== undefined && cpNum !== expCp) continue;

            visited.add(key);
            path.push([nr, nc]);
            if (dfs(nr, nc, cpNum === expCp ? expCp + 1 : expCp)) {
                return true;
            }
            path.pop();
            visited.delete(key);
        }
        return false;
    }

    if (dfs(last[0], last[1], nextExpectedCp) && path.length > currentPath.length) {
        return path[currentPath.length];
    }

    // Fallback: any valid adjacent unvisited cell
    for (const [dr, dc] of dirs) {
        const nr = last[0] + dr;
        const nc = last[1] + dc;
        if (nr < 0 || nr >= size || nc < 0 || nc >= size) continue;
        const key = `${nr},${nc}`;
        if (visited.has(key)) continue;
        if (wallSet.has(kinZipEdgeKey(last[0], last[1], nr, nc))) continue;
        const cpNum = cpMap.get(key);
        if (cpNum !== undefined && cpNum !== nextExpectedCp) continue;
        return [nr, nc];
    }

    return null;
}
