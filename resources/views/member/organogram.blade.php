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
                <div class="header-actions d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-3">
                    <button type="button" onclick="if(window.startAppTour) window.startAppTour();" class="btn btn-outline-primary fw-bold text-center d-flex align-items-center justify-content-center" style="border-radius: 50px; white-space: nowrap; padding: 0.6rem 1.1rem; border: 1px solid #c7d2fe; background: rgba(255, 255, 255, 0.9); box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);" title="Take interactive app tour">
                        <i class="bi bi-compass-fill me-2 text-primary"></i>Take Tour
                    </button>
                    <a href="/member/organogram/export-poster" target="_blank" class="btn btn-outline-dark fw-bold text-center d-flex align-items-center justify-content-center" style="border-radius: 50px; white-space: nowrap; padding: 0.6rem 1.1rem; border: 1px solid #e2e8f0; background: rgba(255, 255, 255, 0.95); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);" title="Download 300 DPI Dynasty Poster (SVG)">
                        <i class="bi bi-printer me-2 text-dark"></i>Dynasty Poster
                    </a>
                    <a href="/familyStudio" id="configureFamilyBtn" class="btn btn-primary fw-bold w-100 w-md-auto text-center" style="border-radius: 50px; white-space: nowrap; padding: 0.6rem 1.2rem; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); background: linear-gradient(135deg, #4f46e5, #3b82f6); border: none;">
                        <i class="bi bi-stars me-2"></i>Lineage Studio
                    </a>
                    <div class="search-box-wrapper w-100" id="memberSearchWrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="memberSearchInput" class="member-search-input w-100" placeholder="Find relative in tree..." autocomplete="off">
                        <div id="searchDropdown" class="search-dropdown"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Modal View Switcher -->
    <div class="tree-mode-switcher-container">
        <div class="tree-mode-pill-group" role="tablist" aria-label="Family Tree View Modes">
            <button type="button" id="btnModeCanvas" class="tree-mode-btn active" onclick="switchTreeViewMode('canvas')">
                <i class="bi bi-diagram-3-fill"></i> <span>Dynamic Canvas</span>
            </button>
            <button type="button" id="btnModePedigree" class="tree-mode-btn" onclick="switchTreeViewMode('pedigree')">
                <i class="bi bi-list-nested"></i> <span>Pedigree Outline</span>
            </button>
        </div>
    </div>

    <!-- Tree Workspace Container -->
    <div class="organogram-container">

        @if(empty($orgData['father']['fullName']) && empty($orgData['mother']['fullName']) && empty($orgData['father']['name']) && empty($orgData['mother']['name']))
            <div class="alert alert-info shadow-sm border-0 rounded-3 mb-4 d-flex flex-column flex-md-row align-items-center justify-content-between mx-3 mt-3 gap-3 text-center text-md-start" style="background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); z-index: 10; position: relative;">
                <div class="d-flex flex-column flex-md-row align-items-center gap-2 gap-md-3">
                    <i class="bi bi-diagram-3-fill fs-3 text-primary"></i>
                    <div>
                        <h6 class="mb-1 fw-bold text-dark">Build your family tree!</h6>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Your tree looks a bit empty. Add your parents and siblings to start building your lineage.</p>
                    </div>
                </div>
                <button type="button" onclick="openAddRelativeModalFromBanner()" class="btn btn-primary btn-sm px-3 py-2 fw-bold w-100 w-md-auto" style="border-radius: 8px; white-space: nowrap;">Add Family Members</button>
            </div>
        @endif

        <!-- Mode 1: Dynamic Canvas View -->
        <div class="tree-container position-relative" id="treeContainer">
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

            <!-- Dynamic Graph Canvas / Wrapper (FamilyTreeJS) -->
            <div id="tree" style="width: 100%; height: 75vh; border-radius: 12px; background: rgba(255,255,255,0.8); box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.4);"></div>

            <!-- Mobile & Desktop Floating Controls Dock -->
            <div class="mobile-tree-fab-dock" id="treeFabDock">
                <button type="button" class="fab-dock-btn" onclick="centerOnMe()" title="Center on Me">
                    <i class="bi bi-crosshair2"></i>
                </button>
                <button type="button" class="fab-dock-btn" onclick="fitTreeScreen()" title="Fit to Screen">
                    <i class="bi bi-aspect-ratio"></i>
                </button>
                <button type="button" class="fab-dock-btn" onclick="zoomInTree()" title="Zoom In">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button" class="fab-dock-btn" onclick="zoomOutTree()" title="Zoom Out">
                    <i class="bi bi-dash-lg"></i>
                </button>
                <button type="button" class="fab-dock-btn" onclick="toggleFullscreenTree()" id="btnTreeFullscreen" title="Fullscreen Canvas">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
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
                <div class="modal-footer p-3 border-top bg-light">
                    <button class="btn btn-sm btn-primary w-100 fw-bold" id="openAddRelativeModalBtn">
                        <i class="bi bi-person-plus-fill"></i> Add Family Member
                    </button>
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

