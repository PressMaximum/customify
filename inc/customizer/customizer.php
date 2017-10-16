<?php
/**
 * _beacon Theme Customizer
 *
 * @package _beacon
 */


if ( ! class_exists( '_Beacon_Customizer' ) ) {
    class  _Beacon_Customizer {
        private $fields = array();
        private $panels = array();
        private $sections = array();

        function __construct()
        {
            add_action( 'customize_register', array( $this, 'register' ) );
        }

        function get_config(){

            $this->add_panel( '_beacon_test', array(
                //'priority' => 22,
                'theme_supports' => '',
                'title'          => esc_html__( 'Beacon Test', '_beacon' ),
                'description'    => '',
            ) );

            $this->add_section( '_beacon_test_section', array(
                'priority'    => 3,
                'title'       => esc_html__( 'Test Section', '_beacon' ),
                'description' => '',
                'panel'       => '_beacon_test',
            ) );


            $this->add_setting( 'test_settings_device', array(
                //'priority'    => 1,
                'section'     => '_beacon_test_section',
                'setting_type' => 'device_select'
            ) );

            $this->add_setting( '_text', array(
                'label'       => __( 'Text', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                //'priority'    => 1,
                'setting_type' => 'text', // text, color, image, textarea,..: if use only one, group: if have more than 1 fields. tabs if you use tab
            ) );

            $this->add_setting( '_textarea', array(
                'label'       => __( 'Textarea', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                //'priority'    => 1,
                'setting_type' => 'textarea',
            ) );

            $this->add_setting( '_select', array(
                'label'       => __( 'Select', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                //'priority'    => 1,
                'setting_type' => 'select',
                'choices' => array(
                    '1' => __( 'One', '_beacon' ),
                    '2' => __( 'Two', '_beacon' ),
                    '3' => __( 'Three', '_beacon' ),
                )
            ) );

            $this->add_setting( '_checkbox', array(
                'label'       => __( 'Checkbox', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                'setting_type' => 'checkbox',
                'checkbox_label' => __( 'This is checkbox label' ),
            ) );


            $this->add_setting( '_radio', array(
                'label'       => __( 'Radio', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                //'priority'    => 1,
                'setting_type' => 'radio',
                'choices' => array(
                    '1' => __( 'One', '_beacon' ),
                    '2' => __( 'Two', '_beacon' ),
                    '3' => __( 'Three', '_beacon' ),
                )
            ) );

            $this->add_setting( '_image', array(
                'label'       => __( 'Image', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description' ),
                'setting_type' => 'image',
            ) );

            $this->add_setting( '_repeater', array(
                'label'       => __( 'Repeater', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description', '_beacon' ),
                //'priority'    => 1,
                'setting_type' => 'repeater', // text, color, image, textarea,..: if use only one, group: if have more than 1 fields. tabs if you use tab
                'live_title_field' => 'text_name',
                'fields' => array(
                    array(
                        'type' => 'text',
                        'name' => 'text',
                        'label' => __( 'Text Field', '_beacon' ),
                    ),
                    array(
                        'type' => 'image',
                        'name' => 'image',
                        'label' => __( 'Image Field', '_beacon' )
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'textarea',
                        'label' => __( 'Textarea Field', '_beacon' )
                    )
                )
            ) );


            $this->add_setting( '_group', array(
                'label'       => esc_html__( 'Group', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description', '_beacon' ),
                //'priority'    => 1,
                'setting_type' => 'group', // text, color, image, textarea,..: if use only one, group: if have more than 1 fields. tabs if you use tab
                'fields' => array(
                    array(
                        'type' => 'text',
                        'name' => 'text_name',
                        'label' => __( 'Text Field', '_beacon' ),
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'textarea_name',
                        'label' => __( 'Textarea Field', '_beacon' )
                    )
                )
            ) );


            $this->add_setting( '_general', array(
                'label'       => esc_html__( 'General Group', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description', '_beacon' ),
                //'priority'    => 1,
                'device'        => 'general',
                'setting_type' => 'group', // text, color, image, textarea,..: if use only one, group: if have more than 1 fields. tabs if you use tab
                'fields' => array(
                    array(
                        'type' => 'text',
                        'name' => 'text_name',
                        'label' => __( 'Text Field', '_beacon' ),
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'textarea_name',
                        'label' => __( 'Textarea Field', '_beacon' )
                    )
                )
            ) );


            $this->add_setting( '_mobile', array(
                'label'       => esc_html__( 'Mobile Group', '_beacon' ),
                'section'     => '_beacon_test_section',
                'description' => __( 'This is description', '_beacon' ),
                //'priority'    => 1,
                'device'        => 'mobile',
                'setting_type' => 'group', // text, color, image, textarea,..: if use only one, group: if have more than 1 fields. tabs if you use tab
                'fields' => array(
                    array(
                        'type' => 'text',
                        'name' => 'text_name',
                        'label' => __( 'Text Field', '_beacon' ),
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'textarea_name',
                        'label' => __( 'Textarea Field', '_beacon' )
                    )
                )
            ) );




            $this->panels   = apply_filters('_beacon/customizer/panels', $this->panels, $this );
            $this->sections = apply_filters('_beacon/customizer/sections', $this->sections, $this );
            $this->fields   = apply_filters('_beacon/customizer/fields', $this->fields, $this );
        }

        function add_panel( $name, $args ){
            $this->panels[ $name ] = $args;
        }

        function add_section( $name, $args ){
            $this->sections[ $name ] = $args;
        }

        function add_setting( $name, $args ){
            $this->fields[ $name ] = $args;
        }

        function register( $wp_customize ){
            require_once get_template_directory().'/inc/customizer/customizer-control.php';

            $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
            $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
            $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

            if ( isset( $wp_customize->selective_refresh ) ) {
                $wp_customize->selective_refresh->add_partial( 'blogname', array(
                    'selector'        => '.site-title a',
                    'render_callback' => '_beacon_customize_partial_blogname',
                ) );
                $wp_customize->selective_refresh->add_partial( 'blogdescription', array(
                    'selector'        => '.site-description',
                    'render_callback' => '_beacon_customize_partial_blogdescription',
                ) );
            }

            $this->get_config();

            foreach ( $this->panels as $name => $args ) {
                $wp_customize->add_panel( $name, $args );
            }

            foreach ( $this->sections as $name => $args ) {
                $wp_customize->add_section( $name, $args );
            }

            foreach ( $this->fields as $name => $args ) {
                $_settings = array();
                if ( isset( $args['_settings'] ) ) {
                    $_settings =  $args['_settings'];
                }

                //unset( $args['_settings'] );
                $_settings = wp_parse_args( $_settings, array(
                    'sanitize_callback' => '_beacon_sanitize_input',
                    'default'           => null,
                    //'transport'			=> 'postMessage' // for selective refresh
                ) );
                $selective_refresh = false;
                if ( isset( $args['_selective_refresh'] ) ) {
                    $selective_refresh = $args['_selective_refresh'];
                    //unset( $args['_selective_refresh' ]);
                    $selective_refresh = wp_parse_args( $selective_refresh, array(
                        'selector'        => '',
                        'render_callback' => '',
                    ) );
                }

                $wp_customize->add_setting( $name, $_settings );
                $wp_customize->add_control( new _Beacon_Customizer_Control( $wp_customize, $name, $args ));
                if ( $selective_refresh ) {
                    $wp_customize->selective_refresh->add_partial( $name, $selective_refresh );
                }

            }

        }
    }
}


new _Beacon_Customizer();

if ( ! function_exists( '_beacon_sanitize_input' ) ) {
    function _beacon_sanitize_input( $input ){
        $input = wp_unslash( $input );
        if ( ! is_array( $input ) ) {
            $input = json_decode( urldecode_deep( $input ) );
        }
        return $input;
    }
}




function _beacon_sanitize_select( $input, $setting ) {
    $input = sanitize_key( $input );
    $choices = $setting->manager->get_control( $setting->id )->choices;
    return ( array_key_exists( $input, $choices ) ? $input : $setting->default );
}

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function _beacon_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function _beacon_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function _beacon_customize_preview_js() {
	wp_enqueue_script( '_beacon-customizer', get_template_directory_uri() . '/assets/js/customizer/customizer.js', array( 'customize-preview' ), '20151215', true );
}
add_action( 'customize_preview_init', '_beacon_customize_preview_js' );
