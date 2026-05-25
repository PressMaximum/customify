<?php
/**
 * Customizer config: Styling panel registration.
 *
 * Note: Site-wide color fields previously registered here (Global Colors section)
 * have been moved to inc/customizer/configs/colors.php — a new top-level
 * "Colors" section that consolidates palette + links + backgrounds + overrides.
 *
 * The styling_panel registration is kept here because typography.php and
 * layouts.php still register sections under it.
 *
 * @package Customify
 */

if ( ! function_exists( 'customify_customizer_styling_config' ) ) {
	function customify_customizer_styling_config( $configs ) {

		$config = array(

			// Styling panel — kept; Typography + Layouts sections register under it.
			array(
				'name'     => 'styling_panel',
				'type'     => 'panel',
				'priority' => 22,
				'title'    => __( 'Styling', 'customify' ),
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_styling_config' );
