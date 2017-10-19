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

        function auto_css(){
            $config = _Beacon_Customizer::get_config();

            return '';
        }
    }

    function _Beacon_Customizer_Auto_CSS(){
        return _Beacon_Customizer_Auto_CSS::get_instance();
    }
}