<?php
function _beacon_builder_config_header_icon_list(){
    $section = 'header_icon_list';
    $prefix = 'header_icon_list_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Icon List', '_beacon' ),
        ),

        array(
            'name' => $prefix.'items',
            'type' => 'repeater',
            'section'     => $section,
            //'priority' => 22,
            'title'          => __( 'Items', '_beacon' ),
            'live_title_field' => 'title',
            'limit' => 4,
            'limit_msg' => __( 'Just limit 4 item, Ability HTML here',  '_beacon' ),
            'default' => array(
                array(
                    'title' => __( 'Title 1', '_beacon' ),
                    'content' => __( 'Content 1', '_beacon' ),
                ),
                array(
                    'title' => __( 'Title 2', '_beacon' ),
                    'content' => __( 'Content 2', '_beacon' ),
                )
            ),
            'fields' => array(
                array(
                    'name' => 'title',
                    'type' => 'text',
                    'label' => __( 'Title', '_beacon' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', '_beacon' ),
                ),
                array(
                    'name' => 'show_text',
                    'type' => 'checkbox',
                    'device_settings' => true,
                    'checkbox_label' => __( 'Show text',  '_beacon' ),
                    'label' => __( 'Show text', '_beacon' ),
                ),

                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'URL', '_beacon' ),
                ),

                array(
                    'name' => 'target',
                    'type' => 'checkbox',
                    'checkbox_label' => __( 'Open URL in new window.',  '_beacon' ),
                    'label' => __( 'Target', '_beacon' ),
                ),

            )
        ),



    );
}