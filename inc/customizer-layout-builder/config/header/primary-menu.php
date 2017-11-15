<?php
function _beacon_builder_config_header_primary_menu() {
    $section = 'header_menu_primary';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title' => __( 'Primary Menu', '_beacon' ),
            'description' => __( 'Assign <a href="#menu_locations"  class="focus-section">Menu Location</a> for Primary menu', '_beacon' )
        ),

        array(
            'name' => 'primary_menu_style',
            'type' => 'select',
            'section' => $section,
            'title'          => __( 'Style', '_beacon' ),
            'choices' => array(
                'default' => __( 'Default', '_beacon' ),
                'style_2' => __( 'Style 2', '_beacon' ),
            )
        ),

        array(
            'name' => 'primary_menu_item_padding',
            'type' => 'css_ruler',
            'section' => $section,
            'title' => __( 'Item Padding', '_beacon' ),
            'selector' => '.primary-menu li a',
            'css_format' => array(
                'unit' => '',
                'top' => 'padding-top: {{value}};',
                'right' => 'padding-right: {{value}};',
                'bottom' => 'padding-bottom: {{value}};',
                'left' => 'padding-left: {{value}};',
            ),
        ),

        array(
            'name' => 'primary_menu_item_margin',
            'type' => 'css_ruler',
            'section' => $section,
            'selector' => '.primary-menu .menu li',
            'css_format' => array(
                'top' => 'margin-top: {{value}};',
                'right' => 'margin-right: {{value}};',
                'bottom' => 'margin-bottom: {{value}};',
                'left' => 'margin-left: {{value}};',
            ),
            'title'  => __( 'Item Margin', '_beacon' ),
        ),

        array(
            'name' => 'primary_menu_item_color',
            'type' => 'color',
            'section' => $section,
            'title'  => __( 'Item Color', '_beacon' ),
            'selector'  => '.primary-menu li a',
            'css_format'  => 'color: {{value}};',
        ),

        array(
            'name' => 'primary_menu_item_color_hover',
            'type' => 'color',
            'section' => $section,
            'title' => __( 'Item Color Hover', '_beacon' ),
            'selector'  => '.primary-menu li a:hover',
            'css_format'  => 'color: {{value}};',
        ),

        array(
            'name' => 'primary_menu_typography',
            'type' => 'group',
            'section'     => $section,
            'title'          => __( 'Typography', '_beacon' ),
            'description'    => __( 'This is description',  '_beacon' ),
            'field_class' => '_beacon-typography-control',
            'selector' => '.primary-menu',
            'css_format' => 'typography',
            'default' => array(

            ),
            'fields' => array(

                array(
                    'name' => 'font',
                    'type' => 'font',
                    'label' => __( 'Font', '_beacon' ),
                ),

                array(
                    'name' => 'font_style',
                    'type' => 'font_style',
                    'label' => __( 'Font Style', '_beacon' ),
                ),

                array(
                    'name' => 'font_size',
                    'type' => 'slider',
                    'label' => __( 'Font Size', '_beacon' ),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'line_height',
                    'type' => 'slider',
                    'label' => __( 'Line Height', '_beacon' ),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'letter_spacing',
                    'type' => 'slider',
                    'label' => __( 'Letter Spacing', '_beacon' ),
                    'min' => -10,
                    'max' => 10,
                ),

            )
        ),

    );
}


function _beacon_builder_primary_menu_item(){

    wp_nav_menu( array(
        'theme_location' => 'menu-1',
        'container' => 'nav',
        'container_id' => 'site-navigation-__id__',
        'container_class' => 'primary-menu',
        'menu_id'        => 'primary-menu-__id__',
    ) );

}