<?php
/**
 * Customify Dashboard v2
 *
 * Top-level admin page (`customify`) powered by
 * `@pressmaximum/dashboard-kit`. Old PHP dashboard lives at
 * `customify-legacy` under Appearance — see inc/admin/dashboard.php.
 *
 * @package Customify
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\\PressMaximum\\DashboardKit\\Admin\\AssetEnqueue' ) ) {
	// Composer autoload missing — nothing to do. functions.php skips the
	// require when vendor/ is absent, so this branch is the developer-
	// without-composer-install case. Bail out silently; the menu just
	// doesn't appear.
	return;
}

use PressMaximum\DashboardKit\Admin\AssetEnqueue;

/**
 * Page slug for the new top-level dashboard.
 */
const CUSTOMIFY_DASHBOARD_V2_SLUG = 'customify';

/**
 * Bundle handle (also script + style handle).
 */
const CUSTOMIFY_DASHBOARD_V2_HANDLE = 'customify-dashboard';

/**
 * Register the top-level menu.
 */
function customify_dashboard_v2_add_menu(): void {
	add_menu_page(
		__( 'Customify', 'customify' ),
		__( 'Customify', 'customify' ),
		'manage_options',
		CUSTOMIFY_DASHBOARD_V2_SLUG,
		'customify_dashboard_v2_render',
		'dashicons-admin-customizer',
		59
	);
}
add_action( 'admin_menu', 'customify_dashboard_v2_add_menu' );

/**
 * Print the SPA root div. JS takes over from there.
 */
function customify_dashboard_v2_render(): void {
	echo '<div id="customify-dashboard" class="customify-dashboard-root"></div>';
}

/**
 * Build the boot data payload localized to window.customifyDashboard.
 *
 * @return array<string, mixed>
 */
