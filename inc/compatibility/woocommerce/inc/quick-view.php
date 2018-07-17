<?php

class Customify_WC_Quick_View {

	private $backup_post = null;

	function __construct() {

		add_filter( 'customify/customizer/config', array( $this, 'config' ), 155 );
	    add_action( 'wp_loaded' , array( $this, 'init' ) );

	}

	function init(){
		if ( Customify()->get_setting('wc_cd_qv_enable') ) {
			add_action( 'wp_ajax_customify/wc/quick-view', array( $this, 'load_product_quick_view_ajax' ) );
			add_action( 'wp_ajax_nopriv_customify/wc/quick-view', array( $this, 'load_product_quick_view_ajax' ) );
			add_action( 'customify_after_loop_product_media', array( $this, 'button' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'load_single_scripts' ), 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'restore_load_single_scripts' ), 9999 );
		}
    }

	function config( $configs ) {
		$section = 'wc_catalog_designer';

		$configs[] = array(
			'name'    => 'wc_cd_qv_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Product Quick View', 'customify' ),
		);

		$configs[] = array(
			'name'    => 'wc_cd_qv_enable',
			'type'    => 'checkbox',
			'section' => $section,
			'default' => 1,
			'checkbox_label'   => __( 'Enable Product Quick View', 'customify' ),
		);

		$configs[] = array(
			'name'       => 'wc_cd_qv_styling',
			'type'       => 'modal',
			'section'    => $section,
			'css_format' => 'styling',
			'selector'   => '.wc-product__part.wc-product__price',
			'label'      => __( 'Quick View Button Styling', 'customify' ),
			'required'   => array( 'wc_cd_qv_enable', '=', 1 ),
			'fields'     => array(
				'tabs'           => array(
					'default' => __( 'Normal', 'customify' ),
					'hover' => __( 'Hover', 'customify' ),
				),
				'default_fields' => array(
					array(
						'name'       => 'opacity',
						'type'       => 'slider',
						'label'      => __( 'Opacity', 'customify' ),
						'max' => 1,
						'min' => 0,
						'step' => 0.1,
						'css_format' => "opacity: {{value_no_unit}};",
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),
					array(
						'name'            => 'padding',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Padding', 'customify' ),
						'css_format'      => array(
							'top'    => 'padding-top: {{value}};',
							'bottom' => 'padding-bottom: {{value}};',
							'left'   => 'padding-left: {{value}};',
							'right'  => 'padding-right: {{value}};',
						),
						'selector'        => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),
					array(
						'name'            => 'margin',
						'type'            => 'css_ruler',
						'device_settings' => true,
						'label'           => __( 'Margin', 'customify' ),
						'css_format'      => array(
							'top'    => 'margin-top: {{value}};',
							'bottom' => 'margin-bottom: {{value}};',
							'left'   => 'margin-left: {{value}};',
							'right'  => 'margin-right: {{value}};',
						),
						'selector'        => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),
					array(
						'name'  => 'border_heading',
						'type'  => 'heading',
						'label' => __( 'Border', 'customify' ),
					),
					array(
						'name'       => 'border_style',
						'type'       => 'select',
						'class'      => 'clear',
						'label'      => __( 'Border Style', 'customify' ),
						'default'    => '',
						'css_format' => "border-style: {{value}};",
						'choices'    => array(
							''       => __( 'Default', 'customify' ),
							'none'   => __( 'None', 'customify' ),
							'solid'  => __( 'Solid', 'customify' ),
							'dotted' => __( 'Dotted', 'customify' ),
							'dashed' => __( 'Dashed', 'customify' ),
							'double' => __( 'Double', 'customify' ),
							'ridge'  => __( 'Ridge', 'customify' ),
							'inset'  => __( 'Inset', 'customify' ),
							'outset' => __( 'Outset', 'customify' ),
						),
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),

					array(
						'name'       => 'border_width',
						'type'       => 'css_ruler',
						'label'      => __( 'Border Width', 'customify' ),
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
						'css_format' => array(
							'top'    => 'border-top-width: {{value}};',
							'bottom' => 'border-bottom-width: {{value}};',
							'left'   => 'border-left-width: {{value}};',
							'right'  => 'border-right-width: {{value}};',
						),
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __( 'Border Color', 'customify' ),
						'css_format' => "border-color: {{value}};",
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),

					array(
						'name'       => 'border_radius',
						'type'       => 'slider',
						'max'        => 100,
						'label'      => __( 'Border Radius', 'customify' ),
						'css_format' => "border-radius: {{value}};",
						'selector'   => '.woocommerce .wc-product-inner .customify-wc-quick-view',
					),

				),
				'hover_fields' => array(
					array(
						'name'       => 'opacity',
						'type'       => 'slider',
						'label'      => __( 'Opacity', 'customify' ),
						'max' => 1,
						'min' => 0,
						'step' => 0.1,
						'css_format' => "opacity: {{value_no_unit}};",
						'selector'   => '.woocommerce .wc-product-inner:hover .customify-wc-quick-view',
					),
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __( 'Color', 'customify' ),
						'css_format' => "color: {{value}};",
						'selector'   => '.woocommerce .wc-product-inner:hover .customify-wc-quick-view',
					),
					array(
						'name'       => 'bg_color',
						'type'       => 'color',
						'label'      => __( 'Background Color', 'customify' ),
						'css_format' => "background-color: {{value}};",
						'selector'   => '.woocommerce .wc-product-inner:hover .customify-wc-quick-view',
					),
                )
			)
		);

		return $configs;
	}

	function load_single_scripts() {
		global $post;
		$this->backup_post = $post;
		if ( ! is_object( $post ) ) {
			$post = ( object ) array(
				'post_content' => ''
			);
		}
		$post->post_content .= '|__[product_page';
	}

	function restore_load_single_scripts() {
		global $post;
		$post = $this->backup_post;
		wp_enqueue_script( 'wc-add-to-cart-variation' );
	}

	function button() {
		?>
        <a class="customify-wc-quick-view" data-id="<?php echo esc_attr( get_the_ID() ); ?>" href="#"><?php _e( 'Quick View', 'customify' ); ?></a>
		<?php
	}

	/**
	 * Ajax action to load product in quick view
	 *
	 * @access public
	 * @return void
	 * @since 1.0.0
	 * @author Francesco Licandro <francesco.licandro@yithemes.com>
	 */
	public function load_product_quick_view_ajax() {

		if ( ! isset( $_REQUEST['product_id'] ) ) {
			die();
		}

		global $sitepress;

		$product_id = intval( $_REQUEST['product_id'] );

		/**
		 * WPML Support:  Localize Ajax Call
		 */
		$lang = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : '';
		if ( defined( 'ICL_LANGUAGE_CODE' ) && $lang && isset( $sitepress ) ) {
			$sitepress->switch_lang( $lang, true );
		}

		// set the main wp query for the product
		wp( 'p=' . $product_id . '&post_type=product' );
		global $post;

		/**
		 * @see WC_Frontend_Scripts::get_script_data();
		 */
		$params = array(

			'review_rating_required' => get_option( 'woocommerce_review_rating_required' ),
			'flexslider'             => apply_filters(
				'woocommerce_single_product_carousel_options', array(
					'rtl'            => is_rtl(),
					'animation'      => 'slide',
					'smoothHeight'   => true,
					'directionNav'   => false,
					'controlNav'     => 'thumbnails',
					'slideshow'      => false,
					'animationSpeed' => 500,
					'animationLoop'  => false, // Breaks photoswipe pagination if true.
					'allowOneSlide'  => false,
				)
			),
			'zoom_enabled'           => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
			'zoom_options'           => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
			'photoswipe_enabled'     => apply_filters( 'woocommerce_single_product_photoswipe_enabled', get_theme_support( 'wc-product-gallery-lightbox' ) ),
			'photoswipe_options'     => apply_filters(
				'woocommerce_single_product_photoswipe_options', array(
					'shareEl'               => false,
					'closeOnScroll'         => false,
					'history'               => false,
					'hideAnimationDuration' => 0,
					'showAnimationDuration' => 0,
				)
			),
			'flexslider_enabled'     => apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) ),
		);

		$GLOBALS['customify_wc_quick_view'] = 1;
		wc_setup_product_data( $post );
		global $product;

		remove_all_actions( 'woocommerce_after_single_product_summary' );
		remove_all_actions( 'woocommerce_after_single_product' );

		remove_all_actions( 'woocommerce_before_single_product' );
		remove_all_actions( 'woocommerce_after_single_product' );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating' );

		do_action( 'customif/wc/before/quick-view');
		ob_start();
		// load content template
		wc_get_template( 'content-single-product.php' );

		$content = ob_get_clean();

		$variation_params = array(
			'wc_ajax_url'                      => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'customify' ),
			'i18n_make_a_selection_text'       => esc_attr__( 'Please select some product options before adding this product to your cart.', 'customify' ),
			'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'customify' ),
		);

		wp_send_json( array(
			'params'           => $params,
			'variation_params' => $variation_params,
			'type'             => $product->product_type,
			'content'          => $content
		) );

		die();
	}
}

new Customify_WC_Quick_View();