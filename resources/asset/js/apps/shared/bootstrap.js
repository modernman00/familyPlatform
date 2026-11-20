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

export function getCsrfToken(bootstrap = {}) {
    if (typeof window !== 'undefined' && typeof window.$csrf === 'function') {
        const token = window.$csrf();
        if (token) return String(token);
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
