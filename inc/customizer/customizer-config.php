<?php
if (!function_exists('customify_customizer_config')) {
    function customify_customizer_config($configs)
    {

        $config = array(
            array(
                'name' => 'customify_panel',
                'type' => 'panel',
                'theme_supports' => '',
                'title' => __('Customify Panel', 'customify'),
            ),

            array(
                'name' => 'customify_section',
                'type' => 'section',
                'panel' => 'customify_panel',
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Customify Section', 'customify'),
                'description' => __('This is section description', 'customify'),
            ),

            array(
                'name' => '_modal',
                'type' => 'modal',
                'section' => 'customify_section',
                'title' => __('Modal', 'customify'),
                'description' => __('This is description', 'customify'),
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'tab_1' => __('Tab 1', 'customify'),
                        'tab_2' => __('Tab 2', 'customify'),
                        'tab_3' => __('Tab 3', 'customify'),
                    ),
                    'tab_1_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    ),
                    'tab_2_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text 2 Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    ),

                    'tab_3_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text 3 Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    )



                ),
            ),


            array(
                'name' => '_modal_2',
                'type' => 'modal',
                'section' => 'customify_section',
                'title' => __('Modal 2', 'customify'),
                'description' => __('This is description', 'customify'),
                'default' => array(),
                'fields' => array(
                    'tabs' => array(
                        'tab_a' => __('A 1', 'customify'),
                        'tab_b' => __('B 2', 'customify'),
                        'tab_c' => __('C 3', 'customify'),
                    ),
                    'tab_a_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    ),
                    'tab_b_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text 2 Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    ),

                    'tab_c_fields' => array(
                        array(
                            'name' => 'text_color',
                            'type' => 'color',
                            'label' => __( 'Text 3 Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                        array(
                            'name' => 'link_color',
                            'type' => 'color',
                            'label' => __( 'Link Color', 'customify' ),
                            'css_format' => 'color: {{value}}; text-decoration-color: {{value}};'
                        ),
                    )



                ),
            ),


            array(
                'name' => 'typography',
                'type' => 'typography',
                'section' => 'customify_section',
                'title' => __('Typography', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => '#page',
                'css_format' => 'typography',
                'default' => array(),
            ),

            array(
                'name' => 'typography_h',
                'type' => 'typography',
                'section' => 'customify_section',
                'title' => __('Typography Heading', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h1, h2, h3, h4, h5, h6',
                'css_format' => 'typography',
                'default' => array(),
            ),

            array(
                'name' => 'styling_new',
                'type' => 'styling',
                'section' => 'customify_section',
                'title' => __('Styling New', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => array(
                    'normal' => '.site-content',
                    'hover' => '.site-content:hover',
                    'normal_link_color' => '.site-content a', // status_{field_name} for special selector
                    'hover_link_color' => '.site-content a:hover', // status_{field_name} for special selector
                ), // Global selector
                'css_format' => 'styling',
                'default' => array(
                    'normal' => array(),
                    'hover' => array(),
                ),
                //'fields' => array()
            ),

            array(
                'name' => 'styling_normal_only',
                'type' => 'styling',
                'section' => 'customify_section',
                'title' => __('Styling Normal', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h1, h2, h3, h4, h5, h6', // Global selector
                'css_format' => 'styling',
                'default' => array(
                    'normal' => array(),
                    'hover' => array(),
                ),
                'fields' => array(
                    'hover_fields' => false, // disable hover tab and all fields inside.
                    'normal_fields' => array(
                        'link_color' => false // disable for special field.
                    )
                )
            ),

            /*
            array(
                'name' => 'repeater',
                'type' => 'repeater',
                'section' => 'customify_section',
                'title' => __('Repeater Title Only', 'customify'),
                'description' => __('This is description', 'customify'),
                'live_title_field' => 'title',
                'limit' => 4,
                'addable' => false,
                'title_only' => true,
                'default' => array(
                    array(
                        '_key' => 'content_1',
                        'title' => __('Title 1', 'customify'),
                    ),
                    array(
                        '_key' => 'content_2',
                        'title' => __('Title 2', 'customify'),
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
                        'label' => __('Title', 'customify'),
                    ),
                )
            ),

            array(
                'name' => '__heading_1',
                'type' => 'heading',
                'section' => 'customify_section',
                'label' => __('This is heading', 'customify'),
            ),


            array(
                'name' => 'repeater_default',
                'type' => 'repeater',
                'section' => 'customify_section',
                'title' => __('Repeater', 'customify'),
                'description' => __('This is description', 'customify'),
                'live_title_field' => 'title',
                'limit' => 4,
                'limit_msg' => __('Just limit 4 item, Ability HTML here', 'customify'),
                'default' => array(
                    array(
                        'title' => __('Title 1', 'customify'),
                        'content' => __('Content 1', 'customify'),
                    ),
                    array(
                        'title' => __('Title 2', 'customify'),
                        'content' => __('Content 2', 'customify'),
                    )
                ),
                'fields' => array(
                    array(
                        'name' => 'title',
                        'type' => 'text',
                        'label' => __('Title', 'customify'),
                    ),
                    array(
                        'name' => 'slider',
                        'type' => 'slider',
                        'device_settings' => true,
                        'label' => __('Slider', 'customify'),
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __('Image', 'customify'),
                    ),
                    array(
                        'name' => 'select',
                        'type' => 'select',
                        'label' => __('Select', 'customify'),
                        'description' => __('Select 2 to show text area', 'customify'),
                        'choices' => array(
                            1 => 1,
                            2 => 2,
                            3 => 3
                        )
                    ),
                    array(
                        'name' => 'content',
                        'type' => 'textarea',
                        'label' => __('Textarea', 'customify'),
                        'required' => array('select', '==', '2')
                    ),
                )
            ),

            array(
                'name' => 'css_ruler_disable',
                'device_settings' => true,
                'type' => 'css_ruler',
                'default' => null,
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                'theme_supports' => '',
                'title' => __('CSS Ruler', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => '.site-content',
                'fields_disabled' => array(
                    'left' => '', // custom text  default: Auto
                    'right' => '',
                ),
                'css_format' => array(
                    'top' => 'padding-top: {{value}}',
                    //'right' => 'padding-right: {{value}}',
                    'bottom' => 'padding-bottom: {{value}}',
                    //'left' => 'padding-left: {{value}}',
                )
            ),

            array(
                'name' => 'slider',
                'type' => 'slider',
                'device_settings' => false,
                'default' => 20,
                'min' => -1,
                'max' => 10,
                'step' => .1,
                'section' => 'customify_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Slider', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h4',
                'css_format' => array(
                    'desktop' => 'font-size: {{value}}',
                    'tablet' => 'font-size: {{value}}',
                    'mobile' => 'font-size: {{value}}',
                )
            ),

            array(
                'name' => 'slider_devices',
                'type' => 'slider',
                'device_settings' => true,
                'default' => array(
                    'desktop' => 40,
                    'tablet' => 30,
                    'mobile' => 20,
                ),
                'min' => -10,
                'max' => 100,
                'section' => 'customify_section',
                //'device' => 'mobile', // mobile || general
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Slider Multiple Devices', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h4',
                'css_format' => 'font-size: {{value}}'
            ),

            array(
                'name' => 'radio_group',
                'type' => 'radio_group',
                'css_format' => 'html_class',
                'selector' => 'body',
                'device_settings' => false,
                'default' => 'c-2',
                'section' => 'customify_section',
                'title' => __('Radio Group', 'customify'),
                'description' => __('This is description', 'customify'),
                'choices' => array(
                    'c-1' => __('One', 'customify'),
                    'c-2' => __('Two', 'customify'),
                    'c-3' => __('Three', 'customify'),
                    'c-4' => __('4', 'customify'),
                    'c-5' => __('Five', 'customify'),
                )
            ),

            array(
                'name' => 'image_select',
                'type' => 'image_select',
                'device_settings' => true,
                'default' => '3',
                'section' => 'customify_section',
                'title' => __('Image Select', 'customify'),
                'description' => __('This is description', 'customify'),
                'choices' => array(
                    '1' => array(
                        'img' => get_template_directory_uri().'/assets/images/customizer/021_layout_wireframe_grid_chat_messages_conversation_forum-128.png',
                        //'label' => 'Option_1'
                    ),
                    '2' => array(
                        'img' => get_template_directory_uri().'/assets/images/customizer/021_layout_wireframe_grid_posts_list-128.png',
                        //'label' => 'Option_2'
                    ),
                    '3' => array(
                        'img' => get_template_directory_uri().'/assets/images/customizer/021_layout_wireframe_grid_list_checkboxes-128.png',
                        //'label' => 'Option_3'
                    ),
                    '4' => array(
                        'img' => get_template_directory_uri().'/assets/images/customizer/021_layout_wireframe_grid_wizard_step_steps-128.png',
                        //'label' => 'Option_4'
                    ),
                )
            ),



            array(
                'name' => 'typography_h1',
                'type' => 'group',
                'section' => 'customify_section',
                'title' => __('Typography H1', 'customify'),
                'description' => __('This is description', 'customify'),
                'field_class' => 'customify-typography-control',
                'selector' => '#page h1',
                'css_format' => 'typography',
                'default' => array(),
                'fields' => array(

                    array(
                        'name' => 'font',
                        'type' => 'font',
                        'label' => __('Font', 'customify'),
                    ),

                    array(
                        'name' => 'font_style',
                        'type' => 'font_style',
                        'label' => __('Font Style', 'customify'),
                    ),

                    array(
                        'name' => 'font_size',
                        'type' => 'slider',
                        'label' => __('Font Size', 'customify'),
                        'device_settings' => true,
                    ),

                    array(
                        'name' => 'line_height',
                        'type' => 'slider',
                        'label' => __('Line Height', 'customify'),
                        'device_settings' => true,
                    ),

                    array(
                        'name' => 'letter_spacing',
                        'type' => 'slider',
                        'label' => __('Letter Spacing', 'customify'),
                        'min' => -10,
                        'max' => 10,
                    ),

                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __('Color', 'customify'),
                    ),

                )
            ),

            array(
                'name' => 'background',
                'type' => 'group',
                'section' => 'customify_section',
                'title' => __('Background', 'customify'),
                'description' => __('This is description', 'customify'),
                'live_title_field' => 'title',
                'field_class' => 'customify-background-control',
                'selector' => '#page',
                'css_format' => 'styling',
                'device_settings' => true,
                'default' => array(),
                'fields' => array(
                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __('Background Color', 'customify'),
                        'device_settings' => true,
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __('Background Image', 'customify'),
                    ),
                    array(
                        'name' => 'cover',
                        'type' => 'checkbox',
                        'required' => array('image', 'not_empty', ''),
                        'label' => __('Background cover', 'customify'),
                        'checkbox_label' => __('Background cover', 'customify'),
                    ),
                    array(
                        'name' => 'position',
                        'type' => 'select',
                        'label' => __('Background Position', 'customify'),
                        'required' => array('image', 'not_empty', ''),
                        'choices' => array(
                            'default' => __('Position', 'customify'),
                            'center' => __('Center', 'customify'),
                            'top_left' => __('Top Left', 'customify'),
                            'top_right' => __('Top Right', 'customify'),
                            'top_center' => __('Top Center', 'customify'),
                            'bottom_left' => __('Bottom Left', 'customify'),
                            'bottom_center' => __('Bottom Center', 'customify'),
                            'bottom_right' => __('Bottom Right', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'repeat',
                        'type' => 'select',
                        'label' => __('Background Repeat', 'customify'),
                        'required' => array(
                            array('image', 'not_empty', ''),
                            // array('style', '!=', 'cover' ),
                        ),
                        'choices' => array(
                            'default' => __('Repeat', 'customify'),
                            'no-repeat' => __('No-repeat', 'customify'),
                            'repeat-x' => __('Repeat Horizontal', 'customify'),
                            'repeat-y' => __('Repeat Vertical', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'attachment',
                        'type' => 'select',
                        'label' => __('Background Attachment', 'customify'),
                        'required' => array(
                            array('image', 'not_empty', ''),
                            array('cover', '!=', '1'),
                        ),
                        'choices' => array(
                            'default' => __('Attachment', 'customify'),
                            'scroll' => __('Scroll', 'customify'),
                            'fixed' => __('Fixed', 'customify')
                        ),
                    ),

                    array(
                        'name' => 'border_width',
                        'type' => 'css_ruler',
                        'label' => __('Border Width', 'customify'),
                    ),

                    array(
                        'name' => 'border_color',
                        'type' => 'color',
                        'label' => __('Border Color', 'customify'),
                    ),
                    array(
                        'name' => 'border_style',
                        'type' => 'select',
                        'label' => __('Border Style', 'customify'),
                        'default' => 'solid',
                        'choices' => array(
                            'solid'     => __('Solid', 'customify'),
                            'dotted'    => __('Dotted', 'customify'),
                            'dashed'    => __('Dashed', 'customify'),
                            'double'    => __('Double', 'customify'),
                            'ridge'     => __('Ridge', 'customify'),
                            'inset'     => __('Inset', 'customify'),
                            'outset'    => __('Outset', 'customify'),
                            'none'      => __('None', 'customify'),
                        ),
                    ),



                )
            ),

            array(
                'name' => 'background_p',
                'type' => 'group',
                'section' => 'customify_section',
                'title' => __('Background P', 'customify'),
                'description' => __('This is description', 'customify'),
                'live_title_field' => 'title',
                'field_class' => 'customify-background-control',
                'selector' => '#page p',
                'css_format' => 'styling',
                'device_settings' => false,
                'default' => array(),
                'fields' => array(
                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __('Background Color', 'customify'),
                        'device_settings' => true,
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __('Background Image', 'customify'),
                    ),
                    array(
                        'name' => 'cover',
                        'type' => 'checkbox',
                        'required' => array('image', 'not_empty', ''),
                        'label' => __('Background cover', 'customify'),
                        'checkbox_label' => __('Background cover', 'customify'),
                    ),
                    array(
                        'name' => 'position',
                        'type' => 'select',
                        'label' => __('Background Position', 'customify'),
                        'required' => array('image', 'not_empty', ''),
                        'choices' => array(
                            'default' => __('Default', 'customify'),
                            'center' => __('Center', 'customify'),
                            'top_left' => __('Top Left', 'customify'),
                            'top_right' => __('Top Right', 'customify'),
                            'top_center' => __('Top Center', 'customify'),
                            'bottom_left' => __('Bottom Left', 'customify'),
                            'bottom_center' => __('Bottom Center', 'customify'),
                            'bottom_right' => __('Bottom Right', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'repeat',
                        'type' => 'select',
                        'label' => __('Background Repeat', 'customify'),
                        'required' => array(
                            array('image', 'not_empty', ''),
                            // array('style', '!=', 'cover' ),
                        ),
                        'choices' => array(
                            'default' => __('Default', 'customify'),
                            'no-repeat' => __('No-repeat', 'customify'),
                            'repeat-x' => __('Repeat Horizontal', 'customify'),
                            'repeat-y' => __('Repeat Vertical', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'attachment',
                        'type' => 'select',
                        'label' => __('Background Attachment', 'customify'),
                        'required' => array(
                            array('image', 'not_empty', ''),
                            array('cover', '!=', '1'),
                        ),
                        'choices' => array(
                            'default' => __('Default', 'customify'),
                            'scroll' => __('Scroll', 'customify'),
                            'fixed' => __('Fixed', 'customify')
                        ),
                    ),

                )
            ),

            array(
                'name' => 'select',
                'type' => 'select',
                //'device_settings' => true,
                'default' => '',
                'section' => 'customify_section',
                //'priority' => 22,
                'title' => __('Select', 'customify'),
                'description' => __('Select 2 to show Dependence field', 'customify'),
                'choices' => array(
                    '1' => __('One', 'customify'),
                    '2' => __('Two', 'customify'),
                    '3' => __('Three', 'customify'),
                )
            ),

            array(
                'name' => 'dependence_text',
                'type' => 'text',
                'device_settings' => true,
                'default' => null,
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Dependence Text Field', 'customify'),
                'description' => __('Show only select=2', 'customify'),
                'required' => array('select', '==', '2')
            ),


            array(
                'name' => 'text',
                'type' => 'text',
                //'device_settings' => true,
                //'sanitize_callback' => '',
                'default' => null,
                'section' => 'customify_section',
                //'priority' => 22,
                'theme_supports' => '',
                'device' => 'mobile',
                'title' => __('Text', 'customify'),
                'description' => __('This is description', 'customify'),


                'selector' => '._test_text1',
                'render_callback' => ''
            ),

            array(
                'name' => 'text2',
                'type' => 'text',
                'device_settings' => true,
                'sanitize_callback' => '',
                'default' => null,
                'section' => 'customify_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Text 2 Inside the Text', 'customify'),
                'description' => __('This is description', 'customify'),

                //'selector' => '._test_text_2',
                //'render_callback' => '_test_2_render_callback'
            ),


            array(
                'name' => 'css_ruler',
                'device_settings' => true,
                'type' => 'css_ruler',
                'default' => null,
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                'theme_supports' => '',
                'title' => __('CSS Ruler', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h4',
                'css_format' => array(
                    'top' => 'padding-top: {{value}}',
                    'right' => 'padding-right: {{value}}',
                    'bottom' => 'padding-bottom: {{value}}',
                    'left' => 'padding-left: {{value}}',
                )
            ),

            array(
                'name' => 'icon',
                'type' => 'icon',
                'device_settings' => true,
                'default' => '',
                'section' => 'customify_section',
                'theme_supports' => '',
                'title' => __('Icon', 'customify'),
                'description' => __('This is description', 'customify'),
            ),

            array(
                'name' => 'textarea',
                'type' => 'textarea',
                'device_settings' => true,
                'default' => '',
                'section' => 'customify_section',
                //'device' => 'mobile', // mobile || general
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Textarea', 'customify'),
                'description' => __('This is description', 'customify'),
            ),

            array(
                'name' => 'checkbox',
                'type' => 'checkbox',
                'device_settings' => true,
                'label' => __('Checkbox', 'customify'),
                'section' => 'customify_section',
                'description' => __('This is description', 'customify'),
                'setting_type' => 'checkbox',
                'checkbox_label' => __('This is checkbox label', 'customify'),
            ),

            array(
                'name' => 'radio',
                'type' => 'radio',
                'device_settings' => true,
                'default' => '',
                'section' => 'customify_section',
                'title' => __('Radio', 'customify'),
                'description' => __('This is description', 'customify'),
                'choices' => array(
                    '1' => __('One', 'customify'),
                    '2' => __('Two', 'customify'),
                    '3' => __('Three', 'customify'),
                )
            ),


            array(
                'name' => 'color',
                'device_settings' => true,
                'type' => 'color',
                'default' => '#f5f5f5',
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                'theme_supports' => '',
                'title' => __('Color', 'customify'),
                'description' => __('This is description', 'customify'),
                'selector' => 'h42',
                'css_format' => 'color: {{value}}'
            ),

            array(
                'name' => 'multiple_dependence_text',
                'type' => 'text',
                'device_settings' => true,
                'default' => null,
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title' => __('Multiple Dependence Text Field', 'customify'),
                'description' => __('Show only select=3 and radio=2', 'customify'),
                'required' => array(
                    array('select', '==', '3'),
                    array('radio', '==', '2')
                )
            ),



            array(
                'name' => 'group',
                'type' => 'group',
                'section' => 'customify_section',
                //'priority' => 22,
                'title' => __('Group', 'customify'),
                'description' => __('This is description', 'customify'),
                'live_title_field' => 'title',
                'default' => array(
                    'title' => __('Title 1', 'customify'),
                    'content' => __('Content 1', 'customify'),
                ),
                'fields' => array(
                    array(
                        'name' => 'title',
                        'type' => 'text',
                        'device_settings' => true,
                        'label' => __('Title', 'customify'),
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __('Image', 'customify'),
                    ),
                    array(
                        'name' => 'select',
                        'type' => 'select',
                        'label' => __('Select', 'customify'),
                        'description' => __('Select 2 to show text area', 'customify'),
                        'choices' => array(
                            1 => 1,
                            2 => 2,
                            3 => 3
                        )
                    ),
                    array(
                        'name' => 'content',
                        'type' => 'textarea',
                        'label' => __('Textarea', 'customify'),
                        'required' => array('select', '==', '2')
                    ),


                )
            ),

            array(
                'name' => 'text_align',
                'type' => 'text_align',
                'device_settings' => true,
                'default' => null,
                'transport' => 'postMessage', // or refresh
                'section' => 'customify_section',
                'theme_supports' => '',
                'title' => __('Text Align', 'customify'),
            ),

            */



        );

        return array_merge($configs, $config);
    }
}

add_filter('customify/customizer/config', 'customify_customizer_config');