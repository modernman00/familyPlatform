import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { ArcadeShell } from './ArcadeShell';

export function mountFamilyArcade() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('family-arcade-root');
    if (!root) return;
    const bootstrap = readIslandBootstrap('arcade-bootstrap', {});
    render(
        <IslandErrorBoundary name="FamilyArcade" fallbackTitle="Unable to load Family Arcade right now.">
            <ArcadeShell bootstrap={bootstrap} />
        </IslandErrorBoundary>,
        root
    );
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFamilyArcade);
    } else {
        mountFamilyArcade();
    }
}
