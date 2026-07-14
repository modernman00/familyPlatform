@extends('email')

@section('title', 'Membership Cancelled')
@section('subject', 'Membership Application Cancelled')

@if (!empty($data['id']))
@section('reference') {{ $data['id'] }} @endsection
@endif

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['firstName'] }} {{ $data['lastName'] }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    We are writing to inform you that your application for membership to the <strong>{{ $data['lastName'] }}</strong> family platform has been cancelled.
</p>

<div style="background-color: #f8fafc; border-left: 4px solid #94a3b8; padding: 15px 20px; border-radius: 4px; margin-bottom: 20px;">
    <p style="margin: 0; color: #475569; font-size: 15px;">
        If you believe this was done in error or you wish to re-apply, please contact your family platform administrator.
    </p>
</div>
@endsection