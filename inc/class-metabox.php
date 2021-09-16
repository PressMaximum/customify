<?php

if ( is_admin() ) {
	// Calls the class on the post edit screen.
	add_action( 'load-post.php', array( 'Customify_MetaBox', 'get_instance' ) );
	add_action( 'load-post-new.php', array( 'Customify_MetaBox', 'get_instance' ) );
}

/**
 * The Metabox.
 */
class Customify_MetaBox {

	public static $_instance = null;
	/**
	 * @see Customify_Form_Fields
	 * @var Customify_Form_Fields null
	 */
	public $field_builder = null;

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			add_action( 'add_meta_boxes', array( self::$_instance, 'add_meta_box' ) );
			add_action( 'save_post', array( self::$_instance, 'save' ) );
			add_action( 'admin_enqueue_scripts', array( self::$_instance, 'scripts' ) );
			require_once get_template_directory() . '/inc/class-metabox-fields.php';
			self::$_instance->field_builder = new Customify_Form_Fields();
			self::$_instance->fields_config();
			do_action( 'customify/metabox/init', self::$_instance );

		}

		return self::$_instance;
	}

	/**
	 * Add metabox fields
	 *
	 * @since 0.2.2
	 */
	function fields_config() {

		$this->field_builder->add_tab(
			'layout',
			array(
				'title' => __( 'Layout', 'customify' ),
				'icon'  => 'dashicons dashicons-grid-view',
			)
		);

		$this->field_builder->add_tab(
			'page_header',
			array(
				'title' => __( 'Page Header', 'customify' ),
				'icon'  => 'dashicons dashicons-editor-kitchensink',
			)
		);

		$this->field_builder->add_field(
			array(
				'title'        => __( 'Content Layout', 'customify' ),
				'name'         => 'content_layout',
				'tab'          => 'layout',
				'type'         => 'select',
				'choices'      => array(
					'full-width'     => __( 'Full Width', 'customify' ),
					'full-stretched' => __( 'Full Width - Stretched', 'customify' ),
				),
				'show_default' => true,
			)
		);

		$this->field_builder->add_field(
			array(
				'title'         => __( 'Sidebar', 'customify' ),
				'name'          => 'sidebar',
				'tab'           => 'layout',
				'type'          => 'select',
				'choices'       => customify_get_config_sidebar_layouts(),
				'show_default'  => true,
				'default_label' => __( 'Inherit from customize settings', 'customify' ),
			)
		);
		$disable_elements_choices = array(
			'disable_header'     => __( 'Disable Header', 'customify' ),
			'disable_page_title' => __( 'Disable Title', 'customify' ),
		);

		$disable_elements_choices['disable_header_top']    = __( 'Disable Header Top', 'customify' );
		$disable_elements_choices['disable_header_main']   = __( 'Disable Header Main', 'customify' );
		$disable_elements_choices['disable_header_bottom'] = __( 'Disable Header Bottom', 'customify' );

		if ( class_exists( 'Customify_Pro' ) ) {
			$disable_elements_choices['disable_footer_top'] = __( 'Disable Footer Top', 'customify' );
		}
		$disable_elements_choices['disable_footer_main']   = __( 'Disable Footer Main', 'customify' );
		$disable_elements_choices['disable_footer_bottom'] = __( 'Disable Footer Bottom', 'customify' );
		$this->field_builder->add_field(
			array(
				'title'   => __( 'Disable Elements', 'customify' ),
				'name'    => 'disable_elements',
				'tab'     => 'layout',
				'type'    => 'multiple_checkbox',
				'choices' => $disable_elements_choices,
			)
		);

		$this->field_builder->add_field(
			array(
				'title'   => __( 'Display', 'customify' ),
				'name'    => 'page_header_display',
				'tab'     => 'page_header',
				'type'    => 'select',
				'choices' => array(
					'default'  => __( 'Inherit from customize settings', 'customify' ),
					'normal'   => __( 'Default', 'customify' ),
					'cover'    => __( 'Cover', 'customify' ),
					'titlebar' => __( 'Titlebar', 'customify' ),
					'none'     => __( 'Hide', 'customify' ),
				),
			)
		);

		if ( Customify_Breadcrumb::get_instance()->support_plugins_active() ) {
			$this->field_builder->add_tab(
				'breadcrumb',
				array(
					'title' => __( 'Breadcrumb', 'customify' ),
					'icon'  => 'dashicons dashicons-admin-links',
				)
			);
			$this->field_builder->add_field(
				array(
					'title'   => __( 'Breadcrumb', 'customify' ),
					'tab'     => 'breadcrumb',
					'name'    => 'breadcrumb_display',
					'type'    => 'select',
					'choices' => array(
						'default' => __( 'Inherit from customize settings', 'customify' ),
						'hide'    => __( 'Hide', 'customify' ),
						'show'    => __( 'Show', 'customify' ),
					),
				)
			);
		}

	}

	public function scripts( $hook ) {
		if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
			return;
		}
		$suffix = Customify()->get_asset_suffix();
		wp_enqueue_script( 'customify-metabox', esc_url( get_template_directory_uri() ) . '/assets/js/admin/metabox' . $suffix . '.js', array( 'jquery' ), Customify::$version, true );
		wp_enqueue_style( 'customify-metabox', esc_url( get_template_directory_uri() ) . '/assets/css/admin/metabox' . $suffix . '.css', false, Customify::$version );
	}

	public function get_support_post_types() {
		$args = array(
			'public' => true,
		);

		$output     = 'names'; // Names or objects, note names is the default.
		$operator   = 'and'; // Can use 'and' or 'or'.
		$post_types = get_post_types( $args, $output, $operator );

		return array_values( $post_types );
	}

	/**
	 * Adds the meta box container.
	 *
	 * @param string $post_type Post Type.
	 */
	public function add_meta_box( $post_type ) {
		// Limit meta box to certain post types.
		$post_types = $this->get_support_post_types();
		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'customify_page_settings',
				__( 'Customify Settings', 'customify' ),
				array( $this, 'render_meta_box_content' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 * @return int|bool
	 */
	public function save( $post_id ) {

		/**
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		if ( ! isset( $_POST['customify_page_settings_nonce'] ) ) { // Check if our nonce is set.
			return $post_id;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['customify_page_settings_nonce'] ) );

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'customify_page_settings' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == get_post_type( $post_id ) ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/**
		 * @since 0.2.2
		 */
		$settings = $this->field_builder->get_submitted_values();

		foreach ( $settings as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$value = wp_kses_post( $value );
			} else {
				$value = array_map( 'wp_kses_post', $value );
			}
			// Update the meta field.
			update_post_meta( $post_id, '_customify_' . $key, $value );
		}

	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'customify_page_settings', 'customify_page_settings_nonce' );
		$values = array();
		foreach ( $this->field_builder->get_all_fields() as $key => $f ) {
			if ( 'multiple_checkbox' == $f['type'] ) {
				foreach ( (array) $f['choices'] as $_key => $label ) {
					$value           = get_post_meta( $post->ID, '_customify_' . $_key, true );
					$values[ $_key ] = $value;
				}
			} elseif ( $f['name'] ) {
				$values[ $f['name'] ] = get_post_meta( $post->ID, '_customify_' . $f['name'], true );
			}
		}

		$this->field_builder->set_values( $values );
		$this->field_builder->render();

	}
}
