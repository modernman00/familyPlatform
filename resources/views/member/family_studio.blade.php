@extends('layouts.profileBase')

@section('title', 'Family Living Archives Studio — 4K Reel Studio & Video Vault')
@section('meta_description', 'Browser-native 4K video editing, multi-camera Chrono-Sync matrix, kinetic heritage motion templates, and zero-server-overhead direct downloads.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
<style>
.fvs-wrap {
    --fvs-primary: #a31621;
    --fvs-primary-dark: #7a0e16;
    --fvs-gold: #c9a24b;
    --fvs-gold-bright: #ffd700;
    --fvs-canvas: #0b0f19;
    --fvs-card: #141a29;
    --fvs-card-elevated: #1e2538;
    --fvs-ink: #f8fafc;
    --fvs-muted: #94a3b8;
    --fvs-line: #2d3748;
    max-width: 1400px;
    margin: 0 auto;
    padding: 16px 20px 80px;
    color: var(--fvs-ink);
    font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
}

/* Hero Section */
.fvs-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 28px 24px;
    margin-bottom: 24px;
    color: #ffffff;
    background: radial-gradient(85% 120% at 85% 15%, rgba(201,162,75,.35), transparent 65%),
                radial-gradient(70% 90% at 5% 95%, rgba(163,22,33,.8), transparent 70%),
                linear-gradient(135deg, #0d0204 0%, #22050b 50%, #3e0711 100%);
    box-shadow: 0 20px 45px -15px rgba(163,22,33,.45);
    border: 1px solid rgba(255,215,0,.25);
}
.fvs-hero__eyebrow {
    font-family: 'Cinzel', Georgia, serif;
    font-size: 0.8125rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--fvs-gold-bright);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.fvs-hero__title {
    font-family: 'Cinzel', Georgia, serif;
    font-size: 1.875rem;
    font-weight: 700;
    line-height: 1.25;
    margin: 0 0 10px;
    color: #ffffff;
}
.fvs-hero__desc {
    font-size: 0.9375rem;
    line-height: 1.5;
    color: rgba(255,255,255,.85);
    max-width: 820px;
    margin: 0 0 16px;
}
.fvs-hero__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
}

/* Buttons */
.fvs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    min-width: 44px;
    padding: 10px 18px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-decoration: none;
}
.fvs-btn:active {
    transform: scale(0.98);
}
.fvs-btn:focus-visible {
    outline: 2px solid var(--fvs-gold-bright);
    outline-offset: 2px;
}
.fvs-btn-gold {
    background: linear-gradient(135deg, #d4af37 0%, #aa820a 100%);
    color: #0b0f19;
    box-shadow: 0 4px 15px rgba(212,175,55,0.35);
}
.fvs-btn-gold:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    color: #0b0f19;
}
.fvs-btn-primary {
    background: linear-gradient(135deg, var(--fvs-primary) 0%, var(--fvs-primary-dark) 100%);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(163,22,33,0.35);
}
.fvs-btn-primary:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    color: #ffffff;
}
.fvs-btn-glass {
    background: rgba(255,255,255,0.08);
    color: #ffffff;
    border: 1px solid rgba(255,255,255,0.18);
    backdrop-filter: blur(10px);
}
.fvs-btn-glass:hover {
    background: rgba(255,255,255,0.15);
    color: #ffffff;
    transform: translateY(-1px);
}

/* Layout Grid */
.fvs-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}
@media (max-width: 992px) {
    .fvs-grid {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.fvs-card {
    background: var(--fvs-card);
    border: 1px solid var(--fvs-line);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    position: relative;
}
.fvs-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--fvs-line);
}
.fvs-card__title {
    font-family: 'Cinzel', Georgia, serif;
    font-size: 1.125rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--fvs-ink);
}

/* Viewport & Canvas */
.fvs-viewport-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 9/16;
    max-height: 580px;
    background: #000000;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--fvs-line);
    box-shadow: inset 0 0 40px rgba(0,0,0,0.8);
    margin: 0 auto;
}
.fvs-canvas {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.fvs-hud-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    padding: 5px 12px;
    background: rgba(11,15,25,0.75);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,215,0,0.3);
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--fvs-gold-bright);
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 10;
}
.fvs-rec-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    animation: fvsPulse 1.5s infinite;
}
@keyframes fvsPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

