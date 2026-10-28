@php
if (defined('THEN_NOW_COMPARISON_MODAL_RENDERED')) {
    return;
}
define('THEN_NOW_COMPARISON_MODAL_RENDERED', true);
@endphp

<!-- Then & Now Expandable Comparison Modal -->
<div class="modal fade" id="thenNowComparisonModal" tabindex="-1" aria-labelledby="thenNowComparisonModalLabel" aria-hidden="true" data-bs-backdrop="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: var(--card-bg, #ffffff);">
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 mb-1" id="thenNowComparisonModalLabel" style="font-size: 1.25rem;">
                        <i class="bi bi-hourglass-split" style="color: #b8202c;"></i>
                        <span>{{ __('profile.then_and_now_comparison', 'Then & Now Comparison') }}</span>
                    </h5>
                    <p class="text-muted small mb-0" id="thenNowModalMeta" style="font-size: 0.85rem; color: var(--text-muted-aa, #475569);"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" 
                        style="min-width: 44px; min-height: 44px; padding: 12px; margin-right: -8px;"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-3 px-sm-4 py-2">
                <!-- Mode Toggle Controls & Carousel Pagination -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group" aria-label="View mode">
                        <button type="button" id="tnViewSliderBtn" class="btn btn-sm rounded-pill fw-semibold active px-3" 
                                style="min-height: 44px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__setThenNowModalViewMode('slider')">
                            <i class="bi bi-sliders me-1"></i> {{ __('feed.slide_to_compare', 'Slide to compare') }}
                        </button>
                        <button type="button" id="tnViewSideBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3" 
                                style="min-height: 44px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__setThenNowModalViewMode('side')">
                            <i class="bi bi-layout-split me-1"></i> {{ __('feed.side_by_side', 'Side by side') }}
                        </button>
                    </div>

                    <!-- Navigation across items if multiple -->
                    <div id="tnNavControls" class="d-none align-items-center gap-2">
                        <button type="button" id="tnPrevBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1"
                                style="min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="window.__prevThenNowItem()" aria-label="Previous comparison">
                            <i class="bi bi-chevron-left"></i> <span class="d-none d-sm-inline">{{ __('common.previous', 'Previous') }}</span>
                        </button>
                        <span id="tnItemCounter" class="small fw-semibold text-muted px-1" style="min-width: 50px; text-align: center;"></span>
                        <button type="button" id="tnNextBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1"
                                style="min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="window.__nextThenNowItem()" aria-label="Next comparison">
                            <span class="d-none d-sm-inline">{{ __('common.next', 'Next') }}</span> <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Slider Comparison View (VSE Display P3 + AVIF/WebP + Zero-CLS Aspect Ratio) -->
                <div id="tnModalSliderStage" class="then-now__stage post-media-stage vse-skeleton-surface position-relative mb-2 shadow-sm" 
                     style="aspect-ratio: 4 / 5; max-height: 60vh; min-height: 280px; width: 100%; touch-action: none; user-select: none; -webkit-user-select: none; cursor: ew-resize; overflow: hidden; position: relative;">
                    <picture style="display: contents;">
                        <source id="tnModalAfterAvif" srcset="" type="image/avif">
                        <source id="tnModalAfterWebp" srcset="" type="image/webp">
                        <img id="tnModalAfterImg" class="then-now__img" src="" alt="Now photo" loading="lazy" decoding="async"
                             style="pointer-events: none; user-select: none; -webkit-user-drag: none; -webkit-user-select: none;">
                    </picture>
                    <picture style="display: contents;">
                        <source id="tnModalBeforeAvif" srcset="" type="image/avif">
                        <source id="tnModalBeforeWebp" srcset="" type="image/webp">
                        <img id="tnModalBeforeImg" class="then-now__img then-now__img--then" src="" alt="Then photo" loading="lazy" decoding="async"
                             style="clip-path: inset(0 50% 0 0); -webkit-clip-path: inset(0 50% 0 0); pointer-events: none; user-select: none; -webkit-user-drag: none; -webkit-user-select: none;">
                    </picture>
                    <span id="tnModalBeforeTag" class="then-now__tag then-now__tag--then" style="pointer-events: none; z-index: 15;">Then</span>
                    <span id="tnModalAfterTag" class="then-now__tag then-now__tag--now" style="pointer-events: none; z-index: 15;">Now</span>
                    <div id="tnModalHandle" class="then-now__handle" style="left: 50%; pointer-events: none; z-index: 16;" aria-hidden="true">
                        <i class="bi bi-arrows-expand-vertical"></i>
                    </div>
                    <input type="range" id="tnModalRange" min="0" max="100" step="0.5" value="50" class="then-now__range" 
                           style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: ew-resize; z-index: 20; touch-action: none;"
                           aria-label="Drag slider to compare Then and Now photos" 
                           oninput="window.__updateThenNowSlider(this.value)"
                           onchange="window.__updateThenNowSlider(this.value)">
                </div>

                <!-- Side-by-Side Comparison View (VSE Display P3 + AVIF/WebP + Zero-CLS Aspect Ratio) -->
                <div id="tnModalSideStage" class="row g-2 mb-2 d-none">
                    <div class="col-12 col-sm-6">
                        <div class="position-relative rounded-3 overflow-hidden shadow-sm vse-skeleton-surface" style="background: #0f172a; aspect-ratio: 4/3;">
                            <picture style="display: contents;">
                                <source id="tnSideBeforeAvif" srcset="" type="image/avif">
                                <source id="tnSideBeforeWebp" srcset="" type="image/webp">
                                <img id="tnSideBeforeImg" class="w-100 h-100" style="object-fit: cover; pointer-events: none; user-select: none; -webkit-user-drag: none;" src="" alt="Then photo" loading="lazy" decoding="async">
                            </picture>
                            <span id="tnSideBeforeTag" class="then-now__tag then-now__tag--then" style="pointer-events: none;">Then</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="position-relative rounded-3 overflow-hidden shadow-sm vse-skeleton-surface" style="background: #0f172a; aspect-ratio: 4/3;">
                            <picture style="display: contents;">
                                <source id="tnSideAfterAvif" srcset="" type="image/avif">
                                <source id="tnSideAfterWebp" srcset="" type="image/webp">
                                <img id="tnSideAfterImg" class="w-100 h-100" style="object-fit: cover; pointer-events: none; user-select: none; -webkit-user-drag: none;" src="" alt="Now photo" loading="lazy" decoding="async">
                            </picture>
                            <span id="tnSideAfterTag" class="then-now__tag then-now__tag--now" style="pointer-events: none;">Now</span>
                        </div>
                    </div>
                </div>

                <!-- Caption / Story Box -->
                <div id="tnStoryBox" class="p-3 rounded-3 mt-3 d-none" style="background: var(--surface-bg, #f8fafc); border: 1px solid rgba(0,0,0,0.06);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-chat-quote-fill text-muted mt-1" style="font-size: 1.1rem;"></i>
                        <div>
                            <span class="fw-semibold small d-block text-secondary mb-1">{{ __('feed.family_story', 'Family Chronicle & Memory') }}</span>
                            <p id="tnStoryText" class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.5;"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Segun PWA Safe-Area Compliant) -->
            <div class="modal-footer border-0 px-4 pt-2 d-flex justify-content-between align-items-center flex-wrap gap-2"
                 style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                    {{ __('common.close', 'Close') }}
                </button>
                <button type="button" id="tnReplyBtn" class="btn btn-sm text-white rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm"
                        style="background-color: var(--primary-color, #a31621); color: #ffffff !important; border: none; min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                        onclick="window.__joinThenNow()">
                    <i class="bi bi-plus-circle-fill text-warning"></i>
                    <span>{{ __('profile.add_then_and_now', 'Add your Then & Now') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#thenNowComparisonModal {
    z-index: 1060 !important;
}
#thenNowComparisonModal .then-now__stage {
    touch-action: none;
    user-select: none;
    -webkit-user-select: none;
    cursor: ew-resize;
}
#thenNowComparisonModal .then-now__img {
    pointer-events: none !important;
    user-select: none !important;
    -webkit-user-drag: none !important;
    -webkit-user-select: none !important;
}
#thenNowComparisonModal .then-now__tag,
#thenNowComparisonModal .then-now__handle {
    pointer-events: none !important;
}
#thenNowComparisonModal .then-now__range {
    cursor: ew-resize !important;
    touch-action: none !important;
}
</style>

