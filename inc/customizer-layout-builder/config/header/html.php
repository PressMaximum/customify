<?php
function _beacon_builder_config_header_html(){
    return array(
        array(
            'name' => 'header_html',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'HTML', '_beacon' ),
            'description' => __( 'This is section description',  '_beacon' ),
        ),

        array(
            'name' => 'header_html',
            'type' => 'icon',
            'section' => 'header_html',
            'theme_supports' => '',
            'title'          => __( 'HTML', '_beacon' ),
        ),
    );
}