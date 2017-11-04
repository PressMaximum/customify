<?php

function _beacon_builder_config_header_row_sidebar(){
    $config  = array(
        array(
            'name' => 'header_sidebar',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Header Sidebar', '_beacon' ),
        ),

        array(
            'name' => 'header_sidebar',
            'type' => 'image',
            'section' => 'header_sidebar',
            'theme_supports' => '',
            'title'          => __( 'Bottom', '_beacon' ),
        ),
    );
    return $config;
}
