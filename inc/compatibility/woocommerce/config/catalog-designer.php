<?php

class Customify_WC_Catalog_Designer {

	private $configs = array();

	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_catalog_url' ), 35 );
		}

		// Loop.
		add_action( 'customify_wc_product_loop', array( $this, 'render' ) );
	}

	/**
	 * Get callback function for item part
	 *
	 * @param string $item_id ID of builder item.
	 *
	 * @return string|object|boolean
	 */
	function callback( $item_id ) {
		$cb = apply_filters( 'customify/product-designer/part', false, $item_id, $this );
		if ( ! is_callable( $cb ) ) {
			$cb = array( $this, 'product__' . $item_id );
		}
		if ( is_callable( $cb ) ) {
			return $cb;
		}

		return false;
	}

	function render() {

		$items = Customify()->get_setting( 'wc_cd_positions' );

		$this->configs['excerpt_type']   = Customify()->get_setting( 'wc_cd_excerpt_type' );
		$this->configs['excerpt_length'] = Customify()->get_setting( 'wc_cd_excerpt_length' );

		$this->configs = apply_filters( 'customify_wc_catalog_designer/configs', $this->configs );

		$cb = $this->callback( 'media' );
		if ( $cb ) {
			call_user_func( $cb, array( null, $this ) );
		}

		echo '<div class="wc-product-contents">';

		/**
		 * Hook: woocommerce_before_shop_loop_item.
		 */
		do_action( 'woocommerce_before_shop_loop_item' );

		$html = '';

		/**
		 * Allow 3rg party to render items html
		 */
		$html = apply_filters( 'customify/product-designer/render_html', $html, $items, $this );

		if ( ! $html ) {
			foreach ( (array) $items as $item ) {
				$item = wp_parse_args(
					$item,
					array(
						'_key'         => '',
						'_visibility'  => '',
						'show_in_grid' => 1,
						'show_in_list' => 1,
					)
				);
				if ( 'hidden' !== $item['_visibility'] ) {

					$cb = $this->callback( $item['_key'] );

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
						call_user_func( $cb, array( $item, $this ) );
						$item_html = ob_get_contents();
						ob_end_clean();

						if ( trim( $item_html ) != '' ) {
							$html .= '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';
							$html .= $item_html;
							$html .= '</div>';
						}
					}
				}
			}
		}

		echo $html; // WPCS: XSS OK.

		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 */
		do_action( 'woocommerce_after_shop_loop_item' );

		echo '</div>'; // End .wc-product-contents.

	}

	/**
	 * Preview url when section open
	 *
	 * @param array $args The section urls config.
	 *
	 * @return array
	 */
	function add_catalog_url( $args ) {
		$args['section_urls']['wc_catalog_designer'] = get_permalink( wc_get_page_id( 'shop' ) );

		return $args;
	}

	/**
	 * Get Default builder items for product designer
	 *
	 * @since 2.0.5
	 *
	 * @return array
	 */
	function get_default_items() {
		$items = array(
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
		);

		return apply_filters( 'customify/product-designer/body-items', $items );
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

		// Catalog header.
		$configs[] = array(
			'name'            => 'wc_cd_show_catalog_header',
			'type'            => 'checkbox',
			'section'         => $section,
			'default'         => 1,
			'priority'        => 10,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'label'           => __( 'Show Catalog Filtering Bar', 'customify' ),
		);

		// Show view mod.
		$configs[] = array(
			'name'            => 'wc_cd_show_view_mod',
			'type'            => 'checkbox',
			'section'         => $section,
			'default'         => 1,
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'checkbox_label'  => __( 'Show Grid/List View Buttons', 'customify' ),
			'priority'        => 11,
		);

		$configs[] = array(
			'name'            => 'wc_cd_default_view',
			'type'            => 'select',
			'section'         => $section,
			'default'         => 'grid',
			'priority'        => 12,
			'choices'         => array(
				'grid' => __( 'Grid', 'customify' ),
				'list' => __( 'List', 'customify' ),
			),
			'selector'        => '.wc-product-listing',
			'render_callback' => 'woocommerce_content',
			'label'           => __( 'Default View Mod', 'customify' ),
		);

		$configs[] = array(
			'name'             => 'wc_cd_positions',
			'section'          => $section,
			'label'            => __( 'Outside Media Items & Positions', 'customify' ),
			'type'             => 'repeater',
			'live_title_field' => 'title',
			'addable'          => false,
			'priority'         => 15,
			'selector'         => '.wc-product-listing',
			'render_callback'  => 'woocommerce_content',
			'default'          => $this->get_default_items(),
			'fields'           => apply_filters(
				'customify/product-designer/body-field-config',
				array(
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
			),
		);

		$configs[] = array(
			'name'     => 'wc_cd_excerpt_type',
			'type'     => 'select',
			'section'  => $section,
			'priority' => 17,
			'title'    => __( 'List view excerpt type', 'customify' ),
			'choices'  => array(
				'excerpt' => __( 'Product short description', 'customify' ),
				'content' => __( 'Full content', 'customify' ),
				'more'    => __( 'Strip by more tag', 'customify' ),
				'custom'  => __( 'Custom', 'customify' ),
			),
		);

		$configs[] = array(
			'name'     => 'wc_cd_excerpt_length',
			'type'     => 'text',
			'section'  => $section,
			'priority' => 17,
			'title'    => __( 'Custom list view excerpt length', 'customify' ),
			'required' => array( 'wc_cd_excerpt_type', '=', 'custom' ),
		);

		// Product Media.
		$configs[] = array(
			'name'     => 'wc_cd_memdia_h',
			'type'     => 'heading',
			'section'  => $section,
			'priority' => 25,
			'label'    => __( 'Product Media & Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_list_media_width',
			'type'            => 'slider',
			'section'         => $section,
			'unit'            => '%',
			'max'             => 100,
			'device_settings' => true,
			'priority'        => 26,
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
			'priority'        => 27,
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
			'priority'        => 28,
			'selector'        => '.wc-grid-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'Grid View - Content Alignment', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_item_list_align',
			'type'            => 'text_align_no_justify',
			'section'         => $section,
			'device_settings' => true,
			'priority'        => 28,
			'selector'        => '.wc-list-view .wc-product-contents',
			'css_format'      => 'text-align: {{value}};',
			'title'           => __( 'List View - Content Alignment', 'customify' ),
		);

		// Product Sale Bubble.
		$configs[] = array(
			'name'     => 'wc_cd_sale_bubble_h',
			'type'     => 'heading',
			'section'  => $section,
			'priority' => 30,
			'label'    => __( 'Product Onsale Bubble', 'customify' ),
		);

		$configs[] = array(
			'name'            => 'wc_cd_sale_bubble_type',
			'type'            => 'select',
			'default'         => 'text',
			'priority'        => 31,
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

		$configs[] = array(
			'name'        => 'wc_cd_sale_bubble_styling',
			'type'        => 'styling',
			'section'     => $section,
			'priority'    => 32,
			'title'       => __( 'Styling', 'customify' ),
			'description' => __( 'Advanced styling for onsale button', 'customify' ),
			'selector'    => array(
				'normal' => '.woocommerce span.onsale',
			),
			'css_format'  => 'styling',
			'default'     => array(),
			'fields'      => array(
				'normal_fields' => array(
					'link_color'    => false, // disable for special field.
					'margin'        => false,
					'bg_image'      => false,
					'bg_cover'      => false,
					'bg_position'   => false,
					'bg_repeat'     => false,
					'bg_attachment' => false,
				),
				'hover_fields'  => false,
			),
		);

		return $configs;
	}

	function product__media() {
		echo '<div class="wc-product-media">';
		/**
		 * Hook: customify/wc-product/before-media
		 * hooked: woocommerce_template_loop_product_link_open - 10
		 */
		do_action( 'customify/wc-product/before-media' );
		woocommerce_show_product_loop_sale_flash();
		woocommerce_template_loop_product_thumbnail();
		customify_wc_secondary_product_thumbnail();
		do_action( 'customify_after_loop_product_media' );
		/**
		 * Hook: customify/wc-product/after-media
		 * hooked: woocommerce_template_loop_product_link_close - 10
		 */
		do_action( 'customify/wc-product/after-media' );
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
		 * @see    woocommerce_shop_loop_item_title.
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

	/**
	 * Trim the excerpt with custom length.
	 *
	 * @see wp_trim_excerpt
	 *
	 * @param string  $text           Text to trim.
	 * @param integer $excerpt_length Number word to trim.
	 *
	 * @return mixed|string
	 */
	function trim_excerpt( $text, $excerpt_length = null ) {
		$text = strip_shortcodes( $text );
		/** This filter is documented in wp-includes/post-template.php */
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		if ( ! $excerpt_length ) {
			/**
			 * Filters the number of words in an excerpt.
			 *
			 * @since 2.7.0
			 *
			 * @param int $number The number of words. Default 55.
			 */
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
		}
		$more_text    = ' &hellip;';
		$excerpt_more = apply_filters( 'excerpt_more', $more_text );

		$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );

		return $text;
	}

	function product__description() {
		echo '<div class="woocommerce-loop-product__desc">';

		if ( 'excerpt' == $this->configs['excerpt_type'] ) {
			the_excerpt();
		} elseif ( 'more' == $this->configs['excerpt_type'] ) {
			the_content( '', true );
		} elseif ( 'content' == $this->configs['excerpt_type'] ) {
			the_content( '', false );
		} else {
			$text = '';
			global $post;
			if ( $post ) {
				if ( $post->post_excerpt ) {
					$text = $post->post_excerpt;
				} else {
					$text = $post->post_content;
				}
			}
			$excerpt = $this->trim_excerpt( $text, $this->configs['excerpt_length'] );
			if ( $excerpt ) {
				// WPCS: XSS OK.
				echo apply_filters( 'the_excerpt', $excerpt );
			} else {
				the_excerpt();
			}
		}

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
