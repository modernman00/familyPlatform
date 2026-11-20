@if(!empty($pinnedWishlists))
<style>
.fp-pw-showcase {
    background-color: var(--card-bg, #ffffff);
    border: 1.5px solid rgba(245, 158, 11, 0.65);
    border-radius: 18px;
    overflow: hidden;
    margin-bottom: 18px;
    box-shadow: 0 10px 28px rgba(245, 158, 11, 0.12);
}
.fp-pw-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #f8fafc;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.fp-pw-icon-tile {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
}
.fp-pw-list-card {
    background-color: var(--surface-alt, rgba(148, 163, 184, 0.07));
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 14px;
    padding: 14px;
    transition: border-color 0.15s ease;
}
.fp-pw-list-card:hover {
    border-color: rgba(245, 158, 11, 0.6);
}
.fp-pw-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #a31621;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
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
.fp-pw-item-pill {
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    padding: 8px 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.fp-pw-item-title {
    color: var(--text-main, #0f172a);
    font-weight: 600;
    font-size: 0.86rem;
}
.fp-pw-price-tag {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e;
    font-weight: 800;
    font-size: 0.76rem;
    padding: 2px 8px;
    border-radius: 6px;
}
.fp-pw-badge-available {
    background: rgba(16, 185, 129, 0.14);
    color: #065f46;
    font-weight: 700;
}
body.dark-mode .fp-pw-price-tag,
body.dark-theme .fp-pw-price-tag,
[data-bs-theme="dark"] .fp-pw-price-tag,
html.dark .fp-pw-price-tag {
    background: rgba(251, 191, 36, 0.2);
    color: #fde68a;
}
body.dark-mode .fp-pw-badge-available,
body.dark-theme .fp-pw-badge-available,
[data-bs-theme="dark"] .fp-pw-badge-available,
html.dark .fp-pw-badge-available {
    background: rgba(16, 185, 129, 0.22);
    color: #6ee7b7;
}
/* Shared dark-mode contrast helpers for Feed Wishlist Card */
.fp-fw-badge-emerald {
    background: rgba(16, 185, 129, 0.15);
    color: #065f46;
    font-weight: 700;
}
.fp-fw-index-pill {
    background: rgba(245, 158, 11, 0.16);
    color: #92400e;
}
body.dark-mode .fp-fw-badge-emerald,
body.dark-theme .fp-fw-badge-emerald,
[data-bs-theme="dark"] .fp-fw-badge-emerald,
html.dark .fp-fw-badge-emerald {
    background: rgba(16, 185, 129, 0.22);
    color: #6ee7b7;
}
body.dark-mode .fp-fw-index-pill,
body.dark-theme .fp-fw-index-pill,
[data-bs-theme="dark"] .fp-fw-index-pill,
html.dark .fp-fw-index-pill {
    background: rgba(251, 191, 36, 0.22);
    color: #fde68a;
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
    font-weight: 700;
    padding: 0 14px;
    transition: transform 0.15s ease;
}
.fp-pw-btn:active {
    transform: scale(0.98);
}
</style>
<section class="fp-pw-showcase" aria-labelledby="fp-pinned-wishlists-heading">
    {{-- Showcase Dark Slate Header Bar --}}
    <div class="fp-pw-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <span class="fp-pw-icon-tile" aria-hidden="true">🎁</span>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge rounded-pill fp-pw-badge-amber">🎁 Pinned Gift Registry</span>
                    <span class="small" style="color: #cbd5e1; font-size: 0.76rem; font-weight: 600;">
                        {{ count($pinnedWishlists) }} {{ count($pinnedWishlists) === 1 ? 'Active List' : 'Active Lists' }}
                    </span>
                </div>
                <h2 id="fp-pinned-wishlists-heading" class="h6 fw-bold mb-0 text-white mt-1">
                    Pinned Family Gift Wishlists
                </h2>
            </div>
        </div>
        <a href="/wishlists" class="fp-pw-btn btn fp-pw-badge-amber btn-sm text-decoration-none">
            <i class="bi bi-gift-fill" aria-hidden="true"></i>
            <span>View All &amp; Shop</span>
        </a>
    </div>

    {{-- Pinned Wishlists Body --}}
    <div class="p-3 d-grid gap-2.5">
        @foreach(array_slice($pinnedWishlists, 0, 3) as $pw)
            @php
                $pwId = (int) ($pw['id'] ?? 0);
                $pwDays = (int) ($pw['days_remaining'] ?? 1);
                $pwItems = is_array($pw['items'] ?? null) ? $pw['items'] : [];
                $pwAuthor = (string) ($pw['author_name'] ?? 'Family Member');
                $pwParts = preg_split('/\s+/', trim($pwAuthor)) ?: [];
                $pwInitials = strtoupper(mb_substr((string) ($pwParts[0] ?? 'F'), 0, 1) . mb_substr((string) ($pwParts[1] ?? ''), 0, 1));
                $claimedCount = count(array_filter($pwItems, static fn (array $i): bool => !empty($i['claimed_at'])));
                $availableCount = max(0, count($pwItems) - $claimedCount);
            @endphp
            <div class="fp-pw-list-card">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 pb-2.5 mb-2.5 border-bottom" style="border-color: var(--border-color, #e2e8f0) !important;">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <span class="fp-pw-avatar" aria-hidden="true">{{ $pwInitials !== '' ? $pwInitials : 'FM' }}</span>
                        <div class="min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-1.5 mb-0.5">
                                <strong class="text-truncate" style="font-size: 0.98rem; color: var(--text-main, #0f172a);" title="{{ $pw['title'] ?? 'Family Gift Wishlist' }}">
                                    {{ $pw['title'] ?? 'Family Gift Wishlist' }}
                                </strong>
                                <span class="badge rounded-pill fp-pw-badge-neutral">{{ $pw['occasion'] ?? 'Gift' }}</span>
                                <span class="badge rounded-pill fp-pw-badge-amber">
                                    📌 {{ $pwDays }} {{ $pwDays === 1 ? 'day' : 'days' }} left
                                </span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.78rem;">
                                Curated by <strong>{{ $pwAuthor }}</strong> &middot;
                                <span class="text-success fw-semibold">{{ $availableCount }} of {{ count($pwItems) }} available to claim</span>
                            </div>
                        </div>
                    </div>
                    <a
                        href="/wishlists#wishlist-{{ $pwId }}"
                        class="fp-pw-btn btn text-white btn-sm text-decoration-none shadow-2xs"
                        style="background: #a31621;"
                    >
                        <i class="bi bi-gift" aria-hidden="true"></i>
                        <span>View &amp; Claim</span>
                        <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                    </a>
                </div>

                @if(!empty($pwItems))
                    <div class="d-grid gap-1.5">
                        @foreach(array_slice($pwItems, 0, 3) as $pItem)
                            <div class="fp-pw-item-pill">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi bi-stars text-warning flex-shrink-0" aria-hidden="true"></i>
                                    <span class="fp-pw-item-title text-truncate" title="{{ $pItem['item_title'] ?? '' }}">
                                        {{ $pItem['item_title'] ?? '' }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    @if(!empty($pItem['approx_price']))
                                        <span class="fp-pw-price-tag">
                                            {{ $pItem['currency'] ?? '£' }}{{ number_format((float) $pItem['approx_price'], 2) }}
                                        </span>
                                    @endif
                                    @if(!empty($pItem['claimed_at']))
                                        <span class="badge rounded-pill text-bg-success">✓ Claimed</span>
                                    @else
                                        <span class="badge rounded-pill fp-pw-badge-available">Available</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        @if(count($pinnedWishlists) > 3)
            <div class="pt-1 text-end">
                <a href="/wishlists" class="small fw-bold text-decoration-none" style="color: #a31621;">
                    +{{ count($pinnedWishlists) - 3 }} more pinned {{ count($pinnedWishlists) - 3 === 1 ? 'wishlist' : 'wishlists' }} &rarr;
                </a>
            </div>
        @endif
    </div>
</section>
@endif
