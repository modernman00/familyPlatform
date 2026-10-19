<div class="modal fade" id="timeCapsuleModal" tabindex="-1" aria-labelledby="timeCapsuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff; max-height: calc(100vh - 3.5rem);">
            @include('csrf')
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2 flex-shrink-0" style="background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border-bottom: 1px solid rgba(217, 70, 239, 0.15) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary shadow-sm" style="width: 48px; height: 48px; background: #f0abfc; font-size: 1.4rem;">
                        <i class="bi bi-hourglass-split text-dark"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="timeCapsuleModalLabel">Digital Time Capsule Vault</h5>
                            <span class="badge bg-dark" style="font-size: 0.75rem; letter-spacing: 0.5px;">LIVING HERITAGE</span>
                        </div>
                        <p class="text-muted small mb-0">Seal letters, voice heirlooms, and life advice for children and relatives until a future milestone.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Vault Mode Nav (Pills) -->
            <div class="px-4 pt-3 pb-2 border-bottom bg-light d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm px-3 py-1.5 fw-bold rounded-pill" id="tabBtnViewVault" style="background: #1e293b; color: #ffffff;" onclick="switchCapsuleTab('view')">
                    <i class="bi bi-safe2 me-1"></i> Family Vault
                </button>
                <button type="button" class="btn btn-sm btn-light border px-3 py-1.5 fw-semibold rounded-pill text-dark" id="tabBtnCreateVault" onclick="switchCapsuleTab('create')">
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
                                <div class="p-3 rounded-3" style="background: #faf5ff; border: 1px dashed #d8b4fe;">
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

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light btn-sm px-3 py-2 fw-semibold border" onclick="switchCapsuleTab('view')">
                                Cancel
                            </button>
                            <button type="submit" id="btnSubmitCapsule" class="btn btn-primary btn-sm px-4 py-2 fw-bold shadow-sm" style="background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%); border: none;">
                                <i class="bi bi-lock-fill me-1"></i> Seal Capsule in Vault
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between flex-shrink-0">
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

<script>
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
                btnView.className = 'btn btn-sm btn-light border px-3 py-1.5 fw-semibold rounded-pill text-dark';
            }
            if (btnCreate) {
                btnCreate.className = 'btn btn-sm px-3 py-1.5 fw-bold rounded-pill text-white';
                btnCreate.style.background = '#1e293b';
            }
        } else {
            if (createTab) createTab.classList.add('d-none');
            if (viewTab) viewTab.classList.remove('d-none');
            if (btnCreate) {
                btnCreate.style.background = '';
                btnCreate.className = 'btn btn-sm btn-light border px-3 py-1.5 fw-semibold rounded-pill text-dark';
            }
            if (btnView) {
                btnView.className = 'btn btn-sm px-3 py-1.5 fw-bold rounded-pill text-white';
                btnView.style.background = '#1e293b';
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
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-2" style="background: #fdf4ff; color: #a855f7;">
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
            card.className = 'card border-0 shadow-sm p-3';
            card.style.borderRadius = '16px';
            card.style.background = capsule.is_unlocked ? '#f0fdf4' : '#faf5ff';
            card.style.borderLeft = capsule.is_unlocked ? '5px solid #10b981 !important' : '5px solid #a855f7 !important';

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
                <div class="p-2.5 rounded-3 ${capsule.is_unlocked ? 'bg-white border text-dark' : 'bg-light text-secondary font-monospace'}" style="font-size: 0.85rem; line-height: 1.5;">
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
