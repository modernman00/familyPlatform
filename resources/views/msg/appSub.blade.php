@extends ('email')
@section('title', 'Application Received')

@section('subject', 'Welcome to the Family')

@if (!empty($data['id']))
@section('reference') {{ $data['id'] }} @endsection
@endif

@section('name')
{{ $data['firstName'] }} {{ $data['lastName'] }}
@endsection

@section('content')

<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    We are thrilled to welcome you to the FamilyPlatform! Your registration has been successfully processed and your account is now active.
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid var(--brand-primary); padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0; color: #334155; font-size: 15px;">
        <strong>Next Steps:</strong> Please return to the login page and sign in with your email and password. For your security, a verification code will be sent to your email to confirm your identity during your first login.
    </p>
</div>

<p style="margin-bottom: 0;">
    If you have any questions in the meantime, please don't hesitate to reach out.
</p>

@endsection