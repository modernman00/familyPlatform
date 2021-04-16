@extends('base')

@section('title','ERROR')

@section('content')

<h1 class="text-center">THERE IS AN ERROR</h1>

<div class="error">

    @foreach($errors as $errors)

    <div class="alert alert-danger" role="alert">
        {{ $errors }}
    </div>



@endforeach


</div>



@endsection
