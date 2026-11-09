{{-- Creative Studio tab strip: shared by the hub and Festive Cards pages. $creativeTab = hub|cards --}}
@php $creativeTab = $creativeTab ?? 'hub'; @endphp
<nav class="d-flex flex-nowrap gap-2 overflow-x-auto mb-4 pb-1" aria-label="Creative Studio sections" style="overflow-x:auto;">
    <a href="/creative-studio" class="btn {{ $creativeTab === 'hub' ? 'btn-dark' : 'btn-outline-secondary' }} fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" @if($creativeTab === 'hub') aria-current="page" @endif>
        <i class="bi bi-palette2 me-2"></i>Creative Studio
    </a>
    <a href="/poster-studio" class="btn btn-outline-warning text-dark fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;">
        <i class="bi bi-easel2-fill me-2"></i>Flyers &amp; Posters
    </a>
    <a href="/member/festive-cards" class="btn {{ $creativeTab === 'cards' ? 'btn-danger' : 'btn-outline-danger' }} fw-bold d-inline-flex align-items-center" style="white-space:nowrap;flex-shrink:0;min-height:44px;border-radius:12px;" @if($creativeTab === 'cards') aria-current="page" @endif>
        <i class="bi bi-envelope-heart-fill me-2"></i>Festive Cards
    </a>
</nav>
