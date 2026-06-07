/**
 * Starter Templates tab.
 *
 * Two modes, picked from the boot payload:
 *
 *   - `boot.importer.active` set by the FameThemes Demo Importer plugin's
 *     Customify adapter (hooks `customify_dashboard_localize`) →
 *     embed the plugin's React app into this tab. The plugin enqueues
 *     its bundle on `toplevel_page_customify`, exposes
 *     `window.ftDemoImporter.mount(el)` / `unmount(el)`, and skips its
 *     own auto-mount (because `embedded: true`).
 *
 *   - Otherwise → render the CTA that one-click installs + activates the
 *     FameThemes Demo Importer plugin via WP's /wp/v2/plugins REST
 *     endpoint, then reloads so the tab flips into the embedded mode
 *     above.
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Button, Notice, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useBoot } from '@pressmaximum/dashboard-kit';

// REST identifier WP uses for a plugin = `{folder}/{file-without-ext}`.
// FameThemes Demo Importer's main file matches its slug.
const PLUGIN_SLUG = 'famethemes-demo-importer';
const PLUGIN_ID = `${ PLUGIN_SLUG }/${ PLUGIN_SLUG }`;

export default function StarterTemplates() {
	const boot = useBoot();
	const slotRef = useRef( null );

	const importerActive = !! boot?.importer?.active;

	useEffect( () => {
		if ( ! importerActive ) {
			return undefined;
		}
		const el = slotRef.current;
		const api = typeof window !== 'undefined' ? window.ftDemoImporter : null;
		if ( ! el || ! api?.mount ) {
			return undefined;
		}
		api.mount( el );
		return () => {
			api.unmount?.( el );
		};
	}, [ importerActive ] );

	if ( importerActive ) {
		return (
			<div className="customify-dashboard-starter-templates is-embedded">
				<div
					ref={ slotRef }
					id="ft-demo-importer-app"
					className="customify-dashboard-starter-templates__slot"
				/>
			</div>
		);
	}

	return <InstallCta boot={ boot } />;
}

/**
 * CTA card — drives the install / activate flow against
 * `/wp/v2/plugins`. Lifecycle:
 *
 *   idle → busy (GET status)
 *     ├── 404 / not_installed → POST /wp/v2/plugins {slug, status:active}
 *     ├── exists & status=inactive → POST /wp/v2/plugins/{id} {status:active}
 *     └── exists & status=active → already-active short-circuit
 *   → on success: window.location.reload()
 *   → on failure: surface message + offer the legacy WP install screen
 *                 link as a manual fallback.
 *
 * Reload (vs. flipping the local state) is deliberate: the importer
 * plugin only adds `boot.importer.active` once its adapter has loaded,
 * which requires a fresh PHP request — there's no way to opt the tab
 * into embedded mode in-place.
 */
function InstallCta( { boot } ) {
	const [ busy, setBusy ] = useState( false );
	const [ error, setError ] = useState( null );

	const fallbackUrl =
		boot?.urls?.starterTemplatesInstall ||
		'plugin-install.php?tab=search&s=famethemes+demo+importer';

	const handleClick = async () => {
		setBusy( true );
		setError( null );
		try {
			// 1. Probe current install state. apiFetch throws on 4xx;
			// a 404 means "not installed" and is a normal branch, not
			// an error.
			let current = null;
			try {
				current = await apiFetch( {
					path: `/wp/v2/plugins/${ PLUGIN_ID }`,
				} );
			} catch ( e ) {
				if ( e?.data?.status !== 404 && e?.code !== 'rest_plugin_not_found' ) {
					throw e;
				}
			}

			if ( ! current ) {
				// 2a. Not installed → install (POST /wp/v2/plugins
				// pulls the slug from wp.org). Passing `status:
				// active` makes WP activate as part of the same
				// request.
				await apiFetch( {
					path: '/wp/v2/plugins',
					method: 'POST',
					data: { slug: PLUGIN_SLUG, status: 'active' },
				} );
			} else if ( current.status !== 'active' ) {
				// 2b. Installed but not active → activate.
				await apiFetch( {
					path: `/wp/v2/plugins/${ PLUGIN_ID }`,
					method: 'POST',
					data: { status: 'active' },
				} );
			}
			// 2c. Already active → fall through to reload.

			window.location.reload();
		} catch ( e ) {
			const msg =
				e?.message ||
				__( 'Could not install or activate the plugin.', 'customify' );
			setError( msg );
			setBusy( false );
		}
	};

	return (
		<div className="customify-dashboard-starter-templates">
			<section className="pmdk-hero">
				<div className="pmdk-hero__content">
					<h2 className="pmdk-hero__title">
						{ __( 'Starter Templates', 'customify' ) }
					</h2>
					<p className="pmdk-hero__tagline">
						{ __(
							'Create and customize professionally designed websites in minutes. Simply choose your template, choose your colors, and import. Done!',
							'customify',
						) }
					</p>
					{ error && (
						<Notice
							status="error"
							isDismissible={ false }
							className="customify-dashboard-starter-templates__notice"
						>
							{ error }{ ' ' }
							<a href={ fallbackUrl }>
								{ __(
									'Install manually instead.',
									'customify',
								) }
							</a>
						</Notice>
					) }
					<Button
						variant="primary"
						className="pmdk-hero__cta"
						onClick={ handleClick }
						disabled={ busy }
						isBusy={ busy }
					>
						{ busy && <Spinner /> }
						{ busy
							? __( 'Activating Starter Templates…', 'customify' )
							: __( 'Activate Customify Starter Templates', 'customify' ) }
					</Button>
				</div>
			</section>
		</div>
	);
}
