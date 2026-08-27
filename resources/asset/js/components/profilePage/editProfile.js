"use strict";
import axios from 'axios';
import Swal from 'sweetalert2';

const FORM_ID = 'editProfileFormModal';
const BTN_ID = 'editProfileBtnModal';
const NOTIFICATION_ID = `${FORM_ID}_notification`;

const el = (elId) => document.getElementById(elId);

/**
 * Paint the inline notification bar inside the modal.
 * @param {'success'|'danger'} type
 * @param {string} message
 */
const setNotification = (type, message) => {
    const box = el(NOTIFICATION_ID);
    if (!box) return;
    box.className = `notification alert ${type === 'success' ? 'alert-success bg-success' : 'alert-danger bg-danger'} text-white`;
    box.innerHTML = message;
    box.style.display = 'block';
    const body = box.closest('.modal-body');
    if (body) body.scrollTop = 0; // bring the (scrollable) modal body back to the top
};

const clearNotification = () => {
    const box = el(NOTIFICATION_ID);
    if (!box) return;
    box.style.display = 'none';
    box.innerHTML = '';
    box.className = '';
};

const setLoader = (show) => {
    const loader = el('setLoader');
    if (!loader) return;
    loader.innerHTML = show
        ? `<div class="spinner-border spinner-border-sm text-success" role="status"><span class="visually-hidden">Saving…</span></div> <span class="text-white-50 small">Saving your changes…</span>`
        : '';
    loader.style.display = show ? 'block' : 'none';
};

let submitting = false;

async function handleSave(btn) {
    if (submitting) return;

    const form = el(FORM_ID);
    if (!form) return;

    submitting = true;
    const originalBtnText = btn ? btn.innerHTML : '';

    clearNotification();
    setLoader(true);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving…`;
    }

    const formData = new FormData(form);
    formData.delete('submit');

    try {
        const response = await axios.post('/member/profilePage/editProfile', formData, {
            withCredentials: true,
            xsrfCookieName: 'XSRF-TOKEN',
            xsrfHeaderName: 'X-XSRF-TOKEN',
        });

        const message = response?.data?.message || 'Your profile has been updated.';
        setNotification('success', message);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Profile updated successfully',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });

        // Reload so the rest of the page picks up the new values.
        setTimeout(() => window.location.assign('/profilePage'), 2500);
    } catch (error) {
        console.error('Edit Profile Submit Error:', error);

        const raw = error?.response?.data?.message
            || error?.response?.data?.error
            || error?.message
            || 'Failed to update profile. Please try again.';
        const message = Array.isArray(raw) ? raw : [raw];

        setNotification('danger', message.join('<br>'));

        // Toast (not a blocking modal) so the inline notification stays uncovered.
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Update failed',
            text: message.join(' '),
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });

        submitting = false;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalBtnText;
        }
        setLoader(false);
    }
}

// Delegated: the "Create Post"/profile chunk can execute before the modal markup
// is parsed, so binding straight to #editProfileBtnModal at load time is racy.
document.addEventListener('click', (e) => {
    const target = e.target instanceof Element ? e.target.closest(`#${BTN_ID}`) : null;
    if (!target) return;
    e.preventDefault();
    handleSave(target);
});
