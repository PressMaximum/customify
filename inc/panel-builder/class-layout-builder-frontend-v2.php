<?php
/**
 * Builder frontemd class
 *
 * @since 0.2.9
 */
class Customify_Layout_Builder_Frontend_V2  extends Customify_Abstract_Layout_Frontend {
	public static $_instance;
	protected $control_id = 'header_builder_panel_v2';
	public $id = 'header';
	protected $render_items = null;
	protected $rows = array();
	protected $flag_cols = array();
	protected $flag_rows = array();
	protected $data = false;
	protected $config_items = false;

	public function __construct() {

	}

	public function set_id( $id ) {
		parent::set_id( $id );
		$this->reset_render_cache();
	}

	public function set_control_id( $id ) {
		parent::set_control_id( $id );
		$this->reset_render_cache();
	}

	protected function reset_render_cache() {
		$this->render_items = null;
		$this->rows         = array();
		$this->flag_cols    = array();
		$this->flag_rows    = array();
	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Override the abstract get_settings() to handle the V2 storage format
	 * (URL-encoded JSON written by the React builder) and to transparently
	 * migrate V1 layout data when no V2 data has been saved yet.
	 *
	 * Priority order:
	 *   1. Explicitly saved V2 data  (decoded from URL-encoded JSON or plain array)
	 *   2. V1 data migrated to V2 column structure
	 *   3. Configured default (from config-default.php via Customify()->get_setting)
	 */
	function get_settings() {
		if ( $this->data ) {
			return $this->data;
		}

		// Use get_theme_mod without a default so we can distinguish "never saved"
		// from "saved as the default value".
		$saved = get_theme_mod( $this->control_id );

		if ( ! empty( $saved ) ) {
			$data = $this->parse_raw_setting( $saved );
		} else {
			// No V2 data saved — try migrating from V1.
			$data = $this->migrate_v1_data();

			if ( empty( $data ) ) {
				// Nothing in V1 either — fall back to the configured default.
				$data = $this->parse_raw_setting(
					Customify()->get_setting( $this->control_id )
				);
			}
		}

		// Run the loaded data through the canonical-shape normalizer before
		// handing it to anything else. Every downstream consumer (render_items,
		// get_render_item, …) can then assume:
		//   - top level is array with only 'desktop' / 'mobile' keys
		//   - each row is array, each col is array of `{id: non-empty string}`
		// Stray keys (extension metadata, test markers, schema-version stamps)
		// and malformed entries are dropped here — once.
		$this->data = self::normalize_layout_data( $data );
		return $this->data;
	}

	/**
	 * Coerce arbitrary layout data into the canonical V2 shape.
	 *
	 * Canonical shape:
	 * {
	 *   desktop: { <row>: { left:[{id}], center:[{id}], right:[{id}], col4:[{id}], col5:[{id}] } },
	 *   mobile:  { <row>: { … }, sidebar: { sidebar: [{id}] } }
	 * }
	 *
	 * Anything that doesn't fit this shape is dropped silently. The function
	 * is intentionally permissive so legacy / extension data never produces
	 * a PHP warning or fatal at render time — `render_items()` already has
	 * defensive guards but a single normalize call here keeps the rest of
	 * the codebase free of repeated `is_array` checks.
	 *
	 * @param mixed $data
	 * @return array
	 */
	public static function normalize_layout_data( $data ) {
		$valid_devices = array( 'desktop', 'mobile' );
		$valid_cols    = array( 'left', 'center', 'right', 'col4', 'col5' );
		$out           = array();

		if ( ! is_array( $data ) ) {
			return $out;
		}

		foreach ( $valid_devices as $device ) {
			$out[ $device ] = array();
			if ( ! isset( $data[ $device ] ) || ! is_array( $data[ $device ] ) ) {
				continue;
			}
			foreach ( $data[ $device ] as $row_id => $row_cols ) {
				// Row id must be a non-empty string and the value must be an
				// array of columns. Sidebar (mobile only) is shaped differently
				// — handle it below.
				if ( ! is_string( $row_id ) || '' === $row_id || ! is_array( $row_cols ) ) {
					continue;
				}
				if ( 'sidebar' === $row_id && 'mobile' === $device ) {
					$sidebar_items = isset( $row_cols['sidebar'] ) && is_array( $row_cols['sidebar'] )
						? $row_cols['sidebar']
						: array();
					$out[ $device ]['sidebar'] = array(
						'sidebar' => self::normalize_layout_items( $sidebar_items ),
					);
					continue;
				}
				$normalized_row = array();
				foreach ( $valid_cols as $col_id ) {
					$col_items                = isset( $row_cols[ $col_id ] ) && is_array( $row_cols[ $col_id ] )
						? $row_cols[ $col_id ]
						: array();
					$normalized_row[ $col_id ] = self::normalize_layout_items( $col_items );
				}
				$out[ $device ][ $row_id ] = $normalized_row;
			}
		}

		return $out;
	}

	/**
	 * Strip an items array down to entries with a non-empty string `id`.
	 *
	 * @param array $items
	 * @return array
	 */
	private static function normalize_layout_items( $items ) {
		$clean = array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || ! is_string( $item['id'] ) ) {
				continue;
			}
			// Drop any keys we don't expect on an item entry. The current
			// schema only uses `id`; keeping the entry as `['id' => ...]`
			// guarantees a single shape across the codebase.
			$clean[] = array( 'id' => $item['id'] );
		}
		return $clean;
	}

	/**
	 * Decode a raw setting value into a PHP array.
	 * Handles: PHP arrays, URL-encoded JSON strings, plain JSON strings.
	 *
	 * @param mixed $raw
	 * @return array
	 */
	private function parse_raw_setting( $raw ) {
		if ( empty( $raw ) ) {
			return array();
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		$decoded = json_decode( urldecode( (string) $raw ), true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}
		$decoded = json_decode( (string) $raw, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Convert saved V1 builder data into the V2 column-based structure.
	 *
	 * V1 format: { device: { row: [ {id, x, width, ...}, ... ] } }
	 * V2 format: { device: { row: { left:[{id}], center:[{id}], right:[{id}], col4:[{id}], col5:[{id}] } } }
	 *
	 * Mapping rules (after sorting items by x):
	 *   - 1 item  → center
	 *   - 2 items → left, right
	 *   - 3+ items → left, center, right, col4, col5  (in order, max 5)
	 *
	 * The special "sidebar" row (header mobile) is converted to the flat
	 * V2 sidebar structure: { sidebar: [ {id}, ... ] }
	 *
	 * @return array  V2-format data, or empty array when no V1 data exists.
	 */
	private function migrate_v1_data() {
		$v1_control = $this->id . '_builder_panel';
		$v1_raw     = get_theme_mod( $v1_control );

		if ( ! is_array( $v1_raw ) || empty( $v1_raw ) ) {
			return array();
		}

		$all_cols = array( 'left', 'center', 'right', 'col4', 'col5' );
		$v2       = array();

		foreach ( $v1_raw as $device => $device_rows ) {
			if ( ! is_array( $device_rows ) ) {
				continue;
			}
			$v2[ $device ] = array();

			foreach ( $device_rows as $row_id => $row_items ) {
				if ( ! is_array( $row_items ) ) {
					continue;
				}

				// Header mobile sidebar — flat list becomes V2 sidebar column.
				if ( 'sidebar' === $row_id ) {
					$v2[ $device ]['sidebar'] = array(
						'sidebar' => array_values(
							array_map(
								function ( $item ) {
									return array( 'id' => $item['id'] );
								},
								array_filter( $row_items, function ( $item ) {
									return ! empty( $item['id'] );
								} )
							)
						),
					);
					continue;
				}

				// Filter out items without an id, then sort by x position.
				$items = array_values(
					array_filter( $row_items, function ( $item ) {
						return ! empty( $item['id'] );
					} )
				);
				usort( $items, function ( $a, $b ) {
					return (int) ( $a['x'] ?? 0 ) - (int) ( $b['x'] ?? 0 );
				} );

				$v2_row = array_fill_keys( $all_cols, array() );
				$count  = count( $items );

				if ( 1 === $count ) {
					$v2_row['center'] = array( array( 'id' => $items[0]['id'] ) );
				} elseif ( 2 === $count ) {
					$v2_row['left']  = array( array( 'id' => $items[0]['id'] ) );
					$v2_row['right'] = array( array( 'id' => $items[1]['id'] ) );
				} else {
					foreach ( $items as $i => $item ) {
						if ( $i < 5 ) {
							$v2_row[ $all_cols[ $i ] ] = array( array( 'id' => $item['id'] ) );
						}
					}
				}

				$v2[ $device ][ $row_id ] = $v2_row;
			}
		}

		return $v2;
	}

	/**
	 * Render builder items.
	 *
	 * @since 0.2.9
	 *
	 * @param string $item_id
	 * @param string $col_id
	 * @param string $row_id
	 * @param string $device
	 * @return array
	 */
	public function render_item( $item_id, $col_id, $row_id, $device ) {

		// $key = $item_id . '_' . $col_id . '_' . $row_id;
		$key = $item_id;
		if ( ! is_array( $this->render_items ) ) {
			$this->render_items = array();
		}

		// Flag to check row has items.
		$this->rows[ $row_id ][ $device ] = $device;

		// Flag to check col has item.
		$flag_key_col = $col_id . '-' . $row_id . '-' . $device;
		$this->flag_cols[ $flag_key_col ] = true;

		// Flag to check row has item.
		$flag_key_row = $row_id . '-' . $device;
		$this->flag_rows[ $flag_key_row ] = true;

		// Check if already render.
		if ( isset( $this->render_items[ $key ] ) ) {
			return $this->render_items[ $key ];
		}

		$item = array(
			'_row_id' => $row_id,
			'_col_id' => $col_id,
			'_id'     => $item_id,
			'_device' => $device,
			'content' => false,
		);

		// START render builder item.
		ob_start();
		$has_cb = false;
		$return_render = false;
		$item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();

		/**
		 * Hook before builder item
		 *
		 * @since 0.2.1
		 */
		do_action( 'customify/builder/' . $this->id . '/before-item/' . $item_id );
		$object_item = Customify_Customize_Layout_Builder()->get_builder_item( $this->id, $item_id );
		// Call render in registered class.
		if ( $object_item ) {
			if ( method_exists( $object_item, 'render' ) ) {
				$return_render = call_user_func_array(
					array(
						$object_item,
						'render',
					),
					array( $item_config, $item_id )
				);
				$has_cb        = true;
			}
		}

		// Call render by function if class do not exists.
		if ( ! $has_cb ) {
			$id            = str_replace( '-', '_', $item_id );
			$fn            = 'customify_builder_' . $id . '_item';

			if ( function_exists( $fn ) ) {
				$return_render = call_user_func_array( $fn, array( $item_config, $item ) );
				$has_cb        = true;
			} else {
				$fn = 'customify_builder_' . $this->id . '_' . $id . '_item';
				if ( function_exists( $fn ) ) {
					$return_render = call_user_func_array( $fn, array( $item_config, $item ) );
					$has_cb        = true;
				}
			}
		}

		/**
		 * Hook after builder item
		 *
		 * @since 0.2.1
		 */
		do_action( 'customify/builder/' . $this->id . '/after-item/' . $item_id );

		// Get item output.
		$ob_render = ob_get_clean();
		// END render builder item.
		if ( ! $return_render ) {
			if ( $ob_render ) {
				$return_render = $ob_render;
			}
		}

		if ( $return_render ) {
			$item ['content'] = $return_render;
		}

		$this->render_items[ $key ] = $item;
		return $item;
	}

	/**
	 * Render items to HTML
	 *
	 * @param array $list_items List Items.
	 *
	 * @return array
	 */
	function render_items( $list_items = array() ) {
		if ( ! is_null( $this->render_items ) ) {
			return $this->render_items;
		}

		$setting = $this->get_settings();
		$items   = array();

		// Loop devices. Each top-level entry is expected to be a device
		// container shaped like `{ row_id: { col_id: [ {id}, … ] } }`. Defend
		// against scalar entries that can sneak in via:
		//   - Stray keys persisted by extensions (e.g. diagnostic markers,
		//     metadata fields written by a custom React panel).
		//   - Settings imported from a different schema version.
		// Skipping non-array entries keeps render_items() crash-safe — without
		// these guards, a single string value at any nesting depth triggers
		// `foreach() argument must be of type array|object`.
		if ( ! is_array( $setting ) ) {
			$setting = array();
		}
		foreach ( $setting as $device => $device_settings ) {
			if ( ! is_array( $device_settings ) ) {
				continue;
			}
			foreach ( $device_settings as $row_id => $row_cols ) {
				if ( ! is_array( $row_cols ) || empty( $row_cols ) ) {
					continue;
				}
				foreach ( $row_cols as $col_id => $col_items ) {
					if ( ! is_array( $col_items ) ) {
						continue;
					}
					foreach ( $col_items as $index => $item ) {
						if ( ! is_array( $item ) || empty( $item['id'] ) ) {
							continue;
						}
						$this->render_item( $item['id'], $col_id, $row_id, $device );
					}
				}
			}
		}

		if ( is_null( $this->render_items ) ) {
			$this->render_items = array();
		}

		return $this->render_items;
	}

	/**
	 * Get rendered item
	 *
	 * @param string $item_id
	 * @return array|bool
	 */
	public function get_render_item( $item_id ) {
		if ( is_null( $this->render_items ) ) {
			$this->render_items();
		}
		if ( isset( $this->render_items[ $item_id ] ) ) {
			return $this->render_items[ $item_id ];
		}
		return false;
	}

	/**
	 * Returns ordered column keys for a footer row based on the col_layout setting.
	 * Returns null when the setting is absent (triggers old column-hiding behaviour).
	 *
	 * @param string $row_id  Row identifier, e.g. 'main' or 'bottom'.
	 * @return array|null
	 */
	protected function get_footer_col_keys( $row_id ) {
		static $all_cols = array( 'left', 'center', 'right', 'col4', 'col5' );

		$raw = Customify()->get_setting( 'footer_' . $row_id . '_col_layout' );
		if ( ! $raw ) {
			return null;
		}

		$data = is_array( $raw ) ? $raw : json_decode( $raw, true );
		if ( ! is_array( $data ) || empty( $data['count'] ) ) {
			return null;
		}

		$count = max( 1, min( 5, intval( $data['count'] ) ) );
		return array_slice( $all_cols, 0, $count );
	}

	public function render_row( $row_settings, $id = '', $device = 'desktop' ) {
		$flag_key_row = $id . '-' . $device;

		// Check if the row are not showing.
		if ( ! isset( $this->flag_rows[ $flag_key_row ] ) ) {
			return false;
		}

		ob_start();
		$count      = 0;
		$no_cols    = array();
		$row_clases = array( 'row-v2', 'row-v2-' . $id );
		$has_center = false;

		// For footer rows use col_layout to get the correct ordered column keys,
		// and always render all defined columns (even empty ones) so that the
		// CSS grid-template-columns applied by customify_footer_row_layout_css()
		// maps onto the right number of grid cells.
		$footer_col_keys = 'footer' === $this->id ? $this->get_footer_col_keys( $id ) : null;
		$force_all_cols  = null !== $footer_col_keys;
		$ordered_cols    = $force_all_cols ? $footer_col_keys : array_keys( $row_settings );

		// Total visible column count, used as `cc-{N}` class on every col-v2
		// so CSS can target a row's layout density (e.g. `.col-v2.cc-5`).
		// Footer: from col_layout.count via $footer_col_keys.
		// Header: 3 (left/center/right slots are always considered, even if
		// some are empty placeholders to keep flex alignment).
		$col_count = $force_all_cols ? count( $footer_col_keys ) : 3;

		foreach ( $ordered_cols as $col_id ) {
			$col_items    = isset( $row_settings[ $col_id ] ) ? $row_settings[ $col_id ] : array();
			$flag_key_col = $col_id . '-' . $id . '-' . $device;
			$has_items    = isset( $this->flag_cols[ $flag_key_col ] );

			// Skip empty columns for header; always render for footer (grid needs them).
			if ( ! $force_all_cols && ! $has_items ) {
				$no_key             = 'no-' . $col_id;
				$no_cols[ $no_key ] = $no_key;
				continue;
			}

			$count ++;
			if ( 'center' === $col_id ) {
				$has_center = true;
			}

			echo '<div class="col-v2 col-v2-' . esc_attr( $col_id ) . ' cc-' . (int) $col_count . '">';
			foreach ( $col_items as $item_index => $col_item ) {
				$item = $this->get_render_item( $col_item['id'] );
				if ( $item ) {
					$item_id = $col_item['id'];
					$content = $item['content'];
					if ( $content ) {
						$item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();
						if ( ! isset( $item_config['section'] ) ) {
							$item_config['section'] = '';
						}
						$item_classes   = array();
						$item_classes[] = 'item--inner';
						$item_classes[] = 'builder-item--' . $item_id;
						if ( strpos( $item_id, '-menu' ) ) {
							$item_classes[] = 'has_menu';
						}
						if ( is_customize_preview() ) {
							$item_classes[] = ' builder-item-focus';
						}

						$item_classes   = join( ' ', $item_classes );
						$row_items_html = '';
						$row_items_html .= '<div class="' . esc_attr( $item_classes ) . '" data-section="' . esc_attr( $item_config['section'] ) . '" data-item-id="' . esc_attr( $item_id ) . '" >';
						$row_items_html .= $this->setup_item_content( $content, $id, $device );
						if ( is_customize_preview() ) {
							$row_items_html .= '<span class="item--preview-name">' . esc_html( $item_config['name'] ) . '</span>';
						}
						$row_items_html .= '</div>';
						echo $row_items_html;
					}
				}
			}
			echo '</div>';
		}

		$row_innner_html = ob_get_clean();

		// For header only: pad center with empty left/right placeholders so flex alignment works.
		if ( ! $force_all_cols && $has_center ) {
			if ( isset( $no_cols['no-left'] ) ) {
				$row_innner_html = '<div class="col-v2 col-v2-left cc-' . (int) $col_count . '"></div>' . $row_innner_html;
			}
			if ( isset( $no_cols['no-right'] ) ) {
				$row_innner_html .= '<div class="col-v2 col-v2-right cc-' . (int) $col_count . '"></div>';
			}
		}

		if ( ! empty( $no_cols ) ) {
			$row_clases = array_merge( $row_clases, $no_cols );
		} else {
			$row_clases[] = 'full-cols';
		}

		$row_html = '<div class="' . esc_attr( join( ' ', $row_clases ) ) . '">';
		$row_html .= $row_innner_html;
		$row_html .= '</div>';

		return $row_html;
	}

	public function render( $row_ids = array( 'top', 'main', 'bottom' ) ) {

		$setting = $this->get_settings();
		$items   = $this->render_items();

		foreach ( $row_ids as $row_id ) {
			$show = customify_is_builder_row_display( $this->id, $row_id );
			if ( $show && isset( $this->rows[ $row_id ] ) ) {
				$show_on_devices = $this->rows[ $row_id ];
				if ( ! empty( $show_on_devices ) ) {
					$classes     = array();
					$_id         = sprintf( '%1$s-%2$s', $this->id, $row_id );
					$classes[]   = $_id;
					$classes[]   = $this->id . '--row';
					$desktop_row = $this->get_row_settings( $row_id, 'desktop' );
					$mobile_row  = $this->get_row_settings( $row_id, 'mobile' );
					$atts        = array();

					if ( ! empty( $desktop_row ) || ! empty( $mobile_row ) ) {

						$align_classes = 'customify-grid-middle';
						if ( 'footer' !== $this->id ) {
							// Header: add visibility classes based on which device data exists.
							if ( empty( $desktop_row ) ) {
								$classes[] = 'hide-on-desktop';
							}
							if ( empty( $mobile_row ) ) {
								$classes[] = 'hide-on-mobile hide-on-tablet';
							}
						}

						$row_layout    = Customify()->get_setting( $this->id . '_' . $row_id . '_layout' );
						$row_text_mode = Customify()->get_setting( $this->id . '_' . $row_id . '_text_mode' );
						if ( $row_layout ) {
							$classes[] = sanitize_text_field( $row_layout );
						}

						$classes = apply_filters( 'customify/builder/row-classes', $classes, $row_id, $this );

						$atts['class']       = join( ' ', $classes );
						$atts['id']          = 'cb-row--' . $_id;
						$atts['data-row-id'] = $row_id;
						$atts                = apply_filters( 'customify/builder/row-attrs', $atts, $row_id, $this );
						$string_atts         = '';
						foreach ( $atts as $k => $s ) {
							if ( is_array( $s ) ) {
								$s = wp_json_encode( $s );
							}
							$string_atts .= ' ' . sanitize_text_field( $k ) . '="' . esc_attr( $s ) . '" ';
						}
						if ( $desktop_row ) {
							$html_desktop = $this->render_row( $desktop_row, $row_id, 'desktop' );
						} else {
							$html_desktop = false;
						}
						if ( $mobile_row ) {
							$html_mobile = $this->render_row( $mobile_row, $row_id, 'mobile' );
						} else {
							$html_mobile = false;
						}

						// Row inner class.
						// Check if the row is header or footer.
						$inner_class = array();
						if ( 'header' == $this->id ) {
							$inner_class[] = 'header--row-inner';
						} else {
							$inner_class[] = 'footer--row-inner';
						}
						$inner_class[] = $_id . '-inner';
						if ( $row_text_mode ) {
							$inner_class['row_text_mode'] = $row_text_mode;
						}

						$inner_class = apply_filters( 'customify/builder/inner-row-classes', $inner_class, $row_id, $this );

						if ( $html_mobile || $html_desktop ) {
							?>
							<div <?php echo $string_atts; ?> data-show-on="<?php echo esc_attr( join( ' ', $show_on_devices ) ); ?>">
								<div class="<?php echo join( ' ', $inner_class ); ?>">
									<div class="customify-container">
										<?php
										if ( 'footer' === $this->id ) {
											// Footer uses a single responsive grid for all viewports.
											// CSS grid-template-columns media queries (from col_layout)
											// handle tablet/mobile layout — no need for separate HTML grids.
											$html_footer = $html_desktop ?: $html_mobile;
											if ( $html_footer ) {
												echo '<div class="customify-grid ' . esc_attr( $align_classes ) . '">';
												echo $html_footer;
												echo '</div>';
											}
										} else {
											if ( $html_desktop ) {
												$c = 'cb-row--desktop hide-on-mobile hide-on-tablet';
												echo '<div class="customify-grid  ' . esc_attr( $c . ' ' . $align_classes ) . '">';
												echo $html_desktop;
												echo '</div>';
											}

											if ( $html_mobile ) {
												echo '<div class="cb-row--mobile hide-on-desktop customify-grid ' . esc_attr( $align_classes ) . '">';
												echo $html_mobile;
												echo '</div>';
											}
										}
										?>
									</div>
								</div>
							</div>
							<?php
						}
					}
				}
			}
		} // end for each row_ids.
	}

	/**
	 * Render sidebar row
	 */
	public function render_mobile_sidebar() {
		$id                = 'sidebar';
		$mobile_items      = $this->get_row_settings( $id, 'mobile' );
		$menu_sidebar_skin = Customify()->get_setting( 'header_sidebar_skin_mode' );

		if ( ! is_array( $mobile_items ) ) {
			$mobile_items = array();
		}

		if ( ! empty( $mobile_items ) || is_customize_preview() ) {

			$classes = array( 'header-menu-sidebar menu-sidebar-panel' );
			if ( '' != $menu_sidebar_skin ) {
				$classes[] = $menu_sidebar_skin;
			}

			echo '<div id="header-menu-sidebar" class="' . esc_attr( join( ' ', $classes ) ) . '">';
			echo '<div id="header-menu-sidebar-bg" class="header-menu-sidebar-bg">';
			echo '<div id="header-menu-sidebar-inner" class="header-menu-sidebar-inner">';

			foreach ( $mobile_items as $row_id => $col_items ) {
				foreach ( $col_items as $item_index => $item ) {
					$item_id     = $item['id'];
					$item        = $this->get_render_item( $item_id );
					$content     = $item['content'];
					$item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();
					$item_config = wp_parse_args(
						$item_config,
						array(
							'section' => '',
							'name'    => '',
						)
					);
					$classes = 'builder-item-sidebar mobile-item--' . $item_id;
					if ( strpos( $item_id, 'menu' ) ) {
						$classes = $classes . ' mobile-item--menu ';
					}
					$inner_classes = 'item--inner';
					if ( is_customize_preview() ) {
						$inner_classes = $inner_classes . ' builder-item-focus ';
					}
					$content = $this->setup_item_content( $content, $id, 'mobile' );
					echo '<div class="' . esc_attr( $classes ) . '">';
					echo '<div class="' . esc_attr( $inner_classes ) . '" data-item-id="' . esc_attr( $item_id ) . '" data-section="' . esc_attr( $item_config['section'] ) . '">';
					echo $content;
					if ( is_customize_preview() ) {
						echo '<span class="item--preview-name">' . esc_html( $item_config['name'] ) . '</span>';
					}
					echo '</div>';
					echo '</div>';
				}
			}

			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}
}


/**
 * Alias of class Customify_Layout_Builder_Frontend_V2
 *
 * @see Customify_Layout_Builder_Frontend
 *
 * @return Customify_Layout_Builder_Frontend_V2
 */
function Customify_Layout_Builder_Frontend_V2() {
	return Customify_Layout_Builder_Frontend_V2::get_instance();
}
