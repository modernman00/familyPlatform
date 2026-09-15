@extends('layouts.landing_layout')
@section('title', 'Admin Portal Login')
@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0 rounded-lg mt-4">
                <div class="card-header bg-dark text-white text-center py-4">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 48px;" class="mb-2">
                    <h3 class="font-weight-light my-2">Administrative Portal</h3>
                    <p class="small text-muted mb-0">Secure Zero-Trust Access Gateway</p>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST" id="lasu" class="lasu styleForm" enctype="multipart/form-data">
                        @php
                            $formArray = [
                                'lasu_notification' => 'showError',
                                'email' => 'email',
                                'password' => 'password',
                                'totp_section' => [
                                    'mixed',
                                    'label'       => ['Google Authenticator Code (2-FA)'],
                                    'attribute'   => ['totp_code'],
                                    'placeholder' => ['6-Digit Authenticator Code (leave blank if 2-FA not enabled)'],
                                    'inputType'   => ['text'],
                                    'icon'        => ['<i class="fas fa-shield-alt"></i>'],
                                ],
                                'checkbox' => 'Remember this session',
                                'token' => 'token',
                                'Login' => [
                                    'button_captcha',
                                    'js' => 'LoginSubmission',
                                    'key' => getenv('RECAPTCHA_SITE_KEY'),
                                    'action' => 'login'
                                ],
                                'showPassword' => 'showPassword',
                            ];

                            $form = new Src\BuildFormBulma($formArray);
                            $form->genForm();
                        @endphp
                    </form>
                </div>
                <div class="card-footer text-center py-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center px-3">
                        <a href="/login/forgot?verify=1" class="small text-primary font-weight-bold">
                            <i class="fa fa-key mr-1"></i> Forgot Password?
                        </a>
                        <a href="/login/changePW" class="small text-secondary">
                            <i class="fa fa-lock mr-1"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection



