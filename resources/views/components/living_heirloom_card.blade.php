{{-- Living Heirloom 2.5D Facial Depth, Talking Lip-Sync & Re-Emotion Studio --}}
{{-- Engineered by the Advanced Technology Group (ATG) --}}
@push('styles')
<link rel="stylesheet" href="/public/css/studio-pip.css?v={{ assetVersion('css/studio-pip.css') }}">
@endpush
<style>
.lh-card {
    background: var(--card, #ffffff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 20px;
}
.lh-state-badge {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 999px;
}
.lh-title {
    color: #0f172a;
    font-size: 1.3rem;
    font-weight: 800;
    margin: 0 0 6px 0;
}
.lh-subtitle {
    color: #475569;
    font-size: 14px;
    margin: 0 0 18px 0;
    line-height: 1.5;
}
.lh-hint {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
}
.lh-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.lh-panel-label {
    font-size: 13px;
    color: #0f172a;
}
.lh-btn-secondary {
    min-height: 44px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    color: #334155;
    white-space: nowrap;
}
.lh-btn-export {
    min-height: 46px;
    min-width: 44px;
    padding: 0 16px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    color: #0f172a;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
}
.lh-btn-dark {
    min-height: 44px;
    background: #0f172a;
    color: #ffffff;
    border: 0;
    border-radius: 10px;
    white-space: nowrap;
    flex-shrink: 0;
}
.lh-action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}

