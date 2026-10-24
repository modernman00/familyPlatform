import { loginFully } from '../support/login';

// RFC-0005 — "Change Our Family Code" card in Settings → Family Network.
// The dev account's role is persistent, so assertions branch on what the card shows;
// the vote, majority rule, apply and chaos paths are covered in tests/Feature/FamilyCodeRenameTest.php.
describe('Change family code by family vote', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        cy.request({ method: 'POST', url: '/api/test/clear-rate-limit', failOnStatusCode: false });
        loginFully();
        cy.visit('/accountSetting#v-pills-familycode');
        cy.get('#codeRenameCard', { timeout: 15000 }).scrollIntoView().should('be.visible');
    });

    it('shows exactly one coherent state with 44px touch targets', () => {
        cy.get('#codeRenameCard').then(($card) => {
            if ($card.find('#codeRenameOpen').length) {
                cy.get('#codeRenameOpen').should('contain.text', 'Vote open');
                cy.contains('#codeRenameCard a', 'Go to the vote').should('be.visible');
            } else if ($card.find('#codeRenameForm').length) {
                cy.get('#newFamilyCode').should('be.visible');
                cy.get('#btnProposeCodeRename').should('be.visible');
            } else {
                cy.get('#codeRenameCard .alert-secondary').should('be.visible');
            }
        });

        // Members who are not the creator only see the explanation, so there may be nothing to tap.
        cy.get('#codeRenameCard').then(($card) => {
            $card.find('button:visible, a.btn:visible').each((_, el) => {
                expect(Cypress.$(el).outerHeight(), 'touch target height').to.be.at.least(43);
            });
        });
    });

    it('refuses an invalid code without starting a vote', () => {
        cy.get('#codeRenameCard').then(($card) => {
            if (!$card.find('#codeRenameForm').length) {
                cy.log('Account is not the code creator, or a vote is already open: skipped');
                return;
            }
            cy.get('#newFamilyCode').type('ab');
            cy.get('#btnProposeCodeRename').click();
            cy.get('#codeRenameAlert', { timeout: 10000 }).should('be.visible').and('contain.text', '4 to 12');
            cy.get('#codeRenameOpen').should('not.exist');
        });
    });
});
