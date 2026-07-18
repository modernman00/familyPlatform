@extends('layouts.landing_layout')

@section('title', 'Login')
@section('data-page-id', 'login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="auth-title h2">Welcome Back</h1>
                    <p class="auth-subtitle">Log in to reconnect with your family network</p>
                </div>

                <form action="" method="" id="login" enctype="multipart/form-data">
                    <div class='form-group'>
                        @php
                            $formArray = [
                                'login_notification' => 'showError',
                                'email' => 'email',
                                'password' => 'password',
                                'checkbox' => 'Remember me',
                                'token' => 'token',
                                'submit' => 'button',
                                'showPassword' => 'showPassword'
                            ];

                            $form = new Src\BuildFormBulma($formArray);
                            $form->genForm();
                        @endphp

                        <div class="mt-4 text-center">
                            <a href="/forgot?verify=1" class="text-decoration-none" style="color: var(--brand-primary); font-weight: 500;">
                                Forgot password?
                            </a>
                        </div>
                        
                        <hr class="my-4" style="opacity: 0.1;">

                        <!-- WebAuthn Container -->
                        <div id="webauthn-container" style="display: none;" class="mb-4">
                            <button type="button" id="btn-webauthn" class="btn btn-outline-primary w-100 py-3" style="font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-fingerprint me-2"></i> Login with FaceID / TouchID
                            </button>
                            <div id="webauthn_error" class="text-danger small text-center mt-2"></div>
                        </div>
                        
                        
                        <div class="text-center">
                            <p class="text-muted small">New to Family Platform? <a href="/register" class="fw-bold text-decoration-none" style="color: var(--brand-primary);">Join now</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Load Shared WebAuthn Client -->
<script type="module">
    import { WebAuthnClient } from '/shared-js-lib/WebAuthnClient.js';
    
    document.addEventListener('DOMContentLoaded', () => {
        if (WebAuthnClient.isSupported()) {
            document.getElementById('webauthn-container').style.display = 'block';
        }

        document.getElementById('btn-webauthn').addEventListener('click', async () => {
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput ? emailInput.value : '';
            const errDiv = document.getElementById('webauthn_error');
            
            if (!email) {
                errDiv.innerHTML = "Enter your email first to find your Passkey.";
                return;
            }
            errDiv.innerHTML = "";
            
            try {
                // 1. Fetch challenge
                const res = await fetch('/webauthn/login/options', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ email: email })
                });
                const options = await res.json();
                
                if (options.status !== 200) {
                    throw new Error(options.message);
                }

                // 2. Prompt FaceID / TouchID
                const assertion = await WebAuthnClient.loginWithDevice(options.data);

                // 3. Verify
                const verifyRes = await fetch('/webauthn/login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(assertion)
                });
                const verifyData = await verifyRes.json();
                
                if (verifyData.status === 200) {
                    window.location.href = '/dashboard';
                } else {
                    throw new Error(verifyData.message);
                }
            } catch (err) {
                // Defensive Degradation
                console.warn(err);
                errDiv.innerHTML = err.message || 'Biometric login failed. Please use your password.';
            }
        });
    });
</script>

@endsection







