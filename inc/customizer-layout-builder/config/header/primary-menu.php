<?php
function _beacon_builder_config_header_primary_menu() {
    $section = 'header_menu_primary';
    return array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => __( 'Primary Menu', '_beacon' ),
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
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Item Padding', '_beacon' ),
        ),

        array(
            'name' => 'primary_menu_item_margin',
            'type' => 'slider',
            'section' => $section,
            'title'          => __( 'Item Margin', '_beacon' ),
        ),

        array(
            'name' => 'primary_menu_item_color',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Item Color', '_beacon' ),
        ),

        array(
            'name' => 'primary_menu_item_color_hover',
            'type' => 'color',
            'section' => $section,
            'title'          => __( 'Item Color Hover', '_beacon' ),
        ),

        array(
            'name' => 'primary_menu_typography',
            'type' => 'group',
            'section'     => $section,
            'title'          => __( 'Typography', '_beacon' ),
            'description'    => __( 'This is description',  '_beacon' ),
            'field_class' => '_beacon-typography-control',
            'selector' => '',
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