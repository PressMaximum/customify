<?php
function customify_builder_config_header_html(){
    return array(
        array(
            'name' => 'header_html',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'HTML', 'customify' ),
        ),

        array(
            'name' => 'header_html',
            'type' => 'textarea',
            'section' => 'header_html',
            'theme_supports' => '',
            'title'          => __( 'HTML', 'customify' ),
            'description'          => __( 'Arbitrary HTML code.', 'customify' ),
        ),
    );
}