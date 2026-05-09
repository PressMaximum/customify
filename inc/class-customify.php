<?php

class Customify
{

	static $_instance;
	static $version;
	static $theme_url;
	static $theme_name;
	static $theme_author;
	static $path;

	/**
	 * @var Customify_Customizer
	 */
	public $customizer = null;

	/**
	 * Add functions to hooks
	 */
	function init_hooks()
	{
		add_action('after_setup_theme', array($this, 'theme_setup'));
		add_action('after_setup_theme', array($this, 'content_width'), 0);
		add_action('widgets_init', array($this, 'register_sidebars'));
		add_action('wp_enqueue_scripts', array($this, 'scripts'), 95);
		add_filter('excerpt_more', array($this, 'excerpt_more'));
		add_filter('excerpt_length', array($this, 'excerpt_length'));
		add_action('wp_head', array($this, 'customify_style'), 2);

		// Force-disable WC-dependent Pro modules when WooCommerce isn't
		// active. Runs at after_setup_theme:25 — before Customify_Pro's
		// own load() at after_setup_theme:30 — so the disabled state is
		// in effect by the time Pro's load_modules() iterates and would
		// otherwise instantiate a module whose constructor calls into
		// undefined WooCommerce functions (fatal on frontend).
		add_action('after_setup_theme', array($this, 'guard_pro_module_dependencies'), 25);
		add_action('admin_notices', array($this, 'render_pro_dependency_notice'));
	}

	/**
	 * Class names of Pro modules that hard-depend on the WooCommerce plugin.
	 * If WC isn't loaded, these can't be safely instantiated — their
	 * constructors hook into actions that call wc_get_page_id() etc.
	 *
	 * Filterable via `customify/dashboard/wc_pro_modules` so child themes or
	 * future Pro releases can extend the list without editing the theme.
	 *
	 * @return array
	 */
	public function get_woocommerce_pro_modules()
	{
		$modules = array(
			'Customify_Pro_Module_WooCommerce_Booster',
			'Customify_Pro_Module_WC_Off_Canvas_Filter',
			'Customify_Pro_Module_WC_Quick_View',
			'Customify_Pro_Module_WC_Gallery_Slider',
			'Customify_Pro_Module_WC_Single_Product_Layout',
		);
		return apply_filters('customify/dashboard/wc_pro_modules', $modules);
	}

	/**
	 * Defensive shim: when WooCommerce is missing but a WC-dependent Pro
	 * module is enabled in the persisted modules option, flip it back to
	 * disabled in-place. Stash the list of forced-off modules in a transient
	 * so we can surface a one-time admin notice explaining what happened.
	 *
	 * Pre-existing Pro plugin bug: `Customify_Pro::load_modules()` does not
	 * gate WC sub-modules on WC being active, so calling their constructors
	 * registers hooks that ultimately invoke `wc_get_page_id()` and crash
	 * the whole frontend with a fatal error. This method makes the same
	 * decision Pro should be making, but from the theme side so we don't
	 * have to fork the plugin.
	 */
	public function guard_pro_module_dependencies()
	{
		if (! function_exists('Customify_Pro')) {
			return;
		}
		if ($this->is_woocommerce_active()) {
			return;
		}
		$option_key = 'customify_modules';
		$modules    = get_option($option_key);
		if (! is_array($modules) || empty($modules)) {
			return;
		}
		$wc_modules     = $this->get_woocommerce_pro_modules();
		$turned_off     = array();
		$updated_option = $modules;
		foreach ($wc_modules as $class_name) {
			if (! empty($updated_option[$class_name])) {
				$updated_option[$class_name] = 0;
				$turned_off[]                = $class_name;
			}
		}
		if (! empty($turned_off)) {
			update_option($option_key, $updated_option);
			set_transient(
				'customify_pro_modules_disabled_for_wc',
				$turned_off,
				HOUR_IN_SECONDS
			);
		}
	}

	/**
	 * Surface the auto-disable explanation as a dismissible admin notice.
	 * The transient is cleared on first read so the message doesn't repeat
	 * indefinitely after the user installs WooCommerce.
	 */
	public function render_pro_dependency_notice()
	{
		$turned_off = get_transient('customify_pro_modules_disabled_for_wc');
		if (! is_array($turned_off) || empty($turned_off)) {
			return;
		}
		delete_transient('customify_pro_modules_disabled_for_wc');
		echo '<div class="notice notice-warning is-dismissible"><p>';
		printf(
			/* translators: %s: comma-separated module class names */
			esc_html__(
				'Customify auto-disabled these Pro modules because WooCommerce is not active: %s. Install and activate WooCommerce, then re-enable them from the Customify dashboard.',
				'customify'
			),
			'<code>' . esc_html(implode(', ', $turned_off)) . '</code>'
		);
		echo '</p></div>';
	}

	function excerpt_length($length)
	{
		return 25;
	}

	/**
	 * Filter the excerpt "read more" string.
	 *
	 * @param string $more "Read more" excerpt string.
	 *
	 * @return string (Maybe) modified "read more" excerpt string.
	 */
	function excerpt_more($more)
	{
		return '&hellip;';
	}

	/**
	 * Main Customify Instance.
	 *
	 * Ensures only one instance of Customify is loaded or can be loaded.
	 *
	 * @return Customify Main instance.
	 */
	static function get_instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance    = new self();
			$theme              = wp_get_theme();
			self::$version      = $theme->get('Version');
			self::$theme_url    = $theme->get('ThemeURI');
			self::$theme_name   = $theme->get('Name');
			self::$theme_author = $theme->get('Author');
			self::$path         = get_template_directory();

