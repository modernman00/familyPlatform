@extends ('email')
@section('title', 'Token email')

@section('subject', 'SUBJECT: New Event')


@section('content')
<p>
@if ($data)
   Hurray, {{ $data['firstName'] }}'s {{ $data['eventName'] }} is on {{ $data['eventDateFormatted'] }}. <br><br>
@else 
    There is a problem with the authentication.
@endif


    <br>

@endsection
