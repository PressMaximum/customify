<?php
/**
 * Product loop sale flash
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;
if ( $product->is_on_sale() ) {

    $type = Customify()->get_setting( 'wc_cd_sale_bubble_type' );
    switch ( $type ) {
	    case 'value':
		    if ( $product->product_type == 'variable' ) {
			    $available_variations = $product->get_available_variations();
			    $maximumper           = 0;
			    for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
				    $variation_id      = $available_variations[ $i ]['variation_id'];
				    $variable_product1 = new WC_Product_Variation( $variation_id );
				    $regular_price     = $variable_product1->regular_price;
				    $sales_price       = $variable_product1->sale_price;
				    $p = $regular_price - $sales_price;
				    if ( $p > $maximumper ) {
					    $maximumper = $p;
				    }
			    }
			    $text = wc_price(  - $maximumper );

		    } else if ( $product->product_type == 'simple' ) {
			    $text = wc_price( - ( $product->regular_price - $product->sale_price )  );
		    }
	        break;
        case 'percent':
            if ( $product->product_type == 'variable' ) {
	            $available_variations = $product->get_available_variations();
	            $maximumper           = 0;
	            for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
		            $variation_id      = $available_variations[ $i ]['variation_id'];
		            $variable_product1 = new WC_Product_Variation( $variation_id );
		            $regular_price     = $variable_product1->regular_price;
		            $sales_price       = $variable_product1->sale_price;
		            $percentage        = round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ), 1 );
		            if ( $percentage > $maximumper ) {
			            $maximumper = $percentage;
		            }
	            }
	            $text = sprintf( __( '%s', 'woocommerce', 'customify' ), - $maximumper . '%' );

            } else if ( $product->product_type == 'simple' ) {
	            $percentage = round( ( ( $product->regular_price - $product->sale_price ) / $product->regular_price ) * 100 );
	            $text = sprintf( __( '%s', 'woocommerce', 'customify' ), - $percentage . '%' );
            }
            break;
        default:
            $text = false;
    }
    if ( ! $text ) {
	    $text = esc_html__( 'Sale!', 'customify' );
    }

	$text = apply_filters( 'woocommerce_sale_flash', '<span class="onsale">'.$text.'</span>' , $post, $product );

    echo $text;

}
