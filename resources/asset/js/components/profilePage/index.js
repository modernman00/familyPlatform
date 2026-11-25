"use strict";

localStorage.removeItem('redirect')

import "./modal"
import "./img"
import "./rsvpBtn"
import "./allEvents"
import "./registerPushNotification"
import "./periodicSync"
import "./createEvent"
import "./editProfile"
import "./postEmojiImgProcess"



import { initEngagementListeners, initMemories } from "./engagement"
import { initPwaShareTargetHandler, initSmartClipboardPaste } from "./pwaShareTarget"
import "../reels/reelsPlayer"
import "../kinship/kinshipRadar"

document.addEventListener('DOMContentLoaded', () => {
    initEngagementListeners();
    initMemories();
    initPwaShareTargetHandler();
    initSmartClipboardPaste();
});





