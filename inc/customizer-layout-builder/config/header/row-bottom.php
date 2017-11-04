<?php

function _beacon_builder_config_header_row_bottom(){
    $config  = array(
        array(
            'name' => 'header_bottom',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Header Bottom', '_beacon' ),
        ),

        array(
            'name' => 'header_bototm',
            'type' => 'image',
            'section' => 'header_bottom',
            'theme_supports' => '',
            'title'          => __( 'Bottom', '_beacon' ),
        ),
    );
    return $config;
}
