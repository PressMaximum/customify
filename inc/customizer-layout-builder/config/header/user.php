<?php
function customify_builder_config_header_user(){
    $section = 'header_user';
    $prefix = 'header_user_';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'User', 'customify' ),
        ),

        array(
            'name' => $prefix.'heading_1',
            'type' => 'heading',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'When user not logged in', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_login',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show login', 'customify' ),
            'checkbox_label'   => __( 'Show login', 'customify' ),
        ),

        array(
            'name' => $prefix.'login',
            'type' => 'group',
            'section' => $section,
            'title'   => __( 'Login item', 'customify' ),
            'required'  => array( $prefix.'show_login','==','1' ),
            'fields' => array(
                array(
                    'name' => 'label',
                    'type' => 'text',
                    'label' => __( 'Label', 'customify' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', 'customify' ),
                ),
                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'Custom Login URL', 'customify' ),
                ),
            )
        ),


        array(
            'name' => $prefix.'show_signup',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show login', 'customify' ),
            'checkbox_label'   => __( 'Show Sign Up', 'customify' ),
        ),

        array(
            'name' => $prefix.'signup',
            'type' => 'group',
            'section' => $section,
            'title'   => __( 'SingUp Settings', 'customify' ),
            'required'  => array( $prefix.'show_signup','==','1' ),
            'fields' => array(
                array(
                    'name' => 'label',
                    'type' => 'text',
                    'label' => __( 'Label', 'customify' ),
                ),
                array(
                    'name' => 'icon',
                    'type' => 'icon',
                    'label' => __( 'Icon', 'customify' ),
                ),
                array(
                    'name' => 'url',
                    'type' => 'text',
                    'label' => __( 'Custom Sign Up URL', 'customify' ),
                ),
            )
        ),


        array(
            'name' => $prefix.'heading_3',
            'type' => 'heading',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'When user logged in', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_avatar',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show Avatar', 'customify' ),
            'checkbox_label'   => __( 'Show Avatar', 'customify' ),
        ),

        array(
            'name' => $prefix.'show_username',
            'type' => 'checkbox',
            'section' => $section,
            'title'   => __( 'Show User Name', 'customify' ),
            'checkbox_label'   => __( 'Show User Name', 'customify' ),
        ),


    );
}