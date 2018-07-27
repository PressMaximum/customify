<?php

class Customify_WC_Single_Product {
	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_product_url' ), 35 );
		}

		add_action( 'wp', array( $this, 'single_product_hooks' ) );
	}

	function single_product_hooks() {
		if ( ! is_product() ) {
			return;
		}

		if ( Customify()->get_setting('wc_single_product_tab_hide_description') ) {
			add_filter( 'woocommerce_product_description_heading', '__return_false', 999 );
		}

		if ( Customify()->get_setting( 'wc_single_product_tab_hide_attr_heading' ) ) {
			add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 999 );
		}

	}

	function add_product_url( $args ) {

		$query = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'orderby'        => 'rand',
		) );

		$products = $query->get_posts();
		if ( count( $products ) ) {
			$args['section_urls']['wc_single_product'] = get_permalink( $products[0] );
		}

		return $args;
	}

	function config( $configs ) {
		$section = 'wc_single_product';

		$configs[] = array(
			'name'  => $section,
			'type'  => 'section',
			'panel' => 'woocommerce',
			'title' => __( 'Single Product', 'customify' ),
		);

		$configs[] = array(
			'name'    => "{$section}_tab_heading",
			'type'    => 'heading',
			'section' => $section,
			'title'   => __( 'Product Tabs', 'customify' ),
		);

		$configs[] = array(
			'name'    => "{$section}_tab",
			'type'    => 'radio_group',
			'default' => 'horizontal',
			'section' => $section,
			'label'   => __( 'Tab Layout', 'customify' ),
			'choices' => array(
				'horizontal' => __( 'Horizontal', 'customify' ),
				'vertical'   => __( 'Vertical', 'customify' )
			)
		);

		$configs[] = array(
			'name' => "{$section}_tab_hide_description",
			'type' => 'checkbox',
			'default' => 1,
			'section' =>  $section,
			'checkbox_label' => __( 'Hide product description heading', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab_hide_attr_heading",
			'type' => 'checkbox',
			'default' => 1,
			'section' =>  $section,
			'checkbox_label' => __( 'Hide product additional information heading', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab_hide_review_heading",
			'type' => 'checkbox',
			'default' => 0,
			'section' =>  $section,
			'checkbox_label' => __( 'Hide product review heading', 'customify' ),
			'selector'       => '.woocommerce-Reviews-title',
			'css_format'     => 'display: none;',
		);

		$configs[] = array(
			'name'    => "{$section}_upsell_heading",
			'type'    => 'heading',
			'section' => $section,
			'title'   => __( 'Upsell Products', 'customify' ),
		);

        $configs[] = array(
            'name'    => "{$section}_upsell_number",
            'type'    => 'text',
            'default' => 3,
            'section' => $section,
            'label'   => __( 'Number of upsell products', 'customify' ),
        );

        $configs[] = array(
            'name'            => "{$section}_upsell_columns",
            'type'            => 'text',
            'device_settings' => true,
            'section'         => $section,
            'label'           => __( 'Upsell products per row', 'customify' ),
        );

		$configs[] = array(
			'name'    => "{$section}_related_heading",
			'type'    => 'heading',
			'section' => $section,
			'title'   => __( 'Related Products', 'customify' ),
		);

		$configs[] = array(
			'name'    => "{$section}_related_number",
			'type'    => 'text',
			'default' => 3,
			'section' => $section,
			'label'   => __( 'Number of related products', 'customify' ),
		);

		$configs[] = array(
			'name'            => "{$section}_related_columns",
			'type'            => 'text',
			'device_settings' => true,
			'section'         => $section,
			'label'           => __( 'Related products per row', 'customify' ),
		);

		return $configs;
	}
}

new Customify_WC_Single_Product();