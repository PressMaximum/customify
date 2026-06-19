<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package customify
 */

if ( ! function_exists( 'customify_get_config_sidebar_layouts' ) ) {
	function customify_get_config_sidebar_layouts() {
		return array(
			'content-sidebar'         => __( 'Content / Sidebar', 'customify' ),
			'sidebar-content'         => __( 'Sidebar / Content', 'customify' ),
			'content'                 => __( 'Content (no sidebars)', 'customify' ),
			'sidebar-content-sidebar' => __( 'Sidebar / Content / Sidebar', 'customify' ),
			'sidebar-sidebar-content' => __( 'Sidebar / Sidebar / Content', 'customify' ),
			'content-sidebar-sidebar' => __( 'Content / Sidebar / Sidebar', 'customify' ),
		);
	}
}

if ( ! function_exists( 'customify_get_container_width_value' ) ) {
	/**
	 * Resolve the numeric container_width Customizer value in pixels.
	 *
	 * The slider control stores its value as either:
	 *   - array shape: array( 'value' => 1500, 'unit' => 'px' )  — modern
	 *   - scalar number: 1500                                    — legacy
	 *   - empty/false: Customizer never saved                    — unsaved
	 *
	 * For the unsaved case we return 1248, which is the value 30K production
	 * sites actually render at — the SCSS `.customify-container { max-width: 1248px }`
	 * declaration in src/frontend/scss/layouts/_layouts.scss:62 wins because the
	 * auto-CSS slider pipeline skips emission when the field has no saved value.
	 *
	 * @return int Container width in pixels (always > 0).
	 */
	function customify_get_container_width_value() {
		$raw = get_theme_mod( 'container_width' );
		if ( is_array( $raw ) && ! empty( $raw['value'] ) ) {
			$value = (int) $raw['value'];
			if ( $value > 0 ) {
				return $value;
			}
		} elseif ( is_numeric( $raw ) ) {
			$value = (int) $raw;
			if ( $value > 0 ) {
				return $value;
			}
		}
		return 1248;
	}
}

if ( ! function_exists( 'customify_get_layout_content_sizes' ) ) {
	/**
	 * Single source of truth for the layout-driven block contentSize.
	 *
	 * Consumed by:
	 *   - inc/admin/editor.php           — block editor inline CSS
	 *   - inc/admin/page-settings.php    — localized to JS for live updates
	 *   - customify_layout_content_size_css() — frontend inline CSS below
	 *
	 * The returned size is `min(cap, parent)`:
	 *
	 *   - **cap** — a typography-driven readability target (originally 1184/863/542
	 *     at container_width=1248). Scaled proportionally by `container_width / anchor`,
	 *     where `anchor` is captured per-site at install / migration time so existing
	 *     sites see `factor=1` immediately after upgrade (zero rendered diff).
	 *   - **parent** — the frontend column geometry: container content-box (after
	 *     2em padding each side), gridlex grid (negative -1em margin each side),
	 *     gridlex column (col-12/9/6 = 100%/75%/50%), minus col padding (1em each
	 *     side) and content-inner right padding (16px). Border (1px when
	 *     sidebar_vertical_border is enabled) is intentionally ignored — sub-pixel.
	 *
	 * Returning `min(cap, parent)` produces the value that blocks actually reach
	 * on the frontend (parent constrains when sliders are at default; the cap
	 * kicks in when container is widened enough that the readability ceiling
	 * matters). Editor consumers apply the same value directly because the editor
	 * iframe has no .customify-container > grid > column nesting — feeding it the
	 * resolved render-width keeps WYSIWYG honest.
	 *
	 * Filterable so a child theme / plugin can override values in one place.
	 *
	 * @return array{no_sidebar:string,one_sidebar:string,two_sidebars:string} CSS lengths.
	 */
	function customify_get_layout_content_sizes() {
		$cw     = customify_get_container_width_value();
		$anchor = (int) get_option( 'customify_layout_content_size_anchor', 1248 );
		if ( $anchor <= 0 ) {
			$anchor = 1248;
		}
		$factor = $cw / $anchor;

		// Typography readability caps (original hardcoded targets at anchor=1248).
		$cap_no_sidebar   = (int) round( 1184 * $factor );
		$cap_one_sidebar  = (int) round(  863 * $factor );
		$cap_two_sidebars = (int) round(  542 * $factor );

		// Frontend column geometry. See src/frontend/scss/layouts/_layouts.scss
		// (.customify-container padding 2em) and src/frontend/scss/vendors/gridlex/
		// (gutter 2em → grid margin -1em, col padding 1em).
		//
		// .content-inner padding varies per layout (layouts/_layouts.scss §"Layout: *"):
		//   - `.content`            (no sidebar)        → no padding (no sidebar to gap from)
		//   - `.content-sidebar`    (1 right sidebar)   → padding-right: ms(0) ≈ 16px
		//   - `.sidebar-content`    (1 left sidebar)    → padding-left: ms(0)  ≈ 16px
		//   - `.sidebar-content-sidebar` (2 sidebars)   → both sides: 32px total
		//   - `.sidebar-sidebar-content` / `.content-sidebar-sidebar` → only one side: 16px
		//
		// Borders from sidebar_vertical_border (1px when enabled) are sub-pixel and
		// intentionally ignored — they don't drive block layout decisions.
		//
		// `two_sidebars` uses the symmetric variant (`sidebar-content-sidebar`) — the most
		// constrained of the 2-sidebar trio. Asymmetric variants render the same value
		// (cap-bound, since cap=542 < parent for asymmetric), which is acceptable: editor
		// is conservative-by-1 rather than divergent.
		$inner = max( 0, $cw - 64 );
		$grid  = $inner + 32;

		$parent_no_sidebar   = $grid - 32;                              // col padding only
		$parent_one_sidebar  = (int) round( $grid * 0.75 ) - 32 - 16;   // col + 1-side content-inner
		$parent_two_sidebars = (int) round( $grid * 0.5 )  - 32 - 32;   // col + 2-side content-inner

		// Floor at a sane minimum so unusual configs don't produce nonsense like 0px.
		$no_sidebar   = max( 200, min( $cap_no_sidebar,   $parent_no_sidebar ) );
		$one_sidebar  = max( 200, min( $cap_one_sidebar,  $parent_one_sidebar ) );
		$two_sidebars = max( 200, min( $cap_two_sidebars, $parent_two_sidebars ) );

		return apply_filters(
			'customify/layout_content_sizes',
			array(
				'no_sidebar'   => $no_sidebar . 'px',
				'one_sidebar'  => $one_sidebar . 'px',
				'two_sidebars' => $two_sidebars . 'px',
			)
		);
	}
}

