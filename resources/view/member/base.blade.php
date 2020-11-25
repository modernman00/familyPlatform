<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title')</title>

      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.0/css/bulma.min.css">
     
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

     <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>

      <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

           <link rel="stylesheet" href="/css/index.css">

      {{-- <link rel="stylesheet" href="/css/allcss.css"> --}}

      <style>
          table {
              font-size: 0.85rem;
          }
         #newApp{

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
    <nav class="navbar is-white" role="navigation" aria-label="main navigation">

            <div class="navbar-brand">
                  <a class="navbar-item" href="/">
                <img src={{ getenv("IMG_LOGO_BLACK") }} width="200" height="600">
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
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

                $result = new \App\classes\AllFunctionalities;
                $result = $result->select_count("account", "status", "new");



                @endphp

                <a href="/admin/newCustomers" class="navbar-item"  >
                    Application(<span id='newApp'>{{ $result }}</span>)
                </a>

                  <a href="/admin/profit" class="navbar-item">
                    Profit
                </a>

                 <a href="/admin/fca" class="navbar-item">
                    FCA
                </a>

                 <a href="/admin/report" class="navbar-item">
                    Reports
                </a>

                <div class="navbar-item has-dropdown is-hoverable">
                    <a class="navbar-link">
                       Loan Info
                    </a>

                    <div class="navbar-dropdown">
                        <a class="navbar-item" href="/admin/allCust">
                            All loan
                        </a>
                        <a class="navbar-item" href="/admin/SingleCustomerView">
                           Loan by customer
                        </a>
                          <a class="navbar-item" href="/admin/completed">
                           Closed Loan
                        </a>
                         <a class="navbar-item" href="/admin/liveloan">
                           Live loan
                        </a>
                        <a class="navbar-item" href="/admin/loanbyyear">
                            Loan by year
                        </a>
                        <hr class="navbar-divider">
                        <a class="navbar-item" href="/complaint">
                            Profit by loan
                        </a>
                    </div>
                </div>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    {{-- <div class="buttons"> --}}
                        <a href="/admin/signout"class="button is-primary">
                            <strong>Sign Out</strong>
                        </a>
                        {{-- <a href="/login" class="button is-light">
                            Log in
                        </a> --}}
                    {{-- </div> --}}
                   <div class="field">
                    <p class="control  has-icons-right">
                        <input class="input" type="text" placeholder="Search">
                        <span class="icon is-small is-right">
                        <i class="fa fa-search"></i>
                        </span>
                    </p>
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


        <img src={{ getenv("IMG_CONTRACT") }}  width="30" height="30"> <br>
        <span class="copyright">Copyright &copy; {{ getenv("COPYRIGHT_YEAR") }} </span>

        </div>
    </div>

      <script src="/index.js"></script>
        <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check for click events on the navbar burger icon
            $(".navbar-burger").click(function() {
                // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
                $(".navbar-burger").toggleClass("is-active");
                $(".subNav").toggleClass("is-hoverable");
                $(".navbar-menu").toggleClass("is-active");


            });
        });
    </script>
</body>

</html>
