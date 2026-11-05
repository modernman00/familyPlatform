{{-- 
  PWA In-App Splash Screen Overlay: FamilyPlatform (Magical & Slow Network Animated Edition)
  Renders an instantaneous branded, magical splash screen on iOS/Android PWA standalone cold starts.
  Zero impact / invisible on standard web browsing or internal session navigations.
  Features:
  - Ambient breathing radiant halo & specular light shimmer sweep across logo badge
  - Magical starlight sparkle micro-animation
  - Automatic slow-network detection & dual-ring glowing spinner with British English reassurance
--}}
<div id="fp-pwa-splash" class="fp-pwa-splash" aria-hidden="true" role="presentation">
    <div class="fp-pwa-splash-content">
        <!-- Ambient Magical Glow / Halo -->
        <div class="fp-pwa-splash-glow" aria-hidden="true"></div>

        <!-- Animated Logo Wrap with Specular Shimmer & Float -->
        <div class="fp-pwa-splash-logo-wrap">
            <div class="fp-pwa-splash-shimmer" aria-hidden="true"></div>
            <div class="fp-pwa-splash-badge" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <img src="/public/apple-touch-icon.png"
                 alt="FamilyPlatform"
                 class="fp-pwa-splash-logo"
                 width="96"
                 height="96"
                 decoding="async"
                 onerror="if(this.src.indexOf('/public/img/favicon/')===-1){this.src='/public/img/favicon/apple-touch-icon.png';}else if(this.src.indexOf('/apple-touch-icon.png')===-1){this.src='/apple-touch-icon.png';}else{this.style.display='none';}">
            <!-- Starlight Sparkle Micro-Accent -->
            <div class="fp-pwa-splash-sparkle" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0L14.59 9.41L24 12L14.59 14.59L12 24L9.41 14.59L0 12L9.41 9.41L12 0Z"/>
                </svg>
            </div>
        </div>

        <!-- Luminous App Title -->
        <div class="fp-pwa-splash-title">FamilyPlatform</div>

        <!-- Fast Network Indicator (Animated Pulse Bar) -->
        <div class="fp-pwa-splash-pulse-bar" aria-hidden="true">
            <div class="fp-pwa-splash-pulse-track"></div>
        </div>

        <!-- Slow Network Indicator (Magical Dual-Ring Spinner + Status) -->
        <div class="fp-pwa-splash-slow-indicator" aria-live="polite">
            <div class="fp-pwa-splash-spinner" aria-hidden="true">
                <div class="fp-pwa-splash-spinner-outer"></div>
                <div class="fp-pwa-splash-spinner-inner"></div>
            </div>
            <div class="fp-pwa-splash-status">Loading application...</div>
        </div>
    </div>
</div>

<style>
#fp-pwa-splash {
    display: none;
}
html.fp-pwa-splash-active #fp-pwa-splash {
    display: flex !important;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    height: 100dvh;
    z-index: 2147483647;
    background-color: #ffffff;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    opacity: 1;
    transition: opacity 0.35s ease-out, visibility 0.35s ease-out;
    pointer-events: auto;
    user-select: none;
    -webkit-user-select: none;
}
.fp-pwa-splash-content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
    animation: fpSplashScale 0.42s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Ambient Magical Glow / Halo */
.fp-pwa-splash-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 200px;
    height: 200px;
    transform: translate(-50%, -60%);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(163, 22, 33, 0.28) 0%, rgba(230, 57, 70, 0.16) 45%, transparent 70%);
    filter: blur(28px);
    opacity: 0.8;
    animation: fpSplashGlow 3s ease-in-out infinite alternate;
    pointer-events: none;
    z-index: 0;
}
@keyframes fpSplashGlow {
    0% { transform: translate(-50%, -60%) scale(0.85); opacity: 0.5; }
    100% { transform: translate(-50%, -60%) scale(1.18); opacity: 0.9; }
}

/* Animated Logo Container */
.fp-pwa-splash-logo-wrap {
    position: relative;
    width: 96px;
    height: 96px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    animation: fpSplashFloat 3.2s ease-in-out infinite alternate;
}
@keyframes fpSplashFloat {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-4px) rotate(0.4deg); }
    100% { transform: translateY(3px) rotate(-0.4deg); }
}

.fp-pwa-splash-badge {
    position: absolute;
    top: 0;
    left: 0;
    width: 96px;
    height: 96px;
    border-radius: 22px;
    background: linear-gradient(135deg, #a31621 0%, #c41e3a 50%, #e63946 100%);
    box-shadow: 0 16px 40px rgba(163, 22, 33, 0.25), 0 4px 16px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    z-index: 1;
}
.fp-pwa-splash-logo {
    position: relative;
    width: 96px;
    height: 96px;
    border-radius: 22px;
    box-shadow: 0 16px 40px rgba(163, 22, 33, 0.25), 0 4px 16px rgba(0, 0, 0, 0.08);
    display: block;
    z-index: 2;
    object-fit: cover;
}

/* Specular Shimmer Sheen */
.fp-pwa-splash-shimmer {
    position: absolute;
    inset: 0;
    border-radius: 22px;
    overflow: hidden;
    pointer-events: none;
    z-index: 3;
}
.fp-pwa-splash-shimmer::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        115deg,
        transparent 38%,
        rgba(255, 255, 255, 0.55) 50%,
        transparent 62%
    );
    transform: rotate(25deg) translateY(-130%);
    animation: fpSplashShimmer 2.6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}
@keyframes fpSplashShimmer {
    0% { transform: rotate(25deg) translateY(-130%); }
    38% { transform: rotate(25deg) translateY(130%); }
    100% { transform: rotate(25deg) translateY(130%); }
}

