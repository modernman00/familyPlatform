/**
 * vse-canvas.js
 *
 * Next-Gen Visual Supremacy Engine (VSE) Client Runtime
 * 
 * Features:
 * - Dynamic P3 wide colour gamut and HDR capability detection
 * - 2.5D tactile gyroscopic micro-parallax with spring damping
 * - Dr. Viktor Kovács & Marcus security killswitch (suspends sensors on input focus)
 * - Accessibility & reduced-motion invariant compliance
 * - 120Hz ProMotion frame pacing via requestAnimationFrame
 */

'use strict';

class VisualSupremacyRuntime {
    constructor() {
        this.isP3 = false;
        this.isHDR = false;
        this.reducedMotion = false;
        this.isLowPowerMode = false;
        this.isSensorActive = false;
        this.isInputFocused = false;
        this.orientation = { gamma: 0, beta: 0 };
        this.targetOrientation = { gamma: 0, beta: 0 };
        this.rafId = null;
        this.activeCards = new Set();
        this.intersectionObserver = null;
    }

    init() {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        // 1. Telemetry: Wide Colour Gamut & Dynamic Range Detection
        try {
            this.isP3 = window.matchMedia && window.matchMedia('(color-gamut: p3)').matches;
            this.isHDR = window.matchMedia && window.matchMedia('(dynamic-range: high)').matches;
            this.reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const root = document.documentElement;
            if (this.isP3) {
                root.classList.add('vse-p3-capable');
            }
            if (this.isHDR) {
                root.classList.add('vse-hdr-capable');
            }

            // Watch for live reduced motion preference changes
            if (window.matchMedia) {
                window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
                    this.reducedMotion = e.matches;
                    if (this.reducedMotion) {
                        this.resetTransforms();
                        this.stopSensors();
                    } else if (!this.isLowPowerMode) {
                        this.startSensors();
                    }
                });
            }
        } catch (_) {}

        if (this.reducedMotion) {
            return;
        }

        // 2. Segun & Victor PWA Battery-Saver & Low-Memory Killswitch
        this.setupBatteryAndMemoryGuard();

        // 3. Observe media cards using IntersectionObserver to only animate visible elements
        this.setupCardObserver();

        // 4. Mandatory Adversarial Security Gate (Dr. Viktor Kovács & Marcus): Input Focus Guard
        this.setupSecurityGuard();

        // 5. Desktop Pointer Parallax Fallback
        this.setupDesktopPointerParallax();

        // 6. Initialize Sensor Pipeline
        this.startSensors();
    }

    setupBatteryAndMemoryGuard() {
        try {
            // Low-end Android / memory-constrained device guard (< 2GB RAM)
            if (typeof navigator !== 'undefined' && navigator.deviceMemory && navigator.deviceMemory < 2) {
                this.isLowPowerMode = true;
                this.stopSensors();
                this.resetTransforms();
                return;
            }

            // Battery Status API guard: Disable 2.5D sensors if battery <= 20% and not charging
            if (typeof navigator !== 'undefined' && typeof navigator.getBattery === 'function') {
                navigator.getBattery().then((battery) => {
                    const checkBattery = () => {
                        const low = !battery.charging && battery.level <= 0.20;
                        this.isLowPowerMode = low;
                        if (low) {
                            this.stopSensors();
                            this.resetTransforms();
                        } else if (!this.reducedMotion && !this.isInputFocused && !document.hidden) {
                            this.startSensors();
                        }
                    };
                    checkBattery();
                    battery.addEventListener('levelchange', checkBattery);
                    battery.addEventListener('chargingchange', checkBattery);
                }).catch(() => {});
            }
        } catch (_) {}
    }

    setupCardObserver() {
        if (!('IntersectionObserver' in window)) {
            return;
        }

        this.intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.activeCards.add(entry.target);
                    if (!this.reducedMotion && !this.isLowPowerMode) {
                        entry.target.classList.add('is-living-active');
                    }
                } else {
                    this.activeCards.delete(entry.target);
                    entry.target.classList.remove('is-living-active');
                    // Reset transform when scrolling out of viewport to save GPU compositing
                    this.resetCardTransform(entry.target);
                }
            });
        }, { threshold: 0.15 });

        // Auto-observe post-media-stage containers
        this.refreshObservedCards();

        // Re-check for dynamically inserted posts (e.g. infinite scroll, new post submission)
        const observer = new MutationObserver(() => this.refreshObservedCards());
        observer.observe(document.body, { childList: true, subtree: true });
    }

    refreshObservedCards() {
        if (!this.intersectionObserver) return;
        const stages = document.querySelectorAll('.post-media-stage:not([data-vse-observed])');
        stages.forEach((el) => {
            el.setAttribute('data-vse-observed', 'true');
            this.intersectionObserver.observe(el);
        });
    }

    setupSecurityGuard() {
        // Gyroscopic Timing Side-Channel Defense: Immediately detach listeners on active input
        const pauseEvents = ['focusin', 'focus'];
        const resumeEvents = ['focusout', 'blur'];

        pauseEvents.forEach((ev) => {
            document.addEventListener(ev, (e) => {
                const tag = e.target && e.target.tagName ? e.target.tagName.toLowerCase() : '';
                const isEditable = e.target && (e.target.isContentEditable || tag === 'input' || tag === 'textarea' || tag === 'select');
                if (isEditable) {
                    this.isInputFocused = true;
                    this.stopSensors();
                    this.resetTransforms();
                }
            }, true);
        });

        resumeEvents.forEach((ev) => {
            document.addEventListener(ev, () => {
                this.isInputFocused = false;
                if (!document.hidden && !this.reducedMotion) {
                    this.startSensors();
                }
            }, true);
        });

        // Tab Visibility Guard
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopSensors();
                this.resetTransforms();
            } else if (!this.isInputFocused && !this.reducedMotion) {
                this.startSensors();
            }
        });
    }

    startSensors() {
        if (this.isSensorActive || this.reducedMotion || this.isInputFocused || this.isLowPowerMode) {
            return;
        }

        if (typeof window.DeviceOrientationEvent !== 'undefined') {
            this.orientationHandler = (e) => this.handleDeviceOrientation(e);
            window.addEventListener('deviceorientation', this.orientationHandler, { passive: true });
            this.isSensorActive = true;
            this.scheduleRenderLoop();
        }
    }

    stopSensors() {
        if (!this.isSensorActive) {
            return;
        }

        if (this.orientationHandler) {
            window.removeEventListener('deviceorientation', this.orientationHandler);
            this.orientationHandler = null;
        }
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
            this.rafId = null;
        }
        this.isSensorActive = false;
    }

    handleDeviceOrientation(event) {
        if (this.isInputFocused || this.reducedMotion || this.isLowPowerMode) return;

        // Gamma: Left to Right (-90 to 90), Beta: Front to Back (-180 to 180)
        let g = event.gamma || 0;
        let b = event.beta || 0;

        // Normalise resting hand angle: smartphones are typically held tilted forward by ~45 degrees
        b = b - 45;

        // Clamp to safe, subtle displacement (±15 degrees input, mapping to ±3.5 degrees visual rotation)
        const clamp = (val, max) => Math.max(-max, Math.min(max, val));
        this.targetOrientation.gamma = clamp(g, 15);
        this.targetOrientation.beta = clamp(b, 15);
    }

    setupDesktopPointerParallax() {
        // For non-touch/pointer devices, provide a subtle interactive hover depth response
        if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
            document.addEventListener('mousemove', (e) => {
                if (this.isInputFocused || this.reducedMotion || this.activeCards.size === 0) return;

                this.activeCards.forEach((card) => {
                    const rect = card.getBoundingClientRect();
                    if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) {
                        const relX = ((e.clientX - rect.left) / rect.width) * 2 - 1; // -1 to 1
                        const relY = ((e.clientY - rect.top) / rect.height) * 2 - 1; // -1 to 1

                        const rotX = -relY * 3.5;
                        const rotY = relX * 3.5;
                        const transX = relX * 4;
                        const transY = relY * 4;

                        const img = card.querySelector('.post-media-first-img');
                        if (img) {
                            img.style.transform = `perspective(800px) rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg) translate3d(${transX.toFixed(1)}px, ${transY.toFixed(1)}px, 6px) scale(1.02)`;
                            img.style.transition = 'transform 0.12s cubic-bezier(0.2, 0, 0, 1)';
                        }

                        // 2.5D Holographic Heirloom Foil Specular Shimmer
                        const glintX = ((relX + 1) * 50).toFixed(1);
                        const glintY = ((relY + 1) * 50).toFixed(1);
                        card.style.setProperty('--vse-glint-pos', `${glintX}% ${glintY}%`);
                        card.classList.add('has-glint');
                    } else {
                        this.resetCardTransform(card);
                    }
                });
            }, { passive: true });
        }
    }

    scheduleRenderLoop() {
        if (this.rafId) return;

        const render = () => {
            // Spring damping / low-pass filter for silky 120fps motion without jitter
            const ease = 0.08;
            this.orientation.gamma += (this.targetOrientation.gamma - this.orientation.gamma) * ease;
            this.orientation.beta += (this.targetOrientation.beta - this.orientation.beta) * ease;

            const rotY = (this.orientation.gamma / 15) * 3.2; // Max 3.2deg
            const rotX = (-this.orientation.beta / 15) * 3.2; // Max 3.2deg
            const transX = (this.orientation.gamma / 15) * 4.5; // Max 4.5px
            const transY = (this.orientation.beta / 15) * 4.5; // Max 4.5px

            if (this.activeCards.size > 0 && (Math.abs(rotY) > 0.05 || Math.abs(rotX) > 0.05)) {
                this.activeCards.forEach((card) => {
                    const img = card.querySelector('.post-media-first-img');
                    if (img) {
                        img.style.transform = `perspective(800px) rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg) translate3d(${transX.toFixed(1)}px, ${transY.toFixed(1)}px, 5px) scale(1.015)`;
                        img.style.transformOrigin = 'center center';
                        img.style.willChange = 'transform';
                    }

                    // Mobile gyroscopic holographic foil sheen update
                    const glintX = Math.max(0, Math.min(100, 50 + (this.orientation.gamma / 30) * 50)).toFixed(1);
                    const glintY = Math.max(0, Math.min(100, 50 + (this.orientation.beta / 30) * 50)).toFixed(1);
                    card.style.setProperty('--vse-glint-pos', `${glintX}% ${glintY}%`);
                    card.classList.add('has-glint');
                });
            }

            this.rafId = requestAnimationFrame(render);
        };

        this.rafId = requestAnimationFrame(render);
    }

    resetCardTransform(card) {
        if (!card) return;
        const img = card.querySelector('.post-media-first-img') || card.querySelector('.living-canvas-img');
        if (img) {
            img.style.transform = '';
            img.style.willChange = 'auto';
            img.style.transition = 'transform 0.4s ease-out';
        }
        card.style.removeProperty('--vse-glint-pos');
        card.classList.remove('has-glint');
    }

    resetTransforms() {
        this.activeCards.forEach((card) => {
            card.classList.remove('is-living-active');
            this.resetCardTransform(card);
        });
        this.targetOrientation = { gamma: 0, beta: 0 };
        this.orientation = { gamma: 0, beta: 0 };
    }
}

