<?php
if ( ! function_exists( 'customify_customizer_blog_config' ) ) {
	function customify_customizer_blog_config( $args = array() ) {

		$args          = wp_parse_args(
			$args,
			array(
				'name'     => __( 'Blog Posts', 'customify' ),
				'id'       => 'blog_post',
				'selector' => '#blog-posts',
				'cb'       => 'customify_blog_posts',
			)
		);
		$top_panel     = 'blog_panel';
		$level_2_panel = 'panel_' . $args['id'];

		$config = array(
			array(
				'name'  => $level_2_panel,
				'type'  => 'panel',
				'panel' => $top_panel,
				'title' => $args['name'],
			),

			array(
				'name'  => $level_2_panel . '_layout',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Layout', 'customify' ),
			),

			array(
				'name'             => $args['id'] . '_layout',
				'type'             => 'image_select',
				'section'          => $level_2_panel . '_layout',
				'label'            => __( 'Layout', 'customify' ),
				'default'          => 'blog_column',
				'selector'         => $args['selector'],
				'render_callback'  => $args['cb'],
				'disabled_msg'     => __( 'This option is available in Customify Pro plugin only.', 'customify' ),
				'disabled_pro_msg' => __( 'Please activate module Blog Posts to use this layout.', 'customify' ),
				'choices'          => array(
					'blog_classic' => array(
						'img' => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/blog_classic.svg',
					),
					'blog_column'  => array(
						'img' => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/blog_column.svg',
					),
					'blog_masonry' => array(
						'img'     => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/blog_masonry.svg',
						'disable' => 1,
						'bubble'  => __( 'Pro', 'customify' ),
					),
					'blog_lateral' => array(
						'img'     => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/blog_lateral.svg',
						'disable' => 1,
						'bubble'  => __( 'Pro', 'customify' ),
					),

				),
				'reset_controls'   => array(
					$args['id'] . '_media_ratio',
					$args['id'] . '_media_width',
				),
			),

			array(
				'name'    => $level_2_panel . '_layout_h1',
				'type'    => 'heading',
				'section' => $level_2_panel . '_layout',
				'title'   => __( 'Article Styling', 'customify' ),
			),

			array(
				'name'       => $args['id'] . '_a_item',
				'type'       => 'styling',
				'section'    => $level_2_panel . '_layout',
				'selector'   => array(
					'normal'        => "{$args['selector'] } .entry-inner",
					'hover'         => "{$args['selector'] } .entry-inner:hover",
					'normal_margin' => "{$args['selector'] } .entry-inner",
				),
				'css_format' => 'styling',
				'label'      => __( 'Article Wrapper', 'customify' ),
				'fields'     => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'bg_image'      => false,
						'bg_cover'      => false,
						'bg_position'   => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
					),
					'hover_fields'  => array(
						'link_color' => false,
					),
				),
			),

			array(
				'name'  => $level_2_panel . '_media',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Media', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_media_hide',
				'type'            => 'checkbox',
				'section'         => $level_2_panel . '_media',
				'checkbox_label'  => __( 'Hide Media', 'customify' ),
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
			),

			array(
				'name'            => $args['id'] . '_media_ratio',
				'type'            => 'slider',
				'section'         => $level_2_panel . '_media',
				'label'           => __( 'Media Ratio', 'customify' ),
				'selector'        => "{$args['selector']} .posts-layout .entry .entry-media",
				'css_format'      => 'padding-top: {{value_no_unit}}%;',
				'max'             => 200,
				'min'             => 0,
				'device_settings' => true,
				'unit'            => '%',
				'required'        => array( $args['id'] . '_media_hide', '!=', '1' ),
			),
			array(
				'name'            => $args['id'] . '_media_width',
				'type'            => 'slider',
				'section'         => $level_2_panel . '_media',
				'label'           => __( 'Media Width', 'customify' ),
				'device_settings' => true,
				'devices'         => array( 'desktop', 'tablet' ),
				'max'             => 100,
				'min'             => 20,
				'unit'            => '%',
				'selector'        => "{$args['selector']} .posts-layout .entry-media, {$args['selector']} .posts-layout.layout--blog_classic .entry-media",
				'css_format'      => 'flex-basis: {{value_no_unit}}%; width: {{value_no_unit}}%;',
				'required'        => array( $args['id'] . '_media_hide', '!=', '1' ),
			),

			array(
				'name'       => $args['id'] . '_media_radius',
				'type'       => 'slider',
				'section'    => $level_2_panel . '_media',
				'label'      => __( 'Media Radius', 'customify' ),
				'max'        => 100,
				'min'        => 0,
				'selector'   => "{$args['selector']} .posts-layout .entry-media",
				'css_format' => 'border-radius: {{value}};',
				'required'   => array( $args['id'] . '_media_hide', '!=', '1' ),
			),

			array(
				'name'            => $args['id'] . '_thumbnail_size',
				'type'            => 'select',
				'section'         => $level_2_panel . '_media',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'default'         => 'medium',
				'label'           => __( 'Thumbnail Size', 'customify' ),
				'choices'         => customify_get_all_image_sizes(),
				'required'        => array( $args['id'] . '_media_hide', '!=', '1' ),
			),
			array(
				'name'            => $args['id'] . '_hide_thumb_if_empty',
				'type'            => 'checkbox',
				'section'         => $level_2_panel . '_media',
				'default'         => '1',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Hide featured image if empty.', 'customify' ),
				'required'        => array( $args['id'] . '_media_hide', '!=', '1' ),
			),

			// Article Excerpt ---------------------------------------------------------------------------------.
			array(
				'name'  => $level_2_panel . '_excerpt',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Excerpt', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_excerpt_type',
				'type'            => 'select',
				'section'         => $level_2_panel . '_excerpt',
				'default'         => 'custom',
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
				'section'         => $level_2_panel . '_excerpt',
				'default'         => 25,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Excerpt Length', 'customify' ),
				'required'        => array( $args['id'] . '_excerpt_type', '=', 'custom' ),
			),
			array(
				'name'            => $args['id'] . '_excerpt_more',
				'type'            => 'text',
				'section'         => $level_2_panel . '_excerpt',
				'default'         => '',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Excerpt More', 'customify' ),
			),

			array(
				'name'  => $level_2_panel . '_meta',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Metas', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_meta_sep',
				'section'         => $level_2_panel . '_meta',
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
				'section'          => $level_2_panel . '_meta',
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
				'section'         => $level_2_panel . '_meta',
				'default'         => 0,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show author avatar', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_avatar_size',
				'type'            => 'slider',
				'section'         => $level_2_panel . '_meta',
				'default'         => 32,
				'max'             => 150,
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Avatar Size', 'customify' ),
				'required'        => array( $args['id'] . '_author_avatar', '==', '1' ),
			),

			array(
				'name'  => $level_2_panel . '_readmore',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Read More', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_more_display',
				'type'            => 'checkbox',
				'default'         => 1,
				'section'         => $level_2_panel . '_readmore',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'checkbox_label'  => __( 'Show Read More Button', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_more_text',
				'type'            => 'text',
				'section'         => $level_2_panel . '_readmore',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'label'           => __( 'Read More Text', 'customify' ),
				'required'        => array( $args['id'] . '_more_display', '==', '1' ),
			),
			array(
				'name'       => $args['id'] . '_more_typography',
				'type'       => 'typography',
				'css_format' => 'typography',
				'section'    => $level_2_panel . '_readmore',
				'selector'   => "{$args['selector'] } .entry-readmore a",
				'label'      => __( 'Typography', 'customify' ),
				'required'   => array( $args['id'] . '_more_display', '==', '1' ),
			),

			array(
				'name'       => $args['id'] . '_more_styling',
				'type'       => 'styling',
				'section'    => $level_2_panel . '_readmore',
				'selector'   => array(
					'normal'        => "{$args['selector'] } .entry-readmore a",
					'hover'         => "{$args['selector'] } .entry-readmore a:hover",
					'normal_margin' => "{$args['selector'] } .entry-readmore",
				),
				'css_format' => 'styling',
				'label'      => __( 'Styling', 'customify' ),
				'fields'     => array(
					'normal_fields' => array(
						'link_color'    => false, // Disable for special field.
						'bg_image'      => false,
						'bg_cover'      => false,
						'bg_position'   => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
					),
					'hover_fields'  => array(
						'link_color' => false, // Disable for special field.
					),
				),
				'required'   => array( $args['id'] . '_more_display', '==', '1' ),
			),

			array(
				'name'  => $level_2_panel . '_pagination',
				'type'  => 'section',
				'panel' => $level_2_panel,
				'title' => __( 'Pagination', 'customify' ),
			),

			array(
				'name'            => $args['id'] . '_pg_show_paging',
				'section'         => $level_2_panel . '_pagination',
				'type'            => 'checkbox',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'default'         => 1,
				'checkbox_label'  => __( 'Show Pagination', 'customify' ),
			),
			array(
				'name'            => $args['id'] . '_pg_show_nav',
				'section'         => $level_2_panel . '_pagination',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'type'            => 'checkbox',
				'default'         => 1,
				'checkbox_label'  => __( 'Show Next, Previous Label', 'customify' ),
				'required'        => array( $args['id'] . '_pg_show_paging', '==', '1' ),
			),
			array(
				'name'            => $args['id'] . '_pg_prev_text',
				'section'         => $level_2_panel . '_pagination',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'type'            => 'text',
				'label'           => __( 'Previous Label', 'customify' ),
				'required'        => array( $args['id'] . '_pg_show_paging', '==', '1' ),
			),
			array(
				'name'            => $args['id'] . '_pg_next_text',
				'section'         => $level_2_panel . '_pagination',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'type'            => 'text',
				'label'           => __( 'Next Label', 'customify' ),
				'required'        => array( $args['id'] . '_pg_show_paging', '==', '1' ),
			),

			array(
				'name'            => $args['id'] . '_pg_mid_size',
				'section'         => $level_2_panel . '_pagination',
				'selector'        => $args['selector'],
				'render_callback' => $args['cb'],
				'type'            => 'text',
				'default'         => 3,
				'label'           => __( 'How many numbers to either side of the current pages', 'customify' ),
				'required'        => array( $args['id'] . '_pg_show_paging', '==', '1' ),
			),

		);

		return $config;
	}
}


if ( ! function_exists( 'customify_customizer_blog_posts_config' ) ) {
	function customify_customizer_blog_posts_config( $configs ) {

		$config = array(
			array(
				'name'     => 'blog_panel',
				'type'     => 'panel',
				'priority' => 20,
				'title'    => __( 'Blog', 'customify' ),
			),
		);

		$blog   = customify_customizer_blog_config();
		$config = array_merge( $config, $blog );

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_blog_posts_config' );
