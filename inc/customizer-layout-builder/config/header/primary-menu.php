<?php
function _beacon_builder_config_header_primary_menu() {
    return array(
        array(
            'name' => 'header_menu_primary',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Primary Menu', '_beacon' ),
        ),

        array(
            'name' => 'header_menu_primary',
            'type' => 'image',
            'section' => 'header_menu_primary',
            'theme_supports' => '',
            'title'          => __( 'Primary menu', '_beacon' ),
        ),

    );
}