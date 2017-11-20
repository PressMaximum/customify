<?php
function customify_builder_config_header_social_icons(){
    $section = 'header_social_icons';
    $prefix = 'header_social_icons_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Social Icons', 'customify' ),
        ),

        array(
            'name' => $prefix.'items',
            'type' => 'repeater',
            'section'     => $section,
            //'priority' => 22,
            'title'          => __( 'Items', 'customify' ),
            'live_title_field' => 'title',
            'limit' => 4,
            'limit_msg' => __( 'Just limit 4 item, Ability HTML here',  'customify' ),
            'default' => array(

            ),
            'fields' => array(
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => __( 'Title', 'customify' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', 'customify' ),
                ),
                array(
                    'name' => 'show_text',
                    'type' => 'checkbox',
                    'device_settings' => true,
                    'checkbox_label' => __( 'Show text',  'customify' ),
                    'label' => __( 'Show text', 'customify' ),
                ),

                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'URL', 'customify' ),
                ),

            )
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'checkbox',
            'section'     => $section,
            'checkbox_label' => __( 'Open URL in new window.',  'customify' ),
            'label' => __( 'Target', 'customify' ),
        ),


    );
}