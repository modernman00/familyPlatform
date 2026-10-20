<!-- Middle Column - Feed -->
<style>
  .highlighted-post {
    animation: postHighlightPulse 2.5s ease-out;
  }
  @keyframes postHighlightPulse {
    0% { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.6); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
  }
</style>
<div class="feed-column">
  @if(!empty($pendingFamilyRequests))
    @foreach($pendingFamilyRequests as $joinReq)
      <div class="card mb-4 border-0 shadow-sm family-join-request-card" id="familyJoinReq-{{ $joinReq['no'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%); border-left: 5px solid #00bfa5 !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #ccfbf1; color: #0f766e; font-size: 1.3rem;">
              <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                👋 <strong>{{ htmlspecialchars($joinReq['requesterName'] ?? 'New Member') }}</strong> wants to join your <strong>{{ htmlspecialchars($joinReq['family_code'] ?? '') }}</strong> family network
              </h6>
              <p class="mb-0 text-muted" style="font-size: 0.88rem;">
                Requested to connect on FamilyPlatform. Approve to grant immediate access to your family tree and updates.
              </p>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold" 
                    id="btnApproveJoin-{{ $joinReq['no'] }}"
                    style="background-color: #00bfa5; min-height: 44px;" 
                    onclick="handleJoinRequestAction({{ (int)$joinReq['no'] }}, '{{ htmlspecialchars($joinReq['approval_token'] ?? '') }}', 'approve')">
              <i class="bi bi-check-lg me-1"></i> Approve Request
            </button>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDenyJoin-{{ $joinReq['no'] }}"
                    style="min-height: 44px;"
                    onclick="handleJoinRequestAction({{ (int)$joinReq['no'] }}, '{{ htmlspecialchars($joinReq['approval_token'] ?? '') }}', 'deny')">
              <i class="bi bi-x-lg me-1"></i> Deny
            </button>
          </div>
        </div>
      </div>
    @endforeach

    <script>
      function handleJoinRequestAction(requestId, token, action) {
        const approveBtn = document.getElementById('btnApproveJoin-' + requestId);
        const denyBtn = document.getElementById('btnDenyJoin-' + requestId);
        const card = document.getElementById('familyJoinReq-' + requestId);

        if (approveBtn) approveBtn.disabled = true;
        if (denyBtn) denyBtn.disabled = true;

        if (action === 'approve' && approveBtn) {
          approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Approving...';
        } else if (action === 'deny' && denyBtn) {
          denyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Denying...';
        }

        const endpoint = `/api/family-code/${action}/${requestId}?token=${encodeURIComponent(token)}`;

        fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const currentNotifCount = Math.max(0, parseInt(sessionStorage.getItem('notificationCount') || '1') - 1);
            sessionStorage.setItem('notificationCount', currentNotifCount);
            window.dispatchEvent(new CustomEvent('pwa:badge-updated', { detail: { count: currentNotifCount } }));
            window.dispatchEvent(new CustomEvent('pwa:notification-received'));

            if (card) {
              const actionMsg = action === 'approve' 
                ? 'Successfully approved! Member has been connected to your family network.' 
                : 'Request denied.';
              const alertColor = action === 'approve' ? 'text-success' : 'text-danger';
              const iconClass = action === 'approve' ? 'bi-check-circle-fill' : 'bi-dash-circle-fill';
              
              card.innerHTML = `<div class="card-body p-3 text-center ${alertColor} fw-bold"><i class="bi ${iconClass} me-2"></i> ${actionMsg}</div>`;
              setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 500);
              }, 2000);
            }
          } else {
            (window.swalError || alert)(data.error || 'Failed to process request.');
            if (approveBtn) { approveBtn.disabled = false; approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Approve Request'; }
            if (denyBtn) { denyBtn.disabled = false; denyBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Deny'; }
          }
        })
        .catch(err => {
          console.error('Error handling join request:', err);
          (window.swalError || alert)('A network error occurred. Please try again.');
          if (approveBtn) { approveBtn.disabled = false; approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Approve Request'; }
          if (denyBtn) { denyBtn.disabled = false; denyBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Deny'; }
        });
      }
    </script>
  @endif

  @if(!empty($pendingMigrations['incomingNudges']))
    @foreach($pendingMigrations['incomingNudges'] as $nudge)
      <div class="card mb-4 border-0 shadow-sm family-migration-nudge-card" id="migrationNudgeCard-{{ $nudge['id'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border-left: 5px solid #8b5cf6 !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #ddd6fe; color: #6d28d9; font-size: 1.3rem;">
              <i class="bi bi-send-check-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                🌟 <strong>{{ htmlspecialchars($nudge['requesterName'] ?? 'A family relative') }}</strong> invited you to migrate to the <strong>#{{ htmlspecialchars($nudge['new_family_code'] ?? '') }}</strong> family network!
              </h6>
              <p class="mb-1 text-muted" style="font-size: 0.88rem;">
                You are currently registered with code #{{ htmlspecialchars($nudge['old_family_code'] ?? '') }}. Accept this invitation to unite under the main family tree and timeline.
              </p>
              @if(!empty($nudge['message']))
                <p class="mb-0 text-secondary fst-italic small bg-white bg-opacity-75 p-2 rounded-2 mt-1 border">
                  "{{ htmlspecialchars($nudge['message']) }}"
                </p>
              @endif
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold" 
                    id="btnAcceptNudge-{{ $nudge['id'] }}"
                    style="background-color: #7c3aed; min-height: 44px;" 
                    onclick="handleMigrationNudgeAction({{ (int)$nudge['id'] }}, '{{ htmlspecialchars($nudge['token'] ?? '') }}', 'accept')">
              <i class="bi bi-check-circle-fill me-1"></i> Accept & Migrate
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDeclineNudge-{{ $nudge['id'] }}"
                    style="min-height: 44px;"
                    onclick="handleMigrationNudgeAction({{ (int)$nudge['id'] }}, '{{ htmlspecialchars($nudge['token'] ?? '') }}', 'decline')">
              <i class="bi bi-x-lg me-1"></i> Decline
            </button>
          </div>
        </div>
      </div>
    @endforeach

    <script>
      function handleMigrationNudgeAction(migrationId, token, action) {
        const acceptBtn = document.getElementById('btnAcceptNudge-' + migrationId);
        const declineBtn = document.getElementById('btnDeclineNudge-' + migrationId);
        const card = document.getElementById('migrationNudgeCard-' + migrationId);

        if (acceptBtn) acceptBtn.disabled = true;
        if (declineBtn) declineBtn.disabled = true;

        if (action === 'accept' && acceptBtn) {
          acceptBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Migrating...';
        } else if (action === 'decline' && declineBtn) {
          declineBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Declining...';
        }

        const endpoint = action === 'accept' ? '/api/family-migration/accept-nudge' : '/api/family-migration/decline';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-XSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ id: migrationId, token: token })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            if (card) {
              const actionMsg = action === 'accept' 
                ? 'Welcome to the family! Your account has successfully migrated. Refreshing page...' 
                : 'Invitation declined.';
              const alertColor = action === 'accept' ? 'text-success' : 'text-muted';
              const iconClass = action === 'accept' ? 'bi-check-circle-fill' : 'bi-dash-circle';
              
              card.innerHTML = `<div class="card-body p-3 text-center ${alertColor} fw-bold"><i class="bi ${iconClass} me-2"></i> ${actionMsg}</div>`;
              setTimeout(() => {
                if (action === 'accept') {
                  window.location.reload();
                } else {
                  card.style.transition = 'opacity 0.5s ease';
                  card.style.opacity = '0';
                  setTimeout(() => card.remove(), 500);
                }
              }, 1500);
            }
          } else {
            (window.swalError || alert)(data.error || 'Failed to process invitation.');
            if (acceptBtn) { acceptBtn.disabled = false; acceptBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Accept & Migrate'; }
            if (declineBtn) { declineBtn.disabled = false; declineBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Decline'; }
          }
        })
        .catch(err => {
          console.error('Error handling migration nudge:', err);
          (window.swalError || alert)('A network error occurred. Please try again.');
          if (acceptBtn) { acceptBtn.disabled = false; acceptBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Accept & Migrate'; }
          if (declineBtn) { declineBtn.disabled = false; declineBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Decline'; }
        });
      }
    </script>
  @endif

  @if(!empty($pendingMigrations['incomingRequests']))
    @foreach($pendingMigrations['incomingRequests'] as $migReq)
      <div class="card mb-4 border-0 shadow-sm family-migration-request-card" id="migrationReqCard-{{ $migReq['id'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-left: 5px solid #2563eb !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #bfdbfe; color: #1d4ed8; font-size: 1.3rem;">
              <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                👋 <strong>{{ htmlspecialchars($migReq['requesterName'] ?? 'A relative') }}</strong> (currently code #{{ htmlspecialchars($migReq['old_family_code'] ?? '') }}) requested to migrate to your <strong>#{{ htmlspecialchars($migReq['new_family_code'] ?? '') }}</strong> family network!
              </h6>
              <p class="mb-1 text-muted" style="font-size: 0.88rem;">
                Approve to welcome them into your family tree, shared memories, and household timeline.
              </p>
              @if(!empty($migReq['message']))
                <p class="mb-0 text-secondary fst-italic small bg-white bg-opacity-75 p-2 rounded-2 mt-1 border">
                  "{{ htmlspecialchars($migReq['message']) }}"
                </p>
              @endif
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold" 
                    id="btnApproveMigReq-{{ $migReq['id'] }}"
                    style="background-color: #2563eb; min-height: 44px;" 
                    onclick="handleMigrationRequestAction({{ (int)$migReq['id'] }}, '{{ htmlspecialchars($migReq['token'] ?? '') }}', 'approve')">
              <i class="bi bi-check-lg me-1"></i> Approve Migration
            </button>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDenyMigReq-{{ $migReq['id'] }}"
                    style="min-height: 44px;"
                    onclick="handleMigrationRequestAction({{ (int)$migReq['id'] }}, '{{ htmlspecialchars($migReq['token'] ?? '') }}', 'deny')">
              <i class="bi bi-x-lg me-1"></i> Deny
            </button>
          </div>
        </div>
      </div>
    @endforeach

    <script>
      function handleMigrationRequestAction(migrationId, token, action) {
        const approveBtn = document.getElementById('btnApproveMigReq-' + migrationId);
        const denyBtn = document.getElementById('btnDenyMigReq-' + migrationId);
        const card = document.getElementById('migrationReqCard-' + migrationId);

        if (approveBtn) approveBtn.disabled = true;
        if (denyBtn) denyBtn.disabled = true;

        if (action === 'approve' && approveBtn) {
          approveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Approving...';
        } else if (action === 'deny' && denyBtn) {
          denyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Denying...';
        }

        const endpoint = action === 'approve' ? '/api/family-migration/approve-request' : '/api/family-migration/decline';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-XSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ id: migrationId, token: token })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            if (card) {
              const actionMsg = action === 'approve' 
                ? 'Successfully approved! Relative has been welcomed into your family network.' 
                : 'Migration request denied.';
              const alertColor = action === 'approve' ? 'text-success' : 'text-danger';
              const iconClass = action === 'approve' ? 'bi-check-circle-fill' : 'bi-dash-circle-fill';
              
              card.innerHTML = `<div class="card-body p-3 text-center ${alertColor} fw-bold"><i class="bi ${iconClass} me-2"></i> ${actionMsg}</div>`;
              setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 500);
              }, 1800);
            }
          } else {
            (window.swalError || alert)(data.error || 'Failed to process migration request.');
            if (approveBtn) { approveBtn.disabled = false; approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Approve Migration'; }
            if (denyBtn) { denyBtn.disabled = false; denyBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Deny'; }
          }
        })
        .catch(err => {
          console.error('Error handling migration request:', err);
          (window.swalError || alert)('A network error occurred. Please try again.');
          if (approveBtn) { approveBtn.disabled = false; approveBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Approve Migration'; }
          if (denyBtn) { denyBtn.disabled = false; denyBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Deny'; }
        });
      }
    </script>
  @endif

  @if(!empty($unclaimedMatch))
  <div class="card mb-4 border-0 shadow-sm unclaimed-match-card" id="unclaimedMatchBanner" style="border-radius: 16px; background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border-left: 5px solid #2563eb !important;">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #dbeafe; color: #2563eb; font-size: 1.4rem;">
          <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div>
          <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
            👋 Welcome {{ htmlspecialchars($data['firstName'] ?? 'there') }}! Connect to your family tree?
          </h6>
          <p class="mb-0 text-muted" style="font-size: 0.88rem;">
            Your family member created a place for <strong>{{ htmlspecialchars($unclaimedMatch['full_name']) }}</strong> (<em>{{ htmlspecialchars($unclaimedMatch['role']) }}</em>) in the family tree. Is this you?
          </p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold" id="btnClaimNode" onclick="claimFamilyNode({{ (int)$unclaimedMatch['node_id'] }})">
          <i class="bi bi-check2-circle me-1"></i> Yes, that's me!
        </button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2" onclick="dismissFamilyNode({{ (int)$unclaimedMatch['node_id'] }})">
          Not now
        </button>
      </div>
    </div>
  </div>
  <script>
    function claimFamilyNode(nodeId) {
      const btn = document.getElementById('btnClaimNode');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Linking...'; }
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ $_SESSION['token'] ?? '' }}';
      fetch('/api/claim-family-node', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-XSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ node_id: nodeId, token: csrfToken })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const banner = document.getElementById('unclaimedMatchBanner');
          if (banner) {
            banner.innerHTML = '<div class="card-body p-3 text-center text-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Successfully connected to your family tree! Refreshing...</div>';
            setTimeout(() => window.location.reload(), 1200);
          }
        } else {
          (window.swalError || alert)(data.message || 'Unable to claim node.');
          if (btn) { btn.disabled = false; btn.innerText = "Yes, that's me!"; }
        }
      })
      .catch(err => {
        console.error(err);
        if (btn) { btn.disabled = false; btn.innerText = "Yes, that's me!"; }
      });
    }

    function dismissFamilyNode(nodeId) {
      const banner = document.getElementById('unclaimedMatchBanner');
      if (banner) banner.style.display = 'none';
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ $_SESSION['token'] ?? '' }}';
      fetch('/api/dismiss-claim-node', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-XSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ node_id: nodeId, token: csrfToken })
      }).catch(console.error);
    }
  </script>
  @endif

  <!-- Pinned Family Announcements Bulletin -->
  @includeIf('member.includes.announcementBanner')

  <!-- Family Reels & Stories Tray -->
  @includeIf('member.includes.reelsTray')

  <!-- 60-Second Onboarding Checklist (Alpine.js - auto hides when complete) -->
  @includeIf('member.includes.onboardingChecklistWidget')

  <!-- Contextual Kinship & Phone Contacts Inner Circle Prompt -->
  @includeIf('member.includes.kinshipPromptCard')

  {{-- 🌟 Ecosystem Cross-App Promotion Ribbon (PartyPlatform / iDecide) --}}
  @include('components.advert_banner', [
    'placement' => 'family_feed',
    'context' => [
      'tags' => ['party', 'event', 'celebration', 'birthday', 'milestone'],
    ]
  ])

  <!-- Post Composer (kept above the Kinship widget - creating a post is the
       primary action; kin discovery is secondary) -->
  <div class="card post-composer mb-4 border-0 shadow-sm" id="openPostModalTrigger" style="border-radius: 16px; overflow: hidden; background-color: var(--card-bg);">
    <div class="card-body p-3 p-sm-4">
      <div class="d-flex align-items-center gap-3">
        <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="profile" class="rounded-circle flex-shrink-0" width="44" height="44" style="object-fit: cover;">

        <div role="button" tabindex="0" class="composer-prompt flex-grow-1 rounded-pill px-3 py-3 text-truncate" data-bs-toggle="modal" data-bs-target="#postModal"
          style="background-color: var(--bg-color); border: 1px solid var(--border-color); cursor: text; transition: border-color 0.2s, background-color 0.2s;">
          <span style="color: var(--text-muted); font-size: 0.95rem;">Share a moment with your family...</span>
        </div>
      </div>

      <div class="composer-quick-actions d-flex align-items-center gap-1 mt-3 pt-3 flex-wrap" style="border-top: 1px solid var(--border-color);">
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Add a photo to a post">
          <i class="bi bi-camera-fill" style="color: #22c55e; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Photo</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Add a video to a post">
          <i class="bi bi-play-btn-fill" style="color: #ef4444; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Video</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal" aria-label="Broadcast family announcement">
          <i class="bi bi-megaphone-fill" style="color: #f59e0b; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Announcement</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" onclick="window.location.href='/familyJournal/create'" aria-label="Write family journal chronicle">
          <i class="bi bi-journal-bookmark-fill" style="color: #6366f1; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Journal</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Create a poll">
          <i class="bi bi-bar-chart-fill" style="color: #f97316; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Poll</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Family Heritage & Rituals Hub (Immediate 1-Click Viral Engagement) -->
  <div class="card border-0 shadow-sm mb-4 family-heritage-hub" style="border-radius: 18px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%); border: 1px solid rgba(0, 0, 0, 0.06) !important;">
    <div class="card-body p-3 p-sm-4">
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill shadow-xs" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-size: 0.72rem; letter-spacing: 0.5px; padding: 4px 10px; font-weight: 700;">
            <i class="bi bi-stars me-1"></i> RITUALS &amp; HERITAGE
          </span>
          <span class="fw-bold text-dark small" style="font-size: 0.92rem;">Family Dynasty Hub</span>
        </div>
        <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas" class="text-decoration-none small d-flex align-items-center gap-1" style="color: #0d6efd; font-size: 0.82rem; font-weight: 600;">
          <span>Explore Tree Canvas</span> <i class="bi bi-chevron-right" style="font-size: 0.72rem;"></i>
        </a>
      </div>

      <div class="row g-2">
        <!-- 1. Quests -->
        <div class="col-6 col-sm-3">
          <button type="button" onclick="openLineageQuestModal()" class="btn w-100 p-2.5 d-flex flex-column align-items-center text-center shadow-xs transition-all" style="border-radius: 14px; background: #f0fdf4; border: 1px solid rgba(16, 185, 129, 0.25) !important; min-height: 84px; justify-content: center;" title="Solve Missing Relatives on WhatsApp">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 text-success shadow-xs" style="width: 36px; height: 36px; background: #d1fae5;">
              <i class="bi bi-compass-fill fs-6"></i>
            </div>
            <span class="fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2;">Tree Quests</span>
            <span class="text-muted" style="font-size: 0.7rem;">Ask on WhatsApp</span>
          </button>
        </div>

        <!-- 2. Sunday Trivia -->
        <div class="col-6 col-sm-3">
          <button type="button" onclick="openComposerWithTrivia()" class="btn w-100 p-2.5 d-flex flex-column align-items-center text-center shadow-xs transition-all" style="border-radius: 14px; background: #fefce8; border: 1px solid rgba(245, 158, 11, 0.25) !important; min-height: 84px; justify-content: center;" title="Create or Play Sunday Family Trivia">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 text-warning shadow-xs" style="width: 36px; height: 36px; background: #fef08a;">
              <i class="bi bi-trophy-fill fs-6"></i>
            </div>
            <span class="fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2;">Sunday Trivia</span>
            <span class="text-muted" style="font-size: 0.7rem;">Quiz &amp; Guess Who</span>
          </button>
        </div>

        <!-- 3. WhatsApp Flyer -->
        <div class="col-6 col-sm-3">
          <button type="button" onclick="openFamilyFlyerModal()" class="btn w-100 p-2.5 d-flex flex-column align-items-center text-center shadow-xs transition-all" style="border-radius: 14px; background: #eff6ff; border: 1px solid rgba(59, 130, 246, 0.25) !important; min-height: 84px; justify-content: center;" title="Generate Heraldic WhatsApp Clan Status Flyer">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 text-primary shadow-xs" style="width: 36px; height: 36px; background: #dbeafe;">
              <i class="bi bi-card-image fs-6"></i>
            </div>
            <span class="fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2;">Clan Flyer</span>
            <span class="text-muted" style="font-size: 0.7rem;">WhatsApp Status</span>
          </button>
        </div>

        <!-- 4. Time Capsule -->
        <div class="col-6 col-sm-3">
          <button type="button" onclick="openTimeCapsuleModal()" class="btn w-100 p-2.5 d-flex flex-column align-items-center text-center shadow-xs transition-all" style="border-radius: 14px; background: #faf5ff; border: 1px solid rgba(168, 85, 247, 0.25) !important; min-height: 84px; justify-content: center;" title="Seal Personal Letters and Heirlooms for Future Milestones">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-1 shadow-xs" style="width: 36px; height: 36px; background: #f3e8ff; color: #a855f7;">
              <i class="bi bi-hourglass-split fs-6"></i>
            </div>
            <span class="fw-bold text-dark" style="font-size: 0.82rem; line-height: 1.2;">Time Capsule</span>
            <span class="text-muted" style="font-size: 0.7rem;">Future Milestones</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Highlights & Events Tray (Facebook Mobile UX: horizontal carousel above feed) -->
  @includeIf('member.includes.mobileHighlightsTray')

  <!-- Kinship Radar: Mobile-Only (visible < 992px, hidden on desktop where sidebar shows it) -->
  <div class="d-lg-none">
    @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => 'kinshipRadarWidgetMobile'])
  </div>

  <!-- Reactive Alpine.js Feed -->
  <div x-data="profileFeed({ famCode: '{{ $data['famCode'] ?? '' }}', userId: '{{ $data['id'] ?? '' }}' })" class="feed-posts-container">
    <!-- Error Alert if loading fails -->
    <template x-if="errorMessage">
      <div class="alert alert-danger shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span x-text="errorMessage"></span>
        <button class="btn btn-sm btn-outline-danger ms-3" @click="fetchPosts()">Retry</button>
      </div>
    </template>

    <!-- Active Family Hashtag Filter Stream Banner -->
    <div x-cloak x-show="activeHashtagFilter" class="card border-0 shadow-sm mb-4 p-3" style="border-radius: 14px; background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); border-left: 4px solid #4f46e5 !important;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill text-white px-3 py-1.5 shadow-xs" style="background: #4f46e5; font-size: 0.88rem; letter-spacing: 0.3px;">
            <i class="bi bi-hash me-0.5"></i><span x-text="activeHashtagFilter"></span>
          </span>
          <span class="text-dark fw-bold small" style="font-size: 0.9rem;">Family Memory Stream</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" @click="clearHashtagFilter()" style="font-size: 0.82rem; font-weight: 600;">
          <i class="bi bi-x-circle-fill"></i> Show All Posts
        </button>
      </div>
    </div>

    <!-- Trending Family Hashtags Carousel -->
    <template x-if="trendingHashtags && trendingHashtags.length > 0 && !activeHashtagFilter">
      <div class="trending-hashtags-tray card border-0 shadow-sm mb-4 p-3" style="border-radius: 14px; background-color: var(--card-bg);">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tags-fill text-primary" style="font-size: 1rem;"></i>
            <span class="fw-bold small text-dark" style="font-size: 0.88rem;">Family Topics &amp; Milestones</span>
          </div>
          <span class="text-muted small" style="font-size: 0.75rem;">Click to filter</span>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <template x-for="item in trendingHashtags" :key="item.tag">
            <button type="button" 
                    class="btn btn-sm family-hashtag-pill d-inline-flex align-items-center gap-1 shadow-xs" 
                    @click="filterByHashtag(item.tag)"
                    :title="'View family memories tagged #' + item.tag">
              <span class="fw-semibold">#<span x-text="item.tag"></span></span>
              <span class="badge rounded-pill bg-white text-primary px-1.5 py-0.5 ms-1" style="font-size: 0.68rem;" x-text="item.count"></span>
            </button>
          </template>
        </div>
      </div>
    </template>

    <!-- Skeleton Loading State -->
    <template x-if="isLoading">
      <div>
        <div class="skeleton-post shadow-sm mb-4" style="background: white; border-radius: 16px; padding: 20px; text-align: left; animation: pulse 1.5s infinite ease-in-out;">
          <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; margin-right: 15px;"></div>
            <div style="flex-grow: 1;">
              <div style="height: 12px; background: #e2e8f0; border-radius: 4px; width: 40%; margin-bottom: 8px;"></div>
              <div style="height: 10px; background: #e2e8f0; border-radius: 4px; width: 20%;"></div>
            </div>
          </div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 100%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 85%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 60%;"></div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <template x-if="!isLoading && posts.length === 0 && !errorMessage">
      <div class="card border-0 shadow-sm p-5 text-center mb-4" style="border-radius: 16px; background: white;">
        <div class="mb-3">
          <i class="bi bi-chat-heart text-muted" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold text-dark">No family posts yet</h5>
        <p class="text-muted mb-0">Share the first memory or event with your family above!</p>
      </div>
    </template>

    <!-- Posts Loop -->
    <div id="postIt" class="postIt">
      <template x-for="post in posts" :key="post.post_no">
        @include('components.feed.post-card')
      </template>
    </div>
    
    <!-- Premium Alpine Lightbox Modal with Drag-to-Dismiss Gesture -->
    <div x-cloak x-show="lightboxOpen" 
         class="lightbox-overlay"
         :style="getLightboxOverlayStyle()"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @keydown.escape.window="closeLightbox()"
         @keydown.right.window="nextLightboxImage()"
         @keydown.left.window="prevLightboxImage()"
         @touchstart.passive="onLightboxTouchStart($event)"
         @touchmove="onLightboxTouchMove($event)"
         @touchend="onLightboxTouchEnd($event)"
         @touchcancel="onLightboxTouchCancel()"
         @click="closeLightbox()">
        
        <!-- Close Button (High contrast, 48px touch target) -->
        <button type="button" 
                @click.stop="closeLightbox()" 
                aria-label="Close photo view"
                style="position: absolute; top: 18px; right: 20px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 46px; height: 46px; border-radius: 50%; font-size: 26px; line-height: 1; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.2s ease; z-index: 100002; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-x-lg" style="font-size: 1.15rem;"></i>
        </button>
        
        <!-- Previous Button -->
        <button type="button" 
                @click.stop.prevent="prevLightboxImage()" 
                x-show="lightboxImages && lightboxImages.length > 1" 
                aria-label="Previous photo"
                style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 100002; user-select: none; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-chevron-left"></i>
        </button>

        <!-- Main Image Container with Drag Translation -->
        <div class="lightbox-image-container" 
             :style="getLightboxImageStyle()"
             @click.stop>
            
            <!-- Loading Spinner -->
            <div x-show="lightboxLoading" 
                 class="spinner-border text-light position-absolute" 
                 style="width: 3rem; height: 3rem; z-index: 100000;" 
                 role="status">
                <span class="visually-hidden">Loading photo...</span>
            </div>

            <!-- Image View -->
            <img :src="getPostImageUrl(lightboxImages[lightboxIndex])" 
                 x-on:load="lightboxLoading = false; lightboxError = false;"
                 x-on:error="lightboxLoading = false; lightboxError = true;"
                 x-show="!lightboxError"
                 style="max-width: 100%; max-height: 84vh; border-radius: 8px; object-fit: contain; box-shadow: 0 25px 60px rgba(0,0,0,0.7); transition: transform 0.2s ease;" 
                 alt="Enlarged family photo"
                 @click.stop>

            <!-- Fallback if Image Fails to Load -->
            <div x-show="lightboxError" 
                 class="text-center text-white p-4" 
                 style="background: rgba(30,30,30,0.85); border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);">
                <i class="bi bi-image text-secondary mb-2" style="font-size: 2.5rem; display: block;"></i>
                <p class="mb-2 fw-semibold">Photo unavailable</p>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" @click.stop="closeLightbox()">Close</button>
            </div>
        </div>
        
        <!-- Next Button -->
        <button type="button" 
                @click.stop.prevent="nextLightboxImage()" 
                x-show="lightboxImages && lightboxImages.length > 1" 
                aria-label="Next photo"
                style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 100002; user-select: none; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-chevron-right"></i>
        </button>
        
        <!-- Image Counter Badge -->
        <div x-show="lightboxImages && lightboxImages.length > 1" 
             style="position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%); color: #ffffff; font-size: 14px; font-weight: 600; background: rgba(0,0,0,0.75); padding: 6px 20px; border-radius: 30px; letter-spacing: 0.5px; box-shadow: 0 4px 14px rgba(0,0,0,0.5); z-index: 100002; border: 1px solid rgba(255,255,255,0.2);" 
             x-text="(lightboxIndex + 1) + ' / ' + lightboxImages.length"></div>
    </div>
  </div>

  <!-- The Modal Structure (Hidden by default) for the post image modal -->
  @includeIf('member.modals.postImg')


  <style>
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }
  </style>
</div>
