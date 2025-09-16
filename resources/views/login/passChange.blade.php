@extends('baseBulmaForm')
@section('title', 'forgot_password')
@section('content')


    <div class="styleform_header">
        <h3>Please, enter the changePW to verify your identity</h3>
    </div>


    <hr class="my-2">
    <form action="" id="changePW" class="styleform_form changePW">
        <div class="form-group">
            <br>
            <div class='row'>

                @php

                    $formArray = [
                        'changePW_notification' => 'showError',
                        'password' => 'password',
                        'confirm_password' => 'password',
                        'token' => 'token',
                           'submit' => 'button',    
                           'showPassword' => 'showPassword'                    
                    ];

                     $form = new Src\BuildFormBulma($formArray);
                    $form->genForm();

                 @endphp

                <br>


    </form>


@endsection
