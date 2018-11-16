<?php
/**
 * Loop Add to Cart
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
$text = $product->add_to_cart_text();
if ( ! isset( $args['class'] ) ) {
	$args['class'] = 'button';
}

if ( strpos( $args['class'], 'add_to_cart_button' ) === false ) {
	$args['class'] .= ' add_to_cart_button';
}

echo apply_filters(
	'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
	sprintf(
		'<a href="%1$s" data-quantity="%2$s" class="%3$s" %4$s><span class="button-label">%5$s</span></a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
		esc_attr( $args['class'] ),
		isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
		$text
	),
	$product,
	$args
);
