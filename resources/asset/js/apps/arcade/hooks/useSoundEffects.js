import { useCallback, useEffect, useState } from 'preact/hooks';

const SOUND_STORAGE_KEY = 'fp_arcade_sound_enabled';
let sharedAudioCtx = null;

function getAudioContext() {
  if (typeof window === 'undefined') return null;
  const Ctor = window.AudioContext || window.webkitAudioContext;
  if (!Ctor) return null;
  if (!sharedAudioCtx) {
    try {
      sharedAudioCtx = new Ctor();
    } catch (_) {
      return null;
    }
  }
  if (sharedAudioCtx.state === 'suspended' && typeof sharedAudioCtx.resume === 'function') {
    sharedAudioCtx.resume().catch(() => {});
  }
  return sharedAudioCtx;
}

/**
 * Trigger subtle mobile haptic vibration when supported.
 * @param {number} [ms=12]
 */
export function triggerHaptic(ms = 12) {
  try {
    if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
      navigator.vibrate(ms);
    }
  } catch (_) {
    // Ignore unsupported or blocked vibration calls
  }
}

function readInitialSoundState() {
  try {
    if (typeof localStorage !== 'undefined') {
      const val = localStorage.getItem(SOUND_STORAGE_KEY);
      if (val !== null) return val !== 'false';
    }
  } catch (_) {
    // Ignore storage restrictions
  }
  return true;
}

/**
 * Zero-dependency procedural Web Audio sound engine for the Preact puzzle suite.
 */
export function useSoundEffects() {
  const [soundEnabled, setSoundEnabled] = useState(readInitialSoundState);

  useEffect(() => {
    try {
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(SOUND_STORAGE_KEY, String(soundEnabled));
      }
    } catch (_) {}
  }, [soundEnabled]);

  // Lazily unlock AudioContext on first pointer/touch interaction
  useEffect(() => {
    if (typeof window === 'undefined') return undefined;
    const unlock = () => { getAudioContext(); };
    window.addEventListener('pointerdown', unlock, { once: true, passive: true });
    return () => window.removeEventListener('pointerdown', unlock);
  }, []);

  const toggleSound = useCallback(() => {
    triggerHaptic(10);
    setSoundEnabled((prev) => {
      const next = !prev;
      if (next) getAudioContext();
      return next;
    });
  }, []);

  const playTap = useCallback(() => {
    if (!soundEnabled) return;
    const ctx = getAudioContext();
    if (!ctx) return;
    try {
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(320, now);
      osc.frequency.exponentialRampToValueAtTime(120, now + 0.045);
      gain.gain.setValueAtTime(0.08, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.045);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.onended = () => { osc.disconnect(); gain.disconnect(); };
      osc.start(now);
      osc.stop(now + 0.05);
    } catch (_) {}
  }, [soundEnabled]);

  const playConnect = useCallback((stepIndex = 1) => {
    if (!soundEnabled) return;
    const ctx = getAudioContext();
    if (!ctx) return;
    try {
      const now = ctx.currentTime;
      const clampedStep = Math.max(0, Math.min(24, Number(stepIndex) || 0));
      const baseFreq = 392 * Math.pow(1.045, clampedStep);
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(baseFreq, now);
      osc.frequency.exponentialRampToValueAtTime(baseFreq * 1.12, now + 0.09);
      gain.gain.setValueAtTime(0.09, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.14);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.onended = () => { osc.disconnect(); gain.disconnect(); };
      osc.start(now);
      osc.stop(now + 0.15);
    } catch (_) {}
  }, [soundEnabled]);

  const playError = useCallback(() => {
    if (!soundEnabled) return;
    const ctx = getAudioContext();
    if (!ctx) return;
    try {
      const now = ctx.currentTime;
      const osc = ctx.createOscillator();
      const filter = ctx.createBiquadFilter();
      const gain = ctx.createGain();
      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(140, now);
      osc.frequency.exponentialRampToValueAtTime(85, now + 0.11);
      filter.type = 'lowpass';
      filter.frequency.setValueAtTime(180, now);
      gain.gain.setValueAtTime(0.11, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.11);
      osc.connect(filter);
      filter.connect(gain);
      gain.connect(ctx.destination);
      osc.onended = () => { osc.disconnect(); filter.disconnect(); gain.disconnect(); };
      osc.start(now);
      osc.stop(now + 0.12);
    } catch (_) {}
  }, [soundEnabled]);

  const playRowComplete = useCallback(() => {
    if (!soundEnabled) return;
    const ctx = getAudioContext();
    if (!ctx) return;
    try {
      const now = ctx.currentTime;
      [523.25, 783.99].forEach((freq, idx) => {
        const start = now + idx * 0.07;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, start);
        gain.gain.setValueAtTime(0.09, start);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.22);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.onended = () => { osc.disconnect(); gain.disconnect(); };
        osc.start(start);
        osc.stop(start + 0.23);
      });
    } catch (_) {}
  }, [soundEnabled]);

  const playVictory = useCallback(() => {
    if (!soundEnabled) return;
    const ctx = getAudioContext();
    if (!ctx) return;
    try {
      const now = ctx.currentTime;
      [523.25, 659.25, 783.99, 987.77].forEach((freq, idx) => {
        const start = now + idx * 0.085;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, start);
        gain.gain.setValueAtTime(0.10, start);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.34);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.onended = () => { osc.disconnect(); gain.disconnect(); };
        osc.start(start);
        osc.stop(start + 0.35);
      });
    } catch (_) {}
  }, [soundEnabled]);

  return {
    soundEnabled,
    toggleSound,
    playTap,
    playConnect,
    playError,
    playRowComplete,
    playVictory,
  };
}

/**
 * Bridge helper for puzzle boards: routes legacy event names to useSoundEffects + haptics.
 */
export function createGameSfx(sound) {
  return (type, idx = 1) => {
    if (type === 'error') {
      triggerHaptic(18);
      sound?.playError?.();
    } else if (type === 'complete') {
      triggerHaptic(20);
      sound?.playVictory?.();
    } else if (type === 'connect' || type === 'pulse') {
      triggerHaptic(12);
      sound?.playConnect?.(idx);
    } else {
      triggerHaptic(10);
      sound?.playTap?.();
    }
  };
}
