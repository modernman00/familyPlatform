<!-- Recommend & Family Invite Modal -->
<div class="modal fade" id="recommendFriendsModal" tabindex="-1" aria-labelledby="recommendFriendsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius: 24px; border: 1px solid rgba(184,32,44, 0.2); box-shadow: 0 25px 50px -12px rgba(163,22,33, 0.25); overflow: hidden;">
      @include('csrf')

      @php
          $modalFamCode = ltrim((string)($_SESSION['famCode'] ?? ($data['famCode'] ?? '')), '#');
          $modalSurname = (string)($maritalSurname ?? ($data['familySurname'] ?? ($data['lastName'] ?? 'Family')));
      @endphp
      
      <!-- Modal Header -->
      <div class="modal-header border-0" style="background: linear-gradient(135deg, #2a0a0d 0%, #2a0a0d 60%, #8a1019 100%); color: #ffffff; padding: 1.5rem 1.75rem;">
        <div class="d-flex align-items-center gap-3">
          <div style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 14px; background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1.5px solid rgba(245, 158, 11, 0.5); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            <i class="bi bi-person-plus-fill"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h5 class="modal-title fw-bold mb-0 text-white" id="recommendFriendsModalLabel" style="font-size: 1.25rem;">
                Invite to FamilyPlatform
              </h5>
              <span class="badge rounded-pill bg-warning text-dark fw-bold px-2 py-0.5" style="font-size: 0.72rem;">
                PRIVATE SANCTUARY
              </span>
            </div>
            <small class="text-white-50" style="font-size: 0.85rem;">Invite relatives to join your family network or recommend friends to start their own</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Navigation Tabs: Family Network vs Friends/In-Laws -->
      <div class="px-4 pt-3 pb-0 bg-light border-bottom">
        <ul class="nav nav-pills nav-fill gap-2 p-1 rounded-pill bg-white border" id="inviteModalTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold py-2 small d-flex align-items-center justify-content-center gap-1.5" id="tab-our-family" data-bs-toggle="pill" data-bs-target="#pane-our-family" type="button" role="tab" aria-controls="pane-our-family" aria-selected="true" style="min-height: 42px;">
              <i class="bi bi-people-fill text-primary"></i> <span>Our Family Network</span>
              @if(!empty($modalFamCode))
                <span class="badge bg-danger-subtle text-danger font-monospace ms-1">#{{ $modalFamCode }}</span>
              @endif
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2 small d-flex align-items-center justify-content-center gap-1.5 text-secondary" id="tab-external-friends" data-bs-toggle="pill" data-bs-target="#pane-external-friends" type="button" role="tab" aria-controls="pane-external-friends" aria-selected="false" style="min-height: 42px;">
              <i class="bi bi-stars text-warning"></i> <span>Friends &amp; In-Laws (New Network)</span>
            </button>
          </li>
        </ul>
      </div>

      <!-- Modal Body Tab Content -->
      <div class="modal-body p-4 tab-content" id="inviteModalTabContent">
        
        <!-- ========================================== -->
        <!-- TAB 1: General Invite to Our Family Network -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="pane-our-family" role="tabpanel" aria-labelledby="tab-our-family">
          
          <!-- Family Context Banner -->
          <div class="modal-sub-card p-3 mb-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2.5">
              <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0"
                   style="width: 38px; height: 38px; background: rgba(16, 185, 129, 0.16); font-size: 1.15rem;">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <strong class="text-dark d-block small" style="font-size: 0.9rem;">Private Family Network</strong>
                <span class="text-muted small" style="font-size: 0.78rem;">
                  Relatives connect to your family tree with Family Code: 
                  <span class="badge bg-primary text-white font-monospace fw-bold" id="navbarModalFamilyCodeBadge">#{{ $modalFamCode ?: '...' }}</span>
                </span>
              </div>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
              Connected Members
            </span>
          </div>

          <!-- Quick 1-Tap Action Buttons -->
          <div class="row g-2 mb-3">
            <!-- Share on WhatsApp -->
            <div class="col-12 col-sm-6">
              <a href="#" target="_blank" id="btnFamilyModalWhatsApp" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold text-white shadow-xs transition-all"
                 style="background: #25D366; border-radius: 12px; min-height: 46px; font-size: 0.92rem; text-decoration: none;">
                <i class="bi bi-whatsapp fs-5"></i>
                <span style="white-space: nowrap;">Share via WhatsApp</span>
              </a>
            </div>
            <!-- Copy Full Rich Invite -->
            <div class="col-12 col-sm-6">
              <button type="button" id="btnFamilyModalCopyFull" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold text-white shadow-xs transition-all"
                      style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); border: none; border-radius: 12px; min-height: 46px; font-size: 0.92rem;">
                <i class="bi bi-clipboard-check-fill fs-5 text-warning"></i>
                <span id="btnFamilyModalCopyFullText" style="white-space: nowrap;">Copy Full Invite</span>
              </button>
            </div>
            <!-- Copy Link Only -->
            <div class="col-12 col-sm-6">
              <button type="button" id="btnFamilyModalCopyLink" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-xs transition-all"
                      style="border-radius: 12px; min-height: 46px; font-size: 0.9rem;">
                <i class="bi bi-link-45deg fs-5"></i>
                <span id="btnFamilyModalCopyLinkText" style="white-space: nowrap;">Copy Link Only</span>
              </button>
            </div>
            <!-- Device Share Sheet -->
            <div class="col-12 col-sm-6">
              <button type="button" id="btnFamilyModalWebShare" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-xs transition-all"
                      style="border-radius: 12px; min-height: 46px; font-size: 0.9rem;">
                <i class="bi bi-share-fill"></i>
                <span style="white-space: nowrap;">Device Share Sheet</span>
              </button>
            </div>
          </div>

          <!-- Real-Time Rich Invitation Preview -->
          <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label class="form-label text-muted fw-semibold small mb-0" style="font-size: 0.8rem;">
                Message Preview (Rich WhatsApp &amp; Clipboard Format)
              </label>
              <span class="badge bg-light-subtle text-muted border font-monospace" style="font-size: 0.72rem;">256-bit signed</span>
            </div>
            <div class="modal-preview-box p-3 rounded-3" id="familyModalInvitePreview">
              Loading rich family invitation...
            </div>
          </div>

          <!-- Personalise or Contacts Link -->
          <div class="modal-sub-card p-2.5 rounded-3 border text-center">
            <span class="small text-muted me-2">Need a personalised invite for Mum, Dad, or specific relatives?</span>
            <button type="button" class="btn btn-link btn-sm fw-bold text-primary p-0 text-decoration-none" onclick="window.switchToFullInviteModal && window.switchToFullInviteModal()" style="font-size: 0.84rem;">
              Open Full Kinship Inviter &rarr;
            </button>
          </div>

          <!-- Feedback Toast Alert -->
          <div id="familyModalFeedbackAlert" class="alert alert-success d-none py-2 px-3 mt-3 text-center small rounded-3 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <span id="familyModalFeedbackText">Invite copied to clipboard!</span>
          </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 2: External Friends / In-Laws Referral -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="pane-external-friends" role="tabpanel" aria-labelledby="tab-external-friends">
          
          <!-- USP Sanctuary Promise Box -->
          <div class="modal-sub-card-green p-3 mb-3">
            <div class="d-flex align-items-start gap-2">
              <span style="font-size: 1.25rem;">🛡️</span>
              <div>
                <strong class="d-block mb-1" style="font-size: 0.95rem;">The Walled Sanctuary Guarantee</strong>
                <p class="mb-0 text-muted" style="font-size: 0.825rem; line-height: 1.4;">
                  Your friends &amp; in-laws will create their <strong>own independent family network</strong> with their own private family code. Your family tree and posts remain 100% strictly private.
                </p>
              </div>
            </div>
          </div>

          <!-- 1-Tap WhatsApp Share Button -->
          <div class="mb-3">
            <a href="#" target="_blank" id="btnShareWhatsApp" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold" style="background: #25D366; color: #ffffff; border-radius: 12px; min-height: 44px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25); text-decoration: none;">
              <i class="bi bi-whatsapp fs-5"></i> Share via WhatsApp
            </a>
          </div>

          <div class="d-flex align-items-center my-3">
            <hr class="flex-grow-1 my-0" style="border-color: #e2e8f0;">
            <span class="px-3 text-muted fw-semibold" style="font-size: 0.8rem;">OR COPY LINK</span>
            <hr class="flex-grow-1 my-0" style="border-color: #e2e8f0;">
          </div>

          <!-- Copyable Link Field -->
          <div class="mb-3">
            <label for="inputShareLink" class="form-label text-muted fw-semibold small mb-1">Your Personal Referral Link</label>
            <div class="input-group">
              <input type="text" id="inputShareLink" class="form-control" readonly style="font-size: 0.9rem; font-family: monospace; border-radius: 10px 0 0 10px;" value="Generating link...">
              <button class="btn btn-primary px-3 fw-semibold" id="btnCopyShareLink" type="button" style="border-radius: 0 10px 10px 0; min-height: 38px;">
                <i class="bi bi-clipboard me-1"></i> Copy
              </button>
            </div>
            <div id="copySuccessFeedback" class="text-success small fw-semibold mt-1 d-none">
              <i class="bi bi-check-circle-fill me-1"></i> Link copied to clipboard!
            </div>
          </div>

          <!-- Stats / Impact Ribbon -->
          <div class="modal-sub-card d-flex align-items-center justify-content-between p-2 px-3 mt-3">
            <span class="text-muted small">Family Hubs Spawned:</span>
            <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold" id="badgeSpawnedCount">0</span>
          </div>

        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0 pt-0 px-4 pb-3">
        <button type="button" class="btn btn-light w-100 fw-semibold text-secondary" style="border-radius: 12px; min-height: 42px;" data-bs-dismiss="modal">
          Done
        </button>
      </div>

    </div>
  </div>
