import {
    createSignal,
    signal,
    computed,
    effect,
    batch,
    useSignal,
    useComputed,
    useSignalValue,
} from '../shared/signals';

export {
    createSignal,
    signal,
    computed,
    effect,
    batch,
    useSignal,
    useComputed,
    useSignalValue,
};

export const activePostId = createSignal(null);
export const isGlobalMuted = createSignal(true);
export const isDocumentVisible = createSignal(
    typeof document !== 'undefined' ? document.visibilityState !== 'hidden' : true
);

const DEFAULT_CAPTION_STYLE = { size: 'medium', background: 'dark', position: 'bottom' };

function readStoredCaptionsEnabled() {
    if (typeof window === 'undefined') return false;
    try {
        return window.localStorage.getItem('fp_cc_enabled') === '1';
    } catch {
        return false;
    }
}

function readStoredCaptionStyle() {
    if (typeof window === 'undefined') return { ...DEFAULT_CAPTION_STYLE };
    try {
        const raw = window.localStorage.getItem('fp_cc_style');
        if (!raw) return { ...DEFAULT_CAPTION_STYLE };
        const parsed = JSON.parse(raw);
        return {
            size: ['small', 'medium', 'large'].includes(parsed?.size) ? parsed.size : 'medium',
            background: ['dark', 'translucent', 'none'].includes(parsed?.background) ? parsed.background : 'dark',
            position: ['bottom', 'top'].includes(parsed?.position) ? parsed.position : 'bottom',
        };
    } catch {
        return { ...DEFAULT_CAPTION_STYLE };
    }
}

export const isCaptionsEnabled = createSignal(readStoredCaptionsEnabled());
export const captionStyle = createSignal(readStoredCaptionStyle());
const subtitleCueCache = new Map();

export function setCaptionsEnabled(enabled) {
    const next = Boolean(enabled);
    isCaptionsEnabled.value = next;
    if (typeof window !== 'undefined') {
        try {
            window.localStorage.setItem('fp_cc_enabled', next ? '1' : '0');
        } catch {
            // Ignore storage quota/privacy errors
        }
    }
}

export function updateCaptionStyle(patch = {}) {
    const cur = captionStyle.peek();
    const next = {
        size: ['small', 'medium', 'large'].includes(patch.size) ? patch.size : cur.size,
        background: ['dark', 'translucent', 'none'].includes(patch.background) ? patch.background : cur.background,
        position: ['bottom', 'top'].includes(patch.position) ? patch.position : cur.position,
    };
    captionStyle.value = next;
    if (typeof window !== 'undefined') {
        try {
            window.localStorage.setItem('fp_cc_style', JSON.stringify(next));
        } catch {
            // Ignore storage errors
        }
    }
}

export function getCachedSubtitles(src) {
    return src ? subtitleCueCache.get(String(src)) || null : null;
}

export function setCachedSubtitles(src, data) {
    if (src && data) {
        subtitleCueCache.set(String(src), data);
    }
}

const DEBOUNCE_MS = 250;
const INTERSECTION_THRESHOLDS = [0, 0.6, 0.85];

const registry = new Map(); // id -> { id, element, ratio, onActivate, onDeactivate }
let sharedObserver = null;
let debounceTimer = null;
let visibilityBound = false;

function handleVisibilityChange() {
    if (typeof document === 'undefined') return;
    const visible = document.visibilityState !== 'hidden';
    isDocumentVisible.value = visible;
    if (!visible) {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
        deactivateAllExcept(null);
        activePostId.value = null;
    } else {
        scheduleEvaluate();
    }
}

function ensureVisibilityListener() {
    if (visibilityBound || typeof document === 'undefined') return;
    document.addEventListener('visibilitychange', handleVisibilityChange);
    visibilityBound = true;
}

function deactivateAllExcept(keepId) {
    registry.forEach((entry, id) => {
        if (id !== keepId && typeof entry.onDeactivate === 'function') {
            try {
                entry.onDeactivate();
            } catch {
                // Ignore cleanup/pause errors
            }
        }
    });
}

