<style>
#timeCapsuleModal .tc-header-icon {
    width: 48px;
    height: 48px;
    background: #f0abfc;
    color: #0f172a;
    font-size: 1.4rem;
}
#timeCapsuleModal .tc-tab-btn {
    min-height: 44px;
    background: #f8fafc;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    font-weight: 600;
}
#timeCapsuleModal .tc-tab-btn.is-active {
    background: #1e293b;
    color: #ffffff;
    border-color: #1e293b;
    font-weight: 700;
}
#timeCapsuleModal .tc-empty-icon {
    background: #fdf4ff;
    color: #a31621;
}
#timeCapsuleModal .tc-capsule-card {
    border-radius: 16px;
}
#timeCapsuleModal .tc-capsule-card--unlocked {
    background: #f0fdf4;
    border-left: 5px solid #10b981 !important;
}
#timeCapsuleModal .tc-capsule-card--sealed {
    background: #fbefef;
    border-left: 5px solid #ef6b73 !important;
}

body.dark-mode #timeCapsuleModal .tc-header-icon,
body.dark-theme #timeCapsuleModal .tc-header-icon,
[data-bs-theme="dark"] #timeCapsuleModal .tc-header-icon,
html.dark #timeCapsuleModal .tc-header-icon,
html.dark-mode #timeCapsuleModal .tc-header-icon {
    background: rgba(217, 70, 239, 0.22) !important;
    color: #f0abfc !important;
}
body.dark-mode #timeCapsuleModal .tc-tab-btn:not(.is-active),
body.dark-theme #timeCapsuleModal .tc-tab-btn:not(.is-active),
[data-bs-theme="dark"] #timeCapsuleModal .tc-tab-btn:not(.is-active),
html.dark #timeCapsuleModal .tc-tab-btn:not(.is-active),
html.dark-mode #timeCapsuleModal .tc-tab-btn:not(.is-active) {
    background: #1e293b !important;
    color: #cbd5e1 !important;
    border-color: rgba(255, 255, 255, 0.14) !important;
}
body.dark-mode #timeCapsuleModal .tc-tab-btn.is-active,
body.dark-theme #timeCapsuleModal .tc-tab-btn.is-active,
[data-bs-theme="dark"] #timeCapsuleModal .tc-tab-btn.is-active,
html.dark #timeCapsuleModal .tc-tab-btn.is-active,
html.dark-mode #timeCapsuleModal .tc-tab-btn.is-active {
    background: linear-gradient(135deg, #d91b38 0%, #b4142c 100%) !important;
    color: #ffffff !important;
    border-color: transparent !important;
}
body.dark-mode #timeCapsuleModal .tc-empty-icon,
body.dark-theme #timeCapsuleModal .tc-empty-icon,
[data-bs-theme="dark"] #timeCapsuleModal .tc-empty-icon,
html.dark #timeCapsuleModal .tc-empty-icon,
html.dark-mode #timeCapsuleModal .tc-empty-icon {
    background: rgba(217, 70, 239, 0.16) !important;
    color: #f0abfc !important;
}
body.dark-mode #timeCapsuleModal .tc-capsule-card--unlocked,
body.dark-theme #timeCapsuleModal .tc-capsule-card--unlocked,
[data-bs-theme="dark"] #timeCapsuleModal .tc-capsule-card--unlocked,
html.dark #timeCapsuleModal .tc-capsule-card--unlocked,
html.dark-mode #timeCapsuleModal .tc-capsule-card--unlocked {
    background: rgba(16, 185, 129, 0.12) !important;
    border: 1px solid rgba(16, 185, 129, 0.28) !important;
    border-left: 5px solid #10b981 !important;
    color: #f8fafc !important;
}
body.dark-mode #timeCapsuleModal .tc-capsule-card--sealed,
body.dark-theme #timeCapsuleModal .tc-capsule-card--sealed,
[data-bs-theme="dark"] #timeCapsuleModal .tc-capsule-card--sealed,
html.dark #timeCapsuleModal .tc-capsule-card--sealed,
html.dark-mode #timeCapsuleModal .tc-capsule-card--sealed {
    background: rgba(255, 77, 97, 0.12) !important;
    border: 1px solid rgba(255, 77, 97, 0.28) !important;
    border-left: 5px solid #ff4d61 !important;
    color: #f8fafc !important;
}
</style>

