<?php

class Customify_WC {
    function __construct()
    {
        add_filter( 'customify_get_layout', array( $this, 'shop_layout' ) );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_filter( 'customify/customizer/config', array( $this, 'customize_shop_sidebars' ) );
        add_filter( 'customify/sidebar-id', array( $this, 'shop_sidebar_id' ), 15, 2 );

        // Override theme default specification for product # per row
        /*
        function loop_columns() {
            return 5; // 5 products per row
        }
        add_filter('loop_shop_columns', 'loop_columns', 999);
        */
    }

    function shop_sidebar_id( $id, $sidebar_type = null ){
        if ( is_woocommerce() ) {
            switch ($sidebar_type) {
                case  'secondary':
                    return 'shop-sidebar-2';
                    break;
                default:
                    return 'shop-sidebar-1';
            }
        }
        return $id;
    }

    function customize_shop_sidebars( $configs = array() ){
        $config = array(
            array(
                'name' => 'product_sidebar_layout',
                'type' => 'select',
                'default' => '',
                'section' => 'sidebar_layout_section',
                'title' => __('Single Product', 'customify'),
                'choices' => array_merge(
                    array( 'default' => __( "Default", 'customify' ) ),
                    customify_get_config_sidebar_layouts()
                )
            ),
        );
        return array_merge( $configs, $config );
    }
    function register_sidebars(){
        register_sidebar( array(
            'name'          => esc_html__( 'Sidebar Shop Primary', 'customify' ),
            'id'            => 'shop-sidebar-1',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Sidebar Shop Secondary', 'customify' ),
            'id'            => 'shop-sidebar-2',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
    }

    function shop_layout( $layout = false ){
        if ( is_shop() || is_product_category() || is_product_tag() || is_product() ) {
            $default    = Customify_Customizer()->get_setting('sidebar_layout');
            $page       = Customify_Customizer()->get_setting('page_sidebar_layout');
            $page_id =  wc_get_page_id('shop');
            $page_custom = get_post_meta( $page_id, '_customify_sidebar', true );
            if ( $page_custom ) {
                $layout = $page_custom;
            } else if ( $page ) {
                $layout = $page;
            } else {
                $layout = $default;
            }
        }

        if ( is_product() ) {
            $product_custom = Customify_Customizer()->get_setting( 'product_sidebar_layout' );
            if ( $product_custom && $product_custom != 'default' ) {
                $layout = $product_custom;
            }
        }
        return $layout;
    }
}

new Customify_WC();
