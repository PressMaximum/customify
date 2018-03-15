<?php
class Customify {

    static $_instance;
    static $version;
    static $theme_url;
    static $theme_name;
    static $theme_author;
    static $path;

    /**
     * @var Customify_Customizer
     */
    public $customizer = null;

    function init_hooks(){
        add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );
        add_action( 'after_setup_theme', array( $this, 'content_width' ), 0 );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 3 );
        add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
    }

    /**
     * Filter the excerpt "read more" string.
     *
     * @param string $more "Read more" excerpt string.
     * @return string (Maybe) modified "read more" excerpt string.
     */
    function excerpt_more( $more ) {
        return '&hellip;';
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

            self::$_instance->init();
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
            'menu-1' => esc_html__( 'Primary', 'customify' )
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

        for( $i = 1; $i <= 4; $i++ ) {
            register_sidebar( array(
                'name'          => sprintf( __( 'Footer Sidebar %d', 'customify' ), $i ),
                'id'            => 'footer-'.$i,
                'description'       => __( 'Add widgets here.', 'customify' ),
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h4 class="widget-title">',
                'after_title'   => '</h4>',
            ) );
        }

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

        if ( ! class_exists( 'Customify_Font_Icons' ) ) {
            require_once  get_template_directory().'/inc/customizer/class-customizer-icons.php';
        }
        Customify_Font_Icons::get_instance()->enqueue();

        $suffix = $this->get_asset_suffix();

        $css_files = apply_filters(  'customify/theme/css', array(
            'style' => $this->get_style_uri()
        ) );

        $js_files = apply_filters(  'customify/theme/js', array(
            'jquery.fitvids.js' => array(
                'url' => get_template_directory_uri() . '/assets/js/jquery.fitvids'.$suffix.'.js',
                'deps' => array( 'jquery' ),
                'ver' => '1.1'
            ),
            'customify-themejs' => array(
                'url' => get_template_directory_uri() . '/assets/js/theme'.$suffix.'.js',
                'deps' => array( 'jquery', 'jquery.fitvids.js' )
            ),
        ) );

        foreach( $css_files as $id => $url ) {
            $deps = array();
            wp_enqueue_style( 'customify-'.$id, $url, $deps, self::$version );
        }

        foreach( $js_files as $id => $arg ) {
            $deps = array();
            $ver = '';
            if ( is_array( $arg ) ) {
                $arg = wp_parse_args( $arg, array(
                    'deps' => '',
                    'url' => '',
                    'ver' => ''
                ) );

                $deps = $arg['deps'];
                $url = $arg['url'];
                $ver = $arg['ver'];
            } else {
                $url = $arg;
            }

            if ( ! $ver ) {
                $ver = self::$version;
            }

            wp_enqueue_script( $id,  $url, $deps, $ver, true );
        }

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS::get_instance()->auto_css() );
        wp_localize_script( 'customify', 'Customify_JS', array(
            'css_media_queries' => Customify_Customizer_Auto_CSS::get_instance()->media_queries
        ) );

        do_action( 'customify/theme/scripts' );
    }

    function admin_scripts(){
        wp_enqueue_style( 'customify-admin',  get_template_directory_uri() . '/assets/css/admin/admin.css', false, self::$version );
    }

    private function includes(){
        $files = array(
            '/inc/template-class.php',                  // Template element classes.
            '/inc/element-classes.php',                 // Functions which enhance the theme by hooking into WordPress and itself (huh?).
            '/inc/metabox.php',                         // Page settings.
            '/inc/template-tags.php',                   // Custom template tags for this theme.
            '/inc/template-functions.php',              // Functions which enhance the theme by hooking into WordPress.
            '/inc/customizer/class-customizer.php',     // Customizer additions.
            '/inc/customizer/admin.php',                // Admin additions.
            '/inc/panel-builder/panel-builder.php',     // Customizer additions.

            '/inc/posts/class-post-entry.php',          // Blog entry builder
            '/inc/posts/class-posts-layout.php',        // Blog builder config
            '/inc/posts/functions-posts-layout.php',   // Posts layout function
        );

        foreach( $files as $file ) {
            require_once self::$path.$file;
        }

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        }

        $this->load_configs();
        $this->load_compatibility();

    }

    /**
     * Load configs
     */
    private function load_configs(){

        $config_files = array(
            // Site Settings
            'layouts',
            'blogs',
            'styling',
            'titlebar',
            'compatibility',

            // Header Builder Panel
            'header/panel',
            'header/html',
            'header/logo',
            'header/nav-icon',
            'header/primary-menu',
            'header/templates',
            'header/templates',
            'header/logo',
            'header/search-icon',
            'header/search-box',
            'header/menus',
            'header/nav-icon',
            'header/button',
            'header/social-icons',
             // Footer Builder Panel
            'footer/panel',
            'footer/widgets',
            'footer/templates',
            'footer/widgets',
            'footer/html',
            'footer/copyright',

        );

        $path = get_template_directory();
        // Load default config values
        require_once $path . "/inc/customizer/configs/config-default.php";

        // Load site configs
        foreach ( $config_files as  $f ) {
            $file = $path . "/inc/customizer/configs/{$f}.php";
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }

    }

    /*
     * Load site compatibility supports
     */
    private function load_compatibility(){

        $compatibility_config_files = array(
            'breadcrumb-navxt', // Plugin breadcrumb-navxt
            'woocommerce/woocommerce',  // Plugin WooCommerce
        );
        foreach ( $compatibility_config_files as  $f ) {
            $file = self::$path . "/inc/compatibility/{$f}.php";
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }

    function is_woocommerce_active(){
        return class_exists('WooCommerce');
    }

    function is_using_post(){
        $use = false;
        if ( is_singular() ){
            $use = true;
        } else {
            if ( is_front_page() && is_home() ) {
                $use = false;
            } elseif ( is_front_page() ) {
                // static homepage
                $use = true;
            } elseif ( is_home() ) {
                // blog page
                $use = true;
            } else {
                if ( $this->is_woocommerce_active() ) {
                    if ( is_shop() ) {
                        $use = true;
                    }
                }
            }
        }
        return $use;
    }

    function get_current_post_id(){
        $id = get_the_ID();
        if ( is_front_page() && is_home() ) {
            $id = false;
        } elseif ( is_front_page() ) {
            // static homepage
            $id = get_option( 'page_on_front' );
        } elseif ( is_home() ) {
            // blog page
            $id = get_option( 'page_for_posts' );
        } else {
            if ( $this->is_woocommerce_active() ) {
                if ( is_shop() ) {
                    $id = wc_get_page_id('shop');
                }
            }
        }
        return $id;
    }

    function init(){
        $this->init_hooks();
        $this->includes();
        $this->customizer = new Customify_Customizer();
        do_action( 'customify/init' );
    }

    function get_setting( $id, $device = 'desktop', $key = null ){
        return $this->customizer->get_setting( $id, $device, $key );
    }

    function get_media( $value, $size = null ){
        return $this->customizer->get_media( $value, $size );
    }

    function get_setting_tab($name, $tab = null) {
        return $this->customizer->get_setting_tab( $name, $tab );
    }

}