<script>
(function() {
    var currentItems = [];
    var currentIndex = 0;
    var currentReplyPostNo = '';

    function getModalEl() {
        return document.getElementById('thenNowComparisonModal');
    }

    /**
     * Defensive DOM reparenting:
     * If the modal was rendered inside a sidebar or card (with overflow or position:sticky),
     * Bootstrap's backdrop will sit in front of it and freeze clicks.
     * Moving the modal directly to document.body ensures it lives in the root stacking context.
     */
    function ensureModalInBody() {
        var el = getModalEl();
        if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
        }
        return el;
    }

    var lastSliderVal = 50;

    function getImgUrl(name) {
        if (!name) return '';
        if (name.startsWith('http') || name.startsWith('/')) return name;
        return '/resources/images/post/' + encodeURIComponent(name);
    }

    function getImgVariantUrl(name, ext) {
        var base = getImgUrl(name);
        if (!base) return '';
        return base.replace(/\.(jpe?g|png|webp|avif)$/i, '.' + ext);
    }

    window.__updateThenNowSlider = function(val) {
        var beforeImg = document.getElementById('tnModalBeforeImg');
        var handle = document.getElementById('tnModalHandle');
        var range = document.getElementById('tnModalRange');
        var num = parseFloat(val);
        if (isNaN(num)) num = 50;
        num = Math.max(0, Math.min(100, num));

        // Segun PWA Tactile Midpoint Detent: Pulse vibration when crossing 50% center split
        if ((lastSliderVal < 50 && num >= 50) || (lastSliderVal > 50 && num <= 50)) {
            if (Math.abs(lastSliderVal - num) < 15) {
                if (typeof window.triggerHaptic === 'function') {
                    window.triggerHaptic('detent');
                } else if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
                    try { navigator.vibrate(12); } catch (_) {}
                }
            }
        }
        lastSliderVal = num;

        if (beforeImg) {
            var insetStr = 'inset(0 ' + (100 - num) + '% 0 0)';
            beforeImg.style.clipPath = insetStr;
            beforeImg.style.webkitClipPath = insetStr;
        }
        if (handle) {
            handle.style.left = num + '%';
        }
        if (range && Math.abs(parseFloat(range.value) - num) > 0.05) {
            range.value = num;
        }
    };

    /**
     * Direct pointer capture on stage to guarantee 60fps smooth dragging
     * without ghost image drag-and-drop or touch freeze on mobile.
     */
    function initSliderDrag() {
        var sliderStage = document.getElementById('tnModalSliderStage');
        if (!sliderStage || sliderStage._dragInitialized) return;
        sliderStage._dragInitialized = true;

        var isDragging = false;

        function updateFromPointer(e) {
            var rect = sliderStage.getBoundingClientRect();
            if (!rect.width) return;
            var clientX = e.clientX;
            if (clientX === undefined && e.touches && e.touches.length) {
                clientX = e.touches[0].clientX;
            }
            if (clientX === undefined) return;
            var pct = ((clientX - rect.left) / rect.width) * 100;
            pct = Math.max(0, Math.min(100, pct));
            window.__updateThenNowSlider(pct);
        }

        sliderStage.addEventListener('pointerdown', function(e) {
            e.preventDefault();
            isDragging = true;
            try { sliderStage.setPointerCapture(e.pointerId); } catch (_) {}
            updateFromPointer(e);
        });

        sliderStage.addEventListener('pointermove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            updateFromPointer(e);
        });

        function stopDrag(e) {
            if (isDragging) {
                isDragging = false;
                try { sliderStage.releasePointerCapture(e.pointerId); } catch (_) {}
            }
        }

        sliderStage.addEventListener('pointerup', stopDrag);
        sliderStage.addEventListener('pointercancel', stopDrag);
    }

    window.__setThenNowModalViewMode = function(mode) {
        var sliderStage = document.getElementById('tnModalSliderStage');
        var sideStage = document.getElementById('tnModalSideStage');
        var sliderBtn = document.getElementById('tnViewSliderBtn');
        var sideBtn = document.getElementById('tnViewSideBtn');

        if (mode === 'side') {
            if (sliderStage) sliderStage.classList.add('d-none');
            if (sideStage) sideStage.classList.remove('d-none');
            if (sliderBtn) {
                sliderBtn.classList.remove('active', 'btn-light');
                sliderBtn.classList.add('text-secondary');
            }
            if (sideBtn) {
                sideBtn.classList.add('active', 'btn-light');
                sideBtn.classList.remove('text-secondary');
            }
        } else {
            if (sliderStage) sliderStage.classList.remove('d-none');
            if (sideStage) sideStage.classList.add('d-none');
            if (sliderBtn) {
                sliderBtn.classList.add('active', 'btn-light');
                sliderBtn.classList.remove('text-secondary');
            }
            if (sideBtn) {
                sideBtn.classList.remove('active', 'btn-light');
                sideBtn.classList.add('text-secondary');
            }
            initSliderDrag();
        }
    };

    function renderCurrentItem() {
        if (!currentItems.length || !currentItems[currentIndex]) return;
        var it = currentItems[currentIndex];
        currentReplyPostNo = it.post_no || '';

        // Images & VSE AVIF/WebP Ladders
        var beforeSrc = getImgUrl(it.before_img);
        var afterSrc = getImgUrl(it.after_img);
        var beforeAvif = getImgVariantUrl(it.before_img, 'avif');
        var beforeWebp = getImgVariantUrl(it.before_img, 'webp');
        var afterAvif = getImgVariantUrl(it.after_img, 'avif');
        var afterWebp = getImgVariantUrl(it.after_img, 'webp');

        var setSource = function(id, url) {
            var el = document.getElementById(id);
            if (el) el.srcset = url;
        };
        setSource('tnModalBeforeAvif', beforeAvif);
        setSource('tnModalBeforeWebp', beforeWebp);
        setSource('tnModalAfterAvif', afterAvif);
        setSource('tnModalAfterWebp', afterWebp);
        setSource('tnSideBeforeAvif', beforeAvif);
        setSource('tnSideBeforeWebp', beforeWebp);
        setSource('tnSideAfterAvif', afterAvif);
        setSource('tnSideAfterWebp', afterWebp);

        var sliderBefore = document.getElementById('tnModalBeforeImg');
        var sliderAfter = document.getElementById('tnModalAfterImg');
        var sideBefore = document.getElementById('tnSideBeforeImg');
        var sideAfter = document.getElementById('tnSideAfterImg');

        if (sliderBefore) sliderBefore.src = beforeSrc;
        if (sliderAfter) sliderAfter.src = afterSrc;
        if (sideBefore) sideBefore.src = beforeSrc;
        if (sideAfter) sideAfter.src = afterSrc;

        // Labels
        var beforeLabel = it.before_label || 'Then';
        var afterLabel = it.after_label || 'Now';

        var bTag = document.getElementById('tnModalBeforeTag');
        var aTag = document.getElementById('tnModalAfterTag');
        var sbTag = document.getElementById('tnSideBeforeTag');
        var saTag = document.getElementById('tnSideAfterTag');

        if (bTag) bTag.textContent = beforeLabel;
        if (aTag) aTag.textContent = afterLabel;
        if (sbTag) sbTag.textContent = beforeLabel;
        if (saTag) saTag.textContent = afterLabel;

        // Reset slider to 50%
        var range = document.getElementById('tnModalRange');
        if (range) range.value = 50;
        window.__updateThenNowSlider(50);
        initSliderDrag();

        // Meta info (Author & Date)
        var meta = document.getElementById('thenNowModalMeta');
        if (meta) {
            var parts = [];
            if (it.fullName) parts.push(it.fullName);
            if (it.date_created) parts.push(it.date_created);
            meta.textContent = parts.length ? parts.join(' • ') : 'Family milestone memory';
        }

        // Story / Caption
        var storyBox = document.getElementById('tnStoryBox');
        var storyText = document.getElementById('tnStoryText');
        var desc = it.post_desc || it.caption || it.postMessage || '';
        if (storyBox && storyText) {
            if (desc.trim()) {
                storyText.textContent = desc;
                storyBox.classList.remove('d-none');
            } else {
                storyBox.classList.add('d-none');
            }
        }

        // Navigation controls
        var navControls = document.getElementById('tnNavControls');
        var counter = document.getElementById('tnItemCounter');
        var prevBtn = document.getElementById('tnPrevBtn');
        var nextBtn = document.getElementById('tnNextBtn');

        if (currentItems.length > 1) {
            if (navControls) {
                navControls.classList.remove('d-none');
                navControls.classList.add('d-flex');
            }
            if (counter) counter.textContent = (currentIndex + 1) + ' of ' + currentItems.length;
            if (prevBtn) prevBtn.disabled = (currentIndex === 0);
            if (nextBtn) nextBtn.disabled = (currentIndex === currentItems.length - 1);
        } else {
            if (navControls) {
                navControls.classList.add('d-none');
                navControls.classList.remove('d-flex');
            }
        }
    }

    window.__prevThenNowItem = function() {
        if (currentIndex > 0) {
            currentIndex--;
            renderCurrentItem();
        }
    };

    window.__nextThenNowItem = function() {
        if (currentIndex < currentItems.length - 1) {
            currentIndex++;
            renderCurrentItem();
        }
    };

    window.__joinThenNow = function() {
        var modalEl = getModalEl();
        var bsModal = modalEl && window.bootstrap?.Modal ? window.bootstrap.Modal.getInstance(modalEl) : null;
        if (bsModal) bsModal.hide();
        setTimeout(function() {
            if (window.__openThenNow) {
                window.__openThenNow(currentReplyPostNo);
            }
        }, 300);
    };

    window.__expandThenNow = function(comparison, meta) {
        if (!comparison) return;
        var modalEl = ensureModalInBody();
        if (!modalEl) return;

        var item = {
            post_no: (meta && meta.post_no) || comparison.post_no || '',
            before_img: comparison.before_img || '',
            after_img: comparison.after_img || '',
            before_label: comparison.before_label || 'Then',
            after_label: comparison.after_label || 'Now',
            fullName: (meta && meta.fullName) || comparison.fullName || '',
            date_created: (meta && meta.date_created) || comparison.date_created || '',
            post_desc: (meta && (meta.post_desc || meta.postMessage)) || comparison.post_desc || ''
        };
        currentItems = [item];
        currentIndex = 0;
        window.__setThenNowModalViewMode('slider');
        renderCurrentItem();

        if (window.bootstrap?.Modal) {
            var inst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            inst.show();
        }
    };

    window.__expandThenNowTimeline = function(items, index) {
        if (!Array.isArray(items) || !items.length) return;
        var modalEl = ensureModalInBody();
        if (!modalEl) return;

        currentItems = items;
        currentIndex = (index >= 0 && index < items.length) ? index : 0;
        window.__setThenNowModalViewMode('slider');
        renderCurrentItem();

        if (window.bootstrap?.Modal) {
            var inst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            inst.show();
        }
    };

    // Attach lifecycle listeners once ready
    function setupModalListeners() {
        var modalEl = ensureModalInBody();
        if (!modalEl || modalEl._listenersBound) return;
        modalEl._listenersBound = true;

        modalEl.addEventListener('shown.bs.modal', function() {
            initSliderDrag();
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            // Defensive cleanup to eliminate stuck backdrops and locked scrolling
            if (!document.querySelector('.modal.show')) {
                document.querySelectorAll('.modal-backdrop').forEach(function(b) {
                    b.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupModalListeners);
    } else {
        setupModalListeners();
    }
})();
</script>
