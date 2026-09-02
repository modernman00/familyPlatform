@extends('email')

@section('title', 'Join Your Family Network')
@section('subject', 'A Recommendation From a Family Member')

@section('content')
<p style="margin-bottom: 20px;">
    Hello <strong>{{ $data['name'] }}</strong>,
</p>

<p style="margin-bottom: 20px;">
    I hope this message finds you well. <strong>{{ $data['yourName'] }}</strong> recently joined our family network and suggested you join too!
</p>

<p style="margin-bottom: 20px;">
    Our family network is a private space where we share updates, explore our ancestry through the family tree, and stay informed about upcoming family events.
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #00bfa5; padding: 20px; border-radius: 4px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 10px; font-weight: 600; color: #64748b; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;">Your Private Family Code</p>
    <span style="font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: 2px;">{{ $data['familyCode'] ?? ($data['famCode'] ?? '') }}</span>
</div>

<p style="margin-bottom: 25px;">
    To get started, simply click the button below and use the family code above during your registration.
</p>

@php
    $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://familyplatform.test')), '/');
    $queryParams = array_filter([
        'famCode' => $data['familyCode'] ?? ($data['famCode'] ?? ''),
        'name'    => $data['name'] ?? '',
        'email'   => $data['email'] ?? '',
    ]);
    $registerUrl = $baseUrl . '/register' . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
@endphp

<div style="text-align: center;">
    <a href="{{ $registerUrl }}" style="background-color: #00bfa5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;">Join Network Now</a>
</div>
@endsection