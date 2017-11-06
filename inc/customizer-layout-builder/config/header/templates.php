<?php
function _beacon_builder_config_header_templates(){
    $section = 'header_templates';
    $prefix = 'header_templates_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Templates', '_beacon' ),
        ),

        array(
            'name' => $prefix.'save',
            'type' => 'custom_html',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'Save Template', '_beacon' ),
            'description'          => '<div class="save-template-form"><input type="text" data-builder-id="header" class="template-input-name"><button class="save-builder-template" type="button">'.esc_html__( 'Save', '_beacon' ).'</button></div>',
        ),
    );
}