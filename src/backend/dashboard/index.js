/**
 * Customify Dashboard — admin page entry.
 *
 * Mounts the React app into the #customify-dashboard node printed by
 * Customify_Theme_Dashboard::render_page(). Loaded only on the Customify
 * admin page (toplevel_page_customify) — see inc/admin/class-theme-dashboard.php.
 */

import { createRoot } from '@wordpress/element';

import App from './app/App';
import './style.css';

document.addEventListener( 'DOMContentLoaded', () => {
	const node = document.getElementById( 'customify-dashboard' );
	if ( ! node ) {
		return;
	}
	createRoot( node ).render( <App /> );
} );
