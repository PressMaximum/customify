/**
 * Snackbar host — renders any notice dispatched into the @wordpress/notices
 * store with `type: 'snackbar'`. Same primitive the block editor uses, so
 * dashboard toasts inherit Gutenberg's auto-dismiss timing + visual style.
 *
 * On top of the native Gutenberg snackbar we prefix each notice's content
 * with a status icon — green check for success (matching the
 * "Next things to do" checklist), warning triangle in amber/red for
 * warning/error, info circle for info. Status detection runs over each
 * notice's `status` field; everything else is left to SnackbarList.
 *
 * Mounted via a portal to document.body so the fixed-position snackbar
 * host escapes the dashboard's #customify-dashboard scroll context.
 *
 * Mount once at the root (App.js); any component can dispatch via:
 *   const { createNotice } = useDispatch( noticesStore );
 *   createNotice( 'success' | 'error' | 'warning' | 'info', message,
 *                 { type: 'snackbar' } );
 */

import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { createPortal } from 'react-dom';

import Icon from '../../ui/Icon';

/**
 * Map a notice's `status` to the icon + variant class that drives its
 * colour. Falls back to info for any status we don't explicitly recognise.
 */
function statusGlyph( status ) {
	switch ( status ) {
		case 'success':
			return { name: 'check', variant: 'success', size: 12 };
		case 'warning':
			return { name: 'warning', variant: 'warning', size: 14 };
		case 'error':
			return { name: 'warning', variant: 'error', size: 14 };
		case 'info':
		default:
			return { name: 'info', variant: 'info', size: 14 };
	}
}

/**
 * Wrap a notice's content so the snackbar renders `[icon] message` in a
 * single inline row. We only mutate `content`; everything else
 * (`actions`, `explicitDismiss`, etc.) is passed through untouched.
 */
function decorateNotice( notice ) {
	const { name, variant, size } = statusGlyph( notice.status );
	return {
		...notice,
		content: (
			<>
				<span
					className={ `pm-toast-icon pm-toast-icon--${ variant }` }
					aria-hidden="true"
				>
					<Icon name={ name } size={ size } />
				</span>
				<span className="pm-toast-text">{ notice.content }</span>
			</>
		),
	};
}

export default function Notices() {
	const notices = useSelect(
		( select ) => select( noticesStore ).getNotices(),
		[]
	);
	const { removeNotice } = useDispatch( noticesStore );
	const snackbarNotices = notices
		.filter( ( n ) => n.type === 'snackbar' )
		.map( decorateNotice );

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
