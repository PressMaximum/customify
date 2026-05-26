<?php
/**
 * Customify Pro <-> Dashboard V2 bridge (fallback for legacy Pro builds).
 *
 * Customify Pro >= 0.4.16 wires its own V2 hooks. This bridge fills the gap
 * for OLDER Pro builds (< 0.4.16) that only know the legacy
 * `appearance_page_customify` dashboard — without it, the V2 Welcome tab
 * keeps showing the marketing list even when Pro is active.
 *
 * The bridge bails entirely when Pro is missing OR Pro is >= 0.4.16, so
 * future Pro builds own the integration without conflict.
 *
 * Pro module Settings pages are HOSTED HERE (not redirected to the legacy
 * `customify-legacy` page) because:
 *   - The legacy menu registration is commented out
 *     (inc/admin/dashboard.php) — the URL 404s.
 *   - Even when re-registered, Pro's legacy module_settings_page() builds
 *     the form action from a hardcoded `admin.php?page=customify` URL,
 *     which now points at the SPA shell that won't process the POST.
 * Self-hosting reuses the public Pro module API (`settings()`, `save()`,
 * `get_settings()`) + theme's `Customify_Form_Fields` renderer, so we
 * don't touch the Pro plugin and we don't depend on Pro's URL plumbing.
 *
 * @package Customify
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bridge is a fallback only — bail BEFORE defining any constants,
// functions, or hooks so a newer Pro build sees an inert bridge file
// (zero symbols leaked into global scope, zero filters registered).
//
//   1. No Customify_Pro class → Pro plugin missing/disabled.
//   2. Pro version >= 0.4.16  → Pro ships its own V2 integration.
//
// Version detection runs in a one-shot closure so its symbols never
// outlive the guard; the resolved string is captured into a file-scope
// var and re-exposed via customify_pro_bridge_get_pro_version() below
// (defined only after the guard passes).
if ( ! class_exists( 'Customify_Pro' ) ) {
	return;
}

$customify_pro_bridge_version = ( static function (): string {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	try {
		$ref  = new ReflectionClass( 'Customify_Pro' );
		$file = $ref->getFileName();
		if ( $file && is_readable( $file ) ) {
			$data = get_plugin_data( $file, false, false );
			if ( ! empty( $data['Version'] ) ) {
				return (string) $data['Version'];
			}
		}
	} catch ( \Throwable $e ) {
		// Fall through to the static-property fallback below.
	}
	if ( isset( Customify_Pro::$version ) ) {
		return (string) Customify_Pro::$version;
	}
	return '';
} )();

if ( '' === $customify_pro_bridge_version ) {
	return;
}
if ( version_compare( $customify_pro_bridge_version, '0.4.16', '>=' ) ) {
	return;
}

// === Past the guard. Bridge is active for Pro < 0.4.16 only. ===

const CUSTOMIFY_PRO_BRIDGE_HANDLE        = 'customify-dashboard-pro-bridge';
const CUSTOMIFY_PRO_BRIDGE_SETTINGS_SLUG = 'customify-pro-module-settings';

// pro-bridge.php is `require_once`d from inside Customify::admin_includes(),
// so $customify_pro_bridge_version lives in *that method's* local scope —
// it disappears as soon as admin_includes() returns and isn't visible to
// the named functions defined below. Export to $GLOBALS so the getter can
// hand the resolved version to filter/action callbacks at request time.
$GLOBALS['customify_pro_bridge_pro_version'] = $customify_pro_bridge_version;

/**
 * Return the resolved Pro version captured during the guard above.
 * Used by boot-data injection + asset cache busts.
 *
 * @return string
 */
function customify_pro_bridge_get_pro_version(): string {
	return (string) ( $GLOBALS['customify_pro_bridge_pro_version'] ?? '' );
}

/**
 * Map a Pro module class name to the kebab id used by data/proModules.js.
 * Mirrors Customify_Pro_Module_Base::get_id() so bridge ids line up with
 * the ids modules use internally.
 *
 * @param string $class_name e.g. "Customify_Pro_Module_Header_Sticky".
 * @return string e.g. "header-sticky".
 */
function customify_pro_bridge_class_to_id( string $class_name ): string {
	$tail = str_replace( 'Customify_Pro_Module_', '', $class_name );
	return strtolower( str_replace( '_', '-', $tail ) );
}

/**
 * Build the URL of the bridge-hosted Settings page for a Pro module.
 *
 * @param string $class_name Pro module class.
 * @return string Admin URL.
 */
function customify_pro_bridge_settings_url( string $class_name ): string {
	return add_query_arg(
		array(
			'page'   => CUSTOMIFY_PRO_BRIDGE_SETTINGS_SLUG,
			'module' => rawurlencode( $class_name ),
		),
		admin_url( 'admin.php' )
	);
}