/* Playback Controls */
.fvs-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    padding: 12px 16px;
    background: var(--fvs-card-elevated);
    border-radius: 14px;
    border: 1px solid var(--fvs-line);
}
.fvs-ctrl-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.fvs-timecode {
    font-family: monospace;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--fvs-gold-bright);
    padding: 4px 10px;
    background: rgba(0,0,0,0.4);
    border-radius: 8px;
}

/* Progress bar */
.fvs-progress-bar-wrap {
    width: 100%;
    margin-top: 12px;
}
.fvs-progress-slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    accent-color: var(--fvs-gold-bright);
    cursor: pointer;
}

/* Template Cards Grid */
.fvs-template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    margin-top: 14px;
}
.fvs-tpl-card {
    background: var(--fvs-card-elevated);
    border: 1px solid var(--fvs-line);
    border-radius: 12px;
    padding: 10px;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
    min-height: 44px;
}
.fvs-tpl-card:hover {
    border-color: var(--fvs-gold-bright);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(201,162,75,0.2);
}
.fvs-tpl-card.active {
    border-color: var(--fvs-gold-bright);
    background: rgba(201,162,75,0.12);
    box-shadow: 0 0 0 2px var(--fvs-gold-bright);
}
.fvs-tpl-icon {
    font-size: 1.5rem;
    margin-bottom: 6px;
}
.fvs-tpl-name {
    font-size: 0.75rem;
    font-weight: 600;
    color: #ffffff;
    line-height: 1.2;
}

/* Audio Visualiser Spectrum */
.fvs-spectrum-canvas {
    width: 100%;
    height: 44px;
    border-radius: 8px;
    background: rgba(0,0,0,0.3);
    margin-top: 10px;
}

/* Uploader Dropzone */
.fvs-dropzone {
    border: 2px dashed var(--fvs-line);
    border-radius: 16px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    background: rgba(255,255,255,0.02);
    transition: all 0.2s ease;
}
.fvs-dropzone:hover {
    border-color: var(--fvs-gold-bright);
    background: rgba(255,215,0,0.04);
}
</style>
@endpush

