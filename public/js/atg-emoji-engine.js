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
         * Bind 60fps 3D micro-tilt physics to all 3D emojis across the page.
         */
        bindMicroTilt: function () {
            document.querySelectorAll('.atg-3d-emoji, [data-atg-3d="true"]').forEach(el => {
                if (el.dataset.tiltBound) return;
                el.dataset.tiltBound = 'true';

                el.addEventListener('mousemove', function (e) {
                    const rect = this.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    const dx = (e.clientX - cx) / (rect.width / 2);
                    const dy = (e.clientY - cy) / (rect.height / 2);

                    const tiltX = Math.max(-25, Math.min(25, dy * -20));
                    const tiltY = Math.max(-25, Math.min(25, dx * 20));

                    this.style.transform = `perspective(600px) rotateX(${tiltX.toFixed(1)}deg) rotateY(${tiltY.toFixed(1)}deg) scale3d(1.1, 1.1, 1.1) translateZ(10px)`;
                });

                el.addEventListener('mouseleave', function () {
                    this.style.transform = 'perspective(600px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1) translateZ(0)';
                });
            });
        },

        /**
         * Initialise Studio interactions if studio container is present.
         */
        initStudio: function () {
            const viewport = document.getElementById('atgAvatarViewport');
            if (!viewport) return;

            this.bindViewportPhysics(viewport);
            this.bindTraitSelectors();
            this.bindStudioActions();
            this.bindEmojiCardClicks();
        },

        /**
         * Interactive drag-to-rotate with spring momentum on the live 3D avatar viewport.
         */
        bindViewportPhysics: function (viewport) {
            const self = this;
            const svgEl = viewport.querySelector('.atg-avatar-svg');

            function updateRotation() {
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

                self.physics.targetRotY = Math.max(-45, Math.min(45, deltaX * 0.4));
                self.physics.targetRotX = Math.max(-35, Math.min(35, -deltaY * 0.4));
            }

            function stopDrag() {
                if (!self.physics.isDragging) return;
                self.physics.isDragging = false;
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

                    // Update active pill state
                    document.querySelectorAll(`.atg-trait-pill[data-trait-group="${group}"]`).forEach(p => p.classList.remove('active'));
                    this.classList.add('active');

                    self.state[group] = value;
                    self.refreshAvatarPreview();
                });
            });
        },

        /**
         * Re-render avatar preview in real time.
         */
        refreshAvatarPreview: function () {
            const viewport = document.getElementById('atgAvatarViewport');
            if (!viewport) return;

            fetch('/api/emoji/render-avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    skin_tone: this.state.skin_tone,
                    hairstyle: this.state.hairstyle,
                    expression: this.state.expression,
                    accessory: this.state.accessory,
                    backdrop: this.state.backdrop,
                    size: 256
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.svg) {
                    viewport.innerHTML = data.svg;
                    const svgEl = viewport.querySelector('.atg-avatar-svg');
                    if (svgEl) {
                        svgEl.style.transform = `perspective(800px) rotateX(${this.physics.rotX.toFixed(2)}deg) rotateY(${this.physics.rotY.toFixed(2)}deg) scale3d(1.04, 1.04, 1.04)`;
                    }
                }
            })
            .catch(err => console.error('Failed to update avatar preview:', err));
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
            const groups = ['skin_tone', 'hairstyle', 'expression', 'accessory', 'backdrop'];
            groups.forEach(group => {
                const pills = Array.from(document.querySelectorAll(`.atg-trait-pill[data-trait-group="${group}"]`));
                if (pills.length > 0) {
                    const randomPill = pills[Math.floor(Math.random() * pills.length)];
                    randomPill.click();
                }
            });
        },

        /**
         * Save current procedural 3D avatar as user's profile photo.
         */
        saveProfileAvatar: function (btn) {
            const viewport = document.getElementById('atgAvatarViewport');
            const svgEl = viewport ? viewport.querySelector('svg') : null;
            if (!svgEl) return;

            // Start tactile spinner
            if (window.ScoreTenantSpinner) {
                window.ScoreTenantSpinner.start(btn);
            }

            // Convert SVG to high-res PNG on offscreen canvas
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 512;
            const ctx = canvas.getContext('2d');

            const svgString = new XMLSerializer().serializeToString(svgEl);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            const img = new Image();

            img.onload = () => {
                ctx.drawImage(img, 0, 0, 512, 512);
                URL.revokeObjectURL(url);
                const pngDataUrl = canvas.toDataURL('image/png');

                // Read CSRF Token from DOM or meta
                const csrfToken = document.querySelector('input[name="token"]')?.value ||
                                  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch('/api/emoji/save-avatar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        token: csrfToken,
                        csrf_token: csrfToken,
                        skin_tone: this.state.skin_tone,
                        hairstyle: this.state.hairstyle,
                        expression: this.state.expression,
                        accessory: this.state.accessory,
                        backdrop: this.state.backdrop,
                        png_base64: pngDataUrl
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (window.ScoreTenantSpinner) {
                        window.ScoreTenantSpinner.stop(btn);
                    }

                    if (data.success) {
                        // Instant client preview across all page avatars
                        const newUrl = data.url + '?v=' + Date.now();
                        document.querySelectorAll('.avatar.profileImg, #profilePreview, #pfViewerThumb, #pfViewerMainImg').forEach(img => {
                            img.src = newUrl;
                        });

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
                    if (window.ScoreTenantSpinner) {
                        window.ScoreTenantSpinner.stop(btn);
                    }
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

            img.onerror = () => {
                if (window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(btn);
                }
                URL.revokeObjectURL(url);
                alert('Could not render image canvas.');
            };

            img.src = url;
        },

        /**
         * Handle clicking on 3D emojis in the catalog to copy or insert.
         */
        bindEmojiCardClicks: function () {
            document.querySelectorAll('.atg-emoji-card').forEach(card => {
                card.addEventListener('click', function () {
                    const emojiId = this.dataset.emojiId;
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
                                    timer: 2000,
                                    timerProgressBar: true
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: `Copied ${emojiTitle} (${shortcode})`
                                });
                            }
                        });
                    }
                });
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
