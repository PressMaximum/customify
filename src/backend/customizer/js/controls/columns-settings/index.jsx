/**
 * Column Settings control — React port that keeps the exact pre-React markup.
 *
 * - Outer chrome: `.customify-actions` (edit/reset dashicons) + `.customify-modal-settings`
 *   slide-down panel.
 * - Inside the panel: `.customify-cs__device-note` + `.customify-cs__accordion`
 *   with `.customify-cs__item` heads/bodies.
 * - Direction field: 2-button group (row / column) using `@wordpress/icons`.
 * - Align field: 4-button group (flex-start / flex-center / flex-end /
 *   space-between) using `@wordpress/icons`.
 * - Gap field: jQuery-UI slider (via `customifyField.initSlider`) with em/px unit picker.
 * - Padding field: rendered by the existing `customifyField.addFields` css_ruler pipeline.
 *
 * React owns the data flow; the DOM remains visually identical to the jQuery version.
 *
 * Saved value shape:
 *   { desktop: { <colKey>: { direction, align, gap: { unit, value }, padding: { ... } } }, mobile: { ... } }
 *
 * `direction` is `'row'` or `'column'`. `align` is one of
 * `flex-start | flex-center | flex-end | space-between` and maps to
 * `justify-content` on the main axis defined by `direction`.
 *
 * Bundled into the existing backend/customizer/control webpack entry
 * (imported from src/backend/customizer/js/control.js).
 */

import { render, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Icon,
	arrowRight,
	arrowDown,
	justifyLeft,
	justifyCenter,
	justifyRight,
	justifySpaceBetween,
	chevronDown,
} from '@wordpress/icons';

// ---------------------------------------------------------------------------
// Setting bridge
// ---------------------------------------------------------------------------

