{{-- Kinship Quest Chore Pinball & 3D Celestial Galaxy Component --}}
{{-- Engineered by the Advanced Technology Group (ATG) --}}
<div class="card p-4 p-md-5 mb-5 shadow-sm rounded-2xl" style="background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); border-radius:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
        <span style="font-size:11px; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:#0284c7; background:rgba(2,132,199,0.1); border:1px solid rgba(2,132,199,0.25); padding:3px 10px; border-radius:999px;">
            ⭐ Kinship Quest Arcade & 3D Galaxy
        </span>
        <div style="display:inline-flex; background:#f1f5f9; padding:4px; border-radius:999px; border:1px solid #e2e8f0; gap:8px;">
            <button type="button" id="familyMode2DBtn" style="min-height:44px; min-width:44px; padding:0 16px; border:0; border-radius:999px; background:#ffffff; color:#0284c7; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.06); transition:all 0.2s; white-space:nowrap; flex-shrink:0;">
                2D Pinball ⭐
            </button>
            <button type="button" id="familyMode3DBtn" style="min-height:44px; min-width:44px; padding:0 16px; border:0; border-radius:999px; background:transparent; color:#64748b; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.2s; white-space:nowrap; flex-shrink:0;">
                3D Galaxy 🌌
            </button>
        </div>
    </div>

    <h3 style="color:#0f172a; font-size:1.15rem; font-weight:800; margin:0 0 6px 0;">
        Turn Household Chores into Family Milestones
    </h3>
    <p style="color:#64748b; font-size:13.5px; margin:0 0 16px 0; line-height:1.45;">
        Complete household tasks to drop pinball achievement balls in 2D, or celebrate collaborative family memories orbiting our 3D celestial sphere.
    </p>

    <!-- 2D Pinball Container -->
    <div id="family2DContainer" style="display:flex; justify-content:center; align-items:center; margin:14px 0;">
        <canvas id="familyPinballCanvas" width="280" height="280" style="background:#0f172a; border-radius:16px; box-shadow:inset 0 4px 12px rgba(0,0,0,0.5);"></canvas>
    </div>

    <!-- 3D Galaxy Container -->
    <div id="family3DContainer" style="display:none; justify-content:center; align-items:center; margin:14px 0;">
        <canvas id="familyGalaxyCanvas" width="280" height="280" style="background:#0a0f1d; border-radius:16px; box-shadow:inset 0 4px 12px rgba(0,0,0,0.6); cursor:pointer; touch-action:none;"></canvas>
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9;">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <button type="button" id="dropFamilyChoreBtn" style="min-height:48px; min-width:44px; padding:0 18px; background:linear-gradient(135deg,#0284c7,#38bdf8); border:0; border-radius:14px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 8px 20px rgba(2,132,199,0.3); transition:all 0.2s;" onmouseover="this.style.filter='brightness(1.08)'" onmouseout="this.style.filter='none'">
                <span>Drop Chore Ball</span>
                <span aria-hidden="true">⭐</span>
            </button>
            <button type="button" id="spinFamilyGalaxyBtn" style="display:none; min-height:48px; min-width:44px; padding:0 18px; background:linear-gradient(135deg,#6366f1,#8b5cf6); border:0; border-radius:14px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 8px 20px rgba(99,102,241,0.3); transition:all 0.2s;">
                <span>Accelerate 3D Orbit</span>
                <span aria-hidden="true">🌌</span>
            </button>
            <button type="button" id="claimRewardBtn" class="btn-spin-on-click" data-spin="true" style="min-height:48px; min-width:44px; padding:0 18px; background:linear-gradient(135deg,#10b981,#059669); border:0; border-radius:14px; color:#ffffff; font-weight:800; font-size:14px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 8px 20px rgba(16,185,129,0.3); transition:all 0.2s; white-space:nowrap; flex-shrink:0;">
                <span>Claim Household Reward</span>
                <span aria-hidden="true">🎁</span>
            </button>
        </div>
        <div style="text-align:right;">
            <div id="familyChorePoints" style="font-size:14px; font-weight:800; color:#0284c7;">
                Family Points: 0
            </div>
            <div id="claimRewardFeedback" style="font-size:12px; color:#10b981; font-weight:700; display:none; margin-top:4px;">
                Household Reward Claimed!
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
document.addEventListener('DOMContentLoaded', function() {
    var mode2DBtn = document.getElementById('familyMode2DBtn');
    var mode3DBtn = document.getElementById('familyMode3DBtn');
    var container2D = document.getElementById('family2DContainer');
    var container3D = document.getElementById('family3DContainer');
    var dropBtn = document.getElementById('dropFamilyChoreBtn');
    var spinGalaxyBtn = document.getElementById('spinFamilyGalaxyBtn');
    var claimBtn = document.getElementById('claimRewardBtn');
    var feedbackEl = document.getElementById('claimRewardFeedback');
    var ptsEl = document.getElementById('familyChorePoints');

    if (mode2DBtn && mode3DBtn) {
        mode2DBtn.addEventListener('click', function() {
            container2D.style.display = 'flex';
            container3D.style.display = 'none';
            dropBtn.style.display = 'inline-flex';
            spinGalaxyBtn.style.display = 'none';
            mode2DBtn.style.background = '#ffffff';
            mode2DBtn.style.color = '#0284c7';
            mode2DBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.06)';
            mode3DBtn.style.background = 'transparent';
            mode3DBtn.style.color = '#64748b';
            mode3DBtn.style.boxShadow = 'none';
        });

        mode3DBtn.addEventListener('click', function() {
            container2D.style.display = 'none';
            container3D.style.display = 'flex';
            dropBtn.style.display = 'none';
            spinGalaxyBtn.style.display = 'inline-flex';
            mode3DBtn.style.background = '#ffffff';
            mode3DBtn.style.color = '#0284c7';
            mode3DBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.06)';
            mode2DBtn.style.background = 'transparent';
            mode2DBtn.style.color = '#64748b';
            mode2DBtn.style.boxShadow = 'none';
        });
    }

    // 2D Pinball Implementation
    var pCanvas = document.getElementById('familyPinballCanvas');
    if (pCanvas) {
        var pCtx = pCanvas.getContext('2d');
        var pegs = [];
        var balls = [];
        var bursts = [];
        var totalPoints = 0;

        for (var r = 0; r < 4; r++) {
            var cols = 5;
            var spacingX = 280 / (cols + 1);
            var offset = (r % 2 === 0) ? 0 : spacingX * 0.5;
            for (var c = 0; c < cols; c++) {
                var x = spacingX * (c + 1) + offset - (r % 2 === 0 ? 0 : spacingX * 0.25);
                var y = 60 + r * 40;
                if (x > 20 && x < 260) {
                    pegs.push({ x: x, y: y, radius: 5, color: '#38bdf8' });
                }
            }
        }

        function addConfetti(x, y) {
            for (var i = 0; i < 18; i++) {
                var a = Math.random() * Math.PI * 2;
                var s = Math.random() * 4 + 1.5;
                bursts.push({
                    x: x, y: y,
                    vx: Math.cos(a) * s,
                    vy: Math.sin(a) * s - 1.2,
                    color: ['#f59e0b', '#ec4899', '#38bdf8', '#10b981'][Math.floor(Math.random() * 4)],
                    alpha: 1.0
                });
            }
        }

        function dropBall() {
            var chores = ['🧹', '🍽️', '🐕', '📚', '⭐', '🧺'];
            balls.push({
                x: 140 + (Math.random() * 50 - 25),
                y: 20,
                vx: (Math.random() - 0.5) * 2,
                vy: 1,
                radius: 13,
                emoji: chores[Math.floor(Math.random() * chores.length)]
            });
        }

        if (dropBtn) dropBtn.addEventListener('click', dropBall);

        if (claimBtn) {
            claimBtn.addEventListener('click', function() {
                var awardPoints = totalPoints > 0 ? totalPoints : 100;
                claimBtn.disabled = true;
                claimBtn.style.opacity = '0.7';
                if (window.ScoreTenantSpinner) {
                    window.ScoreTenantSpinner.start(claimBtn);
                }

                var csrfToken = (document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content'))
                    || (document.querySelector('input[name="token"]') && document.querySelector('input[name="token"]').value)
                    || '';

                fetch('/api/roundtable/record-chore-points', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        points: awardPoints,
                        chore_type: 'Household Pinball Quest',
                        reward_title: 'Family Movie & Treats',
                        token: csrfToken
                    })
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    claimBtn.disabled = false;
                    claimBtn.style.opacity = '1';
                    if (window.ScoreTenantSpinner) {
                        window.ScoreTenantSpinner.stop(claimBtn);
                    }
                    if (data.success) {
                        if (ptsEl) ptsEl.textContent = 'Family Points: ' + (data.total_points || awardPoints);
                        if (feedbackEl) {
                            feedbackEl.textContent = '🎉 ' + (data.message || 'Household points credited to treasury!');
                            feedbackEl.style.display = 'block';
                            setTimeout(function() { feedbackEl.style.display = 'none'; }, 5000);
                        }
                    }
                })
                .catch(function(err) {
                    claimBtn.disabled = false;
                    claimBtn.style.opacity = '1';
                    if (window.ScoreTenantSpinner) {
                        window.ScoreTenantSpinner.stop(claimBtn);
                    }
                    console.warn('[KinshipQuest] Reward submission error:', err);
                });
            });
        }

        function loop2D() {
            pCtx.clearRect(0, 0, 280, 280);

            for (var i = 0; i < pegs.length; i++) {
                var peg = pegs[i];
                pCtx.beginPath();
                pCtx.arc(peg.x, peg.y, peg.radius, 0, Math.PI * 2);
                pCtx.fillStyle = peg.color;
                pCtx.fill();
            }

            for (var b = 0; b < 3; b++) {
                pCtx.fillStyle = 'rgba(255,255,255,0.06)';
                pCtx.fillRect(b * (280/3), 252, 280/3 - 2, 28);
                pCtx.fillStyle = '#94a3b8';
                pCtx.font = 'bold 10px sans-serif';
                pCtx.textAlign = 'center';
                pCtx.fillText((b + 1) * 50 + ' pts', b * (280/3) + 280/6, 268);
            }

            for (var j = balls.length - 1; j >= 0; j--) {
                var ball = balls[j];
                ball.vy += 0.22;
                ball.x += ball.vx;
                ball.y += ball.vy;

                if (ball.x < ball.radius) { ball.x = ball.radius; ball.vx *= -0.7; }
                if (ball.x > 280 - ball.radius) { ball.x = 280 - ball.radius; ball.vx *= -0.7; }

                for (var p = 0; p < pegs.length; p++) {
                    var pg = pegs[p];
                    var dx = ball.x - pg.x;
                    var dy = ball.y - pg.y;
                    var dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < ball.radius + pg.radius) {
                        ball.vx = (dx / dist) * 2.8;
                        ball.vy = (dy / dist) * 2.8;
                    }
                }

                pCtx.font = '15px sans-serif';
                pCtx.textAlign = 'center';
                pCtx.textBaseline = 'middle';
                pCtx.fillText(ball.emoji, ball.x, ball.y);

                if (ball.y > 252) {
                    addConfetti(ball.x, ball.y);
                    var bucket = Math.floor((ball.x / 280) * 3) + 1;
                    totalPoints += bucket * 50;
                    if (ptsEl) ptsEl.textContent = 'Family Points: ' + totalPoints;
                    balls.splice(j, 1);
                }
            }

            for (var k = bursts.length - 1; k >= 0; k--) {
                var c = bursts[k];
                c.x += c.vx;
                c.y += c.vy;
                c.vy += 0.1;
                c.alpha -= 0.025;
                if (c.alpha <= 0) {
                    bursts.splice(k, 1);
                } else {
                    pCtx.save();
                    pCtx.globalAlpha = c.alpha;
                    pCtx.fillStyle = c.color;
                    pCtx.fillRect(c.x, c.y, 3, 3);
                    pCtx.restore();
                }
            }

            requestAnimationFrame(loop2D);
        }
        requestAnimationFrame(loop2D);
    }

    // 3D Celestial Galaxy Implementation
    var galCanvas = document.getElementById('familyGalaxyCanvas');
    if (galCanvas) {
        var gCtx = galCanvas.getContext('2d');
        var gAngle = 0;
        var gSpeed = 0.015;

        var stars = [
            { name: 'Grandma Birthday', dist: 78, angle: 0, color: '#f59e0b', size: 11, emoji: '🎂' },
            { name: 'Graduation Day', dist: 98, angle: 2.1, color: '#38bdf8', size: 13, emoji: '🎓' },
            { name: 'Summer Holiday', dist: 115, angle: 4.2, color: '#ec4899', size: 12, emoji: '🏖️' }
        ];

        if (spinGalaxyBtn) {
            spinGalaxyBtn.addEventListener('click', function() {
                gSpeed = 0.07;
            });
        }
        galCanvas.addEventListener('click', function() {
            gSpeed = 0.06;
        });

        function loop3DGalaxy() {
            gCtx.clearRect(0, 0, 280, 280);
            gAngle += gSpeed;
            gSpeed = Math.max(0.015, gSpeed * 0.985);

            var cx = 140, cy = 140;

            // Celestial Sun Core
            var grad = gCtx.createRadialGradient(cx, cy, 5, cx, cy, 34);
            grad.addColorStop(0, '#ffd700');
            grad.addColorStop(0.5, '#f59e0b');
            grad.addColorStop(1, 'transparent');
            gCtx.fillStyle = grad;
            gCtx.beginPath();
            gCtx.arc(cx, cy, 34, 0, Math.PI * 2);
            gCtx.fill();

            gCtx.font = '18px sans-serif';
            gCtx.textAlign = 'center';
            gCtx.textBaseline = 'middle';
            gCtx.fillText('☀️', cx, cy);

            for (var s = 0; s < stars.length; s++) {
                var st = stars[s];
                st.angle += gSpeed * (85 / st.dist);
                var x = cx + Math.cos(st.angle) * st.dist;
                var y = cy + Math.sin(st.angle) * (st.dist * 0.45);
                var zScale = (Math.sin(st.angle) + 1.8) / 2.8;

                gCtx.strokeStyle = 'rgba(255,255,255,0.12)';
                gCtx.lineWidth = 1;
                gCtx.beginPath();
                gCtx.ellipse(cx, cy, st.dist, st.dist * 0.45, 0, 0, Math.PI * 2);
                gCtx.stroke();

                gCtx.save();
                gCtx.translate(x, y);
                gCtx.scale(zScale, zScale);
                gCtx.beginPath();
                gCtx.arc(0, 0, st.size, 0, Math.PI * 2);
                gCtx.fillStyle = st.color;
                gCtx.fill();
                gCtx.font = '13px sans-serif';
                gCtx.fillText(st.emoji, 0, 1);
                gCtx.restore();
            }

            requestAnimationFrame(loop3DGalaxy);
        }
        requestAnimationFrame(loop3DGalaxy);
    }
});
</script>
