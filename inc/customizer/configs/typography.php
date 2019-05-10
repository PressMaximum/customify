<?php
if ( ! function_exists( 'customify_customizer_typography_config' ) ) {
	/**
	 * Add typograhy settings.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * @param array $configs
	 * @return array
	 */
	function customify_customizer_typography_config( $configs ) {

		$section = 'global_typography';

		$config = array(
			array(
				'name'     => 'typography_panel',
				'type'     => 'panel',
				'priority' => 22,
				'title'    => __( 'Typography', 'customify' ),
			),

			// Base.
			array(
				'name'  => "{$section}_base",
				'type'  => 'section',
				'panel' => 'typography_panel',
				'title' => __( 'Base', 'customify' ),
			),

			array(
				'name'        => "{$section}_base_p",
				'type'        => 'typography',
				'section'     => "{$section}_base",
				'title'       => __( 'Body & Paragraph', 'customify' ),
				'description' => __( 'Apply to body and paragraph text.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => 'body',
			),

			array(
				'name'        => "{$section}_base_heading",
				'type'        => 'typography',
				'section'     => "{$section}_base",
				'title'       => __( 'Heading', 'customify' ),
				'description' => __( 'Apply to all heading elements.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6',
				'fields'      => array(
					'font_size'      => false,
					'line_height'    => false,
					'letter_spacing' => false,
				),
			),
			array(
				'name'        => "{$section}_base_widget_title",
				'type'        => 'typography',
				'section'     => "{$section}_base",
				'title'       => __( 'Widget Title', 'customify' ),
				'description' => __( 'Apply to all widget title in site content.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => '.site-content .widget-title',
			),

			// Site Title and Tagline.
			array(
				'name'  => "{$section}_site_tt",
				'type'  => 'section',
				'panel' => 'typography_panel',
				'title' => __( 'Site Title & Tagline', 'customify' ),
			),

			array(
				'name'       => "{$section}_site_tt_title",
				'type'       => 'typography',
				'section'    => "{$section}_site_tt",
				'title'      => __( 'Site Title', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.site-branding .site-title, .site-branding .site-title a',
			),

			array(
				'name'       => "{$section}_site_tt_desc",
				'type'       => 'typography',
				'section'    => "{$section}_site_tt",
				'title'      => __( 'Tagline', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.site-branding .site-description',
			),

			// Content.
			array(
				'name'  => "{$section}_content",
				'type'  => 'section',
				'panel' => 'typography_panel',
				'title' => __( 'Content', 'customify' ),
			),

			array(
				'name'       => "{$section}_heading_h1",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H1', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h1, .wp-block h1, .entry-single .entry-title',
			),

			array(
				'name'       => "{$section}_heading_h2",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H2', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h2, .wp-block h2',
			),

			array(
				'name'       => "{$section}_heading_h3",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H3', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h3, .wp-block h3',
			),

			array(
				'name'       => "{$section}_heading_h4",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H4', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h4, .wp-block h4',
			),

			array(
				'name'       => "{$section}_heading_h5",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H5', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h5, .wp-block h5',
			),

			array(
				'name'       => "{$section}_heading_h6",
				'type'       => 'typography',
				'section'    => "{$section}_content",
				'title'      => __( 'Heading H6', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.entry-content h6, .wp-block h6',
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_typography_config' );
