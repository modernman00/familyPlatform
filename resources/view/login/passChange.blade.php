@extends ('baseBulma')

@section('title', 'Change Password')

@section('content')

        <h3 >Please, enter your new password</h3>

    
    <hr class="my-2">
    <form action="/login/changePW" method="post" style="margin-left:20%; margin-right:20%;">
        <div class="form-group">
            <br>
            <div class='row'>

                <?php

                    $formArray = [
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
