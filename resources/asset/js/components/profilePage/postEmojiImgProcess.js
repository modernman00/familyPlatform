// Import a helper function to get elements by ID (assumed from your shared utils)
import { showEmojiPicker, initEmojiPickerUX } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';

// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

const emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
const emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
const closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

if (document.getElementById('emojiListPost')) {
    showEmojiPicker('emojiListPost', 'data-emoji-target');
}
if (emojiToggle && emojiContainer) {
    initEmojiPickerUX('emojiPost', 'emojiPickerContainer');
}

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
if (emojiToggle && emojiContainer) {
    emojiToggle.addEventListener('click', () => {
        emojiContainer.classList.toggle('d-none'); // Show/hide the emoji container
        emojiToggle.setAttribute('aria-expanded', emojiContainer.classList.contains('d-none') ? 'false' : 'true');
    });
}

// Close button handler
if (closeEmojiBtn && emojiContainer && emojiToggle) {
    closeEmojiBtn.addEventListener('click', () => {
        emojiContainer.classList.add('d-none');
        emojiToggle.setAttribute('aria-expanded', 'false');
    });
}

if (document.getElementById('imageUpload')) {
    imagePreview('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');
}

// Poll Creation UI Logic
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
    addPollBtn.addEventListener('click', () => {
        const isHidden = pollContainer.classList.contains('d-none');
        if (isHidden) {
            pollContainer.classList.remove('d-none');
            addPollBtn.classList.add('poll-active');
            const questionInput = pollContainer.querySelector('input[name="poll_question"]');
            if (questionInput) setTimeout(() => questionInput.focus(), 60);
        } else {
            closePoll();
        }
    });

    if (removePollBtn) {
        removePollBtn.addEventListener('click', closePoll);
    }

    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', () => {
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

// Expose so the submit handler can reset the builder after a successful post
window.__resetPollBuilder = closePoll;

// Video Embed UI Logic
import { parseVideoUrl } from './videoParser';

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

const closeVideoBuilder = () => {
    if (!videoContainer) return;
    videoContainer.classList.add('d-none');
    if (addVideoBtn) addVideoBtn.classList.remove('video-active');
    if (videoInput) videoInput.value = '';
    if (videoDirectFileInput) videoDirectFileInput.value = '';
    if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
    if (videoLivePreview) {
        videoLivePreview.innerHTML = '';
        videoLivePreview.classList.add('d-none');
    }
};

