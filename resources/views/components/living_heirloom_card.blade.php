{{-- Living Heirloom 2.5D Facial Depth, Talking Lip-Sync & Re-Emotion Studio --}}
{{-- Engineered by the Advanced Technology Group (ATG) --}}
<div class="card p-4 p-md-5 mb-4 shadow-sm" style="background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); border-radius:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#b45309; background:rgba(217,119,6,0.1); border:1px solid rgba(217,119,6,0.25); padding:4px 12px; border-radius:999px;">
            ✨ Living Heirloom Animator v2
        </span>
        <span id="heirloomStateBadge" style="font-size:12px; font-weight:700; color:#475569; background:#f1f5f9; padding:4px 12px; border-radius:999px;">
            Mode: Ready to Animate
        </span>
    </div>

    <h2 style="color:#0f172a; font-size:1.3rem; font-weight:800; margin:0 0 6px 0;">
        Bring Loved Ones &amp; Deceased Grandparents to Life
    </h2>
    <p style="color:#475569; font-size:14px; margin:0 0 18px 0; line-height:1.5;">
        Upload a portrait or select a relative from your Family Tree. Apply natural breathing, eye-blinking, re-emotion expressions, and real-time voice lip-sync.
    </p>

    <div class="row g-4 align-items-start">
        <!-- Left: Portrait Canvas + Live Tribute Caption -->
        <div class="col-md-5 text-center">
            <div style="position:relative; display:inline-block;">
                <canvas id="heirloomCanvas" width="280" height="320" style="background:#1e1b18; border-radius:18px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid rgba(217,119,6,0.25); touch-action:none; max-width:100%; height:auto;"></canvas>
                <div id="heirloomCaptionOverlay" class="d-none" style="position:absolute; left:12px; right:12px; bottom:14px; background:rgba(15,23,42,0.86); color:#ffffff; padding:8px 12px; border-radius:10px; font-size:12.5px; font-weight:600; line-height:1.35;"></div>
            </div>
            <div style="font-size:12px; color:#64748b; margin-top:8px;">Move pointer or tilt phone for 2.5D parallax</div>

            <audio id="heirloomVoiceAudio" class="w-100 mt-2 d-none" controls></audio>
        </div>

        <!-- Right: Controls -->
        <div class="col-md-7">
            <div class="p-3 rounded-4 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                <label for="heirloomUploadInput" class="form-label fw-bold" style="font-size:13px; color:#0f172a;">1. Portrait Source</label>
                <input type="file" id="heirloomUploadInput" accept="image/jpeg,image/png,image/webp" class="form-control mb-2" style="min-height:44px;">

                @if(!empty($treeInventory['photographed']))
                <select id="heirloomTreePortraitSelect" class="form-select mb-2" style="min-height:44px;" aria-label="Choose Family Tree Portrait">
                    <option value="">-- Or select a relative from Family Tree --</option>
                    @foreach($treeInventory['photographed'] as $pm)
                        <option value="{{ $pm['photo_url'] }}">{{ $pm['full_name'] }}</option>
                    @endforeach
                </select>
                @endif

                <button type="button" id="loadSampleAncestorBtn" class="btn btn-sm fw-bold" style="min-height:44px; background:#ffffff; border:1px solid #cbd5e1; border-radius:10px; color:#334155; white-space:nowrap;">
                    Load Vintage Sample Portrait 👵
                </button>
            </div>

            <!-- Expression & Re-Emotion -->
            <div class="p-3 rounded-4 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                <label for="heirloomEmotionSelect" class="form-label fw-bold" style="font-size:13px; color:#0f172a;">2. Expression &amp; Re-Emotion</label>
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
            <div class="p-3 rounded-4 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                <label for="heirloomTributeInput" class="form-label fw-bold" style="font-size:13px; color:#0f172a;">3. Make Portrait Talk (Voice Capsule or Tribute Caption)</label>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <input type="file" id="heirloomAudioUpload" accept="audio/*" class="form-control" style="min-height:44px; flex:1 1 200px;" aria-label="Upload recorded voice note">
                    <button type="button" id="recordVoiceBtn" class="btn fw-bold"
                        style="min-height:44px; background:#be123c; color:#ffffff; border:0; border-radius:10px; white-space:nowrap; flex-shrink:0;">
                        🎙️ Record Voice
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <input type="text" id="heirloomTributeInput" class="form-control" placeholder="e.g. Always remember where you come from..." style="min-height:44px; flex:1 1 200px;">
                    <button type="button" id="speakTributeBtn" class="btn fw-bold"
                        style="min-height:44px; background:#0f172a; color:#ffffff; border:0; border-radius:10px; white-space:nowrap; flex-shrink:0;">
                        Animate Speech 💬
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Primary Action Bar -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9;">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <button type="button" id="startHeirloomAnimBtn" style="min-height:46px; min-width:44px; padding:0 20px; background:linear-gradient(135deg,#d97706,#b45309); border:0; border-radius:12px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; white-space:nowrap; flex-shrink:0;">
                <span>Start Living Motion</span>
                <span aria-hidden="true">✨</span>
            </button>
            <button type="button" id="exportHeirloomClipBtn" data-spin="true" style="min-height:46px; min-width:44px; padding:0 16px; background:#ffffff; border:1px solid #cbd5e1; border-radius:12px; color:#0f172a; font-weight:700; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex-shrink:0;">
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
</div>

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
        activePortraitDataUrl = src;
        if (label) activePortraitLabel = label;
        animator.loadImage(src).then(function() {
            if (badge) badge.textContent = 'Mode: ' + activePortraitLabel + ' Loaded';
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
