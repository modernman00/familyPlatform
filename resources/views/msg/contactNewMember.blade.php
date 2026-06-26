@extends('layouts.email_premium')

@section('title', 'Join Your Family Network')
@section('subtitle', 'A recommendation from a family member')
@section('greeting', "Hello {$data['name']},")

@section('content')
<div style="line-height: 1.6; color: #4b4b4b;">
    <p>I hope this message finds you well. <strong>{{ $data['yourName'] }}</strong> recently joined our family network and suggested you join too!</p>
    
    <p>Our family network is a private space where we share updates, explore our ancestry through the family tree, and stay informed about upcoming family events.</p>

    <div style="margin: 25px 0; padding: 20px; background-color: #ffffff; border: 2px solid #eee; border-radius: 12px; text-align: center;">
        <p style="margin: 0 0 10px; font-weight: 600; color: #6c757d; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;">Your Private Family Code</p>
        <span style="font-size: 24px; font-weight: 800; color: #1c1e21; letter-spacing: 2px;">{{ $data['familyCode'] }}</span>
    </div>

    <p>To get started, simply click the button below and use the family code above during your registration.</p>
</div>
@endsection

@section('actions')
<a href="{{ getenv('MIX_APP_URL2') }}register" class="btn btn-primary">Join Network Now</a>
@endsection