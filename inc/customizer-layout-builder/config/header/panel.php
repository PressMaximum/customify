<?php
if ( ! function_exists( 'customify_customizer_get_header_config' ) ) {
    function customify_customizer_get_header_config( $configs = array() ){

        $config = array(
            array(
                'name' => 'header_settings',
                'type' => 'panel',
                'theme_supports' => '',
                'title'          => __( 'Header', 'customify' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'section',
                'panel' => 'header_settings',
                'theme_supports' => '',
                'title'          => __( 'Header Builder', 'customify' ),
                'description' => __( 'This is section description',  'customify' ),
            ),

            array(
                'name' => 'header_builder_panel',
                'type' => 'js_raw',
                'section' => 'header_builder_panel',
                'theme_supports' => '',
                'title'          => __( 'Header Builder', 'customify' ),
                'description' => __( 'Header Builder panel here....',  'customify' ),
                'selector' => '#masthead',
                'render_callback' => 'customify_customize_render_header',
                'container_inclusive' => true
            ),

        );

        foreach ( Customify_Customizer_Layout_Builder::get_header_sections() as $id ) {
            $file = get_template_directory().'/inc/customizer-layout-builder/config/header/'.$id.'.php';
            if (  is_file( $file ) ) {
                require_once get_template_directory().'/inc/customizer-layout-builder/config/header/'.$id.'.php';
            }

            $func_id = str_replace( '-', '_', $id );

            if ( function_exists( 'customify_builder_config_header_'.$func_id ) ) {
                $config = array_merge( $config, call_user_func_array( 'customify_builder_config_header_'.$func_id, array() ) );
            }
        }

        return array_merge( $configs, $config );
    }
}

add_filter( 'customify/customizer/config', 'customify_customizer_get_header_config' );


function customify_builder_config_header_row_config( $section = false, $section_name = false ){
    if ( ! $section ) {
        $section  = 'header_top';
    }
    if ( ! $section_name ) {
        $section_name = __( 'Header Top', 'customify' );
    }

    $config  = array(
        array(
            'name' => $section,
            'type' => 'section',
            'panel' => 'header_settings',
            'theme_supports' => '',
            'title'          => $section_name,
        ),

        array(
            'name' => $section.'_sticky',
            'type' => 'select',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'Sticky header', 'customify' ),
            'choices' => array(
                'no' =>  __( 'No', 'customify' ),
                'yes' =>  __( 'Yes', 'customify' ),
            )
        ),

        array(
            'name' => $section.'_layout',
            'type' => 'select',
            'section' => $section,
            'theme_supports' => '',
            'title'          => __( 'Layout', 'customify' ),
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
            'title'          => __( 'Sticky header', 'customify' ),
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
            'title'          => __( 'Padding', 'customify' ),
        ),

        array(
            'name' => $section.'_background',
            'type' => 'group',
            'section'     => $section,
            'title'          => __( 'Background', 'customify' ),
            'description'    => __( 'This is description',  'customify' ),
            'live_title_field' => 'title',
            'field_class' => 'customify-background-control',
            'selector' => '#page',
            'css_format' => 'background',
            'device_settings' => true,
            'default' => array(

            ),
            'fields' => array(
                array(
                    'name' => 'color',
                    'type' => 'color',
                    'label' => __( 'Color', 'customify' ),
                    'device_settings' => true,
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
                    'label' => __( 'Background cover', 'customify' ),
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


function customify_builder_config_header_row_render( $row_id ){

}