function customify_dashboard_v2_boot_data(): array {
	$theme = wp_get_theme();
	if ( $theme->parent() ) {
		$theme = $theme->parent();
	}
	$user      = wp_get_current_user();
	$customize = admin_url( 'customize.php' );

	$boot = array(
		'name'         => 'Customify',
		'themeVersion' => (string) $theme->get( 'Version' ),
		'wpVersion'    => get_bloginfo( 'version' ),
		'user'         => array(
			'id'          => (int) $user->ID,
			'displayName' => (string) $user->display_name,
		),
		'urls'         => array(
			'customize'      => $customize,
			'logoIdentity'   => add_query_arg(
				array( 'autofocus' => array( 'section' => 'title_tagline' ) ),
				$customize
			),
			'layout'         => add_query_arg(
				array( 'autofocus' => array( 'section' => 'global_layout_section' ) ),
				$customize
			),
			'headerBuilder'  => add_query_arg(
				array( 'autofocus' => array( 'panel' => 'header_settings' ) ),
				$customize
			),
			'footerBuilder'  => add_query_arg(
				array( 'autofocus' => array( 'panel' => 'footer_settings' ) ),
				$customize
			),
			'styling'        => add_query_arg(
				array( 'autofocus' => array( 'panel' => 'styling_panel' ) ),
				$customize
			),
			'typography'     => add_query_arg(
				array( 'autofocus' => array( 'panel' => 'typography_panel' ) ),
				$customize
			),
			'sidebar'        => add_query_arg(
				array( 'autofocus' => array( 'section' => 'sidebar_layout_section' ) ),
				$customize
			),
			'blog'           => add_query_arg(
				array( 'autofocus' => array( 'panel' => 'blog_panel' ) ),
				$customize
			),
			'homepage'       => add_query_arg(
				array( 'autofocus' => array( 'section' => 'static_front_page' ) ),
				$customize
			),
			'legacyDashboard' => admin_url( 'themes.php?page=customify-legacy' ),
			'docs'           => 'https://pressmaximum.com/docs/customify/',
			'proUpgrade'     => 'https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=pro_modules',
		),
		'rest'         => array(
			'root'        => esc_url_raw( rest_url() ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'settingsEndpoint' => esc_url_raw( rest_url( 'customify/v1/settings' ) ),
			'schemaEndpoint'   => esc_url_raw( rest_url( 'customify/v1/settings/schema' ) ),
		),
		'settings'     => array(
			'faVersion' => (string) get_option( 'customify_fa_ver', 'v4' ),
			'values'    => function_exists( 'customify_dashboard_v2_get_settings' )
				? customify_dashboard_v2_get_settings()
				: array(),
			'schema'    => function_exists( 'customify_dashboard_v2_schema' )
				? customify_dashboard_v2_schema()->buildSchema()
				: array( 'panels' => array() ),
		),
		'changelog'    => customify_dashboard_v2_changelog( (string) $theme->get( 'Version' ) ),
	);

	/**
	 * Filter the dashboard boot payload before it ships to JS.
	 *
	 * @param array  $boot    Boot data.
	 * @param string $context Context (currently always 'dashboard').
	 */
	return (array) apply_filters( 'customify_dashboard_localize', $boot, 'dashboard' );
}

/**
 * Parse the changelog.txt file into a list of releases consumable by
 * the kit's `ReleaseBlock`.
 *
 * @param string $current_version Current theme version (marks the
 *                                matching release with the Current pill).
 * @return array<int, array<string, mixed>>
 */
function customify_dashboard_v2_changelog( string $current_version ): array {
	$file = get_template_directory() . '/changelog.txt';
	if ( ! file_exists( $file ) ) {
		return array();
	}
	$contents = file_get_contents( $file );
	if ( ! $contents ) {
		return array();
	}

	$releases = array();
	$current  = null;
	$category_map = array(
		'new'      => 'new',
		'added'    => 'new',
		'improved' => 'improved',
		'improve'  => 'improved',
		'fixed'    => 'fixed',
		'fix'      => 'fixed',
		'updated'  => 'updated',
		'update'   => 'updated',
		'removed'  => 'removed',
		'remove'   => 'removed',
		'security' => 'security',
	);

	foreach ( preg_split( '/\r?\n/', $contents ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( preg_match( '/^=\s*([0-9][0-9A-Za-z\.\-]*)\s*=$/', $line, $m ) ) {
			if ( $current ) {
				$releases[] = $current;
			}
			$version = $m[1];
			$current = array(
				'version' => $version,
				'current' => version_compare( $version, $current_version, '==' ),
				'items'   => array(),
			);
			continue;
		}
		if ( ! $current ) {
			continue;
		}
		if ( preg_match( '/^\*\s*([A-Za-z]+)\s*:\s*(.+)$/', $line, $m ) ) {
			$tag = strtolower( $m[1] );
			$tone = $category_map[ $tag ] ?? 'neutral';
			$current['items'][] = array(
				'category' => $tone,
				'text'     => $m[2],
			);
		} elseif ( preg_match( '/^\*\s*(.+)$/', $line, $m ) ) {
			$current['items'][] = array(
				'category' => 'neutral',
				'text'     => $m[1],
			);
		}
	}
	if ( $current ) {
		$releases[] = $current;
	}

	return $releases;
}

/**
 * Enqueue the SPA bundle on the dashboard admin page.
 *
 * @param string $hook Current admin page hook.
 */
function customify_dashboard_v2_enqueue( string $hook ): void {
	if ( 'toplevel_page_' . CUSTOMIFY_DASHBOARD_V2_SLUG !== $hook ) {
		return;
	}
	AssetEnqueue::enqueueOn(
		$hook,
		array(
			'handle'      => CUSTOMIFY_DASHBOARD_V2_HANDLE,
			'src_js'      => get_template_directory_uri() . '/build/js/backend/admin/dashboard-v2.js',
			'src_css'     => get_template_directory_uri() . '/build/css/backend/admin/dashboard-v2.css',
			'asset_php'   => get_template_directory() . '/build/js/backend/admin/dashboard-v2.asset.php',
			'boot_global' => 'customifyDashboard',
			'boot_data'   => 'customify_dashboard_v2_boot_data',
			'text_domain' => 'customify',
		)
	);

	// wp-scripts splits any `import 'pkg/style.css'` into a `style-`
	// prefixed sibling output (its block-style convention). The kit ships
	// its tokens + component CSS through this path, so we enqueue the
	// sibling chunk alongside the main bundle so kit components render
	// styled.
	$kit_css_path = get_template_directory() . '/build/css/backend/admin/style-dashboard-v2.css';
	if ( file_exists( $kit_css_path ) ) {
		$theme_version = wp_get_theme()->get( 'Version' );
		wp_enqueue_style(
			CUSTOMIFY_DASHBOARD_V2_HANDLE . '-kit',
			get_template_directory_uri() . '/build/css/backend/admin/style-dashboard-v2.css',
			array(),
			$theme_version . '-' . filemtime( $kit_css_path )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'customify_dashboard_v2_enqueue' );
