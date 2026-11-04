{{-- Awaiting Family Approval Holding Experience --}}
{{-- Rendered when a user logs in with a pending family code request. Private family posts are locked until approved. --}}

<div x-data="pendingApprovalWidget({{ json_encode($pendingApprovalData ?? []) }})" class="pending-approval-holding-container mb-4">
    <!-- Main Status Card -->
    <div class="card border-0 shadow-sm mb-4" 
         style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #fff7f7 100%); border: 1.5px solid #fecdd3 !important; overflow: hidden;">
        
        <!-- Top Accent Bar -->
        <div style="height: 6px; background: linear-gradient(90deg, #a31621 0%, #dc2626 50%, #f59e0b 100%);"></div>

        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" 
                     style="width: 72px; height: 72px; background: rgba(163, 22, 33, 0.1); color: #a31621; font-size: 2.2rem; border: 2px solid rgba(163, 22, 33, 0.2);">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark" style="font-size: 1.6rem; letter-spacing: -0.02em;">
                    Membership Pending Approval
                </h3>
                <p class="text-muted mb-0" style="font-size: 1rem;">
                    Welcome to FamilyPlatform, <strong>{{ htmlspecialchars($data['firstName'] ?? 'Family Member') }}</strong>!
                </p>
                <div class="mt-2">
                    <span class="badge px-3 py-2 rounded-pill fw-bold" 
                          style="background: rgba(163, 22, 33, 0.1); color: #a31621; font-size: 0.92rem; border: 1px solid rgba(163, 22, 33, 0.25);">
                        <i class="bi bi-people-fill me-1"></i> Family Network: #<span x-text="familyCode"></span>
                    </span>
                </div>
            </div>

            <!-- Escalation & Status Banner -->
            <div class="card border-0 mb-4 p-3.5" 
                 :style="isBroadcasted ? 'background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%); border: 1.5px solid #f59e0b !important; border-radius: 14px;' : 'background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); border: 1.5px solid #bfdbfe !important; border-radius: 14px;'">
                
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" 
                         :style="isBroadcasted ? 'width: 42px; height: 42px; background: #fbbf24; color: #78350f; font-size: 1.25rem;' : 'width: 42px; height: 42px; background: #3b82f6; color: #ffffff; font-size: 1.25rem;'">
                        <i :class="isBroadcasted ? 'bi bi-broadcast' : 'bi bi-hourglass-split'"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <h6 class="fw-bold mb-0" :style="isBroadcasted ? 'color: #92400e;' : 'color: #1e3a8a;'" style="font-size: 1.02rem;">
                                <span x-show="!isBroadcasted">Pending Inviter Review</span>
                                <span x-show="isBroadcasted">Escalated to Entire Family</span>
                            </h6>
                            <span class="badge rounded-pill px-2.5 py-1 small" 
                                  :style="isBroadcasted ? 'background: #d97706; color: #ffffff;' : 'background: #2563eb; color: #ffffff;'"
                                  x-text="isBroadcasted ? 'Broadcast Active' : 'Initial 20-Min Window'">
                            </span>
                        </div>

                        <p class="mb-0 text-secondary" style="font-size: 0.92rem; line-height: 1.5;">
                            <template x-if="!isBroadcasted">
                                <span>
                                    We have notified your inviter, <strong class="text-dark" x-text="inviterName"></strong>, to approve your access.
                                    If they do not confirm within <strong class="text-dark" x-text="timeRemainingText"></strong>, this request will automatically broadcast to all family members.
                                </span>
                            </template>
                            <template x-if="isBroadcasted">
                                <span>
                                    Over 20 minutes have passed without initial confirmation. An urgent notification has been broadcast to all verified members of family 
                                    <strong class="text-dark">#<span x-text="familyCode"></span></strong>. Any family member or administrator can now approve your request.
                                </span>
                            </template>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Controls (Nudge Button + Live Status) -->
            <div class="d-flex flex-column align-items-center justify-content-center gap-3 pt-2">
                <button type="button" 
                        class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center gap-2"
                        :style="canNudge ? 'background-color: #a31621; border-color: #a31621; min-height: 48px; min-width: 260px; font-size: 1rem;' : 'background-color: #64748b; border-color: #64748b; min-height: 48px; min-width: 260px; font-size: 1rem; cursor: not-allowed; opacity: 0.85;'"
                        :disabled="!canNudge || isNudging"
                        @click="sendNudge($el)">
                    
                    <span x-show="!isNudging">
                        <i :class="isBroadcasted ? 'bi bi-megaphone-fill' : 'bi bi-bell-fill'"></i>
                    </span>
                    <span x-show="isNudging" class="spinner-border spinner-border-sm"></span>

                    <span x-show="!isNudging && canNudge" x-text="isBroadcasted ? 'Nudge Entire Family' : 'Nudge Approver (' + inviterName + ')'"></span>
                    <span x-show="!isNudging && !canNudge" x-text="'Nudge on Cooldown (' + cooldownText + ')'"></span>
                    <span x-show="isNudging">Sending Nudge...</span>
                </button>

                <p class="text-muted small mb-0 text-center" style="font-size: 0.84rem;">
                    <i class="bi bi-info-circle me-1"></i> 
                    <span x-show="!isBroadcasted">Nudging sends an immediate reminder email and in-app alert to your inviter.</span>
                    <span x-show="isBroadcasted">Nudging sends a high-priority alert to all members of family #<span x-text="familyCode"></span>.</span>
                </p>
            </div>

            <!-- Privacy Guarantee Shield -->
            <div class="mt-4 pt-3 border-top text-center">
                <p class="mb-0 text-muted small" style="font-size: 0.86rem;">
                    🔒 <strong>Privacy Assurance:</strong> Private family stories, photo albums, events, and the family tree remain confidential and will automatically unlock the moment your request is approved.
                </p>
            </div>
        </div>
    </div>

    <!-- What You Can Do While Waiting Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px; background-color: #ffffff;">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3 text-dark d-flex align-items-center gap-2" style="font-size: 1.1rem;">
                <i class="bi bi-palette2 text-primary"></i> Personalise Your Profile While You Wait
            </h5>
            <p class="text-muted small mb-3">
                You can upload your profile photograph and ensure your personal details are up to date. These details will be visible to your family once approved.
            </p>

            <div class="row g-3">
                <div class="col-sm-6">
                    <a href="#editProfileModal" data-bs-toggle="modal" data-bs-target="#editProfileModal" 
                       class="btn btn-outline-secondary w-100 py-2.5 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2"
                       style="min-height: 44px;">
                        <i class="bi bi-pencil-square"></i> Edit Profile Details
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="#profilePicModal" data-bs-toggle="modal" data-bs-target="#profilePicModal" 
                       class="btn btn-outline-primary w-100 py-2.5 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2"
                       style="min-height: 44px; color: #a31621; border-color: #a31621;">
                        <i class="bi bi-camera-fill"></i> Update Profile Photo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function pendingApprovalWidget(initialData) {
    return {
        requestId: initialData.no || 0,
        familyCode: initialData.family_code || '',
        inviterName: initialData.inviter_name || 'Your Inviter',
        isBroadcasted: Boolean(initialData.is_broadcasted),
        secondsUntilBroadcast: parseInt(initialData.seconds_until_broadcast || 0, 10),
        cooldownRemaining: parseInt(initialData.nudge_cooldown_remaining || 0, 10),
        canNudge: Boolean(initialData.can_nudge),
        isNudging: false,
        timerInterval: null,
        pollInterval: null,

        init() {
            this.updateTimers();
            this.startTicker();
            this.startStatusPoller();
        },

        startTicker() {
            this.timerInterval = setInterval(() => {
                if (this.secondsUntilBroadcast > 0) {
                    this.secondsUntilBroadcast--;
                    if (this.secondsUntilBroadcast <= 0) {
                        this.isBroadcasted = true;
                    }
                }

                if (this.cooldownRemaining > 0) {
                    this.cooldownRemaining--;
                    if (this.cooldownRemaining <= 0) {
                        this.canNudge = true;
                    }
                }
            }, 1000);
        },

        startStatusPoller() {
            // Check status every 15 seconds to auto-refresh upon approval
            this.pollInterval = setInterval(async () => {
                try {
                    const res = await fetch('/api/family-code/status', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'approved') {
                            clearInterval(this.pollInterval);
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '🎉 Approval Granted!',
                                    text: 'Welcome to your family network! Loading your family sanctuary...',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert('Your request has been approved! Refreshing...');
                                window.location.reload();
                            }
                        }
                    }
                } catch (e) {
                    // non-blocking
                }
            }, 15000);
        },

        get timeRemainingText() {
            const mins = Math.floor(this.secondsUntilBroadcast / 60);
            const secs = this.secondsUntilBroadcast % 60;
            if (mins <= 0 && secs <= 0) return 'a few moments';
            return `${mins}m ${secs < 10 ? '0' : ''}${secs}s`;
        },

        get cooldownText() {
            const mins = Math.floor(this.cooldownRemaining / 60);
            const secs = this.cooldownRemaining % 60;
            return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        },

        async sendNudge(btn) {
            if (!this.canNudge || this.isNudging) return;

            this.isNudging = true;
            if (window.ScoreTenantSpinner) {
                ScoreTenantSpinner.start(btn);
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                               || '{{ $_SESSION['token'] ?? '' }}';

                const res = await fetch('/api/family-code/nudge', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-XSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ request_id: this.requestId })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.canNudge = false;
                    this.cooldownRemaining = parseInt(data.next_nudge_in || 300, 10);
                    if (data.broadcasted) {
                        this.isBroadcasted = true;
                    }

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Nudge Sent',
                            text: data.message || 'Nudge delivered successfully!',
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert(data.message || 'Nudge sent successfully!');
                    }
                } else {
                    const errorMsg = data.error || data.message || 'Unable to send nudge at this moment.';
                    if (window.Swal) {
                        Swal.fire({ icon: 'warning', title: 'Nudge Notice', text: errorMsg });
                    } else {
                        alert(errorMsg);
                    }
                }
            } catch (err) {
                console.error('Nudge error:', err);
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'Network Error', text: 'Please check your connection and try again.' });
                }
            } finally {
                this.isNudging = false;
                if (window.ScoreTenantSpinner) {
                    ScoreTenantSpinner.stop(btn);
                }
            }
        },

        updateTimers() {}
    };
}
</script>
