@extends('layouts.profileBase')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@push('styles')
    <link rel="stylesheet" href="public/css/profilepage.css">
@endpush
@section('content')


    {{-- @php 

   $url = getenv("APP_URL");
            $url = $url;
            loggedDetection($url, $data['email']);


@endphp --}}
    <div class="container-main">

        @includeIf('member.includes.leftColumn')

        @includeIf('member.includes.middleColumn')

        @includeIf('member.includes.rightColumn')

        @includeIf('member.modals.post')
        @includeIf('member.modals.editProfile')
        @includeIf('member.modals.share')
        @includeIf('member.modals.createEvent')


    </div>



@endsection
