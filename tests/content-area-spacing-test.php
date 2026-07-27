<?php
/**
 * Focused runtime tests for the content area spacing resolver.
 *
 * Run with: php tests/content-area-spacing-test.php
 */

class WP_Post_Type {
	public $name;
	public $has_archive;

	public function __construct( $name, $has_archive ) {
		$this->name        = $name;
		$this->has_archive = $has_archive;
	}
}

class WP_Term {
	public $taxonomy;

	public function __construct( $taxonomy ) {
		$this->taxonomy = $taxonomy;
	}
}

$GLOBALS['customify_test_request'] = array();
$GLOBALS['customify_test_mods']    = array();
$GLOBALS['customify_test_meta']    = array();
$GLOBALS['customify_test_types']   = array(
	'book'    => new WP_Post_Type( 'book', true ),
	'profile' => new WP_Post_Type( 'profile', false ),
	'product' => new WP_Post_Type( 'product', true ),
);
$GLOBALS['customify_test_taxonomies'] = array(
	'book_genre'  => array( 'book' ),
	'product_cat' => array( 'product' ),
	'shared'      => array( 'book', 'post' ),
);

function __( $text ) {
	return $text;
}

function apply_filters( $hook, $value ) {
	return $value;
}

function sanitize_key( $value ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) );
}

function sanitize_html_class( $value ) {
	return preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $value );
}

function absint( $value ) {
	return abs( (int) $value );
}

function wp_parse_args( $args, $defaults ) {
	return array_merge( $defaults, $args );
}

function customify_test_flag( $name ) {
	return ! empty( $GLOBALS['customify_test_request'][ $name ] );
}

function is_search() {
	return customify_test_flag( 'search' );
}

function is_404() {
	return customify_test_flag( '404' );
}

function is_home() {
	return customify_test_flag( 'home' );
}

function is_page() {
	return customify_test_flag( 'page' );
}

function is_singular( $post_type = '' ) {
	if ( ! customify_test_flag( 'singular' ) ) {
		return false;
	}

	return ! $post_type || $post_type === get_post_type();
}

function is_post_type_archive() {
	return customify_test_flag( 'post_type_archive' );
}

function is_tax() {
	return customify_test_flag( 'tax' );
}

function is_category() {
	return customify_test_flag( 'category' );
}

function is_tag() {
	return customify_test_flag( 'tag' );
}

function is_archive() {
	return customify_test_flag( 'archive' );
}

function get_the_ID() {
	return isset( $GLOBALS['customify_test_request']['post_id'] ) ? $GLOBALS['customify_test_request']['post_id'] : 0;
}

function get_post_type() {
	return isset( $GLOBALS['customify_test_request']['post_type'] ) ? $GLOBALS['customify_test_request']['post_type'] : '';
}

function get_query_var( $key ) {
	return isset( $GLOBALS['customify_test_request'][ $key ] ) ? $GLOBALS['customify_test_request'][ $key ] : '';
}

function get_queried_object() {
	return isset( $GLOBALS['customify_test_request']['queried_object'] ) ? $GLOBALS['customify_test_request']['queried_object'] : null;
}

function get_post_type_object( $post_type ) {
	return isset( $GLOBALS['customify_test_types'][ $post_type ] ) ? $GLOBALS['customify_test_types'][ $post_type ] : null;
}

function get_taxonomy( $taxonomy ) {
	if ( ! isset( $GLOBALS['customify_test_taxonomies'][ $taxonomy ] ) ) {
		return false;
	}

	return (object) array( 'object_type' => $GLOBALS['customify_test_taxonomies'][ $taxonomy ] );
}

function get_post_meta( $post_id, $key ) {
	return isset( $GLOBALS['customify_test_meta'][ $post_id ][ $key ] ) ? $GLOBALS['customify_test_meta'][ $post_id ][ $key ] : '';
}

