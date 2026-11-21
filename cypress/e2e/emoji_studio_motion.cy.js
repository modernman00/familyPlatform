import { loginFully } from '../support/login';

describe('Living Avatar & Emoji Studio', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.visit('/member/emoji-studio', {
            onBeforeLoad(win) {
                cy.spy(win.console, 'error').as('consoleError');
            }
        });
        cy.get('#atgAvatarViewport .atg-avatar-svg').should('exist');
    });

    it('renders the motion rig and follows the pointer', () => {
        cy.get('.atg-avatar-svg.atg-js-driven').should('exist');
        cy.get('.atg-avatar-svg .atg-av-gaze').should('have.length', 2);
        cy.get('#atgAvatarViewport').then($vp => {
            const r = $vp[0].getBoundingClientRect();
            cy.window().then(win => {
                win.dispatchEvent(new win.PointerEvent('pointermove', { clientX: r.right + 300, clientY: r.top + r.height / 2 }));
            });
        });
        cy.get('.atg-avatar-svg .atg-av-gaze').first()
            .invoke('attr', 'transform')
            .should(t => {
                const x = parseFloat((t.match(/translate\(([-\d.]+)/) || [])[1]);
                expect(x).to.be.greaterThan(1);
            });
        cy.screenshot('emoji-studio-desktop', { capture: 'viewport' });
    });

    it('re-renders when inclusive traits change', () => {
        cy.get('.atg-section-chip[data-atg-section="hair"]').click();
        cy.get('.atg-trait-pill[data-trait-group="hairstyle"][data-trait-value="hijab"]').click()
            .should('have.attr', 'aria-pressed', 'true');
        cy.get('.atg-avatar-svg').should('have.attr', 'data-atg-traits').and('contain', 'hijab');
        cy.get('.atg-section-chip[data-atg-section="face"]').click();
        cy.get('.atg-trait-pill[data-trait-group="skin_tone"][data-trait-value="porcelain_cool"]').click();
        cy.get('.atg-trait-pill[data-trait-group="eye_shape"][data-trait-value="monolid"]').click()
            .should('have.attr', 'aria-pressed', 'true');
        cy.get('.atg-avatar-svg').should('have.attr', 'data-atg-traits').and('contain', 'monolid');
        cy.get('.atg-avatar-svg .atg-av-gaze').should('have.length', 2);
    });

    it('offers gender expression without locking other traits', () => {
        cy.get('.atg-trait-pill[data-trait-group="presentation"]').should('have.length', 3);
        cy.get('.atg-trait-pill[data-trait-group="presentation"][data-trait-value="feminine"]').click();
        cy.get('.atg-avatar-svg').should('have.attr', 'data-atg-traits').and('contain', 'presentation:feminine');
        cy.get('.atg-section-chip[data-atg-section="hair"]').click();
        cy.get('.atg-trait-pill[data-trait-group="facial_hair"][data-trait-value="full_beard"]').click();
        cy.get('.atg-avatar-svg').should('have.attr', 'data-atg-traits')
            .and('contain', 'presentation:feminine').and('contain', 'full_beard');
    });

    it('plays reactions with particles', () => {
        cy.get('.atg-react-btn[data-reaction="love"]').click();
        cy.get('.atg-avatar-svg').should('have.class', 'atg-react-love');
        cy.get('.atg-react-layer .atg-react-particle').should('have.length.greaterThan', 0);
        cy.screenshot('emoji-studio-reaction', { capture: 'viewport' });
    });

    it('swaps people emojis to the chosen skin tone', () => {
        cy.contains('.atg-tab-btn', 'Living Emojis').click();
        cy.get('.atg-tone-chip[data-emoji-tone="dark"]').click();
        cy.get('.atg-emoji-card[data-emoji-base="family_wave_3d"]')
            .should('have.attr', 'data-emoji-id', 'family_wave_dark_3d');
        cy.get('.atg-emoji-card[data-emoji-base="family_wave_3d"] svg .atgm-wave').should('exist');
        cy.screenshot('emoji-studio-catalog', { capture: 'viewport' });
    });

    it('keeps 44px touch targets and no console errors on mobile', () => {
        cy.viewport(390, 844);
        cy.reload();
        cy.get('#atgAvatarViewport .atg-avatar-svg').should('exist');
        cy.get('.atg-react-btn, .atg-tab-btn, .atg-section-chip, .atg-trait-pill:visible').each($el => {
            expect($el[0].getBoundingClientRect().height).to.be.at.least(44);
        });
        const noHorizontalScroll = () => cy.document().then(doc => {
            expect(doc.documentElement.scrollWidth).to.be.at.most(390);
        });
        noHorizontalScroll();
        // Cards must sit fully inside the viewport (a clipped parent can hide overflow from scrollWidth).
        cy.get('.atg-preview-stage, .atg-controls-card').each($el => {
            expect($el[0].getBoundingClientRect().right).to.be.at.most(390);
        });
        cy.get('.atg-react-btn[data-reaction="clap"]').click();
        cy.get('.atg-avatar-svg').should('have.class', 'atg-react-clap');
        cy.get('.atg-react-btn[data-reaction="point"]').click();
        cy.get('.atg-avatar-svg').should('have.class', 'atg-react-point');
        // Body copy must stay legible on a phone (>= 13px).
        cy.get('.atg-studio-subtitle, .atg-trait-pill:visible, .atg-motion-toggle small').each($el => {
            expect(parseFloat(getComputedStyle($el[0]).fontSize)).to.be.at.least(13);
        });
        cy.screenshot('emoji-studio-mobile', { capture: 'fullPage' });

        cy.get('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]').click();
        noHorizontalScroll();
        cy.get('#photo-avatar-tab').screenshot('emoji-studio-mobile-photo');

        cy.get('.atg-tab-btn[data-atg-tab="catalog-tab"]').click();
        noHorizontalScroll();
        cy.get('.atg-emoji-card-meaning').each($el => {
            expect(parseFloat(getComputedStyle($el[0]).fontSize)).to.be.at.least(13);
        });
        cy.get('.atg-emoji-card[data-emoji-base="family_point_3d"] svg .atgm-point').should('exist');
        cy.get('.atg-emoji-card[data-emoji-base="family_clap_3d"] svg .atgm-clap').should('exist');
        cy.get('#catalog-tab').screenshot('emoji-studio-mobile-catalog');
        cy.get('@consoleError').then((spy) => {
            const realErrors = (spy.args || []).filter((args) => !String(args[0] || '').includes('Created TensorFlow Lite XNNPACK delegate'));
            expect(realErrors, 'Console errors: ' + JSON.stringify(realErrors)).to.have.length(0);
        });
    });

    it('saves a valid image and shows the studio shortcut on the profile photo', () => {
        // The saved avatar must load as an <img> (strict XML), not just inline.
        cy.window()
            .then(win => win.fetch('/api/emoji/render-avatar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ hairstyle: 'hijab', presentation: 'feminine', eyewear: 'cat_eye' })
            }).then(r => r.json()))
            .then(data => {
                const parsed = new DOMParser().parseFromString(data.svg, 'image/svg+xml');
                expect(parsed.querySelector('parsererror'), 'avatar SVG is well-formed XML').to.be.null;
            });
        cy.visit('/member/profilePage');
        cy.get('#leftColProfileAvatar').should($img => {
            expect($img[0].naturalWidth, 'profile photo loads').to.be.greaterThan(0);
        });
        cy.get('#avatarStudioShortcut').should('be.visible').then($a => {
            const r = $a[0].getBoundingClientRect();
            expect(r.width).to.be.at.least(38);
        });
        cy.get('#leftColProfileAvatar').parent().screenshot('profile-avatar-shortcut', { padding: 30 });
        cy.get('#avatarStudioShortcut').click();
        cy.location('pathname').should('eq', '/member/emoji-studio');
    });

    it('supports Set as Profile Picture, Use Profile Photo, Smile/Dance expressions, and Save Living Photo as Profile Picture', () => {
        cy.intercept('POST', '/api/emoji/save-avatar').as('saveAvatarReq');
        cy.intercept('POST', '/api/emoji/photo-to-avatar').as('photoToAvatarReq');

        // Ensure the user has a real/living photo on file so the look switcher renders
        cy.get('body').then($body => {
            if ($body.find('.atg-look-btn[data-look="avatar"]').length === 0) {
                const tinyPng = Cypress.Buffer.from(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=',
                    'base64'
                );
                cy.get('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]').click();
                cy.get('#atgPhotoUploadInput').selectFile(
                    { contents: tinyPng, fileName: 'portrait.png', mimeType: 'image/png' },
                    { force: true }
                );
                cy.wait('@photoToAvatarReq').its('response.statusCode').should('eq', 200);
                cy.get('#atgSaveLivingPortraitBtn').click();
                cy.wait('@saveAvatarReq').its('response.statusCode').should('eq', 200);
                cy.visit('/member/emoji-studio');
                cy.get('#atgAvatarViewport .atg-avatar-svg').should('exist');
            }
        });

        // 1. Switch to 3D Cartoon Avatar look and click "Set as Profile Picture" (#atgSaveAvatarBtn)
        cy.get('.atg-look-btn[data-look="avatar"]').click();
        cy.get('#atgSaveAvatarBtn').should('have.css', 'display').and('not.eq', 'none');
        cy.get('#atgSaveAvatarBtn').click({ force: true });
        cy.wait('@saveAvatarReq').its('response.statusCode').should('eq', 200);
        cy.get('.swal2-confirm').click({ force: true });

        // 2. Switch to "Photo-to-Living Avatar" tab
        cy.get('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]').click();
        cy.get('#photo-avatar-tab').should('be.visible');

        // 3. Click "Use Profile Photo" (#atgUseCurrentProfilePhotoBtn)
        cy.get('#atgUseCurrentProfilePhotoBtn').click();
        cy.wait('@photoToAvatarReq').its('response.statusCode').should('eq', 200);
        cy.get('#atgPhotoCanvasStatus').should('contain', 'Profile photo');

        // 4. Trigger Smile, Dance, and Wink on the Living Photo
        cy.get('.atg-photo-react-btn[data-photo-action="smile"]').click();
        cy.get('#atgPhotoCanvasStatus').should('contain', 'Warm Smile');
        cy.get('.atg-photo-react-btn[data-photo-action="dance"]').click();
        cy.get('#atgPhotoCanvasStatus').should('contain', 'Dancing & Grooving');
        cy.get('#atgPhotoReactLayer .atg-react-particle').should('have.length.greaterThan', 0);

        // 5. Select Dance loop and click "Save Living Photo as Profile Picture" (#atgSaveLivingPortraitBtn)
        cy.get('#atgPhotoChoreographySelect').select('dance');
        cy.get('#atgSaveLivingPortraitBtn').click();
        cy.wait('@saveAvatarReq').then(interception => {
            expect(interception.response.statusCode).to.eq(200);
            expect(interception.request.body.mode).to.eq('living_photo');
            expect(interception.request.body.choreography).to.eq('dance');
            expect(interception.response.body.success).to.eq(true);
            expect(interception.response.body.url).to.match(/\.svg$/);
        });
        cy.screenshot('emoji-studio-living-photo-dance', { capture: 'viewport' });
        cy.get('@consoleError').then((spy) => {
            const realErrors = (spy.args || []).filter((args) => !String(args[0] || '').includes('Created TensorFlow Lite XNNPACK delegate'));
            expect(realErrors, 'Console errors: ' + JSON.stringify(realErrors)).to.have.length(0);
        });
    });

    it('transforms a portrait into a stylised avatar with Before/After split slider and 6 selectable styles', () => {
        cy.get('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]').click();
        cy.get('#photo-avatar-tab').should('be.visible');

        // Verify ATG Photo-to-Avatar Styliser engine, 6 style cards, and Before/After split slider exist
        cy.window().its('AtgPhotoAvatarStyliser').should('be.an', 'object');
        cy.get('.atg-style-card').should('have.length', 6);
        cy.get('#atgPhotoCompareStage').should('have.class', 'is-comparing');
        cy.get('#atgCompareHandle').should('be.visible').and('have.attr', 'role', 'slider');

        // Switch styles and verify pixels on #atgPhotoLivingCanvas genuinely transform
        cy.get('.atg-style-card[data-avatar-style="pop_art"]').click()
            .should('have.attr', 'aria-pressed', 'true');

        cy.window().then(win => {
            const beforeC = win.document.getElementById('atgPhotoBeforeCanvas');
            const afterC = win.document.getElementById('atgPhotoLivingCanvas');
            const bData = beforeC.getContext('2d').getImageData(60, 60, 80, 80).data;
            const aData = afterC.getContext('2d').getImageData(60, 60, 80, 80).data;
            let diffSum = 0;
            for (let i = 0; i < bData.length; i += 4) {
                diffSum += Math.abs(bData[i] - aData[i]) + Math.abs(bData[i + 1] - aData[i + 1]) + Math.abs(bData[i + 2] - aData[i + 2]);
            }
            expect(diffSum, 'After stylised avatar pixels differ from Before raw photo').to.be.greaterThan(500);
        });

        // Keyboard-adjust the Before/After split slider
        cy.get('#atgCompareHandle').focus().trigger('keydown', { key: 'ArrowRight' });
        cy.get('#atgCompareHandle').should('have.attr', 'aria-valuenow', '55');

        // Verify CSRF (403) and Invalid Style / Polyglot (422) adversarial neutralisation on /api/emoji/transform-photo-avatar
        cy.get('input[name="token"]').first().invoke('val').then((validToken) => {
            cy.request({
                method: 'POST',
                url: '/api/emoji/transform-photo-avatar',
                failOnStatusCode: false,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': 'forged_token_123' },
                body: { token: 'forged_token_123', csrf_token: 'forged_token_123', style: 'illustrated_comic', image_base64: 'data:image/png;base64,iVBORw0KGgo=' }
            }).then((r) => {
                expect(r.status, 'CSRF forgery blocked with 403').to.eq(403);
            });
            cy.request({
                method: 'POST',
                url: '/api/emoji/transform-photo-avatar',
                failOnStatusCode: false,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': String(validToken || '') },
                body: { token: String(validToken || ''), csrf_token: String(validToken || ''), style: '"><script>alert(1)</script>', image_base64: 'data:image/png;base64,iVBORw0KGgo=' }
            }).then((r2) => {
                expect(r2.status, 'Injected style blocked with 422').to.eq(422);
            });
        });

        // Run Sovereign DOM Audit on Living Photo Studio
        cy.readFile('scripts/dom_audit.js').then(code => {
            cy.window().then(win => {
                win.eval(code);
                const report = win.runSovereignDomAudit();
                expect(report.passed, 'Sovereign DOM Audit passed: ' + JSON.stringify(report.violations)).to.eq(true);
            });
        });

        cy.get('@consoleError').then((spy) => {
            const realErrors = (spy.args || []).filter((args) => !String(args[0] || '').includes('Created TensorFlow Lite XNNPACK delegate'));
            expect(realErrors, 'Console errors: ' + JSON.stringify(realErrors)).to.have.length(0);
        });
    });
});
