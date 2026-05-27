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

		// Inline CSS templates intentionally removed in the colors→SCSS migration.
		// Every per-selector rule that lived in $primary_css / $secondary_css /
		// $text_css / $link_css / $link_hover_css / $border_css / $meta_css /
		// $heading_css / $w_title_css now lives in the bundled SCSS, consuming
		// the same `--customify-*` tokens via the `$color_*` SCSS variables
		// declared in src/frontend/scss/utils/_vars.scss.
		//
		// :root variables themselves continue to be emitted UNCONDITIONALLY by
		// inc/colors-palette.php (`:root{--customify-primary: …; --customify-link: …; …}`)
		// on every page render, so saved theme_mod overrides keep flowing into
		// the same tokens. Customizer live preview drives the same vars via
		// document.documentElement.style.setProperty (see colors-palette.php
		// preview JS at the bottom of that file) — no inline CSS template is
		// consulted in either path.
		//
		// 30K-site safety: storage keys unchanged. Modern browsers read the
		// `:root` var; legacy browsers without var() support read the
		// `$color_X = var(--customify-X, fallback_hex)` fallback baked into the
		// compiled stylesheet. Fallback hexes in _vars.scss were aligned to the
		// PHP defaults during this migration so the two paths render identical.
		//
		// WC selector additions previously hooked via the per-token filters
		// (customify/styling/{primary-color,secondary-color,link-color,color-border,color-meta})
		// now live in src/frontend/scss/compatibility/wc/*.scss alongside the
		// rest of the WooCommerce visual chrome.

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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
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
				'css_format'  => '',
				'selector'    => 'format',
			),

		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_colors_config' );

// ──────────────────────────────────────────────────────────────────
// Customizer preview wiring for the 13 color settings.
//
// The Customify framework auto-detects postMessage transport from a
// field's css_format (class-customizer.php:1199-1211). After the colors→SCSS
// migration the 13 color controls have `css_format => ''`, leaving transport
// at the framework default `refresh` — Customizer would reload the whole
// preview on every picker drag.
//
// This function:
//   1. Forces transport=postMessage on all 13 color settings (idempotent
//      overlap with customify_color_palette_force_postmessage() in
//      colors-palette.php which already covers the 4 new slot keys —
//      re-setting postMessage on the same setting is a no-op).
//   2. Registers a single selective_refresh partial whose selector is the
//      `<style id='customify-palette-tokens-inline-css'>` tag printed by
//      Customify::print_palette_tokens(). Render callback re-runs the same
//      customify_color_palette_root_css() PHP that generated the initial
//      block, so every derived/computed token (--customify-on-primary,
//      --customify-{primary,secondary,accent}-container, container chroma
//      caps, on-X-container contrast picks, etc.) is recomputed live in the
//      preview rather than staying stale until save+reload.
//
// Pairing with the existing setProperty preview JS in colors-palette.php:
//   - That JS handles BASE tokens (--customify-primary, --customify-text,
//     etc.) instantly during drag via document.documentElement.style.
//   - This partial fires after the setting-change debounce (~250ms) and
//     replaces the style-tag innerHTML with the full re-rendered :root
//     block, so PHP-only computed tokens catch up without a save.
//   - End state is identical regardless of which mechanism wins each frame:
//     both read the same previewed theme_mod values.
//
// `container_inclusive => false` means the partial replaces the inner CSS
// content of the <style> tag, not the tag itself. customify_color_palette_root_css()
// returns the CSS body without a <style> wrapper (matches the way
// class-customify.php prints it).
//
// Priority 1100 runs after the framework registration (default 10) and
// after customify_color_palette_force_postmessage (priority 1000), so all
// settings exist when this hook fires.
// ──────────────────────────────────────────────────────────────────

if ( ! function_exists( 'customify_colors_register_preview_refresh' ) ) {
	function customify_colors_register_preview_refresh( $wp_customize ) {
		$color_settings = array(
			// 6 palette slots
			'global_styling_color_primary',
			'global_styling_color_secondary',
			'customify_palette_accent',
			'customify_palette_text',
			'customify_palette_surface',
			'customify_palette_base',
			// 7 component overrides
			'global_styling_color_link',
			'global_styling_color_link_hover',
			'global_styling_color_heading',
			'global_styling_color_text',
			'global_styling_color_meta',
			'global_styling_color_border',
			'global_styling_color_w_title',
		);

		foreach ( $color_settings as $setting_id ) {
			$setting = $wp_customize->get_setting( $setting_id );
			if ( $setting ) {
				$setting->transport = 'postMessage';
			}
		}

		if ( isset( $wp_customize->selective_refresh ) && function_exists( 'customify_color_palette_root_css' ) ) {
			$wp_customize->selective_refresh->add_partial(
				'customify_palette_tokens',
				array(
					'selector'            => '#customify-palette-tokens-inline-css',
					'settings'            => $color_settings,
					'render_callback'     => 'customify_color_palette_root_css',
					'container_inclusive' => false,
				)
			);
		}
	}
	add_action( 'customize_register', 'customify_colors_register_preview_refresh', 1100 );
}
