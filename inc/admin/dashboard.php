<?php

/**
 * Backward-compatibility surface for the retired legacy dashboard.
 *
 * The primary dashboard now lives at themes.php?page=customify (rendered by
 * Customify_Theme_Dashboard, the React app). This file keeps the
 * Customify_Dashboard class + a HIDDEN admin page so third-party plugins
 * (Customify Pro and friends) continue to function:
 *
 *   - Customify Pro calls Customify_Dashboard::get_instance() at boot and
 *     remove_action('customify/dashboard/main', [..., 'pro_modules_box'], 15)
 *     — both require the class + method to exist as callables.
 *   - Pro's per-module Settings flow (e.g. Typekit Kit ID) navigates to
 *     ?page=customify-legacy&module=Customify_Pro_Module_X. We register the
 *     legacy slug as a hidden submenu (parent=null) so that URL still
 *     renders — Pro hooks `customify/dashboard/content_cb` to swap in its
 *     module settings form. The hidden page is not visible in the WP
 *     sidebar; it's reached only via the deep link.
 *   - Pro also hooks `customify/dashboard/main` and
 *     `customify/dashboard/changelog/before` for its module list / asset
 *     box / changelog tabs. Those actions fire from page_inner() /
 *     tab_changelog() below if anyone navigates to the legacy URL.
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
			// Legacy URL — registered as a hidden submenu so deep links
			// from Pro plugin's per-module Settings flow keep resolving.
			self::$_instance->url = admin_url('admin.php?page=customify-legacy');

			add_action('admin_menu', array(self::$_instance, 'add_menu'), 5);
			add_action('admin_init', array(self::$_instance, 'admin_init'));
			// Legacy changelog tab routing — Pro hooks
			// `customify/dashboard/changelog/before` to inject its own
			// changelog markup, which only fires from inside tab_changelog().
			add_action('customify/dashboard/tab/changelog', array(self::$_instance, 'tab_changelog'));
		}
		return self::$_instance;
	}

	function add_url_args($args = array())
	{
		return add_query_arg($args, self::$_instance->url);
	}

	/**
	 * Register the legacy admin page as a HIDDEN submenu (no visible menu
	 * entry) so the URL ?page=customify-legacy resolves. Used by Pro
	 * plugin's per-module Settings flow.
	 */
	function add_menu()
	{
		self::$_instance->title = __('Customify Options (Legacy)', 'customify');
		add_submenu_page(
			'', // Hidden — not added to any visible menu.
			$this->title,
			$this->title,
			'manage_options',
			'customify-legacy',
			array($this, 'page')
		);
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

	function setup()
	{
		$theme = wp_get_theme();
		if (is_child_theme()) {
			$theme = $theme->parent();
		}
		$this->config = array(
			'name'    => $theme->get('Name'),
			'version' => $theme->get('Version'),
		);
		$this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
	}

	function page()
	{
		$this->setup();
		echo '<div class="wrap">';
		$this->page_header();

		// Pro's per-module Settings flow swaps the content callback through
		// this filter. Default falls back to page_inner() which fires the
		// main + sidebar action zones.
		$cb = apply_filters('customify/dashboard/content_cb', false);
		if (! is_callable($cb)) {
			$cb = array($this, 'page_inner');
		}
		if (is_callable($cb)) {
			call_user_func_array($cb, array($this));
		}

		echo '</div>';
	}

	public function page_header()
	{
		?>
		<h1 style="margin-bottom: 16px;">
			<?php echo esc_html($this->title ?: __('Customify Options', 'customify')); ?>
			<a class="page-title-action" href="<?php echo esc_url(admin_url('themes.php?page=customify')); ?>">
				<?php esc_html_e('Back to Dashboard', 'customify'); ?>
			</a>
		</h1>
		<?php
	}

	function tab_changelog()
	{
		global $wp_filesystem;
		if (! function_exists('WP_Filesystem')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		$file = get_template_directory() . '/changelog.txt';
		$file_contents = '';
		if ($wp_filesystem && $wp_filesystem->exists($file)) {
			$file_contents = $wp_filesystem->get_contents($file);
		}
		?>
		<p>
			<a class="button button-secondary" href="<?php echo esc_url($this->url); ?>">
				<?php esc_html_e('Back', 'customify'); ?>
			</a>
		</p>
		<?php
		do_action('customify/dashboard/changelog/before');
		?>
		<div class="cd-box theme-changelog">
			<div class="cd-box-top"><?php esc_html_e('Changelog', 'customify'); ?></div>
			<div class="cd-box-content">
				<pre style="width: 100%; max-height: 60vh; overflow: auto"><?php echo esc_textarea($file_contents); ?></pre>
			</div>
		</div>
		<?php
		do_action('customify/dashboard/changelog/after');
	}

	private function page_inner()
	{
		?>
		<div id="plugin-filter" class="cd-row metabox-holder">
			<hr class="wp-header-end">
			<?php
			do_action('customify/dashboard/start', $this);

			if ($this->current_tab && has_action('customify/dashboard/tab/' . $this->current_tab)) {
				do_action('customify/dashboard/tab/' . $this->current_tab, $this);
			} else {
				?>
				<div class="cd-main">
					<?php do_action('customify/dashboard/main', $this); ?>
				</div>
				<div class="cd-sidebar">
					<?php do_action('customify/dashboard/sidebar', $this); ?>
				</div>
				<?php
			}

			do_action('customify/dashboard/end', $this);
			?>
		</div>
		<?php
	}

	function admin_init()
	{
		// Backward-compat copy-settings form handler.
		if (isset($_POST['copy_from']) && isset($_POST['copy_to'])) {
			check_admin_referer('copy_theme_settings', '_nonce');
			$from = sanitize_text_field($_POST['copy_from']);
			$to   = sanitize_text_field($_POST['copy_to']);
			if ($from && $to) {
				$mods = get_option('theme_mods_' . $from);
				update_option('theme_mods_' . $to, $mods);
				$url = wp_unslash($_SERVER['REQUEST_URI']);
				$url = add_query_arg(array('copied' => 1), $url);
				wp_redirect($url);
				die();
			}
		}
	}

	/* ------------------------------------------------------------------
	 * No-op stubs preserved as valid callables for third-party plugins
	 * that referenced these via remove_action / has_action.
	 * ------------------------------------------------------------------ */

	function admin_notice() {}
	function scripts($id) {}
	function copy_theme_settings() {}
	function box_links() {}
	function box_font_icons() {}
	function box_community() {}
	function box_plugins() {}
	function box_recommend_plugins() {}
	function pro_modules_box() {}
}

Customify_Dashboard::get_instance();
