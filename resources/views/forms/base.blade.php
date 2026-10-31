<!DOCTYPE html>
<html lang="{{ \App\classes\I18n::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">

    <title>@yield('title') | FamilyPlatform</title>
    <meta name="description" content="@yield('meta_description', 'Private family social network, heritage family tree, and secure digital family memory sanctuary.')">
    <meta name="keywords" content="family platform, heritage tree, family social network, private family memories, family archive">
    <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">

    <script nonce="{{ $nonce }}">
        window.APP_LOCALE = '{{ \App\classes\I18n::getLocale() }}';
        window.__TRANSLATIONS__ = {!! json_encode(\App\classes\I18n::transArray()) !!};
        window.__ = function(k, fb) { return (window.__TRANSLATIONS__ && window.__TRANSLATIONS__[k]) || fb || k; };
        window.trans = window.__;
    </script>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Standards v2.1 CSS -->
    <link rel="stylesheet" type="text/css" href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ getenv('APP_LOGO') ?: '/public/img/logo/logo.png' }}">

    @include('partials.pwa-head')

    <style nonce="{{ $nonce }}">
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap');

        [x-cloak] {
            display: none !important;
        }

        :root {
            --brand-primary: #a31621;
            --brand-primary-hover: #b8202c;
            --brand-primary-dark: #8a1019;
            --brand-accent-glow: rgba(163, 22, 33, 0.25);
            --auth-canvas: #030712;
            --auth-card-bg: rgba(15, 23, 42, 0.7);
            --auth-input-bg: #ffffff;
            --auth-input-text: #0f172a;
            --auth-border: rgba(255, 255, 255, 0.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            background-color: var(--auth-canvas) !important;
            color: #ffffff !important;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .brand-font {
            font-family: 'Outfit', 'Plus Jakarta Sans', system-ui, sans-serif !important;
        }

        .text-brand {
            color: #ef4444 !important;
        }

        .text-brand-hover {
            color: #ef4444 !important;
            text-decoration: none;
            transition: color 0.18s ease;
        }

        .text-brand-hover:hover {
            color: #f87171 !important;
            text-decoration: underline;
        }

        /* Clean form styling overrides */
        .styleForm, .styleform_form {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .form-label {
            color: #94a3b8 !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase;
        }

        /* Input group custom style (High contrast white surface on dark canvas) */
        .input-group {
            background-color: var(--auth-input-bg) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            transition: all 0.2s ease-in-out !important;
            overflow: hidden !important;
            display: flex;
            align-items: center;
        }

        .input-group:focus-within {
            border-color: var(--brand-primary) !important;
            box-shadow: 0 0 0 4px var(--brand-accent-glow) !important;
        }

        .input-group-text {
            background-color: transparent !important;
            border: none !important;
            color: #94a3b8 !important;
            padding-left: 1.15rem !important;
            padding-right: 0.65rem !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .form-control {
            background-color: transparent !important;
            border: none !important;
            color: var(--auth-input-text) !important;
            padding: 0.85rem 1.15rem 0.85rem 0.5rem !important;
            font-size: 0.95rem !important;
            font-weight: 500;
        }

        .form-control:focus {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: var(--auth-input-text) !important;
        }

        .form-control::placeholder {
            color: #94a3b8 !important;
        }

        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 0.85rem 1.5rem !important;
            font-weight: 600 !important;
            color: #ffffff !important;
            min-height: 48px;
            white-space: nowrap;
            flex-shrink: 0;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(163, 22, 33, 0.35);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--brand-primary-hover) 0%, var(--brand-primary) 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(163, 22, 33, 0.45);
        }

        .btn-primary:active {
            transform: scale(0.98) !important;
        }

        /* Outline Google & Social Buttons */
        .btn-outline-social {
            background-color: rgba(17, 24, 39, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            padding: 0.75rem 1.25rem !important;
            min-height: 44px;
            white-space: nowrap;
            flex-shrink: 0;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.65rem;
            transition: all 0.18s ease-in-out !important;
            width: 100% !important;
            text-decoration: none !important;
        }

        .btn-outline-social:hover {
            background-color: rgba(31, 41, 55, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .btn-outline-social:active {
            transform: scale(0.98);
        }

        .btn-outline-primary-brand {
            background-color: rgba(163, 22, 33, 0.12) !important;
            border: 1.5px solid rgba(163, 22, 33, 0.45) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            min-height: 44px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.18s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .btn-outline-primary-brand:hover {
            background-color: rgba(163, 22, 33, 0.22) !important;
            border-color: var(--brand-primary) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Divider with text */
        .divider-text {
            position: relative;
            text-align: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .divider-text::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.12);
            z-index: 1;
        }

        .divider-text span {
            position: relative;
            background-color: var(--auth-canvas);
            padding: 0 1rem;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            z-index: 2;
            text-transform: uppercase;
        }

        /* Form Check */
        .form-check-input {
            background-color: rgba(17, 24, 39, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 4px !important;
            margin-top: 0.2em;
        }

        .form-check-input:checked {
            background-color: var(--brand-primary) !important;
            border-color: var(--brand-primary) !important;
        }

        .form-check-label {
            color: #94a3b8 !important;
            font-size: 0.85rem;
            cursor: pointer;
        }

        /* Password toggle button */
        .password-toggle-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding-right: 1.15rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        .hover-white:hover {
            color: #ffffff !important;
        }

        /* Left hero branding styles */
        .hero-feature-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            backdrop-filter: blur(12px);
            transition: all 0.25s ease;
        }

        .hero-feature-card:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.18);
            transform: translateY(-2px);
        }

        /* Accessible keyboard focus */
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible, [tabindex]:focus-visible {
            outline: 2px solid #ef4444;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    @yield('extra_css')
</head>

<body data-page-id="@yield('data-page-id')">
    @include('partials.pwa-splash')

    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <!-- Left Pane: Atmospheric Brand Sanctuary & Value Props (Visible on Large Screens) -->
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 position-relative overflow-hidden" 
                 style="background: radial-gradient(circle at 25% 25%, #23080e 0%, #0d0a14 55%, #030712 100%); min-height: 100vh;">
                
                <!-- Atmospheric ruby radial aura -->
                <div class="position-absolute" 
                     style="top: -180px; left: -180px; width: 620px; height: 620px; background: radial-gradient(circle, rgba(163, 22, 33, 0.22) 0%, rgba(163, 22, 33, 0) 70%); pointer-events: none;">
                </div>
                <div class="position-absolute" 
                     style="bottom: -220px; right: -220px; width: 550px; height: 550px; background: radial-gradient(circle, rgba(184, 32, 44, 0.12) 0%, rgba(184, 32, 44, 0) 70%); pointer-events: none;">
                </div>

                <!-- Top Brand Header -->
                <div class="z-1 d-flex align-items-center justify-content-between">
                    <a href="/" class="d-inline-flex align-items-center text-decoration-none text-white gap-2">
                        <img src="{{ getenv('APP_LOGO') ?: '/public/img/logo/logo-mark.png' }}" width="42" height="42" alt="FamilyPlatform logo" class="flex-shrink-0" style="border-radius: 12px; box-shadow: 0 4px 18px rgba(163, 22, 33, 0.45);">
                        <span class="fs-4 fw-bold brand-font tracking-tight">FamilyPlatform</span>
                    </a>
                    <span class="badge rounded-pill px-3 py-2 text-white" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); font-size: 0.75rem; letter-spacing: 0.05em;">
                        <i class="fas fa-lock me-1 text-danger"></i> ENCRYPTED SANCTUARY
                    </span>
                </div>

                <!-- Middle Content: Brand Hero & Value Matrix -->
                <div class="z-1 my-auto py-4" style="max-width: 520px;">
                    <div class="mb-4">
                        <span class="text-uppercase fw-bold text-danger small tracking-wider mb-2 d-inline-block">Private Generational Vault</span>
                        <h1 class="display-6 fw-bold text-white mb-3 brand-font" style="line-height: 1.15; letter-spacing: -0.02em;">
                            Preserve your heritage, cherish every family moment.
                        </h1>
                        <p class="text-secondary mb-4" style="color: #94a3b8 !important; font-size: 1.02rem; line-height: 1.6;">
                            Connect across generations with interactive family lineage trees, private encrypted messaging, and shared heirloom photo archives.
                        </p>
                    </div>

                    <!-- Highlight Cards -->
                    <div class="d-flex flex-column gap-3">
                        <div class="hero-feature-card d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(163, 22, 33, 0.2); color: #f87171;">
                                <i class="fas fa-sitemap fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0" style="font-size: 0.95rem;">Interactive Family Tree Visualiser</h6>
                                <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Map complex relationships, ancestral roots, and multi-generational lineages.</p>
                            </div>
                        </div>

                        <div class="hero-feature-card d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(163, 22, 33, 0.2); color: #f87171;">
                                <i class="fas fa-shield-halved fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0" style="font-size: 0.95rem;">100% Private &amp; Zero Ad-Tracking</h6>
                                <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Your family stories, photos, and messages are strictly confidential.</p>
                            </div>
                        </div>

                        <div class="hero-feature-card d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(163, 22, 33, 0.2); color: #f87171;">
                                <i class="fas fa-images fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0" style="font-size: 0.95rem;">Heirloom Memory &amp; Event Archive</h6>
                                <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Chronicle weddings, milestones, and family journals for generations to come.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Stats -->
                <div class="z-1 row g-4 border-top border-secondary border-opacity-25 pt-4">
                    <div class="col-4">
                        <h4 class="fw-bold text-white mb-1 brand-font">AES-256</h4>
                        <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Vault Encryption</p>
                    </div>
                    <div class="col-4 border-start border-secondary border-opacity-25 ps-4">
                        <h4 class="fw-bold text-white mb-1 brand-font">100%</h4>
                        <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Ad-Free Privacy</p>
                    </div>
                    <div class="col-4 border-start border-secondary border-opacity-25 ps-4">
                        <h4 class="fw-bold text-white mb-1 brand-font">Heritage</h4>
                        <p class="small text-secondary mb-0" style="color: #94a3b8 !important;">Generational Care</p>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Authentication Form Canvas -->
            <div class="col-lg-6 col-12 d-flex flex-column justify-content-between p-4 p-md-5" 
                 style="background-color: var(--auth-canvas); min-height: 100vh;">
                
                <!-- Top Brand Header (Visible only on mobile/tablet) -->
                <div class="d-lg-none mb-3 text-center mt-2">
                    <a class="d-inline-flex align-items-center text-decoration-none text-white fs-4 fw-bold brand-font" href="/">
                        <img src="{{ getenv('APP_LOGO') ?: '/public/img/logo/logo-mark.png' }}" width="40" height="40" alt="FamilyPlatform logo" class="me-2 flex-shrink-0" style="border-radius: 10px; box-shadow: 0 4px 14px rgba(163, 22, 33, 0.45);">
                        <span>FamilyPlatform</span>
                    </a>
                </div>

                <!-- Main Form Wrapper -->
                <div class="my-auto mx-auto w-100" style="max-width: 440px; padding: 1.25rem 0;">
                    @yield('content')
                </div>

                <!-- Footer (Inside the Right Pane) -->
                <div class="text-center w-100 pt-4 border-top border-secondary border-opacity-10">
                    <p class="small text-secondary mb-0" style="color: #94a3b8 !important; font-size: 0.8rem;">
                        By continuing, you agree to our <a href="/terms" class="text-secondary text-decoration-underline hover-white">Terms of Service</a> and <a href="/privacy" class="text-secondary text-decoration-underline hover-white">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App Scripts (Bundles Alpine.js 3.x, PWA, SweetAlert2) -->
    <script type="text/javascript" src="/public/js/manifest.js?v=1.0.2"></script>
    <script type="text/javascript" src="/public/js/vendor.js?v=1.0.2"></script>
    <script type="text/javascript" src="/public/js/index.js?v=1.0.2"></script>

    @include('component.submitSpinner')
    @include('partials.pwa-register')

    @if(($_ENV['APP_ENV'] ?? '') !== 'local')
    <script nonce="{{ $nonce }}">
        window.RECAPTCHA_SITE_KEY = "{{ $_ENV['RECAPTCHA_SITE_KEY'] ?? '' }}";
    </script>
    @endif

    @yield('extra_js')
</body>
</html>