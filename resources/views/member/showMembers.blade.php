@extends('layouts.profileBase')
@section('title', 'All Members')
@section('data-page-id', 'allMembers')
@push('styles')
    <link rel="stylesheet" href="public/css/showMembers.css">
@endpush
@section('content')


    <!-- Header Section -->
    <div class="members-header text-center">
        <div class="container">
            <h1 class="display-5 fw-bold">Meet Your Family Members & Close Associates</h1>
            <p class="lead">Connect with amazing people from around the world</p>
            <div class="mt-4">
                <span class="badge bg-light text-dark me-2">256+ Members</span>
                <span class="badge bg-light text-dark me-2">15+ Countries</span>
                <span class="badge bg-light text-dark">50+ Professions</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="members-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input class="form-control" type="search" placeholder="Search members by name"
                            aria-label="Search" id="searchFamily" name="searchFamily">

                    </div>
                </div>

            </div>
        </div>

        <!-- Loading indicator -->
        <div id="setLoader" class="loader">

        </div>

        <p id="searchHidden"></p>

        <!-- Members Grid -->
        <div class="members-grid membersGrid" id="allMembers"> </div>

    </div>


@endsection
