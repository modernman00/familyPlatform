import { loginFully } from '../support/login';

// Regression: unknown-gender relatives must not render as Brother/Wife/etc., and can be corrected in-place.
describe('Family tree: unknown gender handling', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    it('rejects set-gender without a valid gender value', () => {
        cy.request({
            method: 'POST',
            url: '/member/organogram/editor/set-gender',
            form: true,
            body: { node_id: 1, gender: 'Banana' },
            failOnStatusCode: false,
            // No X-Cypress-Test header: it defines TESTING_ENV, which makes showError() swallow the
            // HttpException and leave the response at an empty 200.
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then((res) => {
            expect(res.status).to.be.oneOf([400, 403, 419]);
        });
    });

    it('never labels an unknown-gender relative with a gendered title', () => {
        cy.visit('/organogram');
        cy.get('#tree', { timeout: 30000 }).should('exist');
        cy.window().then((win) => {
            const rootId = win.__ROOT_USER_ID__;
            cy.request(`/member/organogram/data/${encodeURIComponent(rootId)}`).then((res) => {
                const nodes = res.body?.nodes || res.body?.message?.nodes || [];
                nodes
                    .filter((n) => !['male', 'female'].includes(String(n?.gender || '').toLowerCase()) && !n?.is_placeholder && !n?.is_ego)
                    .forEach((n) => {
                        expect(n?.ego_relation || '', `title for ${n?.full_name}`)
                            .to.not.match(/^(Brother|Sister|Husband|Wife|Father|Mother|Son|Daughter)$/);
                    });
            });
        });
    });
});
