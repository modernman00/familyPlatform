@extends ('email')
@section('title', 'email')

@section('subject') 
    MEMBERSHIP DECISION NOTIFICATION for {{ $data['firstName'] }} {{ $data['lastName'] }} 
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

 Hello
    {{ $data['firstName'] }} {{ $data['lastName'] }}, <br><br> Your application for membership of the Olaogun family platform has
    been declined.<br><br>Thanks.<br><br>
           

@endsection