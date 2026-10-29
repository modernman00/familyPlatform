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
                } else {
                    this.activeCards.delete(entry.target);
                    // Reset transform when scrolling out of viewport to save GPU compositing
                    this.resetCardTransform(entry.target);
                }
            });
        }, { threshold: 0.1 });

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
        const img = card.querySelector('.post-media-first-img');
        if (img) {
            img.style.transform = '';
            img.style.willChange = 'auto';
            img.style.transition = 'transform 0.3s ease-out';
        }
        card.style.removeProperty('--vse-glint-pos');
        card.classList.remove('has-glint');
    }

    resetTransforms() {
        this.activeCards.forEach((card) => this.resetCardTransform(card));
        this.targetOrientation = { gamma: 0, beta: 0 };
        this.orientation = { gamma: 0, beta: 0 };
    }
}

export const vseRuntime = new VisualSupremacyRuntime();
export function initVisualSupremacy() {
    vseRuntime.init();
}
