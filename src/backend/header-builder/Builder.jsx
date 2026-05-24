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

import { useState, useEffect, useRef, useCallback, createPortal, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Icon, Popover } from '@wordpress/components';
import { dragHandle, settings, close, plus } from '@wordpress/icons';
import {
	DndContext,
	DragOverlay,
	PointerSensor,
	KeyboardSensor,
	useSensor,
	useSensors,
	useDraggable,
	useDroppable,
	useDndMonitor,
	pointerWithin,
	rectIntersection,
} from '@dnd-kit/core';
import {
	SortableContext,
	useSortable,
	sortableKeyboardCoordinates,
	rectSortingStrategy,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
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
	const lastSaved = useRef( initialData );
	// activeDrag holds the snapshot needed to render the floating ghost.
	// Shape: { itemId, name }
	const [ activeDrag, setActiveDrag ] = useState( null );
	// Cursor position tracked manually so the ghost (portaled to body)
	// can follow the pointer. dnd-kit's <DragOverlay> handles this for us
	// in normal contexts, but the Customizer chrome creates a stacking
	// context that traps DragOverlay's z-index — rendering our own ghost
	// directly under <body> sidesteps the trap.
	const [ cursorPos, setCursorPos ] = useState( null );

	useEffect( () => {
		if ( ! activeDrag ) return;
		const onMove = ( e ) => setCursorPos( { x: e.clientX, y: e.clientY } );
		window.addEventListener( 'pointermove', onMove );
		return () => window.removeEventListener( 'pointermove', onMove );
	}, [ activeDrag ] );

	// Toggle a body class for the duration of an active drag. SCSS uses
	// it to suppress chip / available-row :hover styles so the cursor
	// gliding over another chip doesn't trigger that chip's hover ring
	// (which would compete with the drop-line indicator). Class auto-
	// clears when activeDrag becomes null (drop or cancel).
	useEffect( () => {
		if ( ! activeDrag ) return;
		document.body.classList.add( 'customify-hb-is-dragging' );
		return () => document.body.classList.remove( 'customify-hb-is-dragging' );
	}, [ activeDrag ] );

	const sensors = useSensors(
		// Require a small drag distance before activating so plain click on
		// the handle doesn't initiate a drag (keeps settings/remove buttons
		// usable from inside the chip).
		useSensor( PointerSensor, { activationConstraint: { distance: 4 } } ),
		useSensor( KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates } )
	);

	function handleDragStart( event ) {
		const d = event.active?.data?.current;
		if ( ! d ) return;
		setActiveDrag( { itemId: d.itemId, name: allItems[ d.itemId ]?.name || d.itemId } );
		// Seed cursor at the activator event so the ghost shows up at the
		// correct spot on the very first paint (before pointermove fires).
		const ev = event.activatorEvent;
		if ( ev && typeof ev.clientX === 'number' ) {
			setCursorPos( { x: ev.clientX, y: ev.clientY } );
		}
	}

	function handleDragCancel() {
		setActiveDrag( null );
		setCursorPos( null );
	}

	// Drop happens on dragEnd only. The drop-line indicator rendered by
	// each DropZoneInner via useDndMonitor tells the user where the item
	// will land — chips never shift during the drag.
	function handleDragEnd( event ) {
		setActiveDrag( null );
		setCursorPos( null );

		const { active, over } = event;
		if ( ! active || ! over ) return;
		if ( active.id === over.id ) return;

		const activeData = active.data?.current;
		const overData   = over.data?.current;
		if ( ! activeData || ! overData ) return;

		const itemId = activeData.itemId;
		const from = activeData.type === 'available' ? 'available' : activeData.location;

		let to, insertIndex;
		if ( overData.type === 'placed' ) {
			to = overData.location;
			insertIndex = overData.index;
		} else if ( overData.type === 'column' ) {
			to = overData.location;
			insertIndex = -1;
		} else {
			return;
		}

		moveItem( itemId, from, to, insertIndex );
	}

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

	const moveItem = useCallback( ( itemId, from, to, insertIndex = -1 ) => {
		setData( ( prev ) => {
			const next = JSON.parse( JSON.stringify( prev ) );

			// If reordering within the same column, adjust the insert index to
			// compensate for the upcoming removal: any insertion point that
			// sits AFTER the item's current position effectively shifts by one
			// once the source slot is removed. Without this, dropping an item
			// just below its current row would no-op visually.
			let adjustedIndex = insertIndex;
			if (
				from !== 'available'
				&& to !== 'available'
				&& from.device === to.device
				&& from.row === to.row
				&& from.col === to.col
				&& insertIndex >= 0
			) {
				const sourceCol = next[ from.device ][ from.row ][ from.col ];
				const currentIdx = sourceCol.findIndex( ( i ) => i.id === itemId );
				if ( currentIdx >= 0 && insertIndex > currentIdx ) {
					adjustedIndex = insertIndex - 1;
				}
			}

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

			const targetCol = next[ td ][ tr ][ tc ];
			const finalIdx  = adjustedIndex < 0 || adjustedIndex > targetCol.length
				? targetCol.length
				: adjustedIndex;
			targetCol.splice( finalIdx, 0, { id: itemId } );
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

		// Re-hide the section only once the user has truly returned to the
		// panel root. Doing it on every collapse breaks WP flows that
		// momentarily collapse the section to navigate into a child
		// context — most importantly the block widget editor's "Show
		// more settings", which expands an `InspectorSection` and calls
		// `parentSection.collapse({manualTransition:true})`. That
		// manualTransition path bypasses the standard
		// `Section.onChangeExpanded`, so `wp.customize.state('expandedSection')`
		// is NOT updated to the inspector's id. The only reliable signal
		// that the parent collapsed "into" its inspector is the sidebar-
		// section method `hasSubSectionOpened()`.
		//
		// When the user clicks Back on the inspector, WP's
		// `InspectorSection.onChangeExpanded(false)` calls
		// `parentSection.expand(...)`. We must keep `_customifyForceHide`
		// unbound throughout that flow so the re-expand actually works
		// (otherwise the parent section appears empty on return).
		const expandedSectionState = wp.customize?.state?.( 'expandedSection' );

		function isSubSectionOpen() {
			if ( typeof section.hasSubSectionOpened === 'function' ) {
				try { return !! section.hasSubSectionOpened(); } catch ( _ ) {}
			}
			const inspector = section.inspector;
			if ( inspector && typeof inspector.expanded === 'function' ) {
				try { return !! inspector.expanded(); } catch ( _ ) {}
			}
			return false;
		}

		function finalizeHide() {
			section.expanded.unbind( onExpandChange );
			if ( expandedSectionState ) {
				expandedSectionState.unbind( checkAndHide );
			}
			section.active.set( false );
			if ( section._customifyForceHide ) {
				section.active.bind( section._customifyForceHide );
			}
		}

		function checkAndHide() {
			// User re-expanded our section — keep it visible.
			if ( section.expanded.get() ) {
				if ( expandedSectionState ) {
					expandedSectionState.unbind( checkAndHide );
				}
				return;
			}
			// A sub-section (block inspector) is open — wait until it
			// closes. WP will re-expand the parent when it does.
			if ( isSubSectionOpen() ) {
				return;
			}
			// Some other section is expanded — user navigated sideways or
			// deeper. Wait until they leave or return.
			if ( expandedSectionState && expandedSectionState.get() ) {
				return;
			}
			// No section expanded — user is back at the panel root. Hide.
			finalizeHide();
		}

		function onExpandChange( expanded ) {
			if ( expanded ) {
				// Re-expanded; cancel any pending deferred hide listener.
				if ( expandedSectionState ) {
					expandedSectionState.unbind( checkAndHide );
				}
				return;
			}
			// Collapsed because a sub-section (block inspector) opened.
			// Keep listening on `expanded` — WP re-expands the parent
			// when the sub-section closes, which fires onExpandChange(true).
			if ( isSubSectionOpen() ) {
				return;
			}
			if ( expandedSectionState ) {
				expandedSectionState.bind( checkAndHide );
				// Defer the first check so WP can finish propagating state.
				setTimeout( checkAndHide, 0 );
			} else {
				finalizeHide();
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
		<DndContext
			sensors={ sensors }
			collisionDetection={ pointerWithin }
			autoScroll={ false }
			onDragStart={ handleDragStart }
			onDragEnd={ handleDragEnd }
			onDragCancel={ handleDragCancel }
		>
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

		{ /* Floating ghost rendered directly under <body> via portal so it
			lives outside any Customizer stacking context (the chrome's
			.wp-full-overlay / sidebar layers otherwise win z-index even
			against 99 million). Cursor position is tracked manually via
			pointermove during the drag — we replace dnd-kit's
			<DragOverlay> entirely so we have full control over the portal
			target and stacking. */ }
		{ activeDrag && cursorPos && createPortal(
			<div
				style={ {
					position: 'fixed',
					left: cursorPos.x,
					top:  cursorPos.y,
					transform: 'translate(-20px, -20px)', // small offset from cursor
					pointerEvents: 'none',
					zIndex: 2147483647, // max int — guaranteed top
				} }
			>
				<ChipPreview name={ activeDrag.name } />
			</div>,
			document.body
		) }
		</DndContext>
	);
}

// ---------------------------------------------------------------------------
// Helper: find an item's location within a device
// ---------------------------------------------------------------------------

// String key used to compare container identities (device/row/col).
// `'available'` is reserved for the Available items panel.
function locationKey( loc ) {
	if ( loc === 'available' || ! loc ) return 'available';
	return `${ loc.device }::${ loc.row }::${ loc.col }`;
}

function sameLocation( a, b ) {
	if ( ! a || ! b ) return false;
	return a.device === b.device && a.row === b.row && a.col === b.col;
}

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

function PanelItemsListPortal( { data, device, allItems, availableItems, containerId, builderTitle, builderOpen, onOpenBuilder, onOpenSection, onRemove, onAdd } ) {
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

			{ /* Available items — drag-only source. Each item is a dnd-kit
				draggable; dragging it lands the item in whichever column
				the cursor releases over. */ }
			{ availableItems.length > 0 && (
				<div className="customify-hb__panel-section">
					<div className="customify-hb__panel-section-label">
						{ __( 'Available', 'customify' ) }
					</div>
					<div className="customify-hb__panel-items">
						{ availableItems.map( ( item ) => (
							<AvailableItem key={ item.id } itemId={ item.id } name={ item.name } />
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

// Drag source for the Available panel (items not yet placed in any
// column). useDraggable instead of useSortable because available rows
// don't need internal reordering — they're just sources that, when
// dropped on a column, get inserted via the root onDragEnd handler.
//
// IMPORTANT: We intentionally do NOT apply useDraggable's `transform`
// to the source element. The Available panel lives inside the
// Customizer sidebar (overflow:auto + Customizer's own scroll bridge);
// translating the source row inside that container causes the entire
// sidebar to be visually pulled along with the drag, and the panel
// cannot restore its scroll/layout cleanly after drop. The floating
// <DragOverlay> already renders a chip-shaped ghost that follows the
// cursor — that is effectively the "clone" needed for visual feedback,
// so the source row stays rock-still in the panel (just dimmed).
function AvailableItem( { itemId, name } ) {
	const { attributes, listeners, setNodeRef, setActivatorNodeRef, isDragging } = useDraggable( {
		id:   `avail::${ itemId }`,
		data: { type: 'available', itemId },
	} );

	const style = {
		opacity: isDragging ? 0.4 : 1,
	};

	return (
		<div
			ref={ setNodeRef }
			style={ style }
			className="customify-hb__panel-item customify-hb__panel-item--available"
			title={ __( 'Drag to add to builder', 'customify' ) }
			{ ...attributes }
			{ ...listeners }
		>
			{ /* Keep the activator ref on the icon so keyboard focus
				(Tab → Space) still lands on a sensible target, but the
				pointer listeners now live on the row wrapper so a user
				can grab the whole item from anywhere — clicking the
				name area or empty space also initiates the drag. */ }
			<span ref={ setActivatorNodeRef } className="customify-hb__avail-handle">
				<Icon icon={ dragHandle } className="customify-hb__drag-handle" />
			</span>
			<span className="customify-hb__panel-item-name">{ name }</span>
		</div>
	);
}

function BuilderRow( { rowId, rowLabel, cols, device, allItems, onMove, onOpenSection, onOpenRowSection, onOpenPopover, colLayoutKey } ) {
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

function OffCanvasRow( { items, allItems, onMove, onOpenSection, onOpenRowSection, onOpenPopover } ) {
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
			<DropZoneInner
				containerClass="customify-hb__offcanvas"
				strategy={ verticalListSortingStrategy }
				orientation="vertical"
				location={ location }
				items={ items }
				allItems={ allItems }
				onMove={ onMove }
				onOpenSection={ onOpenSection }
				onOpenPopover={ onOpenPopover }
				emptyHint={
					<span className="customify-hb__drop-hint" style={ { pointerEvents: 'none' } }>
						{ __( 'Click to add items', 'customify' ) }
					</span>
				}
			/>
		</div>
	);
}

// ---------------------------------------------------------------------------
// DropZone (column)
// ---------------------------------------------------------------------------

function DropZone( { colId, rowId, device, items, allItems, onMove, onOpenSection, onOpenPopover } ) {
	const location = { device, row: rowId, col: colId };
	return (
		<DropZoneInner
			containerClass={ `customify-hb__col customify-hb__col--${ colId }` }
			strategy={ rectSortingStrategy }
			orientation="horizontal"
			location={ location }
			items={ items }
			allItems={ allItems }
			onMove={ onMove }
			onOpenSection={ onOpenSection }
			onOpenPopover={ onOpenPopover }
			emptyHint={ <span className="customify-hb__col-empty" style={ { pointerEvents: 'none' } }>+</span> }
		/>
	);
}

// Shared body for column + off-canvas. Wraps a SortableContext for the
// chip drag mechanics and a useDroppable target (so empty cols still
// accept drops from the Available panel or other cols). Inside, a thin
// <DropLine> indicator is rendered between chips at the cursor position
// while a drag is over this column. Chips DO NOT shift — the line is
// the only visual cue, which keeps the layout stable even when the
// column wraps to multiple rows.
function DropZoneInner( { containerClass, strategy, orientation, location, items, allItems, onMove, onOpenSection, onOpenPopover, emptyHint } ) {
	const containerId = `col::${ location.device }::${ location.row }::${ location.col }`;
	const itemIds = items.map( ( i ) => `placed::${ i.id }` );

	const { setNodeRef, isOver } = useDroppable( {
		id:   containerId,
		data: { type: 'column', location, itemIds },
	} );

	// dropAt:
	//   { type: 'before', index: N } — render line BEFORE chip N
	//   { type: 'end' }              — render line AFTER the last chip
	//   null                         — no line in this column
	const [ dropAt, setDropAt ] = useState( null );

	useDndMonitor( {
		onDragOver( { over } ) {
			if ( ! over ) { setDropAt( null ); return; }
			const d = over.data?.current;
			if ( ! d || ! d.location || ! sameLocation( d.location, location ) ) {
				setDropAt( null );
				return;
			}
			if ( d.type === 'placed' ) {
				setDropAt( { type: 'before', index: d.index } );
			} else if ( d.type === 'column' ) {
				setDropAt( { type: 'end' } );
			} else {
				setDropAt( null );
			}
		},
		onDragEnd()    { setDropAt( null ); },
		onDragCancel() { setDropAt( null ); },
	} );

	return (
		<SortableContext items={ itemIds } strategy={ strategy }>
			<div
				ref={ setNodeRef }
				className={ `${ containerClass }${ isOver ? ' is-drag-over' : '' }` }
				onClick={ ( e ) => {
					if ( e.target.closest( '.customify-hb__item' ) ) return;
					onOpenPopover( location, e.currentTarget.getBoundingClientRect() );
				} }
			>
				{ items.map( ( item, idx ) => {
					const info = allItems[ item.id ] || { name: item.id, section: '' };
					return (
						<Fragment key={ item.id }>
							{ dropAt && dropAt.type === 'before' && dropAt.index === idx && (
								<DropLine orientation={ orientation } />
							) }
							<SortableItemChip
								sortableId={ `placed::${ item.id }` }
								itemId={ item.id }
								name={ info.name }
								section={ info.section }
								layoutSection={ info.layout_section }
								location={ location }
								index={ idx }
								onRemove={ ( id ) => onMove( id, location, 'available' ) }
								onOpenSection={ onOpenSection }
							/>
						</Fragment>
					);
				} ) }
				{ dropAt && dropAt.type === 'end' && <DropLine orientation={ orientation } /> }
				{ items.length === 0 && emptyHint }
			</div>
		</SortableContext>
	);
}

// Drop-line indicator. Per the chosen UX:
//   horizontal column → zero-width slot (40px tall) with a 3px absolute
//                       inner bar (vertical line between chips).
//   vertical sidebar  → zero-height slot (full width) with a 3px absolute
//                       inner bar (horizontal line between stacked chips).
// Zero size in the active axis means inserting/removing the line never
// shifts the surrounding chips' positions.
function DropLine( { orientation } ) {
	const className = `customify-hb__drop-line customify-hb__drop-line--${ orientation || 'horizontal' }`;
	return (
		<div className={ className } aria-hidden="true">
			<div className="customify-hb__drop-line__bar" />
		</div>
	);
}

// ---------------------------------------------------------------------------
// ItemChip
// ---------------------------------------------------------------------------

// Sortable chip — placed in a column. Registers with the nearest
// SortableContext via useSortable. The drag handle gets the listeners; the
// rest of the chip stays clickable (settings + remove buttons).
//
// We intentionally do NOT apply useSortable's `transform`/`transition` to
// the chip style. The classic "shift-aside" preview that the sortable
// preset provides does not survive flex-wrap multi-row layouts (chips
// pile up because translates can't simulate wrap). Instead, sibling chips
// stay put and DropZoneInner renders a thin `<DropLine>` between chips
// at the cursor position via useDndMonitor — same UX intent, no overlap.
function SortableItemChip( { sortableId, itemId, name, section, layoutSection, location, index, onRemove, onOpenSection } ) {
	const settingsTarget = layoutSection || section;
	const { attributes, listeners, setNodeRef, setActivatorNodeRef, isDragging } = useSortable( {
		id:   sortableId,
		data: { type: 'placed', itemId, location, index },
	} );

	const style = {
		opacity: isDragging ? 0.4 : 1,
	};

	return (
		<div
			ref={ setNodeRef }
			style={ style }
			className={ `customify-hb__item${ isDragging ? ' is-dragging' : '' }` }
			{ ...attributes }
		>
			<span
				ref={ setActivatorNodeRef }
				className="customify-hb__item-handle"
				{ ...listeners }
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
				onClick={ ( e ) => { e.stopPropagation(); onRemove( itemId ); } }
			>
				<Icon icon={ close } />
			</button>
		</div>
	);
}

// Visual-only preview rendered inside <DragOverlay> while a chip or
// available item is being dragged. Mirrors the placed-chip DOM exactly
// (handle + name + remove button) so the ghost's intrinsic width and
// height match the source — without the trailing button slot the
// inline-flex measurement collapses to a narrower box and the user sees
// a smaller ghost than the original chip.
function ChipPreview( { name } ) {
	return (
		<div className="customify-hb__item customify-hb__item--overlay">
			<span className="customify-hb__item-handle">
				<Icon icon={ dragHandle } />
			</span>
			<span className="customify-hb__item-name">{ name }</span>
			<span
				className="customify-hb__item-btn customify-hb__item-remove"
				aria-hidden="true"
			>
				<Icon icon={ close } />
			</span>
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
