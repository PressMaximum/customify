<?php
if ( ! function_exists( '_beacon_customizer_config' ) ) {
    function _beacon_customizer_config( $configs ){

        $config = array(
            array(
                'name' => '_beacon_panel',
                'type' => 'panel',
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Beacon Panel', '_beacon' ),
            ),

            array(
                'name' => '_beacon_section',
                'type' => 'section',
                'panel' => '_beacon_panel',
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Beacon Section', '_beacon' ),
                'description' => __( 'This is section description' ),
            ),

            array(
                'name' => 'device_select',
                'type' => 'device_select',
                'section' => '_beacon_section',
            ),

            array(
                'name' => 'text',
                'type' => 'text',
                'sanitize_callback' => '_beacon_sanitize_input',
                'default'           => null,
                'transport'			=> 'postMessage', // for selective refresh
                'section' => '_beacon_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Text', '_beacon' ),
                'description' => __( 'This is description' ),
            ),

            array(
                'name' => 'textarea',
                'type' => 'textarea',
                'default' => '',
                'section' => '_beacon_section',
                //'device' => 'mobile', // mobile || general
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Textarea', '_beacon' ),
                'description'    => '',
            ),

            array(
                'name'  => 'checkbox',
                'type'  => 'checkbox',
                'label'       => __( 'Checkbox', '_beacon' ),
                'section'     => '_beacon_section',
                'description' => __( 'This is description' ),
                'setting_type' => 'checkbox',
                'checkbox_label' => __( 'This is checkbox label' ),
            ),

            array(
                'name' => 'select',
                'type' => 'select',
                'default' => '',
                'section'     => '_beacon_section',
                //'priority' => 22,
                'title'          => __( 'Select', '_beacon' ),
                'description'    => '',
                'choices' => array(
                    '1' => __( 'One', '_beacon' ),
                    '2' => __( 'Two', '_beacon' ),
                    '3' => __( 'Three', '_beacon' ),
                )
            ),

            array(
                'name' => 'dependence_text',
                'type' => 'text',
                'default'           => null,
                'transport'			=> 'postMessage', // for selective refresh
                'section' => '_beacon_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Dependence Text Field', '_beacon' ),
                'description'   => __( 'This is description' ),
                'required'      => array( 'select','==','2' )
            ),

            array(
                'name' => 'radio',
                'type' => 'radio',
                'default' => '',
                'section'     => '_beacon_section',
                //'priority' => 22,
                'title'          => __( 'Radio', '_beacon' ),
                'description'    => __( 'This is description' ),
                'choices' => array(
                    '1' => __( 'One', '_beacon' ),
                    '2' => __( 'Two', '_beacon' ),
                    '3' => __( 'Three', '_beacon' ),
                )
            ),

            array(
                'name' => 'multiple_dependence_text',
                'type' => 'text',
                'default'           => null,
                'transport'			=> 'postMessage', // for selective refresh
                'section' => '_beacon_section',
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => __( 'Multiple Dependence Text Field', '_beacon' ),
                'description'   => __( 'Show only select=3 and radio=2' ),
                'required'      => array(
                    array( 'select','==','3' ),
                    array( 'radio','==','2' )
                )
            ),


            array(
                'name' => 'repeater',
                'type' => 'repeater',
                'section'     => '_beacon_section',
                //'priority' => 22,
                'title'          => __( 'Repeater', '_beacon' ),
                'description'    => __( 'This is description' ),
                'live_title_field' => 'title',
                'default' => array(
                    array(
                        'title' => __( 'Title 1', '_beacon' ),
                        'content' => __( 'Content 1', '_beacon' ),
                    ),
                    array(
                        'title' => __( 'Title 2', '_beacon' ),
                        'content' => __( 'Content 2', '_beacon' ),
                    )
                ),
                'fields' => array(
                    array(
                        'name' => 'title',
                        'type' => 'text',
                        'label' => __( 'Title', '_beacon' ),
                    ),
                    array(
                        'name' => 'image',
                        'type' => 'Image',
                        'label' => __( 'Image', '_beacon' ),
                    ),
                    array(
                        'name' => 'content',
                        'type' => 'textarea',
                        'label' => __( 'Textarea', '_beacon' ),
                    )
                )
            ),




        );

        return array_merge( $configs, $config );
    }
}

add_filter( '_beacon/customizer/config', '_beacon_customizer_config' );