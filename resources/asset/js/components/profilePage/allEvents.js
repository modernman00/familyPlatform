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

    document.addEventListener('click', async (e) => {
        const elementId = e.target.id;

        // SUBMIT THE NEW POST (from modal)
        if (elementId && elementId.includes("submitPost")) {
            e.preventDefault();
            const formExtra = id('formPostMessageModal');
            if (!formExtra) return;

            const formData = new FormData(formExtra);
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

            // Change button to spinner
            const submitBtn = id(elementId);
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Posting...';
            submitBtn.disabled = true;

            try {
                const response = await axios.post("/member/profilePage/post", formData, options);

                // Defensive check on response data
                if (response?.data?.status === 'success' || response?.status === 200) {
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





