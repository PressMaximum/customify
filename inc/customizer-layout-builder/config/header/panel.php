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



        );

        foreach ( _Beacon_Customizer_Layout_Builder::get_header_sections() as $id ) {
            $file = get_template_directory().'/inc/customizer-layout-builder/config/header/'.$id.'.php';
            if (  is_file( $file ) ) {
                require_once get_template_directory().'/inc/customizer-layout-builder/config/header/'.$id.'.php';
            }

            $func_id = str_replace( '-', '_', $id );

            if ( function_exists( '_beacon_builder_config_header_'.$func_id ) ) {
                $config = array_merge( $config, call_user_func_array( '_beacon_builder_config_header_'.$func_id, array() ) );
            }
        }

        return array_merge( $configs, $config );
    }
}

add_filter( '_beacon/customizer/config', '_beacon_customizer_header_config' );