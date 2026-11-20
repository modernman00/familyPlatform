import { getCsrfToken } from './bootstrap';

/**
 * Hardened fetch wrapper for Preact/React islands.
 * - Enforces X-Requested-With & X-CSRF-TOKEN on state-changing requests
 * - Injects csrf_token into FormData or JSON payloads when missing
 * - Applies AbortController timeout (default 20s)
 * - Normalises JSON and error responses
 */
export async function apiFetch(url, options = {}, bootstrap = {}) {
    const method = String(options.method || 'GET').toUpperCase();
    const timeoutMs = typeof options.timeoutMs === 'number' ? options.timeoutMs : 20000;
    const headers = new Headers(options.headers || {});

    if (!headers.has('X-Requested-With')) {
        headers.set('X-Requested-With', 'XMLHttpRequest');
    }
    if (!headers.has('Accept')) {
        headers.set('Accept', 'application/json');
    }

    let body = options.body;
    const isMutating = method !== 'GET' && method !== 'HEAD';

    if (isMutating) {
        const csrf = getCsrfToken(bootstrap);
        if (csrf && !headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', csrf);
        }

        if (body instanceof FormData) {
            if (csrf && !body.has('csrf_token') && !body.has('_token')) {
                body.append('csrf_token', csrf);
            }
        } else if (body && typeof body === 'object' && !(body instanceof Blob) && !(body instanceof ArrayBuffer)) {
            if (!headers.has('Content-Type')) {
                headers.set('Content-Type', 'application/json');
            }
            const payload = csrf && !body.csrf_token && !body._token
                ? { ...body, csrf_token: csrf }
                : body;
            body = JSON.stringify(payload);
        }
    }

    const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
    const timer = controller ? setTimeout(() => controller.abort(), timeoutMs) : null;

    try {
        const response = await fetch(url, {
            method,
            headers,
            body,
            credentials: 'same-origin',
            signal: options.signal || (controller ? controller.signal : undefined),
        });

        const contentType = response.headers.get('content-type') || '';
        let data = null;
        if (contentType.includes('application/json')) {
            data = await response.json().catch(() => null);
        } else {
            const text = await response.text().catch(() => '');
            try {
                data = JSON.parse(text);
            } catch {
                data = { message: text };
            }
        }

        if (!response.ok) {
            const errMessage = (data && (data.message || data.error))
                || `Request failed (${response.status})`;
            const error = new Error(errMessage);
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    } catch (err) {
        if (err && err.name === 'AbortError') {
            const timeoutErr = new Error('Request timed out. Please check your connection and try again.');
            timeoutErr.status = 408;
            throw timeoutErr;
        }
        throw err;
    } finally {
        if (timer) clearTimeout(timer);
    }
}
