<?php

class Customify_Builder_Header extends Customify_Customize_Builder_Panel {
	public $id = 'header';

	/**
	 * Panel builder configs.
	 *
	 * @since 0.0.1
	 * @since 0.2.9
	 *
	 * @return array
	 */
	function get_config() {
		return array(
			'id'            => $this->id,
			'title'         => __( 'Header Builder', 'customify' ),
			'control_id'    => 'header_builder_panel', // Control ID for ver 1.
			'version_id'    => 'header_builder_version', // The control id where store version.
			'panel'         => 'header_settings',
			'section'       => 'header_builder_panel',
			// Versions support, can choice v1 or v2.
			'versions'      => array(
				'v1' => array(
					'control_id' => 'header_builder_panel',
					'label' => __( 'Version 1', 'customify' ),
				),
				'v2' => array(
					'control_id' => 'header_builder_panel_v2',
					'label' => __( 'Version 2', 'customify' ),
				),
			),
			'devices'               => array(
				'desktop'      => __( 'Desktop', 'customify' ),
				'mobile'       => __( 'Mobile/Tablet', 'customify' ),
			),
			'react_control_id'      => 'header_builder_panel_v2',
			'panel_items_container' => 'customify-hb-panel-items',
		);
	}

	function get_rows_config() {
		return array(
			'top'     => __( 'Header Top', 'customify' ),
			'main'    => __( 'Header Main', 'customify' ),
			'bottom'  => __( 'Header Bottom', 'customify' ),
			'sidebar' => __( 'Menu Sidebar', 'customify' ),
		);
	}

	function customize() {

		$fn     = 'customify_customize_render_header';
		$config = array(
			array(
				'name'     => 'header_settings',
				'type'     => 'panel',
				'priority' => 1,
				'title'    => __( 'Header', 'customify' ),
			),

			array(
				'name'            => 'header_builder_panel',
				'type'            => 'section',
				'panel'           => 'header_settings',
				'title'           => __( 'Header Builder', 'customify' ),
			),

			// V2 layout data — the only active builder setting.
			array(
				'name'                => 'header_builder_panel_v2',
				'type'                => 'js_raw',
				'section'             => 'header_builder_panel',
				'theme_supports'      => '',
				'title'               => '',
				'selector'            => '#masthead',
				'render_callback'     => $fn,
				'container_inclusive' => true,
			),

		);

		return $config;
	}

	function row_config( $section = false, $section_name = false ) {

		if ( ! $section ) {
			$section = 'header_top';
		}
		if ( ! $section_name ) {
			$section_name = __( 'Header Top', 'customify' );
		}

		// Text skin.
		$color_mode = 'light-mode';
		if ( 'header_top' == $section ) {
			$color_mode = 'dark-mode';
		}

		$selector           = '.header--row.' . str_replace( '_', '-', $section );
		$skin_selector      = '.header--row.' . str_replace( '_', '-', $section );
		$skin_selector      = '.header--row:not(.header--transparent).' . str_replace( '_', '-', $section );
		$skin_mode_selector = '.header--row-inner.' . str_replace( '_', '-', $section ) . '-inner';

		$fn           = 'customify_customize_render_header';
		$selector_all = '#masthead';

		$config = array(
			array(
				'name'            => $section,
				'type'            => 'section',
				'panel'           => 'header_settings',
				'theme_supports'  => '',
				'title'           => $section_name,
			),

			array(
				'name'            => $section . '_layout',
				'type'            => 'select',
				'section'         => $section,
				'title'           => __( 'Layout', 'customify' ),
				'selector'        => $selector,
				'css_format'      => 'html_class',
				'render_callback' => $fn,
				'default'         => 'layout-full-contained',
				'choices'         => array(
					'layout-full-contained' => __( 'Full width - Contained', 'customify' ),
					'layout-fullwidth'      => __( 'Full Width', 'customify' ),
					'layout-contained'      => __( 'Contained', 'customify' ),
				),
			),

			array(
				'name'        => $section . '_noti_layout',
				'type'        => 'custom_html',
				'section'     => $section,
				'title'       => '',
				'description' => __( "Layout <code>Full width - Contained</code> and <code>Full Width</code> will not fit browser width because you've selected <a class='focus-control' data-id='site_layout' href='#'>Site Layout</a> as <code>Boxed</code> or <code>Framed</code>", 'customify' ),
				'required'    => array(
					array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
				),
			),

			array(
				'name'            => $section . '_height',
				'type'            => 'slider',
				'section'         => $section,
				'theme_supports'  => '',
				'device_settings' => true,
				'max'             => 250,
				'selector'        => $selector . " .customify-grid, $selector .style-full-height .primary-menu-ul > li > a",
				'css_format'      => 'min-height: {{value}};',
				'title'           => __( 'Height', 'customify' ),
			),

			// Spacing between the row's column slots (left/center/right).
			// Targets the per-row grid container `.row-v2-{id}` (one per
			// row, classes `row-v2 row-v2-top|main|bottom` emitted by
			// Customify_Layout_Builder_Frontend_V2::render_row()). Scoped
			// inside `.header--row.{section}` so footer rows that share
			// the same row-v2 class name don't pick this up.
			array(
				'name'            => $section . '_column_gap',
				'type'            => 'slider',
				'section'         => $section,
				'theme_supports'  => '',
				'device_settings' => true,
				'max'             => 100,
				'selector'        => $selector . ' .row-v2-' . preg_replace( '/^' . preg_quote( $this->id, '/' ) . '_/', '', $section ),
				'css_format'      => 'column-gap: {{value}};',
				'title'           => __( 'Column Gap', 'customify' ),
				'description'     => __( 'Spacing between header columns.', 'customify' ),
			),

			array(
				'name'       => $section . '_text_mode',
				'type'       => 'image_select',
				'section'    => $section,
				'selector'   => $skin_mode_selector,
				'css_format' => 'html_class',
				'title'      => __( 'Skin Mode', 'customify' ),
				'default'    => $color_mode,
				'choices'    => array(
					'dark-mode'  => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/build/images/customizer/text_mode_light.svg',
						'label' => 'Dark',
					),
					'light-mode' => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/build/images/customizer/text_mode_dark.svg',
						'label' => 'Light',
					),
				),
			),

