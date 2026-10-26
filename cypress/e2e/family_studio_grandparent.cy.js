import { loginFully } from '../support/login';

describe('Family Studio - Add Grandparent', () => {

    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
        cy.visit('/familyStudio');
    });

    it('opens the grandparent form, not the parents form, from the Add Grandparent card', () => {
        cy.contains('.add-card-title', 'Add Grandparent', { timeout: 15000 }).click();

        cy.get('#studioStep2-parents', { timeout: 10000 }).should('be.visible');
        cy.get('#studioParentsHeading').should('have.text', 'Grandparents Details');
        cy.get('#studioParentsMode').should('have.value', 'grandparents');
        cy.get('#studioSaveParentsBtn').should('contain.text', 'Save Grandparents');
        cy.get('#studioParentsForm').contains('label', 'Grandfather First Name').should('exist');
    });

    it('never points the grandparent form at the user\'s own node', () => {
        cy.contains('.add-card-title', 'Add Grandparent', { timeout: 15000 }).click();

        cy.get('#studioParentsForm [name=base_node_id]').invoke('val').then((base) => {
            expect(base, 'a parent is selected').to.not.equal('');
            // The user's own card lives in Generation 0; the base must be a parent (Generation -1) card.
            cy.get('#group-gen-0 #relative-card-node-' + base).should('not.exist');
            cy.get('#group-gen-minus1 #relative-card-node-' + base).should('exist');
        });
    });

    it('opens the regular parents form from Add Father and keeps parents wording', () => {
        cy.get('body').then(($body) => {
            const $add = $body.find('.add-card-title:contains("Add Father")');
            if ($add.length === 0) {
                cy.log('Father already on record - parents form path covered by PHPUnit');
                return;
            }
            cy.wrap($add.first()).click();
            cy.get('#studioParentsHeading').should('have.text', 'Parents Details');
            cy.get('#studioParentsMode').should('have.value', 'parents');
            cy.get('#studioGrandSideWrap').should('have.class', 'd-none');
        });
    });

    describe('saving grandparents', () => {
        const createdNodeIds = [];
        const uid = Date.now().toString().slice(-6);

        const post = (win, path, body) => win.fetch(path, {
            method: 'POST',
            body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json());

        afterEach(() => {
            // Remove everything this test created so the dev tree is left as found.
            cy.window().then(async (win) => {
                while (createdNodeIds.length) {
                    const fd = new win.FormData();
                    fd.append('node_id', String(createdNodeIds.pop()));
                    await post(win, '/member/organogram/editor/delete', fd);
                }
            });
        });

        it('saves grandparents under the chosen parent without touching the parent cards', () => {
            // Ensure a father exists; create a parent pair through the real UI if not.
            cy.get('body').then(($body) => {
                if ($body.find('.add-card-title:contains("Add Father")').length === 0) return;
                cy.contains('.add-card-title', 'Add Father').click();
                cy.get('#studioStep2-parents').should('be.visible');
                cy.get('#studioParentsForm [name=father_first_name]').type('E2Edad' + uid);
                cy.get('#studioParentsForm [name=father_last_name]').type('Parent');
                cy.get('#studioParentsForm [name=mother_first_name]').type('E2Emum' + uid);
                cy.get('#studioParentsForm [name=mother_last_name]').type('Parent');
                cy.intercept('POST', '**/editor/parents').as('addParents');
                cy.get('#studioSaveParentsBtn').click();
                cy.wait('@addParents').then(({ response }) => {
                    const m = response.body.message || {};
                    [m.father?.id, m.mother?.id].filter(Boolean).forEach((id) => createdNodeIds.push(id));
                });
                cy.get('#studioAddRelativeModal').should('not.be.visible');
            });

            cy.get('#group-gen-minus1 > .row > .relative-card-container .relative-name').then(($names) => {
                const parentNamesBefore = [...$names].map((el) => el.textContent.trim());

                cy.contains('.add-card-title', 'Add Grandparent').click();
                cy.get('#studioGrandSide option').should('have.length.at.least', 1);
                // Let the Bootstrap modal finish its fade/focus-trap before typing (typing mid-transition dropped characters)
                cy.get('#studioAddRelativeModal.show').should('be.visible');
                cy.get('#studioParentsForm [name=father_first_name]').should('be.visible').and('not.be.disabled');
                cy.wait(600);
                cy.get('#studioParentsForm [name=father_first_name]').clear().type('Gramps' + uid);
                cy.get('#studioParentsForm [name=father_last_name]').clear().type('Elder');
                cy.get('#studioParentsForm [name=mother_first_name]').clear().type('Granny' + uid);
                cy.get('#studioParentsForm [name=mother_last_name]').clear().type('Elder');

                cy.intercept('POST', '**/editor/parents').as('addGrandparents');
                cy.get('#studioSaveParentsBtn').click();
                cy.wait('@addGrandparents').then(({ request, response }) => {
                    expect(response.statusCode).to.eq(200);
                    expect(response.body.status).to.eq('success');
                    const m = response.body.message || {};
                    [m.father?.id, m.mother?.id].filter(Boolean).forEach((id) => createdNodeIds.push(id));
                    expect(request.body, 'rel_mode sent').to.contain('grandparents');
                });

                // Grandparents rendered in their own row...
                cy.get('#studioGrandparentsRow', { timeout: 10000 })
                    .should('contain.text', 'Gramps' + uid)
                    .and('contain.text', 'Granny' + uid);
                cy.contains('#studioGrandparentsRow .role-chip', 'Grandfather').should('exist');

                // ...and the existing Father/Mother cards were NOT overwritten.
                cy.get('#group-gen-minus1 > .row > .relative-card-container .relative-name').then(($after) => {
                    const after = [...$after].map((el) => el.textContent.trim());
                    parentNamesBefore.forEach((n) => expect(after).to.include(n));
                });
            });
        });
    });
});
