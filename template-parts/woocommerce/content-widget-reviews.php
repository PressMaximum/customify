<?php
/**
 * The template for displaying product widget entries.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-widget-reviews.php
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * @var $product WC_Product
 */
global $product;
if ( ! is_object( $product ) ) {
	return;
}

$has_thumbnail = has_post_thumbnail( $product->get_id() );

?>
<li class="<?php echo ( $has_thumbnail ) ? 'has_thumbnail' : 'no_thumbnai'; ?>">
	<?php do_action( 'woocommerce_widget_product_review_item_start', $args ); ?>
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
			<?php echo wc_get_rating_html( intval( get_comment_meta( $comment->comment_ID, 'rating', true ) ) ); ?>
			<span class="reviewer"><?php echo sprintf( esc_html__( 'by %s', 'customify' ), get_comment_author( $comment->comment_ID ) ); ?></span>
		</span>
	</div>
	<?php do_action( 'woocommerce_widget_product_review_item_end', $args ); ?>
</li>



