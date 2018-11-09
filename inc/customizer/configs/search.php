<?php
if ( ! function_exists( 'customify_customizer_search_config' ) ) {
	function customify_customizer_search_config( $configs = array() ) {

		$args = array(
			'name'     => __( 'Search Results', 'customify' ),
			'id'       => 'search_results',
			'selector' => '',
			'cb'       => '',
		);

		$top_panel     = 'blog_panel';
		$level_2_panel = 'section_' . $args['id'];

		$config = array(

			array(
				'name'  => $level_2_panel,
				'type'  => 'section',
				'panel' => $top_panel,
				'title' => $args['name'],
			),

			array(
				'name'            => $args['id'] . '_excerpt_type',
				'type'            => 'select',
				'section'         => $level_2_panel,
				'default'         => 'excerpt',
				'choices'         => array(
					'custom'   => __( 'Custom', 'customify' ),
					'excerpt'  => __( 'Use excerpt metabox', 'customify' ),
					'more_tag' => __( 'Strip excerpt by more tag', 'customify' ),
					'content'  => __( 'Full content', 'customify' ),
				),
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Excerpt Type', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_excerpt_length',
				'type'            => 'number',
				'section'         => $level_2_panel,
				'default'         => 150,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Excerpt Length', 'customify' ),
				'required'        => array( $args['id'] . '_excerpt_type', '=', 'custom' ),
			),
			array(
				'name'            => $args['id'] . '_excerpt_more',
				'type'            => 'text',
				'section'         => $level_2_panel,
				'default'         => '',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Excerpt More', 'customify' ),
			),

		);

		return array_merge( $configs, $config );

	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_search_config' );
