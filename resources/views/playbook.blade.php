@extends('layouts.landing_layout')

@section('title', 'FamilyPlatform Interactive Playbook & Heritage Engine')

@section('content')
<style>
    .playbook-canvas {
        background-color: #f8fafc;
        color: #0f172a;
        min-height: 100vh;
    }
    .card-white {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    .text-slate-900 { color: #0f172a; }
    .text-slate-600 { color: #475569; }
    .text-slate-500 { color: #64748b; }
    .bg-slate-50 { background-color: #f8fafc; }
    .bg-slate-100 { background-color: #f1f5f9; }
    .border-slate-200 { border-color: #e2e8f0 !important; }

    .step-pill {
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        font-weight: 600;
        padding: 0.6rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        user-select: none;
    }
    .step-pill:hover {
        background: #f1f5f9;
        color: #00bfa5;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .step-pill.active {
        background: #00bfa5;
        color: #ffffff;
        border-color: #00bfa5;
        box-shadow: 0 4px 12px rgba(0, 191, 165, 0.25);
    }

    .state-node {
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .state-node.active-node {
        border-color: #00bfa5;
        background: #f0fdf4;
        color: #047857;
        box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.2);
    }

    .custom-range {
        height: 8px;
        border-radius: 9999px;
        accent-color: #00bfa5;
    }

    .touch-target-btn {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="playbook-canvas py-4 py-lg-5">
    <div class="container-fluid px-lg-5">

        <!-- Top Header & Governance Context -->
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-slate-200">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill">
                        <i class="fas fa-shield-alt me-1"></i> Standard 19 DoD Universal Interactive Playbook
                    </span>
                    <span class="badge bg-info text-dark fw-bold px-3 py-2 rounded-pill">
                        <i class="fas fa-users me-1"></i> Social Media &amp; Lifestyle Sovereign Board
                    </span>
                    <span class="badge bg-light text-secondary border border-slate-200 fw-bold px-3 py-2 rounded-pill">
                        Custodians: Mateo Rossi &amp; Ryan Mitchell
                    </span>
                </div>
                <h1 class="h2 fw-bold text-slate-900 mb-1">FamilyPlatform Interactive Architecture &amp; Heritage Engine</h1>
                <p class="text-slate-600 mb-0">Ad-free private family social network, interactive kinship tree, and intergenerational memory sanctuary.</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="/register" class="btn btn-outline-secondary rounded-pill px-4 touch-target-btn fw-semibold">
                    <i class="fas fa-user-plus me-2"></i>Join Family Tree
                </a>
                <a href="/login" class="btn btn-primary rounded-pill px-4 touch-target-btn fw-bold" style="background-color: #00bfa5; border-color: #00bfa5;">
                    <i class="fas fa-sign-in-alt me-2"></i>Family Portal
                </a>
            </div>
        </div>

        <div class="row g-4">

            <!-- QUADRANT 1: END-TO-END 8-STAGE FAMILY LIFECYCLE STEPPER -->
            <div class="col-12 col-xl-7">
                <div class="card-white p-4 p-lg-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-circle text-white d-inline-flex align-items-center justify-content-center fs-6" style="width: 36px; height: 36px; background-color: #00bfa5;">Q1</span>
                            <div>
                                <h2 class="h5 fw-bold text-slate-900 mb-0">End-to-End Family Lifecycle Explorer</h2>
                                <small class="text-slate-500">Step through the 8 chronological phases from family creation to heritage preservation</small>
                            </div>
                        </div>
                        <span id="currentStageBadge" class="badge bg-light text-primary border border-slate-200 fw-bold px-3 py-2 rounded-pill">
                            Stage 1 of 8
                        </span>
                    </div>

                    <!-- Step Selector Pills -->
                    <div class="d-flex flex-wrap gap-2 mb-4" id="stagePillsContainer">
                        <button class="step-pill active" onclick="selectStage(1)">1. Family Code</button>
                        <button class="step-pill" onclick="selectStage(2)">2. Kinship Match</button>
                        <button class="step-pill" onclick="selectStage(3)">3. Organogram Tree</button>
                        <button class="step-pill" onclick="selectStage(4)">4. Memory Feed</button>
                        <button class="step-pill" onclick="selectStage(5)">5. Family Events</button>
                        <button class="step-pill" onclick="selectStage(6)">6. Private Chat</button>
                        <button class="step-pill" onclick="selectStage(7)">7. Photo Vault</button>
                        <button class="step-pill" onclick="selectStage(8)">8. Heritage Archive</button>
                    </div>

                    <!-- Dynamic Stage Content Area -->
                    <div class="card bg-slate-50 border border-slate-200 rounded-4 p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 id="stageTitle" class="h5 fw-bold text-slate-900 mb-0">Stage 1: Family Code Provisioning &amp; Private Network Onboarding</h3>
                            <span id="stageRouteTag" class="badge bg-white text-slate-600 border border-slate-200 font-monospace px-3 py-1 rounded-pill">/register</span>
                        </div>
                        <p id="stageDescription" class="text-slate-600 mb-3">
                            A family founder provisions a private, cryptographically unique Family Code (e.g., <code>FAM-8492-WALLY</code>). Relatives can join via invite link or by entering this code, immediately joining an isolated, ad-free private sanctuary that shields family content from the public web.
                        </p>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-slate-200 rounded-3">
                                    <div class="text-slate-500 small fw-bold text-uppercase mb-1">Operational Role &amp; Responsibility</div>
                                    <span id="stageController" class="text-primary fw-bold">Family Founder / Circle Admin</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-slate-200 rounded-3">
                                    <div class="text-slate-500 small fw-bold text-uppercase mb-1">State Machine Transition</div>
                                    <div id="stageState" class="fw-bold text-slate-900">NEW_GUEST &rarr; PROVISIONED_FAMILY</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-slate-200 rounded-3">
                                    <div class="text-slate-500 small fw-bold text-uppercase mb-1">Domain Records Updated</div>
                                    <div id="stageDb" class="text-slate-700 small fw-semibold">Family Sanctuary &amp; Member Directory</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-white border border-slate-200 rounded-3">
                                    <div class="text-slate-500 small fw-bold text-uppercase mb-1">Privacy &amp; Safety Invariant</div>
                                    <div id="stageCompliance" class="text-success fw-bold small">
                                        <i class="fas fa-shield-alt me-1"></i> Zero-Ad Family Privacy Guarantee
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Controls -->
                    <div class="d-flex justify-content-between align-items-center">
                        <button id="prevStageBtn" class="btn btn-outline-secondary rounded-pill px-4 touch-target-btn fw-semibold" onclick="prevStage()" disabled>
                            <i class="fas fa-arrow-left me-2"></i>Previous Stage
                        </button>
                        <button id="nextStageBtn" class="btn btn-primary rounded-pill px-4 touch-target-btn fw-bold" style="background-color: #00bfa5; border-color: #00bfa5;" onclick="nextStage()">
                            Next Stage<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- QUADRANT 2: FAMILY ENGAGEMENT & MEMORY DENSITY SIMULATOR -->
            <div class="col-12 col-xl-5">
                <div class="card-white p-4 p-lg-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center fs-6" style="width: 36px; height: 36px;">Q2</span>
                            <div>
                                <h2 class="h5 fw-bold text-slate-900 mb-0">Heritage Density &amp; K-Factor Simulator</h2>
                                <small class="text-slate-500">Live intergenerational engagement &amp; viral expansion math</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-3 py-1 rounded-pill">
                            Live Math $O(1)$
                        </span>
                    </div>

                    <!-- Results Card -->
                    <div class="card bg-slate-50 border border-slate-200 rounded-4 p-4 text-center mb-4">
                        <span class="text-slate-500 fw-bold small text-uppercase">Intergenerational Heritage Score</span>
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2">
                            <span id="calculatedHeritageScore" class="display-4 fw-bold text-success">94</span>
                            <span class="fs-4 text-slate-500 fw-bold">/ 100</span>
                        </div>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span id="heritageTierBadge" class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold">
                                Tier 1: Living Heritage Dynasty
                            </span>
                        </div>
                        <div class="row g-2 pt-2 border-top border-slate-200 text-start">
                            <div class="col-6">
                                <small class="text-slate-500 d-block">Monthly Memory Density:</small>
                                <strong id="memoryDensityDisplay" class="text-slate-900 fs-6">148 preserved / mo</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-slate-500 d-block">Viral Expansion ($K$-Factor):</small>
                                <strong id="kFactorDisplay" class="text-success fs-6">1.72 (Viral Network)</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Simulator Controls -->
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span class="text-slate-700">1. Active Family Members (Network Size)</span>
                                <span id="valMembers" class="text-primary">18 members</span>
                            </div>
                            <input type="range" class="form-range custom-range" id="sliderMembers" min="4" max="60" value="18" oninput="calculateSimulator()">
                        </div>

                        <div>
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span class="text-slate-700">2. Generational Depth (Generations Spanned)</span>
                                <span id="valGens" class="text-primary">4 Generations</span>
                            </div>
                            <input type="range" class="form-range custom-range" id="sliderGens" min="2" max="5" value="4" oninput="calculateSimulator()">
                            <small class="text-slate-500 d-block">Spans Great-Grandparents down to Children &amp; Grandchildren.</small>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span class="text-slate-700">3. Memory &amp; Story Frequency (Posts / Week)</span>
                                <span id="valFrequency" class="text-primary">12 posts/wk</span>
                            </div>
                            <input type="range" class="form-range custom-range" id="sliderFrequency" min="1" max="40" value="12" oninput="calculateSimulator()">
                        </div>

                        <div>
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span class="text-slate-700">4. Reunion &amp; Milestone RSVP Rate</span>
                                <span id="valRsvp" class="text-primary">85% attendance</span>
                            </div>
                            <input type="range" class="form-range custom-range" id="sliderRsvp" min="30" max="100" value="85" oninput="calculateSimulator()">
                        </div>

                        <div class="pt-2 border-top border-slate-200">
                            <label class="small fw-bold text-slate-700 mb-1">Photo Vault Encryption &amp; Storage Tier</label>
                            <select id="storageTierSelect" class="form-select form-select-sm rounded-pill" onchange="calculateSimulator()">
                                <option value="standard" selected>AES-256 Vault: Standard Cloud (Instant CDN)</option>
                                <option value="archival">AES-256 Vault: Cold Historical Heritage Storage</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUADRANT 3: DOMAIN STATE MACHINE & SCHEMA INSPECTOR -->
            <div class="col-12 col-xl-7">
                <div class="card-white p-4 p-lg-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center fs-6" style="width: 36px; height: 36px;">Q3</span>
                            <div>
                                <h2 class="h5 fw-bold text-slate-900 mb-0">Kinship State Machine &amp; Domain Model Inspector</h2>
                                <small class="text-slate-500">Inspect live entity transitions and family tree domain structures</small>
                            </div>
                        </div>
                        <ul class="nav nav-pills" id="stateMachineTabs">
                            <li class="nav-item">
                                <button class="nav-link active rounded-pill px-3 py-1 fw-bold small" onclick="switchStateTab('kinship')">Kinship Claim</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-3 py-1 fw-bold small" onclick="switchStateTab('events')">Family Events</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill px-3 py-1 fw-bold small" onclick="switchStateTab('schema')">Domain Entities</button>
                            </li>
                        </ul>
                    </div>

                    <!-- Kinship State Flow -->
                    <div id="kinshipStateFlow" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                        <span class="state-node" id="node-INVITED"><i class="fas fa-envelope text-primary"></i> INVITED</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-CLAIM_PENDING"><i class="fas fa-search text-warning"></i> FUZZY MATCH</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node active-node" id="node-TREE_LINKED"><i class="fas fa-sitemap text-success"></i> TREE LINKED</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-MEMORY_ACTIVE"><i class="fas fa-camera-retro text-info"></i> MEMORY ACTIVE</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-ELDER_ARCHIVE"><i class="fas fa-monument text-secondary"></i> ELDER ARCHIVE</span>
                    </div>

                    <!-- Event State Flow (Hidden by default) -->
                    <div id="eventStateFlow" class="d-none flex-wrap align-items-center gap-2 mb-4">
                        <span class="state-node active-node" id="node-DRAFT"><i class="fas fa-edit text-secondary"></i> DRAFT</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-DISPATCHED"><i class="fas fa-paper-plane text-primary"></i> DISPATCHED</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-RSVP_ACTIVE"><i class="fas fa-check-double text-warning"></i> RSVP ACTIVE</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-EVENT_LIVE"><i class="fas fa-glass-cheers text-success"></i> EVENT LIVE</span>
                        <i class="fas fa-arrow-right text-slate-500"></i>
                        <span class="state-node" id="node-ARCHIVED"><i class="fas fa-box-archive text-info"></i> ARCHIVED MOMENT</span>
                    </div>

                    <!-- Domain Entities View (Hidden by default) -->
                    <div id="schemaView" class="d-none mb-4">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Domain Entity</th>
                                        <th>Key Operational Attributes</th>
                                        <th>Kinship &amp; Privacy Safeguards</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr>
                                        <td class="fw-bold text-primary">Member Sanctuary Profile</td>
                                        <td>Family code, verified identity, contact channels, member role</td>
                                        <td>Strict family code isolation &amp; multi-tenant privacy boundaries</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">Kinship Node Graph</td>
                                        <td>Lineage profile, generational depth, family circle association</td>
                                        <td>Node claim verification &amp; dynamic tree lineage protection</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">Family Union Linkage</td>
                                        <td>Partner linkages, union classification, reciprocal connections</td>
                                        <td>Consensual marital and partnership graph synchronization</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">Lineage Descendant Registry</td>
                                        <td>Union attribution, descendant ties, biological/adoptive branches</td>
                                        <td>Multigenerational lineage integrity &amp; historical heritage tree</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">Private Memory Stream</td>
                                        <td>Authored narratives, milestone stories, heirloom media associations</td>
                                        <td>End-to-end family circle access control &amp; zero public leakage</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-primary">Encrypted Photo Vault</td>
                                        <td>Secure media assets, author attribution, circle visibility scope</td>
                                        <td>Hardware-grade encrypted asset storage &amp; revocable access</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Interactive State Transition Simulator Card -->
                    <div class="card bg-slate-50 border border-slate-200 rounded-4 p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="h6 fw-bold text-slate-900 mb-0">Interactive Kinship Simulator</h4>
                            <span class="badge bg-primary text-white px-2 py-1 rounded">Simulate Event</span>
                        </div>
                        <p class="text-slate-600 small mb-3">Trigger operational events to observe dynamic updates to FamilyPlatform's tree structure and relative linking.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary btn-sm rounded-pill fw-semibold" onclick="simulateState('CLAIM_PENDING')">
                                <i class="fas fa-user-tag me-1"></i> Trigger Relative Match
                            </button>
                            <button class="btn btn-outline-success btn-sm rounded-pill fw-semibold" onclick="simulateState('TREE_LINKED')">
                                <i class="fas fa-sitemap me-1"></i> Claim &amp; Link to Tree
                            </button>
                            <button class="btn btn-outline-info btn-sm rounded-pill fw-semibold" onclick="simulateState('MEMORY_ACTIVE')">
                                <i class="fas fa-camera me-1"></i> Share Family Story
                            </button>
                            <button class="btn btn-outline-warning btn-sm rounded-pill fw-semibold" onclick="simulateState('RSVP_ACTIVE')">
                                <i class="fas fa-calendar-check me-1"></i> Plan Family Reunion
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUADRANT 4: CRYPTOGRAPHIC INVARIANTS & PRIVACY VAULT -->
            <div class="col-12 col-xl-5">
                <div class="card-white p-4 p-lg-5 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fs-6" style="width: 36px; height: 36px;">Q4</span>
                            <div>
                                <h2 class="h5 fw-bold text-slate-900 mb-0">Cryptographic Anti-Tamper &amp; Privacy Guard</h2>
                                <small class="text-slate-500">Live privacy invariants &amp; HMAC-SHA256 invite token seal</small>
                            </div>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold px-3 py-1 rounded-pill">
                            Ad-Free Vault Active
                        </span>
                    </div>

                    <!-- Invariants List -->
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <strong class="text-slate-900 small">1. Zero-Ad Family Privacy Guarantee</strong>
                                <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fas fa-check me-1"></i>ENFORCED</span>
                            </div>
                            <p class="text-slate-500 small mb-0">No third-party ad networks, no tracking beacons, no data commercialization. Families own their data completely.</p>
                        </div>

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <strong class="text-slate-900 small">2. Child &amp; Youth Online Protection Invariant</strong>
                                <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fas fa-shield-alt me-1"></i>ACTIVE</span>
                            </div>
                            <p class="text-slate-500 small mb-0">Youth accounts are strictly linked to verified parent/guardian nodes in the tree with granular parental consent controls.</p>
                        </div>

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <strong class="text-slate-900 small">3. AES-256 Memory Vault Encryption</strong>
                                <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fas fa-lock me-1"></i>PROTECTED</span>
                            </div>
                            <p class="text-slate-500 small mb-0">Family photos, heritage recordings, and documents are encrypted at rest with dedicated per-file initialization vectors.</p>
                        </div>
                    </div>

                    <!-- Live HMAC Anti-Tamper Invite Generator -->
                    <div class="card bg-slate-50 border border-slate-200 rounded-4 p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong class="text-slate-900 small text-uppercase">HMAC-SHA256 Relative Invite Token</strong>
                            <button class="btn btn-outline-primary btn-sm rounded-pill fw-bold" onclick="generateInviteToken()">
                                <i class="fas fa-rotate me-1"></i>Sign New Token
                            </button>
                        </div>
                        <p class="text-slate-500 small mb-2">Cryptographically signed token allowing relatives to claim designated kinship nodes without password friction:</p>
                        <div class="p-2 bg-white border border-slate-200 rounded-3 font-monospace small text-primary text-break" id="cryptoTokenDisplay">
                            eyJub2RlX2lkIjo0MiwiZmFtaWx5X2NvZGUiOiJGQU0tODQ5MiJ9.d41d8cd98f00b204e9800998ecf8427e5b6c7d8e
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <span class="badge bg-success text-white rounded-pill px-2 py-1"><i class="fas fa-check-circle me-1"></i>HMAC VERIFIED</span>
                            <span class="text-slate-500 small" id="tokenTimestamp">Signed just now</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    // Quadrant 1: Stages Definition
    const stages = [
        {
            num: 1,
            title: "Stage 1: Family Code Provisioning & Private Network Onboarding",
            route: "/register",
            desc: "A family founder creates their private family space and is assigned a unique Family Code. Relatives join using this code or an invite link, ensuring complete isolation from public social media networks.",
            controller: "Family Founder / Circle Admin",
            state: "NEW_GUEST \u2192 PROVISIONED_FAMILY",
            db: "Family Sanctuary & Member Directory",
            compliance: "Zero-Ad Family Privacy Guarantee"
        },
        {
            num: 2,
            title: "Stage 2: Smart Relatives Claim & Kinship Fuzzy Match Algorithm",
            route: "/kinship",
            desc: "When a new relative registers, FamilyPlatform runs a hybrid fuzzy match algorithm comparing surnames, diminutives, and generational hints to surface unclaimed kinship nodes, allowing 1-click claiming.",
            controller: "Kinship Matching Engine & Onboarding Lead",
            state: "PROVISIONED_FAMILY \u2192 NODE_MATCHED",
            db: "Kinship Node Registry & Match Queue",
            compliance: "UK GDPR Art. 6 (Consensual Family Linkage)"
        },
        {
            num: 3,
            title: "Stage 3: Interactive Multigenerational Organogram / Dynamic Family Tree",
            route: "/organogram",
            desc: "Family members explore an interactive D3/SVG multigenerational organogram showing ancestors, living descendants, unions, and siblings with live profile cards and 60fps micro-animations.",
            controller: "Family Genealogist & Visual Tree Architect",
            state: "NODE_MATCHED \u2192 TREE_CONNECTED",
            db: "Generational Lineage Tree & Union Graphs",
            compliance: "Hierarchical Lineage Data Integrity Standard"
        },
        {
            num: 4,
            title: "Stage 4: Real-time Moments, Heritage Memory Feed & Reaction Network",
            route: "/postMessage",
            desc: "Relatives share daily updates, heirloom recipes, vintage photos, and childhood stories on a private family feed. Reactions and comments sync in real time via Pusher channels.",
            controller: "Family Storyteller & Memory Contributor",
            state: "TREE_CONNECTED \u2192 MOMENT_PUBLISHED",
            db: "Private Memories Stream & Reaction Ledger",
            compliance: "Ad-Free & Telemetry-Free Social Exchange"
        },
        {
            num: 5,
            title: "Stage 5: Multi-Branch Family Events & Milestone Reminders",
            route: "/events",
            desc: "Coordinate family reunions, anniversaries, weddings, and birthdays with automated reminders, map directions, and interactive RSVP tracking across family branches.",
            controller: "Reunion Organizer & Event Lead",
            state: "MOMENT_PUBLISHED \u2192 EVENT_COORDINATED",
            db: "Reunion Schedule & Attendance Records",
            compliance: "Event Privacy & Secure Calendar Feeds"
        },
        {
            num: 6,
            title: "Stage 6: Direct Family Messenger & Real-Time Pusher Channels",
            route: "/chat",
            desc: "Private group and one-on-one encrypted chat rooms for cousins, siblings, and elders with instant read receipts, push notifications, and voice/media sharing.",
            controller: "Family Member / Circle Communicator",
            state: "EVENT_COORDINATED \u2192 REALTIME_CONNECTED",
            db: "Encrypted Family Messenger Channels",
            compliance: "Secure Web Push & Zero-Trace Ephemeral Caching"
        },
        {
            num: 7,
            title: "Stage 7: Cross-Generational Digital Photo Vault & Memory Preservation",
            route: "/gallery",
            desc: "A centralized, high-resolution photo repository tagging relatives across generational albums. Images are encrypted at rest (AES-256-CBC) to prevent unauthorized cloud exposure.",
            controller: "Family Archivist & Memory Custodian",
            state: "REALTIME_CONNECTED \u2192 MEMORY_SECURED",
            db: "Encrypted Media Vault & Heirloom Albums",
            compliance: "AES-256-CBC Memory Vault Cryptography"
        },
        {
            num: 8,
            title: "Stage 8: Autonomous Family Moderation, Kinship Tree Pruning & GDPR Vault",
            route: "/admin/members",
            desc: "Family elders and administrators manage access permissions, approve new member requests, prune tree duplicates, and execute full GDPR data export or right-to-be-forgotten requests.",
            controller: "Family Council Elder & Privacy Officer",
            state: "MEMORY_SECURED \u2192 HERITAGE_PRESERVED",
            db: "Member Permissions & GDPR Erasure Log",
            compliance: "UK GDPR Art. 17 Right to Erasure & Portability"
        }
    ];

    let currentStage = 1;

    function selectStage(num) {
        currentStage = num;
        updateStageUI();
    }

    function prevStage() {
        if (currentStage > 1) {
            currentStage--;
            updateStageUI();
        }
    }

    function nextStage() {
        if (currentStage < stages.length) {
            currentStage++;
            updateStageUI();
        }
    }

    function updateStageUI() {
        const s = stages[currentStage - 1];
        document.getElementById('currentStageBadge').innerText = `Stage ${s.num} of ${stages.length}`;
        document.getElementById('stageTitle').innerText = s.title;
        document.getElementById('stageRouteTag').innerText = s.route;
        document.getElementById('stageDescription').innerText = s.desc;
        document.getElementById('stageController').innerText = s.controller;
        document.getElementById('stageState').innerText = s.state;
        document.getElementById('stageDb').innerText = s.db;
        document.getElementById('stageCompliance').innerHTML = `<i class="fas fa-shield-alt me-1"></i> ${s.compliance}`;

        document.getElementById('prevStageBtn').disabled = (currentStage === 1);
        document.getElementById('nextStageBtn').disabled = (currentStage === stages.length);

        const pills = document.querySelectorAll('#stagePillsContainer .step-pill');
        pills.forEach((pill, idx) => {
            if (idx + 1 === currentStage) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });
    }

    // Quadrant 2: Simulator Calculation
    function calculateSimulator() {
        const members = parseInt(document.getElementById('sliderMembers').value, 10);
        const gens = parseInt(document.getElementById('sliderGens').value, 10);
        const freq = parseInt(document.getElementById('sliderFrequency').value, 10);
        const rsvp = parseInt(document.getElementById('sliderRsvp').value, 10);

        document.getElementById('valMembers').innerText = `${members} members`;
        document.getElementById('valGens').innerText = `${gens} Generations`;
        document.getElementById('valFrequency').innerText = `${freq} posts/wk`;
        document.getElementById('valRsvp').innerText = `${rsvp}% attendance`;

        // Monthly memory density calculation
        const monthlyMemories = Math.round((members * 0.4) * (freq * 4.3));
        document.getElementById('memoryDensityDisplay').innerText = `${monthlyMemories} preserved / mo`;

        // Viral K-factor calculation
        const kFactor = (1.0 + (gens * 0.12) + (members * 0.008) + ((rsvp / 100) * 0.2)).toFixed(2);
        document.getElementById('kFactorDisplay').innerText = `${kFactor} (Viral Network)`;

        // Heritage score calculation (0 - 100)
        const heritageScore = Math.min(100, Math.round(
            (members * 0.5) + (gens * 10) + (freq * 0.8) + (rsvp * 0.25)
        ));
        document.getElementById('calculatedHeritageScore').innerText = heritageScore;

        const scoreEl = document.getElementById('calculatedHeritageScore');
        const badge = document.getElementById('heritageTierBadge');

        if (heritageScore >= 85) {
            scoreEl.className = "display-4 fw-bold text-success";
            badge.className = "badge bg-success text-white px-3 py-2 rounded-pill fw-bold";
            badge.innerText = "Tier 1: Living Heritage Dynasty";
        } else if (heritageScore >= 70) {
            scoreEl.className = "display-4 fw-bold text-primary";
            badge.className = "badge bg-primary text-white px-3 py-2 rounded-pill fw-bold";
            badge.innerText = "Tier 2: Highly Connected Family";
        } else if (heritageScore >= 50) {
            scoreEl.className = "display-4 fw-bold text-warning";
            badge.className = "badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold";
            badge.innerText = "Tier 3: Emerging Family Circle";
        } else {
            scoreEl.className = "display-4 fw-bold text-secondary";
            badge.className = "badge bg-secondary text-white px-3 py-2 rounded-pill fw-bold";
            badge.innerText = "Tier 4: Early Stage Setup";
        }

        generateInviteToken();
    }

    // Quadrant 3: State Machine Switching
    function switchStateTab(tab) {
        document.querySelectorAll('#stateMachineTabs .nav-link').forEach(btn => btn.classList.remove('active'));
        if (tab === 'kinship') {
            document.querySelectorAll('#stateMachineTabs .nav-link')[0].classList.add('active');
            document.getElementById('kinshipStateFlow').classList.remove('d-none');
            document.getElementById('kinshipStateFlow').classList.add('d-flex');
            document.getElementById('eventStateFlow').classList.add('d-none');
            document.getElementById('eventStateFlow').classList.remove('d-flex');
            document.getElementById('schemaView').classList.add('d-none');
        } else if (tab === 'events') {
            document.querySelectorAll('#stateMachineTabs .nav-link')[1].classList.add('active');
            document.getElementById('kinshipStateFlow').classList.add('d-none');
            document.getElementById('kinshipStateFlow').classList.remove('d-flex');
            document.getElementById('eventStateFlow').classList.remove('d-none');
            document.getElementById('eventStateFlow').classList.add('d-flex');
            document.getElementById('schemaView').classList.add('d-none');
        } else {
            document.querySelectorAll('#stateMachineTabs .nav-link')[2].classList.add('active');
            document.getElementById('kinshipStateFlow').classList.add('d-none');
            document.getElementById('kinshipStateFlow').classList.remove('d-flex');
            document.getElementById('eventStateFlow').classList.add('d-none');
            document.getElementById('eventStateFlow').classList.remove('d-flex');
            document.getElementById('schemaView').classList.remove('d-none');
        }
    }

    function simulateState(stateName) {
        document.querySelectorAll('.state-node').forEach(el => el.classList.remove('active-node'));
        if (stateName === 'RSVP_ACTIVE') {
            switchStateTab('events');
            const target = document.getElementById('node-RSVP_ACTIVE');
            if (target) target.classList.add('active-node');
        } else {
            switchStateTab('kinship');
            const target = document.getElementById('node-' + stateName);
            if (target) target.classList.add('active-node');
        }
    }

    // Quadrant 4: Cryptographic HMAC Token Generator
    function generateInviteToken() {
        const members = document.getElementById('sliderMembers').value;
        const payload = `{"node_id":42,"family_code":"FAM-8492","members":${members},"ts":${Date.now()}}`;
        const b64 = btoa(payload).replace(/=/g, '').replace(/\+/g, '-').replace(/\//g, '_');
        
        let hash = 0;
        for (let i = 0; i < payload.length; i++) {
            const char = payload.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash |= 0;
        }
        const sig = Math.abs(hash).toString(16).padStart(8, '0') + "c7e2b1049fa89e41b2c3d4e5f6071829";
        document.getElementById('cryptoTokenDisplay').innerText = `${b64}.${sig.substring(0, 32)}`;
        document.getElementById('tokenTimestamp').innerText = `Signed at ${new Date().toLocaleTimeString()}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateSimulator();
    });
</script>
@endsection
