<?php
/**
 * Functions which enhance the theme by hooking into WordPerss and itself (huh?).
 *
 * @package customify
 */

if ( ! function_exists( 'customify_body_classes' ) ) {
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	function customify_body_classes( $classes ) {

		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		$layout = customify_get_layout();
		if ( '' != $layout ) {
			$classes[] = $layout;
			/**
			 * Add more layout classs
			 *
			 * @since 0.2.6
			 */
			$classes[] = 'main-layout-' . $layout;
		}

		$sidebar_vertical_border = Customify()->get_setting( 'sidebar_vertical_border' );
		if ( 'sidebar_vertical_border' == $sidebar_vertical_border ) {
			$classes[] = 'sidebar_vertical_border';
		}

		if ( is_customize_preview() ) {
			$classes[] = 'customize-previewing';
		}

		// Site layout mode.
		$site_layout = sanitize_text_field( Customify()->get_setting( 'site_layout' ) );
		if ( $site_layout ) {
			$classes[] = $site_layout;
		}

		$animate = Customify()->get_setting( 'header_sidebar_animate' );
		if ( ! $animate ) {
			$animate = 'menu_sidebar_slide_left';
		}
		$classes[] = $animate;

		return $classes;
	}
}
add_filter( 'body_class', 'customify_body_classes' );


if ( ! function_exists( 'customify_site_classes' ) ) {
	function customify_site_classes() {
		$classes    = array();
		$classes[]  = 'site';
		$box_shadow = Customify()->get_setting( 'site_box_shadow' );
		if ( $box_shadow ) {
			$classes[] = esc_attr( $box_shadow );
		}

		$classes = apply_filters( 'customify_site_classes', $classes );

		echo 'class="' . join( ' ', $classes ) . '"';
	}
}

if ( ! function_exists( 'customify_site_content_classes' ) ) {
	/**
	 * Adds custom classes to the array of site content classes.
	 *
	 * @param array $classes Classes for the site content element.
	 *
	 * @return array
	 */
	function customify_site_content_classes( $classes ) {
		$classes[] = 'site-content';

		return $classes;
	}
}

add_filter( 'customify_site_content_class', 'customify_site_content_classes' );


if ( ! function_exists( 'customify_sidebar_primary_classes' ) ) {
	/**
	 * Adds custom classes to the array of primary sidebar classes.
	 *
	 * @param array $classes Classes for the primary sidebar element.
	 *
	 * @return array
	 */
	function customify_sidebar_primary_classes( $classes ) {

		$classes[] = 'sidebar-primary';
		$layout    = customify_get_layout();

		if ( 'sidebar-sidebar-content' == $layout ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( 'sidebar-content-sidebar' == $layout ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( 'content-sidebar-sidebar' == $layout ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( 'sidebar-content' == $layout ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( 'content-sidebar' == $layout ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		return $classes;
	}
}
add_filter( 'customify_sidebar_primary_class', 'customify_sidebar_primary_classes' );

if ( ! function_exists( 'customify_sidebar_secondary_classes' ) ) {
	/**
	 * Adds custom classes to the array of secondary sidebar classes.
	 *
	 * @param array $classes Classes for the secondary sidebar element.
	 *
	 * @return array
	 */
	function customify_sidebar_secondary_classes( $classes ) {

		$classes[] = 'sidebar-secondary';
		$layout    = customify_get_layout();

		if ( 'sidebar-sidebar-content' == $layout ) {
			$classes[] = 'customify-col-3_md-0_sm-12';
		}

		if ( 'sidebar-content-sidebar' == $layout ) {
			$classes[] = 'customify-col-3_md-0_sm-12-first'; // Not move to bottom on mobile, ueh?
		}

		if ( 'content-sidebar-sidebar' == $layout ) {
			$classes[] = 'customify-col-3_md-0_sm-12';
		}

		return $classes;
	}
}
add_filter( 'customify_sidebar_secondary_class', 'customify_sidebar_secondary_classes' );

if ( ! function_exists( 'customify_main_content_classes' ) ) {
	/**
	 * Adds custom classes to the array of main content classes.
	 *
	 * @param array $classes Classes for the main content element.
	 *
	 * @return array
	 */
	function customify_main_content_classes( $classes ) {

		$classes[] = 'content-area';
		$layout    = customify_get_layout();

		if ( 'sidebar-sidebar-content' == $layout ) {
			$classes[] = 'customify-col-6_md-9_sm-12-last_sm-first';
		}

		if ( 'sidebar-content-sidebar' == $layout ) {
			$classes[] = 'customify-col-6_md-9_sm-12';
		}

		if ( 'content-sidebar-sidebar' == $layout ) {
			$classes[] = 'customify-col-6_md-9_sm-12-first';
		}

		if ( 'sidebar-content' == $layout ) {
			$classes[] = 'customify-col-9_sm-12-last_sm-first';
		}

		if ( 'content-sidebar' == $layout ) {
			$classes[] = 'customify-col-9_sm-12';
		}

		if ( 'content' == $layout ) {
			$classes[] = 'customify-col-12';
		}

		return $classes;
	}
}
add_filter( 'customify_main_content_class', 'customify_main_content_classes' );

if ( ! function_exists( 'customify_site_content_grid_classes' ) ) {
	/**
	 * Adds custom classes to the array of site content grid classes.
	 *
	 * @param array $classes Classes for the main content element.
	 *
	 * @return array
	 */
	function customify_site_content_grid_classes( $classes ) {

		$classes[] = 'customify-grid';

		return $classes;
	}
}
add_filter( 'customify_site_content_grid_class', 'customify_site_content_grid_classes' );

if ( ! function_exists( 'customify_site_content_container_classes' ) ) {
	/**
	 * Adds custom classes to the array of site content container classes.
	 *
	 * @param array $classes Classes for the main content element.
	 *
	 * @return array
	 */
	function customify_site_content_container_classes( $classes ) {

		$classes[] = 'customify-container';

		return $classes;
	}
}
add_filter( 'customify_site_content_container_class', 'customify_site_content_container_classes' );
