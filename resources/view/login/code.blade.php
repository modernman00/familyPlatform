@extends ('baseBulma')
@section('title', 'Code')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">

    <h3 style="margin-left:20%; margin-right:20%;">
        Please, enter the CODE to verify your identity
    </h3>

    {{-- <p id="codeError"></p> --}}

  

    <hr class="my-2">
    <form action="" method="post" id="codeForm">
        <div class="form-group">
            <br>
            <div class='row'>
                
                <?php

                    $formArray = [
                        "codeForm_notification"=> "showError",
                        'code' => 'text',
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