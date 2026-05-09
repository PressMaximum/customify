/**
 * Settings AJAX client. Wraps tasks dispatched in
 * Customify_Theme_Dashboard::ajax_dispatch (single admin-ajax action +
 * `task` routing field):
 *
 *   get_settings   → current saved settings (defaults merged)
 *   save_settings  → persist + return sanitized shape
 *   clear_cache    → flush cached per-page CSS (stub for now)
 */

import { ajaxCall } from './ajax';

export function fetchSettings() {
	return ajaxCall( 'get_settings' );
}

export function saveSettings( data ) {
	return ajaxCall( 'save_settings', { payload: data } );
}

export function clearCache() {
	return ajaxCall( 'clear_cache' );
}
