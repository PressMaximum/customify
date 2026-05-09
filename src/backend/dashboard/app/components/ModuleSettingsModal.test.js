/**
 * Tests for ModuleSettingsModal — the React modal that renders Pro module
 * settings via the get_module_settings / set_module_settings AJAX tasks.
 *
 * Stubs the API client so tests run in isolation. Verifies:
 *   - schema → fields renders correct controls per type
 *   - save flow refreshes fields + values from server response
 *   - after_save notices surface as snackbar dispatches
 *   - error path keeps modal open
 */

import { render, screen, waitFor, fireEvent } from '@testing-library/react';

// Stub the dashboard's ui barrel BEFORE importing the modal, so we don't
// pull in @wordpress/components (heavy CJS/ESM interop in JSDOM).
jest.mock( '../../ui', () => {
	const React = require( 'react' );
	const Modal = ( { isOpen, children, ariaLabel } ) =>
		isOpen
			? React.createElement(
					'div',
					{ role: 'dialog', 'aria-label': ariaLabel },
					children
			  )
			: null;
	Modal.Header = ( { title } ) => React.createElement( 'h2', null, title );
	Modal.Body = ( { children } ) => React.createElement( 'div', null, children );
	Modal.Footer = ( { children } ) => React.createElement( 'div', null, children );

	const Button = ( { children, onClick, disabled } ) =>
		React.createElement( 'button', { onClick, disabled }, children );

	const Select = ( { value, onChange, options, ariaLabel } ) =>
		React.createElement(
			'select',
			{
				value,
				onChange: ( e ) => onChange( e.target.value ),
				'aria-label': ariaLabel,
			},
			options.map( ( opt ) =>
				React.createElement(
					'option',
					{ key: opt.value, value: opt.value },
					opt.label
				)
			)
		);

	const ToggleSwitch = ( { checked, onChange, ariaLabel } ) =>
		React.createElement( 'input', {
			type: 'checkbox',
			checked,
			onChange: ( e ) => onChange( e.target.checked ),
			'aria-label': ariaLabel,
		} );

	return { Modal, Button, Select, ToggleSwitch };
} );

// Stub the AJAX module — every test gets fresh jest.fn() instances.
jest.mock( '../api/pro-modules', () => ( {
	getModuleSettings: jest.fn(),
	setModuleSettings: jest.fn(),
} ) );

import ModuleSettingsModal from './ModuleSettingsModal';

// noticesStore dispatch is hard to assert without a real WP store. Spy on
// createNotice/removeNotice by mocking @wordpress/data via mock function names
// that follow Jest's "mock-prefix" convention (the only way to reference
// outer variables from a jest.mock factory).
jest.mock( '@wordpress/data', () => {
	const fn = jest.fn();
	const remove = jest.fn();
	return {
		useDispatch: () => ( { createNotice: fn, removeNotice: remove } ),
		__createNoticeMock: fn,
		__removeNoticeMock: remove,
	};
} );
jest.mock(
	'@wordpress/notices',
	() => ( { store: 'notices-store' } ),
	{ virtual: true }
);

import { useDispatch } from '@wordpress/data';
import {
	getModuleSettings,
	setModuleSettings,
} from '../api/pro-modules';

const { __createNoticeMock: createNoticeMock, __removeNoticeMock: removeNoticeMock } = jest.requireMock( '@wordpress/data' );

