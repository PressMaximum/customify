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

	add_filter( 'default_option_elementor_container_width', 'customify_elementor_default_container_width' );
	add_filter( 'option_elementor_container_width', 'customify_elementor_default_container_width' );
	/**
	 * Set the default Elementor content width to 1184px when the site owner
	 * has not customised the value in Elementor > Settings.
	 *
	 * @param mixed $value Current option value (empty string when unset).
	 * @return mixed
	 */
	function customify_elementor_default_container_width( $value ) {
		if ( '' === $value || null === $value || false === $value ) {
			return 1184;
		}
		return $value;
	}
}

