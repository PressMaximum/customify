<?php

class Customify_Builder_Item_Nav_Icon {
	public $id = 'nav-icon';
	public $section = 'header_menu_icon';

	function item() {
		return array(
			'name'    => __( 'Menu Icon', 'customify' ),
			'id'      => $this->id,
			'width'   => '3',
			'section' => $this->section, // Customizer section to focus when click settings.
		);
	}

	function customize() {
		$section  = $this->section;
		$fn       = array( $this, 'render' );
		$selector = '.menu-mobile-toggle';
		$config   = array(
			array(
				'name'            => $section,
				'type'            => 'section',
				'panel'           => 'header_settings',
				'theme_supports'  => '',
				'title'           => __( 'Menu Icon', 'customify' ),
			),

			array(
				'name'            => 'nav_icon_text',
				'type'            => 'text',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'default'         => __( 'Menu', 'customify' ),
				'title'           => __( 'Label', 'customify' ),
			),

			array(
				'name'            => 'nav_icon_icon',
				'type'            => 'icon',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				// EMPTY IS MEANINGFUL. An unset value renders the CSS-drawn
				// hamburger this item has always drawn — bars, squeeze
				// animation, Icon Size steps and all. Only an explicit pick
				// swaps the markup for an icon, and the picker's "×" clear
				// button puts the value back to empty, i.e. back to the
				// hamburger. Registering no `default` keeps it that way for
				// every one of the existing sites (AGENTS.md §4.1).
				'presets'         => array( 'menu', 'menu-lines', 'menu-narrow', 'menu-minimal', 'menu-deep', 'menu-left', 'menu-right' ),
				'title'           => __( 'Icon', 'customify' ),
				'description'     => __( 'Leave empty to keep the default hamburger. Clearing the icon restores it.', 'customify' ),
			),

			array(
				'name'            => 'nav_icon_show_text',
				'type'            => 'checkbox',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'title'           => __( 'Label display', 'customify' ),
				'device_settings' => true,
				// Label off by default on fresh installs (>= 0.4.25); existing
				// sites that never saved this keep showing the label
				// (AGENTS.md §4.1 — defaults must not change silently).
				'default'         => ( function_exists( 'customify_is_fresh_install_since' ) && customify_is_fresh_install_since( '0.4.25' ) )
					? array(
						'desktop' => 0,
						'tablet'  => 0,
						'mobile'  => 0,
					)
					: array(
						'desktop' => 1,
						'tablet'  => 0,
						'mobile'  => 0,
					),
				'checkbox_label'  => __( 'Show Label', 'customify' ),
			),

			array(
				'name'            => 'nav_icon_size',
				'type'            => 'radio_group',
				'section'         => $section,
				'selector'        => $selector,
				'render_callback' => $fn,
				'title'           => __( 'Icon Size', 'customify' ),
				'default'         => array(
					'desktop' => 'medium',
					'tablet'  => 'medium',
					'mobile'  => 'medium',
				),
				'device_settings' => true,
				'choices'         => array(
					'small'  => __( 'Small', 'customify' ),
					'medium' => __( 'Medium', 'customify' ),
					'large'  => __( 'Large', 'customify' ),
				),
			),

			array(
				'name'       => 'nav_icon_item_color',
				'type'       => 'color',
				'section'    => $section,
				'title'      => __( 'Color', 'customify' ),
				'css_format' => 'color: {{value}};',
				'selector'   => ".header--row:not(.header--transparent) {$selector}",

			),

			array(
				'name'       => 'nav_icon_item_color_hover',
				'type'       => 'color',
				'section'    => $section,
				'css_format' => 'color: {{value}};',
				'selector'   => ".header--row:not(.header--transparent) {$selector}:hover",
				'title'      => __( 'Color Hover', 'customify' ),
			),
		);

		// Item Layout.
		return array_merge( $config, customify_header_layout_settings( $this->id, $section ) );
	}

