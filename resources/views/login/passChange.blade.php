@extends('layouts.landing_layout')
@section('title', 'Change Password')

@section('content')

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
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 16px; /* Rounded square */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            background-color: var(--brand-primary);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4); /* Purple glow */
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
            color: #1A1A24;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .styleform_form .form-control {
            border-radius: 8px;
            border: 1px solid #D1D5DB;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            box-shadow: none;
            margin-bottom: 0.25rem;
        }
        
        .styleform_form .form-control::placeholder {
            color: #6B7280;
        }

        .styleform_form .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
            outline: none;
        }

        .input-hint {
            font-size: 0.8rem;
            color: #6B7280;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .stitch-submit {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 1.25rem;
            display: inline-block;
        }

        .stitch-submit:hover {
            background-color: #004182;
        }

        .stitch-checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1A1A24;
            font-size: 0.9rem;
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

        /* Clean form styling */
        .styleform_form .form-check {
            margin-bottom: 1rem;
            text-align: left;
        }
    </style>

    <div class="stitch-auth-container">
        <div class="text-center mb-4">
            <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-2">
        </div>
        <div class="stitch-icon-badge">
            <i class="bi bi-key-fill"></i>
        </div>

        <h1 class="stitch-title">Set a new password</h1>
        <p class="stitch-subtitle">Choose a strong password you haven't used before.</p>

        <form action="" id="changePW" class="styleform_form changePW">
            @php
                $formArray = [
                    'changePW_notification' => 'showError',
                    'password' => 'password',
                    'showPassword' => 'showPassword',
                    'token' => 'token',
                ];

                $form = new Src\BuildFormBStrap($formArray);
                $form->genForm();
            @endphp

            <button type="button" class="stitch-submit w-100 mt-3" id="button">
                Submit
            </button>
        </form>
    </div>


@endsection
