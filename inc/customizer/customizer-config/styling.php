<?php
if ( ! function_exists( 'customify_customizer_styling_config' ) ) {
    function customify_customizer_styling_config( $configs ){

        $section = 'global_styling';

        $config = array(

            // Styling panel
            array(
                'name'           => 'styling_panel',
                'type'           => 'panel',
                'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Layouts', 'customify' ),
            ),

            // Styling Global Section
            array(
                'name'           => "{$section}_global",
                'type'           => 'section',
                'panel'          => 'styling_panel',
                'theme_supports' => '',
                'title'          => __( 'Global', 'customify' ),
            ),
            array(
                'name' => "{$section}_primary",
                'type' => 'color',
                'section' => $section,
                'title' => __('Primary Color', 'customify'),
                'default' => 'site-full-width',
                'css_format' => 'background',
                'selector' => 'body',
            ),


        );
        return array_merge( $configs, $config );
    }
}

add_filter( 'customify/customizer/config', 'customify_customizer_styling_config' );