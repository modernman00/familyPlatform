@extends('forms.base')

@section('title', 'Set a New Password')
@section('data-page-id', 'changePW')
@section('meta_description', 'Securely set a new password for your FamilyPlatform account.')

@section('content')

@include('partials.loader', ['scriptOnly' => true])

<div x-data="passChangeComponent()" class="w-100 text-center text-lg-start">
    <!-- Icon Badge -->
    <div class="mb-3 d-inline-flex align-items-center justify-content-center" 
         style="width: 56px; height: 56px; border-radius: 16px; background: rgba(163, 22, 33, 0.2); color: #f87171; border: 1px solid rgba(163, 22, 33, 0.35);">
        <i class="fas fa-lock fs-4"></i>
    </div>

    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1 text-white brand-font">Set a New Password</h2>
        <p class="text-secondary mb-0" style="font-size: 0.95rem; color: #94a3b8 !important;">
            Choose a strong, memorable password to secure your family sanctuary.
        </p>
    </div>

    {{-- Error messages --}}
    @isset($_GET['error'])
    <div class="alert alert-danger border-0 rounded-3 py-3 mb-4 text-white" style="background-color: rgba(220, 38, 38, 0.25); border: 1px solid rgba(220, 38, 38, 0.5) !important;">
        @if($_GET['error'] === 'expired')
            The reset link has expired or is invalid. Please request a new code.
        @elseif($_GET['error'] === 'password_mismatch')
            Passwords do not match.
        @elseif($_GET['error'] === 'weak_password')
            Password must be at least 8 characters long.
        @else
            An error occurred. Please try again.
        @endif
    </div>
    @endisset

    <!-- Notification container for shared library / AJAX -->
    <div id="changePW_notification" class="alert alert-danger py-2 px-3 small mb-3 noDisplay text-white" style="display: none; border-radius: 10px; background-color: #dc2626; border: none;">
        <p id="showError" class="mb-0"></p>
    </div>

    <!-- Password Change Form -->
    <form id="changePW" class="styleform_form changePW mt-3" autocomplete="off">
        @include('csrf')
        <input type="hidden" name="token" value="{{ $_SESSION['token'] ?? '' }}">

        <!-- New Password -->
        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input :type="showPassword ? 'text' : 'password'" 
                       name="password" 
                       id="password" 
                       class="form-control" 
                       placeholder="••••••••••••" 
                       required 
                       autofocus 
                       autocomplete="new-password"
                       x-model="password"
                       @input="calculateStrength()">
                <button type="button" 
                        class="password-toggle-btn" 
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <i class="far" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <p id="password_error" class="text-danger mt-1 small mb-0"></p>
        </div>

        <!-- Password Strength Meter -->
        <div class="mb-3" x-show="password.length > 0" x-cloak>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-secondary" style="font-size: 0.75rem;">Password Strength</span>
                <span class="small fw-bold" :class="strengthClass" x-text="strengthLabel"></span>
            </div>
            <div class="progress" style="height: 4px; background: rgba(255, 255, 255, 0.1); border-radius: 2px;">
                <div class="progress-bar transition-all" 
                     role="progressbar" 
                     :style="'width: ' + strengthPercent + '%; background-color: ' + strengthColor + ';'"></div>
            </div>

            <!-- Password Checklist -->
            <div class="row g-1 mt-2 text-start">
                <div class="col-6 small" :class="hasLength ? 'text-success' : 'text-secondary'" style="font-size: 0.75rem;">
                    <i class="fas" :class="hasLength ? 'fa-check-circle' : 'fa-circle'"></i> 8+ characters
                </div>
                <div class="col-6 small" :class="hasUpperLower ? 'text-success' : 'text-secondary'" style="font-size: 0.75rem;">
                    <i class="fas" :class="hasUpperLower ? 'fa-check-circle' : 'fa-circle'"></i> Upper &amp; lower case
                </div>
                <div class="col-6 small" :class="hasNumber ? 'text-success' : 'text-secondary'" style="font-size: 0.75rem;">
                    <i class="fas" :class="hasNumber ? 'fa-check-circle' : 'fa-circle'"></i> At least 1 number
                </div>
                <div class="col-6 small" :class="hasSpecial ? 'text-success' : 'text-secondary'" style="font-size: 0.75rem;">
                    <i class="fas" :class="hasSpecial ? 'fa-check-circle' : 'fa-circle'"></i> Special character
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label" for="confirm_password">Confirm New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                <input :type="showConfirmPassword ? 'text' : 'password'" 
                       name="confirm_password" 
                       id="confirm_password" 
                       class="form-control" 
                       placeholder="••••••••••••" 
                       required 
                       autocomplete="new-password"
                       x-model="confirmPassword">
                <button type="button" 
                        class="password-toggle-btn" 
                        @click="showConfirmPassword = !showConfirmPassword"
                        :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'">
                    <i class="far" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <!-- Match indicator -->
            <div class="mt-1 small" x-show="confirmPassword.length > 0" x-cloak>
                <span x-show="passwordsMatch" class="text-success"><i class="fas fa-check-circle me-1"></i> Passwords match</span>
                <span x-show="!passwordsMatch" class="text-danger"><i class="fas fa-times-circle me-1"></i> Passwords do not match</span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="button" 
                id="button" 
                class="btn btn-primary w-100 py-3 fw-bold mb-4 rounded-3 d-flex justify-content-center align-items-center"
                data-spin="true">
            <span>Update Password</span> 
            <i class="fas fa-arrow-right ms-2"></i>
        </button>

        <!-- Return to Sign In -->
        <div class="text-center small text-secondary">
            Remembered your credentials? 
            <a href="/login" class="text-brand text-brand-hover fw-bold">Sign In</a>
        </div>
    </form>
</div>

<script nonce="{{ $nonce }}">
function passChangeComponent() {
    return {
        showPassword: false,
        showConfirmPassword: false,
        password: '',
        confirmPassword: '',
        strengthScore: 0,

        get hasLength() {
            return this.password.length >= 8;
        },
        get hasUpperLower() {
            return /[a-z]/.test(this.password) && /[A-Z]/.test(this.password);
        },
        get hasNumber() {
            return /\d/.test(this.password);
        },
        get hasSpecial() {
            return /[^a-zA-Z0-9]/.test(this.password);
        },
        get passwordsMatch() {
            return this.password.length > 0 && this.password === this.confirmPassword;
        },
        get strengthPercent() {
            return Math.min(100, this.strengthScore * 25);
        },
        get strengthLabel() {
            if (this.strengthScore <= 1) return 'Weak';
            if (this.strengthScore <= 3) return 'Moderate';
            return 'Strong';
        },
        get strengthClass() {
            if (this.strengthScore <= 1) return 'text-danger';
            if (this.strengthScore <= 3) return 'text-warning';
            return 'text-success';
        },
        get strengthColor() {
            if (this.strengthScore <= 1) return '#ef4444';
            if (this.strengthScore <= 3) return '#f59e0b';
            return '#10b981';
        },

        calculateStrength() {
            let score = 0;
            if (this.hasLength) score++;
            if (this.hasUpperLower) score++;
            if (this.hasNumber) score++;
            if (this.hasSpecial) score++;
            this.strengthScore = score;
        }
    };
}
</script>

@endsection
