/**
 * Playwright config for Customify theme E2E tests.
 *
 * Reads target URL + admin credentials from .env. Run against any local
 * WP install where the theme is active. Defaults to http://customify.wp.local
 * if WP_BASE_URL is unset.
 *
 * Auth flow:
 *   - tests/e2e/auth.setup.js logs in once and saves storageState to
 *     tests/e2e/.auth/admin.json.
 *   - All subsequent tests reuse that state, no per-test login needed.
 *
 * To run only on chromium (CI default):
 *   npm run test:e2e
 *
 * To debug interactively:
 *   npm run test:e2e -- --headed --debug
 */

require( 'dotenv' ).config();
const { defineConfig, devices } = require( '@playwright/test' );

const BASE_URL = process.env.WP_BASE_URL || 'http://customify.wp.local';

module.exports = defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: false,        // WP admin is single-state; serial = stable
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	workers: 1,                  // Avoid race conditions on shared admin
	reporter: process.env.CI ? 'github' : [ [ 'list' ], [ 'html', { open: 'never' } ] ],

	use: {
		baseURL: BASE_URL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		ignoreHTTPSErrors: true,
		actionTimeout: 10_000,
		navigationTimeout: 20_000,
	},

	projects: [
		// Login once, persist storageState — every other test depends on this.
		{
			name: 'setup',
			testMatch: /auth\.setup\.js/,
		},
		{
			name: 'admin',
			testMatch: /.*\.spec\.js/,
			// Logged-OUT specs use the literal `frontend.spec.js` filename.
			// Anything else (including customizer-frontend / customizer-css-reflect)
			// stays in the admin project so it inherits storageState.
			testIgnore: /(^|\/)frontend\.spec\.js$/,
			dependencies: [ 'setup' ],
			use: {
				...devices[ 'Desktop Chrome' ],
				storageState: 'tests/e2e/.auth/admin.json',
			},
		},
		{
			name: 'frontend',
			testMatch: /(^|\/)frontend\.spec\.js$/,
			use: {
				...devices[ 'Desktop Chrome' ],
				// Frontend tests run logged-out — no storageState.
			},
		},
	],

	outputDir: 'tests/e2e/.results',
} );
