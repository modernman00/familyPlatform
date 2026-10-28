"use strict";
import { id, log } from "../global"
import FormHelper from '../FormHelper';
import { addToNotificationTab, increaseNotificationCount } from '../navbar'
import { eventHtml } from './eventHTML'
import axios from "axios";
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';


const formInput = document.querySelectorAll('.eventModalForm');
const formInputArr = Array.from(formInput);
const formData = new FormHelper(formInputArr);


const displayNone = () => {
    const modal = id('id_event_modal') || id('createEventModal');
    if (!modal) return;

    // This page loads Bootstrap 5, which has no jQuery .modal() plugin — setting
    // style.display='none' directly skips Bootstrap's own hide lifecycle and leaves
    // the .modal-backdrop overlay + body's modal-open/overflow:hidden stuck behind,
    // which is what made the page look frozen after submitting.
    const instance = window.bootstrap?.Modal?.getInstance(modal);
    if (instance) {
        instance.hide();
    } else {
        const closeBtn = modal.querySelector('.btn-close, [data-bs-dismiss="modal"]');
        if (closeBtn) closeBtn.click();
    }
};

const cancelBtn = id('cancelModal');
if (cancelBtn) {
    cancelBtn.addEventListener('click', displayNone);
}

// Reset the modal out of "edit" mode however it closes (submit, cancel,
// backdrop click, ESC) — editEvent() in sidebarComponents.js is what puts it
// into edit mode by setting these same fields.
const createEventModalEl = id('createEventModal');
if (createEventModalEl) {
    createEventModalEl.addEventListener('hidden.bs.modal', () => {
        const editEventNo = id('editEventNo');
        const notice = id('editEventNotice');
        const title = id('createEventModalLabel');
        const submitBtnEl = id('submitEventModal');
        if (editEventNo) editEventNo.value = '';
        if (notice) notice.classList.add('d-none');
        if (title) title.textContent = 'Create Event';
        if (submitBtnEl) submitBtnEl.textContent = 'Create Event';
        const eventForm = id('eventModalForm');
        if (eventForm) eventForm.reset();
        const partyToggle = id('enablePartyPlatformToggle');
        if (partyToggle) partyToggle.checked = false;
        const locInput = id('eventLocation');
        if (locInput) locInput.value = '';
        const proxMode = id('proximityModeSelect');
        if (proxMode) proxMode.value = 'all_family';
        const targetRad = id('targetRadiusMilesSelect');
        if (targetRad) {
            targetRad.value = '25';
            targetRad.disabled = true;
        }
        const scoutArea = id('scoutResultsArea');
        if (scoutArea) scoutArea.classList.add('d-none');
        const proxNote = id('proximityAudienceNote');
        if (proxNote) proxNote.innerHTML = '<i class="bi bi-info-circle me-1"></i><span>All registered family members will receive notifications for this event.</span>';
        const proxBadge = id('proximityBadgePreview');
        if (proxBadge) {
            proxBadge.textContent = 'All Family';
            proxBadge.className = 'badge rounded-pill bg-light text-secondary border px-2 py-1';
        }
    });
}

// Auto-toggle "Plan with PartyPlatform Studio" ON for large gathering types (Gear 2/3 default)
const eventTypeSelect = id('eventType');
if (eventTypeSelect) {
    eventTypeSelect.addEventListener('change', (ev) => {
        const partyToggle = id('enablePartyPlatformToggle');
        if (!partyToggle) return;
        const studioTypes = ['Party', 'Wedding', 'Reunion', 'House_warming', 'Graduation'];
        if (studioTypes.includes(ev.target.value)) {
            partyToggle.checked = true;
        }
    });
}

// Proximity Localisation and Radius Preview
const proximityModeSelect = id('proximityModeSelect');
const targetRadiusSelect = id('targetRadiusMilesSelect');
const proximityBadgePreview = id('proximityBadgePreview');
const proximityAudienceNote = id('proximityAudienceNote');
const eventLocationInput = id('eventLocation');

