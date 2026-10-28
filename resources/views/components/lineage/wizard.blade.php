{{-- Scoped Family Invitation Modal Component --}}
<div class="modal fade" id="addRelativeModal" tabindex="-1" aria-labelledby="addRelativeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="backdrop-filter: blur(8px);">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: rgba(255, 255, 255, 0.98); max-height: calc(100vh - 3.5rem);">
            <div class="modal-header border-0 text-white flex-shrink-0" style="background: linear-gradient(135deg, #a31621 0%, #b8202c 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-send-fill fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="addRelativeModalLabel">Invite Family Member</h5>
                        <div class="small text-white-50" style="font-size: 0.75rem;">Generate an instant WhatsApp or email invitation to claim their spot</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="overflow-y: auto;">
                <div id="inviteFamilyError" class="alert alert-danger d-none" style="border-radius: 8px;"></div>

                {{-- Step A: Invitation Form --}}
                <div id="inviteFamilyFormSection">
                    {{-- Target Family Tree Selector --}}
                    @php
                        $activeCode = $familyCode ?? ($primaryFamCode ?? ($data['famCode'] ?? ''));
                        $currentScope = $activeLineageScope ?? 'primary';
                    @endphp
                    <div class="mb-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
                            <label class="form-label fw-bold small text-dark mb-0">
                                <i class="bi bi-diagram-3-fill me-1 text-primary"></i>Which family tree are you inviting them to?
                            </label>
                            <span class="badge bg-primary rounded-pill" id="inviteActiveCodeBadge" style="font-size: 0.72rem;">
                                #{{ $activeCode }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2" role="radiogroup" aria-label="Target family tree">
                            <label class="invite-tree-option d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 border bg-white" style="cursor: pointer; min-height: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
                                <input type="radio" name="invite_target_code_radio" value="{{ $primaryFamCode ?? ($data['famCode'] ?? '') }}" data-scope="primary" data-surname="{{ $maritalSurname ?? ($data['lastName'] ?? '') }}" {{ $activeCode === ($primaryFamCode ?? ($data['famCode'] ?? '')) ? 'checked' : '' }} onchange="syncInviteTarget(this)">
                                <span><strong>Marital / Household</strong> <span class="text-muted">(#{{ $primaryFamCode ?? ($data['famCode'] ?? '') }})</span></span>
                            </label>
                            @if(!empty($maternalFamCode) && $maternalFamCode !== ($primaryFamCode ?? ($data['famCode'] ?? '')))
                            <label class="invite-tree-option d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 border bg-white" style="cursor: pointer; min-height: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
                                <input type="radio" name="invite_target_code_radio" value="{{ $maternalFamCode }}" data-scope="maternal" data-surname="{{ $maidenSurname ?? '' }}" {{ $activeCode === $maternalFamCode ? 'checked' : '' }} onchange="syncInviteTarget(this)">
                                <span><strong>Birth / Maiden Family</strong> <span class="text-muted">(#{{ $maternalFamCode }})</span></span>
                            </label>
                            @endif
                            @if(!empty($wifeFamCode) && $wifeFamCode !== ($primaryFamCode ?? ($data['famCode'] ?? '')) && $wifeFamCode !== ($maternalFamCode ?? ''))
                            <label class="invite-tree-option d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 border bg-white" style="cursor: pointer; min-height: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
                                <input type="radio" name="invite_target_code_radio" value="{{ $wifeFamCode }}" data-scope="inlaw" data-surname="" {{ $activeCode === $wifeFamCode ? 'checked' : '' }} onchange="syncInviteTarget(this)">
                                <span><strong>In-Law Lineage</strong> <span class="text-muted">(#{{ $wifeFamCode }})</span></span>
                            </label>
                            @endif
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small text-dark">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="inviteFirstName" class="form-control" placeholder="e.g. Samuel" required style="border-radius: 8px; min-height: 44px;">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small text-dark">Last Name</label>
                            <input type="text" id="inviteLastName" class="form-control" placeholder="e.g. Akinduro" value="{{ ($activeCode === ($maternalFamCode ?? '')) ? ($maidenSurname ?? '') : ($maritalSurname ?? '') }}" style="border-radius: 8px; min-height: 44px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Relationship / Kinship Role</label>
                            <select id="inviteRelationship" class="form-select" style="border-radius: 8px; min-height: 44px;">
                                <option value="Relative">Family Relative (General)</option>
                                <option value="Parent">Parent (Father / Mother)</option>
                                <option value="Sibling">Sibling (Brother / Sister)</option>
                                <option value="Child">Child (Son / Daughter)</option>
                                <option value="Partner">Partner / Spouse</option>
                                <option value="Aunt / Uncle">Auntie / Uncle</option>
                                <option value="In-Law">In-Law (Brother / Sister / Parent-in-law)</option>
                                <option value="Cousin">Cousin</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-envelope-fill me-1 text-primary"></i>Email Address <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input type="email" id="inviteEmail" class="form-control" placeholder="relative@example.com" style="border-radius: 8px; min-height: 44px;">
                            <div class="form-text" style="font-size: 0.75rem;">
                                If supplied, an invitation email with their personalised claim link is dispatched automatically.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-whatsapp me-1 text-success"></i>WhatsApp / Mobile Number <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <input type="tel" id="inviteMobile" class="form-control" placeholder="e.g. +44 7123 456789" style="border-radius: 8px; min-height: 44px;">
                            <div class="form-text" style="font-size: 0.75rem;">
                                For your records or direct messaging. You can also send directly via WhatsApp below.
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" id="btnSubmitInviteFamily" onclick="submitInviteFamilyForm()" class="btn btn-primary fw-bold w-100 py-3 shadow-sm d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #a31621 0%, #b8202c 100%); border: none; border-radius: 12px; font-size: 1rem; min-height: 48px;">
                            <i class="bi bi-send-fill me-2"></i> Generate Invitation
                        </button>
                    </div>
                </div>

                {{-- Step B: Instant Output Section (Revealed on success) --}}
                <div id="inviteFamilyOutputSection" class="d-none">
                    <div class="text-center mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success mb-2" style="width: 56px; height: 56px;">
                            <i class="bi bi-check2-circle fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Invitation Ready!</h5>
                        <p class="text-muted small mb-0" id="inviteOutputSubtext">Your invitation link has been generated.</p>
                    </div>

                    {{-- Email Dispatch Notice (if email was sent) --}}
                    <div id="inviteEmailSentNotice" class="alert alert-success d-none d-flex align-items-center p-3 mb-3" style="border-radius: 12px;">
                        <i class="bi bi-envelope-check-fill fs-4 me-2 text-success flex-shrink-0"></i>
                        <div class="small">
                            <strong>Invitation Email Dispatched!</strong><br>
                            A formal invitation email has been sent to <span id="inviteRecipientEmailText" class="fw-bold"></span>.
                        </div>
                    </div>

                    {{-- WhatsApp Share Button --}}
                    <a id="inviteWhatsAppShareLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold w-100 py-3 mb-2 shadow-sm d-flex align-items-center justify-content-center text-decoration-none" style="background: #25D366; border: none; border-radius: 12px; font-size: 1.05rem; min-height: 50px;">
                        <i class="bi bi-whatsapp fs-4 me-2"></i> Send via WhatsApp
                    </a>

                    {{-- Copy Link Button --}}
                    <button type="button" id="inviteCopyLinkBtn" onclick="copyGeneratedInviteLink(this)" class="btn btn-outline-dark fw-bold w-100 py-2 d-flex align-items-center justify-content-center mb-3" style="border-radius: 12px; min-height: 44px;">
                        <i class="bi bi-link-45deg fs-4 me-2"></i> Copy Invitation Link
                    </button>

                    {{-- Link Display Box --}}
                    <div class="p-2 px-3 rounded-3 mb-3 text-break small text-muted" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-family: monospace; font-size: 0.8rem;" id="inviteGeneratedUrlText">
                    </div>

                    {{-- Reset / Invite Another --}}
                    <button type="button" onclick="resetInviteFamilyForm()" class="btn btn-light border w-100 py-2 small fw-semibold text-secondary" style="border-radius: 10px;">
                        <i class="bi bi-person-plus me-1"></i> Invite Another Relative
                    </button>
                </div>

                {{-- Direct Node Adding Restriction Notice --}}
                <div class="p-3 mt-4 rounded-3 text-center" style="background: #f8fafc; border: 1px dashed #cbd5e1; font-size: 0.82rem; color: #64748b;">
                    <i class="bi bi-shield-lock-fill text-muted me-1"></i> <strong>Adding relatives directly to the tree?</strong><br>
                    Direct tree modifications and offline ancestors are managed in
                    <a href="/familyStudio" class="fw-bold text-primary text-decoration-underline">Family Studio</a> or
                    <a href="/accountSetting" class="fw-bold text-primary text-decoration-underline">Account Settings</a>.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let activeInviteTargetCode = '{{ $activeCode }}';
    let activeInviteTargetScope = '{{ $currentScope }}';
    let lastGeneratedInviteUrl = '';

    function syncInviteTarget(radio) {
        if (!radio) return;
        activeInviteTargetCode = radio.value;
        activeInviteTargetScope = radio.getAttribute('data-scope') || 'primary';
        const badge = document.getElementById('inviteActiveCodeBadge');
        if (badge) badge.textContent = '#' + activeInviteTargetCode;
        const surname = radio.getAttribute('data-surname');
        const lastNameInput = document.getElementById('inviteLastName');
        if (surname && lastNameInput && !lastNameInput.value) {
            lastNameInput.value = surname;
        }
    }

    function openInviteFamilyModal(preferredScope, preferredRelation) {
        const errorDiv = document.getElementById('inviteFamilyError');
        if (errorDiv) errorDiv.classList.add('d-none');

        resetInviteFamilyForm();

        if (preferredRelation) {
            const relSelect = document.getElementById('inviteRelationship');
            if (relSelect) relSelect.value = preferredRelation;
        }

        const modalEl = document.getElementById('addRelativeModal');
        if (modalEl) {
            const myModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            myModal.show();
        }
    }

    // Alias for existing callers
    function openAddRelativeModalFromBanner(relationType) {
        openInviteFamilyModal(null, relationType);
    }

    function openInviteRelativeFromNode() {
        const nodeName = typeof currentBaseNodeName !== 'undefined' ? currentBaseNodeName : '';
        openInviteFamilyModal();
        if (nodeName && nodeName !== 'Yourself') {
            const parts = nodeName.split(' ');
            const fnInput = document.getElementById('inviteFirstName');
            const lnInput = document.getElementById('inviteLastName');
            if (fnInput && parts[0]) fnInput.value = parts[0];
            if (lnInput && parts.length > 1) lnInput.value = parts.slice(1).join(' ');
        }
    }

    function resetInviteFamilyForm() {
        document.getElementById('inviteFamilyFormSection')?.classList.remove('d-none');
        document.getElementById('inviteFamilyOutputSection')?.classList.add('d-none');
        document.getElementById('inviteEmailSentNotice')?.classList.add('d-none');
        const fnInput = document.getElementById('inviteFirstName');
        const emailInput = document.getElementById('inviteEmail');
        const mobInput = document.getElementById('inviteMobile');
        if (fnInput) fnInput.value = '';
        if (emailInput) emailInput.value = '';
        if (mobInput) mobInput.value = '';
        const errorDiv = document.getElementById('inviteFamilyError');
        if (errorDiv) errorDiv.classList.add('d-none');
    }

    async function submitInviteFamilyForm() {
        const firstName = document.getElementById('inviteFirstName')?.value.trim();
        const lastName = document.getElementById('inviteLastName')?.value.trim();
        const relationship = document.getElementById('inviteRelationship')?.value;
        const email = document.getElementById('inviteEmail')?.value.trim();
        const mobile = document.getElementById('inviteMobile')?.value.trim();
        const errorDiv = document.getElementById('inviteFamilyError');
        const submitBtn = document.getElementById('btnSubmitInviteFamily');

        if (!firstName) {
            if (errorDiv) {
                errorDiv.textContent = 'Please enter the relative\'s first name.';
                errorDiv.classList.remove('d-none');
            }
            return;
        }

        if (errorDiv) errorDiv.classList.add('d-none');

        if (window.ScoreTenantSpinner) {
            ScoreTenantSpinner.start(submitBtn);
        } else if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating...';
        }

        try {
            const csrfToken = (function() {
                const el = document.querySelector('meta[name="csrf-token"]') || document.querySelector('input[name="_token"]');
                return el ? (el.getAttribute('content') || el.value) : '';
            })();

            const res = await fetch('/api/invite/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken,
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    mobile: mobile,
                    relationship: relationship,
                    family_code: activeInviteTargetCode,
                    lineage_scope: activeInviteTargetScope,
                    type: 'organogram',
                    token: csrfToken
                })
            });

            const data = await res.json();
            const payload = (data && data.message && typeof data.message === 'object') ? data.message : data;

            if (res.ok && data && (data.status === 'success' || payload.invite_url)) {
                lastGeneratedInviteUrl = payload.invite_url || '';
                
                // Show output
                document.getElementById('inviteFamilyFormSection')?.classList.add('d-none');
                const outSection = document.getElementById('inviteFamilyOutputSection');
                outSection?.classList.remove('d-none');

                const subtext = document.getElementById('inviteOutputSubtext');
                if (subtext) {
                    subtext.textContent = `Invitation created for ${firstName} ${lastName} (${payload.tree_label || '#' + activeInviteTargetCode}).`;
                }

                // Email notice
                const emailNotice = document.getElementById('inviteEmailSentNotice');
                if (payload.email_sent && email) {
                    const recipientText = document.getElementById('inviteRecipientEmailText');
                    if (recipientText) recipientText.textContent = email;
                    emailNotice?.classList.remove('d-none');
                } else {
                    emailNotice?.classList.add('d-none');
                }

                // WhatsApp Link (Beautiful WhatsApp View)
                const waLink = document.getElementById('inviteWhatsAppShareLink');
                if (waLink) {
                    const waShareUrl = payload.whatsapp_share_url || (
                        'https://api.whatsapp.com/send?text=' + encodeURIComponent(
                            payload.whatsapp_message || (
                                `🌳 *Family Tree Invitation* 🌳\n\n` +
                                `You are invited to connect with the *${payload.tree_label || 'our Family Tree'}* on FamilyPlatform (#${activeInviteTargetCode}).\n\n` +
                                `Click below to claim your spot, explore our lineage, and connect with the family:\n` +
                                `👉 ${payload.invite_url}`
                            )
                        )
                    );
                    waLink.href = waShareUrl;
                }

                // URL Text
                const urlText = document.getElementById('inviteGeneratedUrlText');
                if (urlText) {
                    urlText.textContent = payload.invite_url;
                }

                if (window.showAppToast) {
                    window.showAppToast('Invitation link generated successfully!', 'success');
                }
            } else {
                throw new Error((payload && payload.message) || data.message || 'Could not generate invitation.');
            }
        } catch (err) {
            if (errorDiv) {
                errorDiv.textContent = err.message || 'An unexpected error occurred.';
                errorDiv.classList.remove('d-none');
            }
        } finally {
            if (window.ScoreTenantSpinner) {
                ScoreTenantSpinner.stop(submitBtn);
            } else if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i> Generate Invitation';
            }
        }
    }

    function copyGeneratedInviteLink(btn) {
        if (!lastGeneratedInviteUrl) return;
        navigator.clipboard.writeText(lastGeneratedInviteUrl).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-4 me-2"></i> Copied to Clipboard!';
            btn.classList.add('btn-success', 'text-white');
            btn.classList.remove('btn-outline-dark');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-dark');
            }, 3000);
        }).catch(() => {
            prompt('Copy this invitation link:', lastGeneratedInviteUrl);
        });
    }
</script>
