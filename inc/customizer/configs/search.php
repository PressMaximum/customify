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
				'name'            => $args['id'] . '_columns',
				'type'            => 'select',
				'section'         => $level_2_panel,
				'default'         => 3,
				'choices'         => array(
					1 => __( '1 Column', 'customify' ),
					2 => __( '2 Columns', 'customify' ),
					3 => __( '3 Columns', 'customify' ),
					4 => __( '4 Columns', 'customify' ),
				),
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Columns', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_default_scope',
				'type'            => 'select',
				'section'         => $level_2_panel,
				'default'         => '',
				// Built at Customizer registration time, well after `init`, so
				// every custom post type is already registered. Same lazy shape
				// as the header search items' scope select.
				'choices'         => function_exists( 'customify_search_get_scope_choices' ) ? customify_search_get_scope_choices() : array( '' => __( 'Everything', 'customify' ) ),
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Default results scope', 'customify' ),
				'description'     => __( 'Land unscoped searches on this content type\'s results (falls back to Everything when the term has no matches there).', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_show_search_form',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show search form on the results page', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_show_media',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show featured images', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_show_tabs',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show content type tabs', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_show_counts',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show result counts', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_show_type_badge',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show content type badge', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_no_results_products',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 1,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show popular products when nothing matches', 'customify' ),
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
