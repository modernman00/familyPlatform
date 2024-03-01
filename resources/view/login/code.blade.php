@extends ('baseBulma')
@section('title', 'Code')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo">

<div class="styleform_header">
      <h3>
        Please, enter the CODE to verify your identity
    </h3>

</div>



    <hr class="my-2">
    <form action="" method="post" class="styleform_form" id="codeForm" class="codeForm">
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