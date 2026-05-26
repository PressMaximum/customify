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
					'narrow'         => __( 'Narrow', 'customify' ),
				),
				'show_default' => true,
			)
		);

		// Page Title Layout (formerly "Display" under the Page Header tab).
		// Promoted into the Layout tab next to Content Layout because it's
		// the same kind of decision — where does the page title render —
		// and the rest of "Page Header" tab (Transparent Header) is a
		// secondary concern. Stays as a `select` whose values feed
		// `_customify_page_header_display`, so existing data still resolves.
		$this->field_builder->add_field(
			array(
				'title'   => __( 'Page Title Layout', 'customify' ),
				'name'    => 'page_header_display',
				'tab'     => 'layout',
				'type'    => 'select',
				'choices' => array(
					'default'  => __( 'Inherit from customize settings', 'customify' ),
					'normal'   => __( 'Default - inside main content', 'customify' ),
					'cover'    => __( 'Cover', 'customify' ),
					'titlebar' => __( 'Titlebar', 'customify' ),
					'none'     => __( 'Hide', 'customify' ),
				),
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
		// Display order groups header-related toggles together (Header →
		// Header Top/Main/Bottom), then page-content toggles (Page Title,
		// Content Vertical Padding), then footer toggles below. Mirrors the
		// block-editor sidebar order in src/backend/page-settings/index.js
		// so users see the same layout in classic + block editors.
		$disable_elements_choices = array(
			'disable_header'        => __( 'Disable Header', 'customify' ),
			'disable_header_top'    => __( 'Disable Header Top', 'customify' ),
			'disable_header_main'   => __( 'Disable Header Main', 'customify' ),
			'disable_header_bottom' => __( 'Disable Header Bottom', 'customify' ),
			'disable_page_title'    => __( 'Disable Title', 'customify' ),
			// Strips the vertical padding from #main / #sidebar-primary /
			// #sidebar-secondary so full-width landing-page sections sit
			// flush against the header and footer. Default body cascade
			// keeps the padding so content pages still get reading-room.
			'disable_content_vertical_padding' => __( 'Disable Content Vertical Padding', 'customify' ),
		);

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

		// Page Title Layout was previously registered here under the
		// 'page_header' tab as "Display". Moved up into the 'layout' tab
		// next to Content Layout (see the field above) and renamed
		// "Page Title Layout" so the dropdown title describes what it
		// actually controls. The underlying meta key
		// `_customify_page_header_display` is unchanged — saved values on
		// existing sites still load correctly.

		$this->field_builder->add_field(
			array(
				'title'   => __( 'Transparent Header', 'customify' ),
				'name'    => 'header_transparent_display',
				'tab'     => 'page_header',
				'type'    => 'select',
				'choices' => array(
					'default' => __( 'Inherit from Customizer settings', 'customify' ),
					'show'    => __( 'Force transparent', 'customify' ),
					'hide'    => __( 'Force opaque', 'customify' ),
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
		wp_enqueue_script( 'customify-metabox', esc_url( get_template_directory_uri() ) . '/build/js/backend/admin/metabox.js', array( 'jquery' ), Customify::$version, true );
		wp_enqueue_style( 'customify-metabox', esc_url( get_template_directory_uri() ) . '/build/css/backend/admin/metabox.css', false, Customify::$version );
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
	 * Returns true when the current screen is the block editor.
	 *
	 * @return bool
	 */
	private function is_block_editor() {
		$screen = get_current_screen();
		return $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	/**
	 * Adds the meta box container.
	 * Only rendered in the classic editor; the block editor uses the React plugin instead.
	 *
	 * @param string $post_type Post Type.
	 */
	public function add_meta_box( $post_type ) {
		if ( $this->is_block_editor() ) {
			return;
		}

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

		$kses_safe = static function ( $v ) {
			// PHP 8.1+ deprecates passing null to wp_kses_post (internal preg_replace).
			return wp_kses_post( null === $v ? '' : (string) $v );
		};

		foreach ( $settings as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$value = $kses_safe( $value );
			} else {
				$value = array_map( $kses_safe, $value );
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
