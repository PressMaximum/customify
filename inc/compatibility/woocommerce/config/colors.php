<?php

class Customify_WC_Colors {
	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
	}


	function config( $configs ) {
		$section = 'global_styling';

		$configs[] = array(
			'name'    => "{$section}_shop_colors_heading",
			'type'    => 'heading',
			'section' => $section,
			'title'   => __( 'Shop Colors', 'customify' ),
		);

		$configs[] = array(
			'name'        => "{$section}_shop_primary",
			'type'        => 'color',
			'section'     => $section,
			'title'       => __( 'Shop Buttons', 'customify' ),
			'description' => __( 'Color for add to cart, checkout buttons. Default is Secondary color', 'customify' ),
			'css_format'  => apply_filters( 'customify/styling/shop-buttons', '
					.button.add_to_cart_button, 
					.button.alt, .button.added_to_cart, 
					.button.checkout, 
					.button.product_type_variable,
					.item--wc_cart .cart-icon .cart-qty .customify-wc-total-qty
					{
					    background-color: {{value}};
					}'
			),
			'selector'    => 'format',
		);

		$configs[] = array(
			'name'       => "{$section}_shop_rating_stars",
			'type'       => 'color',
			'section'    => $section,
			'title'      => __( 'Rating Stars', 'customify' ),
			'css_format' => apply_filters( 'customify/styling/shop-rating-stars', '
					.comment-form-rating a, 
					.star-rating,
					.comment-form-rating a:hover, 
					.comment-form-rating a:focus, 
					.star-rating:hover, 
					.star-rating:focus
					{
					    color: {{value}};
					}'
			),
			'selector'   => 'format',
		);

		return $configs;
	}
}

new Customify_WC_Colors();