			array(
				'name'             => $section . '_styling',
				'type'             => 'styling',
				'section'          => $section,
				'title'            => __( 'Advanced Styling', 'customify' ),
				'description'      => sprintf( __( 'Advanced styling for %s', 'customify' ), $section_name ),
				'live_title_field' => 'title',
				'selector'         => array(
					'normal' => "{$skin_selector} .header--row-inner",
				),
				'css_format'       => 'styling',
				'fields'           => array(
					'normal_fields' => array(
						'text_color' => false,
						'link_color' => false,
						'padding'    => false,
						'margin'     => false,
					),
					'hover_fields'  => false,
				), // disable hover tab and all fields inside.
			),

			array(
				'name'               => $section . '_columns_settings',
				'type'               => 'columns_settings',
				'section'            => $section,
				'priority'           => 999,
				'title'              => __( 'Column Settings', 'customify' ),
				'description'        => __( 'Per-column direction, align, gap and padding.', 'customify' ),
				'col_layout_setting' => '',
				'column_keys'        => array( 'left', 'center', 'right' ),
				'default_direction'  => 'row',
				// Header double-renders its items: desktop markup lives
				// under `.cb-row--desktop`, mobile/tablet under
				// `.cb-row--mobile`. Per-device CSS therefore scopes via
				// these wrapper classes instead of media queries.
				'device_scope'       => array(
					'desktop' => '.cb-row--desktop',
					'mobile'  => '.cb-row--mobile',
				),
				'selector'           => '.header--row.' . str_replace( '_', '-', $section ),
				'css_format'         => 'columns_settings',
				'sanitize_callback'  => 'customify_sanitize_columns_settings',
			),

		);

		return $config;

	}

	function row_sidebar_config( $section, $section_name ) {
		$selector = '#header-menu-sidebar-bg';

		$config = array(
			array(
				'name'            => $section,
				'type'            => 'section',
				'panel'           => 'header_settings',
				'theme_supports'  => '',
				'title'           => $section_name,
			),

			array(
				'name'            => $section . '_animate',
				'type'            => 'select',
				'section'         => $section,
				'selector'        => 'body',
				'render_callback' => 'customify_customize_render_header',
				'css_format'      => 'html_class',
				'title'           => __( 'Display Type', 'customify' ),
				'default'         => 'menu_sidebar_slide_left',
				'choices'         => array(
					'menu_sidebar_slide_left'    => __( 'Slide From Left', 'customify' ),
					'menu_sidebar_slide_right'   => __( 'Slide From Right', 'customify' ),
					'menu_sidebar_slide_overlay' => __( 'Full-screen Overlay', 'customify' ),
					'menu_sidebar_dropdown'      => __( 'Toggle Dropdown', 'customify' ),
				),
			),

			array(
				'name'       => $section . '_skin_mode',
				'type'       => 'image_select',
				'section'    => $section,
				'selector'   => '#header-menu-sidebar, .close-sidebar-panel',
				'css_format' => 'html_class',
				'title'      => __( 'Skin Mode', 'customify' ),
				'default'    => 'dark-mode',
				'choices'    => array(
					'dark-mode'  => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/build/images/customizer/text_mode_light.svg',
						'label' => 'Dark',
					),
					'light-mode' => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/build/images/customizer/text_mode_dark.svg',
						'label' => 'Light',
					),
				),
			),

			array(
				'name'             => $section . '_styling',
				'type'             => 'styling',
				'section'          => $section,
				'title'            => __( 'Styling', 'customify' ),
				'description'      => sprintf( __( 'Advanced styling for %s', 'customify' ), $section_name ),
				'live_title_field' => 'title',
				'selector'         => array(
					'normal'               => $selector,
					'normal_link_color'    => "{$selector} .menu li a, {$selector} .item--html a, {$selector} .cart-item-link, {$selector} .nav-toggle-icon",
					'hover_link_color'     => "{$selector} .menu li a:hover, {$selector} .item--html a:hover, {$selector} .cart-item-link:hover, {$selector} li.open-sub .nav-toggle-icon",
					'normal_bg_color'      => '#header-menu-sidebar-bg:before',
					'normal_bg_image'      => '#header-menu-sidebar-bg:before',
					'normal_bg_attachment' => '#header-menu-sidebar-bg:before',
					'normal_bg_cover'      => '#header-menu-sidebar-bg:before',
					'normal_bg_repeat'     => '#header-menu-sidebar-bg:before',
					'normal_bg_position'   => '#header-menu-sidebar-bg:before',
					'normal_box_shadow'    => '#header-menu-sidebar',
				),
				'css_format'       => 'styling', // styling.
				'fields'           => array(
					'normal_fields' => array(
						'border_color'  => false,
						'border_radius' => false,
						'border_width'  => false,
						'border_style'  => false,
					),
					'hover_fields'  => array(
						'text_color'     => false,
						'padding'        => false,
						'bg_color'       => false,
						'bg_heading'     => false,
						'bg_cover'       => false,
						'bg_image'       => false,
						'bg_repeat'      => false,
						'border_heading' => false,
						'border_color'   => false,
						'border_radius'  => false,
						'border_width'   => false,
						'border_style'   => false,
						'box_shadow'     => false,
					), // disable hover tab and all fields inside.
				),
			),

			array(
				'name'           => $section . '_menu_no_duplicator',
				'type'           => 'checkbox',
				'section'        => $section,
				'selector'       => '.sub-menu .li-duplicator',
				'css_format'     => 'display:none !important;',
				'checkbox_label' => __( 'Do not copy parent menu to submenu.', 'customify' ),
				'default'        => 1,
			),

			array(
				'name'            => $section . '_align',
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'priority'        => 820,
				'device_settings' => true,
				'selector'        => '.header-menu-sidebar-inner',
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Align', 'customify' ),
			),

			array(
				'name'               => $section . '_columns_settings',
				'type'               => 'columns_settings',
				'section'            => $section,
				'priority'           => 999,
				'title'              => __( 'Off-canvas Settings', 'customify' ),
				'description'        => __( 'Align, gap and padding for the off-canvas.', 'customify' ),
				'col_layout_setting' => '',
				'column_keys'        => array( 'sidebar' ),
				// Direction is locked to `column` (sidebar always stacks
				// vertically). Align is user-pickable so they can left /
				// center / right align item content inside the off-canvas.
				'hide_direction'     => true,
				'forced_direction'   => 'column',
				'col_selectors'      => array(
					'sidebar' => '#header-menu-sidebar-inner',
				),
				'selector'           => '#header-menu-sidebar-inner',
				'css_format'         => 'columns_settings',
				'sanitize_callback'  => 'customify_sanitize_columns_settings',
			),

		);

		return $config;
	}

}

