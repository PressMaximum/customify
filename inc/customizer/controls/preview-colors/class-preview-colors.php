<?php
/**
 * Preview Colors — bootstrap.
 *
 * Color-palette system surfaced exclusively as a Customizer control. Visitors
 * never see a panel; they receive the saved active palette as `:root` CSS
 * vars consumed by overrides.scss. Admins edit through the "Color palette"
 * control under Customizer → Global Colors.
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/class-preview-colors-config.php';
require_once __DIR__ . '/class-preview-colors-ajax.php';
require_once __DIR__ . '/class-preview-colors-dark.php';
require_once __DIR__ . '/class-preview-colors-customizer.php';
require_once __DIR__ . '/class-preview-colors-demo-import.php';
require_once __DIR__ . '/class-preview-colors-compat.php';

class Customify_Preview_Colors
{
	// Style id for the inline `:root { … }` block emitted on every page load.
	const HANDLE = 'customify-preview-colors';

	public static function init()
	{
		// Customizer "Color palette" control — registers settings + control.
		Customify_Preview_Colors_Customizer::init();
		Customify_Preview_Colors_Demo_Import::init();
		// One-time bootstrap: if styling.php has user-saved values and no
		// custom palette exists yet, materialise a "Theme custom" palette
		// from those values + activate it. See class for the full guard.
		Customify_Preview_Colors_Compat::init();

		if (! is_admin()) {
			// Visitor-side: emit `<style>:root { --customify-color-*: … }</style>`
			// with the saved active palette + dark companions + trigger block,
			// so the override layer compiled into style-theme.css (via
			// @import "overrides" in src/frontend/scss/style.scss) has values
			// to consume.
			add_action('wp_head', array(__CLASS__, 'output_root_vars'), 1);
		}
	}

	/**
	 * Resolve the saved active palette to a `{ id, name, colors }` array.
	 * Falls back to the first theme preset if the saved id is missing or stale.
	 */
	private static function get_active_palette()
	{
		$active_id = Customify_Preview_Colors_Ajax::get_active_id();
		$themes    = Customify_Preview_Colors_Config::theme_presets();

		if ('' !== $active_id) {
			foreach ($themes as $p) {
				if (isset($p['id']) && $p['id'] === $active_id) {
					return $p;
				}
			}
			foreach (Customify_Preview_Colors_Ajax::get_user_palettes() as $p) {
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

		// Trigger block is gated on having an active palette — without one
		// the `-dark` companions don't exist, so rebinding to them would
		// leave the active vars undefined.
		$emit_trigger = false;

		if ($has_palette) {

		// 1) Six user-picked slots — hex value only.
		foreach (Customify_Preview_Colors_Config::slots() as $slot) {
			if (empty($pal['colors'][$slot])) {
				continue;
			}
			$hex = $pal['colors'][$slot];
			if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex)) {
				continue;
			}
			$decls .= '--customify-color-' . $slot . ':' . $hex . ';';
		}

		// 2) Auto-computed companions — must mirror src/preview-colors/preview-colors.js
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

		// 3) Dark-mode companion vars (PHASE-7-PLAN). Resolved via the 5-tier
		// chain in Customify_Preview_Colors_Dark::resolve_palette() so values
		// are always concrete hexes, never `var()` chains.
		$dark = Customify_Preview_Colors_Dark::resolve_palette($pal);
		foreach (Customify_Preview_Colors_Config::slots() as $slot) {
			if (empty($dark[$slot])) {
				continue;
			}
			$decls .= '--customify-color-' . $slot . '-dark:' . $dark[$slot] . ';';
		}

		// 3a) Auto-computed companions for dark — recompute against resolved
		// dark slot values. Mirrors the light-mode block above.
		$d_primary   = isset($dark['primary'])   ? $dark['primary']   : '';
		$d_secondary = isset($dark['secondary']) ? $dark['secondary'] : '';
		$d_surface   = isset($dark['surface'])   ? $dark['surface']   : '';
		$d_text      = isset($dark['text'])      ? $dark['text']      : '';
		$d_base      = isset($dark['base'])      ? $dark['base']      : '';

		if ('' !== $d_primary)   { $decls .= '--customify-color-on-primary-dark:'   . self::pick_on($d_primary)   . ';'; }
		if ('' !== $d_secondary) { $decls .= '--customify-color-on-secondary-dark:' . self::pick_on($d_secondary) . ';'; }
		if ('' !== $d_surface)   { $decls .= '--customify-color-on-surface-dark:'   . self::pick_on($d_surface)   . ';'; }

		if ('' !== $d_text) {
			$decls .= '--customify-color-text-muted-dark:color-mix(in srgb, '     . $d_text . ' 55%, transparent);';
			$decls .= '--customify-color-text-subtle-dark:color-mix(in srgb, '    . $d_text . ' 35%, transparent);';
			$decls .= '--customify-color-border-default-dark:color-mix(in srgb, ' . $d_text . ' 12%, transparent);';
		}

		// Note the direction flip vs light mode: blend toward white (#fff)
		// instead of black so hover states stay visible on dark surfaces.
		if ('' !== $d_primary) {
			$decls .= '--customify-color-primary-hover-dark:color-mix(in srgb, ' . $d_primary . ', #fff 12%);';
			if ('' !== $d_base) {
				$decls .= '--customify-color-primary-subtle-dark:color-mix(in srgb, ' . $d_primary . ', ' . $d_base . ' 92%);';
			}
		}

		$emit_trigger = true;
		} // end if ($has_palette)

		$style_id = esc_attr(self::HANDLE) . '-vars';
		$out = "\n<style id=\"" . $style_id . "\">:root{" . $decls . "}\n";

		// 4) Trigger block — rebind the active `--customify-color-<…>` vars
		// to the `-dark` companions inside any subtree carrying one of the
		// dark-mode trigger classes. Specificity (0,1,0) beats `html body`
		// (0,0,2) used by overrides.scss without competing on the property.
		if ($emit_trigger) {
			$trigger_decls = '';
			foreach (Customify_Preview_Colors_Config::slots() as $slot) {
				$trigger_decls .= '--customify-color-' . $slot . ':var(--customify-color-' . $slot . '-dark);';
			}
			foreach (array('on-primary', 'on-secondary', 'on-surface', 'text-muted', 'text-subtle', 'border-default', 'primary-hover', 'primary-subtle') as $k) {
				$trigger_decls .= '--customify-color-' . $k . ':var(--customify-color-' . $k . '-dark);';
			}
			$out .= '.dark-mode,.is-dark-mode,[data-theme="dark"]{' . $trigger_decls . '}';
		}

		$out .= "</style>\n";
		echo $out;
	}

}

Customify_Preview_Colors::init();
