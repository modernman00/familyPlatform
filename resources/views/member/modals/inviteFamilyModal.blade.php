<!-- Invite Family & Phone Contacts Modal (Artboards 15, 16, 17 — Desktop, iPad, Mobile) -->
<style>
#inviteFamilyModal .ifm-dialog {
    max-width: 780px;
}
#inviteFamilyModal .ifm-content {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid #e6dfd5;
    box-shadow: 0 24px 60px rgba(28, 20, 18, 0.22);
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #1c1412;
}
#inviteFamilyModal .ifm-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #efe8de;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    background: #ffffff;
}
#inviteFamilyModal .ifm-header-icon {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 50%;
    background: #fdf2f2;
    color: #a31621;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
#inviteFamilyModal .ifm-title {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 1.35rem;
    font-weight: 800;
    color: #1c1412;
    letter-spacing: -0.02em;
    margin: 0 0 2px;
}
#inviteFamilyModal .ifm-subtitle {
    font-size: 0.85rem;
    color: #786c65;
    margin: 0;
}
#inviteFamilyModal .ifm-close-btn {
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
    border-radius: 50%;
    border: none;
    background: #f6f1ea;
    color: #5c5049;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease;
}
#inviteFamilyModal .ifm-close-btn:hover {
    background: #ede5da;
}
#inviteFamilyModal .ifm-body-grid {
    display: grid;
    grid-template-columns: 1.08fr 0.92fr;
    gap: 24px;
    padding: 20px 24px 16px;
}
@media (max-width: 899.98px) {
    #inviteFamilyModal .ifm-dialog {
        max-width: 560px;
    }
    #inviteFamilyModal .ifm-body-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
}
@media (max-width: 575.98px) {
    #inviteFamilyModal .ifm-dialog {
        margin: auto 0 0 0;
        max-width: 100%;
    }
    #inviteFamilyModal .ifm-content {
        border-radius: 24px 24px 0 0;
    }
}
#inviteFamilyModal .ifm-section-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c7e75;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
#inviteFamilyModal .ifm-lineage-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (max-width: 479.98px) {
    #inviteFamilyModal .ifm-lineage-grid {
        grid-template-columns: 1fr;
    }
}
#inviteFamilyModal .ifm-lineage-card {
    border: 1.5px solid #e6dfd5;
    border-radius: 14px;
    padding: 12px 14px;
    background: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    transition: all 0.15s ease;
    min-height: 44px;
}
#inviteFamilyModal .ifm-lineage-card:has(input:checked) {
    background: #fdf2f2;
    border-color: #a31621;
}
#inviteFamilyModal .ifm-lineage-card input[type="radio"] {
    accent-color: #a31621;
    margin-top: 3px;
}
#inviteFamilyModal .kinship-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 44px;
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid #e2d9cd !important;
    background: #ffffff !important;
    color: #3d322c !important;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    transition: all 0.15s ease;
}
#inviteFamilyModal .kinship-chip.active {
    background: #1c1412 !important;
    border-color: #1c1412 !important;
    color: #ffffff !important;
}
#inviteFamilyModal .ifm-preview-card {
    background: #faf6f0;
    border: 1px solid #e6dfd5;
    border-radius: 14px;
    padding: 14px 16px;
    font-size: 0.83rem;
    line-height: 1.55;
    color: #3d322c;
    max-height: 210px;
    overflow-y: auto;
}
#inviteFamilyModal .ifm-primary-cta {
    background: #a31621;
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    min-height: 50px;
    padding: 12px 20px;
    font-size: 0.96rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-decoration: none;
    width: 100%;
    box-shadow: 0 6px 18px rgba(163, 22, 33, 0.24);
    transition: background 0.15s ease, transform 0.15s ease;
}
#inviteFamilyModal .ifm-primary-cta:hover {
    background: #8a1019;
    color: #ffffff !important;
}
#inviteFamilyModal .ifm-secondary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 10px;
}
@media (max-width: 575.98px) {
    #inviteFamilyModal .ifm-secondary-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
#inviteFamilyModal .ifm-sec-tile {
    border: 1px solid #e6dfd5;
    background: #ffffff;
    border-radius: 12px;
    min-height: 56px;
    padding: 8px 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    color: #3d322c;
    font-size: 0.74rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s ease, border-color 0.15s ease;
}
#inviteFamilyModal .ifm-sec-tile:hover {
    background: #faf6f0;
    border-color: #d5c8b8;
    color: #1c1412;
}
#inviteFamilyModal .ifm-sec-tile i {
    font-size: 1rem;
    color: #5c5049;
}
</style>

