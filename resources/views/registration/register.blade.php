@extends('layouts.landing_layout')

@section('title', 'Register')
@section('data-page-id', 'register')

@php
    $inviteFamCode = $registerPostData['famCode'] ?? '';
    $familySurname = ucwords(trim((string)($registerPostData['familySurname'] ?? ($registerPostData['lastName'] ?? ''))));
    $invitedFirstName = trim((string)($registerPostData['firstName'] ?? ''));
    // An invitation means a family code or tree node came with the link. A first
    // name alone (e.g. from Google sign-in) is not an invitation.
    $hasInvitation = !empty($inviteFamCode) || !empty($registerPostData['claim_node']);
    $fprDoneMsg = [
        'family_title' => __('reg.done_family_title', 'Your family has been created!'),
        'created_title' => __('reg.done_created_title', 'Your account has been created'),
        'share' => __('reg.done_share', 'Share this code with relatives so they can join your family.'),
        'your_code' => __('reg.done_your_code', 'Your family code'),
        'not_found_1' => __('reg.done_not_found_1', 'We couldn\'t find the family code :code, so you aren\'t linked to your relatives yet.'),
        'not_found_2' => __('reg.done_not_found_2', 'Ask them for the right family code, then find them under All Members and request to join their family.'),
        'temp_code' => __('reg.done_temp_code', 'Your own family code for now'),
        'copy' => __('reg.done_copy', 'Copy code'),
        'copied' => __('reg.done_copied', 'Copied'),
        'go' => __('reg.done_go', 'Go to my profile'),
        'go_login' => __('reg.done_go_login', 'Go to sign in'),
        'signed_up_title' => __('reg.done_signed_up', 'You\'re signed up'),
    ];

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
// Register v2: the post-sign-up dialog. Submission itself is handled in
// registration/include/registerSteps.blade.php, which calls window.fprShowDone().
(function () {
    'use strict';
    const T = @json($fprDoneMsg);
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    window.fprShowDone = function (opts) {
        const code = opts && opts.code ? String(opts.code) : '';
        const missing = opts && opts.notFound ? String(opts.notFound) : '';
        const redirectUrl = (opts && opts.redirect) || '/login';
        const message = (opts && opts.message) || '';
        const old = document.getElementById('family-code-modal');
        if (old) old.remove();

        let body;
        if (code && missing) {
            body = `<div class="fpr-done-icon is-warn" aria-hidden="true">!</div>
                <h2 class="fpr-done-title" id="fprDoneTitle">${esc(T.created_title)}</h2>
                <p class="fpr-done-text">${esc(T.not_found_1).replace(':code', '<strong>' + esc(missing) + '</strong>')}</p>
                <p class="fpr-done-text">${esc(T.not_found_2)}</p>
                <p class="fpr-done-label">${esc(T.temp_code)}</p>`;
        } else if (code) {
            body = `<div class="fpr-done-icon" aria-hidden="true">✓</div>
                <h2 class="fpr-done-title" id="fprDoneTitle">${esc(T.family_title)}</h2>
                <p class="fpr-done-text">${esc(T.share)}</p>
                <p class="fpr-done-label">${esc(T.your_code)}</p>`;
        } else {
            body = `<div class="fpr-done-icon" aria-hidden="true">✓</div>
                <h2 class="fpr-done-title" id="fprDoneTitle">${esc(T.signed_up_title)}</h2>
                <p class="fpr-done-text">${esc(message)}</p>`;
        }

        const wrap = document.createElement('div');
        wrap.className = 'fpr-done';
        wrap.id = 'family-code-modal';
        wrap.setAttribute('role', 'dialog');
        wrap.setAttribute('aria-modal', 'true');
        wrap.setAttribute('aria-labelledby', 'fprDoneTitle');
        wrap.innerHTML = `<div class="fpr-done-card">${body}
            ${code ? `<p class="fpr-done-code" id="code-display">${esc(code)}</p>
                      <button type="button" id="copy-code-btn" class="fpr-btn fpr-btn--ghost">${esc(T.copy)}</button>` : ''}
            <a id="dashboard-btn" href="${esc(redirectUrl)}" class="fpr-btn fpr-btn--primary">${esc(redirectUrl.indexOf('/login') === 0 ? T.go_login : T.go)}</a>
        </div>`;
        (document.getElementById('fpr') || document.body).appendChild(wrap);
        const go = document.getElementById('dashboard-btn');
        if (go) go.focus();

        const copyBtn = document.getElementById('copy-code-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(code);
                    copyBtn.textContent = T.copied;
                    setTimeout(() => { copyBtn.textContent = T.copy; }, 2000);
                } catch (err) {
                    console.warn('Copy failed:', err);
                }
            });
        }
    };
})();
</script>
@endsection

