<?php
/**
 * Palette-linked color pickers.
 *
 * Lets any component color FOLLOW a palette token instead of freezing a hex.
 * Picking a swatch in a color picker stores the token reference itself:
 *
 *     var(--customify-primary, #235787)
 *
 * so a later Primary change cascades to the field automatically, while the
 * baked fallback keeps the field rendering on installs where the token is not
 * emitted (see the derived-token opt-in gate in inc/colors-palette.php).
 *
 * This file is the DATA side only — the picker UI lives in
 * `src/backend/customizer/js/control.js` (`initColor()` + the
 * `customifyPaletteLink` helper) and `src/backend/customizer/scss/_control.scss`.
 * That split matters: `initColor()` is the single point every Customify color
 * input passes through — standalone controls, `styling` composite subfields,
 * `modal` subfields and repeater rows alike — and it runs BEFORE
 * wpColorPicker() so a token value can be handled without a write.
 *
 * 30K-site safety
 * ---------------
 * - No new storage key, no value-shape change. A field only ever holds a
 *   `var()` string after the user picks a swatch; every existing hex / rgba /
 *   empty value round-trips byte-identically.
 * - `Customify_Sanitize_Input::sanitize_color()` gained an additive
 *   `var(--customify-*)` branch restricted to an allowlist of emitted token
 *   names; anything else still falls through to the original hex/rgba path.
 * - The picker never writes on open: a linked field seeds the Iris input for
 *   display only, so the Customizer is not dirtied by rendering a control.
 *
 * @package Customify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'customify_color_link_root_map' ) ) {
	/**
	 * Parse the emitted `:root` block into a token => value map.
	 *
	 * Reading back what `customify_color_palette_root_css()` actually emitted
	 * (instead of recomputing the derivation math here) keeps the swatch
	 * previews in lockstep with the real pipeline — including the opt-in gate
	 * that suppresses some tokens entirely.
	 *
	 * @return array<string,string> Raw declaration values, later-wins like CSS.
	 */
	function customify_color_link_root_map() {
		static $map = null;
		if ( null !== $map ) {
			return $map;
		}

		$map = array();
		if ( ! function_exists( 'customify_color_palette_root_css' ) ) {
			return $map;
		}

		$css = customify_color_palette_root_css();
		if ( ! preg_match( '/:root\{(.*?)\}/s', $css, $m ) ) {
			return $map;
		}

		foreach ( explode( ';', $m[1] ) as $decl ) {
			if ( false === strpos( $decl, '--customify-' ) ) {
				continue;
			}
			$parts = explode( ':', $decl, 2 );
			if ( count( $parts ) < 2 ) {
				continue;
			}
			// Later declarations win — same as the browser resolving the block.
			$map[ trim( $parts[0] ) ] = trim( $parts[1] );
		}

		return $map;
	}
}

if ( ! function_exists( 'customify_color_link_resolve' ) ) {
	/**
	 * Resolve a CSS value to a concrete color for the controls-pane swatch.
	 *
	 * The `:root` block chains tokens (`--customify-heading: var(--customify-text, #2b2b2b)`),
	 * and the Customizer CONTROLS document has no `--customify-*` vars at all,
	 * so a swatch background has to be a literal. Walks the chain, falling back
	 * to the inline fallback when a link in the chain isn't emitted.
	 *
	 * @param string $value CSS value: hex, rgba, or `var(--x, fallback)`.
	 * @param int    $depth Recursion guard.
	 *
	 * @return string Literal color, or '' when unresolvable.
	 */
	function customify_color_link_resolve( $value, $depth = 0 ) {
		$value = trim( (string) $value );
		if ( '' === $value || $depth > 6 ) {
			return '';
		}

		if ( ! preg_match( '/^var\(\s*(--customify-[a-z0-9-]+)\s*(?:,\s*(.+)\s*)?\)$/i', $value, $m ) ) {
			// Not a var() — color-mix() and friends can't be resolved here, and
			// the caller falls back to the token's own baked hex.
			return preg_match( '/^(#|rgba?\()/i', $value ) ? $value : '';
		}

		$map      = customify_color_link_root_map();
		$name     = strtolower( $m[1] );
		$fallback = isset( $m[2] ) ? trim( $m[2] ) : '';

		if ( isset( $map[ $name ] ) ) {
			$resolved = customify_color_link_resolve( $map[ $name ], $depth + 1 );
			if ( '' !== $resolved ) {
				return $resolved;
			}
		}

		return '' !== $fallback ? customify_color_link_resolve( $fallback, $depth + 1 ) : '';
	}
}

