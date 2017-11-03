<?php
if ( ! function_exists( '_beacon_customizer_header_config' ) ) {
    function _beacon_customizer_header_config( $configs ){

        $config = array(
            array(
                'name' => 'header_settings',
                'type' => 'panel',
                'theme_supports' => '',
                'title'          => __( 'Header', '_beacon' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => __( 'Header Builder', '_beacon' ),
                'description' => __( 'This is section description',  '_beacon' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'js_raw',
                'section' => 'header_builder_panel',
                'theme_supports' => '',
                'title'          => __( 'Header Builder', '_beacon' ),
                'description' => __( 'Header Builder panel here....',  '_beacon' ),
                'selector' => '#masthead',
                'render_callback' => '_beacon_customize_render_header'
            ),

            // ------  HEADER TOP     --------

            // ------  /END HEADER TOP   --------



            // ------  MENU     --------
            array(
                'name' => 'header_menus',
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => __( 'Primary Menu', '_beacon' ),
            ),

            array(
                'name' => 'header_menu_primary',
                'type' => 'image',
                'section' => 'header_menus',
                'theme_supports' => '',
                'title'          => __( 'Primary menu', '_beacon' ),
            ),

            // ------ /END MENU --------


            // ------ NAV ICON  -------------
            array(
                'name' => 'header_nav_icon',
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => __( 'Nav Icon', '_beacon' ),
                'description' => __( 'This is section description',  '_beacon' ),
            ),

            array(
                'name' => 'header_nav_icon',
                'type' => 'icon',
                'section' => 'header_nav_icon',
                'theme_supports' => '',
                'title'          => __( 'Nav Icon', '_beacon' ),
            ),

            // ------ /END NAV ICON --------


            // ------ HTML  -------------
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
                'section' => 'header_nav_icon',
                'theme_supports' => '',
                'title'          => __( 'HTML', '_beacon' ),
            ),

            // ------ /END HTML --------


        );

        if ( function_exists( '_beacon_builder_config_header_logo' ) ) {
            $config =  array_merge( $config, _beacon_builder_config_header_logo() );
        }


        return array_merge( $configs, $config );
    }
}

add_filter( '_beacon/customizer/config', '_beacon_customizer_header_config' );