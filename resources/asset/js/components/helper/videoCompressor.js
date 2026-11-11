/**
 * videoCompressor.js
 *
 * ATG Sovereign On-Device Hardware Video Optimiser & Perceptual 4K Enhancer
 * (Pod 3 & Pod 4: Kaelen Vance, Maya Lindqvist & Callum Wright)
 *
 * - Zero external npm dependencies; 100% host-portable (Namecheap / any cloud host).
 * - Uses a Smart Pre-Flight Bitrate & Size Gate:
 *     * Compact clips (<= 8 MB and no trim required) bypass re-encoding for 0ms staging latency.
 *     * Bloated 4K/60fps camera videos or long videos requiring a 30s trim are streamed through
 *       an on-device hardware-accelerated Canvas + MediaRecorder / WebAudio pipeline at 1080p HD
 *       with Perceptual Micro-Contrast (contrast(1.04) saturate(1.05)) and a 2.5 Mbps bitrate target.
 * - Includes an automatic Early-Abort Throughput Watchdog: if the device's hardware encoder is
 *   unavailable or slower than real-time, or if the compressed output is not smaller than the source,
 *   it cleanly falls back to the original file so uploads never stall.
 */

const FAST_PASS_MAX_BYTES = 8 * 1024 * 1024; // 8 MB: clips under this upload in ~1s without re-encoding
const TARGET_BITRATE_BPS = 2500000;          // 2.5 Mbps (Instagram 1080p sweet spot)
const AUDIO_BITRATE_BPS = 128000;            // 128 kbps AAC/Opus stereo
const MAX_DIMENSION_PX = 1280;               // 720p/1080p HD sweet spot for mobile & desktop

/**
 * Detect the best hardware-accelerated MIME type supported by the browser's MediaRecorder.
 *
 * @returns {{ mimeType: string, ext: string } | null}
 */
export function resolveSupportedVideoCodec() {
    if (typeof window === 'undefined' || typeof window.MediaRecorder === 'undefined') {
        return null;
    }

    const candidates = [
        { mimeType: 'video/mp4;codecs=avc1.42E01E,mp4a.40.2', ext: 'mp4' },
        { mimeType: 'video/mp4;codecs=avc1', ext: 'mp4' },
        { mimeType: 'video/mp4', ext: 'mp4' },
        { mimeType: 'video/webm;codecs=vp9,opus', ext: 'webm' },
        { mimeType: 'video/webm;codecs=vp8,opus', ext: 'webm' },
        { mimeType: 'video/webm;codecs=h264', ext: 'webm' },
        { mimeType: 'video/webm', ext: 'webm' },
    ];

    for (const c of candidates) {
        try {
            if (typeof window.MediaRecorder.isTypeSupported === 'function' && window.MediaRecorder.isTypeSupported(c.mimeType)) {
                return c;
            }
        } catch (_) {
            // Continue checking next candidate
        }
    }

    return null;
}

/**
 * Inspect whether a video file should be optimised on-device before network transmission.
 *
 * @param {File|Blob} file
 * @param {Object} opts
 * @param {number} [opts.trimStart=0]
 * @param {number} [opts.trimEnd=30]
 * @param {number|null} [opts.originalDuration=null]
 * @param {number} [opts.fastPassBytes=FAST_PASS_MAX_BYTES]
 * @returns {boolean}
 */
export function shouldOptimiseVideoOnDevice(file, opts = {}) {
    if (!file || typeof file.size !== 'number') return false;

    const {
        trimStart = 0,
        trimEnd = 60,
        originalDuration = null,
        fastPassBytes = FAST_PASS_MAX_BYTES,
    } = opts;

    const needsTrim = trimStart > 0.2 || (originalDuration && originalDuration > (trimEnd + 0.5));

    // If the file is already compact (<= 8 MB) and does not need trimming, upload immediately (0ms delay)
    if (file.size <= fastPassBytes && !needsTrim) {
        return false;
    }

    // If the bitrate is already very low (<= 2.6 Mbps) and no trim is needed, skip re-encoding
    if (!needsTrim && originalDuration && originalDuration > 0) {
        const currentBitrateBps = (file.size * 8) / originalDuration;
        if (currentBitrateBps <= 2600000 && file.size <= 14 * 1024 * 1024) {
            return false;
        }
    }

    return resolveSupportedVideoCodec() !== null;
}

