<?php
function _beacon_builder_config_header_logo(){
    $config  = array(
        array(
            'name' => 'header_logo',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Logo', '_beacon' ),
        ),

        array(
            'name' => 'header_logo',
            'type' => 'image',
            'section' => 'header_logo',
            'theme_supports' => '',
            'title'          => __( 'Logo', '_beacon' ),
        ),
    );
    return $config;
}