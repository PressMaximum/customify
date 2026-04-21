<?php

/**
 * Add Panel Builder to WP Customize
 *
 * Class Customify_Customize_Layout_Builder
 */
class Customify_Customize_Layout_Builder {
	static $_instance;
	private $registered_items    = array();
	private $registered_builders = array();

	/**
	 * Initial
	 */
	function init() {

		do_action( 'customify/customize-builder/init' );

		if ( is_admin() ) {
			add_action( 'customize_controls_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'wp_ajax_customify_builder_save_template', array( $this, 'ajax_save_template' ) );
			add_action( 'wp_ajax_customify_builder_export_template', array( $this, 'ajax_export_template' ) );
			add_filter( 'customize_section_active', array( $this, 'hide_builder_item_sections' ), 20, 2 );
		}

	}


	static function verify_nonce( ) {
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : '';
		if ( ! $nonce ) {
			$nonce = isset( $_REQUEST['_nonce'] ) ? sanitize_text_field( $_REQUEST['_nonce'] ) : '';
		}
		return wp_verify_nonce( $nonce, 'Customify_Layout_Builder' );
	}

	/**
	 * Register builder panel
	 *
	 * @see Customify_Customize_Builder_Panel
	 *
	 * @param string                            $id    ID of panel.
	 * @param Customify_Customize_Builder_Panel $class Panel class name.
	 *
	 * @return bool
	 */
	public function register_builder( $id, $class ) {
		if ( ! isset( $id ) ) {
			return false;
		}

		if ( ! is_object( $class ) ) {
			if ( ! class_exists( $class ) ) {
				return false;
			}

			$class = new $class();
		}

		if ( ! $class instanceof Customify_Customize_Builder_Panel ) {
			$name = get_class( $class );
			_doing_it_wrong( $name, sprintf( __( 'Class <strong>%s</strong> do not extends class <strong>Customify_Customize_Builder_Panel</strong>.', 'customify' ), $name ), '1.0.0' );
			return false;
		}

		add_filter( 'customify/customizer/config', array( $class, '_customize' ), 35, 2 );
		$this->registered_builders[ $id ] = $class;
	}

	/**
	 * Get builder class
	 *
	 * @since 0.2.9
	 *
	 * @param string $builder_id
	 * @return object|bool
	 */
	public function get_builder( $builder_id ) {
		return isset( $this->registered_builders[ $builder_id ] ) ? $this->registered_builders[ $builder_id ] : false;
	}


	/**
	 * Add an item builder to panel
	 *
	 * @see Customify_Customize_Layout_Builder::register_builder();
	 *
	 * @param string $builder_id Id of panel.
	 * @param object $class      Class to handle this item.
	 *
	 * @return bool
	 */
	function register_item( $builder_id, $class ) {
		if ( ! $builder_id ) {
			return false;
		}

		if ( is_object( $class ) ) {

		} else {
			if ( ! class_exists( $class ) ) {
				return false;
			}
			$class = new $class();
		}

		if ( ! isset( $this->registered_items[ $builder_id ] ) ) {
			$this->registered_items[ $builder_id ] = array();
		}

		$this->registered_items[ $builder_id ][ $class->id ] = $class;

		return true;

	}

	/**
	 * Get all items for builder panel
	 *
	 * @param string $builder_id Id of panel.
	 *
	 * @return array|mixed
	 */
	function get_builder_items( $builder_id ) {
		if ( ! $builder_id ) {
			return apply_filters( 'customify/builder/' . $builder_id . '/items', array() );
		}
		if ( ! isset( $this->registered_items[ $builder_id ] ) ) {
			return apply_filters( 'customify/builder/' . $builder_id . '/items', array() );
		}
		$items = array();
		foreach ( $this->registered_items[ $builder_id ] as $name => $obj ) {
			if ( method_exists( $obj, 'item' ) ) {
				$item                 = $obj->item();
				$items[ $item['id'] ] = $item;
			}
		}
		$items = apply_filters( 'customify/builder/' . $builder_id . '/items', $items );

		return $items;
	}

	/**
	 * Get all customize settings of all items for builder panel
	 *
	 * @param string               $builder_id   Id of panel.
	 * @param WP_Customize_Manager $wp_customize WP Customize.
	 *
	 * @return array|bool
	 */
	public function get_items_customize( $builder_id, $wp_customize = null ) {
		if ( ! $builder_id ) {
			return false;
		}
		if ( ! isset( $this->registered_items[ $builder_id ] ) ) {
			return false;
		}
		$items = array();
		foreach ( $this->registered_items[ $builder_id ] as $name => $obj ) {
			if ( method_exists( $obj, 'customize' ) ) {
				$item = $obj->customize( $wp_customize );
				if ( is_array( $item ) ) {
					foreach ( $item as $it ) {
						$items[] = $it;
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Get a builder item for builder panel
	 *
	 * @param string $builder_id Id of panel.
	 * @param string $item_id    Builder item id.
	 *
	 * @return bool
	 */
	function get_builder_item( $builder_id, $item_id ) {
		if ( ! $builder_id ) {
			return false;
		}
		if ( ! isset( $this->registered_items[ $builder_id ] ) ) {
			return false;
		}

		if ( ! isset( $this->registered_items[ $builder_id ][ $item_id ] ) ) {
			return false;
		}

		return $this->registered_items[ $builder_id ][ $item_id ];
	}

	/**
	 * Collect every section ID that belongs to a builder item across all registered builders.
	 *
	 * @return array<string,true>  Section ID → true map for fast lookup.
	 */
	public function get_all_item_sections() {
		$sections = array();
		foreach ( $this->registered_items as $builder_id => $items ) {
			foreach ( $items as $obj ) {
				if ( ! method_exists( $obj, 'item' ) ) {
					continue;
				}
				$item = $obj->item();
				foreach ( array( 'section', 'layout_section' ) as $key ) {
					if ( ! empty( $item[ $key ] ) ) {
						$sections[ $item[ $key ] ] = true;
					}
				}
			}
		}
		return $sections;
	}

	/**
	 * Hide all builder item sections from the Customizer panel navigation.
	 * They are only accessible via the builder UI (gear icon on placed items).
	 *
	 * @param bool                 $active
	 * @param WP_Customize_Section $section
	 * @return bool
	 */
	public function hide_builder_item_sections( $active, $section ) {
		// Never override WP sidebar section active state via PHP.
		// Sidebar sections (type 'sidebar') control widget rendering and data sync in the
		// Customizer preview — forcing them inactive breaks widget display and causes reloads.
		// JS handles hiding them from the UI via permanentlyHideSection instead.
		if ( $section instanceof WP_Customize_Sidebar_Section ) {
			return $active;
		}
		$item_sections = $this->get_all_item_sections();
		if ( isset( $item_sections[ $section->id ] ) ) {
			return false;
		}
		return $active;
	}

	/**
	 * Handle event save template
	 */
	function ajax_save_template() {

		if ( ! self::verify_nonce() ) {
			wp_send_json_error( __( 'Access denied', 'customify' ) );
		}

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( __( 'Access denied', 'customify' ) );
		}

		$id        = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : false;
		$control   = isset( $_POST['control'] ) ? sanitize_text_field( $_POST['control'] ) : '';
		$save_name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		if ( ! $save_name ) {
			$save_name = sprintf( __( 'Saved %s', 'customify' ), date_i18n( 'Y-m-d H:i:s' ) );
		}
		$fn = false;
		if ( ! isset( $this->registered_builders[ $id ] ) ) {
			wp_send_json_error( __( 'No Support', 'customify' ) );
		} else {
			$fn = array( $this->registered_builders[ $id ], '_customize' );
		}

		$theme_name  = wp_get_theme()->get( 'Name' );
		$option_name = "{$theme_name}_{$id}_saved_templates";

		$saved_templates = get_option( $option_name );
		if ( ! is_array( $saved_templates ) ) {
			$saved_templates = array();
		}

		if ( isset( $_POST['remove'] ) ) {
			$remove = sanitize_text_field( $_POST['remove'] );
			if ( isset( $saved_templates[ $remove ] ) ) {
				unset( $saved_templates[ $remove ] );
			}

			update_option( $option_name, $saved_templates );
			wp_send_json_success();
		}

		$config            = call_user_func_array( $fn, array() );
		$new_template_data = array();

		foreach ( $config as $k => $field ) {
			if ( 'panel' != $field['type'] && 'section' != $field['type'] ) {
				$name  = $field['name'];
				$value = get_theme_mod( $name );
				if ( is_array( $value ) ) {
					$value = array_filter( $value );
				}
				if ( $value && ! empty( $value ) ) {
					$new_template_data[ $name ] = $value;
				}
			}
		}

		if ( ! $save_name ) {
			$key_id    = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			$save_name = sprintf( __( 'Saved %s', 'customify' ), $key_id );
		} else {
			$key_id = $save_name;
		}

		$saved_templates[ $key_id ] = array(
			'name'  => $save_name,
			'image' => '',
			'data'  => $new_template_data,
		);

		update_option( $option_name, $saved_templates );
		$html = '<li class="saved_template li-boxed" data-control-id="' . esc_attr( $control ) . '" data-id="' . esc_attr( $key_id ) . '" data-data="' . esc_attr( wp_json_encode( $new_template_data ) ) . '">' . esc_html( $save_name ) . ' <a href="#" class="load-tpl">' . __( 'Load', 'customify' ) . '</a><a href="#" class="remove-tpl">' . __( 'Remove', 'customify' ) . '</a></li>'; // WPCS: XSS OK.
		wp_send_json_success(
			array(
				'key_id' => $key_id,
				'name'   => $save_name,
				'li'     => $html,
			)
		);
		die();
	}

	/**
	 * Handle event export template
	 */
	function ajax_export_template() {
		if ( ! self::verify_nonce() ) {
			wp_send_json_error( __( 'Access denied', 'customify' ) );
		}
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( __( 'Access denied', 'customify' ) );
		}
		$id   = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : false;
		$name = isset( $_GET['name'] ) ? sanitize_text_field( wp_unslash( $_GET['name'] ) ) : false;

		$theme_name  = wp_get_theme()->get( 'Name' );
		$option_name = "{$theme_name}_{$id}_saved_templates";
		$data        = get_option( $option_name );
		$var         = null;
		if ( $name ) {
			if ( isset( $data[ $name ] ) ) {
				$var = $data[ $name ]['data'];
				$var = array_filter( $var );
			}
		} else {
			$var = $data;
		}
		var_export( $var ); // phpcs:ignore
		die();
	}

	/**
	 *  Get all builders registered.
	 *
	 * @return array
	 */
	public function get_builders() {
		$builders = array();
		foreach ( $this->registered_builders as $id => $builder ) {
			$config          = $builder->get_config();
			$config['items'] = apply_filters( 'customify/builder/' . $id . '/items', $this->get_builder_items( $id ) );
			$config['rows']  = apply_filters( 'customify/builder/' . $id . '/rows', $builder->get_rows_config() );
			$builders[ $id ] = $config;
		}

		return $builders;
	}

	/**
	 * Enqueue the React header and footer builders and pass builder config to JS.
	 */
	function scripts() {
		$builders_data = array(
			'builders' => $this->get_builders(),
			'is_rtl'   => is_rtl(),
			'nonce'    => wp_create_nonce( 'Customify_Layout_Builder' ),
		);

		// Header builder.
		$header_asset_file = get_template_directory() . '/build/js/backend/header-builder.asset.php';
		if ( file_exists( $header_asset_file ) ) {
			$asset = require $header_asset_file;
			wp_enqueue_script(
				'customify-header-builder',
				esc_url( get_template_directory_uri() ) . '/build/js/backend/header-builder.js',
				array_merge( $asset['dependencies'], array( 'customize-controls' ) ),
				$asset['version'],
				true
			);
			wp_enqueue_style(
				'customify-header-builder',
				esc_url( get_template_directory_uri() ) . '/build/css/backend/style-header-builder.css',
				array( 'dashicons', 'wp-components' ),
				$asset['version']
			);
			wp_localize_script( 'customify-header-builder', 'Customify_Layout_Builder', $builders_data );
			wp_set_script_translations( 'customify-header-builder', 'customify' );
		}

		// Footer builder.
		$footer_asset_file = get_template_directory() . '/build/js/backend/footer-builder.asset.php';
		if ( file_exists( $footer_asset_file ) ) {
			$asset = require $footer_asset_file;
			wp_enqueue_script(
				'customify-footer-builder',
				esc_url( get_template_directory_uri() ) . '/build/js/backend/footer-builder.js',
				array_merge( $asset['dependencies'], array( 'customize-controls' ) ),
				$asset['version'],
				true
			);
			wp_enqueue_style(
				'customify-footer-builder',
				esc_url( get_template_directory_uri() ) . '/build/css/backend/style-footer-builder.css',
				array( 'dashicons', 'wp-components' ),
				$asset['version']
			);
			// Reuse the same global; footer builder reads from it too.
			wp_localize_script( 'customify-footer-builder', 'Customify_Layout_Builder', $builders_data );
			wp_set_script_translations( 'customify-footer-builder', 'customify' );
		}

	}

	static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Panel Builder Template
	 */
	function template() {
		require_once get_template_directory() . '/inc/panel-builder/v1/templates/rows.php';
		require_once get_template_directory() . '/inc/panel-builder/v2/templates/rows.php';
		?>
		<script type="text/html" id="tmpl-customify--builder-panel">
			<div class="customify--customize-builder">
				<div class="customify--cb-inner">
					<div class="customify--cb-header">
						<div class="customify--cb-devices-switcher">
						</div>
						<div class="customify--cb-actions">
							<?php do_action( 'customify/builder-panel/actions-buttons' ); ?>
							<a data-id="{{ data.id }}_templates" class="focus-section button button-secondary" href="#"><?php _e( 'Templates', 'customify' ); ?></a>
							<a class="button button-secondary customify--panel-close" href="#">
								<span class="close-text"><?php _e( 'Close', 'customify' ); ?></span>
								<span class="panel-name-text">{{ data.title }}</span>
							</a>
						</div>
					</div>
					<div class="customify--cb-body"></div>
				</div>
			</div>
		</script>


		<script type="text/html" id="tmpl-customify--cb-item">
			<div class="grid-stack-item item-from-list for-s-{{ data.section }}"
				title="{{ data.name }}"
				data-id="{{ data.id }}"
				data-section="{{ data.section }}"
				data-control="{{ data.control }}"
				data-gs-x="{{ data.x }}"
				data-gs-y="{{ data.y }}"
				data-gs-width="{{ data.width }}"
				data-df-width="{{ data.width }}"
				data-gs-height="1"
			>
				<div class="item-tooltip" data-section="{{ data.section }}">{{ data.name }}</div>
				<div class="grid-stack-item-content">
					<span class="customify--cb-item-name" data-section="{{ data.section }}">{{ data.name }}</span>
					<span class="customify--cb-item-remove customify-cb-icon"></span>
					<span class="customify--cb-item-setting customify-cb-icon" data-section="{{ data.section }}"></span>
				</div>
			</div>
		</script>

		<?php
		if ( ! apply_filters( 'customify/is_pro_activated', false ) ) {
			?>
			<script type="text/html" id="customify-upsell-tmpl">
				<p class="customify-upsell-panel"><?php _e( 'Enjoy building? Upgrade to <a target="_blank" href="https://pressmaximum.com/customify/pro-upgrade/?utm_source=theme_dashboard&utm_medium=links&utm_campaign=panel_text">Customify Pro</a> to get more builder items and other premium features</a>.', 'customify' ); ?></p>
			</script>
			<?php
		}
	}

}

/**
 * Alias of class Customify_Customize_Layout_Builder
 *
 * @see Customify_Customize_Layout_Builder
 *
 * @return Customify_Customize_Layout_Builder
 */
function Customify_Customize_Layout_Builder() {
	return Customify_Customize_Layout_Builder::get_instance();
}
