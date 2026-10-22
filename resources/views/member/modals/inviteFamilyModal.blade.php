<!-- Invite Family & Phone Contacts Modal -->
<div class="modal fade" id="inviteFamilyModal" tabindex="-1" aria-labelledby="inviteFamilyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background: #ffffff; max-height: calc(100vh - 3.5rem);">
            @include('csrf')
            
            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-3 flex-shrink-0" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                         style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; background: rgba(245, 158, 11, 0.25); color: #fbbf24; border: 1.5px solid rgba(245, 158, 11, 0.5); font-size: 1.35rem;">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white" id="inviteFamilyModalLabel" style="font-size: 1.25rem; letter-spacing: -0.01em;">
                                Invite Family &amp; Relatives
                            </h5>
                            <span class="badge rounded-pill bg-warning text-dark fw-bold px-2 py-0.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                PRIVATE SANCTUARY
                            </span>
                        </div>
                        <p class="mb-0 text-white-50 small" style="font-size: 0.85rem;">
                            Add your parents, siblings, children, or kin to your private family hub.
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3" style="overflow-y: auto;">
                
                <!-- 1. Sanctuary Privacy Guarantee Card -->
                <div class="p-3 mb-4 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                     style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0"
                             style="width: 40px; height: 40px; background: #dcfce7; font-size: 1.2rem;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark small" style="font-size: 0.9rem;">
                                100% Private Family Network
                            </div>
                            <div class="text-muted small" style="font-size: 0.78rem;">
                                Relatives join your family with private code: <span class="badge bg-light text-primary border font-monospace fw-bold" id="modalFamilyCodeBadge">{{ $data['famCode'] ?? '...' }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                        End-to-End Privacy
                    </span>
                </div>

                <!-- 2. Kinship Role Personaliser Chips -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-stars text-warning"></i>
                        <span>Who are you inviting? (Personalises message)</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2" id="kinshipChipsContainer">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold kinship-chip active" data-role="General" data-label="Family Member" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            🌟 General Family
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Mum" data-label="Mum" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👑 Mum
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Dad" data-label="Dad" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👑 Dad
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Sister" data-label="Sister" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👭 Sister
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Brother" data-label="Brother" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👬 Brother
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Child" data-label="Son / Daughter" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👶 Son / Daughter
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Grandparent" data-label="Grandparent" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            👵 Grandparent
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold kinship-chip" data-role="Cousin" data-label="Cousin" style="min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                            🤝 Cousin / Relative
                        </button>
                    </div>
                </div>

                <!-- 3. Native Phone Contacts Hero Section (Shown on supported Chromium / Android devices) -->
                <div id="contactPickerSupportedSection" class="p-3 mb-4 rounded-3 d-none"
                     style="background: linear-gradient(135deg, #fefce8 0%, #fffbeb 100%); border: 1.5px solid #fde047; border-radius: 16px;">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.72rem;">
                                NATIVE FEATURE
                            </span>
                            <span class="fw-bold text-dark small" style="font-size: 0.92rem;">
                                Phone Address Book Integration
                            </span>
                        </div>
                        <small class="text-muted">100% Private (device-only)</small>
                    </div>
                    <p class="text-secondary small mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                        Select 1 or more relatives directly from your phone's address book to dispatch an instant, personalised SMS or WhatsApp invitation.
                    </p>
                    <button type="button" 
                            id="btnNativeContactPicker" 
                            class="btn btn-warning w-100 py-2.5 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm text-dark transition-all"
                            style="min-height: 48px; border-radius: 12px; font-size: 0.98rem; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);">
                        <i class="bi bi-person-bounding-box fs-5"></i>
                        <span>Select Relatives from Phone Contacts</span>
                    </button>
                </div>

                <!-- Selected Contacts Tray -->
                <div id="selectedContactsContainer" class="d-none mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="fw-bold mb-0 text-dark small" style="font-size: 0.9rem;">
                            <i class="bi bi-people-fill text-primary me-1"></i> Selected Contacts (<span id="selectedContactsCount">0</span>)
                        </h6>
                        <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" id="btnClearSelectedContacts" style="font-size: 0.78rem;">
                            Clear All
                        </button>
                    </div>
                    <div class="d-flex flex-column gap-2" id="selectedContactsList">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- 4. Universal 1-Tap Share Actions (WhatsApp, SMS, Web Share, Copy Link) -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-send-fill text-primary"></i>
                        <span>Quick 1-Tap Invite Channels</span>
                    </label>
                    <div class="row g-2">
                        <!-- WhatsApp Direct -->
                        <div class="col-12 col-sm-6">
                            <a href="#" target="_blank" id="btnInviteWhatsApp" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold text-white shadow-xs transition-all"
                               style="background: #25D366; border-radius: 12px; min-height: 46px; font-size: 0.92rem; text-decoration: none;">
                                <i class="bi bi-whatsapp fs-5"></i>
                                <span style="white-space: nowrap;">Share via WhatsApp</span>
                            </a>
                        </div>
                        <!-- SMS / iMessage Direct -->
                        <div class="col-12 col-sm-6">
                            <a href="#" id="btnInviteSMS" class="btn w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-bold text-white shadow-xs transition-all"
                               style="background: #0284c7; border-radius: 12px; min-height: 46px; font-size: 0.92rem; text-decoration: none;">
                                <i class="bi bi-chat-text-fill fs-5"></i>
                                <span style="white-space: nowrap;">Send via SMS / iMessage</span>
                            </a>
                        </div>
                        <!-- Native Device Share Sheet -->
                        <div class="col-12 col-sm-6">
                            <button type="button" id="btnInviteWebShare" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-xs transition-all"
                                    style="border-radius: 12px; min-height: 46px; font-size: 0.92rem;">
                                <i class="bi bi-share-fill"></i>
                                <span style="white-space: nowrap;">Device Share Sheet</span>
                            </button>
                        </div>
                        <!-- Copy Invite Link -->
                        <div class="col-12 col-sm-6">
                            <button type="button" id="btnCopyInviteLink" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold shadow-xs transition-all"
                                    style="border-radius: 12px; min-height: 46px; font-size: 0.92rem;">
                                <i class="bi bi-link-45deg fs-5"></i>
                                <span id="copyLinkBtnText" style="white-space: nowrap;">Copy Invite Link</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Manual Direct Relative Add & Text -->
                <div class="p-3 mb-4 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;">
                    <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-telephone-plus-fill text-secondary"></i>
                        <span>Or Send to a Specific Number</span>
                    </label>
                    <div class="row g-2">
                        <div class="col-12 col-sm-5">
                            <input type="text" id="manualRelativeName" class="form-control form-control-sm" placeholder="Relative Name (e.g. Auntie Mary)" style="min-height: 44px; border-radius: 10px; font-size: 0.88rem;">
                        </div>
                        <div class="col-12 col-sm-5">
                            <input type="tel" id="manualRelativePhone" class="form-control form-control-sm" placeholder="Mobile (+44 7123 456789)" style="min-height: 44px; border-radius: 10px; font-size: 0.88rem;">
                        </div>
                        <div class="col-12 col-sm-2 d-flex gap-1">
                            <button type="button" id="btnSendManualSMS" class="btn btn-primary w-100 d-flex align-items-center justify-content-center" title="Send SMS" style="min-height: 44px; border-radius: 10px;" aria-label="Send SMS to entered number">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 6. Real-Time Invitation Message Preview -->
                <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label text-muted fw-semibold small mb-0" style="font-size: 0.8rem;">
                            Message Preview (Sent to relative)
                        </label>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">256-bit signed</span>
                    </div>
                    <div class="p-3 rounded-3" style="background: #f1f5f9; border: 1px solid #cbd5e1; font-size: 0.86rem; color: #334155; line-height: 1.45;" id="inviteMessagePreview">
                        Loading personalised invitation link...
                    </div>
                </div>

                <!-- Feedback Toast Alert -->
                <div id="inviteFeedbackAlert" class="alert alert-success d-none py-2 px-3 mt-3 text-center small rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="inviteFeedbackText">Link copied to clipboard!</span>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between flex-shrink-0">
                <span class="text-muted small d-flex align-items-center gap-1">
                    <i class="bi bi-shield-lock-fill text-success"></i>
                    <span>Zero PII logged on server</span>
                </span>
                <button type="button" class="btn btn-secondary px-4 fw-semibold rounded-pill" data-bs-dismiss="modal" style="min-height: 44px;">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let inviteData = {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $data["famCode"] ?? "" }}',
        surname: '{{ $data["familySurname"] ?? ($data["lastName"] ?? "Family") }}'
    };
    let currentRole = 'General';
    let selectedContacts = [];

    // Kinship template dictionary (British English copy)
    const kinshipTemplates = {
        'General': (surname, url) => `👋 Hey! I've set up our private ${surname} Family Network & Heritage Tree on FamilyPlatform. Claim your branch, explore our lineage, and share memories with us: ${url}`,
        'Mum': (surname, url) => `👋 Hi Mum! I've set up our private ${surname} family tree and sanctuary on FamilyPlatform. Tap here to view our lineage, see photos, and claim your profile: ${url}`,
        'Dad': (surname, url) => `👋 Hi Dad! I've set up our private ${surname} family tree and sanctuary on FamilyPlatform. Tap here to view our lineage, see photos, and claim your profile: ${url}`,
        'Sister': (surname, url) => `👭 Hey sis! Join me on FamilyPlatform for our private ${surname} family hub. Tap here to claim your spot on our family tree: ${url}`,
        'Brother': (surname, url) => `👬 Hey bro! Join me on FamilyPlatform for our private ${surname} family hub. Tap here to claim your spot on our family tree: ${url}`,
        'Child': (surname, url) => `🌟 Hey! I've added you to our official ${surname} family tree on FamilyPlatform. Tap here to claim your profile and see our family heritage: ${url}`,
        'Grandparent': (surname, url) => `👵 Dear Grandparent, our ${surname} family tree has been preserved on FamilyPlatform! Tap here to see our family branches: ${url}`,
        'Cousin': (surname, url) => `👋 Hey cousin! We are bringing the ${surname} family together on our private FamilyPlatform hub. Tap here to claim your spot: ${url}`
    };

    function getCleanPhoneNumber(phone) {
        if (!phone) return '';
        return phone.replace(/[^0-9+]/g, '');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[ch]));
    }

    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    }

    function buildSMSUri(phone, body) {
        const clean = getCleanPhoneNumber(phone);
        const encodedBody = encodeURIComponent(body);
        const delimiter = isIOS() ? '&' : '?';
        return clean ? `sms:${clean}${delimiter}body=${encodedBody}` : `sms:${delimiter}body=${encodedBody}`;
    }

    function buildWhatsAppUri(phone, text) {
        const clean = getCleanPhoneNumber(phone).replace(/^\+/, '');
        const encodedText = encodeURIComponent(text);
        return clean ? `https://wa.me/${clean}?text=${encodedText}` : `https://api.whatsapp.com/send?text=${encodedText}`;
    }

    function getPersonalisedMessage() {
        const url = inviteData.invite_url || window.location.origin + '/register';
        const surname = inviteData.surname || 'Family';
        const generator = kinshipTemplates[currentRole] || kinshipTemplates['General'];
        return generator(surname, url);
    }

    function updateMessageUI() {
        const msg = getPersonalisedMessage();
        const previewEl = document.getElementById('inviteMessagePreview');
        if (previewEl) {
            previewEl.textContent = msg;
        }

        // Update WhatsApp & SMS links
        const waBtn = document.getElementById('btnInviteWhatsApp');
        if (waBtn) {
            waBtn.href = buildWhatsAppUri('', msg);
        }

        const smsBtn = document.getElementById('btnInviteSMS');
        if (smsBtn) {
            smsBtn.href = buildSMSUri('', msg);
        }
    }

    async function recordInviteStep() {
        try {
            const csrfToken = document.querySelector('meta[name="token"]')?.getAttribute('content') 
                           || document.querySelector('input[name="token"]')?.value || '';
            const formData = new FormData();
            formData.append('token', csrfToken);
            formData.append('step', 'invite_family');

            await fetch('/api/onboarding/step', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
        } catch (e) {
            console.warn('[InviteFamily] Step record error:', e);
        }
    }

    async function loadInviteData() {
        try {
            const res = await fetch('/api/onboarding/state', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const json = await res.json();
                if (json.status === 'success' && json.data?.invite_data) {
                    inviteData = Object.assign(inviteData, json.data.invite_data);
                    const codeBadge = document.getElementById('modalFamilyCodeBadge');
                    if (codeBadge && inviteData.family_code) {
                        codeBadge.textContent = inviteData.family_code;
                    }
                    updateMessageUI();
                }
            }
        } catch (err) {
            console.warn('[InviteFamily] Failed loading invite data:', err);
            updateMessageUI();
        }
    }

    function renderSelectedContacts() {
        const container = document.getElementById('selectedContactsContainer');
        const list = document.getElementById('selectedContactsList');
        const countBadge = document.getElementById('selectedContactsCount');
        if (!container || !list) return;

        if (selectedContacts.length === 0) {
            container.classList.add('d-none');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('d-none');
        if (countBadge) countBadge.textContent = String(selectedContacts.length);

        list.innerHTML = '';
        selectedContacts.forEach((contact, idx) => {
            const name = (contact.name && contact.name[0]) ? contact.name[0] : 'Relative';
            const phone = (contact.tel && contact.tel[0]) ? contact.tel[0] : '';
            const clean = getCleanPhoneNumber(phone);
            const msg = getPersonalisedMessage();

            const row = document.createElement('div');
            row.className = 'p-2.5 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border';
            row.style.background = '#ffffff';
            row.style.borderColor = '#e2e8f0';

            row.innerHTML = `
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0"
                         style="width: 38px; height: 38px; background: #e0e7ff; font-weight: 700; font-size: 0.9rem;">
                        ${escapeHtml(name.charAt(0).toUpperCase())}
                    </div>
                    <div class="min-w-0">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;" title="${escapeHtml(name)}">${escapeHtml(name)}</div>
                        <div class="text-muted small font-monospace" style="font-size: 0.76rem;">${escapeHtml(phone || 'No phone')}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <a href="${buildWhatsAppUri(clean, msg)}" target="_blank" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; min-height: 44px;" onclick="window.recordInviteStep && window.recordInviteStep()">
                        <i class="bi bi-whatsapp"></i> <span style="white-space: nowrap;">WhatsApp</span>
                    </a>
                    <a href="${buildSMSUri(clean, msg)}" class="btn btn-sm btn-info text-white rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 0.78rem; min-height: 44px;" onclick="window.recordInviteStep && window.recordInviteStep()">
                        <i class="bi bi-chat-dots-fill"></i> <span style="white-space: nowrap;">SMS</span>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center remove-contact-btn" data-index="${idx}" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px;" title="Remove" aria-label="Remove contact">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            list.appendChild(row);
        });

        // Wire remove buttons
        list.querySelectorAll('.remove-contact-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'), 10);
                if (!isNaN(index) && selectedContacts[index]) {
                    selectedContacts.splice(index, 1);
                    renderSelectedContacts();
                }
            });
        });
    }

    function showFeedback(text) {
        const alertEl = document.getElementById('inviteFeedbackAlert');
        const textEl = document.getElementById('inviteFeedbackText');
        if (!alertEl || !textEl) return;
        textEl.textContent = text;
        alertEl.classList.remove('d-none');
        setTimeout(() => alertEl.classList.add('d-none'), 3500);
    }

    // Initialize listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('inviteFamilyModal');
        if (!modalEl) return;

        // Expose recordInviteStep globally for row clicks
        window.recordInviteStep = recordInviteStep;

        modalEl.addEventListener('show.bs.modal', function() {
            loadInviteData();

            // Detect Contact Picker API availability
            const contactSection = document.getElementById('contactPickerSupportedSection');
            if (contactSection) {
                if ('contacts' in navigator && 'ContactsManager' in window) {
                    contactSection.classList.remove('d-none');
                } else {
                    contactSection.classList.add('d-none');
                }
            }
        });

        // Kinship Role Chips
        const chips = document.querySelectorAll('.kinship-chip');
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => {
                    c.classList.remove('active', 'btn-outline-primary');
                    c.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-outline-primary');
                currentRole = this.getAttribute('data-role') || 'General';
                updateMessageUI();
                if (selectedContacts.length > 0) {
                    renderSelectedContacts();
                }
            });
        });

        // Native Contact Picker Trigger
        const btnContact = document.getElementById('btnNativeContactPicker');
        if (btnContact) {
            btnContact.addEventListener('click', async function() {
                if (!('contacts' in navigator && 'ContactsManager' in window)) {
                    showFeedback('Contact Picker not supported on this browser. Please use WhatsApp or SMS below.');
                    return;
                }

                try {
                    const props = ['name', 'tel'];
                    const opts = { multiple: true };
                    const contacts = await navigator.contacts.select(props, opts);
                    if (Array.isArray(contacts) && contacts.length > 0) {
                        selectedContacts = contacts;
                        renderSelectedContacts();
                        recordInviteStep();
                        showFeedback(`Loaded ${contacts.length} contact(s) from your phone!`);
                    }
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        console.warn('[InviteFamily] Contact picker cancelled or failed:', err);
                    }
                }
            });
        }

        // Clear Contacts
        const btnClear = document.getElementById('btnClearSelectedContacts');
        if (btnClear) {
            btnClear.addEventListener('click', function() {
                selectedContacts = [];
                renderSelectedContacts();
            });
        }

        // Web Share API
        const btnWebShare = document.getElementById('btnInviteWebShare');
        if (btnWebShare) {
            btnWebShare.addEventListener('click', async function() {
                const msg = getPersonalisedMessage();
                const url = inviteData.invite_url || window.location.origin + '/register';
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: `Join our ${inviteData.surname || 'Family'} Sanctuary`,
                            text: msg,
                            url: url
                        });
                        recordInviteStep();
                    } catch (e) {
                        // User cancelled
                    }
                } else {
                    // Fallback to WhatsApp
                    window.open(buildWhatsAppUri('', msg), '_blank', 'noopener,noreferrer');
                    recordInviteStep();
                }
            });
        }

        // Copy Link
        const btnCopy = document.getElementById('btnCopyInviteLink');
        if (btnCopy) {
            btnCopy.addEventListener('click', function() {
                const url = inviteData.invite_url || window.location.origin + '/register';
                navigator.clipboard.writeText(url).then(() => {
                    const textSpan = document.getElementById('copyLinkBtnText');
                    if (textSpan) textSpan.textContent = 'Copied! ✓';
                    showFeedback('Family invite link copied to clipboard!');
                    recordInviteStep();
                    setTimeout(() => {
                        if (textSpan) textSpan.textContent = 'Copy Invite Link';
                    }, 2500);
                }).catch(() => {
                    prompt('Copy your family invite link:', url);
                    recordInviteStep();
                });
            });
        }

        // Direct Manual SMS / WhatsApp
        const btnManualSMS = document.getElementById('btnSendManualSMS');
        if (btnManualSMS) {
            btnManualSMS.addEventListener('click', function() {
                const nameInput = document.getElementById('manualRelativeName');
                const phoneInput = document.getElementById('manualRelativePhone');
                const name = nameInput ? nameInput.value.trim() : '';
                const phone = phoneInput ? phoneInput.value.trim() : '';

                if (!phone) {
                    (window.swalWarning || alert)('Please enter a mobile phone number to send an SMS.');
                    return;
                }

                const msg = getPersonalisedMessage();
                window.location.href = buildSMSUri(phone, msg);
                recordInviteStep();
            });
        }

        // WhatsApp direct button tracking
        const btnWhatsApp = document.getElementById('btnInviteWhatsApp');
        if (btnWhatsApp) {
            btnWhatsApp.addEventListener('click', function() {
                recordInviteStep();
            });
        }

        const btnSMS = document.getElementById('btnInviteSMS');
        if (btnSMS) {
            btnSMS.addEventListener('click', function() {
                recordInviteStep();
            });
        }
    });
})();
</script>
