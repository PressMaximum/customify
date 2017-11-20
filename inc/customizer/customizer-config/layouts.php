<?php
if ( ! function_exists( '_beacon_customizer_layouts_config' ) ) {
	function _beacon_customizer_layouts_config( $configs ){

		$config = array(
			array(
				'name'           => '_beacon_layouts',
				'type'           => 'panel',
				'priority' => 22,
				'theme_supports' => '',
				'title'          => __( 'Layouts', '_beacon' ),
			),

			array(
				'name'           => '_beacon_layouts_section',
				'type'           => 'section',
				'panel'          => '_beacon_layouts',
				//'priority' => 22,
				'theme_supports' => '',
				'title'          => __( 'Beacon Section', '_beacon' ),
				'description'    => __( 'This is section description', '_beacon' ),
			),

			array(
				'name' => 'container_width',
				'type' => 'slider',
				'device_settings' => false,
				'default' => 1200,
				'min' => 700,
				'max' => 2000,
				'section' => '_beacon_layouts_section',
				//'device' => 'mobile', // mobile || general
				//'priority' => 22,
				'title'          => __( 'Container Width', '_beacon' ),
				'selector' => '._beacon_container',
				'css_format' => 'max-width: {{value}}'
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( '_beacon/customizer/config', '_beacon_customizer_layouts_config' );