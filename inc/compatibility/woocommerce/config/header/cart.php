<?php

class Customify_Builder_Item_WC_Cart {
	/**
	 * @var string Item Id.
	 */
	public $id = 'wc_cart'; // Required.
	/**
	 * @var string Section ID.
	 */
	public $section = 'wc_cart'; // Optional.
	/**
	 * @var string Item Name.
	 */
	public $name = 'wc_cart'; // Optional.
	/**
	 * @var string|void Item label.
	 */
	public $label = ''; // Optional.
	/**
	 * @var int Priority.
	 */
	public $priority = 200;
	/**
	 * @var string Panel ID.
	 */
	public $panel = 'header_settings';

	/**
	 * Optional construct
	 *
	 * Customify_Builder_Item_HTML constructor.
	 */
	public function __construct() {
		$this->label = __( 'Shopping Cart', 'customify' );

		// Cart drawer: when the Cart Behavior is set to Drawer, suppress the
		// inline hover dropdown (through the public render_dropdown seam) and
		// print the off-canvas panel near </body>.
		add_filter( 'customify/wc_cart/render_dropdown', array( $this, 'maybe_suppress_dropdown' ), 10, 2 );

		// Don't ship the drawer's inline dynamic CSS to sites that aren't using
		// the drawer: its style controls carry defaults that Customify's auto-CSS
		// would otherwise print (inert) on every WooCommerce page. Kept in drawer
		// mode and in the Customizer preview so switching behavior previews live.
		add_filter( 'customify/customizer/auto_css', array( $this, 'gate_drawer_auto_css' ), 10, 2 );

		if ( ! is_admin() ) {
			add_action( 'wp_footer', array( $this, 'render_cart_drawer' ) );
		}
	}

	/**
	 * Register Builder item
	 *
	 * @return array
	 */
	public function item() {
		return array(
			'name'    => $this->label,
			'id'      => $this->id,
			'col'     => 0,
			'width'   => '4',
			'section' => $this->section, // Customizer section to focus when click settings.
		);
	}

