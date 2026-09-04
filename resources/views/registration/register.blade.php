@extends('layouts.landing_layout')

@section('title', 'Register')
@section('data-page-id', 'register')

@php
    $inviteFamCode = $registerPostData['famCode'] ?? '';
    $inviteName = trim(($registerPostData['firstName'] ?? '') . ' ' . ($registerPostData['lastName'] ?? ''));
    if (!empty($inviteName)) {
        $pageOgTitle = "Join {$inviteName} on FamilyPlatform";
        $pageOgDesc = "You've been invited to connect with the {$inviteFamCode} family on FamilyPlatform. Claim your spot, explore our lineage, and preserve memories.";
    } elseif (!empty($inviteFamCode)) {
        $pageOgTitle = "Join the {$inviteFamCode} Family Tree on FamilyPlatform";
        $pageOgDesc = "You've been invited to explore our family lineage, connect with relatives, and share cherished memories.";
    } else {
        $pageOgTitle = "Create Your Family Account | FamilyPlatform";
        $pageOgDesc = "Connect with your relatives, build your interactive family tree, and share memories securely on FamilyPlatform.";
    }
@endphp
@section('og_title', $pageOgTitle)
@section('og_description', $pageOgDesc)

@section('extra_js')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('familyCodeApprovalForm', () => ({
        familyCode: '{{ old("famCode", "") }}',
        codeExists: false,
        codeVerified: false,
        checkingCode: false,
        verifying: false,
        temporaryCode: '',
        inviterFirstName: '{{ old("inviter_first_name") }}',
        inviterLastName: '{{ old("inviter_last_name") }}',
        inviterContact: '{{ old("inviter_email_or_mobile") }}',

        init() {
            const self = this;
            console.log('✓ Alpine init called for family code approval');
            const form = document.querySelector('form.register');
            if (form) {
                console.log('✓ Form found, binding blur listener');
                form.addEventListener('blur', (e) => {
                    if (e.target && e.target.name === 'famCode') {
                        console.log('✓ Blur on famCode via delegation:', e.target.value);
                        self.familyCode = e.target.value;
                        self.checkFamilyCode();
                    }
                }, true);
                form.addEventListener('input', (e) => {
                    if (e.target && e.target.name === 'famCode') {
                        console.log('✓ Input on famCode:', e.target.value);
                        self.familyCode = e.target.value;
                    }
                });
            } else {
                console.log('✗ Form NOT found');
            }
            setTimeout(() => {
                const familyCodeInput = document.querySelector('input[name="famCode"]');
                if (familyCodeInput && !familyCodeInput.__familyCodeBound) {
                    console.log('✓ Direct binding to famCode input');
                    familyCodeInput.__familyCodeBound = true;
                    familyCodeInput.addEventListener('blur', () => {
                        console.log('✓ Direct blur on famCode:', familyCodeInput.value);
                        self.familyCode = familyCodeInput.value;
                        self.checkFamilyCode();
                    });
                    self.familyCode = familyCodeInput.value;
                    if (self.familyCode.trim()) {
                        self.checkFamilyCode();
                    }
                } else {
                    console.log('✗ famCode input NOT found or already bound');
                }
            }, 100);
        },

        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ||
                   document.querySelector('input[name="_token"]')?.value ||
                   document.querySelector('input[name="token"]')?.value || '';
        },

        async checkFamilyCode() {
            console.log('→ checkFamilyCode() called');
            const input = document.querySelector('input[name="famCode"]');
            if (!input) {
                console.log('✗ Input not found');
                return;
            }
            this.familyCode = input.value;
            console.log('→ Checking code:', this.familyCode);

            if (!this.familyCode.trim()) {
                console.log('✗ Empty code, resetting');
                this.codeExists = false;
                this.codeVerified = false;
                return;
            }

            this.checkingCode = true;

            try {
                console.log('→ Calling API...');
                const response = await fetch('/api/family-code/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': this.getCsrfToken()
                    },
                    body: JSON.stringify({
                        family_code: this.familyCode
                    })
                });

                console.log('→ API response status:', response.status);
                const data = await response.json();
                console.log('→ API data:', data);
                this.codeExists = data.exists === true;
                console.log('✓ codeExists set to:', this.codeExists);

                if (this.codeExists) {
                    this.temporaryCode = data.temporary_code || '';
                }
            } catch (error) {
                console.error('Error checking family code:', error);
                this.codeExists = false;
            } finally {
                this.checkingCode = false;
            }
        },

        async verifyInviter() {
            console.log('→ verifyInviter() called');
            if (!this.inviterFirstName.trim() || !this.inviterLastName.trim() || !this.inviterContact.trim()) {
                console.log('✗ Missing inviter details');
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in all inviter details',
                    confirmButtonText: 'OK'
                });
                return;
            }

            console.log('→ Verifying:', {
                code: this.familyCode,
                firstName: this.inviterFirstName,
                lastName: this.inviterLastName,
                contact: this.inviterContact
            });

            this.verifying = true;

            try {
                console.log('→ Calling verify API...');
                const response = await fetch('/api/family-code/verify-inviter', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': this.getCsrfToken()
                    },
                    body: JSON.stringify({
                        family_code: this.familyCode,
                        inviter_first_name: this.inviterFirstName,
                        inviter_last_name: this.inviterLastName,
                        inviter_email_or_mobile: this.inviterContact
                    })
                });

                console.log('→ API response status:', response.status);
                const data = await response.json();
                console.log('→ API response:', data);

                if (response.ok && data.verified) {
                    console.log('✓ Verification successful!');
                    this.codeVerified = true;

                    // Close modal after showing verified badge (1.5s to display the success message)
                    setTimeout(() => {
                        console.log('Closing modal...');
                        this.codeExists = false;
                        this.codeVerified = false;
                        console.log('Modal closed');
                    }, 1500);
                } else {
                    console.log('✗ Verification failed:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: data.message || 'Unable to verify inviter. Please check the information and try again.',
                        confirmButtonText: 'Try Again'
                    });
                }
            } catch (error) {
                console.error('✗ Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonText: 'OK'
                });
            } finally {
                this.verifying = false;
            }
        }
    }));
});
</script>
@endsection

