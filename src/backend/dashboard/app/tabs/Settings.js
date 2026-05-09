/**
 * Settings tab — left sub-nav + active card with rendered fields driven by
 * the schema in app/data/settings-schema.js. Each field maps to a UI primitive
 * (ToggleSwitch, Select, Button) wired to local controlled state.
 *
 * Settings round-trip:
 *   mount → fetchSettings() → seed local state
 *   user edits → mark dirty
 *   Save → saveSettings(state) → status: saving → saved
 *   Reset → state = defaults from PHP (re-fetched after explicit POST defaults
 *           OR client-side reset using the schema). Here we re-fetch so the
 *           server is authoritative.
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import {
	Card,
	SubNav,
	SettingsLayout,
	SettingRow,
	ToggleSwitch,
	Select,
	Button,
	SaveBar,
	Pill,
} from '../../ui';
import {
	SETTINGS_NAV,
	SETTINGS_CARDS,
	DEFAULT_SUB_TAB,
} from '../data/settings-schema';
import {
	fetchSettings,
	saveSettings,
	clearCache,
} from '../api/settings';

function shallowEqual( a, b ) {
	if ( a === b ) return true;
	if ( ! a || ! b ) return false;
	const ak = Object.keys( a );
	const bk = Object.keys( b );
	if ( ak.length !== bk.length ) return false;
	return ak.every( ( k ) => {
		if ( typeof a[ k ] === 'object' ) {
			return shallowEqual( a[ k ], b[ k ] );
		}
		return a[ k ] === b[ k ];
	} );
}

function renderField( field, value, onChange, actionState, runAction ) {
	if ( field.type === 'toggle' ) {
		return (
			<ToggleSwitch
				checked={ !! value }
				onChange={ ( v ) => onChange( field.key, v ) }
				ariaLabel={ field.title }
				disabled={ !! field.disabled }
			/>
		);
	}
	if ( field.type === 'select' ) {
		return (
			<Select
				value={ value || field.options[ 0 ].value }
				onChange={ ( v ) => onChange( field.key, v ) }
				options={ field.options }
				ariaLabel={ field.title }
			/>
		);
	}
	if ( field.type === 'action' ) {
		return (
			<Button
				variant="secondary"
				onClick={ () => runAction( field.action ) }
				disabled={ actionState[ field.action ] === 'running' }
			>
				{ actionState[ field.action ] === 'running'
					? __( 'Working…', 'customify' )
					: field.ctaLabel }
			</Button>
		);
	}
	if ( field.type === 'static' ) {
		return (
			<Pill style={ { fontSize: 13, padding: '6px 12px' } }>
				{ field.static }
			</Pill>
		);
	}
	return null;
}

export default function Settings() {
	const [ subTab, setSubTab ] = useState( DEFAULT_SUB_TAB );
	const [ initial, setInitial ] = useState( null ); // server-side baseline
	const [ state, setState ] = useState( null ); // current edits
	const [ status, setStatus ] = useState( 'idle' ); // idle | saving | saved | error
	const [ actionState, setActionState ] = useState( {} );

	useEffect( () => {
		fetchSettings()
			.then( ( s ) => {
				setInitial( s );
				setState( s );
			} )
			.catch( () => setStatus( 'error' ) );
	}, [] );

	const dirty = state && initial && ! shallowEqual( state, initial );

	const updateField = useCallback(
		( group ) => ( key, value ) => {
			setState( ( prev ) => ( {
				...prev,
				[ group ]: { ...prev[ group ], [ key ]: value },
			} ) );
			setStatus( 'idle' );
		},
		[]
	);

	const onSave = useCallback( () => {
		setStatus( 'saving' );
		saveSettings( state )
			.then( ( saved ) => {
				setInitial( saved );
				setState( saved );
				setStatus( 'saved' );
			} )
			.catch( () => setStatus( 'error' ) );
	}, [ state ] );

	const onReset = useCallback( () => {
		// Send empty body — PHP fills with defaults via sanitize_settings.
		setStatus( 'saving' );
		saveSettings( {} )
			.then( ( saved ) => {
				setInitial( saved );
				setState( saved );
				setStatus( 'saved' );
			} )
			.catch( () => setStatus( 'error' ) );
	}, [] );

	const runAction = useCallback( ( action ) => {
		setActionState( ( s ) => ( { ...s, [ action ]: 'running' } ) );
		const promise =
			action === 'clear-cache'
				? clearCache()
				: Promise.resolve(); // rollback / migration: stub
		promise
			.then( () =>
				setActionState( ( s ) => ( { ...s, [ action ]: 'done' } ) )
			)
			.catch( () =>
				setActionState( ( s ) => ( { ...s, [ action ]: 'error' } ) )
			);
	}, [] );

	if ( ! state ) {
		// Loading — keep layout stable so the page doesn't jump.
		return (
			<SettingsLayout
				nav={
					<SubNav
						items={ SETTINGS_NAV }
						active={ subTab }
						onChange={ setSubTab }
						ariaLabel={ __(
							'Settings sections',
							'customify'
						) }
					/>
				}
			>
				<Card title={ SETTINGS_CARDS[ subTab ].title } bodyPadding>
					<p>{ __( 'Loading…', 'customify' ) }</p>
				</Card>
			</SettingsLayout>
		);
	}

	const card = SETTINGS_CARDS[ subTab ];
	const groupValues = state[ card.group ] || {};
	const onChange = updateField( card.group );

	return (
		<SettingsLayout
			nav={
				<SubNav
					items={ SETTINGS_NAV }
					active={ subTab }
					onChange={ setSubTab }
					ariaLabel={ __( 'Settings sections', 'customify' ) }
				/>
			}
		>
			<Card title={ card.title }>
				{ card.fields.map( ( field, i ) => {
					const description =
						field.disabled && field.disabledHint ? (
							<>
								{ field.description }
								<br />
								<em className="pm-setting-row__warning">
									{ field.disabledHint }
								</em>
							</>
						) : (
							field.description
						);
					return (
						<SettingRow
							key={ field.key || field.action || i }
							title={ field.title }
							description={ description }
							control={ renderField(
								field,
								groupValues[ field.key ],
								onChange,
								actionState,
								runAction
							) }
						/>
					);
				} ) }
				<SaveBar
					dirty={ dirty }
					status={ status }
					onSave={ onSave }
					onReset={ onReset }
				/>
			</Card>
		</SettingsLayout>
	);
}
