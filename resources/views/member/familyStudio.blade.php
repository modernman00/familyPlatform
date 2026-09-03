@extends ('layouts.profileBase')
@section('title', 'Family Lineage Studio')
@section('data-page-id', 'familyStudio')
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css?v={{ time() }}">
    <style>
        /* Google Stitch / Modern Material 3 Elevation & Tokens */
        :root {
            --stitch-surface: #ffffff;
            --stitch-surface-container: #f8fafc;
            --stitch-surface-container-high: #f1f5f9;
            --stitch-primary: #4f46e5;
            --stitch-primary-container: #eef2ff;
            --stitch-on-primary-container: #3730a3;
            --stitch-outline: #e2e8f0;
            --stitch-outline-variant: #cbd5e1;
            --stitch-shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --stitch-shadow-md: 0 4px 16px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -2px rgba(15, 23, 42, 0.04);
            --stitch-shadow-lg: 0 16px 32px -4px rgba(79, 70, 229, 0.12), 0 6px 12px -4px rgba(79, 70, 229, 0.06);
            --stitch-radius-lg: 20px;
            --stitch-radius-md: 14px;
            --stitch-radius-pill: 9999px;
        }

        body {
            background-color: #f8fafc;
        }

        /* Hero Header with Mesh Gradient & Glassmorphism */
        .studio-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 80%, #6366f1 100%);
            color: #ffffff;
            border-radius: var(--stitch-radius-lg);
            padding: 3rem 2.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px -10px rgba(49, 46, 129, 0.35);
            position: relative;
            overflow: hidden;
        }
        .studio-header::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(99, 102, 241, 0.1) 50%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .studio-header::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -10%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(238, 242, 255, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .studio-title-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: var(--stitch-radius-pill);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #ffffff;
        }

        /* Stat Badges */
        .stat-badge-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 16px;
            padding: 1.1rem 1rem;
            text-align: center;
            color: #ffffff;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .stat-badge-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15);
        }
        .stat-badge-val {
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .stat-badge-lbl {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.85;
            margin-top: 4px;
        }

        /* Filter Toolbar */
        .studio-toolbar {
            background: #ffffff;
            border: 1px solid var(--stitch-outline);
            border-radius: var(--stitch-radius-md);
            padding: 0.75rem 1rem;
            margin-bottom: 2rem;
            box-shadow: var(--stitch-shadow-sm);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .filter-nav-pills {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 2px;
        }
        .filter-pill-btn {
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: var(--stitch-radius-pill);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .filter-pill-btn:hover {
            color: #1e293b;
            background: var(--stitch-surface-container-high);
        }
        .filter-pill-btn.active {
            background: var(--stitch-primary);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }
        .search-input-stitch {
            border: 1px solid var(--stitch-outline);
            border-radius: var(--stitch-radius-pill);
            padding: 6px 14px 6px 36px;
            font-size: 0.85rem;
            background: #f8fafc;
            width: 220px;
            transition: all 0.2s;
        }
        .search-input-stitch:focus {
            outline: none;
            border-color: var(--stitch-primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
            width: 260px;
        }

        /* Generational Section Headings */
        .gen-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2.25rem 0 1.25rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 1.5px solid var(--stitch-outline);
        }
        .gen-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: var(--stitch-radius-pill);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge-gen-minus1 { background: #dbeafe; color: #1e40af; }
        .badge-gen-0 { background: #dcfce7; color: #15803d; }
        .badge-gen-plus1 { background: #e0f2fe; color: #0369a1; }

        /* Google Stitch Relative Card */
        .relative-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: var(--stitch-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--stitch-shadow-sm);
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .relative-card:hover {
            transform: translateY(-4px);
            border-color: #c7d2fe;
            box-shadow: var(--stitch-shadow-lg);
        }
        .relative-card.root-card {
            border: 2px solid #818cf8;
            background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
        }

        /* Real-Time Update Highlight Animation */
        @keyframes pulseHighlight {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); transform: scale(1.02); }
            70% { box-shadow: 0 0 0 14px rgba(79, 70, 229, 0); transform: scale(1.01); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); transform: scale(1); }
        }
        .flash-updated {
            animation: pulseHighlight 1.3s cubic-bezier(0.16, 1, 0.3, 1);
            border-color: #4f46e5 !important;
            background: #fdfdff !important;
        }

        .relative-avatar-wrapper {
            position: relative;
            width: 62px;
            height: 62px;
            flex-shrink: 0;
        }
        .relative-avatar {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background: #f1f5f9;
        }
        .avatar-online-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #22c55e;
            border: 2px solid #ffffff;
            border-radius: 50%;
        }

        /* Material 3 Role Chips */
        .role-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: var(--stitch-radius-pill);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 6px;
        }
        .role-chip-parent { background: #eff6ff; color: #1d4ed8; }
        .role-chip-partner { background: #fff1f2; color: #be123c; }
        .role-chip-child { background: #ecfdf5; color: #047857; }
        .role-chip-sibling { background: #fffbeb; color: #b45309; }
        .role-chip-root { background: #eef2ff; color: #4338ca; }

        .relative-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        .relative-subtext {
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.4;
        }

        .card-meta-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.65rem 0;
        }
        .card-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: #475569;
            margin-bottom: 4px;
        }
        .card-meta-item:last-child {
            margin-bottom: 0;
        }
        .card-meta-item i {
            color: #94a3b8;
            font-size: 0.85rem;
            width: 14px;
        }

        /* Google Tonal Action Buttons */
        .btn-tonal-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 0.55rem 1rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--stitch-primary);
            background: var(--stitch-primary-container);
            border: 1px solid transparent;
            border-radius: var(--stitch-radius-md);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }
        .btn-tonal-action:hover {
            background: #e0e7ff;
            color: #3730a3;
            transform: translateY(-1px);
        }

        /* Empty / Add Card Stitch Standard */
        .btn-add-stitch-card {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: var(--stitch-radius-lg);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.25s ease;
            height: 100%;
            min-height: 200px;
            position: relative;
        }
        .btn-add-stitch-card:hover {
            border-color: var(--stitch-primary);
            background: #fcfdff;
            color: var(--stitch-primary);
            transform: translateY(-3px);
            box-shadow: var(--stitch-shadow-md);
        }
        .add-icon-bubble {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 0.85rem;
            transition: all 0.25s ease;
        }
        .btn-add-stitch-card:hover .add-icon-bubble {
            background: var(--stitch-primary-container);
            color: var(--stitch-primary);
            transform: scale(1.1);
        }
        .add-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .add-card-desc {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
<div class="container py-4">

    <!-- Hero Header with Mesh Elevation -->
    <div class="studio-header">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <div class="studio-title-badge mb-3">
                    <i class="bi bi-stars text-warning"></i> Flagship Heritage Studio
                </div>
                <h1 class="display-6 fw-bold text-white mb-2" style="letter-spacing: -0.02em;">Family Lineage Studio</h1>
                <p class="text-white-50 mb-4" style="font-size: 1.05rem; max-width: 520px; line-height: 1.5;">
                    Curate your family dynasty, document generational stories, and manage relatives with complete ease.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/organogram" class="btn btn-light fw-bold text-primary px-4 py-2" style="border-radius: var(--stitch-radius-pill); box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        <i class="bi bi-diagram-3-fill me-2"></i>View Interactive Tree
                    </a>
                    <button type="button" class="btn btn-outline-light fw-bold px-4 py-2" onclick="openStudioAddModal()" style="border-radius: var(--stitch-radius-pill);">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Family Member
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="col-lg-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val" id="statTotalMembers">{{ $stats['total_members'] ?? 0 }}</div>
                            <div class="stat-badge-lbl">Relatives Mapped</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val">{{ $stats['generations_count'] ?? 1 }}</div>
                            <div class="stat-badge-lbl">Generations</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val">{{ $stats['total_unions'] ?? 0 }}</div>
                            <div class="stat-badge-lbl">Marriages & Unions</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-badge-card">
                            <div class="stat-badge-val">{{ $stats['living_count'] ?? 0 }}</div>
                            <div class="stat-badge-lbl">Living Members</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert / Notification Loader -->
    @include('partials.loader', ['notificationId' => 'familyStudio'])

    <!-- Filter & Search Toolbar -->
    <div class="studio-toolbar">
        <div class="filter-nav-pills">
            <button type="button" class="filter-pill-btn active" onclick="filterStudioCards('all')">
                <i class="bi bi-grid-fill me-1"></i> All Relatives ({{ $stats['total_members'] ?? 0 }})
            </button>
            <button type="button" class="filter-pill-btn" onclick="filterStudioCards('gen-minus1')">
                <i class="bi bi-people me-1"></i> Parents & Ancestors
            </button>
            <button type="button" class="filter-pill-btn" onclick="filterStudioCards('gen-0')">
                <i class="bi bi-house-heart me-1"></i> Household & Siblings
            </button>
            <button type="button" class="filter-pill-btn" onclick="filterStudioCards('gen-plus1')">
                <i class="bi bi-emoji-smile me-1"></i> Children & Descendants
            </button>
        </div>

        <div class="position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted ms-3" style="font-size: 0.85rem;"></i>
            <input type="text" id="studioSearchInput" class="search-input-stitch" placeholder="Filter by name..." oninput="searchStudioRelatives(this.value)">
        </div>
    </div>

    <!-- GENERATION -1: Parents & Ancestors -->
    <div class="studio-gen-group" id="group-gen-minus1">
        <div class="gen-section-header">
            <div class="d-flex align-items-center gap-2">
                <span class="gen-badge-pill badge-gen-minus1">Generation -1</span>
                <h3 class="h5 fw-bold mb-0 text-dark">Parents & Ancestors</h3>
            </div>
            <span class="text-muted small fw-medium">{{ count($generations['parents']) }} Recorded</span>
        </div>

        <div class="row g-3 mb-4">
            <!-- Father Card -->
            @php
                $fatherNode = null;
                foreach ($generations['parents'] as $p) {
                    if (($p['gender'] ?? '') === 'Male' || stripos($p['bio'] ?? '', 'Father') !== false) {
                        $fatherNode = $p;
                        break;
                    }
                }
            @endphp
            <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-{{ $fatherNode['id'] ?? 'father' }}" data-name="{{ strtolower(($fatherNode['first_name'] ?? '') . ' ' . ($fatherNode['last_name'] ?? '')) }}">
                @if($fatherNode)
                    <div class="relative-card">
                        <div>
                            <div class="d-flex align-items-start gap-3">
                                <div class="relative-avatar-wrapper">
                                    <img src="{{ $fatherNode['avatar_url'] ?? '/resources/images/profile/avatarM.png' }}" alt="Father" class="relative-avatar">
                                    <span class="avatar-online-dot"></span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <span class="role-chip role-chip-parent">
                                        <i class="bi bi-person-fill"></i> Father
                                    </span>
                                    <h4 class="relative-name text-truncate">{{ ucwords(strtolower($fatherNode['first_name'] . ' ' . $fatherNode['last_name'])) }}</h4>
                                    <div class="relative-subtext text-truncate">{{ $fatherNode['email'] ?: 'No email on record' }}</div>
                                </div>
                            </div>

                            <ul class="card-meta-list">
                                <li class="card-meta-item">
                                    <i class="bi bi-telephone"></i>
                                    <span class="card-meta-mobile">{{ $fatherNode['mobile'] ?: 'Mobile not provided' }}</span>
                                </li>
                                <li class="card-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="card-meta-location">{{ $fatherNode['location'] ?: 'Location not set' }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal({{ $fatherNode['id'] }})">
                                <i class="bi bi-pencil-square"></i> Edit Details
                            </button>
                            <a href="/organogram?highlight={{ $fatherNode['id'] }}" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="View in Tree" style="border-radius: var(--stitch-radius-md);">
                                <i class="bi bi-diagram-3"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('parents')">
                        <div class="add-icon-bubble"><i class="bi bi-plus-lg"></i></div>
                        <div class="add-card-title">Add Father</div>
                        <div class="add-card-desc">Connect your father's lineage to the tree</div>
                    </div>
                @endif
            </div>

            <!-- Mother Card -->
            @php
                $motherNode = null;
                foreach ($generations['parents'] as $p) {
                    if (($p['gender'] ?? '') === 'Female' || stripos($p['bio'] ?? '', 'Mother') !== false) {
                        $motherNode = $p;
                        break;
                    }
                }
            @endphp
            <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-{{ $motherNode['id'] ?? 'mother' }}" data-name="{{ strtolower(($motherNode['first_name'] ?? '') . ' ' . ($motherNode['last_name'] ?? '')) }}">
                @if($motherNode)
                    <div class="relative-card">
                        <div>
                            <div class="d-flex align-items-start gap-3">
                                <div class="relative-avatar-wrapper">
                                    <img src="{{ $motherNode['avatar_url'] ?? '/resources/images/profile/avatarF.png' }}" alt="Mother" class="relative-avatar">
                                    <span class="avatar-online-dot"></span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <span class="role-chip role-chip-parent">
                                        <i class="bi bi-person-fill"></i> Mother
                                    </span>
                                    <h4 class="relative-name text-truncate">{{ ucwords(strtolower($motherNode['first_name'] . ' ' . $motherNode['last_name'])) }}</h4>
                                    <div class="text-primary fw-semibold small card-maiden-name {{ empty($motherNode['maiden_name']) ? 'd-none' : '' }}">
                                        @if(!empty($motherNode['maiden_name']))
                                            née {{ ucwords(strtolower($motherNode['maiden_name'])) }}
                                        @endif
                                    </div>
                                    <div class="relative-subtext text-truncate">{{ $motherNode['email'] ?: 'No email on record' }}</div>
                                </div>
                            </div>

                            <ul class="card-meta-list">
                                <li class="card-meta-item">
                                    <i class="bi bi-telephone"></i>
                                    <span class="card-meta-mobile">{{ $motherNode['mobile'] ?: 'Mobile not provided' }}</span>
                                </li>
                                <li class="card-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="card-meta-location">{{ $motherNode['location'] ?: 'Location not set' }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal({{ $motherNode['id'] }})">
                                <i class="bi bi-pencil-square"></i> Edit Details
                            </button>
                            <a href="/organogram?highlight={{ $motherNode['id'] }}" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="View in Tree" style="border-radius: var(--stitch-radius-md);">
                                <i class="bi bi-diagram-3"></i>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('parents')">
                        <div class="add-icon-bubble"><i class="bi bi-plus-lg"></i></div>
                        <div class="add-card-title">Add Mother</div>
                        <div class="add-card-desc">Add mother & maiden name history</div>
                    </div>
                @endif
            </div>

            <!-- Add Grandparent Card -->
            <div class="col-md-6 col-lg-4 relative-card-container">
                <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('parents')">
                    <div class="add-icon-bubble"><i class="bi bi-diagram-3"></i></div>
                    <div class="add-card-title">Add Grandparent</div>
                    <div class="add-card-desc">Trace lineage back 2+ generations</div>
                </div>
            </div>
        </div>
    </div>

    <!-- GENERATION 0: Immediate Household & Siblings -->
    <div class="studio-gen-group" id="group-gen-0">
        <div class="gen-section-header">
            <div class="d-flex align-items-center gap-2">
                <span class="gen-badge-pill badge-gen-0">Generation 0</span>
                <h3 class="h5 fw-bold mb-0 text-dark">Household, Partners & Siblings</h3>
            </div>
            <span class="text-muted small fw-medium">{{ count($generations['household']) }} Members</span>
        </div>

        <div class="row g-3 mb-4">
            <!-- Root (You) Card -->
            <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-{{ $rootNodeId ?? 1 }}" data-name="{{ strtolower(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? '')) }}">
                <div class="relative-card root-card">
                    <div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="relative-avatar-wrapper">
                                <img src="{{ $data['img'] ?? '/resources/images/profile/avatarM.png' }}" alt="Self" class="relative-avatar" style="border-color: #c7d2fe;">
                                <span class="avatar-online-dot" style="background: #6366f1;"></span>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <span class="role-chip role-chip-root">
                                    <i class="bi bi-star-fill text-warning"></i> You (Root)
                                </span>
                                <h4 class="relative-name text-truncate">{{ ucwords(strtolower(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''))) }}</h4>
                                <div class="relative-subtext text-truncate">{{ $data['email'] ?? '' }}</div>
                            </div>
                        </div>

                        <ul class="card-meta-list">
                            <li class="card-meta-item">
                                <i class="bi bi-telephone"></i>
                                <span class="card-meta-mobile">{{ $data['mobile'] ?: 'Not provided' }}</span>
                            </li>
                            <li class="card-meta-item">
                                <i class="bi bi-briefcase"></i>
                                <span class="card-meta-occupation">{{ $data['occupation'] ?: 'Occupation not set' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal({{ $rootNodeId ?? 1 }})">
                            <i class="bi bi-pencil-square"></i> Edit Details
                        </button>
                        <a href="/accountSetting" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="Account Settings" style="border-radius: var(--stitch-radius-md);">
                            <i class="bi bi-gear-fill"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Partners & Siblings Loop -->
            @foreach($generations['household'] as $member)
                @if(($member['user_id'] ?? null) === ($data['id'] ?? null))
                    @continue
                @endif
                @php
                    $isPartner = stripos($member['bio'] ?? '', 'Partner') !== false || stripos($member['bio'] ?? '', 'Spouse') !== false;
                @endphp
                <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-{{ $member['id'] }}" data-name="{{ strtolower($member['first_name'] . ' ' . $member['last_name']) }}">
                    <div class="relative-card">
                        <div>
                            <div class="d-flex align-items-start gap-3">
                                <div class="relative-avatar-wrapper">
                                    <img src="{{ $member['avatar_url'] ?? '/resources/images/profile/avatarM.png' }}" alt="Relative" class="relative-avatar">
                                    <span class="avatar-online-dot"></span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <span class="role-chip {{ $isPartner ? 'role-chip-partner' : 'role-chip-sibling' }}">
                                        <i class="bi {{ $isPartner ? 'bi-heart-fill' : 'bi-people-fill' }}"></i>
                                        {{ $isPartner ? 'Partner / Spouse' : 'Sibling' }}
                                    </span>
                                    <h4 class="relative-name text-truncate">{{ ucwords(strtolower($member['first_name'] . ' ' . $member['last_name'])) }}</h4>
                                    <div class="text-primary fw-semibold small card-maiden-name {{ empty($member['maiden_name']) ? 'd-none' : '' }}">
                                        @if(!empty($member['maiden_name']))
                                            née {{ ucwords(strtolower($member['maiden_name'])) }}
                                        @endif
                                    </div>
                                    <div class="relative-subtext text-truncate">{{ $member['email'] ?: 'No email on record' }}</div>
                                </div>
                            </div>

                            <ul class="card-meta-list">
                                <li class="card-meta-item">
                                    <i class="bi bi-telephone"></i>
                                    <span class="card-meta-mobile">{{ $member['mobile'] ?: 'Mobile not provided' }}</span>
                                </li>
                                <li class="card-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span class="card-meta-location">{{ $member['location'] ?: 'Location not set' }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal({{ $member['id'] }})">
                                <i class="bi bi-pencil-square"></i> Edit Details
                            </button>
                            <a href="/organogram?highlight={{ $member['id'] }}" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="View in Tree" style="border-radius: var(--stitch-radius-md);">
                                <i class="bi bi-diagram-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Partner Card -->
            <div class="col-md-6 col-lg-4 relative-card-container">
                <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('partner')">
                    <div class="add-icon-bubble"><i class="bi bi-heart-fill text-danger"></i></div>
                    <div class="add-card-title">Add Partner / Spouse</div>
                    <div class="add-card-desc">Connect a marriage or partnership union</div>
                </div>
            </div>

            <!-- Add Sibling Card -->
            <div class="col-md-6 col-lg-4 relative-card-container">
                <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('sibling')">
                    <div class="add-icon-bubble"><i class="bi bi-people-fill text-warning"></i></div>
                    <div class="add-card-title">Add Sibling</div>
                    <div class="add-card-desc">Add brother or sister to your generation</div>
                </div>
            </div>
        </div>
    </div>

    <!-- GENERATION +1: Children & Descendants -->
    <div class="studio-gen-group" id="group-gen-plus1">
        <div class="gen-section-header">
            <div class="d-flex align-items-center gap-2">
                <span class="gen-badge-pill badge-gen-plus1">Generation +1</span>
                <h3 class="h5 fw-bold mb-0 text-dark">Children & Next Generation</h3>
            </div>
            <span class="text-muted small fw-medium">{{ count($generations['children']) }} Children</span>
        </div>

        <div class="row g-3 mb-4">
            @forelse($generations['children'] as $child)
                <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-{{ $child['id'] }}" data-name="{{ strtolower($child['first_name'] . ' ' . $child['last_name']) }}">
                    <div class="relative-card">
                        <div>
                            <div class="d-flex align-items-start gap-3">
                                <div class="relative-avatar-wrapper">
                                    <img src="{{ $child['avatar_url'] ?? '/resources/images/profile/avatarM.png' }}" alt="Child" class="relative-avatar">
                                    <span class="avatar-online-dot"></span>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <span class="role-chip role-chip-child">
                                        <i class="bi bi-emoji-smile-fill"></i> Child
                                    </span>
                                    <h4 class="relative-name text-truncate">{{ ucwords(strtolower($child['first_name'] . ' ' . $child['last_name'])) }}</h4>
                                    <div class="relative-subtext text-truncate">{{ $child['email'] ?: 'No email on record' }}</div>
                                </div>
                            </div>

                            <ul class="card-meta-list">
                                <li class="card-meta-item">
                                    <i class="bi bi-telephone"></i>
                                    <span class="card-meta-mobile">{{ $child['mobile'] ?: 'Mobile not provided' }}</span>
                                </li>
                                <li class="card-meta-item">
                                    <i class="bi bi-calendar-event"></i>
                                    <span class="card-meta-birth">{{ $child['birth_date'] ? 'Born ' . $child['birth_date'] : 'Birth date not recorded' }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal({{ $child['id'] }})">
                                <i class="bi bi-pencil-square"></i> Edit Details
                            </button>
                            <a href="/organogram?highlight={{ $child['id'] }}" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="View in Tree" style="border-radius: var(--stitch-radius-md);">
                                <i class="bi bi-diagram-3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 bg-white rounded-4 border">
                    <div class="add-icon-bubble mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.6rem; background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-emoji-smile"></i>
                    </div>
                    <h5 class="fw-bold text-dark">No children documented yet</h5>
                    <p class="text-muted small mb-3" style="max-width: 400px; margin: 0 auto;">Document the next generation of your lineage by adding your sons and daughters.</p>
                    <button class="btn btn-primary btn-sm fw-bold px-4 py-2" onclick="selectStudioRelativeType('child')" style="border-radius: var(--stitch-radius-pill);">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add First Child
                    </button>
                </div>
            @endforelse

            @if(count($generations['children']) > 0)
                <div class="col-md-6 col-lg-4 relative-card-container">
                    <div class="btn-add-stitch-card" onclick="selectStudioRelativeType('child')">
                        <div class="add-icon-bubble"><i class="bi bi-plus-lg text-success"></i></div>
                        <div class="add-card-title">Add Another Child</div>
                        <div class="add-card-desc">Add son or daughter to your lineage</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Studio Add Relative Wizard Modal -->
<div class="modal fade" id="studioAddRelativeModal" tabindex="-1" aria-labelledby="studioAddRelativeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--stitch-radius-lg); overflow: hidden;">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <div>
                    <span class="badge bg-white text-primary fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Lineage Wizard</span>
                    <h5 class="modal-title fw-bold text-white" id="studioAddRelativeModalLabel"><i class="bi bi-person-plus-fill me-2"></i>Add Family Member</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="studioRelativeError" class="alert alert-danger d-none" style="border-radius: var(--stitch-radius-md);"></div>

                <!-- Step 1: Relationship Type Selection -->
                <div id="studioStep1">
                    <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.9rem;">What relationship are you adding?</h6>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary text-start p-3 rounded-3" onclick="selectStudioRelativeType('partner')" style="border-color: #e2e8f0;">
                            <div class="d-flex align-items-center">
                                <div class="add-icon-bubble mb-0 me-3" style="width: 42px; height: 42px; background: #fff1f2; color: #e11d48;">
                                    <i class="bi bi-heart-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Partner / Spouse</div>
                                    <div class="text-muted small">Current spouse, partner, or co-parent</div>
                                </div>
                            </div>
                        </button>
                        <button type="button" class="btn btn-outline-success text-start p-3 rounded-3" onclick="selectStudioRelativeType('child')" style="border-color: #e2e8f0;">
                            <div class="d-flex align-items-center">
                                <div class="add-icon-bubble mb-0 me-3" style="width: 42px; height: 42px; background: #ecfdf5; color: #059669;">
                                    <i class="bi bi-emoji-smile-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Child</div>
                                    <div class="text-muted small">Son or daughter</div>
                                </div>
                            </div>
                        </button>
                        <button type="button" class="btn btn-outline-warning text-start p-3 rounded-3" onclick="selectStudioRelativeType('sibling')" style="border-color: #e2e8f0;">
                            <div class="d-flex align-items-center">
                                <div class="add-icon-bubble mb-0 me-3" style="width: 42px; height: 42px; background: #fffbeb; color: #d97706;">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Sibling</div>
                                    <div class="text-muted small">Brother or sister</div>
                                </div>
                            </div>
                        </button>
                        <button type="button" class="btn btn-outline-info text-start p-3 rounded-3" onclick="selectStudioRelativeType('parents')" style="border-color: #e2e8f0;">
                            <div class="d-flex align-items-center">
                                <div class="add-icon-bubble mb-0 me-3" style="width: 42px; height: 42px; background: #eff6ff; color: #2563eb;">
                                    <i class="bi bi-diagram-3-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Parents</div>
                                    <div class="text-muted small">Father, Mother & Maiden name</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Step 2 Forms -->
                <div id="studioStep2-partner" class="d-none">
                    <form id="studioPartnerForm">
                        <input type="hidden" name="base_node_id" value="{{ $rootNodeId ?? 1 }}">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-fill me-2 text-danger"></i>Partner / Spouse Details</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">First Name *</label>
                            <input type="text" name="first_name" class="form-control" placeholder="First name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Last name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="partner@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Mobile (Optional)</label>
                            <input type="text" name="mobile" class="form-control" placeholder="+44...">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="backToStudioStep1()">Back</button>
                            <button type="submit" id="studioSavePartnerBtn" class="btn btn-primary flex-grow-1">Save Partner</button>
                        </div>
                    </form>
                </div>

                <div id="studioStep2-child" class="d-none">
                    <form id="studioChildForm">
                        <input type="hidden" name="base_node_id" value="{{ $rootNodeId ?? 1 }}">
                        <h6 class="fw-bold mb-3 text-success"><i class="bi bi-emoji-smile-fill me-2"></i>Child Details</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">First Name *</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Child's first name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Last name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="child@example.com">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="backToStudioStep1()">Back</button>
                            <button type="submit" id="studioSaveChildBtn" class="btn btn-success flex-grow-1">Save Child</button>
                        </div>
                    </form>
                </div>

                <div id="studioStep2-sibling" class="d-none">
                    <form id="studioSiblingForm">
                        <input type="hidden" name="base_node_id" value="{{ $rootNodeId ?? 1 }}">
                        <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-people-fill me-2"></i>Sibling Details</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">First Name *</label>
                            <input type="text" name="first_name" class="form-control" placeholder="Sibling's first name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" placeholder="Last name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male (Brother)</option>
                                <option value="Female">Female (Sister)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="sibling@example.com">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="backToStudioStep1()">Back</button>
                            <button type="submit" id="studioSaveSiblingBtn" class="btn btn-warning flex-grow-1">Save Sibling</button>
                        </div>
                    </form>
                </div>

                <div id="studioStep2-parents" class="d-none">
                    <form id="studioParentsForm">
                        <input type="hidden" name="base_node_id" value="{{ $rootNodeId ?? 1 }}">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-people-fill me-2"></i>Parents Details</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Father First Name</label>
                                <input type="text" name="father_first_name" class="form-control" placeholder="Father first name">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Father Last Name</label>
                                <input type="text" name="father_last_name" class="form-control" placeholder="Father last name">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Mother First Name</label>
                                <input type="text" name="mother_first_name" class="form-control" placeholder="Mother first name">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Mother Last Name</label>
                                <input type="text" name="mother_last_name" class="form-control" placeholder="Mother last name">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light" onclick="backToStudioStep1()">Back</button>
                            <button type="submit" id="studioSaveParentsBtn" class="btn btn-primary flex-grow-1">Save Parents</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Studio Edit Relative Modal -->
<div class="modal fade" id="studioEditModal" tabindex="-1" aria-labelledby="studioEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--stitch-radius-lg); overflow: hidden;">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <div>
                    <span class="badge bg-white text-primary fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">Update Record</span>
                    <h5 class="modal-title fw-bold text-white" id="studioEditModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Relative Details</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="studioEditError" class="alert alert-danger d-none" style="border-radius: var(--stitch-radius-md);"></div>

                <form id="studioEditForm">
                    <input type="hidden" name="node_id" id="editNodeId" value="">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">First Name *</label>
                            <input type="text" name="first_name" id="editFirstName" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" id="editLastName" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3" id="editMaidenNameGroup">
                        <label class="form-label small fw-semibold">Maiden Name (Optional)</label>
                        <input type="text" name="maiden_name" id="editMaidenName" class="form-control" placeholder="Mother / Spouse Maiden Name">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Gender</label>
                            <select name="gender" id="editGender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Birth Date</label>
                            <input type="date" name="birth_date" id="editBirthDate" class="form-control">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control" placeholder="relative@example.com">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Mobile</label>
                            <input type="text" name="mobile" id="editMobile" class="form-control" placeholder="+44...">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Occupation</label>
                            <input type="text" name="occupation" id="editOccupation" class="form-control" placeholder="e.g. Architect">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Location</label>
                            <input type="text" name="location" id="editLocation" class="form-control" placeholder="e.g. London, UK">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Family Bio / Note</label>
                        <textarea name="bio" id="editBio" class="form-control" rows="2" placeholder="Brief note or memory..."></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="is_deceased" value="1" id="editIsDeceased">
                        <label class="form-check-label small text-muted" for="editIsDeceased">
                            Mark as Deceased / In Loving Memory
                        </label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="studioSaveEditBtn" class="btn btn-primary flex-grow-1">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toTitleCase(str) {
        if (!str) return '';
        return str.toLowerCase().replace(/(?:^|\s|-)\S/g, function(a) { return a.toUpperCase(); });
    }

    function openStudioAddModal() {
        backToStudioStep1();
        var myModal = new bootstrap.Modal(document.getElementById('studioAddRelativeModal'));
        myModal.show();
    }

    function selectStudioRelativeType(type) {
        document.getElementById('studioStep1').classList.add('d-none');
        document.getElementById('studioStep2-partner').classList.add('d-none');
        document.getElementById('studioStep2-child').classList.add('d-none');
        document.getElementById('studioStep2-sibling').classList.add('d-none');
        document.getElementById('studioStep2-parents').classList.add('d-none');
        document.getElementById('studioRelativeError').classList.add('d-none');

        if (type === 'partner') {
            document.getElementById('studioStep2-partner').classList.remove('d-none');
        } else if (type === 'child') {
            document.getElementById('studioStep2-child').classList.remove('d-none');
        } else if (type === 'sibling') {
            document.getElementById('studioStep2-sibling').classList.remove('d-none');
        } else if (type === 'parents') {
            document.getElementById('studioStep2-parents').classList.remove('d-none');
        }

        var myModal = bootstrap.Modal.getInstance(document.getElementById('studioAddRelativeModal'));
        if (!myModal) {
            myModal = new bootstrap.Modal(document.getElementById('studioAddRelativeModal'));
        }
        myModal.show();
    }

    function backToStudioStep1() {
        document.getElementById('studioStep2-partner').classList.add('d-none');
        document.getElementById('studioStep2-child').classList.add('d-none');
        document.getElementById('studioStep2-sibling').classList.add('d-none');
        document.getElementById('studioStep2-parents').classList.add('d-none');
        document.getElementById('studioStep1').classList.remove('d-none');
        document.getElementById('studioRelativeError').classList.add('d-none');
    }

    // Open In-Place Edit Modal
    function openStudioEditModal(nodeId) {
        const errorDiv = document.getElementById('studioEditError');
        errorDiv.classList.add('d-none');
        
        // Fetch Node Details
        fetch(`/member/organogram/node/${nodeId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.message && data.message.node) {
                    const n = data.message.node;
                    document.getElementById('editNodeId').value = n.id;
                    document.getElementById('editFirstName').value = n.first_name || '';
                    document.getElementById('editLastName').value = n.last_name || '';
                    document.getElementById('editMaidenName').value = n.maiden_name || '';
                    document.getElementById('editEmail').value = n.email || '';
                    document.getElementById('editMobile').value = n.mobile || '';
                    document.getElementById('editOccupation').value = n.occupation || '';
                    document.getElementById('editLocation').value = n.location || '';
                    document.getElementById('editBio').value = n.bio || '';
                    document.getElementById('editBirthDate').value = n.birth_date || '';
                    document.getElementById('editGender').value = n.gender || 'Male';
                    document.getElementById('editIsDeceased').checked = (n.is_deceased == 1);

                    var editModal = new bootstrap.Modal(document.getElementById('studioEditModal'));
                    editModal.show();
                } else {
                    if (typeof window.showAppSwal === 'function') {
                        window.showAppSwal('Error', 'Failed to load relative details.', 'error');
                    } else {
                        alert('Failed to load relative details.');
                    }
                }
            })
            .catch(err => {
                console.error('Error fetching node details:', err);
                alert('A network error occurred while loading relative details.');
            });
    }

    // Edit form submission (Real-Time In-Place DOM Update)
    document.getElementById('studioEditForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('studioSaveEditBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving Changes...`;
        btn.disabled = true;

        const errorDiv = document.getElementById('studioEditError');
        errorDiv.classList.add('d-none');

        const formData = new FormData(form);
        const nodeId = formData.get('node_id');
        const firstName = (formData.get('first_name') || '').trim();
        const lastName = (formData.get('last_name') || '').trim();
        const maidenName = (formData.get('maiden_name') || '').trim();
        const email = (formData.get('email') || '').trim();
        const mobile = (formData.get('mobile') || '').trim();
        const location = (formData.get('location') || '').trim();
        const occupation = (formData.get('occupation') || '').trim();
        const bio = (formData.get('bio') || '').trim();
        const birthDate = (formData.get('birth_date') || '').trim();

        fetch('/member/organogram/editor/update', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            const isOk = (data.status === 200 || data.status === 'success' || data.statusCode === 200);
            if (isOk) {
                // Real-Time In-Place DOM Hydration
                const cardContainer = document.getElementById('relative-card-node-' + nodeId);
                if (cardContainer) {
                    const fullName = (firstName + ' ' + lastName).trim();
                    cardContainer.setAttribute('data-name', fullName.toLowerCase());
                    
                    const nameEl = cardContainer.querySelector('.relative-name');
                    if (nameEl) nameEl.textContent = toTitleCase(fullName);

                    const subtextEl = cardContainer.querySelector('.relative-subtext');
                    if (subtextEl) subtextEl.textContent = email || 'No email on record';

                    const maidenEl = cardContainer.querySelector('.card-maiden-name');
                    if (maidenEl) {
                        if (maidenName) {
                            maidenEl.textContent = 'née ' + toTitleCase(maidenName);
                            maidenEl.classList.remove('d-none');
                        } else {
                            maidenEl.textContent = '';
                            maidenEl.classList.add('d-none');
                        }
                    }

                    const mobileEl = cardContainer.querySelector('.card-meta-mobile');
                    if (mobileEl) mobileEl.textContent = mobile || 'Mobile not provided';

                    const locEl = cardContainer.querySelector('.card-meta-location');
                    if (locEl) locEl.textContent = location || 'Location not set';

                    const occEl = cardContainer.querySelector('.card-meta-occupation');
                    if (occEl) occEl.textContent = occupation || 'Occupation not set';

                    const bioEl = cardContainer.querySelector('.card-meta-bio');
                    if (bioEl) bioEl.textContent = bio || 'Relative';

                    const birthEl = cardContainer.querySelector('.card-meta-birth');
                    if (birthEl) birthEl.textContent = birthDate ? ('Born ' + birthDate) : 'Birth date not recorded';

                    // Trigger pulse highlight animation
                    const cardEl = cardContainer.querySelector('.relative-card');
                    if (cardEl) {
                        cardEl.classList.remove('flash-updated');
                        void cardEl.offsetWidth; // force DOM reflow
                        cardEl.classList.add('flash-updated');
                    }
                }

                // Close modal immediately
                const modalEl = document.getElementById('studioEditModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                // Show Real-Time Toast
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast('Updated ' + toTitleCase(firstName) + ' in real-time!', 'success');
                }
            } else {
                errorDiv.textContent = data.message || 'Failed to save changes.';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            errorDiv.textContent = 'A network error occurred.';
            errorDiv.classList.remove('d-none');
        });
    });

    // Filter toolbar
    function filterStudioCards(genKey) {
        document.querySelectorAll('.filter-pill-btn').forEach(btn => btn.classList.remove('active'));
        event.target.closest('.filter-pill-btn').classList.add('active');

        if (genKey === 'all') {
            document.querySelectorAll('.studio-gen-group').forEach(el => el.classList.remove('d-none'));
        } else {
            document.querySelectorAll('.studio-gen-group').forEach(el => el.classList.add('d-none'));
            const target = document.getElementById('group-' + genKey);
            if (target) target.classList.remove('d-none');
        }
    }

    // Instant name search filter
    function searchStudioRelatives(query) {
        const q = query.trim().toLowerCase();
        document.querySelectorAll('.relative-card-container').forEach(card => {
            const name = card.getAttribute('data-name') || '';
            if (!q || name.includes(q)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function createStudioCardHtml(node, role, roleBadgeClass, roleIcon) {
        const fullName = toTitleCase((node.first_name + ' ' + (node.last_name || '')).trim());
        const maidenHtml = node.maiden_name ? `<div class="text-primary fw-semibold small card-maiden-name">née ${toTitleCase(node.maiden_name)}</div>` : `<div class="text-primary fw-semibold small card-maiden-name d-none"></div>`;
        const emailHtml = node.email ? `<div class="relative-subtext text-truncate">${node.email}</div>` : `<div class="relative-subtext text-truncate">No email on record</div>`;
        const mobileText = node.mobile || 'Mobile not provided';
        const locText = node.location || 'Location not set';
        const avatarUrl = node.avatar_url || (node.gender === 'Female' ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png');

        return `
        <div class="col-md-6 col-lg-4 relative-card-container" id="relative-card-node-${node.id}" data-name="${fullName.toLowerCase()}">
            <div class="relative-card flash-updated">
                <div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="relative-avatar-wrapper">
                            <img src="${avatarUrl}" alt="${fullName}" class="relative-avatar">
                            <span class="avatar-online-dot"></span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <span class="role-chip ${roleBadgeClass}">
                                <i class="bi ${roleIcon}"></i> ${toTitleCase(role)}
                            </span>
                            <h4 class="relative-name text-truncate">${fullName}</h4>
                            ${maidenHtml}
                            ${emailHtml}
                        </div>
                    </div>

                    <ul class="card-meta-list">
                        <li class="card-meta-item">
                            <i class="bi bi-telephone"></i>
                            <span class="card-meta-mobile">${mobileText}</span>
                        </li>
                        <li class="card-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <span class="card-meta-location">${locText}</span>
                        </li>
                    </ul>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn-tonal-action flex-grow-1" onclick="openStudioEditModal(${node.id})">
                        <i class="bi bi-pencil-square"></i> Edit Details
                    </button>
                    <a href="/organogram?highlight=${node.id}" class="btn btn-sm btn-light border text-muted px-3 d-flex align-items-center" title="View in Tree" style="border-radius: var(--stitch-radius-md);">
                        <i class="bi bi-diagram-3"></i>
                    </a>
                </div>
            </div>
        </div>`;
    }

    // AJAX Form submissions for Add relative (Real-Time In-Place DOM Insertion)
    document.getElementById('studioPartnerForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitStudioForm(this, '/member/organogram/editor/partner', 'studioSavePartnerBtn', 'Saving Partner...', 'partner');
    });

    document.getElementById('studioChildForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitStudioForm(this, '/member/organogram/editor/child', 'studioSaveChildBtn', 'Saving Child...', 'child');
    });

    document.getElementById('studioSiblingForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitStudioForm(this, '/member/organogram/editor/sibling', 'studioSaveSiblingBtn', 'Saving Sibling...', 'sibling');
    });

    document.getElementById('studioParentsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        submitStudioForm(this, '/member/organogram/editor/parents', 'studioSaveParentsBtn', 'Saving Parents...', 'parents');
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

    function submitStudioForm(form, url, btnId, loadingText, relType) {
        const btn = document.getElementById(btnId);
        const originalText = btn.innerHTML;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${loadingText}`;
        btn.disabled = true;

        const errorDiv = form.closest('.modal-body').querySelector('.alert-danger') || document.getElementById('studioRelativeError');
        if (errorDiv) errorDiv.classList.add('d-none');

        const formData = new FormData(form);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            const isOk = (data.status === 200 || data.status === 'success' || data.statusCode === 200);
            if (isOk) {
                const msg = (data.message && typeof data.message === 'object') ? data.message : {};
                
                // Real-Time In-Place DOM Insertion for Added Relatives
                if (relType === 'partner' && msg.node) {
                    const row = document.querySelector('#group-gen-0 .row');
                    if (row) {
                        const cardHtml = createStudioCardHtml(msg.node, 'Partner / Spouse', 'role-chip-partner', 'bi-heart-fill');
                        const addPartnerCard = row.querySelector('.btn-add-stitch-card')?.closest('.relative-card-container');
                        if (addPartnerCard) {
                            addPartnerCard.insertAdjacentHTML('beforebegin', cardHtml);
                        } else {
                            row.insertAdjacentHTML('beforeend', cardHtml);
                        }
                    }
                } else if (relType === 'sibling' && msg.node) {
                    const row = document.querySelector('#group-gen-0 .row');
                    if (row) {
                        const cardHtml = createStudioCardHtml(msg.node, 'Sibling', 'role-chip-sibling', 'bi-people-fill');
                        const addSiblingCard = row.querySelectorAll('.btn-add-stitch-card')[1]?.closest('.relative-card-container') || row.lastElementChild;
                        if (addSiblingCard) {
                            addSiblingCard.insertAdjacentHTML('beforebegin', cardHtml);
                        } else {
                            row.insertAdjacentHTML('beforeend', cardHtml);
                        }
                    }
                } else if (relType === 'child' && msg.node) {
                    const group = document.getElementById('group-gen-plus1');
                    if (group) {
                        const row = group.querySelector('.row');
                        const emptyState = row?.querySelector('.col-12.text-center');
                        if (emptyState) emptyState.remove();

                        const cardHtml = createStudioCardHtml(msg.node, 'Child', 'role-chip-child', 'bi-emoji-smile-fill');
                        const addChildCard = row.querySelector('.btn-add-stitch-card')?.closest('.relative-card-container');
                        if (addChildCard) {
                            addChildCard.insertAdjacentHTML('beforebegin', cardHtml);
                        } else {
                            row.insertAdjacentHTML('afterbegin', cardHtml);
                        }
                    }
                } else if (relType === 'parents' && (msg.father || msg.mother)) {
                    const row = document.querySelector('#group-gen-minus1 .row');
                    if (row) {
                        if (msg.father) {
                            const fHtml = createStudioCardHtml(msg.father, 'Father', 'role-chip-parent', 'bi-person-fill');
                            const fatherPlaceholder = document.getElementById('relative-card-node-father') || row.firstElementChild;
                            if (fatherPlaceholder) fatherPlaceholder.outerHTML = fHtml;
                        }
                        if (msg.mother) {
                            const mHtml = createStudioCardHtml(msg.mother, 'Mother', 'role-chip-parent', 'bi-person-fill');
                            const motherPlaceholder = document.getElementById('relative-card-node-mother') || row.children[1];
                            if (motherPlaceholder) motherPlaceholder.outerHTML = mHtml;
                        }
                    }
                }

                // Increment Hero Stats Counter in Real-Time
                const statTotal = document.getElementById('statTotalMembers');
                if (statTotal) {
                    const inc = (relType === 'parents') ? 2 : 1;
                    statTotal.textContent = (parseInt(statTotal.textContent) || 0) + inc;
                }

                // Close Modal
                const modalEl = document.getElementById('studioAddRelativeModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                form.reset();

                // Show Success Toast
                const addedName = (msg.node?.first_name || msg.father?.first_name || 'Relative');
                if (typeof window.showAppToast === 'function') {
                    window.showAppToast('Added ' + toTitleCase(addedName) + ' in real-time!', 'success');
                }
            } else {
                if (errorDiv) {
                    errorDiv.textContent = extractStudioErrorMessage(data.message || data.error || data);
                    errorDiv.classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            if (errorDiv) {
                errorDiv.textContent = 'A network error occurred. Please try again.';
                errorDiv.classList.remove('d-none');
            }
        });
    }
</script>
@endsection
