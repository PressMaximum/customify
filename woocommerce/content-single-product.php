<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;
global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.

	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * Removed from theme woocommerce_show_product_sale_flash.
	 * Removed from theme woocommerce_show_product_images.
	 */
	do_action( 'woocommerce_before_single_product_summary' );
	?>
	<?php

	// Single product class.
	$has_col = 0;
	if ( has_action( 'woocommerce_single_product_media' ) || has_action( 'woocommerce_single_product_summary' ) ) {
		$has_col = 1;
		if ( has_action( 'woocommerce_single_product_media' ) && has_action( 'woocommerce_single_product_summary' ) ) {
			$has_col = 2;
		}
	}

	$class = array(
		'left'  => '',
		'right' => '',
	);


	if ( 2 == $has_col ) {
		$class = apply_filters(
			'customify/wc_single_layout_size',
			array(
				'left'  => 'customify-col-6_md-6_sm-12_xs-12',
				'right' => 'customify-col-6_md-6_sm-12_xs-12',
			)
		);
		echo '<div class="customify-grid wc-layout-columns">';
	}

	if ( has_action( 'woocommerce_single_product_media' ) ) { ?>
		<div class="media-product-media <?php echo esc_attr( $class['left'] ); ?>">
			<?php
			/**
			 * Hook woocommerce_single_product_media
			 *
			 * Add from theme. By default plugin wc have not this hook.
			 *
			 * @hooked woocommerce_show_product_images - 20
			 */
			do_action( 'woocommerce_single_product_media' ) ?>
		</div>
	<?php } ?>

	<?php if ( has_action( 'woocommerce_single_product_summary' ) ) { ?>
		<div class="summary entry-summary  <?php echo esc_attr( $class['right'] ); ?>">
			<div class="entry-summary-inner">

				<?php
				if ( has_action( 'woocommerce_single_product_summary_before' ) ) {
					echo '<div class="entry-summary-before">';
					/**
					 * Hook: woocommerce_single_product_summary_before.
					 *
					 * This is new Hook from theme
					 */
					do_action( 'woocommerce_single_product_summary_before' );
					echo '</div>';
				}
				?>

				<div class="entry-summary-box <?php echo esc_attr( apply_filters( 'woocommerce_single_product_summary_classes', '' ) ); ?>">
					<?php
					/**
					 * Hook: woocommerce_single_product_summary.
					 *
					 * @hooked woocommerce_template_single_title - 5
					 * @hooked woocommerce_template_single_rating - 10
					 * @hooked woocommerce_template_single_price - 10
					 * @hooked woocommerce_template_single_excerpt - 20
					 * @hooked woocommerce_template_single_add_to_cart - 30
					 * @hooked woocommerce_template_single_meta - 40
					 * @hooked woocommerce_template_single_sharing - 50
					 * @hooked WC_Structured_Data::generate_product_data() - 60
					 */
					do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
				<?php

				if ( has_action( 'woocommerce_single_product_summary_after' ) ) {
					echo '<div class="entry-summary-after">';
					/**
					 * Hook: woocommerce_single_product_summary_after.
					 *
					 * This is new Hook from theme
					 */
					do_action( 'woocommerce_single_product_summary_after' );
					echo '</div>';
				}
				?>
			</div>
		</div>
	<?php } ?>

	<?php

	if ( 2 == $has_col ) {
		echo '</div>';
	}

	?>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
