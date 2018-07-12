<?php


remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', 'customify_catalog_header', 15 );


function customify_catalog_header(){
    echo '<div class="wc-catalog-header">';
	customify_catalog_view_mod();
	woocommerce_catalog_ordering();
	woocommerce_result_count();
	echo '</div>';
}



function customify_catalog_view_mod(){
    $default = customify_get_default_catalog_view_mod();
    ?>
    <div class="wc-view-switcher">
        <span class="wc-view-mod wc-grid-view <?php echo ( $default == 'grid' ) ? 'active' : ''; ?>" data-mod="grid">
            <svg version="1.1"  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 187.733 187.733"  xml:space="preserve"><g><g><path d="M85.333,0H3.413C1.529,0,0,1.529,0,3.413v81.92c0,1.884,1.529,3.413,3.413,3.413h81.92c1.886,0,3.413-1.529,3.413-3.413V3.413C88.747,1.529,87.219,0,85.333,0z M81.92,81.92H6.827V6.827H81.92V81.92z"/></g></g><g><g><path d="M184.32,0H102.4c-1.886,0-3.413,1.529-3.413,3.413v81.92c0,1.884,1.527,3.413,3.413,3.413h81.92c1.886,0,3.413-1.529,3.413-3.413V3.413C187.733,1.529,186.206,0,184.32,0z M180.907,81.92h-75.093V6.827h75.093V81.92z"/></g></g><g><g><path d="M85.333,98.987H3.413C1.529,98.987,0,100.516,0,102.4v81.92c0,1.884,1.529,3.413,3.413,3.413h81.92c1.886,0,3.413-1.529,3.413-3.413V102.4C88.747,100.516,87.219,98.987,85.333,98.987z M81.92,180.907H6.827v-75.093H81.92V180.907z"/></g></g><g><g><path d="M184.32,98.987H102.4c-1.886,0-3.413,1.529-3.413,3.413v81.92c0,1.884,1.527,3.413,3.413,3.413h81.92c1.886,0,3.413-1.529,3.413-3.413V102.4C187.733,100.516,186.206,98.987,184.32,98.987z M180.907,180.907h-75.093v-75.093h75.093V180.907z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
        </span>
        <span class="wc-view-mod wc-list-view <?php echo ( $default == 'list' ) ? 'active' : ''; ?>" data-mod="list">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 490.1 490.1"  xml:space="preserve"><g><g><path d="M32.1,141.15h76.7c17.7,0,32.1-14.4,32.1-32.1v-76.7c0-17.7-14.4-32.1-32.1-32.1H32.1C14.4,0.25,0,14.65,0,32.35v76.7C0,126.75,14.4,141.15,32.1,141.15z M24.5,32.35c0-4.2,3.4-7.6,7.6-7.6h76.7c4.2,0,7.6,3.4,7.6,7.6v76.7c0,4.2-3.4,7.6-7.6,7.6H32.1c-4.2,0-7.6-3.4-7.6-7.6V32.35z"/><path d="M0,283.45c0,17.7,14.4,32.1,32.1,32.1h76.7c17.7,0,32.1-14.4,32.1-32.1v-76.7c0-17.7-14.4-32.1-32.1-32.1H32.1c-17.7,0-32.1,14.4-32.1,32.1V283.45z M24.5,206.65c0-4.2,3.4-7.6,7.6-7.6h76.7c4.2,0,7.6,3.4,7.6,7.6v76.7c0,4.2-3.4,7.6-7.6,7.6H32.1c-4.2,0-7.6-3.4-7.6-7.6V206.65z"/><path d="M0,457.75c0,17.7,14.4,32.1,32.1,32.1h76.7c17.7,0,32.1-14.4,32.1-32.1v-76.7c0-17.7-14.4-32.1-32.1-32.1H32.1c-17.7,0-32.1,14.4-32.1,32.1V457.75z M24.5,381.05c0-4.2,3.4-7.6,7.6-7.6h76.7c4.2,0,7.6,3.4,7.6,7.6v76.7c0,4.2-3.4,7.6-7.6,7.6H32.1c-4.2,0-7.6-3.4-7.6-7.6V381.05z"/><path d="M477.8,31.75H202.3c-6.8,0-12.3,5.5-12.3,12.3c0,6.8,5.5,12.3,12.3,12.3h275.5c6.8,0,12.3-5.5,12.3-12.3C490.1,37.25,484.6,31.75,477.8,31.75z"/><path d="M477.8,85.15H202.3c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h275.5c6.8,0,12.3-5.5,12.3-12.3C490,90.65,484.6,85.15,477.8,85.15z"/><path d="M477.8,206.05H202.3c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h275.5c6.8,0,12.3-5.5,12.3-12.3C490,211.55,484.6,206.05,477.8,206.05z"/><path d="M477.8,259.55H202.3c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h275.5c6.8,0,12.3-5.5,12.3-12.3C490,265.05,484.6,259.55,477.8,259.55z"/><path d="M477.8,380.45H202.3c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h275.5c6.8,0,12.3-5.5,12.3-12.3C490,385.95,484.6,380.45,477.8,380.45z"/><path d="M490,446.15c0-6.8-5.5-12.3-12.3-12.3H202.3c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h275.5C484.6,458.35,490,452.85,490,446.15z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
        </span>
    </div>
    <?php
}

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


