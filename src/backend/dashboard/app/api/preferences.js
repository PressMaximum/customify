/**
 * User preferences AJAX client. Lightweight wrappers for tasks that
 * persist a single per-user toggle (no settings round-trip needed).
 */

import { ajaxCall } from './ajax';

/**
 * Persist whether the Welcome > "Things to do" card is dismissed for the
 * current user. Server stores under user_meta `customify_things_to_do_hidden`.
 */
export function setThingsToDoHidden( hidden ) {
	return ajaxCall( 'set_things_to_do_hidden', {
		hidden: hidden ? '1' : '0',
	} );
}
