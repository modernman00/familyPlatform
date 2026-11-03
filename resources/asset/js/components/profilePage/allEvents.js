"use strict";
import axios from "axios"
import Swal from 'sweetalert2';
import { id, log, msgException } from "@modernman00/shared-js-lib"
import { deleteNotification } from "../global.js"
import { getSelectedPostFiles, clearSelectedPostFiles } from "../fileUploadPreview";

/**
 * Reliably close the "Create Post" modal. Clicking [data-bs-dismiss] can miss if
 * Bootstrap never instantiated the modal (e.g. opened programmatically), which
 * left the backdrop covering the feed and broke follow-up interactions.
 */
function stripModalChrome() {
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
}

function closePostModal() {
    const modalEl = document.getElementById('postModal');
    if (!modalEl) return;

    // Let Bootstrap run its own teardown (fires hidden.bs.modal, restores focus…).
    const Bs = window.bootstrap?.Modal;
    if (Bs) {
        try { Bs.getOrCreateInstance(modalEl).hide(); } catch (_) { /* noop */ }
    }

    // …but don't wait on the fade-out animation: hide the modal and clear the
    // backdrop synchronously so the feed underneath is immediately interactive.
    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');
    modalEl.removeAttribute('role');
    stripModalChrome();

    // A late Bootstrap transition callback can re-add body chrome; sweep once more.
    setTimeout(stripModalChrome, 350);
}

