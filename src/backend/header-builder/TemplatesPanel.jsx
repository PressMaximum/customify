/**
 * Customify Templates Panel — React port of the legacy Save / Load / Remove
 * template UI for the header & footer builders.
 *
 * Mounts via createPortal into a placeholder rendered by the PHP custom_html
 * control inside the `{builderId}_templates` Customizer section. Initial
 * template list is read from the builder config localized on
 * window.Customify_Layout_Builder.builders.{id}.saved_templates.
 */

import { useState, useEffect, createPortal } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const AJAX_ACTION = 'customify_builder_save_template';

function encodeValue( value ) {
	return encodeURI( JSON.stringify( value ) );
}

/**
 * Best-effort decode of a wp.customize setting value into a plain JS shape.
 *
 * Customify stores most settings as encodeURIComponent(JSON.stringify(value))
 * once the user edits them, but the initial value (loaded from theme_mod) is
 * the raw decoded shape. Try the URL+JSON path first, then plain JSON, then
 * fall back to the raw value (typical for plain string settings like colors).
 */
function decodeSettingValue( raw ) {
	if ( raw === null || raw === undefined ) return raw;
	if ( typeof raw === 'object' ) return raw;
	if ( typeof raw !== 'string' ) return raw;
	try { return JSON.parse( decodeURIComponent( raw ) ); } catch ( _ ) {}
	try { return JSON.parse( raw ); } catch ( _ ) {}
	return raw;
}

function isEmptySetting( v ) {
	if ( v === null || v === undefined || v === '' || v === false ) return true;
	if ( Array.isArray( v ) ) return v.length === 0;
	if ( typeof v === 'object' ) return Object.keys( v ).length === 0;
	return false;
}

function postAjax( params ) {
	const ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
	const body    = new URLSearchParams();
	Object.entries( params ).forEach( ( [ k, v ] ) => body.set( k, v ) );
	return fetch( ajaxUrl, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
		body: body.toString(),
	} ).then( ( r ) => r.json() );
}

