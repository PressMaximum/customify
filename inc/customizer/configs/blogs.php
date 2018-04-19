<?php
if (!function_exists('customify_customizer_blog_config')) {
    function customify_customizer_blog_config( $args = array() )
    {

        $args = wp_parse_args( $args, array(
            'name' => __('Blog Posts', 'customify'),
            'id' => 'blog_post',
            'selector' => '#blog-posts',
            'cb' => 'customify_blog_posts',
        ) );
        $top_panel = 'blog_panel';
        $level_2_panel = 'panel_'.$args['id'];

        $config = array(
            array(
                'name' => $level_2_panel,
                'type' => 'panel',
                'panel' => $top_panel,
                'title' => $args['name'],
            ),

            array(
                'name' => $level_2_panel.'_layout',
                'type' => 'section',
                'panel' => $level_2_panel,
                'title' => __('Layout', 'customify'),
            ),

            array(
                'name' => $args['id'].'_layout',
                'type'    => 'image_select',
                'section' => $level_2_panel.'_layout',
                'label'   => __( 'Layout', 'customify' ),
                'default' => 'blog_classic',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'choices' => array(
                    'blog_classic' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/blog_classic.svg',
                    ),
                    'blog_column' => array(
                        'img' => get_template_directory_uri() . '/assets/images/customizer/blog_column.svg',
                    ),
                )
            ),
            array(
                'name' => $args['id'].'_excerpt_length',
                'type' => 'text',
                'section' => $level_2_panel.'_layout',
                'default' => '',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'label' => __('Excerpt Length', 'customify'),
            ),
            array(
                'name' => $args['id'].'_excerpt_more',
                'type' => 'text',
                'section' => $level_2_panel.'_layout',
                'default' => '',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'label' => __('Excerpt More', 'customify'),
            ),

            array(
                'name' => $level_2_panel.'_media',
                'type' => 'section',
                'panel' => $level_2_panel,
                'title' => __('Media', 'customify'),
            ),

            array(
                'name' => $args['id'].'_media_ratio',
                'type' => 'slider',
                'section' => $level_2_panel.'_media',
                'label' => __( 'Media Ratio', 'customify' ),
                'selector' => "{$args['selector']} .posts-layout .entry .entry-media",
                'css_format' => 'padding-top: {{value_no_unit}}%;',
                'max' => 200,
                'min' => 0,
            ),
            array(
                'name' => $args['id'].'_media_width',
                'type' => 'slider',
                'section' => $level_2_panel.'_media',
                'label' => __( 'Media Width', 'customify' ),
                'max' => 100,
                'min' => 20,
                'selector' => "{$args['selector']} .posts-layout .entry-media, {$args['selector']} .posts-layout.layout--blog_classic .entry-media",
                'css_format' => 'flex-basis: {{value_no_unit}}%; width: {{value_no_unit}}%;',
            ),

            array(
                'name' => $args['id'].'_media_radius',
                'type' => 'slider',
                'section' => $level_2_panel.'_media',
                'label' => __( 'Media Radius', 'customify' ),
                'max' => 100,
                'min' => 0,

                'selector' => "{$args['selector']} .posts-layout .entry-media",
                'css_format' => 'border-radius: {{value_no_unit}}%;',
            ),

            array(
                'name' => $args['id'].'_thumbnail_size',
                'type' => 'select',
                'section' => $level_2_panel.'_media',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => 'medium',
                'label' => __('Thumbnail Size', 'customify'),
                'choices' => customify_get_all_image_sizes()
            ),
            array(
                'name' => $args['id'].'_hide_thumb_if_empty',
                'type' => 'checkbox',
                'section' => $level_2_panel.'_media',
                'default' => '1',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'checkbox_label' => __('Hide thumbnail when empty.', 'customify'),
            ),

            array(
                'name' => $level_2_panel.'_meta',
                'type' => 'section',
                'panel' => $level_2_panel,
                'title' => __('Meta Settings', 'customify'),
            ),

            array(
                'name' => $args['id'].'_meta_sep',
                'section' => $level_2_panel.'_meta',
                'type' => 'text',
                'default' => _x( '-', 'post meta separator', 'customify' ),
                'label' => __( 'Separator', 'customify' ),
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
            ),

            array(
                'name' => $args['id']. '_meta_config',
                'section' => $level_2_panel.'_meta',
                'type' => 'repeater',
                'description' => __('Drag to order meta items', 'customify'),
                'live_title_field' => 'title',
                'limit' => 4,
                'addable' => false,
                'title_only' => true,
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => array(
	                array(
		                '_key' => 'author',
		                'title' => __('Author', 'customify'),
	                ),
	                array(
		                '_key' => 'date',
		                'title' => __('Date', 'customify'),
	                ),
                    array(
                        '_key' => 'categories',
                        'title' => __('Categories', 'customify'),
                    ),
                    array(
                        '_key' => 'comment',
                        'title' => __('Comment', 'customify'),
                    ),

                ),
                'fields' => array(
                    array(
                        'name' => '_key',
                        'type' => 'hidden',
                    ),
                    array(
                        'name' => 'title',
                        'type' => 'hidden',
                        'label' => __('Title', 'customify'),
                    ),
                )
            ),


            array(
                'name' => $level_2_panel.'_readmore',
                'type' => 'section',
                'panel' => $level_2_panel,
                'title' => __('Read More', 'customify'),
            ),

            array(
                'name' => $args['id'].'_more_display',
                'type' => 'checkbox',
                'default' => 1,
                'section' => $level_2_panel.'_readmore',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'checkbox_label' => __( 'Show Read More', 'customify' ),
            ),

            array(
                'name' => $args['id'].'_more_text',
                'type' => 'text',
                'section' => $level_2_panel.'_readmore',
                'default' => ! is_rtl() ? _x( 'Read More &rarr;', 'readmore LTR', 'customify' ) : _x( 'Read More &larr;', 'readmore RTL' , 'customify' ),
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'label' => __( 'Read More Text', 'customify' ),
                'required' => array($args['id'].'_more_display', '==', '1')
            ),
            array(
                'name' => $args['id'].'_more_typography',
                'type' => 'typography',
                'css_format' => 'typography',
                'section' => $level_2_panel.'_readmore',
                'selector' => "{$args['selector'] } .entry-readmore a",
                'label' => __( 'Typography', 'customify' ),
                'required' => array($args['id'].'_more_display', '==', '1')
            ),

            array(
                'name' => $args['id'].'_more_styling',
                'type' => 'styling',
                'section' => $level_2_panel.'_readmore',
                'selector'    => array(
                    'normal' => "{$args['selector'] } .entry-readmore a",
                    'hover' => "{$args['selector'] } .entry-readmore a:hover",
                    'normal_margin' => "{$args['selector'] } .entry-readmore",
                ),
                'css_format'  => 'styling',
                'label' => __( 'Styling', 'customify' ),
                'fields'     => array(
                    'normal_fields' => array(
                        'link_color' => false, // disable for special field.
                        'bg_image' => false,
                        'bg_cover' => false,
                        'bg_position' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                    ),
                    'hover_fields' => array(
                        'link_color' => false, // disable for special field.
                    )
                ),
                'required' => array($args['id'].'_more_display', '==', '1')
            ),

            array(
                'name' => $level_2_panel.'_pagination',
                'type' => 'section',
                'panel' => $level_2_panel,
                'title' => __('Pagination', 'customify'),
            ),

            array(
                'name' => $args['id'].'_pg_show_paging',
                'section' => $level_2_panel.'_pagination',
                'type' => 'checkbox',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => 1,
                'checkbox_label' => __( 'Show Pagination', 'customify' ),
            ),
            array(
                'name' => $args['id'].'_pg_show_nav',
                'section' => $level_2_panel.'_pagination',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'type' => 'checkbox',
                'default' => 1,
                'checkbox_label' => __( 'Show Next, Previous Label', 'customify' ),
                'required' => array($args['id'].'_pg_show_paging', '==', '1')
            ),
            array(
                'name' => $args['id'].'_pg_prev_text',
                'section' => $level_2_panel.'_pagination',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'type' => 'text',
                'label' => __( 'Preview Label', 'customify' ),
                'required' => array($args['id'].'_pg_show_paging', '==', '1')
            ),
            array(
                'name' => $args['id'].'_pg_next_text',
                'section' => $level_2_panel.'_pagination',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'type' => 'text',
                'label' => __( 'Next Label', 'customify' ),
                'required' => array($args['id'].'_pg_show_paging', '==', '1')
            ),

            array(
                'name' => $args['id'].'_pg_mid_size',
                'section' => $level_2_panel.'_pagination',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'type' => 'text',
                'default' => 3,
                'label' => __( 'How many numbers to either side of the current pages', 'customify' ),
                'required' => array($args['id'].'_pg_show_paging', '==', '1')
            ),

        );

        return $config;
    }
}


if (!function_exists('customify_customizer_blog_posts_config')) {
    function customify_customizer_blog_posts_config($configs)
    {

        $config = array(
            array(
                'name' => 'blog_panel',
                'type' => 'panel',
                'priority' => 20,
                'title' => __('Blog', 'customify'),
            ),
        );

        $blog = customify_customizer_blog_config();
        $archive = customify_customizer_blog_config( array(
            'name' => __('Archive Posts', 'customify'),
            'id' => 'archive_post',
            'selector' => '#archive-posts',
            'cb' => 'customify_archive_posts',
        ) );
        $config = array_merge($config, $blog,$archive );

        return array_merge($configs, $config);
    }
}

add_filter('customify/customizer/config', 'customify_customizer_blog_posts_config');