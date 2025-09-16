@extends('baseBulmaForm')
@section('title', 'Token')
@section('content')


    <div class="styleform_header">
        <h3>Please, enter the code to verify your identity</h3>
    </div>


    <hr class="my-2">
    <form action="" id="code" method="post" class="styleform_form code">
        <div class="form-group">
            <br>
            <div class='row'>

                @php

                    $formArray = [
                        "code_notification"=> "showError",
                        'code' => 'text',
                        'token' => 'token',
                          'submit' => 'button',
                    ];

                    $form = new App\classes\BuildFormBulma($formArray);
                    $form->genForm();

                 @endphp

                <br>


    </form>




@endsection