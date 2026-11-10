// Import helper functions and components
import { showEmojiPicker, initEmojiPickerUX } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';
import { parseVideoUrl } from './videoParser';
import axios from 'axios';
import Swal from 'sweetalert2';
import { guessPhotoYear } from '../helper/photoDate';
import { openGhostCamera } from './thenNowGhostCamera';
import { FAMILY_GIFS, isAllowedGifUrl } from './gifCatalog';
import { compressVideoFile, shouldOptimiseVideoOnDevice } from '../helper/videoCompressor';

// Helper to smooth scroll an element into view within the scrollable modal body
const scrollDrawerIntoView = (el) => {
    if (!el) return;
    try {
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } catch (_) {
        el.scrollIntoView(false);
    }
};

// ----------------------------------------------------
// 1. EMOJI PICKER (In-Flow Composer Drawer UX)
// ----------------------------------------------------
const emojiToggle = document.getElementById('emojiPost');
const emojiDrawer = document.getElementById('emojiPickerDrawer') || document.getElementById('emojiPickerContainer');
const closeEmojiBtn = document.getElementById('closeEmojiDrawerBtn') || document.getElementById('closeEmojiPicker');

if (document.getElementById('emojiListPost')) {
    showEmojiPicker('emojiListPost', 'data-emoji-target');
}

export const closeEmojiDrawer = () => {
    if (!emojiDrawer) return;
    emojiDrawer.classList.add('d-none');
    if (emojiToggle) {
        emojiToggle.classList.remove('emoji-active');
        emojiToggle.setAttribute('aria-expanded', 'false');
    }
};

export const openEmojiDrawer = () => {
    if (!emojiDrawer) return;
    if (typeof window.closeAllComposerDrawers === 'function') {
        window.closeAllComposerDrawers('emojiPickerDrawer');
    }
    if (typeof closeGifDrawer === 'function') closeGifDrawer();
    const videoDrawer = document.getElementById('videoEmbedContainer');
    if (videoDrawer) videoDrawer.classList.add('d-none');
    const tagDrawer = document.getElementById('tagPeopleContainer');
    if (tagDrawer) tagDrawer.classList.add('d-none');
    const pollDrawer = document.getElementById('pollCreationContainer');
    if (pollDrawer) pollDrawer.classList.add('d-none');

    emojiDrawer.classList.remove('d-none');
    if (emojiToggle) {
        emojiToggle.classList.add('emoji-active');
        emojiToggle.setAttribute('aria-expanded', 'true');
    }
    scrollDrawerIntoView(emojiDrawer);
};

if (emojiToggle && emojiDrawer) {
    emojiToggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isHidden = emojiDrawer.classList.contains('d-none');
        if (isHidden) {
            openEmojiDrawer();
        } else {
            closeEmojiDrawer();
        }
    });
}

if (closeEmojiBtn) {
    closeEmojiBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeEmojiDrawer();
    });
}

// ----------------------------------------------------
// 2. IMAGE PREVIEW
// ----------------------------------------------------
if (document.getElementById('imageUpload')) {
    imagePreview('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');
}

// ----------------------------------------------------
// 3. POLL CREATION UI LOGIC
// ----------------------------------------------------
const addPollBtn = document.getElementById('addPollBtn');
const pollContainer = document.getElementById('pollCreationContainer');
const addOptionBtn = document.getElementById('addPollOptionBtn');
const optionsContainer = document.getElementById('pollOptionsContainer');
const removePollBtn = document.getElementById('removePollBtn');

const MAX_POLL_OPTIONS = 6;

const closePoll = () => {
    if (!pollContainer) return;
    pollContainer.classList.add('d-none');
    if (addPollBtn) addPollBtn.classList.remove('poll-active');
    // Reset to two blank options
    pollContainer.querySelectorAll('input').forEach(input => (input.value = ''));
    if (optionsContainer) {
        const extras = optionsContainer.querySelectorAll('.poll-builder__option');
        extras.forEach((el, i) => { if (i > 1) el.remove(); });
    }
};

if (addPollBtn && pollContainer) {
    addPollBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = pollContainer.classList.contains('d-none');
        if (isHidden) {
            if (typeof window.closeAllComposerDrawers === 'function') {
                window.closeAllComposerDrawers('pollCreationContainer');
            }
            pollContainer.classList.remove('d-none');
            addPollBtn.classList.add('poll-active');
            scrollDrawerIntoView(pollContainer);
            const questionInput = pollContainer.querySelector('input[name="poll_question"]');
            if (questionInput) setTimeout(() => questionInput.focus(), 60);
        } else {
            closePoll();
        }
    });

    if (removePollBtn) {
        removePollBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closePoll();
        });
    }

    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const current = optionsContainer.querySelectorAll('.poll-builder__option').length;
            if (current >= MAX_POLL_OPTIONS) return;

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'poll_options[]';
            input.className = 'poll-builder__option';
            input.placeholder = `Option ${current + 1}`;
            input.maxLength = 80;
            optionsContainer.appendChild(input);
            input.focus();

            if (current + 1 >= MAX_POLL_OPTIONS) {
                addOptionBtn.style.display = 'none';
            }
        });
    }
}

// Quick Trivia Starters helper
window.applyTriviaTemplate = (type) => {
    if (!pollContainer) return;
    if (pollContainer.classList.contains('d-none')) {
        pollContainer.classList.remove('d-none');
        if (addPollBtn) addPollBtn.classList.add('poll-active');
        scrollDrawerIntoView(pollContainer);
    }
    const questionInput = pollContainer.querySelector('input[name="poll_question"]');
    const options = pollContainer.querySelectorAll('input[name="poll_options[]"]');

    if (type === 'guess_who') {
        if (questionInput) questionInput.value = '👶 Guess who said or did this as a toddler?';
        if (options[0]) options[0].value = 'Mum / Dad';
        if (options[1]) options[1].value = 'Siblings / Cousins';
    } else if (type === 'family_lore') {
        if (questionInput) questionInput.value = '🧠 Who remembers what happened at the family reunion?';
        if (options[0]) options[0].value = 'The legendary barbecue mishap';
        if (options[1]) options[1].value = 'The surprise dance off';
    }
};

window.__resetPollBuilder = closePoll;

// ----------------------------------------------------
// 3b. THEN & NOW (before / after) BUILDER — RFC-0002
// ----------------------------------------------------
const addThenNowBtn = document.getElementById('addThenNowBtn');
const thenNowContainer = document.getElementById('thenNowContainer');
const thenNowFlag = document.getElementById('thenNowFlag');
const thenNowReplyTo = document.getElementById('thenNowReplyTo');
const thenNowFiles = { then: null, now: null };
// In-flight EXIF read + compression per slot, so camera and Post can wait for a photo that is still being prepared.
const thenNowWork = { then: Promise.resolve(), now: Promise.resolve() };

const setThenNowSlot = (slot, file) => {
    thenNowFiles[slot] = file || null;
    const img = document.getElementById(`${slot}Preview`);
    const placeholder = document.getElementById(`${slot}Placeholder`);
    if (!img || !placeholder) return;
    if (img.src && img.src.startsWith('blob:')) URL.revokeObjectURL(img.src);
    if (file) {
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
        placeholder.classList.add('d-none');
    } else {
        img.removeAttribute('src');
        img.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }
};

const closeThenNow = () => {
    if (!thenNowContainer) return;
    thenNowContainer.classList.add('d-none');
    if (addThenNowBtn) addThenNowBtn.classList.remove('thennow-active');
    if (thenNowFlag) thenNowFlag.value = '';
    if (thenNowReplyTo) thenNowReplyTo.value = '';
    const modeInput = document.getElementById('thenNowModeInput');
    if (modeInput) modeInput.value = 'classic';
    const mysteryInput = document.getElementById('thenNowMysteryInput');
    if (mysteryInput) mysteryInput.value = '0';
    const mysteryBtn = document.getElementById('tnMysteryToggleBtn');
    if (mysteryBtn) {
        mysteryBtn.classList.remove('btn-dark', 'text-white');
        mysteryBtn.classList.add('btn-light');
        mysteryBtn.setAttribute('aria-pressed', 'false');
    }
    const voiceInput = document.getElementById('thenNowVoiceInput');
    if (voiceInput) voiceInput.value = '';
    const voicePreviewWrap = document.getElementById('tnVoicePreviewWrap');
    if (voicePreviewWrap) {
        voicePreviewWrap.classList.add('d-none');
        voicePreviewWrap.classList.remove('d-flex');
    }
    const archiveDrawer = document.getElementById('thenArchiveDrawer');
    if (archiveDrawer) archiveDrawer.classList.add('d-none');
    thenNowContainer.querySelectorAll('input[type="text"], input[type="file"]').forEach(i => (i.value = ''));
    setThenNowSlot('then', null);
    setThenNowSlot('now', null);
    const title = document.getElementById('thenNowTitle');
    if (title) title.textContent = 'Then & Now';
};

const openThenNow = (replyToPostNo = '') => {
    if (!thenNowContainer) return;
    thenNowContainer.classList.remove('d-none');
    if (addThenNowBtn) addThenNowBtn.classList.add('thennow-active');
    if (thenNowFlag) thenNowFlag.value = '1';
    if (thenNowReplyTo) thenNowReplyTo.value = replyToPostNo ? String(replyToPostNo) : '';
    const title = document.getElementById('thenNowTitle');
    if (title) title.textContent = replyToPostNo ? 'Add your Then & Now' : 'Then & Now';
    scrollDrawerIntoView(thenNowContainer);
};

