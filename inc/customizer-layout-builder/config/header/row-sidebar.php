<?php

function customify_builder_config_header_row_sidebar(){
    $section  = 'header_sidebar';
    $section_name = __( 'Mobile Sidebar', 'customify' );
    $selector = '#mobile-header-panel-inner';

    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => $section_name,
        ),

        array(
            'name' => $section.'_padding',
            'type' => 'css_ruler',
            'section' => $section,
            'selector' => $selector,
            'css_format' => array(
                'top' => 'padding-top: {{value}};',
                'right' => 'padding-right: {{value}};',
                'bottom' => 'padding-bottom: {{value}};',
                'left' => 'padding-left: {{value}};',
            ),
            'title' => __( 'Padding', 'customify' ),
        ),

        array(
            'name' => $section.'_background',
            'type' => 'group',
            'section' => $section,
            'title' => __( 'Background', 'customify' ),
            'description' => __( 'This is description',  'customify' ),
            'live_title_field' => 'title',
            'field_class' => 'customify-background-control',
            'selector' => '#mobile-header-panel',
            'css_format' => 'background',
            'default' => array(

            ),
            'fields' => array(
                array(
                    'name' => 'color',
                    'type' => 'color',
                    'label' => __( 'Color', 'customify' ),
                    'device_settings' => false,
                ),
                array(
                    'name' => 'image',
                    'type' => 'image',
                    'label' => __( 'Image', 'customify' ),
                ),
                array(
                    'name' => 'cover',
                    'type' => 'checkbox',
                    'required' => array( 'image', 'not_empty', ''),
                    'checkbox_label' => __( 'Background cover', 'customify' ),
                ),
                array(
                    'name' => 'position',
                    'type' => 'select',
                    'label' => __( 'Background Position', 'customify' ),
                    'required' => array( 'image', 'not_empty', ''),
                    'choices' => array(
                        'default'       => __( 'Position', 'customify' ),
                        'center'        => __( 'Center', 'customify' ),
                        'top_left'      => __( 'Top Left', 'customify' ),
                        'top_right'     => __( 'Top Right', 'customify' ),
                        'top_center'    => __( 'Top Center', 'customify' ),
                        'bottom_left'   => __( 'Bottom Left', 'customify' ),
                        'bottom_center' => __( 'Bottom Center', 'customify' ),
                        'bottom_right'  => __( 'Bottom Right', 'customify' ),
                    ),
                ),

                array(
                    'name' => 'repeat',
                    'type' => 'select',
                    'label' => __( 'Background Repeat', 'customify' ),
                    'required' => array(
                        array('image', 'not_empty', ''),
                    ),
                    'choices' => array(
                        'default' => __( 'Repeat', 'customify' ),
                        'no-repeat' => __( 'No-repeat', 'customify' ),
                        'repeat-x' => __( 'Repeat Horizontal', 'customify' ),
                        'repeat-y' => __( 'Repeat Vertical', 'customify' ),
                    ),
                ),

                array(
                    'name' => 'attachment',
                    'type' => 'select',
                    'label' => __( 'Background Attachment', 'customify' ),
                    'required' => array(
                        array('image', 'not_empty', ''),
                        array('cover', '!=', '1' ),
                    ),
                    'choices' => array(
                        'default' => __( 'Attachment', 'customify' ),
                        'scroll' => __( 'Scroll', 'customify' ),
                        'fixed' => __( 'Fixed', 'customify' )
                    ),
                ),

            )
        ),

    );
    return $config;
}
