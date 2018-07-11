<?php
class Customify_WC_Catalog_Designer {
	function __construct() {
		add_filter('customify/customizer/config', array($this, 'config'), 100 );
		if( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_catalog_url' ), 35 );
		}


		// Looop
		add_action( 'customify_wc_product_loop', array( $this, 'render' ) );

	}

	function render(){

		$items = Customify()->get_setting('wc_cd_positions');

		foreach ( ( array ) $items as $item ) {
			$item = wp_parse_args( $item, array(
				'_key' => '',
				'_visibility' => '',
			) );
			if ( $item['_visibility'] !== 'hidden' ) {
				$cb = array( $this, 'product__'. $item['_key'] );
				if ( is_callable( $cb ) ) {
					call_user_func( $cb, array() );
				}
			}
		}

		/*
		add_action( 'customify_wc_product_loop', array( $this, 'product__media' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__category' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__title' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__price' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__rating' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__description' ) );
		add_action( 'customify_wc_product_loop', array( $this, 'product__add_to_cart' ) );
		*/
	}

	function add_catalog_url( $args ){
		$args['section_urls']['wc_catalog_designer'] = get_permalink( wc_get_page_id( 'shop' ) );
		return $args;
	}

	function config( $configs ){
		$section = 'wc_catalog_designer';

		$configs[] = array(
			'name' =>$section,
			'type' => 'section',
			'panel' =>  'woocommerce',
			'label' => __( 'Product Catalog Designer', 'customify' ),
		);

		$configs[] = array(
			'name' => "wc_cd_positions",
			'section' =>  $section,
			'label' => __( 'Item Positions', 'customify' ),
			'type' => 'repeater',
			'title' => __('Body', 'customify'),
			'live_title_field' => 'title',
			'limit' => 4,
			'addable' => false,
			'title_only' => true,
			'selector' =>'.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'default' => array(
				array(
					'_visibility' => '',
					'_key' => 'media',
					'title' => __('Media', 'customify'),
				),
				array(
					'_visibility' => '',
					'_key' => 'title',
					'title' => __('Title', 'customify'),
				),
				array(
					'_key' => 'category',
					'_visibility' => '',
					'title' => __('Category', 'customify'),
				),
				array(
					'_key' => 'price',
					'_visibility' => '',
					'title' => __('Price', 'customify'),
				),
				array(
					'_key' => 'description',
					'_visibility' => '',
					'title' => __('Short Description', 'customify'),
				),
				array(
					'_key' => 'add_to_cart',
					'_visibility' => '',
					'title' => __('Add To Cart', 'customify'),
				),
			),
			'fields' =>  array(
				array(
					'name' => '_key',
					'type' => 'hidden',
				),
				array(
					'name' => 'title',
					'type' => 'hidden',
					'label' => __('Title', 'customify'),
				),
			)
		);

		return $configs;
	}

	function product__media(){
		woocommerce_template_loop_product_link_open();

		woocommerce_show_product_loop_sale_flash();
		woocommerce_template_loop_product_thumbnail();

		woocommerce_template_loop_product_link_close();
	}


	function product__title(){
		woocommerce_template_loop_product_link_open();
		woocommerce_template_loop_product_title();
		woocommerce_template_loop_product_link_close();

	}

	function product__description(){
		echo '<div class="woocommerce-loop-product__desc">';
		the_excerpt();
		echo '</div>';

	}

	function product__price(){
		woocommerce_template_loop_price();
	}

	function product__rating(){
		woocommerce_template_loop_rating();
	}

	function product__category(){
		global $post;

		$tax = 'product_cat';
		$num = 1;

		$terms = get_the_terms( $post, $tax );

		if ( is_wp_error( $terms ) )
			return $terms;

		if ( empty( $terms ) )
			return false;

		$links = array();

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $tax );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . esc_html( $term->name ) . '</a>';
		}

		$categories_list = array_slice( $links, 0, $num );

		echo join( ' ', $categories_list );
	}

	function product__add_to_cart(){
		woocommerce_template_loop_add_to_cart();
	}



}

new Customify_WC_Catalog_Designer();