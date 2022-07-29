<!doctype html>
<html lang="en">

<head>
  <title> @yield('title') </title>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

  <script src="https://use.fontawesome.com/1dca29b934.js"></script>

  {{-- custom css --}}
  <link rel="stylesheet" href="/style.css">

  <noscript>
    <link rel="stylesheet" href="noscript.css" /></noscript>
  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>
  <!-- Bootstrap Core CSS -->

  <link href="/owl-carousel/owl.carousel.css" rel="stylesheet">
</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>

  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top mainNav mx-4 my-2">
    {{--  <div class="container-fluid">  --}}
      <a class="navbar-brand" href="#"> <img src={{ getenv("IMG_CONTRACT") }}></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
    </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <ul class="navbar-nav me-auto mb-2 mb-lg-0">
               <li class="nav-item">
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous">
  </script>

  <script src="/manifest.js"></script>
  <script src="/vendor.js"></script>
  <script src="/index.js"></script>

</body>

</html>