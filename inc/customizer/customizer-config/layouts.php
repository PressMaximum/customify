<?php
if ( ! function_exists( 'customify_customizer_layouts_config' ) ) {
	function customify_customizer_layouts_config( $configs ){

		$config = array(

			// Layout panel
			array(
				'name'           => 'layout_panel',
				'type'           => 'panel',
				'priority' => 22,
				'theme_supports' => '',
				'title'          => __( 'Layouts', 'customify' ),
			),

			// Global layout section.
			array(
				'name'           => 'global_layout_section',
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Global Layouts', 'customify' ),
			),
				array(
					'name' => 'site_layout',
					'type' => 'radio_group',
					'section' => 'global_layout_section',
					'title' => __('Site layout mode', 'customify'),
					'description' => __('Select global site layout.', 'customify'),
					'default' => 'site-full-width',
                    'css_format' => 'html_class',
                    'selector' => 'body',
					'choices' => array(
						'site-full-width' => __('Full Width', 'customify'),
						'site-boxed' => __('Boxed', 'customify'),
						'site-framed' => __('Framed', 'customify'),
					)
				),

				array(
					'name' => 'container_width',
					'type' => 'slider',
					'device_settings' => false,
					'default' => 1200,
					'min' => 700,
					'max' => 2000,
					'section' => 'global_layout_section',
					'title'          => __( 'Container Width', 'customify' ),
					'selector' => '.customify-container, .layout-contained, .site-framed .site, .site-boxed .site',
					'css_format' => 'max-width: {{value}}'
				),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_layouts_config' );