import { createPortal } from 'preact/compat';

/**
 * Document-level Preact Portal primitive.
 * Escapes parent overflow/transform/z-index stacking contexts for modals and lightboxes
 * while falling back gracefully in SSR or headless test environments without document.body.
 */
export function Portal({ children, container = null }) {
    if (typeof document === 'undefined') {
        return children;
    }
    const target = container || document.body;
    if (!target) {
        return children;
    }
    return createPortal(children, target);
}
