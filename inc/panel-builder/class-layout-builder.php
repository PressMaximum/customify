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

		$new_template_data = array();
		$allowed_keys      = array_keys( $this->get_builder_setting_defaults( $id ) );

		// Prefer client-supplied data (captures live, unpublished customizer state);
		// fall back to theme_mod-based capture so the endpoint stays compatible
		// with callers that don't send a `data` payload.
		if ( ! empty( $_POST['data'] ) ) {
			$client_raw  = wp_unslash( $_POST['data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$client_data = json_decode( $client_raw, true );
			if ( is_array( $client_data ) ) {
				$allowed_map = array_flip( $allowed_keys );
				foreach ( $client_data as $name => $value ) {
					if ( ! isset( $allowed_map[ $name ] ) ) {
						continue;
					}
					$value = self::deep_sanitize_template_value( $value );
					if ( '' === $value || null === $value || ( is_array( $value ) && empty( $value ) ) ) {
						continue;
					}
					$new_template_data[ $name ] = $value;
				}
			}
		}

		if ( empty( $new_template_data ) ) {
			$config = call_user_func_array( $fn, array() );
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
		wp_send_json_success(
			array(
				'key_id' => $key_id,
				'name'   => $save_name,
				'data'   => $new_template_data,
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

		// Accept id/name from POST (safer than GET for state-reading admin actions).
		$id   = isset( $_REQUEST['id'] )   ? sanitize_key( wp_unslash( $_REQUEST['id'] ) )   : false;
		$name = isset( $_REQUEST['name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['name'] ) ) : false;

		// Validate $id against registered builders to prevent arbitrary option probing.
		if ( ! $id || ! array_key_exists( $id, $this->registered_builders ) ) {
			wp_send_json_error( __( 'Invalid builder id', 'customify' ) );
		}

		$theme_name  = wp_get_theme()->get( 'Name' );
		$option_name = "{$theme_name}_{$id}_saved_templates";
		$data        = get_option( $option_name );
		$payload     = null;
		if ( $name ) {
			if ( is_array( $data ) && isset( $data[ $name ] ) ) {
				$payload = array_filter( (array) $data[ $name ]['data'] );
			}
		} else {
			$payload = $data;
		}

		wp_send_json_success( $payload );
	}

	/**
	 *  Get all builders registered.
	 *
	 * @return array
	 */
	public function get_builders() {
		$builders = array();
		foreach ( $this->registered_builders as $id => $builder ) {
			$config                     = $builder->get_config();
			$config['items']            = apply_filters( 'customify/builder/' . $id . '/items', $this->get_builder_items( $id ) );
			$config['rows']             = apply_filters( 'customify/builder/' . $id . '/rows', $builder->get_rows_config() );
			$config['saved_templates']  = $this->get_saved_templates( $id );
			$config['setting_defaults'] = $this->get_builder_setting_defaults( $id );
			$builders[ $id ]            = $config;
		}

		return $builders;
	}

	/**
	 * Read the saved templates option for a given builder.
	 *
	 * Newest first so the React UI can render them in reverse-chronological order
	 * without an extra reverse step on the client.
	 *
	 * @param string $builder_id Builder id (e.g. 'header', 'footer').
	 * @return array<string,array{name:string,image:string,data:array}>
	 */
	public function get_saved_templates( $builder_id ) {
		$theme_name  = wp_get_theme()->get( 'Name' );
		$option_name = "{$theme_name}_{$builder_id}_saved_templates";
		$saved       = get_option( $option_name );
		if ( ! is_array( $saved ) ) {
			return array();
		}
		return array_reverse( $saved, true );
	}

	/**
	 * Deep-sanitize a value supplied by the client when saving a template.
	 *
	 * Keys are sanitized as setting names; scalars become sanitized text;
	 * nested arrays are walked recursively. Values are stored as PHP arrays
	 * so reading them back via wp_localize_script produces the expected JS
	 * shape on Load.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private static function deep_sanitize_template_value( $value ) {
		if ( is_array( $value ) ) {
			$out = array();
			foreach ( $value as $k => $v ) {
				// Numeric keys preserve order (e.g. item lists); string keys are sanitized.
				$key       = is_int( $k ) ? $k : sanitize_text_field( (string) $k );
				$out[ $key ] = self::deep_sanitize_template_value( $v );
			}
			return $out;
		}
		if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			// Use wp_kses_post so styling values (e.g. CSS strings, rgba colors) survive.
			return wp_kses_post( $value );
		}
		return '';
	}

	/**
	 * Build a `name => default` map of every setting that belongs to a builder.
	 *
	 * Used by the React Templates panel to reset settings on Load: keys present
	 * in the template are written from the template; keys absent from the
	 * template are reset to their default so loading effectively replaces the
	 * entire builder state.
	 *
	 * @param string $builder_id Builder id (e.g. 'header', 'footer').
	 * @return array<string,mixed>
	 */
	public function get_builder_setting_defaults( $builder_id ) {
		if ( ! isset( $this->registered_builders[ $builder_id ] ) ) {
			return array();
		}
		$config   = call_user_func_array( array( $this->registered_builders[ $builder_id ], '_customize' ), array() );
		$defaults = array();
		foreach ( (array) $config as $field ) {
			if ( ! is_array( $field ) || empty( $field['name'] ) || empty( $field['type'] ) ) {
				continue;
			}
			// Skip wrappers and pure UI elements that have no underlying setting.
			if ( in_array( $field['type'], array( 'panel', 'section', 'custom_html', 'heading' ), true ) ) {
				continue;
			}
			$defaults[ $field['name'] ] = isset( $field['default'] ) ? $field['default'] : '';
		}
		return $defaults;
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
