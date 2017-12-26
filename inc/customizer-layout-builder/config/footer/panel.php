<?php

Customify_Customizer_Layout_Builder()->register_builder( 'footer', new Customify_Builder_Footer() );

class Customify_Builder_Footer  extends  Customify_Customizer_Builder_Panel
{
    public $id = 'footer';

    function get_config()
    {
        return array(
            'id' => $this->id,
            'title' => __('Footer Builder', 'customify'),
            'control_id' => 'footer_builder_panel',
            'panel' => 'footer_settings',
            'section' => 'footer_builder_panel',
            'devices' => array(
                'desktop' => __('Footer Layout', 'customify')
            ),
        );
    }

    function get_rows_config()
    {
        return array(
            //'top' => __('Footer Top', 'customify'),
            'main' => __('Footer Main', 'customify'),
            'bottom' => __('Footer Bottom', 'customify'),
        );
    }

    function customize()
    {
        $fn = 'customify_customize_render_footer';
        $config = array(
            array(
                'name' => 'footer_settings',
                'type' => 'panel',
                'theme_supports' => '',
                'title' => __( 'Footer', 'customify' ),
            ),

            array(
                'name' => 'footer_builder_panel',
                'type' => 'section',
                'panel' => 'footer_settings',
                'title' => __( 'Footer Builder', 'customify' ),
                'description' => __( 'This is section description',  'customify' ),
            ),

            array(
                'name' => 'footer_builder_panel',
                'type' => 'js_raw',
                'section' => 'footer_builder_panel',
                'theme_supports' => '',
                'title' => __( 'Footer Builder', 'customify' ),
                'description' => __( 'Footer Builder panel here....',  'customify' ),
                'selector' => '#site-footer',
                'render_callback' => $fn,
                'container_inclusive' => true
            ),

        );

        return $config;
    }

    function row_config($section = false, $section_name = false)
    {

        if ( ! $section ) {
            $section  = 'footer_top';
        }
        if ( ! $section_name ) {
            $section_name = __( 'Footer Top', 'customify' );
        }

        $selector = '#cb-row--'.str_replace('_', '-', $section );

        $fn = 'customify_customize_render_footer';
        //$selector_all = '#masthead';

        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'footer_settings',
                'theme_supports' => '',
                'title' => $section_name,
            ),

            array(
                'name' => $section.'_layout',
                'type' => 'select',
                'section' => $section,
                'title' => __( 'Layout', 'customify' ),
                'selector' => $selector,
                'render_callback' => $fn,
                'css_format' => 'html_class',
                'default' => 'layout-full-contained',
                'choices' => array(
                    'layout-full-contained' =>  __( 'Full width - Contained', 'customify' ),
                    'layout-fullwidth' =>  __( 'Full Width', 'customify' ),
                    'layout-contained' =>  __( 'Contained', 'customify' ),
                )
            ),

            array(
                'name' => $section.'_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'theme_supports' => '',
                'device_settings' => true,
                'selector' => $selector.' .customify-container',
                'css_format' => array(
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
                'title' => __( 'Padding', 'customify' ),
            ),

            array(
                'name' => $section.'_heading',
                'type' => 'color',
                'section' => $section,
                'selector' => join( ', ', array( $selector.' .widget-title', $selector.' h1', $selector.' h2',  $selector.' h3',  $selector.' h4' ) ),
                'css_format' =>'color: {{value}};',
                'title' => __( 'Heading Color', 'customify' ),
            ),

            array(
                'name' => $section.'_color',
                'type' => 'color',
                'section' => $section,
                'selector' => $selector,
                'css_format' =>'color: {{value}};',
                'title' => __( 'Text Color', 'customify' ),
            ),

            array(
                'name' => $section.'_link_color',
                'type' => 'color',
                'section' => $section,
                'selector' => $selector.' a',
                'css_format' =>'color: {{value}};',
                'title' => __( 'Link Color', 'customify' ),
            ),

            array(
                'name' => $section.'_link_hover_color',
                'type' => 'color',
                'section' => $section,
                'selector' => $selector.' a:hover',
                'css_format' =>'color: {{value}};',
                'title' => __( 'Link Hover Color', 'customify' ),
            ),

            array(
                'name' => $section.'_background',
                'type' => 'group',
                'section'     => $section,
                'title'          => __( 'Background', 'customify' ),
                'live_title_field' => 'title',
                'field_class' => 'customify-background-control',
                'selector' => $selector,
                'css_format' => 'background',
                'default' => array(

                ),
                'fields' => array(
                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __( 'Color', 'customify' ),
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __( 'Image', 'customify' ),
                    ),
                    array(
                        'name' => 'cover',
                        'type' => 'checkbox',
                        'required' => array( 'image', 'not_empty', ''),
                        'checkbox_label' => __( 'Background cover', 'customify' ),
                    ),
                    array(
                        'name' => 'position',
                        'type' => 'select',
                        'label' => __( 'Background Position', 'customify' ),
                        'required' => array( 'image', 'not_empty', ''),
                        'choices' => array(
                            'default'       => __( 'Position', 'customify' ),
                            'center'        => __( 'Center', 'customify' ),
                            'top_left'      => __( 'Top Left', 'customify' ),
                            'top_right'     => __( 'Top Right', 'customify' ),
                            'top_center'    => __( 'Top Center', 'customify' ),
                            'bottom_left'   => __( 'Bottom Left', 'customify' ),
                            'bottom_center' => __( 'Bottom Center', 'customify' ),
                            'bottom_right'  => __( 'Bottom Right', 'customify' ),
                        ),
                    ),

                    array(
                        'name' => 'repeat',
                        'type' => 'select',
                        'label' => __( 'Background Repeat', 'customify' ),
                        'required' => array(
                            array('image', 'not_empty', ''),
                            // array('style', '!=', 'cover' ),
                        ),
                        'choices' => array(
                            'default' => __( 'Repeat', 'customify' ),
                            'no-repeat' => __( 'No-repeat', 'customify' ),
                            'repeat-x' => __( 'Repeat Horizontal', 'customify' ),
                            'repeat-y' => __( 'Repeat Vertical', 'customify' ),
                        ),
                    ),

                    array(
                        'name' => 'attachment',
                        'type' => 'select',
                        'label' => __( 'Background Attachment', 'customify' ),
                        'required' => array(
                            array('image', 'not_empty', '')
                        ),
                        'choices' => array(
                            'default' => __( 'Attachment', 'customify' ),
                            'scroll' => __( 'Scroll', 'customify' ),
                            'fixed' => __( 'Fixed', 'customify' )
                        ),
                    ),

                )
            ),

        );

        return $config;
    }
}


