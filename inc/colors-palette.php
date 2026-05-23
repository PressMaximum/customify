<?php
/**
 * Customify Colors palette — :root CSS variable emitter.
 *
 * Emits a `:root` block declaring the 6 slot vars and ~10 derived vars used by
 * the new Colors panel. Derived vars use a static (PHP-precomputed) fallback
 * plus an `@supports (color-mix)` refinement so modern browsers can live-mix
 * while older browsers still see a sensible static color.
 *
 * Override mechanism: a derived var is only emitted as "computed" when its
 * corresponding legacy theme_mod key has NO saved value. If the legacy key is
 * saved, the derived var is locked to the saved value so 30K+ legacy sites
 * render byte-identical to before.
 *
 * @package Customify
 */

defined( 'ABSPATH' ) || exit;

// ──────────────────────────────────────────────────────────────────
// Color math helpers (sRGB-space; good enough for fallback hex).
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_normalize_hex' ) ) {
	/**
	 * Validate + normalize a color value to #rrggbb form. Accepts 3- or 6-char
	 * hex (with or without leading #). Returns $fallback for anything else
	 * (empty string, invalid chars, wrong length, rgba, var(), etc.).
	 *
	 * Use this on every read of a user-saved color value before feeding it into
	 * CSS output or math helpers — wp-cli and external code can bypass the
	 * Customizer sanitize_callback and write raw values.
	 */
	function customify_color_normalize_hex( $value, $fallback = '#000000' ) {
		if ( ! is_string( $value ) ) {
			return $fallback;
		}
		$hex = ltrim( trim( $value ), '#' );
		if ( strlen( $hex ) === 3 && ctype_xdigit( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return $fallback;
		}
		return '#' . strtolower( $hex );
	}
}

