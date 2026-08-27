<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $_ENV['APP_NAME'] }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">


  <script src="https://kit.fontawesome.com/e78379d4a3.js" crossorigin="anonymous"></script>

  <title>@yield('title')</title>
  <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">

  <link rel="stylesheet" href="/public/noscript.css" />
  </noscript>

  <link rel="icon" type="image/png" sizes="32x32" href={{ $_ENV['APP_LOGO'] }}>

  <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json">

  {{-- reCAPTCHA is only used on the auth forms (login/register/forgot/code). Loading
       enterprise.js with ?render= here made it run an assessment on every member page,
       which triggered the "Unrecognized feature: 'private-token'" warning and a 401 on
       /recaptcha/enterprise/pat. Member pages don't call grecaptcha, so it's removed. --}}
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

  <!-- Dark Mode Toggle -->
  <div class="dark-mode-toggle" id="darkModeToggle">
    <i class="bi bi-moon-fill"></i>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  </script>
  <script nonce="{{ $nonce }}" src="/public/js/index.js"></script>
  <script nonce="{{ $nonce }}" src="/public/js/manifest.js"></script>
  <script nonce="{{ $nonce }}" src="/public/js/vendor.js"></script>

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