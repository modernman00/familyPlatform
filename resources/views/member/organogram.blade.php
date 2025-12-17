@extends ('layouts.profileBase')
@section('title', 'Family Tree')
@section('data-page-id', 'organogram')
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css">
@endpush
@section('content')

<div class="organogram-container">
        <div class="organogram-header">
            <h2 class="organogram-title">
                <i class="bi bi-diagram-3-fill"></i>
                {{ $data['firstName'] ?? '' }}
                {{ $data['lastName'] ?? '' }}'s Family Tree
            </h2>

            <p class="organogram-subtitle">
                Explore your family connections • Click to view details • Drag to navigate
            </p>

        </div>

        <!-- Collapsible Instructions -->
        <button class="instructions-toggle" id="instructionsToggle" title="Toggle Help">
            <i class="bi bi-question-circle-fill"></i>
        </button>

        <div class="instructions collapsed" id="instructions">
            <h4><i class="bi bi-info-circle"></i> How to Navigate:</h4>
            <ul>
                <li><i class="bi bi-cursor-fill"></i> Click any person to view details</li>
                <li><i class="bi bi-zoom-in"></i> Scroll to zoom in/out</li>
                <li><i class="bi bi-arrows-move"></i> Drag to pan around</li>
                <li><i class="bi bi-plus-circle"></i> Use zoom buttons for control</li>
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


                            {{--  THE MAIN PERSON IN FOCUS AND IF THERE IS A SPOUSE  --}}   
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

                                    {{--  check if they have children --}}
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

                                    {{--  check if the main person has siblings --}}
                                
                                    @isset($orgData['siblings'])
                                        @foreach ($orgData['siblings'] as $sibling)
                                        <li>
                                            @isset($sibling['fullName'])
                                    
                                                @include('member.includes.treeNode', ['type' => 'sibling', 'dataDB' => $sibling])
                                        


                                                {{-- if sibling has kids --}}
                                            
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
                <button class="zoom-btn" id="zoomIn">+</button>
                <button class="zoom-btn" id="zoomOut">-</button>
                <button class="zoom-btn" id="resetZoom"><i class="bi bi-arrow-clockwise"></i></button>
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
                <span>Spouse/Partner</span>
            </div>
        </div>

        <!-- Person Detail Modal -->
        <div class="person-modal" id="personModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Person Details</h3>
                    <button class="modal-close" id="closeModal">&times;</button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>

    </div>

@endsection
