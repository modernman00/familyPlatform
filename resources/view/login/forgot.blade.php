@extends ('baseBulma')
@section('title', 'forgot_password')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo">

    <div class="styleform_header">
    <h3>Please, enter the email to verify your identity</h3>
    </div>


    <hr class="my-2">
    <form action="/login/forgot" id="forgotPassword" method="post" class="styleform_form">
        <div class="form-group">
            <br>
            <div class='row'>

                <?php

                    $formArray = [
                        'forgotPassword_notification'=>'showError',
                        'email' => 'email',
                        'token' => 'token',
                        'button' => 'submit'
                    ];

                    $form = new App\classes\BuildFormBulma($formArray);
                    $form->genForm();

                ?>
                <br>

            </div>

    </form>
</div>



@endsection