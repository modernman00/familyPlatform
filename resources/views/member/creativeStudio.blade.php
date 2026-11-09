@extends('layouts.profileBase')

@section('title', 'Creative Studio — FamilyPlatform')
@section('meta_description', 'Design family flyers, posters and festive greeting cards in one place.')

@section('content')
<div class="container py-4" style="max-width:1000px;">
    @include('member.creativeStudioTabs', ['creativeTab' => 'hub'])

    <header class="mb-4">
        <h1 class="fs-2 fw-bold mb-2">Creative Studio</h1>
        <p class="text-body-secondary mb-0">Design something beautiful for your family, then share it on the platform or with friends.</p>
    </header>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <a href="/poster-studio" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm">
                    <i class="bi bi-easel2-fill text-warning fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Flyers &amp; Posters</h2>
                    <p class="text-body-secondary small mb-3">Reunions, anniversaries, naming ceremonies and milestone celebrations.</p>
                    <span class="btn btn-warning text-dark fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Flyer Studio</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/member/festive-cards" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm">
                    <i class="bi bi-envelope-heart-fill text-danger fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Festive Cards</h2>
                    <p class="text-body-secondary small mb-3">Christmas, Easter, Eid, Diwali and more, sent as living envelopes in seven design styles.</p>
                    <span class="btn btn-danger text-white fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Card Studio</span>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
