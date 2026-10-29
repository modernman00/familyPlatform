{{-- 
  PWA In-App Splash Screen Overlay: FamilyPlatform
  Renders an instantaneous branded splash screen on iOS/Android PWA standalone cold starts.
  Zero impact / invisible on standard web browsing or internal session navigations.
--}}
<div id="fp-pwa-splash" class="fp-pwa-splash" aria-hidden="true" role="presentation">
    <div class="fp-pwa-splash-content">
        <div class="fp-pwa-splash-logo-wrap">
            <img src="/apple-touch-icon.png" alt="FamilyPlatform" class="fp-pwa-splash-logo" width="96" height="96" decoding="async">
        </div>
        <div class="fp-pwa-splash-title">FamilyPlatform</div>
        <div class="fp-pwa-splash-pulse-bar">
            <div class="fp-pwa-splash-pulse-track"></div>
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
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px;
    animation: fpSplashScale 0.38s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.fp-pwa-splash-logo-wrap {
    position: relative;
    width: 96px;
    height: 96px;
    margin-bottom: 18px;
}
.fp-pwa-splash-logo {
    width: 96px;
    height: 96px;
    border-radius: 22px;
    box-shadow: 0 16px 40px rgba(163, 22, 33, 0.25), 0 4px 16px rgba(0, 0, 0, 0.08);
    display: block;
}
.fp-pwa-splash-title {
    font-family: var(--font-heading, "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #1e293b;
    margin-bottom: 22px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.fp-pwa-splash-pulse-bar {
    width: 44px;
    height: 4px;
    border-radius: 999px;
    background: rgba(163, 22, 33, 0.15);
    overflow: hidden;
    position: relative;
}
.fp-pwa-splash-pulse-track {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #a31621, #e63946);
    border-radius: 999px;
    animation: fpSplashPulse 1.1s ease-in-out infinite alternate;
}
@keyframes fpSplashScale {
    0% { opacity: 0; transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}
@keyframes fpSplashPulse {
    0% { transform: translateX(-60%); }
    100% { transform: translateX(60%); }
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

        // Failsafe watchdog timer (max 750ms so user is never delayed)
        var watchdog = setTimeout(dismissSplash, 750);

        if (document.readyState === 'complete') {
            setTimeout(dismissSplash, 350);
        } else {
            window.addEventListener('load', function() {
                clearTimeout(watchdog);
                setTimeout(dismissSplash, 250);
            }, { once: true });
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
