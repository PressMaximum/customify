<?php
/**
 * Context-aware content area vertical spacing.
 *
 * @package customify
 */

if ( ! function_exists( 'customify_get_content_area_spacing_choices' ) ) {
	/**
	 * Get the available content area spacing modes.
	 *
	 * @return array<string, string>
	 */
	function customify_get_content_area_spacing_choices() {
		return array(
			'inherit'  => __( 'Inherit', 'customify' ),
			'both'     => __( 'Top & Bottom', 'customify' ),
			'top'      => __( 'Top Only', 'customify' ),
			'bottom'   => __( 'Bottom Only', 'customify' ),
			'disabled' => __( 'Disabled', 'customify' ),
		);
	}
}

if ( ! function_exists( 'customify_get_content_area_spacing_archive_post_types' ) ) {
	/**
	 * Get content post types whose archives are owned by Customify.
	 *
	 * WooCommerce owns the Product archive through its existing shop-page
	 * integration, so it intentionally continues to inherit the general archive
	 * setting instead of receiving a dead per-CPT control.
	 *
	 * @return array<string, array{name:string, singular_name:string}>
	 */
	function customify_get_content_area_spacing_archive_post_types() {
		$post_types = customify_get_content_post_types();

		foreach ( array_keys( $post_types ) as $post_type ) {
			$object = get_post_type_object( $post_type );
			if ( 'product' === $post_type || ! $object || ! $object->has_archive ) {
				unset( $post_types[ $post_type ] );
			}
		}

		/**
		 * Filter content post types that receive an archive spacing setting.
		 *
		 * @param array $post_types Map of post type slug to labels array.
		 */
		return apply_filters( 'customify/content_area_spacing/archive_post_types', $post_types );
	}
}

if ( ! function_exists( 'customify_get_content_area_spacing_setting_name' ) ) {
	/**
	 * Map a request context to its Customizer setting name.
	 *
	 * @param array $context Content area spacing context.
	 * @return string
	 */
	function customify_get_content_area_spacing_setting_name( $context ) {
		$key       = isset( $context['key'] ) ? (string) $context['key'] : '';
		$post_type = isset( $context['post_type'] ) && is_scalar( $context['post_type'] )
			? sanitize_key( (string) $context['post_type'] )
			: '';

		switch ( $key ) {
			case 'page':
				return 'page_content_area_spacing';
			case 'post':
				return 'posts_content_area_spacing';
			case 'blog_archive':
				return 'posts_archives_content_area_spacing';
			case 'search':
				return 'search_content_area_spacing';
			case '404':
				return '404_content_area_spacing';
			case 'post_type_single':
				return $post_type ? $post_type . '_content_area_spacing' : '';
			case 'post_type_archive':
				return $post_type ? $post_type . '_archive_content_area_spacing' : '';
			default:
				return '';
		}
	}
}

