<?php
/**
 * Builder frontemd class
 *
 * @since 0.2.7
 */
class Customify_Layout_Builder_Frontend_V2 {
	public static $_instance;
	private $control_id = 'header_builder_panel_v2';
	public $id = 'header';
	private $render_items = array();
	private $rows = array();
	private $data = false;
	private $config_items = false;

	public function __construct() {

	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function set_id( $id ) {
		$this->id   = $id;
		$this->data = null;
	}

	public function set_control_id( $id ) {
		$this->control_id = $id;
		$this->data       = null;
	}

	/**
	 * Set config items
	 *
	 * @param array $config_items Config items.
	 */
	public function set_config_items( $config_items ) {
		$this->config_items = $config_items;
	}

	/**
	 * Get Panel Settings Data
	 *
	 * @return array|bool
	 */
	function get_settings() {
		if ( $this->data ) {
			return $this->data;
		}
		$data = Customify()->get_setting( $this->control_id );
		$data = wp_parse_args(
			$data,
			array(
				'desktop' => '',
				'tablet'  => '',
				'mobile'  => '',
			)
		);

		foreach ( $data as $k => $v ) {
			if ( ! is_array( $v ) ) {
				$v = array();
			}
			$data[ $k ] = $v;
		}

		$this->data = $data;

		return $data;
	}

	/**
	 * Get settings for row
	 *
	 * @param string $row_id Row ID.
	 * @param string $device Device ID.
	 *
	 * @return bool
	 */
	public function get_row_settings( $row_id, $device = 'desktop' ) {
		$data = $this->get_settings();
		if ( isset( $data[ $device ] ) ) {
			if ( isset( $data[ $device ][ $row_id ] ) ) {
				return ! empty( $data[ $device ][ $row_id ] ) ? $data[ $device ][ $row_id ] : false;
			}
		}

		return false;
	}

	/**
	 * Render items to HTML
	 *
	 * @param array $list_items List Items.
	 *
	 * @return array
	 */
	function render_items( $list_items = array() ) {
		$setting = $this->get_settings();
		$items   = array();

		// Loop devices.
		foreach ( $setting as $device => $device_settings ) {
			var_dump( $device_settings );
			foreach ( $device_settings as $row_id => $row_cols ) {
				if ( is_array( $row_cols ) && ! empty( $row_cols ) ) {
				}
			
			}
		}

		$this->render_items = $items;

		return $items;
	}

	/**
	 * Sort items by their position on the grid.
	 *
	 * @param array $items List item to sort.
	 *
	 * @return  array
	 */
	private function _sort_items_by_position( $items = array() ) {
		$ordered_items = array();

		foreach ( $items as $key => $item ) {
			$ordered_items[ $key ] = $item['x'];
		}

		array_multisort( $ordered_items, SORT_ASC, $items );

		return $items;
	}

	/**
	 * Setup Item content
	 *
	 * @todo Ensure item have not duplicate id
	 *
	 * @param string $content Content.
	 * @param string $id      ID.
	 * @param string $device  Device ID.
	 *
	 * @return mixed
	 */
	public function setup_item_content( $content, $id, $device ) {
		$content = str_replace( '__id__', $id, $content );
		$content = str_replace( '__device__', $device, $content );
		/**
		 *
		 * Ensure only one H! tag for the site title
		 *
		 * @since 0.2.3
		 */
		$content = str_replace( '__site_device_tag__', 'desktop' == $device ? 'h1' : 'h2', $content );

		return $content;
	}

	public function render_row( $row_settings, $id = '', $device = 'desktop' ) {
		var_dump( $row_settings );
	}

	public function render( $row_ids = array( 'top', 'main', 'bottom' ) ) {

		$setting = $this->get_settings();
		$items   = $this->render_items();

		foreach ( $row_ids as $row_id ) {
			$show = customify_is_builder_row_display( $this->id, $row_id );
			if ( $show && isset( $this->rows[ $row_id ] ) ) {
				$show_on_devices = $this->rows[ $row_id ];
				if ( ! empty( $show_on_devices ) ) {
					$classes = array();
					$_id     = sprintf( '%1$s-%2$s', $this->id, $row_id );

					$classes[]     = $_id;
					$classes[]     = $this->id . '--row';
					$desktop_row = $this->get_row_settings( $row_id, 'desktop' );
					$mobile_row  = $this->get_row_settings( $row_id, 'mobile' );
					$atts          = array();
					if ( ! empty( $desktop_row ) || ! empty( $mobile_row ) ) {

						$align_classes = 'customify-grid-middle';
						if ( empty( $desktop_row ) ) {
							$classes[] = 'hide-on-desktop';
						}
						if ( empty( $mobile_row ) ) {
							$classes[] = 'hide-on-mobile hide-on-tablet';
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
										if ( $html_desktop ) {

											if ( $html_desktop ) {
												$c = 'cb-row--desktop hide-on-mobile hide-on-tablet';
												if ( empty( $mobile_items ) ) {
													$c = '';
												}
												echo '<div class="customify-grid ' . esc_attr( $c . ' ' . $align_classes ) . '">';
												echo $html_desktop;
												echo '</div>';
											}
										}

										if ( $html_mobile ) {
											echo '<div class="cb-row--mobile hide-on-desktop customify-grid ' . esc_attr( $align_classes ) . '">';
											echo $html_mobile;
											echo '</div>';
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

			// foreach ( $mobile_items as $item ) {
			// 	$item_id     = $item['id'];
			// 	$content     = $this->render_items[ $item['id'] ]['render_content'];
			// 	$item_config = isset( $this->config_items[ $item_id ] ) ? $this->config_items[ $item_id ] : array();
			// 	$item_config = wp_parse_args(
			// 		$item_config,
			// 		array(
			// 			'section' => '',
			// 			'name'    => '',
			// 		)
			// 	);

			// 	$classes = 'builder-item-sidebar mobile-item--' . $item_id;
			// 	if ( strpos( $item_id, 'menu' ) ) {
			// 		$classes = $classes . ' mobile-item--menu ';
			// 	}
			// 	$inner_classes = 'item--inner';
			// 	if ( is_customize_preview() ) {
			// 		$inner_classes = $inner_classes . ' builder-item-focus ';
			// 	}

			// 	$content = $this->setup_item_content( $content, $id, 'mobile' );

			// 	echo '<div class="' . esc_attr( $classes ) . '">';
			// 	echo '<div class="' . esc_attr( $inner_classes ) . '" data-item-id="' . esc_attr( $item_id ) . '" data-section="' . $item_config['section'] . '">';
			// 	echo $content;
			// 	if ( is_customize_preview() ) {
			// 		echo '<span class="item--preview-name">' . esc_html( $item_config['name'] ) . '</span>';
			// 	}
			// 	echo '</div>';
			// 	echo '</div>';
			// }



			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Close item HTML markup
	 *
	 * @param string $class Icon class.
	 *
	 * @return string
	 */
	public function close_icon( $class = '' ) {
		$menu_sidebar_skin = Customify()->get_setting( 'header_sidebar_text_mode' );
		$close             = '<a class="close is-size-medium ' . $menu_sidebar_skin . esc_attr( $class ) . '" href="#">
        <span class="hamburger hamburger--squeeze is-active">
            <span class="hamburger-box">
              <span class="hamburger-inner"><span class="screen-reader-text">' . __( 'Menu', 'customify' ) . '</span></span>
            </span>
        </span>
        <span class="screen-reader-text">' . __( 'Close', 'customify' ) . '</span>
        </a>';

		return $close;
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
