import axios from 'axios';
import Swal from 'sweetalert2';

let modalInitialized = false;

export function initKinshipRadar() {
    // The widget markup is included twice (a mobile copy in the feed column and a
    // desktop copy in the right sidebar, toggled via responsive classes) so both
    // exist in the DOM at once. Wire up every copy rather than just the first
    // match.
    const containers = document.querySelectorAll('.kinship-radar-widget');
    containers.forEach(bindKinshipRadarContainer);

    initKinshipDetailsModal();
}

function getCsrfToken() {
    return (
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
        document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') ||
        (document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1] ||
        ''
    );
}

export function removeKinCardAcrossWidgets(targetUserId) {
    if (!targetUserId) return;
    const cards = document.querySelectorAll(`.kinship-radar-widget #kinCard_${targetUserId}, .kinship-radar-widget [data-user-id="${targetUserId}"]`);
    const processedCards = new Set();
    cards.forEach(el => {
        const card = el.closest('.kinship-item-card');
        if (card && !processedCards.has(card)) {
            processedCards.add(card);
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
                const widget = card.closest('.kinship-radar-widget');
                card.remove();
                if (widget && widget.querySelectorAll('.kinship-item-card').length === 0) {
                    widget.style.display = 'none';
                    const wrapper = widget.parentElement;
                    if (wrapper && (wrapper.classList.contains('d-lg-none') || wrapper.classList.contains('d-none'))) {
                        wrapper.style.display = 'none';
                    }
                }
            }, 250);
        }
    });
}

async function sendKinshipRequest(targetUserId, connectBtn) {
    if (!targetUserId || !connectBtn) return false;

    connectBtn.disabled = true;
    const originalHtml = connectBtn.innerHTML;
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

            // Once connected, fade out and remove card across widgets
            setTimeout(() => {
                removeKinCardAcrossWidgets(targetUserId);
            }, 1000);

            return true;
        }
    } catch (error) {
        console.error('[KinshipRadar] Connect error:', error);
        connectBtn.disabled = false;
        connectBtn.innerHTML = originalHtml;
        (window.swalError || alert)('Could not send kinship request. Please try again.');
    }
    return false;
}

function openKinshipDetailsModal(kin) {
    if (!kin || !kin.id) return;

    const modalEl = document.getElementById('kinshipDetailsModal');
    if (!modalEl) {
        // Fallback: navigate directly to organogram
        window.location.href = `/organogram/${kin.id}`;
        return;
    }

    // Populate Avatar
    const avatarEl = document.getElementById('kinModalAvatar');
    if (avatarEl) {
        avatarEl.src = kin.avatar || '/resources/images/profile/avatarM.png';
        avatarEl.alt = kin.name || 'Kin';
    }

    // Populate Name
    const nameEl = document.getElementById('kinModalName');
    if (nameEl) {
        nameEl.textContent = kin.name || 'Family Relative';
    }

    // Populate Score & Progress
    const scoreVal = parseInt(kin.score, 10) || 75;
    const scoreEl = document.getElementById('kinModalScore');
    if (scoreEl) {
        scoreEl.textContent = `${scoreVal}% Match`;
    }

    const typeEl = document.getElementById('kinModalType');
    if (typeEl) {
        typeEl.textContent = kin.type || 'Potential Kin';
    }

    const percentLabel = document.getElementById('kinModalPercentLabel');
    if (percentLabel) {
        percentLabel.textContent = `${scoreVal}%`;
    }

    const progressBar = document.getElementById('kinModalProgressBar');
    if (progressBar) {
        progressBar.style.width = `${scoreVal}%`;
        progressBar.setAttribute('aria-valuenow', String(scoreVal));
    }

    // Populate Lineage & Match Reasons
    const reasonsListEl = document.getElementById('kinModalReasonsList');
    if (reasonsListEl) {
        reasonsListEl.innerHTML = '';
        const reasons = (Array.isArray(kin.reasons) && kin.reasons.length > 0) 
            ? kin.reasons 
            : [kin.primaryReason || 'Shared family heritage network'];

        reasons.forEach(reason => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-start gap-2.5 py-1';
            li.innerHTML = `
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 mt-0.5" style="width: 24px; height: 24px; background: rgba(163, 22, 33, 0.1); font-size: 0.75rem;">
                    <i class="bi bi-diagram-2-fill"></i>
                </div>
                <span class="kin-reason-text" style="font-size: 0.875rem; line-height: 1.45; color: var(--text-dark, #1e293b);">
                    ${escapeHtml(reason)}
                </span>
            `;
            reasonsListEl.appendChild(li);
        });
    }

    // Family Branch Details
    const branchBox = document.getElementById('kinModalBranchBox');
    const famCodeEl = document.getElementById('kinModalFamCode');
    if (branchBox && famCodeEl) {
        if (kin.famCode) {
            famCodeEl.textContent = kin.famCode;
            branchBox.classList.remove('d-none');
        } else {
            branchBox.classList.add('d-none');
        }
    }

    // Family Tree Link Button
    const treeBtn = document.getElementById('kinModalTreeBtn');
    if (treeBtn) {
        treeBtn.href = `/organogram/${kin.id}`;
    }

    // Connect Button
    const connectBtn = document.getElementById('kinModalConnectBtn');
    const connectLabel = document.getElementById('kinModalConnectLabel');
    if (connectBtn) {
        connectBtn.setAttribute('data-user-id', kin.id);
        connectBtn.disabled = false;
        connectBtn.classList.remove('btn-success');
        connectBtn.classList.add('btn-primary');
        if (connectLabel) {
            connectLabel.textContent = `Connect with ${kin.name || 'Kin'}`;
        }
    }

    if (window.bootstrap && window.bootstrap.Modal) {
        const bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();
    }
}

