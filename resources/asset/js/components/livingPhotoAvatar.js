/**
 * AtgLivingPhotoAvatarStage (v2 — Neural Mesh Puppet & Synchronised Duet Edition)
 *
 * Composes @modernman00/shared-js-lib ATG engines + AtgPuppetMeshEngine:
 *  - DeformablePhotoPuppet (648-triangle 2D mesh warp: real jaw/mouth talking,
 *    eyelid-pinch blinking, and full-body kinematic dance/groove)
 *  - Synchronised 2-Photo Duet Mode (merge two photos into one phase-locked stage)
 *  - VisualSupremacyRuntime (P3/HDR telemetry & reduced-motion guards)
 *  - Critically-damped spring physics for cursor, touch-drag inertia & gyro tilt
 *  - SovereignVideoStudioClient.exportCanvasVideo() + branded end-card attribution
 *  - ShareManager.share() with deep-linked public challenge URLs
 */

import { DeformablePhotoPuppet } from './atgPuppetMeshEngine.js';
import { encodeAnimatedGif } from './atgGifEncoder.js';
import '@modernman00/shared-js-lib/SovereignVideoStudioClient.js';
import { ShareManager } from '@modernman00/shared-js-lib/ShareManager.js';
import { initVisualSupremacy } from '@modernman00/shared-js-lib/VisualSupremacyRuntime.js';

export const REACTION_CHOREOGRAPHY = {
  hello: { emoji: '👋', ring: '#f59e0b', glow: 'rgba(245, 158, 11, 0.36)', label: 'Wave hello' },
  laugh: { emoji: '😂', ring: '#eab308', glow: 'rgba(234, 179, 8, 0.38)',  label: 'Laugh' },
  wow:   { emoji: '😮', ring: '#38bdf8', glow: 'rgba(56, 189, 248, 0.38)', label: 'Wow' },
  love:  { emoji: '❤️', ring: '#f43f5e', glow: 'rgba(244, 63, 94, 0.40)',  label: 'Love' },
  nod:   { emoji: '🙆', ring: '#10b981', glow: 'rgba(16, 185, 129, 0.36)', label: 'Nod yes' },
  dance: { emoji: '💃', ring: '#a855f7', glow: 'rgba(168, 85, 247, 0.42)', label: 'Dance' },
  point: { emoji: '🫵', ring: '#f97316', glow: 'rgba(249, 115, 22, 0.38)', label: 'Yes, you!' },
  clap:  { emoji: '👏', ring: '#fbbf24', glow: 'rgba(251, 191, 36, 0.38)', label: 'Applause' },
};

export const CHALLENGE_FRAMES = {
  none:     { key: 'none',     label: 'Clean portrait',  badge: '',               accent: '#f59e0b' },
  birthday: { key: 'birthday', label: 'Birthday toast',  badge: '🥂 Birthday Toast', accent: '#f59e0b' },
  sunday:   { key: 'sunday',   label: 'Sunday groove',   badge: '💃 Sunday Groove',  accent: '#a855f7' },
  proud:    { key: 'proud',    label: 'Proud moment',    badge: '👏 Proud Moment',   accent: '#a31621' },
  surprise: { key: 'surprise', label: 'Big news!',       badge: '😮 Big News!',      accent: '#0284c7' },
};

export const AURA_PACKS = {
  classic:     { key: 'classic',     label: 'Signature Gold',  icon: '✨', unlockHint: 'Unlocked',                colors: ['#f59e0b', '#fde68a', '#f59e0b'] },
  royal_crown: { key: 'royal_crown', label: 'Royal Crown',     icon: '👑', unlockHint: 'Try 3 reactions or share', colors: ['#fbbf24', '#fef08a', '#b45309'] },
  fireworks:   { key: 'fireworks',   label: 'Fireworks',       icon: '🎆', unlockHint: 'Try 5 reactions or download', colors: ['#f43f5e', '#38bdf8', '#fbbf24'] },
  kente_prism: { key: 'kente_prism', label: 'Ancestral Prism', icon: '🌈', unlockHint: 'Try all 8 reactions',     colors: ['#10b981', '#f59e0b', '#a31621'] },
};

