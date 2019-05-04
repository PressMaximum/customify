<?php

class Customify_WC {
	static $_instance;

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function is_active() {
		return Customify()->is_woocommerce_active();
	}

	function __construct() {
		if ( $this->is_active() ) {
			/**
			 * Filter shop layout
			 *
			 * @see Customify_WC::shop_layout
			 */
			add_filter( 'customify_get_layout', array( $this, 'shop_layout' ) );
			/**
			 * Filter special meta values for shop pages.
			 *
			 * @see Customify_WC::get_post_metadata
			 */
			add_filter( 'get_post_metadata', array( $this, 'get_post_metadata' ), 999, 4 );

			add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
			add_filter( 'customify/customizer/config', array( $this, 'customize_shop_sidebars' ) );
			add_filter( 'customify/sidebar-id', array( $this, 'shop_sidebar_id' ), 15, 2 );

			add_filter( 'customify_is_header_display', array( $this, 'show_shop_header' ), 15 );
			add_filter( 'customify_is_footer_display', array( $this, 'show_shop_footer' ), 15 );
			add_filter( 'customify_site_content_class', array( $this, 'shop_content_layout' ), 15 );
			add_filter( 'customify_builder_row_display_get_post_id', array( $this, 'builder_row_get_id' ), 15 );

			add_filter( 'customify/titlebar/args', array( $this, 'titlebar_args' ) );
			add_filter( 'customify/titlebar/config', array( $this, 'titlebar_config' ), 15, 2 );
			add_filter( 'customify/titlebar/is-showing', array( $this, 'titlebar_is_showing' ), 15 );
			add_filter( 'customify/page-header/get-settings', array( $this, 'get_page_header_settings' ) );

			add_filter( 'customify/theme/js', array( $this, 'add_js' ) );

			/**
			 * Woocommerce_sidebar hook.
			 *
			 * @hooked woocommerce_get_sidebar - 10
			 */
			add_action( 'wp', array( $this, 'wp' ) );

			// Custom styling.
			add_filter( 'customify/styling/primary-color', array( $this, 'styling_primary' ) );
			add_filter( 'customify/styling/secondary-color', array( $this, 'styling_secondary' ) );
			add_filter( 'customify/styling/link-color', array( $this, 'styling_linkcolor' ) );
			add_filter( 'customify/styling/color-border', array( $this, 'styling_border_color' ) );
			add_filter( 'customify/styling/color-meta', array( $this, 'styling_meta_color' ) );

			// Shopping Cart.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/header/cart.php';
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
			add_filter( 'Customify_JS', array( $this, 'Customify_JS' ) );

			add_filter( 'woocommerce_get_script_data', array( $this, 'woocommerce_get_script_data' ), 15, 2 );

			// Load theme style.
			add_filter( 'woocommerce_enqueue_styles', array( $this, 'custom_styles' ) );

			// Add body class.
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'post_class', array( $this, 'post_class' ), 190, 3 );
			add_filter( 'product_cat_class', array( $this, 'post_cat_class' ), 15 );

			// Change number repleate product.
			add_action( 'customify_wc_loop_start', array( $this, 'loop_start' ) );
			add_filter( 'woocommerce_output_related_products_args', array( $this, 'related_products_args' ) );
			add_filter( 'woocommerce_upsell_display_args', array( $this, 'updsell_products_args' ) );
			add_action( 'woocommerce_before_single_product', array( $this, 'maybe_disable_upsell' ), 1 );
			add_action( 'woocommerce_before_single_product', array( $this, 'maybe_disable_related' ), 1 );

			// Catalog config.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/catalog.php';
			// Product catalog designer.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/catalog-designer.php';
			// Single product config.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/single-product.php';

			// Single product config.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/cart.php';

			// Single colors config.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/config/colors.php';
			// Template Hooks.
			require_once get_template_directory() . '/inc/compatibility/woocommerce/inc/template-hooks.php';

			// Overwrite Categories Walker.
			add_filter( 'woocommerce_product_categories_widget_args', array( $this, 'customify_wc_cat_list_args' ) );

			/**
			 * Move sale flash to new hook wc_product_images_after
			 *
			 * @since 0.2.3
			 */
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash' );
			add_action( 'wc_product_images_after', 'woocommerce_show_product_sale_flash' );

			/**
			 * Move WC Product images to new hook woocommerce_single_product_media
			 * This hook created from theme.
			 */
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			add_action( 'woocommerce_single_product_media', 'woocommerce_show_product_images', 20 );

			// New breadcrumb position.
			add_filter( 'woocommerce_breadcrumb_defaults', array( $this, 'woocommerce_breadcrumb_args' ) );

		}
	}

	function woocommerce_breadcrumb_args( $args ) {
		$args['delimiter']   = '';
		$args['wrap_before'] = '<nav class="woocommerce-breadcrumb text-uppercase text-xsmall link-meta">';
		$args['wrap_after']  = '</nav>';

		return $args;
	}


	/**
	 * Overwrite Categories Walker
	 *
	 * @see WC_Product_Cat_List_Walker
	 *
	 * @param array $args List args.
	 *
	 * @return mixed
	 */
	function customify_wc_cat_list_args( $args ) {
		require_once get_template_directory() . '/inc/compatibility/woocommerce/inc/class-wc-product-cat-list-walker.php';
		$args['walker'] = new Customify_WC_Product_Cat_List_Walker();

		return $args;
	}

	function woocommerce_get_script_data( $data, $handle ) {
		if ( 'woocommerce' == $handle ) {
			$data['qty_pm'] = apply_filters( 'customify_qty_add_plus_minus', 1 );
		}

		return $data;
	}

	/**
	 * Custom number layout
	 */
	function loop_start() {

		/**
		 * @see wc_set_loop_prop
		 */
		$name = wc_get_loop_prop( 'name' );

		wc_set_loop_prop( 'media_secondary', Customify()->get_setting( 'wc_cd_media_secondary' ) );

		if ( ! $name ) { // Main loop.
			wc_set_loop_prop( 'columns', get_option( 'woocommerce_catalog_columns' ) );
			wc_set_loop_prop( 'tablet_columns', get_theme_mod( 'woocommerce_catalog_tablet_columns' ) );
			wc_set_loop_prop( 'mobile_columns', Customify()->get_setting( 'woocommerce_catalog_mobile_columns' ) );

		} elseif ( 'related' == $name || 'up-sells' == $name || 'cross-sells' == $name ) {

			if ( 'up-sells' == $name ) {
				$columns = Customify()->get_setting( 'wc_single_product_upsell_columns', 'all' );
			} else {
				$columns = Customify()->get_setting( 'wc_single_product_related_columns', 'all' );
			}

			$columns = wp_parse_args(
				$columns,
				array(
					'desktop' => 3,
					'tablet'  => 3,
					'mobile'  => 1,
				)
			);

			if ( ! $columns ) {
				$columns['desktop'] = 3;
			}

			wc_set_loop_prop( 'columns', $columns['desktop'] );
			wc_set_loop_prop( 'tablet_columns', $columns['tablet'] );
			wc_set_loop_prop( 'mobile_columns', $columns['mobile'] );
		}

	}


	/**
	 * Custom number related products
	 *
	 * @param array $args Query args.
	 *
	 * @return mixed
	 */
	function related_products_args( $args ) {
		$args['posts_per_page'] = Customify()->get_setting( 'wc_single_product_related_number' );

		return $args;
	}

	function maybe_disable_related() {
		$n = Customify()->get_setting( 'wc_single_product_related_number' );
		if ( 0 == $n ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}
	}

	function maybe_disable_upsell() {
		$n = Customify()->get_setting( 'wc_single_product_upsell_number' );
		if ( 0 == $n ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		}
	}

	/**
	 * Custom number related products
	 *
	 * @param array $args WP Query args.
	 *
	 * @return mixed
	 */
	function updsell_products_args( $args ) {
		$args['posts_per_page'] = Customify()->get_setting( 'wc_single_product_upsell_number' );

		return $args;
	}

	function related_products_columns() {
		return 3;
	}

	function post_cat_class( $classes ) {
		$classes[] = 'customify-col';

		return $classes;
	}

	function post_class( $classes, $class, $post_id ) {

		global $post;
		if ( ! $post_id ) {
			if ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
				$post_id = $post->ID;
			}
		}

		if ( ! $post_id || get_post_type( $post ) !== 'product' ) {
			return $classes;
		}

		global $product;

		if ( is_object( $product ) ) { // Do not add class if is single product.

			if ( isset( $GLOBALS['woocommerce_loop'] ) && ! empty( $GLOBALS['woocommerce_loop'] ) ) {
				if ( is_product() ) {
					if ( $GLOBALS['woocommerce_loop']['name'] ) {
						$classes[] = 'customify-col';
					}
				} else {
					$classes[] = 'customify-col';
				}
			}
		}

		if ( is_object( $product ) ) {
			$setting = wc_get_loop_prop( 'media_secondary' );
			if ( 'none' != $setting ) {
				$image_ids = $product->get_gallery_image_ids();
				if ( $image_ids ) {
					$classes[] = 'product-has-gallery';
				}
			}
		}

		return $classes;
	}

	function body_class( $classes ) {
		$classes['woocommerce'] = 'woocommerce';
		if ( version_compare( WC()->version, '3.6.0' ) >= 0 ) {
			$classes[] = 'later-wc-version';
		}

		return $classes;
	}

	/**
	 * Load load theme styling instead.
	 *
	 * @param array $enqueue_styles List enqueue styles.
	 *
	 * @return mixed
	 */
	function custom_styles( $enqueue_styles ) {
		$suffix                                        = Customify()->get_asset_suffix();
		$enqueue_styles['woocommerce-general']['src']  = esc_url( get_template_directory_uri() ) . '/assets/css/compatibility/woocommerce' . $suffix . '.css';
		$enqueue_styles['woocommerce-general']['deps'] = 'customify-style';

		if ( isset( $enqueue_styles['woocommerce-layout'] ) ) {
			unset( $enqueue_styles['woocommerce-layout'] ); // Remove the layout.
		}

		if ( isset( $enqueue_styles['woocommerce-smallscreen'] ) ) {
			$enqueue_styles['woocommerce-smallscreen']['deps'] = '';
			$enqueue_styles['woocommerce-smallscreen']['src']  = esc_url( get_template_directory_uri() ) . '/assets/css/compatibility/woocommerce-smallscreen' . $suffix . '.css';
			$b                                                 = $enqueue_styles['woocommerce-smallscreen'];
			unset( $enqueue_styles['woocommerce-smallscreen'] );
			$enqueue_styles['woocommerce-smallscreen'] = $b;
		}

		return $enqueue_styles;
	}

	/**
	 * Add more settings to Customify JS
	 *
	 * @param array $args JS settings.
	 *
	 * @return mixed
	 */
	function Customify_JS( $args ) {
		$args['wc_open_cart'] = false;
		if ( isset( $_REQUEST['add-to-cart'] ) && ! empty( $_REQUEST['add-to-cart'] ) ) {
			$args['wc_open_cart'] = true;
		}

		return $args;
	}

	/**
	 * Add more args for cart
	 *
	 * @see WC_AJAX::get_refreshed_fragments();
	 *
	 * @param array $cart_fragments
	 *
	 * @return array
	 */
	function cart_fragments( $cart_fragments = array() ) {
		$sub_total = WC()->cart->get_cart_subtotal();

		$cart_fragments['.customify-wc-sub-total'] = '<span class="customify-wc-sub-total">' . $sub_total . '</span>';
		$quantities                                = WC()->cart->get_cart_item_quantities();

		$qty   = array_sum( $quantities );
		$class = 'customify-wc-total-qty';
		if ( $qty <= 0 ) {
			$class .= ' hide-qty';
		}

		$cart_fragments['.customify-wc-total-qty'] = '<span class="' . $class . '">' . $qty . '</span>';

		return $cart_fragments;
	}

	function styling_primary( $selector ) {
		$selector .= ' 
        
        .wc-svg-btn.active,
        .woocommerce-tabs.wc-tabs-horizontal ul.tabs li.active,
        #review_form {
            border-color: {{value}};
        }
        
        .wc-svg-btn.active,
        .wc-single-tabs ul.tabs li.active a,
        .wc-single-tabs .tab-section.active .tab-section-heading a {
            color: {{value}};
        }';

		return $selector;
	}

	function styling_secondary( $selector ) {
		$selector .= ' 
        
        .add_to_cart_button
        {
            background-color: {{value}};
        }';

		return $selector;
	}

	function styling_linkcolor( $selector ) {
		$selector .= '
		 
		.woocommerce-account .woocommerce-MyAccount-navigation ul li.is-active a,
        .woocommerce-account .woocommerce-MyAccount-navigation ul li a:hover {
            color: {{value}};
        }';

		return $selector;
	}

	function styling_border_color( $selector ) {
		$selector .= '
		.widget_price_filter .price_slider_wrapper .ui-widget-content {
		    background-color: {{value}};
		}
		.product_list_widget li,
		#reviews #comments ol.commentlist li .comment-text,
		.woocommerce-tabs.wc-tabs-vertical .wc-tabs li,
		.product_meta > span,
		.woocommerce-tabs.wc-tabs-horizontal ul.tabs,
		.woocommerce-tabs.wc-tabs-vertical .wc-tabs li:first-child {
            border-color: {{value}};
        }';

		return $selector;
	}

	function styling_meta_color( $selector ) {
		$selector .= '
		.widget_price_filter .ui-slider .ui-slider-handle {
		    border-color: {{value}};
		}
		.wc-product-inner .wc-product__category a {
		    color: {{value}};
		}
		.widget_price_filter .ui-slider .ui-slider-range,
		.widget_price_filter .price_slider_amount .button {
            background-color: {{value}};
        }';

		return $selector;
	}

	function wp() {
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
	}

	function add_css( $css_files ) {
		$suffix                          = Customify()->get_asset_suffix();
		$css_files['plugin-woocommerce'] = esc_url( get_template_directory_uri() ) . '/assets/css/compatibility/woocommerce' . $suffix . '.css';

		return $css_files;
	}

	function add_js( $js_files ) {
		$suffix                         = Customify()->get_asset_suffix();
		$js_files['plugin-woocommerce'] = array(
			'url' => esc_url( get_template_directory_uri() ) . '/assets/js/compatibility/woocommerce' . $suffix . '.js',
			'deps' => array( 'jquery' ),
		);

		return $js_files;
	}

	function get_shop_page_meta( $meta_key ) {
		return get_post_meta( wc_get_page_id( 'shop' ), $meta_key, true );
	}

	function is_shop_pages() {
		return ( is_shop() || is_product_category() || is_product_tag() || is_product() );
	}

	function builder_row_get_id( $id ) {
		if ( $this->is_shop_pages() ) {
			$id = wc_get_page_id( 'shop' );
		}

		return $id;
	}

	function shop_content_layout( $classes = array() ) {
		if ( $this->is_shop_pages() ) {
			$page_layout = $this->get_shop_page_meta( '_customify_content_layout' );
			if ( $page_layout ) {
				$classes['content_layout'] = 'content-' . sanitize_text_field( $page_layout );
			}
		}

		return $classes;
	}

	function show_shop_header( $show = true ) {
		if ( $this->is_shop_pages() ) {
			$disable = $this->get_shop_page_meta( '_customify_disable_header' );
			if ( $disable ) {
				$show = false;
			}
		}

		return $show;
	}

	function show_shop_footer( $show = true ) {
		if ( $this->is_shop_pages() ) {
			$rows    = array( 'main', 'bottom' );
			$count   = 0;
			$shop_id = wc_get_page_id( 'shop' );
			foreach ( $rows as $row_id ) {
				if ( ! customify_is_builder_row_display( 'footer', $row_id, $shop_id ) ) {
					$count ++;
				}
			}
			if ( $count >= count( $rows ) ) {
				$show = false;
			}
		}

		return $show;
	}

	function show_shop_title( $show = true ) {
		if ( $this->is_shop_pages() ) {
			$disable = $this->get_shop_page_meta( '_customify_disable_page_title' );
			if ( $disable ) {
				$show = false;
			}
		}

		if ( $this->titlebar_is_showing() ) {
			$show = false;
		}

		return apply_filters( 'customify_is_shop_title_display', $show );
	}

	/**
	 * Filter header settings pargs
	 *
	 * @TODO display category thumbnail as header cover if set.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	function get_page_header_settings( $args ) {
		if ( is_product_taxonomy() ) {
			global $wp_query;
			$cat          = $wp_query->get_queried_object();
			$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
			$image        = Customify()->get_media( $thumbnail_id, 'full' );
			if ( $image ) {
				$args['image'] = $image;
			}
		}

		return $args;
	}

	function titlebar_is_showing( $show = true ) {

		if ( is_shop() ) {
			// Do not show if page settings disable page title.
			if ( Customify_Breadcrumb::get_instance()->support_plugins_active() && ! Customify()->get_setting( 'titlebar_display_product' ) ) {
				$show = false;
			} else {
				$show = true;
			}
			if ( Customify()->is_using_post() ) {
				$breadcrumb_display = get_post_meta( wc_get_page_id( 'shop' ), '_customify_breadcrumb_display', true );
				if ( 'hide' == $breadcrumb_display ) {
					$show = false;
				} elseif ( 'show' == $breadcrumb_display ) {
					$show = true;
				}
			}
		} elseif ( is_product_taxonomy() ) {
			if ( Customify()->get_setting( 'titlebar_display_product_tax' ) ) {
				$show = true;
			} else {
				$show = false;
			}
		} elseif ( is_product() ) {
			if ( Customify()->get_setting( 'titlebar_display_product' ) ) {
				$show = true;
			} else {
				$show = false;
			}
		}

		return $show;
	}

	function titlebar_config( $config, $titlebar ) {
		$section = 'titlebar';

		$config[] = array(
			'name'           => "{$section}_display_product_tax",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Display on product taxonomies (categories/tags,..)', 'customify' ),
		);

		$config[] = array(
			'name'           => "{$section}_display_product",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Display on single product', 'customify' ),
		);

		return $config;
	}

	function titlebar_args( $args ) {
		if ( is_product_taxonomy() ) {
			$t             = get_queried_object();
			$args['title'] = $t->name;
		} elseif ( is_singular( 'product' ) ) {
			$args['title'] = get_the_title( wc_get_page_id( 'shop' ) );
			$args['tag']   = 'h2';
		}

		return $args;
	}

	function shop_sidebar_id( $id, $sidebar_type = null ) {
		if ( $this->is_shop_pages() ) {
			switch ( $sidebar_type ) {
				case 'secondary':
					return 'shop-sidebar-2';
				default:
					return 'shop-sidebar-1';
			}
		}

		return $id;
	}

	function customize_shop_sidebars( $configs = array() ) {
		return $configs;
	}

	function register_sidebars() {
		register_sidebar(
			array(
				'name'          => esc_html__( 'WooCommerce Primary Sidebar', 'customify' ),
				'id'            => 'shop-sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'customify' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
		register_sidebar(
			array(
				'name'          => esc_html__( 'WooCommerce Secondary Sidebar', 'customify' ),
				'id'            => 'shop-sidebar-2',
				'description'   => esc_html__( 'Add widgets here.', 'customify' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			)
		);
	}


	/**
	 * Filter meta key for shop pages
	 *
	 * @param string $value
	 * @param string $object_id
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return mixed
	 */
	function get_post_metadata( $value, $object_id, $meta_key, $single ) {
		$meta_keys = array(
			'_customify_page_header_display' => '',
			'_customify_breadcrumb_display'  => '',
		);

		if ( ! isset( $meta_keys[ $meta_key ] ) ) {
			return $value;
		}

		if ( wc_get_page_id( 'cart' ) == $object_id || wc_get_page_id( 'checkout' ) == $object_id ) {

			$meta_type = 'post';

			$meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

			if ( ! $meta_cache ) {
				$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
				$meta_cache = $meta_cache[ $object_id ];
			}

			if ( ! $meta_key ) {
				return $value;
			}

			if ( isset( $meta_cache[ $meta_key ] ) ) {
				if ( $single ) {
					$value = maybe_unserialize( $meta_cache[ $meta_key ][0] );
				} else {
					$value = array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
				}
			}

			if ( ! is_array( $value ) ) {
				$value = array( $value );
			}

			switch ( $meta_key ) {
				case '_customify_page_header_display':
					if ( empty( $value ) || 'default' == $value[0] || 'normal' == $value[0] || ! $value[0] ) {
						$value[0] = 'normal';
					}
					break;
				case '_customify_breadcrumb_display':
					if ( empty( $value ) || 'default' == $value[0] || ! $value[0] ) {
						$value[0] = 'hide';
					}
					break;

			}
		}

		return $value;
	}

	/**
	 * Special shop layout
	 *
	 * @param bool $layout
	 *
	 * @return string
	 */
	function shop_layout( $layout = false ) {
		if ( $this->is_shop_pages() ) {
			$default     = Customify()->get_setting( 'sidebar_layout' );
			$page        = Customify()->get_setting( 'page_sidebar_layout' );
			$page_id     = wc_get_page_id( 'shop' );
			$page_custom = get_post_meta( $page_id, '_customify_sidebar', true );
			if ( $page_custom ) {
				$layout = $page_custom;
			} elseif ( $page ) {
				$layout = $page;
			} else {
				$layout = $default;
			}
		}

		if ( is_product() ) {
			$product_sidebar = get_post_meta( get_the_ID(), '_customify_sidebar', true );
			if ( ! $product_sidebar ) {
				$product_custom = Customify()->get_setting( 'product_sidebar_layout' );
				if ( $product_custom && 'default' != $product_custom ) {
					$layout = $product_custom;
				}
			} else {
				$layout = $product_sidebar;
			}
		}

		return $layout;
	}
}

