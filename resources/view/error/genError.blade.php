@extends('baseError')

@section('title','ERROR')

@section('content')

@if ($error)

<justify-content-center vh-100 bg-primary">
        <h1 class="display-1 fw-bold text-white">404</h1>
        <h2> {{ $error }}</h2>
    </div>

@else

<justify-content-center vh-100 bg-primary">
        <h1 class="display-1 fw-bold text-white">404</h1>
        <h2> We can't find the URL</h2>
    </div>
    
@endif



















@endsection