function parseRaw( raw ) {
	if ( raw === null || raw === undefined || raw === '' ) return {};
	let parsed = raw;
	if ( typeof raw !== 'object' ) {
		try { parsed = JSON.parse( decodeURIComponent( raw ) ); }
		catch ( _ ) {
			try { parsed = JSON.parse( raw ); }
			catch ( __ ) { return {}; }
		}
	}
	if ( ! parsed || typeof parsed !== 'object' ) return {};
	return parsed;
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
// Sub-controls
// ---------------------------------------------------------------------------

const DIRECTION_OPTIONS = [
	{ value: 'row',    icon: arrowRight, label: 'Row' },
	{ value: 'column', icon: arrowDown,  label: 'Column' },
];

const ALIGN_OPTIONS = [
	{ value: 'flex-start',    icon: justifyLeft,         label: 'Flex start' },
	{ value: 'flex-center',   icon: justifyCenter,       label: 'Flex center' },
	{ value: 'flex-end',      icon: justifyRight,        label: 'Flex end' },
	{ value: 'space-between', icon: justifySpaceBetween, label: 'Space between' },
];

function ButtonGroup( { fieldName, label, options, value, defaultValue, onChange } ) {
	const current = value || defaultValue || options[ 0 ].value;
	return (
		<div className={ `customify--group-field ft--${ fieldName } customify-cs__${ fieldName }-wrap` } data-field-name={ fieldName }>
			<label className="customize-control-title customify-cs__field-label">{ label }</label>
			<div className="customify-cs__btn-group" role="group">
				<input type="hidden" className="customify-cs__btn-value change-by-js" data-name={ fieldName } value={ current } readOnly />
				{ options.map( ( opt ) => {
					const isActive = current === opt.value;
					return (
						<button
							key={ opt.value }
							type="button"
							className={ `customify-cs__btn components-button is-small${ isActive ? ' is-primary' : '' }` }
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
							<Icon icon={ opt.icon } size={ 20 } />
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

	// Step depends on the current unit — em uses fractional 0.1 steps,
	// px uses whole-pixel 1 steps.
	const stepFor = ( unit ) => ( unit === 'px' ? 1 : 0.1 );
	const step    = stepFor( v.unit || 'em' );

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

	// Sync the jQuery-UI slider's step option whenever the unit changes —
	// initSlider only reads data-step on first mount, so we have to call
	// .slider('option', ...) for later updates. The HTML number input's
	// step attribute is React-managed via the `step` prop below.
	useEffect( () => {
		if ( ! wrapRef.current || ! window.jQuery ) return;
		const $slider = window.jQuery( '.customify-input-slider', wrapRef.current );
		if ( ! $slider.length || ! $slider.slider ) return;
		try {
			if ( $slider.hasClass( 'ui-slider' ) ) {
				$slider.slider( 'option', 'step', step );
			}
		} catch ( _ ) {}
	}, [ step ] );

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
						data-step={ step }
						data-max="100"
					/>
					{ /* `change-by-js` so the customify control container's "change"
					     delegate (which calls control.getValue() and pushes an
					     undefined value back into the setting) skips this input. */ }
					<input
						type="number"
						min="0"
						step={ step }
						max="100"
						className="customify--slider-input customify-input change-by-js"
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
				<span className="customify-cs__head-toggle">
					<Icon icon={ chevronDown } size={ 20 } />
				</span>
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

function App( {
	controlId,
	colLayoutSetting,
	columnKeys,
	defaultValue,
	hideDirection,
	hideAlign,
	forcedDirection,
	forcedAlign,
	defaultDirection,
	defaultAlign,
} ) {
	const [ value, setValue ] = useCustomizeSetting( controlId, defaultValue );
	const count    = useColLayoutCount( colLayoutSetting, Math.min( 3, columnKeys.length ) );
	const device   = usePreviewedDevice();
	const [ open, setOpen ] = useState( false );
	const [ remountKey, setRemountKey ] = useState( 0 );
	const rootRef  = useRef( null );

	const activeCols = columnKeys.slice( 0, Math.max( 1, Math.min( columnKeys.length, count ) ) );
	const deviceData = ( value && value[ device ] ) || {};

	// Resolve default direction. Forced overrides everything, otherwise
	// field-level default, otherwise the hardcoded `'row'` (mirrors PHP
	// Customify_Customizer_Auto_CSS::columns_settings()).
	function defaultDirectionFor() {
		if ( forcedDirection ) return forcedDirection;
		if ( defaultDirection ) return defaultDirection;
		return 'row';
	}

	// Resolve default align for a given column index. Forced overrides
	// everything, otherwise field-level default, otherwise the
	// position-based default that mirrors the PHP CSS generator:
	//   first → flex-start, last → flex-end, middle → flex-center
	//   (single active column → flex-start).
	function defaultAlignFor( idx ) {
		if ( forcedAlign ) return forcedAlign;
		if ( defaultAlign ) return defaultAlign;
		if ( activeCols.length === 1 ) return 'flex-start';
		if ( idx === 0 ) return 'flex-start';
		if ( idx === activeCols.length - 1 ) return 'flex-end';
		return 'flex-center';
	}

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
								<strong>{ device === 'mobile' ? __( 'Mobile/Tablet', 'customify' ) : __( 'Desktop', 'customify' ) }</strong>
							</div>
							<div className="customify-cs__accordion">
								{ activeCols.map( ( colKey, idx ) => {
									const colData = deviceData[ colKey ] || {};

									// Resolve the column's effective direction so the
									// Align options can be tailored to it. Mirrors the
									// PHP resolver: forced > saved > field default > 'row'.
									const effectiveDirection =
										forcedDirection
										|| colData.direction
										|| defaultDirection
										|| 'row';

									// Column direction: hide `space-between`. With our
									// CSS model the justify-content goes on `.item--inner`
									// (default flex-direction: row) so space-between only
									// affects items that have ≥2 internal children, which
									// is rare. If the user picked it under row direction
									// then switched to column, coerce the displayed value
									// to fall through to the position-based default.
									const alignOptions = effectiveDirection === 'column'
										? ALIGN_OPTIONS.filter( ( opt ) => opt.value !== 'space-between' )
										: ALIGN_OPTIONS;
									let alignValue = colData.align;
									if ( effectiveDirection === 'column' && alignValue === 'space-between' ) {
										alignValue = '';
									}

									const fields = (
										<>
											{ ! hideDirection && (
												<ButtonGroup
													fieldName="direction"
													label={ __( 'Direction', 'customify' ) }
													options={ DIRECTION_OPTIONS }
													value={ colData.direction }
													defaultValue={ defaultDirectionFor() }
													onChange={ ( v ) => updateColumn( colKey, { direction: v } ) }
												/>
											) }
											{ ! hideAlign && (
												<ButtonGroup
													fieldName="align"
													label={ __( 'Align', 'customify' ) }
													options={ alignOptions }
													value={ alignValue }
													defaultValue={ defaultAlignFor( idx ) }
													onChange={ ( v ) => updateColumn( colKey, { align: v } ) }
												/>
											) }
											<GapField
												value={ colData.gap }
												onChange={ ( v ) => updateColumn( colKey, { gap: v } ) }
											/>
											<PaddingField
												value={ colData.padding }
												onChange={ ( v ) => updateColumn( colKey, { padding: v } ) }
											/>
										</>
									);

									// Off-canvas sidebar (column key `'sidebar'` — a
									// builder-wide convention used only by the
									// header_sidebar_columns_settings field) renders
									// inline without the accordion head. The
									// `customify-cs__item` + `__body` wrapper nesting
									// is preserved so SCSS that styles fields inside
									// the accordion still applies.
									if ( colKey === 'sidebar' ) {
										return (
											<div
												key={ `${ device }-${ remountKey }-${ colKey }` }
												className="customify-cs__item is-open customify-cs__item--single"
												data-col={ colKey }
											>
												<div className="customify-cs__body">
													{ fields }
												</div>
											</div>
										);
									}

									return (
										<AccordionItem
											key={ `${ device }-${ remountKey }-${ colKey }` }
											colKey={ colKey }
											label={ `Column ${ idx + 1 }` }
										>
											{ fields }
										</AccordionItem>
									);
								} ) }
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
	const controlId         = node.dataset.control || '';
	const colLayoutSetting  = node.dataset.colLayout || '';
	const columnKeys        = parseAttr( node.dataset.columnKeys, [ 'left', 'center', 'right', 'col4', 'col5' ] );
	const defaultValue      = parseAttr( node.dataset.default, {} );
	const hideDirection     = node.dataset.hideDirection === '1';
	const hideAlign         = node.dataset.hideAlign === '1';
	const forcedDirection   = node.dataset.forcedDirection || '';
	const forcedAlign       = node.dataset.forcedAlign || '';
	const defaultDirection  = node.dataset.defaultDirection || '';
	const defaultAlign      = node.dataset.defaultAlign || '';
	if ( ! controlId ) return;
	render(
		<App
			controlId={ controlId }
			colLayoutSetting={ colLayoutSetting }
			columnKeys={ columnKeys }
			defaultValue={ defaultValue }
			hideDirection={ hideDirection }
			hideAlign={ hideAlign }
			forcedDirection={ forcedDirection }
			forcedAlign={ forcedAlign }
			defaultDirection={ defaultDirection }
			defaultAlign={ defaultAlign }
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
