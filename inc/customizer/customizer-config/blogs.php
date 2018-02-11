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

        $section = $args['id'].'_layout';

        $config = array(

            array(
                'name' => $args['id'].'_layout',
                'type' => 'section',
                'panel' => 'blog_panel',
                'title' => $args['name'],
            ),

            array(
                'name' => $args['id'].'_layout',
                'type' => 'modal',
                'section' => $section,
                'title' => __('Layout', 'customify'),
                'field_class' => 'control--bg bottom-0',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'default' => __( 'Default', 'customify' ),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'layout',
                            'type'    => 'radio',
                            'label'   => __( 'Layout', 'customify' ),
                            'default' => 'blog_classic',
                            'class' => 'custom-control-image_select',
                            'choices' => array(
                                'blog_classic' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_classic.svg',
                                ),
                                'blog_2column' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_2column.svg',
                                ),
                                'blog_lateral' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_lateral.svg',
                                    //'label' => 'Pro only'
                                ),
                                'blog_boxed' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_boxed.svg',
                                ),
                                'blog_masonry' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_masonry.svg',
                                ),
                            )
                        ),

                        array(
                            'name' => 'columns',
                            'type' => 'select',
                            'default' => '',
                            'label' => __('Columns', 'customify'),
                            'choices' => array(
                                '0' => __('Default', 'customify'),
                                '1' => __('1 Column', 'customify'),
                                '2' => __('2 Columns', 'customify'),
                                '3' => __('3 Columns', 'customify'),
                                '4' => __('4 Columns', 'customify'),
                                '5' => __('5 Columns', 'customify'),
                                '6' => __('6 Columns', 'customify'),
                            ),
                            'required' => array(
                                array('layout', '!=', 'blog_classic' ),
                                array('layout', '!=', 'blog_timeline' ),
                                array('layout', '!=', 'blog_lateral' ),
                            ),
                        ),

                        array(
                            'name' => 'excerpt_length',
                            'type' => 'text',
                            'default' => '',
                            'label' => __('Excerpt Length', 'customify'),
                        ),

                        array(
                            'name' => 'excerpt_more',
                            'type' => 'text',
                            'default' => '',
                            'label' => __('Excerpt More', 'customify'),
                        ),

                        array(
                            'name' => 'more_text',
                            'type' => 'text',
                            'default' => '',
                            'label' => __('More Text', 'customify'),
                        ),

                        array(
                            'name' => 'thumbnail_size',
                            'type' => 'select',
                            'default' => 'medium',
                            'label' => __('Thumbnail Size', 'customify'),
                            'choices' => customify_get_all_image_sizes()
                        ),
                        array(
                            'name' => 'hide_thumb_if_empty',
                            'type' => 'checkbox',
                            'default' => '1',
                            'checkbox_label' => __('Hide thumbnail when empty.', 'customify'),
                        ),

                    ), // end fields


                ),
            ),

            array(
                'name' => $args['id'].'_pagination',
                'type' => 'modal',
                'section' => $section,
                'title' => __('Pagination', 'customify'),
                'field_class' => 'control--bg bottom-0',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'default' => __( 'Default', 'customify' ),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'show_paging',
                            'type' => 'checkbox',
                            'default' => 1,
                            'checkbox_label' => __( 'Show Pagination', 'customify' ),
                        ),
                        array(
                            'name' => 'show_number',
                            'type' => 'checkbox',
                            'default' => 1,
                            'checkbox_label' => __( 'Show Number', 'customify' ),
                        ),
                        array(
                            'name' => 'show_nav',
                            'type' => 'checkbox',
                            'default' => 1,
                            'checkbox_label' => __( 'Show Next, Previous Label', 'customify' ),
                        ),
                        array(
                            'name' => 'prev_text',
                            'type' => 'text',
                            'label' => __( 'Preview Label', 'customify' ),
                        ),
                        array(
                            'name' => 'next_text',
                            'type' => 'text',
                            'label' => __( 'Next Label', 'customify' ),
                        ),

                        array(
                            'name' => 'mid_size',
                            'type' => 'text',
                            'default' => 3,
                            'label' => __( 'How many numbers to either side of the current pages', 'customify' ),
                        ),

                    ),

                ),
            ),

            array(
                'name' => $args['id'].'_media_styling',
                'type' => 'modal',
                'section' => $section,
                'title' => __('Media Styling', 'customify'),
                'field_class' => 'control--bg control--bg bottom-0',
                'transport' => 'postMessage',
                'selector' => $args['selector'],
                'css_format' => 'modal',
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'normal' => __( 'Normal', 'customify' ),
                    ),
                    'normal_fields' => array(
                        array(
                            'name' => 'media_ratio',
                            'type' => 'slider',
                            'label' => __( 'Media Ratio', 'customify' ),
                            'selector' => "{$args['selector']} .posts-layout .entry .entry-media",
                            'css_format' => 'padding-top: {{value_no_unit}}%;',
                            'max' => 200,
                            'min' => 0,
                        ),
                        array(
                            'name' => 'media_width',
                            'type' => 'slider',
                            'label' => __( 'Media Width', 'customify' ),
                            'max' => 100,
                            'min' => 20,
                            'selector' => "{$args['selector']} .posts-layout .entry-media, {$args['selector']} .posts-layout.layout--blog_classic .entry-media",
                            'css_format' => 'flex-basis: {{value_no_unit}}%; width: {{value_no_unit}}%;',
                        ),

                        array(
                            'name' => 'media_radius',
                            'type' => 'slider',
                            'label' => __( 'Media Radius', 'customify' ),
                            'max' => 100,
                            'min' => 0,
                            'selector' => "{$args['selector']} .posts-layout .entry-media",
                            'css_format' => 'border-radius: {{value_no_unit}}%;',
                        ),

                    ),
                    'hover_fields' => array(
                    ), // end content field

                ),
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
                'priority' => 22,
                'theme_supports' => '',
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