if ( ! function_exists( 'customify_migrate_layout_content_size_anchor' ) ) {
	/**
	 * One-time migration: capture the per-site anchor for content-size scaling.
	 *
	 * Without this, switching customify_get_layout_content_sizes() from hardcoded
	 * 1184/863/542 to a container_width-driven formula would silently widen the
	 * content-area on sites that explicitly saved container_width > 1248 (and shrink
	 * it on sites < 1248). Capturing the anchor at upgrade-time keeps factor=1.0
	 * immediately after migration — zero rendered diff across the 30K install base.
	 *
	 * Subsequent slider changes alter `container_width` but the anchor stays put,
	 * so the proportional scaling kicks in only when the user actually moves the
	 * slider after upgrading.
	 *
	 * Idempotent via the `customify_layout_anchor_migrated_v1` flag. AGENTS.md §4.1.
	 */
	function customify_migrate_layout_content_size_anchor() {
		if ( get_option( 'customify_layout_anchor_migrated_v1' ) ) {
			return;
		}
		$anchor = customify_get_container_width_value();
		update_option( 'customify_layout_content_size_anchor', $anchor, false );
		update_option( 'customify_layout_anchor_migrated_v1', '1', false );
	}
}
add_action( 'after_setup_theme', 'customify_migrate_layout_content_size_anchor', 5 );

if ( ! function_exists( 'customify_get_content_size_for_layout' ) ) {
	/**
	 * Resolve the contentSize CSS length for a sidebar layout slug.
	 *
	 * @param string $layout Sidebar layout slug.
	 * @return string CSS length (with unit).
	 */
	function customify_get_content_size_for_layout( $layout ) {
		$sizes        = customify_get_layout_content_sizes();
		$two_sidebars = array(
			'sidebar-content-sidebar',
			'sidebar-sidebar-content',
			'content-sidebar-sidebar',
		);
		if ( in_array( $layout, $two_sidebars, true ) ) {
			return $sizes['two_sidebars'];
		}
		if ( 'content' === $layout ) {
			return $sizes['no_sidebar'];
		}
		return $sizes['one_sidebar'];
	}
}

