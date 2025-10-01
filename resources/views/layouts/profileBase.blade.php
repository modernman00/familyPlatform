<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $_ENV['APP_NAME'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
      <title>@yield('title')</title>

        <link rel="stylesheet" href="/public/noscript.css" />
    </noscript>

    <link rel="icon" type="image/png" sizes="32x32" href={{ $_ENV['APP_LOGO'] }}>

    <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json">

        <script nonce="{{ $nonce }}" src="https://www.google.com/recaptcha/api.js" async defer></script>
    @stack('styles')

   


</head>
<body data-page-id="@yield('data-page-id')" data-spy="scroll">

  @include('layouts.navbar')

    {{-- <div class="container-main"> --}}

        @yield('content')


    {{-- </div> --}}


    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle" id="darkModeToggle">
        <i class="bi bi-moon-fill"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    </script>
     <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/index.js"></script>

    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/manifest.js"></script>

    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/vendor.js"></script>
      
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