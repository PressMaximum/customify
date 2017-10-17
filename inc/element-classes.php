<?php
/**
 * Functions which enhance the theme by hooking into WordPerss and itself (huh?).
 *
 * @package _beacon
 */

if ( ! function_exists( '_beacon_body_classes' ) ) :
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	add_filter( 'body_class', '_beacon_body_classes' );
	function _beacon_body_classes( $classes ) {
		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		$layout = _beacon_get_layout();
		$layout_vertical_border = true;

		if ( $layout != '' ) {
			$classes[] = $layout;
		}

		if ( $layout_vertical_border ) {
			$classes[] = 'layout_vertial_border';
		}

		return $classes;
	}
endif;

if ( ! function_exists( '_beacon_site_content_classes' ) ) :
	/**
	 * Adds custom classes to the array of site content classes.
	 *
	 * @param array $classes Classes for the site content element.
	 *
	 * @return array
	 */
	function _beacon_site_content_classes( $classes ) {
		$classes[] = 'site-content';
		return $classes;
	}
	add_filter( '_beacon_site_content_class', '_beacon_site_content_classes' );
endif;


if ( ! function_exists( '_beacon_sidebar_primary_classes' ) ) :
	/**
	 * Adds custom classes to the array of primary sidebar classes.
	 *
	 * @param array $classes Classes for the primary sidebar element.
	 *
	 * @return array
	 */
	function _beacon_sidebar_primary_classes( $classes ) {

		$classes[] = 'sidebar-primary';
		$layout = _beacon_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = '_beacon-col-3_sm-12';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = '_beacon-col-3_sm-12';
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = '_beacon-col-3_sm-12';
		}

		if ( $layout == 'sidebar-content' ) {
			$classes[] = '_beacon-col-3_sm-12';
		}

		if ( $layout == 'content-sidebar' ) {
			$classes[] = '_beacon-col-3_sm-12';
		}

		return $classes;
	}
	add_filter( '_beacon_sidebar_primary_class', '_beacon_sidebar_primary_classes' );
endif;

if ( ! function_exists( '_beacon_sidebar_secondary_classes' ) ) :
	/**
	 * Adds custom classes to the array of secondary sidebar classes.
	 *
	 * @param array $classes Classes for the secondary sidebar element.
	 *
	 * @return array
	 */
	function _beacon_sidebar_secondary_classes( $classes ) {

		$classes[] = 'sidebar-secondary';
		$layout = _beacon_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = '_beacon-col-3_md-0_sm-12';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = '_beacon-col-3_md-0_sm-12-first'; // Not move to bottom on mobile, ueh?
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = '_beacon-col-3_md-0_sm-12';
		}

		return $classes;
	}
	add_filter( '_beacon_sidebar_secondary_class', '_beacon_sidebar_secondary_classes' );
endif;

if ( ! function_exists( '_beacon_main_content_classes' ) ) :
	/**
	 * Adds custom classes to the array of main content classes.
	 *
	 * @param array $classes Classes for the main content element.
	 *
	 * @return array
	 */
	function _beacon_main_content_classes( $classes ) {

		$classes[] = 'content-area';
		$layout = _beacon_get_layout();

		if ( $layout == 'sidebar-sidebar-content' ) {
			$classes[] = '_beacon-col-6_md-9_sm-12-last_sm-first';
		}

		if ( $layout == 'sidebar-content-sidebar' ) {
			$classes[] = '_beacon-col-6_md-9_sm-12';
		}

		if ( $layout == 'content-sidebar-sidebar' ) {
			$classes[] = '_beacon-col-6_md-9_sm-12-first';
		}

		if ( $layout == 'sidebar-content' ) {
			$classes[] = '_beacon-col-9_sm-12-last_sm-first';
		}

		if ( $layout == 'content-sidebar' ) {
			$classes[] = '_beacon-col-9_sm-12';
		}

		if ( $layout == 'no-sidebar' ) {
			$classes[] = '_beacon-col-12';
		}

		return $classes;
	}
	add_filter( '_beacon_main_content_class', '_beacon_main_content_classes' );
endif;
