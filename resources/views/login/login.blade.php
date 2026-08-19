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
        border-color: #8B5CF6;
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
        accent-color: #8B5CF6;
        cursor: pointer;
    }

    .stitch-link {
        color: #8B5CF6;
        text-decoration: none;
        font-weight: 600;
    }

    .stitch-link:hover {
        text-decoration: underline;
    }

    .stitch-submit {
        width: 100%;
        background-color: #8B5CF6;
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
        background-color: #7C3AED;
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
</style>

<div class="stitch-auth-container">
    <div class="text-center mb-4">
        <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-2">
    </div>
    <h1 class="stitch-title">Sign in to your account</h1>
    <p class="stitch-subtitle">Welcome back! Please enter your details.</p>

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