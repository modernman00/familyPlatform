@extends ('email')
@section('title', 'Token email')

@section('subject', "{$data['firstName']}'s {$data['eventType']}")


@section('content')

<img src ={{ getenv("IMG_CELEBRATE") }} width="50" height="50" alt="LOGO" />
<p>

   Hurray, {{ $data['emailHTMLContent'] }}. <br><br>


    <br>



@endsection
