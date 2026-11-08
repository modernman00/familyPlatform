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

    window.toggleTranslatePost = function(post, forcedTarget) {
      if (!post) return;
      if (post._isTranslated) {
        post.displayMessage = post._origDisplayMessage || post.displayMessage;
        post._isTranslated = false;
        return;
      }
      const textToTranslate = post.displayMessage || post.postMessage;
      if (!textToTranslate || !String(textToTranslate).trim()) {
        if (window.Swal) {
          window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'No text available to translate.',
            showConfirmButton: false,
            timer: 2500
          });
        }
        return;
      }

      // If already fetched and cached on this client object for this target, toggle directly
      if (post._translatedMessage && !forcedTarget) {
        post._origDisplayMessage = post._origDisplayMessage || post.displayMessage || post.postMessage;
        post.displayMessage = post._translatedMessage;
        post._isTranslated = true;
        return;
      }

      post._isTranslating = true;
      let targetLang = forcedTarget || window.APP_LOCALE || 'en';
      if (targetLang === 'us') targetLang = 'en';

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
          target: targetLang,
          token: csrfToken
        })
      })
      .then(res => res.json())
      .then(data => {
        post._isTranslating = false;
        if (data.success && data.translation) {
          if (data.already_in_target && targetLang === 'en' && !forcedTarget) {
            // Post is already in English. Ask viewer which foreign language they wish to translate into
            if (window.Swal) {
              window.Swal.fire({
                title: window.__ ? window.__('feed.translate_post_title', 'Translate Post') : 'Translate Post',
                text: window.__ ? window.__('feed.already_english', 'This post is already written in English. Which language would you like to translate it into?') : 'This post is already written in English. Which language would you like to translate it into?',
                input: 'select',
                inputOptions: {
                  'fr': 'Français (French)',
                  'es': 'Español (Spanish)',
                  'yo': 'Yorùbá (Yoruba)',
                  'de': 'Deutsch (German)',
                  'pt': 'Português (Portuguese)',
                  'it': 'Italiano (Italian)'
                },
                inputPlaceholder: 'Select a language...',
                showCancelButton: true,
                confirmButtonColor: '#a31621',
                confirmButtonText: window.__ ? window.__('feed.translate_btn', 'Translate') : 'Translate'
              }).then(result => {
                if (result.isConfirmed && result.value) {
                  window.toggleTranslatePost(post, result.value);
                }
              });
              return;
            }
          }
          post._origDisplayMessage = post._origDisplayMessage || post.displayMessage || post.postMessage;
          post._translatedMessage = data.translation;
          post.displayMessage = data.translation;
          post._detectedSource = data.detected_source || '';
          post._isTranslated = true;
        } else {
          if (window.Swal) {
            window.Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'error',
              title: data.error || 'Could not translate post at this moment.',
              showConfirmButton: false,
              timer: 3000
            });
          }
        }
      })
      .catch(err => {
        console.error('Translation error:', err);
        post._isTranslating = false;
        if (window.Swal) {
          window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Translation network error.',
            showConfirmButton: false,
            timer: 3000
          });
        }
      });
    };
  </script>
  {{-- Perf: open the third-party connections early so the render-blocking CSS/JS below skip DNS+TLS setup --}}
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
  <link rel="preconnect" href="https://kit.fontawesome.com" crossorigin>
  <link rel="dns-prefetch" href="https://ka-f.fontawesome.com">
  <link href="/public/lib/bootstrap-5.3.0-alpha1/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/public/lib/bootstrap-icons-1.10.3/bootstrap-icons.css">

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

  {{-- Perf: defer (was parser-blocking, ~1s render delay). Few Font Awesome icons are used on this page; they upgrade as soon as the kit runs. --}}
  <script defer src="https://kit.fontawesome.com/e78379d4a3.js" crossorigin="anonymous"></script>

  <title>@yield('title')</title>
  <meta name="description" content="@yield('meta_description', 'Your private family space on FamilyPlatform: share moments, stay connected and keep your family history together.')">
  @stack('meta')

  <link rel="manifest" href="/manifest.json">

  {{-- reCAPTCHA is only used on the auth forms (login/register/forgot/code). Loading
       enterprise.js with ?render= here made it run an assessment on every member page,
       which triggered the "Unrecognized feature: 'private-token'" warning and a 401 on
       /recaptcha/enterprise/pat. Member pages don't call grecaptcha, so it's removed. --}}
  <link rel="stylesheet" href="/public/css/main.css?v={{ assetVersion('css/main.css') }}">
  <link rel="stylesheet" href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}">
  <link rel="preload" href="/public/css/profile-photo-viewer.css?v={{ assetVersion('css/profile-photo-viewer.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="/public/css/profile-photo-viewer.css?v={{ assetVersion('css/profile-photo-viewer.css') }}"></noscript>
  <link rel="preload" href="/public/css/atg-emoji.css?v={{ assetVersion('css/atg-emoji.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="/public/css/atg-emoji.css?v={{ assetVersion('css/atg-emoji.css') }}"></noscript>
  @stack('styles')
</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll">
  @include('partials.pwa-splash')

  @include('layouts.navbar')

  <main id="main-content" role="main">
    @yield('content')
  </main>

  {{-- Rendered before the app scripts so the profile-page chunk (which may execute
       from prefetch cache before the parser gets here) always finds this markup. --}}
  @includeIf('member.modals.editProfile')
  @includeIf('member.modals.recommendModal')
  @includeIf('member.modals.inviteFamilyModal')
  @includeIf('components.profilePhotoViewer')
  @includeIf('components.omniSearchModal')

  <!-- Mobile PWA Bottom Tab Navigation (Native App Parity) -->
  @include('layouts.bottom_nav')

  @include('partials.push-notification-banner')

  @include('partials.cookie-banner')

  @include('partials.back-to-top')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  {{-- Living emoji catalog for feed rendering, comment picker and tap-to-play (single source: AtgEmojiService) --}}
  @php
    $atgLivingEmojis = [
      'catalog'  => array_map(fn ($e) => ['id' => $e['id'], 'title' => $e['title'], 'meaning' => $e['meaning'] ?? ''], \App\services\AtgEmojiService::EMOJI_CATALOG),
      'toneable' => \App\services\AtgEmojiService::TONEABLE_EMOJIS,
      'tones'    => array_keys(\App\services\AtgEmojiService::EMOJI_TONES),
    ];
  @endphp
  <script nonce="{{ $nonce }}">window.ATG_LIVING_EMOJIS = @json($atgLivingEmojis);</script>
  <script nonce="{{ $nonce }}" src="/public/js/manifest.js?v={{ assetVersion('js/manifest.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/vendor.js?v={{ assetVersion('js/vendor.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/index.js?v={{ assetVersion('js/index.js') }}"></script>
  <script nonce="{{ $nonce }}" src="/public/js/telemetry.js?v={{ assetVersion('js/telemetry.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/pwa-notifications.js?v=1.0.0" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/family-timezone.js?v={{ assetVersion('js/family-timezone.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/profilePhotoViewer.js?v={{ assetVersion('js/profilePhotoViewer.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/family-tooltip.js?v={{ assetVersion('js/family-tooltip.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/atg-emoji-engine.js?v={{ assetVersion('js/atg-emoji-engine.js') }}" defer></script>
  <script nonce="{{ $nonce }}" src="/public/js/smart-nav-scroll.js?v={{ assetVersion('js/smart-nav-scroll.js') }}" defer></script>

  @include('components.atgArViewerModal')
  @include('component.submitSpinner')
  @include('partials.pwa-register')
  @stack('scripts')
</body>

</html>