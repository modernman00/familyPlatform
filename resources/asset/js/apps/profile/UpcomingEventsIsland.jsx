import { useEffect, useState, useCallback } from 'preact/hooks';
import Swal from 'sweetalert2';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken } from '../shared/bootstrap';
import { createHybridRealtimeClient } from '../../components/profilePage/sovereignRealtimeBridge';

const CELEBRATION_TYPES = ['Birthday', 'Anniversary', 'Wedding', 'Graduation', 'Party', 'Reunion'];

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

    return (
        <div
            className="card border-0 shadow-sm mb-4"
            id="upcomingEventsSection"
            style={{ borderRadius: '20px', backgroundColor: 'var(--card-bg)', scrollMarginTop: '80px' }}
        >
            <div className="card-header bg-transparent border-0 pt-4 pb-2 px-4" id="eventHeader">
                <div>
                    <h5 className="mb-0 fw-bold" style={{ color: 'var(--text-color)', fontSize: '1.15rem' }}>
                        Upcoming Events
                    </h5>
                    <p className="mb-0" style={{ fontSize: '0.875rem', color: 'var(--text-muted-aa, #475569)', lineHeight: 1.5 }}>
                        Family gatherings, birthdays &amp; milestones.
                    </p>
                    <div className="mt-1" style={{ fontSize: '0.8125rem', color: 'var(--text-muted-aa, #475569)' }}>
                        <i className="bi bi-globe me-1 text-primary" /> <span className="user-tz-name">Local Time</span>
                    </div>
                </div>
                <div className="d-flex justify-content-end align-items-center mt-2">
                    <button
                        type="button"
                        className="btn btn-link text-decoration-none p-0 d-inline-flex align-items-center gap-1 border-0 bg-transparent flex-shrink-0"
                        style={{
                            color: 'var(--primary-color, #a31621)',
                            fontWeight: 600,
                            fontSize: '0.875rem',
                            minHeight: '44px',
                            minWidth: '44px',
                            justifyContent: 'flex-end',
                            whiteSpace: 'nowrap',
                        }}
                        data-bs-toggle="modal"
                        data-bs-target="#allUpcomingEventsModal"
                        aria-label="See All"
                    >
                        <span>See All</span> <i className="bi bi-chevron-right" style={{ fontSize: '0.75rem' }} />
                    </button>
                </div>
            </div>

            <div className="card-body eventList px-4" id="eventList">
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
                            return (
                                <div className="event-card" key={String(event.no)} id={`linkNotification${event.no}`}>
                                    <div
                                        className="p-3 rounded-3"
                                        style={{
                                            backgroundColor: 'var(--card-bg, #ffffff)',
                                            border: '1px solid var(--border-color, #e2e8f0)',
                                            borderRadius: '14px',
                                            boxShadow: '0 1px 4px rgba(0,0,0,0.04)',
                                        }}
                                    >
                                        <div className="d-flex flex-column">
                                            <h6
                                                className="fw-bold mb-1"
                                                style={{ color: 'var(--text-color)', fontSize: '0.95rem', lineHeight: 1.35, wordBreak: 'break-word' }}
                                                title={event.eventName}
                                            >
                                                {event.eventName}
                                            </h6>
                                            <div className="mb-2">
                                                <span
                                                    className="badge rounded-pill px-2 py-1 fw-bold"
                                                    style={{ background: 'var(--secondary-color)', color: '#8a1019', fontSize: '0.75rem' }}
                                                >
                                                    {event.dateDifference}
                                                </span>
                                            </div>

                                            <div className="d-flex align-items-center mb-1" style={{ fontSize: '0.875rem', color: 'var(--text-muted-aa, #475569)' }}>
                                                <i className="bi bi-calendar3 me-2 text-primary" />
                                                <span>{event.eventDate}</span>
                                            </div>

                                            <div className="d-flex align-items-center justify-content-between mb-2" style={{ fontSize: '0.875rem', color: 'var(--text-muted-aa, #475569)' }}>
                                                <div>
                                                    <i className="bi bi-geo-alt me-2 text-danger" />
                                                    <span>{event.eventType}</span>
                                                </div>
                                                {event.party_rsvp_url && (
                                                    <span
                                                        className="badge rounded-pill px-2 py-1"
                                                        style={{ background: 'rgba(163, 22, 33, 0.1)', color: '#a31621', fontSize: '0.75rem', fontWeight: 700 }}
                                                    >
                                                        ✨ Party Studio
                                                    </span>
                                                )}
                                            </div>

                                            {event.party_rsvp_url && (
                                                <div className="d-flex flex-wrap gap-2 mt-1 mb-2 pt-2 border-top">
                                                    <a
                                                        href={event.party_rsvp_url}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                                                        style={{
                                                            backgroundColor: 'var(--primary-color, #a31621)',
                                                            color: '#ffffff',
                                                            fontSize: '0.85rem',
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
                                                            className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                                                            style={{
                                                                backgroundColor: '#ffffff',
                                                                color: '#8a1019',
                                                                border: '1px solid rgba(163, 22, 33, 0.25)',
                                                                fontSize: '0.85rem',
                                                                minHeight: '44px',
                                                                whiteSpace: 'nowrap',
                                                                flexShrink: 0,
                                                            }}
                                                            title="Sign the Family Celebration Card"
                                                        >
                                                            <i className="bi bi-envelope-paper-heart-fill" /> Sign Card
                                                        </a>
                                                    )}

                                                    {own && (
                                                        <div className="w-100 mt-1">
                                                            <div className="small text-muted mb-1" style={{ fontSize: '0.72rem', color: 'var(--text-muted-aa, #64748b)', lineHeight: 1.2 }}>
                                                                powered by{' '}
                                                                <a
                                                                    href="https://mypartyplatform.com"
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="text-decoration-none fw-semibold"
                                                                    style={{ color: 'var(--primary-color, #a31621)' }}
                                                                >
                                                                    mypartyplatform.com
                                                                </a>
                                                            </div>
                                                            <a
                                                                href={`/member/profilePage/event/${event.no}/party-sso`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                                                                style={{
                                                                    backgroundColor: '#0f172a',
                                                                    color: '#ffffff',
                                                                    fontSize: '0.85rem',
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

                                            {!event.party_rsvp_url && own && (
                                                <div className="mt-1 mb-2 pt-2 border-top">
                                                    <div className="small text-muted mb-1" style={{ fontSize: '0.72rem', color: 'var(--text-muted-aa, #64748b)', lineHeight: 1.2 }}>
                                                        powered by{' '}
                                                        <a
                                                            href="https://mypartyplatform.com"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-decoration-none fw-semibold"
                                                            style={{ color: 'var(--primary-color, #a31621)' }}
                                                        >
                                                            mypartyplatform.com
                                                        </a>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 w-100 justify-content-center"
                                                        style={{
                                                            background: 'linear-gradient(135deg, #a31621 0%, #8a1019 100%)',
                                                            color: '#ffffff',
                                                            border: '1px solid #7a0e16',
                                                            fontSize: '0.875rem',
                                                            minHeight: '44px',
                                                            whiteSpace: 'nowrap',
                                                            flexShrink: 0,
                                                        }}
                                                        disabled={upgrading}
                                                        onClick={() => handleUpgradeToParty(event)}
                                                    >
                                                        {upgrading ? (
                                                            <span>
                                                                <span className="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" /> Syncing Family Tree...
                                                            </span>
                                                        ) : (
                                                            <span>
                                                                <i className="bi bi-stars me-1" /> Enable RSVPs &amp; Party Studio
                                                            </span>
                                                        )}
                                                    </button>
                                                </div>
                                            )}

                                            {own && (
                                                <div className="d-flex gap-2 mt-1 border-top pt-2">
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-link text-muted p-0"
                                                        style={{ fontSize: '0.78rem', textDecoration: 'none', minHeight: '44px', minWidth: '44px' }}
                                                        title="Edit event"
                                                        onClick={() => handleEditEvent(event)}
                                                    >
                                                        <i className="bi bi-pencil me-1" /> Edit
                                                    </button>
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-link text-danger p-0 ms-2"
                                                        style={{ fontSize: '0.78rem', textDecoration: 'none', minHeight: '44px', minWidth: '44px' }}
                                                        title="Delete event"
                                                        onClick={() => handleDeleteEvent(event.no)}
                                                    >
                                                        <i className="bi bi-trash me-1" /> Delete
                                                    </button>
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
                                style={{ minHeight: '44px' }}
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
