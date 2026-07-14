@extends('email')

@section('title', 'Request Status Update')
@section('subject', 'Update on Your Family Network Request')

@section('content')
<p style="margin-bottom: 20px;">
    Hi <strong>{{ $data['firstName'] }}</strong>,
</p>

<div style="background-color: #f8fafc; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 25px;">
    <p style="font-size: 18px; line-height: 1.6; color: #1e293b; margin: 0;">
        <strong>{{ $data['approverName'] }}</strong> has <strong>{{ $data['decision'] }}</strong> your request to join their family network.
    </p>
    
    @if($data['decision'] === 'approved')
        <p style="margin-top: 15px; margin-bottom: 0; color: #64748b;">You can now view their posts, explore their family tree, and interact with their profile!</p>
    @else
        <p style="margin-top: 15px; margin-bottom: 0; color: #64748b;">If you think this was a mistake, you can try sending another request later.</p>
    @endif
</div>

<div style="text-align: center;">
    <a href="{{ getenv('MIX_APP_URL2') }}member/seeProfile/{{ $data['id'] ?? '' }}" style="background-color: #0a66c2; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Go to Profile</a>
</div>
@endsection