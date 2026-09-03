<head>

  <title>@yield('title')</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue-grey.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
  <!-- PWA & Mobile Web App Capabilities -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="FamilyPlatform">
  <link rel="apple-touch-icon" href="/public/img/favicon/apple-touch-icon.png" sizes="180x180">
  <link rel="icon" href="/public/img/favicon/favicon-32x32.png" sizes="32x32" type="image/png">
  <link rel="icon" href="/public/img/favicon/favicon-16x16.png" sizes="16x16" type="image/png">
  <meta name="theme-color" content="#2563eb">
  <meta name="csrf-token" content="{{ $_SESSION['token'] ?? '' }}">

  <!-- iOS WebKit App-Store Splash Screen Matrix (Dr. Soren Lindqvist Gate Mandate) -->
  <!-- iPhone 16 Pro Max, 15 Pro Max, 14 Pro Max -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 16 Pro, 15 Pro, 14 Pro -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 14, 13, 13 Pro, 12, 12 Pro -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 13 mini, 12 mini, 11 Pro, XS, X -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone 11, XR -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)">
  <!-- iPhone 8 Plus, 7 Plus, 6s Plus -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)">
  <!-- iPhone SE (2nd/3rd gen), 8, 7, 6s -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
  <!-- iPad Pro 12.9", 11", Air 10.9" -->
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)">
  <link rel="apple-touch-startup-image" href="/public/img/favicon/android-chrome-512x512.png" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)">

  <link rel="manifest" href="/manifest.json">

      <script nonce="{{ $nonce }}" src="https://www.google.com/recaptcha/api.js" async defer></script>

  

  <link rel="stylesheet" type="text/css" href="/public/css/profilepage.css?v={{ time() }}">
  <link rel="stylesheet" type="text/css"
    href="https://unpkg.com/file-upload-with-preview@4.1.0/dist/file-upload-with-preview.min.css" />

  <style>
    html,
    body,
    h1,
    h2,
    h3,
    h4,
    h5 {
      font-family: var(--font-family, -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif);
    }

    @media screen and (min-width: 768px) {
    .postTimeCal {
        font-size: 1em;
    }
}

@media screen and (min-width: 576px) {
    .postTimeCal {
        font-size: 1em;
    }
}

.icon-button .fa-trash {
    display: block;
    width: 100%;
    height: 100%;
    text-align: center;
    line-height: inherit;
}


.loader {

    border: 8px solid #11e11b79;
    border-radius: 50%;
    border-top: 8px solid #2092ddf3;
    width: 80px;
    height: 80px;
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

@keyframes spinner-grow {
  0% {
    transform: scale(0);
  }
  50% {
    opacity: 1;
    transform: none;
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

/* Default styles for larger screens */
@media (min-width: 1024px) {
  .profileImg {
    width: 18vh;
    height: 18vh;
  }
}

/* iPad Mini (2021) - 8.3 inches, and similar sizes */
@media (min-width: 744px) and (max-width: 834px) {
  .profileImg {
    width: 16vh;
    height: 16vh;
  }
}

/* iPad Pro 11-inch (2021), iPad Air (10.9-inch), and similar */
@media (min-width: 810px) and (max-width: 1194px) {
  .profileImg {
    width: 17vh;
    height: 17vh;
  }
}

/* iPad Pro 12.9-inch (2021) */
@media (min-width: 1024px) and (max-width: 1366px) {
  .profileImg {
    width: 18vh;
    height: 18vh;
  }
}






  </style>


</head>
