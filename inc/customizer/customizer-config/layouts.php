<?php
if ( ! function_exists( 'customify_customizer_layouts_config' ) ) {
	function customify_customizer_layouts_config( $configs ){

	    $section = 'global_layout_section';

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
				'name'           => $section,
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Global Layouts', 'customify' ),
			),
				array(
					'name' => 'site_layout',
					'type' => 'radio_group',
					'section' => $section,
					'title' => __('Site Layout Mode', 'customify'),
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
                    'name' => 'site_box_shadow',
                    'type' => 'radio_group',
                    'section' => $section,
                    'title' => __('Site box shadow', 'customify'),
                    'choices' => array(
                        'box-shadow' => __('Yes', 'customify'),
                        'no-box-shadow' => __('No', 'customify'),
                    ),
                    'default' => 'box-shadow',
                    'css_format' => 'html_class',
                    'selector' => '#page',
                    'required' => array(
                        array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
                    )
                ),

				array(
					'name' => 'container_width',
					'type' => 'slider',
					'device_settings' => false,
					'default' => 1200,
					'min' => 700,
					'max' => 2000,
					'section' => $section,
					'title'          => __( 'Container Width', 'customify' ),
					'selector' => '.customify-container, .layout-contained, .site-framed .site, .site-boxed .site',
					'css_format' => 'max-width: {{value}}'
				),

                array(
                    'name' => 'site_margin',
                    'type' => 'css_ruler',
                    'section' => $section,
                    'title' => __('Framed Margin', 'customify'),
                    'device_settings' => true,
                    'fields_disabled' => array(
                        'left' => '',
                        'right' => '',
                    ),
                    'css_format' => array(
                        'top' => 'margin-top: {{value}};',
                        'bottom' => 'margin-bottom: {{value}};',
                    ),
                    'selector' => '.site-framed .site',
                    'required' => array(
                        array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
                    )
                ),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_layouts_config' );