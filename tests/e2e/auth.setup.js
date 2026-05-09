/**
 * Logs into wp-admin with credentials from .env and persists the storageState
 * to tests/e2e/.auth/admin.json. Every other test depends on this project so
 * the login cookie is reused — no per-test login overhead.
 *
 * Required .env vars:
 *   WP_BASE_URL, WP_ADMIN_USER, WP_ADMIN_PASS
 */

const { test, expect } = require( '@playwright/test' );
const path = require( 'path' );

const STORAGE_PATH = path.join( __dirname, '.auth', 'admin.json' );

test( 'authenticate as admin', async ( { page } ) => {
	const user = process.env.WP_ADMIN_USER;
	const pass = process.env.WP_ADMIN_PASS;
	if ( ! user || ! pass ) {
		throw new Error(
			'Set WP_ADMIN_USER + WP_ADMIN_PASS in .env (copy from .env.example).'
		);
	}

	await page.goto( '/wp-login.php' );
	await page.fill( '#user_login', user );
	await page.fill( '#user_pass', pass );
	await page.click( '#wp-submit' );

	// Confirm we landed on the dashboard (or any /wp-admin/ page).
	await expect( page ).toHaveURL( /\/wp-admin\// );
	await expect(
		page.locator( '#wpadminbar, #adminmenu' ).first()
	).toBeVisible( { timeout: 10_000 } );

	await page.context().storageState( { path: STORAGE_PATH } );
} );