try {
    const options = {
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
    }

    // Reset the modal out of "edit" mode however it closes (submit, cancel,
    // backdrop click, ESC) — editPost() in feedComponent.js is what puts it
    // into edit mode by setting these same three things.
    const postModalEl = document.getElementById('postModal');
    if (postModalEl) {
        postModalEl.addEventListener('hidden.bs.modal', () => {
            const editPostNo = document.getElementById('editPostNo');
            const notice = document.getElementById('editPostNotice');
            const title = document.getElementById('postModalLabel');
            if (editPostNo) editPostNo.value = '';
            if (notice) notice.classList.add('d-none');
            if (title) title.textContent = 'Create Post';
            if (typeof window.__resetTagBuilder === 'function') {
                window.__resetTagBuilder();
            }
            if (typeof window.__resetThenNow === 'function') {
                window.__resetThenNow();
            }
            if (typeof window.__resetVideoBuilder === 'function') {
                window.__resetVideoBuilder();
            }
        });
    }

    document.addEventListener('click', async (e) => {
        const submitBtn = e.target.closest('#submitPost, .submitPost');
        const elementId = e.target.id;

        // SUBMIT THE NEW POST (from modal)
        if (submitBtn) {
            e.preventDefault();
            if (submitBtn.disabled || submitBtn.getAttribute('aria-busy') === 'true') {
                return;
            }

            const formExtra = id('formPostMessageModal');
            if (!formExtra) return;

            // editPost() in feedComponent.js stamps this when reopening the modal
            // to edit an existing post instead of creating a new one.
            const editPostNo = document.getElementById('editPostNo')?.value;
            const isEditing = !!editPostNo;
            const originalBtnText = submitBtn.innerHTML;

            // Immediate visual feedback with spinner
            if (window.ScoreTenantSpinner) {
                window.ScoreTenantSpinner.start(submitBtn);
            } else {
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${isEditing ? 'Saving...' : 'Posting...'}`;
                submitBtn.disabled = true;
            }

            try {
                // Validate poll if it's being created
                const pollContainer = document.getElementById('pollCreationContainer');
                if (pollContainer && !pollContainer.classList.contains('d-none')) {
                    const pollQuestion = formExtra.querySelector('input[name="poll_question"]')?.value?.trim();
                    const pollOptions = Array.from(formExtra.querySelectorAll('input[name="poll_options[]"]'))
                        .map(input => input.value.trim())
                        .filter(val => val.length > 0);

                    if (!pollQuestion) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Poll Incomplete',
                            text: 'Please enter a poll question.',
                            confirmButtonColor: '#a31621'
                        });
                        return;
                    }

                    if (pollOptions.length < 2) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Poll Incomplete',
                            text: 'Please provide at least 2 poll options.',
                            confirmButtonColor: '#a31621'
                        });
                        return;
                    }
                }

                // Ensure any staged or uploading video (including >30s auto-trim) finishes attaching before FormData is built.
                if (!isEditing && typeof window.__ensurePendingVideoUploaded === 'function') {
                    await window.__ensurePendingVideoUploaded();
                }

                // Then & Now needs both photos before anything is sent.
                if (!isEditing && typeof window.__thenNowReady === 'function') {
                    await window.__thenNowReady();
                }

                const thenNow = !isEditing && typeof window.__getThenNowFiles === 'function' ? window.__getThenNowFiles() : null;
                if (!isEditing && document.getElementById('thenNowFlag')?.value === '1' && (!thenNow?.then || !thenNow?.now)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Then & Now Incomplete',
                        text: 'Please pick both a Then photo and a Now photo.',
                        confirmButtonColor: '#a31621'
                    });
                    return;
                }

                // Prevent empty post submission with friendly tactile prompt
                if (!isEditing) {
                    const postMsgVal = (id('postMessage')?.value || '').trim();
                    const selectedFiles = getSelectedPostFiles();
                    const fileInput = document.getElementById('imageUpload');
                    const hasRawFiles = (selectedFiles && selectedFiles.length > 0) || (fileInput && fileInput.files && fileInput.files.length > 0);
                    const hasThenNow = document.getElementById('thenNowFlag')?.value === '1' && thenNow?.then && thenNow?.now;
                    const hasPoll = pollContainer && !pollContainer.classList.contains('d-none');
                    const attachedVidInput = document.getElementById('attachedVideoUrl')?.value || '';
                    const attachedVidsInput = document.getElementById('attachedVideoUrls')?.value || '';
                    const hasAttachedVideo = attachedVidInput !== '' || (attachedVidsInput !== '' && attachedVidsInput !== '[]');
                    const hasGif = Boolean(document.getElementById('attachedGifUrl')?.value);

                    if (!postMsgVal && !hasRawFiles && !hasThenNow && !hasPoll && !hasAttachedVideo && !hasGif) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Empty Post',
                            text: 'Please add some text, an image, a video, or a poll before posting.',
                            confirmButtonColor: '#a31621'
                        });
                        return;
                    }
                }

                let formData;
                if (!isEditing) {
                    formData = new FormData(formExtra);
                    const requesterFamCodeValue = localStorage.getItem('requesterFamCode');
                    formData.append('postFamCode', requesterFamCodeValue);

                    // Explicitly ensure all accumulated image files are appended directly to formData
                    const selectedFiles = getSelectedPostFiles();
                    const fileInput = document.getElementById('imageUpload');
                    const rawFiles = (selectedFiles && selectedFiles.length > 0)
                        ? selectedFiles
                        : (fileInput && fileInput.files ? Array.from(fileInput.files) : []);

                    if (thenNow) {
                        formData.delete('post_img[]');
                        formData.delete('post_img');
                        formData.append('post_img[]', thenNow.then, thenNow.then.name);
                        formData.append('post_img[]', thenNow.now, thenNow.now.name);
                    } else if (rawFiles.length > 0) {
                        formData.delete('post_img[]');
                        formData.delete('post_img');
                        rawFiles.forEach((file) => {
                            formData.append('post_img[]', file, file.name);
                        });
                    }
                }

                // Editing is text-only, so it goes as JSON to a PUT endpoint rather
                // than the multipart POST the create flow uses (images/poll aren't
                // part of the edit payload — see PostMessage::updatePost).
                const response = isEditing
                    ? await axios.put(`/post/${editPostNo}`, { postMessage: id('postMessage').value }, options)
                    : await axios.post("/member/profilePage/post", formData, options);

                // Defensive check on response data
                if (response?.data?.status === 'success' || response?.status === 200) {
                    if (isEditing) {
                        window.dispatchEvent(new CustomEvent('post-updated', {
                            detail: { postNo: editPostNo, postMessage: id('postMessage').value }
                        }));

                        closePostModal();
                        formExtra.reset();

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Post updated successfully',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        return;
                    }
                    const newPostData = (response?.data?.message && typeof response.data.message === 'object')
                        ? response.data.message
                        : null;

                    // Close the modal first so the feed underneath is immediately
                    // interactive, then hand the new post to the Alpine feed.
                    closePostModal();
                    window.dispatchEvent(new CustomEvent('post-created', { detail: newPostData }));

                    formExtra.reset();
                    clearSelectedPostFiles();
                    const closePreview = document.getElementById('closeImagePreview');
                    if (closePreview) closePreview.click();

                    // Reset composer builders after a successful submission
                    if (typeof window.__resetPollBuilder === 'function') {
                        window.__resetPollBuilder();
                    }
                    if (typeof window.__resetThenNow === 'function') {
                        window.__resetThenNow();
                    }
                    if (typeof window.__resetTagBuilder === 'function') {
                        window.__resetTagBuilder();
                    }
                    if (typeof window.__resetGifPicker === 'function') {
                        window.__resetGifPicker();
                    }
                    if (typeof window.__resetVideoBuilder === 'function') {
                        window.__resetVideoBuilder();
                    }
                    const addOptBtn = document.getElementById('addPollOptionBtn');
                    if (addOptBtn) addOptBtn.style.display = '';

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Post published successfully',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    throw new Error(response?.data?.message || 'Failed to publish post');
                }
            } catch (error) {
                console.error("An error occurred:", error);
                const rawMsg = error?.response?.data?.message || error.message || 'There was an error processing your request. Please try again.';
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: typeof rawMsg === 'string' ? rawMsg.replace(/\n/g, '<br>') : rawMsg,
                });
            } finally {
                if (window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.stop(submitBtn);
                } else {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            }
        } 
        // add/delete to/from the notificatn bar 
        else if (elementId && elementId.includes('deleteNotification')) {
            deleteNotification(elementId);
        } 
        // take you to the request card for approval or denial
        else if (e.target.classList.contains('linkRequestCard')) {
            const friendRequestSection = id(`${e.target.getAttribute('data-id')}_linkRequestCard`);
            if (friendRequestSection) {
                friendRequestSection.scrollIntoView({ behavior: "smooth" });
            }
        }
    });
} catch (e) {
    showError(e);
}





