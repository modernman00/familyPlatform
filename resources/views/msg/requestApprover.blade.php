@extends('layouts.email_premium')

@section('title', 'Update')
@section('subtitle', 'Family Network Request Status')
@section('greeting', "Hello {$data['name']},")

@section('content')
<div style="line-height: 1.6; color: #4b4b4b;">
   Hello {{ $app['firstName'] }}, <br> <br>


You have {{ $app['decision'] }} {{ $app['requesterName'] }}'s request to join your family network <br><br>



</div>
@endsection



