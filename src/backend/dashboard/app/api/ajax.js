/**
 * Shared admin-ajax.php client. All dashboard requests go through a single
 * `action` (customify_dashboard); the `task` field routes to the right
 * handler in PHP (Customify_Theme_Dashboard::ajax_dispatch).
 *
 * Reads URL + nonce from the bootstrap window.customifyDashboard object
 * emitted by Customify_Theme_Dashboard::enqueue_assets.
 *
 * Usage:
 *   const data = await ajaxCall( 'get_settings' );
 *   await ajaxCall( 'save_settings', { payload: state } );
 *
 * Object values are JSON-stringified before being sent — server-side
 * handlers `json_decode` the matching field. Throws on HTTP error or when
 * WP responds with `success: false` so callers can use try/catch.
 */

const ACTION = 'customify_dashboard';

export async function ajaxCall( task, data = {} ) {
	const config =
		( typeof window !== 'undefined' && window.customifyDashboard ) || {};
	const body = new FormData();
	body.append( 'action', ACTION );
	body.append( 'task', task );
	body.append( 'nonce', config.nonce || '' );
	Object.entries( data ).forEach( ( [ key, value ] ) => {
		body.append(
			key,
			typeof value === 'object' ? JSON.stringify( value ) : value
		);
	} );

	const res = await fetch( config.ajaxUrl || '', {
		method: 'POST',
		credentials: 'same-origin',
		body,
	} );
	const json = await res.json();
	if ( ! json || ! json.success ) {
		throw new Error(
			( json && json.data ) || `AJAX task ${ task } failed`
		);
	}
	return json.data;
}
