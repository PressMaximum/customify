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
 * Button selectors mirror the theme's own button rule (base/_forms.scss) — see
 * customify_get_button_styling_selectors() below for the scope contract shared
 * by the two layers.
 *
 * ── 30k-site safety ──────────────────────────────────────────────────────────
 * Every subfield defaults to empty, so `Customify_Customizer_Auto_CSS::styling()`
 * (which loops with `$skip_if_val_null = true`) emits NOTHING until a user
 * actually picks a value. Existing sites render byte-identical. All theme_mod
 * keys (`customify_button_styling`, `customify_field_styling`) are brand new.
 *
 * @package Customify
 */

if ( ! function_exists( 'customify_split_selector_list' ) ) {
	/**
	 * Split a CSS selector list on its TOP-LEVEL commas.
	 *
	 * `explode( ',', … )` cannot be used on the scopes built below: they contain
	 * functional pseudo-classes whose arguments are themselves comma-separated
	 * (`:not(a, b)`, `:is(a, b)`). Splitting inside those parentheses yields
	 * invalid selectors, which is why the previous revision of this file had to
	 * carry a "keep every :not() comma-free" constraint — a constraint that
	 * forced the chained `:not(a):not(b):not(c)…` form and, with it, runaway
	 * specificity (each chained `:not()` adds a whole class level).
	 *
	 * @param string $list CSS selector list.
	 * @return array<int, string> Individual selectors, trimmed, empties removed.
	 */
	function customify_split_selector_list( $list ) {
		$parts = array();
		$buffer = '';
		$depth  = 0;
		$length = strlen( $list );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $list[ $i ];

			if ( '(' === $char ) {
				$depth++;
			} elseif ( ')' === $char ) {
				$depth--;
			} elseif ( ',' === $char && 0 === $depth ) {
				$parts[] = trim( $buffer );
				$buffer  = '';
				continue;
			}

			$buffer .= $char;
		}

		$parts[] = trim( $buffer );

		return array_values(
			array_filter(
				$parts,
				function ( $part ) {
					return '' !== $part;
				}
			)
		);
	}
}

if ( ! function_exists( 'customify_suffix_selector_list' ) ) {
	/**
	 * Append a pseudo-class to every selector in a list.
	 *
	 * Used to derive the `:hover` / `:focus` variant of a scope.
	 *
	 * @param string $list   CSS selector list.
	 * @param string $pseudo Pseudo-class to append, e.g. `:hover`.
	 * @return string
	 */
	function customify_suffix_selector_list( $list, $pseudo ) {
		return implode(
			', ',
			array_map(
				function ( $selector ) use ( $pseudo ) {
					return $selector . $pseudo;
				},
				customify_split_selector_list( $list )
			)
		);
	}
}

