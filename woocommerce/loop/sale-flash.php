<?php
/**
 * Product loop sale flash
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly ?
	exit;
}

global $post, $product;
if ( $product->is_on_sale() ) {

	$type = Customify()->get_setting( 'wc_cd_sale_bubble_type' );

	$product_type = $product->get_type();

	$text = '';

	switch ( $type ) {
		case 'value':
			if ( 'variable' == $product_type ) {
				$available_variations = $product->get_available_variations();
				$maximumper           = 0;
				$n = count( $available_variations );
				for ( $i = 0; $i < $n; ++ $i ) {
					$variation_id      = $available_variations[ $i ]['variation_id'];
					$variable_product1 = new WC_Product_Variation( $variation_id );
					$regular_price     = $variable_product1->get_regular_price();
					$sales_price       = $variable_product1->get_sale_price();
					if ( is_numeric( $regular_price ) && is_numeric( $sales_price ) ) {
						$p = $regular_price - $sales_price;
						if ( $p > $maximumper ) {
							$maximumper = $p;
						}
					}
				}
				$text = wc_price( - $maximumper );

			} elseif ( 'simple' == $product_type ) {
				$text = wc_price( - ( $product->get_regular_price() - $product->get_sale_price() ) );
			}
			break;
		case 'percent':
			if ( 'variable' == $product_type ) {
				$available_variations = $product->get_available_variations();
				$maximumper           = 0;
				$n = count( $available_variations );
				for ( $i = 0; $i < $n; ++ $i ) {
					$variation_id      = $available_variations[ $i ]['variation_id'];
					$variable_product1 = new WC_Product_Variation( $variation_id );
					$regular_price     = $variable_product1->get_regular_price();
					$sales_price       = $variable_product1->get_sale_price();
					if ( is_numeric( $regular_price ) && is_numeric( $sales_price ) ) {
						$percentage = round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ), 1 );
						if ( $percentage > $maximumper ) {
							$maximumper = $percentage;
						}
					}
				}

				$text = sprintf(
					'%s',
					- $maximumper . '%'
				);

			} elseif ( 'simple' == $product_type ) {
				$percentage = round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 );

				$text = sprintf(
					'%s',
					- $percentage . '%'
				);
			}
			break;
		default:
			$text = false;
	}
	if ( ! $text ) {
		$text = esc_html__( 'Sale!', 'customify' );
	}

	$text = apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . $text . '</span>', $post, $product );
	echo $text;
}
