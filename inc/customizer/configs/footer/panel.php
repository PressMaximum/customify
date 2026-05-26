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
			'title'      => __( 'Footer Layout', 'customify' ),
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

		// Text skin — unified `dark-mode` default across all footer rows
		// (top / main / bottom) so the Pro-registered Footer Top inherits
		// the same background appearance as Footer Main out of the box.
		$color_mode = 'dark-mode';

		$selector           = '#cb-row--' . str_replace( '_', '-', $section );
		$skin_mode_selector = '.footer--row-inner.' . str_replace( '_', '-', $section ) . '-inner';

		$fn = 'customify_customize_render_footer';

		// Explicit per-section priorities so the Customizer sidebar always
		// renders footer rows in Top → Main → Bottom order regardless of
		// what order rows are registered. WP Customizer sorts sections
		// inside a panel by ascending `priority`. The companion sort
		// filter on `customify/builder/footer/rows` (below) also enforces
		// array-key order so the React canvas matches.
		$section_priorities = array(
			'footer_top'    => 10,
			'footer_main'   => 20,
			'footer_bottom' => 30,
		);
		$section_priority   = isset( $section_priorities[ $section ] ) ? $section_priorities[ $section ] : 50;

		$config = array(
			array(
				'name'           => $section,
				'type'           => 'section',
				'panel'          => 'footer_settings',
				'theme_supports' => '',
				'title'          => $section_name,
				'priority'       => $section_priority,
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

			array(
				'name'        => "{$section}_col_gap",
				'type'        => 'slider',
				'section'     => $section,
				'title'       => __( 'Columns Gap', 'customify' ),
				'description' => __( 'Default gap between columns. Per-column gap in Column Settings overrides this.', 'customify' ),
				'selector'    => $selector . ' .row-v2, ' . $selector . ' .col-v2',
				'css_format'  => 'column-gap: {{value}}; gap: {{value}}',
				'min'         => 0,
				'max'         => 100,
				'default'     => 30,
			),

			array(
				'name'               => "{$section}_columns_settings",
				'type'               => 'columns_settings',
				'section'            => $section,
				'priority'           => 999,
				'title'              => __( 'Column Settings', 'customify' ),
				'description'        => __( 'Per-column direction, align, gap and padding.', 'customify' ),
				'col_layout_setting' => $section . '_col_layout',
				'column_keys'        => array( 'left', 'center', 'right', 'col4', 'col5' ),
				// Footer widgets typically stack vertically inside each column,
				// so the default per-column direction is `column`. Users can
				// still switch any column to row direction.
				'default_direction'  => 'column',
				// Footer columns default to `flex-start` for every slot so the
				// content of each widget aligns to the leading edge by default.
				// Header rows intentionally omit `default_align` to keep the
				// position-based default (first → flex-start, last → flex-end,
				// middle → flex-center) that gives the masthead its natural
				// balanced look.
				'default_align'      => 'flex-start',
				// Footer renders one shared row grid (unlike the header
				// which double-renders markup per device). Even so, each
				// breakpoint may want a distinct flex layout / gap /
				// padding tweak — expose desktop / tablet / mobile as
				// separate buckets so users can tune each independently.
				'devices'            => array( 'desktop', 'tablet', 'mobile' ),
				'selector'           => $selector,
				'css_format'         => 'columns_settings',
				'sanitize_callback'  => 'customify_sanitize_columns_settings',
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
 * Enforce footer row ordering: Top → Main → Bottom.
 *
 * The theme's `get_rows_config()` declares only `main` and `bottom`.
 * Customify Pro hooks `customify/builder/footer/rows` to append a `top`
 * row, which would otherwise land at the end of the array (display
 * order would become Main → Bottom → Top). This filter runs at
 * `PHP_INT_MAX` so it executes AFTER any extension (including Pro)
 * has registered its rows, then reorders them. Unknown row keys are
 * preserved at the end so future extensions don't get dropped.
 */
add_filter(
	'customify/builder/footer/rows',
	function ( $rows ) {
		if ( ! is_array( $rows ) ) {
			return $rows;
		}
		$desired = array( 'top', 'main', 'bottom' );
		$sorted  = array();
		foreach ( $desired as $key ) {
			if ( isset( $rows[ $key ] ) ) {
				$sorted[ $key ] = $rows[ $key ];
				unset( $rows[ $key ] );
			}
		}
		return $sorted + $rows;
	},
	PHP_INT_MAX
);

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

		// Normalize fr length to the row's column count. Legacy data from
		// before RowLayout.jsx synced all devices on count change can have
		// per-device fr arrays longer (or shorter) than count, which would
		// emit extra empty grid tracks (the "col4 still renders after 4→3"
		// bug). Clamp count to the 1–5 range that matches get_footer_col_keys().
		$count = isset( $data['count'] ) ? max( 1, min( 5, intval( $data['count'] ) ) ) : 0;

		// Hide column placeholders beyond the active count. Server-side
		// `render_row()` already skips emitting non-active cols, but in
		// customizer live preview the HTML is the snapshot rendered with
		// the previous count — only CSS reacts to `postMessage` changes.
		// Without this rule, col4/col5 from the stale HTML wrap onto a new
		// grid row below when grid-template-columns shrinks.
		$all_col_slots = array( 'left', 'center', 'right', 'col4', 'col5' );
		if ( $count > 0 && $count < count( $all_col_slots ) ) {
			$hide_selectors = array();
			foreach ( array_slice( $all_col_slots, $count ) as $hide_col ) {
				$hide_selectors[] = $selector . ' .col-v2-' . $hide_col;
			}
			$css .= implode( ', ', $hide_selectors ) . ' { display: none !important; } ';
		}

		foreach ( $devices as $device => $media ) {
			if ( ! isset( $data[ $device ]['fr'] ) || ! is_array( $data[ $device ]['fr'] ) || empty( $data[ $device ]['fr'] ) ) {
				// Mobile fallback: emit a stacked rule so phones don't inherit
				// the tablet/desktop multi-column track when the user never
				// configured mobile. Fires ONLY for missing / null / empty
				// data — any explicit array (even [1,1,1,1] that matches
				// desktop) is treated as the user's choice and rendered below.
				if ( 'mobile' === $device ) {
					$rules = $selector . ' .row-v2 { display: grid !important; grid-template-columns: 1fr; }';
					$css  .= $media . ' { ' . $rules . ' } ';
				}
				continue;
			}

			$device_data = $data[ $device ];

			$fr_len = count( $device_data['fr'] );

			// Preserve fr as-is when it represents an intentional layout:
			//   length 1            = stacked preset (one track, items stack)
			//   length == count     = normal one-row layout
			//   length divides count = wrap preset (e.g. fr=[1,1] with count=4
			//                          → 2×2 grid via grid auto-flow)
			//   mobile device       = respect user's explicit mobile choice
			// Other lengths are legacy stale data — slice/pad to count.
			$is_intentional = $fr_len === 1
				|| $fr_len === $count
				|| ( $fr_len > 1 && $fr_len < $count && $count % $fr_len === 0 );

			if ( $is_intentional || 'mobile' === $device ) {
				$fr = $device_data['fr'];
			} else {
				$fr = $count > 0 ? array_slice( $device_data['fr'], 0, $count ) : $device_data['fr'];
				if ( $count > 0 ) {
					while ( count( $fr ) < $count ) {
						$fr[] = 1;
					}
				}
			}

			$fr_parts  = array_map(
				function ( $v ) { return absint( $v ) . 'fr'; },
				$fr
			);
			$grid_cols = implode( ' ', $fr_parts );

			$rules = $selector . ' .row-v2 { display: grid !important; grid-template-columns: ' . $grid_cols . '; }';
			$css  .= $media ? $media . ' { ' . $rules . ' } ' : $rules . ' ';
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



