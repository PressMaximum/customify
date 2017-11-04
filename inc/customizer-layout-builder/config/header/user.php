<?php
function _beacon_builder_config_header_user(){
    $section = 'header_user';
    $prefix = 'header_user_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'User', '_beacon' ),
        ),

        array(
            'name' => $prefix.'heading_1',
            'type' => 'heading',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'When user not logged in', '_beacon' ),
        ),

        array(
            'name' => $prefix.'show_login',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show login', '_beacon' ),
            'checkbox_label'   => __( 'Show login', '_beacon' ),
        ),

        array(
            'name' => $prefix.'login',
            'type' => 'group',
            'section' => $section,
            'title'   => __( 'Login item', '_beacon' ),
            'required'  => array( $prefix.'show_login','==','1' ),
            'fields' => array(
                array(
                    'name' => 'label',
                    'type' => 'text',
                    'label' => __( 'Label', '_beacon' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', '_beacon' ),
                ),
                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'Custom Login URL', '_beacon' ),
                ),
            )
        ),


        array(
            'name' => $prefix.'show_signup',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show login', '_beacon' ),
            'checkbox_label'   => __( 'Show Sign Up', '_beacon' ),
        ),

        array(
            'name' => $prefix.'signup',
            'type' => 'group',
            'section' => $section,
            'title'   => __( 'SingUp Settings', '_beacon' ),
            'required'  => array( $prefix.'show_signup','==','1' ),
            'fields' => array(
                array(
                    'name' => 'label',
                    'type' => 'text',
                    'label' => __( 'Label', '_beacon' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', '_beacon' ),
                ),
                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'Custom Sign Up URL', '_beacon' ),
                ),
            )
        ),


        array(
            'name' => $prefix.'heading_3',
            'type' => 'heading',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'When user logged in', '_beacon' ),
        ),

        array(
            'name' => $prefix.'show_avatar',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show Avatar', '_beacon' ),
            'checkbox_label'   => __( 'Show Avatar', '_beacon' ),
        ),

        array(
            'name' => $prefix.'show_username',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show User Name', '_beacon' ),
            'checkbox_label'   => __( 'Show User Name', '_beacon' ),
        ),


    );
}