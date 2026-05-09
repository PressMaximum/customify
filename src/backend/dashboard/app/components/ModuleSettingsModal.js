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

import { Modal, Button, Select, ToggleSwitch } from '../../ui';
import { getModuleSettings, setModuleSettings } from '../api/pro-modules';

function FieldControl( { field, value, onChange } ) {
	const type = field.type || 'text';

	// Display-only types render their content/title and have no input.
	if ( type === 'html' ) {
		return (
			<div
				className="pm-module-modal__html"
				dangerouslySetInnerHTML={ { __html: field.content || '' } }
			/>
		);
	}
	if ( type === 'heading' || type === 'section' || type === 'panel' ) {
		return (
			<div className="pm-module-modal__heading">
				{ field.label || field.content || '' }
			</div>
		);
	}

	if ( type === 'select' ) {
		const options = field.options || [];
		const fallback = options.length ? options[ 0 ].value : '';
		return (
			<Select
				value={
					value !== null && value !== undefined
						? String( value )
						: fallback
				}
				onChange={ onChange }
				options={ options }
				ariaLabel={ field.label }
			/>
		);
	}

	if (
		type === 'radio_group' ||
		type === 'text_align' ||
		type === 'text_align_no_justify'
	) {
		const options = field.options || [];
		const current =
			value !== null && value !== undefined ? String( value ) : '';
		return (
			<div className="pm-module-modal__radio-group" role="radiogroup">
				{ options.map( ( opt ) => (
					<label
						key={ opt.value }
						className={
							'pm-module-modal__radio' +
							( current === String( opt.value )
								? ' is-active'
								: '' )
						}
					>
						<input
							type="radio"
							name={ field.name }
							value={ opt.value }
							checked={ current === String( opt.value ) }
							onChange={ () => onChange( opt.value ) }
						/>
						<span>{ opt.label }</span>
					</label>
				) ) }
			</div>
		);
	}

	if ( type === 'image_select' ) {
		const options = field.options || [];
		const current =
			value !== null && value !== undefined ? String( value ) : '';
		return (
			<div className="pm-module-modal__image-select">
				{ options.map( ( opt ) => (
					<button
						type="button"
						key={ opt.value }
						className={
							'pm-module-modal__image-option' +
							( current === String( opt.value )
								? ' is-active'
								: '' )
						}
						onClick={ () => onChange( opt.value ) }
						aria-pressed={ current === String( opt.value ) }
						aria-label={ opt.label }
					>
						{ opt.image && (
							<img src={ opt.image } alt={ opt.label } />
						) }
						<span>{ opt.label }</span>
					</button>
				) ) }
			</div>
		);
	}

	if ( type === 'checkbox' ) {
		const checked =
			value === true ||
			value === 1 ||
			value === '1' ||
			value === 'on' ||
			value === 'true';
		return (
			<div className="pm-module-modal__toggle">
				<ToggleSwitch
					checked={ checked }
					onChange={ ( next ) => onChange( next ? 1 : 0 ) }
					ariaLabel={ field.label }
				/>
				{ field.checkbox_label && (
					<span className="pm-module-modal__toggle-label">
						{ field.checkbox_label }
					</span>
				) }
			</div>
		);
	}

	if ( type === 'number' || type === 'slider' ) {
		const min = field.min !== undefined ? Number( field.min ) : undefined;
		const max = field.max !== undefined ? Number( field.max ) : undefined;
		const step = field.step !== undefined ? Number( field.step ) : 1;
		return (
			<input
				type="number"
				className="pm-module-modal__input"
				value={
					value !== null && value !== undefined && value !== ''
						? Number( value )
						: ''
				}
				min={ min }
				max={ max }
				step={ step }
				placeholder={ field.placeholder }
				onChange={ ( e ) => onChange( e.target.value ) }
				aria-label={ field.label }
			/>
		);
	}

	if ( type === 'color' ) {
		const current =
			typeof value === 'string' && value !== '' ? value : '#000000';
		return (
			<div className="pm-module-modal__color">
				<input
					type="color"
					value={ current.startsWith( '#' ) ? current : '#000000' }
					onChange={ ( e ) => onChange( e.target.value ) }
					aria-label={ field.label }
				/>
				<input
					type="text"
					className="pm-module-modal__input"
					value={ typeof value === 'string' ? value : '' }
					placeholder="#rrggbb / rgba(...)"
					onChange={ ( e ) => onChange( e.target.value ) }
				/>
			</div>
		);
	}

	if (
		type === 'textarea' ||
		type === 'custom_html' ||
		type === 'text/html'
	) {
		return (
			<textarea
				className="pm-module-modal__textarea"
				rows={ field.rows ? Number( field.rows ) : 5 }
				value={
					value !== null && value !== undefined ? String( value ) : ''
				}
				placeholder={ field.placeholder }
				onChange={ ( e ) => onChange( e.target.value ) }
				aria-label={ field.label }
			/>
		);
	}

	if ( type === 'email' ) {
		return (
			<input
				type="email"
				className="pm-module-modal__input"
				value={
					value !== null && value !== undefined ? String( value ) : ''
				}
				placeholder={ field.placeholder }
				onChange={ ( e ) => onChange( e.target.value ) }
				aria-label={ field.label }
			/>
		);
	}

	if ( type === 'hidden' ) {
		return (
			<input
				type="hidden"
				value={
					value !== null && value !== undefined ? String( value ) : ''
				}
				readOnly
			/>
		);
	}

	// `text` and any unknown future type fall through to a text input so
	// new schema additions don't blank out the row before the modal learns
	// how to render them.
	return (
		<input
			type="text"
			className="pm-module-modal__input"
			value={
				value !== null && value !== undefined ? String( value ) : ''
			}
			placeholder={ field.placeholder }
			onChange={ ( e ) => onChange( e.target.value ) }
			aria-label={ field.label }
		/>
	);
}

