<?php
class Customify_Builder_Item_Primary_Menu {
    public  $id = 'primary-menu';
    function item(){
        return array(
            'name' => __( 'Primary Menu', 'customify' ),
            'id' => 'primary-menu',
            'width' => '6',
            'section' => 'header_menu_primary' // Customizer section to focus when click settings
        );
    }


    function customize() {
        $section = 'header_menu_primary';
        $fn = array( $this, 'render' );
        return array(
            array(
                'name' => $section,
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title' => __( 'Primary Menu', 'customify' ),
                'description' => __( 'Assign <a href="#menu_locations"  class="focus-section">Menu Location</a> for Primary menu', 'customify' )
            ),

            array(
                'name' => 'primary_menu_style',
                'type' => 'select',
                'section' => $section,
                'selector' => '.primary-menu',
                'render_callback' => $fn,
                'title' => __( 'Style', 'customify' ),
                'choices' => array(
                    'style_default' => __( 'Default', 'customify' ),
                    'style_2' => __( 'Style 2', 'customify' ),
                )
            ),

            array(
                'name' => 'primary_menu_item_padding',
                'type' => 'css_ruler',
                'section' => $section,
                'title' => __( 'Item Padding', 'customify' ),
                'selector' => '.primary-menu li a',
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
                'name' => 'primary_menu_item_margin',
                'type' => 'css_ruler',
                'section' => $section,
                'selector' => '.primary-menu .menu li',
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
                'name' => 'primary_menu_item_color',
                'type' => 'color',
                'section' => $section,
                'title'  => __( 'Item Color', 'customify' ),
                'selector'  => '.primary-menu li a, .primary-menu li',
                'device_settings' => true,
                'css_format'  => 'color: {{value}};',
            ),

            array(
                'name' => 'primary_menu_item_color_hover',
                'type' => 'color',
                'section' => $section,
                'title' => __( 'Item Color Hover', 'customify' ),
                'device_settings' => true,
                'selector'  => '.primary-menu li a:hover, .primary-menu li:hover > span',
                'css_format'  => 'color: {{value}};',
            ),

            array(
                'name' => 'primary_menu_typography',
                'type' => 'group',
                'section'     => $section,
                'title'          => __( 'Typography', 'customify' ),
                'description'    => __( 'This is description',  'customify' ),
                'field_class' => 'customify-typography-control',
                'selector' => '.primary-menu',
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
                        'label' => __( 'Height', 'customify' ),
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
                'name' => 'header_primary_menu_align',
                'type' => 'text_align_no_justify',
                'section' => $section,
                'device_settings' => false,
                'selector' => '.builder-item--primary-menu',
                'css_format' => 'text-align: {{value}};',
                'title'   => __( 'Align', 'customify' ),
                'description'   => __( 'Apply for desktop only.', 'customify' ),
            ),

        );
    }


    function render(){

        $style = sanitize_text_field( Customify_Customizer()->get_setting('primary_menu_style') );

        wp_nav_menu( array(
            'theme_location' => 'menu-1',
            'container' => 'nav',
            'container_id' => 'site-navigation-__id__-__device__',
            'container_class' => 'primary-menu nav-menu-__device__ primary-menu-__device__'.( $style ? ' '.$style : '' ),
            'menu_id'        => 'primary-menu-__id__-__device__',
            'fallback_cb' => false
        ) );

    }

}


Customify_Customizer_Layout_Builder()->register_item('header', new Customify_Builder_Item_Primary_Menu() );
