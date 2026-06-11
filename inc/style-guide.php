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
			/* Icon tab next to the customizer close button (Astra-style):
			   same 45px square as .customize-controls-close, separated by
			   the same border. */
			#customize-controls .customify-style-guide-toggle {
				position: absolute;
				top: 0;
				left: 45px;
				width: 45px;
				height: 41px;
				padding: 0;
				margin: 0;
				border: 0;
				border-right: 1px solid #ddd;
				border-radius: 0;
				background: #fff;
				color: #50575e;
				cursor: pointer;
				z-index: 11;
			}
			#customize-controls .customify-style-guide-toggle .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
				line-height: 1;
				vertical-align: middle;
			}
			#customize-controls .customify-style-guide-toggle:hover,
			#customize-controls .customify-style-guide-toggle:focus {
				color: #2271b1;
				outline: none;
				box-shadow: none;
			}
			#customize-controls .customify-style-guide-toggle.is-active {
				color: #2271b1;
				box-shadow: inset 0 -3px 0 0 #2271b1;
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
					'<button type="button" class="customify-style-guide-toggle" aria-pressed="false" title="<?php echo esc_attr( __( 'Style Guide', 'customify' ) ); ?>">' +
					'<span class="dashicons dashicons-art"></span>' +
					'<span class="screen-reader-text"><?php echo esc_js( __( 'Style Guide', 'customify' ) ); ?></span>' +
					'</button>'
				);
				$( '#customize-controls' ).append( $btn );

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
