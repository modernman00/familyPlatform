@extends('layouts.landing_layout')
@section('title', 'Login')
@section('content')

    {{-- <div class="styleForm" style="margin-top: 2rem;"> --}}
    
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="auth-title h2">Admin Login</h1>
                    <p class="auth-subtitle">Log in </p>
                </div>
        <form action="" method="" id="lasu" class="lasu styleForm" enctype="multipart/form-data">

            <div class='form-group'>

                <div class='row'>

                    @php
                        $formArray = [
                            'lasu_notification' => 'showError',
                            'email' => 'email',
                            'password' => 'password',
                            'type' => 'text',
                            'checkbox' => 'Remember me',
                            'token' => 'token',
                            'Login' => [
                                'button_captcha',
                                'js' => 'LoginSubmission',
                                'key' => getenv('RECAPTCHA_KEY'),
                                'action' => 'login'
                            ],
                            'showPassword' => 'showPassword',
                        ];

                        $form = new Src\BuildFormBulma($formArray);
                        $form->genForm();
                    @endphp

                    <br>
                    <a href="/forgot?verify=1" class="is-link"> Forgot password? Please click this link</a>

                    <br><br>

                </div>
            </div>

        </form>

    {{-- </div> --}}


@endsection


