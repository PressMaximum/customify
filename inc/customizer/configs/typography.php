<?php
if ( ! function_exists( 'customify_typography_presets' ) ) {
	/**
	 * Font-pair presets for the Typography Presets control.
	 *
	 * Clicking a preset patches ONLY the family bits (font / font_type /
	 * variant) of `global_typography_base_p` (body) and
	 * `global_typography_base_heading` (headings) — see
	 * typography-presets.js. Sizes, weights and every other sub-value the
	 * user tuned stay untouched, and this control stores no value of its
	 * own.
	 *
	 * Pairs stick to the most widely used, style-neutral Google Fonts
	 * (no condensed/display faces) so they work as safe starting points.
	 * `variants` lists the variants written into the setting, trimmed to
	 * regular/italic/600/700 INTERSECTED with what each family actually
	 * ships per src/fonts/google-fonts.json — keep both in lockstep when
	 * changing a pair (css2 rejects requests for missing variants).
	 *
	 * @return array
	 */
	function customify_typography_presets() {
		return array(
			array(
				'name'    => 'Montserrat / Open Sans',
				'heading' => array(
					'family'   => 'Montserrat',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
				'body'    => array(
					'family'   => 'Open Sans',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
			),
			array(
				'name'    => 'Poppins / Inter',
				'heading' => array(
					'family'   => 'Poppins',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
				'body'    => array(
					'family'   => 'Inter',
					'fallback' => 'sans-serif',
					// Inter ships no italics in the catalogue.
					'variants' => array( 'regular', '600', '700' ),
				),
			),
			array(
				'name'    => 'Raleway / Roboto',
				'heading' => array(
					'family'   => 'Raleway',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
				'body'    => array(
					'family'   => 'Roboto',
					'fallback' => 'sans-serif',
					// Roboto ships no 600.
					'variants' => array( 'regular', 'italic', '700' ),
				),
			),
			array(
				'name'    => 'Lato / Merriweather',
				'heading' => array(
					'family'   => 'Lato',
					'fallback' => 'sans-serif',
					// Lato ships no 600.
					'variants' => array( 'regular', 'italic', '700' ),
				),
				'body'    => array(
					'family'   => 'Merriweather',
					'fallback' => 'serif',
					// Merriweather ships no 600.
					'variants' => array( 'regular', 'italic', '700' ),
				),
			),
			array(
				'name'    => 'Playfair Display / Source Sans Pro',
				'heading' => array(
					'family'   => 'Playfair Display',
					'fallback' => 'serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
				'body'    => array(
					'family'   => 'Source Sans Pro',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
			),
			array(
				'name'    => 'Lora / Open Sans',
				'heading' => array(
					'family'   => 'Lora',
					'fallback' => 'serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
				'body'    => array(
					'family'   => 'Open Sans',
					'fallback' => 'sans-serif',
					'variants' => array( 'regular', 'italic', '600', '700' ),
				),
			),
		);
	}
}

if ( ! function_exists( 'customify_typography_preset_fonts' ) ) {
	/**
	 * Load the preset families inside the Customizer CONTROLS frame so the
	 * preset cards render with their real fonts. css2 `text=` subsets the
	 * files to the card glyphs ("Aa" / "Font Family"), so the whole grid
	 * costs a few KB. Frontend/preview loading is untouched — applying a
	 * preset goes through the normal typography pipeline.
	 */
	function customify_typography_preset_fonts() {
		$families = array();
		foreach ( customify_typography_presets() as $preset ) {
			$families[ $preset['heading']['family'] ] = true;
			$families[ $preset['body']['family'] ]    = true;
		}
		$query = array();
		foreach ( array_keys( $families ) as $family ) {
			$query[] = 'family=' . str_replace( ' ', '+', $family );
		}
		$url = 'https://fonts.googleapis.com/css2?' . implode( '&', $query )
			. '&display=swap&text=' . rawurlencode( 'AaFont mily' );
		wp_enqueue_style( 'customify-typo-preset-fonts', $url, array(), null );
	}
}
add_action( 'customize_controls_enqueue_scripts', 'customify_typography_preset_fonts' );

if ( ! function_exists( 'customify_customizer_typography_config' ) ) {
	/**
	 * Add typograhy settings.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * `typography_panel` is a top-level SECTION (formerly a panel) — like the
	 * Colors section, clicking "Typography" drops straight into the controls
	 * instead of an intermediate list. The former Base / Site Title & Tagline /
	 * Content sub-sections are now `heading` separators inside this one section.
	 *
	 * The id stays `typography_panel` (the General Options panel group positions it
	 * via the get_section() fallback, and the Dashboard deep-links target it).
	 * Control names (= theme_mod keys) are unchanged, so saved values on existing
	 * sites are unaffected.
	 *
	 * @param array $configs
	 * @return array
	 */
	function customify_customizer_typography_config( $configs ) {

		$section = 'global_typography';

		$config = array(
			array(
				'name'     => 'typography_panel',
				'type'     => 'section',
				'priority' => 22,
				'title'    => __( 'Typography', 'customify' ),
			),

			// ───────── Presets ─────────
			// Quick-start font pairs. The control is chrome-only: clicking a
			// card patches the family bits of the Body/Heading settings (see
			// typography-presets.js); its own setting is never written.
			array(
				'name'    => 'typography_presets',
				'type'    => 'typography_presets',
				'section' => 'typography_panel',
				'title'   => __( 'Presets', 'customify' ),
				'fields'  => customify_typography_presets(),
			),

			// ───────── Base ─────────
			array(
				'name'    => "{$section}_h_base",
				'type'    => 'heading',
				'section' => 'typography_panel',
				'title'   => __( 'Base', 'customify' ),
			),

			// `display_defaults` below (here and on every typography field) is
			// DISPLAY-ONLY chrome metadata: it fills the trigger preview and
			// the empty popover inputs' placeholders so users can see the
			// effective starting point. The strings mirror the literal CSS
			// fallbacks compiled from `src/frontend/scss/base/_base.scss`
			// (`var(--customify-typo-…, <fallback>)`) — keep them in lockstep
			// when the SCSS type scale changes. They are never written to the
			// setting and never reach the CSS generator.
			array(
				'name'             => "{$section}_base_p",
				'type'             => 'typography',
				'section'          => 'typography_panel',
				'title'            => __( 'Body & Paragraph', 'customify' ),
				'description'      => __( 'Apply to body and paragraph text.', 'customify' ),
				'css_format'       => 'typography',
				'selector'         => 'body',
				'display_defaults' => array(
					// The CSS fallback is literally `inherit`; display the
					// effective browser default (16px) instead so the
					// control offers a concrete starting point like every
					// other typography field.
					'font_size'   => '16px',
					'line_height' => '1.618',
					'font_weight' => '400',
				),
			),

			array(
				'name'             => "{$section}_base_heading",
				'type'             => 'typography',
				'section'          => 'typography_panel',
				'title'            => __( 'Heading', 'customify' ),
				'description'      => __( 'Apply to all heading elements.', 'customify' ),
				'css_format'       => 'typography',
				'selector'         => 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6',
				// Only family + weight are consumed by SCSS (`_base.scss` shared
				// heading rule). Size / line-height / letter-spacing / style /
				// decoration / transform fall to per-level h1–h6 below if needed,
				// or to the browser default — UI hides them to avoid silent no-ops.
				'fields'           => array(
					'font_size'       => false,
					'line_height'     => false,
					'letter_spacing'  => false,
					'style'           => false,
					'text_decoration' => false,
					'text_transform'  => false,
				),
				'display_defaults' => array(
					'font_weight' => '400',
				),
			),
			// ───────── Site Title & Tagline ─────────
			array(
				'name'    => "{$section}_h_site_tt",
				'type'    => 'heading',
				'section' => 'typography_panel',
				'title'   => __( 'Site Title & Tagline', 'customify' ),
			),

			array(
				'name'             => "{$section}_site_tt_title",
				'type'             => 'typography',
				'section'          => 'typography_panel',
				'title'            => __( 'Site Title', 'customify' ),
				'css_format'       => 'typography',
				'selector'         => '.site-branding .site-title, .site-branding .site-title a',
				// Mirrors header/builder_items/_logo_site_identity.scss
				// `.site-title` literals.
				'display_defaults' => array(
					'font_size'   => '1.5em',
					'font_weight' => '600',
					'line_height' => '1.216',
				),
			),

			array(
				'name'             => "{$section}_site_tt_desc",
				'type'             => 'typography',
				'section'          => 'typography_panel',
				'title'            => __( 'Tagline', 'customify' ),
				'css_format'       => 'typography',
				'selector'         => '.site-branding .site-description',
				// `.site-description` carries no typography literals — it
				// inherits the body font; display the effective defaults.
				'display_defaults' => array(
					'font_size'   => '16px',
					'font_weight' => '400',
				),
			),

			// ───────── Content ─────────
			array(
				'name'    => "{$section}_h_content",
				'type'    => 'heading',
				'section' => 'typography_panel',
				'title'   => __( 'Content', 'customify' ),
			),

		);

		// Per-level h1–h6 expose only font-size + line-height. Family /
		// weight / style / decoration / transform are owned by the
		// `base_heading` shared rule (see above) — letting the user
		// re-pick them per level would silently no-op because `_base.scss`
		// doesn't carry per-level consumers for those properties.
		$h_fields = array(
			'font'            => false,
			'font_weight'     => false,
			'letter_spacing'  => false,
			'style'           => false,
			'text_decoration' => false,
			'text_transform'  => false,
		);

		// Display-only defaults per level — must match the compiled `_base.scss`
		// fallbacks (h1/h2 carry per-device variants). Keep in lockstep with the
		// SCSS. The scale was trimmed/evened (h1 2.42→2.2, h2 2.1→1.8, h3/h4 off
		// the modular scale onto explicit ems, h6 0.95→1.0) for a lighter,
		// smoother heading rhythm — see _base.scss.
		$h_display = array(
			'h1' => array(
				'font_size'   => array(
					'desktop' => '2.2em',
					'tablet'  => '1.9em',
					'mobile'  => '1.65em',
				),
				'line_height' => '1.216',
			),
			'h2' => array(
				'font_size'   => array(
					'desktop' => '1.8em',
					'tablet'  => '1.6em',
					'mobile'  => '1.5em',
				),
				'line_height' => '1.216',
			),
			'h3' => array(
				'font_size'   => '1.55em',
				'line_height' => '1.3',
			),
			'h4' => array(
				'font_size'   => '1.35em',
				'line_height' => '1.3',
			),
			'h5' => array(
				'font_size'   => '1.15em',
				'line_height' => '1.3',
			),
			'h6' => array(
				'font_size'   => '1.0em',
				'line_height' => '1.3',
			),
		);

		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) {
			$config[] = array(
				'name'             => "{$section}_heading_{$tag}",
				'type'             => 'typography',
				'section'          => 'typography_panel',
				/* translators: %s: heading tag name (H1, H2, etc.). */
				'title'            => sprintf( __( 'Heading %s', 'customify' ), strtoupper( $tag ) ),
				'css_format'       => 'typography',
				'selector'         => ".entry-content {$tag}, .wp-block {$tag}" . ( 'h1' === $tag ? ', .entry-single .entry-title' : '' ),
				'fields'           => $h_fields,
				'display_defaults' => $h_display[ $tag ],
			);
		}

		// Widget Title closes the Content group (kept after the h1–h6
		// scale). Setting name unchanged — display order only.
		$config[] = array(
			'name'             => "{$section}_base_widget_title",
			'type'             => 'typography',
			'section'          => 'typography_panel',
			'title'            => __( 'Widget Title', 'customify' ),
			'description'      => __( 'Apply to all widget title in site content.', 'customify' ),
			'css_format'       => 'typography',
			'selector'         => '.site-content .widget-title',
			// Mirrors widgets/_widgets.scss `.widget-title` literals.
			'display_defaults' => array(
				'font_size'   => '16px',
				'font_weight' => '500',
			),
		);

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_typography_config' );
