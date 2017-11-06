<?php
function _beacon_builder_config_header_cart(){
    $section = 'header_cart';
    $prefix = 'header_cart_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'title' => __( 'Cart', '_beacon' ),
        ),

        array(
            'name' => $prefix.'icon',
            'type' => 'icon',
            'section' => $section,
            'title'  => __( 'Cart Icon', '_beacon' ),
        ),

        array(
            'name' => $prefix.'show_price',
            'type' => 'check',
            'section' => $section,
            'title' => __( 'Show Total Price', '_beacon' ),
        ),

        array(
            'name' => $prefix.'show_number_item',
            'type' => 'check',
            'section' => $section,
            'title' => __( 'Show Number Item', '_beacon' ),
        ),
        array(
            'name' => $prefix.'url',
            'type' => 'text',
            'section' => $section,
            'title' => __( 'Cart Page URL', '_beacon' ),
        ),

        array(
            'name' => $prefix.'url',
            'type' => 'text',
            'section' => $section,
            'title' => __( 'Cart Page URL', '_beacon' ),
        ),

        array(
            'name' => $prefix.'color',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Color', '_beacon' ),
        ),

        array(
            'name' => $prefix.'color_hover',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Color Hover', '_beacon' ),
        ),

    );
}