if ( ! function_exists( 'customify_header_layout_settings' ) ) {
	function customify_header_layout_settings( $item_id = '', $section = '', $cb = '', $name_prefix = 'header_' ) {

		if ( ! $cb ) {
			$cb = 'customify_customize_render_header';
		}

		$class    = '.header--row';
		$selector = '#masthead';
		if ( ! $name_prefix ) {
			$name_prefix = 'header_';
		} else {
			if ( strpos( $item_id, 'footer' ) !== false ) {
				$class       = '.footer--row';
				$name_prefix = 'footer_';
				$cb          = 'customify_customize_render_footer';
			}
		}

		$layout = array(
			array(
				'name'     => $name_prefix . $item_id . '_l_heading',
				'type'     => 'heading',
				'priority' => 800,
				'section'  => $section,
				'title'    => __( 'Item Layout', 'customify' ),
			),

			array(
				'name'            => $name_prefix . $item_id . '_margin',
				'type'            => 'css_ruler',
				'priority'        => 810,
				'section'         => $section,
				'device_settings' => true,
				'css_format'      => array(
					'top'    => 'margin-top: {{value}};',
					'right'  => 'margin-right: {{value}};',
					'bottom' => 'margin-bottom: {{value}};',
					'left'   => 'margin-left: {{value}};',
				),
				'selector'        => "{$class} .builder-item--{$item_id}, .builder-item.builder-item--group .item--inner.builder-item--{$item_id}",
				'label'           => __( 'Margin', 'customify' ),
			),

			/**
			 * Apply for version 1 only
			 *
			 * @since 0.2.9
			 */
			array(
				'name'            => $name_prefix . $item_id . '_align',
				'type'            => 'text_align_no_justify',
				'section'         => $section,
				'priority'        => 820,
				'device_settings' => true,
				'selector'        => "{$class} .builder-first--" . $item_id,
				'css_format'      => 'text-align: {{value}};',
				'title'           => __( 'Align', 'customify' ),
				'required' => array( 'header_builder_version', '!=', 'v2' ),
			),

			/**
			 * Apply for version 1 only
			 *
			 * @since 0.2.9
			 */
			array(
				'name'            => $name_prefix . $item_id . '_merge',
				'type'            => 'select',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $cb,
				'priority'        => 999,
				'device_settings' => true,
				'devices'         => array( 'desktop', 'mobile' ),
				'title'           => __( 'Merge Item', 'customify' ),
				'description'     => __( 'If you choose to merge this item, the alignment setting will inherit from the item you are merging.', 'customify' ),
				'choices'         => array(
					0      => __( 'No', 'customify' ),
					'prev' => __( 'Merge with left item', 'customify' ),
					'next' => __( 'Merge with right item', 'customify' ),
				),
				'required' => array( 'header_builder_version', '!=', 'v2' ),
			),
		);

		return $layout;
	}
}

