<!-- Kinship Radar Settings Modal -->
<div class="modal fade" id="kinshipRadarSettingsModal" tabindex="-1" aria-labelledby="kinshipRadarSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 540px;">
    <form id="kinshipRadarSettingsForm" method="POST" action="/api/kinship/settings" class="modal-content" style="border-radius: 22px; border: 1px solid rgba(163,22,33, 0.2); box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35); overflow: hidden; max-height: calc(100vh - 3.5rem);">
      @include('csrf')
      
      <!-- Modal Header -->
      <div class="modal-header border-0 position-relative flex-shrink-0" style="background: linear-gradient(135deg, #a31621 0%, #801018 100%); color: #ffffff; padding: 1.25rem 1.5rem;">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.18); font-size: 1.25rem; flex-shrink: 0; color: #ffffff;">
            <i class="bi bi-sliders"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold mb-0 text-white" id="kinshipRadarSettingsModalLabel" style="font-size: 1.15rem; letter-spacing: -0.01em;">
              {{ __('kinship.radar_settings_title', 'Kinship Radar Settings') }}
            </h5>
            <small style="color: #fce79a; font-size: 0.8125rem; font-weight: 500;">
              {{ __('kinship.radar_settings_subtitle', 'Customise lineage matching, discovery sensitivity, and privacy preferences') }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('kinship.close', 'Close') }}" style="opacity: 0.9; min-width: 44px; min-height: 44px;"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-3 p-md-4" style="overflow-y: auto; flex: 1 1 auto; min-height: 0;">
          
          <!-- Section 1: Master Status Switch -->
          <div class="modal-sub-card-rose p-3 rounded-4 mb-3">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div>
                <label for="radarMasterSwitch" class="fw-bold mb-1 d-block text-dark" style="font-size: 0.95rem;">
                  {{ __('kinship.enable_radar', 'Enable Kinship Radar') }}
                </label>
                <small class="text-secondary d-block" style="font-size: 0.8rem; line-height: 1.35;">
                  {{ __('kinship.radar_enabled_desc', 'Automatically scan for potential kin, in-laws, and ancestors matching your heritage.') }}
                </small>
              </div>
              <div class="form-check form-switch m-0 flex-shrink-0" style="padding-left: 2.5em;">
                <input class="form-check-input" type="checkbox" role="switch" id="radarMasterSwitch" name="radar_enabled" value="1" checked style="width: 2.75em; height: 1.45em; cursor: pointer;">
              </div>
            </div>
          </div>

          <!-- Section 2: Lineage Scope & Match Categories -->
          <div class="mb-3 radar-sub-setting" style="transition: opacity 0.25s ease;">
            <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-1.5" style="font-size: 0.875rem;">
              <i class="bi bi-diagram-3-fill text-danger"></i>
              Lineage Scope &amp; Matching Signals
            </h6>
            
            <div class="list-group rounded-4 border overflow-hidden">
              <!-- In-Laws Switch -->
              <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-0 border-bottom" for="radarInlawsSwitch" style="cursor: pointer;">
                <div class="me-3">
                  <div class="fw-semibold text-dark" style="font-size: 0.875rem;">
                    {{ __('kinship.include_inlaws', 'Include In-Laws & Secondary Branches') }}
                  </div>
                  <small class="text-muted d-block" style="font-size: 0.78rem;">
                    {{ __('kinship.include_inlaws_desc', 'Surface matches from marriage connections, secondary family branches, and in-law circles.') }}
                  </small>
                </div>
                <div class="form-check form-switch m-0 flex-shrink-0" style="min-height: 44px; display: flex; align-items: center;">
                  <input class="form-check-input" type="checkbox" role="switch" id="radarInlawsSwitch" name="include_inlaws" value="1" checked style="width: 2.5em; height: 1.3em; cursor: pointer;">
                </div>
              </label>

              <!-- Maternal Lineage Switch -->
              <label class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-0" for="radarMaternalSwitch" style="cursor: pointer;">
                <div class="me-3">
                  <div class="fw-semibold text-dark" style="font-size: 0.875rem;">
                    {{ __('kinship.include_maternal', 'Include Maternal Heritage Matches') }}
                  </div>
                  <small class="text-muted d-block" style="font-size: 0.78rem;">
                    {{ __('kinship.include_maternal_desc', 'Match potential cousins and relatives through mother\'s maiden name and maternal ancestry.') }}
                  </small>
                </div>
                <div class="form-check form-switch m-0 flex-shrink-0" style="min-height: 44px; display: flex; align-items: center;">
                  <input class="form-check-input" type="checkbox" role="switch" id="radarMaternalSwitch" name="include_maternal" value="1" checked style="width: 2.5em; height: 1.3em; cursor: pointer;">
                </div>
              </label>
            </div>
          </div>

          <!-- Section 3: Minimum Match Confidence Sensitivity -->
          <div class="mb-3 radar-sub-setting" style="transition: opacity 0.25s ease;">
            <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-1.5" style="font-size: 0.875rem;">
              <i class="bi bi-speedometer2 text-danger"></i>
              {{ __('kinship.min_confidence', 'Minimum Match Confidence') }}
            </h6>
            <small class="text-muted d-block mb-2" style="font-size: 0.78rem;">
              {{ __('kinship.min_confidence_desc', 'Only show relatives meeting or exceeding this match certainty score.') }}
            </small>

            <div class="row g-2" id="radarConfidenceSelector">
              <div class="col-4">
                <input type="radio" class="btn-check" name="min_confidence" id="conf50" value="50" checked>
                <label class="btn btn-outline-danger w-100 p-2 text-center rounded-3 d-flex flex-column align-items-center justify-content-center" for="conf50" style="font-size: 0.78rem; min-height: 54px; line-height: 1.2;">
                  <span class="fw-bold">50%+</span>
                  <span class="text-nowrap" style="font-size: 0.7rem;">All Matches</span>
                </label>
              </div>
              <div class="col-4">
                <input type="radio" class="btn-check" name="min_confidence" id="conf70" value="70">
                <label class="btn btn-outline-danger w-100 p-2 text-center rounded-3 d-flex flex-column align-items-center justify-content-center" for="conf70" style="font-size: 0.78rem; min-height: 54px; line-height: 1.2;">
                  <span class="fw-bold">70%+</span>
                  <span class="text-nowrap" style="font-size: 0.7rem;">Strong Only</span>
                </label>
              </div>
              <div class="col-4">
                <input type="radio" class="btn-check" name="min_confidence" id="conf80" value="80">
                <label class="btn btn-outline-danger w-100 p-2 text-center rounded-3 d-flex flex-column align-items-center justify-content-center" for="conf80" style="font-size: 0.78rem; min-height: 54px; line-height: 1.2;">
                  <span class="fw-bold">80%+</span>
                  <span class="text-nowrap" style="font-size: 0.7rem;">Direct Clan</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Section 4: Privacy & Radar Discoverability -->
          <div class="modal-sub-card p-3 rounded-4 mb-3 border radar-sub-setting" style="transition: opacity 0.25s ease;">
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div class="me-2">
                <label for="radarDiscoverableSwitch" class="fw-semibold text-dark mb-1 d-block" style="font-size: 0.875rem;">
                  {{ __('kinship.radar_discoverable', 'Allow Relatives to Discover You') }}
                </label>
                <small class="text-muted d-block" style="font-size: 0.78rem; line-height: 1.35;">
                  {{ __('kinship.radar_discoverable_desc', 'Permit verified family members and kin to see you in their Kinship Radar suggestions.') }}
                </small>
              </div>
              <div class="form-check form-switch m-0 flex-shrink-0" style="min-height: 44px; display: flex; align-items: center;">
                <input class="form-check-input" type="checkbox" role="switch" id="radarDiscoverableSwitch" name="discoverable" value="1" checked style="width: 2.5em; height: 1.3em; cursor: pointer;">
              </div>
            </div>
          </div>

          <!-- Walled Sanctuary Note -->
          <div class="modal-sub-card-green p-2.5 rounded-3 d-flex align-items-start gap-2" style="font-size: 0.78rem;">
            <i class="bi bi-shield-check text-success fs-6 mt-0.5 flex-shrink-0"></i>
            <span class="text-secondary" style="line-height: 1.4;">
              {{ __('kinship.privacy_note', 'Your connection is protected by the Walled Sanctuary guarantee. Your private data is never exposed.') }}
            </span>
          </div>

        </div>

        <!-- Modal Footer Actions (Sticky & Guaranteed in View) -->
        <div class="modal-footer border-0 p-3 d-flex justify-content-end gap-2 flex-shrink-0" style="position: sticky; bottom: 0; z-index: 10;">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px;">
            {{ __('common.cancel', 'Cancel') }}
          </button>
          <button type="submit" id="btnSaveRadarSettings" class="btn btn-primary rounded-pill px-4 fw-semibold d-flex align-items-center gap-2 btn-spin-on-click" data-spin="true" style="min-height: 44px; background: #a31621; border-color: #a31621;">
            <i class="bi bi-check2-circle"></i>
            <span>{{ __('kinship.save_radar_settings', 'Save Radar Preferences') }}</span>
          </button>
        </div>
      </form>
  </div>
</div>
