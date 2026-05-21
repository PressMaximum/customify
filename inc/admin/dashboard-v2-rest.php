<?php
/**
 * Customify Dashboard v2 — REST endpoint for the Settings tab.
 *
 * Uses the kit's `SettingsControllerBase` + `SchemaBuilder` so the
 * schema, defaults, and sanitization live in one declaration. Pro / child
 * theme extends the panel set via `customify_dashboard_settings_schema`
 * (SPEC §9.2).
 *
 * Legacy `customify_fa_ver` option stays the source of truth for the
 * front-end FA loader; this controller mirrors writes back to it so the
 * Customizer / theme code keeps reading from where it already does.
 *
 * @package Customify
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (
	! class_exists( '\\PressMaximum\\DashboardKit\\REST\\SettingsControllerBase' )
	|| ! class_exists( '\\PressMaximum\\DashboardKit\\Schema\\SchemaBuilder' )
) {
	return;
}

use PressMaximum\DashboardKit\REST\SettingsControllerBase;
use PressMaximum\DashboardKit\Schema\SchemaBuilder;

const CUSTOMIFY_DASHBOARD_V2_OPTION = 'customify_dashboard_v2_settings';
const CUSTOMIFY_DASHBOARD_V2_REST_NS = 'customify/v1';

/**
 * Build the settings schema. Filterable so Pro can append panels.
 *
 * @return SchemaBuilder
 */
function customify_dashboard_v2_schema(): SchemaBuilder {
	$schema = SchemaBuilder::create()
		->panel( 'general', __( 'Theme settings', 'customify' ) )
		->description( __( 'Theme-wide settings that change how Customify renders the front-end.', 'customify' ) )
		->radioField(
			'fontAwesomeVersion',
			__( 'Font Awesome version', 'customify' ),
			'v4',
			array(
				'v4'   => __( 'Version 4', 'customify' ),
				'v6'   => __( 'Version 6', 'customify' ),
				'v456' => __( 'Version 6 + backwards-compatibility with 4 & 5', 'customify' ),
			),
			array(
				'description' => __( 'Pick the Font Awesome version Customify enqueues on the front-end.', 'customify' ),
			)
		)
		->endPanel();

	/**
	 * Allow Pro / child theme to mutate the schema.
	 *
	 * @param SchemaBuilder $schema The active schema builder.
	 */
	return apply_filters( 'customify_dashboard_v2_schema', $schema );
}

/**
 * REST controller.
 */
final class Customify_Dashboard_V2_Settings_Controller extends SettingsControllerBase {
	protected function getNamespace(): string {
		return CUSTOMIFY_DASHBOARD_V2_REST_NS;
	}
	protected function getRestBase(): string {
		return 'settings';
	}
	protected function getCapability(): string {
		return 'manage_options';
	}
	protected function getOptionName(): string {
		return CUSTOMIFY_DASHBOARD_V2_OPTION;
	}
	protected function getDefaults(): array {
		return customify_dashboard_v2_schema()->buildDefaults();
	}
	protected function sanitizeIncoming( array $incoming ): array {
		return customify_dashboard_v2_schema()->sanitize( $incoming );
	}
}

/**
 * Register routes.
 */
add_action( 'rest_api_init', static function (): void {
	( new Customify_Dashboard_V2_Settings_Controller() )->register_routes();

	// Schema endpoint — JS reads this once at boot. Separate from the
	// values GET so consumers can cache the schema longer.
	register_rest_route(
		CUSTOMIFY_DASHBOARD_V2_REST_NS,
		'/settings/schema',
		array(
			'methods'             => 'GET',
			'permission_callback' => static function (): bool {
				return current_user_can( 'manage_options' );
			},
			'callback'            => static function (): \WP_REST_Response {
				return rest_ensure_response( customify_dashboard_v2_schema()->buildSchema() );
			},
		)
	);
} );

/**
 * Mirror writes to the legacy `customify_fa_ver` option so the
 * front-end FA loader (which still reads the legacy key directly) stays
 * in sync. Hooked at the `updated_option` event for the new option.
 *
 * @param string $option Option name being saved.
 * @param mixed  $old    Previous value.
 * @param mixed  $new    New value.
 */
function customify_dashboard_v2_mirror_to_legacy( string $option, $old, $new ): void {
	if ( $option !== CUSTOMIFY_DASHBOARD_V2_OPTION ) {
		return;
	}
	if ( ! is_array( $new ) ) {
		return;
	}
	$general = $new['general'] ?? array();
	if ( ! is_array( $general ) ) {
		return;
	}
	$version = $general['fontAwesomeVersion'] ?? null;
	if ( in_array( $version, array( 'v4', 'v6', 'v456' ), true ) ) {
		update_option( 'customify_fa_ver', $version );
	}
}
add_action( 'updated_option', 'customify_dashboard_v2_mirror_to_legacy', 10, 3 );
add_action( 'added_option', static function ( string $option, $value ): void {
	customify_dashboard_v2_mirror_to_legacy( $option, null, $value );
}, 10, 2 );

/**
 * Read the merged settings (saved over defaults), reading the legacy
 * `customify_fa_ver` when the new option hasn't been written yet.
 * Surfaced in boot data so the JS can hydrate the form synchronously.
 *
 * @return array<string, array<string, mixed>>
 */
function customify_dashboard_v2_get_settings(): array {
	$defaults = customify_dashboard_v2_schema()->buildDefaults();
	$saved    = get_option( CUSTOMIFY_DASHBOARD_V2_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	if ( empty( $saved ) ) {
		$legacy = get_option( 'customify_fa_ver', '' );
		if ( in_array( $legacy, array( 'v4', 'v6', 'v456' ), true ) ) {
			$saved = array(
				'general' => array(
					'fontAwesomeVersion' => $legacy,
				),
			);
		}
	}

	$merged = $defaults;
	foreach ( $saved as $panel => $values ) {
		if ( ! isset( $merged[ $panel ] ) || ! is_array( $merged[ $panel ] ) ) {
			$merged[ $panel ] = array();
		}
		foreach ( (array) $values as $key => $value ) {
			$merged[ $panel ][ $key ] = $value;
		}
	}

	return $merged;
}
