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
   <meta property="og:url" content="https://www.myfamilyplatform.com"> <!-- Replace with your website URL --> <!-- Replace with your website URL -->

    <!-- Twitter Card meta tags for better sharing on Twitter -->
    <meta name="twitter:title" content="OUR FAMILY NETWORK">
    <meta name="twitter:description" content="The Ultimate Social Platform for Your Family - Social media sites for Families to connect, strengthen Bonds, share Memories, and know the family Tree.">
    <meta name="twitter:image" content="{{ getenv("IMG_CONTRACT") }}"> <!-- Replace with the URL to your website's logo or featured image -->
    <meta name="twitter:card" content="summary_large_image">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

  <script src="https://use.fontawesome.com/1dca29b934.js"></script>

  {{-- custom css --}}
  <link rel="stylesheet" href="/style.css">

  <noscript>
    <link rel="stylesheet" href="noscript.css" /></noscript>
    <!-- Favicons -->
  <link rel="apple-touch-icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="180x180">
  <link rel="icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="32x32" type="image/png">
  <link rel="icon" href="y" sizes="16x16" type="image/png">
  <link rel="manifest" href="{{ getenv("IMG_CONTRACT") }}">
  <link rel="mask-icon" href="{{ getenv("IMG_CONTRACT") }}" color="#563d7c">
  <link rel="icon" href="{{ getenv("IMG_CONTRACT") }}">
  <meta name="msapplication-config" content="{{ getenv("IMG_CONTRACT") }}">
  <meta name="theme-color" content="#563d7c">
  <!-- Bootstrap Core CSS -->

  <link href="/owl-carousel/owl.carousel.css" rel="stylesheet">
</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>

  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top mainNav mx-4 my-2">
    {{--  <div class="container-fluid">  --}}
      <a class="navbar-brand" href="#"> <img src={{ getenv("IMG_CONTRACT") }} alt="Logo"></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
    </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <ul class="navbar-nav me-auto mb-2 mb-lg-0">
               <li class="nav-item allMemberNav">
            <a class="nav-link" href="/allMembers">All Members</a>
          </li>


          <li class="nav-item">
            <a class="nav-link" href="/member/ProfilePage">My Page</a>
          </li>
     
        </ul>



        <form class="form-inline my-2 my-sm-0">
          <button class="btn btn-outline-success my-2 my-sm-0">
            <a href="/signout">Sign out</a>
          </button>
        </form>
      </div>
    {{--  </div>  --}}
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
    <p>Website developed by Jumoke, Oluwatobi and Olawale Olaogun (daddy)</p>
  </footer>

  <!-- Optional JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous">
  </script>

  <script src="/manifest.js"></script>
  <script src="/vendor.js"></script>
  <script src="/index.js"></script>

</body>

</html>