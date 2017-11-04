<?php

if ( ! class_exists( '_Beacon_Customizer_Auto_CSS' ) ) {
    class _Beacon_Customizer_Auto_CSS {
        static $_instance;
        private $fonts = array();
        private $variants = array();
        private $subsets = array();
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
                    return $this->replace_value( $v, $format );
                }
            }

            return false;
        }

        function setup_color( $value, $format ){
            $value = _Beacon_Sanitize_Input::sanitize_color( $value );
            if ( $format ) {
                if (!is_null( $value ) && $value !== '') {
                    return $this->replace_value( $value, $format ).';';
                }
            }
            return false;
        }

        function css_ruler( $field ){
            $code = $this->maybe_devices_setup( $field, 'setup_css_ruler' );
            return $code;
        }

        function slider( $field ){
            $code = $this->maybe_devices_setup( $field, 'setup_slider' );
            return $code;
        }

        function color( $field ){
            $code = $this->maybe_devices_setup( $field, 'setup_color' );
            return $code;
        }

        function setup_background( $value ) {
            $value = wp_parse_args( $value, array(
                'color' => '',
                'image' => '',
                'position' => '',
                'cover' => '',
                'repeat' => '',
                'attachment' => '',
            ) );

            $css = array();
            $color = _Beacon_Sanitize_Input::sanitize_color( $value['color'] );
            if ( $color ) {
                $css['color'] = "background-color: {$color};";
            }

            $image = _Beacon_Customizer()->get_media( $value['image'] );

            if ( $image ) {
                $css['image'] = "background-image: url(\"{$image}\");";
            }

            switch ( $value['position'] ) {
                case 'center':
                    $css['position'] = 'background-position: center center;';
                    break;
                case 'top_left':
                    $css['position'] = 'background-position: top left;';
                    break;
                case 'top_center':
                    $css['position'] = 'background-position: top center;';
                    break;
                case 'top_right':
                    $css['position'] = 'background-position: top right;';
                    break;
                case 'bottom_left':
                    $css['position'] = 'background-position: bottom left;';
                    break;
                case 'bottom_center':
                    $css['position'] = 'background-position: bottom center;';
                    break;
                case 'bottom_right':
                    $css['position'] = 'background-position: bottom right;';
                    break;
                default:
                    $css['position'] = 'background-position: center center;';
            }

            switch ( $value['repeat'] ) {
                case 'no-repeat':
                    $css['repeat'] = 'background-repeat: no-repeat;';
                    break;
                case 'repeat-x':
                    $css['repeat'] = 'background-repeat: repeat-x;';
                    break;
                case 'repeat-y':
                    $css['repeat'] = 'background-repeat: repeat-y;';
                    break;
                default:

            }

            switch ( $value['attachment'] ) {
                case 'scroll':
                    $css['attachment'] = 'background-attachment: scroll;';
                    break;
                case 'fixed':
                    $css['attachment'] = 'background-attachment: fixed;';
                    break;
                default:
            }

            if ( $value['cover'] ) {
                $css['cover'] = '-webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;';
                $css['attachment'] = 'background-attachment: fixed;';
            }

            return join( "\n\t",  $css );

        }

        function background( $field ){
            $code = $this->maybe_devices_setup( $field, 'setup_background' );
            return  $code;
        }

        function maybe_devices_setup( $field, $call_back, $values = null, $no_selector = false ) {
            $code = '';
            $code_array = array();
            $has_device = false;
            $format = isset( $field['css_format'] ) ? $field['css_format']: false;
            if ( isset( $field['device_settings'] ) && $field['device_settings'] ) {
                $has_device = true;
                foreach ( _Beacon_Customizer()->devices as $device ) {
                    $value = null;
                    if ( is_null( $values ) ) {
                        $value = _Beacon_Customizer()->get_setting( $field['name'], $device );
                    } else {
                        if ( isset( $values[ $device ] ) ) {
                            $value = $values[ $device ];
                        }
                    }
                    $_c = false;
                    if ( method_exists( $this, $call_back ) ) {
                        $_c = call_user_func_array( array( $this, $call_back ), array( $value, $format ) );
                    }
                    if ( $_c ) {
                        $code_array[ $device ] = $_c;
                    }
                }
            } else {
                if ( is_null( $values ) ) {
                    $values = _Beacon_Customizer()->get_setting( $field['name'] );
                }
                if ( method_exists( $this, $call_back ) ) {
                    $code = call_user_func_array( array( $this, $call_back ), array( $values, $format ) );
                }
                $code_array['no_devices'] = $code;

            }
            if ( empty( $code_array ) ) {
                return false;
            }

            if ( $no_selector ) {
                return $code_array;
            } else {
                $code = '';
                if ( $has_device ) {
                    foreach ( _Beacon_Customizer()->devices as $device ) {
                        if ( isset( $code_array[ $device ] ) ) {
                            $_c =  $code_array[ $device ];
                            if ( 'desktop' == $device ) {
                                $code .= "\r\n{$field['selector']} {\r\n\t{$_c}\r\n}";
                            } else {
                                $code .= "\r\n.{$device} {$field['selector']} {\r\n\t{$_c}\r\n}";
                            }
                        }

                    }
                } else {
                    $code .= "\r\n{$field['selector']} {\r\n\t{$code_array['no_devices']}\r\n}";
                }
            }

            return $code;
        }

        function setup_font( $value ){

            $value = wp_parse_args( $value, array(
                'font' => null,
                'type' => null,
                'variant' => null,
                'subsets' => null,
            ) );

            if ( ! $value['font'] ) {
                return '';
            }

            if ( $value['type'] == 'google' ){
                $this->fonts[ $value['font'] ] = $value['font'];
                if ( $value['variant'] ) {
                    if ( ! isset( $this->variants[ $value['font'] ] ) ) {
                        $this->variants[ $value['font'] ] = array();
                        if ( ! is_array( $value['variant'] ) ) {
                            $this->variants[ $value['font'] ] = array_merge( $this->variants[ $value['font'] ], array(  $value['variant'] =>  $value['variant'] ) );
                        } else {
                            $this->variants[ $value['font'] ] = array_merge( $this->variants[ $value['font'] ], $value['variant'] );
                        }
                    }
                }

                if ( $value['subsets'] ) {
                    $this->subsets =  array_merge( $this->subsets, $value['subsets'] );
                }
            }

            return "font-family: \"{$value['font']}\";";
        }



        function font( $field, $values = null ){
            $code = '';
            if ( $field['device_settings'] ) {
                foreach ( _Beacon_Customizer()->devices as $device ) {
                    $value = null;
                    if ( is_null( $values ) ) {
                        $value = _Beacon_Customizer()->get_setting( $field['name'], $device );
                    } else {
                        if ( isset( $values[ $device ] ) ) {
                            $value = $values[ $device ];
                        }
                    }

                    $_c = $this->setup_font( $value );
                    if ( $_c ) {
                        if ( 'desktop' == $device ) {
                            $code .= "\r\n{$field['selector']} {\r\n\t{$_c}\r\n}";
                        } else {
                            $code .= "\r\n.{$device} {$field['selector']} {\r\n\t{$_c}\r\n}";
                        }

                    }
                }
            } else {
                if ( is_null( $values ) ) {
                    $values = _Beacon_Customizer()->get_setting( $field['name'] );
                }
                $code = $this->setup_font( $values );
                $code .= "{$field['selector']} {\r\n\t{$code}\r\n}";
            }

            return $code;
        }

        function setup_font_style( $value ){
            $value = wp_parse_args( $value, array(
                'b' => null,
                'i' => null,
                'u' => null,
                's' => null,
                't' => null,
            ) );
            $css = array();
            if ( $value['b'] ) {
                $css['b'] = 'font-weight: bold;';
            }
            if ( $value['i'] ) {
                $css['i'] = 'font-style: italic;';
            }

            $decoration = array();
            if ( $value['u'] ) {
                $decoration['underline'] = 'underline';

            }

            if ( $value['s'] ) {
                $decoration['line-through'] = 'line-through';
            }

            if ( ! empty( $decoration ) ) {
                $css['d'] = 'text-decoration: '.join(' ', $decoration ).';';
            }

            if ( $value['t'] ) {
                $css['t'] = 'text-transform: uppercase;';
            }

            return join( "\r\n\t", $css );

        }

        function typography( $field ){
            $values = _Beacon_Customizer()->get_setting( $field['name'] );
            $values = wp_parse_args( $values, array(
                'font' => null,
                'font_style' => null,
                'font_size' => null,
                'line_height' => null,
                'letter_spacing' => null,
                'color' => null,
            ) );
            $code = array();


            $fields = array();
            $devices_css = array();
            foreach ($field['fields'] as $f) {
                $fields[ $f['name'] ] = $f;
            }

            if ( isset( $fields['font']) ) {
                $code['font'] = $this->setup_font($values['font']);
            }

            if (isset($fields['font_style'])) {
                $code['font_style'] = $this->setup_font_style($values['font_style']);
            }

            if (isset($fields['font_size'])) {
                $fields['font_size']['css_format'] = 'font-size: {{value}};';
                $font_size_css = $this->maybe_devices_setup($fields['font_size'], 'setup_slider', $values['font_size'], true);
                if ($font_size_css) {
                    if (isset($font_size_css['no_devices'])) {
                        $code['font_size'] = $font_size_css['no_devices'];
                    } else {
                        foreach ( $font_size_css  as $device => $_c ) {
                            if ( $device == 'desktop' ) {
                                $code['font_size'] = $_c;
                            } else {
                                if ( ! isset( $devices_css[ $device ] ) ) {
                                    $devices_css[ $device ] = array();
                                }
                                $devices_css[ $device ]['font_size'] = $_c;
                            }
                        }
                    }
                }
            }

            if (isset($fields['line_height'])) {
                $fields['line_height']['css_format'] = 'line-height: {{value}};';
                $font_size_css = $this->maybe_devices_setup($fields['line_height'], 'setup_slider', $values['line_height'], true);
                if ($font_size_css) {
                    if (isset($font_size_css['no_devices'])) {
                        $code['line_height'] = $font_size_css['no_devices'];
                    } else {
                        foreach ( $font_size_css  as $device => $_c ) {
                            if ( $device == 'desktop' ) {
                                $code['line_height'] = $_c;
                            } else {
                                if ( ! isset( $devices_css[ $device ] ) ) {
                                    $devices_css[ $device ] = array();
                                }
                                $devices_css[ $device ]['line_height'] = $_c;
                            }
                        }
                    }
                }
            }

            if (isset($fields['letter_spacing'])) {
                $fields['letter_spacing']['css_format'] = 'letter-spacing: {{value}};';
                $font_size_css = $this->maybe_devices_setup($fields['letter_spacing'], 'setup_slider', $values['letter_spacing'], true);
                if ($font_size_css) {
                    if (isset($font_size_css['no_devices'])) {
                        $code['letter_spacing'] = $font_size_css['no_devices'];
                    } else {
                        foreach ( $font_size_css  as $device => $_c ) {
                            if ( $device == 'desktop' ) {
                                $code['letter_spacing'] = $_c;
                            } else {
                                if ( ! isset( $devices_css[ $device ] ) ) {
                                    $devices_css[ $device ] = array();
                                }
                                $devices_css[ $device ]['letter_spacing'] = $_c;
                            }
                        }
                    }
                }
            }

            if ( isset($fields['letter_spacing'])) {
                $_c = $this->setup_color($values['color'], 'color: {{value}};');
                if ( $_c ) {
                    $code['color'] = $_c;
                }
            }

            $css_code = "{$field['selector']} {\r\n\t".join("\r\n\t", $code )."\r\n}";
            return $css_code;
        }

        function get_google_fonts_url(){
            $url = '//fonts.googleapis.com/css?family=';
            $s = '';
            if ( empty( $this->fonts ) ) {
                return false;
            }
            foreach ( $this->fonts as $font_name ) {
                if ( $s ){
                    $s .= '|';
                }
                $s .= str_replace(' ', '+', $font_name );
                $v = array();
                if ( isset( $this->variants[ $font_name ] ) ) {
                    foreach (  $this->variants[ $font_name ] as $_v ) {
                        if ( $_v != 'regular' ) {
                            switch ( $_v ) {
                                case 'italic':
                                    $v[$_v] = '400i';
                                    break;
                                default:
                                    $v[$_v] = str_replace( 'italic', 'i', $_v );
                            }

                        }
                    }
                }

                if ( ! empty( $v ) ) {
                    $s .=  ':'.join( ',', $v );
                }
            }

            $url .= $s;

            if ( ! empty( $this->subsets ) ) {
                $url .='&subset='.join( ',', $this->subsets );
            }
            return $url;
        }

        function auto_css( $partial = false ){
            $config = _Beacon_Customizer::get_config();
            //$control_settings = $partial->component->manager->get_control($partial->id);
            $css_code = '';
            foreach ( $config as $field ) {
                $field_css = '';
                if ( $field['selector'] ) {
                    switch ($field['type']) {
                        case 'css_ruler':
                            $field_css .= $this->css_ruler($field);
                            break;
                        case 'slider':
                            $field_css .= $this->slider($field);
                            break;
                        case 'color':
                            $field_css .= $this->color($field);
                        case 'font':
                            $field_css .= $this->font($field);
                            break;
                        default:
                            if (isset($field['css_format']) && $field['css_format'] == 'background') {
                                $field_css .= $this->background($field);
                            } else if (isset($field['css_format']) && $field['css_format'] == 'typography') {
                                $field_css .= $this->typography($field);
                            }
                    }
                }
                if ( $field_css ){
                    $field_css .= "\r\n";
                }
                $css_code .= apply_filters('_beacon/customizer/auto_css',  $field_css, $field );

            }

           $url = $this->get_google_fonts_url();
            if ( $url ) {
                $css_code = "\r\n@import url('{$url}');\r\n\r\n".$css_code;
            }

            return $css_code;
        }
    }

    function _Beacon_Customizer_Auto_CSS( $partial = false ){
        return _Beacon_Customizer_Auto_CSS::get_instance()->auto_css( $partial );
    }
}
