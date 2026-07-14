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
    We are thrilled to let you know that we've securely received your application! Our admin team is currently reviewing your details, and you can expect an update from us within the next <strong>24 hours</strong>.
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #0a66c2; padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0; color: #334155; font-size: 15px;">
        <strong>Next Steps:</strong> Once your application is approved, you will be able to log in securely and access the full family social network.
    </p>
</div>

<p style="margin-bottom: 0;">
    If you have any questions in the meantime, please don't hesitate to reach out.
</p>

@endsection