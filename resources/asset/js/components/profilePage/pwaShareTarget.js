"use strict";

const PWA_SHARE_DB = 'FP_PWA_ShareTarget_DB';
const PWA_SHARE_STORE = 'shared_items';

function openPwaShareDB() {
    return new Promise((resolve, reject) => {
        if (!('indexedDB' in window)) {
            return reject(new Error('IndexedDB not supported'));
        }
        const req = indexedDB.open(PWA_SHARE_DB, 1);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains(PWA_SHARE_STORE)) {
                db.createObjectStore(PWA_SHARE_STORE, { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function peekSharedFromIndexedDB() {
    const db = await openPwaShareDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(PWA_SHARE_STORE, 'readonly');
        const store = tx.objectStore(PWA_SHARE_STORE);
        const getAllReq = store.getAll();
        getAllReq.onsuccess = () => {
            const items = getAllReq.result || [];
            resolve(items.length > 0 ? items[items.length - 1] : null);
        };
        getAllReq.onerror = () => reject(getAllReq.error);
    });
}

async function clearSharedFromIndexedDB() {
    const db = await openPwaShareDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(PWA_SHARE_STORE, 'readwrite');
        const store = tx.objectStore(PWA_SHARE_STORE);
        store.clear();
        tx.oncomplete = () => resolve(true);
        tx.onerror = () => reject(tx.error);
    });
}

function waitForPostModal(timeoutMs = 5000) {
    return new Promise((resolve) => {
        const start = Date.now();
        const check = () => {
            const el = document.getElementById('postModal');
            if (el && (window.bootstrap?.Modal || document.querySelector('[data-bs-target="#postModal"]'))) {
                return resolve(el);
            }
            if (Date.now() - start >= timeoutMs) {
                return resolve(el || null);
            }
            setTimeout(check, 100);
        };
        check();
    });
}

function reconstructSharedFile(item, index) {
    if (item instanceof File) {
        return item;
    }
    if (item instanceof Blob) {
        const ext = (item.type && item.type.split('/')[1]) ? item.type.split('/')[1].split(';')[0] : 'jpg';
        return new File([item], `shared-memory-${index}.${ext}`, { type: item.type || 'image/jpeg' });
    }
    if (item && item.buffer) {
        const type = item.type || 'application/octet-stream';
        const name = item.name || `shared-memory-${index}`;
        return new File([item.buffer], name, {
            type,
            lastModified: item.lastModified || Date.now(),
        });
    }
    return null;
}

export async function initPwaShareTargetHandler() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasPwaShareParam = urlParams.has('pwa_shared');
    const ticket = urlParams.get('ticket');

    let sharedData = null;
    let cameFromIndexedDB = false;

    // 1. Check IndexedDB storage from Service Worker (non-destructive peek first)
    try {
        sharedData = await peekSharedFromIndexedDB();
        if (sharedData) {
            cameFromIndexedDB = true;
        }
    } catch (idbErr) {
        console.warn('[PWA Share Target] IndexedDB lookup error:', idbErr);
    }

    // 2. Check Server Session / Ticket fallback if IndexedDB had nothing
    if (!sharedData) {
        try {
            const ticketParam = ticket ? `?ticket=${encodeURIComponent(ticket)}` : '';
            const resp = await fetch('/api/media/pwa-shared' + ticketParam, {
                credentials: 'same-origin',
            });
            if (resp.ok) {
                const json = await resp.json();
                if (json.status === 'success' && json.data) {
                    sharedData = json.data;
                }
            }
        } catch (srvErr) {
            console.warn('[PWA Share Target] Server fallback lookup error:', srvErr);
        }
    }

    if (!sharedData && !hasPwaShareParam && !ticket) {
        return;
    }

    // Clean up query param from URL without page reload
    if (hasPwaShareParam || ticket) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    if (!sharedData) {
        if (hasPwaShareParam && window.Swal) {
            window.Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'No shared media was received. Please try sharing again or tap Attach Photos.',
                showConfirmButton: false,
                timer: 4500,
                timerProgressBar: true,
            });
        }
        return;
    }

    const postModalEl = await waitForPostModal(5000);
    if (!postModalEl) {
        return;
    }

    if (window.bootstrap && window.bootstrap.Modal) {
        const modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
        modal.show();
    } else {
        const trigger = document.querySelector('[data-bs-target="#postModal"]');
        if (trigger) {
            trigger.click();
        }
    }

    if (cameFromIndexedDB) {
        clearSharedFromIndexedDB().catch(() => {});
    }

    // 1. Populate Caption / Message
    const captionText = sharedData.caption || sharedData.text || sharedData.title || '';
    if (captionText) {
        const textarea = document.getElementById('postMessage');
        if (textarea) {
            textarea.value = captionText;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
        const editor = document.getElementById('postMessageEditor');
        if (editor) {
            editor.innerText = captionText;
            editor.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    // 2. Handle Attached Media Files
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewList = document.getElementById('imagePreviewList');

    if (sharedData.mediaFiles && sharedData.mediaFiles.length > 0) {
        const fileInput = document.getElementById('imageUpload');
        if (fileInput && window.DataTransfer) {
            const dt = new DataTransfer();
            sharedData.mediaFiles.forEach((item, idx) => {
                const file = reconstructSharedFile(item, idx);
                if (file && file.size > 0) {
                    dt.items.add(file);
                }
            });
            if (dt.files.length > 0) {
                fileInput.files = dt.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    } else if (sharedData.files && sharedData.files.length > 0 && previewContainer && previewList) {
        previewContainer.classList.remove('d-none');
        previewList.innerHTML = '';
        previewContainer.querySelectorAll('input[name="shared_temp_images[]"]').forEach((el) => el.remove());

        sharedData.files.forEach((fileName) => {
            const isVideo = /\.(mp4|webm|mov|ogg)$/i.test(fileName);
            if (isVideo) {
                const vid = document.createElement('video');
                vid.src = '/resources/images/post/temp/' + fileName;
                vid.className = 'rounded-3 shadow-sm object-fit-cover';
                vid.style.width = '75px';
                vid.style.height = '75px';
                vid.muted = true;
                vid.playsInline = true;
                previewList.appendChild(vid);
            } else {
                const img = document.createElement('img');
                img.src = '/resources/images/post/temp/' + fileName;
                img.className = 'rounded-3 shadow-sm object-fit-cover';
                img.style.width = '75px';
                img.style.height = '75px';
                previewList.appendChild(img);
            }

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'shared_temp_images[]';
            hidden.value = fileName;
            previewContainer.appendChild(hidden);
        });

        const countEl = document.getElementById('postModalImgFileNames');
        if (countEl) {
            const label = sharedData.files.length === 1 ? '1 shared memory' : `${sharedData.files.length} shared memories`;
            countEl.textContent = label;
        }
    }

    // 3. User Feedback & 1-Tap Quick Publish Action in Modal
    const notif = document.getElementById('formPostMessageModal_notification');
    if (notif) {
        notif.innerHTML = `
            <div class="alert alert-light border border-success-subtle py-2.5 px-3 small rounded-3 mb-3 shadow-xs d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2" style="background: rgba(16, 185, 129, 0.05);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-phone-fill text-success fs-5 flex-shrink-0"></i>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">Shared Media Received!</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Ready to publish to your family timeline.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <button type="button" id="quickPublishBtn" class="btn btn-sm btn-primary rounded-pill fw-bold px-3 py-1.5 d-flex align-items-center gap-1 shadow-xs" style="font-size: 0.78rem; background-color: #a31621; border-color: #a31621; min-height: 44px;">
                        <i class="bi bi-send-fill"></i> Publish Now
                    </button>
                    <button type="button" id="addStoryCaptionBtn" class="btn btn-sm btn-outline-secondary rounded-pill fw-medium px-2.5 py-1.5" style="font-size: 0.78rem; min-height: 44px;">
                        Add Story
                    </button>
                </div>
            </div>
        `;

        const quickBtn = document.getElementById('quickPublishBtn');
        if (quickBtn) {
            quickBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const submitBtn = document.getElementById('submitPost');
                if (submitBtn) {
                    submitBtn.click();
                }
            });
        }

        const storyBtn = document.getElementById('addStoryCaptionBtn');
        if (storyBtn) {
            storyBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const editor = document.getElementById('postMessageEditor') || document.getElementById('postMessage');
                if (editor) {
                    editor.focus();
                }
            });
        }
    }
}

