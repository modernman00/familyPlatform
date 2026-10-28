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

@includeIf('member.modals.thenNowComparisonModal')

<script>
(function () {
    var root = document.getElementById('thenNowSection') || document.getElementById(@json($tnId));
    if (!root) return;
    var list = root.querySelector('.then-now-timeline__list');
    var emptyEl = document.getElementById('thenNowEmptyState');
    var img = function (name) { return '/resources/images/post/' + encodeURIComponent(name || ''); };
    var imgVariant = function (name, ext) {
        var base = img(name);
        return base.replace(/\.(jpe?g|png|webp|avif)$/i, '.' + ext);
    };
    var fig = function (name, label, alt) {
        var f = document.createElement('figure');
        f.className = 'then-now-timeline__fig';
        var imgWrapper = document.createElement('div');
        imgWrapper.className = 'then-now-timeline__img-wrap vse-skeleton-surface';
        var pic = document.createElement('picture');
        pic.style.display = 'contents';
        var srcAvif = document.createElement('source');
        srcAvif.srcset = imgVariant(name, 'avif');
        srcAvif.type = 'image/avif';
        var srcWebp = document.createElement('source');
        srcWebp.srcset = imgVariant(name, 'webp');
        srcWebp.type = 'image/webp';
        var i = document.createElement('img');
        i.src = img(name); i.alt = alt; i.loading = 'lazy'; i.decoding = 'async';
        i.onerror = function () { f.style.visibility = 'hidden'; };
        pic.appendChild(srcAvif);
        pic.appendChild(srcWebp);
        pic.appendChild(i);
        imgWrapper.appendChild(pic);
        var c = document.createElement('figcaption');
        c.textContent = label;
        f.appendChild(imgWrapper); f.appendChild(c);
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
            items.forEach(function (it, idx) {
                var card = document.createElement('div');
                card.className = 'then-now-timeline__pair position-relative';
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-label', 'View and expand Then & Now comparison between ' + (it.before_label || 'Then') + ' and ' + (it.after_label || 'Now'));
                card.title = 'Click to expand comparison';

                card.appendChild(fig(it.before_img, it.before_label || 'Then', 'Then photo'));
                card.appendChild(fig(it.after_img, it.after_label || 'Now', 'Now photo'));

                // Visual tap-to-expand badge overlay
                var pill = document.createElement('div');
                pill.className = 'then-now-timeline__expand-pill';
                pill.innerHTML = '<span class="badge rounded-pill bg-dark bg-opacity-75 text-white shadow-sm d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; padding: 4px 10px;"><i class="bi bi-arrows-angle-expand"></i> Expand</span>';
                card.appendChild(pill);

                card.addEventListener('click', function () {
                    if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
                    if (window.__expandThenNowTimeline) {
                        window.__expandThenNowTimeline(items, idx);
                    }
                });

                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        if (typeof window.triggerHaptic === 'function') window.triggerHaptic('selection');
                        if (window.__expandThenNowTimeline) {
                            window.__expandThenNowTimeline(items, idx);
                        }
                    }
                });

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
.then-now-timeline__pair {
    display: flex;
    gap: 8px;
    flex: 0 0 auto;
    position: relative;
    padding: 8px;
    border-radius: 16px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: var(--surface-bg, #f8fafc);
    cursor: pointer;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
}
.then-now-timeline__pair:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    border-color: rgba(184, 32, 44, 0.3);
}
.then-now-timeline__pair:focus-visible {
    outline: 2px solid var(--primary-color, #a31621);
    outline-offset: 2px;
}
.then-now-timeline__pair:active {
    transform: scale(0.98);
}
.then-now-timeline__fig { margin: 0; width: 120px; text-align: center; }
.then-now-timeline__img-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    aspect-ratio: 1 / 1;
    border-radius: 12px;
    overflow: hidden;
    background: #0f172a;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.then-now-timeline__img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.then-now-timeline__fig figcaption {
    font-size: .85rem;
    font-weight: 600;
    padding-top: 5px;
    color: var(--text-color, #1e293b);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.then-now-timeline__expand-pill {
    position: absolute;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.then-now-timeline__pair:hover .then-now-timeline__expand-pill {
    transform: translateX(-50%) scale(1.05);
}
</style>
