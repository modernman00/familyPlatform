// Run: node tests/js/domAudit.test.mjs
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const domAuditSrc = fs.readFileSync(new URL('../../scripts/dom_audit.js', import.meta.url), 'utf8');

function runWithMockDom({
    innerWidth = 390,
    innerHeight = 844,
    docScrollWidth = 390,
    images = [],
    mediaStages = [],
    pillBars = [],
} = {}) {
    const makeEl = (tag, opts = {}) => ({
        tagName: tag.toUpperCase(),
        id: opts.id || '',
        className: opts.className || '',
        innerText: opts.innerText || '',
        value: opts.value || '',
        children: opts.children || [],
        dataset: opts.dataset || {},
        scrollWidth: opts.scrollWidth ?? 100,
        clientWidth: opts.clientWidth ?? 100,
        complete: opts.complete ?? true,
        naturalWidth: opts.naturalWidth ?? 800,
        currentSrc: opts.currentSrc ?? '',
        parentElement: null,
        classList: { contains: (c) => (opts.className || '').split(/\s+/).includes(c) },
        getAttribute: (k) => (opts.attrs && k in opts.attrs ? opts.attrs[k] : null),
        hasAttribute: (k) => Boolean(opts.attrs && k in opts.attrs),
        querySelector: () => null,
        closest: (sel) => (sel === '.post-media-stage' && opts.inMediaStage ? {} : null),
        getBoundingClientRect: () => ({
            width: opts.rectWidth ?? 300,
            height: opts.rectHeight ?? 240,
        }),
        _style: {
            display: opts.display || 'block',
            visibility: 'visible',
            opacity: '1',
            flexWrap: opts.flexWrap || 'wrap',
            color: 'rgb(15, 23, 42)',
            backgroundColor: 'rgb(255, 255, 255)',
            backgroundImage: 'none',
            fontSize: '14px',
            lineHeight: '20px',
            letterSpacing: '0px',
            textTransform: 'none',
        },
    });

    const imgEls = images.map((o) => makeEl('img', o));
    const stageEls = mediaStages.map((o) => makeEl('div', { className: 'post-media-stage', ...o }));
    const pillEls = pillBars.map((o) => makeEl('div', { className: 'fp-stage-converter__row', ...o }));

    const document = {
        documentElement: { scrollWidth: docScrollWidth },
        body: makeEl('body'),
        querySelectorAll: (sel) => {
            if (sel === 'img') return imgEls;
            if (sel === '.post-media-stage') return stageEls;
            if (sel.includes('.fp-stage-converter__row')) return pillEls;
            return [];
        },
    };

    const windowObj = {
        innerWidth,
        innerHeight,
        location: { href: 'https://olaogun.test/member/profilePage' },
        getComputedStyle: (el) => el._style,
        document,
    };
    windowObj.window = windowObj;

    const context = vm.createContext({
        window: windowObj,
        document,
        Array,
        Object,
        Number,
        Boolean,
        Math,
        parseFloat,
        parseInt,
    });
    vm.runInContext(domAuditSrc, context);
    return windowObj.runSovereignDomAudit();
}

// 1. Clean 4-state pass: healthy image, >=220px stage, wrapping pill bar
{
    const report = runWithMockDom({
        images: [{ attrs: { src: '/resources/images/post/a.jpg', alt: 'Family' }, naturalWidth: 800, rectWidth: 320, rectHeight: 240 }],
        mediaStages: [{ rectHeight: 240 }],
        pillBars: [{ flexWrap: 'wrap', scrollWidth: 350, clientWidth: 350 }],
    });
    assert.equal(report.passed, true, 'Clean state should pass');
    assert.equal(report.violationCount, 0);
}

// 2. Broken <picture> currentSrc image detected even if src attr differs
{
    const report = runWithMockDom({
        images: [{ attrs: { src: '/resources/images/post/a.jpg', alt: 'Family' }, currentSrc: '/resources/images/post/a.avif', naturalWidth: 0 }],
    });
    assert.equal(report.passed, false);
    assert.ok(report.violations.some((v) => v.rule === 'BROKEN_IMAGE' && v.message.includes('a.avif')));
}

// 3. Collapsed .post-media-stage (< 120px) detected
{
    const report = runWithMockDom({
        mediaStages: [{ rectHeight: 24 }],
    });
    assert.equal(report.passed, false);
    assert.ok(report.violations.some((v) => v.rule === 'COLLAPSED_MEDIA_STAGE'));
}

// 4. Clipped pill bar (nowrap + scrollWidth > clientWidth without scroll affordance) detected
{
    const report = runWithMockDom({
        pillBars: [{ flexWrap: 'nowrap', scrollWidth: 520, clientWidth: 350 }],
    });
    assert.equal(report.passed, false);
    assert.ok(report.violations.some((v) => v.rule === 'PILL_BAR_CLIPPED'));
}

console.log('domAudit: all 4 assertions passed');