if ( ! function_exists( 'customify_get_button_styling_selectors' ) ) {
	/**
	 * The scope of the global "Button Styling" control.
	 *
	 * ── Why this is an allowlist ────────────────────────────────────────────
	 * Until 0.4.22 this scope matched the BARE `button` element and tried to
	 * subtract everything that is not a real button with a denylist of
	 * individual class names. That approach failed structurally, twice over:
	 *
	 *  1. A denylist cannot enumerate third-party chrome. Every icon control a
	 *     plugin renders as `<button class="…">` inherited the CTA skin —
	 *     Blocksify's product-gallery thumbnails and thumb arrows
	 *     (`.bsy-gallery__thumb`, `.bsy-gallery__thumb-nav`), lightGallery's
	 *     `.lg-prev` / `.lg-next` / `.lg-close` / `.lg-zoom-in` /
	 *     `.lg-fullscreen` / `.lg-autoplay-button` / `.lg-share`, PhotoSwipe's
	 *     `.pswp__button`, and so on. Each one needed its own carve-out, and
	 *     the next plugin needed the next carve-out.
	 *  2. Chained `:not()` inflates specificity: EACH `:not()` contributes a
	 *     full class level. The 0.4.22 selector
	 *     `button:not(a):not(b):not(c):not(d):not(e):not(f)` scored (0,6,1) and
	 *     is emitted inline on the `customify-style` handle (i.e. last), so it
	 *     outranked the component's own stylesheet and could not be corrected
	 *     downstream by the plugin, the child theme or custom CSS.
	 *
	 * ── The rule now ────────────────────────────────────────────────────────
	 * A button is in scope when it OPTS IN by contract, or when it is
	 * structurally a content/form control:
	 *
	 *  • Opt-in classes: `.button`, `.wp-block-button__link`,
	 *    `.wp-element-button`, plus the `input` button types.
	 *  • The bare `button` ELEMENT only when it is
	 *      – an explicit `[type="submit"]` or `[type="reset"]`, or
	 *      – UNCLASSED (`:not([class])`) — author-written content markup, or
	 *      – inside a `<form>` with no `type` attribute at all, which per the
	 *        HTML spec IS a submit button.
	 *
	 * Component chrome is, without exception, a CLASSED `type="button"`
	 * element — that is what every icon control in every library emits — so it
	 * now falls outside the scope structurally, with no per-library carve-out
	 * and no future whack-a-mole.
	 *
	 * ── Specificity contract (deliberate, not incidental) ───────────────────
	 *  • `.button` branch → (0,3,0). Load-bearing: it must keep outranking
	 *    `.button.add_to_cart_button` / `.button.alt` (0,2,0) in
	 *    compatibility/wc/_wc-elements.scss, otherwise saved Button Styling
	 *    would stop applying to WooCommerce add-to-cart / checkout buttons.
	 *  • bare `button` branch → (0,3,1), down from (0,6,1). The extra levels
	 *    only ever existed to out-shout chrome the scope no longer touches.
	 *  • `input[...]` branches → (0,2,0), unchanged from 0.4.22.
	 *
	 * Exclusion lists are single comma-separated `:not()` arguments, so adding
	 * or removing an entry NEVER changes the specificity again.
	 *
	 * ── Theme-owned chrome ──────────────────────────────────────────────────
	 * `.search-submit` and `.menu-mobile-toggle` are the theme's own icon
	 * controls: an SVG magnifier pulled back over the search field, and the
	 * hamburger. Both have dedicated Header-builder styling, exactly like
	 * `.search-field` is excluded from the Field Styling scope below. They are
	 * excluded here so a saved Button Styling value cannot repaint them —
	 * their context rules (header/_search.scss, widgets/_widgets.scss) live in
	 * the bundled stylesheet and can never outrank late inline CSS.
	 *
	 * NOTE: base/_forms.scss carries the same scope for the theme's DEFAULT
	 * button skin, with two documented differences — it keeps `.search-submit`
	 * in scope (that default is what 30k sites already render, and the header /
	 * widget context rules do successfully override the low-specificity bundled
	 * rule), and it uses `:where()` for the chrome exclusions to hold its
	 * historical (0,1,1). Keep the two in lockstep when editing either.
	 *
	 * @return array{normal:string,hover:string}
	 */
	function customify_get_button_styling_selectors() {
		// Block-editor / WooCommerce-blocks chrome. `[class*="wp-block-"]` and
		// `[class*="wc-block-"]` peel off ANY element carrying such a token —
		// that is how core blocks (Search submit, File download, Embed "Try
		// again") and WooCommerce blocks (quantity steppers, filter toggles)
		// render their internal buttons.
		$chrome_blocks = '[class*="wp-block-"], [class*="wc-block-"]';

		// Non-block chrome: editor / Customizer UI (`.components-button`,
		// `.customize-partial-edit-shortcut-button`, the classic editor's
		// `.ed_button` quicktags, WooCommerce's `.lightbox-trigger`) plus the
		// theme's own icon controls. Most are already excluded by the intent
		// gate — they are classed `type="button"` elements — but naming them
		// costs nothing (single `:not()` list) and documents intent.
		$chrome_ui = '.components-button, .customize-partial-edit-shortcut-button, .ed_button, .lightbox-trigger, .search-submit, .menu-mobile-toggle';

		// Intent gate for the bare `button` element (see docblock).
		$bare_gate = '[type="submit"], [type="reset"], :not([class])';

		$selectors = array(
			// Opt-in class. Two chained :not() → (0,3,0), see contract above.
			'.button:not(' . $chrome_blocks . '):not(' . $chrome_ui . ')',

			// Bare element, intent-gated → (0,3,1).
			'button:is(' . $bare_gate . '):not(' . $chrome_blocks . '):not(' . $chrome_ui . ')',

			// A <button> in a <form> with no type attribute is a submit button
			// per the HTML spec, whatever classes it carries (WPForms, Mailchimp
			// and friends ship exactly this markup).
			'form button:not([type]):not(' . $chrome_blocks . '):not(' . $chrome_ui . ')',

			// input button types are form controls by definition — no gate
			// needed. Single :not() list each → (0,2,0), unchanged from 0.4.22.
			'input[type="button"]:not(' . $chrome_blocks . ', ' . $chrome_ui . ')',
			'input[type="reset"]:not(' . $chrome_blocks . ', ' . $chrome_ui . ')',
			'input[type="submit"]:not(' . $chrome_blocks . ', ' . $chrome_ui . ')',

			// Core block / theme.json button contracts.
			'.wp-block-button__link',
			'.wp-element-button:not(.components-button)',
		);

		$normal = implode( ', ', $selectors );

		/**
		 * Filter the global Button Styling scope.
		 *
		 * Lets a child theme or Customify Pro extend the scope (e.g. add a
		 * page-builder's button class) without forking this file. The `:hover`
		 * variant is derived from the filtered value, so both states stay
		 * consistent.
		 *
		 * @since 0.4.23
		 *
		 * @param string $normal Comma-separated selector list.
		 */
		$normal = apply_filters( 'customify/buttons_forms/button_selector', $normal );

		return array(
			'normal' => $normal,
			'hover'  => customify_suffix_selector_list( $normal, ':hover' ),
		);
	}
}

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
		// Buttons: see customify_get_button_styling_selectors() for the scope
		// contract (allowlist + intent gate) and the specificity rationale.
		$button = customify_get_button_styling_selectors();

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

		$field_focus = customify_suffix_selector_list( $field_selector, ':focus' );

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
			// Applies to content and form buttons, including WooCommerce
			// buttons. Component chrome (gallery, lightbox, steppers, icon
			// toggles, editor UI) is out of scope by construction.
			array(
				'name'        => 'customify_button_styling',
				'type'        => 'styling',
				'section'     => $section,
				'priority'    => 11,
				'title'       => __( 'Button Styling', 'customify' ),
				'description' => __( 'Content and form buttons, including WooCommerce.', 'customify' ),
				'selector'    => array(
					'normal' => $button['normal'],
					'hover'  => $button['hover'],
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
