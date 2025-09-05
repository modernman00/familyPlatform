@extends('baseBulmaForm')
@section('title', 'Login')
@section('content')

    {{-- <div class="styleForm" style="margin-top: 2rem;"> --}}

        <form action="" method="" id="login" class="login styleForm" enctype="multipart/form-data">

            <div class='form-group'>
                <br>
                <div class='row'>

                    @php

                        $formArray = [
                            'login_notification' => 'showError',
                            'email' => 'email',
                            'password' => 'password',
                            'checkbox' => 'Remember me',
                            'token' => 'token',
                            'submit' => 'button',
                            'showPassword' => 'showPassword',
                            'recaptcha' => 'recaptcha',
                        ];

                        $form = new Src\BuildFormBulma($formArray);
                        $form->genForm();

                    @endphp

                    <br>

                    {{-- <div class="g-recaptcha" data-sitekey="{{ $_ENV['RECAPTCHA_KEY'] }}" data-theme="dark"></div>
                    <br> --}}

                    <a href="/forgot?verify=1"> Forgot password? Please click this link</a>

                    <br><br>

                </div>
            </div>

        </form>

    {{-- </div> --}}




@endsection
