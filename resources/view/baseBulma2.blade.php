<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <title>@yield('title')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">

  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>


  <noscript>
  <link rel="stylesheet" href="noscript.css" />
  </noscript>


  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>


</head>

<body data-page-id="@yield('data-page-id')" data-spy="scroll" data-target=".navbar" data-offset='60'>


<section class="hero is-info is-large">


   <div class="hero-head">
     @include('includes/bulmaNav')
  </div>

   <div class="hero-body">
    <div class="container has-text-centered">
     
 
        @yield('content')

    </div>
  </div>

</section>   

   <script type="text/javascript" src="/manifest.js"></script>
     <script type="text/javascript" src="/vendor.js"></script>
      <script type="text/javascript" src="/index.js"></script>
 



</body>

</html>