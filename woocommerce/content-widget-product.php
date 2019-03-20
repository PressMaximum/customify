<?php
/**
 * The template for displaying product widget entries.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$has_thumbnail = has_post_thumbnail( $product->get_id() );

?>
<li class="<?php echo ( $has_thumbnail ) ? 'has_thumbnail' : 'no_thumbnai'; ?>">
	<?php do_action( 'woocommerce_widget_product_item_start', $args ); ?>
	<div class="widget-product-item">
		<?php if ( $has_thumbnail ) { ?>
		<a class="media-info" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<?php echo $product->get_image(); ?>
		</a>
		<?php } ?>
		<span class="tex-info">
			<a class="media-info" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<span class="product-title"><?php echo $product->get_name(); ?></span>
			</a>
			<?php if ( ! empty( $show_rating ) ) : ?>
				<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
			<?php endif; ?>
			<span class="price-wrapper">
				<?php echo $product->get_price_html(); ?>
			</span>
		</span>
	</div>

	<?php do_action( 'woocommerce_widget_product_item_end', $args ); ?>
</li>
