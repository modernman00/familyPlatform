@extends('layouts.profileBase')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Great+Vibes&family=Montserrat:wght@500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
<style>
.gold-embossed-text {
    background: linear-gradient(180deg, #fff6b3 0%, #ffd700 35%, #d4af37 70%, #9a741e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 3px 8px rgba(0,0,0,0.85));
}
.gold-shield-card {
    background: linear-gradient(180deg, rgba(8, 28, 20, 0.88) 0%, rgba(3, 14, 10, 0.94) 100%);
    border: 2px solid #d4af37;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5), inset 0 0 15px rgba(212, 175, 55, 0.2);
}
.gold-shield-card-blue {
    background: linear-gradient(180deg, rgba(8, 20, 48, 0.88) 0%, rgba(3, 8, 24, 0.94) 100%);
    border: 2px solid #d4af37;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5), inset 0 0 15px rgba(212, 175, 55, 0.2);
}
.gold-ribbon-bar {
    background: linear-gradient(90deg, transparent 0%, rgba(212, 175, 55, 0.85) 50%, transparent 100%);
    height: 2px;
}
.pearl-embossed-text {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 40%, #e2e8f0 75%, #cbd5e1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.9)) drop-shadow(0 0 15px rgba(212,175,55,0.45));
}
.dress-code-seal {
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: radial-gradient(circle, #1e1b18 0%, #090807 100%);
    border: 2.5px dashed #ffd700;
    box-shadow: 0 6px 18px rgba(0,0,0,0.7), inset 0 0 12px rgba(255,215,0,0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 6px;
}
.vip-glass-pill {
    background: rgba(15, 23, 42, 0.78);
    border: 1.5px solid rgba(212, 175, 55, 0.65);
    border-radius: 14px;
    backdrop-filter: blur(8px);
}
.ticket-brush-banner {
    background: linear-gradient(90deg, rgba(5,5,10,0.95) 0%, rgba(20,16,8,0.98) 50%, rgba(5,5,10,0.95) 100%);
    border-top: 2px solid #d4af37;
    border-bottom: 2px solid #d4af37;
    box-shadow: 0 6px 20px rgba(0,0,0,0.8);
}

/* 🎨 USER-CENTRIC FLYER STUDIO PRESET BUTTONS (ZERO RED FONT INVARIANT & 44PX HIT TARGET) */
.flyer-preset-btn {
    background: #ffffff !important;
    color: #1e293b !important;
    border: 1.5px solid #cbd5e1 !important;
    font-weight: 600 !important;
    font-size: 0.82rem !important;
    padding: 0.45rem 1rem !important;
    border-radius: 9999px !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    min-height: 44px;
    min-width: 44px;
    scroll-snap-align: start;
}
.flyer-preset-btn:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
    transform: translateY(-1px);
}
.flyer-preset-btn.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #d4af37 !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25) !important;
}
.flyer-preset-btn:focus,
.flyer-preset-btn:focus-visible,
.flyer-preset-btn:active {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.45) !important;
    color: #ffffff !important;
}

/* Format Switcher Pills (44px Minimum Hit Area) */
.flyer-format-btn {
    background: #ffffff;
    color: #475569;
    border: 1.5px solid #e2e8f0;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.45rem 0.95rem;
    border-radius: 12px;
    transition: all 0.18s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    min-height: 44px;
    scroll-snap-align: start;
}
.flyer-format-btn:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
}
.flyer-format-btn.active {
    background: #0f172a !important;
    color: #fce79a !important;
    border-color: #d4af37 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.18);
}

/* 3D Holographic Perspective & Parallax Wrap */
.flyer-3d-wrap {
    perspective: 1200px;
    transform-style: preserve-3d;
}
.flyer-3d-card {
    transition: transform 0.15s ease-out, box-shadow 0.2s ease;
    transform-style: preserve-3d;
    will-change: transform;
}
.flyer-hologram-sheen {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 25;
    background: linear-gradient(135deg, rgba(255,255,255,0) 0%, rgba(255, 215, 0, 0.14) 40%, rgba(255,255,255,0.3) 50%, rgba(0, 255, 234, 0.14) 60%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.flyer-3d-card.tilt-active .flyer-hologram-sheen {
    opacity: 1;
}

/* AR live room preview modal */
#arRoomModal .modal-content {
    background: #090d16;
    color: #ffffff;
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.12);
}
.ar-viewfinder {
    position: relative;
    width: 100%;
    height: 480px;
    border-radius: 18px;
    overflow: hidden;
    background: #020617;
    touch-action: none;
}
.ar-poster-proxy {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    cursor: grab;
    box-shadow: 0 25px 60px rgba(0,0,0,0.85);
    border: 4px solid #d4af37;
    border-radius: 8px;
    transition: box-shadow 0.2s;
    user-select: none;
    z-index: 10;
}
.ar-poster-proxy:active {
    cursor: grabbing;
}

/* Horizontal single-row scrollable pill strips (Ergonomic Touch & Snapping) */
.flyer-horiz-strip {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: thin;
    padding-bottom: 6px;
    padding-top: 2px;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
}
.flyer-horiz-strip::-webkit-scrollbar {
    height: 5px;
}
.flyer-horiz-strip::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.flyer-horiz-strip .btn-sm {
    min-height: 38px;
    padding: 0.35rem 0.75rem !important;
    font-size: 0.74rem !important;
    scroll-snap-align: start;
}

