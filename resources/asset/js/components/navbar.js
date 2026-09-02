import { format, render } from "timeago.js"
import { id, showError, qSel } from '@shared'
import { toSentenceCase } from "./helper/general"
import axios from "axios"

// Update notification badge
function updateNotificationBadge(count) {
    const badge = id('notification_count');
    const headerBadge = id('header_notif_count');
    
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
        if (text.includes('post') || text.includes('photo') || text.includes('memory')) return '📸';
        if (text.includes('like') || text.includes('heart')) return '❤️';
        if (text.includes('comment')) return '💬';
        if (text.includes('house')) return '🏡';
        if (text.includes('meeting') || text.includes('event')) return '📅';
        return '🔔';
    };

    const emoji = getEmoji(data.notification_type || '', data.notification_name || '', data.notification_content || '');
    const isUnread = data.notification_status === 'new';
    const { sender_id, notification_name, notification_content, created_at, no } = data;
    const randomNumber = Math.floor(100 + Math.random() * 900);
    const bannerId = `notificationBar${sender_id}${randomNumber}`;

    const formattedTitle = toSentenceCase(notification_name || 'Notification');
    const timeAgoStr = created_at ? format(created_at) : 'Just now';

    return `
    <div id="${bannerId}" class="notif-card-stitch ${isUnread ? 'unread' : ''}">
        <div class="notif-emoji-icon">
            <span>${emoji}</span>
        </div>
        
        <a href="#linkNotification${no}" class="notif-details-link flex-grow-1 text-decoration-none">
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

export const addToNotificationTab = (data) => {
    const tab = qSel('.notification_tab');
    if (tab) {
        tab.insertAdjacentHTML('afterbegin', notificationHTML(data));
    }
};

const yourId = localStorage.getItem('requesterId');
const famCode = localStorage.getItem('requesterFamCode');
const notificationURL = `/member/notifications/id/${yourId}/${famCode}`;

if (yourId && famCode && yourId !== 'null' && famCode !== 'null') {
    axios.get(notificationURL)
        .then(res => {
            const data = res.data.message;
            if (data && data.length > 0) {
                sessionStorage.setItem('notificationCount', data.length);
                updateNotificationBadge(data.length);

                data.forEach(element => {
                    addToNotificationTab(element);
                });

                const updateNotificationTiming = document.querySelectorAll(".notification_timeago");
                if (updateNotificationTiming && updateNotificationTiming.length > 0) {
                    render(updateNotificationTiming);
                }
            } else {
                sessionStorage.setItem('notificationCount', 0);
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
            showError(error);
        });
}

// Dropdown controls
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

// Dismiss listener delegation
const initDeleteOnce = () => {
    const tab = document.getElementById('notification_tab');
    if (!tab) return;

    tab.addEventListener('click', e => {
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