const updateProximityPreview = async () => {
    if (!proximityModeSelect) return;
    const isProximity = proximityModeSelect.value === 'proximity_only';
    if (targetRadiusSelect) {
        targetRadiusSelect.disabled = !isProximity;
    }
    const radius = targetRadiusSelect ? targetRadiusSelect.value : '25';

    if (!isProximity) {
        if (proximityBadgePreview) {
            proximityBadgePreview.textContent = 'All Family';
            proximityBadgePreview.className = 'badge rounded-pill bg-light text-secondary border px-2 py-1';
        }
        if (proximityAudienceNote) {
            proximityAudienceNote.innerHTML = '<i class="bi bi-info-circle me-1"></i><span>All registered family members will receive notifications for this event.</span>';
        }
        return;
    }

    if (proximityBadgePreview) {
        proximityBadgePreview.textContent = `Within ${radius} miles`;
        proximityBadgePreview.className = 'badge rounded-pill bg-danger text-white px-2 py-1';
    }

    const loc = eventLocationInput ? eventLocationInput.value.trim() : '';
    if (!loc) {
        if (proximityAudienceNote) {
            proximityAudienceNote.innerHTML = `<i class="bi bi-geo-alt me-1"></i>Enter a venue or city above to preview how many family members reside within ${radius} miles.`;
        }
        return;
    }

    try {
        const res = await axios.post('/member/activities/preview-audience', {
            venue_location: loc,
            radius_miles: radius
        }, options);
        const summary = res.data?.message?.summary || res.data?.data?.summary;
        if (summary && proximityAudienceNote) {
            proximityAudienceNote.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>${summary}`;
        }
    } catch (_) {
        if (proximityAudienceNote) {
            proximityAudienceNote.innerHTML = `<i class="bi bi-geo-alt me-1"></i>Targeting family members residing within ${radius} miles of ${loc}.`;
        }
    }
};

if (proximityModeSelect) proximityModeSelect.addEventListener('change', updateProximityPreview);
if (targetRadiusSelect) targetRadiusSelect.addEventListener('change', updateProximityPreview);
if (eventLocationInput) {
    let locDebounce;
    eventLocationInput.addEventListener('input', () => {
        clearTimeout(locDebounce);
        locDebounce = setTimeout(updateProximityPreview, 500);
    });
}

// ✨ AI Activity Scout (ResilientWebSearchService)
let currentScoutCategory = 'barbecue';
const scoutCategoryPills = document.querySelectorAll('.scout-pill');
scoutCategoryPills.forEach(pill => {
    pill.addEventListener('click', (e) => {
        scoutCategoryPills.forEach(p => {
            p.classList.remove('active', 'btn-danger', 'text-white');
            p.classList.add('btn-outline-secondary');
        });
        const target = e.currentTarget;
        target.classList.remove('btn-outline-secondary');
        target.classList.add('active', 'btn-danger', 'text-white');
        currentScoutCategory = target.getAttribute('data-cat') || 'barbecue';
    });
});

const btnRunScout = id('btnRunScout');
const scoutLocationInput = id('scoutLocationInput');
const scoutResultsArea = id('scoutResultsArea');
const scoutLoadingSpinner = id('scoutLoadingSpinner');
const scoutCardsContainer = id('scoutCardsContainer');

if (btnRunScout) {
    btnRunScout.addEventListener('click', async () => {
        const loc = scoutLocationInput ? scoutLocationInput.value.trim() : '';
        if (scoutResultsArea) scoutResultsArea.classList.remove('d-none');
        if (scoutLoadingSpinner) scoutLoadingSpinner.classList.remove('d-none');
        if (scoutCardsContainer) scoutCardsContainer.innerHTML = '';

        try {
            const res = await axios.get('/member/activities/discover', {
                params: {
                    category: currentScoutCategory,
                    location: loc || 'London',
                    age_group: 'all-ages'
                }
            });

            if (scoutLoadingSpinner) scoutLoadingSpinner.classList.add('d-none');
            const data = res.data?.message || res.data?.data || {};
            const cards = data.cards || [];

            if (cards.length === 0) {
                if (scoutCardsContainer) {
                    scoutCardsContainer.innerHTML = '<div class="alert alert-light border small text-muted py-2">No specific suggestions found. Try another town or outward district.</div>';
                }
                return;
            }

            cards.forEach(card => {
                const cardEl = document.createElement('div');
                cardEl.className = 'p-3 rounded-3 border bg-white shadow-sm';
                cardEl.style.fontSize = '0.8rem';
                cardEl.style.borderColor = '#e2e8f0';
                cardEl.style.transition = 'all 0.2s ease';
                cardEl.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <strong class="text-dark" style="font-size: 0.85rem; letter-spacing: -0.01em;">${card.title}</strong>
                        <div class="d-flex align-items-center gap-1">
                            ${card.price_guide ? `<span class="badge fw-semibold" style="background: rgba(16, 185, 129, 0.1); color: #047857; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.65rem;">${card.price_guide}</span>` : ''}
                            <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">${card.type}</span>
                        </div>
                    </div>
                    <p class="text-secondary mb-2" style="font-size: 0.78rem; line-height: 1.45;">${card.description}</p>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                        <small class="text-secondary d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                            <i class="bi bi-geo-alt-fill text-danger"></i>
                            <span>${card.location}</span>
                        </small>
                        <div class="d-flex align-items-center gap-1.5">
                            ${card.source_url ? `
                                <a href="${card.source_url}" target="_blank" rel="noopener noreferrer sponsored" class="btn btn-sm rounded-pill px-2.5 py-1 fw-bold text-white d-inline-flex align-items-center gap-1" style="font-size: 0.7rem; text-decoration: none; white-space: nowrap; min-height: 32px; background: linear-gradient(135deg, #059669 0%, #047857 100%); border: 1px solid rgba(255,255,255,0.2);">
                                    ${card.partner_name || 'Book Offer'} <i class="bi bi-box-arrow-up-right" style="font-size: 0.6rem;"></i>
                                </a>
                            ` : ''}
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 btn-use-activity fw-bold text-white" style="font-size: 0.74rem; min-height: 32px; background: linear-gradient(135deg, #a31621 0%, #7e101a 100%); border: 1px solid rgba(255,215,0,0.3); white-space: nowrap; flex-shrink: 0;">
                                ✨ Use This Idea
                            </button>
                        </div>
                    </div>
                `;

                const useBtn = cardEl.querySelector('.btn-use-activity');
                if (useBtn) {
                    useBtn.addEventListener('click', () => {
                        const eventNameEl = id('eventName');
                        const eventDescEl = id('eventDescription');
                        const eventTypeEl = id('eventType');
                        const eventLocEl = id('eventLocation');

                        if (eventNameEl) eventNameEl.value = card.title;
                        if (eventDescEl) eventDescEl.value = card.description;
                        if (eventLocEl) {
                            eventLocEl.value = card.location || loc;
                            updateProximityPreview();
                        }
                        if (eventTypeEl) {
                            for (let opt of eventTypeEl.options) {
                                if (opt.value.toLowerCase() === card.type.toLowerCase()) {
                                    opt.selected = true;
                                    break;
                                }
                            }
                        }

                        if (scoutResultsArea) scoutResultsArea.classList.add('d-none');

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Activity details applied!',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    });
                }

                if (scoutCardsContainer) scoutCardsContainer.appendChild(cardEl);
            });
        } catch (err) {
            if (scoutLoadingSpinner) scoutLoadingSpinner.classList.add('d-none');
            if (scoutCardsContainer) {
                scoutCardsContainer.innerHTML = '<div class="alert alert-light border small text-danger py-2">Unable to load live activity ideas. Please try again.</div>';
            }
        }
    });
}

