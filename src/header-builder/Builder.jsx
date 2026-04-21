/**
 * Customify Header Builder — main React component tree.
 *
 * Data flow:
 *   wp.customize Setting  ←read/write→  React state (normalizeData shape)
 *
 * JSON schema (header_builder_panel_v2):
 * {
 *   desktop: { top: {left:[{id}], center:[{id}], right:[{id}]}, main:{…}, bottom:{…} },
 *   mobile:  { top:{…}, main:{…}, bottom:{…}, sidebar:{sidebar:[{id}]} }
 * }
 * The setting value is stored as encodeURIComponent(JSON.stringify(data)).
 */

import { useState, useEffect, useRef, useCallback, createPortal } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, Popover } from '@wordpress/components';
import { dragHandle, settings, close, plus } from '@wordpress/icons';

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------

const ROWS   = [ 'top', 'main', 'bottom' ];
const COLS   = [ 'left', 'center', 'right' ];
const DEVICES = [
	{ id: 'desktop', label: __( 'Desktop', 'customify' ) },
	{ id: 'mobile',  label: __( 'Mobile / Tablet', 'customify' ) },
];

// Sections always visible in WP Customizer — never hide these.
// title_tagline (Logo & Site Identity) is intentionally NOT included: we hide
// it by default and only show it when the user clicks the logo settings icon,
// matching the same hide/show behaviour as all other builder element sections.
const ALWAYS_VISIBLE_SECTIONS = new Set( [ 'header_templates' ] );

// Builder infrastructure sections — always hidden (rows + panel).
const BUILDER_INFRA_SECTIONS = new Set( [
	'header_builder_panel',
	'header_top', 'header_main', 'header_bottom', 'header_sidebar',
] );

// ---------------------------------------------------------------------------
// wp.customize bridge
// ---------------------------------------------------------------------------

const CONTROL_ID = 'header_builder_panel_v2';

function parseValue( raw ) {
	if ( ! raw ) return {};
	if ( typeof raw === 'object' ) return raw;
	try { return JSON.parse( decodeURIComponent( raw ) ); } catch ( _ ) {}
	try { return JSON.parse( raw ); } catch ( _ ) {}
	return {};
}

function readSetting() {
	try {
		const setting = wp.customize( CONTROL_ID );
		return setting ? parseValue( setting.get() ) : {};
	} catch ( _ ) {
		return {};
	}
}

function writeSetting( data ) {
	try {
		const setting = wp.customize( CONTROL_ID );
		if ( setting ) {
			setting.set( encodeURIComponent( JSON.stringify( data ) ) );
		}
	} catch ( _ ) {}
}

// ---------------------------------------------------------------------------
// Data helpers
// ---------------------------------------------------------------------------

function normalizeData( raw ) {
	const data = {};
	for ( const { id: dev } of DEVICES ) {
		data[ dev ] = {};
		for ( const row of ROWS ) {
			data[ dev ][ row ] = {};
			for ( const col of COLS ) {
				data[ dev ][ row ][ col ] = ( raw?.[ dev ]?.[ row ]?.[ col ] ) || [];
			}
		}
	}
	// Sidebar only exists on mobile.
	data.mobile.sidebar = { sidebar: ( raw?.mobile?.sidebar?.sidebar ) || [] };
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
	// Re-hide if WP Customizer tries to re-activate (e.g. on scroll repaint).
	// Store the handler on the section object so openSection can unbind it by reference.
	if ( ! section._customifyForceHide ) {
		section._customifyForceHide = function( active ) {
			if ( active ) section.active.set( false );
		};
	}
	section.active.bind( section._customifyForceHide );
}

function hideAllBuilderSections( allItems ) {
	if ( ! wp?.customize?.section ) return;
	// Hide infrastructure sections (rows, builder panel).
	for ( const id of BUILDER_INFRA_SECTIONS ) {
		permanentlyHideSection( wp.customize.section( id ) );
	}
	// Hide item element sections.
	for ( const item of Object.values( allItems ) ) {
		if ( ! item.section || ALWAYS_VISIBLE_SECTIONS.has( item.section ) ) continue;
		permanentlyHideSection( wp.customize.section( item.section ) );
	}
}

