@extends('base')

@section('title','Register')
@section('data-page-id', 'registration')
@section('content')


<div class="styleForm">

  <form action="/register" method="POST" class="register" enctype="multipart/form-data">

    @php
    $token = urlencode(base64_encode((random_bytes(32))));
    $_SESSION['token'] = $token;
    @endphp

    {{--  <br><br><br>  --}}

    {{--  <h2 class="text-center text-uppercase">Personal Details</h2>  --}}
    <br><br><br>
    <div class="row" id="personal"></div><hr>
      {{--  <div class="row" id="personal2"></div><hr>  --}}
    {{--  <br><br>  --}}

    {{--  <h2 class="text-center text-uppercase">Contact Information</h2>  --}}
  
    <div id="contact"></div><hr>

    {{--  <br><br>  --}}
    {{--  <h2 class="text-center text-uppercase">Work details</h2>  --}}
  
    <div id="work"></div><hr>

   
    {{--  <h2 class="text-center text-uppercase">Interests</h2>
    <br>  --}}
    <div id="interest"></div><hr>
{{--  
    {{--  <br><br>  --}}  
    {{--  <h2 class="text-center text-uppercase">Create Account</h2><br>  --}}
    <div id="account"></div><hr>

    {{--  <br><br>  --}}
    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" id="checkbox">
      <label class="form-check-label" for="checkbox">By submitting this form, you agree handling your information as
        outlined in our <a href="/privacy"> PRIVACY POLICY</a></label>
    </div>

    <!-- Button trigger modal -->

    <input type="hidden" id="token" name="token" value={{ $token }}>

    <input class="btn btn-primary btn-lg btn-block submit" type="button" id="submit" name="submit" value="Submit Form">




    {{--  <br><br>  --}}


  </form>

</div>


@includeIf("registration.include.kidsModal")
{{-- @include('registration/include/siblingsModal') --}}


@endsection