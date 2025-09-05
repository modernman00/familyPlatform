<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">

  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

     {{-- FAVICON  --}}
   <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("APP_LOGO") }}>


  <!-- If you intended to include a <noscript> block, add the opening tag -->
  <noscript>
    <link rel="stylesheet" href="/public/noscript.css" />
  </noscript>

    <script nonce="{{ $nonce }}"  src="https://www.google.com/recaptcha/api.js" async defer></script>


  <!-- Favicons -->
  {{-- <link rel="icon" type="image/png" sizes="32x32" href="{{ $_ENV('LOGO_DEFAULT') }}"> --}}


  <style>
    .loader {

      border: 16px solid #11e11b79;
      border-radius: 50%;
      border-top: 16px solid #2092ddf3;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite;
      /* Safari */
      animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
      0% {
        -webkit-transform: rotate(0deg);
      }

      100% {
        -webkit-transform: rotate(360deg);
      }
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes spinner-grow {
      0% {
        transform: scale(0);
      }

      50% {
        opacity: 1;
        transform: none;
      }
    }


    .text-primary {
      color: #0d6efd !important
    }

    .text-warning {
      color: #ffc107 !important
    }

    .text-danger {
      color: #dc3545 !important
    }

    @keyframes spinner-grow {
      0% {
        transform: scale(0);
      }

      50% {
        opacity: 1;
        transform: none;
      }
    }

    .spinner-grow {
      display: inline-block;
      width: 2rem;
      height: 2rem;
      vertical-align: -0.125em;
      background-color: currentColor;
      border-radius: 50%;
      opacity: 0;
      -webkit-animation: 0.75s linear infinite spinner-grow;
      animation: 0.75s linear infinite spinner-grow;
    }

    .form__login__logo {
      height: 4.5em;
      /* 1em = 16px, 4.5em = 72px */
      width: 4.5em;
      /* 1em = 16px, 4.5em = 72px */
      margin-bottom: 5rem;
      margin-left: 43%
    }

    .spinner-grow-sm {
      width: 1rem;
      height: 1rem;
    }

    @media (prefers-reduced-motion: reduce) {

      .spinner-border,
      .spinner-grow {
        -webkit-animation-duration: 1.5s;
        animation-duration: 1.5s;
      }
    }

    /* Small devices (landscape phones, 576px and up) */

    @media screen and (min-width: 576px)and (max-width: 767px) {
      .styleform_form {
        margin-left: 5%;
        margin-right: 5%;
      }

      .form__login__logo {
        height: 3.5em;
        /* 1em = 16px, 4.5em = 72px */
        width: 3.5em;
        /* 1em = 16px, 4.5em = 72px */
      }

         select option {
        font-size: 3em; /* Set the font size for the dropdown options */
    }
    }

    /* // Medium devices (tablets, 768px and up) */
    @media screen and (min-width: 768px) {
      .form__login__logo {
        height: 4.5em;
        /* 1em = 16px, 3em = 48px */
        width: 4.5em;
        /* 1em = 16px, 3em = 48px */
      }

      .styleform_form {
        margin-left: 5%;
        margin-right: 5%;
      }

       select option {
        font-size: 3em; /* Set the font size for the dropdown options */
    }
    }

    /* Large devices (desktops, 992px and up) */

    @media screen and (min-width: 992px) {
/* 
      .styleform_form {
        margin-left: 15%;
        margin-right: 15%;
      } */

      .styleForm {
          margin-left: 10%;
        margin-right: 10%;
      }
    }

    /* X-Large devices (large desktops, 1200px and up) */
    @media screen and (min-width: 1200px) {
      /* .styleform_form {
        margin-left: 30%;
        margin-right: 30%;
      } */

        .styleForm {
          margin-left: 15%;
        margin-right: 15%;
      }

    }

    /* XX-Large devices (larger desktops, 1400px and up) */
    @media screen and (min-width: 1400px) {
      /* .styleform_form {
        margin-left: 30%;
        margin-right: 30%;
      } */

        .styleForm {
          margin-left: 15%;
        margin-right: 15%;
          min-height: 100vh;
      }

    }

    section {
      min-height: calc(100vh - 100px);
    }

    .footer {
      height: 50px
    }


    .styleform_header {
      text-align: center;
    }
  </style>


</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-offset='60'>

  @include('includes.bulmaNav')



  <section class="section content">

    <div class="container">

      <div class="styleForm" style="margin-top: 2rem;">

        <img src={{ $_ENV['IMG_CONTRACT2'] }} alt="logo" class="mb-4 form__login__logo"
          style="margin-left:43%; margin-bottom:5rem;">

        {{-- <hr class="my-2"> --}}


              @yield('content')




      </div>


    </div>

  </section>

  <div class="footer">
    <div class="content has-text-centered">


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
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
  const burger = document.querySelector('.navbar-burger');
  const menu = document.getElementById('navbarMenu');

  burger.addEventListener('click', () => {
    burger.classList.toggle('is-active');
    menu.classList.toggle('is-active');
  });
});

  </script>

  <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/index.js"></script>
    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/manifest.js"></script>
    <script type="text/javascript" nonce="{{ $nonce }}" src="public/js/vendor.js"></script>


</body>

</html>
