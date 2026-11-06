{{-- Living Heirloom 2.5D Facial Depth & Parallax Photo Animator --}}
{{-- Engineered by the Advanced Technology Group (ATG) --}}
<div class="card p-4 p-md-5 mb-5 shadow-sm rounded-2xl" style="background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); border-radius:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#b45309; background:rgba(217,119,6,0.1); border:1px solid rgba(217,119,6,0.25); padding:3px 10px; border-radius:999px;">
            ✨ Living Heirloom Animator
        </span>
        <span id="heirloomStateBadge" style="font-size:11.5px; font-weight:700; color:#64748b; background:#f1f5f9; padding:3px 10px; border-radius:999px;">
            Mode: Ready to Animate
        </span>
    </div>

    <h3 style="color:#0f172a; font-size:1.15rem; font-weight:800; margin:0 0 6px 0;">
        Bring Ancestral Family Photographs to Life
    </h3>
    <p style="color:#64748b; font-size:13.5px; margin:0 0 16px 0; line-height:1.45;">
        Transform vintage family portraits into breathing, lifelike moments with subtle respiratory depth, natural eye-blinking, and interactive touch parallax.
    </p>

    <!-- Canvas Container -->
    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; margin:16px 0;">
        <canvas id="heirloomCanvas" width="280" height="320" style="background:#1e1b18; border-radius:18px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid rgba(217,119,6,0.2); touch-action:none;"></canvas>
        <span style="font-size:11px; color:#94a3b8; margin-top:6px;">Tilt phone or move mouse across portrait for parallax</span>
    </div>

    <!-- Controls & Actions -->
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9;">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <button type="button" id="startHeirloomAnimBtn" style="min-height:48px; min-width:44px; padding:0 20px; background:linear-gradient(135deg,#d97706,#b45309); border:0; border-radius:14px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 8px 20px rgba(217,119,6,0.3); transition:all 0.2s;">
                <span>Start Living Motion</span>
                <span aria-hidden="true">✨</span>
            </button>
            <button type="button" id="loadSampleAncestorBtn" style="min-height:48px; min-width:44px; padding:0 16px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:14px; color:#334155; font-weight:700; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <span>Sample Vintage Portrait 👵</span>
            </button>
            <button type="button" id="saveHeirloomBtn" style="min-height:48px; min-width:44px; padding:0 16px; background:linear-gradient(135deg,#059669,#10b981); border:0; border-radius:14px; color:#ffffff; font-weight:800; font-size:13px; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <span>Save to Vault 🏛️</span>
            </button>
        </div>
        <div id="heirloomFeedback" style="font-size:12px; color:#059669; font-weight:700; display:none;">
            Saved to Family Vault!
        </div>
    </div>
</div>

<script src="/public/js/heirloom-animator.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('heirloomCanvas');
    var startBtn = document.getElementById('startHeirloomAnimBtn');
    var sampleBtn = document.getElementById('loadSampleAncestorBtn');
    var saveBtn = document.getElementById('saveHeirloomBtn');
    var badge = document.getElementById('heirloomStateBadge');
    var feedback = document.getElementById('heirloomFeedback');

    var animator = null;
    if (canvas && typeof LivingHeirloomAnimator !== 'undefined') {
        animator = new LivingHeirloomAnimator(canvas, {
            enableParallax: true,
            enableBlink: true,
            enableBreathing: true,
            breathPeriodSec: 4.2
        });

        // Generate synthetic vintage heirloom portrait fallback
        var initSample = function() {
            var temp = document.createElement('canvas');
            temp.width = 280;
            temp.height = 320;
            var tCtx = temp.getContext('2d');

            // Warm vintage vignette background
            var bgGrad = tCtx.createRadialGradient(140, 160, 40, 140, 160, 170);
            bgGrad.addColorStop(0, '#5c4d3c');
            bgGrad.addColorStop(1, '#241b14');
            tCtx.fillStyle = bgGrad;
            tCtx.fillRect(0, 0, 280, 320);

            // Ancestor silhouette / facial contour
            tCtx.fillStyle = '#d2bba0'; // Skin tone
            tCtx.beginPath();
            tCtx.ellipse(140, 140, 52, 68, 0, 0, Math.PI * 2);
            tCtx.fill();

            // Shoulders / Victorian attire
            tCtx.fillStyle = '#1c140e';
            tCtx.beginPath();
            tCtx.ellipse(140, 260, 95, 75, 0, 0, Math.PI * 2);
            tCtx.fill();

            // Eyes
            tCtx.fillStyle = '#3d2817';
            tCtx.beginPath();
            tCtx.arc(120, 130, 4, 0, Math.PI * 2);
            tCtx.arc(160, 130, 4, 0, Math.PI * 2);
            tCtx.fill();

            // Gentle smile
            tCtx.strokeStyle = '#5a3d28';
            tCtx.lineWidth = 2;
            tCtx.beginPath();
            tCtx.arc(140, 162, 14, 0.2, Math.PI - 0.2);
            tCtx.stroke();

            var img = new Image();
            img.src = temp.toDataURL();
            img.onload = function() {
                animator.loadImage(img).then(function() {
                    if (badge) badge.textContent = 'Mode: Vintage Portrait Loaded';
                });
            };
        };

        initSample();

        if (sampleBtn) {
            sampleBtn.addEventListener('click', initSample);
        }

        if (startBtn) {
            startBtn.addEventListener('click', function() {
                if (!animator.isRunning) {
                    animator.start();
                    startBtn.querySelector('span').textContent = 'Pause Motion';
                    if (badge) {
                        badge.textContent = 'Mode: Living Parallax Active';
                        badge.style.color = '#d97706';
                        badge.style.background = 'rgba(217,119,6,0.1)';
                    }
                } else {
                    animator.stop();
                    startBtn.querySelector('span').textContent = 'Start Living Motion';
                    if (badge) {
                        badge.textContent = 'Mode: Paused';
                        badge.style.color = '#64748b';
                        badge.style.background = '#f1f5f9';
                    }
                }
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
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ancestor_name: 'Great-Grandparent Portrait',
                        era_year: 'Circa 1948',
                        image_url: canvas.toDataURL(),
                        story_note: 'Living heirloom portrait animated with 2.5D respiratory depth and natural eye blink.'
                    })
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    saveBtn.disabled = false;
                    saveBtn.style.opacity = '1';
                    if (data.success && feedback) {
                        feedback.textContent = '✓ ' + data.message;
                        feedback.style.display = 'block';
                        setTimeout(function() { feedback.style.display = 'none'; }, 5000);
                    }
                })
                .catch(function(err) {
                    saveBtn.disabled = false;
                    saveBtn.style.opacity = '1';
                    console.warn('[Heirloom] Save error:', err);
                });
            });
        }
    }
});
</script>