	function render() {
		$label      = sanitize_text_field( Customify()->get_setting( 'nav_icon_text' ) );
		$show_label = Customify()->get_setting( 'nav_icon_show_text', 'all' );
		$style      = sanitize_text_field( Customify()->get_setting( 'nav_icon_style' ) );
		$sizes      = Customify()->get_setting( 'nav_icon_size', 'all' );

		$classes       = array( 'menu-mobile-toggle item-button' );
		$label_classes = array( 'nav-icon--label' );
		if ( is_array( $show_label ) ) {
			foreach ( $show_label as $d => $v ) {
				if ( $v ) { // phpcs:ignore

				} else {
					$label_classes[] = 'hide-on-' . $d;
				}
			}
		}

		if ( empty( $sizes ) ) {
			$sizes = 'is-size-' . $sizes;
		}

		if ( is_string( $sizes ) ) {
			$classes[] = $sizes;
		} else {
			foreach ( $sizes as $d => $s ) {
				if ( ! is_string( $s ) ) {
					$s = 'is-size-medium';
				}

				$classes[] = 'is-size-' . $d . '-' . $s;
			}
		}

		if ( $style ) {
			$classes[] = $style;
		}
		// Custom icon, if the user picked one. An unset value (or one whose
		// preset key no longer resolves) returns '' and falls through to the
		// CSS hamburger below — which is what every existing site gets, with
		// byte-identical markup.
		$icon      = Customify()->get_setting( 'nav_icon_icon' );
		$icon_html = customify_render_icon( $icon );

		if ( '' !== $icon_html ) {
			$classes[] = 'has-custom-icon';
		}
		if ( '' !== $icon_html ) {
			/*
			 * Both states are printed and CSS picks one, because the sidebar
			 * toggle is a pure class flip in theme.js — it adds `is-active`
			 * to `.menu-mobile-toggle` (and to `.hamburger`) and never
			 * touches markup. Rendering both keeps that contract intact: no
			 * new JS, no selector changes, and the swap is instant with
			 * nothing to fetch.
			 *
			 * The close glyph is always the library `close` preset rather
			 * than a mirror of the chosen icon's own set: Font Awesome has no
			 * version-stable "times" class (fa-times vs fa-xmark), and a
			 * pasted custom SVG has no close counterpart at all. One
			 * theme-authored glyph on the same 24-grid is the only answer
			 * that works for all three value types.
			 */
			$glyph = "\n\t\t\t" . '<span class="nav-icon--icon nav-icon--icon-open">' . $icon_html . '</span>'
				. "\n\t\t\t" . '<span class="nav-icon--icon nav-icon--icon-close" aria-hidden="true">'
				. customify_get_svg_icon( 'close' ) . '</span>';
		} else {
			/*
			 * The CSS-drawn hamburger, emitted as an explicit string so the
			 * no-icon output is byte-identical to what this item printed
			 * before the Icon field existed — indentation included. Every
			 * existing site takes this branch, and the theme's own SCSS
			 * (`.hamburger-box`, `.hamburger-inner`, the squeeze animation,
			 * the is-size-* bar dimensions) matches on exactly these nodes.
			 * Do not "tidy" the whitespace.
			 */
			$glyph = "\n\t\t\t" . '<span class="hamburger hamburger--squeeze">'
				. "\n\t\t\t\t" . '<span class="hamburger-box">'
				. "\n\t\t\t\t\t" . '<span class="hamburger-inner"></span>'
				. "\n\t\t\t\t" . '</span>'
				. "\n\t\t\t" . '</span>';
		}

		$label_html = '';
		if ( $show_label ) {
			$label_html = '<span class="' . esc_attr( join( ' ', $label_classes ) ) . '">' . $label . '</span>';
		}

		/*
		 * Leading indent only — no newline. The template this replaced had a
		 * PHP closing tag immediately before `<button`, and PHP swallows the
		 * single newline that follows a closing tag, so the first emitted
		 * bytes were the two tabs. Byte-verified against the pre-change
		 * output.
		 *
		 * (Yes, this is a block comment: a literal PHP closing tag inside a
		 * `//` comment ends PHP mode, comment or not.)
		 */
		echo "\t\t" . '<button type="button" class="' . esc_attr( join( ' ', $classes ) ) . '"  aria-label="nav icon">'
			. $glyph // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized icon markup / literal spans.
			. "\n\t\t\t" . $label_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
			. '</button>' . "\n\t\t";
	}

}

Customify_Customize_Layout_Builder()->register_item( 'header', new Customify_Builder_Item_Nav_Icon() );