if ( ! function_exists( 'customify_get_narrow_width_value' ) ) {
	/**
	 * Resolve the Customizer narrow_width value as a CSS length string.
	 *
	 * Mirrors customify_get_container_width_value() shape handling — slider
	 * stores either { value, unit } array or scalar, falls back to the field
	 * default 800px when unsaved.
	 *
	 * @return string CSS length (e.g. "800px").
	 */
	function customify_get_narrow_width_value() {
		$raw = get_theme_mod( 'narrow_width' );
		if ( is_array( $raw ) && isset( $raw['value'] ) && '' !== $raw['value'] ) {
			$unit = ! empty( $raw['unit'] ) ? $raw['unit'] : 'px';
			return (int) $raw['value'] . $unit;
		} elseif ( is_numeric( $raw ) && (int) $raw > 0 ) {
			return (int) $raw . 'px';
		}
		return '800px';
	}
}

if ( ! function_exists( 'customify_layout_content_size_css' ) ) {
	/**
	 * Frontend CSS that maps body.main-layout-* classes to the matching
	 * --wp--style--global--content-size and --wp--style--global--wide-size,
	 * so default-aligned AND wide-aligned blocks both follow the active
	 * sidebar layout. Kept here (not in SCSS) so all values come from
	 * customify_get_layout_content_sizes().
	 *
	 * Wide-size design (per user spec):
	 *   - No-sidebar layouts: wide-size = content-size + 400 (200px breakout each side)
	 *   - Narrow content_layout: wide-size = narrow_width + 400 (emitted from the
	 *     narrow_width field's `css_format`, not here — see Customizer-coupling note).
	 *   - Sidebar layouts: wide-size = content-size (parent-constrained — wide stays
	 *     inside the content column, doesn't overlap sidebars per Q2 confirmation)
	 *
	 * The visual breakout itself happens in SCSS (.entry-content > .alignwide uses
	 * negative margins with a max(100%, min(wide-size, 100vw - 32px)) clamp so the
	 * wide block can't overflow the viewport — see src/frontend/scss/base/_blocks.scss).
	 *
	 * Per-page Content Layout (.site-content.content-{full-width,full-stretched})
	 * forces a specific content+wide-size — these rules are scoped to .site-content
	 * which is closer in the inheritance chain than body, so they win the variable
	 * resolution regardless of body.main-layout-* specificity.
	 *
	 * Customizer-coupling note: this output is enqueued into its OWN inline style
	 * (`customify-layout-style-inline-css`) — NOT the main `customify-style-inline-css`
	 * that the live-preview JS (`src/backend/customizer/js/auto-css.js`) overwrites
	 * on every setting change. Putting the body.main-layout-* rules here would let
	 * the JS rebuild wipe them, falling content-size back to the theme.json default
	 * (863px) and visibly shrinking layout on non-narrow pages while the user drags
	 * the `narrow_width` slider — UX read: "narrow width is being applied to all
	 * pages", which it is not.
	 *
	 * For the same reason the `.site-content.content-narrow` rule is NOT emitted
	 * here — it has a per-field `css_format` (see layouts.php narrow_width) so the
	 * live-preview JS owns it and rebuilds with the dragged value. Emitting it here
	 * too would only matter if the JS bundle failed to load, and the same field's
	 * PHP `auto_css()` output already covers that path.
	 *
	 * @return string CSS.
	 */
	function customify_layout_content_size_css() {
		$sizes = customify_get_layout_content_sizes();

		// Compute wide-size = content-size + 400 (px) for the no-sidebar breakout.
		// `customify_layout_content_size_value_plus()` keeps unit handling local
		// so '1184px' + 400 stays as '1584px'.
		$no_sidebar_wide = customify_layout_content_size_value_plus( $sizes['no_sidebar'], 400 );

		// Full-Width / Full-Stretched content_layout: content-size + wide-size are
		// viewport-bound, not capped at the reading-column no_sidebar value. This
		// lets BOTH Core blocks (max-width via SCSS rule) AND third-party blocks
		// that read `--wp--style--global--content-size` (Blocksify Section's
		// "Inherit Max Width from Theme") naturally fill the available container
		// space. Full-Width keeps the 32px container padding each side
		// (calc(100vw - 64px)); Full-Stretched drops the padding too (100vw).
		return sprintf(
			'body.main-layout-content{--wp--style--global--content-size:%1$s;--wp--style--global--wide-size:%2$s}'
			. 'body.main-layout-content-sidebar,'
			. 'body.main-layout-sidebar-content{--wp--style--global--wide-size:%3$s}'
			. 'body.main-layout-sidebar-content-sidebar,'
			. 'body.main-layout-sidebar-sidebar-content,'
			. 'body.main-layout-content-sidebar-sidebar{--wp--style--global--content-size:%4$s;--wp--style--global--wide-size:%4$s}'
			. '.site-content.content-full-width{--wp--style--global--content-size:calc(100vw - 64px);--wp--style--global--wide-size:calc(100vw - 64px)}'
			. '.site-content.content-full-stretched{--wp--style--global--content-size:100vw;--wp--style--global--wide-size:100vw}',
			esc_attr( $sizes['no_sidebar'] ),
			esc_attr( $no_sidebar_wide ),
			esc_attr( $sizes['one_sidebar'] ),
			esc_attr( $sizes['two_sidebars'] )
		);
	}
}

