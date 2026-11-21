import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { KinRequestsIsland } from './KinRequestsIsland';
import { UpcomingEventsIsland } from './UpcomingEventsIsland';

function formatTitleCase(str) {
    return String(str || '')
        .trim()
        .toLowerCase()
        .replace(/\b([a-z])/g, (_, c) => c.toUpperCase());
}

export function applyProfileUpdateToDom(detail = {}) {
    if (typeof document === 'undefined' || !detail) return;
    const fullName = formatTitleCase(`${detail.firstName || ''} ${detail.lastName || ''}`);

    if (fullName) {
        const nameEl = document.getElementById('leftColProfileName');
        if (nameEl) {
            const textNode = nameEl.querySelector('.js-profile-name-text');
            if (textNode) {
                textNode.textContent = fullName;
            }
        }
        document.querySelectorAll('.js-profile-name').forEach((el) => {
            el.textContent = fullName;
        });
    }

    if (detail.country !== undefined) {
        const countryEl = document.getElementById('leftColProfileCountry');
        if (countryEl) {
            countryEl.textContent = String(detail.country || '').trim() || 'Global';
        }
    }

    if (detail.avatar) {
        document.querySelectorAll('#leftColProfileAvatar, .profileImg, .composer-avatar-img').forEach((img) => {
            if (img && img.tagName === 'IMG') {
                img.src = detail.avatar;
            }
        });
    }
}

export function mountProfileSidebars() {
    if (typeof document === 'undefined') return;

    const kinRoot = document.getElementById('kin-requests-root');
    if (kinRoot) {
        const kinBootstrap = readIslandBootstrap('kin-requests-bootstrap', {});
        render(<KinRequestsIsland bootstrap={kinBootstrap} />, kinRoot);
    }

    const eventsRoot = document.getElementById('upcoming-events-root');
    if (eventsRoot) {
        const eventsBootstrap = readIslandBootstrap('upcoming-events-bootstrap', {});
        render(<UpcomingEventsIsland bootstrap={eventsBootstrap} />, eventsRoot);
    }

    if (!window.__fpProfileUpdatedListenerAttached) {
        window.__fpProfileUpdatedListenerAttached = true;
        window.addEventListener('fp:profile-updated', (e) => {
            applyProfileUpdateToDom(e?.detail || {});
        });
    }
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountProfileSidebars);
    } else {
        mountProfileSidebars();
    }
}