/**
 * Filters events by family code (famCode) to ensure only relevant events are shown
 * @param {Object} event - The event data object
 * @returns {boolean} - Returns true if event is linked to the family code
 */



const options = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
}

// Mirrors rightColumn.blade.php's server-side "l jS \of F Y" format (e.g. "Wednesday 25th of August 2026")
const formatEventDate = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(`${dateStr}T00:00:00`);
    if (isNaN(date)) return dateStr;
    const day = date.getDate();
    const suffix = (day % 10 === 1 && day !== 11) ? 'st'
        : (day % 10 === 2 && day !== 12) ? 'nd'
        : (day % 10 === 3 && day !== 13) ? 'rd' : 'th';
    const weekday = date.toLocaleDateString('en-GB', { weekday: 'long' });
    const month = date.toLocaleDateString('en-GB', { month: 'long' });
    return `${weekday} ${day}${suffix} of ${month} ${date.getFullYear()}`;
};

// Mirrors rightColumn.blade.php's dateDifferenceInt()/number2word() -> "Today"/"Tomorrow"/"in N Days"
const dateDifferenceLabel = (dateStr) => {
    if (!dateStr) return '';
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const target = new Date(`${dateStr}T00:00:00`);
    if (isNaN(target)) return '';
    const diffDays = Math.round((target - today) / 86400000);
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    return `in ${diffDays} Days`;
};

