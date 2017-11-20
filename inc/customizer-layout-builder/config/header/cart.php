<?php
function customify_builder_config_header_cart(){
    $section = 'header_cart';
    $prefix = 'header_cart_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'title' => __( 'Cart', 'customify' ),
        ),

        array(
            'name' => $prefix.'icon',
            'type' => 'icon',
            'section' => $section,
            'title'  => __( 'Cart Icon', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_price',
            'type' => 'check',
            'section' => $section,
            'title' => __( 'Show Total Price', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_number_item',
            'type' => 'check',
            'section' => $section,
            'title' => __( 'Show Number Item', 'customify' ),
        ),
        array(
            'name' => $prefix.'url',
            'type' => 'text',
            'section' => $section,
            'title' => __( 'Cart Page URL', 'customify' ),
        ),

        array(
            'name' => $prefix.'url',
            'type' => 'text',
            'section' => $section,
            'title' => __( 'Cart Page URL', 'customify' ),
        ),

        array(
            'name' => $prefix.'color',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Color', 'customify' ),
        ),

        array(
            'name' => $prefix.'color_hover',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Color Hover', 'customify' ),
        ),

    );
}