if ( ! function_exists( 'customify_single_post_content_size_css' ) ) {
	/**
	 * Frontend CSS that lets the "Post Content Max Width" Customizer setting
	 * actually win on single posts.
	 *
	 * The field's own css_format (`body.single-post { --…-content-size }`, on
	 * the `customify-style` handle) is emitted BEFORE the layout rule
	 * `body.main-layout-content { --…-content-size }` (on the later
	 * `customify-layout-style` handle). The two selectors have EQUAL
	 * specificity, so on a no-sidebar single post the later layout rule wins by
	 * source order and the user's width is silently ignored — the bug behind
	 * "works in Customize, not on the actual post". (Posts WITH a sidebar are
	 * unaffected: the layout rule only sets content-size for the no-sidebar
	 * `main-layout-content` class.)
	 *
	 * Fix: re-emit the same `body.single-post` rule on the SAME
	 * `customify-layout-style` handle, AFTER the layout rule, so it wins by
	 * source order. No specificity bump is used on purpose, so (a) the
	 * per-post Content Layout overrides on `.site-content` still win by
	 * proximity, and (b) the Customizer live-preview <style> — which uses the
	 * same low-specificity selector and loads later still — keeps live drags
	 * working.
	 *
	 * 30K-site safety: emitted ONLY when the user explicitly saved the slider
	 * (a saved value is the `{value, unit}` array; an unsaved field is the bare
	 * `863` scalar default). Sites that never touched it keep the
	 * layout-derived width they render today. Mirrors the saved-gate in
	 * Customify_Editor::css() so the block editor and the frontend agree.
	 *
	 * @return string CSS (empty string when the setting is unsaved).
	 */
	function customify_single_post_content_size_css() {
		$cw = Customify()->get_setting( 'single_blog_post_content_width' );
		if ( ! is_array( $cw ) || ! isset( $cw['value'] ) || '' === $cw['value'] ) {
			return '';
		}
		$unit = ! empty( $cw['unit'] ) ? $cw['unit'] : 'px';
		$size = $cw['value'] . $unit;

		return 'body.single-post{--wp--style--global--content-size:' . esc_attr( $size ) . ';}';
	}
}

if ( ! function_exists( 'customify_single_post_content_size_preview_js' ) ) {
	/**
	 * Live-preview shim for the "Post Content Max Width" setting.
	 *
	 * The frontend override (customify_single_post_content_size_css()) lives in
	 * the #customify-layout-style-inline-css block, which auto-css.js never
	 * rebuilds — so dragging the slider would leave the preview frozen: the
	 * static rule keeps winning by source order over the live-rebuilt
	 * #customify-style block (same specificity, later wins). This appends a
	 * winning `body.single-post` rule at the END of <head> on each change so the
	 * preview tracks the drag. It fires only via setting.bind (user changes),
	 * matching the saved-only frontend gate: untouched leaves the layout default
	 * (or the static saved rule) in place; dragged shows the pending value;
	 * cleared removes the override.
	 */
	function customify_single_post_content_size_preview_js() {
		$js = <<<'JS'
( function ( api ) {
	if ( ! api ) { return; }
	api( 'single_blog_post_content_width', function ( setting ) {
		setting.bind( function ( val ) {
			// The slider stores its live value as URL-encoded JSON
			// ( encodeURIComponent( JSON.stringify( { unit, value } ) ) ), so the
			// changeset hands the bind a string — decode it before reading.
			if ( typeof val === 'string' ) {
				try { val = JSON.parse( decodeURIComponent( val ) ); } catch ( e ) { val = null; }
			}
			var size = '';
			if ( val && typeof val === 'object' && val.value !== '' && val.value != null ) {
				var unit = ( val.unit && val.unit !== '-' ) ? val.unit : ( val.unit === '-' ? '' : 'px' );
				size = String( val.value ) + unit;
			}
			var id = 'customify-single-post-cw-live';
			var el = document.getElementById( id );
			if ( ! el ) {
				el = document.createElement( 'style' );
				el.id = id;
				document.head.appendChild( el );
			}
			el.textContent = size ? ( 'body.single-post{--wp--style--global--content-size:' + size + ';}' ) : '';
		} );
	} );
} )( window.wp && window.wp.customize );
JS;
		wp_add_inline_script( 'customify-customizer-auto-css', $js );
	}
	add_action( 'customize_preview_init', 'customify_single_post_content_size_preview_js', 20 );
}

