<?php

/**
 * Class Customify_WC_Single_Product
 *
 * Single product settings
 */
class Customify_WC_Single_Product {
	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
		if ( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_product_url' ), 35 );
		}

		add_action( 'wp', array( $this, 'single_product_hooks' ) );
	}

	/**
	 * Add more class if nav showing
	 *
	 * @param array $classes HTML classes.
	 *
	 * @return array
	 */
	function post_class( $classes ) {
		if ( Customify()->get_setting( 'wc_single_product_nav_show' ) ) {
			$classes[] = 'nav-in-title';
		}
		return $classes;
	}

	/**
	 * Get adjacent product
	 *
	 * @param bool   $in_same_term In same term.
	 * @param string $excluded_terms Exlclude terms.
	 * @param bool   $previous Previous.
	 * @param string $taxonomy Taxonomy.
	 *
	 * @return null|string|WP_Post
	 */
	public function get_adjacent_product( $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'product_cat' ) {
		return get_adjacent_post( $in_same_term, $excluded_terms, $previous, $taxonomy );
	}

	/**
	 * Display prev - next button
	 */
	public function product_prev_next() {
		if ( ! Customify()->get_setting( 'wc_single_product_nav_show' ) ) {
			return;
		}
		$prev_post = $this->get_adjacent_product();
		$next_post = $this->get_adjacent_product( false, '', false );
		if ( $prev_post || $next_post ) {
			?>
			<div class="wc-product-nav">
				<?php if ( $prev_post ) { ?>
					<a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" title="<?php the_title_attribute( array( 'post' => $prev_post ) ); ?>" class="prev-link">
						<span class="nav-btn nav-next"><?php echo apply_filters( 'customify_nav_prev_icon', '' ); ?></span>
						<?php if ( has_post_thumbnail( $prev_post ) ) { ?>
							<span class="nav-thumbnail">
								<?php
								echo get_the_post_thumbnail( $prev_post, 'woocommerce_thumbnail' );
								?>
							</span>
						<?php } ?>
					</a>
				<?php } ?>
				<?php if ( $next_post ) { ?>
					<a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" title="<?php the_title_attribute( array( 'post' => $next_post ) ); ?>" class="next-link">
						<span class="nav-btn nav-next">
						<?php echo apply_filters( 'customify_nav_next_icon', '' ); ?>
						</span>
						<?php if ( has_post_thumbnail( $next_post ) ) { ?>
							<span class="nav-thumbnail">
								<?php
								echo get_the_post_thumbnail( $next_post, 'woocommerce_thumbnail' );
								?>
							</span>
						<?php } ?>
					</a>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Hooks for single product
	 */
	function single_product_hooks() {
		if ( ! is_product() ) {
			return;
		}

		add_action( 'wc_after_single_product_title', array( $this, 'product_prev_next' ), 2 );
		add_filter( 'post_class', array( $this, 'post_class' ) );

		if ( Customify()->get_setting( 'wc_single_product_tab_hide_description' ) ) {
			add_filter( 'woocommerce_product_description_heading', '__return_false', 999 );
		}

		if ( Customify()->get_setting( 'wc_single_product_tab_hide_attr_heading' ) ) {
			add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 999 );
		}

		$tab_type = Customify()->get_setting( 'wc_single_product_tab' );

		if ( 'section' == $tab_type || 'toggle' == $tab_type ) {
			add_filter( 'woocommerce_product_description_heading', '__return_false', 999 );
			add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 999 );
		}

	}

	/**
	 * Add url to customize preview when section open
	 *
	 * @param array $args Args to add.
	 *
	 * @return mixed
	 */
	public function add_product_url( $args ) {

		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'orderby'        => 'rand',
			)
		);

		$products = $query->get_posts();
		if ( count( $products ) ) {
			$args['section_urls']['wc_single_product'] = get_permalink( $products[0] );
		}

		return $args;
	}

	/**
	 * Customize config
	 *
	 * @param array $configs Config args.
	 *
	 * @return array
	 */
	public function config( $configs ) {
		$section = 'wc_single_product';

		$configs[] = array(
			'name'     => $section,
			'type'     => 'section',
			'panel'    => 'woocommerce',
			'title'    => __( 'Single Product Page', 'customify' ),
			'priority' => 19,
		);

		$configs[] = array(
			'name'    => 'wc_single_layout_h',
			'type'    => 'heading',
			'section' => $section,
			'label'   => __( 'Layout', 'customify' ),
		);

		/*
		$configs[] = array(
			'name'    => 'wc_single_layout',
			'type'    => 'select',
			'section' => $section,
			'default' => 'default',
			'label'   => __( 'Layout', 'customify' ),
			'choices' => array(
				'default'    => __( 'Default', 'customify' ),
				'top-medium' => __( 'Top Gallery Boxed', 'customify' ),
				'top-full'   => __( 'Top Gallery Full Width', 'customify' ),
				'left-grid'  => __( 'Left Gallery Grid', 'customify' ),
			)
		);
		*/

		$configs[] = array(
			'name'             => 'wc_single_layout',
			'type'             => 'image_select',
			'section'          => $section,
			'title'            => __( 'Layout', 'customify' ),
			'default'          => 'default',

			'disabled_msg'     => __( 'This option is available in Customify Pro plugin only.', 'customify' ),
			'disabled_pro_msg' => __( 'Please activate module Single Product Layouts to use this layout.', 'customify' ),

			'choices'          => array(
				'default'    => array(
					'img'   => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/wc-layout-default.svg',
					'label' => __( 'Default', 'customify' ),
				),
				'top-medium' => array(
					'img'     => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/wc-layout-top-medium.svg',
					'label'   => __( 'Top Gallery Boxed', 'customify' ),
					'disable' => 1,
					'bubble'  => __( 'Pro', 'customify' ),
				),
				'top-full'   => array(
					'img'     => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/wc-layout-top-full.svg',
					'label'   => __( 'Top Gallery Full Width', 'customify' ),
					'disable' => 1,
					'bubble'  => __( 'Pro', 'customify' ),
				),
				'left-grid'  => array(
					'img'     => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/wc-layout-left-grid.svg',
					'label'   => __( 'Left Gallery Grid', 'customify' ),
					'disable' => 1,
					'bubble'  => __( 'Pro', 'customify' ),

				),
			),
		);

		$configs[] = array(
			'name'     => "{$section}_nav_heading",
			'type'     => 'heading',
			'section'  => $section,
			'title'    => __( 'Product Navigation', 'customify' ),
			'priority' => 39,
		);

		$configs[] = array(
			'name'           => "{$section}_nav_show",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Show Product Navigation', 'customify' ),
			'priority'       => 39,
		);

		$configs[] = array(
			'name'     => "{$section}_tab_heading",
			'type'     => 'heading',
			'section'  => $section,
			'title'    => __( 'Product Tabs', 'customify' ),
			'priority' => 40,
		);

		$configs[] = array(
			'name'     => "{$section}_tab",
			'type'     => 'select',
			'default'  => 'horizontal',
			'section'  => $section,
			'label'    => __( 'Tab Layout', 'customify' ),
			'choices'  => array(
				'horizontal' => __( 'Horizontal', 'customify' ),
				'vertical'   => __( 'Vertical', 'customify' ),
				'toggle'     => __( 'Toggle', 'customify' ),
				'sections'   => __( 'Sections', 'customify' ),
			),
			'priority' => 45,
		);

		$configs[] = array(
			'name'           => "{$section}_tab_hide_description",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Hide product description heading', 'customify' ),
			'priority'       => 46,
		);

		$configs[] = array(
			'name'           => "{$section}_tab_hide_attr_heading",
			'type'           => 'checkbox',
			'default'        => 1,
			'section'        => $section,
			'checkbox_label' => __( 'Hide product additional information heading', 'customify' ),
			'priority'       => 47,
		);

		$configs[] = array(
			'name'           => "{$section}_tab_hide_review_heading",
			'type'           => 'checkbox',
			'default'        => 0,
			'section'        => $section,
			'checkbox_label' => __( 'Hide product review heading', 'customify' ),
			'selector'       => '.woocommerce-Reviews-title',
			'css_format'     => 'display: none;',
			'priority'       => 48,
		);

		$configs[] = array(
			'name'     => "{$section}_upsell_heading",
			'type'     => 'heading',
			'section'  => $section,
			'title'    => __( 'Upsell Products', 'customify' ),
			'priority' => 60,
		);

		$configs[] = array(
			'name'     => "{$section}_upsell_number",
			'type'     => 'text',
			'default'  => 3,
			'section'  => $section,
			'label'    => __( 'Number of upsell products', 'customify' ),
			'priority' => 65,
		);

		$configs[] = array(
			'name'            => "{$section}_upsell_columns",
			'type'            => 'text',
			'device_settings' => true,
			'section'         => $section,
			'label'           => __( 'Upsell products per row', 'customify' ),
			'priority'        => 66,
		);

		$configs[] = array(
			'name'     => "{$section}_related_heading",
			'type'     => 'heading',
			'section'  => $section,
			'title'    => __( 'Related Products', 'customify' ),
			'priority' => 70,
		);

		$configs[] = array(
			'name'     => "{$section}_related_number",
			'type'     => 'text',
			'default'  => 3,
			'section'  => $section,
			'label'    => __( 'Number of related products', 'customify' ),
			'priority' => 75,
		);

		$configs[] = array(
			'name'            => "{$section}_related_columns",
			'type'            => 'text',
			'device_settings' => true,
			'section'         => $section,
			'label'           => __( 'Related products per row', 'customify' ),
			'priority'        => 76,
		);

		$configs[] = array(
			'name'           => 'wc_single_layout_breadcrumb',
			'type'           => 'checkbox',
			'section'        => $section,
			'default'        => 1,
			'checkbox_label' => __( 'Show shop breadcrumb', 'customify' ),
		);

		return $configs;
	}
}

new Customify_WC_Single_Product();
