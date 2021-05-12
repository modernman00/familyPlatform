@extends ('email')
@section('title', 'email')

@section('subject', 'Apply to join the OLAOGUN Family Network')

@section('name') 
{{ $data['name'] }}
@endsection

@section('content')

Your application has been successfully submitted. Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br><br>

If your application approved, then you should be able to log in to your account and access the family social network

@endsection