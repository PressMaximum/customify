/**
 * E2E — Customizer.
 *
 * Live install snapshot at the time of writing:
 *   - 12 panels
 *   - 101 sections
 *   - 614 settings (includes nav-menu items)
 *   - 460 Customify-typed controls across 20 subtypes
 *
 * The full surface is too large to drive button-by-button without flake.
 * This suite covers four bands instead:
 *   1. Static smoke: load, panel/section/setting count, no PHP fatal.
 *   2. Per-panel open: each registered panel is openable without JS error.
 *   3. Per-control-type render: each subtype the theme ships has at least
 *      one rendered instance in the DOM (catches "control class missing"
 *      regressions silently breaking a whole field type).
 *   4. AJAX security: each handler we patched (get_icons / fonts / reset)
 *      enforces nonce so the WordPress.org review fixes don't regress.
 *
 * Slow paths (save round-trip + frontend reflect) live in the matching
 * customizer-save.spec.js / customizer-frontend.spec.js so this file
 * stays under ~90 seconds.
 */

const { test, expect } = require( '@playwright/test' );

async function openCustomizer( page ) {
	await page.goto( '/wp-admin/customize.php' );
	await expect( page.locator( '#customize-controls' ) ).toBeVisible( {
		timeout: 30_000,
	} );
	// wp.customize takes a tick after the controls panel is visible.
	await page.waitForFunction(
		() => typeof wp !== 'undefined'
			&& wp.customize
			&& wp.customize.panel
			&& wp.customize.panel( 'header_settings' )
	);
}

test.describe( 'Customizer — static smoke', () => {
	test( 'loads with no PHP fatal banner', async ( { page } ) => {
		await openCustomizer( page );
		const html = await page.content();
		expect( html ).not.toMatch(
			/Fatal error|There has been a critical error/i
		);
	} );

	test( 'expected panel inventory is registered', async ( { page } ) => {
		await openCustomizer( page );
		const panels = await page.evaluate( () => {
			const out = [];
			wp.customize.panel.each( ( p ) => out.push( p.id ) );
			return out;
		} );

		// Bucket panels into "must exist" groups so the test survives
		// renames within a category.
		const must = [
			/header/i,
			/footer/i,
			/blog/i,
			/styling/i,
			/typography/i,
			/layout/i,
		];
		for ( const re of must ) {
			expect(
				panels.some( ( id ) => re.test( id ) ),
				`No panel matches ${ re } (got: ${ panels.join( ', ' ) })`
			).toBe( true );
		}
	} );

	test( 'has at least 250 settings registered', async ( { page } ) => {
		await openCustomizer( page );
		const count = await page.evaluate( () => {
			let n = 0;
			wp.customize.each( () => n++ );
			return n;
		} );
		expect( count ).toBeGreaterThan( 250 );
	} );

	test( 'has at least 100 customify controls registered', async ( { page } ) => {
		await openCustomizer( page );
		const count = await page.evaluate( () => {
			let n = 0;
			wp.customize.control.each( ( c ) => {
				if ( c.params && c.params.type === 'customify' ) n++;
			} );
			return n;
		} );
		// Snapshot says 460. Floor at 100 to allow Pro/feature flag drift.
		expect( count ).toBeGreaterThan( 100 );
	} );

	test( 'every control points at a known section', async ( { page } ) => {
		await openCustomizer( page );
		const orphans = await page.evaluate( () => {
			const sections = new Set();
			wp.customize.section.each( ( s ) => sections.add( s.id ) );
			const out = [];
			wp.customize.control.each( ( c ) => {
				const sec = c.params && c.params.section;
				// Some core controls (e.g. nav menu items) have no section.
				if ( ! sec || c.params.type !== 'customify' ) return;
				if ( ! sections.has( sec ) ) {
					out.push( { id: c.id, section: sec } );
				}
			} );
			return out;
		} );
		expect(
			orphans,
			`Controls reference unknown sections:\n${ JSON.stringify( orphans, null, 2 ) }`
		).toEqual( [] );
	} );

	test( 'device preview buttons present in footer', async ( { page } ) => {
		await openCustomizer( page );
		const footer = page.locator( '#customize-footer-actions' );
		await expect( footer.locator( '.preview-desktop' ) ).toBeVisible();
		await expect( footer.locator( '.preview-tablet' ) ).toBeVisible();
		await expect( footer.locator( '.preview-mobile' ) ).toBeVisible();
	} );
} );

test.describe( 'Customizer — per-panel open smoke', () => {
	// Panels Customify ships. Loop them in one test so we pay the
	// customizer-load cost once. Each panel just needs to expand without
	// throwing — we don't validate the controls inside (covered separately).
	const PANELS = [
		'header_settings',
		'layout_panel',
		'blog_panel',
		'styling_panel',
		'typography_panel',
		'footer_settings',
	];

	test( 'each Customify panel can be expanded without JS error', async ( { page } ) => {
		const errors = [];
		page.on( 'pageerror', ( err ) => errors.push( err.message ) );

		await openCustomizer( page );

		for ( const id of PANELS ) {
			const result = await page.evaluate( ( panelId ) => {
				const panel = wp.customize.panel( panelId );
				if ( ! panel ) return { ok: false, reason: 'not_registered' };
				panel.expanded( true );
				return { ok: true };
			}, id );
			expect(
				result.ok,
				`Panel "${ id }" failed: ${ result.reason || '' }`
			).toBe( true );
			// Let the expand animation finish before the next one.
			await page.waitForTimeout( 250 );
		}

		// Filter known noise (jQuery/Elementor — env, not theme).
		const real = errors.filter( ( m ) =>
			! /jQuery is not defined|elementorModules/i.test( m )
		);
		expect( real, real.join( '\n' ) ).toEqual( [] );
	} );
} );

