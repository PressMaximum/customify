<?php
/**
 * customify functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package customify
 */

global $site_layout;
$site_layout = 'content-sidebar';

if ( ! function_exists( 'customify_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function customify_setup() {
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

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'customify_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

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
	}
endif;
add_action( 'after_setup_theme', 'customify_setup' );



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
add_filter( 'customify_the_content', 'prepend_attachment' );
add_filter( 'customify_the_content', 'wp_make_content_images_responsive' );
add_filter( 'customify_the_content', 'capital_P_dangit' );
add_filter( 'customify_the_content', 'do_shortcode' );
add_filter( 'customify_the_content', 'convert_smilies' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function customify_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'customify_content_width', 640 );
}
add_action( 'after_setup_theme', 'customify_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function customify_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Primary', 'customify' ),
		'id'            => 'sidebar-1',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar Secondary', 'customify' ),
		'id'            => 'sidebar-2',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1', 'customify' ),
		'id'            => 'footer-1',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2', 'customify' ),
		'id'            => 'footer-2',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3', 'customify' ),
		'id'            => 'footer-3',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4', 'customify' ),
		'id'            => 'footer-4',
		'description'       => esc_html__( 'Add widgets here.', 'customify' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'customify_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function customify_scripts() {
	wp_enqueue_style( 'customify-style', get_stylesheet_uri() );

	if ( ! function_exists( 'a' ) ) {
	    require_once  get_template_directory().'/inc/customizer/customizer-icons.php';
    }
    Customify_Font_Icons()->enqueue();

	wp_enqueue_script( 'customify', get_template_directory_uri() . '/assets/js/theme.js', array('jquery'), false, true );

	wp_enqueue_script( 'customify-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'customify-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

    wp_add_inline_style( 'customify-style', Customify_Customizer_Auto_CSS() );
}
add_action( 'wp_enqueue_scripts', 'customify_scripts' );

/**
 * Template element classes.
 */
require get_template_directory() . '/inc/template-class.php';

/**
 * Functions which enhance the theme by hooking into WordPerss and itself (huh?).
 */
require get_template_directory() . '/inc/element-classes.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/customizer.php';
require get_template_directory() . '/inc/customizer-layout-builder/init.php';


