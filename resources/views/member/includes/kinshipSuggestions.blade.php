{{-- Kinship Radar widget. Does not occupy space when there is no new suggestion. --}}
@php
    $suggestedKin = $suggestedKin ?? [];
    // The in-feed (mobile) copy scrolls horizontally like the Reels tray; the
    // desktop sidebar copy stays a vertical stack. The widget id is the existing
    // discriminator between the two placements.
    $kinCarousel = ($kinshipWidgetId ?? 'kinshipRadarWidget') === 'kinshipRadarWidgetMobile';

    $hasPaternal = false;
    $hasInLaws = false;
    $hasMaternal = false;
    foreach ($suggestedKin as $k) {
        $t = strtolower($k['kinship_type'] ?? '');
        if (str_contains($t, 'paternal') || str_contains($t, 'clan') || str_contains($t, 'surname')) {
            $hasPaternal = true;
        } elseif (str_contains($t, 'in-law') || str_contains($t, 'inlaw')) {
            $hasInLaws = true;
        } elseif (str_contains($t, 'maternal') || str_contains($t, 'mother') || str_contains($t, 'maiden')) {
            $hasMaternal = true;
        }
    }
    $radarSettings = $radarSettings ?? (!empty($_SESSION['id']) ? \App\services\KinshipEngineService::getRadarSettings((string)$_SESSION['id']) : []);
    $radarEnabled = (bool)($radarSettings['radar_enabled'] ?? true);
