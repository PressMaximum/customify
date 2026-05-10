/**
 * Inline install/activate flow for the Recommend Plugins card.
 *
 * Install: delegates to WP core's `wp.updates.installPlugin()` so the
 * Customizer dashboard reuses the same Updater plumbing the wp-admin
 * Plugins screen does (filesystem credentials prompt, error handling,
 * nonce rotation, etc.). Returns a Promise that resolves with the
 * installed plugin file path on success.
 *
 * Activate: hits the dashboard's shared `customify_dashboard` admin-ajax
 * action with the `activate_recommend_plugin` task. The handler verifies
 * a secondary `customify_recommend_plugin` nonce, looks up the plugin
 * file by slug, and calls `activate_plugin()`. Returns a Promise that
 * resolves with `{ slug, pluginFile, isActive }`.
 *
 * Both paths refuse to run unless `wp.updates` is on the page (install)
 * or capability + nonce flags are present (activate); the caller can fall
 * back to the redirect URL the bootstrap already ships.
 */

import { ajaxCall } from './ajax';
import { RECOMMEND_PLUGINS_NONCE } from '../config';

/**
 * Install a wp.org plugin via WP core's Updates API.
 *
 * @param {string} slug Plugin slug (e.g. 'filebird').
 * @returns {Promise<{slug:string, pluginFile:string, activateUrl?:string}>}
 */
export function installPlugin( slug ) {
	return new Promise( ( resolve, reject ) => {
		const updates =
			typeof window !== 'undefined' &&
			window.wp &&
			window.wp.updates;
		if ( ! updates || typeof updates.installPlugin !== 'function' ) {
			reject(
				new Error(
					'wp.updates.installPlugin is not available on this page.'
				)
			);
			return;
		}
		// Auto-respond to filesystem-credentials prompts when WP can write
		// directly. If creds are required, surface the WP modal — the user
		// can fill it, hit "Proceed", and our success/error callbacks fire.
		updates.installPlugin( {
			slug,
			success( response ) {
				resolve( {
					slug: response.slug || slug,
					pluginFile: response.plugin || '',
					activateUrl: response.activateUrl || '',
				} );
			},
			error( response ) {
				const msg =
					( response && response.errorMessage ) ||
					( response && response.errorCode ) ||
					'Install failed';
				const err = new Error( msg );
				err.detail = response;
				reject( err );
			},
		} );
	} );
}

/**
 * Activate an installed plugin via the theme's AJAX dispatcher.
 *
 * @param {string} slug Plugin slug.
 * @returns {Promise<{slug:string, pluginFile:string, isActive:boolean}>}
 */
export function activatePlugin( slug ) {
	return ajaxCall( 'activate_recommend_plugin', {
		slug,
		plugin_nonce: RECOMMEND_PLUGINS_NONCE,
	} );
}
