<?php

/**
 * Backward-compatibility stub for the retired legacy dashboard.
 *
 * The visible dashboard now lives at themes.php?page=customify (rendered by
 * Customify_Theme_Dashboard, the React app). This file keeps the
 * Customify_Dashboard class definition + a handful of no-op method stubs
 * so third-party plugins that referenced the old surface continue to load:
 *
 *   - Customify Pro calls Customify_Dashboard::get_instance() at boot AND
 *     remove_action('customify/dashboard/main', [Customify_Dashboard::get_instance(), 'pro_modules_box'], 15)
 *     — both require the class + method to exist as a valid callable.
 *   - Other plugins may call has_action / remove_action against any of the
 *     old box_* / page-render methods, so each previously-public method
 *     stays as a no-op stub (zero side effects, signature preserved).
 *
 * What's intentionally NOT registered here anymore:
 *   - Legacy admin menu page (no `add_action('admin_menu', …)`)
 *   - Legacy admin_init copy-settings form handler
 *   - Legacy changelog tab routing
 *   - Legacy script/style enqueue
 *
 * Per-module Pro Settings (e.g. Typekit Kit ID) now flow through the React
 * dashboard's modal (Customify::theme_dashboard_handle_pro_task — tasks
 * `get_module_settings` + `set_module_settings`), not through the old
 * `?page=customify-legacy&module=…` URL.
 *
 * The AJAX endpoint `customify_dashboard_settings` stays alive because it
 * was the documented save path for the FA-version radio (now also written
 * via the React dashboard's `save_settings` task — both update the same
 * `customify_fa_ver` option).
 */
class Customify_Dashboard
{
	static $_instance;
	public $title;
	public $config;
	public $current_tab = '';
	public $url         = '';

	static function get_instance()
	{
		add_action('wp_ajax_customify_dashboard_settings', array(__CLASS__, 'ajax'));
		if (is_null(self::$_instance)) {
			self::$_instance      = new self();
			// Point at the active React dashboard so any plugin code that
			// reads $instance->url lands somewhere useful.
			self::$_instance->url = admin_url('themes.php?page=customify');
		}
		return self::$_instance;
	}

	/**
	 * Backward-compat AJAX endpoint. Allowlists the FA-version option only.
	 */
	static function ajax()
	{
		check_admin_referer('customify_customify_dashboard', '_nonce');
		if (! current_user_can('manage_options')) {
			die(-1);
		}
		$option = isset($_REQUEST['option']) ? sanitize_text_field($_REQUEST['option']) : '';
		$value  = isset($_REQUEST['value']) ? sanitize_text_field($_REQUEST['value']) : '';
		$args   = array('success' => false);

		$allowed_options = array('customify_fa_ver');
		if ($option && in_array($option, $allowed_options, true)) {
			update_option($option, $value);
			$args['success'] = true;
		}

		wp_send_json($args);
	}

	/* ------------------------------------------------------------------
	 * No-op stubs preserved as valid callables for third-party plugins
	 * that referenced these via remove_action / has_action.
	 * ------------------------------------------------------------------ */

	function add_menu() {}
	function admin_notice() {}
	function admin_init() {}
	function scripts($id) {}
	function page() {}
	function page_header() {}
	function tab_changelog() {}
	function copy_theme_settings() {}
	function box_links() {}
	function box_font_icons() {}
	function box_community() {}
	function box_plugins() {}
	function box_recommend_plugins() {}
	function pro_modules_box() {}
}

Customify_Dashboard::get_instance();
