@extends ('email')
@section('title', 'Token email')

@section('subject', 'SUBJECT: LOGIN TOKEN')


@section('content')
<p>
@if ($data)
    Please use this token {{ $data['code'] }} to login to your account within the next ten minutes. <br><br>
@else 
    There is a problem with the authentication.
@endif


    <br>

@endsection
