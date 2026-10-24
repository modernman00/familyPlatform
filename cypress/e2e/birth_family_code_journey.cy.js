import { loginFully } from '../support/login';

// RFC-0004 — full browser journeys for the Birth / Maiden Family Code, on throwaway users
// seeded straight into the dev DB and removed afterwards (cleanupE2EUsers).
//   A. create  → code appears, primary untouched, parents tab routes to it, a mother can use it
//   B. link    → request pending → a family member approves by emailed link → active → unlink
// Local valet runs a 5-worker FPM pool and page loads occasionally take 20s+, so give requests headroom.
describe('Birth / Maiden Family Code — end-to-end journeys', { responseTimeout: 120000, defaultCommandTimeout: 15000 }, () => {
    Cypress.on('uncaught:exception', () => false);

    let users = [];
    let wifeCreate;
    let wifeLink;
    let mother;

    before(() => {
        cy.task('seedE2EUsers', [
            { firstName: 'Bisi', lastName: 'Okafor' },
            { firstName: 'Dayo', lastName: 'Bello' },
            { firstName: 'Grace', lastName: 'Adeyemi' },
        ]).then((seeded) => {
            users = seeded;
            [wifeCreate, wifeLink, mother] = seeded;
        });
    });

    after(() => {
        cy.task('cleanupE2EUsers', users);
    });

    // A cache-busting query makes every call a real page load (a hash-only cy.visit would keep the stale DOM).
    const openFamilyTab = () => {
        cy.visit(`/accountSetting?r=${Date.now()}#v-pills-familycode`);
        cy.get('#birthFamilyCard', { timeout: 20000 }).scrollIntoView().should('be.visible');
    };

    // Success dialogs from the card reload the page when dismissed.
    // `timeout` is generous for the link request: it sends the approval email/push synchronously.
    const dismissSuccessAndReload = (textFragment, timeout = 20000) => {
        cy.get('.swal2-popup', { timeout }).should('contain.text', textFragment);
        cy.get('.swal2-confirm').click();
    };

    it('A. creates a birth family code that parents can use', () => {
        loginFully(wifeCreate.email, wifeCreate.password);
        openFamilyTab();

        cy.get('#birthFamilyCard').should('have.attr', 'data-state', 'none');
        cy.get('#birthMaidenName').clear().type('Adeyemi');
        cy.get('#btnCreateBirthCode').click();
        dismissSuccessAndReload('birth family code is #ADE');

        cy.get('#birthFamilyCard', { timeout: 20000 }).should('have.attr', 'data-state', 'active');
        cy.get('#birthCodeStatusBadge').should('contain.text', 'Created by you');
        cy.get('#btnClearBirthCode').should('not.exist'); // a created code is permanent

        cy.get('#birthCodeValue')
            .invoke('text')
            .then((raw) => {
                const code = raw.trim();
                expect(code).to.match(/^ADE\d{3}$/);

                // Primary household code untouched; membership is a verified admin one.
                cy.task('getBirthState', wifeCreate.id).then((state) => {
                    expect(state.famCode, 'primary famCode').to.eq(wifeCreate.famCode);
                    expect(state.otherFamCode, 'otherFamCode').to.eq(code);
                    expect(state.families).to.deep.include({ family_code: code, role: 'admin', status: 'approved' });
                });

                // "Invite my parents" routes to the birth family: Parents tab scope pill.
                cy.get('#v-pills-parents-tab').click({ force: true });
                cy.get('#parents-scope-maternal').check({ force: true });
                cy.get('#parentsLineageScope').should('have.value', 'maternal');
                cy.get('#parentsLineageCode').should('have.value', code);

                // Her mother can use the code before anyone holds it as a household code…
                cy.request({
                    method: 'POST',
                    url: '/api/family-code/check',
                    body: { family_code: code },
                    headers: { 'X-Cypress-Test': 'true' },
                }).its('body.exists').should('eq', true);

                // …and an invite for it opens registration straight into the maiden family.
                cy.request({
                    method: 'POST',
                    url: '/api/test/create-invite-token',
                    body: { family_code: code, first_name: 'Grace', last_name: 'Adeyemi' },
                    headers: { 'X-Cypress-Test': 'true' },
                })
                    .its('body.token')
                    .then((token) => {
                        cy.visit(`/register?invite=${token}`);
                        cy.get('#famCode', { timeout: 15000 }).should('have.value', code);
                    });
            });

        // A second create is refused.
        openFamilyTab();
        cy.get('#createBirthCodeForm').should('not.exist');
    });

    it('B. links to an existing family, waits for approval, then unlinks', () => {
        loginFully(wifeLink.email, wifeLink.password);
        openFamilyTab();

        cy.get('#birthFamilyCard').should('have.attr', 'data-state', 'none');
        cy.get('#birthLinkCode').type(mother.famCode.toLowerCase()); // normalised server-side
        cy.get('#birthInviterFirst').type(mother.firstName);
        cy.get('#birthInviterLast').type(mother.lastName);
        cy.get('#birthInviterContact').type(mother.email);
        cy.get('#btnRequestBirthLink').click();
        dismissSuccessAndReload('Request sent', 90000);

        cy.get('#birthFamilyCard', { timeout: 20000 }).should('have.attr', 'data-state', 'pending');
        cy.get('#birthCodeStatusBadge').should('contain.text', 'Awaiting approval');
        cy.get('#cancelBirthLinkBtn').should('be.visible');
        // The household "transfer" alert must NOT appear for a birth link.
        cy.get('#pendingRequestAlert').should('not.exist');

        // The family member approves through the emailed signed link.
        cy.task('getLatestApprovalRequest', wifeLink.id).then((req) => {
            expect(req.purpose).to.eq('birth_link');
            cy.request({ url: `/api/test/get-approval-request/${req.no}`, headers: { 'X-Cypress-Test': 'true' } })
                .its('body.approval_token')
                .then((token) => {
                    cy.request({ url: `/api/family-code/approve/${req.no}?token=${token}`, headers: { Accept: 'text/html' } })
                        .then((res) => {
                            expect(res.status).to.eq(200);
                            const title = (res.body.match(/<title>([^<]*)<\/title>/) || [])[1] || res.body.slice(0, 200);
                            expect(title, 'approval page').to.contain('Approved');
                        });
                });
        });

        openFamilyTab();
        cy.get('#birthFamilyCard').should('have.attr', 'data-state', 'active');
        cy.get('#birthCodeStatusBadge').should('contain.text', 'Linked');
        cy.get('#birthCodeValue').should('have.text', mother.famCode);
        cy.task('getBirthState', wifeLink.id).then((state) => {
            expect(state.famCode, 'primary famCode unchanged by a birth link').to.eq(wifeLink.famCode);
            expect(state.otherFamCode).to.eq(mother.famCode);
        });

        // Unlink: link and membership go, the mother's family stays.
        cy.get('#btnClearBirthCode').click();
        cy.get('.swal2-confirm').click(); // "Yes, unlink"
        dismissSuccessAndReload('Done');

        cy.get('#birthFamilyCard', { timeout: 20000 }).should('have.attr', 'data-state', 'none');
        cy.task('getBirthState', wifeLink.id).then((state) => {
            expect(state.otherFamCode ?? '').to.eq('');
            expect(state.families.map((f) => f.family_code)).to.not.include(mother.famCode);
        });
        cy.task('getBirthState', mother.id).then((state) => {
            expect(state.famCode, "mother's household is untouched").to.eq(mother.famCode);
        });
    });
});
