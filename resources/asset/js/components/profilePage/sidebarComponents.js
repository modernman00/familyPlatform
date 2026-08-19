import axios from 'axios';

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
        
        init() {
            // Placeholder for potential reactive extensions
        }
    };
}
