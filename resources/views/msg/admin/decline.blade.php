@extends('email')

@section('title', 'Membership Declined')
@section('subject', 'Membership Application Declined')

@if (!empty($data['id']))
@section('reference') {{ $data['id'] }} @endsection
@endif

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] }} {{ $data['lastName'] }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    We are writing to inform you that your application for membership to the <strong>{{ $data['lastName'] }}</strong> family platform has unfortunately been declined by the administrators.
</p>

<div style="background-color: #f8fafc; border-left: 4px solid #94a3b8; padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0; color: #475569; font-size: 15px;">
        If you have any questions or believe this was a mistake, please reach out directly to your family platform administrator.
    </p>
</div>
@endsection