/**
 * Flip boot.proActive so the V2 shell switches to the Pro-active rendering
 * path (hides "Upgrade now", swaps the version label, etc.).
 */
add_filter( 'customify_dashboard_pro_active', '__return_true' );

/**
 * Inject Pro module catalogue + AJAX wiring into the dashboard boot payload.
 *
 * @param array  $boot    Boot data.
 * @param string $context Context (always 'dashboard' for this hook).
 * @return array
 */
function customify_pro_bridge_localize( array $boot, string $context ): array {
	if ( 'dashboard' !== $context ) {
		return $boot;
	}

	$pro = Customify_Pro();

	$boot['proVersion'] = customify_pro_bridge_get_pro_version();

	$boot['proAjax'] = array(
		'url'    => admin_url( 'admin-ajax.php' ),
		'action' => 'customify_pro_module',
		'nonce'  => wp_create_nonce( 'customify_pro_module' ),
	);

	$modules = array();
	foreach ( $pro->modules as $class_name => $args ) {
		$has_settings = method_exists( $class_name, 'settings' );
		$parent       = ! empty( $args['parent'] ) ? customify_pro_bridge_class_to_id( $args['parent'] ) : null;

		$sub_modules = array();
		if ( ! empty( $args['sub_modules'] ) && is_array( $args['sub_modules'] ) ) {
			foreach ( $args['sub_modules'] as $sub_class ) {
				if ( is_string( $sub_class ) ) {
					$sub_modules[] = customify_pro_bridge_class_to_id( $sub_class );
				}
			}
		}

		$row = array(
			'id'                  => customify_pro_bridge_class_to_id( $class_name ),
			'classKey'            => $class_name,
			'name'                => isset( $args['name'] ) ? (string) $args['name'] : '',
			'description'         => isset( $args['desc'] ) ? (string) $args['desc'] : '',
			'enabled'             => (bool) $pro->is_enabled_module( $class_name ),
			'canToggle'           => isset( $args['can_toggle'] ) ? (bool) $args['can_toggle'] : true,
			'toggleDisableNotice' => isset( $args['toggle_disable_notice'] ) ? (string) $args['toggle_disable_notice'] : '',
			'hasSettings'         => (bool) $has_settings,
			'docHref'             => isset( $args['doc_link'] ) ? (string) $args['doc_link'] : '',
			'parent'              => $parent,
			'subModules'          => $sub_modules,
			'reload'              => ! empty( $args['reload'] ),
		);

		if ( $has_settings ) {
			$row['settingsHref'] = customify_pro_bridge_settings_url( $class_name );
		}

		$modules[] = $row;
	}

	$boot['proModules'] = $modules;

	return $boot;
}
add_filter( 'customify_dashboard_localize', 'customify_pro_bridge_localize', 10, 2 );

/**
 * Enqueue the bridge JS on the V2 dashboard page only. Depends on the
 * dashboard handle so `window.customifyDashboard` is already localized
 * when the bridge runs.
 *
 * @param string $hook Current admin page hook.
 */
function customify_pro_bridge_enqueue( string $hook ): void {
	if ( 'toplevel_page_' . CUSTOMIFY_DASHBOARD_V2_SLUG !== $hook ) {
		return;
	}

	$rel  = 'inc/admin/pro-bridge/pro-bridge.js';
	$path = get_theme_file_path( $rel );
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		CUSTOMIFY_PRO_BRIDGE_HANDLE,
		get_theme_file_uri( $rel ),
		array( 'wp-hooks', CUSTOMIFY_DASHBOARD_V2_HANDLE ),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'customify_pro_bridge_enqueue', 20 );

/**
 * Register the bridge-hosted Settings page as a child of the Customify
 * top-level menu.
 *
 * We register under the `customify` parent (the V2 dashboard's top-level
 * slug) so:
 *   - WP's `get_admin_page_title()` can resolve the title from `$submenu`
 *     (avoids the `strip_tags( null )` deprecation in admin-header.php
 *     that orphan-parent pages trigger on PHP 8.1+).
 *   - The admin sidebar reads as "we're inside the Customify section"
 *     while editing a module (`parent_file` filter pins the highlight).
 *
 * The auto-added submenu row is removed immediately (priority 999) so
 * the page doesn't appear as a permanent sidebar link — users only
 * reach it via the "Settings" affordance on the Welcome tab.
 */