if ( ! function_exists( 'customify_color_link_tokens' ) ) {
	/**
	 * Pickable tokens for the picker swatch row.
	 *
	 * Source list = the block-editor palette (`customify_color_palette_for_theme_json()`),
	 * so a designer sees the same names and the same swatches in the Customizer
	 * and in the block editor. `value` is what gets stored in the theme_mod.
	 *
	 * @return array[] Each: { value, label, preview }.
	 */
	function customify_color_link_tokens() {
		if ( ! function_exists( 'customify_color_palette_for_theme_json' ) ) {
			return array();
		}

		$tokens = array();
		foreach ( customify_color_palette_for_theme_json() as $entry ) {
			if ( empty( $entry['slug'] ) || empty( $entry['color'] ) ) {
				continue;
			}

			// Skip tokens we can't show a truthful swatch for.
			$preview = customify_color_link_resolve( $entry['color'] );
			if ( '' === $preview ) {
				continue;
			}

			$tokens[] = array(
				'value'   => $entry['color'],
				'label'   => isset( $entry['name'] ) ? $entry['name'] : $entry['slug'],
				'preview' => $preview,
			);
		}

		return $tokens;
	}
}

if ( ! function_exists( 'customify_color_link_excluded_controls' ) ) {
	/**
	 * Control names that must NOT offer palette linking.
	 *
	 * Everything registered in the Colors section is excluded:
	 *
	 * - The 6 palette slots DEFINE the tokens. Linking a slot to a token would
	 *   be circular (`--customify-primary: var(--customify-primary)`).
	 * - The 7 component overrides feed the derived-token engine through
	 *   `customify_color_normalize_hex()`, which only accepts hex — a `var()`
	 *   there reads as "no override saved" and silently does nothing. The
	 *   section already offers its own "From palette" quick-pick, which is the
	 *   correct affordance for an override.
	 * - The 3 background composites have their own slot cascade
	 *   (see `customify_color_palette_root_css()`), which keys off whether a
	 *   `bg_color` subfield was saved.
	 *
	 * Derived from the live config rather than hardcoded, so a config change
	 * cannot silently expose a slot picker.
	 *
	 * @return string[]
	 */
	function customify_color_link_excluded_controls() {
		$excluded = array();

		if ( class_exists( 'Customify_Customizer' ) ) {
			foreach ( Customify_Customizer::get_config() as $field ) {
				if ( empty( $field['name'] ) || empty( $field['section'] ) ) {
					continue;
				}
				if ( 'customify_colors' === $field['section'] ) {
					$excluded[] = $field['name'];
				}
			}
		}

		return array_values( array_unique( apply_filters( 'customify/color/palette_link_excluded', $excluded ) ) );
	}
}

if ( ! function_exists( 'customify_color_link_control_args' ) ) {
	/**
	 * Ship palette-link data to the controls JS.
	 *
	 * Rides the existing `Customify_Control_Args` payload (localized on the
	 * `customify-customizer-control` handle) so no extra script or request is
	 * added for the 30K install base.
	 *
	 * `enabled` is false when the palette produced no usable tokens, which
	 * makes the whole feature a no-op rather than rendering an empty row.
	 *
	 * @param array $args Localized control args.
	 *
	 * @return array
	 */
	function customify_color_link_control_args( $args ) {
		$tokens = customify_color_link_tokens();

		$args['palette_link'] = array(
			'enabled'  => ! empty( $tokens ),
			'tokens'   => $tokens,
			'excluded' => customify_color_link_excluded_controls(),
			'l10n'     => array(
				'linked' => __( 'Linked to', 'customify' ),
				'unlink' => __( 'Unlink', 'customify' ),
				'custom' => __( 'Custom color', 'customify' ),
				'copy'   => __( 'Copy value', 'customify' ),
			),
		);

		return $args;
	}
	add_filter( 'Customify_Control_Args', 'customify_color_link_control_args' );
}
