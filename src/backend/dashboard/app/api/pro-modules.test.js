/**
 * Tests for pro-modules API client.
 *
 * Routes everything through ajaxCall(), which posts to the
 * customify_dashboard admin-ajax action with a `task` field. Tests verify
 * the right task name and payload shape per call. Mocks ajaxCall directly
 * so we don't need fetch/XHR mocking.
 */

jest.mock( './ajax', () => ( { ajaxCall: jest.fn() } ) );

import { ajaxCall } from './ajax';
import {
	setModuleState,
	getModuleSettings,
	setModuleSettings,
} from './pro-modules';

describe( 'pro-modules API client', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	describe( 'setModuleState', () => {
		it( 'sends class_name + enabled=1 when enabling', () => {
			ajaxCall.mockResolvedValue( {
				classKey: 'X',
				enabled: true,
			} );
			setModuleState( 'X', true );
			expect( ajaxCall ).toHaveBeenCalledWith( 'set_module_state', {
				class_name: 'X',
				enabled: '1',
			} );
		} );

		it( 'sends class_name + enabled=0 when disabling', () => {
			ajaxCall.mockResolvedValue( {} );
			setModuleState( 'X', false );
			expect( ajaxCall ).toHaveBeenCalledWith( 'set_module_state', {
				class_name: 'X',
				enabled: '0',
			} );
		} );
	} );

	describe( 'getModuleSettings', () => {
		it( 'sends class_name only', () => {
			ajaxCall.mockResolvedValue( { fields: [], values: {} } );
			getModuleSettings( 'X' );
			expect( ajaxCall ).toHaveBeenCalledWith( 'get_module_settings', {
				class_name: 'X',
			} );
		} );

		it( 'returns server response unchanged', async () => {
			const payload = {
				fields: [ { name: 'kit_id', type: 'text' } ],
				values: { kit_id: 'abc' },
			};
			ajaxCall.mockResolvedValue( payload );
			const out = await getModuleSettings( 'X' );
			expect( out ).toEqual( payload );
		} );
	} );

	describe( 'setModuleSettings', () => {
		it( 'sends class_name + payload object', () => {
			ajaxCall.mockResolvedValue( {} );
			const payload = { kit_id: 'abc', load_type: 'import' };
			setModuleSettings( 'X', payload );
			expect( ajaxCall ).toHaveBeenCalledWith( 'set_module_settings', {
				class_name: 'X',
				payload,
			} );
		} );
	} );
} );