Customify_Customize_Layout_Builder()->register_builder( 'header', new Customify_Builder_Header() );

/**
 * Check whether a specific item ID is present anywhere in the v2 header builder layout.
 *
 * Used as active_callback for element sections so they only appear in the
 * Customizer panel when the element has been placed in the header.
 *
 * @param string $item_id Builder item ID (e.g. 'logo', 'primary-menu').
 * @return bool
 */
function customify_header_builder_has_item( $item_id ) {
	$raw = get_theme_mod( 'header_builder_panel_v2', '' );
	if ( ! $raw ) {
		return false;
	}

	// The sanitize callback stores the value as a decoded PHP array.
	// Fall back to JSON-decoding if it somehow arrives as a string.
	if ( is_array( $raw ) ) {
		$data = $raw;
	} else {
		$data = json_decode( urldecode( (string) $raw ), true );
	}

	if ( ! is_array( $data ) ) {
		return false;
	}

	foreach ( $data as $device_data ) {
		if ( ! is_array( $device_data ) ) {
			continue;
		}
		foreach ( $device_data as $row_data ) {
			if ( ! is_array( $row_data ) ) {
				continue;
			}
			foreach ( $row_data as $col_items ) {
				if ( ! is_array( $col_items ) ) {
					continue;
				}
				foreach ( $col_items as $item ) {
					if ( isset( $item['id'] ) && $item['id'] === $item_id ) {
						return true;
					}
				}
			}
		}
	}

	return false;
}

