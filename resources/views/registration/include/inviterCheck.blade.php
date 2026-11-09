{{-- Inline "Who invited you?" check for the register v2 page.
     Same Alpine state, field names and IDs as
     components/auth/family-code-verification.blade.php (the old pop-up), but
     shown in the page under the family code instead of as a modal.
     State lives in components/auth/family-code-script.blade.php. --}}
<div class="fpr-inviter" id="inviter-check" x-show="showInviterModal && !codeVerified" x-transition x-cloak role="group" aria-labelledby="fprInvTitle">
    <div class="fpr-inviter-head">
        <span class="fpr-ic" aria-hidden="true"><i class="fas fa-user-check"></i></span>
        <div>
            <p class="fpr-inviter-title" id="fprInvTitle">{{ __('reg.inv_title', 'Who invited you?') }}</p>
            <p class="fpr-inviter-text">{{ __('reg.inv_text', 'This family is already on FamilyPlatform. Tell us who invited you and we\'ll ask them to approve you.') }}</p>
        </div>
    </div>

    <div class="fpr-row-2">
        <div class="fpr-field">
            <label class="fpr-label" for="inviter_first_name">{{ __('reg.inv_first', 'Their first name') }}</label>
            <input class="fpr-input" type="text" id="inviter_first_name" name="inviter_first_name" x-model="inviterFirstName" placeholder="e.g. Wale" autocomplete="off" :required="showInviterModal && !codeVerified">
        </div>
        <div class="fpr-field">
            <label class="fpr-label" for="inviter_last_name">{{ __('reg.inv_last', 'Their last name') }}</label>
            <input class="fpr-input" type="text" id="inviter_last_name" name="inviter_last_name" x-model="inviterLastName" placeholder="e.g. Olaogun" autocomplete="off" :required="showInviterModal && !codeVerified">
        </div>
    </div>

    <div class="fpr-field">
        <label class="fpr-label" for="inviter_contact">{{ __('reg.inv_contact', 'Their email or mobile') }}</label>
        <input class="fpr-input" type="text" id="inviter_contact" name="inviter_email_or_mobile" x-model="inviterContact" autocomplete="off" aria-describedby="fprInvContactHint" :required="showInviterModal && !codeVerified">
        <p class="fpr-hint" id="fprInvContactHint">{{ __('reg.inv_contact_hint', 'With or without the country code, e.g. 07900123456 or +447900123456.') }}</p>
    </div>

    <button type="button" class="fpr-btn fpr-btn--primary" @click="verifyInviter()" :disabled="verifying || !inviterFirstName || !inviterLastName || !inviterContact">
        <span x-show="!verifying">{{ __('reg.inv_verify', 'Check and continue') }}</span>
        <span x-show="verifying" x-cloak><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> {{ __('reg.inv_verifying', 'Checking…') }}</span>
    </button>

    <button type="button" class="fpr-btn fpr-btn--ghost" @click="requestAdminApproval()" style="min-height:44px;font-size:0.9rem;">
        {{ __('reg.inv_admin', 'Don\'t know their details? Ask the family admin instead') }}
    </button>
</div>

<div class="fpr-verified" id="inviter-verified" x-show="codeExists && codeVerified" x-transition x-cloak role="status">
    <span class="fpr-ic" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
    <div>
        <strong>{{ __('reg.inv_verified', 'Invitation confirmed.') }}</strong>
        <span class="d-block">{{ __('reg.inv_verified_text', 'We\'ll ask them to approve you. You can finish signing up now.') }}</span>
    </div>
</div>
