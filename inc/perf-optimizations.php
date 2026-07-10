<?php
/**
 * Performance optimizations addressing Lighthouse audit findings.
 *
 * Every runtime output path passes through the `style_loader_tag` filter so
 * `<link>` tags are modified in place rather than echoed alongside WordPress
 * core output. This satisfies theme review guidelines that restrict raw
 * `<style>` / `<script>` / `<link>` emissions, and every value at an output
 * point is escaped inline so phpcs can verify it.
 *
 * Fixes applied:
 *
 * - `font-display: swap` on the Font Awesome @font-face (source edit — see
 *   src/fonts/font-awesome/css/font-awesome*.css). No runtime code needed;
 *   text and icons render immediately, the webfont swaps in when it loads.
 *
 * - Load Font Awesome CSS asynchronously via the `media=print → onload=all`
 *   pattern. FA is icon-only styling (header search / cart / menu chevrons)
 *   and is not required for above-the-fold layout, so deferring it
 *   eliminates ~156 ms of render-blocking with negligible FOUC risk. A
 *   `<noscript>` fallback preserves visual parity for no-JS clients.
 *
 * - Add `fetchpriority="high"` to the main theme stylesheet tag so browsers
 *   prioritise its fetch over other resources discovered later in `<head>`.
 *
 * @package Customify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Awesome variant handles Customify_Font_Icons::enqueue() may register.
 *
 * See inc/customizer/class-customizer-icons.php for the `$icons` map:
 *   - font-awesome         → v4 CSS
 *   - font-awesome-v6      → v6 all.min.css
 *   - font-awesome-4-shim  → v6 shim (accompanies v6)
 */
const CUSTOMIFY_FA_HANDLES = array( 'font-awesome', 'font-awesome-v6', 'font-awesome-4-shim' );

/**
 * Insert an attribute into a `<link>` tag before its closing bracket.
 *
 * No-op when the attribute is already present. Supports both `" />"` (WP
 * default XHTML closing) and `" >"` (HTML5).
 *
 * @param string $tag        Full `<link ...>` tag string.
 * @param string $attribute  Attribute name (e.g. `fetchpriority`).
 * @param string $value      Attribute value (will be `esc_attr`-escaped).
 * @return string The tag with the attribute added, or unchanged if no
 *                closing bracket was matched.
 */
function customify_perf_add_link_attr( $tag, $attribute, $value ) {
	if ( false !== strpos( $tag, ' ' . $attribute . '=' ) ) {
		return $tag;
	}
	$injection = ' ' . $attribute . '="' . esc_attr( $value ) . '"';
	if ( false !== strpos( $tag, ' />' ) ) {
		return str_replace( ' />', $injection . ' />', $tag );
	}
	if ( false !== strpos( $tag, '/>' ) ) {
		return str_replace( '/>', $injection . '/>', $tag );
	}
	if ( false !== strpos( $tag, ' >' ) ) {
		return str_replace( ' >', $injection . ' >', $tag );
	}
	return $tag;
}

/**
 * Transform stylesheet tags to satisfy the perf optimizations.
 *
 * - `customify-style` — add `fetchpriority="high"` so browsers prioritise
 *   the main theme stylesheet over other resources in `<head>`.
 *
 * - Font Awesome handles — swap the stylesheet to the non-blocking
 *   `media=print → onload=all` pattern (with a `<noscript>` fallback).
 *
 * @param string $tag    The `<link>` tag WordPress is about to print.
 * @param string $handle The style handle.
 * @param string $href   The stylesheet URL.
 * @param string $media  The current media attribute value.
 * @return string
 */
add_filter(
	'style_loader_tag',
	static function ( $tag, $handle, $href, $media ) {
		if ( 'customify-style' === $handle ) {
			return customify_perf_add_link_attr( $tag, 'fetchpriority', 'high' );
		}

		if ( in_array( $handle, CUSTOMIFY_FA_HANDLES, true ) ) {
			if ( 'all' !== $media ) {
				return $tag;
			}

			// Swap to non-blocking pattern (fetch with media=print, apply
			// on load). WP core prints media in single quotes by default;
			// handle both to stay resilient to filter chains that swap
			// quoting.
			$onload  = "this.media='all';this.onload=null";
			$swapped = str_replace(
				"media='{$media}'",
				"media='print' onload=\"{$onload}\"",
				$tag
			);
			if ( $swapped === $tag ) {
				$swapped = str_replace(
					'media="' . $media . '"',
					'media="print" onload="' . $onload . '"',
					$tag
				);
			}
			if ( $swapped === $tag ) {
				// Couldn't rewrite — return the original untouched so we
				// don't break anything.
				return $tag;
			}

			$noscript = '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '" media="' . esc_attr( $media ) . '"></noscript>';
			return $swapped . $noscript;
		}

		return $tag;
	},
	10,
	4
);