if (addThenNowBtn && thenNowContainer) {
    addThenNowBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (thenNowContainer.classList.contains('d-none')) openThenNow();
        else closeThenNow();
    });
    document.getElementById('removeThenNowBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeThenNow();
    });
    const labelInput = (slot) => thenNowContainer.querySelector(`input[name="${slot}_label"]`);
    // Fill the year only when the member hasn't typed one; never overwrite their words.
    const suggestYear = (slot, year) => {
        const input = labelInput(slot);
        if (input && year && !input.value.trim()) {
            input.value = String(year);
            input.dataset.guessed = '1';
        }
    };

    // Mode switcher: Classic Time Travel vs Genetic Echo (Like Parent, Like Child)
    const modeInput = document.getElementById('thenNowModeInput');
    const classicBtn = document.getElementById('tnModeClassicBtn');
    const echoBtn = document.getElementById('tnModeEchoBtn');
    const setMode = (mode) => {
        if (modeInput) modeInput.value = mode;
        const thenIn = labelInput('then');
        const nowIn = labelInput('now');
        if (mode === 'echo') {
            classicBtn?.classList.remove('active');
            classicBtn?.classList.add('text-secondary');
            echoBtn?.classList.add('active');
            echoBtn?.classList.remove('text-secondary');
            if (thenIn) thenIn.placeholder = 'Parent, e.g. Mum at age 7 (1992)';
            if (nowIn) nowIn.placeholder = 'Child, e.g. Tobi at age 7 (2026)';
        } else {
            echoBtn?.classList.remove('active');
            echoBtn?.classList.add('text-secondary');
            classicBtn?.classList.add('active');
            classicBtn?.classList.remove('text-secondary');
            if (thenIn) thenIn.placeholder = 'Then, e.g. 1998';
            if (nowIn) nowIn.placeholder = 'Now, e.g. 2026';
        }
    };
    classicBtn?.addEventListener('click', () => setMode('classic'));
    echoBtn?.addEventListener('click', () => setMode('echo'));

    // "Guess the Year" Mystery Veil toggle
    const mysteryBtn = document.getElementById('tnMysteryToggleBtn');
    const mysteryInput = document.getElementById('thenNowMysteryInput');
    mysteryBtn?.addEventListener('click', () => {
        const active = mysteryInput?.value !== '1';
        if (mysteryInput) mysteryInput.value = active ? '1' : '0';
        mysteryBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
        mysteryBtn.classList.toggle('btn-dark', active);
        mysteryBtn.classList.toggle('text-white', active);
        mysteryBtn.classList.toggle('btn-light', !active);
    });

    // "Pick from Family Archive" quick drawer for the Then slot
    const archiveBtn = document.getElementById('thenArchivePickBtn');
    const archiveDrawer = document.getElementById('thenArchiveDrawer');
    const archiveGrid = document.getElementById('thenArchiveGrid');
    document.getElementById('closeThenArchiveBtn')?.addEventListener('click', () => {
        archiveDrawer?.classList.add('d-none');
    });
    archiveBtn?.addEventListener('click', async () => {
        if (!archiveDrawer || !archiveGrid) return;
        if (!archiveDrawer.classList.contains('d-none')) {
            archiveDrawer.classList.add('d-none');
            return;
        }
        archiveDrawer.classList.remove('d-none');
        archiveGrid.innerHTML = '<span class="small text-muted py-2 px-2">Loading your family archive photos…</span>';
        try {
            const uid = localStorage.getItem('requesterId') || 'me';
            const res = await fetch(`/api/members/${encodeURIComponent(uid)}/then-now-archive`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const body = res.ok ? await res.json() : null;
            const photos = Array.isArray(body?.data?.photos) ? body.data.photos : [];
            if (!photos.length) {
                archiveGrid.innerHTML = '<span class="small text-muted py-2 px-2">No older family archive photos found yet—pick one from your device above.</span>';
                return;
            }
            archiveGrid.innerHTML = '';
            photos.forEach((item) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn p-0 border rounded-3 overflow-hidden position-relative flex-shrink-0';
                btn.style.cssText = 'width: 72px; height: 72px; background: #0f172a;';
                btn.title = item.caption || (item.year ? `Photo from ${item.year}` : 'Select archive photo');
                const imgUrl = `/resources/images/post/${encodeURIComponent(item.img)}`;
                btn.innerHTML = `
                    <img src="${imgUrl}" alt="Archive photo" style="width:100%;height:100%;object-fit:cover;">
                    ${item.year ? `<span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-0 m-1" style="font-size:0.62rem;">${item.year}</span>` : ''}
                `;
                btn.addEventListener('click', async () => {
                    thenNowWork.then = (async () => {
                        try {
                            const imgResp = await fetch(imgUrl, { credentials: 'same-origin' });
                            const blob = await imgResp.blob();
                            const file = new File([blob], item.img || `then-${Date.now()}.jpg`, { type: blob.type || 'image/jpeg' });
                            setThenNowSlot('then', file);
                            if (item.year) suggestYear('then', item.year);
                            archiveDrawer.classList.add('d-none');
                        } catch (_) {}
                    })();
                });
                archiveGrid.appendChild(btn);
            });
        } catch (_) {
            archiveGrid.innerHTML = '<span class="small text-muted py-2 px-2">Could not load archive photos.</span>';
        }
    });

    // 15-Second Voice Reflection Studio inside Then & Now
    const voiceBtn = document.getElementById('tnVoiceRecordBtn');
    const voiceLabel = document.getElementById('tnVoiceRecordLabel');
    const voiceStatus = document.getElementById('tnVoiceStatusText');
    const voicePreviewWrap = document.getElementById('tnVoicePreviewWrap');
    const voiceAudio = document.getElementById('tnVoiceAudioPreview');
    const voiceClearBtn = document.getElementById('tnVoiceClearBtn');
    const voiceHidden = document.getElementById('thenNowVoiceInput');
    let mediaRecorder = null;
    let voiceChunks = [];
    let voiceTimer = null;

    voiceClearBtn?.addEventListener('click', () => {
        if (voiceHidden) voiceHidden.value = '';
        if (voiceAudio) voiceAudio.removeAttribute('src');
        if (voicePreviewWrap) {
            voicePreviewWrap.classList.add('d-none');
            voicePreviewWrap.classList.remove('d-flex');
        }
        if (voiceLabel) voiceLabel.textContent = 'Add 15s Voice Story';
        if (voiceStatus) voiceStatus.textContent = 'Tell the story behind these years';
    });

    voiceBtn?.addEventListener('click', async () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            return;
        }
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            Swal.fire({ icon: 'info', title: 'Microphone unavailable', text: 'Voice recording is not supported in this browser.', confirmButtonColor: '#b8202c' });
            return;
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            voiceChunks = [];
            mediaRecorder = new MediaRecorder(stream);
            let secondsLeft = 15;
            if (voiceLabel) voiceLabel.textContent = `Stop (${secondsLeft}s)`;
            if (voiceStatus) voiceStatus.textContent = 'Recording your reflection…';

            voiceTimer = setInterval(() => {
                secondsLeft -= 1;
                if (secondsLeft > 0) {
                    if (voiceLabel) voiceLabel.textContent = `Stop (${secondsLeft}s)`;
                } else if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                }
            }, 1000);

            mediaRecorder.ondataavailable = (ev) => {
                if (ev.data && ev.data.size > 0) voiceChunks.push(ev.data);
            };
            mediaRecorder.onstop = () => {
                if (voiceTimer) clearInterval(voiceTimer);
                stream.getTracks().forEach((t) => t.stop());
                const blob = new Blob(voiceChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                const reader = new FileReader();
                reader.onloadend = () => {
                    if (voiceHidden && typeof reader.result === 'string') {
                        voiceHidden.value = reader.result;
                    }
                };
                reader.readAsDataURL(blob);
                if (voiceAudio) voiceAudio.src = URL.createObjectURL(blob);
                if (voicePreviewWrap) {
                    voicePreviewWrap.classList.remove('d-none');
                    voicePreviewWrap.classList.add('d-flex');
                }
                if (voiceLabel) voiceLabel.textContent = 'Re-record Voice';
                if (voiceStatus) voiceStatus.textContent = '15s voice reflection ready!';
            };
            mediaRecorder.start();
        } catch (_) {
            if (voiceStatus) voiceStatus.textContent = 'Microphone permission blocked.';
        }
    });

    ['then', 'now'].forEach((slot) => {
        document.getElementById(`${slot}ImgInput`)?.addEventListener('change', (e) => {
            const file = e.target?.files?.[0] || null;
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                setThenNowSlot(slot, null);
                return;
            }
            thenNowWork[slot] = (async () => {
                // Read the date from the original: compression re-encodes and drops EXIF.
                const year = await guessPhotoYear(file);
                try {
                    const { compressImageFile } = await import('../helper/imageCompressor');
                    setThenNowSlot(slot, await compressImageFile(file));
                } catch (_) {
                    setThenNowSlot(slot, file);
                }
                suggestYear(slot, year);
            })();
        });
    });

    document.getElementById('nowCameraBtn')?.addEventListener('click', async (e) => {
        e.preventDefault();
        await thenNowWork.then;
        if (!thenNowFiles.then) {
            Swal.fire({ icon: 'info', title: 'Pick the Then photo first', text: 'The camera shows it faintly on screen so you can line up the same pose.', confirmButtonColor: '#b8202c' });
            return;
        }
        const shot = await openGhostCamera(thenNowFiles.then);
        if (shot) {
            setThenNowSlot('now', shot);
            suggestYear('now', new Date().getFullYear());
        }
    });
}

window.__getThenNowFiles = () => (
    thenNowFlag?.value === '1' ? { then: thenNowFiles.then, now: thenNowFiles.now } : null
);
window.__resetThenNow = closeThenNow;
// Resolves once any photo still being compressed is ready; Post waits on this.
window.__thenNowReady = () => Promise.all([thenNowWork.then, thenNowWork.now]);
// Used by the "Add your Then & Now" button on a feed card or Flashback Memory prompt.
window.__openThenNow = (replyToPostNo, opts = {}) => {
    const modalEl = document.getElementById('postModal');
    if (modalEl && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    openThenNow(replyToPostNo);

    // Support pre-populating Then slot from an archival memory
    if (opts?.thenImgUrl) {
        thenNowWork.then = (async () => {
            try {
                const imgResp = await fetch(opts.thenImgUrl, { credentials: 'same-origin' });
                if (!imgResp.ok) throw new Error('Could not load memory image');
                const blob = await imgResp.blob();
                const filename = opts.thenImgFilename || `then-archive-${Date.now()}.jpg`;
                const file = new File([blob], filename, { type: blob.type || 'image/jpeg' });
                setThenNowSlot('then', file);
                if (opts.thenYear) {
                    const labelInput = document.querySelector('input[name="then_label"]');
                    if (labelInput) {
                        labelInput.value = String(opts.thenYear);
                        labelInput.dataset.guessed = '1';
                    }
                }
                const nowLabelInput = document.querySelector('input[name="now_label"]');
                if (nowLabelInput && !nowLabelInput.value.trim()) {
                    nowLabelInput.value = 'Now';
                    nowLabelInput.dataset.guessed = '1';
                }
            } catch (err) {
                console.warn('[ThenNow] Failed to load archive photo:', err);
            }
        })();
    }

    // Milestone prompts hand over a ready-made caption; never overwrite what is already typed.
    const caption = typeof opts?.caption === 'string' ? opts.caption : '';
    const box = document.getElementById('postMessage');
    const applyCaption = () => {
        if (caption && box && !box.value.trim()) {
            box.value = caption;
            box.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };
    applyCaption();
    // The modal resets the form as it opens, so apply again once it is fully shown.
    modalEl?.addEventListener('shown.bs.modal', applyCaption, { once: true });
};


// ----------------------------------------------------
// 4. VIDEO EMBED, AUTO-TRIM STUDIO & UPLOAD UI LOGIC
// ----------------------------------------------------
const addVideoBtn = document.getElementById('addVideoBtn');
const videoContainer = document.getElementById('videoEmbedContainer');
const videoInput = document.getElementById('postVideoInput');
const videoDirectFileInput = document.getElementById('videoDirectFileInput');
const removeVideoBtn = document.getElementById('removeVideoBtn');
const videoLivePreview = document.getElementById('videoLivePreview');
const videoUploadProgressWrapper = document.getElementById('videoUploadProgressWrapper');
const videoProgressBar = document.getElementById('videoProgressBar');
const videoUploadStatus = document.getElementById('videoUploadStatus');
const videoUploadPercent = document.getElementById('videoUploadPercent');
const postMessageArea = document.getElementById('postMessage');

// Auto-Trim Studio DOM elements
const videoAutoTrimStudio = document.getElementById('videoAutoTrimStudio');
const videoTrimPreviewPlayer = document.getElementById('videoTrimPreviewPlayer');
const videoTrimStartRange = document.getElementById('videoTrimStartRange');
const videoTrimEndRange = document.getElementById('videoTrimEndRange');
const videoTrimStartLabel = document.getElementById('videoTrimStartLabel');
const videoTrimEndLabel = document.getElementById('videoTrimEndLabel');
const videoTrimDurationBadge = document.getElementById('videoTrimDurationBadge');
const videoTrimMetaBadge = document.getElementById('videoTrimMetaBadge');
const btnConfirmAutoTrim = document.getElementById('btnConfirmAutoTrim');
const btnConfirmAutoTrimLabel = document.getElementById('btnConfirmAutoTrimLabel');
const btnQuickTrimFirst30 = document.getElementById('btnQuickTrimFirst30');

let stagedTrimFile = null;
let stagedTrimDuration = null;
let stagedTrimBlobUrl = null;
let activeVideoUploadPromise = null;
let attachedVideoList = [];
const activeVideoUploadPromises = new Map();

const syncAttachedVideoInputs = () => {
    const urls = attachedVideoList.map(v => v.streamUrl).filter(Boolean);
    const attachedVideoInput = document.getElementById('attachedVideoUrl');
    if (attachedVideoInput) {
        attachedVideoInput.value = urls[0] || '';
    }
    const attachedVideoUrlsInput = document.getElementById('attachedVideoUrls');
    if (attachedVideoUrlsInput) {
        attachedVideoUrlsInput.value = urls.length > 0 ? JSON.stringify(urls) : '';
    }
};

const cleanupTrimStudio = () => {
    stagedTrimFile = null;
    stagedTrimDuration = null;
    if (stagedTrimBlobUrl) {
        URL.revokeObjectURL(stagedTrimBlobUrl);
        stagedTrimBlobUrl = null;
    }
    if (videoTrimPreviewPlayer) {
        videoTrimPreviewPlayer.pause();
        videoTrimPreviewPlayer.removeAttribute('src');
    }
    if (videoAutoTrimStudio) {
        videoAutoTrimStudio.classList.add('d-none');
    }
};

const closeVideoBuilder = () => {
    if (!videoContainer) return;
    cleanupTrimStudio();
    stagedTrimFile = null;
    stagedTrimDuration = null;
    activeVideoUploadPromise = null;
    activeVideoUploadPromises.clear();
    attachedVideoList = [];
    videoContainer.classList.add('d-none');
    if (addVideoBtn) addVideoBtn.classList.remove('video-active');
    if (videoInput) videoInput.value = '';
    if (videoDirectFileInput) videoDirectFileInput.value = '';
    if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
    if (videoLivePreview) {
        videoLivePreview.innerHTML = '';
        videoLivePreview.classList.add('d-none');
    }
    syncAttachedVideoInputs();
    if (postMessageArea) {
        postMessageArea.value = postMessageArea.value.replace(/(?:https?:\/\/[^\s"'<>]+|\/resources\/videos\/posts\/[^\s"'<>]+)\.(?:mp4|webm|ogg|mov)(?:#t=[0-9.,]+)?/gi, '').replace(/\n\s*\n\s*\n/g, '\n\n').trim();
    }
    if (typeof window.__onComposerVideoRemoved === 'function') {
        window.__onComposerVideoRemoved();
    }
};

const removeComposerVideo = (targetId) => {
    if (targetId === undefined || targetId === null) {
        closeVideoBuilder();
        return;
    }

    if (stagedTrimFile && (stagedTrimFile._stagedVidId === targetId || targetId === 'staged')) {
        cleanupTrimStudio();
    }

    if (activeVideoUploadPromises.has(targetId)) {
        activeVideoUploadPromises.delete(targetId);
    }

    const targetIdx = typeof targetId === 'number'
        ? targetId
        : attachedVideoList.findIndex(v => v.id === targetId || v.streamUrl === targetId);

    if (targetIdx >= 0) {
        const removed = attachedVideoList[targetIdx];
        attachedVideoList.splice(targetIdx, 1);
        syncAttachedVideoInputs();

        if (postMessageArea && removed?.streamUrl) {
            postMessageArea.value = postMessageArea.value.replace(removed.streamUrl, '').replace(/\n\s*\n\s*\n/g, '\n\n').trim();
        }
    }

    if (typeof window.__onComposerVideoRemoved === 'function') {
        window.__onComposerVideoRemoved(targetId);
    }

    if (attachedVideoList.length === 0 && !stagedTrimFile && activeVideoUploadPromises.size === 0) {
        closeVideoBuilder();
    } else if (attachedVideoList.length > 0) {
        const nextVid = attachedVideoList[attachedVideoList.length - 1];
        if (nextVid && videoInput) videoInput.value = nextVid.streamUrl;
        renderAllAttachedVideoPreviews();
    }
};
window.__removeComposerVideo = removeComposerVideo;

const syncTrimSlidersUI = (changedSlider = 'start') => {
    if (!videoTrimStartRange || !videoTrimEndRange) return;
    const maxDur = stagedTrimDuration && stagedTrimDuration > 0 ? stagedTrimDuration : 120;
    let start = parseFloat(videoTrimStartRange.value) || 0;
    let end = parseFloat(videoTrimEndRange.value) || Math.min(60, maxDur);

    if (changedSlider === 'start') {
        if (start >= maxDur - 0.5) {
            start = Math.max(0, maxDur - 0.5);
        }
        if (end <= start) {
            end = Math.min(maxDur, start + 60);
        } else if ((end - start) > 60) {
            end = Math.min(maxDur, start + 60);
        }
    } else {
        if (end <= start + 0.5) {
            end = Math.min(maxDur, start + 0.5);
        }
        if ((end - start) > 60) {
            start = Math.max(0, end - 60);
        }
    }

    start = Math.round(start * 10) / 10;
    end = Math.round(end * 10) / 10;
    const clipLen = Math.round(Math.max(0.5, end - start) * 10) / 10;

    videoTrimStartRange.value = String(start);
    videoTrimEndRange.value = String(end);

    if (videoTrimStartLabel) videoTrimStartLabel.textContent = `${start.toFixed(1)}s`;
    if (videoTrimEndLabel) videoTrimEndLabel.textContent = `${end.toFixed(1)}s`;
    if (videoTrimDurationBadge) videoTrimDurationBadge.textContent = `Clip: ${clipLen.toFixed(1)}s / 60s max`;
    if (btnConfirmAutoTrimLabel) {
        btnConfirmAutoTrimLabel.textContent = `Auto-Trim (${start.toFixed(1)}s–${end.toFixed(1)}s) & Attach`;
    }

    if (videoTrimPreviewPlayer && Number.isFinite(start)) {
        try {
            videoTrimPreviewPlayer.currentTime = start;
        } catch (_) { /* ignore seek before metadata */ }
    }
};

if (videoTrimStartRange) {
    videoTrimStartRange.addEventListener('input', () => syncTrimSlidersUI('start'));
}
if (videoTrimEndRange) {
    videoTrimEndRange.addEventListener('input', () => syncTrimSlidersUI('end'));
}
if (videoTrimPreviewPlayer) {
    videoTrimPreviewPlayer.addEventListener('timeupdate', () => {
        const end = parseFloat(videoTrimEndRange?.value || '30');
        const start = parseFloat(videoTrimStartRange?.value || '0');
        if (Number.isFinite(end) && videoTrimPreviewPlayer.currentTime >= end) {
            videoTrimPreviewPlayer.pause();
            videoTrimPreviewPlayer.currentTime = Number.isFinite(start) ? start : 0;
        }
    });
}

const openAutoTrimStudioForFile = (file, duration) => {
    cleanupTrimStudio();
    stagedTrimFile = file;
    stagedTrimDuration = duration;

    if (videoContainer && videoContainer.classList.contains('d-none')) {
        videoContainer.classList.remove('d-none');
        if (addVideoBtn) addVideoBtn.classList.add('video-active');
    }

    if (!videoAutoTrimStudio) return;
    const totalSec = Math.round(duration * 10) / 10;
    const initialEnd = Math.min(60, totalSec);

    if (videoTrimStartRange) {
        videoTrimStartRange.min = '0';
        videoTrimStartRange.max = String(Math.max(1, totalSec - 0.5));
        videoTrimStartRange.value = '0';
    }
    if (videoTrimEndRange) {
        videoTrimEndRange.min = '0.5';
        videoTrimEndRange.max = String(totalSec);
        videoTrimEndRange.value = String(initialEnd);
    }
    if (videoTrimMetaBadge) {
        videoTrimMetaBadge.textContent = `Original: ${Math.round(totalSec)}s → Auto-Trim to 60s`;
    }

    stagedTrimBlobUrl = URL.createObjectURL(file);
    if (videoTrimPreviewPlayer) {
        videoTrimPreviewPlayer.src = stagedTrimBlobUrl;
        videoTrimPreviewPlayer.addEventListener('loadedmetadata', () => {
            if (videoTrimPreviewPlayer.videoWidth && videoTrimPreviewPlayer.videoHeight) {
                const r = videoTrimPreviewPlayer.videoWidth / videoTrimPreviewPlayer.videoHeight;
                const parent = videoTrimPreviewPlayer.parentElement;
                if (parent) {
                    if (r < 0.88) {
                        parent.className = 'ratio ratio-4x3 rounded-3 overflow-hidden mb-2 border border-secondary border-opacity-25';
                        parent.style.maxHeight = '240px';
                        parent.style.maxWidth = r <= 0.65 ? '180px' : '220px';
                        parent.style.margin = '0 auto';
                    } else {
                        parent.className = 'ratio ratio-16x9 rounded-3 overflow-hidden mb-2 border border-secondary border-opacity-25';
                        parent.style.maxHeight = '190px';
                        parent.style.maxWidth = '100%';
                    }
                }
            }
        }, { once: true });
    }

    syncTrimSlidersUI('start');
    videoAutoTrimStudio.classList.remove('d-none');
    scrollDrawerIntoView(videoAutoTrimStudio);
};

const renderAllAttachedVideoPreviews = () => {
    if (!videoLivePreview) return;
    if (attachedVideoList.length === 0) {
        videoLivePreview.innerHTML = '';
        videoLivePreview.classList.add('d-none');
        return;
    }

    videoLivePreview.className = 'mt-2 d-flex gap-2 overflow-x-auto pb-1';
    videoLivePreview.style.scrollSnapType = 'x mandatory';
    videoLivePreview.innerHTML = '';

    attachedVideoList.forEach((entry, idx) => {
        const card = document.createElement('div');
        card.className = 'position-relative rounded-3 overflow-hidden shadow-sm flex-shrink-0';
        card.style.width = attachedVideoList.length === 1 ? '100%' : 'calc(50% - 4px)';
        card.style.maxWidth = attachedVideoList.length === 1 ? '320px' : '260px';
        card.style.minWidth = '180px';
        card.style.background = '#07090e';
        card.style.scrollSnapAlign = 'start';

        const badge = document.createElement('span');
        badge.className = 'badge rounded-pill position-absolute top-0 start-0 m-2 px-2 py-1 shadow-sm';
        badge.style.zIndex = '6';
        badge.style.background = 'rgba(5, 150, 105, 0.88)';
        badge.style.backdropFilter = 'blur(6px)';
        badge.style.color = '#ffffff';
        badge.style.fontSize = '0.72rem';
        badge.style.fontWeight = '600';
        badge.innerHTML = `<i class="bi bi-stars me-1" style="color:#fde047;"></i>4K Ready · Video ${idx + 1}`;
        card.appendChild(badge);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center';
        removeBtn.style.zIndex = '6';
        removeBtn.style.width = '28px';
        removeBtn.style.height = '28px';
        removeBtn.style.padding = '0';
        removeBtn.setAttribute('aria-label', `Remove video ${idx + 1}`);
        removeBtn.innerHTML = '<i class="bi bi-x-lg" style="font-size: 0.72rem;"></i>';
        removeBtn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            removeComposerVideo(entry.id);
        };
        card.appendChild(removeBtn);

        const parsed = parseVideoUrl(entry.streamUrl);
        if (parsed && (parsed.type === 'youtube' || parsed.type === 'vimeo' || parsed.type === 'cloudflare' || parsed.type === 'tiktok')) {
            const iframeWrap = document.createElement('div');
            iframeWrap.className = 'ratio ratio-16x9 w-100';
            iframeWrap.style.maxHeight = '200px';
            iframeWrap.innerHTML = `<iframe src="${parsed.embedUrl}" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>`;
            card.style.width = attachedVideoList.length === 1 ? '100%' : 'calc(75% - 4px)';
            card.style.maxWidth = '360px';
            card.appendChild(iframeWrap);
        } else {
            const vidWrap = document.createElement('div');
            vidWrap.className = 'position-relative w-100';
            vidWrap.style.aspectRatio = '4 / 5';
            vidWrap.style.maxHeight = '220px';
            vidWrap.innerHTML = `<video class="atg-4k-video-render" src="${entry.streamUrl}" controls playsinline preload="metadata" style="width:100%; height:100%; object-fit:contain; display:block;"></video>`;
            const vidEl = vidWrap.querySelector('video');
            if (vidEl) {
                vidEl.addEventListener('loadedmetadata', () => {
                    if (vidEl.videoWidth && vidEl.videoHeight) {
                        const r = vidEl.videoWidth / vidEl.videoHeight;
                        if (r > 1.15) {
                            card.style.width = attachedVideoList.length === 1 ? '100%' : 'calc(75% - 4px)';
                            card.style.maxWidth = '360px';
                            vidWrap.style.aspectRatio = '16 / 9';
                            vidWrap.style.maxHeight = '210px';
                        } else if (r < 0.88) {
                            vidWrap.style.aspectRatio = (r <= 0.65) ? '9 / 16' : '4 / 5';
                            vidWrap.style.maxHeight = '240px';
                        } else {
                            vidWrap.style.aspectRatio = '1 / 1';
                            vidWrap.style.maxHeight = '220px';
                        }
                    }
                }, { once: true });
            }
            card.appendChild(vidWrap);
        }

        videoLivePreview.appendChild(card);
    });

    videoLivePreview.classList.remove('d-none');
};

const renderAttachedVideoPreview = (streamUrl, trimStart = null, trimEnd = null, videoId = null, sourceFile = null, savedBytes = 0) => {
    if (videoInput) videoInput.value = streamUrl;

    const vidId = videoId || `vid_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    const existingIdx = videoId ? attachedVideoList.findIndex(v => v.id === videoId) : -1;
    const entry = {
        id: vidId,
        streamUrl,
        trimStart,
        trimEnd,
        savedBytes
    };

    if (existingIdx >= 0) {
        attachedVideoList[existingIdx] = entry;
    } else {
        const urlIdx = attachedVideoList.findIndex(v => v.streamUrl === streamUrl);
        if (urlIdx >= 0) {
            attachedVideoList[urlIdx] = entry;
        } else if (attachedVideoList.length < 3) {
            attachedVideoList.push(entry);
        }
    }

    syncAttachedVideoInputs();

    if (typeof window.__onComposerVideoUpdated === 'function') {
        window.__onComposerVideoUpdated({
            id: vidId,
            streamUrl,
            file: sourceFile || stagedTrimFile,
            trimStart,
            trimEnd,
            savedBytes,
            uploading: false
        });
    }

    renderAllAttachedVideoPreviews();

    if (postMessageArea) {
        // Keep user caption clean — never inject raw /resources/videos/posts/ paths into visible textarea
        // (#attachedVideoUrl and #attachedVideoUrls hidden inputs carry the video URLs to the backend)
        const cleanedCurrent = postMessageArea.value.replace(/\/resources\/(?:videos|images)\/[^\s"'<>]+/gi, '').trim();
        postMessageArea.value = cleanedCurrent;
    }
};

const uploadVideoWithAutoTrim = (file, trimStart = 0, trimEnd = 60, originalDuration = null, videoId = null) => {
    const vidId = videoId || file?._stagedVidId || `vid_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    const runUpload = async () => {
        if (!file) return null;

        const safeStart = Math.max(0, parseFloat(trimStart) || 0);
        const rawEnd = parseFloat(trimEnd) || (safeStart + 60);
        const safeEnd = Math.min(safeStart + 60, rawEnd > safeStart ? rawEnd : safeStart + 60);
        const clipLen = Math.round((safeEnd - safeStart) * 10) / 10;
        const isLargeFile = file.size > 50 * 1024 * 1024;
        const needsTrim = safeStart > 0 || (originalDuration && originalDuration > 60.5) || (originalDuration && safeEnd < originalDuration - 0.2) || isLargeFile;

        if (videoUploadProgressWrapper) {
            videoUploadProgressWrapper.classList.remove('d-none');
            if (videoProgressBar) {
                videoProgressBar.style.width = '12%';
                videoProgressBar.classList.add('progress-bar-animated');
            }
            if (videoUploadStatus) {
                videoUploadStatus.textContent = needsTrim
                    ? `✂️ Auto-trimming video to ${clipLen}s (${safeStart.toFixed(1)}s–${safeEnd.toFixed(1)}s) & uploading...`
                    : `Optimising & uploading ${clipLen}s family video...`;
            }
            if (videoUploadPercent) videoUploadPercent.textContent = '12%';
        }

        if (btnConfirmAutoTrim) {
            btnConfirmAutoTrim.disabled = true;
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btnConfirmAutoTrim);
        }

        const expirySelect = document.getElementById('videoExpirySelect');
        const expirySeconds = expirySelect ? parseInt(expirySelect.value, 10) : 2592000;
        const csrfToken = document.querySelector('input[name="token"]')?.value
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';

        try {
            let uploadFile = file;
            let effectiveStart = safeStart;
            let effectiveEnd = safeEnd;
            let effectiveOriginalDuration = originalDuration;
            let savedBytes = 0;

            const updateTileBadgeProgress = (htmlText) => {
                const tileBadge = document.getElementById(`composerVidBadge_${vidId}`);
                if (tileBadge) {
                    tileBadge.innerHTML = htmlText;
                }
            };

            if (shouldOptimiseVideoOnDevice(file, { trimStart: safeStart, trimEnd: safeEnd, originalDuration })) {
                if (videoUploadStatus) {
                    videoUploadStatus.textContent = needsTrim
                        ? `✂️ Client Slicing video to ${clipLen}s highlight (${safeStart.toFixed(1)}s–${safeEnd.toFixed(1)}s)...`
                        : `✨ Optimising video for 4K clarity & fast upload (${clipLen}s)...`;
                }
                updateTileBadgeProgress('<span class="spinner-border spinner-border-sm me-0.5" style="width: 8px; height: 8px;"></span> Slicing to 30s...');
                const optResult = await compressVideoFile(file, {
                    trimStart: safeStart,
                    trimEnd: safeEnd,
                    originalDuration,
                    onProgress: (pct) => {
                        const mappedPct = Math.min(45, Math.max(8, Math.round(pct * 0.42)));
                        if (videoProgressBar && videoUploadPercent) {
                            videoProgressBar.style.width = `${mappedPct}%`;
                            videoUploadPercent.textContent = `${mappedPct}%`;
                        }
                        updateTileBadgeProgress(`<span class="spinner-border spinner-border-sm me-0.5" style="width: 8px; height: 8px;"></span> Slicing ${Math.round(pct)}%`);
                    }
                });
                if (optResult && optResult.compressed && optResult.file) {
                    savedBytes = Math.max(0, file.size - optResult.file.size);
                    uploadFile = optResult.file;
                    if (optResult.physicallyTrimmed) {
                        effectiveStart = 0;
                        effectiveEnd = optResult.duration || clipLen;
                        effectiveOriginalDuration = optResult.duration || clipLen;
                    }
                }
            }

            let targetUploadUrl = '/api/video/upload-file';
            let useSovereignLocal = true;
            let cfTokenData = null;

            // Only attempt Cloudflare direct upload if no custom trim window is needed and CF Stream is not known to be in local fallback mode
            if (!needsTrim && !window.__cfStreamFallbackLocal) {
                try {
                    const abortCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                    const abortTimer = abortCtrl ? setTimeout(() => abortCtrl.abort(), 1200) : null;
                    const tokenRes = await fetch('/api/video/direct-upload', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        signal: abortCtrl ? abortCtrl.signal : undefined,
                        body: JSON.stringify({
                            maxDuration: 60,
                            expirySeconds: expirySeconds
                        })
                    });
                    if (abortTimer) clearTimeout(abortTimer);
                    cfTokenData = await tokenRes.json().catch(() => ({}));
                    if (tokenRes.status === 403) {
                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'warning',
                                title: '👑 Premium Retention Required',
                                text: cfTokenData.error || '1 Year and Permanent video retention are available exclusively for Premium members.',
                                confirmButtonText: 'Select 30 Days',
                                confirmButtonColor: '#1e6040'
                            });
                        }
                        if (expirySelect) expirySelect.value = '2592000';
                        throw new Error(cfTokenData.error || 'Premium retention required.');
                    }
                    if (cfTokenData && cfTokenData.fallbackLocal) {
                        window.__cfStreamFallbackLocal = true;
                    }
                    if (tokenRes.ok && cfTokenData.success && cfTokenData.uploadUrl && !cfTokenData.fallbackLocal) {
                        targetUploadUrl = cfTokenData.uploadUrl;
                        useSovereignLocal = false;
                    }
                } catch (preErr) {
                    if (String(preErr.message || '').includes('Premium')) {
                        throw preErr;
                    }
                    useSovereignLocal = true;
                    targetUploadUrl = '/api/video/upload-file';
                }
            }

            const CHUNK_SIZE = 15 * 1024 * 1024; // 15MB chunks bypass proxy/Cloudflare 100MB single-request limits
            const sendSingleOrChunkRequest = (formData, onChunkProgress) => new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', targetUploadUrl, true);
                if (useSovereignLocal && csrfToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                }

                xhr.upload.onprogress = (evt) => {
                    if (evt.lengthComputable && typeof onChunkProgress === 'function') {
                        onChunkProgress(evt.loaded, evt.total);
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let parsedRes = {};
                        try {
                            parsedRes = JSON.parse(xhr.responseText || '{}');
                        } catch (_) { /* ignore non-JSON from CF */ }
                        resolve({ useSovereignLocal, parsedRes, cfTokenData });
                    } else {
                        let errMsg = 'Video upload failed. Please try again.';
                        try {
                            const errJson = JSON.parse(xhr.responseText || '{}');
                            if (errJson.error) errMsg = errJson.error;
                        } catch (_) { /* ignore */ }
                        reject(new Error(errMsg));
                    }
                };

                xhr.onerror = () => reject(new Error('Network error during video upload.'));
                xhr.ontimeout = () => reject(new Error('Video upload timed out. Please try again.'));
                xhr.timeout = useSovereignLocal ? 60000 : 15000;
                xhr.send(formData);
            });

            let uploadResult;
            if (!useSovereignLocal) {
                try {
                    const cfFormData = new FormData();
                    cfFormData.append('file', uploadFile);
                    uploadResult = await sendSingleOrChunkRequest(cfFormData, (loaded, total) => {
                        if (videoProgressBar && videoUploadPercent) {
                            const percent = Math.max(12, Math.round((loaded / total) * 100));
                            videoProgressBar.style.width = `${percent}%`;
                            videoUploadPercent.textContent = `${percent}%`;
                        }
                    });
                } catch (cfUploadErr) {
                    console.warn('[VideoUpload] Direct cloud upload failed/blocked, falling back to Sovereign Local upload:', cfUploadErr);
                    window.__cfStreamFallbackLocal = true;
                    useSovereignLocal = true;
                    targetUploadUrl = '/api/video/upload-file';
                }
            }

            if (useSovereignLocal && !uploadResult) {
                if (uploadFile.size > 20 * 1024 * 1024) {
                    const totalChunks = Math.ceil(uploadFile.size / CHUNK_SIZE);
                    const uploadId = `vidchunk_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`;

                    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                        const startByte = chunkIndex * CHUNK_SIZE;
                        const endByte = Math.min(uploadFile.size, startByte + CHUNK_SIZE);
                        const chunkBlob = uploadFile.slice(startByte, endByte, uploadFile.type || 'video/mp4');

                        const formData = new FormData();
                        formData.append('video_file', chunkBlob, uploadFile.name || 'video.mp4');
                        formData.append('upload_id', uploadId);
                        formData.append('chunk_index', String(chunkIndex));
                        formData.append('total_chunks', String(totalChunks));
                        formData.append('trim_start', String(effectiveStart));
                        formData.append('trim_end', String(effectiveEnd));
                        if (effectiveOriginalDuration) {
                            formData.append('client_duration', String(effectiveOriginalDuration));
                        }
                        formData.append('expiry_seconds', String(expirySeconds));
                        if (csrfToken) {
                            formData.append('token', csrfToken);
                        }

                        uploadResult = await sendSingleOrChunkRequest(formData, (loadedInChunk) => {
                            const overallLoaded = startByte + loadedInChunk;
                            const percent = Math.min(99, Math.max(12, Math.round((overallLoaded / uploadFile.size) * 100)));
                            if (videoProgressBar && videoUploadPercent) {
                                videoProgressBar.style.width = `${percent}%`;
                                videoUploadPercent.textContent = `${percent}%`;
                            }
                            updateTileBadgeProgress(`<span class="spinner-border spinner-border-sm me-0.5" style="width: 8px; height: 8px;"></span> Uploading ${percent}%`);
                        });
                    }
                } else {
                    const formData = new FormData();
                    formData.append('video_file', uploadFile);
                    formData.append('trim_start', String(effectiveStart));
                    formData.append('trim_end', String(effectiveEnd));
                    if (effectiveOriginalDuration) {
                        formData.append('client_duration', String(effectiveOriginalDuration));
                    }
                    formData.append('expiry_seconds', String(expirySeconds));
                    if (csrfToken) {
                        formData.append('token', csrfToken);
                    }

                    uploadResult = await sendSingleOrChunkRequest(formData, (loaded, total) => {
                        const percent = Math.max(12, Math.round((loaded / total) * 100));
                        if (videoProgressBar && videoUploadPercent) {
                            videoProgressBar.style.width = `${percent}%`;
                            videoUploadPercent.textContent = `${percent}%`;
                        }
                        updateTileBadgeProgress(`<span class="spinner-border spinner-border-sm me-0.5" style="width: 8px; height: 8px;"></span> Uploading ${percent}%`);
                    });
                }
            }

            const finalStreamUrl = uploadResult.useSovereignLocal
                ? (uploadResult.parsedRes?.streamUrl || uploadResult.parsedRes?.rawUrl)
                : (uploadResult.cfTokenData?.streamUrl || `https://iframe.videodelivery.net/${uploadResult.cfTokenData?.videoId}`);

            if (!finalStreamUrl) {
                throw new Error('Server did not return a playable video stream URL.');
            }

            if (videoUploadStatus) {
                videoUploadStatus.textContent = needsTrim
                    ? `✅ ATG 4K FastStart: Auto-trimmed to ${clipLen}s & ready to stream!`
                    : '✅ ATG 4K FastStart: Upload complete! Video ready to stream.';
            }
            if (videoProgressBar) {
                videoProgressBar.style.width = '100%';
                videoProgressBar.classList.remove('progress-bar-animated');
            }

            cleanupTrimStudio();
            renderAttachedVideoPreview(finalStreamUrl, safeStart, safeEnd, vidId, uploadFile, savedBytes);
            return finalStreamUrl;
        } catch (err) {
            console.warn('[VideoUpload] Upload/Auto-Trim error:', err);
            cleanupTrimStudio();
            if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
            if (typeof window.__onComposerVideoRemoved === 'function') {
                window.__onComposerVideoRemoved(vidId);
            }
            (window.swalError || alert)(err?.message || 'Unable to upload video clip. Please try again.');
            return null;
        } finally {
            if (btnConfirmAutoTrim) {
                btnConfirmAutoTrim.disabled = false;
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btnConfirmAutoTrim);
            }
            activeVideoUploadPromises.delete(vidId);
            if (activeVideoUploadPromise === uploadPromise) {
                activeVideoUploadPromise = null;
            }
        }
    };

    const uploadPromise = runUpload();
    activeVideoUploadPromises.set(vidId, uploadPromise);
    activeVideoUploadPromise = uploadPromise;
    return uploadPromise;
};

if (btnConfirmAutoTrim) {
    btnConfirmAutoTrim.addEventListener('click', (e) => {
        e.preventDefault();
        if (!stagedTrimFile) return;
        const start = parseFloat(videoTrimStartRange?.value || '0') || 0;
        const end = parseFloat(videoTrimEndRange?.value || '30') || 30;
        const targetId = stagedTrimFile._stagedVidId;
        if (targetId && typeof window.__onComposerVideoStaged === 'function') {
            window.__onComposerVideoStaged({
                id: targetId,
                file: stagedTrimFile,
                uploading: true,
                customising: false
            });
        }
        uploadVideoWithAutoTrim(stagedTrimFile, start, end, stagedTrimDuration, targetId);
    });
}

if (btnQuickTrimFirst30) {
    btnQuickTrimFirst30.addEventListener('click', (e) => {
        e.preventDefault();
        if (!stagedTrimFile) return;
        if (videoTrimStartRange) videoTrimStartRange.value = '0';
        if (videoTrimEndRange) videoTrimEndRange.value = String(Math.min(30, stagedTrimDuration || 30));
        syncTrimSlidersUI('start');
        const targetId = stagedTrimFile._stagedVidId;
        if (targetId && typeof window.__onComposerVideoStaged === 'function') {
            window.__onComposerVideoStaged({
                id: targetId,
                file: stagedTrimFile,
                uploading: true,
                customising: false
            });
        }
        uploadVideoWithAutoTrim(stagedTrimFile, 0, Math.min(30, stagedTrimDuration || 30), stagedTrimDuration, targetId);
    });
}

const handleSelectedVideoFile = async (file) => {
    if (!file) return;

    if (attachedVideoList.length >= 3) {
        (window.swalError || alert)('Maximum of 3 videos reached. Remove a video to upload another.');
        return;
    }

    const vidId = `vid_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    file._stagedVidId = vidId;

    if (videoContainer && videoContainer.classList.contains('d-none')) {
        videoContainer.classList.remove('d-none');
        if (addVideoBtn) addVideoBtn.classList.add('video-active');
        scrollDrawerIntoView(videoContainer);
    }

    // Allow large source videos up to 1GB (1024MB) since Smart Auto-Trim slices them to a 30-second highlight clip
    if (file.size > 1024 * 1024 * 1024) {
        (window.swalError || alert)('Video size exceeds 1GB. Please select a video under 1GB.');
        if (videoDirectFileInput) videoDirectFileInput.value = '';
        return;
    }

    stagedTrimFile = file;
    stagedTrimDuration = null;

    if (typeof window.__onComposerVideoStaged === 'function') {
        window.__onComposerVideoStaged({
            id: vidId,
            file,
            blobUrl: URL.createObjectURL(file),
            uploading: true
        });
    }

    // Inspect video duration via browser metadata with guaranteed timeout race
    let clipDuration = null;
    try {
        clipDuration = await new Promise((resolve) => {
            const tempVideo = document.createElement('video');
            tempVideo.preload = 'metadata';
            const blobUrl = URL.createObjectURL(file);
            tempVideo.src = blobUrl;

            const timer = setTimeout(() => {
                URL.revokeObjectURL(blobUrl);
                resolve(null);
            }, 1200);

            tempVideo.onloadedmetadata = () => {
                clearTimeout(timer);
                URL.revokeObjectURL(blobUrl);
                resolve(Number.isFinite(tempVideo.duration) ? tempVideo.duration : null);
            };

            tempVideo.onerror = () => {
                clearTimeout(timer);
                URL.revokeObjectURL(blobUrl);
                resolve(null);
            };

            try {
                tempVideo.load();
            } catch (_) {}
        });
    } catch (err) {
        console.warn('Video duration check skipped:', err);
    }

    stagedTrimDuration = clipDuration;

    // If video exceeds 60 seconds (or is a large >80MB file whose duration metadata deferred), open the Auto-Trim Studio
    if ((clipDuration && clipDuration > 60.5) || (!clipDuration && file.size > 80 * 1024 * 1024)) {
        const effectiveDuration = clipDuration && clipDuration > 60.5 ? clipDuration : 120;
        openAutoTrimStudioForFile(file, effectiveDuration);

        if (window.Swal) {
            const durationSummary = clipDuration
                ? `Your selected video is <strong>${Math.round(clipDuration)} seconds</strong> long (${(file.size / (1024 * 1024)).toFixed(1)} MB).`
                : `Your selected video is <strong>${(file.size / (1024 * 1024)).toFixed(1)} MB</strong>.`;
            const modalResult = await window.Swal.fire({
                icon: 'info',
                title: '✂️ Auto-Trim Video to 60 Seconds?',
                html: `
                    <div style="text-align: left; font-size: 0.94rem;">
                        <p class="mb-2">${durationSummary} Maximum post clip length is <strong>60 seconds (1 minute)</strong>.</p>
                        <div style="background: #f0fdf4; border-left: 4px solid #16a34a; padding: 10px 14px; border-radius: 8px; margin: 12px 0;">
                            <span style="color: #166534; font-weight: 700;">✨ Smart Auto-Trim Ready:</span>
                            <span style="color: #15803d;">We can automatically trim this video to 60 seconds for you right now, or let you slide to pick your favourite 60-second moment.</span>
                        </div>
                    </div>
                `,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '✂️ Auto-Trim First 60s Now',
                denyButtonText: '🎚️ Customise 60s Window',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#a31621',
                denyButtonColor: '#0f172a'
            });

            if (modalResult.isConfirmed) {
                await uploadVideoWithAutoTrim(file, 0, 60, effectiveDuration, vidId);
            } else if (modalResult.isDenied) {
                file._needsManualTrim = true;
                if (typeof window.__onComposerVideoUpdated === 'function') {
                    window.__onComposerVideoUpdated({
                        id: vidId,
                        file,
                        uploading: false,
                        customising: true
                    });
                }
            } else if (modalResult.isDismissed) {
                removeComposerVideo(vidId);
                cleanupTrimStudio();
                if (videoDirectFileInput) videoDirectFileInput.value = '';
            }
            // If isDenied ("Customise 60s Window"), the #videoAutoTrimStudio remains open for the user to adjust sliders!
        } else {
            await uploadVideoWithAutoTrim(file, 0, 60, effectiveDuration, vidId);
        }
        return;
    }

    // Video is <= 60s: upload immediately
    await uploadVideoWithAutoTrim(file, 0, clipDuration || 60, clipDuration, vidId);
};

window.__handleComposerVideoFile = handleSelectedVideoFile;

// Ensure that if the user clicks "Post" while a video is still in the Auto-Trim Studio or uploading,
// it finishes auto-trimming and attaching before the post form submits!
window.__ensurePendingVideoUploaded = async () => {
    try {
        if (activeVideoUploadPromises.size > 0) {
            await Promise.race([
                Promise.all(Array.from(activeVideoUploadPromises.values())),
                new Promise((_, reject) => setTimeout(() => reject(new Error('Video upload timed out. Please check your connection and try again.')), 120000))
            ]);
        } else if (activeVideoUploadPromise) {
            await Promise.race([
                activeVideoUploadPromise,
                new Promise((_, reject) => setTimeout(() => reject(new Error('Video upload timed out. Please check your connection and try again.')), 120000))
            ]);
        } else if (stagedTrimFile && stagedTrimFile._needsManualTrim) {
            const start = parseFloat(videoTrimStartRange?.value || '0') || 0;
            const end = parseFloat(videoTrimEndRange?.value || '30') || 30;
            const res = await uploadVideoWithAutoTrim(stagedTrimFile, start, end, stagedTrimDuration, stagedTrimFile._stagedVidId);
            if (!res) {
                throw new Error('Video auto-trim upload could not be completed.');
            }
        }
    } catch (e) {
        cleanupTrimStudio();
        throw e;
    }
};

if (addVideoBtn && videoContainer) {
    addVideoBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = videoContainer.classList.contains('d-none');
        if (isHidden) {
            if (typeof window.closeAllComposerDrawers === 'function') {
                window.closeAllComposerDrawers('videoEmbedContainer');
            }
            videoContainer.classList.remove('d-none');
            addVideoBtn.classList.add('video-active');
            scrollDrawerIntoView(videoContainer);
            if (videoInput) setTimeout(() => videoInput.focus(), 60);
        } else {
            if (attachedVideoList.length < 3 && videoDirectFileInput) {
                videoDirectFileInput.click();
            } else if (attachedVideoList.length >= 3) {
                (window.swalError || alert)('Maximum of 3 videos reached.');
            } else {
                closeVideoBuilder();
            }
        }
    });

    if (removeVideoBtn) {
        removeVideoBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeVideoBuilder();
        });
    }

    // "Have a YouTube link?" — hand off to the Link Photos & Videos drawer
    const videoUseLinkBtn = document.getElementById('videoUseLinkBtn');
    if (videoUseLinkBtn) {
        videoUseLinkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Only collapse the drawer if nothing is attached — closeVideoBuilder() discards uploads
            if (attachedVideoList.length === 0 && !activeVideoUploadPromises.size) closeVideoBuilder();
            document.getElementById('addGooglePhotoBtn')?.click();
        });
    }

    if (videoDirectFileInput) {
        videoDirectFileInput.addEventListener('change', async (e) => {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;
            const remaining = Math.max(0, 3 - attachedVideoList.length);
            if (files.length > remaining) {
                (window.swalError || alert)(`You can attach up to 3 videos per post. ${remaining === 0 ? 'Video limit reached.' : `Only ${remaining} video(s) will be added.`}`);
            }
            const toProcess = files.slice(0, remaining);
            await Promise.all(toProcess.map(f => handleSelectedVideoFile(f)));
            videoDirectFileInput.value = '';
        });
    }

    const videoExpirySelectEl = document.getElementById('videoExpirySelect');
    if (videoExpirySelectEl) {
        videoExpirySelectEl.addEventListener('change', (e) => {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            if (selectedOpt && selectedOpt.getAttribute('data-premium') === 'true') {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'info',
                        title: '👑 Premium Video Retention',
                        html: '1 Year and Permanent video memories are available for <strong>Premium Members</strong>.<br><small class="text-muted">Free members enjoy up to 30 days retention.</small>',
                        confirmButtonText: 'Got It',
                        confirmButtonColor: '#1e6040'
                    });
                }
            }
        });
    }

    if (videoInput && videoLivePreview) {
        videoInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const url = videoInput.value.trim();
                const parsed = parseVideoUrl(url);
                if (parsed) {
                    if (attachedVideoList.length >= 3) {
                        (window.swalError || alert)('Maximum of 3 videos reached.');
                        return;
                    }
                    renderAttachedVideoPreview(url, parsed.trimStart ?? null, parsed.trimEnd ?? null);
                    videoInput.value = '';
                }
            }
        });

        videoInput.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            const parsed = parseVideoUrl(url);

            if (parsed) {
                if (attachedVideoList.length >= 3) {
                    (window.swalError || alert)('Maximum of 3 videos reached.');
                    return;
                }
                renderAttachedVideoPreview(url, parsed.trimStart ?? null, parsed.trimEnd ?? null);
                setTimeout(() => {
                    if (videoInput && parseVideoUrl(videoInput.value.trim())) {
                        videoInput.value = '';
                    }
                }, 800);
            }
        });
    }
}

window.__resetVideoBuilder = closeVideoBuilder;

// ----------------------------------------------------
// 5. FAMILY GIF PICKER (Curated, Zero-Friction)
// ----------------------------------------------------
const addGifBtn = document.getElementById('addGifBtn') || document.querySelector('.action-gif');
const gifDrawer = document.getElementById('gifPickerDrawer');
const closeGifDrawerBtn = document.getElementById('closeGifDrawerBtn');
const gifCategoryPills = document.getElementById('gifCategoryPills');
const gifSearchInput = document.getElementById('gifSearchInput');
const gifResultsGrid = document.getElementById('gifResultsGrid');
const selectedGifPreview = document.getElementById('selectedGifPreview');
const selectedGifImg = document.getElementById('selectedGifImg');
const removeSelectedGifBtn = document.getElementById('removeSelectedGifBtn');
const postFormEl = document.getElementById('formPostMessageModal');

let currentGifCategory = 'all';
let currentGifSearch = '';
let currentAttachedGifUrl = '';

const closeGifDrawer = () => {
    if (!gifDrawer) return;
    gifDrawer.classList.add('d-none');
    if (addGifBtn) addGifBtn.classList.remove('gif-active');
};

const renderGifGrid = (cat = 'all', query = '') => {
    if (!gifResultsGrid) return;
    gifResultsGrid.innerHTML = '';

    const cleanQuery = query.toLowerCase().trim();
    const filtered = FAMILY_GIFS.filter(item => {
        const matchesCategory = (cat === 'all' || item.cat === cat);
        const matchesQuery = !cleanQuery || item.title.toLowerCase().includes(cleanQuery) || item.cat.includes(cleanQuery);
        return matchesCategory && matchesQuery;
    });

    if (filtered.length === 0) {
        gifResultsGrid.innerHTML = `
            <div class="text-center w-100 py-3 text-muted small">
                <i class="bi bi-emoji-neutral fs-4 d-block mb-1 opacity-50"></i>
                No matching GIFs found. Try searching another emotion!
            </div>
        `;
        return;
    }

    filtered.forEach(gif => {
        const card = document.createElement('div');
        card.className = 'gif-item-card shadow-xs';
        card.title = gif.title;
        card.innerHTML = `<img src="${gif.url}" alt="${gif.title}" loading="lazy">`;

        card.addEventListener('click', () => {
            attachGif(gif.url);
        });

        gifResultsGrid.appendChild(card);
    });
};

const attachGif = (gifUrl) => {
    currentAttachedGifUrl = gifUrl;

    // Show preview
    if (selectedGifPreview && selectedGifImg) {
        selectedGifImg.src = gifUrl;
        selectedGifPreview.classList.remove('d-none');
        scrollDrawerIntoView(selectedGifPreview);
    }

    // Hidden input for FormData submission
    let hiddenInput = document.getElementById('attachedGifUrlInput');
    if (!hiddenInput && postFormEl) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'attached_gif_url';
        hiddenInput.id = 'attachedGifUrlInput';
        postFormEl.appendChild(hiddenInput);
    }
    if (hiddenInput) {
        hiddenInput.value = gifUrl;
    }

    closeGifDrawer();
};

const detachGif = () => {
    if (selectedGifPreview) selectedGifPreview.classList.add('d-none');
    if (selectedGifImg) selectedGifImg.src = '';
    const hiddenInput = document.getElementById('attachedGifUrlInput');
    if (hiddenInput) hiddenInput.value = '';
    currentAttachedGifUrl = '';
};

// Intercept clipboard paste for mobile keyboard rich content (Memojis, Stickers, GIFs & photos)
const composerTextarea = document.getElementById('postMessageEditor') || document.querySelector('.composer-text') || document.getElementById('postMessage');
const postImageUploadInput = document.getElementById('imageUpload');

if (composerTextarea) {
    composerTextarea.addEventListener('paste', (e) => {
        if (e.__fpImagePasteHandled || e.defaultPrevented) return;
        const clipboard = e.clipboardData || window.clipboardData;
        if (!clipboard) return;

        // 1. Check for binary image files (pasted stickers, Memojis, mobile keyboard GIFs, screenshots, files)
        const pastedImages = [];
        if (clipboard.files && clipboard.files.length) {
            for (let i = 0; i < clipboard.files.length; i++) {
                const f = clipboard.files[i];
                if (f && f.type && f.type.startsWith('image/')) {
                    pastedImages.push(f);
                }
            }
        }
        if (!pastedImages.length && clipboard.items && clipboard.items.length) {
            for (let i = 0; i < clipboard.items.length; i++) {
                const item = clipboard.items[i];
                if (item && item.kind === 'file' && item.type && item.type.startsWith('image/')) {
                    const blob = item.getAsFile();
                    if (blob) {
                        const ext = item.type.split('/')[1] ? item.type.split('/')[1].replace('+xml', '') : 'png';
                        const file = new File([blob], `pasted_image_${Date.now()}_${i}.${ext}`, { type: item.type });
                        pastedImages.push(file);
                    }
                }
            }
        }

        if (pastedImages.length > 0 && postImageUploadInput) {
            e.preventDefault();
            e.__fpImagePasteHandled = true;
            const dt = new DataTransfer();
            pastedImages.forEach(f => dt.items.add(f));
            postImageUploadInput.files = dt.files;
            postImageUploadInput.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        // 2. Check for pasted GIF / image URLs as plain text
        const pastedText = clipboard.getData('text/plain');
        if (pastedText && isAllowedGifUrl(pastedText.trim())) {
            const url = pastedText.trim();
            if (/\.(gif|webp)(\?|$)/i.test(url) || /giphy\.com|tenor\.com/.test(url)) {
                attachGif(url);
            }
        }
    });
}

if (removeSelectedGifBtn) {
    removeSelectedGifBtn.addEventListener('click', (e) => {
        e.preventDefault();
        detachGif();
    });
}

if (addGifBtn && gifDrawer) {
    addGifBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = gifDrawer.classList.contains('d-none');
        if (isHidden) {
            if (typeof window.closeAllComposerDrawers === 'function') {
                window.closeAllComposerDrawers('gifPickerDrawer');
            }
            closeEmojiDrawer();
            gifDrawer.classList.remove('d-none');
            addGifBtn.classList.add('gif-active');
            scrollDrawerIntoView(gifDrawer);
            renderGifGrid(currentGifCategory, currentGifSearch);
            if (gifSearchInput) setTimeout(() => gifSearchInput.focus(), 60);
        } else {
            closeGifDrawer();
        }
    });
}

if (closeGifDrawerBtn) {
    closeGifDrawerBtn.addEventListener('click', (e) => {
        e.preventDefault();
        closeGifDrawer();
    });
}

// Category filter pills
if (gifCategoryPills) {
    gifCategoryPills.addEventListener('click', (e) => {
        const pill = e.target.closest('.gif-cat-pill');
        if (!pill) return;
        gifCategoryPills.querySelectorAll('.gif-cat-pill').forEach(btn => btn.classList.remove('active'));
        pill.classList.add('active');
        currentGifCategory = pill.getAttribute('data-cat') || 'all';
        renderGifGrid(currentGifCategory, currentGifSearch);
    });
}

// Search input with debounce
let gifSearchTimer = null;
if (gifSearchInput) {
    gifSearchInput.addEventListener('input', (e) => {
        const val = e.target.value;
        if (gifSearchTimer) clearTimeout(gifSearchTimer);
        gifSearchTimer = setTimeout(() => {
            currentGifSearch = val;
            renderGifGrid(currentGifCategory, currentGifSearch);
        }, 120);
    });
}

window.__resetGifPicker = () => {
    closeGifDrawer();
    detachGif();
};

// ----------------------------------------------------
// 6. TAG PEOPLE UI LOGIC
// ----------------------------------------------------
const addTagBtn = document.getElementById('addTagBtn');
const tagContainer = document.getElementById('tagPeopleContainer');
const removeTagContainerBtn = document.getElementById('removeTagContainerBtn');
const tagSearchInput = document.getElementById('tagSearchInput');
const tagSuggestionsList = document.getElementById('tagSuggestionsList');
const selectedTagsTray = document.getElementById('selectedTagsTray');
const taggedUserInputs = document.getElementById('taggedUserInputs');
const tagCountBadge = document.getElementById('tagCountBadge');

const MAX_TAGGED_PEOPLE = 20;
const selectedTags = new Map();
let searchDebounceTimer = null;

const renderSelectedTags = () => {
    if (!selectedTagsTray || !taggedUserInputs) return;

    taggedUserInputs.innerHTML = '';
    selectedTagsTray.innerHTML = '';

    const tagHeaderWrapper = document.getElementById('composerTagHeaderWrapper');
    const tagHeaderNames = document.getElementById('composerTagHeaderNames');

    if (selectedTags.size === 0) {
        selectedTagsTray.classList.add('d-none');
        if (tagCountBadge) {
            tagCountBadge.textContent = '0 Tagged';
            tagCountBadge.className = 'badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5';
        }
        if (tagHeaderWrapper) tagHeaderWrapper.classList.add('d-none');
        if (tagHeaderNames) tagHeaderNames.textContent = '';
        return;
    }

    selectedTagsTray.classList.remove('d-none');
    if (tagCountBadge) {
        tagCountBadge.textContent = `${selectedTags.size} Tagged`;
        tagCountBadge.className = 'badge rounded-pill bg-primary text-white px-2 py-0.5 shadow-xs';
    }

    // Facebook-style real-time header display: "[Author] is with [Tagged 1] and [Tagged 2]"
    if (tagHeaderWrapper && tagHeaderNames) {
        tagHeaderWrapper.classList.remove('d-none');
        const tagList = Array.from(selectedTags.values()).map(m => m.fullName);
        if (tagList.length === 1) {
            tagHeaderNames.textContent = tagList[0];
        } else if (tagList.length === 2) {
            tagHeaderNames.textContent = `${tagList[0]} and ${tagList[1]}`;
        } else {
            tagHeaderNames.textContent = `${tagList[0]} and ${tagList.length - 1} others`;
        }
    }

    selectedTags.forEach((member) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tagged_user_ids[]';
        input.value = member.id;
        taggedUserInputs.appendChild(input);

        const chip = document.createElement('span');
        chip.className = 'badge d-inline-flex align-items-center gap-1.5 py-1 px-2.5 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill shadow-xs';
        chip.style.fontSize = '0.8rem';
        chip.style.fontWeight = '500';

        const safeFullName = member.fullName.replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[m]));

        chip.innerHTML = `
            <img src="${member.profileImg}" class="rounded-circle" width="18" height="18" style="object-fit: cover;" alt="">
            <span>${safeFullName}</span>
            <button type="button" class="btn-close ms-1" style="font-size: 0.55rem;" aria-label="Remove tag"></button>
        `;

        chip.querySelector('.btn-close')?.addEventListener('click', (ev) => {
            ev.stopPropagation();
            selectedTags.delete(member.id);
            renderSelectedTags();
            updateSuggestionListSelectionState();
        });

        selectedTagsTray.appendChild(chip);
    });
};

const updateSuggestionListSelectionState = () => {
    if (!tagSuggestionsList) return;
    const items = tagSuggestionsList.querySelectorAll('[data-user-id]');
    items.forEach((item) => {
        const uId = item.getAttribute('data-user-id');
        const isSelected = selectedTags.has(uId);
        const indicator = item.querySelector('.tag-item-indicator');
        if (indicator) {
            indicator.innerHTML = isSelected
                ? '<i class="bi bi-check-circle-fill text-primary fs-5"></i>'
                : '<i class="bi bi-plus-circle text-muted fs-5"></i>';
        }
        item.classList.toggle('border-primary', isSelected);
        item.classList.toggle('bg-primary-subtle', isSelected);
    });
};

const renderSuggestions = (members) => {
    if (!tagSuggestionsList) return;
    tagSuggestionsList.innerHTML = '';

    if (!Array.isArray(members) || members.length === 0) {
        tagSuggestionsList.innerHTML = `
            <div class="text-center py-3 text-muted small">
                <i class="bi bi-person-x fs-4 d-block mb-1 opacity-50"></i>
                No matching family members found
            </div>
        `;
        return;
    }

    members.forEach((m) => {
        const isSelected = selectedTags.has(m.id);
        const el = document.createElement('div');
        el.className = `d-flex align-items-center justify-content-between p-2 rounded-2 transition-all ${isSelected ? 'border-primary bg-primary-subtle' : ''}`;
        el.style.cssText = 'cursor: pointer; border: 1px solid var(--border-color, #e2e8f0); background-color: var(--card-bg, #ffffff);';
        el.setAttribute('data-user-id', m.id);

        const safeFullName = (m.fullName || `${m.firstName} ${m.lastName}`).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c]));
        const subtitle = m.famCode ? `Family Code: #${m.famCode}` : 'Connected Relative';

        el.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <img src="${m.profileImg || '/resources/images/profile/avatarM.png'}" class="rounded-circle" width="34" height="34" style="object-fit: cover;" alt="">
                <div>
                    <div class="fw-semibold small text-dark lh-sm">${safeFullName}</div>
                    <div class="text-muted" style="font-size: 0.72rem;">${subtitle}</div>
                </div>
            </div>
            <div class="tag-item-indicator">
                ${isSelected ? '<i class="bi bi-check-circle-fill text-primary fs-5"></i>' : '<i class="bi bi-plus-circle text-muted fs-5"></i>'}
            </div>
        `;

        el.addEventListener('click', () => {
            if (selectedTags.has(m.id)) {
                selectedTags.delete(m.id);
            } else {
                if (selectedTags.size >= MAX_TAGGED_PEOPLE) {
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Tag Limit Reached',
                            text: `You can tag up to ${MAX_TAGGED_PEOPLE} people per post.`,
                            confirmButtonColor: '#a31621'
                        });
                    }
                    return;
                }
                selectedTags.set(m.id, {
                    id: m.id,
                    fullName: m.fullName || `${m.firstName} ${m.lastName}`.trim(),
                    firstName: m.firstName,
                    lastName: m.lastName,
                    profileImg: m.profileImg || '/resources/images/profile/avatarM.png'
                });
            }
            renderSelectedTags();
            updateSuggestionListSelectionState();
        });

        tagSuggestionsList.appendChild(el);
    });
};

let cachedFamilyMembers = [];

const fetchSuggestions = async (query = '') => {
    try {
        const res = await axios.get('/api/members/tag-suggestions', {
            params: { q: query.trim() }
        });
        const members = res?.data?.message?.members || res?.data?.data?.members || res?.data?.members || [];
        if (Array.isArray(members) && members.length > 0) {
            const memberMap = new Map(cachedFamilyMembers.map(m => [String(m.id), m]));
            members.forEach(m => memberMap.set(String(m.id), m));
            cachedFamilyMembers = Array.from(memberMap.values());
        }
        renderSuggestions(members);
        return members;
    } catch (err) {
        console.error('[TagPeople] Failed to fetch tag suggestions:', err);
        if (tagSuggestionsList) {
            tagSuggestionsList.innerHTML = `
                <div class="text-center py-2 text-danger small">
                    Failed to load family members. Tap to retry.
                </div>
            `;
            tagSuggestionsList.firstElementChild?.addEventListener('click', () => fetchSuggestions(query));
        }
        return [];
    }
};

const closeTagBuilder = () => {
    if (!tagContainer) return;
    tagContainer.classList.add('d-none');
    if (addTagBtn) addTagBtn.classList.remove('tag-active');
};

const resetTagBuilder = () => {
    selectedTags.clear();
    renderSelectedTags();
    closeTagBuilder();
    closeComposerMentionDropdown();
    if (tagSearchInput) tagSearchInput.value = '';
};

// ── Inline @Mention Autocomplete for Composer ─────────────────────────────
const composerEditor = document.getElementById('postMessageEditor');
const composerMentionDropdown = document.getElementById('composerMentionDropdown');
const composerMentionList = document.getElementById('composerMentionList');
let composerMentionMatches = [];
let composerMentionActiveIndex = 0;
let composerMentionDebounceTimer = null;

function getComposerTextBeforeCursor(editorEl) {
    if (!editorEl) return '';
    const sel = window.getSelection();
    if (!sel || !sel.rangeCount || !editorEl.contains(sel.anchorNode)) {
        return editorEl.innerText || editorEl.textContent || '';
    }
    try {
        const range = sel.getRangeAt(0);
        const preCaretRange = range.cloneRange();
        preCaretRange.selectNodeContents(editorEl);
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        return preCaretRange.toString();
    } catch (_) {
        return editorEl.innerText || editorEl.textContent || '';
    }
}

function closeComposerMentionDropdown() {
    if (composerMentionDropdown) {
        composerMentionDropdown.classList.add('d-none');
    }
    composerMentionMatches = [];
    composerMentionActiveIndex = 0;
}

function updateComposerMentionActiveItem() {
    if (!composerMentionList) return;
    const items = composerMentionList.querySelectorAll('.mention-autocomplete__item');
    items.forEach((item, idx) => {
        const isActive = idx === composerMentionActiveIndex;
        item.classList.toggle('mention-autocomplete__item--active', isActive);
        item.style.background = isActive ? 'var(--hover-color, #f1f5f9)' : 'transparent';
        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        if (isActive) {
            item.scrollIntoView({ block: 'nearest' });
        }
    });
}

function renderComposerMentionDropdown() {
    if (!composerMentionDropdown || !composerMentionList) return;
    if (!composerMentionMatches.length) {
        closeComposerMentionDropdown();
        return;
    }

    composerMentionList.innerHTML = '';
    composerMentionMatches.forEach((m, idx) => {
        const isActive = idx === composerMentionActiveIndex;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1.5 border-0 ${isActive ? 'mention-autocomplete__item--active' : ''}`;
        btn.style.cssText = 'background: transparent; border-radius: 8px; cursor: pointer; min-height: 40px;';
        if (isActive) {
            btn.style.background = 'var(--hover-color, #f1f5f9)';
        }
        btn.setAttribute('role', 'option');
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');

        const safeFullName = (m.fullName || `${m.firstName} ${m.lastName}`).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c]));

        const profImg = m.profileImg || '/resources/images/profile/avatarM.png';
        btn.innerHTML = `
            <img src="${profImg}" alt="" class="rounded-circle flex-shrink-0" width="28" height="28" style="object-fit: cover;">
            <div class="flex-grow-1 min-w-0">
                <span class="fw-semibold text-dark text-truncate d-block" style="font-size: 0.8125rem;">${safeFullName}</span>
            </div>
            <span class="badge rounded-pill bg-light text-muted border flex-shrink-0" style="font-size: 0.68rem;">
                ${m.famCode ? '#' + m.famCode : 'Family'}
            </span>
        `;

        btn.addEventListener('mouseenter', () => {
            composerMentionActiveIndex = idx;
            updateComposerMentionActiveItem();
        });

        btn.addEventListener('mousedown', (ev) => {
            ev.preventDefault();
        });

        btn.addEventListener('click', (ev) => {
            ev.preventDefault();
            selectComposerMention(m);
        });

        composerMentionList.appendChild(btn);
    });

    composerMentionDropdown.classList.remove('d-none');
}