if ( ! function_exists( 'customify_layout_content_size_value_plus' ) ) {
	/**
	 * Add a pixel delta to a CSS length string, preserving the unit. Used to
	 * compute wide-size = content-size + 400px without manually parsing every
	 * caller. Returns the input unchanged if the unit isn't `px` (no other unit
	 * is in use today — guard is here so future em/rem use doesn't silently
	 * miscompute).
	 *
	 * @param string $value  CSS length (e.g. "1184px").
	 * @param int    $delta  Pixels to add.
	 * @return string New CSS length (e.g. "1584px").
	 */
	function customify_layout_content_size_value_plus( $value, $delta ) {
		if ( preg_match( '/^(-?\d+(?:\.\d+)?)px$/', $value, $m ) ) {
			return ( (float) $m[1] + (int) $delta ) . 'px';
		}
		return $value;
	}
}
if ( ! function_exists( 'customify_get_all_image_sizes' ) ) {
	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 * @return array $image_sizes The image sizes
	 */
	function customify_get_all_image_sizes() {
		global $_wp_additional_image_sizes;
		$default_image_sizes = array( 'thumbnail', 'medium', 'large' );

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		$options = array();
		foreach ( $image_sizes as $k => $option ) {
			$options[ $k ] = sprintf( '%1$s - (%2$s x %3$s)', $k, $option['width'], $option['height'] );
		}

		$options['full'] = 'Full';

		return $options;
	}
}

if ( ! function_exists( 'customify_get_content_post_types' ) ) {
	/**
	 * Custom post types that warrant per-type Customizer settings (sidebar layout,
	 * Page Header display/title/tagline).
	 *
	 * Starts from Customify()->get_post_types( false ) (publicly_queryable,
	 * non-builtin) and keeps only types with a real, browsable front-end. A type
	 * is dropped when any of these holds:
	 *   - it is a known Customify Pro utility CPT (`customify_hook`, the Hooks
	 *     snippet store) that is publicly_queryable but has no browsable page;
	 *   - it declares `exclude_from_search` (the registering plugin's signal that
	 *     the type is not browsable content);
	 *   - it has neither a public archive (`has_archive`) nor menu visibility
	 *     (`show_in_nav_menus`).
	 *
	 * A CPT that registers itself to look exactly like content (public + in nav
	 * menus + searchable) can only be removed via `customify/content_post_types`.
	 *
	 * @return array<string, array{name:string, singular_name:string}>
	 */
	function customify_get_content_post_types() {
		$post_types = Customify()->get_post_types( false );

		// Customify Pro utility CPTs that are publicly_queryable but have no
		// browsable front-end. Only `customify_hook` (the Hooks snippet store)
		// reaches this list — `customify_ms` / `font` register
		// publicly_queryable=false and are filtered out upstream. Excluded by
		// slug because the theme can't depend on how a given Pro version sets
		// show_in_nav_menus on it (Pro is a separate codebase / release cycle).
		$excluded = array( 'customify_hook' );

		foreach ( $post_types as $pt => $label ) {
			$obj = get_post_type_object( $pt );
			if (
				! $obj
				|| in_array( $pt, $excluded, true )
				|| $obj->exclude_from_search
				|| ! ( $obj->has_archive || $obj->show_in_nav_menus )
			) {
				unset( $post_types[ $pt ] );
			}
		}

		/**
		 * Filter the content post types that receive per-type Customizer settings
		 * (sidebar layout, Page Header display/title/tagline). Use it to add a CPT
		 * the heuristics dropped, or remove a no-front-end CPT they kept (one that
		 * registers as public + in nav menus + searchable, indistinguishable from
		 * content by its flags).
		 *
		 * @param array $post_types Map of post_type slug => labels array.
		 */
		return apply_filters( 'customify/content_post_types', $post_types );
	}
}

