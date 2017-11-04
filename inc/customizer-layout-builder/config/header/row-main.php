<?php

function _beacon_builder_config_header_row_main(){
    $config  = array(
        array(
            'name' => 'header_main',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Header Main', '_beacon' ),
        ),

        array(
            'name' => 'header_main',
            'type' => 'image',
            'section' => 'header_main',
            'theme_supports' => '',
            'title'          => __( 'Main', '_beacon' ),
        ),
    );
    return $config;
}
