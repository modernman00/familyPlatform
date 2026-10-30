@php
    $token = $_SESSION['token'] ?? '';
    $_SESSION['CREATE_EVENT_ID'] = $data['id'];
@endphp

<!-- Create Event Modal Style overrides -->
<style>
    #createEventModal .modal-content {
        border-radius: 20px;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
        color: var(--text-main, #0f172a);
        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        overflow: hidden;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
    }
    #createEventModal .modal-header {
        border-bottom: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        padding: 0.85rem 1.25rem;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
    }
    #createEventModal .modal-title {
        font-family: var(--font-family), sans-serif;
        font-weight: 700;
        color: var(--text-main, #0f172a);
        font-size: 1.25rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    #createEventModal .modal-title .back-btn {
        color: var(--text-main, #0f172a);
        cursor: pointer;
        text-decoration: none;
        background: none;
        border: none;
        padding: 0;
        display: inline-flex;
        align-items: center;
        font-size: 1.25rem;
    }
    #createEventModal .modal-body {
        padding: 0.85rem 1rem;
        background-color: var(--card-bg, var(--bg-white, #ffffff));
    }
    #createEventModal .form-container-box {
        background-color: var(--bg-light, #f8fafc);
        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.06));
        border-radius: 16px;
        padding: 0.85rem;
    }
    #createEventModal .form-label {
        color: var(--text-muted, #64748b);
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }
    #createEventModal .form-control, 
    #createEventModal .form-select {
        background-color: var(--bg-white, #ffffff);
        border: 1px solid var(--border-color, #cbd5e1);
        color: var(--text-main, #0f172a);
        border-radius: 10px;
        padding: 0.45rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.25s;
    }
    #createEventModal .form-control:focus, 
    #createEventModal .form-select:focus {
        border-color: var(--brand-primary, #a31621);
        box-shadow: 0 0 0 3px rgba(163,22,33, 0.15);
        background-color: var(--bg-white, #ffffff);
        color: var(--text-main, #0f172a);
        outline: none;
    }
    #createEventModal .form-control::placeholder {
        color: var(--text-muted, #94a3b8);
    }
    #createEventModal select.form-select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }
    #createEventModal .modal-footer {
        border-top: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
        background-color: var(--card-bg, var(--bg-white, #ffffff));
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        position: sticky;
        bottom: 0;
        z-index: 10;
        margin-top: 0.75rem;
    }
    #createEventModal .btn-cancel {
        border-radius: 50px;
        padding: 0.55rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main, #334155);
        border: 1px solid var(--border-color, #cbd5e1);
        background-color: transparent;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-cancel:hover {
        background-color: var(--bg-light, #f1f5f9);
        color: var(--text-main, #0f172a);
    }
    #createEventModal .btn-save {
        border-radius: 50px;
        padding: 0.55rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: #ffffff !important;
        background-color: var(--brand-primary, #a31621);
        border: none;
        transition: all 0.2s;
        flex: 1;
    }
    #createEventModal .btn-save:hover {
        background-color: var(--brand-secondary, #7a0e16);
        opacity: 1;
    }
    #createEventModal .btn-close {
        display: none;
    }
    /* Date picker indicator styling */
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.6;
        cursor: pointer;
    }
    #createEventModal input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    /* Dark mode support */
    body.dark-mode #createEventModal .modal-content,
    body.dark-mode #createEventModal .modal-header,
    body.dark-mode #createEventModal .modal-body,
    body.dark-mode #createEventModal .modal-footer {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    body.dark-mode #createEventModal .modal-title,
    body.dark-mode #createEventModal .modal-title .back-btn {
        color: #f8fafc !important;
    }
    body.dark-mode #createEventModal .form-container-box {
        background-color: #0f172a !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    body.dark-mode #createEventModal .form-control,
    body.dark-mode #createEventModal .form-select {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    body.dark-mode #createEventModal input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
    body.dark-mode #createEventModal .btn-cancel {
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    body.dark-mode #createEventModal .btn-cancel:hover {
        background-color: #334155 !important;
        color: #ffffff !important;
    }
</style>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title" id="createEventModalLabel">
                    <button type="button" class="back-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    Create Event
                </h5>
            </div>

            <div class="modal-body">
                {{-- Deliberately OUTSIDE the form: FormHelper's massValidate() iterates
                     every named control inside #eventModalForm and treats an empty one
                     as a validation error, which a hidden "not editing" field would be. --}}
                <input type="hidden" id="editEventNo" value="">
                <small id="editEventNotice" class="d-none text-muted d-block mb-2 px-1">Editing this event.</small>
                
                <form id="eventModalForm" class="eventModalForm">

                    <div id="loader" class="text-center mb-2"></div>
                    <div id="eventModalForm_notification">
                        <p id="error" class="text-danger small mb-1"></p>
                    </div>

                    <div class="form-container-box">
                        <div class="mb-2.5">
                            <label for="eventName" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="eventName" name="eventName" placeholder="What's the event name?">
                            <small id="eventName_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="row g-2 mb-2.5">
                            <div class="col-6">
                                <label for="eventDate" class="form-label">Event Date</label>
                                <input type="date" class="form-control" name="eventDate" id="eventDate">
                                <small id="eventDate_error" class="text-danger ps-2"></small>
                            </div>

                            <div class="col-6">
                                <label for="eventType" class="form-label">Event Type</label>
                                <select name="eventType" id="eventType" class="form-select">
                                    <option value="" disabled selected>Choose type</option>
                                    <option value="Birthday">Birthday</option>
                                    <option value="Anniversary">Anniversary</option>
                                    <option value="Barbecue">Barbecue / Cookout</option>
                                    <option value="Museum">Museum / Cultural Outing</option>
                                    <option value="Weekend_Drive">Weekend Drive / Scenic Route</option>
                                    <option value="Holiday">Holiday / Staycation</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="House_warming">House Warming</option>
                                    <option value="Wedding">Wedding</option>
                                    <option value="Party">Party</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Memorial">Memorial</option>
                                    <option value="Graduation">Graduation</option>
                                    <option value="Other">Other</option>
                                </select>
                                <small id="eventType_error" class="text-danger ps-2"></small>
                            </div>
                        </div>

                        <div class="mb-2.5">
                            <label for="eventDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="eventDescription" name="eventDescription" rows="2" placeholder="Describe your event..."></textarea>
                            <small id="eventDescription_error" class="text-danger ps-2"></small>
                        </div>

                        <div class="mb-2.5">
                            <label for="eventFrequency" class="form-label">Frequency</label>
                            <select class="form-select" name="eventFrequency" id="eventFrequency">
                                <option value="" disabled selected>Choose event frequency</option>
                                <option value="One-off">One-off</option>
                                <option value="Annually">Annually</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                            <small id="eventFrequency_error" class="text-danger ps-2"></small>
                        </div>

                        <!-- Venue / Destination Location -->
                        <div class="mb-2.5">
                            <label for="eventLocation" class="form-label d-flex justify-content-between align-items-center">
                                <span>Venue / Location</span>
                                <span class="text-muted" style="font-size: 0.72rem; font-weight: normal;">Park, museum, route, or postcode</span>
                            </label>
                            <input type="text" class="form-control" id="eventLocation" data-optional="true" placeholder="e.g. Richmond Park, Science Museum, Cotswolds">
                        </div>

                        <!-- 📍 Proximity Localisation & Radius Card -->
                        <div class="p-3 mb-2.5 rounded-3 border" style="background: var(--bg-light, #f8fafc); border-color: var(--border-color, #e2e8f0);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0 fw-bold d-flex align-items-center gap-1" style="font-size: 0.85rem; color: var(--text-main, #0f172a);">
                                    <i class="bi bi-geo-alt-fill text-danger"></i>
                                    <span>Audience &amp; Proximity Scoping</span>
                                </label>
                                <span class="badge rounded-pill bg-light text-secondary border px-2 py-1" style="font-size: 0.68rem;" id="proximityBadgePreview">All Family</span>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-7">
                                    <select class="form-select form-select-sm" id="proximityModeSelect" style="font-size: 0.8rem; min-height: 44px;">
                                        <option value="all_family" selected>🌐 All Family Members (Global)</option>
                                        <option value="proximity_only">📍 Nearby Members Only (Localised)</option>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select class="form-select form-select-sm" id="targetRadiusMilesSelect" style="font-size: 0.8rem; min-height: 44px;" disabled>
                                        <option value="10">Within 10 miles</option>
                                        <option value="25" selected>Within 25 miles</option>
                                        <option value="50">Within 50 miles</option>
                                        <option value="100">Within 100 miles</option>
                                    </select>
                                </div>
                            </div>

                            <div id="proximityAudienceNote" class="small text-muted" style="font-size: 0.75rem; line-height: 1.35;">
                                <i class="bi bi-info-circle me-1"></i>
                                <span>All registered family members will receive notifications for this event.</span>
                            </div>
                        </div>

                        <!-- ✨ AI Activity Scout (ResilientWebSearchService) -->
                        <div class="p-3 mb-3 rounded-3" id="aiActivityScoutCard" style="background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 50%, #faf6ec 100%); border: 1.5px solid rgba(212, 175, 55, 0.35); box-shadow: 0 4px 14px rgba(212, 175, 55, 0.08);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge rounded-pill fw-bold" style="background: rgba(212, 175, 55, 0.15); color: #8c6d1f; border: 1px solid rgba(212, 175, 55, 0.4); font-size: 0.7rem;"><i class="bi bi-compass-fill text-warning me-1"></i> Concierge Scout</span>
                                    <span class="fw-bold" style="font-size: 0.85rem; color: #0f172a;">Curated Family Outings</span>
                                </div>
                                <span class="badge bg-light text-secondary border small" style="font-size: 0.65rem;">Grounded &amp; Monetised</span>
                            </div>

                            <p class="text-secondary mb-2" style="font-size: 0.77rem; line-height: 1.4;">
                                Instant suggestions for barbecue parklands, museum exhibitions, scenic drives, or staycations near your venue.
                            </p>

                            <!-- Category Quick-Pills -->
                            <div class="d-flex flex-wrap gap-1 mb-2.5" id="scoutCategoryPills">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill scout-pill active" data-cat="barbecue" style="font-size: 0.75rem; padding: 4px 12px; min-height: 38px; white-space: nowrap; flex-shrink: 0;">
                                    🍖 Barbecue &amp; Picnic
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill scout-pill" data-cat="museum" style="font-size: 0.75rem; padding: 4px 12px; min-height: 38px; white-space: nowrap; flex-shrink: 0;">
                                    🏛️ Museum &amp; Culture
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill scout-pill" data-cat="weekend_drive" style="font-size: 0.75rem; padding: 4px 12px; min-height: 38px; white-space: nowrap; flex-shrink: 0;">
                                    🚗 Weekend Drive
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill scout-pill" data-cat="holiday" style="font-size: 0.75rem; padding: 4px 12px; min-height: 38px; white-space: nowrap; flex-shrink: 0;">
                                    🏖️ Holiday Staycation
                                </button>
                            </div>

                            <!-- Scout Input Bar -->
                            <div class="input-group input-group-sm mb-2 shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-geo-alt-fill text-danger"></i></span>
                                <input type="text" class="form-control border-start-0" id="scoutLocationInput" data-optional="true" placeholder="Town, city or outward district (e.g. Richmond, SW19)" style="min-height: 44px; font-size: 0.82rem;">
                                <button type="button" class="btn d-inline-flex align-items-center gap-1.5 px-3 fw-bold text-white shadow-sm" id="btnRunScout" style="min-height: 44px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0; background: linear-gradient(135deg, #a31621 0%, #7e101a 100%); border: 1px solid rgba(255,215,0,0.3);">
                                    <i class="bi bi-compass"></i>
                                    <span>Scout Outings</span>
                                </button>
                            </div>

                            <!-- Scout Results Area -->
                            <div id="scoutResultsArea" class="d-none mt-2">
                                <div id="scoutLoadingSpinner" class="text-center py-2 d-none">
                                    <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                    <span class="ms-1 small text-muted">Scouting live verified ideas...</span>
                                </div>
                                <div id="scoutCardsContainer" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>

                        <!-- 3-Gear PartyPlatform Studio Escalation Toggle (deliberately no name="" attribute so FormHelper.massValidate() ignores it) -->
                        <div class="mt-3 p-3 rounded-3" id="partyPlatformToggleCard" style="background: linear-gradient(135deg, rgba(163, 22, 33, 0.06) 0%, rgba(245, 158, 11, 0.08) 100%); border: 1px solid rgba(163, 22, 33, 0.2);">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <label for="enablePartyPlatformToggle" class="fw-bold mb-0 d-flex align-items-center gap-1" style="font-size: 0.85rem; color: var(--text-main, #0f172a); cursor: pointer;">
                                        <span>✨ Plan with PartyPlatform Studio</span>
                                        <span class="badge rounded-pill" style="background-color: #a31621; color: #ffffff; font-size: 0.65rem;">Smart Sync</span>
                                    </label>
                                    <p class="text-muted mb-0 mt-1" style="font-size: 0.76rem; line-height: 1.35;">
                                        Automatically pre-load your Family Tree into Guest Builder with smart kinship tiers, interactive RSVP pages, QR check-in &amp; group celebration cards.
                                    </p>
                                </div>
                                <div class="form-check form-switch m-0 ps-0 d-flex align-items-center" style="min-width: 44px; min-height: 44px; justify-content: flex-end;">
                                    <input class="form-check-input m-0" type="checkbox" role="switch" id="enablePartyPlatformToggle" style="width: 2.5rem; height: 1.35rem; cursor: pointer;" aria-label="Plan with PartyPlatform Studio">
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" name="submit" class="btn btn-save" id="submitEventModal">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
