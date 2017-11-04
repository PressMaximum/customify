<?php
function _beacon_builder_config_header_search(){
    $config  = array(
        array(
            'name' => 'header_search',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Search', '_beacon' ),
        ),

        array(
            'name' => 'header_search',
            'type' => 'image',
            'section' => 'header_search',
            'theme_supports' => '',
            'title'          => __( 'Search', '_beacon' ),
        ),
    );
    return $config;
}