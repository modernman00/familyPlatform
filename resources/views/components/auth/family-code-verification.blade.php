<!-- Inviter Verification Modal -->
<div id="inviter-verification-modal" class="modal fade" :class="showInviterModal ? 'show d-block is-active' : ''" x-show="showInviterModal" x-transition style="background-color: rgba(0,0,0,0.5);" x-cloak>
    <div class="modal-background" @click="showInviterModal = false"></div>
    <div class="modal-content" style="background: white; border-radius: 12px; padding: 2rem; max-width: 500px; z-index: 1060; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <button class="modal-close is-large" type="button" @click="showInviterModal = false" aria-label="close"></button>

        <div class="text-center mb-4">
            <h3 class="fw-bold" style="font-size: 1.5rem; color: #00bfa5;">
                <i class="bi bi-shield-check" style="color: #28a745;"></i> Verify Your Invitation
            </h3>
            <p style="font-size: 1rem; font-weight: 600; color: #1e293b; margin-top: 0.5rem; margin-bottom: 0;">
                This family code is already registered.
            </p>
            <p style="font-size: 0.9rem; color: #475569; margin-top: 0.35rem;">
                Tell us who invited you so we can notify them to approve your request.
            </p>
        </div>

        <!-- Section heading -->
        <p class="fw-bold mb-3" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Who invited you?</p>

        <!-- Inviter First Name -->
        <div class="mb-3">
            <label for="inviter_first_name" class="form-label fw-semibold">Their First Name</label>
            <input
                type="text"
                id="inviter_first_name"
                name="inviter_first_name"
                x-model="inviterFirstName"
                class="form-control"
                placeholder="e.g., Wale"
                required
            >
        </div>

        <!-- Inviter Last Name -->
        <div class="mb-3">
            <label for="inviter_last_name" class="form-label fw-semibold">Their Last Name</label>
            <input
                type="text"
                id="inviter_last_name"
                name="inviter_last_name"
                x-model="inviterLastName"
                class="form-control"
                placeholder="e.g., Olaogun"
                required
            >
        </div>

        <!-- Inviter Email or Mobile -->
        <div class="mb-4">
            <label for="inviter_contact" class="form-label fw-semibold">Their Email or Phone Number</label>
            <input
                type="text"
                id="inviter_contact"
                name="inviter_email_or_mobile"
                x-model="inviterContact"
                class="form-control"
                placeholder="Their email address or phone number"
                required
            >
            <div class="form-text text-muted" style="font-size: 0.8rem;">
                <i class="bi bi-info-circle me-1"></i> Phone number can be entered with or without country code (e.g., 07900123456 or +447900123456).
            </div>
        </div>

        <div class="alert alert-info small mb-4" role="alert">
            <i class="bi bi-bell me-1"></i> They will receive an <strong>email and in-app notification</strong> to approve your request. You'll get access once they confirm.
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
                <i class="bi bi-check-circle me-2"></i> Verify &amp; Continue
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
