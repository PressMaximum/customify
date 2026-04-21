/**
 * Footer Row Layout control — mounts into placeholder divs rendered by
 * Customify_Customizer_Control_Row_Layout.
 */

import './style.scss';

import { render } from '@wordpress/element';
import RowLayout from './RowLayout';

function mountRowLayouts() {
	document
		.querySelectorAll( '[id^="cb-row-layout-"]:not([data-rl-mounted])' )
		.forEach( ( el ) => {
			const settingKey = el.dataset.setting;
			if ( ! settingKey ) return;
			el.setAttribute( 'data-rl-mounted', '1' );
			render( <RowLayout settingKey={ settingKey } />, el );
		} );
}

// Primary: WP Customize ready event.
if ( window.wp && wp.customize ) {
	wp.customize.bind( 'ready', () => {
		mountRowLayouts();

		// Bind to section expand events as a fallback (row sections open lazily).
		try {
			wp.customize.section.each( ( section ) => {
				section.expanded.bind( ( isExpanded ) => {
					if ( isExpanded ) setTimeout( mountRowLayouts, 100 );
				} );
			} );
		} catch ( _e ) {
			// Non-fatal — initial mount already ran above.
		}
	} );
}

// Fallback: DOM ready (in case wp.customize.bind fires before this script).
document.addEventListener( 'DOMContentLoaded', () => {
	setTimeout( mountRowLayouts, 500 );
} );
