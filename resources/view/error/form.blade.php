@extends('base')

@section('title','ERROR')

@section('content')

<br><br>

<h1>THERE ARE ERRORS: </h1>

@foreach ($errors as $error)

   <li> {{ $error }}</li>
    
@endforeach

@endsection