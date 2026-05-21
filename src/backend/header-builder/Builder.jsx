/**
 * Customify Layout Builder — generic React component tree (header & footer).
 *
 * Data flow:
 *   wp.customize Setting  ←read/write→  React state (normalizeData shape)
 *
 * JSON schema (stored in the builder's react_control_id setting):
 * {
 *   desktop: { <row>: { left:[{id}], center:[{id}], right:[{id}] }, … },
 *   mobile:  { <row>: {…}, sidebar: { sidebar:[{id}] } }   // header only
 * }
 * The setting value is stored as encodeURIComponent(JSON.stringify(data)).
 */

import { useState, useEffect, useRef, useCallback, createPortal } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, Popover } from '@wordpress/components';
import { dragHandle, settings, close, plus } from '@wordpress/icons';
import TemplatesPanel from './TemplatesPanel';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const COLS     = [ 'left', 'center', 'right' ];
const ALL_COLS = [ 'left', 'center', 'right', 'col4', 'col5' ];

// ---------------------------------------------------------------------------
// wp.customize bridge
// ---------------------------------------------------------------------------

function parseValue( raw ) {
	if ( ! raw ) return {};
	if ( typeof raw === 'object' ) return raw;
	try { return JSON.parse( decodeURIComponent( raw ) ); } catch ( _ ) {}
	try { return JSON.parse( raw ); } catch ( _ ) {}
	return {};
}

function readSetting( controlId ) {
	try {
		const setting = wp.customize( controlId );
		return setting ? parseValue( setting.get() ) : {};
	} catch ( _ ) {
		return {};
	}
}

function writeSetting( data, controlId ) {
	try {
		const setting = wp.customize( controlId );
		if ( setting ) {
			setting.set( encodeURIComponent( JSON.stringify( data ) ) );
		}
	} catch ( _ ) {}
}

// ---------------------------------------------------------------------------
// Data helpers
// ---------------------------------------------------------------------------

// Strip a raw items collection down to entries shaped like `{id: <non-empty string>}`.
// Mirrors `Customify_Layout_Builder_Frontend_V2::normalize_layout_items()` so the
// JS and PHP renderers can never disagree on what counts as a valid layout entry.
function normalizeItems( raw ) {
	if ( ! Array.isArray( raw ) ) return [];
	const out = [];
	for ( const item of raw ) {
		if ( item && typeof item === 'object' && typeof item.id === 'string' && item.id ) {
			out.push( { id: item.id } );
		}
	}
	return out;
}

function normalizeData( raw, deviceIds, rows, hasSidebar ) {
	const data = {};
	const safe = ( raw && typeof raw === 'object' && ! Array.isArray( raw ) ) ? raw : {};
	for ( const dev of deviceIds ) {
		data[ dev ] = {};
		const devData = ( safe[ dev ] && typeof safe[ dev ] === 'object' ) ? safe[ dev ] : {};
		for ( const row of rows ) {
			data[ dev ][ row ] = {};
			const rowData = ( devData[ row ] && typeof devData[ row ] === 'object' ) ? devData[ row ] : {};
			for ( const col of ALL_COLS ) {
				data[ dev ][ row ][ col ] = normalizeItems( rowData[ col ] );
			}
		}
	}
	if ( hasSidebar ) {
		data.mobile = data.mobile || {};
		const sidebar = safe?.mobile?.sidebar;
		const sidebarItems = ( sidebar && typeof sidebar === 'object' ) ? sidebar.sidebar : null;
		data.mobile.sidebar = { sidebar: normalizeItems( sidebarItems ) };
	}
	return data;
}

function getAllPlacedIds( data ) {
	const ids = new Set();
	for ( const dev of Object.keys( data ) ) {
		for ( const row of Object.keys( data[ dev ] ) ) {
			for ( const col of Object.keys( data[ dev ][ row ] ) ) {
				for ( const item of ( data[ dev ][ row ][ col ] || [] ) ) {
					ids.add( item.id );
				}
			}
		}
	}
	return ids;
}

function getDevicePlacedIds( data, device ) {
	const ids = new Set();
	for ( const row of Object.keys( data[ device ] || {} ) ) {
		for ( const col of Object.keys( data[ device ][ row ] || {} ) ) {
			for ( const item of ( data[ device ][ row ][ col ] || [] ) ) {
				ids.add( item.id );
			}
		}
	}
	return ids;
}

