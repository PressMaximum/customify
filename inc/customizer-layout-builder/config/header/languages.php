<?php
function customify_builder_config_header_languages(){
    $section = 'header_languages';
    $prefix = 'header_languages';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Languages Switcher', 'customify' ),
        ),

        array(
            'name' => $prefix.'switcher',
            'type' => 'textarea',
            'section' => $section,
            'theme_supports' => '',
            'title'    => __( 'Languages Switcher', 'customify' ),
        ),
    );
}