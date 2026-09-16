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
                        {{ $data['firstName'] ?? '' }} {{ $data['lastName'] ?? '' }}'s Family Tree
                    </h2>
                    <p class="organogram-subtitle">
                        Explore your family heritage, partners, and lineage. Click any person to see their details and full profile.
                    </p>
                </div>

                <!-- Actions (Search & Config) -->
                <div class="header-actions d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-2">
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
                    <a href="/familyStudio" id="configureFamilyBtn" class="btn-action" title="Open Lineage Studio">
                        <i class="bi bi-stars text-warning"></i> Lineage Studio
                    </a>
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
    <div class="tree-mode-switcher-container text-center mb-3">
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
        </div>
    </div>

    <!-- Tree Workspace Container -->
    <div class="organogram-container">

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

        @if(empty($isReadOnly))
        <div id="missingNodeBanner" class="alert {{ $analysis['is_flourishing'] ? 'alert-success' : 'alert-info' }} shadow-sm border-0 rounded-3 mb-4 d-flex flex-column flex-md-row align-items-center justify-content-between mx-3 mt-3 gap-3 text-center text-md-start" style="background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); z-index: 10; position: relative; border-left: 5px solid {{ $analysis['is_flourishing'] ? '#10b981' : '#3b82f6' }} !important;">
            <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100">
                <div class="d-flex align-items-center justify-content-center rounded-circle p-2" style="background: rgba(59, 130, 246, 0.1); min-width: 44px; min-height: 44px;">
                    <i class="bi {{ $analysis['is_flourishing'] ? 'bi-stars text-success' : 'bi-diagram-3-fill text-primary' }} fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1 justify-content-center justify-content-md-start">
                        <h6 class="mb-0 fw-bold text-dark">{{ $analysis['banner_title'] }}</h6>
                        <span class="badge {{ $analysis['completeness_score'] >= 80 ? 'bg-success' : ($analysis['completeness_score'] >= 40 ? 'bg-warning text-dark' : 'bg-primary') }}" style="font-size: 0.75rem;">
                            {{ $analysis['completeness_score'] }}% Lineage Complete
                        </span>
                        @if(!empty($analysis['missing_labels']))
                            @foreach($analysis['missing_labels'] as $label)
                                <span class="badge bg-light text-dark border" style="font-size: 0.75rem;">
                                    <i class="bi bi-plus-circle-fill text-info me-1"></i>Missing: {{ $label }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                    <p class="mb-0 text-muted" style="font-size: 0.88rem;">{{ $analysis['banner_subtitle'] }}</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-center">
                <button type="button" onclick="openAddRelativeModalFromBanner('{{ $analysis['recommended_cta_type'] }}')" class="btn btn-primary btn-sm px-3 py-2 fw-bold w-100 w-md-auto" style="border-radius: 8px; white-space: nowrap;">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    @if($analysis['recommended_cta_type'] === 'parents')
                        Add Parents
                    @elseif($analysis['recommended_cta_type'] === 'sibling')
                        Add Siblings
                    @elseif($analysis['recommended_cta_type'] === 'partner')
                        Add Partner
                    @elseif($analysis['recommended_cta_type'] === 'child')
                        Add Children
                    @else
                        Add Family Members
                    @endif
                </button>
                <button type="button" class="btn-close text-muted" onclick="document.getElementById('missingNodeBanner').remove();" aria-label="Close" title="Dismiss suggestion"></button>
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
                        <div><i class="bi bi-people-fill me-1"></i> Descendants: <strong id="cardDescendants">0</strong></div>
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
                            <a id="cardSmsBtn" href="#" class="btn-action" style="background: #4f46e5; color: white; border: none; font-size: 0.8rem; padding: 7px 12px; border-radius: 999px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-chat-text-fill"></i> SMS
                            </a>
                            <button type="button" id="cardCopyBtn" onclick="copyCardInviteLink(this)" class="btn-action" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); font-size: 0.8rem; padding: 7px 12px; border-radius: 999px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-link-45deg"></i> Copy Link
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Actions inside card -->
                    <div class="card-actions">
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn-action w-100" onclick="traceCardLineage()" style="background: rgba(99, 102, 241, 0.2); border-color: rgba(129, 140, 248, 0.4); color: #c7d2fe;">
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
                <div id="tree"></div>

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
            <!-- Dynamically populated via JS from graphData -->
        </div>

        <!-- Legend (Canvas mode) -->
        <div class="family-legend" id="familyLegend">
            <div class="legend-item">
                <div class="legend-color legend-male"></div>
                <span>Male</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-female"></div>
                <span>Female</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-spouse"></div>
                <span>Spouse / Partner</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-divorced"></div>
                <span>Divorced / Past Union</span>
            </div>
        </div>

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
                    <button class="btn btn-sm btn-primary flex-grow-1 fw-bold" id="openAddRelativeModalBtn">
                        <i class="bi bi-person-plus-fill"></i> Add Family Member
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

        <!-- Heritage Story Card Modal (9:16 Instagram/WhatsApp Story Format) -->
        <div class="modal fade" id="heritageStoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #0f172a;">
                    <div class="modal-header border-0 pb-0 text-white">
                        <h5 class="modal-title fw-bold" style="font-size: 1rem;"><i class="bi bi-stars text-warning me-2"></i>Heritage Story Card</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div id="storyCardPreview" class="story-card-preview">
                            <canvas id="storyCanvas" width="540" height="960" style="width: 100%; max-width: 280px; border-radius: 18px; box-shadow: 0 16px 36px rgba(0,0,0,0.5);"></canvas>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-3">
                            <button type="button" id="btnDownloadStory" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 50px; background: linear-gradient(135deg, #4f46e5, #3b82f6); border: none;">
                                <i class="bi bi-download me-2"></i>Save Card
                            </button>
                            <button type="button" class="btn btn-outline-light fw-bold px-3 py-2" style="border-radius: 50px;" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/public/js/vendor/kinshiptree.js?v=9.0"></script>
<script>
    window.__ROOT_USER_ID__ = '{{ $data['id'] ?? '' }}';
    window.__FAMILY_CODE__ = '{{ $data['famCode'] ?? '' }}';
    window.__IS_READ_ONLY__ = {{ !empty($isReadOnly) ? 'true' : 'false' }};
    
    const graphData = {!! $graphJson ?? '{}' !!};

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

        if (graphData && graphData.nodes) {
            // Build a lookup for children to find parents
            const childToParents = {};
            if (graphData.children) {
                graphData.children.forEach(child => {
                    const unionId = child.union_id;
                    const union = graphData.unions.find(u => u.id === unionId);
                    if (union) {
                        const p1 = graphData.nodes.find(n => n.id === union.partner_1_id);
                        const p2 = graphData.nodes.find(n => n.id === union.partner_2_id);
                        
                        let fid = null, mid = null;
                        if (p1 && p1.gender === 'Male') fid = p1.id;
                        if (p1 && p1.gender === 'Female') mid = p1.id;
                        
                        if (p2 && p2.gender === 'Male') fid = p2.id;
                        if (p2 && p2.gender === 'Female') mid = p2.id;
                        
                        childToParents[child.child_id] = { fid, mid };
                    }
                });
            }

            // Map Unions to pids (partners)
            const nodeToPids = {};
            if (graphData.unions) {
                graphData.unions.forEach(u => {
                    if (!nodeToPids[u.partner_1_id]) nodeToPids[u.partner_1_id] = [];
                    if (!nodeToPids[u.partner_2_id]) nodeToPids[u.partner_2_id] = [];
                    nodeToPids[u.partner_1_id].push(u.partner_2_id);
                    nodeToPids[u.partner_2_id].push(u.partner_1_id);
                });
            }

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
                    title: node.bio || 'Family Member',
                    legacyId: node.user_id
                });
            });
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
                    field_0: "name",
                    field_1: "nameL2",
                    field_2: "title",
                    img_0: "img"
                },
                nodes: familyTreeNodes
            });
            
            window.family = family;
            window.familyTreeNodes = familyTreeNodes;
            window.graphData = graphData;

            // Intercept clicks to trigger the floating Luxury Detail Card & custom Modal flow
            family.on('click', function (sender, args) {
                const nodeData = familyTreeNodes.find(n => String(n.id) === String(args.node.id));
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
                            email: rawNode ? rawNode.email : '',
                            maritalStatus: rawNode ? rawNode.marital_status : '',
                            occupation: rawNode ? rawNode.occupation : '',
                            country: rawNode ? rawNode.country_of_residence : '',
                            isDeceased: rawNode ? rawNode.is_deceased : false,
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

        // Check view mode preference on initial load
        try {
            const savedMode = localStorage.getItem('fp_tree_view_mode');
            if (savedMode) {
                // User has a saved preference
                if (savedMode === 'pedigree') switchTreeViewMode('pedigree');
                else if (savedMode === 'lineage') switchTreeViewMode('lineage');
            } else {
                // Auto-detect optimal view based on tree structure
                if (window.innerWidth <= 480) {
                    // Always use pedigree on mobile
                    switchTreeViewMode('pedigree');
                } else if (familyTreeNodes && familyTreeNodes.length > 0) {
                    // For desktop, detect if tree is wide (many siblings)
                    // Count nodes per generation to estimate spread
                    const nodesByGeneration = {};
                    familyTreeNodes.forEach(n => {
                        const key = n.fid ? 'has_parents' : (n.pids && n.pids.length ? 'has_partners' : 'orphan');
                        nodesByGeneration[key] = (nodesByGeneration[key] || 0) + 1;
                    });

                    // If many siblings or household members, pedigree is better
                    const householdNodes = familyTreeNodes.filter(n => !n.fid && !n.mid);
                    if (householdNodes.length > 8 || familyTreeNodes.length > 30) {
                        switchTreeViewMode('pedigree');
                    }
                    // Otherwise use default panoramic (canvas)
                }
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
    window.switchTreeViewMode = function(mode) {
        const canvasContainer = document.getElementById('treeContainer');
        const pedigreeContainer = document.getElementById('pedigreeOutlineContainer');
        const legend = document.getElementById('familyLegend');
        const btnCanvas = document.getElementById('btnModeCanvas');
        const btnLineage = document.getElementById('btnModeLineage');
        const btnPedigree = document.getElementById('btnModePedigree');

        window.trackTreeAnalytics('tree_view_mode_switched', null, { mode: mode });

        if (mode === 'pedigree') {
            if (canvasContainer) canvasContainer.classList.add('d-none');
            if (legend) legend.classList.add('d-none');
            if (pedigreeContainer) {
                pedigreeContainer.classList.remove('d-none');
                renderPedigreeOutline();
            }
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnPedigree) btnPedigree.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'pedigree'); } catch(_) {}
        } else if (mode === 'lineage') {
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
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
            if (btnCanvas) btnCanvas.classList.remove('active');
            if (btnLineage) btnLineage.classList.add('active');
        } else {
            if (typeof window.clearLineageTrace === 'function') window.clearLineageTrace();
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (canvasContainer) {
                canvasContainer.classList.remove('d-none');
                if (window.family) {
                    setTimeout(() => window.family.fit(), 100);
                }
            }
            if (legend) legend.classList.remove('d-none');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnLineage) btnLineage.classList.remove('active');
            if (btnCanvas) btnCanvas.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'canvas'); } catch(_) {}
        }
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
        if (genderEl) genderEl.textContent = node.gender ? node.gender.toUpperCase() : 'MALE';
        if (avatarEl) avatarEl.src = node.img || (node.gender === 'female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');

        const layoutNode = window.family && window.family.layout && window.family.layout.nodes 
            ? window.family.layout.nodes.find(n => String(n.id) === String(node.id)) 
            : null;
        if (descendantsEl) descendantsEl.textContent = layoutNode ? layoutNode.descendantCount : 0;

        const isReadOnlyTree = Boolean(window.__IS_READ_ONLY__) || (typeof graphData !== 'undefined' && graphData && graphData.isReadOnly);

        if (node.legacyId) {
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
                const familyCode = window.__FAMILY_CODE__ || 'OLAOGUN';
                const cleanName = (node.name || 'Relative').replace(/\s+/g, ' ').trim();

                // Generate opaque invite token via AJAX — zero PII in the URL
                fetch('/api/invite/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        family_code: familyCode,
                        first_name: cleanName.split(' ')[0] || '',
                        last_name: cleanName.split(' ').slice(1).join(' ') || '',
                        node_id: node.id || null,
                        type: 'organogram'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success' && data.invite_url) {
                        const inviteLink = data.invite_url;
                        currentInviteLink = inviteLink;

                        const inviteMessage = encodeURIComponent(
                            `🌳 *Family Tree Invitation* 🌳\n\n` +
                            `You are invited to connect with the *${familyCode} Family* on FamilyPlatform.\n\n` +
                            `Click below to claim your spot, explore our lineage, and connect with the family:\n` +
                            `👉 ${inviteLink}`
                        );

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

        const isRootUser = (String(node.legacyId) === String(window.__ROOT_USER_ID__)) || 
                           (String(node.id) === String(getGraphNodeId(window.__ROOT_USER_ID__, '')));
        const removeWrapper = document.getElementById('cardRemoveActionWrapper');
        if (removeWrapper) {
            removeWrapper.style.display = (isRootUser || isReadOnlyTree) ? 'none' : 'block';
        }

        card.style.display = 'block';
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
        if (String(nodeId) === String(rootGraphId) || String(nodeId) === String(window.__ROOT_USER_ID__)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Action Not Allowed', 'You cannot remove yourself from your family tree.', 'warning');
            } else {
                alert('You cannot remove yourself from your family tree.');
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
                cancelButtonText: 'Cancel'
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
                        const isDeceased = !!node.is_deceased;
                        const isSelf = String(node.user_id) === String(window.__ROOT_USER_ID__);

                        return `
                        <div class="pedigree-member-card ${isSelf ? 'border-primary' : ''}" onclick="openNodeFromPedigree('${node.id}')">
                            <img src="${avatar}" alt="${fullName}" class="pedigree-member-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                            <div class="pedigree-member-info">
                                <div class="pedigree-member-name">${fullName} ${isSelf ? '<span class="badge bg-primary ms-1" style="font-size: 0.65rem;">You</span>' : ''}</div>
                                <div class="pedigree-member-meta">
                                    <span class="pedigree-role-pill">${role}</span>
                                    ${isDeceased ? '<span class="pedigree-deceased-pill"><i class="bi bi-flower1"></i> Deceased</span>' : ''}
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
                        return `
                        <div class="pedigree-member-card" onclick="openNodeFromPedigree('${node.id}')">
                            <img src="${avatar}" alt="${fullName}" class="pedigree-member-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                            <div class="pedigree-member-info">
                                <div class="pedigree-member-name">${fullName}</div>
                                <div class="pedigree-member-meta">
                                    <span class="pedigree-role-pill">${node.bio || 'Relative'}</span>
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
                maritalStatus: rawNode.marital_status || '',
                occupation: rawNode.occupation || '',
                country: rawNode.country_of_residence || '',
                nodeId: rawNode.id,
                isDeceased: rawNode.is_deceased || false,
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
        grad.addColorStop(0.35, '#1e1b4b');
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
            ctx.font = '600 16px Plus Jakarta Sans, sans-serif';
            ctx.letterSpacing = '3px';
            ctx.fillText('FAMILY DYNASTY ARCHIVE', W / 2, 85);

            // Member Name
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 32px Plus Jakarta Sans, sans-serif';
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
            ctx.font = 'bold 15px Plus Jakarta Sans, sans-serif';
            ctx.fillText((relation || 'Relative').toUpperCase(), W / 2, 508);

            if (isDeceased) {
                ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                ctx.font = 'italic 14px Plus Jakarta Sans, sans-serif';
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
            ctx.font = 'bold 38px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = '#d4af37';
            ctx.fillText(String(count), W / 2 - 100, boxY + 60);
            ctx.font = '600 13px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.fillText('RECORDED KIN', W / 2 - 100, boxY + 90);

            ctx.font = 'bold 38px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = '#d4af37';
            ctx.fillText('6', W / 2 + 100, boxY + 60);
            ctx.font = '600 13px Plus Jakarta Sans, sans-serif';
            ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.fillText('GENERATIONS', W / 2 + 100, boxY + 90);

            // Footer
            ctx.font = '14px Plus Jakarta Sans, sans-serif';
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

            ctx.font = 'bold 70px Cinzel, serif';
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
        document.getElementById('addRelativeBaseName').textContent = currentBaseNodeName;
        document.getElementById('partnerBaseNodeId').value = currentBaseNodeId;
        document.getElementById('childBaseNodeId').value = currentBaseNodeId;

        // Reset wizard
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
        document.getElementById('addRelativeError').classList.add('d-none');

        // Close person modal and open wizard
        document.getElementById('personModal').style.display = 'none';
        var myModal = new bootstrap.Modal(document.getElementById('addRelativeModal'));
        myModal.show();
    });

    function openAddRelativeModalFromBanner(relationType) {
        currentBaseNodeName = 'Yourself';
        currentBaseNodeId = getGraphNodeId(window.__ROOT_USER_ID__, currentBaseNodeName);
        
        document.getElementById('addRelativeBaseName').textContent = currentBaseNodeName;
        document.getElementById('partnerBaseNodeId').value = currentBaseNodeId;
        document.getElementById('childBaseNodeId').value = currentBaseNodeId;

        // Reset wizard steps
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
        document.getElementById('step2-parents').classList.add('d-none');
        document.getElementById('addRelativeError').classList.add('d-none');

        const validTypes = ['parents', 'partner', 'child', 'sibling'];
        if (relationType && validTypes.includes(relationType)) {
            selectRelativeType(relationType);
        }

        var myModal = new bootstrap.Modal(document.getElementById('addRelativeModal'));
        myModal.show();
    }


    // To track active node clicks since showModal is compiled
    document.addEventListener('click', function(e) {
        const node = e.target.closest('.tree-node');
        if (node) {
            document.querySelectorAll('.tree-node').forEach(n => n.classList.remove('active-clicked'));
            node.classList.add('active-clicked');
        }
    });

    function selectRelativeType(type) {
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('addRelativeError').classList.add('d-none');
        
        if (type === 'partner') {
            document.getElementById('step2-partner').classList.remove('d-none');
        } else if (type === 'child') {
            document.getElementById('step2-child').classList.remove('d-none');
            fetchUnionsForNode(currentBaseNodeId);
        } else if (type === 'sibling') {
            document.getElementById('step2-child').classList.remove('d-none'); // Reuses the child form structurally
            fetchParentUnionsForNode(currentBaseNodeId);
        } else if (type === 'parents') {
            document.getElementById('step2-parents').classList.remove('d-none');
        }
    }

    function backToStep1() {
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
        document.getElementById('step2-parents').classList.add('d-none');
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

    // Form Submissions
    document.getElementById('addPartnerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/partner', 'submitPartnerBtn', 'Saving...');
    });

    document.getElementById('addChildForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/child', 'submitChildBtn', 'Saving...');
    });
    
    document.getElementById('addParentsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/parents', 'submitParentsBtn', 'Saving...');
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
        const originalText = btn.innerHTML;
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
                // Success - reload page to show new tree
                window.location.reload();
            } else {
                errorDiv.textContent = window.extractErrorMessage ? window.extractErrorMessage(data) : extractStudioErrorMessage(data.message || data.error || data);
                errorDiv.classList.remove('d-none');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            errorDiv.textContent = window.extractErrorMessage ? window.extractErrorMessage(err, 'A network error occurred. Please try again.') : 'A network error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

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
</script>
@endsection
