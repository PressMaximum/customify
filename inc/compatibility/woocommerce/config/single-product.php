<?php
class Customify_WC_Single_Product {
	function __construct() {
		add_filter('customify/customizer/config', array($this, 'config'), 100 );
		if( is_admin() || is_customize_preview() ) {
			add_filter( 'Customify_Control_Args', array( $this, 'add_product_url' ), 35 );
		}

		add_action( 'wp', array( $this, 'single_product_hooks' ) );
	}

	function single_product_hooks(){
		if ( ! is_product() ) {
			return ;
		}
		if ( ! Customify()->get_setting('wc_single_product_tab_show_description') ) {
			add_filter( 'woocommerce_product_description_heading', '__return_false', 999 );
		}

		if ( ! Customify()->get_setting('_tab_attr_heading') ) {
			add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 999 );
		}

	}

	function add_product_url( $args ){

		$query = new WP_Query( array(
			'post_type' => 'product',
			'posts_per_page' => 1,
			'orderby' => 'rand',
		) );

		$products = $query->get_posts();
		if ( count( $products ) ) {
		    $args['section_urls']['wc_single_product'] = get_permalink( $products[0] );
        }

        return $args;
	}

	function config( $configs ){
		$section = 'wc_single_product';

		$configs[] = array(
			'name' => $section,
			'type' => 'section',
			'panel' => 'woocommerce',
			'title' => __( 'Single Product', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab",
			'type' => 'select',
			'default' => 'horizontal',
			'section' =>  $section,
			'choices' => array(
				'horizontal' => __( 'Horizontal', 'customify' ),
				'vertical' => __( 'Vertical', 'customify' ),
			),
			'label' => __( 'Tabs Display', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab_show_description",
			'type' => 'checkbox',
			'default' => 1,
			'section' =>  $section,
			'checkbox_label' => __( 'Show product description heading', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab_attr_heading",
			'type' => 'checkbox',
			'default' => 1,
			'section' =>  $section,
			'checkbox_label' => __( 'Show product additional information heading', 'customify' ),
		);

		$configs[] = array(
			'name' => "{$section}_tab_review_heading",
			'type' => 'checkbox',
			'default' => 1,
			'section' =>  $section,
			'checkbox_label' => __( 'Show product review heading', 'customify' ),
			'selector' => '.woocommerce-Reviews-title',
			'css_format' => 'display: block;',
		);

		$configs[] = array(
			'name' => "{$section}_tabs_styling",
			'type' => 'modal',
			'default' => 1,
			'section' =>  $section,
			'label' => __( 'Tabs Styling', 'customify' ),
			'selector' => '.wc-single-tabs',
			'css_format' => 'styling',
			'fields' => array(
				'tabs' => array(
					'default' => __( 'Normal', 'customify' ),
					'active' => __( 'Active', 'customify' ),
				),
				'default_fields' => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __('Color', 'customify'),
						'selector'    => "format",
						'css_format' => '.woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs li { color: {{value}}; }',
					),
					array(
						'name'  => 'border_heading',
						'type'  => 'heading',
						'label' => __('Border', 'customify'),
					),
					array(
						'name'       => 'border_style',
						'type'       => 'select',
						'class'      => 'clear',
						'label'      => __('Border Style', 'customify'),
						'default'    => '',
						'selector'    => "format",
						'choices'    => array(
							''       => __('Default', 'customify'),
							'none'   => __('None', 'customify'),
							'solid'  => __('Solid', 'customify'),
							'dotted' => __('Dotted', 'customify'),
							'dashed' => __('Dashed', 'customify'),
							'double' => __('Double', 'customify'),
							'ridge'  => __('Ridge', 'customify'),
							'inset'  => __('Inset', 'customify'),
							'outset' => __('Outset', 'customify'),
						),
						'css_format' => '.woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs { border-bottom-style: {{value}}; } .woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li:first-child { border-top-style: {{value}}; }',
					),

					array(
						'name'       => 'border_width',
						'type'       => 'slider',
						'label'      => __('Border Width', 'customify'),
						'max' => 10,
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'selector'    => "format",
						'css_format' => '.woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs { border-bottom-width: {{value}}; } .woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li:first-child {border-top-width: {{value}}; }',
					),
					array(
						'name'       => 'border_color',
						'type'       => 'color',
						'label'      => __('Border Color', 'customify'),
						'selector'    => "format",
						'required'   => array(
							array( 'border_style', '!=', 'none' ),
							array( 'border_style', '!=', '' )
						),
						'css_format' => '.woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs li, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs { border-bottom-color: {{value}}; } .woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li:first-child{ border-top-color: {{value}}; }',
					),

				),
				'active_fields' => array(
					array(
						'name'       => 'color',
						'type'       => 'color',
						'label'      => __('Active Color', 'customify'),
						'selector'    => "format",
						'css_format' => '.woocommerce-tabs.wc-tabs-vertical .tabs.wc-tabs li.active, .woocommerce-tabs.wc-tabs-horizontal .tabs.wc-tabs li.active { color: {{value}}; } .woocommerce-tabs.wc-tabs-vertical .wc-tabs li.active:after { border-right-color: {{value}}; border-top-color: {{value}};  }',
					),
				)
			),

		);

		$configs[] = array(
			'name' => "{$section}_related_number",
			'type' => 'text',
			'default'=> 3,
			'section' =>  $section,
			'label' => __( 'Number related products', 'customify' ),
		);


		$configs[] = array(
			'name' => "{$section}_related_columns",
			'type' => 'text',
			'device_settings'=> true,
			'section' =>  $section,
			'label' => __( 'Related products per row', 'customify' ),
		);


		return $configs;
	}
}

new Customify_WC_Single_Product();