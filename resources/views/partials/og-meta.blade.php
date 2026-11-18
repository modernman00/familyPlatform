{{--
  Standard Open Graph & Twitter/iMessage Rich Link Preview Metadata Partial
  Ensures WhatsApp (< 300 KB JPEG/PNG, 1200x630 / 1080x1080), iMessage (HTTPS),
  and X/Slack render rich preview cards for every shareable route.
--}}
@php
    $isHttps = (
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || strtolower((string) ($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "")) === "https"
    );
    $reqHost = (string) ($_SERVER["HTTP_HOST"] ?? "localhost");
    $envAppUrl = rtrim(trim((string) ($_ENV["APP_URL"] ?? getenv("APP_URL") ?: ""), " '\""), "/");
    $ogBaseUrl = $envAppUrl !== "" ? $envAppUrl : (($isHttps ? "https" : "http") . "://" . $reqHost);
    $appName = trim((string) ($_ENV["APP_NAME"] ?? "Platform"), " '\"") ?: "Platform";

    if (empty($ogUrl)) {
        $rawUri = (string) ($_SERVER["REQUEST_URI"] ?? "/");
        $parsed = parse_url($rawUri);
        $path = $parsed["path"] ?? "/";
        $safeQuery = "";
        if (!empty($parsed["query"])) {
            parse_str($parsed["query"], $qParams);
            $blockedKeys = ["token", "vault_token", "rsvp_code", "auth", "fbclid", "gclid", "ref"];
            foreach ($qParams as $k => $v) {
                $kl = strtolower((string) $k);
                if (in_array($kl, $blockedKeys, true) || str_starts_with($kl, "utm_")) {
                    unset($qParams[$k]);
                }
            }
            if ($qParams !== []) {
                $safeQuery = "?" . http_build_query($qParams);
            }
        }
        $resolvedUrl = $ogBaseUrl . $path . $safeQuery;
    } else {
        $resolvedUrl = (string) $ogUrl;
    }

    $resolvedTitle = trim((string) ($ogTitle ?? $appName));
    $rawDesc = (string) ($ogDescription ?? ("Explore " . $appName . " — fast, secure, modern digital experience."));
    $cleanDesc = trim(preg_replace("/\s+/", " ", strip_tags($rawDesc)) ?? "");
    if (function_exists("mb_strlen") && mb_strlen($cleanDesc) > 160) {
        $cleanDesc = rtrim(mb_substr($cleanDesc, 0, 157)) . "...";
    } elseif (strlen($cleanDesc) > 160) {
        $cleanDesc = rtrim(substr($cleanDesc, 0, 157)) . "...";
    }

    $resolvedType = (string) ($ogType ?? "website");
    $resolvedImage = (string) ($ogImage ?? "");
    if ($resolvedImage === "") {
        $resolvedImage = $ogBaseUrl . "/public/resources/assets/images/og-share-card.jpg?v=3";
    }
    $resolvedImageType = (string) ($ogImageType ?? (str_ends_with(strtolower(strtok($resolvedImage, "?") ?: ""), ".png") ? "image/png" : "image/jpeg"));
    $resolvedImageWidth = (string) ($ogImageWidth ?? "1200");
    $resolvedImageHeight = (string) ($ogImageHeight ?? "630");
    $resolvedImageAlt = (string) ($ogImageAlt ?? $resolvedTitle);
@endphp
    <!-- Open Graph / WhatsApp / iMessage Rich Preview -->
    <meta property="fb:app_id" content="{{ $_ENV["FACEBOOK_APP_ID"] ?? $_ENV["FB_APP_ID"] ?? "" }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:type" content="{{ $resolvedType }}">
    <meta property="og:url" content="{{ $resolvedUrl }}">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $cleanDesc }}">
    <meta property="og:image" content="{{ $resolvedImage }}">
    <meta property="og:image:secure_url" content="{{ $resolvedImage }}">
    <meta property="og:image:type" content="{{ $resolvedImageType }}">
    <meta property="og:image:width" content="{{ $resolvedImageWidth }}">
    <meta property="og:image:height" content="{{ $resolvedImageHeight }}">
    <meta property="og:image:alt" content="{{ $resolvedImageAlt }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $resolvedUrl }}">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $cleanDesc }}">
    <meta name="twitter:image" content="{{ $resolvedImage }}">