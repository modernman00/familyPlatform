@extends('layouts.profileBase')

@section('title', 'Creative Studio — FamilyPlatform')
@section('meta_description', 'Curate your family lineage in Tree Studio, design family flyers, posters and festive greeting cards, and craft 4K reels in Living Archives Studio.')

@section('content')
<div class="container py-4" style="max-width:1080px;">
    @include('member.creativeStudioTabs', ['creativeTab' => 'hub'])

    <header class="mb-4 d-flex align-items-center gap-3">
        <span class="creative-3d-icon-wrap creative-3d-icon-wrap--lg" aria-hidden="true">
            <span class="creative-3d-gem">
                <span class="creative-3d-sheen"></span>
                <svg class="creative-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.5L14.4 7.8L20.1 8.5L15.8 12.4L17 18.1L12 15.2L7 18.1L8.2 12.4L3.9 8.5L9.6 7.8L12 2.5Z" fill="#fef08a" stroke="#ffffff" stroke-width="1.2" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="creative-3d-spark creative-3d-spark--1"></span>
            <span class="creative-3d-spark creative-3d-spark--2"></span>
        </span>
        <div>
            <h1 class="fs-2 fw-bold mb-1">Creative Studio</h1>
            <p class="text-body-secondary mb-0">Curate your living family tree, design bespoke flyers, posters and festive greeting cards, or craft 4K family reels in Living Archives Studio.</p>
        </div>
    </header>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <a href="/familyStudio" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm d-flex flex-column">
                    <i class="bi bi-diagram-3-fill text-primary fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Tree Studio</h2>
                    <p class="text-body-secondary small mb-3">Curate your family lineage, add parents, partners and children, and document generational stories.</p>
                    <span class="btn btn-primary text-white fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Tree Studio</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/poster-studio" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm d-flex flex-column">
                    <i class="bi bi-easel2-fill text-warning fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Flyers &amp; Posters</h2>
                    <p class="text-body-secondary small mb-3">Reunions, anniversaries, naming ceremonies and milestone celebrations.</p>
                    <span class="btn btn-warning text-dark fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Flyer Studio</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/member/festive-cards" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm d-flex flex-column">
                    <i class="bi bi-envelope-heart-fill text-danger fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Festive Cards</h2>
                    <p class="text-body-secondary small mb-3">Christmas, Easter, Eid, Diwali and more, sent as living envelopes in seven design styles.</p>
                    <span class="btn btn-danger text-white fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Card Studio</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="/family-studio" class="text-decoration-none d-block h-100">
                <div class="card h-100 border rounded-4 p-4 shadow-sm d-flex flex-column">
                    <i class="bi bi-film text-dark fs-2 mb-3"></i>
                    <h2 class="fs-5 fw-semibold text-body">Living Archives Studio</h2>
                    <p class="text-body-secondary small mb-3">Browser-native 4K Reel Studio with multi-angle Chrono-Sync matrix, heritage motion templates and direct MP4 downloads.</p>
                    <span class="btn btn-dark text-white fw-bold mt-auto align-self-start" style="min-height:44px;white-space:nowrap;">Open Living Archives</span>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
