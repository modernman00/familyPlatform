{{-- Single source of truth for the registration modal layer stack (Generate
     Family Code + Inviter Verification) and any SweetAlert2 dialog raised from
     them. Previously duplicated between register.blade.php and
     family-code-verification.blade.php, where the unscoped copies fought. --}}
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal.is-active {
        display: flex;
    }

    .modal-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 41, 130, 0.4);
        backdrop-filter: blur(8px);
    }

    .modal-content {
        position: relative;
        width: 100%;
        max-width: 500px;
        z-index: 1060;
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

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.3);
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 1070;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(0, 0, 0, 0.5);
    }

    #inviter-verification-modal.modal {
        z-index: 9999;
    }

    /* Beats the inline `display: none` the markup ships with, so the modal
       still opens if Alpine has not yet cleared it. */
    #inviter-verification-modal.modal.is-active,
    #inviter-verification-modal[style*="display: flex"],
    #inviter-verification-modal[style*="display: block"] {
        display: flex !important;
    }

    #inviter-verification-modal .modal-background {
        background-color: rgba(0, 41, 130, 0.5);
    }

    #inviter-verification-modal .modal-content {
        z-index: 10000;
    }

    /* SweetAlert2 ships z-index 1060 on .swal2-container, so every validation
       dialog rendered while the inviter modal is open lands *underneath* its
       blurred backdrop — the alert fires but the user never sees it. The
       library injects its own stylesheet at runtime, after this block, so
       !important is required to win. */
    .swal2-container {
        z-index: 11000 !important;
    }
</style>