function permanentlyHideSection( section ) {
	if ( ! section ) return;
	section.active.set( false );
	// Store the handler on the section object so openSection can unbind it by reference.
	if ( ! section._customifyForceHide ) {
		section._customifyForceHide = function( active ) {
			if ( active ) section.active.set( false );
		};
	}
	section.active.bind( section._customifyForceHide );
}

function hideAllBuilderSections( allItems, infraSections, alwaysVisibleSections ) {
	if ( ! wp?.customize?.section ) return;
	for ( const id of infraSections ) {
		permanentlyHideSection( wp.customize.section( id ) );
	}
	for ( const item of Object.values( allItems ) ) {
		// Hide the dedicated layout section (margin/padding/etc.) if present.
		if ( item.layout_section ) {
			permanentlyHideSection( wp.customize.section( item.layout_section ) );
		}
		if ( ! item.section || alwaysVisibleSections.has( item.section ) ) continue;
		permanentlyHideSection( wp.customize.section( item.section ) );
	}
}

// ---------------------------------------------------------------------------
// Builder (root)
// ---------------------------------------------------------------------------

export default function Builder( { config } ) {
	const builderId    = config?.id     || 'header';
	const panelId      = config?.panel  || 'header_settings';
	const controlId    = config?.react_control_id || config?.control_id || 'header_builder_panel_v2';
	const allItems       = config?.items  || {};
	const rowLabels      = config?.rows   || {};
	const rowLayoutKeys  = config?.row_layout_keys || {};
	const deviceMap    = config?.devices || { desktop: __( 'Desktop', 'customify' ), mobile: __( 'Mobile / Tablet', 'customify' ) };
	const deviceIds    = Object.keys( deviceMap );
	const hasMobile    = deviceIds.includes( 'mobile' );
	const hasSidebar   = hasMobile && Object.keys( rowLabels ).includes( 'sidebar' );
	const rows         = Object.keys( rowLabels ).filter( ( r ) => r !== 'sidebar' );
	const DEVICES_LIST = deviceIds.map( ( id ) => ( { id, label: deviceMap[ id ] } ) );
	const panelItemsContainerId = config?.panel_items_container || ( `customify-${ builderId.charAt( 0 ) }b-panel-items` );
	const builderTitle         = config?.title || builderId;

	// Sections that belong to the builder infrastructure — always hidden.
	const infraSections = new Set(
		[ config?.section, ...Object.keys( rowLabels ).map( ( r ) => `${ builderId }_${ r }` ) ]
			.filter( Boolean )
	);
	// Sections that are always visible in WP Customizer (e.g. templates panel).
	const alwaysVisibleSections = new Set( [ `${ builderId }_templates` ] );

	const initialData = normalizeData( readSetting( controlId ), deviceIds, rows, hasSidebar );

	const [ panelExpanded, setPanelExpanded ] = useState( false );
	const [ builderOpen,   setBuilderOpen   ] = useState( false );
	const [ device,        setDevice        ] = useState( deviceIds[ 0 ] || 'desktop' );
	const [ data,          setData          ] = useState( initialData );
	const [ innerLeft,     setInnerLeft     ] = useState( 0 );
	const [ popover,       setPopover       ] = useState( null );
	const lastSaved  = useRef( initialData );
	const dragRef    = useRef( null );

	// Show/hide when the panel expands.
	useEffect( () => {
		const panel = wp.customize?.panel?.( panelId );
		if ( ! panel ) return;
		if ( panel.expanded.get() ) { setPanelExpanded( true ); setBuilderOpen( true ); }
		const handler = ( expanded ) => {
			setPanelExpanded( expanded );
			if ( expanded ) setBuilderOpen( true );
			else setBuilderOpen( false );
		};
		panel.expanded.bind( handler );
		return () => panel.expanded.unbind( handler );
	}, [ panelId ] );

	// Stay in sync with the WP device switcher (header / mobile only).
	useEffect( () => {
		if ( ! hasMobile || ! wp?.customize?.previewedDevice ) return;
		const handler = ( d ) => setDevice( d === 'desktop' ? 'desktop' : 'mobile' );
		wp.customize.previewedDevice.bind( handler );
		return () => wp.customize.previewedDevice.unbind( handler );
	}, [ hasMobile ] );

	// Track Customizer sidebar width to offset the builder inner panel.
	useEffect( () => {
		function getSidebarWidth() {
			const paneVisible = wp.customize?.state?.( 'paneVisible' )?.get?.();
			if ( ! paneVisible ) return 0;
			return document.getElementById( 'customize-controls' )?.offsetWidth || 0;
		}

		function update() {
			setInnerLeft( getSidebarWidth() -1 );
		}

		update();

		const paneState = wp.customize?.state?.( 'paneVisible' );
		if ( paneState ) paneState.bind( update );
		window.addEventListener( 'resize', update );

		return () => {
			if ( paneState ) paneState.unbind( update );
			window.removeEventListener( 'resize', update );
		};
	}, [] );

	// Adjust preview iframe bottom to match builder height, updated live via ResizeObserver.
	useEffect( () => {
		const preview = document.getElementById( 'customize-preview' );
		if ( ! preview ) return;

		if ( ! builderOpen ) {
			preview.classList.remove( 'cb--preview-panel-show' );
			preview.style.bottom = '';
			return;
		}

		const root = document.getElementById( `customify-${ builderId }-builder-root` );
		// .customify-hb is position:fixed so root.offsetHeight is 0 — watch the inner panel instead.
		const panel = root?.querySelector( '.customify-hb' );
		const updateBottom = () => {
			if ( panel ) preview.style.bottom = panel.offsetHeight + 'px';
		};

		preview.classList.add( 'cb--preview-panel-show' );
		updateBottom();

		const observer = new ResizeObserver( updateBottom );
		if ( panel ) observer.observe( panel );
		return () => observer.disconnect();
	}, [ builderOpen, builderId ] );

	// Permanently hide all builder sections on mount.
	useEffect( () => {
		hideAllBuilderSections( allItems, infraSections, alwaysVisibleSections );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Persist data when user changes layout.
	useEffect( () => {
		if ( data === lastSaved.current ) return;
		lastSaved.current = data;
		writeSetting( data, controlId );
	}, [ data ] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Sync React state from external wp.customize setting changes (e.g. Load Template,
	// Multiple Headers variant switch). Two sources are honoured:
	//
	//   1. `setting.bind(handler)` — fires when wp.customize.Setting.set() detects a
	//      deep-unequal value. Echo-protected: skip when the new normalized data
	//      matches the value we just wrote ourselves (otherwise writeSetting →
	//      bind → setData → effect → writeSetting loops forever).
	//
	//   2. `customify/builder/external-update` window event — emitted by extensions
	//      (e.g. Customify Pro's useVariantSwitcher) AFTER they have applied a batch
	//      of set() calls. Carries the explicit intent "re-read this setting now,
	//      do NOT consult the echo guard" because the extension already knows the
	//      value changed at the source even if the local `lastSaved` cache happens
	//      to look identical (variant switching can produce values that normalize
	//      to the same shape as a prior state, e.g. when a variant has no override
	//      and falls back to default).
	useEffect( () => {
		const setting = wp.customize?.( controlId );
		if ( ! setting ) return;

		const applyRaw = ( newRaw, force ) => {
			const newData = normalizeData( parseValue( newRaw ), deviceIds, rows, hasSidebar );
			if ( ! force && JSON.stringify( newData ) === JSON.stringify( lastSaved.current ) ) {
				return;
			}
			lastSaved.current = newData;
			setData( newData );
		};

		const settingHandler = ( newRaw ) => applyRaw( newRaw, false );
		setting.bind( settingHandler );

		const eventHandler = ( e ) => {
			const target = e?.detail?.controlId;
			if ( target && target !== controlId ) return;
			applyRaw( setting.get(), true );
		};
		window.addEventListener( 'customify/builder/external-update', eventHandler );

		return () => {
			setting.unbind( settingHandler );
			window.removeEventListener( 'customify/builder/external-update', eventHandler );
		};
	}, [ controlId ] ); // eslint-disable-line react-hooks/exhaustive-deps

	const switchDevice = useCallback( ( d ) => {
		setDevice( d );
		const selector = d === 'desktop'
			? '#customize-footer-actions .preview-desktop'
			: '#customize-footer-actions .preview-mobile';
		document.querySelector( selector )?.click();
	}, [] );

	const moveItem = useCallback( ( itemId, from, to ) => {
		setData( ( prev ) => {
			const next = JSON.parse( JSON.stringify( prev ) );

			if ( from !== 'available' ) {
				const { device: fd, row: fr, col: fc } = from;
				next[ fd ][ fr ][ fc ] = next[ fd ][ fr ][ fc ].filter( ( i ) => i.id !== itemId );
			}

			if ( to === 'available' ) {
				return next;
			}

			const { device: td, row: tr, col: tc } = to;
			for ( const row of Object.keys( next[ td ] ) ) {
				for ( const col of Object.keys( next[ td ][ row ] ) ) {
					next[ td ][ row ][ col ] = next[ td ][ row ][ col ].filter( ( i ) => i.id !== itemId );
				}
			}

			next[ td ][ tr ][ tc ].push( { id: itemId } );
			return next;
		} );
	}, [] );

	const openSection = useCallback( ( sectionId ) => {
		if ( ! sectionId ) return;
		const section = wp.customize?.section?.( sectionId );
		if ( ! section ) return;

		if ( section._customifyForceHide ) {
			section.active.unbind( section._customifyForceHide );
		}
		section.active.set( true );
		section.focus();

		function onExpandChange( expanded ) {
			if ( ! expanded ) {
				section.expanded.unbind( onExpandChange );
				section.active.set( false );
				if ( section._customifyForceHide ) {
					section.active.bind( section._customifyForceHide );
				}
			}
		}
		section.expanded.bind( onExpandChange );
	}, [] );

	const openRowSection = useCallback( ( rowId ) => {
		openSection( builderId + '_' + rowId );
	}, [ openSection, builderId ] );

	// Expose openSection globally so the preview iframe's JS can call it
	// when the user clicks item--preview-name (bypasses section.focus() which
	// fails because _customifyForceHide prevents active.set(true)).
	useEffect( () => {
		window.customifyBuilderOpenSection = openSection;
		return () => { delete window.customifyBuilderOpenSection; };
	}, [ openSection ] );

	// Expose a global refresh helper for extensions that mutate the layout
	// setting externally (Multiple Headers variant switch, programmatic
	// template apply). Callers may pass a specific controlId to scope the
	// refresh; omitting it refreshes every mounted builder via the event
	// fan-out.
	useEffect( () => {
		const prev = window.customifyBuilderRefresh;
		window.customifyBuilderRefresh = ( target ) => {
			window.dispatchEvent( new CustomEvent( 'customify/builder/external-update', {
				detail: { controlId: target || null },
			} ) );
		};
		return () => { window.customifyBuilderRefresh = prev; };
	}, [] );

	const openPopover = useCallback( ( location, anchorRect ) => {
		setPopover( { location, anchorRect } );
	}, [] );

	const closePopover = useCallback( () => setPopover( null ), [] );

	const addItemFromPopover = useCallback( ( itemId ) => {
		if ( ! popover ) return;
		moveItem( itemId, 'available', popover.location );
		setPopover( null );
	}, [ popover, moveItem ] );

	const placedInDevice = getDevicePlacedIds( data, device );
	const availableItems = Object.values( allItems )
		.filter( ( i ) => ! placedInDevice.has( i.id ) )
		.sort( ( a, b ) => {
			const pa = wp.customize?.section?.( a.section )?.priority?.get() ?? 999;
			const pb = wp.customize?.section?.( b.section )?.priority?.get() ?? 999;
			return pa - pb;
		} );

	return (
		<>
		{ builderOpen && <div className="customify-hb customify--panel-v2" style={ { left: innerLeft } }>
			<div className="customify-hb__inner">

				{ /* Header bar: title + device tabs + close */ }
				<div className="customify-hb__header">
					{ builderTitle && builderId !== 'header' && <div className="customify-hb__title">{ builderTitle }</div> }
					<div className="customify-hb__devices">
						{ DEVICES_LIST.length > 1 && DEVICES_LIST.map( ( d ) => (
							<button
								key={ d.id }
								className={ `customify-hb__device-btn${ device === d.id ? ' is-active' : '' }` }
								onClick={ () => switchDevice( d.id ) }
							>
								{ d.id === 'desktop'
									? <span className="dashicons dashicons-desktop" />
									: <span className="dashicons dashicons-smartphone" />
								}
								{ d.label }
							</button>
						) ) }
					</div>
					<div className="customify-hb__actions">
						<button type="button" className="customify-hb__close button button-secondary" onClick={ () => setBuilderOpen( false ) }>
							{ __( 'Close', 'customify' ) }
						</button>
					</div>
				</div>

				{ /* Builder body */ }
				<div className="customify-hb__body">
					<div className={ `customify-hb__grid${ device === 'mobile' ? ' customify-hb__grid--mobile' : '' }` }>

						{ hasSidebar && device === 'mobile' && (
							<OffCanvasRow
								items={ data.mobile.sidebar.sidebar }
								allItems={ allItems }
								dragRef={ dragRef }
								onMove={ moveItem }
								onOpenSection={ openSection }
								onOpenRowSection={ openRowSection }
								onOpenPopover={ openPopover }
							/>
						) }

						<div className="customify-hb__rows">
							{ rows.map( ( rowId ) => (
								<BuilderRow
									key={ rowId }
									rowId={ rowId }
									rowLabel={ rowLabels[ rowId ] }
									cols={ data[ device ][ rowId ] }
									device={ device }
									allItems={ allItems }
									dragRef={ dragRef }
									onMove={ moveItem }
									onOpenSection={ openSection }
									onOpenRowSection={ openRowSection }
									onOpenPopover={ openPopover }
									colLayoutKey={ rowLayoutKeys[ rowId ] || null }
								/>
							) ) }
						</div>

					</div>
				</div>

				{ popover && (
					<ItemPickerPopover
						items={ availableItems }
						anchorRect={ popover.anchorRect }
						onAdd={ addItemFromPopover }
						onClose={ closePopover }
					/>
				) }

			</div>
		</div> }

		{ /* Portal: items list injected into the panel header — always visible when panel is open */ }
		{ panelExpanded && (
			<PanelItemsListPortal
				data={ data }
				device={ device }
				allItems={ allItems }
				availableItems={ availableItems }
				dragRef={ dragRef }
				containerId={ panelItemsContainerId }
				builderTitle={ config?.title || builderId }
				builderOpen={ builderOpen }
				onOpenBuilder={ () => setBuilderOpen( true ) }
				onOpenSection={ openSection }
				onRemove={ ( itemId ) => moveItem( itemId, findItemLocation( data, device, itemId ), 'available' ) }
				onAdd={ ( itemId ) => moveItem( itemId, 'available', { device, row: rows[ 1 ] || rows[ 0 ], col: 'center' } ) }
			/>
		) }

		{ /* Templates panel — Save / Load / Remove inside the {builderId}_templates section */ }
		<TemplatesPanel
			builderId={ builderId }
			controlId={ `${ builderId }_templates_save` }
			mountId={ `customify-${ builderId }-templates-mount` }
			layoutSettingKey={ controlId }
			onApplyLayout={ ( raw ) => {
				const newData = normalizeData( parseValue( raw ), deviceIds, rows, hasSidebar );
				lastSaved.current = newData;
				setData( newData );
			} }
		/>

		{ /* Slot for Popover components (tooltips, etc.) */ }
		<Popover.Slot />
		</>
	);
}

// ---------------------------------------------------------------------------
// Helper: find an item's location within a device
// ---------------------------------------------------------------------------

function findItemLocation( data, device, itemId ) {
	for ( const row of Object.keys( data[ device ] || {} ) ) {
		for ( const col of Object.keys( data[ device ][ row ] || {} ) ) {
			if ( ( data[ device ][ row ][ col ] || [] ).some( ( i ) => i.id === itemId ) ) {
				return { device, row, col };
			}
		}
	}
	return 'available';
}

// ---------------------------------------------------------------------------
// PanelItemsListPortal — renders into the builder panel items container
// ---------------------------------------------------------------------------

function PanelItemsListPortal( { data, device, allItems, availableItems, dragRef, containerId, builderTitle, builderOpen, onOpenBuilder, onOpenSection, onRemove, onAdd } ) {
	// The container lives inside a WP Customizer Underscore.js template rendered
	// lazily (only when the panel first opens) — watch for it via MutationObserver.
	const [ container, setContainer ] = useState( () => document.getElementById( containerId ) );

	useEffect( () => {
		const el = document.getElementById( containerId );
		if ( el ) {
			setContainer( el );
			return;
		}
		const observer = new MutationObserver( () => {
			const found = document.getElementById( containerId );
			if ( found ) {
				setContainer( found );
				observer.disconnect();
			}
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
		return () => observer.disconnect();
	}, [ containerId ] );

	if ( ! container ) return null;

	const placedIds = getDevicePlacedIds( data, device );
	const placed    = [ ...placedIds ]
		.map( ( id ) => allItems[ id ] || { id, name: id, section: '' } )
		.sort( ( a, b ) => {
			const pa = wp.customize?.section?.( a.section )?.priority?.get() ?? 999;
			const pb = wp.customize?.section?.( b.section )?.priority?.get() ?? 999;
			return pa - pb;
		} );

	return createPortal(
		<>
			{ ! builderOpen && (
				<button
					type="button"
					className="customify-hb__open-builder button button-primary"
					onClick={ onOpenBuilder }
				>
					{ __( 'Open Builder', 'customify' ) }
				</button>
			) }

			{ /* Placed items */ }
			<div className="customify-hb__panel-section">
				<div className="customify-hb__panel-items">
					{ placed.length === 0 ? (
						<span className="customify-hb__panel-items-empty">
							{ __( 'No items placed yet.', 'customify' ) }
						</span>
					) : placed.map( ( item ) => {
						const settingsSection = item.layout_section || item.section;
						return (
						<div
							key={ item.id }
							className={ `customify-hb__panel-item${ item.section ? ' is-clickable' : '' }` }
							onClick={ () => item.section && onOpenSection( item.section ) }
						>
							<span className="customify-hb__panel-item-name">{ item.name }</span>
							{ settingsSection && (
								<button
									type="button"
									className="customify-hb__panel-item-btn customify-hb__panel-item-settings"
									title={ __( 'Item Layout', 'customify' ) }
									onClick={ ( e ) => { e.stopPropagation(); onOpenSection( settingsSection ); } }
								>
									<Icon icon={ settings } />
								</button>
							) }
							<button
								type="button"
								className="customify-hb__panel-item-btn customify-hb__panel-item-remove"
								title={ __( 'Remove', 'customify' ) }
								onClick={ ( e ) => { e.stopPropagation(); onRemove( item.id ); } }
							>
								<Icon icon={ close } />
							</button>
						</div>
						);
					} ) }
				</div>
			</div>

			{ /* Available items — drag-only */ }
			{ availableItems.length > 0 && (
				<div className="customify-hb__panel-section">
					<div className="customify-hb__panel-section-label">
						{ __( 'Available', 'customify' ) }
					</div>
					<div className="customify-hb__panel-items">
						{ availableItems.map( ( item ) => (
							<div
								key={ item.id }
								className="customify-hb__panel-item customify-hb__panel-item--available"
								draggable
								title={ __( 'Drag to add to builder', 'customify' ) }
								onDragStart={ ( e ) => {
									dragRef.current = { id: item.id, from: 'available' };
									e.dataTransfer.effectAllowed = 'move';
								} }
								onDragEnd={ () => { dragRef.current = null; } }
							>
								<Icon icon={ dragHandle } className="customify-hb__drag-handle" />
								<span className="customify-hb__panel-item-name">{ item.name }</span>
							</div>
						) ) }
					</div>
				</div>
			) }
		</>,
		container
	);
}

// ---------------------------------------------------------------------------
// BuilderRow
// ---------------------------------------------------------------------------

function parseColLayout( raw ) {
	if ( ! raw ) return null;
	try {
		return typeof raw === 'string' ? JSON.parse( raw ) : raw;
	} catch ( _ ) {
		return null;
	}
}

function BuilderRow( { rowId, rowLabel, cols, device, allItems, dragRef, onMove, onOpenSection, onOpenRowSection, onOpenPopover, colLayoutKey } ) {
	const [ hovered, setHovered ] = useState( false );
	const rowRef = useRef( null );

	const [ colLayoutValue, setColLayoutValue ] = useState( () => {
		if ( ! colLayoutKey ) return null;
		return parseColLayout( window.wp?.customize?.( colLayoutKey )?.get?.() );
	} );

	useEffect( () => {
		if ( ! colLayoutKey ) return;
		const setting = window.wp?.customize?.( colLayoutKey );
		if ( ! setting ) return;
		const handler = ( val ) => setColLayoutValue( parseColLayout( val ) );
		setting.bind( handler );
		return () => setting.unbind( handler );
	}, [ colLayoutKey ] );

	// Derive active columns and grid proportions from col_layout (always use desktop for builder view).
	let activeCols = COLS;
	let colsStyle   = {};
	if ( colLayoutValue ) {
		// count is global; fr is per-device (fall back to desktop).
		const count = Math.max( 1, Math.min( 5, colLayoutValue.count || colLayoutValue.desktop?.count || 3 ) );
		const d     = colLayoutValue.desktop || {};
		const fr    = Array.isArray( d.fr ) && d.fr.length === count ? d.fr : Array( count ).fill( 1 );
		activeCols  = ALL_COLS.slice( 0, count );
		colsStyle   = { display: 'grid', gridTemplateColumns: fr.map( ( v ) => `${ v }fr` ).join( ' ' ) };
	}

	return (
		<div
			ref={ rowRef }
			className={ `customify-hb__row customify-hb__row--${ rowId }` }
			onMouseEnter={ () => setHovered( true ) }
			onMouseLeave={ () => setHovered( false ) }
		>
			{ hovered && (
				<Popover
					anchor={ rowRef.current }
					placement="top-start"
					noArrow
					focusOnMount={ false }
					className="customify-hb__row-tooltip"
					offset={ 0 }
					flip={ false }
				>
					{ rowLabel }
				</Popover>
			) }
			<button
				type="button"
				className="customify-hb__row-label"
				title={ rowLabel }
				onClick={ () => onOpenRowSection( rowId ) }
			>
				<Icon icon={ settings } />
			</button>
			<div className="customify-hb__cols" style={ colsStyle }>
				{ activeCols.map( ( colId ) => (
					<DropZone
						key={ colId }
						colId={ colId }
						rowId={ rowId }
						device={ device }
						items={ cols[ colId ] || [] }
						allItems={ allItems }
						dragRef={ dragRef }
						onMove={ onMove }
						onOpenSection={ onOpenSection }
						onOpenPopover={ onOpenPopover }
					/>
				) ) }
			</div>
		</div>
	);
}

// ---------------------------------------------------------------------------
// OffCanvasRow (mobile sidebar — header only)
// ---------------------------------------------------------------------------

function OffCanvasRow( { items, allItems, dragRef, onMove, onOpenSection, onOpenRowSection, onOpenPopover } ) {
	const [ isDragOver, setIsDragOver ] = useState( false );
	const location = { device: 'mobile', row: 'sidebar', col: 'sidebar' };

	return (
		<div className="customify-hb__row customify-hb__row--sidebar">
			<div className="customify-hb__row-header">
				<button
					type="button"
					className="customify-hb__row-label"
					title={ __( 'Off Canvas Settings', 'customify' ) }
					onClick={ () => onOpenRowSection( 'sidebar' ) }
				>
					<Icon icon={ settings } />
				</button>
				<span className="customify-hb__row-title">{ __( 'Off Canvas', 'customify' ) }</span>
			</div>
			<div
				className={ `customify-hb__offcanvas${ isDragOver ? ' is-drag-over' : '' }` }
				onDragOver={ ( e ) => { e.preventDefault(); setIsDragOver( true ); } }
				onDragLeave={ () => setIsDragOver( false ) }
				onDrop={ ( e ) => {
					e.preventDefault();
					setIsDragOver( false );
					if ( ! dragRef.current ) return;
					const { id, from } = dragRef.current;
					onMove( id, from, location );
					dragRef.current = null;
				} }
				onClick={ ( e ) => {
					if ( e.target.closest( '.customify-hb__item' ) ) return;
					onOpenPopover( location, e.currentTarget.getBoundingClientRect() );
				} }
			>
				{ items.map( ( item ) => {
					const info = allItems[ item.id ] || { name: item.id, section: '' };
					return (
						<ItemChip
							key={ item.id }
							id={ item.id }
							name={ info.name }
							section={ info.section }
							layoutSection={ info.layout_section }
							from={ location }
							dragRef={ dragRef }
							onRemove={ ( id ) => onMove( id, location, 'available' ) }
							onOpenSection={ onOpenSection }
						/>
					);
				} ) }
				{ items.length === 0 && (
					<span className="customify-hb__drop-hint" style={ { pointerEvents: 'none' } }>
						{ __( 'Click to add items', 'customify' ) }
					</span>
				) }
			</div>
		</div>
	);
}

// ---------------------------------------------------------------------------
// DropZone (column)
// ---------------------------------------------------------------------------

function DropZone( { colId, rowId, device, items, allItems, dragRef, onMove, onOpenSection, onOpenPopover } ) {
	const [ isDragOver, setIsDragOver ] = useState( false );
	const location = { device, row: rowId, col: colId };

	return (
		<div
			className={ `customify-hb__col customify-hb__col--${ colId }${ isDragOver ? ' is-drag-over' : '' }` }
			onDragOver={ ( e ) => { e.preventDefault(); setIsDragOver( true ); } }
			onDragLeave={ () => setIsDragOver( false ) }
			onDrop={ ( e ) => {
				e.preventDefault();
				setIsDragOver( false );
				if ( ! dragRef.current ) return;
				const { id, from } = dragRef.current;
				onMove( id, from, location );
				dragRef.current = null;
			} }
			onClick={ ( e ) => {
				if ( e.target.closest( '.customify-hb__item' ) ) return;
				onOpenPopover( location, e.currentTarget.getBoundingClientRect() );
			} }
		>
			{ items.map( ( item ) => {
				const info = allItems[ item.id ] || { name: item.id, section: '' };
				return (
					<ItemChip
						key={ item.id }
						id={ item.id }
						name={ info.name }
						section={ info.section }
						layoutSection={ info.layout_section }
						from={ location }
						dragRef={ dragRef }
						onRemove={ ( id ) => onMove( id, location, 'available' ) }
						onOpenSection={ onOpenSection }
					/>
				);
			} ) }
			{ items.length === 0 && (
				<span className="customify-hb__col-empty" style={ { pointerEvents: 'none' } }>+</span>
			) }
		</div>
	);
}

// ---------------------------------------------------------------------------
// ItemChip
// ---------------------------------------------------------------------------

function ItemChip( { id, name, section, layoutSection, from, dragRef, onRemove, onOpenSection } ) {
	const settingsTarget = layoutSection || section;
	return (
		<div className="customify-hb__item">
			<span
				className="customify-hb__item-handle"
				draggable
				onDragStart={ ( e ) => {
					dragRef.current = { id, from };
					e.dataTransfer.effectAllowed = 'move';
					// Use the whole chip as the drag ghost instead of just the handle span.
					const chip = e.currentTarget.closest( '.customify-hb__item' );
					if ( chip ) {
						const rect = chip.getBoundingClientRect();
						e.dataTransfer.setDragImage( chip, e.clientX - rect.left, e.clientY - rect.top );
					}
				} }
			>
				<Icon icon={ dragHandle } />
			</span>
			{ settingsTarget ? (
				<button
					type="button"
					className="customify-hb__item-name customify-hb__item-name--clickable"
					title={ __( 'Settings', 'customify' ) }
					onClick={ ( e ) => { e.stopPropagation(); onOpenSection( settingsTarget ); } }
				>
					{ name }
				</button>
			) : (
				<span className="customify-hb__item-name">{ name }</span>
			) }
			<button
				type="button" className="customify-hb__item-btn customify-hb__item-remove"
				title={ __( 'Remove', 'customify' ) }
				onClick={ ( e ) => { e.stopPropagation(); onRemove( id ); } }
			>
				<Icon icon={ close } />
			</button>
		</div>
	);
}

// ---------------------------------------------------------------------------
// ItemPickerPopover
// ---------------------------------------------------------------------------

const ARROW_SIZE   = 8;
const POPOVER_W    = 300;
const POPOVER_H    = 240;

function ItemPickerPopover( { items, anchorRect, onAdd, onClose } ) {
	const ref = useRef( null );

	useEffect( () => {
		function handler( e ) {
			if ( ref.current && ! ref.current.contains( e.target ) ) {
				onClose();
			}
		}
		document.addEventListener( 'mousedown', handler );
		return () => document.removeEventListener( 'mousedown', handler );
	}, [ onClose ] );

	const anchorCenterX = anchorRect.left + anchorRect.width / 2;
	const effectiveW    = Math.min( POPOVER_W, window.innerWidth - 8 );
	const rawLeft       = anchorCenterX - effectiveW / 2;
	const popoverLeft   = Math.max( 4, Math.min( rawLeft, window.innerWidth - effectiveW - 4 ) );

	const arrowLeft = Math.max( 12, Math.min( anchorCenterX - popoverLeft - ARROW_SIZE, effectiveW - 12 - ARROW_SIZE * 2 ) );

	const isAbove = anchorRect.top >= POPOVER_H + ARROW_SIZE + 8;

	const popoverStyle = {
		left:  popoverLeft,
		width: POPOVER_W,
	};
	if ( isAbove ) {
		popoverStyle.bottom = window.innerHeight - anchorRect.top + ARROW_SIZE + 2;
	} else {
		popoverStyle.top = anchorRect.bottom + ARROW_SIZE + 2;
	}

	return (
		<div ref={ ref } className={ `customify-hb__popover${ isAbove ? ' is-above' : ' is-below' }` } style={ popoverStyle }>
			<div
				className="customify-hb__popover-arrow"
				style={ { left: arrowLeft } }
			/>
			<div className="customify-hb__popover-list">
				{ items.length === 0 ? (
					<span className="customify-hb__popover-empty">
						{ __( 'All items are placed in the layout', 'customify' ) }
					</span>
				) : items.map( ( item ) => (
					<button
						key={ item.id }
						className="customify-hb__popover-item"
						onClick={ () => onAdd( item.id ) }
					>
						<Icon icon={ plus } className="customify-hb__popover-item-icon" />
						<span className="customify-hb__popover-item-label">{ item.name }</span>
					</button>
				) ) }
			</div>
		</div>
	);
}
