{{-- Kinship Radar widget. Does not occupy space when there is no new suggestion. --}}
@php
    $suggestedKin = $suggestedKin ?? [];
    // The in-feed (mobile) copy scrolls horizontally like the Reels tray; the
    // desktop sidebar copy stays a vertical stack. The widget id is the existing
    // discriminator between the two placements.
    $kinCarousel = ($kinshipWidgetId ?? 'kinshipRadarWidget') === 'kinshipRadarWidgetMobile';
@endphp
@if(!empty($suggestedKin))
<div class="card border-0 shadow-sm mb-4 kinship-radar-widget" id="{{ $kinshipWidgetId ?? 'kinshipRadarWidget' }}" style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0) !important;">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px; background: rgba(163, 22, 33, 0.12); font-size: 1.1rem; flex-shrink: 0;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 kin-heading" style="font-size: 0.95rem; letter-spacing: -0.01em;">{{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}</h6>
                    <span class="d-block kin-subtitle" style="font-size: 0.8125rem; line-height: 1.4; color: var(--text-muted, #64748b);">{{ __('kinship.people_you_may_know', 'People you may know') }}</span>
                </div>
            </div>
            <span class="badge rounded-pill bg-light text-primary border" style="font-size: 0.75rem; font-weight: 700;">{{ __('kinship.radar', 'Kinship Radar') }}</span>
        </div>

        <div class="{{ $kinCarousel ? 'kinship-suggestions-scroll' : 'd-flex flex-column gap-3' }}" id="kinshipSuggestionsList">
            @foreach($suggestedKin as $kin)
                @php
                    $kinAvatar = !empty($kin['profilePics']) ? (str_starts_with($kin['profilePics'], '/') ? $kin['profilePics'] : '/resources/images/profile/' . $kin['profilePics']) : '/resources/images/profile/avatarM.png';
                    $rawName = trim(($kin['firstName'] ?? '') . ' ' . ($kin['lastName'] ?? ''));
                    $kinName = !empty($rawName) ? ucwords(strtolower($rawName)) : (!empty($kin['famCode']) ? __('kinship.family_relative', 'Family Relative') . ' (' . $kin['famCode'] . ')' : __('kinship.family_relative', 'Family Relative'));
                    $kinId = $kin['user_id'] ?? '';
                    $kinType = $kin['kinship_type'] ?? 'Potential Kin';
                    $primaryReason = $kin['primary_reason'] ?? 'Shared family heritage network';
                    $score = $kin['confidence_score'] ?? 75;
                    $reasons = !empty($kin['reasons']) && is_array($kin['reasons']) ? $kin['reasons'] : [$primaryReason];
                    $kinPayload = [
                        'id' => (string)$kinId,
                        'name' => $kinName,
                        'avatar' => $kinAvatar,
                        'score' => $score,
                        'type' => $kinType,
                        'primaryReason' => $primaryReason,
                        'reasons' => $reasons,
                        'famCode' => $kin['famCode'] ?? '',
                        'familyCode2' => $kin['familyCode2'] ?? '',
                        'maidenName' => $kin['maiden_name'] ?? '',
                    ];
                    $kinDataJson = htmlspecialchars(json_encode($kinPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                @endphp
                <div class="p-3 rounded-4 kinship-item-card position-relative {{ $kinCarousel ? 'kinship-item-card--scroll' : '' }}" 
                     id="kinCard_{{ $kinId }}" 
                     data-kin="{{ $kinDataJson }}"
                     role="button"
                     tabindex="0"
                     aria-label="View {{ $kinName }} kinship details"
                     style="transition: all 0.2s ease; cursor: pointer; background-color: var(--kin-card-bg, #f8fafc); border: 1px solid var(--kin-card-border, #e2e8f0);">
                    <!-- Top row: Avatar + Names + Dismiss -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2 kin-card-info" style="min-width: 0; flex: 1;">
                            <img src="{{ $kinAvatar }}" alt="{{ $kinName }}" class="rounded-circle border flex-shrink-0" width="44" height="44" style="object-fit: cover;">
                            <div class="text-truncate" style="min-width: 0; flex: 1;">
                                <div class="fw-bold kin-member-name text-truncate" title="{{ $kinName }}" style="font-size: 0.95rem; line-height: 1.25; color: var(--text-color, #0f172a);">{{ $kinName }}</div>
                                <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.75rem; font-weight: 700;">{{ $score }}% {{ __('kinship.match', 'Match') }}</span>
                                    <span class="fw-semibold kin-type-label" style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">• {{ $kinType }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dismiss Button (Top Right) -->
                        <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none btn-dismiss-kin flex-shrink-0" data-user-id="{{ $kinId }}" aria-label="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" title="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" style="line-height: 1; margin-top: 2px; min-height: 36px; min-width: 36px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>

                    <!-- Primary Reason Badge / Info -->
                    <div class="mb-3 ps-1 kin-primary-reason text-truncate" style="font-size: 0.85rem; line-height: 1.4; color: var(--text-color, #475569);">
                        <i class="bi bi-diagram-2 text-warning me-1"></i> <span>{{ $primaryReason }}</span>
                    </div>

                    <!-- Connect Action Button (Full Width, never overlaps) -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold btn-connect-kin d-flex align-items-center justify-content-center gap-1 shadow-sm" data-user-id="{{ $kinId }}" style="font-size: 0.875rem; min-height: 44px; padding: 8px 14px; color: #ffffff !important; background-color: var(--brand-primary, #a31621); transition: all 0.2s ease;">
                            <i class="bi bi-person-plus-fill"></i> {{ __('kinship.connect', 'Connect') }}
                        </button>
                    </div>

                    <!-- Subtle tap hint -->
                    <div class="text-center mt-2 kin-tap-hint" style="font-size: 0.72rem; color: var(--text-muted-aa, #64748b);">
                        <i class="bi bi-info-circle me-1"></i>{{ __('kinship.tap_to_view', 'Tap card to view kinship clues') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
