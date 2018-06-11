<?php

class Customify_WC {
    static $_instance;

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance ;
    }

    function is_active(){
        return  Customify()->is_woocommerce_active();
    }

    function __construct()
    {
        if (  $this->is_active() ) {
            add_filter('customify_get_layout', array($this, 'shop_layout'));
            add_action('widgets_init', array($this, 'register_sidebars'));
            add_filter('customify/customizer/config', array($this, 'customize_shop_sidebars'));
            add_filter('customify/sidebar-id', array($this, 'shop_sidebar_id'), 15, 2);

            add_filter('customify_is_header_display', array($this, 'show_shop_header'), 15);
            add_filter('customify_is_footer_display', array($this, 'show_shop_footer'), 15);
            add_filter('customify_site_content_class', array($this, 'shop_content_layout'), 15);
            add_filter('customify_builder_row_display_get_post_id', array($this, 'builder_row_get_id'), 15);

            add_filter('customify/titlebar/args', array($this, 'titlebar_args'));
            add_filter('customify/titlebar/config', array($this, 'titlebar_config'), 15, 2);
            add_filter('customify/titlebar/is-showing', array($this, 'titlebar_is_showing'), 15);

            add_filter('customify/theme/js', array($this, 'add_js'));
            add_filter('customify/theme/css', array($this, 'add_css'));

            /**
             * woocommerce_sidebar hook.
             *
             * @hooked woocommerce_get_sidebar - 10
             */
            add_action( 'wp', array( $this, 'wp' ) );

            // Custom styling
            add_filter('customify/styling/primary-color', array($this, 'styling_primary'));
            add_filter('customify/styling/link-color', array($this, 'styling_linkcolor'));
        }
    }

    function styling_primary( $selector ){
        $selector .= ' .woocommerce  #respond input#submit, 
        .woocommerce  a.button, 
        .woocommerce  button.button, 
        .woocommerce  input.button,
        .woocommerce #respond input#submit:hover, 
        .woocommerce  a.button:hover, 
        .woocommerce  button.button:hover, 
        .woocommerce  input.button:hover {
            background-color: {{value}};
        }';

        return $selector;
    }

	function styling_linkcolor( $selector ){
		$selector .= ' .woocommerce-account .woocommerce-MyAccount-navigation ul li.is-active a,
        .woocommerce-account .woocommerce-MyAccount-navigation ul li a:hover {
            color: {{value}};
        }';

		return $selector;
	}



    function wp(){
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
    }

    function add_css( $css_files ){
        $suffix = Customify()->get_asset_suffix();
        $css_files['plugin-woocommerce'] = esc_url( get_template_directory_uri() ) . '/assets/css/compatibility/woocommerce'.$suffix.'.css';
        return $css_files;
    }

    function add_js( $js_files ){
        $suffix = Customify()->get_asset_suffix();
        $js_files['plugin-woocommerce'] = esc_url( get_template_directory_uri() ) . '/assets/js/compatibility/woocommerce'.$suffix.'.js';
        return $js_files;
    }

    function get_shop_page_meta( $meta_key ){
        return get_post_meta( wc_get_page_id('shop'), $meta_key, true );
    }

    function is_shop_pages(){
        return ( is_shop() || is_product_category() || is_product_tag() || is_product() );
    }

    function builder_row_get_id( $id ){
        if ( $this->is_shop_pages() ) {
            $id = wc_get_page_id('shop');
        }
        return $id;
    }

    function shop_content_layout( $classes = array() ){
        if ( $this->is_shop_pages() ) {
            $page_layout = $this->get_shop_page_meta( '_customify_content_layout' );
            if( $page_layout ) {
                $classes['content_layout'] = 'content-'.sanitize_text_field( $page_layout );
            }
        }
        return $classes;
    }

    function show_shop_header(  $show = true ){
        if ( $this->is_shop_pages() ) {
            $disable = $this->get_shop_page_meta( '_customify_disable_header' );
            if ( $disable ) {
                $show = false;
            }
        }
        return $show;
    }

    function show_shop_footer( $show = true ){
        if ( $this->is_shop_pages() ) {
            $rows = array( 'main', 'bottom' );
            $count = 0;
            $shop_id = wc_get_page_id('shop');
            foreach ( $rows as $row_id ) {
                if ( ! customify_is_builder_row_display( 'footer', $row_id,  $shop_id ) ) {
                    $count ++ ;
                }
            }
            if ( $count >= count( $rows ) ){
                $show = false;
            }
        }
        return $show;
    }

    function show_shop_title( $show = true ){
        if ( $this->is_shop_pages() ) {
            $disable = $this->get_shop_page_meta( '_customify_disable_page_title' );
            if ( $disable ) {
                $show = false;
            }
        }

        if ( $this->titlebar_is_showing() ){
            $show = false;
        }

        return apply_filters( 'customify_is_shop_title_display', $show );
    }

    function titlebar_is_showing( $show = true ){

        if ( is_shop() ) {
            // Do not show if page settings disable page title
            if ( Customify_Breadcrumb::get_instance()->support_plugins_active() && ! Customify()->get_setting( 'titlebar_display_product' ) ) {
                $show = false;
            } else {
                $show = true;
            }
            if ( Customify()->is_using_post() ) {
                $breadcrumb_display = get_post_meta( wc_get_page_id( 'shop' ), '_customify_breadcrumb_display', true);
                if ( $breadcrumb_display == 'hide' ) {
                    $show = false;
                } elseif( $breadcrumb_display == 'show' ) {
                    $show = true;
                }
            }

        } else if (is_product_taxonomy()) {
            if (Customify()->get_setting('titlebar_display_product_tax')) {
                $show = true;
            } else {
                $show = false;
            }
        } elseif (is_product()) {
            if (Customify()->get_setting('titlebar_display_product')) {
                $show = true;
            } else {
                $show = false;
            }
        }

        return $show;
    }

    function titlebar_config( $config, $titlebar ){
        $section = 'titlebar';

        $config[] = array(
            'name' => "{$section}_display_product_tax",
            'type' => 'checkbox',
            'default' => 1,
            'section' =>  $section,
            'checkbox_label' => __( 'Display on product taxonomies (categories/tags,..)', 'customify' ),
        );

        $config[] = array(
            'name' => "{$section}_display_product",
            'type' => 'checkbox',
            'default' => 1,
            'section' =>  $section,
            'checkbox_label' => __( 'Display on single product', 'customify' ),
        );

        return $config;
    }

    function titlebar_args( $args ){
        if ( is_product_taxonomy() ) {
            $t = get_queried_object();
            $args['title'] = $t->name;
        } else if ( is_singular( 'product' ) ) {
            $args['title'] =  get_the_title( wc_get_page_id( 'shop' ) );
            $args['tag'] = 'h2';
        }
        return $args;
    }

    function shop_sidebar_id( $id, $sidebar_type = null ){
        if ( $this->is_shop_pages() ) {
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
        return $configs;
    }
    function register_sidebars(){
        register_sidebar( array(
            'name'          => esc_html__( 'WooCommerce Primary Sidebar', 'customify' ),
            'id'            => 'shop-sidebar-1',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'WooCommerce Secondary Sidebar', 'customify' ),
            'id'            => 'shop-sidebar-2',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
    }

    function shop_layout( $layout = false ){
        if ( $this->is_shop_pages() ) {
            $default    = Customify()->get_setting('sidebar_layout');
            $page       = Customify()->get_setting('page_sidebar_layout');
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
            $product_custom = Customify()->get_setting( 'product_sidebar_layout' );
            if ( $product_custom && $product_custom != 'default' ) {
                $layout = $product_custom;
            }
        }
        return $layout;
    }
}

function Customify_WC(){
    return Customify_WC::get_instance();
}

if ( Customify()->is_woocommerce_active() ) {
    Customify_WC();
}


/**
 * Template pages
 */

if ( ! function_exists( 'woocommerce_content' ) ) {

    /**
     * Output WooCommerce content.
     *
     * This function is only used in the optional 'woocommerce.php' template.
     * which people can add to their themes to add basic woocommerce support.
     * without hooks or modifying core templates.
     */
    function woocommerce_content() {

        if ( is_singular( 'product' ) ) {

            while ( have_posts() ) : the_post();

                wc_get_template_part( 'content', 'single-product' );

            endwhile;

        } else { ?>

            <?php if ( Customify_WC()->show_shop_title() ) { ?>
                <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
                    <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
                <?php endif; ?>
                <?php do_action( 'woocommerce_archive_description' ); ?>
            <?php } ?>

            <?php if ( have_posts() ) : ?>

                <?php do_action( 'woocommerce_before_shop_loop' ); ?>

                <?php woocommerce_product_loop_start(); ?>

                <?php woocommerce_product_subcategories(); ?>

                <?php while ( have_posts() ) : the_post(); ?>

                    <?php wc_get_template_part( 'content', 'product' ); ?>

                <?php endwhile; // end of the loop. ?>

                <?php woocommerce_product_loop_end(); ?>

                <?php do_action( 'woocommerce_after_shop_loop' ); ?>

            <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

                <?php do_action( 'woocommerce_no_products_found' ); ?>

            <?php endif;

        }
    }
}
