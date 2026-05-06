<?php
/**
 * Support Gutenberg Editor.
 *
 * @since 0.2.6
 */
class Customify_Editor {
	private $action      = 'customify_load_editor_style';
	private $editor_file = 'build/css/backend/admin/editor.css';
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
			// Sync content-size CSS var only when editing a post (setting is single-post scoped);
			// pages and other post types keep theme.json's default to match frontend behavior.
			$screen            = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$editing_post_type = $screen ? $screen->post_type : '';
			if ( 'post' === $editing_post_type ) {
				$fields['single_blog_post_content_width']['selector']   = ':root';
				$fields['single_blog_post_content_width']['css_format'] = '--wp--style--global--content-size: {{value}};';
			} else {
				unset( $fields['single_blog_post_content_width'] );
			}
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
		.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide) {
			max-width: var(--wp--style--global--content-size, 780px);
		}
		';

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id ) {
			$maybe_post = get_post();
			$post_id    = $maybe_post ? $maybe_post->ID : 0;
		}

		// Layout-driven contentSize: keep block max-width in sync with the active
		// sidebar layout so what the user sees in the editor matches the frontend
		// column width. Applied last so it wins over single_blog_post_content_width
		// and theme.json. Targets `body` with `!important` because theme.json emits
		// the variable on `body` — a `:root` rule loses to that within body's scope.
		//
		// Content Layout meta (full-width / full-stretched) overrides the sidebar
		// layout to the no-sidebar size — these modes hide the sidebar regardless
		// of the per-post sidebar setting.
		//
		// Live-reactive counterpart lives in src/backend/page-settings/index.js.
		if ( $post_id ) {
			$content_layout = get_post_meta( $post_id, '_customify_content_layout', true );
			if ( in_array( $content_layout, array( 'full-width', 'full-stretched' ), true ) ) {
				$layout = 'content';
			} else {
				$layout = self::resolve_post_sidebar_layout( $post_id );
			}
			$size = customify_get_content_size_for_layout( $layout );
			$css .= "body { --wp--style--global--content-size: {$size} !important; }";

			// Full-stretched additionally drops the max-width so blocks fill the
			// container, matching the frontend `.site-content.content-full-stretched`
			// rule in src/frontend/scss/base/_blocks.scss.
			if ( 'full-stretched' === $content_layout ) {
				$css .= '.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide) { max-width: none; }';
			}
		}

		$css .= '.editor-styles-wrapper ul,
		.editor-styles-wrapper ol {
			margin: 1.5em auto;
			list-style-position: outside;
		}

		.editor-styles-wrapper .wp-block-list,
		.editor-styles-wrapper .wp-block-categories__list,
		.editor-styles-wrapper .wp-block-archives-list {
			padding-left: 2.5em;
		}

		.editor-styles-wrapper ul ul:not(.wp-block-navigation ul),
		.editor-styles-wrapper ol ol,
		.editor-styles-wrapper ul ol,
		.editor-styles-wrapper ol ul:not(.wp-block-navigation ul) {
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
	 * Map a sidebar layout slug to the matching block contentSize.
	 *
	 * Thin wrapper around customify_get_content_size_for_layout() — kept on
	 * the class so the public API surface stays stable.
	 *
	 * @param string $layout Sidebar layout slug.
	 * @return string CSS length (with px unit).
	 */
	public static function content_size_for_sidebar_layout( $layout ) {
		return customify_get_content_size_for_layout( $layout );
	}

	/**
	 * Customizer fallback sidebar layout for a post type — used when the
	 * per-post meta is empty ("Inherit from Customizer").
	 *
	 * Mirrors customify_get_layout() priorities:
	 *   page          → page_sidebar_layout
	 *   post          → posts_sidebar_layout
	 *   any other CPT → {post_type}_sidebar_layout
	 *
	 * For CPTs the customizer offers a 'default' choice meaning "inherit global"
	 * — that sentinel is normalized here by falling through to sidebar_layout.
	 *
	 * @param string $post_type Post type slug.
	 * @return string Layout slug.
	 */
	public static function customizer_sidebar_layout_for_post_type( $post_type ) {
		if ( 'page' === $post_type ) {
			$layout = Customify()->get_setting( 'page_sidebar_layout' );
		} elseif ( 'post' === $post_type ) {
			$layout = Customify()->get_setting( 'posts_sidebar_layout' );
		} else {
			$layout = Customify()->get_setting( $post_type . '_sidebar_layout' );
		}

		if ( ! $layout || 'default' === $layout ) {
			$layout = Customify()->get_setting( 'sidebar_layout' );
		}
		if ( ! $layout || 'default' === $layout ) {
			$layout = 'content-sidebar';
		}
		return $layout;
	}

	/**
	 * Resolve the effective sidebar layout for a post in the block editor.
	 *
	 * Falls back to the matching Customizer setting when the per-post meta is
	 * empty or set to "default", mirroring customify_get_layout() priorities.
	 *
	 * @param int $post_id Post ID.
	 * @return string Layout slug.
	 */
	public static function resolve_post_sidebar_layout( $post_id ) {
		$meta = get_post_meta( $post_id, '_customify_sidebar', true );
		if ( $meta && 'default' !== $meta ) {
			return $meta;
		}
		return self::customizer_sidebar_layout_for_post_type( get_post_type( $post_id ) );
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
