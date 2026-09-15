<!-- Recommend FamilyPlatform Modal -->
<div class="modal fade" id="recommendFriendsModal" tabindex="-1" aria-labelledby="recommendFriendsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.2); box-shadow: 0 25px 50px -12px rgba(79, 70, 229, 0.25);">
      
      <!-- Modal Header -->
      <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff; border-top-left-radius: 19px; border-top-right-radius: 19px; padding: 1.5rem 1.75rem;">
        <div class="d-flex align-items-center gap-3">
          <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
            🌟
          </div>
          <div>
            <h5 class="modal-title fw-bold mb-0" id="recommendFriendsModalLabel" style="font-size: 1.25rem;">
              Recommend FamilyPlatform
            </h5>
            <small style="color: #c7d2fe; font-size: 0.85rem;">Invite in-laws & friends to launch their own family hub</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        
        <!-- USP Sanctuary Promise Box -->
        <div class="p-3 mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px;">
          <div class="d-flex align-items-start gap-2">
            <span style="font-size: 1.25rem;">🛡️</span>
            <div>
              <strong style="color: #166534; font-size: 0.95rem;">The Walled Sanctuary Guarantee</strong>
              <p class="mb-0 text-muted" style="font-size: 0.825rem; line-height: 1.4;">
                Your friends & in-laws will create their <strong>own independent family network</strong> with their own private family code. Your family tree and posts remain 100% strictly private.
              </p>
            </div>
          </div>
        </div>

        <!-- 1-Tap WhatsApp Share Button -->
        <div class="mb-3">
          <a href="#" target="_blank" id="btnShareWhatsApp" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold" style="background: #25D366; color: #ffffff; border-radius: 12px; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25); text-decoration: none;">
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
          <label for="inputShareLink" class="form-label text-muted fw-semibold small mb-1">Your Personal Recommendation Link</label>
          <div class="input-group">
            <input type="text" id="inputShareLink" class="form-control" readonly style="background: #f8fafc; font-size: 0.9rem; font-family: monospace;" value="Generating link...">
            <button class="btn btn-primary px-3 fw-semibold" id="btnCopyShareLink" type="button" style="border-radius: 0 8px 8px 0;">
              <i class="bi bi-clipboard me-1"></i> Copy
            </button>
          </div>
          <div id="copySuccessFeedback" class="text-success small fw-semibold mt-1 d-none">
            <i class="bi bi-check-circle-fill me-1"></i> Link copied to clipboard!
          </div>
        </div>

        <!-- Stats / Impact Ribbon -->
        <div class="d-flex align-items-center justify-content-between p-2 px-3 mt-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
          <span class="text-muted small">Family Hubs Spawned:</span>
          <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold" id="badgeSpawnedCount">0</span>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0 pt-0 px-4 pb-3">
        <button type="button" class="btn btn-light w-100 fw-semibold text-secondary" data-bs-dismiss="modal">
          Done
        </button>
      </div>

    </div>
  </div>
</div>
