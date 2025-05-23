<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

    <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    {{--
    <link rel="stylesheet" href="/css/index.css"> --}}

    {{--
    <link rel="stylesheet" href="/css/allcss.css"> --}}

    <style>
        table {
            font-size: 0.85rem;
        }

        #newApp {

            color: red;
        }
    </style>

</head>

<body>

    <!-- THIS WILL APPEAR IF JAVASCRIPT IS NOT ACTIVATED ON A CUSTOMER COMPUTER -->
    <noscript>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>For full functionality of this site it is necessary to enable JavaScript.
                Here are the</strong> <a href="https://www.enable-javascript.com/">
                instructions how to enable JavaScript in your web browser</a>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </noscript>

    <!-- START NAV -->
    <nav class="navbar" role="navigation" aria-label="main navigation">

        <div class="navbar-brand">
            <a class="navbar-item" href="/">
                <img src={{ getenv("IMG_LOGO_BLACK") }} width="200" height="600">
            </a>

            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false"
                data-target="navbarBasicExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
            <div class="navbar-start">
                <a href="/admin/dashboard" class="navbar-item">
                    Dashboard
                </a>

                @php

                // $result = new \App\classes\Select;
                $array = [
                'selection' => "SELECT_COUNT_ONE",
                'table' => 'account',
                'identifier1'=> 'status',
                'bind' => ['new']
                ];
                $callback = "selectCountFn2";
                $result = \App\classes\Select::combineSelect($array, $callback, "ONE_IDENTIFIER");


                @endphp

                <a href="/admin/reviewApps" class="navbar-item">
                    Application(<span id='newApp'>{{ $result }}</span>)
                </a>

                <a href="/admin/allMembers" class="navbar-item">
                    All Members
                </a>

            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <a href="/admin/signout" class="button is-primary">
                            <strong>Sign Out</strong>
                        </a>


                    </div>
                </div>
            </div>
        </div>

    </nav>
    <br>
    <!-- END NAV -->

    {{-- PAGE INFORMATION --}}

    <section class="hero is-info welcome is-small">
        <div class="hero-body">
            <div class="container">
                <h1 class="subtitle">
                    <b>@yield('content-title')</b>
                </h1>
            </div>
        </div>
    </section>
    <br>



    <div class="container is-fluid ">

        @yield('content')
    </div>


    <br><br><br>
    <div class="hero-footer">
        <div class="container is-fluid ">
            <div class="content has-text-centered">


                <img src={{ getenv("IMG_CONTRACT") }} width="30" height="30" alt="LOGO"> <br>
                <span class="copyright">Copyright &copy; {{ getenv("COPYRIGHT_YEAR") }} </span>

            </div>
        </div>

        <script src="/index.js"></script>
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {

  // Get all "navbar-burger" elements
  const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach( el => {
    el.addEventListener('click', () => {

      // Get the target from the "data-target" attribute
      const target = el.dataset.target;
      const $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');

    });
  });

});
        </script>
</body>

</html>