if ( ! function_exists( 'customify_color_hex_to_rgb' ) ) {
	function customify_color_hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( strlen( $hex ) === 3 && ctype_xdigit( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return array( 0, 0, 0 );
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}

if ( ! function_exists( 'customify_color_rgb_to_hex' ) ) {
	function customify_color_rgb_to_hex( $rgb ) {
		return sprintf( '#%02x%02x%02x',
			max( 0, min( 255, (int) round( $rgb[0] ) ) ),
			max( 0, min( 255, (int) round( $rgb[1] ) ) ),
			max( 0, min( 255, (int) round( $rgb[2] ) ) )
		);
	}
}

if ( ! function_exists( 'customify_color_mix_hex' ) ) {
	/**
	 * Mix two hex colors in sRGB space.
	 *
	 * @param string $a       First color hex.
	 * @param string $b       Second color hex.
	 * @param float  $weight_a Weight of color A in [0,1]. 1.0 = pure A, 0.0 = pure B.
	 * @return string Mixed hex.
	 */
	function customify_color_mix_hex( $a, $b, $weight_a ) {
		$weight_a = max( 0.0, min( 1.0, (float) $weight_a ) );
		$wb       = 1.0 - $weight_a;
		$ra       = customify_color_hex_to_rgb( $a );
		$rb       = customify_color_hex_to_rgb( $b );
		return customify_color_rgb_to_hex( array(
			$ra[0] * $weight_a + $rb[0] * $wb,
			$ra[1] * $weight_a + $rb[1] * $wb,
			$ra[2] * $weight_a + $rb[2] * $wb,
		) );
	}
}

if ( ! function_exists( 'customify_color_relative_luminance' ) ) {
	function customify_color_relative_luminance( $hex ) {
		list( $r, $g, $b ) = customify_color_hex_to_rgb( $hex );
		$f = function ( $v ) {
			$v = $v / 255;
			return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
		};
		return 0.2126 * $f( $r ) + 0.7152 * $f( $g ) + 0.0722 * $f( $b );
	}
}

if ( ! function_exists( 'customify_color_pick_on' ) ) {
	/**
	 * Pick contrast-safe text color (#1A1A1A or #FFFFFF) for a given background.
	 */
	function customify_color_pick_on( $bg_hex ) {
		return customify_color_relative_luminance( $bg_hex ) > 0.45 ? '#1A1A1A' : '#FFFFFF';
	}
}

// ──────────────────────────────────────────────────────────────────
// Slot resolver — reads 6 slot keys with defaults.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_get_slots' ) ) {
	function customify_color_get_slots() {
		// Slot primary / secondary reuse existing legacy theme_mod keys to keep
		// storage compatibility with 30K+ sites. Every read is normalized to
		// guard against wp-cli / external writes that bypass sanitize_callback.
		$defaults = array(
			'base'      => '#FFFFFF',
			'surface'   => '#ECECEC',
			'text'      => '#2b2b2b',
			'primary'   => '#235787',
			'secondary' => '#c3512f',
			'accent'    => '#FFD042',
		);
		$keys     = array(
			'base'      => 'customify_palette_base',
			'surface'   => 'customify_palette_surface',
			'text'      => 'customify_palette_text',
			'primary'   => 'global_styling_color_primary',
			'secondary' => 'global_styling_color_secondary',
			'accent'    => 'customify_palette_accent',
		);
		$slots    = array();
		foreach ( $defaults as $slot => $default ) {
			$raw          = get_theme_mod( $keys[ $slot ], $default );
			$slots[ $slot ] = customify_color_normalize_hex( $raw, $default );
		}
		return $slots;
	}
}

// ──────────────────────────────────────────────────────────────────
// :root CSS block builder.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_root_css' ) ) {
	function customify_color_palette_root_css() {
		$slots = customify_color_get_slots();

		// Static hex fallbacks for derived vars (PHP-precomputed).
		$text_muted_default    = customify_color_mix_hex( $slots['text'], $slots['base'], 0.70 );
		$border_default        = customify_color_mix_hex( $slots['text'], $slots['base'], 0.12 );
		$primary_hover_default = customify_color_mix_hex( $slots['primary'], '#000000', 0.90 ); // primary at 90%, black at 10%
		// Link hover = primary mixed with WHITE 15% (lighter, not darker).
		// Hover state surfaces the link by raising luminance. Note: the
		// existing button :hover (primary_hover) goes the other direction
		// (darker) — different UX semantic: buttons depress, links surface.
		$link_hover_default    = customify_color_mix_hex( $slots['primary'], '#FFFFFF', 0.85 );
		// Body text default = slot.text directly (same pattern as heading).
		// Earlier Phase 2.3 used mix(text, base, 88%) for a softer ink,
		// but that desaturates the user's Text slot — e.g. setting Text to
		// pure white on a dark base yields ~#e0e0e0 grey body copy
		// instead of the explicit white. Use slot.text verbatim so body
		// copy fully respects whatever the user picked.
		$body_text_default     = $slots['text'];

		// Override resolution — legacy explicit values win over computed defaults.
		// Each override is normalized; an invalid stored value (e.g. from a
		// rogue wp-cli write) is treated as "no override" and the derived
		// fallback kicks in instead of polluting the :root with garbage.
		//
		// Critical: `get_theme_mod()` inside the Customizer preview iframe falls
		// back to the customize control's registered default, NOT to our `null`
		// argument — so passing `null` here would still resolve to the field
		// default and look like an explicit user override, suppressing the
		// cascade. Read straight from the raw saved-mods array so the override
		// only counts when the user actually saved something. Outside the
		// customizer, get_theme_mods() returns the same data as get_theme_mod().
		$_saved_mods = get_theme_mods();
		$_get_saved  = function ( $key ) use ( $_saved_mods ) {
			return ( is_array( $_saved_mods ) && array_key_exists( $key, $_saved_mods ) )
				? $_saved_mods[ $key ]
				: null;
		};
		$ov_text_muted   = customify_color_normalize_hex( $_get_saved( 'global_styling_color_meta' ), '' );
		$ov_border       = customify_color_normalize_hex( $_get_saved( 'global_styling_color_border' ), '' );
		$ov_link         = customify_color_normalize_hex( $_get_saved( 'global_styling_color_link' ), '' );
		$ov_link_hover   = customify_color_normalize_hex( $_get_saved( 'global_styling_color_link_hover' ), '' );
		$ov_heading      = customify_color_normalize_hex( $_get_saved( 'global_styling_color_heading' ), '' );
		$ov_widget_title = customify_color_normalize_hex( $_get_saved( 'global_styling_color_w_title' ), '' );
		$ov_body_text    = customify_color_normalize_hex( $_get_saved( 'global_styling_color_text' ), '' );

		$text_muted   = $ov_text_muted   ?: $text_muted_default;
		// Border default is NOT slot-derived anymore — when no override
		// saved, --customify-border is omitted from :root so the CSS rule
		// falls back to `color-mix(in srgb, currentcolor 18%, transparent)`.
		// That makes borders adapt to the containing element's text color
		// instead of locking to slot.text — readable on both light and
		// dark surfaces. Saved override still wins via :root literal.
		$border       = $ov_border;
		$link         = $ov_link         ?: $slots['primary'];
		$link_hover   = $ov_link_hover   ?: $link_hover_default;
		$heading      = $ov_heading      ?: $slots['text'];
		$widget_title = $ov_widget_title ?: $slots['text'];
		// Body text default uses its own mix (88% ink) — stronger than
		// text-muted (70%, used for meta / secondary copy) so body copy
		// stays legible while headings keep contrast above it.
		$body_text    = $ov_body_text    ?: $body_text_default;

		// Contrast picks for on-* (PHP-precomputed).
		//
		// 30K-site safety gate: only emit auto-contrast on-* tokens when the
		// user has explicitly engaged with the new Palette panel (saved any
		// of the 4 truly-new slot keys: base, surface, text, accent — none
		// of which existed pre-Phase-2). For legacy sites that have only
		// touched the long-standing primary/secondary keys (or nothing),
		// leaving the on-* tokens UNSET means bundled rules of the form
		// `color: var(--customify-on-primary, #fff)` fall back to the
		// literal #fff hex — byte-equivalent to the pre-refactor hard-coded
		// `color: #fff;` everywhere buttons consume these tokens.
		//
		// The 4 slot keys are checked via array_key_exists() on the raw
		// saved-mods array (NOT get_theme_mod() — that returns field defaults
		// inside the customize preview and would falsely look "saved"; see
		// the same lesson in the override-resolution block above).
		$has_palette_opt_in = (
			is_array( $_saved_mods ) && (
				array_key_exists( 'customify_palette_base',    $_saved_mods ) ||
				array_key_exists( 'customify_palette_surface', $_saved_mods ) ||
				array_key_exists( 'customify_palette_text',    $_saved_mods ) ||
				array_key_exists( 'customify_palette_accent',  $_saved_mods )
			)
		);
		if ( $has_palette_opt_in ) {
			$on_primary   = customify_color_pick_on( $slots['primary'] );
			$on_secondary = customify_color_pick_on( $slots['secondary'] );
			$on_accent    = customify_color_pick_on( $slots['accent'] );

			// On-surface contrast: when the user saves Surface, pick the
			// readable text color for THAT surface. When Surface is NOT
			// saved but the user opted in, the bundled
			// `var(--customify-surface, #fff)` in rules like `.is-style-card`
			// falls back to literal #fff — pick against that fallback so
			// the card text stays readable regardless of saved Text color.
			// This solves the "saved dark Base + white Text + no Surface"
			// → invisible white-on-white card text case, while preserving
			// 30K safety: when palette opt-in is false, on-surface is left
			// UNSET and `var(--customify-on-surface, inherit)` resolves to
			// inherit (legacy body text cascade), so byte-equivalent output.
			$surface_effective = array_key_exists( 'customify_palette_surface', $_saved_mods )
				? $slots['surface']
				: '#FFFFFF';
			$on_surface = customify_color_pick_on( $surface_effective );
		} else {
			$on_primary = $on_secondary = $on_accent = $on_surface = null;
		}

		// Surface slot is the elevated-container background (cards / table
		// cells / code blocks / form inputs / calendar headers). Only emit
		// to :root when the user EXPLICITLY saved palette_surface — for
		// unsaved sites the bundled SCSS fallback resolves to
		// `color-mix(in srgb, currentcolor 6-12%, transparent)` which
		// auto-adapts to the page background. Emitting the slot default
		// `#ECECEC` here would bake a light gray into :root and break that
		// adaptive behavior for users who saved Base = dark but didn't
		// touch Surface. Same gate doctrine as --customify-on-* (Phase 2.8)
		// and --customify-border (Phase 2.6).
		$ov_surface = ( is_array( $_saved_mods ) && array_key_exists( 'customify_palette_surface', $_saved_mods ) )
			? $slots['surface']
			: null;

		$lines = array(
			"--customify-base: {$slots['base']}",
			"--customify-text: {$slots['text']}",
			"--customify-primary: {$slots['primary']}",
			"--customify-secondary: {$slots['secondary']}",
			"--customify-accent: {$slots['accent']}",
			"--customify-text-muted: {$text_muted}",
			"--customify-body-text: {$body_text}",
			"--customify-primary-hover: {$primary_hover_default}",
			"--customify-link: {$link}",
			"--customify-link-hover: {$link_hover}",
			"--customify-heading: {$heading}",
			"--customify-widget-title: {$widget_title}",
		);
		// On-* contrast tokens — only emitted when user has opted into the
		// new Palette panel (see $has_palette_opt_in above). Absence on
		// legacy sites means bundled `var(--customify-on-X, #fff)` falls
		// back to literal #fff, preserving byte-equivalent button rendering.
		if ( null !== $on_primary ) {
			$lines[] = "--customify-on-primary: {$on_primary}";
		}
		if ( null !== $on_secondary ) {
			$lines[] = "--customify-on-secondary: {$on_secondary}";
		}
		if ( null !== $on_accent ) {
			$lines[] = "--customify-on-accent: {$on_accent}";
		}
		if ( null !== $on_surface ) {
			$lines[] = "--customify-on-surface: {$on_surface}";
		}
		// --customify-border only emitted when override saved; absence
		// lets the CSS rule's `var(--customify-border, color-mix(currentcolor, ...))`
		// fallback fire so borders adapt to local text color.
		if ( $border ) {
			$lines[] = "--customify-border: {$border}";
		}
		// --customify-surface only emitted when user explicitly saved
		// palette_surface (see $ov_surface above). Absence lets the
		// bundled SCSS `$surface_subtle/medium/strong` fallback expressions
		// (color-mix in srgb, currentcolor X%, transparent) fire so surface
		// tints (table cells, code blocks, calendar headers, form inputs)
		// adapt to the page background automatically.
		if ( null !== $ov_surface ) {
			$lines[] = "--customify-surface: {$ov_surface}";
		}

		// Derived-token cascade lines — added AFTER the static lines so
		// modern browsers re-resolve them when the underlying slot changes
		// (e.g. drag the Text slot → headings update without save). Legacy
		// browsers without var() support ignore the duplicate decl as invalid
		// and the static line above still wins, keeping the precomputed hex.
		//
		// Only emit when there is no explicit override saved — an override
		// must remain a frozen static value (cf. 30K-site safety doctrine).
		if ( ! $ov_heading ) {
			$lines[] = "--customify-heading: var(--customify-text, {$slots['text']})";
		}
		// Widget title cascade — same pattern as heading. Sites without a
		// saved w_title override see widget titles follow slot.text. Saved
		// override locks the static value.
		if ( ! $ov_widget_title ) {
			$lines[] = "--customify-widget-title: var(--customify-text, {$slots['text']})";
		}
		// Body text cascade — body follows slot.text directly (no mix).
		// Same pattern as heading/widget-title: pure var() chain so user's
		// Text slot value flows through unchanged. Saved body override
		// suppresses this line and locks --customify-body-text to override.
		if ( ! $ov_body_text ) {
			$lines[] = "--customify-body-text: var(--customify-text, {$slots['text']})";
		}
		// Link cascade — link defaults to slot.primary directly (same
		// pattern as heading→text). The CSS rule `a { color: var(--customify-link, ...) }`
		// then resolves to whatever primary is set to, unless a saved
		// link override beats it. Modern browsers see the cascade live;
		// legacy browsers keep the static `--customify-link: <slot.primary>`
		// emitted earlier in this block.
		if ( ! $ov_link ) {
			$lines[] = "--customify-link: var(--customify-primary, {$slots['primary']})";
		}

		$static_root = ':root{' . implode( ';', $lines ) . ';}';

		// Modern-browser refinement via color-mix(in oklab, ...). Only emit for
		// derived vars that DON'T have an explicit override saved — preserves
		// legacy 30K-site behavior bit-for-bit.
		$mix_lines = array();
		if ( ! $ov_text_muted ) {
			$mix_lines[] = '--customify-text-muted: color-mix(in oklab, var(--customify-text) 70%, var(--customify-base))';
		}
		// Body text cascade removed from @supports block — body now uses a
		// pure var() chain (see static :root section above) instead of a
		// color-mix so the Text slot value flows through unchanged.
		// Border cascade now happens in the CSS rule's var() fallback
		// (`color-mix(in srgb, currentcolor 18%, transparent)`) so no
		// :root entry needed for the no-override case. Override case
		// emits a static :root --customify-border line above.
		$mix_lines[] = '--customify-primary-hover: color-mix(in oklab, var(--customify-primary), black 10%)';
		// Link hover cascade — LIGHTER variant of link (which itself cascades
		// from primary unless overridden). 15% white mixed in oklab keeps
		// perceived hue stable while lifting luminance. Saved override
		// suppresses the cascade so legacy explicit values still win.
		if ( ! $ov_link_hover ) {
			$mix_lines[] = '--customify-link-hover: color-mix(in oklab, var(--customify-link) 85%, white)';
		}

		$css = $static_root;
		if ( $mix_lines ) {
			$css .= '@supports (color: color-mix(in oklab, red, blue)){:root{' . implode( ';', $mix_lines ) . ';}}';
		}

		// Background composite cascade — Page bg / Content Area bg / Site
		// Content bg all fall back to `--customify-base` when the user has
		// NOT explicitly saved a bg_color subfield in the corresponding
		// composite styling control. When saved, the composite's own
		// auto-CSS rule emits the literal hex and that wins via cascade
		// order (palette-tokens loads AFTER customify-style-inline-css,
		// but we suppress emission here for saved composites so the
		// literal stays the only emitter for that selector).
		$bg_composites = array(
			array( 'key' => 'background',           'selector' => 'body' ),
			array( 'key' => 'site_content_styling', 'selector' => '.site-content .content-area' ),
			array( 'key' => 'content_background',   'selector' => '.site-content' ),
		);
		$bg_cascade_lines = array();
		foreach ( $bg_composites as $comp ) {
			$saved = isset( $_saved_mods[ $comp['key'] ]['normal']['bg_color'] )
				? trim( (string) $_saved_mods[ $comp['key'] ]['normal']['bg_color'] )
				: '';
			if ( '' === $saved ) {
				$bg_cascade_lines[] = $comp['selector'] . '{background-color:var(--customify-base, ' . $slots['base'] . ')}';
			}
		}
		if ( $bg_cascade_lines ) {
			$css .= implode( '', $bg_cascade_lines );
		}

		return $css;
	}
}

