/**
 * E2E — frontend smoke tests (run logged-out).
 *
 * Catches catastrophic frontend regressions: missing template files, PHP
 * fatals on the rendered page, theme.js JS errors, broken CSS handle
 * (everything unstyled).
 *
 * Adds explicit guard: WC modules must NOT crash the frontend even when
 * WooCommerce is not active (was the recent off-canvas-filter fatal).
 */

const { test, expect } = require( '@playwright/test' );

function trackPageErrors( page ) {
	const errors = [];
	page.on( 'pageerror', ( err ) => errors.push( `pageerror: ${ err.message }` ) );
	page.on( 'console', ( msg ) => {
		if ( msg.type() !== 'error' ) return;
		const txt = msg.text();
		// Network / environment noise — not theme bugs.
		if ( txt.includes( 'favicon' ) ) return;
		if ( txt.includes( 'net::ERR_' ) ) return;
		if ( txt.includes( 'Failed to load resource' ) ) return;
		errors.push( `console: ${ txt }` );
	} );
	return errors;
}

async function assertNoFatal( page, url ) {
	const html = await page.content();
	expect(
		html,
		`PHP fatal banner on ${ url }`
	).not.toMatch( /Fatal error|There has been a critical error/i );
	expect(
		html,
		`PHP warning/notice leaking to output on ${ url }`
	).not.toMatch( /<b>Warning<\/b>|<b>Notice<\/b>|<b>Fatal error<\/b>/ );
}

test.describe( 'Frontend smoke', () => {
	test( 'homepage renders without PHP fatal or JS error', async ( { page } ) => {
		const errors = trackPageErrors( page );
		await page.goto( '/' );
		await assertNoFatal( page, '/' );

		// Body should have body_class output (proves header.php rendered).
		const bodyClass = await page.locator( 'body' ).getAttribute( 'class' );
		expect( bodyClass ).toBeTruthy();
		expect( bodyClass ).toMatch( /\bcustomify\b|\bhome\b|\bblog\b/i );

		// Footer must be present (proves wp_footer fired).
		await expect( page.locator( '#colophon, footer' ).first() ).toBeAttached();

		expect(
			errors,
			`Console errors:\n${ errors.join( '\n' ) }`
		).toEqual( [] );
	} );

	test( 'compiled stylesheet loaded (style-theme.css present)', async ( { page } ) => {
		await page.goto( '/' );
		const hrefs = await page.$$eval( 'link[rel="stylesheet"]', ( links ) =>
			links.map( ( l ) => l.href )
		);
		const haveTheme = hrefs.some( ( h ) =>
			/style-theme(\.min)?\.css|customify-style/.test( h )
		);
		expect( haveTheme, `stylesheet loaded — got: ${ hrefs.join( '\n' ) }` ).toBe( true );
	} );

	test( '404 page renders with theme template', async ( { page } ) => {
		const resp = await page.goto( '/this-path-does-not-exist-' + Date.now() );
		expect( resp?.status() ).toBe( 404 );
		await assertNoFatal( page, page.url() );
		// Theme's 404.php should output something searchable.
		const text = await page.locator( 'body' ).innerText();
		expect( text.toLowerCase() ).toMatch( /not found|404|search/ );
	} );

	test( 'homepage Customizer auto-CSS injected', async ( { page } ) => {
		await page.goto( '/' );
		// Customify_Customizer_Auto_CSS::render renders inline <style> via
		// wp_head. Catch silent regression where the output goes missing.
		const inlineStyles = await page.$$eval(
			'style',
			( nodes ) => nodes.map( ( n ) => n.textContent || '' )
		);
		const hasRoot = inlineStyles.some( ( s ) =>
			/--wp--style--global--wide-size|customify|container/i.test( s )
		);
		expect(
			hasRoot,
			`No inline auto-CSS detected on /. Saw ${ inlineStyles.length } <style> tags.`
		).toBe( true );
	} );

	test( 'WC module pre-existing fatal does NOT crash frontend (regression)', async ( { page, request } ) => {
		// Direct probe: the off-canvas-filter module's assets() callback would
		// call wc_get_page_id() if instantiated without WooCommerce loaded.
		// guard_pro_module_dependencies should prevent that. If a fatal sneaks
		// in, the response would be 500 or have the WP fatal banner.
		const resp = await request.get( '/' );
		expect( resp.status(), 'homepage HTTP status should be 200' ).toBe( 200 );
		const body = await resp.text();
		expect( body ).not.toMatch( /Fatal error|wc_get_page_id/i );
	} );
} );
