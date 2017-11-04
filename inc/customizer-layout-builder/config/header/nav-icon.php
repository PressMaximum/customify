<?php
function _beacon_builder_config_header_nav_icon(){
    return array(
        array(
            'name' => 'header_nav_icon',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Nav Icon', '_beacon' ),
            'description' => __( 'This is section description',  '_beacon' ),
        ),

        array(
            'name' => 'header_nav_icon',
            'type' => 'icon',
            'section' => 'header_nav_icon',
            'theme_supports' => '',
            'title'          => __( 'Nav Icon', '_beacon' ),
        ),
    );
}