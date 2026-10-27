@php
    $tnId = 'thenNowTimeline_' . substr(md5((string)($memberId ?? '') . ($tnSuffix ?? '')), 0, 8);
@endphp
<section id="thenNowSection" data-tn-id="{{ $tnId }}" class="then-now-timeline card border-0 shadow-sm mb-4 d-none" aria-label="Then and Now photos"
         style="border-radius: 20px; background-color: var(--card-bg, #ffffff); scroll-margin-top: 80px;">
    <div class="card-body px-3 px-sm-4 py-3">
        <h5 class="fw-bold mb-1" style="font-size: 1.05rem;"><i class="bi bi-hourglass-split me-1" style="color:#b8202c;"></i> {{ __('profile.then_and_now', 'Then & Now') }}</h5>
        <p class="mb-3" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569); line-height: 1.5;">Before and after photos {{ !empty($memberName) ? 'of ' . e($memberName) : '' }} from the family feed.</p>
        <div class="then-now-timeline__list d-flex gap-3 overflow-auto pb-1" role="list"></div>
        <div class="then-now-empty-state text-center py-3 d-none" id="thenNowEmptyState">
            <p class="mb-2" style="font-size: 0.875rem; color: var(--text-muted-aa, #475569);">No Then &amp; Now comparisons yet.</p>
            <button type="button" class="btn btn-sm text-white rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                    style="background-color: var(--primary-color, #a31621); color: #ffffff !important; border: none; font-size: 0.85rem; min-height: 44px;"
                    data-bs-toggle="modal" data-bs-target="#postModal"
                    onclick="setTimeout(function () { if (window.__openThenNow) window.__openThenNow(''); }, 0)">
                <i class="bi bi-plus-circle-fill text-warning"></i> Add Then &amp; Now
            </button>
        </div>
    </div>
</section>
<script>
(function () {
    var root = document.getElementById('thenNowSection') || document.getElementById(@json($tnId));
    if (!root) return;
    var list = root.querySelector('.then-now-timeline__list');
    var emptyEl = document.getElementById('thenNowEmptyState');
    var img = function (name) { return '/resources/images/post/' + encodeURIComponent(name || ''); };
    var fig = function (name, label, alt) {
        var f = document.createElement('figure');
        f.className = 'then-now-timeline__fig';
        var i = document.createElement('img');
        i.src = img(name); i.alt = alt; i.loading = 'lazy';
        i.onerror = function () { f.style.visibility = 'hidden'; };
        var c = document.createElement('figcaption');
        c.textContent = label;
        f.appendChild(i); f.appendChild(c);
        return f;
    };
    fetch('/api/members/' + encodeURIComponent(@json((string)($memberId ?? ''))) + '/then-now', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (body) {
            var items = (body && body.data && Array.isArray(body.data.items)) ? body.data.items : [];
            if (!items.length) {
                if (emptyEl) emptyEl.classList.remove('d-none');
                root.classList.remove('d-none');
                return;
            }
            items.forEach(function (it) {
                var card = document.createElement('div');
                card.className = 'then-now-timeline__pair';
                card.setAttribute('role', 'listitem');
                card.appendChild(fig(it.before_img, it.before_label || 'Then', 'Then photo'));
                card.appendChild(fig(it.after_img, it.after_label || 'Now', 'Now photo'));
                list.appendChild(card);
            });
            root.classList.remove('d-none');
        })
        .catch(function () {
            if (emptyEl) emptyEl.classList.remove('d-none');
            root.classList.remove('d-none');
        });
})();
</script>
<style>
.then-now-timeline__pair { display: flex; gap: 6px; flex: 0 0 auto; }
.then-now-timeline__fig { margin: 0; width: 120px; text-align: center; }
.then-now-timeline__fig img { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; }
.then-now-timeline__fig figcaption { font-size: .875rem; font-weight: 600; padding-top: 4px; }
</style>