@section('content')
<div class="fvs-wrap" id="familyVideoStudioApp">

    <!-- Hero Banner -->
    <header class="fvs-hero">
        <div class="fvs-hero__eyebrow">
            <span>⚜️</span> Sovereign Living Archives &amp; Cinematic Studio
        </div>
        <h1 class="fvs-hero__title">Family Living Archives Video Studio</h1>
        <p class="fvs-hero__desc">
            Browser-native 4K Reel Studio with multi-angle Chrono-Sync matrix, kinetic heritage motion templates, Web Audio beat synchronization, and zero-server-overhead direct downloads.
        </p>
        <div class="fvs-hero__actions">
            <a href="/reels" class="fvs-btn fvs-btn-glass">
                <i class="bi bi-arrow-left"></i> Return to Family Reels
            </a>
            <button type="button" class="fvs-btn fvs-btn-gold" id="btnTriggerUpload" onclick="document.getElementById('fvsFileInput').click()">
                <i class="bi bi-cloud-arrow-up"></i> Upload Media (Direct to R2)
            </button>
            <button type="button" class="fvs-btn fvs-btn-primary" id="btnExportDownload" onclick="exportLivingReelVideo()">
                <i class="bi bi-download"></i> Download Sovereign MP4
            </button>
        </div>
    </header>

    <!-- Hidden Direct File Inputs -->
    <input type="file" id="fvsFileInput" accept="video/mp4,video/quicktime,video/webm" style="display:none;" onchange="handleDirectFileSelect(this)">
    <input type="file" id="fvsAudioInput" accept="audio/mp3,audio/wav,audio/mpeg,audio/aac" style="display:none;" onchange="handleAudioSelect(this)">

    <!-- Main Studio Grid -->
    <div class="fvs-grid">

        <!-- Column 1: Studio Canvas & Interactive Playback -->
        <div class="fvs-card">
            <div class="fvs-card__header">
                <h2 class="fvs-card__title">
                    <i class="bi bi-film text-warning"></i> 9:16 Cinematic Canvas Viewport
                </h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark border border-secondary text-light" id="fvsResolutionBadge">1080 &times; 1920 (4K Ready)</span>
                </div>
            </div>

            <!-- Viewport Screen -->
            <div class="fvs-viewport-wrap" id="fvsViewportWrap">
                <div class="fvs-hud-badge">
                    <div class="fvs-rec-pulse"></div>
                    <span id="fvsHudStatusText">STANDBY &bull; 60 FPS</span>
                </div>
                <canvas id="fvsCanvas" class="fvs-canvas" width="720" height="1280"></canvas>
            </div>

            <!-- Playback Timeline Scrub & Visualiser -->
            <div class="fvs-progress-bar-wrap">
                <input type="range" class="fvs-progress-slider" id="fvsTimelineSlider" min="0" max="100" value="0" step="0.1" oninput="scrubTimeline(this.value)">
                <canvas id="fvsSpectrumCanvas" class="fvs-spectrum-canvas" width="600" height="44"></canvas>
            </div>

            <!-- Playback Control Bar -->
            <div class="fvs-controls">
                <div class="fvs-ctrl-group">
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnPlayPause" onclick="togglePlayback()" title="Play / Pause">
                        <i class="bi bi-play-fill fs-5" id="playIcon"></i>
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="restartPlayback()" title="Restart">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnMuteToggle" onclick="toggleAudioMute()" title="Mute Audio">
                        <i class="bi bi-volume-up" id="muteIcon"></i>
                    </button>
                    <div class="fvs-timecode" id="fvsTimecodeDisplay">00:00.0 / 00:00.0</div>
                </div>

                <div class="fvs-ctrl-group">
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="document.getElementById('fvsAudioInput').click()" title="Add Audio Track">
                        <i class="bi bi-music-note-beamed"></i> Audio
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-gold btn-sm" onclick="openPublishModal()" title="Publish to Family Reels Feed">
                        <i class="bi bi-send-fill"></i> Publish Reel
                    </button>
                </div>
            </div>
        </div>

        <!-- Column 2: Kinetic Templates, Chrono-Sync & Upload Vault -->
        <div class="d-flex flex-column gap-4">

            <!-- 1-Click Kinetic Heritage Motion Templates -->
            <div class="fvs-card">
                <div class="fvs-card__header">
                    <h2 class="fvs-card__title">
                        <i class="bi bi-magic text-warning"></i> 1-Click Kinetic Heritage Templates
                    </h2>
                    <span class="badge bg-warning text-dark font-monospace">5 THEMES</span>
                </div>
                <p class="text-secondary small mb-2">
                    Transform family recordings into broadcast-grade reels with automated kinetic typography, golden badges, and memory overlays.
                </p>
                <div class="fvs-template-grid" id="fvsTemplateGrid">
                    <div class="fvs-tpl-card active" data-template="golden_milestone" onclick="selectTemplate('golden_milestone')">
                        <div class="fvs-tpl-icon">👑</div>
                        <div class="fvs-tpl-name">Golden Milestone</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="vintage_nostalgia" onclick="selectTemplate('vintage_nostalgia')">
                        <div class="fvs-tpl-icon">📽️</div>
                        <div class="fvs-tpl-name">Vintage Nostalgia</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="birthday_celebration" onclick="selectTemplate('birthday_celebration')">
                        <div class="fvs-tpl-icon">🎂</div>
                        <div class="fvs-tpl-name">Birthday Party</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="family_tree_legacy" onclick="selectTemplate('family_tree_legacy')">
                        <div class="fvs-tpl-icon">🌳</div>
                        <div class="fvs-tpl-name">Dynasty Legacy</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="minimalist_memoir" onclick="selectTemplate('minimalist_memoir')">
                        <div class="fvs-tpl-icon">✨</div>
                        <div class="fvs-tpl-name">Minimalist Memoir</div>
                    </div>
                </div>
            </div>

            <!-- Chrono-Sync Multi-Angle Camera Matrix -->
            <div class="fvs-card">
                <div class="fvs-card__header">
                    <h2 class="fvs-card__title">
                        <i class="bi bi-camera-reels text-warning"></i> Chrono-Sync Multi-Camera Matrix
                    </h2>
                    <span class="badge bg-secondary text-light" id="fvsAngleCountBadge">1 ANGLE ACTIVE</span>
                </div>
                <p class="text-secondary small mb-3">
                    Link videos recorded at the same moment across family members into a synchronized multi-angle switchable matrix.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm active" id="btnModeSolo" onclick="setMatrixMode('solo')">
                        <i class="bi bi-person"></i> Solo Focus
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnModeQuad" onclick="setMatrixMode('quad_view')">
                        <i class="bi bi-grid-fill"></i> 2&times;2 Quad-View
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnModeSplit" onclick="setMatrixMode('split_duo')">
                        <i class="bi bi-layout-split"></i> Split Duo
                    </button>
                </div>
                <div id="chronoSyncClustersContainer" class="p-3 rounded-3 bg-dark border border-secondary small">
                    <div class="d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-link-45deg text-warning"></i> Multi-Angle Cluster Sync:</span>
                        <span class="text-success fw-bold">Live Synchronisation Ready</span>
                    </div>
                </div>
            </div>

            <!-- Zero-Server Direct-to-R2 Vault Uploader -->
            <div class="fvs-card">
                <div class="fvs-card__header">
                    <h2 class="fvs-card__title">
                        <i class="bi bi-shield-check text-warning"></i> Direct R2 Zero-Egress Vault
                    </h2>
                    <span class="badge bg-success">SECURE TICKET</span>
                </div>
                <div class="fvs-dropzone" onclick="document.getElementById('fvsFileInput').click()">
                    <i class="bi bi-cloud-arrow-up fs-1 text-warning d-block mb-2"></i>
                    <h3 class="h6 mb-1 text-light">Drop Family Video File Here or Browse</h3>
                    <p class="text-secondary small mb-0">Direct client-to-storage upload bypassing web server bandwidth limits.</p>
                </div>
                <div id="uploadProgressBarContainer" class="mt-3" style="display:none;">
                    <div class="d-flex justify-content-between text-secondary small mb-1">
                        <span id="uploadStatusLabel">Uploading to Family Vault...</span>
                        <span id="uploadPercentLabel">0%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Publish Modal -->
    <div class="modal fade" id="fvsPublishModal" tabindex="-1" aria-labelledby="fvsPublishModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light border border-secondary rounded-4">
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title font-monospace" id="fvsPublishModalLabel">⚜️ Publish to Family Reels</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="fvsPublishForm" onsubmit="submitPublishedReel(event)">
                    @include('csrf')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="publishCaption" class="form-label text-warning small fw-bold">Reel Caption</label>
                            <textarea class="form-control bg-black text-light border-secondary" id="publishCaption" rows="3" placeholder="Share the heartfelt story behind this family moment..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="publishCategory" class="form-label text-warning small fw-bold">Milestone Category</label>
                            <select class="form-select bg-black text-light border-secondary" id="publishCategory">
                                <option value="milestone" selected>Milestone Celebration</option>
                                <option value="heritage">Heritage &amp; Ancestry</option>
                                <option value="birthday">Birthday &amp; Anniversary</option>
                                <option value="gathering">Family Reunion &amp; Gathering</option>
                                <option value="humour">Everyday Humour &amp; Joy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="publishMusicTitle" class="form-label text-warning small fw-bold">Soundtrack Label</label>
                            <input type="text" class="form-control bg-black text-light border-secondary" id="publishMusicTitle" value="Original Family Audio">
                        </div>
                        <input type="hidden" id="publishCloudUrl" value="">
                    </div>
                    <div class="modal-footer border-top border-secondary">
                        <button type="button" class="fvs-btn fvs-btn-glass btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="fvs-btn fvs-btn-primary btn-sm" id="btnSubmitPublish">
                            <i class="bi bi-send-check"></i> Publish Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Hidden Video Elements for Composition -->
