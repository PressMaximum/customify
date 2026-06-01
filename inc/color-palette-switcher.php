<?php
/**
 * Customify — Color Palette switcher (Customizer › Colors).
 *
 * Adds a "Palettes" card list at the top of the Colors section: built-in
 * presets + user-created custom palettes, with apply / new / rename / delete /
 * import / export. Applying a palette drives the EXISTING six slot color
 * pickers (same `wpColorPicker('color', hex)` path the "From palette"
 * quick-pick already uses), so nothing here re-implements color storage or
 * the derivation engine — it only orchestrates the six slot settings that
 * inc/colors-palette.php already consumes.
 *
 * 30K-site safety:
 *   - Purely ADDITIVE. Two NEW theme_mod keys (customify_active_palette,
 *     customify_color_palettes); no existing key is read differently, renamed,
 *     or removed.
 *   - Nothing renders or changes on the frontend until the user explicitly
 *     clicks a palette card (which just sets the six existing slot pickers,
 *     exactly as if they had typed the hexes by hand).
 *   - Deleting a custom palette never recolors the site — it only clears the
 *     stored list / active marker; the user's current slot values stay put.
 *
 * @package Customify
 */

defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────────────────────────────
// Built-in preset palettes (filterable). Sunrise == the theme's current
// slot defaults; Midnight (dark) + Signature (alt light) are starting
// points — final brand hexes to be tuned by the owner.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_preset_palettes' ) ) {
	function customify_color_preset_palettes() {
		$presets = array(
			array(
				'id'    => 'sunrise',
				'name'  => __( 'Sunrise', 'customify' ),
				'slots' => array(
					'base'      => '#ffffff',
					// #ECECEC matches the Surface picker's field default, so a fresh
					// site (all slots at their defaults) reads as "linked to Sunrise"
					// — no spurious "Modified" strip on first open.
					'surface'   => '#ECECEC',
					'text'      => '#2b2b2b',
					'primary'   => '#235787',
					'secondary' => '#c3512f',
					'accent'    => '#ffd042',
				),
			),
			array(
				'id'    => 'midnight',
				'name'  => __( 'Midnight', 'customify' ),
				'slots' => array(
					'base'      => '#0f1217',
					'surface'   => '#1a1e25',
					'text'      => '#e8eaed',
					'primary'   => '#5a8fc2',
					'secondary' => '#db6a44',
					'accent'    => '#ffd042',
				),
			),
		);
		return apply_filters( 'customify/color/preset_palettes', $presets );
	}
}

// ──────────────────────────────────────────────────────────────────
// Slot key → existing setting id. Single source of truth shared by PHP
// (localized to JS) and any future server-side consumer.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_slot_map' ) ) {
	function customify_color_palette_slot_map() {
		return array(
			'primary'   => 'global_styling_color_primary',
			'secondary' => 'global_styling_color_secondary',
			'accent'    => 'customify_palette_accent',
			'text'      => 'customify_palette_text',
			'surface'   => 'customify_palette_surface',
			'base'      => 'customify_palette_base',
		);
	}
}

// ──────────────────────────────────────────────────────────────────
// Sanitizer for the custom-palettes store. Hardened: every hex is run
// through customify_color_normalize_hex(), every name through
// sanitize_text_field(), the list is capped, malformed entries dropped.
// Stored as a JSON string in a single theme_mod.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_sanitize_palettes' ) ) {
	function customify_color_sanitize_palettes( $value ) {
		$arr = is_string( $value ) ? json_decode( wp_unslash( $value ), true ) : $value;
		if ( ! is_array( $arr ) ) {
			return '[]';
		}
		$keys  = array( 'base', 'surface', 'text', 'primary', 'secondary', 'accent' );
		$out   = array();
		$count = 0;
		// Built-in preset ids — a custom must never reuse one (it would be shadowed
		// by the preset in findPalette() and mis-render the active card).
		$preset_ids = array();
		if ( function_exists( 'customify_color_preset_palettes' ) ) {
			foreach ( customify_color_preset_palettes() as $pp ) {
				if ( isset( $pp['id'] ) ) {
					$preset_ids[] = $pp['id'];
				}
			}
		}
		foreach ( $arr as $p ) {
			if ( $count >= 100 ) {
				break; // hard cap — avoid unbounded theme_mod growth.
			}
			if ( ! is_array( $p ) || empty( $p['name'] ) || empty( $p['slots'] ) || ! is_array( $p['slots'] ) ) {
				continue;
			}
			$slots = array();
			$valid = true;
			foreach ( $keys as $k ) {
				if ( empty( $p['slots'][ $k ] ) || ! is_string( $p['slots'][ $k ] ) ) {
					$valid = false;
					break;
				}
				// normalize_hex returns the fallback for garbage — reject those
				// so an import can't smuggle a non-color value into the store.
				$norm = customify_color_normalize_hex( $p['slots'][ $k ], '' );
				if ( '' === $norm ) {
					$valid = false;
					break;
				}
				$slots[ $k ] = $norm;
			}
			if ( ! $valid ) {
				continue;
			}
			$id = ( isset( $p['id'] ) && is_string( $p['id'] ) && '' !== $p['id'] )
				? preg_replace( '/[^a-z0-9\-]/', '', strtolower( $p['id'] ) )
				: 'user-' . substr( md5( wp_json_encode( $slots ) . $count ), 0, 10 );
			if ( '' === $id ) {
				$id = 'user-' . substr( md5( wp_json_encode( $slots ) . $count ), 0, 10 );
			}
			if ( in_array( $id, $preset_ids, true ) ) {
				$id = 'user-' . $id; // never collide with a built-in preset id.
			}
			$name = sanitize_text_field( $p['name'] );
			$name = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 60 ) : substr( $name, 0, 60 );
			$out[] = array(
				'id'    => $id,
				'name'  => $name,
				'slots' => $slots,
			);
			$count++;
		}
		return wp_json_encode( $out );
	}
}

