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

// The browser's asset cache persists across tests within a spec file, so only the
// first login of a run pays the cold-load cost that needs the priming reload.
let assetCacheWarmed = false;

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

    // The credential exchange above is pure `cy.request`, so the first `cy.visit`
    // is the run's cold page load — the profilePage webpack chunk, the Bootstrap
    // bundle and Alpine are all still being fetched and evaluated. Prime the
    // browser cache once, then reload so every asset comes back synchronously and
    // the interaction layer (Bootstrap's `data-bs-toggle` data-api included) is
    // wired. Then wait for the Alpine feed to finish its initial fetch: until
    // `isLoading` flips false the feed subtree is still re-rendering, and a modal
    // opened (or a field typed into) mid-render gets torn straight back down —
    // the churn behind the "#<modal> not visible" / "page updated while this
    // command was executing" flake.
    const waitForMemberAreaReady = () => {
        cy.window({ timeout: 20000 }).should('have.property', 'bootstrap');
        cy.window({ timeout: 20000 }).should('have.property', 'Alpine');
        cy.window({ timeout: 20000 }).should('have.property', 'profileFeed');
        cy.window().then((win) => {
            cy.get('.feed-posts-container', { timeout: 20000 }).should(($el) => {
                const data = win.Alpine && win.Alpine.$data($el[0]);
                expect(data, 'Alpine feed data bound').to.be.an('object');
                expect(data.isLoading, 'Alpine feed finished its initial fetch').to.eq(false);
            });
        });
    };

    cy.visit('/profilePage');
    if (!assetCacheWarmed) {
        // First page load of the run: assets are downloading for the first time and
        // the interaction layer wires up unevenly. A reload with everything now in
        // cache lands it deterministically.
        cy.reload();
        assetCacheWarmed = true;
    }
    waitForMemberAreaReady();
}
