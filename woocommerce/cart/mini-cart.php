<?php
/**
 * Mini-cart
 *
 * @package WooCommerce/Templates
 * @version 7.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

				if ( $product_name ) {
					$product_name = '<span class="mini_cart_item__title">' . $product_name . '</span>';
				}

				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					<?php
					if ( $thumbnail ) {
						if ( ! empty( $product_permalink ) ) {
							echo '<a class="mini_cart_item__thumb" href="' . esc_url( $product_permalink ) . '">';
							echo $thumbnail; // WPCS: XSS OK.
							echo '</a>';
						} else {
							echo '<span class="mini_cart_item__thumb">';
							echo $thumbnail; // WPCS: XSS OK.
							echo '</span>';
						}
					}
					?>
					<span class="mini_cart_item__info">
					<?php
					if ( empty( $product_permalink ) ) :
						?>
						<?php
						echo $product_name; // WPCS: XSS OK.
						?>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>">
							<?php echo $product_name; // // WPCS: XSS OK. ?>
						</a>
					<?php endif; ?>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
						<?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity text-xsmall">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); ?>
					</span>
					<?php

					echo apply_filters(
						'woocommerce_cart_item_remove_link',
						sprintf(
							'<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							__( 'Remove this item', 'customify' ),
							esc_attr( $product_id ),
							esc_attr( $cart_item_key ),
							esc_attr( $_product->get_sku() )
						),
						$cart_item_key
					);

					?>
				</li>
				<?php
			}
		}
		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<div class="wc-mini-cart-footer">
		<p class="woocommerce-mini-cart__total total"><?php _e( 'Subtotal', 'customify' ); ?>: <?php echo WC()->cart->get_cart_subtotal(); ?></p>
		<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>
	</div>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php _e( 'No products in the cart.', 'customify' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