/**
 * Register the columns_settings control type so load_controls() includes
 * the class file. The footer panel still registers `row_layout` for its
 * own Columns Layout picker; header rows no longer expose that picker.
 */
add_filter(
	'customify/customize/register-controls',
	function ( $fields ) {
		if ( ! in_array( 'columns_settings', $fields, true ) ) {
			$fields[] = 'columns_settings';
		}
		return $fields;
	}
);

/**
 * Sanitize callback for columns_settings fields.
 *
 * The canonical saved shape is:
 *   { desktop: { <colKey>: { direction, align, gap, padding }, ... },
 *     mobile:  { <colKey>: { ... }, ... } }
 *
 * Unknown top-level keys (anything other than desktop/mobile), unknown
 * per-column keys (anything other than direction/align/gap/padding), and
 * out-of-range enum values are stripped — keeps the stored shape narrow
 * and forward-compatible.
 *
 * Customify's generic sanitize_customizer_input() would otherwise flatten
 * the desktop/mobile envelope away (see class-customizer-sanitize.php:236
 * — it drops a `desktop` key when the field has device_settings=false).
 * Routing through this dedicated callback preserves both buckets.
 */
if ( ! function_exists( 'customify_sanitize_columns_settings' ) ) {
	function customify_sanitize_columns_settings( $input ) {
		if ( is_string( $input ) ) {
			$input = json_decode( urldecode_deep( wp_unslash( $input ) ), true );
		}
		if ( ! is_array( $input ) ) {
			return array();
		}

		$allowed_directions = array( 'row', 'column' );
		$allowed_aligns     = array( 'flex-start', 'flex-center', 'flex-end', 'space-between' );
		$allowed_units      = array( 'em', 'px' );
		$out                = array();

		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			if ( ! isset( $input[ $device ] ) || ! is_array( $input[ $device ] ) ) {
				continue;
			}
			$device_out = array();
			foreach ( $input[ $device ] as $col_key => $col_value ) {
				if ( ! is_array( $col_value ) ) {
					continue;
				}
				$col_out = array();

				// Direction.
				if ( isset( $col_value['direction'] ) && in_array( $col_value['direction'], $allowed_directions, true ) ) {
					$col_out['direction'] = $col_value['direction'];
				}

				// Align.
				if ( isset( $col_value['align'] ) && in_array( $col_value['align'], $allowed_aligns, true ) ) {
					$col_out['align'] = $col_value['align'];
				}

				// Gap — { unit, value }.
				if ( isset( $col_value['gap'] ) && is_array( $col_value['gap'] ) ) {
					$gap   = array();
					$gap_v = $col_value['gap']['value'] ?? '';
					$gap_u = $col_value['gap']['unit']  ?? 'em';
					if ( '' !== $gap_v && null !== $gap_v && is_numeric( $gap_v ) ) {
						$gap['value'] = floatval( $gap_v );
						$gap['unit']  = in_array( $gap_u, $allowed_units, true ) ? $gap_u : 'em';
					}
					if ( ! empty( $gap ) ) {
						$col_out['gap'] = $gap;
					}
				}

				// Padding — { top, right, bottom, left, unit, link }.
				if ( isset( $col_value['padding'] ) && is_array( $col_value['padding'] ) ) {
					$pad      = array();
					$pad_unit = $col_value['padding']['unit'] ?? 'em';
					$pad_link = isset( $col_value['padding']['link'] ) ? intval( $col_value['padding']['link'] ) : 0;
					foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
						if ( ! isset( $col_value['padding'][ $side ] ) ) {
							continue;
						}
						$v = $col_value['padding'][ $side ];
						if ( '' === $v || null === $v ) {
							$pad[ $side ] = '';
							continue;
						}
						if ( is_numeric( $v ) ) {
							$pad[ $side ] = floatval( $v );
						}
					}
					if ( ! empty( $pad ) ) {
						$pad['unit'] = in_array( $pad_unit, $allowed_units, true ) ? $pad_unit : 'em';
						$pad['link'] = $pad_link ? 1 : 0;
						$col_out['padding'] = $pad;
					}
				}

				if ( ! empty( $col_out ) ) {
					$device_out[ sanitize_key( $col_key ) ] = $col_out;
				}
			}
			if ( ! empty( $device_out ) ) {
				$out[ $device ] = $device_out;
			}
		}

		return $out;
	}
}
