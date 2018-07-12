<?php
/**
 * Loop Add to Cart

 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
$text = $product->add_to_cart_text();

$show_text = Customify()->get_setting('wc_cd_button_show_label');
$show_icon = Customify()->get_setting('wc_cd_button_show_icon');
$icon_pos = Customify()->get_setting('wc_cd_button_icon_pos');
$icon = Customify()->get_setting('wc_cd_button_icon');


$icon = wp_parse_args( $icon, array(
	'icon' => ''
) );

$icon_tag = '';
if ( ! $show_text ) {
	$text = '';
}
if ( $show_icon ) {
	if ( $icon['icon'] ) {
		$icon_tag = '<i class="'.esc_attr( $icon['icon'] ).'"></i>';
	}
	if ( $icon_pos == 'after' ) {
		$text = $text.' '.$icon_tag;
	} else {
		$text = $icon_tag.' '.$text;
	}
}


echo apply_filters( 'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
	sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
		isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		$text
	),
$product, $args );
