"use strict";

localStorage.removeItem('redirect');

import "./modal";
import "./img";
import "./rsvpBtn";
import "./registerPushNotification";
import "./periodicSync";
import "./editProfile";
import { initEngagementListeners, initMemories } from "./engagement";
import { initPwaShareTargetHandler, initSmartClipboardPaste } from "./pwaShareTarget";
import "../kinship/kinshipRadar";

// Code-split heavy modal engines (142KB+ of composer media/Tus/emoji/video compression & event creation)
// so mobile first-load & scroll stay feather-light, while preloading on first tap/modal intent or idle window.
let composerBundlePromise = null;
const ensureComposerBundle = () => {
    if (!composerBundlePromise) {
        composerBundlePromise = Promise.all([
            import(/* webpackChunkName: 'profile-composer' */ "./allEvents"),
            import(/* webpackChunkName: 'profile-composer' */ "./postEmojiImgProcess"),
        ]).catch((err) => console.error('[ComposerChunk]', err));
    }
    return composerBundlePromise;
};

let createEventBundlePromise = null;
const ensureCreateEventBundle = () => {
    if (!createEventBundlePromise) {
        createEventBundlePromise = import(/* webpackChunkName: 'profile-create-event' */ "./createEvent")
            .catch((err) => console.error('[CreateEventChunk]', err));
    }
    return createEventBundlePromise;
};

const runBootHooks = () => {
    initEngagementListeners();
    initMemories();
    initPwaShareTargetHandler();
    initSmartClipboardPaste();

    // Eagerly attach intent preloads on composer & event triggers so modal opening feels instantaneous
    ['openPostModalTrigger', 'openPostModalTriggerMobile'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('pointerdown', ensureComposerBundle, { once: true, passive: true });
            el.addEventListener('focusin', ensureComposerBundle, { once: true, passive: true });
        }
    });

    document.addEventListener('show.bs.modal', (e) => {
        const targetId = e?.target?.id;
        if (targetId === 'postModal') {
            ensureComposerBundle();
        } else if (targetId === 'createEventModal') {
            ensureCreateEventBundle();
        }
    });

    // Warm both chunks quietly during browser idle time after initial mobile scroll/paint settles
    const warmModalChunks = () => {
        ensureComposerBundle();
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





