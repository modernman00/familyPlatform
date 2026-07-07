@extends('layouts.landing_layout')
@section('title', 'Token')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="auth-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="auth-title h2">Welcome Back</h1>
                    <p class="auth-subtitle">Log in to reconnect with your family network</p>
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

                    $form = new Src\BuildFormBulma($formArray);
                    $form->genForm();

                 @endphp

                <br>


    </form>




@endsection