/**
 * Trap Tab/Shift+Tab focus inside a container. Saves the previously focused
 * element and restores it on cleanup. Auto-focuses the first focusable
 * element inside the container (or [data-autofocus] if present).
 *
 * Usage:
 *   const ref = useRef();
 *   useFocusTrap(ref, isOpen);
 *   return <div ref={ref}>...</div>;
 */

import { useEffect } from '@wordpress/element';

const FOCUSABLE_SELECTOR = [
	'a[href]',
	'button:not([disabled])',
	'input:not([disabled])',
	'select:not([disabled])',
	'textarea:not([disabled])',
	'[tabindex]:not([tabindex="-1"])',
].join( ',' );

export default function useFocusTrap( ref, enabled ) {
	useEffect( () => {
		if ( ! enabled || ! ref.current ) {
			return undefined;
		}
		const root = ref.current;
		const previouslyFocused = document.activeElement;

		// Initial focus.
		const autofocus = root.querySelector( '[data-autofocus]' );
		const focusables = root.querySelectorAll( FOCUSABLE_SELECTOR );
		const initial = autofocus || focusables[ 0 ] || root;
		if ( initial && typeof initial.focus === 'function' ) {
			initial.focus();
		}

		const onKey = ( e ) => {
			if ( e.key !== 'Tab' ) {
				return;
			}
			const list = root.querySelectorAll( FOCUSABLE_SELECTOR );
			if ( ! list.length ) {
				e.preventDefault();
				return;
			}
			const first = list[ 0 ];
			const last = list[ list.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		};
		document.addEventListener( 'keydown', onKey );
		return () => {
			document.removeEventListener( 'keydown', onKey );
			if (
				previouslyFocused &&
				typeof previouslyFocused.focus === 'function'
			) {
				previouslyFocused.focus();
			}
		};
	}, [ ref, enabled ] );
}
