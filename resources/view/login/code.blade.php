@extends ('baseBulma')
@section('title', 'Code')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">

<div class="styleform_header">
      <h3>
        Please, enter the CODE to verify your identity
    </h3>

</div>

    <hr class="my-2">
    <form action="" method="post" class="styleform_form" id="codeForm">
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