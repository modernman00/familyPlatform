<!doctype html>
<html lang="{{ \App\classes\I18n::getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover">
  <meta name="description" content="@yield('meta_description', 'Private family social network and interactive family tree. Safely connect relatives, coordinate private chats, share cherished memories, and build your digital family archive.')">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Family Platform Team">
  <meta name="language" content="{{ \App\classes\I18n::getLocale() }}">
  <script>
    window.APP_LOCALE = '{{ \App\classes\I18n::getLocale() }}';
    window.__TRANSLATIONS__ = {!! json_encode(\App\classes\I18n::transArray()) !!};
    window.__ = function(k, fb) { return (window.__TRANSLATIONS__ && window.__TRANSLATIONS__[k]) || fb || k; };
    window.trans = window.__;
  </script>
  <title>@yield('title', 'FamilyPlatform | Private Family Social Network')</title>
  <link rel="canonical" href="{{ rtrim($_ENV['APP_URL'] ?? 'https://myfamilyplatform.com', '/') . ($_SERVER['REQUEST_URI'] ?? '/') }}">
  
  <!-- JSON-LD Structured Data Schema (Google Sitelinks) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "WebApplication",
        "name": "FamilyPlatform",
        "url": "https://myfamilyplatform.com",
        "applicationCategory": "SocialNetworkingApplication",
        "operatingSystem": "All",
        "description": "Private family social network, heritage family tree, and secure digital family memory sanctuary."
      },
      {
        "@type": "WebSite",
        "@id": "https://myfamilyplatform.com/#website",
        "name": "FamilyPlatform",
        "url": "https://myfamilyplatform.com"
      },
      {
        "@type": "SiteNavigationElement",
        "name": "Home",
        "url": "https://myfamilyplatform.com/"
      },
      {
        "@type": "SiteNavigationElement",
        "name": "About Us",
        "url": "https://myfamilyplatform.com/aboutus"
      },
      {
        "@type": "SiteNavigationElement",
        "name": "Blog",
        "url": "https://myfamilyplatform.com/blogs"
      },
      {
        "@type": "SiteNavigationElement",
        "name": "Contact",
        "url": "https://myfamilyplatform.com/contact"
      }
    ]
  }
  </script>
  @yield('extra_schema')
  <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">

  @php
    $appUrl = rtrim($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', '/');
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $ogUrl = $appUrl . $requestUri;
    $defaultOgImage = $appUrl . '/public/img/og-invite.jpg';
  @endphp
  <!-- OpenGraph & Social Sharing Meta Tags -->
  <meta property="fb:app_id" content="{{ $_ENV['FACEBOOK_APP_ID'] ?? $_ENV['FB_APP_ID'] ?? '' }}">
  <meta property="og:site_name" content="Family Platform">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('og_title', 'Family Platform')">
  <meta property="og:description" content="@yield('og_description', 'Connect with relatives, explore your family tree, and preserve cherished memories together.')">
  <meta property="og:image" content="@yield('og_image', $defaultOgImage)">
  <meta property="og:image:secure_url" content="@yield('og_image', $defaultOgImage)">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:url" content="@yield('og_url', $ogUrl)">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('og_title', 'Family Platform')">
  <meta name="twitter:description" content="@yield('og_description', 'Connect with relatives, explore your family tree, and preserve cherished memories together.')">
  <meta name="twitter:image" content="@yield('og_image', $defaultOgImage)">
  @yield('meta_tags')
  
  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ getenv('APP_LOGO') }}">
  
  <!-- PWA Manifest & Mobile Meta -->
  @include('partials.pwa-head')
  
  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/landing.css" rel="stylesheet">
  <link href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}" rel="stylesheet">
  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
  
@if(($_ENV['APP_ENV'] ?? '') !== 'local')
<script nonce="{{ $nonce }}" src="https://www.google.com/recaptcha/enterprise.js?render={{ $_ENV['RECAPTCHA_SITE_KEY'] }}"></script>
<script nonce="{{ $nonce }}">window.RECAPTCHA_SITE_KEY = "{{ $_ENV['RECAPTCHA_SITE_KEY'] }}";</script>
@endif

  <style>
    /* Reset some Bulma conflicts with Bootstrap */
    .navbar { background-color: transparent; }
    .container { max-width: 1320px !important; }
    .button { height: auto; }
    .input { height: auto; padding: 0.75rem 1rem; border-radius: 8px; }
    .section { padding: 3rem 1.5rem; }
    
    /* Auth Page specific overrides */
    .auth-card {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        margin-bottom: 4rem;
    }
    
    .auth-title {
        color: var(--brand-secondary);
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    
    .auth-subtitle {
        color: var(--text-muted);
        margin-bottom: 2.5rem;
    }

    /* Style Bulma form elements to match the theme */
    .label { color: var(--text-main); font-weight: 600; margin-bottom: 0.5rem; }
    .input:focus { border-color: var(--brand-primary); box-shadow: 0 0 0 0.25rem rgba(10, 102, 194, 0.1); }
    .button.is-success, .button.is-primary { background-color: var(--brand-primary) !important; border-radius: 24px; font-weight: 600; transition: all 0.2s ease; }
    .button.is-success:hover, .button.is-primary:hover { background-color: var(--brand-secondary) !important; transform: translateY(-1px); }

     .noDisplay {
        display: none;
    }


        .loader {
            border: 16px solid #11e11b79;
            border-radius: 50%;
            border-top: 16px solid #2092ddf3;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }


        @keyframes spinner-grow {
            0% {
                transform: scale(0);
            }
            50% {
                opacity: 1;
                transform: none;
            }
        }

        .spinner-grow {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            vertical-align: -0.125em;
            background-color: currentColor;
            border-radius: 50%;
            opacity: 0;
            animation: 0.75s linear infinite spinner-grow;
        }

        .spinner-grow-sm {
            width: 1rem;
            height: 1rem;
        }

        @media (prefers-reduced-motion: reduce) {
            .spinner-border,
            .spinner-grow {
                animation-duration: 1.5s;
            }
        }
  </style>
  
  @yield('extra_css')
</head>

<body data-page-id="@yield('data-page-id')">
  
  @include('includes.public_nav')

  <div class="content-wrapper" style="padding-top: calc(80px + env(safe-area-inset-top, 0px));">
    @yield('content')
  </div>

  @include('includes.public_footer')

  @include('partials.cookie-banner')

  @include('partials.back-to-top')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- SweetAlert2 for notifications -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- App JS (Bundles Alpine.js, PWA, SweetAlert2) -->
  <script type="text/javascript" src="/public/js/manifest.js?v=1.0.2"></script>
  <script type="text/javascript" src="/public/js/vendor.js?v=1.0.2"></script>
  <script type="text/javascript" src="/public/js/index.js?v=1.0.2"></script>
  
  @yield('extra_js')

  @include('component.submitSpinner')
  @include('partials.pwa-register')
@if(($_ENV['APP_ENV'] ?? '') !== 'local')
   <script nonce="{{ $nonce }}">
        window.RECAPTCHA_SITE_KEY = "{{ $_ENV['RECAPTCHA_SITE_KEY'] }}";
    </script>
@endif
</body>
</html>