function selectComposerMention(member) {
    if (!member) return;

    if (!selectedTags.has(member.id)) {
        selectedTags.set(member.id, {
            id: member.id,
            fullName: member.fullName || `${member.firstName} ${member.lastName}`.trim(),
            firstName: member.firstName,
            lastName: member.lastName,
            profileImg: member.profileImg || '/resources/images/profile/avatarM.png',
            famCode: member.famCode || ''
        });
        renderSelectedTags();
        updateSuggestionListSelectionState();
    }

    replaceComposerMentionText(member);
    closeComposerMentionDropdown();
}

function replaceComposerMentionText(member) {
    const fullName = member.fullName || `${member.firstName} ${member.lastName}`.trim();
    if (!composerEditor) return;
    composerEditor.focus();

    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0 && composerEditor.contains(sel.anchorNode)) {
        const range = sel.getRangeAt(0);
        let node = sel.anchorNode;
        if (node.nodeType === Node.TEXT_NODE) {
            const text = node.nodeValue || '';
            const offset = sel.anchorOffset;
            const before = text.slice(0, offset);
            const after = text.slice(offset);
            const atIndex = before.lastIndexOf('@');
            if (atIndex !== -1) {
                const prefix = before.slice(0, atIndex);
                const insertText = `@${fullName} `;
                node.nodeValue = prefix + insertText + after;

                const newOffset = atIndex + insertText.length;
                const newRange = document.createRange();
                newRange.setStart(node, Math.min(newOffset, node.nodeValue.length));
                newRange.collapse(true);
                sel.removeAllRanges();
                sel.addRange(newRange);
                if (typeof window.syncEditorToTextarea === 'function') {
                    window.syncEditorToTextarea();
                }
                return;
            }
        }
    }

    // Fallback: replace on full text string
    const currentText = composerEditor.innerText || composerEditor.textContent || '';
    const replaced = currentText.replace(/(?:^|\s)@([A-Za-z0-9_'\- ]*)$/, (m) => {
        const p = m.startsWith(' ') ? ' ' : '';
        return `${p}@${fullName} `;
    });
    composerEditor.innerText = replaced;
    if (typeof window.syncEditorToTextarea === 'function') {
        window.syncEditorToTextarea();
    }
}