function customify_pro_bridge_register_settings_page(): void {
	if ( ! defined( 'CUSTOMIFY_DASHBOARD_V2_SLUG' ) ) {
		// dashboard-v2.php is loaded before this file, but bail defensively
		// so a partial theme load doesn't fatal.
		return;
	}

	$page_title = __( 'Customify Pro module settings', 'customify' );

	$hook = add_submenu_page(
		CUSTOMIFY_DASHBOARD_V2_SLUG,
		$page_title,
		$page_title,
		'manage_options',
		CUSTOMIFY_PRO_BRIDGE_SETTINGS_SLUG,
		'customify_pro_bridge_render_settings_page'
	);

	if ( ! $hook ) {
		return;
	}

	// Belt-and-suspenders for older WP versions where title resolution
	// can still leave $title empty for hidden submenu rows.
	add_action(
		'load-' . $hook,
		static function () use ( $page_title ): void {
			global $title;
			if ( empty( $title ) ) {
				$title = $page_title;
			}
		}
	);
}
add_action( 'admin_menu', 'customify_pro_bridge_register_settings_page' );

/**
 * Hide the bridge settings row from the visible Customify submenu via
 * CSS — but leave it in `$submenu`.
 *
 * Why CSS, not `remove_submenu_page()`: WP's admin.php resolves the page
 * hook from `$submenu` (`get_plugin_page_hookname()` walks the menu tree
 * to find a parent). Removing our row causes the resolution to fall
 * back to `admin_page_<slug>`, but our callback was registered against
 * `customify_page_<slug>` — `has_action()` misses it and the URL
 * 403s with "Sorry, you are not allowed to access this page."
 *
 * Keeping the row registered also means WP derives `$parent_file` =
 * 'customify' on its own, so the Customify top-level menu highlights +
 * stays expanded without an extra `parent_file`/`submenu_file` filter.
 *
 * The CSS target is the submenu anchor's href so we don't depend on the
 * row's position within `$submenu`.
 */
function customify_pro_bridge_hide_settings_submenu_css(): void {
	if ( ! defined( 'CUSTOMIFY_DASHBOARD_V2_SLUG' ) ) {
		return;
	}
	?>
<style>
#adminmenu #toplevel_page_<?php echo esc_attr( CUSTOMIFY_DASHBOARD_V2_SLUG ); ?> .wp-submenu a[href*="page=<?php echo esc_attr( CUSTOMIFY_PRO_BRIDGE_SETTINGS_SLUG ); ?>"] {
	display: none !important;
}
</style>
	<?php
}
add_action( 'admin_head', 'customify_pro_bridge_hide_settings_submenu_css' );

/**
 * Enqueue the metabox-field assets that Customify_Form_Fields needs to
 * render — these live in the theme already and Pro's legacy dashboard
 * does the same thing on its own settings screen.
 *
 * @param string $hook Current admin page hook.
 */
function customify_pro_bridge_enqueue_settings_assets( string $hook ): void {
	// Parent slug 'customify' is registered via add_menu_page in
	// dashboard-v2.php, so get_plugin_page_hookname() derives the hook as
	// `<parent_slug>_page_<this_slug>`.
	if ( ! defined( 'CUSTOMIFY_DASHBOARD_V2_SLUG' ) ) {
		return;
	}
	if ( CUSTOMIFY_DASHBOARD_V2_SLUG . '_page_' . CUSTOMIFY_PRO_BRIDGE_SETTINGS_SLUG !== $hook ) {
		return;
	}

	if ( ! class_exists( 'Customify' ) || ! class_exists( 'Customify_Pro' ) ) {
		// Bridge already guards on Customify_Pro at file load, but admin
		// hooks fire later — re-verify before touching either class.
		return;
	}

	$theme_version = Customify::$version ? (string) Customify::$version : (string) wp_get_theme()->get( 'Version' );
	$pro_version   = customify_pro_bridge_get_pro_version();
	$pro_url       = Customify_Pro::$url;

	wp_enqueue_media();
	wp_enqueue_script(
		'customify-metabox',
		esc_url( get_template_directory_uri() ) . '/build/js/backend/admin/metabox.js',
		array( 'jquery' ),
		$theme_version,
		true
	);
	wp_enqueue_style(
		'customify-metabox',
		esc_url( get_template_directory_uri() ) . '/build/css/backend/admin/metabox.css',
		array(),
		$theme_version
	);

	// Some Pro modules' settings fields hook into the Customify Pro admin
	// JS/CSS for their own widgets — load those too so the form behaves
	// the same as on Pro's own settings screen.
	if ( $pro_url ) {
		wp_enqueue_script( 'customify-pro-admin', $pro_url . '/assets/js/admin/customify-admin.js', array( 'jquery' ), $pro_version, true );
		wp_enqueue_style( 'customify-pro-admin', $pro_url . '/assets/css/admin/admin.css', array(), $pro_version );
	}

	// Bridge-owned hero/card styling so the legacy form visually matches
	// the V2 dashboard the user arrived from.
	$css_rel  = 'inc/admin/pro-bridge/pro-bridge.css';
	$css_path = get_theme_file_path( $css_rel );
	if ( is_readable( $css_path ) ) {
		wp_enqueue_style(
			CUSTOMIFY_PRO_BRIDGE_HANDLE,
			get_theme_file_uri( $css_rel ),
			array(),
			(string) filemtime( $css_path )
		);
	}
}
add_action( 'admin_enqueue_scripts', 'customify_pro_bridge_enqueue_settings_assets', 20 );

