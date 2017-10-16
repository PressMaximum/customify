<?php
/**
 * _beacon Theme Customizer
 *
 * @package _beacon
 */

require get_template_directory() . '/inc/customizer/customizer-config.php';

if ( ! class_exists( '_Beacon_Customizer' ) ) {
    class  _Beacon_Customizer {
        private $fields = array();
        private $panels = array();
        private $sections = array();
        private $config = array();

        function __construct()
        {
            add_action( 'customize_register', array( $this, 'register' ) );
        }

        function get_config(){
            $this->config = apply_filters( '_beacon/customizer/config', array() );
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

            foreach ( $this->config as $args ) {
                $args = wp_parse_args( $args, array(
                    'priority'    => null,
                    'title'       => null,
                    'label'       => null,
                    'name'        => null,
                    'type'        => null,
                    'description' => null,
                    'capability' => null,
                ) );
                switch (  $args['type'] ) {
                    case  'panel':
                        $name = $args['name'];
                        unset( $args['name'] );
                        if ( ! $args['title'] ) {
                            $args['title'] = $args['label'];
                        }
                        if ( ! $name ) {
                            $name = $args['title'];
                        }
                        $wp_customize->add_panel( $name, $args );
                        break;
                    case 'section':
                        $name = $args['name'];
                        unset( $args['name'] );
                        if ( ! $args['title'] ) {
                            $args['title'] = $args['label'];
                        }
                        if ( ! $name ) {
                            $name = $args['title'];
                        }
                        $wp_customize->add_section( $name, $args );
                        break;
                    default:

                        $args = wp_parse_args( $args, array(
                            'priority'    => null,
                            'title'       => null,
                            'label'       => null,
                            'name'        => null,
                            'type'        => null,
                            'description' => null,
                            'capability' => null,

                            'device' => null,

                            // For settings
                            'sanitize_callback'     => '_beacon_sanitize_input',
                            'sanitize_js_callback'  => null,
                            'theme_supports'        => null,
                            'transport'             => null,
                            'default' => null,

                            // for selective refresh
                            'selector'        => null,
                            'render_callback' => null,

                            // For control
                            'active_callback' => null,

                        ) );

                        // _beacon_sanitize_input

                       $settings_args = array(
                           'sanitize_callback' => $args['sanitize_callback'],
                           'sanitize_js_callback' => $args['sanitize_js_callback'],
                           'theme_supports' => $args['theme_supports'],
                           'transport' => $args['transport'],
                           'default' => $args['default'],
                       );
                       foreach ( $settings_args as $k => $v ) {
                           unset( $args[ $k ] );
                       }
                       $name = $args['name'];
                       unset( $args['name'] );
                       $args['setting_type'] = $args['type'];
                       unset( $args['type'] );
                       if ( ! $args['label'] ) {
                           $args['label'] =  $args['title'];
                       }

                        $selective_refresh = null;
                        if ( $args['selector'] ) {
                            $selective_refresh= array(
                                'selector'  => $args['selector'],
                                'render_callback' => $args['render_callback'],
                            );
                        }
                        unset( $args['default'] );

                        $wp_customize->add_setting( $name, $settings_args );
                        $wp_customize->add_control( new _Beacon_Customizer_Control( $wp_customize, $name, $args ));
                        if ( $selective_refresh ) {
                            $wp_customize->selective_refresh->add_partial( $name, $selective_refresh );
                        }


                        break;
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
