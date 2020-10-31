@extends ('email')
@section('title', 'email')

@section('subject', 'Applicaton success')

@section('reference') {{ $data['id'] }} @endsection
@section('name') 
{{ $data['firstName'] }} {{ $data['lastName'] }} 
@endsection

@section('content')

Your application has been successfully submitted. Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br><br>

If your application approved, then you should be able to log in to your account and access the family social network

@endsection