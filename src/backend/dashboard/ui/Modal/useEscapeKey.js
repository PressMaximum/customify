/**
 * Reusable hook — call `handler` whenever the Escape key is pressed and the
 * `enabled` flag is true. Detached for use beyond Modal (Dropdown, etc.).
 */

import { useEffect } from '@wordpress/element';

export default function useEscapeKey( enabled, handler ) {
	useEffect( () => {
		if ( ! enabled ) {
			return undefined;
		}
		const onKey = ( e ) => {
			if ( e.key === 'Escape' ) {
				handler( e );
			}
		};
		document.addEventListener( 'keydown', onKey );
		return () => document.removeEventListener( 'keydown', onKey );
	}, [ enabled, handler ] );
}
