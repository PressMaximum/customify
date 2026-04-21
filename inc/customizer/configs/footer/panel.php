<?php

/**
 * Sanitize callback for row_layout settings.
 * The generic sanitize_customizer_input strips keys when it sees a 'desktop' key
 * on controls without device_settings=true, so we use a dedicated callback.
 */
function customify_sanitize_row_layout( $input ) {
	if ( is_string( $input ) ) {
		$input = json_decode( wp_unslash( urldecode( $input ) ), true );
	}
	return is_array( $input ) ? $input : array();
}

class Customify_Builder_Footer extends Customify_Customize_Builder_Panel {
	public $id = 'footer';

	function get_config() {
		return array(
			'id'         => $this->id,
			'title'      => __( 'Footer Builder', 'customify' ),
			'control_id' => 'footer_builder_panel',
			'panel'      => 'footer_settings',
			'section'    => 'footer_builder_panel',
			'devices'               => array(
				'desktop' => __( 'Footer Layout', 'customify' ),
			),
			'react_control_id'      => 'footer_builder_panel_v2',
			'panel_items_container' => 'customify-fb-panel-items',
			'row_layout_keys'       => array(
				'main'   => 'footer_main_col_layout',
				'bottom' => 'footer_bottom_col_layout',
			),
		);
	}

	function get_rows_config() {
		return array(
			'main'   => __( 'Footer Main', 'customify' ),
			'bottom' => __( 'Footer Bottom', 'customify' ),
		);
	}

	function customize() {
		$fn     = 'customify_customize_render_footer';
		$config = array(
			array(
				'name'     => 'footer_settings',
				'type'     => 'panel',
				'priority' => 98,
				'title'    => __( 'Footer', 'customify' ),
			),

			array(
				'name'  => 'footer_builder_panel',
				'type'  => 'section',
				'panel' => 'footer_settings',
				'title' => __( 'Footer Builder', 'customify' ),
			),

			array(
				'name'                => 'footer_builder_panel',
				'type'                => 'js_raw',
				'section'             => 'footer_builder_panel',
				'theme_supports'      => '',
				'title'               => __( 'Footer Builder', 'customify' ),
				'selector'            => '#site-footer',
				'render_callback'     => $fn,
				'container_inclusive' => true,
			),

			// V2 layout data — written and read by the React footer builder.
			array(
				'name'                => 'footer_builder_panel_v2',
				'type'                => 'js_raw',
				'section'             => 'footer_builder_panel',
				'theme_supports'      => '',
				'title'               => '',
				'selector'            => '#site-footer',
				'render_callback'     => $fn,
				'container_inclusive' => true,
			),

		);

		return $config;
	}

	function row_config( $section = false, $section_name = false ) {

		if ( ! $section ) {
			$section = 'footer_top';
		}
		if ( ! $section_name ) {
			$section_name = __( 'Footer Top', 'customify' );
		}

		// Text skin.
		$color_mode = 'dark-mode';
		if ( 'footer_top' == $section ) {
			$color_mode = 'light-mode';
		}

		$selector           = '#cb-row--' . str_replace( '_', '-', $section );
		$skin_mode_selector = '.footer--row-inner.' . str_replace( '_', '-', $section ) . '-inner';

		$fn = 'customify_customize_render_footer';

		$config = array(
			array(
				'name'           => $section,
				'type'           => 'section',
				'panel'          => 'footer_settings',
				'theme_supports' => '',
				'title'          => $section_name,
			),

			array(
				'name'            => $section . '_layout',
				'type'            => 'select',
				'section'         => $section,
				'title'           => __( 'Layout', 'customify' ),
				'selector'        => $selector,
				'render_callback' => $fn,
				'css_format'      => 'html_class',
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
				'name'       => $section . '_text_mode',
				'type'       => 'image_select',
				'section'    => $section,
				'selector'   => $skin_mode_selector,
				'css_format' => 'html_class',
				'title'      => __( 'Skin Mode', 'customify' ),
				'default'    => $color_mode,
				'choices'    => array(
					'dark-mode'  => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/text_mode_light.svg',
						'label' => 'Dark',
					),
					'light-mode' => array(
						'img'   => esc_url( get_template_directory_uri() ) . '/assets/images/customizer/text_mode_dark.svg',
						'label' => 'Light',
					),
				),
			),

			array(
				'name'       => "{$section}_background_color",
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Background Color', 'customify' ),
				'selector'   => "{$selector} .footer--row-inner",
				'css_format' => 'background-color: {{value}}',
			),

			array(
				'name'              => "{$section}_col_layout",
				'type'              => 'row_layout',
				'section'           => $section,
				'title'             => __( 'Columns Layout', 'customify' ),
				'sanitize_callback' => 'customify_sanitize_row_layout',
			),
		);
		$config = apply_filters( 'customify/builder/' . $this->id . '/rows/section_configs', $config, $section, $section_name );
		return $config;
	}
}

