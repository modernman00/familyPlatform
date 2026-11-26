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
        } elseif (stripos($rootPath, 'bcp') !== false) {
            $currentApp = 'bcp';
        } else {
            $currentApp = 'partyplatform';
        }
    }

    $promoService = new \Src\EcosystemPromoService();
    $placementKey = (string) ($placement ?? 'feed');
    $bannerContext = is_array($context ?? null) ? $context : [];

    $bannerDeck = [];
    if (!empty($banner) && is_array($banner)) {
        $bannerDeck = [$banner];
    } elseif (method_exists($promoService, 'getPromoDeckForContext')) {
        $bannerDeck = $promoService->getPromoDeckForContext((string) $currentApp, $placementKey, $bannerContext);
    } else {
        $single = $promoService->getPromoForContext((string) $currentApp, $placementKey, $bannerContext);
        if (!empty($single)) {
            $bannerDeck = [$single];
        }
    }

    // Enrich each slide in the deck with tracked outbound URLs and defaults
    $preparedDeck = [];
    foreach ($bannerDeck as $slide) {
        if (!is_array($slide)) {
            continue;
        }
        $sId = (string) ($slide['id'] ?? 'promo_default');
        $sTargetUrl = (string) ($slide['target_url'] ?? '#');
        try {
            if (class_exists('\App\Services\OutboundTrackingService')) {
                $outboundService = new \App\Services\OutboundTrackingService();
                $sTrackedUrl = $outboundService->generateTrackedUrl($sTargetUrl, [
                    'source' => 'banner_' . $sId,
                    'category' => (string) ($slide['target_app'] ?? 'ecosystem'),
                    'item' => (string) ($slide['title'] ?? 'promo'),
                ]);
            } else {
                $sTrackedUrl = $sTargetUrl;
            }
        } catch (\Throwable) {
            $sTrackedUrl = $sTargetUrl;
        }

        $preparedDeck[] = [
            'id' => $sId,
            'target_app' => (string) ($slide['target_app'] ?? 'ecosystem'),
            'brand_name' => (string) ($slide['brand_name'] ?? ''),
            'badge' => (string) ($slide['badge'] ?? 'SISTER STUDIO'),
            'title' => (string) ($slide['title'] ?? ''),
            'tagline' => (string) ($slide['tagline'] ?? ''),
            'description' => (string) ($slide['description'] ?? ''),
            'cta_text' => (string) ($slide['cta_text'] ?? 'Explore Now'),
            'target_url' => $sTrackedUrl,
            'accent_color' => (string) ($slide['accent_color'] ?? '#9B5CFB'),
            'gradient' => (string) ($slide['gradient'] ?? 'linear-gradient(135deg, #7b03fc 0%, #d946ef 100%)'),
            'icon' => (string) ($slide['icon'] ?? 'bi-stars'),
            'svg_icon' => (string) ($slide['svg_icon'] ?? ''),
            'logo_url' => (string) ($slide['logo_url'] ?? ''),
            'value_pills' => array_values((array) ($slide['value_pills'] ?? [])),
            'disclosure_text' => (string) ($slide['disclosure_text'] ?? 'Sister Studio Spotlight • 1-Click Single Login'),
            'dismissible' => !empty($slide['dismissible']),
        ];
    }

    $banner = $preparedDeck[0] ?? null;
    $instanceUid = 'ppEcoCarousel_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $placementKey);
@endphp

@if(!empty($banner) && is_array($banner))
@php
    $bannerId = (string) $banner['id'];
    $accentColor = (string) $banner['accent_color'];
    $gradient = (string) $banner['gradient'];
    $icon = (string) $banner['icon'];
    $badge = (string) $banner['badge'];
    $brandName = (string) $banner['brand_name'];
    $trackedUrl = (string) $banner['target_url'];
    $isDismissible = !empty($banner['dismissible']);
    $disclosureText = (string) $banner['disclosure_text'];
    $svgIcon = (string) $banner['svg_icon'];
    $logoUrl = (string) $banner['logo_url'];
    $valuePills = (array) $banner['value_pills'];
    $deckCount = count($preparedDeck);
@endphp

