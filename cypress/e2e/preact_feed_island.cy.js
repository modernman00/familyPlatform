import { loginFully } from '../support/login';

describe('Preact Feed Island (Phases 1–4) Browser & DOM Audit', () => {
    Cypress.on('uncaught:exception', () => false);

    const runAuditOnPage = (assertionFn) => {
        cy.readFile('scripts/dom_audit.js', { timeout: 10000 }).then((src) => {
            cy.window().then((win) => {
                if (typeof win.runSovereignDomAudit !== 'function') {
                    win.eval(src);
                }
                expect(typeof win.runSovereignDomAudit).to.eq('function');
                const report = win.runSovereignDomAudit();
                assertionFn(report);
            });
        });
    };

    it('Live E2E Mutation Cycle: classic #postModal create -> Preact edit -> comment -> delete without page reload', () => {
        const uniqueText = `Cypress Preact Classic E2E ${Date.now()}`;
        const editedText = `${uniqueText} - Edited`;
        const commentText = `Cypress comment ${Date.now()}`;

        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });

        cy.visit('/profilePage', { timeout: 120000 });
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);

        // Dismiss cookie banner if present so it does not cover feed actions
        cy.get('body').then(($body) => {
            const acceptBtn = $body.find('button:contains("Accept all")');
            if (acceptBtn.length > 0) {
                cy.wrap(acceptBtn.first()).click({ force: true });
            }
        });

        // 1. Open classic #postModal via #openPostModalTrigger and create a post
        cy.get('#openPostModalTrigger .post-composer__cta').click({ force: true });
        cy.get('#postModal', { timeout: 15000 }).should('be.visible');
        cy.get('#postMessageEditor').click({ force: true }).invoke('text', uniqueText).trigger('input', { force: true });
        cy.get('#postMessage').invoke('val', uniqueText).trigger('input', { force: true });
        cy.get('#submitPost').click({ force: true });
        cy.get('#postModal', { timeout: 65000 }).should('not.be.visible');

        // Verify composer state is 100% flushed after post submission (zero bleed into next post)
        cy.get('#postMessageEditor').should('have.text', '');
        cy.get('#postMessage').should('have.value', '');
        cy.get('#imageUpload').should('have.value', '');
        cy.get('#attachedVideoUrl').should('have.value', '');
        cy.get('#attachedVideoUrls').should('have.value', '');
        cy.get('#composerLinkPreviewContainer').should('have.class', 'd-none');
        cy.get('#imagePreviewContainer').should('have.class', 'd-none');

        cy.contains('#feed-root article.feed-post-card', uniqueText, { timeout: 65000 })
            .should('be.visible')
            .and(($el) => {
                expect($el.attr('id')).to.match(/^post\d+$/);
            })
            .then(($article) => {
                const postId = $article.attr('id');

                // 2. Edit post via kebab menu -> classic #postModal
                cy.get(`#${postId}`).scrollIntoView({ block: 'center' });
                cy.get(`#${postId} button[aria-label="Post options"]`).click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Edit Post').click({ force: true });
                cy.get('#postModal', { timeout: 15000 }).should('be.visible');
                cy.get('#editPostNo').should('have.value', postId.replace(/^post/, ''));
                cy.get('#postMessageEditor').click({ force: true }).invoke('text', editedText).trigger('input', { force: true });
                cy.get('#postMessage').invoke('val', editedText).trigger('input', { force: true });
                cy.get('#submitPost').click({ force: true });
                cy.get('#postModal', { timeout: 50000 }).should('not.be.visible');
                cy.get(`#${postId}`, { timeout: 50000 }).should('contain.text', editedText);

                // 3. Add comment via CommentThread
                cy.get(`#${postId} button[aria-label="Comment on this post"]`).click({ force: true });
                cy.get(`#${postId} input[aria-label="Write a comment"]`).type(commentText, { force: true });
                cy.get(`#${postId} button[aria-label="Send"]`).click({ force: true });
                cy.get(`#${postId} input[aria-label="Write a comment"]`, { timeout: 50000 }).should('have.value', '');
                cy.get(`#${postId}`, { timeout: 50000 }).should('contain.text', commentText);

                // 4. Delete post via kebab menu + confirmation
                cy.get(`#${postId} button[aria-label="Post options"]`).click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Delete Post').click({ force: true });
                cy.get(`#${postId}`).contains('button', 'Confirm Delete').click({ force: true });
                cy.get(`#${postId}`, { timeout: 50000 }).should('not.exist');
            });
    });

    it('Desktop (1440x900): restores classic Hero Composer, mounts Preact feed & sidebars with real posts, and passes DOM Audit', () => {
        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });

        cy.get('#feed-root', { timeout: 30000 }).should('exist');
        cy.get('#kin-requests-root', { timeout: 30000 }).should('exist');
        cy.get('#upcoming-events-root', { timeout: 30000 }).should('exist');
        cy.get('#upcoming-events-root #upcomingEventsSection', { timeout: 30000 }).should('exist').and('contain.text', 'Upcoming Events');
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');

        // Classic Hero Composer with 6 quick-action pills
        cy.get('#openPostModalTrigger').should('be.visible');
        cy.get('#openPostModalTrigger .composer-pill-btn').should('have.length', 6);
        cy.get('[role="tab"]').contains('All Family Memories').should('be.visible');
        cy.get('[role="tab"]').contains('Tagged With You').should('be.visible');

        // Real feed posts must load (not empty state)
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);
        cy.get('#feed-root [role="alert"]').should('not.exist');

        cy.screenshot('preact-feed-desktop-verified', { capture: 'viewport' });
        cy.get('#feed-root article.feed-post-card').first().scrollIntoView({ block: 'center' });
        cy.screenshot('preact-feed-posts-verified', { capture: 'viewport' });

        runAuditOnPage((report) => {
            const criticalViolations = (report.violations || []).filter((v) =>
                ['HORIZONTAL_OVERFLOW', 'MISSING_CSRF_DOM', 'BLACK_ON_RED', 'GHOST_TEXT'].includes(v.rule)
            );
            expect(criticalViolations, JSON.stringify(criticalViolations)).to.have.length(0);
            expect(report.horizontalOverflow, 'No horizontal overflow on Desktop').to.not.be.true;
        });
    });

    it('Mobile (390x844): restores classic mobile Hero Composer, loads real posts with zero horizontal overflow, and passes DOM Audit', () => {
        cy.viewport(390, 844);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/profilePage', { timeout: 120000 });

        cy.get('#feed-root', { timeout: 30000 }).should('exist');
        cy.get('[data-testid="feed-island-shell"]', { timeout: 30000 }).should('be.visible');
        cy.get('#openPostModalTriggerMobile').should('be.visible');
        cy.get('#feed-root article.feed-post-card', { timeout: 65000 }).should('have.length.greaterThan', 0);
        cy.get('#feed-root [role="alert"]').should('not.exist');

        cy.screenshot('preact-feed-mobile-verified', { capture: 'viewport' });

        runAuditOnPage((report) => {
            const criticalViolations = (report.violations || []).filter((v) =>
                ['HORIZONTAL_OVERFLOW', 'MISSING_CSRF_DOM', 'BLACK_ON_RED', 'GHOST_TEXT'].includes(v.rule)
            );
            expect(criticalViolations, JSON.stringify(criticalViolations)).to.have.length(0);
            expect(report.horizontalOverflow, 'No horizontal overflow on Mobile').to.not.be.true;
        });
    });

    it('Fallback (?v=legacy): renders legacy Alpine profileFeed instead of #feed-root', () => {
        cy.viewport(1440, 900);
        loginFully(undefined, undefined, { skipProfileVisit: true });
        cy.visit('/member/ProfilePage?v=legacy', { timeout: 120000 });

        cy.get('[x-data*="profileFeed"]', { timeout: 30000 }).should('exist');
        cy.get('#feed-root').should('not.exist');
    });
});