function customify_footer_layout_settings( $item_id, $section ) {

	global $wp_customize;

	if ( is_object( $wp_customize ) ) {
		global $wp_registered_sidebars;
		$name = $section;
		if ( is_array( $wp_registered_sidebars ) ) {
			if ( isset( $wp_registered_sidebars[ $item_id ] ) ) {
				$name = $wp_registered_sidebars[ $item_id ]['name'];
			}
		}
		// Only create the section if WP hasn't already registered it as a sidebar
		// widget section (WP_Customize_Sidebar_Section). Calling add_section() on
		// an existing sidebar section replaces it with a plain section, which
		// destroys the widget management UI.
		if ( ! $wp_customize->get_section( $section ) ) {
			$wp_customize->add_section(
				$section,
				array(
					'title' => $name,
				)
			);
		}
	}

	if ( function_exists( 'customify_header_layout_settings' ) ) {
		return customify_header_layout_settings( $item_id, $section, 'customify_customize_render_footer', 'footer_' );
	}

	return false;
}

Customify_Customize_Layout_Builder()->register_builder( 'footer', new Customify_Builder_Footer() );

/**
 * Register the row_layout control type so load_controls() includes the class file.
 */
add_filter(
	'customify/customize/register-controls',
	function ( $fields ) {
		$fields[] = 'row_layout';
		return $fields;
	}
);

/**
 * Force postMessage transport for footer row col_layout settings so that the
 * preview JS binding (in customizer.js) can apply grid-template-columns live.
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		foreach ( array( 'footer_main_col_layout', 'footer_bottom_col_layout' ) as $key ) {
			$setting = $wp_customize->get_setting( $key );
			if ( $setting ) {
				$setting->transport = 'postMessage';
			}
		}
	},
	700
);

/**
 * Output grid-template-columns CSS for footer rows based on saved col_layout values.
 */
function customify_footer_row_layout_css() {
	$rows = array(
		'footer_main'   => '#cb-row--footer-main',
		'footer_bottom' => '#cb-row--footer-bottom',
	);

	$css = '';
	foreach ( $rows as $key => $selector ) {
		$raw = Customify()->get_setting( $key . '_col_layout' );
		if ( ! $raw ) {
			continue;
		}
		$data = is_array( $raw ) ? $raw : json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			continue;
		}

		$devices = array(
			'desktop' => '',
			'tablet'  => '@media (max-width: 1024px)',
			'mobile'  => '@media (max-width: 767px)',
		);

		foreach ( $devices as $device => $media ) {
			if ( empty( $data[ $device ]['fr'] ) || ! is_array( $data[ $device ]['fr'] ) ) {
				continue;
			}
			$device_data = $data[ $device ];
			$fr_parts    = array_map(
				function ( $v ) { return absint( $v ) . 'fr'; },
				$device_data['fr']
			);
			$grid_cols = implode( ' ', $fr_parts );
			$gap       = isset( $device_data['gap'] ) ? absint( $device_data['gap'] ) : 0;
			$padding   = isset( $device_data['padding'] ) ? absint( $device_data['padding'] ) : 0;

			$rules  = $selector . ' .row-v2 { display: grid !important; grid-template-columns: ' . $grid_cols . '; column-gap: ' . $gap . 'px; }';
			$rules .= ' ' . $selector . ' .col-v2 { padding-left: ' . $padding . 'px; padding-right: ' . $padding . 'px; }';
			$css   .= $media ? $media . ' { ' . $rules . ' } ' : $rules . ' ';
		}
	}

	if ( $css ) {
		echo '<style id="customify-footer-col-layout">' . $css . "</style>\n";
	}
}
add_action( 'wp_head', 'customify_footer_row_layout_css', 99 );

/**
 * Check whether a specific item ID is present anywhere in the v2 footer builder layout.
 *
 * @param string $item_id Builder item ID (e.g. 'footer-1').
 * @return bool
 */
function customify_footer_builder_has_item( $item_id ) {
	$raw = get_theme_mod( 'footer_builder_panel_v2', '' );
	if ( ! $raw ) {
		return false;
	}

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
 * Register a dedicated layout-settings section for a footer sidebar item.
 * This avoids touching the WP widget section (sidebar-widgets-footer-N) so
 * the widget management UI remains intact.
 *
 * @param string $item_id  Sidebar ID, e.g. 'footer-1'.
 * @param string $layout_section  New section slug, e.g. 'footer_sidebar_1_layout'.
 * @return array  Customizer field configs (margin, padding, etc.).
 */
function customify_footer_sidebar_layout_settings( $item_id, $layout_section ) {
	global $wp_customize;

	if ( is_object( $wp_customize ) && ! $wp_customize->get_section( $layout_section ) ) {
		global $wp_registered_sidebars;
		$title = isset( $wp_registered_sidebars[ $item_id ] )
			? $wp_registered_sidebars[ $item_id ]['name'] . ' — ' . __( 'Layout', 'customify' )
			: ucwords( str_replace( '-', ' ', $item_id ) ) . ' Layout';

		$wp_customize->add_section(
			$layout_section,
			array(
				'title' => $title,
				'panel' => 'footer_settings',
			)
		);
	}

	if ( function_exists( 'customify_header_layout_settings' ) ) {
		return customify_header_layout_settings( $item_id, $layout_section, 'customify_customize_render_footer', 'footer_' );
	}

	return array();
}



