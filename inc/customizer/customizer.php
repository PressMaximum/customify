<?php
/**
 * customify Theme Customizer
 *
 * @package customify
 */

// Load customizer config file.
require_once get_template_directory() . '/inc/customizer/customizer-config/layouts.php';
require_once get_template_directory() . '/inc/customizer/customizer-config/blogs.php';


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

            add_action( 'wp_ajax_customify/customizer/ajax/get_icons', array( $this, 'get_icons' ) );

        }

        static function get_instance(){
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance ;
        }

        /**
         * Reset Customize section
         */
        function get_icons(){
            if ( ! current_user_can( 'customize' ) ) {
                wp_send_json_error();
            }

            require_once get_template_directory().'/inc/customizer/customizer-icons.php';
            wp_send_json_success( Customify_Font_Icons()->get_icons() );
            die();
        }

        /**
         * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
         */
        function preview_js() {
            if ( is_customize_preview() ) {
                wp_enqueue_script('customify-customizer-auto-css', get_template_directory_uri() . '/assets/js/customizer/auto-css.js', array('customize-preview'), '20151215', true);
                wp_enqueue_script('customify-customizer', get_template_directory_uri() . '/assets/js/customizer/customizer.js', array('customize-preview', 'customize-selective-refresh'), '20151215', true);
                wp_localize_script('customify-customizer-auto-css', 'Customify_Preview_Config', array(
                    'fields' => Customify_Customizer::get_config(),
                    'devices' => $this->devices,
                    'typo_fields' => Customify_Customizer()->get_typo_fields(),
                ));
            }
        }


        static function get_config( $wp_customize = null ){
            if ( is_null( self::$config  ) ) {

                $_config = apply_filters( 'customify/customizer/config', array(), $wp_customize );
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

                        'field_class' => null,

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

        function get_typo_fields() {
            $typo_fields =array(
                array(
                    'name' => 'font',
                    'type' => 'select',
                    'label' => __('Font', 'customify'),
                    'choices' => array()
                ),

                array(
                    'name' => 'font_weight',
                    'type' => 'select',
                    'label' => __('Font Weight', 'customify'),
                    'choices' => array()
                ),

                array(
                    'name' => 'languages',
                    'type' => 'checkboxes',
                    'label' => __('Font Languages', 'customify'),
                ),

                array(
                    'name' => 'font_size',
                    'type' => 'slider',
                    'label' => __('Font Size', 'customify'),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'line_height',
                    'type' => 'slider',
                    'label' => __('Line Height', 'customify'),
                    'device_settings' => true,
                ),

                array(
                    'name' => 'letter_spacing',
                    'type' => 'slider',
                    'label' => __('Letter Spacing', 'customify'),
                    'min' => -10,
                    'max' => 10,
                ),

                array(
                    'name' => 'style',
                    'type' => 'select',
                    'label' => __('Font Style', 'customify'),
                    'choices' => array(
                        '' =>__( 'Default', 'customify' ),
                        'normal' =>__( 'Normal', 'customify' ),
                        'italic' =>__( 'Italic', 'customify' ),
                        'oblique' =>__( 'Oblique', 'customify' ),
                    )
                ),

                array(
                    'name' => 'text_decoration',
                    'type' => 'select',
                    'label' => __('Text Decoration', 'customify'),
                    'choices' => array(
                        '' =>__( 'Default', 'customify' ),
                        'underline' =>__( 'Underline', 'customify' ),
                        'overline' =>__( 'Overline', 'customify' ),
                        'line-through' =>__( 'Line through', 'customify' ),
                        'none' =>__( 'None', 'customify' ),
                    )
                ),

                array(
                    'name' => 'text_transform',
                    'type' => 'select',
                    'label' => __('Text Transform', 'customify'),
                    'choices' => array(
                        '' =>__( 'Default', 'customify' ),
                        'uppercase' =>__( 'Uppercase', 'customify' ),
                        'lowercase' =>__( 'Lowercase', 'customify' ),
                        'capitalize' =>__( 'Capitalize', 'customify' ),
                        'none' =>__( 'None', 'customify' ),
                    )
                )
            );

            return $typo_fields;
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

            if ( ! $size ) {
                $size = 'full';
            }

            if ( is_numeric( $value ) ) {
                $image_attributes = wp_get_attachment_image_src( $value, $size );
                if ( $image_attributes ) {
                    return $image_attributes[0];
                } else {
                    return false;
                }
            } elseif ( is_string( $value ) ) {
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
                        $url = $image_attributes[0];
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

            foreach ( self::get_config( $wp_customize ) as $args ) {
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

                        switch ( $args['type'] ) {
                            case  'image_select':
                                $args['setting_type'] = 'radio';
                                $args['field_class'] = 'custom-control-image_select'. ( $args['field_class'] ? ' '.$args['field_class'] : '' );
                                break;
                            case  'radio_group':
                                $args['setting_type'] = 'radio';
                                $args['field_class'] = 'custom-control-radio_group'. ( $args['field_class'] ? ' '.$args['field_class'] : '' );
                                break;
                            default:
                                $args['setting_type'] = $args['type'];
                        }


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
                                //$selective_refresh['selector'] = '#customify-style-inline-css';
                               // $selective_refresh['render_callback'] = 'Customify_Customizer_Auto_CSS';
                                $settings_args['transport'] = 'postMessage';
                                $selective_refresh = null;
                            } else {
                                $settings_args['transport'] = 'postMessage';
                            }


                        }
                        unset( $args['default'] );

                        $wp_customize->add_setting( $name, $settings_args );
                        if ( $settings_args['type'] != 'js_raw' ) {
                            $wp_customize->add_control( new Customify_Customizer_Control( $wp_customize, $name, $args ));
                        }

                        if ( $selective_refresh ) {
                            $s_id = $selective_refresh['render_callback'];
                            $__id = '';
                            if ( is_array( $s_id ) ) {
                                $__id = get_class( $s_id[0] ).'__'.$s_id[1];
                            } else {
                                $__id = $s_id;
                            }
                            if ( ! isset( $this->selective_settings[ $__id ] ) ) {
                                $this->selective_settings[ $__id ] = array(
                                    'settings' => array(),
                                    'selector' => $selective_refresh['selector'],
                                    'container_inclusive' => $s_id == 'Customify_Customizer_Auto_CSS' ? false : true,
                                    'render_callback' => $s_id ,
                                ) ;

                            }

                            $this->selective_settings[ $__id ]['settings'][] = $name;
                        }

                        break;
                }// end switch

            } // End loop config

            // remove_partial
            $wp_customize->selective_refresh->remove_partial( 'custom_logo' );


            $wp_customize->get_section( 'title_tagline' )->panel = 'header_settings';
            $wp_customize->get_section( 'title_tagline' )->title = __( 'Logo & Site Identity' );

            $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
            // add selective refresh
            $wp_customize->get_setting( 'custom_logo' )->transport         = 'postMessage';
            $wp_customize->get_setting( 'blogname' )->transport  = 'postMessage';
            $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

            foreach ( $this->selective_settings as $cb => $settings ){
                reset( $settings['settings'] );
                if ( $cb == 'Customify_Builder_Item_Logo__render' ){
                    $settings['settings'][] = 'custom_logo';
                    $settings['settings'][] = 'blogname';
                    $settings['settings'][] = 'blogdescription';
                }
                $settings = apply_filters( $cb, $settings );
                $wp_customize->selective_refresh->add_partial( $cb , $settings );
            }

            // For live CSS

            $wp_customize->add_setting( 'customify__css' , array(
                'default' => '',
                'transport' => 'postMessage',
                'sanitize_callback' => 'customify_sanitize_css_code',
            ) );

            do_action( 'customify/customize/register_completed', $this );
        }

    }
}

function customify_sanitize_css_code( $val ){
    return wp_kses_post( $val );
}

if ( ! function_exists( 'Customify_Customizer' ) ) {
    function Customify_Customizer(){
        return Customify_Customizer::get_instance();
    }
}
Customify_Customizer();


/**
 * Reset Customize section
 */
function customify__reset_customize_section(){
    if ( ! current_user_can( 'customize' ) ) {
        wp_send_json_error();
    }

    $settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();

    foreach( $settings as $k ) {
        $k = sanitize_text_field( $k );
        remove_theme_mod( $k );
    }

    wp_send_json_success();
}
add_action( 'wp_ajax_customify__reset_section', 'customify__reset_customize_section' );


