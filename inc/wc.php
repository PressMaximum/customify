<?php

class Customify_WC {
    function __construct()
    {
        add_filter( 'customify_get_layout', array( $this, 'shop_layout' ) );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
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
        if ( is_shop() || is_product_category() || is_product_tag() ) {
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

        return $layout;
    }
}

new Customify_WC();