// ──────────────────────────────────────────────────────────────────
// Register the two additive data settings. No controls — the inline JS
// reads/writes them through wp.customize. Priority 1200 runs after the
// colors config (and force-postmessage at 1000 / preview-refresh at 1100).
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_register_settings' ) ) {
	function customify_color_palette_register_settings( $wp_customize ) {
		$wp_customize->add_setting( 'customify_active_palette', array(
			'type'              => 'theme_mod',
			'default'           => '',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_setting( 'customify_color_palettes', array(
			'type'              => 'theme_mod',
			'default'           => '[]',
			'transport'         => 'postMessage',
			'sanitize_callback' => 'customify_color_sanitize_palettes',
		) );
	}
	add_action( 'customize_register', 'customify_color_palette_register_settings', 1200 );
}

// ──────────────────────────────────────────────────────────────────
// Controls-side assets: localized data + the switcher inline script.
// NOWDOC (no PHP interpolation) keeps the jQuery `$` literal so there is
// no `\$`-escaping to get wrong.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_switcher_assets' ) ) {
	function customify_color_palette_switcher_assets() {
		$data = array(
			'presets'         => array_values( customify_color_preset_palettes() ),
			'slots'           => customify_color_palette_slot_map(),
			// Swatch-bar segment order — matches the Palette slot order shown in
			// the Customizer (brand-first: Primary → Secondary → Accent → Text
			// → Surface → Base) so the bar reads consistently with the pickers.
			'barOrder'        => array( 'primary', 'secondary', 'accent', 'text', 'surface', 'base' ),
			'activeSetting'   => 'customify_active_palette',
			'palettesSetting' => 'customify_color_palettes',
			'sectionId'       => 'sub-accordion-section-customify_colors',
			'anchorId'        => 'customize-control-customify_colors_h_palette',
			'i18n'            => array(
				'palettes'   => __( 'Palettes', 'customify' ),
				'theme'      => __( 'Theme', 'customify' ),
				'custom'     => __( 'Custom', 'customify' ),
				'newPalette' => __( 'New palette', 'customify' ),
				'name'       => __( 'Palette name', 'customify' ),
				'startFrom'  => __( 'Start from', 'customify' ),
				'current'    => __( 'Current', 'customify' ),
				'create'     => __( 'Create palette', 'customify' ),
				'cancel'     => __( 'Cancel', 'customify' ),
				'save'       => __( 'Save as new', 'customify' ),
				'saveName'   => __( 'Save', 'customify' ),
				'modified'   => __( 'Modified — diverges from', 'customify' ),
				'import'     => __( 'Import palette(s) from JSON', 'customify' ),
				'importBtn'  => __( 'Import', 'customify' ),
				'exportLbl'  => __( 'Export', 'customify' ),
				'all'        => __( 'All custom palettes', 'customify' ),
				'copy'       => __( 'Copy', 'customify' ),
				'download'   => __( 'Download', 'customify' ),
				'close'      => __( 'Close', 'customify' ),
				'rename'     => __( 'Rename', 'customify' ),
				'delete'     => __( 'Delete', 'customify' ),
				'nameReq'    => __( 'Please enter a palette name.', 'customify' ),
				'badJson'    => __( 'Invalid JSON.', 'customify' ),
				'badSlots'   => __( 'Each palette needs a name and 6 slots (base, surface, text, primary, secondary, accent).', 'customify' ),
				'applied'    => __( 'Palette applied', 'customify' ),
				'noCustom'   => __( 'No custom palettes yet — create one first.', 'customify' ),
				'linkedTip'  => __( 'Linked to palette', 'customify' ),
			),
		);

		$boot   = 'window._customifyPalettes = ' . wp_json_encode( $data ) . ";\n";
		$script = <<<'JS'
( function( $ ) {
	'use strict';
	var D = window._customifyPalettes || {};
	var PRESETS = D.presets || [];
	var SLOTMAP = D.slots || {};
	var SLOT_KEYS = [ 'base', 'surface', 'text', 'primary', 'secondary', 'accent' ];
	var BAR = D.barOrder || SLOT_KEYS;
	var ACTIVE = D.activeSetting;
	var STORE = D.palettesSetting;
	var SECTION_ID = D.sectionId;
	var ANCHOR_ID = D.anchorId;
	var T = D.i18n || {};
	// True while applyPalette() is driving the pickers, so the resulting slot
	// changes aren't mistaken for a manual edit (which would sync into a custom).
	var applying = false;

	var SVG = {
		check:  '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8.5l3.5 3.5L13 4.5"/></svg>',
		pen:    '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M11 2.5l2.5 2.5L6 12.5 3 13l.5-3z"/></svg>',
		trash:  '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h10M6.5 4V2.5h3V4M4.5 4l.5 9h6l.5-9"/></svg>',
		plus:   '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M8 3v10M3 8h10"/></svg>',
		imp:    '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v8M5 7l3 3 3-3M3 13h10"/></svg>',
		exp:    '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 11V3M5 6l3-3 3 3M3 13h10"/></svg>',
		link:   '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 9.5a2.6 2.6 0 0 0 3.7 0l2-2a2.6 2.6 0 1 0-3.7-3.7l-1 1"/><path d="M9.5 6.5a2.6 2.6 0 0 0-3.7 0l-2 2a2.6 2.6 0 1 0 3.7 3.7l1-1"/></svg>'
	};

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function decodeVal( v ) {
		if ( typeof v !== 'string' ) { return v; }
		try { return JSON.parse( decodeURI( v ) ); } catch ( e ) { return v; }
	}
	function readSetting( id ) {
		try { return wp.customize( id ).get(); } catch ( e ) { return undefined; }
	}
	function readSlotHex( key ) {
		var v = decodeVal( readSetting( SLOTMAP[ key ] ) );
		return ( typeof v === 'string' && v ) ? v.toLowerCase() : '';
	}
	function currentSlots() {
		var s = {};
		SLOT_KEYS.forEach( function( k ) { s[ k ] = readSlotHex( k ); } );
		return s;
	}
	function getCustoms() {
		try { var a = JSON.parse( readSetting( STORE ) || '[]' ); return Array.isArray( a ) ? a : []; }
		catch ( e ) { return []; }
	}
	function setCustoms( arr ) { try { wp.customize( STORE ).set( JSON.stringify( arr ) ); } catch ( e ) {} }
	function getActive() { return readSetting( ACTIVE ) || ''; }
	function setActive( id ) { try { wp.customize( ACTIVE ).set( id || '' ); } catch ( e ) {} }

	function allPalettes() {
		var theme = PRESETS.map( function( p ) { return { id: p.id, name: p.name, kind: 'theme', slots: p.slots }; } );
		var user = getCustoms().map( function( p ) { return { id: p.id, name: p.name, kind: 'user', slots: p.slots }; } );
		return theme.concat( user );
	}
	function findPalette( id ) {
		var all = allPalettes(), i;
		for ( i = 0; i < all.length; i++ ) { if ( all[ i ].id === id ) { return all[ i ]; } }
		return null;
	}
	function slotsMatch( a, b ) {
		return SLOT_KEYS.every( function( k ) { return ( a[ k ] || '' ).toLowerCase() === ( b[ k ] || '' ).toLowerCase(); } );
	}

	// Apply a palette by driving the existing slot color pickers — the exact
	// path the "From palette" quick-pick already uses, so the value is stored
	// in the canonical format and live preview + save behave identically.
	function applyPalette( slots ) {
		applying = true;
		try {
			// Re-anchor each picker's reset/dirty baseline to the NEW palette BEFORE
			// driving it, so the pickers' own dirty-check doesn't flash all six reset
			// arrows during the switch.
			syncSlotDefaults( { slots: slots }, true );
			SLOT_KEYS.forEach( function( k ) {
				var setting = SLOTMAP[ k ], hex = slots[ k ];
				if ( ! setting || ! hex ) { return; }
				var $panel = $( '#customize-control-' + setting + ' .customify--color-panel' );
				if ( $panel.length && $.fn.wpColorPicker ) {
					$panel.wpColorPicker( 'color', hex );
				} else {
					try { wp.customize( setting ).set( hex ); } catch ( e ) {}
				}
			} );
		} finally {
			// Always release — even if a picker throws mid-loop — so a stuck
			// `applying` flag can't silently disable the custom auto-sync for the
			// rest of the session. Release after the picker-driven changes settle
			// so a real edit afterward is treated as manual.
			setTimeout( function() { applying = false; }, 250 );
		}
	}

	function barSegs( slots ) {
		return BAR.map( function( k ) {
			return '<span class="cps-seg" style="background:' + esc( slots[ k ] || '#000000' ) + '"></span>';
		} ).join( '' );
	}

	// ---- panels (add / import / export) ----
	var panel = null;       // 'add' | 'import' | 'export' | null
	var fromSel = 'current';
	var exportSel = 'all';

	function chips() {
		var opts = [ { id: 'current', name: T.current, slots: currentSlots() } ].concat( allPalettes() );
		return opts.map( function( o ) {
			var mini = BAR.map( function( k ) { return '<span style="background:' + esc( o.slots[ k ] || '#000000' ) + '"></span>'; } ).join( '' );
			return '<span class="cps-chip' + ( o.id === fromSel ? ' is-sel' : '' ) + '" data-from="' + esc( o.id ) + '">'
				+ '<span class="cps-mini">' + mini + '</span>' + esc( o.name ) + '</span>';
		} ).join( '' );
	}
	function panelHtml() {
		if ( panel === 'add' ) {
			return '<div class="cps-form">'
				+ '<label>' + esc( T.name ) + '</label>'
				+ '<input type="text" class="cps-name-in" autocomplete="off" placeholder="My Brand">'
				+ '<div class="cps-err" data-err></div>'
				+ '<label>' + esc( T.startFrom ) + '</label>'
				+ '<div class="cps-from">' + chips() + '</div>'
				+ '<div class="cps-row"><button type="button" class="button-link" data-act="cancel">' + esc( T.cancel ) + '</button>'
				+ '<button type="button" class="button button-primary" data-act="create">' + esc( T.create ) + '</button></div>'
				+ '</div>';
		}
		if ( panel === 'import' ) {
			return '<div class="cps-form">'
				+ '<label>' + esc( T.import ) + '</label>'
				+ '<textarea class="cps-json" rows="5" spellcheck="false" placeholder=\'{"name":"My Brand","slots":{"base":"#fff", ...}}\'></textarea>'
				+ '<div class="cps-err" data-err></div>'
				+ '<div class="cps-row"><button type="button" class="button-link" data-act="cancel">' + esc( T.cancel ) + '</button>'
				+ '<button type="button" class="button button-primary" data-act="doimport">' + esc( T.importBtn ) + '</button></div>'
				+ '</div>';
		}
		if ( panel === 'export' ) {
			var customs = getCustoms();
			if ( exportSel !== 'all' && ! customs.some( function( p ) { return p.id === exportSel; } ) ) { exportSel = 'all'; }
			var options = [ '<option value="all">' + esc( T.all ) + ' (' + customs.length + ')</option>' ]
				.concat( customs.map( function( p ) { return '<option value="' + esc( p.id ) + '"' + ( p.id === exportSel ? ' selected' : '' ) + '>' + esc( p.name ) + '</option>'; } ) ).join( '' );
			return '<div class="cps-form">'
				+ '<label>' + esc( T.exportLbl ) + '</label>'
				+ '<select class="cps-exp-sel"' + ( customs.length ? '' : ' disabled' ) + '>' + options + '</select>'
				+ '<textarea class="cps-exp-json" rows="5" readonly spellcheck="false">' + esc( exportJsonStr() ) + '</textarea>'
				+ '<div class="cps-row"><button type="button" class="button-link" data-act="cancel">' + esc( T.close ) + '</button>'
				+ '<button type="button" class="button" data-act="copy"' + ( customs.length ? '' : ' disabled' ) + '>' + esc( T.copy ) + '</button>'
				+ '<button type="button" class="button button-primary" data-act="download"' + ( customs.length ? '' : ' disabled' ) + '>' + esc( T.download ) + '</button></div>'
				+ '</div>';
		}
		return '';
	}
	function exportSubset() {
		var customs = getCustoms();
		if ( exportSel !== 'all' ) {
			var one = customs.filter( function( p ) { return p.id === exportSel; } );
			if ( one.length ) { return one; }
		}
		return customs;
	}
	function exportJsonStr() {
		var sub = exportSubset();
		return sub.length
			? JSON.stringify( sub.map( function( p ) { return { name: p.name, slots: p.slots }; } ), null, 2 )
			: '// ' + T.noCustom;
	}

	// Re-anchor each slot picker's "default" (the reset target + the dirty-glyph
	// baseline) to the ACTIVE palette's value. Without this the pickers compare
	// against the theme field-default, so every non-Sunrise palette lights up all
	// six reset arrows and "reset" yanks a slot back to the theme baseline —
	// breaking the chosen palette, or silently corrupting a custom one.
	function syncSlotDefaults( activePal, skipGlyph ) {
		if ( ! activePal || ! activePal.slots ) { return; }
		SLOT_KEYS.forEach( function( k ) {
			var setting = SLOTMAP[ k ];
			var palVal = ( activePal.slots[ k ] || '' ).toLowerCase();
			if ( ! palVal || ! setting ) { return; }
			var li = document.getElementById( 'customize-control-' + setting );
			if ( ! li ) { return; }
			try { $( li ).find( '.customify--color-panel' ).wpColorPicker( 'option', 'defaultColor', palVal ); } catch ( e ) {}
			var input = li.querySelector( 'input.wp-color-picker' );
			if ( input ) { input.setAttribute( 'data-default-color', palVal ); }
			var inner = li.querySelector( '.customify-input-color' );
			if ( inner ) {
				inner.setAttribute( 'data-default', palVal );
				// During a palette switch (applying) the theme's own refreshDirtyState
				// clears the glyph off the picker change events — skip our toggle so a
				// render mid-switch can't flash a stale "dirty" state.
				if ( ! skipGlyph && ! applying ) {
					var cur = ( readSlotHex( k ) || '' ).toLowerCase();
					inner.classList.toggle( 'is-dirty', !! cur && cur !== palVal );
				}
			}
		} );
	}

	function render() {
		if ( ! window.wp || ! wp.customize ) { return; }
		var section = document.getElementById( SECTION_ID );
		if ( ! section ) { return; }
		var host = document.getElementById( 'customify-palette-switcher' );
		if ( ! host ) {
			host = document.createElement( 'li' );
			host.id = 'customify-palette-switcher';
			host.className = 'customize-control customify-cps';
			var anchor = document.getElementById( ANCHOR_ID );
			if ( anchor && anchor.parentNode ) {
				anchor.parentNode.insertBefore( host, anchor );
			} else {
				var content = section.querySelector( '.customize-section-content' ) || section.querySelector( 'ul' ) || section;
				content.insertBefore( host, content.firstChild );
			}
		}

		var cur = currentSlots();
		var pals = allPalettes();
		var stored = getActive();            // the explicitly-applied palette ('' if none)
		var storedPal = findPalette( stored );
		var activePal = null, linked = false;
		if ( storedPal && slotsMatch( cur, storedPal.slots ) ) {
			activePal = storedPal; linked = true;          // on the applied palette, untouched
		} else {
			// Do the live slots exactly match SOME palette? (e.g. a section "reset
			// to default" restores Sunrise's values.) Adopt it so the card + state
			// stay honest — and so a legacy site with custom colours that never
			// used palettes shows NOTHING active, not a bogus "Sunrise, modified"
			// with every slot flagged dirty.
			var matched = pals.filter( function( p ) { return slotsMatch( cur, p.slots ); } )[ 0 ];
			if ( matched ) {
				activePal = matched; linked = true;
				// Persist the adopted id only when the customizer is ALREADY dirty
				// (a user action like a reset) — never on a clean open, which would
				// make merely viewing Colors look like an unsaved change.
				var dirty = false;
				try { dirty = wp.customize.state( 'saved' ).get() === false; } catch ( e ) {}
				if ( dirty && stored !== matched.id ) { setActive( matched.id ); }
			} else {
				activePal = storedPal; linked = false;     // null when a legacy/custom site never applied a palette
			}
		}
		var active = activePal ? activePal.id : '';

		// On a custom palette the slots ARE the palette, so the per-slot "reset to
		// theme default" glyph is meaningless — flag the section so CSS suppresses
		// it (and its gutter). Otherwise the glyph flashes on/off while editing,
		// because the dirty state is computed against the theme default + Iris
		// rewrites the data-default attribute we can't reliably override in JS.
		if ( section ) { section.classList.toggle( 'cps-active-custom', !! ( activePal && activePal.kind === 'user' ) ); }

		var cards = pals.map( function( p ) {
			var on = ( p.id === active );
			var badge = p.kind === 'theme'
				? '<span class="cps-badge">' + esc( T.theme ) + '</span>'
				: '<span class="cps-badge cps-custom">' + esc( T.custom ) + '</span>';
			var acts = p.kind === 'user'
				? '<span class="cps-actions"><button type="button" class="cps-icon" data-act="rename" data-id="' + esc( p.id ) + '" aria-label="' + esc( T.rename ) + '" data-tip="' + esc( T.rename ) + '">' + SVG.pen + '</button>'
				+ '<button type="button" class="cps-icon" data-act="delete" data-id="' + esc( p.id ) + '" aria-label="' + esc( T.delete ) + '" data-tip="' + esc( T.delete ) + '">' + SVG.trash + '</button></span>'
				: '';
			return '<div class="cps-card' + ( on ? ' is-active' : '' ) + ( on && linked ? ' is-linked' : '' ) + '" data-id="' + esc( p.id ) + '" tabindex="0" role="button">'
				+ '<div class="cps-top"><span class="cps-name">' + esc( p.name ) + '</span>' + badge
				+ '<span class="cps-spacer"></span>' + acts
				+ '<span class="cps-link" title="' + esc( T.linkedTip ) + '">' + SVG.link + '</span>'
				+ '<span class="cps-check">' + SVG.check + '</span></div>'
				+ '<div class="cps-bar">' + barSegs( p.slots ) + '</div></div>';
		} ).join( '' );

		// "Modified — diverges from X" is only meaningful for read-only THEME
		// presets. A custom palette absorbs edits live (maybeSyncCustom), so it
		// never diverges from itself — no strip.
		var modified = ( activePal && activePal.kind === 'theme' && ! linked )
			? '<div class="cps-modified"><span>' + esc( T.modified + ' ' + activePal.name ) + '</span>'
			+ '<button type="button" class="button button-small" data-act="saveas">' + esc( T.save ) + '</button></div>'
			: '';

		host.innerHTML =
			'<div class="cps-head"><span class="cps-title">' + esc( T.palettes ) + '</span>'
			+ '<span class="cps-tools">'
			+   '<button type="button" class="cps-icon" data-act="import" aria-label="' + esc( T.importBtn ) + '" data-tip="' + esc( T.importBtn ) + '">' + SVG.imp + '</button>'
			+   '<button type="button" class="cps-icon" data-act="export" aria-label="' + esc( T.exportLbl ) + '" data-tip="' + esc( T.exportLbl ) + '">' + SVG.exp + '</button>'
			+   '<button type="button" class="cps-icon" data-act="add" aria-label="' + esc( T.newPalette ) + '" data-tip="' + esc( T.newPalette ) + '">' + SVG.plus + '</button>'
			+ '</span></div>'
			+ '<div class="cps-panel">' + panelHtml() + '</div>'
			+ '<div class="cps-cards">' + cards + '</div>'
			+ modified;

		// Keep the 6 slot pickers' reset/dirty baseline pointed at the active
		// palette (see syncSlotDefaults).
		syncSlotDefaults( activePal );
	}

	// ---- event delegation ----
	$( document ).on( 'click', '#customify-palette-switcher [data-act]', function( e ) {
		e.preventDefault();
		e.stopPropagation();
		var act = this.getAttribute( 'data-act' );
		var id = this.getAttribute( 'data-id' );
		var host = document.getElementById( 'customify-palette-switcher' );

		if ( act === 'add' ) { panel = ( panel === 'add' ? null : 'add' ); fromSel = 'current'; render(); focusFirst(); return; }
		if ( act === 'import' ) { panel = ( panel === 'import' ? null : 'import' ); render(); focusFirst(); return; }
		if ( act === 'export' ) { panel = ( panel === 'export' ? null : 'export' ); exportSel = 'all'; render(); return; }
		if ( act === 'cancel' ) { panel = null; render(); return; }

		if ( act === 'create' ) {
			var name = ( host.querySelector( '.cps-name-in' ).value || '' ).trim();
			var errEl = host.querySelector( '[data-err]' );
			if ( ! name ) { if ( errEl ) { errEl.textContent = T.nameReq; } return; }
			var base = ( fromSel === 'current' ) ? currentSlots() : ( findPalette( fromSel ) || {} ).slots;
			if ( ! base ) { base = currentSlots(); }
			var slots = {};
			SLOT_KEYS.forEach( function( k ) { slots[ k ] = base[ k ] || '#000000'; } );
			var pid = 'user-' + Date.now();
			var customs = getCustoms();
			customs.push( { id: pid, name: name, slots: slots } );
			setCustoms( customs );
			panel = null;
			applyPalette( slots );
			setActive( pid );
			setTimeout( render, 60 );
			return;
		}

		if ( act === 'doimport' ) {
			var ta = host.querySelector( '.cps-json' );
			var ierr = host.querySelector( '[data-err]' );
			var data;
			try { data = JSON.parse( ta.value ); } catch ( ex ) { if ( ierr ) { ierr.textContent = T.badJson; } return; }
			var list = Array.isArray( data ) ? data : [ data ];
			var customs2 = getCustoms();
			var added = [];
			for ( var i = 0; i < list.length; i++ ) {
				var p = list[ i ];
				if ( ! p || typeof p.name !== 'string' || ! p.name.trim() || ! p.slots ) { if ( ierr ) { ierr.textContent = T.badSlots; } return; }
				var ok = true, sl = {};
				for ( var j = 0; j < SLOT_KEYS.length; j++ ) {
					var v = p.slots[ SLOT_KEYS[ j ] ];
					if ( ! v || typeof v !== 'string' ) { ok = false; break; }
					sl[ SLOT_KEYS[ j ] ] = v;
				}
				if ( ! ok ) { if ( ierr ) { ierr.textContent = T.badSlots; } return; }
				var np = { id: 'user-' + Date.now() + '-' + i, name: p.name.trim(), slots: sl };
				customs2.push( np ); added.push( np );
			}
			setCustoms( customs2 );
			panel = null;
			if ( added.length ) { applyPalette( added[ added.length - 1 ].slots ); setActive( added[ added.length - 1 ].id ); }
			setTimeout( render, 60 );
			return;
		}

		if ( act === 'copy' ) {
			var tx = host.querySelector( '.cps-exp-json' );
			if ( tx && navigator.clipboard ) { navigator.clipboard.writeText( tx.value ); }
			return;
		}
		if ( act === 'download' ) {
			var sub = exportSubset();
			var fname = ( exportSel !== 'all' && sub.length === 1 )
				? 'customify-palette-' + String( sub[ 0 ].name ).toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-|-$/g, '' ) + '.json'
				: 'customify-palettes.json';
			var blob = new Blob( [ exportJsonStr() ], { type: 'application/json' } );
			var url = URL.createObjectURL( blob );
			var a = document.createElement( 'a' ); a.href = url; a.download = fname; a.click(); URL.revokeObjectURL( url );
			return;
		}

		if ( act === 'rename' && id ) {
			var customs3 = getCustoms();
			var target = customs3.filter( function( p ) { return p.id === id; } )[ 0 ];
			if ( ! target ) { return; }
			var card = this.closest( '.cps-card' );
			var nameEl = card ? card.querySelector( '.cps-name' ) : null;
			if ( ! nameEl ) { return; }
			card.classList.add( 'is-renaming' ); // CSS hides badge / actions / check while editing
			var inp = document.createElement( 'input' );
			inp.type = 'text'; inp.className = 'cps-rename-in'; inp.value = target.name; inp.setAttribute( 'aria-label', T.rename );
			nameEl.replaceWith( inp );
			var saveBtn = document.createElement( 'button' );
			saveBtn.type = 'button'; saveBtn.className = 'cps-icon cps-rename-save'; saveBtn.innerHTML = SVG.check;
			saveBtn.setAttribute( 'aria-label', T.saveName ); saveBtn.setAttribute( 'data-tip', T.saveName );
			inp.parentNode.insertBefore( saveBtn, inp.nextSibling );
			inp.focus(); inp.select();
			var done = false;
			var commit = function() {
				if ( done ) { return; }
				done = true;
				var nv = ( inp.value || '' ).trim();
				if ( nv ) { target.name = nv; setCustoms( customs3 ); }
				render();
			};
			var cancel = function() { if ( done ) { return; } done = true; render(); };
			inp.addEventListener( 'click', function( ev ) { ev.stopPropagation(); } );
			inp.addEventListener( 'mousedown', function( ev ) { ev.stopPropagation(); } );
			inp.addEventListener( 'blur', commit );
			inp.addEventListener( 'keydown', function( ev ) {
				ev.stopPropagation(); // keep Space / Enter from bubbling to the card-activate handler
				if ( ev.key === 'Enter' ) { ev.preventDefault(); commit(); }
				else if ( ev.key === 'Escape' ) { ev.preventDefault(); cancel(); }
			} );
			// mousedown fires before the field's blur, so the save click commits cleanly.
			saveBtn.addEventListener( 'mousedown', function( ev ) { ev.preventDefault(); ev.stopPropagation(); commit(); } );
			return;
		}

		if ( act === 'delete' && id ) {
			// 30K-safe: deleting NEVER recolors — just drop from the list and
			// clear the active marker if it pointed here. Current slot values
			// (what the site actually renders) are left untouched.
			var customs4 = getCustoms().filter( function( p ) { return p.id !== id; } );
			setCustoms( customs4 );
			if ( getActive() === id ) { setActive( '' ); }
			setTimeout( render, 60 );
			return;
		}

		if ( act === 'saveas' ) {
			var slotsNow = currentSlots();
			var sn = {};
			SLOT_KEYS.forEach( function( k ) { sn[ k ] = slotsNow[ k ] || '#000000'; } );
			var base = findPalette( getActive() );
			var nm = ( base ? base.name : T.custom ) + ' +';
			var nid = 'user-' + Date.now();
			var cs = getCustoms();
			cs.push( { id: nid, name: nm, slots: sn } );
			setCustoms( cs );
			setActive( nid );
			setTimeout( render, 60 );
			return;
		}
	} );

	// from-chip selection inside the add panel
	$( document ).on( 'click', '#customify-palette-switcher .cps-chip', function( e ) {
		e.preventDefault(); e.stopPropagation();
		fromSel = this.getAttribute( 'data-from' );
		var host = document.getElementById( 'customify-palette-switcher' );
		$( host ).find( '.cps-chip' ).each( function() {
			this.classList.toggle( 'is-sel', this.getAttribute( 'data-from' ) === fromSel );
		} );
	} );

	// export selector change
	$( document ).on( 'change', '#customify-palette-switcher .cps-exp-sel', function() {
		exportSel = this.value;
		var host = document.getElementById( 'customify-palette-switcher' );
		var ta = host.querySelector( '.cps-exp-json' );
		if ( ta ) { ta.value = exportJsonStr(); }
	} );

	// apply a palette by clicking its card (ignore clicks on inner buttons/inputs)
	function cardActivate( card ) {
		var pid = card.getAttribute( 'data-id' );
		var p = findPalette( pid );
		if ( ! p ) { return; }
		// Colours already match this palette — nothing to apply. Skip the re-render
		// so a click/drag on the name can select its text (and no needless
		// "unsaved changes" from re-applying what's already shown).
		if ( slotsMatch( currentSlots(), p.slots ) ) { return; }
		applyPalette( p.slots );  // drive the pickers (≈7ms) — slots are now the palette
		setActive( pid );
		render();                 // instant active-state feedback (no 60ms wait)
	}
	$( document ).on( 'click', '#customify-palette-switcher .cps-card', function( e ) {
		// Ignore the inline rename field + action buttons; clicking anywhere else
		// (including the name) applies the palette.
		if ( e.target.closest( '[data-act]' ) || e.target.closest( '.cps-rename-in' ) ) { return; }
		cardActivate( this );
	} );
	$( document ).on( 'keydown', '#customify-palette-switcher .cps-card', function( e ) {
		if ( e.target && e.target.tagName === 'INPUT' ) { return; } // don't hijack typing in the rename field
		if ( e.key === 'Enter' || e.key === ' ' ) { e.preventDefault(); cardActivate( this ); }
	} );

	function focusFirst() {
		var host = document.getElementById( 'customify-palette-switcher' );
		if ( ! host ) { return; }
		var el = host.querySelector( '.cps-name-in, .cps-json' );
		if ( el ) { el.focus(); }
	}

	// ---- mount + live refresh ----
	var rt = null;
	function scheduleRender() {
		clearTimeout( rt );
		rt = setTimeout( function() {
			// Only the debounced (setting-bind) path defers to an open form /
			// inline rename so a slot change mid-typing can't wipe it. Direct
			// render() calls from the action handlers always rebuild.
			var host = document.getElementById( 'customify-palette-switcher' );
			if ( host && host.querySelector( '.cps-form, .cps-rename-in' ) && host.contains( document.activeElement ) ) { return; }
			render();
		}, 90 );
	}

	// When a slot is edited manually while a CUSTOM palette is active, fold the
	// new values into that palette so its card swatch + stored definition stay in
	// sync (and persist on save). No-op when already in sync → no feedback loop.
	function maybeSyncCustom() {
		if ( applying ) { return; }
		var act = getActive();
		var pal = findPalette( act );
		if ( ! pal || pal.kind !== 'user' ) { return; }
		var cur = currentSlots();
		if ( slotsMatch( cur, pal.slots ) ) { return; }
		var customs = getCustoms().map( function( p ) {
			return p.id === act ? { id: p.id, name: p.name, slots: cur } : p;
		} );
		setCustoms( customs );
	}
	var srt = null;
	function onSlotEdit() {
		if ( ! applying ) { clearTimeout( srt ); srt = setTimeout( maybeSyncCustom, 100 ); }
		scheduleRender();
	}
	function bindSettings() {
		[ ACTIVE, STORE ].forEach( function( id ) {
			try { wp.customize( id, function( s ) { s.bind( scheduleRender ); } ); } catch ( e ) {}
		} );
		Object.keys( SLOTMAP ).forEach( function( k ) {
			try { wp.customize( SLOTMAP[ k ], function( s ) { s.bind( onSlotEdit ); } ); } catch ( e ) {}
		} );
	}

	function mount() {
		if ( ! window.wp || ! wp.customize ) { return setTimeout( mount, 300 ); }
		wp.customize.bind( 'ready', function() {
			render();
			bindSettings();
			// section may render its controls lazily — re-render a couple times.
			setTimeout( render, 400 );
			setTimeout( render, 1200 );
		} );
		// also try immediately in case 'ready' already fired
		setTimeout( function() { render(); bindSettings(); }, 600 );
	}
	$( mount );
} )( jQuery );
JS;

		wp_add_inline_script( 'customize-controls', $boot . $script );
	}
	add_action( 'customize_controls_enqueue_scripts', 'customify_color_palette_switcher_assets' );
}

