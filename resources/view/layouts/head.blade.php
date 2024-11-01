<head>

  <title>@yield('title')</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
  <link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue-grey.css">
  <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans'>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  
    <!-- Favicons -->
  <link rel="apple-touch-icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="180x180">
  <link rel="icon" href="{{ getenv("IMG_CONTRACT") }}" sizes="32x32" type="image/png">
  <link rel="mask-icon" href="{{ getenv("IMG_CONTRACT") }}" color="#563d7c">
  <meta name="msapplication-config" content="{{ getenv("IMG_CONTRACT") }}">
  <meta name="theme-color" content="#563d7c">

  <link rel="manifest" href="/PWA_Manifest.json" type="application/manifest+json"> />

  

  <link rel="stylesheet" type="text/css" href="/public/profilepage.css">
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
      font-family: "Open Sans", sans-serif
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

    border: 16px solid #11e11b79;
    border-radius: 50%;
    border-top: 16px solid #2092ddf3;
    width: 120px;
    height: 120px;
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



  </style>


</head>
