@extends('layouts.profileBase')

@section('title', 'Family Arcade — KinZip, KinGrid, KinWord & KinPulse | FamilyPlatform')
@section('meta_description', 'Play daily family puzzles, race the algorithm or your relatives live, climb the Family Cup league table, and earn your family crown.')

@section('content')
<div class="container-fluid py-3 py-md-4" style="max-width: 1240px;">
    @include('member.creativeStudioTabs', ['creativeTab' => 'arcade'])

    <script id="arcade-bootstrap" type="application/json" nonce="{{ csp_nonce($nonce ?? null) }}">
        @json($bootstrap ?? [])
    </script>

    <div id="family-arcade-root">
        <div class="text-center py-5">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Loading Family Arcade...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="/public/js/arcade-app.js?v={{ assetVersion('js/arcade-app.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>
@endpush
