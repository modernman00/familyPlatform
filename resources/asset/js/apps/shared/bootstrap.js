/**
 * Typed JSON Bridge for Isolated Preact/React Islands.
 * Reads <script type="application/json" id="..."> safely without inline JS execution (CSP-safe).
 */

export function readIslandBootstrap(scriptId, fallback = {}) {
    if (typeof document === 'undefined') {
        return { ...fallback };
    }

    const el = document.getElementById(scriptId);
    if (el && el.textContent) {
        try {
            const parsed = JSON.parse(el.textContent);
            if (parsed && typeof parsed === 'object') {
                return { ...fallback, ...parsed };
            }
        } catch (err) {
            console.warn(`[IslandBootstrap] Failed to parse #${scriptId}:`, err);
        }
    }

    if (typeof window !== 'undefined' && window.__FP_BOOTSTRAP__ && typeof window.__FP_BOOTSTRAP__ === 'object') {
        return { ...fallback, ...window.__FP_BOOTSTRAP__ };
    }

    return { ...fallback };
}

export function getXsrfCookieTokens() {
    if (typeof document === 'undefined' || typeof document.cookie !== 'string' || !document.cookie) {
        return [];
    }

    const hexTokens = [];
    const otherTokens = [];
    const parts = document.cookie.split(';');

    for (const part of parts) {
        const eqIdx = part.indexOf('=');
        if (eqIdx === -1) continue;
        const name = part.slice(0, eqIdx).trim();
        if (name !== 'XSRF-TOKEN') continue;
        const rawVal = part.slice(eqIdx + 1).trim();
        if (!rawVal) continue;
        let decoded = rawVal;
        try {
            decoded = decodeURIComponent(rawVal);
        } catch {
            decoded = rawVal;
        }
        if (!decoded) continue;
        if (/^[0-9a-f]{64}$/i.test(decoded)) {
            if (!hexTokens.includes(decoded)) hexTokens.push(decoded);
        } else if (!otherTokens.includes(decoded)) {
            otherTokens.push(decoded);
        }
    }

    return [...hexTokens, ...otherTokens];
}

export function syncCsrfTokenDom(token) {
    if (!token || typeof document === 'undefined') return;
    const clean = String(token);
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', clean);
    }
    if (typeof window !== 'undefined' && window.__FP_BOOTSTRAP__ && typeof window.__FP_BOOTSTRAP__ === 'object') {
        window.__FP_BOOTSTRAP__.csrfToken = clean;
    }
}

export function getCsrfToken(bootstrap = {}) {
    if (typeof window !== 'undefined' && typeof window.$csrf === 'function') {
        const token = window.$csrf();
        if (token) return String(token);
    }

    if (bootstrap && bootstrap._overrideCsrfToken) {
        return String(bootstrap._overrideCsrfToken);
    }

    const cookieTokens = getXsrfCookieTokens();
    if (cookieTokens.length > 0) {
        return String(cookieTokens[0]);
    }

    if (typeof document !== 'undefined') {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return String(meta.getAttribute('content'));
        }
        const hidden = document.querySelector('input[name="csrf_token"], input[name="_token"]');
        if (hidden && hidden.value) {
            return String(hidden.value);
        }
    }

    return bootstrap && bootstrap.csrfToken ? String(bootstrap.csrfToken) : '';
}

export function notifyToast(title, icon = 'success') {
    if (typeof window !== 'undefined' && typeof window.$toast === 'function') {
        window.$toast(title, icon);
        return;
    }
    if (typeof window !== 'undefined' && window.Swal && typeof window.Swal.fire === 'function') {
        window.Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
        });
    }
}
