<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $meta['title'] ?? 'Family Arcade Challenge — FamilyPlatform' }}</title>
    {!! \App\services\ShareMetadataProtocol::renderHeadMeta($meta ?? []) !!}
    <link rel="canonical" href="{{ $meta['url'] ?? '' }}">
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/public/css/standards-v21.css?v={{ assetVersion('css/standards-v21.css') }}">
    <style nonce="{{ csp_nonce($nonce ?? null) }}">
        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            overflow-x: clip;
        }
        .pub-arc-nav {
            background: #0f172a;
            border-bottom: 1px solid rgba(251, 191, 36, 0.35);
            padding: 8px 10px;
        }
        .pub-arc-brand {
            color: #ffffff;
            font-weight: 800;
            font-size: 1.05rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            white-space: nowrap;
        }
        .pub-arc-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            flex-shrink: 0;
            border-radius: 12px;
            font-weight: 700;
        }
        @media (max-width: 480px) {
            .pub-arc-brand {
                font-size: 0.92rem;
                gap: 6px;
            }
            .pub-arc-btn {
                padding-left: 10px !important;
                padding-right: 10px !important;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <header class="pub-arc-nav">
        <div class="container-fluid px-2 px-md-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="max-width: 1240px;">
            <a href="/" class="pub-arc-brand">
                <i class="bi bi-trophy-fill text-warning" aria-hidden="true"></i>
                <span>FamilyPlatform <span class="text-warning">Arcade</span></span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="/login" class="btn btn-outline-light btn-sm px-3 pub-arc-btn">
                    Sign In
                </a>
                <a href="{{ $bootstrap['registerUrl'] ?? '/register' }}" class="btn btn-warning text-dark btn-sm px-3 pub-arc-btn" id="publicArcadeJoinBtn">
                    <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                    <span>Join Free</span>
                </a>
            </div>
        </div>
    </header>

    <main class="container-fluid px-2 px-md-3 py-2 py-md-4" style="max-width: 1240px;">
        <script id="arcade-bootstrap" type="application/json" nonce="{{ csp_nonce($nonce ?? null) }}">
            @json($bootstrap ?? [])
        </script>

        <div id="family-arcade-root">
            <div class="text-center py-5">
                <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Loading Family Arcade Challenge...</span>
                </div>
            </div>
        </div>
    </main>

    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/manifest.js?v={{ assetVersion('js/manifest.js') }}" defer></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/vendor.js?v={{ assetVersion('js/vendor.js') }}" defer></script>
    <script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/arcade-app.js?v={{ assetVersion('js/arcade-app.js') }}" defer></script>
</body>
</html>
