@extends('layouts.profileBase')

@section('title', 'Director Mode — FamilyPlatform')
@section('meta_description', 'Plan and render multi-shot family heirloom and reunion films. Reference your elders, parents, children, homestead and reunion moments with @tags at zero extra cost.')
@section('data-page-id', 'director-mode')

@push('styles')
<link rel="stylesheet" href="/public/css/director-mode.css?v={{ assetVersion('css/director-mode.css') }}">
<style>[x-cloak]{display:none !important}</style>
@endpush

@section('content')
@php
    $csrfToken = (string) ($_SESSION['token'] ?? '');
    $assetList = array_values($assets ?? []);
    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $csrfJson = json_encode($csrfToken, $jsonFlags) ?: '""';
    $assetsJson = json_encode($assetList, $jsonFlags) ?: '[]';
@endphp

<div class="container py-4">
    @include('member.creativeStudioTabs', ['creativeTab' => 'director'])

    <div id="director-mode-root">
        <div class="mx-auto w-full max-w-5xl px-4 sm:px-6">
            <p class="text-sm leading-relaxed text-slate-600">
                Tags you can use in a shot:
                @foreach ($assetList as $tag)
                    <code class="mr-1 inline-block rounded bg-slate-100 px-2 py-1 text-xs text-slate-900">{{ '@' . $tag }}</code>
                @endforeach
            </p>
        </div>
        {!! file_get_contents(__DIR__ . '/partials/director_mode.partial.html') !!}
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce($nonce ?? null) }}" type="module">
    import { registerDirectorMode } from '/public/js/SovereignDirectorMode.js?v={{ assetVersion('js/SovereignDirectorMode.js') }}';
    document.addEventListener('alpine:init', () => {
        registerDirectorMode(window.Alpine, {
            endpoint: '/api/director-mode/plan',
            renderEndpoint: '/api/director-mode/render',
            csrfToken: {!! $csrfJson !!},
            assets: {!! $assetsJson !!},
        });
    });
</script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
@endpush
