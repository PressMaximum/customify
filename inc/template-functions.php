<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package customify
 */

if ( ! function_exists( 'customify_get_layout' ) ) {
	/**
	 * Get the layout for the current page from Customizer setting or individual page/post.
	 * @since 0.0.1
	 */
	function customify_get_layout() {
		global $site_layout;
		return $site_layout;
	}
}

if ( ! function_exists( 'customify_get_sidebars' ) ) {
	/**
	 * Display primary or/and secondary sidebar base on layout setting.
	 * @since 0.0.1
	 */
	function customify_get_sidebars() {

		// Get the current layout
		$layout = customify_get_layout();

		// Layout with 2 column
		$layout_2_columns = array( 'sidebar-content', 'content-sidebar' );

		// Layout with 3 column
		$layout_3_columns = array( 'sidebar-sidebar-content', 'sidebar-content-sidebar', 'content-sidebar-sidebar' );

		// Only show primary sidebar for 2 column layout
		if ( in_array( $layout , $layout_2_columns) ) {
			get_sidebar();
		}

		// Show both sidebar for 3 column layout
		if ( in_array( $layout, $layout_3_columns ) ) {
			get_sidebar();
			get_sidebar('secondary');
		}

	}
	add_action( 'customify_sidebars', 'customify_get_sidebars' );
}

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function customify_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'customify_pingback_header' );
