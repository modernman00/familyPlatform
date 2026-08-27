"use strict";

localStorage.removeItem('redirect')

import { profileFeed } from "./feedComponent";
window.profileFeed = profileFeed;

import { profileSidebar, upcomingEvents } from "./sidebarComponents";
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

document.addEventListener('DOMContentLoaded', () => {
    initEngagementListeners();
    initMemories();
});





