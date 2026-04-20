<?php
/**
 * Support Gutenberg Editor.
 *
 * @since 0.2.6
 */
class Customify_Editor {
	private $action      = 'customify_load_editor_style';
	private $editor_file = 'assets/css/admin/editor.css';
	public function __construct() {
		$current_wp_version = $GLOBALS['wp_version'];
		if ( version_compare( $current_wp_version, '5.8', '>=' ) ) {
			add_filter( 'block_editor_settings_all', array( $this, 'editor_settings' ) );
		} else {
			add_filter( 'block_editor_settings', array( $this, 'editor_settings' ) );
		}

		// Add ajax action to load css file.
		add_action( 'wp_ajax_' . $this->action, array( $this, 'css_file' ) );
		// Add more editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'assets' ) );
	}

	/**
	 * Add more editor styles and scripts
	 *
	 * @todo Add Custom Fonts and styling settings.
	 *
	 * @return void
	 */
	function assets() {
		$font_url = Customify_Customizer_Auto_CSS::get_instance()->get_font_url();
		if ( $font_url ) {
			wp_enqueue_style( 'customify-editor-fonts', $font_url );
		}
		// wp-edit-post is deprecated in WP 6.2+; wp-edit-blocks is the modern handle.
		$inline_handle = wp_style_is( 'wp-edit-blocks', 'registered' ) ? 'wp-edit-blocks' : 'wp-edit-post';
		wp_add_inline_style( $inline_handle, $this->css() );
	}

	/**
	 * Add styling settings to editor.
	 *
	 * @return string CSS code.
	 */
	public function css() {
		$fields = array();
		$keys   = array(
			'container_width',
			'site_content_styling',
			'content_background',
			'single_blog_post_content_width',
			'global_typography_heading_h1',
			'global_typography_base_heading',
			'global_styling_color_heading',
		);

		foreach ( $keys as $k ) {
			$f = Customify()->customizer->get_field_setting( $k );
			if ( $f ) {
				$fields[ $k ] = $f;
			}
		}

		if ( $fields['global_styling_color_heading'] ) {
			// WP 6.0+: post title is now a block (.wp-block-post-title).
			$fields['global_styling_color_heading']['selector']   = '.editor-styles-wrapper .wp-block-post-title';
			$fields['global_styling_color_heading']['css_format'] = 'color: {{value}};';
		}

		if ( $fields['container_width'] ) {
			// Sync wide-size CSS var into editor so theme.json + customizer stay in sync.
			$fields['container_width']['selector']   = ':root';
			$fields['container_width']['css_format'] = '--wp--style--global--wide-size: {{value}};';
		}

		if ( $fields['single_blog_post_content_width'] ) {
			$fields['single_blog_post_content_width']['selector']   = '.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide)';
			$fields['single_blog_post_content_width']['css_format'] = 'max-width: {{value}};';
		}

		if ( $fields['global_typography_base_heading'] ) {
			// WP 6.0+: post title is a dedicated block.
			$fields['global_typography_base_heading']['selector'] = '.editor-styles-wrapper .wp-block-post-title';
		}
		if ( $fields['global_typography_heading_h1'] ) {
			$fields['global_typography_heading_h1']['selector'] = '.editor-styles-wrapper .wp-block-post-title';
		}

		if ( $fields['site_content_styling'] ) {
			// .editor-styles-wrapper is stable across WP 5.4+.
			$fields['site_content_styling']['selector'] = array(
				'normal' => '.editor-styles-wrapper',
			);
		}

		if ( isset( $fields['content_background'] ) && $fields['content_background'] ) {
			// WP 6.0+ uses .editor-visual-editor instead of .edit-post-layout__content.
			$fields['content_background']['selector'] = array(
				'normal' => '.editor-styles-wrapper',
			);
		}

		$c   = new Customify_Customizer_Auto_CSS();
		$css = $c->render_css( $fields );

		// Metabox compatibility (selectors stable in WP 6.x).
		$css .= '.interface-interface-skeleton__footer { background: #FFF; }
		.editor-styles-wrapper .wp-block-post-title { min-height: 0; }
		.block-editor-page .editor-styles-wrapper button:not(.components-button) { background: none; }
		';

		$css .= '.editor-styles-wrapper pre,
		.editor-styles-wrapper .wp-block-code,
		.editor-styles-wrapper .wp-block-preformatted {
			background: #f2f2f2;
			font-family: "Courier 10 Pitch", Courier, monospace;
			padding: 1.618em;
			overflow: auto;
			margin-left: auto;
			margin-right: auto;
			white-space: pre-wrap;
		}

		.editor-styles-wrapper ul,
		.editor-styles-wrapper ol {
			margin: 1.5em auto;
			list-style-position: outside;
		}

		.editor-styles-wrapper .wp-block-list,
		.editor-styles-wrapper .wp-block-categories__list,
		.editor-styles-wrapper .wp-block-archives-list {
			padding-left: 2.5em;
		}

		.editor-styles-wrapper ul ul,
		.editor-styles-wrapper ol ol,
		.editor-styles-wrapper ul ol,
		.editor-styles-wrapper ol ul {
			margin-bottom: 0;
			margin-top: 0;
			margin-left: 2.5em;
		}

		.editor-styles-wrapper .wp-block-table table,
		.editor-styles-wrapper .wp-block-table tr,
		.editor-styles-wrapper .wp-block-table th,
		.editor-styles-wrapper .wp-block-table td {
			border: 0;
		}

		.editor-styles-wrapper .wp-block-quote {
			border-left-width: 4px;
			border-left-style: solid;
		}

		.editor-styles-wrapper .wp-block-pullquote {
			margin-left: auto;
			margin-right: auto;
		}

		.editor-styles-wrapper .wp-block-pullquote.alignleft {
			margin: 0 1.41575em 1em 2.5em;
		}

		.editor-styles-wrapper .wp-block-pullquote.alignright {
			margin: 0 2.5em 1em 1.41575em;
		}

		.editor-styles-wrapper .wp-block-separator.is-style-dots {
			max-width: 205px;
		}
		';
		return $css;
	}

	/**
	 * Create a dymanic stylesheet url.
	 *
	 * @return string CSS URL
	 */
	public function editor_style_url() {
		return add_query_arg(
			array(
				'action' => $this->action,
				'nonce'  => wp_create_nonce( $this->action ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Add edditor settings.
	 *
	 * @see gutenberg_editor_scripts_and_styles
	 *
	 * @param array $editor_settings
	 * @return array
	 */
	public function editor_settings( $editor_settings ) {

		$editor_settings['styles'][] = array(
			'css' => $this->load_style(),
		);

		return $editor_settings;
	}

	/**
	 * Render dynamic CSS content.
	 *
	 * @return void
	 */
	public function css_file() {
		header( 'Content-type: text/css; charset: UTF-8' );
		echo $this->load_style();
	}

	/**
	 * Load CSS content.
	 *
	 * @return string CSS code.
	 */
	public function load_style() {
		global $wp_filesystem;
		WP_Filesystem();
		$file          = get_template_directory() . '/' . $this->editor_file;
		$file_contents = '';
		if ( file_exists( $file ) ) {
			$file_contents .= $wp_filesystem->get_contents( $file );
		}

		/**
		 * Remove editor background
		 *
		 * @since 0.3.0
		 */
		$config_fields = Customify()->customizer->get_config();
		$c             = new Customify_Customizer_Auto_CSS();
		$css_code      = $c->render_css( $config_fields );

		$file_contents .= $css_code;
		return $file_contents;
	}

}

new Customify_Editor();
