<!-- Family Heritage & Weekly Rituals Sidebar Widget -->
<style>
  body.dark-mode .family-heritage-sidebar-widget .tree-completeness-strip,
  [data-bs-theme="dark"] .family-heritage-sidebar-widget .tree-completeness-strip {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(30, 41, 59, 0.95) 100%) !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
  }
  body.dark-mode .family-heritage-sidebar-widget .tree-completeness-strip .strip-heading,
  [data-bs-theme="dark"] .family-heritage-sidebar-widget .tree-completeness-strip .strip-heading {
    color: #f8fafc !important;
  }
  body.dark-mode .family-heritage-sidebar-widget .btn-light,
  [data-bs-theme="dark"] .family-heritage-sidebar-widget .btn-light {
    background-color: #243044 !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
  }
</style>
<div class="card border-0 shadow-sm mb-4 family-heritage-sidebar-widget" style="border-radius: 20px; background-color: var(--card-bg);">
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">
                <i class="bi bi-stars text-warning me-1"></i> Family Heritage
            </h5>
            <p class="text-muted small mb-0" style="font-size: 0.8rem;">Rituals, quests &amp; generational vault.</p>
        </div>
        <span class="badge rounded-pill bg-success-subtle text-success px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">ACTIVE</span>
    </div>

    <div class="card-body px-4 pt-2 pb-4">
        <!-- Tree Health & Quests Strip -->
        <div class="p-3 rounded-3 mb-3 tree-completeness-strip" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid rgba(16, 185, 129, 0.2);">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="fw-bold text-dark strip-heading small" style="font-size: 0.84rem;">
                    <i class="bi bi-diagram-3-fill text-success me-1"></i> Tree Completeness
                </span>
                <span class="badge bg-success" style="font-size: 0.72rem;" id="sideWidgetScoreBadge">--%</span>
            </div>
            <div class="progress mb-2" style="height: 6px; background: rgba(16, 185, 129, 0.15); border-radius: 999px;">
                <div id="sideWidgetProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
            </div>
            <button type="button" onclick="openLineageQuestModal()" class="btn btn-sm btn-success w-100 py-1.5 fw-bold text-white shadow-xs d-flex align-items-center justify-content-center gap-1.5" style="border-radius: 10px; font-size: 0.8rem; background: #10b981; border: none;">
                <i class="bi bi-whatsapp"></i> Solve WhatsApp Quests
            </button>
        </div>

        <!-- 3 Quick Action Tiles -->
        <div class="d-flex flex-column gap-2">
            <button type="button" onclick="openComposerWithTrivia()" class="btn btn-sm btn-light border w-100 p-2 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px; background: #fef08a;">
                        <i class="bi bi-trophy-fill" style="font-size: 0.9rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.82rem;">Sunday Trivia</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Play weekly heritage challenge</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted small"></i>
            </button>

            <button type="button" onclick="openFamilyFlyerModal()" class="btn btn-sm btn-light border w-100 p-2 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px; background: #dbeafe;">
                        <i class="bi bi-card-image" style="font-size: 0.9rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.82rem;">WhatsApp Clan Flyer</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Share heraldic status card</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted small"></i>
            </button>

            <button type="button" onclick="openTimeCapsuleModal()" class="btn btn-sm btn-light border w-100 p-2 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f3e8ff; color: #a855f7;">
                        <i class="bi bi-hourglass-split" style="font-size: 0.9rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.82rem;">Time Capsule Vault</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Lock memories for milestones</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-muted small"></i>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    function loadSideWidgetHeritage() {
        fetch('/api/lineage/quests')
            .then(res => res.json())
            .then(res => {
                if (res && res.status === 'success' && res.data && res.data.stats) {
                    const score = res.data.stats.completeness_percentage || 0;
                    const badge = document.getElementById('sideWidgetScoreBadge');
                    const bar = document.getElementById('sideWidgetProgressBar');
                    if (badge) badge.textContent = score + '%';
                    if (bar) bar.style.width = score + '%';
                }
            })
            .catch(() => {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadSideWidgetHeritage);
    } else {
        loadSideWidgetHeritage();
    }
})();
</script>
