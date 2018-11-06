<?php
function customify_is_e_theme_location( $location ) {
	$is_exist = function_exists( 'elementor_theme_do_location' );
	if ( $is_exist ) {
		return elementor_theme_do_location( $location );
	}
	return false;
}

if ( defined( 'ELEMENTOR_VERSION' ) ) {
	add_action( 'elementor/theme/register_locations', 'customify_elementor_register_locations' );
	/**
	 * Register Elementor theme location
	 *
	 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_locations_manager Elementor location manager.
	 */
	function customify_elementor_register_locations( $elementor_locations_manager ) {
		$elementor_locations_manager->register_all_core_location();
	}
}

