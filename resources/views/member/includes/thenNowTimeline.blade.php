@php
    $tnId = 'thenNowTimeline_' . substr(md5((string)($memberId ?? '') . ($tnSuffix ?? '')), 0, 8);
@endphp
<section id="{{ $tnId }}" class="then-now-timeline card border-0 shadow-sm mb-4 d-none" aria-label="Then and Now photos"
         style="border-radius: 20px; background-color: var(--card-bg, #ffffff);">
    <div class="card-body px-3 px-sm-4 py-3">
        <h5 class="fw-bold mb-1" style="font-size: 1rem;"><i class="bi bi-hourglass-split me-1" style="color:#b8202c;"></i> Then &amp; Now</h5>
        <p class="text-muted small mb-3" style="font-size: .8rem;">Before and after photos {{ !empty($memberName) ? 'of ' . e($memberName) : '' }} from the family feed.</p>
        <div class="then-now-timeline__list d-flex gap-3 overflow-auto pb-1" role="list"></div>
    </div>
</section>
<script>
(function () {
    var root = document.getElementById(@json($tnId));
    if (!root) return;
    var list = root.querySelector('.then-now-timeline__list');
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
            if (!items.length) return;
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
        .catch(function () { /* the strip is optional: stay hidden on any failure */ });
})();
</script>
<style>
.then-now-timeline__pair { display: flex; gap: 6px; flex: 0 0 auto; }
.then-now-timeline__fig { margin: 0; width: 120px; text-align: center; }
.then-now-timeline__fig img { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; }
.then-now-timeline__fig figcaption { font-size: .75rem; font-weight: 600; padding-top: 2px; }
</style>
