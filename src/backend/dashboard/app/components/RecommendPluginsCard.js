/**
 * Welcome sidebar — recommended free plugins from wordpress.org.
 *
 * Server (Customify::theme_dashboard_inject_recommend_plugins) ships each
 * row with a ready-to-use `actionUrl`:
 *
 *   not-installed → /wp-admin/update.php?action=install-plugin&plugin=…&_wpnonce=…
 *   installed     → /wp-admin/plugins.php?action=activate&plugin=…&_wpnonce=…
 *
 * Both URLs are full wp-admin pages with valid nonces. We fetch them
 * directly with `credentials: 'same-origin'` so the user's auth cookies
 * gate the action, parse the HTML response for the relevant outcome
 * markers, and drive a per-row state machine — no PHP-side AJAX handler,
 * no tab switch, no redirect.
 *
 *   not-installed → installing → installed → activating → active
 *                                                ↘ error
 *
 * If `fetch` is unavailable (legacy browser, blocked by extension) the
 * Button still has the original `href`, so the click falls through to a
 * normal browser navigation as a graceful fallback.
 */

import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { Card, Button } from '../../ui';
import { RECOMMEND_PLUGINS } from '../config';
import { runInstall, runActivate } from '../api/recommend-plugins';

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
	const [ rows, setRows ] = useState( () => {
		const seed = {};
		RECOMMEND_PLUGINS.forEach( ( p ) => {
			seed[ p.slug ] = {
				state: p.state || 'not-installed',
				// Server only sends an activate URL for already-installed
				// rows. After we install fresh, we extract it from the
				// response HTML and stash it here for the next click.
				activateUrl:
					p.state === 'installed' ? p.actionUrl : '',
			};
		} );
		return seed;
	} );

	if ( ! RECOMMEND_PLUGINS.length ) return null;

	function notify( type, message ) {
		createNotice( type, message, { type: 'snackbar' } );
	}

	function patch( slug, next ) {
		setRows( ( prev ) => ( {
			...prev,
			[ slug ]: { ...prev[ slug ], ...next },
		} ) );
	}

	async function handleInstall( plugin ) {
		patch( plugin.slug, { state: 'installing' } );
		try {
			const { activateUrl } = await runInstall(
				plugin.actionUrl,
				plugin.name
			);
			patch( plugin.slug, { state: 'installed', activateUrl } );
			notify(
				'success',
				sprintf(
					/* translators: %s: plugin name */
					__( '"%s" installed.', 'customify' ),
					plugin.name
				)
			);
		} catch ( err ) {
			patch( plugin.slug, { state: 'not-installed' } );
			notify(
				'error',
				( err && err.message ) ||
					sprintf(
						/* translators: %s: plugin name */
						__( 'Could not install "%s".', 'customify' ),
						plugin.name
					)
			);
		}
	}

	async function handleActivate( plugin, activateUrl ) {
		patch( plugin.slug, { state: 'activating' } );
		try {
			await runActivate( activateUrl, plugin.name );
			patch( plugin.slug, { state: 'active' } );
			notify(
				'success',
				sprintf(
					/* translators: %s: plugin name */
					__( '"%s" activated.', 'customify' ),
					plugin.name
				)
			);
		} catch ( err ) {
			patch( plugin.slug, { state: 'installed' } );
			notify(
				'error',
				( err && err.message ) ||
					sprintf(
						/* translators: %s: plugin name */
						__( 'Could not activate "%s".', 'customify' ),
						plugin.name
					)
			);
		}
	}

	function onClick( plugin, e ) {
		const row = rows[ plugin.slug ] || {};
		const state = row.state || 'not-installed';

		// Fail-open: if fetch isn't available let the anchor follow href.
		if ( typeof fetch !== 'function' ) return;

		if ( state === 'not-installed' ) {
			e.preventDefault();
			handleInstall( plugin );
		} else if ( state === 'installed' ) {
			e.preventDefault();
			handleActivate( plugin, row.activateUrl || plugin.actionUrl );
		} else {
			// installing / activating / active — disabled button shouldn't
			// fire onClick, but if a screen reader or assistive tech does,
			// just block.
			e.preventDefault();
		}
	}

	return (
		<Card title={ __( 'Recommend Plugins', 'customify' ) }>
			<ul className="pm-recommend-plugins">
				{ RECOMMEND_PLUGINS.map( ( plugin ) => {
					const row = rows[ plugin.slug ] || {};
					const state = row.state || 'not-installed';
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
										onClick={ ( e ) => onClick( plugin, e ) }
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
