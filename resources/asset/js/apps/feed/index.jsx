import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { FeedShell } from './FeedShell';

export function mountFeed() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('feed-root');
    if (!root || root.dataset.fpFeedMounted === '1') return;
    root.dataset.fpFeedMounted = '1';
    const bootstrap = readIslandBootstrap('feed-bootstrap', {});
    render(
        <IslandErrorBoundary name="FamilyFeed" fallbackTitle="Unable to load your family feed right now.">
            <FeedShell bootstrap={bootstrap} />
        </IslandErrorBoundary>,
        root
    );
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFeed);
    } else {
        mountFeed();
    }
}
