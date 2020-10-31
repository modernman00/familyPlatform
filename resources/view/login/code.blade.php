@extends ('baseBulma')
@section('title', 'Code')
@section('content')

<div class="styleForm" style="margin-top: 4rem;">
<h3 style="margin-left:20%; margin-right:20%;">Please, enter the CODE to verify your identity</h3>


<hr class="my-2">
<form action="/code" method="post" style="margin-left:20%; margin-right:20%;">
    <div class="form-group">
        <br>
        <div class='row'>

            <?php

                    $formArray = [
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