/**
 * Welcome sidebar — recommended free plugins from wordpress.org.
 *
 * Server (Customify::theme_dashboard_inject_recommend_plugins) fetches
 * plugin metadata via plugins_api with a 12h transient cache, filters out
 * already-active plugins, and ships only rows worth surfacing.
 *
 * Each row drives a per-plugin state machine:
 *
 *   not-installed → installing → installed → activating → active
 *                                                 ↘ error
 *
 * No tab switch, no redirect — install via wp.updates.installPlugin()
 * (WP core Updater) and activate via our dashboard AJAX dispatcher. If
 * `wp.updates` is missing (page is older / unprivileged user / JS error),
 * we fall back to the original `actionUrl` redirect so the button is never
 * dead.
 */

import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { Card, Button } from '../../ui';
import {
	RECOMMEND_PLUGINS,
	CAN_INSTALL_PLUGINS,
	CAN_ACTIVATE_PLUGINS,
} from '../config';
import {
	installPlugin,
	activatePlugin,
} from '../api/recommend-plugins';

/**
 * Resolve the visual + behavioral params for one row given its current
 * client-side state. Keeps the JSX below readable.
 */
function actionFor( state, plugin ) {
	switch ( state ) {
		case 'installing':
			return {
				label: __( 'Installing…', 'customify' ),
				busy: true,
				variant: 'secondary',
				disabled: true,
			};
		case 'installed':
			return {
				label: __( 'Activate', 'customify' ),
				variant: 'primary',
			};
		case 'activating':
			return {
				label: __( 'Activating…', 'customify' ),
				busy: true,
				variant: 'primary',
				disabled: true,
			};
		case 'active':
			return {
				label: __( 'Active', 'customify' ),
				variant: 'tertiary',
				disabled: true,
			};
		case 'not-installed':
		default:
			return {
				label: plugin.actionLabel || __( 'Install Now', 'customify' ),
				variant: 'secondary',
			};
	}
}

export default function RecommendPluginsCard() {
	const { createNotice } = useDispatch( noticesStore );
	// Per-slug client state — server-provided state is the initial seed.
	const [ states, setStates ] = useState( () => {
		const seed = {};
		RECOMMEND_PLUGINS.forEach( ( p ) => {
			seed[ p.slug ] = p.state || 'not-installed';
		} );
		return seed;
	} );

	if ( ! RECOMMEND_PLUGINS.length ) {
		return null;
	}

	function notify( type, message ) {
		createNotice( type, message, { type: 'snackbar' } );
	}

	function setState( slug, next ) {
		setStates( ( prev ) => ( { ...prev, [ slug ]: next } ) );
	}

	async function handleInstall( plugin ) {
		if ( ! CAN_INSTALL_PLUGINS ) return; // capability gate; href fallback handles redirect
		setState( plugin.slug, 'installing' );
		try {
			await installPlugin( plugin.slug );
			setState( plugin.slug, 'installed' );
			notify(
				'success',
				sprintf(
					/* translators: %s: plugin name */
					__( '"%s" installed.', 'customify' ),
					plugin.name
				)
			);
		} catch ( err ) {
			setState( plugin.slug, 'not-installed' );
			notify(
				'error',
				err && err.message
					? err.message
					: sprintf(
						/* translators: %s: plugin name */
						__( 'Could not install "%s".', 'customify' ),
						plugin.name
					  )
			);
		}
	}

	async function handleActivate( plugin ) {
		if ( ! CAN_ACTIVATE_PLUGINS ) return;
		setState( plugin.slug, 'activating' );
		try {
			await activatePlugin( plugin.slug );
			setState( plugin.slug, 'active' );
			notify(
				'success',
				sprintf(
					/* translators: %s: plugin name */
					__( '"%s" activated.', 'customify' ),
					plugin.name
				)
			);
		} catch ( err ) {
			setState( plugin.slug, 'installed' );
			notify(
				'error',
				err && err.message
					? err.message
					: sprintf(
						/* translators: %s: plugin name */
						__( 'Could not activate "%s".', 'customify' ),
						plugin.name
					  )
			);
		}
	}

	function onClick( plugin, state, e ) {
		// Capability check has the same gate server-side; without the cap
		// the bootstrap doesn't ship a working flow either way. Let the
		// browser follow the legacy `href` so the unprivileged user still
		// sees the standard wp-admin "Sorry, you are not allowed" screen.
		const canDoAjax = state === 'not-installed'
			? CAN_INSTALL_PLUGINS && !! window.wp?.updates?.installPlugin
			: CAN_ACTIVATE_PLUGINS;
		if ( ! canDoAjax ) {
			return; // let the anchor follow plugin.actionUrl
		}
		e.preventDefault();
		if ( state === 'not-installed' ) {
			handleInstall( plugin );
		} else if ( state === 'installed' ) {
			handleActivate( plugin );
		}
	}

	return (
		<Card title={ __( 'Recommend Plugins', 'customify' ) }>
			<ul className="pm-recommend-plugins">
				{ RECOMMEND_PLUGINS.map( ( plugin ) => {
					const state = states[ plugin.slug ];
					const action = actionFor( state, plugin );
					const isFinal = state === 'active';
					return (
						<li
							key={ plugin.slug }
							className={
								'pm-recommend-plugins__item' +
								( isFinal
									? ' pm-recommend-plugins__item--active'
									: '' )
							}
						>
							{ plugin.iconUrl && (
								<img
									className="pm-recommend-plugins__icon"
									src={ plugin.iconUrl }
									alt=""
									loading="lazy"
								/>
							) }
							<div className="pm-recommend-plugins__body">
								<p className="pm-recommend-plugins__name">
									{ plugin.name }
								</p>
								<div className="pm-recommend-plugins__actions">
									<Button
										variant={ action.variant }
										size="small"
										href={
											isFinal ? undefined : plugin.actionUrl
										}
										disabled={ action.disabled }
										isBusy={ action.busy }
										onClick={ ( e ) =>
											onClick( plugin, state, e )
										}
									>
										{ action.label }
									</Button>
									{ plugin.detailsUrl && (
										<a
											className="pm-recommend-plugins__details"
											href={ plugin.detailsUrl }
											target="_blank"
											rel="noreferrer"
										>
											{ __( 'Details', 'customify' ) }
										</a>
									) }
								</div>
							</div>
						</li>
					);
				} ) }
			</ul>
		</Card>
	);
}
