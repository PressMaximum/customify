<?php

/**
 * Display payment method in the footer
 *
 * @since 0.2.3
 *
 * Class Customify_Builder_Item_Payment_Methods
 */
class Customify_Builder_Item_Payment_Methods {
	public $id = 'footer_payment_methods';
	public $section = 'footer_payment_methods';
	public $class = 'footer_payment_methods';
	public $selector = '';
	public $panel = 'footer_settings';

	function __construct()
	{
		$this->selector = '.'.$this->class;
		add_filter( 'customify/icon_used', array( $this, 'used_icon' ) );
	}

	function used_icon( $list = array() ){
		$list[ $this->id ] = 1;
		return $list;
	}

	function item() {
		return array(
			'name'    => __( 'Payment Methods', 'customify' ),
			'id'      => $this->id,
			'col'     => 0,
			'width'   => '4',
			'section' =>  $this->section // Customizer section to focus when click settings
		);
	}

	function customize() {
		$section = $this->section;
		$prefix  = $this->section;
		$fn      = array( $this, 'render' );
		$selector = "{$this->selector}";
		$config  = array(
			array(
				'name'           => $section,
				'type'           => 'section',
				'panel'          => $this->panel,
				'theme_supports' => '',
				'title'          => __( 'Payment Methods', 'customify' ),
			),

			array(
				'name'             => $prefix . '_items',
				'type'             => 'repeater',
				'section'          => $section,
				'selector'         => $this->selector,
				'render_callback'  => $fn,
				'title'            => __( 'Payment Methods', 'customify' ),
				'live_title_field' => 'title',
				'default'          => array(
					array(
						'title' => "Mastercard",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-mastercard',
						)
					),
					array(
						'title' => "Visa",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-visa',
						)
					),
					array(
						'title' => "Discover",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-discover',
						)
					),
					array(
						'title' => "American Express",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-amex',
						)
					),
					array(
						'title' => "Paypal",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-paypal',
						)
					),
					array(
						'title' => "Stripe",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-stripe',
						)
					),
					array(
						'title' => "JCB",
						'url' => '#',
						'icon' => array(
							'type' => 'font-awesome',
							'icon' => 'fa fa-cc-jcb',
						)
					),
				),
				'fields'           => array(
					array(
						'name'  => 'title',
						'type'  => 'text',
						'label' => __( 'Title', 'customify' ),
					),
					array(
						'name'  => 'icon',
						'type'  => 'icon',
						'label' => __( 'Icon', 'customify' ),
					),

					array(
						'name'  => 'url',
						'type'  => 'text',
						'label' => __( 'URL', 'customify' ),
					),

				)
			),

			array(
				'name'            => $prefix . '_size',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $section,
				'min'             => 10,
				'step'            => 1,
				'max'             => 100,
				'selector'        => "$selector li",
				'css_format'      => "font-size: {{value}};",
				'label'           => __( 'Size', 'customify' ),
			),

			array(
				'name'            => $prefix . '_spacing',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $section,
				'min'             => 10,
				'step'            => 1,
				'max'             => 50,
				'default'         => 10,
				'selector'        => "$selector li",
				'css_format'      => "margin-left: calc( {{value}} / 2 ); margin-right: calc( {{value}} / 2 );",
				'label'           => __( 'Spacing', 'customify' ),
			),

			array(
				'name'            => $prefix . '_color',
				'type'            => 'color',
				'section'         => $section,
				'selector'        => "$selector li",
				'css_format'      => "Color: {{value}};",
				'label'           => __( 'Color', 'customify' ),
			),

		);

		// Item Layout
		return array_merge( $config, customify_footer_layout_settings( $this->id, $section ) );
	}

	function render( $item_config = array() ) {

		$color_type = Customify()->get_setting( $this->section.'_color_type' );
		$items = Customify()->get_setting( $this->section.'_items' );

		if ( ! empty( $items ) ) {
			$classes = array();
			$classes[] = $this->class;
			$classes[] = 'customify-builder-payment-methods';
			if ( $color_type ) {
				$classes[] = 'color-'.sanitize_text_field( $color_type );
			}

			echo '<ul class="' . esc_attr( join( " ", $classes ) ) . '">';
			foreach ( ( array ) $items as $index => $item ) {
				$item = wp_parse_args( $item, array(
					'title'       => '',
					'icon'        => '',
					'_visibility' => ''
				) );

				if ( $item['_visibility'] !== 'hidden' ) {
					$icon = wp_parse_args( $item['icon'], array(
						'type' => '',
						'icon' => '',
					) );

					echo '<li title="'.esc_attr( $item['title'] ).'" class="social-'. str_replace( array( ' ', 'fa-fa' ), array( '-', 'icon' ), esc_attr( $icon['icon'] )). '">';

					if ( ! $item['url'] ) {
						$item['url'] = '#';
					}

					if ( $icon['icon'] ) {
						echo '<i class="icon ' . esc_attr( $icon['icon'] ) . '"></i>';
					}

					echo '</li>';
				}

			}

			echo '</ul>';
		}

	}

}

Customify_Customize_Layout_Builder()->register_item( 'footer', new Customify_Builder_Item_Payment_Methods() );

