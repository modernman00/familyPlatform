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

async function getAndClearSharedFromIndexedDB() {
    const db = await openPwaShareDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(PWA_SHARE_STORE, 'readwrite');
        const store = tx.objectStore(PWA_SHARE_STORE);
        const getAllReq = store.getAll();
        getAllReq.onsuccess = () => {
            const items = getAllReq.result || [];
            if (items.length > 0) {
                store.clear();
                tx.oncomplete = () => resolve(items[items.length - 1]);
            } else {
                resolve(null);
            }
        };
        getAllReq.onerror = () => reject(getAllReq.error);
    });
}

export async function initPwaShareTargetHandler() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasPwaShareParam = urlParams.has('pwa_shared');

    let sharedData = null;

    // 1. Check IndexedDB storage from Service Worker
    try {
        sharedData = await getAndClearSharedFromIndexedDB();
    } catch (idbErr) {
        console.warn('[PWA Share Target] IndexedDB lookup error:', idbErr);
    }

    // 2. Check Server Session fallback if IndexedDB had nothing
    if (!sharedData) {
        try {
            const resp = await fetch('/api/media/pwa-shared');
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

    if (!sharedData && !hasPwaShareParam) {
        return;
    }

    // Clean up query param from URL without page reload
    if (hasPwaShareParam) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    if (sharedData) {
        setTimeout(() => {
            const postModalEl = document.getElementById('postModal');
            if (postModalEl && window.bootstrap && window.bootstrap.Modal) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
                modal.show();

                // 1. Populate Caption / Message
                const captionText = sharedData.caption || sharedData.text || sharedData.title || '';
                if (captionText) {
                    const textarea = document.getElementById('postMessage');
                    if (textarea) {
                        textarea.value = captionText;
                        textarea.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }

                // 2. Handle Attached Media Files
                const previewContainer = document.getElementById('imagePreviewContainer');
                const previewList = document.getElementById('imagePreviewList');

                if (sharedData.mediaFiles && sharedData.mediaFiles.length > 0) {
                    const fileInput = document.getElementById('imageUpload');
                    if (fileInput && window.DataTransfer) {
                        const dt = new DataTransfer();
                        sharedData.mediaFiles.forEach((file) => {
                            if (file instanceof File || file instanceof Blob) {
                                dt.items.add(file instanceof File ? file : new File([file], 'shared-memory.jpg', { type: file.type || 'image/jpeg' }));
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
                    sharedData.files.forEach((fileName) => {
                        const img = document.createElement('img');
                        img.src = '/resources/images/post/temp/' + fileName;
                        img.className = 'rounded-3 shadow-sm object-fit-cover';
                        img.style.width = '75px';
                        img.style.height = '75px';
                        previewList.appendChild(img);

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'shared_temp_images[]';
                        hidden.value = fileName;
                        previewContainer.appendChild(hidden);
                    });
                }

                // 3. User Feedback Toast in Modal
                const notif = document.getElementById('formPostMessageModal_notification');
                if (notif) {
                    notif.innerHTML = `
                        <div class="alert alert-success py-2 px-3 small rounded-3 d-flex align-items-center justify-content-between mb-3 shadow-xs">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-phone-fill text-success fs-5"></i>
                                <span><strong>Gallery Photo Received!</strong> Shared directly into your family composer.</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Web Share Target</span>
                        </div>
                    `;
                }
            }
        }, 350);
    }
}
