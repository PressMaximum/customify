<?php
function customify_builder_config_header_html(){
    $fn = 'customify_builder_header_html_item';
    $section = 'header_html';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'HTML', 'customify' ),
        ),

        array(
            'name' => 'header_html',
            'type' => 'textarea',
            'section' => $section,
            'selector' => '.builder-header-html-item',
            'render_callback' => $fn,
            'theme_supports' => '',
            'title' => __( 'HTML', 'customify' ),
            'description' => __( 'Arbitrary HTML code.', 'customify' ),
        ),

        array(
            'name' => 'header_nav_icon_align',
            'type' => 'text_align_no_justify',
            'section' => $section,
            'selector' => '.builder-item--html',
            'css_format' => 'text-align: {{value}};',
            'title'   => __( 'Align', 'customify' ),
            'device_settings' => true,
        ),

    );
}

function customify_builder_header_html_item(){
    $content = Customify_Customizer()->get_setting( 'header_html' );
    echo '<div class="builder-header-html-item item--html">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
    echo '</div>';
}

