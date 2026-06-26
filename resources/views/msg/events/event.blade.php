@extends('layouts.email_premium')

@section('title', 'New Event Created')
@section('subtitle', 'Mark your calendars!')
@section('greeting', "Hi Family Member,")

@section('content')
<div style="text-align: center; padding: 10px 0;">
    <h2 style="font-size: 20px; font-weight: 700; color: #1c1e21; margin: 0 0 10px;">{{ $data['eventName'] }}</h2>
    <p style="font-size: 16px; color: #6c757d; margin-bottom: 20px;">
        <i class="bi bi-calendar-event"></i> {{ date('F d, Y', strtotime($data['eventDate'])) }}
    </p>
    <div style="text-align: left; padding: 20px; background-color: #ffffff; border-radius: 8px; border: 1px solid #eee;">
        <p style="margin: 0; line-height: 1.6; color: #4b4b4b;">
            {{ $data['eventDescription'] ?? 'No description provided.' }}
        </p>
    </div>
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}profilePage" class="btn btn-primary">View Event Details</a>
@endsection
