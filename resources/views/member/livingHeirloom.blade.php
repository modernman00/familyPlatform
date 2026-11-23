@extends('layouts.profileBase')

@section('content')
<div class="container py-4" id="livingHeirloomStudioRoot">
    @include('member.creativeStudioTabs', ['creativeTab' => 'heirloom'])

    <div class="mb-4 text-center">
        <span style="display:inline-block; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#b45309; background:rgba(217,119,6,0.1); border:1px solid rgba(217,119,6,0.25); padding:4px 14px; border-radius:999px;">
            🏛️ Sovereign Heritage &amp; Portrait Studio
        </span>
        <h1 class="lh-title" style="font-size:2rem; font-weight:800; margin-top:10px; letter-spacing:-0.02em;">
            Living Heirloom, Age Progression &amp; Ancestral Synthesis
        </h1>
        <p class="lh-subtitle" style="font-size:15px; max-width:720px; margin:8px auto 0; line-height:1.55;">
            Bring cherished portraits to life with natural motion and voice lip-sync, forecast future facial ageing across decades, and reverse-compute portraits of lost ancestors calibrated against your Family Tree.
        </p>
    </div>

    <!-- 3-Mode Studio Navigation Tabs (44x44px safe touch targets, nowrap) -->
    <div class="d-flex justify-content-center mb-4" style="overflow-x:auto;">
        <div class="d-inline-flex flex-wrap justify-content-center gap-2 p-2 rounded-4" style="background:#f1f5f9; border:1px solid #e2e8f0;" role="tablist" aria-label="Portrait Studio Modes">
            <button type="button" class="heirloom-tab-btn btn fw-bold px-3" data-tab="motion" role="tab" aria-selected="true"
                style="min-height:44px; min-width:44px; white-space:nowrap; flex-shrink:0; border-radius:12px; background:#0f172a; color:#ffffff; border:0;">
                ✨ Living Motion &amp; Talk
            </button>
            <button type="button" class="heirloom-tab-btn btn fw-bold px-3" data-tab="ageing" role="tab" aria-selected="false"
                style="min-height:44px; min-width:44px; white-space:nowrap; flex-shrink:0; border-radius:12px; background:transparent; color:#334155; border:0;">
                ⏳ Age Progression (+10/+20… Yrs)
            </button>
            <button type="button" class="heirloom-tab-btn btn fw-bold px-3" data-tab="ancestral" role="tab" aria-selected="false"
                style="min-height:44px; min-width:44px; white-space:nowrap; flex-shrink:0; border-radius:12px; background:transparent; color:#334155; border:0;">
                🧬 Ancestral Reverse Computation
            </button>
        </div>
    </div>

    <!-- PANEL 1: LIVING MOTION, TALK & RE-EMOTION -->
    <div id="tabPanelMotion" class="heirloom-tab-panel">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                @include('components.living_heirloom_card')
            </div>
        </div>
    </div>

    <!-- PANEL 2: FUTURE AGE PROGRESSION ENGINE -->
    <div id="tabPanelAgeing" class="heirloom-tab-panel d-none">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card p-4 p-md-5 mb-4 shadow-sm" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:20px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#1d4ed8; background:rgba(37,99,235,0.1); border:1px solid rgba(37,99,235,0.22); padding:4px 12px; border-radius:999px;">
                            ⏳ Chronological Ageing Engine
                        </span>
                        <span id="ageProgBadge" style="font-size:12px; font-weight:700; color:#475569; background:#f1f5f9; padding:4px 12px; border-radius:999px;">
                            Ready to Forecast
                        </span>
                    </div>
                    <h2 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:6px;">
                        Predict How Someone Will Look in 10, 20, 30 or 40 Years
                    </h2>
                    <p style="color:#475569; font-size:14px; margin-bottom:20px;">
                        Identity-locked facial ageing projection preserving bone structure, eye geometry, and complexion while modelling natural chronological maturity.
                    </p>

                    <div class="row g-4 align-items-start">
                        <div class="col-md-5">
                            <div class="p-3 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                <label for="ageProgPhotoInput" class="form-label fw-bold" style="color:#0f172a; font-size:13px;">1. Upload or Choose Portrait</label>
                                <input type="file" id="ageProgPhotoInput" accept="image/jpeg,image/png,image/webp" class="form-control mb-3" style="min-height:44px;">

                                @if(!empty($treeInventory['photographed']))
                                <label for="ageProgTreeSelect" class="form-label fw-bold" style="color:#0f172a; font-size:13px;">Or Pick from Family Tree</label>
                                <select id="ageProgTreeSelect" class="form-select mb-3" style="min-height:44px;">
                                    <option value="">-- Select photographed relative --</option>
                                    @foreach($treeInventory['photographed'] as $pm)
                                        <option value="{{ $pm['photo_url'] }}" data-gender="{{ $pm['gender'] }}">{{ $pm['full_name'] }}</option>
                                    @endforeach
                                </select>
                                @endif

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="ageProgCurrentAge" class="form-label fw-bold" style="color:#0f172a; font-size:13px;">Current Age</label>
                                        <input type="number" id="ageProgCurrentAge" class="form-control" value="30" min="1" max="90" style="min-height:44px;">
                                    </div>
                                    <div class="col-6">
                                        <label for="ageProgDelta" class="form-label fw-bold" style="color:#0f172a; font-size:13px;">Years Ahead</label>
                                        <select id="ageProgDelta" class="form-select" style="min-height:44px;">
                                            <option value="10">+10 Years</option>
                                            <option value="20" selected>+20 Years</option>
                                            <option value="30">+30 Years</option>
                                            <option value="40">+40 Years</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="button" id="runAgeProgressionBtn" data-spin="true"
                                    class="btn w-100 fw-bold"
                                    style="min-height:48px; background:linear-gradient(135deg,#1d4ed8,#2563eb); color:#ffffff; border:0; border-radius:14px; white-space:nowrap;">
                                    Predict Future Appearance ⏳
                                </button>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="row g-3">
                                <div class="col-6 text-center">
                                    <div class="p-2 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                        <span class="d-block fw-bold mb-2" style="font-size:12px; color:#475569;">Original Portrait</span>
                                        <img id="ageProgBeforeImg" src="" alt="Original Portrait Preview" class="img-fluid rounded-3 d-none" style="max-height:260px; object-fit:cover; width:100%;">
                                        <div id="ageProgBeforeEmpty" class="py-5" style="color:#64748b; font-size:13px;">Select or upload a photo</div>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="p-2 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                        <span class="d-block fw-bold mb-2" style="font-size:12px; color:#1d4ed8;">Projected Future</span>
                                        <img id="ageProgAfterImg" src="" alt="Age-Progressed Portrait" class="img-fluid rounded-3 d-none" style="max-height:260px; object-fit:cover; width:100%;">
                                        <div id="ageProgAfterEmpty" class="py-5" style="color:#64748b; font-size:13px;">Projection appears here</div>
                                    </div>
                                </div>
                            </div>
                            <div id="ageProgSummary" class="mt-3 p-3 rounded-3 d-none" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; font-size:13px;"></div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" id="sendAgedToMotionBtn" class="btn d-none fw-bold" style="min-height:44px; background:#0f172a; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                    Animate This Portrait ✨
                                </button>
                                <button type="button" id="saveAgedPortraitBtn" class="btn d-none fw-bold" style="min-height:44px; background:#059669; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                    Save to Family Vault 🏛️
                                </button>
                                <button type="button" id="postAgedFeedBtn" data-spin="true" class="btn d-none fw-bold" style="min-height:44px; background:#a31621; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                    Post to Family Feed 📣
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL 3: ANCESTRAL REVERSE COMPUTATION & CALIBRATION -->
    <div id="tabPanelAncestral" class="heirloom-tab-panel d-none">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card p-4 p-md-5 mb-4 shadow-sm" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:20px;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#7c3aed; background:rgba(124,58,237,0.1); border:1px solid rgba(124,58,237,0.22); padding:4px 12px; border-radius:999px;">
                            🧬 Family Tree Reverse Computation
                        </span>
                        <span id="ancestralStatusBadge" style="font-size:12px; font-weight:700; color:#475569; background:#f1f5f9; padding:4px 12px; border-radius:999px;">
                            {{ count($treeInventory['photographed'] ?? []) }} Photographed · {{ count($treeInventory['unphotographed_ancestors'] ?? []) }} Unphotographed
                        </span>
                    </div>
                    <h2 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:6px;">
                        Calibrate &amp; Synthesise Lost Ancestors from Your Family Tree
                    </h2>
                    <p style="color:#475569; font-size:14px; margin-bottom:20px;">
                        First, calibrate the engine against your known photographed relatives to measure its <strong>Closeness to Exact</strong>. Then select any unphotographed grandparent, great-grandparent, or great-great-grandparent to reverse-compute their likely portrait.
                    </p>

                    <!-- Step 1: Tree Calibration -->
                    <div class="p-3 p-md-4 rounded-4 mb-4" style="background:#faf5ff; border:1px solid #e9d5ff;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h3 style="font-size:1rem; font-weight:800; color:#4c1d95; margin:0 0 4px 0;">
                                    Step 1: Calibrate Accuracy ("Check Closeness to Exact")
                                </h3>
                                <p id="calibrationSummaryText" style="font-size:13px; color:#5b21b6; margin:0;">
                                    Runs Leave-One-Out Cross-Validation across connected relatives in your Family Tree.
                                </p>
                            </div>
                            <button type="button" id="runCalibrationBtn" data-spin="true"
                                class="btn fw-bold"
                                style="min-height:44px; min-width:44px; background:#6d28d9; color:#ffffff; border:0; border-radius:12px; white-space:nowrap; flex-shrink:0;">
                                Run Tree Calibration 📊
                            </button>
                        </div>
                        <div id="calibrationMetricsBox" class="mt-3 d-none">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <div class="px-3 py-2 rounded-3" style="background:#ffffff; border:1px solid #ddd6fe;">
                                    <span style="font-size:11px; color:#6b7280; display:block;">Closeness to Exact</span>
                                    <strong id="calClosenessScore" style="font-size:1.25rem; color:#5b21b6;">--%</strong>
                                </div>
                                <div class="px-3 py-2 rounded-3" style="background:#ffffff; border:1px solid #ddd6fe;">
                                    <span style="font-size:11px; color:#6b7280; display:block;">Calibrated Pairs</span>
                                    <strong id="calPairsCount" style="font-size:1.25rem; color:#0f172a;">0</strong>
                                </div>
                                <div class="px-3 py-2 rounded-3" style="background:#ffffff; border:1px solid #ddd6fe;">
                                    <span style="font-size:11px; color:#6b7280; display:block;">Decay Weight (α)</span>
                                    <strong id="calAlphaVal" style="font-size:1.25rem; color:#0f172a;">0.50</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select Unphotographed Ancestor & Synthesise -->
                    <div class="row g-4 align-items-start">
                        <div class="col-md-5">
                            <div class="p-3 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                <label for="targetAncestorSelect" class="form-label fw-bold" style="color:#0f172a; font-size:13px;">
                                    Step 2: Select Lost / Unphotographed Ancestor
                                </label>
                                <select id="targetAncestorSelect" class="form-select mb-3" style="min-height:44px;">
                                    <option value="">-- Choose an ancestor from Family Tree --</option>
                                    @foreach(($treeInventory['unphotographed_ancestors'] ?? []) as $anc)
                                        <option value="{{ $anc['id'] }}">
                                            {{ $anc['full_name'] }} (Gen {{ $anc['generation_level'] }}{{ $anc['birth_year'] ? ', b. ' . $anc['birth_year'] : '' }})
                                        </option>
                                    @endforeach
                                </select>

                                @if(empty($treeInventory['unphotographed_ancestors']))
                                    <div class="p-3 rounded-3 mb-3" style="background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:13px;">
                                        No unphotographed ancestors found in your Family Tree yet. <a href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas" style="color:#b45309; font-weight:700;">Open your Family Tree</a> to add grandparents or great-grandparents.
                                    </div>
                                @endif

                                <button type="button" id="runReverseAncestorBtn" data-spin="true"
                                    class="btn w-100 fw-bold"
                                    style="min-height:48px; background:linear-gradient(135deg,#b45309,#d97706); color:#ffffff; border:0; border-radius:14px; white-space:nowrap;">
                                    Synthesise Ancestor Portrait 🧬
                                </button>
                            </div>
                        </div>

                        <div class="col-md-7 text-center">
                            <div class="p-3 rounded-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                <span id="reverseResultBadge" class="d-inline-block mb-2 px-3 py-1 rounded-pill" style="font-size:11px; font-weight:700; background:#fef3c7; color:#92400e;">
                                    Calibrated Ancestral Reconstruction
                                </span>
                                <div class="my-2">
                                    <img id="reverseAncestorImg" src="" alt="Synthesised Ancestor Portrait" class="img-fluid rounded-4 d-none" style="max-height:300px; object-fit:cover;">
                                    <div id="reverseAncestorEmpty" class="py-5" style="color:#64748b; font-size:13.5px;">
                                        Select an unphotographed ancestor and click <strong>Synthesise Ancestor Portrait</strong>.
                                    </div>
                                </div>
                                <div id="reverseRelativesList" class="text-start mt-3 d-none" style="font-size:12.5px; color:#334155;"></div>
                                <div class="d-flex justify-content-center flex-wrap gap-2 mt-3">
                                    <button type="button" id="animateAncestorBtn" class="btn d-none fw-bold" style="min-height:44px; background:#0f172a; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                        Bring Ancestor to Life ✨
                                    </button>
                                    <button type="button" id="saveAncestorBtn" class="btn d-none fw-bold" style="min-height:44px; background:#059669; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                        Save to Family Vault 🏛️
                                    </button>
                                    <button type="button" id="postAncestorFeedBtn" data-spin="true" class="btn d-none fw-bold" style="min-height:44px; background:#a31621; color:#ffffff; border-radius:12px; white-space:nowrap;">
                                        Post to Family Feed 📣
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($heirlooms))
    <div class="mt-5">
        <h3 style="font-size:1.35rem; font-weight:800; color:#0f172a; margin-bottom:16px;">
            Family Heirloom Gallery ({{ count($heirlooms) }})
        </h3>
        <div class="row g-4">
            @foreach($heirlooms as $item)
            <div class="col-md-4 col-sm-6">
                <div class="card p-3 shadow-sm rounded-4 h-100" style="background:#ffffff; border:1px solid #e2e8f0;">
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['ancestor_name'] }}" style="width:100%; height:200px; object-fit:cover; border-radius:12px;">
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="text-truncate" title="{{ $item['ancestor_name'] }}" style="font-size:1.05rem; font-weight:700; color:#0f172a; margin:0;">{{ $item['ancestor_name'] }}</h5>
                            <span class="badge bg-light text-dark" style="flex-shrink:0;">{{ $item['era_year'] }}</span>
                        </div>
                        <p style="color:#475569; font-size:13px; margin:6px 0 0 0;">{{ $item['story_note'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // Tab switching
    var tabButtons = document.querySelectorAll('.heirloom-tab-btn');
    var panels = {
        motion: document.getElementById('tabPanelMotion'),
        ageing: document.getElementById('tabPanelAgeing'),
        ancestral: document.getElementById('tabPanelAncestral')
    };

    function activateTab(tabKey) {
        tabButtons.forEach(function(btn) {
            var active = btn.getAttribute('data-tab') === tabKey;
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
            btn.style.background = active ? '#0f172a' : 'transparent';
            btn.style.color = active ? '#ffffff' : '#334155';
        });
        Object.keys(panels).forEach(function(k) {
            if (panels[k]) {
                panels[k].classList.toggle('d-none', k !== tabKey);
            }
        });
        window.dispatchEvent(new CustomEvent('heirloom:tab-changed', { detail: { tabKey: tabKey } }));
    }

    tabButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            activateTab(btn.getAttribute('data-tab'));
        });
    });

    // Helper: Read file input as Data URL
    function readFileAsDataUrl(file) {
        return new Promise(function(resolve, reject) {
            var reader = new FileReader();
            reader.onload = function() { resolve(reader.result); };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    // Helper: Send portrait to Living Motion canvas
    window.loadPortraitIntoLivingStudio = function(imageUrl, label) {
        activateTab('motion');
        if (window.__heirloomLoadExternalPortrait) {
            window.__heirloomLoadExternalPortrait(imageUrl, label || 'Synthesised Portrait');
        }
    };

    // Shared Helper: Publish any portrait directly to Family Post Feed
    window.publishHeirloomToFeed = function(imageSource, defaultCaption, modeLabel, btn) {
        fetch('/api/heirloom/publish-to-feed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                token: csrfToken,
                image_source: imageSource,
                caption: defaultCaption,
                mode_label: modeLabel
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (btn) btn.disabled = false;
            if (res && res.success) {
                if (btn) btn.textContent = 'Posted to Feed ✓';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Published!', text: res.message || 'Published to your Family Feed!', timer: 2500, showConfirmButton: false });
                }
            } else {
                alert((res && res.error) || 'Unable to publish to Family Feed.');
            }
        })
        .catch(function() {
            if (btn) btn.disabled = false;
            alert('Network error while publishing to Family Feed.');
        });
    };

    // ---------------- AGE PROGRESSION ENGINE ----------------
    var ageSourceDataUrl = '';
    var ageProgResultUrl = '';
    var agePhotoInput = document.getElementById('ageProgPhotoInput');
    var ageTreeSelect = document.getElementById('ageProgTreeSelect');
    var ageBeforeImg = document.getElementById('ageProgBeforeImg');
    var ageBeforeEmpty = document.getElementById('ageProgBeforeEmpty');
    var ageAfterImg = document.getElementById('ageProgAfterImg');
    var ageAfterEmpty = document.getElementById('ageProgAfterEmpty');
    var ageSummary = document.getElementById('ageProgSummary');
    var ageBadge = document.getElementById('ageProgBadge');
    var runAgeBtn = document.getElementById('runAgeProgressionBtn');
    var sendAgedBtn = document.getElementById('sendAgedToMotionBtn');
    var saveAgedBtn = document.getElementById('saveAgedPortraitBtn');
    var postAgedFeedBtn = document.getElementById('postAgedFeedBtn');

    function setAgeBefore(src) {
        ageSourceDataUrl = src;
        if (src) {
            ageBeforeImg.src = src;
            ageBeforeImg.classList.remove('d-none');
            ageBeforeEmpty.classList.add('d-none');
        }
    }

    if (agePhotoInput) {
        agePhotoInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                readFileAsDataUrl(e.target.files[0]).then(setAgeBefore);
            }
        });
    }
    if (ageTreeSelect) {
        ageTreeSelect.addEventListener('change', function() {
            if (ageTreeSelect.value) setAgeBefore(ageTreeSelect.value);
        });
    }
    if (runAgeBtn) {
        runAgeBtn.addEventListener('click', function() {
            if (!ageSourceDataUrl) {
                alert('Please upload a portrait or select a family member first.');
                return;
            }
            runAgeBtn.disabled = true;
            if (ageBadge) ageBadge.textContent = 'Computing Future Ageing...';

            var selectedOpt = ageTreeSelect && ageTreeSelect.selectedOptions ? ageTreeSelect.selectedOptions[0] : null;
            var gender = selectedOpt ? (selectedOpt.getAttribute('data-gender') || 'Unspecified') : 'Unspecified';

            fetch('/api/heirloom/age-progression', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: csrfToken,
                    image_source: ageSourceDataUrl,
                    current_age: parseInt(document.getElementById('ageProgCurrentAge').value || '30', 10),
                    years_delta: parseInt(document.getElementById('ageProgDelta').value || '20', 10),
                    gender: gender
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                runAgeBtn.disabled = false;
                if (res && res.success) {
                    ageProgResultUrl = res.image_data_url;
                    ageAfterImg.src = ageProgResultUrl;
                    ageAfterImg.classList.remove('d-none');
                    ageAfterEmpty.classList.add('d-none');
                    ageSummary.textContent = res.summary + ' (' + res.tier + ')';
                    ageSummary.classList.remove('d-none');
                    if (ageBadge) ageBadge.textContent = 'Projected Age ~' + res.target_age;
                    sendAgedBtn.classList.remove('d-none');
                    saveAgedBtn.classList.remove('d-none');
                    if (postAgedFeedBtn) postAgedFeedBtn.classList.remove('d-none');
                } else {
                    if (ageBadge) ageBadge.textContent = 'Error';
                    alert((res && res.error) || 'Unable to generate age progression.');
                }
            })
            .catch(function() {
                runAgeBtn.disabled = false;
                if (ageBadge) ageBadge.textContent = 'Network Error';
            });
        });
    }
    if (sendAgedBtn) {
        sendAgedBtn.addEventListener('click', function() {
            if (ageProgResultUrl) window.loadPortraitIntoLivingStudio(ageProgResultUrl, 'Future Age Projection');
        });
    }
    if (saveAgedBtn) {
        saveAgedBtn.addEventListener('click', function() {
            if (!ageProgResultUrl) return;
            saveAgedBtn.disabled = true;
            fetch('/api/heirloom/save-portrait', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    token: csrfToken,
                    ancestor_name: 'Future Age Projection',
                    era_year: '+' + (document.getElementById('ageProgDelta').value || '20') + ' Years',
                    image_url: ageProgResultUrl,
                    story_note: ageSummary ? ageSummary.textContent : 'Age progression portrait.'
                })
            }).then(function(r) { return r.json(); }).then(function(res) {
                saveAgedBtn.disabled = false;
                if (res && res.success) {
                    saveAgedBtn.textContent = 'Saved to Vault ✓';
                }
            });
        });
    }

    if (postAgedFeedBtn) {
        postAgedFeedBtn.addEventListener('click', function() {
            if (!ageProgResultUrl) return;
            postAgedFeedBtn.disabled = true;
            var delta = (document.getElementById('ageProgDelta') || {}).value || '20';
            window.publishHeirloomToFeed(ageProgResultUrl, '⏳ Future Age Projection (+' + delta + ' Years) — ' + (ageSummary ? ageSummary.textContent : ''), 'Future Age Projection', postAgedFeedBtn);
        });
    }

    // ---------------- ANCESTRAL REVERSE COMPUTATION ----------------
    var runCalBtn = document.getElementById('runCalibrationBtn');
    var calBox = document.getElementById('calibrationMetricsBox');
    var calSummary = document.getElementById('calibrationSummaryText');
    var runRevBtn = document.getElementById('runReverseAncestorBtn');
    var revImg = document.getElementById('reverseAncestorImg');
    var revEmpty = document.getElementById('reverseAncestorEmpty');
    var revList = document.getElementById('reverseRelativesList');
    var animAncBtn = document.getElementById('animateAncestorBtn');
    var saveAncBtn = document.getElementById('saveAncestorBtn');
    var postAncFeedBtn = document.getElementById('postAncestorFeedBtn');
    var lastAncestorResult = null;

    if (runCalBtn) {
        runCalBtn.addEventListener('click', function() {
            runCalBtn.disabled = true;
            fetch('/api/heirloom/calibrate-tree', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ token: csrfToken })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                runCalBtn.disabled = false;
                if (res && res.success && res.calibration) {
                    var c = res.calibration;
                    calBox.classList.remove('d-none');
                    document.getElementById('calClosenessScore').textContent = c.average_closeness_pct + '%';
                    document.getElementById('calPairsCount').textContent = c.calibrated_pairs_count;
                    document.getElementById('calAlphaVal').textContent = Number(c.optimal_decay_alpha).toFixed(2);
                    calSummary.textContent = c.summary;
                }
            })
            .catch(function() { runCalBtn.disabled = false; });
        });
    }

    if (runRevBtn) {
        runRevBtn.addEventListener('click', function() {
            var nodeId = parseInt((document.getElementById('targetAncestorSelect') || {}).value || '0', 10);
            if (!nodeId) {
                alert('Please select an unphotographed ancestor from your Family Tree.');
                return;
            }
            runRevBtn.disabled = true;
            fetch('/api/heirloom/reverse-ancestor', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ token: csrfToken, target_node_id: nodeId })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                runRevBtn.disabled = false;
                if (res && res.success) {
                    lastAncestorResult = res;
                    revImg.src = res.image_data_url;
                    revImg.classList.remove('d-none');
                    revEmpty.classList.add('d-none');
                    if (res.calibration) {
                        calBox.classList.remove('d-none');
                        document.getElementById('calClosenessScore').textContent = res.calibration.average_closeness_pct + '%';
                        document.getElementById('calPairsCount').textContent = res.calibration.calibrated_pairs_count;
                        document.getElementById('calAlphaVal').textContent = Number(res.calibration.optimal_decay_alpha).toFixed(2);
                        calSummary.textContent = res.calibration.summary;
                    }
                    if (res.reference_relatives && res.reference_relatives.length) {
                        revList.classList.remove('d-none');
                        revList.innerHTML = '<strong>Descendant Signals Used:</strong> ' + res.reference_relatives.map(function(r) {
                            return r.name + ' (' + r.relationship + ', ' + r.weight_pct + '%)';
                        }).join(' · ');
                    }
                    animAncBtn.classList.remove('d-none');
                    saveAncBtn.classList.remove('d-none');
                    if (postAncFeedBtn) postAncFeedBtn.classList.remove('d-none');
                } else {
                    alert((res && res.error) || 'Unable to synthesise ancestor.');
                }
            })
            .catch(function() { runRevBtn.disabled = false; });
        });
    }

    if (animAncBtn) {
        animAncBtn.addEventListener('click', function() {
            if (lastAncestorResult && lastAncestorResult.image_data_url) {
                window.loadPortraitIntoLivingStudio(lastAncestorResult.image_data_url, lastAncestorResult.target_node.full_name);
            }
        });
    }
    if (saveAncBtn) {
        saveAncBtn.addEventListener('click', function() {
            if (!lastAncestorResult) return;
            saveAncBtn.disabled = true;
            fetch('/api/heirloom/save-portrait', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    token: csrfToken,
                    ancestor_name: lastAncestorResult.target_node.full_name,
                    era_year: lastAncestorResult.target_node.birth_year ? ('b. ' + lastAncestorResult.target_node.birth_year) : 'Ancestral Reconstruction',
                    image_url: lastAncestorResult.image_data_url,
                    story_note: lastAncestorResult.disclaimer
                })
            }).then(function(r) { return r.json(); }).then(function(res) {
                saveAncBtn.disabled = false;
                if (res && res.success) saveAncBtn.textContent = 'Saved to Vault ✓';
            });
        });
    }
    if (postAncFeedBtn) {
        postAncFeedBtn.addEventListener('click', function() {
            if (!lastAncestorResult || !lastAncestorResult.image_data_url) return;
            postAncFeedBtn.disabled = true;
            var name = lastAncestorResult.target_node ? lastAncestorResult.target_node.full_name : 'Family Ancestor';
            var closeness = lastAncestorResult.calibration ? (' (' + lastAncestorResult.calibration.average_closeness_pct + '% calibrated closeness)') : '';
            window.publishHeirloomToFeed(lastAncestorResult.image_data_url, '🧬 Ancestral Reverse Reconstruction: ' + name + closeness, 'Ancestral Reverse Reconstruction', postAncFeedBtn);
        });
    }
});
</script>
@endsection