<script src="/public/js/vendor/familytree.js?v=8.14" onerror="this.onerror=null;this.src='https://balkan.app/js/FamilyTree.js';"></script>
<script>
    window.__ROOT_USER_ID__ = '{{ $data['id'] ?? '' }}';
    window.__FAMILY_CODE__ = '{{ $data['famCode'] ?? '' }}';
    
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

            function styleFamilyNode(templateName, cardFill) {
                // Clone so late overrides don't leak between male/female/base templates
                FamilyTree.templates[templateName] = Object.assign({}, FamilyTree.templates[templateName]);
                const t = FamilyTree.templates[templateName];

                t.size = [NODE_W, NODE_H];

                t.defs = '<filter id="ftNodeShadow" x="-40%" y="-40%" width="180%" height="180%">'
                    + '<feDropShadow dx="0" dy="6" stdDeviation="7" flood-color="#0f172a" flood-opacity="0.22"></feDropShadow>'
                    + '</filter>';

                if (isMobileScreen) {
                    // Mobile compact card body
                    t.node = '<rect x="4" y="44" width="' + (NODE_W - 8) + '" height="' + (NODE_H - 48) + '" '
                        + 'rx="16" ry="16" stroke-width="0" fill="' + cardFill + '" filter="url(#ftNodeShadow)"></rect>';

                    // Role label
                    t.field_2 = '<text style="font-size:9px;font-weight:600;letter-spacing:0.8px;text-transform:uppercase;" '
                        + 'fill="rgba(255,255,255,0.85)" x="' + CX + '" y="112" text-anchor="middle">{val}</text>';

                    // Name — two lines
                    t.field_0 = '<text data-width="' + (NODE_W - 16) + '" style="font-size:12px;font-weight:700;" '
                        + 'fill="#ffffff" x="' + CX + '" y="132" text-anchor="middle">{val}</text>';
                    t.field_1 = '<text data-width="' + (NODE_W - 16) + '" style="font-size:12px;font-weight:700;" '
                        + 'fill="#ffffff" x="' + CX + '" y="148" text-anchor="middle">{val}</text>';

                    // Circular portrait
                    t.img_0 = '<circle cx="' + CX + '" cy="42" r="38" fill="#ffffff"></circle>'
                        + '<clipPath id="ftNodeImgMobile"><circle cx="' + CX + '" cy="42" r="34"></circle></clipPath>'
                        + '<image preserveAspectRatio="xMidYMid slice" clip-path="url(#ftNodeImgMobile)" xlink:href="{val}" '
                        + 'x="' + (CX - 34) + '" y="8" width="68" height="68"></image>';
                } else {
                    // Rounded card body (portrait overlaps its top edge)
                    t.node = '<rect x="6" y="64" width="' + (NODE_W - 12) + '" height="' + (NODE_H - 72) + '" '
                        + 'rx="22" ry="22" stroke-width="0" fill="' + cardFill + '" filter="url(#ftNodeShadow)"></rect>';

                    // Role / relationship label (uppercase, above the name)
                    t.field_2 = '<text style="font-size:11px;font-weight:600;letter-spacing:1.4px;text-transform:uppercase;" '
                        + 'fill="rgba(255,255,255,0.82)" x="' + CX + '" y="150" text-anchor="middle">{val}</text>';

                    // Name — up to two balanced lines
                    t.field_0 = '<text data-width="' + (NODE_W - 24) + '" style="font-size:15px;font-weight:700;" '
                        + 'fill="#ffffff" x="' + CX + '" y="176" text-anchor="middle">{val}</text>';
                    t.field_1 = '<text data-width="' + (NODE_W - 24) + '" style="font-size:15px;font-weight:700;" '
                        + 'fill="#ffffff" x="' + CX + '" y="197" text-anchor="middle">{val}</text>';

                    // Large circular portrait with white ring
                    t.img_0 = '<circle cx="' + CX + '" cy="60" r="58" fill="#ffffff"></circle>'
                        + '<clipPath id="ftNodeImg"><circle cx="' + CX + '" cy="60" r="52"></circle></clipPath>'
                        + '<image preserveAspectRatio="xMidYMid slice" clip-path="url(#ftNodeImg)" xlink:href="{val}" '
                        + 'x="' + (CX - 52) + '" y="8" width="104" height="104"></image>';
                }
            }

            styleFamilyNode('tommy', '#64748b');
            styleFamilyNode('tommy_male', '#2563eb');
            styleFamilyNode('tommy_female', '#e8630c');

            var family = new FamilyTree(document.getElementById("tree"), {
                template: "tommy",
                mode: "light",
                enableSearch: false,
                mouseScrool: FamilyTree.action.zoom,
                scaleInitial: isMobileScreen ? 0.75 : FamilyTree.match.boundary,
                nodeBinding: {
                    field_0: "nameL1",
                    field_1: "nameL2",
                    field_2: "title",
                    img_0: "img"
                },
                nodes: familyTreeNodes
            });
            
            window.family = family;
            window.familyTreeNodes = familyTreeNodes;
            window.graphData = graphData;

            // Intercept clicks to trigger the custom Bottom Sheet / Modal flow
            family.on('click', function (sender, args) {
                const nodeData = familyTreeNodes.find(n => String(n.id) === String(args.node.id));
                if (nodeData) {
                    currentBaseNodeName = nodeData.name;
                    currentBaseNodeId = nodeData.id;
                    
                    if (typeof window.showPersonDetails === 'function') {
                        const rawNode = graphData.nodes.find(n => String(n.id) === String(args.node.id));
                        
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
                            isRegistered: !!nodeData.legacyId
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
            if (savedMode === 'pedigree' || (!savedMode && window.innerWidth <= 480)) {
                switchTreeViewMode('pedigree');
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
            if (btnPedigree) btnPedigree.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'pedigree'); } catch(_) {}
        } else {
            if (pedigreeContainer) pedigreeContainer.classList.add('d-none');
            if (canvasContainer) {
                canvasContainer.classList.remove('d-none');
                if (window.family) {
                    setTimeout(() => window.family.fit(), 100);
                }
            }
            if (legend) legend.classList.remove('d-none');
            if (btnPedigree) btnPedigree.classList.remove('active');
            if (btnCanvas) btnCanvas.classList.add('active');
            try { localStorage.setItem('fp_tree_view_mode', 'canvas'); } catch(_) {}
        }
    };

    window.centerOnMe = function() {
        if (!window.family) return;
        const rootNodeId = getGraphNodeId(window.__ROOT_USER_ID__, '');
        if (rootNodeId) {
            window.family.center(rootNodeId);
        } else {
            window.family.fit();
        }
    };

    window.fitTreeScreen = function() {
        if (!window.family) return;
        window.family.fit();
    };

    window.zoomInTree = function() {
        if (!window.family) return;
        window.family.zoom(true);
    };

    window.zoomOutTree = function() {
        if (!window.family) return;
        window.family.zoom(false);
    };

    window.toggleFullscreenTree = function() {
        const container = document.getElementById('treeContainer');
        const btn = document.getElementById('btnTreeFullscreen');
        if (!container) return;
        const isFull = container.classList.toggle('fullscreen-active');
        if (btn) {
            btn.innerHTML = isFull ? '<i class="bi bi-fullscreen-exit"></i>' : '<i class="bi bi-arrows-fullscreen"></i>';
        }
        if (window.family) {
            setTimeout(() => window.family.fit(), 250);
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
                isRegistered: !!rawNode.user_id
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

    function openAddRelativeModalFromBanner() {
        currentBaseNodeName = 'Yourself';
        currentBaseNodeId = getGraphNodeId(window.__ROOT_USER_ID__, currentBaseNodeName);
        
        document.getElementById('addRelativeBaseName').textContent = currentBaseNodeName;
        document.getElementById('partnerBaseNodeId').value = currentBaseNodeId;
        document.getElementById('childBaseNodeId').value = currentBaseNodeId;

        // Reset wizard
        document.getElementById('step1').classList.remove('d-none');
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
        document.getElementById('addRelativeError').classList.add('d-none');

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
