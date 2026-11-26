import { useEffect, useState, useCallback } from 'preact/hooks';
import Swal from 'sweetalert2';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken } from '../shared/bootstrap';
import { createHybridRealtimeClient } from '../../components/profilePage/sovereignRealtimeBridge';

const CELEBRATION_TYPES = ['Birthday', 'Anniversary', 'Wedding', 'Graduation', 'Party', 'Reunion'];
const SHORT_MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

function parseEventDateLeaf(event) {
    const raw = String(event?.eventDateRaw || '').trim();
    const isoMatch = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
    if (isoMatch) {
        const monthIdx = Math.max(0, Math.min(11, parseInt(isoMatch[2], 10) - 1));
        const dayNum = String(parseInt(isoMatch[3], 10));
        return { month: SHORT_MONTHS[monthIdx], day: dayNum };
    }
    const display = String(event?.eventDate || '').trim();
    if (display) {
        const cleaned = display.replace(/(\d+)(st|nd|rd|th)\s+of\s+/i, '$1 ');
        const parsed = new Date(cleaned);
        if (!Number.isNaN(parsed.getTime())) {
            return {
                month: SHORT_MONTHS[parsed.getMonth()],
                day: String(parsed.getDate()),
            };
        }
    }
    return { month: 'EVT', day: '•' };
}

function getEventCategoryMeta(type) {
    const clean = String(type || '').trim();
    switch (clean) {
        case 'Birthday':
            return { icon: 'bi-cake2-fill', variant: 'birthday', label: 'Birthday' };
        case 'Anniversary':
            return { icon: 'bi-heart-fill', variant: 'anniversary', label: 'Anniversary' };
        case 'Wedding':
            return { icon: 'bi-gem', variant: 'anniversary', label: 'Wedding' };
        case 'Reunion':
            return { icon: 'bi-people-fill', variant: 'reunion', label: 'Reunion' };
        case 'Gathering':
            return { icon: 'bi-house-heart-fill', variant: 'reunion', label: 'Gathering' };
        case 'Party':
            return { icon: 'bi-stars', variant: 'party', label: 'Party' };
        case 'Graduation':
            return { icon: 'bi-mortarboard-fill', variant: 'milestone', label: 'Graduation' };
        case 'House_warming':
            return { icon: 'bi-key-fill', variant: 'milestone', label: 'House Warming' };
        case 'Milestone':
            return { icon: 'bi-trophy-fill', variant: 'milestone', label: 'Milestone' };
        default:
            return { icon: 'bi-calendar-check-fill', variant: 'default', label: clean || 'Event' };
    }
}

function getCountdownVariant(dateDifference) {
    const lower = String(dateDifference || '').trim().toLowerCase();
    if (lower === 'today') return 'ev-widget-countdown--today';
    if (lower === 'tomorrow') return 'ev-widget-countdown--tomorrow';
    return 'ev-widget-countdown--upcoming';
}

