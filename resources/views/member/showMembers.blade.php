@extends('layouts.profileBase')
@section('title', 'Family Directory & Members')
@section('data-page-id', 'allMembers')
@push('styles')
    <link rel="stylesheet" href="/public/css/showMembers.css?v={{ time() }}">
@endpush
@section('content')
<div class="container py-4">

    <!-- Google Stitch Hero Header -->
    <div class="members-header-stitch">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="stitch-hero-badge mb-3">
                    <i class="bi bi-people-fill text-warning"></i> Global Kinship Network
                </div>
                <h1 class="display-6 fw-bold text-white mb-2" style="letter-spacing: -0.02em;">Family Directory</h1>
                <p class="text-white-50 mb-4" style="font-size: 1.05rem; max-width: 520px; line-height: 1.5;">
                    Stay seamlessly connected with your household, extended lineage, and connected relatives across the globe.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/familyStudio" class="btn btn-light fw-bold text-primary px-4 py-2" style="border-radius: var(--stitch-radius-pill); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="bi bi-stars text-warning me-1"></i> Family Studio
                    </a>
                    <a href="/organogram" class="btn btn-outline-light fw-bold px-4 py-2" style="border-radius: var(--stitch-radius-pill);">
                        <i class="bi bi-diagram-3-fill me-1"></i> Interactive Tree
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="col-lg-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val" id="memberCountDisplay">0</div>
                            <div class="stat-badge-lbl">Members Connected</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val" id="statFamilyBranches">1</div>
                            <div class="stat-badge-lbl">Family Branches</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val" id="statActiveNetwork">100%</div>
                            <div class="stat-badge-lbl">Network Trust</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val"><i class="bi bi-shield-check text-success"></i> Verified</div>
                            <div class="stat-badge-lbl">Privacy Protected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Notification Loader -->
    @include('partials.loader', ['notificationId' => 'allMembers', 'loaderId' => 'allMembersGlobalLoader'])

    <!-- Google Stitch Filter & Search Toolbar -->
    <div class="members-toolbar-stitch">
        <div class="filter-nav-pills" id="memberFilterPills">
            <button type="button" class="filter-pill-btn active" data-filter="all">
                <i class="bi bi-grid-fill me-1"></i> All Connected (<span id="memberCount">0</span>)
            </button>
            <button type="button" class="filter-pill-btn" data-filter="family">
                <i class="bi bi-house-heart-fill me-1"></i> Same Family
            </button>
            <button type="button" class="filter-pill-btn" data-filter="connected">
                <i class="bi bi-link-45deg me-1"></i> Connected Kin
            </button>
            <button type="button" class="filter-pill-btn" data-filter="explore">
                <i class="bi bi-globe2 me-1"></i> Directory Search
            </button>
        </div>

        <div class="position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted ms-3" style="font-size: 0.85rem;"></i>
            <input class="search-input-stitch" type="search" placeholder="Search members by name or email..." aria-label="Search" id="searchFamily" name="searchFamily">
        </div>
    </div>

    <!-- Loading Indicator / Skeleton -->
    <div id="setLoader" class="loader my-5 mx-auto"></div>

    <p id="searchHidden" class="d-none"></p>

    <!-- Members Grid Container -->
    <div class="members-grid-stitch membersGrid" id="allMembers"></div>

</div>
@endsection
