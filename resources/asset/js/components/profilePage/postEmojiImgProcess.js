// Import helper functions and components
import { showEmojiPicker, initEmojiPickerUX } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';
import { parseVideoUrl } from './videoParser';
import axios from 'axios';

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
// 1. EMOJI PICKER (Floating Popover UX with Caret & Auto-Dismiss)
// ----------------------------------------------------
const emojiToggle = document.getElementById('emojiPost');
const emojiContainer = document.getElementById('emojiPickerContainer');
const closeEmojiBtn = document.getElementById('closeEmojiPicker');

if (document.getElementById('emojiListPost')) {
    showEmojiPicker('emojiListPost', 'data-emoji-target');
}

if (emojiToggle && emojiContainer) {
    initEmojiPickerUX('emojiPost', 'emojiPickerContainer');
}

const closeEmojiPicker = () => {
    if (!emojiContainer) return;
    emojiContainer.classList.add('d-none');
    if (emojiToggle) {
        emojiToggle.classList.remove('emoji-active');
        emojiToggle.setAttribute('aria-expanded', 'false');
    }
};

// Sync active class and aria-expanded whenever the container is dismissed (e.g. on emoji selection)
if (emojiContainer && window.MutationObserver) {
    const emojiObserver = new MutationObserver(() => {
        if (emojiContainer.classList.contains('d-none')) {
            if (emojiToggle) {
                emojiToggle.classList.remove('emoji-active');
                emojiToggle.setAttribute('aria-expanded', 'false');
            }
        }
    });
    emojiObserver.observe(emojiContainer, { attributes: true, attributeFilter: ['class'] });
}

if (emojiToggle && emojiContainer) {
    emojiToggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isHidden = emojiContainer.classList.contains('d-none');
        if (isHidden) {
            emojiContainer.classList.remove('d-none');
            emojiToggle.classList.add('emoji-active');
            emojiToggle.setAttribute('aria-expanded', 'true');
        } else {
            closeEmojiPicker();
        }
    });
}

if (closeEmojiBtn) {
    closeEmojiBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeEmojiPicker();
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
// 4. VIDEO EMBED & UPLOAD UI LOGIC
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
    addVideoBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = videoContainer.classList.contains('d-none');
        if (isHidden) {
            videoContainer.classList.remove('d-none');
            addVideoBtn.classList.add('video-active');
            scrollDrawerIntoView(videoContainer);
            if (videoInput) setTimeout(() => videoInput.focus(), 60);
        } else {
            closeVideoBuilder();
        }
    });

    if (removeVideoBtn) {
        removeVideoBtn.addEventListener('click', (e) => {
            e.preventDefault();
            closeVideoBuilder();
        });
    }

    // Direct Video File Upload to Cloudflare Stream
    if (videoDirectFileInput) {
        videoDirectFileInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Cap at 100MB
            if (file.size > 100 * 1024 * 1024) {
                (window.swalError || alert)('Video size exceeds 100MB. Please select a shorter video clip.');
                videoDirectFileInput.value = '';
                return;
            }

            // Enforce strict 30-second duration cap via browser metadata check
            let clipDuration = null;
            try {
                clipDuration = await new Promise((resolve) => {
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

                if (clipDuration && clipDuration > 30.5) {
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: '30-Second Video Limit',
                            html: `
                                <div style="text-align: left; font-size: 0.95rem;">
                                    <p>Direct video uploads on FamilyPlatform are limited to a maximum of <strong>30 seconds</strong> to keep family feeds fast and engaging.</p>
                                    <div style="background: #FFF3CD; border-left: 4px solid #FFC107; padding: 10px 14px; border-radius: 6px; margin: 12px 0;">
                                        <span style="color: #856404; font-weight: 600;">Selected Video Duration:</span> 
                                        <span style="color: #DC3545; font-weight: 700;">${Math.round(clipDuration)} seconds</span>
                                    </div>
                                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Please trim your clip to 30 seconds or choose a shorter video.</p>
                                </div>
                            `,
                            confirmButtonText: 'Understood',
                            confirmButtonColor: '#0056b3'
                        });
                    } else {
                        alert(`Video duration (${Math.round(clipDuration)}s) exceeds the maximum limit of 30 seconds. Please select or trim a shorter clip.`);
                    }
                    videoDirectFileInput.value = '';
                    return;
                }
            } catch (err) {
                console.warn('Video duration check skipped:', err);
            }

            if (videoUploadProgressWrapper) {
                videoUploadProgressWrapper.classList.remove('d-none');
                if (videoProgressBar) videoProgressBar.style.width = '10%';
                const durationLabel = clipDuration ? `Clip: ${Math.round(clipDuration)}s • ` : '';
                if (videoUploadStatus) videoUploadStatus.textContent = `${durationLabel}Requesting secure upload token...`;
                if (videoUploadPercent) videoUploadPercent.textContent = '10%';
            }

            // Read selected video expiration
            const expirySelect = document.getElementById('videoExpirySelect');
            const expirySeconds = expirySelect ? parseInt(expirySelect.value, 10) : 2592000;

            try {
                const tokenRes = await fetch('/api/video/direct-upload', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        maxDuration: 30,
                        expirySeconds: expirySeconds
                    })
                });

                const tokenData = await tokenRes.json();

                if (!tokenRes.ok || !tokenData.success || !tokenData.uploadUrl) {
                    if (tokenRes.status === 403 && window.Swal) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: '👑 Premium Retention Required',
                            text: tokenData.error || '1 Year and Permanent video retention are available exclusively for Premium members.',
                            confirmButtonText: 'Select 30 Days',
                            confirmButtonColor: '#1e6040'
                        });
                        if (expirySelect) expirySelect.value = '2592000';
                    }
                    throw new Error(tokenData.error || 'Failed to obtain direct upload URL.');
                }

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

                        if (videoLivePreview) {
                            videoLivePreview.innerHTML = `<iframe src="${streamUrl}" style="width:100%; height:100%; border:none;" allowfullscreen></iframe>`;
                            videoLivePreview.classList.remove('d-none');
                        }

                        if (postMessageArea && !postMessageArea.value.includes(streamUrl)) {
                            postMessageArea.value = postMessageArea.value ? `${postMessageArea.value.trim()}\n\n${streamUrl}` : streamUrl;
                        }
                    } else {
                        (window.swalError || alert)('Cloudflare upload error. Please try again.');
                        if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
                    }
                };

                xhr.onerror = () => {
                    (window.swalError || alert)('Network error during video upload. Please check your internet connection.');
                    if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
                };

                xhr.send(formData);
            } catch (err) {
                console.warn('[VideoUpload] Direct upload failed:', err);
                if (videoUploadProgressWrapper) videoUploadProgressWrapper.classList.add('d-none');
            }
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

                if (postMessageArea && !postMessageArea.value.includes(url)) {
                    postMessageArea.value = postMessageArea.value ? `${postMessageArea.value.trim()}\n\n${url}` : url;
                }
            } else {
                videoLivePreview.innerHTML = '';
                videoLivePreview.classList.add('d-none');
            }
        });
    }
}