/* Magical Starlight Sparkle */
.fp-pwa-splash-sparkle {
    position: absolute;
    top: -8px;
    right: -8px;
    color: #f59e0b;
    filter: drop-shadow(0 0 6px rgba(245, 158, 11, 0.9));
    z-index: 4;
    animation: fpSplashSparkle 2.2s ease-in-out infinite;
}
@keyframes fpSplashSparkle {
    0%, 100% { transform: scale(0.65) rotate(0deg); opacity: 0.35; }
    50% { transform: scale(1.15) rotate(90deg); opacity: 1; }
}

/* Luminous App Title */
.fp-pwa-splash-title {
    position: relative;
    z-index: 1;
    font-family: var(--font-heading, "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1e293b;
    margin-bottom: 20px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    animation: fpSplashTitle 0.6s ease-out forwards;
}
@keyframes fpSplashTitle {
    0% { opacity: 0; transform: translateY(4px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Fast Network Indicator (Pulse Bar) */
.fp-pwa-splash-pulse-bar {
    position: relative;
    z-index: 1;
    width: 48px;
    height: 4px;
    border-radius: 999px;
    background: rgba(163, 22, 33, 0.15);
    overflow: hidden;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fp-pwa-splash-pulse-track {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #a31621, #e63946);
    border-radius: 999px;
    animation: fpSplashPulse 1.1s ease-in-out infinite alternate;
}
@keyframes fpSplashPulse {
    0% { transform: translateX(-60%); }
    100% { transform: translateX(60%); }
}

/* Slow Network Mode & Magical Dual-Ring Spinner */
.fp-pwa-splash-slow-indicator {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 4px;
    z-index: 1;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.35s ease, transform 0.35s ease;
}
.fp-pwa-splash-slow-active .fp-pwa-splash-pulse-bar {
    display: none !important;
}
.fp-pwa-splash-slow-active .fp-pwa-splash-slow-indicator {
    display: flex !important;
    opacity: 1;
    transform: translateY(0);
}

.fp-pwa-splash-spinner {
    position: relative;
    width: 32px;
    height: 32px;
}
.fp-pwa-splash-spinner-outer {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2.5px solid rgba(163, 22, 33, 0.15);
    border-top-color: #e63946;
    border-right-color: #a31621;
    animation: fpSplashSpin 0.9s cubic-bezier(0.55, 0.15, 0.45, 0.85) infinite;
    filter: drop-shadow(0 0 6px rgba(163, 22, 33, 0.35));
}
.fp-pwa-splash-spinner-inner {
    position: absolute;
    inset: 5px;
    border-radius: 50%;
    border: 2px solid transparent;
    border-bottom-color: #a31621;
    animation: fpSplashSpinReverse 1.3s linear infinite;
    opacity: 0.85;
}
@keyframes fpSplashSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
@keyframes fpSplashSpinReverse {
    0% { transform: rotate(360deg); }
    100% { transform: rotate(0deg); }
}

.fp-pwa-splash-status {
    font-family: var(--font-heading, "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #475569;
    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
    animation: fpSplashPulseText 1.6s ease-in-out infinite alternate;
}
@keyframes fpSplashPulseText {
    0% { opacity: 0.6; }
    100% { opacity: 1; }
}

@keyframes fpSplashScale {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
.fp-pwa-splash-fade {
    opacity: 0 !important;
    pointer-events: none !important;
}
</style>

<script nonce="{{ $nonce ?? '' }}">
(function() {
    try {
        var splash = document.getElementById('fp-pwa-splash');
        if (!splash || !document.documentElement.classList.contains('fp-pwa-splash-active')) {
            return;
        }

        var dismissed = false;
        function dismissSplash() {
            if (dismissed) return;
            dismissed = true;
            try {
                sessionStorage.setItem('fp_pwa_splash_shown', '1');
            } catch (e) {}
            splash.classList.add('fp-pwa-splash-fade');
            setTimeout(function() {
                document.documentElement.classList.remove('fp-pwa-splash-active');
                if (splash.parentNode) {
                    splash.parentNode.removeChild(splash);
                }
            }, 360);
        }

        // Automatic Slow Network Detection via Network Information API
        var isSlowConnection = false;
        if (navigator.connection) {
            var conn = navigator.connection;
            if (conn.saveData || /^(slow-2g|2g|3g)$/i.test(conn.effectiveType) || (conn.downlink && conn.downlink < 1.2)) {
                isSlowConnection = true;
            }
        }

        function activateSlowNetworkMode() {
            if (dismissed || !splash) return;
            splash.classList.add('fp-pwa-splash-slow-active');
        }

        // If slow connection already known, activate spinner quickly (after 250ms),
        // otherwise if page load exceeds 750ms, transition into slow network mode with spinner.
        var slowTimer = setTimeout(activateSlowNetworkMode, isSlowConnection ? 250 : 750);

        // Snappy dismissal: Never block the user behind an opaque splash
        // Watchdog failsafe capped at 900ms max so user is NEVER trapped
        var watchdog = setTimeout(dismissSplash, 900);

        function scheduleDismissal() {
            clearTimeout(slowTimer);
            setTimeout(dismissSplash, 180);
        }

        if (document.readyState === 'interactive' || document.readyState === 'complete') {
            scheduleDismissal();
        } else {
            document.addEventListener('DOMContentLoaded', scheduleDismissal, { once: true });
            window.addEventListener('load', scheduleDismissal, { once: true });
        }
    } catch (err) {
        var el = document.getElementById('fp-pwa-splash');
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
        document.documentElement.classList.remove('fp-pwa-splash-active');
    }
})();
</script>