@extends('layouts.profileBase')
@section('title', 'Family Reels - Short-Form Heritage Videos')
@section('data-page-id', 'reelsPage')
@push('styles')
    <link rel="stylesheet" href="/public/css/reels.css?v={{ time() }}">
@endpush
@section('content')
<div class="reels-theater-container" id="reelsTheaterContainer">

    <!-- Hidden Initial JSON Payload -->
    <script id="reelsInitialData" type="application/json">
        {!! json_encode($initialReels ?? []) !!}
    </script>

    <!-- 9:16 Mobile & Desktop Centered Viewport -->
    <div class="reels-viewport" id="reelsViewport">
        <!-- Rendered dynamically by reelsPlayer.js -->
        <div class="d-flex align-items-center justify-content-center h-100 text-white">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    </div>

    <!-- Slide-out Comments Drawer -->
    <div class="reel-comments-drawer" id="reelCommentsDrawer">
        <div class="reel-comments-header">
            <h6 class="mb-0 fw-bold"><i class="bi bi-chat-dots-fill text-primary me-2"></i> Family Comments</h6>
            <button type="button" class="btn btn-sm btn-link text-white-50 p-0 text-decoration-none fs-5" onclick="document.getElementById('reelCommentsDrawer').classList.remove('open')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="reel-comments-list" id="reelCommentsList">
            <!-- Dynamic comments injected here -->
        </div>

        <div class="reel-comment-input-bar">
            <form id="reelCommentForm" class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control rounded-pill bg-dark text-white border-secondary" id="reelCommentInput" placeholder="Add a family note..." required style="font-size: 0.85rem; padding: 8px 16px;">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" style="font-size: 0.8rem; flex-shrink:0;">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Create Reel Modal -->
@includeIf('member.modals.createReelModal')
@endsection
