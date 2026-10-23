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
    const previouslyFocused = document.activeElement;

    const root = el('div', 'ghost-cam', { role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Camera with your Then photo faintly overlaid' });
    const stage = el('div', 'ghost-cam__stage');
    const video = el('video', 'ghost-cam__video', { autoplay: '', playsinline: '', muted: '' });
    video.muted = true;
    const ghost = el('img', 'ghost-cam__ghost', { alt: '' });
    ghost.src = ghostUrl;
    const notice = el('p', 'ghost-cam__notice', { role: 'status' });
    stage.append(video, ghost, notice);

    const bar = el('div', 'ghost-cam__bar');
    const opacityLabel = el('label', 'ghost-cam__opacity');
    opacityLabel.textContent = 'Ghost ';
    const opacity = el('input', '', { type: 'range', min: '10', max: '80', value: '40', 'aria-label': 'Ghost photo strength' });
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
    opacity.addEventListener('input', () => { ghost.style.opacity = String(Number(opacity.value) / 100); });
    ghost.style.opacity = '0.4';
    flip.addEventListener('click', () => { facing = facing === 'environment' ? 'user' : 'environment'; start(); });
    close.addEventListener('click', () => finish(null));
    shutter.addEventListener('click', () => {
        const w = video.videoWidth;
        const h = video.videoHeight;
        if (!w || !h) return;
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        canvas.getContext('2d')?.drawImage(video, 0, 0, w, h);
        canvas.toBlob((blob) => {
            finish(blob ? new File([blob], `now-${Date.now()}.jpg`, { type: 'image/jpeg' }) : null);
        }, 'image/jpeg', 0.9);
    });

    start();
    shutter.focus();
});
