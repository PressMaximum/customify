<?php
function _beacon_builder_config_header_nav_icon(){
    $section = 'header_nav_icon';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Nav Icon', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_style',
            'type' => 'select',
            'section' => $section,
            'title'          => __( 'Style', '_beacon' ),
            'choices' => array(
                'default' => __( 'Default', '_beacon' ),
                'style_2' => __( 'Style 2', '_beacon' ),
            )
        ),

        array(
            'name' => 'nav_icon',
            'type' => 'icon',
            'section' => $section,
            'title'          => __( 'Icon', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_text',
            'type' => 'text',
            'section' => $section,
            'title'          => __( 'Text', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_show_text',
            'type' => 'checkbox',
            'section' => $section,
            'title'          => __( 'Show Text', '_beacon' ),
            'checkbox_label'         => __( 'Show text', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_size',
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Icon Size', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_padding',
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Icon Padding', '_beacon' ),

        ),

        array(
            'name' => 'nav_icon_item_color',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Color', '_beacon' ),
        ),

        array(
            'name' => 'nav_icon_item_color_hover',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Color Hover', '_beacon' ),
        ),



    );
}