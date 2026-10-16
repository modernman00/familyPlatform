<div class="modal fade" id="lineageQuestModal" tabindex="-1" aria-labelledby="lineageQuestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
            @include('csrf')
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-bottom: 1px solid rgba(16, 185, 129, 0.1) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success shadow-sm" style="width: 48px; height: 48px; background: #d1fae5; font-size: 1.4rem;">
                        <i class="bi bi-compass-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="lineageQuestModalLabel">Lineage Quests</h5>
                            <span class="badge bg-success" style="font-size: 0.75rem; letter-spacing: 0.5px;">WHATSAPP FLYWHEEL</span>
                        </div>
                        <p class="text-muted small mb-0">Ask relatives and elders on WhatsApp to fill in missing roots and claim their profiles.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <!-- Completeness Metric Card -->
                <div class="card border-0 mb-4 shadow-sm" style="border-radius: 18px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 0.8rem; font-weight: 600;">
                                    <i class="bi bi-diagram-3-fill me-1"></i> TREE HEALTH
                                </span>
                                <span class="text-light small text-nowrap" id="questStatsNodesText">Scanning tree...</span>
                            </div>
                            <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: #e2e8f0; font-size: 0.78rem;">
                                <span id="questCompletenessScoreBadge">--</span>% Complete
                            </span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <h3 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px;">
                                <span id="questCompletenessScore">--</span>% <span class="fs-6 fw-normal text-light" style="opacity: 0.9;">Lineage Completeness</span>
                            </h3>
                        </div>
                        <div class="progress mb-2" style="height: 10px; background-color: rgba(255, 255, 255, 0.12); border-radius: 999px;">
                            <div id="questCompletenessProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-secondary small mb-0" style="color: #94a3b8 !important; font-size: 0.8rem;">
                            Every relative who claims a profile or adds an ancestor pushes our dynasty closer to 100%.
                        </p>
                    </div>
                </div>

                <!-- Quests Container (Dynamically Populated) -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Active WhatsApp Quests
                    </h6>
                    <span class="text-muted small" id="questCountBadge">Loading quests...</span>
                </div>

                <div id="questsLoadingState" class="text-center py-5">
                    <div class="spinner-border text-success" role="status" style="width: 2.2rem; height: 2.2rem;">
                        <span class="visually-hidden">Loading quests...</span>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Analyzing lineage gaps & generating signed WhatsApp links...</p>
                </div>

                <div id="questsListContainer" class="d-flex flex-column gap-3 d-none">
                    <!-- Dynamic Quest Cards rendered here -->
                </div>

                <!-- Toast / Copied Alert -->
                <div id="questCopiedAlert" class="alert alert-success d-none py-2 px-3 mt-3 text-center small rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> Invite link copied to clipboard! Share it in your family chat.
                </div>
            </div>

            <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                <span class="text-muted small">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> Zero-PII 256-bit signed invite links
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
    let questsLoaded = false;

    window.openLineageQuestModal = function() {
        const modalEl = document.getElementById('lineageQuestModal');
        if (!modalEl) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();

        if (!questsLoaded) {
            loadLineageQuests();
        }
    };

    function loadLineageQuests() {
        const loadingEl = document.getElementById('questsLoadingState');
        const listEl = document.getElementById('questsListContainer');
        const scoreEl = document.getElementById('questCompletenessScore');
        const barEl = document.getElementById('questCompletenessProgressBar');
        const statsEl = document.getElementById('questStatsNodesText');
        const countBadge = document.getElementById('questCountBadge');

        fetch('/api/lineage/quests')
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                if (res.status !== 'success' || !res.data) {
                    throw new Error(res.message || 'Invalid quest response');
                }
                const data = res.data;
                const pct = data.completeness_percentage || 20;

                if (scoreEl) scoreEl.textContent = pct;
                const badgeScore = document.getElementById('questCompletenessScoreBadge');
                if (badgeScore) badgeScore.textContent = pct;
                if (barEl) {
                    barEl.style.width = pct + '%';
                    barEl.setAttribute('aria-valuenow', pct);
                }
                if (statsEl) {
                    statsEl.textContent = `${data.total_nodes} Connected (${data.registered_nodes} Active Users)`;
                }

                renderQuestCards(data.quests || []);
                questsLoaded = true;

                if (loadingEl) loadingEl.classList.add('d-none');
                if (listEl) listEl.classList.remove('d-none');
                if (countBadge) countBadge.textContent = `${(data.quests || []).length} available`;
            })
            .catch(err => {
                console.error('[LineageQuests] Error loading quests:', err);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-danger small py-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-1"></i>
                            Failed to load quests. Please try again in a moment.
                        </div>
                    `;
                }
            });
    }

    function renderQuestCards(quests) {
        const container = document.getElementById('questsListContainer');
        if (!container) return;
        container.innerHTML = '';

        if (quests.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-check2-all fs-2 text-success d-block mb-1"></i>
                    All major family branches and relatives are connected!
                </div>
            `;
            return;
        }

        quests.forEach(quest => {
            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm p-3 mb-2';
            card.style.borderRadius = '16px';
            card.style.background = '#f8fafc';
            card.style.borderLeft = '5px solid #10b981 !important';

            const badgeBg = quest.badge_color === 'warning' ? 'bg-warning text-dark' : (quest.badge_color === 'info' ? 'bg-info text-dark' : 'bg-success text-white');

            card.innerHTML = `
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0 shadow-sm" style="width: 44px; height: 44px; background: #e0f2fe;">
                        <i class="bi ${quest.icon || 'bi-compass-fill'} fs-5 text-primary"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.35;">${escapeHtml(quest.title)}</h6>
                            <span class="badge ${badgeBg}" style="font-size: 0.72rem; padding: 4px 8px;">${escapeHtml(quest.badge)}</span>
                        </div>
                        <p class="text-muted small mb-0" style="font-size: 0.84rem; line-height: 1.45;">${escapeHtml(quest.subtitle)}</p>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top" style="border-color: rgba(0, 0, 0, 0.06) !important;">
                    <a href="${escapeHtml(quest.whatsapp_url)}" target="_blank" rel="noopener noreferrer" class="btn btn-sm px-3 py-2 fw-bold text-white shadow-sm flex-fill d-flex align-items-center justify-content-center" style="background: #25D366; border-radius: 10px; font-size: 0.85rem; min-height: 38px;">
                        <i class="bi bi-whatsapp me-2 fs-6"></i> Ask on WhatsApp
                    </a>
                    <button type="button" class="btn btn-light btn-sm px-3 py-2 border text-secondary fw-semibold flex-fill d-flex align-items-center justify-content-center" style="border-radius: 10px; font-size: 0.85rem; min-height: 38px;" onclick="copyQuestUrl('${escapeHtml(quest.invite_url)}')">
                        <i class="bi bi-clipboard me-2"></i> Copy Link
                    </button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    window.copyQuestUrl = function(url) {
        if (!url) return;
        navigator.clipboard.writeText(url).then(() => {
            const alertEl = document.getElementById('questCopiedAlert');
            if (alertEl) {
                alertEl.classList.remove('d-none');
                setTimeout(() => {
                    alertEl.classList.add('d-none');
                }, 3500);
            }
        }).catch(err => {
            prompt('Copy this invite link:', url);
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
