/**
 * Generic Pro-module settings panel.
 *
 * Customify Pro registers per-module panels via the
 * `customify.dashboard.settings.panels` JS filter with a
 * `proPanel: true` flag + their own `fields`, `endpoint`,
 * `seedValues`, and (optionally) `nonce`. The new dashboard's
 * Settings tab detects the flag and routes those panels through
 * this component instead of the standard kit pipeline so the
 * panel can read / write Pro's existing per-module storage
 * (`customify_modules[ClassName]`) without going through the
 * theme's `/customify/v1/settings` endpoint.
 *
 * Owns its own form state + Save button; the global SaveBar at
 * the bottom of the Settings tab only reflects theme-side panels.
 */

import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Notice,
	Icon,
} from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';
import {
	SchemaField,
	SaveBar,
	BASE_FIELD_TYPES,
	panelHeadingId,
} from '@pressmaximum/dashboard-kit';

const NOTICES_STORE = 'core/notices';

const SUCCESS_GLYPH = (
	<span className="customify-dashboard-snackbar__check">
		<Icon icon={ checkIcon } size={ 14 } />
	</span>
);

/**
 * One section-level action button (e.g. "Regenerate assets"). Each
 * descriptor in `panel.actions[]` ships its own endpoint + labels; the
 * button owns its own busy state so multiple actions in the same panel
 * stay independent. POSTs use `{}` as the body so Pro REST handlers
 * that read `get_json_params()` always see an array.
 */