/**
 * ATG Sovereign Saliency, Biometric Face-Region Focal & Super-HD Lens Engine
 * (ATG Pods 1, 3 & 4: Dr. Chen Wei, Tobi Adeleke, Maya Lindqvist, Callum Wright, Dami Ogunleye)
 */
class AtgMediaSupremacyEngine {
    constructor() {
        this.cache = new Map();
        this.probeCanvas = null;
        this.probeCtx = null;
        this.svgInjected = false;
    }

    ensureSuperHdSvgFilter() {
        if (this.svgInjected || typeof document === 'undefined' || !document.body) return;
        if (document.getElementById('atg-super-hd-lens')) {
            this.svgInjected = true;
            return;
        }
        try {
            const wrapper = document.createElement('div');
            wrapper.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden;pointer-events:none;';
            wrapper.setAttribute('aria-hidden', 'true');
            wrapper.innerHTML = `
                <svg width="0" height="0" aria-hidden="true">
                  <defs>
                    <filter id="atg-super-hd-lens" color-interpolation-filters="sRGB">
                      <feConvolveMatrix order="3 3" preserveAlpha="true" kernelMatrix="0 -0.065 0 -0.065 1.26 -0.065 0 -0.065 0"/>
                    </filter>
                  </defs>
                </svg>
            `;
            document.body.appendChild(wrapper);
            this.svgInjected = true;
        } catch (_) {}
    }

