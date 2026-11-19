@if(!empty($pinnedWishlists))
<style>
.fp-pw-card {
    background-color: var(--card-bg, #ffffff);
    border: 1.5px solid #f59e0b;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.12);
}
.fp-pw-item-box {
    background-color: var(--surface-alt, rgba(148, 163, 184, 0.08));
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 12px;
    padding: 12px;
}
.fp-pw-badge-neutral {
    background-color: #e2e8f0 !important;
    color: #0f172a !important;
    border: 1px solid #cbd5e1 !important;
    font-weight: 600;
}
.fp-pw-badge-amber {
    background-color: #fbbf24 !important;
    color: #0f172a !important;
    font-weight: 700;
}
.fp-pw-item-title {
    color: inherit;
    font-weight: 500;
}
.fp-pw-btn {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
    flex-shrink: 0;
    border-radius: 12px;
    font-weight: 600;
    padding: 0 14px;
}
</style>
<section class="fp-pw-card" aria-labelledby="fp-pinned-wishlists-heading">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <span class="badge rounded-pill fp-pw-badge-amber mb-1">🎁 Pinned Gift Registry</span>
            <h2 id="fp-pinned-wishlists-heading" class="h6 fw-bold mb-0">Pinned Family Gift Wishlists</h2>
        </div>
        <a href="/wishlists" class="fp-pw-btn btn fp-pw-badge-amber btn-sm text-decoration-none">
            <i class="bi bi-gift" aria-hidden="true"></i>
            <span>View All &amp; Shop</span>
        </a>
    </div>

    <div class="d-grid gap-2">
        @foreach(array_slice($pinnedWishlists, 0, 3) as $pw)
            @php
                $pwId = (int) ($pw['id'] ?? 0);
                $pwDays = (int) ($pw['days_remaining'] ?? 1);
                $pwItems = is_array($pw['items'] ?? null) ? $pw['items'] : [];
            @endphp
            <div class="fp-pw-item-box">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="d-flex flex-wrap gap-2 mb-1">
                            <span class="badge rounded-pill fp-pw-badge-amber">
                                📌 {{ $pwDays }} {{ $pwDays === 1 ? 'day' : 'days' }} left
                            </span>
                            <span class="badge rounded-pill fp-pw-badge-neutral">{{ $pw['occasion'] ?? 'Gift' }}</span>
                        </div>
                        <strong class="d-block">{{ $pw['title'] ?? 'Family Gift Wishlist' }}</strong>
                        <small class="text-muted">By {{ $pw['author_name'] ?? 'Family Member' }} &middot; {{ count($pwItems) }} {{ count($pwItems) === 1 ? 'idea' : 'ideas' }}</small>
                    </div>
                    <a
                        href="/wishlists#wishlist-{{ $pwId }}"
                        class="fp-pw-btn btn text-white btn-sm text-decoration-none"
                        style="background: #a31621;"
                    >
                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                        <span>View &amp; Claim</span>
                    </a>
                </div>

                @if(!empty($pwItems))
                    <ul class="list-unstyled mb-0 small d-grid gap-1">
                        @foreach(array_slice($pwItems, 0, 3) as $pItem)
                            <li class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <span class="fp-pw-item-title">
                                    &bull; {{ $pItem['item_title'] ?? '' }}
                                    @if(!empty($pItem['approx_price']))
                                        <span class="text-muted">({{ $pItem['currency'] ?? '£' }}{{ number_format((float) $pItem['approx_price'], 2) }})</span>
                                    @endif
                                </span>
                                @if(!empty($pItem['claimed_at']))
                                    <span class="badge rounded-pill text-bg-success">✓ Claimed</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    @if(count($pinnedWishlists) > 3)
        <div class="mt-2 text-end">
            <a href="/wishlists" class="small fw-semibold text-decoration-none">
                +{{ count($pinnedWishlists) - 3 }} more pinned {{ count($pinnedWishlists) - 3 === 1 ? 'wishlist' : 'wishlists' }} &rarr;
            </a>
        </div>
    @endif
</section>
@endif
