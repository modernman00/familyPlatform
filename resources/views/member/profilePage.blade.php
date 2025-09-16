@extends('layouts.profileBase')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@section('content')


 @includeIf('member.includes.leftColumn')

 @includeIf('member.includes.middleColumn')

 @includeIf('member.includes.rightColumn')

 @includeIf('member.modals.post')
@includeIf('member.modals.editProfile')
@includeIf('member.modals.share')
@includeIf('member.modals.createEvent')




     <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle" id="darkModeToggle">
        <i class="bi bi-moon-fill"></i>
    </div>


@endsection