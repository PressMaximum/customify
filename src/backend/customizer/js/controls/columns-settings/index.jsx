/**
 * Column Settings control — React port that keeps the exact pre-React markup.
 *
 * - Outer chrome: `.customify-actions` (edit/reset dashicons) + `.customify-modal-settings`
 *   slide-down panel.
 * - Inside the panel: `.customify-cs__device-note` + `.customify-cs__accordion`
 *   with `.customify-cs__item` heads/bodies.
 * - Layout field: custom `.customify-cs__btn-group` with inlined `@wordpress/icons` SVGs.
 * - Gap field: jQuery-UI slider (via `customifyField.initSlider`) with em/px unit picker.
 * - Padding field: rendered by the existing `customifyField.addFields` css_ruler pipeline.
 *
 * React owns the data flow; the DOM remains visually identical to the jQuery version.
 *
 * Saved value shape:
 *   { desktop: { <colKey>: { layout, gap: { unit, value }, padding: { ... } } }, mobile: { ... } }
 *
 * Bundled into the existing backend/customizer/control webpack entry
 * (imported from src/backend/customizer/js/control.js).
 */

import { render, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// ---------------------------------------------------------------------------
// Setting bridge
// ---------------------------------------------------------------------------

function parseRaw( raw ) {
	if ( raw === null || raw === undefined || raw === '' ) return {};
	if ( typeof raw === 'object' ) return raw;
	try { return JSON.parse( decodeURIComponent( raw ) ); } catch ( _ ) {}
	try { return JSON.parse( raw ); } catch ( _ ) {}
	return {};
}

function useCustomizeSetting( controlId, defaultValue ) {
	const skipNext = useRef( false );
	const [ value, setLocal ] = useState( () => {
		const setting = wp.customize?.( controlId );
		const raw = setting ? setting.get() : null;
		const parsed = parseRaw( raw );
		return parsed && Object.keys( parsed ).length ? parsed : ( defaultValue || {} );
	} );

	useEffect( () => {
		const setting = wp.customize?.( controlId );
		if ( ! setting || ! setting.bind ) return;
		const handler = ( raw ) => {
			if ( skipNext.current ) { skipNext.current = false; return; }
			setLocal( parseRaw( raw ) );
		};
		setting.bind( handler );
		return () => setting.unbind && setting.unbind( handler );
	}, [ controlId ] );

	function setValue( next ) {
		setLocal( ( prev ) => {
			const resolved = typeof next === 'function' ? next( prev ) : next;
			const setting  = wp.customize?.( controlId );
			if ( ! setting ) return resolved;

			// Suppress the customify framework's refreshFromSetting() — without
			// this flag, the framework's setting.bind handler treats our update
			// as an external change and empties .customify--settings-fields,
			// which would destroy this React mount node and reset our `open`
			// state. The framework's own getValue() sets the same flag.
			const control = wp.customize?.control?.( controlId );
			skipNext.current = true;
			if ( control ) control._customifyWriting = true;
			try {
				setting.set( encodeURIComponent( JSON.stringify( resolved ) ) );
			} catch ( _ ) {} finally {
				if ( control ) control._customifyWriting = false;
			}
			return resolved;
		} );
	}

	return [ value, setValue ];
}

function useColLayoutCount( settingId, fallback ) {
	function read() {
		if ( ! settingId ) return fallback;
		const setting = wp.customize?.( settingId );
		if ( ! setting ) return fallback;
		const data = parseRaw( setting.get() );
		if ( data && data.count ) {
			const n = parseInt( data.count, 10 );
			if ( ! isNaN( n ) && n >= 1 ) return n;
		}
		return fallback;
	}
	const [ count, setCount ] = useState( read );
	useEffect( () => {
		if ( ! settingId ) return;
		const setting = wp.customize?.( settingId );
		if ( ! setting || ! setting.bind ) return;
		const handler = () => setCount( read() );
		setting.bind( handler );
		return () => setting.unbind && setting.unbind( handler );
	}, [ settingId ] );
	return count;
}

function usePreviewedDevice() {
	function normalize( d ) { return d === 'desktop' ? 'desktop' : 'mobile'; }
	const [ device, setDevice ] = useState( () => {
		try { return normalize( wp.customize?.previewedDevice?.get?.() || 'desktop' ); }
		catch ( _ ) { return 'desktop'; }
	} );
	useEffect( () => {
		const pd = wp.customize?.previewedDevice;
		if ( ! pd || ! pd.bind ) return;
		const handler = ( d ) => setDevice( normalize( d ) );
		pd.bind( handler );
		return () => pd.unbind && pd.unbind( handler );
	}, [] );
	return device;
}

// ---------------------------------------------------------------------------
// Inline SVG icons (same paths copied from @wordpress/icons CJS build)
// ---------------------------------------------------------------------------

const LAYOUT_OPTIONS = [
	{ value: 'flex-start',  label: 'Flex start',  svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M9 9v6h11V9H9zM4 20h1.5V4H4v16z"/></svg>' },
	{ value: 'flex-center', label: 'Flex center', svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M12.5 15v5H11v-5H4V9h7V4h1.5v5h7v6h-7Z"/></svg>' },
	{ value: 'flex-end',    label: 'Flex end',    svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M4 15h11V9H4v6zM18.5 4v16H20V4h-1.5z"/></svg>' },
	{ value: 'stack',       label: 'Stack',       svg: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M17.5 4v5a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2V4H8v5a.5.5 0 0 0 .5.5h7A.5.5 0 0 0 16 9V4h1.5Zm0 16v-5a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v5H8v-5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v5h1.5Z"/></svg>' },
];

const CHEVRON_DOWN_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"/></svg>';

// ---------------------------------------------------------------------------
// Sub-controls
// ---------------------------------------------------------------------------

function LayoutButtons( { value, onChange } ) {
	const current = value || 'flex-start';
	return (
		<div className="customify--group-field ft--layout customify-cs__layout-wrap" data-field-name="layout">
			<label className="customize-control-title customify-cs__field-label">{ __( 'Layout', 'customify' ) }</label>
			<div className="customify-cs__btn-group" role="group">
				<input type="hidden" className="customify-cs__layout-value" data-name="layout" value={ current } readOnly />
				{ LAYOUT_OPTIONS.map( ( opt ) => {
					const isActive = current === opt.value;
					return (
						<button
							key={ opt.value }
							type="button"
							className={ `customify-cs__layout-btn components-button is-small${ isActive ? ' is-pressed is-primary' : '' }` }
							data-value={ opt.value }
							aria-label={ __( opt.label, 'customify' ) }
							title={ __( opt.label, 'customify' ) }
							onMouseDown={ ( e ) => e.preventDefault() }
							onClick={ ( e ) => {
								e.preventDefault();
								e.stopPropagation();
								onChange( opt.value );
							} }
						>
							<span
								className="customify-cs__layout-btn-icon"
								dangerouslySetInnerHTML={ { __html: opt.svg } }
							/>
						</button>
					);
				} ) }
			</div>
		</div>
	);
}

function GapField( { value, onChange } ) {
	const wrapRef = useRef( null );
	const v       = value && typeof value === 'object' ? value : { unit: 'em', value: 1 };
	const uid     = useMemo( () => `gap-${ Date.now() }-${ Math.floor( Math.random() * 1000 ) }`, [] );
	const def     = useMemo( () => ( { unit: 'em', value: 1 } ), [] );

	useEffect( () => {
		if ( ! wrapRef.current || ! window.customifyField || ! window.jQuery ) return;
		const $wrap = window.jQuery( wrapRef.current );
		window.customifyField.initSlider( $wrap );

		const fieldDef = {
			name:    'gap',
			type:    'slider',
			default: def,
			min:     0,
			max:     100,
			step:    1,
		};
		const $group = $wrap.closest( '.customify--group-field' );

		const handler = () => {
			const next = window.customifyField.getValue( fieldDef, $group );
			onChange( next );
		};

		$wrap.on( 'change.cs data-change.cs', 'input, select, textarea', handler );
		return () => { $wrap.off( 'change.cs data-change.cs' ); };
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	return (
		<div className="customify--group-field ft--slider" data-field-name="gap">
			<div className="customify-field-header customify-field-heading">
				<label className="customize-control-title">{ __( 'Gap', 'customify' ) }</label>
			</div>
			<div className="customify-field-settings-inner">
				<div ref={ wrapRef } className="customify-input-slider-wrapper">
					<div
						className="customify-input-slider"
						data-min="0"
						data-default={ JSON.stringify( def ) }
						data-step="1"
						data-max="100"
					/>
					<input
						type="number"
						min="0"
						step="1"
						max="100"
						className="customify--slider-input customify-input"
						data-name="gap-value"
						defaultValue={ v.value ?? '' }
						size={ 4 }
					/>
					<div className="customify--css-unit">
						<label className={ v.unit === 'em' ? 'customify--label-active' : '' }>
							em
							<input
								type="radio"
								className="customify-input customify--label-parent change-by-js"
								data-name="gap-unit"
								name={ `r${ uid }` }
								value="em"
								defaultChecked={ v.unit === 'em' }
							/>
						</label>
						<label className={ v.unit === 'px' ? 'customify--label-active' : '' }>
							px
							<input
								type="radio"
								className="customify-input customify--label-parent change-by-js"
								data-name="gap-unit"
								name={ `r${ uid }` }
								value="px"
								defaultChecked={ v.unit === 'px' }
							/>
						</label>
						<a href="#" className="reset" title={ __( 'Reset', 'customify' ) } onClick={ ( e ) => e.preventDefault() } />
					</div>
				</div>
			</div>
		</div>
	);
}

function PaddingField( { value, onChange } ) {
	const ref = useRef( null );

	useEffect( () => {
		if ( ! ref.current || ! window.customifyField || ! window.jQuery ) return;
		const $el = window.jQuery( ref.current );

		const fieldDef = {
			name:    'padding',
			type:    'css_ruler',
			default: { unit: 'em', top: '', right: '', bottom: '', left: '', link: 1 },
			label:   __( 'Padding', 'customify' ),
		};

		window.customifyField.devices = [ 'desktop' ];
		window.customifyField.addFields(
			[ fieldDef ],
			{ padding: value || {} },
			$el,
			() => {
				const next = window.customifyField.getValue(
					fieldDef,
					$el.find( '.customify--group-field[data-field-name="padding"]' )
				);
				onChange( next );
			}
		);

		return () => { $el.empty(); };
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	return <div ref={ ref } />;
}

// ---------------------------------------------------------------------------
// Accordion item
// ---------------------------------------------------------------------------

function AccordionItem( { colKey, label, children } ) {
	const [ open, setOpen ] = useState( false );
	return (
		<div className={ `customify-cs__item${ open ? ' is-open' : '' }` } data-col={ colKey }>
			<div
				className="customify-cs__head"
				role="button"
				tabIndex={ 0 }
				onClick={ () => setOpen( ( v ) => ! v ) }
				onKeyDown={ ( e ) => {
					if ( e.key === 'Enter' || e.key === ' ' ) {
						e.preventDefault();
						setOpen( ( v ) => ! v );
					}
				} }
			>
				<span className="customify-cs__head-label">{ label }</span>
				<span
					className="customify-cs__head-toggle"
					dangerouslySetInnerHTML={ { __html: CHEVRON_DOWN_SVG } }
				/>
			</div>
			<div className="customify-cs__body">
				{ children }
			</div>
		</div>
	);
}

// ---------------------------------------------------------------------------
// App
// ---------------------------------------------------------------------------

function App( { controlId, colLayoutSetting, columnKeys, defaultValue, hideLayout } ) {
	const [ value, setValue ] = useCustomizeSetting( controlId, defaultValue );
	const count    = useColLayoutCount( colLayoutSetting, Math.min( 3, columnKeys.length ) );
	const device   = usePreviewedDevice();
	const [ open, setOpen ] = useState( false );
	const [ remountKey, setRemountKey ] = useState( 0 );
	const rootRef  = useRef( null );

	const activeCols = columnKeys.slice( 0, Math.max( 1, Math.min( columnKeys.length, count ) ) );
	const deviceData = ( value && value[ device ] ) || {};

	function updateColumn( colKey, partial ) {
		setValue( ( prev ) => {
			const next = { ...( prev || {} ) };
			const cur  = ( next[ device ] && typeof next[ device ] === 'object' ) ? next[ device ] : {};
			next[ device ] = { ...cur, [ colKey ]: { ...( cur[ colKey ] || {} ), ...partial } };
			return next;
		} );
	}

	function reset( e ) {
		e.preventDefault();
		setValue( defaultValue || {} );
		setRemountKey( ( k ) => k + 1 );
	}

	// Close only when the press STARTED outside the wrapper.
	//
	// Record the press origin on `pointerdown` (the very first event of any
	// click/tap, before any React state update or jQuery re-render). The
	// later `click` event then just reads that flag — so even if the click
	// target gets replaced/detached by a setting change in between, the
	// "outside?" decision is the one we made at press time, not based on
	// the (now possibly stale) click target.
	useEffect( () => {
		if ( ! open ) return;
		let startedInside = false;

		function pointerDown( e ) {
			const root = rootRef.current;
			startedInside = !! ( root && e.target && root.contains( e.target ) );
		}

		function maybeClose( e ) {
			if ( startedInside ) return;
			const t = e.target;
			// Be defensive: if for some reason pointerDown didn't fire (e.g.
			// keyboard activation), fall back to checking via closest()
			// against the live DOM.
			if ( t && t.closest && t.closest( '.customify-cs' ) === rootRef.current ) return;
			if ( t && t.closest && (
				t.closest( '.wp-picker-container' ) ||
				t.closest( '.select2-container' ) ||
				t.closest( '.iris-picker' ) ||
				t.closest( '.components-popover' ) ||
				t.closest( '.ui-slider-handle' )
			) ) return;
			setOpen( false );
		}

		document.addEventListener( 'pointerdown', pointerDown, true );
		document.addEventListener( 'click', maybeClose );
		return () => {
			document.removeEventListener( 'pointerdown', pointerDown, true );
			document.removeEventListener( 'click', maybeClose );
		};
	}, [ open ] );

	// Add `modal--opening` to the outer wrapper that owns the `.customify-actions`
	// absolute positioning context so the existing SCSS chevron/X icon swap fires.
	useEffect( () => {
		const root = rootRef.current;
		if ( ! root ) return;
		const wrapper = root.closest( '.customify--settings-wrapper' );
		if ( ! wrapper ) return;
		wrapper.classList.toggle( 'modal--opening', open );
		return () => wrapper.classList.remove( 'modal--opening' );
	}, [ open ] );

	return (
		<div className={ `customify-cs${ open ? ' modal--opening' : '' }` } ref={ rootRef }>
			<div className="customify-actions">
				<a
					href="#"
					className="action--reset"
					style={ { display: open ? 'inline-block' : 'none' } }
					title={ __( 'Reset to default', 'customify' ) }
					onClick={ reset }
				>
					<span className="dashicons dashicons-image-rotate" />
				</a>
				<a
					href="#"
					className="action--edit"
					title={ __( 'Toggle edit panel', 'customify' ) }
					onClick={ ( e ) => { e.preventDefault(); setOpen( ( v ) => ! v ); } }
				>
					<span className="dashicons dashicons-edit" />
				</a>
			</div>

			{ open && (
				<div className="customify-modal-settings" key={ `${ device }-${ remountKey }` }>
					<div className="customify-modal-settings--inner">
						<div className="customify-modal-settings--fields">
							<div className="customify-cs__device-note">
								{ __( 'Editing for: ', 'customify' ) }
								<strong>{ device === 'mobile' ? __( 'Mobile', 'customify' ) : __( 'Desktop', 'customify' ) }</strong>
							</div>
							<div className="customify-cs__accordion">
								{ activeCols.map( ( colKey, idx ) => (
									<AccordionItem
										key={ `${ device }-${ remountKey }-${ colKey }` }
										colKey={ colKey }
										label={ `Column ${ idx + 1 }` }
									>
										{ ! hideLayout && (
											<LayoutButtons
												value={ ( deviceData[ colKey ] || {} ).layout }
												onChange={ ( v ) => updateColumn( colKey, { layout: v } ) }
											/>
										) }
										<GapField
											value={ ( deviceData[ colKey ] || {} ).gap }
											onChange={ ( v ) => updateColumn( colKey, { gap: v } ) }
										/>
										<PaddingField
											value={ ( deviceData[ colKey ] || {} ).padding }
											onChange={ ( v ) => updateColumn( colKey, { padding: v } ) }
										/>
									</AccordionItem>
								) ) }
							</div>
						</div>
					</div>
				</div>
			) }
		</div>
	);
}

// ---------------------------------------------------------------------------
// Mount
// ---------------------------------------------------------------------------

function parseAttr( raw, fallback ) {
	if ( ! raw ) return fallback;
	try { return JSON.parse( raw ); } catch ( _ ) {}
	return fallback;
}

function mountOne( node ) {
	if ( node.dataset.csMounted === '1' ) return;
	node.dataset.csMounted = '1';
	const controlId        = node.dataset.control || '';
	const colLayoutSetting = node.dataset.colLayout || '';
	const columnKeys       = parseAttr( node.dataset.columnKeys, [ 'left', 'center', 'right', 'col4', 'col5' ] );
	const defaultValue     = parseAttr( node.dataset.default, {} );
	const hideLayout       = node.dataset.hideLayout === '1';
	if ( ! controlId ) return;
	render(
		<App
			controlId={ controlId }
			colLayoutSetting={ colLayoutSetting }
			columnKeys={ columnKeys }
			defaultValue={ defaultValue }
			hideLayout={ hideLayout }
		/>,
		node
	);
}

export function mountColumnsSettings() {
	document.querySelectorAll( '.customify-columns-settings-mount' ).forEach( mountOne );
}

export function observeAndMount() {
	mountColumnsSettings();
	const target = document.querySelector( '#customize-theme-controls' );
	if ( ! target || typeof MutationObserver === 'undefined' ) return;
	new MutationObserver( () => mountColumnsSettings() ).observe( target, { childList: true, subtree: true } );
}
