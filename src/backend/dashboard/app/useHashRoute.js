/**
 * Two-way sync between the active tab and window.location.hash so that
 *   a) deep links (?page=customify#settings) land on the right tab
 *   b) tab clicks update the URL without reloading
 *   c) browser back/forward + manual hash edits update the active tab
 *
 * Validation lives in routes.js — invalid hashes fall back to the default.
 */

import { useEffect, useState, useCallback } from '@wordpress/element';

import { DEFAULT_TAB, isValidTab } from './routes';

function readHash() {
	const raw = window.location.hash.replace( /^#/, '' );
	return isValidTab( raw ) ? raw : DEFAULT_TAB;
}

export default function useHashRoute() {
	const [ tab, setTab ] = useState( readHash );

	useEffect( () => {
		const onHashChange = () => setTab( readHash() );
		window.addEventListener( 'hashchange', onHashChange );
		return () =>
			window.removeEventListener( 'hashchange', onHashChange );
	}, [] );

	const navigate = useCallback( ( id ) => {
		if ( ! isValidTab( id ) ) {
			return;
		}
		if ( window.location.hash !== `#${ id }` ) {
			window.location.hash = id;
		} else {
			setTab( id );
		}
	}, [] );

	return [ tab, navigate ];
}
