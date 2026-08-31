@extends('layouts.landing_layout')

@section('title', 'Login')
@section('data-page-id', 'login')
@section('meta_description', 'Sign in to your FamilyPlatform account.')

@section('content')

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
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
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
        background-color: var(--brand-primary);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    .stitch-submit:hover {
        background-color: #004182;
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
        border-radius: 10px;
        padding: 11px 16px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.85rem;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none;
    }

    .stitch-social-btn svg {
        flex-shrink: 0;
    }

    .stitch-social-btn.google {
        background-color: #ffffff;
        color: #374151;
        border: 1.5px solid #E5E7EB;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .stitch-social-btn.google:hover {
        background-color: #F9FAFB;
        border-color: #D1D5DB;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        color: #111827;
        transform: translateY(-1px);
    }

    .stitch-social-btn.facebook {
        background-color: #1877F2;
        color: #ffffff;
        border: 1.5px solid #1877F2;
        box-shadow: 0 2px 6px rgba(24, 119, 242, 0.25);
    }
    .stitch-social-btn.facebook:hover {
        background-color: #166FE5;
        border-color: #166FE5;
        box-shadow: 0 4px 10px rgba(24, 119, 242, 0.35);
        color: #ffffff;
        transform: translateY(-1px);
    }
</style>

<div class="stitch-auth-container">
    <div class="text-center mb-4">
        <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-2">
    </div>
    <h1 class="stitch-title">Sign in to your account</h1>
    <p class="stitch-subtitle">Welcome back! Please enter your details.</p>

    <!-- Social Auth at the top -->
    <a href="/auth/google" class="stitch-social-btn google">
        <svg width="20" height="20" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
        </svg>
        <span>Continue with Google</span>
    </a>
    <a href="/auth/facebook" class="stitch-social-btn facebook">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="#ffffff">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
        <span>Continue with Facebook</span>
    </a>

    <div class="stitch-divider">
        <span>OR</span>
    </div>

    <!-- Email Form -->
    <form id="login" class="styleform_form">
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
                Remember me
            </label>
            <a href="/login/forgot?verify=1" class="stitch-link">Forgot password?</a>
        </div>

        <button type="button" class="stitch-submit" id="button">
            Sign In <i class="bi bi-arrow-right"></i>
        </button>
    </form>

    <div class="stitch-footer">
        Don't have an account? <a href="/register" class="stitch-link">Sign up</a>
    </div>

</div>

@endsection