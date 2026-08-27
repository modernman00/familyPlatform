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
                    <h2 class="organogram-title fw-bold">
                        <i class="bi bi-diagram-3"></i>
                        {{ $data['firstName'] ?? '' }} {{ $data['lastName'] ?? '' }}'s Family Tree
                    </h2>
                    <p class="organogram-subtitle">
                        Explore your family heritage, partners, and lineage. Click any person to see their details and full profile.
                    </p>
                </div>

                <!-- Actions (Search & Config) -->
                <div class="header-actions d-flex align-items-center gap-3">
                    <a href="/accountSetting#family-settings" class="btn btn-primary fw-bold" style="border-radius: 50px; white-space: nowrap; padding: 0.6rem 1.2rem; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); background: linear-gradient(135deg, #4f46e5, #3b82f6); border: none;">
                        <i class="bi bi-gear-fill me-2"></i>Configure Family Tree
                    </a>
                    <div class="search-box-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="memberSearchInput" class="member-search-input" placeholder="Find relative in tree..." autocomplete="off">
                        <div id="searchDropdown" class="search-dropdown"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tree Workspace Container -->
    <div class="organogram-container">

        @if(empty($orgData['father']['fullName']) && empty($orgData['mother']['fullName']) && empty($orgData['father']['name']) && empty($orgData['mother']['name']))
            <div class="alert alert-info shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center justify-content-between mx-3 mt-3" style="background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); z-index: 10; position: relative;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-diagram-3-fill fs-3 me-3 text-primary"></i>
                    <div>
                        <h6 class="mb-1 fw-bold text-dark">Build your family tree!</h6>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Your tree looks a bit empty. Add your parents and siblings to start building your lineage.</p>
                    </div>
                </div>
                <button type="button" onclick="openAddRelativeModalFromBanner()" class="btn btn-primary btn-sm px-3 py-2 fw-bold" style="border-radius: 8px; white-space: nowrap;">Add Family Members</button>
            </div>
        @endif

        <div class="tree-container" id="treeContainer">
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
            </div>

            <!-- Dynamic Graph Canvas / Wrapper (Replaced by FamilyTreeJS) -->
            <div id="tree" style="width: 100%; height: 75vh; border-radius: 12px; background: rgba(255,255,255,0.8); box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.4);"></div>
        </div>

        <!-- Legend -->
        <div class="family-legend">
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

        <!-- Person Detail Modal / Drawer -->
        <div class="person-modal" id="personModal">
            <div class="modal-content">
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

        <!-- Add Relative Wizard Modal -->
        <div class="modal fade" id="addRelativeModal" tabindex="-1" aria-labelledby="addRelativeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="backdrop-filter: blur(8px);">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.95);">
                    <div class="modal-header border-0 bg-primary text-white">
                        <h5 class="modal-title fw-bold" id="addRelativeModalLabel"><i class="bi bi-diagram-3-fill"></i> Add Relative</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="addRelativeError" class="alert alert-danger d-none" style="border-radius: 8px;"></div>
                        
                        <!-- Step 1: Select Type -->
                        <div id="step1">
                            <h6 class="fw-bold mb-3 text-secondary">Who are you adding for <span id="addRelativeBaseName" class="text-primary"></span>?</h6>
                            
                            <div class="d-grid gap-3">
                                <button type="button" class="btn btn-outline-primary text-start p-3" onclick="selectRelativeType('partner')" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-heart-fill fs-3 me-3"></i>
                                        <div>
                                            <div class="fw-bold fs-5">Partner / Spouse</div>
                                            <div class="text-muted small">Current spouse, ex-partner, or co-parent.</div>
                                        </div>
                                    </div>
                                </button>
                                
                                <button type="button" class="btn btn-outline-success text-start p-3" onclick="selectRelativeType('child')" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-emoji-smile-fill fs-3 me-3"></i>
                                        <div>
                                            <div class="fw-bold fs-5">Child</div>
                                            <div class="text-muted small">Son or daughter (requires selecting a union).</div>
                                        </div>
                                    </div>
                                </button>
                                
                                <button type="button" class="btn btn-outline-info text-start p-3" onclick="selectRelativeType('sibling')" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people-fill fs-3 me-3"></i>
                                        <div>
                                            <div class="fw-bold fs-5">Sibling</div>
                                            <div class="text-muted small">Brother or sister (requires selecting their parents).</div>
                                        </div>
                                    </div>
                                </button>
                                
                                <button type="button" class="btn btn-outline-secondary text-start p-3" onclick="selectRelativeType('parents')" style="border-radius: 12px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-hearts fs-3 me-3"></i>
                                        <div>
                                            <div class="fw-bold fs-5">Parents</div>
                                            <div class="text-muted small">Father and/or Mother.</div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Partner Form -->
                        <div id="step2-partner" class="d-none">
                            <form id="addPartnerForm">
                                <input type="hidden" name="base_node_id" id="partnerBaseNodeId">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Relationship Status</label>
                                    <select name="is_current" id="partnerStatus" class="form-select" onchange="toggleDivorceYear()" required>
                                        <option value="yes">Current Partner / Married</option>
                                        <option value="no">Divorced / Past Union</option>
                                    </select>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="Jane">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Doe">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Female">Female</option>
                                        <option value="Male">Male</option>
                                    </select>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email (Optional)</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email for auto-link">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mobile (Optional)</label>
                                        <input type="text" name="mobile" class="form-control" placeholder="Phone number">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Marriage/Start Year</label>
                                        <input type="number" name="marriage_year" class="form-control" placeholder="e.g. 2010" min="1900" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-6" id="divorceYearWrapper" style="display: none;">
                                        <label class="form-label fw-bold">Divorce/End Year</label>
                                        <input type="number" name="divorce_year" class="form-control" placeholder="e.g. 2020" min="1900" max="{{ date('Y') }}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                                    <button type="submit" class="btn btn-primary fw-bold" id="submitPartnerBtn">Save Partner</button>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Child Form -->
                        <div id="step2-child" class="d-none">
                            <form id="addChildForm">
                                <input type="hidden" name="base_node_id" id="childBaseNodeId">
                                
                                <div class="alert alert-warning small py-2">
                                    To add a child, you must select which partnership/union they belong to.
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Select Union <span class="text-danger">*</span></label>
                                    <select name="union_id" id="childUnionSelect" class="form-select" required>
                                        <option value="">Loading unions...</option>
                                    </select>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" required placeholder="John">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Doe">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email (Optional)</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email for auto-link">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mobile (Optional)</label>
                                        <input type="text" name="mobile" class="form-control" placeholder="Phone number">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                                    <button type="submit" class="btn btn-success fw-bold" id="submitChildBtn">Save Child</button>
                                </div>
                            </form>
                        </div>

                        <!-- Step 2: Parents Form -->
                        <div id="step2-parents" class="d-none">
                            <form id="addParentsForm">
                                <input type="hidden" name="base_node_id" id="parentsBaseNodeId">
                                
                                <div class="alert alert-info small py-2">
                                    Provide details for one or both parents. Leave blank if unknown.
                                </div>
                                
                                <!-- Father Section -->
                                <h6 class="fw-bold text-primary mt-3 border-bottom pb-2">Father's Details</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">First Name</label>
                                        <input type="text" name="father_first_name" class="form-control form-control-sm" placeholder="John">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Last Name</label>
                                        <input type="text" name="father_last_name" class="form-control form-control-sm" placeholder="Doe">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Email (Optional)</label>
                                        <input type="email" name="father_email" class="form-control form-control-sm" placeholder="Email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Mobile (Optional)</label>
                                        <input type="text" name="father_mobile" class="form-control form-control-sm" placeholder="Mobile">
                                    </div>
                                </div>

                                <!-- Mother Section -->
                                <h6 class="fw-bold text-primary mt-4 border-bottom pb-2">Mother's Details</h6>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">First Name</label>
                                        <input type="text" name="mother_first_name" class="form-control form-control-sm" placeholder="Jane">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Last Name</label>
                                        <input type="text" name="mother_last_name" class="form-control form-control-sm" placeholder="Doe">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Email (Optional)</label>
                                        <input type="email" name="mother_email" class="form-control form-control-sm" placeholder="Email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Mobile (Optional)</label>
                                        <input type="text" name="mother_mobile" class="form-control form-control-sm" placeholder="Mobile">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                                    <button type="submit" class="btn btn-secondary fw-bold" id="submitParentsBtn">Save Parents</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://balkan.app/js/FamilyTree.js"></script>
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
             * Custom node design: large circular portrait sitting on top of
             * a rounded, gender-coloured card (role label + name).
             * ------------------------------------------------------------ */
            const NODE_W = 230;
            const NODE_H = 232;
            const CX = NODE_W / 2;

            function styleFamilyNode(templateName, cardFill) {
                // Clone so late overrides don't leak between male/female/base templates
                FamilyTree.templates[templateName] = Object.assign({}, FamilyTree.templates[templateName]);
                const t = FamilyTree.templates[templateName];

                t.size = [NODE_W, NODE_H];

                t.defs = '<filter id="ftNodeShadow" x="-40%" y="-40%" width="180%" height="180%">'
                    + '<feDropShadow dx="0" dy="6" stdDeviation="7" flood-color="#0f172a" flood-opacity="0.22"></feDropShadow>'
                    + '</filter>';

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

            styleFamilyNode('tommy', '#64748b');
            styleFamilyNode('tommy_male', '#2563eb');
            styleFamilyNode('tommy_female', '#e8630c');

            var family = new FamilyTree(document.getElementById("tree"), {
                template: "tommy",
                mode: "light",
                enableSearch: false,
                mouseScrool: FamilyTree.action.zoom,
                nodeBinding: {
                    field_0: "nameL1",
                    field_1: "nameL2",
                    field_2: "title",
                    img_0: "img"
                },
                nodes: familyTreeNodes
            });
            
            // Intercept clicks to trigger the custom Bootstrap modal flow
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
    });

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
            if (data.status === 200) {
                // Success - reload page to show new tree
                window.location.reload();
            } else {
                errorDiv.textContent = data.message || 'An error occurred.';
                errorDiv.classList.remove('d-none');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            errorDiv.textContent = 'A network error occurred.';
            errorDiv.classList.remove('d-none');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection
