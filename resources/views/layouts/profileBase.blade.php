<!DOCTYPE html>
<html lang="{{ \App\classes\I18n::getLocale() }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="language" content="{{ \App\classes\I18n::getLocale() }}">
  <title>{{ $_ENV['APP_NAME'] }}</title>
  <script>
    window.APP_LOCALE = '{{ \App\classes\I18n::getLocale() }}';
    window.__TRANSLATIONS__ = {!! json_encode(\App\classes\I18n::transArray()) !!};
    window.__ = function(k, fb) { return (window.__TRANSLATIONS__ && window.__TRANSLATIONS__[k]) || fb || k; };
    window.trans = window.__;

    window.toggleTranslatePost = function(post) {
      if (!post) return;
      if (post._isTranslated) {
        post.displayMessage = post._origDisplayMessage || post.displayMessage;
        post._isTranslated = false;
        return;
      }
      const textToTranslate = post.displayMessage || post.postMessage;
      if (!textToTranslate) return;

      if (post._translatedMessage) {
        post._origDisplayMessage = post.displayMessage || post.postMessage;
        post.displayMessage = post._translatedMessage;
        post._isTranslated = true;
        return;
      }

      post._isTranslating = true;
      const targetLang = window.APP_LOCALE || 'en';
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      fetch('/api/translate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
          text: textToTranslate,
          target: targetLang
        })
      })
      .then(res => res.json())
      .then(data => {
        post._isTranslating = false;
        if (data.success && data.translation) {
          post._origDisplayMessage = post.displayMessage || post.postMessage;
          post._translatedMessage = data.translation;
          post.displayMessage = data.translation;
          post._isTranslated = true;
        }
      })
      .catch(err => {
        console.error('Translation error:', err);
        post._isTranslating = false;
      });
    };
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

  <!-- PWA & Mobile Web App Capabilities -->
  @include('partials.pwa-head')
  <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">
  <meta name="user-id" content="{{ $_SESSION['id'] ?? '' }}">
  <meta name="vapid-public-key" content="{{ $_ENV['VAPID_PUBLIC_KEY'] ?? $_SERVER['VAPID_PUBLIC_KEY'] ?? (getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['MIX_VAPID_PUBLIC_KEY'] ?? '')) }}">
  <meta name="pusher-key" content="{{ $_ENV['MIX_PUSHER_APP_KEY'] ?? $_SERVER['MIX_PUSHER_APP_KEY'] ?? $_ENV['PUSHER_APP_KEY'] ?? $_SERVER['PUSHER_APP_KEY'] ?? '' }}">
  <meta name="pusher-cluster" content="{{ $_ENV['MIX_PUSHER_APP_CLUSTER'] ?? $_SERVER['MIX_PUSHER_APP_CLUSTER'] ?? $_ENV['PUSHER_APP_CLUSTER'] ?? $_SERVER['PUSHER_APP_CLUSTER'] ?? 'eu' }}">

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
  @stack('meta')

  <link rel="manifest" href="/manifest.json">

  {{-- reCAPTCHA is only used on the auth forms (login/register/forgot/code). Loading
       enterprise.js with ?render= here made it run an assessment on every member page,
       which triggered the "Unrecognized feature: 'private-token'" warning and a 401 on
       /recaptcha/enterprise/pat. Member pages don't call grecaptcha, so it's removed. --}}
  <link rel="stylesheet" href="/public/css/main.css?v={{ assetVersion('css/main.css') }}">
  <link rel="stylesheet" href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}">
  <link rel="stylesheet" href="/public/css/profile-photo-viewer.css?v={{ assetVersion('css/profile-photo-viewer.css') }}">
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
  @includeIf('member.modals.recommendModal')
  @includeIf('components.profilePhotoViewer')

  <!-- Mobile PWA Bottom Tab Navigation (Native App Parity) -->
  @include('layouts.bottom_nav')

  @include('partials.push-notification-banner')

  @include('partials.cookie-banner')

  @include('partials.back-to-top')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script nonce="{{ $nonce }}" src="/public/js/manifest.js?v={{ assetVersion('js/manifest.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/vendor.js?v={{ assetVersion('js/vendor.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/index.js?v={{ assetVersion('js/index.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/telemetry.js?v=1.0.0"></script>
  <script nonce="{{ $nonce }}" src="/public/js/pwa-install.js?v=1.0.0"></script>
  <script nonce="{{ $nonce }}" src="/public/js/pwa-notifications.js?v=1.0.0" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/family-timezone.js?v={{ assetVersion('js/family-timezone.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/profilePhotoViewer.js?v={{ assetVersion('js/profilePhotoViewer.js') }}" defer></script>

  @include('component.submitSpinner')
  @include('partials.pwa-register')
  @stack('scripts')
</body>

</html>