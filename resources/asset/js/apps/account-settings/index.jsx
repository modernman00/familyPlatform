import { render } from 'preact';
import { readIslandBootstrap } from '../shared/bootstrap';
import { IslandErrorBoundary } from '../shared/IslandErrorBoundary';
import { AccountSettingsShell } from './AccountSettingsShell';

export function mountAccountSettings() {
    if (typeof document === 'undefined') return;
    const root = document.getElementById('account-settings-root');
    if (!root) return;

    const bootstrap = readIslandBootstrap('account-settings-bootstrap', {});
    render(
        <IslandErrorBoundary
            name="AccountSettings"
            fallbackTitle="Unable to display Account Settings cleanly."
        >
            <AccountSettingsShell bootstrap={bootstrap} />
        </IslandErrorBoundary>,
        root
    );
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountAccountSettings, { once: true });
    } else {
        mountAccountSettings();
    }
}
