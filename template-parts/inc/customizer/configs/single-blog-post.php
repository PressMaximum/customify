<?php
if ( ! function_exists( 'customify_customizer_single_blog_config' ) ) {
	function customify_customizer_single_blog_config( $configs = array() ) {

		$args = array(
			'name'     => __( 'Single Blog Post', 'customify' ),
			'id'       => 'single_blog_post',
			'selector' => '.entry.entry-single',
			'cb'       => 'customify_single_post',
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
				'name'       => $args['id'] . '_content_width',
				'section'    => $level_2_panel,
				'type'       => 'slider',
				'max'        => 1200,
				'label'      => __( 'Content Max Width', 'customify' ),
				'selector'   => '.single-post .content-inner',
				'css_format' => 'max-width: {{value}};',
			),

			array(
				'name'             => $args['id'] . '_items',
				'section'          => $level_2_panel,
				'type'             => 'repeater',
				'title'            => __( 'Items Display', 'customify' ),
				'live_title_field' => 'title',
				'addable'          => false,
				'title_only'       => true,
				'selector'         => $args['selector'],
				'render_callback'  => $args['cb'],
				'default'          => array(
					array(
						'_visibility' => '',
						'_key'        => 'title',
						'title'       => __( 'Title', 'customify' ),
					),
					array(
						'_key'        => 'meta',
						'_visibility' => '',
						'title'       => __( 'Meta', 'customify' ),
					),
					array(
						'_key'        => 'thumbnail',
						'_visibility' => '',
						'title'       => __( 'Thumbnail', 'customify' ),
					),
					array(
						'_key'        => 'content',
						'_visibility' => '',
						'title'       => __( 'Content', 'customify' ),
					),
					array(
						'_key'        => 'categories',
						'_visibility' => 'hidden',
						'title'       => __( 'Categories', 'customify' ),
					),
					array(
						'_key'        => 'tags',
						'_visibility' => '',
						'title'       => __( 'Tags', 'customify' ),
					),
					array(
						'_key'        => 'author_bio',
						'_visibility' => 'hidden',
						'title'       => __( 'Author Biography', 'customify' ),
					),
					array(
						'_key'        => 'navigation',
						'_visibility' => '',
						'title'       => __( 'Post Navigation', 'customify' ),
					),

					array(
						'_key'        => 'related',
						'_visibility' => 'hidden',
						'title'       => __( 'Related Posts', 'customify' ),
					),

					array(
						'_key'        => 'comment_form',
						'_visibility' => '',
						'title'       => __( 'Comment Form', 'customify' ),
					),

				),
				'fields'           => array(
					array(
						'name' => '_key',
						'type' => 'hidden',
					),
					array(
						'name'  => 'title',
						'type'  => 'hidden',
						'label' => __( 'Title', 'customify' ),
					),
				),
			),

			array(
				'name'            => $args['id'] . '_thumbnail_size',
				'type'            => 'select',
				'section'         => $level_2_panel,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'default'         => 'large',
				'label'           => __( 'Thumbnail Size', 'customify' ),
				'choices'         => customify_get_all_image_sizes(),
			),

			array(
				'name'    => $level_2_panel . '_h_meta',
				'type'    => 'heading',
				'section' => $level_2_panel,
				'title'   => __( 'Meta Settings', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_meta_sep',
				'section'         => $level_2_panel,
				'type'            => 'text',
				'default'         => '',
				'label'           => __( 'Separator', 'customify' ),
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
			),

			array(
				'name'       => $args['id'] . '_meta_sep_width',
				'section'    => $level_2_panel . '_meta',
				'type'       => 'slider',
				'max'        => 20,
				'label'      => __( 'Separator Width', 'customify' ),
				'selector'   => $args['selector'] . ' .entry-meta .sep',
				'css_format' => 'margin-left: calc( {{value}} / 2 ); margin-right: calc( {{value}} / 2 );',
			),

			array(
				'name'             => $args['id'] . '_meta_config',
				'section'          => $level_2_panel,
				'type'             => 'repeater',
				'description'      => __( 'Drag to reorder the meta item.', 'customify' ),
				'live_title_field' => 'title',
				'limit'            => 4,
				'addable'          => false,
				'title_only'       => true,
				'selector'         => $args['selector'],
				'render_callback'  => $args['cb'],
				'default'          => array(
					array(
						'_key'  => 'author',
						'title' => __( 'Author', 'customify' ),
					),
					array(
						'_key'  => 'date',
						'title' => __( 'Date', 'customify' ),
					),
					array(
						'_key'  => 'categories',
						'title' => __( 'Categories', 'customify' ),
					),
					array(
						'_key'  => 'comment',
						'title' => __( 'Comment', 'customify' ),
					),

				),
				'fields'           => array(
					array(
						'name' => '_key',
						'type' => 'hidden',
					),
					array(
						'name'  => 'title',
						'type'  => 'hidden',
						'label' => __( 'Title', 'customify' ),
					),
				),
			),

			array(
				'name'            => $args['id'] . '_author_avatar',
				'type'            => 'checkbox',
				'section'         => $level_2_panel,
				'default'         => 0,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show author avatar', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_avatar_size',
				'type'            => 'slider',
				'section'         => $level_2_panel,
				'default'         => 32,
				'max'             => 150,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Avatar Size', 'customify' ),
				'required'        => array( $args['id'] . '_author_avatar', '==', '1' ),
			),

		);

		return array_merge( $configs, $config );

	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_single_blog_config' );

