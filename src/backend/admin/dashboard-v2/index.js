/**
 * Customify Dashboard (v2) entry — bootstraps the kit-powered admin SPA
 * at top-level menu slug `customify`. Page is registered in
 * inc/admin/dashboard-v2.php; this file mounts React into the
 * #customify-dashboard root the PHP renderer emits.
 */

import './dashboard-v2.scss';

// Stub mount — P1 wires the real config + tab components.
// This existence-check keeps the file safe to load on any admin page in
// case the script ever enqueues outside its dashboard hook.
if ( document.getElementById( 'customify-dashboard' ) ) {
	// eslint-disable-next-line no-console
	console.log( 'customify dashboard v2: P0 stub loaded' );
}
