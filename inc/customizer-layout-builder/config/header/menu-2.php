<?php
function customify_builder_config_header_menu_2() {
    $section = 'header_menu_2';
    $fn = 'customify_builder_menu_2_item';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'Secondary Menu', 'customify' ),
            'description' => __( 'Assign <a href="#menu_locations"  class="focus-section">Menu Location</a> for Primary menu', 'customify' )
        ),

        array(
            'name' => 'menu_2_style',
            'type' => 'select',
            'section' => $section,
            'selector' => '.secondary-menu',
            'render_callback' => $fn,
            'title' => __( 'Style', 'customify' ),
            'choices' => array(
                'style_default' => __( 'Default', 'customify' ),
                'style_2' => __( 'Style 2', 'customify' ),
            )
        ),

        array(
            'name' => 'menu_2_item_padding',
            'type' => 'css_ruler',
            'section' => $section,
            'title' => __( 'Item Padding', 'customify' ),
            'selector' => '.secondary-menu li a',
            'device_settings' => true,
            'css_format' => array(
                'unit' => '',
                'top' => 'padding-top: {{value}};',
                'right' => 'padding-right: {{value}};',
                'bottom' => 'padding-bottom: {{value}};',
                'left' => 'padding-left: {{value}};',
            ),
        ),

        array(
            'name' => 'menu_2_item_margin',
            'type' => 'css_ruler',
            'section' => $section,
            'selector' => '.secondary-menu .menu li',
            'device_settings' => true,
            'css_format' => array(
                'top' => 'margin-top: {{value}};',
                'right' => 'margin-right: {{value}};',
                'bottom' => 'margin-bottom: {{value}};',
                'left' => 'margin-left: {{value}};',
            ),
            'title'  => __( 'Item Margin', 'customify' ),
        ),

        array(
            'name' => 'menu_2_item_color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Item Color', 'customify' ),
            'selector'  => '.secondary-menu li a, .secondary-menu li',
            'device_settings' => true,
            'css_format'  => 'color: {{value}};',
        ),

        array(
            'name' => 'menu_2_item_color_hover',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Item Color Hover', 'customify' ),
            'device_settings' => true,
            'selector'  => '.secondary-menu li a:hover, .secondary-menu li:hover > span',
            'css_format'  => 'color: {{value}};',
        ),

        array(
            'name' => 'menu_2_typography',
            'type' => 'group',
            'section'     => $section,
            'title'          => __( 'Typography', 'customify' ),
            'description'    => __( 'This is description',  'customify' ),
            'field_class' => 'customify-typography-control',
            'selector' => '.secondary-menu',
            'css_format' => 'typography',
            'default' => array(

            ),
            'fields' => array(

                array(
                    'name' => 'font',
                    'type' => 'font',
                    'label' => __( 'Font', 'customify' ),
                ),

                array(
                    'name' => 'font_style',
                    'type' => 'font_style',
                    'label' => __( 'Font Style', 'customify' ),
                ),

                array(
                    'name' => 'font_size',
                    'type' => 'slider',
                    'label' => __( 'Font Size', 'customify' ),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'line_height',
                    'type' => 'slider',
                    'label' => __( 'Line Height', 'customify' ),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'letter_spacing',
                    'type' => 'slider',
                    'label' => __( 'Letter Spacing', 'customify' ),
                    'min' => -10,
                    'max' => 10,
                ),

            )
        ),

        array(
            'name' => 'header_menu_2_align',
            'type' => 'text_align_no_justify',
            'section' => $section,
            'device_settings' => false,
            'selector' => '.builder-item--secondary-menu',
            'css_format' => 'text-align: {{value}};',
            'title'   => __( 'Align', 'customify' ),
            'description'   => __( 'Apply for desktop only.', 'customify' ),
        ),

    );
}


function customify_builder_menu_2_item(){

    $style = sanitize_text_field( Customify_Customizer()->get_setting('menu_2_style') );

    wp_nav_menu( array(
        'theme_location' => 'menu-2',
        'container' => 'nav',
        'container_id' => 'site-navigation-__id__-__device__',
        'container_class' => 'secondary-menu nav-menu-__device__ secondary-menu-__device__'.( $style ? ' '.$style : '' ),
        'menu_id'        => 'secondary-menu-__id__-__device__',
        'fallback_cb' => false
    ) );

}