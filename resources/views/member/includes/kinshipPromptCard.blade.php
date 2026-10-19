<!-- Kinship Inner Circle Prompt Card -->
<div class="card border-0 shadow-sm mb-4 kinship-prompt-card overflow-hidden" 
     id="kinshipPromptCard"
     style="border-radius: 20px; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4338ca 100%); color: #ffffff; border: 1.5px solid rgba(129, 140, 248, 0.4); display: none;">
    <div class="card-body p-4 position-relative">
        
        <!-- Dismiss Button -->
        <button type="button" 
                class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                onclick="dismissKinshipPrompt()" 
                aria-label="Dismiss prompt"
                title="Dismiss"></button>

        <div class="d-flex align-items-start gap-3">
            <!-- Icon Bubble -->
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                 style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.5); font-size: 1.35rem;">
                <i class="bi bi-people-fill"></i>
            </div>

            <!-- Content Area -->
            <div class="flex-grow-1 pe-4">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge rounded-pill bg-warning text-dark fw-bold px-2 py-0.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        RECOMMENDED
                    </span>
                    <h5 class="fw-bold mb-0 text-white" style="font-size: 1.12rem; letter-spacing: -0.01em;">
                        Build Your Inner Circle: Invite Your Family
                    </h5>
                </div>
                <p class="text-white-50 small mb-3" style="font-size: 0.86rem; line-height: 1.45; max-width: 580px;">
                    Have you added your parents, siblings, or children? Invite them directly from your phone contacts or WhatsApp to explore your shared family tree and preserve cherished memories.
                </p>

                <!-- Actions -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" 
                            class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm d-inline-flex align-items-center gap-2 transition-all"
                            style="min-height: 44px; white-space: nowrap; flex-shrink: 0; font-size: 0.88rem;"
                            data-bs-toggle="modal" 
                            data-bs-target="#inviteFamilyModal">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Invite from Phone Contacts</span>
                    </button>

                    <button type="button" 
                            class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-2 transition-all"
                            style="min-height: 44px; white-space: nowrap; flex-shrink: 0; font-size: 0.88rem; border-color: rgba(255, 255, 255, 0.35);"
                            data-bs-toggle="modal" 
                            data-bs-target="#inviteFamilyModal">
                        <i class="bi bi-whatsapp" style="color: #4ade80;"></i>
                        <span>WhatsApp Kin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initKinshipPrompt() {
        const card = document.getElementById('kinshipPromptCard');
        if (!card) return;

        // Check if previously dismissed in this browser
        try {
            if (localStorage.getItem('fp_kinship_prompt_dismissed') === 'true') {
                card.style.display = 'none';
                return;
            }
        } catch (e) {}

        // Display card
        card.style.display = 'block';
    }

    window.dismissKinshipPrompt = function() {
        const card = document.getElementById('kinshipPromptCard');
        if (card) {
            card.style.display = 'none';
            try {
                localStorage.setItem('fp_kinship_prompt_dismissed', 'true');
            } catch (e) {}
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKinshipPrompt);
    } else {
        initKinshipPrompt();
    }
})();
</script>
