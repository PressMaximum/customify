<?php
if ( ! function_exists( 'customify_customizer_layouts_config' ) ) {
	/**
	 * Add layout settings.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * @param array $configs
	 * @return array
	 */
	function customify_customizer_layouts_config( $configs ) {
		$config = array(

			// Layout panel.
			array(
				'name'           => 'layout_panel',
				'type'           => 'panel',
				'priority'       => 18,
				'theme_supports' => '',
				'title'          => __( 'Layouts', 'customify' ),
			),

			// Global layout section.
			array(
				'name'           => 'global_layout_section',
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Global', 'customify' ),
			),
			array(
				'name'        => 'site_layout',
				'type'        => 'radio_group',
				'section'     => 'global_layout_section',
				'title'       => __( 'Site layout', 'customify' ),
				'description' => __( 'Select global site layout.', 'customify' ),
				'default'     => 'site-full-width',
				'css_format'  => 'html_class',
				'selector'    => 'body',
				'choices'     => array(
					'site-full-width' => __( 'Full Width', 'customify' ),
					'site-boxed'      => __( 'Boxed', 'customify' ),
					'site-framed'     => __( 'Framed', 'customify' ),
				),
			),

			array(
				'name'       => 'site_box_shadow',
				'type'       => 'radio_group',
				'section'    => 'global_layout_section',
				'title'      => __( 'Site boxed/framed shadow', 'customify' ),
				'choices'    => array(
					'box-shadow'    => __( 'Yes', 'customify' ),
					'no-box-shadow' => __( 'No', 'customify' ),
				),
				'default'    => 'box-shadow',
				'css_format' => 'html_class',
				'selector'   => '#page',
				'required'   => array(
					array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
				),
			),

			array(
				'name'            => 'site_margin',
				'type'            => 'css_ruler',
				'section'         => 'global_layout_section',
				'title'           => __( 'Site framed margin', 'customify' ),
				'device_settings' => true,
				'fields_disabled' => array(
					'left'  => '',
					'right' => '',
				),
				'css_format'      => array(
					'top'    => 'margin-top: {{value}};',
					'bottom' => 'margin-bottom: {{value}};',
				),
				'selector'        => '.site-framed .site',
				'required'        => array(
					array( 'site_layout', '=', 'site-framed' ),
				),
			),
			/**
			 * @since 0.2.6 Change css_format and selector.
			 */
			array(
				'name'            => 'container_width',
				'type'            => 'slider',
				'device_settings' => false,
				'default'         => 1200,
				'min'             => 700,
				'step'            => 10,
				'max'             => 2000,
				'section'         => 'global_layout_section',
				'title'           => __( 'Container width', 'customify' ),
				'selector'        => 'format',
				'css_format'      => '.customify-container, .layout-contained, .site-framed .site, .site-boxed .site { max-width: {{value}}; } .main-layout-content .entry-content > .alignwide { width: calc( {{value}} - 4em ); max-width: 100vw;  }',
			),

			// Site content layout.
			array(
				'name'       => 'site_content_layout',
				'type'       => 'radio_group',
				'section'    => 'global_layout_section',
				'title'      => __( 'Site content layout', 'customify' ),
				'choices'    => array(
					'site-content-fullwidth' => __( 'Full width', 'customify' ),
					'site-content-boxed'     => __( 'Boxed', 'customify' ),
				),
				'default'    => 'site-content-boxed',
				'css_format' => 'html_class',
				'selector'   => '.site-content',
			),

			array(
				'name'            => 'site_content_padding',
				'type'            => 'css_ruler',
				'section'         => 'global_layout_section',
				'title'           => __( 'Site content padding', 'customify' ),
				'device_settings' => true,
				'fields_disabled' => array(
					'left'  => '',
					'right' => '',
				),
				'css_format'      => array(
					'top'    => 'padding-top: {{value}};',
					'bottom' => 'padding-bottom: {{value}};',
				),
				'selector'        => '#sidebar-secondary, #sidebar-primary, #main',
			),

			// Page layout.
			array(
				'name'           => 'sidebar_layout_section',
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Sidebars', 'customify' ),
			),
			// Global sidebar layout.
			array(
				'name'    => 'sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Default Sidebar Layout', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Sidebar vertical border.
			array(
				'name'       => 'sidebar_vertical_border',
				'type'       => 'radio_group',
				'section'    => 'sidebar_layout_section',
				'title'      => __( 'Sidebar with vertical border', 'customify' ),
				'choices'    => array(
					'sidebar_vertical_border'    => __( 'Yes', 'customify' ),
					'no-sidebar_vertical_border' => __( 'No', 'customify' ),
				),
				'default'    => 'sidebar_vertical_border',
				'css_format' => 'html_class',
				'selector'   => 'body',
			),

			// Page sidebar layout.
			array(
				'name'    => 'page_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Pages', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Blog Posts sidebar layout.
			array(
				'name'    => 'posts_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Blog posts', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Blog Posts sidebar layout.
			array(
				'name'    => 'posts_archives_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Blog Archive Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Search.
			array(
				'name'    => 'search_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Search Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// 404.
			array(
				'name'    => '404_sidebar_layout',
				'type'    => 'select',
				'default' => 'content',
				'section' => 'sidebar_layout_section',
				'title'   => __( '404 Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
		);

		$post_types = Customify()->get_post_types( false );

		if ( count( $post_types ) ) {
			$config[] = array(
				'name'    => 'post_types_sidebar_h_tb',
				'type'    => 'heading',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Post Type Settings', 'customify' ),
			);

			foreach ( $post_types as $pt => $label ) {
				$config[] = array(
					'name'    => "{$pt}_sidebar_layout",
					'type'    => 'select',
					'default' => 'content',
					'section' => 'sidebar_layout_section',
					'title'   => sprintf( __( 'Single %s', 'customify' ), $label['singular_name'] ),
					'choices' => array_merge(
						array( 'default' => __( 'Default', 'customify' ) ),
						customify_get_config_sidebar_layouts()
					),
				);
			}
		}

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_layouts_config' );