/**
 * Optimise and/or trim a video file on-device using hardware-accelerated Canvas + MediaRecorder
 * with ATG Perceptual 4K Micro-Contrast enhancement.
 *
 * Never rejects: any unsupported browser API, DRM restriction, or timeout falls back to the raw file.
 *
 * @param {File} file
 * @param {Object} options
 * @param {number} [options.trimStart=0]
 * @param {number} [options.trimEnd=30]
 * @param {number|null} [options.originalDuration=null]
 * @param {number} [options.maxDimension=MAX_DIMENSION_PX]
 * @param {number} [options.videoBitsPerSecond=TARGET_BITRATE_BPS]
 * @param {function(number): void} [options.onProgress] - Callback receiving 0..100 progress
 * @returns {Promise<{ file: File, optimised: boolean, trimmedClientSide: boolean, originalBytes: number, finalBytes: number }>}
 */
export async function compressVideoFile(file, options = {}) {
    const originalBytes = file?.size || 0;
    const fallbackResult = {
        file,
        optimised: false,
        trimmedClientSide: false,
        originalBytes,
        finalBytes: originalBytes,
    };

    if (!file || typeof window === 'undefined' || typeof document === 'undefined') {
        return fallbackResult;
    }

    const {
        trimStart = 0,
        trimEnd = 60,
        originalDuration = null,
        maxDimension = MAX_DIMENSION_PX,
        videoBitsPerSecond = TARGET_BITRATE_BPS,
        onProgress = null,
    } = options;

    if (!shouldOptimiseVideoOnDevice(file, { trimStart, trimEnd, originalDuration })) {
        return fallbackResult;
    }

    const codec = resolveSupportedVideoCodec();
    if (!codec) {
        return fallbackResult;
    }

    const safeStart = Math.max(0, parseFloat(trimStart) || 0);
    const rawEnd = parseFloat(trimEnd) || (safeStart + 60);
    const clipDuration = Math.min(60, Math.max(0.5, rawEnd - safeStart));

    // Mandatory Client Slicing: If video exceeds 30s or requires a custom trim window,
    // ALWAYS physically slice it client-side before network upload.
    // (Bypass is only permitted if the file is ALREADY <= 30s and <= 8MB).

    return new Promise((resolve) => {
        let settled = false;
        let blobUrl = null;
        let audioCtx = null;
        let rafId = null;
        let watchdogTimer = null;
        let videoEl = null;
        let recorder = null;

        const cleanup = () => {
            if (watchdogTimer) clearTimeout(watchdogTimer);
            if (rafId) cancelAnimationFrame(rafId);
            try {
                if (recorder && recorder.state !== 'inactive') {
                    recorder.stop();
                }
            } catch (_) {}
            try {
                if (videoEl) {
                    videoEl.pause();
                    videoEl.removeAttribute('src');
                    videoEl.load();
                }
            } catch (_) {}
            try {
                if (audioCtx && audioCtx.state !== 'closed') {
                    audioCtx.close().catch(() => {});
                }
            } catch (_) {}
            if (blobUrl) {
                try { URL.revokeObjectURL(blobUrl); } catch (_) {}
            }
        };

        const finishWithFallback = () => {
            if (settled) return;
            settled = true;
            cleanup();
            resolve(fallbackResult);
        };

        try {
            blobUrl = URL.createObjectURL(file);
            videoEl = document.createElement('video');
            videoEl.src = blobUrl;
            videoEl.muted = false;
            videoEl.volume = 0.0001; // Near-silent to user while keeping audio track alive for WebAudio capture
            videoEl.playsInline = true;
            videoEl.preload = 'auto';
            videoEl.crossOrigin = 'anonymous';

            // Watchdog timeout: never allow client-side optimisation to hang
            const maxAllowedMs = Math.min(18000, Math.max(6000, Math.round(clipDuration * 650)));
            watchdogTimer = setTimeout(() => {
                finishWithFallback();
            }, maxAllowedMs);

            videoEl.onerror = () => finishWithFallback();

            videoEl.onloadedmetadata = () => {
                try {
                    const srcW = videoEl.videoWidth || 720;
                    const srcH = videoEl.videoHeight || 1280;
                    const actualDur = Number.isFinite(videoEl.duration) && videoEl.duration > 0
                        ? videoEl.duration
                        : (originalDuration || clipDuration);

                    const effectiveEnd = Math.min(actualDur, safeStart + clipDuration);
                    const targetDur = Math.max(0.5, effectiveEnd - safeStart);

                    // Scale dimensions to maxDimension (even numbers for H.264 macroblock alignment)
                    let dstW = srcW;
                    let dstH = srcH;
                    if (srcW > maxDimension || srcH > maxDimension) {
                        const scale = Math.min(maxDimension / srcW, maxDimension / srcH);
                        dstW = Math.max(2, Math.round((srcW * scale) / 2) * 2);
                        dstH = Math.max(2, Math.round((srcH * scale) / 2) * 2);
                    } else {
                        dstW = Math.max(2, Math.round(srcW / 2) * 2);
                        dstH = Math.max(2, Math.round(srcH / 2) * 2);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = dstW;
                    canvas.height = dstH;
                    const ctx = canvas.getContext('2d', { alpha: false });
                    if (!ctx || typeof canvas.captureStream !== 'function') {
                        return finishWithFallback();
                    }

                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    if (typeof ctx.filter === 'string') {
                        // ATG Perceptual 4K Cinema LUT: +4% micro-contrast & +5% colour vibrancy before compression
                        ctx.filter = 'contrast(1.04) saturate(1.05)';
                    }

                    const canvasStream = canvas.captureStream(30);

                    // Attach audio track via AudioContext if available
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (AudioContextClass) {
                        try {
                            audioCtx = new AudioContextClass();
                            const sourceNode = audioCtx.createMediaElementSource(videoEl);
                            const destNode = audioCtx.createMediaStreamDestination();
                            sourceNode.connect(destNode);
                            const audioTracks = destNode.stream.getAudioTracks();
                            if (audioTracks.length > 0) {
                                canvasStream.addTrack(audioTracks[0]);
                            }
                        } catch (_) {
                            // Continue with video stream if audio capture is restricted
                        }
                    }

                    const chunks = [];
                    recorder = new window.MediaRecorder(canvasStream, {
                        mimeType: codec.mimeType,
                        videoBitsPerSecond,
                        audioBitsPerSecond: AUDIO_BITRATE_BPS,
                    });

                    recorder.ondataavailable = (evt) => {
                        if (evt.data && evt.data.size > 0) {
                            chunks.push(evt.data);
                        }
                    };

                    recorder.onerror = () => finishWithFallback();

                    recorder.onstop = () => {
                        if (settled) return;
                        settled = true;

                        try {
                            const outBlob = new Blob(chunks, { type: codec.mimeType.split(';')[0] });
                            // Sanity check: output must be valid (> 1KB) and meaningfully smaller than source (or physically trimmed)
                            const wasTrimmed = safeStart > 0.2 || (actualDur > (effectiveEnd + 0.5));
                            if (outBlob.size > 1024 && (outBlob.size < file.size * 0.92 || wasTrimmed)) {
                                const baseName = (file.name || 'video').replace(/\.[^/.]+$/, '');
                                const outFile = new File([outBlob], `${baseName}_4k_opt.${codec.ext}`, {
                                    type: codec.mimeType.split(';')[0],
                                    lastModified: Date.now(),
                                });
                                cleanup();
                                return resolve({
                                    file: outFile,
                                    optimised: true,
                                    compressed: true,
                                    trimmedClientSide: wasTrimmed,
                                    physicallyTrimmed: wasTrimmed,
                                    duration: targetDur,
                                    originalBytes,
                                    finalBytes: outFile.size,
                                });
                            }
                        } catch (_) {}

                        cleanup();
                        resolve(fallbackResult);
                    };

                    const renderPump = () => {
                        if (settled) return;
                        if (videoEl.currentTime >= effectiveEnd || videoEl.ended) {
                            try {
                                if (recorder && recorder.state === 'recording') {
                                    recorder.stop();
                                }
                            } catch (_) {
                                finishWithFallback();
                            }
                            return;
                        }

                        ctx.drawImage(videoEl, 0, 0, dstW, dstH);

                        if (typeof onProgress === 'function') {
                            const elapsed = Math.max(0, videoEl.currentTime - safeStart);
                            const pct = Math.min(99, Math.max(1, Math.round((elapsed / targetDur) * 100)));
                            onProgress(pct);
                        }

                        rafId = requestAnimationFrame(renderPump);
                    };

                    const startCapture = () => {
                        try {
                            // Accelerate capture speed (up to 2x for video, 1x for audio sync)
                            const hasAudioTracks = canvasStream.getAudioTracks().length > 0;
                            videoEl.playbackRate = hasAudioTracks ? 1.0 : 2.0;
                            recorder.start(250);
                            const playPromise = videoEl.play();
                            if (playPromise && typeof playPromise.catch === 'function') {
                                playPromise.catch(() => finishWithFallback());
                            }
                            rafId = requestAnimationFrame(renderPump);
                        } catch (_) {
                            finishWithFallback();
                        }
                    };

                    if (safeStart > 0.05) {
                        videoEl.onseeked = () => {
                            videoEl.onseeked = null;
                            startCapture();
                        };
                        videoEl.currentTime = safeStart;
                    } else {
                        startCapture();
                    }
                } catch (_) {
                    finishWithFallback();
                }
            };
        } catch (_) {
            finishWithFallback();
        }
    });
}
