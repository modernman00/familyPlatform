<!doctype html>
<html lang="en">

<head>
  <title> @yield('title') </title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <meta name="description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta name="keywords" content="family network, social platform, social media, connect, strengthen bonds, share memories, family tree">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Olawale Olaogun">
    <meta name="language" content="English">
  <meta name="generator" content="Jekyll v4.1.1">

   <!-- OpenGraph meta tags for better sharing on social media -->
    <meta property="og:title" content="OUR FAMILY NETWORK">
    <meta property="og:description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta property="og:type" content="website">
      <meta name="twitter:image" content="{{ getenv("IMG_CONTRACT") }}"> <!-- Replace with the URL to your website's logo or featured image -->
    <meta property="og:url" content="https://www.myfamilyplatform.com"> <!-- Replace with your website URL -->

    <!-- Twitter Card meta tags for better sharing on Twitter -->
    <meta name="twitter:title" content="OUR FAMILY NETWORK">
    <meta name="twitter:description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta name="twitter:image" content="{{ getenv("IMG_CONTRACT") }}"> <!-- Replace with the URL to your website's logo or featured image -->
    <meta name="twitter:card" content="summary_large_image">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

 <script src="https://use.fontawesome.com/1dca29b934.js"></script>
  
  {{-- custom css --}}
  <link rel="stylesheet" href="/public/style.css">

  <noscript>
  <link rel="stylesheet" href="noscript.css" />
  </noscript>


  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>


</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>

  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top mainNav">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active">
          <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/about">About</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/policy">Policy</a>
        </li>

        <li class="nav-item">
          <a class="nav-link disabled" href="#">Disabled</a>
        </li>
      </ul>



      <form class="form-inline my-2 my-sm-0 ">
        <button class="btn btn-outline-success my-2 my-sm-0">
          <a href="/register">Register</a>
        </button>
        <button class="btn btn-outline-success my-2 my-sm-0">
          <a href="/login">Login</a>
        </button>
      </form>
    </div>
  </nav>




  @yield('content')

  <hr>
  <footer class="container-fluid text-center">

    <a href="#myPage" title="To Top">
      <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-bar-up" fill="currentColor"
        xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd"
          d="M11.354 5.854a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0l-3 3a.5.5 0 1 0 .708.708L8 3.207l2.646 2.647a.5.5 0 0 0 .708 0z" />
        <path fill-rule="evenodd"
          d="M8 10a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-1 0v6.5a.5.5 0 0 0 .5.5zm-4.8 1.6c0-.22.18-.4.4-.4h8.8a.4.4 0 0 1 0 .8H3.6a.4.4 0 0 1-.4-.4z" />
      </svg> back to top
    </a>
    <p>Website developed and maintained by Olawale Olaogun </p>
  </footer>

 
  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
   
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>

    <script src="/manifest.js"></script>
     <script src="/vendor.js"></script>
      <script src="/index.js"></script>

</body>

</html>