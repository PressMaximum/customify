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

    $product_type = $product->get_type();

    $text = '';

    switch ( $type ) {
	    case 'value':
		    if ( $product_type == 'variable' ) {
			    $available_variations = $product->get_available_variations();
			    $maximumper           = 0;
			    for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
				    $variation_id      = $available_variations[ $i ]['variation_id'];
				    $variable_product1 = new WC_Product_Variation( $variation_id );
				    //$regular_price     = $variable_product1->regular_price;
				   // $sales_price       = $variable_product1->sale_price;
				    $regular_price     = $variable_product1->get_regular_price();
				    $sales_price       = $variable_product1->get_sale_price();
				    $p = $regular_price - $sales_price;
				    if ( $p > $maximumper ) {
					    $maximumper = $p;
				    }
			    }
			    $text = wc_price(  - $maximumper );

		    } else if ( $product_type == 'simple' ) {
			    //$text = wc_price( - ( $product->regular_price - $product->sale_price )  );
			    $text = wc_price( - ( $product->get_regular_price() - $product->get_sale_price() )  );
		    }
	        break;
        case 'percent':
            if ( $product_type == 'variable' ) {
	            $available_variations = $product->get_available_variations();
	            $maximumper           = 0;
	            for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
		            $variation_id      = $available_variations[ $i ]['variation_id'];
		            $variable_product1 = new WC_Product_Variation( $variation_id );
                    $regular_price     = $variable_product1->get_regular_price();
                    $sales_price       = $variable_product1->get_sale_price();
		            $percentage        = round( ( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 ), 1 );
		            if ( $percentage > $maximumper ) {
			            $maximumper = $percentage;
		            }
	            }
	            $text = sprintf( __( '%s','customify' ), - $maximumper . '%' );

            } else if ( $product_type == 'simple' ) {
	            //$percentage = round( ( ( $product->regular_price - $product->sale_price ) / $product->regular_price ) * 100 );
	            $percentage = round( ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100 );
	            $text = sprintf( __( '%s', 'customify' ), - $percentage . '%' );
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
