<?php
function _beacon_builder_config_header_search(){
    $section = 'header_search';
    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Search', '_beacon' ),
        ),

        array(
            'name' => 'header_search',
            'type' => 'text',
            'section' => 'header_search',
            'theme_supports' => '',
            'title'          => __( 'Search', '_beacon' ),
        ),
    );
    return $config;
}