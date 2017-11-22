<?php
function customify_builder_config_header_button(){
    $section = 'header_button';
    $prefix = 'header_button_';
    $fn = 'customify_builder_header_button_item';
    $selector = '.header-button-item';
    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'title' => __( 'Button', 'customify' ),
        ),

        array(
            'name' => $prefix.'label',
            'type' => 'text',
            'section' => $section,
            'theme_supports' => '',
            'selector' => $selector,
            'render_callback' => $fn,
            'title'  => __( 'Label', 'customify' ),
            'default'  => __( 'Click Me!', 'customify' ),
        ),

        array(
            'name' => $prefix.'icon',
            'type' => 'icon',
            'section' => $section,
            'selector' => $selector,
            'render_callback' => $fn,
            'theme_supports' => '',
            'title'  => __( 'Icon', 'customify' ),
        ),

        array(
            'name' => $prefix.'link',
            'type' => 'text',
            'section' => $section,
            'selector' => $selector,
            'render_callback' => $fn,
            'title'  => __( 'Link', 'customify' ),
        ),

        array(
            'name' => $prefix.'target',
            'type' => 'checkbox',
            'section' => $section,
            'selector' => $selector,
            'render_callback' => $fn,
            'title'  => __( 'Target', 'customify' ),
            'checkbox_label'  => __( 'Open link in new window.', 'customify' ),
        ),

        array(
            'name' => $prefix.'style',
            'type' => 'select',
            'section' => $section,
            'selector' => $selector,
            'render_callback' => $fn,
            'title'  => __( 'Style', 'customify' ),
            'choices' =>  array(
                'style-1' => __( 'Default', 'customify' ),
                'style-2' => __( 'Style 2', 'customify' ),
            )
        ),

        array(
            'name' => $prefix.'color',
            'type' => 'color',
            'section' => $section,
            'css_format' => 'color: {{value}};',
            'selector' => $selector.', '.$selector.':visited',
            'title'  => __( 'Color', 'customify' ),
        ),

        array(
            'name' => $prefix.'color_hover',
            'type' => 'color',
            'section' => $section,
            'css_format' => 'color: {{value}};',
            'selector' => $selector.':hover',
            'title'  => __( 'Color Hover', 'customify' ),
        ),

        array(
            'name' => $prefix.'bg_color',
            'type' => 'color',
            'section' => $section,
            'css_format' => 'background-color: {{value}};',
            'selector' => $selector,
            'title'  => __( 'Background Color', 'customify' ),
        ),

        array(
            'name' => $prefix.'bg_color_hover',
            'type' => 'color',
            'section' => $section,
            'css_format' => 'background-color: {{value}};',
            'selector' => $selector.':hover',
            'title'  => __( 'Background Color Hover', 'customify' ),
        ),

        array(
            'name' => $prefix.'padding',
            'type' => 'css_ruler',
            'section' => $section,
            'css_format' => array(
                'top' => 'padding-top: {{value}};',
                'right' => 'padding-right: {{value}};',
                'bottom' => 'padding-bottom: {{value}};',
                'left' => 'padding-left: {{value}};',
            ),
            'selector' => $selector,
            'device_settings' => true,
            'title'  => __( 'Padding', 'customify' ),
        ),

        array(
            'name' => $prefix.'border_radius',
            'type' => 'slider',
            'section' => $section,
            'max' =>  100,
            'default' =>  0,
            'css_format' =>'-webkit-border-radius: {{value}}; -moz-border-radius: {{value}}; border-radius: {{value}};',
            'selector' => $selector,
            'title'  => __( 'Border Radius', 'customify' ),
        ),

    );
    return $config;
}


function customify_builder_header_button_item(){
    $label = Customify_Customizer()->get_setting('header_button_label' );
    $icon = Customify_Customizer()->get_setting('header_button_icon' );
    $new_window = Customify_Customizer()->get_setting('header_button_target' );
    $link = Customify_Customizer()->get_setting('header_button_link' );
    $style = sanitize_text_field( Customify_Customizer()->get_setting('header_button_style' ) );

    $classes = array('header-button-item button');
    if ( $style ){
        $classes[]= $style;
    }

    $icon = wp_parse_args( $icon, array(
        'type' => '',
        'icon' => ''
    ) );
    $target = '';
    if ( $new_window == 1 ) {
        $target = ' target="_blank" ';
    }

    $icon_html = '';
    if ( $icon['icon'] ) {
        $icon_html = '<i class="'.esc_attr( $icon['icon'] ).'"></i> ';
    }

    echo '<a'.$target.' href="'.esc_url( $link ).'" class="'.esc_attr( join(" ", $classes ) ).'">'.$icon_html.esc_html( $label ).'</a>';
}