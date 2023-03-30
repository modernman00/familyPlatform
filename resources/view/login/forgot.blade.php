@extends ('baseBulma')
@section('title', 'forgot_password')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">
    <h3 style="margin-left:20%; margin-right:20%;">Please, enter the email to verify your identity</h3>


    <hr class="my-2">
    <form action="/login/forgot" id="forgotPassword" method="post">
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