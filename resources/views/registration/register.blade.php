@extends('layouts.landing_layout')

@section('title', 'Register')
@section('data-page-id', 'register')

@section('extra_css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/autocompleter/autocomplete.min.css">
<style>
    .register-card {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
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
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0,0,0,0.3);
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
        background: rgba(0,0,0,0.5);
    }
    
    .stitch-social-btn {
        width: 100%;
        background-color: #fff;
        color: #374151;
        border: 1px solid #D1D5DB;
        border-radius: 8px;
        padding: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        transition: background 0.2s;
        text-decoration: none;
    }
    .stitch-social-btn:hover {
        background-color: #F9FAFB;
    }
    .stitch-social-btn.facebook {
        color: #1877F2;
    }
    .stitch-social-btn.google {
        color: #DB4437;
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
                    <i class="bi bi-google"></i> Continue with Google
                </a>
                <a href="/auth/facebook" class="stitch-social-btn facebook">
                    <i class="bi bi-facebook"></i> Continue with Facebook
                </a>

                <div class="stitch-divider">
                    <span>OR REGISTER WITH EMAIL</span>
                </div>
                @else
                <div class="alert alert-info text-center fw-bold" style="background-color: #EBF5FF; color: #1E3A8A; border: 1px solid #BFDBFE; border-radius: 8px; padding: 12px; margin-bottom: 24px;">
                    <i class="bi bi-info-circle-fill me-2"></i> Almost done! Please provide your Family Code and Mobile Number to complete registration.
                </div>
                @endif

                <form class="register" id="register" enctype="multipart/form-data" autocomplete="off">
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
                                    ['Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo (Brazzaville)', 'Congo (Kinshasa)', 'Costa Rica', 'Côte d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'North Korea', 'South Korea', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'],
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
                    
                    <div class="field mt-4">
                        <label class="checkbox">
                            <input type="checkbox" name="checkbox" id="checkbox" required>
                            By submitting this form, you agree to the handling of your information as outlined in our <a href="/privacy" class="text-decoration-none fw-bold" style="color: var(--brand-primary);">PRIVACY POLICY</a>
                        </label>
                    </div>

                    <div class="field mt-3 mb-4 p-3" style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <label class="checkbox fw-bold text-dark">
                            <input type="checkbox" name="ai_consent" id="ai_consent" value="1">
                            🤖 AI Processing Consent (GDPR)
                        </label>
                        <p class="text-muted small mt-1 mb-0" style="margin-left: 24px;">
                            I consent to having my public family network activity processed by secure AI services to generate family biographies and insights. I understand I can revoke this at any time in my settings.
                        </p>
                    </div>

                    <div class="field mt-5">
                        <button type="submit" id="btnSubmit" class="button is-primary is-fullwidth">Submit form</button>
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
                            
                            <div class="field mt-5">
                                <div class="control glass-input-wrapper">
                                    <input type="text" id="surname" name="surname" class="input is-large glass-input" placeholder=" ">
                                    <label for="surname" class="glass-floating-label">Family Surname</label>
                                </div>
                                <div id="surname_error" class="help is-danger"></div>
                            </div>

                            <div class="field mt-5">
                                <button name="btnFamCode" id="btnFamCode" type="button" class="button is-large is-fullwidth btn-gradient glow-effect">
                                    GENERATE CODE
                                </button>
                            </div>

                            <div class="field mt-5 code-output-wrapper">
                                <p class="text-muted small mb-2 text-start px-1 fw-bold" style="color: #4a5568;">Family Code</p>
                                <div class="field has-addons is-expanded has-addons-centered code-addons">
                                    <div class="control is-expanded" id="createFamCode">
                                        <input class="input is-large glass-code-input text-center fw-bold" type="text" id="createCode" placeholder="**CODE HERE**" readonly>
                                    </div>
                                    <div class="control">
                                        <button id="copyIcon" class="button is-large btn-coral-glow fw-bold" type="button">
                                            Copy Code <i class="far fa-copy ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 text-center">
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