function customify_get_content_post_types() {
	return array(
		'book'    => array(
			'name'          => 'Books',
			'singular_name' => 'Book',
		),
		'profile' => array(
			'name'          => 'Profiles',
			'singular_name' => 'Profile',
		),
		'product' => array(
			'name'          => 'Products',
			'singular_name' => 'Product',
		),
	);
}

function customify_get_taxonomy_archive_post_type( $taxonomy ) {
	$object_types = isset( $GLOBALS['customify_test_taxonomies'][ $taxonomy ] )
		? $GLOBALS['customify_test_taxonomies'][ $taxonomy ]
		: array();

	if ( 1 !== count( $object_types ) ) {
		return '';
	}

	$post_type = reset( $object_types );
	$object    = get_post_type_object( $post_type );
	return 'product' !== $post_type && $object && $object->has_archive ? $post_type : '';
}

class Customify_Content_Area_Spacing_Test_Theme {
	public function get_setting( $setting ) {
		return isset( $GLOBALS['customify_test_mods'][ $setting ] )
			? $GLOBALS['customify_test_mods'][ $setting ]
			: 'inherit';
	}
}

function Customify() {
	static $theme;
	if ( ! $theme ) {
		$theme = new Customify_Content_Area_Spacing_Test_Theme();
	}
	return $theme;
}

require dirname( __DIR__ ) . '/inc/content-area-spacing.php';

function customify_test_reset( $request, $mods = array(), $meta = array() ) {
	$GLOBALS['customify_test_request'] = $request;
	$GLOBALS['customify_test_mods']    = $mods;
	$GLOBALS['customify_test_meta']    = $meta;
}

function customify_test_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		fwrite(
			STDERR,
			$message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) . "\n"
		);
		exit( 1 );
	}
}

customify_test_reset(
	array(
		'page'      => true,
		'singular'  => true,
		'post_type' => 'page',
		'post_id'   => 11,
	)
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'page_content_area_spacing', $context['setting'], 'Pages resolve to the page setting.' );
customify_test_assert_same( 'inherit', customify_resolve_content_area_spacing_mode( $context ), 'Unsaved page settings inherit.' );
customify_test_assert_same( array(), customify_get_content_area_spacing_body_classes( $context ), 'Unsaved settings add no body class.' );

customify_test_reset(
	array(
		'singular'  => true,
		'post_type' => 'post',
		'post_id'   => 12,
	),
	array( 'posts_content_area_spacing' => 'top' )
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'post', $context['key'], 'Blog posts resolve to the post context.' );
customify_test_assert_same( array( 'content-area-spacing-top-only' ), customify_get_content_area_spacing_body_classes( $context ), 'Top-only suppresses bottom spacing.' );

customify_test_reset( array( 'home' => true ) );
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'posts_archives_content_area_spacing', $context['setting'], 'The blog home resolves to Blog Archives.' );

customify_test_reset(
	array(
		'singular'  => true,
		'post_type' => 'book',
		'post_id'   => 13,
	),
	array( 'book_content_area_spacing' => 'bottom' )
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'book_content_area_spacing', $context['setting'], 'CPT singles resolve dynamically.' );
customify_test_assert_same( array( 'content-area-spacing-bottom-only' ), customify_get_content_area_spacing_body_classes( $context ), 'Bottom-only suppresses top spacing.' );

customify_test_reset(
	array(
		'post_type_archive' => true,
		'post_type'         => 'book',
	),
	array( 'book_archive_content_area_spacing' => 'disabled' )
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'book_archive_content_area_spacing', $context['setting'], 'CPT archives resolve dynamically.' );
customify_test_assert_same( array( 'disable-content-vertical-padding' ), customify_get_content_area_spacing_body_classes( $context ), 'Disabled reuses the legacy body class.' );

customify_test_reset(
	array(
		'post_type_archive' => true,
		'post_type'         => 'product',
	),
	array( 'posts_archives_content_area_spacing' => 'disabled' )
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'none', $context['key'], 'The WooCommerce-owned archive has no Customify spacing context.' );
customify_test_assert_same( '', $context['setting'], 'The WooCommerce-owned archive does not read Blog Archives.' );
customify_test_assert_same( array(), customify_get_content_area_spacing_body_classes( $context ), 'Disabling Blog Archives does not change Shop spacing.' );

