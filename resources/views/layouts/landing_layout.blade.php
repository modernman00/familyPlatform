<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
  <meta name="keywords" content="family network, social platform, social media, connect, strengthen bonds, share memories, family tree">
  <meta name="robots" content="index, follow">
  <meta name="author" content="Olawale Olaogun">
  <meta name="language" content="English">
  <title>@yield('title') | OUR FAMILY NETWORK</title>

  <!-- OpenGraph meta tags -->
  <meta property="og:title" content="@yield('title') - Family Platform">
  <meta property="og:description" content="The Ultimate Social Platform for Your Family.">
  <meta property="og:type" content="website">
  <meta property="og:image" content="{{ getenv('IMG_CONTRACT') }}"> 
  <meta property="og:url" content="{{ getenv('APP_URL') }}"> 
  <meta property="og:site_name" content="OUR FAMILY NETWORK">
  
  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ getenv('APP_LOGO') }}">
  
  <!-- PWA Manifest -->
  <link rel="manifest" href="/PWA_Manifest.json">
  <meta name="theme-color" content="#0a66c2">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Family Connect">
  
  <!-- CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/public/css/landing.css" rel="stylesheet">
  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
  
<script nonce="{{ $nonce }}" src="https://www.google.com/recaptcha/enterprise.js?render={{ $_ENV['RECAPTCHA_SITE_KEY'] }}"></script>
<script nonce="{{ $nonce }}">window.RECAPTCHA_SITE_KEY = "{{ $_ENV['RECAPTCHA_SITE_KEY'] }}";</script>

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
  </style>
  
  @yield('extra_css')
</head>

<body data-page-id="@yield('data-page-id')">
  
  @include('includes.public_nav')

  <div class="content-wrapper" style="padding-top: 80px;">
    @yield('content')
  </div>

  @include('includes.public_footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- App JS -->
  <script type="text/javascript" src="/public/js/manifest.js?v=1.0.2"></script>
  <script type="text/javascript" src="/public/js/vendor.js?v=1.0.2"></script>
  <script type="text/javascript" src="/public/js/index.js?v=1.0.2"></script>
  
  @yield('extra_js')

  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
          .then((registration) => {
            console.log('Service Worker registered:', registration);
          })
          .catch((error) => {
            console.error('Service Worker registration failed:', error);
          });
      });
    }
  </script>
   <script nonce="{{ $nonce }}">
        window.RECAPTCHA_SITE_KEY = "{{ $_ENV['RECAPTCHA_SITE_KEY'] }}";
    </script>
</body>
</html>