	/**
	 * Optional, Register customize section and panel.
	 *
	 * @return array
	 */
	function customize() {
		$fn     = array( $this, 'render' );
		$config = array(
			array(
				'name'     => $this->section,
				'type'     => 'section',
				'panel'    => $this->panel,
				'priority' => $this->priority,
				'title'    => $this->label,
			),

			array(
				'name'            => "{$this->name}_text",
				'type'            => 'text',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'title'           => __( 'Label', 'customify' ),
				'default'         => __( 'Cart', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_icon",
				'type'            => 'icon',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'default'         => array(
					'icon' => 'fa fa-shopping-basket',
					'type' => 'font-awesome',
				),
				'title'           => __( 'Icon', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_icon_position",
				'type'            => 'select',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'default'         => 'after',
				'choices'         => array(
					'before' => __( 'Before', 'customify' ),
					'after'  => __( 'After', 'customify' ),
				),
				'title'           => __( 'Icon Position', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_link_to",
				'type'            => 'select',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'default'         => 'cart',
				'choices'         => array(
					'cart'     => __( 'Cart Page', 'customify' ),
					'checkout' => __( 'Checkout', 'customify' ),
				),
				'title'           => __( 'Link To', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_show_label",
				'type'            => 'checkbox',
				'default'         => array(
					'desktop' => 1,
					'tablet'  => 1,
					'mobile'  => 0,
				),
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'theme_supports'  => '',
				'label'           => __( 'Show Label', 'customify' ),
				'checkbox_label'  => __( 'Show Label', 'customify' ),
				'device_settings' => true,
			),

			array(
				'name'            => "{$this->name}_show_sub_total",
				'type'            => 'checkbox',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'theme_supports'  => '',
				'label'           => __( 'Sub Total', 'customify' ),
				'checkbox_label'  => __( 'Show Sub Total', 'customify' ),
				'device_settings' => true,
				'default'         => array(
					'desktop' => 1,
					'tablet'  => 1,
					'mobile'  => 0,
				),
			),

			array(
				'name'            => "{$this->name}_show_qty",
				'type'            => 'checkbox',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'default'         => 1,
				'label'           => __( 'Quantity', 'customify' ),
				'checkbox_label'  => __( 'Show Quantity', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_sep",
				'type'            => 'text',
				'section'         => $this->section,
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'title'           => __( 'Separator', 'customify' ),
				'default'         => __( '/', 'customify' ),
			),

			array(
				'name'       => "{$this->name}_label_styling",
				'type'       => 'styling',
				'section'    => $this->section,
				'title'      => __( 'Styling', 'customify' ),
				'selector'   => array(
					'normal' => '.builder-header-' . $this->id . '-item .cart-item-link',
					'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-item-link',
				),
				'css_format' => 'styling',
				'default'    => array(),
				'fields'     => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'margin'        => false,
						'bg_image'      => false,
						'bg_cover'      => false,
						'bg_position'   => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
					),
					'hover_fields'  => array(
						'link_color' => false, // disable for special field.
					),
				),
			),

			array(
				'name'       => "{$this->name}_typography",
				'type'       => 'typography',
				'section'    => $this->section,
				'title'      => __( 'Typography', 'customify' ),
				'selector'   => '.builder-header-' . $this->id . '-item',
				'css_format' => 'typography',
				'default'    => array(),
			),

			array(
				'name'    => "{$this->name}_icon_h",
				'type'    => 'heading',
				'section' => $this->section,
				'title'   => __( 'Icon Settings', 'customify' ),
			),

			array(
				'name'            => "{$this->name}_icon_size",
				'type'            => 'slider',
				'section'         => $this->section,
				'device_settings' => true,
				'max'             => 150,
				'title'           => __( 'Icon Size', 'customify' ),
				'selector'        => '.builder-header-' . $this->id . '-item .cart-icon i:before',
				'css_format'      => 'font-size: {{value}};',
				'default'         => array(),
			),

			array(
				'name'        => "{$this->name}_icon_styling",
				'type'        => 'styling',
				'section'     => $this->section,
				'title'       => __( 'Styling', 'customify' ),
				'description' => __( 'Advanced styling for cart icon', 'customify' ),
				'selector'    => array(
					'normal' => '.builder-header-' . $this->id . '-item .cart-item-link .cart-icon i',
					'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-item-link .cart-icon i',
				),
				'css_format'  => 'styling',
				'default'     => array(),
				'fields'      => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'bg_image'      => false,
						'bg_cover'      => false,
						'bg_position'   => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
					),
					'hover_fields'  => array(
						'link_color' => false, // disable for special field.
					),
				),
			),

			array(
				'name'        => "{$this->name}_qty_styling",
				'type'        => 'styling',
				'section'     => $this->section,
				'title'       => __( 'Quantity', 'customify' ),
				'description' => __( 'Advanced styling for cart quantity', 'customify' ),
				'selector'    => array(
					'normal' => '.builder-header-' . $this->id . '-item  .cart-icon .cart-qty .customify-wc-total-qty',
					'hover'  => '.builder-header-' . $this->id . '-item:hover .cart-icon .cart-qty .customify-wc-total-qty',
				),
				'css_format'  => 'styling',
				'default'     => array(),
				'fields'      => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'bg_image'      => false,
						'bg_cover'      => false,
						'bg_position'   => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
					),
					'hover_fields'  => array(
						'link_color' => false, // disable for special field.
					),
				),
			),

			// -------------------------------------------------------- Cart behavior
			// Off-canvas drawer as an alternative to the hover dropdown. Default
			// 'dropdown' keeps every existing site identical.
			array(
				'name'    => "{$this->name}_behavior_h",
				'type'    => 'heading',
				'section' => $this->section,
				'title'   => __( 'Cart Behavior', 'customify' ),
			),

			array(
				'name'    => "{$this->name}_behavior",
				'type'    => 'select',
				'section' => $this->section,
				'title'   => __( 'Behavior', 'customify' ),
				'default' => 'dropdown',
				'choices' => array(
					'dropdown' => __( 'Dropdown', 'customify' ),
					'drawer'   => __( 'Off-Canvas Drawer', 'customify' ),
				),
			),

			// ----------------------------------------------------- Dropdown (gated)
			array(
				'name'     => "{$this->name}_d_h",
				'type'     => 'heading',
				'section'  => $this->section,
				'title'    => __( 'Dropdown Settings', 'customify' ),
				'required' => array( "{$this->name}_behavior", '=', 'dropdown' ),
			),

			array(
				'name'            => "{$this->name}_d_align",
				'type'            => 'select',
				'section'         => $this->section,
				'title'           => __( 'Dropdown Alignment', 'customify' ),
				'selector'        => '.builder-header-' . $this->id . '-item',
				'render_callback' => $fn,
				'default'         => array(),
				'required'        => array( "{$this->name}_behavior", '=', 'dropdown' ),
				'choices'         => array(
					'left'  => __( 'Left', 'customify' ),
					'right' => __( 'Right', 'customify' ),
				),
			),

			array(
				'name'            => "{$this->name}_d_width",
				'type'            => 'slider',
				'section'         => $this->section,
				'device_settings' => true,
				'min'             => 280,
				'max'             => 600,
				'title'           => __( 'Dropdown Width', 'customify' ),
				'selector'        => '.builder-header-' . $this->id . '-item  .cart-dropdown-box',
				'css_format'      => 'width: {{value}};',
				'default'         => array(),
				'required'        => array( "{$this->name}_behavior", '=', 'dropdown' ),
			),

			// ------------------------------------------------------- Drawer (gated)
			// width / bg / backdrop / radius / offset are Customizer-owned; their
			// defaults emit as dynamic CSS on first paint (see _wc-cart-drawer.scss),
			// so the SCSS never hardcodes them.
			array(
				'name'     => "{$this->name}_drawer_h",
				'type'     => 'heading',
				'section'  => $this->section,
				'title'    => __( 'Drawer Settings', 'customify' ),
				'required' => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'     => "{$this->name}_drawer_position",
				'type'     => 'select',
				'section'  => $this->section,
				'title'    => __( 'Drawer Position', 'customify' ),
				'default'  => 'right',
				'required' => array( "{$this->name}_behavior", '=', 'drawer' ),
				'choices'  => array(
					'right' => __( 'Right', 'customify' ),
					'left'  => __( 'Left', 'customify' ),
				),
			),

			array(
				'name'            => "{$this->name}_drawer_width",
				'type'            => 'slider',
				'section'         => $this->section,
				'title'           => __( 'Drawer Width', 'customify' ),
				'unit'            => '%',
				'min'             => 20,
				'max'             => 100,
				'device_settings' => true,
				'default'         => array(
					'desktop' => 26,
					'tablet'  => 65,
					'mobile'  => 90,
				),
				'selector'        => '.customify-cart-drawer',
				'css_format'      => 'width: {{value_no_unit}}%;',
				'required'        => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'     => "{$this->name}_drawer_auto_open",
				'type'     => 'checkbox',
				'section'  => $this->section,
				'title'    => __( 'Auto-open on Add to Cart', 'customify' ),
				'default'  => 0,
				'required' => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'            => "{$this->name}_drawer_offset",
				'type'            => 'css_ruler',
				'section'         => $this->section,
				'title'           => __( 'Drawer Offset', 'customify' ),
				'description'     => __( 'Gap from the screen edges. Set values to float the drawer away from the edge.', 'customify' ),
				'device_settings' => true,
				'default'         => array(
					'desktop' => array( 'unit' => 'px', 'top' => 18, 'right' => 18, 'bottom' => 18, 'left' => 18, 'link' => 1 ),
					'tablet'  => array( 'unit' => 'px', 'top' => 18, 'right' => 18, 'bottom' => 18, 'left' => 18, 'link' => 1 ),
					'mobile'  => array( 'unit' => 'px', 'top' => 18, 'right' => 18, 'bottom' => 18, 'left' => 18, 'link' => 1 ),
				),
				'selector'        => '.customify-cart-drawer',
				'css_format'      => array(
					'top'    => 'margin-top: {{value}};',
					'right'  => 'margin-right: {{value}};',
					'bottom' => 'margin-bottom: {{value}};',
					'left'   => 'margin-left: {{value}};',
				),
				'required'        => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'       => "{$this->name}_drawer_radius",
				'type'       => 'slider',
				'section'    => $this->section,
				'title'      => __( 'Corner Radius', 'customify' ),
				'unit'       => 'px',
				'min'        => 0,
				'max'        => 40,
				'default'    => array( 'value' => 7, 'unit' => 'px' ),
				'selector'   => '.customify-cart-drawer',
				'css_format' => 'border-radius: {{value_no_unit}}px;',
				'required'   => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'       => "{$this->name}_drawer_backdrop",
				'type'       => 'color',
				'section'    => $this->section,
				'title'      => __( 'Backdrop Color', 'customify' ),
				'default'    => 'rgba(0,0,0,0.5)',
				'selector'   => '.customify-cart-drawer-overlay',
				'css_format' => 'background-color: {{value}};',
				'required'   => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'       => "{$this->name}_drawer_bg",
				'type'       => 'color',
				'section'    => $this->section,
				'title'      => __( 'Panel Background', 'customify' ),
				'default'    => '#ffffff',
				'selector'   => '.customify-cart-drawer',
				'css_format' => 'background-color: {{value}};',
				'required'   => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

			array(
				'name'       => "{$this->name}_drawer_head_color",
				'type'       => 'color',
				'section'    => $this->section,
				'title'      => __( 'Heading Color', 'customify' ),
				'selector'   => '.customify-cart-drawer__head',
				'css_format' => 'color: {{value}};',
				'required'   => array( "{$this->name}_behavior", '=', 'drawer' ),
			),

		);

		// Item Layout.
		return array_merge( $config, customify_header_layout_settings( $this->id, $this->section ) );
	}

