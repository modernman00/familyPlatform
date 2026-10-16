<div class="modal fade" id="familyFlyerModal" tabindex="-1" aria-labelledby="familyFlyerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff;">
            @include('csrf')
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2" style="background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); border-bottom: 1px solid rgba(245, 158, 11, 0.15) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-warning shadow-sm" style="width: 48px; height: 48px; background: #fde68a; font-size: 1.4rem;">
                        <i class="bi bi-card-image"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="familyFlyerModalLabel">Family Status Flyer</h5>
                            <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">WHATSAPP SOCIAL CARD</span>
                        </div>
                        <p class="text-muted small mb-0">Generate a high-status heraldic flyer of your family clan to share on WhatsApp & social chats.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <!-- Flyer Canvas Preview Wrapper -->
                <div class="card border-0 mb-3 shadow-sm overflow-hidden" style="border-radius: 18px; background: #06170e;">
                    <div class="d-flex justify-content-center align-items-center p-2 p-md-3">
                        <canvas id="familyFlyerCanvas" width="1200" height="630" style="width: 100%; max-width: 680px; height: auto; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.4);"></canvas>
                    </div>
                </div>

                <!-- Customization / Stats Strip -->
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">TOTAL MEMBERS</span>
                            <span class="fw-bold text-dark fs-6" id="flyerStatMembers">--</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">GENERATIONS</span>
                            <span class="fw-bold text-dark fs-6" id="flyerStatGens">--</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded-3 bg-light border">
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">CITIES &amp; REGIONS</span>
                            <span class="fw-bold text-dark fs-6" id="flyerStatHubs">--</span>
                        </div>
                    </div>
                </div>

                <!-- Alert Feedback -->
                <div id="flyerCopiedAlert" class="alert alert-success d-none py-2 px-3 mb-2 text-center small rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> WhatsApp invitation link copied to clipboard!
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="modal-footer border-0 bg-light px-4 py-3 d-flex flex-column flex-sm-row justify-content-between gap-2">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Client-side rendering • Zero PII leaked
                </span>
                <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold rounded-3" onclick="copyFlyerInviteLink()">
                        <i class="bi bi-clipboard me-1"></i> Copy Link
                    </button>
                    <button type="button" class="btn btn-primary btn-sm px-3 py-2 fw-bold rounded-3 shadow-sm" onclick="downloadFlyerImage()">
                        <i class="bi bi-download me-1"></i> Download PNG
                    </button>
                    <button type="button" class="btn btn-success btn-sm px-4 py-2 fw-bold text-white rounded-3 shadow-sm" style="background: #25D366; border-color: #25D366;" onclick="shareFlyerToWhatsApp()">
                        <i class="bi bi-whatsapp me-1"></i> Share to WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let flyerRendered = false;
    let flyerInviteUrl = window.location.origin + '/register';

    window.openFamilyFlyerModal = function() {
        const modalEl = document.getElementById('familyFlyerModal');
        if (!modalEl) return;
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();

        // Fetch or create a general family invite token for this flyer
        fetch('/api/invite/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                type: 'onboarding'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.invite_url) {
                flyerInviteUrl = data.invite_url;
            }
        })
        .catch(() => {})
        .finally(() => {
            renderFamilyFlyer();
        });

        // If outside organogram page, asynchronously fetch live stats from lineage quests API
        if (typeof graphData === 'undefined' || !graphData) {
            fetch('/api/lineage/quests')
                .then(res => res.json())
                .then(res => {
                    if (res && res.status === 'success' && res.data) {
                        window.cachedFlyerStats = {
                            memberCount: res.data.stats?.total_nodes || 1,
                            genCount: Math.max(1, Math.min(6, Math.ceil((res.data.stats?.total_nodes || 1) / 3))),
                            hubsCount: Math.max(1, Math.min(10, Math.ceil((res.data.stats?.total_nodes || 1) / 4))),
                            familySurname: res.data.family_code || 'Family'
                        };
                        renderFamilyFlyer();
                    }
                })
                .catch(() => {});
        }
    };

    function renderFamilyFlyer() {
        const canvas = document.getElementById('familyFlyerCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        const W = canvas.width;
        const H = canvas.height;

        // Extract statistics from graphData if available
        let memberCount = 1;
        let genCount = 1;
        let hubsCount = 1;
        let familySurname = 'Family';

        try {
            if (typeof graphData !== 'undefined' && graphData && graphData.nodes) {
                const nodes = graphData.nodes;
                memberCount = nodes.length || 1;
                const gens = new Set();
                const locations = new Set();
                nodes.forEach(n => {
                    if (typeof n.generation_level !== 'undefined') gens.add(n.generation_level);
                    if (n.location && n.location.trim()) locations.add(n.location.trim());
                    if (n.last_name && n.last_name.trim() && familySurname === 'Family') {
                        familySurname = n.last_name.trim();
                    }
                });
                genCount = Math.max(1, gens.size);
                hubsCount = Math.max(1, locations.size);
            }
        } catch (e) {}

        if (window.cachedFlyerStats) {
            memberCount = window.cachedFlyerStats.memberCount || memberCount;
            genCount = window.cachedFlyerStats.genCount || genCount;
            hubsCount = window.cachedFlyerStats.hubsCount || hubsCount;
            familySurname = window.cachedFlyerStats.familySurname || familySurname;
        }

        const statMemEl = document.getElementById('flyerStatMembers');
        const statGenEl = document.getElementById('flyerStatGens');
        const statHubEl = document.getElementById('flyerStatHubs');
        if (statMemEl) statMemEl.textContent = memberCount;
        if (statGenEl) statGenEl.textContent = genCount;
        if (statHubEl) statHubEl.textContent = hubsCount;

        // 1. Rich Emerald Gradient Background
        const bgGrad = ctx.createRadialGradient(W / 2, H / 2, 50, W / 2, H / 2, W * 0.7);
        bgGrad.addColorStop(0, '#0d2d1d');
        bgGrad.addColorStop(0.6, '#06170e');
        bgGrad.addColorStop(1, '#020905');
        ctx.fillStyle = bgGrad;
        ctx.fillRect(0, 0, W, H);

        // 2. Ornate Double Gold Borders
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 4;
        ctx.strokeRect(30, 30, W - 60, H - 60);

        ctx.strokeStyle = 'rgba(212, 175, 55, 0.35)';
        ctx.lineWidth = 1.5;
        ctx.strokeRect(42, 42, W - 84, H - 84);

        // Corner Gold Accents
        drawCornerFlourish(ctx, 42, 42);
        drawCornerFlourish(ctx, W - 42, 42, true);
        drawCornerFlourish(ctx, 42, H - 42, false, true);
        drawCornerFlourish(ctx, W - 42, H - 42, true, true);

        // 3. Heraldic Top Tag
        ctx.textAlign = 'center';
        ctx.fillStyle = '#f59e0b';
        ctx.font = 'bold 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        ctx.letterSpacing = '3px';
        ctx.fillText('👑  OFFICIAL FAMILY SANCTUARY', W / 2, 95);

        // 4. Large Gold Dynasty Title
        const titleGrad = ctx.createLinearGradient(W / 2 - 250, 0, W / 2 + 250, 0);
        titleGrad.addColorStop(0, '#fef08a');
        titleGrad.addColorStop(0.5, '#eab308');
        titleGrad.addColorStop(1, '#ca8a04');
        ctx.fillStyle = titleGrad;
        ctx.font = 'bold 54px "Georgia", serif';
        ctx.fillText(`THE ${familySurname.toUpperCase()} DYNASTY`, W / 2, 165);

        // 5. Subtitle
        ctx.fillStyle = '#94a3b8';
        ctx.font = '500 22px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.fillText('Recorded Heritage, Lineage Connections & Living Memories', W / 2, 210);

        // 6. Stats Grid Cards
        const cardY = 270;
        const cardH = 155;
        const cardW = 310;
        const gap = 35;
        const startX = (W - (cardW * 3 + gap * 2)) / 2;

        drawStatBox(ctx, startX, cardY, cardW, cardH, String(memberCount), 'MEMBERS CONNECTED', '👥');
        drawStatBox(ctx, startX + cardW + gap, cardY, cardW, cardH, String(genCount), 'GENERATIONS', '🌳');
        drawStatBox(ctx, startX + (cardW + gap) * 2, cardY, cardW, cardH, String(hubsCount), 'CITIES & HUBS', '🌍');

        // 7. Call To Action Footer Banner
        ctx.fillStyle = '#ffffff';
        ctx.font = '600 24px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.fillText('Join our family circle & claim your place on the family tree', W / 2, 495);

        ctx.fillStyle = '#10b981';
        ctx.font = 'bold 20px monospace';
        ctx.fillText(flyerInviteUrl.replace(/^https?:\/\//, ''), W / 2, 535);

        ctx.fillStyle = '#64748b';
        ctx.font = '400 16px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.fillText('Private & Walled Sanctuary • Zero Tree Pollution • FamilyPlatform', W / 2, 585);

        flyerRendered = true;
    }

    function drawStatBox(ctx, x, y, w, h, value, label, icon) {
        ctx.save();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
        ctx.strokeStyle = 'rgba(212, 175, 55, 0.4)';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.roundRect(x, y, w, h, 14);
        ctx.fill();
        ctx.stroke();

        ctx.textAlign = 'center';
        ctx.fillStyle = '#fbbf24';
        ctx.font = 'bold 44px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.fillText(`${icon} ${value}`, x + w / 2, y + 65);

        ctx.fillStyle = '#cbd5e1';
        ctx.font = 'bold 15px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        ctx.letterSpacing = '1px';
        ctx.fillText(label, x + w / 2, y + 115);
        ctx.restore();
    }

    function drawCornerFlourish(ctx, x, y, flipX = false, flipY = false) {
        ctx.save();
        ctx.translate(x, y);
        ctx.scale(flipX ? -1 : 1, flipY ? -1 : 1);
        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 2.5;
        ctx.beginPath();
        ctx.moveTo(0, 25);
        ctx.lineTo(0, 0);
        ctx.lineTo(25, 0);
        ctx.stroke();
        ctx.restore();
    }

    window.downloadFlyerImage = function() {
        const canvas = document.getElementById('familyFlyerCanvas');
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = 'Family_Dynasty_Status_Flyer.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    };

    window.copyFlyerInviteLink = function() {
        navigator.clipboard.writeText(flyerInviteUrl).then(() => {
            const alertEl = document.getElementById('flyerCopiedAlert');
            if (alertEl) {
                alertEl.classList.remove('d-none');
                setTimeout(() => alertEl.classList.add('d-none'), 3500);
            }
        }).catch(() => {
            prompt('Copy invite link:', flyerInviteUrl);
        });
    };

    window.shareFlyerToWhatsApp = function() {
        const canvas = document.getElementById('familyFlyerCanvas');
        if (!canvas) return;

        const shareText = `🎉 Check out our official family dynasty status! We have our family tree and memories connected on FamilyPlatform. Tap here to join our family circle: ${flyerInviteUrl}`;

        // Attempt Web Share API with image file if supported
        if (navigator.canShare && canvas.toBlob) {
            canvas.toBlob(blob => {
                if (blob && navigator.canShare({ files: [new File([blob], 'family_flyer.png', { type: 'image/png' })] })) {
                    const file = new File([blob], 'family_flyer.png', { type: 'image/png' });
                    navigator.share({
                        title: 'Our Family Tree Status',
                        text: shareText,
                        files: [file]
                    }).catch(err => {
                        // User cancelled or fallback
                        fallbackWhatsAppShare(shareText);
                    });
                    return;
                }
                fallbackWhatsAppShare(shareText);
            }, 'image/png');
        } else {
            fallbackWhatsAppShare(shareText);
        }
    };

    function fallbackWhatsAppShare(text) {
        const waUrl = 'https://api.whatsapp.com/send?text=' + rawurlencode(text);
        window.open(waUrl, '_blank', 'noopener,noreferrer');
    }

    function rawurlencode(str) {
        return encodeURIComponent(str).replace(/[!'()*]/g, c => '%' + c.charCodeAt(0).toString(16).toUpperCase());
    }
})();
</script>
