@extends('base2')

@section('title', 'Home page')

@section('content')

{{ !empty($name) ? "Jame" : " Jibs"  }}


@endsection