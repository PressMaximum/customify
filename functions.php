<?php
/**
 * Customify functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package customify
 */

/**
 *  Same hook for the_content
 * @TODO: do not effect content by plugins
 *
 * 8 WP_Embed:run_shortcode
 * 8 WP_Embed:autoembed
 * 10 wptexturize
 * 10 wpautop
 * 10 shortcode_unautop
 * 10 prepend_attachment
 * 10 wp_make_content_images_responsive
 * 11 capital_P_dangit
 * 11 do_shortcode
 * 20 convert_smilies
 */
global $wp_embed;
add_filter( 'customify_the_content', array( $wp_embed, 'run_shortcode' ), 8 );
add_filter( 'customify_the_content', array( $wp_embed, 'autoembed' ), 8 );
add_filter( 'customify_the_content', 'wptexturize' );
add_filter( 'customify_the_content', 'wpautop' );
add_filter( 'customify_the_content', 'shortcode_unautop' );
add_filter( 'customify_the_content', 'wp_make_content_images_responsive' );
add_filter( 'customify_the_content', 'capital_P_dangit' );
add_filter( 'customify_the_content', 'do_shortcode' );
add_filter( 'customify_the_content', 'convert_smilies' );

class Customify_Init {

    static $_instance;
    static $version;
    static $theme_url;
    static $theme_name;
    static $theme_author;
    static $path;

    function init_hooks(){
        add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );
        add_action( 'after_setup_theme', array( $this, 'content_width' ), 0 );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 3 );
    }

    static function get_instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            $theme = wp_get_theme();
            self::$version = $theme->get( 'Version' );
            self::$theme_url = $theme->get( 'ThemeURI' );
            self::$theme_name = $theme->get( 'Name' );
            self::$theme_author = $theme->get( 'Author' );
            self::$path = get_template_directory();

        }
        return self::$_instance ;
    }

    function get( $key ){
        if ( method_exists( $this,'get_'.$key ) ) {
            return call_user_func_array( array( $this, 'get_'.$key ), array() );
        } elseif ( property_exists( $this, $key ) ) {
            return $this->{ $key };
        }
        return false;
    }

    /**
     * Set the content width in pixels, based on the theme's design and stylesheet.
     *
     * Priority 0 to make it available to lower priority callbacks.
     *
     * @global int $content_width
     */
    function content_width() {
        $GLOBALS['content_width'] = apply_filters( 'customify_content_width', 640 );
    }

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function theme_setup(){
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on customify, use a find and replace
         * to change 'customify' to the name of your theme in all the template files.
         */
        load_theme_textdomain( 'customify', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'menu-1' => esc_html__( 'Primary', 'customify' ),
            'menu-2' => esc_html__( 'Secondary', 'customify' ),
        ) );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support( 'html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ) );

        // Add theme support for selective refresh for widgets.
        add_theme_support( 'customize-selective-refresh-widgets' );

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support( 'custom-logo', array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        ) );

        // WooCommerce support
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );

	    // Add editor style
	    add_editor_style( 'assets/css/admin/editor-style.min.css' );

    }

    /**
     * Register sidebars area.
     *
     * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
     */
    function register_sidebars() {
        register_sidebar( array(
            'name'          => esc_html__( 'Primary Sidebar', 'customify' ),
            'id'            => 'sidebar-1',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Secondary Sidebar', 'customify' ),
            'id'            => 'sidebar-2',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Footer Sidebar 1', 'customify' ),
            'id'            => 'footer-1',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Footer Sidebar 2', 'customify' ),
            'id'            => 'footer-2',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Footer Sidebar 3', 'customify' ),
            'id'            => 'footer-3',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
        register_sidebar( array(
            'name'          => esc_html__( 'Footer Sidebar 4', 'customify' ),
            'id'            => 'footer-4',
            'description'       => esc_html__( 'Add widgets here.', 'customify' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ) );
    }

    function get_asset_suffix(){
        $suffix = '.min';
        if( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $suffix = '';
        }
        return $suffix;
    }

    function get_style_uri(){
        $css_file = get_stylesheet_uri();
        if( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $suffix = $this->get_asset_suffix();
            $style_dir = get_stylesheet_directory();
            if( file_exists( $style_dir.'/style'.$suffix.'.css' ) ) {
                $css_file = get_stylesheet_directory_uri() . '/style'.$suffix.'.css';
            }
        }
        return $css_file;
    }

    /**
     * Enqueue scripts and styles.
     */
    function scripts() {

        if ( ! function_exists( 'a' ) ) {
            require_once  get_template_directory().'/inc/customizer/customizer-icons.php';
        }
        Customify_Font_Icons()->enqueue();

        $suffix = $this->get_asset_suffix();

        $css_files = apply_filters(  'customify/theme/css', array(
            'style' => $this->get_style_uri()
        ) );

        $js_files = apply_filters(  'customify/theme/js', array(
            'themejs' => get_template_directory_uri() . '/assets/js/theme'.$suffix.'.js'
        ) );

        foreach( $css_files as $id => $url ) {
            $deeps = array();
            wp_enqueue_style( 'customify-'.$id, $url, $deeps, self::$version );
        }

        foreach( $js_files as $id => $url ) {
            wp_enqueue_script( 'customify-'.$id,  $url, array('jquery'), self::$version, true );
        }

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS()->auto_css() );
        wp_localize_script( 'customify', 'Customify_JS', array(
            'css_media_queries' => Customify_Customizer_Auto_CSS()->media_queries
        ) );

        do_action( 'customify/theme/scripts' );
    }

    function includes(){
        $files = array(
            '/inc/template-class.php',                  // Template element classes.
            '/inc/element-classes.php',                 // Functions which enhance the theme by hooking into WordPerss and itself (huh?).
            '/inc/metabox.php',                         //Page settings.
            '/inc/template-tags.php',                   //  Custom template tags for this theme.
            '/inc/template-functions.php',              // Functions which enhance the theme by hooking into WordPress.
            '/inc/customizer/customizer.php',           // Customizer additions.
            '/inc/customizer-layout-builder/init.php',  // Customizer additions.
            '/inc/posts/post-builder.php',              // Blog builder
            '/inc/posts/posts.php',                     // Blog builder config
        );

        foreach( $files as $file ) {
            if ( file_exists( self::$path.$file ) ) {
                require_once self::$path.$file;
            }
        }

        //WooCommerce
        if ( class_exists('WooCommerce') ) {
            require_once self::$path.'/inc/compatibility/woocommerce/woocommerce.php';
        }

    }

    function init(){
        $this->init_hooks();
        $this->includes();
        do_action( 'customify/init' );
    }

}

function Customify_Init(){
    return Customify_Init::get_instance();
}
Customify_Init()->init();

