@extends('layouts.landing_layout')

@section('title', 'Login')
@section('data-page-id', 'login')
@section('meta_description', 'Sign in to your FamilyPlatform account.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<style nonce="{{ $nonce }}">
    /* CSS for the new Stitch design */
    body {
        background-color: #F4F5FB !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Override the outer Bootstrap container from forms.base */
    .styleForm {
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    .logo-container {
        display: none !important;
    }

   

    .stitch-auth-container {
        max-width: 440px;
        margin: 3rem auto 5rem;
        background: #ffffff;
        border-radius: 20px;
        padding: 3rem 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        text-align: center;
    }

    .stitch-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1A1A24;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .stitch-subtitle {
        font-size: 0.95rem;
        color: #6B7280;
        margin-bottom: 2rem;
    }

    .stitch-divider {
        display: flex;
        align-items: center;
        margin: 2rem 0;
        color: #6B7280;
        font-size: 0.85rem;
    }

    .stitch-divider::before,
    .stitch-divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #E5E7EB;
    }

    .stitch-divider span {
        padding: 0 12px;
    }

    /* Form Styles Overrides */
    .styleform_form {
        text-align: left;
    }

    .styleform_form .form-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .styleform_form .form-control {
        border-radius: 8px;
        border: 1px solid #D1D5DB;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        box-shadow: none;
        margin-bottom: 1.5rem;
    }

    .styleform_form .form-control::placeholder {
        color: #9CA3AF;
    }

    .styleform_form .form-control:focus {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(184,32,44, 0.15);
    }

    .stitch-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0.5rem 0 2rem;
        font-size: 0.9rem;
    }

    .stitch-checkbox-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6B7280;
        font-weight: 500;
        cursor: pointer;
    }

    .stitch-checkbox {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1px solid #D1D5DB;
        accent-color: var(--brand-primary);
        cursor: pointer;
    }

    .stitch-link {
        color: var(--brand-primary);
        text-decoration: none;
        font-weight: 600;
    }

    .stitch-link:hover {
        text-decoration: underline;
    }

    .stitch-submit {
        width: 100%;
        background: linear-gradient(135deg, var(--brand-primary, #a31621) 0%, var(--brand-primary-dark, #8a1019) 100%);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.6rem 1rem;
        min-height: 44px;
        font-size: 0.92rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(163,22,33, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .stitch-submit:hover {
        background: linear-gradient(135deg, var(--brand-primary-light, #b8202c) 0%, var(--brand-primary, #a31621) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(163,22,33, 0.32);
    }

    .stitch-submit:active {
        transform: scale(0.98);
    }

    .stitch-footer {
        margin-top: 2rem;
        font-size: 0.95rem;
        color: #6B7280;
    }

    /* Hide the original BuildFormBStrap submit button and showPassword checkbox */
    .styleform_form #btn-login {
        display: none !important;
    }

    .styleform_form .form-check:has(#showPassword) {
        display: none !important;
    }

    .stitch-social-btn {
        width: 100%;
        border-radius: 12px;
        padding: 0.55rem 1rem;
        min-height: 44px;
        font-size: 0.9rem;
        font-weight: 550;
        letter-spacing: -0.01em;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 0.75rem;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    .stitch-social-btn:active {
        transform: scale(0.98);
    }

    .stitch-social-btn svg {
        flex-shrink: 0;
    }

    .stitch-social-btn.google {
        background-color: #ffffff;
        color: #374151;
        border: 1px solid #E5E7EB;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .stitch-social-btn.google:hover {
        background-color: #F9FAFB;
        border-color: #D1D5DB;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        color: #111827;
        transform: translateY(-1px);
    }

    .stitch-social-btn.facebook {
        background-color: #1877F2;
        color: #ffffff;
        border: 1px solid #1877F2;
        box-shadow: 0 2px 6px rgba(24, 119, 242, 0.22);
    }
    .stitch-social-btn.facebook:hover {
        background-color: #a31621;
        border-color: #a31621;
        box-shadow: 0 4px 10px rgba(24, 119, 242, 0.32);
        color: #ffffff;
        transform: translateY(-1px);
    }
</style>

<div class="stitch-auth-container">
    <!-- Language Selector Dropdown -->
    <div class="d-flex justify-content-end mb-3">
        @php
            $activeLocale = \App\classes\I18n::getLocale();
            $supportedLocales = \App\classes\I18n::getSupportedLocales();
        @endphp
        <div class="d-flex align-items-center gap-1 p-1 rounded-pill" style="background: rgba(15, 23, 42, 0.06); border: 1px solid rgba(0, 0, 0, 0.08);">
            @foreach ($supportedLocales as $locKey => $locMeta)
                <a href="/locale/{{ $locKey }}" class="btn btn-sm py-0.5 px-2 rounded-pill {{ $locKey === $activeLocale ? 'btn-primary fw-bold text-white' : 'text-secondary' }}" style="font-size: 0.75rem; text-decoration: none;">
                    {{ $locMeta['flag'] }} {{ $locMeta['native'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="text-center mb-4">
        <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-2">
    </div>
    <h1 class="stitch-title">{{ __('auth.sign_in_title', 'Sign in to your account') }}</h1>
    <p class="stitch-subtitle">{{ __('auth.welcome_back_subtitle', 'Welcome back! Please enter your details.') }}</p>

    <!-- Social Auth at the top -->
    <a href="/auth/google" class="stitch-social-btn google">
        <svg width="20" height="20" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
        </svg>
        <span>{{ __('auth.continue_google', 'Continue with Google') }}</span>
    </a>
    <a href="/auth/facebook" class="stitch-social-btn facebook">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="#ffffff">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>{{ __('auth.continue_facebook', 'Continue with Facebook') }}</span>
    </a>

    <div class="stitch-divider">
        <span>{{ __('auth.or', 'OR') }}</span>
    </div>

    <!-- Email Form -->
    <form id="login" class="styleform_form">
    @include('csrf')
        <script nonce="{{ $nonce }}">
            try {
                sessionStorage.removeItem('fromForgot');
                localStorage.removeItem('redirect');
            } catch (e) {}
        </script>
        @php
        $formArray = [
        'login_notification' => 'showError',
        'email' => 'email',
        'password' => 'password',
        'token' => 'token',
        'showPassword' => 'showPassword',
        ];

        $form = new Src\BuildFormBStrap($formArray);
        $form->genForm();
        @endphp

        <div class="stitch-options">
            <label class="stitch-checkbox-group">
                <input type="checkbox" class="stitch-checkbox" id="rememberMe" name="rememberMe" value="true">
                {{ __('auth.remember_me', 'Remember me') }}
            </label>
            <a href="/login/forgot?verify=1" class="stitch-link">{{ __('auth.forgot_password', 'Forgot password?') }}</a>
        </div>

        <button type="button" class="stitch-submit" id="button">
            {{ __('auth.sign_in_btn', 'Sign In') }} <i class="bi bi-arrow-right"></i>
        </button>

        <button type="button" id="btn-passkey-login"
                style="margin-top: 0.75rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #ffffff; color: #374151; border: 1px solid #E5E7EB; border-radius: 12px; min-height: 44px; padding: 0.55rem 1rem; font-size: 0.9rem; font-weight: 550; letter-spacing: -0.01em; cursor: pointer; transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04); touch-action: manipulation; -webkit-tap-highlight-color: transparent;">
            <svg style="width: 20px; height: 20px; color: var(--brand-primary, #7a0e16);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11m0 0a8 8 0 00.99 7.132"></path></svg>
            {{ __('auth.sign_in_biometrics', 'Sign in with Biometrics') }}
        </button>

        <button type="button" id="btn-child-login-toggle"
                onclick="openChildLoginModal()"
                style="margin-top: 0.75rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #f8fafc; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 12px; min-height: 44px; padding: 0.55rem 1rem; font-size: 0.9rem; font-weight: 600; letter-spacing: -0.01em; cursor: pointer; transition: all 0.18s ease; touch-action: manipulation; white-space: nowrap; flex-shrink: 0;">
            <span style="font-size: 1.1rem;">🧒</span>
            <span>Child / Minor Sign-In (Family PIN)</span>
        </button>
    </form>

    <!-- Child Sign-In Modal -->
    <div id="childLoginModal" class="d-none" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 420px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1px solid #e2e8f0;">
            <div style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); color: #ffffff; padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.2rem; margin-bottom: 0.25rem;">🧒🎈</div>
                <h4 style="font-weight: 700; margin: 0; font-size: 1.25rem; color: #ffffff;">Family Child Sign-In</h4>
                <p style="margin: 0.25rem 0 0; font-size: 0.825rem; color: rgba(255, 255, 255, 0.85);">Enter your family network code and PIN</p>
            </div>
            <form id="childLoginForm" style="padding: 1.5rem;" onsubmit="handleChildLogin(event)">
                @include('csrf')
                <div id="childLoginError" class="alert alert-danger d-none py-2 px-3 small mb-3" style="border-radius: 8px;"></div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label small fw-bold" style="color: #334155;">Family Code <span class="text-danger">*</span></label>
                    <input type="text" name="family_code" id="childFamCode" class="form-control" placeholder="e.g. FAM-12345" required style="border-radius: 10px; min-height: 44px; text-transform: uppercase;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label class="form-label small fw-bold" style="color: #334155;">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" id="childUsername" class="form-control" placeholder="e.g. sammy_o" required style="border-radius: 10px; min-height: 44px;">
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label class="form-label small fw-bold" style="color: #334155;">Child PIN (4–8 digits) <span class="text-danger">*</span></label>
                    <input type="password" name="pin" id="childPin" class="form-control" placeholder="Enter PIN" maxlength="8" required style="border-radius: 10px; min-height: 44px; letter-spacing: 0.2em;">
                </div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeChildLoginModal()" style="min-height: 44px; min-width: 44px; border: 1px solid #cbd5e1; background: #f8fafc; border-radius: 10px; padding: 0.5rem 1rem; font-weight: 600; cursor: pointer; color: #475569; white-space: nowrap; flex-shrink: 0;">
                        Cancel
                    </button>
                    <button type="submit" id="childLoginSubmitBtn" style="min-height: 44px; min-width: 44px; border: none; background: var(--brand-primary, #7a0e16); color: #ffffff; border-radius: 10px; padding: 0.5rem 1.25rem; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                        Sign In to Family
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="stitch-footer">
        {{ __('auth.no_account_prompt', "Don't have an account?") }} <a href="/register" class="stitch-link">{{ __('auth.sign_up_link', 'Sign up') }}</a>
    </div>

</div>

<script nonce="{{ $nonce }}">
document.addEventListener("DOMContentLoaded", function() {
    const passkeyBtn = document.getElementById('btn-passkey-login');
    if (passkeyBtn) {
        const base64URLToBuffer = (base64URL) => {
            const base64 = base64URL.replace(/-/g, '+').replace(/_/g, '/');
            const padLength = (4 - (base64.length % 4)) % 4;
            const padded = base64.padEnd(base64.length + padLength, '=');
            const binary = atob(padded);
            const bytes = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                bytes[i] = binary.charCodeAt(i);
            }
            return bytes.buffer;
        };

        const bufferToBase64URL = (buffer) => {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        };

        passkeyBtn.addEventListener('click', async () => {
            const originalHtml = passkeyBtn.innerHTML;
            const emailInput = document.getElementById('email');
            const email = emailInput ? emailInput.value.trim() : '';
            const csrfToken = document.querySelector('input[name="token"]')?.value || '';

            try {
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    throw new Error('Biometric authentication requires a secure connection (HTTPS).');
                }
                if (!window.PublicKeyCredential) {
                    throw new Error('Passkeys are not supported on this browser or device.');
                }

                passkeyBtn.disabled = true;
                passkeyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Authenticating...';

                // 1. Fetch challenge
                const res = await fetch('/webauthn/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ token: csrfToken, email: email })
                });
                const optionsData = await res.json();

                if (optionsData.status !== 'success' && optionsData.status !== 200) {
                    throw new Error(optionsData.message || 'Failed to initialize biometric challenge.');
                }

                const options = optionsData.token || optionsData.data;

                // 2. Format challenge
                const isIp = /^(\d{1,3}\.){3}\d{1,3}$/.test(window.location.hostname);
                const publicKey = {
                    challenge: base64URLToBuffer(options.challenge),
                    userVerification: options.userVerification || 'preferred',
                    timeout: options.timeout || 60000
                };
                if (!isIp && window.location.hostname !== 'localhost') {
                    publicKey.rpId = window.location.hostname;
                }

                if (Array.isArray(options.allowCredentials) && options.allowCredentials.length > 0) {
                    publicKey.allowCredentials = options.allowCredentials.map(c => ({
                        type: 'public-key',
                        id: base64URLToBuffer(c.id)
                    }));
                }

                // 3. Prompt Biometric hardware
                const assertion = await navigator.credentials.get({ publicKey });
                if (!assertion) {
                    throw new Error('No credential received.');
                }

                passkeyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Verifying...';

                // 4. Verify assertion
                const verifyRes = await fetch('/webauthn/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({
                        token: csrfToken,
                        id: assertion.id,
                        rawId: bufferToBase64URL(assertion.rawId),
                        type: assertion.type,
                        response: {
                            clientDataJSON: bufferToBase64URL(assertion.response.clientDataJSON),
                            authenticatorData: bufferToBase64URL(assertion.response.authenticatorData),
                            signature: bufferToBase64URL(assertion.response.signature)
                        }
                    })
                });
                const verifyData = await verifyRes.json();

                if (verifyData.status === 'success' || verifyData.status === 200) {
                    passkeyBtn.innerHTML = '✓ Success! Redirecting...';
                    window.location.href = verifyData.data?.redirect || '/profilePage';
                } else {
                    throw new Error(verifyData.message || 'Biometric authentication failed.');
                }
            } catch (err) {
                console.warn(err);
                passkeyBtn.disabled = false;
                passkeyBtn.innerHTML = originalHtml;
                const msg = (err.name === 'NotAllowedError') 
                    ? 'Biometric prompt was cancelled or timed out.' 
                    : (err.message || 'Biometric login failed. Please use your password.');
                (window.swalError || alert)(msg);
            }
        });
    }
});

function openChildLoginModal() {
    const modal = document.getElementById('childLoginModal');
    if (modal) {
        modal.classList.remove('d-none');
        document.getElementById('childFamCode')?.focus();
    }
}

function closeChildLoginModal() {
    const modal = document.getElementById('childLoginModal');
    if (modal) {
        modal.classList.add('d-none');
        const err = document.getElementById('childLoginError');
        if (err) err.classList.add('d-none');
    }
}

async function handleChildLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('childLoginSubmitBtn');
    const errDiv = document.getElementById('childLoginError');
    if (errDiv) errDiv.classList.add('d-none');

    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';

    const formData = new FormData(document.getElementById('childLoginForm'));

    try {
        const res = await fetch('/login/child', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
            btn.innerHTML = '✓ Signed In!';
            window.location.href = data.redirect || '/member/profilePage';
        } else {
            btn.disabled = false;
            btn.innerHTML = origText;
            if (errDiv) {
                errDiv.textContent = data.message || 'Invalid family code, username, or PIN.';
                errDiv.classList.remove('d-none');
            }
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerHTML = origText;
        if (errDiv) {
            errDiv.textContent = 'Connection error. Please try again.';
            errDiv.classList.remove('d-none');
        }
    }
}
</script>

@endsection