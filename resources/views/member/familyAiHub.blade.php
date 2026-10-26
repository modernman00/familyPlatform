@extends('layouts.profileBase')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Great+Vibes&family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">
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
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">
    {{-- Hub Header --}}
    <div class="ai-hub-hero text-center mb-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white shadow-sm small fw-bold mb-3" style="color: #7b03fc;">
            <i class="bi bi-stars"></i> ✨ Studio AI Family Intelligence
        </div>
        <h1 class="h2 fw-bold text-dark mb-2">AI Family Hub</h1>
        <p class="text-muted mx-auto mb-0" style="max-width: 650px;">
            Your household companion for fair chore arbitration, 15-minute fridge meal ideas, illustrated family flyers, and Dynasty Trivia.
        </p>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-white rounded-4 shadow-sm mb-4 border" id="familyAiTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
                    id="tab-referee-btn" data-bs-toggle="pill" data-bs-target="#tab-referee" type="button" role="tab" style="min-height: 44px;">
                <i class="bi bi-hammer text-primary"></i> ⚖️ Judge Whiskers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2 d-inline-flex align-items-center justify-content-center gap-2 w-100" 
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
                <i class="bi bi-trophy-fill text-danger"></i> 👑 Dynasty Trivia
            </button>
        </li>
    </ul>

    {{-- Panes --}}
    <div class="tab-content" id="familyAiContent">

        {{-- ⚖️ TAB 1: DISPUTE REFEREE --}}
        <div class="tab-pane fade show active" id="tab-referee" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-balance text-primary me-2"></i> File a Dispute</h4>
                        <p class="text-muted small mb-4">Kids arguing over dishes or the remote? Enter both sides and let Judge Whiskers rule fairly with a playful compromise.</p>

                        <form id="dispute-form" onsubmit="handleArbitrate(event)">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">First Person (Party A)</label>
                                <input type="text" id="party-a-name" class="form-control rounded-3" value="{{ $userData['firstName'] ?? 'Liam' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Party A's Grievance</label>
                                <textarea id="party-a-claim" class="form-control rounded-3" rows="2" placeholder="e.g. I unloaded the dishwasher, Maya never wiped the counters!" required>I unloaded the dishwasher yesterday, it is Maya's turn today!</textarea>
                            </div>

                            <hr class="my-3">

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Second Person (Party B)</label>
                                <input type="text" id="party-b-name" class="form-control rounded-3" value="Maya" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Party B's Counter-Claim</label>
                                <textarea id="party-b-claim" class="form-control rounded-3" rows="2" placeholder="e.g. I was doing homework until 9pm!" required>I had football practice and homework, Liam was playing video games all evening!</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Dispute Category</label>
                                <select id="dispute-context" class="form-select rounded-3">
                                    <option value="household_chores">🧹 Household Chores &amp; Dishes</option>
                                    <option value="tv_remote">📺 TV Remote &amp; Screen Time</option>
                                    <option value="bathroom_turn">🚿 Morning Bathroom Schedule</option>
                                    <option value="last_snack">🍕 Who Gets the Last Slice/Snack</option>
                                </select>
                            </div>

                            <button type="submit" id="btn-arbitrate" class="btn btn-primary rounded-pill w-100 fw-bold py-2" style="background: linear-gradient(135deg, #7b03fc, #6366f1); border: none; min-height: 44px;">
                                <i class="bi bi-hammer me-1"></i> Adjudicate with Judge Whiskers
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Court Ruling</h4>
                            <span class="badge bg-purple text-white rounded-pill px-3 py-1" style="background: #7b03fc;">Official Verdict</span>
                        </div>

                        <div id="verdict-placeholder" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text display-3 d-block mb-3 text-secondary opacity-50"></i>
                            <span>Submit a dispute to hear the honorable Judge Whiskers' binding family decree.</span>
                        </div>

                        <div id="verdict-card" style="display: none;" class="animate__animated animate__fadeIn">
                            <div class="alert alert-purple bg-light border-purple p-4 rounded-4 mb-4">
                                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-award-fill text-warning me-2"></i> The Finding</h5>
                                <p id="verdict-box" class="lead text-dark mb-0 fs-6"></p>
                            </div>

                            <div class="card border border-light-subtle rounded-4 p-4 mb-4 bg-white shadow-xs">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-success me-2"></i> Action Plan</h6>
                                <div id="action-plan-list" class="d-flex flex-column gap-2"></div>
                            </div>

                            <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 rounded-4 p-3 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    <div>
                                        <div class="fw-bold text-dark small">Playful Penalty / Chore</div>
                                        <div id="penalty-box" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end fst-italic text-muted small" id="closing-quip"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🥦 TAB 2: FRIDGE MEAL PLANNER --}}
        <div class="tab-pane fade" id="tab-fridge" role="tabpanel">
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
                <p class="text-muted small mb-4">Dictate chore checklists, grocery items, or family diary notes effortlessly via Whisper AI.</p>

                <div class="d-flex justify-content-center mb-4">
                    <button type="button" id="fam-voice-btn" class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
                            onclick="toggleFamVoice()" 
                            style="width: 100px; height: 100px; background: linear-gradient(135deg, #7b03fc, #ec4899); border: none; color: white; font-size: 2.2rem; transition: transform 0.2s;">
                        <i class="bi bi-mic-fill" id="fam-mic-icon"></i>
                    </button>
                </div>

                <div id="fam-voice-status" class="small fw-semibold text-muted mb-4">Click to Start Recording</div>

                <div class="text-start">
                    <label class="form-label fw-semibold small">Dictated Notes</label>
                    <textarea id="fam-voice-output" class="form-control rounded-3" rows="4" placeholder="Your spoken notes will appear here..."></textarea>
                    <button type="button" class="btn btn-outline-primary rounded-pill btn-sm mt-2 px-3 fw-bold" onclick="copyFamVoice()">
                        <i class="bi bi-clipboard me-1"></i> Copy to Clipboard
                    </button>
                </div>
            </div>
        </div>

        {{-- 🎨 TAB 4: FAMILY FLYER STUDIO --}}
        <div class="tab-pane fade" id="tab-flyer" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fw-bold mb-0"><i class="bi bi-stars text-warning me-2"></i> Grand Gala Poster Studio</h4>
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small px-2 py-1">👑 3D Gold Luxury</span>
                        </div>
                        <p class="text-muted small mb-3">Create breathtaking, world-class commemorative event posters with 3D embossed gold typography, celebrant portrait integration, and twin celebration shields.</p>

                        {{-- Presets --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Master Celebration Templates</label>
                            <div class="d-flex gap-1 flex-wrap">
                                <button type="button" id="btnPresetMajor" class="btn btn-sm btn-dark rounded-pill py-1 px-3 small fw-bold" onclick="applyMajorPreset()">🎖️ Major @ 50 (Transatlantic)</button>
                                <button type="button" id="btnPresetAllWhite" class="btn btn-sm btn-outline-dark rounded-pill py-1 px-3 small fw-bold" onclick="applyAllWhitePreset()">🥂 All White Party (VIP Gala)</button>
                                <button type="button" id="btnPresetJubilee" class="btn btn-sm btn-light border rounded-pill py-1 px-2 small" onclick="applyJubileePreset()">👑 Golden Jubilee @ 50</button>
                                <button type="button" id="btnPresetHeritage" class="btn btn-sm btn-light border rounded-pill py-1 px-2 small" onclick="applyHeritagePreset()">🥁 African Heritage Fiesta</button>
                            </div>
                        </div>

                        {{-- MODE 1 CONTROLS: TRANSATLANTIC / COMMEMORATIVE GALA --}}
                        <div id="controlsTransatlantic">
                            {{-- Celebrant Photo Upload --}}
                            <div class="mb-3 p-3 rounded-3 bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold small mb-0"><i class="bi bi-person-bounding-box text-primary me-1"></i> Celebrant Portrait</label>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-warning-emphasis fw-bold" onclick="clearCelebrantPhoto()" id="btnClearPhoto" style="display:none;">Hide Photo</button>
                                </div>
                                <input type="file" id="fam-celebrant-file" class="form-control rounded-3 form-control-sm mb-2" accept="image/*" onchange="handleCelebrantPhoto(event)">
                                <div class="d-flex gap-2 align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2 small" onclick="useSampleCelebrant()">
                                        <i class="bi bi-shield-check text-success me-1"></i> Use Sample Officer Photo
                                    </button>
                                    <small class="text-muted">Or upload your celebrant's picture</small>
                                </div>
                            </div>

                            {{-- Typography & Headings --}}
                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Top Headline</label>
                                    <input type="text" id="flyer-headline" class="form-control rounded-3" value="SAVE THE DATE" oninput="updateFamFlyerPreview()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Intro Script</label>
                                    <input type="text" id="flyer-intro" class="form-control rounded-3" value="For the Grand Celebration of" oninput="updateFamFlyerPreview()">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Hero Title (3D Gold)</label>
                                <input type="text" id="flyer-hero-title" class="form-control rounded-3 fw-bold" value="MAJOR @ 50" oninput="updateFamFlyerPreview()">
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Subtitle / Region</label>
                                    <input type="text" id="flyer-subtitle" class="form-control rounded-3" value="IN NIGERIA &amp; US" oninput="updateFamFlyerPreview()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Emotional Motto / Script</label>
                                    <input type="text" id="flyer-motto" class="form-control rounded-3" value="Two Countries • One Celebration • Same Great Man" oninput="updateFamFlyerPreview()">
                                </div>
                            </div>

                            {{-- Destination Shield 1 --}}
                            <div class="p-3 mb-2 rounded-3 border" style="background: #f8fafc;">
                                <div class="fw-bold small text-dark mb-2"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Celebration Plaque 1 (Left Shield)</div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4">
                                        <input type="text" id="flyer-dest1-flag" class="form-control rounded-3 form-control-sm text-center" value="🇳🇬" placeholder="Flag/Icon" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="flyer-dest1-title" class="form-control rounded-3 form-control-sm" value="NIGERIA Celebration" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input type="text" id="flyer-dest1-date" class="form-control rounded-3 form-control-sm" value="DATE: AUGUST, 2027" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" id="flyer-dest1-venue" class="form-control rounded-3 form-control-sm" value="VENUE: COMING SOON" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                            </div>

                            {{-- Destination Shield 2 --}}
                            <div class="p-3 mb-3 rounded-3 border" style="background: #f8fafc;">
                                <div class="fw-bold small text-dark mb-2"><i class="bi bi-geo-alt-fill text-warning me-1"></i> Celebration Plaque 2 (Right Shield)</div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4">
                                        <input type="text" id="flyer-dest2-flag" class="form-control rounded-3 form-control-sm text-center" value="🇺🇸" placeholder="Flag/Icon" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" id="flyer-dest2-title" class="form-control rounded-3 form-control-sm" value="USA Celebration" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input type="text" id="flyer-dest2-date" class="form-control rounded-3 form-control-sm" value="DATE: SEPTEMBER, 2027" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" id="flyer-dest2-venue" class="form-control rounded-3 form-control-sm" value="VENUE: COMING SOON" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Bottom Gold Ribbon Text</label>
                                <input type="text" id="flyer-bottom-ribbon" class="form-control rounded-3" value="★ More Details to Follow ★" oninput="updateFamFlyerPreview()">
                            </div>
                        </div>

                        {{-- MODE 2 CONTROLS: ALL WHITE VIP NIGHTCLUB & GALA --}}
                        <div id="controlsAllWhite" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label fw-semibold small">Top Presenter / Host Line</label>
                                <input type="text" id="aw-presenter" class="form-control rounded-3 form-control-sm" value="SMYTHIE ENTERTAINMENT X LAMBERT MUSIC ENTERTAINMENT PRESENTS" oninput="updateFamFlyerPreview()">
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Hero Title (3D Pearl/Gold)</label>
                                    <input type="text" id="aw-hero-main" class="form-control rounded-3 fw-bold" value="ALL WHITE" oninput="updateFamFlyerPreview()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Gold Brush Script</label>
                                    <input type="text" id="aw-hero-script" class="form-control rounded-3" value="PARTY" oninput="updateFamFlyerPreview()">
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Location Pill Badge</label>
                                    <input type="text" id="aw-city-pill" class="form-control rounded-3 form-control-sm" value="IN SWINDON" oninput="updateFamFlyerPreview()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Gold Foil Dress Code Seal</label>
                                    <input type="text" id="aw-dress-code" class="form-control rounded-3 form-control-sm" value="All White • SMART &amp; STYLISH" oninput="updateFamFlyerPreview()">
                                </div>
                            </div>

                            <div class="p-3 mb-2 rounded-3 border" style="background: #f8fafc;">
                                <div class="fw-bold small text-dark mb-2"><i class="bi bi-calendar2-check text-warning me-1"></i> Event Date, Time &amp; Venue</div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-7">
                                        <input type="text" id="aw-date" class="form-control rounded-3 form-control-sm" value="FRIDAY 30TH MAY 2025" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-sm-5">
                                        <input type="text" id="aw-time" class="form-control rounded-3 form-control-sm" value="10PM - 3AM" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                                <input type="text" id="aw-venue" class="form-control rounded-3 form-control-sm" value="THE TICKET HALL, SWINDON SN1 5JA" oninput="updateFamFlyerPreview()">
                            </div>

                            <div class="p-3 mb-2 rounded-3 border" style="background: #f8fafc;">
                                <div class="fw-bold small text-dark mb-2"><i class="bi bi-stars text-warning me-1"></i> 3-Column VIP Experience Highlights</div>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="text" id="aw-feat1" class="form-control rounded-3 form-control-sm" value="🎧 TOP DJS" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" id="aw-feat2" class="form-control rounded-3 form-control-sm" value="🥂 VIP VIBES" oninput="updateFamFlyerPreview()">
                                    </div>
                                    <div class="col-4">
                                        <input type="text" id="aw-feat3" class="form-control rounded-3 form-control-sm" value="👥 NON-STOP ENERGY" oninput="updateFamFlyerPreview()">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">Tickets Platform</label>
                                    <input type="text" id="aw-tickets" class="form-control rounded-3 form-control-sm" value="TICKETS AVAILABLE ON: Fatsoma" oninput="updateFamFlyerPreview()">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold small">VIP Table Reservations</label>
                                    <input type="text" id="aw-rsvp" class="form-control rounded-3 form-control-sm" value="FOR TABLE RESERVATIONS: DM SMYTHIE +44 7575 930135" oninput="updateFamFlyerPreview()">
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btn-generate-fam-flyer" class="btn btn-primary rounded-pill w-100 fw-bold py-2 mt-1" onclick="generateBackdropArt()" style="background: linear-gradient(135deg, #d4af37, #9a741e); border: none; min-height: 44px; color: #000; font-weight: 800;">
                            <i class="bi bi-magic me-1"></i> Generate New AI Atmosphere Backdrop
                        </button>
                    </div>
                </div>

                {{-- Live Poster Preview Card --}}
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-start">
                                <h5 class="fw-bold mb-0">Live Commemorative Gala Poster</h5>
                                <small class="text-muted">Real-time luxury 3D gold &amp; pearl composite</small>
                            </div>
                            <span id="fam-flyer-badge" class="badge bg-dark text-warning border border-warning px-3 py-1 rounded-pill">👑 World-Class Masterpiece</span>
                        </div>

                        {{-- Visual Commemorative Gala Poster Container --}}
                        <div id="famFlyerCardPreview" class="mx-auto rounded-4 text-white shadow-2xl position-relative overflow-hidden" 
                             style="width: 100%; max-width: 440px; min-height: 640px; background: linear-gradient(160deg, #021a12 0%, #041424 50%, #020712 100%); border: 3px solid #d4af37; box-shadow: 0 25px 50px rgba(0,0,0,0.6);">
                            
                            {{-- Outer Gold Hairline Border --}}
                            <div class="position-absolute" style="top: 8px; left: 8px; right: 8px; bottom: 8px; border: 1px solid rgba(212, 175, 55, 0.45); border-radius: 12px; pointer-events: none; z-index: 10;"></div>

                            {{-- Corner Accents --}}
                            <div class="position-absolute" style="top: 14px; left: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                            <div class="position-absolute" style="top: 14px; right: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                            <div class="position-absolute" style="bottom: 14px; left: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>
                            <div class="position-absolute" style="bottom: 14px; right: 14px; color: #d4af37; z-index: 11; font-size: 0.6rem;">★</div>

                            {{-- Atmosphere / Celebrant Photo Layer (Mode 1) --}}
                            <div id="prevCelebrantWrap" class="position-absolute top-0 start-0 h-100" style="width: 55%; overflow: hidden; z-index: 1; pointer-events: none;">
                                <img id="prevCelebrantPhoto" src="/public/img/celebrant-sample.jpg" alt="Celebrant" 
                                     style="width: 100%; height: 100%; object-fit: cover; object-position: top center; mask-image: linear-gradient(to right, black 65%, transparent 100%); -webkit-mask-image: linear-gradient(to right, black 65%, transparent 100%);">
                            </div>

                            {{-- Background Golden Glow Layer --}}
                            <div class="position-absolute bottom-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 75% 20%, rgba(212, 175, 55, 0.22) 0%, transparent 65%); z-index: 2; pointer-events: none;"></div>

                            {{-- ========================================================== --}}
                            {{-- PREVIEW MODE 1: TRANSATLANTIC COMMEMORATIVE (MAJOR @ 50)   --}}
                            {{-- ========================================================== --}}
                            <div id="previewModeTransatlantic" class="position-relative d-flex flex-column justify-content-between h-100 p-3 pt-4" style="z-index: 5; min-height: 640px;">
                                
                                {{-- Header & Hero Title --}}
                                <div id="prevHeaderColumn" class="text-end pe-2" style="margin-left: 35%;">
                                    <div class="text-center mb-1">
                                        <div style="font-size: 1.2rem; color: #ffd700; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.8));">👑</div>
                                        <h3 id="prevFlyerHeadline" class="fw-extrabold mb-0" 
                                            style="font-family: 'Cinzel', serif; font-size: 1.45rem; letter-spacing: 2px; color: #fce79a; text-shadow: 0 3px 8px rgba(0,0,0,0.9), 0 0 12px rgba(212,175,55,0.4);">
                                            SAVE THE DATE
                                        </h3>
                                        <div id="prevFlyerIntro" class="mb-1" 
                                             style="font-family: 'Great Vibes', cursive; font-size: 1.15rem; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                            For the Grand Celebration of
                                        </div>
                                    </div>

                                    <h1 id="prevFlyerHeroTitle" class="fw-black mb-0 gold-embossed-text" 
                                        style="font-family: 'Cinzel', serif; font-size: 2.2rem; line-height: 1.05; letter-spacing: 1px; font-weight: 900; text-align: center;">
                                        MAJOR @ 50
                                    </h1>

                                    <div id="prevFlyerSubtitle" class="fw-bold text-center mt-1" 
                                         style="font-family: 'Montserrat', sans-serif; font-size: 0.85rem; letter-spacing: 2px; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                        IN NIGERIA &amp; US
                                    </div>

                                    <div class="gold-ribbon-bar my-1 mx-auto" style="width: 70%;"></div>

                                    <div id="prevFlyerMotto" class="text-center small fst-italic mb-2" 
                                         style="font-family: 'Great Vibes', cursive; font-size: 1.05rem; color: #e2e8f0; text-shadow: 0 2px 4px rgba(0,0,0,0.9); line-height: 1.2;">
                                        Two Countries • One Celebration • Same Great Man
                                    </div>
                                </div>

                                <div class="my-auto"></div>

                                {{-- Twin Destination Plaques --}}
                                <div class="row g-2 mb-2 px-1">
                                    {{-- Plaque 1 --}}
                                    <div class="col-6">
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
                                    <div class="col-6">
                                        <div class="gold-shield-card-blue p-2 text-center h-100 position-relative">
                                            <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill p-1 shadow" style="background: #081a3d; border: 1.5px solid #d4af37; font-size: 0.9rem;">
                                                <span id="prevDest2Flag">🇺🇸</span>
                                            </div>
                                            <div class="pt-2">
                                                <div id="prevDest2Title" class="fw-bold small" style="font-family: 'Cinzel', serif; color: #fce79a; font-size: 0.72rem; letter-spacing: 0.5px;">USA Celebration</div>
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

                                {{-- Bottom Gold Ribbon --}}
                                <div class="pt-1 text-center">
                                    <div class="gold-ribbon-bar mb-1"></div>
                                    <div id="prevFlyerRibbon" class="small fw-bold" style="font-family: 'Cinzel', serif; font-size: 0.75rem; letter-spacing: 1.5px; color: #fce79a; text-shadow: 0 1px 4px rgba(0,0,0,0.9);">
                                        ★ More Details to Follow ★
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================== --}}
                            {{-- PREVIEW MODE 2: ALL WHITE VIP NIGHTCLUB & GALA             --}}
                            {{-- ========================================================== --}}
                            <div id="previewModeAllWhite" class="position-relative flex-column justify-content-between h-100 p-3 pt-3" style="z-index: 5; min-height: 640px; display: none;">
                                
                                {{-- Top Presenter Line --}}
                                <div id="prevAwPresenter" class="text-center fw-bold px-2" style="font-family: 'Montserrat', sans-serif; font-size: 0.58rem; letter-spacing: 1.5px; color: #fce79a; text-shadow: 0 2px 4px rgba(0,0,0,0.9);">
                                    SMYTHIE ENTERTAINMENT X LAMBERT MUSIC ENTERTAINMENT PRESENTS
                                </div>

                                {{-- Dress Code Gold Seal + Hero Title Block --}}
                                <div class="position-relative my-1 pt-2">
                                    {{-- Floating Gold Foil Seal (Top Left) --}}
                                    <div class="dress-code-seal position-absolute" style="top: -4px; left: 4px; z-index: 8; transform: rotate(-8deg);">
                                        <span style="font-size: 0.45rem; letter-spacing: 1px; color: #fbbf24; font-weight: 800;">DRESS CODE:</span>
                                        <span id="prevAwDressCode" style="font-family: 'Great Vibes', cursive; font-size: 0.9rem; color: #ffffff; line-height: 1.05;">All White</span>
                                        <span style="font-size: 0.4rem; letter-spacing: 0.5px; color: #fce79a; font-weight: 700;">SMART &amp; STYLISH</span>
                                    </div>

                                    <div class="text-center mt-2">
                                        <div style="font-size: 1.35rem; color: #ffd700; filter: drop-shadow(0 2px 6px rgba(0,0,0,0.9));">👑</div>
                                        <h1 id="prevAwHeroMain" class="fw-black mb-0 pearl-embossed-text" 
                                            style="font-family: 'Cinzel', serif; font-size: 2.5rem; line-height: 0.95; letter-spacing: 2px; font-weight: 900;">
                                            ALL WHITE
                                        </h1>
                                        <div id="prevAwHeroScript" class="gold-embossed-text" 
                                             style="font-family: 'Great Vibes', cursive; font-size: 2.4rem; line-height: 0.9; margin-top: -4px;">
                                            PARTY
                                        </div>
                                        <div class="mt-2">
                                            <span id="prevAwCityPill" class="px-3 py-1 rounded-pill fw-bold" 
                                                  style="background: rgba(15, 23, 42, 0.85); border: 1.5px solid #d4af37; color: #fce79a; font-family: 'Cinzel', serif; font-size: 0.75rem; letter-spacing: 2px;">
                                                IN SWINDON
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="my-auto"></div>

                                {{-- Date, Time & Venue Glass Card --}}
                                <div class="vip-glass-pill p-2 mx-1 mb-2 text-center shadow">
                                    <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 0.72rem; font-weight: 800; color: #ffffff;">
                                        <span><i class="bi bi-calendar-event text-warning me-1"></i><span id="prevAwDate">FRIDAY 30TH MAY 2025</span></span>
                                        <span class="text-warning">•</span>
                                        <span style="color: #fce79a;"><span id="prevAwTime">10PM - 3AM</span></span>
                                    </div>
                                    <div class="gold-ribbon-bar my-1"></div>
                                    <div class="fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 0.68rem; color: #fde68a; letter-spacing: 0.5px;">
                                        <i class="bi bi-geo-alt-fill text-warning me-1"></i><span id="prevAwVenue">THE TICKET HALL, SWINDON SN1 5JA</span>
                                    </div>
                                </div>

                                {{-- 3-Column VIP Perks --}}
                                <div class="row g-1 mx-1 mb-2 text-center" style="font-family: 'Montserrat', sans-serif; font-size: 0.6rem; font-weight: 800; color: #fce79a;">
                                    <div class="col-4">
                                        <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat1">🎧 TOP DJS</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat2">🥂 VIP VIBES</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-1 rounded-3 border border-warning border-opacity-50 bg-dark bg-opacity-75" id="prevAwFeat3">👥 NON-STOP ENERGY</div>
                                    </div>
                                </div>

                                {{-- Ticket & Reservation Brush Banner --}}
                                <div class="ticket-brush-banner p-2 rounded-3 text-center">
                                    <div id="prevAwTickets" class="fw-extrabold mb-1" style="font-family: 'Montserrat', sans-serif; font-size: 0.7rem; color: #fbbf24; letter-spacing: 0.5px;">
                                        🎟️ TICKETS AVAILABLE ON: Fatsoma
                                    </div>
                                    <div id="prevAwRsvp" class="fw-bold" style="font-family: 'Montserrat', sans-serif; font-size: 0.62rem; color: #ffffff; letter-spacing: 0.5px;">
                                        📱 FOR TABLE RESERVATIONS: DM SMYTHIE +44 7575 930135
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm" onclick="downloadFamFlyer()" style="background: linear-gradient(135deg, #ffd700, #d4af37); border: none; min-height: 44px; color: #000;">
                                <i class="bi bi-download me-1"></i> Download Masterpiece Poster (PNG)
                            </button>
                            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" onclick="shareFamFlyerWhatsApp()" style="min-height: 44px;">
                                <i class="bi bi-whatsapp me-1"></i> Share to Family WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 👑 TAB 5: DYNASTY TRIVIA CLASH --}}
        <div class="tab-pane fade" id="tab-trivia" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="fw-bold mb-2"><i class="bi bi-trophy-fill text-danger me-2"></i> Dynasty Trivia Clash</h4>
                        <p class="text-muted small mb-3">Host an affectionate family game show! Test how well relatives know family milestones, childhood stories, and funny memories.</p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Family / Dynasty Name</label>
                            <input type="text" id="trivia-family-name" class="form-control rounded-3" value="The {{ $userData['lastName'] ?? 'Family' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Trivia Category</label>
                            <select id="trivia-category" class="form-select rounded-3">
                                <option value="family_folklore">📜 Family Folklore &amp; Heritage Milestones</option>
                                <option value="childhood_memories">🧸 Hilarious Childhood Mishaps &amp; Stories</option>
                                <option value="who_is_most_likely_to">🤔 Who Is Most Likely To...?</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Family Facts &amp; Lore (One per line)</label>
                            <textarea id="trivia-facts" class="form-control rounded-3" rows="4" placeholder="Enter key facts...">Grandpa moved to London in 1968