function initKinshipDetailsModal() {
    if (modalInitialized) return;
    const modalEl = document.getElementById('kinshipDetailsModal');
    if (!modalEl) return;

    modalInitialized = true;

    const modalConnectBtn = document.getElementById('kinModalConnectBtn');
    if (modalConnectBtn) {
        modalConnectBtn.addEventListener('click', async () => {
            const targetUserId = modalConnectBtn.getAttribute('data-user-id');
            if (!targetUserId) return;

            const success = await sendKinshipRequest(targetUserId, modalConnectBtn);
            if (success) {
                setTimeout(() => {
                    if (window.bootstrap && window.bootstrap.Modal) {
                        const bsModal = window.bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                }, 1200);
            }
        });
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function bindKinshipRadarContainer(container) {
    container.addEventListener('click', async (e) => {
        // 1. Handle Connect Button (Stop propagation to prevent opening modal)
        const connectBtn = e.target.closest('.btn-connect-kin');
        if (connectBtn) {
            e.stopPropagation();
            const targetUserId = connectBtn.getAttribute('data-user-id');
            if (targetUserId) {
                await sendKinshipRequest(targetUserId, connectBtn);
            }
            return;
        }

        // 2. Handle Dismiss Button (Stop propagation to prevent opening modal)
        const dismissBtn = e.target.closest('.btn-dismiss-kin');
        if (dismissBtn) {
            e.stopPropagation();
            const targetUserId = dismissBtn.getAttribute('data-user-id');
            if (!targetUserId) return;

            removeKinCardAcrossWidgets(targetUserId);

            try {
                await axios.post('/api/kinship/dismiss', {
                    dismissed_user_id: targetUserId
                });
            } catch (error) {
                console.warn('[KinshipRadar] Dismiss sync error:', error);
            }
            return;
        }

        // 3. Handle Card Click -> Open Kinship Details Modal
        const card = e.target.closest('.kinship-item-card');
        if (card) {
            const rawData = card.getAttribute('data-kin');
            if (rawData) {
                try {
                    const kin = JSON.parse(rawData);
                    openKinshipDetailsModal(kin);
                } catch (err) {
                    console.error('[KinshipRadar] Failed to parse kin payload:', err);
                }
            }
        }
    });

    // Keyboard support: Allow Enter or Space on a focused card to open details modal
    container.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            const card = e.target.closest('.kinship-item-card');
            if (card && !e.target.closest('button, a')) {
                e.preventDefault();
                const rawData = card.getAttribute('data-kin');
                if (rawData) {
                    try {
                        const kin = JSON.parse(rawData);
                        openKinshipDetailsModal(kin);
                    } catch (err) {
                        console.error('[KinshipRadar] Keydown error:', err);
                    }
                }
            }
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initKinshipRadar);
} else {
    initKinshipRadar();
}
