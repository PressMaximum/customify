/**
 * Pro module settings modal — renders the field schema returned by Pro's
 * `Customify_Pro_Module_Base::settings()` and round-trips edits through the
 * `get_module_settings` / `set_module_settings` AJAX tasks.
 *
 * Schema shape (normalized server-side, see Customify::normalize_pro_module_fields):
 *   { type, name, label, desc, content, options? }
 * Supported types: text, select, html (display-only). Other types render as
 * a text input fallback so unknown future field types don't blank out.
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { Modal, Button, Select } from '../../ui';
import {
	getModuleSettings,
	setModuleSettings,
} from '../api/pro-modules';

function FieldControl( { field, value, onChange } ) {
	if ( field.type === 'select' ) {
		const options = field.options || [];
		const fallback = options.length ? options[ 0 ].value : '';
		return (
			<Select
				value={ value != null ? String( value ) : fallback }
				onChange={ onChange }
				options={ options }
				ariaLabel={ field.label }
			/>
		);
	}
	if ( field.type === 'html' ) {
		// Display-only block — Typekit uses this to surface the loaded
		// fonts list. Trust the server-side content (sanitized by Pro
		// before it ever reaches us).
		return (
			<div
				className="pm-module-modal__html"
				// eslint-disable-next-line react/no-danger
				dangerouslySetInnerHTML={ { __html: field.content || '' } }
			/>
		);
	}
	// text + unknown types
	return (
		<input
			type="text"
			className="pm-module-modal__input"
			value={ value != null ? String( value ) : '' }
			onChange={ ( e ) => onChange( e.target.value ) }
			aria-label={ field.label }
		/>
	);
}

/**
 * Render Pro's `desc` string. Pro stores some descs with inline HTML (e.g.
 * Typekit's link to fonts.adobe.com), so let `<a>` tags through but not
 * arbitrary markup.
 */
function FieldDescription( { html } ) {
	if ( ! html ) {
		return null;
	}
	// Pro descriptions are simple — usually plain text or a single anchor.
	// Render via createInterpolateElement when an <a> is present so the
	// link gets target=_blank rel=noreferrer; otherwise plain text.
	const anchorMatch = html.match(
		/<a\s+[^>]*href=["']([^"']+)["'][^>]*>([\s\S]*?)<\/a>/i
	);
	if ( ! anchorMatch ) {
		return <p className="description">{ html }</p>;
	}
	const [ full, href, label ] = anchorMatch;
	const before = html.slice( 0, html.indexOf( full ) );
	const after = html.slice( html.indexOf( full ) + full.length );
	return (
		<p className="description">
			{ before }
			<a href={ href } target="_blank" rel="noreferrer">
				{ label }
			</a>
			{ after }
		</p>
	);
}

export default function ModuleSettingsModal( {
	isOpen,
	onClose,
	moduleKey,
	moduleName,
} ) {
	const { createNotice, removeNotice } = useDispatch( noticesStore );
	const [ status, setStatus ] = useState( 'idle' ); // idle | loading | ready | saving | error
	const [ fields, setFields ] = useState( [] );
	const [ values, setValues ] = useState( {} );

	// Load schema + values whenever the modal opens for a given module.
	useEffect( () => {
		if ( ! isOpen || ! moduleKey ) {
			return undefined;
		}
		let alive = true;
		setStatus( 'loading' );
		getModuleSettings( moduleKey )
			.then( ( res ) => {
				if ( ! alive ) return;
				setFields( Array.isArray( res?.fields ) ? res.fields : [] );
				setValues(
					res?.values && typeof res.values === 'object'
						? res.values
						: {}
				);
				setStatus( 'ready' );
			} )
			.catch( () => {
				if ( alive ) setStatus( 'error' );
			} );
		return () => {
			alive = false;
		};
	}, [ isOpen, moduleKey ] );

	const onFieldChange = useCallback( ( name, next ) => {
		setValues( ( prev ) => ( { ...prev, [ name ]: next } ) );
	}, [] );

	const onSave = useCallback( () => {
		setStatus( 'saving' );
		setModuleSettings( moduleKey, values )
			.then( ( res ) => {
				const noticeId = `pm-modal-${ Date.now() }`;
				createNotice(
					'success',
					sprintf(
						/* translators: %s: module name */
						__( '"%s" settings saved.', 'customify' ),
						moduleName
					),
					{ type: 'snackbar', id: noticeId }
				);
				setTimeout( () => removeNotice( noticeId ), 3000 );
				if ( res?.values && typeof res.values === 'object' ) {
					setValues( res.values );
				}
				setStatus( 'ready' );
				onClose();
			} )
			.catch( () => {
				setStatus( 'error' );
				createNotice(
					'error',
					sprintf(
						/* translators: %s: module name */
						__(
							'Could not save "%s" settings. Please try again.',
							'customify'
						),
						moduleName
					),
					{ type: 'snackbar' }
				);
			} );
	}, [ moduleKey, moduleName, values, createNotice, removeNotice, onClose ] );

	if ( ! isOpen ) {
		return null;
	}

	const title = sprintf(
		/* translators: %s: module name */
		__( '%s Settings', 'customify' ),
		moduleName || ''
	);

	return (
		<Modal isOpen={ isOpen } onClose={ onClose } size="md" ariaLabel={ title }>
			<Modal.Header title={ title } onClose={ onClose } />
			<Modal.Body>
				{ status === 'loading' && (
					<p>{ __( 'Loading settings…', 'customify' ) }</p>
				) }
				{ status === 'error' && (
					<p>
						{ __(
							'Could not load module settings. Make sure the module is enabled, then reopen this dialog.',
							'customify'
						) }
					</p>
				) }
				{ ( status === 'ready' || status === 'saving' ) &&
					fields.map( ( field, i ) => (
						<div className="pm-module-modal__field" key={ i }>
							{ field.label && field.type !== 'html' && (
								<label className="pm-module-modal__label">
									{ field.label }
								</label>
							) }
							<FieldControl
								field={ field }
								value={ values[ field.name ] }
								onChange={ ( next ) =>
									onFieldChange( field.name, next )
								}
							/>
							<FieldDescription html={ field.desc } />
						</div>
					) ) }
				{ status === 'ready' && fields.length === 0 && (
					<p>
						{ __(
							'This module has no editable settings.',
							'customify'
						) }
					</p>
				) }
			</Modal.Body>
			<Modal.Footer align="end">
				<Button variant="secondary" onClick={ onClose }>
					{ __( 'Cancel', 'customify' ) }
				</Button>
				<Button
					variant="primary"
					onClick={ onSave }
					disabled={ status !== 'ready' || fields.length === 0 }
				>
					{ status === 'saving'
						? __( 'Saving…', 'customify' )
						: __( 'Save changes', 'customify' ) }
				</Button>
			</Modal.Footer>
		</Modal>
	);
}
