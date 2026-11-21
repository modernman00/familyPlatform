import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { FeedShell } from './FeedShell';

export function mountFeed() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('feed-root');
    if (!root) return;
    const bootstrap = readIslandBootstrap('feed-bootstrap', {});
    render(<FeedShell bootstrap={bootstrap} />, root);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFeed);
    } else {
        mountFeed();
    }
}
