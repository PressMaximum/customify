# Customify Theme — Test Suite

Three layers, runnable independently:

| Layer | Tool | Speed | Coverage focus |
|---|---|---|---|
| PHP unit | PHPUnit + Brain Monkey | <1s | Sanitizers, auto-CSS renderer, Pro module helpers, schema validation |
| JS unit | Jest + @testing-library | ~1s | React dashboard components, AJAX client |
| E2E | Playwright | ~4m | Live admin + customizer + frontend smoke against a real WP install |
| Lint (PHP) | PHPCS + WPCS | ~5s | wp.org coding standards |

Plus a Customizer fixture dump tool that captures all 329 settings for the schema test layer.

---

## One-time setup

```bash
# PHP test deps
php composer install

# JS test deps (already installed if you ran npm install)
npm install
```

---

## Running tests

```bash
# PHP unit tests (Brain Monkey, no WP needed) — fast iteration loop
npm run test:php
# or directly
./vendor/bin/phpunit --testsuite unit

# JS unit tests (Jest + JSDOM)
npm test
# watch mode
npm run test:watch
# coverage report
npm run test:coverage

# Both PHP + JS unit
npm run test:all

# E2E (Playwright + real local WP)
cp .env.example .env          # then fill in WP_BASE_URL + WP_ADMIN_USER/PASS
npm run test:e2e              # headless
npm run test:e2e:headed       # see the browser
npm run test:e2e:ui           # Playwright Test Runner UI
npm run test:e2e:debug        # step through with Inspector
npm run test:e2e:report       # open last HTML report

# PHPCS / WPCS (coding standards)
php ./composer lint:php
# Auto-fix what's safe
php ./composer lint:php:fix
```

---

## Test layout

```
tests/php/
├── bootstrap.php                       # PHPUnit bootstrap (Brain Monkey + ABSPATH stub)
├── src/
│   ├── UnitTestCase.php                # Base test class (Brain Monkey + WP function stubs)
│   └── CustomifyHelpersTestCase.php    # For tests that exercise private Customify methods
├── schema/
│   └── CustomizerSchemaTest.php        # Validates ALL Customizer fields against fixture
├── unit/
│   ├── SmokeTest.php                   # Sanity check
│   ├── ParseProAdminNoticesTest.php
│   ├── SanitizeProModuleValuesTest.php
│   ├── WCDependencyGuardTest.php
│   └── customizer/
│       ├── sanitize/                   # Sanitize_Input::sanitize_*
│       └── auto_css/                   # Customizer_Auto_CSS::setup_*
├── integration/                        # WP_UnitTestCase tests (requires WP_TESTS_DIR)
└── fixtures/
    ├── all-configs.sample.php          # Tiny hand-crafted sample for CI without WP
    └── all-configs.php                 # Generated; checked in for reproducibility

tests/js/
├── setup.js                            # window.customifyDashboard mock
└── __mocks__/
    └── style-mock.js                   # CSS import stub

src/backend/dashboard/                  # Component tests live next to source
├── app/api/pro-modules.test.js
└── app/components/ModuleSettingsModal.test.js

tests/e2e/
├── auth.setup.js                       # One-time admin login → storageState
├── dashboard.spec.js                   # Customify dashboard smoke
├── pro-modules.spec.js                 # Pro modules card + WC guard
├── customizer.spec.js                  # Customizer load + AJAX nonce checks
└── frontend.spec.js                    # Homepage / 404 / auto-CSS / no fatal
playwright.config.js                    # Project setup (admin / frontend split)
.env.example                            # Template for local WP credentials
```

---

## Refreshing the Customizer fixture

The schema test (`tests/php/schema/CustomizerSchemaTest.php`) validates every
field the theme registers. To run against the **full** 329-field set instead
of the smoke sample, regenerate the fixture from a live WP install with the
theme active:

```bash
# Run inside your local WP install dir (with wp-cli installed)
wp eval-file wp-content/themes/customify/tools/dump-customizer-configs.php
```