/**
 * @function process
 * @description Processes the event modal form data to create a new event
 * @param {Event} e - The event object
 * @example
 * const eventForm = id('eventModalForm');
 * eventForm.addEventListener('submit', process);
 */
const process = async (e) => {
    try {
        e.preventDefault();
        const errEl = id('error');
        if (errEl) errEl.innerHTML = "";

        const formInput = document.querySelectorAll('.eventModalForm');
        const formInputArr = Array.from(formInput);
        const formData = new FormHelper(formInputArr);
        formData.massValidate();

        if (formData.error && formData.error.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'The form cannot be submitted. Please check the errors',
                confirmButtonColor: '#a31621'
            });
            formData.clearError();
            return;
        }

        // get the form data
        const eventForm = id('eventModalForm');
        let eventFormEntries = new FormData(eventForm);
        const partyToggleEl = id('enablePartyPlatformToggle');
        const enablePartyStudio = Boolean(partyToggleEl && partyToggleEl.checked);
        eventFormEntries.append('enable_party_platform', enablePartyStudio ? '1' : '0');

        const eventLocEl = id('eventLocation');
        const proximityModeEl = id('proximityModeSelect');
        const targetRadiusEl = id('targetRadiusMilesSelect');
        if (eventLocEl && eventLocEl.value) eventFormEntries.append('eventLocation', eventLocEl.value);
        if (proximityModeEl && proximityModeEl.value) eventFormEntries.append('proximity_mode', proximityModeEl.value);
        if (targetRadiusEl && targetRadiusEl.value) eventFormEntries.append('target_radius_miles', targetRadiusEl.value);

        // editEvent() in sidebarComponents.js stamps this when reopening the modal
        // to edit an existing event instead of creating a new one.
        const editEventNo = id('editEventNo')?.value;
        if (editEventNo) {
            const payload = {
                eventName: eventFormEntries.get('eventName') || '',
                eventDate: eventFormEntries.get('eventDate') || '',
                eventType: eventFormEntries.get('eventType') || '',
                eventDescription: eventFormEntries.get('eventDescription') || '',
                eventFrequency: eventFormEntries.get('eventFrequency') || '',
                eventLocation: eventLocEl ? eventLocEl.value : '',
                proximity_mode: proximityModeEl ? proximityModeEl.value : 'all_family',
                target_radius_miles: targetRadiusEl ? targetRadiusEl.value : '25',
            };

            await axios.put(`/member/profilePage/event/${editEventNo}`, payload, options);

            window.dispatchEvent(new CustomEvent('event-updated', {
                detail: {
                    no: editEventNo,
                    eventName: payload.eventName,
                    eventDate: formatEventDate(payload.eventDate),
                    eventType: payload.eventType,
                    dateDifference: dateDifferenceLabel(payload.eventDate),
                }
            }));

            displayNone();

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Event updated successfully',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }

        // POST data to create the event and notification in parallel
        const [eventResponse, notificationResponse] = await Promise.all([
            axios.post("/member/profilePage/event", eventFormEntries, options),
            axios.post('/member/notification/event', eventFormEntries, options)
        ]);

        // Extract and get notificationNo from the responses
        const { message: notificationNo } = notificationResponse.data || {};

        // update all members of similar famcode on their UIs using Pusher
        if (notificationNo) {
            axios.get(`/member/notification/event?notificationNo=${notificationNo}`);
        }

        // The sidebar's upcomingEvents component only ever gets seeded once, from the
        // page's initial server render (see sidebarComponents.js) — nothing was telling
        // it about events created afterward, so they only showed up on a full reload.
        // Dispatch the new event so it can prepend it immediately, mirroring the shape
        // rightColumn.blade.php builds server-side ({no, eventName, eventDate, eventType, dateDifference}).
        const newEventNo = eventResponse?.data?.token;
        if (newEventNo) {
            const createdDetail = {
                no: newEventNo,
                id: localStorage.getItem('requesterId') || '',
                eventName: eventFormEntries.get('eventName') || '',
                eventDate: formatEventDate(eventFormEntries.get('eventDate')),
                eventDateRaw: eventFormEntries.get('eventDate') || '',
                eventType: eventFormEntries.get('eventType') || '',
                eventDescription: eventFormEntries.get('eventDescription') || '',
                eventFrequency: eventFormEntries.get('eventFrequency') || '',
                dateDifference: dateDifferenceLabel(eventFormEntries.get('eventDate')),
                party_event_id: null,
                party_rsvp_url: null,
                party_card_url: null,
                party_sync_status: enablePartyStudio ? 'synced' : 'none',
            };

            window.dispatchEvent(new CustomEvent('event-created', {
                detail: createdDetail
            }));

            if (enablePartyStudio) {
                try {
                    const upgradeRes = await axios.post(`/member/profilePage/event/${newEventNo}/upgrade-party`, {}, options);
                    const bridgeData = upgradeRes?.data?.token || {};
                    if (bridgeData.party_rsvp_url) {
                        window.dispatchEvent(new CustomEvent('event-updated', {
                            detail: {
                                no: newEventNo,
                                party_event_id: bridgeData.party_event_id || null,
                                party_event_slug: bridgeData.party_event_slug || null,
                                party_rsvp_url: bridgeData.party_rsvp_url || null,
                                party_card_url: bridgeData.party_card_url || null,
                                party_sync_status: 'synced',
                            }
                        }));
                    }
                } catch (bridgeErr) {
                    console.warn('PartyPlatform studio link completed in background:', bridgeErr);
                }
            }
        }

        // close the modal
        displayNone();

        if (enablePartyStudio) {
            const swalRes = await Swal.fire({
                icon: 'success',
                title: 'Event Created & Linked to PartyPlatform!',
                html: `<strong>${eventFormEntries.get('eventName') || 'Event'}</strong> is now live on PartyPlatform Studio with your family relatives pre-loaded into Guest Builder.`,
                showCancelButton: true,
                confirmButtonColor: '#a31621',
                cancelButtonColor: '#64748b',
                confirmButtonText: '🚀 Go to Party Studio Now',
                cancelButtonText: 'Stay on FamilyPlatform'
            });

            if (swalRes.isConfirmed && newEventNo) {
                window.location.href = `/member/profilePage/event/${newEventNo}/party-sso`;
                return;
            }
        } else {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Event created successfully',
                showConfirmButton: false,
                timer: 3000
            });
        }

    } catch (error) {
        // showError() writes into the modal's own #error <p> instead of firing a Swal
        // when that element exists (it does, in this modal), so real submission
        // failures were going unnoticed. Show a Swal directly instead, same as the
        // validation-error path above.
        const userMessage = error?.response?.data?.message || error?.message || 'There was an error creating your event. Please try again.';
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: userMessage,
            confirmButtonColor: '#a31621'
        });
        log(error);
    }
};

const submitBtn = id('submitEventModal');
if (submitBtn) {
    submitBtn.addEventListener('click', process);
}





