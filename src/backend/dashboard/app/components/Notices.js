/**
 * Snackbar host — renders any notice dispatched into the @wordpress/notices
 * store with `type: 'snackbar'`. Same primitive the block editor uses, so
 * dashboard toasts inherit Gutenberg's auto-dismiss timing + visual style.
 *
 * Mounted via a portal to document.body so the fixed-position snackbar
 * host escapes the dashboard's #customify-dashboard scroll context. The
 * wrapper div is what we position (bottom-right) — newer
 * @wordpress/components versions sometimes ship the SnackbarList without a
 * fixed-position rule of its own, which is why CSS targeting
 * .components-snackbar-list directly doesn't always reach the toast.
 *
 * Mount once at the root (App.js); any component can dispatch via
 *   const { createNotice } = useDispatch( noticesStore );
 *   createNotice( 'success' | 'error' | 'warning' | 'info', message,
 *                 { type: 'snackbar' } );
 */

import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { createPortal } from 'react-dom';

export default function Notices() {
	const notices = useSelect(
		( select ) => select( noticesStore ).getNotices(),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );
	const snackbarNotices = notices.filter( ( n ) => n.type === 'snackbar' );

	if ( ! snackbarNotices.length ) {
		return null;
	}

	return createPortal(
		<div className="pm-snackbar-host">
			<SnackbarList
				notices={ snackbarNotices }
				className="pm-snackbar-list"
				onRemove={ removeNotice }
			/>
		</div>,
		document.body
	);
}
