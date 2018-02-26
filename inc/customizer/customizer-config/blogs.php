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

                            )
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
            array(
                'name' => $args['id'].'_post_metas',
                'type' => 'modal',
                'section' => $section,
                'title' => __('Meta Settings', 'customify'),
                'field_class' => 'control--bg control--bg bottom-0',
                'selector' => $args['selector'],
                'render_callback' => $args['cb'],
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'default' => __( 'Default', 'customify' ),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'sep',
                            'type' => 'text',
                            'default' => _x( '-', 'post meta separator', 'customify' ),
                            'label' => __( 'Separator', 'customify' ),
                        ),

                        array(
                            'name' => 'items',
                            'type' => 'repeater',
                            'description' => __('Drag to order meta items', 'customify'),
                            'live_title_field' => 'title',
                            'limit' => 4,
                            'addable' => false,
                            'title_only' => true,
                            'default' => array(
                                array(
                                    '_key' => 'categories',
                                    'title' => __('Categories', 'customify'),
                                ),
                                array(
                                    '_key' => 'author',
                                    'title' => __('Author', 'customify'),
                                ),
                                array(
                                    '_key' => 'date',
                                    'title' => __('Date', 'customify'),
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

                    ),
                    'hover_fields' => array(), // end content field

                ),
            ),
            array(
                'name' => $args['id'].'_readmore',
                'type' => 'modal',
                'section' => $section,
                'title' => __('Read More', 'customify'),
                'field_class' => 'control--bg bottom-0',
                'selector' => "{$args['selector'] } .entry-readmore a",
                'render_callback' => $args['cb'],
                'css_format' => 'modal',
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'default' => __( 'Default', 'customify' ),
                        'hover' => __( 'Hover', 'customify' ),
                    ),
                    'default_fields' => array(
                        array(
                            'name' => 'more_text',
                            'type' => 'text',
                            'default' => __( 'Read More &rarr;', 'customify' ),
                            'css_format' => 'html_replace',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'label' => __( 'Read More Text', 'customify' ),
                        ),

                        array(
                            'name' => 'typo_heading',
                            'type' => 'heading',
                            'label' => __( 'Typography', 'customify' ),
                        ),

                        array(
                            'name' => 'font_size',
                            'type' => 'slider',
                            'default' => '',
                            'device_settings' => true,
                            'css_format' => 'font-size: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'label' => __( 'Font Size', 'customify' ),
                            'min' => 9,
                            'max' => 50,
                            'step' => 1
                        ),

                        array(
                            'name' => 'font_weight',
                            'type' => 'select',
                            'default' => '',
                            'css_format' => 'font-weight: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'label' => __( 'Font Weight', 'customify' ),
                            'choices' => array(
                                ''   => __('Default', 'customify'),
                                'normal'    => _x('Normal', 'customify-font-weight', 'customify'),
                                'bold'      => _x('Bold', 'customify-font-weight', 'customify'),
                                '100' => 100,
                                '200' => 200,
                                '300' => 300,
                                '400' => 400,
                                '500' => 500,
                                '600' => 600,
                                '700' => 700,
                                '800' => 800,
                                '900' => 900,
                            )
                        ),

                        array(
                            'name' => 'letter_spacing',
                            'type' => 'slider',
                            'label' => __('Letter Spacing', 'customify'),
                            'min' => -10,
                            'max' => 10,
                            'step' => 0.1,
                            'css_format' => 'letter-spacing: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                        ),
                        array(
                            'name' => 'style',
                            'type' => 'select',
                            'label' => __('Font Style', 'customify'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'font-style: {{value}};',
                            'choices' => array(
                                '' =>__( 'Default', 'customify' ),
                                'normal' =>__( 'Normal', 'customify' ),
                                'italic' =>__( 'Italic', 'customify' ),
                                'oblique' =>__( 'Oblique', 'customify' ),
                            )
                        ),
                        array(
                            'name' => 'text_decoration',
                            'type' => 'select',
                            'label' => __('Text Decoration', 'customify'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'text-decoration: {{value}};',
                            'choices' => array(
                                '' =>__( 'Default', 'customify' ),
                                'underline' =>__( 'Underline', 'customify' ),
                                'overline' =>__( 'Overline', 'customify' ),
                                'line-through' =>__( 'Line through', 'customify' ),
                                'none' =>__( 'None', 'customify' ),
                            )
                        ),
                        array(
                            'name' => 'text_transform',
                            'type' => 'select',
                            'label' => __('Text Transform', 'customify'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'text-transform: {{value}};',
                            'choices' => array(
                                '' =>__( 'Default', 'customify' ),
                                'uppercase' =>__( 'Uppercase', 'customify' ),
                                'lowercase' =>__( 'Lowercase', 'customify' ),
                                'capitalize' =>__( 'Capitalize', 'customify' ),
                                'none' =>__( 'None', 'customify' ),
                            )
                        ),

                        array(
                            'name' => 'color_heading',
                            'type' => 'heading',
                            'label' => __( 'Color', 'customify' ),
                        ),

                        array(
                            'name' => 'color',
                            'type' => 'color',
                            'default' => '',
                            'css_format' => 'color: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'label' => __( 'Color', 'customify' ),
                        ),

                        array(
                            'name' => 'bg_color',
                            'type' => 'color',
                            'default' => '',
                            'css_format' => 'background-color: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'label' => __( 'Background Color', 'customify' ),
                        ),

                        array(
                            'name' => 'border_heading',
                            'type' => 'heading',
                            'label' => __( 'Border', 'customify' ),
                        ),

                        array(
                            'name' => 'border_style',
                            'type' => 'select',
                            'class' => 'clear',
                            'label' => __('Border Style', 'customify'),
                            'default' => 'none',
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'choices' => array(
                                ''          => __('Default', 'customify'),
                                'none'      => __('None', 'customify'),
                                'solid'     => __('Solid', 'customify'),
                                'dotted'    => __('Dotted', 'customify'),
                                'dashed'    => __('Dashed', 'customify'),
                                'double'    => __('Double', 'customify'),
                                'ridge'     => __('Ridge', 'customify'),
                                'inset'     => __('Inset', 'customify'),
                                'outset'    => __('Outset', 'customify'),
                            ),
                            'css_format' => 'border-style: {{value}};',
                        ),

                        array(
                            'name' => 'border_width',
                            'type' => 'css_ruler',
                            'label' => __('Border Width', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => array(
                                'top' => 'border-top-width: {{value}};',
                                'right' => 'border-right-width: {{value}};',
                                'bottom'=> 'border-bottom-width: {{value}};',
                                'left'=> 'border-left-width: {{value}};'
                            ),
                        ),
                        array(
                            'name' => 'border_color',
                            'type' => 'color',
                            'label' => __('Border Color', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'border-color: {{value}};',
                        ),

                        array(
                            'name' => 'border_radius',
                            'type' => 'slider',
                            'label' => __('Border Radius', 'customify'),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'border-radius: {{value}};',
                        ),

                        array(
                            'name' => 'box_shadow',
                            'type' => 'shadow',
                            'label' =>  __( 'Box Shadow', 'customify' ),
                            'selector' => "{$args['selector'] } .entry-readmore a",
                            'css_format' => 'box-shadow: {{value}};',
                        ),

                        array(
                            'name' => 'layout_heading',
                            'type' => 'heading',
                            'label' => __( 'Layout', 'customify' ),
                        ),

                        array(
                            'name' => 'padding',
                            'type' => 'css_ruler',
                            'default' => '',
                            'device_settings' => true,
                            'css_format'      => array(
                                'top'    => 'padding-top: {{value}};',
                                'right'  => 'padding-right: {{value}};',
                                'bottom' => 'padding-bottom: {{value}};',
                                'left'   => 'padding-left: {{value}};',
                            ),
                            'selector' => "{$args['selector'] } .entry-readmore a.button",
                            'label' => __( 'Padding', 'customify' ),
                        ),

                        array(
                            'name' => 'margin',
                            'type' => 'css_ruler',
                            'default' => '',
                            'device_settings' => true,
                            'css_format'      => array(
                                'top'    => 'margin-top: {{value}};',
                                'right'  => 'margin-right: {{value}};',
                                'bottom' => 'margin-bottom: {{value}};',
                                'left'   => 'margin-left: {{value}};',
                            ),
                            'selector' => "{$args['selector'] } .entry-readmore",
                            'label' => __( 'Margin', 'customify' ),
                        ),

                    ),

                    'hover_fields' => array(

                        array(
                            'name' => 'color_heading',
                            'type' => 'heading',
                            'label' => __( 'Color', 'customify' ),
                        ),

                        array(
                            'name' => 'color',
                            'type' => 'color',
                            'default' => '',
                            'css_format' => 'color: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'label' => __( 'Color', 'customify' ),
                        ),

                        array(
                            'name' => 'bg_color',
                            'type' => 'color',
                            'default' => '',
                            'css_format' => 'background-color: {{value}};',
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'label' => __( 'Background Color', 'customify' ),
                        ),

                        array(
                            'name' => 'border_heading',
                            'type' => 'heading',
                            'label' => __( 'Border', 'customify' ),
                        ),

                        array(
                            'name' => 'border_style',
                            'type' => 'select',
                            'class' => 'clear',
                            'label' => __('Border Style', 'customify'),
                            'default' => 'none',
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'choices' => array(
                                ''          => __('Default', 'customify'),
                                'none'      => __('None', 'customify'),
                                'solid'     => __('Solid', 'customify'),
                                'dotted'    => __('Dotted', 'customify'),
                                'dashed'    => __('Dashed', 'customify'),
                                'double'    => __('Double', 'customify'),
                                'ridge'     => __('Ridge', 'customify'),
                                'inset'     => __('Inset', 'customify'),
                                'outset'    => __('Outset', 'customify'),
                            ),
                            'css_format' => 'border-style: {{value}};',
                        ),

                        array(
                            'name' => 'border_width',
                            'type' => 'css_ruler',
                            'label' => __('Border Width', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'css_format' => array(
                                'top' => 'border-top-width: {{value}};',
                                'right' => 'border-right-width: {{value}};',
                                'bottom'=> 'border-bottom-width: {{value}};',
                                'left'=> 'border-left-width: {{value}};'
                            ),
                        ),
                        array(
                            'name' => 'border_color',
                            'type' => 'color',
                            'label' => __('Border Color', 'customify'),
                            'required' => array('border_style', '!=', 'none'),
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'css_format' => 'border-color: {{value}};',
                        ),

                        array(
                            'name' => 'border_radius',
                            'type' => 'slider',
                            'label' => __('Border Radius', 'customify'),
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'css_format' => 'border-radius: {{value}};',
                        ),

                        array(
                            'name' => 'box_shadow',
                            'type' => 'shadow',
                            'label' =>  __( 'Box Shadow', 'customify' ),
                            'selector' => "{$args['selector'] } .entry-readmore a:hover",
                            'css_format' => 'box-shadow: {{value}};',
                        ),


                    ),

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
                            'required' => array('show_paging', '==', '1')
                        ),
                        array(
                            'name' => 'show_nav',
                            'type' => 'checkbox',
                            'default' => 1,
                            'checkbox_label' => __( 'Show Next, Previous Label', 'customify' ),
                            'required' => array('show_paging', '==', '1')
                        ),
                        array(
                            'name' => 'prev_text',
                            'type' => 'text',
                            'label' => __( 'Preview Label', 'customify' ),
                            'required' => array('show_paging', '==', '1')
                        ),
                        array(
                            'name' => 'next_text',
                            'type' => 'text',
                            'label' => __( 'Next Label', 'customify' ),
                            'required' => array('show_paging', '==', '1')
                        ),

                        array(
                            'name' => 'mid_size',
                            'type' => 'text',
                            'default' => 3,
                            'label' => __( 'How many numbers to either side of the current pages', 'customify' ),
                            'required' => array('show_paging', '==', '1')
                        ),

                    ),

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