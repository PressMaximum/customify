<?php
/**
 * Customify Pro compatibility — disable Pro modules that the theme implements
 * natively, and surface the override to the React dashboard so the user sees
 * "Owned by theme" instead of a toggle that silently does nothing.
 *
 * Why a read-time filter (not just the dashboard injection):
 *   Customify_Pro::load_modules() runs at after_setup_theme:30 and
 *   instantiates every enabled module. If the user previously turned the Pro
 *   module on (option = 1) and the theme later started shipping the feature,
 *   we MUST keep the option masked at read so Pro doesn't double-instantiate
 *   the conflicting module on the frontend.
 *
 * Filterable via `customify/compat/pro_modules_owned_by_theme` so a child
 * theme or future Pro module can opt in/out per class name.
 */

/**
 * Map of Pro module class names → reason the theme has disabled them.
 * Reason is shown in the dashboard tooltip when canToggle is false.
 *
 * @return array<string,string>
 */
function customify_pro_modules_owned_by_theme() {
	$owned = array(
		'Customify_Pro_Module_Header_Transparent' =>
			__( 'Built into Customify since 0.4.14 — configure under Customizer → Header → Transparent.', 'customify' ),
	);
	return apply_filters( 'customify/compat/pro_modules_owned_by_theme', $owned );
}

add_filter( 'option_customify_modules', function ( $modules ) {
	if ( ! is_array( $modules ) ) {
		$modules = array();
	}
	foreach ( array_keys( customify_pro_modules_owned_by_theme() ) as $class_name ) {
		$modules[ $class_name ] = 0;
	}
	return $modules;
} );
