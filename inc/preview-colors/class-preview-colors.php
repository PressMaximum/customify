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

class Customify_Preview_Colors
{
	const QUERY_PARAM = 'preview-colors';
	const HANDLE      = 'customify-preview-colors';
	const ROOT_ID     = 'customify-preview-colors-root';

	public static function init()
	{
		Customify_Preview_Colors_Ajax::init();

		if (! is_admin()) {
			add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue'), 100);
			add_action('wp_footer', array(__CLASS__, 'render_root'), 100);
		}
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
		$ver    = defined('Customify') && Customify::$version ? Customify::$version : '0.4.13';

		wp_enqueue_style(
			self::HANDLE,
			$base . '/assets/css/preview-colors' . $suffix . '.css',
			array(),
			$ver
		);

		wp_enqueue_script(
			self::HANDLE,
			$base . '/assets/js/preview-colors' . $suffix . '.js',
			array(),
			$ver,
			true
		);

		wp_localize_script(self::HANDLE, 'CustomifyPreviewColors', array(
			'rootId'        => self::ROOT_ID,
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
