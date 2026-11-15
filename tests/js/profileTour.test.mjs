// Run: node tests/js/profileTour.test.mjs
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);

function createMemoryStorage() {
    const store = new Map();
    return {
        getItem(k) { return store.has(k) ? store.get(k) : null; },
        setItem(k, v) { store.set(k, String(v)); },
        removeItem(k) { store.delete(k); },
        clear() { store.clear(); },
    };
}

function createFakeDom() {
    const headChildren = [];
    const bodyChildren = [];
    const elementsById = new Map();
    const listeners = new Map();

    function makeEl(tag, id = '') {
        const attrs = new Map();
        const children = [];
        const el = {
            tagName: tag.toUpperCase(),
            id,
            className: '',
            style: {},
            textContent: '',
            innerHTML: '',
            parentNode: null,
            offsetParent: {},
            offsetHeight: 260,
            setAttribute(k, v) { attrs.set(k, String(v)); },
            getAttribute(k) { return attrs.get(k) ?? null; },
            appendChild(child) { child.parentNode = el; children.push(child); return child; },
            removeChild(child) {
                const idx = children.indexOf(child);
                if (idx >= 0) children.splice(idx, 1);
                child.parentNode = null;
            },
            addEventListener(ev, fn) {
                if (!listeners.has(ev)) listeners.set(ev, []);
                listeners.get(ev).push(fn);
            },
            removeEventListener() {},
            querySelector(sel) {
                if (sel === '[data-sspot-card]') return makeEl('div');
                if (sel === '[data-sspot-highlight]') return makeEl('div');
                if (sel === '[data-sspot-backdrop]') return makeEl('div');
                if (sel.startsWith('[data-sspot-action=')) return makeEl('button');
                return null;
            },
            querySelectorAll() { return []; },
            getBoundingClientRect() { return { top: 40, left: 40, bottom: 140, right: 240, width: 200, height: 100 }; },
            getClientRects() { return [{ width: 200, height: 100 }]; },
            scrollIntoView() {},
            focus() {},
        };
        if (id) elementsById.set(id, el);
        return el;
    }

    const doc = {
        readyState: 'complete',
        activeElement: null,
        head: {
            appendChild(el) {
                headChildren.push(el);
                if (el.id) elementsById.set(el.id, el);
            },
        },
        body: {
            appendChild(el) { bodyChildren.push(el); el.parentNode = doc.body; },
            removeChild(el) {
                const idx = bodyChildren.indexOf(el);
                if (idx >= 0) bodyChildren.splice(idx, 1);
                el.parentNode = null;
            },
        },
        createElement(tag) { return makeEl(tag); },
        getElementById(id) { return elementsById.get(id) || null; },
        querySelector(sel) {
            if (sel === '#visible-target') return makeEl('div', 'visible-target');
            return null;
        },
        querySelectorAll(sel) {
            if (sel === '#visible-target') return [makeEl('div', 'visible-target')];
            return [];
        },
        addEventListener() {},
        removeEventListener() {},
    };

    return { doc, bodyChildren, headChildren };
}

// Install minimal global browser environment for testing
const fake = createFakeDom();
globalThis.document = fake.doc;
globalThis.window = {
    localStorage: createMemoryStorage(),
    sessionStorage: createMemoryStorage(),
    innerWidth: 1440,
    innerHeight: 900,
    addEventListener() {},
    removeEventListener() {},
    getComputedStyle() { return { display: 'block', visibility: 'visible', opacity: '1' }; },
};

const SovereignSpotlightTour = require('../../public/js/SovereignSpotlightTour.js');
const ProfileTourManager = require('../../public/js/profileTour.js');

