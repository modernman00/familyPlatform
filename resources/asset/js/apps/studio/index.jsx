import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { StudioShell } from './StudioShell';

export function mountCreativeStudio() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('creative-studio-root');
    if (!root) return;
    const bootstrap = readIslandBootstrap('studio-bootstrap', {});
    render(
        <IslandErrorBoundary name="CreativeStudio" fallbackTitle="Unable to load Creative Studio right now.">
            <StudioShell bootstrap={bootstrap} />
        </IslandErrorBoundary>,
        root
    );
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountCreativeStudio);
    } else {
        mountCreativeStudio();
    }
}
