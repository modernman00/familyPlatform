@extends('email')

@section('title', 'Request Status Update')
@section('subject', 'Family Network Request Status')

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>{{ $app['firstName'] }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    This is a quick update regarding your family network. You have successfully <strong>{{ $app['decision'] }}</strong> the request from <strong>{{ $app['requesterName'] }}</strong> to join your network.
</p>

<p style="margin-bottom: 0;">
    No further action is required from you at this time. Thank you for helping keep your family network secure and up to date!
</p>
@endsection
