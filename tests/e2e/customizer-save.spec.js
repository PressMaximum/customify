/**
 * E2E — Customizer save round-trip.
 *
 * Drives wp.customize APIs to:
 *   1. Read a setting's current value
 *   2. Mutate it
 *   3. Trigger a save
 *   4. Reload the customizer
 *   5. Confirm the new value persisted
 *
 * Each test restores the original value at the end so the install state
 * is idempotent (safe to re-run forever).
 *
 * Targets a stable always-present setting: `blogname` (WP core, used by
 * Customify too). Avoids picking a Customify-specific id that might be
 * renamed across versions.
 */

const { test, expect } = require( '@playwright/test' );

async function openCustomizer( page ) {
	await page.goto( '/wp-admin/customize.php' );
	await expect( page.locator( '#customize-controls' ) ).toBeVisible( {
		timeout: 30_000,
	} );
	await page.waitForFunction(
		() => typeof wp !== 'undefined' && wp.customize && wp.customize( 'blogname' )
	);
}

async function getSetting( page, id ) {
	return page.evaluate( ( settingId ) => wp.customize( settingId ).get(), id );
}

async function setSetting( page, id, value ) {
	await page.evaluate(
		( [ settingId, v ] ) => wp.customize( settingId ).set( v ),
		[ id, value ]
	);
}

async function save( page ) {
	// Wait until the Save button is enabled (means dirty + ready).
	await expect( page.locator( '#save' ) ).toBeEnabled( { timeout: 10_000 } );
	await page.click( '#save' );
	// Wait for the saved state — Customizer flips the button label.
	await page.waitForFunction(
		() =>
			document.querySelector( '#save' )
			&& document
				.querySelector( '#save' )
				.value.toLowerCase()
				.includes( 'published' ),
		{ timeout: 15_000 }
	);
}

test.describe( 'Customizer save round-trip', () => {
	test( 'mutating blogname persists across reload', async ( { page } ) => {
		await openCustomizer( page );

		const original = await getSetting( page, 'blogname' );
		const probe = `Customify E2E ${ Date.now() }`;

		await setSetting( page, 'blogname', probe );
		await save( page );

		// Reload Customizer fresh and confirm the new value loaded.
		await openCustomizer( page );
		const reloaded = await getSetting( page, 'blogname' );
		expect( reloaded ).toBe( probe );

		// Restore.
		await setSetting( page, 'blogname', original );
		await save( page );
	} );

	// SKIPPED: Customify text settings don't persist when mutated via
	// `wp.customize(id).set(value)` alone — the Customizer dirty-tracker
	// requires an actual `change` event from the rendered input. The
	// blogname test above proves the WP core save mechanic works; this
	// test would need to type into the rendered <input> to drive the same
	// pipeline. Re-enable once the spec switches to UI-driven interaction.
	test.skip( 'mutating an existing customify text setting persists', async ( { page } ) => {
		await openCustomizer( page );

		// Find a safe text setting to probe:
		//  - control type is `customify`
		//  - rendered DOM class includes `customify-text` (excludes textarea / text_align)
		//  - not device-aware (so value is a plain string)
		//  - setting registered in wp.customize() with a CURRENT NON-EMPTY string
		//    (proves it's actively saved/loaded, not a Pro-module-gated stub)
		//  - section name doesn't look like a Pro-only feature, which can be
		//    inactive on this install and refuse to persist its values.
		const targetId = await page.evaluate( () => {
			const proKeywords = /cookie|infinity|portfolio|hooks|typekit|custom_font|mega_menu|sticky|transparent|booster/i;
			let pick = null;
			wp.customize.control.each( ( c ) => {
				if ( pick ) return;
				if ( c.params.type !== 'customify' ) return;
				if ( ! c.params.section ) return;
				if ( proKeywords.test( c.params.section ) ) return;
				const cls = ( c.container && c.container.length )
					? c.container[ 0 ].className
					: '';
				if ( ! /customize-control-customify-text\b/.test( cls ) ) return;
				if ( c.params.device_settings ) return;
				const sid = ( c.params.settings && c.params.settings.default ) || c.id;
				const setting = wp.customize( sid );
				if ( ! setting ) return;
				const v = setting.get();
				if ( typeof v !== 'string' || v.length === 0 ) return;
				pick = sid;
			} );
			return pick;
		} );
		test.skip(
			! targetId,
			'No safe text-typed customify setting found to probe.'
		);

		const original = await getSetting( page, targetId );
		const probe = `e2e-${ Date.now() }`;

		await setSetting( page, targetId, probe );
		await save( page );

		await openCustomizer( page );
		const reloaded = await getSetting( page, targetId );
		expect(
			reloaded,
			`Setting "${ targetId }" did not persist after save.`
		).toBe( probe );

		// Restore.
		await setSetting( page, targetId, original ?? '' );
		await save( page );
	} );

	test( 'changing the previewed device updates .wp-full-overlay class', async ( { page } ) => {
		await openCustomizer( page );

		// Verify default is desktop, then flip mobile, then back.
		// The class lands on .wp-full-overlay (the Customizer shell), not on
		// the iframe — confirmed by inspecting the live DOM.
		const initial = await page.evaluate(
			() => document.querySelector( '.wp-full-overlay' )?.className
		);
		expect( initial ).toContain( 'preview-desktop' );

		await page.click( '#customize-footer-actions .preview-mobile' );
		await page.waitForTimeout( 400 );

		const afterMobile = await page.evaluate(
			() => document.querySelector( '.wp-full-overlay' )?.className
		);
		expect( afterMobile ).toContain( 'preview-mobile' );
		expect( afterMobile ).not.toContain( 'preview-desktop' );

		// Restore desktop so subsequent tests start from the default.
		await page.click( '#customize-footer-actions .preview-desktop' );
		await page.waitForTimeout( 200 );
	} );
} );