if ( ! function_exists( 'customify_get_meta_support_post_types' ) ) {
	/**
	 * Post types that get the Customify per-post settings (the "Customify Page
	 * Settings" metabox / block-editor panel: Content Layout, Page Title Layout,
	 * Sidebar, Disable Elements).
	 *
	 * Broader than customify_get_content_post_types() — these settings affect a
	 * singular front-end view, so the list keeps built-in `page` / `post` and any
	 * public CPT with a viewable single. It only drops no-front-end utility CPTs:
	 * `customify_hook` (the Pro Hooks store) is public + publicly_queryable but
	 * has no browsable page, and the theme can't rely on how a given Pro version
	 * flags it, so it is deny-listed; types flagged `exclude_from_search` (the
	 * registering plugin's "not browsable content" signal) are dropped too.
	 *
	 * Render-safe: a post type omitted here only loses the editor UI; any saved
	 * `_customify_*` meta is still read at render via get_post_meta.
	 *
	 * @return string[] Indexed array of post type slugs.
	 */
	function customify_get_meta_support_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );

		$excluded = array( 'customify_hook' );
		foreach ( $post_types as $pt ) {
			$obj = get_post_type_object( $pt );
			if ( ! $obj || in_array( $pt, $excluded, true ) || $obj->exclude_from_search ) {
				unset( $post_types[ $pt ] );
			}
		}

		/**
		 * Filter the post types that receive the Customify per-post settings
		 * metabox / block-editor panel. Add a CPT the heuristics dropped, or
		 * remove one they kept.
		 *
		 * @param string[] $post_types Indexed array of post type slugs.
		 */
		return apply_filters( 'customify/meta_support_post_types', array_values( $post_types ) );
	}
}

if ( ! function_exists( 'customify_get_layout' ) ) {
	/**
	 * Get the layout for the current page from Customizer setting or individual page/post.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 */
	function customify_get_layout() {
		$default = Customify()->get_setting( 'sidebar_layout' );
		$layout  = apply_filters( 'customify_get_layout', null );
		if ( ! $layout ) {
			$page = Customify()->get_setting( 'page_sidebar_layout' );

			if ( is_home() && is_front_page() || ( is_home() && ! is_front_page() ) ) { // Blog page.
				$blog_posts = Customify()->get_setting( 'posts_sidebar_layout' );
				$layout     = $blog_posts;
			} elseif ( is_page() ) { // Page.
				$layout = Customify()->get_setting( 'page_sidebar_layout' );
			} elseif ( is_search() ) { // Search.
				$search = Customify()->get_setting( 'search_sidebar_layout' );
				$layout = $search;
			} elseif ( is_post_type_archive() ) { // Custom post type archive.
				$pt = get_query_var( 'post_type' );
				if ( is_array( $pt ) ) {
					$pt = reset( $pt );
				}
				if ( ! $pt ) {
					$queried = get_queried_object();
					$pt      = ( $queried instanceof WP_Post_Type ) ? $queried->name : '';
				}
				$cpt_archive = $pt ? Customify()->get_setting( "{$pt}_archive_sidebar_layout" ) : '';
				// Value 'default' (the "Default" choice) or empty inherits the
				// generic "Blog Archive Page" layout; any other value applies
				// directly. The field default is 'content' (no sidebar), matching
				// the per-CPT single layout.
				if ( $cpt_archive && 'default' !== $cpt_archive ) {
					$layout = $cpt_archive;
				} else {
					$layout = Customify()->get_setting( 'posts_archives_sidebar_layout' );
				}
			} elseif ( is_archive() ) { // Archive.
				$archive = Customify()->get_setting( 'posts_archives_sidebar_layout' );
				$layout  = $archive;
			} elseif ( is_category() || is_tag() || is_singular( 'post' ) ) { // blog page and single page.
				$blog_posts = Customify()->get_setting( 'posts_sidebar_layout' );
				$layout     = $blog_posts;
			} elseif ( is_404() ) { // 404 Page.
				$layout = Customify()->get_setting( '404_sidebar_layout' );
			} elseif ( is_singular() ) {
				$layout = Customify()->get_setting( get_post_type() . '_sidebar_layout' );
			}

			// Support for all posts that using meta settings.
			if ( Customify()->is_using_post() && customify_is_support_meta() ) {

				$post_type   = get_post_type();
				$page_custom = get_post_meta( customify_get_support_meta_id(), '_customify_sidebar', true );

				if ( ! $page_custom ) {
					if ( Customify()->is_woocommerce_active() ) {
						if ( is_cart() || is_checkout() || is_account_page() || is_product() ) {
							$page_custom = 'content';
						}
					}
				}

				if ( $page_custom ) {
					if ( $page_custom && 'default' != $page_custom ) {
						$layout = $page_custom;
					}
				} elseif ( 'page' == $post_type ) {
					$layout = $page;
				}
			}
		}

		if ( ! $layout ) {
			$layout = $default;
		}

		return $layout;
	}
}

