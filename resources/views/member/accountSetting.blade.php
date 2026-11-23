@extends('layouts.profileBase')
@section('title', 'Account Settings — FamilyPlatform')
@section('data-page-id', 'AccountSettingPage')

@push('styles')
  <style>
    body {
      background-color: #f8fafc;
      background-image: radial-gradient(at top left, rgba(255, 235, 240, 0.5) 0%, transparent 40%);
      font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
    }

    body.dark-mode,
    body.dark-theme,
    [data-bs-theme="dark"],
    html.dark-mode {
      background-color: #0b1120;
      background-image: radial-gradient(at top left, rgba(163, 22, 33, 0.14) 0%, transparent 45%);
      color: #f8fafc;
    }

    .as-skeleton-shell {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px 16px 48px;
    }

    .sidebar-card,
    .content-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.06);
      padding: 24px;
    }

    body.dark-mode .sidebar-card,
    body.dark-mode .content-card {
      background: #1e293b !important;
      border: 1px solid #334155 !important;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.45);
      color: #f8fafc;
    }

    .as-skeleton-bar {
      height: 18px;
      border-radius: 8px;
      background: linear-gradient(90deg, rgba(148, 163, 184, 0.18) 25%, rgba(148, 163, 184, 0.32) 50%, rgba(148, 163, 184, 0.18) 75%);
      background-size: 200% 100%;
    }

    @media (prefers-reduced-motion: no-preference) {
      .as-skeleton-bar {
        animation: asSkeletonPulse 1.4s ease-in-out infinite;
      }
      @keyframes asSkeletonPulse {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
      }
    }
  </style>
@endpush

@section('content')
  @php
    if (!isset($restrictionDirectoryJson)) {
      $restrictionDirectoryJson = json_encode($restrictionDirectory ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
    if (!isset($accountSettingsBootstrapJson)) {
      $fallbackBootstrap = \App\controller\members\SettingController::buildSafeBootstrap(
        is_array($accountData ?? null) ? $accountData : [],
        is_array($pendingFamilyRequest ?? null) ? $pendingFamilyRequest : null,
        is_array($birthFamily ?? null) ? $birthFamily : [],
        is_array($codeRename ?? null) ? $codeRename : [],
        is_array($passkeys ?? null) ? $passkeys : [],
        is_array($restrictionDirectory ?? null) ? $restrictionDirectory : [],
        (string) ($_SESSION['fullName'] ?? ''),
        (string) ($_SESSION['token'] ?? ''),
        (string) ($_GET['notice'] ?? '')
      );
      $accountSettingsBootstrapJson = json_encode($fallbackBootstrap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
  @endphp

  <script id="account-settings-bootstrap" type="application/json" nonce="{{ csp_nonce($nonce ?? null) }}">{!! $accountSettingsBootstrapJson !!}</script>
  <script id="restrictionDirectoryData" type="application/json" nonce="{{ csp_nonce($nonce ?? null) }}">{!! $restrictionDirectoryJson !!}</script>
  <span id="fName" class="d-none">{{ $_SESSION['fullName'] ?? '' }}</span>

  <div id="account-settings-root" data-testid="account-settings-root">
    <div class="as-skeleton-shell" aria-busy="true" aria-label="Loading Account Settings">
      <div class="text-center mb-4">
        <div class="as-skeleton-bar mx-auto mb-2" style="width: 240px; height: 28px;"></div>
        <div class="as-skeleton-bar mx-auto" style="width: 340px; max-width: 90%; height: 14px;"></div>
      </div>
      <div class="row g-4">
        <div class="col-lg-3">
          <div class="sidebar-card d-flex flex-column gap-3">
            <div class="as-skeleton-bar" style="width: 100%; height: 40px;"></div>
            <div class="as-skeleton-bar" style="width: 85%; height: 36px;"></div>
            <div class="as-skeleton-bar" style="width: 75%; height: 36px;"></div>
            <div class="as-skeleton-bar" style="width: 90%; height: 36px;"></div>
          </div>
        </div>
        <div class="col-lg-9">
          <div class="content-card d-flex flex-column gap-3" style="min-height: 460px;">
            <div class="as-skeleton-bar" style="width: 220px; height: 24px;"></div>
            <div class="as-skeleton-bar" style="width: 320px; max-width: 95%; height: 14px;"></div>
            <div class="as-skeleton-bar mt-3" style="width: 100%; height: 44px;"></div>
            <div class="as-skeleton-bar" style="width: 100%; height: 44px;"></div>
            <div class="as-skeleton-bar" style="width: 100%; height: 44px;"></div>
          </div>
        </div>
      </div>
      <noscript>
        <div class="alert alert-warning mt-4" role="alert">
          JavaScript is required to display and save your Account Settings interactively. Please enable JavaScript in your browser.
        </div>
      </noscript>
    </div>
  </div>
@endsection

@push('scripts')
  <script defer src="/public/js/account-settings-app.js?v={{ assetVersion('js/account-settings-app.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>
@endpush
