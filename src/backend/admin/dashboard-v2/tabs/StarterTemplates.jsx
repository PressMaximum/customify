/**
 * Starter Templates tab.
 *
 * Three modes, picked from the boot payload:
 *
 *   - Default (`boot.useStarterTemplates` falsy) → render a "Coming soon"
 *     placeholder with a disabled button. This is the public-facing
 *     state while the activation flow isn't ready to ship. Gate is
 *     flipped on by `define( 'CUSTOMIFY_USE_STARTER_TEMPLATES', true )`
 *     in wp-config.php or the `customify_use_starter_templates` filter
 *     (see inc/admin/dashboard-v2.php).
 *
 *   - `boot.importer.active` set by the Customify Starter Sites plugin's
 *     Customify adapter (hooks `customify_dashboard_localize`) →
 *     embed the plugin's React app into this tab. The plugin enqueues
 *     its bundle on `toplevel_page_customify`, exposes
 *     `window.customifyStarterSites.mount(el)` / `unmount(el)`, and skips
 *     its own auto-mount (because `embedded: true`).
 *
 *   - Otherwise → render the CTA that one-click installs + activates the
 *     Customify Starter Sites plugin via WP's /wp/v2/plugins REST
 *     endpoint, then reloads so the tab flips into the embedded mode
 *     above.
 */

import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { Button, Notice, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useBoot } from '@pressmaximum/dashboard-kit';

// REST identifier WP uses for a plugin = `{folder}/{file-without-ext}`.
// Customify Starter Sites' main file matches its slug.
const PLUGIN_SLUG = 'customify-starter-sites';
const PLUGIN_ID = `${ PLUGIN_SLUG }/${ PLUGIN_SLUG }`;

export default function StarterTemplates() {
	const boot = useBoot();
	const slotRef = useRef( null );

	const useStarterTemplates = !! boot?.useStarterTemplates;
	const importerActive = !! boot?.importer?.active;

	useEffect( () => {
		if ( ! useStarterTemplates || ! importerActive ) {
			return undefined;
		}
		const el = slotRef.current;
		const api = typeof window !== 'undefined' ? window.customifyStarterSites : null;
		if ( ! el || ! api?.mount ) {
			return undefined;
		}
		api.mount( el );
		return () => {
			api.unmount?.( el );
		};
	}, [ useStarterTemplates, importerActive ] );

	if ( ! useStarterTemplates ) {
		return <ComingSoon />;
	}

	if ( importerActive ) {
		return (
			<div className="customify-dashboard-starter-templates is-embedded">
				<div
					ref={ slotRef }
					id="customify-starter-sites-app"
					className="customify-dashboard-starter-templates__slot"
				/>
			</div>
		);
	}

	return <InstallCta boot={ boot } />;
}

/**
 * Public-facing placeholder while the activation flow isn't shipping
 * yet. Mirrors the hero shell of InstallCta so the tab keeps the same
 * visual rhythm — only the CTA copy changes and the button is disabled.
 */
function ComingSoon() {
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
					<Button
						variant="primary"
						className="pmdk-hero__cta"
						disabled
						aria-disabled="true"
					>
						{ __( 'Coming soon', 'customify' ) }
					</Button>
				</div>
			</section>
		</div>
	);
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
	// Install state of the plugin, discovered on mount:
	//   'checking'      — probing /wp/v2/plugins (initial)
	//   'not_installed' — needs installing from wordpress.org
	//   'inactive'      — installed but not activated yet
	const [ pluginState, setPluginState ] = useState( 'checking' );

	const fallbackUrl =
		boot?.urls?.starterTemplatesInstall ||
		'plugin-install.php?tab=search&s=customify+starter+sites';

	// Probe whether the plugin is installed / active. A 404 (or
	// rest_plugin_not_found) means it isn't installed — a normal branch,
	// not an error. If it's already active the whole tab is in embedded
	// mode and this CTA never renders, so we only distinguish
	// not-installed vs inactive here.
	const probeState = async () => {
		try {
			const current = await apiFetch( {
				path: `/wp/v2/plugins/${ PLUGIN_ID }`,
			} );
			return current?.status === 'active' ? 'active' : 'inactive';
		} catch ( e ) {
			if ( e?.data?.status === 404 || e?.code === 'rest_plugin_not_found' ) {
				return 'not_installed';
			}
			throw e;
		}
	};

	useEffect( () => {
		let cancelled = false;
		( async () => {
			try {
				const state = await probeState();
				if ( ! cancelled ) {
					// An 'active' result means the adapter should have
					// flipped the tab into embedded mode already; treat it
					// as inactive here so the Activate button still lets the
					// user recover if the boot flag lagged.
					setPluginState( state === 'active' ? 'inactive' : state );
				}
			} catch ( e ) {
				if ( ! cancelled ) {
					setPluginState( 'not_installed' );
				}
			}
		} )();
		return () => {
			cancelled = true;
		};
	}, [] );

	// Step 1 — install the plugin from wordpress.org (without activating).
	// On success, move to the 'inactive' state so the Activate button shows.
	const handleInstall = async () => {
		setBusy( true );
		setError( null );
		try {
			await apiFetch( {
				path: '/wp/v2/plugins',
				method: 'POST',
				// No `status: active` — install only; the user activates
				// with a separate, explicit step below.
				data: { slug: PLUGIN_SLUG },
			} );
			setPluginState( 'inactive' );
		} catch ( e ) {
			setError(
				e?.message ||
					__( 'Could not install the plugin.', 'customify' ),
			);
		} finally {
			setBusy( false );
		}
	};

	// Step 2 — activate the installed plugin, then reload so the tab
	// re-renders in embedded mode (the plugin only sets
	// boot.importer.active after a fresh PHP request).
	const handleActivate = async () => {
		setBusy( true );
		setError( null );
		try {
			await apiFetch( {
				path: `/wp/v2/plugins/${ PLUGIN_ID }`,
				method: 'POST',
				data: { status: 'active' },
			} );
			window.location.reload();
		} catch ( e ) {
			setError(
				e?.message ||
					__( 'Could not activate the plugin.', 'customify' ),
			);
			setBusy( false );
		}
	};

	const installing = busy && pluginState === 'not_installed';
	const activating = busy && pluginState === 'inactive';

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

					{ pluginState === 'checking' && (
						<Button variant="primary" className="pmdk-hero__cta" disabled isBusy>
							<Spinner />
							{ __( 'Checking…', 'customify' ) }
						</Button>
					) }

					{ pluginState === 'not_installed' && (
						<Button
							variant="primary"
							className="pmdk-hero__cta"
							onClick={ handleInstall }
							disabled={ busy }
							isBusy={ installing }
						>
							{ installing && <Spinner /> }
							{ installing
								? __( 'Installing Starter Templates…', 'customify' )
								: __( 'Activate Customify Starter Templates', 'customify' ) }
						</Button>
					) }

					{ pluginState === 'inactive' && (
						<Button
							variant="primary"
							className="pmdk-hero__cta"
							onClick={ handleActivate }
							disabled={ busy }
							isBusy={ activating }
						>
							{ activating && <Spinner /> }
							{ activating
								? __( 'Activating…', 'customify' )
								: __( 'Activate plugin', 'customify' ) }
						</Button>
					) }
				</div>
			</section>
		</div>
	);
}