if ( ! function_exists( 'customify_get_content_area_spacing_context' ) ) {
	/**
	 * Resolve the current request to a content area spacing context.
	 *
	 * @return array{key:string,post_type:string,post_id:int,singular:bool,setting:string}
	 */
	function customify_get_content_area_spacing_context() {
		$context = array(
			'key'       => 'none',
			'post_type' => '',
			'post_id'   => 0,
			'singular'  => false,
			'setting'   => '',
		);

		if ( is_search() ) {
			$context['key'] = 'search';
		} elseif ( is_404() ) {
			$context['key'] = '404';
		} elseif ( is_home() ) {
			$context['key'] = 'blog_archive';
		} elseif ( is_page() ) {
			$context['key']       = 'page';
			$context['post_type'] = 'page';
			$context['post_id']   = (int) get_the_ID();
			$context['singular']  = true;
		} elseif ( is_singular( 'post' ) ) {
			$context['key']       = 'post';
			$context['post_type'] = 'post';
			$context['post_id']   = (int) get_the_ID();
			$context['singular']  = true;
		} elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = 1 === count( $post_type ) ? reset( $post_type ) : '';
			}
			if ( ! $post_type ) {
				$queried   = get_queried_object();
				$post_type = ( $queried instanceof WP_Post_Type ) ? $queried->name : '';
			}

			$archive_post_types = customify_get_content_area_spacing_archive_post_types();
			if ( $post_type && isset( $archive_post_types[ $post_type ] ) ) {
				$context['key']       = 'post_type_archive';
				$context['post_type'] = $post_type;
			} else {
				$context['key'] = 'blog_archive';
			}
		} elseif ( is_tax() ) {
			$queried       = get_queried_object();
			$taxonomy      = ( $queried instanceof WP_Term ) ? $queried->taxonomy : '';
			$post_type     = $taxonomy ? customify_get_taxonomy_archive_post_type( $taxonomy ) : '';
			$archive_types = customify_get_content_area_spacing_archive_post_types();

			if ( $post_type && isset( $archive_types[ $post_type ] ) ) {
				$context['key']       = 'post_type_archive';
				$context['post_type'] = $post_type;
			} else {
				$context['key'] = 'blog_archive';
			}
		} elseif ( is_category() || is_tag() || is_archive() ) {
			$context['key'] = 'blog_archive';
		} elseif ( is_singular() ) {
			$post_type     = get_post_type();
			$content_types = customify_get_content_post_types();

			$context['post_type'] = $post_type;
			$context['post_id']   = (int) get_the_ID();
			$context['singular']  = true;

			if ( $post_type && isset( $content_types[ $post_type ] ) ) {
				$context['key'] = 'post_type_single';
			}
		}

		/**
		 * Filter the content area spacing request context.
		 *
		 * Plugins with unusual front-end routes can supply a core context key and
		 * post type, or provide a custom setting name directly.
		 *
		 * @param array $context Request context data.
		 */
		$context = apply_filters( 'customify/content_area_spacing/context', $context );
		$context = is_array( $context ) ? $context : array();

		$context = wp_parse_args(
			$context,
			array(
				'key'       => 'none',
				'post_type' => '',
				'post_id'   => 0,
				'singular'  => false,
				'setting'   => '',
			)
		);

		$context['key']       = is_scalar( $context['key'] ) ? sanitize_key( (string) $context['key'] ) : 'none';
		$context['post_type'] = is_scalar( $context['post_type'] ) ? sanitize_key( (string) $context['post_type'] ) : '';
		$context['post_id']   = is_scalar( $context['post_id'] ) ? absint( $context['post_id'] ) : 0;
		$context['singular']  = (bool) $context['singular'];
		$context['setting']   = is_scalar( $context['setting'] ) ? sanitize_key( (string) $context['setting'] ) : '';

		if ( ! $context['setting'] ) {
			$context['setting'] = customify_get_content_area_spacing_setting_name( $context );
		}

		return $context;
	}
}

if ( ! function_exists( 'customify_sanitize_content_area_spacing_mode' ) ) {
	/**
	 * Sanitize a content area spacing mode.
	 *
	 * @param mixed $mode Candidate mode.
	 * @return string
	 */
	function customify_sanitize_content_area_spacing_mode( $mode ) {
		$mode    = is_string( $mode ) ? $mode : '';
		$allowed = array( 'inherit', 'both', 'top', 'bottom', 'disabled' );

		return in_array( $mode, $allowed, true ) ? $mode : 'inherit';
	}
}

if ( ! function_exists( 'customify_content_area_spacing_has_legacy_disable' ) ) {
	/**
	 * Check the existing per-post disable flag for a singular context.
	 *
	 * @param array $context Content area spacing context.
	 * @return bool
	 */
	function customify_content_area_spacing_has_legacy_disable( $context ) {
		if ( empty( $context['singular'] ) || empty( $context['post_id'] ) ) {
			return false;
		}

		$value = get_post_meta(
			(int) $context['post_id'],
			'_customify_disable_content_vertical_padding',
			true
		);
		return '1' === (string) $value;
	}
}

