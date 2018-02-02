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

            // Blog Posts Item
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
                'title' => __('Blog Posts Layout', 'customify'),
                'field_class' => 'control--bg bottom-0',
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
                    ),

                ),
            ),

            array(
                'name' => 'blog_post_pagination',
                'type' => 'modal',
                'section' => 'blog_post_layout',
                'title' => __('Pagination', 'customify'),
                'field_class' => 'control--bg control--bg',
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
                            'checkbox_label' => __( 'Show Number', 'customify' ),
                        ),
                        array(
                            'name' => 'preview_label',
                            'type' => 'text',
                            'label' => __( 'Preview Label', 'customify' ),
                        ),
                        array(
                            'name' => 'next_label',
                            'type' => 'text',
                            'label' => __( 'Next Label', 'customify' ),
                        ),


                    ),

                ),
            ),


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
                'name' => 'blog_post_excerpt_length',
                'type' => 'text',
                'section' => 'blog_post_layout',
                'default' => '30',
                'title' => __('Excerpt Length', 'customify'),
                'description' => __('Enter number of words.', 'customify'),
            ),

            array(
                'name' => 'blog_post_thumb_size',
                'type' => 'select',
                'section' => 'blog_post_layout',
                'default' => 'medium',
                'title' => __('Thumbnail Size', 'customify'),
                'choices' => customify_get_all_image_sizes()
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

        );

        return array_merge($configs, $config);
    }
}

add_filter('customify/customizer/config', 'customify_customizer_blog_config');