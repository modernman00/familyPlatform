import { loginFully } from '../support/login';

/**
 * IDOR sweep — the test user is "397755OLUSOLA" in family "SHO", with an
 * approved connection to "432292OLAWALE" (family "MODERNMAN") but none to
 * "964649OLAWALE" (also "MODERNMAN"). Profile views follow the directory rule
 * (own family OR approved connection); family-tree views are family-only.
 */
describe('IDOR sweep — cross-family access is refused', () => {
    Cypress.on('uncaught:exception', () => false);

    beforeEach(() => {
        loginFully();
    });

    const req = (opts) =>
        cy.getCookie('XSRF-TOKEN').then((c) =>
            cy.request({
                failOnStatusCode: false,
                headers: { 'X-XSRF-TOKEN': c ? c.value : '', 'X-Requested-With': 'XMLHttpRequest' },
                ...opts,
            }),
        );

    it('profile view: allows an approved connection, refuses an unconnected member', () => {
        req({ url: '/allMembers/seeProfile/432292OLAWALE' }).then((r) => {
            expect(r.status).to.eq(200);
        });
        req({ url: '/allMembers/seeProfile/964649OLAWALE' }).then((r) => {
            expect(r.status).to.eq(403);
        });
    });

    it('family tree: allows own family, refuses another family (even a connection)', () => {
        req({ url: '/organogram/397755OLUSOLA' }).then((r) => expect(r.status).to.eq(200));
        req({ url: '/organogram/432292OLAWALE' }).then((r) => expect(r.status).to.eq(403));
        req({ url: '/member/organogram/data/432292OLAWALE' }).then((r) => expect(r.status).to.eq(403));
    });

    it('organogram node details: refuses a node in another family', () => {
        // node id 1 is in "SHO", node id 380 is in "Dayo"
        req({ url: '/member/organogram/node/380' }).then((r) => {
            expect(r.status).to.not.eq(200);
        });
    });

    it('removeProfile: refuses deleting a connection the caller is not part of', () => {
        req({
            method: 'DELETE',
            url: '/allMembers/removeProfile/117540OLAWALE/937619LAFANE',
        }).then((r) => {
            expect(r.status).to.eq(403);
        });
    });
});
