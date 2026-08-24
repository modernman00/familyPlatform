@extends ('layouts.profileBase')
@section('title', 'Family Tree & Organogram')
@section('data-page-id', 'organogram')
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css">
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

                <!-- Search & Quick Find -->
                <div class="header-actions">
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
                <a href="/accountSetting" class="btn btn-primary btn-sm px-3 py-2 fw-bold" style="border-radius: 8px; white-space: nowrap;">Add Family Members</a>
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

            <!-- Dynamic Graph Canvas / Wrapper -->
            <div class="tree-wrapper" id="treeWrapper">
                <div class="tree" id="familyTree">
                    <ul>
                        <li>
                            <!-- Generation: Ancestors / Parents -->
                            <div class="couple-wrapper has-children">
                                @include('member.includes.treeNode', ['type' => 'Father', 'dataDB' => $orgData['father']])
                                @include('member.includes.treeNode', ['type' => 'Mother', 'dataDB' => $orgData['mother']])
                            </div>

                            <ul>
                                <li>
                                    @php
                                        $hasChildren = isset($orgData['children']) && count($orgData['children']) > 0;
                                    @endphp
                                    <div class="couple-wrapper {{ $hasChildren ? 'has-children' : '' }}">
                                        @include('member.includes.treeNode', ['type' => 'Me', 'dataDB' => $data])

                                        @if(!empty($orgData['spouse']['fullName']) || !empty($orgData['spouse']['name']))
                                            @include('member.includes.treeNode', [
                                                'type' => 'spouse',
                                                'dataDB' => $orgData['spouse']
                                            ])
                                        @endif
                                    </div>

                                    @if (isset($orgData['children']) && is_array($orgData['children']) && count($orgData['children']) > 0)
                                    <ul>
                                        <li>
                                            @foreach ($orgData['children'] as $child)
                                                @include('member.includes.treeNode', ['type' => 'child', 'dataDB' => $child])
                                            @endforeach
                                        </li>
                                    </ul>
                                    @endif
                                </li>

                                @isset($orgData['siblings'])
                                    @foreach ($orgData['siblings'] as $sibling)
                                    <li>
                                        @isset($sibling['fullName'])
                                            @include('member.includes.treeNode', ['type' => 'sibling', 'dataDB' => $sibling])
                                        
                                            @php
                                                $siblingId = $sibling['id'] ?? null;
                                            @endphp

                                            @if ($siblingId && isset($orgData['sibling_children']))
                                                @foreach ($orgData['sibling_children'] as $child)
                                                    @if (isset($child['father_id']) && $child['father_id'] === $siblingId)
                                                    <ul>
                                                        <li>
                                                            @include('member.includes.treeNode', ['type' => $child['relationship'], 'dataDB' => $child])
                                                        </li>
                                                    </ul>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endisset
                                    </li>
                                    @endforeach
                                @endisset
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Floating Zoom Controls -->
            <div class="zoom-controls">
                <button class="zoom-btn" id="zoomIn" title="Zoom In"><i class="bi bi-plus-lg"></i></button>
                <button class="zoom-btn" id="zoomOut" title="Zoom Out"><i class="bi bi-dash-lg"></i></button>
                <button class="zoom-btn" id="resetZoom" title="Reset View"><i class="bi bi-arrow-counterclockwise"></i></button>
            </div>
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

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-light fw-bold" onclick="backToStep1()">Back</button>
                                    <button type="submit" class="btn btn-success fw-bold" id="submitChildBtn">Save Child</button>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Modal Logic
    let currentBaseNodeId = null;
    let currentBaseNodeName = '';

    document.getElementById('openAddRelativeModalBtn')?.addEventListener('click', function() {
        const nameElem = document.querySelector('#personModal .person-name');
        currentBaseNodeName = nameElem ? nameElem.textContent : 'Relative';
        
        const activeNode = document.querySelector('.tree-node.active-clicked') || document.querySelector('.tree-node:hover');
        let legacyId = activeNode ? (activeNode.dataset.personid || window.__ROOT_USER_ID__) : window.__ROOT_USER_ID__;

        currentBaseNodeId = getGraphNodeId(legacyId, currentBaseNodeName);

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
            // We need to fetch unions for this node to populate the select box
            fetchUnionsForNode(currentBaseNodeId);
        }
    }

    function backToStep1() {
        document.getElementById('step2-partner').classList.add('d-none');
        document.getElementById('step2-child').classList.add('d-none');
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
        
        // We'll use the getNodeDetails API
        fetch(`/member/organogram/node/${nodeId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 200 && data.data && data.data.unions) {
                    const unions = data.data.unions;
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

    // Form Submissions
    document.getElementById('addPartnerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/partner', 'submitPartnerBtn', 'Saving...');
    });

    document.getElementById('addChildForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, '/member/organogram/editor/child', 'submitChildBtn', 'Saving...');
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
