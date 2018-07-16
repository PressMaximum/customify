<?php
/**
 * The template for displaying product content within loops
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php if ( function_exists('wc_product_class') ) { wc_product_class(); } else {  echo 'class="product customify-col"'; }; ?>>
    <div class="wc-product-inner">
    <?php

    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     */
    do_action( 'woocommerce_before_shop_loop_item' );


    do_action('customify_wc_product_loop' );

    /**
     * Hook: woocommerce_after_shop_loop_item.
     *
     */
    do_action( 'woocommerce_after_shop_loop_item' );
	?>
    </div>
</li>
