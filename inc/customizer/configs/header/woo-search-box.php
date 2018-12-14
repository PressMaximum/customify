<?php

class Customify_Builder_Item_Woo_Search_Box {
	public $id = 'woo_search_box';
	public $section = 'woo_search_box';
	public $name = 'woo_search_box';
	public $label = '';

	/**
	 * Optional construct
	 *
	 * Customify_Builder_Item_HTML constructor.
	 */
	function __construct() {
		$this->label = __( 'Woo Search Box', 'customify' );
	}

	/**
	 * Register Builder item
	 *
	 * @return array
	 */
	function item() {
		return array(
			'name'    => $this->label,
			'id'      => $this->id,
			'col'     => 0,
			'width'   => '1',
			'section' => $this->section, // Customizer section to focus when click settings.
		);
	}

	/**
	 * Optional, Register customize section and panel.
	 *
	 * @return array
	 */
	function customize() {
		// Render callback function.
		$fn       = array( $this, 'render' );
		$selector = ".header--row .header-{$this->id}-item";
		$config   = array(
			array(
				'name'  => $this->section,
				'type'  => 'section',
				'panel' => 'header_settings',
				'title' => $this->label,
			),

			array(
				'name'        => $this->section . '_show_cats',
				'type'        => 'checkbox',
				'section'     => $this->section,
				'default'     => false,
				'selector'        => "$selector",
				'render_callback' => $fn,
				'label'       => __( 'Enable product categories', 'customify' ),
				'description' => __( 'Enable search products with category', 'customify' ),
			),
			array(
				'name'            => $this->section . '_all_cat_text',
				'type'            => 'text',
				'section'         => $this->section,
				'selector'        => "$selector",
				'render_callback' => $fn,
				'label'           => __( 'All categories text', 'customify' ),
				'default'         => __( 'All Categories', 'customify' ),
				'required'        => array( $this->section . '_show_cats', '=', '1' ),
			),

			array(
				'name'            => $this->section . '_placeholder',
				'type'            => 'text',
				'section'         => $this->section,
				'selector'        => "$selector",
				'render_callback' => $fn,
				'label'           => __( 'Placeholder', 'customify' ),
				'default'         => __( 'Search ...', 'customify' ),
			),

			array(
				'name'            => $this->section . '_width',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $this->section,
				'selector'        => "$selector .header-search-form",
				'css_format'      => 'width: {{value}};',
				'label'           => __( 'Search Form Width', 'customify' ),
				'description'     => __( 'Note: The width can not greater than grid width.', 'customify' ),
			),

			array(
				'name'            => $this->section . '_height',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $this->section,
				'min'             => 25,
				'step'            => 1,
				'max'             => 100,
				'selector'        => "$selector .header-search-form .search-field, $selector .has_cats_select .search_product_cats",
				'css_format'      => 'height: {{value}};',
				'label'           => __( 'Input Height', 'customify' ),
			),

			array(
				'name'            => $this->section . '_icon_size',
				'type'            => 'slider',
				'device_settings' => true,
				'section'         => $this->section,
				'min'             => 5,
				'step'            => 1,
				'max'             => 100,
				'selector'        => "$selector .search-submit svg",
				'css_format'      => 'height: {{value}}; width: {{value}};',
				'label'           => __( 'Icon Size', 'customify' ),
			),

			array(
				'name'            => $this->section . '_icon_pos',
				'type'            => 'slider',
				'device_settings' => true,
				'default'         => array(
					'desktop' => array(
						'value' => - 40,
						'unit'  => 'px',
					),
					'tablet'  => array(
						'value' => - 40,
						'unit'  => 'px',
					),
					'mobile'  => array(
						'value' => - 40,
						'unit'  => 'px',
					),
				),
				'section'         => $this->section,
				'min'             => - 150,
				'step'            => 1,
				'max'             => 90,
				'selector'        => "$selector .search-submit",
				'css_format'      => 'margin-left: {{value}}; ',
				'label'           => __( 'Icon Position', 'customify' ),
			),

			array(
				'name'        => $this->section . '_font_size',
				'type'        => 'typography',
				'section'     => $this->section,
				'selector'    => "$selector .header-search-form .search-field, $selector .has_cats_select .search_product_cats",
				'css_format'  => 'typography',
				'label'       => __( 'Input Text Typography', 'customify' ),
				'description' => __( 'Typography for search input', 'customify' ),
			),

			array(
				'name'        => $this->section . '_input_styling',
				'type'        => 'styling',
				'section'     => $this->section,
				'css_format'  => 'styling',
				'title'       => __( 'Input Styling', 'customify' ),
				'description' => __( 'Search input styling', 'customify' ),
				'selector'    => array(
					'normal'            => "{$selector} .woo-header-search-form.no_cats_select .search-field,{$selector} .woo-header-search-form.has_cats_select",
					'hover'             => "{$selector} .woo-header-search-form.no_cats_select .search-field:focus",
					'normal_text_color' => "{$selector} .woo-header-search-form.no_cats_select .search-field, {$selector} .woo-header-search-form.no_cats_select input.search-field::placeholder",
				),
				'default'     => array(
					'normal' => array(
						'border_style' => 'solid',
					),
				),
				'fields'      => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'bg_cover'      => false,
						'bg_image'      => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
						'margin'        => false,
						'border_radius' => false,
						'border_style' => false,
						'border_width' => false,
						'border_color' => false,
					),
					'hover_fields'  => false,
				),
			),

