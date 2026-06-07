<?php
if ( ! function_exists( 'customify_customizer_layouts_config' ) ) {
	/**
	 * Add layout settings.
	 *
	 * @since 0.0.1
	 * @since 0.2.6
	 *
	 * @param array $configs
	 * @return array
	 */
	function customify_customizer_layouts_config( $configs ) {
		$config = array(

			// Layout panel.
			array(
				'name'           => 'layout_panel',
				'type'           => 'panel',
				'priority'       => 18,
				'theme_supports' => '',
				'title'          => __( 'Layouts', 'customify' ),
			),

			// Global layout section.
			array(
				'name'           => 'global_layout_section',
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Global', 'customify' ),
			),
			array(
				'name'        => 'site_layout',
				'type'        => 'radio_group',
				'section'     => 'global_layout_section',
				'title'       => __( 'Site layout', 'customify' ),
				'description' => __( 'Select global site layout.', 'customify' ),
				'default'     => 'site-full-width',
				'css_format'  => 'html_class',
				'selector'    => 'body',
				'choices'     => array(
					'site-full-width' => __( 'Full Width', 'customify' ),
					'site-boxed'      => __( 'Boxed', 'customify' ),
					'site-framed'     => __( 'Framed', 'customify' ),
				),
			),

			array(
				'name'       => 'site_box_shadow',
				'type'       => 'radio_group',
				'section'    => 'global_layout_section',
				'title'      => __( 'Site boxed/framed shadow', 'customify' ),
				'choices'    => array(
					'box-shadow'    => __( 'Yes', 'customify' ),
					'no-box-shadow' => __( 'No', 'customify' ),
				),
				'default'    => 'box-shadow',
				'css_format' => 'html_class',
				'selector'   => '#page',
				'required'   => array(
					array( 'site_layout', '=', array( 'site-boxed', 'site-framed' ) ),
				),
			),

			array(
				'name'            => 'site_margin',
				'type'            => 'css_ruler',
				'section'         => 'global_layout_section',
				'title'           => __( 'Site framed margin', 'customify' ),
				'device_settings' => true,
				'fields_disabled' => array(
					'left'  => '',
					'right' => '',
				),
				'css_format'      => array(
					'top'    => 'margin-top: {{value}};',
					'bottom' => 'margin-bottom: {{value}};',
				),
				'selector'        => '.site-framed .site',
				'required'        => array(
					array( 'site_layout', '=', 'site-framed' ),
				),
			),
			array(
				'name'    => 'global_layout_h_width',
				'type'    => 'heading',
				'section' => 'global_layout_section',
				'title'   => __( 'Width', 'customify' ),
			),

			/**
			 * @since 0.2.6 Change css_format and selector.
			 *
			 * Default 1248 matches the SCSS hardcode in
			 * src/frontend/scss/layouts/_layouts.scss `.customify-container, .layout-contained { max-width: 1248px }`.
			 * Auto-CSS skips emission when the saved value equals the field default,
			 * so when the Customizer is unsaved the SCSS hardcode is what actually
			 * renders. Aligning the default avoids a visual mismatch between the
			 * Customizer slider position and the rendered container outer width.
			 * Saved-explicit values are unaffected — they still persist as-is.
			 */
			array(
				'name'            => 'container_width',
				'type'            => 'slider',
				'device_settings' => false,
				'default'         => 1248,
				'min'             => 700,
				'step'            => 10,
				'max'             => 2000,
				'section'         => 'global_layout_section',
				'title'           => __( 'Container Width', 'customify' ),
				'description'     => __( 'Max width of the site container.', 'customify' ),
				'selector'        => 'format',
				'css_format'      => ':root { --wp--style--global--wide-size: {{value}}; } .customify-container, .layout-contained, .site-framed .site, .site-boxed .site { max-width: {{value}}; }',
			),

			// Narrow content width — paired with the "Narrow" Content Layout
			// option (per-post metabox). Mirrors the Full Width / Full Width –
			// Stretched pattern: a content_layout value that overrides sidebar
			// layout to no-sidebar and constrains content-size to this value.
			//
			// The `.site-content.content-narrow` rule below is the SINGLE source of
			// truth for this layout's CSS — emitted on the frontend by the field's
			// PHP `auto_css()` output and live-rebuilt in Customizer preview by
			// `src/backend/customizer/js/auto-css.js`. Keeping the rule inside the
			// per-field `css_format` (not the static `customify_layout_content_size_css()`
			// block) is deliberate: the preview JS overwrites the per-field stylesheet
			// on every drag, so a static duplicate would either (a) win source order
			// and freeze the preview at the saved value, or (b) lose source order
			// and force callers to ensure they enqueue customify-layout-style after
			// customify-style. Owning the rule here removes both traps.
			//
			// Wide-size for narrow = narrow_width + 400 (200px breakout each side)
			// per the dynamic wide-size design. `calc({{value}} + 400px)` lets the
			// auto-CSS rebuild keep wide-size in sync without a separate handler.
			array(
				'name'            => 'narrow_width',
				'type'            => 'slider',
				'device_settings' => false,
				'default'         => 800,
				'min'             => 400,
				'step'            => 10,
				'max'             => 1000,
				'section'         => 'global_layout_section',
				'title'           => __( 'Narrow Content Width', 'customify' ),
				'description'     => __( 'Max width when a post uses the "Narrow" Content Layout option in the Page Settings panel.', 'customify' ),
				'selector'        => 'format',
				'css_format'      => '.site-content.content-narrow { --wp--style--global--content-size: {{value}}; --wp--style--global--wide-size: calc({{value}} + 400px); }',
			),

			array(
				'name'    => 'global_layout_h_content',
				'type'    => 'heading',
				'section' => 'global_layout_section',
				'title'   => __( 'Content Area', 'customify' ),
			),

			// Site content layout.
			array(
				'name'       => 'site_content_layout',
				'type'       => 'radio_group',
				'section'    => 'global_layout_section',
				'title'      => __( 'Site content layout', 'customify' ),
				'choices'    => array(
					'site-content-fullwidth' => __( 'Full width', 'customify' ),
					'site-content-boxed'     => __( 'Boxed', 'customify' ),
				),
				'default'    => 'site-content-boxed',
				'css_format' => 'html_class',
				'selector'   => '.site-content',
			),

			array(
				'name'            => 'site_content_padding',
				'type'            => 'css_ruler',
				'section'         => 'global_layout_section',
				'title'           => __( 'Site content padding', 'customify' ),
				'device_settings' => true,
				'fields_disabled' => array(
					'left'  => '',
					'right' => '',
				),
				'css_format'      => array(
					'top'    => 'padding-top: {{value}};',
					'bottom' => 'padding-bottom: {{value}};',
				),
				'selector'        => '#sidebar-secondary, #sidebar-primary, #main',
			),

			// Page layout.
			array(
				'name'           => 'sidebar_layout_section',
				'type'           => 'section',
				'panel'          => 'layout_panel',
				'theme_supports' => '',
				'title'          => __( 'Sidebars', 'customify' ),
			),

			array(
				'name'    => 'sidebar_layout_h_general',
				'type'    => 'heading',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'General', 'customify' ),
			),

			// Global sidebar layout. Default changed from `content-sidebar`
			// (content + right sidebar) to `content` (no sidebar) — clean
			// out-of-the-box look that matches the "pick a few brand colors
			// and start designing" onboarding flow. Sites that explicitly
			// saved a layout in the Customizer still render with their
			// saved value via the override path.
			array(
				'name'    => 'sidebar_layout',
				'type'    => 'select',
				'default' => 'content',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Default Sidebar Layout', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Sidebar vertical border.
			array(
				'name'       => 'sidebar_vertical_border',
				'type'       => 'radio_group',
				'section'    => 'sidebar_layout_section',
				'title'      => __( 'Sidebar with vertical border', 'customify' ),
				'choices'    => array(
					'sidebar_vertical_border'    => __( 'Yes', 'customify' ),
					'no-sidebar_vertical_border' => __( 'No', 'customify' ),
				),
				'default'    => 'sidebar_vertical_border',
				'css_format' => 'html_class',
				'selector'   => 'body',
			),

			array(
				'name'    => 'sidebar_layout_h_pages',
				'type'    => 'heading',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Page Types', 'customify' ),
			),

			// Page sidebar layout. Default changed from `content-sidebar` to
			// `content` so static pages render full-width by default —
			// matches the global sidebar_layout above.
			array(
				'name'    => 'page_sidebar_layout',
				'type'    => 'select',
				'default' => 'content',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Pages', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Blog Posts sidebar layout.
			array(
				'name'    => 'posts_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Blog posts', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Blog Posts sidebar layout.
			array(
				'name'    => 'posts_archives_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Blog Archive Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// Search.
			array(
				'name'    => 'search_sidebar_layout',
				'type'    => 'select',
				'default' => 'content-sidebar',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Search Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
			// 404.
			array(
				'name'    => '404_sidebar_layout',
				'type'    => 'select',
				'default' => 'content',
				'section' => 'sidebar_layout_section',
				'title'   => __( '404 Page', 'customify' ),
				'choices' => customify_get_config_sidebar_layouts(),
			),
		);

		$post_types = customify_get_content_post_types();

		if ( count( $post_types ) ) {
			$config[] = array(
				'name'    => 'post_types_sidebar_h_tb',
				'type'    => 'heading',
				'section' => 'sidebar_layout_section',
				'title'   => __( 'Post Type Settings', 'customify' ),
			);

			foreach ( $post_types as $pt => $label ) {
				$config[] = array(
					'name'    => "{$pt}_sidebar_layout",
					'type'    => 'select',
					'default' => 'content',
					'section' => 'sidebar_layout_section',
					'title'   => sprintf( __( 'Single %s', 'customify' ), $label['singular_name'] ),
					'choices' => array_merge(
						array( 'default' => __( 'Default', 'customify' ) ),
						customify_get_config_sidebar_layouts()
					),
				);

				// Per-CPT archive sidebar layout. Only registered for post types
				// with a real archive ( has_archive ). WooCommerce owns the
				// `product` shop/archive sidebar via its own customify_get_layout
				// filter, so a control here would be dead — skip it. Default
				// 'content' (no sidebar) mirrors the per-CPT single default
				// ( {$pt}_sidebar_layout ) for a clean out-of-the-box look; sites
				// that explicitly saved a value keep it. Choosing the "Default"
				// option (value 'default') inherits the global "Blog Archive Page"
				// layout via the resolver in customify_get_layout().
				$pt_object = get_post_type_object( $pt );
				if ( 'product' !== $pt && $pt_object && $pt_object->has_archive ) {
					$config[] = array(
						'name'    => "{$pt}_archive_sidebar_layout",
						'type'    => 'select',
						'default' => 'content',
						'section' => 'sidebar_layout_section',
						'title'   => sprintf( __( '%s Archive', 'customify' ), $label['singular_name'] ),
						'choices' => array_merge(
							array( 'default' => __( 'Default', 'customify' ) ),
							customify_get_config_sidebar_layouts()
						),
					);
				}
			}
		}

		return array_merge( $configs, $config );
	}
}

add_filter( 'customify/customizer/config', 'customify_customizer_layouts_config' );