// ──────────────────────────────────────────────────────────────────
// theme.json palette sync — block editor color picker reads from here.
// Slug contract is API surface for Blocksify starter templates: never rename.
// ──────────────────────────────────────────────────────────────────

// Note: the 3 NEW slot slugs (base, surface, accent) are declared statically
// in theme.json alongside the long-standing 8 entries (primary, secondary,
// text, link, heading, background, light-gray, dark-gray). They use the same
// default values as the slot defaults here; the live Customizer values drive
// the `:root` block above (consumed by frontend / Blocksify), while
// theme.json values feed the block editor color picker.
//
// Both pickers are in sync at theme defaults; if the user changes a slot in
// the Customizer, the :root var updates but theme.json palette default does
// not — this matches existing Customify behavior (Customizer color changes
// never propagated to the block editor palette before).

// ──────────────────────────────────────────────────────────────────
// Customizer-controls JS: inject a "From palette" quick-pick row at the
// bottom of every wp-color-picker popup inside the Colors section.
// Lets the user override any component color from the 6 brand slots in
// one click, instead of typing a hex.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_quickpick_js' ) ) {
	function customify_color_palette_quickpick_js() {
		$slots   = customify_color_get_slots();
		// Brand-first display order to match the Palette section UI.
		// `control` is the LI id (without the `customize-control-` prefix)
		// used to detect whether an open picker is a palette slot — in that
		// case the popup shows a hex input instead of the From-Palette row.
		$payload = wp_json_encode( array(
			'slots' => array(
				array( 'key' => 'primary',   'label' => 'Primary',   'color' => $slots['primary'],   'control' => 'global_styling_color_primary' ),
				array( 'key' => 'secondary', 'label' => 'Secondary', 'color' => $slots['secondary'], 'control' => 'global_styling_color_secondary' ),
				array( 'key' => 'accent',    'label' => 'Accent',    'color' => $slots['accent'],    'control' => 'customify_palette_accent' ),
				array( 'key' => 'text',      'label' => 'Text',      'color' => $slots['text'],      'control' => 'customify_palette_text' ),
				array( 'key' => 'surface',   'label' => 'Surface',   'color' => $slots['surface'],   'control' => 'customify_palette_surface' ),
				array( 'key' => 'base',      'label' => 'Base',      'color' => $slots['base'],      'control' => 'customify_palette_base' ),
			),
		) );

		$script = "(function(\$){
	var CFY_COLORS = {$payload};
	var PALETTE_CONTROLS = CFY_COLORS.slots.map(function(s){ return s.control; });
	// Map of override settings → cascade source slot (read from wp.customize
	// on demand). Used to visually sync each override picker's swatch with
	// the resolved cascade value when no user override has been saved.
	// Source value drives the swatch DOM only — the override setting stays
	// empty so the cascade keeps applying. The user dragging the override
	// picker writes a real value and breaks out of cascade mode.
	var CASCADE_MAP = {
		'global_styling_color_link':         'global_styling_color_primary',
		'global_styling_color_heading':      'customify_palette_text',
		'global_styling_color_w_title':      'customify_palette_text',
		'global_styling_color_text':         'customify_palette_text'
	};
	// Field defaults — when an override's value equals its field default,
	// we treat it as 'no override' and apply the cascade-sync display.
	// Mirrors the 'default' keys in inc/customizer/configs/colors.php.
	var FIELD_DEFAULTS = {
		'global_styling_color_link':         '#235787',
		'global_styling_color_heading':      '#2b2b2b',
		'global_styling_color_w_title':      '#2b2b2b',
		'global_styling_color_text':         '#2b2b2b'
	};

	// Decode wp.customize value if Customify wrapped it as URL-encoded JSON.
	function decodeValue(v) {
		if (typeof v !== 'string') return v;
		try { return JSON.parse(decodeURI(v)); } catch (e) { return v; }
	}

	// Sync the override picker's swatch to its cascade source when the
	// override is unset (= still equals the registered field default).
	function syncCascadeSwatch(targetId) {
		var sourceId = CASCADE_MAP[targetId];
		if (!sourceId) return;
		var li = document.getElementById('customize-control-' + targetId);
		if (!li) return;
		var saved = decodeValue(wp.customize(targetId).get() || '');
		var unset = (saved === '' || saved === FIELD_DEFAULTS[targetId]);
		if (!unset) {
			// User override saved — let wp-color-picker paint as usual.
			li.classList.remove('is-cascading');
			li.style.removeProperty('--customify-cascade-display');
			return;
		}
		var cascadeValue = decodeValue(wp.customize(sourceId).get() || '');
		if (!cascadeValue) return;
		li.classList.add('is-cascading');
		li.style.setProperty('--customify-cascade-display', cascadeValue);
	}

	function syncAllCascadeSwatches() {
		Object.keys(CASCADE_MAP).forEach(syncCascadeSwatch);
	}

	// Bind to each cascade SOURCE so swatches refresh when the user drags
	// Primary or Text. Also bind each target's own setting so saving an
	// override flips it out of cascade mode immediately.
	function wireCascadeListeners() {
		if (typeof wp === 'undefined' || !wp.customize) return;
		// Bind targets — react to override save / clear.
		Object.keys(CASCADE_MAP).forEach(function(targetId){
			wp.customize(targetId, function(value){
				value.bind(function(){ setTimeout(function(){ syncCascadeSwatch(targetId); }, 16); });
			});
		});
		// Bind sources — react to Primary / Text slot drags.
		var sources = {};
		Object.keys(CASCADE_MAP).forEach(function(t){ sources[CASCADE_MAP[t]] = true; });
		Object.keys(sources).forEach(function(sourceId){
			wp.customize(sourceId, function(value){
				value.bind(function(){
					setTimeout(syncAllCascadeSwatches, 16);
				});
			});
		});
		// Initial paint once controls mount.
		setTimeout(syncAllCascadeSwatches, 200);
	}
	\$(wireCascadeListeners);

	function getControlId(container) {
		var li = container.closest ? container.closest('.customize-control') : null;
		if ( ! li || ! li.id ) return '';
		return li.id.replace(/^customize-control-/, '');
	}

	function removeAddons(container) {
		var qp = container.querySelector ? container.querySelector('.customify-color-quickpick') : null;
		if (qp) qp.parentNode.removeChild(qp);
		var hex = container.querySelector ? container.querySelector('.customify-color-hexrow') : null;
		if (hex) hex.parentNode.removeChild(hex);
	}

	// Build the From-Palette row used by component overrides (Link, Border,
	// Heading, etc.) so the user can override a single element from the
	// brand palette in one click.
	function buildQuickPick(\$panel, currentVal) {
		var \$row = \$('<div class=\"customify-color-quickpick\"></div>');
		\$row.append('<span class=\"customify-color-quickpick__label\">From palette</span>');
		CFY_COLORS.slots.forEach(function(s){
			var color = (s.color || '').toLowerCase();
			var \$sw = \$('<button type=\"button\" class=\"customify-color-quickpick__swatch\"></button>')
				.css('background-color', color)
				.attr('title', s.label)
				.attr('data-label', s.label)
				.attr('data-color', color);
			if (color === currentVal) \$sw.addClass('is-active');
			\$sw.on('click', function(e){
				e.preventDefault();
				e.stopPropagation();
				\$panel.wpColorPicker('color', color);
				\$row.find('.customify-color-quickpick__swatch').removeClass('is-active');
				\$sw.addClass('is-active');
			});
			\$row.append(\$sw);
		});
		return \$row;
	}

	// Build the hex input + read-only token var rows for a palette slot.
	// Two-way sync the hex with wp-color-picker via Iris events; the token
	// var is purely informational (one-tap select-all for copy/paste).
	function buildHexInput(\$panel, currentVal, slotKey) {
		var \$row = \$('<div class=\"customify-color-hexrow\"></div>');
		var \$input = \$('<input type=\"text\" class=\"customify-color-hex\" spellcheck=\"false\" autocomplete=\"off\" />').val(currentVal);
		\$input.on('input', function(){
			var v = (\$input.val() || '').trim();
			if ( v && v.charAt(0) !== '#' ) v = '#' + v;
			if ( /^#[0-9a-fA-F]{6}\$/.test(v) || /^#[0-9a-fA-F]{8}\$/.test(v) ) {
				\$panel.wpColorPicker('color', v);
			}
		});
		\$input.on('click', function(e){ e.stopPropagation(); });
		\$panel.on('iris-customify-hex-sync', function(){
			var v = \$panel.val();
			if ( v && document.activeElement !== \$input[0] ) \$input.val(v);
		});
		\$row.append(\$input);

		if ( slotKey ) {
			var \$tokenRow = \$('<div class=\"customify-color-tokenrow\"></div>');
			var \$token = \$('<input type=\"text\" class=\"customify-color-token\" readonly />')
				.val('var(--customify-' + slotKey + ')');
			\$token.on('focus', function(){ this.select(); });
			\$token.on('click', function(e){ e.stopPropagation(); this.select(); });
			\$tokenRow.append(\$token);
			// Wrap both rows in a fragment-like jQuery set so the caller can
			// append in one go without changing the public API.
			return \$row.add(\$tokenRow);
		}
		return \$row;
	}

	function injectPopupAddon(container) {
		var \$container = \$(container);
		var \$panel     = \$container.find('.customify--color-panel');
		if ( ! \$panel.length ) return;
		var currentVal = (\$panel.val() || '').toLowerCase();
		var controlId  = getControlId(container);
		var slot       = CFY_COLORS.slots.filter(function(s){ return s.control === controlId; })[0];
		var isPalette  = !!slot;

		// If an addon is already present (pre-built on init), refresh its
		// current-value state instead of rebuilding the DOM from scratch.
		// Overrides now carry BOTH a hex row and a quick-pick row, so refresh
		// either / both if present.
		var existing = container.querySelector ? container.querySelector('.customify-color-quickpick, .customify-color-hexrow') : null;
		if (existing) {
			var hexInput = container.querySelector('.customify-color-hex');
			if (hexInput && document.activeElement !== hexInput) {
				hexInput.value = \$panel.val() || '';
			}
			var quickpick = container.querySelector('.customify-color-quickpick');
			if (quickpick) {
				quickpick.querySelectorAll('.customify-color-quickpick__swatch').forEach(function(sw){
					sw.classList.toggle('is-active', (sw.getAttribute('data-color') || '').toLowerCase() === currentVal);
				});
			}
			return;
		}

		var \$addon;
		if (isPalette) {
			// Palette slots: hex input + readonly token var (var(--customify-<slug>)).
			\$addon = buildHexInput(\$panel, currentVal, slot.key);
		} else {
			// Component overrides (Link, Heading, Border, etc.): hex input
			// (no token row — overrides don't have a stable slot slug to
			// reference) PLUS the From-Palette quick-pick swatches so the
			// user can either type a custom hex or one-tap a slot color.
			\$addon = buildHexInput(\$panel, currentVal, null).add(buildQuickPick(\$panel, currentVal));
		}
		\$container.find('.wp-picker-holder').append(\$addon);
	}

	// Pre-build addons for every color picker in the Colors section right
	// after Customizer init — that way opening a picker just refreshes the
	// already-mounted addon (single class toggle / value update) instead of
	// building the DOM + 6-7 buttons + handlers from scratch each time.
	function prebuildAll(section) {
		section.querySelectorAll('.wp-picker-container').forEach(function(c){
			if ( c.querySelector('.customify-color-quickpick, .customify-color-hexrow') ) return;
			injectPopupAddon(c);
		});
		section.querySelectorAll('.customify-input-color').forEach(refreshDirtyState);
	}

	// Toggle `is-dirty` on the picker row when the value diverges from
	// what was loaded at page-render time. wpColorPicker rewrites
	// input.defaultValue on every set so we can't use it as a baseline;
	// dataset attributes also turn out to be unreliable across DOM
	// re-renders. Keep a closure-scoped map keyed by setting id — that
	// snapshot survives any DOM churn. SCSS uses `.is-dirty` to reveal
	// the small reset glyph to the left of the swatch.
	var initialValues = {};
	function refreshDirtyState(div) {
		if (!div) return;
		var li = div.closest('.customize-control');
		if (!li || !li.id) return;
		var settingId = li.id.replace('customize-control-', '');
		var input = div.querySelector('input.wp-color-picker');
		if (!input) return;
		var cur = (input.value || '').trim().toLowerCase();
		if (!(settingId in initialValues)) {
			// First observation: snapshot the loaded value. Skip if empty
			// — picker not yet initialized; next refresh will retry.
			if (cur === '') return;
			initialValues[settingId] = cur;
		}
		var initial = initialValues[settingId];
		var dirty = cur !== '' && cur !== initial;
		div.classList.toggle('is-dirty', dirty);
	}

	// Wire change listeners once. Two sources cover everything:
	//   • DOM events on the underlying wp-color-picker input — fires for
	//     Iris drag, hex paste, quickpick swatch click.
	//   • wp.customize setting bind — fires for programmatic .set() and
	//     for clicks on wp-picker-default (which resets value via the
	//     customize API, not via a DOM event on the input).
	// Either way, refreshDirtyState re-evaluates and toggles is-dirty.
	\$(document).on('input change keyup', '#sub-accordion-section-customify_colors input.wp-color-picker, #sub-accordion-section-customify_colors .customify-color-hex', function(){
		var li = \$(this).closest('.customify-input-color')[0];
		refreshDirtyState(li);
	});
	// Bind to each picker's underlying wp.customize Setting once it's
	// available so reset-button clicks (which go through the customize
	// API) also refresh the dirty state. `.customify-input-color` is a
	// DIV inside the control LI — settingId comes from the LI's id.
	function bindSettingListeners() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if (!section) return;
		section.querySelectorAll('.customify-input-color').forEach(function(div){
			if (div.dataset.dirtyBound) return;
			var li = div.closest('.customize-control');
			if (!li) return;
			var settingId = (li.id || '').replace('customize-control-', '');
			if (!settingId || !wp.customize(settingId)) return;
			div.dataset.dirtyBound = '1';
			wp.customize(settingId).bind(function(){
				// Defer one tick so wp-color-picker has time to write the
				// new value into the input before we read it.
				setTimeout(function(){ refreshDirtyState(div); }, 16);
			});
		});
	}
	\$(bindSettingListeners);
	setTimeout(bindSettingListeners, 600);


	// Legacy fine-tuning section is collapsed by default. The .customize-control-title
	// span inside the heading control gets a click handler; the LI itself stays
	// inert so it doesn't pick up the browser's default focus outline (which
	// otherwise paints a stuck rectangle around the entire heading row).
	// State persists per-session via sessionStorage.
	function setupLegacyCollapse() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if (!section) return;
		var heading = document.getElementById('customize-control-customify_colors_h_overrides');
		if (!heading) return;
		if (heading.classList.contains('customify-collapsible-heading')) return;
		heading.classList.add('customify-collapsible-heading');

		// Title span is the clickable target (chevron sits next to text).
		var titleEl = heading.querySelector('.customize-control-title');
		if (!titleEl) return;
		titleEl.classList.add('customify-collapsible-toggle');

		var targets = [];
		var next = heading.nextElementSibling;
		while (next) {
			if (next.classList && next.classList.contains('customize-control')) targets.push(next);
			next = next.nextElementSibling;
		}
		if (!targets.length) return;

		var STORAGE_KEY = 'customify-legacy-collapsed';
		var collapsed = sessionStorage.getItem(STORAGE_KEY) !== 'open';

		function apply(state) {
			targets.forEach(function(el){ el.style.display = state ? 'none' : ''; });
			heading.classList.toggle('is-collapsed', state);
			heading.classList.toggle('is-open',     !state);
		}
		apply(collapsed);

		titleEl.setAttribute('role', 'button');
		titleEl.setAttribute('tabindex', '0');
		titleEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

		function toggle(e) {
			e.preventDefault();
			e.stopPropagation();
			collapsed = !collapsed;
			sessionStorage.setItem(STORAGE_KEY, collapsed ? 'closed' : 'open');
			apply(collapsed);
			titleEl.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
		}
		titleEl.addEventListener('click', toggle);
		titleEl.addEventListener('keydown', function(e){
			if (e.key === 'Enter' || e.key === ' ') toggle(e);
		});
	}
	\$(setupLegacyCollapse);
	setTimeout(setupLegacyCollapse, 400);

	// Re-emit a custom event from any change on the picker so the hex input
	// stays in sync while the user drags Iris's saturation / hue / alpha.
	\$(document).on('input change', '#sub-accordion-section-customify_colors .customify--color-panel', function(){
		\$(this).trigger('iris-customify-hex-sync');
	});

	// Monkey-patch jQuery's slide methods so Iris's wpColorPicker can't
	// queue its slow slideToggle('fast') on our color picker holders. The
	// override is scoped to elements WITH .wp-picker-holder — never matches
	// the styling composite control's modal panel (which also lives in the
	// Colors section and uses slideUp/slideDown for its own toggle, and
	// relies on the animation-end callback to remove its .modal--opening
	// class — making it instant would strand the modal open).
	(function patchJQuerySlide(){
		var origDown   = \$.fn.slideDown;
		var origUp     = \$.fn.slideUp;
		var origToggle = \$.fn.slideToggle;
		function isPickerHolder(el){
			return el && el.classList && el.classList.contains('wp-picker-holder')
				&& el.closest && el.closest('#sub-accordion-section-customify_colors');
		}
		\$.fn.slideDown = function(){
			if (this.length && isPickerHolder(this[0])) return this.show();
			return origDown.apply(this, arguments);
		};
		\$.fn.slideUp = function(){
			if (this.length && isPickerHolder(this[0])) return this.hide();
			return origUp.apply(this, arguments);
		};
		\$.fn.slideToggle = function(){
			if (this.length && isPickerHolder(this[0])) {
				return this.is(':visible') ? this.hide() : this.show();
			}
			return origToggle.apply(this, arguments);
		};
	})();

	// Iris stops propagation on its click handler, so jQuery delegation on
	// document never sees the click. Instead we observe the .wp-picker-active
	// class on each wp-picker-container inside the Colors section and add/
	// remove our addon row in lock-step with the picker open/close.
	function startObserver() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if ( ! section ) {
			setTimeout( startObserver, 500 );
			return;
		}
		// Pre-build addons so opens feel instant — give Customify's initColor
		// a tick to call wpColorPicker() and produce .wp-picker-container.
		setTimeout(function(){ prebuildAll(section); }, 100);
		setTimeout(function(){ prebuildAll(section); }, 800);

		var observer = new MutationObserver(function(mutations){
			mutations.forEach(function(m){
				if ( m.type !== 'attributes' || m.attributeName !== 'class' ) return;
				var target = m.target;
				if ( ! target.classList || ! target.classList.contains('wp-picker-container') ) return;
				if ( target.classList.contains('wp-picker-active') ) {
					// Picker just opened — refresh existing addon (or create
					// the first time if pre-build hadn't run for this picker yet).
					injectPopupAddon(target);
				}
				// Note: no removeAddons on close — we keep the addon mounted
				// so the next open is instant. wp-picker-holder is display:none
				// via CSS when not .wp-picker-active so the addon is hidden too.
			});
		});
		observer.observe(section, { attributes: true, subtree: true, attributeFilter: ['class'] });
	}

	\$(startObserver);
})(jQuery);";

		wp_add_inline_script( 'customize-controls', $script );
	}
	add_action( 'customize_controls_enqueue_scripts', 'customify_color_palette_quickpick_js' );
}

