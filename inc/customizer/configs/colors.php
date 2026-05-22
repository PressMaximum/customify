<?php
/**
 * Customizer config: Colors section.
 *
 * Top-level Customizer section that consolidates all site-wide color settings.
 * Flat layout grouped by customify_heading dividers:
 *   - Palette (6 slots)
 *   - Links (link + link hover)
 *   - Backgrounds (page / content area / content container composites)
 *   - Component overrides (text / border / meta / heading / widget title)
 *
 * Storage:
 *   - Slot primary / secondary REUSE existing keys (global_styling_color_primary,
 *     global_styling_color_secondary) so saved values on 30K+ legacy sites are
 *     preserved byte-for-byte.
 *   - Slot base / surface / text / accent use NEW keys (customify_palette_*).
 *   - Link / link-hover / 5 component overrides / 3 background composites all
 *     REUSE their existing legacy keys.
 *
 * 30K-site safety: all existing theme_mod keys are preserved. css_format for
 * reused fields is copied verbatim from styling.php / background.php so the
 * inline-style CSS output is identical for legacy sites.
 *
 * @package Customify
 */

if ( ! function_exists( 'customify_customizer_colors_config' ) ) {
	function customify_customizer_colors_config( $configs ) {
		$section = 'customify_colors';

		// CSS format strings — copied verbatim from styling.php so byte-equivalent
		// CSS is emitted on legacy sites with saved values.
		$primary_css = apply_filters(
			'customify/styling/primary-color',
			'
			.header-top .header--row-inner,
			body:not(.fl-builder-edit) :is(.button, button:not(.menu-mobile-toggle, .components-button, .customize-partial-edit-shortcut-button, .lightbox-trigger, .wp-block-navigation__submenu-icon), input[type="button"]:not(.ed_button)),
			button.button,
			:is(input[type="button"]:not(.ed_button), input[type="reset"], input[type="submit"]):not(.components-button, .customize-partial-edit-shortcut-button),
			.pagination .nav-links span,
			.pagination .nav-links > *:hover,
			.nav-menu-desktop.style-full-height .primary-menu-ul > li:is(.current-menu-item, .current-menu-ancestor) > a,
			.nav-menu-desktop.style-full-height .primary-menu-ul > li > a:hover,
			.posts-layout .readmore-button:hover
			{
			    background-color: {{value}};
			}
			.posts-layout .readmore-button {
				color: {{value}};
			}
			.pagination .nav-links span,
			.pagination .nav-links > *:hover,
			.entry-single :is(.tags-links, .cat-links) a:hover,
			.posts-layout .readmore-button,
			.posts-layout .readmore-button:hover
			{
			    border-color: {{value}};
			}'
		);

		$secondary_css = apply_filters(
			'customify/styling/secondary-color',
			'

			.customify-builder-btn
			{
			    background-color: {{value}};
			}'
		);

		$text_css = apply_filters(
			'customify/styling/text-color',
			'
			body
			{
			    color: {{value}};
			}
			abbr, acronym {
			    border-bottom-color: {{value}};
			}'
		);

		$link_css = apply_filters(
			'customify/styling/link-color',
			'
                a
                {
                    color: {{value}};
				}'
		);

		$link_hover_css = apply_filters(
			'customify/styling/link-color-hover',
			'
a:hover,
a:focus,
.link-meta:hover, .link-meta a:hover
{
    color: {{value}};
}'
		);

		$border_css = apply_filters(
			'customify/styling/color-border',
			'
h2 + h3,
.comments-area h2 + .comments-title,
.h2 + h3,
.comments-area .h2 + .comments-title,
.page-breadcrumb {
    border-top-color: {{value}};
}
blockquote,
.site-content .widget-area .menu li.current-menu-item > a:before
{
    border-left-color: {{value}};
}

@media screen and (min-width: 64em) {
    .comment-list .children li.comment {
        border-left-color: {{value}};
    }
    .comment-list .children li.comment:after {
        background-color: {{value}};
    }
}

.page-titlebar, .page-breadcrumb,
.posts-layout .entry-inner {
    border-bottom-color: {{value}};
}

.header-search-form .search-field,
.entry-content .page-links a,
.header-search-modal,
.pagination .nav-links > *,
.entry-footer .tags-links a, .entry-footer .cat-links a,
.search .content-area article,
.site-content .widget-area .menu li.current-menu-item > a,
.posts-layout .entry-inner,
.post-navigation .nav-links,
article.comment .comment-meta,
.widget-area .widget_pages li a, .widget-area .widget_categories li a, .widget-area .widget_archive li a, .widget-area .widget_meta li a, .widget-area .widget_nav_menu li a, .widget-area .widget_product_categories li a, .widget-area .widget_recent_entries li a, .widget-area .widget_rss li a,
.widget-area .widget_recent_comments li
{
    border-color: {{value}};
}

.header-search-modal::before {
    border-top-color: {{value}};
    border-left-color: {{value}};
}

@media screen and (min-width: 48em) {
    .content-sidebar.sidebar_vertical_border .content-area {
        border-right-color: {{value}};
    }
    .sidebar-content.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-primary {
        border-right-color: {{value}};
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-secondary {
        border-right-color: {{value}};
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-primary {
        border-left-color: {{value}};
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-secondary {
        border-left-color: {{value}};
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
        border-right-color: {{value}};
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: {{value}};
        border-right-color: {{value}};
    }
}
'
		);

		$meta_css = apply_filters(
			'customify/styling/color-meta',
			'
			article.comment .comment-post-author {
				background: {{value}};
			}
			.pagination .nav-links > *,
			.link-meta,
			.link-meta a,
			.color-meta,
			.entry-single .tags-links:before,
			.entry-single .cats-links:before
			{
			    color: {{value}};
			}'
		);

		$heading_css   = apply_filters( 'customify/styling/color-heading', 'h1, h2, h3, h4, h5, h6 { color: {{value}};}' );
		$w_title_css   = '.site-content .widget-title { color: {{value}};}';

		// Composite styling control exclusion list — only expose background-related
		// subfields; hide text/link/padding/margin/border/shadow.
		$bg_only_fields = array(
			'normal_fields' => array(
				'text_color'     => false,
				'link_color'     => false,
				'padding'        => false,
				'margin'         => false,
				'border_heading' => false,
				'border_width'   => false,
				'border_color'   => false,
				'border_radius'  => false,
				'box_shadow'     => false,
				'border_style'   => false,
			),
			'hover_fields'  => false,
		);

		$config = array(

			// ──────────────────────────────────────────────────────────
			// Top-level Section "Colors" (root, not in any panel).
			// ──────────────────────────────────────────────────────────
			array(
				'name'        => $section,
				'type'        => 'section',
				'priority'    => 21,
				'title'       => __( 'Colors', 'customify' ),
				'description' => __( 'Pick 6 brand colors. The theme derives everything else.', 'customify' ),
			),

			// ── HEADING: Palette ──
			array(
				'name'     => "{$section}_h_palette",
				'type'     => 'heading',
				'section'  => $section,
				'title'    => __( 'Palette', 'customify' ),
				'priority' => 5,
			),

			// Display order is brand-first: Primary → Secondary → Accent →
			// Text → Surface → Base. Storage keys + slot identities are
			// unchanged — only the priority values control where each
			// renders inside the Palette heading group.

			// Slot 1: Primary — REUSE existing global_styling_color_primary key.
			array(
				'name'        => 'global_styling_color_primary',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 10,
				'title'       => __( 'Primary', 'customify' ),
				'description' => __( 'Brand color, CTAs.', 'customify' ),
				'default'     => '#235787',
				'placeholder' => '#235787',
				'css_format'  => $primary_css,
				'selector'    => 'format',
			),

			// Slot 2: Secondary — REUSE existing global_styling_color_secondary key.
			array(
				'name'        => 'global_styling_color_secondary',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 11,
				'title'       => __( 'Secondary', 'customify' ),
				'description' => __( 'Secondary brand color.', 'customify' ),
				'default'     => '#c3512f',
				'placeholder' => '#c3512f',
				'css_format'  => $secondary_css,
				'selector'    => 'format',
			),

			// Slot 3: Accent — NEW key.
			array(
				'name'        => 'customify_palette_accent',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 12,
				'title'       => __( 'Accent', 'customify' ),
				'description' => __( 'Highlight / decorative pop.', 'customify' ),
				'default'     => '#FFD042',
				'placeholder' => '#FFD042',
				'selector'    => 'format',
				'css_format'  => '',
			),

			// Slot 4: Text — NEW key (ink baseline; body uses derived muted).
			array(
				'name'        => 'customify_palette_text',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 13,
				'title'       => __( 'Text', 'customify' ),
				'description' => __( 'Ink baseline. Headings inherit from this slot by default.', 'customify' ),
				'default'     => '#2b2b2b',
				'placeholder' => '#2b2b2b',
				'selector'    => 'format',
				'css_format'  => '',
			),

			// Slot 5: Surface — NEW key.
			array(
				'name'        => 'customify_palette_surface',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 14,
				'title'       => __( 'Surface', 'customify' ),
				'description' => __( 'Card / elevated container background.', 'customify' ),
				'default'     => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'selector'    => 'format',
				'css_format'  => '',
			),

			// Slot 6: Base — NEW key.
			array(
				'name'        => 'customify_palette_base',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 15,
				'title'       => __( 'Base', 'customify' ),
				'description' => __( 'Page background.', 'customify' ),
				'default'     => '#FFFFFF',
				'placeholder' => '#FFFFFF',
				'selector'    => 'format',
				'css_format'  => '',
			),

			// ── HEADING: Links ──
			array(
				'name'     => "{$section}_h_links",
				'type'     => 'heading',
				'section'  => $section,
				'title'    => __( 'Links', 'customify' ),
				'priority' => 20,
			),

			// Link color — REUSE existing key.
			array(
				'name'        => 'global_styling_color_link',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 25,
				'title'       => __( 'Link color', 'customify' ),
				'description' => __( 'Default: derived from Primary slot.', 'customify' ),
				'default'     => '#1e4b75',
				'placeholder' => '#1e4b75',
				'css_format'  => $link_css,
				'selector'    => 'format',
			),

			// Link hover color — REUSE existing key.
			array(
				'name'        => 'global_styling_color_link_hover',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 26,
				'title'       => __( 'Link hover color', 'customify' ),
				'description' => __( 'Default: derived (Primary darken 15%).', 'customify' ),
				'default'     => '#111111',
				'placeholder' => '#111111',
				'css_format'  => $link_hover_css,
				'selector'    => 'format',
			),

			// ── HEADING: Backgrounds ──
			array(
				'name'     => "{$section}_h_backgrounds",
				'type'     => 'heading',
				'section'  => $section,
				'title'    => __( 'Backgrounds', 'customify' ),
				'priority' => 30,
			),

			// Page Background (composite) — REUSE existing key `background`.
			array(
				'name'       => 'background',
				'type'       => 'styling',
				'section'    => $section,
				'priority'   => 35,
				'title'      => __( 'Page Background', 'customify' ),
				'selector'   => array(
					'normal' => 'body',
				),
				'default'    => array(
					'normal' => array(
						'bg_color' => '#FFFFFF',
					),
				),
				'css_format' => 'styling',
				'fields'     => $bg_only_fields,
			),

			// Content Area Background (composite) — REUSE existing key.
			array(
				'name'       => 'site_content_styling',
				'type'       => 'styling',
				'section'    => $section,
				'priority'   => 36,
				'title'      => __( 'Content Area Background', 'customify' ),
				'selector'   => array(
					'normal' => '.site-content .content-area',
				),
				'default'    => array(
					'normal' => array(
						'bg_color' => '#FFFFFF',
					),
				),
				'css_format' => 'styling',
				'fields'     => $bg_only_fields,
			),

			// Site Content Background container (composite) — REUSE existing key.
			array(
				'name'       => 'content_background',
				'type'       => 'styling',
				'section'    => $section,
				'priority'   => 37,
				'title'      => __( 'Site Content Background', 'customify' ),
				'selector'   => array(
					'normal' => '.site-content',
				),
				'css_format' => 'styling',
				'fields'     => $bg_only_fields,
			),

			// ── HEADING: Component overrides ──
			array(
				'name'        => "{$section}_h_overrides",
				'type'        => 'heading',
				'section'     => $section,
				'title'       => __( 'Component overrides (advanced)', 'customify' ),
				'description' => __( 'Override individual element colors. Leave blank to inherit from Palette.', 'customify' ),
				'priority'    => 50,
			),

			// Override: body text — REUSE existing key.
			array(
				'name'        => 'global_styling_color_text',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 55,
				'title'       => __( 'Body text override', 'customify' ),
				'placeholder' => '#686868',
				'default'     => '#686868',
				'css_format'  => $text_css,
				'selector'    => 'format',
			),

			// Override: border — REUSE existing key.
			array(
				'name'        => 'global_styling_color_border',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 56,
				'title'       => __( 'Border override', 'customify' ),
				'placeholder' => '#eaecee',
				'default'     => '#eaecee',
				'css_format'  => $border_css,
				'selector'    => 'format',
			),

			// Override: meta — REUSE existing key.
			array(
				'name'        => 'global_styling_color_meta',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 57,
				'title'       => __( 'Meta text override', 'customify' ),
				'placeholder' => '#6d6d6d',
				'default'     => '#6d6d6d',
				'css_format'  => $meta_css,
				'selector'    => 'format',
			),

			// Override: heading — REUSE existing key.
			array(
				'name'        => 'global_styling_color_heading',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 58,
				'title'       => __( 'Heading override', 'customify' ),
				'placeholder' => '#2b2b2b',
				'default'     => '#2b2b2b',
				'css_format'  => $heading_css,
				'selector'    => 'format',
			),

			// Override: widget title — REUSE existing key.
			array(
				'name'        => 'global_styling_color_w_title',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 59,
				'title'       => __( 'Widget title override', 'customify' ),
				'placeholder' => '#444444',
				'default'     => '#444444',
				'css_format'  => $w_title_css,
				'selector'    => 'format',
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_colors_config' );
