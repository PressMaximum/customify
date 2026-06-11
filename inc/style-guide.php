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
			/* Header tab — mirrors Astra's #astra-tour button 1:1 (only
			   the icon differs): a 45px tab pinned next to the close
			   button inside #customize-header-actions, with a 4px top
			   accent and a small tooltip below. */
			#customize-header-actions button.customify-style-guide-toggle {
				display: block;
				position: absolute;
				top: 0;
				bottom: 0;
				left: 48px;
				width: 45px;
				margin-top: 0 !important;
				padding: 0;
				background: #f0f0f1;
				border: none;
				border-radius: 0;
				border-top: 4px solid #f0f0f1;
				border-right: 1px solid #dcdcde;
				color: #3c434a;
				cursor: pointer;
			}
			#customize-header-actions button.customify-style-guide-toggle:hover,
			#customize-header-actions button.customify-style-guide-toggle:focus,
			#customize-header-actions button.customify-style-guide-toggle.is-active {
				background: #fff;
				color: #2271b1;
				border-top-color: #2271b1;
				box-shadow: none;
				outline: none;
			}
			#customize-header-actions button.customify-style-guide-toggle .dashicons {
				font-size: 18px;
				width: 18px;
				height: 18px;
				line-height: 1;
				vertical-align: middle;
			}
			.customify-sg-tooltip {
				display: none;
				position: absolute;
				left: 50%;
				transform: translateX( -50% );
				margin-bottom: 5px;
				background-color: #e5e5e5;
				color: #494948;
				border-radius: 3px;
				white-space: nowrap;
				font-size: 12px;
				z-index: 1000;
				opacity: 0;
				transition: opacity 0.3s ease;
				padding: 0 8px;
				top: 45px;
				box-shadow: rgba( 0, 0, 0, 0.02 ) 0px 1px 3px 0px, rgba( 27, 31, 35, 0.15 ) 0px 0px 0px 1px;
				line-height: 2;
			}
			#customize-header-actions button.customify-style-guide-toggle:hover .customify-sg-tooltip {
				display: block;
				opacity: 1;
			}
			@media screen and ( max-width: 640px ) {
				#customize-header-actions button.customify-style-guide-toggle {
					left: 153px;
				}
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
					'<button type="button" class="customify-style-guide-toggle button-secondary button" aria-pressed="false">' +
					'<span class="dashicons dashicons-art"></span>' +
					'<div class="customify-sg-tooltip"><?php echo esc_js( __( 'Style Guide', 'customify' ) ); ?></div>' +
					'<span class="screen-reader-text"><?php echo esc_js( __( 'Style Guide', 'customify' ) ); ?></span>' +
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

				var syncToggleState = function( url ) {
					var on = isGuideUrl( url );
					$btn.toggleClass( 'is-active', on ).attr( 'aria-pressed', on ? 'true' : 'false' );
				};
				api.previewer.previewUrl.bind( syncToggleState );
				// bind() only fires on changes — reflect the initial URL too
				// (the customizer restores the last previewed page, which
				// may already be the guide).
				syncToggleState( api.previewer.previewUrl.get() );

				// Builder item sections (title_tagline & friends) are
				// force-hidden: hide_builder_item_sections() deactivates
				// them server-side and the header builder binds a
				// _customifyForceHide handler that re-hides on every
				// active.set(true) — plain focus()/activate() silently
				// no-op. Route through the builder's own opener when it is
				// mounted; otherwise replicate its unbind → activate →
				// focus → re-hide-on-collapse cycle.
				function openHiddenSection( section ) {
					if ( typeof window.customifyBuilderOpenSection === 'function' ) {
						window.customifyBuilderOpenSection( section.id );
						return;
					}
					if ( section._customifyForceHide ) {
						section.active.unbind( section._customifyForceHide );
					}
					section.active.set( true );
					section.focus();
					var onCollapse = function( expanded ) {
						if ( ! expanded ) {
							section.expanded.unbind( onCollapse );
							section.active.set( false );
							if ( section._customifyForceHide ) {
								section.active.bind( section._customifyForceHide );
							}
						}
					};
					section.expanded.bind( onCollapse );
				}

				// Pencil buttons inside the guide ask the controls frame to
				// focus the matching control/section.
				api.previewer.bind( 'customify-style-guide-focus', function( data ) {
					if ( ! data || ! data.id ) {
						return;
					}
					var target = 'section' === data.type ? api.section( data.id ) : api.control( data.id );
					if ( ! target ) {
						return;
					}
					var section = 'section' === data.type
						? target
						: ( target.section && target.section() ? api.section( target.section() ) : null );
					if ( section && section.active && ! section.active() ) {
						openHiddenSection( section );
						if ( 'control' === data.type ) {
							// Scroll to the control once the section landed.
							setTimeout( function() {
								var c = api.control( data.id );
								if ( c ) {
									c.focus();
								}
							}, 500 );
						}
						return;
					}
					target.focus();
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
