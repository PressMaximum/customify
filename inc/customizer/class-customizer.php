<?php
/**
 * Customify Theme Customizer
 *
 * @package customify
 */
class  Customify_Customizer {
	static $config;
	static $_instance;
	static $has_icon = false;
	static $has_font = false;
	public $devices = array( 'desktop', 'tablet', 'mobile' );
	private $selective_settings = array();

	function __construct() {

	}

	static function verify_nonce( ) {
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : '';
		if ( ! $nonce ) {
			$nonce = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field( $_REQUEST['_nonce'] ) : '';
		}
		return wp_verify_nonce( $nonce, 'customify_customizer_control' );
	}

	static function create_nonce() {
		return wp_create_nonce( 'customify_customizer_control' );
	}

	/**
	 * Main initial
	 */
	function init() {

		require_once get_template_directory() . '/inc/customizer/class-customizer-sanitize.php';
		// Theme + Font Library bridges must load before auto-css because
		// the CSS generator references them from frontend render
		// (guests included, no admin guard). Font Loader sits at the
		// same level — used by auto-css for dedupe and by enqueue
		// hooks for preconnect / preload.
		require_once get_template_directory() . '/inc/customizer/class-customizer-theme-fonts.php';
		require_once get_template_directory() . '/inc/customizer/class-customizer-font-library.php';
		require_once get_template_directory() . '/inc/customizer/class-customizer-font-loader.php';
		require_once get_template_directory() . '/inc/customizer/class-customizer-auto-css.php';

		if ( is_admin() || is_customize_preview() ) {
			add_action( 'customize_register', array( $this, 'register' ), 666 );
			// Run last so every panel (core, plugins, third-party) is already registered
			// when we slot panels into groups and bump foreign items below the divider.
			add_action( 'customize_register', array( $this, 'register_panel_groups' ), 99999 );
			// Override JS section constructor for the divider type so WP customizer
			// doesn't auto-hide it (default behaviour hides sections with no controls).
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'print_divider_section_constructor' ) );
			add_action( 'customize_preview_init', array( $this, 'preview_js' ) );
			// Inject every theme.json + Font Library family into the
			// preview iframe head so users can switch to any registered
			// font and see it render immediately — the frontend code
			// path only emits @font-face for fonts already saved into
			// settings.
			add_action( 'customize_preview_init', array( $this, 'preview_theme_fonts' ) );
			add_action( 'customize_preview_init', array( $this, 'preview_library_fonts' ) );
			add_action( 'wp_ajax_customify/customizer/ajax/get_icons', array( $this, 'get_icons' ) );