window.__resetVideoBuilder = closeVideoBuilder;

// ----------------------------------------------------
// 5. FAMILY GIF PICKER (Curated, Zero-Friction)
// ----------------------------------------------------
const FAMILY_GIFS = [
    // Celebrate
    { id: 'g_cel_1', cat: 'celebrate', title: 'Party Confetti Celebration', url: 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif' },
    { id: 'g_cel_2', cat: 'celebrate', title: 'Cheers Toast Champagne', url: 'https://media.giphy.com/media/g9582DNuQppxC/giphy.gif' },
    { id: 'g_cel_3', cat: 'celebrate', title: 'Celebration Fireworks', url: 'https://media.giphy.com/media/26tOZ42Mg6pbTUPHW/giphy.gif' },
    { id: 'g_cel_4', cat: 'celebrate', title: 'High Five Teamwork', url: 'https://media.giphy.com/media/3oEjHV0z8S7WM4MwnK/giphy.gif' },

    // Love & Hugs
    { id: 'g_lov_1', cat: 'love', title: 'Warm Big Hug', url: 'https://media.giphy.com/media/l8ooTMA4bGUgs/giphy.gif' },
    { id: 'g_lov_2', cat: 'love', title: 'Heart Flying Love', url: 'https://media.giphy.com/media/26BRv0ThflsDTqUXa/giphy.gif' },
    { id: 'g_lov_3', cat: 'love', title: 'Family Love Heart', url: 'https://media.giphy.com/media/M90mJvfWfd5mbUuULX/giphy.gif' },
    { id: 'g_lov_4', cat: 'love', title: 'Sending Hugs Bear', url: 'https://media.giphy.com/media/3bqtLDeiDtwhq/giphy.gif' },

    // Family
    { id: 'g_fam_1', cat: 'family', title: 'We Are Family Fast', url: 'https://media.giphy.com/media/amg2hcfGDkKt4Q3mV7/giphy.gif' },
    { id: 'g_fam_2', cat: 'family', title: 'Family Dinner Gathering', url: 'https://media.giphy.com/media/xT0xeMA62E1URCXqYo/giphy.gif' },
    { id: 'g_fam_3', cat: 'family', title: 'Proud Parents Cheer', url: 'https://media.giphy.com/media/3ohzdIuqJoo8QdKlnW/giphy.gif' },
    { id: 'g_fam_4', cat: 'family', title: 'Group Family Hug', url: 'https://media.giphy.com/media/VGtdt3dVAbQYTk0fnu/giphy.gif' },

    // Laughs
    { id: 'g_lau_1', cat: 'laugh', title: 'Laughing Out Loud LOL', url: 'https://media.giphy.com/media/10JhviFuU2gWD6/giphy.gif' },
    { id: 'g_lau_2', cat: 'laugh', title: 'Giggle Chuckle Cute', url: 'https://media.giphy.com/media/1d5Zn8FqmJqApu4hNU/giphy.gif' },
    { id: 'g_lau_3', cat: 'laugh', title: 'Rolling On Floor Laughing', url: 'https://media.giphy.com/media/CoDp6NnSmItoY/giphy.gif' },
    { id: 'g_lau_4', cat: 'laugh', title: 'Happy Tears Joy', url: 'https://media.giphy.com/media/26n6Gx9moCpHU1HO8/giphy.gif' },

    // Birthday
    { id: 'g_bdy_1', cat: 'birthday', title: 'Happy Birthday Cake', url: 'https://media.giphy.com/media/KdC9XVrVYO0LZhv1cU/giphy.gif' },
    { id: 'g_bdy_2', cat: 'birthday', title: 'Birthday Candles Wish', url: 'https://media.giphy.com/media/MCeIiRETfwBK2rtVAM/giphy.gif' },
    { id: 'g_bdy_3', cat: 'birthday', title: 'Birthday Balloons Celebration', url: 'https://media.giphy.com/media/GvjRy6pUMUjQxiKNlh/giphy.gif' },
    { id: 'g_bdy_4', cat: 'birthday', title: 'Birthday Dance Party', url: 'https://media.giphy.com/media/l2YWvIqfPvXgN2Q2k/giphy.gif' },

    // Joy / Dance
    { id: 'g_dnc_1', cat: 'dance', title: 'Happy Dance Groove', url: 'https://media.giphy.com/media/blSTtZehjAZ8I/giphy.gif' },
    { id: 'g_dnc_2', cat: 'dance', title: 'Pure Joy Dancing Snoopy', url: 'https://media.giphy.com/media/chzz1FQgqhytWRWbp3/giphy.gif' },
    { id: 'g_dnc_3', cat: 'dance', title: 'Excited Celebration Dance', url: 'https://media.giphy.com/media/5GoVLqeAOo6PK/giphy.gif' },
    { id: 'g_dnc_4', cat: 'dance', title: 'Victory Groove Celebration', url: 'https://media.giphy.com/media/l3q2wJsC23ikJg9xe/giphy.gif' }
];

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
                            confirmButtonColor: '#2563eb'
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

const fetchSuggestions = async (query = '') => {
    if (!tagSuggestionsList) return;
    try {
        const res = await axios.get('/api/members/tag-suggestions', {
            params: { q: query.trim() }
        });
        const members = res?.data?.data?.members || res?.data?.members || [];
        renderSuggestions(members);
    } catch (err) {
        console.error('[TagPeople] Failed to fetch tag suggestions:', err);
        tagSuggestionsList.innerHTML = `
            <div class="text-center py-2 text-danger small">
                Failed to load family members. Tap to retry.
            </div>
        `;
        tagSuggestionsList.firstElementChild?.addEventListener('click', () => fetchSuggestions(query));
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
    if (tagSearchInput) tagSearchInput.value = '';
};

if (addTagBtn && tagContainer) {
    addTagBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const isHidden = tagContainer.classList.contains('d-none');
        if (isHidden) {
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

    if (postMessageArea) {
        postMessageArea.addEventListener('keyup', (e) => {
            if (e.key === '@') {
                if (tagContainer.classList.contains('d-none')) {
                    tagContainer.classList.remove('d-none');
                    addTagBtn.classList.add('tag-active');
                    scrollDrawerIntoView(tagContainer);
                    fetchSuggestions('');
                }
                if (tagSearchInput) {
                    setTimeout(() => tagSearchInput.focus(), 60);
                }
            }
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
    const url = `${origin}/api/media/ios-shortcut?fam_code=${encodeURIComponent(famCode)}&token=${encodeURIComponent(token)}`;
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
        closeEmojiPicker();
        closeVideoBuilder();
        if (window.__resetGifPicker) window.__resetGifPicker();
        resetTagBuilder();
        closePoll();
        closeIosShareDrawer();
    });
}
