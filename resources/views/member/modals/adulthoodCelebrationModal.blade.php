<!-- 🎓 Adulthood Graduation & 18th Birthday Milestone Modal -->
@if(!empty($adulthoodGraduation) && !empty($adulthoodGraduation['graduated']))
<div class="modal fade show" id="adulthoodMilestoneModal" tabindex="-1" aria-labelledby="adulthoodMilestoneTitle" aria-modal="true" style="display: block; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-2xl" style="border-radius: var(--radius-xl, 20px); overflow: hidden;">
            <!-- Festive Header Banner -->
            <div style="background: linear-gradient(135deg, #7a0e16 0%, #b8202c 100%); color: #ffffff; padding: 2rem 1.5rem 1.75rem; text-align: center; position: relative;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem; animation: bounce 1s infinite alternate;">🎂✨</div>
                <h3 id="adulthoodMilestoneTitle" class="fw-bold text-white mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">
                    Happy 18th Birthday, {{ $adulthoodGraduation['full_name'] ?? 'Family Member' }}!
                </h3>
                <p class="text-white-50 mb-0 small" style="font-weight: 500;">
                    A sacred milestone in your family's living lineage
                </p>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 text-center">
                <div class="modal-sub-card mb-3 p-3 text-start">
                    <div class="d-flex align-items-start gap-3">
                        <div style="font-size: 1.5rem; flex-shrink: 0;">👑</div>
                        <div>
                            <h6 class="fw-bold mb-1">Full Sovereign Adulthood</h6>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">
                                You are now officially recognised as an adult family member. You hold complete sovereign data ownership and can contribute, invite, and document your family heritage with full privileges.
                            </p>
                        </div>
                    </div>
                </div>

                @if(!empty($adulthoodGraduation['unsealed_capsules']) && $adulthoodGraduation['unsealed_capsules'] > 0)
                <div class="modal-sub-card-amber mb-4 p-3 text-start">
                    <div class="d-flex align-items-start gap-3">
                        <div style="font-size: 1.5rem; flex-shrink: 0;">🎁</div>
                        <div>
                            <h6 class="fw-bold mb-1">18th Birthday Time Capsule Unsealed!</h6>
                            <p class="small mb-0" style="line-height: 1.5;">
                                Your family sealed {{ $adulthoodGraduation['unsealed_capsules'] }} heirloom memory capsule(s) during your childhood marked <em>"Deliver on 18th Birthday"</em>. They are now unsealed and waiting for you!
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <p class="text-muted small mb-4">
                    Your account has transitioned from a supervised family profile to an independent adult account. Celebrate this momentous occasion with your family!
                </p>

                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn text-white fw-bold py-2 shadow-sm"
                            onclick="closeAdulthoodModal()"
                            style="min-height: 44px; background: var(--brand-primary, #7a0e16); border-radius: 12px; font-size: 1rem;">
                        Enter Family Space & Celebrate 🎉
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ $nonce ?? '' }}">
function closeAdulthoodModal() {
    const modal = document.getElementById('adulthoodMilestoneModal');
    if (modal) {
        modal.style.transition = 'opacity 0.25s ease';
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 250);
    }
}
</script>
@endif