export const ASPECT_RATIOS = {
  '1:1':  { key: '1:1',  label: '1:1 Square',       width: 512, height: 512 },
  '9:16': { key: '9:16', label: '9:16 Story/Reels', width: 360, height: 640 },
  '16:9': { key: '16:9', label: '16:9 Widescreen',  width: 640, height: 360 },
  '4:5':  { key: '4:5',  label: '4:5 Portrait',     width: 448, height: 560 },
  '3:4':  { key: '3:4',  label: '3:4 Classic',      width: 420, height: 560 },
  '2:3':  { key: '2:3',  label: '2:3 Tall',         width: 400, height: 600 },
  '3:2':  { key: '3:2',  label: '3:2 Photo',        width: 600, height: 400 },
  '4:3':  { key: '4:3',  label: '4:3 Standard',     width: 560, height: 420 },
  '5:4':  { key: '5:4',  label: '5:4 Print',        width: 560, height: 448 },
  '9:21': { key: '9:21', label: '9:21 Ultra Tall',  width: 288, height: 672 },
  '21:9': { key: '21:9', label: '21:9 Cinema',      width: 672, height: 288 },
};

const PACK_STORAGE_KEY = 'atg_living_packs_v1';

const VIDEO_MIME_CANDIDATES = [
  'video/mp4;codecs=avc1',
  'video/webm;codecs=vp9',
  'video/webm;codecs=vp8',
  'video/webm',
];

function selectSupportedVideoMime() {
  if (typeof MediaRecorder === 'undefined' || typeof MediaRecorder.isTypeSupported !== 'function') {
    return 'video/webm';
  }
  return VIDEO_MIME_CANDIDATES.find((t) => MediaRecorder.isTypeSupported(t)) || 'video/webm';
}

export class AtgLivingPhotoAvatarStage {
  constructor({ canvas, viewport, photoUrl = '', senderName = '', onPackUnlocked = null, onVoiceStateChange = null, onDuetStateChange = null }) {
    this.canvas = canvas;
    this.viewport = viewport;
    this.photoUrl = photoUrl;
    this.senderName = senderName;
    this.onPackUnlocked = onPackUnlocked;
    this.onVoiceStateChange = onVoiceStateChange;
    this.onDuetStateChange = onDuetStateChange;
    this.ctx = canvas.getContext('2d');

    this.size = 512;
    this.canvas.width = this.size;
    this.canvas.height = this.size;

    // Primary & Duet Deformable Mesh Puppets
    this.primaryPuppet = new DeformablePhotoPuppet({ gridCols: 18, gridRows: 18 });
    this.duetPuppet = new DeformablePhotoPuppet({ gridCols: 18, gridRows: 18 });
    this.hasDuet = false;

    // Backwards-compatible alias for unit tests / external faceMesh inspection
    this.heirloom = {
      faceMesh: {
        get center() { return this._p.rig.headCenter; },
        set center(v) { this._p.rig.headCenter = v; },
        get leftEye() { return this._p.rig.leftEye; },
        set leftEye(v) { this._p.rig.leftEye = v; },
        get rightEye() { return this._p.rig.rightEye; },
        set rightEye(v) { this._p.rig.rightEye = v; },
        get chin() { return { x: this._p.rig.mouth.x, y: this._p.rig.neckY }; },
        _p: this.primaryPuppet,
      },
    };

    const VideoClientCtor = typeof globalThis !== 'undefined' && typeof globalThis.SovereignVideoStudioClient === 'function'
      ? globalThis.SovereignVideoStudioClient
      : null;
    this.studioClient = VideoClientCtor ? new VideoClientCtor() : null;

    this.isLoaded = false;
    this.isActive = false;
    this.isRecording = false;
    this.exportStartMs = 0;
    this.exportDurationMs = 3200;

    this.reducedMotion = typeof window !== 'undefined' && window.matchMedia
      ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
      : false;

    // Critically-damped spring state for 2.5D head tilt & drag inertia
    this.spring = { x: 0, y: 0, vx: 0, vy: 0, targetX: 0, targetY: 0 };
    this.isDragging = false;
    this.dragOrigin = { x: 0, y: 0 };

    // Blink state machine
    this.blinkProgress = 0;
    this.isBlinking = false;
    this.nextBlinkTime = Date.now() + 2600;

    // Reaction, frame & unlockable aura pack state
    this.currentReaction = 'hello';
    this.currentFrame = 'none';
    this.currentPack = 'classic';
    this.currentAspect = '1:1';
    this.triedReactions = new Set(['hello']);
    this.unlockedPacks = this._loadUnlockedPacks();

    this.reactionStartMs = 0;
    this.reactionDurationMs = 2600;
    this.shockwaves = [];

    // On-device Voice-Reactive Talk & Sing state
    this.isVoiceActive = false;
    this.voiceLevel = 0;
    this.micStream = null;
    this.micAnalyser = null;
    this.micData = null;

    this.rafId = null;
    this.audioCtx = null;
    this._boundHandlers = [];
    this._objectUrl = null;
    this._duetObjectUrl = null;
  }

