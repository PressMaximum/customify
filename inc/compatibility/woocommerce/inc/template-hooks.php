<?php

/**
 * Loop Layout
 */
add_action( 'woocommerce_before_shop_loop_item', 'customify_wc_before_shop_loop_item', 1 );
add_action( 'woocommerce_before_subcategory', 'customify_wc_before_shop_loop_item', 1 );

add_action( 'woocommerce_after_shop_loop_item', 'customify_wc_after_shop_loop_item', 9999 );
add_action( 'woocommerce_after_subcategory', 'customify_wc_after_shop_loop_item', 9999 );

function customify_wc_before_shop_loop_item(){
	echo '<div class="wc-product-inner">';
}

function customify_wc_after_shop_loop_item(){
	echo '</div>';
}


add_filter( 'woocommerce_after_output_product_categories', 'customify_wc_after_output_product_categories' );
function customify_wc_after_output_product_categories( $html ){
	if ( wc_get_loop_prop( 'is_shortcode' ) && ! WC_Template_Loader::in_content_filter() ) {
		return $html;
	}
	return '<li class="wc-loop-cats-separator"></li>';
}



remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_after_shop_loop_item', 'customify_wc_product_footer', 15 );
add_action( 'customify_wc_product_footer', 'woocommerce_template_loop_add_to_cart', 15 );
function customify_wc_product_footer(){
	if ( has_action( 'customify_wc_product_footer' ) ) {
		?>
		<div class="wc-product-footer">
			<?php do_action( 'customify_wc_product_footer' ); ?>
		</div>
		<?php
	}
}
