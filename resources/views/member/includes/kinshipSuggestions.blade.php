{{-- Kinship Radar widget. Does not occupy space when there is no new suggestion. --}}
@php
    $suggestedKin = $suggestedKin ?? [];
    // The in-feed (mobile) copy scrolls horizontally like the Reels tray; the
    // desktop sidebar copy stays a vertical stack. The widget id is the existing
    // discriminator between the two placements.
    $kinCarousel = ($kinshipWidgetId ?? 'kinshipRadarWidget') === 'kinshipRadarWidgetMobile';

    // Count categories for filter pills
    $countPaternal = 0;
    $countInLaws = 0;
    $countMaternal = 0;
    foreach ($suggestedKin as $k) {
        $t = strtolower($k['kinship_type'] ?? '');
        if (str_contains($t, 'paternal') || str_contains($t, 'clan') || str_contains($t, 'surname')) {
            $countPaternal++;
        } elseif (str_contains($t, 'in-law') || str_contains($t, 'inlaw')) {
            $countInLaws++;
        } elseif (str_contains($t, 'maternal') || str_contains($t, 'mother') || str_contains($t, 'maiden')) {
            $countMaternal++;
        }
    }
@endphp
@if(!empty($suggestedKin))
<div class="card border-0 shadow-sm mb-4 kinship-radar-widget" 
     id="{{ $kinshipWidgetId ?? 'kinshipRadarWidget' }}" 
     x-data="{ activeFilter: 'all' }"
     style="border-radius: 24px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0) !important;">
    <div class="card-body p-3.5 p-md-4">
        <!-- Header with Radar Active Beacon -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-3 d-flex align-items-center justify-content-center" 
                     style="width: 42px; height: 42px; background: rgba(163, 22, 33, 0.08); font-size: 1.25rem; flex-shrink: 0; color: var(--brand-primary, #8B152B) !important;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 kin-heading" style="font-size: 1rem; letter-spacing: -0.01em; color: var(--text-color, #0f172a);">
                        {{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}
                    </h6>
                    <span class="d-block kin-subtitle" style="font-size: 0.8125rem; line-height: 1.35; color: var(--text-muted, #64748b);">
                        {{ __('kinship.lineage_matches_subtitle', 'Lineage matches you may know') }}
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1.5">
                <span class="badge rounded-pill px-2.5 py-1 kin-radar-badge d-flex align-items-center gap-1.5" style="background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; font-size: 0.72rem; font-weight: 700;">
                    <span class="kin-radar-pulse"></span>
                    {{ __('kinship.radar_active', 'Radar Active') }}
                </span>
            </div>
        </div>

        <!-- Filter Pills Bar -->
        <div class="d-flex align-items-center gap-1.5 mb-3.5 pb-1 kin-filter-bar overflow-x-auto text-nowrap" style="scrollbar-width: none;">
            <span class="text-muted fw-semibold small me-1" style="font-size: 0.78rem;">{{ __('kinship.filter_label', 'Filter:') }}</span>
            <button type="button" 
                    @click="activeFilter = 'all'" 
                    class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-filter-kin" 
                    :class="activeFilter === 'all' ? 'active' : ''"
                    data-filter="all" 
                    style="font-size: 0.78rem; min-height: 32px;">
                {{ __('kinship.all_matches', 'All Matches') }} ({{ count($suggestedKin) }})
            </button>
            @if($countPaternal > 0)
            <button type="button" 
                    @click="activeFilter = 'paternal'" 
                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-filter-kin" 
                    :class="activeFilter === 'paternal' ? 'active' : ''"
                    data-filter="paternal" 
                    style="font-size: 0.78rem; min-height: 32px;">
                {{ __('kinship.paternal_clan', 'Paternal Clan') }} ({{ $countPaternal }})
            </button>
            @endif
            @if($countInLaws > 0)
            <button type="button" 
                    @click="activeFilter = 'inlaws'" 
                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-filter-kin" 
                    :class="activeFilter === 'inlaws' ? 'active' : ''"
                    data-filter="inlaws" 
                    style="font-size: 0.78rem; min-height: 32px;">
                {{ __('kinship.in_laws', 'In-Laws') }} ({{ $countInLaws }})
            </button>
            @endif
            @if($countMaternal > 0)
            <button type="button" 
                    @click="activeFilter = 'maternal'" 
                    class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-filter-kin" 
                    :class="activeFilter === 'maternal' ? 'active' : ''"
                    data-filter="maternal" 
                    style="font-size: 0.78rem; min-height: 32px;">
                {{ __('kinship.maternal_clan', 'Maternal Clan') }} ({{ $countMaternal }})
            </button>
            @endif
        </div>

        <div class="{{ $kinCarousel ? 'kinship-suggestions-scroll' : 'd-flex flex-column gap-3.5' }}" id="kinshipSuggestionsList">
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

                    // Category mapping for client-side filtering
                    $kinTypeLower = strtolower($kinType);
                    if (str_contains($kinTypeLower, 'paternal') || str_contains($kinTypeLower, 'clan') || str_contains($kinTypeLower, 'surname')) {
                        $kinCategory = 'paternal';
                        $lineagePathLabel = __('kinship.paternal_line', 'Paternal Line');
                    } elseif (str_contains($kinTypeLower, 'in-law') || str_contains($kinTypeLower, 'inlaw')) {
                        $kinCategory = 'inlaws';
                        $lineagePathLabel = __('kinship.in_law_line', 'In-Law Line');
                    } elseif (str_contains($kinTypeLower, 'maternal') || str_contains($kinTypeLower, 'mother') || str_contains($kinTypeLower, 'maiden')) {
                        $kinCategory = 'maternal';
                        $lineagePathLabel = __('kinship.maternal_line', 'Maternal Line');
                    } else {
                        $kinCategory = 'other';
                        $lineagePathLabel = __('kinship.heritage_line', 'Heritage Line');
                    }

                    // Monogram Initials fallback
                    $firstChar = !empty($kin['firstName']) ? mb_substr($kin['firstName'], 0, 1, 'UTF-8') : 'F';
                    $lastChar = !empty($kin['lastName']) ? mb_substr($kin['lastName'], 0, 1, 'UTF-8') : 'R';
                    $initials = strtoupper($firstChar . $lastChar);
                    $hasCustomPic = !empty($kin['profilePics']) && !str_contains($kin['profilePics'], 'avatarM.png') && !str_contains($kin['profilePics'], 'avatarF.png');

                    // Branch origin label
                    $branchText = !empty($kin['famCode']) ? $kin['famCode'] . ' ' . __('kinship.family_branch', 'Branch') : '';

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
                <div class="p-3.5 rounded-4 kinship-item-card position-relative {{ $kinCarousel ? 'kinship-item-card--scroll' : '' }}" 
                     id="kinCard_{{ $kinId }}" 
                     data-kin="{{ $kinDataJson }}"
                     data-kin-category="{{ $kinCategory }}"
                     x-show="activeFilter === 'all' || activeFilter === '{{ $kinCategory }}'"
                     role="button"
                     tabindex="0"
                     aria-label="View {{ $kinName }} kinship details"
                     style="transition: all 0.2s ease; cursor: pointer; background-color: var(--kin-card-bg, #f8fafc); border: 1px solid var(--kin-card-border, #e2e8f0);">

                    <!-- Dismiss Button (Top Right) with >= 44px safe touch target -->
                    <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none btn-dismiss-kin position-absolute" 
                            data-user-id="{{ $kinId }}" 
                            aria-label="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" 
                            title="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" 
                            style="top: 8px; right: 8px; width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; z-index: 2;">
                        <i class="bi bi-x-lg" style="font-size: 0.85rem;"></i>
                    </button>

                    <!-- Subtle Genealogy Breadcrumbs (Option 3 combined into Option 1) -->
                    <div class="kin-lineage-breadcrumb d-flex align-items-center gap-1.5 rounded-pill px-2.5 py-1 mb-2.5" 
                         style="background: #f1f5f9; border: 1px solid #e2e8f0; font-size: 0.72rem; color: #475569; width: fit-content; max-width: calc(100% - 44px);">
                        <span class="fw-bold text-dark" style="color: #0f172a;">{{ __('kinship.you', 'You') }}</span>
                        <i class="bi bi-arrow-right text-muted" style="font-size: 0.65rem;"></i>
                        <span class="text-muted text-truncate">{{ $lineagePathLabel }}</span>
                        <i class="bi bi-arrow-right text-muted" style="font-size: 0.65rem;"></i>
                        <span class="fw-bold text-truncate" style="color: var(--brand-primary, #8B152B);">{{ $kinName }}</span>
                    </div>

                    <!-- Identity Row: Avatar + Name + Match Badge -->
                    <div class="d-flex align-items-center gap-3 mb-3 pe-4" style="min-width: 0;">
                        <div class="position-relative flex-shrink-0">
                            @if($hasCustomPic)
                                <img src="{{ $kinAvatar }}" alt="{{ $kinName }}" class="rounded-circle border shadow-sm" width="48" height="48" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" 
                                     style="width: 48px; height: 48px; background-color: #1e293b; font-size: 1rem; letter-spacing: -0.02em;">
                                    {{ $initials }}
                                </div>
                            @endif
                            <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" 
                                  style="width: 12px; height: 12px;" title="Lineage verified"></span>
                        </div>
                        <div class="text-truncate" style="min-width: 0; flex: 1;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-bold kin-member-name text-truncate" title="{{ $kinName }}" style="font-size: 0.98rem; line-height: 1.25; color: var(--text-color, #0f172a);">{{ $kinName }}</span>
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold kin-match-pill" style="font-size: 0.72rem; background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6;">{{ $score }}% {{ __('kinship.match', 'Match') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1.5 mt-1 flex-wrap" style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                                <span style="color: #be123c; font-size: 0.75rem;">•</span>
                                <span class="fw-semibold kin-type-label">{{ $kinType }}</span>
                                @if(!empty($branchText))
                                    <span class="text-muted">•</span>
                                    <span>{{ $branchText }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Heritage Clue Banner -->
                    <div class="kin-heritage-clue rounded-3 p-2.5 px-3 mb-3 d-flex align-items-center gap-2" 
                         style="background: #fefce8; border: 1px solid #fef08a; color: #713f12; font-size: 0.825rem; font-weight: 500;">
                        <i class="bi bi-diagram-3-fill flex-shrink-0" style="color: #d97706; font-size: 0.95rem;"></i>
                        <span class="text-truncate" title="{{ $primaryReason }}">{{ $primaryReason }}</span>
                    </div>

                    <!-- Connect Action Button (Full Width, tactile, min-height 44px) -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold btn-connect-kin d-flex align-items-center justify-content-center gap-1.5 shadow-sm" 
                                data-user-id="{{ $kinId }}" 
                                style="font-size: 0.875rem; min-height: 44px; padding: 10px 16px; color: #ffffff !important; background-color: var(--brand-primary, #8B152B); border-color: var(--brand-primary, #8B152B); transition: all 0.2s ease;">
                            <i class="bi bi-person-plus-fill"></i> + {{ __('kinship.connect', 'Connect') }}
                        </button>
                    </div>

                    <!-- Discovery Footer Link -->
                    <div class="text-center mt-2.5 kin-tap-hint" style="font-size: 0.75rem; color: var(--text-muted-aa, #64748b);">
                        <i class="bi bi-info-circle me-1"></i>{{ __('kinship.tap_to_view_tree', 'Tap to view clan lineage & genealogy tree ›') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