This writes `tests/php/fixtures/all-configs.php` (committed). Re-run any time
you add/modify a config under `inc/customizer/configs/`. The schema test will
fail if a new field type isn't added to its `KNOWN_TYPES` allowlist, which is
intentional — adding a type means the sanitizer + auto-CSS renderer also need
to handle it.

---

## Adding new tests

### A new PHP unit test

1. Drop a `*Test.php` file under `tests/php/unit/` (or a sub-folder).
2. Extend `Customify\Tests\UnitTestCase` (gets Brain Monkey + WP fn stubs).
3. For tests that need access to private Customify methods, extend
   `Customify\Tests\CustomifyHelpersTestCase` and use `$this->invoke('method', …)`.

### A new JS test

1. Drop `*.test.js` next to the source it covers (preferred) or under `tests/js/`.
2. If the component imports from `../../ui`, mock the barrel via
   `jest.mock('../../ui', () => …)` to avoid pulling `@wordpress/components`
   into the JSDOM environment.
3. Mock `@wordpress/notices` virtually:
   `jest.mock('@wordpress/notices', () => ({ store: 'notices-store' }), { virtual: true })`.

### A new E2E spec

1. Drop `*.spec.js` under `tests/e2e/`.
2. Default project is `admin` (logged-in via storageState). Name the file
   `frontend-*.spec.js` to opt into the logged-out `frontend` project.
3. For DOM elements that are visually hidden (e.g. `.pm-toggle` checkbox at
   `opacity:0`), click the parent `<label>` instead — Playwright's strict
   visibility checks reject `opacity:0` inputs.
4. Wait for `window.customifyDashboard` to exist before reading bootstrap
   data (`page.waitForFunction(() => typeof window.customifyDashboard === 'object')`).

### A new sanitizer / auto-CSS handler

When you add a new Customizer field type:

1. Add the type to `tests/php/schema/CustomizerSchemaTest.php::KNOWN_TYPES`.
2. Write a sanitizer test under `tests/php/unit/customizer/sanitize/`.
3. Write an auto-CSS renderer test under `tests/php/unit/customizer/auto_css/`.
4. Re-dump the fixture if you also added the field to a config file.

---

## CI suggestion (GitHub Actions)

```yaml
name: Test
on: [push, pull_request]
jobs:
  php:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with: { php-version: '7.4' }
      - run: composer install --no-progress
      - run: composer lint:php
      - run: composer test
  js:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '18' }
      - run: npm ci
      - run: npm run lint:js
      - run: npm test -- --ci
  e2e:
    runs-on: ubuntu-latest
    services:
      wordpress:
        image: wordpress
        ports: [ '8080:80' ]
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '18' }
      - run: npm ci
      - run: npx playwright install --with-deps chromium
      # Provision your WP test instance here, then:
      - run: |
          echo "WP_BASE_URL=http://localhost:8080"   >> .env
          echo "WP_ADMIN_USER=admin"                 >> .env
          echo "WP_ADMIN_PASS=$WP_ADMIN_PASS"        >> .env
        env:
          WP_ADMIN_PASS: ${{ secrets.WP_ADMIN_PASS }}
      - run: npm run test:e2e
      - uses: actions/upload-artifact@v4
        if: always()
        with:
          name: playwright-report
          path: tests/e2e/playwright-report
          retention-days: 14
```

---

## Known issues surfaced by the E2E suite (not infra bugs)

The dashboard E2E filters these noise patterns; they are real but tracked
separately from the test infrastructure:

- **`Cannot read properties of undefined (reading 'sprintf')`** — wp-i18n is
  declared as a dependency of the dashboard bundle (see `index.asset.php`)
  but `window.wp.i18n` may be undefined at module-eval time on some setups.
  When this fires, React fails to render and the dashboard appears empty.
  Tests skip the toggle check via `test.skip()` when they detect an empty
  React tree.
- **`elementorModules is not defined` / `jQuery is not defined`** — third-
  party / plugin load order issues on the test environment, not theme code.

Drop the matching pattern from `NOISE_PATTERNS` in `tests/e2e/dashboard.spec.js`
once the underlying issue is fixed, so the test re-arms.
