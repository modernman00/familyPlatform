{{-- Creative Studio tab strip: shared by the hub, Tree Studio, and Festive Cards pages. $creativeTab = hub|tree|flyers|cards --}}
@php $creativeTab = $creativeTab ?? 'hub'; @endphp
<nav class="d-flex flex-nowrap gap-2 overflow-x-auto mb-4 pb-1" aria-label="Creative Studio sections" style="overflow-x:auto;">
    <a href="/creative-studio" class="btn {{ $creativeTab === 'hub' ? 'btn-dark' : 'btn-outline-secondary' }} fw-bold d-inline-flex align-items-center gap-2" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" title="Creative Studio — Tree Studio, Flyers &amp; Posters, and Festive Greeting Cards" @if($creativeTab === 'hub') aria-current="page" @endif>
        <span class="creative-3d-icon-wrap" aria-hidden="true">
            <span class="creative-3d-gem">
                <span class="creative-3d-sheen"></span>
                <svg class="creative-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2.5L14.4 7.8L20.1 8.5L15.8 12.4L17 18.1L12 15.2L7 18.1L8.2 12.4L3.9 8.5L9.6 7.8L12 2.5Z" fill="#fef08a" stroke="#ffffff" stroke-width="1.2" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="creative-3d-spark creative-3d-spark--1"></span>
            <span class="creative-3d-spark creative-3d-spark--2"></span>
        </span>
        <span>Creative Studio</span>
    </a>
    <a href="/familyStudio" class="btn {{ $creativeTab === 'tree' ? 'btn-primary' : 'btn-outline-primary' }} fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" title="Tree Studio — Build &amp; curate your family lineage, unions and generations" @if($creativeTab === 'tree') aria-current="page" @endif>
        <i class="bi bi-diagram-3-fill me-2"></i>Tree Studio
    </a>
    <a href="/poster-studio" class="btn {{ $creativeTab === 'flyers' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }} fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" title="Flyer Studio — Design reunion, jubilee &amp; milestone posters">
        <i class="bi bi-easel2-fill me-2"></i>Flyers &amp; Posters
    </a>
    <a href="/member/festive-cards" class="btn {{ $creativeTab === 'cards' ? 'btn-danger' : 'btn-outline-danger' }} fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" title="Festive Cards — Send living 3D greeting envelopes for every occasion" @if($creativeTab === 'cards') aria-current="page" @endif>
        <i class="bi bi-envelope-heart-fill me-2"></i>Festive Cards
    </a>
</nav>
