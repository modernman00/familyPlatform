@extends('layouts.landing_layout')

@section('title', 'Register')
@section('data-page-id', 'register')

@php
    $inviteFamCode = $registerPostData['famCode'] ?? '';
    $familySurname = trim((string)($registerPostData['familySurname'] ?? ($registerPostData['lastName'] ?? '')));
    $invitedFirstName = trim((string)($registerPostData['firstName'] ?? ''));
    $hasInvitation = !empty($inviteFamCode) || !empty($invitedFirstName) || !empty($registerPostData['claim_node']);

    if (!empty($familySurname)) {
        $pageOgTitle = "Join the {$familySurname} Family on FamilyPlatform";
        $pageOgDesc = "You've been invited to connect with the {$familySurname} family on FamilyPlatform. Claim your spot, explore our lineage, and preserve memories.";
    } elseif (!empty($inviteFamCode)) {
        $pageOgTitle = "Join Your Family Tree on FamilyPlatform";
        $pageOgDesc = "You've been invited to connect with your family on FamilyPlatform. Claim your spot, explore our lineage, and preserve memories.";
    } else {
        $pageOgTitle = "Create Your Family Account | FamilyPlatform";
        $pageOgDesc = "Connect with your relatives, build your interactive family tree, and share memories securely on FamilyPlatform.";
    }
@endphp
@section('og_title', $pageOgTitle)
@section('og_description', $pageOgDesc)
@section('og_image', rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', '/') . '/public/img/og-invite.jpg')
@section('og_url', rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', '/') . ($_SERVER['REQUEST_URI'] ?? '/'))

@section('extra_js')
@include('components.auth.family-code-script')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form.register');
    if (!form) return;

    const hasInvitation = @json($hasInvitation);
    const familySurnameInput = form.querySelector('input[name="familySurname"]');
    const famCodeInput = form.querySelector('input[name="famCode"]');
    const radioJoin = form.querySelector('input[name="account_type"][value="join"]');
    const radioCreate = form.querySelector('input[name="account_type"][value="create"]');

    if (!familySurnameInput || !famCodeInput) return;

    // Get the field containers by ID (more reliable than label text parsing)
    let familySurnameField = form.querySelector('#familySurname_div');
    let famCodeField = form.querySelector('#famCode_div');

    const toggleFields = () => {
        const isCreating = radioCreate.checked;

        if (familySurnameField) {
            familySurnameField.style.display = isCreating ? 'block' : 'none';
            familySurnameInput.required = isCreating;
        }

        if (famCodeField) {
            famCodeField.style.display = isCreating ? 'none' : 'block';
            famCodeInput.required = !isCreating;
        }
    };

    // Set initial state
    if (hasInvitation) {
        radioJoin.checked = true;
    } else {
        radioCreate.checked = true;
    }

    // Attach listeners to radio buttons
    if (radioJoin) radioJoin.addEventListener('change', toggleFields);
    if (radioCreate) radioCreate.addEventListener('change', toggleFields);

    // Initial toggle
    toggleFields();

    // Handle form submission — intercept JSON responses for code modal
    form.addEventListener('submit', async (e) => {
        const btn = form.querySelector('#btnSubmit');
        if (btn) {
            btn.classList.add('is-loading');
            btn.disabled = true;
        }

        // Set account_type before submit
        const accountTypeValue = document.querySelector('input[name="account_type"]:checked')?.value === 'create' ? 'true' : 'false';
        const accountTypeHidden = document.querySelector('#account_type_hidden');
        if (accountTypeHidden) {
            accountTypeHidden.value = accountTypeValue;
        }
    });

    // Monitor fetch/XHR to catch JSON responses from registration
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        const response = await originalFetch.apply(window, args);
        if (response.ok && response.headers.get('content-type')?.includes('application/json')) {
            const cloned = response.clone();
            try {
                const json = await cloned.json();
                if (json.show_code_modal && json.family_code) {
                    showFamilyCodeModal(json.family_code, json.redirect || '/login');
                    // Consume the response so normal flow doesn't execute
                    return new Response(JSON.stringify({status: 'handled'}), {status: 200, headers: {'Content-Type': 'application/json'}});
                }
            } catch (e) {
                // Not JSON, continue normally
            }
        }
        return response;
    };

    function showFamilyCodeModal(code, redirectUrl) {
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div class="modal is-active" id="family-code-modal" style="z-index: 9999;">
                <div class="modal-background"></div>
                <div class="modal-card" style="max-width: 500px;">
                    <div class="modal-card-body" style="text-align: center; padding: 3rem 2rem;">
                        <h2 class="title is-3 mb-4">🎉 Family Created!</h2>
                        <p class="subtitle is-6 mb-5">Your family code is:</p>
                        <div class="box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem;">
                            <p style="font-size: 3.5rem; font-weight: bold; color: white; font-family: monospace; letter-spacing: 2px; margin: 0;" id="code-display">
                                ${code}
                            </p>
                        </div>
                        <button id="copy-code-btn" class="button is-primary mt-4" style="width: 100%;">
                            <span class="icon"><i class="fas fa-copy"></i></span>
                            <span>Copy Code</span>
                        </button>
                        <p class="text-muted small mt-4">Share this code with family members so they can join your network.</p>
                        <p style="font-size: 0.9rem; color: #999; margin-top: 1rem;">Redirecting in <span id="countdown">6</span> seconds...</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Copy button handler
        const copyBtn = document.getElementById('copy-code-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(code);
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<span class="icon"><i class="fas fa-check"></i></span><span>Copied!</span>';
                    copyBtn.classList.remove('is-primary');
                    copyBtn.classList.add('is-success');
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                        copyBtn.classList.add('is-primary');
                        copyBtn.classList.remove('is-success');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy:', err);
                }
            });
        }

        let countdown = 6;
        const countdownEl = document.getElementById('countdown');
        const interval = setInterval(() => {
            countdown--;
            if (countdownEl) countdownEl.textContent = countdown;
            if (countdown <= 0) {
                clearInterval(interval);
                window.location.href = redirectUrl;
            }
        }, 1000);
    }
});
</script>
@endsection

