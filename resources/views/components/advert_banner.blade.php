@php
    $currentApp = $currentApp ?? null;
    if ($currentApp === null) {
        $rootPath = (string) (defined('ROOT_DIR') ? constant('ROOT_DIR') : ($_SERVER['DOCUMENT_ROOT'] ?? getcwd()));
        if (stripos($rootPath, 'familyPlatform') !== false) {
            $currentApp = 'familyplatform';
        } elseif (stripos($rootPath, 'idecide') !== false) {
            $currentApp = 'idecide';
        } elseif (stripos($rootPath, 'iaccount') !== false) {
            $currentApp = 'iaccount';
        } elseif (stripos($rootPath, 'scoretenant') !== false) {
            $currentApp = 'scoretenant';
        } elseif (stripos($rootPath, 'execmind') !== false) {
            $currentApp = 'execmind';
        } elseif (stripos($rootPath, 'loaneasy') !== false) {
            $currentApp = 'loaneasyfinance';
        } else {
            $currentApp = 'partyplatform';
        }
    }

    $banner = $banner ?? null;
    if ($banner === null && isset($placement)) {
        $promoService = new \Src\EcosystemPromoService();
        $banner = $promoService->getPromoForContext((string) $currentApp, (string) $placement, $context ?? []);
    }
@endphp

@if(!empty($banner) && is_array($banner))
@php
    $bannerId = (string) ($banner['id'] ?? 'promo_default');
    $accentColor = (string) ($banner['accent_color'] ?? '#9B5CFB');
    $gradient = (string) ($banner['gradient'] ?? 'linear-gradient(135deg, #9B5CFB 0%, #b8202c 100%)');
    $icon = (string) ($banner['icon'] ?? 'bi-stars');
    $badge = (string) ($banner['badge'] ?? 'ECOSYSTEM PERK');
    $brandName = (string) ($banner['brand_name'] ?? '');
    $targetUrl = (string) ($banner['target_url'] ?? '#');
    $isDismissible = !empty($banner['dismissible']);
    $disclosureText = (string) ($banner['disclosure_text'] ?? 'Seamless cross-app utility.');
    $svgIcon = (string) ($banner['svg_icon'] ?? '');

    // Route through OutboundTrackingService for click analytics if available
    try {
        if (class_exists('\App\Services\OutboundTrackingService')) {
            $outboundService = new \App\Services\OutboundTrackingService();
            $trackedUrl = $outboundService->generateTrackedUrl($targetUrl, [
                'source' => 'banner_' . $bannerId,
                'category' => (string) ($banner['target_app'] ?? 'ecosystem'),
                'item' => (string) ($banner['title'] ?? 'promo'),
            ]);
        } else {
            $trackedUrl = $targetUrl;
        }
    } catch (\Throwable) {
        $trackedUrl = $targetUrl;
    }
@endphp

<aside class="pp-advert-banner-container my-3" 
       id="ppBanner_{{ $bannerId }}" 
       data-banner-id="{{ $bannerId }}"
       role="complementary" 
       aria-label="{{ $banner['badge'] ?? 'Special Offer' }}: {{ $banner['title'] ?? '' }}"
       style="--banner-accent: {{ $accentColor }}; --banner-gradient: {{ $gradient }};">
    <div class="pp-advert-banner-card">
        <div class="pp-banner-meta-row">
            <div class="pp-banner-badges">
                <span class="pp-badge-pill">
                    <i class="bi bi-stars me-1" aria-hidden="true"></i> {{ $badge }}
                </span>
                @if(!empty($brandName))
                    <span class="pp-brand-tag">
                        {{ $brandName }}
                    </span>
                @endif
                <span class="pp-disclosure-tag" title="{{ $disclosureText }}">
                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i> {{ $disclosureText }}
                </span>
            </div>
            @if($isDismissible)
                <button type="button" 
                        class="pp-banner-dismiss-btn" 
                        aria-label="Dismiss offer" 
                        onclick="ppDismissAdvertBanner('{{ $bannerId }}')"
                        title="Dismiss offer">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            @endif
        </div>

        <div class="pp-banner-grid">
            <div class="pp-banner-avatar-box">
                <div class="pp-banner-icon-bubble">
                    @if(!empty($svgIcon))
                        <span class="pp-banner-svg-holder">{!! $svgIcon !!}</span>
                    @else
                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                    @endif
                </div>
            </div>

            <div class="pp-banner-body-text">
                <h4 class="pp-banner-heading">
                    {{ $banner['title'] ?? '' }}
                    @if(!empty($banner['tagline']))
                        <span class="pp-banner-subheading">| {{ $banner['tagline'] }}</span>
                    @endif
                </h4>
                <p class="pp-banner-desc">
                    {{ $banner['description'] ?? '' }}
                </p>
            </div>

            <div class="pp-banner-action-box">
                <a href="{{ $trackedUrl }}" 
                   class="pp-banner-cta-button" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   aria-label="{{ $banner['cta_text'] ?? 'Explore' }} (opens in new tab)">
                    <span>{{ $banner['cta_text'] ?? 'Explore Now' }}</span>
                    <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</aside>