<aside class="pp-advert-banner-container my-3"
       id="{{ $instanceUid }}"
       data-banner-id="{{ $bannerId }}"
       data-placement="{{ $placementKey }}"
       role="complementary"
       aria-label="{{ $badge }}: {{ $banner['title'] }}"
       style="--banner-accent: {{ $accentColor }}; --banner-gradient: {{ $gradient }};">
    <div class="pp-advert-banner-card">
        {{-- Top Progress Bar for Auto-Rotation --}}
        @if($deckCount > 1)
            <div class="pp-banner-progress-track" aria-hidden="true">
                <div class="pp-banner-progress-bar" data-role="progress-bar"></div>
            </div>
        @endif

        <div class="pp-banner-meta-row">
            <div class="pp-banner-badges">
                <span class="pp-badge-pill" data-role="badge">
                    <i class="bi bi-stars me-1" aria-hidden="true"></i>
                    <span data-role="badge-text">{{ $badge }}</span>
                </span>
                <span class="pp-brand-tag" data-role="brand-name" @if(empty($brandName)) style="display:none;" @endif>
                    {{ $brandName }}
                </span>
                <span class="pp-disclosure-tag" data-role="disclosure" title="{{ $disclosureText }}">
                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                    <span data-role="disclosure-text">{{ $disclosureText }}</span>
                </span>
            </div>

            <div class="pp-banner-controls-group">
                @if($deckCount > 1)
                    <div class="pp-banner-nav-cluster" role="group" aria-label="Rotate sister studio spotlights">
                        <button type="button"
                                class="pp-banner-nav-btn"
                                data-action="prev"
                                aria-label="Previous spotlight"
                                title="Previous spotlight">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </button>
                        <span class="pp-banner-counter" data-role="counter" aria-live="polite">
                            1 / {{ $deckCount }}
                        </span>
                        <button type="button"
                                class="pp-banner-nav-btn"
                                data-action="next"
                                aria-label="Next spotlight"
                                title="Next spotlight">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                @endif

                @if($isDismissible)
                    <button type="button"
                            class="pp-banner-dismiss-btn"
                            data-action="dismiss"
                            aria-label="Dismiss offer"
                            title="Dismiss offer">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        </div>

        <div class="pp-banner-grid" data-role="slide-body">
            <div class="pp-banner-avatar-box">
                <div class="pp-banner-icon-bubble" data-role="icon-bubble">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}"
                             alt="{{ $brandName ?: 'App' }} Logo"
                             class="pp-banner-brand-img"
                             width="48"
                             height="48"
                             loading="lazy"
                             onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                        <span class="pp-banner-svg-holder" style="display:none;">{!! $svgIcon !!}</span>
                    @elseif(!empty($svgIcon))
                        <span class="pp-banner-svg-holder">{!! $svgIcon !!}</span>
                    @else
                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                    @endif
                </div>
            </div>

            <div class="pp-banner-body-text">
                <h4 class="pp-banner-heading">
                    <span data-role="title">{{ $banner['title'] }}</span>
                    <span class="pp-banner-subheading" data-role="tagline" @if(empty($banner['tagline'])) style="display:none;" @endif>
                        | {{ $banner['tagline'] }}
                    </span>
                </h4>
                <p class="pp-banner-desc" data-role="description">
                    {{ $banner['description'] }}
                </p>

                <div class="pp-banner-pills-row" data-role="value-pills" @if(empty($valuePills)) style="display:none;" @endif>
                    @foreach($valuePills as $pill)
                        <span class="pp-value-pill">{{ $pill }}</span>
                    @endforeach
                </div>
            </div>

            <div class="pp-banner-action-box">
                <a href="{{ $trackedUrl }}"
                   class="pp-banner-cta-button"
                   data-role="cta-link"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="{{ $banner['cta_text'] }} (opens in new tab)">
                    <span data-role="cta-text">{{ $banner['cta_text'] }}</span>
                    <i class="bi bi-arrow-up-right ms-2" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        @if($deckCount > 1)
            <div class="pp-banner-dots-row" role="tablist" aria-label="Choose sister app spotlight">
                @foreach($preparedDeck as $idx => $deckItem)
                    <button type="button"
                            class="pp-banner-dot-btn {{ $idx === 0 ? 'is-active' : '' }}"
                            data-slide-to="{{ $idx }}"
                            role="tab"
                            aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"
                            aria-label="Show {{ $deckItem['brand_name'] ?: ('Spotlight ' . ($idx + 1)) }}"
                            title="{{ $deckItem['brand_name'] ?: ('Spotlight ' . ($idx + 1)) }}">
                        <span class="pp-banner-dot-core"></span>
                    </button>
                @endforeach
            </div>
        @endif
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
    background: linear-gradient(135deg, rgba(24, 10, 28, 0.94) 0%, rgba(36, 11, 22, 0.88) 100%);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 16px;
    padding: 16px 20px 14px;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.10);
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
    opacity: 0.95;
}
.pp-banner-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255, 255, 255, 0.08);
    overflow: hidden;
    z-index: 2;
}
.pp-banner-progress-bar {
    height: 100%;
    width: 0%;
    background: var(--banner-gradient, linear-gradient(90deg, #38bdf8, #f43f5e));
    transition: width 0.15s linear;
}
.pp-advert-banner-card:hover {
    border-color: rgba(245, 200, 66, 0.48);
    box-shadow: 0 14px 32px rgba(0, 0, 0, 0.42);
}
.pp-banner-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.pp-banner-badges {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.pp-badge-pill {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #ffffff;
    background: var(--banner-gradient, rgba(184,32,44, 0.4));
    border-radius: 6px;
    padding: 4px 10px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
.pp-brand-tag {
    font-size: 0.75rem;
    font-weight: 700;
    color: #FDE047;
    background: rgba(253, 224, 71, 0.12);
    border: 1px solid rgba(253, 224, 71, 0.30);
    border-radius: 6px;
    padding: 3px 9px;
    letter-spacing: 0.02em;
    white-space: nowrap;
    flex-shrink: 0;
}
.pp-disclosure-tag {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.72);
    display: inline-flex;
    align-items: center;
}
.pp-banner-controls-group {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}
.pp-banner-nav-cluster {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.pp-banner-counter {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.82);
    font-variant-numeric: tabular-nums;
    min-width: 34px;
    text-align: center;
    white-space: nowrap;
}
.pp-banner-nav-btn,
.pp-banner-dismiss-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.16);
    color: rgba(255, 255, 255, 0.85);
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
    flex-shrink: 0;
}
.pp-banner-nav-btn:hover {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.35);
    color: #ffffff;
}
.pp-banner-nav-btn:active,
.pp-banner-dismiss-btn:active {
    transform: scale(0.96);
}
.pp-banner-dismiss-btn:hover {
    background: rgba(239, 68, 68, 0.22);
    border-color: rgba(239, 68, 68, 0.45);
    color: #fca5a5;
}
.pp-banner-grid {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    transition: opacity 0.22s ease, transform 0.22s ease;
}
.pp-banner-grid.is-transitioning {
    opacity: 0.25;
    transform: translateY(3px);
}
.pp-banner-avatar-box {
    flex-shrink: 0;
}
.pp-banner-icon-bubble {
    width: 58px;
    height: 58px;
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
    overflow: hidden;
    padding: 3px;
}
.pp-banner-brand-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 11px;
    display: block;
}
.pp-banner-svg-holder {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.pp-banner-svg-holder svg {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 11px;
}
.pp-banner-body-text {
    flex: 1 1 260px;
    min-width: 220px;
}
.pp-banner-heading {
    font-family: 'Poppins', 'Outfit', sans-serif;
    font-size: 1.08rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 5px 0;
    line-height: 1.32;
    letter-spacing: -0.01em;
}
.pp-banner-subheading {
    font-size: 0.85rem;
    font-weight: 600;
    color: #FDE047;
    margin-left: 4px;
}
.pp-banner-desc {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.88);
    margin: 0 0 9px 0;
    line-height: 1.48;
}
.pp-banner-pills-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}
.pp-value-pill {
    font-size: 0.72rem;
    font-weight: 600;
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.10);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 999px;
    padding: 3px 10px;
    white-space: nowrap;
    flex-shrink: 0;
}
.pp-banner-action-box {
    flex-shrink: 0;
}
.pp-banner-cta-button {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 12px;
    background: var(--banner-gradient, linear-gradient(135deg, #7b03fc 0%, #d946ef 100%));
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    font-weight: 800;
    font-size: 0.9rem;
    text-decoration: none !important;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
    white-space: nowrap;
    flex-shrink: 0;
}
.pp-banner-cta-button:hover {
    transform: translateY(-1px);
    filter: brightness(1.08);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.42);
    color: #ffffff !important;
}
.pp-banner-cta-button:active {
    transform: scale(0.98);
}
.pp-banner-dots-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    flex-wrap: wrap;
}
.pp-banner-dot-btn {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    padding: 0;
    border: none;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}
