<!DOCTYPE html>
<html>
  
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
    <script src="/manifest.js"></script>
     <script src="/vendor.js"></script>
      <script src="/index.js"></script>
      

</body>
</html> 
