<?php
if ( ! function_exists( 'customify_customizer_layouts_config' ) ) {
	function customify_customizer_layouts_config( $configs ){

		$config = array(
			array(
				'name'           => 'customify_layouts',
				'type'           => 'panel',
				'priority' => 22,
				'theme_supports' => '',
				'title'          => __( 'Layouts', 'customify' ),
			),

			array(
				'name'           => 'customify_layouts_section',
				'type'           => 'section',
				'panel'          => 'customify_layouts',
				//'priority' => 22,
				'theme_supports' => '',
				'title'          => __( 'Customify Section', 'customify' ),
				'description'    => __( 'This is section description', 'customify' ),
			),

			array(
				'name' => 'container_width',
				'type' => 'slider',
				'device_settings' => false,
				'default' => 1200,
				'min' => 700,
				'max' => 2000,
				'section' => 'customify_layouts_section',
				//'device' => 'mobile', // mobile || general
				//'priority' => 22,
				'title'          => __( 'Container Width', 'customify' ),
				'selector' => '.customify_container',
				'css_format' => 'max-width: {{value}}'
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_layouts_config' );