// ──────────────────────────────────────────────────────────────────
// Controls-side CSS — WordPress-admin look (native buttons/active blue),
// with a hairline border around every swatch color so a white slot can't
// melt into the card.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_switcher_css' ) ) {
	function customify_color_palette_switcher_css() {
		?>
<style id="customify-palette-switcher-css">
#customify-palette-switcher.customify-cps { padding: 12px 0 14px; }
.customify-cps .cps-head { display: flex; align-items: center; margin: 0 0 8px; }
.customify-cps .cps-title { flex: 1; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #50575e; }
.customify-cps .cps-tools { display: flex; gap: 2px; }
.customify-cps .cps-icon { position: relative; width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 0; background: transparent; color: #50575e; border-radius: 4px; cursor: pointer; }
.customify-cps .cps-icon[data-tip]:hover::after { content: attr(data-tip); position: absolute; top: 100%; right: 0; margin-top: 4px; padding: 3px 7px; background: #1d2327; color: #fff; font-size: 11px; line-height: 1.5; white-space: nowrap; border-radius: 4px; pointer-events: none; z-index: 100; }
.customify-cps .cps-icon:hover { background: #f0f0f1; color: #1d2327; }
.customify-cps .cps-icon svg { width: 16px; height: 16px; }
.customify-cps .cps-cards { display: flex; flex-direction: column; gap: 6px; }
.customify-cps .cps-card { position: relative; background: #fff; border: 1px solid #dcdcde; border-radius: 4px; padding: 8px 10px; cursor: pointer; }
.customify-cps .cps-card:hover { border-color: #8c8f94; }
.customify-cps .cps-card.is-active { border-color: var(--wp-admin-theme-color, #3858e9); box-shadow: 0 0 0 1px var(--wp-admin-theme-color, #3858e9); }
.customify-cps .cps-top { display: flex; align-items: center; gap: 6px; min-height: 20px; }
.customify-cps .cps-name { min-width: 0; font-size: 13px; font-weight: 600; color: #1d2327; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; user-select: text; }
/* Only the name shrinks (truncates) — badge / check / icons keep their size so
   the check circle can't get squished into an oval when the name is long. */
.customify-cps .cps-badge, .customify-cps .cps-check, .customify-cps .cps-link, .customify-cps .cps-actions { flex-shrink: 0; }
.customify-cps .cps-rename-in { flex: 1 1 auto; min-width: 0; font-size: 13px; font-weight: 600; padding: 1px 5px; border: 1px solid var(--wp-admin-theme-color, #3858e9); border-radius: 3px; }
/* While renaming, hide everything else on the row so the field + save button own it. */
.customify-cps .cps-card.is-renaming .cps-badge,
.customify-cps .cps-card.is-renaming .cps-actions,
.customify-cps .cps-card.is-renaming .cps-check,
.customify-cps .cps-card.is-renaming .cps-link,
.customify-cps .cps-card.is-renaming .cps-spacer { display: none !important; }
.customify-cps .cps-rename-save { color: var(--wp-admin-theme-color, #3858e9); flex-shrink: 0; }
.customify-cps .cps-rename-save:hover { background: color-mix(in srgb, var(--wp-admin-theme-color, #3858e9) 12%, #fff); color: var(--wp-admin-theme-color-darker-10, #2145e6); }
.customify-cps .cps-badge { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; padding: 1px 5px; border-radius: 9px; background: #f0f0f1; color: #646970; }
.customify-cps .cps-badge.cps-custom { background: color-mix(in srgb, var(--wp-admin-theme-color, #3858e9) 12%, #fff); color: var(--wp-admin-theme-color, #3858e9); }
.customify-cps .cps-spacer { flex: 1; }
.customify-cps .cps-actions { display: none; gap: 1px; }
/* Card action icons match the row height so revealing them on hover doesn't grow the card (no jump). */
.customify-cps .cps-actions .cps-icon { width: 20px; height: 20px; }
.customify-cps .cps-actions .cps-icon svg { width: 14px; height: 14px; }
.customify-cps .cps-card:hover .cps-actions { display: flex; }
.customify-cps .cps-card.is-active .cps-actions { display: none; }
.customify-cps .cps-card.is-active:hover .cps-actions { display: flex; }
.customify-cps .cps-link { display: none; color: #50575e; opacity: .7; }
.customify-cps .cps-link svg { width: 14px; height: 14px; }
.customify-cps .cps-card.is-linked:not(:hover) .cps-link { display: inline-flex; }
.customify-cps .cps-check { width: 18px; height: 18px; border-radius: 50%; background: var(--wp-admin-theme-color, #3858e9); color: #fff; display: none; align-items: center; justify-content: center; }
.customify-cps .cps-check svg { width: 12px; height: 12px; }
.customify-cps .cps-card.is-active .cps-check { display: inline-flex; }
.customify-cps .cps-card.is-active.is-linked .cps-link { display: none; }
/* Card preview = 6 round swatches (slot-picker style), compact + left-aligned with a fixed gap. */
.customify-cps .cps-bar { display: flex; justify-content: flex-start; gap: 7px; align-items: center; margin-top: 9px; }
.customify-cps .cps-seg { flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; border: 1px solid rgba(0,0,0,0.15); box-shadow: 0 1px 2px rgba(0,0,0,0.08); box-sizing: border-box; }
.customify-cps .cps-modified { display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 8px 10px; background: #fcf9e8; border: 1px solid #f0e2a0; border-radius: 4px; font-size: 12px; color: #7a5c00; }
.customify-cps .cps-modified span { flex: 1; }
.customify-cps .cps-panel:empty { display: none; }
.customify-cps .cps-panel { margin-bottom: 8px; }
.customify-cps .cps-form { background: #fff; border: 1px solid var(--wp-admin-theme-color, #3858e9); border-radius: 4px; padding: 10px; display: flex; flex-direction: column; gap: 8px; }
.customify-cps .cps-form label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; color: #646970; }
.customify-cps .cps-form input[type=text], .customify-cps .cps-form textarea, .customify-cps .cps-form select { width: 100%; box-sizing: border-box; }
.customify-cps .cps-form textarea { font-family: ui-monospace, Menlo, monospace; font-size: 11px; }
.customify-cps .cps-row { display: flex; gap: 6px; justify-content: flex-end; align-items: center; }
.customify-cps .cps-err { color: #d63638; font-size: 11px; min-height: 13px; }
.customify-cps .cps-err:empty { min-height: 0; }
.customify-cps .cps-from { display: flex; flex-wrap: wrap; gap: 5px; }
.customify-cps .cps-chip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px 3px 5px; border-radius: 12px; background: #f6f7f7; border: 1.5px solid transparent; cursor: pointer; font-size: 12px; color: #50575e; }
.customify-cps .cps-chip.is-sel { border-color: var(--wp-admin-theme-color, #3858e9); background: color-mix(in srgb, var(--wp-admin-theme-color, #3858e9) 8%, #fff); color: #1d2327; }
.customify-cps .cps-chip .cps-mini { display: flex; width: 18px; height: 14px; border-radius: 3px; overflow: hidden; box-shadow: inset 0 0 0 1px rgba(0,0,0,.18); }
.customify-cps .cps-chip .cps-mini span { flex: 1; box-shadow: inset 0 0 0 1px rgba(0,0,0,.18); }
/* When a CUSTOM palette is active, the slots already ARE that palette's values,
   so suppress the per-slot reset glyph + its reserved gutter. Without this the
   reset button flashes on then off while editing (dirty is computed vs the theme
   default, and Iris rewrites the data-default we can't override in JS). */
#sub-accordion-section-customify_colors.cps-active-custom .customify-input-color.is-dirty .wp-picker-input-wrap input.wp-picker-default { display: none !important; }
#sub-accordion-section-customify_colors.cps-active-custom .customize-control-title,
#sub-accordion-section-customify_colors.cps-active-custom .customize-control-description { padding-right: 0 !important; }
</style>
		<?php
	}
	add_action( 'customize_controls_print_styles', 'customify_color_palette_switcher_css' );
}
