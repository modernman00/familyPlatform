import axios from 'axios';
import Pusher from 'pusher-js';
import Swal from 'sweetalert2';

const csrfOptions = {
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
};

export function profileSidebar(initialData) {
    return {
        userData: initialData || {},
        friendRequests: [],
        isLoadingRequests: true,
        requestError: '',

        init() {
            // Defensive typing and fallback check
            if (!this.userData?.id) {
                console.warn('profileSidebar: Missing userData.id');
                this.isLoadingRequests = false;
                return;
            }

            // Secure local storage setup
            try {
                localStorage.setItem('requesterFamCode', this.userData.famCode || '');
                localStorage.setItem('requesterId', this.userData.id || '');
                localStorage.setItem('yourName', `${this.userData.firstName || ''} ${this.userData.lastName || ''}`.trim());
            } catch (e) {
                console.error('Failed to set localStorage profile credentials:', e);
            }

            this.fetchRequests();
        },

        async fetchRequests() {
            this.isLoadingRequests = true;
            this.requestError = '';
            try {
                const response = await axios.get(`/getFriendRequestById?id=${encodeURIComponent(this.userData.id)}`, {
                    timeout: 8000 // Strict timeout gate
                });
                
                const requests = response?.data?.message;
                if (Array.isArray(requests)) {
                    this.friendRequests = requests.map(req => ({
                        id: req?.id ?? req?.requesterId,
                        firstName: req?.firstName ?? req?.requesterFirstName ?? 'Unknown',
                        lastName: req?.lastName ?? req?.requesterLastName ?? '',
                        img: req?.img ?? req?.requesterProfileImg ?? 'avatarM.png',
                        famCode: req?.famCode ?? req?.requesterFamCode ?? ''
                    }));
                } else {
                    this.friendRequests = [];
                }
            } catch (err) {
                console.error('Failed to fetch friend requests:', err);
                this.requestError = 'Could not load requests.';
            } finally {
                this.isLoadingRequests = false;
            }
        },

        getAcceptUrl(req) {
            const requestId = encodeURIComponent(req?.id || '');
            const approverId = encodeURIComponent(this.userData?.id || '');
            const requestCode = encodeURIComponent(req?.famCode || '');
            return `/member/request/${requestId}/${approverId}/50/${requestCode}/pp`;
        },

        getDeclineUrl(req) {
            const requestId = encodeURIComponent(req?.id || '');
            const approverId = encodeURIComponent(this.userData?.id || '');
            return `/member/request/${requestId}/${approverId}/10`;
        }
    };
}

export function upcomingEvents(initialEvents) {
    return {
        events: Array.isArray(initialEvents) ? initialEvents : [],
        currentUserId: localStorage.getItem('requesterId') || '',
        pusher: null,

        init() {
            // Seeded once from the server on page load — without this listener, an
            // event created via the modal never appeared until a full page reload.
            window.addEventListener('event-created', (e) => {
                const newEvent = e?.detail;
                if (newEvent && !this.events.some(ev => String(ev.no) === String(newEvent.no))) {
                    this.events.unshift(newEvent);
                }
            });

            // Dispatched by createEvent.js after a successful edit (the acting
            // user's own tab — other tabs get it via the update-event Pusher bind).
            window.addEventListener('event-updated', (e) => {
                this.applyEventUpdate(e?.detail);
            });

            this.initPusher();
        },

        isOwnEvent(event) {
            return String(event?.id) === String(this.currentUserId);
        },

        // Opens the existing "Create Event" modal in edit mode: prefills every
        // field, stamps a hidden editEventNo the modal's own submit handler
        // (createEvent.js) checks to decide between POST (create) and PUT (update).
        editEvent(event) {
            const editEventNo = document.getElementById('editEventNo');
            const notice = document.getElementById('editEventNotice');
            const title = document.getElementById('createEventModalLabel');
            const submitBtn = document.getElementById('submitEventModal');
            if (!editEventNo) return;

            editEventNo.value = event.no;
            const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
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
        },

        async deleteEvent(eventNo) {
            const result = await Swal.fire({
                title: 'Delete this event?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });
            if (!result.isConfirmed) return;

            try {
                await axios.delete(`/member/profilePage/event/${eventNo}`, csrfOptions);
                this.events = this.events.filter(ev => String(ev.no) !== String(eventNo));
            } catch (err) {
                console.error('Failed to delete event:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: err?.response?.data?.message || 'Failed to delete event.',
                    confirmButtonColor: '#3085d6'
                });
            }
        },

        applyEventUpdate(updated) {
            if (!updated?.no) return;
            const event = this.events.find(ev => String(ev.no) === String(updated.no));
            if (event) Object.assign(event, updated);
        },

        initPusher() {
            try {
                const key = process.env.MIX_PUSHER_APP_KEY;
                const cluster = process.env.MIX_PUSHER_APP_CLUSTER;
                if (!key || !cluster) return;

                this.pusher = new Pusher(key, { cluster, encrypted: true });

                const eventsChannel = this.pusher.subscribe('events-channel');
                eventsChannel.bind('update-event', (data) => this.applyEventUpdate(data));
                eventsChannel.bind('delete-event', (data) => {
                    this.events = this.events.filter(ev => String(ev.no) !== String(data?.no));
                });
            } catch (e) {
                console.warn('Pusher initialization skipped:', e);
            }
        }
    };
}
