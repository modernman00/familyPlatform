@extends('layouts.profileBase')
@section('title', 'Family Reels - Short-Form Heritage Videos')
@section('data-page-id', 'reelsPage')
@push('styles')
    <link rel="stylesheet" href="/public/css/reels.css?v={{ time() }}">
@endpush
@push('meta')
    @if(!empty($ogMeta))
        <meta property="fb:app_id" content="{{ $_ENV['FACEBOOK_APP_ID'] ?? $_ENV['FB_APP_ID'] ?? '' }}">
        <meta property="og:type" content="video.other">
        <meta property="og:site_name" content="Family Platform">
        <meta property="og:title" content="{{ $ogMeta['title'] }}">
        <meta property="og:description" content="{{ $ogMeta['description'] }}">
        <meta property="og:image" content="{{ $ogMeta['image'] }}">
        <meta property="og:image:secure_url" content="{{ $ogMeta['image'] }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="720">
        <meta property="og:image:height" content="1280">
        <meta property="og:url" content="{{ $ogMeta['url'] }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogMeta['title'] }}">
        <meta name="twitter:description" content="{{ $ogMeta['description'] }}">
        <meta name="twitter:image" content="{{ $ogMeta['image'] }}">
    @endif
@endpush
@section('content')
<div class="reels-theater-container" id="reelsTheaterContainer" data-current-user-id="{{ $data['id'] ?? $_SESSION['id'] ?? '' }}">

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

        <!-- GIF Picker Popover Container (Collapsible) -->
        <div class="reel-gif-picker-drawer d-none" id="reelGifPickerDrawer">
            <div class="d-flex align-items-center justify-content-between p-2 border-bottom border-secondary">
                <span class="small fw-semibold text-white"><i class="bi bi-filetype-gif me-1 text-primary"></i> Choose a Family GIF</span>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-0" id="closeReelGifPickerBtn" title="Close GIF Picker">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- Category Pills -->
            <div class="d-flex gap-1 p-2 overflow-x-auto reel-gif-categories" id="reelGifCategories"></div>
            <!-- GIF Grid -->
            <div class="reel-gif-grid p-2" id="reelGifGrid" style="max-height: 180px; overflow-y: auto;"></div>
        </div>

        <!-- Replying & Attached GIF preview chip area -->
        <div class="reel-comment-aux-bar px-3 pt-2 pb-0 d-none" id="reelCommentAuxBar">
            <!-- Replying chip -->
            <div class="badge bg-secondary-subtle text-white d-inline-flex align-items-center gap-1 mb-1 d-none" id="reelReplyBadge">
                <span id="reelReplyTargetName">Replying to ...</span>
                <button type="button" class="btn btn-sm btn-link text-white-50 p-0 ms-1" id="cancelReelReplyBtn" title="Cancel Reply">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <!-- Attached GIF preview -->
            <div class="position-relative d-inline-block mb-1 d-none" id="reelAttachedGifContainer">
                <img src="" id="reelAttachedGifImg" class="rounded-2" alt="Attached GIF" style="max-height: 60px; object-fit: cover;">
                <button type="button" class="btn btn-danger btn-sm p-0 rounded-circle position-absolute top-0 end-0 translate-middle" id="removeReelAttachedGifBtn" style="width: 20px; height: 20px; line-height: 1; font-size: 0.7rem;" title="Remove GIF">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <div class="reel-comment-input-bar">
            <form id="reelCommentForm" class="d-flex gap-2 align-items-center">
    @include('csrf')
                <input type="text" class="form-control rounded-pill bg-dark text-white border-secondary" id="reelCommentInput" placeholder="Add a family note..." style="font-size: 0.85rem; padding: 8px 16px;">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2 fw-semibold text-white-50" id="reelGifToggleBtn" title="Attach Family GIF" style="font-size: 0.75rem; flex-shrink:0;">
                    GIF
                </button>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" id="reelCommentSubmitBtn" style="font-size: 0.8rem; flex-shrink:0;">
                    <i class="bi bi-send-fill" id="reelCommentSubmitIcon"></i>
                    <span class="spinner-border spinner-border-sm d-none" id="reelCommentSubmitSpinner" role="status"></span>
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Create Reel Modal -->
@includeIf('member.modals.createReelModal')
@endsection
