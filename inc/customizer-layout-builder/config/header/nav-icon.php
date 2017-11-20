<?php
function customify_builder_config_header_nav_icon(){
    $section = 'header_nav_icon';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Nav Icon', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_style',
            'type' => 'select',
            'section' => $section,
            'title'          => __( 'Style', 'customify' ),
            'choices' => array(
                'default' => __( 'Default', 'customify' ),
                'style_2' => __( 'Style 2', 'customify' ),
            )
        ),

        array(
            'name' => 'nav_icon',
            'type' => 'icon',
            'section' => $section,
            'title'          => __( 'Icon', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_text',
            'type' => 'text',
            'section' => $section,
            'title'          => __( 'Text', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_show_text',
            'type' => 'checkbox',
            'section' => $section,
            'title'          => __( 'Show Text', 'customify' ),
            'checkbox_label'         => __( 'Show text', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_size',
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Icon Size', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_padding',
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Icon Padding', 'customify' ),

        ),

        array(
            'name' => 'nav_icon_item_color',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Color', 'customify' ),
        ),

        array(
            'name' => 'nav_icon_item_color_hover',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Color Hover', 'customify' ),
        ),



    );
}