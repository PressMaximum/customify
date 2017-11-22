<?php
function customify_builder_config_header_html(){
    $fn = 'customify_builder_header_html_item';
    return array(
        array(
            'name' => 'header_html',
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'HTML', 'customify' ),
        ),

        array(
            'name' => 'header_html',
            'type' => 'textarea',
            'section' => 'header_html',
            'selector' => '.builder-header-html-item',
            'render_callback' => $fn,
            'theme_supports' => '',
            'title' => __( 'HTML', 'customify' ),
            'description' => __( 'Arbitrary HTML code.', 'customify' ),
        ),
    );
}

function customify_builder_header_html_item(){
    $content = Customify_Customizer()->get_setting( 'header_html' );
    echo '<div class="builder-header-html-item">';
        echo apply_filters('customify_the_content', wp_kses_post( balanceTags( $content, true ) ) );
    echo '</div>';
}

