<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $meta['title'] }}</title>
    {!! \App\services\ShareMetadataProtocol::renderHeadMeta($meta) !!}
    <link rel="canonical" href="{{ $meta['url'] }}">
    <link rel="icon" href="/favicon.ico">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #090d16 80%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .spark-preview-card {
            width: 100%;
            max-width: 520px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55);
            text-align: center;
        }
        .spark-preview-img {
            width: 100%;
            aspect-ratio: 1200 / 630;
            object-fit: cover;
            display: block;
        }
        .spark-preview-body {
            padding: 24px;
        }
        .spark-preview-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #fef3c7;
            background: rgba(163, 22, 33, 0.35);
            border: 1px solid rgba(252, 211, 77, 0.5);
            border-radius: 999px;
            padding: 4px 12px;
            margin-bottom: 12px;
        }
        .spark-preview-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }
        .spark-preview-desc {
            font-size: 0.95rem;
            line-height: 1.5;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        .spark-preview-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 10px 24px;
            border-radius: 999px;
            background: #a31621;
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    @include('component.submitSpinner')
    <main class="spark-preview-card">
        <img src="{{ $meta['image'] }}" alt="{{ $meta['image_alt'] }}" class="spark-preview-img">
        <div class="spark-preview-body">
            <span class="spark-preview-badge">{{ $badgeLabel ?? 'DAILY FAMILY SPARK' }}</span>
            <h1 class="spark-preview-title">{{ $meta['title'] }}</h1>
            <p class="spark-preview-desc">{{ $meta['description'] }}</p>
            <a href="{{ $targetRedirectUrl ?? '/register?redirect=%2FprofilePage' }}" class="spark-preview-cta" data-spin="true">Open in FamilyPlatform</a>
            <div style="margin-top: 14px;">
                <a href="{{ $loginUrl ?? '/login?redirect=%2FprofilePage' }}" style="color: #94a3b8; font-size: 0.875rem; text-decoration: none;">Already a family member? Sign in</a>
            </div>
        </div>
    </main>

    @if(!empty($isGuestHuman))
        @include('partials.guest-gate', [
            'shareType' => 'spark',
            'itemKey' => (string)($sparkId ?? ''),
            'registerUrl' => $targetRedirectUrl ?? '/register?redirect=%2FprofilePage',
            'loginUrl' => $loginUrl ?? '/login?redirect=%2FprofilePage',
            'gateTitle' => 'Sign in to FamilyPlatform',
            'gateSubtitle' => 'Join your family circle to vote on this Daily Spark and see everyone’s answers.',
            'referrerSource' => $referrerSource ?? 'direct',
        ])
    @endif
</body>
</html>