<div class="modal fade" id="timeCapsuleModal" tabindex="-1" aria-labelledby="timeCapsuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; max-height: calc(100vh - 3.5rem);">
            @include('csrf')
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2 flex-shrink-0" style="background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border-bottom: 1px solid rgba(217, 70, 239, 0.15) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm tc-header-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="timeCapsuleModalLabel">Digital Time Capsule Vault</h5>
                            <span class="badge bg-dark" style="font-size: 0.75rem; letter-spacing: 0.5px;">LIVING HERITAGE</span>
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;"><i class="bi bi-shield-lock-fill me-1"></i>AES-256 VAULT</span>
                        </div>
                        <p class="text-muted small mb-0">Seal letters, voice heirlooms, and life advice for children and relatives until a future milestone.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Vault Mode Nav (Pills) -->
            <div class="px-4 pt-3 pb-2 border-bottom d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn is-active" id="tabBtnViewVault" onclick="switchCapsuleTab('view')">
                    <i class="bi bi-safe2 me-1"></i> Family Vault
                </button>
                <button type="button" class="btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn" id="tabBtnCreateVault" onclick="switchCapsuleTab('create')">
                    <i class="bi bi-plus-circle me-1"></i> Seal New Capsule
                </button>
            </div>

            <div class="modal-body px-4 py-3" style="overflow-y: auto;">
                <!-- VIEW TAB -->
                <div id="capsuleViewTab">
                    <div id="capsulesLoadingState" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                            <span class="visually-hidden">Loading vault...</span>
                        </div>
                        <p class="text-muted small mt-2 mb-0">Inspecting time-locked cryptographic vaults...</p>
                    </div>

                    <div id="capsulesListContainer" class="d-flex flex-column gap-3 d-none">
                        <!-- Dynamic Capsule Cards -->
                    </div>
                </div>

                <!-- CREATE TAB -->
                <div id="capsuleCreateTab" class="d-none">
                    <form id="createCapsuleForm" onsubmit="handleCapsuleSubmit(event)">
                        @include('csrf')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">Recipient Name (Child / Relative)</label>
                                <input type="text" id="capsuleRecipient" name="recipient_name" class="form-control rounded-3" placeholder="e.g. Seyi Olaogun (Grandson)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">Unlock Milestone / Date</label>
                                <input type="date" id="capsuleUnlockDate" name="unlock_date" class="form-control rounded-3" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark mb-1">Capsule Title</label>
                                <input type="text" id="capsuleTitle" name="title" class="form-control rounded-3" placeholder="e.g. Advice on your 18th Birthday &amp; Our Family Roots" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark mb-1">Personal Letter / Legacy Message</label>
                                <textarea id="capsuleMessage" name="message" class="form-control rounded-3" rows="5" placeholder="Write your words of wisdom, ancestral stories, or prayers. This will remain cryptographically sealed until the unlock date." required></textarea>
                            </div>

                            <div class="col-12">
                                <div class="modal-sub-card-rose p-3 rounded-3" style="border: 1px dashed rgba(217, 70, 239, 0.4);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-shield-lock-fill text-primary"></i>
                                        <span class="fw-bold small text-dark">Zero-Trust Cryptographic Sealing</span>
                                    </div>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">
                                        Once sealed, server-side redaction ensures this letter cannot be read by anyone (including the recipient) until the clock strikes midnight on the unlock date.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Encryption Reassurance Banner -->
                        <div class="p-2.5 rounded-3 d-flex align-items-center gap-2 mt-3" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">
                            <i class="bi bi-shield-lock-fill text-success flex-shrink-0" style="font-size: 1.1rem;"></i>
                            <span class="small text-muted" style="font-size: 0.78rem;">
                                <strong class="text-dark">Family Vault Encrypted:</strong> This message is encrypted at rest using AES-256-GCM. It cannot be unlocked or read until the specified milestone arrives.
                            </span>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light btn-sm px-3 py-2 fw-semibold border" onclick="switchCapsuleTab('view')">
                                Cancel
                            </button>
                            <button type="submit" id="btnSubmitCapsule" class="btn btn-primary btn-sm px-4 py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #ef6b73 0%, #8a1019 100%); border: none;">
                                <i class="bi bi-lock-fill me-1"></i> Seal Capsule in Vault
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer border-0 px-4 py-3 d-flex justify-content-between flex-shrink-0">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Multi-generational preservation
                </span>
                <button type="button" class="btn btn-secondary btn-sm px-3 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    let capsulesLoaded = false;

    window.openTimeCapsuleModal = function() {
        const modalEl = document.getElementById('timeCapsuleModal');
        if (!modalEl) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();

        switchCapsuleTab('view');
        if (!capsulesLoaded) {
            loadCapsules();
        }
    };

    window.switchCapsuleTab = function(tab) {
        const viewTab = document.getElementById('capsuleViewTab');
        const createTab = document.getElementById('capsuleCreateTab');
        const btnView = document.getElementById('tabBtnViewVault');
        const btnCreate = document.getElementById('tabBtnCreateVault');

        if (tab === 'create') {
            if (viewTab) viewTab.classList.add('d-none');
            if (createTab) createTab.classList.remove('d-none');
            if (btnView) {
                btnView.style.background = '';
                btnView.className = 'btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn';
            }
            if (btnCreate) {
                btnCreate.style.background = '';
                btnCreate.className = 'btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn is-active';
            }
        } else {
            if (createTab) createTab.classList.add('d-none');
            if (viewTab) viewTab.classList.remove('d-none');
            if (btnCreate) {
                btnCreate.style.background = '';
                btnCreate.className = 'btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn';
            }
            if (btnView) {
                btnView.style.background = '';
                btnView.className = 'btn btn-sm px-3 py-1.5 rounded-pill tc-tab-btn is-active';
            }
        }
    };

    function loadCapsules() {
        const loadingEl = document.getElementById('capsulesLoadingState');
        const listEl = document.getElementById('capsulesListContainer');

        fetch('/api/capsules')
            .then(res => res.json())
            .then(res => {
                if (res.status !== 'success') throw new Error(res.message || 'Error');
                renderCapsuleCards(res.data || []);
                capsulesLoaded = true;
                if (loadingEl) loadingEl.classList.add('d-none');
                if (listEl) listEl.classList.remove('d-none');
            })
            .catch(err => {
                console.error('[TimeCapsule] Error loading capsules:', err);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-danger small py-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-1"></i>
                            Failed to load vault. Please try again in a moment.
                        </div>
                    `;
                }
            });
    }

    function renderCapsuleCards(capsules) {
        const container = document.getElementById('capsulesListContainer');
        if (!container) return;
        container.innerHTML = '';

        if (capsules.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2 tc-empty-icon">
                        <i class="bi bi-safe2 fs-2"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-dark">The Family Vault is Empty</h6>
                    <p class="text-muted small mb-3">Be the first to seal a message or prayer for future generations.</p>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="switchCapsuleTab('create')">
                        <i class="bi bi-lock-fill me-1"></i> Seal First Capsule
                    </button>
                </div>
            `;
            return;
        }

        capsules.forEach(capsule => {
            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm p-3 tc-capsule-card ' + (capsule.is_unlocked ? 'tc-capsule-card--unlocked' : 'tc-capsule-card--sealed');

            const badgeText = capsule.is_unlocked ? 'UNLOCKED' : `SEALED (${capsule.days_remaining}d remaining)`;
            const badgeClass = capsule.is_unlocked ? 'bg-success text-white' : 'bg-dark text-white';

            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi ${capsule.is_unlocked ? 'bi-envelope-open-heart-fill text-success' : 'bi-lock-fill text-primary'} fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">${escapeHtml(capsule.title)}</h6>
                    </div>
                    <span class="badge ${badgeClass}" style="font-size: 0.72rem;">${badgeText}</span>
                </div>
                <div class="text-muted small mb-2" style="font-size: 0.8rem;">
                    For: <strong>${escapeHtml(capsule.recipient_name)}</strong> • Unlock Date: <strong>${escapeHtml(capsule.unlock_date)}</strong>
                </div>
                <div class="p-2.5 rounded-3 capsule-message-box ${capsule.is_unlocked ? 'border' : 'font-monospace'}" style="font-size: 0.85rem; line-height: 1.5; background-color: var(--card-bg, #ffffff); color: var(--text-color, #0f172a);">
                    ${escapeHtml(capsule.message)}
                </div>
            `;
            container.appendChild(card);
        });
    }

    window.handleCapsuleSubmit = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitCapsule');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sealing...';
        }

        const form = e.target;
        const payload = {
            recipient_name: form.recipient_name.value,
            unlock_date: form.unlock_date.value,
            title: form.title.value,
            message: form.message.value,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch('/api/capsules/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-XSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        })
        .then(res => res.json())
        .then(res => {
            if (res.status !== 'success') {
                (window.swalError || alert)(res.message || 'Failed to seal capsule.');
                return;
            }
            form.reset();
            capsulesLoaded = false;
            loadCapsules();
            switchCapsuleTab('view');
            if (window.swalSuccess) {
                window.swalSuccess('Time capsule safely sealed in vault! ⏳');
            }
        })
        .catch(err => {
            (window.swalError || alert)('A network error occurred.');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Seal Capsule in Vault';
            }
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>
