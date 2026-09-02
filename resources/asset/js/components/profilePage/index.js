"use strict";

localStorage.removeItem('redirect')

import { profileFeed } from "./feedComponent";
import { profileSidebar, upcomingEvents } from "./sidebarComponents";

if (window.Alpine && typeof window.Alpine.data === 'function') {
    window.Alpine.data('profileFeed', profileFeed);
    window.Alpine.data('profileSidebar', profileSidebar);
    window.Alpine.data('upcomingEvents', upcomingEvents);
}
window.profileFeed = profileFeed;
window.profileSidebar = profileSidebar;
window.upcomingEvents = upcomingEvents;

import "./modal"
import "./img"
import "./rsvpBtn"
import "./allEvents"
import "./registerPushNotification"
import "./periodicSync"
import "./createEvent"
// import "./friendRequestCard"  // Disabled in favor of Alpine.js profileSidebar component
import "./editProfile"
import "./postEmojiImgProcess"
// import "./commentEmojiTest"



import { initEngagementListeners, initMemories } from "./engagement"
import "../reels/reelsPlayer"
import "../kinship/kinshipRadar"

document.addEventListener('DOMContentLoaded', () => {
    initEngagementListeners();
    initMemories();
});





