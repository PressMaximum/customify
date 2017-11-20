<?php
function customify_builder_config_header_button(){
    $section = 'header_button';
    $prefix = 'header_button_';
    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'title' => __( 'Button', 'customify' ),
        ),

        array(
            'name' => $prefix.'label',
            'type' => 'text',
            'section' => $section,
            'theme_supports' => '',
            'title'  => __( 'Label', 'customify' ),
        ),

        array(
            'name' => $prefix.'icon',
            'type' => 'text',
            'section' => $section,
            'theme_supports' => '',
            'title'  => __( 'Icon', 'customify' ),
        ),

        array(
            'name' => $prefix.'link',
            'type' => 'text',
            'section' => $section,
            'title'  => __( 'Link', 'customify' ),
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'select',
            'section' => $section,
            'title'  => __( 'Target', 'customify' ),
            'choices' =>  array(
                'default' => __( 'Current Window', 'customify' ),
                '_blank' => __( 'New Window', 'customify' ),
            )
        ),

        array(
            'name' => $prefix.'style',
            'type' => 'select',
            'section' => $section,
            'title'  => __( 'Style', 'customify' ),
            'choices' =>  array(
                '1' => __( 'Default', 'customify' ),
                '2' => __( 'Style 2', 'customify' ),
            )
        ),

        array(
            'name' => $prefix.'color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Color', 'customify' ),
        ),

        array(
            'name' => $prefix.'color_hover',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Color Hover', 'customify' ),
        ),

        array(
            'name' => $prefix.'bg_color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Background Color', 'customify' ),
        ),

        array(
            'name' => $prefix.'bg_color_hover',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Background Color Hover', 'customify' ),
        ),

        array(
            'name' => $prefix.'padding',
            'type' => 'css_ruler',
            'section' => $section,
            'title'  => __( 'Padding', 'customify' ),
        ),

    );
    return $config;
}


function customify_builder_header_button_item(){
    echo "Button Here";
}