import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { ArcadeShell } from './ArcadeShell';

export function mountFamilyArcade() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('family-arcade-root');
    if (!root) return;
    const bootstrap = readIslandBootstrap('arcade-bootstrap', {});
    render(<ArcadeShell bootstrap={bootstrap} />, root);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFamilyArcade);
    } else {
        mountFamilyArcade();
    }
}
