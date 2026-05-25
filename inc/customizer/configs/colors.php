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
		// Primary CSS: var(--customify-primary, fallback). The fallback hex is the
		// substituted {{value}} (i.e. the saved theme_mod or its default), so legacy
		// browsers without CSS-var support render the saved color directly. Modern
		// browsers read --customify-primary from the :root block — which is fed by
		// the same theme_mod via customify_color_get_slots(), so they render the
		// identical hex. Safe for 30K sites; refactor lets future slot edits
		// propagate to every primary-themed selector via :root only.
		// `[class*="wp-block-"]` negation in every button/input slot below
		// mirrors src/frontend/scss/base/_base.scss button rules — keeps the
		// primary-color background off WP default-block chrome buttons
		// (Search submit, File download, Embed "Try again", etc.) both on
		// the frontend and inside the block editor canvas. `.wp-block-button__link`
		// stays themed because it isn't matched here — Button-block color
		// comes from theme.json + the explicit rule in _base.scss.
		$primary_css = apply_filters(
			'customify/styling/primary-color',
			'
			.header-top .header--row-inner,
			body:not(.fl-builder-edit) :is(.button:not([class*="wp-block-"]), button:not(.menu-mobile-toggle, .components-button, .customize-partial-edit-shortcut-button, .lightbox-trigger, [class*="wp-block-"]), input[type="button"]:not(.ed_button, [class*="wp-block-"])),
			button.button:not([class*="wp-block-"]),
			:is(input[type="button"]:not(.ed_button), input[type="reset"], input[type="submit"]):not(.components-button, .customize-partial-edit-shortcut-button, [class*="wp-block-"]),
			.pagination .nav-links span,
			.pagination .nav-links > *:hover,
			.nav-menu-desktop.style-full-height .primary-menu-ul > li:is(.current-menu-item, .current-menu-ancestor) > a,
			.nav-menu-desktop.style-full-height .primary-menu-ul > li > a:hover,
			.posts-layout .readmore-button:hover
			{
			    background-color: var(--customify-primary, {{value}});
			}
			.posts-layout .readmore-button {
				color: var(--customify-primary, {{value}});
			}
			.pagination .nav-links span,
			.pagination .nav-links > *:hover,
			.entry-single :is(.tags-links, .cat-links) a:hover,
			.posts-layout .readmore-button,
			.posts-layout .readmore-button:hover
			{
			    border-color: var(--customify-primary, {{value}});
			}'
		);

		$secondary_css = apply_filters(
			'customify/styling/secondary-color',
			'

			.customify-builder-btn
			{
			    background-color: var(--customify-secondary, {{value}});
			}'
		);

		// Body / ink CSS: var(--customify-body-text, fallback). Body copy
		// follows slot.text directly via the :root cascade chain
		// `--customify-body-text: var(--customify-text, ...)`. Pure var()
		// passthrough — no mix — so user's Text slot value flows through
		// unchanged (white text on a dark base renders white body copy,
		// not a desaturated grey). Fresh-install shift from legacy
		// `#686868` to slot.text `#2b2b2b`. Saved body override locks
		// the static value.
		$text_css = apply_filters(
			'customify/styling/text-color',
			'
			body
			{
			    color: var(--customify-body-text, {{value}});
			}
			abbr, acronym {
			    border-bottom-color: var(--customify-body-text, {{value}});
			}'
		);

		// Link CSS: var(--customify-link, fallback). Link default aligns with
		// slot.primary so editing the Primary slot cascades to link color.
		// Saved overrides feed both pipelines identically. See SPEC §8.6.
		$link_css = apply_filters(
			'customify/styling/link-color',
			'
                a
                {
                    color: var(--customify-link, {{value}});
				}'
		);

		// Link hover CSS: var(--customify-link-hover, fallback). Hover is a
		// LIGHTER tint of link (15% white mixed in), not darker — matches
		// the design intent that hover surfaces the link by raising
		// luminance against most text contexts.
		$link_hover_css = apply_filters(
			'customify/styling/link-color-hover',
			'
a:hover,
a:focus,
.link-meta:hover, .link-meta a:hover
{
    color: var(--customify-link-hover, {{value}});
}'
		);

		// Border CSS: var(--customify-border, fallback). The :root token
		// is emitted UNCONDITIONALLY (Phase 2.13 follow-up), so the
		// `color-mix(currentcolor 9%, transparent)` fallback only fires
		// when modern browsers see an inherited token (`unset` / `initial`)
		// and on legacy browsers without var() support. Token value is
		// `mix(slot.text, slot.base, 9%)` — fallback + slot mix held at
		// the SAME 9% so the legacy-browser path renders match modern.
		//
		// History: Phase 2.0 used 12% for both paths. Spec §2 bumped slot
		// mix to 14% (`#e1e1e1`) but left the fallback at 12% — created
		// drift, and rendered borders perceptibly darker than the original
		// hardcoded `#eaecee`. PR #398 tuned both paths to 9% so render
		// (`#ECECEC` on default install) is grayscale-equivalent of legacy
		// `#eaecee` average — ΔE ≈ 1.1, imperceptible.
		$border_css = apply_filters(
			'customify/styling/color-border',
			'
h2 + h3,
.comments-area h2 + .comments-title,
.h2 + h3,
.comments-area .h2 + .comments-title,
.page-breadcrumb {
    border-top-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
}
blockquote,
.site-content .widget-area .menu li.current-menu-item > a:before
{
    border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
}

@media screen and (min-width: 64em) {
    .comment-list .children li.comment {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .comment-list .children li.comment:after {
        background-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
}

.page-titlebar, .page-breadcrumb,
.posts-layout .entry-inner {
    border-bottom-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
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
    border-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
}

.header-search-modal::before {
    border-top-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
}

@media screen and (min-width: 48em) {
    .content-sidebar.sidebar_vertical_border .content-area {
        border-right-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .sidebar-content.sidebar_vertical_border .content-area {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-primary {
        border-right-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .sidebar-sidebar-content.sidebar_vertical_border .sidebar-secondary {
        border-right-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-primary {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .content-sidebar-sidebar.sidebar_vertical_border .sidebar-secondary {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
        border-right-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
    .sidebar-content-sidebar.sidebar_vertical_border .content-area {
        border-left-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
        border-right-color: var(--customify-border, color-mix(in srgb, currentcolor 9%, transparent));
    }
}
'
		);

		// Meta CSS: var(--customify-text-muted, fallback). Meta and pagination
		// text share the same `--customify-text-muted` token as body's
		// secondary copy. Fresh-install shift `#6d6d6d` → `#6b6b6b`
		// (2/2/2 RGB) — invisible to the eye.
		$meta_css = apply_filters(
			'customify/styling/color-meta',
			'
			article.comment .comment-post-author {
				background: var(--customify-text-muted, {{value}});
			}
			.pagination .nav-links > *,
			.link-meta,
			.link-meta a,
			.color-meta,
			.entry-single .tags-links:before,
			.entry-single .cats-links:before
			{
			    color: var(--customify-text-muted, {{value}});
			}'
		);

		// Heading CSS: var(--customify-heading, fallback). Field default `#2b2b2b`
		// == slot.text default `#2b2b2b` and --customify-heading resolves to
		// `$ov_heading ?: $slots['text']` — so the fallback hex and the var
		// resolve to identical values on every fresh install. Saved overrides
		// (legacy `global_styling_color_heading`) feed both pipelines via the
		// same theme_mod key. Editing slot.text now also cascades to headings
		// via the modern-browser var() path.
		$heading_css   = apply_filters( 'customify/styling/color-heading', 'h1, h2, h3, h4, h5, h6 { color: var(--customify-heading, {{value}});}' );
		// Widget title CSS: var(--customify-widget-title, fallback). Default
		// shifts from `#444444` to slot.text `#2b2b2b` on fresh install
		// (25/25/25 RGB — moderate but documented design call).
		$w_title_css   = '.site-content .widget-title { color: var(--customify-widget-title, {{value}});}';

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
			// Position: between Styling (60) and Typography (70) in the
			// General Options group (divider at 50). Priority 65 keeps
			// Colors directly above Typography — sidebar order reads
			// General Options → Styling → Colors → Typography → Layouts ….
			array(
				'name'        => $section,
				'type'        => 'section',
				'priority'    => 65,
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

			// Slot 5: Surface — NEW key. Default changed from #FFFFFF to
			// #ECECEC so the Surface slot reads as a distinct elevated
			// container colour against the page Base (which stays white).
			// Frontend selectors that consume `--customify-surface` will
			// be wired in a follow-up.
			array(
				'name'        => 'customify_palette_surface',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 14,
				'title'       => __( 'Surface', 'customify' ),
				'description' => __( 'Card / elevated container background.', 'customify' ),
				'default'     => '#ECECEC',
				'placeholder' => '#ECECEC',
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

			// ── HEADING: Colors (link + link-hover + heading overrides) ──
			// Renamed from "Links" to "Colors" once the Heading override
			// was promoted out of Legacy fine-tuning. This group now holds
			// the every-page visible text colors that have user-friendly
			// pickers (vs. the more obscure overrides hidden in Legacy
			// fine-tuning below).
			array(
				'name'     => "{$section}_h_links",
				'type'     => 'heading',
				'section'  => $section,
				'title'    => __( 'Colors', 'customify' ),
				'priority' => 20,
			),

			// Link color — REUSE existing key. Default aligned with
			// slot.primary so editing Primary cascades to links. Fresh
			// installs shift from legacy `#1e4b75` to `#235787` (slot.primary)
			// — small RGB delta (5/12/18), documented design change.
			array(
				'name'        => 'global_styling_color_link',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 25,
				'title'       => __( 'Link color', 'customify' ),
				'description' => __( 'Default: derived from Primary slot.', 'customify' ),
				'default'     => '#235787',
				'placeholder' => '#235787',
				'css_format'  => $link_css,
				'selector'    => 'format',
			),

			// Link hover color — REUSE existing key. Default = link lighter
			// 15% (mix with white) so the hover state surfaces the link by
			// raising luminance. Fresh installs shift from legacy `#111111`
			// (near-black) to `#406F99` — a much bigger jump than other
			// overrides; explicit design call by the project owner.
			array(
				'name'        => 'global_styling_color_link_hover',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 26,
				'title'       => __( 'Link hover color', 'customify' ),
				'description' => __( 'Default: derived (Link color lighter 15%).', 'customify' ),
				'default'     => '#406F99',
				'placeholder' => '#406F99',
				'css_format'  => $link_hover_css,
				'selector'    => 'format',
			),

			// Heading override — REUSE existing key. Promoted out of the
			// Legacy fine-tuning group because Heading color is a common
			// design knob (vs. the more obscure border/widget-title/etc.
			// that stay hidden in Legacy). Priority 27 keeps it directly
			// below Link hover color inside the Colors group.
			array(
				'name'        => 'global_styling_color_heading',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 27,
				'title'       => __( 'Heading color', 'customify' ),
				'description' => __( 'Default: derived from Text slot.', 'customify' ),
				'placeholder' => '#2b2b2b',
				'default'     => '#2b2b2b',
				'css_format'  => $heading_css,
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
			// Default bg_color empty so auto-CSS doesn't emit a literal
			// `body{background-color:#FFFFFF}` for unsaved sites. That lets
			// the var(--customify-base) cascade in customify-palette-tokens
			// take over the body bg when the user hasn't picked a composite.
			// Saved sites still emit `body{background-color:<saved>}` and
			// win the cascade (composite inline loads AFTER palette-tokens).
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
						'bg_color' => '',
					),
				),
				'css_format' => 'styling',
				'fields'     => $bg_only_fields,
			),

			// Content Area Background (composite) — REUSE existing key.
			// Same empty-default pattern as Page Background.
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
						'bg_color' => '',
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

			// ── HEADING: Legacy fine-tuning (collapsible) ──
			// Hidden by default — these are the long-standing per-component
			// pickers from pre-Phase-1 styling.php. They still beat the
			// Palette cascade when saved, but the new architecture pushes
			// users toward editing the 6 slots and letting derivations flow.
			// JS handler (in colors-palette.php quickpick script) toggles
			// every control with priority >= 55 inside this section based
			// on the id `customify_colors_h_overrides`.
			array(
				'name'        => "{$section}_h_overrides",
				'type'        => 'heading',
				'section'     => $section,
				'title'       => __( 'Legacy fine-tuning', 'customify' ),
				'description' => __( 'Per-element color overrides. Beats the Palette cascade when saved. Click to expand.', 'customify' ),
				'priority'    => 50,
			),

			// Override: body text — REUSE existing key. Default aligned with
			// slot.text (`#2b2b2b`) so body copy renders exactly the Text
			// slot value unless explicitly overridden. Inverted from the
			// earlier color-mix(88%) approach — that desaturated user's
			// Text picks (e.g. pure white on dark base would render ~#e0e0e0).
			array(
				'name'        => 'global_styling_color_text',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 55,
				'title'       => __( 'Body text override', 'customify' ),
				'placeholder' => '#2b2b2b',
				'default'     => '#2b2b2b',
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
				// Border field default aligned with slot-derived
				// `mix(text, base, 9%)` = `#ECECEC` — grayscale equivalent
				// of legacy hardcoded `#eaecee`. Picker dirty-state compares
				// `input.value` to this `data-default`; aligning here means
				// typing the rendered slot-mix into the picker doesn't
				// false-flag as dirty.
				'placeholder' => '#ECECEC',
				'default'     => '#ECECEC',
				'css_format'  => $border_css,
				'selector'    => 'format',
			),

			// Override: meta — REUSE existing key. Default aligned with
			// `mix(text, base, 70%)` = `#6b6b6b` (was `#6d6d6d`).
			array(
				'name'        => 'global_styling_color_meta',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 57,
				'title'       => __( 'Meta text override', 'customify' ),
				'placeholder' => '#6b6b6b',
				'default'     => '#6b6b6b',
				'css_format'  => $meta_css,
				'selector'    => 'format',
			),

			// Heading override moved out — see "Colors" group above
			// (priority 27, registered alongside Link / Link hover).

			// Override: widget title — REUSE existing key. Default aligned
			// with slot.text `#2b2b2b` (was `#444444`).
			array(
				'name'        => 'global_styling_color_w_title',
				'type'        => 'color',
				'section'     => $section,
				'priority'    => 59,
				'title'       => __( 'Widget title override', 'customify' ),
				'placeholder' => '#2b2b2b',
				'default'     => '#2b2b2b',
				'css_format'  => $w_title_css,
				'selector'    => 'format',
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_colors_config' );
