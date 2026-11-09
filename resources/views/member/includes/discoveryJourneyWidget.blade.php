<!-- 60-Day Omnichannel Feature Discovery & Activation Hub Widget -->
<!-- Governed by TAT / Sovereign Sector Board: Social Media, Lifestyle & Platform Growth -->
<div class="card border-0 shadow-sm mb-4 overflow-hidden discovery-journey-card"
     id="discoveryJourneyWidget"
     style="border-radius: 20px; background: linear-gradient(135deg, #1e1114 0%, #2a0a0d 45%, #6a0d15 100%); color: #ffffff; border: 1.5px solid rgba(244, 63, 94, 0.35); position: relative;"
     x-data="discoveryJourneyWidget()"
     x-init="initDiscoveryState()"
     x-show="visible && inWindow"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4">

    <!-- Subtle Ambient Glow -->
    <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(244,63,94,0.2) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>

    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-start position-relative">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 46px; height: 46px; min-width: 46px; min-height: 46px; background: rgba(244, 63, 94, 0.2); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.45); box-shadow: 0 0 12px rgba(244, 63, 94, 0.25);">
                <i class="bi fs-5" :class="currentMilestone.icon || 'bi-stars'"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill fw-bold px-2.5 py-1 text-white" 
                          style="background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase;"
                          x-text="currentMilestone.badge || 'Dynasty Feature'"></span>
                    <span class="badge rounded-pill fw-bold px-2.5 py-1"
                          style="background: #fef08a; color: #854d0e; font-size: 0.72rem;"
                          x-text="`Day ${currentMilestone.day || 1} Feature`"></span>
                </div>
                <h5 class="mb-0 fw-bold text-white mt-1" style="font-size: 1.15rem; letter-spacing: -0.01em;"
                    x-text="currentMilestone.title || '60-Day Heritage Discovery'"></h5>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" 
                    class="btn-close btn-close-white" 
                    @click="dismissWidget()" 
                    aria-label="Dismiss discovery card"
                    title="Dismiss"
                    style="min-width: 32px; min-height: 32px;"></button>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body px-4 pt-2 pb-4 position-relative">
        <p class="text-white-50 small mb-3" style="font-size: 0.88rem; line-height: 1.55;"
           x-text="currentMilestone.description || 'Explore the rich heritage features of FamilyPlatform.'"></p>

        <!-- Progress Overview -->
        <div class="p-3 rounded-3 mb-3" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-white-50" style="font-size: 0.78rem;">
                    60-Day Journey Progress: <strong class="text-white" x-text="`${completedCount}/${totalMilestones} Studios Mastered`"></strong>
                </span>
                <span class="badge rounded-pill bg-white text-dark fw-bold px-2 py-0.5" style="font-size: 0.72rem;"
                      x-text="`${progressPercentage}%`"></span>
            </div>
            <div class="progress" style="height: 6px; background-color: rgba(255, 255, 255, 0.15); border-radius: 999px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" 
                     :style="`width: ${progressPercentage}%; background-color: #f43f5e;`" 
                     :aria-valuenow="progressPercentage" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
        </div>

        <!-- Action Row -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">
            <a :href="currentMilestone.action_url || '/profilePage'"
               @click="launchStudio(currentMilestone)"
               class="btn btn-danger fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2"
               style="min-height: 44px; white-space: nowrap; flex-shrink: 0; background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); border: none;">
                <span x-text="currentMilestone.action_label || 'Launch Studio'"></span>
                <i class="bi bi-arrow-right"></i>
            </a>

            <button type="button"
                    class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold small d-inline-flex align-items-center gap-2"
                    @click="showAllStudiosModal = true"
                    style="min-height: 44px; white-space: nowrap; flex-shrink: 0; border-color: rgba(255, 255, 255, 0.35); background: rgba(255, 255, 255, 0.08);">
                <i class="bi bi-grid-fill"></i>
                <span>Explore All 15 Studios</span>
            </button>
        </div>
    </div>

    <!-- All 15 Studios Full Modal / Drawer -->
    <div class="modal fade show d-block" 
         tabindex="-1" 
         role="dialog"
         aria-labelledby="allStudiosModalLabel"
         aria-modal="true"
         x-show="showAllStudiosModal" 
         x-trap.noscroll="showAllStudiosModal"
         style="background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: #ffffff; color: #1e293b;">
                <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, #2a0a0d 0%, #6a0d15 100%); color: #ffffff;">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 36px; height: 36px; background: rgba(244, 63, 94, 0.25); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.5);">
                            <i class="bi bi-compass-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0" id="allStudiosModalLabel" style="font-size: 1.1rem;">
                                60-Day Heritage Discovery: All 15 Studios
                            </h5>
                            <p class="mb-0 text-white-50 small" style="font-size: 0.78rem;">
                                Silas Thorne Adaptive Fast-Forward: Launch any feature at your own pace
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            class="btn-close btn-close-white" 
                            @click="showAllStudiosModal = false" 
                            aria-label="Close"
                            style="min-width: 36px; min-height: 36px;"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <template x-for="m in milestones" :key="m.key">
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between"
                                     :style="m.completed ? 'background: #f0fdf4; border: 1.5px solid #86efac;' : (m.is_current ? 'background: #fff1f2; border: 1.5px solid #f43f5e;' : 'background: #f8fafc; border: 1px solid #e2e8f0;')">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <span class="badge rounded-pill px-2 py-0.5 fw-bold"
                                                  :style="m.completed ? 'background: #22c55e; color: #ffffff;' : 'background: #e2e8f0; color: #475569;'"
                                                  style="font-size: 0.68rem;"
                                                  x-text="`Day ${m.day}`"></span>
                                            <span class="badge rounded-pill px-2 py-0.5 fw-semibold"
                                                  :style="m.completed ? 'background: #dcfce7; color: #166534;' : 'background: #f1f5f9; color: #64748b;'"
                                                  style="font-size: 0.68rem;"
                                                  x-text="m.badge"></span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                                            <i class="bi text-danger" :class="m.icon"></i>
                                            <span x-text="m.title"></span>
                                            <template x-if="m.completed">
                                                <i class="bi bi-patch-check-fill text-success ms-auto"></i>
                                            </template>
                                        </h6>
                                        <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.45;"
                                           x-text="m.description"></p>
                                    </div>

                                    <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                        <span class="small fw-semibold" 
                                              :style="m.completed ? 'color: #166534;' : 'color: #64748b;'"
                                              style="font-size: 0.75rem;"
                                              x-text="m.completed ? '✓ Mastered' : 'Available Now'"></span>
                                        <a :href="m.action_url" 
                                           @click="launchStudio(m)"
                                           class="btn btn-sm rounded-pill px-3 fw-bold"
                                           :class="m.completed ? 'btn-outline-success' : 'btn-danger'"
                                           style="min-height: 36px; white-space: nowrap; flex-shrink: 0; font-size: 0.8rem;">
                                            <span x-text="m.action_label"></span>
                                            <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-2.5 bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted" style="font-size: 0.8rem;">
                        All 15 studios remain permanently unlocked for your family account.
                    </span>
                    <button type="button" 
                            class="btn btn-secondary rounded-pill px-4 fw-semibold" 
                            @click="showAllStudiosModal = false"
                            style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.discoveryJourneyWidget !== 'function') {
    window.discoveryJourneyWidget = function() {
        return {
            visible: false,
            inWindow: false,
            showAllStudiosModal: false,
            completedCount: 0,
            totalMilestones: 15,
            progressPercentage: 0,
            accountAgeDays: 0,
            currentMilestone: {},
            milestones: [],

            async initDiscoveryState() {
                try {
                    if (localStorage.getItem('fp_discovery_dismissed') === 'true' || sessionStorage.getItem('fp_discovery_dismissed') === 'true') {
                        this.visible = false;
                        this.inWindow = false;
                        return;
                    }

                    const response = await fetch('/api/discovery/journey', {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (response.ok) {
                        const res = await response.json();
                        if (res.status === 'success' && res.data) {
                            const d = res.data;
                            this.accountAgeDays = d.account_age_days || 0;
                            this.inWindow = Boolean(d.is_in_discovery_window);
                            this.completedCount = d.completed_count || 0;
                            this.totalMilestones = d.total_milestones || 15;
                            this.progressPercentage = d.progress_percentage || 0;
                            this.currentMilestone = d.current_milestone || {};
                            this.milestones = d.milestones || [];

                            // Strictly visible ONLY if within 60-day window and not all milestones completed
                            if (this.inWindow && this.completedCount < this.totalMilestones) {
                                this.visible = true;
                            } else {
                                this.visible = false;
                            }
                        }
                    }
                } catch (e) {
                    console.warn('[DiscoveryJourney] Error loading state:', e);
                }
            },

            async launchStudio(milestone) {
                if (!milestone || !milestone.key) return;

                try {
                    const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                                   || document.querySelector('input[name="token"]')?.value || '';
                    
                    const formData = new FormData();
                    formData.append('token', csrfToken);
                    formData.append('feature_key', milestone.key);

                    // Fire and forget fast-forward logging
                    fetch('/api/discovery/fast-forward', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    }).catch(() => {});
                } catch (err) {}
            },

            dismissWidget() {
                this.visible = false;
                this.inWindow = false;
                try {
                    localStorage.setItem('fp_discovery_dismissed', 'true');
                    sessionStorage.setItem('fp_discovery_dismissed', 'true');
                } catch (e) {}
            }
        };
    };
}
</script>
