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
		// Mirror the frontend :root palette tokens block (printed at wp_head
		// priority 8 by Customify::print_palette_tokens) into the editor
		// canvas so var(--customify-X) expressions in bundled SCSS + cascade
		// chains (heading→text, link→primary, link-hover color-mix, etc.)
		// resolve identically. Without this, the editor iframe falls back
		// to each var()'s literal-hex fallback — which is the field default,
		// not the user-saved or cascade-resolved value. Result: opted-in
		// users (Phase 2.8 Palette panel) see frontend cascade ≠ editor
		// preview, breaking the WYSIWYG promise.
		$css = '';
		if ( function_exists( 'customify_color_palette_root_css' ) ) {
			$css .= customify_color_palette_root_css();

			// Editor-canvas-specific overrides. The palette-tokens block emits
			// `body{background-color:var(--customify-base, hex)}` for the
			// frontend, but the block editor iframe ships at least 2 LATER-in-
			// source-order `body{background:white}` defaults (one from
			// `:where(body)` reset, one from the iframe canvas stylesheet) that
			// outrank a plain `body` selector by source order. Bump specificity
			// to `.editor-styles-wrapper` — WP's canonical "this content gets
			// theme styling" wrapper, more specific than `body` and scoped to
			// the editor canvas. Same cascade chain, same saved-vs-default
			// resolution as frontend.
			$base_default = '#ffffff';
			$slots_fn     = 'customify_color_get_slots';
			if ( function_exists( $slots_fn ) ) {
				$slots        = $slots_fn();
				$base_default = $slots['base'] ?? $base_default;
			}
			$css .= '.editor-styles-wrapper{background-color:var(--customify-base, ' . esc_attr( $base_default ) . ');}';
		}

		$fields = array();
		$keys   = array(
			'container_width',
			'background',                       // Page bg composite — re-scoped to .editor-styles-wrapper below
			'site_content_styling',
			'content_background',
			'global_typography_heading_h1',
			'global_typography_base_heading',
			'global_styling_color_heading',
		);
		// `single_blog_post_content_width` intentionally NOT in $keys: a :root rule
		// emitted via the standard pipeline would be shadowed by the body-scoped
		// content-size rule the layout-driven block below emits. The user value is
		// folded into that branch when editing a single post, mirroring how the
		// frontend `body.single-post` rule wins by cascade source-order.

		foreach ( $keys as $k ) {
			$f = Customify()->customizer->get_field_setting( $k );
			if ( $f ) {
				$fields[ $k ] = $f;
			}
		}

		if ( $fields['global_styling_color_heading'] ) {
			// WP 6.0+: post title is now a block (.wp-block-post-title).
			// css_format wraps {{value}} in var(--customify-heading, ...) so
			// the post title participates in the heading cascade chain
			// (heading → text slot) — same as h1-h6 on the frontend. A
			// literal `color: {{value}}` here would lock the post title to
			// the saved-or-default hex and skip the cascade, so dragging
			// the Text slot wouldn't update the post title even though
			// other headings cascade correctly.
			$fields['global_styling_color_heading']['selector']   = '.editor-styles-wrapper .wp-block-post-title';
			$fields['global_styling_color_heading']['css_format'] = 'color: var(--customify-heading, {{value}});';
		}

		if ( $fields['container_width'] ) {
			// Sync wide-size CSS var into editor so theme.json + customizer stay in sync.
			$fields['container_width']['selector']   = ':root';
			$fields['container_width']['css_format'] = '--wp--style--global--wide-size: {{value}};';
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

		if ( isset( $fields['background'] ) && $fields['background'] ) {
			// Page-bg composite — frontend targets `body`, but editor canvas
			// has WP/theme.json `body { background: white }` defaults loaded
			// AFTER the auto-CSS inline block (source order beats `body`-on-
			// `body` specificity). Re-scope to .editor-styles-wrapper so the
			// composite's saved bg_color, bg_image, bg_position etc. show in
			// the editor canvas. Same wrapper used by site_content_styling +
			// content_background above.
			$fields['background']['selector'] = array(
				'normal' => '.editor-styles-wrapper',
			);
		}

		$c   = new Customify_Customizer_Auto_CSS();
		$css .= $c->render_css( $fields );

		// Metabox compatibility (selectors stable in WP 6.x).
		$css .= '.interface-interface-skeleton__footer { background: #FFF; }
		.editor-styles-wrapper .wp-block-post-title { min-height: 0; }
		.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide) {
			max-width: var(--wp--style--global--content-size, 780px);
		}
		.editor-styles-wrapper > .is-root-container > .alignwide {
			--customify-alignwide-actual: max(
				100%,
				min(var(--wp--style--global--wide-size, 1200px), calc(100vw - 32px))
			);
			width: var(--customify-alignwide-actual);
			margin-left: calc((100% - var(--customify-alignwide-actual)) / 2);
			margin-right: calc((100% - var(--customify-alignwide-actual)) / 2);
			max-width: none;
			box-sizing: border-box;
		}
		';

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id ) {
			$maybe_post = get_post();
			$post_id    = $maybe_post ? $maybe_post->ID : 0;
		}

		// Layout-driven contentSize: keep block max-width in sync with the active
		// sidebar layout so what the user sees in the editor matches the frontend
		// column width. Targets `body` (no !important): the variable is custom-
		// property-inheritable, so the closer-ancestor body rule overrides the
		// theme.json :root baseline for any element inside body without needing
		// to win specificity.
		//
		// Content Layout meta (full-width / full-stretched) overrides the sidebar
		// layout to the no-sidebar size — these modes hide the sidebar regardless
		// of the per-post sidebar setting.
		//
		// Single-post override: when editing a post, the user's
		// single_blog_post_content_width Customizer setting (if saved) wins over
		// the layout-derived size. Mirrors the frontend override emitted by
		// customify_single_post_content_size_css() (inc/template-functions.php),
		// which re-emits `body.single-post` on the customify-layout-style handle
		// AFTER the layout rule so it wins by source order — same saved-only
		// gate as the `is_array` check below, so editor matches render.
		//
		// Live-reactive counterpart lives in src/backend/page-settings/index.js.
		if ( $post_id ) {
			$content_layout = get_post_meta( $post_id, '_customify_content_layout', true );
			if ( in_array( $content_layout, array( 'full-width', 'full-stretched', 'narrow' ), true ) ) {
				$layout = 'content';
			} else {
				$layout = self::resolve_post_sidebar_layout( $post_id );
			}
			$size = customify_get_content_size_for_layout( $layout );

			// Content Layout overrides for content-size — match the per-layout
			// rules emitted by customify_layout_content_size_css() so the editor
			// canvas matches the frontend. Full-Width uses calc(100vw - 64px)
			// (container content-box at 100% with 2em padding each side);
			// Full-Stretched uses 100vw (no container padding); Narrow uses the
			// Customizer narrow_width value.
			if ( 'narrow' === $content_layout ) {
				$size = customify_get_narrow_width_value();
			} elseif ( 'full-width' === $content_layout ) {
				$size = 'calc(100vw - 64px)';
			} elseif ( 'full-stretched' === $content_layout ) {
				$size = '100vw';
			}

			$screen            = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$editing_post_type = $screen ? $screen->post_type : '';
			if ( 'post' === $editing_post_type ) {
				$user_cw = Customify()->get_setting( 'single_blog_post_content_width' );
				if ( is_array( $user_cw ) && isset( $user_cw['value'] ) && '' !== $user_cw['value'] ) {
					$unit = ! empty( $user_cw['unit'] ) ? $user_cw['unit'] : 'px';
					$size = $user_cw['value'] . $unit;
				}
			}

			$css .= "body { --wp--style--global--content-size: {$size}; }";

			// Block max-width override for full-width / full-stretched is emitted
			// from JS (src/backend/page-settings/index.js::buildContentSizeCss), NOT
			// here. PHP-emitted CSS is static for the page load — if it included the
			// override based on the saved meta, toggling content_layout in the
			// editor sidebar wouldn't be able to undo it because the PHP rule keeps
			// applying. Letting JS own the dynamic override keeps the metabox
			// toggle responsive.
		}

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
	 * Render dynamic CSS content. Endpoint is the URL produced by
	 * `editor_style_url()` and consumed by the block editor's iframe
	 * via the `editor_settings['styles']` URL slot — the browser
	 * fetches it during editor boot.
	 *
	 * Auth: the URL carries a `nonce` query arg minted by
	 * `editor_style_url()`. Verify it server-side and require the same
	 * capability the block editor itself gates on (`edit_posts`) so
	 * subscriber-level users can't pull the stylesheet directly.
	 *
	 * @return void
	 */
	public function css_file() {
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( ! wp_verify_nonce( $nonce, $this->action ) ) {
			status_header( 403 );
			exit;
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			status_header( 403 );
			exit;
		}
		header( 'Content-type: text/css; charset: UTF-8' );
		echo $this->load_style();
		exit;
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

		// Get the ACTIVE fonts' files into the editor iframe. WP 6.5+ renders
		// the canvas in an iframe; only theme.json fontFamilies (auto-@font-face)
		// and `editor_settings['styles']` CSS reach inside it — a `<link>`
		// enqueued in the outer shell (see assets()) never downloads there. The
		// same $c instance just ran setup_font() for every active typography
		// field while rendering above, so its font buckets are populated: reuse
		// them to emit the @font-face / @import the iframe needs, so demos using
		// arbitrary per-template Google fonts render in the editor without a
		// per-demo theme.json edit. Google fonts arrive as an @import (must lead
		// the stylesheet); Theme/Library fonts as standalone @font-face.
		$font_import = '';
		if ( method_exists( $c, 'get_google_fonts_url' ) ) {
			$google_url = $c->get_google_fonts_url();
			if ( $google_url ) {
				// get_google_fonts_url() returns a protocol-relative `//` URL.
				// esc_url_raw (not esc_url) — this lands inside a CSS
				// `@import url('…')` string, so the `&` between query args must
				// stay literal; esc_url would HTML-entity it (`&#038;`) and break
				// the request. Single quotes are stripped by the URL escaper.
				$font_import .= "@import url('" . esc_url_raw( set_url_scheme( $google_url, 'https' ) ) . "');\n";
			}
		}
		$font_faces = '';
		if ( method_exists( $c, 'get_theme_fonts_css' ) ) {
			$font_faces .= (string) $c->get_theme_fonts_css();
		}
		if ( method_exists( $c, 'get_library_fonts_css' ) ) {
			$font_faces .= (string) $c->get_library_fonts_css();
		}
		// @import must precede all other rules in the stylesheet.
		$file_contents  = $font_import . $file_contents . $font_faces;

		$file_contents .= $css_code;

		// The config render above emits the `:root { --customify-typo-*-* }`
		// tokens (Base body / Heading / h1–h6), but the RULES that consume
		// them (`body{font-family:var(--customify-typo-body-font-family,…)}`,
		// `h1..h6{font-family:var(--customify-typo-heading-font-family,…)}`)
		// live only in the FRONTEND stylesheet (src/frontend/scss/base/_base.scss)
		// — they are never compiled into the editor canvas. Worse, WP core
		// ships `.editor-styles-wrapper` typography that out-specifies any
		// bare `body`/`h1` consumer we could add, so the buyer edits in a
		// fallback font even when the token is set. Emit the missing consumer
		// rules here, scoped to `.editor-styles-wrapper` (same wrapper the
		// color/background re-scoping above already uses) so they resolve the
		// same token cascade as the frontend and win over core typography.
		$file_contents .= $this->typography_scope_css();

		return $file_contents;
	}

	/**
	 * Editor-canvas consumer rules for the typography tokens.
	 *
	 * Mirrors the frontend `body` / `h1..h6` font rules from
	 * `src/frontend/scss/base/_base.scss`, but scoped to
	 * `.editor-styles-wrapper` so they out-specify WP core's canvas
	 * typography (bare `body`/`h1` would lose the cascade inside the
	 * editor iframe). Font-family + weight are the only foundational
	 * tokens the shared Heading field emits; the literal fallbacks match
	 * `$font_main` / the per-level SCSS defaults so an unset site renders
	 * identically to the frontend. The `@font-face` that makes the chosen
	 * family actually paint is emitted separately (theme.json fontFamilies
	 * + the preview/editor font bridges).
	 *
	 * @return string CSS code.
	 */
	private function typography_scope_css() {
		$font_main = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
		$w         = '.editor-styles-wrapper';

		$css  = "{$w},{$w} p{"
			. "font-family:var(--customify-typo-body-font-family, {$font_main});"
			. 'font-weight:var(--customify-typo-body-font-weight, 400);'
			. 'line-height:var(--customify-typo-body-line-height, 1.618);'
			. '}';

		$headings = 'h1,h2,h3,h4,h5,h6,.h1,.h2,.h3,.h4,.h5,.h6';
		$sel      = array();
		foreach ( explode( ',', $headings ) as $h ) {
			$sel[] = $w . ' ' . $h;
		}
		// The post title is a block (.wp-block-post-title) that stands in for
		// the page's h1 in the canvas — include it so the title also follows
		// the heading family (matches the h1 re-scoping in css()).
		$sel[] = $w . ' .wp-block-post-title';
		$css  .= implode( ',', $sel ) . '{'
			. "font-family:var(--customify-typo-heading-font-family, {$font_main});"
			. 'font-weight:var(--customify-typo-heading-font-weight, 400);'
			. '}';

		return $css;
	}

}

new Customify_Editor();
