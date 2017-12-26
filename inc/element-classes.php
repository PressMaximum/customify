<?php
/**
 * Functions which enhance the theme by hooking into WordPerss and itself (huh?).
 *
 * @package customify
 */

if ( ! function_exists( 'customify_body_classes' ) ) :
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	add_filter( 'body_class', 'customify_body_classes' );
	function customify_body_classes( $classes ) {
		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		$layout = customify_get_layout();
		$layout_vertical_border = true;

		if ( $layout != '' ) {
			$classes[] = $layout;
		}

		if ( $layout_vertical_border ) {
			$classes[] = 'layout_vertial_border';
		}
		if ( is_customize_preview() ) {
            $classes[] = 'customize-previewing';
        }

        // Site layout mode.
		$site_layout = Customify_Customizer()->get_setting( 'site_layout' );
		if ( $site_layout ) {
			$classes[] = sanitize_text_field($site_layout);
		}

		return $classes;
	}
endif;

if ( ! function_exists( 'customify_site_classes' ) )  {
    function customify_site_classes( ){
        $classes = array();
        $classes[] = 'site';
        $box_shadow = Customify_Customizer()->get_setting( 'site_box_shadow' );
        if ( $box_shadow ) {
            $classes[] = esc_attr( $box_shadow );
        }

        $classes = apply_filters( 'customify_site_classes', $classes );

        echo 'class="' . join( ' ', $classes ) . '"';
    }
}

if ( ! function_exists( 'customify_site_content_classes' ) ) :
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
	add_filter( 'customify_site_content_class', 'customify_site_content_classes' );
endif;


if ( ! function_exists( 'customify_sidebar_primary_classes' ) ) :
	/**
	 * Adds custom classes to the array of primary sidebar classes.
	 *
	 * @param array $classes Classes for the primary sidebar element.
	 *
	 * @return array
	 */
	function customify_sidebar_primary_classes( $classes ) {

		$classes[] = 'sidebar-primary';
		$layout = customify_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( $layout == 'sidebar-content' ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		if ( $layout == 'content-sidebar' ) {
			$classes[] = 'customify-col-3_sm-12';
		}

		return $classes;
	}
	add_filter( 'customify_sidebar_primary_class', 'customify_sidebar_primary_classes' );
endif;

if ( ! function_exists( 'customify_sidebar_secondary_classes' ) ) :
	/**
	 * Adds custom classes to the array of secondary sidebar classes.
	 *
	 * @param array $classes Classes for the secondary sidebar element.
	 *
	 * @return array
	 */
	function customify_sidebar_secondary_classes( $classes ) {

		$classes[] = 'sidebar-secondary';
		$layout = customify_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = 'customify-col-3_md-0_sm-12';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = 'customify-col-3_md-0_sm-12-first'; // Not move to bottom on mobile, ueh?
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = 'customify-col-3_md-0_sm-12';
		}

		return $classes;
	}
	add_filter( 'customify_sidebar_secondary_class', 'customify_sidebar_secondary_classes' );
endif;

if ( ! function_exists( 'customify_main_content_classes' ) ) :
	/**
	 * Adds custom classes to the array of main content classes.
	 *
	 * @param array $classes Classes for the main content element.
	 *
	 * @return array
	 */
	function customify_main_content_classes( $classes ) {

		$classes[] = 'content-area';
		$layout = customify_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = 'customify-col-6_md-9_sm-12-last_sm-first';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = 'customify-col-6_md-9_sm-12';
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = 'customify-col-6_md-9_sm-12-first';
		}

		if ( $layout == 'sidebar-content' ) {
			$classes[] = 'customify-col-9_sm-12-last_sm-first';
		}

		if ( $layout == 'content-sidebar' ) {
			$classes[] = 'customify-col-9_sm-12';
		}

		if ( $layout == 'no-sidebar' ) {
			$classes[] = 'customify-col-12';
		}

		return $classes;
	}
	add_filter( 'customify_main_content_class', 'customify_main_content_classes' );
endif;

if ( ! function_exists( 'customify_site_content_grid_classes' ) ) :
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
	add_filter( 'customify_site_content_grid_class', 'customify_site_content_grid_classes' );
endif;

if ( ! function_exists( 'customify_site_content_container_classes' ) ) :
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
	add_filter( 'customify_site_content_container_class', 'customify_site_content_container_classes' );
endif;
