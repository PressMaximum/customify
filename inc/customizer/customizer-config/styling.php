<?php
if ( ! function_exists( 'customify_customizer_styling_config' ) ) {
    function customify_customizer_styling_config( $configs ){

        $section = 'global_styling';

        $config = array(

            // Styling panel
            array(
                'name'           => 'styling_panel',
                'type'           => 'panel',
                //'priority' => 22,
                'title'          => __( 'Styling', 'customify' ),
            ),

            // Styling Global Section
            array(
                'name'           => "{$section}",
                'type'           => 'section',
                'panel'          => 'styling_panel',
                'title'          => __( 'Global', 'customify' ),
            ),
            array(
                'name' => "{$section}_color_primary",
                'type' => 'color',
                'section' => $section,
                'title' => __('Primary Color', 'customify'),
                'css_format' => '',
                'selector' => 'format',
            ),

            array(
                'name' => "{$section}_color_secondary",
                'type' => 'color',
                'section' => $section,
                'title' => __('Secondary Color', 'customify'),
                'css_format' => '._color_secondary { background-color: {{value}}; }',
                'selector' => 'format',
            ),

            array(
                'name' => "{$section}_color_text",
                'type' => 'color',
                'section' => $section,
                'title' => __('Primary Color', 'customify'),
                'css_format' => '._color_text { background-color: {{value}}; }',
                'selector' => 'format',
            ),

            array(
                'name' => "{$section}_color_link",
                'type' => 'color',
                'section' => $section,
                'title' => __('Link Color', 'customify'),
                'css_format' => '._color_link { background-color: {{value}}; }',
                'selector' => 'format',
            ),

            array(
                'name' => "{$section}_color_link_hover",
                'type' => 'color',
                'section' => $section,
                'title' => __('Link Color Hover', 'customify'),
                'css_format' => '._color_link_hover { background-color: {{value}}; }',
                'selector' => 'format',
            ),

            array(
                'name' => "{$section}_color_border",
                'type' => 'color',
                'section' => $section,
                'title' => __('Link Color Hover', 'customify'),
                'css_format' => '._color_border { background-color: {{value}}; }',
                'selector' => 'format',
            ),


        );
        return array_merge( $configs, $config );
    }
}

add_filter( 'customify/customizer/config', 'customify_customizer_styling_config' );