@extends('email')

@section('title', 'New Event Created')
@section('subject', 'Mark Your Calendars!')

@section('content')
<p style="margin-bottom: 20px;">
    Hello Family Member,
</p>

<p style="margin-bottom: 25px;">
    A new event has been created on the family network! Here are the details:
</p>

<div style="background-color: #f8fafc; border-radius: 8px; padding: 25px; margin-bottom: 25px; text-align: center; border: 1px solid #e2e8f0;">
    <h2 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 10px;">{{ $data['eventName'] }}</h2>
    
    <p style="font-size: 16px; color: #0a66c2; font-weight: 600; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px;">
        {{ date('F d, Y', strtotime($data['eventDate'])) }}
    </p>

    <div style="text-align: left; padding: 15px; background-color: #ffffff; border-radius: 6px; border-left: 4px solid #cbd5e1;">
        <p style="margin: 0; line-height: 1.6; color: #334155; font-size: 15px;">
            {{ $data['eventDescription'] ?? 'No description provided.' }}
        </p>
    </div>
</div>

<div style="text-align: center;">
    <a href="{{ getenv('MIX_APP_URL2') }}profilePage" style="background-color: #0a66c2; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">View Event Details</a>
</div>
@endsection
