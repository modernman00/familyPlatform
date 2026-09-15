@extends('layouts.landing_layout')
@section('title', 'Admin — Set Up 2-Factor Authentication')
@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- Header card --}}
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-dark text-white text-center py-4">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 44px;" class="mb-2">
                    <h4 class="font-weight-light my-1">Google Authenticator Setup</h4>
                    <p class="small text-muted mb-0">Secure your admin account with 2-Factor Authentication</p>
                </div>

                <div class="card-body p-4">

                    {{-- Step-by-step instructions --}}
                    <div class="alert alert-info border-0 rounded mb-4" style="background: #e8f4fd;">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-list-ol mr-1"></i> Setup Steps
                        </h6>
                        <ol class="mb-0 pl-3 small">
                            <li class="mb-1">Install <strong>Google Authenticator</strong> on your phone (iOS / Android).</li>
                            <li class="mb-1">Open the app → tap <strong>+</strong> → choose <strong>"Scan a QR code"</strong>.</li>
                            <li class="mb-1">Point your camera at the QR code below.</li>
                            <li class="mb-1">A 6-digit code will appear in the app — enter it below to confirm.</li>
                        </ol>
                    </div>

                    {{-- QR Code (rendered via JS using qrcode.js — no PHP extension needed) --}}
                    <div class="text-center mb-4">
                        <p class="font-weight-bold text-dark mb-2">
                            <i class="fas fa-qrcode mr-1"></i> Scan with Google Authenticator
                        </p>
                        <div id="qrcode" class="d-inline-block p-3 border rounded bg-white shadow-sm"></div>
                        <p class="small text-muted mt-2">Account: <strong>{{ $account }}</strong></p>
                    </div>

                    {{-- Manual entry fallback --}}
                    <div class="mb-4">
                        <p class="small text-muted text-center mb-1">
                            <i class="fas fa-keyboard mr-1"></i>
                            Can't scan? Enter this key manually in the app:
                        </p>
                        <div class="input-group">
                            <input type="text"
                                   id="manualSecret"
                                   class="form-control text-center font-monospace font-weight-bold"
                                   value="{{ $secret }}"
                                   readonly
                                   style="letter-spacing: 3px; font-size: 1.1rem;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copySecret()" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <p class="small text-muted text-center mt-1">
                            In the app: tap <strong>+</strong> → <strong>"Enter a setup key"</strong> → paste the key above.
                        </p>
                    </div>

                    <hr>

                    {{-- Verification form --}}
                    <form id="totpVerifyForm" method="POST" action="/admin/setup-2fa">
                        <input type="hidden" name="token" id="csrf_token" value="{{ $_SESSION['token'] ?? '' }}">
                        <p class="font-weight-bold text-dark text-center mb-3">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Enter the 6-digit code from the app to activate 2-FA
                        </p>

                        <div id="setup2fa_notification"></div>

                        <div class="form-group">
                            <input type="text"
                                   id="totp_code"
                                   name="totp_code"
                                   class="form-control text-center"
                                   placeholder="000 000"
                                   maxlength="6"
                                   autocomplete="one-time-code"
                                   inputmode="numeric"
                                   pattern="[0-9]{6}"
                                   style="font-size: 2rem; letter-spacing: 0.5rem; font-weight: bold;"
                                   required>
                        </div>

                        <div class="form-group mt-3">
                            <button type="button"
                                    class="btn btn-success btn-lg btn-block"
                                    onclick="verifyAndEnable()">
                                <i class="fas fa-check-circle mr-1"></i>
                                Verify & Enable 2-FA
                            </button>
                        </div>
                    </form>

                    {{-- Disable option (only shown if already enrolled — controller sets flag) --}}
                    <div class="text-center mt-3">
                        <a href="/admin/dashboard" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                    </div>

                </div>{{-- /card-body --}}
            </div>{{-- /card --}}

        </div>
    </div>
</div>

{{-- QRCode.js from CDN (pure JS — no PHP ext needed) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    // Render QR code
    const otpauthUri = "{{ $qrUri }}";
    new QRCode(document.getElementById('qrcode'), {
        text: otpauthUri,
        width: 220,
        height: 220,
        colorDark: '#1a1a2e',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });

    // Copy secret to clipboard
    function copySecret() {
        const el = document.getElementById('manualSecret');
        el.select();
        navigator.clipboard.writeText(el.value).then(() => {
            showToast('Secret key copied!');
        });
    }

    function showToast(msg) {
        const t = document.createElement('div');
        t.className = 'alert alert-success py-1 px-3 text-center small position-fixed';
        t.style.cssText = 'bottom:20px;right:20px;z-index:9999;border-radius:8px;';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2500);
    }

    // Live 6-digit formatting (auto-submit when 6 digits entered)
    const codeInput = document.getElementById('totp_code');
    codeInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    // AJAX verify + enable
    function verifyAndEnable() {
        const code = codeInput.value.trim();
        const notif = document.getElementById('setup2fa_notification');

        if (code.length !== 6) {
            notif.innerHTML = '<div class="alert alert-warning">Please enter a 6-digit code from the app.</div>';
            return;
        }

        const fd = new FormData();
        fd.append('totp_code', code);
        fd.append('token', document.getElementById('csrf_token').value || '');

        fetch('/admin/setup-2fa', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (data.status >= 200 && data.status < 300) {
                notif.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>' + (data.message || '2-FA Enabled!') + '</div>';
                codeInput.disabled = true;
                setTimeout(() => window.location.href = '/admin/dashboard', 2000);
            } else {
                notif.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i>' + (data.message || 'Invalid code. Try again.') + '</div>';
                codeInput.value = '';
                codeInput.focus();
            }
        })
        .catch(() => {
            notif.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
        });
    }
</script>

@endsection
