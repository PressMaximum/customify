<?php
/**
 * Bridge between the WordPress Font Library (WP 6.5+) and Customify's
 * typography picker / frontend CSS pipeline.
 *
 * IMPORTANT — read from the activation set, not the raw CPT.
 *
 * WP Font Library stores uploaded fonts as wp_font_family + wp_font_face
 * CPTs, but the wp-admin "Library" tab UI also lets the user toggle
 * which variants are active on the site. Toggling OFF a variant does
 * NOT delete the wp_font_face post — it only removes that face from
 * the user-origin entry in theme.json (wp_global_styles). So querying
 * the CPTs directly returns every uploaded face, including ones the
 * user has explicitly deactivated.
 *
 * `wp_get_global_settings()['typography']['fontFamilies']['custom']`
 * is the authoritative activation set: WP itself uses it to decide
 * what wp_print_font_faces() emits. Reading from this source gives:
 *   - The same Library catalogue WP exposes in the block editor.
 *   - Automatic respect for user deactivations (the reported bug).
 *   - Pre-resolved font file URLs (file:./ paths already absolute).
 *   - No REST auth round-trip — works in any context including guests.
 *
 * Therefore both get_for_picker() and get_for_frontend() now share a
 * single private read(). Keeping the two public methods separate so
 * callers retain their semantic distinction (picker vs frontend) even
 * though the implementation is unified.
 */
class Customify_Customizer_Font_Library {

	/**
	 * Per-request memo. Auto_CSS iterates fields and may call this many
	 * times indirectly; build the catalogue once.
	 *
	 * @var array|null
	 */
	private $cache = null;

	/**
	 * Read activated Library fonts for the Customizer typography picker.
	 *
	 * @return array
	 */
	public function get_for_picker() {
		return $this->read();
	}

	/**
	 * Read activated Library fonts for the frontend @font-face emitter.
	 *
	 * @return array
	 */
	public function get_for_frontend() {
		return $this->read();
	}

	/**
	 * Single source of truth — pulls user-activated Library fonts from
	 * the merged global settings and normalises them into Customify's
	 * picker shape (variant tokens like "400i" / "700", font_faces[]).
	 *
	 * @return array
	 */
	private function read() {
		if ( null !== $this->cache ) {
			return $this->cache;
		}
		if ( ! function_exists( 'wp_get_global_settings' ) ) {
			return $this->cache = array(); // WP < 5.9 — no Font Library.
		}

		$settings = wp_get_global_settings();
		$by_origin = isset( $settings['typography']['fontFamilies'] )
			? $settings['typography']['fontFamilies']
			: array();

		// We want only the user-activated set (origin = "custom").
		// theme.json fonts live under ['theme'] and are handled by
		// Customify_Customizer_Theme_Fonts instead.
		$families = isset( $by_origin['custom'] ) && is_array( $by_origin['custom'] )
			? $by_origin['custom']
			: array();

		$out = array();
		foreach ( $families as $family ) {
			if ( ! is_array( $family ) ) {
				continue;
			}
			$name = isset( $family['name'] ) ? trim( (string) $family['name'] ) : '';
			if ( '' === $name ) {
				continue;
			}
			if ( empty( $family['fontFace'] ) || ! is_array( $family['fontFace'] ) ) {
				continue; // No activated variants → nothing to render.
			}

			$faces = array();
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

		return $this->cache = $out;
	}

	/**
	 * Convert WP Font Library (weight, style) pair into Google Fonts
	 * variant token used by Customify storage and the JS picker.
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
	 * Pick the best font file URL from a face's src field. WP usually
	 * stores src as a single absolute URL string in merged settings,
	 * but the field accepts arrays too — handle both, preferring
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
