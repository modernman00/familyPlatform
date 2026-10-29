@extends ('layouts.profileBase')
@section('title', 'Family Tree & Organogram')
@section('data-page-id', 'organogram')
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css?v={{ time() }}">
@endpush
@section('content')

<div class="organogram-container-fluid">
    <!-- Header Section -->
    <div class="organogram-header">
        <div class="header-container">
            <div class="header-top-row">
                <div>
                    <h2 class="organogram-title fw-bold" id="treeHeaderTitle">
                        <i class="bi bi-diagram-3"></i>
                        @if(($activeLineageScope ?? 'primary') === 'maternal' && !empty($maidenSurname))
                            {{ $data['firstName'] ?? '' }}'s {{ $maidenSurname }} Birth Family Tree
                        @elseif(($activeLineageScope ?? 'primary') === 'inlaw')
                            {{ $data['firstName'] ?? '' }}'s In-Law Lineage Tree
                        @else
                            {{ $data['firstName'] ?? '' }} {{ $data['lastName'] ?? '' }}'s Family Tree
                        @endif
                    </h2>
                    <p class="organogram-subtitle mb-0">
                        @if(($activeLineageScope ?? 'primary') === 'maternal' && !empty($maternalFamCode))
                            Exploring your Birth / Maiden Family lineage (<strong>#{{ $maternalFamCode }}</strong>). Click any person to see their details and full profile.
                        @elseif(($activeLineageScope ?? 'primary') === 'inlaw')
                            Exploring your In-Law lineage (<strong>#{{ $familyCode }}</strong>). Click any person to see their details and full profile.
                        @else
                            Explore your family heritage, partners, and lineage. Click any person to see their details and full profile.
                        @endif
                    </p>
                    @php
                        $currentViewMode = $_GET['mode'] ?? 'canvas';
                        $otherHeldCodes = array_diff($heldCodes ?? [], array_filter([$primaryFamCode ?? '', $maternalFamCode ?? '', $wifeFamCode ?? '']));
                    @endphp
                    @if(!empty($hasMaternalCode) || !empty($wifeFamCode) || !empty($otherHeldCodes))
                    <div class="d-inline-flex flex-wrap align-items-center gap-2 mt-2 p-1 rounded-pill" style="background: rgba(15, 23, 42, 0.78); border: 1px solid rgba(245, 158, 11, 0.35);">
                        <a href="?mode={{ $currentViewMode }}&family_code={{ $primaryFamCode }}&lineage=primary" class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 px-3 {{ ($activeLineageScope ?? 'primary') === 'primary' ? 'btn-warning text-dark shadow-sm' : 'text-white-50' }}" style="min-height: 36px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                            <i class="bi bi-house-heart-fill"></i> Marital / Household Tree (#{{ $primaryFamCode ?? ($data['famCode'] ?? '') }})
                        </a>
                        @if(!empty($hasMaternalCode) && !empty($maternalFamCode))
                        <a href="?mode={{ $currentViewMode }}&family_code={{ $maternalFamCode }}&lineage=birth" class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 px-3 {{ ($activeLineageScope ?? 'primary') === 'maternal' ? 'btn-warning text-dark shadow-sm' : 'text-white-50' }}" style="min-height: 36px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                            <i class="bi bi-diagram-3-fill"></i> Birth / Maiden Family (#{{ $maternalFamCode }})
                        </a>
                        @endif
                        @if(!empty($wifeFamCode))
                        <a href="?mode={{ $currentViewMode }}&family_code={{ $wifeFamCode }}&lineage=inlaw" class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 px-3 {{ ($activeLineageScope ?? 'primary') === 'inlaw' ? 'btn-warning text-dark shadow-sm' : 'text-white-50' }}" style="min-height: 36px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                            <i class="bi bi-people-fill"></i> In-Law Lineage (#{{ $wifeFamCode }})
                        </a>
                        @endif
                        @foreach($otherHeldCodes as $ohc)
                        <a href="?mode={{ $currentViewMode }}&family_code={{ $ohc }}" class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 px-3 {{ ($familyCode ?? '') === $ohc ? 'btn-warning text-dark shadow-sm' : 'text-white-50' }}" style="min-height: 36px; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                            <i class="bi bi-diagram-2"></i> #{{ $ohc }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Actions (Search & Config) -->
                <div class="header-actions d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-2">
                    @if(empty($isSelf) && empty($graphData['has_built_tree']))
                    <button type="button" class="btn btn-warning text-dark px-3 py-2 fw-bold d-inline-flex align-items-center shadow-sm btn-prompt-tree-action" id="btnHeaderPromptBuildTree" onclick="window.promptMemberToBuildTree()" style="border-radius: 50px; min-height: 44px; white-space: nowrap;" title="Invite relative to connect their branch of the family tree">
                        <i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite {{ explode(' ', trim($data['firstName'] ?? 'Them'))[0] }} to Connect Branch
                    </button>
                    @endif
                    <button type="button" class="btn-action" onclick="centerOnMe()" title="Focus on Me">
                        <i class="bi bi-person-bounding-box text-info"></i> Focus on Me
                    </button>
                    <button type="button" class="btn-action" onclick="if(window.traceCardLineage) window.traceCardLineage();" title="Trace Lineage">
                        <i class="bi bi-compass-fill text-warning"></i> Trace Lineage
                    </button>
                    <button type="button" class="btn-action btn-gold" onclick="fitTreeScreen()" title="Fit Family to Viewport">
                        <i class="bi bi-aspect-ratio"></i> Fit Family
                    </button>
                    @if(empty($isReadOnly))
                    <button type="button" class="btn-action" onclick="openLineageQuestModal()" title="Lineage Quests on WhatsApp" style="border-color: rgba(16, 185, 129, 0.4); color: #10b981;">
                        <i class="bi bi-compass-fill text-success"></i> Quests
                    </button>
                    <button type="button" class="btn-action" onclick="openFamilyFlyerModal()" title="Generate WhatsApp Family Status Flyer" style="border-color: rgba(245, 158, 11, 0.4); color: #f59e0b;">
                        <i class="bi bi-card-image text-warning"></i> WhatsApp Flyer
                    </button>
                    <button type="button" class="btn-action" onclick="openTimeCapsuleModal()" title="Digital Time Capsule Vault" style="border-color: rgba(184,32,44, 0.4); color: #ef6b73;">
                        <i class="bi bi-hourglass-split text-primary"></i> Time Capsule
                    </button>
                    <button type="button" id="configureFamilyBtn" class="btn-action" onclick="switchTreeViewMode('studio')" title="Open Family Studio to update relatives & lineage directly on this page">
                        <i class="bi bi-stars text-warning"></i> Lineage Studio
                    </button>
                    <button type="button" class="btn-action" id="manageRelativesBtn" onclick="openManageRelativesModal()" title="Remove unrecognised names from family tree" style="border-color: rgba(239, 68, 68, 0.4); color: #ef4444;">
                        <i class="bi bi-person-x-fill text-danger"></i> Remove Relatives
                    </button>
                    @endif
                    <div class="search-box-wrapper" id="memberSearchWrapper" style="min-width: 240px;">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="memberSearchInput" class="member-search-input w-100" placeholder="Find relative in tree..." autocomplete="off">
                        <div id="searchDropdown" class="search-dropdown"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Modal View Switcher (Luxury Dark Segmented Control) -->
    <div class="tree-mode-switcher-container text-center mb-1 d-flex flex-wrap align-items-center justify-content-center gap-2">
        @if(empty($isReadOnly))
        <div class="d-flex align-items-center gap-2 flex-shrink-0 tree-quick-actions">
            <button type="button" onclick="openInviteFamilyModal()" class="btn btn-primary fw-bold text-nowrap shadow-sm d-inline-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #a31621 0%, #b8202c 100%); border: none; border-radius: 10px; min-height: 44px; font-size: 0.9rem;">
                <i class="bi bi-send-fill me-1"></i> Invite Family
            </button>
            <button type="button" onclick="openLineageQuestModal()" class="btn btn-success fw-bold text-white text-nowrap shadow-sm d-inline-flex align-items-center justify-content-center" style="border-radius: 10px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; min-height: 44px; font-size: 0.9rem;">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Quests
            </button>
        </div>
        @endif
        <div class="layout-segmented-control" role="group" aria-label="Layout View Modes">
            <button type="button" id="btnModeCanvas" class="layout-segment-btn active" onclick="switchTreeViewMode('canvas')" title="Panoramic Dynasty: Full balanced wide tree of all siblings & branches">
                <i class="bi bi-diagram-3-fill text-info"></i>
                <span>Panoramic Dynasty</span>
            </button>
            <button type="button" id="btnModeLineage" class="layout-segment-btn" onclick="switchTreeViewMode('lineage')" title="Direct Lineage Focus: Highlights direct ancestral path in gold">
                <i class="bi bi-compass-fill text-warning"></i>
                <span>Direct Lineage</span>
            </button>
            <button type="button" id="btnModePedigree" class="layout-segment-btn" onclick="switchTreeViewMode('pedigree')" title="Pedigree Outline: Mobile hierarchical list">
                <i class="bi bi-list-nested text-success"></i>
                <span>Pedigree Outline</span>
            </button>
            <button type="button" id="btnModeFanChart" class="layout-segment-btn" onclick="switchTreeViewMode('fanchart')" title="Radial 360° Fan: Concentric pedigree wheel with colour-coded ancestral quadrants">
                <i class="bi bi-pie-chart-fill text-warning"></i>
                <span>Radial 360° Fan</span>
            </button>
            <button type="button" id="btnModeGlobe" class="layout-segment-btn" onclick="switchTreeViewMode('globe')" title="3D Dynasty Globe: Spatial migration & global diaspora across continents">
                <i class="bi bi-globe-americas text-info"></i>
                <span>Dynasty Globe</span>
            </button>
            <button type="button" id="btnModeStudio" class="layout-segment-btn" onclick="switchTreeViewMode('studio')" title="Family Studio: Curate and update relatives, unions, and lineage directly">
                <i class="bi bi-stars text-warning"></i>
                <span>Family Studio</span>
                <span class="badge bg-warning text-dark ms-1 px-1.5 py-0.5 rounded-pill" style="font-size: 0.68rem; font-weight: 700;">Update Tree</span>
            </button>
        </div>
    </div>

    <!-- Tree Workspace Container -->
    <div class="organogram-container">

        <!-- Integrated Family Studio Discovery Announcement Banner -->
        <div id="treeStudioDiscoveryBanner" class="alert shadow-lg border-0 rounded-4 mb-3 mx-3 d-flex flex-column flex-md-row align-items-center justify-content-between p-3 gap-3" style="background: linear-gradient(135deg, rgba(30, 10, 15, 0.95) 0%, rgba(138, 16, 25, 0.95) 100%); border: 1px solid rgba(251, 191, 36, 0.4); border-left: 5px solid #fbbf24 !important; backdrop-filter: blur(16px); box-shadow: 0 16px 36px rgba(0,0,0,0.4);">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.45); font-size: 1.25rem;">
                    <i class="bi bi-stars"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h6 class="mb-0 fw-bold text-white fs-6">Family Studio is now inside your Family Tree!</h6>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5" style="font-size: 0.68rem; font-weight: 800; letter-spacing: 0.04em;">NEW</span>
                    </div>
                    <p class="mb-0 text-white-50" style="font-size: 0.88rem; line-height: 1.45;">
                        You no longer have to navigate away to update your tree. Easily switch between seeing your interactive tree and adding or curating relatives directly in <strong>Family Studio</strong>!
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button type="button" class="btn btn-warning text-dark px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="switchTreeViewMode('studio')" style="border-radius: 50px; min-height: 44px; font-size: 0.88rem; white-space: nowrap;">
                    <i class="bi bi-pencil-square"></i> Open Family Studio
                </button>
                <button type="button" class="btn btn-outline-light text-white-50 px-2.5 py-2 border-0" onclick="dismissTreeStudioDiscoveryBanner()" title="Dismiss announcement" style="border-radius: 50px; min-height: 44px; min-width: 44px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        @php
            $analysis = $nodeAnalysis ?? [
                'completeness_score' => 20,
                'primary_missing' => 'parents',
                'missing_labels' => ['Parents'],
                'recommended_cta_type' => 'parents',
                'banner_title' => 'Build your family tree!',
                'banner_subtitle' => 'Your tree looks a bit empty. Add your parents and siblings to start building your lineage.',
                'is_flourishing' => false
            ];
        @endphp

        @if(empty($isSelf) && empty($graphData['has_built_tree']))
        <!-- Prominent Lineage Tree Prompt Banner -->
        <div id="promptBuildTreeBanner" class="alert shadow-lg border-0 rounded-4 mb-4 d-flex flex-column flex-md-row align-items-center justify-content-between mx-3 mt-3 p-3 gap-3" style="background: linear-gradient(135deg, rgba(42,10,13, 0.9) 0%, rgba(42,10,13, 0.85) 100%); border: 1px solid rgba(245, 158, 11, 0.3); border-left: 5px solid #f59e0b !important; backdrop-filter: blur(16px); box-shadow: 0 16px 36px rgba(0,0,0,0.4);">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.2); color: #f59e0b; flex-shrink: 0;">
                    <i class="bi bi-bell-fill fs-4"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold text-white fs-6">{{ trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) }} Has Not Connected Their Branch Yet</h6>
                    <p class="mb-0 text-white-50" style="font-size: 0.88rem;">This relative has not added their immediate family members yet. Send them a warm invitation to connect their branch!</p>
                </div>
            </div>
            <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center shadow-sm flex-shrink-0 btn-prompt-tree-action" onclick="window.promptMemberToBuildTree()" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                <i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite {{ explode(' ', trim($data['firstName'] ?? 'Them'))[0] }} to Connect Branch
            </button>
        </div>
        @endif

        @if(empty($isReadOnly))
        <!-- Kinship Auto-Match Discovery Banner ("Is this you on the family tree?") -->
        <div id="kinshipAutoMatchBanner" class="alert shadow-lg border-0 rounded-4 mb-4 d-none flex-column flex-md-row align-items-center justify-content-between mx-3 mt-3 gap-3" style="background: linear-gradient(135deg, rgba(13, 37, 43, 0.95) 0%, rgba(19, 78, 74, 0.85) 100%); border: 1px solid rgba(163,22,33, 0.35); border-left: 5px solid #a31621 !important; backdrop-filter: blur(16px); z-index: 100; position: relative; box-shadow: 0 16px 36px rgba(163,22,33, 0.2) !important;">
            <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center rounded-circle p-2" style="background: rgba(163,22,33, 0.15); min-width: 48px; min-height: 48px;">
                    <i class="bi bi-person-bounding-box fs-3" style="color: #a31621;"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1 justify-content-center justify-content-md-start">
                        <span class="badge" style="background: #a31621; color: #ffffff; font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="bi bi-stars me-1"></i> RELATIVE IDENTIFIED
                        </span>
                        <span id="matchConfidenceBadge" class="badge bg-dark text-light border border-secondary" style="font-size: 0.75rem;">
                            95% Match
                        </span>
                    </div>
                    <h6 class="mb-1 fw-bold text-white fs-6" id="matchHeadline">
                        We found <span id="matchPersonName" style="color: #b8202c;">Relative</span> on this family tree. Is this you?
                    </h6>
                    <p class="mb-0 text-white-50" id="matchRelationshipHint" style="font-size: 0.88rem;">
                        Relative on the family tree
                    </p>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-md-auto justify-content-center justify-content-md-end flex-shrink-0">
                <button type="button" id="btnConfirmMatch" onclick="openClaimMatchModal()" class="btn btn-sm px-4 py-2 fw-bold text-white shadow-sm text-nowrap" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); border-radius: 10px; font-size: 0.9rem; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-check2-circle me-1"></i> Yes, Connect My Account
                </button>
                <button type="button" id="btnDismissMatch" onclick="dismissCurrentMatch()" class="btn btn-dark btn-sm px-3 py-2 fw-semibold text-light border border-secondary text-nowrap" style="border-radius: 10px; font-size: 0.88rem; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    Not Me
                </button>
            </div>
        </div>
        @endif

        <!-- Mode 1: Dynamic Canvas View -->
        <div class="tree-container position-relative" id="treeContainer">
            <!-- Lineage Path Trace Active Banner (KinshipTree In-House Engine) -->
            <div id="lineageTraceBanner" class="position-absolute d-none" style="top: 15px; left: 50%; transform: translateX(-50%); z-index: 106; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(12px); color: #ffffff; padding: 8px 18px; border-radius: 50px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); border: 1.5px solid #f59e0b; display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 0.9rem;">
                <i class="bi bi-diagram-2-fill text-warning"></i>
                <span id="lineageTraceText">Tracing Lineage...</span>
                <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.75rem; cursor: pointer;" onclick="clearLineageTrace();" title="Clear Lineage Trace"></button>
            </div>

            <!-- Offline Status Indicator Badge -->
            <div id="treeOfflineBadge" class="badge bg-warning text-dark d-none position-absolute" style="top: 15px; right: 15px; z-index: 105; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 50px; padding: 6px 14px; font-size: 0.8rem;">
                <i class="bi bi-wifi-off me-1"></i> Offline Cached Mode
            </div>

            <!-- Instructions Guide -->
            <button class="instructions-toggle" id="instructionsToggle" title="View Navigation Guide">
                <i class="bi bi-question-circle"></i>
            </button>

            <div class="instructions collapsed" id="instructions">
                <h4><i class="bi bi-info-circle"></i> Navigation Guide</h4>
                <ul>
                    <li><i class="bi bi-hand-index-thumb"></i> <strong>Click</strong> on any node to view person details & history</li>
                    <li><i class="bi bi-mouse"></i> <strong>Scroll</strong> mouse wheel to zoom in or out</li>
                    <li><i class="bi bi-arrows-move"></i> <strong>Drag</strong> anywhere to pan across the family tree</li>
                    <li><i class="bi bi-plus-circle"></i> Use the <strong>Zoom Buttons</strong> on the bottom right</li>
                </ul>
                <div class="mt-3 pt-2 border-top text-center">
                    <button type="button" class="btn btn-sm btn-primary w-100 fw-bold" onclick="if(window.startAppTour) window.startAppTour();" style="border-radius: 8px;">
                        <i class="bi bi-compass-fill me-2"></i>Interactive App Tour
                    </button>
                </div>
            </div>

            <!-- Dynamic Graph Canvas / Wrapper (KinshipTree Luxury Dark Stage) -->
            <div id="treeStage">
                <!-- Active Lineage Banner -->
                <div id="lineageBanner">
                    <i class="bi bi-stars text-warning fs-5"></i>
                    <span id="lineageBannerText">Tracing Ancestral Lineage</span>
                    <button type="button" class="close-btn" onclick="clearLineageTrace()" title="Clear Lineage Trace">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                <!-- Selected Member Detail Card (Floating Luxury Left Card) -->
                <div id="personDetailsCard">
                    <div class="card-header-flex">
                        <img id="cardAvatar" src="/resources/images/profile/avatarM.png" alt="Avatar" class="card-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                        <button type="button" class="close-btn" onclick="closePersonCard()" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:1.1rem;" title="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div id="cardRole" class="card-title">Family Member</div>
                    <div id="cardName" class="card-name">Family Relative</div>
                    <div id="cardStatusBadge" class="card-status-badge card-status-registered">
                        <i class="bi bi-patch-check-fill"></i> <span>Registered Member</span>
                    </div>

                    <div class="card-meta">
                        <div><i class="bi bi-upc-scan me-1"></i> ID: <strong id="cardId">-</strong></div>
                        <div><i class="bi bi-gender-ambiguous me-1"></i> Gender: <strong id="cardGender">Male</strong></div>
                        <div id="cardSetGenderWrap" style="display: none; margin-top: 6px;">
                            <span style="font-size: 0.8rem; color: #fbbf24;">Gender not set. Mark as:</span>
                            <button type="button" class="btn-action" data-set-gender="Male" style="min-height: 44px; padding: 6px 14px; border-radius: 999px; margin-left: 6px;">Male</button>
                            <button type="button" class="btn-action" data-set-gender="Female" style="min-height: 44px; padding: 6px 14px; border-radius: 999px;">Female</button>
                        </div>
                        <div id="cardOccupationRow"><i class="bi bi-briefcase-fill me-1 text-info"></i> Occupation: <strong id="cardOccupation">Not specified</strong></div>
                        <div id="cardEmailRow"><i class="bi bi-envelope-fill me-1 text-warning"></i> Email: <span id="cardEmail" style="word-break: break-all;">Not provided</span></div>
                        <div id="cardMobileRow"><i class="bi bi-telephone-fill me-1 text-success"></i> Mobile: <span id="cardMobile">Not provided</span></div>
                        <div><i class="bi bi-people-fill me-1"></i> Descendants: <strong id="cardDescendants">0</strong></div>
                        <div id="cardLifespanRow" style="display: none;"><i class="bi bi-hourglass-split me-1 text-warning"></i> Lifespan: <strong id="cardLifespan">-</strong></div>
                    </div>

                    <!-- Conditional Registered: View Profile -->
                    <div id="cardRegisteredActions" style="margin-top: 14px; display: flex; flex-direction: column; gap: 6px;">
                        <a id="cardViewProfileLink" href="#" class="btn-action btn-gold w-100" style="display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; padding: 10px; font-weight: 700; border-radius: 12px;">
                            <i class="bi bi-person-fill"></i> View Profile
                        </a>
                    </div>

                    <!-- Conditional Unregistered: Invite Relative to Claim This Spot -->
                    <div id="cardClaimSpotSection" class="claim-spot-section" style="display: none;">
                        <div class="claim-spot-title">
                            <i class="bi bi-person-plus-fill"></i> Invite Relative to Claim This Spot
                        </div>
                        <div class="claim-spot-subtitle">
                            Send a personal invite so they can claim this spot and share family memories.
                        </div>
                        <div class="claim-spot-btns">
                            <a id="cardWhatsappBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn-action" style="background: #25D366; color: white; border: none; font-size: 0.8rem; padding: 7px 12px; border-radius: 999px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            <a id="cardSmsBtn" href="#" class="btn-action" style="background: #a31621; color: white; border: none; font-size: 0.8rem; padding: 7px 12px; border-radius: 999px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-chat-text-fill"></i> SMS
                            </a>
                            <button type="button" id="cardCopyBtn" onclick="copyCardInviteLink(this)" class="btn-action" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.8rem; padding: 7px 12px; border-radius: 999px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Living Voice Capsule Section -->
                    <div id="cardVoiceCapsuleSection" style="margin-top: 14px; padding: 12px; border-radius: 14px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-mic-fill text-warning fs-6"></i>
                                <span class="fw-bold text-white" style="font-size: 0.82rem; letter-spacing: 0.02em;">LIVING VOICE CAPSULE</span>
                            </div>
                            <span id="voiceCapsuleStatusBadge" class="badge" style="font-size: 0.7rem; background: rgba(255,255,255,0.1); color: #cbd5e1;">None</span>
                        </div>
                        
                        <!-- When audio capsule is present -->
                        <div id="cardVoicePlayerWrapper" style="display: none;">
                            <audio id="cardVoiceAudio" controls preload="none" style="width: 100%; height: 32px; border-radius: 8px; margin-bottom: 6px;"></audio>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-action w-100" onclick="openVoiceCapsuleModal(window.selectedNodeId)" style="font-size: 0.75rem; padding: 4px 8px; background: rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24;">
                                    <i class="bi bi-arrow-repeat me-1"></i> Replace Voice
                                </button>
                                <button type="button" class="btn-action" onclick="deleteVoiceCapsule(window.selectedNodeId)" style="font-size: 0.75rem; padding: 4px 8px; background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5;" title="Delete Voice Capsule">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>

                        <!-- When audio capsule is not yet recorded -->
                        <div id="cardVoiceEmptyWrapper">
                            <p class="text-white-50 mb-2" style="font-size: 0.75rem; line-height: 1.3;">Preserve spoken memories, laughter, and family oral history for eternity.</p>
                            <button type="button" class="btn-action w-100" onclick="openVoiceCapsuleModal(window.selectedNodeId)" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.3), rgba(217, 119, 6, 0.4)); border-color: rgba(245, 158, 11, 0.6); color: #fef3c7; font-weight: 700; font-size: 0.78rem; padding: 6px 12px; justify-content: center;">
                                <i class="bi bi-mic-fill text-warning me-1"></i> Record / Upload Voice Capsule
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Actions inside card -->
                    <div class="card-actions">
                        <div id="cardInLawBranchWrapper" style="display: none; margin-bottom: 8px;">
                            <button type="button" class="btn-action w-100" id="cardToggleInLawBranchBtn" onclick="window.promptOrToggleSpouseInLawBranch(window.selectedNodeId)" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.22), rgba(217, 119, 6, 0.32)); border-color: rgba(245, 158, 11, 0.65); color: #fde68a; font-weight: 700; min-height: 44px; justify-content: center; border-radius: 10px;">
                                <i class="bi bi-people-fill text-warning me-1"></i> <span id="cardToggleInLawBranchText">Show Extended Family</span>
                            </button>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn-action w-100" onclick="traceCardLineage()" style="background: rgba(184,32,44, 0.2); border-color: rgba(239,107,115, 0.4); color: #ecc7c9;">
                                <i class="bi bi-compass-fill text-warning"></i> Trace Lineage
                            </button>
                            <button type="button" class="btn-action" onclick="centerCardNode()" style="flex-shrink: 0;" title="Center on Node">
                                <i class="bi bi-crosshair2"></i> Center
                            </button>
                        </div>
                        <div id="cardRemoveActionWrapper" style="margin-top: 8px; display: none;">
                            <button type="button" class="btn-action w-100" id="cardRemoveNodeBtn" onclick="removeSelectedCardNode()" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: 10px; font-weight: 600; font-size: 0.85rem;">
                                <i class="bi bi-trash3-fill text-danger"></i> Remove from Tree
                            </button>
                        </div>
                    </div>
                </div>


                <!-- Tree SVG Container -->
                <div id="tree">
                    @if(empty($graphData['has_built_tree']))
                        @if(!empty($isSelf))
                            <div class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="min-height: 480px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border: 2px dashed #10b981;">
                                    <i class="bi bi-diagram-3 text-success fs-1"></i>
                                </div>
                                <h3 class="fw-bold text-white mb-2">Build Your Family Tree</h3>
                                <p class="text-white-50 mb-4" style="max-width: 500px;">
                                    Your family tree is currently empty. Start building your lineage by adding your parents, partner, siblings, or children!
                                </p>
                                <button type="button" onclick="switchTreeViewMode('studio')" class="btn btn-success px-4 py-2 fw-bold" style="border-radius: 50px; min-height: 44px; display: inline-flex; align-items: center;">
                                    <i class="bi bi-plus-circle me-1"></i> Add Relatives in Studio
                                </button>
                            </div>
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="min-height: 480px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: rgba(163,22,33, 0.1); border: 2px dashed #a31621;">
                                    <i class="bi bi-diagram-3 text-primary fs-1"></i>
                                </div>
                                <h3 class="fw-bold text-white mb-2">{{ trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) }} Has Not Connected Their Branch Yet</h3>
                                <p class="text-white-50 mb-4" style="max-width: 500px;">
                                    This relative has not added their immediate family members yet. Once they connect their relatives, their branch will appear here.
                                </p>
                                <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
                                    <a href="/allMembers" class="btn btn-outline-light px-4 py-2 fw-bold d-inline-flex align-items-center" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                                        <i class="bi bi-arrow-left me-2"></i> Back to Directory
                                    </a>
                                    <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center shadow-sm btn-prompt-tree-action" id="btnPromptBuildTree" style="border-radius: 50px; min-height: 44px; white-space: nowrap;" onclick="window.promptMemberToBuildTree()">
                                        <i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite {{ explode(' ', trim($data['firstName'] ?? 'Them'))[0] }} to Connect Branch
                                    </button>
                                    <a href="/allMembers/seeProfile/{{ $data['id'] ?? '' }}" class="btn btn-primary px-4 py-2 fw-bold d-inline-flex align-items-center" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                                        <i class="bi bi-person-badge me-2"></i> View Profile
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Mode 1 Enhancement: Generational Time Slider (Chronological Dynasty Flow) -->
                <div id="timeSliderDock" class="time-slider-dock" style="position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); z-index: 32; background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(20px); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 999px; padding: 10px 22px; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5); display: flex; align-items: center; gap: 14px; max-width: 90vw;">
                    <button type="button" id="btnPlayTimeSlider" onclick="toggleTimeSliderPlay()" class="btn btn-sm btn-gold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-radius: 50%; padding: 0;" title="Play Dynasty Chronology">
                        <i class="bi bi-play-fill fs-5" id="timeSliderPlayIcon"></i>
                    </button>
                    <div class="d-flex flex-column time-slider-body" style="min-width: 180px;">
                        <div class="d-flex justify-content-between text-white time-slider-head" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
                            <span id="timeSliderLabel">ALL GENERATIONS</span>
                            <span class="text-warning" id="timeSliderEra">Present Era</span>
                        </div>
                        <input type="range" class="form-range custom-gold-range" id="dynastyTimeSlider" min="-3" max="2" step="1" value="2" oninput="onTimeSliderChange(this.value)" style="cursor: pointer; height: 6px;">
                        <div class="d-flex justify-content-between text-white-50 time-slider-ticks" style="font-size: 0.65rem; margin-top: -3px;">
                            <span>Great-Grands</span>
                            <span>Parents</span>
                            <span>You</span>
                            <span>Grandkids</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill py-0 px-2" onclick="resetTimeSlider()" style="font-size: 0.7rem; height: 26px;" title="Reset to Full Dynasty View">
                        Reset
                    </button>
                </div>

                <!-- Floating Controls Dock (Kinship Controls) -->
                <div class="controls-dock">
                    <button type="button" class="dock-btn" onclick="centerOnMe()" title="Center on Me">
                        <i class="bi bi-crosshair2"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="fitTreeScreen()" title="Fit Family to Screen">
                        <i class="bi bi-aspect-ratio"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="zoomInTree()" title="Zoom In">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="zoomOutTree()" title="Zoom Out">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="toggleFullscreenTree()" id="btnTreeFullscreen" title="Fullscreen Canvas">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="if(window.family && window.family.exportPNG) window.family.exportPNG('Dynasty_Family_Tree.png', 2);" title="Download Heritage Poster (PNG)">
                        <i class="bi bi-image"></i>
                    </button>
                    <button type="button" class="dock-btn" onclick="if(window.family && window.family.exportSVG) window.family.exportSVG('Dynasty_Family_Tree.svg');" title="Download Vector Tree (SVG)">
                        <i class="bi bi-filetype-svg"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mode 2: Pedigree Outline Container (Thumb-First Mobile Mode) -->
        <div class="pedigree-outline-container d-none" id="pedigreeOutlineContainer">
            @if(empty($graphData['has_built_tree']))
                @if(!empty($isSelf))
                    <div class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="min-height: 480px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); border: 2px dashed #10b981;">
                            <i class="bi bi-diagram-3 text-success fs-1"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-2">Build Your Family Tree</h3>
                        <p class="text-white-50 mb-4" style="max-width: 500px;">
                            Your family tree is currently empty. Start building your lineage by adding your parents, partner, siblings, or children!
                        </p>
                        <button type="button" onclick="switchTreeViewMode('studio')" class="btn btn-success px-4 py-2 fw-bold" style="border-radius: 50px; min-height: 44px; display: inline-flex; align-items: center;">
                            <i class="bi bi-plus-circle me-1"></i> Add Relatives in Studio
                        </button>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="min-height: 480px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: rgba(163,22,33, 0.1); border: 2px dashed #a31621;">
                            <i class="bi bi-diagram-3 text-primary fs-1"></i>
                        </div>
                        <h3 class="fw-bold text-white mb-2">{{ trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) }} Has Not Connected Their Branch Yet</h3>
                        <p class="text-white-50 mb-4" style="max-width: 500px;">
                            This relative has not added their immediate family members yet. Once they connect their relatives, their branch will appear here.
                        </p>
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-2">
                            <a href="/allMembers" class="btn btn-outline-light px-4 py-2 fw-bold d-inline-flex align-items-center" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                                <i class="bi bi-arrow-left me-2"></i> Back to Directory
                            </a>
                            <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center shadow-sm btn-prompt-tree-action" id="btnPromptBuildTreePedigree" style="border-radius: 50px; min-height: 44px; white-space: nowrap;" onclick="window.promptMemberToBuildTree()">
                                <i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite {{ explode(' ', trim($data['firstName'] ?? 'Them'))[0] }} to Connect Branch
                            </button>
                            <a href="/allMembers/seeProfile/{{ $data['id'] ?? '' }}" class="btn btn-primary px-4 py-2 fw-bold d-inline-flex align-items-center" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                                <i class="bi bi-person-badge me-2"></i> View Profile
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <!-- Mode 3: Radial 360° Fan Chart Container (Concentric Pedigree Wheel) -->
        <div class="fan-chart-wrapper d-none position-relative" id="fanChartContainer" style="min-height: 720px; background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%); border-radius: 24px; padding: 24px; box-shadow: 0 20px 48px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); overflow: hidden;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h4 class="text-white fw-bold mb-1"><i class="bi bi-pie-chart-fill text-warning me-2"></i>360° Radial Ahnentafel Fan Chart</h4>
                    <p class="text-white-50 mb-0" style="font-size: 0.85rem;">Concentric ancestral wheel mapping 4 generations of direct pedigree by maternal & paternal lineage quadrants.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-light active" id="btnFanSpan360" onclick="setFanChartSpan(360)">360° Full Wheel</button>
                        <button type="button" class="btn btn-outline-light" id="btnFanSpan180" onclick="setFanChartSpan(180)">180° Half Arc</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="resetFanChartZoom()" title="Reset Fan Zoom">
                        <i class="bi bi-arrows-angle-contract"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Quadrant Color Legend -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-3 p-2 rounded-pill" style="background: rgba(15,23,42,0.8); border: 1px solid rgba(255,255,255,0.1); width: fit-content; margin: 0 auto; font-size: 0.8rem;">
                <div class="d-flex align-items-center gap-1 text-white">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #a31621; display: inline-block;"></span>
                    <span>Paternal Grandfather</span>
                </div>
                <div class="d-flex align-items-center gap-1 text-white">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #ef6b73; display: inline-block;"></span>
                    <span>Paternal Grandmother</span>
                </div>
                <div class="d-flex align-items-center gap-1 text-white">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #ec4899; display: inline-block;"></span>
                    <span>Maternal Grandfather</span>
                </div>
                <div class="d-flex align-items-center gap-1 text-white">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #f472b6; display: inline-block;"></span>
                    <span>Maternal Grandmother</span>
                </div>
                <div class="d-flex align-items-center gap-1 text-white">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #f59e0b; display: inline-block;"></span>
                    <span>Focal Person (Ego)</span>
                </div>
            </div>

            <!-- Interactive SVG Canvas for Fan Wheel -->
            <div id="fanChartSvgContainer" style="width: 100%; height: 600px; display: flex; align-items: center; justify-content: center; position: relative; cursor: grab;">
                <!-- SVG dynamically injected by renderRadialFanChart() -->
            </div>
        </div>

        <!-- Mode 4: 3D Dynasty Migration Globe Container (Spatial Lineage & Diaspora) -->
        <div class="dynasty-globe-wrapper d-none position-relative" id="dynastyGlobeContainer" style="min-height: 720px; background: radial-gradient(circle at center, #090d16 0%, #030712 100%); border-radius: 24px; padding: 24px; box-shadow: 0 20px 48px rgba(0,0,0,0.5); border: 1px solid rgba(163,22,33, 0.25); overflow: hidden;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h4 class="text-white fw-bold mb-1"><i class="bi bi-globe-americas text-info me-2"></i>3D Dynasty Migration Globe</h4>
                    <p class="text-white-50 mb-0" style="font-size: 0.85rem;">Spatial visualization of ancestral birthplaces, migration trajectories, and living diaspora across the world.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-info" id="btnToggleGlobeRotation" onclick="toggleGlobeAutoRotation()">
                        <i class="bi bi-arrow-repeat me-1" id="globeRotationIcon"></i> Pause Rotation
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="resetGlobeView()">
                        <i class="bi bi-compass"></i> Reset View
                    </button>
                </div>
            </div>

            <div class="row g-4 align-items-center">
                <!-- 3D Canvas Container -->
                <div class="col-lg-8 position-relative text-center">
                    <canvas id="dynastyGlobeCanvas" width="600" height="520" style="max-width: 100%; width: 560px; height: 500px; border-radius: 20px; cursor: grab; background: transparent;"></canvas>
                    <div class="position-absolute bottom-0 start-50 translate-middle-x pb-2 text-white-50 small">
                        <i class="bi bi-hand-index-thumb me-1"></i> Drag to spin globe • Scroll to zoom • Click hubs for relatives
                    </div>
                </div>

                <!-- Living Diaspora Hubs Card -->
                <div class="col-lg-4">
                    <div class="p-3 rounded-4" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(12px);">
                        <h6 class="text-white fw-bold mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-geo-alt-fill text-warning me-1"></i> Global Dynasty Hubs</span>
                            <span class="badge bg-primary rounded-pill" id="globeHubCount">0 Cities</span>
                        </h6>
                        <div id="globeHubsList" style="max-height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mode 5: Integrated Family Studio Container (Direct In-Page Tree Editing) -->
        <div class="family-studio-wrapper d-none position-relative" id="familyStudioContainer">
            @include('member.includes.familyStudioContent')
        </div>

        <!-- Legend (Canvas mode) -->
        <div class="family-legend" id="familyLegend" role="group" aria-label="How to read the family tree">
            <div class="legend-row">
                <span class="legend-heading">People</span>
                <div class="legend-item"><div class="legend-color legend-male"></div><span>Male</span></div>
                <div class="legend-item"><div class="legend-color legend-female"></div><span>Female</span></div>
                <div class="legend-item"><span class="legend-chip legend-chip-member">✓</span><span>Registered member</span></div>
                <div class="legend-item"><span class="legend-chip legend-chip-memory">🕊️</span><span>In memory</span></div>
                <div class="legend-item"><span class="legend-chip legend-chip-ghost"></span><span>Parent not added yet</span></div>
                <div class="legend-item"><span class="legend-chip legend-chip-pending">?</span><span>Awaiting confirmation</span></div>
            </div>
            <div class="legend-row">
                <span class="legend-heading">Relationships</span>
                <div class="legend-item"><svg class="legend-line" viewBox="0 0 34 10" aria-hidden="true"><line x1="1" y1="5" x2="33" y2="5" stroke="#f43f5e" stroke-width="2.5" stroke-dasharray="6 4"/></svg><span>Spouse / Partner</span></div>
                <div class="legend-item"><svg class="legend-line" viewBox="0 0 34 10" aria-hidden="true"><line x1="1" y1="5" x2="33" y2="5" stroke="#94a3b8" stroke-width="2" stroke-dasharray="4 4"/></svg><span class="legend-stamp legend-stamp-divorced">DIVORCED</span></div>
                <div class="legend-item"><span class="legend-stamp legend-stamp-separated">SEPARATED</span></div>
                <div class="legend-item"><span class="legend-stamp legend-stamp-widowed">WIDOWED</span></div>
                <div class="legend-item"><svg class="legend-line" viewBox="0 0 34 10" aria-hidden="true"><line x1="1" y1="5" x2="33" y2="5" stroke="#ef6b73" stroke-width="2.5"/></svg><span>Parent &amp; child</span></div>
                <div class="legend-item"><svg class="legend-line" viewBox="0 0 34 10" aria-hidden="true"><line x1="1" y1="5" x2="33" y2="5" stroke="#ef6b73" stroke-width="2.5" stroke-dasharray="8 5"/></svg><span>Half-sibling (one shared parent)</span></div>
                <div class="legend-item"><svg class="legend-line" viewBox="0 0 34 10" aria-hidden="true"><line x1="1" y1="5" x2="33" y2="5" stroke="#ef6b73" stroke-width="2.5" stroke-dasharray="2 5" stroke-linecap="round"/></svg><span>Step / adopted</span></div>
            </div>
        </div>


    <!-- Quick add: fill a "not yet added" parent -->
    <div class="modal fade" id="fillParentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:20px;">
                <form id="fillParentForm" autocomplete="off">
                    @csrf
                    <input type="hidden" name="node_id" id="fillParentNodeId">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="fillParentTitle">Add details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="fillParentError"></div>
                        <div class="row g-3">
                            <div class="col-6"><label class="form-label small fw-semibold" for="fillParentFirst">First name *</label>
                                <input type="text" class="form-control" id="fillParentFirst" name="first_name" required></div>
                            <div class="col-6"><label class="form-label small fw-semibold" for="fillParentLast">Last name</label>
                                <input type="text" class="form-control" id="fillParentLast" name="last_name"></div>
                            <div class="col-12"><label class="form-label small fw-semibold" for="fillParentEmail">Email (optional)</label>
                                <input type="email" class="form-control" id="fillParentEmail" name="email"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="fillParentSave" style="min-height:44px">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openFillParentModal(nodeData) {
            document.getElementById('fillParentNodeId').value = nodeData.id;
            document.getElementById('fillParentTitle').textContent = 'Add ' + (/^(Father|Mother)$/.test(nodeData.name || '') ? nodeData.name.toLowerCase() : (nodeData.name || 'details'));
            document.getElementById('fillParentError').classList.add('d-none');
            document.getElementById('fillParentForm').reset();
            document.getElementById('fillParentNodeId').value = nodeData.id;
            const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('fillParentModal'));
            m.show();
            setTimeout(() => document.getElementById('fillParentFirst')?.focus(), 350);
        }
        document.getElementById('fillParentForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('fillParentSave');
            btn.disabled = true;
            fetch('/member/organogram/editor/fill-placeholder', { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(d => {
                    const ok = (d?.status === 200 || d?.status === 'success' || d?.statusCode === 200);
                    if (ok) { window.location.reload(); return; }
                    const err = document.getElementById('fillParentError');
                    err.textContent = (d?.message && d.message.message) || d?.message || 'Could not save. Please try again.';
                    err.classList.remove('d-none');
                    btn.disabled = false;
                })
                .catch(() => { btn.disabled = false; });
        });
    </script>

        <!-- Person Detail Modal / Native Bottom Sheet Drawer -->
        <div class="person-modal" id="personModal">
            <div class="modal-content" id="personModalContent">
                <div class="bottom-sheet-handle" id="bottomSheetHandle">
                    <div class="bottom-sheet-bar"></div>
                </div>
                <div class="modal-header">
                    <h3><i class="bi bi-person-badge"></i> Person Details</h3>
                    <button class="modal-close" id="closeModal"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Populated dynamically via JS -->
                </div>
                <div class="modal-footer p-3 border-top bg-light d-flex gap-2">
                    @if(empty($isReadOnly))
                    <button class="btn btn-sm btn-primary flex-grow-1 fw-bold d-inline-flex align-items-center justify-content-center" id="openAddRelativeModalBtn" onclick="openInviteRelativeFromNode()" style="min-height: 38px;">
                        <i class="bi bi-send-fill me-1"></i> Invite Family Member
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" onclick="closeNodeModalAndOpenStudio()" style="min-height: 38px;" title="To add or edit tree structure directly, open Family Studio">
                        <i class="bi bi-diagram-3-fill me-1"></i> Studio
                    </button>
                    <button class="btn btn-sm btn-outline-danger fw-bold" id="modalRemoveNodeBtn" style="display: none;" onclick="removeCurrentModalNode()">
                        <i class="bi bi-trash3-fill"></i> Remove
                    </button>
                    @endif
                </div>

            </div>
        </div>

        <!-- Add Relative Wizard Modal Component -->
        @include('components.lineage.wizard')

        <!-- Manage & Remove Relatives Modal -->
        <div class="modal fade" id="manageRelativesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 540px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                <i class="bi bi-person-x-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0 fs-6">Manage & Remove Relatives</h5>
                                <small class="text-muted">Remove unrecognised names or incorrect relatives from your tree</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" id="manageRelativesList">
                        <!-- Dynamically filled with relative list -->
                    </div>
                    <div class="modal-footer border-top py-2 px-4 bg-light">
                        <button type="button" class="btn btn-secondary btn-sm fw-bold px-3" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heritage Story Card Modal (9:16 Instagram/WhatsApp Story Format) -->
        <div class="modal fade" id="heritageStoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #0f172a; max-height: calc(100vh - 3.5rem);">
                    <div class="modal-header border-0 pb-0 text-white flex-shrink-0">
                        <h5 class="modal-title fw-bold" style="font-size: 1rem;"><i class="bi bi-stars text-warning me-2"></i>Heritage Story Card</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center" style="overflow-y: auto;">
                        <div id="storyCardPreview" class="story-card-preview">
                            <canvas id="storyCanvas" width="540" height="960" style="width: 100%; max-width: 280px; border-radius: 18px; box-shadow: 0 16px 36px rgba(0,0,0,0.5);"></canvas>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-3">
                            <button type="button" id="btnDownloadStory" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 50px; background: linear-gradient(135deg, #a31621, #a31621); border: none;">
                                <i class="bi bi-download me-2"></i>Save Card
                            </button>
                            <button type="button" class="btn btn-outline-light fw-bold px-3 py-2" style="border-radius: 50px;" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Claim Match Confirmation Modal -->
        <div class="modal fade" id="claimMatchModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(163,22,33, 0.12); width: 40px; height: 40px;">
                                <i class="bi bi-person-check-fill fs-5" style="color: #a31621;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-dark mb-0 fs-6">Claim Your Spot on the Tree</h5>
                                <p class="text-muted small mb-0">Verified identity connection</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 position-relative d-inline-block">
                            <img id="claimModalAvatar" src="/resources/images/profile/avatarM.png" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #a31621;">
                            <span class="position-absolute bottom-0 end-0 text-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 13px; background: #a31621;">
                                <i class="bi bi-link-45deg"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="claimModalName">Family Member</h5>
                        <p class="text-muted small mb-3" id="claimModalRelation">Relative on the family tree</p>

                        <div class="alert alert-light border text-start p-3 mb-0" style="border-radius: 12px; font-size: 0.85rem; background-color: #f8fafc;">
                            <div class="d-flex align-items-start gap-2 text-dark">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <div>
                                    <strong>What happens next:</strong>
                                    <div class="text-muted mt-1">Your account will be directly linked to this position. If you added any spouses or children, they will be seamlessly grafted into this family dynasty.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex gap-2">
                        <button type="button" class="btn btn-light btn-sm fw-semibold px-3 py-2 flex-grow-1" data-bs-dismiss="modal" style="border-radius: 10px;">Cancel</button>
                        <button type="button" id="btnExecuteClaim" onclick="executeClaimMatch()" class="btn btn-primary btn-sm fw-bold px-4 py-2 flex-grow-1 text-white" style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); border: none; border-radius: 10px;">
                            Confirm & Connect
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Living Voice Capsule Recording & Upload Modal -->
        <div class="modal fade" id="voiceCapsuleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 460px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #0f172a; color: #ffffff; border: 1px solid rgba(245, 158, 11, 0.3); max-height: calc(100vh - 3.5rem);">
                    <div class="modal-header border-bottom border-secondary py-3 px-4 flex-shrink-0" style="background: rgba(15, 23, 42, 0.95);">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                                <i class="bi bi-mic-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0 fs-6">Living Voice Capsule</h5>
                                <small class="text-white-50" id="voiceCapsuleTargetName">Preserve spoken heritage for this member</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="overflow-y: auto;">
                        <!-- Navigation Tabs: Record vs Upload -->
                        <ul class="nav nav-pills nav-fill mb-3 p-1 rounded-pill" style="background: rgba(255,255,255,0.06);" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill text-white py-1" id="tabVoiceRecord" data-bs-toggle="pill" data-bs-target="#panelVoiceRecord" type="button" role="tab" style="font-size: 0.82rem; font-weight: 600;">
                                    <i class="bi bi-record-circle me-1 text-danger"></i> Record Mic
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill text-white py-1" id="tabVoiceUpload" data-bs-toggle="pill" data-bs-target="#panelVoiceUpload" type="button" role="tab" style="font-size: 0.82rem; font-weight: 600;">
                                    <i class="bi bi-upload me-1 text-info"></i> Upload File
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Tab 1: Live In-Browser Mic Recording -->
                            <div class="tab-pane fade show active text-center" id="panelVoiceRecord" role="tabpanel">
                                <div class="py-3">
                                    <div id="micRecordPulse" class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 90px; height: 90px; background: rgba(239, 68, 68, 0.15); border: 2px dashed rgba(239, 68, 68, 0.5); transition: all 0.3s ease;">
                                        <i class="bi bi-mic-fill fs-1 text-danger" id="micRecordIcon"></i>
                                    </div>
                                    <div class="fw-bold fs-4 mb-1" id="recordTimer">00:00</div>
                                    <p class="text-white-50 small mb-3" id="recordInstructions">Click "Start Recording" to capture voice memories directly in your browser.</p>

                                    <!-- Recorded Preview Player -->
                                    <div id="recordedPreviewSection" class="d-none mb-3">
                                        <audio id="recordedAudioPreview" controls style="width: 100%; height: 36px; border-radius: 8px;"></audio>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" id="btnStartRecord" onclick="startVoiceRecording()" class="btn btn-danger px-4 py-2 fw-bold" style="border-radius: 50px;">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.65rem;"></i> Start Recording
                                        </button>
                                        <button type="button" id="btnStopRecord" onclick="stopVoiceRecording()" class="btn btn-warning px-4 py-2 fw-bold d-none" style="border-radius: 50px;">
                                            <i class="bi bi-stop-fill me-1"></i> Stop Recording
                                        </button>
                                        <button type="button" id="btnSaveRecord" onclick="saveRecordedVoiceCapsule()" class="btn btn-success px-4 py-2 fw-bold d-none" style="border-radius: 50px;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Save to Tree
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Audio File Upload -->
                            <div class="tab-pane fade" id="panelVoiceUpload" role="tabpanel">
                                <form id="voiceUploadForm" onsubmit="handleVoiceFileUpload(event)" class="py-2">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="voiceAudioFile" class="form-label text-white-50 small mb-1">Select Audio File (MP3, WAV, OGG, M4A, WEBM - max 10MB)</label>
                                        <input type="file" class="form-control" id="voiceAudioFile" name="audio" accept="audio/*,.mp3,.wav,.ogg,.m4a,.webm" required style="background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.2); color: white;">
                                    </div>
                                    <button type="submit" id="btnUploadVoiceFile" class="btn btn-gold w-100 fw-bold py-2" style="border-radius: 12px;">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload & Attach Voice Capsule
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="voiceCapsuleAlert" class="alert d-none mt-3 mb-0 small" role="alert"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('component.swalAlert')
<script src="/public/js/vendor/kinshiptree.js?v=9.5"></script>
<script>
    window.__ROOT_USER_ID__ = '{{ $data['id'] ?? '' }}';
    window.__LOGGED_IN_USER_ID__ = '{{ $_SESSION['id'] ?? '' }}';
    window.__VIEWED_MEMBER_NAME__ = '{{ trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) }}';
    window.__PRIMARY_FAMILY_CODE__ = '{{ $primaryFamCode ?? ($data['famCode'] ?? '') }}';
    window.__BIRTH_FAMILY_CODE__ = '{{ !empty($hasMaternalCode) ? ($maternalFamCode ?? '') : '' }}';
    window.__HAS_BIRTH_FAMILY__ = {{ !empty($hasMaternalCode) ? 'true' : 'false' }};
    window.__ACTIVE_LINEAGE_SCOPE__ = '{{ $activeLineageScope ?? 'primary' }}';
    window.__MARITAL_SURNAME__ = '{{ $maritalSurname ?? ($data['lastName'] ?? '') }}';
    window.__MAIDEN_SURNAME__ = '{{ $maidenSurname ?? '' }}';
    window.__MOTHER_MAIDEN_SURNAME__ = '{{ addslashes($motherMaidenSurname ?? '') }}';
    window.__MOTHER_BIRTH_FAMILY_CODE__ = '{{ addslashes($motherBirthFamCode ?? '') }}';
    window.__PATERNAL_SURNAME__ = '{{ addslashes($paternalSurname ?? ($data['lastName'] ?? '')) }}';
    window.__EGO_GENDER__ = '{{ strtolower((string)($data['gender'] ?? '')) }}';
    window.__FAMILY_CODE__ = '{{ $familyCode ?? (($activeLineageScope ?? 'primary') === 'maternal' && !empty($maternalFamCode) ? $maternalFamCode : ($primaryFamCode ?? ($data['famCode'] ?? ''))) }}';
    window.__WIFE_FAMILY_CODE__ = '{{ $wifeFamCode ?? '' }}';
    window.__IS_READ_ONLY__ = {{ !empty($isReadOnly) ? 'true' : 'false' }};
    
    const graphData = {!! $graphJson ?? '{}' !!};

    // Global helper: Prompt member to build their family tree (available unconditionally)
    window.promptMemberToBuildTree = function() {
        const memberName = window.__VIEWED_MEMBER_NAME__ || 'This Member';
        const firstName = memberName.split(' ')[0] || memberName;
        const targetUserId = window.__ROOT_USER_ID__;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ $_SESSION['token'] ?? '' }}';

        function escapeHtmlText(str) {
            return String(str || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m]);
        }

        function markPromptButtonsSuccess() {
            document.querySelectorAll('#btnPromptBuildTree, #btnPromptBuildTreePedigree, #btnHeaderPromptBuildTree, .btn-prompt-tree-action').forEach(btn => {
                btn.classList.remove('btn-warning', 'text-dark');
                btn.classList.add('btn-secondary', 'text-white');
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i> Invitation Sent';
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `Invite ${escapeHtmlText(firstName)} to Connect Their Branch`,
                html: `
                    <p class="text-muted small mb-3">
                        Send a warm invitation to <strong>${escapeHtmlText(memberName)}</strong> inviting them to connect their branch of the family tree!
                    </p>
                    <div class="text-start mb-2">
                        <label class="form-label small fw-semibold text-secondary" for="swal-prompt-note">Add an optional message:</label>
                        <textarea id="swal-prompt-note" class="form-control" rows="3" maxlength="300" placeholder="e.g. Hello ${escapeHtmlText(firstName)}, let's connect our branches on the family tree!"></textarea>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-send-fill me-1"></i> Send Invitation',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    const note = document.getElementById('swal-prompt-note')?.value?.trim() || '';
                    try {
                        const response = await fetch('/api/family-tree/prompt-build', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-XSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                target_user_id: targetUserId,
                                note: note,
                                token: csrfToken
                            })
                        });
                        const resData = await response.json();
                        if (!response.ok || !resData.success) {
                            throw new Error(resData.message || resData.error || 'Failed to send invitation.');
                        }
                        return resData;
                    } catch (err) {
                        Swal.showValidationMessage(err.message || 'Unable to send invitation. Please try again.');
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Invitation Sent! 🌟',
                        text: result.value.message || `We have sent a warm invitation to ${firstName} to connect their family branch.`,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    });
                    markPromptButtonsSuccess();
                }
            });
        } else {
            const note = prompt(`Send a warm invitation to ${firstName} to connect their family branch:\n(Optional note)`);
            if (note === null) return;
            const buttons = document.querySelectorAll('#btnPromptBuildTree, #btnPromptBuildTreePedigree, #btnHeaderPromptBuildTree, .btn-prompt-tree-action');
            buttons.forEach(b => {
                b.disabled = true;
                b.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
            });
            fetch('/api/family-tree/prompt-build', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    target_user_id: targetUserId,
                    note: note,
                    token: csrfToken
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message || 'Invitation sent successfully!');
                    markPromptButtonsSuccess();
                } else {
                    alert(res.message || res.error || 'Failed to send invitation.');
                    buttons.forEach(b => {
                        b.disabled = false;
                        b.innerHTML = `<i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite ${firstName} to Connect Branch`;
                    });
                }
            })
            .catch(() => {
                alert('Network error. Please try again.');
                buttons.forEach(b => {
                    b.disabled = false;
                    b.innerHTML = `<i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite ${firstName} to Connect Branch`;
                });
            });
        }
    };

    function getGraphNodeId(legacyId, fullName) {
        if (!graphData || !graphData.nodes) return legacyId; // Fallback
        
        // 1. Try matching by user_id
        if (legacyId) {
            const byUserId = graphData.nodes.find(n => String(n.user_id) === String(legacyId));
            if (byUserId) return byUserId.id;
        }

        // 2. Try matching by name
        if (fullName) {
            const byName = graphData.nodes.find(n => n.full_name === fullName || n.first_name === fullName);
            if (byName) return byName.id;
        }

        return legacyId; // Fallback
    }

    // Initialize FamilyTreeJS
    document.addEventListener("DOMContentLoaded", function () {
        let familyTreeNodes = [];
        let layoutUnions = [];

        if (graphData && graphData.nodes) {
            // Build a lookup for children to find parents
            const childToParents = {};
            if (graphData.children) {
                graphData.children.forEach(child => {
                    const unionId = child.union_id;
                    const union = graphData.unions.find(u => String(u.id) === String(unionId));
                    if (union) {
                        const p1 = graphData.nodes.find(n => String(n.id) === String(union.partner_1_id));
                        const p2 = graphData.nodes.find(n => String(n.id) === String(union.partner_2_id));
                        
                        let fid = null, mid = null;
                        const p1Gender = p1 ? String(p1.gender || '').toLowerCase() : '';
                        const p2Gender = p2 ? String(p2.gender || '').toLowerCase() : '';

                        if (p1) {
                            if (p1Gender === 'male') fid = p1.id;
                            else if (p1Gender === 'female') mid = p1.id;
                        }
                        if (p2) {
                            if (p2Gender === 'male') fid = p2.id;
                            else if (p2Gender === 'female') mid = p2.id;
                        }

                        // Defensive fallback: single-parent or unspecified gender unions
                        if (p1 && !fid && !mid) {
                            if (p1Gender === 'female') mid = p1.id;
                            else fid = p1.id;
                        }
                        if (p2 && !fid && !mid) {
                            if (p2Gender === 'female') mid = p2.id;
                            else fid = p2.id;
                        }
                        
                        childToParents[child.child_id] = { fid, mid };
                    }
                });
            }

            // Map Unions to pids (partners)
            const nodeToPids = {};
            if (graphData.unions) {
                graphData.unions.forEach(u => {
                    const p1 = u.partner_1_id;
                    const p2 = u.partner_2_id;
                    // p1 == p2 is a single-parent union, not a partnership: never list someone as their own partner
                    if (p1 && p2 && String(p1) !== String(p2)) {
                        if (!nodeToPids[p1]) nodeToPids[p1] = [];
                        if (!nodeToPids[p2]) nodeToPids[p2] = [];
                        if (!nodeToPids[p1].includes(p2)) nodeToPids[p1].push(p2);
                        if (!nodeToPids[p2].includes(p1)) nodeToPids[p2].push(p1);
                    }
                });
            }

            // Explicit union graph (partners + the children that hang from each union) for the
            // multi-union layout: half-siblings, former partners, remarriage.
            layoutUnions = (graphData.unions || []).map(u => ({
                id: u.id,
                p1: u.partner_1_id,
                p2: u.partner_2_id,
                union_type: u.union_type || 'married',
                is_current: u.is_current ? 1 : 0,
                children: (graphData.children || [])
                    .filter(c => String(c.union_id) === String(u.id))
                    .map(c => c.child_id),
                // Branch style per child: half-sibling (dashed) / step or adopted (dotted)
                kinds: (graphData.children || [])
                    .filter(c => String(c.union_id) === String(u.id))
                    .reduce((acc, c) => {
                        const n = graphData.nodes.find(x => String(x.id) === String(c.child_id));
                        acc[String(c.child_id)] = (c.relationship_type === 'step' || c.relationship_type === 'adopted' || c.relationship_type === 'foster')
                            ? 'step'
                            : (n && n.sibling_kind === 'half' ? 'half' : '');
                        return acc;
                    }, {})
            }));

            // Break a full name onto (at most) two balanced lines so it fits the card
            function splitNameLines(full) {
                full = (full || '').trim().replace(/\s+/g, ' ');
                if (full.length <= 15 || full.indexOf(' ') === -1) return [full, ''];
                const parts = full.split(' ');
                let bestIdx = 1, bestDiff = Infinity;
                for (let i = 1; i < parts.length; i++) {
                    const a = parts.slice(0, i).join(' ').length;
                    const b = parts.slice(i).join(' ').length;
                    const diff = Math.abs(a - b);
                    if (diff < bestDiff) { bestDiff = diff; bestIdx = i; }
                }
                return [parts.slice(0, bestIdx).join(' '), parts.slice(bestIdx).join(' ')];
            }

            graphData.nodes.forEach(node => {
                const parents = childToParents[node.id] || {};
                const nameLines = splitNameLines(node.full_name);
                familyTreeNodes.push({
                    id: node.id,
                    pids: nodeToPids[node.id] || [],
                    fid: parents.fid || undefined,
                    mid: parents.mid || undefined,
                    name: node.full_name,
                    nameL1: nameLines[0],
                    nameL2: nameLines[1],
                    gender: (node.gender || 'Male').toLowerCase(),
                    img: node.avatar_url || (node.gender === 'Male' ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png'),
                    title: node.ego_relation || node.bio || 'Family Member',
                    ego_relation: node.ego_relation || '',
                    legacyId: node.user_id,
                    generation_level: (typeof node.generation_level !== 'undefined') ? Number(node.generation_level) : undefined,
                    voice_capsule_url: node.voice_capsule_url || null,
                    is_placeholder: Boolean(node.is_placeholder),
                    isDeceased: Boolean(node.is_deceased),
                    birth_year: node.birth_year || (node.birth_date ? String(node.birth_date).substring(0, 4) : null),
                    death_year: node.death_year || (node.death_date ? String(node.death_date).substring(0, 4) : null),
                    death_date: node.death_date || null,
                    pending_confirmation: Boolean(node.pending_confirmation),
                    maiden_name: node.maiden_name || '',
                    birth_family_code: node.birth_family_code || '',
                    sibling_kind: node.sibling_kind || null,
                    is_inlaw_branch: Boolean(node.is_inlaw_branch),
                    inlaw_spouse_id: node.inlaw_spouse_id || null,
                    inlaw_branch_count: Number(node.inlaw_branch_count || 0),
                    email: node.email || (node.raw ? node.raw.email : '') || '',
                    mobile: node.mobile || (node.raw ? node.raw.mobile : '') || '',
                    occupation: node.occupation || (node.raw ? node.raw.occupation : '') || '',
                    raw: node
                });
            });
        }

        const hasBuiltTree = Boolean(graphData && graphData.has_built_tree);
        const isSelf = String(window.__ROOT_USER_ID__) === String(window.__LOGGED_IN_USER_ID__);

        if (!hasBuiltTree) {
            const memberName = window.__VIEWED_MEMBER_NAME__ || 'This Member';
            const emptyTreeHtml = isSelf ? `
                <div class="d-flex flex-column align-items-center justify-content-center p-4 p-md-5 text-center" style="min-height: 480px; max-width: 860px; margin: 0 auto;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 76px; height: 76px; background: rgba(245, 158, 11, 0.15); border: 2px solid rgba(245, 158, 11, 0.5); color: #fbbf24;">
                        <i class="bi bi-diagram-3-fill fs-2"></i>
                    </div>
                    <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1 mb-2" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        YOUR FAMILY LEGACY SANCTUARY
                    </span>
                    <h2 class="fw-bold text-white mb-2" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                        Why Build Your Family Tree?
                    </h2>
                    <p class="text-white-50 mb-4" style="max-width: 640px; font-size: 0.95rem; line-height: 1.55;">
                        Your family tree is not just a diagram — it is a living heirloom. When you add your parents, siblings, partner, or children, you unlock permanent benefits for your entire household:
                    </p>

                    <!-- 3 Core Benefit Cards -->
                    <div class="row g-3 text-start w-100 mb-4 justify-content-center">
                        <div class="col-12 col-md-4">
                            <div class="p-3.5 h-100 rounded-3" style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.12);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.2); color: #34d399;">
                                        <i class="bi bi-shield-lock-fill"></i>
                                    </div>
                                    <h6 class="fw-bold text-white mb-0" style="font-size: 0.92rem;">Preserve Living Heritage</h6>
                                </div>
                                <p class="text-white-50 small mb-0" style="font-size: 0.82rem; line-height: 1.45;">
                                    Protect your ancestral roots, maiden names, and oral voice capsules in a private vault before stories fade across generations.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="p-3.5 h-100 rounded-3" style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.12);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.2); color: #60a5fa;">
                                        <i class="bi bi-compass-fill"></i>
                                    </div>
                                    <h6 class="fw-bold text-white mb-0" style="font-size: 0.92rem;">Automatic Kinship</h6>
                                </div>
                                <p class="text-white-50 small mb-0" style="font-size: 0.82rem; line-height: 1.45;">
                                    Never wonder how cousins, in-laws, or aunts are related. Our engine calculates and illustrates exact relationships instantly.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="p-3.5 h-100 rounded-3" style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.12);">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                                        <i class="bi bi-heart-pulse-fill"></i>
                                    </div>
                                    <h6 class="fw-bold text-white mb-0" style="font-size: 0.92rem;">A Gift for Your Children</h6>
                                </div>
                                <p class="text-white-50 small mb-0" style="font-size: 0.82rem; line-height: 1.45;">
                                    Give younger family members a clear, visual identity and pride in their roots, ancestors, and extended family network.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button type="button" onclick="switchTreeViewMode('studio')" class="btn btn-warning px-4 py-2.5 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                            <i class="bi bi-plus-circle-fill"></i> Add Your Parents &amp; Siblings in Studio
                        </button>
                        <button type="button" class="btn btn-outline-light px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2" style="border-radius: 50px; min-height: 44px; white-space: nowrap; border-color: rgba(255,255,255,0.35);" data-bs-toggle="modal" data-bs-target="#inviteFamilyModal">
                            <i class="bi bi-person-lines-fill"></i> Invite Relatives by Phone/WhatsApp
                        </button>
                    </div>
                </div>
            ` : `
                <div class="d-flex flex-column align-items-center justify-content-center p-4 p-md-5 text-center" style="min-height: 480px; max-width: 720px; margin: 0 auto;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 76px; height: 76px; background: rgba(163,22,33, 0.15); border: 2px solid rgba(163,22,33, 0.45); color: #ef6b73;">
                        <i class="bi bi-diagram-3 fs-2"></i>
                    </div>
                    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis fw-bold px-3 py-1 mb-2" style="font-size: 0.78rem;">
                        BRANCH NOT CONNECTED YET
                    </span>
                    <h3 class="fw-bold text-white mb-2">${memberName} Has Not Added Their Family Branch</h3>
                    <p class="text-white-50 mb-4" style="max-width: 520px; font-size: 0.92rem; line-height: 1.5;">
                        A complete family tree thrives when every relative adds their immediate branch. Send ${memberName.split(' ')[0] || 'them'} a warm invitation to connect their parents, spouse, or children so the whole family stays linked.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="/allMembers" class="btn btn-outline-light px-4 py-2 fw-bold d-inline-flex align-items-center gap-2" style="border-radius: 50px; min-height: 44px; white-space: nowrap;">
                            <i class="bi bi-arrow-left"></i> Family Directory
                        </a>
                        <button type="button" class="btn btn-warning text-dark px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center shadow-sm btn-prompt-tree-action" id="btnPromptBuildTree" style="border-radius: 50px; min-height: 44px; white-space: nowrap;" onclick="window.promptMemberToBuildTree()">
                            <i class="bi bi-envelope-heart-fill me-2 text-dark"></i> Invite ${memberName.split(' ')[0] || 'Them'} to Connect Branch
                        </button>
                    </div>
                </div>
            `;
            const treeEl = document.getElementById("tree");
            if (treeEl) treeEl.innerHTML = emptyTreeHtml;
            const pedigreeEl = document.getElementById("pedigreeOutlineContainer");
            if (pedigreeEl) pedigreeEl.innerHTML = emptyTreeHtml;
            return;
        }

        if (familyTreeNodes.length > 0) {
            /* --------------------------------------------------------------
             * Adaptive node design: automatically scales dimensions for
             * mobile screens (160x175) vs desktop screens (230x232).
             * ------------------------------------------------------------ */
            const isMobileScreen = window.innerWidth < 768;
            const NODE_W = isMobileScreen ? 160 : 230;
            const NODE_H = isMobileScreen ? 175 : 232;
            const CX = NODE_W / 2;

            const initialRootId = String(graphData.root_node_id || getGraphNodeId(window.__ROOT_USER_ID__, '') || (familyTreeNodes[0] ? familyTreeNodes[0].id : ''));

            // Initialize KinshipTree (Drop-in FamilyTree engine in Luxury Dark Mode)
            var family = new FamilyTree(document.getElementById("tree"), {
                template: "tommy",
                mode: "dark",
                enableSearch: false,
                mouseScrool: FamilyTree.action.zoom,
                scaleInitial: "family",
                rootId: initialRootId,
                nodeBinding: {
                    field_0: "nameL1",
                    field_1: "nameL2",
                    field_2: "title",
                    img_0: "img"
                },
                nodes: familyTreeNodes,
                unions: layoutUnions
            });
            
            window.family = family;
            setTimeout(() => window.autoSizeTreeStage && window.autoSizeTreeStage(), 400);
            window.familyTreeNodes = familyTreeNodes;
            window.graphData = graphData;

            // Interactive Spouse / In-Law Lineage Progressive Disclosure Prompt
            family.on('inlaw_toggle', function (sender, args) {
                if (typeof window.promptOrToggleSpouseInLawBranch === 'function') {
                    window.promptOrToggleSpouseInLawBranch(args.spouseId);
                }
            });

            // Intercept clicks to trigger the floating Luxury Detail Card & custom Modal flow
            family.on('click', function (sender, args) {
                const nodeData = familyTreeNodes.find(n => String(n.id) === String(args.node.id));
                // "Father / Mother - tap to add": open the quick add dialog instead of a person card
                if (nodeData && nodeData.is_placeholder && !(window.__IS_READ_ONLY__ || (graphData && graphData.isReadOnly))) {
                    openFillParentModal(nodeData);
                    return;
                }
                if (nodeData) {
                    currentBaseNodeName = nodeData.name;
                    currentBaseNodeId = nodeData.id;
                    selectedNodeId = String(nodeData.id);

                    // 1. Open Luxury Floating Card (Matching Screenshot #1)
                    if (typeof window.openPersonCard === 'function') {
                        window.openPersonCard(nodeData);
                    }
                    
                    // 2. Also keep mobile bottom sheet synced for smaller phone screens
                    if (window.innerWidth <= 768 && typeof window.showPersonDetails === 'function') {
                        const rawNode = graphData.nodes.find(n => String(n.id) === String(args.node.id));
                        const rootGraphId = getGraphNodeId(window.__ROOT_USER_ID__, '');
                        const isRoot = (String(nodeData.id) === String(rootGraphId)) || (String(nodeData.legacyId) === String(window.__ROOT_USER_ID__));
                        const isReadOnlyTree = Boolean(window.__IS_READ_ONLY__) || (graphData && graphData.isReadOnly);
                        const modalRemoveBtn = document.getElementById('modalRemoveNodeBtn');
                        if (modalRemoveBtn) {
                            modalRemoveBtn.style.display = (isRoot || isReadOnlyTree) ? 'none' : 'inline-block';
                        }
                        
                        window.showPersonDetails({
                            fullName: nodeData.name,
                            img: nodeData.img,
                            relation: nodeData.title,
                            personId: nodeData.legacyId,
                            nodeId: nodeData.id,
                            familyCode: window.__FAMILY_CODE__,
                            email: (rawNode && rawNode.email) ? rawNode.email : (nodeData.email || ''),
                            mobile: (rawNode && rawNode.mobile) ? rawNode.mobile : (nodeData.mobile || ''),
                            maritalStatus: rawNode ? rawNode.marital_status : '',
                            occupation: (rawNode && rawNode.occupation) ? rawNode.occupation : (nodeData.occupation || ''),
                            country: rawNode ? (rawNode.country_of_residence || rawNode.location) : '',
                            isDeceased: Boolean(rawNode ? rawNode.is_deceased : nodeData.isDeceased),
                            isRegistered: !!nodeData.legacyId,
                            isReadOnly: isReadOnlyTree
                        });
                    }

                }
                return false; 
            });
        } else {
            document.getElementById('tree').innerHTML = '<div class="alert alert-info m-4">No family tree data available yet. Add relatives to begin.</div>';
        }

        // Render Pedigree Outline if data is present
        renderPedigreeOutline();

        // Check view mode preference on initial load:
        // Default MUST be Panoramic Dynasty (canvas). Users can manually switch to Pedigree or Direct Lineage.
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const urlMode = (urlParams.get('mode') || urlParams.get('view') || '').toLowerCase();
            const savedMode = localStorage.getItem('fp_tree_view_mode');

            if (urlMode === 'studio') {
                switchTreeViewMode('studio');
            } else if (urlMode === 'pedigree' || urlMode === 'outline') {
                switchTreeViewMode('pedigree');
            } else if (urlMode === 'lineage') {
                switchTreeViewMode('lineage');
            } else if (urlMode === 'canvas' || urlMode === 'panoramic') {
                switchTreeViewMode('canvas');
            } else if (urlMode === 'fanchart' || urlMode === 'fan') {
                switchTreeViewMode('fanchart');
            } else if (urlMode === 'globe') {
                switchTreeViewMode('globe');
            } else if (savedMode === 'lineage') {
                // Preserve user's manual selection of Direct Lineage during their session
                switchTreeViewMode('lineage');
            } else {
                // Always default to Panoramic Dynasty (canvas) when entering the Family Tree.
                // Overwrite any legacy auto-forced 'pedigree' in localStorage so clicking Family Tree
                // reliably presents Panoramic Dynasty.
                switchTreeViewMode('canvas');
            }
        } catch(_) {
            switchTreeViewMode('canvas');
        }

        // Initialize tree studio discovery banner persistence
        try {
            if (localStorage.getItem('fp_dismissed_tree_studio_banner') === '1') {
                const b = document.getElementById('treeStudioDiscoveryBanner');
                if (b) b.classList.add('d-none');
            }
        } catch(_) {}
    });

    /* =======================================================
       ANALYTICS & TELEMETRY DISPATCHER
       ======================================================= */
    window.trackTreeAnalytics = function(eventType, targetId = null, metadata = null) {
        try {
            if (window.axios) {
                axios.post('/api/analytics/track', {
                    event_type: eventType,
                    target_id: targetId,
                    metadata: metadata
                }).catch(() => {});
            } else if (navigator.sendBeacon) {
                navigator.sendBeacon('/api/analytics/track', JSON.stringify({
                    event_type: eventType,
                    target_id: targetId,
                    metadata: metadata
                }));
            }
        } catch(_) {}
    };

    /* =======================================================
       MULTI-MODAL VIEW & FLOATING DOCK LOGIC
       ======================================================= */
    window.dismissTreeStudioDiscoveryBanner = function() {
        const banner = document.getElementById('treeStudioDiscoveryBanner');
        if (banner) {
            banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(-10px)';
            setTimeout(() => { banner.classList.add('d-none'); }, 300);
        }
        try { localStorage.setItem('fp_dismissed_tree_studio_banner', '1'); } catch(_) {}
    };

    window.closeNodeModalAndOpenStudio = function() {
        const modalEl = document.getElementById('personDetailsModal') || document.getElementById('nodeDetailsModal');
        if (modalEl && window.bootstrap) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        }
        switchTreeViewMode('studio');
    };

    window.switchTreeViewMode = function(mode) {
        const canvasContainer = document.getElementById('treeContainer');
        const pedigreeContainer = document.getElementById('pedigreeOutlineContainer');
        const fanChartContainer = document.getElementById('fanChartContainer');
        const globeContainer = document.getElementById('dynastyGlobeContainer');
        const studioContainer = document.getElementById('familyStudioContainer');
        const legend = document.getElementById('familyLegend');
        const btnCanvas = document.getElementById('btnModeCanvas');
        const btnLineage = document.getElementById('btnModeLineage');
        const btnPedigree = document.getElementById('btnModePedigree');
        const btnFanChart = document.getElementById('btnModeFanChart');
        const btnGlobe = document.getElementById('btnModeGlobe');
        const btnStudio = document.getElementById('btnModeStudio');
        const discoBanner = document.getElementById('treeStudioDiscoveryBanner');

        window.trackTreeAnalytics('tree_view_mode_switched', null, { mode: mode });

        // If leaving studio and changes were made, refresh the page to update the interactive tree layout
        if (window.__familyStudioDirty && mode !== 'studio') {
            window.__familyStudioDirty = false;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('mode', mode);
            window.location.href = currentUrl.toString();
            return;
        }

        // Update URL query string without reloading page
        try {
            const currentUrl = new URL(window.location.href);
            if (currentUrl.searchParams.get('mode') !== mode) {
                currentUrl.searchParams.set('mode', mode);
                window.history.replaceState({ mode: mode }, '', currentUrl.toString());
            }
        } catch(_) {}

        if (mode === 'studio') {
            if (canvasContainer) canvasContainer.classList.add('d-none');
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (fanChartContainer) fanChartContainer.classList.add('d-none');
            if (globeContainer) globeContainer.classList.add('d-none');
            if (legend) legend.classList.add('d-none');
            if (discoBanner) discoBanner.classList.add('d-none');
            if (studioContainer) studioContainer.classList.remove('d-none');

            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.remove('active');
            if (btnStudio) btnStudio.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'studio'); } catch(_) {}
        } else if (mode === 'globe') {
            if (studioContainer) studioContainer.classList.add('d-none');
            if (canvasContainer) canvasContainer.classList.add('d-none');
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (fanChartContainer) fanChartContainer.classList.add('d-none');
            if (legend) legend.classList.add('d-none');
            if (globeContainer) {
                globeContainer.classList.remove('d-none');
                if (typeof window.initDynastyGlobe === 'function') {
                    window.initDynastyGlobe();
                }
            }
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.remove('active');
            if (btnStudio) btnStudio.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'globe'); } catch(_) {}
        } else if (mode === 'fanchart') {
            if (studioContainer) studioContainer.classList.add('d-none');
            if (canvasContainer) canvasContainer.classList.add('d-none');
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (globeContainer) globeContainer.classList.add('d-none');
            if (legend) legend.classList.add('d-none');
            if (fanChartContainer) {
                fanChartContainer.classList.remove('d-none');
                if (typeof window.renderRadialFanChart === 'function') {
                    window.renderRadialFanChart();
                }
            }
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.remove('active');
            if (btnStudio) btnStudio.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'fanchart'); } catch(_) {}
        } else if (mode === 'pedigree') {
            if (studioContainer) studioContainer.classList.add('d-none');
            if (canvasContainer) canvasContainer.classList.add('d-none');
            if (fanChartContainer) fanChartContainer.classList.add('d-none');
            if (globeContainer) globeContainer.classList.add('d-none');
            if (legend) legend.classList.add('d-none');
            if (pedigreeContainer) {
                pedigreeContainer.classList.remove('d-none');
                renderPedigreeOutline();
            }
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.remove('active');
            if (btnStudio) btnStudio.classList.remove('active');
            if (btnPedigree) btnPedigree.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'pedigree'); } catch(_) {}
        } else if (mode === 'lineage') {
            if (studioContainer) studioContainer.classList.add('d-none');
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (fanChartContainer) fanChartContainer.classList.add('d-none');
            if (globeContainer) globeContainer.classList.add('d-none');
            if (canvasContainer) {
                canvasContainer.classList.remove('d-none');
                if (window.family && typeof window.family.traceLineage === 'function') {
                    const firstChild = familyTreeNodes.find(n => n.fid || n.mid);
                    if (firstChild) {
                        window.traceKinshipToSelected(firstChild.id, firstChild.name);
                    }
                }
            }
            if (legend) legend.classList.remove('d-none');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.remove('active');
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnStudio) btnStudio.classList.remove('active');
            if (btnLineage) btnLineage.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'lineage'); } catch(_) {}
        } else {
            if (studioContainer) studioContainer.classList.add('d-none');
            if (typeof window.clearLineageTrace === 'function') window.clearLineageTrace();
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (fanChartContainer) fanChartContainer.classList.add('d-none');
            if (globeContainer) globeContainer.classList.add('d-none');
            if (canvasContainer) {
                canvasContainer.classList.remove('d-none');
                if (window.family) {
                    setTimeout(() => window.family.fit(), 100);
                }
            }
            if (legend) legend.classList.remove('d-none');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnFanChart) btnFanChart.classList.remove('active');
            if (btnGlobe) btnGlobe.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnStudio) btnStudio.classList.remove('active');
            if (btnCanvas) btnCanvas.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'canvas'); } catch(_) {}
        }
    };

    // Pin the canvas to the full viewport height so the tree always has the room.
    window.autoSizeTreeStage = function() {
        const stage = document.getElementById('treeStage');
        const fam = window.family;
        if (!stage || !fam || stage.classList.contains('fullscreen-active')) return;
        if (!stage.querySelector('#tree svg > g')) return;
        const vh = window.innerHeight || 800;
        const mobile = window.matchMedia('(max-width: 768px)').matches;
        const minH = mobile ? 420 : 460;
        // Fill the viewport whatever the tree size, then re-fit the tree inside it.
        const h = Math.max(minH, Math.round(vh * (mobile ? 0.82 : 0.88)));
        stage.style.setProperty('height', h + 'px', 'important');
        stage.style.setProperty('min-height', minH + 'px', 'important');
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
            if (typeof fam.fit === 'function') fam.fit('family', null, false);
        }, 300);
    };

    window.traceKinshipToSelected = function(targetNodeId, targetName) {
        if (!window.family || typeof window.family.traceLineage !== 'function') return;
        const rootNodeId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        window.family.traceLineage(targetNodeId, rootNodeId);

        const banner = document.getElementById('lineageTraceBanner');
        const text = document.getElementById('lineageTraceText');
        if (banner && text) {
            text.textContent = `Tracing Lineage: ${targetName || 'Relative'} ➔ You`;
            banner.classList.remove('d-none');
        }
        const modal = document.getElementById('personModal');
        if (modal) modal.style.display = 'none';
    };

    window.selectedNodeId = null;
    let currentInviteLink = '';

    window.openPersonCard = function(node) {
        if (!node) return;
        window.selectedNodeId = String(node.id);
        
        const card = document.getElementById('personDetailsCard');
        if (!card) return;

        const nameEl = document.getElementById('cardName');
        const roleEl = document.getElementById('cardRole');
        const idEl = document.getElementById('cardId');
        const genderEl = document.getElementById('cardGender');
        const avatarEl = document.getElementById('cardAvatar');
        const descendantsEl = document.getElementById('cardDescendants');
        const statusBadge = document.getElementById('cardStatusBadge');
        const registeredActions = document.getElementById('cardRegisteredActions');
        const claimSpotSection = document.getElementById('cardClaimSpotSection');

        if (nameEl) nameEl.textContent = node.name || 'Family Relative';
        if (roleEl) roleEl.textContent = (node.title || 'Family Member').toUpperCase();
        if (idEl) idEl.textContent = node.id + (node.legacyId ? ' (' + node.legacyId + ')' : '');
        const hasKnownGender = ['male', 'female'].includes(String(node.gender || '').toLowerCase());
        if (genderEl) genderEl.textContent = hasKnownGender ? node.gender.toUpperCase() : 'NOT SET';
        const setGenderWrap = document.getElementById('cardSetGenderWrap');
        if (setGenderWrap) {
            const canEditTree = !(Boolean(window.__IS_READ_ONLY__) || (typeof graphData !== 'undefined' && graphData && graphData.isReadOnly));
            setGenderWrap.style.display = (!hasKnownGender && canEditTree && !node.is_placeholder) ? 'block' : 'none';
            setGenderWrap.querySelectorAll('[data-set-gender]').forEach(btn => {
                btn.onclick = () => {
                    const fd = new FormData();
                    fd.append('node_id', node.id);
                    fd.append('gender', btn.dataset.setGender);
                    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                    fetch('/member/organogram/editor/set-gender', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(d => { if (d?.status === 'success') window.location.reload(); else alert(d?.message || 'Could not update gender.'); })
                        .catch(() => alert('Could not update gender.'));
                };
            });
        }
        const isDeceased = Boolean(node.isDeceased || node.is_deceased || (node.raw && node.raw.is_deceased));

        if (avatarEl) {
            avatarEl.src = node.img || (node.gender === 'female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');
            avatarEl.style.filter = isDeceased ? 'grayscale(100%)' : 'none';
            avatarEl.style.borderColor = isDeceased ? '#94a3b8' : '';
        }

        const layoutNode = window.family && window.family.layout && window.family.layout.nodes 
            ? window.family.layout.nodes.find(n => String(n.id) === String(node.id)) 
            : null;
        if (descendantsEl) descendantsEl.textContent = layoutNode ? layoutNode.descendantCount : 0;

        // Lifespan display in floating detail card
        const lifespanRow = document.getElementById('cardLifespanRow');
        const lifespanEl = document.getElementById('cardLifespan');
        const birthYr = node.birth_year || (node.birth_date ? String(node.birth_date).substring(0, 4) : (node.raw && node.raw.birth_year ? String(node.raw.birth_year) : (node.raw && node.raw.birth_date ? String(node.raw.birth_date).substring(0, 4) : null)));
        const deathYr = node.death_year || (node.death_date ? String(node.death_date).substring(0, 4) : (node.raw && node.raw.death_year ? String(node.raw.death_year) : (node.raw && node.raw.death_date ? String(node.raw.death_date).substring(0, 4) : null)));
        if (isDeceased) {
            const lifeStr = (birthYr && deathYr) ? `${birthYr} – ${deathYr}` : (deathYr ? `Passed in ${deathYr}` : (birthYr ? `Born in ${birthYr} (Deceased)` : 'In Loving Memory'));
            if (lifespanRow && lifespanEl) {
                lifespanEl.textContent = lifeStr;
                lifespanRow.style.display = 'block';
            }
        } else if (lifespanRow) {
            lifespanRow.style.display = 'none';
        }

        // Occupation, Email & Mobile Contact Details
        const occEl = document.getElementById('cardOccupation');
        const emailEl = document.getElementById('cardEmail');
        const mobileEl = document.getElementById('cardMobile');

        const occupationVal = (node.occupation || (node.raw && node.raw.occupation) || '').trim();
        if (occEl) {
            occEl.textContent = occupationVal || 'Not specified';
        }

        const emailVal = (node.email || (node.raw && node.raw.email) || '').trim();
        if (emailEl) {
            if (emailVal) {
                const safeEmail = encodeURI(emailVal);
                const safeDisplay = typeof escapeHtmlText === 'function' ? escapeHtmlText(emailVal) : emailVal;
                emailEl.innerHTML = `<a href="mailto:${safeEmail}" class="card-contact-link text-info text-decoration-none" title="Send email to ${safeEmail}">${safeDisplay}</a>`;
            } else {
                emailEl.innerHTML = `<span class="text-white-50 fst-italic">Not provided</span>`;
            }
        }

        const mobileVal = (node.mobile || (node.raw && node.raw.mobile) || '').trim();
        if (mobileEl) {
            if (mobileVal) {
                const safeMobile = encodeURI(mobileVal);
                const safeDisplay = typeof escapeHtmlText === 'function' ? escapeHtmlText(mobileVal) : mobileVal;
                mobileEl.innerHTML = `<a href="tel:${safeMobile}" class="card-contact-link text-success text-decoration-none" title="Call ${safeMobile}"><i class="bi bi-telephone-outbound me-1"></i>${safeDisplay}</a>`;
            } else {
                mobileEl.innerHTML = `<span class="text-white-50 fst-italic">Not provided</span>`;
            }
        }

        const isReadOnlyTree = Boolean(window.__IS_READ_ONLY__) || (typeof graphData !== 'undefined' && graphData && graphData.isReadOnly);

        if (isDeceased) {
            if (statusBadge) {
                statusBadge.className = 'card-status-badge card-status-deceased';
                statusBadge.innerHTML = '<i class="bi bi-flower1"></i> <span>In Loving Memory (Deceased)</span>';
            }
            if (registeredActions) registeredActions.style.display = node.legacyId ? 'block' : 'none';
            if (claimSpotSection) claimSpotSection.style.display = 'none';
            const profileLink = document.getElementById('cardViewProfileLink');
            if (profileLink && node.legacyId) profileLink.href = '/allMembers/seeProfile/' + encodeURIComponent(node.legacyId);
        } else if (node.legacyId) {
            if (statusBadge) {
                statusBadge.className = 'card-status-badge card-status-registered';
                statusBadge.innerHTML = '<i class="bi bi-patch-check-fill"></i> <span>Registered Member</span>';
            }
            if (registeredActions) registeredActions.style.display = 'block';
            const profileLink = document.getElementById('cardViewProfileLink');
            if (profileLink) profileLink.href = '/allMembers/seeProfile/' + encodeURIComponent(node.legacyId);
            if (claimSpotSection) claimSpotSection.style.display = 'none';
        } else {
            if (statusBadge) {
                statusBadge.className = 'card-status-badge card-status-unclaimed';
                statusBadge.innerHTML = '<i class="bi bi-clock-history"></i> <span>Unclaimed Spot</span>';
            }
            if (registeredActions) registeredActions.style.display = 'none';
            if (claimSpotSection) claimSpotSection.style.display = isReadOnlyTree ? 'none' : 'block';

            if (!isReadOnlyTree) {
                const nodeFamCode = (node.raw && node.raw.family_code) ? String(node.raw.family_code).toUpperCase() : '';
                const birthFamCode = (window.__BIRTH_FAMILY_CODE__ || '').toUpperCase();
                const primaryFamCode = (window.__PRIMARY_FAMILY_CODE__ || window.__FAMILY_CODE__ || 'OLAOGUN').toUpperCase();
                const motherBirthCode = (window.__MOTHER_BIRTH_FAMILY_CODE__ || '').toUpperCase();
                const paternalSurname = (window.__PATERNAL_SURNAME__ || window.__MARITAL_SURNAME__ || primaryFamCode);
                const roleLower = String(node.title || node.ego_relation || '').toLowerCase();
                const egoSide = String(node.ego_side || (node.raw && node.raw.ego_side) || '').toLowerCase();

                // 1. Identify Lineage Sphere: Maternal vs Paternal/Birth vs Marital/Household
                const isMaternalRelative = (egoSide === 'maternal') || 
                                           roleLower.includes('maternal') || 
                                           (window.__ACTIVE_LINEAGE_SCOPE__ === 'maternal') ||
                                           (Boolean(nodeFamCode) && Boolean(motherBirthCode) && nodeFamCode === motherBirthCode);

                const isPaternalBirthRelative = (egoSide === 'paternal') || 
                                                roleLower.includes('paternal') || 
                                                /(father|dad|brother|sister|sibling)/.test(roleLower);

                let familyCode = nodeFamCode || (window.__FAMILY_CODE__ || primaryFamCode);
                let familySurname = window.__MARITAL_SURNAME__ || primaryFamCode;
                let treeContextLabel = `${familySurname} Family Tree (#${familyCode})`;
                let lineageScope = 'primary';
                let claimSubtitleText = `Send a personal invite to join your <strong>Household Family Tree (#${familyCode})</strong> and claim this spot.`;

                if (isMaternalRelative) {
                    lineageScope = 'maternal';
                    const rawNodeLast = (node.last_name || (node.raw && node.raw.last_name) || '').trim();
                    const maternalSurname = (rawNodeLast && rawNodeLast.toUpperCase() !== paternalSurname.toUpperCase())
                        ? rawNodeLast
                        : (window.__MOTHER_MAIDEN_SURNAME__ || 'Maternal');
                    familySurname = maternalSurname;

                    if (motherBirthCode) {
                        familyCode = motherBirthCode;
                        treeContextLabel = `${maternalSurname} Birth Family Tree (#${familyCode})`;
                        claimSubtitleText = `Send a personal invite to join the <strong>${maternalSurname} Birth Family (#${familyCode})</strong> and claim this spot.`;
                    } else {
                        treeContextLabel = `${maternalSurname} Family Tree (Maternal Lineage)`;
                        claimSubtitleText = `Send a personal invite to join our <strong>${maternalSurname} Family Tree (Maternal Lineage)</strong> and claim this spot.`;
                    }
                } else if (window.__HAS_BIRTH_FAMILY__ && birthFamCode && (isPaternalBirthRelative || nodeFamCode === birthFamCode)) {
                    lineageScope = 'birth';
                    familyCode = birthFamCode;
                    familySurname = window.__MAIDEN_SURNAME__ || familyCode;
                    treeContextLabel = `${familySurname} Birth Family Tree (#${familyCode})`;
                    claimSubtitleText = `Send a personal invite to join your <strong>Birth / Maiden Family (#${familyCode})</strong> and claim this spot.`;
                } else if (isPaternalBirthRelative) {
                    lineageScope = 'primary';
                    familySurname = paternalSurname;
                    treeContextLabel = `${paternalSurname} Birth / Paternal Family Tree (#${familyCode})`;
                    claimSubtitleText = `Send a personal invite to join your <strong>Birth / Paternal Family Tree (#${familyCode})</strong> and claim this spot.`;
                } else {
                    claimSubtitleText = `Send a personal invite to join your <strong>Household Family Tree (#${familyCode})</strong> and claim this spot.`;
                }

                const cleanName = (node.name || 'Relative').replace(/\s+/g, ' ').trim();

                // Update card subtitle to clarify Marital vs Birth family code
                const claimSubtitle = document.querySelector('#cardClaimSpotSection .claim-spot-subtitle');
                if (claimSubtitle) {
                    claimSubtitle.innerHTML = claimSubtitleText;
                }

                // Generate opaque invite token via AJAX — zero PII in the URL
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                  document.querySelector('input[name="token"]')?.value || '';

                fetch('/api/invite/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': csrfToken,
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        family_code: familyCode,
                        lineage_scope: lineageScope,
                        family_surname: familySurname,
                        first_name: cleanName.split(' ')[0] || '',
                        last_name: cleanName.split(' ').slice(1).join(' ') || '',
                        node_id: node.id || null,
                        type: 'organogram',
                        token: csrfToken
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.invite_url) {
                        const inviteLink = data.invite_url;
                        currentInviteLink = inviteLink;

                        const relativeRole = node.title || node.ego_relation || 'Family Relative';
                        const inviteMessageText = data.whatsapp_message || (
                            `🌳 *Family Heritage Invitation for ${cleanName}* 🌳\n\n` +
                            `Hello ${cleanName},\n\n` +
                            `You have been honoured as our *${relativeRole}* in the *${treeContextLabel}* on FamilyPlatform.\n\n` +
                            `🔑 *Family Code:* #${familyCode}\n\n` +
                            `✨ Claim your spot on the live family tree, share photos, record your living voice capsule, and preserve our ancestral heritage:\n\n` +
                            `👉 ${inviteLink}\n\n` +
                            `Welcome to our living family legacy! 🏰`
                        );
                        const inviteMessage = encodeURIComponent(inviteMessageText);

                        const whatsappBtn = document.getElementById('cardWhatsappBtn');
                        const smsBtn = document.getElementById('cardSmsBtn');
                        if (whatsappBtn) whatsappBtn.href = `https://api.whatsapp.com/send?text=${inviteMessage}`;
                        if (smsBtn) smsBtn.href = `sms:?body=${inviteMessage}`;
                    } else {
                        console.error('[organogram] invite token generation failed', data);
                    }
                })
                .catch(err => {
                    console.error('[organogram] invite token generation error:', err);
                });
            }
        }

        // Living Voice Capsule synchronization
        const voiceSection = document.getElementById('cardVoiceCapsuleSection');
        const voiceBadge = document.getElementById('voiceCapsuleStatusBadge');
        const voicePlayerWrapper = document.getElementById('cardVoicePlayerWrapper');
        const voiceEmptyWrapper = document.getElementById('cardVoiceEmptyWrapper');
        const voiceAudio = document.getElementById('cardVoiceAudio');

        const voiceUrl = node.voice_capsule_url || (node.raw ? node.raw.voice_capsule_url : null);
        if (voiceUrl) {
            if (voiceBadge) {
                voiceBadge.className = 'badge bg-warning text-dark';
                voiceBadge.innerHTML = '<i class="bi bi-soundwave me-1"></i> Recorded';
            }
            if (voiceAudio) {
                voiceAudio.src = voiceUrl;
            }
            if (voicePlayerWrapper) voicePlayerWrapper.style.display = 'block';
            if (voiceEmptyWrapper) voiceEmptyWrapper.style.display = 'none';
        } else {
            if (voiceBadge) {
                voiceBadge.className = 'badge';
                voiceBadge.style.background = 'rgba(255,255,255,0.1)';
                voiceBadge.style.color = '#cbd5e1';
                voiceBadge.textContent = 'None';
            }
            if (voiceAudio) {
                voiceAudio.pause();
                voiceAudio.removeAttribute('src');
            }
            if (voicePlayerWrapper) voicePlayerWrapper.style.display = 'none';
            if (voiceEmptyWrapper) voiceEmptyWrapper.style.display = 'block';
        }

        const isRootUser = (String(node.legacyId) === String(window.__ROOT_USER_ID__)) || 
                           (String(node.id) === String(getGraphNodeId(window.__ROOT_USER_ID__, '')));
        const removeWrapper = document.getElementById('cardRemoveActionWrapper');
        if (removeWrapper) {
            removeWrapper.style.display = (isRootUser || isReadOnlyTree) ? 'none' : 'block';
        }

        // Spouse / In-Law Extended Family Branch Progressive Disclosure Control
        const inLawWrapper = document.getElementById('cardInLawBranchWrapper');
        const inLawText = document.getElementById('cardToggleInLawBranchText');
        const inLawCount = Number(node.inlaw_branch_count || (node.raw && node.raw.inlaw_branch_count) || 0);
        if (inLawWrapper && inLawText) {
            if (inLawCount > 0) {
                const isExpanded = Boolean(window.family && typeof window.family.isInLawBranchExpanded === 'function' && window.family.isInLawBranchExpanded(node.id));
                const firstName = (node.nameL1 || (node.name || 'Spouse').split(' ')[0] || 'Spouse');
                inLawText.textContent = isExpanded
                    ? `Hide ${firstName}'s Extended Family`
                    : `Show ${firstName}'s Family (+${inLawCount} Relatives)`;
                inLawWrapper.style.display = 'block';
            } else {
                inLawWrapper.style.display = 'none';
            }
        }

        card.style.display = 'block';
    };

    window.promptOrToggleSpouseInLawBranch = function(spouseNodeId) {
        if (!spouseNodeId || !window.family || typeof window.family.toggleInLawBranch !== 'function') return;
        const node = (window.familyTreeNodes || []).find(n => String(n.id) === String(spouseNodeId));
        const inLawCount = Number((node && (node.inlaw_branch_count || (node.raw && node.raw.inlaw_branch_count))) || 0);
        if (inLawCount <= 0) return;

        const isCurrentlyExpanded = window.family.isInLawBranchExpanded(spouseNodeId);
        const firstName = (node && (node.nameL1 || (node.name || 'Spouse').split(' ')[0])) || 'Spouse';
        const fullName = (node && node.name) || firstName;

        // If already expanded, collapse immediately and keep the tree clean
        if (isCurrentlyExpanded) {
            window.family.toggleInLawBranch(spouseNodeId, false);
            if (String(window.selectedNodeId) === String(spouseNodeId) && typeof window.openPersonCard === 'function') {
                window.openPersonCard(node);
            }
            return;
        }

        // Prompt the user before expanding the spouse's extended family branch onto the canvas
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `Show ${firstName}'s Family Lineage?`,
                html: `<p class="text-muted small mb-0">To keep your primary family tree clean and easy to read, <strong>${fullName}'s</strong> parents and siblings (<strong>${inLawCount} relative${inLawCount === 1 ? '' : 's'}</strong>) are tucked away by default.<br><br>Would you like to expand ${firstName}'s family branch on this tree?</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
                confirmButtonText: `<i class="bi bi-people-fill me-1"></i> Show ${firstName}'s Family (+${inLawCount})`,
                cancelButtonText: 'Keep Hidden'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.family.toggleInLawBranch(spouseNodeId, true);
                    if (String(window.selectedNodeId) === String(spouseNodeId) && typeof window.openPersonCard === 'function') {
                        window.openPersonCard(node);
                    }
                }
            });
        } else {
            if (confirm(`Show ${fullName}'s extended family (${inLawCount} relatives) on the tree?`)) {
                window.family.toggleInLawBranch(spouseNodeId, true);
                if (String(window.selectedNodeId) === String(spouseNodeId) && typeof window.openPersonCard === 'function') {
                    window.openPersonCard(node);
                }
            }
        }
    };

    window.removeSelectedCardNode = function() {
        if (!window.selectedNodeId) return;
        const node = familyTreeNodes.find(n => String(n.id) === String(window.selectedNodeId));
        const name = node ? node.name : 'this relative';
        window.confirmRemovePerson(window.selectedNodeId, name);
    };

    window.removeCurrentModalNode = function() {
        if (!currentBaseNodeId) return;
        window.confirmRemovePerson(currentBaseNodeId, currentBaseNodeName || 'this relative');
    };

    window.confirmRemovePerson = function(nodeId, nodeName) {
        if (!nodeId) return;
        
        const rootGraphId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        const isTreeRoot = (String(nodeId) === String(rootGraphId)) || (String(nodeId) === String(window.__ROOT_USER_ID__));
        const isSelfLoggedIn = window.__LOGGED_IN_USER_ID__ && (
            String(nodeId) === String(getGraphNodeId(window.__LOGGED_IN_USER_ID__, '')) ||
            String(nodeId) === String(window.__LOGGED_IN_USER_ID__)
        );

        if (isTreeRoot || isSelfLoggedIn) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Action Not Allowed', 'You cannot remove the primary member from the family tree.', 'warning');
            } else {
                alert('You cannot remove the primary member from the family tree.');
            }
            return;
        }

        const performDelete = () => {
            const formData = new FormData();
            formData.append('node_id', nodeId);

            fetch('/member/organogram/editor/delete', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const isOk = (data.ok === true || data.status === 200 || data.status === 'success' || data.statusCode === 200);
                if (isOk) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Removed!',
                            text: `${nodeName || 'Relative'} has been removed from your family tree.`,
                            icon: 'success',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert(`${nodeName || 'Relative'} has been removed.`);
                        window.location.reload();
                    }
                } else {
                    const err = (data.message || data.error || 'Failed to remove relative.');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', typeof err === 'object' ? JSON.stringify(err) : err, 'error');
                    } else {
                        alert(typeof err === 'object' ? JSON.stringify(err) : err);
                    }
                }
            })
            .catch(err => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'A network error occurred while removing relative.', 'error');
                } else {
                    alert('A network error occurred while removing relative.');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `Remove ${nodeName || 'Relative'}?`,
                text: 'Are you sure you want to remove this relative from your family tree? This will unlink their tree connections.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, remove from tree',
                cancelButtonText: 'Cancel',
                didOpen: (modal) => {
                    const container = document.querySelector('.swal2-container');
                    if (container) container.style.zIndex = '100000';
                }
            }).then(result => {
                if (result.isConfirmed) {
                    performDelete();
                }
            });
        } else {
            if (confirm(`Are you sure you want to remove ${nodeName || 'this relative'} from your family tree?`)) {
                performDelete();
            }
        }
    };

    window.removePersonFallback = function(nodeId, nodeName) {
        window.confirmRemovePerson(nodeId, nodeName);
    };

    window.openManageRelativesModal = function() {
        const listEl = document.getElementById('manageRelativesList');
        if (!listEl) return;

        const isReadOnlyTree = Boolean(window.__IS_READ_ONLY__) || (typeof graphData !== 'undefined' && graphData && graphData.isReadOnly);
        if (isReadOnlyTree) {
            listEl.innerHTML = '<div class="alert alert-warning mb-0">You have read-only access to this family tree and cannot remove relatives.</div>';
            const modalEl = document.getElementById('manageRelativesModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            return;
        }

        const rootGraphId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        const allNodes = (window.graphData && window.graphData.nodes) || [];

        const removableNodes = allNodes.filter(n => {
            const isRoot = (String(n.id) === String(rootGraphId)) || (String(n.user_id) === String(window.__ROOT_USER_ID__));
            const isSelf = window.__LOGGED_IN_USER_ID__ && String(n.user_id) === String(window.__LOGGED_IN_USER_ID__);
            return !isRoot && !isSelf;
        });

        if (removableNodes.length === 0) {
            listEl.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                    <p class="mb-0 fw-semibold">No removable relatives found in this family tree.</p>
                    <small>Primary root members cannot be removed.</small>
                </div>
            `;
        } else {
            let html = '<div class="d-flex flex-column gap-2">';
            removableNodes.forEach(n => {
                const name = n.full_name || (n.first_name + ' ' + (n.last_name || '')).trim() || 'Relative';
                const role = n.ego_relation || n.bio || 'Family Member';
                const avatar = n.avatar_url || ((n.gender || '').toLowerCase() === 'female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');
                const cleanName = name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                
                html += `
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-white shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${avatar}" alt="${cleanName}" class="rounded-circle object-fit-cover" style="width: 44px; height: 44px; border: 2px solid #e2e8f0;">
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">${name}</h6>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.75rem;">${role}</span>
                                ${n.user_id ? '<span class="badge bg-success-subtle text-success ms-1" style="font-size: 0.7rem;">Registered</span>' : ''}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3 py-1.5" onclick="window.confirmRemovePerson(${n.id}, '${cleanName}')" style="border-radius: 8px;">
                            <i class="bi bi-trash3-fill me-1"></i> Remove
                        </button>
                    </div>
                `;
            });
            html += '</div>';
            listEl.innerHTML = html;
        }

        const modalEl = document.getElementById('manageRelativesModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };


    window.closePersonCard = function() {
        const card = document.getElementById('personDetailsCard');
        if (card) card.style.display = 'none';
    };

    window.traceCardLineage = function() {
        if (!window.selectedNodeId) return;
        const target = familyTreeNodes.find(n => String(n.id) === String(window.selectedNodeId));
        const rootId = String(graphData.root_node_id || getGraphNodeId(window.__ROOT_USER_ID__, '') || (familyTreeNodes[0] ? familyTreeNodes[0].id : ''));
        
        if (window.family && typeof window.family.traceLineage === 'function') {
            window.family.traceLineage(window.selectedNodeId, rootId);
        }
        
        const banner = document.getElementById('lineageBanner');
        const text = document.getElementById('lineageBannerText');
        if (banner && text) {
            text.textContent = 'Tracing Lineage: You → ' + (target ? target.name : 'Relative');
            banner.style.display = 'inline-flex';
        }
    };

    window.centerCardNode = function() {
        if (window.selectedNodeId && window.family && typeof window.family.center === 'function') {
            window.family.center(window.selectedNodeId, 0.95);
        }
    };

    window.copyCardInviteLink = function(btn) {
        if (!currentInviteLink) return;
        navigator.clipboard.writeText(currentInviteLink).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = orig;
            }, 2000);
        }).catch(() => {
            prompt('Invite link:', currentInviteLink);
        });
    };

    window.openStoryCardFromCard = function() {
        if (!window.selectedNodeId) return;
        const node = familyTreeNodes.find(n => String(n.id) === String(window.selectedNodeId));
        if (node && typeof window.openHeritageStoryModal === 'function') {
            window.openHeritageStoryModal(node.name, node.title, node.img, false);
        }
    };

    window.clearLineageTrace = function() {
        if (window.family && typeof window.family.clearLineageTrace === 'function') {
            window.family.clearLineageTrace();
        }
        const banner = document.getElementById('lineageBanner');
        if (banner) banner.style.display = 'none';
        const legacyBanner = document.getElementById('lineageTraceBanner');
        if (legacyBanner) legacyBanner.classList.add('d-none');
    };

    window.centerOnMe = function() {
        if (!window.family) return;
        const rootNodeId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        if (rootNodeId) {
            window.family.center(rootNodeId, 0.9);
        } else {
            window.family.fit('family');
        }
    };

    window.fitTreeScreen = function() {
        if (!window.family) return;
        window.family.fit('family');
    };

    window.zoomInTree = function() {
        if (!window.family) return;
        window.family.zoom(1.25);
    };

    window.zoomOutTree = function() {
        if (!window.family) return;
        window.family.zoom(0.8);
    };

    window.toggleFullscreenTree = function() {
        const container = document.getElementById('treeStage');
        const btn = document.getElementById('btnTreeFullscreen');
        if (!container) return;
        const isFull = container.classList.toggle('fullscreen-active');
        if (btn) {
            btn.innerHTML = isFull ? '<i class="bi bi-fullscreen-exit"></i>' : '<i class="bi bi-arrows-fullscreen"></i>';
        }
        if (window.family) {
            setTimeout(() => window.family.fit('family'), 250);
        }
    };

    function renderPedigreeOutline() {
        const container = document.getElementById('pedigreeOutlineContainer');
        if (!container || !graphData || !graphData.nodes) return;

        const generations = {
            '-2': { title: 'Grandparents & Ancestors', icon: 'bi-hourglass-split', nodes: [] },
            '-1': { title: 'Parents, Uncles & Aunts', icon: 'bi-person-heart', nodes: [] },
            '0':  { title: 'Core Household, You & Siblings', icon: 'bi-house-heart-fill', nodes: [] },
            '1':  { title: 'Children', icon: 'bi-emoji-smile-fill', nodes: [] },
            '2':  { title: 'Grandchildren', icon: 'bi-stars', nodes: [] }
        };
        const otherNodes = [];

        graphData.nodes.forEach(node => {
            const lvl = String(node.generation_level !== undefined ? node.generation_level : 0);
            if (generations[lvl]) {
                generations[lvl].nodes.push(node);
            } else {
                otherNodes.push(node);
            }
        });

        let html = '';
        const order = ['-2', '-1', '0', '1', '2'];
        order.forEach(lvl => {
            const gen = generations[lvl];
            if (gen.nodes.length === 0) return;

            html += `
            <div class="pedigree-generation-card">
                <div class="pedigree-gen-header">
                    <h3 class="pedigree-gen-title">
                        <i class="bi ${gen.icon}"></i> ${gen.title}
                    </h3>
                    <span class="pedigree-gen-badge">${gen.nodes.length} Relative${gen.nodes.length === 1 ? '' : 's'}</span>
                </div>
                <div class="pedigree-member-grid">
                    ${gen.nodes.map(node => {
                        const fullName = (node.full_name || `${node.first_name || ''} ${node.last_name || ''}`).trim() || 'Family Member';
                        const avatar = node.avatar_url || (node.gender === 'Female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');
                        const role = node.bio || (lvl === '0' ? (String(node.user_id) === String(window.__ROOT_USER_ID__) ? 'You (Self)' : 'Household / Sibling') : (lvl === '-1' ? 'Parent / Elder' : (lvl === '-2' ? 'Grandparent' : (lvl === '1' ? 'Child' : 'Descendant'))));
                        const isDeceased = Boolean(node.is_deceased);
                        const isSelf = String(node.user_id) === String(window.__ROOT_USER_ID__);

                        return `
                        <div class="pedigree-member-card ${isDeceased ? 'pedigree-deceased-card' : ''} ${isSelf ? 'border-primary' : ''}" onclick="openNodeFromPedigree('${node.id}')">
                            <img src="${avatar}" alt="${fullName}" class="pedigree-member-avatar ${isDeceased ? 'pedigree-avatar-deceased' : ''}" onerror="this.src='/resources/images/profile/avatarM.png'">
                            <div class="pedigree-member-info">
                                <div class="pedigree-member-name">${fullName} ${isSelf ? '<span class="badge bg-primary ms-1" style="font-size: 0.65rem;">You</span>' : ''}</div>
                                <div class="pedigree-member-meta">
                                    <span class="pedigree-role-pill">${role}</span>
                                    ${isDeceased ? '<span class="pedigree-deceased-pill fw-bold"><i class="bi bi-flower1 text-warning"></i> In Memory</span>' : ''}
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted fs-6"></i>
                        </div>
                        `;
                    }).join('')}
                </div>
            </div>
            `;
        });

        if (otherNodes.length > 0) {
            html += `
            <div class="pedigree-generation-card">
                <div class="pedigree-gen-header">
                    <h3 class="pedigree-gen-title"><i class="bi bi-people"></i> Extended Lineage</h3>
                    <span class="pedigree-gen-badge">${otherNodes.length}</span>
                </div>
                <div class="pedigree-member-grid">
                    ${otherNodes.map(node => {
                        const fullName = (node.full_name || `${node.first_name || ''} ${node.last_name || ''}`).trim() || 'Family Member';
                        const avatar = node.avatar_url || (node.gender === 'Female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');
                        const isDeceased = Boolean(node.is_deceased);
                        return `
                        <div class="pedigree-member-card ${isDeceased ? 'pedigree-deceased-card' : ''}" onclick="openNodeFromPedigree('${node.id}')">
                            <img src="${avatar}" alt="${fullName}" class="pedigree-member-avatar ${isDeceased ? 'pedigree-avatar-deceased' : ''}" onerror="this.src='/resources/images/profile/avatarM.png'">
                            <div class="pedigree-member-info">
                                <div class="pedigree-member-name">${fullName}</div>
                                <div class="pedigree-member-meta">
                                    <span class="pedigree-role-pill">${node.bio || 'Relative'}</span>
                                    ${isDeceased ? '<span class="pedigree-deceased-pill fw-bold"><i class="bi bi-flower1 text-warning"></i> In Memory</span>' : ''}
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted fs-6"></i>
                        </div>
                        `;
                    }).join('')}
                </div>
            </div>
            `;
        }

        container.innerHTML = html;
    }

    window.openNodeFromPedigree = function(nodeId) {
        if (!graphData || !graphData.nodes) return;
        const rawNode = graphData.nodes.find(n => String(n.id) === String(nodeId));
        if (!rawNode) return;

        currentBaseNodeName = rawNode.full_name || `${rawNode.first_name || ''} ${rawNode.last_name || ''}`.trim();
        currentBaseNodeId = rawNode.id;
        selectedNodeId = String(rawNode.id);

        const isReadOnlyTree = Boolean(window.__IS_READ_ONLY__) || (typeof graphData !== 'undefined' && graphData && graphData.isReadOnly);
        const rootGraphId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        const isRoot = (String(rawNode.id) === String(rootGraphId)) || (String(rawNode.user_id) === String(window.__ROOT_USER_ID__));
        const modalRemoveBtn = document.getElementById('modalRemoveNodeBtn');
        if (modalRemoveBtn) {
            modalRemoveBtn.style.display = (isRoot || isReadOnlyTree) ? 'none' : 'inline-block';
        }

        if (typeof window.showPersonDetails === 'function') {

            window.showPersonDetails({
                fullName: currentBaseNodeName,
                img: rawNode.avatar_url || (rawNode.gender === 'Female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png'),
                relation: rawNode.bio || 'Family Member',
                personId: rawNode.user_id,
                familyCode: window.__FAMILY_CODE__,
                email: rawNode.email || '',
                mobile: rawNode.mobile || '',
                maritalStatus: rawNode.marital_status || '',
                occupation: rawNode.occupation || '',
                country: rawNode.country_of_residence || rawNode.location || '',
                nodeId: rawNode.id,
                isDeceased: Boolean(rawNode.is_deceased),
                isRegistered: !!rawNode.user_id,
                isReadOnly: isReadOnlyTree
            });
        }
    };

    // Offline Graph Caching & Network Monitoring
    if (graphData && graphData.nodes && graphData.nodes.length > 0) {
        try {
            localStorage.setItem('fp_cached_tree_' + window.__FAMILY_CODE__, JSON.stringify(graphData));
        } catch(e) {}
    }

    function updateNetworkStatus() {
        const badge = document.getElementById('treeOfflineBadge');
        if (badge) {
            if (!navigator.onLine) {
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus();

    /* =======================================================
       KINSHIP PATHFINDER (BFS GRAPH TRAVERSAL)
       ======================================================= */
    window.findKinshipPath = function(startId, targetId) {
        if (!graphData || !graphData.nodes) return null;
        startId = String(startId);
        targetId = String(targetId);
        if (startId === targetId) return [{ id: startId, edgeType: 'self' }];

        const adj = {};
        graphData.nodes.forEach(n => {
            adj[String(n.id)] = [];
        });

        // 1. Spouses (unions)
        if (graphData.unions) {
            graphData.unions.forEach(u => {
                const p1 = String(u.partner_1_id);
                const p2 = String(u.partner_2_id);
                if (adj[p1] && adj[p2]) {
                    adj[p1].push({ to: p2, edgeType: 'spouse' });
                    adj[p2].push({ to: p1, edgeType: 'spouse' });
                }
            });
        }

        // 2. Parents & Children
        if (graphData.children && graphData.unions) {
            graphData.children.forEach(c => {
                const childId = String(c.child_id);
                const union = graphData.unions.find(u => String(u.id) === String(c.union_id));
                if (union) {
                    const parents = [String(union.partner_1_id), String(union.partner_2_id)].filter(pid => adj[pid]);
                    parents.forEach(pId => {
                        if (adj[childId]) adj[childId].push({ to: pId, edgeType: 'parent' });
                        if (adj[pId]) adj[pId].push({ to: childId, edgeType: 'child' });
                    });
                }
            });
        }

        // BFS traversal
        const queue = [[{ id: startId, edgeType: 'start' }]];
        const visited = new Set([startId]);

        while (queue.length > 0) {
            const path = queue.shift();
            const current = path[path.length - 1];

            if (current.id === targetId) {
                return path;
            }

            const neighbors = adj[current.id] || [];
            for (const edge of neighbors) {
                if (!visited.has(edge.to)) {
                    visited.add(edge.to);
                    queue.push([...path, { id: edge.to, edgeType: edge.edgeType }]);
                }
            }
        }

        return null;
    };

    window.traceKinshipToSelected = function(targetNodeId, targetName) {
        const trailContainer = document.getElementById('kinshipPathTrail');
        if (!trailContainer) return;

        // Resolve root user node id
        const rootNodeId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        if (!rootNodeId) {
            trailContainer.innerHTML = '<span class="text-muted small">Could not locate root user in tree.</span>';
            trailContainer.classList.remove('d-none');
            return;
        }

        const path = window.findKinshipPath(rootNodeId, targetNodeId);
        if (!path || path.length === 0) {
            trailContainer.innerHTML = '<span class="text-muted small">No direct genealogical link found in current 6-generation graph.</span>';
            trailContainer.classList.remove('d-none');
            return;
        }

        // Build step breadcrumbs
        let stepsHtml = '<div class="kinship-path-header"><i class="bi bi-diagram-2"></i> Relationship Lineage Path</div>';
        stepsHtml += '<div class="kinship-step-pills">';

        path.forEach((step, idx) => {
            const node = graphData.nodes.find(n => String(n.id) === String(step.id));
            const name = node ? (node.full_name || `${node.first_name || ''} ${node.last_name || ''}`).trim() : 'Relative';
            const isSelf = idx === 0;
            const isTarget = idx === path.length - 1;

            let badgeClass = isSelf ? 'self' : (isTarget ? 'target' : '');
            let label = isSelf ? 'You' : name;

            stepsHtml += `<span class="kinship-node-pill ${badgeClass}">${label}</span>`;
            if (idx < path.length - 1) {
                let relationText = '➔';
                const nextStep = path[idx + 1];
                if (nextStep.edgeType === 'parent') relationText = 'parent ➔';
                else if (nextStep.edgeType === 'child') relationText = 'child ➔';
                else if (nextStep.edgeType === 'spouse') relationText = 'partner ➔';
                stepsHtml += `<span class="kinship-arrow-pill">${relationText}</span>`;
            }
        });

        stepsHtml += '</div>';
        trailContainer.innerHTML = stepsHtml;
        trailContainer.classList.remove('d-none');

        // If on canvas, center on the target node
        if (window.family && !document.getElementById('treeContainer')?.classList.contains('d-none')) {
            window.family.center(targetNodeId);
        }

        if (window.trackTreeAnalytics) {
            window.trackTreeAnalytics('kinship_path_traced', String(targetNodeId), {
                path_length: path.length,
                target_name: targetName
            });
        }
    };

    /* =======================================================
       HERITAGE STORY CARD GENERATOR (9:16 CANVAS EXPORT)
       ======================================================= */
    window.openHeritageStoryModal = function(fullName, relation, imgUrl, isDeceased) {
        const modalEl = document.getElementById('heritageStoryModal');
        const canvas = document.getElementById('storyCanvas');
        if (!modalEl || !canvas) return;

        const ctx = canvas.getContext('2d');
        const W = canvas.width;  // 540
        const H = canvas.height; // 960

        // 1. Rich Gradient Background
        const grad = ctx.createLinearGradient(0, 0, W, H);
        grad.addColorStop(0, '#0f172a');
        grad.addColorStop(0.35, '#2a0a0d');
        grad.addColorStop(0.7, '#1e3a29');
        grad.addColorStop(1, '#064e3b');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, W, H);

        // 2. Gold Border Frame
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 4;
        ctx.strokeRect(16, 16, W - 32, H - 32);

        // Inner ornate corners
        ctx.strokeStyle = 'rgba(212, 175, 55, 0.4)';
        ctx.lineWidth = 1;
        ctx.strokeRect(26, 26, W - 52, H - 52);

        function renderCardDetails() {
            // 3. Typography & Badges
            ctx.textAlign = 'center';
            ctx.fillStyle = '#d4af37';
            ctx.font = '600 16px Inter, sans-serif';
            ctx.letterSpacing = '3px';
            ctx.fillText('FAMILY DYNASTY ARCHIVE', W / 2, 85);

            // Member Name
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 32px Inter, sans-serif';
            ctx.fillText(fullName || 'Family Relative', W / 2, 460);

            // Kinship Role Pill
            ctx.fillStyle = 'rgba(212, 175, 55, 0.2)';
            ctx.beginPath();
            ctx.roundRect(W / 2 - 130, 485, 260, 36, 18);
            ctx.fill();
            ctx.strokeStyle = '#d4af37';
            ctx.lineWidth = 1.5;
            ctx.stroke();

            ctx.fillStyle = '#fef08a';
            ctx.font = 'bold 15px Inter, sans-serif';
            ctx.fillText((relation || 'Relative').toUpperCase(), W / 2, 508);

            if (isDeceased) {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                ctx.font = 'italic 14px Inter, sans-serif';
                ctx.fillText('🕊️ In Loving Memory', W / 2, 545);
            }

            // Dynasty Stats Box
            const boxY = 600;
            ctx.fillStyle = 'rgba(255, 255, 255, 0.08)';
            ctx.beginPath();
            ctx.roundRect(W / 2 - 200, boxY, 400, 130, 20);
            ctx.fill();
            ctx.strokeStyle = 'rgba(212, 175, 55, 0.3)';
            ctx.stroke();

            const count = (graphData && graphData.nodes) ? graphData.nodes.length : 1;
            ctx.font = 'bold 38px Inter, sans-serif';
            ctx.fillStyle = '#d4af37';
            ctx.fillText(String(count), W / 2 - 100, boxY + 60);
            ctx.font = '600 13px Inter, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.fillText('RECORDED KIN', W / 2 - 100, boxY + 90);

            ctx.font = 'bold 38px Inter, sans-serif';
            ctx.fillStyle = '#d4af37';
            ctx.fillText('6', W / 2 + 100, boxY + 60);
            ctx.font = '600 13px Inter, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.fillText('GENERATIONS', W / 2 + 100, boxY + 90);

            // Footer
            ctx.font = '14px Inter, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.fillText('familyplatform.com • Connect Your Heritage', W / 2, 880);
        }

        // 4. Portrait Rendering
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            const cx = W / 2;
            const cy = 300;
            const r = 110;

            ctx.save();
            ctx.beginPath();
            ctx.arc(cx, cy, r + 8, 0, Math.PI * 2);
            ctx.fillStyle = '#d4af37';
            ctx.fill();

            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.clip();
            ctx.drawImage(img, cx - r, cy - r, r * 2, r * 2);
            ctx.restore();

            renderCardDetails();
        };
        img.onerror = function() {
            const cx = W / 2;
            const cy = 300;
            const r = 110;

            ctx.beginPath();
            ctx.arc(cx, cy, r + 8, 0, Math.PI * 2);
            ctx.fillStyle = '#d4af37';
            ctx.fill();

            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.fillStyle = '#1e3a29';
            ctx.fill();

            ctx.font = '800 70px Inter, sans-serif';
            ctx.fillStyle = '#ffffff';
            ctx.textAlign = 'center';
            ctx.fillText(fullName ? fullName.charAt(0) : 'F', cx, cy + 25);

            renderCardDetails();
        };
        img.src = imgUrl || '/resources/images/profile/avatarM.png';

        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();

        document.getElementById('btnDownloadStory').onclick = function() {
            const link = document.createElement('a');
            link.download = `${fullName.replace(/\s+/g, '_')}_Heritage_Card.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();

            if (window.trackTreeAnalytics) {
                window.trackTreeAnalytics('heritage_story_exported', null, {
                    relative_name: fullName,
                    relation: relation
                });
            }
        };
    };

    /* =======================================================
       CLIPBOARD INVITATION HELPER
       ======================================================= */
    window.copyInviteToClipboard = function(link, btn) {
        if (!navigator.clipboard) {
            prompt('Copy your family invite link:', link);
            return;
        }
        navigator.clipboard.writeText(link).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.replace('btn-secondary', 'btn-success');

            if (window.trackTreeAnalytics) {
                window.trackTreeAnalytics('tree_invite_claim', null, { method: 'clipboard' });
            }

            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.replace('btn-success', 'btn-secondary');
            }, 2000);
        });
    };

    // Modal Logic
    let currentBaseNodeId = null;
    let currentBaseNodeName = '';

    document.getElementById('openAddRelativeModalBtn')?.addEventListener('click', function() {
        const pm = document.getElementById('personModal');
        if (pm) pm.style.display = 'none';
        openInviteRelativeFromNode();
    });

    function openAddRelativeModalFromBanner(relationType) {
        openInviteFamilyModal(null, relationType);
    }


    // To track active node clicks since showModal is compiled
    document.addEventListener('click', function(e) {
        const node = e.target.closest('.tree-node');
        if (node) {
            document.querySelectorAll('.tree-node').forEach(n => n.classList.remove('active-clicked'));
            node.classList.add('active-clicked');
        }
    });

    let currentWizardRelativeType = 'partner';

    window.syncWizardLineageScope = function(scopeVal) {
        const isMaternal = (scopeVal === 'maternal');
        const primaryCode = window.__PRIMARY_FAMILY_CODE__ || window.__FAMILY_CODE__ || '';
        const birthCode = window.__BIRTH_FAMILY_CODE__ || '';
        const activeCode = (isMaternal && birthCode) ? birthCode : primaryCode;
        const activeSurname = isMaternal
            ? (window.__MAIDEN_SURNAME__ || birthCode || '')
            : (window.__MARITAL_SURNAME__ || primaryCode || '');

        document.querySelectorAll('input[name="wizard_lineage_scope_radio"]').forEach(r => {
            r.checked = (r.value === (isMaternal ? 'maternal' : 'primary'));
        });
        document.querySelectorAll('.wizard-lineage-scope-input').forEach(inp => {
            inp.value = isMaternal ? 'maternal' : 'primary';
        });
        document.querySelectorAll('.wizard-lineage-code-input').forEach(inp => {
            inp.value = activeCode;
        });

        const badgeEl = document.getElementById('wizardActiveCodeBadge');
        if (badgeEl && activeCode) {
            badgeEl.textContent = '#' + activeCode;
        }

        const isMaleOrUnmarried = (window.__EGO_GENDER__ === 'male' || !window.__HAS_BIRTH_FAMILY__);
        const primaryCodeLabel = isMaleOrUnmarried ? 'Birth / Paternal Tree' : 'Marital / Household Tree';

        const helpEl = document.getElementById('wizardLineageScopeHelp');
        if (helpEl) {
            helpEl.innerHTML = isMaternal
                ? `Adding to your <strong>Birth / Maiden Family (#${activeCode})</strong> so your parents and birth siblings connect directly to your ${activeSurname ? activeSurname + ' ' : ''}birth lineage.`
                : `Adding to your <strong>${primaryCodeLabel} (#${activeCode})</strong>. Switch to Birth / Maiden Family when adding your own parents or birth siblings.`;
        }

        const parentsHint = document.getElementById('parentsLineageHint');
        if (parentsHint) {
            parentsHint.innerHTML = isMaternal
                ? `<i class="bi bi-diagram-3-fill me-1"></i> Saving parents to your <strong>Birth / Maiden Family (#${activeCode})</strong>. Provide details for one or both parents.`
                : `<i class="bi bi-house-heart-fill me-1"></i> Saving parents to your <strong>${primaryCodeLabel} (#${activeCode})</strong>. Provide details for one or both parents.`;
        }

        // Update surname placeholders & button labels to reflect Marital vs Birth family context
        const fatherLastInput = document.querySelector('#addParentsForm input[name="father_last_name"]');
        const motherLastInput = document.querySelector('#addParentsForm input[name="mother_last_name"]');
        const childLastInput = document.querySelector('#addChildForm input[name="last_name"]');
        if (activeSurname) {
            if (fatherLastInput) fatherLastInput.placeholder = activeSurname;
            if (motherLastInput) motherLastInput.placeholder = activeSurname;
            if (childLastInput) childLastInput.placeholder = activeSurname;
        }

        const parentsBtn = document.getElementById('submitParentsBtn');
        if (parentsBtn && activeCode) {
            parentsBtn.textContent = isMaternal
                ? `Save Parents to Birth Family (#${activeCode})`
                : `Save Parents to Marital Tree (#${activeCode})`;
        }

        const childBtn = document.getElementById('submitChildBtn');
        if (childBtn && activeCode) {
            const isSiblingMode = (currentWizardRelativeType === 'sibling');
            const noun = isSiblingMode ? 'Sibling' : 'Child';
            childBtn.textContent = isMaternal
                ? `Save ${noun} to Birth Family (#${activeCode})`
                : `Save ${noun} to Marital Tree (#${activeCode})`;
        }

        const partnerBtn = document.getElementById('submitPartnerBtn');
        if (partnerBtn && activeCode) {
            partnerBtn.textContent = isMaternal
                ? `Save Partner (#${activeCode})`
                : `Save Partner (#${activeCode})`;
        }
    };

    function selectRelativeType(type) {
        currentWizardRelativeType = type;
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('addRelativeError').classList.add('d-none');
        document.getElementById('parentsBaseNodeId').value = currentBaseNodeId;

        // Automatically select the most appropriate family code (Birth vs Marital) when user has both codes
        if (window.__HAS_BIRTH_FAMILY__ && window.__BIRTH_FAMILY_CODE__) {
            const isSelfNode = !currentBaseNodeName || currentBaseNodeName === 'Yourself' || String(currentBaseNodeId) === String(getGraphNodeId(window.__ROOT_USER_ID__, ''));
            if (isSelfNode && (type === 'parents' || type === 'sibling')) {
                window.syncWizardLineageScope(window.__ACTIVE_LINEAGE_SCOPE__ === 'maternal' ? 'maternal' : 'primary');
            } else if (type === 'partner' || type === 'child') {
                window.syncWizardLineageScope(window.__ACTIVE_LINEAGE_SCOPE__ === 'maternal' ? 'maternal' : 'primary');
            }
        }
        
        if (type === 'partner') {
            document.getElementById('step2-partner').classList.remove('d-none');
        } else if (type === 'child') {
            setChildFormMode('child');
            document.getElementById('step2-child').classList.remove('d-none');
            fetchUnionsForNode(currentBaseNodeId);
        } else if (type === 'sibling') {
            setChildFormMode('sibling');
            document.getElementById('step2-child').classList.remove('d-none'); // Reuses the child form structurally
            fetchParentUnionsForNode(currentBaseNodeId);
        } else if (type === 'parents') {
            document.getElementById('step2-parents').classList.remove('d-none');
        } else if (type === 'uncle_aunt') {
            document.getElementById('step2-uncle-aunt')?.classList.remove('d-none');
            const uInp = document.getElementById('uncleAuntBaseNodeId');
            if (uInp) uInp.value = currentBaseNodeId;
            window.refreshUncleAuntSubmitLabel && window.refreshUncleAuntSubmitLabel();
        } else if (type === 'in_law') {
            document.getElementById('step2-in-law')?.classList.remove('d-none');
            const inlawInp = document.getElementById('inLawBaseNodeId');
            if (inlawInp) inlawInp.value = currentBaseNodeId;
            window.refreshInLawSubmitLabel && window.refreshInLawSubmitLabel();
        }
    }

    // The child form is reused for siblings; swap the copy so it reads correctly for each.
    function setChildFormMode(mode) {
        const sibling = mode === 'sibling';
        const hint = document.getElementById('childUnionHint');
        const label = document.getElementById('childUnionLabel');
        const save = document.getElementById('submitChildBtn');
        const isMaternal = document.querySelector('input[name="wizard_lineage_scope_radio"]:checked')?.value === 'maternal';
        const activeCode = isMaternal ? (window.__BIRTH_FAMILY_CODE__ || window.__FAMILY_CODE__ || '') : (window.__PRIMARY_FAMILY_CODE__ || window.__FAMILY_CODE__ || '');
        const treeLabel = isMaternal ? `Birth / Maiden Family (#${activeCode})` : `Marital / Household Tree (#${activeCode})`;

        if (hint) hint.innerHTML = sibling
            ? `Pick the parents you share with this sibling in your <strong>${treeLabel}</strong>. They will be added as your brother or sister.`
            : `To add a child to your <strong>${treeLabel}</strong>, select which partnership/union they belong to.`;
        if (label) label.innerHTML = (sibling ? 'Shared Parents' : 'Select Union') + ' <span class="text-danger">*</span>';
        if (save) {
            const noun = sibling ? 'Sibling' : 'Child';
            save.textContent = activeCode
                ? (isMaternal ? `Save ${noun} to Birth Family (#${activeCode})` : `Save ${noun} to Marital Tree (#${activeCode})`)
                : `Save ${noun}`;
        }
    }

    function backToStep1() {
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
        document.getElementById('step2-parents').classList.add('d-none');
        document.getElementById('step2-uncle-aunt')?.classList.add('d-none');
        document.getElementById('step2-in-law')?.classList.add('d-none');
        document.getElementById('step1').classList.remove('d-none');
    }

    function toggleDivorceYear() {
        const status = document.getElementById('partnerStatus').value;
        const wrapper = document.getElementById('divorceYearWrapper');
        if (status === 'no') {
            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
        }
    }

    function fetchUnionsForNode(nodeId) {
        const select = document.getElementById('childUnionSelect');
        select.innerHTML = '<option value="">Loading unions...</option>';
        
        fetch(`/member/organogram/node/${nodeId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.message && data.message.unions) {
                    const unions = data.message.unions;
                    if (unions.length === 0) {
                        select.innerHTML = '<option value="">No partners found. Add a partner first.</option>';
                        document.getElementById('submitChildBtn').disabled = true;
                    } else {
                        select.innerHTML = unions.map(u => `<option value="${u.union_id}">With ${u.partner_name} (${u.is_current ? 'Current' : 'Past'})</option>`).join('');
                        document.getElementById('submitChildBtn').disabled = false;
                    }
                } else {
                    select.innerHTML = '<option value="">Failed to load unions or no unions found.</option>';
                    document.getElementById('submitChildBtn').disabled = true;
                }
            })
            .catch(err => {
                select.innerHTML = '<option value="">Error loading unions.</option>';
                document.getElementById('submitChildBtn').disabled = true;
            });
    }

    function fetchParentUnionsForNode(nodeId) {
        const select = document.getElementById('childUnionSelect');
        select.innerHTML = '<option value="">Loading parent unions...</option>';
        
        fetch(`/member/organogram/node/${nodeId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.message && data.message.parent_unions) {
                    const parentUnions = data.message.parent_unions;
                    if (parentUnions.length === 0) {
                        select.innerHTML = '<option value="">No parents found. Please add parents first.</option>';
                        document.getElementById('submitChildBtn').disabled = true;
                    } else {
                        select.innerHTML = parentUnions.map(u => `<option value="${u.union_id}">${u.label}</option>`).join('');
                        document.getElementById('submitChildBtn').disabled = false;
                    }
                } else {
                    select.innerHTML = '<option value="">Failed to load parent unions.</option>';
                    document.getElementById('submitChildBtn').disabled = true;
                }
            })
            .catch(err => {
                select.innerHTML = '<option value="">Error loading parent unions.</option>';
                document.getElementById('submitChildBtn').disabled = true;
            });
    }

    // Form Submissions (for backwards compatibility if invoked)
    document.getElementById('addPartnerForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/partner', 'submitPartnerBtn', 'Saving...');
    });

    document.getElementById('addChildForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/child', 'submitChildBtn', 'Saving...');
    });
    
    document.getElementById('addParentsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/parents', 'submitParentsBtn', 'Saving...');
    });

    document.getElementById('addUncleAuntForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/uncle-aunt', 'submitUncleAuntBtn', 'Saving...');
    });

    document.getElementById('addInLawForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/in-law', 'submitInLawBtn', 'Saving In-Law...');
    });

    function extractStudioErrorMessage(err) {
        if (typeof err === 'string' && err.trim() !== '') return err;
        if (err && typeof err === 'object') {
            if (typeof err.message === 'string') return err.message;
            if (typeof err.error === 'string') return err.error;
            const values = Object.values(err);
            if (values.length > 0 && typeof values[0] === 'string') return values[0];
            try { return JSON.stringify(err); } catch (_) {}
        }
        return 'An error occurred. Please try again.';
    }

    function submitForm(form, url, btnId, loadingText) {
        const btn = document.getElementById(btnId);
        if (!btn.dataset.stCleanHtml && !/spinner-border|st-spinner-border/i.test(btn.innerHTML)) {
            btn.dataset.stCleanHtml = btn.innerHTML;
        }
        const originalText = btn.dataset.stCleanHtml || 'Save';
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`;
        btn.disabled = true;
        
        const errorDiv = document.getElementById('addRelativeError');
        errorDiv.classList.add('d-none');

        const formData = new FormData(form);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            const isOk = (data.ok === true || data.status === 200 || data.status === 'success' || data.statusCode === 200);
            if (isOk) {
                // Determine target family code and lineage
                const targetCode = data.family_code || (data.message && data.message.family_code) || formData.get('lineage_code') || formData.get('wife_famcode') || formData.get('birth_famcode') || '';
                const targetScope = data.lineage_scope || (data.message && data.message.lineage_scope) || formData.get('lineage_scope') || '';
                
                // Get current view mode (canvas, lineage, etc.)
                const urlParams = new URLSearchParams(window.location.search);
                const currentMode = urlParams.get('mode') || 'canvas';
                
                let redirectUrl = window.location.pathname + '?mode=' + encodeURIComponent(currentMode);
                if (targetCode) {
                    redirectUrl += '&family_code=' + encodeURIComponent(targetCode);
                }
                if (targetScope) {
                    redirectUrl += '&lineage=' + encodeURIComponent(targetScope);
                }
                
                const successMsg = (data.message && typeof data.message === 'string' ? data.message : (data.message?.message || 'Relative added successfully!')) + (targetCode ? ` in #${targetCode}` : '');
                try {
                    sessionStorage.setItem('organogram_flash_notice', successMsg);
                } catch (_) {}
                
                window.location.href = redirectUrl;
            } else {
                errorDiv.textContent = window.extractErrorMessage ? window.extractErrorMessage(data) : extractStudioErrorMessage(data.message || data.error || data);
                errorDiv.classList.remove('d-none');
                if (window.FamilyPlatformSpinner?.stop) {
                    window.FamilyPlatformSpinner.stop(btn);
                }
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            errorDiv.textContent = window.extractErrorMessage ? window.extractErrorMessage(err, 'A network error occurred. Please try again.') : 'A network error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
            if (window.FamilyPlatformSpinner?.stop) {
                window.FamilyPlatformSpinner.stop(btn);
            }
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Toast flash message on successful relative add
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const flashNotice = sessionStorage.getItem('organogram_flash_notice');
            if (flashNotice) {
                sessionStorage.removeItem('organogram_flash_notice');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: flashNotice,
                        showConfirmButton: false,
                        timer: 4500,
                        timerProgressBar: true
                    });
                }
            }
        } catch (_) {}
    });

    // Native Draggable Mobile Bottom Sheet Touch Physics
    document.addEventListener('DOMContentLoaded', function() {
        const modalContent = document.getElementById('personModalContent');
        const handle = document.getElementById('bottomSheetHandle');
        const personModal = document.getElementById('personModal');

        if (modalContent && handle && personModal) {
            let startY = 0;
            let currentY = 0;
            let isDragging = false;

            handle.addEventListener('touchstart', function(e) {
                if (window.innerWidth > 768) return;
                startY = e.touches[0].clientY;
                isDragging = true;
                modalContent.style.transition = 'none';
            }, { passive: true });

            handle.addEventListener('touchmove', function(e) {
                if (!isDragging || window.innerWidth > 768) return;
                currentY = e.touches[0].clientY;
                const diff = currentY - startY;
                if (diff > 0) {
                    modalContent.style.transform = `translateY(${diff}px)`;
                }
            }, { passive: true });

            handle.addEventListener('touchend', function() {
                if (!isDragging || window.innerWidth > 768) return;
                isDragging = false;
                modalContent.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
                const diff = currentY - startY;
                if (diff > 120) {
                    personModal.style.display = 'none';
                    modalContent.style.transform = '';
                } else {
                    modalContent.style.transform = 'translateY(0)';
                }
            }, { passive: true });

            // Backdrop click dismiss
            personModal.addEventListener('click', function(e) {
                if (e.target === personModal) {
                    personModal.style.display = 'none';
                    modalContent.style.transform = '';
                }
            });
        }
    });

    /* =======================================================
       RADIAL 360° AHNENTAFEL FAN CHART ENGINE
       ======================================================= */
    window.fanChartState = {
        spanAngle: 360,
        zoom: 1.0,
        panX: 0,
        panY: 0,
        isPanning: false,
        startX: 0,
        startY: 0
    };

    window.setFanChartSpan = function(angle) {
        window.fanChartState.spanAngle = angle;
        const btn360 = document.getElementById('btnFanSpan360');
        const btn180 = document.getElementById('btnFanSpan180');
        if (angle === 360) {
            btn360?.classList.add('active');
            btn180?.classList.remove('active');
        } else {
            btn180?.classList.add('active');
            btn360?.classList.remove('active');
        }
        window.renderRadialFanChart();
    };

    window.resetFanChartZoom = function() {
        window.fanChartState.zoom = 1.0;
        window.fanChartState.panX = 0;
        window.fanChartState.panY = 0;
        window.renderRadialFanChart();
    };

    window.renderRadialFanChart = function() {
        const container = document.getElementById('fanChartSvgContainer');
        if (!container) return;

        const ahnentafel = (window.graphData && window.graphData.ahnentafel) ? window.graphData.ahnentafel : null;
        if (!ahnentafel) {
            container.innerHTML = '<div class="text-white-50 p-4 text-center">Radial fan chart requires ancestral pedigree links. Build lineage in Panoramic Dynasty first.</div>';
            return;
        }

        const width = container.clientWidth || 800;
        const height = container.clientHeight || 600;
        const cx = width / 2;
        const cy = height / 2;

        const spanAngle = window.fanChartState.spanAngle || 360;
        const isSemiCircle = (spanAngle === 180);

        // Radii configuration for 4 generations:
        // Gen 0 (Ego): Center circle
        // Gen 1 (Parents): Ring 1
        // Gen 2 (Grandparents): Ring 2
        // Gen 3 (Great-Grandparents): Ring 3
        const r0 = 55;
        const ringStep = 75;
        const maxR = r0 + ringStep * 3;

        const colors = {
            ego: '#f59e0b',
            paternal_gf: '#a31621',
            paternal_gm: '#ef6b73',
            maternal_gf: '#ec4899',
            maternal_gm: '#f472b6'
        };

        const polarToCart = (r, deg) => {
            const rad = (deg - 90) * Math.PI / 180.0;
            return {
                x: cx + (r * Math.cos(rad)),
                y: cy + (r * Math.sin(rad))
            };
        };

        const makeArcD = (innerR, outerR, startAngle, endAngle) => {
            const startOuter = polarToCart(outerR, startAngle);
            const endOuter = polarToCart(outerR, endAngle);
            const startInner = polarToCart(innerR, endAngle);
            const endInner = polarToCart(innerR, startAngle);

            const arcSweep = (endAngle - startAngle <= 180) ? 0 : 1;

            return [
                `M ${startOuter.x} ${startOuter.y}`,
                `A ${outerR} ${outerR} 0 ${arcSweep} 1 ${endOuter.x} ${endOuter.y}`,
                `L ${startInner.x} ${startInner.y}`,
                `A ${innerR} ${innerR} 0 ${arcSweep} 0 ${endInner.x} ${endInner.y}`,
                'Z'
            ].join(' ');
        };

        let svgHtml = `<svg id="fanChartSvg" width="100%" height="100%" viewBox="0 0 ${width} ${height}" style="overflow: visible; user-select: none;">`;
        svgHtml += `<g transform="translate(${window.fanChartState.panX}, ${window.fanChartState.panY}) scale(${window.fanChartState.zoom})" style="transform-origin: ${cx}px ${cy}px; transition: transform 0.15s ease-out;">`;

        // Render generation wedges:
        // Generation 0: Ego (Center Circle)
        const egoSlot = ahnentafel[1] || null;
        const egoNode = egoSlot ? egoSlot.node : null;
        const egoName = egoNode ? (egoNode.full_name || egoNode.first_name || 'You') : 'You';
        const egoColor = colors.ego;

        svgHtml += `
            <g class="fan-wedge ego-wedge" style="cursor: pointer;" onclick="onFanWedgeClick('1')">
                <circle cx="${cx}" cy="${cy}" r="${r0}" fill="${egoColor}" stroke="#ffffff" stroke-width="2.5" opacity="0.95" filter="drop-shadow(0px 4px 10px rgba(0,0,0,0.5))"/>
                <text x="${cx}" y="${cy - 8}" text-anchor="middle" fill="#0f172a" font-weight="800" font-size="12" font-family="Inter, sans-serif">${egoName.split(' ')[0]}</text>
                <text x="${cx}" y="${cy + 8}" text-anchor="middle" fill="#0f172a" font-weight="700" font-size="10" font-family="Inter, sans-serif">${egoName.split(' ').slice(1).join(' ')}</text>
                <text x="${cx}" y="${cy + 22}" text-anchor="middle" fill="#1e293b" font-weight="600" font-size="8.5" font-family="Inter, sans-serif">Focal Person</text>
            </g>
        `;

        // Generations 1, 2, 3
        const baseAngle = isSemiCircle ? -90 : 0;
        const totalSpan = isSemiCircle ? 180 : 360;

        for (let gen = 1; gen <= 3; gen++) {
            const slotsInGen = Math.pow(2, gen);
            const startSlot = Math.pow(2, gen);
            const endSlot = startSlot + slotsInGen - 1;
            const wedgeSpan = totalSpan / slotsInGen;

            const innerR = r0 + (gen - 1) * ringStep;
            const outerR = innerR + ringStep;

            for (let slot = startSlot; slot <= endSlot; slot++) {
                const slotIdx = slot - startSlot;
                const startDeg = baseAngle + slotIdx * wedgeSpan;
                const endDeg = startDeg + wedgeSpan;
                const midDeg = (startDeg + endDeg) / 2;

                const slotData = ahnentafel[slot] || null;
                const node = slotData ? slotData.node : null;
                const quad = slotData ? slotData.quadrant : 'paternal_gf';
                const fillColor = colors[quad] || '#64748b';
                const opacity = node ? '0.9' : '0.25';

                const pathD = makeArcD(innerR, outerR, startDeg, endDeg);
                const textPos = polarToCart((innerR + outerR) / 2, midDeg);

                const displayName = node ? (node.full_name || node.first_name || 'Relative') : 'Empty';
                const parts = displayName.split(' ');
                const l1 = parts[0] || '';
                const l2 = parts.slice(1).join(' ') || '';

                // Rotated text for radial legibility
                let rotAngle = midDeg;
                if (rotAngle > 90 && rotAngle < 270) {
                    rotAngle += 180;
                }

                const isDeceased = Boolean(node && (node.is_deceased || node.isDeceased));
                svgHtml += `
                    <g class="fan-wedge" style="cursor: pointer;" onclick="onFanWedgeClick('${slot}')">
                        <path d="${pathD}" fill="${fillColor}" fill-opacity="${opacity}" stroke="${isDeceased ? '#cbd5e1' : '#0f172a'}" stroke-width="${isDeceased ? '2.5' : '2'}" class="fan-arc-path" />
                        <g transform="translate(${textPos.x}, ${textPos.y}) rotate(${rotAngle})">
                            <text x="0" y="-3" text-anchor="middle" fill="#ffffff" font-weight="700" font-size="${gen === 3 ? '8.5' : '10.5'}" font-family="Inter, sans-serif">${l1}${isDeceased ? ' 🕊️' : ''}</text>
                            ${l2 ? `<text x="0" y="8" text-anchor="middle" fill="#e2e8f0" font-weight="600" font-size="${gen === 3 ? '7.5' : '9'}" font-family="Inter, sans-serif">${l2}</text>` : ''}
                        </g>
                    </g>
                `;
            }
        }

        svgHtml += `</g></svg>`;
        container.innerHTML = svgHtml;

        // Attach smooth zoom & pan drag listeners
        const svgEl = document.getElementById('fanChartSvg');
        if (svgEl) {
            svgEl.onwheel = function(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 0.9 : 1.1;
                window.fanChartState.zoom = Math.max(0.4, Math.min(3.0, window.fanChartState.zoom * delta));
                window.renderRadialFanChart();
            };

            svgEl.onmousedown = function(e) {
                window.fanChartState.isPanning = true;
                window.fanChartState.startX = e.clientX - window.fanChartState.panX;
                window.fanChartState.startY = e.clientY - window.fanChartState.panY;
                svgEl.style.cursor = 'grabbing';
            };

            window.onmousemove = function(e) {
                if (!window.fanChartState.isPanning) return;
                window.fanChartState.panX = e.clientX - window.fanChartState.startX;
                window.fanChartState.panY = e.clientY - window.fanChartState.startY;
                const group = svgEl.querySelector('g');
                if (group) {
                    group.setAttribute('transform', `translate(${window.fanChartState.panX}, ${window.fanChartState.panY}) scale(${window.fanChartState.zoom})`);
                }
            };

            window.onmouseup = function() {
                if (window.fanChartState.isPanning) {
                    window.fanChartState.isPanning = false;
                    if (svgEl) svgEl.style.cursor = 'grab';
                }
            };
        }
    };

    window.onFanWedgeClick = function(slotNumber) {
        const ahnentafel = (window.graphData && window.graphData.ahnentafel) ? window.graphData.ahnentafel : null;
        if (!ahnentafel) return;

        const slot = ahnentafel[slotNumber] || null;
        if (!slot) return;

        if (slot.node) {
            const rawNode = slot.node;
            const treeNode = (window.familyTreeNodes || []).find(n => String(n.id) === String(rawNode.id));
            if (typeof window.openPersonCard === 'function') {
                window.openPersonCard(treeNode || rawNode);
            }
        } else {
            // Empty ancestral slot -> prompt to add ancestor
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Empty Ancestral Slot',
                    text: `Ahnentafel slot #${slotNumber} is currently vacant. Switch to Panoramic Dynasty to add your parent or ancestor.`,
                    icon: 'info',
                    confirmButtonText: 'Switch to Tree'
                }).then((r) => {
                    if (r.isConfirmed) {
                        window.switchTreeViewMode('canvas');
                    }
                });
            }
        }
    };

    /* =======================================================
       LIVING VOICE CAPSULE RECORDING & UPLOAD HANDLERS
       ======================================================= */
    window.voiceRecorderState = {
        mediaRecorder: null,
        audioChunks: [],
        recordedBlob: null,
        stream: null,
        timerInterval: null,
        recordSeconds: 0,
        targetNodeId: null
    };

    window.openVoiceCapsuleModal = function(nodeId) {
        if (!nodeId) return;
        window.voiceRecorderState.targetNodeId = String(nodeId);

        const node = (window.familyTreeNodes || []).find(n => String(n.id) === String(nodeId));
        const nameEl = document.getElementById('voiceCapsuleTargetName');
        if (nameEl) {
            nameEl.textContent = `Preserving voice for: ${node ? node.name : 'Family Relative'}`;
        }

        // Reset recorder UI
        const btnStart = document.getElementById('btnStartRecord');
        const btnStop = document.getElementById('btnStopRecord');
        const btnSave = document.getElementById('btnSaveRecord');
        const timerEl = document.getElementById('recordTimer');
        const previewSec = document.getElementById('recordedPreviewSection');
        const alertEl = document.getElementById('voiceCapsuleAlert');

        if (btnStart) btnStart.classList.remove('d-none');
        if (btnStop) btnStop.classList.add('d-none');
        if (btnSave) btnSave.classList.add('d-none');
        if (timerEl) timerEl.textContent = '00:00';
        if (previewSec) previewSec.classList.add('d-none');
        if (alertEl) alertEl.classList.add('d-none');

        const modalEl = document.getElementById('voiceCapsuleModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    };

    window.startVoiceRecording = function() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Audio recording is not supported in this browser. Please upload an audio file instead.');
            return;
        }

        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                window.voiceRecorderState.stream = stream;
                window.voiceRecorderState.audioChunks = [];
                window.voiceRecorderState.recordedBlob = null;
                window.voiceRecorderState.recordSeconds = 0;

                const mediaRecorder = new MediaRecorder(stream);
                window.voiceRecorderState.mediaRecorder = mediaRecorder;

                mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) {
                        window.voiceRecorderState.audioChunks.push(e.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(window.voiceRecorderState.audioChunks, { type: 'audio/webm' });
                    window.voiceRecorderState.recordedBlob = audioBlob;

                    const audioUrl = URL.createObjectURL(audioBlob);
                    const previewAudio = document.getElementById('recordedAudioPreview');
                    const previewSec = document.getElementById('recordedPreviewSection');
                    if (previewAudio) previewAudio.src = audioUrl;
                    if (previewSec) previewSec.classList.remove('d-none');

                    document.getElementById('btnSaveRecord')?.classList.remove('d-none');
                    document.getElementById('btnStartRecord')?.classList.remove('d-none');
                };

                mediaRecorder.start();

                // UI Pulsing feedback
                const pulse = document.getElementById('micRecordPulse');
                if (pulse) pulse.style.borderColor = '#ef4444';

                document.getElementById('btnStartRecord')?.classList.add('d-none');
                document.getElementById('btnStopRecord')?.classList.remove('d-none');
                document.getElementById('btnSaveRecord')?.classList.add('d-none');

                window.voiceRecorderState.timerInterval = setInterval(() => {
                    window.voiceRecorderState.recordSeconds++;
                    const m = String(Math.floor(window.voiceRecorderState.recordSeconds / 60)).padStart(2, '0');
                    const s = String(window.voiceRecorderState.recordSeconds % 60).padStart(2, '0');
                    const timerEl = document.getElementById('recordTimer');
                    if (timerEl) timerEl.textContent = `${m}:${s}`;
                }, 1000);
            })
            .catch(err => {
                console.error('Microphone access denied:', err);
                alert('Microphone access was denied. Please allow microphone access or upload an audio file.');
            });
    };

    window.stopVoiceRecording = function() {
        if (window.voiceRecorderState.mediaRecorder && window.voiceRecorderState.mediaRecorder.state !== 'inactive') {
            window.voiceRecorderState.mediaRecorder.stop();
        }
        if (window.voiceRecorderState.stream) {
            window.voiceRecorderState.stream.getTracks().forEach(t => t.stop());
        }
        if (window.voiceRecorderState.timerInterval) {
            clearInterval(window.voiceRecorderState.timerInterval);
        }

        document.getElementById('btnStopRecord')?.classList.add('d-none');
        const pulse = document.getElementById('micRecordPulse');
        if (pulse) pulse.style.borderColor = 'rgba(239, 68, 68, 0.5)';
    };

    window.saveRecordedVoiceCapsule = function() {
        if (!window.voiceRecorderState.recordedBlob) return;
        const nodeId = window.voiceRecorderState.targetNodeId;
        if (!nodeId) return;

        const btnSave = document.getElementById('btnSaveRecord');
        const origText = btnSave ? btnSave.innerHTML : 'Save';
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        }

        const formData = new FormData();
        formData.append('node_id', nodeId);
        formData.append('audio', window.voiceRecorderState.recordedBlob, `voice_${nodeId}.webm`);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        formData.append('token', csrfToken);

        fetch('/member/organogram/node/voice-capsule', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.ok) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Living Voice Capsule Saved!',
                        text: 'Voice capsule attached successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    alert('Living Voice Capsule Saved!');
                    window.location.reload();
                }
            } else {
                const alertEl = document.getElementById('voiceCapsuleAlert');
                if (alertEl) {
                    alertEl.className = 'alert alert-danger mt-3 mb-0 small';
                    alertEl.textContent = data.message || 'Failed to save voice capsule.';
                    alertEl.classList.remove('d-none');
                }
                if (btnSave) {
                    btnSave.disabled = false;
                    btnSave.innerHTML = origText;
                }
            }
        })
        .catch(err => {
            const alertEl = document.getElementById('voiceCapsuleAlert');
            if (alertEl) {
                alertEl.className = 'alert alert-danger mt-3 mb-0 small';
                alertEl.textContent = 'Network error while saving voice capsule.';
                alertEl.classList.remove('d-none');
            }
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origText;
            }
        });
    };

    window.handleVoiceFileUpload = function(e) {
        e.preventDefault();
        const fileInput = document.getElementById('voiceAudioFile');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;

        const nodeId = window.voiceRecorderState.targetNodeId;
        if (!nodeId) return;

        const btnUpload = document.getElementById('btnUploadVoiceFile');
        const origText = btnUpload ? btnUpload.innerHTML : 'Upload';
        if (btnUpload) {
            btnUpload.disabled = true;
            btnUpload.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
        }

        const formData = new FormData();
        formData.append('node_id', nodeId);
        formData.append('audio', fileInput.files[0]);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        formData.append('token', csrfToken);

        fetch('/member/organogram/node/voice-capsule', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.ok) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Voice Capsule Uploaded!',
                        text: 'Audio file attached to family member.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                } else {
                    alert('Voice Capsule Uploaded!');
                    window.location.reload();
                }
            } else {
                const alertEl = document.getElementById('voiceCapsuleAlert');
                if (alertEl) {
                    alertEl.className = 'alert alert-danger mt-3 mb-0 small';
                    alertEl.textContent = data.message || 'Failed to upload voice capsule.';
                    alertEl.classList.remove('d-none');
                }
                if (btnUpload) {
                    btnUpload.disabled = false;
                    btnUpload.innerHTML = origText;
                }
            }
        })
        .catch(err => {
            const alertEl = document.getElementById('voiceCapsuleAlert');
            if (alertEl) {
                alertEl.className = 'alert alert-danger mt-3 mb-0 small';
                alertEl.textContent = 'Network error during upload.';
                alertEl.classList.remove('d-none');
            }
            if (btnUpload) {
                btnUpload.disabled = false;
                btnUpload.innerHTML = origText;
            }
        });
    };

    window.deleteVoiceCapsule = function(nodeId) {
        if (!nodeId) return;

        const proceed = () => {
            const formData = new FormData();
            formData.append('node_id', nodeId);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            formData.append('token', csrfToken);

            fetch('/member/organogram/node/delete-voice-capsule', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' || data.ok) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete voice capsule.');
                }
            })
            .catch(() => alert('Network error while deleting voice capsule.'));
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Voice Capsule?',
                text: 'This will remove the recorded audio memories for this member.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete audio'
            }).then(r => {
                if (r.isConfirmed) proceed();
            });
        } else if (confirm('Delete this voice capsule?')) {
            proceed();
        }
    };

    /* =======================================================
       GENERATIONAL TIME SLIDER CHRONOLOGICAL ENGINE
       ======================================================= */
    window.timeSliderInterval = null;
    window.isTimeSliderPlaying = false;

    window.eraDescriptions = {
        '-3': { label: 'GREAT-GRANDPARENTS', era: 'Ancestral Roots (c. 1900-1940)' },
        '-2': { label: 'GRANDPARENTS', era: 'Foundational Matriarchs & Patriarchs' },
        '-1': { label: 'PARENTS & UNCLES', era: 'Second Generation Pioneers' },
        '0':  { label: 'EGO & SIBLINGS', era: 'Living Core Generation' },
        '1':  { label: 'CHILDREN GENERATION', era: 'Flourishing Heirs' },
        '2':  { label: 'ALL GENERATIONS', era: 'Full Modern Living Dynasty' }
    };

    window.onTimeSliderChange = function(maxLevel) {
        maxLevel = Number(maxLevel);
        const info = window.eraDescriptions[String(maxLevel)] || { label: 'GENERATION ' + maxLevel, era: '' };

        const labelEl = document.getElementById('timeSliderLabel');
        const eraEl = document.getElementById('timeSliderEra');
        if (labelEl) labelEl.textContent = info.label;
        if (eraEl) eraEl.textContent = info.era;

        const stage = document.getElementById('tree');
        if (!stage || !window.family) return;

        // Semantic time filtering: dynamically adjust opacity and scale of nodes based on generation
        const svgNodes = stage.querySelectorAll('.kinship-node');
        svgNodes.forEach(nodeG => {
            const nodeId = nodeG.getAttribute('data-id');
            const dataNode = (window.familyTreeNodes || []).find(n => String(n.id) === String(nodeId));
            if (!dataNode) return;

            const genLevel = (typeof dataNode.generation_level !== 'undefined') ? Number(dataNode.generation_level) : 0;
            if (genLevel <= maxLevel) {
                nodeG.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                nodeG.style.opacity = '1';
                nodeG.style.pointerEvents = 'auto';
            } else {
                nodeG.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                nodeG.style.opacity = '0.08';
                nodeG.style.pointerEvents = 'none';
            }
        });

        if (window.trackTreeAnalytics) {
            window.trackTreeAnalytics('time_slider_scrubbed', null, { maxLevel: maxLevel });
        }
    };

    window.toggleTimeSliderPlay = function() {
        const icon = document.getElementById('timeSliderPlayIcon');
        const slider = document.getElementById('dynastyTimeSlider');
        if (!slider) return;

        if (window.isTimeSliderPlaying) {
            // Stop playing
            clearInterval(window.timeSliderInterval);
            window.isTimeSliderPlaying = false;
            if (icon) {
                icon.className = 'bi bi-play-fill fs-5';
            }
        } else {
            // Start chronological playback from oldest to newest
            window.isTimeSliderPlaying = true;
            if (icon) {
                icon.className = 'bi bi-pause-fill fs-5';
            }

            let curr = Number(slider.value);
            if (curr >= 2) {
                curr = -3;
                slider.value = -3;
                window.onTimeSliderChange(-3);
            }

            window.timeSliderInterval = setInterval(() => {
                let val = Number(slider.value) + 1;
                if (val > 2) {
                    val = 2;
                    clearInterval(window.timeSliderInterval);
                    window.isTimeSliderPlaying = false;
                    if (icon) icon.className = 'bi bi-play-fill fs-5';
                }
                slider.value = val;
                window.onTimeSliderChange(val);
            }, 1800);
        }
    };

    window.resetTimeSlider = function() {
        if (window.isTimeSliderPlaying) {
            clearInterval(window.timeSliderInterval);
            window.isTimeSliderPlaying = false;
            const icon = document.getElementById('timeSliderPlayIcon');
            if (icon) icon.className = 'bi bi-play-fill fs-5';
        }
        const slider = document.getElementById('dynastyTimeSlider');
        if (slider) {
            slider.value = 2;
            window.onTimeSliderChange(2);
        }
    };

    /* =======================================================
       3D DYNASTY MIGRATION GLOBE ENGINE (SPATIAL LINEAGE)
       ======================================================= */
    window.globeState = {
        canvas: null,
        ctx: null,
        rotationX: 0.25,
        rotationY: -0.35,
        targetRotationX: 0.25,
        targetRotationY: -0.35,
        zoom: 1.0,
        isDragging: false,
        lastMouseX: 0,
        lastMouseY: 0,
        isAutoRotating: true,
        animationFrameId: null,
        hubs: [],
        arcs: []
    };

    // Standard Geo Coordinates for Major Diaspora & Ancestral Hubs
    window.cityCoordinates = {
        'lagos': { lat: 6.5244, lon: 3.3792, name: 'Lagos, Nigeria' },
        'nigeria': { lat: 9.0820, lon: 8.6753, name: 'Nigeria' },
        'ibadan': { lat: 7.3775, lon: 3.9470, name: 'Ibadan, Nigeria' },
        'abuja': { lat: 9.0765, lon: 7.3986, name: 'Abuja, Nigeria' },
        'london': { lat: 51.5074, lon: -0.1278, name: 'London, UK' },
        'uk': { lat: 55.3781, lon: -3.4360, name: 'United Kingdom' },
        'united kingdom': { lat: 55.3781, lon: -3.4360, name: 'United Kingdom' },
        'manchester': { lat: 53.4808, lon: -2.2426, name: 'Manchester, UK' },
        'new york': { lat: 40.7128, lon: -74.0060, name: 'New York, USA' },
        'usa': { lat: 37.0902, lon: -95.7129, name: 'United States' },
        'united states': { lat: 37.0902, lon: -95.7129, name: 'United States' },
        'atlanta': { lat: 33.7490, lon: -84.3880, name: 'Atlanta, USA' },
        'houston': { lat: 29.7604, lon: -95.3698, name: 'Houston, USA' },
        'toronto': { lat: 43.6532, lon: -79.3832, name: 'Toronto, Canada' },
        'canada': { lat: 56.1304, lon: -106.3468, name: 'Canada' },
        'dublin': { lat: 53.3498, lon: -6.2603, name: 'Dublin, Ireland' },
        'ireland': { lat: 53.1424, lon: -7.6921, name: 'Ireland' }
    };

    window.initDynastyGlobe = function() {
        const canvas = document.getElementById('dynastyGlobeCanvas');
        if (!canvas) return;
        window.globeState.canvas = canvas;
        window.globeState.ctx = canvas.getContext('2d');

        // Extract and cluster geographic nodes from graphData
        window.extractGlobeDynastyData();

        // Attach user mouse & touch interaction handlers
        window.setupGlobeInteractions();

        // Start 60fps render loop
        if (!window.globeState.animationFrameId) {
            window.renderGlobeFrame();
        }
    };

    window.extractGlobeDynastyData = function() {
        if (!window.graphData || !window.graphData.nodes) return;

        const hubMap = new Map();
        const rootNode = window.graphData.nodes.find(n => String(n.user_id) === String(window.__ROOT_USER_ID__)) || window.graphData.nodes[0];
        const defaultOrigin = { lat: 6.5244, lon: 3.3792, name: 'Lagos, Nigeria' };

        window.graphData.nodes.forEach(node => {
            const locStr = String(node.location || node.country_of_residence || node.birth_place || '').trim().toLowerCase();
            let geo = null;

            for (const key in window.cityCoordinates) {
                if (locStr.includes(key)) {
                    geo = window.cityCoordinates[key];
                    break;
                }
            }

            if (!geo) {
                // If unspecified or family origin, map to ancestral seat (Lagos, Nigeria)
                geo = defaultOrigin;
            }

            const hubKey = `${geo.lat.toFixed(2)},${geo.lon.toFixed(2)}`;
            if (!hubMap.has(hubKey)) {
                hubMap.set(hubKey, {
                    name: geo.name,
                    lat: geo.lat,
                    lon: geo.lon,
                    members: []
                });
            }
            hubMap.get(hubKey).members.push(node);
        });

        window.globeState.hubs = Array.from(hubMap.values());

        // Build migration trajectory arcs from ancestral origin (hub with most elders or default origin)
        const originHub = window.globeState.hubs.find(h => h.name.includes('Nigeria') || h.name.includes('Lagos')) || window.globeState.hubs[0];
        window.globeState.arcs = [];

        if (originHub) {
            window.globeState.hubs.forEach(hub => {
                if (hub !== originHub) {
                    window.globeState.arcs.push({
                        from: originHub,
                        to: hub,
                        progress: Math.random() // offset animated trajectory pulses
                    });
                }
            });
        }

        // Populate side list
        const listEl = document.getElementById('globeHubsList');
        const countBadge = document.getElementById('globeHubCount');
        if (countBadge) countBadge.textContent = `${window.globeState.hubs.length} Regions`;

        if (listEl) {
            listEl.innerHTML = window.globeState.hubs.map((hub, idx) => `
                <div class="p-2 rounded-3 d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); cursor: pointer;" onclick="focusGlobeOnHub(${hub.lat}, ${hub.lon})">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; background: rgba(163,22,33, 0.2); color: #ef6b73; font-size: 0.75rem;">
                            ${idx + 1}
                        </span>
                        <div>
                            <div class="text-white fw-bold" style="font-size: 0.82rem;">${hub.name}</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">${hub.members.length} Family Member${hub.members.length > 1 ? 's' : ''}</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                </div>
            `).join('');
        }
    };

    window.setupGlobeInteractions = function() {
        const canvas = window.globeState.canvas;
        if (!canvas) return;

        canvas.onmousedown = function(e) {
            window.globeState.isDragging = true;
            window.globeState.lastMouseX = e.clientX;
            window.globeState.lastMouseY = e.clientY;
            canvas.style.cursor = 'grabbing';
        };

        window.addEventListener('mousemove', function(e) {
            if (!window.globeState.isDragging) return;
            const dx = e.clientX - window.globeState.lastMouseX;
            const dy = e.clientY - window.globeState.lastMouseY;
            window.globeState.lastMouseX = e.clientX;
            window.globeState.lastMouseY = e.clientY;

            window.globeState.targetRotationY += dx * 0.006;
            window.globeState.targetRotationX += dy * 0.006;
            // Clamp pitch to prevent flipping
            window.globeState.targetRotationX = Math.max(-1.4, Math.min(1.4, window.globeState.targetRotationX));
        });

        window.addEventListener('mouseup', function() {
            if (window.globeState.isDragging) {
                window.globeState.isDragging = false;
                if (canvas) canvas.style.cursor = 'grab';
            }
        });

        canvas.onwheel = function(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.92 : 1.08;
            window.globeState.zoom = Math.max(0.6, Math.min(2.2, window.globeState.zoom * delta));
        };
    };

    window.toggleGlobeAutoRotation = function() {
        window.globeState.isAutoRotating = !window.globeState.isAutoRotating;
        const icon = document.getElementById('globeRotationIcon');
        const btn = document.getElementById('btnToggleGlobeRotation');
        if (btn) {
            btn.innerHTML = window.globeState.isAutoRotating 
                ? '<i class="bi bi-arrow-repeat me-1" id="globeRotationIcon"></i> Pause Rotation'
                : '<i class="bi bi-play-fill me-1" id="globeRotationIcon"></i> Resume Rotation';
        }
    };

    window.resetGlobeView = function() {
        window.globeState.targetRotationX = 0.25;
        window.globeState.targetRotationY = -0.35;
        window.globeState.zoom = 1.0;
    };

    window.focusGlobeOnHub = function(lat, lon) {
        window.globeState.targetRotationY = -(lon * Math.PI / 180) - (Math.PI / 2);
        window.globeState.targetRotationX = (lat * Math.PI / 180);
        window.globeState.zoom = 1.35;
    };

    window.renderGlobeFrame = function() {
        const state = window.globeState;
        const canvas = state.canvas;
        if (!canvas || !state.ctx) return;

        const ctx = state.ctx;
        const W = canvas.width;
        const H = canvas.height;
        const cx = W / 2;
        const cy = H / 2;
        const radius = 175 * state.zoom;

        // Auto-rotation when not dragging
        if (state.isAutoRotating && !state.isDragging) {
            state.targetRotationY += 0.0035;
        }

        // Damped interpolation for rotation
        state.rotationX += (state.targetRotationX - state.rotationX) * 0.12;
        state.rotationY += (state.targetRotationY - state.rotationY) * 0.12;

        ctx.clearRect(0, 0, W, H);

        // 1. Atmosphere Glow & Starfield
        const atmosGrad = ctx.createRadialGradient(cx, cy, radius * 0.8, cx, cy, radius * 1.25);
        atmosGrad.addColorStop(0, 'rgba(163,22,33, 0.25)');
        atmosGrad.addColorStop(0.7, 'rgba(42,10,13, 0.1)');
        atmosGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = atmosGrad;
        ctx.beginPath();
        ctx.arc(cx, cy, radius * 1.25, 0, Math.PI * 2);
        ctx.fill();

        // 2. Globe Sphere (Dark Oceanic Void)
        const oceanGrad = ctx.createRadialGradient(cx - radius * 0.3, cy - radius * 0.3, radius * 0.1, cx, cy, radius);
        oceanGrad.addColorStop(0, '#1e293b');
        oceanGrad.addColorStop(0.7, '#0f172a');
        oceanGrad.addColorStop(1, '#020617');
        ctx.fillStyle = oceanGrad;
        ctx.beginPath();
        ctx.arc(cx, cy, radius, 0, Math.PI * 2);
        ctx.fill();

        // 3. Spherical Projection Helper
        const project = (lat, lon) => {
            const phi = lat * Math.PI / 180;
            const theta = (lon * Math.PI / 180) + state.rotationY;

            // 3D coordinates on unit sphere
            const x = Math.cos(phi) * Math.sin(theta);
            const y = -Math.sin(phi);
            const z = Math.cos(phi) * Math.cos(theta);

            // Rotate around X axis (pitch)
            const cosX = Math.cos(state.rotationX);
            const sinX = Math.sin(state.rotationX);
            const rotY = y * cosX - z * sinX;
            const rotZ = y * sinX + z * cosX;

            return {
                x: cx + x * radius,
                y: cy + rotY * radius,
                z: rotZ,
                visible: rotZ > 0 // in front hemisphere
            };
        };

        // 4. Parallels & Meridians (Glowing Latitude/Longitude Wireframe)
        ctx.strokeStyle = 'rgba(148, 163, 184, 0.12)';
        ctx.lineWidth = 1;

        // Latitudes
        for (let lat = -60; lat <= 60; lat += 30) {
            ctx.beginPath();
            let first = true;
            for (let lon = -180; lon <= 180; lon += 10) {
                const p = project(lat, lon);
                if (p.visible) {
                    if (first) { ctx.moveTo(p.x, p.y); first = false; }
                    else { ctx.lineTo(p.x, p.y); }
                } else {
                    first = true;
                }
            }
            ctx.stroke();
        }

        // Longitudes
        for (let lon = -180; lon < 180; lon += 45) {
            ctx.beginPath();
            let first = true;
            for (let lat = -80; lat <= 80; lat += 10) {
                const p = project(lat, lon);
                if (p.visible) {
                    if (first) { ctx.moveTo(p.x, p.y); first = false; }
                    else { ctx.lineTo(p.x, p.y); }
                } else {
                    first = true;
                }
            }
            ctx.stroke();
        }

        // 5. Animated Golden Migration Arcs (Bezier curves lofting in 3D)
        state.arcs.forEach(arc => {
            const pFrom = project(arc.from.lat, arc.from.lon);
            const pTo = project(arc.to.lat, arc.to.lon);

            if (pFrom.visible || pTo.visible) {
                // Arc control apex lofted outwards
                const midLat = (arc.from.lat + arc.to.lat) / 2;
                const midLon = (arc.from.lon + arc.to.lon) / 2;
                const pMid = project(midLat, midLon);
                const loft = 35 * state.zoom;
                const cpX = (pFrom.x + pTo.x) / 2 + (pMid.x - cx) * 0.25;
                const cpY = (pFrom.y + pTo.y) / 2 - loft;

                ctx.strokeStyle = 'rgba(245, 158, 11, 0.4)';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(pFrom.x, pFrom.y);
                ctx.quadraticCurveTo(cpX, cpY, pTo.x, pTo.y);
                ctx.stroke();

                // Trajectory Pulse Particle
                arc.progress = (arc.progress + 0.012) % 1.0;
                const t = arc.progress;
                const px = (1 - t) * (1 - t) * pFrom.x + 2 * (1 - t) * t * cpX + t * t * pTo.x;
                const py = (1 - t) * (1 - t) * pFrom.y + 2 * (1 - t) * t * cpY + t * t * pTo.y;

                ctx.fillStyle = '#fef08a';
                ctx.shadowColor = '#f59e0b';
                ctx.shadowBlur = 8;
                ctx.beginPath();
                ctx.arc(px, py, 3.5, 0, Math.PI * 2);
                ctx.fill();
                ctx.shadowBlur = 0;
            }
        });

        // 6. Living Diaspora Hub Markers
        state.hubs.forEach(hub => {
            const p = project(hub.lat, hub.lon);
            if (p.visible) {
                // Outer ripple
                ctx.fillStyle = 'rgba(163,22,33, 0.35)';
                ctx.beginPath();
                ctx.arc(p.x, p.y, 9 + hub.members.length * 1.5, 0, Math.PI * 2);
                ctx.fill();

                // Inner core beacon
                ctx.fillStyle = '#ef6b73';
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.arc(p.x, p.y, 4.5, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();

                // City & Member Count Label
                ctx.font = 'bold 11px Inter, sans-serif';
                ctx.fillStyle = '#ffffff';
                ctx.shadowColor = 'rgba(0,0,0,0.8)';
                ctx.shadowBlur = 4;
                ctx.textAlign = 'center';
                ctx.fillText(`${hub.name} (${hub.members.length})`, p.x, p.y - 12);
                ctx.shadowBlur = 0;
            }
        });

        state.animationFrameId = requestAnimationFrame(window.renderGlobeFrame);
    };

    // =========================================================================
    // Kinship Auto-Matching Resolution Engine ("Is This You?")
    // =========================================================================
    let currentPendingMatch = null;

    async function checkPendingKinshipMatches() {
        try {
            const resp = await fetch('/api/kinship/pending-matches', {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) return;
            const res = await resp.json();
            if (res.status === 'success' && res.data && res.data.length > 0) {
                currentPendingMatch = res.data[0];
                const banner = document.getElementById('kinshipAutoMatchBanner');
                const nameEl = document.getElementById('matchPersonName');
                const hintEl = document.getElementById('matchRelationshipHint');
                const badgeEl = document.getElementById('matchConfidenceBadge');

                if (nameEl) nameEl.textContent = currentPendingMatch.full_name;
                if (hintEl) hintEl.textContent = currentPendingMatch.relationship_hint || 'Relative on the family tree';
                if (badgeEl) badgeEl.textContent = `${currentPendingMatch.confidence_percent || 90}% Match`;

                if (banner) {
                    banner.classList.remove('d-none');
                    banner.classList.add('d-flex');
                }
            }
        } catch (e) {
            console.debug('Kinship auto-match check: no pending match found');
        }
    }

    window.openClaimMatchModal = function() {
        if (!currentPendingMatch) return;
        const nameEl = document.getElementById('claimModalName');
        const relEl = document.getElementById('claimModalRelation');
        const avEl = document.getElementById('claimModalAvatar');

        if (nameEl) nameEl.textContent = currentPendingMatch.full_name;
        if (relEl) relEl.textContent = currentPendingMatch.relationship_hint || '';
        if (avEl && currentPendingMatch.avatar_url) avEl.src = currentPendingMatch.avatar_url;

        const modalEl = document.getElementById('claimMatchModal');
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };

    window.executeClaimMatch = async function() {
        if (!currentPendingMatch) return;
        const btn = document.getElementById('btnExecuteClaim');
        const originalText = btn ? btn.innerHTML : 'Confirm & Connect';
        if (btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Connecting...';
            btn.disabled = true;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ $_SESSION['token'] ?? '' }}';

        try {
            const resp = await fetch('/api/kinship/claim-match', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    node_id: currentPendingMatch.node_id,
                    token: csrfToken
                })
            });

            const res = await resp.json();
            if (resp.ok && res.status === 'success') {
                const modalEl = document.getElementById('claimMatchModal');
                if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }
                const banner = document.getElementById('kinshipAutoMatchBanner');
                if (banner) banner.style.display = 'none';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome Home!',
                        text: res.message || 'You are now connected to your spot on the family tree.',
                        confirmButtonColor: '#a31621'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.location.reload();
                }
            } else {
                alert(res.message || 'Unable to claim this position. Please try again.');
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        } catch (err) {
            alert('A network error occurred. Please try again.');
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    };

    window.dismissCurrentMatch = async function() {
        if (!currentPendingMatch) return;
        const banner = document.getElementById('kinshipAutoMatchBanner');
        if (banner) banner.style.display = 'none';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ $_SESSION['token'] ?? '' }}';

        try {
            await fetch('/api/kinship/dismiss-match', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-XSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    node_id: currentPendingMatch.node_id,
                    token: csrfToken
                })
            });
        } catch (e) {
            // fail-safe ignore
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        checkPendingKinshipMatches();
    });
</script>

@include('member.modals.lineageQuestModal')
@include('member.modals.familyFlyerModal')
@include('member.modals.timeCapsuleModal')
@include('member.modals.inviteFamilyModal')
@endsection
