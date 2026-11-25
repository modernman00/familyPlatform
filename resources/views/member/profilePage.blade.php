@extends('layouts.profileBase')
@section('title', 'PROFILE_PAGE')
@section('data-page-id', 'profilePage')
@push('styles')
    <link rel="stylesheet" href="/public/css/profilepage.css?v={{ time() }}">
    <link rel="preload" href="/public/css/reels.css?v={{ time() }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/public/css/reels.css?v={{ time() }}"></noscript>
@endpush
@section('content')


    {{-- @php

    $url = getenv("APP_URL");
    $url = $url;
    loggedDetection($url, $data['email']);


    @endphp --}}
    <div class="container-main" style="min-height: 100vh;">

        <!-- Mobile-first above-the-fold Post Composer (<992px) -->
        <div class="mobile-composer-slot d-lg-none w-100">
            @include('member.includes.postComposer', ['composerId' => 'openPostModalTriggerMobile'])
        </div>

        @includeIf('member.includes.leftColumn')

        @includeIf('member.includes.middleColumn')

        @includeIf('member.includes.rightColumn')

    </div>

@endsection

@section('modals')
    @includeIf('member.modals.post')
    @includeIf('member.modals.share')
    @includeIf('member.modals.createEvent')
    @includeIf('member.modals.shareModal2')
    @includeIf('member.modals.createReelModal')
    @includeIf('member.modals.lineageQuestModal')
    @includeIf('member.modals.familyFlyerModal')
    @includeIf('member.modals.timeCapsuleModal')
    @includeIf('member.modals.createAnnouncementModal')
    @includeIf('member.modals.upcomingEventsModal')
    @includeIf('member.modals.adulthoodCelebrationModal')
    @includeIf('member.modals.kinshipDetailsModal')
    @includeIf('member.modals.kinshipRadarSettingsModal')
    @includeIf('member.modals.thenNowComparisonModal')
@endsection

@push('scripts')
    <script nonce="{{ csp_nonce($nonce ?? null) }}">
        // Defensive frontend: Check authentication status silently if needed without page refresh
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // We can silently check session status via api without blocking the UI
                console.log('Page visible, auth checking delegated to API calls');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const fileInput = document.getElementById('img'); // Hidden file input
            const previewImg = document.getElementById('profilePreview'); // Current avatar image

            if (!fileInput || !previewImg) return; // Defensive check

            fileInput.addEventListener('change', function () {
                const file = this.files[0]; // Get the first selected file
                if (!file) return;

                const reader = new FileReader(); // Create a FileReader to read the image
                reader.onload = function (e) {
                    previewImg.src = e.target.result; // Update the image src with base64 data
                };
                reader.readAsDataURL(file); // Read the file as a data URL
            });
        });

        window.appUrl = "{{ $_ENV['MIX_APP_URL'] ?? getenv('MIX_APP_URL') ?: ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '') }}";
        window.__FP_PROFILE_TOUR_CONFIG__ = {
            dismissed: @json((bool)($profileTourDismissed ?? false)),
            isPendingApproval: @json((bool)($isPendingApproval ?? false))
        };
        @php
            $arcadeChampionsMap = [];
            if (!empty($_SESSION['famCode'])) {
                foreach (\App\services\FamilyArcadeService::getFamilyChampions(\Src\Db::connect2(), (string)$_SESSION['famCode']) as $champ) {
                    $uid = (string)($champ['userId'] ?? '');
                    if ($uid !== '') {
                        $arcadeChampionsMap[$uid][] = $champ['badgeTitle'];
                    }
                }
            }
        @endphp
        window.__FP_ARCADE_BADGES__ = @json($arcadeChampionsMap);
    </script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignSpotlightTour.js?v={{ assetVersion('js/SovereignSpotlightTour.js') }}" defer></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/profileTour.js?v={{ assetVersion('js/profileTour.js') }}" defer></script>
    <script type="module" src="/public/js/feed-app.js?v={{ assetVersion('js/feed-app.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>
    <script type="module" src="/public/js/profile-app.js?v={{ assetVersion('js/profile-app.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>

@endpush