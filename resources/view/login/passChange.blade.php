@extends ('baseBulma')

@section('title', 'Change Password')

@section('content')

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo">
    
    <hr class="my-2">
    
    <form action="/login/changePW" id="changePassword" method="post" class="styleform_form">

            <h3 >Please, enter your new password</h3>
        <p id="changePasswordErr"></p>
        <div class="form-group">
            <br>
            <div class='row'>
                {{-- <input type="hidden" id="email_id" placeholder="alex@gmail.com" class="input email is-medium" autocomplete="username" name="email" value=""> --}}

                <?php

                    $formArray = [
                        'changePassword_notification' => 'showError',
                        'password' => 'password',
                        'confirm_password' => 'password',
                        'token' => 'token',
                        'button' => 'submit'                        
                    ];

                    $form = new App\classes\BuildFormBulma($formArray);
                    $form->genForm();

                ?>
                <br>

            </div>

    </form>

@endsection