function filterAndShowComposerMentions(rawQuery) {
    const query = (rawQuery || '').trim().toLowerCase();
    const currentUserId = String(window.currentUserId || '');

    const matches = (cachedFamilyMembers || []).filter(m => {
        if (String(m.id) === currentUserId) return false;
        const first = (m.firstName || '').trim().toLowerCase();
        const last = (m.lastName || '').trim().toLowerCase();
        const full = (m.fullName || `${first} ${last}`).trim().toLowerCase();
        if (!first && !last) return false;
        if (!query) return true;
        return full.includes(query) || first.startsWith(query) || last.startsWith(query);
    }).slice(0, 8);

    composerMentionMatches = matches;
    composerMentionActiveIndex = 0;

    if (matches.length > 0) {
        renderComposerMentionDropdown();
    } else if (query.length > 0) {
        if (composerMentionDebounceTimer) clearTimeout(composerMentionDebounceTimer);
        composerMentionDebounceTimer = setTimeout(async () => {
            const serverMembers = await fetchSuggestions(query);
            const filtered = (serverMembers || []).filter(m => String(m.id) !== currentUserId).slice(0, 8);
            composerMentionMatches = filtered;
            composerMentionActiveIndex = 0;
            if (filtered.length > 0) {
                renderComposerMentionDropdown();
            } else {
                closeComposerMentionDropdown();
            }
        }, 150);
    } else {
        closeComposerMentionDropdown();
    }
}