.pp-banner-dot-core {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.28);
    transition: all 0.25s ease;
}
.pp-banner-dot-btn.is-active .pp-banner-dot-core {
    width: 24px;
    background: #FDE047;
    box-shadow: 0 0 8px rgba(253, 224, 71, 0.6);
}
.pp-banner-dot-btn:hover .pp-banner-dot-core {
    background: rgba(255, 255, 255, 0.65);
}
@media (max-width: 640px) {
    .pp-banner-grid {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .pp-banner-action-box {
        width: 100%;
    }
    .pp-banner-cta-button {
        width: 100%;
    }
    .pp-disclosure-tag {
        display: none;
    }
}
</style>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    const container = document.getElementById(@json($instanceUid));
    if (!container) return;

    const rawDeck = @json($preparedDeck);
    if (!Array.isArray(rawDeck) || rawDeck.length === 0) return;

    const placementKey = container.getAttribute('data-placement') || 'feed';
    const rotationStorageKey = 'pp_eco_last_idx_' + placementKey;

    // Filter out any individual banners the user explicitly dismissed in this browser
    let deck = rawDeck.filter(function(item) {
        try {
            return localStorage.getItem('pp_dismiss_banner_' + item.id) !== 'true';
        } catch (e) {
            return true;
        }
    });

    if (deck.length === 0) {
        container.style.display = 'none';
        return;
    }

    // Advance starting slide across page views so every visit rotates immediately
    let activeIndex = 0;
    try {
        const storedIdx = parseInt(localStorage.getItem(rotationStorageKey) || '-1', 10);
        if (!isNaN(storedIdx) && storedIdx >= 0) {
            activeIndex = (storedIdx + 1) % deck.length;
        }
        localStorage.setItem(rotationStorageKey, String(activeIndex));
    } catch (e) {
        activeIndex = 0;
    }

    const slideBody = container.querySelector('[data-role="slide-body"]');
    const badgeTextEl = container.querySelector('[data-role="badge-text"]');
    const brandNameEl = container.querySelector('[data-role="brand-name"]');
    const disclosureEl = container.querySelector('[data-role="disclosure"]');
    const disclosureTextEl = container.querySelector('[data-role="disclosure-text"]');
    const iconBubbleEl = container.querySelector('[data-role="icon-bubble"]');
    const titleEl = container.querySelector('[data-role="title"]');
    const taglineEl = container.querySelector('[data-role="tagline"]');
    const descEl = container.querySelector('[data-role="description"]');
    const pillsEl = container.querySelector('[data-role="value-pills"]');
    const ctaLinkEl = container.querySelector('[data-role="cta-link"]');
    const ctaTextEl = container.querySelector('[data-role="cta-text"]');
    const counterEl = container.querySelector('[data-role="counter"]');
    const progressBarEl = container.querySelector('[data-role="progress-bar"]');
    const dotButtons = Array.prototype.slice.call(container.querySelectorAll('[data-slide-to]'));

    const ROTATE_INTERVAL_MS = 8000;
    const TICK_MS = 100;
    let elapsedMs = 0;
    let isPaused = false;
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function renderSlide(index, animate) {
        if (deck.length === 0) {
            container.classList.add('pp-banner-hidden');
            setTimeout(function() { container.style.display = 'none'; }, 320);
            return;
        }
        activeIndex = ((index % deck.length) + deck.length) % deck.length;
        const item = deck[activeIndex];
        try {
            localStorage.setItem(rotationStorageKey, String(activeIndex));
        } catch (e) {}

        const applyDomUpdate = function() {
            container.setAttribute('data-banner-id', item.id);
            container.setAttribute('aria-label', (item.badge || 'Spotlight') + ': ' + (item.title || ''));
            container.style.setProperty('--banner-accent', item.accent_color || '#9B5CFB');
            container.style.setProperty('--banner-gradient', item.gradient || 'linear-gradient(135deg, #7b03fc 0%, #d946ef 100%)');

            if (badgeTextEl) badgeTextEl.textContent = item.badge || 'SISTER STUDIO';
            if (brandNameEl) {
                if (item.brand_name) {
                    brandNameEl.textContent = item.brand_name;
                    brandNameEl.style.display = '';
                } else {
                    brandNameEl.style.display = 'none';
                }
            }
            if (disclosureEl && disclosureTextEl) {
                disclosureTextEl.textContent = item.disclosure_text || '';
                disclosureEl.setAttribute('title', item.disclosure_text || '');
            }
            if (iconBubbleEl) {
                if (item.logo_url) {
                    iconBubbleEl.innerHTML =
                        '<img src="' + item.logo_url.replace(/"/g, '&quot;') + '" alt="' + ((item.brand_name || 'App').replace(/"/g, '&quot;')) + ' Logo" class="pp-banner-brand-img" width="48" height="48" loading="lazy">' +
                        '<span class="pp-banner-svg-holder" style="display:none;">' + (item.svg_icon || '') + '</span>';
                    const imgEl = iconBubbleEl.querySelector('img');
                    if (imgEl) {
                        imgEl.addEventListener('error', function() {
                            imgEl.style.display = 'none';
                            const svgHolder = iconBubbleEl.querySelector('.pp-banner-svg-holder');
                            if (svgHolder) svgHolder.style.display = 'flex';
                        });
                    }
                } else if (item.svg_icon) {
                    iconBubbleEl.innerHTML = '<span class="pp-banner-svg-holder">' + item.svg_icon + '</span>';
                } else {
                    iconBubbleEl.innerHTML = '<i class="bi ' + (item.icon || 'bi-stars') + '" aria-hidden="true"></i>';
                }
            }
            if (titleEl) titleEl.textContent = item.title || '';
            if (taglineEl) {
                if (item.tagline) {
                    taglineEl.textContent = '| ' + item.tagline;
                    taglineEl.style.display = '';
                } else {
                    taglineEl.style.display = 'none';
                }
            }
            if (descEl) descEl.textContent = item.description || '';
            if (pillsEl) {
                if (Array.isArray(item.value_pills) && item.value_pills.length > 0) {
                    pillsEl.innerHTML = '';
                    item.value_pills.forEach(function(pillText) {
                        const span = document.createElement('span');
                        span.className = 'pp-value-pill';
                        span.textContent = pillText;
                        pillsEl.appendChild(span);
                    });
                    pillsEl.style.display = 'flex';
                } else {
                    pillsEl.style.display = 'none';
                }
            }
            if (ctaLinkEl) {
                ctaLinkEl.setAttribute('href', item.target_url || '#');
                ctaLinkEl.setAttribute('aria-label', (item.cta_text || 'Explore') + ' (opens in new tab)');
            }
            if (ctaTextEl) ctaTextEl.textContent = item.cta_text || 'Explore Now';
            if (counterEl) counterEl.textContent = (activeIndex + 1) + ' / ' + deck.length;

            dotButtons.forEach(function(btn, dIdx) {
                if (dIdx < deck.length) {
                    btn.style.display = '';
                    const isCurrent = dIdx === activeIndex;
                    btn.classList.toggle('is-active', isCurrent);
                    btn.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
                } else {
                    btn.style.display = 'none';
                }
            });
        };

        if (animate && slideBody && !prefersReducedMotion) {
            slideBody.classList.add('is-transitioning');
            setTimeout(function() {
                applyDomUpdate();
                slideBody.classList.remove('is-transitioning');
            }, 160);
        } else {
            applyDomUpdate();
        }

        elapsedMs = 0;
        if (progressBarEl) progressBarEl.style.width = '0%';
    }

    // Initial render so page load immediately shows the rotated slide
    renderSlide(activeIndex, false);

    // Wire Prev / Next / Dismiss / Dot buttons
    const prevBtn = container.querySelector('[data-action="prev"]');
    const nextBtn = container.querySelector('[data-action="next"]');
    const dismissBtn = container.querySelector('[data-action="dismiss"]');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            renderSlide(activeIndex - 1, true);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            renderSlide(activeIndex + 1, true);
        });
    }
    dotButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetIdx = parseInt(btn.getAttribute('data-slide-to') || '0', 10);
            renderSlide(targetIdx, true);
        });
    });
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function() {
            const currentItem = deck[activeIndex];
            if (currentItem) {
                try {
                    localStorage.setItem('pp_dismiss_banner_' + currentItem.id, 'true');
                } catch (e) {}
            }
            container.classList.add('pp-banner-hidden');
            setTimeout(function() {
                container.style.display = 'none';
            }, 320);
        });
    }

    // Pause auto-rotation on hover or keyboard focus
    container.addEventListener('mouseenter', function() { isPaused = true; });
    container.addEventListener('mouseleave', function() { isPaused = false; });
    container.addEventListener('focusin', function() { isPaused = true; });
    container.addEventListener('focusout', function() { isPaused = false; });

    // Periodic rotation timer ("every now and then")
    if (deck.length > 1 && !prefersReducedMotion) {
        setInterval(function() {
            if (isPaused || document.hidden) return;
            elapsedMs += TICK_MS;
            if (progressBarEl) {
                const pct = Math.min(100, (elapsedMs / ROTATE_INTERVAL_MS) * 100);
                progressBarEl.style.width = pct.toFixed(1) + '%';
            }
            if (elapsedMs >= ROTATE_INTERVAL_MS) {
                renderSlide(activeIndex + 1, true);
            }
        }, TICK_MS);
    }
})();
</script>
@endif
