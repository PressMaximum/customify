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
	 * Convert "#RRGGBB" / "#RGB" to "r, g, b" (decimal integers, comma-separated).
	 * Returns empty string on malformed input.
	 */
	private static function hex_to_rgb_string($hex)
	{
		$h = ltrim((string) $hex, '#');
		if (3 === strlen($h)) {
			$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
		}
		if (! preg_match('/^[0-9A-Fa-f]{6}$/', $h)) {
			return '';
		}
		return hexdec(substr($h, 0, 2)) . ', ' . hexdec(substr($h, 2, 2)) . ', ' . hexdec(substr($h, 4, 2));
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

		$decls = '';
		foreach (Customify_Preview_Colors_Config::SLOTS as $slot) {
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
			'slots'         => Customify_Preview_Colors_Config::SLOTS,
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