if ( ! function_exists( 'customify_force_no_sidebar_for_full_content_layout' ) ) {
	/**
	 * Force no-sidebar layout when the per-post Content Layout is full-width
	 * or full-stretched. These modes hide the sidebar regardless of the
	 * separate sidebar meta — the sidebar dropdown is also hidden in the
	 * block editor's Page Settings panel for the same reason.
	 *
	 * Hooked early on customify_get_layout so the resulting layout flows
	 * through customify_body_classes() (main-layout-content) and
	 * customify_get_sidebars() (no get_sidebar() calls) consistently.
	 *
	 * @param string|null $layout Existing layout from earlier filters.
	 * @return string|null Layout slug or pass-through.
	 */
	function customify_force_no_sidebar_for_full_content_layout( $layout ) {
		if ( $layout ) {
			return $layout;
		}
		if ( ! customify_is_support_meta() ) {
			return $layout;
		}
		$post_id = customify_get_support_meta_id();
		if ( ! $post_id ) {
			return $layout;
		}
		$content_layout = get_post_meta( $post_id, '_customify_content_layout', true );
		if ( in_array( $content_layout, array( 'full-width', 'full-stretched', 'narrow' ), true ) ) {
			return 'content';
		}
		return $layout;
	}
}
add_filter( 'customify_get_layout', 'customify_force_no_sidebar_for_full_content_layout' );

if ( ! function_exists( 'customify_get_sidebars' ) ) {
	/**
	 * Display primary or/and secondary sidebar base on layout setting.
	 *
	 * @since 0.0.1
	 */
	function customify_get_sidebars() {

		// Get the current layout.
		$layout = customify_get_layout();
		if ( ! $layout || 'default' == $layout ) {
			$layout = 'content-sidebar';
		}

		// Layout with 2 column.
		$layout_2_columns = array( 'sidebar-content', 'content-sidebar' );

		// Layout with 3 column.
		$layout_3_columns = array( 'sidebar-sidebar-content', 'sidebar-content-sidebar', 'content-sidebar-sidebar' );

		// Only show primary sidebar for 2 column layout.
		if ( in_array( $layout, $layout_2_columns ) ) { // phpcs:ignore
			get_sidebar();
		}

		// Show both sidebar for 3 column layout.
		if ( in_array( $layout, $layout_3_columns ) ) { // phpcs:ignore
			get_sidebar();
			get_sidebar( 'secondary' );
		}

	}
}
add_action( 'customify/sidebars', 'customify_get_sidebars' );

if ( ! function_exists( 'customify_pingback_header' ) ) {
	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 */
	function customify_pingback_header() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
		}
	}
}
add_action( 'wp_head', 'customify_pingback_header' );

if ( ! function_exists( 'customify_is_support_meta' ) ) {
	function customify_is_support_meta() {
		$support = is_singular();
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			$support = true;
		}

		return $support;
	}
}

if ( ! function_exists( 'customify_get_support_meta_id' ) ) {
	function customify_get_support_meta_id() {
		$id = is_singular() ? get_the_ID() : false;
		if ( is_home() && get_option( 'page_for_posts' ) ) {
			$id = get_option( 'page_for_posts' );
		}

		return $id;
	}
}

