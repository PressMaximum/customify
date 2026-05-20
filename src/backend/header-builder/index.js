/**
 * Customify Header Builder — React-powered Customizer panel.
 *
 * Mounts a React drag-and-drop builder into .wp-full-overlay when the
 * Customizer is ready. All data lives in the `header_builder_panel_v2`
 * wp.customize Setting so the PHP renderer is untouched.
 *
 * Variant switching (Customify Pro Multiple Headers): when the active
 * variant changes WITHOUT a page reload, the underlying setting value
 * changes. The Builder stays mounted — it listens to the
 * `customify/builder/external-update` window event (see Builder.jsx)
 * and updates its `data` state in place via setData. React then
 * reconciles only the changed BuilderRow / DropZone / ItemChip nodes
 * instead of tearing down the whole tree (no flicker).
 *
 * Customify Pro's useVariantSwitcher dispatches that event for us right
 * after applying the setting diff, so no additional wiring is needed
 * here; this entry point just renders the root.
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
