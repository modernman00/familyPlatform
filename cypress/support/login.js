// Logs in fully, including the mandatory 2FA step, for specs that just need an
// authenticated session (not the ones that exercise the login UI itself).
//
// This drives the login purely through `cy.request` rather than the browser
// form. The old browser-driven helper was intermittently failing at
// `cy.wait('@loginPost')` with "no response ever occurred": loading the login
// page fires a burst of module/asset requests that all carry the guest session
// cookie, and PHP's per-session file lock + a small FPM worker pool let those
// occasionally hold the lock long enough for the follow-up `POST /login` to
// exceed the 15s wait. Issuing the two auth requests directly, against an idle
// server with no competing page load, removes that contention entirely and is
// also the documented way to seed a `cy.session`.
//
// The 2FA code is read straight from the local dev DB's code_mgt table (via the
// cypress.config.js task) since there's no test-only bypass for the emailed code.
//
// NB: deliberately NOT cacheAcrossSpecs — restoring a session without the full
// login navigation doesn't leave the Bootstrap/Alpine modals enough settle time
// for the specs that open one in their first assertion.

const DEFAULT_EMAIL = 'cypress_test@myfamilyplatform.com';
const DEFAULT_PASSWORD = 'National2';

// Runs the credential POST + 2FA-code POST. Retries the whole exchange if the
// server bounces us (e.g. a transient 401 from a session-regeneration race)
// rather than letting a single blip fail an unrelated spec.
function authenticate(email, password, attempt = 1, maxAttempts = 3) {
    cy.request({ url: '/tests/clear-rate-limit', failOnStatusCode: false });

    cy.request({
        method: 'POST',
        url: '/login',
        body: { email, password },
        failOnStatusCode: false,
    }).then((credRes) => {
        const credOk = [200, 201].includes(credRes.status);

        if (!credOk) {
            if (attempt >= maxAttempts) {
                throw new Error(
                    `loginFully: POST /login returned ${credRes.status} after ${attempt} attempts ` +
                    `— ${JSON.stringify(credRes.body)}`
                );
            }
            cy.log(`loginFully: POST /login -> ${credRes.status}, retrying (attempt ${attempt + 1})`);
            authenticate(email, password, attempt + 1, maxAttempts);
            return;
        }

        cy.task('getLatest2FACode', email).then((code) => {
            expect(code, '2FA code from code_mgt').to.be.a('string');

            cy.request({
                method: 'POST',
                url: '/login/code',
                body: { code },
                failOnStatusCode: false,
            }).then((codeRes) => {
                if (![200, 201].includes(codeRes.status)) {
                    if (attempt >= maxAttempts) {
                        throw new Error(
                            `loginFully: POST /login/code returned ${codeRes.status} after ${attempt} attempts ` +
                            `— ${JSON.stringify(codeRes.body)}`
                        );
                    }
                    cy.log(`loginFully: POST /login/code -> ${codeRes.status}, retrying (attempt ${attempt + 1})`);
                    authenticate(email, password, attempt + 1, maxAttempts);
                }
            });
        });
    });
}

export function loginFully(email = DEFAULT_EMAIL, password = DEFAULT_PASSWORD) {
    cy.session(
        [email, password],
        () => {
            authenticate(email, password);

            // Confirm the session actually reached an authenticated state before
            // it gets cached.
            cy.request({ url: '/profilePage', failOnStatusCode: false, followRedirect: false })
                .its('status')
                .should('eq', 200);
        },
        {
            validate() {
                // A live, authenticated session serves /profilePage directly; a dead
                // one 302s to /login. followRedirect:false makes that unambiguous.
                cy.request({ url: '/profilePage', failOnStatusCode: false, followRedirect: false })
                    .its('status')
                    .should('eq', 200);
            },
        }
    );

    cy.visit('/profilePage');

    // The credential exchange above is pure `cy.request`, so the `cy.visit` above
    // is the run's first real page load: the profilePage webpack chunk and the
    // Bootstrap bundle are still being fetched/evaluated, and until they are a
    // click on a `data-bs-toggle="modal"` trigger silently no-ops. Wait for the
    // chunk to publish its Alpine factories, then reload once — every asset is
    // warm in the browser cache on the second load, so the whole interaction
    // layer (Bootstrap data-api included) is wired by the time `cy.reload()`
    // resolves. This is what the old navigate-through-/login flow gave us for
    // free; making it explicit keeps the modal-opening specs deterministic.
    cy.window({ timeout: 20000 }).should('have.property', 'Alpine');
    cy.window({ timeout: 20000 }).should('have.property', 'profileFeed');
    cy.reload();
    cy.window({ timeout: 20000 }).should('have.property', 'bootstrap');
    cy.window({ timeout: 20000 }).should('have.property', 'profileFeed');
}
