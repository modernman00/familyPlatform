@extends('layouts.profileBase')
@section('title', 'All Members')
@section('data-page-id', 'allMembers')
@push('styles')
    <link rel="stylesheet" href="/public/css/showMembers.css">
@endpush
@section('content')


    <!-- Header Section -->
    <div class="members-header">
        <div class="members-container">
            <h1 class="fw-bold mb-2">
                <i class="bi bi-people"></i>
                Family Directory
            </h1>
            <p class="lead mb-3">Stay connected with your heritage and extended family members.</p>
            <div class="d-inline-block">
                <span class="badge bg-white text-success rounded-pill px-4 py-2 shadow-sm" id="memberCount">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    0 Members Connected
                </span>
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
                        <input class="form-control" type="search" placeholder="Search for other family members by name"
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
        <div class="members-grid membersGrid " id="allMembers"> </div>

    </div>


@endsection