function handleComposerInput(ev) {
    const target = ev.target;
    const isEditor = target === composerEditor;
    const isTextarea = target === postMessageArea;
    if (!isEditor && !isTextarea) return;

    const textBefore = isEditor
        ? getComposerTextBeforeCursor(composerEditor)
        : (target.value ? target.value.slice(0, target.selectionEnd ?? target.value.length) : '');

    const match = textBefore.match(/(?:^|\s)@([A-Za-z0-9_'\- ]{0,40})$/);

    if (match) {
        if (addTagBtn) addTagBtn.classList.add('tag-active');

        if (!cachedFamilyMembers.length) {
            fetchSuggestions('').then(() => {
                filterAndShowComposerMentions(match[1]);
            });
        } else {
            filterAndShowComposerMentions(match[1]);
        }
    } else {
        closeComposerMentionDropdown();
    }
}

function handleComposerKeydown(ev) {
    if (!composerMentionDropdown || composerMentionDropdown.classList.contains('d-none') || !composerMentionMatches.length) {
        return;
    }

    if (ev.key === 'ArrowDown') {
        ev.preventDefault();
        composerMentionActiveIndex = (composerMentionActiveIndex + 1) % composerMentionMatches.length;
        updateComposerMentionActiveItem();
    } else if (ev.key === 'ArrowUp') {
        ev.preventDefault();
        composerMentionActiveIndex = (composerMentionActiveIndex - 1 + composerMentionMatches.length) % composerMentionMatches.length;
        updateComposerMentionActiveItem();
    } else if (ev.key === 'Enter' || ev.key === 'Tab') {
        if (composerMentionMatches[composerMentionActiveIndex]) {
            ev.preventDefault();
            selectComposerMention(composerMentionMatches[composerMentionActiveIndex]);
        }
    } else if (ev.key === 'Escape') {
        ev.preventDefault();
        closeComposerMentionDropdown();
    }
}

// Global auto-tag scan before submit
window.autoTagMentionedUsersInText = function() {
    const text = (document.getElementById('postMessage')?.value || composerEditor?.innerText || '').trim();
    if (!text || !cachedFamilyMembers.length) return;
    const mentionRegex = /(?:^|\s)@([A-Za-z0-9_'\- ]{2,50})/g;
    let m;
    while ((m = mentionRegex.exec(text)) !== null) {
        const nameQuery = m[1].trim().toLowerCase();
        const found = cachedFamilyMembers.find(member => {
            const full = (member.fullName || `${member.firstName} ${member.lastName}`).trim().toLowerCase();
            return full === nameQuery || (member.firstName && member.lastName && full.startsWith(nameQuery));
        });
        if (found && !selectedTags.has(found.id)) {
            selectedTags.set(found.id, {
                id: found.id,
                fullName: found.fullName || `${found.firstName} ${found.lastName}`.trim(),
                firstName: found.firstName,
                lastName: found.lastName,
                profileImg: found.profileImg || '/resources/images/profile/avatarM.png',
                famCode: found.famCode || ''
            });
        }
    }
    renderSelectedTags();
};

if (composerEditor) {
    composerEditor.addEventListener('input', handleComposerInput);
    composerEditor.addEventListener('keyup', (ev) => {
        if (ev.key === '@' || ev.key === 'Backspace' || ev.key.length === 1) {
            handleComposerInput(ev);
        }
    });
    composerEditor.addEventListener('keydown', handleComposerKeydown);
}

if (postMessageArea) {
    postMessageArea.addEventListener('input', handleComposerInput);
    postMessageArea.addEventListener('keyup', (ev) => {
        if (ev.key === '@' || ev.key === 'Backspace' || ev.key.length === 1) {
            handleComposerInput(ev);
        }
    });
    postMessageArea.addEventListener('keydown', handleComposerKeydown);
}

document.addEventListener('click', (ev) => {
    if (composerMentionDropdown && !composerMentionDropdown.classList.contains('d-none')) {
        if (!composerMentionDropdown.contains(ev.target) && ev.target !== composerEditor) {
            closeComposerMentionDropdown();
        }
    }
});

if (addTagBtn && tagContainer) {
    addTagBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = tagContainer.classList.contains('d-none');
        if (isHidden) {
            if (typeof window.closeAllComposerDrawers === 'function') {
                window.closeAllComposerDrawers('tagPeopleContainer');
            }
            tagContainer.classList.remove('d-none');
            addTagBtn.classList.add('tag-active');
            scrollDrawerIntoView(tagContainer);
            if (tagSearchInput) {
                setTimeout(() => tagSearchInput.focus(), 60);
            }
            fetchSuggestions(tagSearchInput ? tagSearchInput.value : '');
        } else {
            closeTagBuilder();
        }
    });

    if (removeTagContainerBtn) {
        removeTagContainerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeTagBuilder();
        });
    }

    if (tagSearchInput) {
        tagSearchInput.addEventListener('input', (e) => {
            const val = e.target.value;
            if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                fetchSuggestions(val);
            }, 150);
        });
    }
}