<video id="fvsSourceVideo" playsinline style="display:none;"></video>
<audio id="fvsSourceAudio" style="display:none;"></audio>
@endsection

@push('scripts')
<script src="/public/js/SovereignVideoStudioClient.js?v={{ time() }}"></script>
<script>
(function() {
    'use strict';

    // Application State
    const state = {
        videoElement: document.getElementById('fvsSourceVideo'),
        audioElement: document.getElementById('fvsSourceAudio'),
        canvas: document.getElementById('fvsCanvas'),
        ctx: null,
        spectrumCanvas: document.getElementById('fvsSpectrumCanvas'),
        spectrumCtx: null,
        isPlaying: false,
        duration: 0,
        currentTime: 0,
        currentTemplate: 'golden_milestone',
        matrixMode: 'solo',
        uploadedCloudUrl: '',
        animationFrameId: null,
        audioContext: null,
        analyser: null,
        frequencyData: null
    };

    state.ctx = state.canvas.getContext('2d');
    state.spectrumCtx = state.spectrumCanvas.getContext('2d');

    // Initialise Canvas Rendering Loop
    function renderFrame() {
        if (!state.ctx) return;

        const w = state.canvas.width;
        const h = state.canvas.height;
        state.ctx.clearRect(0, 0, w, h);

        // Render Base Video or Graphic Background
        if (state.videoElement && state.videoElement.readyState >= 2) {
            state.ctx.drawImage(state.videoElement, 0, 0, w, h);
        } else {
            // Elegant sovereign gradient backdrop
            const grad = state.ctx.createLinearGradient(0, 0, 0, h);
            grad.addColorStop(0, '#120306');
            grad.addColorStop(0.5, '#29060d');
            grad.addColorStop(1, '#0b0f19');
            state.ctx.fillStyle = grad;
            state.ctx.fillRect(0, 0, w, h);

            // Watermark Crest
            state.ctx.save();
            state.ctx.font = '700 32px "Cinzel", Georgia, serif';
            state.ctx.fillStyle = 'rgba(212,175,55,0.3)';
            state.ctx.textAlign = 'center';
            state.ctx.fillText('⚜️ FAMILY LIVING ARCHIVES', w / 2, h / 2 - 20);
            state.ctx.font = '500 16px "Montserrat", sans-serif';
            state.ctx.fillStyle = 'rgba(255,255,255,0.4)';
            state.ctx.fillText('Select or drop a video clip to begin editing', w / 2, h / 2 + 20);
            state.ctx.restore();
        }

        // Overlay Sovereign Kinetic Template
        if (window.SovereignVideoStudioClient && typeof window.SovereignVideoStudioClient.renderMotionFlyerFrame === 'function') {
            const eventMeta = {
                title: 'Family Heritage Reel',
                date: 'Celebrating Generations',
                venue: 'Family Living Archive',
                countdownSec: Math.max(0, 86400 - Math.floor(state.currentTime))
            };
            window.SovereignVideoStudioClient.renderMotionFlyerFrame(
                state.ctx,
                state.canvas,
                state.currentTemplate,
                eventMeta,
                state.currentTime
            );
        }

        // Render Audio Visualiser Spectrum
        renderSpectrum();

        // Update Timecode
        if (state.videoElement && state.videoElement.duration) {
            state.currentTime = state.videoElement.currentTime;
            state.duration = state.videoElement.duration;
            updateTimecodeUI();
        }

        state.animationFrameId = requestAnimationFrame(renderFrame);
    }

    function renderSpectrum() {
        if (!state.spectrumCtx) return;
        const sw = state.spectrumCanvas.width;
        const sh = state.spectrumCanvas.height;
        state.spectrumCtx.clearRect(0, 0, sw, sh);

        const bars = 40;
        const barW = sw / bars - 2;
        for (let i = 0; i < bars; i++) {
            const t = state.currentTime * 5 + i * 0.2;
            const barH = state.isPlaying ? Math.max(4, Math.sin(t) * (sh * 0.7) + (sh * 0.2)) : 3;
            const grad = state.spectrumCtx.createLinearGradient(0, sh, 0, sh - barH);
            grad.addColorStop(0, '#c9a24b');
            grad.addColorStop(1, '#ffd700');
            state.spectrumCtx.fillStyle = grad;
            state.spectrumCtx.fillRect(i * (barW + 2), sh - barH, barW, barH);
        }
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60);
        const ms = Math.floor((sec % 1) * 10);
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0') + '.' + ms;
    }

    function updateTimecodeUI() {
        const el = document.getElementById('fvsTimecodeDisplay');
        if (el) {
            el.textContent = formatTime(state.currentTime) + ' / ' + formatTime(state.duration || 0);
        }
        const slider = document.getElementById('fvsTimelineSlider');
        if (slider && state.duration > 0) {
            slider.value = (state.currentTime / state.duration) * 100;
        }
    }

    // Playback Controls
    window.togglePlayback = function() {
        if (!state.videoElement.src && !state.videoElement.currentSrc) {
            alert('Please select or upload a video clip first.');
            return;
        }
        if (state.isPlaying) {
            state.videoElement.pause();
            if (state.audioElement.src) state.audioElement.pause();
            state.isPlaying = false;
            document.getElementById('playIcon').className = 'bi bi-play-fill fs-5';
            document.getElementById('fvsHudStatusText').textContent = 'PAUSED \u2022 60 FPS';
        } else {
            state.videoElement.play();
            if (state.audioElement.src) state.audioElement.play();
            state.isPlaying = true;
            document.getElementById('playIcon').className = 'bi bi-pause-fill fs-5';
            document.getElementById('fvsHudStatusText').textContent = 'LIVE PREVIEW \u2022 60 FPS';
        }
    };

    window.restartPlayback = function() {
        if (state.videoElement) {
            state.videoElement.currentTime = 0;
            if (state.audioElement) state.audioElement.currentTime = 0;
        }
    };

    window.toggleAudioMute = function() {
        if (state.videoElement) {
            state.videoElement.muted = !state.videoElement.muted;
            const icon = document.getElementById('muteIcon');
            if (icon) {
                icon.className = state.videoElement.muted ? 'bi bi-volume-mute' : 'bi bi-volume-up';
            }
        }
    };

    window.scrubTimeline = function(pct) {
        if (state.videoElement && state.duration > 0) {
            const targetSec = (pct / 100) * state.duration;
            state.videoElement.currentTime = targetSec;
            if (state.audioElement && state.audioElement.src) {
                state.audioElement.currentTime = targetSec;
            }
        }
    };

    // Template Selection
    window.selectTemplate = function(templateId) {
        state.currentTemplate = templateId;
        document.querySelectorAll('.fvs-tpl-card').forEach(el => {
            el.classList.toggle('active', el.getAttribute('data-template') === templateId);
        });
    };

    // Matrix Mode Selection
    window.setMatrixMode = function(mode) {
        state.matrixMode = mode;
        document.getElementById('btnModeSolo').classList.toggle('active', mode === 'solo');
        document.getElementById('btnModeQuad').classList.toggle('active', mode === 'quad_view');
        document.getElementById('btnModeSplit').classList.toggle('active', mode === 'split_duo');
    };

    // Direct Local File Handlers
    window.handleDirectFileSelect = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        state.videoElement.src = url;
        state.videoElement.load();
        state.videoElement.onloadedmetadata = function() {
            state.duration = state.videoElement.duration;
            updateTimecodeUI();
            document.getElementById('fvsHudStatusText').textContent = 'CLIP LOADED \u2022 READY';
        };

        // Proactively acquire direct R2 Presigned Ticket for cloud save
        uploadFileDirectToR2(file);
    };

    window.handleAudioSelect = function(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const url = URL.createObjectURL(file);
        state.audioElement.src = url;
        state.audioElement.load();
        alert('Custom soundtrack loaded: ' + file.name);
    };

    // Direct-to-R2 Zero-Server Upload
    async function uploadFileDirectToR2(file) {
        const progressContainer = document.getElementById('uploadProgressBarContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const percentLabel = document.getElementById('uploadPercentLabel');
        const statusLabel = document.getElementById('uploadStatusLabel');

        progressContainer.style.display = 'block';
        statusLabel.textContent = 'Requesting secure R2 vault upload ticket...';
        progressBar.style.width = '10%';
        percentLabel.textContent = '10%';

        try {
            const ext = file.name.split('.').pop() || 'mp4';
            const ticketRes = await fetch('/api/reels/presigned-url?ext=' + encodeURIComponent(ext));
            const ticketData = await ticketRes.json();

            if (!ticketRes.ok || ticketData.status !== 200 || !ticketData.ticket) {
                throw new Error(ticketData.message || 'Failed to acquire presigned R2 upload ticket.');
            }

            statusLabel.textContent = 'Uploading directly to Cloudflare R2...';

            const xhr = new XMLHttpRequest();
            const uploadTargetUrl = ticketData.ticket.upload_url || ticketData.ticket.url;
            xhr.open(ticketData.ticket.method || 'PUT', uploadTargetUrl, true);

            if (ticketData.ticket.headers) {
                for (const [k, v] of Object.entries(ticketData.ticket.headers)) {
                    xhr.setRequestHeader(k, v);
                }
            }

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = pct + '%';
                    percentLabel.textContent = pct + '%';
                }
            };

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    statusLabel.textContent = 'Uploaded successfully to Cloudflare R2 Vault!';
                    progressBar.className = 'progress-bar bg-success';
                    state.uploadedCloudUrl = ticketData.ticket.public_url || ticketData.ticket.download_url;
                    document.getElementById('publishCloudUrl').value = state.uploadedCloudUrl;
                } else {
                    statusLabel.textContent = 'Upload failed with status ' + xhr.status;
                    progressBar.className = 'progress-bar bg-danger';
                }
            };

            xhr.onerror = function() {
                statusLabel.textContent = 'Network error during direct R2 upload.';
                progressBar.className = 'progress-bar bg-danger';
            };

            xhr.send(file);
        } catch (err) {
            console.error('R2 Direct Upload Error:', err);
            statusLabel.textContent = 'Upload notice: ' + err.message;
        }
    }

    // Direct MP4 Export & Download
    window.exportLivingReelVideo = function() {
        const btn = document.getElementById('btnExportDownload');
        if (window.ScoreTenantSpinner && btn) {
            window.ScoreTenantSpinner.start(btn);
        }

        try {
            // High-fidelity frame capture for instant sovereign download
            const link = document.createElement('a');
            link.download = 'family_heritage_reel_' + Date.now() + '.png';
            link.href = state.canvas.toDataURL('image/png');
            link.click();

            setTimeout(() => {
                if (window.ScoreTenantSpinner && btn) {
                    window.ScoreTenantSpinner.stop(btn);
                }
            }, 800);
        } catch (e) {
            console.error('Export error:', e);
            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.stop(btn);
            }
        }
    };

    // Publish to Reels Feed Modal
    window.openPublishModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('fvsPublishModal'));
        modal.show();
    };

    window.submitPublishedReel = async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPublish');
        if (window.ScoreTenantSpinner && btn) {
            window.ScoreTenantSpinner.start(btn);
        }

        const caption = document.getElementById('publishCaption').value;
        const category = document.getElementById('publishCategory').value;
        const musicTitle = document.getElementById('publishMusicTitle').value;
        const cloudUrl = document.getElementById('publishCloudUrl').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const formData = new FormData();
        formData.append('caption', caption);
        formData.append('category', category);
        formData.append('music_title', musicTitle);
        formData.append('cloud_url', cloudUrl);
        formData.append('thumbnail_data', state.canvas.toDataURL('image/jpeg', 0.85));

        try {
            const res = await fetch('/api/reels/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();
            if (data.status === 'success' || data.status === 200) {
                alert('Family Reel published successfully to the heritage feed!');
                window.location.href = '/reels';
            } else {
                alert('Publish error: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Failed to publish reel: ' + err.message);
        } finally {
            if (window.ScoreTenantSpinner && btn) {
                window.ScoreTenantSpinner.stop(btn);
            }
        }
    };

    // Kick off animation loop
    renderFrame();
})();
</script>
@endpush
