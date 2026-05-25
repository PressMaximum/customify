<?php
/**
 * Bridge between the theme's theme.json typography.fontFamilies and
 * Customify's typography picker / frontend CSS pipeline.
 *
 * Two read paths, mirroring class-customizer-font-library.php:
 *
 *  - get_for_picker()   runs inside the Customizer AJAX endpoint
 *                        where the current user holds `customize` cap,
 *                        so we hit /wp/v2/global-styles/themes/<slug>
 *                        which exposes the theme.json declaration
 *                        verbatim (canonical source).
 *
 *  - get_for_frontend() runs for any visitor including guests; the
 *                        REST endpoint requires auth, so we read the
 *                        same data via WP_Theme_JSON_Resolver instead.
 *
 * Output shape matches the Font Library bridge so the picker JS,
 * setup_font(), and @font-face emitter can treat them identically.
 */
class Customify_Customizer_Theme_Fonts {

	/**
	 * Per-request memo for the frontend read path.
	 *
	 * @var array|null
	 */
	private $frontend_cache = null;

	/**
	 * Read theme fontFamilies for the Customizer picker.
	 *
	 * @return array
	 */
	public function get_for_picker() {
		if ( ! class_exists( 'WP_REST_Global_Styles_Controller' ) ) {
			return array(); // WP < 5.9
		}

		$stylesheet = get_stylesheet();
		$request    = new WP_REST_Request( 'GET', "/wp/v2/global-styles/themes/{$stylesheet}" );
		$response   = rest_do_request( $request );
		if ( $response->is_error() ) {
			return array();
		}

		$data = $response->get_data();
		$families = isset( $data['settings']['typography']['fontFamilies'] )
			? $data['settings']['typography']['fontFamilies']
			: array();

		// Endpoint normally returns the raw theme.json shape (flat
		// array), but some WP releases nest under 'theme'. Handle both.
		if ( isset( $families['theme'] ) && is_array( $families['theme'] ) ) {
			$families = $families['theme'];
		}

		return $this->normalize_families( $families );
	}

	/**
	 * Read theme fontFamilies for the frontend @font-face emitter.
	 *
	 * @return array
	 */
	public function get_for_frontend() {
		if ( null !== $this->frontend_cache ) {
			return $this->frontend_cache;
		}
		if ( ! class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			return $this->frontend_cache = array();
		}

		$theme_data = WP_Theme_JSON_Resolver::get_theme_data();
		$settings   = $theme_data->get_settings();
		$families   = array();
		if ( isset( $settings['typography']['fontFamilies']['theme'] ) ) {
			$families = $settings['typography']['fontFamilies']['theme'];
		} elseif ( isset( $settings['typography']['fontFamilies'] ) && is_array( $settings['typography']['fontFamilies'] ) ) {
			// Defensive: in case the Resolver flattens in some future WP.
			$first = $settings['typography']['fontFamilies'];
			if ( isset( $first[0] ) ) {
				$families = $first;
			}
		}

		return $this->frontend_cache = $this->normalize_families( $families );
	}

	/**
	 * Normalise a list of theme.json fontFamily entries into the same
	 * shape Customify uses for Google Fonts and Font Library entries.
	 *
	 * @param array $families
	 * @return array
	 */
	private function normalize_families( $families ) {
		$out = array();
		foreach ( (array) $families as $family ) {
			if ( ! is_array( $family ) ) {
				continue;
			}
			$name = isset( $family['name'] ) ? trim( $family['name'] ) : '';
			if ( ! $name ) {
				// Derive a label from the first family in the stack.
				$css = isset( $family['fontFamily'] ) ? $family['fontFamily'] : '';
				$name = $this->name_from_family_css( $css );
			}
			if ( ! $name ) {
				continue;
			}

			$faces = array();
			if ( ! empty( $family['fontFace'] ) && is_array( $family['fontFace'] ) ) {
				foreach ( $family['fontFace'] as $face ) {
					if ( ! is_array( $face ) ) {
						continue;
					}
					$src = $this->pick_src( isset( $face['src'] ) ? $face['src'] : '' );
					if ( ! $src ) {
						continue;
					}
					$weight = isset( $face['fontWeight'] ) ? (string) $face['fontWeight'] : '400';
					$style  = isset( $face['fontStyle'] )  ? (string) $face['fontStyle']  : 'normal';
					$faces[] = array(
						'variant' => $this->to_google_variant( $weight, $style ),
						'weight'  => $weight,
						'style'   => $style,
						'src'     => $src,
					);
				}
			}

			// Variants: derived from font-face entries when present,
			// otherwise just "400" (the picker still needs at least one
			// option to render).
			$variants = ! empty( $faces ) ? wp_list_pluck( $faces, 'variant' ) : array( '400' );

			$out[ $name ] = array(
				'family'          => $name,
				'category'        => 'theme',
				'variants'        => $variants,
				'subsets'         => array(),
				'font_faces'      => $faces,
				// Raw fontFamily CSS string from theme.json — used in
				// the frontend so a family declared as e.g.
				// "Inter, sans-serif" outputs the full stack instead
				// of just the name.
				'font_family_css' => isset( $family['fontFamily'] ) ? $family['fontFamily'] : $name,
			);
		}
		return $out;
	}

	/**
	 * Derive a human-readable name from a CSS font-family stack
	 * (theme.json may declare entries without an explicit `name`).
	 *
	 * @param string $css
	 * @return string
	 */
	private function name_from_family_css( $css ) {
		if ( ! is_string( $css ) || '' === $css ) {
			return '';
		}
		$parts = explode( ',', $css, 2 );
		$first = isset( $parts[0] ) ? $parts[0] : '';
		return trim( $first, " \t\n\r\0\x0B\"'" );
	}

	/**
	 * Pick the best font file URL from a font-face's src field,
	 * resolving theme.json's `file:./` prefix to a real theme URL.
	 *
	 * @param string|array $src
	 * @return string
	 */
	private function pick_src( $src ) {
		$candidates = is_array( $src ) ? $src : array( $src );
		foreach ( array( '.woff2', '.woff', '.ttf', '.otf' ) as $ext ) {
			foreach ( $candidates as $url ) {
				if ( ! is_string( $url ) ) {
					continue;
				}
				$resolved = $this->resolve_uri( $url );
				if ( substr( strtolower( $resolved ), -strlen( $ext ) ) === $ext ) {
					return $resolved;
				}
			}
		}
		foreach ( $candidates as $url ) {
			if ( is_string( $url ) && $url ) {
				return $this->resolve_uri( $url );
			}
		}
		return '';
	}

	/**
	 * Resolve a theme.json font-face URI. theme.json uses the
	 * `file:./<relative-path>` prefix for theme-bundled files; convert
	 * those to actual theme URLs. Already-absolute URLs pass through.
	 *
	 * @param string $uri
	 * @return string
	 */
	private function resolve_uri( $uri ) {
		if ( ! is_string( $uri ) ) {
			return '';
		}
		if ( 0 === strpos( $uri, 'file:./' ) ) {
			return get_theme_file_uri( substr( $uri, 7 ) );
		}
		return $uri;
	}

	/**
	 * Convert (weight, style) into the Google Fonts variant token used
	 * by Customify storage and the JS picker — see the matching helper
	 * in class-customizer-font-library.php.
	 *
	 * @param string $weight
	 * @param string $style
	 * @return string
	 */
	private function to_google_variant( $weight, $style ) {
		$w = (string) intval( $weight );
		if ( '0' === $w ) {
			$w = '400';
		}
		return ( 'italic' === $style ) ? $w . 'i' : $w;
	}
}
