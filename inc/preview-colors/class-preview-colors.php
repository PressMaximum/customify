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
		$pal = self::get_active_palette();
		if (! $pal || empty($pal['colors'])) {
			return;
		}

		// 1) Six user-picked slots — hex + RGB triplet.
		$decls = '';
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

		if ('' === $decls) {
			return;
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

		echo "\n<style id=\"" . esc_attr(self::HANDLE) . "-vars\">:root{" . $decls . "}</style>\n";
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
