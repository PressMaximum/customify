<?php
/**
 * Portable Dashboard bootstrap — single static class that registers an
 * admin page + admin-ajax dispatcher for a React app. Config-driven so
 * the same file can be dropped into any plugin/theme.
 *
 * Adapted from Blocksify_Dashboard. The asset path/url config is split into
 * separate keys (js_rel, css_rel, asset_rel) because the Customify webpack
 * pipeline emits to nested directories (build/js/<name>/index.js +
 * build/css/<name>/index.css) instead of a flat build/ folder.
 *
 * Usage:
 *
 *     Customify_Theme_Dashboard::init( array(
 *         'slug'    => 'customify',
 *         'name'    => __( 'Customify', 'customify' ),
 *         'version' => Customify::$version,
 *         'path'    => get_template_directory() . '/',
 *         'url'     => get_template_directory_uri() . '/',
 *     ) );
 *
 * Required config:
 *   slug, name, version, path, url
 *
 * Optional config (defaults derived from `slug`):
 *   capability      'manage_options'
 *   text_domain     {slug}
 *   menu_parent     null              null = top-level menu; pass a parent
 *                                     file slug like 'themes.php' to nest as
 *                                     a submenu instead.
 *   menu_title      same as `name`    label shown in the admin menu item.
 *   menu_position   58                top-level only
 *   menu_icon       null              data: URL or dashicon name (top-level only)
 *   asset_handle    {slug}-dashboard
 *   js_var          camelCase({slug}) + 'Dashboard'   window object name
 *   mount_id        {slug}-dashboard  #id of the React mount node
 *   option_key      {slug_}_dashboard_settings
 *   ajax_action     {slug}_dashboard
 *   ajax_nonce      {slug}_dashboard
 *   body_class      {slug}-dashboard-page
 *   js_rel          build/js/backend/dashboard/index.js
 *   css_rel         build/css/backend/dashboard/index.css
 *   asset_rel       build/js/backend/dashboard/index.asset.php
 *   changelog_file  changelog.txt
 *   defaults        callable returning settings shape
 *   sanitize        callable( array $incoming ) for save validation
 *
 * @package Customify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customify_Theme_Dashboard {

	/** @var array<string,mixed> */
	private static $cfg = array();

	/** @var string|null Hook suffix returned by add_menu_page / add_submenu_page. */
	private static $hook = null;

	/**
	 * One-shot bootstrap. Call once with the host project's config; all
	 * hooks register here. Subsequent calls are no-ops.
	 */
	public static function init( array $config ) {
		if ( ! empty( self::$cfg ) ) {
			return;
		}

		foreach ( array( 'slug', 'name', 'version', 'path', 'url' ) as $required ) {
			if ( empty( $config[ $required ] ) ) {
				return;
			}
		}

		$slug   = $config['slug'];
		$slug_u = str_replace( '-', '_', $slug );

		self::$cfg = array_merge(
			array(
				'capability'     => 'manage_options',
				'text_domain'    => $slug,
				'menu_parent'    => null,
				'menu_title'     => isset( $config['name'] ) ? $config['name'] : '',
				'menu_position'  => 58,
				'menu_icon'      => null,
				'asset_handle'   => $slug . '-dashboard',
				'js_var'         => self::camel( $slug ) . 'Dashboard',
				'mount_id'       => $slug . '-dashboard',
				'option_key'     => $slug_u . '_dashboard_settings',
				'ajax_action'    => $slug . '_dashboard',
				'ajax_nonce'     => $slug . '_dashboard',
				'body_class'     => $slug . '-dashboard-page',
				'js_rel'         => 'build/js/backend/dashboard/index.js',
				'css_rel'        => 'build/css/backend/dashboard/index.css',
				'asset_rel'      => 'build/js/backend/dashboard/index.asset.php',
				'changelog_file' => 'changelog.txt',
				'defaults'       => array( __CLASS__, 'defaults_default' ),
				'sanitize'       => array( __CLASS__, 'sanitize_default' ),
			),
			$config
		);

		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_ajax_' . self::$cfg['ajax_action'], array( __CLASS__, 'ajax_dispatch' ) );
	}

	/**
	 * Default settings shape. Override via init( [ 'defaults' => $callable ] ).
	 * Mirrored client-side in app/data/settings-schema.js — keep in sync.
	 */
	public static function defaults_default() {
		return array(
			'icons' => array(
				'fa_version' => 'v4',
			),
		);
	}

	/* ---------------------------------------------------------------- */
	/* Admin page                                                       */
	/* ---------------------------------------------------------------- */

	public static function register_menu() {
		$cfg        = self::$cfg;
		$menu_title = ! empty( $cfg['menu_title'] ) ? $cfg['menu_title'] : $cfg['name'];

		if ( ! empty( $cfg['menu_parent'] ) ) {
			self::$hook = add_submenu_page(
				$cfg['menu_parent'],
				$cfg['name'],
				$menu_title,
				$cfg['capability'],
				$cfg['slug'],
				array( __CLASS__, 'render_page' ),
				5
			);
			return;
		}

		self::$hook = add_menu_page(
			$cfg['name'],
			$menu_title,
			$cfg['capability'],
			$cfg['slug'],
			array( __CLASS__, 'render_page' ),
			$cfg['menu_icon'],
			$cfg['menu_position']
		);
	}

	public static function render_page() {
		printf(
			'<div id="%s" class="pm-app"></div>',
			esc_attr( self::$cfg['mount_id'] )
		);
	}

	public static function body_class( $classes ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && self::page_hook() === $screen->id ) {
			$classes .= ' ' . self::$cfg['body_class'];
		}
		return $classes;
	}

	public static function enqueue_assets( $hook ) {
		if ( self::page_hook() !== $hook ) {
			return;
		}
		$cfg        = self::$cfg;
		$asset_path = $cfg['path'] . $cfg['asset_rel'];
		if ( ! file_exists( $asset_path ) ) {
			return;
		}
		$asset = require $asset_path;

		// Defensive dep injection: some installs (Elementor + WP 6.9.x in
		// particular) leave window.wp.hooks unset by the time wp-i18n.min.js
		// runs, which makes i18n abort BEFORE it can attach window.wp.i18n.
		// That cascades into "Cannot read properties of undefined (reading
		// 'sprintf')" inside our React bundle and the dashboard renders
		// blank. Force `wp-hooks` to the dep list so WP enqueues it ahead
		// of i18n regardless of what other plugins did to the registry.
		$dependencies = isset( $asset['dependencies'] ) && is_array( $asset['dependencies'] )
			? $asset['dependencies']
			: array();
		foreach ( array( 'wp-hooks', 'wp-i18n', 'wp-element', 'wp-data' ) as $required_handle ) {
			if ( ! in_array( $required_handle, $dependencies, true ) ) {
				$dependencies[] = $required_handle;
			}
		}

		wp_enqueue_script(
			$cfg['asset_handle'],
			$cfg['url'] . $cfg['js_rel'],
			$dependencies,
			$asset['version'],
			true
		);
		wp_set_script_translations( $cfg['asset_handle'], $cfg['text_domain'] );

		// Enqueue WP's plugin install machinery so the React dashboard can
		// drive `wp.updates.installPlugin()` for the Recommend Plugins card —
		// no full-page redirect needed. WP's localized `_wpUpdatesSettings`
		// blob is the single source of truth for the install nonce + state
		// strings; we reuse it instead of duplicating server-side.
		if ( current_user_can( 'install_plugins' ) ) {
			wp_enqueue_script( 'plugin-install' );
			wp_enqueue_script( 'updates' );
			if ( function_exists( 'wp_print_admin_notice_templates' ) ) {
				add_action( 'admin_print_footer_scripts', 'wp_print_admin_notice_templates' );
			}
		}

		/**
		 * Filter the bootstrap data exposed to the dashboard React app.
		 *
		 * Use to append URLs, feature flags, or current-user context. Keys
		 * become properties of `window.{js_var}`. Changelog is NOT shipped
		 * here — the Changelog tab fetches on demand via ajax.
		 *
		 * @param array $data Default bootstrap data.
		 * @param array $cfg  Resolved dashboard config.
		 */
		$data = apply_filters(
			'customify/dashboard/bootstrap_data',
			array(
				'name'          => $cfg['name'],
				'pluginVersion' => $cfg['version'],
				'wpVersion'     => get_bloginfo( 'version' ),
				'mountId'       => $cfg['mount_id'],
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'action'        => $cfg['ajax_action'],
				'nonce'         => wp_create_nonce( $cfg['ajax_nonce'] ),
				'adminUrl'      => admin_url(),
				'customizerUrl' => admin_url( 'customize.php' ),
				// Base URL of the host theme/plugin so the React app can
				// reference images and other static assets shipped with it.
				'baseUrl'       => $cfg['url'],
			),
			$cfg
		);

		wp_localize_script( $cfg['asset_handle'], $cfg['js_var'], $data );

		$style_path = $cfg['path'] . $cfg['css_rel'];
		if ( file_exists( $style_path ) ) {
			wp_enqueue_style(
				$cfg['asset_handle'],
				$cfg['url'] . $cfg['css_rel'],
				array( 'wp-components' ),
				(string) filemtime( $style_path )
			);
		}
	}

	/* ---------------------------------------------------------------- */
	/* admin-ajax — single action, task-routed                          */
	/* ---------------------------------------------------------------- */

	public static function ajax_dispatch() {
		self::ajax_check();

		$task = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';

		switch ( $task ) {
			case 'get_settings':
				wp_send_json_success( self::get_settings() );
				break;

			case 'save_settings':
				$raw      = isset( $_POST['payload'] ) ? wp_unslash( $_POST['payload'] ) : '';
				$incoming = is_string( $raw ) ? json_decode( $raw, true ) : array();
				if ( ! is_array( $incoming ) ) {
					$incoming = array();
				}
				$sanitized = call_user_func( self::$cfg['sanitize'], $incoming );
				update_option( self::$cfg['option_key'], $sanitized );
				/**
				 * Fires after settings are persisted. Use to mirror values into
				 * legacy options or invalidate caches.
				 *
				 * @param array $sanitized The sanitized settings just saved.
				 */
				do_action( 'customify/dashboard/saved', $sanitized );
				wp_send_json_success( $sanitized );
				break;

			case 'get_changelog':
				wp_send_json_success( self::get_changelog_raw() );
				break;

			default:
				/**
				 * Allow custom AJAX tasks. Handlers must call wp_send_json_*
				 * themselves (which exits the request).
				 *
				 * @param string $task Sanitized task name.
				 */
				do_action( 'customify/dashboard/ajax_task', $task );
				wp_send_json_error( 'unknown_task', 400 );
		}
	}

	private static function ajax_check() {
		check_ajax_referer( self::$cfg['ajax_nonce'], 'nonce' );
		if ( ! current_user_can( self::$cfg['capability'] ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}
	}

	/* ---------------------------------------------------------------- */
	/* Settings                                                         */
	/* ---------------------------------------------------------------- */

	public static function get_settings() {
		$saved    = get_option( self::$cfg['option_key'], array() );
		$defaults = call_user_func( self::$cfg['defaults'] );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		// Two-level merge so missing groups fall back to defaults.
		foreach ( $defaults as $group => $fields ) {
			if ( ! isset( $saved[ $group ] ) || ! is_array( $saved[ $group ] ) ) {
				$saved[ $group ] = $fields;
				continue;
			}
			$saved[ $group ] = array_merge( $fields, $saved[ $group ] );
		}
		return $saved;
	}

	/**
	 * Default sanitizer — coerce booleans, whitelist enums, drop unknown
	 * keys. Override via init( [ 'sanitize' => $callable ] ) for project-
	 * specific schemas.
	 */
	public static function sanitize_default( $incoming ) {
		$defaults = call_user_func( self::$cfg['defaults'] );
		$out      = array();

		// Icons group.
		$icons_in       = isset( $incoming['icons'] ) && is_array( $incoming['icons'] )
			? $incoming['icons']
			: array();
		$out['icons']   = array(
			'fa_version' => in_array(
				isset( $icons_in['fa_version'] ) ? $icons_in['fa_version'] : '',
				array( 'v4', 'v6', 'v456' ),
				true
			)
				? $icons_in['fa_version']
				: $defaults['icons']['fa_version'],
		);

		return $out;
	}

	/* ---------------------------------------------------------------- */
	/* Changelog                                                        */
	/* ---------------------------------------------------------------- */

	/**
	 * Read changelog file via WP_Filesystem (avoids direct file_get_contents).
	 * The JS client owns parsing — see app/tabs/Changelog.js parseChangelog().
	 */
	public static function get_changelog_raw() {
		$path = self::$cfg['path'] . self::$cfg['changelog_file'];

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! WP_Filesystem() ) {
			return '';
		}
		global $wp_filesystem;
		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $path ) ) {
			return '';
		}

		$content = $wp_filesystem->get_contents( $path );
		return ( false === $content ) ? '' : $content;
	}

	/* ---------------------------------------------------------------- */
	/* Helpers                                                          */
	/* ---------------------------------------------------------------- */

	public static function config( $key ) {
		return isset( self::$cfg[ $key ] ) ? self::$cfg[ $key ] : null;
	}

	private static function page_hook() {
		// Once add_menu_page / add_submenu_page has run, WP returns the
		// authoritative hook suffix for this page. Falls back to the
		// top-level shape so calls before menu registration still resolve.
		if ( self::$hook ) {
			return self::$hook;
		}
		return 'toplevel_page_' . self::$cfg['slug'];
	}

	private static function camel( $kebab ) {
		$parts = explode( '-', $kebab );
		$out   = array_shift( $parts );
		foreach ( $parts as $part ) {
			$out .= ucfirst( $part );
		}
		return $out;
	}
}
