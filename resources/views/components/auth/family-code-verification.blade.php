<!-- Inviter Verification Modal -->
<div id="inviter-verification-modal" class="modal" :class="{ 'is-active': codeExists }" x-show="codeExists" style="display: none;" x-cloak>
    <div class="modal-background" @click="codeExists = false; codeVerified = false;"></div>
    <div class="modal-content" style="background: white; border-radius: 12px; padding: 2rem; max-width: 500px; z-index: 1060; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <button class="modal-close is-large" type="button" @click="codeExists = false; codeVerified = false;" aria-label="close"></button>

        <div class="text-center mb-4">
            <h3 class="fw-bold" style="font-size: 1.5rem; color: #00bfa5;">
                <i class="bi bi-shield-check" style="color: #28a745;"></i> Verify Your Invitation
            </h3>
            <p class="text-muted small">This family code is already registered. Please provide the details of the family member who invited you.</p>
        </div>

        <!-- Inviter First Name -->
        <div class="mb-3">
            <label for="inviter_first_name" class="form-label fw-bold">Inviter's First Name</label>
            <input
                type="text"
                id="inviter_first_name"
                name="inviter_first_name"
                x-model="inviterFirstName"
                class="form-control"
                placeholder="e.g., John"
                required
            >
        </div>

        <!-- Inviter Last Name -->
        <div class="mb-3">
            <label for="inviter_last_name" class="form-label fw-bold">Inviter's Last Name</label>
            <input
                type="text"
                id="inviter_last_name"
                name="inviter_last_name"
                x-model="inviterLastName"
                class="form-control"
                placeholder="e.g., Doe"
                required
            >
        </div>

        <!-- Inviter Email or Mobile -->
        <div class="mb-4">
            <label for="inviter_contact" class="form-label fw-bold">Inviter's Email or Mobile</label>
            <input
                type="text"
                id="inviter_contact"
                name="inviter_email_or_mobile"
                x-model="inviterContact"
                class="form-control"
                placeholder="e.g., john@example.com or +1234567890"
                required
            >
        </div>

        <div class="alert alert-info small mb-4" role="alert">
            <i class="bi bi-info-circle me-1"></i> Your family member will receive an approval request to confirm you in their family network.
        </div>

        <button
            type="button"
            @click="verifyInviter()"
            :disabled="verifying || !inviterFirstName || !inviterLastName || !inviterContact"
            class="btn btn-primary w-100"
            style="padding: 0.75rem; font-weight: 600;"
            x-show="!codeVerified"
        >
            <span x-show="!verifying">
                <i class="bi bi-check-circle me-2"></i> Verify & Continue
            </span>
            <span x-show="verifying">
                <i class="bi bi-hourglass-split spinner-border spinner-border-sm me-2"></i> Verifying...
            </span>
        </button>

        <!-- Verified Badge (shown after inviter is verified) -->
        <div x-show="codeVerified" x-transition class="alert alert-success mt-3">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Invitation Verified!</strong>
            <p class="mb-0 mt-2 small">
                A notification will be sent to <strong x-text="inviterFirstName + ' ' + inviterLastName"></strong>.
                Once they approve, you'll be connected to the family network.
            </p>
        </div>
    </div>
</div>

<style>
#inviter-verification-modal.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

#inviter-verification-modal.modal.is-active,
#inviter-verification-modal[style*="display: flex"],
#inviter-verification-modal[style*="display: block"] {
    display: flex !important;
}

.modal-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 41, 130, 0.5);
    backdrop-filter: blur(8px);
}

.modal-content {
    position: relative;
    z-index: 10000;
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
