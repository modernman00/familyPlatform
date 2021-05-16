@extends('baseError')

@section('title','ERROR')

@section('content')

    <div class="jumbotron error">
        <div class="container">
            <h1 class="display-4 text-center">THERE IS AN ERROR</h1>
             <p class="alert alert-danger text-center lead" role="alert">
        {{ $error }}
    </p>
        </div>
    </div>







@endsection