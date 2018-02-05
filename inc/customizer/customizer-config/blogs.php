<?php
if (!function_exists('customify_customizer_blog_config')) {
    function customify_customizer_blog_config($configs)
    {

        $config = array(

            // Layout panel
            array(
                'name' => 'blog_panel',
                'type' => 'panel',
                'priority' => 22,
                'theme_supports' => '',
                'title' => __('Blog', 'customify'),
            ),

            array(
                'name' => 'blog_post_layout',
                'type' => 'section',
                'panel' => 'blog_panel',
                'theme_supports' => '',
                'title' => __('Blog Post', 'customify'),
            ),

            array(
                'name' => 'blog_post_layout',
                'type' => 'modal',
                'section' => 'blog_post_layout',
                'title' => __('Layout', 'customify'),
                'field_class' => 'control--bg bottom-0',
                'selector' => '#blog-posts',
                'render_callback' => 'customify_blog_posts',
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
                                'blog_classic_rounded' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_classic_rounded.svg',
                                ),
                                'blog_column' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_column.svg',
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
                                'blog_timeline' => array(
                                    'img' => get_template_directory_uri() . '/assets/images/customizer/blog_timeline.svg',
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
                            'name' => 'thumbnail_size',
                            'type' => 'select',
                            'default' => 'medium',
                            'label' => __('Thumbnail Size', 'customify'),
                            'choices' => customify_get_all_image_sizes()
                        ),

                    ), // end fields


                ),
            ),

            array(
                'name' => 'blog_post_pagination',
                'type' => 'modal',
                'section' => 'blog_post_layout',
                'title' => __('Pagination', 'customify'),
                'field_class' => 'control--bg bottom-0',
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'default' => __( 'Default', 'customify' ),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'show_paging',
                            'type' => 'checkbox',
                            'checkbox_label' => __( 'Show Pagination', 'customify' ),
                        ),
                        array(
                            'name' => 'show_number',
                            'type' => 'checkbox',
                            'checkbox_label' => __( 'Show Number', 'customify' ),
                        ),
                        array(
                            'name' => 'show_nav',
                            'type' => 'checkbox',
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

                    ),

                ),
            ),

            array(
                'name' => 'blog_post_styling',
                'type' => 'modal',
                'section' => 'blog_post_layout',
                'title' => __('Styling', 'customify'),
                'field_class' => 'control--bg control--bg',
                'transport' => 'postMessage',
                'selector' => '#blog',
                'css_format' => 'modal',
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'media' => __( 'Media', 'customify' ),
                        'content' => __( 'Content', 'customify' ),
                    ),
                    'media_fields' => array(

                        array(
                            'name' => 'media_ratio',
                            'type' => 'slider',
                            'label' => __( 'Media Ratio', 'customify' ),
                            'selector' => '.posts-layout .entry .entry-media',
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
                            'selector' => '.posts-layout .entry-media, #blog-posts .posts-layout.layout--blog_classic .entry-media',
                            'css_format' => 'flex-basis: {{value_no_unit}}%; width: {{value_no_unit}}%;',
                        ),

                    ),
                    'content_fields' => array(
                        array(
                            'name' => 'media_width',
                            'type' => 'text',
                            'label' => __( 'Media Width', 'customify' ),
                        ),


                    ),

                ),
            ),

            /*
            array(
                'name' => 'blog_post_item',
                'type' => 'repeater',
                'section' => 'blog_post_layout',
                'title' => __('Blog Post Item', 'customify'),
                'description' => __('Drag and Drop to build your post item layout.', 'customify'),
                'live_title_field' => 'title',
                'limit' => 4,
                'addable' => false,
                'title_only' => true,
                'default' => array(
                    array(
                        '_key' => 'title',
                        'title' => __('Title', 'customify'),
                    ),
                    array(
                        '_key' => 'meta',
                        'title' => __('Meta', 'customify'),
                    ),
                    array(
                        '_key' => 'thumbnail',
                        'title' => __('Thumbnail', 'customify'),
                    ),
                    array(
                        '_key' => 'excerpt',
                        'title' => __('Excerpt', 'customify'),
                    ),
                    array(
                        '_key' => 'readmore',
                        'title' => __('Readmore', 'customify'),
                    ),
                    array(
                        '_key' => 'content',
                        'title' => __('Content', 'customify'),
                        '_visibility' => 'hidden'
                    )
                ),
                'fields' => array(
                    array(
                        'name' => '_key',
                        'type' => 'hidden',
                    ),
                    array(
                        'name' => 'title',
                        'type' => 'text',
                    ),
                )
            ),

            array(
                'name' => 'blog_post_meta',
                'type' => 'repeater',
                'section' => 'blog_post_layout',
                'title' => __('Post Meta', 'customify'),
                'description' => __('Drag and Drop to order your post meta.', 'customify'),
                'live_title_field' => 'title',
                'limit' => 4,
                'addable' => false,
                'title_only' => true,
                'default' => array(
                    array(
                        '_key' => 'date',
                        'title' => __('Date', 'customify'),
                    ),
                    array(
                        '_key' => 'author',
                        'title' => __('Author', 'customify'),
                    ),
                    array(
                        '_key' => 'comment',
                        'title' => __('Comment', 'customify'),
                    ),
                    array(
                        '_key' => 'categories',
                        'title' => __('Categories', 'customify'),
                    ),
                    array(
                        '_key' => 'tags',
                        'title' => __('Tags', 'customify'),
                    )
                ),
                'fields' => array(
                    array(
                        'name' => '_key',
                        'type' => 'hidden',
                    ),
                    array(
                        'name' => 'title',
                        'type' => 'text',
                    ),
                )
            ),

            */




        );

        return array_merge($configs, $config);
    }
}

add_filter('customify/customizer/config', 'customify_customizer_blog_config');