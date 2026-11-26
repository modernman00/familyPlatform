"use strict";

localStorage.removeItem('redirect');

import "./modal";
import "./img";
import "./rsvpBtn";
import "./allEvents";
import "./registerPushNotification";
import "./periodicSync";
import "./editProfile";
import "./postEmojiImgProcess";
import { initEngagementListeners, initMemories } from "./engagement";
import { initPwaShareTargetHandler, initSmartClipboardPaste } from "./pwaShareTarget";
import "../kinship/kinshipRadar";

const ensureComposerBundle = () => Promise.resolve();
window.__ensureComposerBundleInternal = ensureComposerBundle;
window.ensureComposerBundle = ensureComposerBundle;

let createEventBundlePromise = null;
const ensureCreateEventBundle = () => {
    if (!createEventBundlePromise) {
        createEventBundlePromise = import(/* webpackChunkName: 'profile-create-event' */ "./createEvent")
            .catch((err) => {
                createEventBundlePromise = null;
                console.error('[CreateEventChunk]', err);
                throw err;
            });
    }
    return createEventBundlePromise;
};

const runBootHooks = () => {
    initEngagementListeners();
    initMemories();
    initPwaShareTargetHandler();
    initSmartClipboardPaste();

    document.addEventListener('show.bs.modal', (e) => {
        if (e?.target?.id === 'createEventModal') {
            ensureCreateEventBundle();
        }
    });

    // Warm createEvent chunk quietly during browser idle time after initial mobile scroll/paint settles
    const warmModalChunks = () => {
        ensureCreateEventBundle();
    };
    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(warmModalChunks, { timeout: 2200 });
    } else {
        setTimeout(warmModalChunks, 1200);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runBootHooks, { once: true });
} else {
    runBootHooks();
}





