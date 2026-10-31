@extends('forms.base')

@section('title', 'Forgot Password')
@section('data-page-id', 'forgot')
@section('meta_description', 'Recover your FamilyPlatform account with a secure verification code.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<div x-data="forgotComponent()" class="w-100 text-center text-lg-start">
    <!-- Icon Badge -->
    <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
         style="width: 56px; height: 56px; border-radius: 16px; background: rgba(163, 22, 33, 0.2); color: #f87171; border: 1px solid rgba(163, 22, 33, 0.35);">
        <i class="fas fa-key fs-4"></i>
    </div>

    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-white brand-font">Forgot Password</h2>
        <p class="text-secondary mb-0" style="font-size: 0.95rem; color: #94a3b8 !important;">
            Enter your registered email address to receive a secure 6-digit recovery code.
        </p>
    </div>

    {{-- Session / URL status messages --}}
    @isset($_GET['error'])
    <div class="alert alert-danger border-0 rounded-3 py-3 mb-4 text-white" style="background-color: rgba(220, 38, 38, 0.25); border: 1px solid rgba(220, 38, 38, 0.5) !important;">
        @if($_GET['error'] === 'not_found')
            Email address not found in our family records.
        @else
            An error occurred. Please verify your email and try again.
        @endif
    </div>
    @endisset

    @isset($_GET['success'])
    <div class="alert alert-success border-0 rounded-3 py-3 mb-4 text-white" style="background-color: rgba(16, 185, 129, 0.25); border: 1px solid rgba(16, 185, 129, 0.5) !important;">
        Recovery code dispatched! Please check your email inbox.
    </div>
    @endisset

    <!-- Error container for shared library / AJAX -->
    <div id="forgot_notification" class="alert alert-danger py-2 px-3 small mb-3 noDisplay text-white" style="display: none; border-radius: 10px; background-color: #dc2626; border: none;">
        <p id="showError" class="mb-0"></p>
    </div>

    <!-- Forgot Password Form -->
    <form id="forgot" class="styleform_form mt-3" autocomplete="on">
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
                       autofocus 
                       autocomplete="email"
                       x-model="email">
            </div>
            <p id="email_error" class="text-danger mt-1 small mb-0"></p>
        </div>

        <!-- Submit Button -->
        <button type="button" 
                id="button" 
                class="btn btn-primary w-100 py-3 fw-bold mb-4 rounded-3 d-flex justify-content-center align-items-center"
                data-spin="true">
            <span>Send Recovery Code</span> 
            <i class="fas fa-arrow-right ms-2"></i>
        </button>

        <!-- Return to Sign In -->
        <div class="text-center small text-secondary">
            Remembered your password? 
            <a href="/login" class="text-brand text-brand-hover fw-bold">Sign In</a>
        </div>
    </form>
</div>

<script nonce="{{ $nonce }}">
function forgotComponent() {
    return {
        email: ''
    };
}
</script>

@endsection
