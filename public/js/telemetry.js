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

    // Helper to check cookie consent
    function isConsentGranted() {
        if (window.cookieConsent && typeof window.cookieConsent.allows === 'function') {
            return window.cookieConsent.allows('analytics');
        }
        const m = document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]+)/);
        return m ? decodeURIComponent(m[1]) === 'all' : false;
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
        if (!isConsentGranted()) return;

        try {
            const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
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
                coords: { x: Math.round(e.clientX), y: Math.round(e.clientY) }
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
                    tag: target.tagName.toLowerCase()
                });
            }
        }
    }, { passive: true });

    // Expose Public Telemetry API for intentional app funnels
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
        }
    };

    window.__familyTelemetry = true;
})();
