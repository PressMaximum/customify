<?php
/**
 * Customify Pro compatibility — disable Pro modules that the theme implements natively.
 *
 * Runs at after_setup_theme priority 2, before Customify Pro loads its modules
 * at priority 30. The filter intercepts get_option('customify_modules') so that
 * is_enabled_module() returns false for modules the theme owns.
 */
add_filter( 'option_customify_modules', function ( $modules ) {
	if ( ! is_array( $modules ) ) {
		$modules = array();
	}
	// Theme ships its own transparent header — disable the Pro module.
	$modules['Customify_Pro_Module_Header_Transparent'] = 0;
	return $modules;
} );
