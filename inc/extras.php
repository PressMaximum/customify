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