</div>

<script>
(function() {
    let familyInviteData = {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $modalFamCode }}',
        surname: '{{ $modalSurname }}'
    };

    function buildRichInviteMessage(surname, code, url) {
        return `🌳 *Family Heritage Invitation* 🌳\n\n` +
            `You are invited to join our *${surname || 'Family'} Network & Heritage Tree* on FamilyPlatform.\n\n` +
            `🔑 *Family Code:* #${code}\n\n` +
            `✨ Claim your branch on our live family tree, preserve our heritage, share memories, and connect across generations.\n\n` +
            `👉 Tap here to join our family sanctuary:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`;
    }

    function showFamilyFeedback(msg) {
        const alertEl = document.getElementById('familyModalFeedbackAlert');
        const textEl = document.getElementById('familyModalFeedbackText');
        if (!alertEl || !textEl) return;
        textEl.textContent = msg;
        alertEl.classList.remove('d-none');
        setTimeout(() => alertEl.classList.add('d-none'), 3500);
    }

    function updateFamilyInviteUI() {
        const url = familyInviteData.invite_url || window.location.origin + '/register';
        const code = familyInviteData.family_code || '{{ $modalFamCode }}';
        const surname = familyInviteData.surname || '{{ $modalSurname }}';
        const richMsg = buildRichInviteMessage(surname, code, url);

        const previewEl = document.getElementById('familyModalInvitePreview');
        if (previewEl) previewEl.textContent = richMsg;

        const waBtn = document.getElementById('btnFamilyModalWhatsApp');
        if (waBtn) {
            waBtn.href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(richMsg);
        }

        const badgeEl = document.getElementById('navbarModalFamilyCodeBadge');
        if (badgeEl && code) badgeEl.textContent = '#' + code;
    }

    async function loadGeneralFamilyInvite() {
        try {
            const res = await fetch('/api/onboarding/state', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const json = await res.json();
                if (json.status === 'success' && json.data?.invite_data) {
                    familyInviteData = Object.assign(familyInviteData, json.data.invite_data);
                }
            }
        } catch (e) {
            // fallback
        } finally {
            updateFamilyInviteUI();
        }
    }

    function fallbackCopyText(text, successMsg) {
        try {
            const tempArea = document.createElement('textarea');
            tempArea.value = text;
            tempArea.style.position = 'fixed';
            tempArea.style.left = '-9999px';
            document.body.appendChild(tempArea);
            tempArea.focus();
            tempArea.select();
            document.execCommand('copy');
            document.body.removeChild(tempArea);
            showFamilyFeedback(successMsg);
            return;
        } catch (e) {}
        prompt('Copy invite text:', text);
    }

    window.switchToFullInviteModal = function() {
        const currentModal = document.getElementById('recommendFriendsModal');
        if (currentModal && window.bootstrap) {
            const bsCurrent = bootstrap.Modal.getInstance(currentModal);
            if (bsCurrent) bsCurrent.hide();
        }
        const fullModal = document.getElementById('inviteFamilyModal');
        if (fullModal && window.bootstrap) {
            setTimeout(() => {
                const bsFull = bootstrap.Modal.getOrCreateInstance(fullModal);
                bsFull.show();
            }, 300);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('recommendFriendsModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function() {
            loadGeneralFamilyInvite();
        });

        // Copy Full Rich Invite
        const btnCopyFull = document.getElementById('btnFamilyModalCopyFull');
        if (btnCopyFull) {
            btnCopyFull.addEventListener('click', function() {
                const url = familyInviteData.invite_url || window.location.origin + '/register';
                const code = familyInviteData.family_code || '{{ $modalFamCode }}';
                const surname = familyInviteData.surname || '{{ $modalSurname }}';
                const richMsg = buildRichInviteMessage(surname, code, url);

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(richMsg).then(() => {
                        const span = document.getElementById('btnFamilyModalCopyFullText');
                        if (span) span.textContent = 'Invite Copied! ✓';
                        showFamilyFeedback('Full rich invitation with Family Code copied to clipboard!');
                        setTimeout(() => {
                            if (span) span.textContent = 'Copy Full Invite';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopyText(richMsg, 'Full rich invitation with Family Code copied to clipboard!');
                    });
                } else {
                    fallbackCopyText(richMsg, 'Full rich invitation with Family Code copied to clipboard!');
                }
            });
        }

        // Copy Link Only
        const btnCopyLink = document.getElementById('btnFamilyModalCopyLink');
        if (btnCopyLink) {
            btnCopyLink.addEventListener('click', function() {
                const url = familyInviteData.invite_url || window.location.origin + '/register';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        const span = document.getElementById('btnFamilyModalCopyLinkText');
                        if (span) span.textContent = 'Link Copied! ✓';
                        showFamilyFeedback('Family invite link copied to clipboard!');
                        setTimeout(() => {
                            if (span) span.textContent = 'Copy Link Only';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopyText(url, 'Family invite link copied to clipboard!');
                    });
                } else {
                    fallbackCopyText(url, 'Family invite link copied to clipboard!');
                }
            });
        }

        // Web Share Sheet
        const btnWebShare = document.getElementById('btnFamilyModalWebShare');
        if (btnWebShare) {
            btnWebShare.addEventListener('click', async function() {
                const url = familyInviteData.invite_url || window.location.origin + '/register';
                const code = familyInviteData.family_code || '{{ $modalFamCode }}';
                const surname = familyInviteData.surname || '{{ $modalSurname }}';
                const richMsg = buildRichInviteMessage(surname, code, url);

                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: `Join our ${surname || 'Family'} Sanctuary`,
                            text: richMsg,
                            url: url
                        });
                    } catch (e) {}
                } else {
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(richMsg), '_blank', 'noopener,noreferrer');
                }
            });
        }
    });
})();
</script>