describe( 'ModuleSettingsModal', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders text + select + html fields per schema', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [
				{ name: 'kit_id', type: 'text', label: 'Kit ID' },
				{
					name: 'load_type',
					type: 'select',
					label: 'Embed',
					options: [
						{ value: 'link', label: 'Link tag' },
						{ value: 'import', label: 'Import' },
					],
				},
				{
					name: 'fonts',
					type: 'html',
					content: '<ul><li>Roboto</li></ul>',
				},
			],
			values: { kit_id: 'abc', load_type: 'import' },
		} );

		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ jest.fn() }
			/>
		);

		await waitFor( () =>
			expect( screen.getByLabelText( 'Kit ID' ) ).toHaveValue( 'abc' )
		);
		expect( screen.getByLabelText( 'Embed' ) ).toHaveValue( 'import' );
		// HTML field renders raw markup
		expect( screen.getByText( 'Roboto' ) ).toBeInTheDocument();
	} );

	it( 'renders checkbox + number controls', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [
				{ name: 'enabled', type: 'checkbox', label: 'Enabled' },
				{
					name: 'count',
					type: 'number',
					label: 'Count',
					min: 0,
					max: 10,
				},
			],
			values: { enabled: 1, count: 5 },
		} );

		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ jest.fn() }
			/>
		);

		await waitFor( () =>
			expect( screen.getByLabelText( 'Enabled' ) ).toBeInTheDocument()
		);
		expect( screen.getByLabelText( 'Count' ) ).toHaveValue( 5 );
	} );

	it( 'unknown field type falls back to text input (forward-compat)', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [ { name: 'mystery', type: 'time_machine', label: 'X' } ],
			values: { mystery: 'value' },
		} );

		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ jest.fn() }
			/>
		);

		await waitFor( () => {
			const input = screen.getByLabelText( 'X' );
			expect( input ).toHaveAttribute( 'type', 'text' );
			expect( input ).toHaveValue( 'value' );
		} );
	} );

	it( 'on save: refreshes fields from server response (dynamic schema)', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [ { name: 'kit_id', type: 'text', label: 'Kit ID' } ],
			values: { kit_id: 'abc' },
		} );
		setModuleSettings.mockResolvedValue( {
			values: { kit_id: 'abc' },
			// New html field appears AFTER save (Typekit fonts list)
			fields: [
				{ name: 'kit_id', type: 'text', label: 'Kit ID' },
				{
					name: 'fonts',
					type: 'html',
					content: '<ul><li>Roboto</li></ul>',
				},
			],
			notices: [],
		} );

		const onClose = jest.fn();
		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ onClose }
			/>
		);

		await waitFor( () =>
			expect( screen.getByLabelText( 'Kit ID' ) ).toBeInTheDocument()
		);

		fireEvent.click( screen.getByText( 'Save changes' ) );

		await waitFor( () =>
			expect( screen.queryByText( 'Roboto' ) ).toBeInTheDocument()
		);
	} );

	it( 'surfaces after_save error notice and KEEPS modal open', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [ { name: 'kit_id', type: 'text', label: 'Kit ID' } ],
			values: { kit_id: 'invalid' },
		} );
		setModuleSettings.mockResolvedValue( {
			values: { kit_id: 'invalid' },
			fields: [],
			notices: [
				{
					type: 'error',
					message: 'Could not load font file.',
				},
			],
		} );

		const onClose = jest.fn();
		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ onClose }
			/>
		);

		await waitFor( () =>
			expect( screen.getByLabelText( 'Kit ID' ) ).toBeInTheDocument()
		);

		fireEvent.click( screen.getByText( 'Save changes' ) );

		await waitFor( () => {
			expect( createNoticeMock ).toHaveBeenCalledWith(
				'error',
				'Could not load font file.',
				expect.objectContaining( { type: 'snackbar' } )
			);
		} );
		// Modal must remain open so user can fix the input.
		expect( onClose ).not.toHaveBeenCalled();
	} );

	it( 'success path closes modal + dispatches success snackbar', async () => {
		getModuleSettings.mockResolvedValue( {
			fields: [ { name: 'kit_id', type: 'text', label: 'Kit ID' } ],
			values: { kit_id: 'abc' },
		} );
		setModuleSettings.mockResolvedValue( {
			values: { kit_id: 'abc' },
			fields: [],
			notices: [],
		} );

		const onClose = jest.fn();
		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="MyModule"
				onClose={ onClose }
			/>
		);

		await waitFor( () =>
			expect( screen.getByLabelText( 'Kit ID' ) ).toBeInTheDocument()
		);

		fireEvent.click( screen.getByText( 'Save changes' ) );

		await waitFor( () => expect( onClose ).toHaveBeenCalled() );
		expect( createNoticeMock ).toHaveBeenCalledWith(
			'success',
			expect.stringContaining( 'MyModule' ),
			expect.objectContaining( { type: 'snackbar' } )
		);
	} );

	it( 'shows loading state then ready state', async () => {
		let resolveLoad;
		getModuleSettings.mockReturnValue(
			new Promise( ( resolve ) => {
				resolveLoad = resolve;
			} )
		);

		render(
			<ModuleSettingsModal
				isOpen={ true }
				moduleKey="X"
				moduleName="X"
				onClose={ jest.fn() }
			/>
		);

		expect( screen.getByText( /Loading/i ) ).toBeInTheDocument();

		resolveLoad( {
			fields: [ { name: 'kit_id', type: 'text', label: 'Kit ID' } ],
			values: { kit_id: 'abc' },
		} );

		await waitFor( () =>
			expect( screen.getByLabelText( 'Kit ID' ) ).toBeInTheDocument()
		);
	} );
} );
