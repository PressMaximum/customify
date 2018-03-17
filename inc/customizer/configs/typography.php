<?php
if ( ! function_exists( 'customify_customizer_typography_config' ) ) {
    function customify_customizer_typography_config( $configs ){

        $section = 'global_typography';

        $config = array(

            array(
                'name'           => 'typography_panel',
                'type'           => 'panel',
                'priority'      => 22,
                'title'          => __( 'Typography', 'customify' ),
            ),

            // Paragraph
            array(
                'name'           => "{$section}_p",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Paragraph', 'customify' ),
            ),

            array(
                'name' => "{$section}_p",
                'type' => 'typography',
                'section' => "{$section}_p",
                'title' => __('Paragraph', 'customify'),
                'css_format' => 'typography',
                'selector' => 'body',
            ),

            // Heading
            array(
                'name'           => "{$section}_heading",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Heading', 'customify' ),
            ),

            array(
                'name' => "{$section}_heading_h1",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H1', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h1',
            ),

            array(
                'name' => "{$section}_heading_h2",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H2', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h2',
            ),

            array(
                'name' => "{$section}_heading_h3",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H3', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h3',
            ),

            array(
                'name' => "{$section}_heading_h4",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H4', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h4',
            ),

            array(
                'name' => "{$section}_heading_h5",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H5', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h5',
            ),

            array(
                'name' => "{$section}_heading_h6",
                'type' => 'typography',
                'section' => "{$section}_heading",
                'title' => __('Heading H6', 'customify'),
                'css_format' => 'typography',
                'selector' => 'h6',
            ),

            // Widget title
            array(
                'name'           => "{$section}_titlebar",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Titlebar', 'customify' ),
            ),

            array(
                'name' => "{$section}_titlebar",
                'type' => 'typography',
                'section' => "{$section}_titlebar",
                'title' => __('Titlebar', 'customify'),
                'css_format' => 'typography',
                'selector' => '.page-titlebar .titlebar-title',
            ),

            // Widget title
            array(
                'name'           => "{$section}_widget_title",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Widget Title', 'customify' ),
            ),

            array(
                'name' => "{$section}_widget_title",
                'type' => 'typography',
                'section' => "{$section}_widget_title",
                'title' => __('Widget Title', 'customify'),
                'css_format' => 'typography',
                'selector' => '.widget-title',
            ),



        );
        return array_merge( $configs, $config );
    }
}

add_filter( 'customify/customizer/config', 'customify_customizer_typography_config' );