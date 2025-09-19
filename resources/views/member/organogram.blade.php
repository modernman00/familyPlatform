@extends ('layouts.profileBase')
@section('title', 'Family Tree')
{{-- custom css --}}
@push('styles')
    <link rel="stylesheet" href="/public/css/organogram.css">
@endpush


@section('data-page-id', 'Organogram')
@section('content')

    <div class="organogram-container">
        <div class="organogram-header">
            <h2 class="organogram-title">
                {{ isset($data['main']['firstName']) ? $data['main']['firstName'] : '' }}
                {{ isset($data['main']['lastName']) ? $data['main']['lastName'] : '' }}'s family Tree
            </h2>

            {{-- <p class="organogram-subtitle">
                This family tree represents every member of the family who share the same father and mother.
                In future updates, we will expand the tree to include other family members who are linked solely
                through either the mother's or father's side, further encompassing the broader family network.
            </p> --}}

            <p class="organogram-subtitle">
                Click a person to learn more, scroll to zoom, and drag to pan.
            </p>

        </div>

        <div class="instructions">
            <h4>How to Navigate:</h4>
            <ul>
                <li>Click on any person to view details</li>
                <li>Scroll to zoom in/out</li>
                <li>Drag to pan around the tree</li>
                <li>Use zoom buttons for precise control</li>
            </ul>
        </div>


        <div class="tree-container">
            <div class="tree-wrapper" id="treeWrapper">
                <div class="tree" id="familyTree">
                    <ul>
                        <li>
                            <!-- Parents -->
                            @include('member.includes.treeNode', [
                                'type' => 'Father',
                                'data' => $data['father'],
                            ])

                            @include('member.includes.treeNode', [
                                'type' => 'Mother',
                                'data' => $data['mother'],
                            ])


                            {{--  THE MAIN PERSON IN FOCUS AND IF THERE IS A SPOUSE  --}}
                            <ul>
                                <li>

                                    @include('member.includes.treeNode', [
                                        'type' => 'Me',
                                        'data' => $data['main'],
                                    ])



                                    @isset($data['spouse']['name'])
                                        @include('member.includes.treeNode', [
                                            'type' => 'spouse',
                                            'data' => $data['spouse'],
                                        ])
                                    @endisset

                                    {{--  check if they have children --}}
                                    <ul>
                                        <li>
                                            @if (isset($data['children']) !== null)
                                                @foreach ($data['children'] as $child)
                                                
                                                        @include('member.includes.treeNode', [
                                                            'type' => 'child',
                                                            'data' => $child,
                                                        ])
                                                
                                                @endforeach
                                            @endif
                                        </li>
                                    </ul>

                                </li>

                                    {{--  check if the main person has siblings --}}
                                
                                    @isset($data['siblings'])
                                        @foreach ($data['siblings'] as $sibling)
                                        <li>
                                            @isset($sibling['name'])
                                    
                                                @include('member.includes.treeNode', [
                                                    'type' => 'sibling',
                                                    'data' => $sibling,
                                                ])
                                        


                                                {{-- if sibling has kids --}}
                                            
                                                    @php
                                                        $siblingId = isset($sibling['id']) ? $sibling['id'] : null;
                                                    @endphp
                                                

                                                    @if ($siblingId && isset($data['sibling_children']))
                                                    @foreach ($data['sibling_children'] as $child)
                                                    @if (isset($child['father_id']) && $child['father_id'] === $siblingId)
                                                    <ul>
                                                        <li>
                                                       
                                                            @include('member.includes.treeNode', ['type' => $child['relationship'], 'data' => $child])

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




    @endsection
