/**
 * FamilyPlatform & Fleetwide Privacy-Preserving Telemetry Engine (N1)
 *
 * Self-Hosted, GDPR-Compliant Funnel & UX Friction Telemetry:
 * - Rage Click Detection (3+ clicks within 700ms in a 30px radius)
 * - Dead Click Detection (clicks on non-interactive, non-clickable elements)
 * - Funnel Step Telemetry (e.g. onboarding, tree explore, viral invites)
 * - Zero PII Transmission (never collects inputs, passwords, photos, or personal messages)
 * - Cookie Consent Gated (respects user cookie consent setting)
 */

(function () {
    'use strict';

    if (window.__familyTelemetry) return;

    const TELEMETRY_ENDPOINT = '/api/telemetry/event';
    const RAGE_CLICK_THRESHOLD = 3;
    const RAGE_CLICK_WINDOW_MS = 700;
    const RAGE_CLICK_RADIUS_PX = 30;

    let clickHistory = [];

    // Helper to check cookie consent (allows anonymous first-party RUM unless explicitly rejected)
    function isConsentGranted(isAnonymousVitals) {
        if (window.cookieConsent && typeof window.cookieConsent.allows === 'function') {
            return window.cookieConsent.allows('analytics');
        }
        const m = document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]+)/);
        if (m) {
            const val = decodeURIComponent(m[1]);
            if (val === 'essential' || val === 'reject' || val === 'denied') return false;
            return val === 'all' || val === 'accepted';
        }
        return Boolean(isAnonymousVitals);
    }

    // Classify viewport into canonical mobile/desktop ergonomic tiers
    function getViewportProfile() {
        const w = window.innerWidth || document.documentElement.clientWidth || 0;
        if (w <= 380) return 'compact_375';
        if (w <= 400) return 'standard_390';
        if (w <= 480) return 'large_412';
        if (w <= 1024) return 'tablet';
        return 'desktop';
    }

    // Helper to extract safe non-PII element descriptor
    function getElementDescriptor(el) {
        if (!el || el === document.body || el === document.documentElement) return 'body';
        const tag = el.tagName.toLowerCase();
        const id = el.id ? `#${el.id}` : '';
        const role = el.getAttribute('role') ? `[role="${el.getAttribute('role')}"]` : '';
        const cls = el.className && typeof el.className === 'string' 
            ? '.' + el.className.split(/\s+/).filter(c => c && !c.startsWith('x-') && !c.includes('{')).slice(0, 2).join('.') 
            : '';
        return `${tag}${id}${cls}${role}`.substring(0, 80);
    }

    // Helper to send telemetry event
    async function sendTelemetryEvent(eventType, metadata = {}) {
        const isAnonVitals = eventType === 'rum_vitals' || eventType === 'inp_poor';
        if (!isConsentGranted(isAnonVitals)) return;

        try {
            const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                           || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                           || document.querySelector('input[name="token"]')?.value 
                           || '';

            const payload = {
                event: eventType,
                url: window.location.pathname,
                viewport_width: window.innerWidth,
                viewport_height: window.innerHeight,
                metadata: metadata,
                timestamp: Date.now()
            };

            if (navigator.sendBeacon) {
                const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                navigator.sendBeacon(TELEMETRY_ENDPOINT, blob);
            } else {
                fetch(TELEMETRY_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload),
                    keepalive: true
                }).catch(() => {});
            }
        } catch (e) {
            // Silently swallow client telemetry network errors
        }
    }

    // 1. Rage Click & Dead Click Listener
    document.addEventListener('click', function (e) {
        const now = Date.now();
        const target = e.target;
        const descriptor = getElementDescriptor(target);

        // Prune old clicks outside the rage window
        clickHistory = clickHistory.filter(c => now - c.time < RAGE_CLICK_WINDOW_MS);

        // Record current click
        clickHistory.push({ time: now, x: e.clientX, y: e.clientY, target: descriptor });

        // Check for Rage Clicks
        const nearbyClicks = clickHistory.filter(c => {
            const dx = Math.abs(c.x - e.clientX);
            const dy = Math.abs(c.y - e.clientY);
            return dx <= RAGE_CLICK_RADIUS_PX && dy <= RAGE_CLICK_RADIUS_PX;
        });

        if (nearbyClicks.length >= RAGE_CLICK_THRESHOLD) {
            sendTelemetryEvent('rage_click', {
                target: descriptor,
                click_count: nearbyClicks.length,
                coord_x: Math.round(e.clientX),
                coord_y: Math.round(e.clientY),
                viewport_profile: getViewportProfile()
            });
            clickHistory = []; // Reset after firing
        }

        // Check for Dead Clicks (clicks on static layout elements without pointer cursor, href, or interactive parent)
        const isInteractive = target.closest('a, button, input, textarea, select, label, [role="button"], [tabindex], [data-bs-toggle], [x-on\\:click], [\\@click]');
        if (!isInteractive && !target.hasAttribute('onclick')) {
            const computedStyle = window.getComputedStyle(target);
            if (computedStyle.cursor !== 'pointer') {
                // If user clicks on non-interactive text/div repeatedly
                sendTelemetryEvent('dead_click', {
                    target: descriptor,
                    tag: target.tagName.toLowerCase(),
                    viewport_profile: getViewportProfile()
                });
            }
        }
    }, { passive: true });

    // 2. Sovereign RUM Core Web Vitals & Hardware Ergonomics Collector (v2.0)
    const rumState = {
        lcp_ms: 0,
        lcp_element: 'none',
        inp_ms: 0,
        inp_target: 'none',
        cls_score: 0,
        longtask_count: 0,
        max_longtask_ms: 0,
        ttfb_ms: 0,
        flushed: false
    };

    if (typeof PerformanceObserver !== 'undefined') {
        // 2a. Largest Contentful Paint (LCP)
        try {
            const lcpObs = new PerformanceObserver(function (list) {
                const entries = list.getEntries();
                const last = entries[entries.length - 1];
                if (last) {
                    rumState.lcp_ms = Math.round(last.startTime || last.renderTime || last.loadTime || 0);
                    if (last.element) {
                        rumState.lcp_element = getElementDescriptor(last.element);
                    }
                }
            });
            lcpObs.observe({ type: 'largest-contentful-paint', buffered: true });
        } catch (_) {}

        // 2b. Interaction to Next Paint (INP) via Event Timing API
        try {
            const inpObs = new PerformanceObserver(function (list) {
                const entries = list.getEntries();
                for (let i = 0; i < entries.length; i++) {
                    const entry = entries[i];
                    if (!entry.interactionId && entry.name !== 'click' && entry.name !== 'keydown' && entry.name !== 'pointerdown') {
                        continue;
                    }
                    const dur = Math.round(entry.duration || 0);
                    if (dur > rumState.inp_ms) {
                        rumState.inp_ms = dur;
                        rumState.inp_target = entry.target ? getElementDescriptor(entry.target) : entry.name;
                    }
                }
            });
            inpObs.observe({ type: 'event', buffered: true, durationThreshold: 40 });
        } catch (_) {}

        // 2c. Cumulative Layout Shift (CLS) - session windowing without user input
        try {
            let sessionCls = 0;
            const clsObs = new PerformanceObserver(function (list) {
                const entries = list.getEntries();
                for (let i = 0; i < entries.length; i++) {
                    const entry = entries[i];
                    if (!entry.hadRecentInput && typeof entry.value === 'number') {
                        sessionCls += entry.value;
                        rumState.cls_score = Math.round(sessionCls * 10000) / 10000;
                    }
                }
            });
            clsObs.observe({ type: 'layout-shift', buffered: true });
        } catch (_) {}

        // 2d. Main-Thread Long Tasks (> 50ms Jank Detector)
        try {
            const ltObs = new PerformanceObserver(function (list) {
                const entries = list.getEntries();
                for (let i = 0; i < entries.length; i++) {
                    const dur = Math.round(entries[i].duration || 0);
                    rumState.longtask_count += 1;
                    if (dur > rumState.max_longtask_ms) {
                        rumState.max_longtask_ms = dur;
                    }
                }
            });
            ltObs.observe({ type: 'longtask', buffered: true });
        } catch (_) {}
    }

    function computeNavigationTimings() {
        try {
            const navEntries = performance.getEntriesByType && performance.getEntriesByType('navigation');
            if (navEntries && navEntries.length > 0) {
                const nav = navEntries[0];
                rumState.ttfb_ms = Math.max(0, Math.round(nav.responseStart - nav.requestStart));
                if (!rumState.lcp_ms && nav.domContentLoadedEventEnd > 0) {
                    rumState.lcp_ms = Math.round(nav.domContentLoadedEventEnd);
                }
            }
        } catch (_) {}
    }

    function getVitalsSnapshot() {
        computeNavigationTimings();
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const isStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
        return {
            lcp_ms: rumState.lcp_ms,
            lcp_element: rumState.lcp_element,
            inp_ms: rumState.inp_ms,
            inp_target: rumState.inp_target,
            cls_score: String(rumState.cls_score),
            longtask_count: rumState.longtask_count,
            max_longtask_ms: rumState.max_longtask_ms,
            ttfb_ms: rumState.ttfb_ms,
            dom_nodes: document.getElementsByTagName('*').length,
            viewport_profile: getViewportProfile(),
            net_type: conn && conn.effectiveType ? String(conn.effectiveType) : 'unknown',
            pwa_mode: isStandalone ? 'standalone' : 'browser'
        };
    }

    function flushRumVitals(force) {
        if (rumState.flushed && !force) return;
        rumState.flushed = true;
        const snapshot = getVitalsSnapshot();
        sendTelemetryEvent('rum_vitals', snapshot);
    }

    // Flush consolidated RUM Vitals after initial settle (3.5s) or on page hide
    if (document.readyState === 'complete') {
        setTimeout(function () { flushRumVitals(false); }, 3500);
    } else {
        window.addEventListener('load', function () {
            setTimeout(function () { flushRumVitals(false); }, 3500);
        }, { once: true, passive: true });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            flushRumVitals(true);
        }
    });

    // Expose Public Telemetry API for intentional app funnels & RUM inspection
    window.familyTelemetry = {
        track: function (eventName, data = {}) {
            sendTelemetryEvent(eventName, data);
        },
        trackFunnelStep: function (stepName, stepNumber, totalSteps) {
            sendTelemetryEvent('funnel_step', {
                step: stepName,
                step_number: stepNumber,
                total_steps: totalSteps
            });
        },
        getVitalsSnapshot: getVitalsSnapshot,
        flushVitals: function () {
            flushRumVitals(true);
        }
    };

    window.__familyTelemetry = true;
})();
