/**
 * FamilyPlatform — Admin Auth 2FA Form Handler
 * Initialises shared-js-lib admin auth UI for /portal_* routes.
 */
import { initAdminAuthForm } from '@modernman00/shared-js-lib';

document.addEventListener('DOMContentLoaded', () => {
    try {
        initAdminAuthForm({
            appName: 'FamilyPlatform Admin',
            otpInputSelector: '.otp-input',
            submitSelector: '.btn-submit',
        });
    } catch (err) {
        // Non-fatal — form works without JS enhancement
        console.warn('[AdminAuth] shared-js-lib init skipped:', err?.message);
    }
});
