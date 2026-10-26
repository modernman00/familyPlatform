import { format, render } from "timeago.js"
import { id, showError, qSel } from '@shared'
import { toSentenceCase } from "./helper/general"
import axios from "axios"

// Update notification badge
function updateNotificationBadge(count) {
    const badge = id('notification_count');
    const headerBadge = id('header_notif_count');
    const mobileBadge = id('pwa_bottom_badge');
    
    const countNum = parseInt(count || '0');
    const displayStr = countNum > 99 ? '99+' : (countNum > 0 ? countNum.toString() : '');

    if (badge) {
        if (countNum <= 0) {
            badge.style.display = 'none';
            badge.textContent = '';
        } else {
            badge.textContent = displayStr;
            badge.style.display = 'flex';
        }
    }

    if (headerBadge) {
        if (countNum <= 0) {
            headerBadge.style.display = 'none';
            headerBadge.textContent = '';
        } else {
            headerBadge.textContent = displayStr;
            headerBadge.style.display = 'flex';
        }
    }

    if (mobileBadge) {
        if (countNum <= 0) {
            mobileBadge.style.display = 'none';
            mobileBadge.textContent = '';
        } else {
            mobileBadge.textContent = displayStr;
            mobileBadge.style.display = 'inline-flex';
        }
    }

    if (typeof navigator !== 'undefined' && 'setAppBadge' in navigator) {
        if (countNum > 0) {
            navigator.setAppBadge(countNum).catch(() => {});
        } else {
            navigator.clearAppBadge().catch(() => {});
        }
    }
}

// Google Stitch Notification Item Template
const notificationHTML = (data) => {
    // Determine friendly emoji / icon based on type and content
    const getEmoji = (type, name = '', content = '') => {
        const text = `${type} ${name} ${content}`.toLowerCase();
        if (text.includes('birthday') || text.includes('cake')) return '🎂';
        if (text.includes('anniversary') || text.includes('celebrate') || text.includes('cypress') || text.includes('party')) return '🥂';
        if (text.includes('crash') || text.includes('chaos') || text.includes('alert') || text.includes('warning')) return '🧧';
        if (text.includes('test') || text.includes('lab')) return '🧪';
        if (text.includes('wedding') || text.includes('marriage')) return '💍';
        if (text.includes('friend') || text.includes('request') || text.includes('connect')) return '👋';
        if (text.includes('reel') || text.includes('video')) return '🎬';
        if (text.includes('announcement')) return '📢';
        if (text.includes('journal') || text.includes('chronicle')) return '📖';
        if (text.includes('capsule')) return '⏳';
        if (text.includes('dispute') || text.includes('tribunal') || text.includes('roundtable')) return '⚖️';
        if (text.includes('trivia')) return '🏆';
        if (text.includes('post') || text.includes('photo') || text.includes('memory')) return '📸';
        if (text.includes('like') || text.includes('heart')) return '❤️';
        if (text.includes('comment')) return '💬';
        if (text.includes('house')) return '🏡';
        if (text.includes('meeting') || text.includes('event')) return '📅';
        return '🔔';
    };

    const emoji = getEmoji(data.notification_type || '', data.notification_name || '', data.notification_content || '');
    const isUnread = data.notification_status === 'new';
    const { sender_id, notification_name, notification_content, created_at, notification_date, no } = data;
    const randomNumber = Math.floor(100 + Math.random() * 900);
    const bannerId = `notificationBar${sender_id}${randomNumber}`;

    const formattedTitle = toSentenceCase(notification_name || 'Notification');
    const timestamp = created_at || notification_date;
    const timeAgoStr = timestamp ? format(timestamp) : 'Just now';

    return `
    <div id="${bannerId}" class="notif-card-stitch ${isUnread ? 'unread' : ''}">
        <div class="notif-emoji-icon">
            <span>${emoji}</span>
        </div>
        
        <a href="${data.action_url || '/profilePage'}" 
           class="notif-details-link flex-grow-1 text-decoration-none"
           data-no="${no}"
           data-action-url="${data.action_url || '/profilePage'}">
            <h6 class="notif-item-title">${formattedTitle}</h6>
            <p class="notif-item-desc">${notification_content || ''}</p>
            <span class="notif-item-time">${timeAgoStr}</span>
        </a>

        <button class="notif-dismiss-btn" 
                data-no="${no}"
                data-is="${sender_id}"
                type="button"
                id="deleteNotification${sender_id}${randomNumber}"
                title="Dismiss">
            <i class="bi bi-x-lg" style="pointer-events: none;"></i>
        </button>
    </div>
    `;
};

export const increaseNotificationCount = () => {
    const current = parseInt(sessionStorage.getItem('notificationCount') || '0') + 1;
    sessionStorage.setItem('notificationCount', current);
    updateNotificationBadge(current);
};

export const addToNotificationTab = (data, position = 'afterbegin') => {
    const tab = qSel('.notification_tab');
    if (tab) {
        tab.insertAdjacentHTML(position, notificationHTML(data));
    }
};

