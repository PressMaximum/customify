<?php
if ( ! function_exists( 'customify_customizer_get_footer_config' ) ) {
    function customify_customizer_get_footer_config( $configs = array() ){
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
                'selector' => '#masthead',
                'render_callback' => $fn,
                'container_inclusive' => true
            ),

        );

        foreach ( Customify_Customizer_Layout_Builder::get_footer_sections() as $id ) {
            $file = get_template_directory().'/inc/customizer-layout-builder/config/footer/'.$id.'.php';
            if (  is_file( $file ) ) {
                require_once get_template_directory().'/inc/customizer-layout-builder/config/footer/'.$id.'.php';
            }

            $func_id = str_replace( '-', '_', $id );

            if ( function_exists( 'customify_builder_config_footer_'.$func_id ) ) {
                $config = array_merge( $config, call_user_func_array( 'customify_builder_config_footer_'.$func_id, array() ) );
            }
        }

        return array_merge( $configs, $config );
    }
}

add_filter( 'customify/customizer/config', 'customify_customizer_get_footer_config' );


function customify_builder_config_footer_row_config( $section = false, $section_name = false ){
    if ( ! $section ) {
        $section  = 'footer_top';
    }
    if ( ! $section_name ) {
        $section_name = __( 'Footer Top', 'customify' );
    }

    $selector = '#cb-row--'.str_replace('_', '-', $section );

    $fn = 'customify_customize_render_footer';
    $selector_all = '#masthead';

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
            'selector' => $selector_all,
            'render_callback' => $fn,
            'choices' => array(
                'default' =>  __( 'Default', 'customify' ),
                'fullwidth' =>  __( 'Full Width', 'customify' ),
                'boxed' =>  __( 'Boxed', 'customify' ),
            )
        ),

        array(
            'name' => $section.'_sticky',
            'type' => 'select',
            'section' => $section,
            'theme_supports' => '',
            'title' => __( 'Sticky footer', 'customify' ),
            'selector' => $selector_all,
            'render_callback' => $fn,
            'choices' => array(
                'no' =>  __( 'No', 'customify' ),
                'yes' =>  __( 'Yes', 'customify' ),
            )
        ),

        array(
            'name' => $section.'_padding',
            'type' => 'css_ruler',
            'section' => $section,
            'theme_supports' => '',
            'device_settings' => true,
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
            'name' => $section.'_background',
            'type' => 'group',
            'section'     => $section,
            'title'          => __( 'Background', 'customify' ),
            'live_title_field' => 'title',
            'field_class' => 'customify-background-control',
            'selector' => $selector,
            'css_format' => 'background',
            'device_settings' => true,
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
                        array('image', 'not_empty', ''),
                        array('cover', '!=', '1' ),
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

function customify_change_footer_widgets_location( $wp_customize ){
    for ( $i = 1; $i<= 4; $i ++ ) {
        if (  $wp_customize->get_section( 'sidebar-widgets-footer-'.$i ) ) {
            $wp_customize->get_section( 'sidebar-widgets-footer-'.$i )->panel = 'footer_settings';
        }
    }


}

add_action( 'customize_register', 'customify_change_footer_widgets_location', 199 );


function customify_builder_config_footer_row_render( $row_id ){
    echo "Footer";
}