	function array_to_class( $array, $prefix ) {
		if ( ! is_array( $array ) ) {
			return $prefix . '-' . $array;
		}
		$classes = array();
		$array   = array_reverse( $array );
		foreach ( $array as $k => $v ) {
			if ( 1 == $v ) {
				$v = 'show';
			} elseif ( 0 == $v ) {
				$v = 'hide';
			}
			$classes[] = "{$prefix}-{$k}-{$v}";
		}

		return join( ' ', $classes );
	}

	/**
	 * Optional. Render item content
	 */
	public function render() {

		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$icon          = Customify()->get_setting( "{$this->name}_icon" );
		$icon_position = Customify()->get_setting( "{$this->name}_icon_position" );
		$text          = Customify()->get_setting( "{$this->name}_text" );

		$show_label     = Customify()->get_setting( "{$this->name}_show_label", 'all' );
		$show_sub_total = Customify()->get_setting( "{$this->name}_show_sub_total", 'all' );
		$show_qty       = Customify()->get_setting( "{$this->name}_show_qty" );
		$sep            = Customify()->get_setting( "{$this->name}_sep" );
		$link_to        = Customify()->get_setting( "{$this->name}_link_to" );

		$classes = array();

		$align = Customify()->get_setting( "{$this->name}_d_align" );
		if ( ! $align ) {
			$align = 'right';
		}
		$classes[] = $this->array_to_class( $align, 'd-align' );

		$label_classes    = $this->array_to_class( $show_label, 'wc-cart' );
		$subtotal_classes = $this->array_to_class( $show_sub_total, 'wc-cart' );

		$icon = wp_parse_args(
			$icon,
			array(
				'type' => '',
				'icon' => '',
			)
		);

		$icon_html = '';
		if ( $icon['icon'] ) {
			$icon_html = '<i class="' . esc_attr( $icon['icon'] ) . '"></i> ';
		}

		if ( $text ) {
			$text = '<span class="cart-text cart-label ' . esc_attr( $label_classes ) . '">' . sanitize_text_field( $text ) . '</span>';
		}

		$sub_total  = WC()->cart->get_cart_subtotal();
		$quantities = WC()->cart->get_cart_item_quantities();

		$html = $text;

		if ( $sep && $html ) {
			$html .= '<span class="cart-sep cart-label ' . esc_attr( $label_classes ) . '">' . sanitize_text_field( $sep ) . '</span>';
		}
		$html .= '<span class="cart-subtotal cart-label ' . esc_attr( $subtotal_classes ) . '"><span class="customify-wc-sub-total">' . $sub_total . '</span></span>';

		$qty   = array_sum( $quantities );
		$class = 'customify-wc-total-qty';
		if ( $qty <= 0 ) {
			$class .= ' hide-qty';
		}

		if ( $icon_html ) {
			$icon_html = '<span class="cart-icon">' . $icon_html;
			if ( $show_qty ) {
				$icon_html .= '<span class="cart-qty"><span class="' . $class . '">' . array_sum( $quantities ) . '</span></span>';
			}
			$icon_html .= '</span>';
		}

		if ( 'before' == $icon_position ) {
			$html = $icon_html . $html;
		} else {
			$html = $html . $icon_html;
		}

		$classes[] = 'builder-header-' . $this->id . '-item';
		$classes[] = 'item--' . $this->id;

		$link = '';
		if ( 'checkout' == $link_to ) {
			$link = get_permalink( wc_get_page_id( 'checkout' ) );
		} else {
			$link = get_permalink( wc_get_page_id( 'cart' ) );
		}

		echo '<div class="' . esc_attr( join( ' ', $classes ) ) . '">';

		echo '<a href="' . esc_url( $link ) . '" class="cart-item-link text-uppercase text-small link-meta">';
		echo $html; // WPCS: XSS OK.
		echo '</a>';

		/**
		 * Filter whether the header cart item prints its inline mini-cart
		 * dropdown. Default true, so the hover dropdown is unchanged for every
		 * existing site. Customify Pro's Cart Drawer feature returns false when
		 * it takes over the cart item with an off-canvas drawer, so the
		 * mini-cart content is not rendered twice (the drawer prints its own
		 * WC_Widget_Cart in the footer while sharing the same AJAX fragments).
		 *
		 * @param bool                           $render_dropdown Whether to print the inline dropdown. Default true.
		 * @param Customify_Builder_Item_WC_Cart $item            The cart builder item instance.
		 */
		$render_dropdown = apply_filters( 'customify/wc_cart/render_dropdown', true, $this );

		if ( $render_dropdown ) {
			add_filter( 'woocommerce_widget_cart_is_hidden', '__return_false', 999 );

			echo '<div class="cart-dropdown-box widget-area">';
			the_widget(
				'WC_Widget_Cart',
				array(
					'hide_if_empty' => 0,
				)
			);
			echo '</div>';

			remove_filter( 'woocommerce_widget_cart_is_hidden', '__return_false', 999 );
		}

		echo '</div>';
	}