<div class="modal fade" id="inviteFamilyModal" tabindex="-1" aria-labelledby="inviteFamilyModalLabel" aria-hidden="true" x-data="{ isOpen: false }" x-init="$el.addEventListener('show.bs.modal', () => isOpen = true); $el.addEventListener('hidden.bs.modal', () => isOpen = false);">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ifm-dialog" x-trap="isOpen">
        <div class="modal-content ifm-content">
            @include('csrf')
            
            <!-- Modal Header -->
            <div class="ifm-header flex-shrink-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="ifm-header-icon flex-shrink-0">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div>
                        <h5 class="ifm-title" id="inviteFamilyModalLabel">
                            Invite family
                        </h5>
                        <p class="ifm-subtitle" id="modalFamilyContextSub">
                            Personalise a private link for your <span id="modalFamilyLineageTypeLabel" class="fw-semibold text-dark">Marital / Household family</span> (<span class="font-monospace fw-bold" id="modalFamilyCodeBadge">#{{ $data['famCode'] ?? '...' }}</span>)
                        </p>
                        <span class="d-none" id="modalFamilyContextHeading">100% Private Family Network</span>
                    </div>
                </div>
                <button type="button" class="ifm-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body p-0" style="overflow-y: auto;">
                <div class="ifm-body-grid">
                    <!-- Left Column: Step 1 (Which family) + Step 2 (Who are you inviting?) -->
                    <div>
                        <!-- Choice 1: Which family are you inviting them to? -->
                        <div id="inviteModalLineagePickerWrap" class="mb-4">
                            <div class="ifm-section-label">
                                <span>Which family are you inviting them to?</span>
                                <span class="badge rounded-pill" style="background:#f6f1ea;color:#786c65;font-size:0.66rem;text-transform:none;letter-spacing:0;" id="inviteModalAutoRouteBadge">
                                    Auto-adjusts by relative
                                </span>
                            </div>
                            <div class="ifm-lineage-grid" role="radiogroup" aria-label="Select Marital or Birth Family Code">
                                <label class="ifm-lineage-card">
                                    <input type="radio" name="invite_modal_lineage_scope" value="primary" id="inviteRadioPrimary" checked>
                                    <div>
                                        <div class="fw-bold" style="font-size:0.86rem;color:#1c1412;">Marital / Household</div>
                                        <div class="font-monospace" style="font-size:0.75rem;color:#786c65;" id="inviteRadioPrimaryCode">#{{ $primaryFamCode ?? ($data['famCode'] ?? '') }}</div>
                                    </div>
                                </label>
                                <label class="ifm-lineage-card">
                                    <input type="radio" name="invite_modal_lineage_scope" value="maternal" id="inviteRadioMaternal">
                                    <div>
                                        <div class="fw-bold" style="font-size:0.86rem;color:#1c1412;">Birth / Maiden family</div>
                                        <div class="font-monospace" style="font-size:0.75rem;color:#786c65;" id="inviteRadioMaternalCode">#{{ !empty($maternalFamCode) ? $maternalFamCode : ($primaryFamCode ?? ($data['famCode'] ?? '')) }}</div>
                                    </div>
                                </label>
                            </div>
                            <div class="text-muted mt-1.5 d-none" id="inviteModalLineageHint" style="font-size: 0.74rem;">
                                Selecting Mum, Dad, Sister, Brother, or Grandparent automatically switches to your Birth / Maiden family code.
                            </div>
                        </div>

                        <!-- Choice 2: Who are you inviting? (Line-icon chips, no emoji) -->
                        <div class="mb-3">
                            <div class="ifm-section-label">
                                <span>Who are you inviting?</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="kinshipChipsContainer">
                                <button type="button" class="btn kinship-chip active" data-role="General" data-label="Family Member">
                                    <i class="bi bi-people"></i> <span>General family</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Mum" data-label="Mum">
                                    <i class="bi bi-heart"></i> <span>Mum</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Dad" data-label="Dad">
                                    <i class="bi bi-shield-check"></i> <span>Dad</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Sister" data-label="Sister">
                                    <i class="bi bi-person-heart"></i> <span>Sister</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Brother" data-label="Brother">
                                    <i class="bi bi-person-check"></i> <span>Brother</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Child" data-label="Son / Daughter">
                                    <i class="bi bi-emoji-smile"></i> <span>Son / Daughter</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Grandparent" data-label="Grandparent">
                                    <i class="bi bi-award"></i> <span>Grandparent</span>
                                </button>
                                <button type="button" class="btn kinship-chip" data-role="Cousin" data-label="Cousin">
                                    <i class="bi bi-diagram-3"></i> <span>Cousin / Relative</span>
                                </button>
                            </div>
                        </div>

                        <!-- Native Phone Contacts Section (Shown on supported devices) -->
                        <div id="contactPickerSupportedSection" class="p-3 mb-3 rounded-3 d-none" style="background:#faf6f0;border:1px solid #e6dfd5;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold small" style="color:#1c1412;">Phone Address Book</span>
                                <small class="text-muted">Device-only</small>
                            </div>
                            <button type="button" id="btnNativeContactPicker" class="btn btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="min-height: 44px; border-radius: 10px; background:#ffffff; border:1px solid #d5c8b8; color:#1c1412;">
                                <i class="bi bi-person-lines-fill"></i>
                                <span>Select Relatives from Phone Contacts</span>
                            </button>
                        </div>

                        <!-- Selected Contacts Tray -->
                        <div id="selectedContactsContainer" class="d-none mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold mb-0 small">Selected Contacts (<span id="selectedContactsCount">0</span>)</h6>
                                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" id="btnClearSelectedContacts" style="font-size: 0.78rem;">Clear All</button>
                            </div>
                            <div class="d-flex flex-column gap-2" id="selectedContactsList"></div>
                        </div>

                        <!-- Compact Direct SMS Accordion -->
                        <details class="mt-2">
                            <summary class="small fw-semibold" style="color:#786c65;cursor:pointer;list-style:none;display:inline-flex;align-items:center;gap:6px;min-height:44px;">
                                <i class="bi bi-telephone-plus"></i> <span>Or send directly to a phone number</span>
                            </summary>
                            <div class="row g-2 mt-1">
                                <div class="col-12 col-sm-5">
                                    <input type="text" id="manualRelativeName" class="form-control form-control-sm" placeholder="Name (e.g. Auntie Mary)" style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                </div>
                                <div class="col-12 col-sm-5">
                                    <input type="tel" id="manualRelativePhone" class="form-control form-control-sm" placeholder="Mobile (+44 7123 456789)" style="min-height: 44px; border-radius: 10px; font-size: 0.84rem;">
                                </div>
                                <div class="col-12 col-sm-2 d-flex">
                                    <button type="button" id="btnSendManualSMS" class="btn w-100 d-flex align-items-center justify-content-center" title="Send SMS" style="min-height: 44px; border-radius: 10px; background:#1c1412; color:#fff;" aria-label="Send SMS to entered number">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Right Column: Choice 3 (Message preview) + Primary CTA + 4 Secondary Tiles -->
                    <div class="d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <div class="ifm-section-label">
                                <span>Message preview</span>
                                <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold" style="font-size:0.74rem;color:#a31621;text-transform:none;letter-spacing:0;" onclick="document.getElementById('btnCopyFullInvite')?.click()">
                                    Copy text
                                </button>
                            </div>
                            <div class="ifm-preview-card" style="white-space: pre-wrap;" id="inviteMessagePreview">Loading personalised invitation link...</div>
                        </div>

                        <div>
                            <!-- Primary Action: Share via WhatsApp -->
                            <a href="#" target="_blank" id="btnInviteWhatsApp" class="ifm-primary-cta">
                                <i class="bi bi-whatsapp fs-5"></i>
                                <span style="white-space: nowrap;">Share via WhatsApp</span>
                            </a>

                            <!-- Secondary 4-Tile Row: Copy Full Invite, SMS, Share sheet, Copy Link Only -->
                            <div class="ifm-secondary-grid">
                                <button type="button" id="btnCopyFullInvite" class="ifm-sec-tile">
                                    <i class="bi bi-clipboard-check"></i>
                                    <span id="copyFullInviteBtnText" style="white-space: nowrap;">Copy Full Invite</span>
                                </button>
                                <a href="#" id="btnInviteSMS" class="ifm-sec-tile">
                                    <i class="bi bi-chat-dots"></i>
                                    <span style="white-space: nowrap;">SMS</span>
                                </a>
                                <button type="button" id="btnInviteWebShare" class="ifm-sec-tile">
                                    <i class="bi bi-box-arrow-up"></i>
                                    <span style="white-space: nowrap;">Share sheet</span>
                                </button>
                                <button type="button" id="btnCopyInviteLink" class="ifm-sec-tile">
                                    <i class="bi bi-link-45deg"></i>
                                    <span id="copyLinkBtnText" style="white-space: nowrap;">Copy Link Only</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Toast Alert -->
                <div id="inviteFeedbackAlert" class="alert alert-success d-none py-2 px-3 mx-4 mb-3 text-center small rounded-3 border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <span id="inviteFeedbackText">Link copied to clipboard!</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 py-2.5 d-flex justify-content-between flex-shrink-0" style="background:#faf6f0;border-top:1px solid #efe8de !important;">
                <span class="small d-flex align-items-center gap-1.5" style="color:#786c65;font-size:0.76rem;">
                    <i class="bi bi-lock" style="color:#a31621;"></i>
                    <span>Private link · Only people you invite can join</span>
                </span>
                <button type="button" class="btn btn-sm px-3 fw-bold rounded-pill" data-bs-dismiss="modal" style="min-height: 44px; background:#ffffff; border:1px solid #dfd7cc; color:#1c1412;">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    let primaryInviteData = {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $primaryFamCode ?? ($data["famCode"] ?? "") }}',
        surname: '{{ $maritalSurname ?? ($data["familySurname"] ?? ($data["lastName"] ?? "Family")) }}',
        lineage_scope: 'primary'
    };
    let birthInviteData = @if(!empty($hasMaternalCode) && !empty($maternalFamCode)) {
        invite_url: '',
        whatsapp_url: '',
        invite_text: '',
        family_code: '{{ $maternalFamCode }}',
        surname: '{{ $maidenSurname ?? $maternalFamCode }}',
        lineage_scope: 'maternal'
    } @else null @endif;

    let activeScope = '{{ ($activeLineageScope ?? "primary") === "maternal" ? "maternal" : "primary" }}';
    let inviteData = Object.assign({}, primaryInviteData);
    let currentRole = 'General';
    let selectedContacts = [];

    // Kinship template dictionary (British English copy, dynamically adapts for Marital vs Birth family code)
    const kinshipTemplates = {
        'General': (surname, url, code, isBirth) => isBirth
            ? `🌳 *Family Heritage Invitation* 🌳\n\n` +
              `You are invited to join our *${surname} Birth Family Network & Heritage Tree* on FamilyPlatform.\n\n` +
              `🔑 *Birth Family Code:* #${code}\n\n` +
              `✨ Claim your branch on our live family tree, preserve our heritage, share memories, and connect across generations.\n\n` +
              `🔗 Tap to Enter Our Family Sanctuary:\n${url}\n\n` +
              `Welcome to our living family legacy! 🏰`
            : `🌳 *Family Heritage Invitation* 🌳\n\n` +
              `You are invited to join our *${surname} Family Network & Heritage Tree*.\n\n` +
              `🔑 *Family Code:* #${code}\n\n` +
              `✨ Claim your branch on our live family tree, preserve our oral histories, share memories, and connect across generations.\n\n` +
              `🔗 Tap to Enter Our Family Sanctuary:\n${url}\n\n` +
              `Welcome to our living family legacy! 🏰`,
        'Mum': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Mum* 🌳\n\n` +
            `Hi Mum,\n\n` +
            `I've set up our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* sanctuary.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ View our ancestral lineage, see cherished photos, and claim your spot on our living family tree:\n\n` +
            `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Dad': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Dad* 🌳\n\n` +
            `Hi Dad,\n\n` +
            `I've set up our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* sanctuary.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ View our ancestral lineage, see cherished photos, and claim your spot on our living family tree:\n\n` +
            `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Sister': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Sister* 🌳\n\n` +
            `Hey sis,\n\n` +
            `Join our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage hub.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ Claim your branch on our living family tree, share memories, and preserve our stories:\n\n` +
            `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Brother': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Brother* 🌳\n\n` +
            `Hey bro,\n\n` +
            `Join our private *${surname} ${isBirth ? 'Birth Family' : 'Family'}* heritage hub.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ Claim your branch on our living family tree, share memories, and preserve our stories:\n\n` +
            `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Child': (surname, url, code) => `🌳 *Family Heritage Invitation* 🌳\n\n` +
            `Hey,\n\n` +
            `I've added you to our official *${surname} Family Tree*.\n\n` +
            `🔑 *Family Code:* #${code}\n\n` +
            `✨ Claim your profile, view our ancestral roots, and share memories:\n\n` +
            `🔗 Tap to Claim Your Profile:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Grandparent': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Grandparent* 🌳\n\n` +
            `Dear Grandparent,\n\n` +
            `Our *${surname} ${isBirth ? 'Birth Family' : 'Family'}* tree and heritage have been preserved.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ View our family branches, photos, oral history recordings, and ancestral records:\n\n` +
            `🔗 Tap to View Our Family Tree:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`,
        'Cousin': (surname, url, code, isBirth) => `🌳 *Family Heritage Invitation for Cousin* 🌳\n\n` +
            `Hey cousin,\n\n` +
            `We are bringing the *${surname} ${isBirth ? 'Birth Family' : 'Family'}* together on our private family hub.\n\n` +
            `🔑 *${isBirth ? 'Birth Family Code' : 'Family Code'}:* #${code}\n\n` +
            `✨ Claim your branch on our live family tree and view our roots:\n\n` +
            `🔗 Tap to Claim Your Branch:\n${url}\n\n` +
            `Welcome to our living family legacy! 🏰`
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

    function setInviteLineageScope(scope) {
        const useBirth = (scope === 'maternal' && birthInviteData && birthInviteData.family_code);
        activeScope = useBirth ? 'maternal' : 'primary';
        inviteData = Object.assign({}, useBirth ? birthInviteData : primaryInviteData);

        const rPrimary = document.getElementById('inviteRadioPrimary');
        const rMaternal = document.getElementById('inviteRadioMaternal');
        if (rPrimary) rPrimary.checked = !useBirth;
        if (rMaternal) rMaternal.checked = useBirth;

        const codeBadge = document.getElementById('modalFamilyCodeBadge');
        if (codeBadge && inviteData.family_code) {
            codeBadge.textContent = inviteData.family_code;
        }

        const typeLabel = document.getElementById('modalFamilyLineageTypeLabel');
        if (typeLabel) {
            typeLabel.textContent = useBirth
                ? `Birth / Maiden family (${inviteData.surname || inviteData.family_code})`
                : `Marital / Household family (${inviteData.surname || inviteData.family_code})`;
        }

        const hintEl = document.getElementById('inviteModalLineageHint');
        if (hintEl) {
            hintEl.innerHTML = useBirth
                ? `Using your <strong>Birth / Maiden family code (#${escapeHtml(inviteData.family_code)})</strong> so your parents, siblings, and birth relatives join your maiden lineage.`
                : `Using your <strong>Marital / Household family code (#${escapeHtml(inviteData.family_code)})</strong> for your partner, children, and household relatives.`;
        }

        updateMessageUI();
        if (selectedContacts.length > 0) {
            renderSelectedContacts();
        }
    }

    function getPersonalisedMessage() {
        const url = inviteData.invite_url || window.location.origin + '/register';
        const surname = inviteData.surname || 'Family';
        const code = inviteData.family_code || '';
        const isBirth = (activeScope === 'maternal');
        const generator = kinshipTemplates[currentRole] || kinshipTemplates['General'];
        return generator(surname, url, code, isBirth);
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
                    primaryInviteData = Object.assign(primaryInviteData, json.data.invite_data);
                    const pCodeEl = document.getElementById('inviteRadioPrimaryCode');
                    if (pCodeEl && primaryInviteData.family_code) {
                        pCodeEl.textContent = '(#' + primaryInviteData.family_code + ')';
                    }

                    if (json.data.birth_invite_data && json.data.birth_invite_data.family_code) {
                        birthInviteData = Object.assign(birthInviteData || {}, json.data.birth_invite_data);
                        const mCodeEl = document.getElementById('inviteRadioMaternalCode');
                        if (mCodeEl && birthInviteData.family_code) {
                            mCodeEl.textContent = '(#' + birthInviteData.family_code + ')';
                        }
                        const pickerWrap = document.getElementById('inviteModalLineagePickerWrap');
                        if (pickerWrap) {
                            pickerWrap.classList.remove('d-none');
                        }
                    }

                    setInviteLineageScope(activeScope);
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
                         style="width: 38px; height: 38px; background: #f5dede; font-weight: 700; font-size: 0.9rem;">
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

        // Marital vs. Birth Family Code Radio Listeners
        document.querySelectorAll('input[name="invite_modal_lineage_scope"]').forEach(radio => {
            radio.addEventListener('change', function() {
                setInviteLineageScope(this.value);
            });
        });

        // Kinship Role Chips (automatically switch between Birth and Marital family codes when appropriate)
        const chips = document.querySelectorAll('.kinship-chip');
        const birthRoles = ['Mum', 'Dad', 'Sister', 'Brother', 'Grandparent'];
        const maritalRoles = ['Child'];
        chips.forEach(chip => {
            chip.addEventListener('click', function() {
                chips.forEach(c => {
                    c.classList.remove('active', 'btn-outline-primary');
                    c.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('active', 'btn-outline-primary');
                currentRole = this.getAttribute('data-role') || 'General';

                if (birthInviteData && birthInviteData.family_code) {
                    if (birthRoles.includes(currentRole)) {
                        setInviteLineageScope('maternal');
                        return;
                    } else if (maritalRoles.includes(currentRole)) {
                        setInviteLineageScope('primary');
                        return;
                    }
                }

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

        function fallbackCopy(text, feedbackMsg) {
            try {
                const tempArea = document.createElement('textarea');
                tempArea.value = text;
                tempArea.style.position = 'fixed';
                tempArea.style.left = '-9999px';
                tempArea.style.top = '0';
                document.body.appendChild(tempArea);
                tempArea.focus();
                tempArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(tempArea);
                if (successful) {
                    showFeedback(feedbackMsg);
                    recordInviteStep();
                    return;
                }
            } catch (e) {}
            prompt('Copy invitation to clipboard:', text);
            recordInviteStep();
        }

        // Copy Full Rich Invite (with Family Code, emojis, and link)
        const btnCopyFull = document.getElementById('btnCopyFullInvite');
        if (btnCopyFull) {
            btnCopyFull.addEventListener('click', function() {
                const msg = getPersonalisedMessage();
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(msg).then(() => {
                        const textSpan = document.getElementById('copyFullInviteBtnText');
                        if (textSpan) textSpan.textContent = 'Invite Copied! ✓';
                        showFeedback('Full rich invitation with Family Code copied to clipboard!');
                        recordInviteStep();
                        setTimeout(() => {
                            if (textSpan) textSpan.textContent = 'Copy Full Invite';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopy(msg, 'Full rich invitation with Family Code copied to clipboard!');
                    });
                } else {
                    fallbackCopy(msg, 'Full rich invitation with Family Code copied to clipboard!');
                }
            });
        }

        // Copy Link Only
        const btnCopy = document.getElementById('btnCopyInviteLink');
        if (btnCopy) {
            btnCopy.addEventListener('click', function() {
                const url = inviteData.invite_url || window.location.origin + '/register';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        const textSpan = document.getElementById('copyLinkBtnText');
                        if (textSpan) textSpan.textContent = 'Link Copied! ✓';
                        showFeedback('Family invite link copied to clipboard!');
                        recordInviteStep();
                        setTimeout(() => {
                            if (textSpan) textSpan.textContent = 'Copy Link Only';
                        }, 2500);
                    }).catch(() => {
                        fallbackCopy(url, 'Family invite link copied to clipboard!');
                    });
                } else {
                    fallbackCopy(url, 'Family invite link copied to clipboard!');
                }
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