			self::$_instance->init();
		}

		return self::$_instance;
	}

	/**
	 * Get data from method of property
	 *
	 * @param string $key
	 *
	 * @return bool|mixed
	 */
	function get($key)
	{
		if (method_exists($this, 'get_' . $key)) {
			return call_user_func_array(array($this, 'get_' . $key), array());
		} elseif (property_exists($this, $key)) {
			return $this->{$key};
		}

		return false;
	}


	/**
	 * Set the content width in pixels, based on the theme's design and stylesheet.
	 *
	 * Priority 0 to make it available to lower priority callbacks.
	 *
	 * @global int $content_width
	 */
	function content_width()
	{
		$GLOBALS['content_width'] = apply_filters('customify_content_width', 843);
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function theme_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on customify, use a find and replace
		 * to change 'customify' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('customify', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__('Primary', 'customify'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		// Add theme support for page excerpt.
		add_post_type_support('page', 'excerpt');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		/**
		 * WooCommerce support.
		 */
		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');

		/**
		 * Support Gutenberg / Block Editor.
		 *
		 * @since 0.2.6
		 * @since 0.4.14 Added responsive-embeds, block-template-parts, editor-color-palette.
		 */
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'block-template-parts' );
		add_theme_support( 'appearance-tools' );
		add_theme_support( 'editor-color-palette',
			array(
				array(
					'name'  => __( 'Primary', 'customify' ),
					'slug'  => 'primary',
					'color' => '#235787',
				),
				array(
					'name'  => __( 'Secondary', 'customify' ),
					'slug'  => 'secondary',
					'color' => '#c3512f',
				),
				array(
					'name'  => __( 'Text', 'customify' ),
					'slug'  => 'text',
					'color' => '#686868',
				),
				array(
					'name'  => __( 'Link', 'customify' ),
					'slug'  => 'link',
					'color' => '#1e4b75',
				),
				array(
					'name'  => __( 'Light Gray', 'customify' ),
					'slug'  => 'light-gray',
					'color' => '#f2f2f2',
				),
				array(
					'name'  => __( 'Dark Gray', 'customify' ),
					'slug'  => 'dark-gray',
					'color' => '#444444',
				),
			)
		);
	}

	/**
	 * Register sidebars area.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
	 */
	function register_sidebars()
	{
		register_sidebar(
			array(
				'name'          => esc_html__('Primary Sidebar', 'customify'),
				'id'            => 'sidebar-1',
				'description'   => esc_html__('Add widgets here.', 'customify'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
		register_sidebar(
			array(
				'name'          => esc_html__('Secondary Sidebar', 'customify'),
				'id'            => 'sidebar-2',
				'description'   => esc_html__('Add widgets here.', 'customify'),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);

		for ($i = 1; $i <= 6; $i++) {
			register_sidebar(
				array(
					/* translators: 1: Widget number. */
					'name'          => sprintf(__('Footer Sidebar %d', 'customify'), $i),
					'id'            => 'footer-' . $i,
					'description'   => __('Add widgets here.', 'customify'),
					'before_widget' => '<section id="%1$s" class="widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				)
			);
		}
	}

	/**
	 * Get asset suffix `.min` or empty if WP_DEBUG enabled
	 *
	 * @return string
	 */
	function get_asset_suffix()
	{
		$suffix = '.min';
		if (defined('WP_DEBUG') && WP_DEBUG) {
			$suffix = '';
		}

		return $suffix;
	}

	/**
	 * Enqueue scripts and styles.
	 */
	function scripts()
	{

		if (! class_exists('Customify_Font_Icons')) {
			require_once get_template_directory() . '/inc/customizer/class-customizer-icons.php';
		}
		Customify_Font_Icons::get_instance()->enqueue();

		$suffix = $this->get_asset_suffix();

		do_action('customify/load-scripts');

		$css_files = apply_filters(
			'customify/theme/css',
			array(
				'google-font' => Customify_Customizer_Auto_CSS::get_instance()->get_font_url(),
				'style'       => esc_url(get_template_directory_uri()) . '/build/css/frontend/style-theme' . $suffix . '.css',
			)
		);

		$js_files = apply_filters(
			'customify/theme/js',
			array(
				'customify-themejs' => array(
					'url' => esc_url(get_template_directory_uri()) . '/build/js/frontend/theme' . $suffix . '.js',
					'ver' => self::$version,
				),
			)
		);

		foreach ($css_files as $id => $url) {
			$deps = array();
			if (is_array($url)) {
				$arg = wp_parse_args(
					$url,
					array(
						'deps' => array(),
						'url'  => '',
						'ver'  => self::$version,
					)
				);
				wp_enqueue_style('customify-' . $id, $arg['url'], $arg['deps'], $arg['ver']);
			} elseif ($url) {
				wp_enqueue_style('customify-' . $id, $url, $deps, self::$version);
			}
		}

		foreach ($js_files as $id => $arg) {
			$deps = array();
			$ver  = '';
			if (is_array($arg)) {
				$arg = wp_parse_args(
					$arg,
					array(
						'deps' => '',
						'url'  => '',
						'ver'  => '',
					)
				);

				$deps = $arg['deps'];
				$url  = $arg['url'];
				$ver  = $arg['ver'];
			} else {
				$url = $arg;
			}

			if (! $ver) {
				$ver = self::$version;
			}

			wp_enqueue_script($id, $url, $deps, $ver, true);
		}

		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}

		wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS::get_instance()->auto_css() );
		wp_add_inline_style( 'customify-style', customify_layout_content_size_css() );
		wp_localize_script(
			'customify-themejs',
			'Customify_JS',
			apply_filters( // phpcs:ignore
				'Customify_JS',
				array(
					'is_rtl'                     => is_rtl(),
					'css_media_queries'          => Customify_Customizer_Auto_CSS::get_instance()->media_queries,
					'sidebar_menu_no_duplicator' => Customify()->get_setting('header_sidebar_menu_no_duplicator'),
				)
			)
		);

		do_action('customify/theme/scripts');
	}

	public function customify_style() {}

	function admin_scripts() {}

	private function includes()
	{
		$files = array(
			'/inc/class-metabox.php',
			// Metabox settings.
			'/inc/template-class.php',
			// Template element classes.
			'/inc/extras.php',
			// Custom functions that act independently of the theme templates.
			'/inc/element-classes.php',
			// Functions which enhance the theme by hooking into WordPress and itself (huh?).
			'/inc/template-tags.php',
			// Custom template tags for this theme.
			'/inc/template-functions.php',
			// Functions which enhance the theme by hooking into WordPress.
			'/inc/customizer/class-customizer.php',
			// Customizer additions.
			'/inc/panel-builder/class-panel-builder.php',
			// Panel builder additions.
			'/inc/blog/class-related-posts.php',
			// Blog entry builder.
			'/inc/blog/class-post-entry.php',
			// Blog entry builder.
			'/inc/blog/class-posts-layout.php',
			// Blog posts layout.
			'/inc/blog/functions-posts-layout.php',
			// Block editor enhancements (block styles, patterns category).
			'/inc/admin/block-styles.php',
			// Block editor Page Settings panel (also registers meta for REST API).
			'/inc/admin/page-settings.php',
		);

		foreach ($files as $file) {
			require_once self::$path . $file;
		}

		add_action('after_setup_theme', [$this, 'load_configs'], 2);
		add_action('after_setup_theme', [$this, 'load_compatibility'], 2);
		$this->admin_includes();

		// Custom categories walker class.
		if (! is_admin()) {
			require_once self::$path . '/inc/class-categories-walker.php';
		}
	}

	/**
	 * Load admin files
	 *
	 * @since 0.0.1
	 * @since 0.2.6 Load editor style.
	 *
	 * @return void
	 */
	public function admin_includes()
	{
		if (! is_admin()) {
			return;
		}

		$files = array(
			'/inc/admin/editor.php',                // Block editor style integration.
			'/inc/admin/dashboard.php',             // Legacy dashboard — kept for 3rd-party plugin compatibility (Customify_Dashboard class + customify/dashboard/* hooks).
			'/inc/admin/class-theme-dashboard.php', // React-based top-level dashboard.
		);

		foreach ($files as $file) {
			require_once self::$path . $file;
		}

		add_action('init', array($this, 'boot_theme_dashboard'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
	}

	/**
	 * Boot the React-based theme dashboard.
	 *
	 * Wrapped in `init` because the `name` config flows through `__()`. WP 6.7+
	 * warns when translation functions run before text domains are loaded; `init`
	 * fires after that, so it's the safe entry point.
	 */
	public function boot_theme_dashboard()
	{
		if (! class_exists('Customify_Theme_Dashboard')) {
			return;
		}
		Customify_Theme_Dashboard::init(array(
			'slug'        => 'customify',
			'name'        => __('Customify', 'customify'),
			'menu_title'  => __('Customify Options', 'customify'),
			'menu_parent' => 'themes.php', // Nest under Appearance.
			'version'     => self::$version,
			'path'        => trailingslashit(self::$path),
			'url'         => trailingslashit(get_template_directory_uri()),
			// CSS sits at css/backend/dashboard/style-index.css because the React
			// entry imports `./style.css`; wp-scripts prepends `style-` to entry-
			// triggered stylesheet emits. JS + asset.php still use `index`.
			'css_rel'     => 'build/css/backend/dashboard/style-index.css',
			// Seed defaults + sanitize from this class so groups added by Pro
			// (e.g. `pro.assets_compress`) round-trip correctly.
			'defaults'    => array($this, 'theme_dashboard_defaults'),
			'sanitize'    => array($this, 'theme_dashboard_sanitize'),
		));

		// Mirror new dashboard saves back to legacy single-option keys so
		// downstream consumers (Customizer icon loader, etc.) keep working
		// without a separate migration pass.
		add_action('customify/dashboard/saved', array($this, 'theme_dashboard_mirror_to_legacy'));

		// Surface Customify Pro module list + state to the React app and
		// route the toggle AJAX task into Customify_Pro's enable/disable
		// helpers. Both are no-ops when the Pro plugin isn't active.
		add_filter('customify/dashboard/bootstrap_data', array($this, 'theme_dashboard_inject_pro_data'));
		add_action('customify/dashboard/ajax_task', array($this, 'theme_dashboard_handle_pro_task'));

		// Detect which Welcome > "Things to do" checklist items the user has
		// already configured so the dashboard can pre-check them.
		add_filter('customify/dashboard/bootstrap_data', array($this, 'theme_dashboard_inject_todo_status'));

		// Surface the Customify Sites Library plugin's install/activate
		// state so the Welcome sidebar can render the right CTA.
		add_filter('customify/dashboard/bootstrap_data', array($this, 'theme_dashboard_inject_sites_plugin'));

		// Per-user "hide Things to do" dismissal — bootstrap reads it,
		// AJAX task `set_things_to_do_hidden` persists it.
		add_filter('customify/dashboard/bootstrap_data', array($this, 'theme_dashboard_inject_todo_hidden'));
		add_action('customify/dashboard/ajax_task', array($this, 'theme_dashboard_handle_todo_hidden'));

		// Welcome sidebar — recommended free plugins from wordpress.org.
		add_filter('customify/dashboard/bootstrap_data', array($this, 'theme_dashboard_inject_recommend_plugins'));
	}

	/**
	 * Default settings shape for the React dashboard. Reads the legacy
	 * stand-alone options so an upgrade from the old jQuery dashboard
	 * preserves user choices.
	 */
	public function theme_dashboard_defaults()
	{
		return array(
			'icons' => array(
				'fa_version' => get_option('customify_fa_ver', 'v4'),
			),
			'pro' => array(
				// Customify Pro stores this as 'on'/'off'; we expose it as a
				// boolean to the React app and convert in the mirror.
				'assets_compress' => get_option('customify_pro_assets_compress', 'on') === 'on',
			),
		);
	}

	/**
	 * Sanitize the React dashboard's settings payload.
	 *
	 * @param array $incoming Raw settings posted from the client.
	 * @return array
	 */
	public function theme_dashboard_sanitize($incoming)
	{
		if (! is_array($incoming)) {
			$incoming = array();
		}
		$defaults = $this->theme_dashboard_defaults();
		$out      = array();

		// Icons group.
		$icons_in     = isset($incoming['icons']) && is_array($incoming['icons']) ? $incoming['icons'] : array();
		$out['icons'] = array(
			'fa_version' => in_array(
				isset($icons_in['fa_version']) ? $icons_in['fa_version'] : '',
				array('v4', 'v6', 'v456'),
				true
			)
				? $icons_in['fa_version']
				: $defaults['icons']['fa_version'],
		);

		// Pro group.
		$pro_in     = isset($incoming['pro']) && is_array($incoming['pro']) ? $incoming['pro'] : array();
		$out['pro'] = array(
			'assets_compress' => isset($pro_in['assets_compress'])
				? (bool) $pro_in['assets_compress']
				: $defaults['pro']['assets_compress'],
		);

		return $out;
	}

	/**
	 * After the React dashboard saves, copy the relevant fields back to the
	 * legacy single-key options the rest of the theme reads from.
	 *
	 * @param array $sanitized The sanitized settings just saved.
	 */
	public function theme_dashboard_mirror_to_legacy($sanitized)
	{
		if (! is_array($sanitized)) {
			return;
		}
		if (isset($sanitized['icons']['fa_version'])) {
			update_option('customify_fa_ver', $sanitized['icons']['fa_version']);
		}
		// Mirror Pro's "Combine module assets" toggle into the option key
		// Customify Pro reads from ('on'|'off' — not bool). Then ask the Pro
		// asset compiler to clear its cache so the new mode takes effect on
		// next page load.
		if (isset($sanitized['pro']['assets_compress'])) {
			$flag = $sanitized['pro']['assets_compress'] ? 'on' : 'off';
			update_option('customify_pro_assets_compress', $flag);
			if (class_exists('Customify_Pro_Assets')
				&& method_exists('Customify_Pro_Assets', 'get_instance')
			) {
				$assets = Customify_Pro_Assets::get_instance();
				if (is_object($assets) && method_exists($assets, 'clear')) {
					$assets->clear();
				}
			}
		}
	}

	/**
	 * Inject Customify Pro module list + on/off state into the React app's
	 * bootstrap window object.
	 *
	 * Output shape (only present when the Pro plugin is loaded):
	 *   proActive:  bool
	 *   proModules: [
	 *     { classKey, name, description, docHref, enabled, parent, subModules: [classKey] },
	 *     ...
	 *   ]
	 *
	 * @param array $data Existing bootstrap data.
	 * @return array
	 */
	public function theme_dashboard_inject_pro_data($data)
	{
		$pro = $this->get_pro_instance();
		if (! $pro) {
			$data['proActive']  = false;
			$data['proModules'] = array();
			return $data;
		}

		$wc_modules    = $this->get_woocommerce_pro_modules();
		$wc_active     = $this->is_woocommerce_active();
		$modules       = array();
		foreach ($pro->modules as $class_name => $args) {
			// React opens module settings via the `get_module_settings` /
			// `set_module_settings` AJAX tasks. We only need to flag which
			// modules expose a settings() method — generic check so future
			// Pro modules light up the modal automatically.
			$has_settings = class_exists($class_name) && method_exists($class_name, 'settings');
			$meta         = $this->resolve_pro_module_meta($pro, $class_name, $args, $has_settings);
			$can_toggle   = isset($args['can_toggle']) ? (bool) $args['can_toggle'] : true;

			// Surface the WooCommerce requirement so the dashboard can show
			// a "Requires WooCommerce" warning and lock the toggle off. Lets
			// users self-diagnose instead of toggling on a broken module.
			$requires = '';
			if (in_array($class_name, $wc_modules, true) && ! $wc_active) {
				$requires   = 'woocommerce';
				$can_toggle = false;
			}

			$modules[] = array(
				'classKey'    => $class_name,
				'name'        => isset($args['name']) ? (string) $args['name'] : $class_name,
				'description' => isset($args['desc']) ? (string) $args['desc'] : '',
				'docHref'     => isset($args['doc_link']) ? (string) $args['doc_link'] : '',
				'enabled'     => (bool) $pro->is_enabled_module($class_name),
				'canToggle'   => $can_toggle,
				'parent'      => isset($args['parent']) && $args['parent'] ? (string) $args['parent'] : null,
				'subModules'  => isset($args['sub_modules']) && is_array($args['sub_modules'])
					? array_values($args['sub_modules'])
					: array(),
				'hasSettings'        => $has_settings,
				'settingsScope'      => $meta['scope'],
				'settingsHref'       => $meta['href'],
				'settingsLabel'      => $meta['label'],
				'requirementMissing' => $requires,
			);
		}

		$data['proActive']  = true;
		$data['proModules'] = $modules;

		// Asset combiner status — disables the Performance toggle when the
		// save dir isn't writable, matches the legacy Pro dashboard's UX.
		if (class_exists('Customify_Pro_Assets')
			&& method_exists('Customify_Pro_Assets', 'get_instance')
		) {
			$assets_instance = Customify_Pro_Assets::get_instance();
			if (is_object($assets_instance) && property_exists($assets_instance, 'save_dir')) {
				$data['proAssetsSavePath'] = (string) $assets_instance->save_dir;
				$data['proAssetsWritable'] = (bool) is_writable($assets_instance->save_dir);
			}
		}

		return $data;
	}

	/**
	 * Handle the `set_module_state` AJAX task from the React dashboard.
	 *
	 * Expects POST: class_name (string), enabled (truthy).
	 * Calls into Customify_Pro::enable_module() / disable_module() then
	 * responds with the refreshed flag. Other tasks fall through.
	 *
	 * @param string $task Sanitized task key from ajax_dispatch().
	 */
	public function theme_dashboard_handle_pro_task($task)
	{
		if (! in_array($task, array('set_module_state', 'get_module_settings', 'set_module_settings'), true)) {
			return;
		}
		$pro = $this->get_pro_instance();
		if (! $pro) {
			wp_send_json_error('pro_inactive', 400);
		}

		$class_name = isset($_POST['class_name']) ? sanitize_text_field(wp_unslash($_POST['class_name'])) : '';
		if (! $class_name || ! isset($pro->modules[$class_name])) {
			wp_send_json_error('unknown_module', 400);
		}

		switch ($task) {
			case 'set_module_state':
				$enabled = isset($_POST['enabled']) ? rest_sanitize_boolean(wp_unslash($_POST['enabled'])) : false;
				// Reject enabling a WC-dependent module when WooCommerce is
				// not active. Same guard as guard_pro_module_dependencies()
				// but at the AJAX boundary, so the dashboard surfaces the
				// reason instead of letting the toggle persist and then
				// crash the frontend.
				if ($enabled
					&& in_array($class_name, $this->get_woocommerce_pro_modules(), true)
					&& ! $this->is_woocommerce_active()
				) {
					wp_send_json_error('woocommerce_required', 400);
				}
				if ($enabled) {
					$pro->enable_module($class_name);
				} else {
					$pro->disable_module($class_name);
				}
				wp_send_json_success(array(
					'classKey' => $class_name,
					'enabled'  => (bool) $pro->is_enabled_module($class_name),
				));
				break;

			case 'get_module_settings':
				$module = $this->resolve_pro_module_instance($pro, $class_name);
				if (! $module || ! method_exists($module, 'settings')) {
					wp_send_json_error('no_settings', 400);
				}
				wp_send_json_success(array(
					'fields' => $this->normalize_pro_module_fields($module->settings()),
					'values' => method_exists($module, 'get_settings') ? (array) $module->get_settings() : array(),
				));
				break;

			case 'set_module_settings':
				$module = $this->resolve_pro_module_instance($pro, $class_name);
				if (! $module || ! method_exists($module, 'save')) {
					wp_send_json_error('no_settings', 400);
				}
				$raw    = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
				$values = is_string($raw) ? json_decode($raw, true) : array();
				if (! is_array($values)) {
					$values = array();
				}
				$module->save($this->sanitize_pro_module_values($module->settings(), $values));

				// Pro modules echo `<div class="notice ...">` markup from
				// after_save() (the legacy admin page rendered them inline).
				// Capture that buffer so the React modal can surface them as
				// snackbar notices instead of swallowing the output.
				$notices = array();
				if (method_exists($module, 'after_save')) {
					ob_start();
					$module->after_save($this);
					$buffered = ob_get_clean();
					$notices  = $this->parse_pro_admin_notices($buffered);
				}
				do_action('customify-pro/after-module-saved', $this);

				// Re-derive the schema after save — Pro modules like Typekit
				// generate dynamic html fields (loaded fonts list) that reflect
				// the just-fetched data. Returning fresh `fields` lets the
				// modal refresh its UI without a full reopen.
				$fresh_fields = method_exists($module, 'settings')
					? $this->normalize_pro_module_fields($module->settings())
					: array();

				wp_send_json_success(array(
					'values'  => method_exists($module, 'get_settings') ? (array) $module->get_settings() : array(),
					'fields'  => $fresh_fields,
					'notices' => $notices,
				));
				break;
		}
	}

	/**
	 * Decide where a Pro module exposes its settings so the React dashboard
	 * can render the right action: an inline modal, a Customizer deep-link,
	 * a CPT admin screen, or no action at all.
	 *
	 * Returns shape: `[ 'scope' => 'inline'|'customizer'|'cpt'|'none',
	 *                  'href'  => string|null,
	 *                  'label' => string|null ]`.
	 *
	 * Scopes:
	 *   - `inline`     — module's settings() returns a non-empty schema; the
	 *                    modal can render it.
	 *   - `customizer` — module is style-only; deep-link to the right panel.
	 *   - `cpt`        — module manages content via a custom post type.
	 *   - `none`       — toggle-only, no per-module settings to expose.
	 *
	 * Filterable via `customify/dashboard/pro_module_meta` so child themes
	 * or future Pro modules can override without forking.
	 *
	 * @param object $pro
	 * @param string $class_name
	 * @param array  $args         Pro registry args for this module.
	 * @param bool   $has_settings Whether the module exposes settings().
	 * @return array
	 */
	private function resolve_pro_module_meta($pro, $class_name, $args, $has_settings)
	{
		$meta = array(
			'scope' => 'none',
			'href'  => null,
			'label' => null,
		);

		// Static map of well-known modules whose UI lives elsewhere. Listed
		// here (not in the Pro plugin) so we don't have to edit Pro to teach
		// the dashboard new tricks. Extendable via the filter below.
		$customizer_panels = array(
			'Customify_Pro_Module_Header_Sticky'             => 'panel_header',
			'Customify_Pro_Module_Header_Transparent'        => 'panel_header',
			'Customify_Pro_Module_Header_Footer_Items'       => 'panel_header',
			'Customify_Pro_Module_Multiple_Headers'          => 'panel_header',
			'Customify_Pro_Module_Mega_Menu'                 => 'panel_header',
			'Customify_Pro_Module_Scrolltop'                 => 'general',
			'Customify_Pro_Module_Cookie_Notice'             => 'general',
			'Customify_Pro_Module_Blog'                      => 'panel_blog',
			'Customify_Pro_Module_Advanced_Styling'          => 'styling',
			'Customify_Pro_Module_Infinity'                  => 'panel_blog',
			'Customify_Pro_Module_Woo_Booster'               => 'woocommerce',
		);
		$cpt_slugs = array(
			'Customify_Pro_Module_Custom_Fonts'      => 'font',
			'Customify_Pro_Module_Hooks'             => 'customify_hook',
			'Customify_Pro_Module_Multiple_Headers'  => 'customify_ms',
			'Customify_Pro_Module_Portfolio'         => 'portfolio',
		);

		if ($has_settings) {
			$meta['scope'] = 'inline';
			$meta['label'] = __('Settings', 'customify');
		} elseif (isset($cpt_slugs[$class_name])) {
			$meta['scope'] = 'cpt';
			$meta['href']  = admin_url('edit.php?post_type=' . $cpt_slugs[$class_name]);
			$meta['label'] = __('Manage', 'customify');
		} elseif (isset($customizer_panels[$class_name])) {
			$meta['scope'] = 'customizer';
			$meta['href']  = add_query_arg(
				'autofocus[panel]',
				$customizer_panels[$class_name],
				admin_url('customize.php')
			);
			$meta['label'] = __('Open Customizer', 'customify');
		}

		/**
		 * Filter the resolved settings UI metadata for a Pro module.
		 *
		 * @param array  $meta       { scope, href, label }
		 * @param string $class_name Pro module class.
		 * @param array  $args       Pro registry args.
		 * @param object $pro        Customify_Pro instance.
		 */
		$meta = apply_filters(
			'customify/dashboard/pro_module_meta',
			$meta,
			$class_name,
			$args,
			$pro
		);

		// Defensive: filter callers may return a partial array.
		$meta = wp_parse_args(
			is_array($meta) ? $meta : array(),
			array('scope' => 'none', 'href' => null, 'label' => null)
		);

		return $meta;
	}

	/**
	 * Resolve a Pro module instance — prefer the one Pro instantiated at
	 * boot (active modules), fall back to a fresh `new $class_name()` so we
	 * can read settings even on the first request after enabling.
	 *
	 * @param object $pro
	 * @param string $class_name
	 * @return object|null
	 */
	private function resolve_pro_module_instance($pro, $class_name)
	{
		if (method_exists($pro, 'get_module')) {
			$module = $pro->get_module($class_name, true);
			if (is_object($module)) {
				return $module;
			}
		}
		if (class_exists($class_name)) {
			if (method_exists($class_name, 'get_instance')) {
				return $class_name::get_instance();
			}
			return new $class_name();
		}
		return null;
	}

	/**
	 * Convert Pro's settings() schema into a JSON-friendly shape for the
	 * React modal: each select field's options array becomes a list of
	 * `{value, label}` pairs.
	 *
	 * @param array $fields
	 * @return array
	 */
	private function normalize_pro_module_fields($fields)
	{
		if (! is_array($fields)) {
			return array();
		}
		$out = array();
		foreach ($fields as $field) {
			if (! is_array($field)) {
				continue;
			}
			$type = isset($field['type']) ? (string) $field['type'] : 'text';
			$normalized = array(
				'type'    => $type,
				'name'    => isset($field['name']) ? (string) $field['name'] : '',
				'label'   => $this->decode_pro_label(
					isset($field['label']) ? $field['label'] : (isset($field['title']) ? $field['title'] : '')
				),
				// `desc` is allowed to carry simple HTML (e.g. an <a> link)
				// because the React side parses it explicitly. Leave entities
				// intact so URLs containing & survive.
				'desc'    => isset($field['desc']) ? (string) $field['desc'] : '',
				'content' => isset($field['content']) ? (string) $field['content'] : '',
			);

			// Pass through extra metadata used by the corresponding React
			// renderer. Pro already encodes these per field type (e.g. min/max
			// on `slider`, `placeholder` on text fields). Unknown keys are
			// dropped — the React side gracefully falls back to a text input
			// for types it doesn't recognize, so adding fields here is purely
			// additive.
			$passthrough = array(
				'placeholder',
				'default',
				'min',
				'max',
				'step',
				'rows',
				'checkbox_label',
				'description',
			);
			foreach ($passthrough as $key) {
				if (isset($field[$key])) {
					$normalized[$key] = is_scalar($field[$key])
						? (string) $field[$key]
						: $field[$key];
				}
			}

			// `select` and `radio_group` advertise their options under
			// `options`; some Pro fields use the Customizer-style `choices`
			// key. Normalize both shapes into a list of {value,label} pairs.
			$opt_source = null;
			if (isset($field['options']) && is_array($field['options'])) {
				$opt_source = $field['options'];
			} elseif (isset($field['choices']) && is_array($field['choices'])) {
				$opt_source = $field['choices'];
			}
			if (in_array($type, array('select', 'radio_group', 'image_select', 'text_align', 'text_align_no_justify'), true)
				&& is_array($opt_source)
			) {
				$opts = array();
				foreach ($opt_source as $value => $label) {
					if (is_array($label)) {
						// `image_select` uses `[value => ['img' => ..., 'label' => ...]]`.
						$opts[] = array(
							'value' => (string) $value,
							'label' => $this->decode_pro_label(
								isset($label['label']) ? $label['label'] : $value
							),
							'image' => isset($label['img']) ? (string) $label['img'] : '',
						);
					} else {
						$opts[] = array(
							'value' => (string) $value,
							// Pro hand-encodes select labels (e.g. "Use &lt;link&gt; tag")
							// because the legacy form rendered them as HTML text.
							// React's <option>{label}</option> writes JS text directly,
							// so decode here to avoid `&lt;` showing up literally.
							'label' => $this->decode_pro_label($label),
						);
					}
				}
				$normalized['options'] = $opts;
			}

			/**
			 * Per-field hook so a child theme or future Pro module can attach
			 * extra metadata (e.g. icon list, font preview URL) without
			 * editing the theme.
			 *
			 * @param array $normalized The serializable schema row.
			 * @param array $field      Original schema row from settings().
			 */
			$normalized = apply_filters(
				'customify/dashboard/pro_field_normalize',
				$normalized,
				$field
			);

			$out[] = is_array($normalized) ? $normalized : array();
		}
		return $out;
	}

	/**
	 * Decode HTML entities in a Pro field label so they render correctly when
	 * React writes them as plain text inside an <option> or <label>.
	 *
	 * No tag-stripping: React's text interpolation `{value}` escapes
	 * angle brackets when it writes them into the DOM, so a decoded
	 * `<link>` shows up as the literal characters the user expects rather
	 * than executing as markup.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function decode_pro_label($value)
	{
		return html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	/**
	 * Drop unknown keys + coerce incoming values to the type advertised by
	 * the field schema. Pro module's save() trusts its caller, so we
	 * sanitize at our boundary.
	 *
	 * @param array $fields
	 * @param array $values
	 * @return array
	 */
	private function sanitize_pro_module_values($fields, $values)
	{
		if (! is_array($fields) || ! is_array($values)) {
			return array();
		}
		$out = array();
		foreach ($fields as $field) {
			if (empty($field['name']) || ! isset($values[$field['name']])) {
				continue;
			}
			$type = isset($field['type']) ? $field['type'] : 'text';
			$raw  = $values[$field['name']];
			switch ($type) {
				case 'select':
				case 'radio_group':
				case 'image_select':
				case 'text_align':
				case 'text_align_no_justify':
					$allowed = $this->collect_field_options($field);
					$value   = sanitize_text_field((string) $raw);
					if ($allowed && ! in_array($value, $allowed, true)) {
						$value = isset($allowed[0]) ? $allowed[0] : '';
					}
					$out[$field['name']] = $value;
					break;
				case 'checkbox':
					$out[$field['name']] = rest_sanitize_boolean($raw) ? 1 : 0;
					break;
				case 'number':
				case 'slider':
					if ('' === $raw || null === $raw) {
						break; // leave unset so default applies
					}
					$num = is_numeric($raw) ? floatval($raw) : 0;
					if (isset($field['min']) && is_numeric($field['min'])) {
						$num = max(floatval($field['min']), $num);
					}
					if (isset($field['max']) && is_numeric($field['max'])) {
						$num = min(floatval($field['max']), $num);
					}
					$out[$field['name']] = $num;
					break;
				case 'color':
					$value = is_string($raw) ? trim($raw) : '';
					// Allow common forms: #rgb, #rrggbb, #rrggbbaa, rgb()/rgba().
					if ($value === '' || preg_match('/^(#([0-9a-f]{3,8})|rgba?\([0-9,\s.\/%-]+\))$/i', $value)) {
						$out[$field['name']] = $value;
					}
					break;
				case 'textarea':
				case 'custom_html':
				case 'text/html':
					// Same boundary as wp_kses_post: keep common HTML, strip
					// scripts/iframes. Pro modules that genuinely need raw
					// markup (Hooks PHP/HTML) live in CPT, not in this modal.
					$out[$field['name']] = wp_kses_post((string) $raw);
					break;
				case 'email':
					$value = sanitize_email((string) $raw);
					$out[$field['name']] = $value;
					break;
				case 'phone':
					$out[$field['name']] = preg_replace('/[^0-9+\-\s().]/', '', (string) $raw);
					break;
				case 'image':
				case 'icon':
					$out[$field['name']] = sanitize_text_field((string) $raw);
					break;
				case 'hidden':
					$out[$field['name']] = sanitize_text_field((string) $raw);
					break;
				case 'html':
				case 'heading':
				case 'section':
				case 'panel':
					// Display-only fields, no persisted value.
					break;
				default:
					// `text` and any unknown future type — fall back to plain
					// text. Future-proof: if Pro adds a new type before this
					// theme catches up, the modal renders a text input and we
					// store the string verbatim.
					$out[$field['name']] = sanitize_text_field((string) $raw);
			}
		}
		return $out;
	}

	/**
	 * Read the allowed value list off a normalized field schema, supporting
	 * both the new {value,label} list shape and the legacy associative array.
	 *
	 * @param array $field
	 * @return array
	 */
	private function collect_field_options($field)
	{
		$allowed = array();
		if (isset($field['options']) && is_array($field['options'])) {
			foreach ($field['options'] as $key => $val) {
				if (is_array($val) && isset($val['value'])) {
					$allowed[] = (string) $val['value'];
				} else {
					$allowed[] = (string) $key;
				}
			}
		}
		if (! $allowed && isset($field['choices']) && is_array($field['choices'])) {
			$allowed = array_map('strval', array_keys($field['choices']));
		}
		return $allowed;
	}

	/**
	 * Extract `<div class="notice notice-{type}">…</div>` blocks from a Pro
	 * module's after_save() output. The legacy admin page rendered these
	 * inline; the new dashboard turns them into snackbar notices.
	 *
	 * Returns an array of `{ type: 'success'|'error'|'warning'|'info', message }`.
	 *
	 * @param string $html Buffered output from the module's after_save().
	 * @return array
	 */
	private function parse_pro_admin_notices($html)
	{
		if (! is_string($html) || '' === trim($html)) {
			return array();
		}
		$notices = array();
		if (! preg_match_all('/<div[^>]*class="[^"]*notice[^"]*"[^>]*>([\s\S]*?)<\/div>/i', $html, $matches, PREG_SET_ORDER)) {
			return array();
		}
		foreach ($matches as $match) {
			$full = isset($match[0]) ? $match[0] : '';
			$body = isset($match[1]) ? $match[1] : '';
			$type = 'info';
			if (preg_match('/notice-(success|error|warning|info)/i', $full, $tm)) {
				$type = strtolower($tm[1]);
			}
			$message = trim(wp_strip_all_tags($body));
			if ('' === $message) {
				continue;
			}
			$notices[] = array(
				'type'    => $type,
				'message' => $message,
			);
		}
		return $notices;
	}

	/**
	 * Surface a per-item "done" map for the Welcome > Things-to-do checklist.
	 *
	 * Detection runs against persisted state (theme_mods + options), not
	 * against runtime defaults — a setting that just returns the theme's
	 * default value counts as untouched. Item IDs match the entries in
	 * src/backend/dashboard/app/data/things-to-do.js.
	 *
	 * Filterable via `customify/dashboard/things_to_do_status` so a child
	 * theme or plugin can override or add detection for custom items.
	 *
	 * @param array $data Bootstrap data passed through the filter chain.
	 * @return array
	 */
	public function theme_dashboard_inject_todo_status($data)
	{
		$theme_mods    = get_option('theme_mods_' . get_stylesheet());
		$theme_mods    = is_array($theme_mods) ? $theme_mods : array();
		$primary_color = isset($theme_mods['global_styling_color_primary'])
			? $theme_mods['global_styling_color_primary']
			: '';

		// "Header builder configured" if the user has saved any header_* key
		// to theme_mods. Cheap because we only look at the persisted blob —
		// no live get_theme_mod() calls (those would always return defaults).
		$header_touched = false;
		foreach (array_keys($theme_mods) as $mod_key) {
			if (is_string($mod_key) && 0 === strpos($mod_key, 'header_')) {
				$header_touched = true;
				break;
			}
		}

		$status = array(
			'logo'           => isset($theme_mods['custom_logo']) && (int) $theme_mods['custom_logo'] > 0,
			'header-builder' => $header_touched,
			'styling'        => $primary_color !== '' && $primary_color !== '#235787',
			'icons'          => false !== get_option('customify_fa_ver', false)
				|| isset(get_option('customify_dashboard_settings', array())['icons']['fa_version']),
		);

		/**
		 * Filter the things-to-do completion map before it ships to the
		 * React app. Keys are item IDs from things-to-do.js; values are
		 * boolean "done?" flags.
		 *
		 * @param array $status Default detection results.
		 */
		$data['thingsToDoStatus'] = (array) apply_filters('customify/dashboard/things_to_do_status', $status);

		return $data;
	}

	/**
	 * Bootstrap key for the user's "hide Things to do" dismissal. Per-user
	 * (user_meta) so each admin can dismiss independently.
	 *
	 * @param array $data
	 * @return array
	 */
	public function theme_dashboard_inject_todo_hidden($data)
	{
		$user_id                 = get_current_user_id();
		$data['thingsToDoHidden'] = $user_id
			? '1' === (string) get_user_meta($user_id, 'customify_things_to_do_hidden', true)
			: false;
		return $data;
	}

	/**
	 * AJAX handler for `set_things_to_do_hidden` — persists the dismissal
	 * to user_meta. Expects POST `hidden` (truthy/falsy).
	 *
	 * @param string $task
	 */
	public function theme_dashboard_handle_todo_hidden($task)
	{
		if ('set_things_to_do_hidden' !== $task) {
			return;
		}
		$user_id = get_current_user_id();
		if (! $user_id) {
			wp_send_json_error('no_user', 400);
		}
		$hidden = isset($_POST['hidden']) ? rest_sanitize_boolean(wp_unslash($_POST['hidden'])) : false;
		if ($hidden) {
			update_user_meta($user_id, 'customify_things_to_do_hidden', '1');
		} else {
			delete_user_meta($user_id, 'customify_things_to_do_hidden');
		}
		wp_send_json_success(array('hidden' => (bool) $hidden));
	}

	/**
	 * Build the Welcome sidebar "Recommend Plugins" list.
	 *
	 * Mirrors the legacy Customify_Dashboard::box_recommend_plugins(): fetch
	 * plugin info from wordpress.org via plugins_api (cached 12h in a
	 * transient), skip already-active plugins, return install/activate
	 * action URLs for the rest. The plugin list itself stays filterable
	 * via `customify/recommend-plugins` so the slug list matches the old
	 * extension point.
	 *
	 * @param array $data
	 * @return array
	 */
	public function theme_dashboard_inject_recommend_plugins($data)
	{
		$slugs = array(
			'themeisle-companion',
			'filebird',
		);
		if (function_exists('WC')) {
			$slugs[] = 'currency-switcher-for-woocommerce';
			$slugs[] = 'bulk-edit-for-woocommerce';
		}
		/** @see Customify_Dashboard::box_recommend_plugins() */
		$slugs = (array) apply_filters('customify/recommend-plugins', $slugs);

		// Cache key includes the resolved slug list so a filter change
		// invalidates the transient automatically.
		$cache_key = 'customify_plugins_info_' . wp_hash(wp_json_encode($slugs));
		$infos     = get_transient($cache_key);
		if (false === $infos) {
			if (! function_exists('plugins_api')) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}
			$infos = array();
			foreach ($slugs as $slug) {
				$info = plugins_api(
					'plugin_information',
					array('slug' => $slug, 'fields' => array('icons' => true))
				);
				if (! is_wp_error($info)) {
					$infos[$slug] = $info;
				}
			}
			set_transient($cache_key, $infos, 12 * HOUR_IN_SECONDS);
		}

		if (! function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_files = array_keys(get_plugins());

		$plugins = array();
		foreach ($infos as $slug => $info) {
			if (! is_object($info)) {
				continue;
			}

			$plugin_file = $this->resolve_plugin_file($installed_files, $slug);
			$is_active   = $plugin_file && is_plugin_active($plugin_file);

			// Match legacy behaviour: don't surface already-active plugins.
			if ($is_active) {
				continue;
			}

			$icons    = property_exists($info, 'icons') ? (array) $info->icons : array();
			$icon_url = '';
			if (isset($icons['1x'])) {
				$icon_url = (string) $icons['1x'];
			} elseif (! empty($icons)) {
				$icon_url = (string) reset($icons);
			}

			if ($plugin_file) {
				$state        = 'installed';
				$action_url   = wp_nonce_url(
					'plugins.php?action=activate&plugin=' . urlencode($plugin_file),
					'activate-plugin_' . $plugin_file
				);
				$action_label = __('Activate', 'customify');
			} else {
				$state        = 'not-installed';
				$action_url   = wp_nonce_url(
					add_query_arg(
						array('action' => 'install-plugin', 'plugin' => $slug),
						network_admin_url('update.php')
					),
					'install-plugin_' . $slug
				);
				$action_label = __('Install Now', 'customify');
			}

			// wp.org plugin names ship HTML-encoded (e.g. "Plugin &#8211; Add-on",
			// "Foo &amp; Bar") because the legacy admin UI rendered them as
			// HTML. React's `{name}` interpolation writes JS text directly so
			// the entities would show up literally — decode here.
			$name = isset($info->name) ? (string) $info->name : $slug;
			$name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

			$plugins[] = array(
				'slug'        => $slug,
				'name'        => $name,
				'iconUrl'     => $icon_url,
				'state'       => $state,
				'actionUrl'   => $action_url,
				'actionLabel' => $action_label,
				'detailsUrl'  => 'https://wordpress.org/plugins/' . $slug . '/',
			);
		}

		$data['recommendPlugins'] = $plugins;
		return $data;
	}

	/**
	 * Match an installed plugin file (e.g. `slug/slug.php`) by its slug.
	 *
	 * @param string[] $installed_files
	 * @param string   $slug
	 * @return string|null
	 */
	private function resolve_plugin_file($installed_files, $slug)
	{
		$prefix = $slug . '/';
		foreach ($installed_files as $file) {
			if (0 === strpos($file, $prefix)) {
				return $file;
			}
		}
		return null;
	}

	/**
	 * Surface the Customify Sites Library plugin state for the Welcome
	 * sidebar's "Customify ready to import sites" card. Three states:
	 *
	 *   - active:        plugin is loaded → CTA links straight to the library page.
	 *   - installed:     plugin files exist but inactive → CTA activates it (nonce-signed).
	 *   - not-installed: nothing on disk → CTA points at the GitHub releases page.
	 *
	 * @param array $data Bootstrap data passed through the filter chain.
	 * @return array
	 */
	public function theme_dashboard_inject_sites_plugin($data)
	{
		if (! function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_slug = 'customify-sites-library';
		$plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
		$installed   = is_dir(WP_PLUGIN_DIR . '/' . $plugin_slug);
		$active      = is_plugin_active($plugin_file);

		$thumb_path = self::$path . '/build/images/admin/sites_thumbnail.jpg';
		$thumb_url  = file_exists($thumb_path)
			? trailingslashit(get_template_directory_uri()) . 'build/images/admin/sites_thumbnail.jpg'
			: '';

		if ($active) {
			$state        = 'active';
			$action_url   = add_query_arg(array('page' => 'customify-sites'), admin_url('themes.php'));
			$action_label = __('View Site Library', 'customify');
		} elseif ($installed) {
			$state        = 'installed';
			$action_url   = wp_nonce_url(
				add_query_arg(
					array(
						'action'        => 'activate',
						'plugin'        => rawurlencode($plugin_file),
						'plugin_status' => 'all',
						'paged'         => '1',
					),
					network_admin_url('plugins.php')
				),
				'activate-plugin_' . $plugin_file
			);
			$action_label = __('Activate Plugin', 'customify');
		} else {
			$state        = 'not-installed';
			$action_url   = 'https://github.com/PressMaximum/customify-sites-library/releases/';
			$action_label = __('Download Plugin', 'customify');
		}

		$data['sitesPlugin'] = array(
			'state'        => $state,
			'actionUrl'    => $action_url,
			'actionLabel'  => $action_label,
			'detailsUrl'   => 'https://github.com/PressMaximum/customify-sites-library',
			'thumbnailUrl' => $thumb_url,
		);

		return $data;
	}

	/**
	 * Resolve the Customify_Pro singleton if the plugin is loaded.
	 *
	 * @return Customify_Pro|null
	 */
	private function get_pro_instance()
	{
		if (! function_exists('Customify_Pro')) {
			return null;
		}
		$pro = Customify_Pro();
		if (! $pro || ! is_object($pro) || empty($pro->modules) || ! is_array($pro->modules)) {
			return null;
		}
		return $pro;
	}

	/**
	 * Load configs
	 */
	public function load_configs()
	{

		$config_files = array(
			// Site Settings.
			'upsell',
			'layouts',
			'blogs',
			'single-blog-post',
			'related-posts',

			'search',
			'styling',
			'typography',
			'page-header',
			'background',
			'compatibility',
			// Header Builder Panel.
			'header/transparent',
			'header/panel',
			'header/html',
			'header/logo',
			'header/nav-icon',
			'header/primary-menu',
			'header/templates',
			'header/templates',
			'header/logo',
			'header/search-icon',
			'header/search-box',
			'header/menus',
			'header/nav-icon',
			'header/button',
			'header/social-icons',
			// Footer Builder Panel.
			'footer/panel',
			'footer/widgets',
			'footer/templates',
			'footer/widgets',
			'footer/copyright',
			'footer/social-icons',

		);

		$path = get_template_directory();
		// Load default config values.
		require_once $path . '/inc/customizer/configs/config-default.php';

		// Load site configs.
		foreach ($config_files as $f) {
			$file = $path . "/inc/customizer/configs/{$f}.php";
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}

	/**
	 * Load site compatibility supports
	 */
	public function load_compatibility()
	{

		$compatibility_config_files = array(
			'customify-pro',     // Disable Pro modules implemented natively by the theme.
			'elementor',         // Plugin breadcrumb-navxt & Yoat Seo.
			'breadcrumb',         // Plugin breadcrumb-navxt & Yoat Seo.
			'woocommerce/woocommerce',  // Plugin WooCommerce.
		);
		foreach ($compatibility_config_files as $f) {
			$file = self::$path . "/inc/compatibility/{$f}.php";
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}

	/**
	 * Check if WooCommerce plugin activated
	 *
	 * @see WooCommerce
	 * @see wc
	 *
	 * @return bool
	 */
	function is_woocommerce_active()
	{
		return class_exists('WooCommerce') || function_exists('wc');
	}

	function is_using_post()
	{
		$use = false;
		if (is_singular()) {
			$use = true;
		} else {
			if (is_front_page() && is_home()) {
				$use = false;
			} elseif (is_front_page()) {
				// static homepage.
				$use = true;
			} elseif (is_home()) {
				// blog page.
				$use = true;
			} else {
				if ($this->is_woocommerce_active()) {
					if (is_shop()) {
						$use = true;
					}
				}
			}
		}

		return $use;
	}

	function is_blog()
	{
		$is_blog = false;
		if (is_front_page() && is_home()) {
			$is_blog = true;
		} elseif (is_front_page()) { //phpcs:ignore
			// static homepage.
		} elseif (is_home()) {
			$is_blog = true;
		}

		return $is_blog;
	}

	function get_current_post_id()
	{
		$id = get_the_ID();
		if (is_front_page() && is_home()) {
			$id = false;
		} elseif (is_front_page()) {
			// Static homepage.
			$id = get_option('page_on_front');
		} elseif (is_home()) {
			// Blog page.
			$id = get_option('page_for_posts');
		} else {
			if ($this->is_woocommerce_active()) {
				if (is_shop()) {
					$id = wc_get_page_id('shop');
				}
			}
		}

		return $id;
	}

	function init()
	{
		$this->init_hooks();
		$this->includes();
		$this->customizer = Customify_Customizer::get_instance();
		$this->customizer->init();
		do_action('customify/init');
	}

	function get_setting($id, $device = 'desktop', $key = null)
	{
		return Customify_Customizer::get_instance()->get_setting($id, $device, $key);
	}

	function get_media($value, $size = null)
	{
		return Customify_Customizer::get_instance()->get_media($value, $size);
	}

	function get_setting_tab($name, $tab = null)
	{
		return Customify_Customizer::get_instance()->get_setting_tab($name, $tab);
	}

	function get_post_types($_builtin = true)
	{
		if ('all' === $_builtin) {
			$post_type_args = array(
				'publicly_queryable' => true,
			);
		} else {
			$post_type_args = array(
				'publicly_queryable' => true,
				'_builtin'           => $_builtin,
			);
		}

		$_post_types = get_post_types($post_type_args, 'objects');

		$post_types = array();

		foreach ($_post_types as $post_type => $object) {
			$post_types[$post_type] = array(
				'name'          => $object->label,
				'singular_name' => $object->labels->singular_name,
			);
		}

		return $post_types;
	}
}