window.__resetTagBuilder = resetTagBuilder;

// ----------------------------------------------------
// 7. iOS DIRECT APPLE PHOTOS SHARE GUIDE DRAWER
// ----------------------------------------------------
const iosShareBtn = document.getElementById('iosShareBtn');
const iosShareDrawer = document.getElementById('iosShareDrawer');
const closeIosShareDrawerBtn = document.getElementById('closeIosShareDrawerBtn');

const closeIosShareDrawer = () => {
    if (!iosShareDrawer) return;
    iosShareDrawer.classList.add('d-none');
    if (iosShareBtn) iosShareBtn.classList.remove('apple-active');
};

if (iosShareBtn && iosShareDrawer) {
    iosShareBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = iosShareDrawer.classList.contains('d-none');
        if (isHidden) {
            iosShareDrawer.classList.remove('d-none');
            iosShareBtn.classList.add('apple-active');
            scrollDrawerIntoView(iosShareDrawer);
        } else {
            closeIosShareDrawer();
        }
    });
}

if (closeIosShareDrawerBtn) {
    closeIosShareDrawerBtn.addEventListener('click', (e) => {
        e.preventDefault();
        closeIosShareDrawer();
    });
}

window.copyFamilyShareEndpoint = (famCode, token) => {
    const origin = window.location.origin;
    const url = `${origin}/api/media/native-share?fam_code=${encodeURIComponent(famCode)}&user_token=${encodeURIComponent(token)}`;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Direct Link Copied',
                    text: 'The iOS Photos direct upload link has been copied to your clipboard.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Copied direct share link to clipboard!');
            }
        }).catch(() => {
            prompt('Copy this iOS direct upload link:', url);
        });
    } else {
        prompt('Copy this iOS direct upload link:', url);
    }
};

window.__resetIosShareGuide = closeIosShareDrawer;

// ----------------------------------------------------
// 8. UNIFIED DRAWER RESET ON POST MODAL CLOSE
// ----------------------------------------------------
const postModalElement = document.getElementById('postModal');
if (postModalElement) {
    postModalElement.addEventListener('hidden.bs.modal', () => {
        closeEmojiDrawer();
        closeVideoBuilder();
        if (window.__resetGifPicker) window.__resetGifPicker();
        resetTagBuilder();
        closePoll();
        closeIosShareDrawer();
    });
}

if (typeof window !== 'undefined') {
    window.parseVideoUrl = parseVideoUrl;
    window.renderAttachedVideoPreview = renderAttachedVideoPreview;
}
