/**
 * Changelog AJAX client. Server returns the raw changelog.txt content
 * (read via WP_Filesystem); parsing into release records lives on the
 * client — see app/tabs/Changelog.js parseChangelog().
 */

import { ajaxCall } from './ajax';

export function fetchChangelog() {
	return ajaxCall( 'get_changelog' );
}
