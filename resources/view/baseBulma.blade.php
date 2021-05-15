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

  <style>
.loader {
  border: 16px solid #11e11c;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 350px;
  height: 350px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>


  </head>
  <body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>

  @include('includes/bulmaNav')

  <section class="section">

    <div class="container">
       @yield('content')
    </div>

  </section>

  <footer class="footer">
  <div class="content has-text-centered">
    
      <p>Website developed and maintained by Olawale Olaogun </p>
  
     <a href="@yield('data-page-id')" title="To Top">
      <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-bar-up" fill="currentColor"
        xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd"
          d="M11.354 5.854a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L8 3.207l2.646 2.647a.5.5 0 0 0 .708 0z" />
        <path fill-rule="evenodd"
          d="M8 10a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-1 0v6.5a.5.5 0 0 0 .5.5zm-4.8 1.6c0-.22.18-.4.4-.4h8.8a.4.4 0 0 1 0 .8H3.6a.4.4 0 0 1-.4-.4z" />
      </svg> back to top
    </a>
  </div>
</footer>

      <script src="/manifest.js"></script>
     <script src="/vendor.js"></script>
      <script src="/index.js"></script>
  </body>
</html>