<style>
.pp-advert-banner-container {
    width: 100%;
    contain: layout style;
    display: block;
    transition: opacity 0.3s ease, transform 0.3s ease, max-height 0.4s ease;
}
.pp-advert-banner-container.pp-banner-hidden {
    opacity: 0;
    transform: translateY(-8px);
    max-height: 0;
    overflow: hidden;
    margin: 0 !important;
    padding: 0 !important;
    pointer-events: none;
}
.pp-advert-banner-card {
    background: linear-gradient(135deg, rgba(42,10,13, 0.90) 0%, rgba(42,10,13, 0.76) 100%);
    border: 1px solid rgba(184,32,44, 0.32);
    border-radius: 16px;
    padding: 14px 18px;
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
}
.pp-advert-banner-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--banner-gradient, linear-gradient(135deg, #FFE259 0%, #FFA751 100%));
}
.pp-advert-banner-card:hover {
    border-color: rgba(245, 200, 66, 0.55);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.38);
}
.pp-banner-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}
.pp-banner-badges {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.pp-badge-pill {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #ffffff;
    background: var(--banner-gradient, rgba(184,32,44, 0.3));
    border-radius: 6px;
    padding: 3px 8px;
    display: inline-flex;
    align-items: center;
}
.pp-brand-tag {
    font-size: 0.72rem;
    font-weight: 700;
    color: #F5C842;
    background: rgba(245, 200, 66, 0.12);
    border: 1px solid rgba(245, 200, 66, 0.25);
    border-radius: 6px;
    padding: 2px 7px;
    letter-spacing: 0.02em;
}
.pp-disclosure-tag {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.55);
    display: inline-flex;
    align-items: center;
}
.pp-banner-dismiss-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.7);
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}
.pp-banner-dismiss-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: rgba(239, 68, 68, 0.4);
    color: #ef4444;
}
.pp-banner-grid {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.pp-banner-avatar-box {
    flex-shrink: 0;
}
.pp-banner-icon-bubble {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: var(--banner-gradient, rgba(184,32,44, 0.25));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    padding: 6px;
}
.pp-banner-svg-holder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.pp-banner-svg-holder svg {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}
.pp-banner-body-text {
    flex: 1 1 240px;
    min-width: 200px;
}
.pp-banner-heading {
    font-family: 'Poppins', 'Outfit', sans-serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: #ffffff;
    margin: 0 0 4px 0;
    line-height: 1.3;
}
.pp-banner-subheading {
    font-size: 0.84rem;
    font-weight: 500;
    color: #F5C842;
    margin-left: 4px;
}
.pp-banner-desc {
    font-size: 0.83rem;
    color: rgba(255, 255, 255, 0.78);
    margin: 0;
    line-height: 1.45;
}
.pp-banner-action-box {
    flex-shrink: 0;
}
.pp-banner-cta-button {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    border-radius: 10px;
    background: var(--banner-gradient, linear-gradient(135deg, #FFE259 0%, #FFA751 100%));
    color: #120326 !important;
    font-weight: 700;
    font-size: 0.88rem;
    text-decoration: none !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    white-space: nowrap;
}
.pp-banner-cta-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
}
.pp-banner-cta-button:active {
    transform: scale(0.98);
}
@media (max-width: 640px) {
    .pp-banner-grid {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .pp-banner-avatar-box {
        display: none;
    }
    .pp-banner-action-box {
        width: 100%;
    }
    .pp-banner-cta-button {
        width: 100%;
    }
}
</style>

<script>
(function() {
    const bannerId = '{{ $bannerId }}';
    const storageKey = 'pp_dismiss_banner_' + bannerId;
    if (localStorage.getItem(storageKey) === 'true') {
        const el = document.getElementById('ppBanner_' + bannerId);
        if (el) {
            el.style.display = 'none';
        }
    }
})();

function ppDismissAdvertBanner(bannerId) {
    const el = document.getElementById('ppBanner_' + bannerId);
    if (el) {
        el.classList.add('pp-banner-hidden');
        setTimeout(() => {
            el.style.display = 'none';
        }, 320);
    }
    try {
        localStorage.setItem('pp_dismiss_banner_' + bannerId, 'true');
    } catch (e) {
        // Safe localStorage fallback
    }
}
</script>
@endif