/**
 * Render Pro's `desc` string. Pro stores some descs with inline HTML (e.g.
 * Typekit's link to fonts.adobe.com), so let `<a>` tags through but not
 * arbitrary markup.
 * @param root0
 * @param root0.html
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
				if ( ! alive ) {
					return;
				}
				setFields( Array.isArray( res?.fields ) ? res.fields : [] );
				setValues(
					res?.values && typeof res.values === 'object'
						? res.values
						: {}
				);
				setStatus( 'ready' );
			} )
			.catch( () => {
				if ( alive ) {
					setStatus( 'error' );
				}
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
				// Pro modules can return server-rendered notices from
				// after_save() (e.g. Typekit's "Could not load font file"
				// when the kit_id is wrong). Surface each one as a snackbar
				// and keep the modal open if any are errors so the user
				// has a chance to fix the input.
				const notices = Array.isArray( res?.notices )
					? res.notices
					: [];
				let hasError = false;
				notices.forEach( ( n ) => {
					if ( ! n || ! n.message ) {
						return;
					}
					const type = [
						'success',
						'error',
						'warning',
						'info',
					].includes( n.type )
						? n.type
						: 'info';
					if ( type === 'error' ) {
						hasError = true;
					}
					const id = `pm-modal-after-save-${ Date.now() }-${ Math.random() }`;
					createNotice( type, n.message, {
						type: 'snackbar',
						id,
					} );
					setTimeout( () => removeNotice( id ), 4000 );
				} );

				if ( ! hasError ) {
					const okId = `pm-modal-${ Date.now() }`;
					createNotice(
						'success',
						sprintf(
							/* translators: %s: module name */
							__( '"%s" settings saved.', 'customify' ),
							moduleName
						),
						{ type: 'snackbar', id: okId }
					);
					setTimeout( () => removeNotice( okId ), 3000 );
				}

				if ( res?.values && typeof res.values === 'object' ) {
					setValues( res.values );
				}
				// Pro modules with dynamic schemas (Typekit's loaded fonts
				// `html` field) re-derive their field list inside settings()
				// after each save. Refresh the schema if the server sent one.
				if ( Array.isArray( res?.fields ) && res.fields.length ) {
					setFields( res.fields );
				}
				setStatus( 'ready' );
				if ( ! hasError ) {
					onClose();
				}
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
		<Modal
			isOpen={ isOpen }
			onClose={ onClose }
			size="md"
			ariaLabel={ title }
		>
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
					fields.map( ( field, i ) => {
						const isDisplay = [
							'html',
							'heading',
							'section',
							'panel',
							'hidden',
						].includes( field.type );
						return (
							<div className="pm-module-modal__field" key={ i }>
								{ field.label && ! isDisplay && (
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
						);
					} ) }
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
