<?php

class Customify_Assets_Config {
    static $_instance = null;
    function __construct() {
        add_filter( 'customify/customizer/config', array( $this, 'config' ) );
        add_filter( 'customify/load-icons', array( $this, 'maybe_load_icons' ) );
        self::$_instance = $this;
    }

    function maybe_load_icons( $load ){
        if( Customify()->get_setting('site_assets_font_awesome') ) {
            return true;
        }
        return $load;

    }

    static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function config( $configs = array() ) {
        $section      = 'site_assets';
        $name         = 'site_assets';

        $configs[] = array(
            'name'  => $section,
            'type'  => 'section',
            'priority' => 180,
            'title' => __( 'Assets', 'customify' ),
        );

        $configs[] = array(
            'name'            => "{$name}_font_awesome",
            'type'            => 'checkbox',
            'section'         => $section,
            'label'           => __( 'Font Awesome', 'customify' ),
            'checkbox_label'  => __( 'Always load font Font Awesome', 'customify' ),
            'description'     => __( 'By default, the Font Awesome icon is loaded only when you use the Header - Social Icons item, or any item includes the Icons field. If you want to load font icon, just check option bellow', 'customify' ),
            'default'         => false,
        );

        return $configs;
    }

}

Customify_Assets_Config::get_instance();

