@extends('forms.base')

@section('title', 'Sign In')
@section('data-page-id', 'login')
@section('meta_description', 'Sign in to your private FamilyPlatform account.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<div x-data="loginComponent()" class="w-100">
    <!-- Language Selector Pill -->
    <div class="d-flex justify-content-end mb-3">
        @php
            $activeLocale = \App\classes\I18n::getLocale();
            $supportedLocales = \App\classes\I18n::getSupportedLocales();
        @endphp
        <div class="d-flex align-items-center gap-1 p-1 rounded-pill" style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1);">
            @foreach ($supportedLocales as $locKey => $locMeta)
                <a href="/locale/{{ $locKey }}" class="btn btn-sm py-0.5 px-2 rounded-pill {{ $locKey === $activeLocale ? 'btn-primary fw-bold text-white' : 'text-secondary' }}" style="font-size: 0.75rem; text-decoration: none; min-height: 28px; min-width: 28px;">
                    {{ $locMeta['flag'] }} {{ $locMeta['native'] }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Header Titles -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-white brand-font">{{ __('auth.sign_in_title', 'Welcome back') }}</h2>
        <p class="text-secondary mb-0" style="font-size: 0.95rem; color: #94a3b8 !important;">
            {{ __('auth.welcome_back_subtitle', 'Sign in to your private family sanctuary') }}
        </p>
    </div>

    {{-- Secure session flash error / message --}}
    @if(!empty($_SESSION['flash_error']))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.4); color: #fbbf24; border-radius: 12px;">
        <span>⚠️ {{ $_SESSION['flash_error'] }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" style="filter: invert(1);"></button>
    </div>
    @php unset($_SESSION['flash_error']); @endphp
    @endif

    <!-- Google OAuth Button -->
    <a href="/auth/google" class="btn btn-outline-social mb-2">
        <svg class="me-2 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
        </svg>
        <span>{{ __('auth.continue_google', 'Continue with Google') }}</span>
    </a>

    <!-- Facebook OAuth Button -->
    <a href="/auth/facebook" class="btn btn-outline-social mb-3" style="background-color: rgba(24, 119, 242, 0.12) !important; border-color: rgba(24, 119, 242, 0.35) !important;">
        <svg class="me-2 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>{{ __('auth.continue_facebook', 'Continue with Facebook') }}</span>
    </a>

    <!-- Biometric WebAuthn Button -->
    <button type="button" 
            id="btn-passkey-login" 
            @click="handlePasskeyLogin()" 
            class="btn btn-outline-primary-brand mb-2 w-100 py-2 fw-bold">
        <i class="fas fa-fingerprint fs-5"></i>
        <span x-text="passkeyStatus || 'Use Face ID / Touch ID'">Use Face ID / Touch ID</span>
    </button>

    <!-- Child / Minor Sign-In Toggle -->
    <button type="button" 
            id="btn-child-login-toggle" 
            @click="childModalOpen = true" 
            class="btn btn-outline-social mb-3 w-100 py-2"
            style="background: rgba(255, 255, 255, 0.04) !important;">
        <span class="fs-5">🧒</span>
        <span>Child / Minor Sign-In (Family PIN)</span>
    </button>

    <!-- Divider -->
    <div class="divider-text">
        <span>or continue with email</span>
    </div>

    <!-- Error container for shared library / AJAX -->
    <div id="login_notification" class="alert alert-danger py-2 px-3 small mb-3 noDisplay" style="display: none; border-radius: 10px;">
        <p id="showError" class="mb-0 text-white"></p>
    </div>

    <!-- Email Sign-In Form -->
    <form id="login" class="styleform_form mt-2" autocomplete="on">
        @include('csrf')
        <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

        <!-- Email Address -->
        <div class="mb-4">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" 
                       name="email" 
                       id="email" 
                       class="form-control" 
                       placeholder="name@family.com" 
                       required 
                       autocomplete="username"
                       x-model="email">
            </div>
            <p id="email_error" class="text-danger mt-1 small mb-0"></p>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0" for="password">Password</label>
                <a href="/login/forgot?verify=1" class="text-brand text-brand-hover small text-decoration-none">
                    {{ __('auth.forgot_password', 'Forgot password?') }}
                </a>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input :type="showPassword ? 'text' : 'password'" 
                       name="password" 
                       id="password" 
                       class="form-control" 
                       placeholder="Enter your password" 
                       required 
                       autocomplete="current-password"
                       x-model="password">
                <button type="button" 
                        class="password-toggle-btn" 
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <i class="far" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <p id="password_error" class="text-danger mt-1 small mb-0"></p>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="mb-4 form-check d-flex align-items-center gap-2">
            <input type="checkbox" name="rememberMe" class="form-check-input mt-0" id="rememberMe" value="true">
            <label class="form-check-label small" for="rememberMe">{{ __('auth.remember_me', 'Remember me on this device') }}</label>
        </div>

        <!-- Submit Button -->
        <button type="button" 
                id="button" 
                class="btn btn-primary w-100 py-3 fw-bold mb-4 rounded-3 d-flex justify-content-center align-items-center"
                data-spin="true">
            <span>{{ __('auth.sign_in_btn', 'Sign In') }}</span> 
            <i class="fas fa-arrow-right ms-2"></i>
        </button>

        <!-- Create Account Link -->
        <div class="text-center small text-secondary">
            {{ __('auth.no_account_prompt', "Don't have an account?") }} 
            <a href="/register" class="text-brand text-brand-hover fw-bold">{{ __('auth.sign_up_link', 'Create account') }}</a>
        </div>
    </form>

    <!-- Child Sign-In Modal (Alpine Managed with Focus Trap & Smooth Transitions) -->
    <div x-show="childModalOpen" 
         x-cloak 
         x-trap.noscroll="childModalOpen"
         @keydown.escape.window="childModalOpen = false"
         class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-3" 
         style="background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(8px); z-index: 9999;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="w-100 rounded-4 overflow-hidden" 
             style="max-width: 420px; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);"
             @click.outside="childModalOpen = false">
            
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #7a0e16 0%, #a31621 100%); color: #ffffff; padding: 1.5rem; text-align: center;">
                <div style="font-size: 2.2rem; margin-bottom: 0.25rem;">🧒🎈</div>
                <h4 class="fw-bold mb-1 text-white brand-font">Family Child Sign-In</h4>
                <p class="mb-0 small text-white-50">Enter your family network code and PIN</p>
            </div>

            <!-- Modal Form -->
            <form id="childLoginForm" style="padding: 1.5rem;" @submit.prevent="handleChildLogin">
                @include('csrf')
                <div x-show="childError" x-cloak class="alert alert-danger py-2 px-3 small mb-3" style="border-radius: 8px;" x-text="childError"></div>

                <div class="mb-3">
                    <label class="form-label" for="childFamCode">Family Code <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <input type="text" 
                               name="family_code" 
                               id="childFamCode" 
                               class="form-control text-uppercase" 
                               placeholder="e.g. FAM-12345" 
                               required 
                               x-model="childFamCode">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="childUsername">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" 
                               name="username" 
                               id="childUsername" 
                               class="form-control" 
                               placeholder="e.g. sammy_o" 
                               required 
                               x-model="childUsername">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="childPin">Child PIN (4–8 digits) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" 
                               name="pin" 
                               id="childPin" 
                               class="form-control" 
                               placeholder="••••" 
                               maxlength="8" 
                               required 
                               style="letter-spacing: 0.2em;" 
                               x-model="childPin">
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" 
                            @click="childModalOpen = false" 
                            class="btn btn-outline-social py-2 px-3" 
                            style="width: auto; min-width: 80px;">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="childLoginSubmitBtn" 
                            class="btn btn-primary py-2 px-4" 
                            :disabled="childSubmitting"
                            style="width: auto;">
                        <span x-show="!childSubmitting">Sign In to Family</span>
                        <span x-show="childSubmitting" x-cloak>
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Signing in...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="{{ $nonce }}">
function loginComponent() {
    return {
        showPassword: false,
        email: '',
        password: '',
        childModalOpen: false,
        childFamCode: '',
        childUsername: '',
        childPin: '',
        childSubmitting: false,
        childError: '',
        passkeyStatus: '',

        async handleChildLogin() {
            this.childSubmitting = true;
            this.childError = '';

            const form = document.getElementById('childLoginForm');
            const formData = new FormData(form);

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
                    window.location.href = data.redirect || '/member/profilePage';
                } else {
                    this.childError = data.message || 'Invalid family code, username, or PIN.';
                }
            } catch (err) {
                this.childError = 'Connection error. Please try again.';
            } finally {
                this.childSubmitting = false;
            }
        },

        async handlePasskeyLogin() {
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

            const csrfToken = document.querySelector('input[name="token"]')?.value || '';

            try {
                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                    throw new Error('Biometric authentication requires a secure connection (HTTPS).');
                }
                if (!window.PublicKeyCredential) {
                    throw new Error('Passkeys are not supported on this device.');
                }

                this.passkeyStatus = 'Authenticating...';

                // 1. Fetch challenge
                const res = await fetch('/webauthn/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ token: csrfToken, email: this.email.trim() })
                });
                const optionsData = await res.json();

                if (optionsData.status !== 'success' && optionsData.status !== 200) {
                    throw new Error(optionsData.message || 'Failed to initialise biometric challenge.');
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

                // 3. Prompt Biometric Hardware
                const assertion = await navigator.credentials.get({ publicKey });
                if (!assertion) {
                    throw new Error('No credential received.');
                }

                this.passkeyStatus = 'Verifying...';

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
                    this.passkeyStatus = 'Success! Redirecting...';
                    window.location.href = verifyData.data?.redirect || '/profilePage';
                } else {
                    throw new Error(verifyData.message || 'Biometric authentication failed.');
                }
            } catch (err) {
                this.passkeyStatus = '';
                const msg = (err.name === 'NotAllowedError') 
                    ? 'Biometric prompt was cancelled or timed out.' 
                    : (err.message || 'Biometric login failed. Please use your password.');
                if (window.Swal) {
                    Swal.fire({ icon: 'warning', title: 'Biometrics', text: msg });
                } else {
                    alert(msg);
                }
            }
        }
    };
}
</script>

@endsection
