import apiFetch from '@wordpress/api-fetch';
import { register } from '@wordpress/data';
import { createSettingsStore } from '@pressmaximum/dashboard-kit';

const boot = window.customifyDashboard || {};
const restRoot = boot?.rest?.root || '';
const nonce = boot?.rest?.nonce;

// Wire the wp.apiFetch middleware so REST writes carry the nonce. The
// API-fetch middleware is global to wp.data; setting it once on boot
// covers every call the kit's store makes.
if ( restRoot ) {
	apiFetch.use( apiFetch.createRootURLMiddleware( restRoot ) );
}
if ( nonce ) {
	apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
}

const seedSaved = boot?.settings?.values || {};

const { STORE_NAME, store } = createSettingsStore( {
	storeName: 'customify/dashboard-settings',
	endpoint: '/customify/v1/settings',
	fetch: ( args ) => apiFetch( args ),
	seedSaved,
} );

register( store );

export const CUSTOMIFY_SETTINGS_STORE = STORE_NAME;
