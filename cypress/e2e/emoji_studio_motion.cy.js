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
        cy.get('.atg-trait-pill[data-trait-group="eye_shape"][data-trait-value="monolid"]').click();
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
        cy.get('.atg-react-btn, .atg-section-chip, .atg-trait-pill:visible').each($el => {
            expect($el[0].getBoundingClientRect().height).to.be.at.least(44);
        });
        cy.document().then(doc => {
            expect(doc.documentElement.scrollWidth).to.be.at.most(390);
        });
        cy.screenshot('emoji-studio-mobile', { capture: 'viewport' });
        cy.get('@consoleError').should('not.have.been.called');
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

        // 1. Click "Set as Profile Picture" (#atgSaveAvatarBtn) on the 3D Avatar stage
        cy.get('#atgSaveAvatarBtn').click();
        cy.wait('@saveAvatarReq').its('response.statusCode').should('eq', 200);
        cy.get('.swal2-confirm').click({ force: true });

        // 2. Switch to "Photo-to-Living Avatar" tab
        cy.contains('.atg-tab-btn', 'Photo-to-Living Avatar').click();
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
        cy.get('@consoleError').should('not.have.been.called');
    });
});
