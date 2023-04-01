@extends ('baseBulma')

@section('title', 'Change Password')

@section('content')
    
    <hr class="my-2">
    
    <form action="/login/changePW" id="changePassword" method="post" class="styleform_form">

            <h3 >Please, enter your new password</h3>
        <p id="changePasswordErr"></p>
        <div class="form-group">
            <br>
            <div class='row'>

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
