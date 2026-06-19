<?php
/**
 * Customizer config: Buttons & Form Fields section.
 *
 * A single top-level Customizer section that exposes the global look of the
 * theme's buttons and form controls. Positioned ABOVE the Colors section in the
 * "General Options" panel group (see get_panel_groups() in
 * inc/customizer/class-customizer.php — `customify_buttons_forms` is listed as
 * the FIRST entry, so register_panel_groups() assigns it priority 60 and pushes
 * Colors to 70).
 *
 * ── Control model ────────────────────────────────────────────────────────────
 * Both groups use the theme's existing `styling` composite control — the same
 * "Advanced Styling" popover used by the Header builder and the Colors
 * background controls (defined in get_styling_config(), rendered by
 * inc/customizer/controls/class-control-styling.php, CSS by
 * Customify_Customizer_Auto_CSS::styling()). It bundles Background / Border /
 * Radius / Box Shadow (+ text color, padding) into ONE popover with Normal and
 * Hover (here relabelled "Focus" for fields) tabs.
 *
 * The composite emits per-selector CSS (e.g. `<button selectors> { … }`) the
 * standard theme way — NOT a `:root` custom property. This is deliberate:
 *   • It applies immediately, no SCSS token plumbing required.
 *   • It does NOT go through the `selector => 'format'` live-preview path that
 *     rebuilds (and would drop) the `:root` Palette block — so editing these
 *     controls never disturbs the Colors palette in the Customizer preview.
 *
 * Button selectors mirror the theme's own button rule (base/_forms.scss) so the
 * composite output matches its specificity and, loading later (inline on the
 * `customify-style` handle), wins the cascade — including over WooCommerce
 * buttons.
 *
 * ── 30k-site safety ──────────────────────────────────────────────────────────
 * Every subfield defaults to empty, so `Customify_Customizer_Auto_CSS::styling()`
 * (which loops with `$skip_if_val_null = true`) emits NOTHING until a user
 * actually picks a value. Existing sites render byte-identical. All theme_mod
 * keys (`customify_button_styling`, `customify_field_styling`) are brand new.
 *
 * @package Customify
 */