const fetchUserNotifications = () => {
    // Only attempt to poll/fetch notifications if notification UI elements exist on the page
    const hasNotificationUI = document.getElementById('notificationBtn') || document.querySelector('.notification_tab');
    if (!hasNotificationUI) {
        return;
    }

    const yourId = localStorage.getItem('requesterId');
    const famCode = localStorage.getItem('requesterFamCode');
    const notificationURL = (yourId && famCode && yourId !== 'null' && famCode !== 'null')
        ? `/member/notifications/id/${encodeURIComponent(yourId)}/${encodeURIComponent(famCode)}`
        : '/member/notifications/user';

    axios.get(notificationURL)
        .then(res => {
            const data = res.data.message;
            if (data && Array.isArray(data) && data.length > 0) {
                sessionStorage.setItem('notificationCount', data.length);
                updateNotificationBadge(data.length);

                const tab = qSel('.notification_tab');
                if (tab) tab.innerHTML = '';

                // Backend returns notifications ordered newest-first (DESC);
                // append in sequence ('beforeend') so the newest stays at the top.
                data.forEach(element => {
                    addToNotificationTab(element, 'beforeend');
                });

                const updateNotificationTiming = document.querySelectorAll(".notification_timeago");
                if (updateNotificationTiming && updateNotificationTiming.length > 0) {
                    render(updateNotificationTiming);
                }
            } else {
                sessionStorage.setItem('notificationCount', '0');
                updateNotificationBadge(0);

                const tab = qSel('.notification_tab');
                if (tab && !tab.children.length) {
                    tab.innerHTML = `
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50"></i>
                        <span class="small fw-medium">All caught up! No notifications</span>
                    </div>`;
                }
            }
        })
        .catch(error => {
            // Silently handle error if session expired or unauthenticated
            if (error?.response?.status === 401) {
                localStorage.removeItem('requesterId');
                localStorage.removeItem('requesterFamCode');
                return;
            }
            if (error?.response?.status !== 404) {
                showError(error);
            }
        });
};
export { updateNotificationBadge, fetchUserNotifications };

if (typeof window !== 'undefined') {
    window.updateNotificationBadge = updateNotificationBadge;
    window.fetchUserNotifications = fetchUserNotifications;
}

// Dropdown controls
const initNavbarControls = () => {
    const notificationBtn = id('notificationBtn');
    const notificationDropdown = id('notificationDropdown');
    const markAllReadBtn = id('markAllRead');

    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function (e) {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });

        notificationDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function () {
            const unreadItems = document.querySelectorAll('.notif-card-stitch.unread');
            unreadItems.forEach(item => {
                item.classList.remove('unread');
            });

            sessionStorage.setItem('notificationCount', '0');
            updateNotificationBadge(0);
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initNavbarControls();
        fetchUserNotifications();
    });
} else {
    initNavbarControls();
    fetchUserNotifications();
}

// Smooth scroll and highlight an item navigated via notification
export const scrollToAndHighlightItem = (hash) => {
    if (!hash || hash === '#' || hash === '#!') return;
    const rawId = hash.replace(/^#/, '');

    let attempts = 0;
    const maxAttempts = 30; // 3 seconds total (polling every 100ms)
    const interval = setInterval(() => {
        attempts++;

        let el = document.getElementById(rawId)
            || document.querySelector(hash)
            || document.querySelector(`[data-id="${rawId}"]`)
            || document.querySelector(`[id*="${rawId}"]`);

        // Heuristic fallbacks for specific known sections
        if (!el) {
            if (rawId === 'familyJoinRequests' || rawId.startsWith('familyJoinReq')) {
                el = document.querySelector('.family-join-request-card') || document.getElementById('familyJoinRequests');
            } else if (rawId === 'migrationRequests' || rawId.startsWith('migrationReq')) {
                el = document.querySelector('.family-migration-request-card') || document.getElementById('migrationRequests');
            } else if (rawId === 'migrationNudges' || rawId.startsWith('migrationNudge')) {
                el = document.querySelector('.family-migration-nudge-card') || document.getElementById('migrationNudges');
            } else if (rawId === 'friendRequestsCard' || rawId.toLowerCase().includes('friend')) {
                el = document.getElementById('friendRequestsCard') || document.querySelector('.requestFriendClass');
            } else if (rawId === 'eventHeader' || rawId.startsWith('linkNotification')) {
                el = document.getElementById('eventHeader') || document.getElementById('eventList');
            } else if (rawId.startsWith('post')) {
                el = document.getElementById(rawId) || document.querySelector(`.${rawId}`);
            } else if (rawId.startsWith('comment')) {
                el = document.getElementById(rawId) || document.querySelector(`.${rawId}`) || document.querySelector(`[data-comment-no="${rawId.replace(/\D/g, '')}"]`);
            } else if (rawId === 'memoriesCard' || rawId === 'milestonesCard' || rawId === 'memoriesMilestonesWidget' || rawId === 'memoriesWidgetDesktop' || rawId.startsWith('memory') || rawId.startsWith('milestone') || rawId.startsWith('flashback')) {
                el = document.getElementById('memoriesCard')
                    || document.getElementById('milestonesCard')
                    || document.getElementById('mobileMemoriesCard')
                    || document.getElementById('mobileMilestonesCard')
                    || document.getElementById('memoriesWidgetDesktop')
                    || document.getElementById('memoriesMilestonesWidget')
                    || document.getElementById('mobileMemoriesMilestones')
                    || document.querySelector('.mobile-highlights-container');
            }
        }

        if (el) {
            clearInterval(interval);
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('notif-item-target-highlight');
            setTimeout(() => {
                el.classList.remove('notif-item-target-highlight');
            }, 3000);
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            if (rawId.startsWith('linkNotification')) {
                const eventHeader = document.getElementById('eventHeader') || document.getElementById('eventList');
                if (eventHeader) {
                    eventHeader.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    eventHeader.classList.add('notif-item-target-highlight');
                    setTimeout(() => eventHeader.classList.remove('notif-item-target-highlight'), 3000);
                }
            }
        }
    }, 100);
};

export const handleNotificationClickNavigation = (targetUrl, e) => {
    if (!targetUrl) return;

    try {
        const parsed = new URL(targetUrl, window.location.origin);
        const currentPath = window.location.pathname.toLowerCase().replace(/\/$/, '');
        const targetPath = parsed.pathname.toLowerCase().replace(/\/$/, '');
        const isCurrentPage = (currentPath === targetPath) || 
            (currentPath === '' && targetPath === '/profilepage') ||
            (currentPath === '/profilepage' && targetPath === '');

        if (isCurrentPage) {
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
            }

            if (parsed.hash) {
                if (window.location.hash !== parsed.hash) {
                    history.pushState(null, '', parsed.hash);
                }
                scrollToAndHighlightItem(parsed.hash);
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } else {
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
                window.location.href = targetUrl;
            }
        }
    } catch (err) {
        window.location.href = targetUrl;
    }
};

