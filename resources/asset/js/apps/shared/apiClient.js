import { getCsrfToken, getXsrfCookieTokens, syncCsrfTokenDom } from './bootstrap';

function isStaleCsrfMessage(message) {
    return typeof message === 'string' && message.toLowerCase().includes('not familiar with');
}

function resolveRefreshedCsrfToken(failedToken, bootstrap = {}) {
    const candidates = [
        ...getXsrfCookieTokens(),
        typeof document !== 'undefined'
            ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            : '',
        bootstrap && bootstrap.csrfToken ? String(bootstrap.csrfToken) : '',
    ].filter(Boolean);

    const fresh = candidates.find((candidate) => candidate !== failedToken);
    return fresh || candidates[0] || '';
}

/**
 * Hardened fetch wrapper for Preact/React islands.
 * - Enforces X-Requested-With, X-CSRF-TOKEN & X-XSRF-TOKEN on state-changing requests
 * - Injects csrf_token into FormData or JSON payloads when missing
 * - Automatically retries once on 401 stale-CSRF ("not familiar with the nature of your activities")
 *   using the refreshed Set-Cookie: XSRF-TOKEN (matching the global axios interceptor)
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
    let usedCsrf = '';

    if (isMutating) {
        const csrf = getCsrfToken(bootstrap);
        usedCsrf = csrf || '';
        if (csrf && !headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', csrf);
        }
        if (csrf && !headers.has('X-XSRF-TOKEN')) {
            headers.set('X-XSRF-TOKEN', csrf);
        }

        if (body instanceof FormData) {
            if (csrf) {
                if (options._csrfRetried) {
                    body.set('csrf_token', csrf);
                    if (body.has('_token')) body.set('_token', csrf);
                    if (body.has('token')) body.set('token', csrf);
                } else if (!body.has('csrf_token') && !body.has('_token')) {
                    body.append('csrf_token', csrf);
                }
            }
        } else if (body && typeof body === 'object' && !(body instanceof Blob) && !(body instanceof ArrayBuffer) && !(body instanceof URLSearchParams)) {
            if (!headers.has('Content-Type')) {
                headers.set('Content-Type', 'application/json');
            }
            const payload = csrf && (options._csrfRetried || (!body.csrf_token && !body._token))
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

            if (isMutating && response.status === 401 && !options._csrfRetried && isStaleCsrfMessage(errMessage)) {
                let refreshedToken = resolveRefreshedCsrfToken(usedCsrf, bootstrap);
                if (!refreshedToken || refreshedToken === usedCsrf) {
                    await fetch('/api/hashtags/trending?limit=1', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).catch(() => null);
                    refreshedToken = resolveRefreshedCsrfToken(usedCsrf, bootstrap);
                }

                if (refreshedToken) {
                    syncCsrfTokenDom(refreshedToken);
                }

                const retryHeaders = new Headers(options.headers || {});
                retryHeaders.delete('X-CSRF-TOKEN');
                retryHeaders.delete('x-csrf-token');
                retryHeaders.delete('X-XSRF-TOKEN');
                retryHeaders.delete('x-xsrf-token');

                const nextBootstrap = refreshedToken
                    ? { ...bootstrap, csrfToken: refreshedToken, _overrideCsrfToken: refreshedToken }
                    : bootstrap;

                return apiFetch(
                    url,
                    { ...options, headers: retryHeaders, _csrfRetried: true },
                    nextBootstrap
                );
            }

            const error = new Error(errMessage);
            error.status = response.status;
            error.data = data;
            throw error;
        }

        const latestCookieToken = getXsrfCookieTokens()[0];
        if (latestCookieToken) {
            syncCsrfTokenDom(latestCookieToken);
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
