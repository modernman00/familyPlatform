@extends('base')

@section('title','Olaoguns')
@section('content')

<header class="masthead">
  <div class="container">
    <div class="intro-text">
      {{-- <div class="alert alert-warning alert-dismissible fade show">

        In order to optimise our website and to be able to continuously improve it, we use cookies. And, by continuing
        to use the website, you agree to the use of <strong>Cookies</strong>. For more information, please see our <a
          href="https://loaneasyfinance.com/policy">Cookies Policy</a>. You can delete and / or block cookies by
        changing your browser settings.

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
      </div> --}}
      <div class="intro-heading text-uppercase">THE OLAOGUNS</div>
      <div class="intro-lead-in">
          <form class="form-inline subscribeForm" method="POST" action="/subscribe" >
            <div class="input-group mb-3">
                <input type="email" class="form-control homePageSubscribe" size="80" placeholder="enter your email address" name="email"
                    id="email" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger"> Subscribe</button>
                </div>
            </div>
        </form>
      </div>

      <div class="row">

           <div class="col ">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/message">Message</a>
          </div>
          <div class="col">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/login">Login</a>
          </div>

           <div class="col">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/about">About</a>
          </div>

          <div class="col">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/register">Register</a>
          </div>
       

  
      </div>
  

    </div>
  </div>
</header>



   {{-- <div class="header">
        <h1>The Olaoguns</h1>
        <i> We are set for greater height</i>
        <form class="form-inline subscribeForm" method="POST" action="/subscribe" >
            <div class="input-group mb-3">
                <input type="email" class="form-control homePageSubscribe" size="80" placeholder="enter your email address" name="email"
                    id="email" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger"> Subscribe</button>
                </div>
            </div>
        </form>
    </div> --}}

    




@endsection