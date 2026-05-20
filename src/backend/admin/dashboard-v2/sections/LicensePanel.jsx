/**
 * License panel for the new dashboard's Settings tab. Specialised
 * renderer for the Customify Pro "Automatic updates" panel — the form
 * runs independently of the page-level SaveBar (no Save button), with
 * dedicated Activate / Deactivate actions that round-trip through
 * Pro's EDD updater via the panel.endpoints map.
 *
 * Panel shape consumed:
 *   {
 *     id, kind: 'license', label, description, nonce,
 *     endpoints: { status, activate, deactivate },
 *     seedValues: { key, status, expires?, customerName?, errorCode? },
 *   }
 *
 * Settings.jsx detects panel.kind === 'license' and renders this
 * component in place of ProModuleSettingsPanel.
 */

import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import { Card, CardBody, CardHeader, Button, Notice, Icon } from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';
import { panelHeadingId } from '@pressmaximum/dashboard-kit';

const NOTICES_STORE = 'core/notices';

const SUCCESS_GLYPH = (
	<span className="customify-dashboard-snackbar__check">
		<Icon icon={ checkIcon } size={ 14 } />
	</span>
);

/**
 * Map EDD status strings → tone for the status pill.
 *
 * EDD returns: 'valid', 'invalid', 'expired', 'inactive',
 * 'disabled', 'site_inactive', 'item_name_mismatch', 'no_activations_left',
 * 'revoked'. We collapse the long tail into 4 visible tones.
 */
function statusTone( status ) {
	if ( 'valid' === status ) {
		return 'active';
	}
	if ( 'expired' === status ) {
		return 'expired';
	}
	if ( ! status || 'inactive' === status || 'site_inactive' === status ) {
		return 'inactive';
	}
	return 'error';
}

function statusLabel( status ) {
	switch ( status ) {
		case 'valid':
			return __( 'Active', 'customify' );
		case 'expired':
			return __( 'Expired', 'customify' );
		case 'invalid':
			return __( 'Invalid', 'customify' );
		case 'inactive':
		case '':
			return __( 'Inactive', 'customify' );
		case 'site_inactive':
			return __( 'Not active on this site', 'customify' );
		case 'no_activations_left':
			return __( 'No activations left', 'customify' );
		case 'disabled':
		case 'revoked':
			return __( 'Disabled', 'customify' );
		case 'item_name_mismatch':
			return __( 'Wrong product key', 'customify' );
		default:
			return status || __( 'Unknown', 'customify' );
	}
}