    analyzeImage(imgEl) {
        this.ensureSuperHdSvgFilter();

        if (!imgEl || !imgEl.naturalWidth || !imgEl.naturalHeight) {
            return {
                aspectRatio: '1 / 1',
                numericRatio: 1.0,
                focalPosition: '50% 35%'
            };
        }

        const srcKey = imgEl.currentSrc || imgEl.src || '';
        if (srcKey && this.cache.has(srcKey)) {
            return this.cache.get(srcKey);
        }

        const w = imgEl.naturalWidth;
        const h = imgEl.naturalHeight;
        const rawRatio = w / h;
        // Clamp strictly to Instagram feed standard bounds: 4:5 (0.80) portrait to 1.91:1 landscape
        const clampedRatio = Math.min(1.91, Math.max(0.80, rawRatio));
        const aspectRatio = clampedRatio.toFixed(4) + ' / 1';

        // Default biometric rule-of-thirds fallback (protects portrait heads from top-clipping)
        let focalX = 50;
        let focalY = rawRatio < 0.92 ? 28 : (rawRatio > 1.2 ? 42 : 36);

        try {
            if (typeof document !== 'undefined') {
                const S = 48;
                if (!this.probeCanvas) {
                    this.probeCanvas = document.createElement('canvas');
                    this.probeCanvas.width = S;
                    this.probeCanvas.height = S;
                    this.probeCtx = this.probeCanvas.getContext('2d', { willReadFrequently: true });
                }

                if (this.probeCtx) {
                    this.probeCtx.clearRect(0, 0, S, S);
                    this.probeCtx.drawImage(imgEl, 0, 0, S, S);
                    const data = this.probeCtx.getImageData(0, 0, S, S).data;

                    let sumW = 0;
                    let sumX = 0;
                    let sumY = 0;

                    for (let y = 2; y < S - 2; y++) {
                        const normY = y / S;
                        // Suppress top 8% ceiling lights/glare and bottom 15% torso/floor
                        if (normY < 0.08 || normY > 0.85) continue;

                        // Upper-third biometric attention prior for human faces
                        const verticalPrior = (normY >= 0.16 && normY <= 0.56) ? 1.65 : 0.75;

                        for (let x = 2; x < S - 2; x++) {
                            const idx = (y * S + x) * 4;
                            const r = data[idx];
                            const g = data[idx + 1];
                            const b = data[idx + 2];

                            // Skip blown-out ceiling spotlights or pitch-black borders
                            const lum = 0.299 * r + 0.587 * g + 0.114 * b;
                            if (lum > 242 || lum < 12) continue;

                            // Sobel local gradient energy (eyes, glasses frames, smiles, bowties)
                            const idxRight = (y * S + (x + 1)) * 4;
                            const idxDown = ((y + 1) * S + x) * 4;
                            const gradX = Math.abs(r - data[idxRight]) + Math.abs(g - data[idxRight + 1]);
                            const gradY = Math.abs(r - data[idxDown]) + Math.abs(g - data[idxDown + 1]);
                            const edgeEnergy = (gradX + gradY) * 0.5;

                            // Inclusive human skin-tone chrominance detector (covers all melanin tones)
                            const minGB = Math.min(g, b);
                            const isSkinChrominance = (r > 55 && g > 32 && b > 18 && r > g && g >= b * 0.85 && (r - minGB) > 12 && (r - g) < 95);
                            const skinBoost = isSkinChrominance ? 2.4 : 1.0;

                            const weight = (edgeEnergy + 8) * skinBoost * verticalPrior;
                            sumW += weight;
                            sumX += (x / S) * 100 * weight;
                            sumY += normY * 100 * weight;
                        }
                    }

                    if (sumW > 0) {
                        const detectedX = sumX / sumW;
                        const detectedY = sumY / sumW;
                        focalX = Math.round(Math.min(75, Math.max(25, detectedX)));
                        // For portraits, keep vertical focal point anchored in [20%, 42%] so heads never clip
                        const maxY = rawRatio < 0.95 ? 42 : 58;
                        focalY = Math.round(Math.min(maxY, Math.max(18, detectedY)));
                    }
                }
            }
        } catch (_) {
            // Cross-origin taint or canvas unavailable: keep biometric rule-of-thirds fallback
        }

        const result = {
            aspectRatio,
            numericRatio: clampedRatio,
            focalPosition: `${focalX}% ${focalY}%`
        };

        if (srcKey) {
            if (this.cache.size > 200) {
                const firstKey = this.cache.keys().next().value;
                this.cache.delete(firstKey);
            }
            this.cache.set(srcKey, result);
        }

        return result;
    }
}

export const atgMediaSupremacy = new AtgMediaSupremacyEngine();
if (typeof window !== 'undefined') {
    window.AtgMediaSupremacy = atgMediaSupremacy;
}

export const vseRuntime = new VisualSupremacyRuntime();
export function initVisualSupremacy() {
    atgMediaSupremacy.ensureSuperHdSvgFilter();
    vseRuntime.init();
}
