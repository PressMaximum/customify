<?php
/**
 * Bridge between the WordPress Font Library (WP 6.5+) and Customify's
 * typography picker / frontend CSS pipeline.
 *
 * Two read paths because the contexts have different capabilities:
 *  - get_for_picker()   runs inside the Customizer AJAX endpoint where
 *                        the current user holds `customize` cap, so we
 *                        go through the REST controller (proper layer).
 *  - get_for_frontend() runs for any visitor including guests, who do
 *                        not have REST read access to the private
 *                        `wp_font_family` CPT. Direct WP_Query bypass.
 */
class Customify_Customizer_Font_Library {

	/**
	 * Per-request memo for the frontend read path. Auto_CSS iterates
	 * fields and may call this many times indirectly; query once.
	 *
	 * @var array|null
	 */
	private $frontend_cache = null;

	/**
	 * Read fonts for the Customizer typography picker.
	 * Returns map keyed by family name → metadata in the same shape
	 * Customify uses for Google Fonts (variants[], subsets[], …).
	 *
	 * @return array
	 */
	public function get_for_picker() {
		if ( ! post_type_exists( 'wp_font_family' ) ) {
			return array();
		}

		$request = new WP_REST_Request( 'GET', '/wp/v2/font-families' );
		$request->set_param( 'context', 'edit' );
		$request->set_param( 'per_page', 100 );
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			return array();
		}

		$out = array();
		foreach ( (array) $response->get_data() as $item ) {
			$settings = isset( $item['font_family_settings'] ) ? $item['font_family_settings'] : array();
			$name     = isset( $settings['name'] ) ? $settings['name'] : '';
			$family_id = isset( $item['id'] ) ? (int) $item['id'] : 0;
			if ( ! $name || ! $family_id ) {
				continue;
			}

			$faces = $this->load_faces_for( $family_id );
			if ( empty( $faces ) ) {
				continue;
			}

			$out[ $name ] = array(
				'family'     => $name,
				'category'   => 'library',
				'variants'   => wp_list_pluck( $faces, 'variant' ),
				'subsets'    => array(),
				'font_faces' => $faces,
			);
		}

		return $out;
	}

	/**
	 * Read fonts for the frontend @font-face emitter.
	 * Memoised per request because auto_css() may indirectly trigger
	 * many lookups during a single render pass.
	 *
	 * @return array
	 */
	public function get_for_frontend() {
		if ( null !== $this->frontend_cache ) {
			return $this->frontend_cache;
		}
		if ( ! post_type_exists( 'wp_font_family' ) ) {
			return $this->frontend_cache = array();
		}

		$families = get_posts( array(
			'post_type'        => 'wp_font_family',
			'posts_per_page'   => 100,
			'post_status'      => 'publish',
			'suppress_filters' => true,
		) );

		$out = array();
		foreach ( $families as $family ) {
			// WP Font Library schema (verified WP 6.5+): the family
			// post stores its display name in `post_title`; the
			// `post_content` JSON only carries the CSS `fontFamily`
			// stack and a preview URL. Fall back to post_content[name]
			// in case a future WP release adds it there.
			$settings = json_decode( $family->post_content, true );
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}
			$name = trim( (string) $family->post_title );
			if ( '' === $name && isset( $settings['name'] ) ) {
				$name = (string) $settings['name'];
			}
			if ( '' === $name ) {
				continue;
			}

			$faces = $this->load_faces_for( $family->ID );
			if ( empty( $faces ) ) {
				continue;
			}

			$out[ $name ] = array(
				'family'     => $name,
				'category'   => 'library',
				'variants'   => wp_list_pluck( $faces, 'variant' ),
				'subsets'    => array(),
				'font_faces' => $faces,
			);
		}

		return $this->frontend_cache = $out;
	}

	/**
	 * Load font-face children for a given font-family post and
	 * normalise them into Customify's variant shape.
	 *
	 * @param int $family_id
	 * @return array
	 */
	private function load_faces_for( $family_id ) {
		$faces = get_posts( array(
			'post_type'        => 'wp_font_face',
			'post_parent'      => $family_id,
			'posts_per_page'   => 50,
			'post_status'      => 'publish',
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'suppress_filters' => true,
		) );

		$out = array();
		foreach ( $faces as $face ) {
			$cfg    = json_decode( $face->post_content, true );
			if ( ! is_array( $cfg ) ) {
				continue;
			}
			$src    = $this->pick_src( isset( $cfg['src'] ) ? $cfg['src'] : '' );
			if ( ! $src ) {
				continue;
			}
			$weight = isset( $cfg['fontWeight'] ) ? (string) $cfg['fontWeight'] : '400';
			$style  = isset( $cfg['fontStyle'] )  ? (string) $cfg['fontStyle']  : 'normal';
			$out[]  = array(
				'variant' => $this->to_google_variant( $weight, $style ),
				'weight'  => $weight,
				'style'   => $style,
				'src'     => $src,
			);
		}
		return $out;
	}

	/**
	 * Convert WP Font Library (weight, style) pair into Google Fonts
	 * variant token used by Customify storage and JS picker.
	 * Examples: (400, normal)→"400", (400, italic)→"400i", (700)→"700".
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

	/**
	 * Pick the best font file URL from a face's src field. WP Font
	 * Library stores src as either a string or array of URLs; prefer
	 * woff2 → woff → ttf → otf for compression and browser support.
	 *
	 * @param string|array $src
	 * @return string
	 */
	private function pick_src( $src ) {
		if ( is_string( $src ) ) {
			return $src;
		}
		if ( ! is_array( $src ) || empty( $src ) ) {
			return '';
		}
		foreach ( array( '.woff2', '.woff', '.ttf', '.otf' ) as $ext ) {
			foreach ( $src as $url ) {
				if ( is_string( $url ) && substr( strtolower( $url ), -strlen( $ext ) ) === $ext ) {
					return $url;
				}
			}
		}
		foreach ( $src as $url ) {
			if ( is_string( $url ) && $url ) {
				return $url;
			}
		}
		return '';
	}
}
