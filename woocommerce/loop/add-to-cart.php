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
$cart_icon = Customify()->get_setting('wc_cd_button_cart_icon');
if ( ! $icon_pos ) {
	$icon_pos = 'before';
}

$icon = wp_parse_args( $icon, array(
	'icon' => ''
) );

$cart_icon = wp_parse_args( $cart_icon, array(
	'icon' => ''
) );

$icon_tag = '';
if ( ! $show_text ) {
	$text = __( 'Add to cart', 'customify' );
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
	sprintf( '<a href="%1$s" data-quantity="%2$s" class="%3$s" data-icon-pos="%6$s" data-cart-icon="%7$s" %4$s>%5$s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
		isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		$text,
		esc_attr( $icon_pos ),
		esc_attr( $cart_icon['icon'] )
	),
$product, $args );
