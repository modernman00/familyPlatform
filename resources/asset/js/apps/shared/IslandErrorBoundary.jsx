import { useErrorBoundary } from 'preact/hooks';

/**
 * Sovereign Preact Error Boundary (Preact 11 `useErrorBoundary` hook).
 * Isolates runtime exceptions at the island or card level so a single bad payload
 * or WebGL failure never unmounts the surrounding page, and offers a 1-tap retry.
 */
export function IslandErrorBoundary({
    name = 'Island',
    fallbackTitle = 'Something went wrong while loading this section.',
    compact = false,
    onError,
    children,
}) {
    const [error, resetError] = useErrorBoundary((err, errorInfo) => {
        if (typeof console !== 'undefined' && typeof console.error === 'function') {
            console.error(`[PreactErrorBoundary:${name}]`, err, errorInfo);
        }
        if (typeof onError === 'function') {
            try {
                onError(err, errorInfo);
            } catch {
                // Isolate telemetry callback failures
            }
        }
    });

    if (error) {
        return (
            <div
                role="alert"
                data-testid={`error-boundary-${String(name).toLowerCase().replace(/[^a-z0-9_-]/g, '-')}`}
                className={compact ? 'p-3 my-2 rounded-3 border' : 'p-4 my-3 rounded-4 border shadow-sm'}
                style={{
                    backgroundColor: 'var(--card-bg, #ffffff)',
                    borderColor: 'rgba(163, 22, 33, 0.28)',
                    color: 'var(--text-main, #0f172a)',
                }}
            >
                <div className="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div className="d-flex align-items-center gap-2">
                        <i
                            className="bi bi-exclamation-triangle-fill"
                            style={{ color: '#a31621', fontSize: compact ? '1.1rem' : '1.35rem' }}
                            aria-hidden="true"
                        />
                        <div>
                            <div className="fw-bold" style={{ fontSize: compact ? '0.9rem' : '1rem' }}>
                                {fallbackTitle}
                            </div>
                            {!compact && (
                                <div className="small text-muted mt-1">
                                    We have isolated this issue so the rest of your family workspace stays active.
                                </div>
                            )}
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={resetError}
                        className="btn btn-sm rounded-pill px-3 fw-semibold"
                        style={{
                            minHeight: '44px',
                            minWidth: '44px',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                            backgroundColor: '#a31621',
                            color: '#ffffff',
                            border: 'none',
                        }}
                    >
                        Try Again
                    </button>
                </div>
            </div>
        );
    }

    return children;
}
