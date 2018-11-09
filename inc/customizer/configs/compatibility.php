<?php
if ( ! function_exists( 'customify_customizer_compatibility_config' ) ) {
	/**
	 * Add compatibility panel.
	 *
	 * @param array $configs List customize settings.
	 *
	 * @return array
	 */
	function customify_customizer_compatibility_config( $configs ) {

		$panel  = 'compatibility';
		$config = array(
			// Layout panel.
			array(
				'name'     => $panel . '_panel',
				'type'     => 'panel',
				'priority' => 100,
				'title'    => __( 'Compatibility', 'customify' ),
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_compatibility_config' );
