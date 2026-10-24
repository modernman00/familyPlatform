const { defineConfig } = require('cypress');
const path = require('path');
const mysql = require('mysql2/promise');
const crypto = require('crypto');
const { execFileSync } = require('child_process');

require('dotenv').config({ path: path.join(__dirname, '.env') });


const connectDb = () =>
  mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
  });

// Argon2id hash in the exact format the app's password_verify() accepts.
let e2ePasswordHash = null;
const hashE2EPassword = (password) => {
  e2ePasswordHash ??= execFileSync('php', ['-r', 'echo password_hash($argv[1], PASSWORD_ARGON2ID);', password], { encoding: 'utf8' });
  return e2ePasswordHash;
};

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
        // --- RFC-0004 birth-family E2E fixtures: fresh users in, everything they touched out. ---
        async seedE2EUsers(specs) {
          const connection = await connectDb();
          const password = 'E2ePass123!';
          const hash = hashE2EPassword(password);
          const users = [];
          try {
            for (const spec of specs) {
              const suffix = crypto.randomBytes(4).toString('hex');
              const id = `e2e-bf-${suffix}`;
              const email = `${id}@e2e.myfamilyplatform.test`;
              const famCode = `E2E${suffix.toUpperCase().slice(0, 5)}`;
              await connection.execute('INSERT INTO account (id, email, password, status, deleted_at) VALUES (?, ?, ?, "active", NULL)', [id, email, hash]);
              await connection.execute('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)', [id, spec.firstName, spec.lastName, famCode]);
              await connection.execute(
                'INSERT INTO contact (id, email, country, mobile, two_factor_auth) VALUES (?, ?, "UK", ?, "on")',
                [id, email, `0770${Math.floor(1000000 + Math.random() * 8999999)}`]
              );
              users.push({ id, email, password, famCode, firstName: spec.firstName, lastName: spec.lastName });
            }
            return users;
          } finally {
            await connection.end();
          }
        },
        async getBirthState(userId) {
          const connection = await connectDb();
          try {
            const [[personal]] = await connection.execute('SELECT famCode FROM personal WHERE id = ?', [userId]);
            const [other] = await connection.execute('SELECT otherFamCode FROM otherFamily WHERE id = ?', [userId]);
            const [families] = await connection.execute('SELECT family_code, role, status FROM user_families WHERE user_id = ?', [userId]);
            return { famCode: personal?.famCode ?? null, otherFamCode: other[0]?.otherFamCode ?? null, families };
          } finally {
            await connection.end();
          }
        },
        async getLatestApprovalRequest(userId) {
          const connection = await connectDb();
          try {
            const [rows] = await connection.execute('SELECT no, purpose, status, family_code FROM family_approval_requests WHERE id = ? ORDER BY no DESC LIMIT 1', [userId]);
            return rows[0] ?? null;
          } finally {
            await connection.end();
          }
        },
        async cleanupE2EUsers(users) {
          if (!Array.isArray(users) || users.length === 0) return null;
          const ids = users.map((u) => u.id);
          const emails = users.map((u) => u.email);
          const connection = await connectDb();
          try {
            const [created] = await connection.query('SELECT code FROM family_codes WHERE created_by IN (?)', [ids]);
            const codes = [...users.map((u) => u.famCode), ...created.map((r) => r.code)];
            const run = (sql, params) => connection.query(sql, params).catch(() => null);
            await run('DELETE FROM family_nodes WHERE user_id IN (?) OR family_code IN (?)', [ids, codes]);
            await run('DELETE FROM invite_tokens WHERE family_code IN (?)', [codes]);
            await run('DELETE FROM user_families WHERE user_id IN (?) OR family_code IN (?)', [ids, codes]);
            await run('DELETE FROM family_codes WHERE created_by IN (?) OR code IN (?)', [ids, codes]);
            await run('DELETE FROM family_approval_requests WHERE id IN (?)', [ids]);
            await run('DELETE FROM notification WHERE sender_id IN (?) OR receiver_id IN (?)', [ids, ids]);
            await run('DELETE FROM code_mgt WHERE email IN (?)', [emails]);
            await run('DELETE FROM otherFamily WHERE id IN (?)', [ids]);
            await run('DELETE FROM contact WHERE id IN (?)', [ids]);
            await run('DELETE FROM personal WHERE id IN (?)', [ids]);
            await run('DELETE FROM account WHERE id IN (?)', [ids]);
            return null;
          } finally {
            await connection.end();
          }
        },
        log(message) {
          console.log('[CYPRESS LOG]', message);
          return null;
        },
      });
    },
    baseUrl: 'https://olaogun.test', // Virtual host
    chromeWebSecurity: false,
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: false,
    video: false,
    screenshotOnRunFailure: true,
    numTestsKeptInMemory: 1,
  },
});
