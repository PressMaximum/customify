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

            // Base
            array(
                'name'           => "{$section}_base",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Base', 'customify' ),
            ),

	        array(
		        'name' => "{$section}_base_p",
		        'type' => 'typography',
		        'section' => "{$section}_base",
		        'title' => __('Body & Paragraph', 'customify'),
		        'description' => __('Apply to body and paragraph text.', 'customify'),
		        'css_format' => 'typography',
		        'selector' => 'body, body p, body button, body input, body select, body optgroup, body textarea, body div',
	        ),

            array(
                'name' => "{$section}_base_heading",
                'type' => 'typography',
                'section' => "{$section}_base",
                'title' => __('Heading', 'customify'),
                'description' => __('Apply to all heading elements.', 'customify'),
                'css_format' => 'typography',
                'selector' => 'body h1, body h2, body h3, body h4, body h5, body h6, body .h1, body .h2,body .h3',
                'fields' => array(
                    'font_size' => false,
                    'line_height' => false,
                    'letter_spacing' => false,
                )
            ),

            // Site Title and Tagline
            array(
                'name'           => "{$section}_site_tt",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Site Title & Tagline', 'customify' ),
            ),

            array(
                'name' => "{$section}_site_tt_title",
                'type' => 'typography',
                'section' => "{$section}_site_tt",
                'title' => __('Site Title', 'customify'),
                'css_format' => 'typography',
                'selector' => '.site-branding .site-title, .site-branding .site-title a',
            ),

            array(
                'name' => "{$section}_site_tt_desc",
                'type' => 'typography',
                'section' => "{$section}_site_tt",
                'title' => __('Tagline', 'customify'),
                'css_format' => 'typography',
                'selector' => '.site-branding .site-description',
            ),


            // Content
            array(
                'name'           => "{$section}_content",
                'type'           => 'section',
                'panel'          => 'typography_panel',
                'title'          => __( 'Content', 'customify' ),
            ),

            array(
                'name' => "{$section}_heading_h1",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H1', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h1',
            ),

            array(
                'name' => "{$section}_heading_h2",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H2', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h2',
            ),

            array(
                'name' => "{$section}_heading_h3",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H3', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h3',
            ),

            array(
                'name' => "{$section}_heading_h4",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H4', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h4',
            ),

            array(
                'name' => "{$section}_heading_h5",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H5', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h5',
            ),

            array(
                'name' => "{$section}_heading_h6",
                'type' => 'typography',
                'section' => "{$section}_content",
                'title' => __('Heading H6', 'customify'),
                'css_format' => 'typography',
                'selector' => '.entry-content h6',
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