function PanelActionButton( { action, nonce } ) {
	const [ busy, setBusy ] = useState( false );

	const handleClick = async () => {
		if ( busy ) {
			return;
		}
		setBusy( true );
		const method = ( action.method || 'POST' ).toUpperCase();
		const headers = method === 'POST' ? { 'Content-Type': 'application/json' } : {};
		if ( nonce ) {
			headers[ 'X-WP-Nonce' ] = nonce;
		}
		try {
			const res = await fetch( action.endpoint, {
				method,
				credentials: 'same-origin',
				headers,
				body: method === 'POST' ? '{}' : undefined,
			} );
			if ( ! res.ok ) {
				let message = `HTTP ${ res.status }`;
				try {
					const data = await res.json();
					if ( data?.message ) {
						message = data.message;
					}
				} catch ( _ ) {
					// Non-JSON error body; fall through with HTTP status.
				}
				throw new Error( message );
			}
			dispatch( NOTICES_STORE ).createSuccessNotice(
				action.successMessage || __( 'Done.', 'customify' ),
				{
					type: 'snackbar',
					isDismissible: true,
					icon: SUCCESS_GLYPH,
				},
			);
		} catch ( err ) {
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message || __( 'Action failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		} finally {
			setBusy( false );
		}
	};

	return (
		<Button
			variant={ action.variant === 'primary' ? 'primary' : 'secondary' }
			onClick={ handleClick }
			disabled={ busy }
		>
			{ busy
				? action.busyLabel || __( 'Working…', 'customify' )
				: action.label }
		</Button>
	);
}

/**
 * @param {object} props
 * @param {object} props.panel    Panel descriptor — { id, label, description?,
 *                                fields[], endpoint, seedValues?, nonce? }.
 * @param {boolean} [props.scrollIntoView] When true the card scrolls itself
 *                                into view on mount (used when the route
 *                                deep-links to this panel via
 *                                #settings/<panelId>).
 */
export default function ProModuleSettingsPanel( { panel, scrollIntoView } ) {
	const initialValues = useMemo(
		() => ( panel?.seedValues ? { ...panel.seedValues } : {} ),
		[ panel ],
	);
	const [ values, setValues ] = useState( initialValues );
	const [ savedValues, setSavedValues ] = useState( initialValues );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const cardRef = useRef( null );

	useEffect( () => {
		if ( scrollIntoView && cardRef.current ) {
			cardRef.current.scrollIntoView( {
				behavior: 'smooth',
				block: 'start',
			} );
		}
	}, [ scrollIntoView ] );

	const isDirty = useMemo(
		() => JSON.stringify( values ) !== JSON.stringify( savedValues ),
		[ values, savedValues ],
	);

	const handleChange = ( fieldId, next ) => {
		setValues( ( prev ) => ( { ...prev, [ fieldId ]: next } ) );
	};

	const handleSave = async () => {
		if ( ! panel?.endpoint || saving ) {
			return;
		}
		setSaving( true );
		setError( null );
		try {
			const headers = { 'Content-Type': 'application/json' };
			if ( panel.nonce ) {
				headers[ 'X-WP-Nonce' ] = panel.nonce;
			}
			const res = await fetch( panel.endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers,
				body: JSON.stringify( values ),
			} );
			if ( ! res.ok ) {
				throw new Error( 'HTTP ' + res.status );
			}
			const data = await res.json();
			const nextValues =
				data && typeof data === 'object' && data.values
					? data.values
					: values;
			setSavedValues( nextValues );
			setValues( nextValues );
			dispatch( NOTICES_STORE ).createSuccessNotice(
				__( 'Settings saved.', 'customify' ),
				{
					type: 'snackbar',
					isDismissible: true,
					icon: SUCCESS_GLYPH,
				},
			);
		} catch ( err ) {
			setError( err );
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message ||
					__( 'Saving settings failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		} finally {
			setSaving( false );
		}
	};

	const handleDiscard = () => {
		// The kit's SaveBar disables this action via
		// `resetDisabledWhenNotDirty` when the form is clean, so a click
		// arriving here always has something to throw away.
		setValues( savedValues );
		setError( null );
		dispatch( NOTICES_STORE ).createSuccessNotice(
			__( 'Changes discarded.', 'customify' ),
			{ type: 'snackbar', isDismissible: true, icon: SUCCESS_GLYPH },
		);
	};

	if ( ! panel ) {
		return null;
	}

	const headingId = panelHeadingId( panel.id );
	const fields = Array.isArray( panel.fields ) ? panel.fields : [];

	return (
		<>
			<Card
				ref={ cardRef }
				className="customify-dashboard-settings__panel customify-dashboard-settings__pro-panel"
				data-panel-id={ panel.id }
			>
				<CardHeader>
					<h2 id={ headingId }>{ panel.label }</h2>
				</CardHeader>
				<CardBody>
					{ panel.description && (
						<p className="customify-dashboard-settings__description">
							{ panel.description }
						</p>
					) }
					<div className="pmdk-schema-form" role="group" aria-labelledby={ headingId }>
						{ fields.map( ( field ) => (
							<SchemaField
								key={ field.id }
								field={ field }
								value={ values[ field.id ] }
								onChange={ ( next ) => handleChange( field.id, next ) }
								fieldTypes={ BASE_FIELD_TYPES }
							/>
						) ) }
					</div>
					{ error && error.message && (
						<Notice
							status="error"
							isDismissible={ false }
							className="customify-dashboard-settings__pro-panel-error"
						>
							{ error.message }
						</Notice>
					) }
					{ Array.isArray( panel.actions ) && panel.actions.length > 0 && (
						<div className="customify-dashboard-settings__panel-actions-inline">
							{ panel.actions.map( ( action ) => (
								<PanelActionButton
									key={ action.id }
									action={ action }
									nonce={ panel.nonce }
								/>
							) ) }
						</div>
					) }
				</CardBody>
			</Card>
			<div className="customify-dashboard-settings__panel-actions">
				<SaveBar
					isDirty={ isDirty }
					isSaving={ saving }
					onSave={ handleSave }
					onReset={ handleDiscard }
					// Pro module storage uses revert-to-last-saved semantics
					// (no factory-defaults endpoint), so Discard should only
					// fire when there's something dirty to throw away.
					resetDisabledWhenNotDirty
					labels={ {
						regionLabel: __( 'Settings actions', 'customify' ),
						saveLabel: __( 'Save changes', 'customify' ),
						savingLabel: __( 'Saving…', 'customify' ),
						// Pro module storage doesn't expose a factory-defaults
						// endpoint today; "Reset" here reverts the form to the
						// last server-confirmed snapshot. Label accordingly so
						// users don't expect a real wipe.
						resetLabel: __( 'Discard changes', 'customify' ),
						// Mirror the kit's neutral default through the
						// `customify` text domain so the string lands in the
						// theme POT for translation.
						statusSaved: __( 'No pending changes', 'customify' ),
						statusDirty: __( 'Unsaved changes', 'customify' ),
						statusSaving: __( 'Saving…', 'customify' ),
					} }
				/>
			</div>
		</>
	);
}