/**
 * Render the bridge-hosted Settings page for a single Pro module. Reuses
 * the public Pro module API (`settings()`, `save()`, `get_settings()`)
 * and the theme's `Customify_Form_Fields` renderer — same building blocks
 * Pro's own class-dashboard.php uses. The form posts back to the same URL.
 */
function customify_pro_bridge_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'customify' ) );
	}

	$class_name = isset( $_GET['module'] ) ? sanitize_text_field( wp_unslash( $_GET['module'] ) ) : '';
	$pro        = Customify_Pro();

	// Module must be registered, enabled, AND expose a settings() method.
	// Matches Pro's own setup() guard in class-dashboard.php so users land
	// on a coherent error rather than a half-rendered form.
	if (
		'' === $class_name
		|| ! isset( $pro->modules[ $class_name ] )
		|| ! $pro->is_enabled_module( $class_name )
		|| ! isset( $pro->installed_modules[ $class_name ] )
		|| ! method_exists( $class_name, 'settings' )
	) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Module settings unavailable', 'customify' ) . '</h1>';
		echo '<p>' . esc_html__( 'This module is not registered, not enabled, or does not expose any settings.', 'customify' ) . '</p>';
		printf(
			'<p><a class="button" href="%1$s">%2$s</a></p>',
			esc_url( admin_url( 'admin.php?page=' . CUSTOMIFY_DASHBOARD_V2_SLUG ) ),
			esc_html__( 'Back to dashboard', 'customify' )
		);
		echo '</div>';
		return;
	}

	$module = $pro->installed_modules[ $class_name ];
	$info   = $pro->modules[ $class_name ];

	$fields_class_path = get_template_directory() . '/inc/class-metabox-fields.php';
	if ( ! file_exists( $fields_class_path ) ) {
		wp_die( esc_html__( 'Form fields class missing.', 'customify' ) );
	}
	require_once $fields_class_path;

	$field_builder = new Customify_Form_Fields();
	$field_builder->set_group_name( 'customify_module_settings' );
	$field_builder->add_fields( $module->settings() );
	$field_builder->using_tabs( false );

	$action_url = customify_pro_bridge_settings_url( $class_name );
	$saved      = false;

	if ( isset( $_POST['customify_pro_bridge_form_submit'] ) ) {
		check_admin_referer( 'customify_pro_bridge_save_' . $class_name );
		$values = $field_builder->get_submitted_values();
		$module->save( $values );
		$saved = true;

		if ( method_exists( $module, 'after_save' ) ) {
			// Pro's class-dashboard passes the dashboard instance here; the
			// modules that consume it (if any) treat it as opaque, so null
			// is safe.
			$module->after_save( null );
		}
		do_action( 'customify-pro/after-module-saved', null );

		// Re-build fields against the new saved state so default-derived
		// fields refresh (matches Pro's flow).
		$fresh = $module->settings();
		$field_builder->reset_fields();
		$field_builder->add_fields( $fresh );
	}

	$field_builder->set_values( $module->get_settings() );

	$back_url    = admin_url( 'admin.php?page=' . CUSTOMIFY_DASHBOARD_V2_SLUG );
	$module_name = isset( $info['name'] ) ? (string) $info['name'] : $class_name;
	?>
	<div class="wrap customify-pro-bridge-settings">
		<h1>
			<?php
			printf(
				/* translators: %s: Pro module name. */
				esc_html__( '%s Settings', 'customify' ),
				esc_html( $module_name )
			);
			?>
		</h1>
		<p>
			<a class="button button-secondary" href="<?php echo esc_url( $back_url ); ?>">
				<?php esc_html_e( '← Back to dashboard', 'customify' ); ?>
			</a>
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'customify' ); ?></p>
			</div>
		<?php endif; ?>

		<?php
		if ( method_exists( $module, 'before_form' ) ) {
			$module->before_form();
		}
		?>

		<form method="post" action="<?php echo esc_url( $action_url ); ?>">
			<?php wp_nonce_field( 'customify_pro_bridge_save_' . $class_name ); ?>
			<?php $field_builder->render(); ?>
			<input type="hidden" name="customify_pro_bridge_form_submit" value="1">
			<?php submit_button(); ?>
		</form>

		<?php
		if ( method_exists( $module, 'after_form' ) ) {
			$module->after_form();
		}
		?>
	</div>
	<?php
}