	/**
	 * When the Cart Behavior is set to Drawer, don't print the inline hover
	 * dropdown — the off-canvas drawer takes over the cart item. Routed through
	 * the public `customify/wc_cart/render_dropdown` seam so third-party
	 * consumers keep working.
	 *
	 * @param bool                                $render Whether to print the inline dropdown.
	 * @param Customify_Builder_Item_WC_Cart|null $item   The cart builder item (unused).
	 *
	 * @return bool
	 */
	public function maybe_suppress_dropdown( $render, $item = null ) {
		if ( 'drawer' === Customify()->get_setting( "{$this->name}_behavior" ) ) {
			return false;
		}

		return $render;
	}

	/**
	 * Suppress the drawer style controls' dynamic CSS when the drawer isn't the
	 * active behavior, so a dropdown-mode site (the 30k default) doesn't ship
	 * inert `.customify-cart-drawer{…}` inline rules on every page. Still emitted
	 * in drawer mode and in the Customizer preview so switching previews live.
	 * Only the `wc_cart_drawer_*` fields are touched; every other field passes
	 * through unchanged.
	 *
	 * @param array $code_array The generated CSS pieces for a field.
	 * @param array $field      The field config.
	 *
	 * @return array
	 */
	public function gate_drawer_auto_css( $code_array, $field ) {
		if ( empty( $field['name'] ) || 0 !== strpos( $field['name'], "{$this->name}_drawer_" ) ) {
			return $code_array;
		}
		if ( is_customize_preview() ) {
			return $code_array;
		}
		if ( 'drawer' !== Customify()->get_setting( "{$this->name}_behavior" ) ) {
			return array();
		}

		return $code_array;
	}