export default function LicensePanel( { panel } ) {
	const initial = panel?.seedValues || {};
	const [ key, setKey ] = useState( initial.key || '' );
	const [ snapshot, setSnapshot ] = useState( initial );
	const [ busy, setBusy ] = useState( false );
	const [ error, setError ] = useState( null );

	const isActive = 'valid' === snapshot?.status;
	const headingId = panelHeadingId( panel?.id || 'license' );

	const headers = ( method ) => {
		const out = method ? { 'Content-Type': 'application/json' } : {};
		if ( panel?.nonce ) {
			out[ 'X-WP-Nonce' ] = panel.nonce;
		}
		return out;
	};

	useEffect( () => {
		setKey( initial.key || '' );
		setSnapshot( initial );
	}, [ panel?.id ] );

	const handleActivate = async () => {
		const trimmed = ( key || '' ).trim();
		if ( ! trimmed ) {
			setError( new Error( __( 'Please enter your license key.', 'customify' ) ) );
			return;
		}
		setBusy( true );
		setError( null );
		try {
			const res = await fetch( panel.endpoints.activate, {
				method:      'POST',
				credentials: 'same-origin',
				headers:     headers( 'POST' ),
				body:        JSON.stringify( { key: trimmed } ),
			} );
			const data = await res.json();
			if ( ! res.ok ) {
				throw new Error( data?.message || `HTTP ${ res.status }` );
			}
			const next = data.license || {};
			setSnapshot( next );
			setKey( next.key || trimmed );
			dispatch( NOTICES_STORE ).createSuccessNotice(
				'valid' === next.status
					? __( 'License activated.', 'customify' )
					: __( 'Activation returned a non-active status — check the badge below.', 'customify' ),
				{
					type:        'snackbar',
					isDismissible: true,
					icon:         'valid' === next.status ? SUCCESS_GLYPH : undefined,
				},
			);
		} catch ( err ) {
			setError( err );
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message ||
					__( 'License activation failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		} finally {
			setBusy( false );
		}
	};

	const handleDeactivate = async () => {
		setBusy( true );
		setError( null );
		try {
			const res = await fetch( panel.endpoints.deactivate, {
				method:      'POST',
				credentials: 'same-origin',
				headers:     headers( 'POST' ),
				body:        '{}',
			} );
			const data = await res.json();
			if ( ! res.ok ) {
				throw new Error( data?.message || `HTTP ${ res.status }` );
			}
			const next = data.license || {};
			setSnapshot( next );
			setKey( '' );
			dispatch( NOTICES_STORE ).createSuccessNotice(
				__( 'License deactivated.', 'customify' ),
				{
					type:        'snackbar',
					isDismissible: true,
					icon:         SUCCESS_GLYPH,
				},
			);
		} catch ( err ) {
			setError( err );
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message ||
					__( 'Deactivation failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		} finally {
			setBusy( false );
		}
	};

	const tone = statusTone( snapshot.status );
	const label = statusLabel( snapshot.status );

	return (
		<Card
			className="customify-dashboard-settings__panel customify-dashboard-license"
			data-panel-id={ panel.id }
		>
			<CardHeader>
				<h2 id={ headingId }>{ panel.label }</h2>
				<span
					className={ `customify-dashboard-license__status customify-dashboard-license__status--${ tone }` }
				>
					{ label }
				</span>
			</CardHeader>
			<CardBody>
				{ panel.description && (
					<p className="customify-dashboard-settings__description">
						{ panel.description }
					</p>
				) }
				<div className="customify-dashboard-license__form">
					<label
						htmlFor="customify-dashboard-license-key"
						className="customify-dashboard-license__label"
					>
						{ __( 'License key', 'customify' ) }
					</label>
					<input
						id="customify-dashboard-license-key"
						type="text"
						className="customify-dashboard-license__input"
						value={ key }
						onChange={ ( e ) => setKey( e.target.value ) }
						disabled={ busy || isActive }
						placeholder={ __( 'Enter your license key', 'customify' ) }
					/>
				</div>
				{ snapshot.expires && (
					<p className="customify-dashboard-license__meta">
						{ __( 'Expires:', 'customify' ) } <strong>{ snapshot.expires }</strong>
					</p>
				) }
				{ snapshot.customerName && (
					<p className="customify-dashboard-license__meta">
						{ __( 'Customer:', 'customify' ) } <strong>{ snapshot.customerName }</strong>
					</p>
				) }
				{ error && error.message && (
					<Notice
						status="error"
						isDismissible={ false }
						className="customify-dashboard-license__error"
					>
						{ error.message }
					</Notice>
				) }
				<div className="customify-dashboard-license__actions">
					{ isActive ? (
						<Button
							variant="secondary"
							onClick={ handleDeactivate }
							disabled={ busy }
						>
							{ busy
								? __( 'Deactivating…', 'customify' )
								: __( 'Deactivate', 'customify' ) }
						</Button>
					) : (
						<Button
							variant="primary"
							onClick={ handleActivate }
							disabled={ busy || ! key.trim() }
						>
							{ busy
								? __( 'Activating…', 'customify' )
								: __( 'Activate', 'customify' ) }
						</Button>
					) }
				</div>
			</CardBody>
		</Card>
	);
}