/**
 * Smart Clipboard Camera Roll Bridge (Zero-Install iOS & Desktop Solution)
 */
export function initSmartClipboardPaste() {
    window.pastePhotoFromClipboard = async function() {
        if (!navigator.clipboard || !navigator.clipboard.read) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'Clipboard Access',
                    text: 'To attach a copied photo, press Cmd+V (Mac) or Ctrl+V (Windows/Android) whilst the composer is open.',
                    confirmButtonColor: '#a31621'
                });
            }
            return;
        }

        try {
            const items = await navigator.clipboard.read();
            const files = [];
            for (const item of items) {
                for (const type of item.types) {
                    if (type.startsWith('image/')) {
                        const blob = await item.getType(type);
                        const ext = type.split('/')[1] ? type.split('/')[1].replace('+xml', '') : 'png';
                        const file = new File([blob], `clipboard_photo_${Date.now()}.${ext}`, { type });
                        files.push(file);
                    }
                }
            }

            if (files.length > 0) {
                const imageInput = document.getElementById('imageUpload');
                if (imageInput && window.DataTransfer) {
                    const dt = new DataTransfer();
                    files.forEach(f => dt.items.add(f));
                    imageInput.files = dt.files;
                    imageInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                const postModalEl = document.getElementById('postModal');
                if (postModalEl && window.bootstrap && window.bootstrap.Modal) {
                    const modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
                    modal.show();
                }

                if (window.Swal) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Photo attached from camera roll clipboard!'
                    });
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Photo in Clipboard',
                        text: 'Open Apple Photos or your phone gallery, tap Share &rarr; Copy (or copy a photo), then tap this button again.',
                        confirmButtonColor: '#a31621'
                    });
                }
            }
        } catch (err) {
            console.warn('[Smart Clipboard] Error reading clipboard:', err);
            if (window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'Clipboard Permission',
                    text: 'Please allow clipboard access when prompted to paste photos directly from your camera roll.',
                    confirmButtonColor: '#a31621'
                });
            }
        }
    };
}