/* Luxury Atelier Form & Input Styling (High-Status VIP Craftsmanship) */
.luxury-card-box {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1rem 1.15rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.luxury-card-box:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}
.luxury-card-gold {
    background: linear-gradient(135deg, #fffdf8 0%, #fefcf3 100%);
    border: 1.5px solid #e6ca65;
    border-radius: 16px;
    padding: 1.15rem;
    box-shadow: 0 4px 16px rgba(212, 175, 55, 0.09);
    position: relative;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.luxury-card-gold:hover {
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.15);
    border-color: #d4af37;
}
.luxury-card-plaque-1 {
    background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
    border: 1.5px solid #86efac;
    border-radius: 16px;
    padding: 1.15rem;
    box-shadow: 0 4px 14px rgba(22, 101, 52, 0.05);
    transition: all 0.2s ease;
}
.luxury-card-plaque-1:hover {
    box-shadow: 0 6px 18px rgba(22, 101, 52, 0.08);
}
.luxury-card-plaque-2 {
    background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
    border: 1.5px solid #93c5fd;
    border-radius: 16px;
    padding: 1.15rem;
    box-shadow: 0 4px 14px rgba(30, 64, 175, 0.05);
    transition: all 0.2s ease;
}
.luxury-card-plaque-2:hover {
    box-shadow: 0 6px 18px rgba(30, 64, 175, 0.08);
}
.luxury-card-ribbon {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1.5px solid #fde68a;
    border-radius: 16px;
    padding: 1.15rem;
    box-shadow: 0 4px 14px rgba(212, 175, 55, 0.08);
    transition: all 0.2s ease;
}
.luxury-field-label {
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #334155;
    margin-bottom: 0.35rem;
    display: flex;
    align-items: center;
}
.luxury-input {
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0.55rem 0.85rem;
    font-size: 0.88rem;
    color: #0f172a;
    background-color: #ffffff;
    transition: all 0.15s ease-in-out;
}
.luxury-input:focus {
    border-color: #d4af37 !important;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.22) !important;
    background-color: #ffffff;
    outline: none;
}
.luxury-input-hero {
    border: 2px solid #d4af37;
    border-radius: 0 12px 12px 0;
    padding: 0.65rem 1rem;
    font-size: 1.05rem;
    font-weight: 800;
    color: #1e1b18;
    background: #ffffff;
    letter-spacing: 0.03em;
}
.luxury-input-hero:focus {
    border-color: #aa771c !important;
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.3) !important;
    outline: none;
}
.luxury-input-addon {
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-right: none;
    border-radius: 10px 0 0 10px;
    color: #64748b;
    font-size: 0.88rem;
    padding: 0 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.input-group > .luxury-input:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">
    {{-- Hub Header --}}
    <div class="ai-hub-hero text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white shadow-sm small fw-bold mb-3" style="color: #7b03fc;">
            <i class="bi bi-stars"></i> ✨ Studio AI Family Intelligence
        </div>
        <h1 class="h2 fw-bold text-dark mb-2">The Fun Place</h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 680px;">
            Your household creative playground for 15-minute fridge meal ideas, transcribed voice notes, illustrated family flyers, and Family Game Show.
        </p>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-white rounded-4 shadow-sm mb-4 border" id="familyAiTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
                    id="tab-fridge-btn" data-bs-toggle="pill" data-bs-target="#tab-fridge" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-egg-fried text-success"></i> 🥦 Fridge Chef
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
                    id="tab-voice-btn" data-bs-toggle="pill" data-bs-target="#tab-voice" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-mic-fill text-warning"></i> 🎤 Voice Notes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
                    id="tab-flyer-btn" data-bs-toggle="pill" data-bs-target="#tab-flyer" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-image-fill text-info"></i> 🎨 Family Flyer Studio
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
                    id="tab-trivia-btn" data-bs-toggle="pill" data-bs-target="#tab-trivia" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-trophy-fill text-danger"></i> 🎮 Family Game Show
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a href="/family/roundtable" class="nav-link rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100 text-dark" style="min-height: 44px; border: 1.5px dashed #d4af37; background: #fffdf5;">
                <i class="bi bi-shield-shaded text-warning"></i> 🛡️ Family RoundTable <i class="bi bi-box-arrow-up-right small text-muted"></i>
            </a>
        </li>
    </ul>

    {{-- Panes --}}
    <div class="tab-content" id="familyAiContent">

        {{-- 🥦 TAB 1: FRIDGE MEAL PLANNER --}}
        <div class="tab-pane fade show active" id="tab-fridge" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-camera-fill text-success me-2"></i> Snap Your Fridge</h4>
                        <p class="text-muted small mb-4">Upload a quick snap of your open fridge, crisper drawer, or pantry. Our AI vision will identify ingredients and craft 2 fast 15-minute family recipes.</p>

                        <div class="drop-zone border-2 border-dashed rounded-4 p-4 text-center mb-3 bg-light" 
                             id="fridge-drop-zone"
                             onclick="document.getElementById('fridge-photo-input').click()"
                             style="cursor: pointer; border-color: #cbd5e1;">
                            <input type="file" id="fridge-photo-input" accept="image/*" class="d-none" onchange="previewFridgePhoto(this)">
                            <i class="bi bi-cloud-arrow-up display-4 text-success d-block mb-2"></i>
                            <span class="fw-bold text-dark d-block">Click to upload photo</span>
                            <span class="text-muted small">JPG, PNG, or HEIC (up to 25MB &mdash; auto-optimised)</span>
                        </div>

                        <div id="photo-preview-wrap" style="display:none;" class="mb-3 text-center">
                            <img id="fridge-preview-img" src="" alt="Fridge Preview" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px;">
                        </div>

                        <button type="button" id="btn-scan-fridge" class="btn btn-success rounded-pill w-100 fw-bold py-2" onclick="scanFridge()" style="min-height: 44px;">
                            <i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook
                        </button>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-book-half text-primary me-2"></i> 15-Minute Recipe Ideas</h4>
                        
                        <div id="detected-ingredients-wrap" style="display:none;" class="mb-4">
                            <h6 class="fw-bold small text-muted text-uppercase mb-2">Detected Ingredients:</h6>
                            <div id="ingredients-badges" class="d-flex flex-wrap gap-1"></div>
                        </div>

                        <div id="recipes-list" class="d-flex flex-column gap-3">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-basket display-4 d-block mb-2 text-secondary opacity-50"></i>
                                <span>No fridge photo analysed yet. Upload a picture of your pantry or fridge to see instant meal ideas.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎤 TAB 3: VOICE NOTES --}}
        <div class="tab-pane fade" id="tab-voice" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center mx-auto" style="max-width: 600px;">
                <h3 class="fw-bold mb-2">Family Voice Notes</h3>
                <p class="text-muted small mb-4">Dictate chore checklists, grocery items, or family memories effortlessly with instantaneous voice recognition and resilient AI transcription.</p>

                <div class="d-flex justify-content-center mb-4">
                    <button type="button" id="fam-voice-btn" class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
                            onclick="toggleFamVoice()" 
                            aria-label="Toggle voice recording"
                            style="width: 100px; height: 100px; background: linear-gradient(135deg, #7b03fc, #ec4899); border: none; color: white; font-size: 2.2rem; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="bi bi-mic-fill" id="fam-mic-icon"></i>
                    </button>
                </div>

                <div id="fam-voice-status" class="small fw-semibold text-muted mb-4">Click to Start Recording</div>

                <div class="text-start">
                    <label class="form-label fw-semibold small" for="fam-voice-output">Dictated Notes</label>
                    <textarea id="fam-voice-output" class="form-control rounded-3" rows="4" placeholder="Your spoken notes will appear here in real time..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
                        <button type="button" id="btn-copy-fam-voice" class="btn btn-outline-primary rounded-pill btn-sm px-3 fw-bold btn-spin-on-click" onclick="copyFamVoice()" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-clipboard me-1" id="btn-copy-fam-icon"></i> <span id="btn-copy-fam-text">Copy to Clipboard</span>
                        </button>
                        <button type="button" id="btn-clear-fam-voice" class="btn btn-outline-secondary rounded-pill btn-sm px-3 fw-bold" onclick="clearFamVoice()" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            <i class="bi bi-eraser me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎨 TAB 4: FAMILY FLYER STUDIO --}}
        <div class="tab-pane fade" id="tab-flyer" role="tabpanel">
            <div class="row g-4">
                {{-- Left Column: Controls & Studio Tools (Vertical Ergonomic Architecture) --}}
                <div class="col-lg-5" id="flyerControlsColumn">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        {{-- Top Header & Linked Event Bar --}}
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-stars text-warning me-1"></i> Event Poster &amp; Gig Studio</h4>
                                <small class="text-muted">Customise celebration posters, live gig flyers, stories &amp; AR designs.</small>
                            </div>
                        </div>

                        {{-- 🎨 Choose Template & Vibe (Clean Vertical Category & Grid Deck) --}}
                        <div class="p-3 rounded-4 mb-3 border bg-light">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold small text-dark mb-0">
                                    <i class="bi bi-grid-fill text-warning me-1"></i> Choose Template &amp; Vibe
                                </label>
                                <span class="badge bg-dark text-warning small px-2 py-1 fw-bold">13 Styles</span>
                            </div>

                            {{-- Category Filter Pills --}}
                            <div class="d-flex gap-1 mb-2 overflow-x-auto pb-1 flyer-horiz-strip" style="scrollbar-width: none;">
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold template-cat-filter active btn-dark" id="filterCatAll" onclick="filterTemplateCategory('all')" style="min-height: 36px; font-size: 0.75rem;">All</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold template-cat-filter btn-light border" id="filterCatCelebration" onclick="filterTemplateCategory('celebration')" style="min-height: 36px; font-size: 0.75rem;">🎂 Celebrations</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold template-cat-filter btn-light border" id="filterCatGigs" onclick="filterTemplateCategory('gigs')" style="min-height: 36px; font-size: 0.75rem;">🎸 Gigs &amp; Shows</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold template-cat-filter btn-light border" id="filterCatAr" onclick="filterTemplateCategory('ar')" style="min-height: 36px; font-size: 0.75rem;">🔮 3D &amp; AR</button>
                            </div>

                            {{-- Grid of Template Buttons (Responsive 2-Col Grid) --}}
                            <div class="row g-2" id="studioTemplateGrid">
                                <div class="col-6 tpl-item tpl-cat-celebration">
                                    <button type="button" id="btnPresetBirthday" class="flyer-preset-btn w-100 justify-content-center active" onclick="applyBirthdayPreset()">🎂 Birthday</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-celebration">
                                    <button type="button" id="btnPresetJubilee" class="flyer-preset-btn w-100 justify-content-center" onclick="applyJubileePreset()">👑 Golden Jubilee</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-celebration">
                                    <button type="button" id="btnPresetFiesta" class="flyer-preset-btn w-100 justify-content-center" onclick="applyFiestaPreset()">🪅 Fiesta</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetGigHeadline" class="flyer-preset-btn w-100 justify-content-center" onclick="applyGigPreset('headline_gig')">🎸 Headline Gig</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetGigRave" class="flyer-preset-btn w-100 justify-content-center" onclick="applyGigPreset('dj_rave')">🎧 DJ Rave</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetGigComedy" class="flyer-preset-btn w-100 justify-content-center" onclick="applyGigPreset('comedy_night')">🎤 Comedy</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetGigJazz" class="flyer-preset-btn w-100 justify-content-center" onclick="applyGigPreset('jazz_lounge')">🎷 Afro-Jazz</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetGigFestival" class="flyer-preset-btn w-100 justify-content-center" onclick="applyGigPreset('festival_show')">🎪 Festival</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-gigs">
                                    <button type="button" id="btnPresetLiveShow" class="flyer-preset-btn w-100 justify-content-center" onclick="applyLiveShowPreset()">🎭 Live Show &amp; Concert</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-ar">
                                    <button type="button" id="btnPresetArHologram" class="flyer-preset-btn w-100 justify-content-center" onclick="applyArPreset('hologram')">🔮 AR Hologram</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-ar">
                                    <button type="button" id="btnPresetArCyber" class="flyer-preset-btn w-100 justify-content-center" onclick="applyArPreset('cyber_emerald')">⚡ AR Cyber</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-ar">
                                    <button type="button" id="btnPresetArRoyal" class="flyer-preset-btn w-100 justify-content-center" onclick="applyArPreset('royal_plaque')">🏛️ AR Royal</button>
                                </div>
                                <div class="col-6 tpl-item tpl-cat-ar">
                                    <button type="button" id="btnPresetArNeon" class="flyer-preset-btn w-100 justify-content-center" onclick="applyArPreset('neon_glass')">💎 AR Neon</button>
                                </div>
                            </div>
                        </div>

                        {{-- Tabbed Studio Controls: Content vs Visual Customiser vs AR Tools --}}
                        <ul class="nav nav-tabs nav-fill mb-3" id="flyerControlTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active small py-2 fw-semibold" id="tab-ctrl-content-btn" data-bs-toggle="tab" data-bs-target="#tab-ctrl-content" type="button" role="tab">
                                    <i class="bi bi-pencil-square me-1"></i> Content &amp; Photo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link small py-2 fw-semibold" id="tab-ctrl-layout-btn" data-bs-toggle="tab" data-bs-target="#tab-ctrl-layout" type="button" role="tab">
                                    <i class="bi bi-sliders me-1"></i> Move &amp; Style
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link small py-2 fw-semibold" id="tab-ctrl-ar-btn" data-bs-toggle="tab" data-bs-target="#tab-ctrl-ar" type="button" role="tab">
                                    <i class="bi bi-view-list me-1"></i> AR Room View
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="flyerControlTabContent">
                            {{-- SUBTAB 1: CONTENT EDITING --}}
                            <div class="tab-pane fade show active" id="tab-ctrl-content" role="tabpanel">
                                
                                {{-- 📷 UNIVERSAL PHOTO & ARTWORK STUDIO (WORKS ACROSS CELEBRATION, GIG & SHOW MODES) --}}
                                <div class="mb-3 p-3 rounded-4 bg-light border" id="universalPhotoStudioCard">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold small mb-0 text-dark">
                                            <i class="bi bi-person-bounding-box text-primary me-1"></i> <span id="labelPhotoTitle">Celebrant / Artist Photo</span>
                                        </label>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 fw-semibold" onclick="clearCelebrantPhoto(true)" id="btnRemovePhoto" style="font-size: 0.75rem; display: none; min-height: 28px;">
                                            <i class="bi bi-trash me-1"></i> Remove Photo
                                        </button>
                                    </div>
                                    <input type="file" id="fam-celebrant-file" class="form-control rounded-3 form-control-sm mb-2" accept="image/*" onchange="handleCelebrantPhoto(event)">
                                    
                                    {{-- Live Active Photo Status & Scaling Controls --}}
                                    <div class="p-2 rounded-3 bg-white border" id="celebrantScaleRow" style="display: none !important;">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                                <i class="bi bi-check-circle-fill me-1"></i> Photo Active &amp; Synced
                                            </span>
                                            <div class="d-flex align-items-center gap-2 ms-auto">
                                                <label class="small text-muted mb-0" style="font-size: 0.72rem;">Scale:</label>
                                                <input type="range" id="celebrant-scale" class="form-range" min="50" max="150" value="100" style="width: 85px;" oninput="updateCelebrantScale(this.value)">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODE 1 CONTROLS: CELEBRATION GALA (BIRTHDAY, JUBILEE, FIESTA, AR) --}}
                                <div id="controlsTransatlantic">
                                    {{-- Proclamation & Script Intro Card --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge" style="background: #f1f5f9; color: #1e293b; font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase; font-weight: 700; border: 1px solid #e2e8f0;">
                                                <i class="bi bi-megaphone-fill text-warning me-1"></i> Proclamation Banners
                                            </span>
                                            <span class="text-muted small" style="font-size: 0.74rem;">Invitation Header</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Top Headline</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-card-heading"></i></span>
                                                    <input type="text" id="flyer-headline" class="form-control luxury-input form-control-sm" value="SAVE THE DATE" oninput="updateFamFlyerPreview()" placeholder="e.g. SAVE THE DATE">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Intro Script</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-pen"></i></span>
                                                    <input type="text" id="flyer-intro" class="form-control luxury-input form-control-sm" value="For the Grand Celebration of" oninput="updateFamFlyerPreview()" placeholder="e.g. For the Grand Celebration of">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hero Title Centerpiece (Regal 3D Gold Card) --}}
                                    <div class="luxury-card-gold mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge" style="background: linear-gradient(135deg, #d4af37, #aa771c); color: #ffffff; font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 700; box-shadow: 0 2px 6px rgba(170, 119, 28, 0.35);">
                                                <i class="bi bi-crown-fill me-1"></i> Regal Centerpiece
                                            </span>
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle small fw-semibold" style="font-size: 0.72rem;">3D Gold Embossed</span>
                                        </div>
                                        <label class="luxury-field-label" style="color: #78350f;">Hero Title (3D Gold)</label>
                                        <div class="input-group">
                                            <span class="input-group-text luxury-input-addon" style="border-color: #d4af37; background: #fffdf5; color: #b8860b;"><i class="bi bi-stars"></i></span>
                                            <input type="text" id="flyer-hero-title" class="form-control luxury-input luxury-input-hero" value="BIRTHDAY GALA" oninput="updateFamFlyerPreview()" placeholder="e.g. BIRTHDAY GALA">
                                        </div>
                                    </div>

                                    {{-- Territory & Family Creed Card --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge" style="background: #f1f5f9; color: #1e293b; font-size: 0.72rem; letter-spacing: 0.04em; text-transform: uppercase; font-weight: 700; border: 1px solid #e2e8f0;">
                                                <i class="bi bi-globe-americas text-primary me-1"></i> Scope &amp; Tagline
                                            </span>
                                            <span class="text-muted small" style="font-size: 0.74rem;">Sub-Headings</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Subtitle / Region</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-geo-alt"></i></span>
                                                    <input type="text" id="flyer-subtitle" class="form-control luxury-input form-control-sm" value="IN NIGERIA &amp; UK" oninput="updateFamFlyerPreview()" placeholder="e.g. IN NIGERIA &amp; UK">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Emotional Motto / Script</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-quote"></i></span>
                                                    <input type="text" id="flyer-motto" class="form-control luxury-input form-control-sm" value="Two Countries • One Celebration • Same Great Family" oninput="updateFamFlyerPreview()" placeholder="e.g. Two Countries • One Celebration">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Destination Shield 1 --}}
                                    <div class="luxury-card-plaque-1 mb-3" id="ctrlPlaque1Wrap">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success text-white px-2 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                                    <i class="bi bi-shield-fill-check me-1"></i> PLAQUE 1
                                                </span>
                                                <div class="fw-bold small text-dark"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Celebration Plaque 1 (Left Shield)</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 small" onclick="toggleLayerVisibility('plaque1')" id="btnTogglePlaque1" style="font-size: 0.75rem; border-radius: 20px; font-weight: 600; min-height: 28px;">Hide</button>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-sm-4">
                                                <label class="luxury-field-label">Flag / Icon</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-flag"></i></span>
                                                    <input type="text" id="flyer-dest1-flag" class="form-control luxury-input form-control-sm text-center" value="🇳🇬" placeholder="Flag/Icon" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <label class="luxury-field-label">Celebration Title</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-geo"></i></span>
                                                    <input type="text" id="flyer-dest1-title" class="form-control luxury-input form-control-sm" value="NIGERIA Celebration" placeholder="e.g. NIGERIA Celebration" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Date &amp; Schedule</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-calendar-event"></i></span>
                                                    <input type="text" id="flyer-dest1-date" class="form-control luxury-input form-control-sm" value="DATE: AUGUST, 2027" placeholder="e.g. DATE: AUGUST, 2027" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Venue / Coordinates</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-pin-map"></i></span>
                                                    <input type="text" id="flyer-dest1-venue" class="form-control luxury-input form-control-sm" value="VENUE: COMING SOON" placeholder="e.g. VENUE: COMING SOON" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Destination Shield 2 --}}
                                    <div class="luxury-card-plaque-2 mb-3" id="ctrlPlaque2Wrap">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary text-white px-2 py-1" style="font-size: 0.72rem; font-weight: 700;">
                                                    <i class="bi bi-shield-fill-check me-1"></i> PLAQUE 2
                                                </span>
                                                <div class="fw-bold small text-dark"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Celebration Plaque 2 (Right Shield)</div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 small" onclick="toggleLayerVisibility('plaque2')" id="btnTogglePlaque2" style="font-size: 0.75rem; border-radius: 20px; font-weight: 600; min-height: 28px;">Hide</button>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-sm-4">
                                                <label class="luxury-field-label">Flag / Icon</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-flag"></i></span>
                                                    <input type="text" id="flyer-dest2-flag" class="form-control luxury-input form-control-sm text-center" value="🇬🇧" placeholder="Flag/Icon" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <label class="luxury-field-label">Celebration Title</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-geo"></i></span>
                                                    <input type="text" id="flyer-dest2-title" class="form-control luxury-input form-control-sm" value="UK Celebration" placeholder="e.g. UK Celebration" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Date &amp; Schedule</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-calendar-event"></i></span>
                                                    <input type="text" id="flyer-dest2-date" class="form-control luxury-input form-control-sm" value="DATE: SEPTEMBER, 2027" placeholder="e.g. DATE: SEPTEMBER, 2027" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Venue / Coordinates</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-pin-map"></i></span>
                                                    <input type="text" id="flyer-dest2-venue" class="form-control luxury-input form-control-sm" value="VENUE: COMING SOON" placeholder="e.g. VENUE: COMING SOON" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom Gold Ribbon --}}
                                    <div class="luxury-card-ribbon mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge" style="background: linear-gradient(135deg, #d4af37, #9a741e); color: #ffffff; font-size: 0.72rem; font-weight: 700;">
                                                    <i class="bi bi-bookmark-star-fill me-1"></i> GOLD RIBBON
                                                </span>
                                                <label class="luxury-field-label mb-0" style="color: #78350f;">Bottom Gold Ribbon Text</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 small" onclick="toggleLayerVisibility('ribbon')" id="btnToggleRibbon" style="font-size: 0.75rem; border-radius: 20px; font-weight: 600; min-height: 28px;">Hide</button>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text luxury-input-addon" style="border-color: #fde68a; background: #fffbeb; color: #b8860b;"><i class="bi bi-award-fill"></i></span>
                                            <input type="text" id="flyer-bottom-ribbon" class="form-control luxury-input" value="★ More Details to Follow ★" oninput="updateFamFlyerPreview()" placeholder="★ More Details to Follow ★">
                                        </div>
                                    </div>
                                </div>

                                {{-- MODE 2 CONTROLS: LIVE SHOW & CONCERT (FORMERLY ALL WHITE) --}}
                                <div id="controlsAllWhite" style="display: none;">
                                    {{-- Presenter / Host Line --}}
                                    <div class="luxury-card-box mb-3">
                                        <label class="luxury-field-label">Top Presenter / Host Line</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text luxury-input-addon"><i class="bi bi-mic-fill text-warning"></i></span>
                                            <input type="text" id="aw-presenter" class="form-control luxury-input form-control-sm" value="FAMILY ENTERTAINMENT PRESENTS" oninput="updateFamFlyerPreview()">
                                        </div>
                                    </div>

                                    {{-- Hero Title (3D Pearl/Gold) & Brush Script --}}
                                    <div class="luxury-card-gold mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge" style="background: linear-gradient(135deg, #d4af37, #aa771c); color: #ffffff; font-size: 0.72rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 700;">
                                                <i class="bi bi-music-note-beamed me-1"></i> Headliner &amp; Gig
                                            </span>
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle small fw-semibold" style="font-size: 0.72rem;">3D Pearl / Gold</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label" style="color: #78350f;">Hero Title</label>
                                                <input type="text" id="aw-hero-main" class="form-control luxury-input fw-bold" value="LIVE SHOW" oninput="updateFamFlyerPreview()" placeholder="LIVE SHOW">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label" style="color: #78350f;">Gold Brush Script</label>
                                                <input type="text" id="aw-hero-script" class="form-control luxury-input" value="&amp; CONCERT" oninput="updateFamFlyerPreview()" placeholder="&amp; CONCERT">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Location Pill & Dress Code --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Location Pill Badge</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-geo-alt"></i></span>
                                                    <input type="text" id="aw-city-pill" class="form-control luxury-input form-control-sm" value="IN LONDON" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="luxury-field-label">Dress Code Seal</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-tag"></i></span>
                                                    <input type="text" id="aw-dress-code" class="form-control luxury-input form-control-sm" value="Dress Code • GLAMOROUS &amp; CHIC" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Date, Time & Venue --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="fw-bold small text-dark mb-2"><i class="bi bi-calendar2-check text-warning me-1"></i> Event Date, Time &amp; Venue</div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-sm-7">
                                                <label class="luxury-field-label">Date</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-calendar-event"></i></span>
                                                    <input type="text" id="aw-date" class="form-control luxury-input form-control-sm" value="FRIDAY 30TH MAY 2026" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <label class="luxury-field-label">Time</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-clock"></i></span>
                                                    <input type="text" id="aw-time" class="form-control luxury-input form-control-sm" value="7PM - MIDNIGHT" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="luxury-field-label">Venue Coordinates</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text luxury-input-addon"><i class="bi bi-building"></i></span>
                                                <input type="text" id="aw-venue" class="form-control luxury-input form-control-sm" value="THE GRAND ARENA, LONDON SW1" oninput="updateFamFlyerPreview()">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- VIP Highlights (Vertical as requested previously) --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="fw-bold small text-dark mb-2"><i class="bi bi-stars text-warning me-1"></i> VIP Highlights (Vertical)</div>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text luxury-input-addon"><i class="bi bi-check2-circle text-success"></i></span>
                                                <input type="text" id="aw-feat1" class="form-control luxury-input form-control-sm" value="🎧 LIVE BAND" oninput="updateFamFlyerPreview()">
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text luxury-input-addon"><i class="bi bi-check2-circle text-success"></i></span>
                                                <input type="text" id="aw-feat2" class="form-control luxury-input form-control-sm" value="🥂 VIP TABLES" oninput="updateFamFlyerPreview()">
                                            </div>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text luxury-input-addon"><i class="bi bi-check2-circle text-success"></i></span>
                                                <input type="text" id="aw-feat3" class="form-control luxury-input form-control-sm" value="👥 RED CARPET" oninput="updateFamFlyerPreview()">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tickets & VIP Tables (Vertical as requested) --}}
                                    <div class="luxury-card-box mb-3">
                                        <div class="d-flex flex-column gap-2">
                                            <div>
                                                <label class="luxury-field-label">Tickets Platform</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-ticket-perforated text-primary"></i></span>
                                                    <input type="text" id="aw-tickets" class="form-control luxury-input form-control-sm" value="TICKETS: RSVP VIA FAMILY PLATFORM" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="luxury-field-label">VIP Table Reservations</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text luxury-input-addon"><i class="bi bi-telephone text-success"></i></span>
                                                    <input type="text" id="aw-rsvp" class="form-control luxury-input form-control-sm" value="RESERVATIONS: DM HOST OR APP" oninput="updateFamFlyerPreview()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="btn-generate-fam-flyer" class="btn btn-primary rounded-pill w-100 fw-bold py-2 mt-1" onclick="generateBackdropArt()" style="background: linear-gradient(135deg, #d4af37, #9a741e); border: none; min-height: 44px; color: #000; font-weight: 800;">
                                    <i class="bi bi-magic me-1"></i> Generate AI Atmosphere Backdrop
                                </button>
                            </div>

                            {{-- SUBTAB 2: MOVE, RESIZE, FONT & CONTAINER FREEDOM --}}
                            <div class="tab-pane fade" id="tab-ctrl-layout" role="tabpanel">
                                {{-- Poster Background Colour & Palette Swatches --}}
                                <div class="p-3 rounded-4 bg-light border mb-3" id="flyerBgColorCard">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold small text-dark mb-0">
                                            <i class="bi bi-palette-fill text-warning me-1"></i> Poster Background Colour
                                        </label>
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="small text-muted mb-0" style="font-size: 0.72rem;">Custom:</label>
                                            <input type="color" id="flyer-bg-color-picker" value="#021a12" oninput="setCustomFlyerBgColor(this.value, null)" style="width: 32px; height: 32px; padding: 0; border: 2px solid #cbd5e1; border-radius: 50%; cursor: pointer; background: transparent;" title="Pick Any Custom Background Colour">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <label class="small text-muted mb-0 text-nowrap" style="font-size: 0.72rem;">Fill Style:</label>
                                        <select class="form-select form-select-sm rounded-pill fw-semibold" id="flyer-bg-fill-style" style="font-size: 0.75rem; height: 32px;" onchange="setFlyerBgFillStyle(this.value)" aria-label="Background Fill Style">
                                            <option value="gradient" selected>✨ Luxury Gradient</option>
                                            <option value="solid">🎨 Solid Colour</option>
                                            <option value="radial">🌟 Spotlight Glow</option>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-1 flex-wrap" id="flyerBgSwatches">
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #021a12, #041424); color: #fce79a; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('emerald')" title="Emerald Night">Emerald</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #2a1f0a, #050403); color: #ffd700; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('obsidian')" title="Obsidian Gold">Obsidian</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #051937, #000c1e); color: #93c5fd; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('sapphire')" title="Sapphire Gala">Sapphire</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #3d0714, #0a0104); color: #fda4af; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('ruby')" title="Royal Ruby">Ruby</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #2e0854, #0d021a); color: #e9d5ff; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('purple')" title="Imperial Purple">Purple</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #1e1b4b, #090d16); color: #7dd3fc; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('indigo')" title="Electric Indigo Stage">Indigo</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #431407, #1e1b4b); color: #fdba74; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('sunset')" title="Festival Sunset">Sunset</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #4a1525, #1f070e); color: #fecdd3; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('rose')" title="Rose Blush">Rose</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #042f2e, #021014); color: #99f6e4; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('teal')" title="Midnight Teal">Teal</button>
                                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border" style="background: linear-gradient(135deg, #fdfbf7, #e6dec8); color: #1e293b; font-size: 0.75rem; min-height: 36px;" onclick="setFlyerBgScheme('champagne')" title="Champagne Cream">Cream</button>
                                    </div>
                                </div>
                                <div class="p-3 rounded-3 bg-light border mb-3">
                                    <label class="form-label fw-bold small text-dark mb-1"><i class="bi bi-hand-index-thumb text-primary me-1"></i> Select Element to Move &amp; Format</label>
                                    <select id="flyer-layer-picker" class="form-select rounded-3 mb-3" onchange="onSelectLayerToCustomise(this.value)">
                                        <option value="hero_title">👑 Hero Title</option>
                                        <option value="headline">Top Headline</option>
                                        <option value="intro">Intro Script</option>
                                        <option value="subtitle">Subtitle / Region</option>
                                        <option value="motto">Motto / Tagline</option>
                                        <option value="dest1">Plaque 1 (Left Shield)</option>
                                        <option value="dest2">Plaque 2 (Right Shield)</option>
                                        <option value="ribbon">Bottom Ribbon</option>
                                    </select>

                                    {{-- Font Family Picker --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark mb-1">Font Family</label>
                                        <select id="layer-font-family" class="form-select rounded-3 form-select-sm" onchange="updateSelectedLayerFontFamily(this.value)">
                                            <option value="'Cinzel', serif">Cinzel (Regal Classical Serif)</option>
                                            <option value="'Great Vibes', cursive">Great Vibes (Luxury Calligraphy Script)</option>
                                            <option value="'Montserrat', sans-serif">Montserrat (Geometric Modern Sans)</option>
                                            <option value="'Playfair Display', serif">Playfair Display (Editorial High-Fashion)</option>
                                            <option value="'Oswald', sans-serif">Oswald (Bold Stage &amp; Concert Title)</option>
                                            <option value="'Inter', sans-serif">Inter (Clean Contemporary UI)</option>
                                        </select>
                                    </div>

                                    {{-- Font Size Slider --}}
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold small text-dark mb-0">Font Size</label>
                                            <span id="layer-size-badge" class="badge bg-dark text-warning">35px</span>
                                        </div>
                                        <input type="range" id="layer-font-size" class="form-range" min="12" max="72" value="35" oninput="updateSelectedLayerFontSize(this.value)">
                                    </div>

                                    {{-- Position / Move Things Around --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark mb-1">Move Layer (Vertical &amp; Horizontal)</label>
                                        <div class="d-flex gap-2 align-items-center mb-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" onclick="moveSelectedLayerY(-8)">
                                                <i class="bi bi-arrow-up"></i> Move Up
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold" onclick="moveSelectedLayerY(8)">
                                                <i class="bi bi-arrow-down"></i> Move Down
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="resetSelectedLayerOffset()">
                                                Reset
                                            </button>
                                        </div>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setSelectedLayerAlign('left')"><i class="bi bi-text-left"></i> Left</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary active" onclick="setSelectedLayerAlign('center')"><i class="bi bi-text-center"></i> Centre</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setSelectedLayerAlign('right')"><i class="bi bi-text-right"></i> Right</button>
                                        </div>
                                    </div>

                                    {{-- Layer Action Buttons --}}
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill w-50" onclick="toggleSelectedLayerVisibility()" id="btnLayerVisibility">
                                            <i class="bi bi-eye-slash me-1"></i> Hide Layer
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill w-50" onclick="deleteSelectedLayer()">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>

                                {{-- Container Size & Frame Customiser --}}
                                <div class="p-3 rounded-3 bg-light border mb-3">
                                    <label class="form-label fw-bold small text-dark mb-2"><i class="bi bi-bounding-box text-success me-1"></i> Container &amp; Frame Settings</label>
                                    
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Poster Width</label>
                                            <span id="container-width-badge" class="badge bg-secondary">440px</span>
                                        </div>
                                        <input type="range" id="flyer-container-width" class="form-range" min="340" max="560" value="440" oninput="updateContainerWidth(this.value)">
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Corner Rounding</label>
                                            <span id="container-radius-badge" class="badge bg-secondary">24px</span>
                                        </div>
                                        <input type="range" id="flyer-container-radius" class="form-range" min="0" max="36" value="24" oninput="updateContainerRadius(this.value)">
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Background Atmosphere &amp; Custom Colour</label>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="color" id="flyer-bg-color-layout" value="#021a12" oninput="setCustomFlyerBgColor(this.value, null)" style="width: 24px; height: 24px; padding: 0; border: none; border-radius: 50%; cursor: pointer; background: transparent;" title="Pick Any Custom Background Colour">
                                                <span class="small text-muted" style="font-size: 0.7rem;">Any Colour</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('emerald')">Emerald Night</button>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('obsidian')">Obsidian Gold</button>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('sapphire')">Sapphire Gala</button>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('ruby')">Royal Ruby</button>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('purple')">Imperial Purple</button>
                                            <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-0 px-2" style="font-size: 0.72rem;" onclick="setFlyerBgScheme('champagne')">Champagne Cream</button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Add Custom Text Layer --}}
                                <div class="p-3 rounded-3 border bg-white text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3" onclick="promptAddCustomTextLayer()">
                                        <i class="bi bi-plus-circle me-1"></i> Add Custom Text Block
                                    </button>
                                </div>
                            </div>

                            {{-- SUBTAB 3: AUGMENTED REALITY (AR) TOOLS --}}
                            <div class="tab-pane fade" id="tab-ctrl-ar" role="tabpanel">
                                <div class="p-3 rounded-3 bg-light border mb-3">
                                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-badge-vr text-primary me-1"></i> Interactive AR Experience</h6>
                                    <p class="text-muted small mb-3">Preview your high-status family flyer in real-world 3D space, mount it on your living room wall via camera passthrough, and embed a scannable AR experience badge.</p>

                                    {{-- 3D Tilt Toggle --}}
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="toggleFlyerTiltSwitch" onchange="toggle3DTilt(this.checked)">
                                        <label class="form-check-label small fw-semibold text-dark" for="toggleFlyerTiltSwitch">
                                            3D Holographic Gyroscope &amp; Mouse Parallax Tilt
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Tilt your phone or move your mouse over the poster to see realistic gold foil reflections.</small>
                                    </div>

                                    {{-- AR QR Code Toggle --}}
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="toggleArQrBadgeSwitch" onchange="toggleArQrBadge(this.checked)">
                                        <label class="form-check-label small fw-semibold text-dark" for="toggleArQrBadgeSwitch">
                                            Embed Scannable AR 3D Experience QR Code
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Stamps a scannable QR badge on the poster that opens the 3D model on any smartphone.</small>
                                    </div>

                                    {{-- Launch AR Room Modal Button --}}
                                    <button type="button" class="btn btn-dark rounded-pill w-100 py-2 fw-bold text-white shadow-sm mb-2" onclick="launchArRoomView()" style="background: #0f172a; min-height: 44px;">
                                        <i class="bi bi-camera-video text-warning me-2"></i> Launch AR Camera Room View
                                    </button>
                                    <small class="text-muted text-center d-block" style="font-size: 0.72rem;">Mounts framed poster onto your real wall via device camera with instant photo snap.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Live Interactive Poster Preview & Action Hub --}}
                <div class="col-lg-7" id="flyerPreviewColumn">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="text-start">
                                <h5 class="fw-bold mb-0 text-dark">Live Interactive Poster</h5>
                                <small class="text-muted">Real-time luxury 3D gold &amp; AR composite preview</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="fam-flyer-badge" class="badge bg-warning-subtle text-dark border border-warning px-3 py-1 rounded-pill fw-semibold">
                                    <i class="bi bi-stars text-warning me-1"></i> Interactive Flyer Studio
                                </span>
                            </div>
                        </div>

                        {{-- Format & Aspect Ratio Switcher (Right Above the Live Poster) --}}
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3 flex-wrap" id="flyerFormatPills">
                            <button type="button" id="btnFmtPortrait" class="flyer-format-btn active" onclick="setFlyerFormat('portrait_3_4')">🖼️ Poster (3:4)</button>
                            <button type="button" id="btnFmtStory" class="flyer-format-btn" onclick="setFlyerFormat('story_9_16')">📱 Story (9:16)</button>
                            <button type="button" id="btnFmtSquare" class="flyer-format-btn" onclick="setFlyerFormat('square_1_1')">🔲 Square (1:1)</button>
                            <button type="button" id="btnFmtLandscape" class="flyer-format-btn" onclick="setFlyerFormat('landscape_16_9')">🖥️ Banner (16:9)</button>
                        </div>

                        {{-- 3D Wrap for Hologram Parallax --}}
                        <div class="flyer-3d-wrap mx-auto" style="width: 100%; max-width: 440px;" id="flyer3DWrap">
                            {{-- Visual Commemorative Gala Poster Container --}}
                            <div id="famFlyerCardPreview" class="flyer-3d-card rounded-4 text-white shadow-2xl position-relative overflow-hidden" 
                                 style="width: 100%; max-width: 440px; min-height: 620px; aspect-ratio: 3/4; background: linear-gradient(160deg, #021a12 0%, #041424 50%, #020712 100%); border: 3px solid #d4af37; box-shadow: 0 25px 50px rgba(0,0,0,0.6); transform: perspective(1000px) rotateX(0deg) rotateY(0deg);">
                                
                                {{-- Holographic Shimmer Overlay --}}
                                <div class="flyer-hologram-sheen" id="flyerSheen"></div>

                                {{-- Outer Gold Hairline Border --}}
                                <div class="position-absolute" style="top: 8px; left: 8px; right: 8px; bottom: 8px; border: 1px solid rgba(212, 175, 55, 0.45); border-radius: 12px; pointer-events: none; z-index: 10;"></div>

                                {{-- Corner Accents --}}
                                <div class="position-absolute" style="top: 14px; left: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                                <div class="position-absolute" style="top: 14px; right: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                                <div class="position-absolute" style="bottom: 14px; left: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                                <div class="position-absolute" style="bottom: 14px; right: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>

                                {{-- Atmosphere / Celebrant Photo Layer (Mode 1 - Hidden by default until user uploads portrait) --}}
                                <div id="prevCelebrantWrap" class="position-absolute top-0 start-0 h-100" style="width: 55%; overflow: hidden; z-index: 1; pointer-events: none; display: none;">
                                    <img id="prevCelebrantPhoto" src="" alt="Celebrant" 
                                         style="display: none; width: 100%; height: 100%; object-fit: cover; object-position: top center; mask-image: linear-gradient(to right, black 65%, transparent 100%); -webkit-mask-image: linear-gradient(to right, black 65%, transparent 100%);">
                                </div>

                                {{-- Artist / Performer Backdrop Photo Layer (Mode 2 - Live Show & Gigs) --}}
                                <div id="prevAwArtistPhotoWrap" class="position-absolute top-0 start-0 w-100 h-100" style="overflow: hidden; z-index: 1; pointer-events: none; display: none;">
                                    <img id="prevAwArtistPhoto" src="" alt="Performer" 
                                         style="display: none; width: 100%; height: 58%; object-fit: cover; object-position: top center; mask-image: linear-gradient(to bottom, black 50%, transparent 100%); -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%); opacity: 0.55;">
                                </div>

                                {{-- Background Golden Glow Layer --}}
                                <div class="position-absolute bottom-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 75% 20%, rgba(212, 175, 55, 0.22) 0%, transparent 65%); z-index: 2; pointer-events: none;"></div>

                                {{-- ========================================================== --}}
                                {{-- PREVIEW MODE 1: CELEBRATION GALA (BIRTHDAY, JUBILEE, FIESTA, AR) --}}
                                {{-- ========================================================== --}}
                                <div id="previewModeTransatlantic" class="position-relative d-flex flex-column justify-content-between h-100 p-3 pt-4" style="z-index: 5; min-height: 620px;">
                                    
                                    {{-- Header & Hero Title --}}
                                    <div id="prevHeaderColumn" class="text-center px-2" style="margin-left: 0;">
                                        <div class="text-center mb-1" id="wrap-headline">
                                            <div id="prevCrownIcon" style="font-size: 1.2rem; color: #ffd700; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.8));">👑</div>
                                            <h3 id="prevFlyerHeadline" class="fw-extrabold mb-0" 
                                                style="font-family: 'Cinzel', serif; font-size: 1.45rem; letter-spacing: 2px; color: #fce79a; text-shadow: 0 3px 8px rgba(0,0,0,0.9), 0 0 12px rgba(212,175,55,0.4);">
                                                SAVE THE DATE
                                            </h3>
                                            <div id="prevFlyerIntro" class="mb-1" 
                                                 style="font-family: 'Great Vibes', cursive; font-size: 1.15rem; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                                For the Grand Celebration of
                                            </div>
                                        </div>

                                        <div id="wrap-hero-title">
                                            <h1 id="prevFlyerHeroTitle" class="fw-black mb-0 gold-embossed-text" 
                                                style="font-family: 'Cinzel', serif; font-size: 2.2rem; line-height: 1.05; letter-spacing: 1px; font-weight: 900; text-align: center;">
                                                BIRTHDAY GALA
                                            </h1>
                                        </div>

                                        <div id="wrap-subtitle">
                                            <div id="prevFlyerSubtitle" class="fw-bold text-center mt-1" 
                                                 style="font-family: 'Montserrat', sans-serif; font-size: 0.85rem; letter-spacing: 2px; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                                IN NIGERIA &amp; UK
                                            </div>
                                        </div>

                                        <div class="gold-ribbon-bar my-1 mx-auto" style="width: 70%;" id="prevHeroDivider"></div>

                                        <div id="wrap-motto">
                                            <div id="prevFlyerMotto" class="text-center small fst-italic mb-2" 
                                                 style="font-family: 'Great Vibes', cursive; font-size: 1.05rem; color: #e2e8f0; text-shadow: 0 2px 4px rgba(0,0,0,0.9); line-height: 1.2;">
                                                Two Countries • One Celebration • Same Great Family
                                            </div>
                                        </div>
                                    </div>

                                    <div class="my-auto" id="prevCustomLayersContainer"></div>

                                    {{-- Twin Destination Plaques --}}
                                    <div class="row g-2 mb-2 px-1" id="wrap-plaques">
                                        {{-- Plaque 1 --}}
                                        <div class="col-6" id="prevDest1Col">
                                            <div class="gold-shield-card p-2 text-center h-100 position-relative">
                                                <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill p-1 shadow" style="background: #022c1b; border: 1.5px solid #d4af37; font-size: 0.9rem;">
                                                    <span id="prevDest1Flag">🇳🇬</span>
                                                </div>
                                                <div class="pt-2">
                                                    <div id="prevDest1Title" class="fw-bold small" style="font-family: 'Cinzel', serif; color: #fce79a; font-size: 0.72rem; letter-spacing: 0.5px;">NIGERIA Celebration</div>
                                                    <div class="mt-2 text-start px-1" style="font-size: 0.68rem;">
                                                        <div class="d-flex align-items-center gap-1 mb-1" style="color: #fbbf24;">
                                                            <i class="bi bi-calendar-event"></i>
                                                            <span id="prevDest1Date" class="fw-bold" style="font-size: 0.65rem;">DATE: AUGUST, 2027</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1" style="color: #fde68a;">
                                                            <i class="bi bi-geo-alt-fill"></i>
                                                            <span id="prevDest1Venue" class="fw-semibold" style="font-size: 0.65rem;">VENUE: COMING SOON</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Plaque 2 --}}
                                        <div class="col-6" id="prevDest2Col">
                                            <div class="gold-shield-card-blue p-2 text-center h-100 position-relative">
                                                <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill p-1 shadow" style="background: #081a3d; border: 1.5px solid #d4af37; font-size: 0.9rem;">
                                                    <span id="prevDest2Flag">🇬🇧</span>
                                                </div>
                                                <div class="pt-2">
                                                    <div id="prevDest2Title" class="fw-bold small" style="font-family: 'Cinzel', serif; color: #fce79a; font-size: 0.72rem; letter-spacing: 0.5px;">UK Celebration</div>
                                                    <div class="mt-2 text-start px-1" style="font-size: 0.68rem;">
                                                        <div class="d-flex align-items-center gap-1 mb-1" style="color: #fbbf24;">
                                                            <i class="bi bi-calendar-event"></i>
                                                            <span id="prevDest2Date" class="fw-bold" style="font-size: 0.65rem;">DATE: SEPTEMBER, 2027</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-1" style="color: #fde68a;">
                                                            <i class="bi bi-geo-alt-fill"></i>
                                                            <span id="prevDest2Venue" class="fw-semibold" style="font-size: 0.65rem;">VENUE: COMING SOON</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- AR QR Code Badge (Conditional) --}}
                                    <div id="prevArQrBadgeWrap" class="mb-1 text-center" style="display: none;">
                                        <div class="d-inline-flex align-items-center gap-2 p-1 px-3 rounded-pill" style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid #d4af37; backdrop-filter: blur(6px);">
                                            <i class="bi bi-qr-code text-warning"></i>
                                            <span style="font-family: 'Montserrat', sans-serif; font-size: 0.65rem; color: #fce79a; font-weight: 700;">SCAN FOR 3D AR VIEW</span>
                                        </div>
                                    </div>

                                    {{-- Bottom Gold Ribbon --}}
                                    <div class="pt-1 text-center" id="wrap-ribbon">
                                        <div class="gold-ribbon-bar mb-1"></div>
                                        <div id="prevFlyerRibbon" class="small fw-bold" style="font-family: 'Cinzel', serif; font-size: 0.75rem; letter-spacing: 1.5px; color: #fce79a; text-shadow: 0 1px 4px rgba(0,0,0,0.9);">
                                            ★ More Details to Follow ★
                                        </div>
                                    </div>
                                </div>

                                {{-- ========================================================== --}}
                                {{-- PREVIEW MODE 2: LIVE SHOW & CONCERT (FORMERLY ALL WHITE)   --}}
                                {{-- ========================================================== --}}
                                <div id="previewModeAllWhite" class="position-relative flex-column justify-content-between h-100 p-3 pt-3" style="z-index: 5; min-height: 620px; display: none;">
                                    
                                    {{-- Top Presenter Line --}}
                                    <div id="prevAwPresenter" class="text-center fw-bold px-2" style="font-family: 'Montserrat', sans-serif; font-size: 0.58rem; letter-spacing: 1.5px; color: #fce79a; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                        FAMILY ENTERTAINMENT PRESENTS
                                    </div>

                                    {{-- Dress Code Gold Seal + Hero Title Block --}}
                                    <div class="position-relative my-1 pt-2">
                                        {{-- Floating Gold Foil Seal (Top Left) --}}
                                        <div class="dress-code-seal position-absolute" style="top: -4px; left: 4px; z-index: 8; transform: rotate(-8deg);">
                                            <span style="font-size: 0.45rem; letter-spacing: 1px; color: #fbbf24; font-weight: 800;">DRESS:</span>
                                            <span id="prevAwDressCode" style="font-family: 'Great Vibes', cursive; font-size: 0.9rem; color: #ffffff; line-height: 1.05;">Glamorous</span>
                                            <span style="font-size: 0.4rem; letter-spacing: 0.5px; color: #fce79a; font-weight: 700;">CHIC &amp; STYLISH</span>
                                        </div>

                                        <div class="text-center mt-2">
                                            <div style="font-size: 1.35rem; color: #ffd700; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.9));">👑</div>
                                            <h1 id="prevAwHeroMain" class="fw-black mb-0 pearl-embossed-text" 
                                                style="font-family: 'Cinzel', serif; font-size: 2.5rem; line-height: 0.95; letter-spacing: 2px; font-weight: 900;">
                                                LIVE SHOW
                                            </h1>
                                            <div id="prevAwHeroScript" class="gold-embossed-text" 
                                                 style="font-family: 'Great Vibes', cursive; font-size: 2.4rem; line-height: 0.9; margin-top: -4px;">
                                                &amp; CONCERT
                                            </div>
                                            <div class="mt-2">
                                                <span id="prevAwCityPill" class="px-3 py-1 rounded-pill fw-bold" 
                                                      style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid #d4af37; color: #fce79a; font-family: 'Cinzel', serif; font-size: 0.75rem; letter-spacing: 2px;">
                                                    IN LONDON
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="my-auto"></div>

                                    {{-- Date, Time & Venue Glass Card --}}
                                    <div class="vip-glass-pill p-2 mx-1 mb-2 text-center shadow">
                                        <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 0.72rem; font-weight: 800; color: #ffffff;">
                                            <span><i class="bi bi-calendar-event text-warning me-1"></i><span id="prevAwDate">FRIDAY 30TH MAY 2026</span></span>
                                            <span class="text-warning">•</span>
                                            <span style="color: #fce79a;"><span id="prevAwTime">7PM - MIDNIGHT</span></span>
                                        </div>
                                        <div class="gold-ribbon-bar my-1"></div>
                                        <div class="fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 0.68rem; color: #fde68a; letter-spacing: 0.5px;">
                                            <i class="bi bi-geo-alt-fill text-warning me-1"></i><span id="prevAwVenue">THE GRAND ARENA, LONDON SW1</span>
                                        </div>
                                    </div>

                                    {{-- 3-Column VIP Perks --}}
                                    <div class="row g-1 mx-1 mb-2 text-center" style="font-family: 'Montserrat', sans-serif; font-size: 0.6rem; font-weight: 800; color: #fce79a;">
                                        <div class="col-4">
                                            <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat1">🎧 LIVE BAND</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat2">🥂 VIP TABLES</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat3">👥 RED CARPET</div>
                                        </div>
                                    </div>

                                    {{-- Ticket & Reservation Brush Banner --}}
                                    <div class="ticket-brush-banner p-2 rounded-3 text-center">
                                        <div id="prevAwTickets" class="fw-extrabold mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 0.7rem; color: #fbbf24; letter-spacing: 0.5px;">
                                            🎟️ TICKETS: RSVP VIA FAMILY PLATFORM
                                        </div>
                                        <div id="prevAwRsvp" class="fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 0.62rem; color: #ffffff; letter-spacing: 0.5px;">
                                            📱 RESERVATIONS: DM HOST OR APP
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons & Omnichannel Social Share Hub --}}
                        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" onclick="downloadFamFlyer()" style="background: linear-gradient(135deg, #ffd700, #d4af37); border: none; min-height: 44px; color: #000;">
                                <i class="bi bi-download me-1"></i> Download Poster (PNG)
                            </button>
                            <a id="btnPrimaryPosterWhatsApp" href="https://api.whatsapp.com/send?text=SAVE%20THE%20DATE%3A%20BIRTHDAY%20GALA" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center" onclick="sharePosterToSocial('whatsapp', event)" style="background: #25D366; border: none; min-height: 44px; color: #fff; text-decoration: none;">
                                <i class="bi bi-whatsapp me-1"></i> Share to WhatsApp
                            </a>
                            <button type="button" class="btn btn-outline-dark rounded-pill px-3 fw-bold" onclick="copyPosterImageToClipboard(event)" style="min-height: 44px;">
                                <i class="bi bi-clipboard-check me-1"></i> Copy Poster
                            </button>
                            <button type="button" class="btn btn-outline-dark rounded-pill px-3 fw-bold" onclick="launchArRoomView()" style="min-height: 44px;">
                                <i class="bi bi-camera-video me-1 text-primary"></i> View in AR
                            </button>
                        </div>

                        {{-- Multi-Platform Social Share Bar (WhatsApp, Instagram, Facebook, X, Telegram, Email, Native) --}}
                        <div class="mt-3 p-3 rounded-4 border" style="background: #f8fafc;">
                            <div class="small fw-bold text-uppercase text-muted mb-2" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                                <i class="bi bi-share-fill text-primary me-1"></i> Share Poster &amp; Invitation Across Social Networks
                            </div>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <a id="btnSocialShareWhatsapp" href="https://api.whatsapp.com/send?text=SAVE%20THE%20DATE%3A%20BIRTHDAY%20GALA" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('whatsapp', event)" style="background: #25D366; color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </a>
                                <a id="btnSocialShareInstagram" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('instagram', event)" style="background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-instagram"></i> Instagram
                                </a>
                                <a id="btnSocialShareFacebook" href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($_ENV['APP_URL'] ?? 'https://familyplatform.test') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('facebook', event)" style="background: #1877F2; color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-facebook"></i> Facebook
                                </a>
                                <a id="btnSocialShareTwitter" href="https://twitter.com/intent/tweet?text=SAVE%20THE%20DATE%3A%20BIRTHDAY%20GALA" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('twitter', event)" style="background: #0f172a; color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-twitter-x"></i> X / Post
                                </a>
                                <a id="btnSocialShareTelegram" href="https://t.me/share/url?url={{ rawurlencode($_ENV['APP_URL'] ?? 'https://familyplatform.test') }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('telegram', event)" style="background: #0088cc; color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-telegram"></i> Telegram
                                </a>
                                <a id="btnSocialShareEmail" href="mailto:?subject=Invitation" class="btn btn-sm rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="sharePosterToSocial('email', event)" style="background: #475569; color: #ffffff; border: none; min-height: 40px; text-decoration: none;">
                                    <i class="bi bi-envelope-fill"></i> Email
                                </a>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 text-dark" onclick="copyPosterCaptionText(event)" style="min-height: 40px;">
                                    <i class="bi bi-chat-quote text-primary"></i> Copy Caption
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 text-dark" onclick="sharePosterNativeOS(event)" style="min-height: 40px;">
                                    <i class="bi bi-phone-fill text-success"></i> More Apps
                                </button>
                            </div>
                        </div>
                        <div id="flyerActionFeedback" class="text-center mt-2 small" style="min-height: 24px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AR CAMERA ROOM PREVIEW MODAL --}}
        <div class="modal fade" id="arRoomModal" tabindex="-1" aria-labelledby="arRoomModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-2xl overflow-hidden">
                    <div class="modal-header border-0 px-4 pt-4 pb-2 d-flex justify-content-between align-items-center" style="background: #0f172a; border-bottom: 1px solid rgba(255,255,255,0.08) !important;">
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="arRoomModalLabel">
                                <i class="bi bi-badge-vr-fill text-warning me-2"></i> AR Room View: Mount on Your Wall
                            </h5>
                            <p class="text-white-50 small mb-0">Drag and scale your bespoke poster to see how it looks hanging in your room before printing.</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeArRoomModal()" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-3 p-md-4">
                        {{-- AR Viewfinder --}}
                        <div class="ar-viewfinder" id="arViewfinder">
                            {{-- Real Camera Video Stream --}}
                            <video id="arVideoFeed" playsinline autoplay muted style="width: 100%; height: 100%; object-fit: cover; display: none;"></video>

                            {{-- Simulated Gallery Wall Fallback --}}
                            <div id="arSimulatedWall" style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 120px; background: linear-gradient(0deg, #090d16 0%, transparent 100%); pointer-events: none;"></div>
                                <div class="text-center text-white-50 small" id="arFallbackNote">
                                    <i class="bi bi-camera-video-off fs-4 d-block mb-1 text-warning"></i>
                                    <span>Live room camera inactive &middot; Simulated modern living room gallery wall</span>
                                </div>
                            </div>

                            {{-- Draggable Framed Poster Proxy --}}
                            <div class="ar-poster-proxy" id="arPosterProxy" style="width: 170px; height: 230px;">
                                <img id="arPosterProxyImg" src="" alt="Framed Poster" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                <div style="position: absolute; inset: 0; box-shadow: inset 0 0 15px rgba(0,0,0,0.5); pointer-events: none;"></div>
                            </div>
                        </div>

                        {{-- AR Placement Controls --}}
                        <div class="row g-3 mt-2 align-items-center">
                            <div class="col-sm-4">
                                <label class="small text-white-50 d-block mb-1">Wall Scale: <span id="arScaleVal" class="text-warning fw-bold">100%</span></label>
                                <input type="range" id="arScaleSlider" class="form-range" min="50" max="220" value="100" oninput="updateArPosterScale(this.value)">
                            </div>
                            <div class="col-sm-4">
                                <label class="small text-white-50 d-block mb-1">Perspective Angle: <span id="arTiltVal" class="text-warning fw-bold">0°</span></label>
                                <input type="range" id="arTiltSlider" class="form-range" min="-35" max="35" value="0" oninput="updateArPosterTilt(this.value)">
                            </div>
                            <div class="col-sm-4 text-sm-end pt-3 pt-sm-0">
                                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" onclick="snapArPhoto()" style="min-height: 44px; color: #000;">
                                    <i class="bi bi-camera me-1"></i> Snap AR Photo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Quick-Jump Pill: Jump between Studio Controls and Live Poster Preview (Segun Mobile Invariant) --}}
            <div class="d-lg-none position-fixed bottom-0 end-0 p-3" style="z-index: 1040; pointer-events: none;">
                <button type="button" id="mobileFlyerJumpBtn" onclick="toggleMobileFlyerView()" class="btn btn-dark shadow-lg rounded-pill px-3 py-2 fw-bold border border-warning" style="pointer-events: auto; min-height: 44px; backdrop-filter: blur(8px); box-shadow: 0 8px 24px rgba(0,0,0,0.45) !important;">
                    <span class="text-warning"><i class="bi bi-eye-fill me-1" id="mobileFlyerJumpIcon"></i></span> <span id="mobileFlyerJumpText" class="text-white">View Live Poster</span>
                </button>
            </div>
        </div>

        {{-- 🎮 TAB 5: FAMILY GAME SHOW & RITUALS --}}
        <div class="tab-pane fade" id="tab-trivia" role="tabpanel">
            {{-- Game Mode Subtabs Navigation --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-2 bg-white rounded-pill shadow-sm border">
                <div class="nav nav-pills gap-1" id="gameModeSubTabs" role="tablist">
                    <button class="nav-link active rounded-pill fw-bold py-2 px-3 small d-inline-flex align-items-center gap-2" 
                            id="subtab-twotruths-btn" data-bs-toggle="pill" data-bs-target="#subtab-twotruths" type="button" role="tab" style="min-height: 40px;">
                        <i class="bi bi-mask text-danger"></i> 🎭 Two Truths &amp; a Lie (Weekly Challenge)
                    </button>
                    <button class="nav-link rounded-pill fw-bold py-2 px-3 small d-inline-flex align-items-center gap-2" 
                            id="subtab-trivia-btn" data-bs-toggle="pill" data-bs-target="#subtab-trivia" type="button" role="tab" style="min-height: 40px;">
                        <i class="bi bi-trophy text-warning"></i> 🏆 Live Family Game Show &amp; Trivia
                    </button>
                </div>
                <div class="px-2 d-none d-md-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3 py-1 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalCreateTwoTruths" style="min-height: 38px;">
                        <i class="bi bi-plus-circle"></i> Create Challenge
                    </button>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold small">
                        <i class="bi bi-fire me-1"></i> Diaspora Ritual
                    </span>
                </div>
            </div>

            <div class="tab-content" id="gameModeSubContent">
                {{-- SUB-TAB 1: TWO TRUTHS & A LIE (HERITAGE EDITION) --}}
                <div class="tab-pane fade show active" id="subtab-twotruths" role="tabpanel">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" id="twoTruthsArenaCard">
                                {{-- Top Status Banner --}}
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            @if(!empty($twoTruthsGame['author_avatar']))
                                                <img src="{{ $twoTruthsGame['author_avatar'] }}" alt="Storyteller" class="rounded-circle shadow-sm object-fit-cover" style="width: 48px; height: 48px;">
                                            @else
                                                <div class="rounded-circle bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                                    {{ mb_substr($twoTruthsGame['author_name'] ?? 'Family', 0, 1) }}
                                                </div>
                                            @endif
                                            <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-white p-1" title="Active Storyteller">
                                                <span class="visually-hidden">Active</span>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 mb-1 fw-bold small" id="tt-status-badge">
                                                {{ ($twoTruthsGame['status'] ?? '') === 'revealed' ? '🎉 Revealed & Archived' : '⏳ Live Weekend Challenge' }}
                                            </span>
                                            <h4 class="fw-bold mb-0 text-dark" id="tt-game-title">{{ $twoTruthsGame['title'] ?? 'Can you spot the lie?' }}</h4>
                                            <small class="text-muted">Storyteller: <strong class="text-dark" id="tt-author-name">{{ $twoTruthsGame['author_name'] ?? 'Family Member' }}</strong></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-muted" id="tt-countdown-label">Total Votes</div>
                                        <div class="fw-extrabold fs-4 text-danger" id="tt-total-votes">{{ $twoTruthsGame['total_votes'] ?? 0 }}</div>
                                    </div>
                                </div>

                                {{-- Instructions pill --}}
                                <div class="p-3 bg-light rounded-4 mb-4 border d-flex align-items-start gap-2">
                                    <i class="bi bi-info-circle-fill text-danger fs-5 flex-shrink-0 mt-1"></i>
                                    <div class="small text-muted" style="line-height: 1.45;">
                                        <strong>How to play:</strong> Two of these statements are authentic life moments, but one is an elaborate fabrication! Select the card you believe is the <strong>Lie</strong> and place your vote. When revealed, genuine memories are immortalised in our official Family Journal.
                                    </div>
                                </div>

                                {{-- Statement Cards Container --}}
                                <div class="d-flex flex-column gap-3 mb-4" id="twoTruthsCardsContainer">
                                    @php
                                        $userGuess = $twoTruthsGame['user_vote']['guessed_lie_index'] ?? null;
                                        $isRevealed = ($twoTruthsGame['status'] ?? '') === 'revealed';
                                        $actualLie = $twoTruthsGame['lie_index'] ?? null;
                                    @endphp

                                    @for($i = 1; $i <= 3; $i++)
                                        @php
                                            $stKey = "statement_{$i}";
                                            $stText = $twoTruthsGame[$stKey] ?? "Statement {$i}";
                                            $isMyPick = ($userGuess == $i);
                                            $isLie = ($isRevealed && $actualLie == $i);
                                            $isTruth = ($isRevealed && $actualLie != $i);
                                            $rvStory = $twoTruthsGame["reveal_story_{$i}"] ?? '';
                                            $votePct = 0;
                                            if (!empty($twoTruthsGame['total_votes']) && !empty($twoTruthsGame['vote_distribution'][$i])) {
                                                $votePct = round(($twoTruthsGame['vote_distribution'][$i] / $twoTruthsGame['total_votes']) * 100);
                                            }
                                        @endphp
                                        <div class="card rounded-4 border-2 p-3 p-md-4 transition-all tt-statement-card position-relative {{ $isMyPick ? 'border-primary bg-primary-subtle bg-opacity-10' : ($isLie ? 'border-danger bg-danger-subtle bg-opacity-25' : ($isTruth ? 'border-success bg-success-subtle bg-opacity-25' : 'border-light-subtle bg-white')) }}" 
                                             data-opt="{{ $i }}" 
                                             onclick="selectTwoTruthsOption({{ $i }})"
                                             style="cursor: {{ (!$twoTruthsGame['has_voted'] && !$isRevealed) ? 'pointer' : 'default' }}; min-height: 44px;">
                                            <div class="d-flex align-items-start justify-content-between gap-3">
                                                <div class="d-flex align-items-start gap-3">
                                                    <span class="badge rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0 {{ $isLie ? 'bg-danger text-white' : ($isTruth ? 'bg-success text-white' : ($isMyPick ? 'bg-primary text-white' : 'bg-light text-dark border')) }}" style="width: 36px; height: 36px; font-size: 1rem;">
                                                        {{ $i }}
                                                    </span>
                                                    <div>
                                                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.05rem; line-height: 1.4;">
                                                            {{ $stText }}
                                                        </h5>
                                                        @if($isRevealed)
                                                            <div class="mt-2 pt-2 border-top small">
                                                                @if($isLie)
                                                                    <div class="text-danger fw-bold mb-1"><i class="bi bi-x-circle-fill me-1"></i> THE LIE UNMASKED!</div>
                                                                    <div class="text-muted">{{ $rvStory ?: 'This was an elaborate fabrication crafted to fool the family!' }}</div>
                                                                @else
                                                                    <div class="text-success fw-bold mb-1"><i class="bi bi-check-circle-fill me-1"></i> VERIFIED FAMILY TRUTH &mdash; Archived in Journal</div>
                                                                    <div class="text-muted">{{ $rvStory ?: 'An authentic heritage memory now saved to our permanent records.' }}</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="text-end flex-shrink-0">
                                                    @if($isMyPick)
                                                        <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold small"><i class="bi bi-person-check-fill me-1"></i> Your Pick</span>
                                                    @elseif(!$twoTruthsGame['has_voted'] && !$isRevealed)
                                                        <div class="form-check m-0">
                                                            <input class="form-check-input tt-radio-select" type="radio" name="tt_choice" id="tt_radio_{{ $i }}" value="{{ $i }}" style="cursor: pointer; width: 22px; height: 22px;">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(($twoTruthsGame['has_voted'] || $isRevealed) && !empty($twoTruthsGame['total_votes']))
                                                <div class="mt-3 pt-2">
                                                    <div class="d-flex justify-content-between small fw-semibold text-muted mb-1">
                                                        <span>{{ $twoTruthsGame['vote_distribution'][$i] ?? 0 }} vote(s)</span>
                                                        <span>{{ $votePct }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar {{ $isLie ? 'bg-danger' : ($isTruth ? 'bg-success' : 'bg-primary') }}" role="progressbar" style="width: {{ $votePct }}%;" aria-valuenow="{{ $votePct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                </div>

                                {{-- Action Footer --}}
                                <div class="p-3 bg-light rounded-4 border" id="twoTruthsActionBox">
                                    @if(empty($twoTruthsGame['has_voted']) && !$isRevealed)
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="small text-muted">
                                                <i class="bi bi-hand-index-thumb text-danger me-1"></i> Tap an option card above to select your guess.
                                            </div>
                                            <button type="button" id="btn-cast-tt-vote" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="castTwoTruthsVote(this)" disabled style="min-height: 44px;">
                                                <i class="bi bi-send-fill"></i> Spot as Lie &amp; Cast Guess
                                            </button>
                                        </div>
                                    @elseif(!empty($twoTruthsGame['has_voted']) && !$isRevealed)
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div>
                                                <div class="fw-bold text-dark"><i class="bi bi-check-circle-fill text-success me-1"></i> Your Guess is Registered!</div>
                                                <div class="small text-muted">The lie unmasks Sunday. Share this challenge with cousins on WhatsApp:</div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="shareTwoTruthsToWhatsApp()" style="min-height: 44px; background: #25D366; border-color: #25D366;">
                                                    <i class="bi bi-whatsapp"></i> WhatsApp Challenge
                                                </button>
                                                @if(($twoTruthsGame['author_id'] ?? '') === ($userData['id'] ?? ''))
                                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1" onclick="revealTwoTruthsGame(this, '{{ $twoTruthsGame['id'] ?? '' }}')" style="min-height: 44px;">
                                                        <i class="bi bi-unlock-fill"></i> Unmask Now
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- Revealed State --}}
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                            <div>
                                                <div class="fw-bold text-dark fs-5">
                                                    @if(!empty($twoTruthsGame['user_vote']['is_correct']))
                                                        🎉 <span class="text-success">Splendid! You Spotted the Lie! (+50 pts)</span>
                                                    @else
                                                        😅 <span class="text-danger">{{ $twoTruthsGame['author_name'] ?? 'The storyteller' }} fooled you! (+10 pts)</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">The verified truths have been permanently archived into our Family Journal.</div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="/family/journal" class="btn btn-outline-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1" style="min-height: 44px;">
                                                    <i class="bi bi-book"></i> Read in Journal
                                                </a>
                                                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalCreateTwoTruths" style="min-height: 44px;">
                                                    <i class="bi bi-plus-circle"></i> Create Next Challenge
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUB-TAB 2: LIVE GAME SHOW & TRIVIA --}}
                <div class="tab-pane fade" id="subtab-trivia" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                                <h4 class="fw-bold mb-2"><i class="bi bi-trophy-fill text-danger me-2"></i> Family Game Show</h4>
                                <p class="text-muted small mb-3">Host an affectionate family game show! Test how well relatives know family milestones, childhood stories, and funny memories.</p>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Family Name</label>
                                    <input type="text" id="trivia-family-name" class="form-control rounded-3" value="The {{ $userData['lastName'] ?? 'Family' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">Game Show Theme</label>
                                    <select id="trivia-category" class="form-select rounded-3">
                                        <option value="family_folklore">📜 Family Folklore &amp; Heritage Milestones</option>
                                        <option value="childhood_memories">🧸 Hilarious Childhood Mishaps &amp; Stories</option>
                                        <option value="who_is_most_likely_to">🤔 Who Is Most Likely To...?</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-semibold small mb-0"><i class="bi bi-chat-heart text-danger me-1"></i> Family Stories &amp; Memories</label>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" onclick="insertExampleTriviaFacts()" style="font-size: 0.78rem;">
                                            <i class="bi bi-magic me-1"></i> Try an Example
                                        </button>
                                    </div>

                                    {{-- Friendly Guidance Box for Older & Less Tech-Educated Users --}}
                                    <div class="p-3 mb-2 rounded-3 bg-light border" style="font-size: 0.8rem; line-height: 1.45;">
                                        <div class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Easy Guide for Family Stories:</div>
                                        <div class="text-muted mb-2">Write 2 to 4 quick memories or funny moments from your family. Put each story on its own line:</div>
                                        <div class="row g-2">
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🎉 Milestones &amp; Origins</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Grandpa moved to London in 1968</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">😂 Funny Mishaps</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Uncle Tunde burnt the Christmas turkey</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🏆 Proud Achievements</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Maya won the school spelling bee</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="p-2 bg-white rounded-2 border border-light-subtle h-100">
                                                    <span class="fw-semibold text-dark d-block">🍲 Famous Claims &amp; Habits</span>
                                                    <span class="text-muted" style="font-size: 0.74rem;">e.g. Auntie Simi claims the best jollof recipe</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <textarea id="trivia-facts" class="form-control rounded-3" rows="4" placeholder="Type or paste your family memories here, one per line...">Grandpa moved to London in 1968
Uncle Tunde burnt the Christmas turkey in 2012
Auntie Simi claims she invented Nigerian fried rice
Maya won the school spelling bee with the word 'onomatopoeia'</textarea>

                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-check2-circle text-success me-1"></i>Each line becomes an interactive question for family to play.</small>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none" onclick="document.getElementById('trivia-facts').value='';" style="font-size: 0.72rem;">Clear text</button>
                                    </div>
                                </div>

                                <button type="button" id="btn-launch-trivia" class="btn btn-danger rounded-pill w-100 fw-bold py-2 mt-2 d-flex align-items-center justify-content-center gap-2" onclick="generateTriviaShow()" style="min-height: 44px;">
                                    <i class="bi bi-play-circle-fill"></i> Start Family Game Show
                                </button>
                                <div class="text-center mt-2">
                                    <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-broadcast text-primary me-1"></i>Automatically posted to your family feed so everyone can play</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                                {{-- Posted to Feed Confirmation Banner --}}
                                <div id="trivia-posted-alert" class="alert alert-success d-flex align-items-center justify-content-between rounded-4 p-3 mb-3 border-0 shadow-sm text-start" style="display:none;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
                                        <div>
                                            <div class="fw-bold text-dark">Posted to Family Feed!</div>
                                            <div class="text-muted small">Your Family Game Show &amp; Round 1 poll are now live for everyone to play.</div>
                                        </div>
                                    </div>
                                    <a id="trivia-post-feed-link" href="/profilePage" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-semibold text-nowrap px-3">
                                        View on Feed <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>

                                <div id="trivia-empty-state" class="py-5">
                                    <i class="bi bi-trophy display-3 text-warning opacity-75 d-block mb-3"></i>
                                    <h4 class="fw-bold">Ready for Family Game Show Night?</h4>
                                    <p class="text-muted small mx-auto" style="max-width: 420px;">
                                        Enter your family's memories and stories on the left and tap 'Start Family Game Show' to launch an interactive game show.
                                    </p>
                                </div>

                                <div id="trivia-game-arena" style="display:none;" class="text-start">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                        <div>
                                            <span class="badge bg-danger rounded-pill px-3 py-1 mb-1">Live Game Show</span>
                                            <h4 id="trivia-game-title" class="fw-bold mb-0 text-dark">Family Game Show</h4>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">Score</div>
                                            <div class="fw-extrabold fs-4 text-primary" id="trivia-score">0 pts</div>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-light rounded-4 mb-3 border">
                                        <span class="badge bg-secondary mb-2" id="trivia-q-num">Question 1 of 3</span>
                                        <h5 class="fw-bold text-dark mb-0" id="trivia-q-text">Loading question...</h5>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mb-3" id="trivia-options-box">
                                        {{-- Dynamically populated options --}}
                                    </div>

                                    <div id="trivia-feedback-box" class="p-3 rounded-3 small fw-semibold mb-3" style="display:none;"></div>

                                    <div class="d-flex justify-content-end">
                                        <button type="button" id="btn-next-trivia" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="nextTriviaQuestion()" style="display:none;">
                                            Next Question <i class="bi bi-chevron-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // 🥦 FRIDGE SCANNER
    async function compressImageForUpload(file, maxDimension = 1600, quality = 0.82) {
        if (!file || !(file instanceof Blob)) return file;
        return new Promise((resolve) => {
            const img = new Image();
            const reader = new FileReader();
            reader.onload = (e) => {
                img.onload = () => {
                    let { width, height } = img;
                    if (width > maxDimension || height > maxDimension) {
                        if (width > height) {
                            height = Math.round((height * maxDimension) / width);
                            width = maxDimension;
                        } else {
                            width = Math.round((width * maxDimension) / height);
                            height = maxDimension;
                        }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        resolve(blob || file);
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => resolve(file);
                img.src = e.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    function previewFridgePhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('fridge-preview-img').src = e.target.result;
                document.getElementById('photo-preview-wrap').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    async function scanFridge() {
        const fileInput = document.getElementById('fridge-photo-input');
        if (!fileInput.files || !fileInput.files[0]) {
            alert('Please select or snap a photo of your fridge first.');
            return;
        }

        const btn = document.getElementById('btn-scan-fridge');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Optimising &amp; Analysing Ingredients...';

        try {
            // Client-side pre-compression (shared-js-lib pattern): reduces 10-25MB camera uploads down to ~300KB
            const originalFile = fileInput.files[0];
            const optimizedBlob = await compressImageForUpload(originalFile, 1600, 0.82);

            const fd = new FormData();
            fd.append('photo', optimizedBlob, originalFile.name || 'fridge.jpg');

            const res = await fetch('/api/ai/inspect-fridge', { method: 'POST', body: fd });
            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook';

            if (data.success) {
                const badgesWrap = document.getElementById('ingredients-badges');
                badgesWrap.innerHTML = '';
                (data.detected_ingredients || []).forEach(item => {
                    const b = document.createElement('span');
                    b.className = 'badge bg-light text-dark border px-2 py-1 rounded-pill small';
                    b.textContent = item;
                    badgesWrap.appendChild(b);
                });
                document.getElementById('detected-ingredients-wrap').style.display = 'block';

                const list = document.getElementById('recipes-list');
                list.innerHTML = '';
                (data.suggested_recipes || []).forEach(r => {
                    const card = document.createElement('div');
                    card.className = 'card border rounded-4 p-3 bg-light';
                    let stepsHtml = '';
                    (r.instructions || []).forEach((st, idx) => {
                        stepsHtml += `<li class="small text-muted mb-1">${st}</li>`;
                    });
                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">${r.title}</h6>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                <i class="bi bi-clock me-1"></i>${r.cooking_time_minutes} mins
                            </span>
                        </div>
                        <ol class="ps-3 mb-0">${stepsHtml}</ol>
                    `;
                    list.appendChild(card);
                });
            } else {
                alert(data.error || 'Could not analyse fridge image.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> Detect Ingredients &amp; Cook';
            alert('Scan error: ' + err.message);
        }
    }

    // 🎤 VOICE NOTES (Dual-Engine: Live Browser SpeechRecognition + Resilient Server AI Cascade)
    let famRecorder = null;
    let famChunks = [];
    let isFamRecording = false;
    let famSpeechRecognition = null;
    let liveSpeechTranscript = '';

    async function toggleFamVoice() {
        const btn = document.getElementById('fam-voice-btn');
        const status = document.getElementById('fam-voice-status');
        const icon = document.getElementById('fam-mic-icon');

        if (!isFamRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                famRecorder = new MediaRecorder(stream);
                famChunks = [];
                liveSpeechTranscript = '';

                // 1. Initialise Live Web Speech API if supported by browser (Instant zero-latency feedback)
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (SpeechRec) {
                    try {
                        famSpeechRecognition = new SpeechRec();
                        famSpeechRecognition.continuous = true;
                        famSpeechRecognition.interimResults = true;
                        famSpeechRecognition.lang = 'en-GB';

                        famSpeechRecognition.onresult = (e) => {
                            let interim = '';
                            let final = '';
                            for (let i = 0; i < e.results.length; ++i) {
                                if (e.results[i].isFinal) {
                                    final += e.results[i][0].transcript + ' ';
                                } else {
                                    interim += e.results[i][0].transcript;
                                }
                            }
                            const current = (final + interim).trim();
                            if (current) {
                                liveSpeechTranscript = current;
                                const outEl = document.getElementById('fam-voice-output');
                                if (outEl) outEl.value = current;
                            }
                        };
                        famSpeechRecognition.onerror = (e) => {
                            console.warn('[VoiceNotes] Live speech recognition notice:', e.error);
                        };
                        famSpeechRecognition.start();
                    } catch (recErr) {
                        console.warn('[VoiceNotes] SpeechRecognition startup notice:', recErr);
                    }
                }

                famRecorder.ondataavailable = e => { if (e.data.size > 0) famChunks.push(e.data); };
                famRecorder.onstop = async () => {
                    // Stop stream tracks
                    stream.getTracks().forEach(t => t.stop());

                    const blob = new Blob(famChunks, { type: 'audio/webm' });
                    if (status) status.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1" role="status"></span> Transcribing with Resilient AI Engine...';
                    if (btn) btn.disabled = true;

                    const fd = new FormData();
                    fd.append('audio', blob, 'voice.webm');

                    try {
                        const res = await fetch('/api/ai/transcribe-voice', { method: 'POST', body: fd });
                        const data = await res.json();
                        if (btn) btn.disabled = false;

                        if (data.success && data.text && data.text.trim().length > 0) {
                            const outEl = document.getElementById('fam-voice-output');
                            if (outEl) outEl.value = data.text.trim();
                            const providerBadge = data.provider ? ` (${data.provider})` : '';
                            if (status) status.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Transcription complete!${providerBadge}</span>`;
                        } else {
                            // If live speech captured text, preserve it gracefully
                            const outEl = document.getElementById('fam-voice-output');
                            const existingText = outEl ? outEl.value.trim() : '';
                            if (existingText.length > 0) {
                                if (status) status.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Spoken words captured via device speech recognition.</span>';
                            } else {
                                if (status) status.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-exclamation-circle-fill me-1"></i> ${data.error || 'No audible speech detected. Please speak clearly into your microphone.'}</span>`;
                            }
                        }
                    } catch (err) {
                        if (btn) btn.disabled = false;
                        const outEl = document.getElementById('fam-voice-output');
                        const existingText = outEl ? outEl.value.trim() : '';
                        if (existingText.length > 0) {
                            if (status) status.innerHTML = '<span class="text-primary fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Dictation saved from browser speech stream.</span>';
                        } else {
                            if (status) status.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-wifi-off me-1"></i> Upload error: ' + (err.message || 'Network disconnected') + '</span>';
                        }
                    }
                };

                famRecorder.start();
                isFamRecording = true;
                if (btn) {
                    btn.style.transform = 'scale(1.1)';
                    btn.style.boxShadow = '0 0 25px rgba(236, 72, 153, 0.6)';
                }
                if (icon) icon.className = 'bi bi-stop-fill';
                if (status) status.innerHTML = '<span class="text-danger fw-bold"><i class="bi bi-record-circle-fill me-1"></i> Recording... Click to Stop</span>';
            } catch (err) {
                alert('Microphone access denied: ' + err.message);
            }
        } else {
            // Stop live recognition if active
            if (famSpeechRecognition) {
                try { famSpeechRecognition.stop(); } catch (_) {}
                famSpeechRecognition = null;
            }

            if (famRecorder && famRecorder.state !== 'inactive') {
                famRecorder.stop();
            }
            isFamRecording = false;
            if (btn) {
                btn.style.transform = 'scale(1)';
                btn.style.boxShadow = '';
            }
            if (icon) icon.className = 'bi bi-mic-fill';
        }
    }

    function copyFamVoice() {
        const output = document.getElementById('fam-voice-output');
        const text = output ? output.value.trim() : '';
        const btnText = document.getElementById('btn-copy-fam-text');
        const btnIcon = document.getElementById('btn-copy-fam-icon');
        const btn = document.getElementById('btn-copy-fam-voice');

        if (!text) {
            alert('No dictated notes to copy yet. Speak into the microphone first!');
            if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
            return;
        }

        navigator.clipboard.writeText(text).then(() => {
            if (btnText) btnText.textContent = 'Copied!';
            if (btnIcon) btnIcon.className = 'bi bi-check2 me-1';
            setTimeout(() => {
                if (btnText) btnText.textContent = 'Copy to Clipboard';
                if (btnIcon) btnIcon.className = 'bi bi-clipboard me-1';
                if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
            }, 2000);
        }).catch(() => {
            alert('Notes copied to clipboard!');
            if (btn && window.FamilyPlatformSpinner) window.FamilyPlatformSpinner.stop(btn);
        });
    }

    function clearFamVoice() {
        const output = document.getElementById('fam-voice-output');
        if (output) output.value = '';
        const status = document.getElementById('fam-voice-status');
        if (status) status.innerHTML = 'Click to Start Recording';
    }

    // 🎨 FAMILY FLYER STUDIO (USER-CENTRIC MODULAR AR & EVENT POSTER ENGINE)
    let currentPosterLayoutMode = 'transatlantic'; // 'transatlantic' | 'allwhite_vip'
    let currentFlyerFormat = 'portrait_3_4'; // 'portrait_3_4' | 'story_9_16' | 'square_1_1' | 'landscape_16_9'
    let currentBgScheme = 'emerald'; // 'emerald' | 'obsidian' | 'sapphire' | 'ruby' | 'purple' | 'rose' | 'teal' | 'champagne' | 'custom'
    let currentBgColor1 = '#021a12';
    let currentBgColor2 = '#041424';
    let currentBgFillStyle = 'gradient'; // 'gradient' | 'solid' | 'radial'
    let hasUserCustomBg = false;
    let currentFamFlyerImageData = null;
    let currentCelebrantPhotoData = null;
    let hasCelebrantPhoto = false;
    let isArTiltActive = false;
    let hasArQrBadge = false;
    let arVideoStream = null;
    let arPosterScale = 1.0;
    let arPosterTilt = 0;
    let arProxyPos = { x: 0, y: 0 };
    let isArDragging = false;
    let arDragStart = { x: 0, y: 0 };

    // Modular Layer State for complete user-focused customisation
    const flyerLayerState = {
        activeLayer: 'hero_title',
        layers: {
            hero_title: { visible: true, size: 35, font: "'Cinzel', serif", align: 'center', offsetY: 0, elId: 'prevFlyerHeroTitle' },
            headline: { visible: true, size: 23, font: "'Cinzel', serif", align: 'center', offsetY: 0, elId: 'prevFlyerHeadline' },
            intro: { visible: true, size: 18, font: "'Great Vibes', cursive", align: 'center', offsetY: 0, elId: 'prevFlyerIntro' },
            subtitle: { visible: true, size: 14, font: "'Montserrat', sans-serif", align: 'center', offsetY: 0, elId: 'prevFlyerSubtitle' },
            motto: { visible: true, size: 17, font: "'Great Vibes', cursive", align: 'center', offsetY: 0, elId: 'prevFlyerMotto' },
            dest1: { visible: true, elId: 'prevDest1Col' },
            dest2: { visible: true, elId: 'prevDest2Col' },
            ribbon: { visible: true, size: 12, font: "'Cinzel', serif", align: 'center', offsetY: 0, elId: 'wrap-ribbon' },
            celebrant: { visible: false, scale: 100, elId: 'prevCelebrantWrap' }
        },
        customLayers: []
    };

    function highlightActivePresetBtn(activeId) {
        const presetIds = [
            'btnPresetBirthday', 'btnPresetLiveShow',
            'btnPresetGigHeadline', 'btnPresetGigRave', 'btnPresetGigComedy', 'btnPresetGigJazz', 'btnPresetGigFestival',
            'btnPresetJubilee', 'btnPresetFiesta',
            'btnPresetArHologram', 'btnPresetArCyber', 'btnPresetArRoyal', 'btnPresetArNeon'
        ];
        presetIds.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === activeId) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
    }

    function setFlyerFormat(format) {
        currentFlyerFormat = format;
        ['btnFmtPortrait', 'btnFmtStory', 'btnFmtSquare', 'btnFmtLandscape'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.classList.remove('active');
        });

        const card = document.getElementById('famFlyerCardPreview');
        const wrap = document.getElementById('flyer3DWrap');
        if (!card) return;

        if (format === 'story_9_16') {
            document.getElementById('btnFmtStory')?.classList.add('active');
            card.style.aspectRatio = '9/16';
            card.style.maxWidth = '380px';
            card.style.minHeight = '675px';
            if (wrap) wrap.style.maxWidth = '380px';
        } else if (format === 'square_1_1') {
            document.getElementById('btnFmtSquare')?.classList.add('active');
            card.style.aspectRatio = '1/1';
            card.style.maxWidth = '460px';
            card.style.minHeight = '460px';
            if (wrap) wrap.style.maxWidth = '460px';
        } else if (format === 'landscape_16_9') {
            document.getElementById('btnFmtLandscape')?.classList.add('active');
            card.style.aspectRatio = '16/9';
            card.style.maxWidth = '580px';
            card.style.minHeight = '326px';
            if (wrap) wrap.style.maxWidth = '580px';
        } else {
            // portrait_3_4 default
            document.getElementById('btnFmtPortrait')?.classList.add('active');
            card.style.aspectRatio = '3/4';
            card.style.maxWidth = '440px';
            card.style.minHeight = '620px';
            if (wrap) wrap.style.maxWidth = '440px';
        }
        updateFamFlyerPreview();
    }

    function updatePhotoDisplay() {
        const celImg = document.getElementById('prevCelebrantPhoto');
        const celWrap = document.getElementById('prevCelebrantWrap');
        const awImg = document.getElementById('prevAwArtistPhoto');
        const awWrap = document.getElementById('prevAwArtistPhotoWrap');
        const hdr = document.getElementById('prevHeaderColumn');
        const btnRemove = document.getElementById('btnRemovePhoto');
        const scaleRow = document.getElementById('celebrantScaleRow');
        const labelTitle = document.getElementById('labelPhotoTitle');
        const scaleVal = flyerLayerState?.layers?.celebrant?.scale || 100;

        if (labelTitle) {
            labelTitle.textContent = currentPosterLayoutMode === 'allwhite_vip' ? 'Headliner / Artist Photo' : 'Celebrant Portrait (Optional)';
        }

        if (hasCelebrantPhoto && currentCelebrantPhotoData) {
            if (celImg) {
                celImg.src = currentCelebrantPhotoData;
                celImg.style.display = 'block';
                celImg.style.transform = `scale(${scaleVal / 100})`;
                celImg.style.transformOrigin = 'top left';
            }
            if (awImg) {
                awImg.src = currentCelebrantPhotoData;
                awImg.style.display = 'block';
                awImg.style.transform = `scale(${scaleVal / 100})`;
                awImg.style.transformOrigin = 'top center';
            }
            if (btnRemove) btnRemove.style.display = 'inline-block';
            if (scaleRow) scaleRow.style.setProperty('display', 'flex', 'important');

            if (currentPosterLayoutMode === 'allwhite_vip') {
                if (celWrap) celWrap.style.display = 'none';
                if (awWrap) awWrap.style.display = 'block';
                if (hdr) {
                    hdr.style.marginLeft = '0';
                    hdr.classList.remove('text-end');
                    hdr.classList.add('text-center');
                }
            } else {
                if (awWrap) awWrap.style.display = 'none';
                if (celWrap) celWrap.style.display = 'block';
                if (hdr) {
                    hdr.style.marginLeft = '35%';
                    hdr.classList.remove('text-center');
                    hdr.classList.add('text-end');
                }
            }
        } else {
            if (celImg) { celImg.src = ''; celImg.style.display = 'none'; }
            if (awImg) { awImg.src = ''; awImg.style.display = 'none'; }
            if (celWrap) celWrap.style.display = 'none';
            if (awWrap) awWrap.style.display = 'none';
            if (btnRemove) btnRemove.style.display = 'none';
            if (scaleRow) scaleRow.style.setProperty('display', 'none', 'important');
            if (hdr) {
                hdr.style.marginLeft = '0';
                hdr.classList.remove('text-end');
                hdr.classList.add('text-center');
            }
        }
    }
    window.updatePhotoDisplay = updatePhotoDisplay;

    function setPosterMode(mode) {
        currentPosterLayoutMode = mode;
        const ctrlTrans = document.getElementById('controlsTransatlantic');
        const ctrlWhite = document.getElementById('controlsAllWhite');
        const prevTrans = document.getElementById('previewModeTransatlantic');
        const prevWhite = document.getElementById('previewModeAllWhite');
        const card = document.getElementById('famFlyerCardPreview');

        if (mode === 'allwhite_vip') {
            if (ctrlTrans) ctrlTrans.style.display = 'none';
            if (ctrlWhite) ctrlWhite.style.display = 'block';
            if (prevTrans) {
                prevTrans.classList.remove('d-flex');
                prevTrans.style.display = 'none';
            }
            if (prevWhite) {
                prevWhite.style.display = 'flex';
                prevWhite.classList.add('d-flex');
            }
            if (card && !currentFamFlyerImageData) {
                applyPosterBgColorsToCard(card);
            }
        } else {
            if (ctrlTrans) ctrlTrans.style.display = 'block';
            if (ctrlWhite) ctrlWhite.style.display = 'none';
            if (prevWhite) {
                prevWhite.classList.remove('d-flex');
                prevWhite.style.display = 'none';
            }
            if (prevTrans) {
                prevTrans.style.display = 'flex';
                prevTrans.classList.add('d-flex');
            }
            if (card && !currentFamFlyerImageData) {
                applyPosterBgColorsToCard(card);
            }
        }
        updatePhotoDisplay();
        updateFamFlyerPreview();
    }

    function darkenHexColor(hex, factor = 0.45) {
        const clean = String(hex || '#021a12').replace('#', '');
        if (clean.length !== 6) return '#041424';
        const r = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(0, 2), 16) * factor)));
        const g = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(2, 4), 16) * factor)));
        const b = Math.max(0, Math.min(255, Math.round(parseInt(clean.substring(4, 6), 16) * factor)));
        return '#' + [r, g, b].map(v => v.toString(16).padStart(2, '0')).join('');
    }

    function isLightHexColor(hex) {
        const clean = String(hex || '#021a12').replace('#', '');
        if (clean.length !== 6) return false;
        const r = parseInt(clean.substring(0, 2), 16);
        const g = parseInt(clean.substring(2, 4), 16);
        const b = parseInt(clean.substring(4, 6), 16);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.62;
    }

    function syncBgColorInputs() {
        const c1 = document.getElementById('flyer-bg-color-1');
        const h1 = document.getElementById('flyer-bg-hex-1');
        const c2 = document.getElementById('flyer-bg-color-2');
        const cLayout = document.getElementById('flyer-bg-color-layout');
        const styleSel = document.getElementById('flyer-bg-style');
        const c2Wrap = document.getElementById('flyerBgColor2Wrap');

        if (c1) c1.value = currentBgColor1;
        if (h1 && document.activeElement !== h1) h1.value = currentBgColor1.toUpperCase();
        if (c2) c2.value = currentBgColor2;
        if (cLayout) cLayout.value = currentBgColor1;
        if (styleSel) styleSel.value = currentBgFillStyle;
        if (c2Wrap) c2Wrap.style.display = currentBgFillStyle === 'solid' ? 'none' : 'flex';
    }

    function applyPosterBgColorsToCard(card) {
        if (!card) card = document.getElementById('famFlyerCardPreview');
        if (!card) return;

        if (currentBgFillStyle === 'solid') {
            card.style.background = currentBgColor1;
        } else if (currentBgFillStyle === 'radial') {
            const deepEdge = darkenHexColor(currentBgColor2, 0.45);
            card.style.background = `radial-gradient(circle at 50% 28%, ${currentBgColor1} 0%, ${currentBgColor2} 60%, ${deepEdge} 100%)`;
        } else {
            const deepEnd = darkenHexColor(currentBgColor2, 0.55);
            card.style.background = `linear-gradient(160deg, ${currentBgColor1} 0%, ${currentBgColor2} 55%, ${deepEnd} 100%)`;
        }

        // Adapt outer text legibility automatically if user chooses a light background (e.g. white, cream, pastel)
        const isLight = isLightHexColor(currentBgColor1) && (currentBgFillStyle === 'solid' || isLightHexColor(currentBgColor2));
        const bodyTextColor = isLight ? '#0f172a' : '#ffffff';
        const goldAccentColor = isLight ? '#785208' : '#fce79a';
        const textShadowStyle = isLight ? '0 1px 2px rgba(255,255,255,0.85)' : '0 2px 6px rgba(0,0,0,0.9)';

        ['prevFlyerIntro', 'prevFlyerSubtitle'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.color = bodyTextColor;
                el.style.textShadow = textShadowStyle;
            }
        });
        const awHeroEl = document.getElementById('prevAwHeroMain');
        if (awHeroEl) {
            if (isLight) {
                awHeroEl.style.webkitTextFillColor = '#0f172a';
                awHeroEl.style.color = '#0f172a';
            } else {
                awHeroEl.style.webkitTextFillColor = 'transparent';
                awHeroEl.style.color = '';
            }
        }
        ['prevFlyerHeadline', 'prevFlyerMotto', 'prevAwPresenter'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.style.color = goldAccentColor;
                el.style.textShadow = textShadowStyle;
            }
        });

        syncBgColorInputs();
    }

    function applyBgGradient(card, scheme) {
        currentBgScheme = scheme;
        if (!card) card = document.getElementById('famFlyerCardPreview');

        const schemes = {
            emerald:   { c1: '#021a12', c2: '#041424', style: 'gradient', border: '#d4af37' },
            obsidian:  { c1: '#2a1f0a', c2: '#120e06', style: 'radial',   border: '#d4af37' },
            sapphire:  { c1: '#051937', c2: '#00254f', style: 'gradient', border: '#60a5fa' },
            ruby:      { c1: '#3d0714', c2: '#1c0309', style: 'gradient', border: '#f59e0b' },
            purple:    { c1: '#2e0854', c2: '#120324', style: 'gradient', border: '#d4af37' },
            indigo:    { c1: '#1e1b4b', c2: '#090d16', style: 'radial',   border: '#38bdf8' },
            sunset:    { c1: '#431407', c2: '#1e1b4b', style: 'gradient', border: '#fb923c' },
            rose:      { c1: '#4a1525', c2: '#1f070e', style: 'gradient', border: '#f9a8d4' },
            teal:      { c1: '#042f2e', c2: '#021519', style: 'gradient', border: '#2dd4bf' },
            champagne: { c1: '#fdfbf7', c2: '#e6dec8', style: 'gradient', border: '#b48a28' }
        };

        if (schemes[scheme]) {
            currentBgColor1 = schemes[scheme].c1;
            currentBgColor2 = schemes[scheme].c2;
            currentBgFillStyle = schemes[scheme].style;
            if (card) card.style.borderColor = schemes[scheme].border;
        }
        applyPosterBgColorsToCard(card);
    }

    function setFlyerBgScheme(scheme) {
        currentFamFlyerImageData = null;
        hasUserCustomBg = true;
        applyBgGradient(null, scheme);
    }

    function setCustomFlyerBgColor(color1, color2) {
        currentFamFlyerImageData = null;
        hasUserCustomBg = true;
        currentBgScheme = 'custom';
        if (color1) {
            currentBgColor1 = color1;
            if (!color2) {
                currentBgColor2 = isLightHexColor(color1) ? darkenHexColor(color1, 0.88) : darkenHexColor(color1, 0.45);
            }
        }
        if (color2) {
            currentBgColor2 = color2;
        }
        applyPosterBgColorsToCard(null);
    }
    window.setCustomFlyerBgColor = setCustomFlyerBgColor;

    function onHexBgInput(val, idx) {
        const clean = String(val || '').trim();
        if (/^#[0-9A-Fa-f]{6}$/.test(clean)) {
            if (idx === 1) {
                setCustomFlyerBgColor(clean, null);
            } else {
                setCustomFlyerBgColor(null, clean);
            }
        }
    }
    window.onHexBgInput = onHexBgInput;

    function setFlyerBgFillStyle(style) {
        currentFamFlyerImageData = null;
        hasUserCustomBg = true;
        currentBgFillStyle = style || 'gradient';
        applyPosterBgColorsToCard(null);
    }
    window.setFlyerBgFillStyle = setFlyerBgFillStyle;

    // 🎂 PRESET 1: BIRTHDAY (RENAMED FROM MAJOR@50)
    function applyBirthdayPreset() {
        highlightActivePresetBtn('btnPresetBirthday');
        currentFamFlyerImageData = null;
        clearCelebrantPhoto();
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
        setVal('flyer-headline', 'SAVE THE DATE');
        setVal('flyer-intro', 'For the Grand Birthday Celebration of');
        setVal('flyer-hero-title', 'BIRTHDAY GALA');
        setVal('flyer-subtitle', 'IN NIGERIA & UK');
        setVal('flyer-motto', 'Celebrating Life • Honouring Legacy • Family First');
        setVal('flyer-dest1-flag', '🇳🇬');
        setVal('flyer-dest1-title', 'LAGOS Reception');
        setVal('flyer-dest1-date', 'DATE: AUGUST, 2027');
        setVal('flyer-dest1-venue', 'VENUE: EKO HOTEL & SUITES');
        setVal('flyer-dest2-flag', '🇬🇧');
        setVal('flyer-dest2-title', 'LONDON Banquet');
        setVal('flyer-dest2-date', 'DATE: SEPTEMBER, 2027');
        setVal('flyer-dest2-venue', 'VENUE: THE DORCHESTER, MAYFAIR');
        setVal('flyer-bottom-ribbon', '★ Formal Attire • Gold & Champagne ★');
        applyBgGradient(null, 'emerald');
        setPosterMode('transatlantic');
        updatePhotoDisplay();
        updateFamFlyerPreview();
    }
    window.applyMajorPreset = applyBirthdayPreset; // Backward compatibility

    // 🎭 PRESET 2: LIVE SHOW & CONCERT (RENAMED FROM ALL WHITE VIP GALA)
    function applyLiveShowPreset() {
        highlightActivePresetBtn('btnPresetLiveShow');
        currentFamFlyerImageData = null;
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
        setVal('aw-presenter', 'FAMILY ENTERTAINMENT & STAGE PRODUCTIONS PRESENTS');
        setVal('aw-hero-main', 'LIVE SHOW');
        setVal('aw-hero-script', '& CONCERT');
        setVal('aw-city-pill', 'IN LONDON');
        setVal('aw-dress-code', 'Dress Code • GLAMOROUS & CHIC');
        setVal('aw-date', 'FRIDAY 30TH MAY 2026');
        setVal('aw-time', '7PM - MIDNIGHT');
        setVal('aw-venue', 'THE GRAND ARENA, LONDON SW1');
        setVal('aw-feat1', '🎧 LIVE BAND');
        setVal('aw-feat2', '🥂 VIP TABLES');
        setVal('aw-feat3', '👥 RED CARPET');
        setVal('aw-tickets', 'TICKETS: RSVP VIA FAMILY PLATFORM');
        setVal('aw-rsvp', 'RESERVATIONS: DM HOST OR IN-APP');
        applyBgGradient(null, 'obsidian');
        setPosterMode('allwhite_vip');
    }
    window.applyAllWhitePreset = applyLiveShowPreset; // Backward compatibility

    // 🎸 EXCITING GIG & SHOW TEMPLATES (HEADLINE GIG, DJ RAVE, COMEDY, AFRO-JAZZ, FESTIVAL)
    function applyGigPreset(gigType) {
        currentFamFlyerImageData = null;
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };

        if (gigType === 'headline_gig') {
            highlightActivePresetBtn('btnPresetGigHeadline');
            setVal('aw-presenter', 'LIVE STAGE & WORLD TOUR PRESENTS');
            setVal('aw-hero-main', 'HEADLINE GIG');
            setVal('aw-hero-script', 'LIVE ON STAGE');
            setVal('aw-city-pill', 'WORLD TOUR • LONDON');
            setVal('aw-dress-code', 'Dress Code • ROCK CHIC & VIP PASS');
            setVal('aw-date', 'SATURDAY 18TH JULY 2026');
            setVal('aw-time', '8PM TILL LATE');
            setVal('aw-venue', 'O2 FORUM KENTISH TOWN, LONDON');
            setVal('aw-feat1', '🎸 LIVE HEADLINER');
            setVal('aw-feat2', '🔥 FRONT ROW PIT');
            setVal('aw-feat3', '🥂 BACKSTAGE VIP');
            setVal('aw-tickets', 'TICKETS: EARLY BIRD & VIP PASSES');
            setVal('aw-rsvp', 'GUESTLIST: SCAN QR OR RSVP LINK');
            applyBgGradient(null, 'indigo');
        } else if (gigType === 'dj_rave') {
            highlightActivePresetBtn('btnPresetGigRave');
            setVal('aw-presenter', 'EXCLUSIVE NIGHTLIFE & SOUND SYSTEM');
            setVal('aw-hero-main', 'CLUB RAVE');
            setVal('aw-hero-script', '& DJ TAKEOVER');
            setVal('aw-city-pill', 'ALL NIGHT • LONDON');
            setVal('aw-dress-code', 'Dress Code • STREET LUXE & DRIP');
            setVal('aw-date', 'FRIDAY 28TH AUGUST 2026');
            setVal('aw-time', '10PM - 4AM');
            setVal('aw-venue', 'MINISTRY OF SOUND, LONDON SE1');
            setVal('aw-feat1', '🎧 GUEST DJ SETS');
            setVal('aw-feat2', '🍾 BOTTLE SERVICE');
            setVal('aw-feat3', '⚡ LASER & BASS STAGE');
            setVal('aw-tickets', 'ENTRY: GUESTLIST & TABLE BOOKINGS');
            setVal('aw-rsvp', 'VIP BOOTH: SCAN QR OR DM HOST');
            applyBgGradient(null, 'purple');
        } else if (gigType === 'comedy_night') {
            highlightActivePresetBtn('btnPresetGigComedy');
            setVal('aw-presenter', 'SPOTLIGHT COMEDY & ENCORE CLUB');
            setVal('aw-hero-main', 'COMEDY SPECIAL');
            setVal('aw-hero-script', 'LIVE & UNCUT');
            setVal('aw-city-pill', 'ENCORE TOUR • LONDON');
            setVal('aw-dress-code', 'Dress Code • SMART CASUAL & GLAM');
            setVal('aw-date', 'SUNDAY 12TH SEPTEMBER 2026');
            setVal('aw-time', 'DOORS 6:30PM • SHOW 8PM');
            setVal('aw-venue', 'THE COMEDY STORE, SOHO, LONDON');
            setVal('aw-feat1', '🎤 TOP COMEDIANS');
            setVal('aw-feat2', '🍸 COCKTAIL LOUNGE');
            setVal('aw-feat3', '📸 MEET & GREET');
            setVal('aw-tickets', 'SEATING: CABARET & VIP TABLES');
            setVal('aw-rsvp', 'BOX OFFICE: SCAN QR OR TAP LINK');
            applyBgGradient(null, 'ruby');
        } else if (gigType === 'jazz_lounge') {
            highlightActivePresetBtn('btnPresetGigJazz');
            setVal('aw-presenter', 'ACOUSTIC SOUL & ROOFTOP SESSIONS');
            setVal('aw-hero-main', 'AFRO-JAZZ NIGHT');
            setVal('aw-hero-script', 'UNPLUGGED SESSIONS');
            setVal('aw-city-pill', 'INTIMATE SHOW • MAYFAIR');
            setVal('aw-dress-code', 'Dress Code • SILK, LINEN & ELEGANCE');
            setVal('aw-date', 'THURSDAY 15TH OCTOBER 2026');
            setVal('aw-time', '7:30PM - MIDNIGHT');
            setVal('aw-venue', 'ROOFTOP JAZZ LOUNGE, MAYFAIR');
            setVal('aw-feat1', '🎷 LIVE HORNS & BAND');
            setVal('aw-feat2', '🍷 FINE WINE & TAPAS');
            setVal('aw-feat3', '✨ CANDLELIGHT STAGE');
            setVal('aw-tickets', 'ADMISSION: LIMITED LOUNGE SEATING');
            setVal('aw-rsvp', 'RESERVATIONS: SCAN QR TO BOOK');
            applyBgGradient(null, 'teal');
        } else if (gigType === 'festival_show') {
            highlightActivePresetBtn('btnPresetGigFestival');
            setVal('aw-presenter', 'MAIN STAGE FESTIVAL & DAY PARTY');
            setVal('aw-hero-main', 'SUMMER FEST');
            setVal('aw-hero-script', 'MAIN STAGE LIVE');
            setVal('aw-city-pill', 'OPEN AIR • LONDON');
            setVal('aw-dress-code', 'Dress Code • FESTIVAL VIBES & CARNIVAL');
            setVal('aw-date', 'BANK HOLIDAY SUNDAY 30TH AUG');
            setVal('aw-time', '2PM - 11PM');
            setVal('aw-venue', 'VICTORIA PARK MAIN ARENA, LONDON');
            setVal('aw-feat1', '🎪 3 LIVE STAGES');
            setVal('aw-feat2', '🍹 VIP CABANAS');
            setVal('aw-feat3', '🎆 FIREWORKS FINALE');
            setVal('aw-tickets', 'WRISTBANDS: GENERAL & GOLDEN CIRCLE');
            setVal('aw-rsvp', 'PASSES: SCAN QR OR TAP LINK');
            applyBgGradient(null, 'sunset');
        }
        setPosterMode('allwhite_vip');
    }
    window.applyGigPreset = applyGigPreset;

    // 👑 PRESET 3: GOLDEN JUBILEE (FIXED TYPO FROM GOLDERN JUBILEE @ 50)
    function applyJubileePreset() {
        highlightActivePresetBtn('btnPresetJubilee');
        currentFamFlyerImageData = null;
        clearCelebrantPhoto();
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
        setVal('flyer-headline', 'ROYAL INVITATION');
        setVal('flyer-intro', 'Honouring 50 Glorious Years of');
        setVal('flyer-hero-title', 'GOLDEN JUBILEE');
        setVal('flyer-subtitle', 'LONDON & LAGOS');
        setVal('flyer-motto', 'A Legacy of Grace, Wisdom & Family Unity');
        setVal('flyer-dest1-flag', '🇬🇧');
        setVal('flyer-dest1-title', 'LONDON Thanksgiving');
        setVal('flyer-dest1-date', 'DATE: OCTOBER, 2026');
        setVal('flyer-dest1-venue', 'VENUE: THE DORCHESTER, MAYFAIR');
        setVal('flyer-dest2-flag', '🇳🇬');
        setVal('flyer-dest2-title', 'LAGOS Grand Reception');
        setVal('flyer-dest2-date', 'DATE: DECEMBER, 2026');
        setVal('flyer-dest2-venue', 'VENUE: EKO HOTEL & SUITES');
        setVal('flyer-bottom-ribbon', '★ Formal Attire • Gold & Champagne ★');
        applyBgGradient(null, 'obsidian');
        setPosterMode('transatlantic');
        updatePhotoDisplay();
        updateFamFlyerPreview();
    }

    // 🪅 PRESET 4: FIESTA (RENAMED FROM AFRICAN HERITAGE FIESTA)
    function applyFiestaPreset() {
        highlightActivePresetBtn('btnPresetFiesta');
        currentFamFlyerImageData = null;
        clearCelebrantPhoto();
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
        setVal('flyer-headline', 'ANNUAL HOMECOMING');
        setVal('flyer-intro', 'You Are Cordially Invited to the');
        setVal('flyer-hero-title', 'FIESTA GALA');
        setVal('flyer-subtitle', 'HERITAGE, MUSIC & CULTURE');
        setVal('flyer-motto', 'Celebrating Our Roots • Uplifting Our Dynasty');
        setVal('flyer-dest1-flag', '🥁');
        setVal('flyer-dest1-title', 'CULTURAL CARNIVAL');
        setVal('flyer-dest1-date', 'DATE: 24 AUGUST, 2026');
        setVal('flyer-dest1-venue', 'VENUE: GRAND PAVILION');
        setVal('flyer-dest2-flag', '🥂');
        setVal('flyer-dest2-title', 'EVENING BANQUET');
        setVal('flyer-dest2-date', 'TIME: 6:00 PM TILL LATE');
        setVal('flyer-dest2-venue', 'DRESS: ROYAL TRADITIONAL');
        setVal('flyer-bottom-ribbon', '★ RSVP via Family Platform ★');
        applyBgGradient(null, 'emerald');
        setPosterMode('transatlantic');
        updatePhotoDisplay();
        updateFamFlyerPreview();
    }
    window.applyHeritagePreset = applyFiestaPreset; // Backward compatibility

    // 🔮 AUGMENTED REALITY (AR) PRESETS
    function applyArPreset(theme) {
        if (theme === 'hologram') {
            highlightActivePresetBtn('btnPresetArHologram');
            applyBgGradient(null, 'obsidian');
            document.getElementById('flyer-hero-title').value = 'AR HOLOGRAM GOLD';
            document.getElementById('flyer-subtitle').value = '3D VIRTUAL SHOWCASE';
            document.getElementById('flyer-motto').value = 'Interactive WebXR Experience • Floating Gold Sheen';
            toggle3DTilt(true);
            toggleArQrBadge(true);
        } else if (theme === 'cyber_emerald') {
            highlightActivePresetBtn('btnPresetArCyber');
            applyBgGradient(null, 'emerald');
            document.getElementById('flyer-hero-title').value = 'CYBER EMERALD';
            document.getElementById('flyer-subtitle').value = 'DIGITAL CREST & HERITAGE';
            document.getElementById('flyer-motto').value = 'Immersive Spatial Identity • Future of Family Platforms';
            toggle3DTilt(true);
            toggleArQrBadge(true);
        } else if (theme === 'royal_plaque') {
            highlightActivePresetBtn('btnPresetArRoyal');
            applyBgGradient(null, 'ruby');
            document.getElementById('flyer-hero-title').value = 'ROYAL PLAQUE';
            document.getElementById('flyer-subtitle').value = 'HIGH HERALDIC ARCHIVE';
            document.getElementById('flyer-motto').value = 'Solid Brass Embossment • Preserved for Generations';
            toggle3DTilt(true);
            toggleArQrBadge(false);
        } else if (theme === 'neon_glass') {
            highlightActivePresetBtn('btnPresetArNeon');
            applyBgGradient(null, 'sapphire');
            document.getElementById('flyer-hero-title').value = 'FLOATING NEON';
            document.getElementById('flyer-subtitle').value = 'MODERN SPATIAL EVENT';
            document.getElementById('flyer-motto').value = 'Frosted Iridescent Layers • Ambient Glow Lighting';
            toggle3DTilt(true);
            toggleArQrBadge(true);
        }
        setPosterMode('transatlantic');
        updateFamFlyerPreview();
    }

    // 👤 CELEBRANT PHOTO MANAGEMENT (USER UPLOADS ONLY - ZERO HARD-CODED POSTER COLLISION)
    function handleCelebrantPhoto(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            currentCelebrantPhotoData = e.target.result;
            hasCelebrantPhoto = true;
            if (flyerLayerState && flyerLayerState.layers && flyerLayerState.layers.celebrant) {
                flyerLayerState.layers.celebrant.visible = true;
            }
            updatePhotoDisplay();
            updateFamFlyerPreview();
        };
        reader.readAsDataURL(file);
    }

    function clearCelebrantPhoto(force = false) {
        if (!force && hasCelebrantPhoto && currentCelebrantPhotoData) {
            updatePhotoDisplay();
            return;
        }
        hasCelebrantPhoto = false;
        currentCelebrantPhotoData = null;
        if (flyerLayerState && flyerLayerState.layers && flyerLayerState.layers.celebrant) {
            flyerLayerState.layers.celebrant.visible = false;
        }
        const fileInput = document.getElementById('fam-celebrant-file');
        if (fileInput) fileInput.value = '';
        updatePhotoDisplay();
        updateFamFlyerPreview();
    }

    function toggleCelebrantVisibility() {
        if (hasCelebrantPhoto) {
            clearCelebrantPhoto(true);
        } else {
            document.getElementById('fam-celebrant-file')?.click();
        }
    }

    function updateCelebrantScale(val) {
        const wrap = document.getElementById('prevCelebrantWrap');
        if (wrap) {
            wrap.style.width = `${Math.min(90, Math.max(30, val * 0.55))}%`;
        }
        if (flyerLayerState && flyerLayerState.layers && flyerLayerState.layers.celebrant) {
            flyerLayerState.layers.celebrant.scale = parseInt(val, 10);
        }
        updatePhotoDisplay();
    }

    function filterTemplateCategory(cat) {
        document.querySelectorAll('.template-cat-filter').forEach(btn => {
            btn.classList.remove('active', 'btn-dark');
            btn.classList.add('btn-light', 'border');
        });
        const activeBtn = document.getElementById('filterCat' + cat.charAt(0).toUpperCase() + cat.slice(1));
        if (activeBtn) {
            activeBtn.classList.remove('btn-light', 'border');
            activeBtn.classList.add('active', 'btn-dark');
        }
        const items = document.querySelectorAll('#studioTemplateGrid .tpl-item');
        items.forEach(item => {
            if (cat === 'all' || item.classList.contains('tpl-cat-' + cat)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
    window.filterTemplateCategory = filterTemplateCategory;

    // 🎨 LAYER CUSTOMISATION & MOVING THINGS AROUND
    function onSelectLayerToCustomise(layerKey) {
        flyerLayerState.activeLayer = layerKey;
        const layer = flyerLayerState.layers[layerKey];
        if (!layer) return;

        const sizeSlider = document.getElementById('layer-font-size');
        const sizeBadge = document.getElementById('layer-size-badge');
        const fontSelect = document.getElementById('layer-font-family');
        const btnVis = document.getElementById('btnLayerVisibility');

        if (sizeSlider && layer.size) {
            sizeSlider.value = layer.size;
            if (sizeBadge) sizeBadge.textContent = `${layer.size}px`;
        }
        if (fontSelect && layer.font) {
            fontSelect.value = layer.font;
        }
        if (btnVis) {
            btnVis.innerHTML = layer.visible !== false ? '<i class="bi bi-eye-slash me-1"></i> Hide Layer' : '<i class="bi bi-eye me-1"></i> Show Layer';
        }
    }

    function updateSelectedLayerFontFamily(font) {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.font = font;

        const el = document.getElementById(layer.elId);
        if (el) el.style.fontFamily = font;
    }

    function updateSelectedLayerFontSize(size) {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.size = parseInt(size, 10);

        const badge = document.getElementById('layer-size-badge');
        if (badge) badge.textContent = `${size}px`;

        const el = document.getElementById(layer.elId);
        if (el) el.style.fontSize = `${size * 0.065}rem`;
    }

    function moveSelectedLayerY(delta) {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.offsetY = (layer.offsetY || 0) + delta;

        const el = document.getElementById(layer.elId);
        if (el) {
            el.style.transform = `translateY(${layer.offsetY}px)`;
        }
    }

    function resetSelectedLayerOffset() {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.offsetY = 0;
        const el = document.getElementById(layer.elId);
        if (el) el.style.transform = 'translateY(0px)';
    }

    function setSelectedLayerAlign(align) {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.align = align;
        const el = document.getElementById(layer.elId);
        if (el) el.style.textAlign = align;
    }

    function toggleSelectedLayerVisibility() {
        const key = flyerLayerState.activeLayer;
        toggleLayerVisibility(key);
    }

    function toggleLayerVisibility(layerKey) {
        const layer = flyerLayerState.layers[layerKey];
        if (!layer) return;
        layer.visible = layer.visible === false ? true : false;

        const el = document.getElementById(layer.elId || `wrap-${layerKey}`);
        if (el) {
            el.style.display = layer.visible ? '' : 'none';
        }

        // Toggle button states
        const btnMap = {
            plaque1: 'btnTogglePlaque1',
            plaque2: 'btnTogglePlaque2',
            ribbon: 'btnToggleRibbon'
        };
        const toggleBtn = document.getElementById(btnMap[layerKey]);
        if (toggleBtn) {
            toggleBtn.textContent = layer.visible ? 'Hide' : 'Show';
            toggleBtn.className = layer.visible ? 'btn btn-sm btn-outline-danger py-0 px-2 small' : 'btn btn-sm btn-outline-success py-0 px-2 small';
        }

        const btnVis = document.getElementById('btnLayerVisibility');
        if (btnVis && flyerLayerState.activeLayer === layerKey) {
            btnVis.innerHTML = layer.visible ? '<i class="bi bi-eye-slash me-1"></i> Hide Layer' : '<i class="bi bi-eye me-1"></i> Show Layer';
        }
    }

    function deleteSelectedLayer() {
        const key = flyerLayerState.activeLayer;
        const layer = flyerLayerState.layers[key];
        if (!layer) return;
        layer.visible = false;
        const el = document.getElementById(layer.elId);
        if (el) el.style.display = 'none';
    }

    function promptAddCustomTextLayer() {
        const text = prompt('Enter custom text to display on the flyer (e.g. "Special Guest of Honour", "Black Tie Strictly", "RSVP Deadline"):', '★ Honoured Guests & Family ★');
        if (!text || !text.trim()) return;

        const container = document.getElementById('prevCustomLayersContainer');
        if (!container) return;

        const id = 'customLayer_' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'text-center my-1 py-1 px-2 rounded-2 position-relative';
        div.style.background = 'rgba(15, 23, 42, 0.65)';
        div.style.border = '1px solid rgba(212, 175, 55, 0.4)';
        div.style.fontFamily = "'Cinzel', serif";
        div.style.fontSize = '0.75rem';
        div.style.color = '#fce79a';
        div.style.letterSpacing = '1px';
        div.style.cursor = 'pointer';
        div.textContent = text.trim();

        // Add delete button on hover
        const delBtn = document.createElement('span');
        delBtn.innerHTML = ' &times;';
        delBtn.style.color = '#ef4444';
        delBtn.style.fontWeight = 'bold';
        delBtn.style.cursor = 'pointer';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            div.remove();
            flyerLayerState.customLayers = flyerLayerState.customLayers.filter(l => l.id !== id);
        };
        div.appendChild(delBtn);

        container.appendChild(div);
        flyerLayerState.customLayers.push({ id: id, text: text.trim() });
    }

    // 📦 CONTAINER SIZE & RADIUS
    function updateContainerWidth(val) {
        const badge = document.getElementById('container-width-badge');
        const card = document.getElementById('famFlyerCardPreview');
        const wrap = document.getElementById('flyer3DWrap');
        if (badge) badge.textContent = `${val}px`;
        if (card) card.style.maxWidth = `${val}px`;
        if (wrap) wrap.style.maxWidth = `${val}px`;
    }

    function updateContainerRadius(val) {
        const badge = document.getElementById('container-radius-badge');
        const card = document.getElementById('famFlyerCardPreview');
        if (badge) badge.textContent = `${val}px`;
        if (card) card.style.borderRadius = `${val}px`;
    }

    // 🌀 3D HOLOGRAPHIC GYROSCOPE & MOUSE TILT
    function toggle3DTilt(enable) {
        isArTiltActive = enable;
        const card = document.getElementById('famFlyerCardPreview');
        const switchEl = document.getElementById('toggleFlyerTiltSwitch');
        if (switchEl) switchEl.checked = enable;

        if (card) {
            if (enable) {
                card.classList.add('tilt-active');
                attachTiltListeners();
            } else {
                card.classList.remove('tilt-active');
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
                detachTiltListeners();
            }
        }
    }

    let tiltMouseHandler = null;
    let tiltDeviceHandler = null;

    function attachTiltListeners() {
        const card = document.getElementById('famFlyerCardPreview');
        const sheen = document.getElementById('flyerSheen');
        if (!card) return;

        tiltMouseHandler = (e) => {
            if (!isArTiltActive) return;
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -16;
            const rotateY = ((x - centerX) / centerX) * 16;
            card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale3d(1.02, 1.02, 1.02)`;

            if (sheen) {
                const px = (x / rect.width) * 100;
                const py = (y / rect.height) * 100;
                sheen.style.background = `radial-gradient(circle at ${px}% ${py}%, rgba(255, 215, 0, 0.28) 0%, rgba(255,255,255,0.18) 35%, rgba(0, 255, 234, 0.08) 55%, transparent 75%)`;
                sheen.style.opacity = '1';
            }
        };

        const wrap = document.getElementById('flyer3DWrap') || card;
        wrap.addEventListener('mousemove', tiltMouseHandler);
        wrap.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
            if (sheen) sheen.style.opacity = '0';
        });

        // Mobile DeviceOrientation (Gyroscope)
        if (window.DeviceOrientationEvent) {
            tiltDeviceHandler = (e) => {
                if (!isArTiltActive) return;
                const gamma = Math.max(-25, Math.min(25, e.gamma || 0)); // left to right [-90,90]
                const beta = Math.max(-25, Math.min(25, (e.beta || 0) - 45)); // front back [-180,180]
                card.style.transform = `perspective(1000px) rotateX(${(-beta * 0.4).toFixed(2)}deg) rotateY(${(gamma * 0.4).toFixed(2)}deg)`;
            };
            window.addEventListener('deviceorientation', tiltDeviceHandler);
        }
    }

    function detachTiltListeners() {
        const wrap = document.getElementById('flyer3DWrap');
        if (wrap && tiltMouseHandler) wrap.removeEventListener('mousemove', tiltMouseHandler);
        if (tiltDeviceHandler) window.removeEventListener('deviceorientation', tiltDeviceHandler);
    }

    // 📱 AR QR CODE TOGGLE
    function toggleArQrBadge(enable) {
        hasArQrBadge = enable;
        const wrap = document.getElementById('prevArQrBadgeWrap');
        const switchEl = document.getElementById('toggleArQrBadgeSwitch');
        if (switchEl) switchEl.checked = enable;
        if (wrap) wrap.style.display = enable ? 'block' : 'none';
    }

    // 🎥 AR CAMERA ROOM VIEW MODAL (VIEW IN YOUR SPACE)
    async function launchArRoomView() {
        const modalEl = document.getElementById('arRoomModal');
        if (!modalEl) return;

        // Render current poster canvas to proxy image
        try {
            const canvas = await renderFullPosterCanvas();
            const dataUrl = canvas.toDataURL('image/png', 0.85);
            const proxyImg = document.getElementById('arPosterProxyImg');
            if (proxyImg) proxyImg.src = dataUrl;
        } catch (_) {}

        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();

        // Setup pointer drag on the proxy poster
        setupArPosterDrag();

        // Request camera feed
        const video = document.getElementById('arVideoFeed');
        const simWall = document.getElementById('arSimulatedWall');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            try {
                arVideoStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                if (video) {
                    video.srcObject = arVideoStream;
                    video.style.display = 'block';
                    if (simWall) simWall.style.display = 'none';
                }
            } catch (err) {
                // Graceful fallback to simulated room gallery wall
                if (video) video.style.display = 'none';
                if (simWall) simWall.style.display = 'flex';
            }
        } else {
            if (video) video.style.display = 'none';
            if (simWall) simWall.style.display = 'flex';
        }
    }

    function closeArRoomModal() {
        if (arVideoStream) {
            arVideoStream.getTracks().forEach(track => track.stop());
            arVideoStream = null;
        }
        const modalEl = document.getElementById('arRoomModal');
        if (modalEl) {
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();
        }
    }

    function setupArPosterDrag() {
        const proxy = document.getElementById('arPosterProxy');
        const container = document.getElementById('arViewfinder');
        if (!proxy || !container) return;

        proxy.onpointerdown = (e) => {
            isArDragging = true;
            proxy.setPointerCapture(e.pointerId);
            arDragStart.x = e.clientX - arProxyPos.x;
            arDragStart.y = e.clientY - arProxyPos.y;
        };

        proxy.onpointermove = (e) => {
            if (!isArDragging) return;
            arProxyPos.x = e.clientX - arDragStart.x;
            arProxyPos.y = e.clientY - arDragStart.y;
            applyArProxyTransform();
        };

        proxy.onpointerup = (e) => {
            isArDragging = false;
            try { proxy.releasePointerCapture(e.pointerId); } catch (_) {}
        };
    }

    function applyArProxyTransform() {
        const proxy = document.getElementById('arPosterProxy');
        if (!proxy) return;
        proxy.style.transform = `translate(calc(-50% + ${arProxyPos.x}px), calc(-50% + ${arProxyPos.y}px)) scale(${arPosterScale}) perspective(600px) rotateY(${arPosterTilt}deg)`;
    }

    function updateArPosterScale(val) {
        arPosterScale = val / 100;
        const badge = document.getElementById('arScaleVal');
        if (badge) badge.textContent = `${val}%`;
        applyArProxyTransform();
    }

    function updateArPosterTilt(val) {
        arPosterTilt = parseInt(val, 10);
        const badge = document.getElementById('arTiltVal');
        if (badge) badge.textContent = `${val}°`;
        applyArProxyTransform();
    }

    async function snapArPhoto() {
        const video = document.getElementById('arVideoFeed');
        const proxy = document.getElementById('arPosterProxy');
        const container = document.getElementById('arViewfinder');
        if (!container || !proxy) return;

        const outCanvas = document.createElement('canvas');
        outCanvas.width = 1280;
        outCanvas.height = 720;
        const ctx = outCanvas.getContext('2d');
        if (!ctx) return;

        // Draw camera video or simulated wall
        if (video && video.style.display !== 'none' && video.readyState >= 2) {
            ctx.drawImage(video, 0, 0, 1280, 720);
        } else {
            const bgGrad = ctx.createLinearGradient(0, 0, 1280, 720);
            bgGrad.addColorStop(0, '#1e293b');
            bgGrad.addColorStop(1, '#0f172a');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, 1280, 720);
        }

        // Draw framed poster
        const posterCanvas = await renderFullPosterCanvas();
        const rect = proxy.getBoundingClientRect();
        const cRect = container.getBoundingClientRect();

        const scaleX = 1280 / cRect.width;
        const scaleY = 720 / cRect.height;

        const px = (rect.left - cRect.left) * scaleX;
        const py = (rect.top - cRect.top) * scaleY;
        const pw = rect.width * scaleX;
        const ph = rect.height * scaleY;

        ctx.save();
        ctx.shadowColor = 'rgba(0,0,0,0.85)';
        ctx.shadowBlur = 30;
        ctx.shadowOffsetY = 15;
        ctx.drawImage(posterCanvas, px, py, pw, ph);

        // Frame border
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 6;
        ctx.strokeRect(px, py, pw, ph);
        ctx.restore();

        // Download snapshot
        const a = document.createElement('a');
        a.href = outCanvas.toDataURL('image/png', 1.0);
        a.download = `ar-wall-preview-${Date.now()}.png`;
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    // 🔄 UPDATE LIVE HTML PREVIEW
    function updateFamFlyerPreview() {
        if (currentPosterLayoutMode === 'allwhite_vip') {
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            setTxt('prevAwPresenter', document.getElementById('aw-presenter')?.value || '');
            setTxt('prevAwHeroMain', document.getElementById('aw-hero-main')?.value || 'LIVE SHOW');
            setTxt('prevAwHeroScript', document.getElementById('aw-hero-script')?.value || '& CONCERT');
            setTxt('prevAwCityPill', document.getElementById('aw-city-pill')?.value || 'IN LONDON');
            setTxt('prevAwDressCode', (document.getElementById('aw-dress-code')?.value || 'Glamorous').split('•')[0].trim());
            setTxt('prevAwDate', document.getElementById('aw-date')?.value || '');
            setTxt('prevAwTime', document.getElementById('aw-time')?.value || '');
            setTxt('prevAwVenue', document.getElementById('aw-venue')?.value || '');
            setTxt('prevAwFeat1', document.getElementById('aw-feat1')?.value || '');
            setTxt('prevAwFeat2', document.getElementById('aw-feat2')?.value || '');
            setTxt('prevAwFeat3', document.getElementById('aw-feat3')?.value || '');
            setTxt('prevAwTickets', '🎟️ ' + (document.getElementById('aw-tickets')?.value || ''));
            setTxt('prevAwRsvp', '📱 ' + (document.getElementById('aw-rsvp')?.value || ''));
        } else {
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            setTxt('prevFlyerHeadline', document.getElementById('flyer-headline')?.value || 'SAVE THE DATE');
            setTxt('prevFlyerIntro', document.getElementById('flyer-intro')?.value || '');
            setTxt('prevFlyerHeroTitle', document.getElementById('flyer-hero-title')?.value || 'BIRTHDAY GALA');
            setTxt('prevFlyerSubtitle', document.getElementById('flyer-subtitle')?.value || '');
            setTxt('prevFlyerMotto', document.getElementById('flyer-motto')?.value || '');
            setTxt('prevDest1Flag', document.getElementById('flyer-dest1-flag')?.value || '🇳🇬');
            setTxt('prevDest1Title', document.getElementById('flyer-dest1-title')?.value || '');
            setTxt('prevDest1Date', document.getElementById('flyer-dest1-date')?.value || '');
            setTxt('prevDest1Venue', document.getElementById('flyer-dest1-venue')?.value || '');
            setTxt('prevDest2Flag', document.getElementById('flyer-dest2-flag')?.value || '🇬🇧');
            setTxt('prevDest2Title', document.getElementById('flyer-dest2-title')?.value || '');
            setTxt('prevDest2Date', document.getElementById('flyer-dest2-date')?.value || '');
            setTxt('prevDest2Venue', document.getElementById('flyer-dest2-venue')?.value || '');
            setTxt('prevFlyerRibbon', document.getElementById('flyer-bottom-ribbon')?.value || '');
        }
    }

    async function generateBackdropArt() {
        const btn = document.getElementById('btn-generate-fam-flyer');
        const prompt = currentPosterLayoutMode === 'allwhite_vip'
            ? 'Luxury VIP live show concert stage gala, golden sparkles and warm lights'
            : 'Luxury emerald and royal navy evening skyline with warm golden firework sparkles';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating Atmosphere Backdrop...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch('/api/ai/generate-flyer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ prompt: prompt, theme: prompt, style: 'luxury_gold_black' })
            });

            const data = await res.json();
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generate AI Atmosphere Backdrop';
            }

            if (data.success && (data.image_base64 || data.data_uri)) {
                currentFamFlyerImageData = data.image_base64 || data.data_uri;
                const card = document.getElementById('famFlyerCardPreview');
                if (card) {
                    card.style.backgroundImage = `linear-gradient(160deg, rgba(4, 18, 28, 0.72) 0%, rgba(5, 8, 18, 0.86) 100%), url('${currentFamFlyerImageData}')`;
                    card.style.backgroundSize = 'cover';
                    card.style.backgroundPosition = 'center';
                }

                const badge = document.getElementById('fam-flyer-badge');
                if (badge) {
                    badge.className = 'badge bg-success text-white px-3 py-1 rounded-pill fw-semibold';
                    badge.textContent = '✨ AI Atmosphere Backdrop Applied';
                }
            }
        } catch (err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generate AI Atmosphere Backdrop';
            }
        }
    }

    function loadCanvasImage(src) {
        return new Promise((resolve) => {
            if (!src) return resolve(null);
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = src;
        });
    }

    /**
     * Composites a high-resolution, print-ready event poster respecting user-selected format, layers, and AR elements.
     */
    async function renderFullPosterCanvas() {
        // Wait for all web fonts to load completely before drawing on canvas
        try {
            if (document.fonts && document.fonts.ready) {
                await document.fonts.ready;
            }
        } catch (e) {
            console.warn('Font ready check failed, proceeding:', e);
        }

        const canvas = document.createElement('canvas');
        let width = 1200;
        let height = 1600;

        if (currentFlyerFormat === 'story_9_16') {
            width = 1080;
            height = 1920;
        } else if (currentFlyerFormat === 'square_1_1') {
            width = 1200;
            height = 1200;
        } else if (currentFlyerFormat === 'landscape_16_9') {
            width = 1200;
            height = 675;
        }

        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        if (!ctx) throw new Error('Canvas context unavailable');

        // Helper for rounded rectangles with fallback
        const fillRoundRect = (x, y, w, h, r, fillStyle, strokeStyle = null, lineWidth = 0) => {
            ctx.save();
            ctx.beginPath();
            if (ctx.roundRect) {
                ctx.roundRect(x, y, w, h, r);
            } else {
                ctx.rect(x, y, w, h);
            }
            if (fillStyle) {
                ctx.fillStyle = fillStyle;
                ctx.fill();
            }
            if (strokeStyle && lineWidth > 0) {
                ctx.strokeStyle = strokeStyle;
                ctx.lineWidth = lineWidth;
                ctx.stroke();
            }
            ctx.restore();
        };

        // Helper to fit text within available width
        const drawFittedText = (text, x, y, maxWidth, targetSize, fontDef, fillStyle, align = 'center', shadow = false) => {
            ctx.save();
            ctx.textAlign = align;
            let size = targetSize;
            ctx.font = `${size}px ${fontDef}`;
            while (ctx.measureText(text).width > maxWidth && size > 14) {
                size -= 2;
                ctx.font = `${size}px ${fontDef}`;
            }
            if (shadow) {
                ctx.shadowColor = 'rgba(0, 0, 0, 0.9)';
                ctx.shadowBlur = 18;
            }
            ctx.fillStyle = fillStyle;
            ctx.fillText(text, x, y);
            ctx.restore();
        };

        // 1. Custom / Preset Background Colour (respecting user-chosen colour & fill style)
        if (currentBgFillStyle === 'solid') {
            ctx.fillStyle = currentBgColor1 || '#021a12';
            ctx.fillRect(0, 0, width, height);
        } else if (currentBgFillStyle === 'radial') {
            const c1 = currentBgColor1 || '#2a1f0a';
            const c2 = currentBgColor2 || '#120e06';
            const c3 = darkenHexColor(c2, 0.45);
            const bgGrad = ctx.createRadialGradient(width / 2, height * 0.28, 80, width / 2, height * 0.5, height * 0.75);
            bgGrad.addColorStop(0, c1);
            bgGrad.addColorStop(0.55, c2);
            bgGrad.addColorStop(1, c3);
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);
        } else {
            const c1 = currentBgColor1 || '#021a12';
            const c2 = currentBgColor2 || '#041424';
            const c3 = darkenHexColor(c2, 0.55);
            const bgGrad = ctx.createLinearGradient(0, 0, width, height);
            bgGrad.addColorStop(0, c1);
            bgGrad.addColorStop(0.55, c2);
            bgGrad.addColorStop(1, c3);
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);
        }

        // Optional AI Backdrop Overlay
        if (currentFamFlyerImageData) {
            const bgImg = await loadCanvasImage(currentFamFlyerImageData);
            if (bgImg) {
                ctx.save();
                ctx.globalAlpha = 0.32;
                ctx.drawImage(bgImg, 0, 0, width, height);
                ctx.restore();
            }
        }

        // Sparkle / Bokeh Golden Particles
        ctx.save();
        const bokehCount = 12;
        for (let i = 0; i < bokehCount; i++) {
            const bx = (width * 0.08) + Math.random() * (width * 0.84);
            const by = (height * 0.08) + Math.random() * (height * 0.84);
            const br = 20 + Math.random() * 40;
            const g = ctx.createRadialGradient(bx, by, 2, bx, by, br);
            g.addColorStop(0, 'rgba(255, 215, 0, 0.35)');
            g.addColorStop(1, 'rgba(255, 215, 0, 0)');
            ctx.fillStyle = g;
            ctx.beginPath();
            ctx.arc(bx, by, br, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();

        // 2. Ornate Double Gold Borders & Luxury Corner Accents
        ctx.save();
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 6;
        ctx.strokeRect(32, 32, width - 64, height - 64);
        ctx.strokeStyle = 'rgba(252, 231, 154, 0.45)';
        ctx.lineWidth = 2;
        ctx.strokeRect(48, 48, width - 96, height - 96);

        // Corner Stars
        ctx.font = '22px serif';
        ctx.fillStyle = '#ffd700';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('★', 40, 40);
        ctx.fillText('★', width - 40, 40);
        ctx.fillText('★', 40, height - 40);
        ctx.fillText('★', width - 40, height - 40);
        ctx.restore();

        // Gold Helper Gradient
        const makeGoldGradient = (yStart, yEnd) => {
            const g = ctx.createLinearGradient(0, yStart, 0, yEnd);
            g.addColorStop(0, '#fff7c2');
            g.addColorStop(0.35, '#ffd700');
            g.addColorStop(0.7, '#d4af37');
            g.addColorStop(1, '#8c6614');
            return g;
        };

        const isLandscape = (currentFlyerFormat === 'landscape_16_9');

        // ====================================================================
        // MODE 2: LIVE SHOW & CONCERT
        // ====================================================================
        if (currentPosterLayoutMode === 'allwhite_vip') {
            const presenter = (document.getElementById('aw-presenter')?.value || 'FAMILY ENTERTAINMENT PRESENTS').toUpperCase();
            const heroMain = (document.getElementById('aw-hero-main')?.value || 'LIVE SHOW').toUpperCase();
            const heroScript = document.getElementById('aw-hero-script')?.value || '& CONCERT';
            const cityPill = (document.getElementById('aw-city-pill')?.value || 'IN LONDON').toUpperCase();
            const dressCode = document.getElementById('aw-dress-code')?.value || 'Glamorous & Chic';
            const dateStr = (document.getElementById('aw-date')?.value || 'FRIDAY 30TH MAY 2026').toUpperCase();
            const timeStr = (document.getElementById('aw-time')?.value || '7PM - MIDNIGHT').toUpperCase();
            const venueStr = (document.getElementById('aw-venue')?.value || 'THE GRAND ARENA, LONDON SW1').toUpperCase();
            const feat1 = (document.getElementById('aw-feat1')?.value || '🎧 LIVE BAND').toUpperCase();
            const feat2 = (document.getElementById('aw-feat2')?.value || '🥂 VIP TABLES').toUpperCase();
            const feat3 = (document.getElementById('aw-feat3')?.value || '👥 RED CARPET').toUpperCase();
            const tickets = document.getElementById('aw-tickets')?.value || 'TICKETS: RSVP VIA FAMILY PLATFORM';
            const rsvp = document.getElementById('aw-rsvp')?.value || 'RESERVATIONS: DM HOST OR IN-APP';

            // Performer / Headliner Artist Photo on Canvas in Mode 2 (Live Shows & Gigs)
            if (hasCelebrantPhoto && currentCelebrantPhotoData) {
                const celImg = await loadCanvasImage(currentCelebrantPhotoData);
                if (celImg) {
                    ctx.save();
                    ctx.globalAlpha = 0.50;
                    const targetW = isLandscape ? width * 0.45 : width;
                    const targetH = isLandscape ? height * 0.90 : height * 0.58;
                    const imgRatio = celImg.width / celImg.height;
                    const targetRatio = targetW / targetH;
                    let sX = 0, sY = 0, sW = celImg.width, sH = celImg.height;
                    if (imgRatio > targetRatio) {
                        sW = celImg.height * targetRatio;
                        sX = (celImg.width - sW) / 2;
                    } else {
                        sH = celImg.width / targetRatio;
                        sY = 0;
                    }
                    const drawX = isLandscape ? 40 : 0;
                    ctx.drawImage(celImg, sX, sY, sW, sH, drawX, 0, targetW, targetH);
                    
                    // Fade bottom into stage background
                    const fadeGrad = ctx.createLinearGradient(0, targetH * 0.45, 0, targetH);
                    fadeGrad.addColorStop(0, 'rgba(4, 18, 28, 0)');
                    fadeGrad.addColorStop(1, '#050c18');
                    ctx.fillStyle = fadeGrad;
                    ctx.fillRect(drawX, targetH * 0.45, targetW, targetH * 0.55);
                    ctx.restore();
                }
            }

            if (isLandscape) {
                // Widescreen 16:9 2-column layout
                const leftX = width * 0.32;
                const rightX = width * 0.74;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '700 16px "Montserrat", sans-serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText(presenter, leftX, height * 0.12);

                ctx.font = '36px serif';
                ctx.fillText('👑', leftX, height * 0.22);

                drawFittedText(heroMain, leftX, height * 0.38, width * 0.5, 68, '"Cinzel", Georgia, serif', '#ffffff', 'center', true);

                ctx.font = 'italic 52px "Great Vibes", Georgia, serif';
                ctx.fillStyle = makeGoldGradient(height * 0.40, height * 0.54);
                ctx.fillText(heroScript, leftX, height * 0.52);

                // City Pill
                fillRoundRect(leftX - 140, height * 0.58, 280, 42, 21, 'rgba(15, 23, 42, 0.9)', '#d4af37', 2);
                ctx.font = '800 18px "Cinzel", Georgia, serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText(cityPill, leftX, height * 0.58 + 27);

                // Right Column Cards
                fillRoundRect(width * 0.54, height * 0.12, width * 0.4, height * 0.32, 20, 'rgba(12, 18, 32, 0.92)', '#d4af37', 2);
                drawFittedText(`📅 ${dateStr}`, rightX, height * 0.22, width * 0.36, 22, '"Montserrat", sans-serif', '#ffffff');
                drawFittedText(`⏰ ${timeStr}`, rightX, height * 0.28, width * 0.36, 18, '"Montserrat", sans-serif', '#fbbf24');
                drawFittedText(`📍 ${venueStr}`, rightX, height * 0.36, width * 0.36, 18, '"Montserrat", sans-serif', '#fce79a');

                fillRoundRect(width * 0.54, height * 0.48, width * 0.4, height * 0.38, 20, 'rgba(12, 18, 32, 0.92)', '#ffd700', 2);
                drawFittedText(`🎟️ ${tickets}`, rightX, height * 0.58, width * 0.36, 18, '"Montserrat", sans-serif', '#fbbf24');
                drawFittedText(`📱 ${rsvp}`, rightX, height * 0.66, width * 0.36, 17, '"Montserrat", sans-serif', '#ffffff');
                drawFittedText(`✨ Dress Code: ${dressCode}`, rightX, height * 0.76, width * 0.36, 16, '"Montserrat", sans-serif', '#fce79a');
                ctx.restore();
            } else {
                // Portrait / Story / Square vertical stack
                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '700 22px "Montserrat", sans-serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText(presenter, width / 2, height * 0.075);

                // Dress Code Seal
                const sealX = width * 0.18;
                const sealY = height * 0.16;
                ctx.beginPath();
                ctx.arc(sealX, sealY, Math.min(80, height * 0.055), 0, Math.PI * 2);
                ctx.fillStyle = '#12100c';
                ctx.fill();
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#ffd700';
                ctx.stroke();

                ctx.font = '800 14px "Montserrat", sans-serif';
                ctx.fillStyle = '#fbbf24';
                ctx.fillText('DRESS CODE:', sealX, sealY - 18);
                ctx.font = 'italic 28px "Great Vibes", serif';
                ctx.fillStyle = '#ffffff';
                ctx.fillText(dressCode.split('•')[0].trim(), sealX, sealY + 12);
                ctx.font = '700 11px "Montserrat", sans-serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText('GLAMOROUS', sealX, sealY + 34);

                // Crown + Hero Title
                ctx.font = '50px serif';
                ctx.fillText('👑', width / 2, height * 0.14);

                drawFittedText(heroMain, width / 2, height * 0.22, width * 0.85, 100, '"Cinzel", Georgia, serif', '#ffffff', 'center', true);

                ctx.font = 'italic 95px "Great Vibes", Georgia, serif';
                ctx.fillStyle = makeGoldGradient(height * 0.23, height * 0.31);
                ctx.fillText(heroScript, width / 2, height * 0.30);

                // City Pill
                const pillW = 320;
                const pillH = 54;
                fillRoundRect((width - pillW) / 2, height * 0.33, pillW, pillH, 27, 'rgba(15, 23, 42, 0.92)', '#d4af37', 2.5);
                ctx.font = '800 24px "Cinzel", Georgia, serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText(cityPill, width / 2, height * 0.33 + 36);

                // Date & Venue Card
                const cardW = width * 0.84;
                const cardH = height * 0.13;
                const cardX = (width - cardW) / 2;
                const cardY = height * 0.44;
                fillRoundRect(cardX, cardY, cardW, cardH, 24, 'rgba(12, 18, 32, 0.9)', '#d4af37', 2.5);

                drawFittedText(`📅 ${dateStr}   •   ⏰ ${timeStr}`, width / 2, cardY + cardH * 0.42, cardW * 0.92, 30, '"Montserrat", sans-serif', '#ffffff');
                drawFittedText(`📍 ${venueStr}`, width / 2, cardY + cardH * 0.80, cardW * 0.92, 26, '"Montserrat", sans-serif', '#fce79a');

                // 3-Column VIP Perks
                const perks = [feat1, feat2, feat3];
                const colW = (width * 0.84) / 3 - 16;
                const startX = (width - ((colW * 3) + 32)) / 2;
                const perkY = height * 0.60;
                perks.forEach((pText, idx) => {
                    const px = startX + idx * (colW + 16);
                    fillRoundRect(px, perkY, colW, 60, 16, 'rgba(15, 20, 28, 0.9)', 'rgba(212, 175, 55, 0.7)', 2);
                    drawFittedText(pText, px + colW / 2, perkY + 38, colW * 0.88, 19, '"Montserrat", sans-serif', '#fce79a');
                });

                // Tickets & RSVP Banner
                const banY = height * 0.72;
                const banH = height * 0.18;
                fillRoundRect(60, banY, width - 120, banH, 20, 'rgba(8, 8, 12, 0.96)', '#d4af37', 3);
                drawFittedText('🎟️ ' + tickets, width / 2, banY + banH * 0.40, (width - 160), 28, '"Montserrat", sans-serif', '#fbbf24');
                drawFittedText('📱 ' + rsvp, width / 2, banY + banH * 0.78, (width - 160), 24, '"Montserrat", sans-serif', '#ffffff');
                ctx.restore();
            }

            return canvas;
        }

        // ====================================================================
        // MODE 1: CELEBRATION GALA (BIRTHDAY, JUBILEE, FIESTA, AR)
        // ====================================================================
        // Celebrant Portrait Cutout (when user has uploaded portrait and layer is visible)
        if (hasCelebrantPhoto && currentCelebrantPhotoData && flyerLayerState.layers.celebrant.visible) {
            const celImg = await loadCanvasImage(currentCelebrantPhotoData);
            if (celImg) {
                ctx.save();
                const scaleMult = (flyerLayerState.layers.celebrant.scale || 100) / 100;
                const targetW = width * (isLandscape ? 0.32 : 0.50) * scaleMult;
                const targetH = height * (isLandscape ? 0.80 : 0.64) * scaleMult;
                const drawX = 48;
                const drawY = 60;

                // Aspect ratio preserving cover crop
                const imgRatio = celImg.width / celImg.height;
                const targetRatio = targetW / targetH;
                let sX = 0, sY = 0, sW = celImg.width, sH = celImg.height;
                if (imgRatio > targetRatio) {
                    sW = celImg.height * targetRatio;
                    sX = (celImg.width - sW) / 2;
                } else {
                    sH = celImg.width / targetRatio;
                    sY = 0; // Keep head at top
                }

                ctx.drawImage(celImg, sX, sY, sW, sH, drawX, drawY, targetW, targetH);

                // Feather right edge so it blends into the ambient background
                const fadeRight = ctx.createLinearGradient(drawX + targetW * 0.6, 0, drawX + targetW, 0);
                fadeRight.addColorStop(0, 'rgba(4, 20, 36, 0)');
                fadeRight.addColorStop(1, '#041424');
                ctx.fillStyle = fadeRight;
                ctx.fillRect(drawX + targetW * 0.6, drawY, targetW * 0.45, targetH);
                ctx.restore();
            }
        }

        const headline = (document.getElementById('flyer-headline')?.value || 'SAVE THE DATE').toUpperCase();
        const intro = document.getElementById('flyer-intro')?.value || 'For the Grand Celebration of';
        const heroTitle = (document.getElementById('flyer-hero-title')?.value || 'BIRTHDAY GALA').toUpperCase();
        const subtitle = (document.getElementById('flyer-subtitle')?.value || 'IN NIGERIA & UK').toUpperCase();
        const motto = document.getElementById('flyer-motto')?.value || 'Celebrating Life • Honouring Legacy • Family First';
        const d1Flag = document.getElementById('flyer-dest1-flag')?.value || '🇳🇬';
        const d1Title = document.getElementById('flyer-dest1-title')?.value || 'NIGERIA Celebration';
        const d1Date = document.getElementById('flyer-dest1-date')?.value || 'DATE: AUGUST, 2027';
        const d1Venue = document.getElementById('flyer-dest1-venue')?.value || 'VENUE: COMING SOON';
        const d2Flag = document.getElementById('flyer-dest2-flag')?.value || '🇬🇧';
        const d2Title = document.getElementById('flyer-dest2-title')?.value || 'UK Celebration';
        const d2Date = document.getElementById('flyer-dest2-date')?.value || 'DATE: SEPTEMBER, 2027';
        const d2Venue = document.getElementById('flyer-dest2-venue')?.value || 'VENUE: COMING SOON';
        const ribbon = document.getElementById('flyer-bottom-ribbon')?.value || '★ More Details to Follow ★';

        if (isLandscape) {
            // WIDESCREEN LANDSCAPE (16:9): 2-Column Balanced Architecture
            const leftCenterX = width * 0.32;

            // Left Column: Typography
            if (flyerLayerState.layers.headline.visible) {
                ctx.save();
                ctx.font = '32px serif';
                ctx.textAlign = 'center';
                ctx.fillText('👑', leftCenterX, height * 0.12);
                drawFittedText(headline, leftCenterX, height * 0.20 + (flyerLayerState.layers.headline.offsetY || 0), width * 0.48, Math.round(flyerLayerState.layers.headline.size * 1.5), flyerLayerState.layers.headline.font, makeGoldGradient(height * 0.16, height * 0.22), flyerLayerState.layers.headline.align || 'center');
                ctx.restore();
            }

            if (flyerLayerState.layers.intro.visible) {
                drawFittedText(intro, leftCenterX, height * 0.30 + (flyerLayerState.layers.intro.offsetY || 0), width * 0.48, Math.round(flyerLayerState.layers.intro.size * 1.5), flyerLayerState.layers.intro.font, '#ffffff', flyerLayerState.layers.intro.align || 'center');
            }

            if (flyerLayerState.layers.hero_title.visible) {
                drawFittedText(heroTitle, leftCenterX, height * 0.46 + (flyerLayerState.layers.hero_title.offsetY || 0), width * 0.52, Math.round(flyerLayerState.layers.hero_title.size * 1.8), flyerLayerState.layers.hero_title.font, makeGoldGradient(height * 0.36, height * 0.50), flyerLayerState.layers.hero_title.align || 'center', true);
            }

            if (flyerLayerState.layers.subtitle.visible) {
                drawFittedText(subtitle, leftCenterX, height * 0.58 + (flyerLayerState.layers.subtitle.offsetY || 0), width * 0.48, Math.round(flyerLayerState.layers.subtitle.size * 1.5), flyerLayerState.layers.subtitle.font, '#ffffff', flyerLayerState.layers.subtitle.align || 'center');
            }

            if (flyerLayerState.layers.motto.visible) {
                drawFittedText(motto, leftCenterX, height * 0.70 + (flyerLayerState.layers.motto.offsetY || 0), width * 0.50, Math.round(flyerLayerState.layers.motto.size * 1.5), flyerLayerState.layers.motto.font, '#fce79a', flyerLayerState.layers.motto.align || 'center');
            }

            if (flyerLayerState.layers.ribbon.visible) {
                drawFittedText(ribbon, leftCenterX, height * 0.88 + (flyerLayerState.layers.ribbon.offsetY || 0), width * 0.50, Math.round(flyerLayerState.layers.ribbon.size * 1.5), flyerLayerState.layers.ribbon.font, '#fce79a', flyerLayerState.layers.ribbon.align || 'center');
            }

            // Right Column: Twin Plaques stacked vertically
            const shieldX = width * 0.58;
            const shieldW = width * 0.36;
            const shieldH = height * 0.36;

            const drawShield = (x, y, w, h, bgTop, bgBot, flag, title, dateTxt, venueTxt) => {
                const g = ctx.createLinearGradient(0, y, 0, y + h);
                g.addColorStop(0, bgTop);
                g.addColorStop(1, bgBot);
                fillRoundRect(x, y, w, h, 20, g, '#d4af37', 3);

                // Flag circle badge
                ctx.save();
                ctx.beginPath();
                ctx.arc(x + 40, y + 40, 24, 0, Math.PI * 2);
                ctx.fillStyle = bgBot;
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#ffd700';
                ctx.stroke();
                ctx.textAlign = 'center';
                ctx.font = '22px serif';
                ctx.fillText(flag, x + 40, y + 48);

                drawFittedText(title, x + 80, y + 46, w - 90, 22, '"Cinzel", Georgia, serif', '#fce79a', 'left');
                drawFittedText('📅 ' + dateTxt, x + 24, y + h * 0.62, w - 48, 17, '"Montserrat", sans-serif', '#fbbf24', 'left');
                drawFittedText('📍 ' + venueTxt, x + 24, y + h * 0.84, w - 48, 17, '"Montserrat", sans-serif', '#fde68a', 'left');
                ctx.restore();
            };

            if (flyerLayerState.layers.dest1.visible) {
                drawShield(shieldX, height * 0.12, shieldW, shieldH, 'rgba(6, 38, 26, 0.94)', 'rgba(2, 16, 11, 0.98)', d1Flag, d1Title, d1Date, d1Venue);
            }
            if (flyerLayerState.layers.dest2.visible) {
                drawShield(shieldX, height * 0.54, shieldW, shieldH, 'rgba(8, 24, 56, 0.94)', 'rgba(3, 10, 26, 0.98)', d2Flag, d2Title, d2Date, d2Venue);
            }

        } else {
            // PORTRAIT (3:4), STORY (9:16), SQUARE (1:1)
            const hasCelebrant = (hasCelebrantPhoto && flyerLayerState.layers.celebrant.visible);
            const centerX = hasCelebrant ? width * 0.68 : width / 2;
            const maxTextWidth = hasCelebrant ? width * 0.56 : width * 0.86;

            // Headline
            if (flyerLayerState.layers.headline.visible) {
                ctx.save();
                ctx.font = '48px serif';
                ctx.textAlign = 'center';
                ctx.fillText('👑', centerX, height * 0.095);
                drawFittedText(headline, centerX, height * 0.14 + (flyerLayerState.layers.headline.offsetY || 0), maxTextWidth, flyerLayerState.layers.headline.size * 2, flyerLayerState.layers.headline.font, makeGoldGradient(height * 0.11, height * 0.15), flyerLayerState.layers.headline.align || 'center');
                ctx.restore();
            }

            // Intro
            if (flyerLayerState.layers.intro.visible) {
                drawFittedText(intro, centerX, height * 0.18 + (flyerLayerState.layers.intro.offsetY || 0), maxTextWidth, flyerLayerState.layers.intro.size * 2, flyerLayerState.layers.intro.font, '#ffffff', flyerLayerState.layers.intro.align || 'center');
            }

            // Hero Title
            if (flyerLayerState.layers.hero_title.visible) {
                drawFittedText(heroTitle, centerX, height * 0.25 + (flyerLayerState.layers.hero_title.offsetY || 0), maxTextWidth, flyerLayerState.layers.hero_title.size * 2.3, flyerLayerState.layers.hero_title.font, makeGoldGradient(height * 0.20, height * 0.27), flyerLayerState.layers.hero_title.align || 'center', true);
            }

            // Subtitle
            if (flyerLayerState.layers.subtitle.visible) {
                drawFittedText(subtitle, centerX, height * 0.29 + (flyerLayerState.layers.subtitle.offsetY || 0), maxTextWidth, flyerLayerState.layers.subtitle.size * 2, flyerLayerState.layers.subtitle.font, '#ffffff', flyerLayerState.layers.subtitle.align || 'center');
            }

            // Divider
            ctx.save();
            ctx.strokeStyle = '#d4af37';
            ctx.lineWidth = 2.5;
            ctx.beginPath();
            ctx.moveTo(centerX - 180, height * 0.315);
            ctx.lineTo(centerX + 180, height * 0.315);
            ctx.stroke();
            ctx.restore();

            // Motto
            if (flyerLayerState.layers.motto.visible) {
                drawFittedText(motto, centerX, height * 0.35 + (flyerLayerState.layers.motto.offsetY || 0), maxTextWidth, flyerLayerState.layers.motto.size * 2, flyerLayerState.layers.motto.font, '#fce79a', flyerLayerState.layers.motto.align || 'center');
            }

            // Custom User Text Layers
            if (flyerLayerState.customLayers && flyerLayerState.customLayers.length > 0) {
                let customY = height * 0.40;
                flyerLayerState.customLayers.forEach(l => {
                    drawFittedText(l.text, centerX, customY, maxTextWidth, 24, '"Cinzel", serif', '#ffd700', 'center');
                    customY += 34;
                });
            }

            // Twin Plaques (Destinations / Venues)
            const shieldY = height * (currentFlyerFormat === 'square_1_1' ? 0.62 : 0.66);
            const shieldW = width * 0.42;
            const shieldH = height * (currentFlyerFormat === 'square_1_1' ? 0.21 : 0.18);

            const drawShield = (x, y, w, h, bgTop, bgBot, flag, title, dateTxt, venueTxt) => {
                const g = ctx.createLinearGradient(0, y, 0, y + h);
                g.addColorStop(0, bgTop);
                g.addColorStop(1, bgBot);
                fillRoundRect(x, y, w, h, 24, g, '#d4af37', 3.5);

                // Flag circle badge
                ctx.save();
                const badgeR = Math.min(34, h * 0.20);
                ctx.beginPath();
                ctx.arc(x + w / 2, y, badgeR, 0, Math.PI * 2);
                ctx.fillStyle = bgBot;
                ctx.fill();
                ctx.lineWidth = 2.5;
                ctx.strokeStyle = '#ffd700';
                ctx.stroke();
                ctx.textAlign = 'center';
                ctx.font = `${Math.round(badgeR * 0.95)}px serif`;
                ctx.fillText(flag, x + w / 2, y + badgeR * 0.35);

                drawFittedText(title, x + w / 2, y + h * 0.38, w * 0.88, 26, '"Cinzel", Georgia, serif', '#fce79a', 'center');
                drawFittedText('📅  ' + dateTxt, x + 24, y + h * 0.65, w - 48, 20, '"Montserrat", sans-serif', '#fbbf24', 'left');
                drawFittedText('📍  ' + venueTxt, x + 24, y + h * 0.86, w - 48, 20, '"Montserrat", sans-serif', '#fde68a', 'left');
                ctx.restore();
            };

            if (flyerLayerState.layers.dest1.visible) {
                drawShield(width * 0.06, shieldY, shieldW, shieldH, 'rgba(6, 38, 26, 0.94)', 'rgba(2, 16, 11, 0.98)', d1Flag, d1Title, d1Date, d1Venue);
            }
            if (flyerLayerState.layers.dest2.visible) {
                drawShield(width * 0.52, shieldY, shieldW, shieldH, 'rgba(8, 24, 56, 0.94)', 'rgba(3, 10, 26, 0.98)', d2Flag, d2Title, d2Date, d2Venue);
            }

            // AR QR Code Stamp
            if (hasArQrBadge) {
                const qrY = height * 0.88;
                fillRoundRect((width - 320) / 2, qrY, 320, 46, 23, 'rgba(15, 23, 42, 0.92)', '#d4af37', 2);
                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '700 17px "Montserrat", sans-serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText('📱 SCAN FOR 3D AR VIEW', width / 2, qrY + 30);
                ctx.restore();
            }

            // Bottom Ribbon
            if (flyerLayerState.layers.ribbon.visible) {
                ctx.save();
                ctx.strokeStyle = 'rgba(212, 175, 55, 0.75)';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.moveTo(160, height * 0.945);
                ctx.lineTo(width - 160, height * 0.945);
                ctx.stroke();

                drawFittedText(ribbon, width / 2, height * 0.975 + (flyerLayerState.layers.ribbon.offsetY || 0), width * 0.85, flyerLayerState.layers.ribbon.size * 2, flyerLayerState.layers.ribbon.font, '#fce79a', flyerLayerState.layers.ribbon.align || 'center');
                ctx.restore();
            }
        }

        return canvas;
    }

    async function downloadFamFlyer() {
        const btn = event?.currentTarget || document.querySelector('button[onclick="downloadFamFlyer()"]');
        const originalText = btn ? btn.innerHTML : '';
        const feedbackEl = document.getElementById('flyerActionFeedback');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Compositing Event Poster...';
        }
        if (feedbackEl) {
            feedbackEl.innerHTML = '<span class="text-warning"><i class="bi bi-hourglass-split me-1"></i> Rendering high-resolution canvas...</span>';
        }

        try {
            const canvas = await renderFullPosterCanvas();
            const rawTitle = currentPosterLayoutMode === 'allwhite_vip'
                ? (document.getElementById('aw-hero-main')?.value || 'live-show')
                : (document.getElementById('flyer-hero-title')?.value || 'birthday-gala');
            const slug = rawTitle.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-');
            const filename = `${slug}-poster.png`;

            if (canvas.toBlob) {
                canvas.toBlob((blob) => {
                    if (!blob) throw new Error('Canvas blob generation failed');
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(() => {
                        URL.revokeObjectURL(blobUrl);
                        a.remove();
                    }, 2500);
                    if (feedbackEl) {
                        feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Poster downloaded in high definition!</span>';
                        setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 6000);
                    }
                }, 'image/png', 1.0);
            } else {
                const dataUrl = canvas.toDataURL('image/png', 1.0);
                const a = document.createElement('a');
                a.href = dataUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                if (feedbackEl) {
                    feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Poster downloaded in high definition!</span>';
                    setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 6000);
                }
            }
        } catch (err) {
            console.error('Poster download failed:', err);
            if (feedbackEl) {
                feedbackEl.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Download failed: ${err.message}</span>`;
            }
            alert('Poster download failed: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    function buildFlyerSharePayload() {
        let shareTitle = '';
        let shareText = '';
        const inviteUrl = window.location.origin;

        if (currentPosterLayoutMode === 'allwhite_vip') {
            const hero = (document.getElementById('aw-hero-main')?.value || 'LIVE SHOW').trim();
            const script = (document.getElementById('aw-hero-script')?.value || '& CONCERT').trim();
            shareTitle = `${hero} ${script}`;
            const city = document.getElementById('aw-city-pill')?.value || 'LONDON';
            const date = document.getElementById('aw-date')?.value || '';
            const time = document.getElementById('aw-time')?.value || '';
            const venue = document.getElementById('aw-venue')?.value || '';
            const dress = document.getElementById('aw-dress-code')?.value || 'Glamorous & Chic';
            const tickets = document.getElementById('aw-tickets')?.value || 'RSVP via Family Platform';
            const rsvp = document.getElementById('aw-rsvp')?.value || 'DM Host or in-app';

            shareText = `🎭 *${shareTitle} (${city})*\n\n` +
                `📅 *When:* ${date} (${time})\n` +
                `📍 *Where:* ${venue}\n` +
                `✨ *Dress Code:* ${dress}\n` +
                `🎟️ *Tickets:* ${tickets}\n` +
                `📱 *Reservations:* ${rsvp}\n\n` +
                `🔗 *RSVP & Details:* ${inviteUrl}\n` +
                `🏛️ *Official Invitation via FamilyPlatform*`;
        } else {
            const title = document.getElementById('flyer-hero-title')?.value || 'BIRTHDAY GALA';
            const headline = document.getElementById('flyer-headline')?.value || 'SAVE THE DATE';
            const sub = document.getElementById('flyer-subtitle')?.value || '';
            const motto = document.getElementById('flyer-motto')?.value || '';
            const d1Flag = document.getElementById('flyer-dest1-flag')?.value || '🇳🇬';
            const d1Title = document.getElementById('flyer-dest1-title')?.value || '';
            const d1Date = document.getElementById('flyer-dest1-date')?.value || '';
            const d1Venue = document.getElementById('flyer-dest1-venue')?.value || '';
            const d2Flag = document.getElementById('flyer-dest2-flag')?.value || '🇬🇧';
            const d2Title = document.getElementById('flyer-dest2-title')?.value || '';
            const d2Date = document.getElementById('flyer-dest2-date')?.value || '';
            const d2Venue = document.getElementById('flyer-dest2-venue')?.value || '';
            const ribbon = document.getElementById('flyer-bottom-ribbon')?.value || '';

            shareTitle = `${headline}: ${title}${sub ? ' (' + sub + ')' : ''}`;
            shareText = `👑 *${headline}: ${title}*` + (sub ? ` (${sub})\n\n` : `\n\n`) +
                (motto ? `✨ _${motto}_\n\n` : '') +
                (d1Title ? `${d1Flag} *${d1Title}*\n📅 ${d1Date}\n📍 ${d1Venue}\n\n` : '') +
                (d2Title ? `${d2Flag} *${d2Title}*\n📅 ${d2Date}\n📍 ${d2Venue}\n\n` : '') +
                (ribbon ? `🌟 *${ribbon}*\n\n` : '') +
                `🔗 *RSVP & Details:* ${inviteUrl}\n` +
                `🏛️ *Created & Shared via FamilyPlatform*`;
        }

        const slug = shareTitle.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '') || 'event';
        const filename = `${slug}-flyer.png`;
        return { shareTitle, shareText, shareUrl: inviteUrl, filename };
    }

    function syncPosterShareLinks() {
        const { shareTitle, shareText, shareUrl } = buildFlyerSharePayload();
        const encText = encodeURIComponent(shareText);
        const encUrl = encodeURIComponent(shareUrl);
        const encTitle = encodeURIComponent(shareTitle);

        const waUrl = 'https://api.whatsapp.com/send?text=' + encText;
        const fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encUrl + '&quote=' + encText;
        const twUrl = 'https://twitter.com/intent/tweet?text=' + encText;
        const tgUrl = 'https://t.me/share/url?url=' + encUrl + '&text=' + encText;
        const emUrl = 'mailto:?subject=' + encTitle + '&body=' + encText;

        const btnPrimaryWa = document.getElementById('btnPrimaryPosterWhatsApp');
        const btnWa = document.getElementById('btnSocialShareWhatsapp');
        const btnFb = document.getElementById('btnSocialShareFacebook');
        const btnTw = document.getElementById('btnSocialShareTwitter');
        const btnTg = document.getElementById('btnSocialShareTelegram');
        const btnEm = document.getElementById('btnSocialShareEmail');

        if (btnPrimaryWa) btnPrimaryWa.href = waUrl;
        if (btnWa) btnWa.href = waUrl;
        if (btnFb) btnFb.href = fbUrl;
        if (btnTw) btnTw.href = twUrl;
        if (btnTg) btnTg.href = tgUrl;
        if (btnEm) btnEm.href = emUrl;
    }

    function canvasToBlobAsync(canvas) {
        return new Promise((resolve) => {
            if (!canvas || !canvas.toBlob) return resolve(null);
            canvas.toBlob((blob) => resolve(blob), 'image/png', 1.0);
        });
    }

    function triggerBlobDownload(blob, filename) {
        if (!blob) return;
        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            URL.revokeObjectURL(blobUrl);
            a.remove();
        }, 2500);
    }

    async function copyPosterToClipboard(blob, fallbackText) {
        try {
            if (blob && navigator.clipboard && window.ClipboardItem) {
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                return 'image';
            }
        } catch (_) {}
        try {
            if (fallbackText && navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(fallbackText);
                return 'text';
            }
        } catch (_) {}
        return null;
    }

    async function sharePosterToSocial(platform, evt) {
        const btn = evt?.currentTarget;
        const originalText = btn ? btn.innerHTML : '';
        const feedbackEl = document.getElementById('flyerActionFeedback');
        syncPosterShareLinks();
        const { shareTitle, shareText, shareUrl, filename } = buildFlyerSharePayload();

        const isNativeAnchor = Boolean(btn && btn.tagName === 'A' && btn.getAttribute('href'));
        if (!isNativeAnchor) {
            const encodedText = encodeURIComponent(shareText);
            const encodedUrl = encodeURIComponent(shareUrl);
            const encodedTitle = encodeURIComponent(shareTitle);

            if (platform === 'whatsapp') {
                window.open('https://api.whatsapp.com/send?text=' + encodedText, '_blank', 'noopener,noreferrer');
            } else if (platform === 'facebook') {
                window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl + '&quote=' + encodedText, '_blank', 'noopener,noreferrer');
            } else if (platform === 'instagram') {
                window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
            } else if (platform === 'twitter') {
                window.open('https://twitter.com/intent/tweet?text=' + encodedText, '_blank', 'noopener,noreferrer');
            } else if (platform === 'telegram') {
                window.open('https://t.me/share/url?url=' + encodedUrl + '&text=' + encodedText, '_blank', 'noopener,noreferrer');
            } else if (platform === 'email') {
                window.location.href = 'mailto:?subject=' + encodedTitle + '&body=' + encodedText;
            }
        }

        if (btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Preparing...';
        }

        try {
            const canvas = await renderFullPosterCanvas();
            const blob = await canvasToBlobAsync(canvas);

            if (platform === 'instagram') {
                try { await navigator.clipboard?.writeText(shareText); } catch (_) {}
                triggerBlobDownload(blob, filename);
                if (feedbackEl) {
                    feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-instagram me-1"></i> Instagram opened! Poster downloaded &amp; caption copied — upload the image to your Story or Feed and paste!</span>';
                }
            } else {
                const copiedMode = await copyPosterToClipboard(blob, shareText);
                triggerBlobDownload(blob, filename);
                const labelMap = {
                    whatsapp: 'WhatsApp',
                    facebook: 'Facebook',
                    twitter: 'X (Twitter)',
                    telegram: 'Telegram',
                    email: 'Email'
                };
                const pName = labelMap[platform] || 'Social network';
                const pasteHint = copiedMode === 'image'
                    ? 'HD Poster copied to clipboard (press Cmd+V / Ctrl+V to paste directly) &amp; saved to downloads!'
                    : 'HD Poster saved to your downloads to attach!';
                if (feedbackEl) {
                    feedbackEl.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> ${pName} opened! ${pasteHint}</span>`;
                }
            }
            setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 9000);
        } catch (err) {
            console.error('Social share error:', err);
        } finally {
            if (btn) {
                btn.innerHTML = originalText;
            }
        }
    }

    async function copyPosterImageToClipboard(evt) {
        const btn = evt?.currentTarget;
        const originalText = btn ? btn.innerHTML : '';
        const feedbackEl = document.getElementById('flyerActionFeedback');
        const { shareText, filename } = buildFlyerSharePayload();
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Copying...';
        }
        try {
            const canvas = await renderFullPosterCanvas();
            const blob = await canvasToBlobAsync(canvas);
            const mode = await copyPosterToClipboard(blob, shareText);
            if (mode === 'image') {
                if (feedbackEl) {
                    feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-clipboard-check-fill me-1"></i> HD Poster image copied! Press Cmd+V (or Ctrl+V) inside WhatsApp, Instagram, Facebook, or X.</span>';
                }
            } else {
                triggerBlobDownload(blob, filename);
                if (feedbackEl) {
                    feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-download me-1"></i> Poster downloaded &amp; invitation caption copied to clipboard!</span>';
                }
            }
            setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 7000);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    async function copyPosterCaptionText(evt) {
        const btn = evt?.currentTarget;
        const originalText = btn ? btn.innerHTML : '';
        const feedbackEl = document.getElementById('flyerActionFeedback');
        const { shareText } = buildFlyerSharePayload();
        try {
            await navigator.clipboard.writeText(shareText);
            if (btn) btn.innerHTML = '<i class="bi bi-check2 text-success"></i> Copied!';
            if (feedbackEl) {
                feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Invitation caption copied to clipboard!</span>';
            }
            setTimeout(() => {
                if (btn) btn.innerHTML = originalText;
                if (feedbackEl) feedbackEl.innerHTML = '';
            }, 3500);
        } catch (_) {
            window.prompt('Copy your invitation caption:', shareText);
        }
    }

    async function sharePosterNativeOS(evt) {
        const btn = evt?.currentTarget;
        const originalText = btn ? btn.innerHTML : '';
        const feedbackEl = document.getElementById('flyerActionFeedback');
        const { shareTitle, shareText, filename } = buildFlyerSharePayload();

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Preparing...';
        }
        try {
            const canvas = await renderFullPosterCanvas();
            const blob = await canvasToBlobAsync(canvas);
            if (blob) {
                const file = new File([blob], filename, { type: 'image/png' });
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        title: shareTitle,
                        text: shareText,
                        files: [file]
                    });
                    if (feedbackEl) {
                        feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Shared via device share menu!</span>';
                        setTimeout(() => { if (feedbackEl) feedbackEl.innerHTML = ''; }, 6000);
                    }
                    return;
                }
            }
            if (navigator.share) {
                await navigator.share({ title: shareTitle, text: shareText });
                return;
            }
            await copyPosterToClipboard(blob, shareText);
            triggerBlobDownload(blob, filename);
            if (feedbackEl) {
                feedbackEl.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Poster downloaded &amp; copied to clipboard!</span>';
            }
        } catch (err) {
            if (err && err.name !== 'AbortError') {
                console.warn('Native share notice:', err);
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    async function shareFamFlyerWhatsApp(evt) {
        return sharePosterToSocial('whatsapp', evt);
    }

    function toggleMobileFlyerView() {
        var previewCol = document.getElementById('flyerPreviewColumn');
        var controlsCol = document.getElementById('flyerControlsColumn');
        var btnText = document.getElementById('mobileFlyerJumpText');
        var btnIcon = document.getElementById('mobileFlyerJumpIcon');
        if (!previewCol) return;
        var rect = previewCol.getBoundingClientRect();
        var isPreviewVisible = (rect.top <= 250 && rect.bottom >= 250);
        if (isPreviewVisible && controlsCol) {
            controlsCol.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (btnText) btnText.textContent = 'View Live Poster';
            if (btnIcon) btnIcon.className = 'bi bi-eye-fill me-1';
        } else {
            previewCol.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (btnText) btnText.textContent = 'Back to Controls';
            if (btnIcon) btnIcon.className = 'bi bi-pencil-fill me-1';
        }
    }
    window.toggleMobileFlyerView = toggleMobileFlyerView;

    window.addEventListener('scroll', function() {
        var previewCol = document.getElementById('flyerPreviewColumn');
        var btnText = document.getElementById('mobileFlyerJumpText');
        var btnIcon = document.getElementById('mobileFlyerJumpIcon');
        if (!previewCol || !btnText) return;
        var rect = previewCol.getBoundingClientRect();
        var isPreviewVisible = (rect.top <= 250 && rect.bottom >= 250);
        if (isPreviewVisible) {
            btnText.textContent = 'Back to Controls';
            if (btnIcon) btnIcon.className = 'bi bi-pencil-fill me-1';
        } else {
            btnText.textContent = 'View Live Poster';
            if (btnIcon) btnIcon.className = 'bi bi-eye-fill me-1';
        }
    }, { passive: true });

    // 🎮 FAMILY GAME SHOW
    let triviaQuestions = [];
    let currentTriviaIndex = 0;
    let triviaScore = 0;

    function insertExampleTriviaFacts() {
        const examples = [
            "Grandpa moved to London in 1968",
            "Uncle Tunde burnt the Christmas turkey in 2012",
            "Auntie Simi claims she invented Nigerian fried rice",
            "Maya won the school spelling bee with the word 'onomatopoeia'"
        ];
        document.getElementById('trivia-facts').value = examples.join('\n');
    }

    async function generateTriviaShow() {
        const famName = document.getElementById('trivia-family-name').value.trim();
        const category = document.getElementById('trivia-category').value;
        const factsRaw = document.getElementById('trivia-facts').value.split('\n').filter(f => f.trim() !== '');

        const btn = document.getElementById('btn-launch-trivia');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Preparing Family Game Show...';

        const alertEl = document.getElementById('trivia-posted-alert');
        if (alertEl) {
            alertEl.style.display = 'none';
        }

        try {
            const res = await fetch('/api/ai/generate-trivia', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    family_name: famName,
                    category: category,
                    family_facts: factsRaw,
                    question_count: 3
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Family Game Show';

            if (data.success && data.questions && data.questions.length > 0) {
                triviaQuestions = data.questions;
                currentTriviaIndex = 0;
                triviaScore = 0;
                document.getElementById('trivia-game-title').textContent = data.quiz_title || 'Family Game Show';
                document.getElementById('trivia-score').textContent = '0 pts';
                document.getElementById('trivia-empty-state').style.display = 'none';
                document.getElementById('trivia-game-arena').style.display = 'block';

                if (data.posted && data.post_url && alertEl) {
                    const linkEl = document.getElementById('trivia-post-feed-link');
                    if (linkEl) {
                        linkEl.href = data.post_url;
                    }
                    alertEl.style.display = 'flex';
                }

                renderTriviaQuestion();
            } else {
                alert(data.error || 'Failed to generate game show quiz.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Family Game Show';
            alert('Game Show error: ' + err.message);
        }
    }

    function renderTriviaQuestion() {
        const q = triviaQuestions[currentTriviaIndex];
        if (!q) return;

        document.getElementById('trivia-q-num').textContent = `Question ${currentTriviaIndex + 1} of ${triviaQuestions.length}`;
        document.getElementById('trivia-q-text').textContent = q.question;
        document.getElementById('trivia-feedback-box').style.display = 'none';
        document.getElementById('btn-next-trivia').style.display = 'none';

        const optBox = document.getElementById('trivia-options-box');
        optBox.innerHTML = '';

        q.options.forEach((optText, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-dark text-start p-3 rounded-3 fw-semibold trivia-opt-btn';
            btn.innerHTML = `<span class="badge bg-secondary me-2">${String.fromCharCode(65 + idx)}</span> ${optText}`;
            btn.onclick = () => selectTriviaAnswer(idx, q.correct_index, q.fun_fact);
            optBox.appendChild(btn);
        });
    }

    function selectTriviaAnswer(chosenIdx, correctIdx, funFact) {
        const buttons = document.querySelectorAll('.trivia-opt-btn');
        buttons.forEach((b, idx) => {
            b.disabled = true;
            if (idx === correctIdx) {
                b.className = 'btn btn-success text-start p-3 rounded-3 fw-bold';
            } else if (idx === chosenIdx) {
                b.className = 'btn btn-danger text-start p-3 rounded-3 fw-semibold';
            }
        });

        const feedback = document.getElementById('trivia-feedback-box');
        if (chosenIdx === correctIdx) {
            triviaScore += 100;
            document.getElementById('trivia-score').textContent = `${triviaScore} pts`;
            feedback.className = 'alert alert-success p-3 rounded-3 small fw-semibold mb-3';
            feedback.innerHTML = `🎉 <strong>Correct! (+100 pts)</strong> ${funFact}`;
        } else {
            feedback.className = 'alert alert-warning p-3 rounded-3 small fw-semibold mb-3';
            feedback.innerHTML = `❌ <strong>Not quite!</strong> ${funFact}`;
        }
        feedback.style.display = 'block';

        const nextBtn = document.getElementById('btn-next-trivia');
        if (currentTriviaIndex < triviaQuestions.length - 1) {
            nextBtn.innerHTML = 'Next Question <i class="bi bi-chevron-right ms-1"></i>';
            nextBtn.style.display = 'inline-block';
        } else {
            nextBtn.innerHTML = '🏁 Finish Game';
            nextBtn.style.display = 'inline-block';
        }
    }

    function nextTriviaQuestion() {
        if (currentTriviaIndex < triviaQuestions.length - 1) {
            currentTriviaIndex++;
            renderTriviaQuestion();
        } else {
            alert(`🏆 Game Finished! Your final family score is ${triviaScore} points!`);
            document.getElementById('trivia-game-arena').style.display = 'none';
            document.getElementById('trivia-empty-state').style.display = 'block';
        }
    }

    // =========================================================================
    // 🎭 TWO TRUTHS & A LIE (HERITAGE EDITION) CLIENT CONTROLLER
    // =========================================================================
    let selectedTwoTruthsOpt = null;
    let activeTwoTruthsGameId = @json($twoTruthsGame['id'] ?? null);

    function selectTwoTruthsOption(num) {
        @if(!empty($twoTruthsGame['has_voted']) || ($twoTruthsGame['status'] ?? '') === 'revealed')
            return;
        @endif

        selectedTwoTruthsOpt = num;
        const cards = document.querySelectorAll('.tt-statement-card');
        cards.forEach((card) => {
            const opt = parseInt(card.getAttribute('data-opt'), 10);
            const radio = card.querySelector('.tt-radio-select');
            if (opt === num) {
                card.classList.remove('border-light-subtle', 'bg-white');
                card.classList.add('border-danger', 'bg-danger-subtle', 'bg-opacity-10');
                if (radio) radio.checked = true;
            } else {
                card.classList.remove('border-danger', 'bg-danger-subtle', 'bg-opacity-10');
                card.classList.add('border-light-subtle', 'bg-white');
                if (radio) radio.checked = false;
            }
        });

        const btn = document.getElementById('btn-cast-tt-vote');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Spot Statement #${num} as the Lie`;
        }
    }

    async function castTwoTruthsVote(btn) {
        if (!selectedTwoTruthsOpt || !activeTwoTruthsGameId) {
            alert('Please select the statement card you believe is the lie.');
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Casting Guess...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    game_id: activeTwoTruthsGameId,
                    guess: selectedTwoTruthsOpt,
                    token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) {
                window.ScoreTenantSpinner.stop(btn);
            }

            if (data.status === 'success') {
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to register vote.');
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> Spot Statement #${selectedTwoTruthsOpt} as the Lie`;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Network error while casting vote: ' + err.message);
        }
    }

    async function revealTwoTruthsGame(btn, gameId) {
        if (!confirm('Are you sure you want to reveal the lie now? This will award points and archive verified truths to the Family Journal.')) {
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Unmasking Lie...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/reveal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    game_id: gameId,
                    token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);

            if (data.status === 'success') {
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to reveal game.');
                btn.disabled = false;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Error unmasking lie: ' + err.message);
        }
    }

    async function submitNewTwoTruthsGame(btn) {
        const title = document.getElementById('tt-new-title').value.trim();
        const st1 = document.getElementById('tt-new-st1').value.trim();
        const st2 = document.getElementById('tt-new-st2').value.trim();
        const st3 = document.getElementById('tt-new-st3').value.trim();
        const story1 = document.getElementById('tt-new-story1').value.trim();
        const story2 = document.getElementById('tt-new-story2').value.trim();
        const story3 = document.getElementById('tt-new-story3').value.trim();

        const lieChecked = document.querySelector('input[name="tt_new_lie_index"]:checked');
        if (!lieChecked) {
            alert('Please select which of the three statements is the lie.');
            return;
        }
        const lieIndex = parseInt(lieChecked.value, 10);

        if (!st1 || !st2 || !st3) {
            alert('All three statements are required.');
            return;
        }

        if (window.ScoreTenantSpinner?.start) {
            window.ScoreTenantSpinner.start(btn);
        } else {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Publishing...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || @json($_SESSION['token'] ?? '');

        try {
            const res = await fetch('/api/games/two-truths/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    title: title,
                    statement_1: st1,
                    statement_2: st2,
                    statement_3: st3,
                    lie_index: lieIndex,
                    reveal_story_1: story1,
                    reveal_story_2: story2,
                    reveal_story_3: story3,
                    token: csrfToken
                })
            });

            const data = await res.json();
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);

            if (data.status === 'success') {
                const modalEl = document.getElementById('modalCreateTwoTruths');
                if (modalEl && window.bootstrap?.Modal) {
                    const inst = window.bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                }
                triggerCelebrationConfetti();
                window.location.reload();
            } else {
                alert(data.message || 'Unable to publish challenge.');
                btn.disabled = false;
            }
        } catch (err) {
            if (window.ScoreTenantSpinner?.stop) window.ScoreTenantSpinner.stop(btn);
            btn.disabled = false;
            alert('Error creating game: ' + err.message);
        }
    }

    function shareTwoTruthsToWhatsApp() {
        const title = @json($twoTruthsGame['title'] ?? 'Two Truths & a Lie');
        const author = @json($twoTruthsGame['author_name'] ?? 'Family Member');
        const st1 = @json($twoTruthsGame['statement_1'] ?? '');
        const st2 = @json($twoTruthsGame['statement_2'] ?? '');
        const st3 = @json($twoTruthsGame['statement_3'] ?? '');
        const gameUrl = window.location.origin + '/member/family-ai#tab-trivia';

        const text = `🎭 *Family Challenge: Two Truths & a Lie!*\n\n`
            + `${author} has laid down a challenge:\n`
            + `1️⃣ ${st1}\n`
            + `2️⃣ ${st2}\n`
            + `3️⃣ ${st3}\n\n`
            + `Two are true life moments, but one is an elaborate lie! Can you spot it?\n`
            + `👉 Vote here on FamilyPlatform: ${gameUrl}`;

        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }

    function triggerCelebrationConfetti() {
        const colors = ['#dc2626', '#2563eb', '#16a34a', '#eab308', '#9333ea', '#ec4899'];
        for (let i = 0; i < 35; i++) {
            const p = document.createElement('div');
            const size = Math.random() * 9 + 5;
            p.style.position = 'fixed';
            p.style.zIndex = '99999';
            p.style.width = size + 'px';
            p.style.height = size + 'px';
            p.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            p.style.left = Math.random() * 100 + 'vw';
            p.style.top = '-10px';
            p.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            p.style.opacity = '0.9';
            p.style.pointerEvents = 'none';
            p.style.transform = `rotate(${Math.random() * 360}deg)`;
            p.style.transition = 'transform 3s cubic-bezier(.25,.46,.45,.94), top 3s cubic-bezier(.25,.46,.45,.94), opacity 3s ease';

            document.body.appendChild(p);

            setTimeout(() => {
                p.style.top = '105vh';
                p.style.transform = `rotate(${Math.random() * 720}deg) scale(0.6)`;
                p.style.opacity = '0';
            }, 50);

            setTimeout(() => {
                p.remove();
            }, 3200);
        }
    }
