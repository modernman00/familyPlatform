<!-- Middle Column - Feed -->
<style>
  .highlighted-post, .highlighted-comment {
    animation: postHighlightPulse 2.5s ease-out;
    border-radius: 8px;
  }
  @keyframes postHighlightPulse {
    0% { box-shadow: 0 0 0 4px rgba(163,22,33, 0.65); }
    100% { box-shadow: 0 0 0 0 rgba(163,22,33, 0); }
  }
</style>
<div class="feed-column">
  <!-- Proactive Wedding Anniversary Prompt Banner (World-Class Milestone Onboarding) -->
  @if(!empty($data['needs_wedding_date_prompt']))
  <div class="card mb-4 border-0 shadow-sm" id="weddingAnniversaryPromptCard" style="border-radius: 18px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%); border: 1.5px solid #f59e0b !important; overflow: hidden;">
    <div class="card-body p-4">
      <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 52px; height: 52px; background: #ffffff; color: #b8202c; font-size: 1.5rem; border: 2px solid #fbbf24;">
            💍
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h6 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem; letter-spacing: -0.01em;">
                Celebrate Your Marriage & Special Day
              </h6>
              <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                ANNIVERSARY MILESTONES
              </span>
            </div>
            <p class="mb-3 text-dark" style="font-size: 0.88rem; line-height: 1.5; opacity: 0.9;">
              We noticed you are married! When is your wedding anniversary? Add your special date so your family network can celebrate your milestones, send personalised wishes, and receive memorable flashbacks every year.
            </p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <label for="bannerWeddingDate" class="visually-hidden">Wedding Date</label>
                <input type="date" 
                       id="bannerWeddingDate" 
                       name="banner_wedding_date" 
                       max="{{ date('Y-m-d') }}" 
                       class="form-control" 
                       style="max-width: 185px; min-height: 44px; border-radius: 10px; border: 1.5px solid #d97706; background-color: #ffffff; color: #1e293b; font-weight: 500;">
                <button type="button" 
                        id="btnSaveBannerAnniversary" 
                        class="btn text-white fw-bold rounded-pill px-3 py-2 d-flex align-items-center gap-1 shadow-sm" 
                        style="background-color: #a31621; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;" 
                        data-spin="true" 
                        onclick="saveWeddingAnniversaryFromBanner()">
                  <i class="bi bi-heart-fill"></i>
                  <span>Save Anniversary</span>
                </button>
              </div>
              <button type="button" 
                      class="btn btn-link text-muted text-decoration-none small px-2 py-2" 
                      style="min-height: 44px; font-size: 0.82rem;" 
                      onclick="dismissAnniversaryBanner()">
                Remind me later
              </button>
            </div>
            <div id="bannerAnniversaryError" class="text-danger small mt-2" style="display: none;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    (function() {
      if (sessionStorage.getItem('dismissed_anniversary_prompt') === '1') {
        var card = document.getElementById('weddingAnniversaryPromptCard');
        if (card) card.style.display = 'none';
      }
    })();

    function dismissAnniversaryBanner() {
      var card = document.getElementById('weddingAnniversaryPromptCard');
      if (card) {
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';
        setTimeout(function() { card.remove(); }, 400);
      }
      sessionStorage.setItem('dismissed_anniversary_prompt', '1');
    }

    async function saveWeddingAnniversaryFromBanner() {
      var input = document.getElementById('bannerWeddingDate');
      var errBox = document.getElementById('bannerAnniversaryError');
      var btn = document.getElementById('btnSaveBannerAnniversary');
      if (!input || !btn) return;

      var val = (input.value || '').trim();
      if (!val) {
        if (errBox) {
          errBox.textContent = 'Please choose your wedding date to continue.';
          errBox.style.display = 'block';
        }
        input.focus();
        return;
      }
      if (errBox) errBox.style.display = 'none';

      if (window.ScoreTenantSpinner) {
        ScoreTenantSpinner.start(btn);
      } else {
        btn.disabled = true;
      }

      var csrfToken = document.querySelector('input[name="token"]')?.value 
                   || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || @json($_SESSION['token'] ?? '');

      try {
        var resp = await fetch('/member/profilePage/updateAnniversary', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
          },
          body: JSON.stringify({
            wedding_date: val,
            token: csrfToken
          })
        });

        var res = await resp.json().catch(function() { return {}; });

        if (resp.ok) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Anniversary saved! 🎉',
              text: res.message || 'Your family network will now celebrate your special day every year.',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true
            });
          }
          dismissAnniversaryBanner();
          setTimeout(function() {
            window.location.reload();
          }, 1200);
        } else {
          var msg = res.message || 'Failed to save wedding anniversary. Please try again.';
          if (errBox) {
            errBox.textContent = msg;
            errBox.style.display = 'block';
          }
        }
      } catch (e) {
        console.error('Error saving anniversary:', e);
        if (errBox) {
          errBox.textContent = 'A network error occurred. Please try again.';
          errBox.style.display = 'block';
        }
      } finally {
        if (window.ScoreTenantSpinner) {
          ScoreTenantSpinner.stop(btn);
        } else {
          btn.disabled = false;
        }
      }
    }
  </script>
  @endif
  @if(!empty($pendingFamilyRequests))
    @foreach($pendingFamilyRequests as $joinReq)
      <div class="card mb-4 border-0 shadow-sm family-join-request-card" id="familyJoinReq-{{ $joinReq['no'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #fdf5f5 0%, #fbefef 100%); border-left: 5px solid #a31621 !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #f3d9db; color: #8a1019; font-size: 1.3rem;">
              <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                👋 <strong>{{ htmlspecialchars($joinReq['requesterName'] ?? 'New Member') }}</strong> {{ __('family.wants_to_join', 'wants to join your :code family network', ['code' => htmlspecialchars($joinReq['family_code'] ?? '')]) }}
              </h6>
              <p class="mb-0 text-muted" style="font-size: 0.88rem;">
                {{ __('family.join_desc', 'Requested to connect on FamilyPlatform. Approve to grant immediate access to your family tree and updates.') }}
              </p>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold" 
                    id="btnApproveJoin-{{ $joinReq['no'] }}"
                    style="background-color: #a31621; min-height: 44px;" 
                    onclick="handleJoinRequestAction({{ (int)$joinReq['no'] }}, '{{ htmlspecialchars($joinReq['approval_token'] ?? '') }}', 'approve')">
              <i class="bi bi-check-lg me-1"></i> {{ __('family.approve_request', 'Approve Request') }}
            </button>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDenyJoin-{{ $joinReq['no'] }}"
                    style="min-height: 44px;"
                    onclick="handleJoinRequestAction({{ (int)$joinReq['no'] }}, '{{ htmlspecialchars($joinReq['approval_token'] ?? '') }}', 'deny')">
              <i class="bi bi-x-lg me-1"></i> {{ __('family.deny', 'Deny') }}
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
      <div class="card mb-4 border-0 shadow-sm family-migration-nudge-card" id="migrationNudgeCard-{{ $nudge['id'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #f5f3ff 0%, #fbefef 100%); border-left: 5px solid #b8202c !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #ddd6fe; color: #6d28d9; font-size: 1.3rem;">
              <i class="bi bi-send-check-fill"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                🌟 <strong>{{ htmlspecialchars($nudge['requesterName'] ?? 'A family relative') }}</strong> {{ __('family.invited_to_migrate', 'invited you to migrate to the :code family network!', ['code' => '#' . htmlspecialchars($nudge['new_family_code'] ?? '')]) }}
              </h6>
              <p class="mb-1 text-muted" style="font-size: 0.88rem;">
                {{ __('family.join_desc', 'Requested to connect on FamilyPlatform. Approve to grant immediate access to your family tree and updates.') }}
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
                    style="background-color: #8a1019; min-height: 44px;" 
                    onclick="handleMigrationNudgeAction({{ (int)$nudge['id'] }}, '{{ htmlspecialchars($nudge['token'] ?? '') }}', 'accept')">
              <i class="bi bi-check-circle-fill me-1"></i> {{ __('family.accept_migrate', 'Accept & Migrate') }}
            </button>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDeclineNudge-{{ $nudge['id'] }}"
                    style="min-height: 44px;"
                    onclick="handleMigrationNudgeAction({{ (int)$nudge['id'] }}, '{{ htmlspecialchars($nudge['token'] ?? '') }}', 'decline')">
              <i class="bi bi-x-lg me-1"></i> {{ __('family.decline', 'Decline') }}
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
      <div class="card mb-4 border-0 shadow-sm family-migration-request-card" id="migrationReqCard-{{ $migReq['id'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%); border-left: 5px solid #a31621 !important;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #bfdbfe; color: #8a1019; font-size: 1.3rem;">
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
                    style="background-color: #a31621; min-height: 44px;" 
                    onclick="handleMigrationRequestAction({{ (int)$migReq['id'] }}, '{{ htmlspecialchars($migReq['token'] ?? '') }}', 'approve')">
              <i class="bi bi-check-lg me-1"></i> {{ __('family.approve_request', 'Approve Migration') }}
            </button>
            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold" 
                    id="btnDenyMigReq-{{ $migReq['id'] }}"
                    style="min-height: 44px;"
                    onclick="handleMigrationRequestAction({{ (int)$migReq['id'] }}, '{{ htmlspecialchars($migReq['token'] ?? '') }}', 'deny')">
              <i class="bi bi-x-lg me-1"></i> {{ __('family.deny', 'Deny') }}
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
  <div class="card mb-4 border-0 shadow-sm unclaimed-match-card" id="unclaimedMatchBanner" style="border-radius: 16px; background: linear-gradient(135deg, #fdf5f5 0%, #f0fdf4 100%); border-left: 5px solid #a31621 !important;">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #f5dede; color: #a31621; font-size: 1.4rem;">
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
          <i class="bi bi-check2-circle me-1"></i> {{ __('family.thats_me', "Yes, that's me!") }}
        </button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2" onclick="dismissFamilyNode({{ (int)$unclaimedMatch['node_id'] }})">
          {{ __('family.not_now', 'Not now') }}
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
        <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="profile" class="rounded-circle flex-shrink-0 composer-avatar-img" width="44" height="44" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; max-width: 44px; max-height: 44px; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 50%;">

        <div role="button" tabindex="0" class="composer-prompt flex-grow-1 rounded-pill px-3 py-3 text-truncate" data-bs-toggle="modal" data-bs-target="#postModal"
          style="background-color: #f1f5f9; border: 1.5px solid #cbd5e1; cursor: text; transition: all 0.2s ease-in-out;">
          <span style="color: #64748b; font-size: 0.95rem; font-weight: 500;">{{ __('feed.share_prompt', 'Share a moment with your family...') }}</span>
        </div>
      </div>

      <style>
        .composer-icon-btn {
          width: 42px;
          height: 42px;
          min-width: 42px;
          min-height: 42px;
          border-radius: 12px !important;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          padding: 0;
          transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .composer-icon-btn:hover {
          transform: translateY(-2px);
          filter: brightness(1.08);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }
        .composer-icon-btn:active {
          transform: scale(0.94);
        }
        body.dark-mode .composer-icon-btn,
        [data-bs-theme="dark"] .composer-icon-btn {
          border-color: rgba(255, 255, 255, 0.12) !important;
        }
      </style>
      <div class="composer-quick-actions d-flex align-items-center justify-content-between mt-3 pt-3 gap-2" style="border-top: 1px solid var(--border-color, #e2e8f0);">
        <div class="d-flex align-items-center justify-content-between justify-content-sm-start gap-2 flex-grow-1 flex-sm-grow-0">
          <!-- 1. Then & Now -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  data-bs-toggle="modal" data-bs-target="#postModal"
                  onclick="setTimeout(function () { if (window.__openThenNow) window.__openThenNow(''); }, 0)"
                  title="Then &amp; Now - Compare milestone photos across years"
                  aria-label="{{ __('feed.then_now', 'Then & Now photo comparison') }}"
                  style="background: rgba(163, 22, 33, 0.1); color: #a31621; border: 1px solid rgba(163, 22, 33, 0.2);">
            <i class="bi bi-hourglass-split" style="font-size: 1.15rem;"></i>
          </button>
          
          <!-- 2. Photo -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  data-bs-toggle="modal" data-bs-target="#postModal"
                  title="Upload Photos - Add memories from your device or camera"
                  aria-label="{{ __('feed.photo', 'Add a photo to post') }}"
                  style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2);">
            <i class="bi bi-camera-fill" style="font-size: 1.15rem;"></i>
          </button>

          <!-- 3. Video -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  data-bs-toggle="modal" data-bs-target="#postModal"
                  title="Upload Video - Embed or share family video clips"
                  aria-label="{{ __('feed.video', 'Add a video to post') }}"
                  style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.2);">
            <i class="bi bi-play-btn-fill" style="font-size: 1.15rem;"></i>
          </button>

          <!-- 4. Announcement -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  data-bs-toggle="modal" data-bs-target="#createAnnouncementModal"
                  title="Family Announcement - Broadcast pinned notices to all relatives"
                  aria-label="{{ __('feed.announcement', 'Broadcast family announcement') }}"
                  style="background: rgba(217, 119, 6, 0.1); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.2);">
            <i class="bi bi-megaphone-fill" style="font-size: 1.15rem;"></i>
          </button>

          <!-- 5. Journal -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  onclick="window.location.href='/familyJournal/create'"
                  title="Family Journal - Write memoirs, stories, and chronicles"
                  aria-label="{{ __('feed.journal', 'Write family journal chronicle') }}"
                  style="background: rgba(5, 150, 105, 0.1); color: #059669; border: 1px solid rgba(5, 150, 105, 0.2);">
            <i class="bi bi-journal-bookmark-fill" style="font-size: 1.15rem;"></i>
          </button>

          <!-- 6. Poll -->
          <button type="button" class="btn composer-icon-btn shadow-xs"
                  data-bs-toggle="modal" data-bs-target="#postModal"
                  title="Family Poll - Gather opinions and vote on dates or plans"
                  aria-label="{{ __('feed.poll', 'Create a poll') }}"
                  style="background: rgba(234, 88, 12, 0.1); color: #ea580c; border: 1px solid rgba(234, 88, 12, 0.2);">
            <i class="bi bi-bar-chart-fill" style="font-size: 1.15rem;"></i>
          </button>
        </div>

        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold d-none d-sm-inline-flex align-items-center gap-1.5 shadow-xs flex-shrink-0"
                data-bs-toggle="modal" data-bs-target="#postModal"
                title="Create Post - Share stories, photos, and milestones with family"
                style="background-color: var(--primary-color, #a31621); color: #ffffff !important; border: none; min-height: 40px; white-space: nowrap;">
          <i class="bi bi-plus-lg"></i> <span>{{ __('feed.post_btn', 'Post') }}</span>
        </button>
      </div>
    </div>
  </div>


  <!-- Kinship Radar: Mobile-Only (visible < 992px, hidden on desktop where sidebar shows it) -->
  @if(!empty($suggestedKin))
  <div class="d-lg-none">
    @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => 'kinshipRadarWidgetMobile'])
  </div>
  @endif

  <!-- Reactive Alpine.js Feed -->
  <div x-data="profileFeed({ famCode: '{{ $data['famCode'] ?? '' }}', userId: '{{ $data['id'] ?? '' }}', familyRoster: {{ json_encode($data['familyRoster'] ?? []) }} })" class="feed-posts-container">
    <!-- Error Alert if loading fails -->
    <template x-if="errorMessage">
      <div class="alert alert-danger shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span x-text="errorMessage"></span>
        <button class="btn btn-sm btn-outline-danger ms-3" @click="fetchPosts()">Retry</button>
      </div>
    </template>

    <!-- Active Family Hashtag Filter Stream Banner -->
    <div x-cloak x-show="activeHashtagFilter" class="card border-0 shadow-sm mb-4 p-3" style="border-radius: 14px; background: linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%); border-left: 4px solid #a31621 !important;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill text-white px-3 py-1.5 shadow-xs" style="background: #a31621; font-size: 0.88rem; letter-spacing: 0.3px;">
            <i class="bi bi-hash me-0.5"></i><span x-text="activeHashtagFilter"></span>
          </span>
          <span class="text-dark fw-bold small" style="font-size: 0.9rem;">{{ __('feed.memory_stream', 'Family Memory Stream') }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" @click="clearHashtagFilter()" style="font-size: 0.82rem; font-weight: 600;">
          <i class="bi bi-x-circle-fill"></i> {{ __('feed.show_all_posts', 'Show All Posts') }}
        </button>
      </div>
    </div>

    <!-- Active Tagged With You Stream Banner -->
    <div x-cloak x-show="activeStreamFilter === 'tagged'" class="card border-0 shadow-sm mb-4 p-3" style="border-radius: 14px; background: linear-gradient(135deg, #fdf5f5 0%, #f5dede 100%); border-left: 4px solid #a31621 !important;">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill text-white px-3 py-1.5 shadow-xs" style="background: #a31621; font-size: 0.88rem; letter-spacing: 0.3px;">
            <i class="bi bi-tag-fill me-1"></i>{{ __('feed.tagged_with_you', 'Tagged With You') }}
          </span>
          <span class="text-dark fw-bold small" style="font-size: 0.9rem;">{{ __('feed.living_scrapbook', 'Your Living Heritage Scrapbook') }}</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" @click="setStreamFilter('all')" style="font-size: 0.82rem; font-weight: 600;">
          <i class="bi bi-arrow-left-circle-fill"></i> {{ __('feed.show_all_memories', 'Show All Memories') }}
        </button>
      </div>
    </div>

    <!-- Feed Stream Selector Tabs (All Posts vs Tagged With You) -->
    <div class="feed-tabs-container mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="btn-group p-1 rounded-pill bg-light border shadow-2xs" role="group" aria-label="Feed memory stream tabs">
        <button type="button" 
                class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold transition-all"
                :class="activeStreamFilter === 'all' ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'"
                @click="setStreamFilter('all')"
                style="font-size: 0.82rem; white-space: nowrap; min-height: 38px;">
          <i class="bi bi-clock-history me-1"></i>{{ __('feed.all_memories', 'All Family Memories') }}
        </button>
        <button type="button" 
                class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold transition-all d-inline-flex align-items-center gap-1.5"
                :class="activeStreamFilter === 'tagged' ? 'btn-primary text-white shadow-xs' : 'text-muted border-0 bg-transparent'"
                @click="setStreamFilter('tagged')"
                style="font-size: 0.82rem; white-space: nowrap; min-height: 38px;">
          <i class="bi bi-tag-fill me-0.5"></i>{{ __('feed.tagged_with_you', 'Tagged With You') }}
          <span class="badge rounded-pill" :class="activeStreamFilter === 'tagged' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark'" style="font-size: 0.68rem;" x-text="taggedPostsCount"></span>
        </button>
      </div>
      <template x-if="activeStreamFilter === 'tagged'">
        <span class="small text-muted" style="font-size: 0.75rem;">{{ __('feed.showing_tagged', 'Showing memories featuring you') }}</span>
      </template>
    </div>

    <!-- Trending Family Hashtags Carousel -->
    <template x-if="trendingHashtags && trendingHashtags.length > 0 && !activeHashtagFilter">
      <div class="trending-hashtags-tray card border-0 shadow-sm mb-4 p-3" style="border-radius: 14px; background-color: var(--card-bg);">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tags-fill text-primary" style="font-size: 1rem;"></i>
            <span class="fw-bold small text-dark" style="font-size: 0.88rem;">{{ __('feed.topics_milestones', 'Family Topics & Milestones') }}</span>
          </div>
          <span class="text-muted small" style="font-size: 0.75rem;">{{ __('feed.click_to_filter', 'Click to filter') }}</span>
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
          <i class="bi text-muted" :class="activeStreamFilter === 'tagged' ? 'bi-tags-fill' : 'bi-chat-heart'" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold text-dark" x-text="activeStreamFilter === 'tagged' ? (window.__ ? window.__('feed.no_tagged_yet', 'No tagged memories yet') : 'No tagged memories yet') : (window.__ ? window.__('feed.no_posts_yet', 'No family posts yet') : 'No family posts yet')"></h5>
        <p class="text-muted mb-0" x-text="activeStreamFilter === 'tagged' ? (window.__ ? window.__('feed.no_tagged_desc', 'When relatives tag you in stories or milestone photos, they will appear in this personal scrapbook.') : 'When relatives tag you in stories or milestone photos, they will appear in this personal scrapbook.') : (window.__ ? window.__('feed.no_posts_desc', 'Share the first memory or event with your family above!') : 'Share the first memory or event with your family above!')"></p>
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

    <!-- Author Viewers Roster Modal (Post & Comment Views) -->
    <div x-cloak x-show="isViewersModalOpen"
         class="viewers-modal-overlay"
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 10500; display: flex; align-items: center; justify-content: center; padding: 16px;"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @keydown.escape.window="closeViewersModal()"
         @click.self="closeViewersModal()"
         role="dialog"
         aria-modal="true"
         :aria-label="viewersType === 'comment' ? 'Comment Viewers' : 'Post Viewers'">
        
        <div class="card border-0 shadow-lg"
             style="width: 100%; max-width: 480px; max-height: 85vh; border-radius: 20px; overflow: hidden; display: flex; flex-direction: column; background: var(--card-bg, #ffffff);">
            
            <!-- Modal Header -->
            <div class="d-flex align-items-center justify-content-between p-3 px-4 border-bottom"
                 style="background: linear-gradient(135deg, #a31621 0%, #801018 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.18); font-size: 1.15rem; color: #ffffff;">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white" style="font-size: 1.05rem;"
                            x-text="viewersType === 'comment' ? 'Comment Viewers' : 'Post Viewers'"></h6>
                        <small style="color: #fce79a; font-size: 0.78rem;"
                               x-text="(viewersList.length || 0) + (viewersList.length === 1 ? ' family member seen' : ' family members seen')"></small>
                    </div>
                </div>
                <button type="button" 
                        class="btn-close btn-close-white" 
                        @click="closeViewersModal()" 
                        aria-label="Close viewers modal"
                        style="opacity: 0.9; min-width: 44px; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;"></button>
            </div>

            <!-- Search Filter Bar (if more than 3 viewers) -->
            <div x-show="viewersList.length > 3" class="p-3 border-bottom bg-light">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           placeholder="Filter family members..." 
                           x-model="viewersSearchQuery"
                           aria-label="Filter family members"
                           style="box-shadow: none;">
                </div>
            </div>

            <!-- Modal Body (Scrollable Roster) -->
            <div class="p-3 overflow-y-auto" style="max-height: 55vh; min-height: 180px;">
                <!-- Loading State Skeleton -->
                <template x-if="viewersLoading">
                    <div class="d-flex flex-column gap-3 py-2">
                        <template x-for="i in [1, 2, 3]" :key="i">
                            <div class="d-flex align-items-center gap-3 animate-pulse">
                                <div class="rounded-circle bg-secondary" style="width: 44px; height: 44px; opacity: 0.25;"></div>
                                <div class="flex-grow-1">
                                    <div class="bg-secondary rounded mb-1.5" style="height: 14px; width: 55%; opacity: 0.25;"></div>
                                    <div class="bg-secondary rounded" style="height: 11px; width: 35%; opacity: 0.18;"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Empty State (No viewers yet) -->
                <template x-if="!viewersLoading && viewersList.length === 0">
                    <div class="text-center py-5 px-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 58px; height: 58px; background: #f8fafc; color: #94a3b8; font-size: 1.75rem;">
                            <i class="bi bi-eye-slash"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">No views recorded yet</h6>
                        <p class="text-muted small mb-0"
                           x-text="viewersType === 'comment' ? 'When family members view your comment, they will appear here.' : 'When family members view your post, they will appear here.'"></p>
                    </div>
                </template>

                <!-- Filtered No Match -->
                <template x-if="!viewersLoading && viewersList.length > 0 && getFilteredViewers().length === 0">
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-search me-1"></i> No family members match "<span x-text="viewersSearchQuery"></span>"
                    </div>
                </template>

                <!-- Viewers List -->
                <template x-if="!viewersLoading && getFilteredViewers().length > 0">
                    <div class="d-flex flex-column gap-2">
                        <template x-for="viewer in getFilteredViewers()" :key="viewer.user_id">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 hover-bg"
                                 style="transition: background-color 0.15s ease;">
                                <div class="d-flex align-items-center gap-2.5 min-w-0">
                                    <img :src="getViewerAvatar(viewer)" 
                                         alt="Avatar" 
                                         class="rounded-circle flex-shrink-0" 
                                         width="42" 
                                         height="42" 
                                         style="width: 42px; height: 42px; min-width: 42px; min-height: 42px; aspect-ratio: 1 / 1; object-fit: cover; border: 1px solid #e2e8f0; border-radius: 50%;"
                                         x-on:error="$event.target.src = '/public/avatar/avatarM.png'">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.9rem;"
                                             x-text="(viewer.firstName || '') + ' ' + (viewer.lastName || '')"></div>
                                        <div class="text-muted" style="font-size: 0.76rem;"
                                             x-text="formatViewerTime(viewer.updated_at || viewer.created_at)"></div>
                                    </div>
                                </div>
                                <template x-if="viewer.view_count > 1">
                                    <span class="badge rounded-pill bg-light text-secondary border flex-shrink-0 ms-2"
                                          style="font-size: 0.72rem; padding: 4px 8px;"
                                          x-text="viewer.view_count + ' views'"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-2 px-3 border-top bg-light d-flex justify-content-end">
                <button type="button" 
                        class="btn btn-sm btn-secondary rounded-pill px-3" 
                        style="min-height: 44px; min-width: 80px;"
                        @click="closeViewersModal()">
                    Close
                </button>
            </div>
        </div>
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