export function evaluateActivePlayer() {
    if (typeof document !== 'undefined' && document.visibilityState === 'hidden') {
        deactivateAllExcept(null);
        activePostId.value = null;
        return;
    }

    const viewportH = typeof window !== 'undefined'
        ? (window.innerHeight || document.documentElement?.clientHeight || 900)
        : 900;
    const viewportCenter = viewportH / 2;

    let bestId = null;
    let bestTier = -1; // 2 for >= 0.85, 1 for >= 0.6
    let bestDistance = Infinity;

    registry.forEach((entry, id) => {
        if (entry.ratio < 0.6) return;
        const tier = entry.ratio >= 0.85 ? 2 : 1;
        let dist = 0;
        if (entry.element && typeof entry.element.getBoundingClientRect === 'function') {
            const rect = entry.element.getBoundingClientRect();
            const center = rect.top + rect.height / 2;
            dist = Math.abs(center - viewportCenter);
        }

        if (tier > bestTier || (tier === bestTier && dist < bestDistance)) {
            bestTier = tier;
            bestDistance = dist;
            bestId = id;
        }
    });

    if (bestId !== activePostId.peek()) {
        deactivateAllExcept(bestId);
        activePostId.value = bestId;
        if (bestId !== null) {
            const winner = registry.get(bestId);
            if (winner && typeof winner.onActivate === 'function') {
                winner.onActivate();
            }
        }
    }
}

function scheduleEvaluate() {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(() => {
        debounceTimer = null;
        evaluateActivePlayer();
    }, DEBOUNCE_MS);
}

function ensureObserver() {
    if (sharedObserver || typeof IntersectionObserver === 'undefined') return sharedObserver;
    sharedObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                for (const reg of registry.values()) {
                    if (reg.element === entry.target) {
                        reg.ratio = entry.isIntersecting ? entry.intersectionRatio : 0;
                    }
                }
            });
            scheduleEvaluate();
        },
        { threshold: INTERSECTION_THRESHOLDS }
    );
    return sharedObserver;
}

/**
 * Register a feed media player (native_video or inline_iframe) for single-active coordination.
 */
export function registerMediaPlayer(id, element, callbacks = {}) {
    const key = String(id);
    ensureVisibilityListener();
    const obs = ensureObserver();

    registry.set(key, {
        id: key,
        element,
        ratio: 0,
        onActivate: callbacks.onActivate || null,
        onDeactivate: callbacks.onDeactivate || null,
    });

    if (obs && element) {
        obs.observe(element);
    }

    return () => {
        const existing = registry.get(key);
        if (existing && obs && existing.element) {
            obs.unobserve(existing.element);
        }
        registry.delete(key);
        if (activePostId.peek() === key) {
            activePostId.value = null;
            scheduleEvaluate();
        }
    };
}

/**
 * Manually promote a player (e.g. user clicked Play on a facade or native video).
 */
export function requestActivePlayer(id) {
    const key = String(id);
    if (debounceTimer) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
    }
    const alreadyActive = activePostId.peek() === key;
    deactivateAllExcept(key);
    activePostId.value = key;
    if (!alreadyActive) {
        const entry = registry.get(key);
        if (entry && typeof entry.onActivate === 'function') {
            entry.onActivate();
        }
    }
}

export function setGlobalMuted(muted) {
    isGlobalMuted.value = Boolean(muted);
}

export function toggleGlobalMute() {
    isGlobalMuted.value = !isGlobalMuted.peek();
    return isGlobalMuted.peek();
}

export function resetMediaPlaybackStore() {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
        debounceTimer = null;
    }
    if (sharedObserver) {
        sharedObserver.disconnect();
        sharedObserver = null;
    }
    if (visibilityBound && typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', handleVisibilityChange);
        visibilityBound = false;
    }
    registry.clear();
    activePostId.value = null;
    isGlobalMuted.value = true;
    isDocumentVisible.value = typeof document !== 'undefined' ? document.visibilityState !== 'hidden' : true;
}