if ( ! function_exists( 'customify_is_header_display' ) ) {
	/**
	 * Check if show header
	 *
	 * @return bool
	 */
	function customify_is_header_display() {
		$show = true;
		// page_for_posts.
		if ( customify_is_support_meta() ) {
			$disable = get_post_meta( customify_get_support_meta_id(), '_customify_disable_header', true );
			if ( $disable ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_header_display', $show );
	}
}

if ( ! function_exists( 'customify_is_footer_display' ) ) {
	/**
	 * Check if show header
	 *
	 * @return bool
	 */
	function customify_is_footer_display() {
		$show = true;
		if ( customify_is_support_meta() ) {
			// Derive rows from the same filter the builder pipeline uses so
			// Pro/child-theme/3rd-party rows count toward "is there anything
			// to show?". Hardcoding `main + bottom + maybe top` skipped any
			// other extension row entirely.
			$rows  = function_exists( 'customify_get_footer_row_ids' )
				? customify_get_footer_row_ids()
				: array( 'main', 'bottom' );
			$count = 0;
			foreach ( $rows as $row_id ) {
				if ( ! customify_is_builder_row_display( 'footer', $row_id ) ) {
					$count ++;
				}
			}
			if ( $count >= count( $rows ) ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_footer_display', $show );
	}
}

if ( ! function_exists( 'customify_is_builder_row_display' ) ) {

	/**
	 * Check if show header
	 *
	 * @param string $builder_id
	 * @param bool   $row_id
	 * @param bool   $post_id
	 *
	 * @return mixed
	 */
	function customify_is_builder_row_display( $builder_id, $row_id = false, $post_id = false ) {
		$show = true;
		if ( $row_id && $builder_id ) {
			if ( ! $post_id ) {
				$post_id = apply_filters( 'customify_builder_row_display_get_post_id', customify_get_support_meta_id() );
			}
			$key     = $builder_id . '_' . $row_id;
			$disable = get_post_meta( $post_id, '_customify_disable_' . $key, true );
			if ( $disable ) {
				$show = false;
			}
		}

		return apply_filters( 'customify_is_builder_row_display', $show, $builder_id, $row_id, $post_id );
	}
}

if ( ! function_exists( 'customify_show_post_title' ) ) {
	/**
	 * Check if display title of any post type
	 */
	function customify_is_post_title_display() {
		$show = true;
		if ( Customify()->is_using_post() ) {
			$disable = get_post_meta( Customify()->get_current_post_id(), '_customify_disable_page_title', true );
			if ( $disable ) {
				$show = false;
			}
		}

		$r = apply_filters( 'customify_is_post_title_display', $show );

		return $r;
	}
}


/**
 * Retrieve the archive title based on the queried object.
 *
 * @param string $title
 *
 * @return string Archive title.
 */
function customify_get_the_archive_title( $title ) {
	$disable = Customify()->get_setting( 'page_header_show_archive_prefix' );
	if ( ! $disable ) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'customify' ) );
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'customify' ) );
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'customify' ) );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );
		}
	}

	return $title;
}

add_filter( 'get_the_archive_title', 'customify_get_the_archive_title', 15 );

function customify_search_form( $form ) {
	$form = '
		<form class="sidebar-search-form" action="' . esc_url( home_url( '/' ) ) . '">
            <label>
                <span class="screen-reader-text">' . _x( 'Search for:', 'label', 'customify' ) . '</span>
                <input type="search" class="search-field" placeholder="' . esc_attr__( 'Search &hellip;', 'customify' ) . '" value="' . get_search_query() . '" name="s" title="' . esc_attr_x( 'Search for:', 'label', 'customify' ) . '" />
            </label>
            <button type="submit" class="search-submit" >
                <svg aria-hidden="true" focusable="false" role="presentation" xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21">
                    <path id="svg-search" fill="currentColor" fill-rule="evenodd" d="M12.514 14.906a8.264 8.264 0 0 1-4.322 1.21C3.668 16.116 0 12.513 0 8.07 0 3.626 3.668.023 8.192.023c4.525 0 8.193 3.603 8.193 8.047 0 2.033-.769 3.89-2.035 5.307l4.999 5.552-1.775 1.597-5.06-5.62zm-4.322-.843c3.37 0 6.102-2.684 6.102-5.993 0-3.31-2.732-5.994-6.102-5.994S2.09 4.76 2.09 8.07c0 3.31 2.732 5.993 6.102 5.993z"></path>
                </svg>
            </button>
        </form>';

	return $form;
}

add_filter( 'get_search_form', 'customify_search_form' );
