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