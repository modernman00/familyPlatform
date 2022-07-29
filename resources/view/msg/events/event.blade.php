@extends ('email')
@section('title', 'Token email')

@section('subject', 'SUBJECT: New Event')


@section('content')
<p>
@if ($data)
   Hurray, {{ $data['firstName'] }} is celebrating {{ $data['eventName'] }} on the {{ $data['eventDate'] }} . <br><br>
@else 
    There is a problem with the authentication.
@endif


    <br>

@endsection
