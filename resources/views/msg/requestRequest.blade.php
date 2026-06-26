@extends('layouts.email_premium')

@section('title', 'Request Status Update')
@section('subtitle', 'Update on your family network request')
@section('greeting', "Hi {$data['firstName']},")

@section('content')
<div style="text-align: center; padding: 10px 0;">
    <p style="font-size: 18px; line-height: 1.6; color: #1c1e21; margin: 0;">
        <strong>{{ $data['approverName'] }}</strong> has <strong>{{ $data['decision'] }}</strong> your request to join their family network.
    </p>
    @if($data['decision'] === 'approved')
        <p style="margin-top: 15px; color: #6c757d;">You can now see their posts and interact with their profile.</p>
    @else
        <p style="margin-top: 15px; color: #6c757d;">If you think this was a mistake, you can try sending another request later.</p>
    @endif
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}member/seeProfile/{{ $data['id'] ?? '' }}" class="btn btn-primary">Go to Profile</a>
@endsection