// ---------------------------------------------------------------------------
// Builder (root)
// ---------------------------------------------------------------------------

export default function Builder( { config } ) {
	const panelId  = config?.panel || 'header_settings';
	const allItems = config?.items || {};
	const rowLabels = config?.rows  || {
		top:     __( 'Header Top', 'customify' ),
		main:    __( 'Header Main', 'customify' ),
		bottom:  __( 'Header Bottom', 'customify' ),
		sidebar: __( 'Menu Sidebar', 'customify' ),
	};

	const initialData = normalizeData( readSetting() );

	const [ visible,   setVisible   ] = useState( false );
	const [ device,    setDevice    ] = useState( 'desktop' );
	const [ data,      setData      ] = useState( initialData );
	const [ innerLeft, setInnerLeft ] = useState( 0 );
	const [ popover,   setPopover   ] = useState( null ); // { location, anchorRect }
	const lastSaved  = useRef( initialData );
	const dragRef    = useRef( null );

	// Show/hide when the Header panel expands.
	useEffect( () => {
		const panel = wp.customize?.panel?.( panelId );
		if ( ! panel ) return;
		// Check current state (handles deep-link / URL hash cases).
		if ( panel.expanded.get() ) setVisible( true );
		const handler = ( expanded ) => setVisible( expanded );
		panel.expanded.bind( handler );
		return () => panel.expanded.unbind( handler );
	}, [ panelId ] );

	// Stay in sync with the WP device switcher.
	useEffect( () => {
		if ( ! wp?.customize?.previewedDevice ) return;
		const handler = ( d ) => setDevice( d === 'desktop' ? 'desktop' : 'mobile' );
		wp.customize.previewedDevice.bind( handler );
		return () => wp.customize.previewedDevice.unbind( handler );
	}, [] );

	// Track Customizer sidebar width to offset the builder inner panel.
	useEffect( () => {
		function getSidebarWidth() {
			const paneVisible = wp.customize?.state?.( 'paneVisible' )?.get?.();
			if ( ! paneVisible ) return 0;
			return document.getElementById( 'customize-controls' )?.offsetWidth || 0;
		}

		function update() {
			setInnerLeft( getSidebarWidth() );
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

	// Adjust preview iframe height when builder shows/hides.
	useEffect( () => {
		const preview = document.getElementById( 'customize-preview' );
		if ( ! preview ) return;
		preview.classList.toggle( 'cb--preview-panel-show', visible );
	}, [ visible ] );

	// Permanently hide all builder sections on mount.
	useEffect( () => {
		hideAllBuilderSections( allItems );
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Persist data when user changes layout.
	useEffect( () => {
		if ( data === lastSaved.current ) return;
		lastSaved.current = data;
		writeSetting( data );
	}, [ data ] ); // eslint-disable-line react-hooks/exhaustive-deps

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

			// Remove from source location.
			if ( from !== 'available' ) {
				const { device: fd, row: fr, col: fc } = from;
				next[ fd ][ fr ][ fc ] = next[ fd ][ fr ][ fc ].filter( ( i ) => i.id !== itemId );
			}

			if ( to === 'available' ) {
				// Item returned to pool — already removed from source above.
				return next;
			}

			// Remove from any existing placement in the target device (one placement per device).
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

		// Remove the stored force-hide handler (by reference) so active.set(true) sticks.
		if ( section._customifyForceHide ) {
			section.active.unbind( section._customifyForceHide );
		}
		section.active.set( true );
		section.focus();

		// Re-hide when the section collapses (user navigates back).
		function onExpandChange( expanded ) {
			if ( ! expanded ) {
				section.expanded.unbind( onExpandChange );
				section.active.set( false );
				// Re-bind the stored forceHide so it stays hidden until next click.
				if ( section._customifyForceHide ) {
					section.active.bind( section._customifyForceHide );
				}
			}
		}
		section.expanded.bind( onExpandChange );
	}, [] );

	const openRowSection = useCallback( ( rowId ) => {
		openSection( 'header_' + rowId );
	}, [ openSection ] );

	const openPopover = useCallback( ( location, anchorRect ) => {
		setPopover( { location, anchorRect } );
	}, [] );

	const closePopover = useCallback( () => setPopover( null ), [] );

	const addItemFromPopover = useCallback( ( itemId ) => {
		if ( ! popover ) return;
		moveItem( itemId, 'available', popover.location );
		setPopover( null );
	}, [ popover, moveItem ] );

	if ( ! visible ) return null;

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
		<div className="customify-hb customify--panel-v2" style={ { left: innerLeft } }>
			<div className="customify-hb__inner">

				{ /* Header bar: device tabs + close */ }
				<div className="customify-hb__header">
					<div className="customify-hb__devices">
						{ DEVICES.map( ( d ) => (
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
						<button className="customify-hb__close button button-secondary" onClick={ () => setVisible( false ) }>
							{ __( 'Close', 'customify' ) }
						</button>
					</div>
				</div>

				{ /* Builder body */ }
				<div className="customify-hb__body">
					<div className={ `customify-hb__grid${ device === 'mobile' ? ' customify-hb__grid--mobile' : '' }` }>

						{ device === 'mobile' && (
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
							{ ROWS.map( ( rowId ) => (
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
		</div>

		{ /* Portal: items list injected into the panel header */ }
		<PanelItemsListPortal
			data={ data }
			device={ device }
			allItems={ allItems }
			availableItems={ availableItems }
			dragRef={ dragRef }
			onOpenSection={ openSection }
			onRemove={ ( itemId ) => moveItem( itemId, findItemLocation( data, device, itemId ), 'available' ) }
			onAdd={ ( itemId ) => moveItem( itemId, 'available', { device, row: 'main', col: 'center' } ) }
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
// PanelItemsListPortal — renders into #customify-hb-panel-items via portal
// ---------------------------------------------------------------------------

function PanelItemsListPortal( { data, device, allItems, availableItems, dragRef, onOpenSection, onRemove, onAdd } ) {
	// Track the portal container with state so React re-renders when it appears.
	// The container lives inside a WP Customizer Underscore.js template that is
	// rendered lazily (only when the panel first opens), so it may not exist on
	// the initial React render — we watch for it via MutationObserver.
	const [ container, setContainer ] = useState( () => document.getElementById( 'customify-hb-panel-items' ) );

	useEffect( () => {
		const el = document.getElementById( 'customify-hb-panel-items' );
		if ( el ) {
			setContainer( el );
			return;
		}
		const observer = new MutationObserver( () => {
			const found = document.getElementById( 'customify-hb-panel-items' );
			if ( found ) {
				setContainer( found );
				observer.disconnect();
			}
		} );
		observer.observe( document.body, { childList: true, subtree: true } );
		return () => observer.disconnect();
	}, [] );

	if ( ! container ) return null;

	const placedIds = getDevicePlacedIds( data, device );
	// Map each placed id → allItems entry; fall back to a minimal object so items
	// always appear even if allItems is stale or incomplete.
	const placed    = [ ...placedIds ]
		.map( ( id ) => allItems[ id ] || { id, name: id, section: '' } )
		.sort( ( a, b ) => {
			const pa = wp.customize?.section?.( a.section )?.priority?.get() ?? 999;
			const pb = wp.customize?.section?.( b.section )?.priority?.get() ?? 999;
			return pa - pb;
		} );

	return createPortal(
		<>
			{ /* Placed items — no label */ }
			<div className="customify-hb__panel-section">
				<div className="customify-hb__panel-items">
					{ placed.length === 0 ? (
						<span className="customify-hb__panel-items-empty">
							{ __( 'No items placed yet.', 'customify' ) }
						</span>
					) : placed.map( ( item ) => (
						<div
							key={ item.id }
							className={ `customify-hb__panel-item${ item.section ? ' is-clickable' : '' }` }
							onClick={ () => item.section && onOpenSection( item.section ) }
						>
							<span className="customify-hb__panel-item-name">{ item.name }</span>
							{ item.section && (
								<Icon icon={ settings } className="customify-hb__panel-item-icon" />
							) }
							<button
								className="customify-hb__panel-item-btn customify-hb__panel-item-remove"
								title={ __( 'Remove', 'customify' ) }
								onClick={ ( e ) => { e.stopPropagation(); onRemove( item.id ); } }
							>
								<Icon icon={ close } />
							</button>
						</div>
					) ) }
				</div>
			</div>

			{ /* Available items — drag-only, displayed as compact chips */ }
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
								title={ __( 'Drag to add to header', 'customify' ) }
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

function BuilderRow( { rowId, rowLabel, cols, device, allItems, dragRef, onMove, onOpenSection, onOpenRowSection, onOpenPopover } ) {
	const [ hovered, setHovered ] = useState( false );
	const rowRef = useRef( null );

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
				className="customify-hb__row-label"
				title={ rowLabel }
				onClick={ () => onOpenRowSection( rowId ) }
			>
				<Icon icon={ settings } />
			</button>
			<div className="customify-hb__cols">
				{ COLS.map( ( colId ) => (
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
// OffCanvasRow (mobile sidebar)
// ---------------------------------------------------------------------------

function OffCanvasRow( { items, allItems, dragRef, onMove, onOpenSection, onOpenRowSection, onOpenPopover } ) {
	const [ isDragOver, setIsDragOver ] = useState( false );
	const location = { device: 'mobile', row: 'sidebar', col: 'sidebar' };

	return (
		<div className="customify-hb__row customify-hb__row--sidebar">
			<div className="customify-hb__row-header">
				<button
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

function ItemChip( { id, name, section, from, dragRef, onRemove, onOpenSection } ) {
	return (
		<div
			className="customify-hb__item"
			draggable
			onDragStart={ ( e ) => {
				dragRef.current = { id, from };
				e.dataTransfer.effectAllowed = 'move';
			} }
		>
			<Icon icon={ dragHandle } className="customify-hb__item-handle" />
			<span className="customify-hb__item-name">{ name }</span>
			{ section && (
				<button
					className="customify-hb__item-btn customify-hb__item-settings"
					title={ __( 'Settings', 'customify' ) }
					onClick={ ( e ) => { e.stopPropagation(); onOpenSection( section ); } }
				>
					<Icon icon={ settings } />
				</button>
			) }
			<button
				className="customify-hb__item-btn customify-hb__item-remove"
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
const POPOVER_W    = 220;
const POPOVER_H    = 240;

function ItemPickerPopover( { items, anchorRect, onAdd, onClose } ) {
	const ref = useRef( null );

	// Close on click outside.
	useEffect( () => {
		function handler( e ) {
			if ( ref.current && ! ref.current.contains( e.target ) ) {
				onClose();
			}
		}
		document.addEventListener( 'mousedown', handler );
		return () => document.removeEventListener( 'mousedown', handler );
	}, [ onClose ] );

	// Center popover over anchor, clamp to viewport edges.
	const anchorCenterX = anchorRect.left + anchorRect.width / 2;
	const effectiveW    = Math.min( POPOVER_W, window.innerWidth - 8 );
	const rawLeft       = anchorCenterX - effectiveW / 2;
	const popoverLeft   = Math.max( 4, Math.min( rawLeft, window.innerWidth - effectiveW - 4 ) );

	// Arrow horizontal offset inside popover (points at anchor center).
	const arrowLeft = Math.max( 12, Math.min( anchorCenterX - popoverLeft - ARROW_SIZE, effectiveW - 12 - ARROW_SIZE * 2 ) );

	// Flip: prefer above, fall back to below.
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
						{ item.name }
					</button>
				) ) }
			</div>
		</div>
	);
}
