@extends ('email')
@section('title', 'email')

@section('subject')
MEMBERSHIP DECISION NOTIFICATION for {{ strtoupper($data['firstName']) }} {{ $data['lastName'] }}
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

    Hello
    {{ strtoupper($data['firstName']) }} {{ $data['lastName'] }}, <br><br> Your application for membership of the {{ $data['lastName'] }} family platform has
    been approved.<br><br>

    You can now log in at {{ getenv('APP_URL') }}login with your details and upload your profile pics.

    <br><br>Thanks.<br><br>


@endsection