Uncle Tunde burnt the Christmas turkey in 2012
Auntie Simi claims she invented Nigerian fried rice
Maya won the school spelling bee with the word 'onomatopoeia'</textarea>
                            <small class="text-muted">The AI uses these memories to craft witty multiple-choice trivia.</small>
                        </div>

                        <button type="button" id="btn-launch-trivia" class="btn btn-danger rounded-pill w-100 fw-bold py-2 mt-2" onclick="generateTriviaShow()" style="min-height: 44px;">
                            <i class="bi bi-play-circle-fill me-1"></i> Start Dynasty Trivia Clash
                        </button>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <div id="trivia-empty-state" class="py-5">
                            <i class="bi bi-trophy display-3 text-warning opacity-75 d-block mb-3"></i>
                            <h4 class="fw-bold">Ready for Family Trivia Night?</h4>
                            <p class="text-muted small mx-auto" style="max-width: 420px;">
                                Enter your family's funny lore on the left and tap 'Start Dynasty Trivia Clash' to launch an interactive game show.
                            </p>
                        </div>

                        <div id="trivia-game-arena" style="display:none;" class="text-start">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <span class="badge bg-danger rounded-pill px-3 py-1 mb-1">Live Game Show</span>
                                    <h4 id="trivia-game-title" class="fw-bold mb-0 text-dark">Dynasty Trivia</h4>
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