</script>

{{-- Modal: Create Two Truths & a Lie Challenge --}}
<div class="modal fade" id="modalCreateTwoTruths" tabindex="-1" aria-labelledby="modalCreateTwoTruthsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 mb-1 fw-bold small">
                        🎭 Family Heritage Ritual
                    </span>
                    <h4 class="modal-title fw-bold text-dark" id="modalCreateTwoTruthsLabel">Create a "Two Truths &amp; a Lie" Challenge</h4>
                    <p class="text-muted small mb-0">Share two authentic life memories or milestones, and craft one convincing lie. Can your family tell the difference?</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="min-height: 44px; min-width: 44px;"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="formCreateTwoTruths" onsubmit="event.preventDefault(); submitNewTwoTruthsGame(document.getElementById('btn-submit-two-truths'));">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Challenge Title</label>
                        <input type="text" id="tt-new-title" class="form-control rounded-3" placeholder="e.g. Uncle Tunde's 1980s London Adventures" value="Heritage Challenge: Can you spot the lie?" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 1</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_1" value="1" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_1" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st1" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 1..." required></textarea>
                                <input type="text" id="tt-new-story1" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 2</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_2" value="2" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_2" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st2" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 2..." required></textarea>
                                <input type="text" id="tt-new-story2" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card p-3 rounded-4 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small mb-0 text-dark">Statement 3</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tt_new_lie_index" id="lie_opt_3" value="3" required style="width: 20px; height: 20px; cursor: pointer;">
                                        <label class="form-check-label small fw-bold text-danger ms-1" for="lie_opt_3" style="cursor: pointer;">This is the Lie</label>
                                    </div>
                                </div>
                                <textarea id="tt-new-st3" class="form-control rounded-3 mb-2" rows="2" placeholder="Write statement 3..." required></textarea>
                                <input type="text" id="tt-new-story3" class="form-control form-control-sm rounded-3" placeholder="Backstory / revelation explanation (revealed after Sunday)">
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-3 border mb-3 small text-muted">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> <strong>Secret Anti-Cheat Gate:</strong> Your designated lie remains strictly hidden server-side until the reveal. The two true statements will automatically be immortalised in your official Family Journal.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal" style="min-height: 44px;">Cancel</button>
                        <button type="submit" id="btn-submit-two-truths" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="min-height: 44px;">
                            <i class="bi bi-broadcast"></i> Publish Challenge to Family Network
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
