<?php

/**
 * Cart config
 *
 * Class Customify_WC_Cart.
 *
 * @since 0.2.2
 */
class Customify_WC_Cart {
	public function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_cart_url' ), 35 );
		}

		add_action( 'wp', array( $this, 'cart_hooks' ) );
	}

	public function cart_hooks() {
		if ( ! is_cart() ) {
			return;
		}

		$hide_cross_sell = Customify()->get_setting( 'wc_cart_page_hide_cross_sells' );
		if ( $hide_cross_sell ) {
			remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
			remove_action( 'woocommerce_after_cart_table', 'woocommerce_cross_sell_display' );
		}

	}

	public function add_cart_url( $args ) {
		$args['section_urls']['wc_cart_page'] = get_permalink( wc_get_page_id( 'cart' ) );

		return $args;
	}

	public function config( $configs ) {
		$section = 'wc_cart_page';

		$configs[] = array(
			'name'  => $section,
			'type'  => 'section',
			'panel' => 'woocommerce',
			'title' => __( 'Cart', 'customify' ),
		);

		$configs[] = array(
			'name'           => "{$section}_hide_cross_sells",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Hide cross-sells', 'customify' ),
		);

		return $configs;
	}
}

new Customify_WC_Cart();
