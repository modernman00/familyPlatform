@extends('forms.base')

@section('title', 'Security Verification')
@section('data-page-id', 'code')
@section('meta_description', 'Enter the 6-digit verification code sent to your email to verify your identity.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<style nonce="{{ $nonce }}">
    .otp-box {
        width: 54px;
        min-width: 42px;
        height: 60px;
        padding: 0;
        margin: 0;
        text-align: center;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 60px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-family: 'Outfit', 'Plus Jakarta Sans', system-ui, sans-serif;
        text-transform: uppercase;
        caret-color: var(--brand-primary);
        transition: all 0.18s ease;
    }

    .otp-box:focus {
        border-color: var(--brand-primary) !important;
        box-shadow: 0 0 0 4px var(--brand-accent-glow) !important;
        outline: none;
    }

    @media (max-width: 420px) {
        .otp-box {
            width: 44px;
            min-width: 38px;
            height: 54px;
            font-size: 1.35rem;
            line-height: 54px;
            border-radius: 10px;
        }
    }
</style>

<div x-data="otpComponent()" class="w-100 text-center text-lg-start">
    <!-- Icon Badge -->
    <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
         style="width: 56px; height: 56px; border-radius: 16px; background: rgba(163, 22, 33, 0.2); color: #f87171; border: 1px solid rgba(163, 22, 33, 0.35);">
        <i class="fas fa-shield-alt fs-4"></i>
    </div>

    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-white brand-font">Security Verification</h2>
        <p class="text-secondary mb-0" style="font-size: 0.95rem; color: #94a3b8 !important;">
            Please enter the 6-digit code sent to your email to verify your identity.
        </p>
    </div>

    @php
        $isForgotFlow = (!empty($_SESSION['auth']['flow']) && $_SESSION['auth']['flow'] === 'forgot_password' && empty($_SESSION['auth']['pendingUser']));
        $targetRedirect = $isForgotFlow ? '/changePW' : '/profilePage';
    @endphp

    <!-- Error container -->
    <div class="alert alert-danger noDisplay text-white py-2 px-3 small mb-3" 
         id="code_notification" 
         role="alert" 
         aria-live="assertive"
         style="display: none; border-radius: 10px; background-color: #dc2626; border: none;">
        <p id="error" class="mb-0"></p>
    </div>

    <!-- 2FA Form -->
    <form id="code" class="styleform_form mt-3" autocomplete="off" data-redirect="{{ $targetRedirect }}">
        @include('csrf')
        <input type="hidden" name="token" id="token" value="{{ $_SESSION['token'] ?? '' }}">
        <input type="hidden" name="code" id="codeValue" x-model="combinedCode">

        <!-- 6-Digit OTP Container -->
        <div class="d-flex justify-content-between gap-2 mb-4" id="otp-container" role="group" aria-label="6-digit verification code">
            <template x-for="(digit, idx) in digits" :key="idx">
                <input type="text"
                       class="form-control otp-box otp-input"
                       :id="'otp' + (idx + 1)"
                       :maxlength="idx === 0 ? 6 : 1"
                       inputmode="numeric"
                       pattern="[a-zA-Z0-9]*"
                       :autocomplete="idx === 0 ? 'one-time-code' : 'off'"
                       :aria-label="'Digit ' + (idx + 1) + ' of 6'"
                       x-model="digits[idx]"
                       @input="onInput($event, idx)"
                       @keydown="onKeyDown($event, idx)"
                       @paste="onPaste($event)"
                       required>
            </template>
        </div>

        <!-- Submit Button -->
        <button type="button" 
                id="button" 
                class="btn btn-primary w-100 py-3 fw-bold mb-3 rounded-3 d-flex justify-content-center align-items-center"
                data-spin="true">
            <span>Verify &amp; Continue</span> 
            <i class="fas fa-arrow-right ms-2"></i>
        </button>

        <!-- Paste from clipboard button -->
        <div class="text-center mb-3">
            <button type="button" 
                    class="btn btn-outline-social btn-sm px-4 py-2 w-auto d-inline-flex" 
                    id="pasteBtn"
                    @click="pasteFromClipboard()">
                <i class="fas fa-paste me-2"></i> Paste Code from Email
            </button>
        </div>

        <!-- Resend code section -->
        <div class="text-center mt-4">
            <p class="text-secondary small mb-1" style="color: #94a3b8 !important;">Didn't receive the code?</p>
            <template x-if="cooldown === 0">
                <a href="javascript:void(0)" 
                   id="resendBtn" 
                   class="text-brand text-brand-hover fw-bold text-decoration-none small"
                   @click="triggerResend()">
                    Resend Verification Code
                </a>
            </template>
            <template x-if="cooldown > 0">
                <span class="text-secondary small fw-medium" x-text="'Resend available in ' + cooldown + 's'"></span>
            </template>
        </div>

        <div class="text-center mt-3 pt-2 border-top border-secondary border-opacity-10">
            <a href="/login" class="text-secondary small text-decoration-none hover-white">
                <i class="fas fa-arrow-left me-1"></i> Return to Sign In
            </a>
        </div>
    </form>
</div>

<script nonce="{{ $nonce }}">
function otpComponent() {
    return {
        digits: ['', '', '', '', '', ''],
        cooldown: 0,
        timer: null,

        get combinedCode() {
            return this.digits.join('').toUpperCase();
        },

        init() {
            this.$nextTick(() => {
                const first = document.getElementById('otp1');
                if (first) first.focus();
            });
        },

        normalize(val) {
            if (!val) return '';
            return val
                .replace(/[٠-٩]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0x0660 + 48))
                .replace(/[۰-۹]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0x06F0 + 48))
                .replace(/[^a-zA-Z0-9]/g, '')
                .toUpperCase();
        },

        onInput(e, idx) {
            const clean = this.normalize(e.target.value);
            if (clean.length > 1) {
                this.fillFromCode(clean);
                return;
            }
            this.digits[idx] = clean ? clean.slice(-1) : '';
            this.syncHidden();

            if (clean && idx < 5) {
                const next = document.getElementById('otp' + (idx + 2));
                if (next) next.focus();
            }

            if (this.combinedCode.length === 6) {
                this.autoSubmit();
            }
        },

        onKeyDown(e, idx) {
            if (e.key === 'Backspace' && !this.digits[idx] && idx > 0) {
                const prev = document.getElementById('otp' + idx);
                if (prev) {
                    prev.focus();
                    this.digits[idx - 1] = '';
                    this.syncHidden();
                }
            }
        },

        onPaste(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            this.fillFromCode(text);
        },

        async pasteFromClipboard() {
            try {
                if (navigator.clipboard && navigator.clipboard.readText) {
                    const text = await navigator.clipboard.readText();
                    this.fillFromCode(text);
                } else {
                    document.getElementById('otp1')?.focus();
                }
            } catch (err) {
                document.getElementById('otp1')?.focus();
            }
        },

        fillFromCode(str) {
            const clean = this.normalize(str).slice(0, 6);
            if (!clean) return;
            for (let i = 0; i < 6; i++) {
                this.digits[i] = clean[i] || '';
            }
            this.syncHidden();
            const focusIdx = Math.min(clean.length, 6);
            const target = document.getElementById('otp' + focusIdx);
            if (target) target.focus();

            if (clean.length === 6) {
                this.autoSubmit();
            }
        },

        syncHidden() {
            const hidden = document.getElementById('codeValue');
            if (hidden) hidden.value = this.combinedCode;
        },

        autoSubmit() {
            setTimeout(() => {
                const btn = document.getElementById('button');
                if (btn) btn.click();
            }, 100);
        },

        triggerResend() {
            if (this.cooldown > 0) return;
            this.cooldown = 60;
            this.timer = setInterval(() => {
                if (this.cooldown <= 1) {
                    clearInterval(this.timer);
                    this.cooldown = 0;
                } else {
                    this.cooldown--;
                }
            }, 1000);

            const csrfToken = document.getElementById('token')?.value || '';
            fetch('/login/code/resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ token: csrfToken })
            }).then(r => r.json()).then(data => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Code Sent',
                        text: 'A new 6-digit verification code has been dispatched to your email.',
                        timer: 3500,
                        showConfirmButton: false
                    });
                }
            }).catch(() => {});
        }
    };
}
</script>

@endsection
