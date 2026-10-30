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
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="btn-group p-1 bg-light rounded-pill border" role="group" aria-label="View mode">
                            <button type="button" id="tnViewSliderBtn" class="btn btn-sm rounded-pill fw-semibold active px-3" 
                                    style="min-height: 44px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__setThenNowModalViewMode('slider')">
                                <i class="bi bi-sliders me-1"></i> {{ __('feed.slide_to_compare', 'Slide to compare') }}
                            </button>
                            <button type="button" id="tnViewMorphBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3" 
                                    style="min-height: 44px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__setThenNowModalViewMode('morph')">
                                <i class="bi bi-magic me-1"></i> {{ __('feed.morph_view', 'Morph') }}
                            </button>
                            <button type="button" id="tnViewSideBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3" 
                                    style="min-height: 44px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__setThenNowModalViewMode('side')">
                                <i class="bi bi-layout-split me-1"></i> {{ __('feed.side_by_side', 'Side by side') }}
                            </button>
                        </div>

                        <button type="button" id="tnModalTimeWarpBtn" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1"
                                style="min-height: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;" onclick="window.__toggleThenNowModalWarp()">
                            <i class="bi bi-play-circle-fill text-danger" id="tnModalTimeWarpIcon"></i>
                            <span id="tnModalTimeWarpLabel">Time-Warp</span>
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
                    <span id="tnModalDeltaBadge" class="then-now__delta-pill d-none" style="pointer-events: none; z-index: 15;"></span>
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

                <!-- Voice Reflection Bar inside Modal -->
                <div id="tnModalVoiceBox" class="p-2.5 rounded-3 mt-2 d-none align-items-center justify-content-between gap-2" style="background: rgba(163, 22, 33, 0.06); border: 1px solid rgba(163, 22, 33, 0.16);">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-mic-fill" style="color: #a31621;"></i>
                        <span class="small fw-semibold text-dark">15s Family Voice Story</span>
                    </div>
                    <audio id="tnModalVoiceAudio" controls preload="metadata" style="height: 32px; max-width: 240px;"></audio>
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                        {{ __('common.close', 'Close') }}
                    </button>
                    <button type="button" id="tnSaveKeepsakeBtn" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5"
                            style="min-height: 44px; white-space: nowrap; flex-shrink: 0;"
                            onclick="window.__downloadThenNowKeepsake()">
                        <i class="bi bi-download" style="color: #a31621;"></i>
                        <span>Save Heirloom Card</span>
                    </button>
                </div>
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
    var currentModalMode = 'slider';
    var modalWarpFrame = null;
    var isModalWarping = false;

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

    function computeDeltaLabel(beforeLabel, afterLabel, mode) {
        if (mode === 'echo') return '🧬 Like Parent, Like Child';
        var m1 = String(beforeLabel || '').match(/\b(19\d{2}|20\d{2})\b/);
        var m2 = String(afterLabel || '').match(/\b(19\d{2}|20\d{2})\b/);
        if (!m1 || !m2) return '';
        var diff = Math.abs(parseInt(m2[1], 10) - parseInt(m1[1], 10));
        if (!diff) return '';
        return diff === 1 ? '⏳ 1 Year Apart' : '⏳ ' + diff + ' Years Apart';
    }

    function stopModalWarp() {
        isModalWarping = false;
        if (modalWarpFrame) {
            cancelAnimationFrame(modalWarpFrame);
            modalWarpFrame = null;
        }
        var icon = document.getElementById('tnModalTimeWarpIcon');
        var label = document.getElementById('tnModalTimeWarpLabel');
        if (icon) icon.className = 'bi bi-play-circle-fill text-danger';
        if (label) label.textContent = 'Time-Warp';
    }

    window.__toggleThenNowModalWarp = function() {
        if (isModalWarping) {
            stopModalWarp();
            return;
        }
        window.__setThenNowModalViewMode('morph');
        isModalWarping = true;
        var icon = document.getElementById('tnModalTimeWarpIcon');
        var label = document.getElementById('tnModalTimeWarpLabel');
        if (icon) icon.className = 'bi bi-pause-circle-fill text-warning';
        if (label) label.textContent = 'Pause Warp';

        var voiceEl = document.getElementById('tnModalVoiceAudio');
        if (voiceEl && voiceEl.src && voiceEl.paused) {
            voiceEl.play().catch(function() {});
        }

        var t0 = performance.now();
        var duration = 3600;
        function step(now) {
            if (!isModalWarping) return;
            var elapsed = (now - t0) % duration;
            var pct = ((1 - Math.cos((elapsed / duration) * 2 * Math.PI)) / 2) * 100;
            window.__updateThenNowSlider(pct);
            modalWarpFrame = requestAnimationFrame(step);
        }
        modalWarpFrame = requestAnimationFrame(step);
    };

    window.__updateThenNowSlider = function(val) {
        var beforeImg = document.getElementById('tnModalBeforeImg');
        var afterImg = document.getElementById('tnModalAfterImg');
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
            if (currentModalMode === 'morph') {
                beforeImg.style.clipPath = 'none';
                beforeImg.style.webkitClipPath = 'none';
                beforeImg.style.opacity = String(((100 - num) / 100).toFixed(3));
                if (afterImg) {
                    afterImg.style.transform = 'scale(' + (1 + num / 1200).toFixed(3) + ')';
                }
            } else {
                var insetStr = 'inset(0 ' + (100 - num) + '% 0 0)';
                beforeImg.style.opacity = '1';
                beforeImg.style.clipPath = insetStr;
                beforeImg.style.webkitClipPath = insetStr;
                if (afterImg) {
                    afterImg.style.transform = '';
                }
            }
        }
        if (handle) {
            handle.style.display = currentModalMode === 'morph' ? 'none' : '';
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
            stopModalWarp();
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
        currentModalMode = mode || 'slider';
        if (mode !== 'morph') {
            stopModalWarp();
        }
        var sliderStage = document.getElementById('tnModalSliderStage');
        var sideStage = document.getElementById('tnModalSideStage');
        var sliderBtn = document.getElementById('tnViewSliderBtn');
        var morphBtn = document.getElementById('tnViewMorphBtn');
        var sideBtn = document.getElementById('tnViewSideBtn');

        [sliderBtn, morphBtn, sideBtn].forEach(function(b) {
            if (!b) return;
            b.classList.remove('active', 'btn-light');
            b.classList.add('text-secondary');
        });

        if (mode === 'side') {
            if (sliderStage) sliderStage.classList.add('d-none');
            if (sideStage) sideStage.classList.remove('d-none');
            if (sideBtn) {
                sideBtn.classList.add('active', 'btn-light');
                sideBtn.classList.remove('text-secondary');
            }
        } else {
            if (sliderStage) sliderStage.classList.remove('d-none');
            if (sideStage) sideStage.classList.add('d-none');
            var activeBtn = mode === 'morph' ? morphBtn : sliderBtn;
            if (activeBtn) {
                activeBtn.classList.add('active', 'btn-light');
                activeBtn.classList.remove('text-secondary');
            }
            window.__updateThenNowSlider(lastSliderVal);
            initSliderDrag();
        }
    };

    function renderCurrentItem() {
        stopModalWarp();
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

        // Labels & Delta Badge
        var beforeLabel = it.before_label || 'Then';
        var afterLabel = it.after_label || 'Now';

        var bTag = document.getElementById('tnModalBeforeTag');
        var aTag = document.getElementById('tnModalAfterTag');
        var sbTag = document.getElementById('tnSideBeforeTag');
        var saTag = document.getElementById('tnSideAfterTag');
        var deltaEl = document.getElementById('tnModalDeltaBadge');

        if (bTag) bTag.textContent = beforeLabel;
        if (aTag) aTag.textContent = afterLabel;
        if (sbTag) sbTag.textContent = beforeLabel;
        if (saTag) saTag.textContent = afterLabel;

        var deltaStr = it.delta_badge || computeDeltaLabel(beforeLabel, afterLabel, it.comparison_mode);
        if (deltaEl) {
            if (deltaStr) {
                deltaEl.textContent = deltaStr;
                deltaEl.classList.remove('d-none');
            } else {
                deltaEl.textContent = '';
                deltaEl.classList.add('d-none');
            }
        }

        // Voice Reflection Bar
        var voiceBox = document.getElementById('tnModalVoiceBox');
        var voiceAudio = document.getElementById('tnModalVoiceAudio');
        if (voiceBox && voiceAudio) {
            if (it.voice_capsule_url) {
                voiceAudio.src = it.voice_capsule_url;
                voiceBox.classList.remove('d-none');
                voiceBox.classList.add('d-flex');
            } else {
                voiceAudio.pause();
                voiceAudio.removeAttribute('src');
                voiceBox.classList.add('d-none');
                voiceBox.classList.remove('d-flex');
            }
        }

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
        stopModalWarp();
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
            comparison_mode: comparison.comparison_mode || 'classic',
            delta_badge: comparison.delta_badge || '',
            fullName: (meta && meta.fullName) || comparison.fullName || '',
            date_created: (meta && meta.date_created) || comparison.date_created || '',
            post_desc: (meta && (meta.post_desc || meta.postMessage)) || comparison.post_desc || '',
            voice_capsule_url: (meta && meta.voice_capsule_url) || comparison.voice_capsule_url || null
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

    /**
     * Load & open the entire "Family Time-Warp Chain" (root + all family responses) in the modal carousel.
     */
    window.__openThenNowChain = function(postNo) {
        if (!postNo) return;
        fetch('/api/posts/' + encodeURIComponent(String(postNo)) + '/then-now-chain', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(body) {
                var items = (body && body.data && Array.isArray(body.data.items)) ? body.data.items : [];
                if (items.length) {
                    window.__expandThenNowTimeline(items, 0);
                }
            })
            .catch(function() {});
    };

    /**
     * Private Family Keepsake Card Generator (Canvas -> High-DPI PNG Download).
     */
    window.__downloadThenNowKeepsake = function() {
        var it = currentItems[currentIndex];
        if (!it) return;
        var btn = document.getElementById('tnSaveKeepsakeBtn');
        var origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';
        }

        var loadImg = function(src) {
            return new Promise(function(resolve, reject) {
                var im = new Image();
                im.crossOrigin = 'anonymous';
                im.onload = function() { resolve(im); };
                im.onerror = reject;
                im.src = src;
            });
        };

        Promise.all([loadImg(getImgUrl(it.before_img)), loadImg(getImgUrl(it.after_img))])
            .then(function(imgs) {
                var bImg = imgs[0];
                var aImg = imgs[1];
                var canvas = document.createElement('canvas');
                canvas.width = 1200;
                canvas.height = 900;
                var ctx = canvas.getContext('2d');
                if (!ctx) return;

                // Deep Heirloom Slate & Warm Cream Frame
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 0, 1200, 900);

                // Top Header Banner
                ctx.fillStyle = '#a31621';
                ctx.fillRect(0, 0, 1200, 88);
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 30px system-ui, -apple-system, sans-serif';
                ctx.fillText('⏳ Family Then & Now Heirloom Keepsake', 40, 55);

                var deltaText = it.delta_badge || computeDeltaLabel(it.before_label, it.after_label, it.comparison_mode);
                if (deltaText) {
                    ctx.fillStyle = '#fce79a';
                    ctx.font = 'bold 24px system-ui, -apple-system, sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText(deltaText, 1160, 54);
                    ctx.textAlign = 'left';
                }

                // Draw cover-fit helper
                var drawCover = function(img, x, y, w, h) {
                    var ir = img.width / img.height;
                    var tr = w / h;
                    var sx = 0, sy = 0, sw = img.width, sh = img.height;
                    if (ir > tr) {
                        sw = img.height * tr;
                        sx = (img.width - sw) / 2;
                    } else {
                        sh = img.width / tr;
                        sy = (img.height - sh) / 2;
                    }
                    ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
                };

                drawCover(bImg, 40, 120, 545, 640);
                drawCover(aImg, 615, 120, 545, 640);

                // Pills
                ctx.fillStyle = 'rgba(15, 23, 42, 0.82)';
                ctx.fillRect(60, 140, 200, 44);
                ctx.fillRect(635, 140, 200, 44);
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 22px system-ui, -apple-system, sans-serif';
                ctx.fillText(String(it.before_label || 'Then').slice(0, 18), 76, 170);
                ctx.fillText(String(it.after_label || 'Now').slice(0, 18), 651, 170);

                // Footer attribution
                ctx.fillStyle = '#f8fafc';
                ctx.font = 'bold 22px system-ui, -apple-system, sans-serif';
                ctx.fillText(String(it.fullName || 'Family Member'), 40, 815);
                ctx.fillStyle = '#cbd5e1';
                ctx.font = '18px system-ui, -apple-system, sans-serif';
                var story = String(it.post_desc || '').trim();
                if (story) {
                    ctx.fillText(story.length > 90 ? story.slice(0, 90) + '…' : story, 40, 850);
                }

                canvas.toBlob(function(blob) {
                    if (!blob) return;
                    var link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'then-and-now-' + (it.post_no || Date.now()) + '.png';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(function() { URL.revokeObjectURL(link.href); }, 2000);
                }, 'image/png');
            })
            .catch(function() {})
            .finally(function() {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            });
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
