<?php
/**
 * Preview Colors — bootstrap.
 *
 * Frontend-only sidebar that appears when `?preview-colors=1` is in the URL
 * AND the current user has the `edit_theme_options` capability.
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
	const QUERY_PARAM = 'preview-colors';
	const HANDLE      = 'customify-preview-colors';
	const ROOT_ID     = 'customify-preview-colors-root';

	public static function init()
	{
		Customify_Preview_Colors_Ajax::init();
		// Customizer integration — registers the "Color palette" control.
		// AJAX endpoints registered above are reachable from both contexts
		// (frontend overlay + Customizer controls page).
		Customify_Preview_Colors_Customizer::init();
		Customify_Preview_Colors_Demo_Import::init();
		// One-time bootstrap: if styling.php has user-saved values and no
		// custom palette exists yet, materialise a "Theme custom" palette
		// from those values + activate it. See class for the full guard.
		Customify_Preview_Colors_Compat::init();

		if (! is_admin()) {
			// Always-on (every visitor, every page):
			//   <style>:root { --customify-color-*: … }</style> with the saved
			//   active palette so the override layer compiled into the main
			//   theme stylesheet (style-theme.css, via @import "overrides" in
			//   src/frontend/scss/style.scss) has values to consume.
			add_action('wp_head', array(__CLASS__, 'output_root_vars'), 1);

			// Admin-in-preview-mode only (gated by is_active()):
			//   - Panel sidebar JS bundle + localized data
			//   - Empty root <div> for the shadow DOM mount
			//   - Hide the WP admin bar
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue'), 100);
			// Priority 5 so the root <div> is emitted BEFORE wp_print_footer_scripts
			// (which fires at priority 20). Otherwise the bundle's IIFE runs first
			// and bails because document.getElementById(rootId) is null.
			add_action('wp_footer', array(__CLASS__, 'render_root'), 5);
			add_filter('show_admin_bar', array(__CLASS__, 'maybe_hide_admin_bar'), 100);
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
	 * Comma-separated "r, g, b" string for `rgba(var(--…-rgb), <alpha>)` consumers.
	 */
	private static function hex_to_rgb_string($hex)
	{
		$rgb = self::hex_to_rgb_array($hex);
		return null === $rgb ? '' : implode(', ', $rgb);
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

		// 1) Six user-picked slots — hex + RGB triplet.
		foreach (Customify_Preview_Colors_Config::slots() as $slot) {
			if (empty($pal['colors'][$slot])) {
				continue;
			}
			$hex = $pal['colors'][$slot];
			if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $hex)) {
				continue;
			}
			$decls .= '--customify-color-' . $slot . ':' . $hex . ';';
			$rgb = self::hex_to_rgb_string($hex);
			if ('' !== $rgb) {
				$decls .= '--customify-color-' . $slot . '-rgb:' . $rgb . ';';
			}
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

		// 2b) Text alpha derivatives + border (universal browser support).
		$text_rgb = self::hex_to_rgb_string($text);
		if ('' !== $text_rgb) {
			$decls .= '--customify-color-text-muted:rgba('     . $text_rgb . ', 0.55);';
			$decls .= '--customify-color-text-subtle:rgba('    . $text_rgb . ', 0.35);';
			$decls .= '--customify-color-border-default:rgba(' . $text_rgb . ', 0.12);';
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
			$hex = $dark[$slot];
			$decls .= '--customify-color-' . $slot . '-dark:' . $hex . ';';
			$rgb = self::hex_to_rgb_string($hex);
			if ('' !== $rgb) {
				$decls .= '--customify-color-' . $slot . '-dark-rgb:' . $rgb . ';';
			}
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

		$d_text_rgb = self::hex_to_rgb_string($d_text);
		if ('' !== $d_text_rgb) {
			$decls .= '--customify-color-text-muted-dark:rgba('     . $d_text_rgb . ', 0.55);';
			$decls .= '--customify-color-text-subtle-dark:rgba('    . $d_text_rgb . ', 0.35);';
			$decls .= '--customify-color-border-default-dark:rgba(' . $d_text_rgb . ', 0.12);';
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
				$trigger_decls .= '--customify-color-' . $slot . '-rgb:var(--customify-color-' . $slot . '-dark-rgb);';
			}
			foreach (array('on-primary', 'on-secondary', 'on-surface', 'text-muted', 'text-subtle', 'border-default', 'primary-hover', 'primary-subtle') as $k) {
				$trigger_decls .= '--customify-color-' . $k . ':var(--customify-color-' . $k . '-dark);';
			}
			$out .= '.dark-mode,.is-dark-mode,[data-theme="dark"]{' . $trigger_decls . '}';
		}

		$out .= "</style>\n";
		echo $out;
	}

	public static function maybe_hide_admin_bar($show)
	{
		return self::is_active() ? false : $show;
	}

	private static function is_active()
	{
		if (is_admin() || wp_doing_ajax()) {
			return false;
		}
		if (! isset($_GET[self::QUERY_PARAM])) {
			return false;
		}
		if (! current_user_can(Customify_Preview_Colors_Ajax::CAPABILITY)) {
			return false;
		}
		return true;
	}

	public static function enqueue()
	{
		if (! self::is_active()) {
			return;
		}

		$suffix = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		$base   = get_template_directory_uri();
		$ver    = Customify::$version ?: '0.4.13';

		// CSS is NOT enqueued via wp_enqueue_style — it lives inside the panel's
		// shadow DOM, loaded as <link> by the JS bundle. We just pass the URL.
		$css_url = $base . '/build/css/frontend/preview-colors' . $suffix . '.css?ver=' . rawurlencode($ver);

		wp_enqueue_script(
			self::HANDLE,
			$base . '/build/js/frontend/preview-colors' . $suffix . '.js',
			array(),
			$ver,
			true
		);

		wp_localize_script(self::HANDLE, 'CustomifyPreviewColors', array(
			'rootId'        => self::ROOT_ID,
			'cssUrl'        => $css_url,
			'ajaxUrl'       => admin_url('admin-ajax.php'),
			'nonce'         => wp_create_nonce(Customify_Preview_Colors_Ajax::NONCE_ACTION),
			'slots'         => Customify_Preview_Colors_Config::slots(),
			'slotDesc'      => Customify_Preview_Colors_Config::slot_descriptions(),
			'themePresets'  => Customify_Preview_Colors_Config::theme_presets(),
			'settingsRows'  => Customify_Preview_Colors_Config::settings_rows(),
			'userPalettes'  => Customify_Preview_Colors_Ajax::get_user_palettes(),
			'activeId'      => Customify_Preview_Colors_Ajax::get_active_id(),
			// Same dark-mode blobs as the Customizer control bundle — keeps
			// the resolve chain consistent across both panel hosts.
			'darkBaselines' => Customify_Preview_Colors_Dark::baselines(),
			'legacyMods'    => Customify_Preview_Colors_Customizer::collect_legacy_mods(),
		));
	}

	public static function render_root()
	{
		if (! self::is_active()) {
			return;
		}
		echo '<div id="' . esc_attr(self::ROOT_ID) . '"></div>';
	}
}

Customify_Preview_Colors::init();
