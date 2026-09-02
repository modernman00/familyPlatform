<div class="modal fade" id="createReelModal" tabindex="-1" aria-labelledby="createReelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-0 bg-light px-4 pt-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; background: #e0e7ff; font-size: 1.3rem;">
                        <i class="bi bi-camera-reels-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="createReelModalLabel">Publish Family Reel</h5>
                        <p class="text-muted small mb-0">Share a milestone, memory, or family story.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="createReelForm" enctype="multipart/form-data">
                <input type="hidden" id="reelThumbnailDataInput" name="thumbnail_data">
                <div class="modal-body px-4 py-3">
                    
                    <!-- Drag and Drop Video Dropzone -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">Select Video</label>
                        <div class="border rounded-4 p-3 text-center position-relative" id="reelDropzone" style="background: #f8fafc; border: 2px dashed #cbd5e1 !important; cursor: pointer;">
                            <input type="file" class="position-absolute opacity-0 w-100 h-100 top-0 start-0" id="reelVideoFileInput" name="video_file" accept="video/mp4,video/webm,video/quicktime" style="cursor: pointer;">
                            <div id="dropzonePrompt">
                                <i class="bi bi-cloud-arrow-up-fill fs-2 text-primary"></i>
                                <div class="fw-bold text-dark small mt-1">Click or drag video file here</div>
                                <div class="text-muted" style="font-size: 0.75rem;">MP4, WebM, MOV up to 100MB</div>
                            </div>
                            <div id="dropzoneSelected" class="d-none">
                                <i class="bi bi-file-earmark-play-fill fs-2 text-success"></i>
                                <div class="fw-bold text-dark small mt-1" id="dropzoneFileName">video.mp4</div>
                                <div class="text-primary small" style="font-size: 0.75rem;">Click to change video</div>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Video Preview -->
                    <div class="mb-3 d-none text-center" id="reelVideoPreviewContainer">
                        <div class="mx-auto rounded-3 overflow-hidden shadow-sm bg-black position-relative" style="width: 170px; height: 280px;">
                            <video id="reelPreviewVideo" class="w-100 h-100" style="object-fit: cover;" playsinline loop muted controls></video>
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
                        </select>
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

                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4" id="btnSubmitReel">
                        <i class="bi bi-camera-video-fill me-1"></i> Publish Reel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
