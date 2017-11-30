<?php
/**
 * customify Theme Customizer
 *
 * @package customify
 */

// Load customizer config file.
require_once get_template_directory() . '/inc/customizer/customizer-config/layouts.php';
require_once get_template_directory() . '/inc/customizer/customizer-config.php';

require_once get_template_directory() . '/inc/customizer/customizer-fonts.php';
require_once get_template_directory() . '/inc/customizer/customizer-sanitize.php';
require_once get_template_directory() . '/inc/customizer/customizer-auto-css.php';

if ( ! class_exists( 'Customify_Customizer' ) ) {
    class  Customify_Customizer {
        static $config;
        static $_instance;
        static $has_icon = false;
        static $has_font = false;
        public $devices = array( 'desktop', 'tablet', 'mobile');
        private $selective_settings = array();
        function __construct()
        {
            add_action( 'customize_register', array( $this, 'register' ) );
            add_action( 'customize_preview_init', array( $this, 'preview_js' ) );
        }

        static function get_instance(){
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance ;
        }

        /**
         * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
         */
        function preview_js() {
            wp_enqueue_script( 'customify-customizer', get_template_directory_uri() . '/assets/js/customizer/customizer.js', array( 'customize-preview' ), '20151215', true );
            wp_localize_script( 'customify-customizer', 'Customify_Preview_Config_Fields', Customify_Customizer::get_config() );

        }


        static function get_config(){
            if ( is_null( self::$config  ) ) {

                $_config = apply_filters( 'customify/customizer/config', array() );
                $config = array();
                foreach ( $_config as $f ) {

                    $f = wp_parse_args( $f, array(

                        'priority'    => null,
                        'title'       => null,
                        'label'       => null,
                        'name'        => null,
                        'type'        => null,
                        'description' => null,

                        'capability' => null,
                        'mod' => null, // theme_mod || option default theme_mod

                        'settings' => null,

                        'device' => null,
                        'device_settings' => null,

                        // For settings
                        'sanitize_callback'     => 'customify_sanitize_customizer_input',
                        'sanitize_js_callback'  => null,
                        'theme_supports'        => null,
                        //'transport'             => 'postMessage', // refresh
                        'default' => null,

                        // for selective refresh
                        'selector'        => null,
                        'render_callback' => null,
                        'css_format'      => null,

                        // For control
                        'active_callback' => null,

                    ) );

                    if ( ! isset( $f['type'] ) )  {
                        $f['type'] = null;
                    }

                    switch ( $f['type'] ) {
                        case 'panel':
                            $config['panel|'.$f['name']] = $f;
                            break;
                        case 'section':
                            $config['section|'.$f['name']] = $f;
                            break;
                        default:
                            if ( $f['type'] == 'icon' ) {
                                self::$has_icon = true;
                            }

                            if ( $f['type'] == 'font' ) {
                                self::$has_font = true;
                            }

                            if ( isset( $f['fields'] ) ) {
                                $types = wp_list_pluck( $f['fields'], 'type' );
                                if ( in_array( 'icon', $types ) ) {
                                    self::$has_icon = true;
                                }

                                if ( in_array( 'font', $types ) ) {
                                    self::$has_font = true;
                                }
                            }
                            $config['setting|'.$f['name']] = $f;

                    }
                }
                self::$config = $config;
            }
            return self::$config;
        }

        /**
         * Check if has icon field;
         *
         * @return bool
         */
        function has_icon(){
            return self::$has_icon;
        }

        /**
         * Check if has font field;
         *
         * @return bool
         */
        function has_font(){
            return self::$has_icon;
        }

        /**
         *  Get Customizer setting.
         *
         * @param $name
         * @param string $device
         * @param bool $key
         * @return array|bool|mixed|null|string|void
         */
        function get_setting( $name, $device = 'desktop', $key = false ){
            $config = self::get_config();
            $get_value = null;
            if ( isset( $config['setting|'.$name ] ) ) {
                $default = isset( $config['setting|'.$name ]['default'] ) ? $config['setting|'.$name ]['default'] : false;
                if ( 'option' == $config['setting|'.$name]['mod'] ) {
                    $value =  get_option( $name, $default );
                } else {
                    $value =  get_theme_mod( $name, $default );
                }

                if ( ! $config['setting|'.$name ]['device_settings'] ) {
                    return $value;
                }

            } else {
                $value =  get_theme_mod( $name, null );
            }


            if ( ! $key ) {
                if ( $device != 'all' ) {
                    if ( is_array( $value ) && isset( $value[ $device ] ) ) {
                        $get_value =  $value[ $device ];
                    } else {
                        $get_value =  $value;
                    }
                } else {
                    $get_value = $value;
                }
            } else {
                $value_by_key = isset( $value[ $key ] ) ?  $value[ $key ]: false;
                if ( $device != 'all' && is_array( $value_by_key ) ) {
                    if ( is_array( $value_by_key ) && isset( $value_by_key[ $device ] ) ) {
                        $get_value =  $value_by_key[ $device ];
                    } else {
                        $get_value =  $value_by_key;
                    }
                } else {
                    $get_value = $value_by_key;
                }
            }

            return $get_value;
        }

        function setup_icon( $icon ){
            if ( ! is_array( $icon ) ) {
                $icon = array();
            }
            return wp_parse_args( $icon, array( 'type' =>'', 'icon' => '') );
        }

        function get_field_setting( $key ){
            $config = self::get_config();
            if ( isset($config['setting|'.$key] ) ) {
                return $config['setting|'.$key];
            }
            return false;
        }

        function get_media( $value, $size = null ) {
            if ( is_numeric( $value ) ) {
                if ( ! $size ) {
                    return wp_get_attachment_url( $value );
                } else {
                    $image_attributes = wp_get_attachment_image_src( $value = 8, $size );
                    if ( $image_attributes ) {
                        return $image_attributes[0];
                    } else {
                        return false;
                    }
                }
            }elseif ( is_string( $value ) ) {
                return $value;
            } elseif ( is_array( $value ) ) {
                $value = wp_parse_args( $value, array(
                    'id'    => '',
                    'url'   => '',
                    'mime'  => ''
                ) );

                $url = '';

                if ( strpos( $value['mime'], 'image/' ) !== false ) {
                    $image_attributes = wp_get_attachment_image_src( $value['id'], $size );
                    if ( $image_attributes ) {
                        $url =  $image_attributes[0];
                    }
                } else {
                    $url = wp_get_attachment_url( $value );
                }

                if ( ! $url ) {
                    $url = $value['url'];
                }

                return $url;

            }

            return false;
        }

        /**
         * Register Customize Settings
         *
         * @param $wp_customize
         */
        function register( $wp_customize ){
            require_once get_template_directory().'/inc/customizer/customizer-control.php';

            $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
            $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
            $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

            foreach ( self::get_config() as $args ) {
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

                        $args['setting_type'] = $args['type'];
                        $args['defaultValue'] = $args['default'];
                        $settings_args = array(
                           'sanitize_callback' => $args['sanitize_callback'],
                           'sanitize_js_callback' => $args['sanitize_js_callback'],
                           'theme_supports' => $args['theme_supports'],
                           //'transport' => $args['transport'],
                           'type' => $args['mod'],
                           'default' => $args['default'],
                        );

                        $settings_args['transport'] = 'refresh';
                        if ( ! $settings_args['sanitize_callback'] ) {
                            $settings_args['sanitize_callback'] = 'customify_sanitize_customizer_input';
                        }

                        foreach ( $settings_args as $k => $v ) {
                           unset( $args[ $k ] );
                        }

                        unset( $args['mod'] );
                        $name = $args['name'];
                        unset( $args['name'] );

                        unset( $args['type'] );
                        if ( ! $args['label'] ) {
                           $args['label'] =  $args['title'];
                        }

                        $selective_refresh = null;
                        if ( $args['selector'] && ( $args['render_callback'] || $args['css_format'] ) ) {
                            $selective_refresh= array(
                                'selector'  => $args['selector'],
                                'render_callback' => $args['render_callback'],
                            );

                            if ( $args['css_format'] ) {
                                $selective_refresh['selector'] = '#customify-style-inline-css';
                                $selective_refresh['render_callback'] = 'Customify_Customizer_Auto_CSS';
                            }

                            $settings_args['transport'] = 'postMessage';
                        }
                        unset( $args['default'] );


                        $wp_customize->add_setting( $name, $settings_args );
                        $wp_customize->add_control( new Customify_Customizer_Control( $wp_customize, $name, $args ));
                        if ( $selective_refresh ) {
                            $s_id = $selective_refresh['render_callback'];
                            if ( ! isset( $this->selective_settings[ $s_id ] ) ) {
                                $this->selective_settings[ $s_id ] = array(
                                    'settings' => array(),
                                    'selector' => $selective_refresh['selector'],
                                    'container_inclusive' => $s_id == 'Customify_Customizer_Auto_CSS' ? false : true,
                                    'render_callback' => $s_id ,
                                );

                            }

                            $this->selective_settings[ $s_id ]['settings'][] = $name;
                            //$wp_customize->selective_refresh->add_partial( $name, $selective_refresh );
                        }

                        break;
                }// end switch

            } // End loop config

            // add selective refresh
            // remove_partial
            $wp_customize->selective_refresh->remove_partial( 'custom_logo' );
            foreach ( $this->selective_settings as $cb => $settings ){
                $name = current( $settings['settings'] );
                reset( $settings['settings'] );
                if ( $cb == 'customify_builder_logo_item' ){
                    $settings['settings'][] = 'custom_logo';
                    $settings['settings'][] = 'blogname';
                    $settings['settings'][] = 'blogdescription';
                }
                $wp_customize->selective_refresh->add_partial( $name, $settings );
            }

            do_action( 'customify/customize/register_completed', $this );
        }

    }
}

if ( ! function_exists( 'Customify_Customizer' ) ) {
    function Customify_Customizer(){
        return Customify_Customizer::get_instance();
    }
}
Customify_Customizer();


/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function customify_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function customify_customize_partial_blogdescription() {
	bloginfo( 'description' );
}