	/**
	 * Print the off-canvas drawer panel + overlay once, near </body>, when the
	 * Cart Behavior is Drawer. Skipped on Cart/Checkout (nothing to preview
	 * there — the cart link just follows its href). The body reuses WooCommerce
	 * core's own `.widget_shopping_cart_content` + woocommerce_mini_cart() so
	 * the AJAX fragments keep it in live sync on add/remove.
	 */
	public function render_cart_drawer() {
		if ( ! function_exists( 'WC' ) || is_cart() || is_checkout() ) {
			return;
		}

		if ( 'drawer' !== Customify()->get_setting( "{$this->name}_behavior" ) ) {
			return;
		}

		$position = 'left' === Customify()->get_setting( "{$this->name}_drawer_position" ) ? 'left' : 'right';
		?>
		<div class="customify-cart-drawer-overlay" hidden></div>
		<aside id="customify-cart-drawer" class="customify-cart-drawer" data-position="<?php echo esc_attr( $position ); ?>"
			role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping cart', 'customify' ); ?>" hidden>
			<div class="customify-cart-drawer__head">
				<span class="customify-cart-drawer__title"><?php esc_html_e( 'Shopping Cart', 'customify' ); ?></span>
				<?php // Same markup/class as the Quick View close (theme's a.remove2x) so it looks identical. ?>
				<a href="#" class="remove2x customify-cart-drawer__close" role="button" aria-label="<?php esc_attr_e( 'Close', 'customify' ); ?>">&times;</a>
			</div>
			<div class="customify-cart-drawer__body">
				<?php
				// Match WooCommerce core's own fragment exactly (see
				// WC_AJAX::get_refreshed_fragments): a `.widget_shopping_cart_content`
				// wrapper around woocommerce_mini_cart(). Keeps the AJAX live-sync
				// working AND avoids the second "Cart" heading that
				// the_widget( 'WC_Widget_Cart' ) prints.
				?>
				<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
			</div>
			<?php // Shown only when the cart is empty (JS toggles .is-cart-empty). ?>
			<div class="customify-cart-drawer__continue">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button"><?php esc_html_e( 'Continue Shopping', 'customify' ); ?></a>
			</div>
		</aside>
		<?php
	}
}

Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_WC_Cart() );
