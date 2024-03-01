@extends('baseBulma')

@section('title', 'login')

@section('data-page-id', 'login')
@section('content')


<div class="styleForm" style="margin-top: 2rem;">

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo"
    style="margin-left:43%; margin-bottom:5rem;">

  <form action="{{ $formAction }}" method="POST" class="loginNow styleform_form" id="loginNow"
    enctype="multipart/form-data">

    <div id="setLoader" class="loader" style="display: none;">
    </div>
     
      <div class="notification" id="loginNow_notification" style="display: none;">

        <p id="error"></p>
      </div>



    <div class="form-group">

      @php

      // if $formAction is '/lasu', add code to the input
      if($formAction === '/lasu') {
        $formArray = [
           'email' => 'email',
      'password' => 'password',
      'type' => 'text',
      'checkbox'=> 'Remember me',
      'token' => 'token',
      'button' => 'button'
        ];
      } else {
          $formArray = [
      'email' => 'email',
      'password' => 'password',
      'checkbox'=> 'Remember me',
      'token' => 'token',
      'button' => 'button'
      ];
      }

      $form = new App\classes\BuildFormBulma($formArray);
      $form->genForm();

      @endphp


      <label class="checkbox">
        <input type="checkbox" id="showPassword_id">
        Show Password
      </label><br>

      <a href="/login/forgot"> Forgot password? Please click this link</a>
      <br><br>

    </div>


  </form>

</div>

<script>
  sessionStorage.setItem('loginURL1', @json($formAction))  
  
//          function onClick(e) {
//   e.preventDefault();
//   grecaptcha.enterprise.ready(async () => {
//     const token = await grecaptcha.enterprise.execute('6Ld4P5InAAAAAOXcbqWaN3VmT9qXQp0EMEsIcMyS', {action: 'LOGIN'});
//     // IMPORTANT: The 'token' that results from execute is an encrypted response sent by
//     // reCAPTCHA Enterprise to the end user's browser.
//     // This token must be validated by creating an assessment.
//     // See https://cloud.google.com/recaptcha-enterprise/docs/create-assessment
//   });
// }





  
</script>



@endsection