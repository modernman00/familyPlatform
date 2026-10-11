@extends('layouts.landing_layout')
@section('title', 'Forgot Password')
@section('content')

    @include('partials.loader', ['scriptOnly' => true])

    <style nonce="{{ $nonce }}">
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

        .noDisplay {
            display: none !important;
        }

        .stitch-auth-container {
            max-width: 440px;
            margin: 4rem auto 5rem;
            background: #ffffff;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            text-align: center;
        }

        .stitch-icon-badge {
            width: 56px;
            height: 56px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #7b03fc;
            background-color: #EEDDFF;
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
            line-height: 1.5;
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

        .stitch-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--brand-primary, #00bfa5) 0%, var(--brand-primary-dark, #00897b) 100%);
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
            margin-top: 1rem;
            box-shadow: 0 2px 8px rgba(0, 191, 165, 0.22), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .stitch-submit:hover {
            background: linear-gradient(135deg, var(--brand-primary-light, #33c9b2) 0%, var(--brand-primary, #00bfa5) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 191, 165, 0.32);
        }

        .stitch-submit:active {
            transform: scale(0.98);
        }

        .stitch-footer {
            margin-top: 2rem;
            font-size: 0.95rem;
            color: #6B7280;
        }

        .stitch-link {
            color: var(--brand-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .stitch-link:hover {
            text-decoration: underline;
        }

        /* Hide the original BuildFormBStrap submit button if any */
        .styleform_form #btn-forgot {
            display: none !important;
        }
    </style>

    <div class="stitch-auth-container">
        <div class="text-center mb-4">
            <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-2">
        </div>
        
        <div class="stitch-icon-badge">
            <i class="bi bi-shield-check"></i>
        </div>

        <h1 class="stitch-title">Verify your identity</h1>
        <p class="stitch-subtitle">Please enter your email address to receive a 6-digit security code.</p>

        <form id="forgot" class="styleform_form">
    @include('csrf')
            @php
                $formArray = [
                    'forgot_notification' => 'showError',
                    'email' => 'email', // email notification
                    'token' => 'token'
                ];

                $form = new Src\BuildFormBStrap($formArray);
                $form->genForm();
            @endphp
            
            <div id="setLoader" class="loader noDisplay" style="margin: 0 auto; display: none;"></div>

            <button type="button" class="stitch-submit" id="button">
                Send Code <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <div class="stitch-footer">
            Remember your password? <a href="/login" class="stitch-link">Sign in instead</a>
        </div>
    </div>

    <script nonce="{{ $nonce }}">
        document.addEventListener("DOMContentLoaded", function() {
            var emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.placeholder = 'you@example.com';
            }
            
            document.querySelectorAll('.mb-3').forEach(function(el) {
                el.classList.remove('mb-3');
            });
        });
    </script>

@endsection
