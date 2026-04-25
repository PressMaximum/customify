<?php
/**
 * Preview Colors — AJAX handlers.
 *
 * Endpoints (all `wp_ajax_*` only — no nopriv):
 *   - customify_preview_colors_save_palettes
 *   - customify_preview_colors_set_active
 *
 * @package customify
 */

if (! defined('ABSPATH')) {
	exit;
}

class Customify_Preview_Colors_Ajax
{
	const NONCE_ACTION   = 'customify_preview_colors';
	const OPTION_PALETTES = 'customify_preview_user_palettes';
	const OPTION_ACTIVE   = 'customify_preview_active_palette';
	const CAPABILITY      = 'edit_theme_options';

	public static function init()
	{
		add_action('wp_ajax_customify_preview_colors_save_palettes', array(__CLASS__, 'save_palettes'));
		add_action('wp_ajax_customify_preview_colors_set_active', array(__CLASS__, 'set_active'));
	}

	private static function guard()
	{
		if (! current_user_can(self::CAPABILITY)) {
			wp_send_json_error(array('message' => 'forbidden'), 403);
		}
		check_ajax_referer(self::NONCE_ACTION, 'nonce');
	}

	public static function get_user_palettes()
	{
		$raw = get_option(self::OPTION_PALETTES, array());
		if (! is_array($raw)) {
			return array();
		}
		return self::sanitize_palettes($raw);
	}

	public static function get_active_id()
	{
		$id = get_option(self::OPTION_ACTIVE, '');
		return is_string($id) ? sanitize_key($id) : '';
	}

	public static function save_palettes()
	{
		self::guard();

		$raw = isset($_POST['palettes']) ? wp_unslash($_POST['palettes']) : '';
		$decoded = json_decode($raw, true);
		if (! is_array($decoded)) {
			wp_send_json_error(array('message' => 'invalid_payload'), 400);
		}

		$clean = self::sanitize_palettes($decoded);
		update_option(self::OPTION_PALETTES, $clean, false);

		wp_send_json_success(array('palettes' => $clean));
	}

	public static function set_active()
	{
		self::guard();

		$id = isset($_POST['id']) ? sanitize_key(wp_unslash($_POST['id'])) : '';
		if ('' === $id) {
			wp_send_json_error(array('message' => 'invalid_id'), 400);
		}

		update_option(self::OPTION_ACTIVE, $id, false);
		wp_send_json_success(array('id' => $id));
	}

	private static function sanitize_palettes($items)
	{
		$out = array();
		foreach ($items as $item) {
			if (! is_array($item)) {
				continue;
			}
			$id   = isset($item['id']) ? sanitize_key($item['id']) : '';
			$name = isset($item['name']) ? sanitize_text_field($item['name']) : '';
			$cols = isset($item['colors']) && is_array($item['colors']) ? $item['colors'] : array();
			if ('' === $id || '' === $name) {
				continue;
			}
			$colors = array();
			foreach (Customify_Preview_Colors_Config::SLOTS as $slot) {
				$val = isset($cols[$slot]) ? trim((string) $cols[$slot]) : '';
				if (! preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $val)) {
					continue 2;
				}
				$colors[$slot] = strtoupper($val);
			}
			$out[] = array('id' => $id, 'name' => $name, 'colors' => $colors);
		}
		return $out;
	}
}
