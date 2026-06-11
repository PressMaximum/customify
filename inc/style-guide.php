<?php
/**
 * Customizer Style Guide.
 *
 * A live specimen page (site identity, color slots, buttons, form
 * fields, typography) rendered INSIDE the Customizer preview iframe at
 * ?customify-style-guide=1. Because it is a real front-end page, every
 * specimen renders with the site's actual CSS — palette tokens,
 * typography variables, button/field styling, page background — and
 * postMessage live-preview updates apply to it natively. A dark site
 * (black background, white text) shows a dark style guide.
 *
 * Hovering a specimen reveals a pencil button; clicking it messages the
 * controls frame, which focuses the matching control/section. The
 * toggle button in the Customizer header swaps the preview URL between
 * the guide and the page being previewed.
 *
 * Customizer-only: the template never renders outside
 * is_customize_preview().
 */

if ( ! function_exists( 'customify_style_guide_template' ) ) {
	/**
	 * Serve the style guide template inside the Customizer preview.
	 *
	 * Late priority so page-builder template overrides (which win at
	 * ~1001) don't swallow the guide.
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	function customify_style_guide_template( $template ) {
		if ( is_customize_preview() && isset( $_GET['customify-style-guide'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return get_template_directory() . '/inc/style-guide-template.php';
		}
		return $template;
	}
}
add_filter( 'template_include', 'customify_style_guide_template', 9999 );

if ( ! function_exists( 'customify_style_guide_controls_assets' ) ) {
	/**
	 * Controls-frame side of the guide: the header toggle button and the
	 * focus bridge for the guide's pencil buttons.
	 */
	function customify_style_guide_controls_assets() {
		$guide_url = add_query_arg( 'customify-style-guide', '1', home_url( '/' ) );
		?>
		<style>
			#customize-header-actions .customify-style-guide-toggle {
				float: right;
				margin: 8px 8px 0 0;
				display: inline-flex;
				align-items: center;
				gap: 4px;
			}
			#customize-header-actions .customify-style-guide-toggle .dashicons {
				font-size: 16px;
				width: 16px;
				height: 16px;
				line-height: 1.2;
			}
			#customize-header-actions .customify-style-guide-toggle.is-active {
				background: #2c3338;
				border-color: #2c3338;
				color: #fff;
			}
		</style>
		<script>
		( function( api, $ ) {
			var guideUrl = <?php echo wp_json_encode( $guide_url ); ?>;
			var lastUrl  = null;

			function isGuideUrl( url ) {
				return !! url && url.indexOf( 'customify-style-guide=1' ) !== -1;
			}

			api.bind( 'ready', function() {
				var $btn = $(
					'<button type="button" class="button customify-style-guide-toggle" aria-pressed="false">' +
					'<span class="dashicons dashicons-art"></span>' +
					'<?php echo esc_js( __( 'Style Guide', 'customify' ) ); ?>' +
					'</button>'
				);
				$( '#customize-header-actions' ).append( $btn );

				$btn.on( 'click', function() {
					var current = api.previewer.previewUrl.get();
					if ( isGuideUrl( current ) ) {
						api.previewer.previewUrl.set( lastUrl || api.settings.url.home );
					} else {
						lastUrl = current;
						api.previewer.previewUrl.set( guideUrl );
					}
				} );

				api.previewer.previewUrl.bind( function( url ) {
					var on = isGuideUrl( url );
					$btn.toggleClass( 'is-active', on ).attr( 'aria-pressed', on ? 'true' : 'false' );
				} );

				// Pencil buttons inside the guide ask the controls frame to
				// focus the matching control/section.
				api.previewer.bind( 'customify-style-guide-focus', function( data ) {
					if ( ! data || ! data.id ) {
						return;
					}
					var target = 'section' === data.type ? api.section( data.id ) : api.control( data.id );
					if ( target ) {
						target.focus();
					}
				} );

				api.previewer.bind( 'customify-style-guide-close', function() {
					api.previewer.previewUrl.set( lastUrl || api.settings.url.home );
				} );
			} );
		} )( wp.customize, jQuery );
		</script>
		<?php
	}
}
add_action( 'customize_controls_print_footer_scripts', 'customify_style_guide_controls_assets' );
