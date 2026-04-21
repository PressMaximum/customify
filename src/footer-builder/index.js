/**
 * Customify Footer Builder — React-powered Customizer panel.
 *
 * Reuses the generic Builder component from header-builder.
 * Config is read from window.Customify_Layout_Builder.builders.footer
 * (injected by PHP alongside the header builder data).
 */

import { render } from '@wordpress/element';
import { SlotFillProvider } from '@wordpress/components';
import Builder from '../header-builder/Builder';
import '../header-builder/style.scss';

// Row layout control — CSS + mounting logic bundled here so we don't need
// a separate script enqueue for it.
import '../footer-row-layout/style.scss';
import RowLayout from '../footer-row-layout/RowLayout';

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

wp.customize.bind( 'ready', () => {
	const config = window.Customify_Layout_Builder?.builders?.footer || {};

	const container = document.createElement( 'div' );
	container.id = 'customify-footer-builder-root';
	document.querySelector( 'body .wp-full-overlay' )?.appendChild( container );

	render(
		<SlotFillProvider>
			<Builder config={ config } />
		</SlotFillProvider>,
		container
	);

	// Mount Columns Layout React controls into the footer row sections.
	mountRowLayouts();

	// Re-mount when any section expands (row sections open via gear icon).
	try {
		wp.customize.section.each( ( section ) => {
			section.expanded.bind( ( isExpanded ) => {
				if ( isExpanded ) setTimeout( mountRowLayouts, 100 );
			} );
		} );
	} catch ( _e ) {}
} );
