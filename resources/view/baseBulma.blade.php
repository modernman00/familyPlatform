<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.2/css/bulma.min.css">

    {{-- custom css --}}
    {{--  <link rel="stylesheet" href="/style.css">  --}}

    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

      <link rel="stylesheet" href="noscript.css" />
  </noscript>

  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>


  </head>
  <body>
  <section class="section">
    <div class="container">
       @yield('content')
    </div>
  </section>
  </body>
</html>