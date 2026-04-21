# AGENTS.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project snapshot
- This is a custom PHP 8.3+ web app (not Laravel framework) with:
  - Front controller: `index.php`
  - Routing: `altorouter/altorouter`
  - Views: Blade templates under `resources/views` (via `modernman00/shared-lib`)
  - Data access: custom PDO classes in `app/classes`
  - Frontend bundling: Laravel Mix (`webpack.mix.js`) compiling assets from `resources/asset` to `public/`
- `README.txt` is a template/theme credit file and does not describe repository workflows.

## Setup and common commands
### Dependencies
```bash
composer install
npm install
```

### Frontend build/watch
```bash
# one-off development build
npm run dev

# watch mode during frontend development
npm run watch

# production build (minified + versioned assets)
npm run prod
```

### Static analysis / linting
```bash
# PHPStan (configured by phpstan.neon)
./vendor/bin/phpstan analyze

# Psalm (configured by psalm.xml)
./vendor/bin/psalm
```

### “Single test” equivalent in this repository
- There is no working automated unit/integration test suite configured (no `phpunit.xml`; `bootstrap/testUnit/submitform.php` is only a stub snippet).
- For targeted verification on one area, run file-scoped static analysis instead:
```bash
./vendor/bin/phpstan analyze app/controller/members/ProfilePage.php
./vendor/bin/psalm --focus=app/controller/members/ProfilePage.php
```

## Runtime notes
- Environment variables are loaded in `app/config/_env.php` via `vlucas/phpdotenv`; ensure `.env` is present.
- Database connection is configured from env vars in `app/classes/Db.php` (`DB_HOST`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`).
- There is no dedicated app “start” script in `composer.json` or `package.json`; run under a local PHP web server stack that serves this repo with `index.php` as entrypoint.

## High-level architecture
### Request lifecycle (backend)
1. `index.php` loads `app/config/init.php`, then `app/router/router.php`, then dispatches routes via `App\router\RouteDispatch`.
2. `app/router/router.php` composes routes by including domain route files (`home.php`, `login.php`, `register.php`, `post.php`, `allMembersRoute.php`, etc.).
3. `RouteDispatch` resolves `Controller@method` strings and handles:
   - not found routes (`resources/views/errors/404.blade.php`)
   - unhandled exceptions (`resources/views/errors/500.blade.php`)
4. Controllers in `app/controller/**` orchestrate request validation, domain operations, and rendering/JSON responses.

### Domain/module boundaries
- `app/controller/login/*`: auth/login/password-reset/admin-login entrypoints.
- `app/controller/register/*`: registration flow, family code creation, submission pipeline.
- `app/controller/members/*`: member profile, posts, reactions, events, family tree (organogram), requests.
- `app/controller/admin/*`: application review and dashboard.
- Route files in `app/router/*` map HTTP endpoints to those modules.

### Data and persistence pattern
- `app/classes/Db.php` provides PDO connection helpers.
- Query/building helpers live in `app/classes` (`Select`, `Insert`, `Update`, `InnerJoin`, etc.).
- Models in `app/model/*` are thin wrappers around those query helpers (for example `Post`, `SingleCustomerData`, `AllMembersData`).
- Many operations rely on helper functions autoloaded from `app/function/helper/*` (declared in `composer.json`).

### Auth/session/cross-cutting behavior
- Session is initialized in `app/config/init.php` with secure cookie/session flags.
- `app/controller/BaseController.php` enforces sign-in verification (`Src\functionality\SignIn::verify`) and provides shared member-data assembly utilities.
- App-wide utility functions such as `view()`, `showError()`, `msgSuccess()`, etc. are provided by the shared library (`modernman00/shared-lib`), so trace into `vendor/` when behavior is unclear.

### Frontend architecture
- Main entry: `resources/asset/js/index.js`.
- The frontend uses path-based dynamic imports (route map keyed by `window.location.pathname`) to load only relevant feature modules (e.g. register, login, profilePage, familyTree).
- Shared browser helpers are in `resources/asset/js/components/global.js`.
- Webpack aliases in `webpack.mix.js` (`@`, `@components`, `@shared`, `@scss`, etc.) are heavily used in imports.
- Blade layouts under `resources/views/layouts/*` include compiled bundles from `public/js/{manifest,vendor,index}.js`.

### Practical guidance for code changes
- Backend endpoint changes usually require edits in three places:
  1) route map in `app/router/*.php`,
  2) controller method in `app/controller/**`,
  3) corresponding Blade/JS caller in `resources/views/**` or `resources/asset/js/**`.
- For member/profile features specifically, expect coordinated changes across:
  - `app/controller/members/ProfilePage.php`
  - `app/model/Post.php` (and related model helpers)
  - `resources/asset/js/components/profilePage/**`
  - `resources/views/member/**`
- After frontend edits, rebuild assets (`npm run dev` or `npm run prod`) so `public/` reflects source updates.
