<?php

class Customify_Builder_Footer  extends  Customify_Customize_Builder_Panel
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
                'priority' => 98,
                'title' => __( 'Footer', 'customify' ),
            ),

            array(
                'name' => 'footer_general',
                'type' => 'section',
                'panel' => 'footer_settings',
                'priority' => 0,
                'title' => __( 'General Settings', 'customify' ),
            ),

            array(
                'name' => 'footer_general_styling',
                'type' => 'styling',
                'section' => 'footer_general',
                'title' => __( 'Footer Styling', 'customify' ),
                'selector' => array(
                    'normal' => "#site-footer",
                    'normal_link_color' => "#site-footer a",
                    'hover_link_color' => "#site-footer a:hover",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        //'padding' => false // disable for special field.
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

            array(
                'name' => 'footer_general_typo',
                'type' => 'typography',
                'section' => 'footer_general',
                'title' => __( 'Footer Typography', 'customify' ),
                'selector' => '.site-footer .widget-area .widget',
                'css_format' => 'typography',
            ),

            array(
                'name' => 'footer_widget_title_styling',
                'type' => 'styling',
                'section' => 'footer_general',
                'title' => __( 'Widget Title Styling', 'customify' ),
                'selector' => array(
                    'normal' => ".site-footer .widget-title",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        //'padding' => false // disable for special field.
                        'link_color' => false
                    ),
                    'hover_fields' => false
                )
            ),

            array(
                'name' => 'footer_widget_title_typo',
                'type' => 'typography',
                'section' => 'footer_general',
                'title' => __( 'Widget Title Typography', 'customify' ),
                'selector' => '.site-footer .widget-title',
                'css_format' => 'typography',
            ),

            array(
                'name' => 'footer_builder_panel',
                'type' => 'section',
                'panel' => 'footer_settings',
                'title' => __( 'Footer Builder', 'customify' ),
            ),

            array(
                'name' => 'footer_builder_panel',
                'type' => 'js_raw',
                'section' => 'footer_builder_panel',
                'theme_supports' => '',
                'title' => __( 'Footer Builder', 'customify' ),
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
                'name' => $section.'_noti_layout',
                'type' => 'custom_html',
                'section' => $section,
                'title' => '',
                'description' => __("Layout <code>Full width - Contained</code> and <code>Full Width</code> will not fit browser width because you've selected <a class='focus-control' data-id='site_layout' href='#'>Site Layout</a> as <code>Boxed</code> or <code>Framed</code>", 'customify'),
                'required' => array(
                    array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
                )
            ),

            array(
                'name' => $section.'_heading',
                'type' => 'color',
                'section' => $section,
                'selector' => join( ', ', array( $selector.' .widget-title', $selector.' h1', $selector.' h2',  $selector.' h3',  $selector.' h4' ) ),
                'css_format' =>'color: {{value}};',
                'title' => __( 'Widget Title Color', 'customify' ),
            ),

            array(
                'name' => $section.'_styling',
                'type' => 'styling',
                'section' => $section,
                'title'  => __( 'Styling', 'customify' ),
                'description'  => sprintf( __( 'Advanced styling for %s', 'customify' ), $section_name ),
                'selector' => array(
                    'normal' => "{$selector}",
                    'normal_padding' => $selector.' .customify-container',
                    'normal_link_color' => "{$selector} a",
                    'hover_link_color' => "{$selector} a:hover",
                ),
                'css_format' => 'styling', // styling
                'fields' => array(
                    'normal_fields' => array(
                        //'padding' => false // disable for special field.
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

        return $config;
    }
}

function customify_footer_layout_settings( $item_id, $section ){

    global $wp_customize;

    if ( is_object( $wp_customize ) ) {
        global $wp_registered_sidebars;
        $name = $section;
        if ( is_array( $wp_registered_sidebars ) ) {
            if ( isset( $wp_registered_sidebars[ $item_id ] ) ) {
                $name = $wp_registered_sidebars[ $item_id ]['name'];
            }
        }
        $wp_customize->add_section( $section , array(
            'title'      => $name,
        ) );
    }

    if ( function_exists( 'customify_header_layout_settings' ) ) {
        return customify_header_layout_settings( $item_id, $section, 'customify_customize_render_footer', 'footer_' );
    }

    return false;
}

Customify_Customize_Layout_Builder()->register_builder( 'footer', new Customify_Builder_Footer() );