@section('extra_css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/autocompleter/autocomplete.min.css">
<style>
    .register-card {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
        margin-bottom: 4rem;
    }

    .register-title {
        color: var(--brand-secondary);
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .register-subtitle {
        color: var(--text-muted);
        margin-bottom: 2.5rem;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal.is-active {
        display: flex;
    }

    .modal-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 41, 130, 0.4);
        backdrop-filter: blur(8px);
    }

    .modal-content {
        position: relative;
        width: 100%;
        max-width: 500px;
        z-index: 1060;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.3);
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 1070;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(0, 0, 0, 0.5);
    }

    .stitch-social-btn {
        width: 100%;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 1rem;
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

    /* Hide form section titles */
    .register .form-divider,
    .register .title,
    .register h2,
    .register h3 {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="register-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="register-title h2">Join Your Family Network</h1>
                    <p class="register-subtitle">Complete the steps below to create your secure family account</p>
                </div>

                @include('partials.loader', ['notificationId'=> 'register'])

                @if (!isset($_SESSION['oauth_pending']))
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
                    <span>OR REGISTER WITH EMAIL</span>
                </div>
                @else
                <div class="alert alert-info text-center fw-bold" style="background-color: #EBF5FF; color: #1E3A8A; border: 1px solid #BFDBFE; border-radius: 8px; padding: 12px; margin-bottom: 24px;">
                    <i class="bi bi-info-circle-fill me-2"></i> Almost done! Please provide your Family Code and Mobile Number to complete registration.
                </div>
                @endif

                @if(!empty($registerPostData['claim_node']))
                <div class="alert alert-success text-center fw-semibold mb-4" style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px;">
                    <i class="bi bi-tree-fill me-2"></i> <strong>Family Invitation:</strong> You are registering to connect directly with family code <strong>{{ $registerPostData['famCode'] ?? '' }}</strong>!
                </div>
                @endif

                <form class="register" id="register" method="POST" enctype="multipart/form-data" autocomplete="off" x-data="familyCodeApprovalForm()">
                    @if(!empty($registerPostData['claim_node']))
                    <input type="hidden" name="claim_node" value="{{ (int)$registerPostData['claim_node'] }}">
                    @endif

                    @php
                    $formArray = [
                    'Personal Information' => 'title',
                    'name' => [
                    'mixed',
                    'label' => ['first Name', 'last Name', 'Family code <button type="button" id="generateFamilyCode" class="button is-small is-primary ms-2 js-modal-trigger" data-target="modal-familyCode" style="font-size: 0.7rem; padding: 0.2rem 0.5rem; vertical-align: middle;">Generate</button>'],
                    'attribute' => ['firstName', 'lastName', 'famCode'],
                    'placeholder' => ['Toyin', 'Edwars', 'check your email for the code '],
                    'inputType' => ['text', 'text', 'text'],
                    'value' => [
                    isset($registerPostData['firstName']) ? $registerPostData['firstName'] : '',
                    isset($registerPostData['lastName']) ? $registerPostData['lastName'] : '',
                    isset($registerPostData['famCode']) ? $registerPostData['famCode'] : ''
                    ],
                    'icon' => [
                    '<i class="fas fa-user"></i>',
                    '<i class="fas fa-user"></i>',
                    '<i class="fas fa-barcode"></i>'
                    ]
                    ],
                    'Date_of_Birth' => 'birthday',
                    'Contact Information' => 'title',
                    'email_mobile' => [
                    'mixed',
                    'label' => ["Email", "Country", 'Mobile'],
                    'attribute' => ['email', 'country', 'mobile'],
                    'placeholder' => ['toyin@yahoo.com', 'e.g. UK', 'include the area code - 234 or 1 or 44'],
                    'inputType' => ['email', 'select', 'text'],
                    'value' => [
                    isset($registerPostData['email']) ? $registerPostData['email'] : '',
                    isset($registerPostData['country']) ? $registerPostData['country'] : '',
                    isset($registerPostData['mobile']) ? $registerPostData['mobile'] : ''
                    ],
                    'options' => [
                    [],
                    ['United Kingdom','Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo (Brazzaville)', 'Congo (Kinshasa)', 'Costa Rica', 'Côte d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'North Korea', 'South Korea', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'],
                    []
                    ],
                    'icon' => [
                    '<i class="fas fa-envelope-square"></i>',
                    '<i class="fas fa-globe"></i>',
                    '<i class="fas fa-mobile-alt"></i>',
                    ]
                    ]
                    ];

                    if (!isset($_SESSION['oauth_pending'])) {
                    $formArray['create an account'] = 'title';
                    $formArray['account'] = [
                    'mixed',
                    'label' => ['Password', 'Confirm password'],
                    'attribute' => ['password', 'confirm_password'],
                    'placeholder' => ['xxxx', 'xxxx'],
                    'inputType' => ['password', 'password'],
                    'value' => [
                    isset($registerPostData['password']) ? $registerPostData['password'] : '',
                    isset($registerPostData['confirm_password']) ? $registerPostData['confirm_password'] : ''
                    ],
                    'icon' => [
                    '<i class="fas fa-user-secret"></i>',
                    '<i class="fas fa-user-secret"></i>',
                    ]
                    ];
                    } else {
                    echo '<input type="hidden" name="password" value="' . htmlspecialchars($registerPostData['password'] ?? '') . '">';
                    echo '<input type="hidden" name="confirm_password" value="' . htmlspecialchars($registerPostData['confirm_password'] ?? '') . '">';
                    }

                    $form = new \Src\BuildFormBulma($formArray);
                    $form->genForm();
                    @endphp

                    <!-- Inviter Verification Modal (shown when valid code entered) -->
                    @include('components.auth.family-code-verification', ['errors' => $errors ?? []])

                    <!-- Hidden inputs to track approval state -->
                    <input type="hidden" id="joining_via_invitation" x-model="codeExists" name="joining_via_invitation">
                    <input type="hidden" id="temporary_code" name="temporary_code" x-model="temporaryCode">

                    <div class="field mt-4">
                        <label class="checkbox">
                            <input type="checkbox" name="checkbox" id="checkbox" required>
                            By submitting this form, you agree to the handling of your information as outlined in our <a href="/privacy" class="text-decoration-none fw-bold" style="color: var(--brand-primary);">PRIVACY POLICY</a>
                        </label>
                        <div id="checkbox_error" class="help is-danger mt-1"></div>
                    </div>

                    <div class="field mt-5">
                        <button type="submit" name="submit" id="btnSubmit" data-ready="true" class="button is-primary is-fullwidth">Submit form</button>
                    </div>
                    <input type="hidden" name="token" id="token" value="{{ $_SESSION['token'] ?? '' }}">

                </form>

                <div id="modal-familyCode" class="modal glass-modal">
                    <div class="modal-background glass-overlay"></div>
                    <div class="modal-content glass-card">
                        <div class="text-center mb-4">
                            <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 48px;" class="mb-3 logo-glow">
                            <h3 class="fw-bold premium-text">Create Your Family Code</h3>
                            <p class="text-muted small">Create a unique code to invite your family members</p>
                        </div>

                        <div class="field mt-4">
                            <div class="control glass-input-wrapper">
                                <input type="text" id="surname" name="surname" class="input is-large glass-input" placeholder=" ">
                                <label for="surname" class="glass-floating-label">Family Surname</label>
                            </div>
                            <div id="surname_error" class="help is-danger"></div>
                        </div>

                        <div class="field mt-4">
                            <button name="btnFamCode" id="btnFamCode" type="button" class="button is-large is-fullwidth btn-gradient glow-effect">
                                GENERATE CODE
                            </button>
                        </div>

                        <div class="field mt-4 code-output-wrapper">
                            <p class="text-muted small mb-2 text-center px-1 fw-bold" style="color: #4a5568;">Family Code</p>
                            <div class="field mb-3" id="createFamCode">
                                <input class="input is-large glass-code-input text-center fw-bold" type="text" id="createCode" placeholder="**CODE HERE**" readonly style="width: 100%;">
                            </div>
                            <div class="field">
                                <button id="copyIcon" class="button is-large is-fullwidth btn-coral-glow fw-bold" type="button">
                                    Copy Code <i class="far fa-copy ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="text-muted small">Share this code with your family members so they can join your secure network.</p>
                        </div>
                    </div>
                    <button type="button" id="modal-close-code" class="modal-close is-large" aria-label="close"></button>
                </div>


                @if (getenv('APP_ENV') === 'development' || getenv('APP_ENV') === 'local')
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const dummyData = @json($registerPostData ?? []);
                        for (const [name, val] of Object.entries(dummyData)) {
                            const el = document.querySelector(`[name="${name}"]`);
                            if (el && val !== null && val !== '') {
                                el.value = val;
                            }
                        }
                        const termsCheckbox = document.getElementById('checkbox');
                        if (termsCheckbox) termsCheckbox.checked = true;
                    });
                </script>
                @endif

                <div class="mt-4 text-center">
                    <p class="text-muted small">Already have an account? <a href="/login" class="fw-bold text-decoration-none" style="color: var(--brand-primary);">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection