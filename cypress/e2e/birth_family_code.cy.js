import { loginFully } from '../support/login';

// RFC-0004 — Birth / Maiden Family Code card in Settings → Family Network.
// The dev account's state is persistent (a created code is permanent), so the flow
// assertions branch on the card's data-state; create/link/approve/clear are covered
// end-to-end in tests/Feature/BirthFamilyCodeTest.php.
describe('Birth / Maiden Family Code', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.request({ method: 'POST', url: '/api/test/clear-rate-limit', failOnStatusCode: false });
        loginFully();
        cy.visit('/accountSetting#v-pills-familycode');
        cy.get('#birthFamilyCard', { timeout: 15000 }).scrollIntoView().should('be.visible');
    });

    const withState = (fn) => cy.get('#birthFamilyCard').invoke('attr', 'data-state').then(fn);

    // Same-origin POST using the page's own CSRF token, like the settings JS does.
    const postAction = (fields) =>
        cy.get('meta[name="csrf-token"]').invoke('attr', 'content').then((token) =>
            cy.window().then((win) => {
                const fd = new win.FormData();
                Object.entries({ ...fields, token }).forEach(([k, v]) => fd.append(k, v));
                return cy.wrap(
                    win
                        .fetch('/accountSetting', {
                            method: 'POST',
                            body: fd,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                        })
                        .then((r) => r.text().then((t) => { try { return JSON.parse(t); } catch (e) { return { message: `HTTP ${r.status} redirected=${r.redirected} url=${r.url} ct=${r.headers.get('content-type')} body=${t.slice(0, 200)}` }; } }))
                );
            })
        );

    it('renders one coherent state with 44px touch targets', () => {
        withState((state) => {
            expect(['none', 'pending', 'active', 'unverified']).to.include(state);

            if (state === 'active') {
                cy.get('#birthCodeValue').invoke('text').should('match', /^[A-Z0-9]{4,12}$/);
                cy.get('#btnCopyBirthCode').should('be.visible');
                cy.get('#btnCreateBirthCode').should('not.exist');
            } else if (state === 'pending') {
                cy.get('#cancelBirthLinkBtn').should('be.visible');
            } else {
                cy.get('#btnCreateBirthCode').should('be.visible');
                cy.get('#btnRequestBirthLink').should('be.visible');
                cy.get('#birthMaidenName').should('be.visible');
            }
        });

        cy.get('#birthFamilyCard button:visible').each(($btn) => {
            expect($btn.outerHeight(), 'touch target height').to.be.at.least(43);
        });
        cy.get('#birthFamilyCard').scrollIntoView();
        cy.wait(600);
        cy.screenshot('birth-family-card', { capture: 'viewport' });
    });

    it('rejects a free-text family code the user does not hold (BOLA PoC)', () => {
        postAction({ action: 'updateSecondaryFamilyCode', otherFamCode: 'DOE123' }).then((res) => {
            expect(String(res.status)).to.not.equal('success');
            expect(res.message || '').to.match(/only use a family you created|approved into|cannot be identical/i);
        });
    });

    it('rejects an invalid maiden name server-side', () => {
        postAction({ action: 'createBirthFamilyCode', maiden_name: '<script>alert(1)</script>' }).then((res) => {
            expect(res.message || '').to.match(/letters only|already have a birth family/i);
        });
    });

    it('shows a friendly error for an unknown family code when linking', () => {
        withState((state) => {
            if (state !== 'none' && state !== 'unverified') {
                cy.log(`state is ${state}; link form not shown`);
                return;
            }
            cy.get('#birthLinkCode').type('ZZQ9999');
            cy.get('#birthInviterFirst').type('Grace');
            cy.get('#birthInviterLast').type('Adeyemi');
            cy.get('#birthInviterContact').type('nobody@example.com');
            cy.get('#btnRequestBirthLink').click();
            cy.get('.swal2-popup', { timeout: 10000 }).should('contain.text', 'was not found');
            cy.get('.swal2-confirm').click();
            cy.get('#birthFamilyAlert').should('be.visible').and('contain.text', 'was not found');
        });
    });

    it('asks for a maiden name before creating', () => {
        withState((state) => {
            if (state !== 'none' && state !== 'unverified') {
                cy.log(`state is ${state}; create form not shown`);
                return;
            }
            cy.get('#birthMaidenName').clear();
            cy.get('#btnCreateBirthCode').click();
            cy.get('#birthFamilyAlert').should('be.visible').and('contain.text', 'maiden');
        });
    });
});
