<?php

class Customify_WC_Catalog_Designer {
	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_catalog_url' ), 35 );
		}

		// Loop
		add_action( 'customify_wc_product_loop', array( $this, 'render' ) );

	}

	function render() {

		$items = Customify()->get_setting( 'wc_cd_positions' );

		$this->product__media();

		echo '<div class="wc-product-contents">';

		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 *
		 */
		do_action( 'woocommerce_before_shop_loop_item' );

		foreach ( ( array ) $items as $item ) {
			$item = wp_parse_args( $item, array(
				'_key'         => '',
				'_visibility'  => '',
				'show_in_grid' => 1,
				'show_in_list' => 1,
			) );
			if ( $item['_visibility'] !== 'hidden' ) {
				$cb = apply_filters( 'customify/product-designer/part', false, $item['_key'] );
				if ( ! is_callable( $cb ) ) {
					$cb = array( $this, 'product__' . $item['_key'] );
				}

				if ( is_callable( $cb ) ) {
					$classes   = array();
					$classes[] = 'wc-product__part';
					$classes[] = 'wc-product__' . $item['_key'];

					if ( $item['show_in_grid'] ) {
						$classes[] = 'show-in-grid';
					} else {
						$classes[] = 'hide-in-grid';
					}
					if ( $item['show_in_list'] ) {
						$classes[] = 'show-in-list';
					} else {
						$classes[] = 'hide-in-list';
					}

					$item_html = '';
					ob_start();
					call_user_func( $cb, array() );
					$item_html = ob_get_contents();
					ob_end_clean();

					if ( trim( $item_html ) != '' ) {
						echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';
						echo $item_html;
						echo '</div>';
					}

				}
			}
		}

		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 */
		do_action( 'woocommerce_after_shop_loop_item' );

		echo '</div>'; // end .wc-product-contents

	}

	/**
	 * Preview url when section open
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	function add_catalog_url( $args ) {
		$args['section_urls']['wc_catalog_designer'] = get_permalink( wc_get_page_id( 'shop' ) );

		return $args;
	}

	function config( $configs ) {

		$section = 'wc_catalog_designer';

		$configs[] = array(
			'name'     => $section,
			'type'     => 'section',
			'panel'    => 'woocommerce',
			'priority' => 10,
			'label'    => __( 'Product Catalog Designer', 'customify' ),
		);

		// catalog header
		$configs[] = array(
			'name'            => 'wc_cd_show_catalog_header',
			'type'            => 'checkbox',
			'section'         => $section,
			'default'         => 1,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'checkbox_label'  => __( 'Show Catalog Filtering Bar', 'customify' ),
		);

		// Show view mod
		$configs[] = array(
			'name'            => 'wc_cd_show_view_mod',
			'type'            => 'checkbox',
			'section'         => $section,
			'default'         => 1,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'checkbox_label'  => __( 'Show Grid/List View Buttons', 'customify' ),
			//'required'        => array( 'wc_cd_show_catalog_header', '=', 1 ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_default_view',
			'type'            => 'select',
			'section'         => $section,
			'default'         => 'grid',
			'choices'         => array(
				'grid' => __( 'Grid', 'customify' ),
				'list' => __( 'List', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			/*
			'required'        => array(
				array( 'wc_cd_show_view_mod', '=', 1 ),
				array( 'wc_cd_show_catalog_header', '=', 1 ),
			),
			*/
			'label'           => __( 'Default View Mod', 'customify' ),
		);

		$configs[] = array(
			'name'             => "wc_cd_positions",
			'section'          => $section,
			'label'            => __( 'Item Positions', 'customify' ),
			'type'             => 'repeater',
			'title'            => __( 'Body', 'customify' ),
			'live_title_field' => 'title',
			'limit'            => 4,
			'addable'          => false,
			'selector'         => '.wc-product-listing',
			'render_callback'  => 'woocommerce_content',
			'default'          => array(
				array(
					'_key'         => 'category',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Category', 'customify' ),
				),
				array(
					'_visibility'  => '',
					'_key'         => 'title',
					'title'        => __( 'Title', 'customify' ),
					'show_in_grid' => 1,
					'show_in_list' => 1,
				),
				array(
					'_key'         => 'rating',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Rating', 'customify' ),
				),

				array(
					'_key'         => 'price',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Price', 'customify' ),
				),
				array(
					'_key'         => 'description',
					'_visibility'  => '',
					'show_in_grid' => 0,
					'show_in_list' => 1,
					'title'        => __( 'Short Description', 'customify' ),
				),
				array(
					'_key'         => 'add_to_cart',
					'_visibility'  => '',
					'show_in_grid' => 1,
					'show_in_list' => 1,
					'title'        => __( 'Add To Cart', 'customify' ),
				),
			),
			'fields'           => array(
				array(
					'name' => '_key',
					'type' => 'hidden',
				),
				array(
					'name'  => 'title',
					'type'  => 'hidden',
					'label' => __( 'Title', 'customify' ),
				),
				array(
					'name'           => 'show_in_grid',
					'type'           => 'checkbox',
					'checkbox_label' => __( 'Show in grid view', 'customify' ),
				),
				array(
					'name'           => 'show_in_list',
					'type'           => 'checkbox',
					'checkbox_label' => __( 'Show in list view', 'customify' ),
				),
			)
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_spacing',
			'type'            => 'slider',
			'device_settings' => false,
			'min'             => 0,
			'step'            => 1,
			'max'             => 100,
			'section'         => $section,
			'title'           => __( 'Item Spacing', 'customify' ),
			'selector'        => '.wc-product-inner .wc-product-contents > *',
			'css_format'      => 'margin-top: {{value}}'
		);

		// Product Media
		$configs[] = array(
			'name'    => 'wc_cd_memdia_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Media & Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_list_media_width',
			'type'            => 'slider',
			'section'         => $section,
			'unit'            => '%',
			'max'             => 100,
			'device_settings' => true,
			'selector'        => 'format',
			'css_format'      => '.woocommerce-listing.wc-list-view .product.customify-col:not(.product-category) .wc-product-inner .wc-product-media { flex-basis: {{value_no_unit}}%; } .woocommerce-listing.wc-list-view .product.customify-col:not(.product-category) .wc-product-inner .wc-product-contents{ flex-basis: calc(100% - {{value_no_unit}}%); }',
			'title'           => __( 'List View Media Width', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_media_secondary',
			'type'            => 'select',
			'choices'         => array(
				'first' => __( 'Use first image of product gallery', 'customify' ),
				'last'  => __( 'Use last image of product gallery', 'customify' ),
				'none'  => __( 'Disable', 'customify' ),
			),
			'section'         => $section,
			'default'         => 'first',
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'description'     => __( 'This setting adds a hover effect that will reveal a secondary product thumbnail to product images on your product listings. This is ideal for displaying front and back images of products.', 'customify' ),
			'title'           => __( 'Secondary Thumbnail', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_grid_align',
			'type'            => 'text_align_no_justify',
			'section'         => $section,
			'device_settings' => true,
			'selector'        => '.wc-grid-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'Grid View - Content Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_list_align',
			'type'            => 'text_align_no_justify',
			'section'         => $section,
			'device_settings' => true,
			'selector'        => '.wc-list-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'List View - Content Alignment', 'customify' ),
		);

		// Product Sale Bubble
		$configs[] = array(
			'name'    => 'wc_cd_sale_bubble_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Onsale Bubble', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_sale_bubble_type',
			'type'            => 'select',
			'default'         => 'text',
			'choices'         => array(
				'text'    => __( 'Text', 'customify' ),
				'percent' => __( 'Discount percent', 'customify' ),
				'value'   => __( 'Discount value', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'section'         => $section,
			'label'           => __( 'Display Type', 'customify' ),
		);

		return $configs;
	}

	function product__media() {
		echo '<div class="wc-product-media">';
		woocommerce_template_loop_product_link_open();
		woocommerce_show_product_loop_sale_flash();
		woocommerce_template_loop_product_thumbnail();
		customify_wc_secondary_product_thumbnail();
		do_action( 'customify_after_loop_product_media' );
		woocommerce_template_loop_product_link_close();
		echo '</div>';
	}

	function product__title() {

		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		do_action( 'woocommerce_before_shop_loop_item_title' );

		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
		do_action( 'woocommerce_shop_loop_item_title' );


		/**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item_title' );

	}

	function product__description() {
		echo '<div class="woocommerce-loop-product__desc">';
		the_excerpt();
		echo '</div>';

	}

	function product__price() {
		woocommerce_template_loop_price();
	}

	function product__rating() {
		woocommerce_template_loop_rating();
	}

	function product__category() {
		global $post;

		$tax = 'product_cat';
		$num = 1;

		$terms = get_the_terms( $post, $tax );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( empty( $terms ) ) {
			return false;
		}

		$links = array();

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $tax );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			$links[] = '<a class="text-uppercase text-xsmall link-meta" href="' . esc_url( $link ) . '" rel="tag">' . esc_html( $term->name ) . '</a>';
		}

		$categories_list = array_slice( $links, 0, $num );

		echo join( ' ', $categories_list );
	}

	function product__add_to_cart() {
		woocommerce_template_loop_add_to_cart();
	}


}

new Customify_WC_Catalog_Designer();