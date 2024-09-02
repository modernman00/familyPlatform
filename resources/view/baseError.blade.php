<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.2/css/bulma.min.css">

  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>


  <noscript>
  <link rel="stylesheet" href="noscript.css" />
  </noscript>


  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>

    <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json"> />


</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>


<section class="hero is-info is-large">


   <div class="hero-head">
     @include('includes/bulmaNav')
  </div>

   <div class="hero-body">
    <div class="container has-text-centered">
      <p class="title">
        There is an Error: 
      </p>
      <h3 class="title">
        @yield('content')
      </h3>
    </div>
  </div>

</section>    
 
 




</body>

</html>