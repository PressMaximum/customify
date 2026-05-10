/**
 * Snackbar host — renders any notice dispatched into the @wordpress/notices
 * store with `type: 'snackbar'`.
 *
 * We render a custom list (instead of @wordpress/components SnackbarList) so
 * we can prefix each toast with a status icon — green check for success, an
 * amber/red warning triangle for warning/error, info circle for info — and
 * match the iconography the "Next things to do" checklist uses.
 *
 * Auto-dismiss timing mirrors WP's SnackbarList default: we set a 4-second
 * timeout when a notice is mounted; user can still close it manually via
 * the close button or by clicking the body. Mount once at the root
 * (App.js); any component can dispatch via:
 *
 *   const { createNotice } = useDispatch( noticesStore );
 *   createNotice( 'success' | 'error' | 'warning' | 'info', message,
 *                 { type: 'snackbar' } );
 */

import { useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { createPortal } from 'react-dom';

import Icon from '../../ui/Icon';

const AUTO_DISMISS_MS = 4000;

/**
 * Map a notice's `status` to the icon name + the variant class that drives
 * its color (green / amber / red / blue). Falls back to info for any
 * status we don't explicitly recognise.
 */
function mapStatus( status ) {
	switch ( status ) {
		case 'success':
			return { icon: 'check', variant: 'success' };
		case 'warning':
			return { icon: 'warning', variant: 'warning' };
		case 'error':
			return { icon: 'warning', variant: 'error' };
		case 'info':
		default:
			return { icon: 'info', variant: 'info' };
	}
}

function ToastItem( { notice, onRemove } ) {
	const { icon, variant } = mapStatus( notice.status );

	useEffect( () => {
		if ( ! notice.explicitDismiss ) {
			const timer = setTimeout( () => onRemove( notice.id ), AUTO_DISMISS_MS );
			return () => clearTimeout( timer );
		}
		return undefined;
	}, [ notice.id, notice.explicitDismiss, onRemove ] );

	return (
		<div
			className={ `pm-toast pm-toast--${ variant }` }
			role={ variant === 'error' ? 'alert' : 'status' }
		>
			<span className={ `pm-toast__icon pm-toast__icon--${ variant }` }>
				<Icon
					name={ icon }
					size={ variant === 'success' ? 12 : 16 }
				/>
			</span>
			<div className="pm-toast__body">{ notice.content }</div>
			<button
				type="button"
				className="pm-toast__close"
				aria-label="Dismiss"
				onClick={ () => onRemove( notice.id ) }
			>
				<Icon name="close" size={ 14 } />
			</button>
		</div>
	);
}

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
		<div className="pm-snackbar-host" aria-live="polite">
			<ul className="pm-toast-list">
				{ snackbarNotices.map( ( notice ) => (
					<li key={ notice.id }>
						<ToastItem notice={ notice } onRemove={ removeNotice } />
					</li>
				) ) }
			</ul>
		</div>,
		document.body
	);
}
