<?php
/**
 * _beacon Theme Customizer
 *
 * @package _beacon
 */

require get_template_directory() . '/inc/customizer/customizer-config.php';
require get_template_directory() . '/inc/customizer/customizer-auto-css.php';

if ( ! class_exists( '_Beacon_Customizer' ) ) {
    class  _Beacon_Customizer {
        static $config;
        static $_instance;
        static $has_icon = false;
        public $devices = array( 'desktop', 'tablet', 'mobile');
        function __construct()
        {
            add_action( 'customize_register', array( $this, 'register' ) );
        }

        static function get_instance(){
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance ;
        }

        static function get_config(){
            if ( is_null( self::$config  ) ) {

                $_config = apply_filters( '_beacon/customizer/config', array() );
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

                        'device' => null,
                        'device_settings' => null,

                        // For settings
                        'sanitize_callback'     => '_beacon_sanitize_customizer_input',
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
                            $config['setting|'.$f['name']] = $f;

                    }
                }
                self::$config = $config;
            }
            return self::$config;
        }

        function has_icon(){
            return self::$has_icon;
        }

        function get_setting( $key, $device = 'desktop' ){
            $config = self::get_config();
            if ( isset( $config['setting|'.$key ] ) ) {
                $default = isset( $config['setting|'.$key ]['default'] ) ? $config['setting|'.$key ]['default'] : false;
                $value =  get_theme_mod( $key, $default );
                if ( $device != 'all' ) {
                    if ( is_array( $value ) && isset( $value[ $device ] ) ) {
                        return $value[ $device ];
                    } else {
                        return $value;
                    }
                }
                return $value;
            } else {
                return null;
            }
        }

        function get_field_setting( $key ){
            $config = self::get_config();
            if ( isset($config['setting|'.$key] ) ) {
                return $config['setting|'.$key];
            }
            return false;
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

                       $settings_args = array(
                           'sanitize_callback' => $args['sanitize_callback'],
                           'sanitize_js_callback' => $args['sanitize_js_callback'],
                           'theme_supports' => $args['theme_supports'],
                           //'transport' => $args['transport'],
                           'default' => $args['default'],
                       );
                        $settings_args['transport'] = 'refresh';
                        if ( ! $settings_args['sanitize_callback'] ) {
                            $settings_args['sanitize_callback'] = '_beacon_sanitize_customizer_input';
                        }

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
                        if ( $args['selector'] && ( $args['render_callback'] || $args['css_format'] ) ) {
                            $selective_refresh= array(
                                'selector'  => $args['selector'],
                                'render_callback' => $args['render_callback'],
                            );

                            if ( $args['css_format'] ) {
                                $selective_refresh['render_callback'] = '_Beacon_Customizer_Auto_CSS';
                            }

                            $settings_args['transport'] = 'postMessage';
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



if ( ! function_exists( '_Beacon_Customizer' ) ) {
    function _Beacon_Customizer(){
        return _Beacon_Customizer::get_instance();
    }
}
_Beacon_Customizer();


if ( ! function_exists( '_beacon_sanitize_customizer_input' ) ) {

    class _Beacon_Sanitize_Input {

        private $control;
        private $setting;
        private $fonts = array();
        private $icons = array();

        function __construct( $control, $setting )
        {
            $this->control = $control;
            $this->setting = $setting;
        }

        private function sanitize_css_ruler( $value ){
            $default = array(
                'unit' => 'px',
                'top' => null,
                'right' => null,
                'bottom' => null,
                'left' => null,
                'link' => 1,
            );
            $value = wp_parse_args( $value, $default );
            $new_value = array();
            $new_value['unit'] = sanitize_text_field( $value['unit'] );
            $new_value['top'] = sanitize_text_field( $value['top'] );
            $new_value['right'] = sanitize_text_field( $value['right'] );
            $new_value['bottom'] = sanitize_text_field( $value['bottom'] );
            $new_value['link'] = $value['link'] ? 1 : null;
            return $new_value;
        }

        private function sanitize_slider( $value ){
            $default = array(
                'unit' => 'px',
                'value' => null,
            );
            $value = wp_parse_args( $value, $default );
            $new_value = array();
            $new_value['unit'] = sanitize_text_field( $value['unit'] );
            $new_value['value'] = sanitize_text_field( $value['value'] );
            return $new_value;
        }

        private function sanitize_checkbox( $value ){
            if ( $value == 1 || $value == 'on' ) {
                $value = 1;
            } else {
                $value = 0;
            }
            return $value;
        }

        private function sanitize_color( $color ){
            if ( empty( $color ) || is_array( $color ) ){
                return '';
            }

            // If string does not start with 'rgba', then treat as hex
            // sanitize the hex color and finally convert hex to rgba
            if ( false === strpos( $color, 'rgba' ) ) {
                return sanitize_hex_color( $color );
            }

            // By now we know the string is formatted as an rgba color so we need to further sanitize it.
            $color = str_replace( ' ', '', $color );
            sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );
            return 'rgba('.$red.','.$green.','.$blue.','.$alpha.')';
        }

        private function sanitize_media( $value ){
            $value = wp_parse_args( $value, array(
                'id'    => '',
                'url'   => '',
                'mime'  => ''
            ) );
            $value['id'] = sanitize_text_field( $value['id'] );
            $value['url'] = sanitize_text_field( $value['url'] );
            $value['mime'] = sanitize_text_field( $value['mime'] );
            return $value;
        }

        private function sanitize_icon( $value ){
            $value = wp_parse_args( $value, array(
                'type'    => '',
                'icon'   => '',
            ) );
            $value['type'] = sanitize_text_field( $value['type'] );
            $value['icon'] = sanitize_text_field( $value['icon'] );
            $this->icons[ $value['type'] ] = true;
            return $value;
        }

        private function sanitize_text_field_deep( $value ){
            if ( ! is_array(  $value ) ) {
                $value = sanitize_text_field( $value );
            } else {
                if ( is_array( $value ) ) {
                    foreach ( $value as $k => $v ) {
                        $value[ $k ] = $this->sanitize_text_field_deep( $v );
                    }
                }
            }
            return $value;
        }

        private function sanitize_group( $value ){

            if ( ! is_array( $value ) ) {
                $value = array();
            }
            foreach ( $this->control->fields as $field ) {
                if ( ! isset( $value[ $field['name'] ] ) ) {
                    $value[ $field['name'] ] = '';
                }
                $_v =  $value[ $field['name'] ];
                $_v = $this->sanitize( $_v, $field );
                $value[ $field['name'] ] = $_v;
            }
            return $value;
        }

        private function sanitize_repeater( $value ){
            if ( ! is_array( $value ) ) {
                $value = array();
            }

            foreach ( $value as $k => $iv ) {
                foreach ( $this->control->fields as $field ) {

                    if ( ! isset( $iv[ $field['name'] ] ) ) {
                        $iv[ $field['name'] ] = '';
                    }

                    $_v =  $iv[ $field['name'] ];
                    $_v = $this->sanitize( $_v, $field );
                    $iv[ $field['name'] ] = $_v;
                }

                $value[ $k ] = $iv;
            }

            return $value;
        }

        function end_sanitize(){
           // update_option( '_beacon_customizer_config', true );
        }

        function sanitize( $value, $field = array() ){

            $type = null;
            $device_settings = false;
            if ( is_array( $field ) && ! empty( $field ) ) {
                if ( isset( $field['type'] ) ) {
                    $type = $field['type'];
                } elseif ( isset( $field['setting_type'] ) ) {
                    $type =  $field['setting_type'];
                }

                if ( isset( $field['device_settings'] ) && $field['device_settings'] ) {
                    $device_settings = true;
                }

            } else {
                $type = $this->control->setting_type;
                $device_settings = $this->control->device_settings ;
            }

            switch ( $type ) {
                case 'color':
                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_color( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_color( $value );
                    }
                    break;
                case 'group':
                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_group( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_group( $value );
                    }
                    break;
                case 'repeater':

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_repeater( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_repeater( $value );
                    }

                    break;
                case 'media':
                case 'image':
                case 'attachment':
                case 'video':
                case 'autio':

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_media( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_media( $value );
                    }

                    break;
                case 'select':
                case 'radio':
                case 'image_select':

                    $default = null;
                    $choices = array();
                    if ( ! empty( $field )  ) {
                        if ( isset( $field['default'] ) ) {
                            $default =  $field['default'];
                        }

                        if ( isset( $field['choices'] ) ) {
                            $choices =  $field['choices'];
                        }

                    } else {
                        $default = $this->setting->default;
                        $choices =  $this->control->choices;
                    }

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    if ( ! isset( $choices[ $value[ $device ] ] ) ) {
                                        if ( is_array( $default ) && isset( $default[ $device ] ) ) {
                                            $value[ $device ] = $default;
                                        } else {
                                            $value[ $device ] = $default;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        if ( ! isset( $choices[ $value ] ) ) {
                            $value = $default;
                        }
                    }

                    break;
                case 'checkbox':
                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_checkbox( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_checkbox( $value );
                    }

                    break;
                case 'css_ruler':

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_css_ruler( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_css_ruler( $value );
                    }

                    break;
                case 'slider':

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_slider( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_slider( $value );
                    }

                    break;
                case 'icon':

                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_icon( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_icon( $value );
                    }

                    break;
                default:
                    $has_device = false;
                    if ( $device_settings ) {
                        if ( is_array( $value ) ) {
                            $has_device = false;
                            foreach ( _Beacon_Customizer()->devices as $device ) {
                                if ( isset( $value[ $device ] ) ) {
                                    $has_device = true;
                                    $value[ $device ] = $this->sanitize_text_field_deep( $value[ $device ] );
                                }
                            }
                        }
                    }
                    if ( ! $has_device ) {
                        $value = $this->sanitize_text_field_deep( $value );
                    }

            }

            $this->end_sanitize();

            return $value;
        }
    }

    function _beacon_sanitize_customizer_input( $input, $setting ){

        $input = wp_unslash( $input );
        if ( ! is_array( $input ) ) {
            $input = json_decode( urldecode_deep( $input ), true );
        }
        $control = $setting->manager->get_control( $setting->id );
        $s = new _Beacon_Sanitize_Input( $control, $setting );
        $input = $s->sanitize( $input );
        return $input;

    }

}


function _test_1_render_callback( $partial = false ){
    echo '<div class="_test_text1">';

    if( $partial ) {
        $control_settings = $partial->component->manager->get_control($partial->id);
        echo '<pre>';
        var_dump($control_settings);
        echo '</pre>';

    }

    $html = '<h2 class="dsadsadsa">'.esc_html( _Beacon_Customizer()->get_setting( 'text' ) ).'<div class="_test_text_2">'.esc_html( _Beacon_Customizer()->get_setting( 'text2' ) ).'</div></h2>';
    echo $html;

    echo '</div>';
}
function _test_2_render_callback(){
    $html = '<div class="_test_text_2">'.esc_html( get_theme_mod( 'text2' ) ).'</div>';
    echo $html;
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
	wp_localize_script( '_beacon-customizer', '_Beacon_Preview_Config_Fields', _Beacon_Customizer::get_config() );

}
add_action( 'customize_preview_init', '_beacon_customize_preview_js' );
