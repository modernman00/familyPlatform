@extends('layouts.profileBase')

@push('styles')
<style>
.fw-hero {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 20px;
    padding: 24px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}
.fw-card {
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 16px;
    padding: 20px;
}
.fw-card-pinned {
    border-color: #f59e0b;
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.12);
}
.fw-btn {
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
    padding: 0 16px;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.fw-btn:active { transform: scale(0.98); }
.fw-pill {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 16px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #0f172a;
    font-weight: 600;
    font-size: 0.875rem;
    white-space: nowrap;
    flex: 0 0 auto;
    cursor: pointer;
}
.fw-pill.active {
    background: #a31621;
    border-color: #a31621;
    color: #ffffff;
}
.fw-item-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr auto;
    gap: 8px;
    align-items: center;
    margin-bottom: 10px;
}
@media (max-width: 640px) {
    .fw-item-row { grid-template-columns: 1fr; }
}
[x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width: 960px;" x-data="familyWishlistApp()">
    <div class="fw-hero mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="badge rounded-pill text-bg-warning text-dark mb-2">🎁 Family Gift Registry</span>
                <h1 class="h3 fw-bold mb-1 text-white">Family Gift Wishlists</h1>
                <p class="mb-0" style="color: #cbd5e1;">
                    Post your Christmas, Birthday, or occasion gift ideas &amp; links—pinned at the top for as many days as you choose.
                </p>
            </div>
            <button type="button" class="fw-btn btn btn-warning text-dark" @click="showForm = !showForm">
                <i class="bi bi-pin-angle-fill" aria-hidden="true"></i>
                <span x-text="showForm ? 'Close Form' : 'Post a Wishlist'"></span>
            </button>
        </div>
    </div>

    {{-- Post a Pinned Wishlist Form --}}
    <div class="fw-card mb-4" x-show="showForm" x-collapse x-cloak>
        <h2 class="h5 fw-bold mb-3">Share a Pinned Gift Wishlist</h2>
        <div class="alert alert-danger" x-show="errorMsg" x-text="errorMsg" x-cloak role="alert"></div>

        <form @submit.prevent="submitWishlist()">
            @include('csrf')

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="fw-occasion" class="form-label fw-semibold">Occasion</label>
                    <select id="fw-occasion" class="form-select" style="min-height: 44px;" x-model="occasion">
                        @foreach($occasions as $occ)
                            <option value="{{ $occ }}">{{ $occ }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="fw-title" class="form-label fw-semibold">Wishlist Title</label>
                    <input id="fw-title" type="text" maxlength="160" class="form-control" style="min-height: 44px;" x-model="title" placeholder="e.g. Dad's Christmas Gift Ideas">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Persist pinned at the top for how many days?</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <button type="button" class="fw-pill" :class="{ active: pinDays === 7 }" @click="pinDays = 7">7 Days</button>
                    <button type="button" class="fw-pill" :class="{ active: pinDays === 14 }" @click="pinDays = 14">14 Days</button>
                    <button type="button" class="fw-pill" :class="{ active: pinDays === 30 }" @click="pinDays = 30">30 Days</button>
                    <button type="button" class="fw-pill" :class="{ active: pinDays === 60 }" @click="pinDays = 60">60 Days</button>
                </div>
                <input type="number" min="1" max="90" class="form-control" style="max-width: 180px; min-height: 44px;" x-model.number="pinDays" aria-label="Custom pin duration in days">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Gift Ideas &amp; Store Links</label>
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="fw-item-row">
                        <input type="text" maxlength="160" class="form-control" style="min-height: 44px;" x-model="item.item_title" placeholder="Gift idea (e.g. Sony Headphones)" required>
                        <input type="url" maxlength="500" class="form-control" style="min-height: 44px;" x-model="item.item_url" placeholder="Store link (https://...)">
                        <input type="number" step="0.01" min="0" class="form-control" style="min-height: 44px;" x-model="item.approx_price" placeholder="Price (£)">
                        <button type="button" class="fw-btn btn btn-outline-danger" @click="removeItem(idx)" x-show="items.length > 1" aria-label="Remove item">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </template>
                <button type="button" class="fw-btn btn btn-outline-secondary mt-1" @click="addItem()">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    <span>Add Another Item</span>
                </button>
            </div>

            <div class="mb-3">
                <label for="fw-notes" class="form-label fw-semibold">Notes (sizes, colours, preferences)</label>
                <input id="fw-notes" type="text" maxlength="500" class="form-control" style="min-height: 44px;" x-model="notes" placeholder="e.g. Prefer navy blue or size L">
            </div>

            <button type="submit" class="fw-btn btn text-white" style="background: #a31621;" :disabled="submitting" data-spin="true">
                <i class="bi bi-pin-angle-fill" aria-hidden="true"></i>
                <span x-text="submitting ? 'Pinning...' : `Publish & Pin for ${pinDays} Days`"></span>
            </button>
        </form>
    </div>

    {{-- Occasion Filter Pills --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="fw-pill" :class="{ active: occasionFilter === 'all' }" @click="occasionFilter = 'all'">All</button>
        @foreach($occasions as $occ)
            <button type="button" class="fw-pill" :class="{ active: occasionFilter === @json($occ) }" @click="occasionFilter = @json($occ)">{{ $occ }}</button>
        @endforeach
    </div>

    {{-- Wishlists Feed (Pinned at Top) --}}
    @if(empty($wishlists))
        <div class="fw-card text-center py-5">
            <i class="bi bi-gift fs-1 text-warning d-block mb-2" aria-hidden="true"></i>
            <h2 class="h5 fw-bold">No gift wishlists shared yet</h2>
            <p class="text-muted mb-3">Post your Christmas or Birthday ideas so family members know exactly what to get you.</p>
            <button type="button" class="fw-btn btn text-white" style="background: #a31621;" @click="showForm = true">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                <span>Post Your First Wishlist</span>
            </button>
        </div>
    @else
        <div class="d-grid gap-3">
            @foreach($wishlists as $wl)
                @php
                    $wlId = (int) $wl['id'];
                    $isPinned = !empty($wl['is_pinned']);
                    $daysLeft = (int) ($wl['days_remaining'] ?? 0);
                    $isOwner = ((string) ($wl['user_id'] ?? '') === (string) $userId);
                @endphp
                <article
                    id="wishlist-{{ $wlId }}"
                    class="fw-card {{ $isPinned ? 'fw-card-pinned' : '' }}"
                    x-show="occasionFilter === 'all' || occasionFilter === @json($wl['occasion'])"
                    x-cloak
                >
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if($isPinned)
                                    <span class="badge rounded-pill text-bg-warning text-dark">
                                        📌 Pinned &middot; {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} left
                                    </span>
                                @endif
                                <span class="badge rounded-pill text-bg-light border text-dark">{{ $wl['occasion'] }}</span>
                            </div>
                            <h2 class="h5 fw-bold mb-0">{{ $wl['title'] }}</h2>
                            <small class="text-muted">Posted by {{ $wl['author_name'] }}</small>
                        </div>

                        @if($isOwner)
                            <form method="POST" action="/member/wishlists/{{ $wlId }}/delete" class="m-0">
                                @include('csrf')
                                <button type="submit" class="fw-btn btn btn-outline-danger btn-sm" data-spin="true">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                    <span>Remove</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(!empty($wl['notes']))
                        <p class="text-muted small mb-3">{{ $wl['notes'] }}</p>
                    @endif

                    @if(!empty($wl['items']))
                        <div class="d-grid gap-2">
                            @foreach($wl['items'] as $it)
                                @php
                                    $itId = (int) $it['id'];
                                    $isClaimed = !empty($it['claimed_at']);
                                @endphp
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 rounded-3 border bg-light">
                                    <div>
                                        <strong class="d-block text-dark">{{ $it['item_title'] }}</strong>
                                        @if(!empty($it['approx_price']))
                                            <span class="small text-muted">Approx. {{ $it['currency'] }}{{ number_format((float) $it['approx_price'], 2) }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        @if(!empty($it['item_url']))
                                            <a href="{{ $it['item_url'] }}" target="_blank" rel="noopener noreferrer" class="fw-btn btn btn-outline-dark btn-sm">
                                                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                                <span>View Link</span>
                                            </a>
                                        @endif
                                        <template x-if="claimedIds.includes({{ $itId }}) || {{ $isClaimed ? 'true' : 'false' }}">
                                            <span class="badge rounded-pill text-bg-success py-2 px-3">
                                                ✓ Claimed
                                            </span>
                                        </template>
                                        @if(!$isClaimed)
                                            <button
                                                type="button"
                                                class="fw-btn btn btn-warning text-dark btn-sm"
                                                x-show="!claimedIds.includes({{ $itId }})"
                                                @click="claimItem({{ $itId }}, $event.currentTarget)"
                                            >
                                                <i class="bi bi-gift" aria-hidden="true"></i>
                                                <span>Claim Gift</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</div>

<script>
function familyWishlistApp() {
    return {
        showForm: false,
        occasionFilter: 'all',
        occasion: 'Christmas',
        title: '',
        notes: '',
        pinDays: 14,
        items: [{ item_title: '', item_url: '', approx_price: '' }],
        submitting: false,
        errorMsg: '',
        claimedIds: [],

        addItem() {
            if (this.items.length >= 15) return;
            this.items.push({ item_title: '', item_url: '', approx_price: '' });
        },

        removeItem(idx) {
            if (this.items.length <= 1) return;
            this.items.splice(idx, 1);
        },

        getCsrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"], input[name="csrf_token"], input[name="token"]')?.value
                || @json($csrfToken);
        },

        async submitWishlist() {
            this.errorMsg = '';
            this.submitting = true;
            try {
                const csrf = this.getCsrf();
                const res = await fetch('/member/wishlists', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        _token: csrf,
                        csrf_token: csrf,
                        occasion: this.occasion,
                        title: this.title,
                        notes: this.notes,
                        pin_days: this.pinDays,
                        currency: '£',
                        items: this.items,
                    }),
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') {
                    this.errorMsg = json.message || 'Could not publish wishlist.';
                    return;
                }
                window.location.hash = 'wishlist-' + (json.data?.wishlist?.id || '');
                window.location.reload();
            } catch (e) {
                this.errorMsg = 'Network error. Please try again.';
            } finally {
                this.submitting = false;
            }
        },

        async claimItem(itemId, btn) {
            if (btn) btn.disabled = true;
            try {
                const csrf = this.getCsrf();
                const res = await fetch('/member/wishlists/items/' + itemId + '/claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        _token: csrf,
                        csrf_token: csrf,
                    }),
                });
                const json = await res.json();
                if (!res.ok || json.status !== 'success') {
                    this.errorMsg = json.message || 'Could not claim gift.';
                    if (btn) btn.disabled = false;
                    return;
                }
                this.claimedIds.push(itemId);
            } catch (e) {
                this.errorMsg = 'Network error while claiming gift.';
                if (btn) btn.disabled = false;
            }
        }
    };
}
</script>
@endsection
