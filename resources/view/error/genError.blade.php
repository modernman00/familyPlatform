@extends('baseError')

@section('title','ERROR')

@section('content')


<justify-content-center vh-100 bg-primary">
        <h1 class="display-1 fw-bold text-white">404</h1>
        <h2>  @isset($error) {{ $error }} @endisset</h2>

   
    </div>





















@endsection