@extends('layouts.profileBase')

@section('title', 'Family Living Archives Studio — 4K Reel Studio & Video Vault')
@section('meta_description', 'Browser-native 4K video editing, multi-camera Chrono-Sync matrix, kinetic heritage motion templates, and zero-server-overhead direct downloads.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="/public/css/family_studio.css?v={{ time() }}">
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
    aspect-ratio: 16/9;
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
    transition: aspect-ratio 0.2s ease;
}
.fvs-viewport-wrap[data-aspect="1:1"] {
    aspect-ratio: 1/1;
    max-width: 520px;
}
.fvs-viewport-wrap[data-aspect="9:16"] {
    aspect-ratio: 9/16;
    max-width: 340px;
}
.fvs-format-btn {
    min-height: 44px;
    min-width: 44px;
    white-space: nowrap;
    flex-shrink: 0;
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
    @include('member.creativeStudioTabs', ['creativeTab' => 'archives'])

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
            <button type="button" class="fvs-btn fvs-btn-gold" id="btnTriggerUpload">
                <i class="bi bi-cloud-arrow-up"></i> Upload Media (Direct to R2)
            </button>
            <button type="button" class="fvs-btn fvs-btn-primary" id="btnExportDownload">
                <i class="bi bi-download"></i> Download Sovereign MP4
            </button>
        </div>
    </header>

    <!-- Hidden Direct File Inputs -->
    <input type="file" id="fvsFileInput" accept="video/mp4,video/quicktime,video/webm" multiple style="display:none;">
    <input type="file" id="fvsAudioInput" accept="audio/mp3,audio/wav,audio/mpeg,audio/aac" style="display:none;">

    <!-- Main Studio Grid -->
    <div class="fvs-grid">

        <!-- Column 1: Studio Canvas & Interactive Playback -->
        <div class="fvs-card">
            <div class="fvs-card__header">
                <h2 class="fvs-card__title" id="fvsViewportTitle">
                    <i class="bi bi-film text-warning"></i> 16:9 Cinematic Canvas Viewport
                </h2>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group" role="group" aria-label="Canvas aspect ratio">
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold fvs-format-btn" data-fvs-aspect="16:9">16:9</button>
                        <button type="button" class="btn btn-sm btn-outline-light fvs-format-btn" data-fvs-aspect="1:1">1:1</button>
                        <button type="button" class="btn btn-sm btn-outline-light fvs-format-btn" data-fvs-aspect="9:16">9:16</button>
                    </div>
                    <span class="badge bg-dark border border-secondary text-light" id="fvsResolutionBadge">1920 &times; 1080 (16:9)</span>
                </div>
            </div>

            <!-- Viewport Screen -->
            <div class="fvs-viewport-wrap" id="fvsViewportWrap" data-aspect="16:9">
                <div class="fvs-hud-badge">
                    <div class="fvs-rec-pulse"></div>
                    <span id="fvsHudStatusText">STANDBY &bull; 60 FPS</span>
                </div>
                <canvas id="fvsCanvas" class="fvs-canvas" width="1280" height="720"></canvas>
            </div>

            <!-- Playback Timeline Scrub & Visualiser -->
            <div class="fvs-progress-bar-wrap">
                <input type="range" class="fvs-progress-slider" id="fvsTimelineSlider" min="0" max="100" value="0" step="0.1">
                <canvas id="fvsSpectrumCanvas" class="fvs-spectrum-canvas" width="600" height="44"></canvas>
            </div>

            <!-- Playback Control Bar -->
            <div class="fvs-controls">
                <div class="fvs-ctrl-group">
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnPlayPause" title="Play / Pause">
                        <i class="bi bi-play-fill fs-5" id="playIcon"></i>
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnRestart" title="Restart">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnMuteToggle" title="Mute Audio">
                        <i class="bi bi-volume-up" id="muteIcon"></i>
                    </button>
                    <div class="fvs-timecode" id="fvsTimecodeDisplay">00:00.0 / 00:00.0</div>
                </div>

                <div class="fvs-ctrl-group">
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnAudioTrigger" title="Add Audio Track">
                        <i class="bi bi-music-note-beamed"></i> Audio
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-gold btn-sm" id="btnPublishTrigger" title="Publish to Family Reels Feed">
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
                    <div class="fvs-tpl-card active" data-template="golden_milestone">
                        <div class="fvs-tpl-icon">👑</div>
                        <div class="fvs-tpl-name">Golden Milestone</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="vintage_nostalgia">
                        <div class="fvs-tpl-icon">📽️</div>
                        <div class="fvs-tpl-name">Vintage Nostalgia</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="birthday_celebration">
                        <div class="fvs-tpl-icon">🎂</div>
                        <div class="fvs-tpl-name">Birthday Party</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="family_tree_legacy">
                        <div class="fvs-tpl-icon">🌳</div>
                        <div class="fvs-tpl-name">Dynasty Legacy</div>
                    </div>
                    <div class="fvs-tpl-card" data-template="minimalist_memoir">
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
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm active" id="btnModeSolo">
                        <i class="bi bi-person"></i> Solo Focus
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnModeQuad">
                        <i class="bi bi-grid-fill"></i> 2&times;2 Quad-View
                    </button>
                    <button type="button" class="fvs-btn fvs-btn-glass btn-sm" id="btnModeSplit">
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
                <div class="fvs-dropzone" id="fvsDropzone">
                    <i class="bi bi-cloud-arrow-up fs-1 text-warning d-block mb-2"></i>
                    <h3 class="h6 mb-1 text-light">Drop Up to 4 Family Video Clips or Browse</h3>
                    <p class="text-secondary small mb-0">Select 1 clip for Solo Focus, 2 for Split Duo, or 4 for 2&times;2 Quad-View. Direct upload bypasses server limits.</p>
                </div>
                <div id="uploadProgressBarContainer" class="mt-3 p-3 rounded-3 bg-dark border border-secondary" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center small mb-2">
                        <span id="uploadStatusLabel" class="text-light fw-semibold">Uploading to Family Vault...</span>
                        <span id="uploadPercentLabel" class="text-warning fw-bold font-monospace">0%</span>
                    </div>
                    <div class="progress" style="height: 24px; border-radius: 999px; background: rgba(255,255,255,0.08);">
                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
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
                <form id="fvsPublishForm">
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

    <!-- 4-Quadrant Non-Linear Video Editor (NLE) Workspace -->
    <div id="sovereignNleRoot"></div>

    <!-- Sovereign Kinship Video Studio: 12-Module Procedural & WASM Suite -->
    <section class="sks-suite-panel" aria-label="Sovereign Kinship 12-Module Video Studio">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h2 class="fvs-card__title mb-1"><i class="bi bi-cpu-fill text-warning"></i> Sovereign Kinship 12-Module Studio Suite</h2>
                <p class="text-muted small mb-0">100% client-side WASM/WebGL/Web Audio procedural processing with deterministic PHP/FFmpeg fallbacks.</p>
            </div>
            <span class="sks-status-pill"><i class="bi bi-shield-check"></i> Zero-SaaS &amp; GDPR Art. 9 Protected</span>
        </div>

        <div class="sks-module-grid">
            <!-- 1. Text-to-Video Generation -->
            <div class="sks-module-card">
                <h3>1. Text-to-Video Generation</h3>
                <p>Procedural prompt-to-timeline storyboard assembler with kinetic typography and canvas B-roll synthesis.</p>
                <input type="text" id="sksPromptInput" class="form-control form-control-sm bg-dark text-light border-secondary" value="Grandad Arthur arrived in London in 1962. Sixty years of family heritage." aria-label="Story prompt">
                <button type="button" class="fvs-btn fvs-btn-gold btn-sm" onclick="window.sksStudio && window.sksStudio.runTextToVideo(document.getElementById('sksPromptInput').value)">
                    <i class="bi bi-magic"></i> Assemble Storyboard
                </button>
                <span class="small" id="sksStatus_text_to_video">Ready</span>
            </div>

            <!-- 2. Image-to-Video Animation -->
            <div class="sks-module-card">
                <h3>2. Image-to-Video Animation</h3>
                <p>Living Archives 2.5D depth-map parallax shaders and smooth Ken Burns zoom-bursts on family photos.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runImageToVideoParallax(0.04)">
                    <i class="bi bi-layers-half"></i> Apply 2.5D Parallax
                </button>
                <span class="small" id="sksStatus_image_to_video">Ready</span>
            </div>

            <!-- 3. AI Avatars -->
            <div class="sks-module-card">
                <h3>3. AI Narrator Avatars</h3>
                <p>Procedural 2D/2.5D lip-synced family narrator driven by real-time RMS visemes, blink cycles, and head nods.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.toggleProceduralAvatar(0.45)">
                    <i class="bi bi-person-bounding-box"></i> Toggle Narrator Avatar
                </button>
                <span class="small" id="sksStatus_ai_avatars">Ready</span>
            </div>

            <!-- 4. Auto Captions -->
            <div class="sks-module-card">
                <h3>4. Auto Captions (Karaoke)</h3>
                <p>Word-level timestamped speech-to-text captions with kinetic karaoke highlighting.</p>
                <input type="text" id="sksCaptionInput" class="form-control form-control-sm bg-dark text-light border-secondary" value="Celebrating our cherished family memories together." aria-label="Caption script">
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runAutoCaptions(document.getElementById('sksCaptionInput').value)">
                    <i class="bi bi-badge-cc-fill"></i> Generate Karaoke Captions
                </button>
                <span class="small" id="sksStatus_auto_captions">Ready</span>
            </div>

            <!-- 5. Text-to-Speech Voiceovers -->
            <div class="sks-module-card">
                <h3>5. British English Voiceovers</h3>
                <p>Oliver, Serena, and Elders en-GB synthesis with Web Audio DSP formant and prosody control.</p>
                <select id="sksVoiceProfile" class="form-select form-select-sm bg-dark text-light border-secondary" aria-label="British voice profile">
                    <option value="oliver">Oliver (British RP)</option>
                    <option value="serena">Serena (Home Counties)</option>
                    <option value="elders">Elders (Warm Heritage)</option>
                </select>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runBritishVoiceover(document.getElementById('sksCaptionInput').value, document.getElementById('sksVoiceProfile').value)">
                    <i class="bi bi-volume-up-fill"></i> Synthesise Voiceover
                </button>
                <span class="small" id="sksStatus_tts_voiceovers">Ready</span>
            </div>

            <!-- 6. Voice Cloning & Dubbing (GDPR Art. 9 Gate) -->
            <div class="sks-module-card">
                <h3>6. Voice Cloning &amp; Dubbing</h3>
                <p>Real-time DSP vocal timbre/formant profiler for lineage narration.</p>
                <label class="sks-consent-box">
                    <input type="checkbox" id="sksGdprArt9Consent">
                    <span><strong>GDPR Article 9 Consent:</strong> I confirm explicit biometric voice consent has been granted by the speaker.</span>
                </label>
                <button type="button" class="fvs-btn fvs-btn-primary btn-sm" onclick="window.sksStudio && window.sksStudio.runVoiceCloning(document.getElementById('sksGdprArt9Consent').checked)">
                    <i class="bi bi-mic-fill"></i> Profile &amp; Dub Voice
                </button>
                <span class="small" id="sksStatus_voice_cloning">Consent required</span>
            </div>

            <!-- 7. Auto-Cut & Silence Removal -->
            <div class="sks-module-card">
                <h3>7. Auto-Cut &amp; Silence Removal</h3>
                <p>RMS waveform energy-threshold analyser for sub-second jump-cutting and dead-air trimming.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runSilenceRemoval(0.02)">
                    <i class="bi bi-scissors"></i> Trim Dead Air
                </button>
                <span class="small" id="sksStatus_auto_cut_silence">Ready</span>
            </div>

            <!-- 8. Background Removal -->
            <div class="sks-module-card">
                <h3>8. Background Removal</h3>
                <p>Real-time YCbCr chroma-keying, portrait edge segmentation, and virtual studio backdrops.</p>
                <select id="sksBackdropSelect" class="form-select form-select-sm bg-dark text-light border-secondary" aria-label="Virtual studio backdrop">
                    <option value="heritage_manor">Heritage Manor</option>
                    <option value="warm_study">Warm Study</option>
                    <option value="neon_lounge">Neon Lounge</option>
                </select>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runBackgroundRemoval(document.getElementById('sksBackdropSelect').value)">
                    <i class="bi bi-person-square"></i> Apply Studio Backdrop
                </button>
                <span class="small" id="sksStatus_background_removal">Ready</span>
            </div>

            <!-- 9. Subject Tracking -->
            <div class="sks-module-card">
                <h3>9. Subject Tracking</h3>
                <p>Lucas-Kanade optical flow feature tracking for smart pan-and-scan, callouts, and blur masks.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runSubjectTracking('callout')">
                    <i class="bi bi-crosshair"></i> Lock Subject Tracker
                </button>
                <span class="small" id="sksStatus_subject_tracking">Ready</span>
            </div>

            <!-- 10. Video Upscaling -->
            <div class="sks-module-card">
                <h3>10. Lanczos-3 4K Upscaling</h3>
                <p>Separable Lanczos-3 resampling, unsharp-mask edge sharpening, and temporal de-noising.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runLanczosUpscale()">
                    <i class="bi bi-badge-4k-fill"></i> Restore &amp; Upscale
                </button>
                <span class="small" id="sksStatus_video_upscaling">Ready</span>
            </div>

            <!-- 11. AI Colour Matching & Enhancement -->
            <div class="sks-module-card">
                <h3>11. Colour Matching &amp; LUTs</h3>
                <p>Shot-to-shot RGB/LAB histogram matching with 3D LUTs and skin-tone vector protection.</p>
                <select id="sksColourPreset" class="form-select form-select-sm bg-dark text-light border-secondary" aria-label="Colour LUT preset">
                    <option value="heritage_gold">Heritage Gold</option>
                    <option value="cinema_teal_orange">Cinema Teal &amp; Orange</option>
                    <option value="vintage_kodak">Vintage Kodak</option>
                    <option value="crisp_natural">Crisp Natural</option>
                </select>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runColourMatching(document.getElementById('sksColourPreset').value)">
                    <i class="bi bi-palette-fill"></i> Optimise Colour Grade
                </button>
                <span class="small" id="sksStatus_colour_matching">Ready</span>
            </div>

            <!-- 12. Audio Noise Reduction -->
            <div class="sks-module-card">
                <h3>12. Audio Noise Reduction</h3>
                <p>Dual-pass spectral subtraction, 50/60Hz hum notch filtering, and vocal isolation.</p>
                <button type="button" class="fvs-btn fvs-btn-glass btn-sm" onclick="window.sksStudio && window.sksStudio.runAudioNoiseReduction(0.015)">
                    <i class="bi bi-soundwave"></i> Clean Audio Track
                </button>
                <span class="small" id="sksStatus_audio_noise_reduction">Ready</span>
            </div>
        </div>
    </section>

</div>

<!-- Hidden Video Elements for Composition (offscreen, not display:none, so browsers decode video frames) -->
<video id="fvsSourceVideo" playsinline preload="auto" style="position:fixed;left:-9999px;top:-9999px;width:1px;height:1px;opacity:0.001;pointer-events:none;"></video>
<audio id="fvsSourceAudio" preload="auto" style="display:none;"></audio>
@endsection

@push('scripts')
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignVideoStudioClient.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignNleEngine.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignSpotlightTour.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignHelpSearchEngine.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/SovereignNleWorkspace.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/family_studio.js?v={{ time() }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    'use strict';
    window.sksStudio = new window.SovereignKinshipStudioController({
        canvas: document.getElementById('fvsCanvas'),
        csrfToken: '{{ $_SESSION['token'] ?? '' }}'
    });
    if (window.SovereignNleWorkspace && document.getElementById('sovereignNleRoot')) {
        window.sovereignNleWorkspace = new window.SovereignNleWorkspace({
            root: '#sovereignNleRoot',
            appName: 'FamilyPlatform Living Archives',
            aspectRatio: '16:9',
            howToUseUrl: '/how-to-use',
            csrfToken: '{{ $_SESSION['token'] ?? '' }}'
        });
    }

    const aspectDimensions = {
        '16:9': { w: 1280, h: 720, label: '1920 × 1080 (16:9)' },
        '1:1':  { w: 720,  h: 720, label: '1080 × 1080 (1:1)' },
        '9:16': { w: 720,  h: 1280, label: '1080 × 1920 (9:16)' }
    };
    document.querySelectorAll('[data-fvs-aspect]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const ratio = btn.getAttribute('data-fvs-aspect') || '16:9';
            const spec = aspectDimensions[ratio] || aspectDimensions['16:9'];
            const wrap = document.getElementById('fvsViewportWrap');
            const canvasEl = document.getElementById('fvsCanvas');
            const badgeEl = document.getElementById('fvsResolutionBadge');
            const titleEl = document.getElementById('fvsViewportTitle');
            if (wrap) wrap.setAttribute('data-aspect', ratio);
            if (canvasEl) { canvasEl.width = spec.w; canvasEl.height = spec.h; }
            if (badgeEl) badgeEl.textContent = spec.label;
            if (titleEl) titleEl.innerHTML = '<i class="bi bi-film text-warning"></i> ' + ratio + ' Cinematic Canvas Viewport';
            document.querySelectorAll('[data-fvs-aspect]').forEach(function(b) {
                const isCurrent = b.getAttribute('data-fvs-aspect') === ratio;
                b.className = 'btn btn-sm fvs-format-btn ' + (isCurrent ? 'btn-warning text-dark fw-semibold' : 'btn-outline-light');
            });
            if (window.sovereignNleWorkspace && window.sovereignNleWorkspace.engine) {
                window.sovereignNleWorkspace.engine.setAspectRatio(ratio);
                window.sovereignNleWorkspace.render();
            }
        });
    });
})();
</script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
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
        angles: [],
        activeAngleIndex: 0,
        isMuted: false,
        uploadedCloudUrl: '',
        animationFrameId: null,
        audioContext: null,
        analyser: null,
        frequencyData: null
    };

    state.ctx = state.canvas.getContext('2d');
    state.spectrumCtx = state.spectrumCanvas.getContext('2d');
    const studioClient = window.SovereignVideoStudioClient ? new window.SovereignVideoStudioClient() : null;
    const bootTimeMs = performance.now();

    const TEMPLATE_THEMES = {
        golden_milestone: {
            border: '#d4af37',
            accent: '#ffd700',
            badge: '👑 GOLDEN MILESTONE',
            tint: 'rgba(212, 175, 55, 0.06)'
        },
        vintage_nostalgia: {
            border: '#d97706',
            accent: '#fde68a',
            badge: '📽️ VINTAGE NOSTALGIA',
            tint: 'rgba(120, 53, 15, 0.16)'
        },
        birthday_celebration: {
            border: '#ec4899',
            accent: '#f9a8d4',
            badge: '🎂 BIRTHDAY PARTY',
            tint: 'rgba(236, 72, 153, 0.08)'
        },
        family_tree_legacy: {
            border: '#10b981',
            accent: '#6ee7b7',
            badge: '🌳 DYNASTY LEGACY',
            tint: 'rgba(16, 185, 129, 0.08)'
        },
        minimalist_memoir: {
            border: '#e2e8f0',
            accent: '#ffffff',
            badge: '✨ MINIMALIST MEMOIR',
            tint: 'rgba(15, 23, 42, 0.05)'
        }
    };

    function drawVideoCover(ctx, video, dx, dy, dw, dh) {
        const vw = video.videoWidth || dw;
        const vh = video.videoHeight || dh;
        const scale = Math.max(dw / vw, dh / vh);
        const sw = dw / scale;
        const sh = dh / scale;
        const sx = (vw - sw) / 2;
        const sy = (vh - sh) / 2;
        ctx.drawImage(video, sx, sy, sw, sh, dx, dy, dw, dh);
    }

    function renderHeritageOverlay(ctx, w, h, templateId, timeSec, hasVideo) {
        const theme = TEMPLATE_THEMES[templateId] || TEMPLATE_THEMES.golden_milestone;
        ctx.save();

        if (hasVideo) {
            // Subtle atmospheric tint only — keep the video crystal clear
            ctx.fillStyle = theme.tint;
            ctx.fillRect(0, 0, w, h);

            // Top & bottom subtle gradient scrims for legible typography
            const topScrim = ctx.createLinearGradient(0, 0, 0, 180);
            topScrim.addColorStop(0, 'rgba(0, 0, 0, 0.65)');
            topScrim.addColorStop(1, 'rgba(0, 0, 0, 0)');
            ctx.fillStyle = topScrim;
            ctx.fillRect(0, 0, w, 180);

            const botScrim = ctx.createLinearGradient(0, h - 220, 0, h);
            botScrim.addColorStop(0, 'rgba(0, 0, 0, 0)');
            botScrim.addColorStop(1, 'rgba(0, 0, 0, 0.72)');
            ctx.fillStyle = botScrim;
            ctx.fillRect(0, h - 220, w, 220);
        } else {
            // Idle sovereign gradient backdrop when no clip is loaded
            const grad = ctx.createLinearGradient(0, 0, 0, h);
            grad.addColorStop(0, '#120306');
            grad.addColorStop(0.5, '#29060d');
            grad.addColorStop(1, '#0b0f19');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);

            ctx.font = '700 30px "Cinzel", Georgia, serif';
            ctx.fillStyle = theme.accent;
            ctx.textAlign = 'center';
            ctx.fillText('⚜️ FAMILY LIVING ARCHIVES', w / 2, h / 2 - 20);
            ctx.font = '500 16px "Montserrat", sans-serif';
            ctx.fillStyle = 'rgba(255,255,255,0.65)';
            ctx.fillText('Select or drop a video clip to begin editing', w / 2, h / 2 + 20);
        }

        // Floating ambient particles
        for (let i = 0; i < 18; i++) {
            const seed = (i * 9301 + 49297) % 233280;
            const px = (seed / 233280) * w;
            const py = ((seed * 2 + timeSec * 45) % h);
            const size = 1.5 + (i % 4);
            ctx.fillStyle = theme.accent;
            ctx.globalAlpha = hasVideo ? 0.35 : 0.45;
            ctx.beginPath();
            ctx.arc(px, py, size, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        // Kinetic template frame border
        ctx.strokeStyle = theme.border;
        ctx.lineWidth = templateId === 'minimalist_memoir' ? 2 : 5;
        ctx.strokeRect(24, 24, w - 48, h - 48);
        if (templateId !== 'minimalist_memoir') {
            ctx.lineWidth = 1.25;
            ctx.strokeRect(34, 34, w - 68, h - 68);
        }

        // Top template pill badge
        ctx.textAlign = 'center';
        ctx.fillStyle = theme.accent;
        ctx.font = '700 18px "Montserrat", sans-serif';
        ctx.fillText(theme.badge, w / 2, 74);

        // Bottom watermark & subtle pulse bar
        const bars = 20;
        const barW = (w - 160) / bars;
        for (let b = 0; b < bars; b++) {
            const barH = 8 + Math.abs(Math.sin(timeSec * 5 + b * 0.6) * 28);
            ctx.fillStyle = theme.accent;
            ctx.globalAlpha = 0.75;
            ctx.fillRect(80 + b * barW, h - 78 - barH, barW - 4, barH);
        }
        ctx.globalAlpha = 0.85;
        ctx.fillStyle = '#e2e8f0';
        ctx.font = '600 13px "Montserrat", sans-serif';
        ctx.fillText('FAMILYPLATFORM LIVING ARCHIVES STUDIO', w / 2, h - 44);

        ctx.restore();
    }

    // Initialise Canvas Rendering Loop
    function renderFrame() {
        if (!state.ctx) return;

        const w = state.canvas.width;
        const h = state.canvas.height;
        state.ctx.clearRect(0, 0, w, h);

        // Render Base Video(s) or Graphic Background
        const readyAngles = state.angles.filter(a => a.videoEl && a.videoEl.readyState >= 2);
        const primaryEl = (state.angles[state.activeAngleIndex] && state.angles[state.activeAngleIndex].videoEl && state.angles[state.activeAngleIndex].videoEl.readyState >= 2)
            ? state.angles[state.activeAngleIndex].videoEl
            : (state.videoElement && state.videoElement.readyState >= 2 ? state.videoElement : null);

        const hasVideo = readyAngles.length > 0 || Boolean(primaryEl);

        if (readyAngles.length > 0) {
            if (state.matrixMode === 'solo' || readyAngles.length === 1 || !studioClient || typeof studioClient.renderMultiAngleMatrix !== 'function') {
                if (primaryEl) {
                    drawVideoCover(state.ctx, primaryEl, 0, 0, w, h);
                }
            } else {
                const ordered = readyAngles.map(a => a.videoEl);
                studioClient.renderMultiAngleMatrix(state.ctx, state.canvas, ordered, state.matrixMode);
            }
        } else if (primaryEl) {
            drawVideoCover(state.ctx, primaryEl, 0, 0, w, h);
        }

        const animTimeSec = (state.videoElement && state.videoElement.duration && state.isPlaying)
            ? state.videoElement.currentTime
            : (performance.now() - bootTimeMs) / 1000;

        // Overlay Transparent Kinetic Heritage Template (never covers video!)
        renderHeritageOverlay(state.ctx, w, h, state.currentTemplate, animTimeSec, hasVideo);

        // Render Active Sovereign 12-Module Effects (persists at 60fps)
        if (window.sksStudio && typeof window.sksStudio.renderActiveEffects === 'function') {
            window.sksStudio.renderActiveEffects(state.ctx, state.canvas, animTimeSec);
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
        const activeVideos = state.angles.map(a => a.videoEl).filter(Boolean);
        if (activeVideos.length === 0 && !state.videoElement.src && !state.videoElement.currentSrc) {
            alert('Please select or upload at least one video clip first.');
            return;
        }
        if (state.isPlaying) {
            activeVideos.forEach(v => v.pause());
            if (state.videoElement.src) state.videoElement.pause();
            if (state.audioElement.src) state.audioElement.pause();
            state.isPlaying = false;
            document.getElementById('playIcon').className = 'bi bi-play-fill fs-5';
            document.getElementById('fvsHudStatusText').textContent = 'PAUSED \u2022 60 FPS';
        } else {
            activeVideos.forEach(v => v.play().catch(() => {}));
            if (state.videoElement.src && !activeVideos.includes(state.videoElement)) {
                state.videoElement.play().catch(() => {});
            }
            if (state.audioElement.src) state.audioElement.play().catch(() => {});
            state.isPlaying = true;
            document.getElementById('playIcon').className = 'bi bi-pause-fill fs-5';
            document.getElementById('fvsHudStatusText').textContent = 'LIVE PREVIEW \u2022 60 FPS';
        }
    };

    window.restartPlayback = function() {
        state.angles.forEach(a => {
            if (a.videoEl) a.videoEl.currentTime = 0;
        });
        if (state.videoElement) state.videoElement.currentTime = 0;
        if (state.audioElement) state.audioElement.currentTime = 0;
    };

    window.toggleAudioMute = function() {
        state.isMuted = !state.isMuted;
        state.angles.forEach((a, idx) => {
            if (a.videoEl) a.videoEl.muted = state.isMuted || idx !== state.activeAngleIndex;
        });
        if (state.videoElement) {
            state.videoElement.muted = state.isMuted;
        }
        const icon = document.getElementById('muteIcon');
        if (icon) {
            icon.className = state.isMuted ? 'bi bi-volume-mute' : 'bi bi-volume-up';
        }
    };

    window.scrubTimeline = function(pct) {
        if (state.duration > 0) {
            const targetSec = (pct / 100) * state.duration;
            state.angles.forEach(a => {
                if (a.videoEl && a.videoEl.duration) {
                    a.videoEl.currentTime = Math.min(targetSec, a.videoEl.duration);
                }
            });
            if (state.videoElement && state.videoElement.src) {
                state.videoElement.currentTime = targetSec;
            }
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

    function renderAngleList() {
        const container = document.getElementById('chronoSyncClustersContainer');
        const badge = document.getElementById('fvsAngleCountBadge');
        const count = state.angles.length;
        if (badge) {
            badge.textContent = count === 0
                ? '0 ANGLES ACTIVE'
                : (count === 1 ? '1 ANGLE ACTIVE' : count + ' ANGLES ACTIVE');
        }
        if (!container) return;

        if (count === 0) {
            container.innerHTML = '<div class="d-flex align-items-center justify-content-between">'
                + '<span><i class="bi bi-link-45deg text-warning"></i> Multi-Angle Cluster Sync:</span>'
                + '<span class="text-success fw-bold">Live Synchronisation Ready</span>'
                + '</div>';
            return;
        }

        const listHtml = state.angles.map((a, idx) => {
            const isPrimary = idx === state.activeAngleIndex;
            const durText = a.duration ? formatTime(a.duration) : 'Loading...';
            const safeName = String(a.name || 'Clip ' + (idx + 1))
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return '<div class="d-flex align-items-center justify-content-between gap-2 py-1 ' + (idx > 0 ? 'border-top border-secondary mt-1 pt-2' : '') + '">'
                + '<button type="button" class="btn btn-sm ' + (isPrimary ? 'btn-warning text-dark' : 'btn-outline-light') + ' fw-bold text-truncate" style="min-height:44px;max-width:75%;text-align:left;" onclick="focusAngle(' + idx + ')" title="Focus CAM ' + (idx + 1) + '">'
                + 'CAM ' + (idx + 1) + ' &bull; ' + safeName + ' (' + durText + ')'
                + '</button>'
                + '<button type="button" class="btn btn-sm btn-outline-danger" style="min-height:44px;min-width:44px;" onclick="removeAngle(' + idx + ')" title="Remove CAM ' + (idx + 1) + '" aria-label="Remove CAM ' + (idx + 1) + '">&times;</button>'
                + '</div>';
        }).join('');
        container.innerHTML = listHtml;
    }

    window.focusAngle = function(idx) {
        if (idx < 0 || idx >= state.angles.length) return;
        state.activeAngleIndex = idx;
        state.videoElement = state.angles[idx].videoEl;
        state.duration = state.angles[idx].duration || 0;
        state.angles.forEach((a, i) => {
            if (a.videoEl) a.videoEl.muted = state.isMuted || i !== idx;
        });
        updateTimecodeUI();
        renderAngleList();
    };

    window.removeAngle = function(idx) {
        if (idx < 0 || idx >= state.angles.length) return;
        const removed = state.angles.splice(idx, 1)[0];
        if (removed && removed.videoEl) {
            removed.videoEl.pause();
            if (removed.videoEl.id === 'fvsSourceVideo') {
                removed.videoEl.removeAttribute('src');
                removed.videoEl.load();
            } else {
                removed.videoEl.remove();
            }
        }
        if (removed && removed.url) {
            URL.revokeObjectURL(removed.url);
        }
        if (state.angles.length === 0) {
            state.videoElement = document.getElementById('fvsSourceVideo');
            state.activeAngleIndex = 0;
            state.duration = 0;
            state.currentTime = 0;
            state.isPlaying = false;
            document.getElementById('playIcon').className = 'bi bi-play-fill fs-5';
            document.getElementById('fvsHudStatusText').textContent = 'STANDBY \u2022 60 FPS';
        } else {
            state.activeAngleIndex = Math.min(state.activeAngleIndex, state.angles.length - 1);
            state.videoElement = state.angles[state.activeAngleIndex].videoEl;
            state.duration = state.angles[state.activeAngleIndex].duration || 0;
        }
        updateTimecodeUI();
        renderAngleList();
    };

    // Direct Local File Handlers (supports up to 4 multi-angle clips)
    window.handleDirectFileSelect = function(input) {
        if (!input.files || input.files.length === 0) return;

        const remainingSlots = Math.max(0, 4 - state.angles.length);
        const filesToAdd = Array.from(input.files).slice(0, remainingSlots || 4);
        if (remainingSlots === 0) {
            // Replace oldest batch if already at 4
            while (state.angles.length > 0) {
                removeAngle(0);
            }
        }

        filesToAdd.forEach((file, offset) => {
            const angleIdx = state.angles.length;
            const url = URL.createObjectURL(file);
            const videoEl = angleIdx === 0
                ? document.getElementById('fvsSourceVideo')
                : document.createElement('video');

            if (angleIdx > 0) {
                videoEl.playsInline = true;
                videoEl.preload = 'auto';
                videoEl.style.cssText = 'position:fixed;left:-9999px;top:-9999px;width:1px;height:1px;opacity:0.001;pointer-events:none;';
                document.body.appendChild(videoEl);
            }

            videoEl.muted = state.isMuted || angleIdx !== state.activeAngleIndex;
            videoEl.src = url;
            videoEl.load();

            const angleObj = {
                file,
                url,
                videoEl,
                name: file.name,
                duration: 0
            };
            state.angles.push(angleObj);

            videoEl.onloadeddata = function() {
                if (videoEl.currentTime === 0 && videoEl.duration > 0.1) {
                    try { videoEl.currentTime = 0.05; } catch (_) {}
                }
            };

            videoEl.onloadedmetadata = function() {
                angleObj.duration = videoEl.duration || 0;
                if (angleIdx === state.activeAngleIndex || state.duration === 0) {
                    state.videoElement = videoEl;
                    state.duration = angleObj.duration;
                    updateTimecodeUI();
                }
                document.getElementById('fvsHudStatusText').textContent =
                    state.angles.length > 1
                        ? state.angles.length + ' ANGLES LOADED \u2022 READY'
                        : 'CLIP LOADED \u2022 READY';
                renderAngleList();
            };

            // Upload primary clip to R2 for Publish Reel
            if (angleIdx === 0 && offset === 0) {
                uploadFileDirectToR2(file);
            }
        });

        // Auto-select matrix layout when multiple clips are loaded
        if (state.angles.length >= 4) {
            setMatrixMode('quad_view');
        } else if (state.angles.length >= 2 && state.matrixMode === 'solo') {
            setMatrixMode('split_duo');
        }

        renderAngleList();
        input.value = '';
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
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark fw-bold';
        progressBar.style.width = '5%';
        progressBar.setAttribute('aria-valuenow', '5');
        progressBar.textContent = '5%';
        percentLabel.textContent = '5%';

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
                if (e.lengthComputable && e.total > 0) {
                    const pct = Math.min(100, Math.round((e.loaded / e.total) * 100));
                    const loadedMb = (e.loaded / (1024 * 1024)).toFixed(1);
                    const totalMb = (e.total / (1024 * 1024)).toFixed(1);
                    progressBar.style.width = pct + '%';
                    progressBar.setAttribute('aria-valuenow', String(pct));
                    progressBar.textContent = pct + '%';
                    percentLabel.textContent = pct + '%';
                    statusLabel.textContent = 'Uploading to Cloudflare R2 (' + loadedMb + ' MB / ' + totalMb + ' MB)...';
                }
            };

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    statusLabel.textContent = 'Uploaded successfully to Cloudflare R2 Vault!';
                    progressBar.className = 'progress-bar bg-success text-white fw-bold';
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', '100');
                    progressBar.textContent = '100%';
                    percentLabel.textContent = '100%';
                    state.uploadedCloudUrl = ticketData.ticket.public_url || ticketData.ticket.download_url;
                    document.getElementById('publishCloudUrl').value = state.uploadedCloudUrl;
                } else {
                    uploadViaSameOriginRelay(file, progressBar, percentLabel, statusLabel);
                }
            };

            xhr.onerror = function() {
                // Automatic same-origin relay fallback (e.g., when R2 bucket CORS is not yet enabled)
                uploadViaSameOriginRelay(file, progressBar, percentLabel, statusLabel);
            };

            xhr.send(file);
        } catch (err) {
            console.error('R2 Direct Upload Error:', err);
            uploadViaSameOriginRelay(file, progressBar, percentLabel, statusLabel);
        }
    }

    function uploadViaSameOriginRelay(file, progressBar, percentLabel, statusLabel) {
        statusLabel.textContent = 'Uploading via Sovereign Vault Relay...';
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark fw-bold';
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressBar.textContent = '0%';
        percentLabel.textContent = '0%';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('#fvsPublishForm input[name="_token"]')?.value
            || '';
        const formData = new FormData();
        formData.append('video', file);
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        const relayXhr = new XMLHttpRequest();
        relayXhr.open('POST', '/api/reels/r2-proxy-upload', true);
        if (csrfToken) {
            relayXhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }

        relayXhr.upload.onprogress = function(e) {
            if (e.lengthComputable && e.total > 0) {
                const pct = Math.min(99, Math.round((e.loaded / e.total) * 100));
                const loadedMb = (e.loaded / (1024 * 1024)).toFixed(1);
                const totalMb = (e.total / (1024 * 1024)).toFixed(1);
                progressBar.style.width = pct + '%';
                progressBar.setAttribute('aria-valuenow', String(pct));
                progressBar.textContent = pct + '%';
                percentLabel.textContent = pct + '%';
                statusLabel.textContent = 'Uploading to Family Vault (' + loadedMb + ' MB / ' + totalMb + ' MB)...';
            }
        };

        relayXhr.onload = function() {
            try {
                const res = JSON.parse(relayXhr.responseText || '{}');
                if (relayXhr.status >= 200 && relayXhr.status < 300 && res.status === 200 && res.public_url) {
                    statusLabel.textContent = res.storage === 'r2'
                        ? 'Uploaded successfully to Cloudflare R2 Vault!'
                        : 'Uploaded successfully to Family Vault!';
                    progressBar.className = 'progress-bar bg-success text-white fw-bold';
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', '100');
                    progressBar.textContent = '100%';
                    percentLabel.textContent = '100%';
                    state.uploadedCloudUrl = res.public_url;
                    document.getElementById('publishCloudUrl').value = state.uploadedCloudUrl;
                    return;
                }
                throw new Error(res.message || ('Upload failed with status ' + relayXhr.status));
            } catch (e) {
                statusLabel.textContent = 'Upload notice: ' + e.message;
                progressBar.className = 'progress-bar bg-danger text-white fw-bold';
                progressBar.style.width = '100%';
                progressBar.textContent = 'Error';
                percentLabel.textContent = '0%';
            }
        };

        relayXhr.onerror = function() {
            statusLabel.textContent = 'Upload notice: Network error during upload.';
            progressBar.className = 'progress-bar bg-danger text-white fw-bold';
            progressBar.style.width = '100%';
            progressBar.textContent = 'Error';
            percentLabel.textContent = '0%';
        };

        relayXhr.send(formData);
    }

    // Direct Video Export & Download (WebCodecs / MediaRecorder)
    window.exportLivingReelVideo = async function() {
        const btn = document.getElementById('btnExportDownload');
        const hudStatus = document.getElementById('fvsHudStatusText');
        if (window.ScoreTenantSpinner && btn) {
            window.ScoreTenantSpinner.start(btn);
        }

        try {
            if (studioClient && typeof studioClient.exportCanvasVideo === 'function') {
                const exportDuration = (state.duration && state.duration > 0)
                    ? Math.min(state.duration, 15)
                    : 3.5;
                if (hudStatus) {
                    hudStatus.textContent = 'COMPILING VIDEO \u2022 ' + exportDuration.toFixed(1) + 's';
                }
                const blob = await studioClient.exportCanvasVideo(state.canvas, {
                    fps: 30,
                    durationSeconds: exportDuration,
                    filename: 'family_heritage_reel_' + Date.now()
                });
                if (hudStatus) {
                    hudStatus.textContent = 'COMPILED (' + Math.round(blob.size / 1024) + ' KB) \u2022 READY';
                }
            } else {
                // Fallback frame capture if MediaRecorder is unavailable
                const link = document.createElement('a');
                link.download = 'family_heritage_reel_' + Date.now() + '.png';
                link.href = state.canvas.toDataURL('image/png');
                link.click();
            }
        } catch (e) {
            console.error('Export error:', e);
            if (hudStatus) {
                hudStatus.textContent = 'EXPORT ERROR \u2022 STANDBY';
            }
        } finally {
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

    // Bind all studio controls via addEventListener (CSP-safe & reliable)
    document.getElementById('btnTriggerUpload')?.addEventListener('click', () => document.getElementById('fvsFileInput')?.click());
    document.getElementById('fvsDropzone')?.addEventListener('click', () => document.getElementById('fvsFileInput')?.click());
    document.getElementById('btnExportDownload')?.addEventListener('click', () => window.exportLivingReelVideo());
    document.getElementById('fvsFileInput')?.addEventListener('change', function() { window.handleDirectFileSelect(this); });
    document.getElementById('fvsAudioInput')?.addEventListener('change', function() { window.handleAudioSelect(this); });
    document.getElementById('fvsTimelineSlider')?.addEventListener('input', function() { window.scrubTimeline(this.value); });
    document.getElementById('btnPlayPause')?.addEventListener('click', () => window.togglePlayback());
    document.getElementById('btnRestart')?.addEventListener('click', () => window.restartPlayback());
    document.getElementById('btnMuteToggle')?.addEventListener('click', () => window.toggleAudioMute());
    document.getElementById('btnAudioTrigger')?.addEventListener('click', () => document.getElementById('fvsAudioInput')?.click());
    document.getElementById('btnPublishTrigger')?.addEventListener('click', () => window.openPublishModal());
    document.getElementById('btnModeSolo')?.addEventListener('click', () => window.setMatrixMode('solo'));
    document.getElementById('btnModeQuad')?.addEventListener('click', () => window.setMatrixMode('quad_view'));
    document.getElementById('btnModeSplit')?.addEventListener('click', () => window.setMatrixMode('split_duo'));
    document.getElementById('fvsPublishForm')?.addEventListener('submit', (e) => window.submitPublishedReel(e));

    document.querySelectorAll('.fvs-tpl-card').forEach(card => {
        card.addEventListener('click', () => {
            const tplId = card.getAttribute('data-template');
            if (tplId) window.selectTemplate(tplId);
        });
    });

    // Kick off animation loop
    renderFrame();
})();
</script>
@endpush
