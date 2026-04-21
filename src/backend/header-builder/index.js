/**
 * Customify Header Builder — React-powered Customizer panel.
 *
 * Mounts a React drag-and-drop builder into .wp-full-overlay when the
 * Customizer is ready.  All data lives in the `header_builder_panel_v2`
 * wp.customize Setting so the PHP renderer is untouched.
 */

import { render } from '@wordpress/element';
import { SlotFillProvider } from '@wordpress/components';
import Builder from './Builder';
import './style.scss';

wp.customize.bind( 'ready', () => {
	const config = window.Customify_Layout_Builder?.builders?.header || {};

	const container = document.createElement( 'div' );
	container.id = 'customify-header-builder-root';
	document.querySelector( 'body .wp-full-overlay' )?.appendChild( container );

	render(
		<SlotFillProvider>
			<Builder config={ config } />
		</SlotFillProvider>,
		container
	);
} );
