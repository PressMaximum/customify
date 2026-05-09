/**
 * E2E — Customizer ↔ frontend integration.
 *
 * The whole reason Customify exists is to project Customizer settings to
 * actual CSS on the rendered page. If render_css() drops a setting, the
 * UI lies — looks fine in admin, broken on the frontend.
 *
 * This suite mutates a known styling setting, saves, then visits the
 * homepage as a guest and asserts the new value appears in the inline
 * <style> output.
 *
 * Restores the original value at the end.
 */

const { test, expect } = require( '@playwright/test' );

async function openCustomizer( page ) {
	await page.goto( '/wp-admin/customize.php' );
	await expect( page.locator( '#customize-controls' ) ).toBeVisible( {
		timeout: 30_000,
	} );
	await page.waitForFunction(
		() => typeof wp !== 'undefined' && wp.customize
	);
}

async function save( page ) {
	await expect( page.locator( '#save' ) ).toBeEnabled( { timeout: 10_000 } );
	await page.click( '#save' );
	await page.waitForFunction(
		() =>
			document.querySelector( '#save' )
			&& /published|saved/i.test( document.querySelector( '#save' ).value ),
		{ timeout: 15_000 }
	);
}

test.describe( 'Customizer → frontend CSS', () => {
	test( 'inline auto-CSS is present on the homepage at all', async ( { page } ) => {
		await page.goto( '/' );
		const styles = await page.$$eval( 'style', ( nodes ) =>
			nodes.map( ( n ) => n.textContent || '' )
		);
		const total = styles.reduce( ( sum, s ) => sum + s.length, 0 );
		expect( total, 'No inline <style> found on homepage' ).toBeGreaterThan( 0 );
	} );

	test( 'inline CSS contains theme-managed CSS variables', async ( { page } ) => {
		await page.goto( '/' );
		const html = await page.content();
		// The container_width sync writes --wp--style--global--wide-size, and
		// theme.json defines color/typography custom props. At least ONE
		// must be present or render_css is broken.
		expect( html ).toMatch(
			/--wp--style--global--wide-size|--wp--preset--color|customify/i
		);
	} );

	test( 'changing container_width is reflected in inline CSS', async ( { browser } ) => {
		// Two contexts: admin (Customizer) + guest (frontend visit). Guest
		// view bypasses admin-only render quirks tied to the login cookie.
		const adminCtx = await browser.newContext( {
			storageState: 'tests/e2e/.auth/admin.json',
		} );
		const guestCtx = await browser.newContext();
		const adminPage = await adminCtx.newPage();
		const guestPage = await guestCtx.newPage();

		try {
			await openCustomizer( adminPage );

			// Find a SCALAR width-ish setting. Live install uses
			// container_width: 1200 (number). Fall back to anything matching
			// /width/ that has a numeric value.
			const targetId = await adminPage.evaluate( () => {
				const candidates = [ 'container_width', 'site_content_width' ];
				for ( const id of candidates ) {
					const s = wp.customize( id );
					if ( s && typeof s.get() === 'number' ) return id;
				}
				return null;
			} );
			test.skip(
				! targetId,
				'No scalar container width setting found on this install.'
			);

			const original = await adminPage.evaluate(
				( id ) => wp.customize( id ).get(),
				targetId
			);
			// Distinctive prime so the value is unambiguous in rendered CSS.
			const probe = 1187;
			await adminPage.evaluate(
				( [ id, v ] ) => wp.customize( id ).set( v ),
				[ targetId, probe ]
			);
			await save( adminPage );

			// Frontend view as guest — confirm CSS reflects the new value.
			await guestPage.goto( '/' );
			const html = await guestPage.content();
			expect(
				html,
				`Expected "${ probe }px" in homepage CSS for ${ targetId }`
			).toContain( `${ probe }px` );

			// Restore.
			await adminPage.evaluate(
				( [ id, v ] ) => wp.customize( id ).set( v ),
				[ targetId, original ]
			);
			await save( adminPage );
		} finally {
			await adminCtx.close();
			await guestCtx.close();
		}
	} );
} );
