/**
 * "Ghost" camera for Then & Now (RFC-0003): live camera preview with the Then photo
 * faintly laid over it, so the Now photo can be framed and posed to match.
 * Nothing leaves the device here; the captured frame is handed back as a File and goes
 * through the normal upload path.
 */

const el = (tag, className, attrs = {}) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    Object.entries(attrs).forEach(([k, v]) => node.setAttribute(k, v));
    return node;
};

/**
 * @param {File} thenFile the Then photo used as the ghost
 * @returns {Promise<File|null>} the captured Now photo, or null when closed without a capture
 */
export const openGhostCamera = (thenFile) => new Promise((resolve) => {
    if (!thenFile || !navigator.mediaDevices?.getUserMedia) {
        resolve(null);
        return;
    }

    const ghostUrl = URL.createObjectURL(thenFile);
    let stream = null;
    let facing = 'environment';
    let settled = false;
    let mirrored = false;
    let gridVisible = true;
    let timerSeconds = 0; // 0s or 3s countdown
    let countdownActive = false;
    const previouslyFocused = document.activeElement;

    const root = el('div', 'ghost-cam', { role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Camera with your Then photo faintly overlaid' });
    const stage = el('div', 'ghost-cam__stage');
    const video = el('video', 'ghost-cam__video', { autoplay: '', playsinline: '', muted: '' });
    video.muted = true;
    const ghost = el('img', 'ghost-cam__ghost', { alt: '' });
    ghost.src = ghostUrl;

    // Pose-Lock Rule-of-Thirds & Eye-Line Horizon Grid Overlay
    const gridOverlay = el('div', 'ghost-cam__grid', { 'aria-hidden': 'true' });
    gridOverlay.innerHTML = `
        <span class="ghost-cam__grid-line ghost-cam__grid-line--v1"></span>
        <span class="ghost-cam__grid-line ghost-cam__grid-line--v2"></span>
        <span class="ghost-cam__grid-line ghost-cam__grid-line--h1"></span>
        <span class="ghost-cam__grid-line ghost-cam__grid-line--h2"></span>
        <span class="ghost-cam__eye-guide">👁️ Align eyes &amp; shoulders</span>
    `;

    // Big Countdown Badge for 3s hands-free pose matching
    const countdownBadge = el('div', 'ghost-cam__countdown d-none', { 'aria-live': 'assertive' });

    const notice = el('p', 'ghost-cam__notice', { role: 'status' });

    // Top studio tools pill bar (Grid toggle, Mirror toggle, 3s Timer toggle, Hold-to-Blink compare)
    const topTools = el('div', 'ghost-cam__top-tools');
    const timerBtn = el('button', 'ghost-cam__pill-btn', { type: 'button', 'aria-label': 'Toggle 3-second countdown timer', title: '3s Countdown Timer' });
    timerBtn.innerHTML = '<i class="bi bi-stopwatch"></i> <span>Timer: Off</span>';

    const gridBtn = el('button', 'ghost-cam__pill-btn is-active', { type: 'button', 'aria-label': 'Toggle pose alignment grid', title: 'Pose Alignment Grid' });
    gridBtn.innerHTML = '<i class="bi bi-grid-3x3"></i> <span>Pose Grid</span>';

    const mirrorBtn = el('button', 'ghost-cam__pill-btn', { type: 'button', 'aria-label': 'Mirror live camera horizontally', title: 'Mirror Camera' });
    mirrorBtn.innerHTML = '<i class="bi bi-symmetry-vertical"></i> <span>Mirror</span>';

    const blinkBtn = el('button', 'ghost-cam__pill-btn', { type: 'button', 'aria-label': 'Hold to flash 100% Then photo', title: 'Hold to inspect Then photo' });
    blinkBtn.innerHTML = '<i class="bi bi-eye-fill"></i> <span>Hold: Then</span>';

    topTools.append(timerBtn, gridBtn, mirrorBtn, blinkBtn);
    stage.append(video, ghost, gridOverlay, countdownBadge, topTools, notice);

    const bar = el('div', 'ghost-cam__bar');
    const opacityLabel = el('label', 'ghost-cam__opacity');
    opacityLabel.textContent = 'Ghost ';
    const opacity = el('input', '', { type: 'range', min: '10', max: '85', value: '42', 'aria-label': 'Ghost photo strength' });
    opacityLabel.append(opacity);
    const flip = el('button', 'ghost-cam__btn', { type: 'button', 'aria-label': 'Switch camera' });
    flip.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
    const shutter = el('button', 'ghost-cam__btn ghost-cam__shutter', { type: 'button', 'aria-label': 'Take photo' });
    shutter.innerHTML = '<i class="bi bi-camera-fill"></i>';
    const close = el('button', 'ghost-cam__btn', { type: 'button', 'aria-label': 'Close camera' });
    close.innerHTML = '<i class="bi bi-x-lg"></i>';
    bar.append(opacityLabel, flip, shutter, close);
    root.append(stage, bar);
    document.body.appendChild(root);
    document.body.classList.add('ghost-cam-open');

    const stopStream = () => {
        stream?.getTracks().forEach((t) => t.stop());
        stream = null;
        video.srcObject = null;
    };

    const finish = (file) => {
        if (settled) return;
        settled = true;
        stopStream();
        URL.revokeObjectURL(ghostUrl);
        document.removeEventListener('keydown', onKey, true);
        root.remove();
        document.body.classList.remove('ghost-cam-open');
        previouslyFocused?.focus?.();
        resolve(file);
    };

    const onKey = (e) => {
        if (e.key === 'Escape') {
            e.stopPropagation();
            finish(null);
        }
    };
    document.addEventListener('keydown', onKey, true);

    const start = async () => {
        stopStream();
        notice.textContent = '';
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: facing }, width: { ideal: 1920 }, height: { ideal: 1440 } },
                audio: false,
            });
            if (settled) {
                stopStream();
                return;
            }
            video.srcObject = stream;
            await video.play().catch(() => {});
        } catch (err) {
            shutter.disabled = true;
            notice.textContent = err?.name === 'NotAllowedError'
                ? 'Camera access was blocked. Allow it in your browser settings, or close this and pick a photo instead.'
                : 'No camera available here. Close this and pick a photo instead.';
        }
    };

    video.addEventListener('loadedmetadata', () => {
        if (video.videoWidth && video.videoHeight) {
            stage.style.aspectRatio = `${video.videoWidth} / ${video.videoHeight}`;
        }
    });

    opacity.addEventListener('input', () => {
        ghost.style.opacity = String(Number(opacity.value) / 100);
    });
    ghost.style.opacity = '0.42';

    // Studio tool controls
    timerBtn.addEventListener('click', () => {
        timerSeconds = timerSeconds === 0 ? 3 : 0;
        timerBtn.classList.toggle('is-active', timerSeconds > 0);
        timerBtn.innerHTML = `<i class="bi bi-stopwatch"></i> <span>Timer: ${timerSeconds > 0 ? '3s' : 'Off'}</span>`;
    });

    gridBtn.addEventListener('click', () => {
        gridVisible = !gridVisible;
        gridBtn.classList.toggle('is-active', gridVisible);
        gridOverlay.style.display = gridVisible ? '' : 'none';
    });

    mirrorBtn.addEventListener('click', () => {
        mirrored = !mirrored;
        mirrorBtn.classList.toggle('is-active', mirrored);
        video.style.transform = mirrored ? 'scaleX(-1)' : '';
    });

    const showFullThen = () => { ghost.style.opacity = '0.96'; };
    const restoreThen = () => { ghost.style.opacity = String(Number(opacity.value) / 100); };
    blinkBtn.addEventListener('pointerdown', showFullThen);
    blinkBtn.addEventListener('pointerup', restoreThen);
    blinkBtn.addEventListener('pointerleave', restoreThen);
    blinkBtn.addEventListener('pointercancel', restoreThen);

    flip.addEventListener('click', () => {
        facing = facing === 'environment' ? 'user' : 'environment';
        start();
    });
    close.addEventListener('click', () => finish(null));

    const captureFrameNow = () => {
        const w = video.videoWidth;
        const h = video.videoHeight;
        if (!w || !h) return;
        if (typeof window.triggerHaptic === 'function') window.triggerHaptic('impact');
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        if (ctx) {
            if (mirrored) {
                ctx.translate(w, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0, w, h);
        }
        canvas.toBlob((blob) => {
            finish(blob ? new File([blob], `now-${Date.now()}.jpg`, { type: 'image/jpeg' }) : null);
        }, 'image/jpeg', 0.92);
    };

    shutter.addEventListener('click', () => {
        if (countdownActive || !video.videoWidth) return;
        if (timerSeconds <= 0) {
            captureFrameNow();
            return;
        }
        countdownActive = true;
        let remaining = timerSeconds;
        countdownBadge.textContent = String(remaining);
        countdownBadge.classList.remove('d-none');
        if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');

        const tick = setInterval(() => {
            if (settled) {
                clearInterval(tick);
                return;
            }
            remaining -= 1;
            if (remaining > 0) {
                countdownBadge.textContent = String(remaining);
                if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
            } else {
                clearInterval(tick);
                countdownBadge.classList.add('d-none');
                countdownActive = false;
                captureFrameNow();
            }
        }, 1000);
    });

    start();
    shutter.focus();
});
