import { loginFully } from '../support/login';

// RFC-0008: Celebrations card, private wishes, notification deep link, and API guards.
const occasion = {
    key: 'birthday:CY_WALE:2026',
    type: 'birthday',
    target_id: 'CY_WALE',
    title: "Wale Olaogun's birthday",
    subtitle: 'Birthday',
    date_label: 'Today',
    already_wished: false,
};

const stubList = (data) => cy.intercept('GET', '/api/celebrations', { statusCode: 200, body: { status: 'success', data } }).as('celebrations');

describe('Family celebrations (RFC-0008)', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('shows today\'s birthday and sends a private wish', () => {
        stubList({ occasions: [occasion], wishes: [] });
        cy.intercept('POST', '/api/celebrations/wish', { statusCode: 200, body: { status: 'success', message: 'Your wishes have been sent 🎉' } }).as('wish');

        cy.visit('/profilePage');
        cy.wait('@celebrations');
        cy.get('.celebrations-widget:visible').should('have.length', 1).within(() => {
            cy.contains("Wale Olaogun's birthday");
            cy.contains('button', 'Send wishes').click();
        });

        cy.get('#celebrationWishModal').should('be.visible');
        cy.get('#celebrationWishTitle').should('contain.text', "Wale Olaogun's birthday");
        cy.get('#celebrationWishSend').click();
        cy.get('#celebrationWishError').should('contain.text', 'Please write a message');

        cy.get('#celebrationWishMessage').type('Happy birthday, uncle! 🎂');
        cy.get('#celebrationWishSend').click();
        cy.wait('@wish').then(({ request }) => {
            const body = String(request.body);
            expect(body).to.contain('name="type"\r\n\r\nbirthday');
            expect(body).to.contain('name="target_id"\r\n\r\nCY_WALE');
            expect(request.headers).to.have.property('x-xsrf-token');
        });
        cy.get('#celebrationWishModal').should('not.be.visible');
    });

    it('shows the celebrant their wishes as plain text', () => {
        stubList({
            occasions: [],
            wishes: [{ occasion_type: 'birthday', sender_name: 'Ade Viewer', message: '<img src=x onerror=alert(1)> Enjoy!', created_at: new Date().toISOString(), read: false }],
        });
        cy.visit('/profilePage#celebrationWishes');
        cy.wait('@celebrations');
        cy.get('.celebrations-widget:visible').within(() => {
            cy.contains('Your wishes');
            cy.contains('1 new');
            cy.contains('Ade Viewer');
            cy.contains('<img src=x onerror=alert(1)> Enjoy!'); // rendered as text, not HTML
            cy.get('img').should('not.exist');
        });
    });

    it('opens the wish form from a notification link', () => {
        stubList({ occasions: [occasion], wishes: [] });
        cy.visit('/profilePage#celebrate-birthday-CY_WALE');
        cy.wait('@celebrations');
        cy.get('#celebrationWishModal').should('be.visible');
        cy.location('hash').should('eq', '');
    });

    it('hides the card when there is nothing to celebrate', () => {
        stubList({ occasions: [], wishes: [] });
        cy.visit('/profilePage');
        cy.wait('@celebrations');
        cy.get('.celebrations-widget:visible').should('have.length', 0);
    });

    it('rejects wishes without a CSRF token and for unknown celebrations (real API)', () => {
        cy.request({ method: 'POST', url: '/api/celebrations/wish', form: true, failOnStatusCode: false, body: { type: 'birthday', target_id: 'NOBODY', message: 'hi' } })
            .its('status').should('eq', 401);

        cy.getCookie('XSRF-TOKEN').then((cookie) => {
            const token = decodeURIComponent(cookie?.value || '');
            cy.request({
                method: 'POST', url: '/api/celebrations/wish', form: true, failOnStatusCode: false,
                headers: { 'X-XSRF-TOKEN': token },
                body: { token, type: 'birthday', target_id: 'NOBODY_' + Date.now(), message: 'hi' },
            }).its('status').should('eq', 404);
        });
    });
});
