/**
 * ATG Sovereign 3D Emoji & Avatar Studio Engine
 * Developed by Advanced Technology Group (ATG Pods 3 & 4)
 * Lead Architects: Kaelen Vance & Maya Lindqvist
 * Zero-npm, 60fps micro-tilt physics, hardware-accelerated 3D transforms.
 */

(function (window, document) {
    'use strict';

    const AtgEmojiEngine = {
        // Active trait state
        state: {
            skin_tone: 'warm_chestnut',
            hairstyle: 'afro_crown',
            expression: 'warm_smile',
            accessory: 'none',
            backdrop: 'warm_sunset'
        },

        // 3D Gyroscope & drag rotation physics
        physics: {
            rotX: 0,
            rotY: 0,
            targetRotX: 0,
            targetRotY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            animFrame: null
        },

        init: function () {
            this.bindMicroTilt();
            this.initStudio();
            this.initArViewerEvents();
        },

        /**
         * Bind 60fps 3D micro-tilt physics to all 3D emojis across desktop and mobile.
         */
        bindMicroTilt: function () {
            document.querySelectorAll('.atg-3d-emoji, [data-atg-3d="true"]').forEach(el => {
                if (el.dataset.tiltBound) return;
                el.dataset.tiltBound = 'true';

                // Desktop mouse events
                el.addEventListener('mousemove', function (e) {
                    const rect = this.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    const dx = (e.clientX - cx) / (rect.width / 2 || 1);
                    const dy = (e.clientY - cy) / (rect.height / 2 || 1);

                    const tiltX = Math.max(-25, Math.min(25, dy * -20));
                    const tiltY = Math.max(-25, Math.min(25, dx * 20));

                    this.style.transform = `perspective(600px) rotateX(${tiltX.toFixed(1)}deg) rotateY(${tiltY.toFixed(1)}deg) scale3d(1.1, 1.1, 1.1) translateZ(10px)`;
                });

                el.addEventListener('mouseleave', function () {
                    this.style.transform = 'perspective(600px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1) translateZ(0)';
                });

                // Mobile touch events with spring-back physics
                el.addEventListener('touchstart', function (e) {
                    if (e.touches && e.touches.length === 1) {
                        this.classList.add('touch-active');
                        this.style.transition = 'transform 0.1s ease-out, filter 0.1s ease-out';
                        const touch = e.touches[0];
                        const rect = this.getBoundingClientRect();
                        const cx = rect.left + rect.width / 2;
                        const cy = rect.top + rect.height / 2;
                        const dx = (touch.clientX - cx) / (rect.width / 2 || 1);
                        const dy = (touch.clientY - cy) / (rect.height / 2 || 1);
                        const tiltX = Math.max(-22, Math.min(22, dy * -18));
                        const tiltY = Math.max(-22, Math.min(22, dx * 18));
                        this.style.transform = `perspective(600px) rotateX(${tiltX.toFixed(1)}deg) rotateY(${tiltY.toFixed(1)}deg) scale3d(1.12, 1.12, 1.12) translateZ(12px)`;
                    }
                }, { passive: true });

                el.addEventListener('touchmove', function (e) {
                    if (e.touches && e.touches.length === 1) {
                        const touch = e.touches[0];
                        const rect = this.getBoundingClientRect();
                        const cx = rect.left + rect.width / 2;
                        const cy = rect.top + rect.height / 2;
                        const dx = (touch.clientX - cx) / (rect.width / 2 || 1);
                        const dy = (touch.clientY - cy) / (rect.height / 2 || 1);
                        const tiltX = Math.max(-25, Math.min(25, dy * -20));
                        const tiltY = Math.max(-25, Math.min(25, dx * 20));
                        this.style.transform = `perspective(600px) rotateX(${tiltX.toFixed(1)}deg) rotateY(${tiltY.toFixed(1)}deg) scale3d(1.15, 1.15, 1.15) translateZ(12px)`;
                    }
                }, { passive: true });

                const resetTouch = function () {
                    this.classList.remove('touch-active');
                    this.style.transition = 'transform 0.35s cubic-bezier(0.25, 1, 0.5, 1), filter 0.35s ease';
                    this.style.transform = 'perspective(600px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1) translateZ(0)';
                };

                el.addEventListener('touchend', resetTouch, { passive: true });
                el.addEventListener('touchcancel', resetTouch, { passive: true });
            });
        },

        /**
         * Initialise Studio interactions if studio container is present.
         */
        initStudio: function () {
            const viewport = document.getElementById('atgAvatarViewport');
            if (!viewport || viewport.dataset.studioBound === 'true') return;
            viewport.dataset.studioBound = 'true';

            // Resume from the server-provided trait set (saved look or defaults)
            try {
                const initial = JSON.parse(viewport.dataset.traits || '{}');
                if (initial && typeof initial === 'object') {
                    Object.assign(this.state, initial);
                }
            } catch (e) { /* keep defaults */ }

            // Bind Studio top-level navigation tabs cleanly
            document.querySelectorAll('.atg-tab-btn[data-atg-tab]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const tabId = this.getAttribute('data-atg-tab');
                    if (!tabId) return;
                    document.querySelectorAll('.atg-tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.atg-tab-content').forEach(c => { c.style.display = 'none'; });
                    this.classList.add('active');
                    const target = document.getElementById(tabId);
                    if (target) target.style.display = 'block';
                });
            });

            this.bindViewportPhysics(viewport);
            this.bindTraitSelectors();
            this.bindStudioActions();
            this.bindEmojiCardClicks();
            this.bindSectionChips();
            this.bindEmojiToneChips();
            this.initMotionRig(viewport);
            this.initPhotoToAvatarStudio();
        },

        /**
         * Interactive drag-to-rotate with spring momentum on the live 3D avatar viewport.
         */
        bindViewportPhysics: function (viewport) {
            const self = this;
            function updateRotation() {
                const svgEl = viewport.querySelector('.atg-avatar-svg');
                // Spring restitution towards target
                self.physics.rotX += (self.physics.targetRotX - self.physics.rotX) * 0.15;
                self.physics.rotY += (self.physics.targetRotY - self.physics.rotY) * 0.15;

                if (svgEl) {
                    svgEl.style.transform = `perspective(800px) rotateX(${self.physics.rotX.toFixed(2)}deg) rotateY(${self.physics.rotY.toFixed(2)}deg) scale3d(1.04, 1.04, 1.04)`;
                }

                if (Math.abs(self.physics.targetRotX - self.physics.rotX) > 0.05 ||
                    Math.abs(self.physics.targetRotY - self.physics.rotY) > 0.05 ||
                    self.physics.isDragging) {
                    self.physics.animFrame = requestAnimationFrame(updateRotation);
                } else {
                    self.physics.animFrame = null;
                }
            }

            function startDrag(clientX, clientY) {
                self.physics.isDragging = true;
                self.physics.moved = 0;
                self.physics.startX = clientX;
                self.physics.startY = clientY;
                if (!self.physics.animFrame) {
                    self.physics.animFrame = requestAnimationFrame(updateRotation);
                }
            }

            function moveDrag(clientX, clientY) {
                if (!self.physics.isDragging) return;
                const deltaX = clientX - self.physics.startX;
                const deltaY = clientY - self.physics.startY;
                self.physics.moved = Math.max(self.physics.moved || 0, Math.abs(deltaX) + Math.abs(deltaY));

                self.physics.targetRotY = Math.max(-45, Math.min(45, deltaX * 0.4));
                self.physics.targetRotX = Math.max(-35, Math.min(35, -deltaY * 0.4));
            }

            function stopDrag() {
                if (!self.physics.isDragging) return;
                self.physics.isDragging = false;
                // A press without travel is a tap → playful reaction
                if ((self.physics.moved || 0) < 6) {
                    self.playReaction(self.randomTapReaction());
                }
                // Gently spring back to resting front-facing position
                self.physics.targetRotX = 0;
                self.physics.targetRotY = 0;
            }

            // Mouse events
            viewport.addEventListener('mousedown', e => {
                e.preventDefault();
                startDrag(e.clientX, e.clientY);
            });
            window.addEventListener('mousemove', e => moveDrag(e.clientX, e.clientY));
            window.addEventListener('mouseup', stopDrag);

            // Touch events
            viewport.addEventListener('touchstart', e => {
                if (e.touches.length === 1) {
                    startDrag(e.touches[0].clientX, e.touches[0].clientY);
                }
            }, { passive: true });
            window.addEventListener('touchmove', e => {
                if (e.touches.length === 1) {
                    moveDrag(e.touches[0].clientX, e.touches[0].clientY);
                }
            }, { passive: true });
            window.addEventListener('touchend', stopDrag);

            viewport.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    self.playReaction(self.randomTapReaction());
                }
            });
        },

        /**
         * Bind trait pills in the studio.
         */
        bindTraitSelectors: function () {
            const self = this;
            document.querySelectorAll('.atg-trait-pill').forEach(pill => {
                pill.addEventListener('click', function () {
                    const group = this.dataset.traitGroup;
                    const value = this.dataset.traitValue;

                    if (!group || !value) return;

                    self.setTrait(group, value);
                    self.refreshAvatarPreview();
                });
            });
        },

        /**
         * Update trait state + pill UI without re-rendering.
         */
        setTrait: function (group, value) {
            document.querySelectorAll(`.atg-trait-pill[data-trait-group="${group}"]`).forEach(p => {
                const on = p.dataset.traitValue === value;
                p.classList.toggle('active', on);
                p.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            this.state[group] = value;
        },

        /**
         * Re-render avatar preview in real time.
         */
        refreshAvatarPreview: function () {
            const viewport = document.getElementById('atgAvatarViewport');
            if (!viewport) return;

            // Cancel stale renders when traits change rapidly
            if (this._renderAbort) this._renderAbort.abort();
            this._renderAbort = typeof AbortController !== 'undefined' ? new AbortController() : null;

            fetch('/api/emoji/render-avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(Object.assign({}, this.state, { size: 256, animated: true })),
                signal: this._renderAbort ? this._renderAbort.signal : undefined
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.svg) {
                    const oldSvg = viewport.querySelector('.atg-avatar-svg');
                    const holder = document.createElement('div');
                    holder.innerHTML = data.svg;
                    const newSvg = holder.querySelector('svg');
                    if (!newSvg) return;
                    if (oldSvg) {
                        oldSvg.replaceWith(newSvg);
                    } else {
                        viewport.prepend(newSvg);
                    }
                    newSvg.style.transform = `perspective(800px) rotateX(${this.physics.rotX.toFixed(2)}deg) rotateY(${this.physics.rotY.toFixed(2)}deg) scale3d(1.04, 1.04, 1.04)`;
                    this.attachRig();
                }
            })
            .catch(err => {
                if (err && err.name === 'AbortError') return;
                console.error('Failed to update avatar preview:', err);
            });
        },

        /**
         * Bind studio action buttons (Save Avatar, Randomise, etc.).
         */
        bindStudioActions: function () {
            const self = this;
            const saveBtn = document.getElementById('atgSaveAvatarBtn');
            const randomBtn = document.getElementById('atgRandomAvatarBtn');

            if (randomBtn) {
                randomBtn.addEventListener('click', function () {
                    self.randomiseTraits();
                });
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    self.saveProfileAvatar(this);
                });
            }
        },

        /**
         * Randomise traits for playful discovery.
         */
        randomiseTraits: function () {
            const groups = Array.from(new Set(Array.from(document.querySelectorAll('.atg-trait-pill')).map(p => p.dataset.traitGroup)));
            // Keep "Surprise Me" tasteful: most looks are unadorned, beards/eyewear sometimes.
            const sparse = { facial_hair: 0.35, eyewear: 0.3, accessory: 0.45, skin_detail: 0.4, hair_color: 0.45 };
            groups.forEach(group => {
                const pills = Array.from(document.querySelectorAll(`.atg-trait-pill[data-trait-group="${group}"]`));
                if (!pills.length) return;
                let pick = pills[Math.floor(Math.random() * pills.length)];
                if (sparse[group] !== undefined && Math.random() > sparse[group]) {
                    pick = pills[0];
                }
                this.setTrait(group, pick.dataset.traitValue);
            });
            this.refreshAvatarPreview();
            this.playReaction('wow');
        },

        /**
         * Save current procedural 3D avatar as user's profile photo.
         */
        saveProfileAvatar: function (btn) {
            const viewport = document.getElementById('atgAvatarViewport');
            const liveSvg = viewport ? viewport.querySelector('.atg-avatar-svg') : null;
            if (!liveSvg) return;
            // Snapshot a neutral pose (no 3D tilt / gaze offsets / talk mouth)
            const svgEl = liveSvg.cloneNode(true);
            svgEl.removeAttribute('style');
            svgEl.querySelectorAll('[transform]').forEach(el => {
                if (el.matches('.atg-av-gaze, .atg-av-headpose')) el.setAttribute('transform', 'translate(0 0)');
            });
            svgEl.querySelectorAll('.atg-av-talk').forEach(el => el.setAttribute('opacity', '0'));
            // Head-and-shoulders crop for the profile photo (computed server-side per hairstyle)
            svgEl.setAttribute('viewBox', svgEl.getAttribute('data-portrait-viewbox') || '34 30 188 188');
            svgEl.querySelectorAll('.atg-av-mouth').forEach(el => el.removeAttribute('opacity'));

            // Start tactile spinner
            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.start(btn);
            }

            const stopSpinner = () => {
                if (window.ScoreTenantSpinner && btn) window.ScoreTenantSpinner.stop(btn);
                if (window.FamilyPlatformSpinner && btn) window.FamilyPlatformSpinner.stop(btn);
            };

            const submitAvatarPayload = pngDataUrl => {
                const csrfToken = document.querySelector('input[name="token"]')?.value ||
                                  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch('/api/emoji/save-avatar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(Object.assign({}, this.state, {
                        token: csrfToken,
                        csrf_token: csrfToken,
                        mode: 'procedural',
                        animated: !!(document.getElementById('atgAnimatedSave') || {}).checked,
                        png_base64: pngDataUrl || null
                    }))
                })
                .then(res => res.json())
                .then(data => {
                    stopSpinner();

                    if (data.success) {
                        const newUrl = data.url + '?v=' + Date.now();
                        document.querySelectorAll('.avatar.profileImg, #profilePreview, #pfViewerThumb, #pfViewerMainImg').forEach(imgEl => {
                            imgEl.src = newUrl;
                        });
                        const useBtn = document.getElementById('atgUseCurrentProfilePhotoBtn');
                        if (useBtn && (data.png_url || data.url)) {
                            useBtn.dataset.profilePhoto = data.png_url || data.url;
                        }
                        if (this.photoRig && this.photoRig.canvas && (data.png_url || data.url)) {
                            this.photoRig.canvas.dataset.profilePhoto = data.png_url || data.url;
                        }

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: '3D Avatar Live!',
                                text: data.message || 'Your procedural 3D avatar is now your official profile photo.',
                                confirmButtonColor: '#a31621'
                            });
                        } else {
                            alert(data.message || 'Avatar saved successfully!');
                        }
                    } else {
                        throw new Error(data.error || 'Failed to update profile photo.');
                    }
                })
                .catch(err => {
                    stopSpinner();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: err.message,
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert('Error: ' + err.message);
                    }
                });
            };

            // Convert SVG to high-res PNG on offscreen canvas using a CSP-safe data: URI (never blob:)
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');

            const svgString = new XMLSerializer().serializeToString(svgEl);
            const dataUri = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgString);
            const img = new Image();

            img.onload = () => {
                try {
                    ctx.drawImage(img, 0, 0, 512, 512);
                    submitAvatarPayload(canvas.toDataURL('image/png'));
                } catch (e) {
                    submitAvatarPayload(null);
                }
            };

            img.onerror = () => {
                // Fallback: save server-side SVG directly even if offscreen image decode fails
                submitAvatarPayload(null);
            };

            img.src = dataUri;
        },

        /**
         * Handle clicking on 3D emojis in the catalog to copy or insert.
         */
        bindEmojiCardClicks: function () {
            document.querySelectorAll('.atg-emoji-card').forEach(card => {
                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
                });
                card.addEventListener('click', function () {
                    const emojiId = this.dataset.emojiId;
                    this.classList.remove('atg-emoji-pop');
                    void this.offsetWidth;
                    this.classList.add('atg-emoji-pop');
                    if (navigator.vibrate && navigator.userActivation && navigator.userActivation.hasBeenActive) {
                try { navigator.vibrate(12); } catch (_) { /* unsupported */ }
            }
                    const emojiTitle = this.dataset.emojiTitle || emojiId;

                    // Copy shortcode to clipboard
                    const shortcode = `:${emojiId}:`;
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(shortcode).then(() => {
                            if (window.Swal) {
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3500,
                                    timerProgressBar: true
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: `Copied ${emojiTitle}`,
                                    text: 'Paste it into any post or comment — it comes alive in the feed.'
                                });
                            }
                        });
                    }
                });
            });
        },

        /* -------------------------------------------------------------------------
         * Customiser sections & emoji skin tones
         * ------------------------------------------------------------------------- */
        bindSectionChips: function () {
            const chips = document.querySelectorAll('.atg-section-chip');
            chips.forEach(chip => {
                chip.addEventListener('click', () => {
                    const key = chip.dataset.atgSection;
                    chips.forEach(c => {
                        const on = c === chip;
                        c.classList.toggle('active', on);
                        c.setAttribute('aria-pressed', on ? 'true' : 'false');
                    });
                    document.querySelectorAll('.atg-section-panel').forEach(panel => {
                        panel.hidden = panel.dataset.atgPanel !== key;
                    });
                });
            });
        },

        _toneCache: {},

        bindEmojiToneChips: function () {
            const self = this;
            const chips = document.querySelectorAll('.atg-tone-chip');
            chips.forEach(chip => {
                chip.addEventListener('click', () => {
                    const tone = chip.dataset.emojiTone || '';
                    chips.forEach(c => {
                        const on = c === chip;
                        c.classList.toggle('active', on);
                        c.setAttribute('aria-pressed', on ? 'true' : 'false');
                    });
                    document.querySelectorAll('.atg-emoji-card[data-toneable="1"]').forEach(card => {
                        const base = card.dataset.emojiBase;
                        const id = tone ? base.replace(/_3d$/, `_${tone}_3d`) : base;
                        if (!/^[a-z0-9_]+_3d$/.test(id)) return;
                        card.dataset.emojiId = id;
                        card.title = `Click to copy :${id}:`;
                        const holder = card.querySelector('.atg-emoji-container');
                        const apply = svgText => {
                            if (!holder || card.dataset.emojiId !== id) return;
                            holder.innerHTML = svgText;
                            card.classList.remove('atg-emoji-pop');
                            void card.offsetWidth;
                            card.classList.add('atg-emoji-pop');
                            self.bindMicroTilt();
                        };
                        if (self._toneCache[id]) {
                            apply(self._toneCache[id]);
                            return;
                        }
                        fetch(`/api/emoji/svg/${id}?size=96`)
                            .then(r => (r.ok ? r.text() : Promise.reject(new Error('HTTP ' + r.status))))
                            .then(text => {
                                if (text.trim().startsWith('<svg')) {
                                    self._toneCache[id] = text;
                                    apply(text);
                                }
                            })
                            .catch(err => console.error('Emoji tone render failed:', err));
                    });
                });
            });
        },

        /* -------------------------------------------------------------------------
         * Motion-Response Avatar Rig
         * Gaze + head follow the pointer (or phone tilt), taps trigger reactions,
         * Talk mode lip-syncs the mouth to the microphone level (processed locally).
         * ------------------------------------------------------------------------- */
        motion: {
            viewport: null,
            rig: null,
            gx: 0, gy: 0, tgx: 0, tgy: 0,
            lastInput: 0,
            nextWander: 0,
            raf: null,
            reduce: false,
            sensors: false,
            baseBeta: null,
            lastShake: 0,
            lastAcc: null,
            talking: false,
            talkLevel: 0,
            audio: null,
            reactTimer: null
        },

        initMotionRig: function (viewport) {
            const self = this;
            const m = this.motion;
            m.viewport = viewport;
            m.reduce = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
            this.attachRig();

            window.addEventListener('pointermove', e => self.aimAt(e.clientX, e.clientY), { passive: true });

            document.querySelectorAll('.atg-react-btn').forEach(btn => {
                btn.addEventListener('click', () => self.playReaction(btn.dataset.reaction));
            });

            const talkBtn = document.getElementById('atgTalkBtn');
            if (talkBtn) talkBtn.addEventListener('click', () => self.toggleTalk(talkBtn));

            const isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
            const sensorBtn = document.getElementById('atgSensorBtn');
            const hint = document.getElementById('atgStageHint');
            if (isTouch && hint) hint.textContent = '👆 Tap it to react · tilt your phone to steer its gaze';
            if (sensorBtn && isTouch && 'DeviceOrientationEvent' in window) {
                sensorBtn.hidden = false;
                sensorBtn.addEventListener('click', () => self.toggleSensors(sensorBtn));
                // Android/Chromium needs no permission prompt → switch on immediately.
                if (typeof DeviceOrientationEvent.requestPermission !== 'function') {
                    self.toggleSensors(sensorBtn);
                }
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    self.stopTalk();
                    if (m.raf) { cancelAnimationFrame(m.raf); m.raf = null; }
                } else if (!m.raf) {
                    m.raf = requestAnimationFrame(t => self.motionFrame(t));
                }
            });
            window.addEventListener('pagehide', () => self.stopTalk());

            m.raf = requestAnimationFrame(t => self.motionFrame(t));
        },

        attachRig: function () {
            const m = this.motion;
            const svg = m.viewport ? m.viewport.querySelector('.atg-avatar-svg') : null;
            if (!svg) { m.rig = null; return; }
            svg.classList.add('atg-js-driven');
            m.rig = {
                svg: svg,
                gaze: Array.from(svg.querySelectorAll('.atg-av-gaze')),
                head: svg.querySelector('.atg-av-headpose'),
                mouth: svg.querySelector('.atg-av-mouth'),
                talk: svg.querySelector('.atg-av-talk'),
                last: ''
            };
        },

        aimAt: function (x, y) {
            const m = this.motion;
            if (!m.viewport) return;
            const r = m.viewport.getBoundingClientRect();
            const reach = Math.max(260, r.width * 1.4);
            m.tgx = Math.max(-1, Math.min(1, (x - (r.left + r.width / 2)) / reach));
            m.tgy = Math.max(-1, Math.min(1, (y - (r.top + r.height * 0.45)) / reach));
            m.lastInput = performance.now();
        },

        motionFrame: function (now) {
            const self = this;
            const m = this.motion;
            m.raf = requestAnimationFrame(t => self.motionFrame(t));
            const rig = m.rig;
            if (!rig || !rig.svg.isConnected) {
                this.attachRig();
                return;
            }

            // Idle wander: glance around after a few seconds without input
            if (!m.reduce && !m.sensors && now - m.lastInput > 3500 && now > m.nextWander) {
                const lookAway = Math.random() < 0.6;
                m.tgx = lookAway ? (Math.random() * 1.2 - 0.6) : 0;
                m.tgy = lookAway ? (Math.random() * 0.6 - 0.3) : 0;
                m.nextWander = now + 1800 + Math.random() * 2600;
            }

            const ease = m.reduce ? 0.3 : 0.12;
            m.gx += (m.tgx - m.gx) * ease;
            m.gy += (m.tgy - m.gy) * ease;

            // Talk level (mic RMS → mouth openness)
            let level = 0;
            if (m.talking && m.audio) {
                const a = m.audio;
                a.analyser.getByteTimeDomainData(a.buf);
                let sum = 0;
                for (let i = 0; i < a.buf.length; i++) {
                    const v = (a.buf[i] - 128) / 128;
                    sum += v * v;
                }
                const rms = Math.sqrt(sum / a.buf.length);
                level = Math.max(0, Math.min(1, (rms - 0.015) * 7));
            }
            m.talkLevel += (level - m.talkLevel) * (level > m.talkLevel ? 0.5 : 0.25);

            const gx = m.gx.toFixed(2);
            const gy = m.gy.toFixed(2);
            const tl = m.talkLevel.toFixed(2);
            const key = gx + '|' + gy + '|' + tl;
            if (key === rig.last) return;
            rig.last = key;

            rig.gaze.forEach(g => g.setAttribute('transform', `translate(${(m.gx * 3.4).toFixed(2)} ${(m.gy * 2.6).toFixed(2)})`));
            if (rig.head) {
                rig.head.setAttribute('transform', `translate(${(m.gx * 4).toFixed(2)} ${(m.gy * 3).toFixed(2)}) rotate(${(m.gx * 5).toFixed(2)} 128 176)`);
            }
            if (rig.talk && rig.mouth) {
                const open = m.talkLevel > 0.06;
                rig.talk.setAttribute('opacity', open ? '1' : '0');
                rig.mouth.setAttribute('opacity', open ? '0' : '1');
                const t = rig.talk.getAttribute('transform') || '';
                const base = t.replace(/\s*scale\([^)]*\)/, '');
                rig.talk.setAttribute('transform', `${base} scale(${(0.8 + m.talkLevel * 0.25).toFixed(2)} ${(0.25 + m.talkLevel * 0.9).toFixed(2)})`);
            }
        },

        randomTapReaction: function () {
            const pool = ['hello', 'laugh', 'wow', 'love', 'nod', 'dance', 'point', 'clap'];
            return pool[Math.floor(Math.random() * pool.length)];
        },

        playReaction: function (name) {
            const m = this.motion;
            const allowed = { hello: ['👋', '✨'], laugh: ['😂', '🤣'], wow: ['😮', '⭐'], love: ['❤️', '💖', '💕'], nod: ['👍', '✅'], dance: ['🎶', '💃', '🕺'], point: ['🫵', '👉', '✨'], clap: ['👏', '👏', '🎉'] };
            if (!allowed[name] || !m.viewport) return;
            const svg = m.viewport.querySelector('.atg-avatar-svg');
            if (svg) {
                Object.keys(allowed).forEach(k => svg.classList.remove('atg-react-' + k));
                void svg.getBoundingClientRect();
                svg.classList.add('atg-react-' + name);
                clearTimeout(m.reactTimer);
                m.reactTimer = setTimeout(() => svg.classList.remove('atg-react-' + name), name === 'dance' ? 2200 : 1300);
            }
            if (navigator.vibrate) navigator.vibrate(name === 'laugh' ? [14, 40, 14] : (name === 'clap' ? [10, 60, 10, 60, 10] : 12));
            if (m.reduce) return;

            const layer = m.viewport.querySelector('.atg-react-layer');
            if (!layer) return;
            const glyphs = allowed[name];
            for (let i = 0; i < 7; i++) {
                const p = document.createElement('span');
                p.className = 'atg-react-particle';
                p.textContent = glyphs[i % glyphs.length];
                p.style.left = (18 + Math.random() * 64) + '%';
                p.style.setProperty('--drift', (Math.random() * 60 - 30).toFixed(0) + 'px');
                p.style.animationDelay = (i * 70) + 'ms';
                p.style.fontSize = (18 + Math.random() * 14).toFixed(0) + 'px';
                layer.appendChild(p);
                setTimeout(() => p.remove(), 1900 + i * 70);
            }
        },

        toggleTalk: async function (btn) {
            const m = this.motion;
            if (m.talking) {
                this.stopTalk();
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.motionToast('Talk mode needs a browser with microphone support.', 'info');
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
                const Ctx = window.AudioContext || window.webkitAudioContext;
                const ctx = new Ctx();
                const src = ctx.createMediaStreamSource(stream);
                const analyser = ctx.createAnalyser();
                analyser.fftSize = 512;
                src.connect(analyser);
                m.audio = { stream: stream, ctx: ctx, analyser: analyser, buf: new Uint8Array(analyser.fftSize), btn: btn };
                m.talking = true;
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
                this.motionToast('Talk mode on — say hello to your avatar!', 'success');
            } catch (err) {
                this.motionToast('Microphone access was blocked, so Talk mode is off.', 'warning');
            }
        },

        stopTalk: function () {
            const m = this.motion;
            if (!m.audio) { m.talking = false; return; }
            try { m.audio.stream.getTracks().forEach(t => t.stop()); } catch (e) { /* noop */ }
            try { m.audio.ctx.close(); } catch (e) { /* noop */ }
            if (m.audio.btn) {
                m.audio.btn.classList.remove('active');
                m.audio.btn.setAttribute('aria-pressed', 'false');
            }
            m.audio = null;
            m.talking = false;
        },

        toggleSensors: function (btn) {
            const self = this;
            const m = this.motion;
            if (m.sensors) {
                window.removeEventListener('deviceorientation', self._onOrient);
                window.removeEventListener('devicemotion', self._onMotion);
                m.sensors = false;
                btn.classList.remove('active');
                btn.setAttribute('aria-pressed', 'false');
                return;
            }
            self._onOrient = self._onOrient || function (e) {
                if (e.gamma === null || e.beta === null) return;
                if (m.baseBeta === null) m.baseBeta = e.beta;
                m.tgx = Math.max(-1, Math.min(1, e.gamma / 28));
                m.tgy = Math.max(-1, Math.min(1, (e.beta - m.baseBeta) / 28));
                m.lastInput = performance.now();
            };
            self._onMotion = self._onMotion || function (e) {
                const a = e.accelerationIncludingGravity;
                if (!a || a.x === null) return;
                const now = performance.now();
                if (m.lastAcc) {
                    const delta = Math.abs(a.x - m.lastAcc.x) + Math.abs(a.y - m.lastAcc.y) + Math.abs(a.z - m.lastAcc.z);
                    if (delta > 32 && now - m.lastShake > 1200) {
                        m.lastShake = now;
                        self.onShake();
                    }
                }
                m.lastAcc = { x: a.x, y: a.y, z: a.z };
            };
            const start = () => {
                window.addEventListener('deviceorientation', self._onOrient);
                window.addEventListener('devicemotion', self._onMotion);
                m.sensors = true;
                m.baseBeta = null;
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');
            };
            if (typeof DeviceOrientationEvent.requestPermission === 'function') {
                DeviceOrientationEvent.requestPermission()
                    .then(state => {
                        if (state === 'granted') {
                            if (typeof DeviceMotionEvent !== 'undefined' && typeof DeviceMotionEvent.requestPermission === 'function') {
                                DeviceMotionEvent.requestPermission().catch(() => {});
                            }
                            start();
                        } else {
                            self.motionToast('Motion access was declined.', 'warning');
                        }
                    })
                    .catch(() => self.motionToast('Motion sensors are unavailable on this device.', 'info'));
            } else {
                start();
            }
        },

        onShake: function () {
            const catalog = document.getElementById('catalog-tab');
            if (catalog && catalog.style.display !== 'none') {
                const cards = Array.from(document.querySelectorAll('.atg-emoji-card'));
                cards.sort(() => Math.random() - 0.5).slice(0, 6).forEach((card, i) => {
                    setTimeout(() => {
                        card.classList.remove('atg-emoji-pop');
                        void card.offsetWidth;
                        card.classList.add('atg-emoji-pop');
                    }, i * 90);
                });
                if (navigator.vibrate) navigator.vibrate([10, 30, 10]);
                return;
            }
            this.playReaction('laugh');
        },

        motionToast: function (text, icon) {
            if (window.Swal) {
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2600, timerProgressBar: true })
                    .fire({ icon: icon || 'info', title: text });
            } else {
                console.info('[AtgEmojiEngine]', text);
            }
        },

        /* -------------------------------------------------------------------------
         * Photo-to-Living Avatar Studio (OpenArt-Grade Hybrid Styliser & 2.5D Rig)
         * ------------------------------------------------------------------------- */
        photoRig: {
            canvas: null,
            ctx: null,
            beforeCanvas: null,
            beforeCtx: null,
            rawImg: null,
            stylisedHighRes: null,
            img: null,
            style: 'illustrated_comic',
            compareMode: true,
            splitPct: 50,
            neuralCache: {},
            photoUid: 1,
            isTransforming: false,
            cameraStream: null,
            tiltX: 0,
            tiltY: 0,
            targetX: 0,
            targetY: 0,
            blink: 0,
            blinking: false,
            nextBlink: 0,
            raf: null,
            sampleRgb: [138, 84, 52],
            hairRgb: [24, 18, 14],
            activeAction: 'smile',
            actionUntil: 0,
            choreoLoop: 'smile'
        },

        initPhotoToAvatarStudio: function () {
            const self = this;
            const canvas = document.getElementById('atgPhotoLivingCanvas');
            if (!canvas) return;

            const pr = this.photoRig;
            pr.canvas = canvas;
            pr.ctx = canvas.getContext('2d');
            pr.beforeCanvas = document.getElementById('atgPhotoBeforeCanvas');
            pr.beforeCtx = pr.beforeCanvas ? pr.beforeCanvas.getContext('2d') : null;
            pr.nextBlink = performance.now() + 2200;

            // Re-apply styliser if it finishes loading after engine init
            window.addEventListener('atg:styliser-ready', () => {
                if (pr.rawImg) {
                    self.applyInstantAvatarStyle(false);
                }
            });

            // Load default synthetic portrait immediately, then preload ?photo_url= (from Post Feed) or user's profile photo
            this.loadDefaultPhotoPortrait();
            const urlParams = new URLSearchParams(window.location.search || '');
            const queryPhotoUrl = urlParams.get('photo_url') || '';
            const isSafeLocalPhoto = /^\/(resources\/images|public\/img|storage)\/[A-Za-z0-9_./-]+\.(png|jpe?g|webp|gif|svg)(\?[A-Za-z0-9_=&-]+)?$/i.test(queryPhotoUrl)
                && !queryPhotoUrl.includes('..')
                && !queryPhotoUrl.includes('//');
            const initialProfileUrl = canvas.dataset.profilePhoto || '';
            if (isSafeLocalPhoto) {
                pr.autoNeuralOnLoad = true;
                this.loadPhotoFromUrl(queryPhotoUrl, true);
            } else if (initialProfileUrl && !initialProfileUrl.endsWith('avatarM.png') && !initialProfileUrl.endsWith('avatarF.png')) {
                this.loadPhotoFromUrl(initialProfileUrl, false);
            }

            canvas.addEventListener('pointermove', e => {
                const r = canvas.getBoundingClientRect();
                pr.targetX = ((e.clientX - r.left) / (r.width || 1) - 0.5) * 0.24;
                pr.targetY = ((e.clientY - r.top) / (r.height || 1) - 0.5) * 0.18;
            });
            canvas.addEventListener('pointerleave', () => {
                pr.targetX = 0;
                pr.targetY = 0;
            });

            // Tap on the Living Photo canvas to cycle fun expressions
            canvas.addEventListener('click', () => {
                const actions = ['smile', 'dance', 'wink', 'laugh', 'nod', 'love'];
                const next = actions[Math.floor(Math.random() * actions.length)];
                self.playPhotoReaction(next);
            });

            // Bind Before/After vs Living Motion view mode switcher & draggable split slider
            this.bindPhotoCompareSlider();

            // Bind Avatar Art Style Cards (6 styles + Original Photo reset chip)
            document.querySelectorAll('[data-avatar-style]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const chosen = this.getAttribute('data-avatar-style') || 'illustrated_comic';
                    self.selectPhotoAvatarStyle(chosen);
                });
            });

            // Bind Neural AI HD Redraw button
            const transformBtn = document.getElementById('atgTransformAvatarBtn');
            if (transformBtn) {
                transformBtn.addEventListener('click', function () {
                    self.generateNeuralAvatar(this);
                });
            }

            // Bind Fine-Tune progressive disclosure toggle & live sliders
            const ftToggle = document.getElementById('atgToggleFineTuneBtn');
            const ftDrawer = document.getElementById('atgFineTuneDrawer');
            const ftChevron = document.getElementById('atgFineTuneChevron');
            if (ftToggle && ftDrawer) {
                ftToggle.addEventListener('click', () => {
                    const isHidden = ftDrawer.hasAttribute('hidden');
                    if (isHidden) {
                        ftDrawer.removeAttribute('hidden');
                        ftToggle.setAttribute('aria-expanded', 'true');
                        if (ftChevron) ftChevron.textContent = '▴';
                    } else {
                        ftDrawer.setAttribute('hidden', '');
                        ftToggle.setAttribute('aria-expanded', 'false');
                        if (ftChevron) ftChevron.textContent = '▾';
                    }
                });
            }

            const styleSliders = [
                ['atgStyleStrength', 'atgStyleStrengthVal'],
                ['atgStyleLineWeight', 'atgStyleLineWeightVal'],
                ['atgStyleDetailLock', 'atgStyleDetailLockVal']
            ];
            styleSliders.forEach(([inputId, valId]) => {
                const el = document.getElementById(inputId);
                const valEl = document.getElementById(valId);
                if (el && valEl) {
                    el.addEventListener('input', () => {
                        valEl.textContent = Math.round(parseFloat(el.value || '0.75') * 100) + '%';
                        self.updateIdentityLockScore();
                        self.applyInstantAvatarStyle(true);
                    });
                }
            });

            const resetStyleBtn = document.getElementById('atgResetStyleControlsBtn');
            if (resetStyleBtn) {
                resetStyleBtn.addEventListener('click', () => {
                    const defs = {
                        atgStyleStrength: ['0.78', '78%'],
                        atgStyleLineWeight: ['0.68', '68%'],
                        atgStyleDetailLock: ['0.80', '80%']
                    };
                    Object.keys(defs).forEach(k => {
                        const input = document.getElementById(k);
                        const label = document.getElementById(k + 'Val');
                        if (input) input.value = defs[k][0];
                        if (label) label.textContent = defs[k][1];
                    });
                    self.updateIdentityLockScore();
                    self.applyInstantAvatarStyle(true);
                });
            }

            // Bind Download HD Avatar button
            const downloadBtn = document.getElementById('atgDownloadAvatarBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', () => self.downloadStylisedAvatar());
            }

            // Bind Share Branded Avatar button
            const shareBtn = document.getElementById('atgShareAvatarBtn');
            if (shareBtn) {
                shareBtn.addEventListener('click', () => self.shareStylisedAvatar());
            }

            // Bind Webcam Snapshot capture & drag-and-drop upload
            this.bindPhotoCameraAndDrop();

            // Bind Living Photo expression buttons (Smile, Dance, Wink, Laugh, Nod, Love)
            document.querySelectorAll('.atg-photo-react-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const action = this.dataset.photoAction || 'smile';
                    document.querySelectorAll('.atg-photo-react-btn').forEach(b => b.classList.remove('active', 'btn-dark', 'text-white'));
                    this.classList.add('active');
                    self.playPhotoReaction(action);
                });
            });

            // Bind choreography loop selector
            const choreoSelect = document.getElementById('atgPhotoChoreographySelect');
            if (choreoSelect) {
                choreoSelect.addEventListener('change', function () {
                    pr.choreoLoop = this.value || 'smile';
                    self.playPhotoReaction(pr.choreoLoop === 'idle' ? 'nod' : pr.choreoLoop);
                });
            }

            // Bind file upload
            const fileInput = document.getElementById('atgPhotoUploadInput');
            if (fileInput) {
                fileInput.addEventListener('change', e => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    self.handleUploadedPhotoFile(file);
                });
            }

            // Bind "Use Profile Photo" button
            const useProfileBtn = document.getElementById('atgUseCurrentProfilePhotoBtn');
            if (useProfileBtn) {
                useProfileBtn.addEventListener('click', function () {
                    const profileEl = document.querySelector('#leftColProfileAvatar, .avatar.profileImg');
                    const src = this.dataset.profilePhoto ||
                                canvas.dataset.profilePhoto ||
                                (profileEl ? profileEl.src : '') ||
                                '/resources/images/profile/avatarM.png';
                    self.loadPhotoFromUrl(src, true);
                });
            }

            const syncBtn = document.getElementById('atgSyncPhotoToAvatarBtn');
            if (syncBtn) {
                syncBtn.addEventListener('click', function () {
                    self.syncPhotoTo3dAvatar(this);
                });
            }

            ['atgSaveLivingPortraitBtn', 'atgSaveLivingPortraitQuickBtn'].forEach(id => {
                const saveLivingBtn = document.getElementById(id);
                if (saveLivingBtn) {
                    saveLivingBtn.addEventListener('click', function () {
                        self.saveLivingPhotoAvatar(this);
                    });
                }
            });

            ['atgPostLivingPortraitBtn', 'atgPostLivingPortraitQuickBtn'].forEach(id => {
                const postLivingBtn = document.getElementById(id);
                if (postLivingBtn) {
                    postLivingBtn.addEventListener('click', function () {
                        self.postLivingPhotoToFeed(this);
                    });
                }
            });

            if (!pr.raf) {
                const startT = performance.now();
                const loop = now => {
                    self.renderPhotoLivingFrame((now - startT) / 1000, now);
                    pr.raf = requestAnimationFrame(loop);
                };
                pr.raf = requestAnimationFrame(loop);
            }
        },

        /**
         * Handle a File object from input or drag-and-drop.
         */
        handleUploadedPhotoFile: function (file) {
            const self = this;
            const pr = this.photoRig;
            if (!file || !/^image\/(png|jpeg|jpg|webp)$/i.test(file.type || '')) {
                this.motionToast('Please choose a valid PNG, JPEG or WEBP portrait photo.', 'warning');
                return;
            }
            const reader = new FileReader();
            reader.onload = ev => {
                const rawImg = new Image();
                rawImg.onload = () => {
                    self.setNormalizedPhotoImage(rawImg);
                    self.extractPortraitPalette(pr.rawImg || pr.img);
                    self.setPhotoCompareMode(true);
                    self.setPhotoSplitPercent(50);
                    self.playPhotoReaction('smile');
                    self.syncPhotoTo3dAvatar();
                    const statusEl = document.getElementById('atgPhotoCanvasStatus');
                    if (statusEl) {
                        statusEl.textContent = '✨ Generating OpenArt-grade Neural HD Avatar...';
                    }
                    self.scheduleNeuralAvatar();
                };
                rawImg.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        },

        /**
         * Bind Before/After split-comparison drag slider, keyboard accessibility, and view mode switcher.
         */
        bindPhotoCompareSlider: function () {
            const self = this;
            const stage = document.getElementById('atgPhotoCompareStage');
            const handle = document.getElementById('atgCompareHandle');
            const compareBtn = document.getElementById('atgViewModeCompareBtn');
            const livingBtn = document.getElementById('atgViewModeLivingBtn');

            if (compareBtn) {
                compareBtn.addEventListener('click', () => {
                    self.setPhotoCompareMode(true);
                    if (self.photoRig.splitPct < 10 || self.photoRig.splitPct > 90) {
                        self.setPhotoSplitPercent(50);
                    }
                });
            }
            if (livingBtn) {
                livingBtn.addEventListener('click', () => {
                    self.setPhotoCompareMode(false);
                });
            }

            if (!stage || !handle) return;

            let dragging = false;
            const updateFromClientX = clientX => {
                const rect = stage.getBoundingClientRect();
                if (!rect.width) return;
                const pct = ((clientX - rect.left) / rect.width) * 100;
                self.setPhotoSplitPercent(pct);
            };

            handle.addEventListener('pointerdown', e => {
                e.preventDefault();
                e.stopPropagation();
                dragging = true;
                if (handle.setPointerCapture) {
                    try { handle.setPointerCapture(e.pointerId); } catch (_) { /* noop */ }
                }
            });

            window.addEventListener('pointermove', e => {
                if (!dragging) return;
                updateFromClientX(e.clientX);
            }, { passive: true });

            const stopDrag = () => { dragging = false; };
            window.addEventListener('pointerup', stopDrag);
            window.addEventListener('pointercancel', stopDrag);

            // Touch fallback for older mobile webviews
            handle.addEventListener('touchstart', e => {
                if (e.touches && e.touches.length === 1) {
                    dragging = true;
                }
            }, { passive: true });
            window.addEventListener('touchmove', e => {
                if (dragging && e.touches && e.touches.length === 1) {
                    updateFromClientX(e.touches[0].clientX);
                }
            }, { passive: true });
            window.addEventListener('touchend', stopDrag);

            // Keyboard accessibility on the slider handle
            handle.addEventListener('keydown', e => {
                const step = e.shiftKey ? 10 : 5;
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    self.setPhotoSplitPercent(self.photoRig.splitPct - step);
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    self.setPhotoSplitPercent(self.photoRig.splitPct + step);
                } else if (e.key === 'Home') {
                    e.preventDefault();
                    self.setPhotoSplitPercent(0);
                } else if (e.key === 'End') {
                    e.preventDefault();
                    self.setPhotoSplitPercent(100);
                }
            });
        },

        setPhotoCompareMode: function (enabled) {
            const pr = this.photoRig;
            pr.compareMode = !!enabled;
            const stage = document.getElementById('atgPhotoCompareStage');
            const compareBtn = document.getElementById('atgViewModeCompareBtn');
            const livingBtn = document.getElementById('atgViewModeLivingBtn');
            if (stage) {
                stage.classList.toggle('is-comparing', pr.compareMode);
            }
            if (compareBtn) {
                compareBtn.classList.toggle('active', pr.compareMode);
                compareBtn.setAttribute('aria-pressed', pr.compareMode ? 'true' : 'false');
            }
            if (livingBtn) {
                livingBtn.classList.toggle('active', !pr.compareMode);
                livingBtn.setAttribute('aria-pressed', !pr.compareMode ? 'true' : 'false');
            }
        },

        setPhotoSplitPercent: function (pct) {
            const pr = this.photoRig;
            const clamped = Math.max(0, Math.min(100, Math.round(pct)));
            pr.splitPct = clamped;
            const stage = document.getElementById('atgPhotoCompareStage');
            const handle = document.getElementById('atgCompareHandle');
            if (stage) {
                stage.style.setProperty('--atg-split', clamped + '%');
            }
            if (handle) {
                handle.setAttribute('aria-valuenow', String(clamped));
            }
        },

        /**
         * Select one of the 6 avatar styles (or 'original') and transform immediately.
         * Automatically triggers the OpenArt-grade Neural AI Studio pass when not cached.
         */
        selectPhotoAvatarStyle: function (styleKey) {
            const pr = this.photoRig;
            pr.style = styleKey || 'illustrated_comic';

            document.querySelectorAll('[data-avatar-style]').forEach(btn => {
                const active = btn.getAttribute('data-avatar-style') === pr.style;
                btn.classList.toggle('active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            if (pr.style === 'original') {
                this.setPhotoCompareMode(false);
            } else if (!pr.compareMode) {
                this.setPhotoCompareMode(true);
                if (pr.splitPct < 15 || pr.splitPct > 85) {
                    this.setPhotoSplitPercent(50);
                }
            }

            this.applyInstantAvatarStyle(false);

            const cacheKey = pr.photoUid + ':' + pr.style;
            if (pr.style !== 'original' && pr.rawImg && !pr.neuralCache[cacheKey]) {
                this.scheduleNeuralAvatar();
            }
        },

        scheduleNeuralAvatar: function () {
            const self = this;
            const pr = this.photoRig;
            if (pr.neuralDebounceTimer) {
                clearTimeout(pr.neuralDebounceTimer);
            }
            pr.neuralDebounceTimer = setTimeout(() => {
                const cacheKey = pr.photoUid + ':' + pr.style;
                if (pr.style !== 'original' && pr.rawImg && !pr.neuralCache[cacheKey]) {
                    self.generateNeuralAvatar(document.getElementById('atgTransformAvatarBtn'));
                }
            }, 220);
        },

        /**
         * Run the instant on-device GPU WebGL2 / Canvas2D styliser (or restore cached Neural HD image).
         */
        applyInstantAvatarStyle: function (ignoreNeuralCache) {
            const pr = this.photoRig;
            if (!pr.rawImg) return;

            // Update Before canvas with the untouched portrait
            if (pr.beforeCanvas && pr.beforeCtx) {
                const bw = pr.beforeCanvas.width || 960;
                const bh = pr.beforeCanvas.height || 1080;
                pr.beforeCtx.clearRect(0, 0, bw, bh);
                pr.beforeCtx.imageSmoothingEnabled = true;
                pr.beforeCtx.imageSmoothingQuality = 'high';
                pr.beforeCtx.drawImage(pr.rawImg, 0, 0, bw, bh);
            }

            if (pr.style === 'original') {
                pr.stylisedHighRes = pr.rawImg;
                this.syncDownscaledStageImage(pr.rawImg);
                this.updateEngineBadge('📷 Original Portrait');
                return;
            }

            const cacheKey = pr.photoUid + ':' + pr.style;
            if (!ignoreNeuralCache && pr.neuralCache[cacheKey]) {
                pr.stylisedHighRes = pr.neuralCache[cacheKey];
                this.syncDownscaledStageImage(pr.stylisedHighRes);
                this.updateEngineBadge('✨ ATG Neural Studio HD');
                return;
            }

            const strength = parseFloat((document.getElementById('atgStyleStrength') || {}).value || '0.78');
            const lineWeight = parseFloat((document.getElementById('atgStyleLineWeight') || {}).value || '0.68');
            const detailLock = parseFloat((document.getElementById('atgStyleDetailLock') || {}).value || '0.80');

            if (window.AtgPhotoAvatarStyliser && typeof window.AtgPhotoAvatarStyliser.stylise === 'function') {
                const res = window.AtgPhotoAvatarStyliser.stylise(pr.rawImg, pr.style, {
                    strength: strength,
                    lineWeight: lineWeight,
                    detailLock: detailLock
                });
                pr.stylisedHighRes = res.canvas;
                this.syncDownscaledStageImage(res.canvas);
                const isGpu = res.engine === 'webgl2_anisotropic_kuwahara';
                this.updateEngineBadge(isGpu ? `⚡ ATG GPU Styliser (${res.ms}ms)` : `🛡️ ATG Sovereign Styliser (${res.ms}ms)`);
            } else {
                pr.stylisedHighRes = pr.rawImg;
                this.syncDownscaledStageImage(pr.rawImg);
            }
        },

        syncDownscaledStageImage: function (sourceCanvas) {
            const pr = this.photoRig;
            const targetW = (pr.canvas && pr.canvas.width) || 960;
            const targetH = (pr.canvas && pr.canvas.height) || 1080;
            const stageImg = document.createElement('canvas');
            stageImg.width = targetW;
            stageImg.height = targetH;
            const sctx = stageImg.getContext('2d');
            sctx.imageSmoothingEnabled = true;
            sctx.imageSmoothingQuality = 'high';
            sctx.drawImage(sourceCanvas, 0, 0, targetW, targetH);
            pr.img = stageImg;
        },

        updateEngineBadge: function (label) {
            const badge = document.getElementById('atgAvatarEngineBadge');
            if (badge && label) {
                badge.textContent = label;
            }
        },

        /**
         * Call POST /api/emoji/transform-photo-avatar for a deep Neural AI HD Redraw
         * (with automatic Sovereign fallback).
         */
        generateNeuralAvatar: function (btn) {
            const self = this;
            const pr = this.photoRig;
            if (pr.isTransforming) return;
            if (!pr.rawImg) {
                this.loadDefaultPhotoPortrait();
            }

            if (pr.style === 'original') {
                this.selectPhotoAvatarStyle('illustrated_comic');
            }

            pr.isTransforming = true;
            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.start(btn);
            }

            const overlay = document.getElementById('atgTransformLoadingOverlay');
            const overlayText = document.getElementById('atgTransformLoadingText');
            const activeCard = document.querySelector(`.atg-style-card[data-avatar-style="${pr.style}"] .atg-style-name`);
            const styleTitle = activeCard ? activeCard.textContent : 'Illustrated Comic';
            if (overlayText) {
                overlayText.textContent = `Redrawing in ${styleTitle} style...`;
            }
            if (overlay) {
                overlay.removeAttribute('hidden');
            }

            const stopLoading = () => {
                pr.isTransforming = false;
                if (overlay) overlay.setAttribute('hidden', '');
                if (window.ScoreTenantSpinner && btn) window.ScoreTenantSpinner.stop(btn);
                if (window.FamilyPlatformSpinner && btn) window.FamilyPlatformSpinner.stop(btn);
            };

            let jpegDataUrl = '';
            try {
                jpegDataUrl = pr.rawImg.toDataURL('image/jpeg', 0.92);
            } catch (err) {
                this.loadDefaultPhotoPortrait();
                jpegDataUrl = pr.rawImg.toDataURL('image/jpeg', 0.92);
            }

            const csrfToken = document.querySelector('input[name="token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const strength = parseFloat((document.getElementById('atgStyleStrength') || {}).value || '0.78');
            const lineWeight = parseFloat((document.getElementById('atgStyleLineWeight') || {}).value || '0.68');
            const detailLock = parseFloat((document.getElementById('atgStyleDetailLock') || {}).value || '0.80');
            const targetStyle = pr.style;
            const targetPhotoUid = pr.photoUid;

            fetch('/api/emoji/transform-photo-avatar', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: csrfToken,
                    csrf_token: csrfToken,
                    image_base64: jpegDataUrl,
                    style: targetStyle,
                    strength: strength,
                    line_weight: lineWeight,
                    detail_lock: detailLock
                })
            })
            .then(res => res.json())
            .then(payload => {
                if (!payload || (!payload.success && !payload.ok) || !payload.data || !payload.data.avatar_data_uri) {
                    throw new Error((payload && (payload.error || payload.message)) || 'Could not transform portrait.');
                }

                const data = payload.data;
                const hasClientWebGL = window.AtgPhotoAvatarStyliser && window.AtgPhotoAvatarStyliser.isWebGL2Supported();

                // If server fell back to GD while client WebGL2 GPU shader is active, retain the higher-craft GPU Anisotropic Kuwahara + XDoG canvas
                if (data.fallback_used && hasClientWebGL) {
                    stopLoading();
                    self.applyInstantAvatarStyle(true);
                    self.setPhotoCompareMode(true);
                    self.setPhotoSplitPercent(50);
                    self.updateEngineBadge('⚡ ATG Sovereign GPU HD');
                    const statusEl = document.getElementById('atgPhotoCanvasStatus');
                    if (statusEl) {
                        statusEl.textContent = `✨ Transformed into ${data.style_label || styleTitle}! Drag ↔ to compare Before & After.`;
                    }
                    self.motionToast(`Transformed into ${data.style_label || styleTitle} avatar!`, 'success');
                    return;
                }

                const genImg = new Image();
                genImg.onload = () => {
                    stopLoading();
                    const hd = document.createElement('canvas');
                    hd.width = 480;
                    hd.height = 540;
                    hd.getContext('2d').drawImage(genImg, 0, 0, 480, 540);
                    const cacheKey = targetPhotoUid + ':' + targetStyle;
                    pr.neuralCache[cacheKey] = hd;
                    if (pr.photoUid === targetPhotoUid && pr.style === targetStyle) {
                        pr.stylisedHighRes = hd;
                        self.syncDownscaledStageImage(hd);
                        self.setPhotoCompareMode(true);
                        self.setPhotoSplitPercent(50);
                        self.updateEngineBadge(data.fallback_used ? '🛡️ ATG Sovereign Engine' : '✨ ATG Neural Studio HD');
                    }
                    const idBadge = document.getElementById('atgIdentityScoreBadge');
                    if (idBadge && data.identity_lock) {
                        idBadge.textContent = `Identity Lock: ${data.identity_lock}%`;
                    }
                    const statusEl = document.getElementById('atgPhotoCanvasStatus');
                    if (statusEl) {
                        statusEl.textContent = `✨ Neural HD ${data.style_label || styleTitle} ready! Drag ↔ to compare Before & After.`;
                    }
                    self.motionToast(`Neural HD ${data.style_label || styleTitle} avatar ready!`, 'success');
                };
                genImg.onerror = () => {
                    stopLoading();
                    self.applyInstantAvatarStyle(true);
                };
                genImg.src = data.avatar_data_uri;
            })
            .catch(err => {
                stopLoading();
                self.motionToast((err && err.message) ? err.message : 'Using on-device sovereign styliser.', 'info');
            });
        },

        /**
         * Bind webcam snapshot capture and drag-and-drop portrait upload on #atgPhotoStageCard.
         */
        bindPhotoCameraAndDrop: function () {
            const self = this;
            const pr = this.photoRig;
            const stageCard = document.getElementById('atgPhotoStageCard');
            const openCamBtn = document.getElementById('atgOpenCameraBtn');
            const camPanel = document.getElementById('atgCameraCapturePanel');
            const camVideo = document.getElementById('atgCameraVideo');
            const snapBtn = document.getElementById('atgCaptureSnapBtn');
            const cancelCamBtn = document.getElementById('atgCancelCameraBtn');

            const stopCam = () => {
                if (pr.cameraStream) {
                    try { pr.cameraStream.getTracks().forEach(t => t.stop()); } catch (_) { /* noop */ }
                    pr.cameraStream = null;
                }
                if (camVideo) camVideo.srcObject = null;
                if (camPanel) camPanel.setAttribute('hidden', '');
            };

            if (openCamBtn && camPanel && camVideo) {
                openCamBtn.addEventListener('click', async () => {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        self.motionToast('Camera access is not supported in this browser.', 'warning');
                        return;
                    }
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 720 } },
                            audio: false
                        });
                        pr.cameraStream = stream;
                        camVideo.srcObject = stream;
                        camPanel.removeAttribute('hidden');
                        await camVideo.play();
                    } catch (err) {
                        self.motionToast('Camera permission was declined or no camera was found.', 'warning');
                    }
                });
            }

            if (snapBtn && camVideo) {
                snapBtn.addEventListener('click', () => {
                    const vw = camVideo.videoWidth || 480;
                    const vh = camVideo.videoHeight || 540;
                    const snap = document.createElement('canvas');
                    snap.width = vw;
                    snap.height = vh;
                    snap.getContext('2d').drawImage(camVideo, 0, 0, vw, vh);
                    stopCam();
                    self.setNormalizedPhotoImage(snap);
                    self.extractPortraitPalette(pr.rawImg || pr.img);
                    self.setPhotoCompareMode(true);
                    self.setPhotoSplitPercent(50);
                    self.playPhotoReaction('smile');
                    self.syncPhotoTo3dAvatar();
                    self.scheduleNeuralAvatar();
                    self.motionToast('Webcam portrait captured — generating Neural HD Avatar!', 'success');
                });
            }

            if (cancelCamBtn) {
                cancelCamBtn.addEventListener('click', stopCam);
            }

            window.addEventListener('pagehide', stopCam);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) stopCam();
            });

            if (stageCard) {
                stageCard.addEventListener('dragover', e => {
                    e.preventDefault();
                    stageCard.classList.add('is-dragover');
                });
                stageCard.addEventListener('dragleave', () => {
                    stageCard.classList.remove('is-dragover');
                });
                stageCard.addEventListener('drop', e => {
                    e.preventDefault();
                    stageCard.classList.remove('is-dragover');
                    const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                    if (file) {
                        self.handleUploadedPhotoFile(file);
                    }
                });
            }
        },

        /**
         * Download the transformed stylised avatar as a crisp 512x576 HD PNG.
         */
        downloadStylisedAvatar: function () {
            const pr = this.photoRig;
            const src = pr.stylisedHighRes || pr.img;
            if (!src) return;

            const out = document.createElement('canvas');
            out.width = 1024;
            out.height = 1152;
            const octx = out.getContext('2d');
            octx.imageSmoothingEnabled = true;
            octx.imageSmoothingQuality = 'high';
            octx.drawImage(src, 0, 0, 1024, 1152);

            try {
                const link = document.createElement('a');
                link.download = `familyplatform-avatar-${pr.style || 'stylised'}.png`;
                link.href = out.toDataURL('image/png');
                link.click();
                this.motionToast('Downloaded HD stylised avatar PNG!', 'success');
            } catch (e) {
                this.motionToast('Could not export PNG from this image.', 'warning');
            }
        },

        /**
         * Share a branded FamilyPlatform Living Avatar advert card via Web Share API
         * (with clipboard + PNG download fallback on desktop browsers).
         */
        shareStylisedAvatar: async function () {
            const pr = this.photoRig;
            const src = pr.stylisedHighRes || pr.img;
            if (!src) {
                this.motionToast('Please upload or choose a photo first.', 'warning');
                return;
            }

            const styleKey = pr.style || 'stylised';
            const styleLabel = (this.photoStyleLabels && this.photoStyleLabels[styleKey]) || 'Living Avatar';
            const host = window.location.host || 'familyplatform.co.uk';
            const shareUrl = window.location.origin + '/';

            const card = document.createElement('canvas');
            card.width = 1024;
            card.height = 1280;
            const ctx = card.getContext('2d');
            if (!ctx) return;
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.scale(2, 2);

            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, 512, 640);
            ctx.drawImage(src, 0, 0, 512, 576);

            // Subtle branded advert footer bar
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 576, 512, 64);
            ctx.fillStyle = '#a31621';
            ctx.fillRect(0, 576, 512, 3);

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 16px system-ui, -apple-system, sans-serif';
            ctx.fillText('✨ FamilyPlatform · Living Avatar Studio', 16, 603);

            ctx.fillStyle = '#fbbf24';
            ctx.font = '600 13px system-ui, -apple-system, sans-serif';
            ctx.fillText(`${styleLabel}  ·  Create yours at ${host}`, 16, 625);

            try {
                const blob = await new Promise(resolve => card.toBlob(resolve, 'image/png'));
                if (!blob) {
                    this.motionToast('Could not prepare shareable avatar image.', 'warning');
                    return;
                }

                const file = new File([blob], `familyplatform-avatar-${styleKey}.png`, { type: 'image/png' });
                const shareText = `I just transformed my photo into a ${styleLabel} on FamilyPlatform! Create yours: ${shareUrl}`;

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: 'My Living Avatar on FamilyPlatform',
                        text: shareText,
                        files: [file],
                    });
                    this.motionToast('Shared your branded Living Avatar!', 'success');
                    return;
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    try {
                        await navigator.clipboard.writeText(shareText);
                    } catch (_) {
                        // Ignore clipboard permission errors on non-HTTPS contexts
                    }
                }

                const link = document.createElement('a');
                link.download = `familyplatform-avatar-share-${styleKey}.png`;
                link.href = URL.createObjectURL(blob);
                link.click();
                setTimeout(() => URL.revokeObjectURL(link.href), 4000);
                this.motionToast('Branded avatar card downloaded & invite link copied — ready to share!', 'success');
            } catch (err) {
                if (err && err.name === 'AbortError') return;
                this.motionToast('Could not share avatar image.', 'warning');
            }
        },

        /**
         * Normalise any uploaded or profile image into a clean 480x540 high-res source canvas
         * and immediately run the active ATG Photo-to-Avatar transformation style.
         */
        setNormalizedPhotoImage: function (rawImg) {
            const pr = this.photoRig;
            const norm = document.createElement('canvas');
            norm.width = 480;
            norm.height = 540;
            const c = norm.getContext('2d');
            c.fillStyle = '#1e1b18';
            c.fillRect(0, 0, 480, 540);

            const iw = rawImg.naturalWidth || rawImg.width || 480;
            const ih = rawImg.naturalHeight || rawImg.height || 540;
            const scale = Math.max(480 / iw, 540 / ih);
            const dw = iw * scale;
            const dh = ih * scale;
            const dx = (480 - dw) / 2;
            const dy = (540 - dh) / 2;
            c.drawImage(rawImg, dx, dy, dw, dh);

            pr.rawImg = norm;
            pr.photoUid = (pr.photoUid || 0) + 1;
            pr.neuralCache = {};
            this.applyInstantAvatarStyle(false);
        },

        /**
         * Load a profile photo URL into the Living Photo stage.
         */
        loadPhotoFromUrl: function (url, notifyUser) {
            const self = this;
            const pr = this.photoRig;
            const cleanUrl = (url || '/resources/images/profile/avatarM.png').split('?')[0] + '?v=' + Date.now();
            const img = new Image();
            img.onload = () => {
                self.setNormalizedPhotoImage(img);
                self.extractPortraitPalette(pr.rawImg || pr.img);
                self.playPhotoReaction('smile');
                if (notifyUser || pr.autoNeuralOnLoad) {
                    pr.autoNeuralOnLoad = false;
                    self.syncPhotoTo3dAvatar();
                    const statusEl = document.getElementById('atgPhotoCanvasStatus');
                    if (statusEl) {
                        statusEl.textContent = '✨ Generating OpenArt-grade Neural HD Avatar...';
                    }
                    self.scheduleNeuralAvatar();
                }
            };
            img.onerror = () => {
                self.loadDefaultPhotoPortrait();
                self.playPhotoReaction('smile');
                if (notifyUser) {
                    const statusEl = document.getElementById('atgPhotoCanvasStatus');
                    if (statusEl) {
                        statusEl.textContent = 'Using studio portrait — tap Smile, Dance, or Wink!';
                    }
                    self.motionToast('Loaded studio living portrait!', 'info');
                }
            };
            img.src = cleanUrl;
        },

        /**
         * Trigger an interactive expression / choreography on the Living Photo canvas
         * (smile, dance, wink, laugh, nod, love) with floating particles.
         */
        playPhotoReaction: function (action) {
            const pr = this.photoRig;
            const allowed = {
                smile: ['😄', '✨', '🌟'],
                dance: ['💃', '🎶', '🕺', '✨'],
                wink:  ['😉', '⭐', '✨'],
                laugh: ['😂', '🤣', '✨'],
                nod:   ['👍', '✅', '✨'],
                love:  ['❤️', '💖', '💕']
            };
            const key = allowed[action] ? action : 'smile';
            pr.activeAction = key;
            pr.actionUntil = performance.now() + (key === 'dance' ? 3200 : 2600);
            if (pr.compareMode && typeof this.setPhotoCompareMode === 'function') {
                this.setPhotoCompareMode(false);
            }

            const statusLabels = {
                smile: '😄 Living Photo: Warm Smile & Cheek Lift',
                dance: '💃 Living Photo: Dancing & Grooving!',
                wink:  '😉 Living Photo: Cheeky Wink & Head Tilt',
                laugh: '😂 Living Photo: Joyful Laughter!',
                nod:   '👍 Living Photo: Affirmative Head Nod',
                love:  '❤️ Living Photo: Sending Family Love!'
            };
            const statusEl = document.getElementById('atgPhotoCanvasStatus');
            if (statusEl && statusLabels[key]) {
                statusEl.textContent = statusLabels[key];
            }

            if (navigator.vibrate && navigator.userActivation && navigator.userActivation.hasBeenActive) {
                try { navigator.vibrate(12); } catch (_) { /* unsupported */ }
            }

            const layer = document.getElementById('atgPhotoReactLayer');
            if (!layer) return;
            const glyphs = allowed[key];
            for (let i = 0; i < 6; i++) {
                const p = document.createElement('span');
                p.className = 'atg-react-particle';
                p.textContent = glyphs[i % glyphs.length];
                p.style.left = (16 + Math.random() * 68) + '%';
                p.style.setProperty('--drift', (Math.random() * 54 - 27).toFixed(0) + 'px');
                p.style.animationDelay = (i * 65) + 'ms';
                p.style.fontSize = (18 + Math.random() * 12).toFixed(0) + 'px';
                layer.appendChild(p);
                setTimeout(() => p.remove(), 1850 + i * 65);
            }
        },

        loadDefaultPhotoPortrait: function () {
            const pr = this.photoRig;
            const off = document.createElement('canvas');
            off.width = 960;
            off.height = 1080;
            const c = off.getContext('2d');
            c.scale(4, 4);

            const bg = c.createRadialGradient(120, 120, 30, 120, 135, 160);
            bg.addColorStop(0, '#475569');
            bg.addColorStop(1, '#0f172a');
            c.fillStyle = bg;
            c.fillRect(0, 0, 240, 270);

            // Shoulders
            c.fillStyle = '#1e293b';
            c.beginPath();
            c.ellipse(120, 242, 82, 56, 0, 0, Math.PI * 2);
            c.fill();

            // Neck & Head with soft subsurface shading
            const skinGrad = c.createRadialGradient(112, 108, 12, 120, 122, 64);
            skinGrad.addColorStop(0, '#c88a58');
            skinGrad.addColorStop(0.65, '#8e5430');
            skinGrad.addColorStop(1, '#5a3118');
            c.fillStyle = skinGrad;
            c.beginPath();
            c.ellipse(120, 122, 46, 58, 0, 0, Math.PI * 2);
            c.fill();

            // Hair crown
            c.fillStyle = '#18110d';
            c.beginPath();
            c.arc(120, 94, 52, Math.PI, 0, false);
            c.fill();

            // Lifelike eyes with catchlights
            [99, 141].forEach(ex => {
                c.fillStyle = '#fbfaf8';
                c.beginPath();
                c.ellipse(ex, 116, 8.5, 5.2, 0, 0, Math.PI * 2);
                c.fill();
                c.fillStyle = '#3b2214';
                c.beginPath();
                c.arc(ex, 116, 4.2, 0, Math.PI * 2);
                c.fill();
                c.fillStyle = '#ffffff';
                c.beginPath();
                c.arc(ex + 1.6, 114.2, 1.5, 0, Math.PI * 2);
                c.fill();
            });

            // Warm smile
            c.strokeStyle = '#4a201e';
            c.lineWidth = 2.4;
            c.beginPath();
            c.arc(120, 142, 14, 0.2, Math.PI - 0.2);
            c.stroke();

            this.setNormalizedPhotoImage(off);
        },

        extractPortraitPalette: function (img) {
            const pr = this.photoRig;
            const off = document.createElement('canvas');
            off.width = 64;
            off.height = 64;
            const c = off.getContext('2d');
            c.drawImage(img, 0, 0, 64, 64);
            try {
                // Sample central cheek/forehead area
                const skinData = c.getImageData(24, 24, 16, 16).data;
                let r = 0, g = 0, b = 0, count = 0;
                for (let i = 0; i < skinData.length; i += 4) {
                    r += skinData[i];
                    g += skinData[i + 1];
                    b += skinData[i + 2];
                    count++;
                }
                if (count > 0) {
                    pr.sampleRgb = [Math.round(r / count), Math.round(g / count), Math.round(b / count)];
                }

                // Sample top hair crown area
                const hairData = c.getImageData(22, 6, 20, 10).data;
                let hr = 0, hg = 0, hb = 0, hCount = 0;
                for (let i = 0; i < hairData.length; i += 4) {
                    hr += hairData[i];
                    hg += hairData[i + 1];
                    hb += hairData[i + 2];
                    hCount++;
                }
                if (hCount > 0) {
                    pr.hairRgb = [Math.round(hr / hCount), Math.round(hg / hCount), Math.round(hb / hCount)];
                }
            } catch (e) { /* cross-origin fallback keeps defaults */ }
        },

        updateIdentityLockScore: function () {
            const strength = parseFloat((document.getElementById('atgStyleStrength') || {}).value || '0.78');
            const detailLock = parseFloat((document.getElementById('atgStyleDetailLock') || {}).value || '0.80');
            const score = Math.max(91.0, Math.min(99.6, 94.2 + detailLock * 5.2 - (strength - 0.5) * 1.8)).toFixed(1);
            const badge = document.getElementById('atgIdentityScoreBadge');
            if (badge) {
                badge.textContent = `Identity Lock: ${score}%`;
            }
        },

        renderPhotoLivingFrame: function (elapsedSec, now) {
            const pr = this.photoRig;
            if (!pr.ctx || !pr.img) return;
            const ctx = pr.ctx;
            const w = pr.canvas.width;
            const h = pr.canvas.height;
            const scale = w / 240;

            pr.tiltX += (pr.targetX - pr.tiltX) * 0.12;
            pr.tiltY += (pr.targetY - pr.tiltY) * 0.12;

            // Active reaction or continuous choreography loop
            const isReacting = now < pr.actionUntil;
            const mode = isReacting ? pr.activeAction : (pr.choreoLoop || 'smile');

            let danceDx = 0;
            let danceDy = 0;
            let danceRot = 0;
            let smileFactor = 0;
            let winkRight = false;
            let rosyGlow = 0;

            if (mode === 'dance') {
                // 120 BPM rhythmic dance groove
                const beat = elapsedSec * 6.2;
                danceDx = Math.sin(beat) * 7.5;
                danceDy = -Math.abs(Math.cos(beat)) * 6.5;
                danceRot = Math.sin(beat) * 0.085;
                smileFactor = 0.75 + Math.sin(beat * 2) * 0.25;
                rosyGlow = 0.18;
            } else if (mode === 'laugh') {
                const shake = elapsedSec * 14;
                danceDy = Math.sin(shake) * 4.2;
                danceRot = Math.cos(shake * 0.5) * 0.04;
                smileFactor = 1.0;
                rosyGlow = 0.22;
            } else if (mode === 'wink') {
                danceRot = 0.055;
                smileFactor = 0.75;
                winkRight = isReacting || (Math.sin(elapsedSec * 2.2) > 0.55);
                rosyGlow = 0.15;
            } else if (mode === 'nod') {
                danceDy = Math.sin(elapsedSec * 9.0) * 7.0;
                smileFactor = 0.45;
            } else if (mode === 'love') {
                danceRot = Math.sin(elapsedSec * 2.4) * 0.05;
                smileFactor = 0.85;
                rosyGlow = 0.28;
            } else if (mode === 'smile') {
                smileFactor = isReacting ? 0.95 : (0.45 + Math.sin(elapsedSec * 1.8) * 0.35);
                rosyGlow = 0.14;
            }

            const idleX = Math.sin(elapsedSec * 0.9) * 0.025;
            const idleY = Math.cos(elapsedSec * 0.7) * 0.018;
            const tx = pr.tiltX + idleX;
            const ty = pr.tiltY + idleY;

            // Natural breathing cycle (4.2s)
            const breath = Math.sin((elapsedSec * Math.PI * 2) / 4.2) * 2.6;

            // Blink state machine
            if (!pr.blinking && now > pr.nextBlink) {
                pr.blinking = true;
                pr.blink = 0;
            }
            if (pr.blinking) {
                pr.blink += 0.18;
                if (pr.blink >= 2) {
                    pr.blinking = false;
                    pr.blink = 0;
                    pr.nextBlink = now + 2600 + Math.random() * 2600;
                }
            }

            const sharpen = parseFloat((document.getElementById('atgParamSharpening') || {}).value || '0.45');
            const eyeRetouch = parseFloat((document.getElementById('atgParamEyeRetouch') || {}).value || '0.80');
            const skinTex = parseFloat((document.getElementById('atgParamSkinTexture') || {}).value || '0.72');
            const talkLevel = (this.motion && this.motion.talking) ? (this.motion.talkLevel || 0) : 0;

            // In Before/After Compare mode (while not actively playing a tapped expression),
            // lock geometric translation/scale to 1:1 so the Before and After halves align pixel-for-pixel across the split slider.
            const lockCompareAlignment = pr.compareMode && !isReacting;

            ctx.clearRect(0, 0, w, h);
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.save();
            if (lockCompareAlignment) {
                ctx.drawImage(pr.img, 0, 0, w, h);
            } else if (typeof window.AtgDeformablePhotoPuppet === 'function') {
                ctx.filter = `contrast(${(1 + (sharpen - 0.4) * 0.18).toFixed(2)}) saturate(${(1 + (skinTex - 0.5) * 0.15).toFixed(2)})`;
                ctx.translate(w / 2 + danceDx * scale, h / 2 + danceDy * scale);
                ctx.rotate(tx * 0.22 + danceRot);
                ctx.scale(1.06, 1.06);
                ctx.translate(-w / 2, -h / 2);

                if (!pr.meshPuppet) {
                    pr.meshPuppet = new window.AtgDeformablePhotoPuppet({ gridCols: 28, gridRows: 30 });
                }
                if (pr.meshPuppet.image !== pr.img) {
                    pr.meshPuppet.setImage(pr.img);
                }

                const blinkAmount = pr.blinking ? (pr.blink <= 1 ? pr.blink : 2 - pr.blink) : 0;
                const actionKey = isReacting ? (pr.activeAction || 'smile') : (talkLevel > 0.04 ? 'talk' : 'hello');
                const actionEnv = isReacting ? Math.min(1, (pr.actionUntil - now) / 600) : 0;

                pr.meshPuppet.updateDeformation({
                    elapsedSec: elapsedSec,
                    mode: actionKey,
                    intensity: Math.max(0, actionEnv),
                    voiceLevel: talkLevel,
                    blinkAmount: blinkAmount,
                    winkAmount: (isReacting && actionKey === 'wink') ? Math.sin(actionEnv * Math.PI) : 0,
                    smileAmount: smileFactor,
                    tiltX: tx * 3.5,
                    tiltY: ty * 3.5
                });
                pr.meshPuppet.render(ctx, 0, 0, w, h);
            } else {
                ctx.filter = `contrast(${(1 + (sharpen - 0.4) * 0.18).toFixed(2)}) saturate(${(1 + (skinTex - 0.5) * 0.15).toFixed(2)})`;
                ctx.translate(w / 2 + danceDx * scale, h / 2 + danceDy * scale);
                ctx.rotate(tx * 0.22 + danceRot);
                ctx.scale(1.06, 1.06);
                ctx.translate(-w / 2, -h / 2);

                const sx = tx * 14 * scale;
                const sy = (ty * 10 + breath) * scale;

                // 2.5D Horizontal Strip Facial Warping (Cheek-lift Smile + Talk Jaw Motion)
                const stripH = Math.max(6, Math.round(6 * scale));
                const imgW = pr.img.width || w;
                const imgH = pr.img.height || h;
                for (let y = 0; y < h; y += stripH) {
                    const ny = y / h;
                    let liftY = 0;
                    let expandX = 1.0;

                    // Cheek & smile zone (46% to 74% vertical height)
                    if (smileFactor > 0.02 && ny >= 0.44 && ny <= 0.76) {
                        const bell = Math.sin(((ny - 0.44) / 0.32) * Math.PI);
                        liftY = -smileFactor * bell * 5.2 * scale;
                        expandX = 1.0 + smileFactor * bell * 0.036;
                    }

                    // Talk mode jaw motion (62% to 84% vertical height)
                    if (talkLevel > 0.04 && ny >= 0.62 && ny <= 0.84) {
                        const jawBell = Math.sin(((ny - 0.62) / 0.22) * Math.PI);
                        liftY += talkLevel * jawBell * 6.5 * scale;
                    }

                    const destW = w * expandX;
                    const destX = sx - (destW - w) / 2;
                    const srcY = (y / h) * imgH;
                    const srcStripH = Math.min((stripH / h) * imgH, imgH - srcY);
                    if (srcStripH > 0) {
                        ctx.drawImage(pr.img, 0, srcY, imgW, srcStripH, destX, y + sy + liftY, destW, stripH + (0.6 * scale));
                    }
                }
            }

            ctx.restore();
        },

        /**
         * Save the actual Living Photo (from #atgPhotoLivingCanvas) as the user's profile picture.
         * Generates a 512x512 high-resolution portrait PNG and instructs the backend to wrap it
         * in a self-contained animated Living Photo SVG that smiles, blinks, and dances/grooves!
         */
        saveLivingPhotoAvatar: function (btn) {
            const pr = this.photoRig;
            if (!pr.img) {
                this.loadDefaultPhotoPortrait();
            }

            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.start(btn);
            }

            const stopSpinner = () => {
                if (window.ScoreTenantSpinner && btn) window.ScoreTenantSpinner.stop(btn);
                if (window.FamilyPlatformSpinner && btn) window.FamilyPlatformSpinner.stop(btn);
            };

            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = 1024;
            exportCanvas.height = 1024;
            const ectx = exportCanvas.getContext('2d');
            ectx.imageSmoothingEnabled = true;
            ectx.imageSmoothingQuality = 'high';

            const drawCoverSquare = (src) => {
                const sw = src.naturalWidth || src.width || 1024;
                const sh = src.naturalHeight || src.height || 1024;
                const side = Math.min(sw, sh);
                const sx = Math.max(0, Math.floor((sw - side) / 2));
                const sy = Math.max(0, Math.floor((sh - side) / 3));
                ectx.drawImage(src, sx, sy, side, side, 0, 0, 1024, 1024);
            };

            const sourceForSave = pr.stylisedHighRes || pr.img;
            drawCoverSquare(sourceForSave);

            let pngDataUrl = null;
            try {
                pngDataUrl = exportCanvas.toDataURL('image/png');
            } catch (e) {
                this.loadDefaultPhotoPortrait();
                drawCoverSquare(pr.stylisedHighRes || pr.img);
                pngDataUrl = exportCanvas.toDataURL('image/png');
            }

            const csrfToken = document.querySelector('input[name="token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const choreo = (document.getElementById('atgPhotoChoreographySelect') || {}).value || 'smile';

            fetch('/api/emoji/save-avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(Object.assign({}, this.state, {
                    token: csrfToken,
                    csrf_token: csrfToken,
                    mode: 'living_photo',
                    avatar_style: pr.style || 'illustrated_comic',
                    choreography: choreo,
                    sample_r: pr.sampleRgb[0],
                    sample_g: pr.sampleRgb[1],
                    sample_b: pr.sampleRgb[2],
                    animated: true,
                    png_base64: pngDataUrl
                }))
            })
            .then(res => res.json())
            .then(data => {
                stopSpinner();
                if (data.success) {
                    const newUrl = data.url + '?v=' + Date.now();
                    document.querySelectorAll('.avatar.profileImg, #profilePreview, #pfViewerThumb, #pfViewerMainImg').forEach(imgEl => {
                        imgEl.src = newUrl;
                    });
                    const useBtn = document.getElementById('atgUseCurrentProfilePhotoBtn');
                    if (useBtn && data.png_url) {
                        useBtn.dataset.profilePhoto = data.png_url;
                    }
                    if (pr.canvas && data.png_url) {
                        pr.canvas.dataset.profilePhoto = data.png_url;
                    }
                    this.playPhotoReaction(choreo === 'idle' ? 'smile' : choreo);

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Living Photo Active!',
                            text: data.message || 'Your Living Photo is now your official profile picture!',
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert(data.message || 'Living Photo saved as your profile picture!');
                    }
                } else {
                    throw new Error(data.error || 'Failed to save Living Photo.');
                }
            })
            .catch(err => {
                stopSpinner();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: err.message,
                        confirmButtonColor: '#a31621'
                    });
                } else {
                    alert('Error: ' + err.message);
                }
            });
        },

        /**
         * Publish the current stylised Living Photo portrait directly to the Family Feed
         * (with an editable caption and high-resolution 512x576 PNG attachment).
         */
        postLivingPhotoToFeed: async function (btn) {
            const pr = this.photoRig;
            if (!pr.img) {
                this.loadDefaultPhotoPortrait();
            }

            const sourceForPost = pr.stylisedHighRes || pr.img;
            if (!sourceForPost) {
                this.motionToast('Please choose or capture a photo first.', 'warning');
                return;
            }

            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = 1024;
            exportCanvas.height = 1152;
            const ectx = exportCanvas.getContext('2d');
            ectx.imageSmoothingEnabled = true;
            ectx.imageSmoothingQuality = 'high';
            ectx.drawImage(sourceForPost, 0, 0, 1024, 1152);

            let previewDataUrl = '';
            try {
                previewDataUrl = exportCanvas.toDataURL('image/png');
            } catch (e) {
                this.loadDefaultPhotoPortrait();
                ectx.drawImage(pr.stylisedHighRes || pr.img, 0, 0, 1024, 1152);
                previewDataUrl = exportCanvas.toDataURL('image/png');
            }

            const styleKey = pr.style || 'illustrated_comic';
            const activeCard = document.querySelector(`.atg-style-card[data-avatar-style="${styleKey}"] .atg-style-name`);
            const styleLabel = activeCard
                ? activeCard.textContent.trim()
                : ((this.photoStyleLabels && this.photoStyleLabels[styleKey]) || 'Living Avatar');

            let caption = `✨ Just created my new ${styleLabel} Living Photo in the FamilyPlatform Avatar Studio! #LivingAvatar`;

            if (window.Swal && typeof window.Swal.fire === 'function') {
                const promptRes = await window.Swal.fire({
                    title: 'Post Living Photo to Family Feed',
                    html: `
                        <div class="text-start">
                            <div class="d-flex justify-content-center mb-3">
                                <img src="${previewDataUrl}" alt="Living Photo preview" style="width: 148px; height: 166px; object-fit: cover; border-radius: 14px; border: 2px solid #a31621; box-shadow: 0 8px 20px rgba(15,23,42,0.18);">
                            </div>
                            <label for="atgLivingPhotoPostCaption" class="form-label fw-bold small mb-1">Add a caption for your family:</label>
                            <textarea id="atgLivingPhotoPostCaption" class="form-control" rows="3" maxlength="500" style="border-radius: 12px; font-size: 0.9rem;">${caption}</textarea>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#a31621',
                    confirmButtonText: '📣 Publish to Family Feed',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const el = document.getElementById('atgLivingPhotoPostCaption');
                        return el ? el.value.trim() : caption;
                    }
                });
                if (!promptRes.isConfirmed) {
                    return;
                }
                if (typeof promptRes.value === 'string' && promptRes.value.trim() !== '') {
                    caption = promptRes.value.trim();
                }
            }

            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.start(btn);
            }
            const stopSpinner = () => {
                if (window.ScoreTenantSpinner && btn) window.ScoreTenantSpinner.stop(btn);
                if (window.FamilyPlatformSpinner && btn) window.FamilyPlatformSpinner.stop(btn);
            };

            try {
                const blob = await new Promise(resolve => exportCanvas.toBlob(resolve, 'image/png'));
                if (!blob) {
                    throw new Error('Could not prepare Living Photo image file.');
                }

                const csrfToken = document.querySelector('input[name="token"]')?.value ||
                                  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const file = new File([blob], `living-photo-${styleKey}-${Date.now()}.png`, { type: 'image/png' });

                const formData = new FormData();
                formData.append('postMessage', caption);
                formData.append('post_img[]', file);
                if (csrfToken) {
                    formData.append('token', csrfToken);
                    formData.append('csrf_token', csrfToken);
                }

                const resp = await fetch('/member/profilePage/post', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                    },
                    body: formData
                });

                const payload = await resp.json().catch(() => ({}));
                stopSpinner();

                if (!resp.ok || payload.status === 'error') {
                    throw new Error(payload.message || payload.error || 'Could not publish Living Photo to feed.');
                }

                const postObj = (payload.message && typeof payload.message === 'object') ? payload.message : payload;
                const postNo = postObj && (postObj.post_no || postObj.postNo) ? (postObj.post_no || postObj.postNo) : '';
                const feedUrl = '/member/profilePage' + (postNo ? '#post' + postNo : '');

                if (window.Swal && typeof window.Swal.fire === 'function') {
                    const navRes = await window.Swal.fire({
                        icon: 'success',
                        title: 'Published to Family Feed!',
                        text: 'Your stylised Living Photo has been shared with your family.',
                        showCancelButton: true,
                        confirmButtonColor: '#a31621',
                        confirmButtonText: 'View in Family Feed →',
                        cancelButtonText: 'Stay in Studio'
                    });
                    if (navRes.isConfirmed) {
                        window.location.href = feedUrl;
                    }
                } else {
                    window.location.href = feedUrl;
                }
            } catch (err) {
                stopSpinner();
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Could Not Publish Post',
                        text: (err && err.message) ? err.message : 'An unexpected error occurred.',
                        confirmButtonColor: '#a31621'
                    });
                } else {
                    alert('Error: ' + ((err && err.message) ? err.message : 'Could not publish post.'));
                }
            }
        },

        syncPhotoTo3dAvatar: function (btn) {
            const self = this;
            const pr = this.photoRig;
            if (btn && window.ScoreTenantSpinner) {
                window.ScoreTenantSpinner.start(btn);
            }

            const csrfToken = document.querySelector('input[name="token"]')?.value ||
                              document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const payload = {
                token: csrfToken,
                csrf_token: csrfToken,
                sample_r: pr.sampleRgb[0],
                sample_g: pr.sampleRgb[1],
                sample_b: pr.sampleRgb[2],
                hair_r: pr.hairRgb[0],
                hair_g: pr.hairRgb[1],
                hair_b: pr.hairRgb[2],
                denoising_strength: parseFloat((document.getElementById('atgParamDenoising') || {}).value || '0.38'),
                cfg_scale: parseFloat((document.getElementById('atgParamCfg') || {}).value || '6.5'),
                face_restoration: parseFloat((document.getElementById('atgParamFaceRestore') || {}).value || '0.65'),
                skin_texture: parseFloat((document.getElementById('atgParamSkinTexture') || {}).value || '0.72'),
                sharpening: parseFloat((document.getElementById('atgParamSharpening') || {}).value || '0.45'),
                eye_retouch: parseFloat((document.getElementById('atgParamEyeRetouch') || {}).value || '0.80'),
                traits: this.state
            };

            fetch('/api/emoji/photo-to-avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (btn && window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(btn);
                }
                if (res.success && res.data) {
                    const d = res.data;
                    if (d.traits) {
                        Object.keys(d.traits).forEach(k => self.setTrait(k, d.traits[k]));
                    }
                    self.refreshAvatarPreview();
                    self.playReaction('hello');
                    const summary = document.getElementById('atgPhotoMatchSummary');
                    if (summary && d.sampled_palette) {
                        summary.style.display = 'block';
                        summary.textContent = `✓ Matched complexion: ${d.sampled_palette.skin_label} (${d.sampled_palette.undertone} undertone) · Identity Lock ${d.identity_score}%`;
                    }
                }
            })
            .catch(() => {
                if (btn && window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(btn);
                }
            });
        },

        /* -------------------------------------------------------------------------
         * Augmented Reality (AR) & 3D Holographic Viewer Engine
         * ------------------------------------------------------------------------- */
        ar: {
            activeEmojiId: null,
            cameraStream: null,
            isCameraActive: false,
            rotX: 0,
            rotY: 0,
            targetRotX: 0,
            targetRotY: 0,
            scale: 1,
            posX: 0,
            posY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            animFrame: null,
            particleAnim: null,
            particles: []
        },

        initArViewerEvents: function () {
            const self = this;

            // Bind toggle buttons in AR Modal
            const orbitBtn = document.getElementById('atgToggleOrbitBtn');
            const camBtn = document.getElementById('atgToggleCameraBtn');
            const burstBtn = document.getElementById('atgBurstParticlesBtn');
            const snapBtn = document.getElementById('atgArSnapshotBtn');
            const modalEl = document.getElementById('atgArEmojiModal');

            if (orbitBtn) {
                orbitBtn.addEventListener('click', () => self.setArMode('orbit'));
            }
            if (camBtn) {
                camBtn.addEventListener('click', () => self.setArMode('camera'));
            }
            if (burstBtn) {
                burstBtn.addEventListener('click', () => self.burstArParticles(40));
            }
            if (snapBtn) {
                snapBtn.addEventListener('click', () => self.takeArSnapshot());
            }

            // Kill camera when modal is closed to protect privacy
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', () => {
                    self.stopArCamera();
                    self.stopArParticles();
                    if (self.ar.animFrame) {
                        cancelAnimationFrame(self.ar.animFrame);
                        self.ar.animFrame = null;
                    }
                });
            }

            this.bindArStageGestures();
        },

        /**
         * Launch 3D / AR Viewer for an emoji.
         */
        openArViewer: function (emojiId, title, desc) {
            const self = this;
            self.ar.activeEmojiId = emojiId;

            const modalEl = document.getElementById('atgArEmojiModal');
            if (!modalEl) return;

            const imgEl = document.getElementById('atgArEmojiImg');
            const titleEl = document.getElementById('atgArEmojiName');
            const descEl = document.getElementById('atgArEmojiMeaning');

            if (imgEl) {
                const bust = Date.now();
                imgEl.onerror = () => { imgEl.src = `/img/emoji/${emojiId}.png?v=${bust}`; };
                imgEl.src = `/img/emoji/${emojiId}.svg?v=${bust}`;
            }

            const cleanTitle = title || emojiId.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            if (titleEl) titleEl.textContent = cleanTitle;
            if (descEl) descEl.textContent = desc || 'Interactive 3D Augmented Reality Family Emoji';

            // Reset transformations
            self.ar.rotX = 0;
            self.ar.rotY = 0;
            self.ar.targetRotX = 0;
            self.ar.targetRotY = 0;
            self.ar.scale = 1;
            self.ar.posX = 0;
            self.ar.posY = 0;
            self.setArMode('orbit');

            // Show modal via Bootstrap
            if (window.bootstrap && window.bootstrap.Modal) {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();
            } else {
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
            }

            self.startArLoop();
            self.startArParticles();
        },

        /**
         * Switch between 3D Orbit and Camera Augmented Reality modes.
         */
        setArMode: function (mode) {
            const self = this;
            const orbitBtn = document.getElementById('atgToggleOrbitBtn');
            const camBtn = document.getElementById('atgToggleCameraBtn');
            const snapBtn = document.getElementById('atgArSnapshotBtn');
            const camVideo = document.getElementById('atgArCameraFeed');
            const reticle = document.getElementById('atgArCameraReticle');
            const badge = document.getElementById('atgArModeBadge');

            if (mode === 'camera') {
                self.startArCamera()
                    .then(success => {
                        if (success) {
                            if (camBtn) camBtn.classList.add('active');
                            if (orbitBtn) orbitBtn.classList.remove('active');
                            if (snapBtn) snapBtn.classList.remove('d-none');
                            if (camVideo) camVideo.classList.remove('d-none');
                            if (reticle) reticle.classList.remove('d-none');
                            if (badge) badge.innerHTML = '<i class="bi bi-camera-video-fill text-warning"></i> Room AR Camera';
                        }
                    });
            } else {
                self.stopArCamera();
                if (orbitBtn) orbitBtn.classList.add('active');
                if (camBtn) camBtn.classList.remove('active');
                if (snapBtn) snapBtn.classList.add('d-none');
                if (camVideo) camVideo.classList.add('d-none');
                if (reticle) reticle.classList.add('d-none');
                if (badge) badge.innerHTML = '<i class="bi bi-box"></i> 3D Orbit Mode';
            }
        },

        /**
         * Stream device camera into AR viewport.
         */
        startArCamera: async function () {
            const self = this;
            const videoEl = document.getElementById('atgArCameraFeed');
            if (!videoEl) return false;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Augmented Reality camera access is not supported by your browser.');
                return false;
            }

            try {
                // Request environment/rear camera if available
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });

                self.ar.cameraStream = stream;
                self.ar.isCameraActive = true;
                videoEl.srcObject = stream;
                await videoEl.play();
                return true;
            } catch (err) {
                console.warn('AR Camera permission denied or device not found:', err);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Camera Access Needed for AR',
                        text: 'Please allow camera access in your browser to project this 3D family emoji into your room.',
                        confirmButtonColor: '#a31621'
                    });
                } else {
                    alert('Camera access needed to project in room.');
                }
                return false;
            }
        },

        /**
         * Stop camera feed and turn off camera light.
         */
        stopArCamera: function () {
            if (this.ar.cameraStream) {
                this.ar.cameraStream.getTracks().forEach(track => track.stop());
                this.ar.cameraStream = null;
            }
            this.ar.isCameraActive = false;
            const videoEl = document.getElementById('atgArCameraFeed');
            if (videoEl) videoEl.srcObject = null;
        },

        /**
         * Gesture controls: Drag to rotate/reposition and wheel/pinch to scale in AR.
         */
        bindArStageGestures: function () {
            const self = this;
            const stage = document.getElementById('atgArStageContainer');
            if (!stage) return;

            let lastDist = 0;

            function onPointerDown(clientX, clientY) {
                self.ar.isDragging = true;
                self.ar.startX = clientX;
                self.ar.startY = clientY;
            }

            function onPointerMove(clientX, clientY) {
                if (!self.ar.isDragging) return;
                const dx = clientX - self.ar.startX;
                const dy = clientY - self.ar.startY;

                if (self.ar.isCameraActive) {
                    // In camera mode: drag translates position in the room
                    self.ar.posX += dx * 0.8;
                    self.ar.posY += dy * 0.8;
                } else {
                    // In orbit mode: drag rotates 3D object
                    self.ar.targetRotY += dx * 0.6;
                    self.ar.targetRotX -= dy * 0.6;
                }

                self.ar.startX = clientX;
                self.ar.startY = clientY;
            }

            function onPointerUp() {
                self.ar.isDragging = false;
            }

            stage.addEventListener('mousedown', e => {
                e.preventDefault();
                onPointerDown(e.clientX, e.clientY);
            });
            window.addEventListener('mousemove', e => onPointerMove(e.clientX, e.clientY));
            window.addEventListener('mouseup', onPointerUp);

            stage.addEventListener('touchstart', e => {
                if (e.touches.length === 1) {
                    onPointerDown(e.touches[0].clientX, e.touches[0].clientY);
                } else if (e.touches.length === 2) {
                    // Pinch zoom
                    lastDist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                }
            }, { passive: true });

            window.addEventListener('touchmove', e => {
                if (e.touches.length === 1) {
                    onPointerMove(e.touches[0].clientX, e.touches[0].clientY);
                } else if (e.touches.length === 2 && lastDist > 0) {
                    const dist = Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY
                    );
                    const zoomDelta = (dist - lastDist) * 0.005;
                    self.ar.scale = Math.max(0.4, Math.min(2.5, self.ar.scale + zoomDelta));
                    lastDist = dist;
                }
            }, { passive: true });

            window.addEventListener('touchend', () => {
                onPointerUp();
                lastDist = 0;
            });

            // Mouse wheel zoom
            stage.addEventListener('wheel', e => {
                e.preventDefault();
                const zoomDelta = e.deltaY * -0.0015;
                self.ar.scale = Math.max(0.4, Math.min(2.5, self.ar.scale + zoomDelta));
            }, { passive: false });
        },

        /**
         * 60fps render loop for 3D physics and smooth spring rotation.
         */
        startArLoop: function () {
            const self = this;
            const objectEl = document.getElementById('atgArFloatingObject');

            function loop() {
                if (!self.ar.isDragging && !self.ar.isCameraActive) {
                    // Gentle auto-rotation in 3D orbit mode
                    self.ar.targetRotY += 0.35;
                }

                self.ar.rotX += (self.ar.targetRotX - self.ar.rotX) * 0.12;
                self.ar.rotY += (self.ar.targetRotY - self.ar.rotY) * 0.12;

                if (objectEl) {
                    objectEl.style.transform = `translate3d(${self.ar.posX}px, ${self.ar.posY}px, 0) perspective(700px) rotateX(${self.ar.rotX.toFixed(2)}deg) rotateY(${self.ar.rotY.toFixed(2)}deg) scale3d(${self.ar.scale}, ${self.ar.scale}, ${self.ar.scale})`;
                }

                self.ar.animFrame = requestAnimationFrame(loop);
            }

            if (!self.ar.animFrame) {
                self.ar.animFrame = requestAnimationFrame(loop);
            }
        },

        /**
         * Ambient floating particles field on HTML5 Canvas.
         */
        startArParticles: function () {
            const self = this;
            const canvas = document.getElementById('atgArParticleCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            canvas.width = canvas.parentElement.clientWidth;
            canvas.height = canvas.parentElement.clientHeight;

            // Generate particles
            self.ar.particles = [];
            for (let i = 0; i < 35; i++) {
                self.ar.particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 0.8,
                    vy: (Math.random() - 0.5) * 0.8 - 0.3,
                    size: Math.random() * 3 + 1.5,
                    alpha: Math.random() * 0.6 + 0.2,
                    color: Math.random() > 0.4 ? '#f59e0b' : '#38bdf8'
                });
            }

            function drawParticles() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                self.ar.particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;
                    if (p.x < 0) p.x = canvas.width;
                    if (p.x > canvas.width) p.x = 0;
                    if (p.y < 0) p.y = canvas.height;
                    if (p.y > canvas.height) p.y = 0;

                    ctx.fillStyle = p.color;
                    ctx.globalAlpha = p.alpha;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                    ctx.fill();
                });
                ctx.globalAlpha = 1;

                self.ar.particleAnim = requestAnimationFrame(drawParticles);
            }

            if (!self.ar.particleAnim) {
                self.ar.particleAnim = requestAnimationFrame(drawParticles);
            }
        },

        stopArParticles: function () {
            if (this.ar.particleAnim) {
                cancelAnimationFrame(this.ar.particleAnim);
                this.ar.particleAnim = null;
            }
        },

        burstArParticles: function (count) {
            const canvas = document.getElementById('atgArParticleCanvas');
            if (!canvas) return;
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;

            for (let i = 0; i < count; i++) {
                const angle = Math.random() * Math.PI * 2;
                const speed = Math.random() * 5 + 2;
                this.ar.particles.push({
                    x: cx,
                    y: cy,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    size: Math.random() * 4 + 2,
                    alpha: 1,
                    color: ['#f59e0b', '#ec4899', '#3b82f6', '#10b981', '#fef08a'][Math.floor(Math.random() * 5)]
                });
            }
        },

        takeArSnapshot: function () {
            const canvas = document.createElement('canvas');
            const video = document.getElementById('atgArCameraFeed');
            const img = document.getElementById('atgArEmojiImg');
            if (!video || !img) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');

            // Draw video frame
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Draw 3D emoji at center
            const emojiSize = 160;
            ctx.drawImage(img, (canvas.width - emojiSize) / 2, (canvas.height - emojiSize) / 2, emojiSize, emojiSize);

            const link = document.createElement('a');
            link.download = `family_ar_${Date.now()}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();

            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'AR Photo Captured!',
                    text: 'Your Augmented Reality family photo has been saved.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },

        /**
         * Feed / comment living emoji tap: the first tap plays the emoji with a themed
         * particle burst; a second tap within a short window opens the 3D / AR viewer.
         */
        tapLive: function (el, emojiId) {
            if (!el || !emojiId || !/^[a-z0-9_]+_3d$/i.test(emojiId)) return;
            const now = Date.now();
            const last = Number(el.dataset.atgPlayedAt || 0);
            if (now - last < 2500) {
                el.dataset.atgPlayedAt = '0';
                const meta = this.livingEmojiMeta(emojiId);
                this.openArViewer(emojiId, meta ? meta.title : undefined, meta ? meta.meaning : undefined);
                return;
            }
            el.dataset.atgPlayedAt = String(now);
            el.classList.remove('atg-live-emoji--play');
            void el.offsetWidth; // restart the animation on rapid taps
            el.classList.add('atg-live-emoji--play');
            setTimeout(() => el.classList.remove('atg-live-emoji--play'), 900);
            if (navigator.vibrate) { try { navigator.vibrate(12); } catch (_) { /* unsupported */ } }

            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reduce) this.burstParticles(el, emojiId);
        },

        /** Signed-in member's Me-stickers (RFC-0011), fetched once per page. */
        getMeStickers: function (force) {
            const self = (this && typeof this === 'object' && this !== window) ? this : AtgEmojiEngine;
            if (!self._meStickers || force) {
                self._meStickers = fetch('/api/emoji/me-stickers', { credentials: 'same-origin', headers: { Accept: 'application/json' } })
                    .then(r => r.json())
                    .catch(err => { self._meStickers = null; throw err; });
            }
            return self._meStickers;
        },

        /** Feed tap on a Me-sticker: replay its pop + burst (no AR viewer for avatars). */
        tapSticker: function (el, reaction) {
            const self = (this && typeof this === 'object' && this !== window) ? this : AtgEmojiEngine;
            if (!el) return;
            el.classList.remove('atg-live-emoji--play');
            void el.offsetWidth;
            el.classList.add('atg-live-emoji--play');
            setTimeout(() => el.classList.remove('atg-live-emoji--play'), 900);
            const map = { hello: 'family_wave_3d', laugh: 'laugh_tears_3d', wow: 'star_struck_3d', love: 'family_hug_3d', nod: 'kinship_pride_3d', dance: 'family_celebrate_3d', point: 'family_point_3d', clap: 'family_clap_3d' };
            const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reduce) self.burstParticles(el, map[reaction] || '');
            if (navigator.vibrate) { try { navigator.vibrate(12); } catch (_) { /* unsupported */ } }
        },

        livingEmojiMeta: function (emojiId) {
            const cfg = window.ATG_LIVING_EMOJIS;
            if (!cfg || !Array.isArray(cfg.catalog)) return null;
            const base = String(emojiId).replace(/_(medium_light|medium_dark|light|medium|dark)_3d$/, '_3d');
            return cfg.catalog.find(e => e.id === base) || null;
        },

        burstParticles: function (el, emojiId) {
            const base = String(emojiId).replace(/_(medium_light|medium_dark|light|medium|dark)_3d$/, '_3d');
            const themes = {
                family_celebrate_3d: ['🎉', '✨', '🎊'], family_feast_3d: ['🎉', '🍲', '✨'],
                family_toast_3d: ['🥂', '✨', '🎉'], family_hug_3d: ['❤️', '💖', '🤗'],
                family_heart_hands_3d: ['💖', '❤️', '💕'], family_hearth_3d: ['🔥', '💛', '✨'],
                ancestor_blessing_3d: ['🙏', '✨', '💛'], ancestor_crown_3d: ['👑', '✨', '⭐'],
                family_clap_3d: ['👏', '✨', '🎉'], laugh_tears_3d: ['😂', '💧', '🤣'],
                star_struck_3d: ['⭐', '🌟', '✨'], kinship_pride_3d: ['🌟', '💪', '✨'],
                baby_cradle_3d: ['🍼', '💕', '⭐'], wedding_vows_3d: ['💍', '💐', '💕'],
                family_wave_3d: ['👋', '✨', '💛'], family_point_3d: ['👉', '✨', '⭐'],
                family_roots_3d: ['🌿', '🍃', '✨'], memory_capsule_3d: ['📸', '✨', '💫'],
            };
            const glyphs = themes[base] || ['✨', '💛', '⭐'];
            const rect = el.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const layer = document.createElement('div');
            layer.className = 'atg-burst-layer';
            layer.setAttribute('aria-hidden', 'true');
            document.body.appendChild(layer);
            const count = 10;
            for (let i = 0; i < count; i++) {
                const p = document.createElement('span');
                p.className = 'atg-burst-particle';
                p.textContent = glyphs[i % glyphs.length];
                p.style.left = cx + 'px';
                p.style.top = cy + 'px';
                layer.appendChild(p);
                const angle = (Math.PI * 2 * i) / count + (Math.random() - 0.5) * 0.6;
                const dist = 38 + Math.random() * 46;
                const dx = Math.cos(angle) * dist;
                const dy = Math.sin(angle) * dist - 24;
                if (p.animate) {
                    p.animate([
                        { transform: 'translate(-50%, -50%) scale(0.4)', opacity: 1 },
                        { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) scale(1.1) rotate(${(Math.random() - 0.5) * 60}deg)`, opacity: 1, offset: 0.6 },
                        { transform: `translate(calc(-50% + ${dx * 1.15}px), calc(-50% + ${dy + 30}px)) scale(0.8)`, opacity: 0 }
                    ], { duration: 850 + Math.random() * 250, easing: 'cubic-bezier(.2,.8,.3,1)', fill: 'forwards' });
                }
            }
            setTimeout(() => layer.remove(), 1300);
        }
    };

    // Auto-initialise on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AtgEmojiEngine.init());
    } else {
        AtgEmojiEngine.init();
    }

    window.AtgEmojiEngine = AtgEmojiEngine;

})(window, document);
