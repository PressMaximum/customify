<?php
function _beacon_builder_config_header_row_top(){
    $config  = array(
        array(
            'name' => 'header_top',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Header Top', '_beacon' ),
        ),

        array(
            'name' => 'header_top',
            'type' => 'image',
            'section' => 'header_top',
            'theme_supports' => '',
            'title'          => __( 'header_top', '_beacon' ),
        ),
    );
    return $config;
}
