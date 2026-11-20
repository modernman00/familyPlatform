import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { OrganogramShell } from './OrganogramShell';

export function mountOrganogram() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('organogram-root');
    if (!root) return;
    const bootstrap = readIslandBootstrap('organogram-bootstrap', {});
    render(<OrganogramShell bootstrap={bootstrap} />, root);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountOrganogram);
    } else {
        mountOrganogram();
    }
}
