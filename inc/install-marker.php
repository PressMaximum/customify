<?php
/**
 * Install-version marker.
 *
 * Answers one question: **did this site start out on this version of the theme,
 * or has it been running since an earlier one?**
 *
 * Why that matters: the theme ships on 30,000+ live sites, and AGENTS.md §4.1
 * says a default must never change silently underneath them. But a default
 * that can never improve is a default frozen in 2019. The marker splits the
 * difference — a site that installed the theme at or after version X gets the
 * modern default, every site that predates X keeps exactly what it rendered
 * yesterday, and nobody has to re-save anything.
 *
 * The stamp is written ONCE, the first time anything asks for it:
 *
 *   • site already has Customify `theme_mod`s  → stamped `0.0.0` (legacy).
 *     Someone has configured this site, so it existed before the marker did.
 *   • no Customify `theme_mod`s at all         → stamped the running version.
 *     Nothing has ever been configured, so this is (indistinguishably) a
 *     fresh install.
 *
 * The ambiguity is deliberate and fails SAFE in the direction that matters:
 * the only site that can be misread is one that has never been customised at
 * all, where "what it rendered yesterday" was itself only ever a default.
 *
 * @package Customify
 * @since   0.4.25
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option holding the version this site first installed the theme at.
 *
 * Public API — Customify Pro reads it to keep its own builder-item defaults in
 * step with the theme's. Never rename ([`AGENTS.md`](../AGENTS.md) §4.1).
 */
if ( ! defined( 'CUSTOMIFY_INSTALLED_VERSION_OPTION' ) ) {
	define( 'CUSTOMIFY_INSTALLED_VERSION_OPTION', 'customify_installed_version' );
}

/**
 * Version stamped on sites that predate the marker.
 *
 * Lower than any real release, so `customify_is_fresh_install_since()` is
 * false for every comparison — legacy sites never pick up a gated default.
 */
if ( ! defined( 'CUSTOMIFY_LEGACY_INSTALL_VERSION' ) ) {
	define( 'CUSTOMIFY_LEGACY_INSTALL_VERSION', '0.0.0' );
}

if ( ! function_exists( 'customify_get_theme_version' ) ) {
	/**
	 * The running theme's version, read from the parent theme's stylesheet.
	 *
	 * Reads the PARENT (`get_template()`) deliberately: on a child-theme site
	 * `wp_get_theme()` returns the child, whose version tracks the child's own
	 * releases and says nothing about which Customify the site started on.
	 *
	 * @since 0.4.25
	 *
	 * @return string Version string, or '' when it cannot be determined.
	 */
	function customify_get_theme_version() {
		static $version = null;

		if ( null === $version ) {
			$theme   = wp_get_theme( get_template() );
			$version = $theme->exists() ? (string) $theme->get( 'Version' ) : '';
		}

		return $version;
	}
}

if ( ! function_exists( 'customify_site_has_theme_mods' ) ) {
	/**
	 * Whether this site has ever saved a Customify `theme_mod`.
	 *
	 * WordPress seeds `theme_mods_<stylesheet>` on theme switch with
	 * bookkeeping entries of its own (`0 => false`, `nav_menu_locations`,
	 * `custom_css_post_id`), so the option merely EXISTING proves nothing.
	 * Those keys are skipped and only a real setting counts.
	 *
	 * @since 0.4.25
	 *
	 * @return bool True when at least one genuine setting is saved.
	 */
	function customify_site_has_theme_mods() {
		$mods = get_theme_mods();

		if ( ! is_array( $mods ) || empty( $mods ) ) {
			return false;
		}

		// Written by WordPress itself, not by the user configuring the theme.
		$bookkeeping = array( 0, 'nav_menu_locations', 'custom_css_post_id', 'sidebars_widgets' );

		foreach ( array_keys( $mods ) as $key ) {
			if ( ! in_array( $key, $bookkeeping, true ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'customify_get_installed_version' ) ) {
	/**
	 * The version this site first installed the theme at.
	 *
	 * Stamps the option on first call, so the marker exists from the first
	 * request after upgrading without needing a dedicated activation hook to
	 * have fired. `after_switch_theme` calls this too, which covers the
	 * genuinely-fresh case at the earliest possible moment.
	 *
	 * @since 0.4.25
	 *
	 * @return string Stamped version, or `CUSTOMIFY_LEGACY_INSTALL_VERSION`.
	 */
	function customify_get_installed_version() {
		$stamped = get_option( CUSTOMIFY_INSTALLED_VERSION_OPTION, '' );

		if ( is_string( $stamped ) && '' !== $stamped ) {
			return $stamped;
		}

		// Never write during install/upgrade routines or from a context with
		// no options table yet — just answer "legacy" and stamp later.
		if ( ! function_exists( 'wp_installing' ) || wp_installing() ) {
			return CUSTOMIFY_LEGACY_INSTALL_VERSION;
		}

		$version = customify_get_theme_version();

		if ( '' === $version || customify_site_has_theme_mods() ) {
			$stamped = CUSTOMIFY_LEGACY_INSTALL_VERSION;
		} else {
			$stamped = $version;
		}

		// Autoloaded: read on nearly every request that builds the Customizer
		// config, and a single short string.
		update_option( CUSTOMIFY_INSTALLED_VERSION_OPTION, $stamped, true );

		return $stamped;
	}
}

if ( ! function_exists( 'customify_is_fresh_install_since' ) ) {
	/**
	 * Whether this site first installed the theme at or after `$version`.
	 *
	 * The gate for any default or style that would otherwise change what an
	 * existing site renders. Use it at config-build time:
	 *
	 *     'default' => customify_is_fresh_install_since( '0.4.25' )
	 *         ? array( 'type' => 'svg', 'icon' => 'bag', 'svg' => '' )
	 *         : array( 'type' => 'font-awesome', 'icon' => 'fa fa-shopping-basket' ),
	 *
	 * A site that has saved an explicit value is unaffected either way — the
	 * saved value wins over any default. This only decides what a site that
	 * never touched the field sees.
	 *
	 * @since 0.4.25
	 *
	 * @param string $version Minimum install version, e.g. '0.4.25'.
	 *
	 * @return bool
	 */
	function customify_is_fresh_install_since( $version ) {
		return version_compare( customify_get_installed_version(), $version, '>=' );
	}
}

/**
 * Stamp the marker the moment the theme is activated.
 *
 * At this point a genuinely fresh site still has no Customify `theme_mod`s, so
 * it stamps the running version; a site re-activating the theme after having
 * configured it stamps legacy, which is the correct answer for it.
 */
add_action( 'after_switch_theme', 'customify_get_installed_version' );