function Customify_WC() {
	return Customify_WC::get_instance();
}

if ( Customify()->is_woocommerce_active() ) {
	Customify_WC();
}

/**
 * Get default view for product catalog
 *
 * @return string
 */
function customify_get_default_catalog_view_mod() {
	$name    = wc_get_loop_prop( 'name' );
	$default = Customify()->get_setting( 'wc_cd_default_view' );
	if ( $name ) {
		return apply_filters( 'customify_get_default_catalog_view_mod', 'grid' );
	}

	$use_cookies = true;
	if ( is_customize_preview() ) {
		$use_cookies = false;
	}

	if ( ! Customify()->get_setting( 'wc_cd_show_view_mod' ) ) {
		$use_cookies = false;
	}

	if ( $use_cookies ) { // Do not use cookie in customize.
		$cookie_mod = ( isset( $_COOKIE['customify_wc_pl_view_mod'] ) && $_COOKIE['customify_wc_pl_view_mod'] ) ? sanitize_text_field( $_COOKIE['customify_wc_pl_view_mod'] ) : false; // WPCS: sanitization ok.
		if ( $cookie_mod ) {
			if ( 'grid' == $cookie_mod || 'list' == $cookie_mod ) {
				$default = $cookie_mod;
			}
		}
	}

	if ( ! $default ) {
		$default = 'grid';
	}

	return apply_filters( 'customify_get_default_catalog_view_mod', $default );
}


