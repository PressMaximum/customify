/**
 * Customify Pro <-> Dashboard V2 bridge (client side).
 *
 * Enqueued by inc/admin/pro-bridge/pro-bridge.php only when the active
 * Customify Pro build is older than 0.4.16 (the version that ships its own
 * V2 integration). Hooks into the dashboard's Pro extension filters:
 *
 *   customify.dashboard.pro.modules  — enrich the catalogue with classKey
 *                                      + enabled state from boot.proModules
 *   customify.dashboard.pro.toggle   — handler that POSTs to Pro's legacy
 *                                      `customify_pro_module` AJAX action
 *
 * Plus a tiny hashchange listener: when the user clicks the Settings link
 * for a Pro module, the dashboard navigates to `#settings/<id>` which has
 * no matching theme panel; we redirect to the bridge-hosted settings page
 * (`admin.php?page=customify-pro-module-settings&module=<class>`) so the
 * form fields render through the theme's Customify_Form_Fields pipeline.
 */

( function ( wp ) {
	if ( ! wp || ! wp.hooks ) {
		return;
	}
	var boot = window.customifyDashboard;
	if ( ! boot || ! Array.isArray( boot.proModules ) ) {
		return;
	}

	var NS = 'customify/pro-bridge';
	var proList = boot.proModules;
	var ajax = boot.proAjax || {};

	// Index by kebab id for O(1) lookups inside the filter callbacks. The
	// catalogue is small (~17 rows) but the filters fire on every render.
	var byId = {};
	proList.forEach( function ( mod ) {
		if ( mod && mod.id ) {
			byId[ mod.id ] = mod;
		}
	} );

	/**
	 * Merge a Pro payload row into a catalogue base row. Base provides the
	 * marketing copy + docs link (curated text); Pro overrides with the
	 * live class/enabled/canToggle state.
	 */
	function mergePro( base, p ) {
		return Object.assign( {}, base, {
			classKey: p.classKey,
			enabled: p.enabled,
			canToggle: p.canToggle,
			toggleDisableNotice: p.toggleDisableNotice,
			hasSettings: p.hasSettings,
			settingsHref: p.settingsHref,
			parent: p.parent || base.parent || null,
			subModules:
				p.subModules && p.subModules.length
					? p.subModules
					: base.subModules,
		} );
	}

	// Build a catalogue row from scratch when Pro registers a module the
	// theme's static catalogue doesn't know about (forward-compat).
	function rowFromPro( p ) {
		return {
			id: p.id,
			name: p.name,
			description: p.description,
			docHref: p.docHref,
			classKey: p.classKey,
			enabled: p.enabled,
			canToggle: p.canToggle,
			toggleDisableNotice: p.toggleDisableNotice,
			hasSettings: p.hasSettings,
			settingsHref: p.settingsHref,
			parent: p.parent || null,
			subModules: p.subModules || [],
		};
	}

	wp.hooks.addFilter(
		'customify.dashboard.pro.modules',
		NS,
		function ( base ) {
			if ( ! Array.isArray( base ) ) {
				return base;
			}
			var baseIds = {};
			var merged = base.map( function ( m ) {
				baseIds[ m.id ] = true;
				var p = byId[ m.id ];
				return p ? mergePro( m, p ) : m;
			} );
			// Append Pro-only modules (id not in base catalogue).
			proList.forEach( function ( p ) {
				if ( ! baseIds[ p.id ] ) {
					merged.push( rowFromPro( p ) );
				}
			} );
			return merged;
		}
	);

	// Toggle handler. Returns a Promise<{ enabled }> as required by
	// ProModulesSection's contract; reject path triggers the section's
	// optimistic-revert + error snackbar.
	wp.hooks.addFilter(
		'customify.dashboard.pro.toggle',
		NS,
		function () {
			return function ( id, nextEnabled ) {
				var mod = byId[ id ];
				if ( ! mod || ! mod.classKey || ! ajax.url ) {
					return Promise.reject(
						new Error( 'Unknown Pro module: ' + id )
					);
				}

				var body = new URLSearchParams();
				body.set( 'action', ajax.action );
				body.set( '_nonce', ajax.nonce );
				body.set( 'doing', 'toggle_module' );
				body.set( 'name', mod.classKey );
				body.set( 'enable', nextEnabled ? '1' : '0' );

				return fetch( ajax.url, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type':
							'application/x-www-form-urlencoded; charset=UTF-8',
					},
					body: body.toString(),
				} )
					.then( function ( res ) {
						if ( ! res.ok ) {
							throw new Error( 'HTTP ' + res.status );
						}
						return res.json();
					} )
					.then( function ( json ) {
						if ( ! json || ! json.success ) {
							throw new Error( 'Toggle rejected by server' );
						}
						mod.enabled = !! nextEnabled;
						if ( mod.reload ) {
							// Pro flags some modules as "reload required"
							// (e.g. modules that register Customizer panels
							// at boot). Defer slightly so the success
							// snackbar paints before the page tears down.
							setTimeout( function () {
								window.location.reload();
							}, 250 );
						}
						return { enabled: !! nextEnabled };
					} );
			};
		}
	);

	// Redirect #settings/<pro-module-id> to Pro's legacy module-settings
	// page. The hash fires before React renders the (empty) Settings panel
	// for the missing id, so the user lands on the legacy form without a
	// flash of "No settings registered yet".
	function maybeRedirectSettings() {
		var hash = window.location.hash || '';
		var m = hash.match( /^#settings\/([^/]+)$/ );
		if ( ! m ) {
			return;
		}
		var mod = byId[ m[ 1 ] ];
		if ( mod && mod.settingsHref ) {
			window.location.replace( mod.settingsHref );
		}
	}
	window.addEventListener( 'hashchange', maybeRedirectSettings );
	maybeRedirectSettings();
} )( window.wp );