if ( ! function_exists( 'customify_resolve_content_area_spacing_mode' ) ) {
	/**
	 * Resolve the current content area spacing mode.
	 *
	 * The legacy singular post meta is an absolute override. All new settings
	 * default to inherit, which preserves the existing global padding output.
	 *
	 * @param array|null $context Optional explicit context, primarily for integrations.
	 * @return string
	 */
	function customify_resolve_content_area_spacing_mode( $context = null ) {
		$context = is_array( $context ) ? $context : customify_get_content_area_spacing_context();

		if ( customify_content_area_spacing_has_legacy_disable( $context ) ) {
			return 'disabled';
		}

		$setting = isset( $context['setting'] ) && is_scalar( $context['setting'] )
			? sanitize_key( (string) $context['setting'] )
			: '';
		if ( ! $setting ) {
			$setting = customify_get_content_area_spacing_setting_name( $context );
		}

		$mode = $setting ? Customify()->get_setting( $setting ) : 'inherit';
		$mode = customify_sanitize_content_area_spacing_mode( $mode );

		/**
		 * Filter the resolved content area spacing mode.
		 *
		 * The legacy per-post disable flag returns before this filter by design.
		 *
		 * @param string $mode    Resolved mode.
		 * @param array  $context Request context data.
		 * @param string $setting Customizer setting name, or an empty string.
		 */
		$mode = apply_filters( 'customify/content_area_spacing/mode', $mode, $context, $setting );

		return customify_sanitize_content_area_spacing_mode( $mode );
	}
}

if ( ! function_exists( 'customify_get_content_area_spacing_components' ) ) {
	/**
	 * Resolve whether the top and bottom spacing components are enabled.
	 *
	 * @param array|null $context Optional explicit context.
	 * @return array{top:bool,bottom:bool}
	 */
	function customify_get_content_area_spacing_components( $context = null ) {
		$context = is_array( $context ) ? $context : customify_get_content_area_spacing_context();
		$legacy  = customify_content_area_spacing_has_legacy_disable( $context );
		$mode    = $legacy ? 'disabled' : customify_resolve_content_area_spacing_mode( $context );

		$components = array(
			'top'    => in_array( $mode, array( 'inherit', 'both', 'top' ), true ),
			'bottom' => in_array( $mode, array( 'inherit', 'both', 'bottom' ), true ),
		);

		if ( ! $legacy ) {
			/**
			 * Filter the enabled content area spacing components.
			 *
			 * @param array  $components Enabled top and bottom components.
			 * @param string $mode       Resolved mode.
			 * @param array  $context    Request context data.
			 */
			$components = apply_filters( 'customify/content_area_spacing/components', $components, $mode, $context );
		}
		$components = is_array( $components ) ? $components : array();

		return array(
			'top'    => ! empty( $components['top'] ),
			'bottom' => ! empty( $components['bottom'] ),
		);
	}
}

if ( ! function_exists( 'customify_get_content_area_spacing_body_classes' ) ) {
	/**
	 * Get body classes required by the resolved spacing components.
	 *
	 * @param array|null $context Optional explicit context.
	 * @return string[]
	 */
	function customify_get_content_area_spacing_body_classes( $context = null ) {
		$context    = is_array( $context ) ? $context : customify_get_content_area_spacing_context();
		$components = customify_get_content_area_spacing_components( $context );
		$classes    = array();

		if ( ! $components['top'] && ! $components['bottom'] ) {
			$classes[] = 'disable-content-vertical-padding';
		} elseif ( $components['top'] && ! $components['bottom'] ) {
			$classes[] = 'content-area-spacing-top-only';
		} elseif ( ! $components['top'] && $components['bottom'] ) {
			$classes[] = 'content-area-spacing-bottom-only';
		}

		/**
		 * Filter content area spacing body classes.
		 *
		 * @param string[] $classes    Spacing body classes.
		 * @param array    $components Enabled top and bottom components.
		 * @param array    $context    Request context data.
		 */
		$classes = apply_filters( 'customify/content_area_spacing/body_classes', $classes, $components, $context );

		return is_array( $classes )
			? array_values( array_filter( array_map( 'sanitize_html_class', $classes ) ) )
			: array();
	}
}
