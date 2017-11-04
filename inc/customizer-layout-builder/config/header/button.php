<?php
function _beacon_builder_config_header_button(){
    $section = 'header_button';
    $prefix = 'header_button_';
    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'title' => __( 'Button', '_beacon' ),
        ),

        array(
            'name' => $prefix.'label',
            'type' => 'text',
            'section' => $section,
            'theme_supports' => '',
            'title'  => __( 'Label', '_beacon' ),
        ),

        array(
            'name' => $prefix.'icon',
            'type' => 'text',
            'section' => $section,
            'theme_supports' => '',
            'title'  => __( 'Icon', '_beacon' ),
        ),

        array(
            'name' => $prefix.'link',
            'type' => 'text',
            'section' => $section,
            'title'  => __( 'Link', '_beacon' ),
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'select',
            'section' => $section,
            'title'  => __( 'Target', '_beacon' ),
            'choices' =>  array(
                'default' => __( 'Current Window', '_beacon' ),
                '_blank' => __( 'New Window', '_beacon' ),
            )
        ),

        array(
            'name' => $prefix.'style',
            'type' => 'select',
            'section' => $section,
            'title'  => __( 'Style', '_beacon' ),
            'choices' =>  array(
                '1' => __( 'Default', '_beacon' ),
                '2' => __( 'Style 2', '_beacon' ),
            )
        ),

        array(
            'name' => $prefix.'color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Color', '_beacon' ),
        ),

        array(
            'name' => $prefix.'color_hover',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Color Hover', '_beacon' ),
        ),

        array(
            'name' => $prefix.'bg_color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Background Color', '_beacon' ),
        ),

        array(
            'name' => $prefix.'bg_color_hover',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Background Color Hover', '_beacon' ),
        ),

        array(
            'name' => $prefix.'padding',
            'type' => 'css_ruler',
            'section' => $section,
            'title'  => __( 'Padding', '_beacon' ),
        ),

    );
    return $config;
}