export function UpcomingEventsIsland({ bootstrap = {} }) {
    const initialEvents = Array.isArray(bootstrap?.events) ? bootstrap.events : [];
    const currentUserId = String(bootstrap?.userId || (typeof localStorage !== 'undefined' ? localStorage.getItem('requesterId') || '' : ''));
    const famCode = String(bootstrap?.famCode || '').replace(/[^A-Za-z0-9_-]/g, '');

    const [events, setEvents] = useState(initialEvents);
    const [eventsLimit, setEventsLimit] = useState(6);
    const [upgradingId, setUpgradingId] = useState(null);

    const applyEventUpdate = useCallback((updated) => {
        if (!updated?.no) return;
        setEvents((prev) => prev.map((ev) => (String(ev.no) === String(updated.no) ? { ...ev, ...updated } : ev)));
    }, []);

    useEffect(() => {
        const onCreated = (e) => {
            const newEvent = e?.detail;
            if (!newEvent?.no) return;
            setEvents((prev) => (prev.some((ev) => String(ev.no) === String(newEvent.no)) ? prev : [newEvent, ...prev]));
        };
        const onUpdated = (e) => applyEventUpdate(e?.detail);

        window.addEventListener('event-created', onCreated);
        window.addEventListener('event-updated', onUpdated);

        let rtClient = null;
        try {
            if (famCode) {
                const key = document.querySelector('meta[name="pusher-key"]')?.getAttribute('content')
                    || window.PUSHER_APP_KEY
                    || '';
                const cluster = document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content')
                    || window.PUSHER_APP_CLUSTER
                    || 'eu';
                rtClient = createHybridRealtimeClient({
                    key,
                    cluster,
                    getCsrfToken: () => getCsrfToken(bootstrap),
                });
                const channel = rtClient.subscribe(`private-family-${famCode}`);
                channel.bind('update-event', (data) => applyEventUpdate(data));
                channel.bind('delete-event', (data) => {
                    setEvents((prev) => prev.filter((ev) => String(ev.no) !== String(data?.no)));
                });
            }
        } catch {
            // ignore realtime init failure
        }

        return () => {
            window.removeEventListener('event-created', onCreated);
            window.removeEventListener('event-updated', onUpdated);
            if (rtClient && typeof rtClient.disconnect === 'function') {
                try {
                    rtClient.disconnect();
                } catch {
                    // ignore
                }
            }
        };
    }, [famCode, applyEventUpdate]);

    const isOwnEvent = (event) => String(event?.id) === currentUserId;

    const handleEditEvent = (event) => {
        const editEventNo = document.getElementById('editEventNo');
        const notice = document.getElementById('editEventNotice');
        const title = document.getElementById('createEventModalLabel');
        const submitBtn = document.getElementById('submitEventModal');
        if (!editEventNo) return;

        editEventNo.value = event.no;
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        };
        setVal('eventName', event.eventName);
        setVal('eventDate', event.eventDateRaw);
        setVal('eventType', event.eventType);
        setVal('eventDescription', event.eventDescription);
        setVal('eventFrequency', event.eventFrequency);
        setVal('eventLocation', event.eventLocation || '');
        setVal('proximityModeSelect', event.proximity_mode || 'all_family');
        setVal('targetRadiusMilesSelect', String(event.target_radius_miles || 25));
        const proxSelect = document.getElementById('proximityModeSelect');
        if (proxSelect) proxSelect.dispatchEvent(new Event('change', { bubbles: true }));

        if (notice) notice.classList.remove('d-none');
        if (title) title.textContent = 'Edit Event';
        if (submitBtn) submitBtn.textContent = 'Save Changes';

        const modalEl = document.getElementById('createEventModal');
        const instance = window.bootstrap?.Modal?.getOrCreateInstance(modalEl);
        instance?.show();
    };

    const handleDeleteEvent = async (eventNo) => {
        const result = await Swal.fire({
            title: 'Delete this event?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#a31621',
            confirmButtonText: 'Yes, delete it!',
        });
        if (!result.isConfirmed) return;

        try {
            await apiFetch(`/member/profilePage/event/${encodeURIComponent(eventNo)}`, {
                method: 'DELETE',
                timeoutMs: 20000,
            }, bootstrap);
            setEvents((prev) => prev.filter((ev) => String(ev.no) !== String(eventNo)));
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: err?.message || 'Failed to delete event.',
                confirmButtonColor: '#a31621',
            });
        }
    };

    const handleUpgradeToParty = async (event) => {
        if (!event?.no || upgradingId) return;
        setUpgradingId(String(event.no));
        try {
            const token = getCsrfToken(bootstrap);
            const payload = new URLSearchParams();
            if (token) payload.append('token', token);

            const res = await apiFetch(`/member/profilePage/event/${encodeURIComponent(event.no)}/upgrade-party`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    ...(token ? { 'X-CSRF-Token': token, 'X-XSRF-TOKEN': token } : {}),
                },
                body: payload.toString(),
                timeoutMs: 30000,
            }, bootstrap);

            const bridgeData = res?.token || {};
            applyEventUpdate({
                no: event.no,
                party_event_id: bridgeData.party_event_id || event.party_event_id,
                party_event_slug: bridgeData.party_event_slug || event.party_event_slug,
                party_rsvp_url: bridgeData.party_rsvp_url || event.party_rsvp_url,
                party_card_url: bridgeData.party_card_url || event.party_card_url,
                party_sync_status: 'synced',
            });

            const seededCount = Number(bridgeData.seeded_prospects_count || 0);
            const modalRes = await Swal.fire({
                icon: 'success',
                title: 'PartyPlatform Studio Ready!',
                html: `Your event is now live on PartyPlatform Studio${seededCount > 0 ? ` with <strong>${seededCount} family relatives</strong> pre-loaded into Guest Builder by kinship tier` : ''}.`,
                showCancelButton: true,
                confirmButtonColor: '#a31621',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Open Party Studio ↗',
                cancelButtonText: 'Stay Here',
            });

            if (modalRes.isConfirmed) {
                window.open(`/member/profilePage/event/${event.no}/party-sso`, '_blank', 'noopener');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Studio Sync Failed',
                text: err?.message || 'Could not connect to PartyPlatform Studio. Please try again.',
                confirmButtonColor: '#a31621',
            });
        } finally {
            setUpgradingId(null);
        }
    };

    const visibleEvents = events.slice(0, eventsLimit);
    const defaultTz = (() => {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || 'Local Time';
        } catch {
            return 'Local Time';
        }
    })();

    return (
        <div
            className="card border-0 shadow-sm mb-4 ev-upcoming-widget"
            id="upcomingEventsSection"
            style={{ borderRadius: '20px', backgroundColor: 'var(--card-bg)', scrollMarginTop: '80px' }}
        >
            <div className="card-header bg-transparent border-0 pt-3.5 pb-2 px-3" id="eventHeader">
                <div className="d-flex align-items-center justify-content-between gap-2">
                    <div className="d-flex align-items-center gap-2 min-w-0">
                        <div
                            className="d-inline-flex align-items-center justify-content-center flex-shrink-0 ev-widget-header-icon"
                            aria-hidden="true"
                        >
                            <i className="bi bi-calendar2-heart-fill" />
                        </div>
                        <h3
                            className="h5 mb-0 fw-bold ev-widget-heading text-truncate"
                            style={{ color: 'var(--text-color)', fontSize: '1.02rem', lineHeight: 1.25, letterSpacing: '-0.01em' }}
                        >
                            Upcoming Events
                        </h3>
                    </div>

                    <button
                        type="button"
                        className="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1 flex-shrink-0 ev-widget-see-all-btn"
                        style={{
                            minHeight: '44px',
                            minWidth: '44px',
                            whiteSpace: 'nowrap',
                            flexShrink: 0,
                        }}
                        data-bs-toggle="modal"
                        data-bs-target="#allUpcomingEventsModal"
                        aria-label="See All"
                    >
                        <span>See All</span> <i className="bi bi-chevron-right" style={{ fontSize: '0.7rem' }} />
                    </button>
                </div>

                <p className="mb-0 mt-1" style={{ fontSize: '0.8125rem', color: 'var(--text-muted-aa, #475569)', lineHeight: 1.4 }}>
                    Family gatherings, birthdays &amp; milestones.
                </p>

                <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 pt-2 ev-widget-subbar">
                    <div className="d-inline-flex align-items-center gap-1.5 ev-widget-tz-pill">
                        <i className="bi bi-globe2" aria-hidden="true" />
                        <span className="user-tz-name">{defaultTz}</span>
                    </div>
                    {events.length > 0 && (
                        <button
                            type="button"
                            className="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1 ev-widget-add-trigger"
                            style={{ minHeight: '44px', minWidth: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                            data-bs-toggle="modal"
                            data-bs-target="#createEventModal"
                            title="Schedule a new family event"
                        >
                            <i className="bi bi-plus-circle-fill" aria-hidden="true" />
                            <span>Add Event</span>
                        </button>
                    )}
                </div>
            </div>

            <div className="card-body eventList px-3 pt-2 pb-3.5" id="eventList">
                {events.length === 0 ? (
                    <div className="text-center py-4 px-2">
                        <div
                            className="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle p-3"
                            style={{ width: '52px', height: '52px', background: 'var(--secondary-color)', color: 'var(--primary-color)' }}
                        >
                            <i className="bi bi-calendar-event fs-3" />
                        </div>
                        <h6 className="fw-bold mb-1" style={{ color: 'var(--text-color)', fontSize: '0.95rem' }}>
                            No Upcoming Events
                        </h6>
                        <p className="mb-3" style={{ fontSize: '0.875rem', color: 'var(--text-muted-aa, #475569)', lineHeight: 1.45 }}>
                            Looks like the calendar is clear. Why not plan the next family gathering or reunion?
                        </p>
                        <button
                            type="button"
                            className="btn btn-sm text-white rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#createEventModal"
                            style={{
                                backgroundColor: 'var(--primary-color, #a31621)',
                                color: '#ffffff',
                                border: 'none',
                                fontSize: '0.875rem',
                                minHeight: '44px',
                                whiteSpace: 'nowrap',
                                flexShrink: 0,
                            }}
                        >
                            <i className="bi bi-plus-circle-fill me-1 text-warning" /> Create Event
                        </button>
                    </div>
                ) : (
                    <div className="d-flex flex-column gap-3">
                        {visibleEvents.map((event) => {
                            const own = isOwnEvent(event);
                            const upgrading = upgradingId === String(event.no);
                            const leaf = parseEventDateLeaf(event);
                            const catMeta = getEventCategoryMeta(event.eventType);
                            const countdownClass = getCountdownVariant(event.dateDifference);

                            return (
                                <div className="event-card mb-0" key={String(event.no)} id={`linkNotification${event.no}`}>
                                    <div className="ev-widget-card">
                                        <div className="d-flex flex-column">
                                            {/* Top Identity Row: Calendar Date Leaf + Title, Countdown & Category */}
                                            <div className="d-flex align-items-center gap-2.5">
                                                <div className="ev-widget-date-leaf flex-shrink-0" aria-hidden="true">
                                                    <span className="ev-widget-date-leaf__month">{leaf.month}</span>
                                                    <span className="ev-widget-date-leaf__day">{leaf.day}</span>
                                                </div>

                                                <div className="flex-grow-1 min-w-0">
                                                    <div className="d-flex align-items-start justify-content-between gap-2 mb-1.5">
                                                        <h4
                                                            className="h6 fw-bold mb-0 ev-widget-title"
                                                            style={{ color: 'var(--text-color)', fontSize: '0.95rem', lineHeight: 1.25, wordBreak: 'break-word' }}
                                                            title={event.eventName}
                                                        >
                                                            {event.eventName}
                                                        </h4>
                                                        {event.dateDifference && (
                                                            <span
                                                                className={`badge rounded-pill fw-bold flex-shrink-0 ev-widget-countdown ${countdownClass}`}
                                                                style={{ whiteSpace: 'nowrap' }}
                                                            >
                                                                {event.dateDifference}
                                                            </span>
                                                        )}
                                                    </div>

                                                    <div className="d-flex align-items-center justify-content-between flex-wrap gap-1.5">
                                                        <span className={`ev-widget-cat-pill ev-widget-cat-pill--${catMeta.variant}`}>
                                                            <i className={`bi ${catMeta.icon}`} aria-hidden="true" />
                                                            <span>{event.eventType || catMeta.label}</span>
                                                        </span>

                                                        {event.party_rsvp_url && (
                                                            <span className="ev-widget-studio-pill">
                                                                <i className="bi bi-stars" aria-hidden="true" /> Party Studio
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Full-Width Single-Line Date & Optional Location Row */}
                                            <div className="d-flex align-items-center gap-1.5 mt-2 ev-widget-date-meta">
                                                <i className="bi bi-calendar3 flex-shrink-0" aria-hidden="true" />
                                                <span className="text-break">{event.eventDate}</span>
                                            </div>

                                            {event.eventLocation && (
                                                <div
                                                    className="d-flex align-items-start mt-1"
                                                    style={{ fontSize: '0.8rem', color: 'var(--text-muted-aa, #475569)', lineHeight: 1.35 }}
                                                    title={event.eventLocation}
                                                >
                                                    <i className="bi bi-geo-alt-fill me-1.5 text-danger flex-shrink-0" style={{ marginTop: '1px' }} />
                                                    <span style={{ display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden', wordBreak: 'break-word' }}>
                                                        {event.eventLocation}
                                                    </span>
                                                </div>
                                            )}

                                            {/* PartyPlatform Live Links (when synced) */}
                                            {event.party_rsvp_url && (
                                                <div className="ev-widget-studio-box mt-2.5">
                                                    <div className="d-flex flex-wrap gap-2">
                                                        <a
                                                            href={event.party_rsvp_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                                            style={{
                                                                backgroundColor: 'var(--primary-color, #a31621)',
                                                                color: '#ffffff',
                                                                fontSize: '0.8125rem',
                                                                minHeight: '44px',
                                                                whiteSpace: 'nowrap',
                                                                flexShrink: 0,
                                                            }}
                                                            title="Open Interactive RSVP & Event Hub"
                                                        >
                                                            <i className="bi bi-ticket-perforated-fill text-warning" /> RSVP &amp; Hub
                                                        </a>

                                                        {event.party_card_url && CELEBRATION_TYPES.includes(event.eventType) && (
                                                            <a
                                                                href={event.party_card_url}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs ev-widget-sign-card-btn"
                                                                style={{
                                                                    fontSize: '0.8125rem',
                                                                    minHeight: '44px',
                                                                    whiteSpace: 'nowrap',
                                                                    flexShrink: 0,
                                                                }}
                                                                title="Sign the Family Celebration Card"
                                                            >
                                                                <i className="bi bi-envelope-paper-heart-fill" /> Sign Card
                                                            </a>
                                                        )}
                                                    </div>

                                                    {own && (
                                                        <div className="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 pt-2 ev-widget-studio-subrow">
                                                            <div className="ev-widget-powered">
                                                                powered by{' '}
                                                                <a
                                                                    href="https://mypartyplatform.com"
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="text-decoration-none fw-semibold"
                                                                >
                                                                    mypartyplatform.com
                                                                </a>
                                                            </div>
                                                            <a
                                                                href={`/member/profilePage/event/${event.no}/party-sso`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs"
                                                                style={{
                                                                    backgroundColor: '#0f172a',
                                                                    color: '#ffffff',
                                                                    fontSize: '0.8125rem',
                                                                    minHeight: '44px',
                                                                    whiteSpace: 'nowrap',
                                                                    flexShrink: 0,
                                                                }}
                                                                title="Launch Full PartyPlatform Event Studio (Single Sign-On)"
                                                            >
                                                                <i className="bi bi-stars text-warning" /> Manage Studio ↗
                                                            </a>
                                                        </div>
                                                    )}
                                                </div>
                                            )}

                                            {/* PartyPlatform Enable Callout (when own event & not yet synced) */}
                                            {!event.party_rsvp_url && own && (
                                                <div className="ev-widget-studio-box mt-2.5">
                                                    <div className="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1.5">
                                                        <span className="ev-widget-studio-heading">
                                                            <i className="bi bi-stars text-warning" aria-hidden="true" />
                                                            <span>Guest RSVPs &amp; Celebration Hub</span>
                                                        </span>
                                                        <span className="ev-widget-powered">
                                                            powered by{' '}
                                                            <a
                                                                href="https://mypartyplatform.com"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="text-decoration-none fw-semibold"
                                                            >
                                                                mypartyplatform.com
                                                            </a>
                                                        </span>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 w-100 ev-widget-upgrade-btn"
                                                        style={{
                                                            minHeight: '44px',
                                                            whiteSpace: 'nowrap',
                                                            flexShrink: 0,
                                                        }}
                                                        data-spin="true"
                                                        disabled={upgrading}
                                                        onClick={() => handleUpgradeToParty(event)}
                                                    >
                                                        {upgrading ? (
                                                            <span>
                                                                <span className="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" /> Syncing Family Tree...
                                                            </span>
                                                        ) : (
                                                            <span>
                                                                <i className="bi bi-stars me-1 text-warning" /> Enable RSVPs &amp; Party Studio
                                                            </span>
                                                        )}
                                                    </button>
                                                </div>
                                            )}

                                            {/* Unified Card Footer: Frequency & Author Edit/Delete Controls */}
                                            {own && (
                                                <div className="d-flex align-items-center justify-content-between gap-2 mt-2.5 pt-2 ev-widget-card-footer">
                                                    <span className="ev-widget-freq-label">
                                                        <i className="bi bi-arrow-repeat" aria-hidden="true" />
                                                        <span>{event.eventFrequency || 'Scheduled Event'}</span>
                                                    </span>
                                                    <div className="d-inline-flex align-items-center gap-2">
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1 ev-widget-action-btn"
                                                            style={{ minHeight: '44px', minWidth: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                                            title="Edit event"
                                                            onClick={() => handleEditEvent(event)}
                                                        >
                                                            <i className="bi bi-pencil-square" aria-hidden="true" />
                                                            <span>Edit</span>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1 ev-widget-action-btn ev-widget-action-btn--danger"
                                                            style={{ minHeight: '44px', minWidth: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                                            title="Delete event"
                                                            onClick={() => handleDeleteEvent(event.no)}
                                                        >
                                                            <i className="bi bi-trash3" aria-hidden="true" />
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            )}

                                            <input type="hidden" name="event_no" id={`event${event.no}`} value={event.no} />
                                        </div>
                                    </div>
                                </div>
                            );
                        })}

                        {events.length > eventsLimit && (
                            <button
                                type="button"
                                onClick={() => setEventsLimit((l) => l + 10)}
                                className="btn btn-sm btn-outline-secondary rounded-pill fw-semibold"
                                style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                            >
                                Show {Math.min(10, events.length - eventsLimit)} more events
                            </button>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}
