@extends ('email')
@section('title', 'email')



@section('content')

Hello {{ $app['firstName'] }}, <br> <br>


You have {{ $app['decision'] }} {{ $app['requesterName'] }}'s request to join your family network <br><br>


@endsection

