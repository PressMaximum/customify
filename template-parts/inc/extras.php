<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Customify
 */

/**
 * Support Elementor plugin
 */
if ( ! defined( 'ELEMENTOR_PARTNER_ID' ) ) {
	define( 'ELEMENTOR_PARTNER_ID', 2123 );
}

/**
 * Support Beaver Builder plugin
 */
function customify_bb_upgrade_link() {
	return 'https://www.wpbeaverbuilder.com/pricing/?fla=2294';
}

add_filter( 'fl_builder_upgrade_url', 'customify_bb_upgrade_link' );

/**
 * Support WPForms plugin
 */
if ( ! defined( 'WPFORMS_SHAREASALE_ID' ) ) {
	define( 'WPFORMS_SHAREASALE_ID', '1816909' );
}

/**
 * Filter the OrbitFox plugin suggestions.
 */
function customify_remove_of_sdk_suggestions() {
	__return_empty_string();
}

add_filter( 'themeisle_sdk_recommend_plugin_or_theme', 'customify_remove_of_sdk_suggestions', 100 );

/**
 * Filter the output of archive links.
 */
if ( ! function_exists( 'customify_get_archives_link' ) ) {
	/**
	 * @see get_archives_link
	 *
	 * @param string $link_html
	 * @param string $url
	 * @param string $text
	 * @param string $format
	 * @param string $before
	 * @param string $after
	 *
	 * @return string
	 */
	function customify_get_archives_link( $link_html, $url, $text, $format = 'html', $before = '', $after = '' ) {
		if ( 'link' == $format ) {
			$link_html = "\t<link rel='archives' title='" . esc_attr( $text ) . "' href='$url' />\n";
		} elseif ( 'option' == $format ) {
			$link_html = "\t<option value='$url'>$before $text $after</option>\n";
		} elseif ( 'html' == $format ) {
			$link_html = "\t<li><a href='$url'>{$before}{$text}{$after}</a></li>\n";
		} else // custom.
		{
			$link_html = "\t<a href='$url'>{$before}{$text}{$after}</a>\n";
		}

		return $link_html;
	}
}
add_filter( 'get_archives_link', 'customify_get_archives_link', 15, 6 );