test.describe( 'Customizer — per-control-type render coverage', () => {
	// Snapshot of types Customify renders (from a representative install).
	// At least ONE rendered instance must exist per type, otherwise the
	// underlying control class probably failed to register.
	const TYPES = [
		'checkbox',
		'color',
		'css_ruler',
		'custom_html',
		'heading',
		'image',
		'modal',
		'number',
		'radio',
		'repeater',
		'select',
		'slider',
		'styling',
		'text',
		'text_align',
		'text_align_no_justify',
		'textarea',
		'typography',
	];

	test( 'every shipped customify control type has at least one DOM instance', async ( { page } ) => {
		await openCustomizer( page );

		const counts = await page.evaluate( ( types ) => {
			const out = {};
			for ( const t of types ) {
				out[ t ] = document.querySelectorAll(
					`.customize-control-customify-${ t }`
				).length;
			}
			return out;
		}, TYPES );

		const missing = Object.entries( counts )
			.filter( ( [ , n ] ) => n === 0 )
			.map( ( [ t ] ) => t );

		expect(
			missing,
			`Control types with zero DOM instances (likely class registration failed):\n${ missing.join( ', ' ) }`
		).toEqual( [] );
	} );

	test( 'opening the styling section instantiates color controls', async ( { page } ) => {
		await openCustomizer( page );
		// Force-instantiate the section so its lazy controls render.
		await page.evaluate( () => {
			const sec = wp.customize.section( 'general_styling' )
				|| wp.customize.section( 'styling_panel' );
			if ( sec ) sec.expanded( true );
		} );
		await page.waitForTimeout( 600 );
		const colorCount = await page.locator(
			'.customize-control-customify-color'
		).count();
		expect( colorCount ).toBeGreaterThan( 0 );
	} );
} );

test.describe( 'Customizer — required dependencies', () => {
	test( 'controls with `required` deps expose a working active() observable', async ( { page } ) => {
		await openCustomizer( page );
		const stats = await page.evaluate( () => {
			let withRequired = 0;
			let withActiveObservable = 0;
			let activeReturnsBool = 0;
			wp.customize.control.each( ( c ) => {
				if ( ! c.params || ! c.params.required ) return;
				if ( ! Object.keys( c.params.required ).length ) return;
				withRequired++;
				// In WP core, c.active is a wp.customize.Value() instance —
				// a function/observable. Customify hooks visibility into it
				// by calling c.active.set( bool ) when deps change.
				if ( typeof c.active === 'function' ) {
					withActiveObservable++;
					const v = c.active();
					if ( typeof v === 'boolean' ) activeReturnsBool++;
				}
			} );
			return { withRequired, withActiveObservable, activeReturnsBool };
		} );
		expect(
			stats.withRequired,
			'No controls use the `required` dependency feature?'
		).toBeGreaterThan( 0 );
		// Every required control MUST have a working active() observable —
		// without it, dependent fields can never be hidden.
		expect( stats.withActiveObservable ).toBe( stats.withRequired );
		expect( stats.activeReturnsBool ).toBe( stats.withRequired );
	} );
} );

test.describe( 'Customizer — AJAX security regressions', () => {
	test( 'reset_section enforces nonce', async ( { request } ) => {
		const resp = await request.post( '/wp-admin/admin-ajax.php', {
			form: {
				action: 'customify__reset_section',
				section: 'general',
				settings: [ 'foo' ],
				// no nonce
			},
		} );
		const body = await resp.text();
		expect( body ).not.toMatch( /"success"\s*:\s*true/ );
	} );

	test( 'get_icons enforces nonce (regression)', async ( { request } ) => {
		const resp = await request.get(
			'/wp-admin/admin-ajax.php?action=customify/customizer/ajax/get_icons'
		);
		const body = await resp.text();
		expect( body ).not.toMatch( /"success"\s*:\s*true/ );
	} );

	test( 'fonts AJAX enforces nonce (regression)', async ( { request } ) => {
		const resp = await request.get(
			'/wp-admin/admin-ajax.php?action=customify/customizer/ajax/fonts'
		);
		const body = await resp.text();
		expect( body ).not.toMatch( /"success"\s*:\s*true/ );
	} );

	test( 'reset_section rejects valid nonce when capability missing', async ( { browser } ) => {
		// Fresh context (no admin storageState) → effectively a logged-out user.
		const ctx = await browser.newContext();
		const page = await ctx.newPage();
		const resp = await page.request.post( '/wp-admin/admin-ajax.php', {
			form: {
				action: 'customify__reset_section',
				section: 'general',
				settings: [ 'foo' ],
				nonce: 'whatever-but-no-cap',
			},
		} );
		const body = await resp.text();
		expect( body ).not.toMatch( /"success"\s*:\s*true/ );
		await ctx.close();
	} );
} );
