@if(!empty($pinnedWishlists))
<style>
.fp-pw-showcase {
    background-color: var(--card-bg, #ffffff);
    border: 1px solid rgba(245, 158, 11, 0.35);
    border-radius: var(--radius-lg, 20px);
    overflow: hidden;
    margin-bottom: var(--space-6, 24px);
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 4px 10px rgba(245, 158, 11, 0.08);
}
.fp-pw-showcase .small,
.fp-pw-showcase small {
    font-style: normal !important;
}
.fp-pw-header {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: #f8fafc;
    padding: var(--space-4, 16px) var(--space-5, 20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3, 12px);
}
.fp-pw-header-brand {
    display: flex;
    align-items: center;
    gap: var(--space-3, 12px);
    min-width: 0;
    flex: 1 1 240px;
}
.fp-pw-icon-tile {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-md, 12px);
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #78350f;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.25);
}
.fp-pw-header-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 2px;
}
.fp-pw-header-tag {
    color: #fde68a !important;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}
.fp-pw-active-count {
    color: #94a3b8 !important;
    font-size: var(--text-xs, 0.75rem) !important;
    font-weight: 500 !important;
    font-style: normal !important;
}
.fp-pw-heading {
    font-size: var(--text-base, 1rem);
    font-weight: 700;
    color: #ffffff !important;
    margin: 0;
    line-height: 1.3;
}
.fp-pw-header-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #fde68a !important;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    padding: 8px 14px;
    border-radius: var(--radius-md, 10px);
    border: 1px solid rgba(251, 191, 36, 0.35);
    background: rgba(251, 191, 36, 0.08);
    transition: all 0.15s ease;
    white-space: nowrap;
    flex-shrink: 0;
    min-height: 44px;
}
.fp-pw-header-link:hover {
    color: #ffffff !important;
    background: rgba(251, 191, 36, 0.18);
    border-color: rgba(251, 191, 36, 0.55);
    transform: translateY(-1px);
}
.fp-pw-header-link:active {
    transform: scale(0.98);
}
.fp-pw-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.fp-pw-close {
    min-width: 44px;
    min-height: 44px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md, 10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
}
.fp-pw-close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}
.fp-pw-close:active {
    transform: scale(0.98);
}
.fp-pw-close:focus-visible {
    outline: 2px solid #fde68a;
    outline-offset: 2px;
}
.fp-pw-body {
    padding: var(--space-5, 20px);
    display: grid;
    gap: var(--space-4, 16px);
}
.fp-pw-list-card {
    background-color: var(--surface-alt, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--radius-md, 16px);
    padding: var(--space-4, 16px) var(--space-5, 20px);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.fp-pw-list-card:hover {
    border-color: rgba(245, 158, 11, 0.5);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}
.fp-pw-list-top {
    padding-bottom: var(--space-3, 12px);
    margin-bottom: var(--space-3, 12px);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.fp-pw-list-identity {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3, 12px);
    min-width: 0;
}
.fp-pw-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #a31621 0%, #7d1018 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: var(--text-sm, 0.875rem);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(163, 22, 33, 0.22);
}
.fp-pw-identity-details {
    min-width: 0;
    flex: 1 1 auto;
}
.fp-pw-title-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2, 8px);
    margin-bottom: 6px;
}
.fp-pw-list-title {
    font-size: var(--text-base, 1rem);
    font-weight: 700;
    color: var(--text-main, #0f172a);
    line-height: 1.35;
    margin: 0;
    overflow-wrap: anywhere;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.fp-pw-badges-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}
.fp-pw-curated-line {
    font-size: var(--text-sm, 0.875rem) !important;
    color: var(--text-muted-aa, #475569) !important;
    font-style: normal !important;
    line-height: 1.45;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.fp-pw-curated-line strong {
    color: var(--text-main, #0f172a);
    font-weight: 600;
}
.fp-pw-bullet {
    color: #94a3b8;
    user-select: none;
}
.fp-pw-available-summary {
    color: #047857 !important;
    font-weight: 600;
}
.fp-pw-badge-neutral {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
    font-weight: 600;
    font-size: var(--text-xs, 0.75rem);
    padding: 4px 10px;
    white-space: nowrap;
}
.fp-pw-badge-amber {
    background-color: #fef3c7 !important;
    color: #92400e !important;
    border: 1px solid #fde68a !important;
    font-weight: 700;
    font-size: var(--text-xs, 0.75rem);
    padding: 4px 10px;
    white-space: nowrap;
}
.fp-pw-items-stack {
    display: grid;
    gap: var(--space-3, 10px);
    margin-bottom: var(--space-4, 16px);
}
.fp-pw-item-pill {
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: var(--radius-md, 12px);
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3, 12px);
}
.fp-pw-item-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex: 1 1 auto;
}
.fp-pw-item-title {
    color: var(--text-main, #0f172a);
    font-weight: 600;
    font-size: var(--text-sm, 0.875rem);
    line-height: 1.35;
    overflow-wrap: anywhere;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.fp-pw-item-right {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    flex-shrink: 0;
}
.fp-pw-price-tag {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    font-weight: 700;
    font-size: var(--text-xs, 0.75rem);
    padding: 4px 8px;
    border-radius: 8px;
    white-space: nowrap;
}
.fp-pw-badge-available {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    font-weight: 700;
    font-size: var(--text-xs, 0.75rem);
    padding: 4px 8px;
    white-space: nowrap;
}
.fp-pw-card-footer {
    display: flex;
    justify-content: flex-end;
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
    font-size: var(--text-sm, 0.875rem);
    padding: 0 18px;
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.fp-pw-btn:hover {
    filter: brightness(1.04);
    transform: translateY(-1px);
}
.fp-pw-btn:active {
    transform: scale(0.98);
}
.fp-pw-btn-claim {
    background: linear-gradient(135deg, #a31621 0%, #7d1018 100%) !important;
    color: #ffffff !important;
    border: none;
    box-shadow: 0 4px 12px rgba(163, 22, 33, 0.22);
}
.fp-pw-btn-claim:hover {
    color: #ffffff !important;
    box-shadow: 0 6px 16px rgba(163, 22, 33, 0.32);
}

/* 5-Selector Dark Mode Parity */
body.dark-mode .fp-pw-showcase,
body.dark-theme .fp-pw-showcase,
[data-bs-theme="dark"] .fp-pw-showcase,
[data-theme="dark"] .fp-pw-showcase,
html.dark .fp-pw-showcase {
    background-color: #0f172a;
    border-color: rgba(245, 158, 11, 0.45);
}
body.dark-mode .fp-pw-list-card,
body.dark-theme .fp-pw-list-card,
[data-bs-theme="dark"] .fp-pw-list-card,
[data-theme="dark"] .fp-pw-list-card,
html.dark .fp-pw-list-card {
    background-color: #1e293b;
    border-color: #334155;
}
body.dark-mode .fp-pw-list-top,
body.dark-theme .fp-pw-list-top,
[data-bs-theme="dark"] .fp-pw-list-top,
[data-theme="dark"] .fp-pw-list-top,
html.dark .fp-pw-list-top {
    border-bottom-color: #334155;
}
body.dark-mode .fp-pw-list-title,
body.dark-theme .fp-pw-list-title,
[data-bs-theme="dark"] .fp-pw-list-title,
[data-theme="dark"] .fp-pw-list-title,
html.dark .fp-pw-list-title,
body.dark-mode .fp-pw-curated-line strong,
body.dark-theme .fp-pw-curated-line strong,
[data-bs-theme="dark"] .fp-pw-curated-line strong,
[data-theme="dark"] .fp-pw-curated-line strong,
html.dark .fp-pw-curated-line strong,
body.dark-mode .fp-pw-item-title,
body.dark-theme .fp-pw-item-title,
[data-bs-theme="dark"] .fp-pw-item-title,
[data-theme="dark"] .fp-pw-item-title,
html.dark .fp-pw-item-title {
    color: #f8fafc !important;
}
body.dark-mode .fp-pw-curated-line,
body.dark-theme .fp-pw-curated-line,
[data-bs-theme="dark"] .fp-pw-curated-line,
[data-theme="dark"] .fp-pw-curated-line,
html.dark .fp-pw-curated-line {
    color: #94a3b8 !important;
}
body.dark-mode .fp-pw-bullet,
body.dark-theme .fp-pw-bullet,
[data-bs-theme="dark"] .fp-pw-bullet,
[data-theme="dark"] .fp-pw-bullet,
html.dark .fp-pw-bullet {
    color: #64748b !important;
}
body.dark-mode .fp-pw-available-summary,
body.dark-theme .fp-pw-available-summary,
[data-bs-theme="dark"] .fp-pw-available-summary,
[data-theme="dark"] .fp-pw-available-summary,
html.dark .fp-pw-available-summary {
    color: #6ee7b7 !important;
}
body.dark-mode .fp-pw-item-pill,
body.dark-theme .fp-pw-item-pill,
[data-bs-theme="dark"] .fp-pw-item-pill,
[data-theme="dark"] .fp-pw-item-pill,
html.dark .fp-pw-item-pill {
    background-color: #0f172a;
    border-color: #334155;
}
body.dark-mode .fp-pw-badge-neutral,
body.dark-theme .fp-pw-badge-neutral,
[data-bs-theme="dark"] .fp-pw-badge-neutral,
[data-theme="dark"] .fp-pw-badge-neutral,
html.dark .fp-pw-badge-neutral {
    background-color: #334155 !important;
    color: #f8fafc !important;
    border-color: #475569 !important;
}
body.dark-mode .fp-pw-badge-amber,
body.dark-theme .fp-pw-badge-amber,
[data-bs-theme="dark"] .fp-pw-badge-amber,
[data-theme="dark"] .fp-pw-badge-amber,
html.dark .fp-pw-badge-amber {
    background-color: rgba(245, 158, 11, 0.22) !important;
    color: #fde68a !important;
    border-color: rgba(245, 158, 11, 0.35) !important;
}
body.dark-mode .fp-pw-price-tag,
body.dark-theme .fp-pw-price-tag,
[data-bs-theme="dark"] .fp-pw-price-tag,
[data-theme="dark"] .fp-pw-price-tag,
html.dark .fp-pw-price-tag {
    background: rgba(251, 191, 36, 0.16);
    color: #fde68a;
    border-color: rgba(251, 191, 36, 0.3);
}
body.dark-mode .fp-pw-badge-available,
body.dark-theme .fp-pw-badge-available,
[data-bs-theme="dark"] .fp-pw-badge-available,
[data-theme="dark"] .fp-pw-badge-available,
html.dark .fp-pw-badge-available {
    background: rgba(16, 185, 129, 0.18);
    color: #6ee7b7;
    border-color: rgba(16, 185, 129, 0.3);
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
[data-theme="dark"] .fp-fw-badge-emerald,
html.dark .fp-fw-badge-emerald {
    background: rgba(16, 185, 129, 0.22);
    color: #6ee7b7;
}
body.dark-mode .fp-fw-index-pill,
body.dark-theme .fp-fw-index-pill,
[data-bs-theme="dark"] .fp-fw-index-pill,
[data-theme="dark"] .fp-fw-index-pill,
html.dark .fp-fw-index-pill {
    background: rgba(251, 191, 36, 0.22);
    color: #fde68a;
}
@media (max-width: 575.98px) {
    .fp-pw-header {
        padding: 12px 14px;
    }
    .fp-pw-body {
        padding: 12px 12px;
    }
    .fp-pw-list-card {
        padding: 14px 12px;
    }
    .fp-pw-title-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .fp-pw-item-pill {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .fp-pw-item-left {
        width: 100%;
    }
    .fp-pw-item-right {
        width: 100%;
        justify-content: flex-end;
    }
    .fp-pw-btn-claim {
        width: 100%;
    }
}
</style>
@php
    $fpPwSignature = implode('-', array_map(static fn (array $w): string => (string) ($w['id'] ?? '0'), $pinnedWishlists));
@endphp
<section
    class="fp-pw-showcase"
    aria-labelledby="fp-pinned-wishlists-heading"
    x-data="{ dismissedSig: Alpine.$persist('').as('fp_pinned_registry_dismissed'), sig: @js($fpPwSignature) }"
    x-show="dismissedSig !== sig"
    x-transition.opacity.duration.200ms
    x-cloak
>
    {{-- Showcase Dark Slate Header Bar --}}
    <div class="fp-pw-header">
        <div class="fp-pw-header-brand">
            <span class="fp-pw-icon-tile" aria-hidden="true">
                <i class="bi bi-gift-fill"></i>
            </span>
            <div class="min-w-0">
                <div class="fp-pw-header-meta">
                    <span class="fp-pw-header-tag">Pinned Registry</span>
                    <span class="fp-pw-active-count">
                        &middot; {{ count($pinnedWishlists) }} {{ count($pinnedWishlists) === 1 ? 'active list' : 'active lists' }}
                    </span>
                </div>
                <h2 id="fp-pinned-wishlists-heading" class="fp-pw-heading">
                    Pinned Family Gift Wishlists
                </h2>
            </div>
        </div>
        <div class="fp-pw-header-actions">
            <a href="/wishlists" class="fp-pw-header-link text-decoration-none">
                <span>View all wishlists</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <button
                type="button"
                class="fp-pw-close"
                @click="dismissedSig = sig"
                aria-label="Close pinned registry"
                title="Close"
            >
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    {{-- Pinned Wishlists Body --}}
    <div class="fp-pw-body">
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
                <div class="fp-pw-list-top">
                    <div class="fp-pw-list-identity">
                        <span class="fp-pw-avatar" aria-hidden="true">{{ $pwInitials !== '' ? $pwInitials : 'FM' }}</span>
                        <div class="fp-pw-identity-details">
                            <div class="fp-pw-title-row">
                                <h3 class="fp-pw-list-title" title="{{ $pw['title'] ?? 'Family Gift Wishlist' }}">
                                    {{ $pw['title'] ?? 'Family Gift Wishlist' }}
                                </h3>
                                <div class="fp-pw-badges-group">
                                    <span class="badge rounded-pill fp-pw-badge-neutral">{{ $pw['occasion'] ?? 'Gift' }}</span>
                                    <span class="badge rounded-pill fp-pw-badge-amber">
                                        📌 {{ $pwDays }} {{ $pwDays === 1 ? 'day' : 'days' }} left
                                    </span>
                                </div>
                            </div>
                            <div class="fp-pw-curated-line">
                                <span>Curated by <strong>{{ $pwAuthor }}</strong></span>
                                <span class="fp-pw-bullet" aria-hidden="true">&middot;</span>
                                <span class="fp-pw-available-summary">{{ $availableCount }} of {{ count($pwItems) }} available to claim</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($pwItems))
                    <div class="fp-pw-items-stack">
                        @foreach(array_slice($pwItems, 0, 3) as $pItem)
                            <div class="fp-pw-item-pill">
                                <div class="fp-pw-item-left">
                                    <i class="bi bi-stars text-warning flex-shrink-0" aria-hidden="true"></i>
                                    <span class="fp-pw-item-title" title="{{ $pItem['item_title'] ?? '' }}">
                                        {{ $pItem['item_title'] ?? '' }}
                                    </span>
                                </div>
                                <div class="fp-pw-item-right">
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

                <div class="fp-pw-card-footer">
                    <a
                        href="/wishlists#wishlist-{{ $pwId }}"
                        class="fp-pw-btn fp-pw-btn-claim text-decoration-none"
                        data-spin="true"
                    >
                        <i class="bi bi-gift" aria-hidden="true"></i>
                        <span>View Wishlist &amp; Claim</span>
                        <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        @endforeach

        @if(count($pinnedWishlists) > 3)
            <div class="pt-1 text-end">
                <a href="/wishlists" class="fw-bold text-decoration-none" style="color: #a31621; font-size: var(--text-sm, 0.875rem);">
                    +{{ count($pinnedWishlists) - 3 }} more pinned {{ count($pinnedWishlists) - 3 === 1 ? 'wishlist' : 'wishlists' }} &rarr;
                </a>
            </div>
        @endif
    </div>
</section>
@endif
