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

  /* Wedding Anniversary Prompt Banner - Light Mode Default */
  .wedding-anniversary-prompt-card {
    border-radius: 18px !important;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%) !important;
    border: 1.5px solid #f59e0b !important;
    overflow: hidden;
  }
  .wedding-anniversary-prompt-card .anniversary-icon-circle {
    width: 52px;
    height: 52px;
    background: #ffffff !important;
    color: #b8202c !important;
    font-size: 1.5rem;
    border: 2px solid #fbbf24 !important;
  }
  .wedding-anniversary-prompt-card .anniversary-title {
    font-size: 1.05rem;
    letter-spacing: -0.01em;
    color: #1e293b !important;
  }
  .wedding-anniversary-prompt-card .anniversary-desc {
    font-size: 0.88rem;
    line-height: 1.5;
    color: #334155 !important;
  }
  .wedding-anniversary-prompt-card .anniversary-date-input {
    background-color: #ffffff !important;
    color: #1e293b !important;
    border: 1.5px solid #d97706 !important;
    color-scheme: light;
  }
  .wedding-anniversary-prompt-card .anniversary-dismiss-btn {
    color: #92400e !important;
  }
  .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover {
    color: #78350f !important;
    text-decoration: underline !important;
  }

  /* Wedding Anniversary Prompt Banner - Dark Mode (WCAG AA/AAA Guaranteed Contrast) */
  body.dark-mode .wedding-anniversary-prompt-card,
  body.dark-theme .wedding-anniversary-prompt-card,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card,
  html.dark .wedding-anniversary-prompt-card,
  html.dark-mode .wedding-anniversary-prompt-card {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.16) 0%, #1e293b 65%, #1e293b 100%) !important;
    background-color: #1e293b !important;
    border: 1.5px solid rgba(245, 158, 11, 0.5) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45), 0 0 16px rgba(245, 158, 11, 0.12) !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-icon-circle,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-icon-circle,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-icon-circle,
  html.dark .wedding-anniversary-prompt-card .anniversary-icon-circle,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-icon-circle {
    background: rgba(245, 158, 11, 0.2) !important;
    color: #fbbf24 !important;
    border: 2px solid rgba(251, 191, 36, 0.65) !important;
    box-shadow: 0 0 14px rgba(245, 158, 11, 0.25) !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-title,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-title,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-title,
  html.dark .wedding-anniversary-prompt-card .anniversary-title,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-title {
    color: #f8fafc !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-desc,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-desc,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-desc,
  html.dark .wedding-anniversary-prompt-card .anniversary-desc,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-desc {
    color: #cbd5e1 !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-date-input,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-date-input,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-date-input,
  html.dark .wedding-anniversary-prompt-card .anniversary-date-input,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-date-input {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border: 1.5px solid rgba(245, 158, 11, 0.6) !important;
    color-scheme: dark !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-date-input::-webkit-calendar-picker-indicator,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-date-input::-webkit-calendar-picker-indicator,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-date-input::-webkit-calendar-picker-indicator,
  html.dark .wedding-anniversary-prompt-card .anniversary-date-input::-webkit-calendar-picker-indicator,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-date-input::-webkit-calendar-picker-indicator {
    filter: invert(0.85) !important;
    cursor: pointer;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-dismiss-btn,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-dismiss-btn,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-dismiss-btn,
  html.dark .wedding-anniversary-prompt-card .anniversary-dismiss-btn,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-dismiss-btn {
    color: #94a3b8 !important;
  }
  body.dark-mode .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover,
  body.dark-theme .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover,
  [data-bs-theme="dark"] .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover,
  html.dark .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover,
  html.dark-mode .wedding-anniversary-prompt-card .anniversary-dismiss-btn:hover {
    color: #f1f5f9 !important;
  }

  /* Gift Wishlist Feed Card WCAG AA Light & Dark Contrast Tokens */
  .fp-fw-badge-emerald {
    background: rgba(16, 185, 129, 0.15);
    color: #065f46;
    font-weight: 700;
  }
  .fp-fw-index-pill {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e;
  }
  body.dark-mode .fp-fw-badge-emerald,
  body.dark-theme .fp-fw-badge-emerald,
  [data-bs-theme="dark"] .fp-fw-badge-emerald,
  html.dark .fp-fw-badge-emerald,
  html.dark-mode .fp-fw-badge-emerald {
    background: rgba(16, 185, 129, 0.22);
    color: #6ee7b7;
  }
  body.dark-mode .fp-fw-index-pill,
  body.dark-theme .fp-fw-index-pill,
  [data-bs-theme="dark"] .fp-fw-index-pill,
  html.dark .fp-fw-index-pill,
  html.dark-mode .fp-fw-index-pill {
    background: rgba(251, 191, 36, 0.22);
    color: #fde68a;
  }

  /* Family Migration & Unclaimed Match Cards - Dark Mode Surface & Contrast Overrides */
  body.dark-mode .family-migration-request-card,
  body.dark-theme .family-migration-request-card,
  [data-bs-theme="dark"] .family-migration-request-card,
  html.dark .family-migration-request-card,
  html.dark-mode .family-migration-request-card {
    background: linear-gradient(135deg, rgba(255, 77, 97, 0.14) 0%, #141418 75%) !important;
    border-left-color: #ff4d61 !important;
    color: #f8fafc !important;
  }
  body.dark-mode .family-migration-request-card .mig-req-icon,
  body.dark-theme .family-migration-request-card .mig-req-icon,
  [data-bs-theme="dark"] .family-migration-request-card .mig-req-icon,
  html.dark .family-migration-request-card .mig-req-icon,
  html.dark-mode .family-migration-request-card .mig-req-icon {
    background: rgba(255, 77, 97, 0.2) !important;
    color: #ff8fa3 !important;
  }
  body.dark-mode .unclaimed-match-card,
  body.dark-theme .unclaimed-match-card,
  [data-bs-theme="dark"] .unclaimed-match-card,
  html.dark .unclaimed-match-card,
  html.dark-mode .unclaimed-match-card {
    background: linear-gradient(135deg, rgba(255, 77, 97, 0.12) 0%, rgba(16, 185, 129, 0.12) 100%) !important;
    background-color: #141418 !important;
    border-left-color: #ff4d61 !important;
    color: #f8fafc !important;
  }
  body.dark-mode .unclaimed-match-card .unclaimed-match-icon,
  body.dark-theme .unclaimed-match-card .unclaimed-match-icon,
  [data-bs-theme="dark"] .unclaimed-match-card .unclaimed-match-icon,
  html.dark .unclaimed-match-card .unclaimed-match-icon,
  html.dark-mode .unclaimed-match-card .unclaimed-match-icon {
    background: rgba(255, 77, 97, 0.2) !important;
    color: #ff8fa3 !important;
  }
</style>
<div class="feed-column">
  @if(!empty($isPendingApproval))
    @include('member.includes.pendingApprovalHoldingCard')
  @else
  <!-- Proactive Wedding Anniversary Prompt Banner (World-Class Milestone Onboarding) -->
  @if(!empty($data['needs_wedding_date_prompt']))
  <div class="card mb-4 border-0 shadow-sm wedding-anniversary-prompt-card" id="weddingAnniversaryPromptCard">
    <div class="card-body p-4">
      <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 anniversary-icon-circle">
            💍
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
              <h6 class="fw-bold mb-0 anniversary-title">
                Celebrate Your Marriage & Special Day
              </h6>
              <span class="badge bg-danger text-white px-2 py-0.5 rounded-pill" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                ANNIVERSARY MILESTONES
              </span>
            </div>
            <p class="mb-3 anniversary-desc">
              We noticed you are married! When is your wedding anniversary? Add your special date so your family network can celebrate your milestones, send personalised wishes, and receive memorable flashbacks every year.
            </p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="d-flex align-items-start gap-2">
                <div class="d-flex flex-column">
                  <label for="bannerWeddingDate" class="visually-hidden">Wedding Date</label>
                  <input type="date" 
                         id="bannerWeddingDate" 
                         name="banner_wedding_date" 
                         max="{{ date('Y-m-d', strtotime('+1 year')) }}" 
                         class="form-control anniversary-date-input" 
                         style="max-width: 195px; min-height: 44px; border-radius: 10px; font-weight: 500;"
                         placeholder="DD/MM/YYYY"
                         aria-label="Wedding Date"
                         aria-describedby="bannerAnniversaryHelp bannerAnniversaryError">
                  <span id="bannerAnniversaryHelp" class="small text-muted mt-1" style="font-size: 0.76rem;">Select date or enter DD/MM/YYYY</span>
                </div>
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
                      class="btn btn-link text-decoration-none small px-2 py-2 anniversary-dismiss-btn" 
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
  <script nonce="{{ csp_nonce($nonce ?? null) }}">
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
      if (!val && input.validity && input.validity.badInput) {
        if (errBox) {
          errBox.textContent = 'Please choose a valid wedding date from the calendar or enter as DD/MM/YYYY.';
          errBox.style.display = 'block';
        }
        input.focus();
        return;
      }
      if (!val) {
        if (errBox) {
          errBox.textContent = 'Please select your wedding date from the calendar or enter DD/MM/YYYY to continue.';
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
        } else {
          var msg = res.message || 'Please enter a valid wedding date (e.g. DD/MM/YYYY or select from the calendar).';
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

  {{-- Connected Kin Requests Banner (Top of Feed — always mounted for live Pusher arrival) --}}
  @php
    $hasInitialRequests = !empty($requestData) && is_array($requestData) && count($requestData) > 0;
  @endphp
  <div id="kinshipRequest" class="d-flex flex-column gap-3 {{ $hasInitialRequests ? 'mb-4' : '' }}" data-approver-id="{{ htmlspecialchars((string)($_SESSION['id'] ?? ($data['id'] ?? ''))) }}">
    @if($hasInitialRequests)
      @foreach($requestData as $req)
        @php
          $reqFirstName = htmlspecialchars($req['firstName'] ?? 'Family Member');
          $reqLastName = htmlspecialchars($req['lastName'] ?? '');
          $reqFullName = trim("{$reqFirstName} {$reqLastName}");
          $reqId = htmlspecialchars((string)($req['id'] ?? ''));
          $reqFamCode = htmlspecialchars((string)($req['famCode'] ?? ''));
          $approverId = htmlspecialchars((string)($_SESSION['id'] ?? ($data['id'] ?? '')));
          $acceptUrl = "/member/request/{$reqId}/{$approverId}/50/{$reqFamCode}/pp";
          $declineUrl = "/member/request/{$reqId}/{$approverId}/10/{$reqFamCode}/pp";
          $relRole = htmlspecialchars((string)($req['relationship_role'] ?? 'Connected Kin'));
          $relLabel = htmlspecialchars((string)($req['relationship_full_label'] ?? $relRole));
          $relNote = htmlspecialchars((string)($req['relationship_note'] ?? ''));
          $rawImg = (string)($req['img'] ?? ($req['profilePics'] ?? 'avatarM.png'));
          $reqImg = !empty($rawImg) ? (str_starts_with($rawImg, '/') ? $rawImg : "/resources/images/profile/{$rawImg}") : '/resources/images/profile/avatarM.png';
        @endphp
        <div class="card border-0 shadow-sm kinship-request-card surface-light" data-surface="light" id="kinshipRequest-{{ $reqId }}" style="border-radius: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-left: 5px solid #059669 !important; color: #0f172a;">
          <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <img src="{{ $reqImg }}" alt="{{ $reqFullName }}" class="rounded-circle shadow-sm flex-shrink-0" style="width: 52px; height: 52px; object-fit: cover;">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <h3 class="fw-bold mb-0" style="font-size: 1.05rem; color: #0f172a !important;">
                    🤝 <strong style="color: #0f172a !important;">{{ $reqFullName }}</strong> <span style="color: #0f172a !important;">wants to connect as</span> <span class="badge rounded-pill bg-white border px-2.5 py-1" style="font-size: 0.8rem; border-color: #a7f3d0 !important; background-color: #ffffff !important; color: #065f46 !important;"><span class="fw-bold" style="color: #047857 !important;">{{ $relLabel }}</span></span>
                  </h3>
                </div>
                @if(!empty($relNote))
                  <p class="mb-1 fst-italic" style="font-size: 0.88rem; line-height: 1.4; color: #334155 !important;">
                    “{{ $relNote }}”
                  </p>
                @endif
                <div class="small" style="font-size: 0.78rem; color: #475569 !important;">
                  <i class="bi bi-shield-check text-success me-1"></i> Connected Kin: Both family trees remain 100% private to your family codes.
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a href="{{ $acceptUrl }}" class="btn btn-sm btn-success text-white rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center text-nowrap" style="min-height: 44px; text-decoration: none; white-space: nowrap; flex-shrink: 0;" title="Accept as Connected Kin">
                <i class="bi bi-check-lg me-1"></i> Accept Kinship
              </a>
              <a href="{{ $declineUrl }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center text-nowrap" style="min-height: 44px; text-decoration: none; white-space: nowrap; flex-shrink: 0;" title="Decline">
                <i class="bi bi-x-lg me-1"></i> Decline
              </a>
            </div>
          </div>
        </div>
      @endforeach
    @endif
  </div>

  @if(!empty($pendingFamilyRequests))
    @foreach($pendingFamilyRequests as $joinReq)
      <div class="card mb-4 border-0 shadow-sm family-join-request-card surface-light" data-surface="light" id="familyJoinReq-{{ $joinReq['no'] }}" style="border-radius: 16px; background: linear-gradient(135deg, #fdf5f5 0%, #fbefef 100%); border-left: 5px solid #a31621 !important; color: #0f172a;">
        <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; background: #f3d9db; color: #8a1019; font-size: 1.3rem;">
              <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <h3 class="fw-bold mb-0" style="font-size: 1rem; color: #0f172a !important;">
                  👋 <strong style="color: #0f172a !important;">{{ htmlspecialchars($joinReq['requesterName'] ?? 'New Member') }}</strong> <span style="color: #0f172a !important;">{{ __('family.wants_to_join', 'wants to join your :code family network', ['code' => htmlspecialchars($joinReq['family_code'] ?? '')]) }}</span>
                </h3>
                @if(!empty($joinReq['is_escalated']))
                  <span class="badge rounded-pill px-2.5 py-1" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.76rem;">
                    <i class="bi bi-broadcast me-1"></i> Family Broadcast Escalation
                  </span>
                @endif
              </div>
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

    <script nonce="{{ csp_nonce($nonce ?? null) }}">
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

    <script nonce="{{ csp_nonce($nonce ?? null) }}">
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
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm mig-req-icon" style="width: 48px; height: 48px; background: #bfdbfe; color: #8a1019; font-size: 1.3rem;">
              <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <div>
              <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
                👋 <strong>{{ htmlspecialchars($migReq['requesterName'] ?? 'A relative') }}</strong> requested to migrate to your <strong>#{{ htmlspecialchars($migReq['new_family_code'] ?? '') }}</strong> family network!
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

    <script nonce="{{ csp_nonce($nonce ?? null) }}">
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
        <div class="rounded-circle d-flex align-items-center justify-content-center unclaimed-match-icon" style="width: 48px; height: 48px; background: #f5dede; color: #a31621; font-size: 1.4rem;">
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
  <script nonce="{{ csp_nonce($nonce ?? null) }}">
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

  <!-- Post Composer (Desktop >=992px; mobile renders above the fold at top of container-main) -->
  <div class="d-none d-lg-block">
    @include('member.includes.postComposer', ['composerId' => 'openPostModalTrigger'])
  </div>

  <!-- Unified Daily Family Spark (Living Spark Card) -->
  @includeIf('member.includes.dailyFamilySparkCard')

  <!-- Daily Family Arcade Banner -->
  @includeIf('member.includes.dailyArcadeBanner')

  <!-- Family Reels & Stories Tray -->
  @includeIf('member.includes.reelsTray')

  <!-- Weekly Activity Video Reel Approval Card (Curated Highlights) -->
  @includeIf('member.includes.weeklyReelApprovalCard')

  <!-- 60-Second Onboarding Checklist (Alpine.js - auto hides when complete) -->
  @includeIf('member.includes.onboardingChecklistWidget')

  <!-- 60-Day Omnichannel Feature Discovery & Activation Hub Widget -->
  @includeIf('member.includes.discoveryJourneyWidget')

  <!-- Contextual Kinship & Phone Contacts Inner Circle Prompt -->
  @includeIf('member.includes.kinshipPromptCard')

  {{-- 🌟 Ecosystem Cross-App Promotion Ribbon (PartyPlatform / iDecide) --}}
  @include('components.advert_banner', [
    'placement' => 'family_feed',
    'context' => [
      'tags' => ['party', 'event', 'celebration', 'birthday', 'milestone'],
    ]
  ])

  <!-- Suggested Kin & In-Laws: Facebook-Style Horizontal Carousel (Visible in feed across mobile & desktop) -->
  @if(!empty($suggestedKin))
  <div class="w-100 mb-4 kin-feed-carousel-wrapper">
    @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => 'kinshipRadarWidgetMobile'])
  </div>
  @endif

  <!-- Pinned Family Gift Wishlists (Top of Feed) -->
  @includeIf('member.includes.pinnedWishlistsFeedCard')
  <div id="feed-root"></div>
    <script type="application/json" id="feed-bootstrap">{!! $feedBootstrapJson ?? '{}' !!}</script>

  <!-- The Modal Structure (Hidden by default) for the post image modal -->
  @includeIf('member.modals.postImg')
  @endif

  <style>
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }
  </style>
</div>
