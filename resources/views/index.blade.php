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
  <meta name="generator" content="Jekyll v4.1.1">
  <title>OUR FAMILY NETWORK</title>

  <!-- OpenGraph meta tags for better sharing on social media -->
  <meta property="og:title" content="Your Family Platform - Connect, Share, and Grow Together">
  <meta property="og:description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
  <meta property="og:type" content="website">
  <meta property="og:image" content="{{ getenv("IMG_CONTRACT") }}"> 
  <meta property="og:url" content="https://www.myfamilyplatform.com"> 
  <meta property="og:site_name" content="OUR FAMILY NETWORK">
  <meta property="og:locale" content="en_UK">




  <!-- Twitter Card meta tags for better sharing on Twitter -->
  <meta name="twitter:title" content="OUR FAMILY NETWORK">
  <meta name="twitter:description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
  <meta name="twitter:image" content="{{ getenv("IMG_CONTRACT") }}"> 
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@familyplatform"

  <!-- Favicons -->
  <link rel="apple-touch-icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="180x180" alt="apple-touch-icon">
  <link rel="icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="32x32" type="image/png" alt="icon-32x32">
  {{-- <link rel="icon" href="y" sizes="16x16" type="image/png"> --}}
  {{-- <link rel="manifest" href="{{ getenv("IMG_CONTRACT") }}"> --}}
  <link rel="mask-icon" href="{{ getenv("IMG_CONTRACT") }}" color="#563d7c">
  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("APP_LOGO") }} alt="icon">
  <meta name="msapplication-config" content="{{ getenv("IMG_CONTRACT") }}">
  <meta name="theme-color" content="#563d7c">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

  <!-- Custom styles for this template -->
  <link href="/public/css/homePage.css" rel="stylesheet">

  <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json"> 


  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }

    .copyright {
      text-align: center;
      background-color: #333;
      color: #fff;
      padding: 10px 0;
    }

  </style>


</head>



<body>
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="masthead mb-auto">
      <div class="inner">

        <nav class="nav nav-masthead justify-content-center" role='navigation' aria-label='Main Navigationb'>


          <a class="nav-link active" href="/" aria-label="Home page">Home</a>
          <a class="nav-link" href="/register" aria-label="Register page">Register</a>
          <a class="nav-link" href="/login" aria-label="Login page">Login</a>
          <a class="nav-link" href="/aboutus" aria-label="About Us page">About us</a>

        </nav>
      </div>
    </header>

    <main role="main" class="inner cover text-center">
      <h1 class="cover-heading">OUR FAMILY NETWORK</h1>
      <p class="lead">The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.</p>


      <p class="lead">
        <a href="/register" class="btn btn-lg btn-secondary">Join Now!</a>
      </p>

    </main>

    <footer class="mastfoot mt-auto">
      {{-- <div class="inner">
        <p>The site is built and maintained by Wale Olaogun<a href="https://twitter.com/modernman">@modernman</a>. </p>
      </div>
       <br> --}}

      <div class="copyright">
        &copy; 2023 {{ getenv("APP_NAME") }}. All rights reserved.
        {{-- <p>The site is built and maintained by Wale Olaogun<a href="https://twitter.com/modernman">@modernman</a>. </p> --}}
        <a href="/privacy">Privacy</a> | <a href="/terms">Terms</a> | <a href="/contact">Contact</a> | <a href="/aboutus">About us</a>
      </div>




    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

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