// 1. Two-tier dismissal: temporary close vs permanent dismiss
{
    window.localStorage.clear();
    window.sessionStorage.clear();
    let permanentCalled = 0;
    let closeCalled = 0;

    const tour = new SovereignSpotlightTour({
        storageKey: 'test_profile_tour',
        permanentOnClose: false,
        showPermanentDismiss: true,
        steps: [
            { id: 's1', target: null, title: 'Step 1', description: 'Welcome' },
            { id: 's2', target: '#visible-target', title: 'Step 2', description: 'Second' },
        ],
        onClose: () => { closeCalled += 1; },
        onPermanentDismiss: () => { permanentCalled += 1; },
    });

    assert.equal(tour.isPermanentlyDismissed(), false, 'Initially not permanently dismissed');
    assert.equal(tour.isSessionClosed(), false, 'Initially not session-closed');

    tour.start(0);
    assert.equal(tour.isOpen, true, 'Tour opens on start()');

    // Temporary close (e.g. clicking × or pressing Esc)
    tour.close();
    assert.equal(tour.isOpen, false, 'Tour closes');
    assert.equal(closeCalled, 1, 'onClose fired');
    assert.equal(tour.isSessionClosed(), true, 'Session close recorded');
    assert.equal(tour.isPermanentlyDismissed(), false, 'Temporary close must NOT permanently dismiss');
    assert.equal(tour.autoStartIfFirstVisit(0), false, 'Auto-start suppressed for rest of session');

    // Manual relaunch still works after temporary close
    tour.start(0);
    assert.equal(tour.isOpen, true, 'Manual start() works after temporary close');

    // Permanent dismissal
    tour.dismissPermanently();
    assert.equal(tour.isOpen, false, 'Tour closes on permanent dismiss');
    assert.equal(permanentCalled, 1, 'onPermanentDismiss fired');
    assert.equal(tour.isPermanentlyDismissed(), true, 'Permanent dismissal persisted');

    // Manual relaunch STILL works even after permanent dismissal
    tour.start(0);
    assert.equal(tour.isOpen, true, 'Manual start() works even after permanent dismissal');
    tour.destroy();
}

// 2. Server-side dismissal flag suppresses auto-start
{
    window.localStorage.clear();
    window.sessionStorage.clear();
    const tour = new SovereignSpotlightTour({
        storageKey: 'test_server_dismissed',
        serverDismissed: true,
        steps: [{ id: 's1', target: null, title: 'Step 1', description: 'Welcome' }],
    });
    assert.equal(tour.isPermanentlyDismissed(), true, 'Server dismissal flag honoured');
    assert.equal(tour.autoStartIfFirstVisit(0), false, 'Auto-start suppressed when serverDismissed is true');
    tour.start(0);
    assert.equal(tour.isOpen, true, 'Manual start() works when serverDismissed is true');
    tour.destroy();
}

// 3. Optional steps with missing targets are skipped cleanly
{
    window.localStorage.clear();
    window.sessionStorage.clear();
    const tour = new SovereignSpotlightTour({
        storageKey: 'test_optional_steps',
        steps: [
            { id: 'intro', target: null, title: 'Intro', description: 'Always shown' },
            { id: 'missing', target: '#non-existent-widget', optional: true, title: 'Missing', description: 'Skipped' },
            { id: 'present', target: '#visible-target', optional: true, title: 'Present', description: 'Shown' },
        ],
    });
    tour.start(0);
    assert.equal(tour.steps.length, 2, 'Optional missing target skipped');
    assert.equal(tour.steps[0].id, 'intro');
    assert.equal(tour.steps[1].id, 'present');
    tour.next();
    assert.equal(tour.getCurrentStep().id, 'present');
    tour.prev();
    assert.equal(tour.getCurrentStep().id, 'intro');
    tour.destroy();
}

// 4. Completing the final step calls onComplete and marks seen
{
    window.localStorage.clear();
    window.sessionStorage.clear();
    let completed = 0;
    const tour = new SovereignSpotlightTour({
        storageKey: 'test_complete',
        steps: [
            { id: 's1', target: null, title: 'One', description: 'First' },
            { id: 's2', target: null, title: 'Two', description: 'Last' },
        ],
        onComplete: () => { completed += 1; },
    });
    tour.start(0);
    tour.next();
    assert.equal(tour.currentIndex, 1);
    tour.next();
    assert.equal(tour.isOpen, false, 'Tour closes after final step');
    assert.equal(completed, 1, 'onComplete invoked');
    assert.equal(tour.isPermanentlyDismissed(), true, 'Completion marks tour seen');
}

// 5. ProfileTourManager step coverage & British English check
{
    const steps = ProfileTourManager.buildProfileTourSteps();
    assert.ok(Array.isArray(steps) && steps.length >= 10, 'Profile tour covers comprehensive functionalities');
    const ids = steps.map((s) => s.id);
    for (const requiredId of [
        'profile-welcome',
        'cover-and-avatar',
        'family-codes',
        'family-chat',
        'quick-actions',
        'post-composer',
        'reels-tray',
        'family-feed',
        'upcoming-events',
        'heritage-widgets',
        'family-network',
        'search-and-alerts',
    ]) {
        assert.ok(ids.includes(requiredId), `Includes step: ${requiredId}`);
    }
    const allText = steps.map((s) => `${s.title} ${s.description} ${(s.uses || []).join(' ')} ${s.tip || ''}`).join(' ');
    assert.equal(/\b(personalize|customize|optimize|organize|color|center)\b/i.test(allText), false, 'Strictly uses British English');
}

console.log('ALL PROFILE TOUR JS TESTS PASSED');