@endphp
@if(!empty($suggestedKin) || !$radarEnabled)
<div class="card border-0 shadow-sm mb-4 kinship-radar-widget" 
     id="{{ $kinshipWidgetId ?? 'kinshipRadarWidget' }}" 
     x-data="{ activeFilter: 'all' }"
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0) !important;">
    <div class="card-body p-3 p-md-3">
        <!-- Widget Header: Radar Active & Settings above Suggested Kin -->
        <div class="mb-3.5" style="margin-bottom: 16px;">
            <!-- Top Row: Radar Active badge + Settings icon button -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                @if($radarEnabled)
                <span class="badge rounded-pill kin-radar-badge d-inline-flex align-items-center gap-1.5" 
                      style="background: #fdf2f2; color: #8B152B; border: none; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; line-height: 1;">
                    <span class="kin-radar-pulse" style="width: 6px; height: 6px; background-color: #8B152B;"></span>
                    {{ __('kinship.radar_active', 'Radar Active') }}
                </span>
                @else
                <span class="badge rounded-pill kin-radar-badge-paused d-inline-flex align-items-center gap-1.5" 
                      style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; line-height: 1;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #94a3b8; display: inline-block;"></span>
                    {{ __('kinship.radar_paused', 'Radar Paused') }}
                </span>
                @endif
                <button type="button" class="btn btn-sm btn-link text-muted p-1 d-flex align-items-center justify-content-center btn-radar-settings" 
                        title="{{ __('kinship.radar_settings', 'Radar settings') }}" 
                        aria-label="{{ __('kinship.radar_settings', 'Radar settings') }}" 
                        data-bs-toggle="modal" 
                        data-bs-target="#kinshipRadarSettingsModal"
                        style="color: #64748b; font-size: 1.05rem; line-height: 1; text-decoration: none; min-width: 44px; min-height: 44px; border-radius: 8px;">
                    <i class="bi bi-sliders"></i>
                </button>
            </div>

            <!-- Title Row: People Icon + Suggested Kin & Subtitle (Un-squeezed) -->
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 d-flex align-items-center justify-content-center" 
                     style="width: 38px; height: 38px; border-radius: 12px; background: #fdf2f2; color: #8B152B; font-size: 1.15rem; flex-shrink: 0;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h6 class="fw-bold mb-0 kin-heading text-truncate" style="font-size: 0.95rem; line-height: 1.25; color: var(--text-color, #0f172a); letter-spacing: -0.01em;" title="{{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}">
                        {{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}
                    </h6>
                    <span class="d-block kin-subtitle text-truncate" style="font-size: 0.78rem; color: var(--text-muted, #64748b); line-height: 1.35; margin-top: 2px;" title="{{ __('kinship.lineage_matches_subtitle', 'Lineage matches you may know') }}">
                        {{ __('kinship.lineage_matches_subtitle', 'Lineage matches you may know') }}
                    </span>
                </div>
            </div>
        </div>

        @if(!$radarEnabled)
        <!-- Paused State -->
        <div class="p-3 text-center rounded-3 my-2" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
            <i class="bi bi-radar text-secondary" style="font-size: 1.75rem;"></i>
            <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.88rem; color: #0f172a;">{{ __('kinship.radar_paused_title', 'Kinship Radar is Paused') }}</h6>
            <p class="text-muted mb-3" style="font-size: 0.78rem;">{{ __('kinship.radar_paused_desc', 'Lineage scanning is currently disabled. Turn radar back on anytime to discover relatives.') }}</p>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-resume-radar btn-spin-on-click" data-spin="true" style="font-size: 0.78rem; min-height: 44px;">
                <i class="bi bi-play-circle me-1"></i> {{ __('kinship.resume_radar', 'Resume Radar') }}
            </button>
        </div>
        @else

        <!-- Filter Pills Bar (Exact Reference Match) -->
        <div class="d-flex align-items-center gap-1.5 mb-3 kin-filter-bar overflow-x-auto text-nowrap" style="scrollbar-width: none;">
            <span class="text-muted fw-normal" style="font-size: 0.75rem; color: #94a3b8 !important; margin-right: 2px;">{{ __('kinship.filter_label', 'Filter:') }}</span>
            <button type="button" 
                    @click="activeFilter = 'all'" 
                    class="btn btn-sm rounded-pill btn-filter-kin" 
                    :class="activeFilter === 'all' ? 'active' : ''"
                    data-filter="all" 
                    style="font-size: 0.75rem; padding: 4px 12px; line-height: 1.3;">
                {{ __('kinship.all_matches', 'All Matches') }} ({{ count($suggestedKin) }})
            </button>
            @if($hasPaternal)
            <button type="button" 
                    @click="activeFilter = 'paternal'" 
                    class="btn btn-sm rounded-pill btn-filter-kin" 
                    :class="activeFilter === 'paternal' ? 'active' : ''"
                    data-filter="paternal" 
                    style="font-size: 0.75rem; padding: 4px 12px; line-height: 1.3;">
                {{ __('kinship.paternal_clan', 'Paternal Clan') }}
            </button>
            @endif
            @if($hasInLaws)
            <button type="button" 
                    @click="activeFilter = 'inlaws'" 
                    class="btn btn-sm rounded-pill btn-filter-kin" 
                    :class="activeFilter === 'inlaws' ? 'active' : ''"
                    data-filter="inlaws" 
                    style="font-size: 0.75rem; padding: 4px 12px; line-height: 1.3;">
                {{ __('kinship.in_laws', 'In-Laws') }}
            </button>
            @endif
            @if($hasMaternal)
            <button type="button" 
                    @click="activeFilter = 'maternal'" 
                    class="btn btn-sm rounded-pill btn-filter-kin" 
                    :class="activeFilter === 'maternal' ? 'active' : ''"
                    data-filter="maternal" 
                    style="font-size: 0.75rem; padding: 4px 12px; line-height: 1.3;">
                {{ __('kinship.maternal_clan', 'Maternal Clan') }}
            </button>
            @endif
        </div>

        <!-- Suggestion Cards List -->
        <div class="{{ $kinCarousel ? 'kinship-suggestions-scroll' : 'd-flex flex-column gap-3' }}" id="kinshipSuggestionsList">
            @foreach($suggestedKin as $kin)
                @php
                    $kinAvatar = !empty($kin['profilePics']) ? (str_starts_with($kin['profilePics'], '/') ? $kin['profilePics'] : '/resources/images/profile/' . $kin['profilePics']) : '/resources/images/profile/avatarM.png';
                    $rawName = trim(($kin['firstName'] ?? '') . ' ' . ($kin['lastName'] ?? ''));
                    $kinName = !empty($rawName) ? ucwords(strtolower($rawName)) : (!empty($kin['famCode']) ? __('kinship.family_relative', 'Family Relative') . ' (' . $kin['famCode'] . ')' : __('kinship.family_relative', 'Family Relative'));
                    $kinId = $kin['user_id'] ?? '';
                    $kinType = $kin['kinship_type'] ?? 'Potential Kin';
                    $primaryReason = str_ireplace('Shared Family Surname', 'Shared Surname', (string)($kin['primary_reason'] ?? 'Shared family heritage network'));
                    $score = $kin['confidence_score'] ?? 75;
                    $rawReasons = !empty($kin['reasons']) && is_array($kin['reasons']) ? $kin['reasons'] : [$primaryReason];
                    $reasons = array_map(function($r) { return str_ireplace('Shared Family Surname', 'Shared Surname', (string)$r); }, $rawReasons);

                    // Category mapping for client-side filtering
                    $kinTypeLower = strtolower($kinType);
                    if (str_contains($kinTypeLower, 'paternal') || str_contains($kinTypeLower, 'clan') || str_contains($kinTypeLower, 'surname')) {
                        $kinCategory = 'paternal';
                    } elseif (str_contains($kinTypeLower, 'in-law') || str_contains($kinTypeLower, 'inlaw')) {
                        $kinCategory = 'inlaws';
                    } elseif (str_contains($kinTypeLower, 'maternal') || str_contains($kinTypeLower, 'mother') || str_contains($kinTypeLower, 'maiden')) {
                        $kinCategory = 'maternal';
                    } else {
                        $kinCategory = 'other';
                    }

                    // Monogram Initials
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
                <div class="kin-card kinship-item-card position-relative {{ $kinCarousel ? 'kinship-item-card--scroll' : '' }}" 
                     id="kinCard_{{ $kinId }}" 
                     data-kin="{{ $kinDataJson }}"
                     data-kin-category="{{ $kinCategory }}"
                     x-show="activeFilter === 'all' || activeFilter === '{{ $kinCategory }}'"
                     role="button"
                     tabindex="0"
                     aria-label="View {{ $kinName }} kinship details"
                     style="transition: all 0.2s ease; cursor: pointer; background: var(--kin-card-bg, #ffffff); border: 1px solid var(--kin-card-border, #eef2f6); border-radius: 16px; padding: 14px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);">

                    <!-- Dismiss Button (Top Right) - 44x44 safe touch area -->
                    <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none btn-dismiss-kin position-absolute min-hit-target" 
                            data-user-id="{{ $kinId }}" 
                            aria-label="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" 
                            title="{{ __('kinship.dismiss', 'Dismiss suggestion') }}" 
                            style="top: 8px; right: 8px; width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; z-index: 2; color: #cbd5e1;">
                        <i class="bi bi-x-lg" style="font-size: 0.825rem;"></i>
                    </button>

                    <!-- Top Row: Avatar + Names + Match Pill -->
                    <div class="d-flex align-items-center gap-3 mb-3 pe-4" style="min-width: 0; gap: 12px; margin-bottom: 12px;">
                        <div class="position-relative flex-shrink-0">
                            @if($hasCustomPic)
                                <img src="{{ $kinAvatar }}" alt="{{ $kinName }}" class="rounded-circle border" width="44" height="44" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="rounded-circle text-white fw-bold align-items-center justify-content-center" 
                                     style="display: none; width: 44px; height: 44px; background: #273344; font-size: 0.95rem; letter-spacing: -0.02em;">
                                    {{ $initials }}
                                </div>
                            @else
                                <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 44px; height: 44px; background: #273344; font-size: 0.95rem; letter-spacing: -0.02em;">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                        <div class="text-truncate" style="min-width: 0; flex: 1;">
                            <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: 8px;">
                                <span class="fw-bold kin-member-name text-truncate" title="{{ $kinName }}" style="font-size: 0.92rem; color: var(--text-color, #0f172a); line-height: 1.2;">{{ $kinName }}</span>
                                <span class="badge rounded-pill kin-match-pill" style="font-size: 0.68rem; font-weight: 700; background: #ffeef0; color: #b8202c; padding: 2px 7px; line-height: 1.2;">{{ $score }}% {{ __('kinship.match', 'Match') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1 flex-wrap" style="font-size: 0.75rem; color: var(--text-muted, #64748b); gap: 6px; margin-top: 4px;">
                                <span class="fw-medium kin-type-label">{{ $kinType }}</span>
                                @if(!empty($branchText))
                                    <span>{{ $branchText }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Heritage Clue Banner (Exact Reference Match) -->
                    <div class="kin-heritage-clue rounded-2 p-2 px-2.5 mb-3.5 d-flex align-items-center gap-2" 
                         style="background: #fffbeb; border: 1px solid #fef08a; color: #78350f; font-size: 0.75rem; font-weight: 600; border-radius: 8px; margin-bottom: 14px;">
                        <i class="bi bi-key-fill flex-shrink-0" style="color: #d97706; font-size: 0.85rem;"></i>
                        <span class="text-truncate" title="{{ $primaryReason }}">{{ $primaryReason }}</span>
                    </div>

                    <!-- Full-Width Connect Action Button (Exact Reference Match) -->
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold btn-connect-kin d-flex align-items-center justify-content-center gap-1.5 shadow-sm text-white" 
                                data-user-id="{{ $kinId }}" 
                                style="font-size: 0.85rem; min-height: 44px; padding: 9px 16px; background-color: var(--brand-primary, #8B152B); border: none; transition: all 0.2s ease;">
                            <i class="bi bi-person-plus-fill"></i> + {{ __('kinship.connect', 'Connect') }}
                        </button>
                    </div>

                    <!-- Subtle Footer Hint (Exact Reference Match) -->
                    <div class="text-center mt-2 kin-tap-hint" style="font-size: 0.72rem; color: var(--text-muted, #94a3b8);">
                        <i class="bi bi-info-circle me-1"></i>{{ __('kinship.tap_to_view_tree', 'Tap to view clan lineage & genealogy tree ›') }}
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif
