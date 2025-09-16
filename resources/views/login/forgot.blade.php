@extends('baseBulmaForm')
@section('title', 'forgot_password')
@section('content')


    <div class="styleform_header">
        <h3>Please, enter the email to verify your identity</h3>
    </div>


    <hr class="my-2">
    <form action="/forgot" id="forgot" method="post" class="styleform_form">
        <div class="form-group">
            <br>
            <div class='row'>

                @php

                    $formArray = [
                        'forgot_notification' => 'showError',
                        'email' => 'email',
                        'token' => 'token',
                        'submit' => 'button',
                    ];

                    $form = new Src\BuildFormBulma($formArray);
                    $form->genForm();
                @endphp

                <br>

                <div class="g-recaptcha" name="g-recaptcha-response" data-sitekey="{{ $_ENV['RECAPTCHA_KEY'] }}"
                    data-theme="dark"></div>
                <br>

    </form>




@endsection
