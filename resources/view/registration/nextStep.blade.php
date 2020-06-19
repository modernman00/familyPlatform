@extends('base')

@section('title','next step')

@section('content')

<h1> Ref: {{ $_SESSION['id'] }} </h1><br><br>
    <h3>{{ $_SESSION['firstName'] }}, thanks for the applications.</h3> 

@endsection