<script>
    // ⚖️ DISPUTE ARBITRATION
    async function handleArbitrate(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-arbitrate');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Judge Whiskers is Deliberating...';

        try {
            const res = await fetch('/api/ai/arbitrate-dispute', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    party_a_name: document.getElementById('party-a-name').value,
                    party_a_claim: document.getElementById('party-a-claim').value,
                    party_b_name: document.getElementById('party-b-name').value,
                    party_b_claim: document.getElementById('party-b-claim').value,
                    context: document.getElementById('dispute-context').value
                })
            });
            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-hammer me-1"></i> Adjudicate with Judge Whiskers';

            if (data.success) {
                document.getElementById('verdict-placeholder').style.display = 'none';
                document.getElementById('verdict-box').textContent = data.verdict;

                const planList = document.getElementById('action-plan-list');
                planList.innerHTML = '';
                for (const [person, task] of Object.entries(data.compromise_action_plan || {})) {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center justify-content-between p-2 bg-light rounded-3';
                    row.innerHTML = `<span class="fw-bold small text-dark">${person}:</span><span class="small text-muted">${task}</span>`;
                    planList.appendChild(row);
                }

                document.getElementById('penalty-box').textContent = data.penalty_or_chore;
                document.getElementById('closing-quip').textContent = `"${data.closing_quip}" — Judge Whiskers`;
                document.getElementById('verdict-card').style.display = 'block';
            } else {
                alert(data.error || 'Arbitration failed. Please check server logs.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-hammer me-1"></i> Adjudicate with Judge Whiskers';
            alert('Request error: ' + err.message);
        }
    }

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

    // 🎤 VOICE NOTES
    let famRecorder = null;
    let famChunks = [];
    let isFamRecording = false;

    async function toggleFamVoice() {
        const btn = document.getElementById('fam-voice-btn');
        const status = document.getElementById('fam-voice-status');

        if (!isFamRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                famRecorder = new MediaRecorder(stream);
                famChunks = [];

                famRecorder.ondataavailable = e => { if (e.data.size > 0) famChunks.push(e.data); };
                famRecorder.onstop = async () => {
                    const blob = new Blob(famChunks, { type: 'audio/webm' });
                    status.textContent = 'Transcribing with Whisper AI...';

                    const fd = new FormData();
                    fd.append('audio', blob, 'voice.webm');

                    try {
                        const res = await fetch('/api/ai/transcribe-voice', { method: 'POST', body: fd });
                        const data = await res.json();
                        if (data.success && data.text) {
                            document.getElementById('fam-voice-output').value = data.text;
                            status.textContent = 'Transcription complete!';
                        } else {
                            status.textContent = data.error || 'Transcription failed.';
                        }
                    } catch (err) {
                        status.textContent = 'Upload error: ' + err.message;
                    }
                };

                famRecorder.start();
                isFamRecording = true;
                btn.style.transform = 'scale(1.1)';
                status.textContent = 'Recording... Click to Stop';
            } catch (err) {
                alert('Microphone access denied: ' + err.message);
            }
        } else {
            if (famRecorder && famRecorder.state !== 'inactive') {
                famRecorder.stop();
            }
            isFamRecording = false;
            btn.style.transform = 'scale(1)';
        }
    }

    function copyFamVoice() {
        const text = document.getElementById('fam-voice-output').value;
        navigator.clipboard.writeText(text).then(() => alert('Notes copied to clipboard!'));
    }

    // 🎨 FAMILY FLYER STUDIO (DUAL-MODE MASTERPIECE ENGINE)
    let currentPosterLayoutMode = 'transatlantic'; // 'transatlantic' | 'allwhite_vip'
    let currentFamFlyerImageData = null;
    let currentCelebrantPhotoData = '/public/img/celebrant-sample.jpg';
    let hasCelebrantPhoto = true;

    function highlightActivePresetBtn(activeId) {
        ['btnPresetMajor', 'btnPresetAllWhite', 'btnPresetJubilee', 'btnPresetHeritage'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === activeId) {
                el.className = 'btn btn-sm btn-dark rounded-pill py-1 px-3 small fw-bold';
            } else {
                el.className = 'btn btn-sm btn-light border rounded-pill py-1 px-2 small';
            }
        });
    }

    function setPosterMode(mode) {
        currentPosterLayoutMode = mode;
        const ctrlTrans = document.getElementById('controlsTransatlantic');
        const ctrlWhite = document.getElementById('controlsAllWhite');
        const prevTrans = document.getElementById('previewModeTransatlantic');
        const prevWhite = document.getElementById('previewModeAllWhite');
        const celWrap = document.getElementById('prevCelebrantWrap');
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
            if (celWrap) celWrap.style.display = 'none';
            if (card && !currentFamFlyerImageData) {
                card.style.background = 'radial-gradient(circle at 50% 28%, #473414 0%, #171209 55%, #070604 100%)';
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
            if (celWrap) celWrap.style.display = hasCelebrantPhoto ? 'block' : 'none';
            if (card && !currentFamFlyerImageData) {
                card.style.background = 'linear-gradient(160deg, #021a12 0%, #041424 50%, #020712 100%)';
            }
        }
        updateFamFlyerPreview();
    }

    function applyMajorPreset() {
        highlightActivePresetBtn('btnPresetMajor');
        currentFamFlyerImageData = null;
        useSampleCelebrant();
        document.getElementById('flyer-headline').value = 'SAVE THE DATE';
        document.getElementById('flyer-intro').value = 'For the Grand Celebration of';
        document.getElementById('flyer-hero-title').value = 'MAJOR @ 50';
        document.getElementById('flyer-subtitle').value = 'IN NIGERIA & US';
        document.getElementById('flyer-motto').value = 'Two Countries • One Celebration • Same Great Man';
        document.getElementById('flyer-dest1-flag').value = '🇳🇬';
        document.getElementById('flyer-dest1-title').value = 'NIGERIA Celebration';
        document.getElementById('flyer-dest1-date').value = 'DATE: AUGUST, 2027';
        document.getElementById('flyer-dest1-venue').value = 'VENUE: COMING SOON';
        document.getElementById('flyer-dest2-flag').value = '🇺🇸';
        document.getElementById('flyer-dest2-title').value = 'USA Celebration';
        document.getElementById('flyer-dest2-date').value = 'DATE: SEPTEMBER, 2027';
        document.getElementById('flyer-dest2-venue').value = 'VENUE: COMING SOON';
        document.getElementById('flyer-bottom-ribbon').value = '★ More Details to Follow ★';
        setPosterMode('transatlantic');
    }

    function applyAllWhitePreset() {
        highlightActivePresetBtn('btnPresetAllWhite');
        currentFamFlyerImageData = null;
        document.getElementById('aw-presenter').value = 'SMYTHIE ENTERTAINMENT X LAMBERT MUSIC ENTERTAINMENT PRESENTS';
        document.getElementById('aw-hero-main').value = 'ALL WHITE';
        document.getElementById('aw-hero-script').value = 'PARTY';
        document.getElementById('aw-city-pill').value = 'IN SWINDON';
        document.getElementById('aw-dress-code').value = 'All White';
        document.getElementById('aw-date').value = 'FRIDAY 30TH MAY 2025';
        document.getElementById('aw-time').value = '10PM - 3AM';
        document.getElementById('aw-venue').value = 'THE TICKET HALL, SWINDON SN1 5JA';
        document.getElementById('aw-feat1').value = '🎧 TOP DJS';
        document.getElementById('aw-feat2').value = '🥂 VIP VIBES';
        document.getElementById('aw-feat3').value = '👥 NON-STOP ENERGY';
        document.getElementById('aw-tickets').value = 'TICKETS AVAILABLE ON: Fatsoma';
        document.getElementById('aw-rsvp').value = 'FOR TABLE RESERVATIONS: DM SMYTHIE +44 7575 930135';
        setPosterMode('allwhite_vip');
    }

    function applyJubileePreset() {
        highlightActivePresetBtn('btnPresetJubilee');
        currentFamFlyerImageData = null;
        clearCelebrantPhoto();
        document.getElementById('flyer-headline').value = 'ROYAL INVITATION';
        document.getElementById('flyer-intro').value = 'Honouring 50 Glorious Years of';
        document.getElementById('flyer-hero-title').value = 'GOLDEN JUBILEE';
        document.getElementById('flyer-subtitle').value = 'LONDON & LAGOS';
        document.getElementById('flyer-motto').value = 'A Legacy of Grace, Wisdom & Family Unity';
        document.getElementById('flyer-dest1-flag').value = '🇬🇧';
        document.getElementById('flyer-dest1-title').value = 'LONDON Thanksgiving';
        document.getElementById('flyer-dest1-date').value = 'DATE: OCTOBER, 2026';
        document.getElementById('flyer-dest1-venue').value = 'VENUE: THE DORCHESTER';
        document.getElementById('flyer-dest2-flag').value = '🇳🇬';
        document.getElementById('flyer-dest2-title').value = 'LAGOS Grand Reception';
        document.getElementById('flyer-dest2-date').value = 'DATE: DECEMBER, 2026';
        document.getElementById('flyer-dest2-venue').value = 'VENUE: EKO HOTEL & SUITES';
        document.getElementById('flyer-bottom-ribbon').value = '★ Formal Attire • Gold & Champagne ★';
        setPosterMode('transatlantic');
    }

    function applyHeritagePreset() {
        highlightActivePresetBtn('btnPresetHeritage');
        currentFamFlyerImageData = null;
        clearCelebrantPhoto();
        document.getElementById('flyer-headline').value = 'ANNUAL HOMECOMING';
        document.getElementById('flyer-intro').value = 'You Are Cordially Invited to the';
        document.getElementById('flyer-hero-title').value = 'DYNASTY FIESTA';
        document.getElementById('flyer-subtitle').value = 'HERITAGE & CULTURE GALA';
        document.getElementById('flyer-motto').value = 'Celebrating Our Roots • Building Our Future';
        document.getElementById('flyer-dest1-flag').value = '🥁';
        document.getElementById('flyer-dest1-title').value = 'CULTURAL CARNIVAL';
        document.getElementById('flyer-dest1-date').value = 'DATE: 24 AUGUST, 2026';
        document.getElementById('flyer-dest1-venue').value = 'VENUE: GRAND PAVILION';
        document.getElementById('flyer-dest2-flag').value = '🥂';
        document.getElementById('flyer-dest2-title').value = 'EVENING BANQUET';
        document.getElementById('flyer-dest2-date').value = 'TIME: 6:00 PM TILL LATE';
        document.getElementById('flyer-dest2-venue').value = 'DRESS: ROYAL TRADITIONAL';
        document.getElementById('flyer-bottom-ribbon').value = '★ RSVP via Family Platform ★';
        setPosterMode('transatlantic');
    }

    function handleCelebrantPhoto(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            currentCelebrantPhotoData = e.target.result;
            hasCelebrantPhoto = true;
            const img = document.getElementById('prevCelebrantPhoto');
            const wrap = document.getElementById('prevCelebrantWrap');
            const hdr = document.getElementById('prevHeaderColumn');
            const btnClear = document.getElementById('btnClearPhoto');
            if (img) img.src = currentCelebrantPhotoData;
            if (wrap) wrap.style.display = 'block';
            if (hdr) hdr.style.marginLeft = '35%';
            if (btnClear) btnClear.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
    }

    function useSampleCelebrant() {
        currentCelebrantPhotoData = '/public/img/celebrant-sample.jpg';
        hasCelebrantPhoto = true;
        const img = document.getElementById('prevCelebrantPhoto');
        const wrap = document.getElementById('prevCelebrantWrap');
        const hdr = document.getElementById('prevHeaderColumn');
        const btnClear = document.getElementById('btnClearPhoto');
        if (img) img.src = currentCelebrantPhotoData;
        if (wrap && currentPosterLayoutMode === 'transatlantic') wrap.style.display = 'block';
        if (hdr) hdr.style.marginLeft = '35%';
        if (btnClear) btnClear.style.display = 'inline-block';
    }

    function clearCelebrantPhoto() {
        hasCelebrantPhoto = false;
        const wrap = document.getElementById('prevCelebrantWrap');
        const hdr = document.getElementById('prevHeaderColumn');
        const btnClear = document.getElementById('btnClearPhoto');
        if (wrap) wrap.style.display = 'none';
        if (hdr) hdr.style.marginLeft = '0';
        if (btnClear) btnClear.style.display = 'none';
    }

    function updateFamFlyerPreview() {
        if (currentPosterLayoutMode === 'allwhite_vip') {
            const setTxt = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
            setTxt('prevAwPresenter', document.getElementById('aw-presenter')?.value || '');
            setTxt('prevAwHeroMain', document.getElementById('aw-hero-main')?.value || 'ALL WHITE');
            setTxt('prevAwHeroScript', document.getElementById('aw-hero-script')?.value || 'PARTY');
            setTxt('prevAwCityPill', document.getElementById('aw-city-pill')?.value || 'IN SWINDON');
            setTxt('prevAwDressCode', (document.getElementById('aw-dress-code')?.value || 'All White').split('•')[0].trim());
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
            setTxt('prevFlyerHeroTitle', document.getElementById('flyer-hero-title')?.value || 'MAJOR @ 50');
            setTxt('prevFlyerSubtitle', document.getElementById('flyer-subtitle')?.value || '');
            setTxt('prevFlyerMotto', document.getElementById('flyer-motto')?.value || '');
            setTxt('prevDest1Flag', document.getElementById('flyer-dest1-flag')?.value || '🇳🇬');
            setTxt('prevDest1Title', document.getElementById('flyer-dest1-title')?.value || '');
            setTxt('prevDest1Date', document.getElementById('flyer-dest1-date')?.value || '');
            setTxt('prevDest1Venue', document.getElementById('flyer-dest1-venue')?.value || '');
            setTxt('prevDest2Flag', document.getElementById('flyer-dest2-flag')?.value || '🇺🇸');
            setTxt('prevDest2Title', document.getElementById('flyer-dest2-title')?.value || '');
            setTxt('prevDest2Date', document.getElementById('flyer-dest2-date')?.value || '');
            setTxt('prevDest2Venue', document.getElementById('flyer-dest2-venue')?.value || '');
            setTxt('prevFlyerRibbon', document.getElementById('flyer-bottom-ribbon')?.value || '');
        }
    }

    async function generateBackdropArt() {
        const btn = document.getElementById('btn-generate-fam-flyer');
        const prompt = currentPosterLayoutMode === 'allwhite_vip'
            ? 'Luxury VIP white and gold nightclub gala celebration, champagne sparkles, golden balloons and bokeh lights'
            : 'Luxury emerald and navy royal gala evening skyline with warm golden firework sparkles';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating Luxury Backdrop...';
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
                btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generate New AI Atmosphere Backdrop';
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
                    badge.className = 'badge bg-success text-white px-3 py-1 rounded-pill';
                    badge.textContent = '✨ 1080p Editorial Backdrop Applied';
                }
            }
        } catch (err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic me-1"></i> Generate New AI Atmosphere Backdrop';
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
     * Composites a world-class 1200x1600px print-ready poster for either Transatlantic Gala or All White VIP Party.
     */
    async function renderFullPosterCanvas() {
        const canvas = document.createElement('canvas');
        const width = 1200;
        const height = 1600;
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        if (!ctx) throw new Error('Canvas context unavailable');

        // 1. Base Luxury Gradient Background
        if (currentPosterLayoutMode === 'allwhite_vip') {
            const bgGrad = ctx.createRadialGradient(width / 2, 420, 80, width / 2, 800, 1100);
            bgGrad.addColorStop(0, '#4a3614');
            bgGrad.addColorStop(0.5, '#191308');
            bgGrad.addColorStop(1, '#060503');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);
        } else {
            const bgGrad = ctx.createLinearGradient(0, 0, width, height);
            bgGrad.addColorStop(0, '#032217');
            bgGrad.addColorStop(0.5, '#05162b');
            bgGrad.addColorStop(1, '#020813');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);
        }

        // Optional AI Backdrop Overlay
        if (currentFamFlyerImageData) {
            const bgImg = await loadCanvasImage(currentFamFlyerImageData);
            if (bgImg) {
                ctx.save();
                ctx.globalAlpha = 0.28;
                ctx.drawImage(bgImg, 0, 0, width, height);
                ctx.restore();
            }
        }

        // Golden Bokeh / Sparkle Particles
        ctx.save();
        const bokehCoords = [
            [180, 220, 45], [1040, 180, 65], [920, 420, 35], [240, 820, 50],
            [1020, 860, 55], [600, 190, 40], [150, 1280, 45], [1060, 1320, 50]
        ];
        bokehCoords.forEach(([bx, by, br]) => {
            const g = ctx.createRadialGradient(bx, by, 2, bx, by, br);
            g.addColorStop(0, 'rgba(255, 215, 0, 0.35)');
            g.addColorStop(1, 'rgba(255, 215, 0, 0)');
            ctx.fillStyle = g;
            ctx.beginPath();
            ctx.arc(bx, by, br, 0, Math.PI * 2);
            ctx.fill();
        });
        ctx.restore();

        // 2. Ornate Double Gold Borders
        ctx.save();
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 6;
        ctx.strokeRect(32, 32, width - 64, height - 64);
        ctx.strokeStyle = 'rgba(252, 231, 154, 0.45)';
        ctx.lineWidth = 2;
        ctx.strokeRect(48, 48, width - 96, height - 96);
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

        if (currentPosterLayoutMode === 'allwhite_vip') {
            // =========================================================================
            // CANVAS RENDER MODE 2: ALL WHITE VIP NIGHTCLUB & GALA
            // =========================================================================
            const presenter = (document.getElementById('aw-presenter')?.value || '').toUpperCase();
            const heroMain = (document.getElementById('aw-hero-main')?.value || 'ALL WHITE').toUpperCase();
            const heroScript = document.getElementById('aw-hero-script')?.value || 'PARTY';
            const cityPill = (document.getElementById('aw-city-pill')?.value || 'IN SWINDON').toUpperCase();
            const dressCode = document.getElementById('aw-dress-code')?.value || 'All White';
            const dateStr = (document.getElementById('aw-date')?.value || 'FRIDAY 30TH MAY 2025').toUpperCase();
            const timeStr = (document.getElementById('aw-time')?.value || '10PM - 3AM').toUpperCase();
            const venueStr = (document.getElementById('aw-venue')?.value || 'THE TICKET HALL, SWINDON SN1 5JA').toUpperCase();
            const feat1 = (document.getElementById('aw-feat1')?.value || '🎧 TOP DJS').toUpperCase();
            const feat2 = (document.getElementById('aw-feat2')?.value || '🥂 VIP VIBES').toUpperCase();
            const feat3 = (document.getElementById('aw-feat3')?.value || '👥 NON-STOP ENERGY').toUpperCase();
            const tickets = document.getElementById('aw-tickets')?.value || 'TICKETS AVAILABLE ON: Fatsoma';
            const rsvp = document.getElementById('aw-rsvp')?.value || 'FOR TABLE RESERVATIONS: DM SMYTHIE +44 7575 930135';

            // Top Presenter Line
            ctx.save();
            ctx.textAlign = 'center';
            ctx.font = '700 22px "Montserrat", sans-serif';
            ctx.fillStyle = '#fce79a';
            ctx.fillText(presenter, width / 2, 115);
            ctx.restore();

            // Circular Gold Foil Dress Code Seal (Top Left)
            ctx.save();
            const sealX = 210;
            const sealY = 270;
            ctx.beginPath();
            ctx.arc(sealX, sealY, 105, 0, Math.PI * 2);
            ctx.fillStyle = '#12100c';
            ctx.fill();
            ctx.lineWidth = 4;
            ctx.strokeStyle = '#ffd700';
            ctx.stroke();

            ctx.textAlign = 'center';
            ctx.font = '800 18px "Montserrat", sans-serif';
            ctx.fillStyle = '#fbbf24';
            ctx.fillText('DRESS CODE:', sealX, sealY - 28);
            ctx.font = 'italic 44px "Great Vibes", Georgia, serif';
            ctx.fillStyle = '#ffffff';
            ctx.fillText(dressCode.split('•')[0].trim(), sealX, sealY + 18);
            ctx.font = '700 15px "Montserrat", sans-serif';
            ctx.fillStyle = '#fce79a';
            ctx.fillText('SMART & STYLISH', sealX, sealY + 54);
            ctx.restore();

            // Crown + 3D Pearl/Gold Hero Title
            ctx.save();
            ctx.textAlign = 'center';
            ctx.font = '64px serif';
            ctx.fillText('👑', width / 2, 235);

            ctx.shadowColor = 'rgba(0, 0, 0, 0.92)';
            ctx.shadowBlur = 24;
            ctx.shadowOffsetY = 8;
            ctx.font = '900 132px "Cinzel", Georgia, serif';
            const pearlGrad = ctx.createLinearGradient(0, 260, 0, 390);
            pearlGrad.addColorStop(0, '#ffffff');
            pearlGrad.addColorStop(0.5, '#f8fafc');
            pearlGrad.addColorStop(1, '#cbd5e1');
            ctx.fillStyle = pearlGrad;
            ctx.fillText(heroMain, width / 2, 370);

            // Golden Brush Script "PARTY"
            ctx.font = 'italic 128px "Great Vibes", "Cinzel", Georgia, serif';
            ctx.fillStyle = makeGoldGradient(400, 530);
            ctx.fillText(heroScript, width / 2, 505);

            // Location Pill Badge
            const pillW = 360;
            const pillH = 64;
            ctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
            ctx.beginPath();
            ctx.roundRect((width - pillW) / 2, 550, pillW, pillH, 32);
            ctx.fill();
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#d4af37';
            ctx.stroke();
            ctx.font = '800 30px "Cinzel", Georgia, serif';
            ctx.fillStyle = '#fce79a';
            ctx.fillText(cityPill, width / 2, 593);
            ctx.restore();

            // Date, Time & Venue Glass Card
            ctx.save();
            const cardW = 980;
            const cardH = 220;
            const cardX = (width - cardW) / 2;
            const cardY = 880;
            ctx.fillStyle = 'rgba(12, 18, 32, 0.86)';
            ctx.beginPath();
            ctx.roundRect(cardX, cardY, cardW, cardH, 28);
            ctx.fill();
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#d4af37';
            ctx.stroke();

            ctx.textAlign = 'center';
            ctx.font = '800 36px "Montserrat", sans-serif';
            ctx.fillStyle = '#ffffff';
            ctx.fillText(`📅 ${dateStr}   •   ⏰ ${timeStr}`, width / 2, cardY + 85);

            ctx.strokeStyle = 'rgba(212, 175, 55, 0.45)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(cardX + 80, cardY + 120);
            ctx.lineTo(cardX + cardW - 80, cardY + 120);
            ctx.stroke();

            ctx.font = '700 32px "Montserrat", sans-serif';
            ctx.fillStyle = '#fce79a';
            ctx.fillText(`📍 ${venueStr}`, width / 2, cardY + 178);
            ctx.restore();

            // 3-Column VIP Perks Row
            ctx.save();
            const perks = [feat1, feat2, feat3];
            const colW = 310;
            const gap = 25;
            const startX = (width - (colW * 3 + gap * 2)) / 2;
            const perkY = 1150;
            perks.forEach((pText, idx) => {
                const px = startX + idx * (colW + gap);
                ctx.fillStyle = 'rgba(15, 20, 28, 0.9)';
                ctx.beginPath();
                ctx.roundRect(px, perkY, colW, 74, 18);
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'rgba(212, 175, 55, 0.7)';
                ctx.stroke();

                ctx.textAlign = 'center';
                ctx.font = '800 24px "Montserrat", sans-serif';
                ctx.fillStyle = '#fce79a';
                ctx.fillText(pText, px + colW / 2, perkY + 46);
            });
            ctx.restore();

            // Bottom Brush Ticket & Reservation Banner
            ctx.save();
            const banY = 1290;
            const banH = 190;
            ctx.fillStyle = 'rgba(8, 8, 12, 0.95)';
            ctx.fillRect(60, banY, width - 120, banH);
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#d4af37';
            ctx.strokeRect(60, banY, width - 120, banH);

            ctx.textAlign = 'center';
            ctx.font = '800 34px "Montserrat", sans-serif';
            ctx.fillStyle = '#fbbf24';
            ctx.fillText('🎟️ ' + tickets, width / 2, banY + 75);

            ctx.font = '700 28px "Montserrat", sans-serif';
            ctx.fillStyle = '#ffffff';
            ctx.fillText('📱 ' + rsvp, width / 2, banY + 140);
            ctx.restore();

            return canvas;
        }

        // =========================================================================
        // CANVAS RENDER MODE 1: TRANSATLANTIC COMMEMORATIVE GALA (MAJOR @ 50)
        // =========================================================================
        if (hasCelebrantPhoto && currentCelebrantPhotoData) {
            const celImg = await loadCanvasImage(currentCelebrantPhotoData);
            if (celImg) {
                ctx.save();
                const targetW = 620;
                const targetH = 1050;
                ctx.drawImage(celImg, 48, 60, targetW, targetH);

                // Smooth horizontal & bottom vignette blend
                const fadeRight = ctx.createLinearGradient(420, 0, 668, 0);
                fadeRight.addColorStop(0, 'rgba(4, 20, 36, 0)');
                fadeRight.addColorStop(1, '#041424');
                ctx.fillStyle = fadeRight;
                ctx.fillRect(420, 60, 260, targetH);

                const fadeBottom = ctx.createLinearGradient(0, 820, 0, 1110);
                fadeBottom.addColorStop(0, 'rgba(3, 12, 24, 0)');
                fadeBottom.addColorStop(1, '#030c18');
                ctx.fillStyle = fadeBottom;
                ctx.fillRect(48, 820, targetW, 300);
                ctx.restore();
            }
        }

        const headline = (document.getElementById('flyer-headline')?.value || 'SAVE THE DATE').toUpperCase();
        const intro = document.getElementById('flyer-intro')?.value || 'For the Grand Celebration of';
        const heroTitle = (document.getElementById('flyer-hero-title')?.value || 'MAJOR @ 50').toUpperCase();
        const subtitle = (document.getElementById('flyer-subtitle')?.value || 'IN NIGERIA & US').toUpperCase();
        const motto = document.getElementById('flyer-motto')?.value || 'Two Countries • One Celebration • Same Great Man';
        const d1Flag = document.getElementById('flyer-dest1-flag')?.value || '🇳🇬';
        const d1Title = document.getElementById('flyer-dest1-title')?.value || 'NIGERIA Celebration';
        const d1Date = document.getElementById('flyer-dest1-date')?.value || 'DATE: AUGUST, 2027';
        const d1Venue = document.getElementById('flyer-dest1-venue')?.value || 'VENUE: COMING SOON';
        const d2Flag = document.getElementById('flyer-dest2-flag')?.value || '🇺🇸';
        const d2Title = document.getElementById('flyer-dest2-title')?.value || 'USA Celebration';
        const d2Date = document.getElementById('flyer-dest2-date')?.value || 'DATE: SEPTEMBER, 2027';
        const d2Venue = document.getElementById('flyer-dest2-venue')?.value || 'VENUE: COMING SOON';
        const ribbon = document.getElementById('flyer-bottom-ribbon')?.value || '★ More Details to Follow ★';

        const centerX = hasCelebrantPhoto ? 780 : width / 2;

        // Crown + Top Headline
        ctx.save();
        ctx.textAlign = 'center';
        ctx.font = '54px serif';
        ctx.fillText('👑', centerX, 175);

        ctx.shadowColor = 'rgba(0,0,0,0.9)';
        ctx.shadowBlur = 18;
        ctx.font = '800 54px "Cinzel", Georgia, serif';
        ctx.fillStyle = makeGoldGradient(190, 250);
        ctx.fillText(headline, centerX, 240);

        // Intro Cursive Script
        ctx.font = 'italic 44px "Great Vibes", Georgia, serif';
        ctx.fillStyle = '#ffffff';
        ctx.fillText(intro, centerX, 305);

        // Massive 3D Gold Hero Title (e.g. MAJOR @ 50)
        ctx.font = '900 96px "Cinzel", Georgia, serif';
        ctx.fillStyle = makeGoldGradient(330, 440);
        ctx.fillText(heroTitle, centerX, 415);

        // Region Subtitle
        ctx.font = '800 36px "Montserrat", sans-serif';
        ctx.fillStyle = '#ffffff';
        ctx.fillText(subtitle, centerX, 480);

        // Gold Divider Line
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(centerX - 220, 510);
        ctx.lineTo(centerX + 220, 510);
        ctx.stroke();

        // Emotional Motto
        ctx.font = 'italic 38px "Great Vibes", Georgia, serif';
        ctx.fillStyle = '#fce79a';
        ctx.fillText(motto, centerX, 565);
        ctx.restore();

        // Twin Destination Shield Plaques
        const drawShieldPlaque = (x, y, w, h, bgTop, bgBot, flag, title, dateTxt, venueTxt) => {
            ctx.save();
            const g = ctx.createLinearGradient(0, y, 0, y + h);
            g.addColorStop(0, bgTop);
            g.addColorStop(1, bgBot);
            ctx.fillStyle = g;
            ctx.beginPath();
            ctx.roundRect(x, y, w, h, 32);
            ctx.fill();
            ctx.lineWidth = 4;
            ctx.strokeStyle = '#d4af37';
            ctx.stroke();

            // Flag Circle Badge
            ctx.beginPath();
            ctx.arc(x + w / 2, y, 42, 0, Math.PI * 2);
            ctx.fillStyle = bgBot;
            ctx.fill();
            ctx.lineWidth = 3;
            ctx.strokeStyle = '#ffd700';
            ctx.stroke();

            ctx.textAlign = 'center';
            ctx.font = '40px serif';
            ctx.fillText(flag, x + w / 2, y + 14);

            // Plaque Title
            ctx.font = '800 32px "Cinzel", Georgia, serif';
            ctx.fillStyle = '#fce79a';
            ctx.fillText(title, x + w / 2, y + 95);

            // Divider
            ctx.strokeStyle = 'rgba(212, 175, 55, 0.35)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(x + 40, y + 120);
            ctx.lineTo(x + w - 40, y + 120);
            ctx.stroke();

            // Date & Venue (Warm Gold & Champagne — ZERO RED FONT)
            ctx.textAlign = 'left';
            ctx.font = '700 26px "Montserrat", sans-serif';
            ctx.fillStyle = '#fbbf24';
            ctx.fillText('📅  ' + dateTxt, x + 45, y + 175);

            ctx.fillStyle = '#fde68a';
            ctx.fillText('📍  ' + venueTxt, x + 45, y + 235);
            ctx.restore();
        };

        const shieldW = 500;
        const shieldH = 290;
        const shieldY = 1080;
        drawShieldPlaque(76, shieldY, shieldW, shieldH, 'rgba(6, 38, 26, 0.94)', 'rgba(2, 16, 11, 0.98)', d1Flag, d1Title, d1Date, d1Venue);
        drawShieldPlaque(624, shieldY, shieldW, shieldH, 'rgba(8, 24, 56, 0.94)', 'rgba(3, 10, 26, 0.98)', d2Flag, d2Title, d2Date, d2Venue);

        // Bottom Gold Ribbon
        ctx.save();
        ctx.strokeStyle = 'rgba(212, 175, 55, 0.75)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(200, 1440);
        ctx.lineTo(width - 200, 1440);
        ctx.stroke();

        ctx.textAlign = 'center';
        ctx.font = '800 32px "Cinzel", Georgia, serif';
        ctx.fillStyle = '#fce79a';
        ctx.fillText(ribbon, width / 2, 1495);
        ctx.restore();

        return canvas;
    }

    async function downloadFamFlyer() {
        const btn = event?.currentTarget || document.querySelector('button[onclick="downloadFamFlyer()"]');
        const originalText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Compositing Masterpiece Poster...';
        }

        try {
            const canvas = await renderFullPosterCanvas();
            const dataUrl = canvas.toDataURL('image/png', 1.0);

            const rawTitle = currentPosterLayoutMode === 'allwhite_vip'
                ? (document.getElementById('aw-hero-main')?.value || 'all-white-party')
                : (document.getElementById('flyer-hero-title')?.value || 'major-at-50');
            const slug = rawTitle.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-');

            const a = document.createElement('a');
            a.href = dataUrl;
            a.download = `${slug}-poster.png`;
            document.body.appendChild(a);
            a.click();
            a.remove();
        } catch (err) {
            alert('Poster download failed: ' + err.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    function shareFamFlyerWhatsApp() {
        let msg = '';
        if (currentPosterLayoutMode === 'allwhite_vip') {
            const title = (document.getElementById('aw-hero-main')?.value || 'ALL WHITE') + ' ' + (document.getElementById('aw-hero-script')?.value || 'PARTY');
            const city = document.getElementById('aw-city-pill')?.value || '';
            const date = document.getElementById('aw-date')?.value || '';
            const time = document.getElementById('aw-time')?.value || '';
            const venue = document.getElementById('aw-venue')?.value || '';
            const rsvp = document.getElementById('aw-rsvp')?.value || '';
            msg = encodeURIComponent(`👑 *${title} (${city})*\n\n📅 *When:* ${date} (${time})\n📍 *Where:* ${venue}\n✨ *Dress Code:* All White (Smart & Stylish)\n🎟️ *RSVP:* ${rsvp}`);
        } else {
            const title = document.getElementById('flyer-hero-title')?.value || 'MAJOR @ 50';
            const sub = document.getElementById('flyer-subtitle')?.value || '';
            const d1 = (document.getElementById('flyer-dest1-title')?.value || '') + ' (' + (document.getElementById('flyer-dest1-date')?.value || '') + ')';
            const d2 = (document.getElementById('flyer-dest2-title')?.value || '') + ' (' + (document.getElementById('flyer-dest2-date')?.value || '') + ')';
            msg = encodeURIComponent(`👑 *SAVE THE DATE: ${title} (${sub})*\n\n🇳🇬 *${d1}*\n🇺🇸 *${d2}*\n\n✨ *Two Countries • One Celebration • Same Great Man*`);
        }
        window.open(`https://api.whatsapp.com/send?text=${msg}`, '_blank');
    }

    // 👑 DYNASTY TRIVIA CLASH
    let triviaQuestions = [];
    let currentTriviaIndex = 0;
    let triviaScore = 0;

    async function generateTriviaShow() {
        const famName = document.getElementById('trivia-family-name').value.trim();
        const category = document.getElementById('trivia-category').value;
        const factsRaw = document.getElementById('trivia-facts').value.split('\n').filter(f => f.trim() !== '');

        const btn = document.getElementById('btn-launch-trivia');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Writing Quiz with Llama-3...';

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
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Dynasty Trivia Clash';

            if (data.success && data.questions && data.questions.length > 0) {
                triviaQuestions = data.questions;
                currentTriviaIndex = 0;
                triviaScore = 0;
                document.getElementById('trivia-game-title').textContent = data.quiz_title || 'Dynasty Trivia';
                document.getElementById('trivia-score').textContent = '0 pts';
                document.getElementById('trivia-empty-state').style.display = 'none';
                document.getElementById('trivia-game-arena').style.display = 'block';
                renderTriviaQuestion();
            } else {
                alert(data.error || 'Failed to generate trivia quiz.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-1"></i> Start Dynasty Trivia Clash';
            alert('Trivia error: ' + err.message);
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
</script>
@endsection
