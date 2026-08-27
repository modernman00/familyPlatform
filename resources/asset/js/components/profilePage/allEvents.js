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
        });
    }

    document.addEventListener('click', async (e) => {
        const elementId = e.target.id;

        // SUBMIT THE NEW POST (from modal)
        if (elementId && elementId.includes("submitPost")) {
            e.preventDefault();
            const formExtra = id('formPostMessageModal');
            if (!formExtra) return;

            // editPost() in feedComponent.js stamps this when reopening the modal
            // to edit an existing post instead of creating a new one.
            const editPostNo = document.getElementById('editPostNo')?.value;
            const isEditing = !!editPostNo;

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
                        confirmButtonColor: '#4ade80'
                    });
                    return;
                }

                if (pollOptions.length < 2) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Poll Incomplete',
                        text: 'Please provide at least 2 poll options.',
                        confirmButtonColor: '#4ade80'
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
                const filesToAppend = (selectedFiles && selectedFiles.length > 0)
                    ? selectedFiles
                    : (fileInput && fileInput.files ? Array.from(fileInput.files) : []);

                if (filesToAppend.length > 0) {
                    formData.delete('post_img[]');
                    formData.delete('post_img');
                    filesToAppend.forEach((file) => {
                        formData.append('post_img[]', file, file.name);
                    });
                }
            }

            // Change button to spinner
            const submitBtn = id(elementId);
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${isEditing ? 'Saving...' : 'Posting...'}`;
            submitBtn.disabled = true;

            try {
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

                    // Reset the poll builder after a successful submission
                    if (typeof window.__resetPollBuilder === 'function') {
                        window.__resetPollBuilder();
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
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error?.response?.data?.message || error.message || 'There was an error processing your request. Please try again.',
                });
            } finally {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
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