@section('extra_css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/autocompleter/autocomplete.min.css">
@include('components.auth.family-code-styles')
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

    /* Hide the auto-generated form section titles ("Personal Information" etc). */
    .register .form-divider,
    .register .title,
    .register h2,
    .register h3 {
        display: none !important;
    }

    /* ...but #inviter-verification-modal lives inside <form class="register">
       (so it can share the Alpine component), and its own <h3> heading must
       stay visible. Re-assert it with higher specificity. */
    #inviter-verification-modal h3,
    #inviter-verification-modal h2 {
        display: block !important;
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

                @if (!empty($inviteTokenError))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="background-color: #fee2e2; border: 1.5px solid #fca5a5; color: #7f1d1d; border-radius: 10px; padding: 16px;">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.3rem; flex-shrink: 0;"></i>
                        <div>
                            <h5 class="alert-heading" style="margin-bottom: 6px; color: #7f1d1d; font-weight: 700;">Invite Link Issue</h5>
                            <p style="margin-bottom: 0; font-size: 0.95rem;">{{ htmlspecialchars($inviteTokenError) }}</p>
                            <hr style="border-color: rgba(0,0,0,0.1); margin: 10px 0;">
                            <p style="margin-bottom: 0; font-size: 0.9rem; color: #991b1b;">
                                <strong>What to do:</strong> Ask your family member to send you a new invite link, or proceed to register with your family code directly.
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                @endif

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

                @if($hasInvitation)
                <div class="invited-relative-hero mb-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); border-radius: 18px; padding: 26px; color: #ffffff; box-shadow: 0 16px 36px rgba(49, 46, 129, 0.28); border: 1.5px solid rgba(129, 140, 248, 0.4); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
                    
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45); font-size: 0.78rem; padding: 5px 12px; border-radius: 999px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                            <i class="bi bi-tree-fill me-1"></i> Official Family Dynasty Invitation
                        </span>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.45); font-size: 0.78rem; padding: 5px 12px; border-radius: 999px; font-weight: 700;">
                            <i class="bi bi-check-circle-fill me-1"></i> Verified Lineage Spot
                        </span>
                    </div>

                    <h2 class="h3 fw-bold mb-2" style="color: #ffffff; letter-spacing: -0.02em;">
                        @if(!empty($invitedFirstName))
                            Welcome, {{ htmlspecialchars($invitedFirstName) }}! Claim Your Spot
                        @else
                            Claim Your Spot in the {{ htmlspecialchars($familySurname ?: 'Family') }} Dynasty
                        @endif
                    </h2>

                    <p style="color: rgba(224, 231, 255, 0.88); font-size: 0.94rem; line-height: 1.55; margin-bottom: 18px; max-width: 620px;">
                        You have been personally invited to claim your branch on the <strong>{{ htmlspecialchars($familySurname ?: 'Family') }} Family Tree</strong>. Connect directly with your kin, discover ancestral stories, and preserve cherished memories together.
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3" style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 12px; padding: 12px 18px;">
                        <div class="d-flex align-items-center gap-2">
                            <span style="color: #94a3b8; font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;">Family Code:</span>
                            <strong style="color: #fbbf24; font-size: 1rem; letter-spacing: 0.06em; font-family: monospace;">{{ htmlspecialchars($inviteFamCode ?: 'PRE-SET') }}</strong>
                        </div>
                        <div style="height: 16px; width: 1px; background: rgba(255,255,255,0.2);"></div>
                        <div class="d-flex align-items-center gap-2" style="color: #c7d2fe; font-size: 0.84rem;">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Fast-Track: Name &amp; Code Pre-Filled Below</span>
                        </div>
                    </div>
                </div>
                @endif

                <form class="register" id="register" method="POST" enctype="multipart/form-data" autocomplete="off" x-data="familyCodeApprovalForm()" x-init="isCreating = $el.dataset.isCreating === 'true'" :data-is-creating="isCreating">
                    @if(!empty($registerPostData['claim_node']))
                    <input type="hidden" name="claim_node" value="{{ (int)$registerPostData['claim_node'] }}">
                    @endif

                    <!-- Create vs Join Toggle -->
                    <div class="field mb-4" style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 12px; padding: 20px;">
                        <label style="color: #cbd5e1; font-weight: 600; font-size: 0.95rem; display: block; margin-bottom: 12px;">What would you like to do?</label>
                        <div class="d-flex gap-4">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #e2e8f0;">
                                <input type="radio" name="account_type" value="join" x-model="isCreating" :value="false" style="cursor: pointer;">
                                <span>Join existing family (have a code?)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #e2e8f0;">
                                <input type="radio" name="account_type" value="create" x-model="isCreating" :value="true" style="cursor: pointer;">
                                <span>Create new family</span>
                            </label>
                        </div>
                    </div>

                    @php
                    $formArray = [
                    'Personal Information' => 'title',
                    'name' => [
                    'mixed',
                    'label' => ['first Name', 'last Name', 'Family Surname', 'Family code <button type="button" id="generateFamilyCode" class="button is-small is-primary ms-2 js-modal-trigger" data-target="modal-familyCode" style="font-size: 0.7rem; padding: 0.2rem 0.5rem; vertical-align: middle;">Generate</button>'],
                    'attribute' => ['firstName', 'lastName', 'familySurname', 'famCode'],
                    'placeholder' => ['Toyin', 'Edwars', 'e.g. Olaogun', 'check your email for the code '],
                    'inputType' => ['text', 'text', 'text', 'text'],
                    'value' => [
                    isset($registerPostData['firstName']) ? $registerPostData['firstName'] : '',
                    isset($registerPostData['lastName']) ? $registerPostData['lastName'] : '',
                    isset($registerPostData['familySurname']) ? $registerPostData['familySurname'] : '',
                    isset($registerPostData['famCode']) ? $registerPostData['famCode'] : ''
                    ],
                    'icon' => [
                    '<i class="fas fa-user"></i>',
                    '<i class="fas fa-user"></i>',
                    '<i class="fas fa-home"></i>',
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

                    <!-- Hidden inputs to track approval state. joining_via_invitation
                         must reflect a *verified* invitation, not merely that the
                         code exists, so the server creates the approval request. -->
                    <input type="hidden" id="joining_via_invitation" :value="codeVerified ? 'true' : 'false'" name="joining_via_invitation">
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
                    <input type="hidden" name="account_type" id="account_type_hidden">

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