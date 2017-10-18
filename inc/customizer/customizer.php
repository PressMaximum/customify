<?php
/**
 * _beacon Theme Customizer
 *
 * @package _beacon
 */

require get_template_directory() . '/inc/customizer/customizer-config.php';

if ( ! class_exists( '_Beacon_Customizer' ) ) {
    class  _Beacon_Customizer {
        static $config;
        static $_instance;
        static $has_icon = false;
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

        function get_setting( $key ){
            $config = self::get_config();
            if ( isset( $config['setting|'.$key ] ) ) {
                $default = isset( $config['setting|'.$key ]['default'] ) ? $config['setting|'.$key ]['default'] : false;
                return get_theme_mod( $key, $default );
            } else {
                return null;
            }
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
                            'sanitize_callback'     => '_beacon_sanitize_customizer_input',
                            'sanitize_js_callback'  => null,
                            'theme_supports'        => null,
                            //'transport'             => 'postMessage', // refresh
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
                           //'transport' => $args['transport'],
                           'default' => $args['default'],
                       );
                        $settings_args['transport'] = 'refresh';

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

if ( ! function_exists( '_Beacon_Customizer' ) ) {
    function _Beacon_Customizer(){
        return _Beacon_Customizer::get_instance();
    }
}
_Beacon_Customizer();


if ( ! function_exists( '_beacon_sanitize_input' ) ) {

    class _Beacon_Sanitize_Input {

        private $control;
        private $setting;
        private $font_icon_types;
        public $has_icon = false;

        function __construct( $control, $setting )
        {
            $this->control = $control;
            $this->setting = $setting;
        }

        function sanitize_css_ruler( $value ){
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

        function sanitize_slider( $value ){
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

        function sanitize_checkbox( $value ){
            if ( $value == 1 || $value == 'on' ) {
                $value = 1;
            } else {
                $value = 0;
            }
            return $value;
        }

        function sanitize_color( $color ){
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

        private function _sanitize_media( $value ){
            $value = wp_parse_args( $value, array(
                'id'    => '',
                'url'   => '',
                'mime'  => ''
            ) );
            $value['id'] = sanitize_text_field( $value['id'] );
            $value['url'] = sanitize_text_field( $value['id'] );
            $value['mime'] = sanitize_text_field( $value['id'] );
            return $value;
        }

        function sanitize_text_field_deep( $value ){
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

        function sanitize_group( $value ){

            if ( ! is_array( $value ) ) {
                $value = array();
            }

            foreach ( $this->control->fields as $field ) {

                if ( ! isset( $value[ $field['name'] ] ) ) {
                    $value[ $field['name'] ] = '';
                }

                $_v =  $value[ $field['name'] ];

                switch (  $field['type'] ) {
                    case 'color':
                        $_v = $this->sanitize_color( $_v );
                        break;
                    case 'media':
                    case 'image':
                    case 'attachment':
                    case 'video':
                        $_v = $this->_sanitize_media( $_v );
                        break;
                    case 'select':
                    case 'radio':
                    case 'image_select':
                        if ( ! isset( $field['choices'][ $_v ] ) ) {
                            $_v = ( isset( $field['default'] ) ) ? $field['default'] : null;
                        }
                        break;
                    case 'checkbox':
                        $_v = $this->sanitize_checkbox( $_v );
                        break;
                    case 'css_ruler':
                        $_v = $this->sanitize_css_ruler( $_v );
                        break;
                    case 'slider':
                        $_v = $this->sanitize_slider( $_v );
                        break;
                    default:
                        $_v = $this->sanitize_text_field_deep( $_v );

                }

                $value[ $field['name'] ] = $_v;

            }

            return $value;
        }

        function sanitize_repeater( $value ){
            if ( ! is_array( $value ) ) {
                $value = array();
            }

            foreach ( $value as $k => $iv ) {
                foreach ( $this->control->fields as $field ) {

                    if ( ! isset( $iv[ $field['name'] ] ) ) {
                        $iv[ $field['name'] ] = '';
                    }

                    $_v =  $iv[ $field['name'] ];
                    switch (  $field['type'] ) {
                        case 'color':
                            $_v = $this->sanitize_color( $_v );
                            break;
                        case 'media':
                        case 'image':
                        case 'attachment':
                        case 'video':
                            $_v = $this->_sanitize_media( $_v );
                            break;
                        case 'select':
                        case 'radio':
                        case 'image_select':
                            if ( ! isset( $field['choices'][ $_v ] ) ) {
                                $_v = ( isset( $field['default'] ) ) ? $field['default'] : null;
                            }
                            break;
                        case 'checkbox':
                            $_v = $this->sanitize_checkbox( $_v );
                            break;
                        case 'css_ruler':
                            $_v = $this->sanitize_css_ruler( $_v );
                            break;
                        case 'slider':
                            $_v = $this->sanitize_slider( $_v );
                            break;
                        default:
                            $_v = $this->sanitize_text_field_deep( $_v );

                    }

                    $iv[ $field['name'] ] = $_v;
                }

                $value[ $k ] = $iv;
            }

            return $value;
        }

        function sanitize( $value, $type = null ){
            switch ( $type ) {
                case 'color':
                    $value = $this->sanitize_color( $value );
                    break;
                case 'group':
                    $value = $this->sanitize_group( $value );
                    break;
                case 'repeater':
                    $value = $this->sanitize_repeater( $value );
                    break;
                case 'media':
                case 'image':
                case 'attachment':
                case 'video':
                    $value = $this->_sanitize_media( $value );
                    break;
                case 'select':
                case 'radio':
                case 'image_select':
                    if ( ! isset( $this->control->choices[ $value ] ) ) {
                        $value = $this->setting->default;
                    }
                    break;
                case 'checkbox':
                    $value = $this->sanitize_checkbox( $value );
                    break;
                case 'css_ruler':
                    $value = $this->sanitize_css_ruler( $value );
                    break;
                case 'slider':
                    $value = $this->sanitize_slider( $value );
                    break;
                default:
                    $value = $this->sanitize_text_field_deep( $value );

            }

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