export default function TemplatesPanel( { builderId, controlId, mountId, onApplyLayout, layoutSettingKey } ) {
	const [ container, setContainer ] = useState( () => document.getElementById( mountId ) );
	const [ templates, setTemplates ] = useState(
		() => window.Customify_Layout_Builder?.builders?.[ builderId ]?.saved_templates || {}
	);
	const [ name, setName ]     = useState( '' );
	const [ saving, setSaving ] = useState( false );

	useEffect( () => {
		const el = document.getElementById( mountId );
		if ( el ) { setContainer( el ); return; }
		const observer = new MutationObserver( () => {
			const found = document.getElementById( mountId );
			if ( found ) { setContainer( found ); observer.disconnect(); }
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
		return () => observer.disconnect();
	}, [ mountId ] );

	if ( ! container ) return null;

	const nonce = window.Customify_Layout_Builder?.nonce || '';

	const onSave = () => {
		const tplName = name.trim();
		if ( ! tplName || saving ) return;

		// Capture the live wp.customize values for every builder setting — this
		// includes pending edits the user has not yet Published (which would be
		// invisible to a server-side get_theme_mod() call).
		const defaults = window.Customify_Layout_Builder?.builders?.[ builderId ]?.setting_defaults || {};
		const wpc      = window.wp?.customize;
		const payload  = {};
		if ( wpc ) {
			Object.keys( defaults ).forEach( ( key ) => {
				const setting = wpc( key );
				if ( ! setting ) return;
				const value = decodeSettingValue( setting.get?.() );
				if ( isEmptySetting( value ) ) return;
				payload[ key ] = value;
			} );
		}

		setSaving( true );
		postAjax( {
			action:  AJAX_ACTION,
			name:    tplName,
			id:      builderId,
			control: controlId,
			nonce,
			data:    JSON.stringify( payload ),
		} )
			.then( ( res ) => {
				setSaving( false );
				if ( ! res?.success ) return;
				const { key_id: keyId, name: savedName, data } = res.data || {};
				if ( ! keyId ) return;
				setTemplates( ( prev ) => {
					const rest = Object.fromEntries(
						Object.entries( prev ).filter( ( [ k ] ) => k !== keyId )
					);
					return { [ keyId ]: { name: savedName, image: '', data: data || {} }, ...rest };
				} );
				setName( '' );
			} )
			.catch( () => setSaving( false ) );
	};

	const onRemove = ( key ) => {
		const snapshot = templates;
		setTemplates( ( prev ) => {
			const next = { ...prev };
			delete next[ key ];
			return next;
		} );
		postAjax( {
			action: AJAX_ACTION,
			id:     builderId,
			remove: key,
			nonce,
		} )
			.then( ( res ) => {
				if ( ! res?.success ) setTemplates( snapshot );
			} )
			.catch( () => setTemplates( snapshot ) );
	};

	const onLoad = ( tpl ) => {
		if ( ! window.wp?.customize ) return;
		const wpc      = window.wp.customize;
		const data     = tpl?.data || {};
		const defaults = window.Customify_Layout_Builder?.builders?.[ builderId ]?.setting_defaults || {};

		// Loading a template fully replaces the builder state: every known builder
		// setting gets either the template's value (when present) or its default
		// (when absent).
		const allKeys = new Set( [ ...Object.keys( defaults ), ...Object.keys( data ) ] );
		allKeys.forEach( ( key ) => {
			const setting = wpc( key );
			if ( ! setting ) return;
			const hasTemplateValue = Object.prototype.hasOwnProperty.call( data, key );
			const newValue         = hasTemplateValue ? data[ key ] : ( defaults[ key ] ?? '' );
			setting.set( encodeValue( newValue ) );
		} );

		// Belt-and-braces: also push the layout payload into the Builder's React
		// state directly. wp.customize fires bind handlers on .set(), but we rely
		// on a deep-equal check inside that path which can no-op in subtle cases
		// (e.g. layout setting unchanged at the encoded-string level). The direct
		// callback guarantees the visible grid updates immediately.
		if ( typeof onApplyLayout === 'function' && layoutSettingKey ) {
			const layoutValue = Object.prototype.hasOwnProperty.call( data, layoutSettingKey )
				? data[ layoutSettingKey ]
				: ( defaults[ layoutSettingKey ] ?? {} );
			onApplyLayout( layoutValue );
		}
	};

	const entries      = Object.entries( templates );
	const hasTemplates = entries.length > 0;

	return createPortal(
		<>
			<div className="save-template-form">
				<input
					type="text"
					className="template-input-name change-by-js"
					value={ name }
					placeholder={ __( 'Template name', 'customify' ) }
					onChange={ ( e ) => setName( e.target.value ) }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' ) {
							e.preventDefault();
							onSave();
						}
					} }
					disabled={ saving }
				/>
				<button
					type="button"
					className="button button-secondary save-builder-template"
					onClick={ onSave }
					disabled={ saving || ! name.trim() }
				>
					{ saving ? __( 'Saving…', 'customify' ) : __( 'Save', 'customify' ) }
				</button>
			</div>

			<span className="customize-control-title">{ __( 'Saved Templates', 'customify' ) }</span>
			<ul className={ `list-saved-templates list-boxed ${ hasTemplates ? 'has-templates' : 'no-templates' }` }>
				{ entries.map( ( [ key, tpl ] ) => (
					<li key={ key } className="saved_template li-boxed" data-id={ key }>
						{ tpl?.name || __( 'Untitled', 'customify' ) }
						<a
							href="#"
							className="load-tpl"
							onClick={ ( e ) => { e.preventDefault(); onLoad( tpl ); } }
						>
							{ __( 'Load', 'customify' ) }
						</a>
						<a
							href="#"
							className="remove-tpl"
							onClick={ ( e ) => { e.preventDefault(); onRemove( key ); } }
						>
							{ __( 'Remove', 'customify' ) }
						</a>
					</li>
				) ) }
				{ ! hasTemplates && (
					<li className="no_template">{ __( 'No saved templates.', 'customify' ) }</li>
				) }
			</ul>
		</>,
		container
	);
}