			require_once get_template_directory() . '/inc/customizer/class-customizer-fonts.php';
			require_once get_template_directory() . '/inc/customizer/class-customizer.php';
		}

		add_action( 'wp_ajax_customify__reset_section', array( 'Customify_Customizer', 'reset_customize_section' ) );
	}

	/**
	 * Instance.
	 *
	 * @return Customify_Customizer
	 */
	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Reset Customize section
	 */
	static function reset_customize_section() {
		if ( ! self::verify_nonce() ) {
			return wp_send_json_error();
		}
		if ( ! current_user_can( 'customize' ) ) {
			return wp_send_json_error();
		}

		$settings = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array(); // phpcs:ignore

		foreach ( $settings as $k ) {
			$k = sanitize_text_field( $k );
			remove_theme_mod( $k );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX: return the registered icon library for the Customizer's
	 * font-icon picker. Gated by the Customizer preview nonce because
	 * the picker only runs inside that iframe.
	 */
	function get_icons() {
		// The customize-preview iframe ships its WP-issued preview nonce
		// as `_nonce` (see _wpCustomizeSettings.nonce.preview). Match the
		// stylesheet-bound action WP_Customize_Manager registers.
		$nonce = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! wp_verify_nonce( $nonce, 'preview-customize_' . get_stylesheet() ) ) {
			wp_send_json_error( 'invalid_nonce', 403 );
		}
		if ( ! current_user_can( 'customize' ) ) {
			wp_send_json_error( 'forbidden', 403 );
		}

		require_once get_template_directory() . '/inc/customizer/class-customizer-icons.php';
		wp_send_json_success( Customify_Font_Icons::get_instance()->get_icons() );
		die();
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	/**
	 * Counterpart to preview_library_fonts() for theme.json fonts.
	 * Same rationale (cheap to ship, browsers lazy-load font files).
	 */
	function preview_theme_fonts() {
		if ( ! class_exists( 'Customify_Customizer_Theme_Fonts' ) ) {
			return;
		}
		add_action( 'wp_head', function () {
			$theme = ( new Customify_Customizer_Theme_Fonts() )->get_for_frontend();
			if ( empty( $theme ) ) {
				return;
			}
			// Skip families WP auto-prints — see same dedupe in
			// get_theme_fonts_css(). The preview iframe runs WP core
			// hooks too, so wp_print_font_faces() already emitted the
			// CSS we'd otherwise duplicate here.
			$handled = class_exists( 'Customify_Customizer_Font_Loader' )
				? Customify_Customizer_Font_Loader::wp_handled_names()
				: array();
			$buf = '';
			foreach ( $theme as $name => $data ) {
				if ( isset( $handled[ $name ] ) ) {
					continue;
				}
				if ( empty( $data['font_faces'] ) ) {
					continue;
				}
				foreach ( $data['font_faces'] as $face ) {
					$buf .= sprintf(
						"@font-face{font-family:'%s';font-style:%s;font-weight:%s;font-display:swap;src:url('%s');}",
						esc_attr( $name ),
						esc_attr( $face['style'] ),
						esc_attr( $face['weight'] ),
						esc_url( $face['src'] )
					);
				}
			}
			if ( '' === $buf ) {
				return;
			}
			echo '<style id="customify-theme-fonts-preview">' . $buf . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}, 100 );
	}

	/**
	 * Print @font-face declarations for every WP Font Library family
	 * into the customize preview iframe so the user can preview any
	 * uploaded font even before saving it. Browsers only download
	 * font files when a matching font-family is actually used, so
	 * shipping the full catalogue costs CSS bytes but no HTTP traffic.
	 */
	function preview_library_fonts() {
		if ( ! class_exists( 'Customify_Customizer_Font_Library' ) ) {
			return;
		}
		add_action( 'wp_head', function () {
			$library = ( new Customify_Customizer_Font_Library() )->get_for_frontend();
			if ( empty( $library ) ) {
				return;
			}
			// Skip families WP auto-prints (activated Library fonts in
			// global settings). The preview iframe runs WP core hooks
			// so wp_print_font_faces() already emitted those.
			$handled = class_exists( 'Customify_Customizer_Font_Loader' )
				? Customify_Customizer_Font_Loader::wp_handled_names()
				: array();
			echo '<style id="customify-library-fonts-preview">';
			foreach ( $library as $name => $data ) {
				if ( isset( $handled[ $name ] ) ) {
					continue;
				}
				if ( empty( $data['font_faces'] ) ) {
					continue;
				}
				foreach ( $data['font_faces'] as $face ) {
					printf(
						"@font-face{font-family:'%s';font-style:%s;font-weight:%s;font-display:swap;src:url('%s');}",
						esc_attr( $name ),
						esc_attr( $face['style'] ),
						esc_attr( $face['weight'] ),
						esc_url( $face['src'] )
					);
				}
			}
			echo '</style>';
		}, 100 );
	}

	function preview_js() {
		if ( is_customize_preview() ) {
			$suffix = Customify()->get_asset_suffix();

			// Use webpack-emitted content hashes so browsers refetch the bundle
			// after every rebuild. The hardcoded '20151215' used previously
			// meant `?ver=` never changed, so users had to hard-refresh to
			// see JS updates (e.g. footer col-layout live-preview fixes).
			$auto_css_asset = require get_template_directory() . '/build/js/backend/customizer/auto-css.asset.php';
			$customizer_asset = require get_template_directory() . '/build/js/backend/customizer/customizer.asset.php';

			wp_enqueue_script(
				'customify-customizer-auto-css',
				esc_url( get_template_directory_uri() ) . '/build/js/backend/customizer/auto-css.js',
				array_merge( array( 'customize-preview' ), $auto_css_asset['dependencies'] ),
				$auto_css_asset['version'],
				true
			);
			wp_enqueue_script(
				'customify-customizer',
				esc_url( get_template_directory_uri() ) . '/build/js/backend/customizer/customizer.js',
				array_merge( array( 'customize-preview', 'customize-selective-refresh' ), $customizer_asset['dependencies'] ),
				$customizer_asset['version'],
				true
			);
			wp_localize_script(
				'customify-customizer-auto-css',
				'Customify_Preview_Config',
				array(
					'fields'         => $this->get_config(),
					'devices'        => $this->devices,
					'typo_fields'    => $this->get_typo_fields(),
					'styling_config' => $this->get_styling_config(),
					// Filtered footer row IDs (top/main/bottom + 3rd-party
					// additions). customizer.js iterates this to bind a
					// live-preview handler on every footer row's
					// col_layout setting.
					'footer_rows'    => function_exists( 'customify_get_footer_row_ids' )
						? customify_get_footer_row_ids()
						: array( 'main', 'bottom' ),
				)
			);
		}
	}

	/**
	 * Get all customizer settings/control that added via `customify/customizer/config` hook
	 *
	 *  Ensure you call this method after all config files loaded
	 *
	 * @param WP_Customize_Manager $wp_customize
	 *
	 * @return array
	 */
	static function get_config( $wp_customize = null ) {
		if ( is_null( self::$config ) ) {

			$_config = apply_filters( 'customify/customizer/config', array(), $wp_customize );
			$config  = array();
			foreach ( $_config as $f ) {

				$f = wp_parse_args(
					$f,
					array(

						'priority'    => null,
						'title'       => null,
						'label'       => null,
						'name'        => null,
						'type'        => null,
						'description' => null,
						'capability'  => null,
						'mod'         => null, // Can be theme_mod or option, default theme_mod.
						'settings'    => null,

						'active_callback'      => null, // For control.

						/**
						 * For settings
						 */
						'sanitize_callback'    => array( 'Customify_Sanitize_Input', 'sanitize_customizer_input' ),
						'sanitize_js_callback' => null,
						'theme_supports'       => null,
						'default'              => null,

						/**
						 * For selective refresh
						 */
						'selector'             => null,
						'render'               => null, // same render_callback.
						'render_callback'      => null,
						'css_format'           => null,

						'device'          => null,
						'device_settings' => null,

						'field_class' => null, // Custom class for control.
					)
				);

				if ( ! isset( $f['type'] ) ) {
					$f['type'] = null;
				}

				switch ( $f['type'] ) {
					case 'panel':
						$config[ 'panel|' . $f['name'] ] = $f;
						break;
					case 'section':
						$config[ 'section|' . $f['name'] ] = $f;
						break;
					default:
						if ( 'icon' == $f['type'] ) {
							self::$has_icon = true;
						}

						if ( 'font' == $f['type'] ) {
							self::$has_font = true;
						}

						if ( isset( $f['fields'] ) ) {
							if ( ! in_array( $f['type'], array( 'typography', 'styling', 'modal' ) ) ) { //phpcs:ignore
								$types = wp_list_pluck( $f['fields'], 'type' );
								if ( in_array( 'icon', $types ) ) { //phpcs:ignore
									self::$has_icon = true;
								}

								if ( in_array( 'font', $types ) ) { //phpcs:ignore
									self::$has_font = true;
								}
							}
						}

						$config[ 'setting|' . $f['name'] ] = $f;

				}
			}

			self::$config = $config;
		}

		return self::$config;
	}

	/**
	 * Check if has icon field;
	 *
	 * @return bool
	 */
	function has_icon() {
		return self::$has_icon;
	}

	/**
	 * Check if has font field;
	 *
	 * @return bool
	 */
	function has_font() {
		return self::$has_icon;
	}

	/**
	 *  Get Customizer setting.
	 *
	 * @param string      $name   Customize setting id.
	 * @param string      $device Device type for settings.
	 * @param string|bool $key    Value of this array key that you want to get.
	 *
	 * @return array|bool|string
	 */
	function get_setting( $name, $device = 'desktop', $key = false ) {
		$config    = self::get_config();
		$get_value = null;
		if ( isset( $config[ 'setting|' . $name ] ) ) {
			$default = isset( $config[ 'setting|' . $name ]['default'] ) ? $config[ 'setting|' . $name ]['default'] : false;
			$default = apply_filters( 'customify/customize/settings-default', $default, $name );

			if ( 'option' == $config[ 'setting|' . $name ]['mod'] ) {
				$value = get_option( $name, $default );
			} else {
				$value = get_theme_mod( $name, $default );
			}

			// Maybe need merge defined items with saved item for defined list.
			if (
				'repeater' == $config[ 'setting|' . $name ]['type']
				&& isset( $config[ 'setting|' . $name ]['addable'] )
				&& false == $config[ 'setting|' . $name ]['addable']
			) {
				$value = self::merge_items( $value, $default );
			}

			if ( ! $config[ 'setting|' . $name ]['device_settings'] ) {
				return $value;
			}
		} else {
			$value = get_theme_mod( $name, null );
		}

		if ( ! $key ) {
			if ( 'all' != $device ) {
				if ( is_array( $value ) && isset( $value[ $device ] ) ) {
					$get_value = $value[ $device ];
				} else {
					$get_value = $value;
				}
			} else {
				$get_value = $value;
			}
		} else {
			$value_by_key = isset( $value[ $key ] ) ? $value[ $key ] : false;
			if ( 'all' != $device && is_array( $value_by_key ) ) {
				if ( is_array( $value_by_key ) && isset( $value_by_key[ $device ] ) ) {
					$get_value = $value_by_key[ $device ];
				} else {
					$get_value = $value_by_key;
				}
			} else {
				$get_value = $value_by_key;
			}
		}

		return $get_value;
	}


	/**
	 * Merge 2 array width `_key` is key of each item
	 *
	 * @since 0.2.5
	 *
	 * @param array $destination
	 * @param array $new_array
	 *
	 * @return array
	 */
	public static function merge_items( $destination = array(), $new_array = array() ) {

		$keys     = array();
		$new_keys = array();

		foreach ( $destination as $item ) {
			if ( isset( $item['_key'] ) ) {
				$keys[ $item['_key'] ] = $item['_key'];
			}
		}

		if ( empty( $keys ) ) {
			return $destination;
		}

		// Add item if it not in saved list.
		foreach ( $new_array as $item ) {
			if ( isset( $item['_key'] ) ) {
				$new_keys[ $item['_key'] ] = $item['_key'];
				if ( ! isset( $keys[ $item['_key'] ] ) ) {
					$destination[] = $item;
				}
			}
		}

		// Remove the item not in the defined list.
		$new_destination = array();
		foreach ( $destination as $_dk => $item ) {
			if ( isset( $item['_key'] ) ) {
				if ( isset( $new_keys[ $item['_key'] ] ) ) {
					$new_destination[] = $item;
				}
			} else {
				$new_destination[] = $item;
			}
		}

		return $new_destination;
	}

	/**
	 * Get customizer setting data when the field type is `modal`
	 *
	 * @param string      $name Setting name.
	 * @param string|bool $tab  String tab name.
	 *
	 * @return array|bool
	 */
	function get_setting_tab( $name, $tab = null ) {
		$values = $this->get_setting( $name, 'all' );
		if ( ! $tab ) {
			return $values;
		}
		if ( is_array( $values ) && isset( $values[ $tab ] ) ) {
			return $values[ $tab ];
		}

		return false;
	}

	/**
	 * Get typography fields
	 *
	 * @return array
	 */
	function get_typo_fields() {
		$typo_fields = array(
			array(
				'name'    => 'font',
				'type'    => 'select',
				'label'   => __( 'Font Family', 'customify' ),
				'choices' => array(),
			),
			array(
				'name'    => 'font_weight',
				'type'    => 'select',
				'label'   => __( 'Font Weight', 'customify' ),
				'choices' => array(),
			),
			array(
				'name'  => 'languages',
				'type'  => 'checkboxes',
				'label' => __( 'Font Languages', 'customify' ),
			),
			array(
				'name'            => 'font_size',
				'type'            => 'slider',
				'label'           => __( 'Font Size', 'customify' ),
				'device_settings' => true,
				'min'             => 9,
				'max'             => 80,
				'step'            => 1,
			),
			array(
				'name'            => 'line_height',
				'type'            => 'slider',
				'label'           => __( 'Line Height', 'customify' ),
				'device_settings' => true,
				'min'             => 9,
				'max'             => 80,
				'step'            => 1,
			),
			array(
				'name'  => 'letter_spacing',
				'type'  => 'slider',
				'label' => __( 'Letter Spacing', 'customify' ),
				'min'   => - 10,
				'max'   => 10,
				'step'  => 0.1,
			),
			array(
				'name'    => 'style',
				'type'    => 'select',
				'label'   => __( 'Font Style', 'customify' ),
				'choices' => array(
					''        => __( 'Default', 'customify' ),
					'normal'  => __( 'Normal', 'customify' ),
					'italic'  => __( 'Italic', 'customify' ),
					'oblique' => __( 'Oblique', 'customify' ),
				),
			),
			array(
				'name'    => 'text_decoration',
				'type'    => 'select',
				'label'   => __( 'Text Decoration', 'customify' ),
				'choices' => array(
					''             => __( 'Default', 'customify' ),
					'underline'    => __( 'Underline', 'customify' ),
					'overline'     => __( 'Overline', 'customify' ),
					'line-through' => __( 'Line through', 'customify' ),
					'none'         => __( 'None', 'customify' ),
				),
			),
			array(
				'name'    => 'text_transform',
				'type'    => 'select',
				'label'   => __( 'Text Transform', 'customify' ),
				'choices' => array(
					''           => __( 'Default', 'customify' ),
					'uppercase'  => __( 'Uppercase', 'customify' ),
					'lowercase'  => __( 'Lowercase', 'customify' ),
					'capitalize' => __( 'Capitalize', 'customify' ),
					'none'       => __( 'None', 'customify' ),
				),
			),
		);

		return $typo_fields;
	}

	/**
	 * Get styling field
	 *
	 * @return array
	 */
	function get_styling_config() {
		$fields = array(
			'tabs'          => array(
				'normal' => __( 'Normal', 'customify' ),  // null or false to disable.
				'hover'  => __( 'Hover', 'customify' ), // null or false to disable.
			),
			'normal_fields' => array(
				array(
					'name'       => 'text_color',
					'type'       => 'color',
					'label'      => __( 'Color', 'customify' ),
					'css_format' => 'color: {{value}}; text-decoration-color: {{value}};',
				),
				array(
					'name'       => 'link_color',
					'type'       => 'color',
					'label'      => __( 'Link Color', 'customify' ),
					'css_format' => 'color: {{value}}; text-decoration-color: {{value}};',
				),

				array(
					'name'            => 'margin',
					'type'            => 'css_ruler',
					'device_settings' => true,
					'css_format'      => array(
						'top'    => 'margin-top: {{value}};',
						'right'  => 'margin-right: {{value}};',
						'bottom' => 'margin-bottom: {{value}};',
						'left'   => 'margin-left: {{value}};',
					),
					'label'           => __( 'Margin', 'customify' ),
				),

				array(
					'name'            => 'padding',
					'type'            => 'css_ruler',
					'device_settings' => true,
					'css_format'      => array(
						'top'    => 'padding-top: {{value}};',
						'right'  => 'padding-right: {{value}};',
						'bottom' => 'padding-bottom: {{value}};',
						'left'   => 'padding-left: {{value}};',
					),
					'label'           => __( 'Padding', 'customify' ),
				),

				array(
					'name'  => 'bg_heading',
					'type'  => 'heading',
					'label' => __( 'Background', 'customify' ),
				),

				array(
					'name'       => 'bg_color',
					'type'       => 'color',
					'label'      => __( 'Background Color', 'customify' ),
					'css_format' => 'background-color: {{value}};',
				),
				array(
					'name'       => 'bg_image',
					'type'       => 'image',
					'label'      => __( 'Background Image', 'customify' ),
					'css_format' => 'background-image: url("{{value}}");',
				),
				array(
					'name'       => 'bg_cover',
					'type'       => 'select',
					'choices'    => array(
						''        => __( 'Default', 'customify' ),
						'auto'    => __( 'Auto', 'customify' ),
						'cover'   => __( 'Cover', 'customify' ),
						'contain' => __( 'Contain', 'customify' ),
					),
					'required'   => array( 'bg_image', 'not_empty', '' ),
					'label'      => __( 'Size', 'customify' ),
					'class'      => 'field-half-left',
					'css_format' => '-webkit-background-size: {{value}}; -moz-background-size: {{value}}; -o-background-size: {{value}}; background-size: {{value}};',
				),
				array(
					'name'       => 'bg_position',
					'type'       => 'select',
					'label'      => __( 'Position', 'customify' ),
					'required'   => array( 'bg_image', 'not_empty', '' ),
					'class'      => 'field-half-right',
					'choices'    => array(
						''              => __( 'Default', 'customify' ),
						'center'        => __( 'Center', 'customify' ),
						'top left'      => __( 'Top Left', 'customify' ),
						'top right'     => __( 'Top Right', 'customify' ),
						'top center'    => __( 'Top Center', 'customify' ),
						'bottom left'   => __( 'Bottom Left', 'customify' ),
						'bottom center' => __( 'Bottom Center', 'customify' ),
						'bottom right'  => __( 'Bottom Right', 'customify' ),
					),
					'css_format' => 'background-position: {{value}};',
				),
				array(
					'name'       => 'bg_repeat',
					'type'       => 'select',
					'label'      => __( 'Repeat', 'customify' ),
					'class'      => 'field-half-left',
					'required'   => array(
						array( 'bg_image', 'not_empty', '' ),
					),
					'choices'    => array(
						'repeat'    => __( 'Default', 'customify' ),
						'no-repeat' => __( 'No repeat', 'customify' ),
						'repeat-x'  => __( 'Repeat horizontal', 'customify' ),
						'repeat-y'  => __( 'Repeat vertical', 'customify' ),
					),
					'css_format' => 'background-repeat: {{value}};',
				),

				array(
					'name'       => 'bg_attachment',
					'type'       => 'select',
					'label'      => __( 'Attachment', 'customify' ),
					'class'      => 'field-half-right',
					'required'   => array(
						array( 'bg_image', 'not_empty', '' ),
					),
					'choices'    => array(
						''       => __( 'Default', 'customify' ),
						'scroll' => __( 'Scroll', 'customify' ),
						'fixed'  => __( 'Fixed', 'customify' ),
					),
					'css_format' => 'background-attachment: {{value}};',
				),

				array(
					'name'  => 'border_heading',
					'type'  => 'heading',
					'label' => __( 'Border', 'customify' ),
				),

				array(
					'name'       => 'border_style',
					'type'       => 'select',
					'class'      => 'clear',
					'label'      => __( 'Border Style', 'customify' ),
					'default'    => '',
					'choices'    => array(
						''       => __( 'Default', 'customify' ),
						'none'   => __( 'None', 'customify' ),
						'solid'  => __( 'Solid', 'customify' ),
						'dotted' => __( 'Dotted', 'customify' ),
						'dashed' => __( 'Dashed', 'customify' ),
						'double' => __( 'Double', 'customify' ),
						'ridge'  => __( 'Ridge', 'customify' ),
						'inset'  => __( 'Inset', 'customify' ),
						'outset' => __( 'Outset', 'customify' ),
					),
					'css_format' => 'border-style: {{value}};',
				),

				array(
					'name'       => 'border_width',
					'type'       => 'css_ruler',
					'label'      => __( 'Border Width', 'customify' ),
					'required'   => array(
						array( 'border_style', '!=', 'none' ),
						array( 'border_style', '!=', '' ),
					),
					'css_format' => array(
						'top'    => 'border-top-width: {{value}};',
						'right'  => 'border-right-width: {{value}};',
						'bottom' => 'border-bottom-width: {{value}};',
						'left'   => 'border-left-width: {{value}};',
					),
				),
				array(
					'name'       => 'border_color',
					'type'       => 'color',
					'label'      => __( 'Border Color', 'customify' ),
					'required'   => array(
						array( 'border_style', '!=', 'none' ),
						array( 'border_style', '!=', '' ),
					),
					'css_format' => 'border-color: {{value}};',
				),

				array(
					'name'       => 'border_radius',
					'type'       => 'css_ruler',
					'label'      => __( 'Border Radius', 'customify' ),
					'css_format' => array(
						'top'    => 'border-top-left-radius: {{value}};',
						'right'  => 'border-top-right-radius: {{value}};',
						'bottom' => 'border-bottom-right-radius: {{value}};',
						'left'   => 'border-bottom-left-radius: {{value}};',
					),
				),

				array(
					'name'       => 'box_shadow',
					'type'       => 'shadow',
					'label'      => __( 'Box Shadow', 'customify' ),
					'css_format' => 'box-shadow: {{value}};',
				),

			),

			'hover_fields' => array(
				array(
					'name'       => 'text_color',
					'type'       => 'color',
					'label'      => __( 'Color', 'customify' ),
					'css_format' => 'color: {{value}}; text-decoration-color: {{value}};',
				),
				array(
					'name'       => 'link_color',
					'type'       => 'color',
					'label'      => __( 'Link Color', 'customify' ),
					'css_format' => 'color: {{value}}; text-decoration-color: {{value}};',
				),
				array(
					'name'  => 'bg_heading',
					'type'  => 'heading',
					'label' => __( 'Background', 'customify' ),
				),
				array(
					'name'       => 'bg_color',
					'type'       => 'color',
					'label'      => __( 'Background Color', 'customify' ),
					'css_format' => 'background-color: {{value}};',
				),
				array(
					'name'  => 'border_heading',
					'type'  => 'heading',
					'label' => __( 'Border', 'customify' ),
				),
				array(
					'name'       => 'border_style',
					'type'       => 'select',
					'label'      => __( 'Border Style', 'customify' ),
					'default'    => '',
					'choices'    => array(
						''       => __( 'Default', 'customify' ),
						'none'   => __( 'None', 'customify' ),
						'solid'  => __( 'Solid', 'customify' ),
						'dotted' => __( 'Dotted', 'customify' ),
						'dashed' => __( 'Dashed', 'customify' ),
						'double' => __( 'Double', 'customify' ),
						'ridge'  => __( 'Ridge', 'customify' ),
						'inset'  => __( 'Inset', 'customify' ),
						'outset' => __( 'Outset', 'customify' ),
					),
					'css_format' => 'border-style: {{value}};',
				),
				array(
					'name'       => 'border_width',
					'type'       => 'css_ruler',
					'label'      => __( 'Border Width', 'customify' ),
					'required'   => array( 'border_style', '!=', 'none' ),
					'css_format' => array(
						'top'    => 'border-top-width: {{value}};',
						'right'  => 'border-right-width: {{value}};',
						'bottom' => 'border-bottom-width: {{value}};',
						'left'   => 'border-left-width: {{value}};',
					),
				),
				array(
					'name'       => 'border_color',
					'type'       => 'color',
					'label'      => __( 'Border Color', 'customify' ),
					'required'   => array( 'border_style', '!=', 'none' ),
					'css_format' => 'border-color: {{value}};',
				),
				array(
					'name'       => 'border_radius',
					'type'       => 'css_ruler',
					'label'      => __( 'Border Radius', 'customify' ),
					'css_format' => array(
						'top'    => 'border-top-left-radius: {{value}};',
						'right'  => 'border-top-right-radius: {{value}};',
						'bottom' => 'border-bottom-right-radius: {{value}};',
						'left'   => 'border-bottom-left-radius: {{value}};',
					),
				),
				array(
					'name'       => 'box_shadow',
					'type'       => 'shadow',
					'label'      => __( 'Box Shadow', 'customify' ),
					'css_format' => 'box-shadow: {{value}};',
				),

			),

		);

		return apply_filters( 'customify/get_styling_config', $fields );
	}

	/**
	 * Setup icon args
	 *
	 * @param array $icon
	 *
	 * @return array
	 */
	function setup_icon( $icon ) {
		if ( ! is_array( $icon ) ) {
			$icon = array();
		}

		return wp_parse_args(
			$icon,
			array(
				'type' => '',
				'icon' => '',
			)
		);
	}

	/**
	 * Get customize setting data
	 *
	 * @param string $key Setting name.
	 *
	 * @return bool|mixed
	 */
	function get_field_setting( $key ) {
		$config = self::get_config();
		if ( isset( $config[ 'setting|' . $key ] ) ) {
			return $config[ 'setting|' . $key ];
		}

		return false;
	}

	/**
	 * Get Media url from data
	 *
	 * @param string/array $value   media data.
	 * @param null|string  $size WordPress image size name.
	 *
	 * @return string|false Media url or empty
	 */
	function get_media( $value, $size = null ) {

		if ( empty( $value ) ) {
			return false;
		}

		if ( ! $size ) {
			$size = 'full';
		}

		if ( is_numeric( $value ) ) {
			$image_attributes = wp_get_attachment_image_src( $value, $size );
			if ( $image_attributes ) {
				return $image_attributes[0];
			} else {
				return false;
			}
		} elseif ( is_string( $value ) ) {
			$img_id = attachment_url_to_postid( $value );
			if ( $img_id ) {
				$image_attributes = wp_get_attachment_image_src( $img_id, $size );
				if ( $image_attributes ) {
					return $image_attributes[0];
				} else {
					return false;
				}
			}

			return $value;
		} elseif ( is_array( $value ) ) {
			$value = wp_parse_args(
				$value,
				array(
					'id'   => '',
					'url'  => '',
					'mime' => '',
				)
			);

			if ( empty( $value['id'] ) && empty( $value['url'] ) ) {
				return false;
			}

			$url = '';

			if ( strpos( $value['mime'], 'image/' ) !== false ) {
				$image_attributes = wp_get_attachment_image_src( $value['id'], $size );
				if ( $image_attributes ) {
					$url = $image_attributes[0];
				}
			} else {
				$url = wp_get_attachment_url( $value['id'] );
			}

			if ( ! $url ) {
				$url = $value['url'];
				if ( $url ) {
					$img_id = attachment_url_to_postid( $url );
					if ( $img_id ) {
						return wp_get_attachment_url( $img_id );
					}
				}
			}

			return $url;
		}

		return false;
	}

	/**
	 * Load support controls
	 */
	function load_controls() {
		$fields = array(
			'base',
			'select',
			'font',
			'font_style',
			'text_align',
			'text_align_no_justify',
			'checkbox',
			'css_ruler',
			'shadow',
			'icon',
			'slider',
			'color',
			'textarea',
			'radio',

			'media',
			'image',
			'video',

			'text',
			'hidden',
			'heading',
			'typography',
			'modal',
			'styling',
			'hr',

			'repeater',

			'pro',
		);

		$fields = apply_filters( 'customify/customize/register-controls', $fields );

		foreach ( $fields as $k => $f ) {

			if ( is_numeric( $k ) ) {
				$field_type = $f;
				$file       = get_template_directory() . '/inc/customizer/controls/class-control-' . str_replace( '_', '-', $field_type ) . '.php';
			} else {
				$field_type = $k;
				$file       = $f;
			}

			if ( file_exists( $file ) ) {

				$control_class_name = 'Customify_Customizer_Control_';
				$tpl_type           = str_replace( '_', ' ', $field_type );
				$tpl_type           = str_replace( ' ', '_', ucfirst( $tpl_type ) );
				$control_class_name .= $tpl_type;
				require_once $file;

				if ( $control_class_name ) {
					if ( method_exists( $control_class_name, 'field_template' ) ) {
						add_action(
							'customize_controls_print_footer_scripts',
							array(
								$control_class_name,
								'field_template',
							)
						);
					}
				}
			}
		}

	}


	/**
	 * Register Customize Settings
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	function register( $wp_customize ) {

		// Custom panel.
		require_once get_template_directory() . '/inc/customizer/class-customify-panel.php';
		// Load custom section.
		require_once get_template_directory() . '/inc/customizer/class-customify-section.php';

		// Load custom section pro.
		require_once get_template_directory() . '/inc/customizer/class-customify-section-pro.php';

		// Register new panel and section type.
		$wp_customize->register_panel_type( 'Customify_WP_Customize_Panel' );
		$wp_customize->register_panel_type( 'Customify_Header_Builder_Panel' );
		$wp_customize->register_panel_type( 'Customify_Footer_Builder_Panel' );
		$wp_customize->register_section_type( 'Customify_WP_Customize_Section' );

		// Register section type.
		$wp_customize->register_section_type( 'Customify_WP_Customize_Section_Pro' );

		$this->load_controls();

		foreach ( self::get_config( $wp_customize ) as $args ) {
			switch ( $args['type'] ) {
				case 'panel':
					$name = $args['name'];
					unset( $args['name'] );
					if ( ! $args['title'] ) {
						$args['title'] = $args['label'];
					}
					if ( ! $name ) {
						$name = $args['title'];
					}
					unset( $args['name'] );
					unset( $args['type'] );
					if ( 'header_settings' === $name ) {
						$panel_class = 'Customify_Header_Builder_Panel';
					} elseif ( 'footer_settings' === $name ) {
						$panel_class = 'Customify_Footer_Builder_Panel';
					} else {
						$panel_class = 'Customify_WP_Customize_Panel';
					}
					$panel = new $panel_class( $wp_customize, $name, $args );
					$wp_customize->add_panel( $panel );
					break;
				case 'section':
					$name = $args['name'];
					unset( $args['name'] );
					if ( ! $args['title'] ) {
						$args['title'] = $args['label'];
					}
					if ( ! $name ) {
						$name = $args['title'];
					}
					unset( $args['name'] );
					unset( $args['type'] );
					if ( isset( $args['section_class'] ) && class_exists( $args['section_class'] ) ) { // Allow custom class.
						$wp_customize->add_section( new $args['section_class']( $wp_customize, $name, $args ) );
					} else {
						$wp_customize->add_section( new Customify_WP_Customize_Section( $wp_customize, $name, $args ) );
					}

					break;
				default:
					switch ( $args['type'] ) {
						case 'image_select':
							$args['setting_type'] = 'radio';
							$args['field_class']  = 'custom-control-image_select' . ( $args['field_class'] ? ' ' . $args['field_class'] : '' );
							break;
						case 'radio_group':
							$args['setting_type'] = 'radio';
							$args['field_class']  = 'custom-control-radio_group' . ( $args['field_class'] ? ' ' . $args['field_class'] : '' );
							break;
						default:
							$args['setting_type'] = $args['type'];
					}

					$args['default_value']    = $args['default'];
					$settings_args            = array(
						'sanitize_callback'    => $args['sanitize_callback'],
						'sanitize_js_callback' => $args['sanitize_js_callback'],
						'theme_supports'       => $args['theme_supports'],
						'type'                 => $args['mod'],
					);
					$settings_args['default'] = apply_filters( 'customify/customize/settings-default', $args['default'], $args['name'] );

					$settings_args['transport'] = 'refresh';
					if ( ! $settings_args['sanitize_callback'] ) {
						$settings_args['sanitize_callback'] = array(
							'Customify_Sanitize_Input',
							'sanitize_customizer_input',
						);
					}

					foreach ( $settings_args as $k => $v ) {
						unset( $args[ $k ] );
					}

					unset( $args['mod'] );
					$name = $args['name'];
					unset( $args['name'] );

					unset( $args['type'] );
					if ( ! $args['label'] ) {
						$args['label'] = $args['title'];
					}

					$selective_refresh = null;
					if ( $args['selector'] && ( ( $args['render_callback'] || $args['render'] ) || $args['css_format'] ) ) {
						$selective_refresh = array(
							'selector'        => $args['selector'],
							'render_callback' => ( $args['render'] ) ? $args['render'] : $args['render_callback'],
						);

						if ( $args['css_format'] ) {
							$settings_args['transport'] = 'postMessage';
							$selective_refresh          = null;
						} else {
							$settings_args['transport'] = 'postMessage';
						}
					}
					unset( $args['default'] );

					$wp_customize->add_setting(
						$name,
						array_merge(
							array(
								'sanitize_callback' => array(
									'Customify_Sanitize_Input',
									'sanitize_customizer_input',
								),
							),
							$settings_args
						)
					);

					$control_class_name = 'Customify_Customizer_Control_';
					$tpl_type           = str_replace( '_', ' ', $args['setting_type'] );
					$tpl_type           = str_replace( ' ', '_', ucfirst( $tpl_type ) );
					$control_class_name .= $tpl_type;

					if ( 'js_raw' != $settings_args['type'] ) {
						if ( class_exists( $control_class_name ) ) {
							$wp_customize->add_control( new $control_class_name( $wp_customize, $name, $args ) );
						} else {
							$wp_customize->add_control( new Customify_Customizer_Control_Base( $wp_customize, $name, $args ) );
						}
					}

					if ( $selective_refresh ) {
						$s_id = $selective_refresh['render_callback'];
						if ( is_array( $s_id ) ) {
							if ( is_string( $s_id[0] ) ) {
								$__id = $s_id[0] . '__' . $s_id[1];
							} else {
								$__id = get_class( $s_id[0] ) . '__' . $s_id[1];
							}
						} else {
							$__id = $s_id;
						}
						if ( ! isset( $this->selective_settings[ $__id ] ) ) {
							$this->selective_settings[ $__id ] = array(
								'settings'            => array(),
								'selector'            => $selective_refresh['selector'],
								'container_inclusive' => ( strpos( $__id, 'Customify_Customizer_Auto_CSS' ) === false ) ? true : false,
								'render_callback'     => $s_id,
							);
						}

						$this->selective_settings[ $__id ]['settings'][] = $name;
					}

					break;
			}// end switch.
		} // End loop config.

		// Remove partial for default custom logo and add this by theme custom.
		$wp_customize->selective_refresh->remove_partial( 'custom_logo' );

		$wp_customize->get_section( 'title_tagline' )->panel        = 'header_settings';
		$wp_customize->get_section( 'title_tagline' )->title        = __( 'Logo & Site Identity', 'customify' );
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		// Add selective refresh.
		$wp_customize->get_setting( 'custom_logo' )->transport     = 'postMessage';
		$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		foreach ( $this->selective_settings as $cb => $settings ) {
			reset( $settings['settings'] );
			if ( 'Customify_Builder_Item_Logo__render' == $cb ) {
				$settings['settings'][] = 'custom_logo';
				$settings['settings'][] = 'blogname';
				$settings['settings'][] = 'blogdescription';
			}
			$settings = apply_filters( $cb, $settings );
			$wp_customize->selective_refresh->add_partial( $cb, $settings );
		}

		// For live CSS.
		$wp_customize->add_setting(
			'customify__css',
			array(
				'default'           => '',
				'transport'         => 'postMessage',
				'sanitize_callback' => array( 'Customify_Sanitize_Input', 'sanitize_css_code' ),
			)
		);

		do_action( 'customify/customize/register_completed', $this );
	}

	/**
	 * Definition of panel groups shown in the Customizer side list.
	 *
	 * Each group renders a non-clickable divider section (see
	 * Customify_WP_Customize_Section_Divider) at its `priority`, and every
	 * panel/top-level-section named under `panels` is re-priorit­ised to sit
	 * directly under that divider.
	 *
	 * Extend via the `customify/customizer/panel_groups` filter — e.g. a
	 * companion plugin can append its own panels to the `post_types` group
	 * without forking this list.
	 *
	 * Priority budget: each group has a 100-priority window for its panels
	 * (`group_priority + 10, +20, +30, …`). The bump applied to foreign
	 * panels (+2000) sits comfortably above this range.
	 *
	 * @return array<string, array{title:string, priority:int, panels:string[]}>
	 */
	public function get_panel_groups() {
		$groups = array(
			'general_options' => array(
				'title'    => __( 'General Options', 'customify' ),
				'priority' => 50,
				'panels'   => array(
					// Colors is a top-level SECTION (not a panel) — the
					// get_section() fallback in register_panel_groups() positions
					// it deterministically (priority 60) as the first entry here.
					// Replaces the now-removed empty `styling_panel`.
					'customify_colors',
					'typography_panel',
				),
			),
			'layouts'         => array(
				'title'    => __( 'Layouts', 'customify' ),
				'priority' => 200,
				'panels'   => array(
					'layout_panel',
					'header_settings',
					'footer_settings',
				),
			),
			// Group KEY stays `post_types` — it's a documented
			// `customify/customizer/panel_groups` extension point (companion plugins
			// append panels to it), so only the display title changed to "Content".
			'post_types'      => array(
				'title'    => __( 'Content', 'customify' ),
				'priority' => 350,
				'panels'   => array(
					'blog_panel',
					// `portfolio_panel` is added by the Pro Portfolio module — if the module
					// is inactive the panel won't exist and `get_panel()` returns null below.
					'portfolio_panel',
				),
			),
			// Theme + plugin integration settings, sitting above the WP-core group.
			'plugins'         => array(
				'title'    => __( 'Plugins', 'customify' ),
				'priority' => 600,
				'panels'   => array(
					// WooCommerce registers its own panel (id `woocommerce`) OUTSIDE
					// Customify's config; register_panel_groups() exempts grouped panels
					// from the +2000 foreign bump, so it sits here cleanly. Absent (no-op)
					// when WooCommerce is inactive — Compatibility still anchors the group.
					'woocommerce',
					// Compatibility is the theme's plugin-SUPPORT panel, so it belongs with
					// the plugin integrations — not with the WP-core group.
					'compatibility_panel',
				),
			),
			// Key stays `core_plugins` (kept for any external panel_groups filter);
			// title is now just "Core" — the WP-core items (Menus, Widgets, Homepage
			// Settings, Additional CSS) get bumped into this zone. Compatibility +
			// WooCommerce now live in the "Plugins" group above.
			'core_plugins'    => array(
				'title'    => __( 'Core', 'customify' ),
				'priority' => 1000,
				'panels'   => array(),
			),
		);

		/**
		 * Filter the Customizer panel groups.
		 *
		 * @param array $groups Group definitions keyed by group slug.
		 */
		return apply_filters( 'customify/customizer/panel_groups', $groups );
	}

	/**
	 * Insert group dividers in the Customizer panel list and push everything
	 * not managed by Customify (WordPress core, plugins) into the bottom group.
	 *
	 * Runs at `customize_register` priority 99999 so every other extension has
	 * already registered its panels and sections by the time we sweep.
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function register_panel_groups( $wp_customize ) {
		require_once get_template_directory() . '/inc/customizer/class-customify-section-divider.php';
		$wp_customize->register_section_type( 'Customify_WP_Customize_Section_Divider' );

		$groups = $this->get_panel_groups();

		// 1. Add each divider section + re-priorit­ise its panels to follow it.
		foreach ( $groups as $group_key => $group ) {
			$divider_id = 'customify_divider_' . $group_key;

			$wp_customize->add_section(
				new Customify_WP_Customize_Section_Divider(
					$wp_customize,
					$divider_id,
					array(
						'title'    => $group['title'],
						'priority' => $group['priority'],
					)
				)
			);

			$offset = 10;
			foreach ( $group['panels'] as $name ) {
				$obj = $wp_customize->get_panel( $name );
				if ( ! $obj ) {
					// Fall back to a top-level section with the same name. The
					// "General Options" group relies on this to position the Colors
					// section (`customify_colors`); the upsell strip in upsell.php
					// uses the same shape.
					$section = $wp_customize->get_section( $name );
					if ( $section && empty( $section->panel ) ) {
						$obj = $section;
					}
				}
				if ( $obj ) {
					$obj->priority = $group['priority'] + $offset;
					$offset       += 10;
				}
			}
		}

		// 2. Bump anything not managed by Customify into the Core & Plugins zone.
		$managed       = self::get_config();
		$divider_token = 'customify_divider_';

		// Panels explicitly placed into a group in step 1 keep that position — do
		// NOT bump them even when foreign (e.g. the WooCommerce panel, pinned under
		// Compatibility). Without this, step 1's positioning would be undone here.
		$grouped_panels = array();
		foreach ( $groups as $group ) {
			foreach ( $group['panels'] as $name ) {
				$grouped_panels[ $name ] = true;
			}
		}

		foreach ( $wp_customize->panels() as $panel_id => $panel ) {
			if ( isset( $managed[ 'panel|' . $panel_id ] ) || isset( $grouped_panels[ $panel_id ] ) ) {
				continue;
			}
			$panel->priority = (int) $panel->priority + 2000;
		}

		foreach ( $wp_customize->sections() as $section_id => $section ) {
			// Sections nested under a panel only appear when their parent is
			// expanded; they have no effect on the top-level order.
			if ( ! empty( $section->panel ) ) {
				continue;
			}
			if ( isset( $managed[ 'section|' . $section_id ] ) ) {
				continue;
			}
			// Skip the dividers we just registered.
			if ( strpos( $section_id, $divider_token ) === 0 ) {
				continue;
			}
			$section->priority = (int) $section->priority + 2000;
		}
	}

	/**
	 * Print the JS section constructor for the divider type.
	 *
	 * Why: by default WP's wp.customize.Section sets `display: none` on any
	 * section whose `isContextuallyActive()` returns false — and a section with
	 * zero controls is, by definition, not contextually active. The divider
	 * carries no controls (it's purely visual), so the default constructor
	 * would always hide it. We extend the prototype to force the section to
	 * count as contextually active.
	 */
	public function print_divider_section_constructor() {
		?>
		<script>
		( function () {
			if ( typeof wp === 'undefined' || ! wp.customize || ! wp.customize.Section ) {
				return;
			}
			wp.customize.sectionConstructor['customify-divider'] = wp.customize.Section.extend( {
				isContextuallyActive: function () {
					return true;
				}
			} );
		} )();
		</script>
		<?php
	}

}