  _loadUnlockedPacks() {
    const base = new Set(['classic']);
    try {
      const raw = localStorage.getItem(PACK_STORAGE_KEY);
      if (raw) {
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) parsed.forEach((k) => { if (AURA_PACKS[k]) base.add(k); });
      }
    } catch (_) {}
    return base;
  }

  _saveUnlockedPacks() {
    try {
      localStorage.setItem(PACK_STORAGE_KEY, JSON.stringify(Array.from(this.unlockedPacks)));
    } catch (_) {}
  }

  unlockPack(packKey) {
    if (!AURA_PACKS[packKey] || this.unlockedPacks.has(packKey)) return false;
    this.unlockedPacks.add(packKey);
    this._saveUnlockedPacks();
    if (typeof this.onPackUnlocked === 'function') this.onPackUnlocked(AURA_PACKS[packKey]);
    return true;
  }

  isPackUnlocked(packKey) {
    return this.unlockedPacks.has(packKey);
  }

  setFrame(frameKey) {
    if (CHALLENGE_FRAMES[frameKey]) this.currentFrame = frameKey;
  }

  setPack(packKey) {
    if (!AURA_PACKS[packKey] || !this.isPackUnlocked(packKey)) return false;
    this.currentPack = packKey;
    return true;
  }

  setAspectRatio(ratioKey) {
    const spec = ASPECT_RATIOS[ratioKey];
    if (!spec) return false;
    this.currentAspect = ratioKey;
    this.canvas.width = spec.width;
    this.canvas.height = spec.height;
    return true;
  }

  async init() {
    try { initVisualSupremacy(); } catch (_) {}
    if (!this.photoUrl) return false;
    return this.loadPhotoUrl(this.photoUrl);
  }

  async _decodeImage(url) {
    const loadImg = (src, withCors) => new Promise((resolve, reject) => {
      const img = new Image();
      img.decoding = 'async';
      if (withCors) img.crossOrigin = 'anonymous';
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = src;
    });

    const isCrossOrigin = typeof window !== 'undefined'
      && /^https?:\/\//i.test(url)
      && !url.startsWith(window.location.origin);

    if (!isCrossOrigin) {
      return loadImg(url, false);
    }

    // Use <img> (governed by img-src, avoiding connect-src CSP errors from fetch()).
    // Try anonymous CORS first for clean canvas export, then fall back to non-CORS
    // so the 60fps canvas animation still renders if the host/cache lacks CORS headers.
    try {
      return await loadImg(url, true);
    } catch (_) {
      return loadImg(url, false);
    }
  }

  async loadPhotoUrl(url) {
    if (!url) return false;
    this.photoUrl = url;
    try {
      const img = await this._decodeImage(url);
      this.primaryPuppet.setImage(img);
      this.primaryPuppet.detectRigAsync(); // Async landmark refinement (non-blocking)
      if (!this._boundHandlers.length) {
        this._bindInteractions();
      }
      this.isLoaded = true;
      return true;
    } catch (err) {
      console.warn('[AtgLivingPhotoAvatarStage] Portrait load failed:', err);
      return false;
    }
  }

  async loadCustomFile(file) {
    if (!file) return false;
    if (this._objectUrl) URL.revokeObjectURL(this._objectUrl);
    this._objectUrl = URL.createObjectURL(file);
    const ok = await this.loadPhotoUrl(this._objectUrl);
    if (ok) {
      this.start();
      this.triggerReaction(this.currentReaction, { playSound: true });
    }
    return ok;
  }

  /**
   * Load a second photo to merge into a synchronised Duet choreography stage.
   */
  async loadDuetFile(file) {
    if (!file) return false;
    if (this._duetObjectUrl) URL.revokeObjectURL(this._duetObjectUrl);
    this._duetObjectUrl = URL.createObjectURL(file);
    try {
      const img = await this._decodeImage(this._duetObjectUrl);
      this.duetPuppet.setImage(img);
      this.duetPuppet.detectRigAsync();
      this.hasDuet = true;
      if (typeof this.onDuetStateChange === 'function') this.onDuetStateChange(true);
      this.start();
      this.triggerReaction(this.currentReaction, { playSound: true });
      return true;
    } catch (err) {
      console.warn('[AtgLivingPhotoAvatarStage] Duet photo load failed:', err);
      return false;
    }
  }

  clearDuet() {
    this.hasDuet = false;
    this.duetPuppet.image = null;
    if (this._duetObjectUrl) {
      URL.revokeObjectURL(this._duetObjectUrl);
      this._duetObjectUrl = null;
    }
    if (typeof this.onDuetStateChange === 'function') this.onDuetStateChange(false);
  }

  applyFaceBox(box) {
    this.primaryPuppet.applyFaceBox(box);
  }

  _listen(target, event, handler, opts) {
    target.addEventListener(event, handler, opts);
    this._boundHandlers.push(() => target.removeEventListener(event, handler, opts));
  }

  _bindInteractions() {
    const onPointerMove = (e) => {
      if (this.reducedMotion) return;
      const rect = this.viewport.getBoundingClientRect();
      if (!rect.width || !rect.height) return;
      const nx = ((e.clientX - rect.left) / rect.width - 0.5) * 2;
      const ny = ((e.clientY - rect.top) / rect.height - 0.5) * 2;
      this.spring.targetX = Math.max(-1.2, Math.min(1.2, nx)) * (this.isDragging ? 1.45 : 0.75);
      this.spring.targetY = Math.max(-1.2, Math.min(1.2, ny)) * (this.isDragging ? 1.45 : 0.75);
    };

    const onPointerDown = (e) => {
      this.isDragging = true;
      this.dragOrigin = { x: e.clientX, y: e.clientY };
    };

    const onPointerUp = (e) => {
      if (!this.isDragging) return;
      this.isDragging = false;
      if (Math.hypot(e.clientX - this.dragOrigin.x, e.clientY - this.dragOrigin.y) < 10) {
        this.triggerReaction(this.currentReaction, { playSound: true });
      }
    };

    const onLeave = () => {
      this.isDragging = false;
      this.spring.targetX = 0;
      this.spring.targetY = 0;
    };

    this._listen(window, 'pointermove', onPointerMove, { passive: true });
    this._listen(this.viewport, 'pointerdown', onPointerDown, { passive: true });
    this._listen(window, 'pointerup', onPointerUp, { passive: true });
    this._listen(this.viewport, 'pointerleave', onLeave, { passive: true });

    if (typeof window !== 'undefined' && 'DeviceOrientationEvent' in window) {
      this._listen(window, 'deviceorientation', (e) => {
        if (this.reducedMotion || this.isDragging || e.gamma == null || e.beta == null) return;
        this.spring.targetX = Math.max(-1, Math.min(1, e.gamma / 35));
        this.spring.targetY = Math.max(-1, Math.min(1, (e.beta - 45) / 35));
      }, { passive: true });
    }

    this._listen(document, 'visibilitychange', () => {
      if (document.hidden) {
        this._stopLoop();
      } else if (this.isActive) {
        this._startLoop();
      }
    });
  }

  start() {
    this.isActive = true;
    this.canvas.hidden = false;
    if (!this.isLoaded) return;
    this._startLoop();
  }

  stop() {
    this.isActive = false;
    this.canvas.hidden = true;
    this._stopLoop();
    this.stopVoiceReactive();
  }

  _startLoop() {
    if (this.rafId !== null || !this.isLoaded) return;
    const startMs = performance.now();
    const tick = (now) => {
      if (!this.isActive) {
        this.rafId = null;
        return;
      }
      this._sampleVoiceEnergy();
      this._stepPhysics(now);
      this._renderComposite(now, (now - startMs) / 1000);
      this.rafId = requestAnimationFrame(tick);
    };
    this.rafId = requestAnimationFrame(tick);
  }

  _stopLoop() {
    if (this.rafId !== null) {
      cancelAnimationFrame(this.rafId);
      this.rafId = null;
    }
  }

  async toggleVoiceReactive() {
    if (this.isVoiceActive) {
      this.stopVoiceReactive();
      return false;
    }
    return this.startVoiceReactive();
  }

  async startVoiceReactive() {
    if (this.isVoiceActive) return true;
    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
      throw new Error('Microphone access is not supported in this browser.');
    }
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!this.audioCtx) this.audioCtx = new AudioCtx();
    if (this.audioCtx.state === 'suspended') await this.audioCtx.resume();

    const source = this.audioCtx.createMediaStreamSource(stream);
    const analyser = this.audioCtx.createAnalyser();
    analyser.fftSize = 256;
    source.connect(analyser);

    this.micStream = stream;
    this.micAnalyser = analyser;
    this.micData = new Uint8Array(analyser.frequencyBinCount);
    this.isVoiceActive = true;
    if (typeof this.onVoiceStateChange === 'function') this.onVoiceStateChange(true);
    return true;
  }

  stopVoiceReactive() {
    if (!this.isVoiceActive && !this.micStream) return;
    if (this.micStream) {
      this.micStream.getTracks().forEach((t) => t.stop());
      this.micStream = null;
    }
    this.micAnalyser = null;
    this.micData = null;
    this.voiceLevel = 0;
    this.isVoiceActive = false;
    if (typeof this.onVoiceStateChange === 'function') this.onVoiceStateChange(false);
  }

  _sampleVoiceEnergy() {
    if (!this.isVoiceActive || !this.micAnalyser || !this.micData) {
      this.voiceLevel *= 0.85;
      return;
    }
    this.micAnalyser.getByteTimeDomainData(this.micData);
    let sumSq = 0;
    for (let i = 0; i < this.micData.length; i++) {
      const v = (this.micData[i] - 128) / 128;
      sumSq += v * v;
    }
    const rms = Math.sqrt(sumSq / this.micData.length);
    const target = Math.min(1, rms * 5.2);
    this.voiceLevel = this.voiceLevel * 0.55 + target * 0.45;
  }

  triggerReaction(reaction, opts = {}) {
    if (!REACTION_CHOREOGRAPHY[reaction]) return;
    this.currentReaction = reaction;
    this.reactionStartMs = performance.now();
    this.triedReactions.add(reaction);

    if (this.triedReactions.size >= 3) this.unlockPack('royal_crown');
    if (this.triedReactions.size >= 5) this.unlockPack('fireworks');
    if (this.triedReactions.size >= 8) this.unlockPack('kente_prism');

    const spec = REACTION_CHOREOGRAPHY[reaction];
    const pack = AURA_PACKS[this.currentPack] || AURA_PACKS.classic;
    this.shockwaves.push({ startMs: this.reactionStartMs, color: pack.colors[0] || spec.ring });
    this.spring.vy -= 0.16;

    if (opts.playSound) this._playMicroChime(reaction);
  }

  _stepPhysics(now) {
    const stiffness = 0.14;
    const damping = 0.78;
    this.spring.vx = (this.spring.vx + (this.spring.targetX - this.spring.x) * stiffness) * damping;
    this.spring.vy = (this.spring.vy + (this.spring.targetY - this.spring.y) * stiffness) * damping;
    this.spring.x += this.spring.vx;
    this.spring.y += this.spring.vy;

    // Natural eye blink state machine
    if (!this.isBlinking && now > this.nextBlinkTime) {
      this.isBlinking = true;
      this.blinkProgress = 0;
    }
    if (this.isBlinking) {
      this.blinkProgress += 0.16;
      if (this.blinkProgress >= 2.0) {
        this.isBlinking = false;
        this.blinkProgress = 0;
        this.nextBlinkTime = now + 2400 + Math.random() * 2800;
      }
    }

    this.shockwaves = this.shockwaves.filter((sw) => now - sw.startMs < 850);
  }

  _renderPuppetCover(ctx, puppet, targetX, targetY, targetW, targetH) {
    if (!puppet.image) return;
    const iw = puppet.imgW || 1;
    const ih = puppet.imgH || 1;
    const scale = Math.max(targetW / iw, targetH / ih) * 1.06;
    const drawW = iw * scale;
    const drawH = ih * scale;
    const dx = targetX + (targetW - drawW) / 2;
    const dy = targetY + (targetH - drawH) / 2;
    puppet.render(ctx, dx, dy, drawW, drawH);
  }

  _renderComposite(now, elapsedSec) {
    const ctx = this.ctx;
    const W = this.canvas.width;
    const H = this.canvas.height;
    const S = Math.min(W, H);
    const cx = W / 2;
    const cy = H / 2;
    const radius = S * 0.44;
    const spec = REACTION_CHOREOGRAPHY[this.currentReaction] || REACTION_CHOREOGRAPHY.hello;
    const pack = AURA_PACKS[this.currentPack] || AURA_PACKS.classic;
    const frame = CHALLENGE_FRAMES[this.currentFrame] || CHALLENGE_FRAMES.none;

    const elapsed = now - this.reactionStartMs;
    const intensity = (!this.reducedMotion && this.reactionStartMs > 0 && elapsed <= this.reactionDurationMs)
      ? Math.sin((elapsed / this.reactionDurationMs) * Math.PI)
      : (this.currentReaction === 'dance' ? 0.55 : 0);

    const blinkAmount = this.isBlinking
      ? (this.blinkProgress <= 1.0 ? this.blinkProgress : 2.0 - this.blinkProgress)
      : 0;

    const deformState = {
      elapsedSec,
      mode: this.isVoiceActive && intensity < 0.2 ? 'talk' : this.currentReaction,
      intensity,
      voiceLevel: this.voiceLevel,
      blinkAmount,
      tiltX: this.spring.x,
      tiltY: this.spring.y,
    };

    this.primaryPuppet.updateDeformation(deformState);
    if (this.hasDuet && this.duetPuppet.image) {
      this.duetPuppet.updateDeformation(deformState);
    }

    ctx.clearRect(0, 0, W, H);

    // 1. Studio ambient aura glow
    const audioRadius = radius * (1.15 + this.voiceLevel * 0.14);
    const glowGrad = ctx.createRadialGradient(cx, cy, radius * 0.76, cx, cy, audioRadius);
    glowGrad.addColorStop(0, spec.glow);
    glowGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
    ctx.fillStyle = glowGrad;
    ctx.beginPath();
    ctx.arc(cx, cy, audioRadius, 0, Math.PI * 2);
    ctx.fill();

    // 2. Shockwave pulse rings
    for (const sw of this.shockwaves) {
      const t = (now - sw.startMs) / 850;
      ctx.save();
      ctx.strokeStyle = sw.color;
      ctx.globalAlpha = Math.max(0, 1 - t) * 0.65;
      ctx.lineWidth = 4 * (1 - t) + 1;
      ctx.beginPath();
      ctx.arc(cx, cy, radius + t * 30, 0, Math.PI * 2);
      ctx.stroke();
      ctx.restore();
    }

    // 3. Clipped circular stage — Single Deformable Mesh OR Synchronised 2-Photo Duet
    ctx.save();
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.clip();

    if (this.hasDuet && this.duetPuppet.image) {
      const leftX = cx - radius;
      const topY = cy - radius;
      const halfW = radius;
      const fullH = radius * 2;

      ctx.save();
      ctx.beginPath();
      ctx.rect(leftX, topY, halfW, fullH);
      ctx.clip();
      this._renderPuppetCover(ctx, this.primaryPuppet, leftX, topY, halfW, fullH);
      ctx.restore();

      ctx.save();
      ctx.beginPath();
      ctx.rect(cx, topY, halfW, fullH);
      ctx.clip();
      this._renderPuppetCover(ctx, this.duetPuppet, cx, topY, halfW, fullH);
      ctx.restore();

      // Glowing sync seam down the centre
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.78)';
      ctx.lineWidth = 3;
      ctx.beginPath();
      ctx.moveTo(cx, topY);
      ctx.lineTo(cx, topY + fullH);
      ctx.stroke();
    } else {
      this._renderPuppetCover(ctx, this.primaryPuppet, cx - radius, cy - radius, radius * 2, radius * 2);
    }

    // 4. Directional studio specular sheen
    const specX = cx + this.spring.x * radius * 0.55;
    const specY = cy + this.spring.y * radius * 0.55 - radius * 0.25;
    const sheen = ctx.createRadialGradient(specX, specY, 8, specX, specY, radius * 1.15);
    sheen.addColorStop(0, 'rgba(255, 255, 255, 0.16)');
    sheen.addColorStop(0.5, 'rgba(255, 255, 255, 0.03)');
    sheen.addColorStop(1, 'rgba(255, 255, 255, 0)');
    ctx.fillStyle = sheen;
    ctx.fillRect(0, 0, W, H);
    ctx.restore();

    // 5. Aura Pack metallic/prism ring border
    ctx.save();
    const colors = pack.key === 'classic' ? [spec.ring, '#fde68a', spec.ring] : pack.colors;
    const ringGrad = ctx.createLinearGradient(cx - radius, cy - radius, cx + radius, cy + radius);
    ringGrad.addColorStop(0, colors[0]);
    ringGrad.addColorStop(0.5, colors[1]);
    ringGrad.addColorStop(1, colors[2]);
    ctx.strokeStyle = ringGrad;
    ctx.lineWidth = 8 + this.voiceLevel * 4;
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.stroke();
    ctx.restore();

    // 6. Optional Themed Challenge Frame Pill or Duet Sync Pill
    const topBadge = this.hasDuet
      ? (frame.badge ? `${frame.badge} · Sync Duet` : '🤝 Sync Duet')
      : frame.badge;
    if (topBadge) {
      ctx.save();
      ctx.font = '700 18px system-ui, -apple-system, sans-serif';
      const textW = ctx.measureText(topBadge).width;
      const pillW = Math.min(S - 48, textW + 32);
      const pillH = 36;
      const pillX = cx - pillW / 2;
      const pillY = 16;
      ctx.fillStyle = 'rgba(15, 23, 42, 0.88)';
      this._roundRect(ctx, pillX, pillY, pillW, pillH, 18);
      ctx.fill();
      ctx.strokeStyle = frame.accent || '#f59e0b';
      ctx.lineWidth = 2.5;
      ctx.stroke();
      ctx.fillStyle = '#ffffff';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(topBadge, cx, pillY + pillH / 2 + 1);
      ctx.restore();
    }

    // 7. Branded Attribution End-Card (last 0.85s of exported video only)
    if (this.isRecording && this.exportStartMs > 0) {
      const exportElapsed = now - this.exportStartMs;
      const fadeStart = this.exportDurationMs - 850;
      if (exportElapsed >= fadeStart) {
        const alpha = Math.min(1, (exportElapsed - fadeStart) / 300);
        ctx.save();
        ctx.globalAlpha = alpha;
        const cardW = Math.min(W - 24, 340);
        const cardH = 38;
        const cardX = cx - cardW / 2;
        const cardY = H - 48;
        ctx.fillStyle = 'rgba(15, 23, 42, 0.92)';
        this._roundRect(ctx, cardX, cardY, cardW, cardH, 19);
        ctx.fill();
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 2;
        ctx.stroke();
        ctx.fillStyle = '#ffffff';
        ctx.font = '600 15px system-ui, -apple-system, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('✨ Made on FamilyPlatform · Try yours', cx, cardY + cardH / 2 + 1);
        ctx.restore();
      }
    }
  }

  _roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }

  _playMicroChime(reaction) {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      if (!this.audioCtx) this.audioCtx = new AudioCtx();
      if (this.audioCtx.state === 'suspended') this.audioCtx.resume().catch(() => {});
      const now = this.audioCtx.currentTime;
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();
      const baseFreq = reaction === 'wow' ? 587.33 : reaction === 'love' ? 523.25 : 440;
      osc.type = 'sine';
      osc.frequency.setValueAtTime(baseFreq, now);
      osc.frequency.exponentialRampToValueAtTime(baseFreq * 1.25, now + 0.11);
      gain.gain.setValueAtTime(0.04, now);
      gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.14);
      osc.connect(gain);
      gain.connect(this.audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.15);
    } catch (_) {}
  }

  _buildChallengeUrl() {
    const params = new URLSearchParams({
      reaction: this.currentReaction,
      frame: this.currentFrame,
      pack: this.currentPack,
    });
    if (this.senderName) params.set('from', this.senderName);
    return `${window.location.origin}/living-avatar?${params.toString()}`;
  }

  async downloadAnimatedVideo() {
    if (this.isRecording || !this.isLoaded) return null;
    this.isRecording = true;
    this.exportStartMs = performance.now();
    this.unlockPack('fireworks');
    try {
      this.start();
      this.triggerReaction(this.currentReaction, { playSound: false });
      if (this.studioClient && typeof this.studioClient.exportCanvasVideo === 'function') {
        return await this.studioClient.exportCanvasVideo(this.canvas, {
          fps: 30,
          durationSeconds: 3.2,
          filename: `living-avatar-${this.currentReaction}`,
          videoBitsPerSecond: 6000000,
        });
      }
      const { blob, ext } = await this._captureVideoBlob(this.exportDurationMs, 30);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `living-avatar-${this.currentReaction}.${ext}`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(() => URL.revokeObjectURL(url), 2000);
      return blob;
    } finally {
      this.isRecording = false;
      this.exportStartMs = 0;
    }
  }

  /**
   * Capture and download a looping animated GIF89a of the current reaction/duet.
   */
  async downloadAnimatedGif(frameCount = 16, intervalMs = 100) {
    if (this.isRecording || !this.isLoaded) return null;
    this.isRecording = true;
    this.unlockPack('fireworks');
    try {
      this.start();
      this.triggerReaction(this.currentReaction, { playSound: false });

      const scale = Math.min(1, 240 / Math.max(this.canvas.width, this.canvas.height));
      const gw = Math.max(64, Math.round(this.canvas.width * scale));
      const gh = Math.max(64, Math.round(this.canvas.height * scale));
      const tmp = document.createElement('canvas');
      tmp.width = gw;
      tmp.height = gh;
      const tCtx = tmp.getContext('2d');

      const frames = [];
      for (let i = 0; i < frameCount; i++) {
        tCtx.clearRect(0, 0, gw, gh);
        tCtx.drawImage(this.canvas, 0, 0, gw, gh);
        frames.push(tCtx.getImageData(0, 0, gw, gh).data);
        await new Promise((r) => setTimeout(r, intervalMs));
      }

      const gifBlob = encodeAnimatedGif(frames, gw, gh, Math.round(intervalMs / 10));
      const url = URL.createObjectURL(gifBlob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `living-avatar-${this.currentReaction}.gif`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(() => URL.revokeObjectURL(url), 2000);
      return gifBlob;
    } finally {
      this.isRecording = false;
    }
  }

  async recordAnimatedVideoFile() {
    if (this.isRecording || !this.isLoaded) return null;
    this.isRecording = true;
    this.exportStartMs = performance.now();
    this.unlockPack('royal_crown');
    try {
      this.start();
      this.triggerReaction(this.currentReaction, { playSound: false });
      const { blob, ext, mime } = await this._captureVideoBlob(this.exportDurationMs, 30);
      const file = new File([blob], `living-avatar-${this.currentReaction}.${ext}`, { type: mime });
      const spec = REACTION_CHOREOGRAPHY[this.currentReaction] || REACTION_CHOREOGRAPHY.hello;
      return {
        file,
        blob,
        ext,
        mime,
        reaction: this.currentReaction,
        label: spec.label,
        emoji: spec.emoji,
      };
    } finally {
      this.isRecording = false;
      this.exportStartMs = 0;
    }
  }

  async shareAnimatedVideo() {
    const captured = await this.recordAnimatedVideoFile();
    if (!captured) return false;
    return await ShareManager.share({
      title: `My Living Avatar (${captured.label})`,
      text: `See my Living Avatar ${this.hasDuet ? 'Duet ' : ''}in motion ${captured.emoji} — tap to try yours on FamilyPlatform!`,
      url: this._buildChallengeUrl(),
      file: captured.file,
    });
  }

  _captureVideoBlob(durationMs = 3200, fps = 30) {
    return new Promise((resolve, reject) => {
      if (typeof this.canvas.captureStream !== 'function' || typeof MediaRecorder === 'undefined') {
        reject(new Error('Your browser does not support live canvas video recording.'));
        return;
      }
      const mime = selectSupportedVideoMime();
      const ext = mime.includes('mp4') ? 'mp4' : 'webm';
      const stream = this.canvas.captureStream(fps);
      const chunks = [];
      let recorder;
      try {
        recorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 6000000 });
      } catch (_) {
        recorder = new MediaRecorder(stream);
      }
      recorder.ondataavailable = (ev) => {
        if (ev.data && ev.data.size > 0) chunks.push(ev.data);
      };
      recorder.onstop = () => {
        resolve({ blob: new Blob(chunks, { type: mime }), ext, mime });
      };
      recorder.onerror = (err) => reject(err);
      recorder.start(100);
      setTimeout(() => {
        if (recorder.state === 'recording') recorder.stop();
      }, durationMs);
    });
  }

  destroy() {
    this._stopLoop();
    this.stopVoiceReactive();
    this._boundHandlers.forEach((off) => off());
    this._boundHandlers = [];
    if (this._objectUrl) { URL.revokeObjectURL(this._objectUrl); this._objectUrl = null; }
    if (this._duetObjectUrl) { URL.revokeObjectURL(this._duetObjectUrl); this._duetObjectUrl = null; }
    if (this.audioCtx && typeof this.audioCtx.close === 'function') {
      this.audioCtx.close().catch(() => {});
    }
  }
}

if (typeof window !== 'undefined') {
  window.AtgLivingPhotoAvatarStage = AtgLivingPhotoAvatarStage;
  window.ATG_LIVING_FRAMES = CHALLENGE_FRAMES;
  window.ATG_LIVING_PACKS = AURA_PACKS;
  window.ATG_LIVING_ASPECTS = ASPECT_RATIOS;
}
