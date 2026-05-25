<?php

class Customify_Fonts {

	function __construct() {
		// Add ajax handle.
		add_action( 'wp_ajax_customify/customizer/ajax/fonts', array( $this, 'ajax_fonts' ) );
	}

	/**
	 * AJAX: return the registered font catalogue (default + Google Web
	 * Fonts) for the Customizer's typography picker. Gated by the
	 * Customizer preview nonce + `customize` capability — the picker
	 * only runs inside the Customizer iframe.
	 */
	function ajax_fonts() {
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

		// Group order: theme → library → normal → google. Theme is the
		// theme.json-declared canonical set, surfaced first. Library is
		// user-uploaded via wp-admin Font Library. On name collisions
		// the EARLIER group wins (the user-mental-model "what's at the
		// top is what I'm trying to use"). Concretely:
		//   - Theme wins over Library + Google
		//   - Library wins over Google
		$theme   = ( new Customify_Customizer_Theme_Fonts() )->get_for_picker();
		$library = ( new Customify_Customizer_Font_Library() )->get_for_picker();
		$google  = $this->get_google_fonts();

		if ( ! empty( $theme ) ) {
			if ( is_array( $library ) ) {
				$library = array_diff_key( $library, $theme );
			}
			if ( is_array( $google ) ) {
				$google = array_diff_key( $google, $theme );
			}
		}
		if ( ! empty( $library ) && is_array( $google ) ) {
			$google = array_diff_key( $google, $library );
		}

		$fonts = array();
		if ( ! empty( $theme ) ) {
			$fonts['theme'] = array(
				'title' => __( 'Theme', 'customify' ),
				'fonts' => $theme,
			);
		}
		// Always show the WP Font Library group label so users see the
		// integration point exists. When no fonts are activated, emit
		// a disabled placeholder explaining how to add them — picker
		// JS renders `_disabled: true` entries as <option disabled>
		// and reads `label` for the visible text.
		$fonts['library'] = array(
			'title' => __( 'WP Font Library', 'customify' ),
			'fonts' => ! empty( $library )
				? $library
				: array(
					'__customify_no_library_fonts__' => array(
						'label'     => __( 'No fonts activated — manage via Appearance → Fonts', 'customify' ),
						'_disabled' => true,
					),
				),
		);
		$fonts['normal'] = array(
			'title' => __( 'Default Web Fonts', 'customify' ),
			'fonts' => $this->get_normal_fonts(),
		);
		$fonts['google'] = array(
			'title' => __( 'Google Web Fonts', 'customify' ),
			'fonts' => $google,
		);

		wp_send_json_success( apply_filters( 'customify/list-fonts', $fonts ) );
	}

	/**
	 * Get Google WebFont fonts from json file
	 *
	 * @return array
	 */
	function get_google_fonts() {
		global $wp_filesystem;
		WP_Filesystem();
		$file = get_template_directory() . '/build/fonts/google-fonts.json';
		if ( file_exists( $file ) ) {
			$file_contents = $wp_filesystem->get_contents( $file );
			return json_decode( $file_contents, true );
		}

		return array();
	}

	/**
	 * Default fonts
	 *
	 * @return array
	 */
	function get_normal_fonts() {
		$fonts = array(
			'Arial'       => array(
				'family'   => 'Arial',
				'category' => ' sans-serif',
			),
			'Baskerville' => array(
				'family'   => 'Baskerville',
				'category' => 'serif',
			),
			'Palatino'    => array(
				'family'   => 'Palatino',
				'category' => 'serif',
			),

			'Bodoni MT' => array(
				'family'   => 'Bodoni MT',
				'category' => 'serif',
			),

			'Georgia' => array(
				'family'   => 'Georgia',
				'category' => 'serif',
			),

			'Century Gothic' => array(
				'family'   => 'Century Gothic',
				'category' => 'sans-serif',
			),

			'Tahoma' => array(
				'family'   => 'Tahoma',
				'category' => 'sans-serif',
			),

			'Arial Narrow' => array(
				'family'   => 'Arial Narrow',
				'category' => ' sans-serif',
			),

			'Trebuchet MS' => array(
				'family'   => 'Trebuchet MS',
				'category' => ' sans-serif',
			),

			'Consolas' => array(
				'family'   => 'Consolas',
				'category' => ' sans-serif',
			),

		);

		return $fonts;
	}
}

new Customify_Fonts();
