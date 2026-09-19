@php
    $adminPrefix = $adminPrefix ?? (\class_exists('\App\middleware\AdminGuardMiddleware') && \method_exists('\App\middleware\AdminGuardMiddleware', 'getSecretAdminPath') ? \App\middleware\AdminGuardMiddleware::getSecretAdminPath() : '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/'));
@endphp
<!DOCTYPE html>
<html lang="en">
  <head >


<title>@yield('title') | Admin Command Center</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
     <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>
<style>
    html,
    body,
    h1,
    h2,
    h3,
    h4,
    h5 {
        font-family: "Raleway", sans-serif
    }

    .likeCounter{
  padding: 10px;
  margin: 12px;
  color: red;
}
</style>

  <link rel="manifest" href="/manifest.json" type="application/manifest+json">

  </head>

<body class="w3-light-grey">

    <!-- Top container -->
    <div class="w3-bar w3-top w3-black w3-large" style="z-index:4">
        <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey"
            onclick="w3_open();"><i class="fa fa-bars"></i>  Menu</button>
        <span class="w3-bar-item w3-left" style="font-weight:bold;"><a href="{{ $adminPrefix }}/dashboard" style="text-decoration:none; color:#fff;">Admin Command Center</a></span>
        <span class="w3-bar-item w3-right">
            <a href="/" target="_blank" style="margin-right:15px; color:#81c784; text-decoration:none;"><i class="fa fa-globe"></i> View Site</a>
            <a href='{{ $adminPrefix }}/logout' style="color:#ef4444; text-decoration:none;"><i class="fa fa-sign-out"></i> Sign out</a>
        </span>
    </div>

    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;" id="mySidebar"><br>
        <div class="w3-container w3-row" style="margin-top:10px;">
            <div class="w3-col s4">
                <i class="fa fa-user-circle w3-text-teal" style="font-size:46px;"></i>
            </div>
            <div class="w3-col s8 w3-bar">
                <span>Welcome, <strong>Administrator</strong></span><br>
                <a href="{{ $adminPrefix }}/setup-2fa" class="w3-bar-item w3-button" title="Security Settings"><i class="fa fa-shield"></i></a>
                <a href="{{ $adminPrefix }}/telemetry" class="w3-bar-item w3-button" title="Telemetry"><i class="fa fa-line-chart"></i></a>
                <a href="{{ $adminPrefix }}/logout" class="w3-bar-item w3-button" title="Logout"><i class="fa fa-sign-out"></i></a>
            </div>
        </div>
        <hr>
        <div class="w3-container">
            <h5><b>Navigation</b></h5>
        </div>
        <div class="w3-bar-block">
            <a href="#" class="w3-bar-item w3-button w3-padding-16 w3-hide-large w3-dark-grey w3-hover-black"
                onclick="w3_close()" title="close menu"><i class="fa fa-remove fa-fw"></i>  Close Menu</a>
            <a href="{{ $adminPrefix }}/dashboard" class="w3-bar-item w3-button w3-padding w3-blue"><i class="fa fa-tachometer fa-fw"></i>  Overview / Dashboard</a>
            <a href="{{ $adminPrefix }}/new-registrations" class="w3-bar-item w3-button w3-padding"><i class="fa fa-user-plus fa-fw"></i>  New Registrations</a>
            <a href="{{ $adminPrefix }}/members" class="w3-bar-item w3-button w3-padding"><i class="fa fa-users fa-fw"></i>  Registered Users</a>
            <a href="{{ $adminPrefix }}/online-users" class="w3-bar-item w3-button w3-padding"><i class="fa fa-signal fa-fw"></i>  Users Online Now</a>
            <a href="{{ $adminPrefix }}/blog/create" class="w3-bar-item w3-button w3-padding"><i class="fa fa-pencil fa-fw"></i>  Create Blog Post</a>
            <a href="/blogs" class="w3-bar-item w3-button w3-padding"><i class="fa fa-newspaper-o fa-fw"></i>  All Blog Posts</a>
            <a href="{{ $adminPrefix }}/telemetry" class="w3-bar-item w3-button w3-padding"><i class="fa fa-line-chart fa-fw"></i>  Telemetry & RUM</a>
            <a href="{{ $adminPrefix }}/erasure" class="w3-bar-item w3-button w3-padding"><i class="fa fa-user-times fa-fw"></i>  GDPR Erasure</a>
            <a href="{{ $adminPrefix }}/setup-2fa" class="w3-bar-item w3-button w3-padding"><i class="fa fa-shield fa-fw"></i>  2-FA Security</a>
            <a href="/" target="_blank" class="w3-bar-item w3-button w3-padding"><i class="fa fa-globe fa-fw"></i>  View Live Site</a>
            <a href="{{ $adminPrefix }}/logout" class="w3-bar-item w3-button w3-padding w3-text-red"><i class="fa fa-sign-out fa-fw"></i>  Logout</a><br><br>
        </div>
    </nav>


    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer"
        title="close side menu" id="myOverlay">
    </div>

    <!-- !PAGE CONTENT! -->
    <div class="w3-main" style="margin-left:300px;margin-top:43px;">

    @yield('content')

     
        </div>

        <!-- Footer -->
        <footer class="w3-container w3-padding-16 w3-light-grey">
            <h4>FOOTER</h4>
            <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
        </footer>

        <!-- End page content -->
    </div>

    <script>
        // Get the Sidebar
var mySidebar = document.getElementById("mySidebar");

// Get the DIV with overlay effect
var overlayBg = document.getElementById("myOverlay");

// Toggle between showing and hiding the sidebar, and add overlay effect
function w3_open() {
  if (mySidebar.style.display === 'block') {
    mySidebar.style.display = 'none';
    overlayBg.style.display = "none";
  } else {
    mySidebar.style.display = 'block';
    overlayBg.style.display = "block";
  }
}

// Close the sidebar with the close button
function w3_close() {
  mySidebar.style.display = "none";
  overlayBg.style.display = "none";
}
    </script>

    <script type="text/javascript" src="/public/manifest.js" defer></script>
  <script type="text/javascript" src="/public/vendor.js" defer></script>
  <script type="text/javascript" src="/public/index.js" defer></script>
  <script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js')
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
