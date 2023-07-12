@extends ('email')
@section('title', 'email')

@section('subject') 
    MEMBERSHIP DECISION NOTIFICATION for {{ $data['firstName'] }} {{ $data['lastName'] }} 
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

 Hello
    {{ $data['firstName'] }} {{ $data['lastName'] }},<br><br> Your application for membership of the {{ $data['lastName'] }} family platform has
    been deleted from our system.<br><br> Thanks.<br><br>
           

@endsection