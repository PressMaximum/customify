<?php
/**
 * Color Palette — bootstrap.
 *
 * Color-palette system surfaced exclusively as a Customizer control. Visitors
 * never see a panel; they receive the saved active palette as `:root` CSS
 * vars consumed by the override layer. Admins edit through the "Color palette"
 * control under Customizer → Global Colors.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/class-color-palette-config.php';
require_once __DIR__ . '/class-color-palette-ajax.php';
require_once __DIR__ . '/class-color-palette-customizer.php';
require_once __DIR__ . '/class-color-palette-import.php';
require_once __DIR__ . '/class-color-palette-compat.php';

class Customify_Color_Palette
{
	// Style id for the inline `:root { … }` block emitted on every page load.
	const HANDLE = 'customify-color-palette';

	public static function init()
	{
		// Customizer "Color palette" control — registers settings + control.
		Customify_Color_Palette_Customizer::init();
		Customify_Color_Palette_Import::init();
		// One-time bootstrap: if styling.php has user-saved values and no
		// custom palette exists yet, materialise a "Theme custom" palette
		// from those values + activate it. See class for the full guard.
		Customify_Color_Palette_Compat::init();

		if (! is_admin()) {
			// Visitor-side: emit `<style>:root { --customify-color-*: … }</style>`
			// with the saved active palette so the override layer compiled into
			// style-theme.css (via @import "overrides" in
			// src/frontend/scss/style.scss) has values to consume.
			add_action('wp_head', array(__CLASS__, 'output_root_vars'), 1);
		}
	}

	/**
	 * Resolve the saved active palette to a `{ id, name, colors }` array.
	 * Falls back to the first theme preset if the saved id is missing or stale.
	 */
	private static function get_active_palette()
	{
		$active_id = Customify_Color_Palette_Ajax::get_active_id();
		$themes    = Customify_Color_Palette_Config::theme_presets();

		if ('' !== $active_id) {
			foreach ($themes as $p) {
				if (isset($p['id']) && $p['id'] === $active_id) {
					return $p;
				}
			}
			foreach (Customify_Color_Palette_Ajax::get_user_palettes() as $p) {
				if (isset($p['id']) && $p['id'] === $active_id) {
					return $p;
				}
			}
		}

		return ! empty($themes) ? $themes[0] : null;
	}

	/**
	 * Parse "#RRGGBB" / "#RGB" → array [r, g, b] of integers, or null.
	 */
	private static function hex_to_rgb_array($hex)
	{
		$h = ltrim((string) $hex, '#');
		if (3 === strlen($h)) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if (! preg_match('/^[0-9A-Fa-f]{6}$/', $h)) {
			return null;
		}
		return array(hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2)));
	}

	/**
	 * Pick a contrast-aware foreground (#1A1A1A or #FFFFFF) for the given hex
	 * background. WCAG-style relative luminance with the threshold nudged to
	 * 0.45 (Style Pack) so warm tones still round to white.
	 */
	private static function pick_on($hex)
	{
		$rgb = self::hex_to_rgb_array($hex);
		if (null === $rgb) {
			return '#FFFFFF';
		}
		$f = function ($v) {
			$v /= 255;
			return $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
		};
		$lum = 0.2126 * $f($rgb[0]) + 0.7152 * $f($rgb[1]) + 0.0722 * $f($rgb[2]);
		return $lum > 0.45 ? '#1A1A1A' : '#FFFFFF';
	}

	/**
	 * Print the saved active palette as `:root { --customify-color-*: … }` so
	 * frontend visitors see the palette applied via the override stylesheet.
	 *
	 * In preview mode the panel JS sets the same vars as inline style on
	 * <html>, which beats this stylesheet rule by specificity — admin's live
	 * edits win over the saved values without removing them.
	 */
	public static function output_root_vars()
	{
		// Slot-independent contrast tokens (PHASE-7-PLAN §16). FIXED values,
		// never rebound by the trigger block. Used by hardcoded `.dark-mode`
		// / `.light-mode` rules (footer / header bands with literal hex bg)
		// to guarantee text contrast regardless of the active palette.
		// Always emitted, even when no palette is active, so the SCSS
		// migrations in `_footer-common.scss` etc. always have a value.
		$decls = '--customify-color-on-dark-bg:#FCFCFC;'
			. '--customify-color-on-light-bg:#1A1A1A;';

		$pal = self::get_active_palette();
		$has_palette = $pal && ! empty($pal['colors']);

		if ($has_palette) {

		// 1) Six user-picked slots — hex value only.
		foreach (Customify_Color_Palette_Config::slots() as $slot) {
			if (empty($pal['colors'][$slot])) {
				continue;
			}
			$hex = $pal['colors'][$slot];
			if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex)) {
				continue;
			}
			$decls .= '--customify-color-' . $slot . ':' . $hex . ';';
		}

		// 2) Auto-computed companions — must mirror the JS panel's
		// `applyColorVars()` so visitors and admins (pre-JS-bootstrap) see the
		// same values.
		$primary   = isset($pal['colors']['primary'])   ? $pal['colors']['primary']   : '';
		$secondary = isset($pal['colors']['secondary']) ? $pal['colors']['secondary'] : '';
		$surface   = isset($pal['colors']['surface'])   ? $pal['colors']['surface']   : '';
		$text      = isset($pal['colors']['text'])      ? $pal['colors']['text']      : '';
		$base      = isset($pal['colors']['base'])      ? $pal['colors']['base']      : '';

		// 2a) Contrast-aware on-*.
		if ('' !== $primary)   { $decls .= '--customify-color-on-primary:'   . self::pick_on($primary)   . ';'; }
		if ('' !== $secondary) { $decls .= '--customify-color-on-secondary:' . self::pick_on($secondary) . ';'; }
		if ('' !== $surface)   { $decls .= '--customify-color-on-surface:'   . self::pick_on($surface)   . ';'; }

		// 2b) Text alpha derivatives + border via color-mix.
		if ('' !== $text) {
			$decls .= '--customify-color-text-muted:color-mix(in srgb, '     . $text . ' 55%, transparent);';
			$decls .= '--customify-color-text-subtle:color-mix(in srgb, '    . $text . ' 35%, transparent);';
			$decls .= '--customify-color-border-default:color-mix(in srgb, ' . $text . ' 12%, transparent);';
		}

		// 2c) Primary hover / subtle via color-mix (modern browsers).
		if ('' !== $primary) {
			$decls .= '--customify-color-primary-hover:color-mix(in srgb, ' . $primary . ', #000 15%);';
			if ('' !== $base) {
				$decls .= '--customify-color-primary-subtle:color-mix(in srgb, ' . $primary . ', ' . $base . ' 92%);';
			}
		}
		} // end if ($has_palette)

		$style_id = esc_attr(self::HANDLE) . '-vars';
		$out = "\n<style id=\"" . $style_id . "\">:root{" . $decls . "}\n</style>\n";
		echo $out;
	}

}

Customify_Color_Palette::init();
