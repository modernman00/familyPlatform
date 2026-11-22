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

export function playArcadeSfx(type, index = 0) {
    if (typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        // Still allow gentle audio unless muted, or keep minimal
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
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, now);
        gain.gain.setValueAtTime(0.14, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
        osc.start(now);
        osc.stop(now + 0.3);
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
