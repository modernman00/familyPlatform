@php
$wId = $widgetId ?? 'memoriesWidgetDesktop';
@endphp
<div class="card border-0 shadow-sm mb-4 overflow-hidden" 
     id="{{ $wId }}"
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e5e7eb);"
     x-data="memoriesMilestonesWidget('{{ $wId }}')"
     x-init="initWidget()"
     x-show="loaded ? (memories.length > 0 || milestones.length > 0) : true">
    
    <!-- Widget Header -->
    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-2 px-3 px-sm-4">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" 
                 style="width: 36px; height: 36px; background: rgba(99, 102, 241, 0.12); color: var(--primary-color, #4f46e5);">
                <i class="bi bi-stars fs-6 text-primary"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold" style="color: var(--text-color, #1f2937); font-size: 1rem;">Memories &amp; Milestones</h5>
                <p class="text-muted small mb-0" style="font-size: 0.78rem;">On this day flashbacks &amp; family celebrations</p>
            </div>
        </div>
        <button type="button" 
                class="btn btn-sm btn-link text-muted p-2" 
                @click="loadData()" 
                title="Refresh memories" 
                aria-label="Refresh memories"
                style="font-size: 0.85rem; text-decoration: none; min-width: 38px; min-height: 38px;">
            <i class="bi bi-arrow-clockwise" :class="{'spin-anim': loading}"></i>
        </button>
    </div>

    <!-- Widget Body -->
    <div class="card-body px-3 px-sm-4 pt-1 pb-3 pb-sm-4">
        <!-- Loading State -->
        <template x-if="loading && !loaded">
            <div class="py-4 text-center">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Gathering family memories...</p>
            </div>
        </template>

        <template x-if="loaded">
            <div class="d-flex flex-column gap-3">
                
                <!-- 1. Nostalgia "On This Day" Memory Card -->
                <template x-if="memories.length > 0">
                    <div class="p-3 rounded-4 position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(236, 72, 153, 0.08) 100%); border: 1px solid rgba(99, 102, 241, 0.18);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge rounded-pill px-2.5 py-1.5 fw-bold text-white d-inline-flex align-items-center gap-1 shadow-sm" 
                                  style="background: linear-gradient(135deg, #4f46e5, #ec4899); font-size: 0.72rem;">
                                <i class="bi bi-clock-history"></i> <span x-text="memories[0].years_ago_label"></span>
                            </span>
                            <span class="text-muted small" style="font-size: 0.75rem;" x-text="memories[0].formatted_date"></span>
                        </div>

                        <!-- Optional Memory Image Preview -->
                        <template x-if="memories[0].primary_image">
                            <div class="mb-2 rounded-3 overflow-hidden shadow-sm position-relative" style="max-height: 160px;">
                                <img :src="'/resources/images/post/' + memories[0].primary_image" 
                                     alt="Memory" 
                                     class="w-100 object-fit-cover" 
                                     style="height: 160px; max-height: 160px;"
                                     onerror="this.style.display='none'">
                            </div>
                        </template>

                        <!-- Memory Text -->
                        <p class="mb-2 fw-medium" style="color: var(--text-color, #1f2937); font-size: 0.88rem; line-height: 1.45;" x-text="memories[0].postMessage"></p>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                            <div class="d-flex align-items-center gap-2">
                                <template x-if="memories[0].profileImg">
                                    <img :src="'/resources/images/profile/' + memories[0].profileImg" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;" alt="Author">
                                </template>
                                <span class="text-muted small fw-semibold text-truncate" style="font-size: 0.78rem; max-width: 130px;" x-text="memories[0].fullName"></span>
                            </div>

                            <button type="button" 
                                    class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1 shadow-sm"
                                    style="font-size: 0.78rem; background-color: var(--primary-color, #4f46e5); border: none; min-height: 34px;"
                                    @click="openShareModal(memories[0])"
                                    :disabled="sharing">
                                <i class="bi bi-share-fill" style="font-size: 0.7rem;"></i> Share Flashback
                            </button>
                        </div>
                    </div>
                </template>

                <!-- 2. Upcoming Milestones List (Birthdays & Anniversaries) -->
                <template x-if="milestones.length > 0">
                    <div>
                        <h6 class="fw-bold mb-2 text-uppercase tracking-wider text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                            🎉 Upcoming Milestones
                        </h6>
                        <div class="d-flex flex-column gap-2">
                            <template x-for="(milestone, idx) in milestones.slice(0, 4)" :key="idx">
                                <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-between" 
                                     style="background-color: var(--hover-color, #f9fafb); border: 1px solid var(--border-color, #e5e7eb);">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                             :class="milestone.color_class" 
                                             style="width: 34px; height: 34px; font-size: 0.85rem;">
                                            <i class="bi" :class="milestone.icon"></i>
                                        </div>
                                        <div class="text-truncate">
                                            <div class="fw-bold text-truncate" style="color: var(--text-color, #1f2937); font-size: 0.84rem;" x-text="milestone.title"></div>
                                            <div class="text-muted small text-truncate" style="font-size: 0.75rem;" x-text="milestone.subtitle"></div>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold flex-shrink-0" 
                                          :class="milestone.days_remaining === 0 ? 'bg-danger text-white' : 'bg-light text-primary border'"
                                          style="font-size: 0.72rem;" 
                                          x-text="milestone.date_badge"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Empty State (No memories or milestones today) -->
                <template x-if="memories.length === 0 && milestones.length === 0">
                    <div class="text-center py-3 px-2">
                        <div class="mb-2 d-inline-flex align-items-center justify-content-center rounded-circle p-2.5" 
                             style="width: 44px; height: 44px; background: rgba(99, 102, 241, 0.08); color: var(--primary-color, #4f46e5);">
                            <i class="bi bi-camera-reels fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-color, #1f2937); font-size: 0.88rem;">No Flashbacks Today</h6>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                            Keep sharing photos and family moments! They will reappear here as cherished memories on this day in future years.
                        </p>
                    </div>
                </template>

            </div>
        </template>
    </div>

    <!-- Share Memory Modal -->
    <div class="modal fade" id="shareMemoryModal_{{ $wId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" style="font-size: 1.1rem;">
                        ✨ Share Flashback Memory
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <template x-if="activeMemory">
                        <div>
                            <div class="p-3 mb-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span class="fw-bold" x-text="activeMemory.years_ago_label"></span>
                                    <span x-text="activeMemory.formatted_date"></span>
                                </div>
                                <p class="mb-0 small text-secondary fst-italic" x-text="'&ldquo;' + activeMemory.postMessage + '&rdquo;'"></p>
                            </div>

                            <label class="form-label fw-semibold small mb-1">Add a reflection message (optional):</label>
                            <textarea class="form-control rounded-3" 
                                      rows="3" 
                                      x-model="reflectionText" 
                                      placeholder="Remember this? How time flies..."></textarea>
                        </div>
                    </template>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" 
                            class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" 
                            @click="submitShareMemory()" 
                            :disabled="sharing">
                        <span x-show="!sharing">Share to Family Feed</span>
                        <span x-show="sharing" class="spinner-border spinner-border-sm" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.memoriesMilestonesWidget !== 'function') {
    window.memoriesMilestonesWidget = function(widgetId) {
        return {
            widgetId: widgetId || 'memoriesWidgetDesktop',
            loading: true,
            loaded: false,
            memories: [],
            milestones: [],
            activeMemory: null,
            reflectionText: '',
            sharing: false,

            initWidget() {
                this.loadData();
            },

            async loadData() {
                this.loading = true;
                try {
                    const response = await fetch('/api/memories/today', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Failed to fetch memories');
                    const result = await response.json();
                    if (result.status === 'success' && result.data) {
                        this.memories = result.data.memories || [];
                        this.milestones = result.data.milestones || [];
                    }
                } catch (err) {
                    console.warn('[MemoriesWidget] Load error:', err);
                } finally {
                    this.loading = false;
                    this.loaded = true;
                }
            },

            openShareModal(memory) {
                this.activeMemory = memory;
                this.reflectionText = '';
                const modalEl = document.getElementById(`shareMemoryModal_${this.widgetId}`);
                if (modalEl && window.bootstrap) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            },

            async submitShareMemory() {
                if (!this.activeMemory) return;
                this.sharing = true;

                try {
                    const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                                   || document.querySelector('input[name="token"]')?.value || '';

                    const formData = new FormData();
                    formData.append('token', csrfToken);
                    formData.append('post_no', this.activeMemory.post_no);
                    formData.append('reflection_message', this.reflectionText);
                    formData.append('fam_code', this.activeMemory.postFamCode || '');

                    const response = await fetch('/api/memories/share', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();

                    if (response.ok && data.status === 'success') {
                        const modalEl = document.getElementById(`shareMemoryModal_${this.widgetId}`);
                        if (modalEl && window.bootstrap) {
                            bootstrap.Modal.getInstance(modalEl)?.hide();
                        }
                        if (window.Toast) {
                            Toast.fire({ icon: 'success', title: 'Flashback shared to family feed! ✨' });
                        } else {
                            alert('Flashback memory shared to family feed!');
                        }
                    } else {
                        alert(data.message || 'Failed to share memory');
                    }
                } catch (err) {
                    console.error('[MemoriesWidget] Share error:', err);
                    alert('An error occurred while sharing the memory.');
                } finally {
                    this.sharing = false;
                }
            }
        };
    };
}
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spin-anim {
    display: inline-block;
    animation: spin 0.8s linear infinite;
}
</style>
