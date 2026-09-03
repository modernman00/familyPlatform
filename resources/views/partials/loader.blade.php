@php
    $targetNotificationId = $notificationId ?? 'notification';
    $targetLoaderId = $loaderId ?? 'setLoader';
    $targetLoaderText = $loaderText ?? 'Processing your request, please wait...';
    // scriptOnly => render just the Swal bridge + helpers, no banner/loader markup.
    // Use it on pages that already emit their own *_notification element (e.g.
    // BuildFormBStrap forms) so ids don't collide.
    $scriptOnly = $scriptOnly ?? false;

    $swalFlashJson = 'null';
    if (!empty($_SESSION['success']) || !empty($_SESSION['error']) || !empty($_SESSION['status']) || !empty($_SESSION['info']) || !empty($_SESSION['warning'])) {
        $swalFlash = [
            'type'  => !empty($_SESSION['error']) ? 'error' : (!empty($_SESSION['warning']) ? 'warning' : (!empty($_SESSION['info']) ? 'info' : 'success')),
            'title' => !empty($_SESSION['error']) ? 'Error' : (!empty($_SESSION['warning']) ? 'Notice' : (!empty($_SESSION['info']) ? 'Information' : 'Success')),
            'msg'   => (string) ($_SESSION['error'] ?? $_SESSION['warning'] ?? $_SESSION['success'] ?? $_SESSION['status'] ?? $_SESSION['info'] ?? ''),
        ];
        $swalFlashJson = json_encode($swalFlash, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: 'null';
        unset($_SESSION['success'], $_SESSION['error'], $_SESSION['status'], $_SESSION['warning'], $_SESSION['info']);
    }
@endphp

@unless($scriptOnly)
<!-- Inline fallback banner (used only when SweetAlert2 is unavailable) -->
<div id="{{ $targetNotificationId }}_notification" class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3 notification-container" style="display: none;" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-octagon-fill fs-4 me-3 text-danger flex-shrink-0" id="{{ $targetNotificationId }}_notification_icon"></i>
        <div class="flex-grow-1">
            <p id="{{ $targetNotificationId }}_notification_error" class="mb-0 text-dark fw-medium"></p>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('{{ $targetNotificationId }}_notification').style.display='none';" aria-label="Close"></button>
    </div>
</div>

<!-- Modern Animated Spinner / Loader -->
<div id="{{ $targetLoaderId }}" class="app-loader-container text-center my-3" style="display: none;" aria-live="polite" aria-busy="true">
    <div class="d-inline-flex flex-column align-items-center justify-content-center p-3 rounded-3 shadow-sm bg-white border" style="min-width: 220px;">
        <div class="spinner-border text-primary mb-2" role="status" style="width: 2.2rem; height: 2.2rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span class="text-muted fw-semibold small loader-status-text">{{ $targetLoaderText }}</span>
    </div>
</div>
@endunless

<script nonce="{{ $nonce ?? '' }}">
(function () {
    'use strict';

    // ---- Server-side flash message (runs once per page) --------------------
    var __flash = {!! $swalFlashJson !!};
    if (__flash && !window.__familyFlashShown) {
        window.__familyFlashShown = true;
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.notify === 'function') {
                window.notify(__flash.msg, __flash.type, { title: __flash.title });
            }
        });
    }

    // ---- One-time helpers + notification bridge ---------------------------
    if (window.__familyNotify) return;
    window.__familyNotify = true;

    var BRAND = '#4f46e5';

    // The shared JS lib sets the outcome class dynamically (bg-* / is-* / w3-*);
    // a static alert-* class on the same element is only the banner's resting
    // style, so the dynamic markers must win.
    var DYNAMIC_MARKERS = [
        ['success', ['bg-success', 'is-success', 'w3-green']],
        ['error',   ['bg-danger', 'is-danger', 'w3-red']],
        ['warning', ['bg-warning', 'is-warning', 'w3-yellow']],
        ['info',    ['bg-info', 'is-info', 'w3-blue']]
    ];
    var STATIC_MARKERS = [
        ['success', ['alert-success']],
        ['warning', ['alert-warning']],
        ['info',    ['alert-info']],
        ['error',   ['alert-danger']]
    ];

    function scanMarkers(cl, table) {
        for (var i = 0; i < table.length; i++) {
            for (var j = 0; j < table[i][1].length; j++) {
                if (cl.contains(table[i][1][j])) return table[i][0];
            }
        }
        return null;
    }

    function typeFromClassList(cl) {
        return scanMarkers(cl, DYNAMIC_MARKERS) ||
               scanMarkers(cl, STATIC_MARKERS) ||
               'error'; // these banners are red by default in this codebase
    }

    var TITLES = { error: 'Something went wrong', success: 'Done', warning: 'Please check', info: 'Heads up' };

    function toast(icon, message) {
        return window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            didOpen: function (t) {
                t.onmouseenter = window.Swal.stopTimer;
                t.onmouseleave = window.Swal.resumeTimer;
            }
        }).fire({ icon: icon, title: message });
    }

    /**
     * Unified notification entry point.
     *   notify('Saved!', 'success')
     *   notify('Bad password', 'error', { title: 'Login failed' })
     * Falls back to the inline banner / native alert when Swal isn't loaded.
     */
    window.notify = function (message, type, opts) {
        type = type || 'info';
        opts = opts || {};
        message = (message == null ? '' : String(message)).trim();
        if (!message) return;

        if (typeof window.Swal !== 'undefined') {
            if (type === 'success' || type === 'info') {
                return toast(type, message);
            }
            return window.Swal.fire({
                icon: type,
                title: opts.title || TITLES[type] || '',
                text: message,
                confirmButtonColor: BRAND,
                confirmButtonText: opts.confirmButtonText || 'OK'
            });
        }

        // No Swal: reveal the inline banner if one exists, else alert().
        var el = opts.el || document.querySelector('[id$="_notification"]');
        if (el) {
            el.style.display = 'block';
            el.textContent = message;
        } else {
            window.alert(message);
        }
    };

    // Backwards-compatible aliases used elsewhere in the codebase.
    window.showNotification = function (baseId, type, message) {
        var el = document.getElementById(baseId) ||
                 document.getElementById(baseId + '_notification');
        window.notify(message, type === 'danger' ? 'error' : (type === 'green' ? 'success' : type), { el: el });
    };
    window.showAppSwal = function (title, text, icon) {
        return window.notify(text, icon || 'info', { title: title });
    };
    window.showAppToast = function (message, icon) {
        return window.notify(message, icon || 'success');
    };
    window.toggleAppLoader = function (show, message, loaderId) {
        var loaderEl = document.getElementById(loaderId || '{{ $targetLoaderId }}');
        if (!loaderEl) return;
        loaderEl.style.display = (show === false ? 'none' : 'block');
        if (message) {
            var textEl = loaderEl.querySelector('.loader-status-text');
            if (textEl) textEl.textContent = message;
        }
    };

    // ---- MutationObserver: turn inline *_notification banners into Swal ----
    // The shared JS lib (@modernman00/shared-js-lib) reports form outcomes by
    // dropping text into `#<formId>_notification` and toggling display + a
    // bg-danger / bg-success class. We intercept that, show a Swal instead, and
    // hide the raw banner so users never see the unstyled block.
    var recent = {};

    function each(list, fn) {
        Array.prototype.forEach.call(list || [], fn);
    }

    var OUTCOME_RE = /(?:^|\s)(?:bg|is)-(?:danger|success|warning|info)(?:\s|$)/;
    var W3_RE = /(?:^|\s)w3-(?:red|green|yellow|blue)(?:\s|$)/;

    function bridge(el) {
        if (!el || !el.id || el.id.slice(-13) !== '_notification') return;

        var text = (el.textContent || '').trim();
        if (!text || recent[text]) return;

        // Fire only once the shared lib (or app code) has actually populated the
        // banner: it sets an inline style.display and/or adds an outcome class.
        // Note: some auth pages hide the banner with `.noDisplay{display:none
        // !important}`, so we must NOT gate on computed display — that would
        // suppress the message entirely. The inline flag / class is the signal.
        var inlineShown = el.style.display && el.style.display !== 'none';
        var hasOutcomeClass = OUTCOME_RE.test(el.className) || W3_RE.test(el.className);
        if (!inlineShown && !hasOutcomeClass) return;

        if (typeof window.Swal === 'undefined') return; // keep the banner as-is

        recent[text] = true;
        setTimeout(function () { delete recent[text]; }, 3000);

        var type = typeFromClassList(el.classList);

        // Hide the raw banner; the Swal is now the notification.
        el.style.display = 'none';
        el.classList.add('noDisplay');
        el.textContent = '';
        each(['bg-danger', 'bg-success', 'is-danger', 'is-success', 'notification'], function (c) {
            el.classList.remove(c);
        });

        window.notify(text, type);
    }

    function scan(root) {
        each((root || document).querySelectorAll('[id$="_notification"]'), bridge);
    }

    function closestNotification(node) {
        var t = node && node.nodeType === 1 ? node : (node ? node.parentElement : null);
        while (t && t !== document.body) {
            if (t.id && t.id.slice(-13) === '_notification') return t;
            t = t.parentElement;
        }
        return null;
    }

    var observer = new MutationObserver(function (mutations) {
        each(mutations, function (m) {
            if (m.type === 'attributes' || m.type === 'characterData') {
                var hit = closestNotification(m.target);
                if (hit) bridge(hit);
            } else if (m.type === 'childList') {
                each(m.addedNodes, function (n) {
                    if (n.nodeType !== 1) return;
                    if (n.id && n.id.slice(-13) === '_notification') bridge(n);
                    else scan(n);
                });
                bridge(m.target);
            }
        });
    });

    function start() {
        scan(document);
        observer.observe(document.body, {
            subtree: true,
            childList: true,
            characterData: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
</script>