			array(
				'name'        => $this->section . '_icon_styling',
				'type'        => 'styling',
				'section'     => $this->section,
				'css_format'  => 'styling',
				'title'       => __( 'Icon Styling', 'customify' ),
				'description' => __( 'Search input styling', 'customify' ),
				'selector'    => array(
					'normal' => "{$selector} .search-submit",
					'hover'  => "{$selector} .search-submit:hover",
				),
				'fields'      => array(
					'normal_fields' => array(
						'link_color'    => false, // disable for special field.
						'bg_cover'      => false,
						'bg_image'      => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
						'margin'        => false,
					),
					'hover_fields'  => array(
						'link_color'    => false,
						'padding'       => false,
						'bg_cover'      => false,
						'bg_image'      => false,
						'bg_repeat'     => false,
						'bg_attachment' => false,
						'border_radius' => false,
					), // disable hover tab and all fields inside.
				),
			),

		);

		// Item Layout.
		return array_merge( $config, customify_header_layout_settings( $this->id, $this->section ) );
	}

	/**
	 * Optional. Render item content
	 */
	function render() {
		$all_cats_text = Customify()->get_setting( $this->section . '_all_cat_text' );

		$args = array(
			'show_option_all' => esc_html( $all_cats_text ),
			'taxonomy'     => 'product_cat',
			'orderby'      => 'ID',
			'order'        => 'ASC',
			'hierarchical' => true,
			'hide_empty'   => true,
			'value_field'  => 'slug',
			'selected'     => '',
			'name'         => 'product_cat',
			'id'           => 'product_cat_' . uniqid(),
			'class'        => 'search_product_cats',
		);
		$all_categories = get_categories( $args );

		$placeholder = Customify()->get_setting( $this->section . '_placeholder' );
		$enable_cats = Customify()->get_setting( $this->section . '_show_cats' );

		$placeholder = sanitize_text_field( $placeholder );
		echo '<div class="header-' . esc_attr( $this->id ) . '-item item--' . esc_attr( $this->id ) . '">';
		$extra_class = array();
		if ( $enable_cats ) {
			$extra_class[] = 'has_cats_select';
		} else {
			$extra_class[] = 'no_cats_select';
		}
		?>
		<form role="search" class="header-search-form woo-header-search-form <?php echo esc_attr( implode( ' ', $extra_class ) ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label>
				<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'customify' ); ?></span>
				<?php
				if ( $enable_cats ) {
					wp_dropdown_categories( $args );
				}
				?>
				<input type="search" class="search-field" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'label', 'customify' ); ?>" />
				<input type="hidden" name="post_type" value="product" />
			</label>
			<button type="submit" class="search-submit">
				<svg aria-hidden="true" focusable="false" role="presentation" xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21">
					<path fill="currentColor" fill-rule="evenodd" d="M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z"></path>
				</svg>
			</button>
		</form>
		<?php
		echo '</div>';
	}
}

if ( class_exists( 'WooCommerce' ) ) {
	Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Woo_Search_Box() );
}
