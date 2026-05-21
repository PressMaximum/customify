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
			'surface'   => '#FFFFFF',
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
		$link_hover_default    = customify_color_mix_hex( $slots['primary'], '#000000', 0.85 );

		// Override resolution — legacy explicit values win over computed defaults.
		// Each override is normalized; an invalid stored value (e.g. from a
		// rogue wp-cli write) is treated as "no override" and the derived
		// fallback kicks in instead of polluting the :root with garbage.
		$ov_text_muted   = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_meta', null ), '' );
		$ov_border       = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_border', null ), '' );
		$ov_link         = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_link', null ), '' );
		$ov_link_hover   = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_link_hover', null ), '' );
		$ov_heading      = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_heading', null ), '' );
		$ov_widget_title = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_w_title', null ), '' );
		$ov_body_text    = customify_color_normalize_hex( get_theme_mod( 'global_styling_color_text', null ), '' );

		$text_muted   = $ov_text_muted   ?: $text_muted_default;
		$border       = $ov_border       ?: $border_default;
		$link         = $ov_link         ?: $slots['primary'];
		$link_hover   = $ov_link_hover   ?: $link_hover_default;
		$heading      = $ov_heading      ?: $slots['text'];
		$widget_title = $ov_widget_title ?: $slots['text'];
		// Body-text override stays as a slot-like value for components that need it.
		$body_text    = $ov_body_text    ?: $text_muted_default;

		// Contrast picks for on-* (PHP-precomputed).
		$on_primary   = customify_color_pick_on( $slots['primary'] );
		$on_secondary = customify_color_pick_on( $slots['secondary'] );
		$on_accent    = customify_color_pick_on( $slots['accent'] );

		$lines = array(
			"--customify-base: {$slots['base']}",
			"--customify-surface: {$slots['surface']}",
			"--customify-text: {$slots['text']}",
			"--customify-primary: {$slots['primary']}",
			"--customify-secondary: {$slots['secondary']}",
			"--customify-accent: {$slots['accent']}",
			"--customify-text-muted: {$text_muted}",
			"--customify-body-text: {$body_text}",
			"--customify-border: {$border}",
			"--customify-primary-hover: {$primary_hover_default}",
			"--customify-link: {$link}",
			"--customify-link-hover: {$link_hover}",
			"--customify-heading: {$heading}",
			"--customify-widget-title: {$widget_title}",
			"--customify-on-primary: {$on_primary}",
			"--customify-on-secondary: {$on_secondary}",
			"--customify-on-accent: {$on_accent}",
		);

		$static_root = ':root{' . implode( ';', $lines ) . ';}';

		// Modern-browser refinement via color-mix(in oklab, ...). Only emit for
		// derived vars that DON'T have an explicit override saved — preserves
		// legacy 30K-site behavior bit-for-bit.
		$mix_lines = array();
		if ( ! $ov_text_muted ) {
			$mix_lines[] = '--customify-text-muted: color-mix(in oklab, var(--customify-text) 70%, var(--customify-base))';
		}
		if ( ! $ov_border ) {
			$mix_lines[] = '--customify-border: color-mix(in oklab, var(--customify-text) 12%, var(--customify-base))';
		}
		$mix_lines[] = '--customify-primary-hover: color-mix(in oklab, var(--customify-primary), black 10%)';
		if ( ! $ov_link_hover ) {
			$mix_lines[] = '--customify-link-hover: color-mix(in oklab, var(--customify-primary), black 15%)';
		}

		$css = $static_root;
		if ( $mix_lines ) {
			$css .= '@supports (color: color-mix(in oklab, red, blue)){:root{' . implode( ';', $mix_lines ) . ';}}';
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
		$payload = wp_json_encode( array(
			'slots' => array(
				array( 'key' => 'base',      'label' => 'Base',      'color' => $slots['base'] ),
				array( 'key' => 'surface',   'label' => 'Surface',   'color' => $slots['surface'] ),
				array( 'key' => 'text',      'label' => 'Text',      'color' => $slots['text'] ),
				array( 'key' => 'primary',   'label' => 'Primary',   'color' => $slots['primary'] ),
				array( 'key' => 'secondary', 'label' => 'Secondary', 'color' => $slots['secondary'] ),
				array( 'key' => 'accent',    'label' => 'Accent',    'color' => $slots['accent'] ),
			),
		) );

		$script = "(function(\$){
	var CFY_COLORS = {$payload};

	function injectQuickPick(container) {
		var \$container = \$(container);
		if ( \$container.find('.customify-color-quickpick').length ) return;
		var \$panel = \$container.find('.customify--color-panel');
		if ( ! \$panel.length ) return;
		var currentVal = (\$panel.val() || '').toLowerCase();

		var \$row = \$('<div class=\"customify-color-quickpick\"></div>');
		\$row.append('<span class=\"customify-color-quickpick__label\">From palette</span>');

		CFY_COLORS.slots.forEach(function(s){
			var color = (s.color || '').toLowerCase();
			var \$sw = \$('<button type=\"button\" class=\"customify-color-quickpick__swatch\"></button>')
				.css('background-color', color)
				.attr('title', s.label + ' — ' + color)
				.attr('data-color', color);
			if (color === currentVal) \$sw.addClass('is-active');
			\$sw.on('click', function(e){
				e.preventDefault();
				\$panel.wpColorPicker('color', color);
				\$row.find('.customify-color-quickpick__swatch').removeClass('is-active');
				\$sw.addClass('is-active');
			});
			\$row.append(\$sw);
		});

		\$container.find('.wp-picker-holder').append(\$row);
	}

	// Iris stops propagation on its own click handler, so jQuery delegation
	// on document never sees the event. We watch for `.wp-picker-active`
	// class additions on any wp-picker-container inside the Colors section
	// and inject the quick-pick row at that moment.
	function startObserver() {
		var section = document.getElementById('sub-accordion-section-customify_colors');
		if ( ! section ) {
			// Section not in DOM yet — try again when Customizer renders it.
			setTimeout( startObserver, 500 );
			return;
		}
		var observer = new MutationObserver(function(mutations){
			mutations.forEach(function(m){
				if ( m.type !== 'attributes' || m.attributeName !== 'class' ) return;
				var target = m.target;
				if ( target.classList && target.classList.contains('wp-picker-container') && target.classList.contains('wp-picker-active') ) {
					injectQuickPick(target);
				}
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