customify_test_reset(
	array(
		'tax'            => true,
		'queried_object' => new WP_Term( 'product_cat' ),
	),
	array( 'posts_archives_content_area_spacing' => 'disabled' )
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'none', $context['key'], 'A product-only taxonomy has no Customify spacing context.' );
customify_test_assert_same( '', $context['setting'], 'A product-only taxonomy does not read Blog Archives.' );
customify_test_assert_same( array(), customify_get_content_area_spacing_body_classes( $context ), 'Disabling Blog Archives does not change product taxonomy spacing.' );

customify_test_reset(
	array(
		'tax'            => true,
		'queried_object' => new WP_Term( 'book_genre' ),
	)
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'book_archive_content_area_spacing', $context['setting'], 'A taxonomy owned by one CPT inherits its archive.' );

customify_test_reset(
	array(
		'tax'            => true,
		'queried_object' => new WP_Term( 'shared' ),
	)
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'posts_archives_content_area_spacing', $context['setting'], 'Shared taxonomies fall back to blog archives.' );

customify_test_reset( array( 'search' => true ), array( 'search_content_area_spacing' => 'disabled' ) );
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'search_content_area_spacing', $context['setting'], 'Search resolves to its own setting.' );

customify_test_reset( array( '404' => true ), array( '404_content_area_spacing' => 'top' ) );
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( '404_content_area_spacing', $context['setting'], '404 resolves to its own setting.' );

customify_test_reset(
	array(
		'singular'  => true,
		'post_type' => 'book',
		'post_id'   => 14,
	),
	array( 'book_content_area_spacing' => 'both' ),
	array(
		14 => array( '_customify_disable_content_vertical_padding' => '1' ),
	)
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'disabled', customify_resolve_content_area_spacing_mode( $context ), 'Legacy meta wins over a context setting.' );
customify_test_assert_same( array( 'disable-content-vertical-padding' ), customify_get_content_area_spacing_body_classes( $context ), 'Legacy meta keeps its existing body class.' );

customify_test_reset(
	array(
		'singular'  => true,
		'post_type' => 'utility',
		'post_id'   => 15,
	),
	array(),
	array(
		15 => array( '_customify_disable_content_vertical_padding' => '1' ),
	)
);
$context = customify_get_content_area_spacing_context();
customify_test_assert_same( 'none', $context['key'], 'Unsupported singular types do not receive a context setting.' );
customify_test_assert_same( array( 'disable-content-vertical-padding' ), customify_get_content_area_spacing_body_classes( $context ), 'Legacy meta remains effective on unsupported singular types.' );

$inherit_requests = array(
	'page'           => array(
		'page'      => true,
		'singular'  => true,
		'post_type' => 'page',
		'post_id'   => 21,
	),
	'blog post'      => array(
		'singular'  => true,
		'post_type' => 'post',
		'post_id'   => 22,
	),
	'blog home'      => array( 'home' => true ),
	'CPT single'     => array(
		'singular'  => true,
		'post_type' => 'book',
		'post_id'   => 23,
	),
	'CPT archive'    => array(
		'post_type_archive' => true,
		'post_type'         => 'book',
	),
	'owned taxonomy' => array(
		'tax'            => true,
		'queried_object' => new WP_Term( 'book_genre' ),
	),
	'shared taxonomy' => array(
		'tax'            => true,
		'queried_object' => new WP_Term( 'shared' ),
	),
	'search'          => array( 'search' => true ),
	'404'             => array( '404' => true ),
);

foreach ( $inherit_requests as $label => $request ) {
	customify_test_reset( $request );
	customify_test_assert_same(
		array(),
		customify_get_content_area_spacing_body_classes(),
		'Unsaved inherit adds no body class for ' . $label . '.'
	);
}

echo "Content area spacing tests passed.\n";