if ( ! function_exists( 'customify_customizer_buttons_forms_config' ) ) {
	/**
	 * Register the "Buttons & Form Fields" section and its controls.
	 *
	 * @param array $configs Existing Customizer config entries.
	 * @return array
	 */
	function customify_customizer_buttons_forms_config( $configs ) {
		$section = 'customify_buttons_forms';

		// ── Selector lists ───────────────────────────────────────────────────
		// Buttons: mirror the global button rule in base/_forms.scss so the
		// composite output ties its specificity and wins by load order. Built
		// once, then a `:hover` variant is derived for the Hover tab.
		// The bare `button` part also excludes:
		//   - the Customizer preview's selective-refresh edit-shortcut buttons
		//     (round pencil icons WP injects next to editable partials inside
		//     the preview iframe) — they are plain <button>s and would
		//     otherwise get the full theme button skin;
		//   - `.menu-mobile-toggle` — the hamburger trigger is a <button> with
		//     its own dedicated nav-icon styling (transparent bg, no padding,
		//     custom hover). Letting Button Styling reach it paints the
		//     hamburger with the brand button background.
		// Keep every :not() comma-free ($suffix_each below splits on commas).
		$button_selector = '.button:not([class*="wp-block-"]):not(.menu-mobile-toggle), '
			. 'button:not([class*="wp-block-"]):not(.customize-partial-edit-shortcut-button):not(.menu-mobile-toggle), '
			. 'input[type="button"]:not([class*="wp-block-"]), '
			. 'input[type="reset"]:not([class*="wp-block-"]), '
			. 'input[type="submit"]:not([class*="wp-block-"]), '
			. '.wp-block-button__link, '
			. '.wp-element-button:not(.components-button)';

		// Form fields: the input/select/textarea family from base/_forms.scss.
		// `input[type="search"]:not(.search-field)` deliberately EXCLUDES the
		// header/modal search input (`.search-field`) — the Header builder's
		// search item has its own dedicated styling and lives in a distinct
		// header context, so the global Form Fields settings must not leak into
		// it (not even text color). Content search inputs (no `.search-field`
		// class, e.g. the core Search block) still follow Form Fields.
		$field_selector = 'input[type="text"], input[type="email"], input[type="url"], '
			. 'input[type="password"], input[type="search"]:not(.search-field), input[type="number"], '
			. 'input[type="tel"], input[type="date"], input[type="month"], '
			. 'input[type="week"], input[type="time"], input[type="datetime"], '
			. 'input[type="datetime-local"], input[type="color"], select, textarea';

		// Derive `:hover` / `:focus` variants by suffixing each comma-separated
		// selector (none of these selectors contain commas inside :not()).
		$suffix_each = function ( $list, $pseudo ) {
			$parts = array_map(
				function ( $sel ) use ( $pseudo ) {
					return trim( $sel ) . $pseudo;
				},
				explode( ',', $list )
			);
			return implode( ', ', $parts );
		};
		$button_hover = $suffix_each( $button_selector, ':hover' );
		$field_focus  = $suffix_each( $field_selector, ':focus' );

		// Subfields to HIDE in the popover (keep it focused on bg / border /
		// radius / shadow + text color + padding). `false` hides a subfield;
		// see Customify_Customizer_Auto_CSS::setup_styling_fields().
		$hide_decor = array(
			'link_color'    => false,
			'margin'        => false,
			'bg_image'      => false,
			'bg_cover'      => false,
			'bg_position'   => false,
			'bg_repeat'     => false,
			'bg_attachment' => false,
		);

		$config = array(

			// ──────────────────────────────────────────────────────────
			// Top-level Section "Buttons & Form Fields" (root, not in any
			// panel). Positioned as the LAST entry of the "General Options"
			// panel group by get_panel_groups(), which overrides this priority
			// to 80 (below Colors 60 and Typography 70). The 85 here is only the
			// pre-override fallback used before register_panel_groups() runs at
			// 99999 — kept above Colors/Typography so it still sorts last there.
			// ──────────────────────────────────────────────────────────
			array(
				'name'     => $section,
				'type'     => 'section',
				'priority' => 85,
				'title'    => __( 'Buttons & Form Fields', 'customify' ),
			),

			// ════════════════════════════════════════════════════════════
			// BUTTONS
			// ════════════════════════════════════════════════════════════
			array(
				'name'     => 'customify_buttons_heading',
				'type'     => 'heading',
				'section'  => $section,
				'priority' => 10,
				'title'    => __( 'Buttons', 'customify' ),
			),

			// Button styling — Advanced Styling popover (Normal / Hover).
			// Applies to every theme button, including WooCommerce buttons.
			array(
				'name'        => 'customify_button_styling',
				'type'        => 'styling',
				'section'     => $section,
				'priority'    => 11,
				'title'       => __( 'Button Styling', 'customify' ),
				'description' => __( 'All theme buttons.', 'customify' ),
				'selector'    => array(
					'normal' => $button_selector,
					'hover'  => $button_hover,
				),
				'css_format'  => 'styling',
				'default'     => array(
					'normal' => array(),
					'hover'  => array(),
				),
				'fields'      => array(
					'normal_fields' => $hide_decor,
					'hover_fields'  => array(
						'link_color' => false,
					),
				),
			),

			// ════════════════════════════════════════════════════════════
			// FORM FIELDS  (text inputs, select, textarea)
			// ════════════════════════════════════════════════════════════
			array(
				'name'     => 'customify_form_fields_heading',
				'type'     => 'heading',
				'section'  => $section,
				'priority' => 20,
				'title'    => __( 'Form Fields', 'customify' ),
			),

			// Field styling — Advanced Styling popover. The "Hover" tab is
			// relabelled "Focus" and points at the `:focus` selectors so the
			// second tab styles the active/focused state of inputs.
			array(
				'name'        => 'customify_field_styling',
				'type'        => 'styling',
				'section'     => $section,
				'priority'    => 21,
				'title'       => __( 'Field Styling', 'customify' ),
				'description' => __( 'Inputs, selects & textareas. Focus tab = active state.', 'customify' ),
				'selector'    => array(
					'normal' => $field_selector,
					'hover'  => $field_focus,
				),
				'css_format'  => 'styling',
				'default'     => array(
					'normal' => array(),
					'hover'  => array(),
				),
				'fields'      => array(
					'tabs'          => array(
						'normal' => __( 'Normal', 'customify' ),
						'hover'  => __( 'Focus', 'customify' ),
					),
					'normal_fields' => $hide_decor,
					'hover_fields'  => array(
						'link_color' => false,
					),
				),
			),
		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_buttons_forms_config' );
