@php
    $reelsExpDays = \App\model\Reel::getExpirationDays();
@endphp
<div class="modal fade" id="createReelModal" tabindex="-1" aria-labelledby="createReelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff; max-height: calc(100vh - 3.5rem);">
            <div class="modal-header border-0 bg-light px-4 pt-4 pb-2 flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; background: #f5dede; font-size: 1.3rem;">
                        <i class="bi bi-camera-reels-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="createReelModalLabel">Publish Family Reel</h5>
                        <p class="text-muted small mb-0">Share a milestone, memory, or family story {{ $reelsExpDays > 0 ? '(active for ' . $reelsExpDays . ' ' . ($reelsExpDays === 1 ? 'day' : 'days') . ')' : '(saved permanently)' }}.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createReelForm" enctype="multipart/form-data" class="d-flex flex-column" style="min-height: 0; flex: 1 1 auto; overflow: hidden; margin: 0;">
    @include('csrf')
                <input type="hidden" id="reelThumbnailDataInput" name="thumbnail_data">
                <input type="hidden" id="reelAspectRatioInput" name="aspect_ratio" value="9:16">
                <div class="modal-body px-4 py-3" style="overflow-y: auto;">
                    
                    <!-- Drag and Drop Video Dropzone -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Select Video</label>
                        <div class="border rounded-4 p-3 text-center position-relative" id="reelDropzone" style="background: #f8fafc; border: 2px dashed #cbd5e1 !important; cursor: pointer;">
                            <input type="file" class="position-absolute opacity-0 w-100 h-100 top-0 start-0" id="reelVideoFileInput" name="video_file" accept="video/mp4,video/webm,video/quicktime" style="cursor: pointer;">
                            <div id="dropzonePrompt">
                                <i class="bi bi-cloud-arrow-up-fill fs-2 text-primary"></i>
                                <div class="fw-bold text-dark small mt-1">Click or drag video file here</div>
                                <div class="text-muted" style="font-size: 0.75rem;">MP4, WebM, MOV up to 100MB (Portrait or Landscape)</div>
                            </div>
                            <div id="dropzoneSelected" class="d-none">
                                <i class="bi bi-file-earmark-play-fill fs-2 text-success"></i>
                                <div class="fw-bold text-dark small mt-1" id="dropzoneFileName">video.mp4</div>
                                <div class="text-primary small" style="font-size: 0.75rem;">Click to change video</div>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Video & Aspect-Preserving Cover Studio -->
                    <div class="mb-3 d-none" id="reelVideoPreviewContainer">
                        <div class="p-3 rounded-4 border" style="background: #0f172a; color: #ffffff;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <span class="small fw-bold text-white d-flex align-items-center gap-1">
                                    <i class="bi bi-aspect-ratio text-warning"></i>
                                    <span id="reelDetectedOrientationBadge">Smart Aspect Preview</span>
                                </span>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Framing mode">
                                    <button type="button" class="btn btn-outline-light active py-1 px-2" id="btnReelFrameCinema" style="font-size: 0.72rem;">
                                        <i class="bi bi-Bounding-box-circles me-1"></i>Cinema Fit
                                    </button>
                                    <button type="button" class="btn btn-outline-light py-1 px-2" id="btnReelFrameCover" style="font-size: 0.72rem;">
                                        <i class="bi bi-arrows-angle-expand me-1"></i>9:16 Fill
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                <!-- Active Video Player Preview -->
                                <div class="rounded-3 overflow-hidden shadow position-relative" style="width: 148px; height: 262px; background: #000000; border: 1px solid rgba(255,255,255,0.15);">
                                    <div id="reelPreviewAmbientBg" class="position-absolute inset-0 w-100 h-100" style="background-size: cover; background-position: center; filter: blur(16px) brightness(0.45); transform: scale(1.15);"></div>
                                    <video id="reelPreviewVideo" class="w-100 h-100 position-relative" style="object-fit: contain; z-index: 2;" playsinline loop muted controls></video>
                                    <span class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-1" style="z-index: 3; font-size: 0.62rem;">Video</span>
                                </div>

                                <!-- Live Tray Cover Card Preview (Zero-Stretch Verification) -->
                                <div class="rounded-3 overflow-hidden shadow position-relative" style="width: 115px; height: 190px; background: #000000; border: 2px solid #a31621;">
                                    <canvas id="reelCoverPreviewCanvas" width="540" height="960" class="w-100 h-100" style="object-fit: cover; display: block;"></canvas>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-1" style="font-size: 0.6rem;">Tray Cover</span>
                                </div>
                            </div>

                            <!-- Cover Frame Timeline Scrubber -->
                            <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                <div class="d-flex justify-content-between align-items-center small text-white-50 mb-1" style="font-size: 0.72rem;">
                                    <span><i class="bi bi-sliders me-1 text-warning"></i>Scrub to pick Cover Frame:</span>
                                    <span id="reelScrubberTimeLabel" class="text-white fw-bold">0.8s</span>
                                </div>
                                <input type="range" class="form-range" id="reelCoverTimeScrubber" min="0" max="10" step="0.1" value="0.8">
                            </div>
                        </div>
                    </div>

                    <!-- Video URL Alternative (Optional) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Or Paste Video Link (MP4, YouTube, Cloudflare)</label>
                        <input type="url" class="form-control rounded-pill" id="reelVideoUrlInput" name="video_url" placeholder="https://example.com/family-video.mp4" style="font-size: 0.85rem; padding: 8px 16px;">
                    </div>

                    <!-- Category / Milestone Tag -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Milestone Category</label>
                        <select class="form-select rounded-pill" name="category" style="font-size: 0.88rem; padding: 8px 16px;">
                            <option value="milestone" selected>🎉 Milestone Celebration</option>
                            <option value="first_steps">👶 First Steps & Kids</option>
                            <option value="storytelling">📖 Family Story & Wisdom</option>
                            <option value="recipe">🍳 Secret Family Recipe</option>
                            <option value="reunion">🏡 Reunion & Gathering</option>
                            <option value="graduation">🎓 Graduation & Achievement</option>
                            <option value="wedding">💍 Wedding & Anniversary</option>
                            <option value="night_out">🌙 Night Out</option>
                            <option value="weekend_out">🏖️ Weekend Out</option>
                            <option value="holiday">✈️ Holiday & Travel</option>
                            <option value="birthday">🎂 Birthday Party</option>
                            <option value="day_trip">🚗 Day Trip & Outing</option>
                            <option value="sports">⚽ Sports & Activities</option>
                            <option value="food">🍽️ Family Meal & Cookout</option>
                            <option value="faith">🙏 Faith & Celebration</option>
                            <option value="funny">😂 Funny Moments</option>
                            <option value="everyday">🏠 Everyday Life</option>
                            <option value="custom">➕ Other (type your own)…</option>
                        </select>
                        <input type="text" name="custom_category" id="reelCustomCategory" maxlength="30"
                               class="form-control rounded-pill mt-2 d-none" placeholder="e.g. Game Night"
                               style="font-size: 0.88rem; padding: 8px 16px;" autocomplete="off">
                        <script>
                        (function () {
                            var sel = document.currentScript.parentElement.querySelector('select[name="category"]');
                            var inp = document.getElementById('reelCustomCategory');
                            sel?.addEventListener('change', function () {
                                var custom = sel.value === 'custom';
                                inp?.classList.toggle('d-none', !custom);
                                if (custom) { inp?.focus(); }
                            });
                        })();
                        </script>
                    </div>

                    <!-- Caption Textarea -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Caption</label>
                        <textarea class="form-control rounded-4" name="caption" rows="2" placeholder="Tell your family about this special moment..." style="font-size: 0.88rem;"></textarea>
                    </div>

                    <!-- Audio / Music Track Name -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small mb-1">Audio Title</label>
                        <input type="text" class="form-control rounded-pill" name="music_title" placeholder="Original Family Audio" value="Original Family Audio" style="font-size: 0.85rem; padding: 8px 16px;">
                    </div>

                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-1 flex-shrink-0">
                    <button type="button" class="btn btn-light px-3 py-2 fw-semibold" style="border-radius: 12px; min-height: 42px;" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 shadow-sm" style="border-radius: 12px; min-height: 42px;" id="btnSubmitReel">
                        <i class="bi bi-camera-video-fill me-1"></i> Publish Reel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
