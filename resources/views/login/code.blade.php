@extends('layouts.landing_layout')

@section('title', 'Security Verification')
@section('data-page-id', 'code')
@section('meta_description', 'Enter the 6-digit verification code sent to your email.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<style nonce="{{ $nonce }}">
    body {
        background-color: #F4F5FB !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .stitch-auth-container {
        max-width: 460px;
        margin: 3rem auto 5rem;
        background: #ffffff;
        border-radius: 20px;
        padding: 3rem 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        text-align: center;
    }

    .stitch-icon-badge {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: #ffffff;
        background: linear-gradient(135deg, #7b03fc, #6366f1);
        box-shadow: 0 8px 20px rgba(123, 3, 252, 0.28);
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

    #otp-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        width: 100%;
        margin: 0 auto 1.75rem;
    }

    .otp-input {
        width: 56px;
        min-width: 44px;
        height: 64px;
        padding: 0;
        margin: 0;
        text-align: center;
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 64px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background-color: #ffffff;
        color: #1e1b4b;
        box-sizing: border-box;
        text-transform: uppercase;
        direction: ltr;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-variant-numeric: tabular-nums;
        caret-color: #7b03fc;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .otp-input:focus {
        border-color: #7b03fc;
        box-shadow: 0 0 0 4px rgba(123, 3, 252, 0.15);
        outline: none;
    }

    .stitch-submit {
        width: 100%;
        background-color: var(--brand-primary, #7b03fc);
        color: #ffffff;
        border: none;
        border-radius: 999px;
        padding: 13px 16px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 6px 16px rgba(123, 3, 252, 0.25);
    }

    .stitch-submit:hover {
        background-color: #6a02da;
        transform: translateY(-1px);
    }

    .stitch-paste-btn {
        background: #ffffff;
        border: 1px solid #D1D5DB;
        border-radius: 999px;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .stitch-paste-btn:hover {
        border-color: #7b03fc;
        color: #7b03fc;
    }

    .stitch-link {
        color: var(--brand-primary, #7b03fc);
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        font: inherit;
    }

    .stitch-link:hover {
        text-decoration: underline;
    }

    .otp-input:focus-visible,
    .stitch-submit:focus-visible,
    .stitch-paste-btn:focus-visible,
    .stitch-link:focus-visible {
        outline: 2px solid #7b03fc;
        outline-offset: 2px;
    }

    #code_notification {
        border-radius: 10px;
        text-align: left;
        margin-bottom: 1.25rem;
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
    }

    #code_notification.bg-danger,
    #code_notification.bg-success {
        color: #ffffff;
        border: none;
    }

    #code_notification.bg-success { background-color: #16a34a !important; }
    #code_notification.bg-danger { background-color: #dc2626 !important; }

    #code_notification.noDisplay,
    #setLoader.noDisplay {
        display: none !important;
    }

    #setLoader {
        margin: 0 auto 1.25rem;
    }

    @media (max-width: 420px) {
        .stitch-auth-container { padding: 2rem 1.25rem; }
        .otp-input {
            width: 44px;
            min-width: 40px;
            height: 56px;
            font-size: 1.4rem;
            line-height: 56px;
            border-radius: 10px;
        }
        #otp-container { gap: 6px; }
    }
</style>

<div class="stitch-auth-container">
    <div class="stitch-icon-badge">
        <i class="fas fa-shield-alt"></i>
    </div>

    <h1 class="stitch-title">Security Verification</h1>
    <p class="stitch-subtitle">Please enter the 6-digit code sent to your email to verify your identity.</p>

    <form id="code" class="styleform_form" autocomplete="off">

        <div id="setLoader" tabindex="-1" class="loader noDisplay"></div>
        <div class="alert alert-danger noDisplay" id="code_notification" role="alert" aria-live="assertive"><p id="error"></p></div>

        <div id="otp-container" role="group" aria-label="6-digit verification code">
            @for ($i = 1; $i <= 6; $i++)
                <input type="text"
                       class="otp-input"
                       maxlength="1"
                       inputmode="text"
                       pattern="[a-zA-Z0-9]*"
                       aria-label="Digit {{ $i }} of 6"
                       autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}">
            @endfor
        </div>

        <input type="hidden" name="code" id="codeValue">
        <input type="hidden" name="token" id="token" value="{{ $_SESSION['token'] ?? '' }}">

        <button type="button" id="button" class="stitch-submit">
            Verify &amp; Continue <i class="fas fa-arrow-right"></i>
        </button>

        <div class="mt-3">
            <button type="button" class="stitch-paste-btn" id="pasteBtn">
                <i class="far fa-clipboard"></i> Paste Code from Email
            </button>
        </div>

        <div class="mt-3">
            <p class="stitch-subtitle" style="margin-bottom: 0.25rem;">Didn't receive the code?</p>
            <button type="button" class="stitch-link" id="resendBtn">Resend Verification Code</button>
        </div>
    </form>
</div>

@endsection
