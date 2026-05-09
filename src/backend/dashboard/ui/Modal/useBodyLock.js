/**
 * Lock document.body scroll while a modal/overlay is open. Restores the
 * original overflow value on unmount or when `enabled` flips false.
 */

import { useEffect } from '@wordpress/element';

export default function useBodyLock( enabled ) {
	useEffect( () => {
		if ( ! enabled ) {
			return undefined;
		}
		const previous = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		return () => {
			document.body.style.overflow = previous;
		};
	}, [ enabled ] );
}
