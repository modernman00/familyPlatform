<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title')</title>

    <!-- Bootstrap Core CSS -->
    <link href="/bootstrap.min.css" rel="stylesheet" type="text/css">

    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
    integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> --}}

    <!-- Custom CSS -->
    <link href="/css/profile.css" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="/css/fontawesome.css" type="text/css">

    <!-- Custom Fonts -->
    {{-- <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"> --}}

    {{-- <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script> --}}
    <link rel="icon" type="image/png" sizes="32x32" href={{ getenv("IMG_CONTRACT") }}>
    <!-- Owl Carousel Assets -->
    <link href="/css/carousel.css" rel="stylesheet" type="text/css">
    <!-- <link href="owl-carousel/owl.theme.css" rel="stylesheet"> -->


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="js/html5shiv.js"></script>
        <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="index-page">

    @yield('content')



    <!-- jQuery and Plugin-->
    {{-- <script src="js/jquery-3.2.1.min.js"></script> --}}
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    {{-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
    integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
  </script> --}}
    <script src="/js/jquery.min.js"></script>
    <script src="/index.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    {{-- 
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
    integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
  </script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
    integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
  </script> --}}

    <!-- Google Map -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB7V-mAjEzzmP6PCQda8To0ZW_o3UOCVCE&callback=initMap"
        async defer></script>
    <!-- Logo -->
    <!-- carousel -->
    <script src="/js/carousel.js"></script>
    <script>
        $(document).ready(function() {
		  var b2_box = $("#owl-slide");
		  
		  b2_box.owlCarousel({
			autoPlay: false,
			items : 1,
			itemsDesktop : [1199,1],
			itemsDesktopSmall : [979,1],
			itemsTablet : [768, 1],
			itemsMobile : [479, 1],
			pagination: false
		  });
		  
		  // Custom Navigation Events 1
		  $(".next-b_2-slide").click(function(){
			b2_box.trigger('owl.next');
		  });
		  $(".prev-b_2-slide").click(function(){
			b2_box.trigger('owl.prev');
		  });
		  
		});
    </script>

    <script type="text/javascript" src="/js/validator.min.js"></script>
    <script type="text/javascript" src="/js/formScripts.js"></script>

</body>

</html>