<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>{{ $_ENV['APP_NAME'] }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

  <!-- PWA & Mobile Web App Capabilities -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="FamilyPlatform">
  <link rel="apple-touch-icon" href="/public/img/favicon/apple-touch-icon.png" sizes="180x180">
  <link rel="icon" href="/public/img/favicon/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="/public/img/favicon/favicon-16x16.png" sizes="16x16" type="image/png">
  <meta name="theme-color" content="#2563eb">
  <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">

  <!-- iOS WebKit App-Store Splash Screen Matrix (Dr. Soren Lindqvist Gate Mandate) -->
  <!-- iPhone 16 Pro Max, 15 Pro Max, 14 Pro Max -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 16 Pro, 15 Pro, 14 Pro -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 14, 13, 13 Pro, 12, 12 Pro -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 13 mini, 12 mini, 11 Pro, XS, X -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 11, XR -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)">
  <!-- iPhone 8 Plus, 7 Plus, 6s Plus -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone SE (2nd/3rd gen), 8, 7, 6s -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
  <!-- iPad Pro 12.9", 11", Air 10.9" -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)">
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)">

  <script src="https://kit.fontawesome.com/e78379d4a3.js" crossorigin="anonymous"></script>

  <title>@yield('title')</title>

  <link rel="manifest" href="/manifest.json">

  {{-- reCAPTCHA is only used on the auth forms (login/register/forgot/code). Loading
       enterprise.js with ?render= here made it run an assessment on every member page,
       which triggered the "Unrecognized feature: 'private-token'" warning and a 401 on
       /recaptcha/enterprise/pat. Member pages don't call grecaptcha, so it's removed. --}}
  <link rel="stylesheet" href="/public/css/main.css?v={{ assetVersion('css/main.css') }}">
  @stack('styles')
</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll">

  @include('layouts.navbar')

  {{-- <div class="container-main"> --}}

    @yield('content')


    {{-- </div> --}}

  {{-- Rendered before the app scripts so the profile-page chunk (which may execute
       from prefetch cache before the parser gets here) always finds this markup. --}}
  @includeIf('member.modals.editProfile')

  <!-- Mobile PWA Bottom Tab Navigation (Native App Parity) -->
  @include('layouts.bottom_nav')

  @include('partials.cookie-banner')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script nonce="{{ $nonce }}" src="/public/js/manifest.js?v={{ assetVersion('js/manifest.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/vendor.js?v={{ assetVersion('js/vendor.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/index.js?v={{ assetVersion('js/index.js') }}"></script>

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
</body>

</html>