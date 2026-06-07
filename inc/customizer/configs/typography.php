<?php
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

			// ───────── Base ─────────
			array(
				'name'    => "{$section}_h_base",
				'type'    => 'heading',
				'section' => 'typography_panel',
				'title'   => __( 'Base', 'customify' ),
			),

			array(
				'name'        => "{$section}_base_p",
				'type'        => 'typography',
				'section'     => 'typography_panel',
				'title'       => __( 'Body & Paragraph', 'customify' ),
				'description' => __( 'Apply to body and paragraph text.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => 'body',
			),

			array(
				'name'        => "{$section}_base_heading",
				'type'        => 'typography',
				'section'     => 'typography_panel',
				'title'       => __( 'Heading', 'customify' ),
				'description' => __( 'Apply to all heading elements.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6',
				// Only family + weight are consumed by SCSS (`_base.scss` shared
				// heading rule). Size / line-height / letter-spacing / style /
				// decoration / transform fall to per-level h1–h6 below if needed,
				// or to the browser default — UI hides them to avoid silent no-ops.
				'fields'      => array(
					'font_size'       => false,
					'line_height'     => false,
					'letter_spacing'  => false,
					'style'           => false,
					'text_decoration' => false,
					'text_transform'  => false,
				),
			),
			array(
				'name'        => "{$section}_base_widget_title",
				'type'        => 'typography',
				'section'     => 'typography_panel',
				'title'       => __( 'Widget Title', 'customify' ),
				'description' => __( 'Apply to all widget title in site content.', 'customify' ),
				'css_format'  => 'typography',
				'selector'    => '.site-content .widget-title',
			),

			// ───────── Site Title & Tagline ─────────
			array(
				'name'    => "{$section}_h_site_tt",
				'type'    => 'heading',
				'section' => 'typography_panel',
				'title'   => __( 'Site Title & Tagline', 'customify' ),
			),

			array(
				'name'       => "{$section}_site_tt_title",
				'type'       => 'typography',
				'section'    => 'typography_panel',
				'title'      => __( 'Site Title', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.site-branding .site-title, .site-branding .site-title a',
			),

			array(
				'name'       => "{$section}_site_tt_desc",
				'type'       => 'typography',
				'section'    => 'typography_panel',
				'title'      => __( 'Tagline', 'customify' ),
				'css_format' => 'typography',
				'selector'   => '.site-branding .site-description',
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

		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $tag ) {
			$config[] = array(
				'name'       => "{$section}_heading_{$tag}",
				'type'       => 'typography',
				'section'    => 'typography_panel',
				/* translators: %s: heading tag name (H1, H2, etc.). */
				'title'      => sprintf( __( 'Heading %s', 'customify' ), strtoupper( $tag ) ),
				'css_format' => 'typography',
				'selector'   => ".entry-content {$tag}, .wp-block {$tag}" . ( 'h1' === $tag ? ', .entry-single .entry-title' : '' ),
				'fields'     => $h_fields,
			);
		}

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_typography_config' );
