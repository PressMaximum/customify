<?php

if ( ! class_exists( '_Beacon_Customizer_Auto_CSS' ) ) {
    class _Beacon_Customizer_Auto_CSS {
        static $_instance;
        static function get_instance(){
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance ;
        }

        private function replace_value( $value, $format ){
            return str_replace( '{{value}}', $value, $format );
        }

        function setup_css_ruler( $value, $format ){
            $value = wp_parse_args( $value, array(
                'unit' => '',
                'top' => null,
                'right' => null,
                'bottom' => null,
                'left' => null,
            ) );

            if ( ! $value['unit'] ) {
                $value['unit'] = 'px';
            }

            $format = wp_parse_args( $format, array(
                'top' => null,
                'right' => null,
                'bottom' => null,
                'left' => null,
            ) );

            $code = array();
            foreach ( $format as $pos => $string ) {
                $v = $value[ $pos ];
                if ( $string ) {
                    if (!is_null( $v ) && $v !== '') {
                        $v = $v.$value['unit'];
                        $code[ $pos ] = $this->replace_value( $v, $string ).';';
                    }
                }
            }

            return join( "\n\t",  $code );
        }

        function setup_slider( $value, $format ){
            $value = wp_parse_args( $value, array(
                'unit' => '',
                'value' => null,
            ) );

            if ( ! $value['unit'] ) {
                $value['unit'] = 'px';
            }

            if ( $format ) {
                if (!is_null( $value['value'] ) && $value['value'] !== '') {
                    $v = $value['value'].$value['unit'];
                    return $this->replace_value( $v, $format ).';';
                }
            }

            return false;
        }

        function css_ruler( $field ){
            $code = '';
            if ( $field['device_settings'] ) {
                foreach ( _Beacon_Customizer()->devices as $device ) {
                    $value = _Beacon_Customizer()->get_setting(  $field['name'], $device );
                    $_c = $this->setup_css_ruler( $value, $field['css_format'] );
                    if ( $_c ) {
                        if ( 'desktop' == $device ) {
                            $code .= "{$field['selector']} { {$_c} }";
                        } else {
                            $code .= ".{$device} {$field['selector']} { {$_c} }";
                        }

                    }
                }
            } else {
                $value = _Beacon_Customizer()->get_setting(  $field['name'] );
                $_c = $this->setup_css_ruler( $value, $field['css_format'] );
                if ( $_c ) {
                    $code .= "{$field['selector']} { {$_c} }";
                }
            }
            return $code;
        }

        function slider( $field ){
            $code = '';
            if ( $field['device_settings'] ) {
                foreach ( _Beacon_Customizer()->devices as $device ) {
                    $value = _Beacon_Customizer()->get_setting(  $field['name'], $device );
                    $_c = $this->setup_slider( $value, $field['css_format'] );
                    if ( $_c ) {
                        if ( 'desktop' == $device ) {
                            $code .= "{$field['selector']} { {$_c} }";
                        } else {
                            $code .= ".{$device} {$field['selector']} { {$_c} }";
                        }

                    }
                }
            } else {
                $value = _Beacon_Customizer()->get_setting(  $field['name'] );
                $_c = $this->setup_slider( $value, $field['css_format'] );
                if ( $_c ) {
                    $code .= "{$field['selector']} { {$_c} }";
                }
            }
            return $code;
        }

        function auto_css( $partial = false ){
            $config = _Beacon_Customizer::get_config();
            //  $control_settings = $partial->component->manager->get_control($partial->id);
            $css_code = '';
            foreach ( $config as $field ) {
                switch ( $field['type'] ) {
                    case 'css_ruler':
                        $css_code .= $this->css_ruler( $field );
                        break;
                    case 'slider':
                        $css_code .= $this->slider( $field );
                        break;
                }
            }

            return $css_code;
        }
    }

    function _Beacon_Customizer_Auto_CSS( $partial = false ){
        return _Beacon_Customizer_Auto_CSS::get_instance()->auto_css( $partial );
    }
}
