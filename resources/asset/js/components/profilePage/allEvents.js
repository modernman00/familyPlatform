"use strict";
import { id, log, msgException, deleteNotification } from "../global"
import axios from "axios"
import Swal from 'sweetalert2';
import { getSelectedPostFiles, clearSelectedPostFiles } from "../fileUploadPreview";

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

                        const closeBtn = document.querySelector('#postModal .btn-close');
                        if (closeBtn) closeBtn.click();
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
                    window.dispatchEvent(new CustomEvent('post-created', { detail: newPostData }));

                    const closeBtn = document.querySelector('#postModal .btn-close');
                    if (closeBtn) closeBtn.click();
                    formExtra.reset();
                    clearSelectedPostFiles();
                    const closePreview = document.getElementById('closeImagePreview');
                    if (closePreview) closePreview.click();
                    
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