if ( ! function_exists( 'woocommerce_template_loop_product_link_open' ) ) {
	/**
	 * Insert the opening anchor tag for products in the loop.
	 *
	 * @param string $classs
	 */
	function woocommerce_template_loop_product_link_open( $classs = '' ) {
		global $product;

		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );

		echo '<a href="' . esc_url( $link ) . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
	}
}

if ( ! function_exists( 'woocommerce_shop_loop_item_title' ) ) {
	/**
	 * Show the product title in the product loop. By default this is an H2.
	 * Overridden function `woocommerce_shop_loop_item_title`
	 */
	function woocommerce_template_loop_product_title() {
		echo '<h2 class="woocommerce-loop-product__title">';
		woocommerce_template_loop_product_link_open();
		echo get_the_title();
		woocommerce_template_loop_product_link_close();
		echo '</h2>';
	}
}

/**
 * Template pages
 */

if ( ! function_exists( 'woocommerce_content' ) ) {
	/**
	 * Output WooCommerce content.
	 *
	 * This function is only used in the optional 'woocommerce.php' template.
	 * which people can add to their themes to add basic woocommerce support.
	 * without hooks or modifying core templates.
	 */
	function woocommerce_content() {
		if ( is_singular( 'product' ) ) {
			while ( have_posts() ) :
				the_post();
				wc_get_template_part( 'content', 'single-product' );
			endwhile;
		} else {
			$view = customify_get_default_catalog_view_mod();
			?>
			<div class="woocommerce-listing wc-product-listing <?php echo esc_attr( 'wc-' . $view . '-view' ); ?>">
				<?php
				if ( Customify_WC()->show_shop_title() ) {
					if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
						<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
						<?php
					endif;
					do_action( 'woocommerce_archive_description' );
				}
				if ( have_posts() ) {
					do_action( 'woocommerce_before_shop_loop' );
					woocommerce_product_loop_start();
					while ( have_posts() ) :
						the_post();
						wc_get_template_part( 'content', 'product' );
						endwhile; // end of the loop.
					woocommerce_product_loop_end();
					do_action( 'woocommerce_after_shop_loop' );
				} elseif ( ! woocommerce_product_subcategories(
					array(
						'before' => woocommerce_product_loop_start( false ),
						'after'  => woocommerce_product_loop_end( false ),
					)
				) ) {
					do_action( 'woocommerce_no_products_found' );
				}
				?>
			</div>
			<?php
		}
	}
}
