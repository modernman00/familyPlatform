<!-- Family Announcements Bulletin Component -->
@php
    $famCode = (string)($_SESSION['famCode'] ?? $data['famCode'] ?? '');
    $activeAnnouncements = [];
    if (!empty($famCode)) {
        $activeAnnouncements = \App\model\FamilyAnnouncement::getActiveAnnouncements($famCode, 3);
    }
@endphp

<div id="familyAnnouncementsContainer" class="mb-3">
    @if(!empty($activeAnnouncements))
        @foreach($activeAnnouncements as $announcement)
            @php
                $prio = (string)($announcement['priority'] ?? 'important');
                $isUrgent = ($prio === 'urgent');
                $isNormal = ($prio === 'normal');
                
                $bgColor = $isUrgent 
                    ? 'linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%)' 
                    : ($isNormal ? 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)' : 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)');
                
                $borderLeftColor = $isUrgent ? '#ef4444' : ($isNormal ? '#3b82f6' : '#f59e0b');
                $textColor = $isUrgent ? '#991b1b' : ($isNormal ? '#1e40af' : '#92400e');
                $iconClass = $isUrgent ? 'bi-exclamation-octagon-fill text-danger' : ($isNormal ? 'bi-megaphone-fill text-primary' : 'bi-pin-angle-fill text-warning');
                $badgeText = $isUrgent ? 'URGENT NOTICE' : ($isNormal ? 'FAMILY BULLETIN' : 'IMPORTANT ANNOUNCEMENT');
                $badgeClass = $isUrgent ? 'bg-danger text-white' : ($isNormal ? 'bg-primary text-white' : 'bg-warning text-dark');
                
                $annAuthorName = trim((string)($announcement['firstName'] ?? 'Family') . ' ' . (string)($announcement['lastName'] ?? 'Member'));
                $annDate = !empty($announcement['created_at']) ? date('M j, g:i A', strtotime((string)$announcement['created_at'])) : 'Recent';
                $canDelete = ($announcement['author_id'] === ($_SESSION['id'] ?? '')) || (($_SESSION['type'] ?? '') === 'admin');
            @endphp

            <div class="card mb-3 border-0 shadow-sm family-announcement-card" id="announcementCard-{{ $announcement['id'] }}"
                 style="border-radius: 16px; background: {{ $bgColor }}; border-left: 5px solid {{ $borderLeftColor }} !important;">
                <div class="card-body p-3 p-sm-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $badgeClass }} px-3 py-1 rounded-pill fw-bold" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                <i class="bi {{ $iconClass }} me-1"></i> {{ $badgeText }}
                            </span>
                            <span class="text-muted small" style="font-size: 0.78rem;">
                                By <strong>{{ htmlspecialchars($annAuthorName, ENT_QUOTES, 'UTF-8') }}</strong> &bull; {{ $annDate }}
                            </span>
                        </div>

                        @if($canDelete)
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-muted" 
                                    onclick="dismissAnnouncement({{ (int)$announcement['id'] }})"
                                    title="Remove this announcement">
                                <i class="bi bi-x-circle fs-6"></i>
                            </button>
                        @endif
                    </div>

                    <h5 class="fw-bold mb-2" style="color: {{ $textColor }}; font-size: 1.1rem;">
                        {{ htmlspecialchars((string)$announcement['title'], ENT_QUOTES, 'UTF-8') }}
                    </h5>

                    <p class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.6; white-space: pre-line;">
                        {{ htmlspecialchars((string)$announcement['message'], ENT_QUOTES, 'UTF-8') }}
                    </p>
                </div>
            </div>
        @endforeach
    @endif
</div>

<script>
function dismissAnnouncement(announcementId) {
    if (!confirm('Dismiss this family announcement?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch('/api/announcement/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            id: announcementId,
            token: csrfToken
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const card = document.getElementById('announcementCard-' + announcementId);
            if (card) {
                card.style.transition = 'opacity 0.4s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 400);
            }
        } else {
            alert(data.message || 'Unable to dismiss announcement.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error.');
    });
}
</script>
