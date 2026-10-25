{{--
  Family Celebrations (RFC-0008): today's birthdays and anniversaries in your
  families with a "Send wishes" button, and the private wishes you received.
  Included twice (desktop right column, mobile feed); both copies share one
  /api/celebrations request. The wish form lives in modals/celebrationWishModal.
--}}
@php
$cId = $celebrationWidgetId ?? 'celebrationsWidgetDesktop';
@endphp
<div class="card border-0 shadow-sm mb-4 overflow-hidden celebrations-widget"
     id="{{ $cId }}"
     style="border-radius: 20px; background-color: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e5e7eb);"
     x-data="celebrationsWidget()"
     x-init="init()"
     x-show="occasions.length > 0 || wishes.length > 0"
     x-cloak>

    <div class="card-header bg-transparent border-0 d-flex align-items-center gap-2 pt-3 pb-2 px-3 px-sm-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
             style="width: 36px; height: 36px; background: rgba(236, 72, 153, 0.12); font-size: 1.1rem;" aria-hidden="true">🎉</div>
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-color, #1f2937); font-size: 1rem;">Celebrations</h5>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Birthdays &amp; anniversaries in your family</p>
        </div>
    </div>

    <div class="card-body px-3 px-sm-4 pt-1 pb-3">
        <template x-if="occasions.length > 0">
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0" aria-label="Family celebrations">
                <template x-for="o in occasions" :key="o.key">
                    <li class="p-2 rounded-3 d-flex align-items-center justify-content-between gap-2"
                        style="background-color: var(--hover-color, #fdf2f8); border: 1px solid var(--border-color, #fbcfe8);">
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate" style="color: var(--text-color, #1f2937); font-size: 0.86rem;">
                                <span aria-hidden="true" x-text="o.type === 'birthday' ? '🎂 ' : '💞 '"></span><span x-text="o.title"></span>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size: 0.75rem;" x-text="o.subtitle + ' · ' + o.date_label"></div>
                        </div>
                        <button type="button"
                                class="btn btn-sm rounded-pill flex-shrink-0 fw-bold"
                                style="min-height: 44px; min-width: 44px; background: #db2777; color: #fff;"
                                :aria-label="(o.already_wished ? 'Edit your wishes for ' : 'Send wishes for ') + o.title"
                                @click="openWish(o)">
                            <span x-text="o.already_wished ? 'Wishes sent ✓' : 'Send wishes'"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </template>

        <template x-if="wishes.length > 0">
            <div class="mt-3" id="{{ $cId }}_wishes">
                <h6 class="fw-bold mb-2" style="font-size: 0.85rem; color: var(--text-color, #1f2937);">
                    💌 Your wishes <span class="badge rounded-pill bg-danger ms-1" x-show="unread > 0" x-text="unread + ' new'"></span>
                </h6>
                <ul class="list-unstyled d-flex flex-column gap-2 mb-0" aria-label="Wishes you received">
                    <template x-for="(w, i) in wishes" :key="i">
                        <li class="p-2 rounded-3" style="background-color: var(--hover-color, #f9fafb); border: 1px solid var(--border-color, #e5e7eb);">
                            <div class="d-flex justify-content-between gap-2 small">
                                <span class="fw-bold" style="color: var(--text-color, #1f2937);" x-text="w.sender_name"></span>
                                <span class="text-muted" x-text="when(w.created_at)"></span>
                            </div>
                            <p class="mb-0 small" style="color: var(--text-color, #374151); white-space: pre-line; overflow-wrap: anywhere;" x-text="w.message"></p>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
if (typeof window.celebrationsWidget !== 'function') {
    // One request shared by every widget copy on the page; refreshed after a wish is sent.
    window.loadCelebrations = function (force = false) {
        if (!force && window.__celebrationsPromise) return window.__celebrationsPromise;
        window.__celebrationsPromise = fetch('/api/celebrations', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then((r) => (r.ok ? r.json() : null))
            .then((json) => {
                const data = json?.data || {};
                return {
                    occasions: Array.isArray(data.occasions) ? data.occasions : [],
                    wishes: Array.isArray(data.wishes) ? data.wishes : [],
                };
            })
            .catch(() => ({ occasions: [], wishes: [] }));
        return window.__celebrationsPromise;
    };

    // Fetch once, then let every widget copy read that same response. (Reading
    // wishes marks them read, so a second fetch would lose the "new" badge.)
    window.refreshCelebrations = function () {
        window.loadCelebrations(true);
        window.dispatchEvent(new CustomEvent('celebration:refresh'));
    };

    window.celebrationsWidget = function () {
        return {
            occasions: [],
            wishes: [],
            unread: 0,

            async init() {
                await this.apply(window.loadCelebrations());
                // Fired after a wish is sent, and when a notification link changes the hash
                // on an already-open page (no reload happens, so fetch fresh data).
                window.addEventListener('celebration:refresh', () => this.apply(window.loadCelebrations()));
            },

            async apply(promise) {
                const data = await promise;
                this.occasions = data.occasions;
                // The first load marks wishes read on the server; keep the "new" badge from that load.
                this.wishes = data.wishes;
                this.unread = data.wishes.filter((w) => w && w.read === false).length || this.unread;
            },

            openWish(occasion) {
                window.dispatchEvent(new CustomEvent('celebration:open', { detail: occasion }));
            },

            when(stamp) {
                const date = new Date(String(stamp || '').replace(' ', 'T'));
                if (isNaN(date)) return '';
                const days = Math.floor((Date.now() - date.getTime()) / 86400000);
                if (days <= 0) return 'Today';
                if (days === 1) return 'Yesterday';
                return `${days} days ago`;
            },
        };
    };
}
</script>
