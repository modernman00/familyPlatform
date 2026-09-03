import axios from 'axios';
import Swal from 'sweetalert2';

export function initKinshipRadar() {
    // The widget markup is included twice (a mobile copy in the feed column and a
    // desktop copy in the right sidebar, toggled via responsive classes) so both
    // exist in the DOM at once. Wire up every copy rather than just the first
    // match - getElementById would silently bind only to whichever one happens to
    // render first, which is often the hidden one for the current viewport.
    const containers = document.querySelectorAll('.kinship-radar-widget');
    containers.forEach(bindKinshipRadarContainer);
}

function getCsrfToken() {
    return (
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') ||
        (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1] ||
        ''
    );
}

function bindKinshipRadarContainer(container) {
    // 1. Handle Connect Button
    container.addEventListener('click', async (e) => {
        const connectBtn = e.target.closest('.btn-connect-kin');
        if (connectBtn) {
            const targetUserId = connectBtn.getAttribute('data-user-id');
            if (!targetUserId) return;

            connectBtn.disabled = true;
            connectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Connecting...';

            try {
                const csrfToken = getCsrfToken();
                const res = await axios.post('/members/familyRequestMgt', {
                    approverId: targetUserId,
                    token: csrfToken
                }, {
                    headers: {
                        'X-XSRF-TOKEN': csrfToken,
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.data?.status === 'success' || res.status === 200) {
                    connectBtn.classList.remove('btn-primary');
                    connectBtn.classList.add('btn-success');
                    connectBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Request Sent';

                    // Track kinship connect event (fire-and-forget)
                    axios.post('/api/analytics/track', {
                        event_type: 'kinship_connect',
                        target_id: targetUserId
                    }).catch(() => {});

                    if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'success', title: 'Kinship request sent!' });
                    }
                }
            } catch (error) {
                console.error('[KinshipRadar] Connect error:', error);
                connectBtn.disabled = false;
                connectBtn.innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Connect';
                alert('Could not send kinship request. Please try again.');
            }
            return;
        }

        // 2. Handle Dismiss Button
        const dismissBtn = e.target.closest('.btn-dismiss-kin');
        if (dismissBtn) {
            const targetUserId = dismissBtn.getAttribute('data-user-id');
            if (!targetUserId) return;

            // Scoped to the button that was actually clicked rather than a
            // document-wide id lookup - the same kin's card id is duplicated
            // across the mobile/desktop widget copies, and getElementById would
            // only ever find (and remove) the first one.
            const card = dismissBtn.closest('.kinship-item-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 250);
            }

            try {
                await axios.post('/api/kinship/dismiss', {
                    dismissed_user_id: targetUserId
                });
            } catch (error) {
                console.warn('[KinshipRadar] Dismiss sync error:', error);
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKinshipRadar);
} else {
    initKinshipRadar();
}
