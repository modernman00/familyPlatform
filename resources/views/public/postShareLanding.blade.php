<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="index, follow">
    <title>{{ $ogTitle }} | FamilyPlatform</title>
    <meta name="description" content="{{ $ogDescription }}">

    <!-- Open Graph & Social Previews (WhatsApp, Facebook, LinkedIn, iMessage) -->
    <meta property="og:site_name" content="FamilyPlatform">
    <meta property="og:type" content="{{ $ogType ?? 'article' }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $ogTitle }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">

    <!-- Twitter / 𝕏 Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="/favicon.ico">

    @if(!empty($shouldRedirect) && !empty($targetRedirectUrl))
        <!-- Instant forward for human visitors -->
        <meta http-equiv="refresh" content="0;url={{ $targetRedirectUrl }}">
        <script>
            try {
                window.location.replace({!! json_encode($targetRedirectUrl) !!});
            } catch (e) {
                window.location.href = {!! json_encode($targetRedirectUrl) !!};
            }
        </script>
    @endif

    <style>
        :root {
            --bg-canvas: #090d16;
            --surface-card: rgba(255, 255, 255, 0.05);
            --border-card: rgba(255, 255, 255, 0.12);
            --text-heading: #f8fafc;
            --text-body: #94a3b8;
            --accent-brand: #1d72fe;
            --accent-gold: #f59e0b;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-canvas);
            color: var(--text-heading);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #090d16 80%);
        }
        .preview-card {
            width: 100%;
            max-width: 480px;
            background: var(--surface-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            text-align: center;
        }
        .preview-media-container {
            width: 100%;
            height: 260px;
            position: relative;
            background: #0f172a;
            overflow: hidden;
        }
        .preview-media-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .media-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 20px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .preview-body {
            padding: 24px;
        }
        .family-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--accent-gold);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .author-title {
            font-size: 1.28rem;
            font-weight: 800;
            line-height: 1.35;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .caption-text {
            font-size: 0.92rem;
            color: var(--text-body);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn-cta {
            display: block;
            width: 100%;
            background: var(--accent-brand);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            padding: 15px 20px;
            border-radius: 14px;
            transition: transform 0.15s ease, filter 0.15s ease;
            box-shadow: 0 8px 24px rgba(29, 114, 254, 0.35);
        }
        .btn-cta:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }
        .btn-secondary-link {
            display: inline-block;
            margin-top: 16px;
            color: #64748b;
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .btn-secondary-link:hover {
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="preview-card">
        <div class="preview-media-container">
            <img src="{{ $ogImage }}" alt="{{ $ogTitle }}" class="preview-media-img" onerror="this.onerror=null; this.src='/public/img/og-invite.jpg';">
            @if(!empty($mediaLabel))
                <div class="media-badge">{{ $mediaLabel }}</div>
            @endif
        </div>
        <div class="preview-body">
            @if(!empty($cleanFamCode))
                <div class="family-pill">{{ $cleanFamCode }} Family Circle</div>
            @endif
            <h1 class="author-title">{{ $ogTitle }}</h1>
            <p class="caption-text">{{ $ogDescription }}</p>

            <a href="{{ $targetRedirectUrl ?? '/register' }}" class="btn-cta">
                View Memory on FamilyPlatform &rarr;
            </a>

            <div>
                <a href="/login?redirect={{ urlencode($redirectTarget ?? '/profilePage') }}" class="btn-secondary-link">
                    Already a family member? Sign in
                </a>
            </div>
        </div>
    </div>
</body>
</html>
