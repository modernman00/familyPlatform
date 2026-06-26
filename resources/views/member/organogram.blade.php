@extends ('layouts.profileBase')
@section('title', 'Family Tree')
@section('data-page-id', 'organogram')
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css">
@endpush
@section('content')

<div class="organogram-container-fluid">
    <div class="organogram-header">
        <div class="header-container">
            <h2 class="organogram-title fw-bold">
                <i class="bi bi-diagram-3"></i>
                {{ $data['firstName'] ?? '' }} {{ $data['lastName'] ?? '' }}'s Family Tree
            </h2>

            <p class="organogram-subtitle lead small">
                Explore your family heritage and connections. Drag to navigate, scroll to zoom, and click any profile to see details.
            </p>
        </div>
    </div>

    <div class="organogram-container">

        <!-- Collapsible Instructions -->
        <button class="instructions-toggle" id="instructionsToggle" title="View Navigation Guide">
            <i class="bi bi-question-circle"></i>
        </button>

        <div class="instructions collapsed" id="instructions">
            <h4><i class="bi bi-info-circle"></i> Navigation Guide</h4>
            <ul>
                <li><i class="bi bi-hand-index-thumb"></i> <strong>Click</strong> to view person details</li>
                <li><i class="bi bi-mouse"></i> <strong>Scroll</strong> to zoom in or out</li>
                <li><i class="bi bi-arrows-move"></i> <strong>Drag</strong> to move around the tree</li>
                <li><i class="bi bi-plus-circle"></i> Use <strong>Zoom Buttons</strong> for control</li>
            </ul>
        </div>

        <div class="tree-container">
            <div class="tree-wrapper" id="treeWrapper">
                <div class="tree" id="familyTree">
                    <ul>
                        <li>
                            <!-- Parents -->
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
                                                $siblingId = isset($sibling['id']) ? $sibling['id'] : null;
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

            <div class="zoom-controls">
                <button class="zoom-btn" id="zoomIn" title="Zoom In"><i class="bi bi-plus-lg"></i></button>
                <button class="zoom-btn" id="zoomOut" title="Zoom Out"><i class="bi bi-dash-lg"></i></button>
                <button class="zoom-btn" id="resetZoom" title="Reset View"><i class="bi bi-arrow-counterclockwise"></i></button>
            </div>
        </div>

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
        </div>

        <!-- Person Detail Modal -->
        <div class="person-modal" id="personModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="bi bi-person-badge"></i> Person Details</h3>
                    <button class="modal-close" id="closeModal"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Content populated by JS -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