if (addVideoBtn && videoContainer) {
    addVideoBtn.addEventListener('click', () => {
        const isHidden = videoContainer.classList.contains('d-none');
        if (isHidden) {
            videoContainer.classList.remove('d-none');
            addVideoBtn.classList.add('video-active');
            if (videoInput) setTimeout(() => videoInput.focus(), 60);
        } else {
            closeVideoBuilder();
        }
    });

    if (removeVideoBtn) {
        removeVideoBtn.addEventListener('click', closeVideoBuilder);
    }

    // Direct Video File Upload to Cloudflare Stream
    if (videoDirectFileInput) {
        videoDirectFileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Cap at 100MB
            if (file.size > 100 * 1024 * 1024) {
                alert('Video size exceeds 100MB. Please select a shorter video clip.');
                videoDirectFileInput.value = '';
                return;
            }

            // Enforce strict 30-second duration cap via browser metadata check
            try {
                const duration = await new Promise((resolve) => {
                    const tempVideo = document.createElement('video');
                    tempVideo.preload = 'metadata';
                    const blobUrl = URL.createObjectURL(file);
                    tempVideo.src = blobUrl;

                    tempVideo.onloadedmetadata = () => {
                        URL.revokeObjectURL(blobUrl);
                        resolve(tempVideo.duration);
                    };

                    tempVideo.onerror = () => {
                        URL.revokeObjectURL(blobUrl);
                        resolve(null);
                    };
                });

                if (duration && duration > 30.5) {
                    alert(`Video duration (${Math.round(duration)}s) exceeds the maximum limit of 30 seconds. Please select or trim a shorter clip.`);
                    videoDirectFileInput.value = '';
                    return;
                }
            } catch (err) {
                console.warn('Video duration check skipped:', err);
            }

            if (videoUploadProgressWrapper) {
                videoUploadProgressWrapper.classList.remove('d-none');
                if (videoProgressBar) videoProgressBar.style.width = '10%';
                if (videoUploadStatus) videoUploadStatus.textContent = 'Requesting secure upload token...';
                if (videoUploadPercent) videoUploadPercent.textContent = '10%';
            }

            try {
                // 1. Request one-time upload URL from our backend with strict 30s cap
                const tokenRes = await fetch('/api/video/direct-upload', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ maxDuration: 30 })
                });

                const tokenData = await tokenRes.json();

                if (!tokenRes.ok || !tokenData.success || !tokenData.uploadUrl) {
                    throw new Error(tokenData.error || 'Failed to obtain direct upload URL.');
                }

                // 2. Upload file directly to Cloudflare Stream via XMLHttpRequest for progress tracking
                if (videoUploadStatus) videoUploadStatus.textContent = 'Uploading directly to Cloudflare edge...';
                
                const formData = new FormData();
                formData.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', tokenData.uploadUrl, true);

                xhr.upload.onprogress = (evt) => {
                    if (evt.lengthComputable && videoProgressBar && videoUploadPercent) {
                        const percent = Math.round((evt.loaded / evt.total) * 100);
                        videoProgressBar.style.width = `${percent}%`;
                        videoUploadPercent.textContent = `${percent}%`;
                    }
                };

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const streamUrl = tokenData.streamUrl || `https://iframe.videodelivery.net/${tokenData.videoId}`;
                        if (videoUploadStatus) videoUploadStatus.textContent = 'Upload complete! Video ready to stream.';
                        if (videoProgressBar) {
                            videoProgressBar.style.width = '100%';
                            videoProgressBar.classList.remove('progress-bar-animated');
                        }
                        if (videoInput) videoInput.value = streamUrl;

                        // Render live player preview
                        if (videoLivePreview) {
                            videoLivePreview.innerHTML = `<iframe src="${streamUrl}" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>`;
                            videoLivePreview.classList.remove('d-none');
                        }

                        // Auto-append URL to post text
                        if (postMessageArea && !postMessageArea.value.includes(streamUrl)) {
                            postMessageArea.value = postMessageArea.value ? `${postMessageArea.value.trim()}\n${streamUrl}` : streamUrl;
                        }
                    } else {
                        alert('Cloudflare upload error. Please try again.');
                        if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
                    }
                };

                xhr.onerror = () => {
                    alert('Network error during video upload. Please check your internet connection.');
                    if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
                };

                xhr.send(formData);
            } catch (err) {
                console.warn('[VideoUpload] Direct upload failed:', err);
                alert(err.message || 'Direct video upload could not be initialized.');
                if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
            }
        });
    }

    if (videoInput && videoLivePreview) {
        videoInput.addEventListener('input', (e) => {
            const url = e.target.value.trim();
            const parsed = parseVideoUrl(url);

            if (parsed) {
                let previewHtml = '';
                if (parsed.type === 'youtube' || parsed.type === 'vimeo' || parsed.type === 'cloudflare') {
                    previewHtml = `<iframe src="${parsed.embedUrl}" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>`;
                } else if (parsed.type === 'direct') {
                    previewHtml = `<video src="${parsed.embedUrl}" controls style="width:100%; height:100%; object-fit:contain; background:#000;"></video>`;
                }
                videoLivePreview.innerHTML = previewHtml;
                videoLivePreview.classList.remove('d-none');

                // Auto-append URL to post text if not already there
                if (postMessageArea && !postMessageArea.value.includes(url)) {
                    postMessageArea.value = postMessageArea.value ? `${postMessageArea.value.trim()}\n${url}` : url;
                }
            } else {
                videoLivePreview.innerHTML = '';
                videoLivePreview.classList.add('d-none');
            }
        });
    }
}

window.__resetVideoBuilder = closeVideoBuilder;

