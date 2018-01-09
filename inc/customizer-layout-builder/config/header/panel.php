<?php

Customify_Customizer_Layout_Builder()->register_builder( 'header', new Customify_Builder_Header() );

class Customify_Builder_Header  extends  Customify_Customizer_Builder_Panel{
    public $id = 'header';

    function get_config(){
        return array(
            'id'         => $this->id,
            'title'      => __( 'Header Builder', 'customify' ),
            'control_id' => 'header_builder_panel',
            'panel'      => 'header_settings',
            'section'    => 'header_builder_panel',
            'devices' => array(
                'desktop'   => __( 'Desktop', 'customify' ),
                'mobile'    => __( 'Mobile/Tablet', 'customify' ),
            ),
        );
    }

    function get_rows_config(){
        return array(
            'top' =>  __( 'Header Top', 'customify' ),
            'main' =>  __( 'Header Main', 'customify' ),
            'bottom' =>  __( 'Header Bottom', 'customify' ),
            'sidebar' =>  __( 'Mobile Sidebar', 'customify' ),
        );
    }

    function customize( ){

        $fn = 'customify_customize_render_header';
        $config = array(
            array(
                'name' => 'header_settings',
                'type' => 'panel',
                'priority'    => 1,
                'title' => __( 'Header', 'customify' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'section',
                'panel' => 'header_settings',
                'title' => __( 'Header Builder', 'customify' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'js_raw',
                'section' => 'header_builder_panel',
                'theme_supports' => '',
                'title' => __( 'Header Builder', 'customify' ),
                'selector' => '#masthead',
                'render_callback' => $fn,
                'container_inclusive' => true
            ),
        );

        return $config;
    }

    function row_config(  $section = false, $section_name = false ){

        if ( ! $section ) {
            $section  = 'header_top';
        }
        if ( ! $section_name ) {
            $section_name = __( 'Header Top', 'customify' );
        }

        $selector = '#cb-row--'.str_replace('_', '-', $section );

        $fn = 'customify_customize_render_header';
        $selector_all = '#masthead';

        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title' => $section_name,
            ),

            array(
                'name' => $section.'_layout',
                'type' => 'select',
                'section' => $section,
                'title' => __( 'Layout', 'customify' ),
                'selector' => $selector,
                'css_format' => 'html_class',
                'render_callback' => $fn,
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
                'name' => $section.'_height',
                'type' => 'slider',
                'section' => $section,
                'theme_supports' => '',
                'device_settings' => true,
                'max' => 250,
                //'selector' => $selector.' .customify-grid, '.$selector,
                'selector' => $selector.' .customify-grid',
                'css_format' => 'min-height: {{value}};',
                'title' => __( 'Height', 'customify' ),
            ),

            array(
                'name' => $section.'_styling',
                'type' => 'group',
                'section' => $section,
                'title'  => __( 'Styling', 'customify' ),
                'live_title_field' => 'title',
                'field_class' => 'customify-background-control',
                'selector' => "{$selector} .customify-container, {$selector}.layout-full-contained, {$selector}.layout-fullwidth",
                'css_format' => 'styling', // styling
                'device_settings' => true,
                'default' => array(),
                'fields' => array(
                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __( 'Background Color', 'customify' ),
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __( 'Background Image', 'customify' ),
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

                    array(
                        'name' => 'border_style',
                        'type' => 'select',
                        'label' => __('Border Style', 'customify'),
                        'default' => 'none',
                        'choices' => array(
                            'none'      => __('None', 'customify'),
                            'solid'     => __('Solid', 'customify'),
                            'dotted'    => __('Dotted', 'customify'),
                            'dashed'    => __('Dashed', 'customify'),
                            'double'    => __('Double', 'customify'),
                            'ridge'     => __('Ridge', 'customify'),
                            'inset'     => __('Inset', 'customify'),
                            'outset'    => __('Outset', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'border_width',
                        'type' => 'css_ruler',
                        'label' => __('Border Width', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),
                    array(
                        'name' => 'border_color',
                        'type' => 'color',
                        'label' => __('Border Color', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),

                )
            ),

        );

        return $config;

    }
    function row_sidebar_config( $section , $section_name ){
        $selector = '#mobile-header-panel-inner';

        $config  = array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => $section_name,
            ),

            array(
                'name' => $section.'_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'selector' => $selector,
                'css_format' => array(
                    'top' => 'padding-top: {{value}};',
                    'right' => 'padding-right: {{value}};',
                    'bottom' => 'padding-bottom: {{value}};',
                    'left' => 'padding-left: {{value}};',
                ),
                'title' => __( 'Padding', 'customify' ),
            ),

            array(
                'name' => $section.'_styling',
                'type' => 'group',
                'section' => $section,
                'title' => __( 'Background', 'customify' ),
                'description' => __( 'This is description',  'customify' ),
                'live_title_field' => 'title',
                'field_class' => 'customify-background-control',
                'selector' => '#mobile-header-panel',
                'css_format' => 'styling',
                'default' => array(

                ),
                'fields' => array(
                    array(
                        'name' => 'color',
                        'type' => 'color',
                        'label' => __( 'Background Color', 'customify' ),
                        'device_settings' => false,
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'image',
                        'label' => __( 'Background Image', 'customify' ),
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
                            array('image', 'not_empty', ''),
                            array('cover', '!=', '1' ),
                        ),
                        'choices' => array(
                            'default' => __( 'Attachment', 'customify' ),
                            'scroll' => __( 'Scroll', 'customify' ),
                            'fixed' => __( 'Fixed', 'customify' )
                        ),
                    ),

                    array(
                        'name' => 'border_style',
                        'type' => 'select',
                        'label' => __('Border Style', 'customify'),
                        'default' => 'none',
                        'choices' => array(
                            'none'      => __('None', 'customify'),
                            'solid'     => __('Solid', 'customify'),
                            'dotted'    => __('Dotted', 'customify'),
                            'dashed'    => __('Dashed', 'customify'),
                            'double'    => __('Double', 'customify'),
                            'ridge'     => __('Ridge', 'customify'),
                            'inset'     => __('Inset', 'customify'),
                            'outset'    => __('Outset', 'customify'),
                        ),
                    ),

                    array(
                        'name' => 'border_width',
                        'type' => 'css_ruler',
                        'label' => __('Border Width', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),
                    array(
                        'name' => 'border_color',
                        'type' => 'color',
                        'label' => __('Border Color', 'customify'),
                        'required' => array('border_style', '!=', 'none'),
                    ),


                )
            ),

        );
        return $config;
    }

}

if ( ! function_exists( 'customify_header_merge_item_settings' ) ) {
    function customify_header_merge_item_settings( $item_id = '', $section = '' )
    {
        return array(
            'name' => 'header_'.$item_id.'_merge',
            'type' => 'select',
            'section' => $section,
            'selector' => '#masthead',
            'render_callback' => 'customify_customize_render_header',
            'priority' => 999,
            'device_settings' => true,
            'devices' => array( 'desktop', 'mobile' ),
            'title' => __('Merge Item', 'customify'),
            'description' => __('If you choose to merge this item, the alignment setting will inherit from the item you are merging.', 'customify'),
            'choices' => array(
                0 => __('No', 'customify'),
                'prev' => __('Merge with left item', 'customify'),
                'next' => __('Merge with right item', 'customify'),

            )
        );
    }
}
