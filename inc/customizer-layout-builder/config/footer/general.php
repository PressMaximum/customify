<?php
class Customify_Builder_Footer_General
{
    public $id = 'footer_general';

    function customize(){
        $section = 'footer_general';
        $prefix = 'footer_general_';
        $selector = '.site-footer';

        return array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'footer_settings',
                'priority' => -1,
                'title' => __( 'General', 'customify' ),
            ),


            array(
                'name' => $section.'_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Styling', 'customify' ),
                'description'  => __( 'Advanced styling for Footer', 'customify' ),
                'selector' => array(
                    'normal' => "{$selector}",
                    'normal_link_color' => "{$selector} a",
                    'hover_link_color' => "{$selector} a:hover",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        //'padding' => true // disable for special field.
                    ),
                    'hover_fields' => array(
                        'text_color' => false,
                        'padding' => false,
                        'bg_color' => false,
                        'bg_heading' => false,
                        'bg_cover' => false,
                        'bg_image' => false,
                        'bg_repeat' => false,
                        'bg_attachment' => false,
                        'bg_position' => false,
                        'border_heading' => false,
                        'border_color' => false,
                        'border_radius' => false,
                        'border_width' => false,
                        'border_style' => false,
                        'box_shadow' => false,
                    ), // disable hover tab and all fields inside.
                )
            ),


        );
    }
}

Customify_Customizer_Layout_Builder()->register_item( 'footer', 'Customify_Builder_Footer_General' );