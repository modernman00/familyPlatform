<!DOCTYPE HTML>
<!--
	Forty by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>

<head>
    <title> Profile Page</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="stylesheet" href="/css/forty.css" />
    <noscript>
        <link rel="stylesheet" href="/css/noscript40.css" /></noscript>
        <!-- Material Design Bootstrap -->
        <!-- Font Awesome -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
<!-- Google Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
{{-- <!-- Bootstrap core CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet"> --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css" rel="stylesheet">
</head>

<body class="is-preload">

    <!-- Wrapper -->
    <div id="wrapper">

        <!-- Header -->
        <header id="header" class="alt">
            <a href="index.html" class="logo"><strong>Forty</strong> <span>by HTML5 UP</span></a>
            <nav>
                <a href="#menu">Menu</a>
            </nav>
        </header>

        <!-- Menu -->
        <nav id="menu">
            <ul class="links">
                <li><a href="index.html">Home</a></li>
                <li><a href="landing.html">Landing</a></li>
                <li><a href="generic.html">Generic</a></li>
                <li><a href="elements.html">Elements</a></li>
            </ul>
            <ul class="actions stacked">
                <li><a href="#" class="button primary fit">Get Started</a></li>
                <li><a href="#" class="button fit">Log In</a></li>
            </ul>
        </nav>

        <!-- Banner -->
        <section id="banner" class="major">
            <div class="inner">
                <header class="major">
                    <h1>Hi, {{ $data['firstName'] }} {{ $data['lastName'] }}</h1>
                </header>
                <div class="content">
                    <p>A responsive site template designed by HTML5 UP<br />
                        and released under the Creative Commons.</p>
                        <input type="file" id="myfile" name="myfile">

                        {{-- FILE UPLOAD --}}

                        <div class="input-group">
  <div class="custom-file">
    <input type="file" class="custom-file-input" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04">
    <label class="custom-file-label" for="inputGroupFile04">Choose file</label>
  </div>
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" id="inputGroupFileAddon04">Button</button>
  </div>
</div>

                    <ul class="actions">
                        <li><a href="#one" class="button next scrolly">Get Started</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Main -->
        <div id="main">

            <!-- One -->
            <section id="one" class="tiles">
                <article>
                    <span class="image">
                        {{-- <img src="/img/general/fulls/pic01.jpg" alt="" /> --}}
                            <img src="/img/seyi/seyi1.jpeg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Aliquam</a></h3>
                        <p>Ipsum dolor sit amet</p>
                    </header>
                </article>
                <article>
                    <span class="image">
                        {{-- <img src="/img/general/fulls/pic02.jpg" alt="" />   --}}
                        <img src="/img/seyi/seyi2.jpeg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Tempus</a></h3>
                        <p>feugiat amet tempus</p>
                    </header>
                </article>
                <article>
                    <span class="image">
                        {{-- <img src="/img/general/fulls/pic03.jpg" alt="" /> --}}
                          <img src="/img/seyi/seyi3.jpeg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Magna</a></h3>
                        <p>Lorem etiam nullam</p>
                    </header>
                </article>
                <article>
                    <span class="image">
                        <img src="/img/general/fulls/pic04.jpg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Ipsum</a></h3>
                        <p>Nisl sed aliquam</p>
                    </header>
                </article>
                <article>
                    <span class="image">
                        <img src="/img/general/fulls/pic05.jpg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Consequat</a></h3>
                        <p>Ipsum dolor sit amet</p>
                    </header>
                </article>
                <article>
                    <span class="image">
                        <img src="/img/general/fulls/pic06.jpg" alt="" />
                    </span>
                    <header class="major">
                        <h3><a href="landing.html" class="link">Etiam</a></h3>
                        <p>Feugiat amet tempus</p>
                    </header>
                </article>
            </section>

            <!-- Two -->
            <section id="two">
                <div class="inner">
                    <header class="major">
                        <h2>Massa libero</h2>
                    </header>
                    <p>Nullam et orci eu lorem consequat tincidunt vivamus et sagittis libero. Mauris aliquet magna
                        magna sed nunc rhoncus pharetra. Pellentesque condimentum sem. In efficitur ligula tate urna.
                        Maecenas laoreet massa vel lacinia pellentesque lorem ipsum dolor. Nullam et orci eu lorem
                        consequat tincidunt. Vivamus et sagittis libero. Mauris aliquet magna magna sed nunc rhoncus
                        amet pharetra et feugiat tempus.</p>
                    <ul class="actions">
                        <li><a href="landing.html" class="button next">Get Started</a></li>
                    </ul>
                </div>
            </section>

        </div>

        <!-- Contact -->
        <section id="contact">
            <div class="inner">
                <section>
                    <form method="post" action="#">
                        <div class="fields">
                            <div class="field half">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" />
                            </div>
                            <div class="field half">
                                <label for="email">Email</label>
                                <input type="text" name="email" id="email" />
                            </div>
                            <div class="field">
                                <label for="message">Message</label>
                                <textarea name="message" id="message" rows="6"></textarea>
                            </div>
                        </div>
                        <ul class="actions">
                            <li><input type="submit" value="Send Message" class="primary" /></li>
                            <li><input type="reset" value="Clear" /></li>
                        </ul>
                    </form>
                </section>
                <section class="split">
                    <section>
                        <div class="contact-method">
                            <span class="icon solid alt fa-envelope"></span>
                            <h3>Email</h3>
                            <a href="#">i{{ $data['email'] }}</a>
                        </div>
                    </section>
                    <section>
                        <div class="contact-method">
                            <span class="icon solid alt fa-phone"></span>
                            <h3>Phone</h3>
                            <span>{{ $data['mobile'] }}</span>
                        </div>
                    </section>
                    <section>
                        <div class="contact-method">
                            <span class="icon solid alt fa-home"></span>
                            <h3>Address</h3>
                            <span>{{ $data['address'] }},
                                {{ $data['region'] }}, {{ $data['county'] }}<br />
                                {{ $data['postcode'] }}, {{ $data['country'] }}</span>
                        </div>
                    </section>
                </section>
            </div>
        </section>

        <!-- Footer -->
        <footer id="footer">
            <div class="inner">
                <ul class="icons">
                    <li><a href="#" class="icon brands alt fa-twitter"><span class="label">Twitter</span></a></li>
                    <li><a href="#" class="icon brands alt fa-facebook-f"><span class="label">Facebook</span></a></li>
                    <li><a href="#" class="icon brands alt fa-instagram"><span class="label">Instagram</span></a></li>
                    <li><a href="#" class="icon brands alt fa-github"><span class="label">GitHub</span></a></li>
                    <li><a href="#" class="icon brands alt fa-linkedin-in"><span class="label">LinkedIn</span></a></li>
                </ul>
                <ul class="copyright">
                    <li>&copy; Untitled</li>
                    <li>Design: <a href="https://html5up.net">HTML5 UP</a></li>
                </ul>
            </div>
        </footer>

    </div>

    <!-- Scripts -->
    <script src="/js/forty/jquery.min.js"></script>
    <script src="/js/forty/jquery.scrolly.min.js"></script>
    <script src="/js/forty/jquery.scrollex.min.js"></script>
    <script src="/js/forty/browser.min.js"></script>
    <script src="/js/forty/breakpoints.min.js"></script>
    <script src="/js/forty/util.js"></script>
    <script src="/js/forty/main.js"></script>

</body>

</html>