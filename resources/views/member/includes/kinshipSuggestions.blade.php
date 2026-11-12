{{-- Kinship Radar widget: Facebook-Style Suggested Kin & In-Laws Carousel. Does not occupy space when there is no new suggestion. --}}
@php
    $suggestedKin = $suggestedKin ?? [];
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
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0) !important; overflow: hidden;">
    
    <div class="card-body p-3 p-md-4">
        <!-- Top Row: Radar Active badge + Settings & Close Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-2.5">
            <div class="d-flex align-items-center gap-2">
                @if($radarEnabled)
                <span class="badge rounded-pill kin-radar-badge d-inline-flex align-items-center gap-1.5" 
                      style="background: #fdf2f2; color: #8B152B; border: none; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; line-height: 1;">
                    <span class="kin-radar-pulse" style="width: 6px; height: 6px; background-color: #8B152B; border-radius: 50%;"></span>
                    {{ __('kinship.radar_active', 'Kinship Radar Active') }}
                </span>
                @else
                <span class="badge rounded-pill kin-radar-badge-paused d-inline-flex align-items-center gap-1.5" 
                      style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; line-height: 1;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #94a3b8; display: inline-block;"></span>
                    {{ __('kinship.radar_paused', 'Kinship Radar Paused') }}
                </span>
                @endif
            </div>

            <!-- Controls: 3-Dots Settings & Close/Dismiss Widget -->
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-sm btn-link text-muted p-1 d-flex align-items-center justify-content-center btn-radar-settings" 
                        title="{{ __('kinship.radar_settings', 'Radar settings') }}" 
                        aria-label="{{ __('kinship.radar_settings', 'Radar settings') }}" 
                        data-bs-toggle="modal" 
                        data-bs-target="#kinshipRadarSettingsModal"
                        style="color: #64748b; font-size: 1.05rem; line-height: 1; text-decoration: none; min-width: 44px; min-height: 44px; border-radius: 8px;">
                    <i class="bi bi-sliders"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-muted p-1 d-flex align-items-center justify-content-center btn-close-kin-widget" 
                        title="{{ __('kinship.dismiss', 'Dismiss suggestions') }}" 
                        aria-label="{{ __('kinship.dismiss', 'Dismiss suggestions') }}"
                        style="color: #94a3b8; font-size: 1.05rem; line-height: 1; text-decoration: none; min-width: 44px; min-height: 44px; border-radius: 8px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Title Row: People Icon + Suggested Kin & Subtitle (Facebook Style) -->
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" 
                 style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #fdf2f2 0%, #fee2e2 100%); color: #8B152B; font-size: 1.25rem;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div style="min-width: 0; flex: 1;">
                <h6 class="fw-bold mb-0 kin-heading text-truncate" style="font-size: 1.05rem; line-height: 1.25; color: var(--text-color, #0f172a); letter-spacing: -0.01em;" title="{{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}">
                    {{ __('kinship.suggested_kin', 'Suggested Kin & In-Laws') }}
                </h6>
                <span class="d-block kin-subtitle text-truncate" style="font-size: 0.8125rem; color: var(--text-muted, #64748b); line-height: 1.35; margin-top: 2px;" title="{{ __('kinship.lineage_matches_subtitle', 'Lineage matches you may know') }}">
                    {{ __('kinship.lineage_matches_subtitle', 'Lineage matches you may know') }}
                </span>
            </div>
        </div>

        @if(!$radarEnabled)
        <!-- Paused State -->
        <div class="p-3 text-center rounded-3 my-2" style="background-color: var(--card-bg, #f8fafc); border: 1px dashed var(--border-color, #cbd5e1);">
            <i class="bi bi-radar text-secondary" style="font-size: 1.75rem;"></i>
            <h6 class="fw-bold mt-2 mb-1" style="font-size: 0.88rem; color: var(--text-color, #0f172a);">{{ __('kinship.radar_paused_title', 'Kinship Radar is Paused') }}</h6>
            <p class="text-muted mb-3" style="font-size: 0.78rem;">{{ __('kinship.radar_paused_desc', 'Lineage scanning is currently disabled. Turn radar back on anytime to discover relatives.') }}</p>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-resume-radar btn-spin-on-click" data-spin="true" style="font-size: 0.78rem; min-height: 44px;">
                <i class="bi bi-play-circle me-1"></i> {{ __('kinship.resume_radar', 'Resume Radar') }}
            </button>
        </div>
        @else

        <!-- Filter Pills Bar -->
        <div class="d-flex align-items-center gap-2 mb-3 kin-filter-bar overflow-x-auto text-nowrap" style="scrollbar-width: none; gap: 8px;">
            <span class="text-muted fw-normal kin-filter-label" style="font-size: 0.75rem; color: #64748b !important; margin-right: 4px; flex-shrink: 0;">{{ __('kinship.filter_label', 'Filter:') }}</span>
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

        <!-- Facebook-Style Horizontal Carousel Container -->
        <div class="position-relative kin-carousel-viewport">
            <!-- Left scroll arrow (desktop hover) -->
            <button type="button" 
                    class="btn btn-light rounded-circle shadow-sm kin-carousel-arrow kin-carousel-arrow-prev d-none d-md-flex align-items-center justify-content-center" 
                    aria-label="Scroll left"
                    style="position: absolute; left: -10px; top: 40%; transform: translateY(-50%); z-index: 8; width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 50%; background: #ffffff; border: 1px solid #e2e8f0; color: #0f172a; cursor: pointer;">
                <i class="bi bi-chevron-left"></i>
            </button>

            <!-- Card Track (Horizontal Scroll Container with Snap) -->
            <div class="kinship-suggestions-scroll kinship-carousel-track" id="kinshipSuggestionsList"
                 style="display: flex; gap: 14px; overflow-x: auto; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: 4px 2px 14px 2px; scroll-behavior: smooth;">
                
                <!-- 0. Lead Card: Facebook-Style "Upload Your Contacts" Card (RFC-0038) -->
                <div class="kin-card kinship-item-card kinship-item-card--contacts-lead position-relative" 
                     id="kinContactsUploadCard"
                     style="flex: 0 0 240px; min-width: 240px; max-width: 240px; scroll-snap-align: start; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; background: linear-gradient(175deg, var(--brand-primary, #a31621) 0%, #b8202c 50%, var(--brand-secondary, #7a0e16) 100%); color: #ffffff; border: 1.5px solid rgba(255, 255, 255, 0.2); box-shadow: 0 4px 16px rgba(163, 22, 33, 0.25);">
                    
                    <!-- Top Section: Visual Family/Mobile Illustration (Mirroring Facebook Benchmark Reference) -->
                    <div class="position-relative p-3 text-center d-flex align-items-center justify-content-center" 
                         style="height: 210px; width: 100%; background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.02) 100%);">
                        
                        <!-- Dismiss Cross for Lead Card -->
                        <button type="button" 
                                class="btn-close btn-close-white position-absolute top-0 end-0 m-2.5 shadow-xs btn-dismiss-contacts-lead" 
                                aria-label="{{ __('kinship.dismiss_contacts_card', 'Dismiss contact suggestions') }}" 
                                title="{{ __('kinship.dismiss_contacts_card', 'Dismiss contact suggestions') }}"
                                style="z-index: 5; font-size: 0.72rem; min-width: 36px; min-height: 36px;"></button>

                        <!-- Bespoke SVG Illustration: Family Taking Selfies & Connecting on Mobile Phones -->
                        <svg width="170" height="150" viewBox="0 0 180 160" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto" aria-hidden="true">
                            <circle cx="90" cy="80" r="65" fill="#FFFFFF" fill-opacity="0.14"/>
                            <circle cx="50" cy="55" r="35" fill="#EC4899" fill-opacity="0.25"/>
                            <circle cx="130" cy="55" r="35" fill="#F87171" fill-opacity="0.3"/>
                            
                            <!-- Left: Young Kin Selfie taker -->
                            <circle cx="48" cy="45" r="16" fill="#FDE68A"/>
                            <path d="M38 40C38 34 43 30 48 30C53 30 58 34 58 40" stroke="#B45309" stroke-width="2" stroke-linecap="round"/>
                            <rect x="62" y="32" width="14" height="22" rx="3" fill="#1E293B" transform="rotate(15 62 32)"/>
                            <circle cx="68" cy="40" r="2" fill="#38BDF8"/>
                            <path d="M32 75C32 62 40 58 48 58C56 58 64 62 64 75" fill="#F472B6"/>
                            
                            <!-- Right: Grandparent on Phone -->
                            <circle cx="132" cy="45" r="16" fill="#FED7AA"/>
                            <path d="M122 42C122 36 127 32 132 32C137 32 142 36 142 42" stroke="#E2E8F0" stroke-width="3" stroke-linecap="round"/>
                            <path d="M126 50C126 56 138 56 138 50" fill="#FFFFFF"/>
                            <rect x="108" y="36" width="14" height="22" rx="3" fill="#8B5CF6"/>
                            <circle cx="115" cy="44" r="2" fill="#FCD34D"/>
                            <path d="M116 75C116 62 124 58 132 58C140 58 148 62 148 75" fill="#FB7185"/>

                            <!-- Center: Hand holding selfie stick phone with smiling face -->
                            <line x1="90" y1="140" x2="90" y2="85" stroke="#E2E8F0" stroke-width="4" stroke-linecap="round"/>
                            <rect x="65" y="70" width="50" height="32" rx="6" fill="#FFFFFF" stroke="#a31621" stroke-width="2"/>
                            <circle cx="90" cy="84" r="7" fill="#FCD34D"/>
                            <path d="M86 87C87 89 93 89 94 87" stroke="#92400E" stroke-width="1.2" stroke-linecap="round"/>
                            
                            <!-- Sparkle Stars -->
                            <path d="M90 20L92 25L97 27L92 29L90 34L88 29L83 27L88 25L90 20Z" fill="#FDE047"/>
                            <path d="M155 90L156.5 94L160.5 95.5L156.5 97L155 101L153.5 97L149.5 95.5L153.5 94L155 90Z" fill="#FDE047"/>
                            <path d="M25 95L26.5 98L29.5 99.5L26.5 101L25 104L23.5 101L20.5 99.5L23.5 98L25 95Z" fill="#FFFFFF"/>
                        </svg>
                    </div>

                    <!-- Bottom Content Area -->
                    <div class="p-3 d-flex flex-column flex-grow-1 justify-content-between" style="background: rgba(15, 23, 42, 0.25); backdrop-filter: blur(8px);">
                        <div>
                            <h6 class="fw-bold text-white mb-1.5" style="font-size: 1rem; line-height: 1.25; letter-spacing: -0.01em;">
                                {{ __('kinship.upload_contacts_title', 'Upload your contacts') }}
                            </h6>
                            <p class="small text-white-50 mb-3" style="font-size: 0.78rem; line-height: 1.38;">
                                {{ __('kinship.upload_contacts_desc', 'See who is also on FamilyPlatform and choose which relatives to connect with.') }}
                            </p>
                        </div>

                        <!-- Action Button: Upload / Find Kin (Facebook Benchmark Match) -->
                        <div>
                            <button type="button" 
                                    class="btn btn-light fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-sm btn-upload-contacts-lead btn-spin-on-click"
                                    data-spin="true"
                                    aria-label="{{ __('kinship.upload_contacts_btn', 'Upload') }}"
                                    style="border-radius: 10px; min-height: 44px; font-size: 0.88rem; font-weight: 700; color: var(--brand-primary, #a31621) !important; background-color: #ffffff; border: none; white-space: nowrap; flex-shrink: 0; transition: transform 0.15s ease;">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>{{ __('kinship.upload_contacts_btn', 'Upload') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                @foreach($suggestedKin as $kin)
                    @php
                        $kinAvatar = !empty($kin['profilePics']) ? (str_starts_with($kin['profilePics'], '/') ? $kin['profilePics'] : '/resources/images/profile/' . $kin['profilePics']) : '/resources/images/profile/avatarM.png';
                        $rawName = trim(($kin['firstName'] ?? '') . ' ' . ($kin['lastName'] ?? ''));
                        $radarViewerId = (string)($_SESSION['auth']['id'] ?? ($_SESSION['id'] ?? ''));
                        $visibleRadarFamCode = (!empty($kin['famCode']) && \App\controller\BaseController::viewerCanSeeFamilyCode((string)$kin['famCode'], $radarViewerId)) ? (string)$kin['famCode'] : '';
                        $visibleRadarFamilyCode2 = (!empty($kin['familyCode2']) && \App\controller\BaseController::viewerCanSeeFamilyCode((string)$kin['familyCode2'], $radarViewerId)) ? (string)$kin['familyCode2'] : '';
                        $kinName = !empty($rawName) ? ucwords(strtolower($rawName)) : ($visibleRadarFamCode !== '' ? __('kinship.family_relative', 'Family Relative') . ' (' . $visibleRadarFamCode . ')' : __('kinship.family_relative', 'Family Relative'));
                        $kinId = $kin['user_id'] ?? '';
                        $kinType = $kin['kinship_type'] ?? 'Potential Kin';
                        $primaryReason = str_ireplace('Shared Family Surname', 'Shared Surname', (string)($kin['primary_reason'] ?? 'Shared family heritage network'));
                        $score = $kin['confidence_score'] ?? 75;
                        $rawReasons = !empty($kin['reasons']) && is_array($kin['reasons']) ? $kin['reasons'] : [$primaryReason];
                        $reasons = array_map(function($r) { return str_ireplace('Shared Family Surname', 'Shared Surname', (string)$r); }, $rawReasons);
                        $mutualCount = (int)($kin['mutual_kin_count'] ?? 0);

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
                        $branchText = $visibleRadarFamCode !== '' ? $visibleRadarFamCode . ' ' . __('kinship.family_branch', 'Branch') : '';

                        $kinPayload = [
                            'id' => (string)$kinId,
                            'name' => $kinName,
                            'avatar' => $kinAvatar,
                            'score' => $score,
                            'type' => $kinType,
                            'primaryReason' => $primaryReason,
                            'reasons' => $reasons,
                            'famCode' => $visibleRadarFamCode,
                            'familyCode2' => $visibleRadarFamilyCode2,
                            'maidenName' => $kin['maiden_name'] ?? '',
                            'mutualKinCount' => $mutualCount,
                        ];
                        $kinDataJson = htmlspecialchars(json_encode($kinPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    @endphp

                    <!-- Individual Facebook-Style Kinship Card -->
                    <div class="kin-card kinship-item-card position-relative kinship-item-card--carousel" 
                         id="kinCard_{{ $kinId }}" 
                         data-kin="{{ $kinDataJson }}"
                         data-kin-category="{{ $kinCategory }}"
                         x-show="activeFilter === 'all' || activeFilter === '{{ $kinCategory }}'"
                         role="button"
                         tabindex="0"
                         aria-label="View {{ $kinName }} kinship details"
                         style="flex: 0 0 240px; min-width: 240px; max-width: 240px; scroll-snap-align: start; border-radius: 18px; overflow: hidden; display: flex; flex-direction: column; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05); transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease; cursor: pointer;">

                        <!-- Top Section: Large Majestic Portrait Image with Overlay Badges -->
                        <div class="kin-portrait-container position-relative" style="height: 210px; width: 100%; overflow: hidden; background: #0f172a;">
                            @if($hasCustomPic)
                                <img src="{{ $kinAvatar }}" alt="{{ $kinName }}" class="kin-portrait-img" 
                                     style="width: 100%; height: 100%; object-fit: cover; object-position: center 20%; display: block; transition: transform 0.4s ease;" 
                                     loading="lazy" 
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <!-- Regal fallback avatar if image fails -->
                                <div class="kin-portrait-fallback w-100 h-100 align-items-center justify-content-center flex-column text-white" 
                                     style="display: none; background: linear-gradient(145deg, #1e293b 0%, #0f172a 60%, #8B152B 100%);">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 76px; height: 76px; border: 2.5px solid #ffd700; background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); font-size: 1.75rem; font-weight: 800;">
                                        {{ $initials }}
                                    </div>
                                    <span class="small mt-2" style="font-size: 0.72rem; color: #fde047; letter-spacing: 0.5px;">LINEAGE SANCTUARY</span>
                                </div>
                            @else
                                <!-- Dignified royal heritage portrait avatar -->
                                <div class="kin-portrait-fallback w-100 h-100 d-flex align-items-center justify-content-center flex-column text-white" 
                                     style="background: linear-gradient(145deg, #1e293b 0%, #0f172a 60%, #8B152B 100%);">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                         style="width: 76px; height: 76px; border: 2.5px solid #ffd700; background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); font-size: 1.75rem; font-weight: 800;">
                                        {{ $initials }}
                                    </div>
                                    <span class="small mt-2 fw-semibold" style="font-size: 0.72rem; color: #fde047; letter-spacing: 0.5px;">LINEAGE SANCTUARY</span>
                                </div>
                            @endif

                            <!-- Floating Glassmorphic Kinship Tier Badge (Top Left) -->
                            <div class="position-absolute" style="top: 10px; left: 10px; z-index: 2;">
                                <span class="badge rounded-pill kin-type-badge d-inline-flex align-items-center gap-1" 
                                      style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: 0 2px 6px rgba(0,0,0,0.25);">
                                    <i class="bi bi-shield-check" style="font-size: 0.75rem; color: #ffd700;"></i>
                                    {{ $kinType }}
                                </span>
                            </div>

                            <!-- Floating Match Score Badge (Top Right) -->
                            <div class="position-absolute" style="top: 10px; right: 10px; z-index: 2;">
                                <span class="badge rounded-pill kin-match-pill" 
                                      style="font-size: 0.72rem; font-weight: 800; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(4px); color: #8B152B; padding: 4px 9px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); border: 1px solid rgba(139, 21, 43, 0.15);">
                                    {{ $score }}% {{ __('kinship.match', 'Match') }}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Section: Card Body with Name, Mutual Connection Context & Action Buttons -->
                        <div class="p-3 d-flex flex-column flex-grow-1" style="background-color: var(--card-bg, #ffffff);">
                            <!-- Full Name (Clamped with zero mid-word truncation) -->
                            <h6 class="fw-bold kin-member-name mb-1 text-truncate" 
                                title="{{ $kinName }}" 
                                style="font-size: 1rem; color: var(--text-color, #0f172a); line-height: 1.25; letter-spacing: -0.01em;">
                                {{ $kinName }}
                            </h6>

                            <!-- Mutual Connection / Lineage Context (Exactly like Facebook: [mini-avatar] 1 mutual friend) -->
                            <div class="d-flex align-items-center gap-1.5 mb-3 kin-mutual-context" style="min-height: 22px;">
                                @if($mutualCount > 0)
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                         style="width: 20px; height: 20px; background: #e0e7ff; color: #4338ca; font-size: 0.68rem;">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <span class="small text-muted text-truncate fw-medium" style="font-size: 0.78rem;" title="{{ $mutualCount }} mutual connections">
                                        {{ $mutualCount }} mutual {{ $mutualCount > 1 ? 'relatives' : 'relative' }}
                                    </span>
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                         style="width: 20px; height: 20px; background: #fef3c7; color: #b45309; font-size: 0.68rem;">
                                        <i class="bi bi-diagram-3-fill"></i>
                                    </div>
                                    <span class="small text-muted text-truncate fw-medium" style="font-size: 0.78rem;" title="{{ $primaryReason }}">
                                        {{ $primaryReason }}
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons Row: [+ Connect] and [Remove] (Exact Facebook Reference Match) -->
                            <div class="d-flex align-items-center gap-2 mt-auto" style="gap: 8px;">
                                <!-- Primary Action: + Connect -->
                                <button type="button" 
                                        class="btn btn-primary fw-bold btn-connect-kin flex-grow-1 d-flex align-items-center justify-content-center gap-1 btn-spin-on-click" 
                                        data-spin="true" 
                                        data-user-id="{{ $kinId }}" 
                                        style="min-height: 44px; font-size: 0.84rem; background-color: var(--brand-primary, #8B152B); border: none; border-radius: 10px; color: #ffffff !important; white-space: nowrap; flex-shrink: 0; padding: 8px 12px; transition: all 0.15s ease;">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>+ {{ __('kinship.connect', 'Connect') }}</span>
                                </button>

                                <!-- Secondary Action: Remove -->
                                <button type="button" 
                                        class="btn btn-light fw-semibold btn-dismiss-kin d-flex align-items-center justify-content-center" 
                                        data-user-id="{{ $kinId }}" 
                                        aria-label="{{ __('kinship.remove', 'Remove') }}" 
                                        title="{{ __('kinship.remove', 'Remove') }}" 
                                        style="min-height: 44px; min-width: 44px; font-size: 0.84rem; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; border-radius: 10px; white-space: nowrap; flex-shrink: 0; padding: 8px 14px; transition: all 0.15s ease;">
                                    <span>{{ __('kinship.remove', 'Remove') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Right scroll arrow (desktop hover) -->
            <button type="button" 
                    class="btn btn-light rounded-circle shadow-sm kin-carousel-arrow kin-carousel-arrow-next d-none d-md-flex align-items-center justify-content-center" 
                    aria-label="Scroll right"
                    style="position: absolute; right: -10px; top: 40%; transform: translateY(-50%); z-index: 8; width: 40px; height: 40px; min-width: 40px; min-height: 40px; border-radius: 50%; background: #ffffff; border: 1px solid #e2e8f0; color: #0f172a; cursor: pointer;">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Section Footer: "See all ›" Button (Exact Facebook Reference Match) -->
        <div class="text-center pt-2.5 mt-2 border-top" style="border-color: var(--border-color, #e2e8f0) !important;">
            <button type="button" 
                    class="btn btn-link text-decoration-none fw-bold d-inline-flex align-items-center gap-1 btn-see-all-kin" 
                    style="color: var(--brand-primary, #8B152B); font-size: 0.9rem; min-height: 44px; padding: 8px 16px;">
                <span>{{ __('kinship.see_all', 'See all') }}</span>
                <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
            </button>
        </div>
        @endif
    </div>
</div>
@endif
