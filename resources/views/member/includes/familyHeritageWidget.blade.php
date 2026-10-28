<!-- Family Rituals & Quests Sidebar Widget -->
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
<div class="card border-0 shadow-sm mb-4 family-heritage-sidebar-widget" id="familyHeritageSection" style="border-radius: 20px; background-color: var(--card-bg); scroll-margin-top: 80px;">
    <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
        <!-- Top Row: ACTIVE badge -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge rounded-pill bg-success-subtle text-success px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem; font-weight: 700; line-height: 1;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #059669; display: inline-block;"></span>
                ACTIVE
            </span>
        </div>

        <!-- Title Row: Stars Icon + Family Rituals & Quests & Subtitle (Un-squeezed) -->
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 d-flex align-items-center justify-content-center" 
                 style="width: 38px; height: 38px; border-radius: 12px; background: rgba(245, 158, 11, 0.14); color: #d97706; font-size: 1.15rem; flex-shrink: 0;">
                <i class="bi bi-stars"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
                <h5 class="mb-0 fw-bold text-truncate" style="color: var(--text-color); font-size: 1rem; line-height: 1.25; letter-spacing: -0.01em;" title="{{ __('heritage.family_heritage', 'Family Rituals & Quests') }}">
                    {{ __('heritage.family_heritage', 'Family Rituals & Quests') }}
                </h5>
                <p class="mb-0 text-truncate" style="font-size: 0.78rem; color: var(--text-muted-aa, #475569); line-height: 1.35; margin-top: 2px;" title="{{ __('heritage.rituals_subtitle', 'Rituals, quests & generational vault.') }}">
                    {{ __('heritage.rituals_subtitle', 'Rituals, quests & generational vault.') }}
                </p>
            </div>
        </div>
    </div>

    <div class="card-body px-4 pt-2 pb-4">
        <!-- Tree Health & Quests Strip -->
        <div class="p-3 rounded-3 mb-3 tree-completeness-strip" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid rgba(16, 185, 129, 0.2);">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="fw-bold text-dark strip-heading small" style="font-size: 0.875rem;">
                    <i class="bi bi-diagram-3-fill text-success me-1"></i> {{ __('heritage.tree_completeness', 'Tree Completeness') }}
                </span>
                <span class="badge bg-success" style="font-size: 0.75rem;" id="sideWidgetScoreBadge">--%</span>
            </div>
            <div class="progress mb-2" style="height: 6px; background: rgba(16, 185, 129, 0.15); border-radius: 999px;">
                <div id="sideWidgetProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
            </div>
            <button type="button" onclick="openLineageQuestModal()" class="btn btn-sm btn-success w-100 py-1.5 fw-bold text-white shadow-xs d-flex align-items-center justify-content-center gap-1.5" style="border-radius: 10px; font-size: 0.875rem; min-height: 44px; background: #059669; color: #ffffff !important; border: none;" title="Solve WhatsApp Quests - Fill missing tree branches with WhatsApp family quizzes">
                <i class="bi bi-whatsapp"></i> {{ __('heritage.solve_quests', 'Solve WhatsApp Quests') }}
            </button>
        </div>

        <!-- 3 Quick Action Tiles -->
        <div class="d-flex flex-column gap-2">
            <button type="button" onclick="openComposerWithTrivia()" class="btn heritage-action-tile w-100 p-2.5 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="min-height: 54px;" title="Sunday Trivia - Play weekly heritage challenge with relatives">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(245, 158, 11, 0.16); color: #b45309;">
                        <i class="bi bi-trophy-fill" style="font-size: 0.95rem;"></i>
                    </div>
                    <div>
                        <div class="tile-title fw-bold" style="font-size: 0.92rem; color: #0f172a;">{{ __('heritage.sunday_trivia', 'Sunday Trivia') }}</div>
                        <div class="tile-subtitle" style="font-size: 0.82rem; color: #475569; line-height: 1.35;">{{ __('heritage.play_weekly_challenge', 'Play weekly heritage challenge') }}</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right" style="color: #64748b;"></i>
            </button>

            <button type="button" onclick="openFamilyFlyerModal()" class="btn heritage-action-tile w-100 p-2.5 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="min-height: 54px;" title="Clan Flyer - Generate and download family tree status flyer for WhatsApp">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(163, 22, 33, 0.12); color: #a31621;">
                        <i class="bi bi-card-image" style="font-size: 0.95rem;"></i>
                    </div>
                    <div>
                        <div class="tile-title fw-bold" style="font-size: 0.92rem; color: #0f172a;">{{ __('heritage.clan_flyer', 'Clan Flyer') }}</div>
                        <div class="tile-subtitle" style="font-size: 0.82rem; color: #475569; line-height: 1.35;">{{ __('heritage.whatsapp_status', 'WhatsApp Status') }}</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right" style="color: #64748b;"></i>
            </button>

            <button type="button" onclick="openTimeCapsuleModal()" class="btn heritage-action-tile w-100 p-2.5 d-flex align-items-center justify-content-between text-start shadow-xs transition-all" style="min-height: 54px;" title="Time Capsule - Seal photos, voice notes, and messages for future delivery">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(109, 40, 217, 0.14); color: #6d28d9;">
                        <i class="bi bi-hourglass-split" style="font-size: 0.95rem;"></i>
                    </div>
                    <div>
                        <div class="tile-title fw-bold" style="font-size: 0.92rem; color: #0f172a;">{{ __('heritage.time_capsule', 'Time Capsule') }}</div>
                        <div class="tile-subtitle" style="font-size: 0.82rem; color: #475569; line-height: 1.35;">{{ __('heritage.future_milestones', 'Future Milestones') }}</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right" style="color: #64748b;"></i>
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
