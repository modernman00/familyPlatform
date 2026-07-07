@extends('layouts.landing_layout')
@section('title', 'forgot_password')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="auth-title h2">Change Password</h1>
                    <p class="auth-subtitle">Please, enter the new password</p>
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
