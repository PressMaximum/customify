/**
 * Pro modules AJAX client. The dashboard talks to the server through a
 * single admin-ajax action (`customify_dashboard`); the `task` field
 * routes to the right handler in PHP.
 *
 * `set_module_state` is handled by Customify::theme_dashboard_handle_pro_task
 * which forwards into Customify_Pro::enable_module / disable_module.
 *
 * Resolves to the persisted state echoed by the server, e.g.
 * `{ classKey, enabled }`. Throws on error so callers can revert UI on
 * failure.
 */

import { ajaxCall } from './ajax';

export function setModuleState( classKey, enabled ) {
	return ajaxCall( 'set_module_state', {
		class_name: classKey,
		enabled: enabled ? '1' : '0',
	} );
}

/**
 * Fetch a Pro module's settings schema + current values. Server resolves
 * the module instance via Customify::resolve_pro_module_instance().
 *
 * @param {string} classKey - Pro module class name.
 * @returns {Promise<{fields: Array, values: Object}>}
 */
export function getModuleSettings( classKey ) {
	return ajaxCall( 'get_module_settings', { class_name: classKey } );
}

/**
 * Persist a Pro module's settings via Customify_Pro_Module_Base::save().
 * The payload is JSON-serialized at the network boundary by ajaxCall.
 */
export function setModuleSettings( classKey, payload ) {
	return ajaxCall( 'set_module_settings', {
		class_name: classKey,
		payload,
	} );
}
