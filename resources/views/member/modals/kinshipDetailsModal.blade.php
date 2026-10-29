<!-- Kinship Radar Details & Lineage Clues Modal -->
<div class="modal fade" id="kinshipDetailsModal" tabindex="-1" aria-labelledby="kinshipDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px;">
    <div class="modal-content" style="border-radius: 22px; border: 1px solid rgba(163,22,33, 0.2); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); overflow: hidden;">
      
      <!-- Modal Header -->
      <div class="modal-header border-0 position-relative" style="background: linear-gradient(135deg, #a31621 0%, #801018 100%); color: #ffffff; padding: 1.25rem 1.5rem;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.18); font-size: 1.25rem; flex-shrink: 0; color: #ffffff;">
            <i class="bi bi-radar"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold mb-0 text-white" id="kinshipDetailsModalLabel" style="font-size: 1.15rem; letter-spacing: -0.01em;">
              {{ __('kinship.details_modal_title', 'Kinship Radar Clues') }}
            </h5>
            <small style="color: #fce79a; font-size: 0.8125rem; font-weight: 500;">
              {{ __('kinship.details_modal_subtitle', 'Explore heritage connections & lineage clues before deciding to connect') }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('kinship.close', 'Close') }}" style="opacity: 0.9;"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-3 p-md-4" style="max-height: 75vh;">
        
        <!-- Profile Banner Card -->
        <div class="modal-sub-card-rose p-3 rounded-4 mb-3 kin-modal-profile-card">
          <div class="d-flex align-items-center gap-3">
            <img id="kinModalAvatar" src="/resources/images/profile/avatarM.png" alt="Avatar" class="rounded-circle border border-2 border-white shadow-sm flex-shrink-0" width="68" height="68" style="width: 68px; height: 68px; min-width: 68px; min-height: 68px; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 50%;">
            <div style="min-width: 0; flex: 1;">
              <h5 class="fw-bold mb-1 text-truncate kin-modal-name" id="kinModalName" style="font-size: 1.15rem; line-height: 1.3;">
                {{ __('kinship.family_relative', 'Family Relative') }}
              </h5>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span id="kinModalScore" class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: #a31621; color: #ffffff; font-size: 0.75rem;">
                  75% {{ __('kinship.match', 'Match') }}
                </span>
                <span id="kinModalType" class="badge rounded-pill bg-light text-dark border px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                  Potential Kin
                </span>
              </div>
            </div>
          </div>

          <!-- Match Strength Bar -->
          <div class="mt-3 pt-2 border-top border-light-subtle">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-secondary fw-semibold" style="font-size: 0.78rem;">
                {{ __('kinship.match_strength', 'Match Strength') }}
              </span>
              <span id="kinModalPercentLabel" class="fw-bold text-primary" style="font-size: 0.78rem;">75%</span>
            </div>
            <div class="progress" style="height: 6px; border-radius: 999px; background-color: #e2e8f0;">
              <div id="kinModalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%; background: linear-gradient(90deg, #b8202c, #a31621); border-radius: 999px;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>

        <!-- Lineage & Heritage Clues Section -->
        <div class="mb-3">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-diagram-3-fill text-primary" style="font-size: 1rem;"></i>
            <h6 class="fw-bold mb-0 kin-section-title" style="font-size: 0.95rem;">
              {{ __('kinship.lineage_clues', 'Lineage & Heritage Clues') }}
            </h6>
          </div>
          
          <div class="modal-sub-card p-3 rounded-4 border kin-clues-box">
            <ul id="kinModalReasonsList" class="list-unstyled mb-0 d-flex flex-column gap-2">
              <!-- Dynamically populated -->
            </ul>
          </div>
        </div>

        <!-- Family Branch Info (Secondary) -->
        <div id="kinModalBranchBox" class="mb-3 d-none">
          <div class="modal-sub-card p-2.5 rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size: 0.8125rem;">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-house-door text-muted"></i>
              <span class="text-secondary">{{ __('kinship.family_branch', 'Family Branch') }}:</span>
              <strong id="kinModalFamCode" class="text-dark"></strong>
            </div>
            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-2 py-0.5" id="kinModalHeritageNote">Clan Match</span>
          </div>
        </div>

        <!-- Walled Sanctuary Privacy Note -->
        <div class="modal-sub-card-green p-3 rounded-3" style="font-size: 0.8125rem; line-height: 1.45;">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-shield-lock-fill text-success fs-6 mt-0.5 flex-shrink-0"></i>
            <div>
              <strong style="color: #166534;">The Walled Sanctuary Guarantee</strong>
              <p class="mb-0 text-secondary" style="font-size: 0.78rem;">
                {{ __('kinship.privacy_note', 'Your connection is protected by the Walled Sanctuary guarantee. Your private data is never exposed.') }}
              </p>
            </div>
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0 p-3 p-md-4 pt-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="#" id="kinModalTreeBtn" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5" style="min-height: 44px; font-size: 0.875rem; white-space: nowrap; flex-shrink: 0;">
          <i class="bi bi-diagram-3"></i> {{ __('kinship.view_family_tree', 'View in Family Tree') }}
        </a>
        
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light rounded-pill px-3 py-2 fw-semibold text-secondary" data-bs-dismiss="modal" style="min-height: 44px; font-size: 0.875rem; white-space: nowrap; flex-shrink: 0;">
            {{ __('kinship.close', 'Close') }}
          </button>
          <button type="button" id="kinModalConnectBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" data-user-id="" style="min-height: 44px; font-size: 0.875rem; white-space: nowrap; flex-shrink: 0; color: #ffffff !important; background-color: var(--brand-primary, #a31621); border-color: var(--brand-primary, #a31621); transition: all 0.2s ease;">
            <i class="bi bi-person-plus-fill"></i> <span id="kinModalConnectLabel">{{ __('kinship.connect', 'Connect') }}</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