@section('extra_css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/autocompleter/autocomplete.min.css">
@include('components.auth.family-code-styles')
<link rel="stylesheet" href="/public/css/register-v2.css?v={{ assetVersion('css/register-v2.css') }}">
@endsection

@section('content')
@php
    $rp = $registerPostData ?? [];
    $fv = static function (string $k) use ($rp): string {
        $v = $rp[$k] ?? '';
        return is_scalar($v) ? (string) $v : '';
    };
    $isOauth = isset($_SESSION['oauth_pending']);
    $activeLocale = \App\classes\I18n::getLocale();
    $supportedLocales = \App\classes\I18n::getSupportedLocales();
    $currLocale = $supportedLocales[$activeLocale] ?? reset($supportedLocales);
    $currFlag = is_array($currLocale) ? (string) ($currLocale['flag'] ?? '') : '';
    $currCode = strtoupper(substr((string) $activeLocale, 0, 2));
    $csrfValue = (string) ($_SESSION['token'] ?? '');
    $treeFamily = $familySurname !== '' ? $familySurname : __('reg.tree_family', 'Your family');
    $treeYou = $invitedFirstName !== '' ? $invitedFirstName : __('reg.tree_you', 'You');
    $selCountry = $fv('country') !== '' ? $fv('country') : 'United Kingdom';
    $selDay = $fv('day');
    $selMonth = $fv('month');
    $selYear = $fv('year');
    $selGender = $fv('gender');
    $monthNames = explode(',', __('reg.months', 'January,February,March,April,May,June,July,August,September,October,November,December'));
    $monthKeys = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $months = [];
    foreach ($monthKeys as $mi => $mk) {
        $months[$mk] = trim((string) ($monthNames[$mi] ?? $mk));
    }
    $maxYear = (int) date('Y') - 13;
    $countries = ['United Kingdom','Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo (Brazzaville)', 'Congo (Kinshasa)', 'Costa Rica', 'Côte d\'Ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Fiji', 'Finland', 'France', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'North Korea', 'South Korea', 'Kosovo', 'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macedonia', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Swaziland', 'Sweden', 'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'];
    $oauthParams = [];
    foreach (['invite', 'ref', 'claim_node'] as $qk) {
        if (!empty($_GET[$qk]) && is_string($_GET[$qk])) {
            $oauthParams[$qk] = (string) $_GET[$qk];
        }
    }
    $s3Title = $isOauth ? __('reg.s3_title_oauth', 'Last step') : __('reg.s3_title', 'Secure your account');
    $s3Sub = $isOauth ? __('reg.s3_sub_oauth', 'Confirm and you\'re in.') : __('reg.s3_sub', 'Choose a password you don\'t use anywhere else.');
    // Mirrors hasDirectInvite in components/auth/family-code-script: these links
    // arrive with the code already confirmed, so the person starts on step 2.
    $isDirectInvite = !empty($_GET['invite']) || !empty($_GET['invite_token']) || !empty($_GET['claim_node'])
        || (!empty($_GET['famCode']) && (!empty($_GET['name']) || !empty($_GET['firstName'])));
    $startStep = ($isDirectInvite && $inviteFamCode !== '' && empty($inviteTokenError)) ? 2 : 1;
    if ($invitedFirstName !== '' && $familySurname !== '') {
        $welcomeTitle = \App\classes\I18n::trans('reg.welcome_title_both', ['name' => $invitedFirstName, 'family' => $familySurname], 'Welcome, :name! The :family family saved you a place.');
    } elseif ($familySurname !== '') {
        $welcomeTitle = \App\classes\I18n::trans('reg.welcome_title_family', ['family' => $familySurname], 'The :family family saved you a place.');
    } elseif ($invitedFirstName !== '') {
        $welcomeTitle = \App\classes\I18n::trans('reg.welcome_title_name', ['name' => $invitedFirstName], 'Welcome, :name! Your family saved you a place.');
    } else {
        $welcomeTitle = __('reg.welcome_title', 'Your family saved you a place.');
    }
    $welcomeText = $startStep === 2
        ? __('reg.welcome_text_ready', 'Your family code is filled in. Check your details, then choose a password.')
        : __('reg.welcome_text', 'Your family code is filled in below. Tell us who invited you to continue.');
    $oauthQueryStr = !empty($oauthParams) ? '?' . http_build_query($oauthParams) : '';
@endphp

<div class="fpr" id="fpr" data-start-step="{{ $startStep }}" data-has-invitation="{{ $hasInvitation ? '1' : '0' }}" data-oauth="{{ $isOauth ? '1' : '0' }}">
<div class="fpr-shell">

    {{-- ===== Story panel ===== --}}
    <aside class="fpr-aside" aria-hidden="false">
        <a href="/" class="fpr-brand"><img src="{{ getenv('APP_LOGO') }}" alt="" width="36" height="36"> FamilyPlatform</a>
        <div>
            <h2 class="fpr-aside-title">{{ __('reg.aside_title', 'Every family has a story.') }} <em>{{ __('reg.aside_accent', 'Add your branch.') }}</em></h2>
            <p class="fpr-aside-lede" style="margin-top:12px;">{{ __('reg.aside_lede', 'One private space for your family tree, memories and conversations, shared only with relatives.') }}</p>
        </div>

        <svg class="fpr-tree" viewBox="0 0 360 250" role="img" aria-label="{{ __('reg.tree_label', 'Your place on the family tree') }}">
            <path class="branch" d="M180 62 C180 100 80 96 80 132" />
            <path class="branch" d="M180 62 C180 100 280 96 280 132" />
            <path class="branch" d="M80 160 C80 190 40 186 40 206" />
            <path class="branch-you" d="M280 160 C280 192 220 186 220 204" />
            <path class="branch" d="M280 160 C280 190 320 186 320 206" />
            <rect class="node node-root" x="110" y="22" width="140" height="40" rx="20" />
            <text x="180" y="47" data-fpr-tree="family">{{ $treeFamily }}</text>
            <circle class="node" cx="80" cy="146" r="14" />
            <circle class="node" cx="280" cy="146" r="14" />
            <circle class="node" cx="40" cy="218" r="11" />
            <circle class="node" cx="320" cy="218" r="11" />
            <rect class="halo" x="160" y="200" width="120" height="38" rx="19" />
            <rect class="node-you" x="166" y="204" width="108" height="30" rx="15" />
            <text class="t-you" x="220" y="223" data-fpr-tree="you">{{ $treeYou }}</text>
        </svg>

        <ul class="fpr-points">
            <li><span class="fpr-ic"><i class="fas fa-user-shield"></i></span><span>{{ __('reg.point1', 'Relatives join with your family code, and you approve who gets in.') }}</span></li>
            <li><span class="fpr-ic"><i class="fas fa-sitemap"></i></span><span>{{ __('reg.point2', 'Build the tree together and keep everyone\'s stories in one place.') }}</span></li>
            <li><span class="fpr-ic"><i class="fas fa-mobile-alt"></i></span><span>{{ __('reg.point3', 'Works on phone, tablet and computer.') }}</span></li>
        </ul>
    </aside>

    {{-- ===== Form panel ===== --}}
    <main class="fpr-main">
        <div class="fpr-head">
            <div>
                <h1 class="fpr-title">{{ __('reg.title', 'Create your account') }}</h1>
                <p class="fpr-sub">{{ __('reg.subtitle', 'Three short steps.') }}</p>
            </div>
            @if(!empty($supportedLocales) && count($supportedLocales) > 1)
            <div class="fpr-lang dropdown">
                <button type="button" class="fpr-lang-btn" id="fprLangBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('reg.change_language', 'Change language') }}">
                    <span class="fpr-ic" aria-hidden="true"><i class="fas fa-globe"></i></span><span>{{ $currCode }}</span><span class="fpr-ic" style="font-size:0.7rem;" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="fprLangBtn">
                    @foreach ($supportedLocales as $locKey => $locMeta)
                    <li>
                        <a class="dropdown-item {{ $locKey === $activeLocale ? 'active' : '' }}" href="/locale/{{ $locKey }}" lang="{{ str_replace('_', '-', (string) $locKey) }}" @if($locKey === $activeLocale) aria-current="true" @endif>
                            <span aria-hidden="true">{{ $locMeta['flag'] }}</span>
                            <span>{{ $locMeta['native'] }}</span>
                            @if($locKey === $activeLocale)<span class="fpr-ic" aria-hidden="true"><i class="fas fa-check fpr-check"></i></span>@endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <ol class="fpr-steps" id="fprStepper" aria-label="{{ __('reg.progress', 'Sign-up progress') }}">
            <li data-step="1" data-state="current"><span><span class="n">1</span>{{ __('reg.step1', 'Your family') }}</span></li>
            <li data-step="2" data-state="todo"><span><span class="n">2</span>{{ __('reg.step2', 'About you') }}</span></li>
            <li data-step="3" data-state="todo"><span><span class="n">3</span>{{ __('reg.step3', 'Secure account') }}</span></li>
        </ol>

        @if ($hasInvitation)
        <div class="fpr-welcome" role="status">
            <span class="fpr-welcome-icon" aria-hidden="true"><span class="fpr-ic"><i class="fas fa-tree"></i></span></span>
            <div class="fpr-welcome-body">
                <p class="fpr-welcome-eyebrow">{{ __('reg.welcome_eyebrow', 'Family invitation') }}</p>
                <h2 class="fpr-welcome-title">{{ $welcomeTitle }}</h2>
                <p class="fpr-welcome-text">{{ $welcomeText }}</p>
                @if($inviteFamCode !== '')
                <p class="fpr-welcome-chip"><span class="fpr-ic"><i class="fas fa-check-circle"></i></span> {{ __('reg.family_code', 'Family code') }} <strong>{{ $inviteFamCode }}</strong></p>
                @endif
            </div>
        </div>
        @endif

        @if (!empty($inviteTokenError))
        <div class="fpr-note fpr-note--error" role="alert">
            <span class="fpr-ic"><i class="fas fa-exclamation-triangle"></i></span>
            <div>
                <strong class="d-block">{{ __('reg.invite_issue', 'There\'s a problem with your invite link') }}</strong>
                <span class="d-block">{{ $inviteTokenError }}</span>
                <span class="d-block" style="margin-top:4px;">{{ __('reg.invite_issue_help', 'Ask your relative for a new link, or enter your family code yourself below.') }}</span>
            </div>
        </div>
        @endif

        @include('partials.loader', ['notificationId'=> 'register'])

        @if (!$isOauth)
        <!-- Webview Alert for WhatsApp / Facebook / Instagram in-app browsers -->
        <div id="webview-alert" class="fpr-note fpr-note--warn" style="display: none;">
            <i class="fas fa-external-link-alt"></i>
            <div>
                <strong>Browsing inside WhatsApp or Facebook?</strong>
                <span class="d-block" style="font-size: 0.85rem;">For fastest 1-tap sign-in with Google, tap the menu (<strong>⋮</strong> or <strong>⋯</strong>) and choose <strong>"Open in Browser"</strong>.</span>
            </div>
        </div>
        <script>
            (function() {
                const ua = navigator.userAgent || navigator.vendor || window.opera;
                if (ua && (ua.includes('FBAN') || ua.includes('FBAV') || ua.includes('Instagram') || ua.includes('WhatsApp'))) {
                    const el = document.getElementById('webview-alert');
                    if (el) el.style.display = 'flex';
                }
            })();
        </script>

        <div class="fpr-social" data-fpr-social>
            <a href="/auth/google{{ $oauthQueryStr }}">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                </svg>
                <span>Google</span>
            </a>
            <a href="/auth/apple{{ $oauthQueryStr }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.85c.65-.8 1.1-1.92.97-3.05-1 .04-2.16.67-2.84 1.47-.6.69-1.12 1.82-.98 2.92 1.12.09 2.21-.55 2.85-1.34z"/>
                </svg>
                <span>Apple</span>
            </a>
            <a href="/auth/facebook{{ $oauthQueryStr }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Facebook</span>
            </a>
        </div>
        <div class="fpr-or" data-fpr-social><span>{{ __('reg.or_email', 'or sign up with email') }}</span></div>
        @else
        <div class="fpr-note fpr-note--info">
            <span class="fpr-ic"><i class="fas fa-info-circle"></i></span>
            <span><strong>{{ __('reg.oauth_almost', 'Almost done!') }}</strong> {{ __('reg.oauth_note', 'Add your family details and mobile number to finish signing up.') }}</span>
        </div>
        @endif

        <form class="register" id="register" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate x-data="familyCodeApprovalForm()">
            @if(!empty($registerPostData['claim_node']))
            <input type="hidden" name="claim_node" value="{{ (int)$registerPostData['claim_node'] }}">
            @endif

            {{-- ============ STEP 1 — Your family ============ --}}
            <section class="fpr-step" data-fpr-step="1" aria-labelledby="fprStep1Title">
                <h2 class="fpr-step-title" id="fprStep1Title">{{ __('auth.what_would_you_like_to_do', 'What would you like to do?') }}</h2>
                <p class="fpr-step-sub">{{ __('reg.s1_sub', 'Join relatives who are already here, or start your family\'s space.') }}</p>

                <fieldset>
                    <legend class="visually-hidden">{{ __('reg.choose', 'Choose how to sign up') }}</legend>
                    <div class="fpr-choices">
                        <label class="fpr-choice">
                            <input type="radio" name="account_type" value="join" {{ $hasInvitation ? 'checked' : '' }}>
                            <span class="fpr-choice-body">
                                <span class="fpr-choice-icon"><span class="fpr-ic"><i class="fas fa-users"></i></span></span>
                                <span class="fpr-choice-title">{{ __('reg.join_title', 'Join my family') }}</span>
                                <span class="fpr-choice-text">{{ __('reg.join_text', 'A relative gave me a family code.') }}</span>
                            </span>
                            <span class="fpr-choice-tick" aria-hidden="true"><span class="fpr-ic"><i class="fas fa-check"></i></span></span>
                        </label>
                        <label class="fpr-choice">
                            <input type="radio" name="account_type" value="create" {{ $hasInvitation ? '' : 'checked' }}>
                            <span class="fpr-choice-body">
                                <span class="fpr-choice-icon"><span class="fpr-ic"><i class="fas fa-tree"></i></span></span>
                                <span class="fpr-choice-title">{{ __('reg.create_title', 'Start a new family') }}</span>
                                <span class="fpr-choice-text">{{ __('reg.create_text', 'We\'ll create a code you can share.') }}</span>
                            </span>
                            <span class="fpr-choice-tick" aria-hidden="true"><span class="fpr-ic"><i class="fas fa-check"></i></span></span>
                        </label>
                    </div>
                </fieldset>

                {{-- Join: family code + inline inviter check --}}
                <div class="fpr-grid" data-fpr-mode="join">
                    <div class="fpr-field" id="famCode_div">
                        <label class="fpr-label" for="famCode">{{ __('reg.family_code', 'Family code') }}</label>
                        <input class="fpr-input fpr-code" type="text" name="famCode" id="famCode" value="{{ $fv('famCode') }}" placeholder="e.g. OLA1234" maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false" aria-describedby="famCode_hint famCode_error">
                        <p class="fpr-hint" id="famCode_hint">{{ __('reg.family_code_hint', 'Ask the relative who invited you. It\'s in their invite message too.') }}</p>
                        <div class="fpr-status fpr-status--busy" x-show="checkingCode" x-cloak><span class="spinner-border spinner-border-sm" aria-hidden="true"></span> {{ __('reg.checking', 'Checking code…') }}</div>
                        <div class="fpr-status fpr-status--ok" x-show="!checkingCode && codeExists && codeVerified" x-cloak><span class="fpr-ic"><i class="fas fa-check-circle"></i></span> {{ __('reg.code_found', 'Family found') }}</div>
                        <div class="fpr-status fpr-status--warn" x-show="!checkingCode && lastCheckedCode && !codeExists" x-cloak><span class="fpr-ic"><i class="fas fa-exclamation-circle"></i></span> {{ __('reg.code_not_found', 'We couldn\'t find that code. You can still sign up, then link to your family\'s right code afterwards.') }}</div>
                        <p class="fpr-err" id="famCode_error" role="alert"></p>
                    </div>

                    @include('registration.include.inviterCheck')
                </div>

                {{-- Create: family surname --}}
                <div class="fpr-grid" data-fpr-mode="create">
                    <div class="fpr-field" id="familySurname_div">
                        <label class="fpr-label" for="familySurname">{{ __('reg.family_surname', 'Family surname') }}</label>
                        <input class="fpr-input" type="text" name="familySurname" id="familySurname" value="{{ $fv('familySurname') }}" placeholder="e.g. Olaogun" maxlength="30" autocomplete="family-name" aria-describedby="familySurname_hint familySurname_error">
                        <p class="fpr-hint" id="familySurname_hint">{{ __('reg.family_surname_hint', 'This names your family space. We\'ll create a family code from it after you sign up.') }}</p>
                        <p class="fpr-err" id="familySurname_error" role="alert"></p>
                    </div>
                </div>
            </section>

            {{-- ============ STEP 2 — About you ============ --}}
            <section class="fpr-step" data-fpr-step="2" aria-labelledby="fprStep2Title" hidden>
                <h2 class="fpr-step-title" id="fprStep2Title">{{ __('reg.step2', 'About you') }}</h2>
                <p class="fpr-step-sub">{{ __('reg.s2_sub', 'This is how relatives will recognise you on the tree.') }}</p>

                <div class="fpr-grid">
                    <div class="fpr-row-2">
                        <div class="fpr-field">
                            <label class="fpr-label" for="firstName">{{ __('reg.first_name', 'First name') }}</label>
                            <input class="fpr-input" type="text" name="firstName" id="firstName" value="{{ $fv('firstName') }}" placeholder="Toyin" maxlength="35" autocomplete="given-name" aria-describedby="firstName_error">
                            <p class="fpr-err" id="firstName_error" role="alert"></p><span id="firstName_help" hidden></span>
                        </div>
                        <div class="fpr-field">
                            <label class="fpr-label" for="lastName">{{ __('reg.last_name', 'Last name') }}</label>
                            <input class="fpr-input" type="text" name="lastName" id="lastName" value="{{ $fv('lastName') }}" placeholder="Edwards" maxlength="35" autocomplete="family-name" aria-describedby="lastName_error">
                            <p class="fpr-err" id="lastName_error" role="alert"></p><span id="lastName_help" hidden></span>
                        </div>
                    </div>

                    <fieldset class="fpr-field" aria-describedby="Date_of_Birth_hint Date_of_Birth_error">
                        <legend class="fpr-label" style="margin-bottom:6px;">{{ __('reg.dob', 'Date of birth') }}</legend>
                        <div class="fpr-dob">
                            <select class="fpr-select" name="day" id="day" aria-label="{{ __('reg.day', 'Day') }}" autocomplete="bday-day">
                                <option value="select">{{ __('reg.day', 'Day') }}</option>
                                @for ($d = 1; $d <= 31; $d++)
                                <option value="{{ $d }}" {{ $selDay === (string) $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endfor
                            </select>
                            <select class="fpr-select" name="month" id="month" aria-label="{{ __('reg.month', 'Month') }}" autocomplete="bday-month">
                                <option value="select">{{ __('reg.month', 'Month') }}</option>
                                @foreach ($months as $mKey => $mName)
                                <option value="{{ $mKey }}" {{ $selMonth === $mKey ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                            <select class="fpr-select" name="year" id="year" aria-label="{{ __('reg.year', 'Year') }}" autocomplete="bday-year">
                                <option value="select">{{ __('reg.year', 'Year') }}</option>
                                @for ($y = $maxYear; $y >= 1920; $y--)
                                <option value="{{ $y }}" {{ $selYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <p class="fpr-hint" id="Date_of_Birth_hint">{{ __('reg.dob_hint', 'You need to be 13 or over to sign up yourself.') }}</p>
                        <p class="fpr-err" id="Date_of_Birth_error" role="alert"></p>
                        <p class="fpr-err" id="day_error"></p>
                        <p class="fpr-err" id="month_error"></p>
                        <p class="fpr-err" id="year_error"></p>
                    </fieldset>

                    <div class="fpr-field">
                        <label class="fpr-label" for="gender">{{ __('reg.gender', 'Gender') }}</label>
                        <select class="fpr-select" name="gender" id="gender" aria-describedby="gender_error" autocomplete="sex">
                            <option value="">{{ __('reg.select_gender', 'Select gender') }}</option>
                            <option value="Female" {{ $selGender === 'Female' ? 'selected' : '' }}>{{ __('reg.female', 'Female') }}</option>
                            <option value="Male" {{ $selGender === 'Male' ? 'selected' : '' }}>{{ __('reg.male', 'Male') }}</option>
                            <option value="Other" {{ $selGender === 'Other' ? 'selected' : '' }}>{{ __('reg.other', 'Other') }}</option>
                        </select>
                        <p class="fpr-err" id="gender_error" role="alert"></p>
                    </div>

                    <div class="fpr-field">
                        <label class="fpr-label" for="email">{{ __('reg.email', 'Email') }}</label>
                        <input class="fpr-input" type="email" name="email" id="email" value="{{ $fv('email') }}" placeholder="{{ __('auth.email_placeholder', 'e.g. alex@example.com') }}" maxlength="50" autocomplete="email" inputmode="email" aria-describedby="email_error">
                        <p class="fpr-err" id="email_error" role="alert"></p><span id="email_help" hidden></span>
                    </div>

                    <div class="fpr-field">
                        <span class="fpr-label" id="fprPhoneLabel">{{ __('reg.mobile', 'Mobile number') }}</span>
                        <div class="fpr-phone">
                            <select class="fpr-select" name="country" id="country" aria-labelledby="fprPhoneLabel" aria-label="{{ __('reg.country', 'Country') }}" autocomplete="country-name">
                                @foreach ($countries as $c)
                                <option value="{{ $c }}" {{ $selCountry === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                            <input class="fpr-input" type="tel" name="mobile" id="mobile" value="{{ $fv('mobile') }}" placeholder="7123 456789" maxlength="25" autocomplete="tel" inputmode="tel" aria-label="{{ __('reg.mobile', 'Mobile number') }}" aria-describedby="mobile_hint mobile_error">
                        </div>
                        <p class="fpr-hint" id="mobile_hint">{{ __('reg.mobile_hint', 'Pick your country and we\'ll add the dialling code.') }}</p>
                        <p class="fpr-err" id="country_error" role="alert"></p><span id="country_help" hidden></span>
                        <p class="fpr-err" id="mobile_error" role="alert"></p><span id="mobile_help" hidden></span>
                    </div>
                </div>
            </section>

            {{-- ============ STEP 3 — Secure account ============ --}}
            <section class="fpr-step" data-fpr-step="3" aria-labelledby="fprStep3Title" hidden>
                <h2 class="fpr-step-title" id="fprStep3Title">{{ $s3Title }}</h2>
                <p class="fpr-step-sub">{{ $s3Sub }}</p>

                <div class="fpr-grid">
                    @if (!$isOauth)
                    <div class="fpr-field">
                        <label class="fpr-label" for="password">{{ __('reg.password', 'Password') }}</label>
                        <div class="fpr-pw">
                            <input class="fpr-input" type="password" name="password" id="password" value="{{ $fv('password') }}" placeholder="{{ __('reg.pw_placeholder', 'At least 8 characters') }}" maxlength="50" autocomplete="new-password" aria-describedby="fprPwRules password_error">
                            <button type="button" class="fpr-pw-toggle" id="fprPwToggle" aria-controls="password" aria-pressed="false">{{ __('reg.show', 'Show') }}</button>
                        </div>
                        <div class="fpr-meter" id="fprMeter" data-score="0" aria-hidden="true"><span></span></div>
                        <ul class="fpr-rules" id="fprPwRules" aria-live="polite">
                            <li data-rule="len">{{ __('reg.rule_len', '8+ characters') }}</li>
                            <li data-rule="case">{{ __('reg.rule_case', 'Upper & lower case') }}</li>
                            <li data-rule="num">{{ __('reg.rule_num', 'A number') }}</li>
                        </ul>
                        <p class="fpr-err" id="password_error" role="alert"></p>
                        {{-- Kept for the server contract; mirrors the password (no second box to type). --}}
                        <input type="hidden" name="confirm_password" id="confirm_password" value="{{ $fv('confirm_password') }}">
                        <p class="fpr-err" id="confirm_password_error" role="alert"></p>
                    </div>
                    @else
                    <input type="hidden" name="password" value="{{ $fv('password') }}">
                    <input type="hidden" name="confirm_password" value="{{ $fv('confirm_password') }}">
                    @endif

                    <label class="fpr-consent" for="checkbox">
                        <input type="checkbox" name="checkbox" id="checkbox" required>
                        <span>{{ __('reg.consent', 'I agree to FamilyPlatform handling my information as set out in the') }} <a href="/privacy" target="_blank" rel="noopener">{{ __('reg.privacy_policy', 'privacy policy') }}</a>.</span>
                    </label>
                    <p class="fpr-err" id="checkbox_error" role="alert"></p>
                </div>
            </section>

            <!-- Hidden inputs to track approval state. joining_via_invitation
                 must reflect a *verified* invitation, not merely that the
                 code exists, so the server creates the approval request. -->
            <input type="hidden" id="joining_via_invitation" :value="codeVerified ? 'true' : 'false'" name="joining_via_invitation">
            <input type="hidden" id="temporary_code" name="temporary_code" x-model="temporaryCode">

            {{-- Zero-Friction Honeypot Trap --}}
            <div style="display: none !important; position: absolute !important; left: -9999px !important;" aria-hidden="true">
                <input type="text" name="website_hp" tabindex="-1" autocomplete="off" value="">
            </div>

            <p class="fpr-note fpr-note--error" id="fprFormError" role="alert" hidden></p>
            <div class="fpr-nav">
                <button type="button" class="fpr-btn fpr-btn--ghost" id="fprBack" hidden><span class="fpr-ic" aria-hidden="true"><i class="fas fa-arrow-left"></i></span> {{ __('reg.back', 'Back') }}</button>
                <button type="button" class="fpr-btn fpr-btn--primary" id="fprNext">{{ __('reg.continue', 'Continue') }} <span class="fpr-ic" aria-hidden="true"><i class="fas fa-arrow-right"></i></span></button>
                <button type="submit" name="submit" id="btnSubmit" data-ready="true" class="fpr-btn fpr-btn--primary" hidden>{{ __('reg.submit', 'Create my account') }} <span class="fpr-ic" aria-hidden="true"><i class="fas fa-check"></i></span></button>
            </div>
            <input type="hidden" name="token" id="token" value="{{ $csrfValue }}">
        </form>

        <p class="fpr-foot">{{ __('reg.have_account', 'Already have an account?') }} <a href="/login">{{ __('reg.log_in', 'Log in') }}</a></p>
    </main>
</div>
</div>

{{-- Legacy "Generate family code" modal. No longer linked from the page (new
     families get their code from the server on sign-up), but familyCode.js
     binds to #btnFamCode / #copyIcon unguarded, so the markup must stay. --}}
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
    </div>
    <button type="button" id="modal-close-code" class="modal-close is-large" aria-label="close"></button>
</div>

@if (getenv('APP_ENV') === 'development' || getenv('APP_ENV') === 'local')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const termsCheckbox = document.getElementById('checkbox');
        if (termsCheckbox && @json(!empty($registerPostData['password']))) termsCheckbox.checked = true;
    });
</script>
@endif

@include('registration.include.registerSteps')
@endsection
