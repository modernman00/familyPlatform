<!DOCTYPE html>
<html lang="en">
  
{{-- head --}}
@include('layouts.head')


<body class="w3-theme-l5" id="@yield('data-page-id')">

<!-- Navbar -->
@include('layouts.nav')

@yield('content');





<!-- Footer -->
@include('layouts.footer')

{{--  <script src="/index.js" type="text/javascript"></script>  --}}
<script>
// Accordion
function myFunction(id) {
  var x = document.getElementById(id);
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
    x.previousElementSibling.className += " w3-theme-d1";
  } else { 
    x.className = x.className.replace("w3-show", "");
    x.previousElementSibling.className = 
    x.previousElementSibling.className.replace(" w3-theme-d1", "");
  }
}

// Used to toggle the menu on smaller screens when clicking on the menu button

</script>
   <script type="text/javascript" src="/public/manifest.js" defer></script>
  <script type="text/javascript" src="/public/vendor.js" defer></script>
  <script type="text/javascript" src="/public/index.js" defer></script>
      
        <script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/service-worker.js')
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

