@extends('base')

@section('title','REGISTER')
@section('content')

<div class="styleForm">

  
<form action="/register" method="POST" class="register">

  <?php $token = urlencode(base64_encode((random_bytes(32))));
$_SESSION['token'] = $token;
?>

  <br><br><br>

  <h2 class="text-center text-uppercase">Personal Details</h2> <br>

  <div class="row" id="personal"></div>




  <h2 class="text-center text-uppercase">Contact</h2>
  <div id="contact"></div>

  <h2 class="text-center text-uppercase">Work details</h2>
  <div id="work"></div>

    <h2 class="text-center text-uppercase">Interests</h2>
  <div id="interest"></div>

      <h2 class="text-center text-uppercase">Create Account</h2>
  <div id="account"></div>



  <input type="hidden" name="token" value={{ $token }}>

  <button type="button" id="submit" class="btn btn-primary btn-lg">Submit</button>
</form>

</div>




@endsection