body.dark-mode .lh-card,
body.dark-theme .lh-card,
[data-bs-theme="dark"] .lh-card,
html.dark .lh-card,
html.dark-mode .lh-card {
    background: #141418 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode .lh-state-badge,
body.dark-theme .lh-state-badge,
[data-bs-theme="dark"] .lh-state-badge,
html.dark .lh-state-badge,
html.dark-mode .lh-state-badge {
    background: #1e293b !important;
    color: #cbd5e1 !important;
    border: 1px solid rgba(255, 255, 255, 0.12);
}
body.dark-mode .lh-title,
body.dark-mode .lh-panel-label,
body.dark-theme .lh-title,
body.dark-theme .lh-panel-label,
[data-bs-theme="dark"] .lh-title,
[data-bs-theme="dark"] .lh-panel-label,
html.dark .lh-title,
html.dark .lh-panel-label,
html.dark-mode .lh-title,
html.dark-mode .lh-panel-label {
    color: #f8fafc !important;
}
body.dark-mode .lh-subtitle,
body.dark-mode .lh-hint,
body.dark-theme .lh-subtitle,
body.dark-theme .lh-hint,
[data-bs-theme="dark"] .lh-subtitle,
[data-bs-theme="dark"] .lh-hint,
html.dark .lh-subtitle,
html.dark .lh-hint,
html.dark-mode .lh-subtitle,
html.dark-mode .lh-hint {
    color: #cbd5e1 !important;
}
body.dark-mode .lh-panel,
body.dark-theme .lh-panel,
[data-bs-theme="dark"] .lh-panel,
html.dark .lh-panel,
html.dark-mode .lh-panel {
    background: #1a2030 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    color: #f8fafc !important;
}
body.dark-mode .lh-btn-secondary,
body.dark-mode .lh-btn-export,
body.dark-theme .lh-btn-secondary,
body.dark-theme .lh-btn-export,
[data-bs-theme="dark"] .lh-btn-secondary,
[data-bs-theme="dark"] .lh-btn-export,
html.dark .lh-btn-secondary,
html.dark .lh-btn-export,
html.dark-mode .lh-btn-secondary,
html.dark-mode .lh-btn-export {
    background: #232c40 !important;
    border-color: rgba(255, 255, 255, 0.18) !important;
    color: #f8fafc !important;
}
body.dark-mode .lh-btn-dark,
body.dark-theme .lh-btn-dark,
[data-bs-theme="dark"] .lh-btn-dark,
html.dark .lh-btn-dark,
html.dark-mode .lh-btn-dark {
    background: #2563eb !important;
    color: #ffffff !important;
}
body.dark-mode .lh-action-bar,
body.dark-theme .lh-action-bar,
[data-bs-theme="dark"] .lh-action-bar,
html.dark .lh-action-bar,
html.dark-mode .lh-action-bar {
    border-top-color: rgba(255, 255, 255, 0.1) !important;
}
</style>
<div class="card p-4 p-md-5 mb-4 shadow-sm lh-card" x-data="livingHeirloomPip()" x-init="initHeirloomPip()">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#d97706; background:rgba(217,119,6,0.12); border:1px solid rgba(217,119,6,0.3); padding:4px 12px; border-radius:999px;">
            ✨ Living Heirloom Animator v2
        </span>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span id="heirloomStateBadge" class="lh-state-badge">
                Mode: Ready to Animate
            </span>
            <button type="button" class="spip-trigger" @click="togglePipPopout()" :aria-pressed="pipForced && !pipMinimised" title="Pop out preview while editing">
                <i class="bi bi-pip" aria-hidden="true"></i>
                <span x-text="(pipForced && !pipMinimised) ? 'Docked' : 'Pop out'"></span>
            </button>
        </div>
    </div>

    <h2 class="lh-title">
        Bring Loved Ones &amp; Deceased Grandparents to Life
    </h2>
    <p class="lh-subtitle">
        Upload a portrait or select a relative from your Family Tree. Apply natural breathing, eye-blinking, re-emotion expressions, and real-time voice lip-sync.
    </p>

    <div class="row g-4 align-items-start">
        <!-- Left: Portrait Canvas + Live Tribute Caption -->
        <div class="col-md-5 text-center">
            <div id="heirloomStageWrap" style="position:relative; display:inline-block;">
                <canvas id="heirloomCanvas" width="280" height="320" style="background:#1e1b18; border-radius:18px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid rgba(217,119,6,0.25); touch-action:none; max-width:100%; height:auto;"></canvas>
                <div id="heirloomCaptionOverlay" class="d-none" style="position:absolute; left:12px; right:12px; bottom:14px; background:rgba(15,23,42,0.86); color:#ffffff; padding:8px 12px; border-radius:10px; font-size:12.5px; font-weight:600; line-height:1.35;"></div>
            </div>
            <div class="lh-hint">Move pointer or tilt phone for 2.5D parallax</div>

            <audio id="heirloomVoiceAudio" class="w-100 mt-2 d-none" controls></audio>
        </div>

        <!-- Right: Controls -->
        <div class="col-md-7">
            <div class="p-3 rounded-4 mb-3 lh-panel">
                <label for="heirloomUploadInput" class="form-label fw-bold lh-panel-label">1. Portrait Source</label>
                <input type="file" id="heirloomUploadInput" accept="image/jpeg,image/png,image/webp" class="form-control mb-2" style="min-height:44px;">

                @if(!empty($treeInventory['photographed']))
                <select id="heirloomTreePortraitSelect" class="form-select mb-2" style="min-height:44px;" aria-label="Choose Family Tree Portrait">
                    <option value="">-- Or select a relative from Family Tree --</option>
                    @foreach($treeInventory['photographed'] as $pm)
                        <option value="{{ $pm['photo_url'] }}">{{ $pm['full_name'] }}</option>
                    @endforeach
                </select>
                @endif

                <button type="button" id="loadSampleAncestorBtn" class="btn btn-sm fw-bold lh-btn-secondary">
                    Load Vintage Sample Portrait 👵
                </button>
            </div>

            <!-- Expression & Re-Emotion -->
            <div class="p-3 rounded-4 mb-3 lh-panel">
                <label for="heirloomEmotionSelect" class="form-label fw-bold lh-panel-label">2. Expression &amp; Re-Emotion</label>
                <div class="d-flex flex-wrap gap-2">
                    <select id="heirloomEmotionSelect" class="form-select" style="min-height:44px; flex:1 1 180px;">
                        <option value="serene">Serene Breathing &amp; Blink</option>
                        <option value="warm_smile">Warm Smile</option>
                        <option value="nostalgic_nod">Nostalgic Nod</option>
                        <option value="joyful_laugh">Joyful Laugh</option>
                    </select>
                    <button type="button" id="neuralReEmotionBtn" data-spin="true" class="btn fw-bold"
                        style="min-height:44px; background:#7c3aed; color:#ffffff; border:0; border-radius:10px; white-space:nowrap; flex-shrink:0;">
                        AI Re-Emotion Keyframe ✨
                    </button>
                </div>
            </div>

            <!-- Voice Lip-Sync & Tribute -->
            <div class="p-3 rounded-4 mb-3 lh-panel">
                <label for="heirloomTributeInput" class="form-label fw-bold lh-panel-label">3. Make Portrait Talk (Voice Capsule or Tribute Caption)</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <input type="file" id="heirloomAudioUpload" accept="audio/*" class="form-control" style="min-height:44px; flex:1 1 200px;" aria-label="Upload recorded voice note">
                    <button type="button" id="recordVoiceBtn" class="btn fw-bold"
                        style="min-height:44px; background:#be123c; color:#ffffff; border:0; border-radius:10px; white-space:nowrap; flex-shrink:0;">
                        🎙️ Record Voice
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <input type="text" id="heirloomTributeInput" class="form-control" placeholder="e.g. Always remember where you come from..." style="min-height:44px; flex:1 1 200px;">
                    <button type="button" id="speakTributeBtn" class="btn fw-bold lh-btn-dark">
                        Animate Speech 💬
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Action Bar -->
    <div class="lh-action-bar">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <button type="button" id="startHeirloomAnimBtn" style="min-height:46px; min-width:44px; padding:0 20px; background:linear-gradient(135deg,#d97706,#b45309); border:0; border-radius:12px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; white-space:nowrap; flex-shrink:0;">
                <span>Start Living Motion</span>
                <span aria-hidden="true">✨</span>
            </button>
            <button type="button" id="exportHeirloomClipBtn" data-spin="true" class="lh-btn-export">
                <span>Export Video Clip 🎬</span>
            </button>
            <button type="button" id="saveHeirloomBtn" data-spin="true" style="min-height:46px; min-width:44px; padding:0 16px; background:linear-gradient(135deg,#059669,#10b981); border:0; border-radius:12px; color:#ffffff; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex-shrink:0;">
                <span>Save to Vault 🏛️</span>
            </button>
            <button type="button" id="postHeirloomFeedBtn" data-spin="true" style="min-height:46px; min-width:44px; padding:0 16px; background:linear-gradient(135deg,#a31621,#be123c); border:0; border-radius:12px; color:#ffffff; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex-shrink:0;">
                <span>Post to Family Feed 📣</span>
            </button>
        </div>
        <div id="heirloomFeedback" style="font-size:13px; color:#047857; font-weight:700; display:none;">
            Saved to Family Vault!
        </div>
    </div>

    {{-- Floating PiP & Expandable Preview for Living Heirloom --}}
    @include('member.includes.studioPip', [
        'mode' => 'canvas',
        'title' => 'Living heirloom preview',
        'blockedExpr' => '!heirloomMotionTabActive',
        'hasVideoControls' => false,
    ])
</div>

<script src="/public/js/studio-pip.js?v={{ assetVersion('js/studio-pip.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
function livingHeirloomPip() {
    var base = window.FPStudioPip ? window.FPStudioPip.create({
        stageSelector: '#heirloomStageWrap',
        canvasSelector: '#heirloomCanvas',
        storageKey: 'fp_pip_living_heirloom',
        fps: 12,
    }) : {
        stageVisible: true,
        pipForced: false,
        pipMinimised: false,
        pipExpanded: false,
        pipBottom: false,
        initPip: function () {},
        isPipDockVisible: function () { return false; },
        isPipPillVisible: function () { return false; },
        togglePipPopout: function () {},
        minimisePip: function () {},
        restorePip: function () {},
        expandPip: function () {},
        closeExpandedPip: function () {},
        toggleCorner: function () {},
    };

    return Object.assign({}, base, {
        heirloomMotionTabActive: true,
        initHeirloomPip: function () {
            var self = this;
            if (typeof this.initPip === 'function') {
                this.initPip();
            }
            var panel = document.getElementById('tabPanelMotion');
            self.heirloomMotionTabActive = !panel || !panel.classList.contains('d-none');
            window.addEventListener('heirloom:tab-changed', function (e) {
                var key = e && e.detail ? e.detail.tabKey : 'motion';
                self.heirloomMotionTabActive = (key === 'motion');
                if (!self.heirloomMotionTabActive) {
                    self.pipExpanded = false;
                }
            });
        },
    });
}
</script>
<script src="/public/js/heirloom-animator.js"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var canvas = document.getElementById('heirloomCanvas');
    var startBtn = document.getElementById('startHeirloomAnimBtn');
    var sampleBtn = document.getElementById('loadSampleAncestorBtn');
    var saveBtn = document.getElementById('saveHeirloomBtn');
    var postFeedBtn = document.getElementById('postHeirloomFeedBtn');
    var exportBtn = document.getElementById('exportHeirloomClipBtn');
    var reEmotionBtn = document.getElementById('neuralReEmotionBtn');
    var emotionSelect = document.getElementById('heirloomEmotionSelect');
    var uploadInput = document.getElementById('heirloomUploadInput');
    var treeSelect = document.getElementById('heirloomTreePortraitSelect');
    var audioUpload = document.getElementById('heirloomAudioUpload');
    var audioEl = document.getElementById('heirloomVoiceAudio');
    var recordBtn = document.getElementById('recordVoiceBtn');
    var speakBtn = document.getElementById('speakTributeBtn');
    var tributeInput = document.getElementById('heirloomTributeInput');
    var captionOverlay = document.getElementById('heirloomCaptionOverlay');
    var badge = document.getElementById('heirloomStateBadge');
    var feedback = document.getElementById('heirloomFeedback');

    var activePortraitLabel = 'Great-Grandparent Portrait';
    var activePortraitDataUrl = '';
    var animator = null;

    if (!canvas || typeof LivingHeirloomAnimator === 'undefined') return;

    animator = new LivingHeirloomAnimator(canvas, {
        enableParallax: true,
        enableBlink: true,
        enableBreathing: true,
        breathPeriodSec: 4.2
    });

    function loadSourceIntoAnimator(src, label) {
        var cleanSrc = typeof src === 'string' ? src.trim() : '';
        if (!cleanSrc) return;
        if (!/^(?:https?:|data:|blob:|\/)/i.test(cleanSrc)) {
            cleanSrc = '/' + cleanSrc;
        }
        activePortraitDataUrl = cleanSrc;
        if (label) activePortraitLabel = label.trim();
        if (badge) badge.textContent = 'Mode: Loading ' + activePortraitLabel + '…';
        animator.loadImage(cleanSrc).then(function() {
            if (badge) badge.textContent = 'Mode: ' + activePortraitLabel + ' Loaded';
        }).catch(function() {
            if (badge) badge.textContent = 'Mode: Unable to load portrait';
        });
    }

    window.__heirloomLoadExternalPortrait = function(src, label) {
        loadSourceIntoAnimator(src, label);
        if (!animator.isRunning) {
            animator.start();
            startBtn.querySelector('span').textContent = 'Pause Motion';
        }
    };

    var initSample = function() {
        var temp = document.createElement('canvas');
        temp.width = 280;
        temp.height = 320;
        var tCtx = temp.getContext('2d');

        var bgGrad = tCtx.createRadialGradient(140, 160, 40, 140, 160, 170);
        bgGrad.addColorStop(0, '#5c4d3c');
        bgGrad.addColorStop(1, '#241b14');
        tCtx.fillStyle = bgGrad;
        tCtx.fillRect(0, 0, 280, 320);

        tCtx.fillStyle = '#d2bba0';
        tCtx.beginPath();
        tCtx.ellipse(140, 140, 52, 68, 0, 0, Math.PI * 2);
        tCtx.fill();

        tCtx.fillStyle = '#1c140e';
        tCtx.beginPath();
        tCtx.ellipse(140, 260, 95, 75, 0, 0, Math.PI * 2);
        tCtx.fill();

        tCtx.fillStyle = '#3d2817';
        tCtx.beginPath();
        tCtx.arc(120, 130, 4, 0, Math.PI * 2);
        tCtx.arc(160, 130, 4, 0, Math.PI * 2);
        tCtx.fill();

        tCtx.strokeStyle = '#5a3d28';
        tCtx.lineWidth = 2;
        tCtx.beginPath();
        tCtx.arc(140, 162, 14, 0.2, Math.PI - 0.2);
        tCtx.stroke();

        loadSourceIntoAnimator(temp.toDataURL('image/jpeg', 0.9), 'Vintage Heirloom Portrait');
    };

    initSample();

    if (sampleBtn) sampleBtn.addEventListener('click', initSample);

    if (uploadInput) {
        uploadInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function() { loadSourceIntoAnimator(reader.result, e.target.files[0].name); };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    if (treeSelect) {
        treeSelect.addEventListener('change', function() {
            if (treeSelect.value) {
                var opt = treeSelect.selectedOptions[0];
                loadSourceIntoAnimator(treeSelect.value, opt ? opt.textContent : 'Family Relative');
            }
        });
    }

    if (emotionSelect) {
        emotionSelect.addEventListener('change', function() {
            animator.setEmotionMode(emotionSelect.value);
            if (!animator.isRunning) {
                animator.start();
                startBtn.querySelector('span').textContent = 'Pause Motion';
            }
            if (badge) badge.textContent = 'Emotion: ' + emotionSelect.options[emotionSelect.selectedIndex].text;
        });
    }

    if (reEmotionBtn) {
        reEmotionBtn.addEventListener('click', function() {
            var src = activePortraitDataUrl || canvas.toDataURL('image/jpeg', 0.9);
            reEmotionBtn.disabled = true;
            fetch('/api/heirloom/re-emotion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    token: csrfToken,
                    image_source: src,
                    emotion: emotionSelect ? emotionSelect.value : 'warm_smile'
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                reEmotionBtn.disabled = false;
                if (res && res.success && res.image_data_url) {
                    loadSourceIntoAnimator(res.image_data_url, activePortraitLabel);
                    animator.setEmotionMode(emotionSelect ? emotionSelect.value : 'warm_smile');
                    animator.start();
                    startBtn.querySelector('span').textContent = 'Pause Motion';
                    if (badge) badge.textContent = 'AI Re-Emotion: ' + res.label;
                }
            })
            .catch(function() { reEmotionBtn.disabled = false; });
        });
    }

    if (audioUpload && audioEl) {
        audioUpload.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                audioEl.src = URL.createObjectURL(e.target.files[0]);
                audioEl.classList.remove('d-none');
                animator.attachAudioElement(audioEl);
                audioEl.play();
                startBtn.querySelector('span').textContent = 'Pause Motion';
                if (badge) badge.textContent = 'Mode: Live Voice Lip-Sync';
            }
        });
    }

    // Live Microphone Voice Recording for Ancestor Tribute
    var mediaRecorder = null;
    var recChunks = [];
    if (recordBtn && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        recordBtn.addEventListener('click', function() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                recordBtn.textContent = '🎙️ Record Voice';
                return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
                recChunks = [];
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = function(e) { if (e.data.size > 0) recChunks.push(e.data); };
                mediaRecorder.onstop = function() {
                    stream.getTracks().forEach(function(t) { t.stop(); });
                    var blob = new Blob(recChunks, { type: 'audio/webm' });
                    audioEl.src = URL.createObjectURL(blob);
                    audioEl.classList.remove('d-none');
                    animator.attachAudioElement(audioEl);
                    audioEl.play();
                    startBtn.querySelector('span').textContent = 'Pause Motion';
                    if (badge) badge.textContent = 'Mode: Recorded Tribute Lip-Sync';
                };
                mediaRecorder.start();
                recordBtn.textContent = '⏹️ Stop Recording';
            }).catch(function() {
                alert('Microphone permission is required to record a voice tribute.');
            });
        });
    }

    if (speakBtn && tributeInput) {
        speakBtn.addEventListener('click', function() {
            var text = (tributeInput.value || 'Remember our heritage with pride and love.').trim();
            if (captionOverlay) {
                captionOverlay.textContent = '"' + text + '"';
                captionOverlay.classList.remove('d-none');
                setTimeout(function() { captionOverlay.classList.add('d-none'); }, 5500);
            }
            animator.startTributeSpeech(5500);
            startBtn.querySelector('span').textContent = 'Pause Motion';
            if (badge) badge.textContent = 'Mode: Talking Tribute Active';
        });
    }

    if (startBtn) {
        startBtn.addEventListener('click', function() {
            if (!animator.isRunning) {
                animator.start();
                startBtn.querySelector('span').textContent = 'Pause Motion';
                if (badge) {
                    badge.textContent = 'Mode: Living Motion Active';
                    badge.style.color = '#b45309';
                    badge.style.background = 'rgba(217,119,6,0.12)';
                }
            } else {
                animator.stop();
                startBtn.querySelector('span').textContent = 'Start Living Motion';
                if (badge) {
                    badge.textContent = 'Mode: Paused';
                    badge.style.color = '#475569';
                    badge.style.background = '#f1f5f9';
                }
            }
        });
    }

    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportBtn.disabled = true;
            animator.exportVideoClip(4000).then(function(blob) {
                exportBtn.disabled = false;
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'living-heirloom-portrait.webm';
                a.click();
                setTimeout(function() { URL.revokeObjectURL(url); }, 3000);
            }).catch(function(err) {
                exportBtn.disabled = false;
                alert(err.message || 'Unable to export video clip.');
            });
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.7';

            fetch('/api/heirloom/save-portrait', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: csrfToken,
                    ancestor_name: activePortraitLabel,
                    era_year: 'Circa 1948',
                    image_url: canvas.toDataURL('image/jpeg', 0.9),
                    story_note: 'Living heirloom portrait animated with 2.5D respiratory depth, eye blink, and expression synthesis.'
                })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                saveBtn.disabled = false;
                saveBtn.style.opacity = '1';
                if (data && data.success && feedback) {
                    feedback.style.display = 'block';
                    setTimeout(function() { feedback.style.display = 'none'; }, 3500);
                }
            })
            .catch(function() {
                saveBtn.disabled = false;
                saveBtn.style.opacity = '1';
            });
        });
    }

    if (postFeedBtn) {
        postFeedBtn.addEventListener('click', function() {
            postFeedBtn.disabled = true;
            var imgSrc = canvas.toDataURL('image/jpeg', 0.92);
            var tribute = tributeInput && tributeInput.value.trim() ? (' — "' + tributeInput.value.trim() + '"') : '';
            window.publishHeirloomToFeed(imgSrc, '✨ Living Heirloom Portrait: ' + activePortraitLabel + tribute, 'Living Heirloom', postFeedBtn);
        });
    }
});
</script>
