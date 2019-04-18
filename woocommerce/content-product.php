<?php
/**
 * The template for displaying product content within loops
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li 
<?php
if ( function_exists( 'wc_product_class' ) ) {
	wc_product_class( '', $product );
} else {
	echo 'class="product customify-col"';
};
?>
>
	<div class="wc-product-inner">
	<?php

	/**
	 * All hooks moved to catalog designer
	 *
	 * @see Customify_WC_Catalog_Designer::render();
	 */
	do_action( 'customify_wc_product_loop' );

	?>
	</div>
</li>
