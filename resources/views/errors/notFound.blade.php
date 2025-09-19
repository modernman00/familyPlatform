@extends('baseError')

@section('title','ERROR')

@section('content')

<br><br>

<div class="jumbotron error">
        <div class="container">
                <h1 class="display-4 text-center">HMMM - WHERE DO YOU WANT TO GO? </h1>
                <div class="alert alert-danger text-center lead" role="alert">
                        @isset($login)

                        <a href=/{{ $login }}> GO TO YOUR PAGE</a> @else <a href="/"> GO TO THE HOME PAGE</a>

                        @endisset
                </div>
        </div>
</div>


@endsection