// ──────────────────────────────────────────────────────────────────
// Force transport=postMessage on the 4 new slot settings.
// The 2 legacy slot keys (primary, secondary) already get postMessage
// via Customify_Customizer's css_format detection (class-customizer.php
// L1085-1097). The 4 new slot fields have empty css_format (they only
// feed :root vars, not auto-CSS rules) so they default to 'refresh' —
// override here so the preview JS below can live-update :root.
// Priority 1000 ensures Customify_Customizer::register (priority 666)
// has already added all settings.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_force_postmessage' ) ) {
	function customify_color_palette_force_postmessage( $wp_customize ) {
		$slot_settings = array(
			'customify_palette_base',
			'customify_palette_surface',
			'customify_palette_text',
			'customify_palette_accent',
		);
		foreach ( $slot_settings as $setting_id ) {
			$setting = $wp_customize->get_setting( $setting_id );
			if ( $setting ) {
				$setting->transport = 'postMessage';
			}
		}
	}
	add_action( 'customize_register', 'customify_color_palette_force_postmessage', 1000 );
}

// ──────────────────────────────────────────────────────────────────
// Preview JS: live-update --customify-<slot> on document.documentElement
// when any of the 6 slot settings changes. Modern browsers re-resolve
// derived tokens (text-muted, border, primary-hover, link-hover) via the
// color-mix() lines in :root automatically. Static fallbacks and on-*
// contrast picks don't live-update — they refresh on Customizer save.
//
// For primary/secondary the auto-CSS pipeline already regenerates the
// rule strings in the preview iframe via the existing Customify auto-css
// JS; this handler additionally keeps the :root var in sync so the
// var(--customify-primary, ...) refactor renders the new color in
// modern browsers.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_color_palette_preview_js' ) ) {
	function customify_color_palette_preview_js() {
		$payload = wp_json_encode( array(
			'global_styling_color_primary'   => '--customify-primary',
			'global_styling_color_secondary' => '--customify-secondary',
			'customify_palette_accent'       => '--customify-accent',
			'customify_palette_text'         => '--customify-text',
			'customify_palette_surface'      => '--customify-surface',
			'customify_palette_base'         => '--customify-base',
			// Override-style settings whose CSS rule now consumes the var()
			// token. Live-updating the token on drag means the user's direct
			// picker change wins over the slot cascade (the :root cascade
			// chain `--customify-heading: var(--customify-text, ...)` is
			// overridden by the inline style.setProperty we do here). When
			// the user clears the picker, our `normalize()` returns '' and
			// removeProperty() restores the cascade.
			'global_styling_color_heading'   => '--customify-heading',
			'global_styling_color_text'      => '--customify-body-text',
			'global_styling_color_link'      => '--customify-link',
			'global_styling_color_link_hover' => '--customify-link-hover',
			'global_styling_color_border'    => '--customify-border',
			'global_styling_color_meta'      => '--customify-text-muted',
			'global_styling_color_w_title'   => '--customify-widget-title',
		) );

		$script = "(function(){
	if (typeof wp === 'undefined' || ! wp.customize) return;
	var SLOT_VARS = {$payload};
	// Cascade expressions for derived override tokens. When the user CLEARS
	// an override picker mid-session via .set(''), removing the inline style
	// would fall through to the PHP-baked :root rule — which still holds the
	// SAVED override hex (palette-tokens block is rendered once at page load,
	// not regenerated on setting change). Result: cleared overrides keep
	// showing the old value until next save+reload.
	//
	// Fix: when normalize() returns empty for one of these tokens, set the
	// inline value to the cascade expression instead of removeProperty. CSS
	// custom-property values support nested var()/color-mix(), so the cascade
	// chain re-engages immediately and the rendered value tracks the source
	// slot in real time. After save+reload, PHP re-renders :root cleanly so
	// the inline override is no longer needed.
	//
	// Tokens NOT in this map fall back to the old removeProperty behavior:
	//   - slot tokens (primary/secondary/accent/text/surface/base): their
	//     :root values come from PHP defaults/saved slots — removing the
	//     inline override correctly reverts to those.
	//   - border: bundled CSS rule already has a smart fallback
	//     (color-mix(currentcolor 12%, transparent)) so absence is correct.
	var CASCADE_FALLBACK = {
		'--customify-heading':      'var(--customify-text)',
		'--customify-body-text':    'var(--customify-text)',
		'--customify-widget-title': 'var(--customify-text)',
		'--customify-link':         'var(--customify-primary)',
		'--customify-link-hover':   'color-mix(in oklab, var(--customify-link) 85%, white)',
		'--customify-text-muted':   'color-mix(in oklab, var(--customify-text) 70%, var(--customify-base))'
	};
	// Customify wraps stored setting values as urlencode(json_encode(value))
	// so a saved hex arrives as '%22#ff00aa%22'. Mirror Customify's decode
	// (control.js / customizer.js use the same JSON.parse(decodeURI(v))
	// pattern) so we get the raw '#ff00aa' before validating.
	function decode(v) {
		if (typeof v !== 'string') return v;
		try { return JSON.parse(decodeURI(v)); } catch(e) { return v; }
	}
	function normalize(v) {
		var d = decode(v);
		if (typeof d !== 'string') return '';
		d = d.trim();
		if (!d) return '';
		if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})\$/.test(d)) return d;
		if (/^rgba?\\(/.test(d)) return d;
		return '';
	}
	Object.keys(SLOT_VARS).forEach(function(setting){
		wp.customize(setting, function(value){
			value.bind(function(newval){
				var clean = normalize(newval);
				var token = SLOT_VARS[setting];
				if (clean) {
					document.documentElement.style.setProperty(token, clean);
				} else if (CASCADE_FALLBACK[token]) {
					document.documentElement.style.setProperty(token, CASCADE_FALLBACK[token]);
				} else {
					document.documentElement.style.removeProperty(token);
				}
			});
		});
	});

	// WCAG on-* live preview — mirrors PHP customify_color_pick_on().
	// Listens to the 3 brand slot pickers and recomputes the auto-contrast
	// text color on every drag. Always runs in the preview iframe regardless
	// of opt-in status: the user actively dragging IS engagement, and
	// showing the cascade behavior in preview informs the design choice.
	// On save, the PHP opt-in gate determines whether the on-* tokens
	// persist into the frontend :root block.
	function _hexToRgb(hex) {
		hex = (hex || '').replace(/^#/, '');
		if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
		if (!/^[0-9a-fA-F]{6}\$/.test(hex)) return null;
		return [
			parseInt(hex.slice(0,2), 16),
			parseInt(hex.slice(2,4), 16),
			parseInt(hex.slice(4,6), 16)
		];
	}
	function _relativeLuminance(hex) {
		var rgb = _hexToRgb(hex);
		if (!rgb) return 0;
		var f = function(v) {
			v = v / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
		};
		return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
	}
	function _pickOn(hex) {
		// Mirror PHP: relative_luminance > 0.45 ? '#1A1A1A' : '#FFFFFF'.
		return _relativeLuminance(hex) > 0.45 ? '#1A1A1A' : '#FFFFFF';
	}

	var ON_MAP = {
		'global_styling_color_primary':   '--customify-on-primary',
		'global_styling_color_secondary': '--customify-on-secondary',
		'customify_palette_accent':       '--customify-on-accent',
		'customify_palette_surface':      '--customify-on-surface'
	};
	// When a slot is cleared, the matching --customify-on-X token should
	// stay in sync with the SCSS var() fallback that rules consume:
	//   • Surface: `var(--customify-surface, #fff)` paints #fff in
	//     `.is-style-card` etc. → on-surface picks against #fff → #1A1A1A.
	//   • Primary/Secondary/Accent: buttons & similar use the saved hex
	//     directly; when cleared, the SCSS rule's var() fallback paints
	//     the legacy default hex and the corresponding on-* should pick
	//     against that. Keeping the map clean: only Surface needs a
	//     non-null clear value here.
	var ON_CLEAR_FALLBACK = {
		'--customify-on-surface': _pickOn('#FFFFFF')
	};
	Object.keys(ON_MAP).forEach(function(setting){
		wp.customize(setting, function(value){
			value.bind(function(newval){
				var clean = normalize(newval);
				var prop  = ON_MAP[setting];
				if (clean && clean.charAt(0) === '#') {
					document.documentElement.style.setProperty(prop, _pickOn(clean));
				} else if (ON_CLEAR_FALLBACK[prop]) {
					// Clear → fall back to picking against the SCSS var() default.
					document.documentElement.style.setProperty(prop, ON_CLEAR_FALLBACK[prop]);
				} else {
					document.documentElement.style.removeProperty(prop);
				}
			});
		});
	});
})();";

		wp_add_inline_script( 'customize-preview', $script );
	}
	add_action( 'customize_preview_init', 'customify_color_palette_preview_js' );
}
