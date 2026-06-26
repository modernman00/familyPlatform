@extends('layouts.landing_layout')

@section('title', 'Login')
@section('data-page-id', 'login')

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

                <form action="" method="" id="login" enctype="multipart/form-data">
                    <div class='form-group'>
                        @php
                            $formArray = [
                                'login_notification' => 'showError',
                                'email' => 'email',
                                'password' => 'password',
                                'checkbox' => 'Remember me',
                                'token' => 'token',
                                'submit' => 'button',
                                'showPassword' => 'showPassword'
                            ];

                            $form = new Src\BuildFormBulma($formArray);
                            $form->genForm();
                        @endphp

                        <div class="mt-4 text-center">
                            <a href="/forgot?verify=1" class="text-decoration-none" style="color: var(--brand-primary); font-weight: 500;">
                                Forgot password?
                            </a>
                        </div>
                        
                        <hr class="my-4" style="opacity: 0.1;">
                        
                        <div class="text-center">
                            <p class="text-muted small">New to Family Platform? <a href="/register" class="fw-bold text-decoration-none" style="color: var(--brand-primary);">Join now</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection







