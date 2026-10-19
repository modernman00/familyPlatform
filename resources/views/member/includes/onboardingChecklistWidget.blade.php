<div class="card border-0 shadow-sm mb-4 overflow-hidden onboarding-wizard-card"
     id="onboardingChecklistWidget"
     style="border-radius: 20px; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); color: #ffffff; border: 1.5px solid rgba(129, 140, 248, 0.4);"
     x-data="onboardingChecklist()"
     x-init="initState()"
     x-show="visible && !isDone()"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform -translate-y-4">

    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center"
                 style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.45);">
                <i class="bi bi-lightning-charge-fill fs-5"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="mb-0 fw-bold text-white" style="font-size: 1.1rem; letter-spacing: -0.01em;">
                        60-Second Quick-Start
                    </h5>
                    <span class="badge rounded-pill bg-warning text-dark fw-bold px-2 py-0.5" style="font-size: 0.72rem;">
                        <span x-text="state.completed_count"></span>/3 Done
                    </span>
                </div>
                <p class="mb-0 text-white-50 small" style="font-size: 0.82rem;">
                    Unlock the full power of your family dynasty in 3 fast steps
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" 
                    class="btn-close btn-close-white" 
                    @click="dismiss()" 
                    aria-label="Dismiss checklist"
                    title="Dismiss"></button>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="px-4 pt-2 pb-1">
        <div class="progress" style="height: 6px; background-color: rgba(255, 255, 255, 0.15); border-radius: 999px;">
            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 :style="`width: ${state.progress_percentage}%`" 
                 :aria-valuenow="state.progress_percentage" 
                 aria-valuemin="0" 
                 aria-valuemax="100"></div>
        </div>
    </div>

    <!-- Card Body: 3 Interactive Action Steps -->
    <div class="card-body px-4 pt-3 pb-4">
        <div class="d-flex flex-column gap-2.5">
            
            <!-- Step 1: Explore Family Tree -->
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all"
                 :style="state.steps?.explore_tree?.done ? 'background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(52, 211, 153, 0.35);' : 'background: rgba(15, 23, 42, 0.45); border: 1px solid rgba(255, 255, 255, 0.12);'">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         :style="state.steps?.explore_tree?.done ? 'width: 32px; height: 32px; background: #10b981; color: #ffffff;' : 'width: 32px; height: 32px; background: rgba(255, 255, 255, 0.15); color: #ffffff;'">
                        <i class="bi" :class="state.steps?.explore_tree?.done ? 'bi-check-lg' : 'bi-diagram-3-fill'"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white" style="font-size: 0.9rem;" :class="{'text-decoration-line-through text-white-50': state.steps?.explore_tree?.done}">
                            1. Explore Your Family Tree
                        </div>
                        <div class="text-white-50 small" style="font-size: 0.78rem;">
                            View generations, organogram nodes, and family branches.
                        </div>
                    </div>
                </div>
                <template x-if="!state.steps?.explore_tree?.done">
                    <a href="/organogram?mode=canvas" 
                       class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary shadow-sm"
                       style="font-size: 0.78rem; min-height: 32px;"
                       @click="markStep('explore_tree')">
                        Open Tree <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </template>
                <template x-if="state.steps?.explore_tree?.done">
                    <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Completed ✓</span>
                </template>
            </div>

            <!-- Step 2: 1-Tap Family Invite (WhatsApp / WebShare) -->
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all"
                 :style="state.steps?.invite_family?.done ? 'background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(52, 211, 153, 0.35);' : 'background: rgba(15, 23, 42, 0.45); border: 1px solid rgba(255, 255, 255, 0.12);'">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         :style="state.steps?.invite_family?.done ? 'width: 32px; height: 32px; background: #10b981; color: #ffffff;' : 'width: 32px; height: 32px; background: rgba(255, 255, 255, 0.15); color: #ffffff;'">
                        <i class="bi" :class="state.steps?.invite_family?.done ? 'bi-check-lg' : 'bi-person-plus-fill'"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white" style="font-size: 0.9rem;" :class="{'text-decoration-line-through text-white-50': state.steps?.invite_family?.done}">
                            2. Invite Relatives to Connect
                        </div>
                        <div class="text-white-50 small" style="font-size: 0.78rem;">
                            Share your code: <strong class="text-warning" x-text="state.invite_data?.family_code"></strong> with parents, siblings, or kin.
                        </div>
                    </div>
                </div>
                <template x-if="!state.steps?.invite_family?.done">
                    <div class="d-flex gap-2">
                        <button type="button" 
                                class="btn btn-sm btn-warning rounded-pill px-3 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-1"
                                style="font-size: 0.78rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                                data-bs-toggle="modal" 
                                data-bs-target="#inviteFamilyModal"
                                @click="openInviteModal()">
                            <i class="bi bi-person-lines-fill"></i> Invite Family
                        </button>
                    </div>
                </template>
                <template x-if="state.steps?.invite_family?.done">
                    <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Connected ✓</span>
                </template>
            </div>

            <!-- Step 3: Post First Family Memory -->
            <div class="p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all"
                 :style="state.steps?.post_memory?.done ? 'background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(52, 211, 153, 0.35);' : 'background: rgba(15, 23, 42, 0.45); border: 1px solid rgba(255, 255, 255, 0.12);'">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         :style="state.steps?.post_memory?.done ? 'width: 32px; height: 32px; background: #10b981; color: #ffffff;' : 'width: 32px; height: 32px; background: rgba(255, 255, 255, 0.15); color: #ffffff;'">
                        <i class="bi" :class="state.steps?.post_memory?.done ? 'bi-check-lg' : 'bi-camera-fill'"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-white" style="font-size: 0.9rem;" :class="{'text-decoration-line-through text-white-50': state.steps?.post_memory?.done}">
                            3. Share Your First Family Photo/Update
                        </div>
                        <div class="text-white-50 small" style="font-size: 0.78rem;">
                            Post a picture, story, or milestone to the private family feed.
                        </div>
                    </div>
                </div>
                <template x-if="!state.steps?.post_memory?.done">
                    <button type="button" 
                            class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-primary shadow-sm"
                            style="font-size: 0.78rem; min-height: 32px;"
                            data-bs-toggle="modal" 
                            data-bs-target="#postModal"
                            @click="markStep('post_memory')">
                        Create Post <i class="bi bi-plus-circle ms-1"></i>
                    </button>
                </template>
                <template x-if="state.steps?.post_memory?.done">
                    <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Posted ✓</span>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.onboardingChecklist !== 'function') {
    window.onboardingChecklist = function() {
        return {
            visible: false,
            isPreview: false,
            state: {
                completed: false,
                progress_percentage: 0,
                completed_count: 0,
                total_steps: 3,
                steps: {},
                invite_data: {}
            },

            isDone() {
                if (this.isPreview) return false;
                const total = this.state.total_steps || 3;
                return Boolean(this.state.completed || (typeof this.state.completed_count === 'number' && this.state.completed_count >= total));
            },

            async initState() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('onboarding') && urlParams.get('onboarding') === 'reset') {
                    try {
                        localStorage.removeItem('fp_onboarding_dismissed');
                        sessionStorage.removeItem('fp_onboarding_minimized');
                        sessionStorage.removeItem('fp_onboarding_completed');
                    } catch (e) {}
                }

                if (urlParams.has('onboarding') && urlParams.get('onboarding') === 'preview') {
                    this.isPreview = true;
                }

                if (!this.isPreview && sessionStorage.getItem('fp_onboarding_completed') === 'true') {
                    this.visible = false;
                    return;
                }

                if (!this.isPreview && sessionStorage.getItem('fp_onboarding_minimized') === 'true') {
                    this.visible = false;
                    return;
                }

                try {
                    const response = await fetch('/api/onboarding/state', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const res = await response.json();
                        if (res.status === 'success' && res.data) {
                            this.state = res.data;
                            if (this.isPreview) {
                                this.state.completed = false;
                                this.visible = true;
                                return;
                            }

                            if (this.isDone()) {
                                this.visible = false;
                                try {
                                    sessionStorage.setItem('fp_onboarding_completed', 'true');
                                } catch (e) {}
                            } else {
                                this.visible = true;
                            }
                        }
                    }
                } catch (e) {
                    console.warn('[Onboarding] Failed to load state:', e);
                }
            },

            async markStep(stepKey) {
                try {
                    const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                                   || document.querySelector('input[name="token"]')?.value || '';
                    
                    const formData = new FormData();
                    formData.append('token', csrfToken);
                    formData.append('step', stepKey);

                    await fetch('/api/onboarding/step', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });

                    if (this.state.steps && this.state.steps[stepKey]) {
                        this.state.steps[stepKey].done = true;
                        let count = 0;
                        for (const k in this.state.steps) {
                            if (this.state.steps[k]?.done) count++;
                        }
                        this.state.completed_count = count;
                        const total = this.state.total_steps || 3;
                        this.state.progress_percentage = Math.round((count / total) * 100);

                        if (count >= total) {
                            this.state.completed = true;
                            // Allow user to briefly register 3/3 completion, then completely disappear
                            setTimeout(() => {
                                this.visible = false;
                                try {
                                    sessionStorage.setItem('fp_onboarding_completed', 'true');
                                } catch (e) {}
                            }, 1000);
                        }
                    }
                } catch (err) {
                    console.error('[Onboarding] Error marking step:', err);
                }
            },

            openInviteModal() {
                this.markStep('invite_family');
                const modalEl = document.getElementById('inviteFamilyModal');
                if (modalEl && window.bootstrap?.Modal) {
                    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                }
            },

            async shareInvite() {
                const invite = this.state.invite_data;
                if (!invite) return;

                await this.markStep('invite_family');

                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: `Join our ${invite.surname || 'Family'} Dynasty on FamilyPlatform`,
                            text: invite.invite_text,
                            url: invite.invite_url
                        });
                        return;
                    } catch (e) {
                        // User cancelled or unsupported, fallback to WhatsApp
                    }
                }

                // Fallback: Open WhatsApp directly
                window.open(invite.whatsapp_url, '_blank', 'noopener,noreferrer');
            },

            dismiss() {
                this.visible = false;
                try {
                    sessionStorage.setItem('fp_onboarding_minimized', 'true');
                } catch (e) {}
            }
        };
    };
}
</script>
