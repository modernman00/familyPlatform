<!-- Modal: Create Family Announcement -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-labelledby="createAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-megaphone-fill text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createAnnouncementModalLabel" style="font-size: 1.15rem;">
                            Broadcast Family Announcement
                        </h5>
                        <small class="text-white-50">Pin an important notice to your family feed</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="familyAnnouncementForm">
                @include('csrf')
                <div class="modal-body p-4">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label fw-bold small text-dark">Announcement Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="announcementTitle" name="title" placeholder="e.g. 2026 Reunion Date or Important Family Update" required>
                    </div>

                    <!-- Priority & Category -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="announcementPriority" class="form-label fw-bold small text-dark">Priority</label>
                            <select class="form-select rounded-3" id="announcementPriority" name="priority">
                                <option value="important">📌 Important Notice</option>
                                <option value="urgent">🚨 Urgent Alert</option>
                                <option value="normal">📢 General Bulletin</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="announcementType" class="form-label fw-bold small text-dark">Category</label>
                            <select class="form-select rounded-3" id="announcementType" name="type">
                                <option value="general">General Update</option>
                                <option value="reunion">Reunion / Gathering</option>
                                <option value="milestone">Milestone / Life Event</option>
                                <option value="celebration">Celebration</option>
                                <option value="alert">Urgent Notice</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label for="announcementMessage" class="form-label fw-bold small text-dark">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" id="announcementMessage" name="message" rows="4" placeholder="Provide full details, dates, addresses, or instructions for the family..." required></textarea>
                    </div>

                    <!-- Expiration Date -->
                    <div class="mb-2">
                        <label for="announcementExpiresAt" class="form-label fw-semibold small text-muted">Pin Until (Optional)</label>
                        <input type="date" class="form-control rounded-3" id="announcementExpiresAt" name="expires_at" min="{{ date('Y-m-d') }}">
                        <small class="text-muted">Leave empty to keep pinned until manually dismissed.</small>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnBroadcastAnnouncement" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #4f46e5; border-color: #4f46e5; min-height: 44px;">
                        <i class="bi bi-broadcast me-1"></i> Broadcast Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('component.submitSpinner')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const annForm = document.getElementById('familyAnnouncementForm');
    if (!annForm) return;

    annForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const title = document.getElementById('announcementTitle').value.trim();
        const message = document.getElementById('announcementMessage').value.trim();
        const priority = document.getElementById('announcementPriority').value;
        const type = document.getElementById('announcementType').value;
        const expiresAt = document.getElementById('announcementExpiresAt').value;
        const csrfToken = annForm.querySelector('input[name="token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!title || !message) {
            alert('Please provide both title and message.');
            return;
        }

        const submitBtn = document.getElementById('btnBroadcastAnnouncement');
        if (window.FamilyPlatformSpinner) {
            window.FamilyPlatformSpinner.start(submitBtn, ' Broadcasting...');
        } else {
            submitBtn.disabled = true;
        }

        fetch('/api/announcement/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                message: message,
                priority: priority,
                type: type,
                expires_at: expiresAt,
                token: csrfToken
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const modalEl = document.getElementById('createAnnouncementModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                annForm.reset();

                // Alert and reload feed to show pinned bulletin
                alert('Family announcement broadcasted!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to broadcast announcement.');
                if (window.FamilyPlatformSpinner) {
                    window.FamilyPlatformSpinner.stop(submitBtn);
                } else {
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred.');
            if (window.FamilyPlatformSpinner) {
                window.FamilyPlatformSpinner.stop(submitBtn);
            } else {
                submitBtn.disabled = false;
            }
        });
    });
});
</script>
