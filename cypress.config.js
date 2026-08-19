const { defineConfig } = require('cypress');
const path = require('path');
const mysql = require('mysql2/promise');

require('dotenv').config({ path: path.join(__dirname, '.env') });

module.exports = defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      on('task', {
        // Reads the most recently issued 2FA code for an email directly from the
        // local dev DB, so e2e tests can complete the mandatory-2FA login flow
        // without needing access to the test account's real inbox.
        async getLatest2FACode(email) {
          const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USERNAME,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
          });

          try {
            const [rows] = await connection.execute(
              `SELECT code FROM ${process.env.DB_TABLE_CODE_MGT} WHERE email = ? ORDER BY no DESC LIMIT 1`,
              [email]
            );
            return rows[0]?.code ?? null;
          } finally {
            await connection.end();
          }
        },
      });
    },
    baseUrl: 'http://olaogun.test', // Virtual host
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: false,
    video: false,
    screenshotOnRunFailure: true,
  },
});