// Dismiss and navigation listener delegation
const initDeleteOnce = () => {
    const tab = document.getElementById('notification_tab');
    if (!tab) return;

    tab.addEventListener('click', e => {
        // Handle clicking the notification body to navigate directly to the item itself
        const link = e.target.closest('.notif-details-link');
        if (link) {
            const no = link.getAttribute('data-no');
            const targetUrl = link.getAttribute('data-action-url') || link.getAttribute('href') || '/profilePage';

            // Mark as read asynchronously in background
            if (no) {
                axios.put(`/removeNotification/${encodeURIComponent(no)}`).catch(() => {});
            }

            // Visual update: remove unread styling & decrement badge count
            const card = link.closest('.notif-card-stitch');
            if (card && card.classList.contains('unread')) {
                card.classList.remove('unread');
                const currentCount = parseInt(sessionStorage.getItem('notificationCount') || '1') - 1;
                const newValues = currentCount > 0 ? currentCount : 0;
                sessionStorage.setItem('notificationCount', newValues);
                updateNotificationBadge(newValues);
            }

            // Auto-close notification dropdown
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.remove('show');
            }

            handleNotificationClickNavigation(targetUrl, e);
            return;
        }

        // Handle clicking the dismiss button
        const btn = e.target.closest('button[id*="deleteNotification"]');
        if (!btn) return;

        e.stopPropagation();
        const bannerId = btn.id.replace('deleteNotification', 'notificationBar');
        const no = btn.getAttribute('data-no');
        const url = `/removeNotification/${no}`;

        axios.put(url)
            .then(response => {
                if (response.data.message === 'Notification marked as read') {
                    const item = document.getElementById(bannerId);
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(10px)';
                        setTimeout(() => {
                            item.remove();
                            if (!tab.querySelectorAll('.notif-card-stitch').length) {
                                tab.innerHTML = `
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-bell-slash fs-3 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="small fw-medium">All caught up! No notifications</span>
                                </div>`;
                            }
                        }, 200);
                    }

                    const currentCount = parseInt(sessionStorage.getItem('notificationCount') || '1') - 1;
                    const newValues = currentCount > 0 ? currentCount : 0;
                    sessionStorage.setItem('notificationCount', newValues);
                    updateNotificationBadge(newValues);
                }
            })
            .catch(showError);
    });
};

document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', initDeleteOnce)
    : initDeleteOnce();

if (typeof window !== 'undefined') {
    window.scrollToAndHighlightItem = scrollToAndHighlightItem;
    window.handleNotificationClickNavigation = handleNotificationClickNavigation;

    if (window.location.hash) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => scrollToAndHighlightItem(window.location.hash), 300);
            });
        } else {
            setTimeout(() => scrollToAndHighlightItem(window.location.hash), 300);
        }
    }

    window.addEventListener('hashchange', () => {
        if (window.location.hash) {
            scrollToAndHighlightItem(window.location.hash);
        }
    });

    window.addEventListener('pwa:badge-updated', (e) => {
        if (typeof e.detail?.count === 'number') {
            updateNotificationBadge(e.detail.count);
        }
    });
    window.addEventListener('pwa:notification-received', () => {
        fetchUserNotifications();
    });
}
