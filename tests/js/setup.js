/**
 * Jest test setup. Runs once per test file.
 *
 * Provides browser globals the dashboard expects (window.customifyDashboard
 * bootstrap blob, ajaxUrl, nonce) so components can render without ReferenceErrors.
 *
 * Per-test overrides: set window.customifyDashboard.* directly inside the
 * test before render.
 */

import '@testing-library/jest-dom';

global.window.customifyDashboard = {
	ajaxUrl: 'http://example.test/wp-admin/admin-ajax.php',
	nonce: 'test-nonce',
	proActive: true,
	proModules: [],
	thingsToDoStatus: {},
	thingsToDoHidden: false,
	recommendPlugins: [],
	sitesPlugin: { installed: false, active: false, installUrl: '' },
};

// Silence noisy "useLayoutEffect on server" warnings from @wordpress/components
const originalError = console.error;
beforeAll( () => {
	console.error = ( ...args ) => {
		const msg = String( args[ 0 ] || '' );
		if ( msg.includes( 'useLayoutEffect' ) ) return;
		originalError( ...args );
	};
} );
afterAll( () => {
	console.error = originalError;
} );
